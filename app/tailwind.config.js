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
    safelist: [
        {
          pattern: /grid-cols-(1|2|3|4|5)/,
          variants: ['sm', 'hover', 'focus'],
        },
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
                    'light': colors.red[600],
                    'light-hover': colors.red[500],
                    'dark': colors.red[600],
                    'dark-hover': colors.red[500],
                },
                success: {
                    'light': colors.green[700],
                    'light-hover': colors.green[600],
                    'dark': colors.green[700],
                    'dark-hover': colors.green[600],
                },
                warning: {
                    'light': colors.yellow[400],
                    'light-hover': colors.yellow[300],
                    'dark': colors.yellow[400],
                    'dark-hover': colors.yellow[300],
                },
                info: {
                    'light': colors.blue[800],
                    'light-hover': colors.blue[700],
                    'dark': colors.blue[800],
                    'dark-hover': colors.blue[700],
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
                    'light': colors.white,
                    'dark': colors.white,
                },
                success: {
                    'light': colors.white,
                    'dark': colors.white,
                },
                warning: {
                    'light': colors.black,
                    'dark': colors.black,
                },
                info: {
                    'light': colors.white,
                    'dark': colors.white,
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
                    'light': colors.red[700],
                    'dark': colors.red[700],
                },
                success: {
                    'light': colors.green[800],
                    'dark': colors.green[800],
                },
                warning: {
                    'light': colors.yellow[500],
                    'dark': colors.yellow[500],
                },
                info: {
                    'light': colors.blue[700],
                    'dark': colors.blue[700],
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
                    'light': colors.red[700],
                    'dark': colors.red[700],
                },
                success: {
                    'light': colors.green[800],
                    'dark': colors.green[800],
                },
                warning: {
                    'light': colors.yellow[500],
                    'dark': colors.yellow[500],
                },
                info: {
                    'light': colors.blue[700],
                    'dark': colors.blue[700],
                },
            },
            ringOffsetColor: {
                primary: {
                    'light': colors.gray[300],
                    'dark': colors.gray[800],
                },
                secondary: {
                    'light': colors.gray[200],
                    'dark': colors.gray[800],
                },
                danger: {
                    'light': colors.red[300],
                    'dark': colors.red[900],
                },
                success: {
                    'light': colors.green[300],
                    'dark': colors.green[900],
                },
                warning: {
                    'light': colors.yellow[200],
                    'dark': colors.yellow[900],
                },
                info: {
                    'light': colors.blue[200],
                    'dark': colors.blue[950],
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
