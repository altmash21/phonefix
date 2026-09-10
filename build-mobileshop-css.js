/**
 * Build mobileshop Tailwind CSS — run with: node build-mobileshop-css.js
 * Generates public/css/mobileshop-panel.css (replaces cdn.tailwindcss.com)
 */
const postcss = require('./node_modules/postcss');
const tailwindcss = require('./node_modules/tailwindcss');
const autoprefixer = require('./node_modules/autoprefixer');
const fs = require('fs');
const path = require('path');

const inputCSS = `@tailwind base;
@tailwind components;
@tailwind utilities;`;

// Mobileshop-specific Tailwind config (mirrors the inline config in layout.blade.php)
const mobileshopConfig = {
    content: [
        './resources/views/mobileshop/**/*.blade.php',
    ],
    corePlugins: {
        preflight: false, // Don't reset base styles — admin-panel.css handles it
    },
    theme: {
        extend: {
            colors: {
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
            },
            fontFamily: {
                display: ["Inter", "SF Pro Display", "-apple-system", "system-ui", "Segoe UI", "Roboto", "sans-serif"],
                text: ["Inter", "SF Pro Display", "-apple-system", "system-ui", "Segoe UI", "Roboto", "sans-serif"],
                mono: ["JetBrains Mono", "ui-monospace", "SF Mono", "Menlo", "monospace"],
            },
            screens: {
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
.process(inputCSS, { from: undefined })
.then(result => {
    fs.writeFileSync(outputPath, result.css);
    const sizeKb = (result.css.length / 1024).toFixed(1);
    console.log(`✅ Built: public/css/mobileshop-panel.css (${sizeKb} KB)`);
    console.log('✅ Now remove <script src="https://cdn.tailwindcss.com"> from layout.blade.php');
})
.catch(err => {
    console.error('❌ Build failed:', err.message);
    process.exit(1);
});
