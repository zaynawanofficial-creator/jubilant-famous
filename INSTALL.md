# INSTALL — IamFamous.uk

## How updates are deployed now (from 17 Nov 2026)
**Claude deploys directly** through the Hostinger connector. Nothing to upload by hand.
1. Claude uploads only the changed files into `wp-content/themes/iamfamous/` and `wp-content/plugins/iamfamous-core/` (`tools/deploy-hostinger.sh`).
2. The theme version number changes, so on the next visit the site rebuilds itself once in the background: re-imports the catalogue, rebuilds the pages and guides made by the Setup screen, sets Rank Math fields and purges LiteSpeed. Pages you created yourself are never touched.
3. Claude clears the Hostinger server and CDN cache and checks the live pages.

**If you ever deploy by hand instead** (for example from a new session without the connector):
1. Back up in Hostinger.
2. Plugins → Add Plugin → Upload → `iamfamous-core.zip` → **Replace current with uploaded**.
3. Appearance → Themes → Add Theme → Upload → `iamfamous-theme.zip` → **Replace current with uploaded**.
4. Open the home page once. The automatic rebuild runs within a minute. (Or run Appearance → IamFamous Setup with **Overwrite** ticked.)
5. LiteSpeed Cache → Toolbox → **Purge All**.

**If you edit a page made by the Setup screen** in the block editor and want to keep your edit, tell Claude first: the next version's automatic rebuild overwrites those pages.

## Which section do I follow?
- **Nothing installed yet:** follow "First install" below with the newest zips (theme 1.3.0, plugin 1.3.0). You get the whole site in one go. Skip the update section.
- **An earlier version is installed:** follow "Updating to theme 1.3.0 + plugin 1.3.0".

## Updating to theme 1.3.0 + plugin 1.3.0 (16 Nov 2026) — only if an earlier version is installed
Brings everything up to the final build: approved prices (minimum £10), bundles, optional extras, customer feedback, all company and policy pages, seven guides, the cookie banner, the sole-trader details and Rank Math support.
1. **Backup** in Hostinger.
2. **Plugin:** Plugins → Add Plugin → Upload Plugin → `iamfamous-core.zip` → **Replace current with uploaded**.
3. **Import:** Tools → **IamFamous catalog** → untick Dry run → tick **Refresh package images** → **Run import**. This publishes the packages with the approved prices and switches Local SEO to its own order form.
4. **Theme:** Appearance → Themes → Add Theme → Upload Theme → `iamfamous-theme.zip` → **Replace current with uploaded**.
5. Appearance → **IamFamous Setup** → tick **Overwrite** → **Build the site**.
6. Do the **Rank Math checks** below.
7. LiteSpeed Cache → Toolbox → **Purge All** (plus CSS/JS and UCSS if on).
8. **Check on your phone, in a private tab:**
   - [ ] A small cookie bar appears at the bottom. Tap **Essential only**: it disappears and stays gone on the next page. **Cookie settings** in the footer brings it back.
   - [ ] The footer shows "IamFamous is the trading name of Mehreen, a sole trader" and the address.
   - [ ] `/services/off-page-seo/`: the smallest package is £10.
   - [ ] Open any one-off package (for example a 5-Page Website): **Optional extras** shows an extra round of changes and express delivery, each with a price. Tick one and continue: the basket total includes it.
   - [ ] `/plans/` shows three bundles and a price table for all 20 services.
   - [ ] `/insights/` shows seven guides.
   - [ ] `/llms.txt` shows a plain-text summary of the business.

## Rank Math checks (after the Setup screen has run)
The Setup screen fills these in when they are empty. Check them once.
1. Rank Math → Titles & Meta → **Local SEO**: Organisation, name **IamFamous**, the logo, email **support@iamfamous.uk**, address **27 Old Gloucester Street, London, WC1N 3AX**. Save.
2. Rank Math → General Settings → **Breadcrumbs** → switch **Enable breadcrumbs function** on. This adds breadcrumb data to Rank Math's schema. Our own breadcrumb trail on the page is unchanged.
3. Rank Math → Titles & Meta → **Products**: leave as it is. Package pages, basket, checkout and account are set to noindex by the IamFamous plugin, because the service pages are the ones that should rank.
4. Rank Math → **Sitemap Settings**: Pages and Posts on, Products off. Then submit `https://iamfamous.uk/sitemap_index.xml` in Google Search Console.
5. Open any service page → Rank Math box in the editor: the title, description and focus keyword are already filled. Change them there if you want different wording. The Setup screen will not overwrite a field you have edited.

