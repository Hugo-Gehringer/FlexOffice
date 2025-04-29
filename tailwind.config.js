/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./vendor/tales-from-a-dev/flowbite-bundle/templates/**/*.html.twig",
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#1D4ED8', // This makes `bg-primary` work
          600: '#1D4ED8',     // This makes `bg-primary-600` work
        }
      }
    },
  },
  plugins: [
    require('flowbite/plugin')
  ],
}
