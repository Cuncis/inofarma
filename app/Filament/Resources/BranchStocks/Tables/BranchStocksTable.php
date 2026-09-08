<?php

namespace App\Filament\Resources\BranchStocks\Tables;

use App\Models\BranchStock;
use App\Support\AdminOptions;
use App\Support\Inventory\InsufficientStockException;
use App\Support\Inventory\StockAdjuster;
use App\Support\Inventory\StockAllocator;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class BranchStocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['branch', 'product'])->whereHas('product'))
            ->defaultSort('quantity')
            ->columns([
                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable(),
                TextColumn::make('product.sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->alignRight(),
                TextColumn::make('reserved_quantity')
                    ->label('Dipesan')
                    ->alignRight(),
                TextColumn::make('available')
                    ->label('Tersedia')
                    ->alignRight(),
                TextColumn::make('reorder_point')
                    ->label('Titik Pesan Ulang')
                    ->alignRight(),
                IconColumn::make('is_low')
                    ->label('Stok Menipis')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('Cabang')
                    ->relationship('branch', 'name'),
            ])
            ->recordActions([
                Action::make('sesuaikan')
                    ->label('Sesuaikan')
                    ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                    ->visible(fn () => Auth::guard('web')->user()?->can('Inventaris:Sesuaikan Stok'))
                    ->schema([
                        TextInput::make('delta')
                            ->label('Jumlah Penyesuaian')
                            ->numeric()
                            ->integer()
                            ->minValue(-100000)
                            ->maxValue(100000)
                            ->rule('not_in:0')
                            ->validationMessages(['not_in' => 'Jumlah penyesuaian tidak boleh nol.'])
                            ->helperText('Gunakan angka negatif untuk mengurangi stok.')
                            ->required(),
                        Select::make('reason')
                            ->label('Alasan')
                            ->options(array_flip(AdminOptions::ADJUSTMENT_REASONS))
                            ->required(),
                        Textarea::make('note')
                            ->label('Catatan')
                            ->maxLength(255),
                    ])
                    ->action(function (array $data, BranchStock $record) {
                        try {
                            (new StockAdjuster)->adjust(
                                $record->branch,
                                $record->product,
                                (int) $data['delta'],
                                $data['reason'],
                                Auth::guard('web')->id(),
                                $data['note'] ?? null,
                            );
                        } catch (InsufficientStockException $exception) {
                            Notification::make()->danger()->title($exception->getMessage())->send();

                            return;
                        }

                        Notification::make()
                            ->success()
                            ->title("Stok \"{$record->product->name}\" di {$record->branch->name} disesuaikan.")
                            ->send();
                    }),
                Action::make('terima')
                    ->label('Terima Barang')
                    ->icon(Heroicon::OutlinedInboxArrowDown)
                    ->visible(fn () => Auth::guard('web')->user()?->can('Inventaris:Terima Barang'))
                    ->schema([
                        TextInput::make('batchNumber')
                            ->label('Nomor Batch')
                            ->required()
                            ->maxLength(60),
                        DatePicker::make('expiresAt')
                            ->label('Tanggal Kedaluwarsa')
                            ->required()
                            ->after('today'),
                        TextInput::make('quantity')
                            ->label('Jumlah')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(100000)
                            ->required(),
                        TextInput::make('costPrice')
                            ->label('Harga Beli')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(1000000000),
                        Textarea::make('note')
                            ->label('Catatan')
                            ->maxLength(255),
                    ])
                    ->action(function (array $data, BranchStock $record) {
                        (new StockAllocator)->receive(
                            $record->branch,
                            $record->product,
                            [[
                                'batch_number' => $data['batchNumber'],
                                'expires_at' => $data['expiresAt'],
                                'quantity' => $data['quantity'],
                                'cost_price' => $data['costPrice'] ?? null,
                            ]],
                            'pembelian',
                            null,
                            Auth::guard('web')->id(),
                            $data['note'] ?? null,
                        );

                        Notification::make()
                            ->success()
                            ->title("{$data['quantity']} \"{$record->product->name}\" diterima di {$record->branch->name}.")
                            ->send();
                    }),
            ]);
    }
}
