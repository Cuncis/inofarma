import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
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
                brand: '#0900AA',
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
            },

            maxWidth: {
                app: '430px',
            },

            spacing: {
                appbar: '48px',
                tabbar: '60px',
                control: '52px',
            },
        },
    },

    plugins: [forms],
};
