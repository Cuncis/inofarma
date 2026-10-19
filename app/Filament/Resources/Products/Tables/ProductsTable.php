<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use App\Support\AdminOptions;
use App\Support\AuditLogger;
use App\Support\Money;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id')
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with(['category', 'supplier'])
                ->withSum('stocks', 'quantity'))
            ->columns([
                TextColumn::make('sku')
                    ->label(__('SKU'))
                    ->searchable(),
                TextColumn::make('name')
                    ->label(__('Nama'))
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label(__('Kategori')),
                TextColumn::make('price')
                    ->label(__('Harga'))
                    ->alignRight()
                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
                TextColumn::make('stocks_sum_quantity')
                    ->label(__('Stok'))
                    ->alignRight()
                    ->state(fn (Product $record) => (int) $record->stocks_sum_quantity)
                    ->badge()
                    ->color(fn (Product $record) => match (true) {
                        (int) $record->stocks_sum_quantity <= 0 => 'danger',
                        (int) $record->stocks_sum_quantity <= 20 => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('max_qty_per_order')
                    ->label(__('Maks. Beli'))
                    ->alignRight()
                    ->state(fn (Product $record) => $record->purchaseLimit())
                    ->tooltip(__(Product::OVER_LIMIT_MESSAGE))
                    ->toggleable(),
                TextColumn::make('sold_count')
                    ->label(__('Terjual'))
                    ->alignRight(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => AdminOptions::toLabel(AdminOptions::PRODUCT_STATUSES, $state))
                    ->color(fn (string $state) => match ($state) {
                        'aktif' => 'success',
                        'arsip' => 'gray',
                        default => 'danger',
                    }),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label(__('Kategori'))
                    ->relationship('category', 'name'),
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(AdminOptions::options(AdminOptions::PRODUCT_STATUSES)),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->after(fn (Product $record) => AuditLogger::log('produk_dihapus', null, ['name' => $record->name, 'sku' => $record->sku])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::purchaseLimitBulkAction(),
                    self::statusBulkAction(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Sets the per-order purchase limit on every selected product. A shopper
     * who goes over it sees {@see Product::OVER_LIMIT_MESSAGE}.
     */
    private static function purchaseLimitBulkAction(): BulkAction
    {
        return BulkAction::make('setPurchaseLimit')
            ->label(__('Ubah Maks. Pembelian'))
            ->icon(Heroicon::OutlinedShoppingCart)
            ->visible(fn () => (bool) Auth::guard('web')->user()?->can('Produk:Ubah'))
            ->modalDescription(__('Pembeli yang melewati batas ini melihat pesan: ":message".', ['message' => __(Product::OVER_LIMIT_MESSAGE)]))
            ->schema([
                TextInput::make('max_qty_per_order')
                    ->label(__('Maks. pembelian per transaksi'))
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->maxValue(65535)
                    ->default(Product::MAX_QUANTITY_PER_ORDER)
                    ->required(),
            ])
            ->action(function (Collection $records, array $data) {
                Product::withTrashed()->whereKey($records->modelKeys())->update(['max_qty_per_order' => (int) $data['max_qty_per_order']]);

                Notification::make()->success()->title(__(':count produk diperbarui.', ['count' => $records->count()]))->send();
            })
            ->deselectRecordsAfterCompletion();
    }

    /**
     * Sets the status on every selected product. Status is an audited field,
     * so each product that actually changes gets its own `produk_diubah` entry.
     */
    private static function statusBulkAction(): BulkAction
    {
        return BulkAction::make('setStatus')
            ->label(__('Ubah Status'))
            ->icon(Heroicon::OutlinedCheckCircle)
            ->visible(fn () => (bool) Auth::guard('web')->user()?->can('Produk:Ubah'))
            ->schema([
                Select::make('status')
                    ->label(__('Status'))
                    ->options(AdminOptions::options(AdminOptions::PRODUCT_STATUSES))
                    ->required(),
            ])
            ->action(function (Collection $records, array $data) {
                $records
                    ->reject(fn (Product $product) => $product->status === $data['status'])
                    ->each(function (Product $product) use ($data) {
                        $before = $product->status;
                        $product->update(['status' => $data['status']]);

                        AuditLogger::log('produk_diubah', $product, ['status' => $before], ['status' => $product->status]);
                    });

                Notification::make()->success()->title(__(':count produk diperbarui.', ['count' => $records->count()]))->send();
            })
            ->deselectRecordsAfterCompletion();
    }
}
