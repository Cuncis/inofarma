<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Support\Shipping\Biteship\BiteshipClient;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * Finds out why checkout shows no couriers, without guessing. Read-only: it
 * checks the settings, asks Biteship for its courier list, then asks for a
 * price from each delivering branch to a point a few kilometres away. Run it on
 * the server that has the problem:
 *
 *   php artisan biteship:cek
 */
class CheckBiteship extends Command
{
    protected $signature = 'biteship:cek';

    protected $description = 'Periksa pengaturan Biteship dan coba ambil ongkos kirim dari tiap cabang yang mengantar';

    public function handle(): int
    {
        $failed = false;

        $key = (string) config('services.biteship.api_key');

        if ($key === '') {
            $this->error('BITESHIP_API_KEY kosong. Isi di .env lalu jalankan: php artisan config:clear');

            return self::FAILURE;
        }

        $this->info('API key: terisi ('.(str_starts_with($key, 'biteship_live') ? 'live' : (str_starts_with($key, 'biteship_test') ? 'TEST' : 'jenis tidak dikenal')).')');
        $this->line('Token webhook: '.(config('services.biteship.webhook_token') ? 'terisi' : 'KOSONG (webhook akan ditolak)'));

        $client = BiteshipClient::fromConfig();

        try {
            $codes = $client->courierCodes();
            $this->info('Daftar kurir dari Biteship: '.count($codes).' perusahaan ('.implode(', ', array_slice($codes, 0, 8)).(count($codes) > 8 ? ', ...' : '').')');
        } catch (RuntimeException $exception) {
            $this->error('Gagal mengambil daftar kurir: '.$exception->getMessage());

            return self::FAILURE;
        }

        $branches = Branch::active()->where('supports_delivery', true)->get();

        if ($branches->isEmpty()) {
            $this->error('Tidak ada cabang aktif yang melayani antar (Cabang > Ubah > Melayani antar).');

            return self::FAILURE;
        }

        foreach ($branches as $branch) {
            $this->newLine();
            $this->line("<options=bold>{$branch->name}</>");

            $problems = [];

            if ($branch->latitude === null || $branch->longitude === null) {
                $problems[] = 'koordinat (lintang/bujur) belum diisi';
            }

            if (blank($branch->address_line) || str_starts_with((string) $branch->address_line, 'Alamat belum diisi')) {
                $problems[] = 'alamat masih kosong atau placeholder';
            }

            if (blank($branch->phone ?: $branch->whatsapp)) {
                $problems[] = 'telepon belum diisi';
            }

            if (blank($branch->postal_code)) {
                $this->warn('  Kode pos kosong (disarankan diisi).');
            }

            foreach ($problems as $problem) {
                $this->error("  {$problem}");
            }

            $failed = $failed || $problems !== [];

            if ($branch->latitude === null || $branch->longitude === null) {
                $failed = true;

                continue;
            }

            try {
                $pricing = $client->rates([
                    'origin_latitude' => (float) $branch->latitude,
                    'origin_longitude' => (float) $branch->longitude,
                    'destination_latitude' => (float) $branch->latitude - 0.03,
                    'destination_longitude' => (float) $branch->longitude + 0.03,
                    'items' => [['name' => 'Tes', 'value' => 10000, 'quantity' => 1, 'weight' => 200]],
                ]);
            } catch (RuntimeException $exception) {
                $failed = true;
                $this->error('  Biteship menolak permintaan ongkir: '.$exception->getMessage());

                continue;
            }

            if ($pricing === []) {
                $failed = true;
                $this->error('  Biteship tidak mengembalikan satu pun kurir untuk rute uji ini.');

                continue;
            }

            $this->info('  Ongkir berhasil: '.count($pricing).' pilihan kurir (contoh: '.collect($pricing)->take(3)->map(fn (array $option) => ($option['company'] ?? '?').' '.($option['type'] ?? '').' Rp'.number_format((int) ($option['price'] ?? 0), 0, ',', '.'))->implode(', ').')');
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
