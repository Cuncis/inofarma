<?php

namespace Database\Seeders;

use App\Models\Newsletter;
use App\Models\Subscriber;
use Illuminate\Database\Seeder;

/**
 * Sample data for the Pemasaran menu: dummy subscribers and ready-made
 * newsletter drafts to start from. Safe to run more than once, it only adds
 * what is missing and never touches rows that already exist.
 *
 * Every dummy address ends in @example.com, a domain reserved for examples
 * that real mail can never reach, so they are easy to find and remove:
 *
 *   Subscriber::where('email', 'like', '%@example.com')->delete();
 *
 * Run on a server (needs --force in production):
 *
 *   php artisan db:seed --class=NewsletterSeeder --force
 */
class NewsletterSeeder extends Seeder
{
    private const SUBSCRIBED = 24;

    private const UNSUBSCRIBED = 6;

    public function run(): void
    {
        $this->subscribers();
        $this->newsletters();
    }

    private function subscribers(): void
    {
        $names = [
            'ani.wijaya', 'budi.santoso', 'citra.lestari', 'dewi.anggraini', 'eko.prasetyo', 'fitri.handayani',
            'gunawan.setiadi', 'hana.pertiwi', 'irfan.hakim', 'jihan.maharani', 'kevin.saputra', 'lia.kusuma',
            'maman.sulaiman', 'nadia.putri', 'oki.firmansyah', 'putri.ayu', 'qori.rahman', 'rina.marlina',
            'satria.nugraha', 'tika.amelia', 'umar.faruq', 'vina.oktaviani', 'wawan.hidayat', 'yuni.kartika',
            'zaki.maulana', 'abdul.rozak', 'bella.safitri', 'candra.wibowo', 'dian.permata', 'edi.susanto',
        ];

        foreach ($names as $index => $name) {
            $email = "{$name}@example.com";

            if (Subscriber::where('email', $email)->exists()) {
                continue;
            }

            $subscribedAt = now()->subDays(60 - $index * 2)->setTime(9 + $index % 8, ($index * 7) % 60);

            if ($index < self::SUBSCRIBED) {
                Subscriber::create(['email' => $email, 'status' => Subscriber::SUBSCRIBED, 'subscribed_at' => $subscribedAt]);

                continue;
            }

            Subscriber::create([
                'email' => $email,
                'status' => Subscriber::UNSUBSCRIBED,
                'subscribed_at' => $subscribedAt,
                'unsubscribed_at' => $subscribedAt->copy()->addDays(10),
            ]);
        }
    }

    private function newsletters(): void
    {
        $templates = [
            [
                'name' => 'Contoh: Selamat Datang',
                'subject' => 'Selamat datang di Apotek Inofarma',
                'preview_text' => 'Belanja kebutuhan kesehatan lebih hemat dan lebih lengkap.',
                'content' => $this->welcome(),
            ],
            [
                'name' => 'Contoh: Promo Vitamin',
                'subject' => 'Promo vitamin dan suplemen minggu ini',
                'preview_text' => 'Harga hemat untuk vitamin pilihan, hanya sampai Minggu.',
                'content' => $this->promo(),
            ],
            [
                'name' => 'Contoh: Tips Sehat Musim Hujan',
                'subject' => '5 tips menjaga daya tahan tubuh di musim hujan',
                'preview_text' => 'Sederhana, murah, dan bisa dimulai hari ini.',
                'content' => $this->tips(),
            ],
        ];

        foreach ($templates as $template) {
            Newsletter::firstOrCreate(['name' => $template['name']], $template + ['status' => Newsletter::DRAFT]);
        }

        // One finished campaign, so the history table has something to show.
        Newsletter::firstOrCreate(['name' => 'Contoh: Newsletter Bulan Lalu'], [
            'subject' => 'Info kesehatan dan promo bulan lalu',
            'preview_text' => 'Rangkuman promo dan tips kesehatan.',
            'content' => $this->tips(),
            'status' => Newsletter::SENT,
            'sent_at' => now()->subMonth()->setTime(9, 30),
            'recipient_count' => 120,
            'success_count' => 118,
            'failed_count' => 2,
        ]);
    }

    private function button(string $label, string $url): string
    {
        $config = htmlspecialchars(json_encode(['label' => $label, 'url' => $url], JSON_UNESCAPED_SLASHES), ENT_QUOTES);

        return '<div data-type="customBlock" data-id="button" data-config="'.$config.'"></div>';
    }

    private function welcome(): string
    {
        return '<h2>Halo, Sobat Ino!</h2>'
            .'<p>Terima kasih sudah berlangganan newsletter Apotek Inofarma. Mulai sekarang Anda akan mendapat info kesehatan, program, dan penawaran menarik langsung di email.</p>'
            .'<h3>Kenapa belanja di Inofarma?</h3>'
            .'<ul><li>Produk kesehatan lengkap dengan harga hemat</li><li>Apotek buka 24 jam</li><li>Layanan antar 24 jam dengan gratis ongkir hingga Rp 25.000</li><li>Konsultasi gratis dengan apoteker</li></ul>'
            .'<p>Temukan cabang terdekat atau mulai belanja sekarang.</p>'
            .$this->button('Mulai Belanja', 'https://inofarma.com/shop');
    }

    private function promo(): string
    {
        return '<h2>Vitamin pilihan, harga hemat</h2>'
            .'<p>Jaga daya tahan tubuh keluarga dengan vitamin dan suplemen favorit. Promo berlaku untuk pembelian di seluruh cabang dan lewat website, selama persediaan masih ada.</p>'
            .'<ul><li>Vitamin C dan D untuk dewasa dan anak</li><li>Suplemen penambah darah</li><li>Minyak kayu putih dan balsem</li></ul>'
            .'<p><strong>Promo berakhir hari Minggu.</strong> Ada pertanyaan soal dosis? Tanyakan langsung ke apoteker di cabang terdekat.</p>'
            .$this->button('Lihat Promo', 'https://inofarma.com/shop?category=Vitamin%20%26%20Suplemen');
    }

    private function tips(): string
    {
        return '<h2>5 tips menjaga daya tahan tubuh di musim hujan</h2>'
            .'<p>Cuaca yang tidak menentu membuat badan mudah drop. Lima kebiasaan sederhana ini bisa membantu:</p>'
            .'<ol><li>Minum air putih yang cukup</li><li>Tidur 7 sampai 8 jam setiap malam</li><li>Konsumsi buah dan sayur setiap hari</li><li>Cuci tangan sebelum makan</li><li>Lengkapi dengan vitamin bila perlu</li></ol>'
            .'<p>Baca info kesehatan lainnya di <a href="http://info.inofarma.com/">info.inofarma.com</a>.</p>';
    }
}
