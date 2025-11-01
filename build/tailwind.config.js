/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    '../**/*.php',
    '../assets/**/*.js',
    '../dist/**/*.js'
  ],
  safelist: [
    // Layout utilities
    'flex', 'flex-row', 'flex-col', 'flex-1', 'flex-wrap',
    'md:flex', 'md:flex-row', 'md:flex-col', 'md:block', 'md:hidden',
    'lg:flex', 'lg:flex-row', 'lg:flex-col', 'lg:block', 'lg:hidden',
    'sm:flex', 'sm:flex-row', 'sm:flex-col',
    // Width utilities
    'w-64', 'w-full', 'md:w-64', 'lg:w-64', 'md:w-1/2', 'md:w-1/3', 'md:w-2/3',
    // Spacing
    'px-4', 'px-6', 'px-8', 'py-8', 'sm:px-6', 'lg:px-8',
    'gap-4', 'gap-6', 'gap-8',
  ],
  theme: {
    extend: {
      colors: {
        light: {
          50: '#fafafa',
          100: '#f5f5f5',
          200: '#e5e5e5',
          300: '#d4d4d4',
          400: '#a3a3a3',
          500: '#737373',
          600: '#525252',
          700: '#404040',
          800: '#262626',
          900: '#171717',
          950: '#0a0a0a',
        },
        primary: {
          50: '#f0f9ff',
          100: '#e0f2fe',
          200: '#bae6fd',
          300: '#7dd3fc',
          400: '#38bdf8',
          500: '#0ea5e9',
          600: '#0284c7',
          700: '#0369a1',
          800: '#075985',
          900: '#0c4a6e',
        },
        accent: {
          50: '#fdf4ff',
          100: '#fae8ff',
          200: '#f5d0fe',
          300: '#f0abfc',
          400: '#e879f9',
          500: '#d946ef',
          600: '#c026d3',
          700: '#a21caf',
          800: '#86198f',
          900: '#701a75',
        }
      }
    },
  },
  plugins: [],
}
