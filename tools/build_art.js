// Renders the labelled illustration scenes (HTML + brand fonts) to PNG with a transparent background.
// tools/build_art.py runs this and converts the PNGs to WebP. Canvas: 560x420 CSS px at 2x.
const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');
const ROOT = path.resolve(__dirname, '..');
const T = ROOT + '/wp-content/themes/iamfamous/assets';
const CSS = `
@font-face{font-family:B;src:url(file://${T}/fonts/bricolage-grotesque-latin-wght-normal.woff2);font-weight:200 800}
@font-face{font-family:F;src:url(file://${T}/fonts/figtree-latin-wght-normal.woff2);font-weight:300 900}
*{box-sizing:border-box;margin:0;padding:0}
html,body{background:transparent}
body{width:560px;height:420px;position:relative;font:500 12px/1.35 F;color:#3D3647;overflow:hidden}
.c{position:absolute;background:#fff;border:1px solid #EDE6EA;border-radius:16px;box-shadow:0 18px 44px rgba(22,15,31,.22);padding:14px}
.h{font:800 15px/1.15 B;color:#160F1F;letter-spacing:-.01em}
.k{font:800 9px/1 F;letter-spacing:.12em;text-transform:uppercase;color:#B5122B}
.m{color:#6B6475;font-size:11px}
.ex{position:absolute;right:10px;top:10px;font:800 8.5px/1 F;letter-spacing:.1em;text-transform:uppercase;color:#7A4B00;background:#FFF4DB;border-radius:99px;padding:4px 7px}
.row{display:flex;align-items:center;gap:8px}
.sp{justify-content:space-between}
.pill{display:inline-flex;align-items:center;gap:4px;font:700 10px/1 F;border-radius:99px;padding:5px 8px;background:#FFF1F1;color:#B5122B;white-space:nowrap}
.pill.ok{background:#E8F6EC;color:#15803D}.pill.gold{background:#FFF4DB;color:#7A4B00}.pill.plum{background:#160F1F;color:#fff}.pill.wait{background:#F3EEF5;color:#6B6475}
.btn{display:inline-flex;align-items:center;justify-content:center;font:800 11px/1 F;color:#fff;background:#E11D2E;border-radius:9px;padding:8px 12px;box-shadow:0 6px 14px rgba(225,29,46,.28)}
.btn.o{background:#fff;color:#160F1F;border:1.5px solid #160F1F;box-shadow:none}
.star{color:#FFB020;letter-spacing:1px;font-size:12px}
.ln{height:7px;border-radius:4px;background:#EDE6EA}.ln.d{background:#D8CEDA}.ln.r{background:#FFE1E1}
.tick{width:16px;height:16px;border-radius:50%;background:#E11D2E;flex:none;position:relative}
.tick:after{content:"";position:absolute;left:5px;top:3.5px;width:4px;height:7px;border:solid #fff;border-width:0 2px 2px 0;transform:rotate(45deg)}
.n{width:22px;height:22px;border-radius:50%;background:#FFB020;color:#160F1F;font:800 12px/22px B;text-align:center;flex:none;box-shadow:0 0 0 3px rgba(255,176,32,.3)}
.call{position:absolute;display:flex;align-items:center;gap:6px;background:#160F1F;color:#fff;font:700 10.5px/1 F;border-radius:99px;padding:5px 10px 5px 5px;box-shadow:0 8px 18px rgba(22,15,31,.35);white-space:nowrap}
.call .n{width:18px;height:18px;font:800 10px/18px B;box-shadow:none}
.t{width:100%;border-collapse:collapse;font-size:10.5px}.t th{font:800 8.5px/1 F;letter-spacing:.1em;text-transform:uppercase;color:#6B6475;text-align:left;padding:0 6px 6px 0}
.t td{padding:6px 6px 6px 0;border-top:1px solid #EDE6EA;white-space:nowrap}.t td b{color:#160F1F}.u{color:#B5122B;text-decoration:underline}
.sel{border:1.5px solid #E11D2E;background:#FFF1F1;box-shadow:0 0 0 3px rgba(225,29,46,.14)}
.tier{border:1px solid #EDE6EA;border-radius:10px;padding:8px 10px;font:700 11.5px/1.2 F;color:#160F1F}
.ic{width:26px;height:26px;border-radius:8px;background:#FFF1F1;flex:none;display:grid;place-items:center;color:#B5122B;font:800 12px B}
.ph{position:absolute;background:#160F1F;border-radius:26px;padding:8px;box-shadow:0 20px 44px rgba(22,15,31,.4)}
.ph>div{background:#fff;border-radius:19px;height:100%;overflow:hidden;position:relative}
.band{background:radial-gradient(160px 90px at 85% 0,rgba(225,29,46,.5),transparent 65%),#160F1F;color:#fff}
.chip{font:800 8.5px/1 F;border-radius:5px;padding:4px 5px;color:#160F1F;background:#FFE1E1;display:block;text-align:center;white-space:nowrap}
.chip.g{background:#FFE1A6}.chip.p{background:#E6DDF0}.chip.o{background:#D6F0DD}
.bar{height:9px;border-radius:5px;background:#E11D2E}
`;
const stars = '<span class="star">★★★★★</span>';
const SCENES = {
'hero-home': `
<div class="c" style="left:6px;top:38px;width:312px;padding:12px">
  <span class="ex">Example</span>
  <div class="row" style="border:1px solid #EDE6EA;border-radius:99px;padding:7px 12px;width:210px;font-weight:600;color:#160F1F"><span style="color:#B5122B;font-weight:800">⌕</span> plumber near me</div>
  <div style="height:58px;border-radius:10px;margin:10px 0;background:linear-gradient(135deg,#FFF1F1,#FFF8F4);position:relative;overflow:hidden">
    <i style="position:absolute;left:-10px;top:26px;width:340px;height:5px;background:#fff;transform:rotate(-6deg)"></i><i style="position:absolute;left:120px;top:-10px;width:5px;height:90px;background:#fff;transform:rotate(18deg)"></i>
    <b style="position:absolute;left:62px;top:14px;width:14px;height:14px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);background:#D8CEDA"></b>
    <b style="position:absolute;left:150px;top:18px;width:20px;height:20px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);background:#E11D2E;box-shadow:0 0 0 5px rgba(225,29,46,.2)"></b>
    <b style="position:absolute;left:236px;top:26px;width:14px;height:14px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);background:#D8CEDA"></b></div>
  <div class="tier" style="margin-bottom:6px;opacity:.7"><div class="ln d" style="width:120px;margin-bottom:6px"></div><div class="ln" style="width:170px"></div></div>
  <div class="tier sel" style="margin-bottom:6px"><div class="row sp"><span>Your business</span><span class="pill ok">Open now</span></div>
    <div class="row" style="margin-top:4px;font-weight:500">${stars}<span class="m">Plumber · Leeds</span></div></div>
  <div class="tier" style="opacity:.7"><div class="ln d" style="width:100px;margin-bottom:6px"></div><div class="ln" style="width:150px"></div></div>
</div>
<div class="call" style="left:20px;top:6px"><span class="n">1</span>Found nearby</div>
<div class="ph" style="left:340px;top:44px;width:172px;height:300px"><div>
  <div class="band" style="padding:14px 12px 16px"><div class="row sp"><b style="font:800 10px B">Your business</b><span style="font-size:13px">≡</span></div>
    <div style="font:800 17px/1.08 B;margin:14px 0 8px;letter-spacing:-.02em">Boiler repairs in Leeds</div><div class="m" style="color:#E9E3EE;font-size:9.5px;margin-bottom:10px">Same-week visits. Fixed quotes.</div><span class="btn" style="width:100%">Call now</span></div>
  <div style="padding:12px"><div class="k" style="margin-bottom:8px">Services</div><div class="row" style="margin-bottom:8px"><span class="ic">1</span><div style="flex:1"><div class="ln d" style="width:80%;margin-bottom:5px"></div><div class="ln" style="width:55%"></div></div></div>
    <div class="row"><span class="ic">2</span><div style="flex:1"><div class="ln d" style="width:70%;margin-bottom:5px"></div><div class="ln" style="width:50%"></div></div></div>
    <div style="border:1px solid #EDE6EA;border-radius:9px;padding:8px;margin-top:12px"><div class="k" style="margin-bottom:6px">Get a quote</div><div class="ln" style="margin-bottom:5px"></div><div class="ln" style="width:70%"></div></div></div>
</div></div>
<div class="call" style="left:392px;top:12px"><span class="n">2</span>A site that sells</div>
<div class="c" style="left:60px;top:326px;width:330px;padding:12px">
  <div class="row sp" style="margin-bottom:8px"><span class="h" style="font-size:13px">This week's posts</span><span class="pill gold">Scheduled</span></div>
  <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:6px;text-align:center;font:700 9px F;color:#6B6475">
    <div>Mon<span class="chip" style="margin-top:4px">Tip</span></div><div>Tue<span class="chip" style="margin-top:4px;background:#F6F1F6;color:#9A90A6">–</span></div><div>Wed<span class="chip g" style="margin-top:4px">Offer</span></div><div>Thu<span class="chip" style="margin-top:4px;background:#F6F1F6;color:#9A90A6">–</span></div><div>Fri<span class="chip p" style="margin-top:4px">Review</span></div></div>
</div>
<div class="call" style="left:236px;top:310px"><span class="n">3</span>Active on social</div>`,

'art-local': `
<div class="c" style="left:10px;top:40px;width:300px">
  <span class="ex">Example</span><div class="k" style="margin-bottom:6px">Google Business Profile</div>
  <div class="h" style="font-size:19px">Your Business Name</div>
  <div class="row" style="margin:5px 0 2px">${stars}<span class="m">Google reviews</span></div>
  <div class="m">Plumber · Leeds · <b style="color:#15803D">Open</b> · Closes 6 pm</div>
  <div class="row" style="margin:12px 0"><span class="btn">Call</span><span class="btn o">Directions</span><span class="btn o">Website</span></div>
  <div style="border-top:1px solid #EDE6EA;padding-top:10px;display:grid;grid-template-columns:1fr 1fr;gap:8px 10px;font-weight:600;color:#160F1F;font-size:11px">
    <div class="row"><i class="tick"></i>Categories</div><div class="row"><i class="tick"></i>Opening hours</div><div class="row"><i class="tick"></i>Services listed</div><div class="row"><i class="tick"></i>Service areas</div><div class="row"><i class="tick"></i>Description</div><div class="row"><i class="tick"></i>Weekly posts</div></div>
</div>
<div class="call" style="left:24px;top:6px"><span class="n">1</span>Profile set up properly</div>
<div class="c" style="left:236px;top:226px;width:314px;padding:12px 14px">
  <div class="row sp" style="margin-bottom:8px"><span class="h" style="font-size:13.5px">Directory listings</span><span class="m">same name · address · phone</span></div>
  <table class="t"><tr><td><b>Yell</b></td><td class="u">live link</td><td style="text-align:right"><span class="pill ok">Live</span></td></tr>
  <tr><td><b>Bing Places</b></td><td class="u">live link</td><td style="text-align:right"><span class="pill ok">Live</span></td></tr>
  <tr><td><b>Apple Maps</b></td><td class="u">live link</td><td style="text-align:right"><span class="pill ok">Live</span></td></tr>
  <tr><td><b>Thomson Local</b></td><td class="m">submitted</td><td style="text-align:right"><span class="pill wait">Pending</span></td></tr></table>
</div>
<div class="call" style="left:350px;top:194px"><span class="n">2</span>Listed where people look</div>`,

'art-webdev': `
<div class="c" style="left:8px;top:42px;width:400px;height:290px;padding:0;overflow:hidden">
  <div class="row" style="padding:8px 12px;border-bottom:1px solid #EDE6EA;gap:5px"><i style="width:8px;height:8px;border-radius:50%;background:#FFE1E1"></i><i style="width:8px;height:8px;border-radius:50%;background:#FFE1A6"></i><i style="width:8px;height:8px;border-radius:50%;background:#EDE6EA"></i><span class="m" style="margin-left:8px;border:1px solid #EDE6EA;border-radius:99px;padding:2px 10px;font-size:9.5px">yourbusiness.co.uk</span></div>
  <div class="band" style="padding:16px 18px 20px"><div class="row sp"><b style="font:800 12px B">Your business</b><span class="row" style="gap:12px;font-size:9.5px;color:#E9E3EE">Services<span>Prices</span><span>Contact</span></span></div>
    <div style="font:800 25px/1.05 B;letter-spacing:-.02em;margin:20px 0 8px;width:230px">Garden design in Bristol</div><div style="color:#E9E3EE;font-size:10.5px;margin-bottom:12px;width:220px">Patios, planting and lawns, with a fixed quote before we start.</div><div class="row"><span class="btn">Get a free quote</span><span class="btn o" style="background:transparent;color:#fff;border-color:rgba(255,255,255,.4)">See our work</span></div></div>
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;padding:14px 18px">${[1,2,3].map(()=>'<div style="border:1px solid #EDE6EA;border-radius:10px;padding:9px"><div class="ln r" style="height:26px;margin-bottom:7px"></div><div class="ln d" style="width:70%;margin-bottom:5px"></div><div class="ln" style="width:90%"></div></div>').join('')}</div>
</div>
<span class="ex" style="left:340px;right:auto;top:50px">Example</span>
<div class="ph" style="left:392px;top:120px;width:150px;height:280px"><div>
  <div class="band" style="padding:12px 10px 14px"><b style="font:800 9.5px B">Your business</b><div style="font:800 15px/1.08 B;margin:12px 0 8px">Garden design in Bristol</div><span class="btn" style="width:100%;padding:7px">Get a free quote</span></div>
  <div style="padding:10px"><div class="k" style="margin-bottom:7px">Send an enquiry</div><div style="border:1px solid #D8CEDA;border-radius:7px;height:20px;margin-bottom:6px"></div><div style="border:1px solid #D8CEDA;border-radius:7px;height:20px;margin-bottom:6px"></div><div style="border:1px solid #D8CEDA;border-radius:7px;height:36px;margin-bottom:8px"></div><span class="btn" style="width:100%;padding:7px;background:#160F1F;box-shadow:none">Send</span></div>
</div></div>
<div class="call" style="left:22px;top:8px"><span class="n">1</span>Says what you do and where</div>
<div class="call" style="left:236px;top:236px"><span class="n">2</span>One clear button</div>
<div class="call" style="left:196px;top:386px"><span class="n">3</span>Enquiry form that reaches you</div>`,

'art-social': `
<div class="c" style="left:8px;top:40px;width:330px">
  <span class="ex">Example</span><div class="k" style="margin-bottom:5px">Content calendar</div><div class="h" style="font-size:17px;margin-bottom:10px">Your month, planned</div>
  <div style="display:grid;grid-template-columns:34px repeat(5,1fr);gap:6px;align-items:center;font:700 9px F;color:#6B6475;text-align:center">
    <span></span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span>
    ${[['Wk 1',['Tip','','Offer','','Review']],['Wk 2',['Behind scenes','','Tip','','Before / after']],['Wk 3',['Tip','','Offer','','FAQ']],['Wk 4',['Meet the team','','Tip','','Review']]].map(([w,d])=>`<span style="text-align:left">${w}</span>`+d.map(x=>x?`<span class="chip ${x==='Offer'?'g':x==='Review'||x==='FAQ'?'p':x==='Tip'?'':'o'}" style="padding:7px 2px;font-size:7.8px">${x}</span>`:'<span style="height:22px;border-radius:5px;background:#F8F3F6"></span>').join('')).join('')}
  </div>
  <div class="row" style="margin-top:12px;flex-wrap:wrap;gap:6px"><span class="chip" style="display:inline-block">Tip</span><span class="chip g" style="display:inline-block">Offer</span><span class="chip p" style="display:inline-block">Proof</span><span class="chip o" style="display:inline-block">Your story</span></div>
</div>
<div class="call" style="left:22px;top:8px"><span class="n">1</span>Planned a month ahead</div>
<div class="c" style="left:346px;top:128px;width:206px;padding:10px">
  <div class="row" style="margin-bottom:8px"><span class="ic" style="border-radius:50%;background:#E11D2E;color:#fff">Y</span><div><b style="color:#160F1F;font-size:11px">yourbusiness</b><div class="m" style="font-size:9px">Scheduled · Wed 9:00</div></div></div>
  <div class="band" style="border-radius:10px;height:112px;padding:14px;display:flex;flex-direction:column;justify-content:flex-end"><span class="pill gold" style="align-self:flex-start;margin-bottom:6px">This week only</span><div style="font:800 17px/1.05 B">Free callout with every repair</div></div>
  <div style="margin-top:9px"><div class="ln d" style="width:92%;margin-bottom:5px"></div><div class="ln" style="width:70%;margin-bottom:5px"></div><span style="color:#B5122B;font:700 9.5px F">#leeds #plumber #boilerrepair</span></div>
</div>
<div class="call" style="left:360px;top:96px"><span class="n">2</span>Designed and captioned</div>
<div class="call" style="left:120px;top:330px"><span class="n">3</span>You approve first</div>`,

'art-services': `
<div class="c" style="left:8px;top:42px;width:250px">
  <div class="k" style="margin-bottom:10px">Services</div>
  ${[['G','Get found on Google','Local SEO, audits, links'],['W','Websites','New sites, shops, fixes'],['A','Ads and social media','Google, Facebook, TikTok'],['D','Words and design','Articles, logos, graphics']].map(([i,a,b],n)=>`<div class="row tier ${n===0?'sel':''}" style="margin-bottom:8px;padding:10px"><span class="ic">${i}</span><div><div>${a}</div><div class="m" style="font-weight:500;font-size:10px">${b}</div></div></div>`).join('')}
</div>
<div class="c" style="left:262px;top:92px;width:290px">
  <span class="ex">Example</span><div class="h" style="font-size:16px">Local SEO</div><div class="m" style="margin-bottom:10px">Pick a package</div>
  <div class="tier row sp" style="margin-bottom:7px"><span>Profile optimisation</span><span class="m">3–5 days</span></div>
  <div class="tier sel row sp" style="margin-bottom:7px"><span>Starter · 20 listings</span><span class="pill gold">Most popular</span></div>
  <div class="tier row sp" style="margin-bottom:12px"><span>Growth · 40 listings</span><span class="m">10–14 days</span></div>
  <div class="row" style="flex-wrap:wrap;gap:6px 12px;margin-bottom:12px;font-size:10.5px;font-weight:600;color:#160F1F"><span class="row" style="gap:5px"><i class="tick"></i>Fixed price</span><span class="row" style="gap:5px"><i class="tick"></i>Written scope</span><span class="row" style="gap:5px"><i class="tick"></i>Live-link report</span></div>
  <span class="btn" style="width:100%;padding:11px">Choose this package →</span>
</div>
<div class="call" style="left:22px;top:12px"><span class="n">1</span>Choose a service</div>
<div class="call" style="left:330px;top:60px"><span class="n">2</span>Pick a package</div>
<div class="call" style="left:100px;top:372px"><span class="n">3</span>Order online</div>`,

'art-growth': `
<div class="c" style="left:14px;top:22px;width:532px;padding:16px">
  <span class="ex">Example</span><div class="k" style="margin-bottom:5px">Free growth check</div><div class="h" style="font-size:18px;margin-bottom:12px">Three things we would fix first</div>
  ${[['1','Google listing','Two categories missing and no services listed.','Local SEO'],['2','Website','No phone number or button on the first screen.','Website'],['3','Social media','Last post was four months ago.','Social media']].map(([n,a,b,c])=>`<div class="row" style="border:1px solid #EDE6EA;border-radius:12px;padding:11px 12px;margin-bottom:9px;gap:12px"><span class="n">${n}</span><div style="flex:1"><b style="color:#160F1F;font:800 13px B">${a}</b><div class="m">${b}</div></div><span class="pill">${c}</span></div>`).join('')}
  <div class="row sp" style="margin-top:12px"><span class="m">Sent by email within one working day</span><span class="pill plum">Free · nothing to sign</span></div>
</div>`,

'report-local': `
<div class="c" style="left:10px;top:12px;width:540px;padding:16px">
  <span class="ex">Example</span><div class="k" style="margin-bottom:5px">Delivery report</div><div class="h" style="font-size:17px;margin-bottom:12px">Local SEO Starter · listings</div>
  <table class="t"><tr><th>Directory</th><th>Live link</th><th>Details used</th><th style="text-align:right">Status</th></tr>
  ${[['Yell','yell.com/biz/…','Name · address · phone'],['Bing Places','bing.com/maps/…','Name · address · phone'],['Apple Maps','maps.apple.com/…','Name · address · phone'],['Thomson Local','thomsonlocal.com/…','Name · address · phone']].map(([a,b,c])=>`<tr><td><b>${a}</b></td><td class="u">${b}</td><td class="m">${c}</td><td style="text-align:right"><span class="pill ok">Live</span></td></tr>`).join('')}
  <tr><td class="m" colspan="4">…and 16 more rows, one for every listing in the package</td></tr></table>
</div>
<div class="c" style="left:120px;top:282px;width:420px;padding:13px 14px">
  <div class="h" style="font-size:13px;margin-bottom:8px">Google Business Profile: what changed</div>
  <div class="row" style="margin-bottom:6px"><i class="tick"></i><span><b style="color:#160F1F">Categories</b> <span class="m">1 before</span> → <b style="color:#B5122B">3 after</b></span></div>
  <div class="row" style="margin-bottom:6px"><i class="tick"></i><span><b style="color:#160F1F">Services</b> <span class="m">none listed</span> → <b style="color:#B5122B">8 listed</b></span></div>
  <div class="row"><i class="tick"></i><span><b style="color:#160F1F">Description</b> <span class="m">empty</span> → <b style="color:#B5122B">written</b></span></div>
</div>`,

'report-webdev': `
<div class="c" style="left:10px;top:14px;width:330px;padding:16px">
  <span class="ex">Example</span><div class="k" style="margin-bottom:5px">Handover checklist</div><div class="h" style="font-size:17px;margin-bottom:12px">5-Page Website</div>
  ${['Home, About, Services, Prices, Contact built','Checked on phone, tablet and desktop','Enquiry form tested to your inbox','Page titles, descriptions and sitemap set','Images compressed, caching on','Admin login created in your name'].map(x=>`<div class="row" style="padding:8px 0;border-top:1px solid #EDE6EA;font-weight:600;color:#160F1F;font-size:11.5px"><i class="tick"></i>${x}</div>`).join('')}
</div>
<div class="c" style="left:300px;top:150px;width:250px;padding:12px">
  <div class="band" style="border-radius:11px;height:128px;display:grid;place-items:center;position:relative"><span style="width:44px;height:44px;border-radius:50%;background:#E11D2E;display:grid;place-items:center;box-shadow:0 0 0 8px rgba(225,29,46,.25)"><i style="border-left:14px solid #fff;border-top:9px solid transparent;border-bottom:9px solid transparent;margin-left:4px"></i></span><span class="pill gold" style="position:absolute;left:10px;bottom:10px">Recorded on your site</span></div>
  <div class="h" style="font-size:13px;margin:10px 0 3px">Your walkthrough video</div><div class="m">How to edit text, swap photos and add a page.</div>
</div>`,

'report-social': `
<div class="c" style="left:10px;top:12px;width:296px;padding:16px">
  <span class="ex">Example</span><div class="k" style="margin-bottom:5px">Monthly report</div><div class="h" style="font-size:17px;margin-bottom:12px">What went out</div>
  <table class="t"><tr><th>Date</th><th>Post</th><th style="text-align:right">Link</th></tr>
  ${[['Mon 3','Tip: bleeding a radiator'],['Wed 5','Offer: free callout'],['Fri 7','Customer review'],['Mon 10','Behind the scenes'],['Wed 12','Tip: frozen pipes']].map(([a,b])=>`<tr><td class="m">${a}</td><td><b>${b}</b></td><td class="u" style="text-align:right">view</td></tr>`).join('')}
  <tr><td class="m" colspan="3">…one row for every post published</td></tr></table>
</div>
<div class="c" style="left:322px;top:92px;width:230px;padding:14px">
  <div class="h" style="font-size:13px;margin-bottom:3px">How each type did</div><div class="m" style="margin-bottom:10px;font-size:10px">Reach, as reported by the platform (example data)</div>
  ${[['Offers',88],['Reviews',70],['Tips',52],['Your story',40]].map(([a,w])=>`<div class="row" style="margin-bottom:8px"><span style="width:62px;font-weight:700;color:#160F1F;font-size:10.5px">${a}</span><div style="flex:1;background:#F6F1F6;border-radius:5px"><div class="bar" style="width:${w}%"></div></div></div>`).join('')}
</div>
<div class="c" style="left:330px;top:290px;width:220px;padding:12px 14px">
  <div class="h" style="font-size:13px;margin-bottom:7px">Next month</div>
  <div class="row" style="margin-bottom:5px"><i class="tick"></i><span style="font-weight:600;color:#160F1F">More offer posts</span></div><div class="row"><i class="tick"></i><span style="font-weight:600;color:#160F1F">Plan ready for your approval</span></div>
</div>`,
};
// ---- Round 2 scenes, generated from tools/scenes_round2.py (exported to _scenes.json by build_art.py).
const esc = t => String(t).replace(/&/g, '&amp;').replace(/</g, '&lt;');
const pill = st => ({ ok: 'ok', wait: 'wait', gold: 'gold' }[st] || 'wait');
const heroScene = ([k, title, sub, ticks, sideTitle, rows, calls]) => `
<div class="c" style="left:10px;top:40px;width:292px">
  <span class="ex">Example</span><div class="k" style="margin-bottom:6px">${esc(k)}</div>
  <div class="h" style="font-size:19px">${esc(title)}</div><div class="m" style="margin:3px 0 12px">${esc(sub)}</div>
  <div style="border-top:1px solid #EDE6EA;padding-top:11px;display:grid;gap:9px;font-weight:600;color:#160F1F;font-size:11.5px">${ticks.map(t => `<div class="row"><i class="tick"></i>${esc(t)}</div>`).join('')}</div>
</div>
<div class="call" style="left:24px;top:6px"><span class="n">1</span>${esc(calls[0])}</div>
<div class="c" style="left:226px;top:214px;width:324px;padding:12px 14px">
  <div class="h" style="font-size:13.5px;margin-bottom:8px">${esc(sideTitle)}</div>
  <table class="t">${rows.map(([a, b, st]) => `<tr><td style="white-space:normal;max-width:150px"><b>${esc(a)}</b></td><td style="text-align:right"><span class="pill ${pill(st)}" style="max-width:150px;white-space:normal;text-align:right;line-height:1.2">${esc(b)}</span></td></tr>`).join('')}</table>
</div>
<div class="call" style="left:330px;top:182px"><span class="n">2</span>${esc(calls[1])}</div>`;
const reportScene = ([k, title, cols, rows, foot, sideTitle, side]) => `
<div class="c" style="left:10px;top:12px;width:540px;padding:16px">
  <span class="ex">Example</span><div class="k" style="margin-bottom:5px">${esc(k)}</div><div class="h" style="font-size:17px;margin-bottom:12px">${esc(title)}</div>
  <table class="t"><tr>${cols.map((c, i) => `<th${i === 2 ? ' style="text-align:right"' : ''}>${esc(c)}</th>`).join('')}</tr>
  ${rows.map(([a, b, c]) => `<tr><td><b>${esc(a)}</b></td><td class="m">${esc(b)}</td><td style="text-align:right"><span class="pill ${/pend|paus|not |wrong|outdat|^–$/i.test(c) ? 'wait' : /high|example/i.test(c) ? 'gold' : 'ok'}">${esc(c)}</span></td></tr>`).join('')}
  <tr><td class="m" colspan="3">${esc(foot)}</td></tr></table>
</div>
<div class="c" style="left:170px;top:286px;width:370px;padding:13px 14px">
  <div class="h" style="font-size:13px;margin-bottom:8px">${esc(sideTitle)}</div>
  ${side.map(x => `<div class="row" style="margin-bottom:6px"><i class="tick"></i><span style="font-weight:600;color:#160F1F">${esc(x)}</span></div>`).join('')}
</div>`;
const data = fs.existsSync(__dirname + '/_scenes.json') ? JSON.parse(fs.readFileSync(__dirname + '/_scenes.json', 'utf8')) : {};
const extra = data.scenes || {};
const covers = data.covers || {};
for (const [slug, sc] of Object.entries(extra)) { SCENES['art-' + slug] = heroScene(sc.hero); SCENES['report-' + slug] = reportScene(sc.report); }

