/**
 * Build mobileshop Tailwind CSS — run with: node build-mobileshop-css.js
 * Generates public/css/mobileshop-panel.css (replaces cdn.tailwindcss.com)
 */
const postcss = require('./node_modules/postcss');
const tailwindcss = require('./node_modules/tailwindcss');
const autoprefixer = require('./node_modules/autoprefixer');
const fs = require('fs');
const path = require('path');

const inputSourcePath = path.resolve(__dirname, 'resources/assets/sass/mobileshop.css');
const inputCSS = fs.existsSync(inputSourcePath)
    ? fs.readFileSync(inputSourcePath, 'utf8')
    : `@tailwind base;\n@tailwind components;\n@tailwind utilities;`;

// Mobileshop-specific Tailwind config (mirrors the linear light & shadcn design system tokens)
const mobileshopConfig = {
    content: [
        './resources/views/mobileshop/**/*.blade.php',
        './resources/views/auth/**/*.blade.php',
        './resources/views/components/ui/**/*.blade.php',
        './resources/views/components/**/*.blade.php',
    ],
    corePlugins: {
        preflight: false, // Don't reset base styles — admin-panel.css handles it
    },
    theme: {
        extend: {
            colors: {
                border: "hsl(var(--border, 214.3 31.8% 91.4%) / <alpha-value>)",
                input: "hsl(var(--input, 214.3 31.8% 91.4%) / <alpha-value>)",
                ring: "hsl(var(--ring, 236 57% 59%) / <alpha-value>)",
                background: "hsl(var(--background, 0 0% 100%) / <alpha-value>)",
                foreground: "hsl(var(--foreground, 222.2 84% 4.9%) / <alpha-value>)",
                card: {
                    DEFAULT: "hsl(var(--card, 0 0% 100%) / <alpha-value>)",
                    foreground: "hsl(var(--card-foreground, 222.2 84% 4.9%) / <alpha-value>)",
                },
                popover: {
                    DEFAULT: "hsl(var(--popover, 0 0% 100%) / <alpha-value>)",
                    foreground: "hsl(var(--popover-foreground, 222.2 84% 4.9%) / <alpha-value>)",
                },
                secondary: {
                    DEFAULT: "hsl(var(--secondary, 210 40% 96.1%) / <alpha-value>)",
                    foreground: "hsl(var(--secondary-foreground, 222.2 47.4% 11.2%) / <alpha-value>)",
                },
                muted: {
                    DEFAULT: "hsl(var(--muted, 210 40% 96.1%) / <alpha-value>)",
                    foreground: "hsl(var(--muted-foreground, 215.4 16.3% 46.9%) / <alpha-value>)",
                },
                accent: {
                    DEFAULT: "hsl(var(--accent, 210 40% 96.1%) / <alpha-value>)",
                    foreground: "hsl(var(--accent-foreground, 222.2 47.4% 11.2%) / <alpha-value>)",
                },
                destructive: {
                    DEFAULT: "hsl(var(--destructive, 0 84.2% 60.2%) / <alpha-value>)",
                    foreground: "hsl(var(--destructive-foreground, 210 40% 98%) / <alpha-value>)",
                },
                canvas: "#ffffff",
                surface: {
                    1: "#f7f8fa",
                    2: "#f1f2f4",
                    3: "#e9ebee",
                    4: "#e2e5e9",
                },
                ink: {
                    DEFAULT: "#111113",
                    muted: "#4f535b",
                    subtle: "#737780",
                    tertiary: "#9a9ea6",
                },
                primary: {
                    DEFAULT: "#5e6ad2",
                    hover: "#4f5bc2",
                    focus: "#5e69d1",
                    tint: "#f0f2ff",
                },
                hairline: {
                    DEFAULT: "#e2e4e8",
                    strong: "#cfd3d9",
                    tertiary: "#bcc1c8",
                },
                success: "#27a644",
                danger: "#eb5757",
                brand: {
                    50: '#F0FDFA',
                    100: '#CCFBF1',
                    200: '#99F6E4',
                    300: '#5EEAD4',
                    400: '#2DD4BF',
                    500: '#14B8A6',
                    600: '#0F766E',
                    700: '#115E59',
                    800: '#134E4A',
                    900: '#042F2E',
                },
                apple: {
                    primary: '#0066cc',
                    'primary-focus': '#0071e3',
                    'primary-dark': '#2997ff',
                    ink: '#1d1d1f',
                    body: '#1d1d1f',
                    'body-dark': '#ffffff',
                    'body-muted': '#cccccc',
                    'muted-80': '#333333',
                    'muted-48': '#7a7a7a',
                    'divider-soft': '#f0f0f0',
                    hairline: '#e0e0e0',
                    canvas: '#ffffff',
                    parchment: '#f5f5f7',
                    pearl: '#fafafc',
                    'tile-1': '#272729',
                    'tile-2': '#2a2a2c',
                    'tile-3': '#252527',
                    black: '#000000',
                    'chip-translucent': 'rgba(210, 210, 215, 0.64)'
                },
            },
            fontFamily: {
                display: ["Inter", "SF Pro Display", "-apple-system", "system-ui", "Segoe UI", "Roboto", "sans-serif"],
                text: ["Inter", "SF Pro Display", "-apple-system", "system-ui", "Segoe UI", "Roboto", "sans-serif"],
                mono: ["JetBrains Mono", "ui-monospace", "SF Mono", "Menlo", "monospace"],
            },
            screens: {
                sm: "640px",
                md: "768px",
                lg: "1024px",
                xl: "1280px",
                "2xl": "1536px",
                mobile: "480px",
                tablet: "768px",
                desktop: "1024px",
                wide: "1280px",
                "desktop-xl": "1440px",
            },
            borderRadius: {
                xs: "4px",
                sm: "6px",
                md: "8px",
                lg: "12px",
                xl: "16px",
                pill: "9999px",
            }
        }
    },
    plugins: [],
};

const outputPath = path.resolve(__dirname, 'public/css/mobileshop-panel.css');

postcss([
    tailwindcss(mobileshopConfig),
    autoprefixer(),
])
.process(inputCSS, { from: inputSourcePath })
.then(result => {
    fs.writeFileSync(outputPath, result.css);
    const sizeKb = (result.css.length / 1024).toFixed(1);
    console.log(`✅ Built: public/css/mobileshop-panel.css (${sizeKb} KB)`);
})
.catch(err => {
    console.error('❌ Build failed:', err.message);
    process.exit(1);
});
