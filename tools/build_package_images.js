// Renders one 1200x900 visual per package from SERVICE_CATALOG.csv into
// wp-content/plugins/iamfamous-core/data/images/{sku}.png (converted to WebP by build_package_images.py).
// The importer attaches {sku}.webp to each product. Prices are never drawn on the image.
// Run: node tools/build_package_images.js && python3 tools/build_package_images.py
const { chromium } = require('playwright');
const fs = require('fs'); const path = require('path');
const ROOT = path.resolve(__dirname, '..');
const T = ROOT + '/wp-content/themes/iamfamous/assets';
const OUT = ROOT + '/wp-content/plugins/iamfamous-core/data/images';
fs.mkdirSync(OUT, { recursive: true });
const csv = fs.readFileSync(ROOT + '/SERVICE_CATALOG.csv', 'utf8').trim().split('\n');
const head = csv.shift().split(',');
const parse = l => { const o = []; let cur = '', q = false; for (const ch of l) { if (ch === '"') q = !q; else if (ch === ',' && !q) { o.push(cur); cur = ''; } else cur += ch; } o.push(cur); return Object.fromEntries(head.map((h, i) => [h, o[i]])); };
const ART = { 'Local SEO': 'art-local', 'Website Development': 'art-webdev', 'Social Media Management': 'art-social' };
const ICON = { 'Off-Page SEO': 'link', 'SEO Audit': 'audit', 'Technical SEO': 'wrench', 'Website Maintenance': 'wrench', 'Content Writing': 'pen', 'Graphic Design': 'palette', 'Analytics and Tracking': 'chart', 'Email Marketing': 'chat',
  'Marketing Automation': 'spark', 'Monthly SEO': 'chart', 'SEO Consultancy': 'chat', 'Ecommerce SEO': 'cart', 'On-Page SEO': 'page', 'White Label SEO': 'label', 'AI SEO': 'spark', 'Website Migration': 'move',
  'Ecommerce Website Development': 'cart', 'PPC Management': 'target', 'Facebook Ads': 'megaphone', 'TikTok Ads': 'video' };
const esc = s => s.replace(/&/g, '&amp;').replace(/</g, '&lt;');
const logo = fs.readFileSync(T + '/images/logo-on-dark.svg', 'utf8');
(async () => {
  const b = await chromium.launch(); const p = await b.newPage({ viewport: { width: 1200, height: 900 } });
  const tmp = path.join(OUT, '_tpl.html'); let n = 0;
  for (const line of csv) {
    const r = parse(line);
    const countable = r.quantity && r.unit && !['service', 'project', 'scope', 'site', 'month', 'report', 'call', 'plan', 'guest post', 'press release'].includes(r.unit);
    const dr = (r.package_title.match(/DR \d+\+/) || [])[0];
    let big, sub;
    if (dr) { big = `<div class="qty">${dr.replace(' ', '&nbsp;')}<span>Guest post</span></div>`; sub = 'One article on a real website'; }
    else if (countable) { big = `<div class="qty">${Number(r.quantity).toLocaleString('en-GB')}<span>${esc(r.unit)}</span></div>`; sub = esc(r.package_title); }
    else { big = `<div class="name${r.package_title.length > 16 ? ' long' : ''}">${esc(r.package_title)}</div>`; sub = esc(r.group); }
    const side = ART[r.service]
      ? `<img class="art" src="file://${T}/images/${ART[r.service]}.webp">`
      : `<div class="tile"><img src="file://${T}/images/icon-${ICON[r.service] || 'spark'}.svg"></div>`;
    const html = `<!doctype html><html><head><meta charset="utf-8"><style>
      @font-face{font-family:B;src:url(file://${T}/fonts/bricolage-grotesque-latin-wght-normal.woff2);font-weight:200 800}
      @font-face{font-family:F;src:url(file://${T}/fonts/figtree-latin-wght-normal.woff2);font-weight:300 900}
      body{margin:0;width:1200px;height:900px;overflow:hidden;font-family:F}
      .c{position:relative;width:1200px;height:900px;color:#fff;background:radial-gradient(900px 480px at 88% 6%,rgba(225,29,46,.42),transparent 62%),radial-gradient(620px 320px at 8% 108%,rgba(255,176,32,.18),transparent 60%),#160F1F}
      .eb{position:absolute;left:80px;top:84px;font:800 24px F;letter-spacing:.12em;text-transform:uppercase;color:#fff;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.18);border-radius:99px;padding:12px 22px 12px 44px}
      .eb:before{content:"";position:absolute;left:20px;top:21px;width:12px;height:12px;border-radius:50%;background:#FFB020}
      .qty{position:absolute;left:76px;top:190px;font:800 210px/1 B;letter-spacing:-.04em;white-space:nowrap}
      .qty span{display:block;font:700 48px B;letter-spacing:-.01em;color:#FF4D5A;margin-top:8px}
      .name{position:absolute;left:80px;top:200px;width:600px;font:800 92px/1.02 B;letter-spacing:-.03em}.name.long{font-size:68px;width:560px}
      .sub{position:absolute;left:80px;bottom:84px;font:600 30px F;color:#E9E3EE;width:560px}
      .art{position:absolute;right:20px;bottom:120px;width:560px}
      .tile{position:absolute;right:110px;bottom:220px;width:300px;height:300px;border-radius:72px;background:#fff;display:grid;place-items:center;box-shadow:0 40px 80px rgba(0,0,0,.45);transform:rotate(-6deg)}
      .tile img{width:230px;height:230px}
      .logo{position:absolute;right:76px;top:80px;width:266px}.logo svg{width:266px;height:40px;display:block}
    </style></head><body><div class="c"><div class="eb">${esc(r.service)}</div>${big}<div class="sub">${sub}</div>${side}<div class="logo">${logo}</div></div></body></html>`;
    fs.writeFileSync(tmp, html); await p.goto('file://' + tmp); await p.evaluate(() => document.fonts.ready);
    await p.screenshot({ path: path.join(OUT, r.package_code.toLowerCase() + '.png') }); n++;
  }
  fs.unlinkSync(tmp); await b.close(); console.log(n + ' package images');
})();
