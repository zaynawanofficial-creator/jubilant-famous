# PROGRESS

## Round 1: theme 1.0.0 + plugin 1.0.0 · 12 Nov 2026 · 🟡 ready to install
**Deploy route:** zips (see INSTALL.md). The Hostinger MCP address the owner supplied (`websites-agents.hostinger.com/iamfamous.uk/mcp`) and the Hostinger connector are both refused by this cloud environment's network policy, and wordpress.org is blocked too. Nothing on the live site was touched.

### Done
- **Repo bootstrapped** from the handover pack: CLAUDE.md, brief, decisions, brand, pricing, lessons, `SERVICE_CATALOG.csv`, `tools_price_draft.py`, `brand/`, `reference/`.
- **Code forked** from SEO Booster theme 1.4.0 / plugin 0.6.0 into `iamfamous` / `iamfamous-core`. Every prefix, text domain, option, meta key, shortcode and file name renamed (`ifm_`, `IFM_`, `ifm-`, `_ifm_`). A scan for the old brand, old prefixes, WoodMart, the founder's name and dollar prices returns nothing in shipped code.
- **Brand applied:** theme.json palette, gradients (`plum-glow`, `rose-soft`), shadows (`card`, `lift`, `red-cta`) and the BRAND.md type scale; self-hosted Bricolage Grotesque + Figtree with preload; logo SVGs, exported `logo.png` and 512px `site-icon.png`; header, footer, heroes, cards, picker, cart, checkout and blog restyled (plum bands, red actions, gold proof; red ticks on light, gold on dark).
- **Currency and catalogue:** GBP throughout, importer reads the new CSV, SKUs `if-…`. All 112 priced packages import as **drafts** (`price_approved=0`).
- **Prices v2** (owner: "improve prices but round off after exchange rate"): rate rechecked to £1 = $1.333 (×0.75), rounding applied after conversion to clean price points, £5 minimum order. 61 prices changed from the first draft. Table in PRICING.md.
- **Original copy** for Home, Services hub, Local SEO, Website Development, Social Media Management, plus Free growth check and Contact (needed so buttons have somewhere to go). Package wording for those three services rewritten in `data/group-content-ifm.php`. No invented numbers, reviews or guarantees; banned-word scan is clean.
- **Illustrations:** 9 labelled scenes (home hero, services, 3 service heroes, 3 delivery reports, growth check) rendered in the brand fonts to WebP, 23 line icons, 113 package images.
- **Owner answers applied:** trading name IamFamous; no VAT (stated on site, Woo tax off); support@iamfamous.uk; no founder shown (agency voice, organisation as author); no review blocks.

### Better than SEO Booster (new in this build)
- Sticky **Choose package** bar on service pages (phones), hidden while the packages are on screen.
- **Comparison table** under every picker, stacking into cards on phones.
- **Free growth check** as the main lead form.
- **301 redirects** for the old site, firing only on a 404.
- Setup screen **parks the previous site's pages** as drafts, including address clashes.
- `[ifm_from]` takes `prefix` and `fallback`, so pages read properly before prices are published; the price cache is cleared by a version option instead of a direct database query.
- Polished **empty state** for a service with no published prices.
- Section reveal and hover lift, off for reduced motion and never hiding anything already on screen.
- A **real WordPress render** in the container (`tests/local-wp/`), which the SEO Booster build never had.

### Tests run
- `php -l` on every PHP file in the theme, plugin and tests: clean.
- `tests/importer-smoke.php`: ALL PASSED (25 checks: draft gate, GBP only, £5 floor, whole pounds, IamFamous copy, approval path, no duplicates).
- `tests/nav-smoke.php`: ALL PASSED (13 checks, including the mega panel when groups are added).
- `tests/redirects-smoke.php`: ALL PASSED (8 checks; all 48 old product slugs covered).
- `tests/validate-blocks.js` (`@wordpress/blocks` + `@wordpress/block-library` 12.1): 25 files, 1,247 blocks, **0 invalid**. 13 Custom HTML blocks are counted but not compared (jsdom mis-parses inline SVG); 8 WooCommerce blocks are not registered in the validator.
- **Real render** in a local WordPress 6.7-alpha (SQLite) with the real theme and plugin and a test-only WooCommerce stand-in: importer run (113 rows), Setup screen run (pages created, old `/services/` parked as `services-old`, old pages drafted), package page with the project-details form, live 301s (`/web-development/`, `/product/sm-basic-plan/`).
- Playwright full-page at **360, 390, 768, 1024 and 1440**: no horizontal scroll on any page checked (home, services, three service pages, growth check, contact, package page, insights, 404 at 390/768/1440; home, services, Local SEO, growth check, package page at 360/1024).
- UI checks (`tests/local-wp/ui-checks.js`), 14/14: drawer opens with Enter, focus trapped, Escape returns focus; picker arrow keys; sticky bar; comparison table; fonts loaded; header sticky without blur; red primary button; own JS 8.8 KB; cart and checkout templates dark hero with white heading.

### Not run (check on the live site)
- The real **WooCommerce** cart, checkout, order confirmation and My Account blocks. Their templates and CSS were ported and colour-mapped, and the templates render, but Woo itself could not be installed here.
- Add to cart → checkout → order → brief flow, order emails, payment gateways.
- Lighthouse (no Chrome Lighthouse run in this container).
- WordPress 7.1.4 / PHP 8.5 specifically: local render used WP 6.7-alpha on PHP 8.3.

### Known gaps for later rounds
- 17 service pages, Packages, How it works, About, FAQ, legal pages, original guides, bundles, add-ons.
- Package wording for the other 20 groups still uses the shared scope text. Those packages are drafts and have no public page yet; rewrite each group when its service page is written.
- Account, brief and order-email strings are functional UI text carried over; they sit on private or noindexed pages.
- Local SEO packages use the off-page brief form, which asks for an anchor-text preference. A local-specific brief form would be better.
- Heading and menu links to Terms, Refunds and Privacy are deliberately absent until those pages exist.

### Blockers / owner input
- Price approval (PRICING.md).
- Reviews: none reached the session.
- Confirm the "reply within one working day" promise.
- Company number and address, for the legal pages round.

### Next step
Install the two zips with INSTALL.md, check on a phone, then approve or adjust prices.
