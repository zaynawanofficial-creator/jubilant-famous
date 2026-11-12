<?php
/**
 * Order flow: Package → Project details → Checkout → Kick-off.
 *
 * [ifm_order_form] renders the project-details form on the service details (product) page.
 * The answers travel with the cart item, are saved on the order line, pre-fill the project brief,
 * and mark the brief complete when every required field was answered, so the customer is not asked twice.
 *
 * @package IamFamousCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Pre-checkout project details.
 */
class IFM_Order_Flow {

	const NONCE = 'ifm_order';

	public static function init(): void {
		add_shortcode( 'ifm_order_form', array( __CLASS__, 'render' ) );
		add_filter( 'woocommerce_add_to_cart_validation', array( __CLASS__, 'validate' ), 10, 3 );
		add_filter( 'woocommerce_add_cart_item_data', array( __CLASS__, 'cart_item_data' ), 10, 2 );
		add_filter( 'woocommerce_get_item_data', array( __CLASS__, 'item_data' ), 10, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'save_line' ), 20, 3 );
		add_action( 'woocommerce_checkout_order_processed', array( __CLASS__, 'prefill_brief' ), 20, 1 );
		add_action( 'woocommerce_store_api_checkout_order_processed', array( __CLASS__, 'prefill_brief' ), 20, 1 );
		add_filter( 'woocommerce_add_to_cart_redirect', array( __CLASS__, 'redirect' ), 20 );
		add_filter( 'wc_add_to_cart_message_html', array( __CLASS__, 'quiet_message' ), 20 );
	}

	private static function posted_for( int $product_id ): bool {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce checked in validate().
		return isset( $_POST['ifm_order_product'] ) && absint( $_POST['ifm_order_product'] ) === $product_id;
	}

	/** Fields for a product: its brief profile minus the free-text "anything else" (kept as optional). */
	public static function fields( WC_Product $product ): array {
		$id      = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();
		$profile = (string) get_post_meta( $id, '_ifm_brief_profile', true );
		$all     = IFM_Brief::profiles();
		return $all[ $profile ] ?? $all['generic'];
	}

	public static function render(): string {
		$product = wc_get_product( get_the_ID() );
		if ( ! $product || ! IFM_Packages::is_package( $product ) ) {
			return '';
		}
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
		do_action( 'litespeed_control_set_nocache', 'sb order form' );

		$turn = (string) $product->get_meta( '_ifm_turnaround' );
		ob_start();
		echo '<div class="ifm-order" id="order">';
		echo '<div class="ifm-order__price"><span class="ifm-order__label">' . esc_html__( 'Package price', 'iamfamous-core' ) . '</span>';
		if ( 'quote' === $product->get_meta( '_ifm_pricing_model' ) || ! $product->is_purchasable() ) {
			echo '<strong>' . esc_html__( 'Custom quote', 'iamfamous-core' ) . '</strong></div>';
			echo '<p>' . esc_html__( 'This package is priced to your requirements. Tell us what you need and we will reply with a fixed price.', 'iamfamous-core' ) . '</p>';
			echo '<a class="ifm-btn ifm-btn--primary ifm-order__submit" href="' . esc_url( IFM_Packages::quote_url( $product ) ) . '">' . esc_html__( 'Get a quote', 'iamfamous-core' ) . '</a></div>';
			return (string) ob_get_clean();
		}
		echo '<strong>' . wp_kses_post( $product->get_price_html() ) . '</strong>';
		if ( $turn ) {
			echo '<span class="ifm-order__turn">' . esc_html( sprintf( /* translators: %s: turnaround */ __( 'Delivery: %s', 'iamfamous-core' ), $turn ) ) . '</span>';
		}
		echo '</div>';

		echo '<ol class="ifm-order__steps" aria-label="' . esc_attr__( 'Order steps', 'iamfamous-core' ) . '"><li class="is-done">' . esc_html__( 'Package', 'iamfamous-core' ) . '</li><li class="is-current" aria-current="step">' . esc_html__( 'Project details', 'iamfamous-core' ) . '</li><li>' . esc_html__( 'Checkout', 'iamfamous-core' ) . '</li><li>' . esc_html__( 'Kick-off', 'iamfamous-core' ) . '</li></ol>';

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- refill after a failed validation only.
		$posted = self::posted_for( $product->get_id() ) && isset( $_POST['ifm_order'] ) && is_array( $_POST['ifm_order'] ) ? wp_unslash( $_POST['ifm_order'] ) : array();
		// phpcs:enable
		echo '<form class="ifm-order__form cart" method="post" action="' . esc_url( $product->get_permalink() ) . '#order" enctype="multipart/form-data" novalidate>';
		wp_nonce_field( self::NONCE, 'ifm_order_nonce' );
		echo '<input type="hidden" name="add-to-cart" value="' . esc_attr( (string) $product->get_id() ) . '">';
		echo '<input type="hidden" name="quantity" value="1">';
		echo '<input type="hidden" name="ifm_order_product" value="' . esc_attr( (string) $product->get_id() ) . '">';
		$required = array();
		$optional = array();
		foreach ( self::fields( $product ) as $key => $def ) {
			$html = IFM_Brief::field_html( 'ifm_order[' . $key . ']', 'ifm-order-' . $key, $def, sanitize_textarea_field( (string) ( $posted[ $key ] ?? '' ) ) );
			if ( $def[2] ) {
				$required[] = $html;
			} else {
				$optional[] = $html;
			}
		}
		echo '<div class="ifm-order__fields">' . implode( '', $required ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in field_html().
		if ( $optional ) {
			echo '<details class="ifm-order__more"><summary>' . esc_html__( 'More details (optional, you can add these later)', 'iamfamous-core' ) . '</summary><div class="ifm-order__fields">' . implode( '', $optional ) . '</div></details>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in field_html().
		}
		echo '<button type="submit" class="ifm-btn ifm-btn--primary ifm-order__submit single_add_to_cart_button">' . esc_html__( 'Continue to checkout', 'iamfamous-core' ) . ' →</button>';
		echo '<ul class="ifm-order__trust"><li>' . esc_html__( 'Secure checkout', 'iamfamous-core' ) . '</li><li>' . esc_html__( 'Upload brand files after payment', 'iamfamous-core' ) . '</li><li>' . esc_html__( 'Never share passwords, we use user invites', 'iamfamous-core' ) . '</li></ul>';
		echo '</form></div>';
		return (string) ob_get_clean();
	}

	/**
	 * @param bool $ok         Current validity.
	 * @param int  $product_id Product.
	 * @param int  $qty        Quantity.
	 */
	public static function validate( $ok, $product_id, $qty ) {
		if ( ! self::posted_for( (int) $product_id ) ) {
			return $ok;
		}
		if ( ! isset( $_POST['ifm_order_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ifm_order_nonce'] ) ), self::NONCE ) ) {
			wc_add_notice( __( 'Your session expired. Please submit your project details again.', 'iamfamous-core' ), 'error' );
			return false;
		}
		$product = wc_get_product( $product_id );
		$input   = isset( $_POST['ifm_order'] ) && is_array( $_POST['ifm_order'] ) ? wp_unslash( $_POST['ifm_order'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized per field.
		$missing = array();
		foreach ( self::fields( $product ) as $key => $def ) {
			$val = IFM_Brief::sanitize( (string) ( $input[ $key ] ?? '' ), $def );
			if ( $def[2] && '' === $val ) {
				$missing[] = $def[0];
			} elseif ( '' !== trim( (string) ( $input[ $key ] ?? '' ) ) && '' === $val && in_array( $def[1], array( 'url', 'email' ), true ) ) {
				/* translators: %s: field label */
				wc_add_notice( sprintf( __( 'Please check the format of: %s', 'iamfamous-core' ), $def[0] ), 'error' );
				return false;
			}
		}
		if ( $missing ) {
			/* translators: %s: list of field labels */
			wc_add_notice( sprintf( __( 'Please complete: %s', 'iamfamous-core' ), implode( ', ', $missing ) ), 'error' );
			return false;
		}
		return $ok;
	}

	public static function cart_item_data( array $data, int $product_id ): array {
		if ( ! self::posted_for( $product_id ) ) {
			return $data;
		}
		$product = wc_get_product( $product_id );
		$input   = isset( $_POST['ifm_order'] ) && is_array( $_POST['ifm_order'] ) ? wp_unslash( $_POST['ifm_order'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- nonce checked in validate(); sanitized per field.
		$details = array();
		foreach ( self::fields( $product ) as $key => $def ) {
			$val = IFM_Brief::sanitize( (string) ( $input[ $key ] ?? '' ), $def );
			if ( '' !== $val ) {
				$details[ $key ] = $val;
			}
		}
		$data['ifm_details'] = $details;
		return $data;
	}

	/** Short summary in cart and checkout (key fields only). */
	public static function item_data( array $item_data, array $cart_item ): array {
		if ( empty( $cart_item['ifm_details'] ) || empty( $cart_item['data'] ) ) {
			return $item_data;
		}
		$fields = self::fields( $cart_item['data'] );
		$shown  = 0;
		foreach ( $cart_item['ifm_details'] as $key => $val ) {
			if ( ! isset( $fields[ $key ] ) || ! $fields[ $key ][2] || $shown >= 3 ) {
				continue;
			}
			$item_data[] = array(
				'key'   => wp_strip_all_tags( $fields[ $key ][0] ),
				'value' => esc_html( wp_html_excerpt( str_replace( "\n", ', ', IFM_Brief::display_value( $fields[ $key ], $val ) ), 60, '…' ) ),
			);
			++$shown;
		}
		$item_data[] = array(
			'key'   => __( 'Project details', 'iamfamous-core' ),
			'value' => __( 'Received ✓', 'iamfamous-core' ),
		);
		return $item_data;
	}

	public static function save_line( WC_Order_Item_Product $item, $cart_item_key, array $values ): void {
		if ( ! empty( $values['ifm_details'] ) ) {
			$item->add_meta_data( '_ifm_details', $values['ifm_details'], true );
		}
	}

	/**
	 * Copy the project details into the order brief. Brief = complete when all required fields are answered.
	 *
	 * @param int|WC_Order $order Order or ID.
	 */
	public static function prefill_brief( $order ): void {
		$order = $order instanceof WC_Order ? $order : wc_get_order( $order );
		if ( ! $order || $order->get_meta( '_ifm_brief_data' ) ) {
			return;
		}
		$lines = IFM_Brief::lines( $order );
		if ( ! $lines ) {
			return;
		}
		$profiles = IFM_Brief::profiles();
		$data     = array();
		$missing  = 0;
		foreach ( $lines as $item_id => $line ) {
			$item    = $order->get_item( $item_id );
			$details = $item ? (array) $item->get_meta( '_ifm_details' ) : array();
			foreach ( $profiles[ $line['profile'] ] as $key => $def ) {
				$data[ $item_id ][ $key ] = (string) ( $details[ $key ] ?? '' );
				if ( $def[2] && '' === $data[ $item_id ][ $key ] ) {
					++$missing;
				}
			}
		}
		$order->update_meta_data( '_ifm_brief_data', $data );
		$order->update_meta_data( '_ifm_brief_status', $missing ? 'partial' : 'complete' );
		$order->update_meta_data( '_ifm_brief_submitted_at', gmdate( 'c' ) );
		$order->add_order_note( $missing ? __( 'Project details captured at checkout; some brief fields still to complete.', 'iamfamous-core' ) : __( 'Project details captured at checkout; brief complete.', 'iamfamous-core' ) );
		$order->save();
	}

	public static function redirect( $url ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- read-only routing.
		return isset( $_POST['ifm_order_product'] ) ? wc_get_checkout_url() : $url;
	}

	public static function quiet_message( $message ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- read-only.
		return isset( $_POST['ifm_order_product'] ) ? '' : $message;
	}
}
