<?php
/**
 * Smoke test for order add-ons (IFM_Addons) with minimal stubs.
 * Run: php tests/addons-smoke.php
 */
define( 'ABSPATH', '/' );
function add_filter( ...$a ) {}
function add_action( ...$a ) {}
function apply_filters( $h, $v ) { return $v; }
function __( $s ) { return $s; }
function esc_html__( $s ) { return $s; }
function esc_html( $s ) { return htmlspecialchars( (string) $s ); }
function esc_attr( $s ) { return htmlspecialchars( (string) $s ); }
function wp_strip_all_tags( $s ) { return strip_tags( $s ); }
function wc_price( $v, $a = array() ) { return '£' . number_format( (float) $v, $a['decimals'] ?? 2 ); }
function is_admin() { return false; }
function wp_doing_ajax() { return false; }
class P { public $price; public $meta; public $set; function __construct( $price, $meta = array() ) { $this->price = $price; $this->meta = $meta; } function get_meta( $k ) { return $this->meta[ $k ] ?? ''; } function get_price() { return $this->price; } function is_purchasable() { return true; } function set_price( $v ) { $this->set = $v; } }
$GLOBALS['products'] = array( 1 => new P( 279, array( '_ifm_pricing_model' => 'fixed', '_ifm_turnaround' => '10–14 working days', '_ifm_unit' => 'pages' ) ) );
function wc_get_product( $id ) { return $GLOBALS['products'][ $id ] ?? false; }
require __DIR__ . '/../wp-content/plugins/iamfamous-core/includes/class-ifm-addons.php';

$fail = 0;
function check( string $name, bool $ok ): void { global $fail; echo ( $ok ? 'PASS ' : 'FAIL ' ) . $name . "\n"; $fail += $ok ? 0 : 1; }
check( 'fees are a percentage in whole pounds (279: +42 changes, +70 express)', 42 === IFM_Addons::fee( 'revision', 279 ) && 70 === IFM_Addons::fee( 'express', 279 ) );
check( 'small packages use the minimum fee (10: +10 changes, +15 express)', 10 === IFM_Addons::fee( 'revision', 10 ) && 15 === IFM_Addons::fee( 'express', 10 ) );
check( 'unknown add-on costs nothing', 0 === IFM_Addons::fee( 'nope', 100 ) );
check( 'one-off fixed package is eligible', IFM_Addons::eligible( $GLOBALS['products'][1] ) );
check( 'monthly, booked and quote packages are not', ! IFM_Addons::eligible( new P( 159, array( '_ifm_pricing_model' => 'fixed', '_ifm_turnaround' => 'Ongoing for 30 days', '_ifm_unit' => 'month' ) ) ) && ! IFM_Addons::eligible( new P( 42, array( '_ifm_pricing_model' => 'fixed', '_ifm_turnaround' => 'Booked within 3 working days' ) ) ) && ! IFM_Addons::eligible( new P( 0, array( '_ifm_pricing_model' => 'quote' ) ) ) );
$html = IFM_Addons::render( '', $GLOBALS['products'][1] );
check( 'form shows both extras with their prices', 2 === substr_count( $html, 'name="ifm_addons[]"' ) && str_contains( $html, '+£42' ) && str_contains( $html, '+£70' ) );
$line = new P( 279 );
$cart = new class( $line ) { public $l; function __construct( $l ) { $this->l = $l; } function get_cart() { return array( array( 'product_id' => 1, 'ifm_addons' => array( 'revision', 'express' ), 'data' => $this->l ), array( 'product_id' => 1, 'data' => new P( 279 ) ) ); } };
IFM_Addons::apply_prices( $cart ); IFM_Addons::apply_prices( $cart );
check( 'basket price = package + extras, and does not compound when recalculated', 391.0 === (float) $line->set );
$rows = IFM_Addons::item_data( array(), array( 'product_id' => 1, 'ifm_addons' => array( 'express' ) ) );
check( 'basket lists the extra with its price', 1 === count( $rows ) && str_contains( $rows[0]['value'], 'Express delivery (+£70)' ) );
echo $fail ? "\n{$fail} FAILED\n" : "\nALL PASSED\n";
exit( $fail ? 1 : 0 );
