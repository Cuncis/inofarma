<?php

namespace App\Support;

/**
 * The text the four document pages shipped with, shown until staff save their
 * own under Konten Website. Kept apart from {@see DocumentPageContent} only
 * because it is long. Section and clause numbers are not stored: the page
 * numbers them in order.
 */
class DocumentPageDefaults
{
    public const SHIPPING = [
        'title' => 'Kebijakan Pengiriman',
        'sections' => [
            0 => [
                'title' => 'Ongkos Kirim',
                'body' => 'Ongkos kirim dihitung langsung dari cabang yang Anda pilih ke alamat tujuan, bukan tarif rata, dan berbeda-beda tergantung jarak, berat, dan kurir yang Anda pilih saat checkout (termasuk kurir instan seperti Gojek/Grab untuk pengiriman cepat di sekitar cabang).',
            ],
            1 => [
                'title' => 'Area Pengiriman',
                'body' => 'Setiap cabang punya radius antar sendiri. Alamat di luar radius cabang yang Anda pilih tidak bisa diproses untuk pengiriman, pilih cabang lain yang lebih dekat dengan alamat Anda, atau pilih Ambil di Toko.',
            ],
            2 => [
                'title' => 'Estimasi Waktu Kirim',
                'body' => 'Estimasi waktu tiba mengikuti perkiraan dari kurir yang Anda pilih saat checkout, ditampilkan sebelum Anda membayar. Setelah pesanan diserahkan ke kurir, status pengiriman diperbarui otomatis sampai barang diterima.',
            ],
            3 => [
                'title' => 'Batas Waktu Pembayaran',
                'body' => 'Pesanan yang belum dibayar dalam 24 jam dibatalkan otomatis dan stok dikembalikan, supaya barang tidak tertahan untuk pesanan yang tidak jadi dibayar.',
            ],
            4 => [
                'title' => 'Ambil di Toko',
                'body' => 'Setelah pesanan disiapkan cabang, Anda mendapat kode ambil 6 digit (dan QR) yang berlaku 48 jam. Tunjukkan ke kasir untuk mengambil pesanan.',
            ],
            5 => [
                'title' => 'Metode Pembayaran',
                'body' => 'Pembayaran online diproses DOKU: transfer bank, e-wallet (GoPay, OVO, DANA, dll.), QRIS, dan kartu debit/kredit. Semua diproses langsung di halaman DOKU, tidak lewat server kami. Pesanan Ambil di Toko juga bisa dibayar tunai saat pengambilan. Semua transaksi online dilindungi enkripsi standar industri milik DOKU.',
            ],
        ],
    ];

    public const REFUND = [
        'title' => 'Kebijakan Pengembalian Dana',
        'sections' => [
            0 => [
                'title' => 'Kapan Dana Dikembalikan',
                'body' => 'Dana dikembalikan bila: pesanan dibatalkan sebelum diproses cabang, pesanan kedaluwarsa (melewati batas waktu bayar atau batas waktu ambil) padahal sudah terlanjur dibayar, atau klaim retur obat pada Syarat & Ketentuan §7 disetujui cabang.',
            ],
            1 => [
                'title' => 'Cara Dana Dikembalikan',
                'body' => 'Untuk pembayaran online lewat DOKU (transfer bank, e-wallet, QRIS, kartu), dana dikembalikan ke metode pembayaran yang sama yang Anda pakai saat membayar. Untuk pesanan yang dibayar tunai di kasir, dana dikembalikan tunai atau transfer ke rekening yang Anda konfirmasi ke cabang.',
            ],
            2 => [
                'title' => 'Berapa Lama',
                'body' => 'Pengembalian dana dicatat dan diproses oleh staf cabang setelah dana secara nyata sudah dikirim kembali, bukan otomatis lewat sistem pembayaran. Perkirakan 3-14 hari kerja tergantung metode pembayaran awal dan bank/e-wallet penerima. Status "Refund" pada halaman Lacak Pesanan berarti pengembalian sudah tercatat oleh cabang.',
            ],
            3 => [
                'title' => 'Stok pada Pesanan yang Di-refund',
                'body' => 'Stok yang sudah dikonsumsi untuk pesanan yang di-refund karena klaim retur tidak dikembalikan otomatis ke rak, karena barangnya sudah pernah berpindah tangan ke pelanggan. Untuk pembatalan/kedaluwarsa sebelum barang berpindah tangan, stok dikembalikan otomatis sejak awal.',
            ],
            4 => [
                'title' => 'Pertanyaan Status Refund',
                'body' => 'Hubungi cabang tempat pesanan Anda dibuat, nomor telepon dan WhatsApp cabang tersedia di halaman tiap apotek (bagian Apotek Inofarma Terdekat di beranda) dan pada halaman Lacak Pesanan Anda.',
            ],
        ],
    ];

