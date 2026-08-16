<?php

namespace App\Console\Commands;

use App\Models\Branch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Fill in or correct a branch's coordinates.
 *
 * Two modes:
 *
 *   php artisan cabang:geocode CB-001 --lat=-6.614 --lng=106.788
 *       Sets the coordinates directly — use this once a precise value is known
 *       (a plus code, a pin dropped on Google Maps at the actual storefront).
 *
 *   php artisan cabang:geocode CB-001
 *       Looks the branch's stored address up on Nominatim (OpenStreetMap),
 *       free and keyless. Good enough to unblock "nearest branch" during
 *       development, not a substitute for a manual check before launch.
 *
 * At 1.000 branches this stops being a one-by-one command. Fase 15.2 replaces
 * the lookup with the Google Geocoding API (better coverage of Indonesian
 * addresses, needs `services.google.geocoding_key` in `config/services.php`)
 * and batches every branch missing coordinates in one run.
 */
class GeocodeBranch extends Command
{
    protected $signature = 'cabang:geocode {code : Branch code, e.g. CB-001}
        {--lat= : Latitude, when it is already known}
        {--lng= : Longitude, when it is already known}';

    protected $description = 'Isi atau perbaiki koordinat satu cabang';

    public function handle(): int
    {
        $branch = Branch::where('code', $this->argument('code'))->first();

        if (! $branch) {
            $this->error("Cabang dengan kode {$this->argument('code')} tidak ditemukan.");

            return self::FAILURE;
        }

        if ($this->option('lat') !== null || $this->option('lng') !== null) {
            return $this->setDirectly($branch);
        }

        return $this->lookUp($branch);
    }

    private function setDirectly(Branch $branch): int
    {
        $lat = $this->option('lat');
        $lng = $this->option('lng');

        if (! is_numeric($lat) || ! is_numeric($lng)) {
            $this->error('--lat dan --lng harus diisi berdua, sebagai angka.');

            return self::FAILURE;
        }

        $branch->update([
            'latitude' => (float) $lat,
            'longitude' => (float) $lng,
            'maps_url' => "https://www.google.com/maps?q={$lat},{$lng}",
        ]);

        $this->info("{$branch->name}: koordinat diatur ke {$lat}, {$lng}.");

        return self::SUCCESS;
    }

    private function lookUp(Branch $branch): int
    {
        $query = collect([
            $branch->address_line,
            $branch->kelurahan,
            $branch->kecamatan,
            $branch->kota,
            $branch->provinsi,
            'Indonesia',
        ])->filter()->implode(', ');

        $response = Http::withHeaders([
            // Nominatim's usage policy requires an identifying User-Agent.
            'User-Agent' => 'Inofarma-Geocoder/1.0 (internal branch onboarding)',
        ])->get('https://nominatim.openstreetmap.org/search', [
            'format' => 'json',
            'limit' => 1,
            'q' => $query,
        ]);

        $result = $response->json(0);

        if (! $response->successful() || ! $result) {
            $this->error("Tidak menemukan koordinat untuk \"{$query}\". Coba --lat dan --lng manual.");

            return self::FAILURE;
        }

        $branch->update([
            'latitude' => (float) $result['lat'],
            'longitude' => (float) $result['lon'],
            'maps_url' => "https://www.google.com/maps?q={$result['lat']},{$result['lon']}",
        ]);

        $this->info("{$branch->name}: {$result['lat']}, {$result['lon']}");
        $this->line("  ({$result['display_name']})");
        $this->comment('  Perkiraan dari alamat — verifikasi manual sebelum dipakai untuk pengantaran nyata.');

        return self::SUCCESS;
    }
}
