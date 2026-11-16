<?php
/**
 * Content checks on the generated page patterns and data files (no WordPress needed).
 * Run: php tests/content-smoke.php
 */
$root = dirname( __DIR__ );
$fail = 0;
function check( string $name, bool $ok, string $extra = '' ): void {
	global $fail;
	echo ( $ok ? 'PASS ' : 'FAIL ' ) . $name . ( $ok || '' === $extra ? '' : ' — ' . $extra ) . "\n";
	$fail += $ok ? 0 : 1;
}
define( 'ABSPATH', '/' );
$manifest = require $root . '/wp-content/themes/iamfamous/inc/site-manifest.php';
$pages    = $manifest['pages'];
$files    = glob( $root . '/wp-content/themes/iamfamous/patterns/*.php' );
$all      = '';
foreach ( $files as $f ) { $all .= file_get_contents( $f ); }
$all .= file_get_contents( $root . '/wp-content/themes/iamfamous/parts/footer.html' );

check( 'manifest has 34 pages and 7 guides', 34 === count( $pages ) && 7 === count( $manifest['posts'] ) );
$bad = array_filter( $pages, fn( $p ) => '' === $p['seo_title'] || strlen( $p['seo_title'] ) > 70 || strlen( $p['excerpt'] ) < 70 || strlen( $p['excerpt'] ) > 158 || '' === $p['kw'] );
check( 'every page has an SEO title (max 70), a description (70 to 158) and a focus keyword', ! $bad, implode( ', ', array_column( $bad, 'slug' ) ) );
check( 'SEO titles and descriptions are unique', count( array_unique( array_column( $pages, 'seo_title' ) ) ) === count( $pages ) && count( array_unique( array_column( $pages, 'excerpt' ) ) ) === count( $pages ) );
$h1 = array_filter( $files, fn( $f ) => str_contains( basename( $f ), 'page-' ) && 1 !== substr_count( file_get_contents( $f ), '<h1' ) );
check( 'every page pattern has exactly one H1', ! $h1, implode( ', ', array_map( 'basename', $h1 ) ) );
check( 'no banned filler words', ! preg_match( '/\b(unlock|elevate|supercharge|digital landscape|cutting-edge|seamless|game-changer|pride ourselves|one-stop|tailored|next level|results-driven)\b/i', $all, $m ), $m[0] ?? '' );
check( 'nothing mentions the other brand, dollars or a named founder', ! preg_match( '/seo.?booster|\$\d|Zain/i', $all, $m ), $m[0] ?? '' );
check( 'no hard-coded pound prices in page copy (prices come from WooCommerce)', ! preg_match( '/£\d/', preg_replace( '/Ad budgets[^<]*/', '', $all ), $m ), $m[0] ?? '' );
preg_match_all( '#href="(/[a-z0-9/-]*?)(?:\#[a-z0-9-]*)?(?:\?[^"]*)?"#', $all, $links );
$known = array( '/', '/insights/', '/my-account/', '/my-account/orders/', '/basket/' );
check( 'the sole trader is named in the footer and the terms', str_contains( file_get_contents( $root . '/wp-content/themes/iamfamous/parts/footer.html' ), 'trading name of Mehreen' ) && str_contains( file_get_contents( $root . '/wp-content/themes/iamfamous/patterns/page-terms.php' ), 'Mehreen, a sole trader trading as IamFamous' ) );
foreach ( $pages as $p ) { $known[] = ( $p['parent'] ? '/' . $p['parent'] : '' ) . '/' . ( 'home' === $p['slug'] ? '' : $p['slug'] . '/' ); }
$dead = array_diff( array_unique( $links[1] ), $known, array( '' ) );
check( 'every internal link points at a page that exists', ! $dead, implode( ' ', $dead ) );
$reviews = require $root . '/wp-content/plugins/iamfamous-core/data/reviews.php';
$src     = file_get_contents( $root . '/reference/owner-testimonials-2026-11-15.md' );
check( '28 customer quotes, each word for word from the owner\'s file', 28 === count( $reviews ) && ! array_filter( $reviews, fn( $r ) => ! str_contains( $src, $r['text'] ) ) );
check( 'no star ratings or review schema are generated', ! preg_match( '/AggregateRating|"Review"|ratingValue/', file_get_contents( $root . '/wp-content/plugins/iamfamous-core/includes/class-ifm-schema.php' ) . file_get_contents( $root . '/wp-content/plugins/iamfamous-core/includes/class-ifm-reviews.php' ) ) );
check( 'business address appears in the footer and on the legal pages', str_contains( file_get_contents( $root . '/wp-content/themes/iamfamous/parts/footer.html' ), 'WC1N 3AX' ) && str_contains( file_get_contents( $root . '/wp-content/themes/iamfamous/patterns/page-terms.php' ), '27 Old Gloucester Street' ) );
echo $fail ? "\n{$fail} FAILED\n" : "\nALL PASSED\n";
exit( $fail ? 1 : 0 );
