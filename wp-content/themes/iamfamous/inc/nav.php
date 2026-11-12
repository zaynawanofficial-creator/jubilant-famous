<?php
/**
 * Main navigation: desktop menu with a Services mega panel, and a slide-in drawer for tablets and phones.
 *
 * Why custom: the core Navigation overlay is position:fixed inside the sticky header, so any header effect
 * (blur, transform) traps it in a 64px strip ("only Home shows"). It also switches to the toggle at 600px,
 * when the full menu no longer fits from about 1024px. This version controls both.
 *
 * Edit the links in ifm_nav_items() or with the 'ifm_nav_items' filter.
 *
 * @package IamFamousTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Menu structure. Top-level items with 'groups' render as the mega panel.
 *
 * @return array<int, array<string, mixed>>
 */
function ifm_nav_items(): array {
	$items = array(
		array(
			'label' => __( 'Home', 'iamfamous' ),
			'url'   => '/',
		),
		array(
			'label' => __( 'Services', 'iamfamous' ),
			'url'   => '/services/',
		),
		array(
			'label' => __( 'Local SEO', 'iamfamous' ),
			'url'   => '/services/local-seo/',
		),
		array(
			'label' => __( 'Websites', 'iamfamous' ),
			'url'   => '/services/website-development/',
		),
		array(
			'label' => __( 'Social media', 'iamfamous' ),
			'url'   => '/services/social-media-management/',
		),
		array(
			'label' => __( 'Contact', 'iamfamous' ),
			'url'   => '/contact/',
		),
	);
	/*
	 * Round 1 has three service pages, so the menu is flat. When more service pages go live, give the
	 * Services item a 'groups' array and it becomes the mega panel (desktop) and an accordion (drawer):
	 *
	 *   'groups' => array(
	 *       array( 'label' => 'SEO', 'links' => array( array( 'Local SEO', '/services/local-seo/' ), … ) ),
	 *       …
	 *   ),
	 */
	return (array) apply_filters( 'ifm_nav_items', $items );
}

