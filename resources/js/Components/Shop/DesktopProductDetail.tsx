import { useEffect, useRef, useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import DesktopLayout from '@/Layouts/DesktopLayout';
import { DrugClassBadge } from './DrugInfo';
import DesktopHeader from './DesktopHeader';
import ProductGrid from './ProductGrid';
import RecentlyViewed from './RecentlyViewed';
import flyToCart from './flyToCart';
import { money, useShopCatalog } from './data';

function Gallery({ product }: { product: CatalogProduct }) {
    const images = product.images?.length ? product.images : [{ path: product.image, alt: product.name }];
    const [active, setActive] = useState(0);
    const current = images[active] ?? images[0];

    return (
        <div className="flex gap-4 border border-line bg-white p-5">
            {images.length > 1 ? (
                <div className="flex w-16 shrink-0 flex-col gap-2">
                    {images.map((image, index) => (
                        <button
                            key={image.path}
                            type="button"
                            onClick={() => setActive(index)}
                            aria-label={`Foto ${index + 1}`}
                            className={`border p-0.5 ${index === active ? 'border-2 border-success' : 'border-line'}`}
                        >
                            <img src={image.path} alt={image.alt} className="aspect-square w-full object-contain" />
                        </button>
                    ))}
                </div>
            ) : null}

            <div className="relative flex min-h-[420px] flex-1 items-center justify-center">
                <img src={current.path} alt={current.alt} className="max-h-[420px] w-full object-contain" />
            </div>
        </div>
    );
}

/**
 * Branch + quantity + add-to-cart panel. Same endpoint and branch-conflict
 * handling as the phone `BranchPicker`, just laid out as the desktop buy box:
 * the nearest branch with stock is preselected and can be swapped from a
 * dropdown.
 */
function BuyBox({ product }: { product: CatalogProduct }) {
    const [branches, setBranches] = useState<ProductBranch[] | null>(null);
    const [selectedId, setSelectedId] = useState('');
    const [quantity, setQuantity] = useState(1);
    const [added, setAdded] = useState(false);
    const [loadError, setLoadError] = useState('');
    const buttonRef = useRef<HTMLButtonElement>(null);

    const { transform, post, processing, errors: formErrors } = useForm({});

    useEffect(() => {
        let cancelled = false;

        fetch(`/api/cabang/untuk-produk/${product.id}`, { headers: { Accept: 'application/json' } })
            .then((response) => {
                if (! response.ok) {
                    throw new Error('gagal memuat');
                }

                return response.json();
            })
            .then((body: { branches: ProductBranch[] }) => {
                if (! cancelled) {
                    setBranches(body.branches);
                    setSelectedId(body.branches.find((branch) => branch.selectable)?.id ?? '');
                }
            })
            .catch(() => {
                if (! cancelled) {
                    setLoadError('Tidak bisa memuat daftar cabang. Coba lagi nanti.');
                }
            });

        return () => {
            cancelled = true;
        };
    }, [product.id]);

    const errors = formErrors as Record<string, string | undefined>;
    const selected = branches?.find((branch) => branch.id === selectedId) ?? null;
    const maxQuantity = Math.min(
        selected?.available ?? Infinity,
        product.maxQtyPerOrder ?? Infinity,
    );

    const addToCart = (switchBranch = false) => {
        if (! selected) {
            return;
        }

        transform(() => ({ productId: product.id, branchId: selected.id, quantity, switchBranch }));

        post('/keranjang', {
            preserveScroll: true,
            onSuccess: () => {
                setAdded(true);
                flyToCart(buttonRef.current);
                window.setTimeout(() => setAdded(false), 1800);
            },
        });
    };

    return (
        <div className="border border-line bg-white p-6">
            <h1 className="font-display text-2xl text-brand">{product.name}</h1>
            <p className="mt-1 text-[11px] text-faint">{product.category}</p>

            <div className="mt-4 text-[11px]">
                <span className="text-muted">Dikirim dari:</span>

                {loadError ? (
                    <p className="mt-1 text-danger">{loadError}</p>
                ) : ! branches ? (
                    <div className="mt-1 h-4 w-48 animate-pulse bg-[#f5f5f5]" aria-hidden="true" />
                ) : branches.length === 0 ? (
                    <p className="mt-1 text-muted">Belum ada cabang yang menjual produk ini.</p>
                ) : (
                    <select
                        value={selectedId}
                        onChange={(event) => {
                            setSelectedId(event.target.value);
                            setQuantity(1);
                        }}
                        aria-label="Pilih cabang"
                        className="mt-1 block w-full max-w-sm border border-line bg-white py-1.5 pl-2 pr-8 text-[11px] uppercase text-success-deep focus:border-brand focus:ring-0"
                    >
                        {branches.map((branch) => (
                            <option key={branch.id} value={branch.id} disabled={! branch.selectable}>
                                {branch.name}
                                {branch.distanceKm !== null ? ` (${branch.distanceKm} km)` : ''}
                                {branch.selectable ? '' : ' - stok kosong'}
                            </option>
                        ))}
                    </select>
                )}
            </div>

            <dl className="mt-5 space-y-4 border-t border-line pt-5 text-[13px]">
                <div className="flex items-center gap-6">
                    <dt className="w-20 shrink-0 text-[11px] font-bold text-brand">Harga:</dt>
                    <dd className="flex items-baseline gap-3">
                        <span className="text-xl text-success">{money(product.price)}</span>
                        {product.oldPrice ? (
                            <span className="text-[11px] text-faint line-through">{money(product.oldPrice)}</span>
                        ) : null}
                    </dd>
                </div>

                <div className="flex items-center gap-6">
                    <dt className="w-20 shrink-0 text-[11px] font-bold text-brand">Stok:</dt>
                    <dd className="text-muted">
                        {selected ? `Tersedia ${selected.available}` : branches ? 'Stok kosong' : '...'}
                    </dd>
                </div>

                <div className="flex items-center gap-6">
                    <dt className="w-20 shrink-0 text-[11px] font-bold text-brand">Kuantitas:</dt>
                    <dd className="flex items-center border border-line">
                        <button
                            type="button"
                            onClick={() => setQuantity((current) => Math.max(1, current - 1))}
                            aria-label={`Kurangi jumlah ${product.name}`}
                            className="flex h-9 w-9 items-center justify-center text-muted"
                        >
                            &minus;
                        </button>
                        <span className="flex h-9 w-12 items-center justify-center border-x border-line text-[13px]">
                            {quantity}
                        </span>
                        <button
                            type="button"
                            onClick={() => setQuantity((current) => Math.min(maxQuantity, current + 1))}
                            aria-label={`Tambah jumlah ${product.name}`}
                            className="flex h-9 w-9 items-center justify-center text-muted"
                        >
                            +
                        </button>
                    </dd>
                </div>
            </dl>

            {product.maxQtyPerOrder ? (
                <p className="mt-2 text-[10px] text-muted">Maksimal {product.maxQtyPerOrder} per transaksi.</p>
            ) : null}

            {errors.quantity ? <p className="mt-2 text-[11px] text-danger">{errors.quantity}</p> : null}

            {errors.branch ? (
                <div className="mt-4 max-w-sm border border-danger bg-danger/5 p-2.5 text-[11px] text-danger-deep">
                    <p className="mb-1.5">{errors.branch}</p>
                    <button
                        type="button"
                        onClick={() => addToCart(true)}
                        disabled={processing}
                        className="font-bold underline"
                    >
                        Ya, kosongkan &amp; pindah
                    </button>
                </div>
            ) : (
                <button
                    ref={buttonRef}
                    type="button"
                    onClick={() => addToCart(false)}
                    disabled={! selected || processing}
                    className="mt-5 flex h-11 w-full max-w-sm items-center justify-center bg-success text-xs font-bold text-white disabled:opacity-60"
                >
                    {added ? 'Ditambahkan ✓' : 'Tambahkan ke keranjang'}
                </button>
            )}
        </div>
    );
}

function Description({ product }: { product: CatalogProduct }) {
    const rows = [
        ['Nama Produk & Sediaan', product.name],
        ['Indikasi', product.indication],
        ['Komposisi & Kekuatan', product.composition],
        ['Aturan Pakai', product.dosage],
        ['Efek Samping', product.sideEffects],
        ['Golongan Produk', product.drugClass],
        ['Kemasan', product.variants?.join(', ')],
        ['Produsen', product.manufacturer],
        ['Nomor Izin Edar (NIE)', product.nie],
        ['Kondisi Penyimpanan', product.storage],
    ].filter(([, value]) => Boolean(value));

    return (
        <div className="border border-line bg-white p-8">
            <div className="mb-6 flex items-center justify-between">
                <h2 className="font-display text-xl text-brand">Deskripsi</h2>
                <DrugClassBadge drugClass={product.drugClass} />
            </div>

            {product.needsWarningLabel && product.warning ? (
                <div className="mb-6 border-2 border-ink bg-[#eaf2fc] px-3 py-2.5 text-[11px] font-semibold leading-relaxed text-ink">
                    {product.warning}
                </div>
            ) : null}

            {product.blurb ? <p className="mb-6 whitespace-pre-line text-[13px] leading-relaxed text-muted">{product.blurb}</p> : null}

            <dl>
                {rows.map(([label, value]) => (
                    <div key={label} className="border-b border-line py-4 last:border-b-0">
                        <dt className="mb-1.5 text-[11px] font-bold text-muted">{label}:</dt>
                        <dd className="text-[13px] leading-7 text-muted">{value}</dd>
                    </div>
                ))}
            </dl>
        </div>
    );
}

/**
 * Desktop product page: breadcrumb, photo gallery beside a buy box, the full
 * description underneath, then other products from the same category.
 */
export default function DesktopProductDetail({ product }: { product: CatalogProduct }) {
    const { shopProducts } = useShopCatalog();

    const related = shopProducts
        .filter((tile) => tile.category === product.category && tile.id !== product.id)
        .slice(0, 6);

    return (
        <DesktopLayout title={product.name} header={<DesktopHeader />}>
            <nav aria-label="Breadcrumb" className="py-5 text-[11px] text-muted">
                <Link href="/">Beranda</Link>
                <span className="mx-2">&rsaquo;</span>
                <Link href="/shop">Semua produk</Link>
                <span className="mx-2">&rsaquo;</span>
                <span>{product.name}</span>
            </nav>

            <div className="grid grid-cols-[1fr_1fr] items-start gap-6">
                <div className="space-y-6">
                    <Gallery product={product} />
                    <Description product={product} />
                </div>

                <BuyBox product={product} />
            </div>

            {related.length ? (
                <section className="mt-12">
                    <h2 className="mb-4 font-display text-xl text-brand">Produk Lainnya yang Mungkin Anda Suka</h2>
                    <ProductGrid products={related} />
                </section>
            ) : null}

            <RecentlyViewed excludeId={product.id} />
        </DesktopLayout>
    );
}
