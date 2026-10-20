<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * The desktop site footer and the company details, edited under Konten
 * Website > Footer & Perusahaan. The company block also closes the Syarat &
 * Ketentuan and Kebijakan Privasi popups ({@see DocumentPageContent}).
 */
class SiteFooterContent
{
    public const KEY = 'content.footer';

    public const MAX_LINKS = 8;

    /**
     * @var array{
     *     aboutTitle: string,
     *     aboutText: string,
     *     menuTitle: string,
     *     links: list<array{label: string, href: string}>,
     *     copyright: string,
     *     company: array{name: string, email: string, phone: string, address: string},
     * }
     */
    public const DEFAULTS = [
        'aboutTitle' => 'Tentang Apotek Inofarma',
        'aboutText' => 'Temukan Solusi Kesehatan Terhemat dan Terlengkap yang Selalu Dekat untuk Masyarakat.',
        'menuTitle' => 'Menu Utama',
        'links' => [
            ['label' => 'Beranda', 'href' => '/'],
            ['label' => 'Semua Produk', 'href' => '/shop'],
            ['label' => 'Tentang Kami', 'href' => 'http://info.inofarma.com/'],
        ],
        'copyright' => 'inofarma.com. Hak cipta dilindungi.',
        'company' => [
            'name' => 'PT Teknologi Medika Pratama',
            'email' => 'cs@inofarma.com',
            'phone' => '0812-1401-8964',
            'address' => 'Berrybenka Headquarter Lantai 1, Jl. KH Mas Mansyur No.19, Desa/Kelurahan Karet Tengsin, Kec. Tanah Abang, Kota Adm. Jakarta Pusat, Provinsi DKI Jakarta, Kode Pos: 10220',
        ],
    ];

    /**
     * Saved content over the defaults; the company details merge field by field.
     *
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        $saved = array_intersect_key(ContentStore::read(self::KEY), self::DEFAULTS);
        $saved['company'] = array_replace(self::DEFAULTS['company'], array_intersect_key((array) ($saved['company'] ?? []), self::DEFAULTS['company']));

        return array_replace(self::DEFAULTS, $saved);
    }

    /**
     * @param  array<string, mixed>  $content  the admin form's state
     */
    public static function save(array $content): void
    {
        $current = self::all();
        $text = fn (string $key) => trim((string) ($content[$key] ?? ''));

        ContentStore::write(self::KEY, [
            'aboutTitle' => $text('aboutTitle'),
            'aboutText' => $text('aboutText'),
            'menuTitle' => $text('menuTitle'),
            'links' => collect((array) ($content['links'] ?? []))
                ->map(fn ($link) => ['label' => trim((string) ($link['label'] ?? '')), 'href' => trim((string) ($link['href'] ?? ''))])
                ->filter(fn (array $link) => $link['label'] !== '' && $link['href'] !== '')
                ->take(self::MAX_LINKS)
                ->values()
                ->all(),
            'copyright' => $text('copyright'),
            'company' => collect(self::DEFAULTS['company'])
                ->map(fn (string $default, string $field) => trim((string) ($content['company'][$field] ?? '')) ?: $current['company'][$field])
                ->all(),
        ]);
    }

    /**
     * The footer as every desktop page renders it. A full http(s) link opens
     * outside the shop, so the page marks it external.
     *
     * @return array<string, mixed>
     */
    public static function forStorefront(): array
    {
        $content = self::all();

        return [
            'aboutTitle' => $content['aboutTitle'],
            'aboutText' => $content['aboutText'],
            'menuTitle' => $content['menuTitle'],
            'links' => array_map(fn (array $link) => [
                ...$link,
                'external' => Str::startsWith($link['href'], ['http://', 'https://']),
            ], $content['links']),
            'copyright' => $content['copyright'],
        ];
    }

    /**
     * @return array{name: string, email: string, phone: string, address: string}
     */
    public static function company(): array
    {
        return self::all()['company'];
    }
}
