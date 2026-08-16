<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Pelanggan contoh beserta satu alamat utama masing-masing.
 *
 * Kata sandi sengaja sama dan lemah — ini data demo untuk lingkungan lokal.
 * Pendaftaran sungguhan menetapkan kata sandi pelanggan sendiri (Fase 3.3).
 */
class CustomerSeeder extends Seeder
{
    /**
     * code, nama, email, telepon, kota, alamat, avatar, status, bergabung
     *
     * @var list<array<int, mixed>>
     */
    private const CUSTOMERS = [
        ['CUS-001', 'Kirana Wijaya', 'kirana.wijaya@mail.com', '+62 812-3456-7890', 'Jakarta Barat', 'Jl. Kebon Jeruk Raya No. 27', 1, 'aktif', '2024-01-12'],
        ['CUS-002', 'Rizky Ananda', 'rizky.ananda@mail.com', '+62 813-2233-4455', 'Bandung', 'Jl. Asia Afrika No. 88', 2, 'aktif', '2024-03-03'],
        ['CUS-003', 'Dinda Puspita', 'dinda.puspita@mail.com', '+62 856-7788-9900', 'Surabaya', 'Jl. Pemuda No. 14', 3, 'aktif', '2024-06-21'],
        ['CUS-004', 'Bagas Saputra', 'bagas.saputra@mail.com', '+62 878-1122-3344', 'Yogyakarta', 'Jl. Malioboro No. 5', 4, 'nonaktif', '2024-09-09'],
        ['CUS-005', 'Sari Wulandari', 'sari.wulandari@mail.com', '+62 811-5566-7788', 'Semarang', 'Jl. Pandanaran No. 62', 5, 'aktif', '2024-11-17'],
        ['CUS-006', 'Anisa Rahmawati', 'anisa.rahmawati@mail.com', '+62 852-9900-1122', 'Medan', 'Jl. Gatot Subroto No. 30', 6, 'aktif', '2025-08-02'],
    ];

    public function run(): void
    {
        foreach (self::CUSTOMERS as [$code, $name, $email, $phone, $kota, $address, $avatar, $status, $joined]) {
            $customer = Customer::withTrashed()->updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'email' => $email,
                    'email_verified_at' => Carbon::parse($joined),
                    'phone' => $phone,
                    'password' => Hash::make('password'),
                    'avatar_path' => "/media/images/users/avatar-{$avatar}.jpg",
                    'status' => $status,
                    'consent_at' => Carbon::parse($joined),
                    'consent_version' => '1.0',
                    'created_at' => Carbon::parse($joined),
                    'deleted_at' => null,
                ],
            );

            CustomerAddress::updateOrCreate(
                ['customer_id' => $customer->id, 'label' => 'Rumah'],
                [
                    'recipient_name' => $name,
                    'phone' => $phone,
                    'address_line' => $address,
                    'kota' => $kota,
                    'provinsi' => 'DKI Jakarta',
                    'is_default' => true,
                ],
            );
        }
    }
}
