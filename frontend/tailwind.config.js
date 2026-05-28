
/* lutfeeya /adminDashboard */

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        navy: {
          DEFAULT: '#093C5D',
          light: '#0B4A72',
          dark: '#062C45',
        },
        blue: {
          DEFAULT: '#3B7597',
          light: '#4D8AAD',
          dark: '#2D5A75',
        },
        olive: {
          DEFAULT: '#9CB07A',
          light: '#B2C496',
          dark: '#7E8F62',
        },
        background: '#F5F5F5',
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
      },
    },
  },
  plugins: [],
}

/* lutfeeya /adminDashboard */
