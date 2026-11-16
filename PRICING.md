# IamFamous.uk pricing (v3, approved 15 Nov 2026)

## Status: APPROVED (15 Nov 2026)
The owner approved the prices on 15 Nov 2026 and asked for "what is more professional and trust building". Two things were decided on that basis:
- **Monthly plans are all held at the 10% discount** (not 25% on the smallest). They carry the ongoing work, and a steady monthly price reads as more credible than a cut-price one. Six prices rose by £10 to £20: Local SEO Management £159, SEO Starter £129, SEO Growth £269, White Label Starter £199, PPC Starter £199, Social Starter £159.
- **Three starter bundles** were added: the sum of their parts less 10%, rounded down to a clean price.

Every priced package with a live service page is now `price_approved=YES` and is published by the importer. Analytics, Email Marketing and Marketing Automation stay drafts because they have no page and no confirmed scope.

## How the prices are made
`tools_price_draft.py` reads the SEO Booster USD catalogue (snapshot in `reference/seobooster-catalog-usd-2026-11-12.csv`) and writes `SERVICE_CATALOG.csv`, which the `iamfamous-core` importer reads. SKUs are prefixed `IF-` and the currency is GBP.
1. Discount by price rank inside each group: 25% for the cheapest, 10% for the dearest, evenly in between. Monthly plans (unit "month") always get 10%.
2. `GBP = USD × 0.75 × (1 − discount)`. **0.75 = £1 : $1.333**, from the 6 Nov 2026 market close of 1.3352.
3. **Rounding happens after the exchange rate and discount**, to clean price points: £5, 6, 8, 9, 10, 12, 15, 18, 19, 22, 25, 29 under £30, then 32, 35, 39, 42, 45, 49, 55, 59 … 99, 109, 119 … Quantity ladders always rise with quantity.
4. **£5 minimum order.**
5. Quote-only packages stay quote-only.
6. Bundles: sum of the component prices × 0.90, rounded down to a clean price.

Re-run after any change to the rate or the rule:
```
python3 tools_price_draft.py reference/seobooster-catalog-usd-2026-11-12.csv SERVICE_CATALOG.csv
```

## Changing a price later
- **One package:** Products → (package) → change the price in Product data → Update. The picker, comparison table and "from" prices update everywhere.
- **Many at once:** tell Claude. The catalogue and the plugin zip are rebuilt, and you re-run Tools → IamFamous catalog.

## Worth watching after launch
- **Micro tiers** (5 links £5, 10 links £6, DR 10+ guest post £6, 1 social graphic £5, 500-word article £8): check they still clear your fulfilment cost and card fees. Raise them in wp-admin if not.
- **VAT:** not registered, so none is charged or shown. If you register, tell Claude: every "no VAT" statement on the site has to change.
- **Exchange rate:** prices are now fixed in pounds and do not move with the rate. Recheck the reference comparison only if you reprice.

