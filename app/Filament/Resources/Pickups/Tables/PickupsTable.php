<?php

namespace App\Filament\Resources\Pickups\Tables;

use App\Models\Order;
use App\Support\Pickup\PickupCodeException;
use App\Support\Pickup\PickupCodeService;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PickupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with(['customer', 'branch'])
                ->where('status', 'siap diambil')
                ->orderBy('ready_at'))
            ->columns([
                TextColumn::make('number')
                    ->label(__('No. Pesanan'))
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->label(__('Pelanggan'))
                    ->searchable(),
                TextColumn::make('branch.name')
                    ->label(__('Cabang')),
                TextColumn::make('ready_at')
                    ->label(__('Siap Sejak'))
                    ->dateTime('d M Y, H:i'),
                TextColumn::make('pickup_code_expires_at')
                    ->label(__('Berlaku Sampai'))
                    ->dateTime('d M Y, H:i'),
            ])
            ->recordActions([
                Action::make('serahkan')
                    ->label(__('Serahkan'))
                    ->icon(Heroicon::OutlinedCheckBadge)
                    ->schema([
                        TextInput::make('code')
                            ->label(__('Kode Ambil'))
                            ->required()
                            ->maxLength(10),
                    ])
                    ->fillForm(fn ($livewire) => ['code' => $livewire->prefillCode ?? null])
                    ->action(function (array $data, Order $record) {
                        try {
                            PickupCodeService::handOver($record, $data['code'], Auth::guard('web')->user());
                        } catch (PickupCodeException $exception) {
                            Notification::make()->danger()->title($exception->getMessage())->send();

                            return;
                        }

                        Notification::make()
                            ->success()
                            ->title(__('Pesanan #:number berhasil diserahkan.', ['number' => $record->number]))
                            ->send();
                    }),
            ])
            ->emptyStateHeading(__('Tidak ada pesanan yang siap diambil.'));
    }
}
