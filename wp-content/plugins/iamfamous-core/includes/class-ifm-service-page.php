<?php
/**
 * Service details page parts for a package (product) page:
 * [ifm_service_hero], [ifm_service_body], [ifm_service_more]. The theme's single-product template lays them out
 * around [ifm_order_form].
 *
 * @package IamFamousCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Package detail page.
 */
class IFM_Service_Page {

	public static function init(): void {
		add_shortcode( 'ifm_service_hero', array( __CLASS__, 'hero' ) );
		add_shortcode( 'ifm_service_body', array( __CLASS__, 'body' ) );
		add_shortcode( 'ifm_service_more', array( __CLASS__, 'more' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ), 20 );
	}

	public static function assets(): void {
		if ( function_exists( 'is_product' ) && ( is_product() || is_cart() || is_checkout() ) ) {
			wp_enqueue_style( 'ifm-packages' );
		}
	}

	private static function product(): ?WC_Product {
		$p = wc_get_product( get_the_ID() );
		return $p && IFM_Packages::is_package( $p ) ? $p : null;
	}

	/** Service (category) name and its service page URL. */
	public static function service( WC_Product $p ): array {
		$cats = get_the_terms( $p->get_id(), 'product_cat' );
		if ( ! $cats || is_wp_error( $cats ) ) {
			return array( '', '', '' );
		}
		$page = get_page_by_path( 'services/' . $cats[0]->slug );
		return array( $cats[0]->name, $page ? get_permalink( $page ) : home_url( '/services/' ), $cats[0]->slug );
	}

	private static function group( WC_Product $p ): ?WP_Term {
		$g = wp_get_post_terms( $p->get_id(), IFM_Packages::TAX );
		return ( $g && ! is_wp_error( $g ) ) ? $g[0] : null;
	}