What each plugin does once Rank Math is active: **Rank Math** writes page titles, descriptions, robots tags, the sitemap and the Organisation schema. **IamFamous Core** adds Service schema (with the lowest price) and FAQ schema to Rank Math's output, built from the questions shown on each page.

## First install (theme + plugin, any version)


Two files to upload: **`iamfamous-core.zip`** (plugin) and **`iamfamous-theme.zip`** (theme). Allow 20 minutes. Do the steps in this order.

You have told us **WooCommerce and Rank Math are already installed**, so step 2 is only a check.

### 1. Backup
Hostinger hPanel → Websites → iamfamous.uk → Backups → **Create backup**. Wait for it to finish. Nothing here deletes content, but a backup makes rollback one click.

### 2. Check WooCommerce and Rank Math
Plugins → both **WooCommerce** and **Rank Math SEO** should show as active. If Rank Math's setup wizard has not been run, run it now (choose "Small business site", keep the defaults). Do not connect a payment provider yet.

### 3. Install the plugin
Plugins → Add Plugin → **Upload Plugin** → `iamfamous-core.zip` → Install → **Activate**.

### 4. Import the catalogue (this publishes the prices)
Tools → **IamFamous catalog**.
1. Leave **Dry run** ticked → **Run import**. You should see 116 lines: 106 priced packages and 1 quote-only package ending in `publish`, and 9 ending in `draft` (Analytics, Email Marketing and Marketing Automation, which have no page yet).
2. Untick **Dry run** → **Run import**.

The packages are now live with the prices you approved on 15 Nov 2026 (full table in `PRICING.md`). To change one later: Products → (package) → edit the price.

### 5. Install and activate the theme
Appearance → Themes → Add Theme → **Upload Theme** → `iamfamous-theme.zip` → Install → **Activate**. The Hostinger AI theme stays installed; to roll back, reactivate it.

### 6. Build the site
Appearance → **IamFamous Setup** → leave the default ticks → **Build the site**.

This:
- creates Home, Services, all 20 service pages, Plans, How it works, About, Reviews, FAQ, Free growth check, Contact, Insights, five policy pages and seven guides
- links the Terms and Privacy pages to the checkout
- sets the Rank Math title, description and focus keyword on every page
- sets Home as the front page and Insights as the blog page
- uploads the logo and site icon, sets the title and tagline
- moves the previous site's pages to **Draft** (nothing is deleted; if one sits on an address the new site needs, its address gets `-old` added)
- sets WooCommerce to pounds, account required at checkout, and tax off (you are not VAT registered)

Read the result log. Lines starting `ERROR` or `SKIP` should not appear; send them to Claude if they do.

### 7. Rank Math checks
Do the five short **Rank Math checks** listed near the top of this file.

### 7b. Refresh links and caches
1. Settings → Permalinks → **Save Changes** (no need to change anything).
2. LiteSpeed Cache → Toolbox → **Purge All**. If CSS/JS optimisation or UCSS is on, purge those too.

### 8. Check on your phone (private tab)
- [ ] Home shows the dark plum hero: "Get seen. Get chosen. Get famous."
- [ ] Header shows only the logo, basket and **Menu**. Tap Menu: a panel slides in with Home first, then Services (tap it for all 20 in four groups), Local SEO, Websites, Social media, Contact.
- [ ] `/services/local-seo/`: scroll down past the hero. A **Choose package** bar appears at the bottom and disappears when you reach the packages section.
- [ ] `/free-growth-check/`: send a test request. It should appear in wp-admin → **Leads** and arrive at the admin email.
- [ ] `/web-development/` (an old address) lands on the new Website Development page.

