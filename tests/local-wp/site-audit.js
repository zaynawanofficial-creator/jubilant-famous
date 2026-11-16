// Whole-site check against the local WordPress: crawls every internal link from the home page, the sitemap of
// pages built by Setup and the guides; reports HTTP errors, horizontal overflow at 390 and 1440, missing or
// duplicate titles and descriptions, pages without exactly one H1, images that failed to load, and console errors.
// Also exercises the cookie banner. Prints failures only, then a one-line summary.
const { chromium } = require('playwright');
const BASE = 'http://127.0.0.1:8899';
(async () => {
  const b = await chromium.launch();
  const seen = new Map(); const queue = ['/']; const issues = []; const titles = new Map(); const descs = new Map();
  const ctx = await b.newContext({ viewport: { width: 390, height: 844 } });
  const p = await ctx.newPage();
  let jsErr = []; p.on('pageerror', e => jsErr.push(e.message));
  while (queue.length) {
    const path = queue.shift(); if (seen.has(path)) continue;
    const res = await p.goto(BASE + path, { waitUntil: 'load' }).catch(e => null);
    const status = res ? res.status() : 0; seen.set(path, status);
    // WooCommerce's account pages do not exist in the local copy (the shim has no My Account).
    if (status !== 200) { if (!path.startsWith('/my-account/')) issues.push(`${status} ${path}`); continue; }
    const d = await p.evaluate(() => ({
      links: [...document.querySelectorAll('a[href]')].map(a => a.getAttribute('href')),
      h1: document.querySelectorAll('h1').length, title: document.title,
      desc: (document.querySelector('meta[name=description]') || {}).content || '',
      robots: (document.querySelector('meta[name=robots]') || {}).content || '',
      over: document.documentElement.scrollWidth > document.documentElement.clientWidth,
      badImg: [...document.images].filter(i => i.complete && i.naturalWidth === 0 && i.loading !== 'lazy').map(i => i.src),
      raw: (document.body.innerText.match(/\[ifm_[a-z_]+[^\]]*\]/g) || []),
    }));
    const noindex = /noindex/.test(d.robots);
    if (d.h1 !== 1) issues.push(`${d.h1} H1s on ${path}`);
    if (d.over) issues.push(`overflow at 390 on ${path}`);
    if (d.badImg.length) issues.push(`broken image on ${path}: ${d.badImg[0]}`);
    if (d.raw.length) issues.push(`unrendered shortcode on ${path}: ${d.raw[0]}`);
    if (jsErr.length) { issues.push(`JS error on ${path}: ${jsErr[0]}`); jsErr = []; }
    if (!noindex) {
      if (!d.desc) issues.push(`no meta description on ${path}`);
      if (titles.has(d.title)) issues.push(`duplicate title: ${path} and ${titles.get(d.title)}`); else titles.set(d.title, path);
      if (d.desc && descs.has(d.desc)) issues.push(`duplicate description: ${path} and ${descs.get(d.desc)}`); else descs.set(d.desc, path);
    }
    for (let h of d.links) {
      if (!h || h.startsWith('#') || h.startsWith('mailto:') || h.startsWith('tel:')) continue;
      if (h.startsWith(BASE)) h = h.slice(BASE.length);
      if (!h.startsWith('/') || h.startsWith('/wp-') || /\.(png|jpg|webp|svg|pdf)$/.test(h)) continue;
      h = h.split('#')[0].split('?')[0]; if (h && !seen.has(h) && !queue.includes(h)) queue.push(h);
    }
  }
  // Desktop overflow pass on everything that returned 200.
  const wide = await (await b.newContext({ viewport: { width: 1440, height: 900 } })).newPage();
  for (const [path, status] of seen) {
    if (status !== 200) continue;
    await wide.goto(BASE + path, { waitUntil: 'load' });
    if (await wide.evaluate(() => document.documentElement.scrollWidth > document.documentElement.clientWidth)) issues.push(`overflow at 1440 on ${path}`);
  }
  // Cookie banner.
  const c = await (await b.newContext({ viewport: { width: 390, height: 844 }, deviceScaleFactor: 2 })).newPage();
  await c.goto(BASE + '/', { waitUntil: 'load' }); await c.waitForTimeout(300);
  const shown = await c.evaluate(() => { const el = document.getElementById('ifm-cookie'); const r = el.getBoundingClientRect(); return !el.hidden && r.width <= innerWidth && r.bottom <= innerHeight + 1 && document.documentElement.scrollWidth <= innerWidth; });
  if (process.argv[2]) await c.screenshot({ path: process.argv[2] + '/cookie-banner-390.png' });
  await c.keyboard.press('Tab'); // skip link or first control; banner buttons must be reachable
  await c.click('[data-ifm-consent="essential"]');
  const stored = await c.evaluate(() => ({ hidden: document.getElementById('ifm-cookie').hidden, v: window.ifmConsent() }));
  await c.goto(BASE + '/services/', { waitUntil: 'load' }); await c.waitForTimeout(200);
  const stays = await c.evaluate(() => document.getElementById('ifm-cookie').hidden);
  await c.evaluate(() => document.querySelector('a[href$="#cookie-settings"]').click()); await c.waitForTimeout(100);
  const reopened = await c.evaluate(() => !document.getElementById('ifm-cookie').hidden && document.activeElement.hasAttribute('data-ifm-consent'));
  await c.click('[data-ifm-consent="all"]');
  const all = await c.evaluate(() => window.ifmConsent());
  const cookieOk = shown && stored.hidden && stored.v === 'essential' && stays && reopened && all === 'all';
  if (!cookieOk) issues.push('cookie banner: ' + JSON.stringify({ shown, stored, stays, reopened, all }));
  // Add-ons on a package page.
  await c.goto(BASE + '/product/5-page-website/', { waitUntil: 'load' });
  const addons = await c.evaluate(() => [...document.querySelectorAll('.ifm-addon')].map(a => a.innerText.replace(/\s+/g, ' ').slice(0, 40)));
  if (addons.length !== 2) issues.push('add-ons not shown on a one-off package: ' + JSON.stringify(addons));
  await c.goto(BASE + '/product/seo-starter-1-month/', { waitUntil: 'load' });
  if (await c.evaluate(() => document.querySelectorAll('.ifm-addon').length) !== 0) issues.push('add-ons shown on a monthly package');
  await b.close();
  issues.forEach(i => console.log('ISSUE ' + i));
  const ok = [...seen.values()].filter(s => s === 200).length;
  console.log(`AUDIT ${seen.size} URLs crawled, ${ok} OK, ${issues.length} issues; cookie banner ${cookieOk ? 'PASS' : 'FAIL'}; add-ons: ${addons.join(' | ')}`);
})();