    public const TERMS = [
        'title' => 'Syarat & Ketentuan Inofarma',
        'contactHeading' => 'Untuk pertanyaan, saran, atau keluhan terkait penggunaan Situs dan Layanan Inofarma, Pengguna dapat menghubungi:',
        'updatedAt' => 'November 2025',
        'sections' => [
            0 => [
                'title' => 'Pendahuluan',
                'clauses' => [
                    0 => [
                        'text' => 'Dokumen ini merupakan perjanjian hukum antara Pengguna (“Anda”) dan Apotek Inofarma (“Inofarma”), yang mengatur hak dan kewajiban dalam penggunaan situs web www.inofarma.com (“Situs”) serta seluruh layanan yang disediakan di dalamnya (“Layanan”).',
                        'items' => '',
                    ],
                    1 => [
                        'text' => 'Dengan mengakses atau menggunakan Situs, Anda dianggap telah membaca, memahami, dan menyetujui seluruh isi Syarat dan Ketentuan ini.',
                        'items' => '',
                    ],
                    2 => [
                        'text' => 'Apabila Anda tidak menyetujui sebagian atau seluruh isi dari Syarat dan Ketentuan ini, Anda tidak diperkenankan untuk menggunakan Situs maupun Layanan yang disediakan oleh Inofarma.',
                        'items' => '',
                    ],
                    3 => [
                        'text' => 'Inofarma berhak mengubah, menambah, atau memperbarui Syarat dan Ketentuan ini sewaktu-waktu tanpa pemberitahuan sebelumnya. Perubahan tersebut akan berlaku sejak tanggal pembaruan diumumkan di Situs.',
                        'items' => '',
                    ],
                ],
            ],
            1 => [
                'title' => 'Definisi',
                'clauses' => [
                    0 => [
                        'text' => 'Dalam Syarat dan Ketentuan ini, istilah-istilah berikut memiliki arti sebagai berikut:',
                        'items' => '“Situs” berarti situs resmi Apotek Inofarma yang beralamat di www.inofarma.com
“Layanan” berarti seluruh fasilitas yang disediakan Inofarma melalui Situs, termasuk namun tidak terbatas pada pendaftaran akun, pembelian produk, konsultasi apoteker, dan sistem pembayaran online.
“Pengguna” berarti setiap individu yang mengakses, menggunakan, atau melakukan transaksi melalui Situs.
“Akun” berarti identitas digital yang dibuat oleh Pengguna untuk mengakses Layanan yang disediakan di Situs.
“Produk” berarti barang atau jasa kesehatan yang ditawarkan melalui Situs, termasuk obat-obatan, suplemen, alat kesehatan, dan produk farmasi lainnya.',
                    ],
                ],
            ],
            2 => [
                'title' => 'Ketentuan Umum Penggunaan Situs',
                'clauses' => [
                    0 => [
                        'text' => 'Pengguna wajib berusia sekurang-kurangnya 18 (delapan belas) tahun, atau memiliki izin dari orang tua/wali sah apabila berusia di bawah ketentuan tersebut.',
                        'items' => '',
                    ],
                    1 => [
                        'text' => 'Pengguna wajib memberikan informasi yang benar, lengkap, dan terkini dalam setiap penggunaan Layanan.',
                        'items' => '',
                    ],
                    2 => [
                        'text' => 'Pengguna bertanggung jawab penuh atas keamanan dan kerahasiaan akun, termasuk kata sandi. Segala aktivitas yang dilakukan melalui akun Pengguna akan dianggap sebagai tindakan yang sah dari Pengguna yang bersangkutan.',
                        'items' => '',
                    ],
                    3 => [
                        'text' => 'Inofarma berhak menonaktifkan atau menghapus akun Pengguna apabila ditemukan pelanggaran terhadap Syarat dan Ketentuan ini, atau terdapat indikasi penyalahgunaan akun.',
                        'items' => '',
                    ],
                ],
            ],
            3 => [
                'title' => 'Ketentuan Pembelian Produk',
                'clauses' => [
                    0 => [
                        'text' => 'Pengguna dapat melakukan pembelian Produk melalui Situs sesuai dengan prosedur transaksi yang telah ditetapkan oleh Inofarma.',
                        'items' => '',
                    ],
                    1 => [
                        'text' => 'Harga Produk yang tercantum di Situs sudah termasuk pajak sesuai dengan ketentuan peraturan perundang-undangan yang berlaku.',
                        'items' => '',
                    ],
                    2 => [
                        'text' => 'Pembayaran hanya dapat dilakukan melalui metode resmi yang disediakan oleh Inofarma.',
                        'items' => '',
                    ],
                    3 => [
                        'text' => 'Inofarma berhak menolak atau membatalkan pesanan apabila:',
                        'items' => 'Produk tidak tersedia;
Terdapat kesalahan harga atau informasi produk;
Terdapat indikasi penyalahgunaan atau pelanggaran hukum oleh Pengguna.',
                    ],
                ],
            ],
            4 => [
                'title' => 'Pengiriman Produk',
                'clauses' => [
                    0 => [
                        'text' => 'Pengiriman Produk dilakukan melalui mitra logistik resmi yang bekerja sama dengan Inofarma.',
                        'items' => '',
                    ],
                    1 => [
                        'text' => 'Estimasi waktu pengiriman dapat berbeda-beda tergantung pada lokasi tujuan, ketersediaan produk, dan kondisi operasional pihak pengiriman.',
                        'items' => '',
                    ],
                    2 => [
                        'text' => 'Inofarma tidak bertanggung jawab atas keterlambatan pengiriman yang disebabkan oleh faktor eksternal di luar kendali Inofarma, termasuk namun tidak terbatas pada bencana alam, gangguan sistem, atau kebijakan pemerintah (force majeure).',
                        'items' => '',
                    ],
                    3 => [
                        'text' => 'Pengguna wajib memastikan alamat pengiriman yang diberikan sudah benar dan lengkap. Kesalahan alamat menjadi tanggung jawab Pengguna sepenuhnya.',
                        'items' => '',
                    ],
                ],
            ],
            5 => [
                'title' => 'Kebijakan Pengembalian dan Pembatalan',
                'clauses' => [
                    0 => [
                        'text' => 'Pembatalan pesanan hanya dapat dilakukan sebelum pesanan diproses atau dikirim.',
                        'items' => '',
                    ],
                    1 => [
                        'text' => 'Produk yang sudah diterima tidak dapat dikembalikan kecuali dalam kondisi:',
                        'items' => 'Produk rusak akibat kesalahan pengiriman;
Produk salah kirim;
Produk melewati tanggal kadaluarsa saat diterima.',
                    ],
                    2 => [
                        'text' => 'Pengguna wajib melaporkan keluhan pengembalian paling lambat 2x24 jam setelah produk diterima, disertai bukti foto dan nomor pesanan.',
                        'items' => '',
                    ],
                    3 => [
                        'text' => 'Proses pengembalian dana atau penggantian produk akan dilakukan sesuai dengan kebijakan internal Inofarma setelah proses verifikasi selesai.',
                        'items' => '',
                    ],
                ],
            ],
            6 => [
                'title' => 'Tanggung Jawab dan Batasan Tanggung Jawab',
                'clauses' => [
                    0 => [
                        'text' => 'Inofarma berkomitmen untuk menyediakan informasi produk yang akurat dan layanan yang berkualitas tinggi. Namun demikian, Inofarma tidak menjamin bahwa seluruh informasi di Situs bebas dari kesalahan atau kekeliruan teknis.',
                        'items' => '',
                    ],
                    1 => [
                        'text' => 'Inofarma tidak bertanggung jawab atas:',
                        'items' => 'Kerugian langsung maupun tidak langsung yang timbul akibat penggunaan Situs;
Gangguan teknis yang disebabkan oleh faktor eksternal;
Penggunaan Produk yang tidak sesuai dengan petunjuk dokter atau apoteker.',
                    ],
                    2 => [
                        'text' => 'Pengguna setuju bahwa penggunaan Situs dilakukan dengan risiko yang sepenuhnya ditanggung oleh Pengguna.',
                        'items' => '',
                    ],
                ],
            ],
            7 => [
                'title' => 'Hak Kekayaan Intelektual',
                'clauses' => [
                    0 => [
                        'text' => 'Seluruh logo, merek dagang, desain, teks, gambar, serta materi lain yang terdapat di Situs merupakan milik Inofarma dan dilindungi oleh undang-undang hak cipta dan kekayaan intelektual yang berlaku di Republik Indonesia.',
                        'items' => '',
                    ],
                    1 => [
                        'text' => 'Pengguna dilarang menyalin, memperbanyak, menyebarkan, memodifikasi, atau menggunakan materi tersebut untuk kepentingan apa pun tanpa izin tertulis dari Inofarma.',
                        'items' => '',
                    ],
                ],
            ],
            8 => [
                'title' => 'Larangan Penggunaan',
                'clauses' => [
                    0 => [
                        'text' => 'Pengguna dilarang menggunakan Situs untuk tujuan yang bertentangan dengan hukum, termasuk namun tidak terbatas pada:',
                        'items' => 'Mengunggah konten yang mengandung SARA, pornografi, kekerasan, atau kebencian;
Melakukan penipuan atau penyalahgunaan data;
Mengganggu sistem keamanan, server, atau jaringan milik Inofarma.',
                    ],
                    1 => [
                        'text' => 'Pelanggaran terhadap ketentuan ini dapat mengakibatkan penghentian akun dan/atau tindakan hukum sesuai peraturan yang berlaku.',
                        'items' => '',
                    ],
                ],
            ],
            9 => [
                'title' => 'Perubahan Layanan',
                'clauses' => [
                    0 => [
                        'text' => 'Inofarma berhak untuk menambah, mengubah, menangguhkan, atau menghentikan sebagian maupun seluruh Layanan di Situs sewaktu-waktu dengan atau tanpa pemberitahuan terlebih dahulu.',
                        'items' => '',
                    ],
                    1 => [
                        'text' => 'Pengguna setuju bahwa Inofarma tidak bertanggung jawab atas kerugian apa pun yang timbul akibat perubahan atau penghentian Layanan tersebut.',
                        'items' => '',
                    ],
                ],
            ],
            10 => [
                'title' => 'Hukum yang Berlaku dan Penyelesaian Sengketa',
                'clauses' => [
                    0 => [
                        'text' => 'Syarat dan Ketentuan ini diatur dan ditafsirkan berdasarkan hukum Republik Indonesia.',
                        'items' => '',
                    ],
                    1 => [
                        'text' => 'Setiap sengketa yang timbul akibat pelaksanaan atau penafsiran dokumen ini akan diselesaikan terlebih dahulu secara musyawarah untuk mufakat.',
                        'items' => '',
                    ],
                    2 => [
                        'text' => 'Apabila penyelesaian secara musyawarah tidak tercapai, maka sengketa akan diselesaikan melalui lembaga peradilan yang berwenang di wilayah hukum Republik Indonesia.',
                        'items' => '',
                    ],
                ],
            ],
            11 => [
                'title' => 'Kontak dan Layanan Pelanggan',
                'clauses' => [
                    0 => [
                        'text' => 'Untuk pertanyaan, saran, atau keluhan terkait penggunaan Situs dan Layanan Inofarma, Pengguna dapat menghubungi:',
                        'items' => '',
                    ],
                ],
            ],
        ],
    ];

