/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./index.html",
        "./**/*.{vue,js,ts,jsx,tsx}",
        "!./node_modules",
    ],
    plugins: [require('tailwindcss-primeui')],
    theme: {
        extend: {
          colors: {
            gray: {
              50: "#F6F7F9",
              100: "#ededf1",
              200: "#b5b7be",
              300: "#91949e",
              400: "#6c707d",
              500: "#474c5d",
              600: "#393d4a",
              700: "#2b2e38",
              800: "#1c1e25",
              900: "#0e0f13"
            },
            info: {
              100: "#d1defd",
              200: "#a3bcfc",
              300: "#759bfa",
              400: "#4779f9",
              500: "#1958f7",
              600: "#1446c6",
              700: "#0f3594",
              800: "#0a2363",
              900: "#051231"
            },
            success: {
              100: "#cfece2",
              200: "#9fd9c5",
              300: "#6ec5a8",
              400: "#3eb28b",
              500: "#0e9f6e",
              600: "#0b7f58",
              700: "#085f42",
              800: "#06402c",
              900: "#032016"
            },
            danger: {
              100: "#f9d3d3",
              200: "#f3a7a7",
              300: "#ec7c7c",
              400: "#e65050",
              500: "#e02424",
              600: "#b31d1d",
              700: "#861616",
              800: "#5a0e0e",
              900: "#2d0707"
            },
            warning: {
              100: "#fef4d0",
              200: "#fdeaa1",
              300: "#fcdf73",
              400: "#fbd544",
              500: "#faca15",
              600: "#c8a211",
              700: "#96790d",
              800: "#645108",
              900: "#322804"
            },
          },
          boxShadow: {
            'card': '0px 2px 16px 0px #D7D9E0CC',
          }
        },
      },

}
