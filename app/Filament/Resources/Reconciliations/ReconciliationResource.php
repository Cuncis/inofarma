<?php

namespace App\Filament\Resources\Reconciliations;

use App\Filament\Resources\Reconciliations\Pages\ListReconciliations;
use App\Filament\Resources\Reconciliations\Tables\ReconciliationsTable;
use App\Models\Payment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * "Rekonsiliasi harian, dipecah per cabang untuk setoran" (ROADMAP.md Fase
 * 6) — read-only, save one action. Every write to a `Payment` happens
 * through `DokuPaymentService`, either the webhook (the normal path) or the
 * "Cek Status" row action here (a manual nudge for one stuck attempt), same
 * underlying `applyNotification()` either way. See PaymentController.
 */
class ReconciliationResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $modelLabel = 'Rekonsiliasi';

    protected static ?string $pluralModelLabel = 'Rekonsiliasi';

    protected static ?string $recordTitleAttribute = 'invoice_number';

    protected static ?string $slug = 'rekonsiliasi';

    public static function table(Table $table): Table
    {
        return ReconciliationsTable::configure($table);
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
            'index' => ListReconciliations::route('/'),
        ];
    }
}
