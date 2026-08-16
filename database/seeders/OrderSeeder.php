<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Pesanan contoh, tersebar ke beberapa cabang.
 *
 * Baris pesanan menyalin nama dan harga satuan produk saat itu — sama seperti
 * yang dilakukan pengendali saat pesanan sungguhan dibuat. Menyeed lewat relasi
 * `product` akan salah: pesanan lama harus tetap menunjukkan harga lamanya.
 */
class OrderSeeder extends Seeder
{
    /**
     * nomor, email pelanggan, tanggal, pembayaran, status, cara terima, ongkir,
     * [sku => jumlah]
     *
     * @var list<array<int, mixed>>
     */
    private const ORDERS = [
        ['INO-2451', 'kirana.wijaya@mail.com', '2025-08-14', 'Transfer Bank', 'selesai', 'antar', 25000,
            ['PRD-001' => 12, 'PRD-004' => 6, 'PRD-009' => 2]],
        ['INO-2450', 'rizky.ananda@mail.com', '2025-08-14', 'GoPay', 'diproses', 'antar', 25000,
            ['PRD-003' => 10, 'PRD-007' => 7]],
        ['INO-2449', 'dinda.puspita@mail.com', '2025-08-13', 'OVO', 'dikirim', 'antar', 20000,
            ['PRD-005' => 5, 'PRD-012' => 4]],
        ['INO-2448', 'bagas.saputra@mail.com', '2025-08-13', 'DANA', 'dibatalkan', 'antar', 12000,
            ['PRD-008' => 2, 'PRD-010' => 1]],
        ['INO-2447', 'sari.wulandari@mail.com', '2025-08-12', 'Tunai', 'selesai', 'ambil', 0,
            ['PRD-011' => 5, 'PRD-006' => 1]],
        ['INO-2446', 'kirana.wijaya@mail.com', '2025-08-10', 'Transfer Bank', 'selesai', 'antar', 18000,
            ['PRD-002' => 5, 'PRD-001' => 8]],
        ['INO-2445', 'dinda.puspita@mail.com', '2025-08-08', 'OVO', 'menunggu pembayaran', 'antar', 15000,
            ['PRD-009' => 3, 'PRD-012' => 4]],
    ];

    public function run(): void
    {
        $branches = Branch::orderBy('id')->get();

        if ($branches->isEmpty()) {
            return;
        }

        $customers = Customer::pluck('id', 'email');
        $products = Product::get()->keyBy('sku');

        foreach (self::ORDERS as $index => [$number, $email, $date, $payment, $status, $fulfilment, $shipping, $lines]) {
            if (! $customers->has($email)) {
                continue;
            }

            // Two orders can share a date. Stagger the hour so "newest first"
            // has something to sort on and the seed order is reproducible.
            $placedAt = Carbon::parse($date)->setTime(18 - $index, 0);

            $order = Order::withTrashed()->updateOrCreate(
                ['number' => $number],
                [
                    'branch_id' => $branches[$index % $branches->count()]->id,
                    'customer_id' => $customers[$email],
                    'fulfilment' => $fulfilment,
                    'status' => $status,
                    'payment_method' => $payment,
                    'payment_status' => $status === 'selesai' ? 'lunas' : 'belum bayar',
                    'shipping_total' => $shipping,
                    'paid_at' => $status === 'selesai' ? $placedAt : null,
                    'completed_at' => $status === 'selesai' ? $placedAt : null,
                    'cancelled_at' => $status === 'dibatalkan' ? $placedAt : null,
                    'note' => '',
                    'created_at' => $placedAt,
                    'updated_at' => $placedAt,
                    'deleted_at' => null,
                ],
            );

            $order->items()->delete();

            foreach ($lines as $sku => $quantity) {
                $product = $products[$sku];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'unit_price' => $product->price,
                    'quantity' => $quantity,
                    'line_total' => $product->price * $quantity,
                ]);
            }

            $subtotal = (int) $order->items()->sum('line_total');

            $order->forceFill([
                'subtotal' => $subtotal,
                'grand_total' => $subtotal + $shipping,
            ])->save();
        }
    }
}
