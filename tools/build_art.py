"""Builds the theme's illustrations.

  1. icon-*.svg      small line icons (red on a rose tile), written here.
  2. *.webp scenes   labelled UI scenes rendered from HTML by tools/build_art.js (Playwright), then converted to WebP here.
Run: python3 tools/build_art.py
Scenes use the brand fonts and palette (BRAND.md). All business names and figures in them are marked as examples.
"""
import glob, os, subprocess
from PIL import Image

HERE = os.path.dirname(os.path.abspath(__file__))
IMG = os.path.join(HERE, '..', 'wp-content', 'themes', 'iamfamous', 'assets', 'images')
os.makedirs(IMG, exist_ok=True)

S = 'fill="none" stroke="#B5122B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"'
ICONS = {
    'local': '<path d="M24 37s-9-8.4-9-15a9 9 0 0 1 18 0c0 6.6-9 15-9 15z"/><circle cx="24" cy="22" r="3.2"/>',
    'webdev': '<rect x="12" y="14" width="24" height="17" rx="2.5"/><path d="M12 19h24M20 36h8M24 31v5"/>',
    'social': '<path d="M14 16h20a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H23l-6 5v-5h-3a2 2 0 0 1-2-2V18a2 2 0 0 1 2-2z"/><path d="M24 26.5l-3.2-3a2 2 0 0 1 2.9-2.8l.3.3.3-.3a2 2 0 0 1 2.9 2.800z"/>',
    'pound': '<circle cx="24" cy="24" r="12"/><path d="M28 19.500a3.500 3.500 0 0 0-6.500 1.800V29h-2m0 0h8.500M19.500 24.500h6"/>',
    'scope': '<rect x="15" y="13" width="18" height="23" rx="2.5"/><path d="M20 13v-1.500h8V13M19.500 21l1.800 1.800 3-3.300M19.500 28.500l1.800 1.800 3-3.300M27.500 21.500h2M27.500 29h2"/>',
    'report': '<path d="M16 12h11l6 6v18H16z"/><path d="M27 12v6h6M20 31v-4M24 31v-7M28 31v-2.500"/>',
    'shield': '<path d="M24 12l10 3.500v7c0 6.500-4.200 11-10 13.500-5.800-2.500-10-7-10-13.500v-7z"/><path d="M19.500 24l3.200 3.200 5.800-6"/>',
    'chart': '<path d="M13 35h22M17 35V25M24 35V17M31 35V21"/><path d="M14 21l6-5 5 3 8-7"/>',
    'link': '<path d="M21.500 26.500a5 5 0 0 0 7 0l4-4a5 5 0 0 0-7-7l-1.500 1.500"/><path d="M26.500 21.500a5 5 0 0 0-7 0l-4 4a5 5 0 0 0 7 7l1.500-1.500"/>',
    'audit': '<circle cx="22" cy="22" r="8"/><path d="M28 28l7 7M19 22l2.200 2.200 4-4.200"/>',
    'wrench': '<path d="M31.500 14a6 6 0 0 0-7.800 7.800L13.500 32a2.300 2.300 0 0 0 3.300 3.300l10.200-10.200A6 6 0 0 0 34.800 17.300l-3.800 3.800-3-.800-.800-3z"/>',
    'page': '<rect x="14" y="12" width="20" height="24" rx="2.500"/><path d="M18.500 18h11M18.500 23h11M18.500 28h6"/>',
    'cart': '<path d="M12 14h3.500l3 14h13l2.500-10H17"/><circle cx="20.500" cy="33" r="1.800"/><circle cx="30" cy="33" r="1.800"/>',
    'spark': '<path d="M24 12l2.600 7.400L34 22l-7.400 2.600L24 32l-2.600-7.400L14 22l7.400-2.600z"/><path d="M33 30v5M30.500 32.500h5"/>',
    'label': '<path d="M13 14h10l12 12-10 10-12-12z"/><circle cx="19" cy="20" r="1.800"/>',
    'chat': '<path d="M14 15h20a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H24l-6 5v-5h-4a2 2 0 0 1-2-2V17a2 2 0 0 1 2-2z"/><path d="M18.500 21h11M18.500 25h7"/>',
    'move': '<rect x="11" y="15" width="11" height="14" rx="2"/><rect x="28" y="19" width="9" height="14" rx="2"/><path d="M19 35h7m0 0l-2.500-2.500M26 35l-2.500 2.500"/>',
    'target': '<circle cx="24" cy="24" r="12"/><circle cx="24" cy="24" r="7"/><circle cx="24" cy="24" r="2"/>',
    'megaphone': '<path d="M14 21v6h4l10 6V15l-10 6z"/><path d="M32 20.500a5 5 0 0 1 0 7M19 27l1.500 7h3"/>',
    'video': '<rect x="12" y="15" width="17" height="18" rx="3"/><path d="M29 22l7-4v12l-7-4z"/>',
    'pen': '<path d="M14 34l1.500-6.500L30 13l5 5-14.500 14.500z"/><path d="M26.500 16.500l5 5"/>',
    'palette': '<path d="M24 12a12 12 0 1 0 0 24c2 0 2.800-1.400 2-3-.900-1.800.300-3.500 2.500-3.500H31a5 5 0 0 0 5-5c0-7-5.400-12.500-12-12.500z"/><circle cx="18.500" cy="22" r="1.400"/><circle cx="23" cy="17.500" r="1.400"/><circle cx="29" cy="19.500" r="1.400"/>',
    'calendar': '<rect x="12" y="14" width="24" height="22" rx="3"/><path d="M12 20h24M18 11v5M30 11v5M17.500 25.500h3M22.500 25.500h3M27.500 25.500h3M17.500 30.500h3M22.500 30.500h3"/>',
}
for name, body in ICONS.items():
    svg = (f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="48" height="48" aria-hidden="true">'
           f'<rect width="48" height="48" rx="13" fill="#FFF1F1"/><g {S}>{body}</g></svg>\n')
    with open(os.path.join(IMG, f'icon-{name}.svg'), 'w') as f:
        f.write(svg)
print(len(ICONS), 'icons')

# Round 2 scenes: data lives in scenes_round2.py; hand it to the renderer as JSON.
import json, sys
sys.path.insert(0, HERE)
from scenes_round2 import SCENES as ROUND2
with open(os.path.join(HERE, '_scenes.json'), 'w') as f:
    from pages_round3 import COVERS
    json.dump({'scenes': ROUND2, 'covers': COVERS}, f)
subprocess.run(['node', os.path.join(HERE, 'build_art.js')], check=True)
os.remove(os.path.join(HERE, '_scenes.json'))
n = 0
os.makedirs(os.path.join(IMG, 'covers'), exist_ok=True)
for png in glob.glob(os.path.join(IMG, '_cover-*.png')):
    Image.open(png).convert('RGB').save(os.path.join(IMG, 'covers', os.path.basename(png)[len('_cover-'):-4] + '.webp'), 'WEBP', quality=86, method=4)
    os.remove(png)
for png in glob.glob(os.path.join(IMG, '_scene-*.png')):
    out = os.path.join(IMG, os.path.basename(png)[len('_scene-'):-4] + '.webp')
    Image.open(png).save(out, 'WEBP', quality=88, method=4)
    os.remove(png)
    n += 1
print(n, 'scenes')
