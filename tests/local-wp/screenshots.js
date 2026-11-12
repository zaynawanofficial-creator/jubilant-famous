// usage: node shot.js <outdir> <width[,width]> <path=name> ...
const { chromium } = require('playwright');
(async () => {
  const [,, out, widths, ...pages] = process.argv;
  const b = await chromium.launch();
  for (const w of widths.split(',').map(Number)) {
    const ctx = await b.newContext({ viewport: { width: w, height: 900 }, deviceScaleFactor: 1 });
    const p = await ctx.newPage();
    const errs = []; p.on('pageerror', e => errs.push(e.message)); p.on('response', r => { if (r.status() >= 400) errs.push(r.status() + ' ' + r.url()); });
    for (const spec of pages) {
      const [path, name] = spec.split('=');
      await p.goto('http://127.0.0.1:8899' + path, { waitUntil: 'networkidle' });
      await p.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += 600) { window.scrollTo(0, y); await new Promise(r => setTimeout(r, 150)); } window.scrollTo(0, 0); });
      await p.waitForTimeout(300);
      const ov = await p.evaluate(() => ({ sw: document.documentElement.scrollWidth, cw: document.documentElement.clientWidth, h: document.body.scrollHeight }));
      await p.screenshot({ path: `${out}/${name}-${w}.png`, fullPage: true });
      console.log(`${name}@${w}: scrollWidth ${ov.sw}/${ov.cw}${ov.sw > ov.cw ? '  <-- OVERFLOW' : ''} height ${ov.h}${errs.length ? ' errors: ' + [...new Set(errs)].join(' | ') : ''}`);
      errs.length = 0;
    }
    await ctx.close();
  }
  await b.close();
})();
