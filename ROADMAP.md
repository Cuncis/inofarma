# Inofarma — Jalur Menuju Rilis

Rencana langkah demi langkah dari prototipe saat ini sampai jaringan apotek
online yang siap melayani pelanggan sungguhan.

**Konteks yang sudah disepakati**

| Keputusan | Pilihan |
| --- | --- |
| Model bisnis | **Jaringan apotek multi-cabang** — 10 cabang Jabodetabek sekarang, target 1.000 se-Indonesia |
| Cara beli | Pelanggan cari produk → pilih cabang terdekat → pilih **antar** atau **ambil di toko** |
| Golongan obat saat rilis | Obat bebas + bebas terbatas (tanpa obat keras/resep) |
| Tampilan etalase | **Tetap versi ponsel di semua perangkat**, termasuk desktop dan tablet |
| Tim | Satu pengembang, tanpa tenggat keras |
| Pembayaran | Midtrans atau Xendit |
| Pengiriman | RajaOngkir / Biteship + kurir instan per cabang |
| Hosting | VPS/cloud region Jakarta |

> **Catatan penting soal hukum.** Dokumen ini ditulis oleh pengembang, bukan
> penasihat hukum atau konsultan regulasi. Bagian kepatuhan di bawah adalah
> *daftar hal yang perlu Anda konfirmasi*, bukan nasihat hukum. Regulasi farmasi
> dan perlindungan data di Indonesia berubah cukup sering. Sebelum menerima
> pesanan pertama dari publik, mintalah tinjauan dari konsultan perizinan apotek
> dan, untuk urusan data pribadi, penasihat hukum. Menjual obat tanpa izin yang
> benar berisiko pidana, bukan sekadar denda administratif.
>
> **Model multi-cabang menggandakan beban perizinan.** Setiap cabang apotek
> umumnya butuh **SIA sendiri** dan **Apoteker Penanggung Jawab sendiri dengan
> SIPA aktif di cabang itu**. Sepuluh cabang berarti sepuluh berkas, bukan satu.
> Ini yang paling sering diremehkan saat merencanakan jaringan apotek.

---

## Daftar Isi

