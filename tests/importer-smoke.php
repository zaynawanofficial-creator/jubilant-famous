<?php
/**
 * Smoke test for IFM_Importer::import() against the real SERVICE_CATALOG.csv,
 * using minimal in-memory stubs for the WordPress/WooCommerce functions it calls.
 * Run: php tests/importer-smoke.php
 */
define( 'ABSPATH', __DIR__ );
define( 'IFM_CORE_DIR', dirname( __DIR__ ) . '/wp-content/plugins/iamfamous-core/' );
$GLOBALS['terms'] = array(); $GLOBALS['products'] = array(); $GLOBALS['obj_terms'] = array(); $GLOBALS['term_meta'] = array();
function sanitize_title( $s ) { return trim( preg_replace( '/[^a-z0-9]+/', '-', strtolower( str_replace( '&', 'and', $s ) ) ), '-' ); }
function __( $s ) { return $s; }
function esc_html__( $s ) { return $s; }
function esc_html( $s ) { return htmlspecialchars( $s, ENT_QUOTES ); }
function esc_url( $s ) { return $s; }
function home_url( $p = '' ) { return 'https://example.test' . $p; }
function get_term_by( $f, $slug, $tax ) { return $GLOBALS['terms'][ $tax ][ $slug ] ?? false; }
function wp_insert_term( $name, $tax, $a ) { $id = count( $GLOBALS['terms'], COUNT_RECURSIVE ) + 1; $GLOBALS['terms'][ $tax ][ $a['slug'] ] = (object) array( 'term_id' => $id, 'name' => $name, 'parent' => $a['parent'] ); return array( 'term_id' => $id ); }
function is_wp_error( $x ) { return false; }
function update_term_meta( $id, $k, $v ) { $GLOBALS['term_meta'][ $id ][ $k ] = $v; }
function wp_update_term( $id, $tax, $a ) { foreach ( $GLOBALS['terms'][ $tax ] as $t ) { if ( $t->term_id === $id && isset( $a['parent'] ) ) { $t->parent = $a['parent']; } } }
function wc_get_product_id_by_sku( $sku ) { foreach ( $GLOBALS['products'] as $id => $p ) { if ( $p->sku === $sku ) { return $id; } } return 0; }
function wc_get_product( $id ) { return $GLOBALS['products'][ $id ]; }
function get_post_thumbnail_id( $id ) { return 0; }
function wp_upload_bits() { return array( 'error' => 'stub' ); }
function wp_set_object_terms( $id, $t, $tax ) { $GLOBALS['obj_terms'][ $id ] = $t; }
class IFM_Packages { const TAX = 'ifm_package_group'; public static bool $bypass_gate = false; }
class WC_Product_Simple {
	public $id = 0, $description = '', $name, $sku, $status, $price, $virtual, $downloadable, $meta = array(), $cats;
	function __call( $m, $a ) { $map = array( 'set_description' => 'description', 'set_name' => 'name', 'set_sku' => 'sku', 'set_status' => 'status', 'set_regular_price' => 'price', 'set_virtual' => 'virtual', 'set_downloadable' => 'downloadable', 'set_category_ids' => 'cats' ); if ( isset( $map[ $m ] ) ) { $this->{$map[ $m ]} = $a[0]; } }
	function update_meta_data( $k, $v ) { $this->meta[ $k ] = $v; }
	function save() { if ( ! $this->id ) { $this->id = count( $GLOBALS['products'] ) + 1; } $GLOBALS['products'][ $this->id ] = $this; return $this->id; }
}
require IFM_CORE_DIR . 'includes/class-ifm-importer.php';

$fail = 0;
function check( $cond, $msg ) { global $fail; echo ( $cond ? "PASS " : "FAIL " ) . $msg . "\n"; if ( ! $cond ) { $fail++; } }

$csv = dirname( __DIR__ ) . '/SERVICE_CATALOG.csv';
// Simulate a pre-existing top-level category (as found on the old site).
wp_insert_term( 'Content Writing', 'product_cat', array( 'slug' => 'content-writing', 'parent' => 0 ) );
$dry = IFM_Importer::import( $csv, true );
check( count( $GLOBALS['products'] ) === 0, 'dry run saves nothing' );
check( str_contains( implode( "\n", $dry ), '£' ) && ! str_contains( implode( "\n", $dry ), '$' ), 'dry-run log shows pounds, never dollars' );
IFM_Importer::import( $csv );
$rows = IFM_Importer::read_csv( $csv );
check( count( $GLOBALS['products'] ) === count( $rows ) && count( $rows ) >= 100, 'one product per CSV row (' . count( $rows ) . ')' );
IFM_Importer::import( $csv );
check( count( $GLOBALS['products'] ) === count( $rows ), 're-import upserts by SKU (no duplicates)' );

$by = array(); foreach ( $GLOBALS['products'] as $p ) { $by[ $p->sku ] = $p; }
check( ! array_filter( array_keys( $by ), fn( $k ) => ! str_starts_with( $k, 'if-' ) ), 'every SKU carries the if- prefix' );
check( ! array_filter( $rows, fn( $r ) => 'GBP' !== $r['currency'] ), 'every catalogue row is in GBP' );

