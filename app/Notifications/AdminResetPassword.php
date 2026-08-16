<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Laravel's stock `Illuminate\Auth\Notifications\ResetPassword` hard-codes
 * the route name `password.reset`, which belongs to neither guard here — the
 * admin's reset link is `admin.atur-ulang-sandi`. See `CustomerResetPassword`
 * for the storefront's equivalent.
 */
class AdminResetPassword extends Notification
{
    public function __construct(private readonly string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $url = route('admin.atur-ulang-sandi', ['token' => $this->token, 'email' => $notifiable->email]);

        return (new MailMessage)
            ->subject('Pemulihan Kata Sandi — Inofarma Admin')
            ->greeting("Halo, {$notifiable->name}!")
            ->line('Klik tombol di bawah untuk mengatur ulang kata sandi Anda.')
            ->action('Atur Ulang Kata Sandi', $url)
            ->line('Tautan ini berlaku selama 60 menit. Jika Anda tidak meminta ini, abaikan email ini.');
    }
}
