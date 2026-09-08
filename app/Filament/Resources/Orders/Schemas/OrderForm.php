<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Product;
use App\Support\AdminOptions;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pesanan')
                    ->columns(2)
                    ->schema([
                        Select::make('customer_id')
                            ->label('Pelanggan')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('branch_id')
                            ->label('Cabang')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('fulfilment')
                            ->label('Cara Terima')
                            ->options(array_flip(AdminOptions::FULFILMENTS))
                            ->required(),
                        Select::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->options(array_combine(AdminOptions::paymentMethods(), AdminOptions::paymentMethods()))
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options(array_flip(AdminOptions::ORDER_STATUSES))
                            ->required(),
                        TextInput::make('shipping_total')
                            ->label('Ongkos Kirim')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10000000)
                            ->default(0)
                            ->required(),
                        Textarea::make('note')
                            ->label('Catatan')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),
                Section::make('Item Pesanan')
                    ->schema([
                        Repeater::make('items')
                            ->label('')
                            ->relationship('items')
                            ->schema([
                                Select::make('product_id')
                                    ->label('Produk')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(2),
                                TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(10000)
                                    ->default(1)
                                    ->required(),
                            ])
                            ->mutateRelationshipDataBeforeCreateUsing(fn (array $data) => self::snapshot($data))
                            ->mutateRelationshipDataBeforeSaveUsing(fn (array $data) => self::snapshot($data))
                            ->columns(3)
                            ->addActionLabel('Tambah Item')
                            ->minItems(1)
                            ->required(),
                    ]),
            ]);
    }

    /**
     * A line item snapshots the product's name, SKU and price at the moment
     * it's added — see OrderController's docblock. Repricing or deleting a
     * product later must never rewrite what somebody was charged.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function snapshot(array $data): array
    {
        $product = Product::find($data['product_id']);

        $data['product_name'] = $product->name;
        $data['sku'] = $product->sku;
        $data['unit_price'] = $product->price;
        $data['line_total'] = $product->price * (int) $data['quantity'];

        return $data;
    }
}
