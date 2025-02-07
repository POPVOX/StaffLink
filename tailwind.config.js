import defaultTheme from 'tailwindcss/defaultTheme';
import colors from 'tailwindcss/colors';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'selector',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        "./vendor/livewire/flux-pro/stubs/**/*.blade.php",
        "./vendor/livewire/flux/stubs/**/*.blade.php",
    ],
    theme: {
        extend: {
            colors: {
                // Re-assign Flux's gray of choice...
                zinc: colors.gray,

                // Accent variables are defined in resources/css/app.css...
                accent: {
                    DEFAULT: 'var(--color-accent)',
                    content: 'var(--color-accent-content)',
                    foreground: 'var(--color-accent-foreground)',
                },

                'pvox-orange': '#FD8161',
                'pvox-dark': '#2d3748',
                'pvox-link-dark': '#ff653f',
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [],
};
