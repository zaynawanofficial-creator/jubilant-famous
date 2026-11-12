<?php
// TEST ONLY: prepares a local WordPress (see README.md): theme, plugin, catalogue import, Setup screen run.
$_SERVER['HTTP_HOST'] = '127.0.0.1:8899'; $_SERVER['REQUEST_URI'] = '/';
define( 'WP_ADMIN', true );
require $argv[1] . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/admin.php';
update_option( 'siteurl', 'http://127.0.0.1:8899' ); update_option( 'home', 'http://127.0.0.1:8899' );
update_option( 'permalink_structure', '/%postname%/' );
switch_theme( 'iamfamous' );
update_option( 'active_plugins', array( 'iamfamous-core/iamfamous-core.php' ) );
if ( ! class_exists( 'IFM_Importer' ) ) { echo "plugin not loaded this run; run again\n"; exit; }
$u = get_users( array( 'role' => 'administrator', 'number' => 1 ) ); wp_set_current_user( $u ? $u[0]->ID : 1 );
// Old-site stand-ins to prove the clean-up: a "services" page and "about-us".
foreach ( array( 'services' => 'Old Services (Elementor)', 'about-us' => 'About Us (old)' ) as $slug => $t ) {
	if ( ! get_page_by_path( $slug ) && ! get_page_by_path( $slug . '-old' ) ) { wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $t, 'post_content' => 'old' ) ); }
}
$csv = $argv[2];
echo implode( "\n", array_slice( IFM_Importer::import( $csv, false, true ), -3 ) ), "\n";
echo implode( "\n", IFM_Setup::run( array( 'pages' => true, 'overwrite' => true, 'posts' => true, 'reading' => true, 'brand' => true, 'legacy' => true, 'woo' => true ) ) ), "\n";
IFM_Site::flush_prices();
flush_rewrite_rules();