## Price table (* cheaper = real-value discount against the SEO Booster price at £1 = $1.333)
| Service | Package | Reference (USD) | IamFamous price | Cheaper* | On sale |
|---|---|---:|---:|---:|---|
| Off-Page SEO | 5 Profile & Citation Links | $5 | £5 | £5 minimum | Yes |
| Off-Page SEO | 10 Profile & Citation Links | $10 | £6 | 20% | Yes |
| Off-Page SEO | 15 Profile & Citation Links | $14 | £8 | 24% | Yes |
| Off-Page SEO | 20 Profile & Citation Links | $17 | £10 | 22% | Yes |
| Off-Page SEO | 30 Profile & Citation Links | $24 | £15 | 17% | Yes |
| Off-Page SEO | 50 Profile & Citation Links | $37 | £25 | 10% | Yes |
| Off-Page SEO | 75 Profile & Citation Links | $52 | £32 | 18% | Yes |
| Off-Page SEO | 100 Profile & Citation Links | $65 | £42 | 14% | Yes |
| Off-Page SEO | Guest Post – DR 10+ | $10 | £6 | 20% | Yes |
| Off-Page SEO | Guest Post – DR 20+ | $25 | £15 | 20% | Yes |
| Off-Page SEO | Guest Post – DR 30+ | $49 | £29 | 21% | Yes |
| Off-Page SEO | Guest Post – DR 40+ | $69 | £42 | 19% | Yes |
| Off-Page SEO | Guest Post – DR 50+ | $89 | £55 | 18% | Yes |
| Off-Page SEO | Guest Post – DR 60+ | $139 | £89 | 15% | Yes |
| Off-Page SEO | Guest Post – DR 70+ | $199 | £129 | 14% | Yes |
| Off-Page SEO | Press Release – Writing + Distribution | $99 | £59 | 21% | Yes |
| Website Development | 1-Page Landing Website | $149 | £79 | 29% | Yes |
| Website Development | 2-Page Website | $249 | £149 | 20% | Yes |
| Website Development | 5-Page Website | $449 | £279 | 17% | Yes |
| Website Development | 10-Page Website | $799 | £499 | 17% | Yes |
| Website Maintenance | WordPress Issue Diagnosis | $29 | £15 | 31% | Yes |
| Website Maintenance | Single WordPress Fix | $49 | £29 | 21% | Yes |
| Website Maintenance | Multi-Fix Bundle (up to 5 issues) | $149 | £99 | 11% | Yes |
| Website Maintenance | Basic Care – 1 Month | $49 | £29 | 21% | Yes |
| Website Maintenance | Growth Care – 1 Month | $129 | £79 | 18% | Yes |
| Content Writing | 500-Word SEO Article | $15 | £8 | 29% | Yes |
| Content Writing | 1,000-Word SEO Article | $29 | £18 | 17% | Yes |
| Content Writing | 1,500-Word SEO Article | $42 | £25 | 21% | Yes |
| Content Writing | 2,000-Word SEO Article | $55 | £35 | 15% | Yes |
| Content Writing | 3,000-Word SEO Article | $79 | £49 | 17% | Yes |
| Content Writing | 3 × 1,000-Word Articles | $84 | £45 | 29% | Yes |
| Content Writing | 5 × 1,000-Word Articles | $129 | £75 | 22% | Yes |
| Content Writing | 10 × 1,000-Word Articles | $249 | £149 | 20% | Yes |
| Content Writing | 20 × 1,000-Word Articles | $469 | £299 | 15% | Yes |
| Graphic Design | 1 Social Media Graphic | $10 | £6 | 20% | Yes |
| Graphic Design | 3 Social Media Graphics | $25 | £15 | 20% | Yes |
| Graphic Design | 5 Social Media Graphics | $39 | £25 | 15% | Yes |
| Graphic Design | 10 Social Media Graphics | $69 | £45 | 13% | Yes |
| Graphic Design | Logo Design | $59 | £32 | 28% | Yes |
| Graphic Design | Mini Brand Kit | $99 | £59 | 21% | Yes |
| Graphic Design | Brand Refresh | $149 | £95 | 15% | Yes |
| Graphic Design | Full Brand Identity | $249 | £159 | 15% | Yes |
| SEO Audit | Quick Technical SEO Review | $49 | £29 | 21% | Yes |
| SEO Audit | Local SEO Audit | $79 | £45 | 24% | Yes |
| SEO Audit | Full SEO Audit | $149 | £89 | 20% | Yes |
| SEO Audit | Ecommerce SEO Audit | $249 | £159 | 15% | Yes |
| SEO Audit | Enterprise Audit (500+ pages) | QUOTE | QUOTE | – | Quote |
| Technical SEO | Schema Markup Setup | $79 | £42 | 29% | Yes |
| Technical SEO | Core Web Vitals Optimisation | $149 | £89 | 20% | Yes |
| Technical SEO | Technical Fix Bundle | $199 | £119 | 20% | Yes |
| Technical SEO | Technical SEO – 1 Month | $249 | £159 | 15% | Yes |
| Analytics and Tracking | GA4 Audit | $49 | £29 | 21% | No (no page yet) |
| Analytics and Tracking | GA4 and GTM Setup | $129 | £79 | 18% | No (no page yet) |
| Analytics and Tracking | Conversion Dashboard | $149 | £99 | 11% | No (no page yet) |
| Email Marketing | Email Template Design | $49 | £29 | 21% | No (no page yet) |
| Email Marketing | Automation Setup | $199 | £119 | 20% | No (no page yet) |
| Email Marketing | Campaign Management – 1 Month | $249 | £159 | 15% | No (no page yet) |
| Marketing Automation | Workflow Audit | $79 | £42 | 29% | No (no page yet) |
| Marketing Automation | Single Workflow Build | $149 | £99 | 11% | No (no page yet) |
| Marketing Automation | Managed Automation | QUOTE | QUOTE | – | No (no page yet) |
| Local SEO | Google Business Profile Optimisation | $79 | £42 | 29% | Yes |
| Local SEO | Local SEO Starter | $149 | £89 | 20% | Yes |
| Local SEO | Local SEO Growth | $299 | £199 | 11% | Yes |
| Local SEO | Local SEO Management – 1 Month | $249 | £159 | 15% | Yes |
| Monthly SEO | SEO Starter – 1 Month | $199 | £129 | 14% | Yes |
| Monthly SEO | SEO Growth – 1 Month | $399 | £269 | 10% | Yes |
| Monthly SEO | SEO Pro – 1 Month | $699 | £449 | 14% | Yes |
| Monthly SEO | SEO Strategy Roadmap | $149 | £79 | 29% | Yes |
| SEO Consultancy | 60-Minute SEO Consultation | $79 | £42 | 29% | Yes |
| SEO Consultancy | SEO Strategy Session | $149 | £89 | 20% | Yes |
| SEO Consultancy | 90-Day SEO Roadmap | $299 | £189 | 16% | Yes |
| SEO Consultancy | SEO Consultant – 1 Month | $499 | £329 | 12% | Yes |
| Ecommerce SEO | Product Page Optimisation (10 products) | $99 | £55 | 26% | Yes |
| Ecommerce SEO | Category Page Optimisation (5 categories) | $129 | £75 | 22% | Yes |
| Ecommerce SEO | Store SEO Setup (Shopify or WooCommerce) | $249 | £149 | 20% | Yes |
| Ecommerce SEO | Ecommerce SEO – 1 Month | $399 | £269 | 10% | Yes |
| On-Page SEO | On-Page SEO – 1 Page | $29 | £15 | 31% | Yes |
| On-Page SEO | On-Page SEO – 5 Pages | $119 | £69 | 23% | Yes |
| On-Page SEO | On-Page SEO – 10 Pages | $219 | £139 | 15% | Yes |
| On-Page SEO | On-Page SEO – 25 Pages | $499 | £329 | 12% | Yes |
| White Label SEO | White Label SEO Audit | $99 | £55 | 26% | Yes |
| White Label SEO | White Label Local SEO Pack | $199 | £119 | 20% | Yes |
| White Label SEO | White Label SEO – 1 Month (Starter) | $299 | £199 | 11% | Yes |
| White Label SEO | White Label SEO – 1 Month (Growth) | $599 | £399 | 11% | Yes |
| AI SEO | AI Search Visibility Check | $79 | £42 | 29% | Yes |
| AI SEO | Entity & Schema Setup | $149 | £89 | 20% | Yes |
| AI SEO | AI-Ready Content Optimisation (5 pages) | $199 | £119 | 20% | Yes |
| AI SEO | AI SEO – 1 Month | $349 | £229 | 13% | Yes |
| Website Migration | Migration Audit & Plan | $99 | £55 | 26% | Yes |
| Website Migration | WordPress Host Migration | $149 | £89 | 20% | Yes |
| Website Migration | SEO Website Migration (up to 100 pages) | $399 | £249 | 17% | Yes |
| Website Migration | Large SEO Migration (up to 500 pages) | $799 | £499 | 17% | Yes |
| Ecommerce Website Development | Product Upload (25 products) | $49 | £29 | 21% | Yes |
| Ecommerce Website Development | Shopify Starter Store | $549 | £329 | 20% | Yes |
| Ecommerce Website Development | WooCommerce Starter Store | $599 | £379 | 16% | Yes |
| Ecommerce Website Development | Growth Store (up to 100 products) | $999 | £649 | 13% | Yes |
| PPC Management | Google Ads Audit | $99 | £55 | 26% | Yes |
| PPC Management | Campaign Setup | $199 | £119 | 20% | Yes |
| PPC Management | PPC Management – 1 Month (Starter) | $299 | £199 | 11% | Yes |
| PPC Management | PPC Management – 1 Month (Growth) | $499 | £329 | 12% | Yes |
| Facebook Ads | Facebook Ads Audit | $79 | £42 | 29% | Yes |
| Facebook Ads | Facebook Ads Campaign Setup | $179 | £109 | 19% | Yes |
| Facebook Ads | Ad Creative Pack (5 ads) | $99 | £59 | 21% | Yes |
| Facebook Ads | Facebook Ads Management – 1 Month | $299 | £199 | 11% | Yes |
| TikTok Ads | TikTok Ads Audit | $79 | £45 | 24% | Yes |
| TikTok Ads | TikTok Hooks & Scripts (5) | $69 | £35 | 32% | Yes |
| TikTok Ads | TikTok Ads Campaign Setup | $199 | £119 | 20% | Yes |
| TikTok Ads | TikTok Ads Management – 1 Month | $299 | £199 | 11% | Yes |
| Social Media Management | Content Calendar (1 month) | $99 | £55 | 26% | Yes |
| Social Media Management | 12 Social Posts + Captions | $149 | £89 | 20% | Yes |
| Social Media Management | Social Management – 1 Month (Starter) | $249 | £159 | 15% | Yes |
| Social Media Management | Social Management – 1 Month (Growth) | $449 | £299 | 11% | Yes |
| Graphic Design | Canva Brand Kit Setup | $69 | £39 | 25% | Yes |
| Bundles | Local Starter Bundle | – | £109 | 17% off £131 | Yes |
| Bundles | Get Found Bundle | – | £329 | 11% off £368 | Yes |
| Bundles | Online Presence Bundle | – | £399 | 13% off £457 | Yes |
