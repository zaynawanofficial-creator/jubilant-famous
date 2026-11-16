<?php
/**
 * Daily housekeeping that keeps the promises made in the privacy policy:
 *  - enquiries (Leads) older than 12 months are deleted;
 *  - files uploaded with a project brief are deleted 12 months after the order was completed or delivered.
 * Orders and invoices themselves are kept (tax records). Filter 'ifm_retention_months' to change the period.
 *
 * @package IamFamousCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Data retention.
 */
class IFM_Cleanup {

	const HOOK = 'ifm_daily_cleanup';

	public static function init(): void {
		add_action( self::HOOK, array( __CLASS__, 'run' ) );
		add_action( 'init', array( __CLASS__, 'schedule' ) );
	}

	public static function schedule(): void {
		if ( ! wp_next_scheduled( self::HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::HOOK );
		}
	}

	public static function unschedule(): void {
		wp_clear_scheduled_hook( self::HOOK );
	}

	public static function run(): void {
		$months = max( 1, (int) apply_filters( 'ifm_retention_months', 12 ) );
		$before = gmdate( 'Y-m-d H:i:s', strtotime( '-' . $months . ' months' ) );

		$leads = get_posts(
			array(
				'post_type'      => IFM_Leads::CPT,
				'post_status'    => 'any',
				'posts_per_page' => 50,
				'fields'         => 'ids',
				'date_query'     => array( array( 'before' => $before ) ),
			)
		);
		foreach ( $leads as $id ) {
			wp_delete_post( (int) $id, true );
		}

		if ( ! function_exists( 'wc_get_orders' ) ) {
			return;
		}
		$orders = wc_get_orders(
			array(
				'limit'         => 20,
				'status'        => array( 'wc-completed', 'wc-delivered' ),
				'date_modified' => '<' . strtotime( $before ),
				'meta_key'      => '_ifm_brief_files', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_compare'  => 'EXISTS',
			)
		);
		foreach ( $orders as $order ) {
			$files = (array) $order->get_meta( '_ifm_brief_files' );
			if ( ! $files ) {
				continue;
			}
			$dir = IFM_Brief::base_dir() . '/' . $order->get_id();
			if ( is_dir( $dir ) ) {
				foreach ( (array) glob( $dir . '/*' ) as $file ) {
					if ( is_file( $file ) ) {
						wp_delete_file( $file );
					}
				}
				@rmdir( $dir ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- empty folder only.
			}
			$order->delete_meta_data( '_ifm_brief_files' );
			$order->add_order_note( __( 'Uploaded project files deleted automatically, 12 months after the order was finished (privacy policy).', 'iamfamous-core' ) );
			$order->save();
		}
	}
}
