<?php

namespace App\Http\Controllers\Technicien;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Comment;
use App\Models\Category;
use App\Models\Devis;
use App\Models\Facture;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    /**
     * Display a listing of the tickets.
     */
    public function index()
    {
        $tickets = Ticket::where('assigned_to', Auth::id())
            ->orWhere(function ($query) {
                $query->whereNull('assigned_to')
                    ->where('statut', 'nouveau');
            })
            ->with(['category', 'creator', 'assignedTo'])
            ->latest()
            ->paginate(10);
            
        return view('technicien.tickets.index', compact('tickets'));
    }

    /**
     * Display the specified ticket.
     */
    public function show(Ticket $ticket)
    {
        // Vérification que le technicien a accès au ticket
        if ($ticket->assigned_to !== Auth::id() && $ticket->assigned_to !== null) {
            return redirect()->route('technicien.tickets.index')
                ->with('error', 'Vous n\'êtes pas autorisé à voir ce ticket.');
        }

        $ticket->load(['category', 'creator', 'assignedTo', 'comments.user', 'attachments', 'devis', 'factures']);

        return view('technicien.tickets.show', compact('ticket'));
    }

    /**
     * Show the form for editing the specified ticket.
     */
    public function edit(Ticket $ticket)
    {
        // Vérification que le technicien a accès au ticket
        if ($ticket->assigned_to !== Auth::id() && $ticket->assigned_to !== null) {
            return redirect()->route('technicien.tickets.index')
                ->with('error', 'Vous n\'êtes pas autorisé à modifier ce ticket.');
        }
        
        return view('technicien.tickets.edit', compact('ticket'));
    }

    /**
     * Update the specified ticket in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        // Vérification que le technicien a accès au ticket
        if ($ticket->assigned_to !== Auth::id() && $ticket->assigned_to !== null) {
            return redirect()->route('technicien.tickets.index')
                ->with('error', 'Vous n\'êtes pas autorisé à modifier ce ticket.');
        }

        $validated = $request->validate([
            'statut' => 'required|in:en_cours,resolu',
        ]);

        // Si le ticket n'est pas encore assigné, l'assigner au technicien actuel
        if ($ticket->assigned_to === null) {
            $ticket->assigned_to = Auth::id();
        }

        // Si on change le statut à résolu, mettre la date de clôture
        if ($validated['statut'] === 'resolu' && $ticket->statut !== 'resolu') {
            $ticket->closed_at = now();
        }

        $ticket->statut = $validated['statut'];
        $ticket->save();

        return redirect()->route('technicien.tickets.show', $ticket->id)
            ->with('success', 'Ticket mis à jour avec succès.');
    }

    /**
     * Ajouter un commentaire à un ticket.
     */
    public function addComment(Request $request, Ticket $ticket)
    {
        // Vérification que le technicien a accès au ticket
        if ($ticket->assigned_to !== Auth::id() && $ticket->assigned_to !== null) {
            return redirect()->route('technicien.tickets.index')
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

        return redirect()->route('technicien.tickets.show', $ticket->id)
            ->with('success', 'Commentaire ajouté avec succès.');
    }

    /**
     * Créer un devis pour un ticket.
     */
    public function addDevis(Request $request, Ticket $ticket)
    {
        // Vérification que le technicien a accès au ticket
        if ($ticket->assigned_to !== Auth::id()) {
            return redirect()->route('technicien.tickets.index')
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

        return redirect()->route('technicien.tickets.show', $ticket->id)
            ->with('success', 'Devis créé avec succès.');
    }

    /**
     * Créer une facture pour un ticket.
     */
    public function addFacture(Request $request, Ticket $ticket)
    {
        // Vérification que le technicien a accès au ticket
        if ($ticket->assigned_to !== Auth::id()) {
            return redirect()->route('technicien.tickets.index')
                ->with('error', 'Vous n\'êtes pas autorisé à créer une facture pour ce ticket.');
        }

        // Vérification que le ticket est résolu
        if ($ticket->statut !== 'resolu' && $ticket->statut !== 'cloture') {
            return redirect()->route('technicien.tickets.show', $ticket->id)
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

        return redirect()->route('technicien.tickets.show', $ticket->id)
            ->with('success', 'Facture créée avec succès.');
    }
}
