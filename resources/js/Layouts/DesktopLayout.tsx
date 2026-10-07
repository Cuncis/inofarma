import { Head, Link } from '@inertiajs/react';

/**
 * Desktop page shell: a normal scrolling document (unlike `MobileLayout`'s
 * fixed-height phone frame) with a full-bleed header and a centred content
 * column and the site footer. There is deliberately no bottom tab bar here.
 */
export default function DesktopLayout({ title, children, header = null, narrow = false }: {
  title: string,
  children: import('react').ReactNode,
  header?: import('react').ReactNode,
  narrow?: boolean,
}) {
    return (
        <>
            <Head title={title} />

            <div className="min-h-screen bg-canvas font-shop text-ink">
                {header}
                <main className={`mx-auto w-full px-6 pb-12 ${narrow ? 'max-w-2xl pt-8' : 'max-w-6xl'}`}>{children}</main>

                <footer className="bg-brand text-white">
                    <div className="mx-auto grid max-w-6xl grid-cols-[1fr_auto] gap-16 px-6 py-12">
                        <div>
                            <h3 className="mb-4 text-[10px] font-bold uppercase tracking-[1.5px]">
                                Tentang Apotek Inofarma
                            </h3>
                            <p className="max-w-sm text-[11px] leading-relaxed text-white/75">
                                Temukan Solusi Kesehatan Terhemat dan Terlengkap yang Selalu Dekat untuk
                                Masyarakat.
                            </p>
                        </div>

                        <nav aria-label="Menu utama">
                            <h3 className="mb-4 text-[10px] font-bold uppercase tracking-[1.5px]">Menu Utama</h3>
                            <ul className="space-y-3 text-[11px] text-white/75">
                                <li><Link href="/">Beranda</Link></li>
                                <li><Link href="/ui/shop">Semua Produk</Link></li>
                                <li>
                                    <a href="http://info.inofarma.com/" target="_blank" rel="noopener noreferrer">
                                        Tentang Kami
                                    </a>
                                </li>
                                <li><Link href="/ui/cabang-kami">Cabang Kami</Link></li>
                            </ul>
                        </nav>
                    </div>

                    <div className="border-t border-white/15 py-4 text-center text-[10px] text-white/60">
                        &copy; {new Date().getFullYear()} inofarma.com. Hak cipta dilindungi.
                    </div>
                </footer>
            </div>
        </>
    );
}
