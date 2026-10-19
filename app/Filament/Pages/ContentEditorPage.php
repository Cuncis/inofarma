<?php

namespace App\Filament\Pages;

use App\Support\AuditLogger;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

/**
 * One storefront page's editor under Konten Website. Handles what every
 * editor shares: the `Konten:Lihat`/`Konten:Ubah` permissions, the full-width
 * form, saving with an audit entry, and the link to the page in the shop.
 * A new editor also gets an entry in {@see WebsiteContent::EDITORS}.
 *
 * @property-read Schema $form
 */
abstract class ContentEditorPage extends Page
{
    protected string $view = 'filament.pages.content-editor';

    /** Full width, so the image grids and repeaters have room. */
    protected Width|string|null $maxContentWidth = Width::Full;

    /** @var array<string, mixed> */
    public ?array $data = [];

    /** One line for this page's card on the Konten Website overview. */
    abstract public static function description(): string;

    /** Where the page is in the shop, or null for content without a page of its own (a popup). */
    abstract public static function storefrontUrl(): ?string;

    /**
     * @return array<string, mixed> the form's state, from what is saved now
     */
    abstract protected function currentContent(): array;

    /**
     * @param  array<string, mixed>  $state  the validated form state
     */
    abstract protected function persist(array $state): void;

    /**
     * @return list<Component>
     */
    abstract protected function editorComponents(): array;

    public static function getNavigationParentItem(): ?string
    {
        return WebsiteContent::getNavigationLabel();
    }

    public static function canAccess(): bool
    {
        return (bool) Auth::guard('web')->user()?->can('Konten:Lihat');
    }

    public static function canEdit(): bool
    {
        return (bool) Auth::guard('web')->user()?->can('Konten:Ubah');
    }

    public function mount(): void
    {
        $this->form->fill($this->currentContent());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->disabled(! static::canEdit())
            ->components($this->editorComponents());
    }

    public function save(): void
    {
        abort_unless(static::canEdit(), 403);

        $this->persist($this->form->getState());

        AuditLogger::log('konten_diubah', null, [], ['halaman' => static::getSlug()]);

        Notification::make()->success()->title(__(':page disimpan.', ['page' => $this->getTitle()]))->send();

        $this->mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')
                ->label(__('Lihat di Toko'))
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->visible(fn () => static::storefrontUrl() !== null)
                ->url(fn () => static::storefrontUrl(), shouldOpenInNewTab: true),
        ];
    }
}
