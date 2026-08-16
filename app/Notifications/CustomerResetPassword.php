<?php

namespace App\Notifications;

use App\Models\Customer;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * See `AdminResetPassword`'s docblock — same reasoning, pointed at
 * `ui.new-password` instead.
 */
class CustomerResetPassword extends Notification
{
    public function __construct(private readonly string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(Customer $notifiable): MailMessage
    {
        $url = route('ui.new-password', ['token' => $this->token, 'email' => $notifiable->email]);

        return (new MailMessage)
            ->subject('Pemulihan Kata Sandi — Inofarma')
            ->greeting("Halo, {$notifiable->name}!")
            ->line('Klik tombol di bawah untuk mengatur ulang kata sandi Anda.')
            ->action('Atur Ulang Kata Sandi', $url)
            ->line('Tautan ini berlaku selama 60 menit. Jika Anda tidak meminta ini, abaikan email ini.');
    }
}
