"""Builds SERVICE_CATALOG.csv (GBP, DRAFT) from SEO Booster's USD catalogue.

Rule (owner decision 12 Nov 2026): IamFamous.uk is cheaper than SEO Booster in real value.
  - Discount runs from 25% on the cheapest package in each group to 10% on the most expensive,
    in steps by price rank.
  - GBP = USD x FX x (1 - discount), rounded DOWN to a clean price point (nearest below GBP 30).
  - Quantity ladders (5/10/20 links ...) are forced to rise with quantity.
  - Rounding happens AFTER the exchange rate and discount are applied (owner instruction, 12 Nov 2026).
  - No package is priced under GBP 5 (MIN_PRICE): card fees eat most of a smaller order.
FX: GBP 1 = USD 1.333 (x0.75), from the 6 Nov 2026 market close of 1.3352. Recheck before launch and re-run:
  python3 tools_price_draft.py reference/seobooster-catalog-usd-2026-11-12.csv SERVICE_CATALOG.csv
"""
import csv, sys
from collections import defaultdict

FX = 0.75
MIN_PRICE = 5
D_ENTRY, D_TOP = 0.25, 0.10
SMALL = [5, 6, 8, 9, 10, 12, 15, 18, 19, 22, 25, 29]  # clean price points under GBP 30
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
            r['price'], r['price_approved'] = str(gbp), '0'
            cmp = f'{real:.0%} cheaper at FX {FX}' if real > 0 else f'held at the GBP {MIN_PRICE} minimum order'
            note = f'DRAFT: reference ${usd:g} -> GBP {gbp} ({cmp}); needs owner approval. '
        r['notes'] = (note + (r['notes'] or '')).strip()
        out.append(r)
    with open(out_path, 'w', newline='', encoding='utf-8') as f:
        w = csv.DictWriter(f, fieldnames=list(rows[0].keys()))
        w.writeheader()
        w.writerows(out)
    return rows, out

if __name__ == '__main__':
    src, dst = sys.argv[1], sys.argv[2]
    a, b = build(src, dst)
    print(f'{len(b)} rows written to {dst}')
