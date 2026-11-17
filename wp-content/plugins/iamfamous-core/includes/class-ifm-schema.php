<?php
/**
 * Structured data for service pages and FAQs, plus the hand-off to Rank Math when it is active.
 *
 * - Service: name, description, provider, area served (United Kingdom) and the lowest published price.
 * - FAQPage: built from the page's own Details (accordion) blocks, so the markup always matches the visible text.
 * - With Rank Math: both are added to Rank Math's graph ('rank_math/json_ld'); transactional pages get noindex
 *   through Rank Math's own filter. Without an SEO plugin they are printed in wp_head.
 *
 * Nothing here promises a rich result: Google decides what it shows.
 *
 * @package IamFamousCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Service and FAQ schema.
 */
class IFM_Schema {

	public static function init(): void {
		add_action( 'wp_head', array( __CLASS__, 'head' ), 3 );
		add_filter( 'rank_math/json_ld', array( __CLASS__, 'rank_math' ), 20 );
		add_filter( 'rank_math/frontend/robots', array( __CLASS__, 'rank_math_robots' ) );
		// Plan (product) pages and shop categories are noindex, so keep them out of Rank Math's sitemap too.
		add_filter( 'rank_math/sitemap/exclude_post_type', static fn( $exclude, $type ) => in_array( $type, array( 'product' ), true ) ? true : $exclude, 10, 2 );
		// Basket, checkout and account pages redirect or are private: not for the sitemap.
		add_filter(
			'rank_math/sitemap/posts_to_exclude',
			static function ( $ids ) {
				foreach ( array( 'cart', 'checkout', 'myaccount', 'shop' ) as $page ) {
					$id = function_exists( 'wc_get_page_id' ) ? (int) wc_get_page_id( $page ) : 0;
					if ( $id > 0 ) {
						$ids[] = $id;
					}
				}
				return $ids;
			}
		);
		add_filter( 'rank_math/sitemap/exclude_taxonomy', static fn( $exclude, $tax ) => in_array( $tax, array( 'product_cat', 'product_tag' ), true ) ? true : $exclude, 10, 2 );
	}

	/** Is the current request a service page (a child of /services/)? Returns the page or null. */
	private static function service_page(): ?WP_Post {
		if ( ! is_page() ) {
			return null;
		}
		$page   = get_queried_object();
		$parent = $page instanceof WP_Post && $page->post_parent ? get_post( $page->post_parent ) : null;
		return ( $parent && 'services' === $parent->post_name ) ? $page : null;
	}

	public static function service_node(): array {
		$page = self::service_page();
		if ( ! $page ) {
			return array();
		}
		$node = array(
			'@type'       => 'Service',
			'@id'         => get_permalink( $page ) . '#service',
			'name'        => wp_strip_all_tags( get_the_title( $page ) ),
			'serviceType' => wp_strip_all_tags( get_the_title( $page ) ),
			'description' => wp_strip_all_tags( (string) $page->post_excerpt ),
			'url'         => get_permalink( $page ),
			'provider'    => array( '@type' => 'Organization', '@id' => home_url( '/#organization' ), 'name' => IFM_Site::business()['name'], 'url' => home_url( '/' ) ),
			'areaServed'  => array( '@type' => 'Country', 'name' => 'United Kingdom' ),
		);
		$min = IFM_Site::min_price( $page->post_name );
		if ( '' !== $min ) {
			$node['offers'] = array(
				'@type'         => 'AggregateOffer',
				'priceCurrency' => function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'GBP',
				'lowPrice'      => $min,
				'url'           => get_permalink( $page ) . '#plans',
			);
		}
		return array_filter( $node );
	}

	/** Questions and answers from the page's Details blocks. */
	public static function faq_items( ?WP_Post $post = null ): array {
		$post = $post ? $post : ( is_singular() ? get_queried_object() : null );
		if ( ! $post instanceof WP_Post || ! has_block( 'core/details', $post ) ) {
			return array();
		}
		$out  = array();
		$walk = static function ( array $blocks ) use ( &$walk, &$out ): void {
			foreach ( $blocks as $b ) {
				if ( 'core/details' === $b['blockName'] && preg_match( '#<summary>(.*?)</summary>#s', (string) $b['innerHTML'], $m ) ) {
					$answer = '';
					foreach ( (array) $b['innerBlocks'] as $inner ) {
						$answer .= ' ' . wp_strip_all_tags( do_shortcode( (string) $inner['innerHTML'] ) );
					}
					$q = trim( wp_strip_all_tags( $m[1] ) );
					$a = trim( preg_replace( '/\s+/', ' ', $answer ) );
					if ( '' !== $q && '' !== $a ) {
						$out[] = array( $q, $a );
					}
				} elseif ( ! empty( $b['innerBlocks'] ) ) {
					$walk( $b['innerBlocks'] );
				}
			}
		};
		$walk( parse_blocks( (string) $post->post_content ) );
		return $out;
	}

	public static function faq_node(): array {
		$items = self::faq_items();
		if ( ! $items ) {
			return array();
		}
		return array(
			'@type'      => 'FAQPage',
			'@id'        => get_permalink() . '#faq',
			'mainEntity' => array_map(
				static fn( $qa ) => array( '@type' => 'Question', 'name' => $qa[0], 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $qa[1] ) ),
				$items
			),
		);
	}

	public static function head(): void {
		if ( IFM_Site::seo_plugin_active() || is_admin() ) {
			return;
		}
		$graph = array_values( array_filter( array( self::service_node(), self::faq_node() ) ) );
		if ( $graph ) {
			echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
		}
	}

	/** Add our nodes to Rank Math's graph. */
	public static function rank_math( $data ) {
		if ( ! is_array( $data ) ) {
			return $data;
		}
		$service = self::service_node();
		if ( $service ) {
			$data['ifmService'] = $service;
		}
		$faq = self::faq_node();
		if ( $faq ) {
			$data['ifmFaq'] = $faq;
		}
		return $data;
	}

	/** Keep basket, checkout, account, plan and search pages out of the index when Rank Math writes the robots tag. */
	public static function rank_math_robots( $robots ) {
		if ( is_array( $robots ) && IFM_Site::is_private_page() ) {
			$robots['index']  = 'noindex';
			$robots['follow'] = 'follow';
		}
		return $robots;
	}
}
