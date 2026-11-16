#!/usr/bin/env bash
# Builds dist/iamfamous-theme.zip (Appearance → Themes → Add New → Upload Theme).
# Regenerates illustrations and page patterns first so the zip always matches tools/*.py.
set -euo pipefail
cd "$(dirname "$0")/.."
python3 tools/build_art.py >/dev/null
python3 tools/build_patterns.py >/dev/null
for f in $(find wp-content/themes/iamfamous -name '*.php'); do php -l "$f" >/dev/null; done
python3 -c "import json;json.load(open('wp-content/themes/iamfamous/theme.json'))"
mkdir -p dist && rm -f dist/iamfamous-theme.zip
(cd wp-content/themes && zip -qr ../../dist/iamfamous-theme.zip iamfamous -x '*.DS_Store' -x '*/_scene*' -x '*/_cover*')
echo "Built dist/iamfamous-theme.zip"