### 9. Only if Elementor is still installed: switch it off safely
Do this **after** step 6, when the old pages are already drafts.
1. Plugins → deactivate in this order: **Envato Elements**, **MetForm**, **Elementor Pro** (if present), then **Elementor**. Deactivate only. Do not delete yet.
2. Check the site in a private tab. The new pages do not use Elementor, so nothing should change.
3. Leave **Hello Elementor** installed but inactive.
4. Keep everything deactivated for at least two weeks. Old Elementor pages keep their designs in the database while the plugins are only deactivated, so you can still switch back.
5. When you are sure, delete the plugins. Do **not** tick "remove data" in Elementor's settings first, in case you ever want the old pages back.
6. Yoast: either keep it or remove it, not both. The IamFamous plugin prints its own titles, descriptions, Open Graph tags and schema, and automatically steps aside when Yoast is active. If you keep Yoast, set titles there.

### Payments (test mode only)
Do not take live payments yet. When you want to test an order: Appearance → IamFamous Setup → untick everything except **Enable "Direct bank transfer"** → Build the site. Stripe and PayPal get connected in test mode in a later round, and go live only when you say so.

---

## 301 plan for the old URLs

The plugin redirects an old address **only when it would otherwise be a 404**, so a page that still exists is never hijacked. The map lives in `wp-content/plugins/iamfamous-core/includes/class-ifm-redirects.php`.

| Old address | Goes to |
|---|---|
| `/web-development/` | `/services/website-development/` |
| `/google-ads-service/` | `/services/ppc-management/` |
| `/backlinks-service/` | `/services/off-page-seo/` |
| `/monthly-seo-service/` | `/services/monthly-seo/` |
| `/on-page-seo/` | `/services/on-page-seo/` |
| `/content-writing/` | `/services/content-writing/` |
| `/social-media-management/` | `/services/social-media-management/` |
| `/contact-us/` | `/contact/` |
| `/about-us/` | `/about/` |
| `/faqs/` | `/faq/` |
| `/shop/` | `/plans/` |
| `/packages/` | `/plans/` |
| `/blog/` | `/insights/` |
| `/product-category/…` (7 old categories) | The matching service page |
| 9 backlink products | `/services/off-page-seo/` |
| 9 content products | `/services/content-writing/` |
| 6 Google Ads products | `/services/ppc-management/` |
| 6 on-page products | `/services/on-page-seo/` |
| 3 SEO plans | `/services/monthly-seo/` |
| 6 social plans | `/services/social-media-management/` |
| 4 website builds | `/services/website-development/` |
| E-Commerce Starter | `/services/ecommerce-website-development/` |
| Fix & Tune, Quick Fix | `/services/website-maintenance/` |
| Speed Boost, Starter Optimization | `/services/technical-seo/` |

**The 5 old blog posts** are not in the map because their addresses were not in the handover pack. They stay published and show in the new blog design. Before launch, decide for each one: keep, rewrite or redirect.

**Check after install:** in Google Search Console → Pages, look at "Not found (404)" after a week and send Claude any old address that still 404s.

---

## Editing later
- **Page text and images:** Pages → edit with the block editor. Everything is ordinary blocks. Running the Setup screen with **Overwrite** ticked replaces pages it created with the theme's latest version, so untick it if you have edited them by hand.
- **Menu links:** `ifm_nav_items()` in `wp-content/themes/iamfamous/inc/nav.php`, or ask Claude.
- **Prices and package details:** Products → (package). The picker, comparison table and "from" prices update everywhere.
- **Leads:** wp-admin → Leads.

## Before launch (owner)
- **Have the five policy pages checked** by someone qualified. They match how the site works, but they are not legal advice.
- Make sure the **support@iamfamous.uk** mailbox exists and receives mail.
- Check whether you need to pay the **ICO data protection fee** (ico.org.uk).
- Connect a payment provider in **test mode**, place a test order with an optional extra ticked, check the total, then go live.
- If you add Google Analytics, a pixel or a chat widget later, tell Claude: it must be wired to the cookie banner so it only loads after "Allow all", and the cookie policy must list it.
