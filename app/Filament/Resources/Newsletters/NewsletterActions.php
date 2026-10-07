<?php

namespace App\Filament\Resources\Newsletters;

use App\Jobs\SendNewsletter;
use App\Mail\NewsletterMail;
use App\Models\Newsletter;
use App\Models\Subscriber;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\TagsInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;

/**
 * Preview, test email and send, shared by the edit page and the list rows.
 * Each takes a closure that returns the newsletter to act on, so the edit
 * page can hand over its unsaved edits while a list row hands over the record.
 */
class NewsletterActions
{
    /**
     * @param  Closure  $newsletter  Resolves to the Newsletter; may ask for `$record`.
     */
    public static function preview(Closure $newsletter): Action
    {
        return Action::make('preview')
            ->label(__('Pratinjau'))
            ->icon(Heroicon::OutlinedEye)
            ->color('gray')
            ->modalHeading(__('Pratinjau Newsletter'))
            ->modalWidth('4xl')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel(__('Kembali Mengedit'))
            ->modalContent(function (Action $action) use ($newsletter) {
                $html = (new NewsletterMail($action->evaluate($newsletter), new Subscriber(['unsubscribe_token' => 'pratinjau'])))
                    ->render();

                return new HtmlString('<iframe sandbox="" class="h-[70vh] w-full rounded-lg border border-gray-200" srcdoc="'.e($html).'"></iframe>');
            });
    }

    /**
     * @param  Closure  $newsletter  Resolves to the Newsletter; may ask for `$record`.
     */
    public static function sendTest(Closure $newsletter): Action
    {
        return Action::make('sendTest')
            ->label(__('Kirim Email Uji'))
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->color('gray')
            ->modalHeading(__('Kirim Email Uji'))
            ->modalDescription(__('Email uji dikirim hanya ke alamat di bawah, bukan ke pelanggan.'))
            ->modalSubmitActionLabel(__('Kirim'))
            ->schema([
                TagsInput::make('emails')
                    ->label(__('Alamat Email'))
                    ->helperText(__('Tekan Enter setelah setiap alamat.'))
                    ->placeholder('nama@email.com')
                    ->nestedRecursiveRules(['email'])
                    ->required(),
            ])
            ->action(function (array $data, Action $action) use ($newsletter) {
                $record = $action->evaluate($newsletter);

                foreach ($data['emails'] as $email) {
                    $testRecipient = new Subscriber(['email' => $email, 'unsubscribe_token' => 'uji-coba']);

                    Mail::to($email)->send(new NewsletterMail($record, $testRecipient, isTest: true));
                }

                Notification::make()
                    ->success()
                    ->title(__('Email uji dikirim ke :count alamat.', ['count' => count($data['emails'])]))
                    ->send();
            });
    }

    /**
     * @param  (Closure(): mixed)|null  $beforeSend  Runs first, e.g. to save the form so the send uses what is on screen.
     */
    public static function send(?Closure $beforeSend = null): Action
    {
        return Action::make('send')
            ->label(__('Kirim ke Pelanggan'))
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->color('primary')
            ->visible(fn (Newsletter $record) => $record->isDraft())
            ->requiresConfirmation()
            ->modalHeading(__('Kirim newsletter sekarang?'))
            ->modalDescription(fn () => __('Newsletter akan dikirim ke :count pelanggan yang berlangganan. Setelah dikirim, tindakan ini tidak bisa dibatalkan.', [
                'count' => number_format(Subscriber::query()->subscribed()->count(), 0, ',', '.'),
            ]))
            ->modalSubmitActionLabel(__('Ya, Kirim Sekarang'))
            ->action(function (Newsletter $record) use ($beforeSend) {
                if ($beforeSend) {
                    $beforeSend();
                    $record->refresh();
                }

                $recipients = Subscriber::query()->subscribed()->count();

                if ($recipients === 0) {
                    Notification::make()->danger()->title(__('Belum ada pelanggan yang berlangganan.'))->send();

                    return;
                }

                // Moving out of "draf" first is what stops a double click from sending twice.
                $claimed = Newsletter::whereKey($record->id)
                    ->where('status', Newsletter::DRAFT)
                    ->update(['status' => Newsletter::SENDING, 'recipient_count' => $recipients]);

                if (! $claimed) {
                    return;
                }

                SendNewsletter::dispatch($record->id);

                Notification::make()
                    ->success()
                    ->title(__('Newsletter sedang dikirim ke :count pelanggan.', ['count' => $recipients]))
                    ->send();
            });
    }
}
