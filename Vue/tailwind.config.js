/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./index.html",
        "./**/*.{vue,js,ts,jsx,tsx}",
        "!./node_modules",
    ],
    plugins: [require('tailwindcss-primeui')],
    theme: {
        extend: {},
    },

}
