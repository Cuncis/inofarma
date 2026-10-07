/**
 * Ambient types shared across the storefront and admin screens.
 *
 * The catalogue shape mirrors `ShopCatalogPresenter::forStorefront()`, which
 * `HandleInertiaRequests` shares with every page as the `catalog` prop.
 */

interface CatalogImage {
    path: string;
    alt: string;
}

interface CatalogProduct {
    /** SKU, e.g. PRD-001. */
    id: string;
    slug: string;
    name: string;
    category: string;
    /** The primary photo, used by grids, strips and cards. */
    image: string;
    /** Every photo in display order, used by the product-detail gallery. */
    images: CatalogImage[];
    /** Current selling price, in rupiah. */
    price: number;
    /** Struck-through price when the item is discounted. */
    oldPrice?: number;
    /** Across every branch. */
    stock: number;
    sold: number;
    rating: string;
    /** Availability: Tersedia | Stok Menipis | Habis. */
    status: string;
    unit: string;
    variants: string[];
    prescription: boolean;
    blurb: string;
    /** Non-Obat | Bebas | Bebas Terbatas | Keras. */
    drugClass?: string;
    /** True for Bebas Terbatas, where the P1-P6 warning must show. */
    needsWarningLabel: boolean;
    /** NIE BPOM registration number. */
    nie?: string;
    composition?: string;
    indication?: string;
    dosage?: string;
    sideEffects?: string;
    /** P1-P6 warning text for Bebas Terbatas. */
    warning?: string;
    manufacturer?: string;
    maxQtyPerOrder?: number;
    /** Suhu Ruang | Sejuk (15-25°C) | Dingin/Kulkas (2-8°C). */
    storage?: string;
}

interface CatalogCategory {
    name: string;
    slug: string;
    image: string;
    status: string;
    products: number;
}

interface Catalog {
    products: CatalogProduct[];
    categories: CatalogCategory[];
}

/** A product as the storefront grids show it, with the price already formatted. */
interface ProductTile {
    id: string;
    name: string;
    category: string;
    image: string;
    price: string;
    oldPrice?: string;
    rating: string;
    brand: string;
    soldOut: boolean;
}

interface ShopUser {
    name: string;
    email: string;
    phone: string;
}

interface Review {
    author: string;
    avatar: string;
    score: number;
    age: string;
    body: string;
}

/** A pharmacy branch as returned by `/api/cabang/untuk-produk/{product}`. */
interface ProductBranch {
    id: string;
    name: string;
    kota: string;
    distanceKm: number | null;
    available: number;
    selectable: boolean;
    supportsDelivery: boolean;
    supportsPickup: boolean;
    isOpenNow: boolean;
}

/** The branch a cart or order is tied to, as `CartPresenter` returns it. */
interface CartBranch {
    id: string;
    name: string;
    kota: string;
    fullAddress: string;
    supportsDelivery: boolean;
    supportsPickup: boolean;
    apjName?: string | null;
    apjWhatsappUrl?: string | null;
}

interface SavedAddress {
    id: number | string;
    label: string;
    isDefault: boolean;
    fullAddress: string;
}

interface CartLine {
    sku: string;
    name: string;
    image: string;
    quantity: number;
    unitPrice: number;
    lineTotal: number;
    available: number;
}

interface CartCoupon {
    code: string;
    freeShipping: boolean;
}

interface CourierOption {
    courierCompany: string;
    courierType: string;
    courierName: string;
    serviceName: string;
    duration: string | null;
    price: number;
}

interface OrderListItem {
    number: string;
    status: string;
    fulfilment: string;
    branchName: string;
    date: string;
    total: number;
}

interface OrderLine {
    sku: string;
    name: string;
    quantity: number;
    lineTotal: number;
}

/** A branch as the "Cabang Kami" page lists it. */
interface StorefrontBranch {
    id: string;
    name: string;
    kota: string;
    fullAddress: string;
    distanceKm: number | null;
    isOpenNow: boolean;
    todaysHours?: string | null;
    supportsDelivery: boolean;
    supportsPickup: boolean;
    siaNumber?: string | null;
    apjName?: string | null;
    apjSipaNumber?: string | null;
    phone?: string | null;
    mapsUrl?: string | null;
}

interface CoverageArea {
    provinsi: string;
    kota: string;
}

/** One line of the cart as `CartPresenter` returns it. */
interface CartPreviewItem extends CartLine {
    brand: string;
    maxQtyPerOrder: number | null;
}

/** The cart as the header dropdown fetches it from `ui.keranjang.ringkas`. */
interface CartPreview {
    items: CartPreviewItem[];
    itemCount: number;
    subtotal: number;
}
