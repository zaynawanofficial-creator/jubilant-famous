# IamFamous.uk: project brief (handover from the SEOBooster.uk session, 12 Nov 2026)

## 1. What we're building
A commercial agency site on WordPress + WooCommerce at **iamfamous.uk**, where UK small businesses buy fixed-price digital marketing packages, add project details, pay and track the order. It is the same business logic as SEOBooster.uk, rebuilt as the **master version**: better design, better conversion, cleaner code. Improvements made here are later back-ported to SEOBooster.uk.

- **Owner:** Zain Malik (zaynawanofficial-creator on GitHub). Briefs may come in Roman Urdu. Public copy is English with UK spelling.
- **Hosting:** Hostinger, the same account as seobooster.uk. Stack: PHP 8.5, WordPress 7.1.4, WooCommerce 11.2.1, LiteSpeed Cache, hcdn.
- **Current site (to replace):**
  - Elementor + Hello Elementor, Yoast, MetForm, Jetpack, Envato Elements, Duplicate Post
  - 20 pages, 5 posts, 48 WooCommerce products priced in USD
  - Generic template copy with typos and unverified counters ("Years of Experience", "Satisfied Client")
  - See `reference/`.
- **Mother brand:** iamfamous.com.au, which sells Instagram/Facebook/TikTok followers, likes and views. **Only its brand colours were taken.** Nothing else (services, copy, prices) carries over.

## 2. Positioning (so the two sites don't cannibalise each other)
| | SEOBooster.uk | **IamFamous.uk** |
|---|---|---|
| Who | International buyers, agencies (white label), technical SEO buyers | **UK small businesses**: local trades, clinics, salons, shops, ecommerce starters, coaches |
| Currency | USD | **GBP** |
| Price level | Budget-marketplace band | **10–25% cheaper in real value** (entry tiers −25%, top tiers −10%) |
| Voice | Expert, technical, precise | **Friendly, direct, confident**: "get seen, get chosen". Plain English, no jargon without a one-line explanation |
| Look | Navy / electric blue / lime, Manrope + Inter | **Famous Red / Midnight Plum / Spotlight Gold, Bricolage Grotesque + Figtree** (see BRAND.md) |
| Lead offers on the home page | Link building, SEO audit, monthly SEO | **Local SEO + Google Business Profile, a small-business website, social media management**, then the rest |

**Duplicate-network risk:** two sites with the same services, the same founder and the same host.
- **Every page needs original copy.** Never paste SEO Booster text; rewrite from the buyer's UK small-business angle.
- Don't add sitewide cross-links between the two sites or any link scheme.
- Each site gets its own Organization schema, email, phone and (if any) address.
- Founder: showing Zain Malik on both is honest and allowed. The owner must confirm he wants it (see DECISIONS.md).

## 3. Services (owner decision: the same ~20 services as SEO Booster)
- **SEO:** Monthly SEO, Local SEO, Link Building / Off-Page (Profiles & Citations; Premium Links: guest posts DR10–70, press release), SEO Audit, Technical SEO, On-Page SEO, Ecommerce SEO, AI SEO, White Label SEO, SEO Consultancy.
- **Websites:** Website Development, Ecommerce Websites, Website Maintenance, Website Migration.
- **Ads & social:** PPC (Google Ads), Facebook Ads, TikTok Ads, Social Media Management.
- **Content & design:** Content Writing, Graphic Design & Branding (incl. Canva Brand Kit).
- Analytics, Email Marketing and Marketing Automation exist in the catalogue as DRAFT. Publish them only if the owner asks.
- **No follower, like or view packages.** Stripe and PayPal prohibit them, and they would put the payment accounts at risk (owner decision, 12 Nov 2026).
- Draft catalogue: `IAMFAMOUS_CATALOG_DRAFT.csv` (113 rows, GBP, `price_approved=0`). Rule and reasoning: `PRICING.md`.

## 4. Site map (mirror SEO Booster's, plus UK specifics)
- Home
- Services hub (4 groups), plus 20 service pages at `/services/{slug}/`
- Packages
- How it works
- About (founder, if approved)
- Contact
- Get a quote / free growth check
- FAQ
- Insights (blog) + 7+ original UK-angle guides
- Legal: Terms, Refunds, Privacy, Cookies, Accessibility (drafts for legal review)
- Cart, Checkout, Order received, My account (brief + order status)

**301 redirects from the current site's slugs:** `web-development`, `google-ads-service`, `backlinks-service`, `monthly-seo-service`, `on-page-seo`, `content-writing`, `social-media-management`, `about-us`, `contact-us`, `faqs`, `blog`, the 48 old product slugs, and the 5 old posts (keep, rewrite or redirect). Check which old URLs are indexed or linked before deleting anything.

## 5. "Better than SEO Booster": the upgrade list
**Design**
- A sharper system: dark plum hero bands with a red glow, cream sections, gold accents for proof elements.
- Bigger, more expressive Bricolage headings.
- Every hero and service gets a **labelled, informative illustration**: real UI snippets such as a Google Business Profile card, a citation tracker, a content calendar, ad metrics labelled as examples, or a page wireframe. No stock people.
- Purposeful motion: section reveal and hover lift, with `prefers-reduced-motion` respected.

**Conversion**
- Sticky mobile "Choose package" bar on service pages.
- Package comparison table under the picker.
- Bundles ("Local Starter": GBP optimisation + citations + 1-page site). Prices need owner approval.
- Add-ons / order bumps on the product page (extra revisions, express delivery). Owner approval needed.
- A free "growth check" form as the main lead magnet.
- WhatsApp click-to-chat, if the owner supplies a number.
- Trust row backed only by verifiable facts: turnaround, revision rounds, live-URL reports, refund-before-work-starts, secure checkout.

**Performance budget (mobile)**
- LCP < 2.0s, CLS < 0.05, INP < 200ms.
- Own JS under 30 KB.
- Fonts self-hosted woff2 with preload. Images in WebP/AVIF with explicit sizes.

**Accessibility:** WCAG 2.2 AA. Contrast is checked in BRAND.md. Keyboard-complete menu, picker and checkout.

**Code**
- Same structure as SEO Booster: theme `iamfamous`, plugin `iamfamous-core`, prefixes `ifm_` (PHP), `ifm-` (CSS), `IFM_` (classes).
- Keep the file layout parallel to `seo-booster`, so fixes port by diff.
- Data-driven navigation (`inc/nav.php`) from day one.

## 6. Launch-critical inputs the owner still has to give (don't invent them)
- Legal/trading name, company number if any, registered address (or "online only")
- VAT status (affects whether prices show inc. VAT)
- Public email (proposed `hello@iamfamous.uk`; the mailbox must exist), phone or WhatsApp number
- Founder shown on IamFamous? Same bio and photo as SEO Booster?
- Real reviews or testimonials, if any (Trustpilot / Google). Without them, show no review blocks.
- Payment gateways (Stripe / PayPal / BACS) in test mode first; go-live approval
- Final price approval per package (the draft is in `IAMFAMOUS_CATALOG_DRAFT.csv`)
- A WordPress Application Password, only if the Hostinger Connector or MCP route works from the new session. Otherwise the zip install route.
