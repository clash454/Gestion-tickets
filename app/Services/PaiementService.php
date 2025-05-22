<?php

namespace App\Services;

use App\Models\Facture;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Illuminate\Support\Facades\Config;
use Exception;

class PaiementService
{
    /**
     * Créer une session de paiement Stripe pour une facture
     *
     * @param \App\Models\Facture $facture Facture à payer
     * @return array Données de la session de paiement
     */
    public function creerSessionPaiement(Facture $facture)
    {
        try {
            // Définir la clé API Stripe
            Stripe::setApiKey(Config::get('services.stripe.secret'));
            
            // Créer une session de paiement
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'eur',
                            'product_data' => [
                                'name' => 'Facture #' . $facture->id,
                                'description' => $facture->description,
                            ],
                            'unit_amount' => $facture->montant * 100, // Conversion en centimes
                        ],
                        'quantity' => 1,
                    ]
                ],
                'mode' => 'payment',
                'success_url' => route('paiement.succes', $facture->id),
                'cancel_url' => route('paiement.cancel', $facture->id),
                'client_reference_id' => $facture->id,
                'metadata' => [
                    'facture_id' => $facture->id,
                    'ticket_id' => $facture->ticket_id,
                ],
            ]);
            
            return [
                'id' => $session->id,
                'url' => $session->url,
            ];
        } catch (Exception $e) {
            Log::error('Erreur lors de la création de la session de paiement: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Traiter un paiement réussi
     *
     * @param int $factureId ID de la facture payée
     * @param string $sessionId ID de la session de paiement Stripe
     * @return \App\Models\Facture
     */
    public function traiterPaiementReussi(int $factureId, string $sessionId)
    {
        $facture = Facture::findOrFail($factureId);
        
        // Mettre à jour le statut de la facture
        $facture->update([
            'statut' => 'payee',
            'reference_paiement' => $sessionId,
            'date_paiement' => now(),
        ]);
        
        // Mettre à jour le statut du ticket associé si nécessaire
        $ticket = $facture->ticket;
        if ($ticket && $ticket->statut === 'en_cours') {
            $ticket->update([
                'statut' => 'resolu',
            ]);
        }
        
        return $facture;
    }
    
    /**
     * Annuler un paiement
     *
     * @param int $factureId ID de la facture
     * @return \App\Models\Facture
     */
    public function annulerPaiement(int $factureId)
    {
        $facture = Facture::findOrFail($factureId);
        
        // Remettre la facture en statut à payer si elle était en cours de paiement
        if ($facture->statut === 'en_cours_paiement') {
            $facture->update([
                'statut' => 'a_payer',
            ]);
        }
        
        return $facture;
    }
    
    /**
     * Récupérer des statistiques de paiement
     *
     * @return array Statistiques de paiement
     */
    public function getStatistiques()
    {
        $totalFactures = Facture::count();
        $totalPayees = Facture::where('statut', 'payee')->count();
        $totalEnAttente = Facture::where('statut', 'a_payer')->count();
        $montantTotal = Facture::where('statut', 'payee')->sum('montant');
        $montantEnAttente = Facture::where('statut', 'a_payer')->sum('montant');
        
        return [
            'total_factures' => $totalFactures,
            'total_payees' => $totalPayees,
            'total_en_attente' => $totalEnAttente,
            'montant_total' => $montantTotal,
            'montant_en_attente' => $montantEnAttente,
        ];
    }
} 