<?php
/**
 * Basket, mini basket and checkout tuned for services rather than physical goods:
 *  - each plan can be bought once per order (no quantity box);
 *  - the item picture is the service's own icon (a rose tile), not a product photo;
 *  - item details are short: delivery time and "Project details: Received".
 *
 * @package IamFamousCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Service-shaped basket and checkout.
 */
class IFM_Checkout {

	/** Service (product_cat slug) => theme icon name (assets/images/icon-{name}.svg). */
	const ICONS = array(
		'local-seo'                     => 'local',
		'monthly-seo'                   => 'chart',
		'off-page-seo'                  => 'link',
		'seo-audit'                     => 'audit',
		'technical-seo'                 => 'wrench',
		'on-page-seo'                   => 'page',
		'ecommerce-seo'                 => 'cart',
		'ai-seo'                        => 'spark',
		'white-label-seo'               => 'label',
		'seo-consultancy'               => 'chat',
		'website-development'           => 'webdev',
		'ecommerce-website-development' => 'cart',
		'website-maintenance'           => 'wrench',
		'website-migration'             => 'move',
		'social-media-management'       => 'social',
		'ppc-management'                => 'target',
		'facebook-ads'                  => 'megaphone',
		'tiktok-ads'                    => 'video',
		'content-writing'               => 'pen',
		'graphic-design'                => 'palette',
		'bundles'                       => 'scope',
	);

	public static function init(): void {
		add_filter( 'woocommerce_is_sold_individually', array( __CLASS__, 'sold_individually' ), 10, 2 );
		add_filter( 'woocommerce_store_api_cart_item_images', array( __CLASS__, 'item_images' ), 10, 3 );
		add_filter( 'woocommerce_cart_item_thumbnail', array( __CLASS__, 'classic_thumbnail' ), 10, 3 );
	}

	public static function sold_individually( $yes, $product ) {
		$id = $product && $product->get_parent_id() ? $product->get_parent_id() : ( $product ? $product->get_id() : 0 );
		return $id && IFM_Packages::is_package( $id ) ? true : $yes;
	}

	/** Icon URL for the service a product belongs to, or '' if none. */
	public static function icon_url( int $product_id ): string {
		$cats = get_the_terms( $product_id, 'product_cat' );
		$name = 'scope';
		if ( $cats && ! is_wp_error( $cats ) ) {
			foreach ( $cats as $c ) {
				if ( isset( self::ICONS[ $c->slug ] ) ) {
					$name = self::ICONS[ $c->slug ];
					break;
				}
			}
		}
		$rel = 'assets/images/icon-' . $name . '.svg';
		return function_exists( 'get_theme_file_path' ) && is_readable( get_theme_file_path( $rel ) ) ? get_theme_file_uri( $rel ) : '';
	}

	/** Block basket / checkout / mini basket: replace the product image with the service icon. */
	public static function item_images( $images, $cart_item, $key = '' ) {
		$pid = (int) ( $cart_item['product_id'] ?? 0 );
		if ( ! $pid || ! IFM_Packages::is_package( $pid ) ) {
			return $images;
		}
		$url = self::icon_url( $pid );
		if ( '' === $url ) {
			return $images;
		}
		return array(
			(object) array(
				'id'        => 0,
				'src'       => $url,
				'thumbnail' => $url,
				'srcset'    => '',
				'sizes'     => '',
				'name'      => '',
				'alt'       => '',
			),
		);
	}

	/** Classic templates (fallback). */
	public static function classic_thumbnail( $html, $cart_item, $key ) {
		$pid = (int) ( $cart_item['product_id'] ?? 0 );
		$url = $pid && IFM_Packages::is_package( $pid ) ? self::icon_url( $pid ) : '';
		return $url ? '<img class="ifm-cart-icon" src="' . esc_url( $url ) . '" width="48" height="48" alt="">' : $html;
	}
}
