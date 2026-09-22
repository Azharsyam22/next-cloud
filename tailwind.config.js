import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#EFF6FF',
                    100: '#DBEAFE',
                    200: '#BFDBFE',
                    300: '#93C5FD',
                    400: '#60A5FA',
                    500: '#3B82F6',
                    600: '#2563EB', // UIUX_BRIEF §7: Warna Primer Biru Kampus (#2563EB)
                    700: '#1D4ED8',
                    800: '#1E40AF',
                    900: '#1E3A8A',
                },
                accent: {
                    success: '#16A34A', // UIUX_BRIEF §7: Hijau (#16A34A)
                    danger: '#DC2626',  // UIUX_BRIEF §7: Merah (#DC2626)
                    warning: '#D97706', // UIUX_BRIEF §7: Kuning (#D97706)
                },
            },
            borderRadius: {
                'lg': '8px',
            },
        },
    },
    plugins: [forms],
};
