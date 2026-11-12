# Local WordPress for real-render checks

The cloud container cannot download WordPress or WooCommerce, but npm is reachable. This folder holds what was used to render the **real** theme and plugin in round 1 (theme 1.0.0, plugin 1.0.0).

What it is:
- WordPress 6.7-alpha from the npm package `@wp-playground/wordpress-builds` (`src/wordpress/wp-nightly.zip`), run on PHP's built-in server with the bundled SQLite integration (`wp-content/db.php` made from the plugin's `db.copy`).
- `woo-shim.php`: a **test-only** stand-in for WooCommerce (products as posts, `wc_get_products`, `wc_price` and so on), dropped into `wp-content/mu-plugins/`. It lets the real importer, picker, package page and from-prices run. It never ships in a zip.
- `bootstrap.php <wp-dir> <catalogue.csv>`: activates the theme and plugin, imports the catalogue, runs the Setup screen's `IFM_Setup::run()`. Pass a copy of the CSV with `price_approved=YES` to see the picker with the draft prices.
- `screenshots.js <outdir> <widths> </path=name> …`: full-page Playwright screenshots, reporting `scrollWidth` for sideways overflow.
- `ui-checks.js <outdir>`: keyboard pass on the menu drawer and picker, sticky bar, fonts, own-JS size, cart and checkout template checks.

What it does not cover: the real WooCommerce cart, checkout, order confirmation and My Account blocks, payment gateways, and emails. Those need the live site.
