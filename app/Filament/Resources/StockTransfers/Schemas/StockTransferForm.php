<?php

namespace App\Filament\Resources\StockTransfers\Schemas;

use App\Models\BranchStock;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class StockTransferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('from_branch_id')
                    ->label('Cabang Asal')
                    ->relationship('fromBranch', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->rule(fn () => function (string $attribute, $value, Closure $fail) {
                        $user = Auth::guard('web')->user();

                        if ($user->branch_id !== null && $user->branch_id !== (int) $value) {
                            $fail('Anda hanya bisa meminta transfer dari cabang Anda sendiri.');
                        }
                    }),
                Select::make('to_branch_id')
                    ->label('Cabang Tujuan')
                    ->relationship('toBranch', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->different('from_branch_id')
                    ->validationMessages(['different' => 'Cabang tujuan harus berbeda dari cabang asal.']),
                Select::make('product_id')
                    ->label('Produk')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live(),
                TextInput::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->maxValue(100000)
                    ->required()
                    ->rule(fn (Get $get) => function (string $attribute, $value, Closure $fail) use ($get) {
                        $branchId = $get('from_branch_id');
                        $productId = $get('product_id');

                        if (! $branchId || ! $productId) {
                            return;
                        }

                        $stock = BranchStock::where('branch_id', $branchId)->where('product_id', $productId)->first();
                        $available = $stock ? $stock->quantity - $stock->reserved_quantity : 0;

                        if ($available < (int) $value) {
                            $fail("Stok di cabang asal hanya tersisa {$available}.");
                        }
                    }),
                Textarea::make('note')
                    ->label('Catatan')
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }
}
