<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facture;
use App\Notifications\FacturePayee;
use App\Services\PaiementService;

class PaiementController extends Controller
{
    protected $paiementService;
    
    /**
     * Constructeur du contrôleur
     * 
     * @param \App\Services\PaiementService $paiementService
     */
    public function __construct(PaiementService $paiementService)
    {
        $this->middleware('auth');
        $this->paiementService = $paiementService;
    }

    /**
     * Afficher la page de paiement pour une facture
     * 
     * @param \App\Models\Facture $facture
     * @return \Illuminate\Http\RedirectResponse
     */
    public function payer(Facture $facture)
    {
        try {
            // Vérifier que l'utilisateur peut payer cette facture
            $this->authorize('payer', $facture);
            
            // Mettre à jour le statut de la facture
            $facture->update([
                'statut' => 'en_cours_paiement'
            ]);
            
            // Créer une session de paiement Stripe
            $sessionData = $this->paiementService->creerSessionPaiement($facture);
            
            // Rediriger vers la page de paiement Stripe
            return redirect($sessionData['url']);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la préparation du paiement: ' . $e->getMessage());
        }
    }

    /**
     * Traiter le succès d'un paiement
     * 
     * @param \App\Models\Facture $facture
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function succes(Facture $facture, Request $request)
    {
        try {
            // Récupérer la session ID de Stripe
            $sessionId = $request->query('session_id');
            
            // Traiter le paiement réussi
            $facture = $this->paiementService->traiterPaiementReussi($facture->id, $sessionId);
            
            // Envoyer une notification par email
            if ($facture->user) {
                $facture->user->notify(new FacturePayee($facture));
            }

            return redirect()->route('tickets.show', $facture->ticket_id)
                ->with('success', 'Paiement réussi. Merci pour votre règlement.');
        } catch (\Exception $e) {
            return redirect()->route('tickets.show', $facture->ticket_id)
                ->with('error', 'Erreur lors du traitement du paiement: ' . $e->getMessage());
        }
    }

    /**
     * Traiter l'annulation d'un paiement
     * 
     * @param \App\Models\Facture $facture
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(Facture $facture)
    {
        try {
            // Annuler le paiement
            $this->paiementService->annulerPaiement($facture->id);

            return redirect()->route('tickets.show', $facture->ticket_id)
                ->with('info', 'Paiement annulé.');
        } catch (\Exception $e) {
            return redirect()->route('tickets.show', $facture->ticket_id)
                ->with('error', 'Erreur lors de l\'annulation du paiement: ' . $e->getMessage());
        }
    }
} 