    public const PRIVACY = [
        'title' => 'Kebijakan Privasi Inofarma',
        'contactHeading' => 'Untuk pertanyaan, permintaan, atau keluhan terkait pengelolaan Data Pribadi, Pengguna dapat menghubungi:',
        'updatedAt' => 'November 2025',
        'sections' => [
            0 => [
                'title' => 'Pendahuluan',
                'clauses' => [
                    0 => [
                        'text' => 'Apotek Inofarma (“Inofarma”) adalah jaringan apotek yang berdedikasi untuk meningkatkan kesehatan dan kesejahteraan masyarakat di seluruh Indonesia melalui penyediaan produk kesehatan berkualitas tinggi, harga yang terjangkau, dan layanan farmasi profesional oleh apoteker.',
                        'items' => '',
                    ],
                    1 => [
                        'text' => 'Inofarma beroperasi di bawah naungan PT Teknologi Medika Pratama (TMP), perusahaan yang berdiri sejak tahun 2021 dan memiliki jaringan operasional di berbagai provinsi di Indonesia. TMP berkomitmen untuk memperkuat layanan kesehatan masyarakat melalui pengembangan jaringan apotek ritel independen yang profesional dan terstandarisasi.',
                        'items' => '',
                    ],
                    2 => [
                        'text' => 'Dengan mengakses dan menggunakan situs web www.inofarma.com (“Situs”), serta mendaftar sebagai anggota atau melakukan transaksi melalui platform ini (“Layanan”), Anda menyetujui untuk terikat dengan ketentuan dalam Kebijakan Privasi ini.',
                        'items' => '',
                    ],
                    3 => [
                        'text' => 'Kebijakan Privasi ini menjelaskan bagaimana Inofarma mengumpulkan, menggunakan, menyimpan, melindungi, dan mengungkapkan data pribadi Anda dengan Undang-Undang Nomor 27 Tahun 2022 tentang Perlindungan Data Pribadi (UU PDP) dan peraturan terkait lainnya.',
                        'items' => '',
                    ],
                    4 => [
                        'text' => 'Jika Anda tidak menyetujui sebagian atau seluruh isi dari Kebijakan Privasi ini, Anda disarankan untuk tidak menggunakan Situs atau Layanan yang disediakan oleh Inofarma.',
                        'items' => '',
                    ],
                ],
            ],
            1 => [
                'title' => 'Jenis Data Pribadi yang Dikumpulkan',
                'clauses' => [
                    0 => [
                        'text' => 'Inofarma dapat mengumpulkan data pribadi dari Pengguna (“Data Pribadi”) ketika Pengguna melakukan pendaftaran akun, transaksi, atau berinteraksi dengan Layanan.',
                        'items' => '',
                    ],
                    1 => [
                        'text' => 'Data Pribadi yang dikumpulkan meliputi namun tidak terbatas pada:',
                        'items' => 'Data Identitas Pribadi, seperti nama lengkap, tanggal lahir, dan nomor telepon.
Data Kontak, seperti alamat email, alamat pengiriman, dan informasi wilayah.
Data Transaksi, meliputi riwayat pembelian, metode pembayaran, serta status pengiriman.
Data Akun, seperti nama pengguna (username), kata sandi, dan preferensi akun.
Data Kesehatan Terkait, apabila diperlukan untuk pembelian obat dengan resep dokter.
Data Teknis, seperti alamat IP, tipe perangkat, dan aktivitas pada Situs.',
                    ],
                    2 => [
                        'text' => 'Data dapat dikumpulkan secara langsung dari Pengguna maupun secara otomatis melalui penggunaan Situs.',
                        'items' => '',
                    ],
                ],
            ],
            2 => [
                'title' => 'Tujuan Penggunaan Data Pribadi',
                'clauses' => [
                    0 => [
                        'text' => 'Inofarma menggunakan Data Pribadi Pengguna untuk tujuan sebagai berikut:',
                        'items' => 'Memproses pendaftaran akun dan keanggotaan Pengguna di Inofarma.
Memproses transaksi, pembayaran, dan pengiriman produk kepada Pengguna.
Menyampaikan informasi mengenai produk, promosi, dan penawaran khusus.
Memberikan dukungan layanan pelanggan serta menindaklanjuti keluhan atau permintaan informasi.
Melakukan verifikasi dan validasi terhadap transaksi atau akun Pengguna.
Meningkatkan kualitas Layanan, termasuk melalui analisis perilaku pengguna.
Memenuhi kewajiban hukum dan peraturan yang berlaku.',
                    ],
                    1 => [
                        'text' => 'Penggunaan Data Pribadi dilakukan secara sah, transparan, dan proporsional sesuai dengan tujuan yang telah disebutkan di atas.',
                        'items' => '',
                    ],
                ],
            ],
            3 => [
                'title' => 'Pengungkapan Data Pribadi kepada Pihak Ketiga',
                'clauses' => [
                    0 => [
                        'text' => 'Inofarma tidak akan menjual, menyewakan, atau memperdagangkan Data Pribadi Pengguna kepada pihak mana pun.',
                        'items' => '',
                    ],
                    1 => [
                        'text' => 'Inofarma dapat memberikan Data Pribadi kepada pihak ketiga yang bekerja sama untuk mendukung pelaksanaan Layanan, termasuk namun tidak terbatas pada:',
                        'items' => 'Penyedia jasa logistik atau kurir pengiriman.
Mitra pembayaran atau penyedia layanan transaksi keuangan.
Penyedia layanan teknologi informasi atau penyimpanan data.
Otoritas hukum, apabila diwajibkan berdasarkan peraturan perundang-undangan.',
                    ],
                    2 => [
                        'text' => 'Pihak ketiga yang menerima Data Pribadi diwajibkan untuk menjaga kerahasiaan dan keamanan data sesuai dengan standar yang ditetapkan oleh Inofarma dan ketentuan hukum yang berlaku.',
                        'items' => '',
                    ],
                ],
            ],
            4 => [
                'title' => 'Penyimpanan dan Keamanan Data',
                'clauses' => [
                    0 => [
                        'text' => 'Inofarma menerapkan langkah-langkah keamanan fisik, elektronik, dan administratif untuk melindungi Data Pribadi Pengguna dari kehilangan, penyalahgunaan, atau akses tidak sah.',
                        'items' => '',
                    ],
                    1 => [
                        'text' => 'Data Pribadi disimpan dalam sistem yang terenkripsi dan hanya dapat diakses oleh personel berwenang untuk kepentingan operasional yang sah.',
                        'items' => '',
                    ],
                    2 => [
                        'text' => 'Meskipun Inofarma telah menerapkan standar keamanan terbaik, Pengguna diharapkan tetap menjaga kerahasiaan akun dan kata sandi masing-masing.',
                        'items' => '',
                    ],
                ],
            ],
            5 => [
                'title' => 'Hak Pengguna atas Data Pribadi',
                'clauses' => [
                    0 => [
                        'text' => 'Pengguna memiliki hak-hak sebagai berikut:',
                        'items' => 'Hak untuk mengakses Data Pribadi yang dimiliki oleh Inofarma.
Hak untuk memperbarui, memperbaiki, atau mengubah Data Pribadi yang tidak akurat.
Hak untuk menarik kembali persetujuan terhadap penggunaan Data Pribadi.
Hak untuk menghapus Data Pribadi sesuai dengan ketentuan hukum yang berlaku.',
                    ],
                    1 => [
                        'text' => 'Permintaan terkait hak-hak tersebut dapat diajukan melalui email resmi Inofarma di cs@inofarma.com atau 0812-1401-8964',
                        'items' => '',
                    ],
                ],
            ],
            6 => [
                'title' => 'Retensi Data',
                'clauses' => [
                    0 => [
                        'text' => 'Inofarma akan menyimpan Data Pribadi selama akun Pengguna aktif atau selama diperlukan untuk memenuhi tujuan pengumpulan data.',
                        'items' => '',
                    ],
                    1 => [
                        'text' => 'Setelah akun ditutup, Data Pribadi akan dihapus secara aman setelah jangka waktu penyimpanan yang diwajibkan oleh peraturan perundang-undangan berakhir.',
                        'items' => '',
                    ],
                ],
            ],
            7 => [
                'title' => 'Perubahan atas Kebijakan Privasi',
                'clauses' => [
                    0 => [
                        'text' => 'Inofarma berhak mengubah atau memperbarui Kebijakan Privasi ini sewaktu-waktu untuk menyesuaikan dengan ketentuan hukum, kebijakan internal, atau kebutuhan operasional.',
                        'items' => '',
                    ],
                    1 => [
                        'text' => 'Setiap perubahan akan diumumkan melalui Situs resmi Inofarma dan mulai berlaku sejak tanggal pembaruan ditampilkan.',
                        'items' => '',
                    ],
                ],
            ],
            8 => [
                'title' => 'Kontak dan Pertanyaan',
                'clauses' => [
                    0 => [
                        'text' => 'Untuk pertanyaan, permintaan, atau keluhan terkait pengelolaan Data Pribadi, Pengguna dapat menghubungi:',
                        'items' => '',
                    ],
                ],
            ],
        ],
    ];
}
