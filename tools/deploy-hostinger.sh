#!/usr/bin/env bash
# Uploads changed theme/plugin files straight into the live site through Hostinger's file server (TUS).
# Usage: tools/deploy-hostinger.sh <file-list> <upload-url> <auth-key> <rest-auth-key>
# Get the URL and keys from the Hostinger API operation hosting_files_generate-upload-url (they expire after a few hours).
# Order: plugin files first, then theme files, then the theme's functions.php last (its version number triggers the
# automatic rebuild, so it must arrive after everything else).
set -euo pipefail
LIST=$1 U=$2 A=$3 R=$4
cd "$(dirname "$0")/.."
ordered=$( { grep '^wp-content/plugins/' "$LIST" || true; grep '^wp-content/themes/' "$LIST" | grep -v 'iamfamous/functions.php$' || true; grep 'iamfamous/functions.php$' "$LIST" || true; } )
ok=0; fail=0
for f in $ordered; do
  [ -f "$f" ] || continue
  size=$(stat -c%s "$f")
  enc=$(python3 -c 'import sys,urllib.parse;print(urllib.parse.quote(sys.argv[1]))' "$f")
  c1=$(curl -s -m 60 -o /dev/null -w '%{http_code}' -X POST "$U/$enc?override=true" -H "X-Auth: $A" -H "X-Auth-Rest: $R" -H "Tus-Resumable: 1.0.0" -H "Upload-Length: $size" -H "Upload-Offset: 0")
  if [ "$size" -gt 0 ]; then
    c2=$(curl -s -m 120 -o /dev/null -w '%{http_code}' -X PATCH "$U/$enc?override=true" -H "X-Auth: $A" -H "X-Auth-Rest: $R" -H "Tus-Resumable: 1.0.0" -H "Content-Type: application/offset+octet-stream" -H "Upload-Offset: 0" --data-binary "@$f")
  else c2=204; fi
  if [ "$c1" = 201 ] && [ "$c2" = 204 ]; then ok=$((ok+1)); else fail=$((fail+1)); echo "FAIL $c1/$c2 $f"; fi
done
echo "uploaded $ok, failed $fail"
