<?php
/**
 * 301 redirects from the previous iamfamous.uk site (Elementor build, 2025–26) to the new URLs.
 *
 * Safe by design: a redirect only fires when WordPress has already decided the request is a 404, so a page,
 * post or product that still exists at an old address is never hijacked. Edit the map with the
 * 'ifm_redirects' filter. Query strings are dropped on purpose (old add-to-cart links must not carry over).
 *
 * @package IamFamousCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Old URL → new URL map.
 */
class IFM_Redirects {

	public static function init(): void {
		add_action( 'template_redirect', array( __CLASS__, 'maybe_redirect' ), 1 );
	}

	/**
	 * First path segment(s) → target path. Keys have no leading or trailing slash.
	 *
	 * @return array<string,string>
	 */
	public static function map(): array {
		$map = array(
			// Old service pages.
			'web-development'         => '/services/website-development/',
			'google-ads-service'      => '/services/ppc-management/',
			'backlinks-service'       => '/services/off-page-seo/',
			'monthly-seo-service'     => '/services/monthly-seo/',
			'on-page-seo'             => '/services/on-page-seo/',
			'content-writing'         => '/services/content-writing/',
			'social-media-management' => '/services/social-media-management/',
			// Old company pages.
			'about-us'                => '/',
			'contact-us'              => '/contact/',
			'faqs'                    => '/services/',
			'blog'                    => '/insights/',
			'shop'                    => '/services/',
		);
		// Old WooCommerce product categories.
		$cats = array(
			'backlink-services'       => '/services/off-page-seo/',
			'content-writing'         => '/services/content-writing/',
			'google-ads'              => '/services/ppc-management/',
			'on-page-seo'             => '/services/on-page-seo/',
			'seo'                     => '/services/monthly-seo/',
			'social-media-management' => '/services/social-media-management/',
			'website-development'     => '/services/website-development/',
		);
		foreach ( $cats as $slug => $to ) {
			$map[ 'product-category/' . $slug ] = $to;
		}
		// The 48 old products, by old category.
		$products = array(
			'/services/off-page-seo/'            => array( 'authority-profile-boost', 'elite-backlinks-plan', 'premium-backlinks-plan', 'advanced-backlinks-plan', 'complete-link-starter', 'standard-backlinks-plan', 'monthly-profile-builder', 'basic-backlinks-plan', 'ultimate-backlinks-plan' ),
			'/services/content-writing/'         => array( 'content-advanced-plan', 'content-basic-plan', 'content-elite-plan', 'content-platinum-plan', 'content-premium-gold-plan', 'content-premium-plan', 'content-premium-silver-plan', 'content-standard-plan', 'content-starter-plan' ),
			'/services/ppc-management/'          => array( 'basic-campaign-setup', 'mini-ads-management', 'niche-launch-pack', 'quick-audit', 'starter-e-commerce', 'starter-growth-plan' ),
			'/services/on-page-seo/'             => array( 'advanced-plan-onpage', 'basic-plan', 'monthly-maintenance', 'standard-plan', 'starter-plan', 'ultimate-plan-onpage' ),
			'/services/monthly-seo/'             => array( 'seo-enterprise-plan', 'seo-professional-plan', 'seo-stratup-plan' ),
			'/services/social-media-management/' => array( 'sm-advanced-plan', 'sm-basic-plan', 'sm-premium-plan', 'sm-standard-plan', 'sm-stratup-plan', 'sm-ultimate-plan' ),
			'/services/website-development/'     => array( 'business-website-pro', 'landing-page-build', 'multi-page-mini-site', 'full-scale-web-development' ),
			'/services/ecommerce-website-development/' => array( 'e-commerce-starter' ),
			'/services/website-maintenance/'     => array( 'fix-tune', 'quick-fix' ),
			'/services/technical-seo/'           => array( 'speed-boost', 'starter-optimization' ),
		);
		foreach ( $products as $to => $slugs ) {
			foreach ( $slugs as $slug ) {
				$map[ 'product/' . $slug ] = $to;
			}
		}
		return (array) apply_filters( 'ifm_redirects', $map );
	}

	/** Target for a request path, or '' when there is no rule. */
	public static function target( string $path ): string {
		$path = strtolower( trim( $path, '/' ) );
		if ( '' === $path ) {
			return '';
		}
		$map = self::map();
		return $map[ $path ] ?? '';
	}

	public static function maybe_redirect(): void {
		if ( ! is_404() || is_admin() ) {
			return;
		}
		$path = (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '', PHP_URL_PATH );
		$home = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
		if ( '' !== $home && '/' !== $home && str_starts_with( $path, rtrim( $home, '/' ) ) ) {
			$path = substr( $path, strlen( rtrim( $home, '/' ) ) );
		}
		$to = self::target( $path );
		if ( '' === $to ) {
			return;
		}
		// Only send visitors to a page that exists; otherwise fall back to the services hub, then the home page.
		$page = get_page_by_path( trim( $to, '/' ) );
		if ( '/' !== $to && ! $page ) {
			$to = get_page_by_path( 'services' ) ? '/services/' : '/';
		}
		wp_safe_redirect( home_url( $to ), 301, 'IamFamous Core' );
		exit;
	}
}
