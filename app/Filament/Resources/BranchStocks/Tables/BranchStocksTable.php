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
                    ->label(__('Cabang'))
                    ->searchable(),
                TextColumn::make('product.sku')
                    ->label(__('SKU'))
                    ->searchable(),
                TextColumn::make('product.name')
                    ->label(__('Produk'))
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label(__('Jumlah'))
                    ->alignRight(),
                TextColumn::make('reserved_quantity')
                    ->label(__('Dipesan'))
                    ->alignRight(),
                TextColumn::make('available')
                    ->label(__('Tersedia'))
                    ->alignRight(),
                TextColumn::make('reorder_point')
                    ->label(__('Titik Pesan Ulang'))
                    ->alignRight(),
                IconColumn::make('is_low')
                    ->label(__('Stok Menipis'))
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label(__('Cabang'))
                    ->relationship('branch', 'name'),
            ])
            ->recordActions([
                Action::make('sesuaikan')
                    ->label(__('Sesuaikan'))
                    ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                    ->visible(fn () => Auth::guard('web')->user()?->can('Inventaris:Sesuaikan Stok'))
                    ->schema([
                        TextInput::make('delta')
                            ->label(__('Jumlah Penyesuaian'))
                            ->numeric()
                            ->integer()
                            ->minValue(-100000)
                            ->maxValue(100000)
                            ->rule('not_in:0')
                            ->validationMessages(['not_in' => __('Jumlah penyesuaian tidak boleh nol.')])
                            ->helperText(__('Gunakan angka negatif untuk mengurangi stok.'))
                            ->required(),
                        Select::make('reason')
                            ->label(__('Alasan'))
                            ->options(AdminOptions::options(AdminOptions::ADJUSTMENT_REASONS))
                            ->required(),
                        Textarea::make('note')
                            ->label(__('Catatan'))
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
                            ->title(__('Stok ":name" di :name2 disesuaikan.', ['name' => $record->product->name, 'name2' => $record->branch->name]))
                            ->send();
                    }),
                Action::make('terima')
                    ->label(__('Terima Barang'))
                    ->icon(Heroicon::OutlinedInboxArrowDown)
                    ->visible(fn () => Auth::guard('web')->user()?->can('Inventaris:Terima Barang'))
                    ->schema([
                        TextInput::make('batchNumber')
                            ->label(__('Nomor Batch'))
                            ->required()
                            ->maxLength(60),
                        DatePicker::make('expiresAt')
                            ->label(__('Tanggal Kedaluwarsa'))
                            ->required()
                            ->after('today'),
                        TextInput::make('quantity')
                            ->label(__('Jumlah'))
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(100000)
                            ->required(),
                        TextInput::make('costPrice')
                            ->label(__('Harga Beli'))
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(1000000000),
                        Textarea::make('note')
                            ->label(__('Catatan'))
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
                            ->title(__(':quantity ":name" diterima di :name2.', ['quantity' => $data['quantity'], 'name' => $record->product->name, 'name2' => $record->branch->name]))
                            ->send();
                    }),
            ]);
    }
}
