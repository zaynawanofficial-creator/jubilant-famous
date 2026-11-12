<?php
/**
 * Smoke test for the theme navigation (inc/nav.php) with minimal WordPress stubs.
 * Run: php tests/nav-smoke.php
 */
define( 'ABSPATH', '/' );
define( 'IFM_THEME_VERSION', 'test' );
$_SERVER['REQUEST_URI'] = '/services/local-seo/';
function __( $s ) { return $s; }
function esc_html__( $s ) { return htmlspecialchars( $s ); }
function esc_attr__( $s ) { return htmlspecialchars( $s ); }
function esc_html( $s ) { return htmlspecialchars( (string) $s ); }
function esc_attr( $s ) { return htmlspecialchars( (string) $s ); }
function esc_url( $s ) { return htmlspecialchars( (string) $s ); }
function esc_url_raw( $s ) { return $s; }
function wp_unslash( $s ) { return $s; }
function wp_parse_url( $u, $c = -1 ) { return parse_url( $u, $c ); }
function home_url( $p = '/' ) { return 'https://example.test' . $p; }
function trailingslashit( $s ) { return rtrim( $s, '/' ) . '/'; }
function apply_filters( $h, $v ) { return isset( $GLOBALS['test_filters'][ $h ] ) ? $GLOBALS['test_filters'][ $h ]( $v ) : $v; }
function is_singular( $t = '' ) { return false; }
function is_category() { return false; }
function is_tag() { return false; }
function add_action( ...$a ) {}
function add_shortcode( ...$a ) {}
function has_custom_logo() { return false; }
function get_theme_file_uri( $f ) { return 'https://example.test/theme/' . $f; }
function get_bloginfo( $k ) { return 'IamFamous'; }
function wc_get_page_permalink( $p ) { return 'https://example.test/my-account/'; }
require __DIR__ . '/../wp-content/themes/iamfamous/inc/nav.php';

$fail = 0;
function check( string $name, bool $ok ): void {
	global $fail;
	echo ( $ok ? 'PASS ' : 'FAIL ' ) . $name . "\n";
	$fail += $ok ? 0 : 1;
}

$nav = ifm_unraw( ifm_nav_shortcode() );
$tog = ifm_unraw( '<p>' . ifm_nav_toggle_shortcode() . '</p>' );
ob_start();
ifm_drawer();
$drawer = ob_get_clean();

check( 'desktop menu has 6 top-level items', 6 === substr_count( $nav, 'class="ifm-nav__item' ) );
check( 'Home is the first item', (bool) preg_match( '#<ul class="ifm-nav__list"><li class="ifm-nav__item[^"]*"><a class="ifm-nav__link" href="https://example.test/">Home</a>#', $nav ) );
check( 'three live service pages are linked in the menu and the drawer', 3 === preg_match_all( '#href="https://example.test/services/[a-z-]+/"#', $nav ) && 3 === preg_match_all( '#href="https://example.test/services/[a-z-]+/"#', explode( 'ifm-drawer__foot', $drawer )[0] ) );
check( 'current page marked aria-current', str_contains( $nav, 'href="https://example.test/services/local-seo/" aria-current="page"' ) );
check( 'only the current service is active (hub is not)', 1 === substr_count( $nav, 'is-active' ) );
check( 'no link to a page that does not exist yet', ! preg_match( '#/(packages|about|faq|how-it-works|get-a-quote|terms|refund-policy)/#', $nav . $drawer ) );
check( 'drawer has the growth-check button and the support email', str_contains( $drawer, '/free-growth-check/' ) && str_contains( $drawer, 'mailto:support@iamfamous.uk' ) );
check( 'toggle unwrapped from <p> after wpautop', str_starts_with( $tog, '<div class="ifm-burger-wrap">' ) );
check( 'drawer starts hidden and is a modal dialog', str_contains( $drawer, 'role="dialog" aria-modal="true"' ) && str_contains( $drawer, ' hidden>' ) );
check( 'no placeholder left behind', ! str_contains( $nav . $tog, 'ifm-raw' ) );
check( 'nothing mentions the other brand', ! preg_match( '/seo.?booster/i', $nav . $drawer ) );

// Mega panel still works when later rounds add groups to the Services item.
$GLOBALS['test_filters']['ifm_nav_items'] = static function ( $items ) {
	$items[1]['groups'] = array(
		array( 'label' => 'SEO', 'links' => array( array( 'Local SEO', '/services/local-seo/' ), array( 'SEO Audit', '/services/seo-audit/' ) ) ),
		array( 'label' => 'Websites', 'links' => array( array( 'Website Development', '/services/website-development/' ) ) ),
	);
	return $items;
};
$mega = ifm_unraw( ifm_nav_shortcode() );
ob_start(); ifm_drawer(); $drawer2 = ob_get_clean();
check( 'with groups: mega panel lists the grouped links', 3 === substr_count( $mega, 'class="ifm-mega__link"' ) && str_contains( $mega, 'aria-expanded="false"' ) );
check( 'with groups: Services is active on a service page and open in the drawer', str_contains( $mega, 'has-mega is-active' ) && str_contains( $drawer2, '<details open>' ) );

echo $fail ? "\n{$fail} FAILED\n" : "\nALL PASSED\n";
exit( $fail ? 1 : 0 );
