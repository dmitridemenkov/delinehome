/** @type {import('tailwindcss').Config} */
export default {
  content: ['./**/*.php'],
  theme: {
    extend: {
      colors: {
        primary: '#3B82F6',
        secondary: '#10B981', 
        accent: '#F59E0B',
        dark: '#1F2937',
        light: '#F9FAFB',
      },
      maxWidth: {
        'container': '1200px',
      },
      spacing: {
        'section': '80px',
        'block': '40px',
      },
    },
  },
}