(async () => {
  const b = await chromium.launch();
  const p = await b.newPage({ viewport: { width: 560, height: 420 }, deviceScaleFactor: 2 });
  const tmp = `${T}/images/_scene.html`;
  for (const [name, body] of Object.entries(SCENES)) {
    fs.writeFileSync(tmp, `<!doctype html><meta charset="utf-8"><style>${CSS}</style><body>${body}</body>`);
    await p.goto('file://' + tmp);
    await p.evaluate(() => document.fonts.ready);
    await p.screenshot({ path: `${T}/images/_scene-${name}.png`, omitBackground: true });
  }
  // Guide covers: 1200x675, plum band with the guide title.
  const logo = fs.readFileSync(`${T}/images/logo-on-dark.svg`, 'utf8');
  const cp = await b.newPage({ viewport: { width: 1200, height: 675 }, deviceScaleFactor: 1 });
  for (const [slug, [kicker, title, sub]] of Object.entries(covers)) {
    fs.writeFileSync(tmp, `<!doctype html><meta charset="utf-8"><style>
      @font-face{font-family:B;src:url(file://${T}/fonts/bricolage-grotesque-latin-wght-normal.woff2);font-weight:200 800}
      @font-face{font-family:F;src:url(file://${T}/fonts/figtree-latin-wght-normal.woff2);font-weight:300 900}
      body{margin:0;width:1200px;height:675px;overflow:hidden;color:#fff;font-family:F;background:radial-gradient(900px 420px at 85% 10%,rgba(225,29,46,.42),transparent 60%),radial-gradient(600px 300px at 10% 110%,rgba(255,176,32,.18),transparent 60%),#160F1F}
      .k{position:absolute;left:80px;top:84px;font:800 22px F;letter-spacing:.12em;text-transform:uppercase;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.18);border-radius:99px;padding:12px 22px 12px 44px}
      .k:before{content:"";position:absolute;left:20px;top:19px;width:12px;height:12px;border-radius:50%;background:#FFB020}
      h1{position:absolute;left:80px;top:190px;width:900px;margin:0;font:800 84px/1.02 B;letter-spacing:-.03em}
      .s{position:absolute;left:80px;bottom:84px;font:600 30px F;color:#FF4D5A}
      .l{position:absolute;right:80px;bottom:80px;width:240px}.l svg{width:240px;height:36px;display:block}
    </style><body><div class="k">${esc(kicker)}</div><h1>${esc(title)}</h1><div class="s">${esc(sub)}</div><div class="l">${logo}</div></body>`);
    await cp.goto('file://' + tmp); await cp.evaluate(() => document.fonts.ready);
    await cp.screenshot({ path: `${T}/images/_cover-${slug}.png` });
  }
  fs.unlinkSync(tmp);
  await b.close();
  console.log(Object.keys(SCENES).length + ' scenes rendered');
})();
