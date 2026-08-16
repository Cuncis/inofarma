<?php

namespace App\Notifications;

use App\Models\Customer;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

/**
 * Not `Illuminate\Auth\Notifications\VerifyEmail` — that notification hard-codes
 * the `verification.verify` route name, which belongs to the `users` guard's
 * (deleted) Breeze scaffold. This builds the same kind of signed, expiring URL
 * but against `ui.verifikasi-email`, the customer-guard equivalent.
 */
class CustomerVerifyEmail extends Notification
{
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(Customer $notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute(
            'ui.verifikasi-email',
            Carbon::now()->addMinutes(60),
            ['id' => $notifiable->getKey(), 'hash' => sha1($notifiable->email)],
        );

        return (new MailMessage)
            ->subject('Verifikasi Alamat Email Anda — Inofarma')
            ->greeting("Halo, {$notifiable->name}!")
            ->line('Klik tombol di bawah untuk memverifikasi alamat email Anda.')
            ->action('Verifikasi Email', $url)
            ->line('Tautan ini berlaku selama 60 menit. Jika Anda tidak mendaftar di Inofarma, abaikan email ini.');
    }
}
