<?php
/**
 * Site helpers that must survive a theme change:
 * [ifm_from], [ifm_year], [ifm_breadcrumbs], basic SEO head tags, product quote button, old-URL redirects.
 *
 * @package IamFamousCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Site-wide helpers.
 */
class IFM_Site {

	public static function init(): void {
		add_shortcode( 'ifm_from', array( __CLASS__, 'from_price' ) );
		add_shortcode( 'ifm_year', static fn() => esc_html( wp_date( 'Y' ) ) );
		add_shortcode( 'ifm_currency', static fn() => esc_html( get_woocommerce_currency() ) );
		add_shortcode( 'ifm_breadcrumbs', array( __CLASS__, 'breadcrumbs' ) );
		add_shortcode( 'ifm_address', static fn() => esc_html( implode( ', ', self::business()['address'] ) ) );
		add_action( 'init', array( __CLASS__, 'llms_txt' ), 99 );
		add_shortcode( 'ifm_author', array( __CLASS__, 'author_box' ) );
		add_shortcode( 'ifm_byline', array( __CLASS__, 'byline' ) );
		add_shortcode( 'ifm_author_photo', array( __CLASS__, 'author_photo' ) );
		add_action( 'save_post_product', array( __CLASS__, 'flush_prices' ) );
		add_action( 'woocommerce_update_product', array( __CLASS__, 'flush_prices' ) );
		add_filter( 'wp_robots', array( __CLASS__, 'robots' ) );
		add_filter( 'pre_get_document_title', array( __CLASS__, 'seo_title' ), 20 );
		add_action( 'wp_head', array( __CLASS__, 'head' ), 2 );
		add_filter( 'render_block_woocommerce/add-to-cart-form', array( __CLASS__, 'quote_button_block' ), 10, 2 );
		add_filter( 'render_block_woocommerce/add-to-cart-with-options', array( __CLASS__, 'quote_button_block' ), 10, 2 );
		add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'quote_button_classic' ), 31 );
	}

	/* ------------------------------------------------------------------ author (the agency, not a person) */

	/**
	 * Who articles are attributed to. Owner decision (12 Nov 2026): IamFamous speaks as an agency and shows
	 * no named individual. Filter 'ifm_author' to change it (e.g. add a named person later).
	 */
	public static function author(): array {
		return apply_filters(
			'ifm_author',
			array(
				'name'  => __( 'The IamFamous team', 'iamfamous-core' ),
				'role'  => __( 'SEO, websites and social media for UK small businesses', 'iamfamous-core' ),
				'photo' => function_exists( 'get_theme_file_uri' ) ? get_theme_file_uri( 'assets/images/icon.svg' ) : '',
				'url'   => home_url( '/services/' ),
				'bio'   => __( 'We write these guides from the work we do on client orders: local SEO, small-business websites and social media. Each one is checked against the current Google and platform documentation before it is published.', 'iamfamous-core' ),
				'type'  => 'Organization',
			)
		);
	}

	public static function author_photo( $atts = array() ): string {
		$f    = self::author();
		$size = (int) ( $atts['size'] ?? 96 );
		return $f['photo'] ? '<img class="ifm-author-photo" src="' . esc_url( $f['photo'] ) . '" width="' . $size . '" height="' . $size . '" alt="" loading="lazy">' : '';
	}

	public static function byline(): string {
		$f    = self::author();
		$cats = get_the_category();
		$out  = '<p class="ifm-byline">' . self::author_photo( array( 'size' => 36 ) ) . '<span>' . esc_html__( 'By', 'iamfamous-core' ) . ' ' . esc_html( $f['name'] ) . '</span>';
		$out .= '<span>' . esc_html( (string) get_the_date() ) . '</span>';
		$mod  = get_the_modified_date( 'U' );
		if ( $mod && $mod - (int) get_the_date( 'U' ) > DAY_IN_SECONDS ) {
			/* translators: %s: date */
			$out .= '<span>' . esc_html( sprintf( __( 'Updated %s', 'iamfamous-core' ), get_the_modified_date() ) ) . '</span>';
		}
		$words = str_word_count( wp_strip_all_tags( (string) get_post_field( 'post_content', get_the_ID() ) ) );
		/* translators: %d: minutes */
		$out .= '<span>' . esc_html( sprintf( __( '%d min read', 'iamfamous-core' ), max( 1, (int) round( $words / 220 ) ) ) ) . '</span>';
		if ( $cats ) {
			$out .= '<span><a href="' . esc_url( get_category_link( $cats[0] ) ) . '">' . esc_html( $cats[0]->name ) . '</a></span>';
		}
		return $out . '</p>';
	}

	public static function author_box(): string {
		$f = self::author();
		return '<aside class="ifm-author" aria-label="' . esc_attr__( 'About the author', 'iamfamous-core' ) . '">' . self::author_photo( array( 'size' => 88 ) )
			. '<div><p class="ifm-author__kicker">' . esc_html__( 'Written by', 'iamfamous-core' ) . '</p><p class="ifm-author__name">' . esc_html( $f['name'] ) . ' <span>' . esc_html( $f['role'] ) . '</span></p>'
			. '<p class="ifm-author__bio">' . esc_html( $f['bio'] ) . '</p></div></aside>';
	}

	/* ------------------------------------------------------------------ business facts */

	/**
	 * The business details shown on the site and in structured data (owner, 15 Nov 2026).
	 * One place to change them; filter 'ifm_business' to override.
	 */
	public static function business(): array {
		return (array) apply_filters(
			'ifm_business',
			array(
				'name'     => 'IamFamous',
				'email'    => apply_filters( 'ifm_public_email', 'support@iamfamous.uk' ),
				'address'  => array( '27 Old Gloucester Street', 'London', 'WC1N 3AX', 'United Kingdom' ),
				'street'   => '27 Old Gloucester Street',
				'city'     => 'London',
				'postcode' => 'WC1N 3AX',
				'country'  => 'GB',
			)
		);
	}

	/** Basket, checkout, account, package and search pages: never indexed. */
	public static function is_private_page(): bool {
		return is_search() || ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() ) ) || ( function_exists( 'is_product' ) && is_product() ) || ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() ) );
	}

	/**
	 * /llms.txt: a plain-text summary of the business and its main pages for AI tools.
	 * Built from the published pages, so it stays in step with the site.
	 */
	public static function llms_txt(): void {
		$path = (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '', PHP_URL_PATH );
		$base = rtrim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
		if ( $base . '/llms.txt' !== rtrim( $path, '/' ) ) {
			return;
		}
		$b     = self::business();
		$lines = array(
			'# ' . $b['name'],
			'',
			'> ' . $b['name'] . ' is a UK digital marketing agency that sells fixed-price packages to small businesses: local SEO, websites, social media management, Google and social ads, content writing and design. Orders are placed online, priced in pounds sterling with no VAT added, and are one-off purchases with no contract.',
			'',
			'- Business name: ' . $b['name'],
			'- Address: ' . implode( ', ', $b['address'] ),
			'- Email: ' . $b['email'],
			'- Area served: United Kingdom',
			'- Website: ' . home_url( '/' ),
			'',
		);
		$hub      = get_page_by_path( 'services' );
		$sections = array( 'Company' => 0 );
		if ( $hub ) {
			$sections = array( 'Services' => (int) $hub->ID, 'Company' => 0 );
		}
		foreach ( $sections as $title => $parent ) {
			$pages = get_posts(
				array(
					'post_type'   => 'page',
					'post_status' => 'publish',
					'post_parent' => $parent,
					'numberposts' => 60,
					'orderby'     => 'menu_order title',
					'order'       => 'ASC',
					'meta_key'    => '_ifm_generated', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				)
			);
			if ( ! $pages ) {
				continue;
			}
			$lines[] = '## ' . $title;
			foreach ( $pages as $pg ) {
				$lines[] = '- [' . wp_strip_all_tags( get_the_title( $pg ) ) . '](' . get_permalink( $pg ) . ')' . ( $pg->post_excerpt ? ': ' . wp_strip_all_tags( $pg->post_excerpt ) : '' );
			}
			$lines[] = '';
		}
		nocache_headers();
		header( 'Content-Type: text/plain; charset=utf-8' );
		header( 'X-Robots-Tag: noindex' );
		echo implode( "\n", $lines ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- plain text response.
		exit;
	}

	/** Lowest published fixed price for a service (product_cat slug), optionally one package group. '' when none. Cached for a day. */
	public static function min_price( string $service = '', string $group = '' ): string {
		$key = 'ifm_from_' . md5( $service . '|' . $group . '|' . (string) get_option( 'ifm_price_ver', '1' ) );
		$min = get_transient( $key );
		if ( false === $min ) {
			$args = array( 'status' => 'publish', 'limit' => 200, 'return' => 'objects' );
			if ( $service ) {
				$args['category'] = array( sanitize_title( $service ) );
			}
			$min = '';
			foreach ( wc_get_products( $args ) as $p ) {
				if ( ! IFM_Packages::is_package( $p ) || 'quote' === $p->get_meta( '_ifm_pricing_model' ) ) {
					continue;
				}
				if ( $group && ! has_term( sanitize_title( $group ), IFM_Packages::TAX, $p->get_id() ) ) {
					continue;
				}
				$price = (float) $p->get_price();
				if ( $price > 0 && ( '' === $min || $price < (float) $min ) ) {
					$min = (string) $price;
				}
			}
			set_transient( $key, $min, DAY_IN_SECONDS );
		}
		return (string) $min;
	}

	/* ------------------------------------------------------------------ prices */

	/**
	 * Lowest published price for a service (product_cat slug), optionally one package group.
	 * [ifm_from] alone = lowest price in the whole catalogue.
	 * prefix="From " is printed before a price; fallback="…" replaces the whole output when no price is published.
	 */
	public static function from_price( $atts ): string {
		$atts = shortcode_atts( array( 'service' => '', 'group' => '', 'prefix' => '', 'fallback' => '' ), $atts, 'ifm_from' );
		$min  = self::min_price( (string) $atts['service'], (string) $atts['group'] );
		if ( '' === $min ) {
			// No published price yet (e.g. prices awaiting approval): show the page's own fallback wording.
			return '' !== $atts['fallback'] ? esc_html( $atts['fallback'] ) : esc_html__( 'quote', 'iamfamous-core' );
		}
		return esc_html( $atts['prefix'] ) . wp_strip_all_tags( wc_price( (float) $min, array( 'decimals' => floor( (float) $min ) == (float) $min ? 0 : 2 ) ) ); // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
	}

	/** Prices changed: bump the cache version so every cached "from" price is recalculated (old transients expire). */
	public static function flush_prices(): void {
		update_option( 'ifm_price_ver', (string) time(), false );
	}

	/* ------------------------------------------------------------------ breadcrumbs */

	/** @return array<int,array{0:string,1:string}> [label, url] */
	public static function trail(): array {
		$trail = array( array( __( 'Home', 'iamfamous-core' ), home_url( '/' ) ) );
		if ( is_front_page() ) {
			return array();
		}
		if ( function_exists( 'is_product' ) && is_product() ) {
			$trail[] = array( __( 'Services', 'iamfamous-core' ), home_url( '/services/' ) );
			$cats    = get_the_terms( get_the_ID(), 'product_cat' );
			if ( $cats && ! is_wp_error( $cats ) ) {
				$page = get_page_by_path( 'services/' . $cats[0]->slug );
				if ( $page ) {
					$trail[] = array( get_the_title( $page ), get_permalink( $page ) );
				}
			}
			$trail[] = array( get_the_title(), '' );
		} elseif ( is_page() ) {
			foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $a ) {
				$trail[] = array( get_the_title( $a ), get_permalink( $a ) );
			}
			$trail[] = array( get_the_title(), '' );
		} elseif ( is_singular( 'post' ) ) {
			$posts_page = (int) get_option( 'page_for_posts' );
			if ( $posts_page ) {
				$trail[] = array( get_the_title( $posts_page ), get_permalink( $posts_page ) );
			}
			$trail[] = array( get_the_title(), '' );
		} elseif ( is_home() ) {
			$trail[] = array( single_post_title( '', false ), '' );
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$trail[] = array( single_term_title( '', false ), '' );
		} elseif ( is_search() ) {
			$trail[] = array( __( 'Search', 'iamfamous-core' ), '' );
		}
		return $trail;
	}

	public static function breadcrumbs(): string {
		$trail = self::trail();
		if ( count( $trail ) < 2 ) {
			return '';
		}
		$items = array();
		$ld    = array();
		foreach ( $trail as $i => $t ) {
			$label   = esc_html( wp_strip_all_tags( $t[0] ) );
			$items[] = $t[1] && $i < count( $trail ) - 1 ? '<li><a href="' . esc_url( $t[1] ) . '">' . $label . '</a></li>' : '<li aria-current="page">' . $label . '</li>';
			$ld[]    = array_filter(
				array(
					'@type'    => 'ListItem',
					'position' => $i + 1,
					'name'     => wp_strip_all_tags( $t[0] ),
					'item'     => $t[1] ? $t[1] : null,
				)
			);
		}
		$json = wp_json_encode( array( '@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $ld ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		return '<nav class="ifm-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'iamfamous-core' ) . '"><ol>' . implode( '', $items ) . '</ol></nav>'
			. ( self::seo_plugin_active() ? '' : '<script type="application/ld+json">' . $json . '</script>' );
	}

	/* ------------------------------------------------------------------ SEO head */

	public static function seo_plugin_active(): bool {
		return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || class_exists( 'SEOPress' ) || defined( 'THE_SEO_FRAMEWORK_VERSION' );
	}

	/** Custom <title> from the page's _ifm_seo_title meta (only when no SEO plugin manages titles). */
	public static function seo_title( $title ) {
		if ( self::seo_plugin_active() ) {
			return $title;
		}
		$id = 0;
		if ( is_front_page() && get_option( 'page_on_front' ) ) {
			$id = (int) get_option( 'page_on_front' );
		} elseif ( is_home() && get_option( 'page_for_posts' ) ) {
			$id = (int) get_option( 'page_for_posts' );
		} elseif ( is_singular() ) {
			$id = get_queried_object_id();
		}
		$custom = $id ? (string) get_post_meta( $id, '_ifm_seo_title', true ) : '';
		return '' !== $custom ? esc_html( $custom ) : $title;
	}

	/** Keep transactional and thin pages out of the index. */
	public static function robots( array $robots ): array {
		$noindex = self::is_private_page();
		if ( $noindex ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
		}
		return $robots;
	}

	private static function description(): string {
		$d = '';
		if ( is_front_page() || is_singular() ) {
			$id = is_front_page() && get_option( 'page_on_front' ) ? (int) get_option( 'page_on_front' ) : get_queried_object_id();
			$d  = has_excerpt( $id ) ? get_the_excerpt( $id ) : '';
		} elseif ( is_home() ) {
			$id = (int) get_option( 'page_for_posts' );
			$d  = $id && has_excerpt( $id ) ? get_the_excerpt( $id ) : '';
		} elseif ( is_category() || is_tax() ) {
			$d = term_description();
		}
		if ( '' === $d ) {
			$d = get_bloginfo( 'description' );
		}
		return trim( wp_html_excerpt( wp_strip_all_tags( $d ), 158, '…' ) );
	}

	public static function head(): void {
		if ( self::seo_plugin_active() || is_admin() ) {
			return;
		}
		$desc  = self::description();
		$title = wp_get_document_title();
		$url   = is_singular() ? get_permalink() : home_url( add_query_arg( array() ) );
		$logo  = (int) get_theme_mod( 'custom_logo' );
		$img   = get_site_icon_url( 512 );
		if ( is_singular() && has_post_thumbnail() ) {
			$img = get_the_post_thumbnail_url( null, 'large' );
		}
		if ( $desc ) {
			echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
		}
		if ( is_singular() || is_front_page() ) {
			echo '<link rel="canonical" href="' . esc_url( is_front_page() ? home_url( '/' ) : get_permalink() ) . '">' . "\n";
		}
		printf( '<meta property="og:type" content="%s">' . "\n", is_singular( 'post' ) ? 'article' : 'website' );
		printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
		printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
		if ( $desc ) {
			printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $desc ) );
		}
		printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
		if ( $img ) {
			printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $img ) );
		}
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";

		if ( is_singular( 'post' ) ) {
			$f       = self::author();
			$article = array(
				'@context'      => 'https://schema.org',
				'@type'         => 'Article',
				'headline'      => wp_strip_all_tags( get_the_title() ),
				'description'   => $desc,
				'datePublished' => get_the_date( 'c' ),
				'dateModified'  => get_the_modified_date( 'c' ),
				'mainEntityOfPage' => get_permalink(),
				'image'         => has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'full' ) : null,
				'author'        => array( '@type' => 'Organization', 'name' => get_bloginfo( 'name' ), 'url' => home_url( '/' ) ),
				'publisher'     => array( '@type' => 'Organization', 'name' => get_bloginfo( 'name' ), 'url' => home_url( '/' ) ),
			);
			echo '<script type="application/ld+json">' . wp_json_encode( array_filter( $article ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
		}

		if ( is_front_page() ) {
			$org = array(
				'@context' => 'https://schema.org',
				'@graph'   => array(
					array_filter(
						array(
							'@type' => 'Organization',
							'@id'   => home_url( '/#organization' ),
							'name'  => get_bloginfo( 'name' ),
							'url'   => home_url( '/' ),
							'logo'  => $logo ? wp_get_attachment_image_url( $logo, 'full' ) : null,
							'email' => self::business()['email'],
							'address' => array( '@type' => 'PostalAddress', 'streetAddress' => self::business()['street'], 'addressLocality' => self::business()['city'], 'postalCode' => self::business()['postcode'], 'addressCountry' => self::business()['country'] ),
							'areaServed' => array( '@type' => 'Country', 'name' => 'United Kingdom' ),
						)
					),
					array(
						'@type'     => 'WebSite',
						'@id'       => home_url( '/#website' ),
						'url'       => home_url( '/' ),
						'name'      => get_bloginfo( 'name' ),
						'publisher' => array( '@id' => home_url( '/#organization' ) ),
					),
				),
			);
			echo '<script type="application/ld+json">' . wp_json_encode( $org, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
		}
	}

	/* ------------------------------------------------------------------ product page quote button */

	private static function quote_html( WC_Product $product ): string {
		return '<p class="ifm-product-quote"><a class="wp-element-button ifm-btn ifm-btn--primary" href="' . esc_url( IFM_Packages::quote_url( $product ) ) . '">' . esc_html__( 'Get a quote for this package', 'iamfamous-core' ) . '</a></p>';
	}

	public static function quote_button_block( string $html ): string {
		$product = wc_get_product( get_the_ID() );
		if ( $product && 'quote' === $product->get_meta( '_ifm_pricing_model' ) ) {
			return $html . self::quote_html( $product );
		}
		return $html;
	}

	public static function quote_button_classic(): void {
		global $product;
		if ( $product instanceof WC_Product && 'quote' === $product->get_meta( '_ifm_pricing_model' ) && ! wp_is_block_theme() ) {
			echo self::quote_html( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in quote_html().
		}
	}
}
