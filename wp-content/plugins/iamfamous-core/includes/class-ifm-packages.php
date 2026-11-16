<?php
/**
 * Package data model: group taxonomy, package meta, publish gate, order line snapshot.
 *
 * @package IamFamousCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Service package model on top of WooCommerce products.
 */
class IFM_Packages {

	const TAX = 'ifm_package_group';

	/** Set by the importer, which applies the same approval rules itself. */
	public static bool $bypass_gate = false;

	/**
	 * Meta fields: key => [label, type]. Types: text, textarea (one item per line), select, checkbox, number.
	 *
	 * @return array<string,array>
	 */
	public static function fields(): array {
		return array(
			'_ifm_pricing_model'  => array( __( 'Pricing model', 'iamfamous-core' ), 'select', array( 'fixed' => 'Fixed price', 'quote' => 'Quote only (no add to cart)' ) ),
			'_ifm_quantity'       => array( __( 'Quantity', 'iamfamous-core' ), 'number' ),
			'_ifm_unit'           => array( __( 'Unit (e.g. links, pages, words)', 'iamfamous-core' ), 'text' ),
			'_ifm_ideal_for'      => array( __( 'Ideal for (one line)', 'iamfamous-core' ), 'text' ),
			'_ifm_deliverables'   => array( __( 'Deliverables (one per line)', 'iamfamous-core' ), 'textarea' ),
			'_ifm_exclusions'     => array( __( 'Not included (one per line)', 'iamfamous-core' ), 'textarea' ),
			'_ifm_requirements'   => array( __( 'What we need from you (one per line)', 'iamfamous-core' ), 'textarea' ),
			'_ifm_turnaround'     => array( __( 'Turnaround', 'iamfamous-core' ), 'text' ),
			'_ifm_revisions'      => array( __( 'Revisions / replacements', 'iamfamous-core' ), 'text' ),
			'_ifm_brief_profile'  => array( __( 'Brief form', 'iamfamous-core' ), 'select', array( 'offpage' => 'Off-page SEO', 'local' => 'Local SEO', 'web' => 'Website build', 'fix' => 'Fix / maintenance', 'content' => 'Content', 'design' => 'Design', 'seo' => 'SEO / audit', 'ads' => 'Paid ads', 'social' => 'Social media', 'generic' => 'Generic' ) ),
			'_ifm_terms_version'  => array( __( 'Terms version', 'iamfamous-core' ), 'text' ),
			'_ifm_sort'           => array( __( 'Sort order', 'iamfamous-core' ), 'number' ),
			'_ifm_featured'       => array( __( 'Highlight as "Most popular"', 'iamfamous-core' ), 'checkbox' ),
			'_ifm_price_approved' => array( __( 'Price approved by owner', 'iamfamous-core' ), 'checkbox' ),
			'_ifm_scope_approved' => array( __( 'Scope approved by owner', 'iamfamous-core' ), 'checkbox' ),
		);
	}

