<?php

namespace App\Filament\Resources\Suppliers;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Suppliers\Pages\CreateSupplier;
use App\Filament\Resources\Suppliers\Pages\EditSupplier;
use App\Filament\Resources\Suppliers\Pages\ListSuppliers;
use App\Filament\Resources\Suppliers\Schemas\SupplierForm;
use App\Filament\Resources\Suppliers\Tables\SuppliersTable;
use App\Models\Supplier;
use App\Support\Auth\AuthorizesByPermission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SupplierResource extends Resource
{
    use AuthorizesByPermission;

    protected static ?string $model = Supplier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?int $navigationSort = 3;

    public static function getNavigationParentItem(): ?string
    {
        return ProductResource::getNavigationLabel();
    }

    public static function getModelLabel(): string
    {
        return __('Pemasok');
    }

    public static function getPluralModelLabel(): string
    {
        return __('plural:Pemasok');
    }

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'pemasok';

    /**
     * Pemasok is product master data, so it follows the Produk permissions.
     *
     * @return array{viewAny: string, create?: string, update?: string, delete?: string}
     */
    protected static function permissions(): array
    {
        return [
            'viewAny' => 'Produk:Lihat',
            'create' => 'Produk:Tambah',
            'update' => 'Produk:Ubah',
            'delete' => 'Produk:Hapus',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return SupplierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SuppliersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSuppliers::route('/'),
            'create' => CreateSupplier::route('/create'),
            'edit' => EditSupplier::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
