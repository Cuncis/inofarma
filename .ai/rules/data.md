---
paths:
  - 'app/Models/Region.php,app/Console/Commands/ImportRegions.php,app/Http/Controllers/Shop/RegionController.php,database/data/**'
---

# Data

## Indonesia region data (`regions` table) is static and self-hosted, not a live API
The Provinsi/Kota/Kecamatan/Kelurahan cascading dropdowns on `Shop/AddNewAddress.jsx` read from the `regions` table (code=PK, parent_code, level 1-4, name, postal_code — level 4 only has postal_code), populated by `php artisan regions:import` from the bundled dumps at `database/data/wilayah.sql` + `wilayah_kodepos.sql` (cahyadsn/wilayah + wilayah_kodepos on GitHub, MIT, Kepmendagri No 300.2.2-2138/2025 basis, downloaded 2026-08-28). Same "self-host, never depend on a third party staying up" call this app already makes for storefront images — there is no runtime call to an external wilayah API.

`CustomerAddress` still stores plain-text `provinsi`/`kota`/`kecamatan`/`kelurahan`/`postal_code` — the dropdowns only constrain what a shopper can type, they don't change that schema or `AddressController::store()`'s validation. Kode Pos is never an independent dropdown: each kelurahan/desa has exactly one official postal code in this dataset, so it's derived from the selected kelurahan and shown read-only.

To refresh the region data later, replace the two files under `database/data/` with newer dumps from the same repos and re-run `regions:import` — it truncates and reloads `regions`, safe to re-run.