1. [Posisi saat ini](#1-posisi-saat-ini)
2. [Utang teknis yang sudah diketahui](#2-utang-teknis-yang-sudah-diketahui)
3. [Model multi-cabang: apa yang berubah](#3-model-multi-cabang-apa-yang-berubah)
4. [Fase 0 — Keputusan sebelum menulis kode](#fase-0--keputusan-sebelum-menulis-kode)
5. [Fase 1 — Basis data dan model](#fase-1--basis-data-dan-model)
6. [Fase 2 — Cabang, stok per cabang, dan lokasi](#fase-2--cabang-stok-per-cabang-dan-lokasi)
7. [Fase 3 — Autentikasi dan otorisasi](#fase-3--autentikasi-dan-otorisasi)
8. [Fase 4 — Katalog yang sesungguhnya](#fase-4--katalog-yang-sesungguhnya)
9. [Fase 5 — Alur belanja: pilih cabang, antar atau ambil](#fase-5--alur-belanja-pilih-cabang-antar-atau-ambil)
10. [Fase 6 — Pembayaran](#fase-6--pembayaran)
11. [Fase 7 — Pengiriman dan pengambilan](#fase-7--pengiriman-dan-pengambilan)
12. [Fase 8 — Notifikasi](#fase-8--notifikasi)
13. [Fase 9 — Kepatuhan dan halaman legal](#fase-9--kepatuhan-dan-halaman-legal)
14. [Fase 10 — Pengamanan](#fase-10--pengamanan)
15. [Fase 11 — Performa, SEO, analitik](#fase-11--performa-seo-analitik)
16. [Fase 12 — Pengujian dan CI](#fase-12--pengujian-dan-ci)
17. [Fase 13 — Deployment dan operasional](#fase-13--deployment-dan-operasional)
18. [Fase 14 — Checklist sebelum rilis](#fase-14--checklist-sebelum-rilis)
19. [Fase 15 — Setelah rilis dan jalan menuju 1.000 cabang](#fase-15--setelah-rilis-dan-jalan-menuju-1000-cabang)
20. [Ringkasan urutan dan estimasi](#ringkasan-urutan-dan-estimasi)
21. [Lampiran A — Daftar cabang saat ini](#lampiran-a--daftar-cabang-saat-ini)

---

## 1. Posisi saat ini

Yang **sudah ada dan berfungsi**:

- Laravel 12 + Inertia + React 18 + Tailwind, `npm run build` berhasil.
- **Etalase pelanggan**: 38 halaman — 37 layar (beranda, katalog, pencarian,
  keranjang, checkout, profil, riwayat, ulasan) plus halaman indeks. Terkunci
  pada lebar 430px di semua perangkat — **ini sesuai keinginan dan dipertahankan**.
- **Panel admin**: 53 halaman — dasbor, produk, kategori, pelanggan, penjual,
  pesanan, inventaris, pembelian, faktur, kupon, peran, hak akses, laporan,
  pengaturan, dan aplikasi pendukung (chat, email, kalender, todo).
- **CRUD berfungsi penuh** untuk Produk, Kategori, Pelanggan, Penjual, Pesanan —
  termasuk validasi sisi server dalam Bahasa Indonesia, aturan integritas
  (hapus ditolak bila masih dirujuk, ganti nama merambat ke data terkait), dan
  snapshot harga pada baris pesanan.
- **Login admin** dengan middleware penjaga di seluruh `/admin/*`.
- **119 pengujian otomatis**, 1.052 asersi, semuanya lulus.
- Palet dan bahasa sudah diseragamkan: Rupiah, Bahasa Indonesia, warna merek.

Yang **belum ada sama sekali**:

- **Basis data.** Semua data hidup di *session*. Hilang saat ganti browser,
  tidak terbagi antar pengguna, tidak ada riwayat.
- **Konsep cabang.** Ini yang paling besar. Seluruh model saat ini
  mengasumsikan **satu titik stok**. Tidak ada cabang, tidak ada stok per
  cabang, tidak ada lokasi, tidak ada pilihan ambil di toko.
- **Akun pelanggan yang nyata.** Login etalase menerima email/kata sandi apa pun.
- **Pembayaran, pengiriman, email, unggah berkas.**
- **Kepatuhan apa pun** — belum ada halaman legal yang mengikat, persetujuan
  data, atau jejak audit.

Artinya: yang ada sekarang adalah **prototipe yang sangat matang secara
antarmuka**, tetapi belum menyimpan apa pun dan belum mengenal cabang. Fase 1
dan 2 adalah pekerjaan terbesar.

---

## 2. Utang teknis yang sudah diketahui

| # | Hal | Dampak | Kapan diperbaiki |
| --- | --- | --- | --- |
| 1 | Etalase membaca katalog statis JS, admin membaca *store* server | Perubahan produk di admin **tidak muncul** di etalase | Fase 1 |
| 2 | **`stock` adalah satu angka pada produk** | Tidak bisa menyatakan "ada 5 di Otista, habis di Parakan" | **Fase 2 — perubahan mendasar** |
| 3 | Gambar ilustrasi kosong dimuat dari `george-fx.github.io` (host pihak ketiga) | Etalase bergantung pada server orang lain | Fase 4 |
| 4 | Atribut, Peran, Kupon, Faktur masih data contoh | Belum bisa dipakai | Fase 1 & 4 |
| 5 | "Penjual" bermakna *marketplace seller* | Tidak cocok untuk jaringan sendiri | Fase 1 — ubah jadi Pemasok |
| 6 | Gambar produk adalah render generik dari template | Terlihat jelas bukan foto obat asli | Fase 4 |
| 7 | Pencarian dijalankan di browser atas seluruh data | Tidak sanggup di atas ~500 produk, apalagi × 1.000 cabang | Fase 11 |
| 8 | Tidak ada `.env.example` untuk kunci pihak ketiga | Sulit di-*setup* ulang | Fase 13 |
| 9 | Alamat pelanggan hanya teks bebas | Tidak bisa hitung jarak ke cabang | Fase 2 |

Nomor 2 adalah yang paling penting. Setiap fitur yang dibangun di atas asumsi
"satu stok global" harus ditulis ulang. **Kerjakan Fase 2 sebelum menambah
fitur apa pun di atas inventaris.**

---

## 3. Model multi-cabang: apa yang berubah

Ini bagian baru dan paling menentukan arsitektur. Baca sebelum mulai coding.

### 3.1 Alur yang diinginkan

```
Pelanggan buka etalase
        │
        ├─ (izinkan lokasi)  ATAU  (pilih area manual)
        │
        ▼
Cari / telusuri produk  ──►  Halaman produk
                                    │
                                    ▼
                     "Tersedia di 4 apotek dekat Anda"
                     ┌──────────────────────────────┐
                     │ Otista        1,2 km   5 stok│
                     │ Parakan       3,8 km   2 stok│
                     │ Syahdan       6,1 km  12 stok│
                     │ Kebagusan     9,4 km   habis │ ← tidak bisa dipilih
                     └──────────────────────────────┘
                                    │
                                    ▼
                       Pilih cabang  →  Pilih cara terima
                                        ├─ Antar ke alamat saya
                                        └─ Ambil sendiri di toko
                                    │
                                    ▼
                        Keranjang (terikat pada 1 cabang)
                                    │
                                    ▼
                              Checkout & bayar
```

### 3.2 Konsekuensi teknis

| Hal | Sebelum | Sesudah |
| --- | --- | --- |
| Stok | satu angka per produk | satu baris per **produk × cabang** |
| Harga | satu harga | boleh sama untuk semua cabang, atau ditimpa per cabang |
| Keranjang | milik pelanggan | milik pelanggan **+ cabang** |
| Pesanan | tanpa asal | punya `branch_id` dan `fulfilment` (antar/ambil) |
| Ongkir | dari satu titik | dihitung dari **koordinat cabang** ke alamat |
| Pencarian | "apakah ada?" | "**di mana** ada, dan seberapa dekat?" |
| Staf admin | lihat semua | staf cabang lihat **cabangnya saja** |
| Laporan | satu angka | per cabang **dan** konsolidasi |

### 3.3 Keputusan desain yang saya sarankan

**Satu keranjang = satu cabang.** Bila pelanggan sudah punya barang dari
Cabang Otista lalu menambah produk yang hanya ada di Parakan, tampilkan pilihan:
*"Kosongkan keranjang dan pindah ke Parakan?"* atau *"Cari cabang lain yang
punya keduanya"*. Mencampur cabang dalam satu keranjang berarti satu pesanan
dikirim dari dua tempat — rumit, mahal, dan membingungkan pelanggan. Jangan.

**Harga dasar nasional, boleh ditimpa per cabang.** Simpan `price` di produk,
dan `price_override` opsional di `branch_stocks`. Tanpa ini, mengubah harga satu
obat berarti menyunting 1.000 baris nanti.

**Stok tersedia = fisik − dialokasikan.** Barang yang sudah dipesan tapi belum
diambil/dikirim tidak boleh terjual lagi. Untuk *pickup*, alokasi ditahan sampai
batas waktu ambil lewat, lalu dikembalikan otomatis.

**Cabang punya jam operasional.** Pesanan *pickup* di luar jam buka harus
ditolak atau dijadwalkan. Cabang tutup harus hilang dari daftar pilihan.

---

## Fase 0 — Keputusan sebelum menulis kode

Kerjakan paralel dengan Fase 1 karena perizinan makan waktu berminggu-minggu.

### 0.1 Perizinan — per cabang

- [ ] **NIB** badan usaha melalui OSS.
- [ ] **SIA untuk setiap cabang** — 10 cabang, 10 SIA.
- [ ] **APJ + SIPA untuk setiap cabang.** Satu apoteker tidak bisa menjadi
      penanggung jawab beberapa apotek sekaligus. Merekrut 10 apoteker
      penanggung jawab adalah biaya operasional tetap yang besar; masukkan ke
      rencana keuangan.
- [ ] **NPWP** badan usaha, status **PKP** bila omzet memenuhi ambang.
- [ ] Konfirmasikan: apakah izin penjualan daring diajukan **per cabang** atau
      **satu untuk badan usaha**. Ini pertanyaan pertama untuk konsultan Anda
      dan jawabannya mengubah biaya secara signifikan.

### 0.2 Izin khusus penjualan daring

- [ ] **PSEF** — pendaftaran penyelenggara sistem elektronik farmasi ke BPOM.
- [ ] **PSE Kominfo** — penyelenggara sistem elektronik lingkup privat.
- [ ] Konfirmasi aturan **iklan dan promosi obat**.

### 0.3 Keputusan produk yang mempengaruhi kode

- [x] ~~Etalase desktop~~ — **diputuskan: tetap tampilan ponsel di semua
      perangkat.** Catatan: pengunjung desktop akan melihat kolom 430px di
      tengah layar. Untuk mengurangi kesan kosong, di Fase 11 saya sarankan
      memberi latar belakang bermerek di sisi kiri-kanan, bukan abu-abu polos.
- [ ] **Radius antar per cabang.** Berapa km maksimal sebuah cabang mau
      mengantar? Ini menentukan cabang mana yang muncul sebagai pilihan "antar".
- [ ] **Batas waktu ambil di toko.** Berapa lama barang ditahan sebelum stok
      dikembalikan? (Saran: 2×24 jam.)
- [ ] **Bayar di tempat saat ambil?** Boleh bayar di kasir cabang, atau wajib
      bayar online dulu? Bayar di kasir menaikkan konversi tapi menambah risiko
      pesanan tidak diambil.
- [ ] **Tamu boleh checkout atau wajib daftar?**
- [ ] **PPN.** Konfirmasikan tarif berjalan dan SKU mana yang dikecualikan
      dengan konsultan pajak.
- [ ] **Kebijakan retur obat.**

**Selesai bila:** semua izin diajukan, dan enam keputusan produk tercatat.

---

## Fase 1 — Basis data dan model — ✅ SELESAI

Seluruh `*Store` berbasis session sudah diganti Eloquent. Data kini bertahan,
dan etalase serta admin membaca tabel yang sama.

### 1.1 Siapkan basis data — ✅

- [x] MySQL 8.0.46, basis data `inofarma_db`.
      **Catatan:** MySQL 8 dipilih karena sudah cukup sampai ribuan cabang.
      Jarak dihitung dengan rumus Haversine di SQL (`Branch::nearest()`),
      bukan `ST_Distance_Sphere`, supaya pengujian tetap jalan di SQLite.
- [x] `.env` diatur, seluruh migrasi jalan.

### 1.2 Skema inti — ✅ sebagian

Empat belas tabel sudah ada:

```
users                    staf admin (+ branch_id, soft delete)
branches                 10 cabang, dengan koordinat & jam buka
customers                akun pelanggan, terpisah dari users
customer_addresses       alamat + koordinat
categories
products                 katalog nasional — TANPA kolom stok
product_images
branch_stocks            stok, stok dipesan & harga per produk × cabang
inventory_batches        batch + kedaluwarsa, per cabang (FEFO)
inventory_movements      setiap pergerakan stok, per cabang
suppliers                (dulu "penjual") distributor/PBF
orders                   + branch_id + fulfilment (antar/ambil)
order_items              + snapshot nama, SKU & harga satuan
audit_logs, settings
```

Belum dibuat, menyusul di fase yang membutuhkannya: `roles`/`permissions`
(Fase 3), `stock_transfers` (Fase 2.2), `purchase_orders` (Fase 4.3),
`payments` (Fase 6), `shipments`/`pickups` (Fase 7), `coupons` (Fase 4.3),
`reviews` (Fase 4.3).

Aturan yang sudah menjadi *constraint* basis data, bukan hanya kode:

- [x] `products.category_id`, `products.supplier_id` → FK `RESTRICT`.
- [x] `orders.customer_id`, `orders.branch_id` → FK `RESTRICT`.
- [x] `order_items.product_id` → FK `SET NULL`, agar produk boleh hilang
      tanpa merusak riwayat.
- [x] `branch_stocks` → unique `(branch_id, product_id)`.
- [x] `order_items` menyimpan `product_name`, `sku` dan `unit_price` sebagai
      kolom sendiri.
- [x] `categories.slug`, `products.sku`, `customers.email`, `branches.slug`,
      `suppliers.license_number`, `orders.number` → unique index.
- [x] Semua kolom uang → `unsignedBigInteger` dalam **rupiah penuh**.

### 1.3 Model, factory, seeder — ✅

- [x] 14 model + `$fillable` + relasi + casts.
- [x] Factory untuk seluruh entitas inti.
- [x] Seeder: 10 cabang, 6 kategori, 5 pemasok, 12 produk, 120 baris stok,
      6 pelanggan, 7 pesanan.
- [x] *Soft deletes* pada products, customers, orders, branches, categories,
      suppliers, dan users.

**Masih harus dikerjakan manual:** koordinat sepuluh cabang sengaja dibiarkan
`null`. Isi dari Google Maps sebelum Fase 2.3 — `Branch::nearest()` melewati
baris tanpa koordinat.

### 1.4 Ganti store dengan Eloquent — ✅

- [x] Products, Categories, Customers, Suppliers, Orders.
- [x] `app/Support/*Store.php` dan `Catalog.php` dihapus.
- [x] Pengujian pakai `RefreshDatabase` dan data seeder.

Yang berubah di antarmuka, karena skemanya menuntut:

- Formulir produk **tidak lagi punya kolom stok**. Stok milik produk × cabang;
  satu kotak isian tidak bisa menjawab "stok di cabang yang mana". Daftar
  produk menampilkan total seluruh cabang, dan halaman detail memecahnya per
  cabang. Penyesuaian stok menyusul di Fase 2.4.
- Status produk kini `Aktif / Nonaktif / Arsip` (yang bisa diatur), terpisah
  dari ketersediaan `Tersedia / Stok Menipis / Habis` (yang diturunkan dari
  stok). Keduanya ditampilkan.
- Formulir pesanan **wajib** memilih cabang dan cara terima (antar/ambil).
  Keduanya menentukan stok mana yang dipakai.
- Status pemasok menjadi `Aktif / Nonaktif`, mengikuti enum tabel.
- Mengubah email pelanggan **tidak lagi memutus riwayat pesanan**. Dulu
  pesanan dikaitkan lewat email; sekarang lewat kunci asing.

### 1.5 Satukan etalase dan admin — ✅

- [x] Etalase mengambil produk dari basis data lewat prop Inertia bersama
      `catalog` (`ShopCatalogPresenter`), bukan `resources/js/lib/catalog.js`.
- [x] Katalog JS statis dihapus; berkas itu kini hanya berisi bentuk data dan
      turunannya (`useCatalog`, `findProduct`, `bestSellers`).
- [x] Terverifikasi: ubah harga di admin → berubah di etalase, pada permintaan
      berikutnya, untuk pengunjung yang belum masuk sekalipun.
      Dijaga oleh `tests/Feature/StorefrontCatalogTest.php`.

Etalase hanya menerima produk berstatus `aktif`, dan `status` yang dilihat
pembeli adalah **ketersediaan**, bukan status katalog.

**Selesai bila:** ~~data bertahan setelah restart, etalase dan admin sepakat,
dan seluruh pengujian lulus dengan `RefreshDatabase`.~~ ✅ 133 pengujian lulus.

---

## Fase 2 — Cabang, stok per cabang, dan lokasi — ✅ SELESAI

Inti dari model bisnis, dan sekarang berfungsi ujung ke ujung: satu produk
punya stok berbeda di setiap cabang, etalase mengurutkan cabang berdasarkan
jarak sungguhan, transfer stok memindahkan batch dengan kedaluwarsanya utuh,
dan admin punya CRUD, matriks, dan alur transfer yang lengkap.

### 2.1 Entitas Cabang — ✅

```php
branches
  id, name, slug
  address_line, kelurahan, kecamatan, kota, provinsi, postal_code
  latitude, longitude          // wajib — dasar semua perhitungan jarak
  phone, whatsapp
  sia_number                   // izin apotek cabang ini
  apj_name, apj_sipa_number    // apoteker penanggung jawab cabang ini
  operating_hours              // JSON: jam buka per hari
  supports_delivery            // boolean
  supports_pickup              // boolean
  delivery_radius_km
  status                       // aktif | tutup sementara | tutup permanen
  maps_url
```

- [x] Migrasi + model + factory. *(selesai di Fase 1)*
- [x] Seed 10 cabang dari Lampiran A. *(selesai di Fase 1)*
- [x] **Geocoding**: kesepuluh cabang digeokode dari OpenStreetMap/Nominatim
      pada tingkat jalan atau kelurahan — cukup akurat untuk pengurutan jarak,
      belum diverifikasi manual terhadap lokasi gerai sesungguhnya. Perintah
      `php artisan cabang:geocode {code} [--lat=] [--lng=]` tersedia untuk
      memasukkan koordinat pasti satu per satu, atau mencari ulang lewat
      Nominatim. Di 1.000 cabang, ganti pencariannya dengan Google Geocoding
      API (cakupan alamat Indonesia lebih baik) dan proses semua cabang yang
      koordinatnya kosong sekaligus.
- [ ] Index spasial (`SPATIAL`/`POINT`) pada kolom koordinat — sengaja ditunda.
      Indeks komposit `(status, latitude, longitude)` dari Fase 1 sudah cukup
      sampai ribuan baris; indeks spasial sungguhan baru bernilai pada skala
      yang menurut Fase 15.2 masih jauh di depan.

### 2.2 Stok per cabang — ✅

- [x] `branch_stocks`: `branch_id`, `product_id`, `quantity`,
      `reserved_quantity`, `price_override` (nullable), `reorder_point`.
      *(selesai di Fase 1)*
- [x] **`stock` tidak pernah ada di tabel `products`.** Perambatannya ke admin
      produk, etalase dan pengujian sudah dikerjakan sekaligus di Fase 1.4.
- [x] Stok tersedia = `quantity − reserved_quantity` (`BranchStock::available`).
- [x] `inventory_batches` per cabang: nomor batch, kedaluwarsa, jumlah.
- [x] Pengambilan stok pakai **FEFO** — `App\Support\Inventory\StockAllocator
      ::consume()` mengambil dari batch yang paling cepat kedaluwarsa lebih
      dulu, mengunci baris stok dan batch dalam satu transaksi, dan menolak
      permintaan yang melebihi stok tersedia. `::receive()` adalah sisi
      sebaliknya — stok baru datang dengan nomor batch dan tanggal
      kedaluwarsa, dari pembelian atau dari transfer.
- [x] `stock_transfers`: pindah barang antar cabang, dengan status
      (diminta → dikirim → diterima → dibatalkan). Saat dikirim, batch yang
      dipilih (FEFO) dan tanggal kedaluwarsanya disimpan di
      `batches_shipped`; saat diterima, batch yang persis sama dibuat ulang
      di cabang tujuan — kedaluwarsa tidak pernah hilang di tengah jalan.
      Barang dianggap "di jalan" di antara kedua langkah: sudah berkurang di
      asal, belum bertambah di tujuan mana pun.

### 2.3 Pencarian berbasis lokasi — ✅

- [x] Izin lokasi browser (`navigator.geolocation`) di halaman "Cabang Kami"
      dan di pemilih cabang pada halaman produk; **jalur mundur** berupa
      pilih kota, dibangun dari cakupan cabang yang sungguhan ada, bukan
      daftar administratif nasional yang belum diperlukan.
- [x] Pilihan area disimpan di session (`App\Support\LocationPreference`,
      lewat `POST/DELETE /ui/lokasi`) sehingga tidak ditanya berulang.
- [x] Endpoint "cabang terdekat" (`GET /api/cabang/terdekat`,
      `GET /api/cabang/untuk-produk/{sku}`) — mengurutkan berdasarkan jarak
      Haversine, menyaring cabang yang bukan `aktif`.
- [x] Halaman produk menampilkan daftar cabang: **nama, jarak, sisa stok,
      status buka/tutup, dukungan antar/ambil** — `Components/Shop/BranchPicker`.
- [x] Cabang tanpa stok tetap ditampilkan tapi tidak bisa dipilih (`selectable:
      false`) — pelanggan tetap tahu cabang itu ada.
- [x] Halaman "Cabang Kami" (`Shop/OurBranches`, `/ui/cabang-kami`) dengan
      daftar lengkap terurut jarak, tertaut dari menu Profil. Peta interaktif
      belum ada — setiap cabang tertaut ke Google Maps lewat `mapsUrl`
      alih-alih peta tertanam, cukup untuk sepuluh cabang saat ini.

### 2.4 Admin cabang — ✅ sebagian

- [x] CRUD Cabang (`/admin/cabang`), pola sama seperti Produk/Kategori.
      Cabang dengan stok atau riwayat pesanan tidak bisa dihapus, hanya
      ditutup permanen.
- [x] Halaman stok per cabang (`/admin/inventaris/stok/{cabang}`): lihat,
      sesuaikan (opname/rusak/kedaluwarsa/retur), dan terima stok baru
      dengan nomor batch dan kedaluwarsa.
- [x] Tampilan matriks (`/admin/inventaris/matriks`): satu produk × semua
      cabang dalam satu tabel, untuk melihat sebaran stok sekaligus.
- [x] Alur transfer stok antar cabang (`/admin/inventaris/transfer`):
      minta → kirim → terima, dengan pembatalan sebelum dikirim.
- [ ] **Pemilih cabang di topbar admin** — ditunda ke Fase 3.2. Belum ada
      peran atau `users.branch_id` yang berarti, jadi "staf pusat vs staf
      cabang" belum punya perbedaan nyata untuk dipilih di antaranya.

**Selesai bila:** ~~satu produk bisa punya stok berbeda di tiga cabang, etalase
menampilkan ketiganya diurutkan jarak, dan hanya yang ada stok bisa dipilih.~~
✅ Dibuktikan lewat `BranchLocatorTest` dan verifikasi manual: dari titik dekat
Monas, cabang Keamanan (2,9 km), Syahdan (5,3 km) dan Duri Kepa (6,6 km) semua
tampil terurut jarak dengan status stok masing-masing.

**Estimasi solo:** 4–5 minggu. *(Sebagian besar selesai dalam satu sesi kerja
karena arsitektur Fase 1 — stok per cabang, snapshot, kunci asing — sudah
menyiapkan jalannya. Verifikasi manual dan pengerasan produksi tetap perlu
waktu sungguhan sebelum rilis.)*

---

## Fase 3 — Autentikasi dan otorisasi

### 3.1 Autentikasi admin sungguhan

- [ ] Ganti `EnsureAdminIsAuthenticated` dengan guard `auth` Laravel.
- [ ] Ganti `AdminAuthController@login` dengan `Auth::attempt()`.
- [ ] Rate limiting: maksimal 5 percobaan per menit per IP.
- [ ] **2FA** untuk akun admin.
- [ ] Reset kata sandi lewat email dengan token kedaluwarsa.
- [ ] Kunci sesi otomatis setelah 30 menit tidak aktif.

### 3.2 Peran, hak akses, dan **cakupan cabang**

Halaman Peran dan Hak Akses sudah ada tampilannya — tinggal diberi tenaga.
Multi-cabang menambah satu dimensi: **peran saja tidak cukup, harus ada
cakupan.**

- [ ] Pasang `spatie/laravel-permission`.
- [ ] Peran: **Super Admin**, **Manajer Area**, **APJ Cabang**, **Kasir**,
      **Staf Gudang**.
- [ ] Tambahkan `users.branch_id` (null = akses pusat/semua cabang).
- [ ] **Global scope**: Kasir di Cabang Otista hanya melihat pesanan, stok, dan
      pelanggan Cabang Otista. Ini harus di lapisan query, bukan sekadar
      menyembunyikan tombol.
- [ ] Policy pada setiap controller.
- [ ] `audit_logs` mencatat siapa, apa, kapan, dari IP mana, **di cabang mana**.

### 3.3 Akun pelanggan

- [ ] Guard terpisah untuk pelanggan.
- [ ] Daftar + verifikasi email.
- [ ] Verifikasi nomor HP via OTP — layarnya sudah ada.
- [ ] Lupa kata sandi.
- [ ] Profil: alamat (dengan koordinat), riwayat pesanan, cabang favorit.

**Selesai bila:** staf cabang tidak bisa melihat data cabang lain, dibuktikan
dengan pengujian.

**Estimasi solo:** 2–3 minggu.

---

## Fase 4 — Katalog yang sesungguhnya

### 4.1 Gambar

- [ ] Unggah ke S3-compatible (AWS S3, atau Biznet/IDCloudHost untuk residensi
      data).
- [ ] Varian ukuran otomatis dengan `intervention/image`.
- [ ] Banyak gambar per produk, bisa diurutkan.
- [ ] Ganti seluruh gambar template dengan **foto produk asli**.
- [ ] **Self-host ilustrasi kosong** (utang teknis #3).

### 4.2 Data produk untuk apotek

- [ ] **Golongan obat** — bebas (hijau) / bebas terbatas (biru), dengan logonya.
- [ ] **Nomor izin edar (NIE) BPOM.**
- [ ] **Komposisi zat aktif.**
- [ ] **Indikasi, aturan pakai, efek samping, peringatan.** Untuk obat bebas
      terbatas, peringatan **P1–P6** wajib ditampilkan.
- [ ] **Produsen / pemegang izin edar.**
- [ ] **Batas pembelian per transaksi.**
- [ ] **Kondisi penyimpanan** — penting untuk memutuskan cabang mana boleh
      menjualnya (produk rantai dingin hanya di cabang berkulkas).

### 4.3 Selesaikan CRUD yang tersisa

- [ ] Atribut, Kupon, Faktur — pola sama seperti Produk/Kategori.
- [ ] Pemasok — ubah dari "Penjual" (utang teknis #5).
- [ ] Kupon: tambahkan cakupan **berlaku di cabang mana**.

**Selesai bila:** satu produk asli bisa dibuat lengkap dengan foto, NIE,
golongan, dan stok di tiga cabang berbeda.

**Estimasi solo:** 3 minggu.

---

## Fase 5 — Alur belanja: pilih cabang, antar atau ambil

Layarnya sebagian sudah ada; sekarang diberi tenaga dan ditambah langkah cabang.

### 5.1 Pemilihan cabang

- [ ] Halaman produk menampilkan cabang terdekat yang punya stok.
- [ ] Pelanggan memilih cabang **sebelum** menambah ke keranjang.
- [ ] Simpan cabang aktif; tampilkan di header ("Belanja dari: **Otista**").
- [ ] Ganti cabang: peringatkan bila keranjang tidak kosong, tawarkan pindah
      atau kosongkan (lihat [3.3](#33-keputusan-desain-yang-saya-sarankan)).

### 5.2 Cara terima

- [ ] Pilih **Antar** atau **Ambil di Toko** saat checkout.
- [ ] **Antar**: butuh alamat, hitung ongkir dari koordinat cabang, tolak bila
      di luar radius cabang.
- [ ] **Ambil**: tanpa ongkir, tampilkan alamat + jam buka + peta cabang,
      pilih perkiraan waktu ambil.

### 5.3 Keranjang dan checkout

- [ ] Keranjang tersimpan di basis data untuk pengguna login, session untuk
      tamu, digabung saat login.
- [ ] Keranjang terikat pada satu `branch_id`.
- [ ] Validasi stok saat menambah **dan sekali lagi saat checkout** — stok
      cabang bisa habis di antara keduanya.
- [ ] Terapkan batas pembelian obat bebas terbatas.
- [ ] Alamat tersimpan dengan koordinat; pilih wilayah bertingkat provinsi →
      kota → kecamatan → kelurahan → kode pos.
- [ ] Kupon dengan aturan: minimum belanja, kuota, tanggal, cabang berlaku,
      satu kali per pelanggan.
- [ ] Ringkasan: subtotal, diskon, ongkir, PPN, total.
- [ ] Nomor pesanan bermakna dan tidak mudah ditebak.
- [ ] Lacak pesanan; pembatalan oleh pelanggan sebelum diproses.

**Selesai bila:** pelanggan bisa menyelesaikan dua pesanan — satu antar, satu
ambil di toko — dari cabang berbeda, tanpa campur tangan admin.

**Estimasi solo:** 3–4 minggu.

---

## Fase 6 — Pembayaran

Pakai **Midtrans** atau **Xendit**. **Jangan pernah menyentuh data kartu
sendiri.**

- [ ] Pasang SDK resmi; kunci di `.env`, tidak pernah di repositori.
- [ ] Buat transaksi saat checkout, arahkan ke halaman bayar gateway.
- [ ] **Webhook** sebagai sumber kebenaran status — bukan redirect browser.
- [ ] **Verifikasi tanda tangan webhook.** Tanpa ini siapa pun bisa memalsukan
      "pembayaran berhasil".
- [ ] Webhook **idempoten** — gateway mengirim ulang notifikasi yang sama.
- [ ] Tangani setiap status: pending, berhasil, gagal, kedaluwarsa, refund,
      chargeback.
- [ ] Batas waktu bayar (mis. 24 jam), stok cabang dikembalikan otomatis.
- [ ] Bila diputuskan di Fase 0: **bayar di kasir** untuk pesanan *pickup*.
- [ ] Alur refund dengan pencatatan.
- [ ] Rekonsiliasi harian, **dipecah per cabang** untuk setoran.

**Selesai bila:** pembayaran asli Rp 10.000 berhasil di produksi, status berubah
lewat webhook, dan refund berhasil.

**Estimasi solo:** 2 minggu.

---

## Fase 7 — Pengiriman dan pengambilan

### 7.1 Antar

- [ ] **RajaOngkir** atau **Biteship** dengan **origin = koordinat cabang**,
      bukan satu gudang pusat.
- [ ] Isi **berat dan dimensi** setiap produk.
- [ ] **Kurir instan (Gojek/Grab)** — untuk jaringan cabang ini justru pilihan
      utama, bukan tambahan. Jarak cabang ke pelanggan biasanya pendek, dan
      obat sering dibutuhkan hari itu juga.
- [ ] Tolak alamat di luar radius cabang, sarankan cabang lain.
- [ ] Buat label dan resi dari admin cabang.
- [ ] Lacak resi otomatis.
- [ ] Persyaratan pengemasan obat: segel, pelindung suhu bila perlu.

### 7.2 Ambil di toko

- [ ] **Kode ambil** (angka pendek + QR) dikirim ke pelanggan.
- [ ] Antrean "siap diambil" di panel cabang.
- [ ] Staf memindai/memasukkan kode untuk menyerahkan pesanan.
- [ ] **Batas waktu ambil**; lewat batas → stok dikembalikan otomatis dan
      pelanggan diberi tahu.
- [ ] Catat siapa yang menyerahkan barang (jejak audit).

**Selesai bila:** satu pesanan antar terlacak sampai "diterima", dan satu
pesanan ambil diserahkan lewat pemindaian kode.

**Estimasi solo:** 3 minggu.

---

## Fase 8 — Notifikasi

- [ ] Email transaksional (Postmark / Resend / Amazon SES) — **jangan** SMTP
      hosting biasa.
- [ ] Atur SPF, DKIM, DMARC.
- [ ] Email: verifikasi akun, reset sandi, konfirmasi pesanan, bukti bayar,
      pesanan dikirim + resi, **siap diambil + kode**, selesai, dibatalkan.
- [ ] **WhatsApp** untuk "pesanan siap diambil" dan "kurir berangkat" — di
      Indonesia jauh lebih dibaca daripada email. Pakai penyedia resmi WhatsApp
      Business API.
- [ ] Notifikasi ke admin cabang: pesanan baru **di cabangnya**, stok menipis,
      produk mendekati kedaluwarsa, pesanan belum diambil mendekati batas.
- [ ] Semua email keluar lewat **queue**.

**Selesai bila:** setiap perubahan status memicu pesan yang benar ke pelanggan
**dan** ke cabang yang tepat.

**Estimasi solo:** 1–2 minggu.

---

## Fase 9 — Kepatuhan dan halaman legal

### 9.1 Halaman wajib

- [ ] **Syarat & Ketentuan**, termasuk kebijakan retur obat.
- [ ] **Kebijakan Privasi** sesuai **UU 27/2022 (PDP)**.
- [ ] **Kebijakan Pengiriman**, **Kebijakan Pengembalian Dana**.
- [ ] **Tentang Kami** dengan nama badan usaha dan kontak resmi.
- [ ] **Halaman Cabang** yang menampilkan, untuk setiap cabang: alamat, peta,
      jam buka, **nomor SIA**, **nama APJ dan nomor SIPA**. Ini membangun
      kepercayaan sekaligus umumnya diwajibkan.

### 9.2 Perlindungan data (UU PDP)

- [ ] Persetujuan eksplisit saat pendaftaran, tercatat dengan stempel waktu.
- [ ] **Persetujuan terpisah untuk data lokasi** — koordinat pelanggan adalah
      data pribadi, dan model ini mengumpulkannya sejak halaman pertama.
- [ ] Halaman privasi: unduh data saya, hapus akun saya.
- [ ] Kebijakan retensi.
- [ ] Enkripsi data sensitif saat disimpan.
- [ ] Rencana penanganan kebocoran data.
- [ ] Perjanjian pemrosesan data dengan setiap pihak ketiga.

### 9.3 Khusus farmasi

- [ ] Tampilkan **golongan obat** dan logonya di setiap halaman produk.
- [ ] Tampilkan **peringatan P1–P6** untuk obat bebas terbatas.
- [ ] Tampilkan **NIE BPOM** per produk.
- [ ] Cara menghubungi apoteker — **arahkan ke APJ cabang yang dipilih**.
- [ ] Sangkalan: informasi di situs bukan pengganti nasihat medis.
- [ ] Jejak audit untuk setiap perubahan data obat dan setiap penjualan,
      per cabang.

**Selesai bila:** konsultan perizinan meninjau situs dan tidak menemukan
penghalang.

**Estimasi solo:** 1–2 minggu teknis, plus waktu tunggu tinjauan.

---

## Fase 10 — Pengamanan

- [ ] HTTPS dipaksa, HSTS aktif.
- [ ] Header: CSP, X-Frame-Options, X-Content-Type-Options.
- [ ] Rate limiting pada login, pendaftaran, lupa sandi, **endpoint pencarian
      cabang** (endpoint lokasi mudah disalahgunakan untuk memetakan jaringan).
- [ ] CSRF (bawaan Laravel — pastikan tidak dimatikan).
- [ ] Validasi unggahan: tipe, ukuran, pindai isi.
- [ ] Semua kueri lewat Eloquent — jangan sambung string SQL.
- [ ] Kunci pihak ketiga hanya di `.env`; `.env` ada di `.gitignore`.
- [ ] `APP_DEBUG=false` di produksi.
- [ ] Backup harian otomatis, **dan uji pemulihannya**.
- [ ] `composer audit` dan `npm audit` rutin.
- [ ] Pertimbangkan uji penetrasi sebelum menerima pembayaran sungguhan.

**Selesai bila:** nilai A pada Mozilla Observatory, dan pemulihan backup pernah
berhasil.

**Estimasi solo:** 1 minggu.

---

## Fase 11 — Performa, SEO, analitik

- [ ] Pencarian produk ke sisi server dengan indeks; Meilisearch/Typesense bila
      katalog besar (utang teknis #7).
- [ ] **Kueri "cabang terdekat" harus pakai indeks spasial**, bukan menghitung
      jarak ke semua baris. Dengan 10 cabang apa pun jalan; dengan 1.000 ini
      menjadi kueri paling sering dan paling mahal di seluruh sistem.
- [ ] Cache daftar cabang per area — jarang berubah, sering diminta.
- [ ] Paginasi setiap daftar admin.
- [ ] *Eager loading* untuk menghindari N+1.
- [ ] Redis untuk cache katalog dan pengaturan.
- [ ] Optimalkan gambar: WebP, `loading="lazy"`, ukuran responsif.
- [ ] Target Lighthouse ≥ 90 di ponsel.
- [ ] **Latar bermerek di sisi kiri-kanan kanvas 430px** untuk pengunjung
      desktop, sesuai catatan di Fase 0.3.
- [ ] SEO: meta per produk, data terstruktur `Product` dan **`Pharmacy`
      (LocalBusiness) untuk setiap cabang** — ini yang memunculkan cabang Anda
      di Google Maps dan pencarian "apotek dekat saya", kanal akuisisi terbesar
      untuk model ini.
- [ ] Halaman landing per cabang dengan URL sendiri (`/cabang/otista`).
- [ ] Daftarkan setiap cabang di **Google Business Profile**.
- [ ] Analitik dengan pelacakan e-commerce; pisahkan metrik per cabang.
- [ ] Sentry/Flare untuk error, pemantauan uptime dengan notifikasi ke ponsel.

**Selesai bila:** katalog 1.000 produk × 10 cabang terasa cepat, dan cabang
muncul di pencarian lokal.

**Estimasi solo:** 2–3 minggu.

---

## Fase 12 — Pengujian dan CI

Sudah ada 119 pengujian — pertahankan kebiasaan itu.

- [ ] Pertahankan cakupan untuk setiap CRUD dan setiap alur uang.
- [ ] Uji khusus: perhitungan harga, aturan kupon, **pengurangan stok per
      cabang**, **cakupan cabang pada peran staf**, webhook pembayaran
      (termasuk pengiriman ulang), hitung ongkir, kedaluwarsa kode ambil.
- [ ] Uji ujung-ke-ujung (Playwright/Dusk) untuk dua alur: antar dan ambil.
- [ ] GitHub Actions: pengujian, Pint, `npm run build` pada setiap push.
- [ ] Blokir merge bila pengujian merah.

**Estimasi solo:** 1–2 minggu.

---

## Fase 13 — Deployment dan operasional

- [ ] Server produksi region Jakarta.
- [ ] Lingkungan **staging** dan **production** terpisah.
- [ ] Deployment tanpa henti.
- [ ] Supervisor untuk `queue:work`; scheduler untuk `schedule:run`
      (kedaluwarsa kode ambil dan pembatalan otomatis bergantung pada ini).
- [ ] Redis untuk cache, session, queue.
- [ ] SSL dengan perpanjangan otomatis.
- [ ] Backup harian ke lokasi terpisah, retensi 30 hari.
- [ ] `.env.example` lengkap (utang teknis #8).
- [ ] Runbook: cara deploy, rollback, pulihkan backup, siapa dihubungi saat
      gateway bermasalah, **dan cara menambah cabang baru**.

**Estimasi solo:** 1–2 minggu.

---

## Fase 14 — Checklist sebelum rilis

**Legal**
- [ ] SIA terbit untuk **setiap** cabang yang dijual daring.
- [ ] APJ dengan SIPA aktif terdaftar di **setiap** cabang.
- [ ] PSEF dan PSE Kominfo sesuai arahan konsultan.
- [ ] Halaman legal terbit dan ditinjau.
- [ ] Nomor SIA dan nama APJ tercantum di halaman setiap cabang.

**Uang**
- [ ] Kunci produksi gateway aktif; transaksi asli berhasil.
- [ ] Refund asli berhasil.
- [ ] PPN dikonfirmasi konsultan pajak.
- [ ] Rekening penyelesaian benar; pemecahan setoran per cabang jelas.

**Data**
- [ ] Katalog asli lengkap dengan NIE, golongan, foto.
- [ ] **Stok awal dan batch dimasukkan untuk setiap cabang.**
- [ ] **Koordinat setiap cabang diverifikasi di peta** — satu koordinat salah
      berarti pelanggan diarahkan ke cabang yang jauh.
- [ ] Jam operasional setiap cabang benar.
- [ ] Semua data contoh dihapus — tidak ada "Kirana Wijaya" tersisa.

**Teknis**
- [ ] `APP_DEBUG=false`, `APP_ENV=production`.
- [ ] Backup berjalan dan pemulihan diuji.
- [ ] Pemantauan error dan uptime aktif.
- [ ] SSL valid, header keamanan lulus.
- [ ] Alur checkout diuji di ponsel Android dan iOS sungguhan, **kedua mode**.
- [ ] Email tidak masuk spam.

**Operasional**
- [ ] Staf **setiap cabang** dilatih memakai panel dan alur serah-terima.
- [ ] Jam operasional dan waktu respons ditetapkan.
- [ ] Kontak layanan pelanggan aktif.
- [ ] Prosedur bila apoteker cabang tidak tersedia.

---

## Fase 15 — Setelah rilis dan jalan menuju 1.000 cabang

### 15.1 Segera setelah rilis

- [ ] Pantau ketat 72 jam pertama.
- [ ] Jalur rollback cepat siap.
- [ ] Kumpulkan masukan pelanggan sejak hari pertama.
- [ ] Tinjau pesanan gagal, keranjang ditinggalkan, dan **pesanan tidak diambil**
      setiap minggu.

### 15.2 Yang dibutuhkan untuk tumbuh dari 10 ke 1.000

Sebagian besar ini **tidak perlu dibangun sekarang** — tapi jangan membuat
keputusan yang menghalanginya:

- [ ] **Onboarding cabang mandiri** — menambah cabang harus jadi pekerjaan
      15 menit lewat panel, bukan migrasi basis data.
- [ ] **Hierarki wilayah**: wilayah → area → cabang, untuk laporan dan peran
      Manajer Area.
- [ ] **Routing pesanan otomatis** — bila cabang terdekat kehabisan stok,
      tawarkan cabang berikutnya secara otomatis.
- [ ] **Gudang pusat** yang memasok cabang, dengan usulan pengisian ulang
      otomatis berbasis laju penjualan.
- [ ] **Harga dan promo per wilayah.**
- [ ] **Aplikasi mobile** — pada skala ini biasanya terbayar.
- [ ] **Waralaba/kemitraan** bila sebagian cabang dimiliki mitra: butuh
      pemisahan kepemilikan, bagi hasil, dan penyelesaian dana. Ini pada
      dasarnya mengubah model menjadi *marketplace* dan kembali ke Fase 0.
- [ ] **Obat keras dengan resep** — butuh unggah resep, antrean verifikasi APJ,
      dan tanda tangan elektronik. Kembali ke Fase 0 untuk perizinannya.

### 15.3 Keputusan arsitektur yang menjaga pintu tetap terbuka

Yang penting dilakukan **sekarang** supaya 1.000 cabang tidak menyakitkan nanti:

1. **Jangan pernah menaruh `stock` di tabel produk.** Sudah dibahas di Fase 2.
2. **Semua kueri stok dan pesanan wajib melewati `branch_id`.** Jangan pernah
   menulis kueri yang mengasumsikan satu cabang.
3. **Indeks spasial sejak awal**, walau dengan 10 cabang terasa berlebihan.
4. **Cakupan cabang di lapisan query**, bukan di antarmuka.
5. **Jam operasional sebagai data, bukan teks.** Suatu saat perlu dibaca mesin
   untuk memutuskan cabang mana bisa menerima pesanan sekarang.

---

## Ringkasan urutan dan estimasi

Untuk satu pengembang, berurutan, kerja penuh waktu.

| Fase | Isi | Estimasi | Bisa dilewati? |
| --- | --- | --- | --- |
| 0 | Keputusan & perizinan | paralel | **Tidak** |
| 1 | Basis data & model | 3–5 minggu | **Tidak** |
| 2 | **Cabang, stok per cabang, lokasi** | 4–5 minggu | **Tidak** |
| 3 | Auth & otorisasi + cakupan cabang | 2–3 minggu | **Tidak** |
| 4 | Katalog sesungguhnya | 3 minggu | **Tidak** |
| 5 | Alur belanja + pilih cabang | 3–4 minggu | **Tidak** |
| 6 | Pembayaran | 2 minggu | **Tidak** |
| 7 | Antar & ambil di toko | 3 minggu | Ambil dulu, antar nanti |
| 8 | Notifikasi | 1–2 minggu | Email wajib, WA nanti |
| 9 | Kepatuhan | 1–2 minggu + tinjauan | **Tidak** |
| 10 | Pengamanan | 1 minggu | **Tidak** |
| 11 | Performa & SEO lokal | 2–3 minggu | Bertahap |
| 12 | Pengujian & CI | 1–2 minggu | Bertahap |
| 13 | Deployment | 1–2 minggu | **Tidak** |
| 14 | Checklist rilis | 3–5 hari | **Tidak** |

**Total kasar: 7–9 bulan** kerja penuh waktu untuk satu orang, dengan perizinan
berjalan paralel. Multi-cabang menambah sekitar 2 bulan dibanding apotek tunggal
— itu harga dari model bisnis Anda, dan tidak bisa ditawar tanpa mengorbankan
inti produknya.

### Jalur tercepat menuju "bisa menerima uang"

Bila ingin memvalidasi pasar lebih dulu:

**Fase 0 → 1 → 2 → 3 → 5 → 6 → 9 → 10 → 13 → 14**

Dengan penyederhanaan:

- **Mulai dengan 2–3 cabang saja**, bukan sepuluh. Alur multi-cabang terbukti,
  perizinan lebih ringan, dan menambah cabang berikutnya jadi pekerjaan data.
- **Ambil di toko dulu, antar menyusul.** *Pickup* jauh lebih sederhana: tanpa
  integrasi kurir, tanpa hitung ongkir, tanpa radius. Ini memangkas hampir
  seluruh Fase 7.
- Fase 4 dikurangi seperlunya, Fase 8 hanya email, Fase 11 dan 12 menyusul.

Perkiraan **4–5 bulan**.

Yang **tidak boleh** dilewati: perizinan (0), basis data (1), model cabang (2),
autentikasi (3), pembayaran yang benar (6), kepatuhan (9), pengamanan (10).

---

## Lampiran A — Daftar cabang saat ini

Sepuluh cabang Jabodetabek. Ini menjadi data seed di Fase 1.3 dan 2.1.

**Koordinat masih kosong** — isi manual dari Google Maps sebelum Fase 2.3,
karena seluruh fitur "cabang terdekat" bergantung padanya.

| # | Nama | Alamat | Kota | Lat/Lng |
| --- | --- | --- | --- | --- |
| 1 | Apotek Inofarma Kapten Yusuf | Jl. Kapten Yusuf RT 001 RW 007, Kel. Cikaret, Kec. Bogor Selatan | Kota Bogor, Jawa Barat | _isi_ |
| 2 | Apotek Inofarma Otista | Jl. Otista Raya No.27A, Ciputat, Kec. Ciputat | Kota Tangerang Selatan, Banten 15411 | _isi_ |
| 3 | Apotek Inofarma Darul Fallah | Jl. Mesjid Darul Falah No.27, RT.4/RW.2, Petukangan Utara, Kec. Pesanggrahan | Jakarta Selatan, DKI Jakarta 12260 | _isi_ |
| 4 | Apotek Inofarma Parakan | Jl. Parakan No.101e, RT.1/RW.1, Pd. Benda, Kec. Pamulang | Kota Tangerang Selatan, Banten 15416 | _isi_ |
| 5 | Apotek Inofarma Syahdan | Jl. Kyai H. Syahdan No.2, RT.2/RW.11, Palmerah, Kec. Palmerah | Jakarta Barat, DKI Jakarta 11480 | _isi_ |
| 6 | Apotek Inofarma Taruna Jaya | Jl. Taruna Jaya 4 No.6, RT.7/RW.5, Cibubur, Kec. Ciracas | Jakarta Timur, DKI Jakarta 13720 | _isi_ |
| 7 | Apotek Inofarma Keamanan | Jl. Keamanan No.59, RT.001/RW.007, Keagungan, Kec. Taman Sari | Jakarta Barat, DKI Jakarta 11130 | _isi_ |
| 8 | Apotek Inofarma Pulo Gebang | Jl. Raya Pulo Gebang No.21, RT.1/RW.6, Pulo Gebang, Kec. Cakung | Jakarta Timur, DKI Jakarta 13950 | _isi_ |
| 9 | Apotek Inofarma Duri Kepa | Jl. Duri Raya No.5, RT.6/RW.1, Duri Kepa, Kec. Kb. Jeruk | Jakarta Barat, DKI Jakarta 11510 | _isi_ |
| 10 | Apotek Inofarma Kebagusan | Jl. Kebagusan Raya, RT.7/RW.6, Kebagusan, Ps. Minggu | Jakarta Selatan, DKI Jakarta 12520 | _isi_ |

Untuk setiap cabang, sebelum rilis juga perlu: **nomor SIA**, **nama APJ**,
**nomor SIPA**, **nomor telepon/WhatsApp**, **jam operasional per hari**, dan
**radius antar**.

---

## Bagaimana memakai dokumen ini

1. Baca [bagian 3](#3-model-multi-cabang-apa-yang-berubah) lebih dulu — di
   situlah model bisnis Anda bertemu arsitektur.
2. Jawab enam keputusan produk di Fase 0.3.
3. Ajukan perizinan **sekarang**, terutama SIA dan APJ per cabang — ini proses
   terpanjang dan memblokir rilis.
4. Mulai Fase 1, lalu langsung Fase 2. Jangan menambah fitur di atas asumsi
   "satu stok global"; semuanya harus ditulis ulang.

Bila ada bagian yang ingin diperdalam, dipecah lebih rinci, atau dihapus karena
tidak relevan, beri tahu saya dan dokumen ini saya sesuaikan.
