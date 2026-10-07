<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * The footer's "Berlangganan" form. Anyone can sign up with just an email.
 *
 * Someone who was already on the list gets the same friendly answer as a new
 * sign-up, so the form can't be used to find out who is subscribed. Someone
 * who earlier unsubscribed and now signs up again is choosing to come back,
 * which is consent, so they are re-subscribed (unlike a CSV import, which can
 * never do that).
 */
class NewsletterSubscribeController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ], [
            'email.required' => 'Masukkan alamat email Anda.',
            'email.email' => 'Alamat email tidak valid.',
        ]);

        $email = Str::lower(trim($data['email']));
        $subscriber = Subscriber::where('email', $email)->first();

        if (! $subscriber) {
            Subscriber::create(['email' => $email, 'status' => Subscriber::SUBSCRIBED]);
        } elseif (! $subscriber->isSubscribed()) {
            $subscriber->update([
                'status' => Subscriber::SUBSCRIBED,
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ]);
        }

        return back()->with('success', 'Terima kasih! Anda sudah berlangganan info sehat dan hemat dari Inofarma.');
    }
}
