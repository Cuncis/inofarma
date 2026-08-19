import { useEffect, useRef, useState } from 'react';
import { Link } from '@inertiajs/react';

/**
 * The storefront's hero banner — three real Apotek Inofarma marketing
 * banners, self-hosted under `public/media/images/hero/` (same reasoning as
 * utang teknis #3 in Fase 4: never depend on a third-party host staying
 * online for artwork the storefront needs) and re-encoded to JPEG at the
 * same 1600px/quality-82 the product-photo uploader already uses
 * (`App\Support\ProductImageUploader`) — the originals were 1.9-2.3MB PNGs
 * apiece, ~4.8MB total for one home-page load; these are ~250KB each.
 * Auto-advances, but a tap on a dot or a swipe both take over immediately
 * and reset the timer, so it never fights a shopper actively looking at
 * one slide.
 */
const SLIDES = [
    { image: '/media/images/hero/hero-banner-1.jpg', href: '/ui/shop', alt: 'Lebih hemat, lebih lengkap — belanja produk kesehatan di Apotek Inofarma' },
    { image: '/media/images/hero/hero-banner-2.jpg', href: '/ui/signup', alt: 'Gabung Sobat Ino — daftar gratis dan dapatkan harga spesial' },
    { image: '/media/images/hero/hero-banner-3.jpg', href: '/ui/cabang-kami', alt: 'Inofarma siap antar 24/7' },
];

const AUTO_ADVANCE_MS = 5000;

export default function HeroCarousel() {
    const [index, setIndex] = useState(0);
    const touchStartX = useRef(null);
    const drag = useRef({ active: false, startX: 0, moved: false });
    const timerRef = useRef(null);

    const restartTimer = () => {
        clearInterval(timerRef.current);
        timerRef.current = setInterval(() => {
            setIndex((current) => (current + 1) % SLIDES.length);
        }, AUTO_ADVANCE_MS);
    };

    useEffect(() => {
        restartTimer();

        return () => clearInterval(timerRef.current);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const goTo = (next) => {
        setIndex(next);
        restartTimer();
    };

    const onTouchStart = (event) => {
        touchStartX.current = event.touches[0].clientX;
    };

    const onTouchEnd = (event) => {
        if (touchStartX.current === null) {
            return;
        }

        const delta = event.changedTouches[0].clientX - touchStartX.current;
        touchStartX.current = null;

        if (Math.abs(delta) < 40) {
            return;
        }

        goTo(delta < 0
            ? (index + 1) % SLIDES.length
            : (index - 1 + SLIDES.length) % SLIDES.length);
    };

    // Touch already swipes natively through onTouchStart/onTouchEnd above —
    // this is the desktop counterpart, a held mouse cursor dragged sideways.
    // Tracked with window-level mousemove/mouseup (not pointer capture) so a
    // release outside the carousel still ends the drag cleanly instead of
    // leaving the "just dragged" flag stuck and silently blocking every
    // click afterwards.
    useEffect(() => {
        const onMouseMove = (event) => {
            if (! drag.current.active) {
                return;
            }

            // Below this, a plain click's natural mousedown-to-mouseup drift
            // (a trackpad especially) would otherwise register as a drag and
            // swallow the click that opens the slide's link.
            if (Math.abs(event.clientX - drag.current.startX) > 8) {
                drag.current.moved = true;
            }
        };

        const onMouseUp = (event) => {
            if (! drag.current.active) {
                return;
            }

            const delta = event.clientX - drag.current.startX;
            drag.current.active = false;

            if (Math.abs(delta) >= 40) {
                goTo(delta < 0
                    ? (index + 1) % SLIDES.length
                    : (index - 1 + SLIDES.length) % SLIDES.length);
            }

            // See useDragScroll.js for why this is deferred a tick: it lets
            // the click that immediately follows (when the drag ended back
            // over the carousel) still see `moved` true and get suppressed,
            // while still clearing it for the next interaction either way.
            setTimeout(() => {
                drag.current.moved = false;
            }, 0);
        };

        window.addEventListener('mousemove', onMouseMove);
        window.addEventListener('mouseup', onMouseUp);

        return () => {
            window.removeEventListener('mousemove', onMouseMove);
            window.removeEventListener('mouseup', onMouseUp);
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [index]);

    const onMouseDown = (event) => {
        if (event.button !== 0) {
            return;
        }

        drag.current = { active: true, startX: event.clientX, moved: false };
    };

    // A mouse drag ends with the cursor over a slide, so the browser follows
    // it with a click — swallow that click when it was really a drag, or the
    // Link underneath would navigate away instead of just changing slides.
    const onClickCapture = (event) => {
        if (drag.current.moved) {
            event.preventDefault();
            event.stopPropagation();
            drag.current.moved = false;
        }
    };

    return (
        <div className="relative mx-3.5 mt-3.5 overflow-hidden rounded-lg border border-line">
            <div
                className="flex cursor-grab select-none transition-transform duration-500 ease-out active:cursor-grabbing"
                style={{ transform: `translateX(-${index * 100}%)` }}
                onTouchStart={onTouchStart}
                onTouchEnd={onTouchEnd}
                onMouseDown={onMouseDown}
                onClickCapture={onClickCapture}
            >
                {SLIDES.map((slide) => (
                    <Link
                        key={slide.image}
                        href={slide.href}
                        className="aspect-[16/9] w-full shrink-0"
                    >
                        <img
                            src={slide.image}
                            alt={slide.alt}
                            className="h-full w-full object-cover"
                        />
                    </Link>
                ))}
            </div>

            <div className="absolute bottom-2.5 left-0 flex w-full justify-center gap-1.5">
                {SLIDES.map((slide, slideIndex) => (
                    <button
                        key={slide.image}
                        type="button"
                        onClick={() => goTo(slideIndex)}
                        aria-label={`Slide ${slideIndex + 1}`}
                        className={`h-1.5 rounded-full transition-all ${
                            slideIndex === index ? 'w-5 bg-white' : 'w-1.5 bg-white/60'
                        }`}
                    />
                ))}
            </div>
        </div>
    );
}
