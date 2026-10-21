<?php

namespace App\Filament\Pages;

use App\Support\DocumentPageContent;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Editor for one of the document pages in {@see DocumentPageContent}. A plain
 * page is a list of titled sections; a legal document (a popup) has sections
 * of clauses, numbered automatically, and a "Terakhir diperbarui" date set
 * when its text changes. Each document is a small subclass naming it.
 */
abstract class EditDocumentPage extends ContentEditorPage
{
    /** One of {@see DocumentPageContent::DOCUMENTS}. */
    abstract protected static function document(): string;

    public function getTitle(): string|Htmlable
    {
        return __('Konten :page', ['page' => static::getNavigationLabel()]);
    }

    protected function currentContent(): array
    {
        return DocumentPageContent::all(static::document());
    }

    protected function persist(array $state): void
    {
        DocumentPageContent::save(static::document(), $state);
    }

    protected function editorComponents(): array
    {
        $legal = DocumentPageContent::isLegal(static::document());

        return [
            Section::make()
                ->columns(['default' => 1, 'lg' => 2])
                ->schema([
                    TextInput::make('title')->label(__('Judul'))->required()->maxLength(120),
                    ...($legal ? [
                        Text::make(fn () => __('Terakhir diperbarui: :date. Diisi otomatis dengan bulan ini setiap kali isi dokumen berubah.', [
                            'date' => DocumentPageContent::all(static::document())['updatedAt'],
                        ])),
                        Textarea::make('contactHeading')
                            ->label(__('Kalimat sebelum kontak'))
                            ->helperText(__('Tampil di akhir dokumen, di atas data perusahaan (diubah di Footer & Perusahaan).'))
                            ->required()
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ] : []),
                ]),
            $legal ? $this->legalSections() : $this->sections(),
        ];
    }

    private function sections(): Repeater
    {
        return Repeater::make('sections')
            ->label(__('Bagian'))
            ->schema([
                TextInput::make('title')->label(__('Judul bagian'))->required()->maxLength(160),
                Textarea::make('body')
                    ->label(__('Isi'))
                    ->helperText(__('Pisahkan paragraf dengan satu baris kosong.'))
                    ->required()
                    ->rows(5)
                    ->maxLength(5000),
            ])
            ->grid(['default' => 1, 'xl' => 2])
            ->itemLabel(fn (array $state) => $state['title'] ?? null)
            ->addActionLabel(__('Tambah bagian'))
            ->reorderable()
            ->collapsible()
            ->collapsed()
            ->minItems(1)
            ->maxItems(30);
    }

    private function legalSections(): Repeater
    {
        return Repeater::make('sections')
            ->label(__('Bagian'))
            ->helperText(__('Nomor bagian (1., 2., ...) dan pasal (1.1, 1.2, ...) dibuat otomatis sesuai urutan, jadi jangan ditulis di judul atau isi.'))
            ->schema([
                TextInput::make('title')->label(__('Judul bagian'))->required()->maxLength(160),
                Repeater::make('clauses')
                    ->label(__('Pasal'))
                    ->schema([
                        Textarea::make('text')->label(__('Isi pasal'))->required()->rows(3)->maxLength(3000),
                        Textarea::make('items')
                            ->label(__('Poin-poin (opsional)'))
                            ->helperText(__('Satu poin per baris. Tampil sebagai daftar di bawah isi pasal.'))
                            ->rows(3)
                            ->maxLength(5000),
                    ])
                    ->itemNumbers()
                    ->addActionLabel(__('Tambah pasal'))
                    ->reorderable()
                    ->collapsible()
                    ->minItems(1)
                    ->maxItems(40),
            ])
            ->itemNumbers()
            ->itemLabel(fn (array $state) => $state['title'] ?? null)
            ->addActionLabel(__('Tambah bagian'))
            ->reorderable()
            ->collapsible()
            ->collapsed()
            ->minItems(1)
            ->maxItems(40);
    }
}
