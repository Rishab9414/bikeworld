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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    red: 'var(--brand-red, #E31E24)',
                    black: 'var(--brand-black, #0A0A0A)',
                    dark: 'var(--brand-dark, #141414)',
                    gray: '#F4F4F5',
                },
            },
        },
    },

    plugins: [forms],
};
