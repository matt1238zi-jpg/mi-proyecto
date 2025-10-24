// tailwind.config.cjs
module.exports = {
  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.{vue,js}',
  ],
  theme: {
    extend: {
      keyframes: {
        'fade-in': {
          '0%': { opacity: 0 },
          '100%': { opacity: 1 },
        },
        'fade-in-up': {
          '0%': { opacity: 0, transform: 'translateY(8px)' },
          '100%': { opacity: 1, transform: 'translateY(0)' },
        },
        'pulse-soft': {
          '0%,100%': { transform: 'scale(1)' },
          '50%': { transform: 'scale(1.02)' },
        },
      },
      animation: {
        'fade-in': 'fade-in .35s ease-out both',
        'fade-in-up': 'fade-in-up .45s ease-out both',
        'pulse-soft': 'pulse-soft 1.6s ease-in-out infinite',
      },
    },
  },
  plugins: [],
}

module.exports = {
  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.{vue,js}',
  ],
  theme: {
    extend: {
      keyframes: {
        shake: {
          '0%,100%': { transform: 'translateX(0)' },
          '20%,60%': { transform: 'translateX(-6px)' },
          '40%,80%': { transform: 'translateX(6px)' },
        },
      },
      animation: {
        shake: 'shake .35s ease-in-out',
      },
    },
  },
  plugins: [],
}
