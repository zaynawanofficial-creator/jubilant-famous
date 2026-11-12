# IamFamous.uk pricing (DRAFT v2, 12 Nov 2026: needs owner approval before publishing)

## Owner decisions (12 Nov 2026)
- Charge in **GBP**.
- Be **cheaper than SEOBooster.uk in real value**, tiered: **−25% on each group's entry package, sliding to −10% on the top package.** Cheap entry tiers win first orders and reviews; upper tiers stay close to SEO Booster so the margin holds on big jobs.
- **Same scope, turnaround and revisions** as the matching SEO Booster package. The price is the difference, not the quality.

## How the draft was made
`tools_price_draft.py` reads the SEO Booster USD catalogue (snapshot in `reference/seobooster-catalog-usd-2026-11-12.csv`) and writes `SERVICE_CATALOG.csv`. That file is what the `iamfamous-core` importer reads. In it: SKUs are prefixed `IF-`, currency is GBP, and every row has `price_approved=0`.
1. Discount by price rank inside each group: 25% for the cheapest, 10% for the dearest, evenly in between.
2. `GBP = USD × 0.75 × (1 − discount)`. **0.75 = £1 : $1.333**, from the 6 Nov 2026 market close of 1.3352 (rechecked 12 Nov 2026; the first draft used 0.78).
3. **Rounding happens after the exchange rate and discount** (owner instruction, 12 Nov 2026), to clean price points: £5, 6, 8, 9, 10, 12, 15, 18, 19, 22, 25, 29 under £30, then 32, 35, 39, 42, 45, 49, 55, 59 … 99, 109, 119 … Quantity ladders (5/10/20… links) always rise with quantity.
4. **£5 minimum order.** Nothing is priced below £5, because card fees take most of a smaller order. Only "5 Profile & Citation Links" is affected (£3 → £5).
5. Quote-only packages stay quote-only.

Re-run after any change to the rate or the rule:
```
python3 tools_price_draft.py reference/seobooster-catalog-usd-2026-11-12.csv SERVICE_CATALOG.csv
```

## How to approve prices
Nothing with a fixed price is visible on the site until you approve it. Two ways:
- **All at once:** tell Claude "prices approved" (or list the ones to change). The catalogue is updated to `price_approved=YES`, the plugin zip is rebuilt, and you re-run Tools → IamFamous catalog.
- **One by one:** Products → (package) → tick **Price approved by owner** → Publish.

## Check before approving
- **Micro tiers** (10 links £6, DR10+ guest post £6, 1 social graphic £5, 500-word article £8): confirm they clear fulfilment cost plus payment fees.
- **Monthly plans** (Monthly SEO, Social, PPC, Ads management) carry most of the profit. They follow the same −25% to −10% rule today. Say so if you would rather hold them all at −10%.
- **VAT:** not registered, so no VAT is charged or shown (owner, 12 Nov 2026). WooCommerce tax calculation is switched off by the Setup screen.
- The old iamfamous.uk prices are in `reference/current-iamfamous-uk-products-2026-11-12.txt`. Old product URLs get 301 redirects (plugin: `class-ifm-redirects.php`).