	/** Line-item snapshot keys copied from the product at purchase. */
	const SNAPSHOT = array( '_ifm_quantity', '_ifm_unit', '_ifm_turnaround', '_ifm_revisions', '_ifm_terms_version', '_ifm_brief_profile' );

	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'register_taxonomy' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ) );
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'save_meta' ) );
		add_filter( 'wp_insert_post_data', array( __CLASS__, 'publish_gate' ), 20, 2 );
		add_action( 'admin_notices', array( __CLASS__, 'gate_notice' ) );
		add_filter( 'woocommerce_is_purchasable', array( __CLASS__, 'quote_not_purchasable' ), 10, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'snapshot_line_item' ), 10, 3 );
		add_filter( 'woocommerce_order_item_display_meta_key', array( __CLASS__, 'line_meta_label' ), 10, 2 );
	}

	public static function register_taxonomy(): void {
		register_taxonomy(
			self::TAX,
			'product',
			array(
				'labels'            => array(
					'name'          => __( 'Package groups', 'iamfamous-core' ),
					'singular_name' => __( 'Package group', 'iamfamous-core' ),
				),
				'hierarchical'      => true,
				'public'            => false,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => false,
			)
		);
	}

	public static function is_package( $product ): bool {
		$product = wc_get_product( $product );
		return $product && '' !== (string) $product->get_meta( '_ifm_pricing_model' );
	}

	public static function get( $product, string $key ) {
		$product = wc_get_product( $product );
		return $product ? $product->get_meta( $key ) : '';
	}

	/** Split a one-per-line field into a clean list. */
	public static function lines( $product, string $key ): array {
		return array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) self::get( $product, $key ) ) ) ) );
	}

	public static function add_meta_box(): void {
		add_meta_box( 'ifm_package', __( 'Service package', 'iamfamous-core' ), array( __CLASS__, 'render_meta_box' ), 'product', 'normal', 'high' );
	}

	public static function render_meta_box( WP_Post $post ): void {
		$product = wc_get_product( $post->ID );
		wp_nonce_field( 'ifm_package_save', 'ifm_package_nonce' );
		echo '<table class="form-table" role="presentation">';
		foreach ( self::fields() as $key => $def ) {
			$val = $product ? $product->get_meta( $key ) : '';
			$id  = esc_attr( ltrim( $key, '_' ) );
			echo '<tr><th><label for="' . $id . '">' . esc_html( $def[0] ) . '</label></th><td>';
			switch ( $def[1] ) {
				case 'textarea':
					echo '<textarea class="large-text" rows="4" id="' . $id . '" name="' . esc_attr( $key ) . '">' . esc_textarea( (string) $val ) . '</textarea>';
					break;
				case 'select':
					echo '<select id="' . $id . '" name="' . esc_attr( $key ) . '"><option value="">' . esc_html__( '— Not a service package —', 'iamfamous-core' ) . '</option>';
					foreach ( $def[2] as $opt => $label ) {
						echo '<option value="' . esc_attr( $opt ) . '"' . selected( $val, $opt, false ) . '>' . esc_html( $label ) . '</option>';
					}
					echo '</select>';
					break;
				case 'checkbox':
					echo '<input type="checkbox" id="' . $id . '" name="' . esc_attr( $key ) . '" value="1"' . checked( $val, '1', false ) . '>';
					break;
				case 'number':
					echo '<input type="number" min="0" step="1" id="' . $id . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( (string) $val ) . '">';
					break;
				default:
					echo '<input type="text" class="regular-text" id="' . $id . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( (string) $val ) . '">';
			}
			echo '</td></tr>';
		}
		echo '</table><p class="description">' . esc_html__( 'Fixed-price packages can only be published when both approvals are ticked and a price is set. Quote-only packages show "Get a quote" instead of Add to cart.', 'iamfamous-core' ) . '</p>';
	}

	public static function save_meta( WC_Product $product ): void {
		if ( ! isset( $_POST['ifm_package_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ifm_package_nonce'] ) ), 'ifm_package_save' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_product', $product->get_id() ) ) {
			return;
		}
		foreach ( self::fields() as $key => $def ) {
			$raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized per type below.
			switch ( $def[1] ) {
				case 'textarea':
					$val = sanitize_textarea_field( $raw );
					break;
				case 'select':
					$val = array_key_exists( $raw, $def[2] ) ? $raw : '';
					break;
				case 'checkbox':
					$val = $raw ? '1' : '';
					break;
				case 'number':
					$val = '' === $raw ? '' : (string) absint( $raw );
					break;
				default:
					$val = sanitize_text_field( $raw );
			}
			$product->update_meta_data( $key, $val );
		}
		if ( self::is_package_meta( $product ) ) {
			$product->set_virtual( true );
			$product->set_downloadable( false );
		}
	}

	private static function is_package_meta( WC_Product $product ): bool {
		return '' !== (string) $product->get_meta( '_ifm_pricing_model' );
	}

	/** True when a package may be published. */
	public static function can_publish( int $product_id ): bool {
		$model = get_post_meta( $product_id, '_ifm_pricing_model', true );
		if ( '' === $model || 'quote' === $model ) {
			return true;
		}
		$price = get_post_meta( $product_id, '_regular_price', true );
		return '1' === get_post_meta( $product_id, '_ifm_price_approved', true )
			&& '1' === get_post_meta( $product_id, '_ifm_scope_approved', true )
			&& '' !== (string) $price && (float) $price > 0;
	}

	/**
	 * Keep unapproved fixed-price packages out of the public catalogue.
	 * Runs before save; meta from this request is saved later, so it re-checks on the next save too.
	 */
	public static function publish_gate( array $data, array $postarr ): array {
		if ( self::$bypass_gate || 'product' !== $data['post_type'] || 'publish' !== $data['post_status'] || empty( $postarr['ID'] ) ) {
			return $data;
		}
		$id = (int) $postarr['ID'];
		// Prefer the values being submitted in this request (admin edit screen).
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- read-only check; nonce verified in save_meta.
		if ( isset( $_POST['_ifm_pricing_model'] ) ) {
			$model    = sanitize_text_field( wp_unslash( $_POST['_ifm_pricing_model'] ) );
			$price    = isset( $_POST['_regular_price'] ) ? wc_format_decimal( wp_unslash( $_POST['_regular_price'] ) ) : '';
			$approved = ! empty( $_POST['_ifm_price_approved'] ) && ! empty( $_POST['_ifm_scope_approved'] );
			$ok       = '' === $model || 'quote' === $model || ( $approved && (float) $price > 0 );
		} else {
			$ok = self::can_publish( $id );
		}
		// phpcs:enable
		if ( ! $ok ) {
			$data['post_status'] = 'draft';
			set_transient( 'ifm_gate_' . get_current_user_id(), $id, 60 );
		}
		return $data;
	}

	public static function gate_notice(): void {
		$id = get_transient( 'ifm_gate_' . get_current_user_id() );
		if ( ! $id ) {
			return;
		}
		delete_transient( 'ifm_gate_' . get_current_user_id() );
		echo '<div class="notice notice-warning"><p>' . esc_html__( 'Package kept as draft: a fixed-price package needs a price above zero plus "Price approved" and "Scope approved" before it can be published.', 'iamfamous-core' ) . '</p></div>';
	}

	public static function quote_not_purchasable( bool $purchasable, $product ): bool {
		$id = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();
		if ( 'quote' === get_post_meta( $id, '_ifm_pricing_model', true ) ) {
			return false;
		}
		return $purchasable;
	}

	public static function quote_url( WC_Product $product ): string {
		$page = get_page_by_path( 'free-growth-check' );
		$base = $page ? get_permalink( $page ) : home_url( '/free-growth-check/' );
		return add_query_arg( 'package', rawurlencode( $product->get_sku() ? $product->get_sku() : (string) $product->get_id() ), $base );
	}

	/** Snapshot package terms onto the order line so the order stays accurate if the product changes. */
	public static function snapshot_line_item( WC_Order_Item_Product $item, $cart_item_key, array $values ): void {
		$product = $values['data'] ?? null;
		if ( ! $product instanceof WC_Product ) {
			return;
		}
		$pid = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();
		if ( ! self::is_package( $pid ) ) {
			return;
		}
		// Keys without a leading underscore so WooCommerce shows them on the order (admin, emails, account).
		$item->add_meta_data( 'ifm_package_sku', $product->get_sku(), true );
		$groups = wp_get_post_terms( $pid, self::TAX, array( 'fields' => 'names' ) );
		if ( ! is_wp_error( $groups ) && $groups ) {
			$item->add_meta_data( 'ifm_group', implode( ', ', $groups ), true );
		}
		foreach ( self::SNAPSHOT as $key ) {
			$item->add_meta_data( ltrim( $key, '_' ), (string) get_post_meta( $pid, $key, true ), true );
		}
	}

	public static function line_meta_label( string $key ): string {
		$map = array(
			'ifm_package_sku'   => __( 'Package', 'iamfamous-core' ),
			'ifm_group'         => __( 'Group', 'iamfamous-core' ),
			'ifm_quantity'      => __( 'Quantity', 'iamfamous-core' ),
			'ifm_unit'          => __( 'Unit', 'iamfamous-core' ),
			'ifm_turnaround'    => __( 'Turnaround', 'iamfamous-core' ),
			'ifm_revisions'     => __( 'Revisions', 'iamfamous-core' ),
			'ifm_terms_version' => __( 'Terms version', 'iamfamous-core' ),
			'ifm_brief_profile' => __( 'Brief form', 'iamfamous-core' ),
		);
		return $map[ $key ] ?? $key;
	}
}
