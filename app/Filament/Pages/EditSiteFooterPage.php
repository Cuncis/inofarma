<?php

namespace App\Filament\Pages;

use App\Support\SiteFooterContent;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Konten Website > Footer & Perusahaan: the desktop site footer and the
 * company details that close the Syarat & Ketentuan and Kebijakan Privasi popups.
 */
class EditSiteFooterPage extends ContentEditorPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?int $navigationSort = 7;

    protected static ?string $slug = 'konten-website/footer';

    public static function getNavigationLabel(): string
    {
        return __('Footer & Perusahaan');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Konten Footer & Perusahaan');
    }

    public static function description(): string
    {
        return __('Teks dan tautan di footer setiap halaman (desktop), serta data perusahaan di dokumen legal.');
    }

    public static function storefrontUrl(): ?string
    {
        return route('home');
    }

    protected function currentContent(): array
    {
        return SiteFooterContent::all();
    }

    protected function persist(array $state): void
    {
        SiteFooterContent::save($state);
    }

    protected function editorComponents(): array
    {
        return [
            Section::make(__('Footer'))
                ->description(__('Tampil di bagian bawah setiap halaman versi desktop. Kolom pendaftaran newsletter tetap ada di sebelahnya.'))
                ->columns(['default' => 1, 'lg' => 2])
                ->schema([
                    TextInput::make('aboutTitle')->label(__('Judul kolom tentang'))->required()->maxLength(80),
                    TextInput::make('menuTitle')->label(__('Judul kolom menu'))->required()->maxLength(80),
                    Textarea::make('aboutText')->label(__('Teks tentang'))->required()->rows(3)->maxLength(500)->columnSpanFull(),
                    Repeater::make('links')
                        ->label(__('Tautan menu'))
                        ->schema([
                            TextInput::make('label')->label(__('Teks'))->required()->maxLength(60),
                            TextInput::make('href')
                                ->label(__('Tautan'))
                                ->placeholder('/shop')
                                ->required()
                                ->maxLength(500)
                                ->regex('#^(/|https?://)#'),
                        ])
                        ->columns(2)
                        ->itemLabel(fn (array $state) => $state['label'] ?? null)
                        ->addActionLabel(__('Tambah tautan'))
                        ->reorderable()
                        ->maxItems(SiteFooterContent::MAX_LINKS)
                        ->columnSpanFull(),
                    TextInput::make('copyright')
                        ->label(__('Teks hak cipta'))
                        ->prefix('© '.now()->year)
                        ->helperText(__('Tahun diisi otomatis.'))
                        ->required()
                        ->maxLength(120)
                        ->columnSpanFull(),
                ]),
            Section::make(__('Data perusahaan'))
                ->description(__('Tampil di akhir Syarat & Ketentuan dan Kebijakan Privasi.'))
                ->columns(['default' => 1, 'lg' => 2])
                ->schema([
                    TextInput::make('company.name')->label(__('Nama perusahaan'))->required()->maxLength(120),
                    TextInput::make('company.email')->label(__('Email'))->email()->required()->maxLength(120),
                    TextInput::make('company.phone')->label(__('Telepon'))->required()->maxLength(40),
                    Textarea::make('company.address')->label(__('Alamat'))->required()->rows(3)->maxLength(500)->columnSpanFull(),
                ]),
        ];
    }
}
