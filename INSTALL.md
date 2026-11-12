# INSTALL — IamFamous.uk

## Round 1: theme 1.0.0 + plugin 1.0.0 (12 Nov 2026)

Two files to upload: **`iamfamous-core.zip`** (plugin) and **`iamfamous-theme.zip`** (theme). Allow 15 minutes. Do the steps in this order.

Your screenshots show the site currently running the **Hostinger AI theme** with only Hostinger and LiteSpeed plugins. WooCommerce and Elementor are not in the plugin list, so step 2 installs WooCommerce and step 9 only applies if Elementor is still there.

### 1. Backup
Hostinger hPanel → Websites → iamfamous.uk → Backups → **Create backup**. Wait for it to finish. Every step below can be undone, and nothing here deletes content, but a backup makes rollback one click.

### 2. Install WooCommerce
Plugins → Add Plugin → search **WooCommerce** → Install → Activate. Skip its setup wizard (the Setup screen in step 6 sets what matters). Do not connect a payment provider yet.

### 3. Install the plugin
Plugins → Add Plugin → **Upload Plugin** → `iamfamous-core.zip` → Install → **Activate**.

### 4. Import the catalogue (prices stay hidden)
Tools → **IamFamous catalog**.
1. Leave **Dry run** ticked → **Run import**. You should see 113 lines: 112 ending in `draft` and 1 quote-only package (Enterprise Audit) ending in `publish`.
2. Untick **Dry run** → **Run import**.

The 112 priced packages are created as **drafts** with the draft GBP prices; the one quote-only package has no price. Nothing with a price is visible on the site until you approve prices (see "Approving prices" below). Until then, service pages show a "Prices for this service are being confirmed" box with an **Ask for a price** button.

### 5. Install and activate the theme
Appearance → Themes → Add Theme → **Upload Theme** → `iamfamous-theme.zip` → Install → **Activate**. The Hostinger AI theme stays installed; to roll back, reactivate it.

### 6. Build the site
Appearance → **IamFamous Setup** → leave the default ticks → **Build the site**.

This:
- creates Home, Services, Local SEO, Website Development, Social Media Management, Free growth check, Contact and Insights
- sets Home as the front page and Insights as the blog page
- uploads the logo and site icon, sets the title and tagline
- moves the previous site's pages to **Draft** (nothing is deleted; if one sits on an address the new site needs, its address gets `-old` added)
- sets WooCommerce to pounds, account required at checkout, and tax off (you are not VAT registered)

Read the result log. Lines starting `ERROR` or `SKIP` should not appear; send them to Claude if they do.

### 7. Refresh links and caches
1. Settings → Permalinks → **Save Changes** (no need to change anything).
2. LiteSpeed Cache → Toolbox → **Purge All**. If CSS/JS optimisation or UCSS is on, purge those too.

### 8. Check on your phone (private tab)
- [ ] Home shows the dark plum hero: "Get seen. Get chosen. Get famous."
- [ ] Header shows only the logo, basket and **Menu**. Tap Menu: a panel slides in with Home first, then Services, Local SEO, Websites, Social media, Contact.
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

### Approving prices
The draft is in `PRICING.md` (full table) and `SERVICE_CATALOG.csv`.
- **All at once:** tell Claude "prices approved", or list the changes. You get a new `iamfamous-core.zip`; upload it (Replace current with uploaded) and re-run Tools → IamFamous catalog with Dry run unticked. Packages publish and prices appear everywhere.
- **One by one:** Products → open a package → tick **Price approved by owner** → Publish.

### Payments (test mode only)
Do not take live payments yet. When you want to test an order: Appearance → IamFamous Setup → untick everything except **Enable "Direct bank transfer"** → Build the site. Stripe and PayPal get connected in test mode in a later round, and go live only when you say so.

---

## 301 plan for the old URLs

The plugin redirects an old address **only when it would otherwise be a 404**, so a page that still exists is never hijacked. The map lives in `wp-content/plugins/iamfamous-core/includes/class-ifm-redirects.php`.

| Old address | Goes to | Why |
|---|---|---|
| `/web-development/` | `/services/website-development/` | Same service |
| `/social-media-management/` | `/services/social-media-management/` | Same service |
| `/google-ads-service/`, `/backlinks-service/`, `/monthly-seo-service/`, `/on-page-seo/`, `/content-writing/`, `/faqs/`, `/shop/` | `/services/` | Their own pages come in later rounds; each rule is then pointed at its page |
| `/contact-us/` | `/contact/` | Same page |
| `/about-us/` | `/` | No About page yet |
| `/blog/` | `/insights/` | Same page, new name |
| `/product-category/…` (7 categories) | The matching service page, or `/services/` | Old shop categories |
| `/product/sm-…` (6 products) | `/services/social-media-management/` | Old social plans |
| `/product/…` website products (9) | `/services/website-development/` | Old website plans |
| `/product/…` the other 33 products | `/services/` | Backlink, content, ads, on-page and SEO plans |

**The 5 old blog posts** are not in the map because their addresses were not in the handover pack. They stay published and show in the new blog design. Before launch, decide for each one: keep, rewrite or redirect.

**Check after install:** in Google Search Console → Pages, look at "Not found (404)" after a week and send Claude any old address that still 404s.

---

## Editing later
- **Page text and images:** Pages → edit with the block editor. Everything is ordinary blocks. Running the Setup screen with **Overwrite** ticked replaces pages it created with the theme's latest version, so untick it if you have edited them by hand.
- **Menu links:** `ifm_nav_items()` in `wp-content/themes/iamfamous/inc/nav.php`, or ask Claude.
- **Prices and package details:** Products → (package). The picker, comparison table and "from" prices update everywhere.
- **Leads:** wp-admin → Leads.

## Before launch (owner)
- Make sure the **support@iamfamous.uk** mailbox exists and receives mail.
- Approve prices.
- Send real reviews if you want a reviews section (none reached this session).
- Legal pages (Terms, Refunds, Privacy, Cookies) arrive in a later round and need your company details.
