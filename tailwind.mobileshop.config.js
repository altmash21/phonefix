module.exports = {
    content: [
        './resources/views/mobileshop/**/*.blade.php',
        './resources/views/auth/login/**/*.blade.php',
        './resources/views/components/**/*.blade.php',
    ],
    corePlugins: {
        preflight: false,
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
                    950: '#021E1D',
                }
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
                'apple-xs': '5px',
                'apple-sm': '8px',
                'apple-md': '11px',
                'apple-lg': '18px',
                'apple-pill': '9999px',
            }
        }
    }
};
