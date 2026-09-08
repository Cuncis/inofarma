<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Models\Category;
use App\Support\AdminOptions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('position')
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('products'))
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => AdminOptions::toLabel(AdminOptions::CATEGORY_STATUSES, $state)),
                TextColumn::make('products_count')
                    ->label('Produk')
                    ->alignRight(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                // A category still holding products can't be deleted —
                // `products.category_id` is restrictOnDelete at the database
                // level too; see CategoryController::destroy().
                DeleteAction::make()
                    ->before(function (Category $record, DeleteAction $action) {
                        $count = $record->products()->count();

                        if ($count > 0) {
                            Notification::make()
                                ->danger()
                                ->title("Kategori \"{$record->name}\" masih dipakai {$count} produk dan tidak bisa dihapus.")
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
