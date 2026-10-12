<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use App\Models\Product;
use App\Support\AdminOptions;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Informasi Pesanan'))
                    ->columns(2)
                    ->schema([
                        Select::make('customer_id')
                            ->label(__('Pelanggan'))
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('branch_id')
                            ->label(__('Cabang'))
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('fulfilment')
                            ->label(__('Cara Terima'))
                            ->options(AdminOptions::options(AdminOptions::FULFILMENTS))
                            ->required()
                            ->live()
                            // Delivery and pickup have different steps: a status the
                            // new way of receiving does not have goes back to the start.
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                if ($state && ! in_array($get('status'), Order::statusesFor($state), true)) {
                                    $set('status', 'menunggu pembayaran');
                                }
                            }),
                        Select::make('payment_method')
                            ->label(__('Metode Pembayaran'))
                            ->options(AdminOptions::listOptions(AdminOptions::paymentMethods()))
                            ->required(),
                        Select::make('status')
                            ->label(__('Status'))
                            ->options(fn (Get $get) => AdminOptions::options(AdminOptions::orderStatusesFor($get('fulfilment') ?: 'antar')))
                            ->required(),
                        TextInput::make('shipping_total')
                            ->label(__('Ongkos Kirim'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10000000)
                            ->default(0)
                            ->required(),
                        Textarea::make('note')
                            ->label(__('Catatan'))
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),
                Section::make(__('Item Pesanan'))
                    ->schema([
                        Repeater::make('items')
                            ->label('')
                            ->relationship('items')
                            ->schema([
                                Select::make('product_id')
                                    ->label(__('Produk'))
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(2),
                                TextInput::make('quantity')
                                    ->label(__('Jumlah'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(10000)
                                    ->default(1)
                                    ->required(),
                            ])
                            ->mutateRelationshipDataBeforeCreateUsing(fn (array $data) => self::snapshot($data))
                            ->mutateRelationshipDataBeforeSaveUsing(fn (array $data) => self::snapshot($data))
                            ->columns(3)
                            ->addActionLabel(__('Tambah Item'))
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
