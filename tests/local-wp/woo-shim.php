<?php
/**
 * TEST ONLY: a minimal stand-in for WooCommerce so IamFamous Core's real code (importer, picker, service page,
 * from-prices) can run in a local WordPress where WooCommerce cannot be downloaded. Never ship this.
 */
if ( defined( 'WC_VERSION' ) ) { return; }
class WooCommerce {}
add_action( 'init', function () {
	register_post_type( 'product', array( 'public' => true, 'label' => 'Products', 'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail' ), 'rewrite' => array( 'slug' => 'product' ), 'show_in_rest' => true ) );
	register_taxonomy( 'product_cat', 'product', array( 'hierarchical' => true, 'public' => true, 'rewrite' => array( 'slug' => 'product-category' ) ) );
}, 0 );
class WC_Product {
	public $id = 0; public $d = array( 'name' => '', 'sku' => '', 'price' => '', 'status' => 'draft', 'desc' => '', 'short' => '', 'cats' => array() ); public $m = array();
	public function __construct( $id = 0 ) { if ( $id ) { $p = get_post( $id ); $this->id = (int) $id; $this->d['name'] = $p->post_title; $this->d['status'] = $p->post_status; $this->d['desc'] = $p->post_content; $this->d['short'] = $p->post_excerpt; $this->d['sku'] = (string) get_post_meta( $id, '_sku', true ); $this->d['price'] = (string) get_post_meta( $id, '_regular_price', true ); } }
	public function get_id() { return $this->id; } public function get_parent_id() { return 0; }
	public function get_name() { return $this->d['name']; } public function get_sku() { return $this->d['sku']; }
	public function get_price() { return $this->d['price']; } public function get_regular_price() { return $this->d['price']; }
	public function get_short_description() { return $this->d['short']; } public function get_description() { return $this->d['desc']; }
	public function get_meta( $k ) { return array_key_exists( $k, $this->m ) ? $this->m[ $k ] : get_post_meta( $this->id, $k, true ); }
	public function update_meta_data( $k, $v ) { $this->m[ $k ] = $v; }
	public function get_permalink() { return get_permalink( $this->id ); }
	public function get_catalog_visibility() { return 'visible'; } public function is_on_sale() { return false; }
	public function is_purchasable() { return apply_filters( 'woocommerce_is_purchasable', 'publish' === $this->d['status'] && '' !== $this->d['price'], $this ); }
	public function get_image_id() { return (int) get_post_thumbnail_id( $this->id ); }
	public function get_price_html() { return wc_price( (float) $this->d['price'] ); }
	public function set_name( $v ) { $this->d['name'] = $v; } public function set_sku( $v ) { $this->d['sku'] = $v; } public function set_regular_price( $v ) { $this->d['price'] = $v; }
	public function set_status( $v ) { $this->d['status'] = $v; } public function set_description( $v ) { $this->d['desc'] = $v; } public function set_short_description( $v ) { $this->d['short'] = $v; }
	public function set_category_ids( $v ) { $this->d['cats'] = $v; }
	public function set_virtual( $v ) {} public function set_downloadable( $v ) {} public function set_sold_individually( $v ) {} public function set_tax_status( $v ) {}
	public function save() {
		$a = array( 'post_type' => 'product', 'post_title' => $this->d['name'], 'post_status' => $this->d['status'], 'post_content' => $this->d['desc'], 'post_excerpt' => $this->d['short'] );
		if ( $this->id ) { $a['ID'] = $this->id; }
		$this->id = (int) wp_insert_post( wp_slash( $a ) );
		update_post_meta( $this->id, '_sku', $this->d['sku'] ); update_post_meta( $this->id, '_regular_price', $this->d['price'] ); update_post_meta( $this->id, '_price', $this->d['price'] );
		foreach ( $this->m as $k => $v ) { update_post_meta( $this->id, $k, $v ); }
		if ( $this->d['cats'] ) { wp_set_object_terms( $this->id, array_map( 'intval', $this->d['cats'] ), 'product_cat' ); }
		return $this->id;
	}
}
class WC_Product_Simple extends WC_Product {}
function wc_get_product( $p = 0 ) { if ( $p instanceof WC_Product ) { return $p; } $id = $p instanceof WP_Post ? $p->ID : (int) $p; return ( $id && 'product' === get_post_type( $id ) ) ? new WC_Product_Simple( $id ) : false; }
function wc_get_product_id_by_sku( $sku ) { $q = get_posts( array( 'post_type' => 'product', 'post_status' => 'any', 'meta_key' => '_sku', 'meta_value' => $sku, 'fields' => 'ids', 'numberposts' => 1 ) ); return $q ? (int) $q[0] : 0; }
function wc_get_products( $args ) {
	$q = array( 'post_type' => 'product', 'post_status' => $args['status'] ?? 'publish', 'numberposts' => $args['limit'] ?? 100, 'fields' => 'ids' );
	if ( ! empty( $args['category'] ) ) { $q['tax_query'] = array( array( 'taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => $args['category'] ) ); }
	return array_map( 'wc_get_product', get_posts( $q ) );
}
function get_woocommerce_currency() { return 'GBP'; }
function wc_price( $v, $a = array() ) { $d = $a['decimals'] ?? 2; return '<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">&pound;</span>' . number_format( (float) $v, $d ) . '</bdi></span>'; }
function wc_format_decimal( $v ) { return (string) (float) $v; }
function wc_get_price_to_display( $p ) { return (float) $p->get_price(); }
function is_product() { return is_singular( 'product' ); } function is_cart() { return false; } function is_checkout() { return false; } function is_account_page() { return false; } function is_shop() { return false; } function is_product_taxonomy() { return is_tax( 'product_cat' ); }
function wc_get_page_permalink( $p ) { return home_url( '/my-account/' ); } function wc_get_checkout_url() { return home_url( '/checkout/' ); } function wc_get_cart_url() { return home_url( '/basket/' ); }
