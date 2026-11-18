import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                // Plus Jakarta Sans: humanis, sangat terbaca di ukuran kecil (tabel/form).
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Warna utama "Afindo Learn": teal tua -> tenang, terkesan edukatif & tepercaya.
                brand: {
                    50: '#eefbf7',
                    100: '#d5f5ea',
                    200: '#aeead8',
                    300: '#79d8c0',
                    400: '#43bea3',
                    500: '#22a38a',
                    600: '#168471',
                    700: '#146a5d',
                    800: '#14554c',
                    900: '#134640',
                    950: '#062825',
                },
            },
        },
    },

    plugins: [forms],
};
