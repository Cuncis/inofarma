<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Pages\ViewProduct;
use App\Filament\Resources\Products\RelationManagers\ImagesRelationManager;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Filament\Resources\Products\Schemas\ProductInfolist;
use App\Filament\Resources\Products\Tables\ProductsTable;
use App\Models\Product;
use App\Support\Auth\AuthorizesByPermission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * Product CRUD for the admin — see ProductController's docblock. Images are
 * managed on the edit page's own relation manager, never on this form: they
 * are multipart requests against a product that already exists.
 */
class ProductResource extends Resource
{
    use AuthorizesByPermission;

    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?int $navigationSort = -8;

    public static function getModelLabel(): string
    {
        return __('Produk');
    }

    public static function getPluralModelLabel(): string
    {
        return __('plural:Produk');
    }

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'produk';

    /**
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
        return ProductForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProductInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ImagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'view' => ViewProduct::route('/{record}'),
            'edit' => EditProduct::route('/{record}/edit'),
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
