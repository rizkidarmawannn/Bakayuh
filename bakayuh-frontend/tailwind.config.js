/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './index.html',
    './src/**/*.{vue,js,ts,jsx,tsx}',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
      colors: {
        gold: {
          DEFAULT: '#F5BA31',
          light: '#FCE082',
          dark: '#D89918',
        },
        kemenkum: {
          navy: '#0C2B64',
          'navy-dark': '#091F4A',
          'navy-light': '#1A3F7A',
          gold: '#F5BA31',
          'gold-light': '#FCE082',
          silver: '#C5D0E6',
          'silver-dark': '#A8B8D4',
        },
        surface: {
          DEFAULT: '#F8FAFC',
          elevated: '#FFFFFF',
          border: '#E2E8F0',
          'border-dark': '#CBD5E1',
        },
        status: {
          green: '#16A34A',
          'green-light': '#DCFCE7',
          yellow: '#CA8A04',
          'yellow-light': '#FEF9C3',
          red: '#DC2626',
          'red-light': '#FEE2E2',
          blue: '#2563EB',
          'blue-light': '#DBEAFE',
          gray: '#6B7280',
          'gray-light': '#F3F4F6',
        }
      },
      boxShadow: {
        card: '0 1px 3px 0 rgba(0,0,0,0.08), 0 1px 2px -1px rgba(0,0,0,0.06)',
        'card-hover': '0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.06)',
        sidebar: '2px 0 8px rgba(0,0,0,0.15)',
      },
      animation: {
        shimmer: 'shimmer 1.5s infinite',
        'fade-in': 'fadeIn 0.25s ease-out',
        'slide-in': 'slideIn 0.25s cubic-bezier(0.16, 1, 0.3, 1)',
      },
      keyframes: {
        shimmer: {
          '0%': { backgroundPosition: '-200% 0' },
          '100%': { backgroundPosition: '200% 0' },
        },
        fadeIn: {
          '0%': { opacity: '0', transform: 'translateY(4px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        slideIn: {
          '0%': { opacity: '0', transform: 'translateX(-8px)' },
          '100%': { opacity: '1', transform: 'translateX(0)' },
        },
      }
    },
  },
  plugins: [],
}