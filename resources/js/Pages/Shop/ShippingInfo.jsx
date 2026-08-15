import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import { money } from '@/Components/Shop/data';

const sections = [
    {
        title: 'Kebijakan Pengiriman',
        body: `Gratis ongkir untuk setiap pembelian di atas ${money(750000)}. Pengiriman reguler memakan waktu 5-7 hari kerja. Tersedia juga pengiriman ekspres (2-3 hari kerja) dengan biaya ${money(150000)}.`,
    },
    {
        title: 'Kebijakan Pengembalian',
        body: 'Barang dapat dikembalikan dalam 30 hari setelah pembelian, dengan kondisi asli dan label masih terpasang. Dana dikembalikan dalam 5-10 hari kerja.',
    },
    {
        title: 'Info Pembayaran',
        body: 'Kami menerima Visa, MasterCard, transfer bank, GoPay, OVO, dan DANA. Seluruh transaksi diamankan dengan enkripsi SSL.',
    },
];

export default function ShippingInfo() {
    return (
        <MobileLayout
            title="Info Pengiriman & Pembayaran"
            header={<AppBar title="Info Pengiriman" back="/ui/profile" />}
        >
            <div className="flex-1 overflow-y-auto p-4">
                {sections.map((section) => (
                    <div key={section.title} className="mb-5">
                        <div className="mb-2.5 border-b-2 border-ink pb-2 font-display text-[15px]">
                            {section.title}
                        </div>

                        <p className="text-xs leading-[1.7] text-muted">{section.body}</p>
                    </div>
                ))}
            </div>
        </MobileLayout>
    );
}
