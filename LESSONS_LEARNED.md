# Lessons learned building SEOBooster.uk (apply all of these to IamFamous.uk from day one)

SEOBooster.uk took four rounds of owner feedback (theme 1.0 → 1.4, plugin 0.1 → 0.6) to reach its current standard. Every item below was a real bug or a real owner complaint, with the fix that worked. The reference code is the `jubilant-broccoli` repo: `wp-content/themes/seo-booster` and `wp-content/plugins/seo-booster-core`.

## A. Architecture that worked (keep it)
- **Route `wordpress`:** a custom **block theme** (theme.json + templates + parts + PHP-generated patterns), plus **one small business-logic plugin**, plus **WooCommerce**. No Elementor or WoodMart on the new site: they were slower and fought our CSS.
- **Theme = presentation. Plugin = everything that must survive a theme change:**
  - package model and meta, package groups taxonomy, approval gate
  - package picker shortcode, live "from" prices (`[sb_from]`), breadcrumbs
  - lead forms + Leads CPT, SEO head tags/schema, order flow (project details before checkout)
  - post-purchase brief, custom order statuses (`awaiting-brief`, `brief-received`, `client-review`, `delivered`)
- **Packages:** WooCommerce simple virtual products. Prices live only in WooCommerce and are never hard-coded in page copy. The CSV catalogue is imported by the plugin's importer (Tools → catalog), which has a dry run, matches by SKU, and attaches package images.
- **Pages:** generated as core-block patterns by Python (`tools/build_patterns.py` + `tools/blocks.py`). A one-click **Setup screen** (`inc/setup.php`, Appearance → Setup) creates or overwrites pages and posts from a manifest. Pages stay editable in the block editor.
- **Order flow:** service page → package picker → product (service details) page with a **project-details form** → cart item data → checkout → order line meta → post-purchase brief in My Account → admin fulfilment statuses.
- **Images:** per-package WebP images and article covers, rendered with Playwright from HTML templates (`tools/build_covers.js`) and converted with PIL. Informative SVG illustrations with **real labels** come from `tools/build_art_v2.py` (order steps, audit issues, change logs, wireframes). No stock photos, no AI-art people.

## B. WordPress / block-theme gotchas (each one cost a round of feedback)
1. **Shortcodes in templates and patterns:** WP runs `do_shortcode` on the template *before* `do_blocks`, so a shortcode inside a pattern or part was printed literally. Fix: a `render_block_core/shortcode` filter that runs `do_shortcode`.
2. **The Shortcode block runs `wpautop`:** it wraps output in `<p>`/`<br>` and breaks nav/div markup. Fix: return a placeholder comment (`sbt_raw()`) and swap in the real HTML after the block renders (`sbt_unraw()` in the same filter). Tested against WP's real `wpautop`.
3. **Mobile menu showed only "Home":** `backdrop-filter` (or `transform`/`filter`) on the sticky header makes it the containing block for `position:fixed` children. The core Navigation overlay was clipped to 64px. Fix: never put blur or transform on the header. Make the template-part wrapper `header.wp-block-template-part` sticky. Use a **custom nav** (`inc/nav.php` + `assets/js/nav.js`):
   - desktop mega panel ≥1024px
   - slide-in drawer below that, with focus trap, Esc, scroll lock and `aria-current`
   - The core Navigation block also switches to its toggle only at 600px, which is too late for a 7-item menu.
4. **Stale CSS on phones:** LiteSpeed CSS combine/UCSS and browser caches served an old stylesheet. Fixes:
   - Version `style.css` with `filemtime()`.
   - Inline a small critical CSS block (`#sb-critical` in `wp_head`) for the header, menu breakpoint and dark checkout hero.
   - Tell the owner to purge LiteSpeed (all + CSS/JS + UCSS) after every update.
