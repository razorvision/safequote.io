/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: ["class"],
  content: [
    './**/*.php',
    './assets/js/**/*.js',
    './assets/css/**/*.css',
    './src/css/**/*.css',
    // Exclude node_modules and vendor directories
    '!./node_modules/**',
    '!./vendor/**',
  ],
  theme: {
    container: {
      center: true,
      padding: "2rem",
      screens: {
        "2xl": "1400px",
      },
    },
    extend: {
      colors: {
        // Use absolute HSL values (not CSS variables) for WordPress theme
        border: "hsl(215, 28%, 90%)",
        input: "hsl(215, 28%, 85%)",
        ring: "hsl(195, 90%, 45%)",
        background: "hsl(0, 0%, 100%)",
        foreground: "hsl(224, 71.4%, 4.1%)",
        primary: {
          DEFAULT: "hsl(195, 90%, 40%)", // Teal
          foreground: "hsl(210, 20%, 98%)",
        },
        secondary: {
          DEFAULT: "hsl(35, 85%, 90%)", // Warm Sand
          foreground: "hsl(224, 71.4%, 4.1%)",
        },
        destructive: {
          DEFAULT: "hsl(0, 84.2%, 60.2%)",
          foreground: "hsl(210, 20%, 98%)",
        },
        muted: {
          DEFAULT: "hsl(210, 20%, 94%)",
          foreground: "hsl(215, 25%, 27%)",
        },
        accent: {
          DEFAULT: "hsl(210, 20%, 94%)",
          foreground: "hsl(224, 71.4%, 4.1%)",
        },
        popover: {
          DEFAULT: "hsl(0, 0%, 100%)",
          foreground: "hsl(224, 71.4%, 4.1%)",
        },
        card: {
          DEFAULT: "hsl(0, 0%, 100%)",
          foreground: "hsl(224, 71.4%, 4.1%)",
        },
        // Add teal colors for gradient support
        teal: {
          50: '#f0fdfa',
          100: '#ccfbf1',
          200: '#99f6e4',
          300: '#5eead4',
          400: '#2dd4bf',
          500: '#14b8a6',
          600: '#0d9488',
          700: '#0f766e',
          800: '#115e59',
          900: '#134e4a',
          950: '#042f2e',
        },
      },
      borderRadius: {
        lg: "var(--radius)",
        md: "calc(var(--radius) - 2px)",
        sm: "calc(var(--radius) - 4px)",
      },
      keyframes: {
        "accordion-down": {
          from: { height: 0 },
          to: { height: "var(--radix-accordion-content-height)" },
        },
        "accordion-up": {
          from: { height: "var(--radix-accordion-content-height)" },
          to: { height: 0 },
        },
      },
      animation: {
        "accordion-down": "accordion-down 0.2s ease-out",
        "accordion-up": "accordion-up 0.2s ease-out",
      },
    },
  },
  plugins: [],
}