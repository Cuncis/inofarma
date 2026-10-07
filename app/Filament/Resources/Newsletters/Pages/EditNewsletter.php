<?php

namespace App\Filament\Resources\Newsletters\Pages;

use App\Filament\Resources\Newsletters\NewsletterActions;
use App\Filament\Resources\Newsletters\NewsletterResource;
use App\Models\Newsletter;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNewsletter extends EditRecord
{
    protected static string $resource = NewsletterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            NewsletterActions::preview(fn () => $this->previewRecord()),
            NewsletterActions::sendTest(fn () => $this->previewRecord()),
            NewsletterActions::send(beforeSend: fn () => $this->save(shouldRedirect: false)),
            DeleteAction::make()->visible(fn (Newsletter $record) => $record->isDraft()),
        ];
    }

    /**
     * The newsletter as it is on screen right now, unsaved edits included, so
     * a preview or test email shows what the editor sees, not the last save.
     */
    private function previewRecord(): Newsletter
    {
        $record = $this->getRecord()->replicate();
        $record->forceFill($this->form->getState());

        return $record;
    }
}
