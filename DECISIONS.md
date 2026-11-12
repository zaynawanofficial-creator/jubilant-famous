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
| Service scope for round 1 | Live pages: Local SEO, Website Development, Social Media Management. The other 17 services are listed on the hub with "Ask for a price" until their pages are written |
| Lead magnet | `/free-growth-check/` replaces "Get a quote" as the main form (it also takes price requests) |

## Decided by Claude in round 1 (change any of these by saying so)
| Item | Decision and reason |
|---|---|
| Menu | Flat for now (Home, Services, Local SEO, Websites, Social media, Contact), because only three service pages exist. The mega panel code is kept and switches on when groups are added in `inc/nav.php` |
| Footer and checkout links | No links to Terms, Refunds, Privacy or Cookies until those pages exist (a later round). The lead form states how the data is used |
| Previous site pages | The Setup screen moves old published pages to Draft (never deletes) and renames a clashing address to `…-old` |
| Old URLs | 301 only when the old address is a 404, so nothing live is hijacked. Map in `class-ifm-redirects.php` |
| "From" prices | `[ifm_from]` shows a fallback line ("Fixed price, agreed in writing") while no price is published |
| Response-time wording | The site says "we reply within one working day" (forms, checkout). Confirm you can keep it, or give the figure you want |
| Payment marks | Footer shows Visa, Mastercard, Amex, PayPal, Apple Pay, Google Pay (what Stripe + PayPal provide). Trim once the gateways are chosen |

## Open (ask the owner when it becomes blocking; don't invent)
| Item | Status |
|---|---|
| Company number / registered address | Not supplied. Left out of the site. Needed for the legal pages round |
| Phone / WhatsApp | Not supplied. No phone number or WhatsApp button is shown |
| Reviews/testimonials | The owner said "reviews attached", but no review file reached the session (only the handover zip and two screenshots). No review blocks are shown. Send the reviews as text, a file or a link (Google / Trustpilot) |
| Final price approval | Draft v2 is in `SERVICE_CATALOG.csv` and `PRICING.md`. Nothing is published until approved |
| Hostinger MCP (`websites-agents.hostinger.com/iamfamous.uk/mcp`) | Not reachable from the cloud session (the environment's network policy refuses the host), so delivery is by zip. To use it, allow that host in the environment and add it as a connector |
| Micro-tier prices | A £5 floor is applied. Tiers at £5–£8 still need checking against fulfilment cost |
| Bundles and add-ons | Concept only. Prices need owner approval |
| Old site URLs | 301 map shipped for the old pages, product categories and 48 products. The 5 old posts are not redirected (not listed in the handover): decide keep, rewrite or redirect |
| "IamFamous" capital I | In sans-serif type "I" can read as "l". Accepted for v1 (same as .com.au). Alternative: a slightly wider gap or a colour split after "I" if the owner wants it |
