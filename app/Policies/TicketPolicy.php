<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TicketPolicy
{
    /**
     * Vérifie si l'utilisateur a le type/rôle spécifié
     */
    private function hasRole(User $user, string $role): bool
    {
        return $user->type === $role;
    }
    
    /**
     * Vérifie si l'utilisateur est un administrateur
     */
    private function isAdmin(User $user): bool
    {
        return $this->hasRole($user, 'admin');
    }
    
    /**
     * Vérifie si l'utilisateur est un technicien interne
     */
    private function isTechnicien(User $user): bool
    {
        return $this->hasRole($user, 'technicien_interne');
    }
    
    /**
     * Vérifie si l'utilisateur est un prestataire
     */
    private function isPrestataire(User $user): bool
    {
        return $this->hasRole($user, 'prestataire');
    }
    
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Tout utilisateur authentifié peut voir une liste de tickets (selon son rôle)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        // Un utilisateur standard peut voir uniquement ses propres tickets
        if ($this->hasRole($user, 'utilisateur')) {
            return $user->id === $ticket->created_by;
        }
        
        // Un technicien interne peut voir les tickets qui lui sont assignés ou non assignés
        if ($this->isTechnicien($user)) {
            return $ticket->assigned_to === $user->id || $ticket->assigned_to === null;
        }
        
        // Un prestataire peut voir les tickets qui lui sont assignés
        if ($this->isPrestataire($user)) {
            return $ticket->prestataire_id === $user->id;
        }
        
        // Les administrateurs peuvent voir tous les tickets
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Seuls les utilisateurs standards et administrateurs peuvent créer des tickets
        return $this->hasRole($user, 'utilisateur') || $this->isAdmin($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        // Un utilisateur standard peut modifier uniquement ses propres tickets et seulement s'ils sont nouveaux
        if ($this->hasRole($user, 'utilisateur')) {
            return $user->id === $ticket->created_by && $ticket->statut === 'nouveau';
        }
        
        // Un technicien interne peut mettre à jour les tickets qui lui sont assignés
        if ($this->isTechnicien($user)) {
            return $ticket->assigned_to === $user->id;
        }
        
        // Un prestataire peut mettre à jour les tickets qui lui sont assignés
        if ($this->isPrestataire($user)) {
            return $ticket->prestataire_id === $user->id;
        }
        
        // Les administrateurs peuvent mettre à jour tous les tickets
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        // Seuls les administrateurs peuvent supprimer des tickets
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Ticket $ticket): bool
    {
        // Seuls les administrateurs peuvent restaurer des tickets
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Ticket $ticket): bool
    {
        // Seuls les administrateurs peuvent supprimer définitivement des tickets
        return $this->isAdmin($user);
    }
    
    /**
     * Détermine si l'utilisateur peut ajouter un commentaire au ticket.
     */
    public function addComment(User $user, Ticket $ticket): bool
    {
        // Les utilisateurs peuvent commenter leurs propres tickets
        if ($this->hasRole($user, 'utilisateur')) {
            return $user->id === $ticket->created_by;
        }
        
        // Les techniciens peuvent commenter les tickets qui leur sont assignés
        if ($this->isTechnicien($user)) {
            return $ticket->assigned_to === $user->id;
        }
        
        // Les prestataires peuvent commenter les tickets qui leur sont assignés
        if ($this->isPrestataire($user)) {
            return $ticket->prestataire_id === $user->id;
        }
        
        // Les administrateurs peuvent commenter tous les tickets
        return $this->isAdmin($user);
    }
    
    /**
     * Détermine si l'utilisateur peut changer le statut du ticket.
     */
    public function changeStatus(User $user, Ticket $ticket): bool
    {
        // Les techniciens peuvent changer le statut des tickets qui leur sont assignés
        if ($this->isTechnicien($user)) {
            return $ticket->assigned_to === $user->id;
        }
        
        // Les prestataires peuvent changer le statut des tickets qui leur sont assignés
        if ($this->isPrestataire($user)) {
            return $ticket->prestataire_id === $user->id;
        }
        
        // Les administrateurs peuvent changer le statut de tous les tickets
        return $this->isAdmin($user);
    }
    
    /**
     * Détermine si l'utilisateur peut ajouter un devis ou une facture au ticket.
     */
    public function addDevisOrFacture(User $user, Ticket $ticket): bool
    {
        // Les techniciens peuvent ajouter des devis/factures aux tickets qui leur sont assignés
        if ($this->isTechnicien($user)) {
            return $ticket->assigned_to === $user->id;
        }
        
        // Les prestataires peuvent ajouter des devis/factures aux tickets qui leur sont assignés
        if ($this->isPrestataire($user)) {
            return $ticket->prestataire_id === $user->id;
        }
        
        // Les administrateurs peuvent ajouter des devis/factures à tous les tickets
        return $this->isAdmin($user);
    }
    
    /**
     * Détermine si l'utilisateur peut ajouter une évaluation au ticket.
     */
    public function addEvaluation(User $user, Ticket $ticket): bool
    {
        // Uniquement l'utilisateur qui a créé le ticket peut l'évaluer, et seulement si résolu ou clôturé
        return $user->id === $ticket->created_by && 
               in_array($ticket->statut, ['resolu', 'cloture']) && 
               $ticket->assigned_to !== null;
    }
}
