# IamFamous.uk

Fixed-price digital marketing packages for UK small businesses, on WordPress + WooCommerce. Block theme `iamfamous` + plugin `iamfamous-core`. This is the "master" build of the SEOBooster.uk logic: same order flow, new brand, original copy.

**Start with:** `INSTALL.md` (how to put it live), `PROGRESS.md` (what is done and tested), `DECISIONS.md` (what is locked and what is still open), `PRICING.md` (draft GBP prices awaiting approval).

| Path | What it is |
|---|---|
| `wp-content/themes/iamfamous/` | Block theme: theme.json tokens, templates, parts, generated page patterns, nav, Setup screen |
| `wp-content/plugins/iamfamous-core/` | Packages, picker + comparison table, order flow, brief, order statuses, lead forms, SEO head, 301 redirects, catalogue importer |
| `SERVICE_CATALOG.csv` | 113 packages in GBP, all `price_approved=0` (DRAFT). Built by `tools_price_draft.py` |
| `tools/` | Generators: `build_patterns.py` (pages, footer, manifest) with page data in `services_seo.py` / `services_more.py`; `build_art.py` + `build_art.js` (icons, illustrations) with `scenes_round2.py`; `build_package_images.*`; zip builders |
| `tests/` | `*-smoke.php` (importer, nav, redirects), `validate-blocks.js`, `local-wp/` (real WordPress render) |
| `brand/`, `BRAND.md` | Brand system v1, logo files, fonts |
| `docs/screens/` | Screenshots from the local WordPress render, per round |
| `reference/` | Old-site snapshots, old product list, the SEO Booster USD catalogue used for pricing |

Build the zips:
```bash
tools/build-plugin-zip.sh
```
```bash
tools/build-theme-zip.sh
```
