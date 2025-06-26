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
          '50':  '#f0f5ff',
          '100': '#e5edff',
          '200': '#cddbfe',
          '300': '#b4c6fc',
          '400': '#8da2fb',
          '500': '#6875f5',
          '600': '#1e40af',
          '700': '#1e3a8a',
          '800': '#1e3a8a',
          '900': '#1e3a8a',
          '950': '#172554',
        }
      }
    },
  },
  plugins: [
    require('flowbite/plugin')
  ],
}
