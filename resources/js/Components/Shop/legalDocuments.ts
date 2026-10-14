export interface LegalClause {
    /** Clause text, numbered like "1.1" by the caller's data. */
    number: string;
    text: string;
    /** Optional bullet list that follows the clause text. */
    items?: string[];
}

export interface LegalSection {
    title: string;
    clauses: LegalClause[];
}

export interface LegalDocument {
    title: string;
    sections: LegalSection[];
    contactHeading: string;
    updatedAt: string;
}

export type LegalDocumentKey = 'privacy' | 'terms';

export const company = {
    name: 'PT Teknologi Medika Pratama',
    email: 'cs@inofarma.com',
    phone: '0812-1401-8964',
    address: 'Berrybenka Headquarter Lantai 1, Jl. KH Mas Mansyur No.19, Desa/Kelurahan Karet Tengsin, Kec. Tanah Abang, Kota Adm. Jakarta Pusat, Provinsi DKI Jakarta, Kode Pos: 10220',
};

export const legalDocuments: Record<LegalDocumentKey, LegalDocument> = {
    privacy: {
        title: 'Kebijakan Privasi Inofarma',
        contactHeading: 'Untuk pertanyaan, permintaan, atau keluhan terkait pengelolaan Data Pribadi, Pengguna dapat menghubungi:',
        updatedAt: 'November 2025',
        sections: [
            {
                title: '1. Pendahuluan',
                clauses: [
                    { number: '1.1', text: 'Apotek Inofarma (“Inofarma”) adalah jaringan apotek yang berdedikasi untuk meningkatkan kesehatan dan kesejahteraan masyarakat di seluruh Indonesia melalui penyediaan produk kesehatan berkualitas tinggi, harga yang terjangkau, dan layanan farmasi profesional oleh apoteker.' },
                    { number: '1.2', text: 'Inofarma beroperasi di bawah naungan PT Teknologi Medika Pratama (TMP), perusahaan yang berdiri sejak tahun 2021 dan memiliki jaringan operasional di berbagai provinsi di Indonesia. TMP berkomitmen untuk memperkuat layanan kesehatan masyarakat melalui pengembangan jaringan apotek ritel independen yang profesional dan terstandarisasi.' },
                    { number: '1.3', text: 'Dengan mengakses dan menggunakan situs web www.inofarma.com (“Situs”), serta mendaftar sebagai anggota atau melakukan transaksi melalui platform ini (“Layanan”), Anda menyetujui untuk terikat dengan ketentuan dalam Kebijakan Privasi ini.' },
                    { number: '1.4', text: 'Kebijakan Privasi ini menjelaskan bagaimana Inofarma mengumpulkan, menggunakan, menyimpan, melindungi, dan mengungkapkan data pribadi Anda dengan Undang-Undang Nomor 27 Tahun 2022 tentang Perlindungan Data Pribadi (UU PDP) dan peraturan terkait lainnya.' },
                    { number: '1.5', text: 'Jika Anda tidak menyetujui sebagian atau seluruh isi dari Kebijakan Privasi ini, Anda disarankan untuk tidak menggunakan Situs atau Layanan yang disediakan oleh Inofarma.' },
                ],
            },
            {
                title: '2. Jenis Data Pribadi yang Dikumpulkan',
                clauses: [
                    { number: '2.1', text: 'Inofarma dapat mengumpulkan data pribadi dari Pengguna (“Data Pribadi”) ketika Pengguna melakukan pendaftaran akun, transaksi, atau berinteraksi dengan Layanan.' },
                    {
                        number: '2.2',
                        text: 'Data Pribadi yang dikumpulkan meliputi namun tidak terbatas pada:',
                        items: [
                            'Data Identitas Pribadi, seperti nama lengkap, tanggal lahir, dan nomor telepon.',
                            'Data Kontak, seperti alamat email, alamat pengiriman, dan informasi wilayah.',
                            'Data Transaksi, meliputi riwayat pembelian, metode pembayaran, serta status pengiriman.',
                            'Data Akun, seperti nama pengguna (username), kata sandi, dan preferensi akun.',
                            'Data Kesehatan Terkait, apabila diperlukan untuk pembelian obat dengan resep dokter.',
                            'Data Teknis, seperti alamat IP, tipe perangkat, dan aktivitas pada Situs.',
                        ],
                    },
                    { number: '2.3', text: 'Data dapat dikumpulkan secara langsung dari Pengguna maupun secara otomatis melalui penggunaan Situs.' },
                ],
            },
            {
                title: '3. Tujuan Penggunaan Data Pribadi',
                clauses: [
                    {
                        number: '3.1',
                        text: 'Inofarma menggunakan Data Pribadi Pengguna untuk tujuan sebagai berikut:',
                        items: [
                            'Memproses pendaftaran akun dan keanggotaan Pengguna di Inofarma.',
                            'Memproses transaksi, pembayaran, dan pengiriman produk kepada Pengguna.',
                            'Menyampaikan informasi mengenai produk, promosi, dan penawaran khusus.',
                            'Memberikan dukungan layanan pelanggan serta menindaklanjuti keluhan atau permintaan informasi.',
                            'Melakukan verifikasi dan validasi terhadap transaksi atau akun Pengguna.',
                            'Meningkatkan kualitas Layanan, termasuk melalui analisis perilaku pengguna.',
                            'Memenuhi kewajiban hukum dan peraturan yang berlaku.',
                        ],
                    },
                    { number: '3.2', text: 'Penggunaan Data Pribadi dilakukan secara sah, transparan, dan proporsional sesuai dengan tujuan yang telah disebutkan di atas.' },
                ],
            },
            {
                title: '4. Pengungkapan Data Pribadi kepada Pihak Ketiga',
                clauses: [
                    { number: '4.1', text: 'Inofarma tidak akan menjual, menyewakan, atau memperdagangkan Data Pribadi Pengguna kepada pihak mana pun.' },
                    {
                        number: '4.2',
                        text: 'Inofarma dapat memberikan Data Pribadi kepada pihak ketiga yang bekerja sama untuk mendukung pelaksanaan Layanan, termasuk namun tidak terbatas pada:',
                        items: [
                            'Penyedia jasa logistik atau kurir pengiriman.',
                            'Mitra pembayaran atau penyedia layanan transaksi keuangan.',
                            'Penyedia layanan teknologi informasi atau penyimpanan data.',
                            'Otoritas hukum, apabila diwajibkan berdasarkan peraturan perundang-undangan.',
                        ],
                    },
                    { number: '4.3', text: 'Pihak ketiga yang menerima Data Pribadi diwajibkan untuk menjaga kerahasiaan dan keamanan data sesuai dengan standar yang ditetapkan oleh Inofarma dan ketentuan hukum yang berlaku.' },
                ],
            },
            {
                title: '5. Penyimpanan dan Keamanan Data',
                clauses: [
                    { number: '5.1', text: 'Inofarma menerapkan langkah-langkah keamanan fisik, elektronik, dan administratif untuk melindungi Data Pribadi Pengguna dari kehilangan, penyalahgunaan, atau akses tidak sah.' },
                    { number: '5.2', text: 'Data Pribadi disimpan dalam sistem yang terenkripsi dan hanya dapat diakses oleh personel berwenang untuk kepentingan operasional yang sah.' },
                    { number: '5.3', text: 'Meskipun Inofarma telah menerapkan standar keamanan terbaik, Pengguna diharapkan tetap menjaga kerahasiaan akun dan kata sandi masing-masing.' },
                ],
            },
            {
                title: '6. Hak Pengguna atas Data Pribadi',
                clauses: [
                    {
                        number: '6.1',
                        text: 'Pengguna memiliki hak-hak sebagai berikut:',
                        items: [
                            'Hak untuk mengakses Data Pribadi yang dimiliki oleh Inofarma.',
                            'Hak untuk memperbarui, memperbaiki, atau mengubah Data Pribadi yang tidak akurat.',
                            'Hak untuk menarik kembali persetujuan terhadap penggunaan Data Pribadi.',
                            'Hak untuk menghapus Data Pribadi sesuai dengan ketentuan hukum yang berlaku.',
                        ],
                    },
                    { number: '6.2', text: 'Permintaan terkait hak-hak tersebut dapat diajukan melalui email resmi Inofarma di cs@inofarma.com atau 0812-1401-8964' },
                ],
            },
            {
                title: '7. Retensi Data',
                clauses: [
                    { number: '7.1', text: 'Inofarma akan menyimpan Data Pribadi selama akun Pengguna aktif atau selama diperlukan untuk memenuhi tujuan pengumpulan data.' },
                    { number: '7.2', text: 'Setelah akun ditutup, Data Pribadi akan dihapus secara aman setelah jangka waktu penyimpanan yang diwajibkan oleh peraturan perundang-undangan berakhir.' },
                ],
            },
            {
                title: '8. Perubahan atas Kebijakan Privasi',
                clauses: [
                    { number: '8.1', text: 'Inofarma berhak mengubah atau memperbarui Kebijakan Privasi ini sewaktu-waktu untuk menyesuaikan dengan ketentuan hukum, kebijakan internal, atau kebutuhan operasional.' },
                    { number: '8.2', text: 'Setiap perubahan akan diumumkan melalui Situs resmi Inofarma dan mulai berlaku sejak tanggal pembaruan ditampilkan.' },
                ],
            },
            {
                title: '9. Kontak dan Pertanyaan',
                clauses: [
                    { number: '9.1', text: 'Untuk pertanyaan, permintaan, atau keluhan terkait pengelolaan Data Pribadi, Pengguna dapat menghubungi:' },
                ],
            },
        ],
    },
    terms: {
        title: 'Syarat & Ketentuan Inofarma',
        contactHeading: 'Untuk pertanyaan, saran, atau keluhan terkait penggunaan Situs dan Layanan Inofarma, Pengguna dapat menghubungi:',
        updatedAt: 'November 2025',
        sections: [
            {
                title: '1. Pendahuluan',
                clauses: [
                    { number: '1.1', text: 'Dokumen ini merupakan perjanjian hukum antara Pengguna (“Anda”) dan Apotek Inofarma (“Inofarma”), yang mengatur hak dan kewajiban dalam penggunaan situs web www.inofarma.com (“Situs”) serta seluruh layanan yang disediakan di dalamnya (“Layanan”).' },
                    { number: '1.2', text: 'Dengan mengakses atau menggunakan Situs, Anda dianggap telah membaca, memahami, dan menyetujui seluruh isi Syarat dan Ketentuan ini.' },
                    { number: '1.3', text: 'Apabila Anda tidak menyetujui sebagian atau seluruh isi dari Syarat dan Ketentuan ini, Anda tidak diperkenankan untuk menggunakan Situs maupun Layanan yang disediakan oleh Inofarma.' },
                    { number: '1.4', text: 'Inofarma berhak mengubah, menambah, atau memperbarui Syarat dan Ketentuan ini sewaktu-waktu tanpa pemberitahuan sebelumnya. Perubahan tersebut akan berlaku sejak tanggal pembaruan diumumkan di Situs.' },
                ],
            },
            {
                title: '2. Definisi',
                clauses: [
                    {
                        number: '2.1',
                        text: 'Dalam Syarat dan Ketentuan ini, istilah-istilah berikut memiliki arti sebagai berikut:',
                        items: [
                            '“Situs” berarti situs resmi Apotek Inofarma yang beralamat di www.inofarma.com',
                            '“Layanan” berarti seluruh fasilitas yang disediakan Inofarma melalui Situs, termasuk namun tidak terbatas pada pendaftaran akun, pembelian produk, konsultasi apoteker, dan sistem pembayaran online.',
                            '“Pengguna” berarti setiap individu yang mengakses, menggunakan, atau melakukan transaksi melalui Situs.',
                            '“Akun” berarti identitas digital yang dibuat oleh Pengguna untuk mengakses Layanan yang disediakan di Situs.',
                            '“Produk” berarti barang atau jasa kesehatan yang ditawarkan melalui Situs, termasuk obat-obatan, suplemen, alat kesehatan, dan produk farmasi lainnya.',
                        ],
                    },
                ],
            },
            {
                title: '3. Ketentuan Umum Penggunaan Situs',
                clauses: [
                    { number: '3.1', text: 'Pengguna wajib berusia sekurang-kurangnya 18 (delapan belas) tahun, atau memiliki izin dari orang tua/wali sah apabila berusia di bawah ketentuan tersebut.' },
                    { number: '3.2', text: 'Pengguna wajib memberikan informasi yang benar, lengkap, dan terkini dalam setiap penggunaan Layanan.' },
                    { number: '3.3', text: 'Pengguna bertanggung jawab penuh atas keamanan dan kerahasiaan akun, termasuk kata sandi. Segala aktivitas yang dilakukan melalui akun Pengguna akan dianggap sebagai tindakan yang sah dari Pengguna yang bersangkutan.' },
                    { number: '3.4', text: 'Inofarma berhak menonaktifkan atau menghapus akun Pengguna apabila ditemukan pelanggaran terhadap Syarat dan Ketentuan ini, atau terdapat indikasi penyalahgunaan akun.' },
                ],
            },
            {
                title: '4. Ketentuan Pembelian Produk',
                clauses: [
                    { number: '4.1', text: 'Pengguna dapat melakukan pembelian Produk melalui Situs sesuai dengan prosedur transaksi yang telah ditetapkan oleh Inofarma.' },
                    { number: '4.2', text: 'Harga Produk yang tercantum di Situs sudah termasuk pajak sesuai dengan ketentuan peraturan perundang-undangan yang berlaku.' },
                    { number: '4.3', text: 'Pembayaran hanya dapat dilakukan melalui metode resmi yang disediakan oleh Inofarma.' },
                    {
                        number: '4.4',
                        text: 'Inofarma berhak menolak atau membatalkan pesanan apabila:',
                        items: [
                            'Produk tidak tersedia;',
                            'Terdapat kesalahan harga atau informasi produk;',
                            'Terdapat indikasi penyalahgunaan atau pelanggaran hukum oleh Pengguna.',
                        ],
                    },
                ],
            },
            {
                title: '5. Pengiriman Produk',
                clauses: [
                    { number: '5.1', text: 'Pengiriman Produk dilakukan melalui mitra logistik resmi yang bekerja sama dengan Inofarma.' },
                    { number: '5.2', text: 'Estimasi waktu pengiriman dapat berbeda-beda tergantung pada lokasi tujuan, ketersediaan produk, dan kondisi operasional pihak pengiriman.' },
                    { number: '5.3', text: 'Inofarma tidak bertanggung jawab atas keterlambatan pengiriman yang disebabkan oleh faktor eksternal di luar kendali Inofarma, termasuk namun tidak terbatas pada bencana alam, gangguan sistem, atau kebijakan pemerintah (force majeure).' },
                    { number: '5.4', text: 'Pengguna wajib memastikan alamat pengiriman yang diberikan sudah benar dan lengkap. Kesalahan alamat menjadi tanggung jawab Pengguna sepenuhnya.' },
                ],
            },
            {
                title: '6. Kebijakan Pengembalian dan Pembatalan',
                clauses: [
                    { number: '6.1', text: 'Pembatalan pesanan hanya dapat dilakukan sebelum pesanan diproses atau dikirim.' },
                    {
                        number: '6.2',
                        text: 'Produk yang sudah diterima tidak dapat dikembalikan kecuali dalam kondisi:',
                        items: [
                            'Produk rusak akibat kesalahan pengiriman;',
                            'Produk salah kirim;',
                            'Produk melewati tanggal kadaluarsa saat diterima.',
                        ],
                    },
                    { number: '6.3', text: 'Pengguna wajib melaporkan keluhan pengembalian paling lambat 2x24 jam setelah produk diterima, disertai bukti foto dan nomor pesanan.' },
                    { number: '6.4', text: 'Proses pengembalian dana atau penggantian produk akan dilakukan sesuai dengan kebijakan internal Inofarma setelah proses verifikasi selesai.' },
                ],
            },
            {
                title: '7. Tanggung Jawab dan Batasan Tanggung Jawab',
                clauses: [
                    { number: '7.1', text: 'Inofarma berkomitmen untuk menyediakan informasi produk yang akurat dan layanan yang berkualitas tinggi. Namun demikian, Inofarma tidak menjamin bahwa seluruh informasi di Situs bebas dari kesalahan atau kekeliruan teknis.' },
                    {
                        number: '7.2',
                        text: 'Inofarma tidak bertanggung jawab atas:',
                        items: [
                            'Kerugian langsung maupun tidak langsung yang timbul akibat penggunaan Situs;',
                            'Gangguan teknis yang disebabkan oleh faktor eksternal;',
                            'Penggunaan Produk yang tidak sesuai dengan petunjuk dokter atau apoteker.',
                        ],
                    },
                    { number: '7.3', text: 'Pengguna setuju bahwa penggunaan Situs dilakukan dengan risiko yang sepenuhnya ditanggung oleh Pengguna.' },
                ],
            },
            {
                title: '8. Hak Kekayaan Intelektual',
                clauses: [
                    { number: '8.1', text: 'Seluruh logo, merek dagang, desain, teks, gambar, serta materi lain yang terdapat di Situs merupakan milik Inofarma dan dilindungi oleh undang-undang hak cipta dan kekayaan intelektual yang berlaku di Republik Indonesia.' },
                    { number: '8.2', text: 'Pengguna dilarang menyalin, memperbanyak, menyebarkan, memodifikasi, atau menggunakan materi tersebut untuk kepentingan apa pun tanpa izin tertulis dari Inofarma.' },
                ],
            },
            {
                title: '9. Larangan Penggunaan',
                clauses: [
                    {
                        number: '9.1',
                        text: 'Pengguna dilarang menggunakan Situs untuk tujuan yang bertentangan dengan hukum, termasuk namun tidak terbatas pada:',
                        items: [
                            'Mengunggah konten yang mengandung SARA, pornografi, kekerasan, atau kebencian;',
                            'Melakukan penipuan atau penyalahgunaan data;',
                            'Mengganggu sistem keamanan, server, atau jaringan milik Inofarma.',
                        ],
                    },
                    { number: '9.2', text: 'Pelanggaran terhadap ketentuan ini dapat mengakibatkan penghentian akun dan/atau tindakan hukum sesuai peraturan yang berlaku.' },
                ],
            },
            {
                title: '10. Perubahan Layanan',
                clauses: [
                    { number: '10.1', text: 'Inofarma berhak untuk menambah, mengubah, menangguhkan, atau menghentikan sebagian maupun seluruh Layanan di Situs sewaktu-waktu dengan atau tanpa pemberitahuan terlebih dahulu.' },
                    { number: '10.2', text: 'Pengguna setuju bahwa Inofarma tidak bertanggung jawab atas kerugian apa pun yang timbul akibat perubahan atau penghentian Layanan tersebut.' },
                ],
            },
            {
                title: '11. Hukum yang Berlaku dan Penyelesaian Sengketa',
                clauses: [
                    { number: '11.1', text: 'Syarat dan Ketentuan ini diatur dan ditafsirkan berdasarkan hukum Republik Indonesia.' },
                    { number: '11.2', text: 'Setiap sengketa yang timbul akibat pelaksanaan atau penafsiran dokumen ini akan diselesaikan terlebih dahulu secara musyawarah untuk mufakat.' },
                    { number: '11.3', text: 'Apabila penyelesaian secara musyawarah tidak tercapai, maka sengketa akan diselesaikan melalui lembaga peradilan yang berwenang di wilayah hukum Republik Indonesia.' },
                ],
            },
            {
                title: '12. Kontak dan Layanan Pelanggan',
                clauses: [
                    { number: '12.1', text: 'Untuk pertanyaan, saran, atau keluhan terkait penggunaan Situs dan Layanan Inofarma, Pengguna dapat menghubungi:' },
                ],
            },
        ],
    },
};
