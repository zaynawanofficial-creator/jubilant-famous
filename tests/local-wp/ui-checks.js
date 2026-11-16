const { chromium } = require('playwright');
(async () => {
  const b = await chromium.launch(); const out = process.argv[2]; const log = (n, ok, x = '') => console.log((ok ? 'PASS ' : 'FAIL ') + n + (x ? ' — ' + x : ''));
  // ---- phone: drawer
  let ctx = await b.newContext({ viewport: { width: 390, height: 844 }, deviceScaleFactor: 2 }); let p = await ctx.newPage();
  await p.goto('http://127.0.0.1:8899/services/local-seo/', { waitUntil: 'networkidle' });
  const hdr = await p.evaluate(() => ({ nav: getComputedStyle(document.querySelector('.ifm-nav')).display, burger: !!document.querySelector('.ifm-burger') && document.querySelector('.ifm-burger').offsetParent !== null, cta: document.querySelector('.ifm-header__cta') ? getComputedStyle(document.querySelector('.ifm-header__cta')).display : 'none' }));
  log('phone header: desktop menu hidden, Menu button shown, header CTA hidden', hdr.nav === 'none' && hdr.burger && hdr.cta === 'none', JSON.stringify(hdr));
  await p.focus('.ifm-burger'); await p.keyboard.press('Enter'); await p.waitForTimeout(400);
  const open = await p.evaluate(() => { const d = document.getElementById('ifm-drawer'); const r = d.querySelector('.ifm-drawer__panel').getBoundingClientRect(); return { hidden: d.hidden, h: Math.round(r.height), links: d.querySelectorAll('.ifm-drawer__link').length, focus: document.activeElement.className, first: d.querySelector('.ifm-drawer__link').textContent.trim() }; });
  log('drawer opens with Enter, fills the screen height, lists every menu item, Home first', !open.hidden && open.h > 700 && open.links === 7 && open.first === 'Home', JSON.stringify(open));
  log('focus moves into the drawer', /drawer/.test(open.focus), open.focus);
  await p.screenshot({ path: out + '/menu-390.png' });
  for (let i = 0; i < 25; i++) await p.keyboard.press('Tab');
  log('focus stays trapped in the drawer after 25 Tabs', await p.evaluate(() => !!document.activeElement.closest('#ifm-drawer')));
  await p.keyboard.press('Escape'); await p.waitForTimeout(300);
  log('Escape closes the drawer and returns focus to the Menu button', await p.evaluate(() => document.getElementById('ifm-drawer').hidden && document.activeElement.classList.contains('ifm-burger')));
  // ---- sticky bar
  await p.evaluate(() => window.scrollTo(0, document.querySelector('.ifm-whatis').getBoundingClientRect().top + scrollY)); await p.waitForTimeout(500);
  const sb1 = await p.evaluate(() => document.querySelector('.ifm-stickybar').classList.contains('is-on'));
  await p.evaluate(() => document.getElementById('packages').scrollIntoView()); await p.waitForTimeout(500);
  const sb2 = await p.evaluate(() => document.querySelector('.ifm-stickybar').classList.contains('is-on'));
  log('sticky "Choose package" bar shows below the hero and hides on the packages section', sb1 && !sb2, `below=${sb1} onPackages=${sb2}`);
  await p.evaluate(() => window.scrollTo(0, document.querySelector('.ifm-whatis').getBoundingClientRect().top + scrollY)); await p.waitForTimeout(500);
  await p.screenshot({ path: out + '/stickybar-390.png' });
  // ---- picker keyboard
  await p.evaluate(() => document.getElementById('packages').scrollIntoView()); await p.focus('.ifm-pk__tier[aria-checked="true"]');
  const before = await p.evaluate(() => document.querySelector('.ifm-pk__panel:not([hidden]) .ifm-pk__title').textContent);
  await p.keyboard.press('ArrowDown'); await p.waitForTimeout(150);
  const after = await p.evaluate(() => ({ t: document.querySelector('.ifm-pk__panel:not([hidden]) .ifm-pk__title').textContent, checked: document.activeElement.getAttribute('aria-checked'), n: document.querySelectorAll('.ifm-pk__panel:not([hidden])').length }));
  log('picker: Arrow Down selects the next tier and swaps the panel', after.t !== before && after.checked === 'true' && after.n === 1, `${before} → ${after.t}`);
  await p.evaluate(() => document.querySelector('.ifm-pkcmp summary').click()); await p.waitForTimeout(200);
  await p.locator('.ifm-pkcmp').screenshot({ path: out + '/compare-390.png' });
  log('comparison table: one row per package, no sideways scroll', await p.evaluate(() => document.querySelectorAll('.ifm-pkcmp tbody tr').length === 4 && document.documentElement.scrollWidth <= innerWidth));
  await ctx.close();
  // ---- desktop
  ctx = await b.newContext({ viewport: { width: 1440, height: 900 } }); p = await ctx.newPage();
  await p.goto('http://127.0.0.1:8899/', { waitUntil: 'networkidle' });
  const d = await p.evaluate(() => ({ nav: getComputedStyle(document.querySelector('.ifm-nav')).display, burger: document.querySelector('.ifm-burger').offsetParent !== null, pos: getComputedStyle(document.querySelector('header.wp-block-template-part')).position, bf: getComputedStyle(document.querySelector('.ifm-header')).backdropFilter, fonts: [...document.fonts].filter(f => f.status === 'loaded').map(f => f.family.replace(/"/g, '')).filter((v, i, a) => a.indexOf(v) === i).join(','), h1: getComputedStyle(document.querySelector('h1')).fontFamily.split(',')[0], body: getComputedStyle(document.body).fontFamily.split(',')[0], btn: getComputedStyle(document.querySelector('.ifm-hero .wp-block-button__link')).backgroundColor, js: performance.getEntriesByType('resource').filter(r => /themes\/iamfamous|plugins\/iamfamous-core/.test(r.name) && /\.js/.test(r.name)).reduce((s, r) => s + r.decodedBodySize, 0) }));
  log('desktop header: full menu, no Menu button, sticky wrapper, no blur on the header', d.nav !== 'none' && !d.burger && d.pos === 'sticky' && (d.bf === 'none' || !d.bf), JSON.stringify({ pos: d.pos, bf: d.bf }));
  log('brand fonts loaded: Bricolage Grotesque headings, Figtree body', /Bricolage/.test(d.fonts) && /Figtree/.test(d.fonts) && /Bricolage/.test(d.h1) && /Figtree/.test(d.body), d.fonts);
  log('primary button is Famous Red', d.btn === 'rgb(225, 29, 46)', d.btn);
  log('own JavaScript under 30 KB', d.js > 0 && d.js < 30000, (d.js / 1024).toFixed(1) + ' KB');
  await p.goto('http://127.0.0.1:8899/cart/', { waitUntil: 'networkidle' }); await p.screenshot({ path: out + '/cart-template-1440.png', fullPage: true });
  const c = await p.evaluate(() => ({ bg: getComputedStyle(document.querySelector('.ifm-shop-hero')).backgroundImage.slice(0, 40), h1: getComputedStyle(document.querySelector('.ifm-shop-hero h1')).color }));
  log('cart hero is the dark plum band with a white heading', /radial-gradient/.test(c.bg) && c.h1 === 'rgb(255, 255, 255)', JSON.stringify(c));
  await ctx.close();
  ctx = await b.newContext({ viewport: { width: 390, height: 844 } }); p = await ctx.newPage();
  await p.goto('http://127.0.0.1:8899/checkout/', { waitUntil: 'networkidle' }); await p.screenshot({ path: out + '/checkout-template-390.png', fullPage: true });
  log('checkout template: no sideways scroll at 390', await p.evaluate(() => document.documentElement.scrollWidth <= innerWidth));
  await b.close();
})();
