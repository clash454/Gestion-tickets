<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NouveauTicket extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Le ticket qui a été créé
     */
    public $ticket;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nouveau ticket: ' . $this->ticket->titre)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Un nouveau ticket a été créé et nécessite votre attention.')
            ->line('Titre: ' . $this->ticket->titre)
            ->line('Catégorie: ' . $this->ticket->category->nom)
            ->action('Voir le ticket', url('/tickets/' . $this->ticket->id))
            ->line('Merci de traiter ce ticket dans les plus brefs délais.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'titre' => $this->ticket->titre,
            'category_id' => $this->ticket->category_id,
            'created_by' => $this->ticket->created_by,
        ];
    }
} 