import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';
import colors from 'tailwindcss/colors'; // IMPORTACIÓN NECESARIA PARA ACCEDER A LA PALETA COMPLETA

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            
            // AÑADIMOS LA EXTENSIÓN DE COLORES
            colors: {
                // Mantiene los colores por defecto
                ...defaultTheme.colors, 
                
                // 1. SOBRESCRITURA DE COLOR PRIMARIO DE JETSTREAM (Indigo -> Azul)
                // Cualquier componente de Jetstream que use 'indigo' ahora usará 'blue'.
                indigo: colors.blue, 
                
                // 2. Disponibiliza la paleta NARANJA para acentos.
                orange: colors.orange, 

                // 3. Mantiene 'blue' disponible también con su propio nombre (opcional)
                blue: colors.blue,
            },
        },
    },

    plugins: [forms, typography],
};