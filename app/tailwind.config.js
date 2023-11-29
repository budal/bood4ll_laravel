import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

const plugin = require('tailwindcss/plugin')
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
            pattern: /grid-cols-(1|2|3|4|5|6|7|8|9)/,
            variants: ['sm'],
        },
        {
            pattern: /col-span-(1|2|3|4|5|6|7|8|9)/,
            variants: ['sm'],
        },
        {
            pattern: /(bg|border|ring|ring-offset|text)-(zero|primary|secondary|danger|success|warning|info)-(light|dark)/,
            variants: [
                'dark', 
                'hover', 
                'dark:hover', 
                'focus', 
                'dark:focus', 
                'autofill', 
                'dark:autofill', 
                'data-[state=on]', 
                'data-[state=on]:hover', 
                'data-[state=on]:dark', 
                'data-[state=on]:dark:hover', 
                'data-[state=off]', 
                'data-[state=off]:hover', 
                'data-[state=off]:dark',
                'data-[state=off]:dark:hover', 
            ],
        },
    ],
    theme: {
        extend: {
            colors: {
                zero: {
                    'white': colors.white,
                    'light': colors.gray[100],
                    'light-hover': colors.gray[200],
                    'dark': colors.gray[800],
                    'dark-hover': colors.gray[700],
                    'black': colors.gray[900],
                },
                primary: {
                    'light': colors.gray[800],
                    'light-hover': colors.gray[600],
                    'dark': colors.gray[100],
                    'dark-hover': colors.gray[300],
                },
                secondary: {
                    'light': colors.white,
                    'light-hover': colors.gray[200],
                    'dark': colors.gray[700],
                    'dark-hover': colors.gray[600],
                },
                danger: {
                    'light': colors.red[600],
                    'light-hover': colors.red[400],
                    'dark': colors.red[800],
                    'dark-hover': colors.red[600],
                },
                success: {
                    'light': colors.green[600],
                    'light-hover': colors.green[400],
                    'dark': colors.green[800],
                    'dark-hover': colors.green[600],
                },
                warning: {
                    'light': colors.yellow[400],
                    'light-hover': colors.yellow[200],
                    'dark': colors.yellow[600],
                    'dark-hover': colors.yellow[400],
                },
                info: {
                    'light': colors.blue[600],
                    'light-hover': colors.blue[400],
                    'dark': colors.blue[800],
                    'dark-hover': colors.blue[600],
                },
            },
            textColor: {
                zero: {
                    'light': colors.gray[700],
                    'dark': colors.gray[300],
                },
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
                zero: {
                    'light': colors.gray[300],
                    'dark': colors.gray[600],
                },
                primary: {
                    'light': colors.gray[700],
                    'dark': colors.gray[200],
                },
                secondary: {
                    'light': colors.gray[200],
                    'dark': colors.gray[600],
                },
                danger: {
                    'light': colors.red[500],
                    'dark': colors.red[700],
                },
                success: {
                    'light': colors.green[500],
                    'dark': colors.green[700],
                },
                warning: {
                    'light': colors.yellow[500],
                    'dark': colors.yellow[700],
                },
                info: {
                    'light': colors.blue[500],
                    'dark': colors.blue[700],
                },
            },
            ringColor: {
                zero: {
                    'light': colors.gray[800],
                    'dark': colors.gray[200],
                },
                primary: {
                    'light': colors.gray[800],
                    'dark': colors.gray[200],
                },
                secondary: {
                    'light': colors.gray[400],
                    'dark': colors.gray[500],
                },
                danger: {
                    'light': colors.red[500],
                    'dark': colors.red[700],
                },
                success: {
                    'light': colors.green[500],
                    'dark': colors.green[700],
                },
                warning: {
                    'light': colors.yellow[500],
                    'dark': colors.yellow[700],
                },
                info: {
                    'light': colors.blue[500],
                    'dark': colors.blue[700],
                },
            },
            ringOffsetColor: {
                zero: {
                    'light': colors.gray[200],
                    'dark': colors.gray[800],
                },
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
                    'light': colors.yellow[300],
                    'dark': colors.yellow[900],
                },
                info: {
                    'light': colors.blue[300],
                    'dark': colors.blue[900],
                },
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            keyframes: {
                enterFromRight: {
                    from: { opacity: 0, transform: 'translateX(200px)' },
                    to: { opacity: 1, transform: 'translateX(0)' },
                },
                enterFromLeft: {
                    from: { opacity: 0, transform: 'translateX(-200px)' },
                    to: { opacity: 1, transform: 'translateX(0)' },
                },
                exitToRight: {
                    from: { opacity: 1, transform: 'translateX(0)' },
                    to: { opacity: 0, transform: 'translateX(200px)' },
                },
                exitToLeft: {
                    from: { opacity: 1, transform: 'translateX(0)' },
                    to: { opacity: 0, transform: 'translateX(-200px)' },
                },
                scaleIn: {
                    from: { opacity: 0, transform: 'rotateX(-10deg) scale(0.9)' },
                    to: { opacity: 1, transform: 'rotateX(0deg) scale(1)' },
                },
                scaleOut: {
                    from: { opacity: 1, transform: 'rotateX(0deg) scale(1)' },
                    to: { opacity: 0, transform: 'rotateX(-10deg) scale(0.95)' },
                },
                fadeIn: {
                    from: { opacity: 0 },
                    to: { opacity: 1 },
                },
                fadeOut: {
                    from: { opacity: 1 },
                    to: { opacity: 0 },
                },
                slideDownAndFade: {
                    from: { opacity: 0, transform: 'translateY(-20px)' },
                    to: { opacity: 1, transform: 'translateY(0)' },
                },
                slideLeftAndFade: {
                    from: { opacity: 0, transform: 'translateX(20px)' },
                    to: { opacity: 1, transform: 'translateX(0)' },
                },
                slideUpAndFade: {
                    from: { opacity: 0, transform: 'translateY(20px)' },
                    to: { opacity: 1, transform: 'translateY(0)' },
                },
                slideRightAndFade: {
                    from: { opacity: 0, transform: 'translateX(-20px)' },
                    to: { opacity: 1, transform: 'translateX(0)' },
                },
                overlayShow: {
                    from: { opacity: 0 },
                    to: { opacity: 1 },
                },
                contentShow: {
                    from: { opacity: 0, transform: 'translate(-50%, -48%) scale(0.96)' },
                    to: { opacity: 1, transform: 'translate(-50%, -50%) scale(1)' },
                },
                overlayHide: {
                    from: { opacity: 1 },
                    to: { opacity: 0 },
                },
                contentHide: {
                    from: { opacity: 1, transform: 'translate(-50%, -48%) scale(0.96)' },
                    to: { opacity: 0, transform: 'translate(-50%, -50%) scale(1)' },
                },
            },
            animation: {
                scaleIn: 'scaleIn 200ms ease',
                scaleOut: 'scaleOut 200ms ease',
                fadeIn: 'fadeIn 200ms ease',
                fadeOut: 'fadeOut 200ms ease',
                enterFromLeft: 'enterFromLeft 250ms ease',
                enterFromRight: 'enterFromRight 250ms ease',
                exitToLeft: 'exitToLeft 250ms ease',
                exitToRight: 'exitToRight 250ms ease',
                slideDownAndFade: 'slideDownAndFade 400ms cubic-bezier(0.16, 1, 0.3, 1)',
                slideLeftAndFade: 'slideLeftAndFade 400ms cubic-bezier(0.16, 1, 0.3, 1)',
                slideUpAndFade: 'slideUpAndFade 400ms cubic-bezier(0.16, 1, 0.3, 1)',
                slideRightAndFade: 'slideRightAndFade 400ms cubic-bezier(0.16, 1, 0.3, 1)',
                overlayShow: 'overlayShow 300ms cubic-bezier(0.16, 1, 0.3, 1)',
                contentShow: 'contentShow 300ms cubic-bezier(0.16, 1, 0.3, 1)',
                overlayHide: 'overlayHide 300ms cubic-bezier(0.16, 1, 0.3, 1)',
                contentHide: 'contentHide 300ms cubic-bezier(0.16, 1, 0.3, 1)',
            },
        },
    },
    plugins: [
        forms,
        plugin(({ matchUtilities }) => {
            matchUtilities({
                perspective: value => ({
                    perspective: value,
                }),
            })
        }),
    ],
    corePlugins: {
        scrollbar: true,
    },
};
