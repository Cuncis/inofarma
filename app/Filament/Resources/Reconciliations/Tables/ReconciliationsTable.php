<?php

namespace App\Filament\Resources\Reconciliations\Tables;

use App\Models\Payment;
use App\Support\Money;
use App\Support\Payments\DokuPaymentService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class ReconciliationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading(__('Log Pembayaran'))
            ->defaultSort('id', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('order.branch'))
            ->columns([
                TextColumn::make('order.number')
                    ->label(__('No. Pesanan'))
                    ->searchable(),
                TextColumn::make('order.branch.name')
                    ->label(__('Cabang')),
                TextColumn::make('channel')
                    ->label(__('Kanal'))
                    ->placeholder('—'),
                TextColumn::make('amount')
                    ->label(__('Jumlah'))
                    ->alignRight()
                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => __(ucfirst($state)))
                    ->color(fn (string $state) => match ($state) {
                        'success' => 'success',
                        'pending' => 'warning',
                        'refunded' => 'info',
                        default => 'danger',
                    }),
                TextColumn::make('created_at')
                    ->label(__('Waktu'))
                    ->dateTime('d M Y, H:i'),
            ])
            ->recordActions([
                // Only a still-open attempt has anything to learn from DOKU —
                // success/expired/refunded are already final.
                Action::make('cekStatus')
                    ->label(__('Cek Status'))
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->visible(fn (Payment $record) => $record->status === 'pending'
                        && Auth::guard('web')->user()?->can('Pesanan:Proses'))
                    ->action(function (Payment $record) {
                        try {
                            $result = DokuPaymentService::make()->reconcile($record);
                        } catch (RuntimeException $exception) {
                            report($exception);

                            Notification::make()
                                ->danger()
                                ->title(__('Gagal menghubungi DOKU. Coba lagi sebentar lagi.'))
                                ->send();

                            return;
                        }

                        if (! $result) {
                            Notification::make()
                                ->danger()
                                ->title(__('DOKU tidak mengenali pembayaran ini.'))
                                ->send();

                            return;
                        }

                        if ($result->status === 'pending') {
                            Notification::make()
                                ->danger()
                                ->title(__('Menurut DOKU, pembayaran #:invoice_number masih menunggu pembayaran.', ['invoice_number' => $record->invoice_number]))
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->success()
                            ->title(__('Status pembayaran #:invoice_number diperbarui: :status.', ['invoice_number' => $record->invoice_number, 'status' => $result->status]))
                            ->send();
                    }),
            ]);
    }
}
