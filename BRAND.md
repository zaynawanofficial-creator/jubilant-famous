# IamFamous.uk brand system v1 (approved 12 Nov 2026)

Visual reference: `brand/brand-board.png` (source: `brand/brand-board.html`).

## Idea
"Get seen. Get chosen. Get famous." The red heritage of iamfamous.com.au, made sharper and more premium. **Midnight Plum** carries authority, **Famous Red** drives action, and **Spotlight Gold** marks proof and highlights. It should feel confident and lively, never cheap or shouty: big type, dark hero bands, generous whitespace, red used only on purpose.

## Colour tokens
| Token (theme.json slug) | Hex | Use | Contrast (checked) |
|---|---|---|---|
| `red` Famous Red | `#E11D2E` | Primary buttons, links, key accents | white on it 4.75:1 (AA) |
| `red-deep` | `#B5122B` | Hover, small red text, eyebrow on light | 6.80:1 on white |
| `red-heritage` | `#F23131` | **Large shapes and illustrations only** (from .com.au) | 3.99:1, never for text or button fills |
| `red-glow` | `#FF4D5A` | Red text **on dark** backgrounds (headline accent words) | 5.76:1 on plum |
| `ink` Midnight Plum | `#160F1F` | Headings, dark bands, footer | 18.7:1 on white |
| `ink-2` Plum 2 | `#2A1F38` | Cards on dark bands | |
| `gold` Spotlight Gold | `#FFB020` | Stars, "most popular", ticks on dark, badges | ink on it 10.2:1; **never** as text on white |
| `rose-100` | `#FFE1E1` | Highlight blocks, illustration fills (from .com.au) | |
| `rose-50` | `#FFF1F1` | Selected states, soft panels | |
| `cream` | `#FFF8F4` | Alternate section background (instead of grey) | |
| `body` | `#3D3647` | Body text | 11.56:1 on white |
| `muted` | `#6B6475` | Secondary text | 5.67:1 on white |
| `line` | `#EDE6EA` | Borders, dividers | |
| `ok` | `#15803D` | Success states only | |

Gradients:
- `plum-glow`: `radial-gradient(900px 420px at 85% 10%, rgba(225,29,46,.38), transparent 60%), radial-gradient(600px 300px at 10% 110%, rgba(255,176,32,.16), transparent 60%), #160F1F` for heroes and CTA bands.
- `rose-soft`: `linear-gradient(180deg, #FFF1F1, #FFF8F4)` for light heroes.

Shadows:
- `card`: `0 1px 2px rgba(22,15,31,.06), 0 8px 24px rgba(22,15,31,.06)`
- `lift`: `0 24px 60px rgba(22,15,31,.14)`
- `red-cta`: `0 10px 24px rgba(225,29,46,.28)`

**Ratio rule:** about 60% white/cream, 25% plum, 10% red, 5% gold. Red is for actions and the logo, not for big backgrounds. The .com.au all-red hero is exactly what we're moving away from.

## Typography
- **Headings: Bricolage Grotesque** (variable, 200–800, SIL OFL). Weights 700–800, tracking −0.02em, `text-wrap: balance`.
  - Display `clamp(2.6rem, 6vw, 4.25rem)` / 1.02
  - H1 `clamp(2.2rem, 5vw, 3.25rem)`
  - H2 `clamp(1.75rem, 3.5vw, 2.5rem)`
  - H3 `1.375rem`
- **Body and UI: Figtree** (variable, 300–900, SIL OFL). Body 17px / 1.7. Labels and eyebrows 12px, 800 weight, uppercase, +0.12em. Use `font-variant-numeric: tabular-nums` for prices and tables.
- Files: `brand/fonts/*.woff2` (latin subsets; licences included). Preload both. `font-display: swap`.

## Logo
- `brand/logo.svg` (light backgrounds), `brand/logo-on-dark.svg`, `brand/wordmark.svg`, `brand/icon.svg`, `brand/favicon.svg`.
- Icon: a rounded red square with a white cheering figure on a stage arc (the crowd) under a gold star. It keeps the .com.au idea (a person lifted by a crowd) and stays legible at 16px.
- Wordmark: Bricolage Grotesque 800 **converted to outlines** (no font needed). "Iam" in Plum (or white on dark), "Famous" in Famous Red (Red Glow on dark).
- Clear space: the icon's corner radius on all sides. Minimum size: icon 20px; lockup 120px wide.
- Don't: recolour the icon background, add effects, stretch it, or put the red icon on a red background (use the white-reversed version).
- The new session should export a PNG logo and a 512px site icon from these SVGs (WordPress Site Icon needs PNG).

## Components (look and feel)
- **Buttons:**
  - primary: red with white text, 14px radius, 52px tall, red shadow
  - secondary: 1.5px plum outline
  - on dark: a transparent button with a white 40% outline
  - Gold buttons are for rare promo moments only.
- **Cards:** white, 1px `line` border, 20–22px radius, `card` shadow, `lift` on hover (translateY −3px).
- **Eyebrow:** red-deep caps label with a 18×2px bar before it.
- **Package picker:** selected tier gets a red border, a rose-50 fill and a 3px red focus ring. "Most popular" uses a gold pill with dark-brown text (`#7A4B00` on `#FFF4DB`).
- **Ticks:** gold on dark, red on light.
- **Dark bands:** `plum-glow` background, white headings, `#E9E3EE` body text, red-glow accent words.
- **Illustrations:** flat UI-style scenes with real labels, in the palette (plum frames, rose fills, red highlights, gold stars). Rendered as SVG/WebP. No people photos, emoji or 3D blobs.

## theme.json palette (paste into settings.color)
```json
"palette": [
  {"slug":"red","color":"#E11D2E","name":"Famous Red"},
  {"slug":"red-deep","color":"#B5122B","name":"Red Deep"},
  {"slug":"red-heritage","color":"#F23131","name":"Heritage Red"},
  {"slug":"red-glow","color":"#FF4D5A","name":"Red Glow"},
  {"slug":"ink","color":"#160F1F","name":"Midnight Plum"},
  {"slug":"ink-2","color":"#2A1F38","name":"Plum 2"},
  {"slug":"gold","color":"#FFB020","name":"Spotlight Gold"},
  {"slug":"rose-100","color":"#FFE1E1","name":"Rose 100"},
  {"slug":"rose-50","color":"#FFF1F1","name":"Rose 50"},
  {"slug":"cream","color":"#FFF8F4","name":"Cream"},
  {"slug":"body","color":"#3D3647","name":"Body"},
  {"slug":"muted","color":"#6B6475","name":"Muted"},
  {"slug":"line","color":"#EDE6EA","name":"Line"},
  {"slug":"white","color":"#FFFFFF","name":"White"}
]
```
