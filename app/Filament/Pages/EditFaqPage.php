<?php

namespace App\Filament\Pages;

use App\Support\FaqContent;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Konten Website > FAQ: the questions and answers on the storefront FAQ page.
 */
class EditFaqPage extends ContentEditorPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'konten-website/faq';

    public static function getNavigationLabel(): string
    {
        return __('FAQ');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Konten FAQ');
    }

    public static function description(): string
    {
        return __('Pertanyaan yang sering diajukan beserta jawabannya.');
    }

    public static function storefrontUrl(): ?string
    {
        return route('ui.faq');
    }

    protected function currentContent(): array
    {
        return ['items' => FaqContent::all()];
    }

    protected function persist(array $state): void
    {
        FaqContent::save($state['items'] ?? []);
    }

    protected function editorComponents(): array
    {
        return [
            Repeater::make('items')
                ->hiddenLabel()
                ->helperText(__('Pertanyaan pertama tampil terbuka. Seret untuk mengubah urutan.'))
                ->schema([
                    TextInput::make('question')->label(__('Pertanyaan'))->required()->maxLength(200),
                    Textarea::make('answer')
                        ->label(__('Jawaban'))
                        ->helperText(__('Pisahkan paragraf dengan satu baris kosong.'))
                        ->required()
                        ->rows(4)
                        ->maxLength(3000),
                ])
                ->grid(['default' => 1, 'xl' => 2])
                ->itemLabel(fn (array $state) => $state['question'] ?? null)
                ->addActionLabel(__('Tambah pertanyaan'))
                ->reorderable()
                ->collapsible()
                ->minItems(1)
                ->maxItems(FaqContent::MAX_QUESTIONS),
        ];
    }
}
