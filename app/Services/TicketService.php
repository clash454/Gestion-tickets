<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\Comment;
use App\Models\Attachment;
use App\Models\Evaluation;
use App\Models\Devis;
use App\Models\Facture;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class TicketService
{
    /**
     * Récupérer les tickets selon le type d'utilisateur connecté
     *
     * @param string $userType Type d'utilisateur ('utilisateur', 'technicien_interne', 'prestataire')
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getTicketsByUserType(string $userType)
    {
        $query = Ticket::with(['category', 'creator', 'assignedTo']);
        
        switch ($userType) {
            case 'utilisateur':
                $query->where('created_by', Auth::id());
                break;
            case 'technicien_interne':
                $query->where('assigned_to', Auth::id())
                      ->orWhereNull('assigned_to');
                break;
            case 'prestataire':
                // Pour un prestataire, on récupère les tickets assignés à lui ou à ses techniciens
                $technicienIds = Auth::user()->techniciens->pluck('id')->toArray();
                $technicienIds[] = Auth::id();
                
                $query->whereIn('assigned_to', $technicienIds)
                      ->orWhereNull('assigned_to');
                break;
        }
        
        return $query->latest()->paginate(10);
    }
    
    /**
     * Créer un nouveau ticket
     *
     * @param array $data Données validées du ticket
     * @param array|null $files Fichiers joints (pièces jointes)
     * @return \App\Models\Ticket
     */
    public function createTicket(array $data, ?array $files = null)
    {
        $ticket = Ticket::create([
            'titre' => $data['titre'],
            'description' => $data['description'],
            'category_id' => $data['category_id'],
            'statut' => 'nouveau',
            'created_by' => Auth::id(),
        ]);
        
        // Gestion des pièces jointes si présentes
        if ($files) {
            $this->saveAttachments($files, $ticket->id);
        }
        
        return $ticket;
    }
    
    /**
     * Mettre à jour un ticket existant
     *
     * @param \App\Models\Ticket $ticket Ticket à mettre à jour
     * @param array $data Données validées du ticket
     * @param array|null $files Fichiers joints (pièces jointes)
     * @return \App\Models\Ticket
     */
    public function updateTicket(Ticket $ticket, array $data, ?array $files = null)
    {
        $ticket->update($data);
        
        // Gestion des pièces jointes si présentes
        if ($files) {
            $this->saveAttachments($files, $ticket->id);
        }
        
        return $ticket;
    }
    
    /**
     * Ajouter un commentaire à un ticket
     *
     * @param \App\Models\Ticket $ticket Ticket concerné
     * @param array $data Données validées du commentaire
     * @param bool $isVisibleForUser Indique si le commentaire est visible pour l'utilisateur
     * @param array|null $files Fichiers joints (pièces jointes)
     * @return \App\Models\Comment
     */
    public function addComment(Ticket $ticket, array $data, bool $isVisibleForUser = true, ?array $files = null)
    {
        $comment = Comment::create([
            'contenu' => $data['contenu'],
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'visible_pour_utilisateur' => $isVisibleForUser,
        ]);
        
        // Gestion des pièces jointes du commentaire si présentes
        if ($files) {
            $this->saveAttachments($files, null, $comment->id);
        }
        
        return $comment;
    }
    
    /**
     * Ajouter ou mettre à jour une évaluation pour un ticket
     *
     * @param \App\Models\Ticket $ticket Ticket concerné
     * @param array $data Données validées de l'évaluation
     * @return \App\Models\Evaluation
     */
    public function addOrUpdateEvaluation(Ticket $ticket, array $data)
    {
        // Vérification si une évaluation existe déjà
        $existingEvaluation = Evaluation::where('ticket_id', $ticket->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingEvaluation) {
            $existingEvaluation->update([
                'note' => $data['note'],
                'commentaire' => $data['commentaire'] ?? null,
            ]);
            
            return $existingEvaluation;
        } else {
            return Evaluation::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'technicien_id' => $ticket->assigned_to,
                'note' => $data['note'],
                'commentaire' => $data['commentaire'] ?? null,
            ]);
        }
    }
    
    /**
     * Ajouter un devis à un ticket
     *
     * @param \App\Models\Ticket $ticket Ticket concerné
     * @param array $data Données validées du devis
     * @param array|null $files Fichiers joints (pièces jointes)
     * @return \App\Models\Devis
     */
    public function addDevis(Ticket $ticket, array $data, ?array $files = null)
    {
        $devis = Devis::create([
            'montant' => $data['montant'],
            'description' => $data['description'],
            'validite' => $data['validite'],
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'statut' => 'en_attente',
        ]);
        
        // Gestion des pièces jointes du devis si présentes
        if ($files) {
            $this->saveAttachments($files, null, null, $devis->id);
        }
        
        return $devis;
    }
    
    /**
     * Ajouter une facture à un ticket
     *
     * @param \App\Models\Ticket $ticket Ticket concerné
     * @param array $data Données validées de la facture
     * @param array|null $files Fichiers joints (pièces jointes)
     * @return \App\Models\Facture
     */
    public function addFacture(Ticket $ticket, array $data, ?array $files = null)
    {
        $facture = Facture::create([
            'montant' => $data['montant'],
            'description' => $data['description'],
            'date_echeance' => $data['date_echeance'],
            'ticket_id' => $ticket->id,
            'devis_id' => $data['devis_id'] ?? null,
            'user_id' => Auth::id(),
            'statut' => 'a_payer',
        ]);
        
        // Gestion des pièces jointes de la facture si présentes
        if ($files) {
            $this->saveAttachments($files, null, null, null, $facture->id);
        }
        
        return $facture;
    }
    
    /**
     * Changer le statut d'un ticket
     *
     * @param \App\Models\Ticket $ticket Ticket concerné
     * @param string $statut Nouveau statut
     * @return \App\Models\Ticket
     */
    public function changeStatus(Ticket $ticket, string $statut)
    {
        $ticket->statut = $statut;
        
        if ($statut === 'cloture') {
            $ticket->closed_at = now();
        }
        
        $ticket->save();
        return $ticket;
    }
    
    /**
     * Assigner un ticket à un technicien ou prestataire
     *
     * @param \App\Models\Ticket $ticket Ticket concerné
     * @param int $userId ID de l'utilisateur (technicien ou prestataire)
     * @return \App\Models\Ticket
     */
    public function assignTicket(Ticket $ticket, int $userId)
    {
        $ticket->assigned_to = $userId;
        $ticket->save();
        return $ticket;
    }
    
    /**
     * Enregistrer des pièces jointes
     *
     * @param array $files Fichiers à enregistrer
     * @param int|null $ticketId ID du ticket associé
     * @param int|null $commentId ID du commentaire associé
     * @param int|null $devisId ID du devis associé
     * @param int|null $factureId ID de la facture associée
     * @return array Collection des pièces jointes créées
     */
    private function saveAttachments(array $files, ?int $ticketId = null, ?int $commentId = null, ?int $devisId = null, ?int $factureId = null)
    {
        $attachments = [];
        
        foreach ($files as $file) {
            $path = $file->store('attachments', 'public');
            
            $attachment = Attachment::create([
                'nom' => $file->getClientOriginalName(),
                'chemin' => $path,
                'type_mime' => $file->getMimeType(),
                'size' => $file->getSize(),
                'ticket_id' => $ticketId,
                'comment_id' => $commentId,
                'devis_id' => $devisId,
                'facture_id' => $factureId,
            ]);
            
            $attachments[] = $attachment;
        }
        
        return $attachments;
    }
} 