## Draft table (* cheaper = real-value discount against the SEO Booster price at £1 = $1.333)
| Service | Package | Reference (USD) | IamFamous draft | Cheaper* |
|---|---|---:|---:|---:|
| Off-Page SEO | 5 Profile & Citation Links | $5 | £5 | £5 minimum |
| Off-Page SEO | 10 Profile & Citation Links | $10 | £6 | 20% |
| Off-Page SEO | 15 Profile & Citation Links | $14 | £8 | 24% |
| Off-Page SEO | 20 Profile & Citation Links | $17 | £10 | 22% |
| Off-Page SEO | 30 Profile & Citation Links | $24 | £15 | 17% |
| Off-Page SEO | 50 Profile & Citation Links | $37 | £25 | 10% |
| Off-Page SEO | 75 Profile & Citation Links | $52 | £32 | 18% |
| Off-Page SEO | 100 Profile & Citation Links | $65 | £42 | 14% |
| Off-Page SEO | Guest Post – DR 10+ | $10 | £6 | 20% |
| Off-Page SEO | Guest Post – DR 20+ | $25 | £15 | 20% |
| Off-Page SEO | Guest Post – DR 30+ | $49 | £29 | 21% |
| Off-Page SEO | Guest Post – DR 40+ | $69 | £42 | 19% |
| Off-Page SEO | Guest Post – DR 50+ | $89 | £55 | 18% |
| Off-Page SEO | Guest Post – DR 60+ | $139 | £89 | 15% |
| Off-Page SEO | Guest Post – DR 70+ | $199 | £129 | 14% |
| Off-Page SEO | Press Release – Writing + Distribution | $99 | £59 | 21% |
| Website Development | 1-Page Landing Website | $149 | £79 | 29% |
| Website Development | 2-Page Website | $249 | £149 | 20% |
| Website Development | 5-Page Website | $449 | £279 | 17% |
| Website Development | 10-Page Website | $799 | £499 | 17% |
| Website Maintenance | WordPress Issue Diagnosis | $29 | £15 | 31% |
| Website Maintenance | Single WordPress Fix | $49 | £29 | 21% |
| Website Maintenance | Multi-Fix Bundle (up to 5 issues) | $149 | £99 | 11% |
| Website Maintenance | Basic Care – 1 Month | $49 | £29 | 21% |
| Website Maintenance | Growth Care – 1 Month | $129 | £79 | 18% |
| Content Writing | 500-Word SEO Article | $15 | £8 | 29% |
| Content Writing | 1,000-Word SEO Article | $29 | £18 | 17% |
| Content Writing | 1,500-Word SEO Article | $42 | £25 | 21% |
| Content Writing | 2,000-Word SEO Article | $55 | £35 | 15% |
| Content Writing | 3,000-Word SEO Article | $79 | £49 | 17% |
| Content Writing | 3 × 1,000-Word Articles | $84 | £45 | 29% |
| Content Writing | 5 × 1,000-Word Articles | $129 | £75 | 22% |
| Content Writing | 10 × 1,000-Word Articles | $249 | £149 | 20% |
| Content Writing | 20 × 1,000-Word Articles | $469 | £299 | 15% |
| Graphic Design | 1 Social Media Graphic | $10 | £6 | 20% |
| Graphic Design | 3 Social Media Graphics | $25 | £15 | 20% |
| Graphic Design | 5 Social Media Graphics | $39 | £25 | 15% |
| Graphic Design | 10 Social Media Graphics | $69 | £45 | 13% |
| Graphic Design | Logo Design | $59 | £32 | 28% |
| Graphic Design | Mini Brand Kit | $99 | £59 | 21% |
| Graphic Design | Brand Refresh | $149 | £95 | 15% |
| Graphic Design | Full Brand Identity | $249 | £159 | 15% |
| SEO Audit | Quick Technical SEO Review | $49 | £29 | 21% |
| SEO Audit | Local SEO Audit | $79 | £45 | 24% |
| SEO Audit | Full SEO Audit | $149 | £89 | 20% |
| SEO Audit | Ecommerce SEO Audit | $249 | £159 | 15% |
| SEO Audit | Enterprise Audit (500+ pages) | QUOTE | QUOTE | – |
| Technical SEO | Schema Markup Setup | $79 | £42 | 29% |
| Technical SEO | Core Web Vitals Optimisation | $149 | £89 | 20% |
| Technical SEO | Technical Fix Bundle | $199 | £119 | 20% |
| Technical SEO | Technical SEO – 1 Month | $249 | £159 | 15% |
| Analytics and Tracking | GA4 Audit | $49 | £29 | 21% |
| Analytics and Tracking | GA4 and GTM Setup | $129 | £79 | 18% |
| Analytics and Tracking | Conversion Dashboard | $149 | £99 | 11% |
| Email Marketing | Email Template Design | $49 | £29 | 21% |
| Email Marketing | Automation Setup | $199 | £119 | 20% |
| Email Marketing | Campaign Management – 1 Month | $249 | £159 | 15% |
| Marketing Automation | Workflow Audit | $79 | £42 | 29% |
| Marketing Automation | Single Workflow Build | $149 | £99 | 11% |
| Marketing Automation | Managed Automation | QUOTE | QUOTE | – |
| Local SEO | Google Business Profile Optimisation | $79 | £42 | 29% |
| Local SEO | Local SEO Starter | $149 | £89 | 20% |
| Local SEO | Local SEO Growth | $299 | £199 | 11% |
| Local SEO | Local SEO Management – 1 Month | $249 | £149 | 20% |
| Monthly SEO | SEO Starter – 1 Month | $199 | £119 | 20% |
| Monthly SEO | SEO Growth – 1 Month | $399 | £249 | 17% |
| Monthly SEO | SEO Pro – 1 Month | $699 | £449 | 14% |
| Monthly SEO | SEO Strategy Roadmap | $149 | £79 | 29% |
| SEO Consultancy | 60-Minute SEO Consultation | $79 | £42 | 29% |
| SEO Consultancy | SEO Strategy Session | $149 | £89 | 20% |
| SEO Consultancy | 90-Day SEO Roadmap | $299 | £189 | 16% |
| SEO Consultancy | SEO Consultant – 1 Month | $499 | £329 | 12% |
| Ecommerce SEO | Product Page Optimisation (10 products) | $99 | £55 | 26% |
| Ecommerce SEO | Category Page Optimisation (5 categories) | $129 | £75 | 22% |
| Ecommerce SEO | Store SEO Setup (Shopify or WooCommerce) | $249 | £149 | 20% |
| Ecommerce SEO | Ecommerce SEO – 1 Month | $399 | £269 | 10% |
| On-Page SEO | On-Page SEO – 1 Page | $29 | £15 | 31% |
| On-Page SEO | On-Page SEO – 5 Pages | $119 | £69 | 23% |
| On-Page SEO | On-Page SEO – 10 Pages | $219 | £139 | 15% |
| On-Page SEO | On-Page SEO – 25 Pages | $499 | £329 | 12% |
| White Label SEO | White Label SEO Audit | $99 | £55 | 26% |
| White Label SEO | White Label Local SEO Pack | $199 | £119 | 20% |
| White Label SEO | White Label SEO – 1 Month (Starter) | $299 | £189 | 16% |
| White Label SEO | White Label SEO – 1 Month (Growth) | $599 | £399 | 11% |
| AI SEO | AI Search Visibility Check | $79 | £42 | 29% |
| AI SEO | Entity & Schema Setup | $149 | £89 | 20% |
| AI SEO | AI-Ready Content Optimisation (5 pages) | $199 | £119 | 20% |
| AI SEO | AI SEO – 1 Month | $349 | £229 | 13% |
| Website Migration | Migration Audit & Plan | $99 | £55 | 26% |
| Website Migration | WordPress Host Migration | $149 | £89 | 20% |
| Website Migration | SEO Website Migration (up to 100 pages) | $399 | £249 | 17% |
| Website Migration | Large SEO Migration (up to 500 pages) | $799 | £499 | 17% |
| Ecommerce Website Development | Product Upload (25 products) | $49 | £29 | 21% |
| Ecommerce Website Development | Shopify Starter Store | $549 | £329 | 20% |
| Ecommerce Website Development | WooCommerce Starter Store | $599 | £379 | 16% |
| Ecommerce Website Development | Growth Store (up to 100 products) | $999 | £649 | 13% |
| PPC Management | Google Ads Audit | $99 | £55 | 26% |
| PPC Management | Campaign Setup | $199 | £119 | 20% |
| PPC Management | PPC Management – 1 Month (Starter) | $299 | £189 | 16% |
| PPC Management | PPC Management – 1 Month (Growth) | $499 | £329 | 12% |
| Facebook Ads | Facebook Ads Audit | $79 | £42 | 29% |
| Facebook Ads | Facebook Ads Campaign Setup | $179 | £109 | 19% |
| Facebook Ads | Ad Creative Pack (5 ads) | $99 | £59 | 21% |
| Facebook Ads | Facebook Ads Management – 1 Month | $299 | £199 | 11% |
| TikTok Ads | TikTok Ads Audit | $79 | £45 | 24% |
| TikTok Ads | TikTok Hooks & Scripts (5) | $69 | £35 | 32% |
| TikTok Ads | TikTok Ads Campaign Setup | $199 | £119 | 20% |
| TikTok Ads | TikTok Ads Management – 1 Month | $299 | £199 | 11% |
| Social Media Management | Content Calendar (1 month) | $99 | £55 | 26% |
| Social Media Management | 12 Social Posts + Captions | $149 | £89 | 20% |
| Social Media Management | Social Management – 1 Month (Starter) | $249 | £149 | 20% |
| Social Media Management | Social Management – 1 Month (Growth) | $449 | £299 | 11% |
| Graphic Design | Canva Brand Kit Setup | $69 | £39 | 25% |