	public static function hero(): string {
		$p = self::product();
		if ( ! $p ) {
			return '';
		}
		list( $svc ) = self::service( $p );
		$group       = self::group( $p );
		$ideal       = (string) $p->get_meta( '_ifm_ideal_for' );
		$facts       = array_filter(
			array(
				__( 'Delivery', 'iamfamous-core' )  => (string) $p->get_meta( '_ifm_turnaround' ),
				__( 'Revisions', 'iamfamous-core' ) => (string) $p->get_meta( '_ifm_revisions' ),
				__( 'Group', 'iamfamous-core' )     => $group ? $group->name : '',
			)
		);
		ob_start();
		echo '<div class="ifm-svc-hero"><div class="ifm-svc-hero__text">';
		echo IFM_Site::breadcrumbs(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside.
		echo '<p class="ifm-eyebrow">' . esc_html( $svc ) . '</p>';
		echo '<h1 class="ifm-svc-hero__title">' . esc_html( $p->get_name() ) . '</h1>';
		if ( $p->get_short_description() ) {
			echo '<p class="ifm-lead">' . esc_html( wp_strip_all_tags( $p->get_short_description() ) ) . '</p>';
		}
		echo '<div class="ifm-svc-hero__buy"><span class="ifm-svc-hero__price">';
		echo 'quote' === $p->get_meta( '_ifm_pricing_model' ) ? esc_html__( 'Custom quote', 'iamfamous-core' ) : wp_kses_post( $p->get_price_html() );
		echo '</span><a class="wp-element-button ifm-btn ifm-btn--primary" href="#order">' . esc_html__( 'Start your order', 'iamfamous-core' ) . '</a></div>';
		if ( $ideal ) {
			echo '<p class="ifm-svc-hero__ideal"><strong>' . esc_html__( 'Ideal for:', 'iamfamous-core' ) . '</strong> ' . esc_html( $ideal ) . '</p>';
		}
		if ( $facts ) {
			echo '<dl class="ifm-svc-hero__facts">';
			foreach ( $facts as $k => $v ) {
				echo '<div><dt>' . esc_html( $k ) . '</dt><dd>' . esc_html( $v ) . '</dd></div>';
			}
			echo '</dl>';
		}
		echo '</div><div class="ifm-svc-hero__media">';
		echo $p->get_image_id() ? wp_get_attachment_image( $p->get_image_id(), 'large', false, array( 'fetchpriority' => 'high', 'alt' => $p->get_name() ) ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core function.
		echo '</div></div>';
		return (string) ob_get_clean();
	}

	private static function list_html( string $title, array $items, string $cls ): string {
		if ( ! $items ) {
			return '';
		}
		return '<section class="ifm-svc-block"><h2>' . esc_html( $title ) . '</h2><ul class="' . esc_attr( $cls ) . '">' . implode( '', array_map( static fn( $i ) => '<li>' . esc_html( $i ) . '</li>', $items ) ) . '</ul></section>';
	}

	public static function body(): string {
		$p = self::product();
		if ( ! $p ) {
			return '';
		}
		$steps = array(
			array( __( 'Order', 'iamfamous-core' ), __( 'Fill in the short form on this page, then pay at the secure checkout.', 'iamfamous-core' ) ),
			array( __( 'We check', 'iamfamous-core' ), __( 'We read what you sent. If something is missing we email you within one working day.', 'iamfamous-core' ) ),
			array( __( 'We do the work', 'iamfamous-core' ), __( 'A person on our team does the job and a second person checks it.', 'iamfamous-core' ) ),
			array( __( 'You get it', 'iamfamous-core' ), __( 'The links, files or report arrive by email and in your account. Changes are covered as shown above.', 'iamfamous-core' ) ),
		);
		ob_start();
		echo '<div class="ifm-svc-body">';
		echo self::list_html( __( "What's included", 'iamfamous-core' ), IFM_Packages::lines( $p, '_ifm_deliverables' ), 'ifm-ticks' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in list_html().
		echo '<div class="ifm-svc-body__two">';
		echo self::list_html( __( 'What we need from you', 'iamfamous-core' ), IFM_Packages::lines( $p, '_ifm_requirements' ), 'ifm-ticks ifm-ticks--blue' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in list_html().
		echo self::list_html( __( 'Not included', 'iamfamous-core' ), IFM_Packages::lines( $p, '_ifm_exclusions' ), 'ifm-crosses' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in list_html().
		echo '</div>';
		echo '<section class="ifm-svc-block"><h2>' . esc_html__( 'What happens after you order', 'iamfamous-core' ) . '</h2><ol class="ifm-svc-steps">';
		foreach ( $steps as $i => $s ) {
			echo '<li><span class="ifm-svc-steps__n">' . esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ) . '</span><strong>' . esc_html( $s[0] ) . '</strong><span>' . esc_html( $s[1] ) . '</span></li>';
		}
		echo '</ol></section>';
		echo '<section class="ifm-svc-block ifm-svc-faq"><h2>' . esc_html__( 'Good to know', 'iamfamous-core' ) . '</h2>';
		$faq = array(
			__( 'When does delivery start?', 'iamfamous-core' )  => __( 'The clock starts when we have the details asked for in the form on this page. Fill it in fully and we can begin as soon as you have paid.', 'iamfamous-core' ),
			__( 'Can I send files?', 'iamfamous-core' )          => __( 'Yes. Once you have ordered, you can upload logos, photos and documents from your account.', 'iamfamous-core' ),
			__( 'Is a result guaranteed?', 'iamfamous-core' )  => __( 'We guarantee the work listed above is done and checked. We cannot promise a position on Google, a number of followers or a number of enquiries, because those depend on things outside our control.', 'iamfamous-core' ),
			__( 'What if I want changes?', 'iamfamous-core' )    => __( 'The changes included are shown above. If you want something outside the package, we tell you the price first and only go ahead if you agree.', 'iamfamous-core' ),
		);
		foreach ( $faq as $q => $a ) {
			echo '<details><summary>' . esc_html( $q ) . '</summary><p>' . esc_html( $a ) . '</p></details>';
		}
		echo '</section></div>';
		return (string) ob_get_clean();
	}

	public static function more(): string {
		$p = self::product();
		if ( ! $p ) {
			return '';
		}
		list( $svc, $url, $slug ) = self::service( $p );
		$group                    = self::group( $p );
		ob_start();
		echo '<div class="ifm-svc-more"><div class="ifm-svc-more__head"><div><p class="ifm-eyebrow">' . esc_html__( 'Compare', 'iamfamous-core' ) . '</p><h2>' . esc_html( sprintf( /* translators: %s: service */ __( 'Other %s packages', 'iamfamous-core' ), $svc ) ) . '</h2></div>';
		echo '<a class="ifm-card__more" href="' . esc_url( $url ) . '">' . esc_html__( 'About this service', 'iamfamous-core' ) . ' →</a></div>';
		echo IFM_Shortcode::render( array( 'service' => $slug, 'group' => $group ? $group->slug : '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside.
		echo '</div>';
		return (string) ob_get_clean();
	}
}
