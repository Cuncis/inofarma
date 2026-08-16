import { useMemo, useState } from 'react';
import { usePage } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import Button from '@/Components/Shop/Button';
import Icon from '@/Components/Shop/Icon';
import IconLink from '@/Components/Shop/IconLink';
import ProductCard from '@/Components/Shop/ProductCard';
import SearchBar from '@/Components/Shop/SearchBar';
import TabBar from '@/Components/Shop/TabBar';
import { useShopCatalog } from '@/Components/Shop/data';

/**
 * Seed the field from `?q=` so a search can be linked to and survives a reload.
 *
 * @param {string} url
 * @returns {string}
 */
function initialQuery(url) {
    const query = url.split('?')[1];

    return query ? (new URLSearchParams(query).get('q') ?? '') : '';
}

export default function Shop() {
    const { filterCategories, shopProducts } = useShopCatalog();
    const { url } = usePage();
    const [query, setQuery] = useState(() => initialQuery(url));
    const [category, setCategory] = useState('Semua');

    const results = useMemo(() => {
        const needle = query.trim().toLowerCase();

        return shopProducts.filter((product) => {
            const matchesCategory = category === 'Semua' || product.category === category;
            const matchesQuery =
                ! needle ||
                product.name.toLowerCase().includes(needle) ||
                product.category.toLowerCase().includes(needle);

            return matchesCategory && matchesQuery;
        });
    }, [query, category, shopProducts]);

    const searching = query.trim().length > 0 || category !== 'Semua';

    return (
        <MobileLayout
            title="Belanja"
            header={
                <AppBar
                    brand
                    actions={
                        <>
                            <IconLink name="tag" href="/ui/filter" label="Filter" />
                            <IconLink name="bag" href="/ui/cart" label="Keranjang" />
                        </>
                    }
                />
            }
            footer={<TabBar active="home" />}
        >
            <div className="flex-1 overflow-y-auto pb-[70px]">
                <div className="sticky top-0 z-10 border-b border-line bg-white px-3 pb-2.5 pt-3">
                    <SearchBar value={query} onChange={setQuery} />

                    <div className="mt-2.5 flex gap-1.5 overflow-x-auto scrollbar-none">
                        {['Semua', ...filterCategories].map((name) => (
                            <button
                                key={name}
                                type="button"
                                onClick={() => setCategory(name)}
                                className={`shrink-0 px-3 py-1.5 text-[11px] ${
                                    category === name
                                        ? 'border border-brand bg-brand text-white'
                                        : 'border border-line text-muted'
                                }`}
                            >
                                {name}
                            </button>
                        ))}
                    </div>
                </div>

                <div className="px-3 pt-3">
                    {searching ? (
                        <p className="mb-2.5 text-[11px] text-faint">
                            {results.length} produk ditemukan
                            {query.trim() ? ` untuk "${query.trim()}"` : ''}
                        </p>
                    ) : null}

                    {results.length === 0 ? (
                        <div className="flex flex-col items-center px-6 py-14 text-center">
                            <Icon name="search" size={54} className="mb-4 text-brand opacity-20" />

                            <p className="mb-1.5 font-display text-[17px]">Produk tidak ditemukan</p>

                            <p className="mb-5 text-xs leading-relaxed text-muted">
                                Coba kata kunci lain atau ubah kategori yang dipilih.
                            </p>

                            <Button
                                onClick={() => {
                                    setQuery('');
                                    setCategory('Semua');
                                }}
                                variant="outline"
                            >
                                Atur Ulang Pencarian
                            </Button>
                        </div>
                    ) : (
                        <div className="grid grid-cols-2 gap-3">
                            {results.map((product) => (
                                <ProductCard key={product.id} product={product} />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </MobileLayout>
    );
}