// --- DRAFT gate: nothing with a fixed price may be published until the owner approves prices.
$published_fixed = array_filter( $by, fn( $p ) => 'publish' === $p->status && 'fixed' === $p->meta['_ifm_pricing_model'] );
$approved_rows   = array_filter( $rows, fn( $r ) => 'YES' === $r['price_approved'] && 'fixed' === $r['pricing_model'] );
check( count( $published_fixed ) === count( array_filter( $approved_rows, fn( $r ) => 'YES' === $r['scope_approved'] && 'READY' === $r['publish'] ) ), 'only owner-approved fixed prices are published (' . count( $published_fixed ) . ' now)' );
$p = $by['if-local-seo-local-seo-packages-2'];
check( ( 'YES' === array_values( array_filter( $rows, fn( $r ) => 'IF-local-seo-local-seo-packages-2' === $r['package_code'] ) )[0]['price_approved'] ) === ( 'publish' === $p->status ), 'Local SEO Starter status follows price_approved' );
check( true === $p->virtual && false === $p->downloadable, 'packages are virtual, not downloadable' );
$min = min( array_map( fn( $r ) => (float) $r['price'], array_filter( $rows, fn( $r ) => is_numeric( $r['price'] ) ) ) );
check( $min >= 5, 'no package is priced under £5 (lowest £' . $min . ')' );
foreach ( array_filter( $rows, fn( $r ) => is_numeric( $r['price'] ) ) as $r ) { if ( (float) $r['price'] !== floor( (float) $r['price'] ) ) { check( false, 'price not a whole pound: ' . $r['package_code'] ); } }
check( true, 'all prices are whole pounds' );

// --- Copy: IamFamous wording wins for round-1 services; content keys work despite the SKU prefix.
check( '1' === $p->meta['_ifm_featured'] && str_contains( $p->meta['_ifm_deliverables'], 'UK directory listings' ), 'Local SEO Starter: featured, with IamFamous deliverables (by_sku lookup without prefix)' );
check( str_contains( $by['if-website-development-website-packages-3']->meta['_ifm_deliverables'], 'recorded walkthrough' ) && '1' === $by['if-website-development-website-packages-3']->meta['_ifm_featured'], '5-page website: IamFamous deliverables, featured by quantity' );
check( 'Most small businesses' === $by['if-website-development-website-packages-3']->meta['_ifm_ideal_for'], 'ideal-for by quantity from the IamFamous copy file' );
check( str_contains( $by['if-social-media-management-social-media-packages-1']->meta['_ifm_exclusions'], 'Buying followers' ), 'social packages exclude bought followers' );
check( '' === $by['if-off-page-seo-profiles-and-citations-2']->meta['_ifm_featured'] && '1' === $by['if-off-page-seo-profiles-and-citations-4']->meta['_ifm_featured'], 'featured by quantity (20 links)' );
check( str_contains( $by['if-graphic-design-branding-5']->meta['_ifm_deliverables'], 'Canva' ), 'Canva package gets its own deliverables' );
$q = $by['if-seo-audit-audit-packages-3'];
check( '' === $q->price && 'quote' === $q->meta['_ifm_pricing_model'], 'quote package has no price' );
check( 'draft' === $by['if-analytics-and-tracking-tracking-setup-1']->status, 'planned services stay draft' );
check( str_contains( $p->description, '/services/local-seo/' ) && ! str_contains( $p->description, 'seobooster' ), 'product description links to its service page' );
foreach ( $by as $sku => $x ) {
	if ( 'publish' === $x->status && 'fixed' === $x->meta['_ifm_pricing_model'] && ! ( (float) $x->price > 0 ) ) { check( false, "published fixed package without price: $sku" ); }
	if ( empty( $x->meta['_ifm_deliverables'] ) && 'draft' !== $x->status ) { check( false, "published package without deliverables: $sku" ); }
}
check( true, 'no published package lacks a price or deliverables' );
$cw = $GLOBALS['terms']['product_cat']['content-writing']; $fam = $GLOBALS['terms']['product_cat']['content'];
check( $cw->parent === $fam->term_id, 'pre-existing service category is moved under its family' );
check( false === IFM_Packages::$bypass_gate, 'publish gate bypass reset after import' );

// --- Approval path: flip price_approved to YES in a copy and the same packages publish.
$tmp = tempnam( sys_get_temp_dir(), 'ifm' );
$fh  = fopen( $tmp, 'w' ); fputcsv( $fh, array_keys( $rows[0] ), ',', '"', '\\' );
foreach ( $rows as $r ) { if ( '0' === $r['price_approved'] ) { $r['price_approved'] = 'YES'; } fputcsv( $fh, $r, ',', '"', '\\' ); }
fclose( $fh );
IFM_Importer::import( $tmp ); unlink( $tmp );
$by = array(); foreach ( $GLOBALS['products'] as $x ) { $by[ $x->sku ] = $x; }
check( 'publish' === $by['if-local-seo-local-seo-packages-2']->status && (float) $by['if-local-seo-local-seo-packages-2']->price > 0, 'after approval the package publishes with its GBP price' );
check( count( $GLOBALS['products'] ) === count( $rows ), 'approval re-import creates no duplicates' );
echo $fail ? "\n$fail FAILED\n" : "\nALL PASSED\n";
exit( $fail ? 1 : 0 );
