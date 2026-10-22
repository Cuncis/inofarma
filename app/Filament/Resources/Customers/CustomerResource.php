<?php

namespace App\Filament\Resources\Customers;

use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Schemas\CustomerForm;
use App\Filament\Resources\Customers\Tables\CustomersTable;
use App\Models\Customer;
use App\Support\Auth\AuthorizesByPermission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    use AuthorizesByPermission;

    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?int $navigationSort = -7;

    public static function getModelLabel(): string
    {
        return __('Pelanggan');
    }

    public static function getPluralModelLabel(): string
    {
        return __('plural:Pelanggan');
    }

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'pelanggan';

    /**
     * @return array{viewAny: string, create?: string, update?: string, delete?: string}
     */
    protected static function permissions(): array
    {
        return [
            'viewAny' => 'Pelanggan:Lihat',
            'create' => 'Pelanggan:Tambah',
            'update' => 'Pelanggan:Ubah',
            'delete' => 'Pelanggan:Hapus',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return CustomerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomersTable::configure($table);
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
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'edit' => EditCustomer::route('/{record}/edit'),
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