5. **Site Editor copies override theme files:** if the owner ever saves the header in the Site Editor, theme updates stop showing. Fix: a Setup-screen option that moves `wp_template`/`wp_template_part` copies for the theme to the Trash.
6. **Block validation:** core block markup must match core `save()` exactly, or the editor shows "invalid block". Validate every template, part and pattern with `@wordpress/blocks` + `@wordpress/block-library` (`parse()` + `isValid`) before each release. Target: 0 invalid.
7. **Block attribute JSON containing `--`** (e.g. CSS vars) must be escaped as `--`, as WP does, or the block comment breaks.
8. **Global "no white strips" rule:** `.wp-site-blocks > * + *` and `post-content > * {margin-block-start:0}` removed WP's gaps between full-width sections. It also killed paragraph spacing inside **articles**. Scope prose rhythm separately (`.sb-prose > * + * {margin-block-start:1.25rem}`, with higher specificity).
9. **WP layout specificity:** `.wp-block-buttons.is-layout-flex{display:flex}` out-ranks simple hide rules. Use specific selectors or `!important` for responsive hides.
10. **Constrained layout + inline-flex:** an `inline-flex` eyebrow ignores auto margins, so it misaligns. Use `display:flex; width:fit-content`.
11. **CSS counters inside WP grid layouts broke** (every step showed "01"), and WP `auto-fill` grids left empty tracks. Use **explicit numbers** and **fixed column grids** (4/3/2 → 1).
12. **Plugin vs theme class collisions** (`sb-grid`, `sb-card`, `sb-panel`) turned theme grids into swipe rows. Prefix plugin UI classes separately (e.g. `sb-pk*`).
13. **Plugin CSS loads only where its shortcodes run.** Styles for anything rendered on posts (byline, author box) must live in the **theme**.
14. **Post internal links:** posts live at the root (`/slug/`), not `/insights/slug/`.
15. **Mobile tables:** a `render_block_core/table` filter adds `data-label` attributes (from `<thead>`) to tables with class `sb-table`, so they stack on phones without changing saved markup.

## C. WooCommerce gotchas
1. **Price markup:** `get_price_html()` contains nested `<span>`s. A theme rule like `.price span{display:block}` split "$ / 449.00" onto separate lines. Wrap your own parts in your own classes (`.sb-pk__amount`, `.sb-pk__unit`), never style bare `span`. Show whole prices without ".00" (`wc_price($v, ['decimals'=>0])`), but keep Woo's markup for sale prices.
2. **Cart/checkout heroes:** a later `.sb-shop-hero{background:light}` overrode the dark `.sb-hero`, giving white text on a light background. Set an explicit dark background on the combined selector. Pin Woo text colours.
3. **Woo cart on phones** is a grid (`.is-mobile` / `.is-small`). Don't set fixed widths on the image cell. Use `grid-template-columns: 84px 1fr auto`.
4. **Checkout summary:** box only `.wp-block-woocommerce-checkout-order-summary-block`, not the sidebar wrapper (that gave a box inside a box). Also style the duplicate in-form summary (`.checkout-order-summary-block-fill-wrapper`).
5. **Templates to override in the theme:** `page-cart`, `page-checkout`, `order-confirmation`, `single-product` (as a service details page with a hero, body, sticky order card and project-details form). Checkout uses the **full** site header and footer: the owner disliked the slim checkout header.
6. **HPOS-safe meta** via `$order->update_meta_data()`. Declare `custom_order_tables` and `cart_checkout_blocks` compatibility.
7. **Cart item data:** use `woocommerce_add_cart_item_data`, then `woocommerce_get_item_data`, then `woocommerce_checkout_create_order_line_item`.
8. **noindex** cart, checkout, account, product, shop and search pages. Products are reached from service pages, and the service pages are the ones that rank.
9. **Payments stay in test/sandbox mode** until the owner approves going live. BACS is opt-in from the Setup screen.

