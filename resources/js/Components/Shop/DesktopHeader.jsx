import { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import { asset } from './data';
import Icon from './Icon';
import useCartCount from './useCartCount';
import useShopUser from './useShopUser';

/**
 * Desktop storefront header: a thin announcement strip, the main bar (logo,
 * live search field, account, cart) and the service-notice line underneath.
 * Search submits to the shop listing's existing `?q=` filter.
 */
export default function DesktopHeader() {
    const cartCount = useCartCount();
    const { name, signedIn } = useShopUser();
    const [query, setQuery] = useState('');

    const submit = (event) => {
        event.preventDefault();

        const needle = query.trim();

        router.visit(needle ? `/ui/shop?q=${encodeURIComponent(needle)}` : '/ui/shop');
    };

    return (
        <header>
            <div className="bg-brand text-white">
                <div className="mx-auto flex h-9 max-w-6xl items-center justify-between px-6">
                    <span className="text-xs font-bold">Belanja di Apotek Inofarma, Lebih Hemat Lebih Lengkap!</span>

                    <Link
                        href="/ui/tentang-kami"
                        className="-mr-6 flex h-9 items-center bg-success px-6 text-xs font-bold text-white"
                    >
                        Tentang Kami
                    </Link>
                </div>
            </div>

            <div className="border-t border-white/15 bg-brand text-white">
                <div className="mx-auto flex h-16 max-w-6xl items-center gap-8 px-6">
                    <Link href="/" aria-label="Inofarma" className="shrink-0">
                        <img src={asset.logo('blue')} alt="Inofarma" className="h-8 w-auto" />
                    </Link>

                    <form onSubmit={submit} role="search" className="flex h-10 flex-1 overflow-hidden bg-white">
                        <input
                            type="search"
                            value={query}
                            onChange={(event) => setQuery(event.target.value)}
                            placeholder="Cari produk kesehatan di Inofarma"
                            aria-label="Cari produk kesehatan di Inofarma"
                            className="min-w-0 flex-1 border-0 px-4 text-sm text-ink placeholder:text-faint focus:outline-none focus:ring-0 [&::-webkit-search-cancel-button]:hidden"
                        />

                        <button
                            type="submit"
                            aria-label="Cari"
                            className="flex w-11 shrink-0 items-center justify-center bg-success text-cream"
                        >
                            <Icon name="search" size={18} />
                        </button>
                    </form>

                    <Link
                        href={signedIn ? '/ui/profile' : '/ui/signin'}
                        className="flex shrink-0 flex-col text-xs leading-tight"
                    >
                        <span className="text-white/70">{signedIn ? 'Halo,' : 'Masuk / Daftar'}</span>
                        <span className="font-bold">{signedIn ? name : 'Akun saya'}</span>
                    </Link>

                    <Link href="/ui/cart" className="flex shrink-0 items-center gap-2 text-sm font-bold">
                        <span className="relative flex items-center">
                            <Icon name="cart" size={22} />

                            {cartCount > 0 ? (
                                <span className="absolute -right-2 -top-2 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-danger px-1 text-[10px] font-bold text-white">
                                    {cartCount}
                                </span>
                            ) : null}
                        </span>
                        Keranjang
                    </Link>
                </div>
            </div>

            <div className="border-b border-warning/30 bg-[#FFF8E1]">
                <p className="mx-auto flex max-w-6xl items-center justify-center gap-2 px-6 py-2 text-center text-xs text-warning-deep">
                    <Icon name="info" size={14} />
                    Layanan pemesanan melalui website saat ini belum beroperasi. Kami mohon maaf atas ketidaknyamanannya.
                </p>
            </div>
        </header>
    );
}
