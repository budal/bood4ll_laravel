import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

const colors = require('tailwindcss/colors')

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: "class",
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            colors: {
                primary: {
                    'light': colors.gray[800],
                    'light-hover': colors.gray[700],
                    'dark': colors.gray[200],
                    'dark-hover': colors.gray[50],
                },
                secondary: {
                    'light': colors.white,
                    'light-hover': colors.gray[50],
                    'dark': colors.gray[800],
                    'dark-hover': colors.gray[700],
                },
                danger: {
                    'light': colors.white,
                    'light-hover': colors.gray[50],
                    'dark': colors.gray[800],
                    'dark-hover': colors.gray[700],
                },
                success: {
                    'light': colors.white,
                    'light-hover': colors.gray[50],
                    'dark': colors.gray[800],
                    'dark-hover': colors.gray[700],
                },
                warning: {
                    'light': colors.yellow,
                    'light-hover': colors.yellow[50],
                    'dark': colors.yellow[800],
                    'dark-hover': colors.yellow[700],
                },
                info: {
                    'light': colors.white,
                    'light-hover': colors.gray[50],
                    'dark': colors.gray[800],
                    'dark-hover': colors.gray[700],
                },
            },
            textColor: {
                primary: {
                    'light': colors.gray[300],
                    'dark': colors.gray[700],
                },
                secondary: {
                    'light': colors.gray[700],
                    'dark': colors.gray[300],
                },
                danger: {
                    'light': colors.gray[700],
                    'dark': colors.gray[300],
                },
                success: {
                    'light': colors.gray[700],
                    'dark': colors.gray[300],
                },
                warning: {
                    'light': colors.gray[700],
                    'dark': colors.gray[300],
                },
                info: {
                    'light': colors.gray[700],
                    'dark': colors.gray[300],
                },
            },
            borderColor: {
                primary: {
                    'light': colors.gray[300],
                    'dark': colors.gray[500],
                },
                secondary: {
                    'light': colors.gray[300],
                    'dark': colors.gray[500],
                },
                danger: {
                    'light': colors.gray[300],
                    'dark': colors.gray[500],
                },
                success: {
                    'light': colors.gray[300],
                    'dark': colors.gray[500],
                },
                warning: {
                    'light': colors.gray[300],
                    'dark': colors.gray[500],
                },
                info: {
                    'light': colors.gray[300],
                    'dark': colors.gray[500],
                },
            },
            ringColor: {
                primary: {
                    'light': colors.gray[800],
                    'dark': colors.gray[200],
                },
                secondary: {
                    'light': colors.gray[400],
                    'dark': colors.gray[500],
                },
                danger: {
                    'light': colors.gray[400],
                    'dark': colors.gray[500],
                },
                success: {
                    'light': colors.gray[400],
                    'dark': colors.gray[500],
                },
                warning: {
                    'light': colors.gray[400],
                    'dark': colors.gray[500],
                },
                info: {
                    'light': colors.gray[400],
                    'dark': colors.gray[500],
                },
            },
            ringOffsetColor: {
                primary: {
                    'light': colors.gray[200],
                    'dark': colors.gray[800],
                },
                secondary: {
                    'light': colors.gray[200],
                    'dark': colors.gray[800],
                },
                danger: {
                    'light': colors.gray[200],
                    'dark': colors.gray[800],
                },
                success: {
                    'light': colors.gray[200],
                    'dark': colors.gray[800],
                },
                warning: {
                    'light': colors.gray[200],
                    'dark': colors.gray[800],
                },
                info: {
                    'light': colors.gray[200],
                    'dark': colors.gray[800],
                },
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
    corePlugins: {
        scrollbar: true,
    },
};
