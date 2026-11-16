<?php
/**
 * Optional add-ons on the package order form: an extra round of changes and express delivery.
 *
 * The chosen add-ons travel with the basket item, raise that item's price, show in the basket and checkout,
 * and are saved on the order line so the team sees them. Prices are a percentage of the package price with a
 * minimum, in whole pounds. Not offered on quote-only packages or on month-long ("Ongoing") packages.
 *
 * Change or switch off with the 'ifm_addons' filter (return an empty array to remove them).
 *
 * @package IamFamousCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Order add-ons.
 */
class IFM_Addons {

	public static function init(): void {
		add_filter( 'ifm_order_form_extras', array( __CLASS__, 'render' ), 10, 2 );
		add_filter( 'woocommerce_add_cart_item_data', array( __CLASS__, 'cart_item_data' ), 20, 2 );
		add_action( 'woocommerce_before_calculate_totals', array( __CLASS__, 'apply_prices' ), 20 );
		add_filter( 'woocommerce_get_item_data', array( __CLASS__, 'item_data' ), 20, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'save_line' ), 30, 3 );
	}

	/** @return array<string,array{label:string,help:string,pct:int,min:int}> */
	public static function options(): array {
		return (array) apply_filters(
			'ifm_addons',
			array(
				'revision' => array(
					'label' => __( 'Extra round of changes', 'iamfamous-core' ),
					'help'  => __( 'One more round of changes on top of those included in the package.', 'iamfamous-core' ),
					'pct'   => 15,
					'min'   => 10,
				),
				'express'  => array(
					'label' => __( 'Express delivery', 'iamfamous-core' ),
					'help'  => __( 'Your order goes to the front of the queue and we aim to deliver in half the usual time. If it takes longer than the standard delivery time, the express fee is refunded.', 'iamfamous-core' ),
					'pct'   => 25,
					'min'   => 15,
				),
			)
		);
	}

	/** Add-ons are for one-off, fixed-price packages only. */
	public static function eligible( $product ): bool {
		if ( ! $product || 'quote' === $product->get_meta( '_ifm_pricing_model' ) || ! $product->is_purchasable() || ! ( (float) $product->get_price() > 0 ) ) {
			return false;
		}
		$turn = (string) $product->get_meta( '_ifm_turnaround' );
		return 'month' !== (string) $product->get_meta( '_ifm_unit' ) && false === stripos( $turn, 'ongoing' ) && false === stripos( $turn, 'booked' );
	}

	/** Fee in whole pounds for one add-on on a given package price. */
	public static function fee( string $key, float $price ): int {
		$o = self::options()[ $key ] ?? null;
		return $o ? (int) max( (int) $o['min'], round( $price * (int) $o['pct'] / 100 ) ) : 0;
	}

	private static function money( int $amount ): string {
		return wp_strip_all_tags( wc_price( $amount, array( 'decimals' => 0 ) ) );
	}

	/** Checkbox list shown above the "Continue to checkout" button. */
	public static function render( $html, $product ): string {
		if ( ! self::eligible( $product ) || ! self::options() ) {
			return (string) $html;
		}
		$price = (float) $product->get_price();
		$out   = '<fieldset class="ifm-addons"><legend>' . esc_html__( 'Optional extras', 'iamfamous-core' ) . '</legend>';
		foreach ( self::options() as $key => $o ) {
			$id   = 'ifm-addon-' . $key;
			$out .= '<label class="ifm-addon" for="' . esc_attr( $id ) . '"><input type="checkbox" id="' . esc_attr( $id ) . '" name="ifm_addons[]" value="' . esc_attr( $key ) . '">'
				. '<span class="ifm-addon__text"><strong>' . esc_html( $o['label'] ) . '</strong><small>' . esc_html( $o['help'] ) . '</small></span>'
				. '<span class="ifm-addon__price">+' . esc_html( self::money( self::fee( $key, $price ) ) ) . '</span></label>';
		}
		return (string) $html . $out . '</fieldset>';
	}

	public static function cart_item_data( $data, $product_id ) {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce verified in IFM_Order_Flow::validate().
		if ( ! isset( $_POST['ifm_order_product'], $_POST['ifm_addons'] ) || absint( $_POST['ifm_order_product'] ) !== (int) $product_id || ! is_array( $_POST['ifm_addons'] ) ) {
			return $data;
		}
		$chosen = array_values( array_intersect( array_map( 'sanitize_key', wp_unslash( $_POST['ifm_addons'] ) ), array_keys( self::options() ) ) );
		// phpcs:enable
		if ( $chosen && self::eligible( wc_get_product( $product_id ) ) ) {
			$data['ifm_addons'] = $chosen;
		}
		return $data;
	}

	/** Package price plus the chosen add-ons. Always recalculated from the product's own price, so it never compounds. */
	public static function apply_prices( $cart ): void {
		if ( ! is_object( $cart ) || ( is_admin() && ! wp_doing_ajax() ) ) {
			return;
		}
		foreach ( $cart->get_cart() as $item ) {
			if ( empty( $item['ifm_addons'] ) || empty( $item['data'] ) ) {
				continue;
			}
			$base = wc_get_product( $item['product_id'] );
			if ( ! $base ) {
				continue;
			}
			$price = (float) $base->get_price();
			$total = $price;
			foreach ( (array) $item['ifm_addons'] as $key ) {
				$total += self::fee( (string) $key, $price );
			}
			$item['data']->set_price( $total );
		}
	}

	public static function item_data( $rows, $item ) {
		if ( empty( $item['ifm_addons'] ) ) {
			return $rows;
		}
		$price = (float) ( wc_get_product( $item['product_id'] ) ? wc_get_product( $item['product_id'] )->get_price() : 0 );
		foreach ( (array) $item['ifm_addons'] as $key ) {
			$o = self::options()[ $key ] ?? null;
			if ( $o ) {
				$rows[] = array( 'key' => __( 'Extra', 'iamfamous-core' ), 'value' => $o['label'] . ' (+' . self::money( self::fee( (string) $key, $price ) ) . ')' );
			}
		}
		return $rows;
	}

	public static function save_line( $item, $cart_item_key, $values ): void {
		if ( empty( $values['ifm_addons'] ) ) {
			return;
		}
		$labels = array();
		foreach ( (array) $values['ifm_addons'] as $key ) {
			$o = self::options()[ $key ] ?? null;
			if ( $o ) {
				$labels[] = $o['label'];
			}
		}
		if ( $labels ) {
			$item->add_meta_data( __( 'Extras', 'iamfamous-core' ), implode( ', ', $labels ), true );
		}
	}
}
