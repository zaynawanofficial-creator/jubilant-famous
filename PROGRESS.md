# PROGRESS

## Round 4 (final build): theme 1.3.0 + plugin 1.3.0 · 16 Nov 2026 · 🟢 ready for owner review
All planned work is built. What remains is the owner's review, the install and the live checks that cannot be run here.

### Done
- **Legal identity:** "Mehreen, a sole trader trading as IamFamous" in the footer, About, Contact, the five policy pages and `llms.txt`.
- **Cookie banner:** small card bottom-left, full-width bar on phones; "Essential only" / "Allow all"; choice kept 180 days in `ifm_consent`; reopened from "Cookie settings" in the footer; `window.ifmConsent()` and an `ifm:consent` event for any optional script added later. Cookie and privacy policies updated to match.
- **Prices:** minimum raised from £5 to £10 with the cheap tiers stepped up (9 prices changed; Local Starter Bundle now £119).
- **Add-ons** on one-off packages: extra round of changes (+15%, minimum £10) and express delivery (+25%, minimum £15). Carried in the basket, added to the price, shown at checkout and saved on the order line. Not offered on monthly, booked-call or quote packages. Covered in the terms and FAQ.
- **Local SEO order form** of its own (business details, hours, services, areas, Google profile access) in place of the link-building form.
- **Automatic clean-up** once a day: enquiries older than 12 months are deleted, and uploaded project files are deleted 12 months after an order is finished, as the privacy policy says.
- **Four more guides** (seven in total): asking for reviews within UK rules, Google Ads or Facebook ads, how SEO is priced, what a citation is.
- Fixed in the final check: the button on the quote-only package page overflowed on phones; guide category pages shared one description.

### Final check (whole site)
- `php -l` on every PHP file: clean.
- Smoke tests: importer 30/30, nav 15/15, redirects 10/10, content 12/12, add-ons 8/8.
- Block validation: 59 files, 5,723 blocks, **0 invalid** (13 Custom HTML blocks counted but not compared).
- **Site audit** (`tests/local-wp/site-audit.js`) on the local WordPress, crawling every internal link from the home page: **156 URLs, 0 issues**. It checks HTTP status, one H1 per page, a unique title and description on every indexable page, no unrendered shortcodes, no broken images, no JavaScript errors, and no horizontal scroll at 390 and 1440. `/my-account/` is excluded because WooCommerce is not in the local copy.
- Cookie banner: shows on first visit, fits a 390px screen, hides after a choice, stays hidden on the next page, reopens from the footer with focus on the first button, stores `essential` / `all`.
- Add-ons appear on a one-off package and not on a monthly one; the basket-price calculation is covered by the add-ons smoke test.
- `ui-checks.js` 14/14 and `menu-checks.js` pass.

### Not run (needs the live site)
- The real WooCommerce basket, checkout, order confirmation, My Account, payments and emails, **including the add-on price at checkout**. Place a test order with an extra ticked before going live.
- The real Rank Math plugin (tested against a stand-in).
- The daily clean-up job running on the server's schedule.
- Lighthouse; WordPress 7.1.4 / PHP 8.5 specifically.

### Owner review list
1. Read the five policy pages and have them checked.
2. Confirm you can staff express delivery, or ask for it to be removed.
3. Install with INSTALL.md, do the Rank Math checks, place a test order in test mode.
4. Confirm the support@iamfamous.uk mailbox works; check the ICO fee.

## Round 3: theme 1.2.0 + plugin 1.2.0 · 15–16 Nov 2026 · 🟡 ready to install
Rounds 1 and 2 are pushed (`24ea183`, `e62825e`). Delivery is still by zip: the Hostinger MCP host is refused by this environment's network policy.

### Done
- **Prices approved and live in the catalogue.** Monthly plans held at −10% (six prices rose £10 to £20); 106 priced packages publish on import. Three **starter bundles** added at about 10% off their parts.
- **Customer feedback:** the owner's 28 quotes are in `data/reviews.php`, shown word for word and without names on Home, About, every service page (matched by service) and a Reviews page. No star ratings and no review schema.
- **Package wording:** deliverables, exclusions, requirements and "ideal for" lines rewritten in IamFamous wording for all 17 newer services and the bundles (`tools/package_copy.py`). Maintenance plans and the press release now have their own correct lists. No package still uses the shared SEO Booster wording.
- **New pages:** Packages (bundles + price table for all 20 services), How it works, About, FAQ (19 questions in 5 groups), Reviews, Terms, Refund policy, Privacy policy, Cookie policy, Accessibility statement.
- **Guides:** Google Business Profile checklist, small business website checklist, 20 social media post ideas, each with a cover, key points, FAQs and internal links.
- **SEO and GEO:**
  - Service schema (with the lowest price) and FAQ schema built from the questions shown on each page.
  - Organisation schema with the address.
  - `/llms.txt` summarising the business and its pages for AI tools.
  - **Rank Math support:** our Service and FAQ nodes join Rank Math's graph; basket, checkout, account and package pages are noindexed through Rank Math; the Setup screen sets title, description and focus keyword on every page and fills empty business settings.
  - Every page has a unique title (max 70), description (70 to 158) and focus keyword, one H1, and only internal links that resolve.
- **Business details:** IamFamous, 27 Old Gloucester Street, London, WC1N 3AX in the footer, contact, about and legal pages, described as a postal address.
- **Menu:** Home, Services (20 in four groups), Packages, How it works, Reviews, About, Contact. Footer has legal links.
- Setup screen sets UK date format and London time; redirects for `/about-us/`, `/faqs/` and `/shop/` now go to the new pages.

