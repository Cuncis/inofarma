<?php

namespace App\Filament\Resources\Coupons;

use App\Filament\Resources\Coupons\Pages\CreateCoupon;
use App\Filament\Resources\Coupons\Pages\EditCoupon;
use App\Filament\Resources\Coupons\Pages\ListCoupons;
use App\Filament\Resources\Coupons\Schemas\CouponForm;
use App\Filament\Resources\Coupons\Tables\CouponsTable;
use App\Models\Coupon;
use App\Support\Auth\AuthorizesByPermission;
use App\Support\Auth\SuperAdmin;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CouponResource extends Resource
{
    use AuthorizesByPermission;

    protected static ?string $model = Coupon::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?int $navigationSort = -5;

    public static function getModelLabel(): string
    {
        return __('Kupon');
    }

    public static function getPluralModelLabel(): string
    {
        return __('plural:Kupon');
    }

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?string $slug = 'kupon';

    /**
     * A coupon sets prices, so only Super Admin manages coupons.
     *
     * @return array{viewAny: string, create?: string, update?: string, delete?: string}
     */
    protected static function permissions(): array
    {
        return [
            'viewAny' => SuperAdmin::ROLE,
            'create' => SuperAdmin::ROLE,
            'update' => SuperAdmin::ROLE,
            'delete' => SuperAdmin::ROLE,
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return CouponForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CouponsTable::configure($table);
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
            'index' => ListCoupons::route('/'),
            'create' => CreateCoupon::route('/create'),
            'edit' => EditCoupon::route('/{record}/edit'),
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
