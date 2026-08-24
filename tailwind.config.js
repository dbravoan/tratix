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
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // TRATIX brand palette (Slate / Emerald)
                tratix: {
                    bg: '#0F172A',       // slate-900
                    surface: '#1E293B',  // slate-800
                    accent: '#10B981',   // emerald-500
                },
            },
        },
    },

    plugins: [forms],
};
