<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Contracts\View\View;

/**
 * The page behind every newsletter's "Berhenti berlangganan" link.
 *
 * Opening the link only shows a confirmation; the opt-out itself is a POST.
 * Mail scanners and link previewers open links automatically, and they must
 * not be able to unsubscribe people by accident. The POST is also what mail
 * apps call for their one-click unsubscribe button (List-Unsubscribe-Post).
 */
class NewsletterUnsubscribeController extends Controller
{
    public function show(string $token): View
    {
        $subscriber = Subscriber::where('unsubscribe_token', $token)->firstOrFail();

        return view('newsletter-unsubscribe', ['subscriber' => $subscriber, 'done' => ! $subscriber->isSubscribed()]);
    }

    public function destroy(string $token): View
    {
        $subscriber = Subscriber::where('unsubscribe_token', $token)->firstOrFail();

        $subscriber->unsubscribe();

        return view('newsletter-unsubscribe', ['subscriber' => $subscriber, 'done' => true]);
    }
}
