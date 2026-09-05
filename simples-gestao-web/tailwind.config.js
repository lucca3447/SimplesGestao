/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        simples: {
          orange: '#e06236',
          'orange-hover': '#c95229',
          'orange-light': '#fef2eb',
          'orange-border': '#fad9cc',
          green: '#208b5d',
          'green-hover': '#18724b',
          bg: '#f5f6f8',
          sidebar: '#ffffff',
          border: '#e5e7eb',
        },
      },
      fontFamily: {
        sans: ['"Plus Jakarta Sans"', 'system-ui', '-apple-system', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
