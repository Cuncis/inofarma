<?php

namespace App\Filament\Resources\Subscribers\Tables;

use App\Models\Subscriber;
use App\Support\AdminOptions;
use App\Support\Newsletters\SubscriberCsv;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SubscribersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state) => $state === Subscriber::SUBSCRIBED ? 'success' : 'gray')
                    ->formatStateUsing(fn (string $state) => AdminOptions::toLabel(AdminOptions::SUBSCRIBER_STATUSES, $state)),
                TextColumn::make('subscribed_at')
                    ->label(__('Tanggal Berlangganan'))
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('unsubscribed_at')
                    ->label(__('Tanggal Berhenti'))
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(AdminOptions::options(AdminOptions::SUBSCRIBER_STATUSES)),
            ])
            ->headerActions([
                Action::make('import')
                    ->label(__('Import CSV'))
                    ->icon(Heroicon::OutlinedArrowUpTray)
                    ->color('gray')
                    ->modalHeading(__('Import Subscriber dari CSV'))
                    ->modalDescription(__('Satu alamat email per baris, atau file dengan kolom "email". Email yang sudah ada dilewati, dan yang pernah berhenti tidak akan didaftarkan ulang.'))
                    ->schema([
                        FileUpload::make('file')
                            ->label(__('File CSV'))
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                            ->storeFiles(false)
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $result = SubscriberCsv::import($data['file']->getRealPath());

                        Notification::make()
                            ->success()
                            ->title(__('Import selesai'))
                            ->body(__(':imported ditambahkan, :duplicates sudah ada, :invalid tidak valid.', $result))
                            ->send();
                    }),
                Action::make('export')
                    ->label(__('Export CSV'))
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('gray')
                    ->action(fn ($livewire) => response()->streamDownload(
                        SubscriberCsv::export($livewire->getFilteredTableQuery()),
                        'subscriber-'.now()->format('Ymd-His').'.csv',
                        ['Content-Type' => 'text/csv'],
                    )),
            ])
            ->recordActions([
                Action::make('unsubscribe')
                    ->label(__('Berhentikan'))
                    ->icon(Heroicon::OutlinedUserMinus)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(__('Berhentikan langganan?'))
                    ->modalDescription(fn (Subscriber $record) => __(':email tidak akan menerima newsletter lagi.', ['email' => $record->email]))
                    ->visible(fn (Subscriber $record) => $record->isSubscribed())
                    ->action(function (Subscriber $record) {
                        $record->unsubscribe();

                        Notification::make()->success()->title(__('Langganan dihentikan.'))->send();
                    }),
            ])
            ->emptyStateHeading(__('Belum ada subscriber.'));
    }
}
