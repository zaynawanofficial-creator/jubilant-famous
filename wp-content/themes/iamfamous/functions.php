<?php
/**
 * IamFamous theme bootstrap.
 *
 * @package IamFamousTheme
 */

defined( 'ABSPATH' ) || exit;

define( 'IFM_THEME_VERSION', '1.4.0' );

add_action(
	'after_setup_theme',
	static function () {
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'editor-styles' );
		add_theme_support( 'woocommerce' );
		add_theme_support( 'responsive-embeds' );
		add_editor_style( 'style.css' );
	}
);

add_action(
	'wp_enqueue_scripts',
	static function () {
		// Version from the file's modified time, so every theme update busts browser and optimiser caches.
		wp_enqueue_style( 'iamfamous', get_stylesheet_uri(), array(), IFM_THEME_VERSION . '.' . (string) filemtime( get_stylesheet_directory() . '/style.css' ) );
	}
);

/*
 * Critical header CSS printed inline, so the mobile header is correct even if a cached or "optimised"
 * stylesheet is stale (LiteSpeed CSS combine / UCSS can keep old copies per page).
 */
add_action(
	'wp_head',
	static function () {
		echo '<style id="ifm-critical">'
			. '@media (max-width:1080px){.ifm-header .ifm-header__cta,.ifm-header .ifm-header__search{display:none!important}}'
			. '@media (max-width:600px){.ifm-header .ifm-header__account{display:none!important}.ifm-header__inner{min-height:64px}.ifm-header__nav{gap:.5rem!important}}'
			. '.ifm-steps-bar{list-style:none!important;display:flex;flex-wrap:wrap;gap:.5rem;padding:0!important}'
			. '.ifm-header{backdrop-filter:none!important;-webkit-backdrop-filter:none!important;background:#fff}.ifm-burger-wrap{display:none}.ifm-drawer[hidden]{display:none}'
			. '@media (max-width:1023px){.ifm-header .ifm-nav{display:none!important}.ifm-burger-wrap{display:block}}'
			. '.ifm-shop-hero.ifm-hero{background:var(--wp--preset--gradient--plum-glow)!important}.ifm-shop-hero.ifm-hero h1{color:#fff!important}'
			. '.ifm-stickybar{display:none}.ifm-cookie[hidden]{display:none!important}'
			. '</style>' . "\n";
	},
	99
);

/*
 * Shortcodes inside patterns used by templates (e.g. [ifm_breadcrumbs] in the Insights hero) are expanded after
 * WordPress has already run do_shortcode on the template, so run it on the rendered Shortcode block.
 */
add_filter(
	'render_block_core/shortcode',
	static function ( string $html ): string {
		$html = str_contains( $html, '[' ) ? do_shortcode( $html ) : $html;
		return ifm_unraw( $html );
	}
);

// Preload the two variable fonts used above the fold.
add_action(
	'wp_head',
	static function () {
		foreach ( array( 'bricolage-grotesque-latin-wght-normal.woff2', 'figtree-latin-wght-normal.woff2' ) as $font ) {
			printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( get_theme_file_uri( 'assets/fonts/' . $font ) ) );
		}
	},
	1
);

add_action(
	'init',
	static function () {
		register_block_pattern_category( 'iamfamous-pages', array( 'label' => __( 'IamFamous: full pages', 'iamfamous' ) ) );
		register_block_pattern_category( 'iamfamous-sections', array( 'label' => __( 'IamFamous: sections', 'iamfamous' ) ) );
	}
);

// Fallback year shortcode if the companion plugin is inactive.
add_action(
	'init',
	static function () {
		if ( ! shortcode_exists( 'ifm_year' ) ) {
			add_shortcode( 'ifm_year', static fn() => esc_html( wp_date( 'Y' ) ) );
		}
		if ( ! shortcode_exists( 'ifm_currency' ) ) {
			add_shortcode( 'ifm_currency', static fn() => function_exists( 'get_woocommerce_currency' ) ? esc_html( get_woocommerce_currency() ) : 'GBP' );
		}
		if ( ! shortcode_exists( 'ifm_breadcrumbs' ) ) {
			add_shortcode( 'ifm_breadcrumbs', '__return_empty_string' );
		}
	},
	20
);

// Site icon fallback before a Site Icon is set in WordPress.
add_action(
	'wp_head',
	static function () {
		if ( ! has_site_icon() ) {
			echo '<link rel="icon" href="' . esc_url( get_theme_file_uri( 'assets/images/favicon.svg' ) ) . '" type="image/svg+xml">' . "\n";
		}
	}
);

/**
 * Tables with the "ifm-table" class: copy header labels onto cells so they can stack on phones.
 * Front-end only (saved block markup stays untouched, so the editor keeps validating).
 */
