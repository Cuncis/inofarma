import AdminLayout from '@/Layouts/AdminLayout';
import Card from '@/Components/Admin/Card';

const sections = [
    {
        title: '1. Informasi yang Kami Kumpulkan',
        body: 'Kami mengumpulkan data yang Anda berikan saat membuat akun, memesan produk, atau menghubungi layanan pelanggan — meliputi nama, alamat email, nomor telepon, alamat pengiriman, dan riwayat transaksi.',
    },
    {
        title: '2. Penggunaan Data',
        body: 'Data digunakan untuk memproses pesanan, mengirimkan produk, memberikan dukungan pelanggan, serta meningkatkan kualitas layanan. Kami tidak menjual data pribadi Anda kepada pihak ketiga.',
    },
    {
        title: '3. Data Kesehatan',
        body: 'Untuk produk yang memerlukan resep, kami menyimpan salinan resep sesuai ketentuan Peraturan Menteri Kesehatan. Data ini hanya dapat diakses oleh apoteker berizin.',
    },
    {
        title: '4. Keamanan',
        body: 'Seluruh transaksi diamankan dengan enkripsi SSL. Akses ke data pelanggan dibatasi berdasarkan peran dan dicatat dalam log aktivitas.',
    },
    {
        title: '5. Hak Anda',
        body: 'Anda berhak meminta salinan, perbaikan, atau penghapusan data pribadi Anda dengan menghubungi kami melalui halaman Pusat Bantuan.',
    },
    {
        title: '6. Perubahan Kebijakan',
        body: 'Kebijakan ini dapat diperbarui sewaktu-waktu. Perubahan penting akan diberitahukan melalui email atau notifikasi di dalam aplikasi.',
    },
];

export default function PrivacyPolicy() {
    return (
        <AdminLayout
            title="Kebijakan Privasi"
            heading="Kebijakan Privasi"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Kebijakan Privasi' }]}
        >
            <Card className="mx-auto max-w-3xl">
                <p className="mb-6 text-xs text-admin-muted dark:text-admin-dark-muted">
                    Terakhir diperbarui: 15 Agustus 2025
                </p>

                <div className="space-y-6">
                    {sections.map((section) => (
                        <section key={section.title}>
                            <h2 className="mb-2 text-[15px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                                {section.title}
                            </h2>
                            <p className="text-[13px] leading-relaxed text-admin-body dark:text-admin-dark-body">
                                {section.body}
                            </p>
                        </section>
                    ))}
                </div>
            </Card>
        </AdminLayout>
    );
}
