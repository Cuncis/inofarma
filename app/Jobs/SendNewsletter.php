<?php

namespace App\Jobs;

use App\Mail\NewsletterMail;
use App\Models\Newsletter;
use App\Models\NewsletterDelivery;
use App\Models\Subscriber;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Sends one newsletter to everyone currently subscribed.
 *
 * Each person gets a delivery row as soon as they are attempted, so a job that
 * is retried (or the queue restarting halfway) skips the people already done
 * instead of mailing them twice. A bad address fails on its own and is logged;
 * it never stops the rest.
 */
class SendNewsletter implements ShouldQueue
{
    use Queueable;

    public int $timeout = 3600;

    public int $tries = 1;

    public function __construct(public int $newsletterId) {}

    public function handle(): void
    {
        $newsletter = Newsletter::find($this->newsletterId);

        if (! $newsletter || ! in_array($newsletter->status, [Newsletter::SENDING], true)) {
            return;
        }

        Subscriber::query()->subscribed()->orderBy('id')->chunkById(100, function ($subscribers) use ($newsletter) {
            foreach ($subscribers as $subscriber) {
                $this->deliver($newsletter, $subscriber);
            }
        });

        $this->finish($newsletter);
    }

    public function failed(Throwable $exception): void
    {
        Newsletter::whereKey($this->newsletterId)->update(['status' => Newsletter::FAILED]);
    }

    private function deliver(Newsletter $newsletter, Subscriber $subscriber): void
    {
        if (NewsletterDelivery::where('newsletter_id', $newsletter->id)->where('subscriber_id', $subscriber->id)->exists()) {
            return;
        }

        // Someone may unsubscribe while a long send is still running.
        if (! $subscriber->fresh()?->isSubscribed()) {
            return;
        }

        try {
            Mail::to($subscriber->email)->send(new NewsletterMail($newsletter, $subscriber));

            NewsletterDelivery::create([
                'newsletter_id' => $newsletter->id,
                'subscriber_id' => $subscriber->id,
                'status' => 'terkirim',
                'sent_at' => now(),
            ]);
        } catch (Throwable $exception) {
            NewsletterDelivery::create([
                'newsletter_id' => $newsletter->id,
                'subscriber_id' => $subscriber->id,
                'status' => 'gagal',
                'error' => mb_substr($exception->getMessage(), 0, 1000),
            ]);
        }
    }

    private function finish(Newsletter $newsletter): void
    {
        $sent = $newsletter->deliveries()->where('status', 'terkirim')->count();
        $failed = $newsletter->deliveries()->where('status', 'gagal')->count();

        $newsletter->update([
            'success_count' => $sent,
            'failed_count' => $failed,
            // "Gagal" only when nothing at all went out; a partly failed send is still Terkirim,
            // with the failure count beside it.
            'status' => $sent === 0 && $failed > 0 ? Newsletter::FAILED : Newsletter::SENT,
            'sent_at' => now(),
        ]);
    }
}
