---
version: alpha
name: Linear-admin-white-theme
description: "A precision software-craft admin panel canvas built on Linear's design DNA, implemented as a pristine white / light theme. Anchored around a crisp white canvas (#ffffff / #fbfbfc), deep near-black ink (#0f1011) for maximum legibility, a 4-step light surface ladder, hairline borders (#e6e8ec), and the iconic Linear lavender-blue (#5e6ad2) as the single chromatic accent. The system reads as high-density software craft: technical, calm, and quietly luxurious."

colors:
  primary: "#5e6ad2"
  on-primary: "#ffffff"
  primary-hover: "#717dd9"
  primary-active: "#4e5ac0"
  primary-focus: "#5e69d1"
  primary-light: "#f4f5fd"
  primary-subtle: "rgba(94, 106, 210, 0.08)"
  primary-glow: "rgba(94, 106, 210, 0.22)"

  ink: "#0f1011"
  ink-muted: "#555962"
  ink-subtle: "#8a8f98"
  ink-tertiary: "#b0b4bc"

  canvas: "#fbfbfc"
  surface-1: "#ffffff"
  surface-2: "#f7f8f9"
  surface-3: "#eceef2"
  surface-4: "#e2e4e9"

  hairline: "#e6e8ec"
  hairline-strong: "#d1d4dc"
  hairline-tertiary: "#b8bcc6"

  semantic-success: "#27a644"
  semantic-success-light: "#edf7ee"
  semantic-warning: "#d97706"
  semantic-warning-light: "#fef8eb"
  semantic-danger: "#eb5757"
  semantic-danger-light: "#fdf2f2"
  brand-secure: "#7a7fad"

typography:
  display-xl:
    fontFamily: "Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif"
    fontSize: 56px
    fontWeight: 600
    lineHeight: 1.08
    letterSpacing: -1.8px
  display-lg:
    fontFamily: "Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif"
    fontSize: 40px
    fontWeight: 600
    lineHeight: 1.15
    letterSpacing: -1.0px
  headline:
    fontFamily: "Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif"
    fontSize: 24px
    fontWeight: 600
    lineHeight: 1.20
    letterSpacing: -0.5px
  card-title:
    fontFamily: "Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif"
    fontSize: 16px
    fontWeight: 600
    lineHeight: 1.30
    letterSpacing: -0.2px
  body:
    fontFamily: "Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif"
    fontSize: 13px
    fontWeight: 400
    lineHeight: 1.50
    letterSpacing: -0.05px
  body-sm:
    fontFamily: "Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif"
    fontSize: 12px
    fontWeight: 400
    lineHeight: 1.45
    letterSpacing: 0
  caption:
    fontFamily: "Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif"
    fontSize: 11px
    fontWeight: 500
    lineHeight: 1.40
    letterSpacing: 0
  mono:
    fontFamily: "'JetBrains Mono', 'SF Mono', Menlo, Consolas, monospace"
    fontSize: 12px
    fontWeight: 400
    lineHeight: 1.40
    letterSpacing: 0

rounded:
  xs: 4px
  sm: 6px
  md: 8px
  lg: 12px
  xl: 16px
  pill: 9999px

spacing:
  xxs: 4px
  xs: 8px
  sm: 12px
  md: 16px
  lg: 24px
  xl: 32px
---

# Linear White-Theme Design Specification for MobiTrack Admin Panel

## 1. Design Overview
The MobiTrack ERP Admin Panel uses the **Linear software-craft design philosophy**, translated into a crisp **white / light mode**.
- **Canvas**: Pristine near-white (`#FBFBFC`) providing calm breathing room between operational modules.
- **Surface Ladder**: Surface-1 (`#FFFFFF`) for operational cards and tables; Surface-2 (`#F7F8F9`) for table headers, active strips, and hover states.
- **Single Chromatic Accent**: Linear Lavender-Blue (`#5E6AD2`) used on primary CTAs, active nav states, focus rings, and verified links — never excessively.
- **Hairline Geometry**: 1px crisp hairline borders (`#E6E8EC`) establish hierarchy without relying on heavy muddy drop shadows.
- **Measured Negative Tracking**: Display and headlines run `-0.02em` to `-0.03em` tracking; tabular numerals use JetBrains Mono / SF Mono.

---

## 2. Color Palette & Tokens

### Primary & Accent
- **Lavender-Blue (`--color-primary`)**: `#5E6AD2` — Primary CTA, active sidebar items, focus rings.
- **Lavender Hover (`--color-primary-hover`)**: `#717DD9` — Hovered primary button.
- **Lavender Active (`--color-primary-active`)**: `#4E5AC0` — Pressed state.
- **Lavender Light (`--color-primary-light`)**: `#F4F5FD` — Active table row, hover nav tint.
- **Lavender Subtle (`--color-primary-subtle`)**: `rgba(94, 106, 210, 0.08)` — Pill badges, soft background.

