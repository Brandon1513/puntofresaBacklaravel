<?php

namespace App\Notifications;

use App\Models\Expense;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ExpenseSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(public Expense $expense) {}

    /**
     * Canales de entrega.
     */
    public function via(object $notifiable): array
    {
        // Por ahora mail; si quieres también 'database'
        return ['mail'];
    }

    /**
     * Contenido del correo.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nuevo gasto pendiente de revisión')
            ->greeting('Hola '.$notifiable->name.',')
            ->line('Se ha registrado un nuevo gasto por parte de: '.$this->expense->creator?->name)
            ->line('Monto: $'.number_format($this->expense->monto, 2))
            ->line('Categoría: '.($this->expense->category?->nombre ?? 'Sin categoría'))
            ->line('Centro de costo: '.($this->expense->costCenter?->nombre ?? 'Sin centro de costo'))
            ->action('Revisar gasto', route('expenses.show', $this->expense)) // 👈 AQUÍ
            ->line('Por favor revisa y aprueba o rechaza este gasto.');
    }


    /**
     * (opcional) para canal database.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'expense_id' => $this->expense->id,
            'monto'      => $this->expense->monto,
            'creado_por' => $this->expense->creator?->name,
            'url'        => route('expenses.show', $this->expense),
        ];
    }

}