add_filter(
	'render_block_core/table',
	static function ( string $html, array $block ): string {
		if ( empty( $block['attrs']['className'] ) || ! str_contains( $block['attrs']['className'], 'ifm-table' ) || ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
			return $html;
		}
		if ( ! preg_match( '#<thead>(.*?)</thead>#s', $html, $m ) || ! preg_match_all( '#<th[^>]*>(.*?)</th>#s', $m[1], $th ) ) {
			return $html;
		}
		$labels = array_map( static fn( $t ) => trim( wp_strip_all_tags( $t ) ), $th[1] );
		$p      = new WP_HTML_Tag_Processor( $html );
		$i      = 0;
		while ( $p->next_tag() ) {
			$tag = $p->get_tag();
			if ( 'TR' === $tag ) {
				$i = 0;
			} elseif ( 'TD' === $tag ) {
				if ( isset( $labels[ $i ] ) && '' !== $labels[ $i ] ) {
					$p->set_attribute( 'data-label', $labels[ $i ] );
				}
				++$i;
			}
		}
		return $p->get_updated_html();
	},
	10,
	2
);

/*
 * Small front-end script: section reveal (skipped for reduced motion) and the sticky "Choose a plan" bar.
 */
add_action(
	'wp_enqueue_scripts',
	static function () {
		$file = get_theme_file_path( 'assets/js/site.js' );
		wp_enqueue_script( 'iamfamous-site', get_theme_file_uri( 'assets/js/site.js' ), array(), IFM_THEME_VERSION . '.' . (string) filemtime( $file ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}
);

/**
 * Sticky "Choose a plan" bar on service pages (phones and small tablets only, shown by CSS + site.js
 * once the hero has scrolled away and hidden again while the plans section is on screen).
 */
add_action(
	'wp_footer',
	static function () {
		if ( ! is_page() ) {
			return;
		}
		$page   = get_queried_object();
		$parent = $page && $page->post_parent ? get_post( $page->post_parent ) : null;
		if ( ! $parent || 'services' !== $parent->post_name ) {
			return;
		}
		$from = shortcode_exists( 'ifm_from' ) ? do_shortcode( '[ifm_from service="' . sanitize_title( $page->post_name ) . '"]' ) : '';
		$has  = '' !== $from && ! str_contains( $from, 'quote' );
		echo '<div class="ifm-stickybar" data-ifm-stickybar hidden><p class="ifm-stickybar__txt"><strong>' . esc_html( get_the_title( $page ) ) . '</strong>';
		if ( $has ) {
			/* translators: %s: price */
			echo '<span>' . esc_html( sprintf( __( 'Plans from %s', 'iamfamous' ), $from ) ) . '</span>';
		} else {
			echo '<span>' . esc_html__( 'Fixed-price plans', 'iamfamous' ) . '</span>';
		}
		echo '</p><a class="ifm-stickybar__btn" href="#plans">' . esc_html__( 'Choose a plan', 'iamfamous' ) . '</a></div>';
	},
	6
);

/**
 * Cookie banner. Printed hidden; assets/js/site.js shows it until the visitor chooses, stores the choice in the
 * "ifm_consent" cookie (all | essential) for 180 days and fires an "ifm:consent" event. Optional scripts added
 * later must check window.ifmConsent() === 'all' before loading. "Cookie settings" in the footer reopens it.
 */
add_action(
	'wp_footer',
	static function () {
		$policy = get_page_by_path( 'cookie-policy' );
		echo '<div class="ifm-cookie" id="ifm-cookie" role="region" aria-label="' . esc_attr__( 'Cookie choices', 'iamfamous' ) . '" hidden>';
		echo '<p class="ifm-cookie__text"><strong>' . esc_html__( 'Cookies', 'iamfamous' ) . '</strong> ' . esc_html__( 'We use essential cookies to run the basket and login. Optional cookies are only used if you allow them.', 'iamfamous' );
		if ( $policy ) {
			echo ' <a href="' . esc_url( get_permalink( $policy ) ) . '">' . esc_html__( 'Cookie policy', 'iamfamous' ) . '</a>';
		}
		echo '</p><div class="ifm-cookie__actions"><button type="button" class="ifm-cookie__btn" data-ifm-consent="essential">' . esc_html__( 'Essential only', 'iamfamous' ) . '</button>';
		echo '<button type="button" class="ifm-cookie__btn is-primary" data-ifm-consent="all">' . esc_html__( 'Allow all', 'iamfamous' ) . '</button></div></div>';
	},
	7
);

require_once get_theme_file_path( 'inc/setup.php' );
require_once get_theme_file_path( 'inc/nav.php' );
require_once get_theme_file_path( 'inc/post.php' );