### Light Surfaces & Dividers
- **Canvas (`--color-bg`)**: `#FBFBFC` — Default page background.
- **Surface 1 (`--color-surface`)**: `#FFFFFF` — Cards, tables, modals, flyouts.
- **Surface 2 (`--color-surface-hover`)**: `#F7F8F9` — Table headers, sub-bars, hover background.
- **Surface 3 (`--color-surface-subtle`)**: `#ECEEF2` — Inactive pills, subtle dividers.
- **Hairline Border (`--color-border`)**: `#E6E8EC` — 1px cards, headers, divider rules.
- **Strong Hairline (`--color-hairline-strong`)**: `#D1D4DC` — Input focus borders, active card borders.

### Ink (Typography)
- **Ink Primary (`--color-text-primary`)**: `#0F1011` — Headings, table cells, metric values.
- **Ink Muted (`--color-text-secondary`)**: `#555962` — Labels, subheaders, secondary buttons.
- **Ink Subtle (`--color-text-muted`)**: `#8A8F98` — Table `<th>`, helper text, placeholders.
- **Ink Tertiary (`--color-text-tertiary`)**: `#B0B4BC` — Disabled elements, subtle metadata.

### Semantics
- **Success (`--color-success`)**: `#27A644` (Background: `#EDF7EE`, Border: `#C8E6C9`).
- **Warning (`--color-warning`)**: `#D97706` (Background: `#FEF8EB`, Border: `#FDE68A`).
- **Danger (`--color-danger`)**: `#EB5757` (Background: `#FDF2F2`, Border: `#FECDCA`).
- **Security / Verified (`--brand-secure`)**: `#7A7FAD`.

---

## 3. Component Standards

### Buttons
- **`btn-primary`**: 
  - Height: ~32–36px.
  - Background: `#5E6AD2`.
  - Text: `#FFFFFF`, font-weight 500.
  - Border: 1px solid transparent.
  - Radius: `8px` (`rounded.md`).
  - Hover: Background `#717DD9`, transform `translateY(-1px)`, box-shadow `0 2px 6px rgba(94, 106, 210, 0.25)`.
- **`btn-outline` / `btn-secondary`**:
  - Background: `#FFFFFF`.
  - Border: `1px solid #E6E8EC`.
  - Text: `#0F1011`, font-weight 500.
  - Radius: `8px`.
  - Hover: Background `#F7F8F9`, border `#D1D4DC`.

### Data Tables
- Header row: Background `#F7F8F9`, border-bottom `1px solid #E6E8EC`.
- Header text: `#555962`, 11px font-size, 600 weight, uppercase, letter-spacing `0.04em`.
- Row border: `1px solid #F0F2F5`.
- Row hover: Background `#F8F9FB`.
- Monospace figures: Amounts, IMEIs, invoice codes, phone numbers rendered in `JetBrains Mono, SF Mono, monospace; font-variant-numeric: tabular-nums`.

### KPI & Metric Cards
- Background: `#FFFFFF`.
- Border: `1px solid #E6E8EC` with left indicator stripe (`3.5px solid`).
- Radius: `12px` (`rounded.lg`).
- Label: Uppercase, 11px, weight 600, color `#555962`, letter-spacing `0.03em`.
- Value: 20px, weight 700, color `#0F1011`, tabular numerals.
- Hover: Subtle lift `translateY(-1px)` and box-shadow `0 4px 12px -2px rgba(0, 0, 0, 0.05), 0 0 0 1px #D1D4DC`.

### Status Badges
- Pill shape (`9999px`) or `6px` radius.
- Padding: `2px 8px`.
- Font size: `11px`, weight `600`.
- Tone-on-tone light fills:
  - Green (Paid / Available): `#EDF7EE` / `#27A644`
  - Lavender (New / Active / Primary): `#F4F5FD` / `#5E6AD2`
  - Amber (Due / In Progress): `#FEF8EB` / `#D97706`
  - Red (Overdue / Voided): `#FDF2F2` / `#EB5757`
  - Neutral / Gray: `#F4F5F7` / `#555962`

### Form Controls
- Height: 34–36px.
- Background: `#FFFFFF`.
- Border: `1px solid #E6E8EC`, radius `8px`.
- Focus ring: `0 0 0 3px rgba(94, 106, 210, 0.18)` and border `#5E6AD2`.
- Typography: 13px, color `#0F1011`.
