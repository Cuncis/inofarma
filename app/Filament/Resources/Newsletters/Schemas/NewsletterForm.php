<?php

namespace App\Filament\Resources\Newsletters\Schemas;

use App\Filament\RichContent\ButtonBlock;
use App\Models\Newsletter;
use App\Support\Newsletters\NewsletterRenderer;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class NewsletterForm
{
    public static function configure(Schema $schema): Schema
    {
        $locked = fn (?Newsletter $record) => $record !== null && ! $record->isDraft();

        return $schema
            ->components([
                Section::make(__('Kampanye'))
                    ->description(fn (?Newsletter $record) => $locked($record)
                        ? __('Newsletter ini sudah dikirim, jadi tidak bisa diubah lagi.')
                        : null)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Nama Kampanye (internal)'))
                            ->helperText(__('Hanya terlihat oleh tim, tidak dikirim ke pelanggan.'))
                            ->required()
                            ->maxLength(255)
                            ->disabled($locked),
                        TextInput::make('subject')
                            ->label(__('Subjek Email'))
                            ->required()
                            ->maxLength(255)
                            ->disabled($locked),
                        TextInput::make('preview_text')
                            ->label(__('Teks Pratinjau'))
                            ->helperText(__('Kalimat singkat yang tampil di samping subjek di kotak masuk.'))
                            ->maxLength(150)
                            ->disabled($locked),
                    ])
                    ->columns(1),
                Section::make(__('Isi Email'))
                    ->schema([
                        Tabs::make('isi')
                            ->persistTabInQueryString('isi')
                            ->activeTab(fn (?Newsletter $record) => filled($record?->custom_html) ? 2 : 1)
                            ->tabs([
                                Tab::make(__('Editor'))
                                    ->schema([
                                        RichEditor::make('content')
                                            ->hiddenLabel()
                                            ->required(fn (Get $get) => blank($get('custom_html')))
                                            ->toolbarButtons([
                                                ['h2', 'h3'],
                                                ['bold', 'italic'],
                                                ['bulletList', 'orderedList'],
                                                ['link', 'attachFiles', 'customBlocks'],
                                                ['undo', 'redo'],
                                            ])
                                            ->customBlocks([ButtonBlock::class])
                                            ->fileAttachmentsDisk(NewsletterRenderer::ATTACHMENT_DISK)
                                            ->fileAttachmentsDirectory(NewsletterRenderer::ATTACHMENT_DIRECTORY)
                                            ->fileAttachmentsVisibility('public')
                                            ->fileAttachmentsAcceptedFileTypes(['image/png', 'image/jpeg', 'image/webp', 'image/gif'])
                                            ->fileAttachmentsMaxSize(2048)
                                            ->disabled($locked),
                                    ]),
                                Tab::make(__('Custom HTML'))
                                    ->badge(fn (Get $get) => filled($get('custom_html')) ? __('Aktif') : null)
                                    ->schema([
                                        CodeEditor::make('custom_html')
                                            ->hiddenLabel()
                                            ->language(Language::Html)
                                            ->helperText(__('Opsional. Tempel HTML dan CSS email buatan Anda di sini. Jika diisi, ini dipakai menggantikan isi tab Editor. Gunakan CSS inline atau blok <style>; skrip dibuang otomatis. Tautan "Berhenti berlangganan" tetap ditambahkan otomatis di bawah isi Anda.'))
                                            ->disabled($locked),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
