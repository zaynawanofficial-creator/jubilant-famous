<?php
/**
 * [ifm_reviews] — customer feedback from data/reviews.php.
 *
 * Usage: [ifm_reviews service="ppc-management,general" count="3"]   quotes about a service, topped up from the next tag
 *        [ifm_reviews count="0"]                                     every quote, grouped by service (reviews page)
 *
 * Quotes are shown exactly as supplied, without names, star ratings or review schema.
 *
 * @package IamFamousCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Customer feedback.
 */
class IFM_Reviews {

	public static function init(): void {
		add_shortcode( 'ifm_reviews', array( __CLASS__, 'render' ) );
	}

	/** @return array<int,array{tag:string,label:string,text:string}> */
	public static function all(): array {
		static $all = null;
		if ( null === $all ) {
			$all = (array) apply_filters( 'ifm_reviews', (array) require IFM_CORE_DIR . 'data/reviews.php' );
		}
		return $all;
	}

	/**
	 * Quotes for a list of tags, in tag order, up to $count.
	 *
	 * @param string[] $tags  Tags in priority order.
	 * @param int      $count Maximum number (0 = all).
	 */
	public static function pick( array $tags, int $count ): array {
		$out = array();
		foreach ( $tags as $tag ) {
			foreach ( self::all() as $r ) {
				if ( $r['tag'] === $tag ) {
					$out[] = $r;
					if ( $count && count( $out ) >= $count ) {
						return $out;
					}
				}
			}
		}
		return $out;
	}

	private static function card( array $r ): string {
		return '<figure class="ifm-review"><blockquote><p>' . esc_html( $r['text'] ) . '</p></blockquote><figcaption><span class="ifm-review__who">' . esc_html__( 'IamFamous customer', 'iamfamous-core' ) . '</span><span class="ifm-review__what">' . esc_html( $r['label'] ) . '</span></figcaption></figure>';
	}

	public static function render( $atts ): string {
		$atts  = shortcode_atts( array( 'service' => '', 'count' => '3' ), $atts, 'ifm_reviews' );
		$count = max( 0, (int) $atts['count'] );
		if ( 0 === $count && '' === $atts['service'] ) {
			// Everything, grouped by label in file order.
			$groups = array();
			foreach ( self::all() as $r ) {
				$groups[ $r['label'] ][] = $r;
			}
			$out = '';
			foreach ( $groups as $label => $items ) {
				$out .= '<section class="ifm-reviews-group"><h2>' . esc_html( $label ) . '</h2><div class="ifm-reviews ifm-rail ifm-rail--3">' . implode( '', array_map( array( __CLASS__, 'card' ), $items ) ) . '</div></section>';
			}
			return $out;
		}
		$tags  = array_filter( array_map( 'sanitize_title', explode( ',', (string) $atts['service'] ) ) );
		$items = self::pick( $tags ? $tags : array( 'general' ), $count );
		if ( ! $items ) {
			return '';
		}
		return '<div class="ifm-reviews ifm-rail ifm-rail--3">' . implode( '', array_map( array( __CLASS__, 'card' ), $items ) ) . '</div>';
	}
}
