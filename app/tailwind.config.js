/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./vendor/tales-from-a-dev/flowbite-bundle/templates/**/*.html.twig",
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  darkMode: 'class', // or 'media' or 'class'
  theme: {
    extend: {
      colors:{
        background: 'var(--bg-color)',
        text: 'var(--text-color)',
        primary: {
          600: '#1D4ED8', // Define your primary-600 color here
        },
      }
    },
  },
  plugins: [
    require('flowbite/plugin')
  ],
}
