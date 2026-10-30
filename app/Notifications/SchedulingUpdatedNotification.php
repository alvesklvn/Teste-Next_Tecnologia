<?php

namespace App\Notifications;

use App\Models\Scheduling;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class SchedulingUpdatedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private Scheduling $scheduling)
    {
        
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
        $date = Carbon::parse($this->scheduling->start_date)->format('d/m/Y');
        $start_time = Carbon::parse($this->scheduling->start_date)->format('H:i');
        $end_time = Carbon::parse($this->scheduling->end_date)->format('H:i');
        return (new MailMessage)
            ->subject('Um agendamento foi atualizado')
            ->greeting("Olá, {$notifiable->name}")
            ->line("Você está associado a um agendamento que foi atualizado.")
            ->line(new HtmlString("<strong>Cliente:</strong> {$this->scheduling->client->user->name}"))
            ->line(new HtmlString("<strong>Data:</strong> $date"))
            ->line(new HtmlString("<strong>Horário:</strong> <i>$start_time - $end_time</i>"));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
