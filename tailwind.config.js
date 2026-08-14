import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Lato', 'Figtree', ...defaultTheme.fontFamily.sans],
                display: ['"Tenor Sans"', ...defaultTheme.fontFamily.serif],
            },

            colors: {
                brand: '#d05278',
                blush: '#fcedea',
                ink: '#222222',
                lilac: '#faf9ff',
                shell: '#d8dce3',
                star: '#F5C102',
                line: '#eeeeee',
                muted: '#666666',
                faint: '#999999',
                sale: '#a3d2a2',
                success: '#4caf50',
                warning: '#EF962D',
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
