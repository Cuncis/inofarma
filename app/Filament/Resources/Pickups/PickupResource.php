<?php

namespace App\Filament\Resources\Pickups;

use App\Filament\Resources\Pickups\Pages\ListPickups;
use App\Filament\Resources\Pickups\Tables\PickupsTable;
use App\Models\Order;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

/**
 * The branch counter's own screen (ROADMAP.md 7.2): every order currently
 * `siap diambil` — scoped to the signed-in staff member's own branch for
 * free by `Order`'s `BranchScope` — and the code-entry action that hands one
 * over. See PickupController and App\Support\Pickup\PickupCodeService.
 */
class PickupResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQrCode;

    protected static ?string $modelLabel = 'Pengambilan';

    protected static ?string $pluralModelLabel = 'Pengambilan';

    protected static ?string $recordTitleAttribute = 'number';

    protected static ?string $slug = 'pengambilan';

    public static function table(Table $table): Table
    {
        return PickupsTable::configure($table);
    }

    public static function canViewAny(): bool
    {
        return (bool) Auth::guard('web')->user()?->can('Pesanan:Proses');
    }

    public static function canCreate(): bool
    {
        return false;
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
            'index' => ListPickups::route('/'),
        ];
    }
}
