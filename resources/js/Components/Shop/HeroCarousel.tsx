import Carousel from './Carousel';

/**
 * The storefront's hero banner: a "coming soon" notice first, then three
 * real Apotek Inofarma marketing banners. All are self-hosted under
 * `public/media/images/hero/` (same reasoning as utang teknis #3 in Fase 4:
 * never depend on a third-party host staying online for artwork the
 * storefront needs). The three marketing banners are re-encoded to JPEG at
 * the same 1600px/quality-82 the product-photo uploader already uses
 * (`App\Support\ProductImageUploader`) — the originals were 1.9-2.3MB PNGs
 * apiece, ~4.8MB total for one home-page load; these are ~250KB each. The
 * coming-soon banner ships as the ~85KB WebP it was uploaded as.
 */
const SLIDES = [
    { image: '/media/images/hero/coming-soon.webp', href: '/shop', alt: 'Coming soon! Website masih dalam persiapan, stay tuned' },
    { image: '/media/images/hero/hero-banner-1.jpg', href: '/shop', alt: 'Lebih hemat, lebih lengkap, belanja produk kesehatan di Apotek Inofarma' },
    { image: '/media/images/hero/hero-banner-2.jpg', href: '/signup', alt: 'Gabung Sobat Ino, daftar gratis dan dapatkan harga spesial' },
    { image: '/media/images/hero/hero-banner-3.jpg', href: '/cabang-kami', alt: 'Inofarma siap antar 24/7' },
];

export default function HeroCarousel({ className = 'mx-3.5 mt-3.5' }) {
    return <Carousel slides={SLIDES} aspect="aspect-video" className={className} />;
}
