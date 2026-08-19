import { Link } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';

/**
 * "Kebijakan Privasi" sesuai UU 27/2022 (PDP) — ROADMAP.md Fase 9.2. Versi
 * `1.0` di sini harus tetap sama dengan `AuthController::CONSENT_VERSION`;
 * naikkan keduanya bersamaan kalau isi berubah secara materiil.
 */
const sections = [
    {
        title: '1. Data yang Kami Kumpulkan',
        body: 'Data akun (nama, email, nomor telepon, kata sandi terenkripsi), alamat pengiriman beserta koordinatnya, riwayat pesanan, dan data lokasi perangkat Anda (bila Anda memberikan izin terpisah — lihat bagian 5). Kami tidak pernah menyimpan nomor kartu pembayaran atau kredensial e-wallet Anda; itu diproses langsung oleh DOKU.',
    },
    {
        title: '2. Tujuan Pengumpulan',
        body: 'Data dipakai untuk: memproses dan mengirim pesanan Anda, menghitung cabang terdekat dan ongkos kirim, verifikasi identitas (OTP), mengirim notifikasi status pesanan (email dan WhatsApp), dan mematuhi kewajiban pembukuan/perpajakan.',
    },
    {
        title: '3. Dasar Pemrosesan',
        body: 'Kami memproses data Anda berdasarkan persetujuan eksplisit yang Anda berikan saat mendaftar (tercatat dengan stempel waktu dan versi kebijakan), serta untuk melaksanakan kontrak jual-beli saat Anda membuat pesanan.',
    },
    {
        title: '4. Pihak Ketiga yang Memproses Data Anda',
        body: 'DOKU (pembayaran), Biteship (pengiriman dan kurir instan), Amazon SES (email transaksional), dan WhatsApp Business Platform milik Meta (notifikasi WhatsApp). Masing-masing hanya menerima data yang mereka butuhkan untuk fungsinya — misalnya kurir hanya menerima nama, telepon, dan alamat penerima, bukan riwayat belanja Anda.',
    },
    {
        title: '5. Data Lokasi — Persetujuan Terpisah',
        body: 'Koordinat perangkat Anda adalah data pribadi tersendiri menurut kami, terpisah dari persetujuan akun. Kami meminta persetujuan eksplisit lagi setiap kali fitur "gunakan lokasi saya" dipakai pertama kali (di halaman Cabang Kami dan Tambah Alamat) sebelum peramban Anda diminta membagikan lokasi. Anda selalu bisa memilih area secara manual sebagai gantinya.',
    },
    {
        title: '6. Retensi Data',
        body: 'Data akun disimpan selama akun Anda aktif. Data pesanan disimpan lebih lama sebagai catatan transaksi keuangan sesuai kewajiban pembukuan, walau akun sudah dihapus. Kode OTP dan token verifikasi kedaluwarsa otomatis dalam hitungan menit dan tidak disimpan setelah itu.',
    },
    {
        title: '7. Hak Anda',
        body: 'Anda berhak mengunduh salinan data Anda dan menghapus akun Anda kapan saja dari halaman "Privasi Saya" tanpa perlu menghubungi kami. Anda juga berhak meminta koreksi data yang keliru lewat "Ubah Profil".',
        action: { label: 'Buka Privasi Saya', href: '/ui/privasi-saya' },
    },
    {
        title: '8. Keamanan Data',
        body: 'Kata sandi disimpan ter-hash (bukan teks biasa), dan kredensial dua-faktor staf kami dienkripsi saat disimpan. Akses ke data pelanggan di panel admin dibatasi per peran dan per cabang, dan setiap perubahan data sensitif tercatat dalam jejak audit.',
    },
    {
        title: '9. Rencana Penanganan Kebocoran Data',
        body: 'Bila terjadi insiden keamanan yang berpotensi mengekspos data pribadi, kami akan: (a) mengisolasi celah yang ditemukan dalam 24 jam sejak terdeteksi, (b) menilai data dan pengguna yang terdampak, (c) memberi tahu pengguna terdampak dan otoritas terkait sesuai jangka waktu yang diwajibkan UU PDP, dan (d) mempublikasikan ringkasan insiden serta langkah perbaikan setelah investigasi selesai.',
    },
    {
        title: '10. Kontak',
        body: 'Pertanyaan seputar data pribadi Anda dapat diajukan ke privasi@inofarma.co.id.',
    },
];

export default function PrivacyPolicy() {
    return (
        <MobileLayout
            title="Kebijakan Privasi"
            header={<AppBar title="Kebijakan Privasi" back="/ui/profile" />}
        >
            <div className="flex-1 overflow-y-auto p-4">
                <p className="mb-4 text-[11px] text-faint">Versi 1.0 — berlaku sejak pendaftaran akun Anda.</p>

                {sections.map((section) => (
                    <div key={section.title} className="mb-5">
                        <div className="mb-2.5 border-b-2 border-ink pb-2 font-display text-[15px]">
                            {section.title}
                        </div>
                        <p className="text-xs leading-[1.7] text-muted">{section.body}</p>
                        {section.action ? (
                            <Link href={section.action.href} className="mt-2 inline-block text-xs font-bold text-brand underline">
                                {section.action.label} →
                            </Link>
                        ) : null}
                    </div>
                ))}
            </div>
        </MobileLayout>
    );
}
