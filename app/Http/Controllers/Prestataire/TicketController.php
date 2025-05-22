<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Comment;
use App\Models\Category;
use App\Models\Devis;
use App\Models\Facture;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class TicketController extends Controller
{
    /**
     * Display a listing of the tickets.
     */
    public function index()
    {
        // Récupérer les tickets assignés au prestataire et à ses techniciens
        $prestataire = Auth::user();
        $technicienIds = $prestataire->techniciens->pluck('id')->toArray();
        $technicienIds[] = $prestataire->id;
        
        $tickets = Ticket::whereIn('assigned_to', $technicienIds)
            ->with(['category', 'creator', 'assignedTo'])
            ->latest()
            ->paginate(10);
            
        return view('prestataire.tickets.index', compact('tickets'));
    }

    /**
     * Display the specified ticket.
     */
    public function show(Ticket $ticket)
    {
        // Récupérer les techniciens du prestataire
        $prestataire = Auth::user();
        $technicienIds = $prestataire->techniciens->pluck('id')->toArray();
        $technicienIds[] = $prestataire->id;
        
        // Vérifier que le ticket est assigné au prestataire ou à l'un de ses techniciens
        if (!in_array($ticket->assigned_to, $technicienIds)) {
            return redirect()->route('prestataire.tickets.index')
                ->with('error', 'Vous n\'êtes pas autorisé à voir ce ticket.');
        }

        $ticket->load(['category', 'creator', 'assignedTo', 'comments.user', 'attachments', 'devis', 'factures']);
        $techniciens = $prestataire->techniciens;

        return view('prestataire.tickets.show', compact('ticket', 'techniciens'));
    }

    /**
     * Update the specified ticket in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        // Récupérer les techniciens du prestataire
        $prestataire = Auth::user();
        $technicienIds = $prestataire->techniciens->pluck('id')->toArray();
        $technicienIds[] = $prestataire->id;
        
        // Vérifier que le ticket est assigné au prestataire ou à l'un de ses techniciens
        if (!in_array($ticket->assigned_to, $technicienIds)) {
            return redirect()->route('prestataire.tickets.index')
                ->with('error', 'Vous n\'êtes pas autorisé à modifier ce ticket.');
        }

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'statut' => 'required|in:en_cours,resolu',
        ]);

        // Vérifier que l'assignation est faite à un technicien du prestataire
        if (!in_array($validated['assigned_to'], $technicienIds)) {
            return redirect()->route('prestataire.tickets.show', $ticket->id)
                ->with('error', 'Vous ne pouvez assigner ce ticket qu\'à vous-même ou à l\'un de vos techniciens.');
        }

        // Si on change le statut à résolu, mettre la date de clôture
        if ($validated['statut'] === 'resolu' && $ticket->statut !== 'resolu') {
            $ticket->closed_at = now();
        }

        $ticket->assigned_to = $validated['assigned_to'];
        $ticket->statut = $validated['statut'];
        $ticket->save();

        return redirect()->route('prestataire.tickets.show', $ticket->id)
            ->with('success', 'Ticket mis à jour avec succès.');
    }

    /**
     * Ajouter un commentaire à un ticket.
     */
    public function addComment(Request $request, Ticket $ticket)
    {
        // Récupérer les techniciens du prestataire
        $prestataire = Auth::user();
        $technicienIds = $prestataire->techniciens->pluck('id')->toArray();
        $technicienIds[] = $prestataire->id;
        
        // Vérifier que le ticket est assigné au prestataire ou à l'un de ses techniciens
        if (!in_array($ticket->assigned_to, $technicienIds)) {
            return redirect()->route('prestataire.tickets.index')
                ->with('error', 'Vous n\'êtes pas autorisé à commenter ce ticket.');
        }

        $validated = $request->validate([
            'contenu' => 'required|string',
            'visible_pour_utilisateur' => 'boolean',
        ]);

        $comment = Comment::create([
            'contenu' => $validated['contenu'],
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'visible_pour_utilisateur' => $validated['visible_pour_utilisateur'] ?? true,
        ]);

        // Gestion des pièces jointes du commentaire
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('comment-attachments', 'public');
                
                Attachment::create([
                    'nom' => $file->getClientOriginalName(),
                    'chemin' => $path,
                    'type_mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'comment_id' => $comment->id,
                ]);
            }
        }

        return redirect()->route('prestataire.tickets.show', $ticket->id)
            ->with('success', 'Commentaire ajouté avec succès.');
    }

    /**
     * Créer un devis pour un ticket.
     */
    public function addDevis(Request $request, Ticket $ticket)
    {
        // Récupérer les techniciens du prestataire
        $prestataire = Auth::user();
        $technicienIds = $prestataire->techniciens->pluck('id')->toArray();
        $technicienIds[] = $prestataire->id;
        
        // Vérifier que le ticket est assigné au prestataire ou à l'un de ses techniciens
        if (!in_array($ticket->assigned_to, $technicienIds)) {
            return redirect()->route('prestataire.tickets.index')
                ->with('error', 'Vous n\'êtes pas autorisé à créer un devis pour ce ticket.');
        }

        $validated = $request->validate([
            'montant' => 'required|numeric|min:0',
            'description' => 'required|string',
            'fichier' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
        ]);

        $devis = new Devis([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'montant' => $validated['montant'],
            'description' => $validated['description'],
            'statut' => 'en_attente',
        ]);

        // Traitement du fichier si présent
        if ($request->hasFile('fichier')) {
            $path = $request->file('fichier')->store('devis-files', 'public');
            $devis->fichier_path = $path;
        }

        $devis->save();

        return redirect()->route('prestataire.tickets.show', $ticket->id)
            ->with('success', 'Devis créé avec succès.');
    }

    /**
     * Créer une facture pour un ticket.
     */
    public function addFacture(Request $request, Ticket $ticket)
    {
        // Récupérer les techniciens du prestataire
        $prestataire = Auth::user();
        $technicienIds = $prestataire->techniciens->pluck('id')->toArray();
        $technicienIds[] = $prestataire->id;
        
        // Vérifier que le ticket est assigné au prestataire ou à l'un de ses techniciens
        if (!in_array($ticket->assigned_to, $technicienIds)) {
            return redirect()->route('prestataire.tickets.index')
                ->with('error', 'Vous n\'êtes pas autorisé à créer une facture pour ce ticket.');
        }

        // Vérification que le ticket est résolu
        if ($ticket->statut !== 'resolu' && $ticket->statut !== 'cloture') {
            return redirect()->route('prestataire.tickets.show', $ticket->id)
                ->with('error', 'Vous ne pouvez créer une facture que pour un ticket résolu ou clôturé.');
        }

        $validated = $request->validate([
            'devis_id' => 'nullable|exists:devis,id',
            'montant' => 'required|numeric|min:0',
            'numero_facture' => 'required|string|unique:factures,numero_facture',
            'description' => 'required|string',
            'fichier' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
        ]);

        $facture = new Facture([
            'ticket_id' => $ticket->id,
            'devis_id' => $validated['devis_id'] ?? null,
            'user_id' => Auth::id(),
            'montant' => $validated['montant'],
            'numero_facture' => $validated['numero_facture'],
            'description' => $validated['description'],
            'statut' => 'en_attente',
        ]);

        // Traitement du fichier si présent
        if ($request->hasFile('fichier')) {
            $path = $request->file('fichier')->store('facture-files', 'public');
            $facture->fichier_path = $path;
        }

        $facture->save();

        return redirect()->route('prestataire.tickets.show', $ticket->id)
            ->with('success', 'Facture créée avec succès.');
    }
    
    /**
     * Afficher la liste des techniciens du prestataire.
     */
    public function indexTechniciens()
    {
        $techniciens = Auth::user()->techniciens()->paginate(10);
        
        return view('prestataire.techniciens.index', compact('techniciens'));
    }
    
    /**
     * Afficher le formulaire de création d'un technicien.
     */
    public function createTechnicien()
    {
        return view('prestataire.techniciens.create');
    }
    
    /**
     * Enregistrer un nouveau technicien.
     */
    public function storeTechnicien(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'telephone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        $technicien = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'telephone' => $validated['telephone'],
            'password' => Hash::make($validated['password']),
            'type' => 'prestataire',
            'is_active' => true,
            'prestataire_id' => Auth::id(),
        ]);
        
        return redirect()->route('prestataire.techniciens.index')
            ->with('success', 'Technicien ajouté avec succès.');
    }
    
    /**
     * Afficher le formulaire d'édition d'un technicien.
     */
    public function editTechnicien(User $technicien)
    {
        // Vérifier que le technicien appartient bien au prestataire connecté
        if ($technicien->prestataire_id !== Auth::id()) {
            return redirect()->route('prestataire.techniciens.index')
                ->with('error', 'Vous n\'êtes pas autorisé à modifier ce technicien.');
        }
        
        return view('prestataire.techniciens.edit', compact('technicien'));
    }
    
    /**
     * Mettre à jour un technicien.
     */
    public function updateTechnicien(Request $request, User $technicien)
    {
        // Vérifier que le technicien appartient bien au prestataire connecté
        if ($technicien->prestataire_id !== Auth::id()) {
            return redirect()->route('prestataire.techniciens.index')
                ->with('error', 'Vous n\'êtes pas autorisé à modifier ce technicien.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $technicien->id,
            'telephone' => 'nullable|string|max:20',
            'is_active' => 'required|boolean',
            'password' => 'nullable|string|min:8|confirmed',
        ]);
        
        $technicien->name = $validated['name'];
        $technicien->email = $validated['email'];
        $technicien->telephone = $validated['telephone'];
        $technicien->is_active = $validated['is_active'];
        
        if (!empty($validated['password'])) {
            $technicien->password = Hash::make($validated['password']);
        }
        
        $technicien->save();
        
        return redirect()->route('prestataire.techniciens.index')
            ->with('success', 'Technicien mis à jour avec succès.');
    }
    
    /**
     * Supprimer un technicien.
     */
    public function destroyTechnicien(User $technicien)
    {
        // Vérifier que le technicien appartient bien au prestataire connecté
        if ($technicien->prestataire_id !== Auth::id()) {
            return redirect()->route('prestataire.techniciens.index')
                ->with('error', 'Vous n\'êtes pas autorisé à supprimer ce technicien.');
        }
        
        // Vérifier que le technicien n'a pas de tickets assignés
        $assignedTickets = Ticket::where('assigned_to', $technicien->id)
            ->where('statut', '!=', 'cloture')
            ->count();
            
        if ($assignedTickets > 0) {
            return redirect()->route('prestataire.techniciens.index')
                ->with('error', 'Impossible de supprimer ce technicien car il a des tickets en cours assignés.');
        }
        
        $technicien->delete();
        
        return redirect()->route('prestataire.techniciens.index')
            ->with('success', 'Technicien supprimé avec succès.');
    }
}
