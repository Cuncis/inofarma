import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Lato', 'Figtree', ...defaultTheme.fontFamily.sans],
                display: ['"Tenor Sans"', ...defaultTheme.fontFamily.serif],
            },

            /**
             * Storefront palette.
             *
             * Three brand colours drive everything: `brand` (#0900AA), `success`
             * (#24CE30) and `warning` (#FE7900). `blush` is a light tint of brand
             * for panels and headers; the `deep` shades exist because the bright
             * green and orange are unreadable as text on a light background — use
             * the DEFAULT for fills and icons, the `deep` shade for text.
             */
            colors: {
                /**
                 * `lift` is the dark-mode step of the brand hue. #0900AA scores
                 * 1.2:1 against the dark chart surface — invisible — so charts
                 * switch to this step there rather than flipping automatically.
                 */
                brand: {
                    DEFAULT: '#0900AA',
                    lift: '#6C63FF',
                },
                blush: '#ebebf8',
                ink: '#222222',
                lilac: '#faf9ff',
                shell: '#d8dce3',
                star: '#FE7900',
                line: '#eeeeee',
                muted: '#666666',
                faint: '#999999',
                sale: '#24CE30',
                success: {
                    DEFAULT: '#24CE30',
                    deep: '#147A1D',
                },
                warning: {
                    DEFAULT: '#FE7900',
                    deep: '#B35400',
                },
                /** Button text color, sitting on the `success` green button background. */
                cream: '#FFEFE9',
                /** The app's default page background, everywhere neither the storefront nor a screen picks something else on purpose. */
                canvas: '#F3F5F6',

                /**
                 * Admin surfaces and text, carried over from the source theme's
                 * proportions but repainted with the palette above. `danger` has
                 * no counterpart in the three brand colours — destructive actions
                 * still need a red that reads as "stop".
                 */
                admin: {
                    bg: '#F3F5F6',
                    nav: '#ffffff',
                    card: '#ffffff',
                    heading: '#313b5e',
                    body: '#5d7186',
                    muted: '#8a99ad',
                    border: '#e6e9f0',
                    hover: '#f2f3f9',
                    dark: {
                        bg: '#161a25',
                        nav: '#1d2231',
                        card: '#1d2231',
                        heading: '#e6e9f0',
                        body: '#a4aec1',
                        muted: '#7a869c',
                        border: '#2b3245',
                        hover: '#252b3c',
                    },
                },
                danger: {
                    DEFAULT: '#ef5f5f',
                    deep: '#c62828',
                },
                info: {
                    DEFAULT: '#4ecac2',
                    deep: '#0f766e',
                },
            },

            maxWidth: {
                app: '430px',
            },

            spacing: {
                appbar: '48px',
                tabbar: '60px',
                control: '52px',
                topbar: '70px',
                sidebar: '280px',
                'sidebar-sm': '76px',
            },

            boxShadow: {
                card: '0 3px 4px 0 rgba(0, 0, 0, 0.03)',
                pop: '0 8px 24px -4px rgba(19, 25, 43, 0.14)',
            },
        },
    },

    plugins: [forms],
};
