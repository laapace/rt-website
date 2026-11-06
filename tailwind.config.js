import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Palet design.md §2.1 — jangan pakai warna di luar daftar ini
                primary: {
                    100: '#DCFCE7',
                    600: '#15803D',
                    700: '#14532D',
                },
                accent: {
                    500: '#D97706',
                },
                success: {
                    600: '#15803D',
                },
                warning: {
                    600: '#B45309',
                },
                danger: {
                    600: '#B91C1C',
                },
                info: {
                    600: '#1D4ED8',
                },
            },
        },
    },

    plugins: [forms],
};