## D. Design feedback the owner gave (don't make him say it twice)
- "Default WooCommerce look" is not acceptable. Cart and checkout must look like part of the brand: dark hero, progress steps, "what happens next", trust row, payment marks.
- **Heroes:** split heroes with an informative illustration on every inner page. Breadcrumbs and eyebrow aligned. No empty right column.
- **No empty or placeholder illustrations.** Art must carry real labels that explain the service.
- **Compact pricing:** a tier selector (radio list) with a detail panel, not long card grids. At least **4 packages per service**.
- **Footer:** brand column + 4 equal link columns + a payments bar + legal links. Equal column widths.
- **Mobile header:** logo, cart and menu button only. All links in the drawer. Home is the first item.
- **Blog:**
  - real hero, featured post, topic chips, card grid, founder CTA
  - single posts: category chip, standfirst, byline, cover overlapping the hero, prose rhythm, sticky "On this page", author box, related posts
  - posts must be long and useful, with internal links
- **Don't print "Prices in USD"** or similar filler.
- **Legal pages:** hero, at-a-glance box, sticky contents, numbered sections, contact card. Drafts marked for legal review.
- **Whitespace:** no white strips between full-width sections; consistent section padding (`clamp(3rem,6vw,4.75rem)`).

## E. Content rules (the owner's "no AI fluff")
- Never invent facts: years in business, client counts, reviews, awards, case-study metrics, ranking guarantees, partner logos, addresses, company numbers. The current IamFamous.uk Elementor template has "Years of Experience / Expert Team / Satisfied Client" counters. **Remove them** unless the owner supplies real numbers.
- Numbers on the site must be **verifiable facts about the offer**: turnaround days, revisions, word counts, link counts, DR thresholds, prices from WooCommerce, response times you actually commit to.
- Banned filler: "unlock", "elevate", "supercharge", "in today's digital landscape", "cutting-edge", "seamless", "game-changer", "we pride ourselves", "one-stop shop", "tailored solutions", "take your business to the next level", "results-driven" (unless followed by the result).
- Use UK spelling. Write short paragraphs, specific nouns and second person. Every section answers a buyer question: what do I get, how long, how much, what do you need from me, what happens if it goes wrong.
- SEO: one page per real keyword cluster (no thin near-duplicates), a "What is X" section, FAQs from real questions, internal links between related services and guides, and Organization/Article/Person/Breadcrumb schema. Never promise rankings or rich results.
- **Don't copy SEO Booster's wording.** IamFamous.uk needs original copy for every page (see PROJECT_BRIEF.md: duplicate-network risk).

## F. Delivery and verification workflow that worked
- **This cloud container cannot authenticate to WP REST/MCP:** the egress proxy alters the `Authorization` header and gives `401 incorrect_password` even with valid Application Passwords. Don't burn time on it. Deliver **zips** (`tools/build-theme-zip.sh`, `tools/build-plugin-zip.sh`). The owner installs them (Plugins/Themes → Upload → Replace current) and runs the Setup screen.
- **Hostinger Connector** needs the owner to authorise it in claude.ai connector settings. If it's available, prefer it for deploys and cache purges.
- **Verify before claiming:**
  - PHP lint on every changed file
  - block validator
  - stubbed PHP smoke tests (`tests/importer-smoke.php`, `tests/nav-smoke.php`)
  - Playwright screenshots at 360/390/768/1024/1440, checking `scrollWidth` for horizontal overflow
  - Test new CSS against the **live** site by intercepting `style.css` with Playwright `route()`, injecting new markup with `page.evaluate`, and screenshotting. This catches real Woo/WP markup issues a static preview misses.
- **End every round with:** a version bump (theme + plugin), zips, INSTALL.md steps at the top (backup → plugin → import if needed → theme → Setup screen → purge LiteSpeed → what to check on a phone), PROGRESS.md (fixed / tests run / not run / next step), then commit and push.
- **Never print secrets.** `.mcp.json` reads `${WP_API_PASSWORD}`.
