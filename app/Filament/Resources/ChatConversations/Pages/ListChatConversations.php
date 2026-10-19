<?php

namespace App\Filament\Resources\ChatConversations\Pages;

use App\Filament\Resources\ChatConversations\ChatConversationResource;
use App\Support\ChatSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ListChatConversations extends ListRecords
{
    protected static string $resource = ChatConversationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('chatSettings')
                ->label(__('Pengaturan Chat'))
                ->icon(Heroicon::OutlinedCog6Tooth)
                ->color('gray')
                ->visible(fn () => (bool) Auth::guard('web')->user()?->can('Inbox:Balas'))
                ->modalHeading(__('Pengaturan Chat'))
                ->modalDescription(__('Pesan sambutan dan pertanyaan cepat yang tampil di jendela chat toko sebelum pengunjung menulis pesan.'))
                ->fillForm(fn () => [
                    'welcome_message' => ChatSettings::welcomeMessage(),
                    'quick_questions' => ChatSettings::quickQuestions(),
                ])
                ->schema([
                    Textarea::make('welcome_message')
                        ->label(__('Pesan sambutan'))
                        ->required()
                        ->rows(3)
                        ->maxLength(500),
                    Repeater::make('quick_questions')
                        ->label(__('Pertanyaan cepat'))
                        ->helperText(__('Tampil sebagai tombol di atas kolom ketik. Pengunjung cukup menekannya untuk mengirim pertanyaan itu.'))
                        ->schema([
                            Select::make('type')
                                ->label(__('Jenis tombol'))
                                ->options([
                                    ChatSettings::TYPE_MESSAGE => __('Pertanyaan atau tautan'),
                                    ChatSettings::TYPE_AUTO_REPLY => __('Jawaban otomatis (mis. jam buka, info antar)'),
                                    ChatSettings::TYPE_TRACK_ORDER => __('Lacak Pesanan (formulir nomor pesanan dan email)'),
                                    ChatSettings::TYPE_ASK_PRODUCT => __('Cek ketersediaan obat (formulir nama obat)'),
                                    ChatSettings::TYPE_WHATSAPP => __('Chat WhatsApp'),
                                    ChatSettings::TYPE_PHONE => __('Telepon'),
                                ])
                                ->default(ChatSettings::TYPE_MESSAGE)
                                ->native(false)
                                ->selectablePlaceholder(false)
                                ->live(),
                            TextInput::make('question')
                                ->label(__('Pertanyaan'))
                                ->required()
                                ->maxLength(120),
                            TextInput::make('url')
                                ->visible(fn (Get $get) => ($get('type') ?? ChatSettings::TYPE_MESSAGE) === ChatSettings::TYPE_MESSAGE)
                                ->label(__('Tautan (opsional)'))
                                ->helperText(__('Bila diisi, menekan tombol ini membuka halaman tersebut, bukan mengirim pesan. Contoh: /shop atau https://inofarma.com.'))
                                ->placeholder('/shop')
                                ->maxLength(255)
                                ->rule(fn () => function (string $attribute, mixed $value, \Closure $fail) {
                                    if ($value && ! preg_match('#^(/(?!/)|https?://)#i', $value)) {
                                        $fail(__('Tautan harus diawali / atau https://.'));
                                    }
                                }),
                            Textarea::make('answer')
                                ->label(__('Jawaban'))
                                ->helperText(__('Ditampilkan langsung di chat saat tombol ditekan.'))
                                ->visible(fn (Get $get) => $get('type') === ChatSettings::TYPE_AUTO_REPLY)
                                ->required(fn (Get $get) => $get('type') === ChatSettings::TYPE_AUTO_REPLY)
                                ->rows(3)
                                ->maxLength(600),
                            TextInput::make('phone')
                                ->label(__('Nomor telepon / WhatsApp'))
                                ->helperText(__('Boleh diawali 0 atau 62. Contoh: 0812-1401-8964.'))
                                ->tel()
                                ->visible(fn (Get $get) => in_array($get('type'), [ChatSettings::TYPE_WHATSAPP, ChatSettings::TYPE_PHONE], true))
                                ->required(fn (Get $get) => in_array($get('type'), [ChatSettings::TYPE_WHATSAPP, ChatSettings::TYPE_PHONE], true))
                                ->maxLength(20),
                        ])
                        ->addActionLabel(__('Tambah pertanyaan'))
                        ->maxItems(ChatSettings::MAX_QUESTIONS)
                        ->reorderable()
                        ->defaultItems(0),
                ])
                ->action(function (array $data) {
                    ChatSettings::save(
                        $data['welcome_message'],
                        $data['quick_questions'] ?? [],
                    );

                    Notification::make()->success()->title(__('Pengaturan chat disimpan.'))->send();
                }),
        ];
    }
}
