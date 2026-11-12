// Validates every template, template part and pattern with WordPress's own block parser and save() functions.
// A block is "invalid" when its saved markup differs from what core would save (the editor would show a warning).
// Setup (once, anywhere outside the repo):  npm i @wordpress/blocks @wordpress/block-library jsdom jsdom-global
// Run:  NODE_PATH=/path/to/node_modules node tests/validate-blocks.js
require('jsdom-global')(undefined, { url: 'http://localhost/' });
global.MutationObserver = window.MutationObserver;
global.requestAnimationFrame = cb => setTimeout(cb, 0);
global.cancelAnimationFrame = id => clearTimeout(id);
if (!window.matchMedia) { window.matchMedia = () => ({ matches: false, addListener() {}, removeListener() {}, addEventListener() {}, removeEventListener() {} }); }
global.matchMedia = window.matchMedia;
window.atob = global.atob = s => Buffer.from(String(s), 'base64').toString('binary'); // jsdom's atob rejects some inline assets
global.ResizeObserver = global.ResizeObserver || class { observe() {} unobserve() {} disconnect() {} };
global.IntersectionObserver = global.IntersectionObserver || class { observe() {} unobserve() {} disconnect() {} };
const fs = require('fs'); const path = require('path'); const { execFileSync } = require('child_process');
const { parse, validateBlock, getBlockType } = require('@wordpress/blocks');
const { registerCoreBlocks } = require('@wordpress/block-library');
const quiet = console.warn; console.warn = () => {}; console.error = () => {}; console.info = () => {};
registerCoreBlocks();
const THEME = path.resolve(__dirname, '..', 'wp-content', 'themes', 'iamfamous');
const files = [];
for (const dir of ['templates', 'parts', 'patterns']) {
  for (const f of fs.readdirSync(path.join(THEME, dir)).sort()) { if (/\.(html|php)$/.test(f)) files.push(path.join(dir, f)); }
}
const stub = "function esc_url($s){return $s;} function get_theme_file_uri($f){return 'https://example.test/wp-content/themes/iamfamous/'.$f;}";
let blocks = 0, bad = 0, raw = 0, unknown = {};
const walk = (list, file) => {
  for (const b of list) {
    if (!b.name || b.name === 'core/freeform' && !b.originalContent.trim()) continue;
    blocks++;
    if (b.name === 'core/missing') { const n = b.attributes.originalName; unknown[n] = (unknown[n] || 0) + 1; continue; }
    if (b.name === 'core/freeform') { bad++; console.log(`INVALID ${file}: loose HTML outside a block: ${b.originalContent.trim().slice(0, 80)}`); continue; }
    // Custom HTML blocks hold raw markup and cannot be "invalid" in the editor; jsdom's HTML tokenizer mis-compares inline SVG, so they are counted but not compared.
    if (b.name === 'core/html') { raw++; continue; }
    const type = getBlockType(b.name);
    const [ok, issues] = validateBlock(b, type);
    if (!ok) { bad++; console.log(`INVALID ${file}: ${b.name}\n  ${(issues || []).map(i => String(i.args ? i.args.join(' ') : i)).join('\n  ').slice(0, 600)}`); }
    walk(b.innerBlocks || [], file);
  }
};
for (const f of files) {
  let src = fs.readFileSync(path.join(THEME, f), 'utf8');
  if (f.endsWith('.php')) { src = execFileSync('php', ['-r', stub + " include $argv[1];", path.join(THEME, f)], { encoding: 'utf8' }); }
  walk(parse(src), f);
}
const un = Object.entries(unknown).map(([k, v]) => `${k}×${v}`).join(', ');
process.stdout.write(`${files.length} files, ${blocks} blocks, ${bad} invalid, ${raw} Custom HTML blocks not compared${un ? `\nNot checked (blocks from plugins, not registered here): ${un}` : ''}\n`);
process.exit(bad ? 1 : 0);
