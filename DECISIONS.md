# IamFamous.uk: owner decisions

## Locked (12 Nov 2026, in the SEOBooster.uk session)
| Item | Decision |
|---|---|
| Project | New site at iamfamous.uk. Same nature and logic as SEOBooster.uk, built as the **master** version (better design, development and conversion). Improvements later port back to SEO Booster |
| Session / repo | Separate GitHub repo and a separate Claude Code session on the "IamFamous UK" cloud environment. The SEO Booster repo (`jubilant-broccoli`) is attached read-only as the reference implementation |
| Route | `wordpress`: custom block theme `iamfamous` + plugin `iamfamous-core` + WooCommerce. Elementor, Hello Elementor and MetForm stay installed but inactive until the owner removes them |
| Services | **The same ~20 services as SEO Booster** (SEO, link building, websites, ads, social media management, content, design). **No follower/like/view packages** (Stripe/PayPal prohibit them) |
| Pricing | **Tiered discount vs SEO Booster in real value: −25% on each group's entry package, down to −10% on the top package.** Draft in `IAMFAMOUS_CATALOG_DRAFT.csv`, every row `price_approved=0`. The owner approves before publishing |
| Currency | **GBP (£)** |
| Brand | **v1 approved**: Famous Red #E11D2E, Red Deep #B5122B, Heritage Red #F23131 (large art only), Midnight Plum #160F1F, Plum 2 #2A1F38, Spotlight Gold #FFB020, Rose 100 #FFE1E1, Rose 50 #FFF1F1, Cream #FFF8F4, Body #3D3647, Muted #6B6475, Line #EDE6EA. Fonts: Bricolage Grotesque (headings) + Figtree (body), self-hosted. New logo: cheering figure on a stage arc under a gold star, plus the "IamFamous" wordmark (files in `brand/`) |
| Brand source | Only the colours came from iamfamous.com.au (red #F23131 / #DD0000 / pink #FFE1E1). Its services, copy, fonts and prices are **not** used |
| Content | Original copy for every page (no reuse of SEO Booster text). UK spelling. No invented numbers, reviews or claims (see LESSONS_LEARNED.md §E) |
| Payments | Test/sandbox only until the owner approves going live |
| Delivery | Zips + INSTALL.md steps (proxy blocks WP REST auth from cloud containers). Use the Hostinger Connector if the owner authorises it |

## Locked (12 Nov 2026, owner answers at the start of the IamFamous session)
| Item | Decision |
|---|---|
| Trading name | **IamFamous**. No company number or address supplied, so none is shown |
| VAT | **Not registered, not charged.** The site says "no VAT added"; WooCommerce tax calculation is off |
| Public email | **support@iamfamous.uk** (used in the footer, contact page, checkout, drawer and Organization schema). The mailbox must exist before launch |
| Founder | **Not shown.** IamFamous speaks as an agency ("the IamFamous team"). No named person, bio or photo; articles are attributed to the organisation |
| Prices | "Improve prices but round off after the exchange rate": rate rechecked (£1 = $1.333), rounding moved after conversion to clean price points, £5 minimum order. Still DRAFT (`price_approved=0`) |
| Service pages | Round 1: Local SEO, Website Development, Social Media Management. Round 2 (13 Nov 2026): the other 17. All 20 are live pages with their own packages |
| Lead magnet | `/free-growth-check/` replaces "Get a quote" as the main form (it also takes price requests) |

## Decided by Claude in round 1 (change any of these by saying so)
| Item | Decision and reason |
|---|---|
| Menu | Home, Services (mega panel with all 20 in four groups), then the three lead offers (Local SEO, Websites, Social media) and Contact. Edit in `inc/nav.php` |
| Footer and checkout links | No links to Terms, Refunds, Privacy or Cookies until those pages exist (a later round). The lead form states how the data is used |
| Previous site pages | The Setup screen moves old published pages to Draft (never deletes) and renames a clashing address to `…-old` |
| Old URLs | 301 only when the old address is a 404, so nothing live is hijacked. Map in `class-ifm-redirects.php` |
| "From" prices | `[ifm_from]` shows a fallback line ("Fixed price, agreed in writing") while no price is published |
| Response-time wording | The site says "we reply within one working day" (forms, checkout). Confirm you can keep it, or give the figure you want |
| Payment marks | Footer shows Visa, Mastercard, Amex, PayPal, Apple Pay, Google Pay (what Stripe + PayPal provide). Trim once the gateways are chosen |

## Decided by Claude in round 2
| Item | Decision and reason |
|---|---|
| Analytics, Email Marketing, Marketing Automation | In the catalogue as drafts, no pages (the brief says publish only if the owner asks) |
| Scope stated in copy | Page copy only promises what the package deliverables list. Where a draft said more (shop test orders, video editing, a refund if a fix fails) it was removed |
| Service names | "Link Building" for Off-Page SEO, "Google Ads (PPC)" for PPC Management, "Facebook & Instagram Ads" for Facebook Ads. Page addresses keep the catalogue slugs |

## Locked (15 Nov 2026, owner answers before round 3)
| Item | Decision |
|---|---|
| Prices | **Approved.** The owner delegated the judgement ("what is more professional, trust building"): monthly plans held at −10%, three starter bundles added. See PRICING.md |
| Reviews | 28 customer quotes supplied by the owner (`reference/owner-testimonials-2026-11-15.md`). Published word for word, without names, star ratings or review schema. The owner is responsible for holding the record of who said what |
| Response time | "We reply within one working day" confirmed |
| Business name and address | IamFamous, 27 Old Gloucester Street, London, WC1N 3AX, United Kingdom. Shown in the footer, contact, about and legal pages, Organisation schema and `llms.txt`. Described as a postal address (we work online) |
| SEO plugin | Rank Math and WooCommerce are installed. Rank Math owns titles, descriptions, robots and the Organisation schema; our plugin adds Service and FAQ schema to its graph and sets the per-page fields from the Setup screen |

## Decided by Claude in round 3 (change any of these by saying so)
| Item | Decision and reason |
|---|---|
| Legal pages | Written to match how the site works and published without a "draft" banner. **Not legal advice: have them checked.** They commit you to: refunds paid within 5 working days of being agreed; the right to cancel an undelivered part if we are more than 10 working days late; complaints answered within one working day and resolved within 10; project files deleted 12 months after an order; unanswered enquiries deleted after 12 months. Tell Claude if any of these should change |
| Cookies | The cookie policy says only essential cookies are used and no banner is shown. True for this build. **If you add Google Analytics, a pixel or a chat widget, you need a consent banner and the policy must change** |
| Bundles | Three bundles at about 10% off the parts. Add-ons (express delivery, extra revisions) are not built: they need a delivery promise from you first |
| Reviews wording | Labelled "IamFamous customer" plus the service. No claims about who the customers are |
| Guides | 3 of the planned 7+ are written (Google Business Profile checklist, small business website checklist, 20 social post ideas) |

## Locked (16 Nov 2026, owner answers before round 4)
| Item | Decision |
|---|---|
| Legal identity | **Mehreen, a sole trader trading as IamFamous.** Shown in the footer, About, Contact, the five policy pages and `llms.txt` |
| Cookie banner | Built: small card (full-width bar on phones) with "Essential only" and "Allow all". The choice is kept for 180 days in `ifm_consent`; "Cookie settings" in the footer reopens it |
| Prices | Minimum raised to £10, cheap tiers stepped up (see PRICING.md) |
| Remaining jobs | Finished: four more guides (seven in total), add-ons, Local SEO order form, automatic 12-month clean-up |

## Decided by Claude in round 4 (change any of these by saying so)
| Item | Decision and reason |
|---|---|
| Add-on prices | Extra round of changes +15% (minimum £10); express delivery +25% (minimum £15). Not offered on monthly, booked-call or quote packages |
| Express promise | "We aim to deliver in half the usual time; if it takes longer than the standard delivery time, the express fee is refunded." This is in the terms. Remove express with the `ifm_addons` filter if you cannot staff it |
| Cookie banner behaviour | No optional cookies exist today, so both choices give the same site. Any analytics added later must check `window.ifmConsent() === 'all'` before loading |
| Owner's name | Shown as "Mehreen", exactly as supplied. If a surname should appear, tell Claude |

## Open (ask the owner when it becomes blocking; don't invent)
| Item | Status |
|---|---|
| ICO registration | Most businesses that handle customer details must pay the ICO data protection fee. Check ico.org.uk and tell Claude the registration number if you want it shown |
| Phone / WhatsApp | Not supplied. No phone number or WhatsApp button is shown |
| Hostinger MCP (`websites-agents.hostinger.com/iamfamous.uk/mcp`) | Not reachable from the cloud session (the environment's network policy refuses the host), so delivery is by zip. To use it, allow that host in the environment and add it as a connector |
| Old site URLs | 301 map shipped for the old pages, product categories and 48 products. The 5 old posts are not redirected (not listed in the handover): decide keep, rewrite or redirect |
| "IamFamous" capital I | In sans-serif type "I" can read as "l". Accepted for v1 (same as .com.au). Alternative: a slightly wider gap or a colour split after "I" if the owner wants it |