/** Current request path, e.g. "/services/local-seo/". */
function ifm_nav_path(): string {
	$path = (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/', PHP_URL_PATH );
	$home = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	if ( '' !== $home && '/' !== $home && str_starts_with( $path, rtrim( $home, '/' ) ) ) {
		$path = substr( $path, strlen( rtrim( $home, '/' ) ) );
	}
	return trailingslashit( '/' . ltrim( $path, '/' ) );
}

/** Is this URL the current page (exact) or a parent section (prefix)? */
function ifm_nav_state( string $url, bool $prefix = false ): string {
	$path = ifm_nav_path();
	if ( $path === $url ) {
		return 'page';
	}
	if ( $prefix && '/' !== $url && str_starts_with( $path, $url ) ) {
		return 'section';
	}
	return '';
}

/** Insights posts live at the root, so treat single posts as part of Insights. */
function ifm_nav_is_active( array $item ): bool {
	if ( ! empty( $item['groups'] ) ) {
		return '' !== ifm_nav_state( '/services/', true ) || ( function_exists( 'is_product' ) && is_product() );
	}
	if ( '/services/' === $item['url'] ) {
		// Flat menu: the hub is active on itself and on package pages; each service link marks its own page.
		return 'page' === ifm_nav_state( '/services/' ) || ( function_exists( 'is_product' ) && is_product() );
	}
	if ( '/insights/' === $item['url'] && ( is_singular( 'post' ) || is_category() || is_tag() ) ) {
		return true;
	}
	return '' !== ifm_nav_state( $item['url'], '/' !== $item['url'] );
}

function ifm_nav_link( string $label, string $url, string $class ): string {
	$current = 'page' === ifm_nav_state( $url ) ? ' aria-current="page"' : '';
	return '<a class="' . esc_attr( $class ) . '" href="' . esc_url( home_url( $url ) ) . '"' . $current . '>' . esc_html( $label ) . '</a>';
}

function ifm_chevron(): string {
	return '<svg class="ifm-nav__chev" width="12" height="12" viewBox="0 0 12 12" aria-hidden="true" focusable="false"><path d="M2 4.25 6 8l4-3.75" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

/**
 * Shortcode output is swapped in after the Shortcode block's wpautop, so the markup is never split by <p>/<br>.
 */
function ifm_raw( string $html ): string {
	static $n = 0;
	++$n;
	$GLOBALS['ifm_raw'][ $n ] = $html;
	return '<!--ifm-raw:' . $n . '-->';
}

function ifm_unraw( string $html ): string {
	if ( ! str_contains( $html, '<!--ifm-raw:' ) ) {
		return $html;
	}
	return (string) preg_replace_callback(
		'#(?:<p>\s*)?<!--ifm-raw:(\d+)-->(?:\s*(?:<br\s*/?>\s*)?</p>)?#',
		static fn( $m ) => $GLOBALS['ifm_raw'][ (int) $m[1] ] ?? '',
		$html
	);
}

/** [ifm_nav] desktop menu (hidden below 1024px by CSS). */
function ifm_nav_shortcode(): string {
	$out = '<nav class="ifm-nav" aria-label="' . esc_attr__( 'Main', 'iamfamous' ) . '"><ul class="ifm-nav__list">';
	foreach ( ifm_nav_items() as $i => $item ) {
		$active = ifm_nav_is_active( $item ) ? ' is-active' : '';
		if ( empty( $item['groups'] ) ) {
			$out .= '<li class="ifm-nav__item' . $active . '">' . ifm_nav_link( $item['label'], $item['url'], 'ifm-nav__link' ) . '</li>';
			continue;
		}
		$id   = 'ifm-mega-' . $i;
		$out .= '<li class="ifm-nav__item has-mega' . $active . '">';
		$out .= '<button type="button" class="ifm-nav__link ifm-nav__trigger" aria-expanded="false" aria-controls="' . esc_attr( $id ) . '">' . esc_html( $item['label'] ) . ifm_chevron() . '</button>';
		$out .= '<div class="ifm-mega" id="' . esc_attr( $id ) . '"><div class="ifm-mega__inner"><div class="ifm-mega__cols">';
		foreach ( $item['groups'] as $g ) {
			$out .= '<div class="ifm-mega__col"><p class="ifm-mega__h">' . esc_html( $g['label'] ) . '</p><ul>';
			foreach ( $g['links'] as $l ) {
				$out .= '<li>' . ifm_nav_link( $l[0], $l[1], 'ifm-mega__link' ) . '</li>';
			}
			$out .= '</ul></div>';
		}
		$out .= '</div><div class="ifm-mega__foot"><p>' . esc_html__( 'Fixed prices in pounds, a written scope and a report you can check on every order.', 'iamfamous' ) . '</p>';
		$out .= '<div class="ifm-mega__actions">' . ifm_nav_link( __( 'All services', 'iamfamous' ), $item['url'], 'ifm-mega__more' ) . ifm_nav_link( __( 'Free growth check', 'iamfamous' ), '/free-growth-check/', 'ifm-mega__btn' ) . '</div>';
		$out .= '</div></div></div></li>';
	}
	$out .= '</ul></nav>';
	return ifm_raw( $out );
}

/** [ifm_nav_toggle] menu button for tablets and phones; the drawer prints in the footer. */
function ifm_nav_toggle_shortcode(): string {
	$GLOBALS['ifm_drawer'] = true;
	return ifm_raw(
		'<div class="ifm-burger-wrap"><button type="button" class="ifm-burger" aria-expanded="false" aria-controls="ifm-drawer" aria-label="' . esc_attr__( 'Open menu', 'iamfamous' ) . '">'
		. '<span class="ifm-burger__lines" aria-hidden="true"><span></span><span></span><span></span></span><span class="ifm-burger__txt">' . esc_html__( 'Menu', 'iamfamous' ) . '</span></button></div>'
	);
}

function ifm_drawer(): void {
	if ( empty( $GLOBALS['ifm_drawer'] ) ) {
		return;
	}
	$logo = '<a class="ifm-drawer__brand" href="' . esc_url( home_url( '/' ) ) . '"><img src="' . esc_url( get_theme_file_uri( 'assets/images/logo.svg' ) ) . '" width="159" height="24" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '"></a>';
	$out  = '<div class="ifm-drawer" id="ifm-drawer" role="dialog" aria-modal="true" aria-label="' . esc_attr__( 'Site menu', 'iamfamous' ) . '" hidden>';
	$out .= '<div class="ifm-drawer__scrim" data-ifm-close></div><div class="ifm-drawer__panel">';
	$out .= '<div class="ifm-drawer__top"><div class="ifm-drawer__logo">' . $logo . '</div><button type="button" class="ifm-drawer__close" data-ifm-close aria-label="' . esc_attr__( 'Close menu', 'iamfamous' ) . '"><svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button></div>';
	$out .= '<div class="ifm-drawer__body">';
	$out .= '<form class="ifm-drawer__search" role="search" method="get" action="' . esc_url( home_url( '/' ) ) . '"><label class="screen-reader-text" for="ifm-drawer-s">' . esc_html__( 'Search', 'iamfamous' ) . '</label><input id="ifm-drawer-s" type="search" name="s" placeholder="' . esc_attr__( 'Search services and guides', 'iamfamous' ) . '"><button type="submit" aria-label="' . esc_attr__( 'Search', 'iamfamous' ) . '"><svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/><path d="m20 20-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button></form>';
	$out .= '<nav aria-label="' . esc_attr__( 'Mobile', 'iamfamous' ) . '"><ul class="ifm-drawer__list">';
	foreach ( ifm_nav_items() as $item ) {
		if ( empty( $item['groups'] ) ) {
			$out .= '<li>' . ifm_nav_link( $item['label'], $item['url'], 'ifm-drawer__link' ) . '</li>';
			continue;
		}
		$open = ifm_nav_is_active( $item ) ? ' open' : '';
		$out .= '<li class="ifm-drawer__services"><details' . $open . '><summary class="ifm-drawer__link">' . esc_html( $item['label'] ) . ifm_chevron() . '</summary><div class="ifm-drawer__groups">';
		foreach ( $item['groups'] as $g ) {
			$out .= '<div class="ifm-drawer__group"><p class="ifm-drawer__h">' . esc_html( $g['label'] ) . '</p><ul>';
			foreach ( $g['links'] as $l ) {
				$out .= '<li>' . ifm_nav_link( $l[0], $l[1], 'ifm-drawer__sublink' ) . '</li>';
			}
			$out .= '</ul></div>';
		}
		$out .= '<p class="ifm-drawer__all">' . ifm_nav_link( __( 'View all services', 'iamfamous' ), $item['url'], '' ) . '</p></div></details></li>';
	}
	$out .= '</ul></nav></div>';
	$account = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
	$out    .= '<div class="ifm-drawer__foot"><a class="ifm-drawer__btn is-primary" href="' . esc_url( home_url( '/free-growth-check/' ) ) . '">' . esc_html__( 'Free growth check', 'iamfamous' ) . '</a><a class="ifm-drawer__btn" href="' . esc_url( home_url( '/services/' ) ) . '">' . esc_html__( 'See all services', 'iamfamous' ) . '</a>';
	$out    .= '<p class="ifm-drawer__meta"><a href="' . esc_url( $account ) . '">' . esc_html__( 'My account', 'iamfamous' ) . '</a><a href="mailto:support@iamfamous.uk">support@iamfamous.uk</a></p></div>';
	$out    .= '</div></div>';
	echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts above.
}

add_action(
	'init',
	static function () {
		add_shortcode( 'ifm_nav', 'ifm_nav_shortcode' );
		add_shortcode( 'ifm_nav_toggle', 'ifm_nav_toggle_shortcode' );
	}
);
add_action( 'wp_footer', 'ifm_drawer', 5 );
add_action(
	'wp_enqueue_scripts',
	static function () {
		$file = get_theme_file_path( 'assets/js/nav.js' );
		wp_enqueue_script( 'iamfamous-nav', get_theme_file_uri( 'assets/js/nav.js' ), array(), IFM_THEME_VERSION . '.' . (string) filemtime( $file ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}
);