### Tests run
- `php -l` on every PHP file: clean.
- `tests/importer-smoke.php` 30/30, `tests/nav-smoke.php` 15/15, `tests/redirects-smoke.php` 10/10, new `tests/content-smoke.php` 11/11 (titles, descriptions, keywords, one H1 per page, no banned words, no hard-coded prices, no dead internal links, quotes match the owner's file word for word, no review schema).
- `tests/validate-blocks.js`: 55 files, 5,498 blocks, **0 invalid** (13 Custom HTML blocks counted but not compared).
- Real render in the local WordPress with the approved catalogue: 116 rows imported, all new pages and guides created with covers, no errors or warnings.
- Checked in that render: Service + FAQPage + BreadcrumbList on a service page (lowest price £42 on Local SEO), Organisation with address on Home, `/llms.txt` output, 6 quotes on Home and 3 service-matched quotes on a service page.
- **Rank Math path, with a stand-in for the plugin:** Service and FAQ nodes added to its graph, our own head tags switched off, package page set to noindex, per-page fields and business settings written by the Setup screen. Not tested against the real Rank Math plugin.
- Playwright full-page, **44 renders, no horizontal scroll**: 16 pages at 390 and 1440; Packages, Terms, Reviews and a guide at 360, 768 and 1024.
- `ui-checks.js` 14/14 and `menu-checks.js` pass.

### Not run
- The real WooCommerce basket, checkout, order confirmation and My Account, payments and emails.
- The real Rank Math plugin (option names and filters follow its documented behaviour; check the five points in INSTALL.md).
- Lighthouse; WordPress 7.1.4 / PHP 8.5 specifically.

### Not done, and why
- **Add-ons / order bumps** (express delivery, extra revisions): need a delivery promise from the owner and cart fee logic.
- **4 more guides** of the planned 7+.
- **Legal name of the trader** on the site: not supplied (see DECISIONS.md, Open).
- A local-SEO-specific brief form (it still uses the link-building form).
- Automatic deletion of old enquiries and project files after 12 months, which the privacy policy promises: currently a manual job.

### Next step
Install with INSTALL.md, do the Rank Math checks, place a test order in test mode. Then: legal name, legal review of the policy pages, remaining guides, add-ons.

## Round 2: theme 1.1.0 + plugin 1.1.0 · 13 Nov 2026 · 🟡 ready to install
**Round 1 push:** done on 13 Nov (commit `24ea183`), after the owner asked again; the earlier attempts were refused by the session's permission layer.

### Done
- **17 new service pages**, original UK copy, same structure as the first three (hero, fit / not fit, picker with comparison table, "what is", delivery proof, what the work covers, process, what we need, five FAQs, related services, growth-check CTA): Monthly SEO, Link Building, SEO Audit, Technical SEO, On-Page SEO, Ecommerce SEO, AI SEO, White Label SEO, SEO Consultancy, Ecommerce Websites, Website Maintenance, Website Migration, Google Ads, Facebook & Instagram Ads, TikTok Ads, Content Writing, Graphic Design & Branding. Data in `tools/services_seo.py` and `tools/services_more.py`.
- **34 new illustrations** (a labelled hero scene and a delivery-report scene for each new service), generated from `tools/scenes_round2.py`.
- **Services hub** rebuilt: the three lead offers with illustrated cards, then all 20 in four groups. Home gets an eight-card "more services" grid. Related-service cards on every service page.
- **Menu:** Services is now the mega panel (desktop) and accordion (drawer) with all 20 services in four groups. Footer has four equal link columns.
- **Redirects** now point each old page, category and product at its own new service page.
- **Package intros** rewritten in IamFamous wording for the other 20 package groups (shown above each picker).
- Copy checked line by line against the package deliverables; three claims that went beyond the packages were removed.

### Tests run
- `php -l` on every PHP file in the theme, plugin and tests: clean.
- `tests/importer-smoke.php`: ALL PASSED (25 checks). `tests/nav-smoke.php`: ALL PASSED (14 checks: all 20 services in the mega panel and drawer, each with a page pattern). `tests/redirects-smoke.php`: ALL PASSED (10 checks: every target is a page the theme builds).
- `tests/validate-blocks.js`: 42 files, 4,377 blocks, **0 invalid** (13 Custom HTML blocks counted but not compared; WooCommerce blocks not registered in the validator).
- Real render in the local WordPress: Setup screen run with Overwrite created the 17 new pages and updated the existing ones, with no errors or warnings.
- Playwright full-page, **50 renders, no horizontal scroll**: home, hub and all 17 new service pages at 390 and 1440; home, hub, Link Building and Google Ads at 360, 768 and 1024.
- `tests/local-wp/ui-checks.js`: 14/14. `tests/local-wp/menu-checks.js`: mega panel opens with Enter, lists 20 links, marks the current page, closes with Escape and returns focus; fits the viewport at 1024; the drawer accordion opens on a service page with 20 links.

### Not run
Same as round 1: the real WooCommerce basket, checkout, order confirmation and My Account, payments, emails, Lighthouse, and WordPress 7.1.4 / PHP 8.5 specifically.

### Known gaps
- Deliverable bullet lists for the 17 new services still use the shared scope wording (all are drafts until prices are approved). Reword them before or with price approval.
- Packages page, How it works, About, FAQ, legal pages, original guides, bundles and add-ons.
- A local-SEO-specific brief form.

### Next step
Install with INSTALL.md (first install, or the 1.1.0 update), then approve prices. Next round candidates: reword the remaining deliverables, Packages / How it works / FAQ pages, legal pages (needs company details), first guides.

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
