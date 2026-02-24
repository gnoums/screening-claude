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
                primary: {
                    50:  '#f4f7ee',
                    100: '#e6edcf',
                    200: '#cddc9f',
                    300: '#afc56d',
                    400: '#8eaa44',
                    500: '#718e2d',
                    600: '#5a7123',
                    700: '#45581c',
                    800: '#394617',
                    900: '#303c15',
                    950: '#172009',
                },
            },
        },
    },

    plugins: [forms],
};
