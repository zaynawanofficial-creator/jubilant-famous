#!/usr/bin/env bash
# Builds dist/iamfamous-core.zip for upload via wp-admin → Plugins → Add New → Upload Plugin.
# Copies the root SERVICE_CATALOG.csv into the plugin so the importer always reads the current catalogue.
set -euo pipefail
cd "$(dirname "$0")/.."
cp SERVICE_CATALOG.csv wp-content/plugins/iamfamous-core/data/SERVICE_CATALOG.csv
for f in $(find wp-content/plugins/iamfamous-core -name '*.php'); do php -l "$f" >/dev/null; done
mkdir -p dist && rm -f dist/iamfamous-core.zip
(cd wp-content/plugins && zip -qr ../../dist/iamfamous-core.zip iamfamous-core -x '*.DS_Store')
echo "Built dist/iamfamous-core.zip"
