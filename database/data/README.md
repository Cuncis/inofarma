# Indonesia region data

`wilayah.sql` and `wilayah_kodepos.sql` are bundled, unmodified dumps from
[cahyadsn/wilayah](https://github.com/cahyadsn/wilayah) and
[cahyadsn/wilayah_kodepos](https://github.com/cahyadsn/wilayah_kodepos) (MIT
licensed), downloaded 2026-08-28. Both are built off Kepmendagri No
300.2.2-2138 Tahun 2025 — the newest gazetted Indonesian administrative
region code regulation available at the time.

`php artisan regions:import` parses these into the `regions` table that
backs the Provinsi/Kota/Kecamatan/Kelurahan dropdowns on
`Shop/AddNewAddress.jsx`. To refresh the data later (a regazetting, a new
regency split off an existing one, etc.), replace these two files with
newer dumps from the same repos and re-run the command — it truncates and
reloads `regions` from scratch, so it's safe to run repeatedly.
