<?php

namespace App\Notifications;

use App\Models\Facture;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FacturePayee extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * La facture qui a été payée
     */
    public $facture;

    /**
     * Create a new notification instance.
     */
    public function __construct(Facture $facture)
    {
        $this->facture = $facture;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Facture payée')
            ->line("La facture #{$this->facture->numero_facture} a été réglée.")
            ->action('Voir la facture', url('/admin'))
            ->line('Merci pour votre collaboration.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'facture_id' => $this->facture->id,
            'montant' => $this->facture->montant,
            'ticket_id' => $this->facture->ticket_id,
        ];
    }
}
