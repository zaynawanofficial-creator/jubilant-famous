<?php
/**
 * Plugin Name:       IamFamous Core
 * Description:       Service packages, package picker, project briefs, order workflow, lead forms, SEO head tags and old-URL redirects for IamFamous.uk (WooCommerce).
 * Version:           1.4.1
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Requires Plugins:  woocommerce
 * Author:            IamFamous
 * Text Domain:       iamfamous-core
 * License:           GPL-2.0-or-later
 *
 * @package IamFamousCore
 */

defined( 'ABSPATH' ) || exit;

define( 'IFM_CORE_VERSION', '1.4.1' );
define( 'IFM_CORE_FILE', __FILE__ );
define( 'IFM_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'IFM_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once IFM_CORE_DIR . 'includes/class-ifm-packages.php';
require_once IFM_CORE_DIR . 'includes/class-ifm-shortcode.php';
require_once IFM_CORE_DIR . 'includes/class-ifm-orders.php';
require_once IFM_CORE_DIR . 'includes/class-ifm-brief.php';
require_once IFM_CORE_DIR . 'includes/class-ifm-importer.php';
require_once IFM_CORE_DIR . 'includes/class-ifm-site.php';
require_once IFM_CORE_DIR . 'includes/class-ifm-leads.php';
require_once IFM_CORE_DIR . 'includes/class-ifm-order-flow.php';
require_once IFM_CORE_DIR . 'includes/class-ifm-service-page.php';
require_once IFM_CORE_DIR . 'includes/class-ifm-redirects.php';
require_once IFM_CORE_DIR . 'includes/class-ifm-reviews.php';
require_once IFM_CORE_DIR . 'includes/class-ifm-schema.php';
require_once IFM_CORE_DIR . 'includes/class-ifm-addons.php';
require_once IFM_CORE_DIR . 'includes/class-ifm-cleanup.php';
require_once IFM_CORE_DIR . 'includes/class-ifm-checkout.php';

// Declare HPOS + cart/checkout blocks compatibility.
add_action(
	'before_woocommerce_init',
	static function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', IFM_CORE_FILE, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', IFM_CORE_FILE, true );
		}
	}
);

add_action(
	'plugins_loaded',
	static function () {
		// Old-URL redirects must work even while WooCommerce is being installed.
		IFM_Redirects::init();
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action(
				'admin_notices',
				static function () {
					echo '<div class="notice notice-error"><p>' . esc_html__( 'IamFamous Core requires WooCommerce.', 'iamfamous-core' ) . '</p></div>';
				}
			);
			return;
		}
		IFM_Packages::init();
		IFM_Shortcode::init();
		IFM_Orders::init();
		IFM_Brief::init();
		IFM_Importer::init();
		IFM_Site::init();
		IFM_Leads::init();
		IFM_Order_Flow::init();
		IFM_Service_Page::init();
		IFM_Reviews::init();
		IFM_Schema::init();
		IFM_Addons::init();
		IFM_Cleanup::init();
		IFM_Checkout::init();
	}
);

register_activation_hook(
	__FILE__,
	static function () {
		IFM_Packages::register_taxonomy();
		IFM_Brief::add_endpoint();
		IFM_Brief::protect_upload_dir();
		IFM_Leads::register();
		flush_rewrite_rules();
	}
);

register_deactivation_hook(
	__FILE__,
	static function () {
		IFM_Cleanup::unschedule();
		flush_rewrite_rules();
	}
);
