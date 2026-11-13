<?php
/**
 * Smoke test for the old-URL redirect map (IFM_Redirects) with minimal WordPress stubs.
 * Run: php tests/redirects-smoke.php
 */
define( 'ABSPATH', '/' );
function add_action( ...$a ) {}
function apply_filters( $h, $v ) { return $v; }
require __DIR__ . '/../wp-content/plugins/iamfamous-core/includes/class-ifm-redirects.php';

$fail = 0;
function check( string $name, bool $ok ): void {
	global $fail;
	echo ( $ok ? 'PASS ' : 'FAIL ' ) . $name . "\n";
	$fail += $ok ? 0 : 1;
}
$map = IFM_Redirects::map();
check( 'old service pages map to the new ones', '/services/website-development/' === IFM_Redirects::target( '/web-development/' ) && '/services/social-media-management/' === IFM_Redirects::target( 'social-media-management' ) );
check( 'old company pages are covered', '/contact/' === IFM_Redirects::target( '/contact-us/' ) && '/insights/' === IFM_Redirects::target( '/blog/' ) && '/' === IFM_Redirects::target( '/about-us' ) );
check( 'all 48 old products are covered', 48 === count( array_filter( array_keys( $map ), fn( $k ) => str_starts_with( $k, 'product/' ) ) ) );
$listed = array_filter( array_map( fn( $l ) => trim( (string) ( explode( '|', $l )[3] ?? '' ) ), file( __DIR__ . '/../reference/current-iamfamous-uk-products-2026-11-12.txt' ) ) );
$missing = array_filter( $listed, fn( $slug ) => ! isset( $map[ 'product/' . $slug ] ) );
check( 'every slug in the old product list has a rule (' . count( $listed ) . ' listed)', 48 === count( $listed ) && ! $missing );
check( 'targets are internal paths', ! array_filter( $map, fn( $t ) => ! str_starts_with( $t, '/' ) || str_contains( $t, '//' ) ) );
check( 'case and slashes are normalised', '/services/monthly-seo/' === IFM_Redirects::target( '/Product/SEO-Enterprise-Plan/' ) );
check( 'old services map to their own new pages', '/services/ppc-management/' === IFM_Redirects::target( '/google-ads-service/' ) && '/services/off-page-seo/' === IFM_Redirects::target( '/backlinks-service/' ) && '/services/content-writing/' === IFM_Redirects::target( '/product/content-basic-plan/' ) );
$pages = array_map( fn( $f ) => substr( basename( $f, '.php' ), 5 ), glob( __DIR__ . '/../wp-content/themes/iamfamous/patterns/page-*.php' ) );
$dead = array_filter( array_unique( $map ), fn( $t ) => '/' !== $t && '/insights/' !== $t && ! in_array( basename( $t ), $pages, true ) );
check( 'every redirect target is a page the theme builds', ! $dead );
check( 'unknown paths and the home page are left alone', '' === IFM_Redirects::target( '/' ) && '' === IFM_Redirects::target( '/something-new/' ) );
check( 'no rule shadows a new page', ! array_intersect( array_keys( $map ), array( 'services', 'contact', 'insights', 'free-growth-check', 'services/local-seo' ) ) );
echo $fail ? "\n{$fail} FAILED\n" : "\nALL PASSED\n";
exit( $fail ? 1 : 0 );
