"""Builds SERVICE_CATALOG.csv (GBP) from SEO Booster's USD catalogue.

Rule (owner decision 12 Nov 2026): IamFamous.uk is cheaper than SEO Booster in real value.
  - Discount runs from 25% on the cheapest package in each group to 10% on the most expensive,
    in steps by price rank.
  - GBP = USD x FX x (1 - discount), rounded DOWN to a clean price point (nearest below GBP 30).
  - Quantity ladders (5/10/20 links ...) are forced to rise with quantity.
  - Rounding happens AFTER the exchange rate and discount are applied (owner instruction, 12 Nov 2026).
  - No package is priced under GBP 10 (MIN_PRICE, owner 16 Nov 2026; it was GBP 5). Cheap tiers above the
    minimum step up with it, because quantity ladders must always rise.
  - Monthly plans (unit "month") are all held at the 10% discount: they carry the ongoing work, and a
    steady, credible monthly price builds more trust than a cut-price one (owner delegated, 15 Nov 2026).
  - APPROVED = True marks every priced row price_approved=YES (owner approval, 15 Nov 2026).
  - BUNDLES adds starter bundles: the sum of their parts less BUNDLE_OFF, rounded down to a clean price.
FX: GBP 1 = USD 1.333 (x0.75), from the 6 Nov 2026 market close of 1.3352. Recheck before launch and re-run:
  python3 tools_price_draft.py reference/seobooster-catalog-usd-2026-11-12.csv SERVICE_CATALOG.csv
"""
import csv, sys
from collections import defaultdict

FX = 0.75
MIN_PRICE = 10
APPROVED = True
BUNDLE_OFF = 0.10
# (code, title, [component package codes], turnaround, ideal for)
BUNDLES = [
    ('IF-bundles-starter-bundles-1', 'Local Starter Bundle', ['IF-local-seo-local-seo-packages-1', 'IF-off-page-seo-profiles-and-citations-4', 'IF-website-development-website-packages-1'], '7–10 working days'),
    ('IF-bundles-starter-bundles-2', 'Get Found Bundle', ['IF-local-seo-local-seo-packages-2', 'IF-website-development-website-packages-3'], '10–14 working days'),
    ('IF-bundles-starter-bundles-3', 'Online Presence Bundle', ['IF-local-seo-local-seo-packages-2', 'IF-website-development-website-packages-3', 'IF-social-media-management-social-media-packages-2'], '10–14 working days'),
]
D_ENTRY, D_TOP = 0.25, 0.10
SMALL = [10, 12, 15, 18, 20, 22, 25, 29]  # clean price points under GBP 30
LADDER = sorted(set([v for v in SMALL if v >= MIN_PRICE] + [29, 32, 35, 39, 42, 45, 49, 55, 59, 65, 69, 75, 79, 85, 89, 95, 99, 109, 119, 129, 139, 149, 159, 169,
                179, 189, 199, 219, 229, 249, 269, 279, 299, 329, 349, 379, 399, 429, 449, 479, 499, 549, 599, 649, 699, 749, 799, 849, 899, 949, 999]))

def floor_ladder(t):
    if t < 30:  # small prices: round to nearest, or rounding down overshoots the discount
        return min(LADDER, key=lambda v: (abs(v - t), v))
    lo = [v for v in LADDER if v <= t]
    return lo[-1] if lo else LADDER[0]

def nxt(v):
    return next((x for x in LADDER if x > v), v + 10)

def num(s):
    try:
        return float(s)
    except (TypeError, ValueError):
        return None

def build(src_path, out_path):
    rows = list(csv.DictReader(open(src_path, encoding='utf-8')))
    groups = defaultdict(list)
    for i, r in enumerate(rows):
        if num(r['price']) is not None:
            groups[(r['service'], r['group'])].append(i)
    disc = {}
    for idx in groups.values():
        order = sorted(idx, key=lambda i: num(rows[i]['price']))
        n = len(order)
        for rank, i in enumerate(order):
            disc[i] = D_ENTRY if n == 1 else D_ENTRY - (D_ENTRY - D_TOP) * rank / (n - 1)
            if rows[i]['unit'] == 'month':
                disc[i] = D_TOP
    prev, out = {}, []
    for i, r in enumerate(rows):
        r = dict(r)
        usd = num(r['price'])
        r['currency'] = 'GBP'
        if r['package_code']:
            r['package_code'] = 'IF-' + r['package_code']
        note = ''
        if usd is not None:
            gbp = floor_ladder(usd * FX * (1 - disc[i]))
            key = (r['service'], r['group'], r['unit'])
            q = num(r['quantity']) or 0
            if key in prev and q > prev[key][0] > 0 and usd > prev[key][2] and gbp <= prev[key][1]:
                gbp = nxt(prev[key][1])
            prev[key] = (q, gbp, usd)
            real = 1 - gbp / (usd * FX)
            r['price'], r['price_approved'] = str(gbp), ('YES' if APPROVED else '0')
            cmp = f'{real:.0%} cheaper at FX {FX}' if real > 0 else f'held at the GBP {MIN_PRICE} minimum order'
            note = f'Reference ${usd:g} -> GBP {gbp} ({cmp}); ' + ('approved by owner 15-16 Nov 2026. ' if APPROVED else 'DRAFT, needs owner approval. ')
        r['notes'] = (note + (r['notes'] or '')).strip()
        out.append(r)
    by_code = {r['package_code']: r for r in out}
    for code, title, parts, turn in BUNDLES:
        total = sum(int(by_code[c]['price']) for c in parts)
        price = floor_ladder(total * (1 - BUNDLE_OFF))
        out.append(dict(category='Bundles', service='Bundles', group='Starter Bundles', package_code=code, package_title=title, quantity='', unit='project',
                        pricing_model='fixed', currency='GBP', price=str(price), price_approved='YES' if APPROVED else '0', scope_approved='YES', publish='READY',
                        turnaround=turn, revisions='As listed on each included package',
                        notes=f'Bundle of {" + ".join(by_code[c]["package_title"] for c in parts)}: GBP {total} separately, {1 - price / total:.0%} off.'))
    with open(out_path, 'w', newline='', encoding='utf-8') as f:
        w = csv.DictWriter(f, fieldnames=list(rows[0].keys()))
        w.writeheader()
        w.writerows(out)
    return rows, out

if __name__ == '__main__':
    src, dst = sys.argv[1], sys.argv[2]
    a, b = build(src, dst)
    print(f'{len(b)} rows written to {dst}')
