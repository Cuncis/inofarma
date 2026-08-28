<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Populates `regions` from the two SQL dumps bundled at `database/data/`:
 *
 *   - wilayah.sql          38 provinsi, 514 kota/kabupaten, 7.285 kecamatan,
 *                          83.762 kelurahan/desa — code + name only.
 *   - wilayah_kodepos.sql  the postal code for each of those 83.762
 *                          kelurahan/desa.
 *
 * Both come from cahyadsn/wilayah and cahyadsn/wilayah_kodepos on GitHub
 * (MIT licensed), built off Kepmendagri No 300.2.2-2138 Tahun 2025 — the
 * newest gazetted region-code regulation at the time these were bundled
 * (2026-08-28). That's the same "self-host, don't depend on a third party
 * staying up" call this app already makes for storefront images: the
 * cascading dropdowns on `Shop/AddNewAddress.jsx` read `regions` from our
 * own database, never a live external API.
 *
 * The dumps are parsed here rather than executed as SQL — they're plain
 * `INSERT ... VALUES` statements, and regexing the tuples out is simpler
 * than standing up their MyISAM staging tables just to throw them away
 * afterward. Indonesian region names occasionally contain an apostrophe,
 * which the dumps escape SQL-style as `''` (e.g. `Pasi Kuala Ba''u`) — the
 * tuple regex accounts for that, and it's unescaped back to `'` below.
 *
 * Re-running this command is safe: it truncates `regions` first, so it's
 * also how you refresh the data if Kemendagri regazettes the codes later —
 * replace the two files under `database/data/` with newer dumps from the
 * same repos and run this again.
 */
class ImportRegions extends Command
{
    protected $signature = 'regions:import';

    protected $description = 'Impor data wilayah Indonesia (provinsi/kota/kecamatan/kelurahan) dan kode pos';

    private const TUPLE_PATTERN = "/\('([0-9.]+)',\s*'((?:[^']|'')*)'\)/";

    public function handle(): int
    {
        $wilayahPath = database_path('data/wilayah.sql');
        $kodeposPath = database_path('data/wilayah_kodepos.sql');

        if (! is_file($wilayahPath) || ! is_file($kodeposPath)) {
            $this->error('Berkas database/data/wilayah.sql atau wilayah_kodepos.sql tidak ditemukan.');

            return self::FAILURE;
        }

        $this->line('Membaca kode pos...');
        $postalCodes = $this->parseTuples(file_get_contents($kodeposPath));

        $this->line('Membaca wilayah...');
        $regions = $this->parseTuples(file_get_contents($wilayahPath));

        $this->info(sprintf('%d wilayah, %d kode pos ditemukan.', count($regions), count($postalCodes)));

        $rows = [];
        foreach ($regions as $code => $name) {
            $segments = explode('.', $code);

            $rows[] = [
                'code' => $code,
                'parent_code' => count($segments) > 1
                    ? implode('.', array_slice($segments, 0, -1))
                    : null,
                'level' => count($segments),
                'name' => $name,
                'postal_code' => $postalCodes[$code] ?? null,
            ];
        }

        DB::table('regions')->truncate();

        $bar = $this->output->createProgressBar(count($rows));
        foreach (array_chunk($rows, 5000) as $chunk) {
            DB::table('regions')->insert($chunk);
            $bar->advance(count($chunk));
        }
        $bar->finish();
        $this->newLine();

        $this->info(sprintf('%d baris wilayah disimpan.', count($rows)));

        return self::SUCCESS;
    }

    /**
     * @return array<string, string> code => unescaped name/postal code
     */
    private function parseTuples(string $sql): array
    {
        preg_match_all(self::TUPLE_PATTERN, $sql, $matches, PREG_SET_ORDER);

        $tuples = [];
        foreach ($matches as $match) {
            // A handful of names in the source dump carry a stray trailing
            // space (e.g. "Kota Administrasi Jakarta Utara ").
            $tuples[$match[1]] = trim(str_replace("''", "'", $match[2]));
        }

        return $tuples;
    }
}
