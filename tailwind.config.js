import defaultTheme from 'tailwindcss/defaultTheme';
import colors from 'tailwindcss/colors';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Outfit Variable"', ...defaultTheme.fontFamily.sans],
            },

            // Um acento so (verde floresta). `indigo` e remapeado para ele, assim
            // todas as telas antigas herdam a nova marca sem reescrever classe por classe.
            // Neutros: `gray` aponta para zinc (uma paleta so, sem alternar quente/frio).
            colors: {
                brand: {
                    50: 'rgb(var(--brand-50) / <alpha-value>)',
                    100: 'rgb(var(--brand-100) / <alpha-value>)',
                    200: 'rgb(var(--brand-200) / <alpha-value>)',
                    300: '#7fd2a9',
                    400: '#4ab986',
                    500: '#279d6b',
                    600: '#1a7f56',
                    700: '#166647',
                    800: '#14513a',
                    900: '#114230',
                    950: '#08261c',
                },
                indigo: {
                    50: 'rgb(var(--brand-50) / <alpha-value>)',
                    100: 'rgb(var(--brand-100) / <alpha-value>)',
                    200: 'rgb(var(--brand-200) / <alpha-value>)',
                    300: '#7fd2a9',
                    400: '#4ab986',
                    500: '#279d6b',
                    600: '#1a7f56',
                    700: '#166647',
                    800: '#14513a',
                    900: '#114230',
                    950: '#08261c',
                },
                // Neutros e superficie trocam por variavel CSS (ver app.css). `zinc` segue fixo,
                // para o que precisa ficar escuro ou claro nos dois temas (ex.: gradientes sobre foto).
                gray: {
                    50: 'rgb(var(--gray-50) / <alpha-value>)',
                    100: 'rgb(var(--gray-100) / <alpha-value>)',
                    200: 'rgb(var(--gray-200) / <alpha-value>)',
                    300: 'rgb(var(--gray-300) / <alpha-value>)',
                    400: 'rgb(var(--gray-400) / <alpha-value>)',
                    500: 'rgb(var(--gray-500) / <alpha-value>)',
                    600: 'rgb(var(--gray-600) / <alpha-value>)',
                    700: 'rgb(var(--gray-700) / <alpha-value>)',
                    800: 'rgb(var(--gray-800) / <alpha-value>)',
                    900: 'rgb(var(--gray-900) / <alpha-value>)',
                    950: 'rgb(var(--gray-950) / <alpha-value>)',
                },
                surface: 'rgb(var(--surface) / <alpha-value>)',
            },

            // Escala de raio unica: 10 / 12 / 16 / 20. Botoes e inputs 10, cards 16.
            borderRadius: {
                md: '0.625rem',
                lg: '0.75rem',
                xl: '1rem',
                '2xl': '1.25rem',
            },

            // Sombras suaves, tintadas no verde-escuro (nunca preto puro).
            boxShadow: {
                xs: '0 1px 2px 0 rgb(8 38 28 / 0.05)',
                sm: '0 1px 2px 0 rgb(8 38 28 / 0.06), 0 1px 3px 0 rgb(8 38 28 / 0.05)',
                md: '0 2px 4px -1px rgb(8 38 28 / 0.06), 0 6px 12px -2px rgb(8 38 28 / 0.07)',
                lg: '0 4px 8px -2px rgb(8 38 28 / 0.07), 0 14px 28px -6px rgb(8 38 28 / 0.1)',
                xl: '0 8px 16px -4px rgb(8 38 28 / 0.08), 0 24px 48px -12px rgb(8 38 28 / 0.16)',
            },

            // Curvas do guia de motion (as nativas do CSS sao fracas demais).
            transitionTimingFunction: {
                out: 'cubic-bezier(0.23, 1, 0.32, 1)',
                'in-out-strong': 'cubic-bezier(0.77, 0, 0.175, 1)',
                drawer: 'cubic-bezier(0.32, 0.72, 0, 1)',
            },

            keyframes: {
                'rise-in': {
                    from: { opacity: '0', transform: 'translateY(8px)' },
                    to: { opacity: '1', transform: 'translateY(0)' },
                },
                'pop-in': {
                    from: { opacity: '0', transform: 'scale(0.96)' },
                    to: { opacity: '1', transform: 'scale(1)' },
                },
            },
            animation: {
                'rise-in': 'rise-in 320ms cubic-bezier(0.23, 1, 0.32, 1) both',
                'pop-in': 'pop-in 160ms cubic-bezier(0.23, 1, 0.32, 1) both',
            },
        },
    },

    plugins: [forms],
};
