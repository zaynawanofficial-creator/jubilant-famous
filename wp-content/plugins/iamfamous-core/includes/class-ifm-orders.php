<?php
/**
 * Service order lifecycle on top of WooCommerce statuses.
 *
 * Paid -> Awaiting brief -> Brief received -> Processing (in progress) -> Client review -> Delivered -> Completed.
 * Payment semantics are untouched: the custom statuses are registered as "paid" statuses.
 *
 * @package IamFamousCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Order statuses + transitions.
 */
class IFM_Orders {

	/** Status slug (without wc-) => label. */
	public static function statuses(): array {
		return array(
			'ifm-awaiting-brief' => _x( 'Paid – awaiting brief', 'Order status', 'iamfamous-core' ),
			'ifm-brief-received' => _x( 'Brief received', 'Order status', 'iamfamous-core' ),
			'ifm-client-review'  => _x( 'Client review', 'Order status', 'iamfamous-core' ),
			'ifm-delivered'      => _x( 'Delivered', 'Order status', 'iamfamous-core' ),
		);
	}

	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'register_statuses' ) );
		add_filter( 'wc_order_statuses', array( __CLASS__, 'add_to_list' ) );
		add_filter( 'woocommerce_order_is_paid_statuses', array( __CLASS__, 'paid_statuses' ) );
		add_filter( 'woocommerce_valid_order_statuses_for_payment_complete', array( __CLASS__, 'not_payable_again' ) );
		add_filter( 'woocommerce_payment_complete_order_status', array( __CLASS__, 'after_payment' ), 20, 3 );
		add_filter( 'woocommerce_reports_order_statuses', array( __CLASS__, 'report_statuses' ) );
		add_filter( 'woocommerce_analytics_actionable_order_statuses', array( __CLASS__, 'report_statuses' ) );
		add_filter( 'woocommerce_order_actions', array( __CLASS__, 'order_actions' ) );
		add_action( 'woocommerce_order_action_ifm_mark_client_review', array( __CLASS__, 'action_client_review' ) );
		add_action( 'woocommerce_order_action_ifm_mark_delivered', array( __CLASS__, 'action_delivered' ) );
		add_filter( 'woocommerce_my_account_my_orders_actions', array( __CLASS__, 'keep_view_action' ), 10, 2 );
	}

	public static function register_statuses(): void {
		foreach ( self::statuses() as $slug => $label ) {
			register_post_status(
				'wc-' . $slug,
				array(
					'label'                     => $label,
					'public'                    => false,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					/* translators: %s: number of orders */
					'label_count'               => _n_noop( $label . ' <span class="count">(%s)</span>', $label . ' <span class="count">(%s)</span>', 'iamfamous-core' ), // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralSingular,WordPress.WP.I18n.NonSingularStringLiteralPlural
				)
			);
		}
	}

	/** Insert custom statuses after "processing". */
	public static function add_to_list( array $list ): array {
		$out = array();
		foreach ( $list as $key => $label ) {
			if ( 'wc-processing' === $key ) {
				$out['wc-ifm-awaiting-brief'] = self::statuses()['ifm-awaiting-brief'];
				$out['wc-ifm-brief-received'] = self::statuses()['ifm-brief-received'];
				$out[ $key ]                 = _x( 'In progress', 'Order status', 'iamfamous-core' );
				$out['wc-ifm-client-review']  = self::statuses()['ifm-client-review'];
				$out['wc-ifm-delivered']      = self::statuses()['ifm-delivered'];
				continue;
			}
			$out[ $key ] = $label;
		}
		return $out;
	}

	public static function paid_statuses( array $statuses ): array {
		return array_unique( array_merge( $statuses, array_keys( self::statuses() ) ) );
	}

	public static function report_statuses( $statuses ) {
		if ( ! is_array( $statuses ) ) {
			return $statuses;
		}
		return array_unique( array_merge( $statuses, array_keys( self::statuses() ) ) );
	}

	/** Payment-complete must not run again on orders already in a paid service status. */
	public static function not_payable_again( array $statuses ): array {
		return array_diff( $statuses, array_keys( self::statuses() ) );
	}

	/** True when the order contains at least one service package. */
	public static function has_packages( WC_Order $order ): bool {
		foreach ( $order->get_items() as $item ) {
			if ( $item instanceof WC_Order_Item_Product && IFM_Packages::is_package( $item->get_product_id() ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Service orders never auto-complete on payment (Woo would otherwise complete virtual-only orders).
	 */
	public static function after_payment( string $status, int $order_id, $order = null ): string {
		$order = $order instanceof WC_Order ? $order : wc_get_order( $order_id );
		if ( ! $order || ! self::has_packages( $order ) ) {
			return $status;
		}
		return 'complete' === $order->get_meta( '_ifm_brief_status' ) ? 'ifm-brief-received' : 'ifm-awaiting-brief';
	}

	public static function order_actions( array $actions ): array {
		$actions['ifm_mark_client_review'] = __( 'Service: move to Client review (emails customer)', 'iamfamous-core' );
		$actions['ifm_mark_delivered']     = __( 'Service: mark Delivered (emails customer)', 'iamfamous-core' );
		return $actions;
	}

	public static function action_client_review( WC_Order $order ): void {
		$order->update_status( 'ifm-client-review', __( 'Moved to client review.', 'iamfamous-core' ) );
		self::notify_customer( $order, __( 'Your order is ready for review', 'iamfamous-core' ), __( 'Your deliverables are ready for review. Log in to your account to see them and reply with any change requests within your revision allowance.', 'iamfamous-core' ) );
	}

	public static function action_delivered( WC_Order $order ): void {
		$order->update_status( 'ifm-delivered', __( 'Marked delivered.', 'iamfamous-core' ) );
		self::notify_customer( $order, __( 'Your order has been delivered', 'iamfamous-core' ), __( 'Your order has been delivered. You can find the final report and files in your account.', 'iamfamous-core' ) );
	}

	/** Plain transactional email in the WooCommerce template wrapper. */
	public static function notify_customer( WC_Order $order, string $heading, string $message ): void {
		$to = $order->get_billing_email();
		if ( ! $to ) {
			return;
		}
		$mailer = WC()->mailer();
		$body   = '<p>' . esc_html( $message ) . '</p><p><a href="' . esc_url( $order->get_view_order_url() ) . '">' . esc_html__( 'View your order', 'iamfamous-core' ) . '</a></p>';
		/* translators: 1: order number, 2: heading */
		$subject = sprintf( __( 'Order #%1$s: %2$s', 'iamfamous-core' ), $order->get_order_number(), $heading );
		$mailer->send( $to, $subject, $mailer->wrap_message( $heading, $body ) );
		$order->add_order_note( sprintf( /* translators: %s: subject */ __( 'Customer emailed: %s', 'iamfamous-core' ), $subject ) );
	}

	/** Make sure customers can always open the order (brief link lives there). */
	public static function keep_view_action( array $actions, WC_Order $order ): array {
		if ( ! isset( $actions['view'] ) ) {
			$actions['view'] = array(
				'url'  => $order->get_view_order_url(),
				'name' => __( 'View', 'iamfamous-core' ),
			);
		}
		return $actions;
	}
}
