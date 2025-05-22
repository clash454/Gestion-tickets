<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Comment;
use App\Models\Category;
use App\Models\Evaluation;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class TicketController extends Controller
{
    /**
     * Constructeur du contrôleur.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:utilisateur');
    }

    /**
     * Display a listing of the tickets.
     */
    public function index()
    {
        // Utilisation de la policy pour vérifier si l'utilisateur peut voir la liste de ses tickets
        $this->authorize('viewAny', Ticket::class);
        
        $tickets = Ticket::where('created_by', Auth::id())
            ->with(['category', 'creator', 'assignedTo'])
            ->latest()
            ->paginate(10);
            
        return view('user.tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new ticket.
     */
    public function create()
    {
        // Utilisation de la policy pour vérifier si l'utilisateur peut créer un ticket
        $this->authorize('create', Ticket::class);
        
        $categories = Category::where('is_active', true)->get();
        
        return view('user.tickets.create', compact('categories'));
    }

    /**
     * Store a newly created ticket in storage.
     */
    public function store(Request $request)
    {
        // Utilisation de la policy pour vérifier si l'utilisateur peut créer un ticket
        $this->authorize('create', Ticket::class);
        
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        $ticket = Ticket::create([
            'titre' => $validated['titre'],
            'description' => $validated['description'],
            'category_id' => $validated['category_id'],
            'statut' => 'nouveau',
            'created_by' => Auth::id(),
        ]);

        // Gestion des pièces jointes
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('ticket-attachments', 'public');
                
                Attachment::create([
                    'nom' => $file->getClientOriginalName(),
                    'chemin' => $path,
                    'type_mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'ticket_id' => $ticket->id,
                ]);
            }
        }

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Ticket créé avec succès.');
    }

    /**
     * Display the specified ticket.
     */
    public function show(Ticket $ticket)
    {
        // Utilisation de la policy pour vérifier si l'utilisateur peut voir ce ticket
        $this->authorize('view', $ticket);

        $ticket->load(['category', 'creator', 'assignedTo', 'comments' => function ($query) {
            $query->where('visible_pour_utilisateur', true)
                ->with('user');
        }, 'attachments']);

        return view('user.tickets.show', compact('ticket'));
    }

    /**
     * Show the form for editing the specified ticket.
     */
    public function edit(Ticket $ticket)
    {
        // Utilisation de la policy pour vérifier si l'utilisateur peut modifier ce ticket
        $this->authorize('update', $ticket);

        $categories = Category::where('is_active', true)->get();
        
        return view('user.tickets.edit', compact('ticket', 'categories'));
    }

    /**
     * Update the specified ticket in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        // Utilisation de la policy pour vérifier si l'utilisateur peut modifier ce ticket
        $this->authorize('update', $ticket);

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        $ticket->update($validated);

        // Gestion des nouvelles pièces jointes
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('ticket-attachments', 'public');
                
                Attachment::create([
                    'nom' => $file->getClientOriginalName(),
                    'chemin' => $path,
                    'type_mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'ticket_id' => $ticket->id,
                ]);
            }
        }

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Ticket mis à jour avec succès.');
    }

    /**
     * Ajouter un commentaire à un ticket.
     */
    public function addComment(Request $request, Ticket $ticket)
    {
        // Utilisation de la policy pour vérifier si l'utilisateur peut commenter ce ticket
        $this->authorize('addComment', $ticket);

        $validated = $request->validate([
            'contenu' => 'required|string',
        ]);

        $comment = Comment::create([
            'contenu' => $validated['contenu'],
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'visible_pour_utilisateur' => true,
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

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Commentaire ajouté avec succès.');
    }

    /**
     * Ajouter une évaluation à un ticket.
     */
    public function addEvaluation(Request $request, Ticket $ticket)
    {
        // Utilisation de la policy pour vérifier si l'utilisateur peut évaluer ce ticket
        $this->authorize('addEvaluation', $ticket);

        $validated = $request->validate([
            'note' => 'required|integer|between:1,5',
            'commentaire' => 'nullable|string',
        ]);

        // Vérification si une évaluation existe déjà
        $existingEvaluation = Evaluation::where('ticket_id', $ticket->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingEvaluation) {
            $existingEvaluation->update([
                'note' => $validated['note'],
                'commentaire' => $validated['commentaire'] ?? null,
            ]);
            
            $message = 'Évaluation mise à jour avec succès.';
        } else {
            Evaluation::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'technicien_id' => $ticket->assigned_to,
                'note' => $validated['note'],
                'commentaire' => $validated['commentaire'] ?? null,
            ]);
            
            $message = 'Évaluation ajoutée avec succès.';
        }

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', $message);
    }
}
