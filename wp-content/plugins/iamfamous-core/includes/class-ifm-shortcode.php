<?php
/**
 * [ifm_packages] — data-driven plan groups (tabs), a tier picker and a comparison table for any page.
 *
 * Usage: [ifm_packages service="off-page-seo"]  (service = product_cat slug)
 *        [ifm_packages service="off-page-seo" group="premium-links"]
 *
 * @package IamFamousCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders plans straight from WooCommerce data. No prices live in page content.
 */
class IFM_Shortcode {

	public static function init(): void {
		add_shortcode( 'ifm_packages', array( __CLASS__, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
	}

	public static function register_assets(): void {
		wp_register_style( 'ifm-packages', IFM_CORE_URL . 'assets/ifm-packages.css', array(), IFM_CORE_VERSION );
		wp_register_script( 'ifm-packages', IFM_CORE_URL . 'assets/ifm-packages.js', array(), IFM_CORE_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
	}

	/**
	 * Published plans for a service, grouped by plan group.
	 *
	 * @return array<int,array{term:WP_Term,products:WC_Product[]}>
	 */
	public static function grouped( string $service, string $group = '' ): array {
		$args = array(
			'status'   => 'publish',
			'limit'    => 100,
			'category' => array( $service ),
			'return'   => 'objects',
		);
		$products = wc_get_products( $args );
		$out      = array();
		foreach ( $products as $product ) {
			if ( ! IFM_Packages::is_package( $product ) || 'hidden' === $product->get_catalog_visibility() ) {
				continue;
			}
			$terms = wp_get_post_terms( $product->get_id(), IFM_Packages::TAX );
			if ( is_wp_error( $terms ) || ! $terms ) {
				continue;
			}
			$term = $terms[0];
			if ( $group && $term->slug !== $group ) {
				continue;
			}
			if ( ! isset( $out[ $term->term_id ] ) ) {
				$out[ $term->term_id ] = array( 'term' => $term, 'products' => array() );
			}
			$out[ $term->term_id ]['products'][] = $product;
		}
		foreach ( $out as &$g ) {
			usort(
				$g['products'],
				static fn( $a, $b ) => (int) $a->get_meta( '_ifm_sort' ) <=> (int) $b->get_meta( '_ifm_sort' )
			);
		}
		unset( $g );
		uasort(
			$out,
			static fn( $a, $b ) => (int) get_term_meta( $a['term']->term_id, '_ifm_group_sort', true ) <=> (int) get_term_meta( $b['term']->term_id, '_ifm_group_sort', true )
		);
		return array_values( $out );
	}

	public static function render( $atts ): string {
		$atts = shortcode_atts( array( 'service' => '', 'group' => '' ), $atts, 'ifm_packages' );
		$service = sanitize_title( $atts['service'] );
		if ( ! $service ) {
			return '';
		}
		$groups = self::grouped( $service, sanitize_title( $atts['group'] ) );
		if ( ! $groups ) {
			wp_enqueue_style( 'ifm-packages' );
			return '<div class="ifm-packages ifm-packages--empty"><p class="ifm-packages__empty-title">' . esc_html__( 'Prices for this service are being confirmed', 'iamfamous-core' ) . '</p><p>' . esc_html__( 'Tell us what you need and we will reply by email with a fixed price and a written scope. There is nothing to pay until you accept it.', 'iamfamous-core' ) . '</p><p><a class="ifm-btn ifm-btn--primary" href="' . esc_url( add_query_arg( 'service', $service, home_url( '/free-growth-check/' ) ) ) . '">' . esc_html__( 'Ask for a price', 'iamfamous-core' ) . '</a></p></div>';
		}
		wp_enqueue_style( 'ifm-packages' );
		wp_enqueue_script( 'ifm-packages' );

		$uid = 'ifmp-' . wp_unique_id();
		ob_start();
		echo '<div class="ifm-packages" id="' . esc_attr( $uid ) . '">';
		if ( count( $groups ) > 1 ) {
			echo '<div class="ifm-tabs" role="tablist" aria-label="' . esc_attr__( 'Plan groups', 'iamfamous-core' ) . '">';
			foreach ( $groups as $i => $g ) {
				$tid = $uid . '-tab-' . $i;
				$pid = $uid . '-panel-' . $i;
				printf(
					'<button type="button" class="ifm-tab" role="tab" id="%1$s" aria-controls="%2$s" aria-selected="%3$s" tabindex="%4$s">%5$s</button>',
					esc_attr( $tid ),
					esc_attr( $pid ),
					0 === $i ? 'true' : 'false',
					0 === $i ? '0' : '-1',
					esc_html( $g['term']->name )
				);
			}
			echo '</div>';
		}
		foreach ( $groups as $i => $g ) {
			$tid   = $uid . '-tab-' . $i;
			$pid   = $uid . '-panel-' . $i;
			$multi = count( $groups ) > 1;
			printf(
				'<section class="ifm-pkpanel" id="%1$s" %2$s %3$s>',
				esc_attr( $pid ),
				$multi ? 'role="tabpanel" aria-labelledby="' . esc_attr( $tid ) . '" tabindex="0"' : 'aria-label="' . esc_attr( $g['term']->name ) . '"',
				( $multi && $i > 0 ) ? 'hidden' : ''
			);
			// The intro duplicates the section text on single-group services, so it only shows with tabs.
			if ( $multi && $g['term']->description ) {
				echo '<p class="ifm-pkpanel__intro">' . esc_html( $g['term']->description ) . '</p>';
			}
			echo self::selector( $g['products'], $pid ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in selector().
			echo self::compare( $g['products'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in compare().
			echo '</section>';
		}
		echo '</div>';
		return (string) ob_get_clean();
	}

	/** "£0.85 per link" style unit price, when the plan has a countable quantity. */
	public static function unit_price( WC_Product $p ): string {
		$qty  = (int) $p->get_meta( '_ifm_quantity' );
		$unit = (string) $p->get_meta( '_ifm_unit' );
		$map  = array( 'links' => __( 'per link', 'iamfamous-core' ), 'articles' => __( 'per article', 'iamfamous-core' ), 'graphics' => __( 'per graphic', 'iamfamous-core' ), 'pages' => __( 'per page', 'iamfamous-core' ), 'products' => __( 'per product', 'iamfamous-core' ), 'posts' => __( 'per post', 'iamfamous-core' ), 'words' => __( 'per 100 words', 'iamfamous-core' ) );
		if ( $qty < 2 || ! isset( $map[ $unit ] ) || 'quote' === $p->get_meta( '_ifm_pricing_model' ) || ! (float) $p->get_price() ) {
			return '';
		}
		$each = (float) $p->get_price() / $qty * ( 'words' === $unit ? 100 : 1 );
		return wp_strip_all_tags( wc_price( $each ) ) . ' ' . $map[ $unit ];
	}

	/** Panel price: whole numbers without ".00"; sale prices keep WooCommerce's own del/ins markup. */
	private static function panel_price( WC_Product $p ): string {
		if ( $p->is_on_sale() ) {
			return $p->get_price_html();
		}
		$v = (float) wc_get_price_to_display( $p );
		return wc_price( $v, array( 'decimals' => floor( $v ) == $v ? 0 : 2 ) ); // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
	}

	private static function short_price( WC_Product $p ): string {
		if ( 'quote' === $p->get_meta( '_ifm_pricing_model' ) || ! $p->is_purchasable() ) {
			return esc_html__( 'Quote', 'iamfamous-core' );
		}
		$v = (float) $p->get_price();
		return esc_html( wp_strip_all_tags( wc_price( $v, array( 'decimals' => floor( $v ) == $v ? 0 : 2 ) ) ) ); // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
	}

	/**
	 * Compact plan picker: tier list (left) + one detail panel (right).
	 *
	 * @param WC_Product[] $products Group products, sorted.
	 */
	public static function selector( array $products, string $uid ): string {
		$default = 0;
		foreach ( $products as $i => $p ) {
			if ( '1' === $p->get_meta( '_ifm_featured' ) ) {
				$default = $i;
				break;
			}
		}
		ob_start();
		echo '<div class="ifm-pk" data-ifm-pk>';
		echo '<div class="ifm-pk__tiers" role="radiogroup" aria-label="' . esc_attr__( 'Choose a plan', 'iamfamous-core' ) . '">';
		foreach ( $products as $i => $p ) {
			$on    = $i === $default;
			$ideal = (string) $p->get_meta( '_ifm_ideal_for' );
			printf(
				'<button type="button" role="radio" class="ifm-pk__tier%1$s" aria-checked="%2$s" tabindex="%3$s" aria-controls="%4$s" data-panel="%4$s"><span class="ifm-pk__radio" aria-hidden="true"></span><span class="ifm-pk__name">%5$s%6$s</span>%7$s<span class="ifm-pk__tprice">%8$s</span></button>',
				'1' === $p->get_meta( '_ifm_featured' ) ? ' is-featured' : '',
				$on ? 'true' : 'false',
				$on ? '0' : '-1',
				esc_attr( $uid . '-p' . $i ),
				esc_html( $p->get_name() ),
				$ideal ? '<small>' . esc_html( $ideal ) . '</small>' : '',
				'1' === $p->get_meta( '_ifm_featured' ) ? '<span class="ifm-pk__pop">' . esc_html__( 'Popular', 'iamfamous-core' ) . '</span>' : '',
				self::short_price( $p ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in short_price().
			);
		}
		echo '</div><div class="ifm-pk__panels">';
		foreach ( $products as $i => $p ) {
			$quote = 'quote' === $p->get_meta( '_ifm_pricing_model' ) || ! $p->is_purchasable();
			$items = IFM_Packages::lines( $p, '_ifm_deliverables' );
			$excl  = IFM_Packages::lines( $p, '_ifm_exclusions' );
			$unit  = self::unit_price( $p );
			$facts = array_filter(
				array(
					__( 'Delivery', 'iamfamous-core' )  => (string) $p->get_meta( '_ifm_turnaround' ),
					__( 'Revisions', 'iamfamous-core' ) => (string) $p->get_meta( '_ifm_revisions' ),
				)
			);
			echo '<div class="ifm-pk__panel" id="' . esc_attr( $uid . '-p' . $i ) . '" role="region" aria-label="' . esc_attr( $p->get_name() ) . '"' . ( $i === $default ? '' : ' hidden' ) . '>';
			echo '<div class="ifm-pk__head"><div><h3 class="ifm-pk__title">' . esc_html( $p->get_name() ) . '</h3>';
			if ( $p->get_meta( '_ifm_ideal_for' ) ) {
				echo '<p class="ifm-pk__ideal">' . esc_html__( 'Ideal for:', 'iamfamous-core' ) . ' ' . esc_html( (string) $p->get_meta( '_ifm_ideal_for' ) ) . '</p>';
			}
			echo '</div><div class="ifm-pk__price' . ( $quote ? ' is-quote' : '' ) . '"><span class="ifm-pk__label">' . esc_html( $quote ? __( 'Price', 'iamfamous-core' ) : __( 'Fixed price', 'iamfamous-core' ) ) . '</span>';
			echo '<span class="ifm-pk__amount">' . ( $quote ? esc_html__( 'Custom quote', 'iamfamous-core' ) : wp_kses_post( self::panel_price( $p ) ) ) . '</span>';
			if ( $unit ) {
				echo '<span class="ifm-pk__unit">' . esc_html( $unit ) . '</span>';
			}
			echo '</div></div>';
			if ( $facts ) {
				echo '<ul class="ifm-pk__facts">';
				foreach ( $facts as $k => $v ) {
					echo '<li><span>' . esc_html( $k ) . '</span>' . esc_html( $v ) . '</li>';
				}
				echo '</ul>';
			}
			if ( $items ) {
				echo '<ul class="ifm-pk__list">' . implode( '', array_map( static fn( $x ) => '<li>' . esc_html( $x ) . '</li>', $items ) ) . '</ul>';
			}
			echo '<div class="ifm-pk__cta">';
			if ( $quote ) {
				echo '<a class="ifm-btn ifm-btn--primary" href="' . esc_url( IFM_Packages::quote_url( $p ) ) . '">' . esc_html__( 'Get a quote', 'iamfamous-core' ) . '</a>';
			} else {
				/* translators: %s: plan name */
				echo '<a class="ifm-btn ifm-btn--primary" href="' . esc_url( $p->get_permalink() . '#order' ) . '" aria-label="' . esc_attr( sprintf( __( 'Choose %s', 'iamfamous-core' ), $p->get_name() ) ) . '">' . esc_html__( 'Choose this plan', 'iamfamous-core' ) . ' →</a>';
			}
			echo '<a class="ifm-pk__more" href="' . esc_url( $p->get_permalink() ) . '">' . esc_html__( 'Full details', 'iamfamous-core' ) . '</a></div>';
			if ( $excl ) {
				echo '<details class="ifm-pk__excl"><summary>' . esc_html__( 'Not included', 'iamfamous-core' ) . '</summary><ul>' . implode( '', array_map( static fn( $x ) => '<li>' . esc_html( $x ) . '</li>', $excl ) ) . '</ul></details>';
			}
			echo '</div>';
		}
		echo '</div></div>';
		return (string) ob_get_clean();
	}

	/**
	 * Side-by-side comparison under the picker: one row per plan, stacked into cards on phones.
	 *
	 * @param WC_Product[] $products Group products, sorted.
	 */
	public static function compare( array $products ): string {
		if ( count( $products ) < 2 ) {
			return '';
		}
		$cols = array(
			'name'  => __( 'Plan', 'iamfamous-core' ),
			'ideal' => __( 'Best for', 'iamfamous-core' ),
			'turn'  => __( 'Delivery', 'iamfamous-core' ),
			'rev'   => __( 'Revisions', 'iamfamous-core' ),
			'price' => __( 'Price', 'iamfamous-core' ),
		);
		$out  = '<details class="ifm-pkcmp"><summary>' . esc_html__( 'Compare all plans side by side', 'iamfamous-core' ) . '</summary>';
		$out .= '<div class="ifm-pkcmp__scroll"><table class="ifm-pkcmp__table"><thead><tr>';
		foreach ( $cols as $label ) {
			$out .= '<th scope="col">' . esc_html( $label ) . '</th>';
		}
		$out .= '</tr></thead><tbody>';
		foreach ( $products as $p ) {
			$unit = self::unit_price( $p );
			$row  = array(
				'name'  => '<a href="' . esc_url( $p->get_permalink() ) . '">' . esc_html( $p->get_name() ) . '</a>' . ( '1' === $p->get_meta( '_ifm_featured' ) ? ' <span class="ifm-pk__pop">' . esc_html__( 'Popular', 'iamfamous-core' ) . '</span>' : '' ),
				'ideal' => esc_html( (string) $p->get_meta( '_ifm_ideal_for' ) ),
				'turn'  => esc_html( (string) $p->get_meta( '_ifm_turnaround' ) ),
				'rev'   => esc_html( (string) $p->get_meta( '_ifm_revisions' ) ),
				'price' => '<strong>' . self::short_price( $p ) . '</strong>' . ( $unit ? '<small>' . esc_html( $unit ) . '</small>' : '' ),
			);
			$out .= '<tr>';
			foreach ( $row as $key => $cell ) {
				$tag  = 'name' === $key ? 'th scope="row"' : 'td';
				$out .= '<' . $tag . ' data-label="' . esc_attr( $cols[ $key ] ) . '">' . ( '' !== $cell ? $cell : '&ndash;' ) . '</' . ( 'name' === $key ? 'th' : 'td' ) . '>';
			}
			$out .= '</tr>';
		}
		return $out . '</tbody></table></div></details>';
	}

	public static function card( WC_Product $product ): string {
		$model    = $product->get_meta( '_ifm_pricing_model' );
		$featured = '1' === $product->get_meta( '_ifm_featured' );
		$items    = IFM_Packages::lines( $product, '_ifm_deliverables' );
		$excl     = IFM_Packages::lines( $product, '_ifm_exclusions' );
		$ideal    = $product->get_meta( '_ifm_ideal_for' );
		$turn     = $product->get_meta( '_ifm_turnaround' );
		$rev      = $product->get_meta( '_ifm_revisions' );

		ob_start();
		echo '<li class="ifm-pkcard' . ( $featured ? ' ifm-pkcard--featured' : '' ) . '">';
		if ( $featured ) {
			echo '<span class="ifm-pkcard__badge">' . esc_html__( 'Most popular', 'iamfamous-core' ) . '</span>';
		}
		if ( $product->get_image_id() ) {
			echo '<a class="ifm-pkcard__media" href="' . esc_url( $product->get_permalink() ) . '" tabindex="-1" aria-hidden="true">' . wp_get_attachment_image( $product->get_image_id(), 'medium_large', false, array( 'loading' => 'lazy', 'alt' => '' ) ) . '</a>';
		}
		echo '<h3 class="ifm-pkcard__title">' . esc_html( $product->get_name() ) . '</h3>';
		if ( $ideal ) {
			echo '<p class="ifm-pkcard__ideal">' . esc_html( $ideal ) . '</p>';
		}
		echo '<div class="ifm-pkcard__price">';
		if ( 'quote' === $model ) {
			echo '<span class="ifm-pkcard__quote">' . esc_html__( 'Custom quote', 'iamfamous-core' ) . '</span>';
		} else {
			echo wp_kses_post( $product->get_price_html() );
		}
		echo '</div>';
		echo '<dl class="ifm-pkcard__facts">';
		if ( $turn ) {
			echo '<div><dt>' . esc_html__( 'Delivery', 'iamfamous-core' ) . '</dt><dd>' . esc_html( $turn ) . '</dd></div>';
		}
		if ( $rev ) {
			echo '<div><dt>' . esc_html__( 'Revisions', 'iamfamous-core' ) . '</dt><dd>' . esc_html( $rev ) . '</dd></div>';
		}
		echo '</dl>';
		if ( $items ) {
			echo '<ul class="ifm-pkcard__list">';
			foreach ( $items as $line ) {
				echo '<li>' . esc_html( $line ) . '</li>';
			}
			echo '</ul>';
		}
		if ( $excl ) {
			echo '<details class="ifm-pkcard__excl"><summary>' . esc_html__( 'Not included', 'iamfamous-core' ) . '</summary><ul>';
			foreach ( $excl as $line ) {
				echo '<li>' . esc_html( $line ) . '</li>';
			}
			echo '</ul></details>';
		}
		echo '<div class="ifm-pkcard__cta">';
		if ( 'quote' === $model || ! $product->is_purchasable() ) {
			echo '<a class="ifm-btn ifm-btn--primary" href="' . esc_url( IFM_Packages::quote_url( $product ) ) . '">' . esc_html__( 'Get a quote', 'iamfamous-core' ) . '</a>';
		} else {
			/* translators: %s: plan name */
			echo '<a class="ifm-btn ifm-btn--primary" href="' . esc_url( $product->get_permalink() . '#order' ) . '" aria-label="' . esc_attr( sprintf( __( 'Choose %s', 'iamfamous-core' ), $product->get_name() ) ) . '">' . esc_html__( 'Choose plan', 'iamfamous-core' ) . ' →</a>';
		}
		echo '<a class="ifm-pkcard__details" href="' . esc_url( $product->get_permalink() ) . '">' . esc_html__( 'Full plan details', 'iamfamous-core' ) . '</a>';
		echo '</div></li>';
		return (string) ob_get_clean();
	}
}
