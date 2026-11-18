import colors from 'tailwindcss/colors';
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
                // Geist: sans-serif modern yang bersih, nyaman di ukuran kecil maupun judul besar.
                sans: ['Geist', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Aksen utama: biru (tombol, link, status aktif).
                brand: colors.blue,
                // Abu netral ala shadcn: semua kelas gray-* memakai palet zinc.
                gray: colors.zinc,
            },
        },
    },

    plugins: [forms],
};
