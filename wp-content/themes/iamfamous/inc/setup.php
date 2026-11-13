<?php
/**
 * Appearance → IamFamous Setup: builds the whole site from the theme's patterns in one click.
 *
 * Every step is optional and idempotent:
 *  - Pages are created from patterns/page-*.php. Existing pages are only overwritten when they were created
 *    by this screen AND "Overwrite" is ticked. Pages you created yourself are never touched.
 *  - Pages from the previous iamfamous.uk site (Elementor / Hostinger AI builds) are moved to Draft, never deleted.
 *    If one of them holds an address the new site needs (e.g. /services/), its address gets an "-old" suffix first.
 *
 * @package IamFamousTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Site builder.
 */
final class IFM_Setup {

	const META   = '_ifm_generated';
	/** Pages that must never be drafted by the clean-up: WooCommerce system pages and the privacy page. */
	const KEEP   = array( 'cart', 'basket', 'checkout', 'my-account', 'shop', 'refund_returns' );
	const ACTION = 'ifm_setup';

	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_notices', array( __CLASS__, 'nudge' ) );
	}

	public static function menu(): void {
		add_theme_page( __( 'IamFamous Setup', 'iamfamous' ), __( 'IamFamous Setup', 'iamfamous' ), 'manage_options', 'ifm-setup', array( __CLASS__, 'page' ) );
	}

	/** One-time hint after activating the theme. */
	public static function nudge(): void {
		if ( get_option( 'ifm_setup_done' ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( $screen && 'appearance_page_ifm-setup' === $screen->id ) {
			return;
		}
		echo '<div class="notice notice-info"><p><strong>IamFamous theme:</strong> ' . esc_html__( 'build the pages, logo and shop settings in one click.', 'iamfamous' ) . ' <a class="button button-primary" href="' . esc_url( admin_url( 'themes.php?page=ifm-setup' ) ) . '">' . esc_html__( 'Open setup', 'iamfamous' ) . '</a></p></div>';
	}

	private static function manifest(): array {
		return require get_theme_file_path( 'inc/site-manifest.php' );
	}

	/** Pattern markup (PHP already evaluated, so image URLs are absolute). */
	private static function pattern( string $slug ): string {
		$reg = WP_Block_Patterns_Registry::get_instance();
		$p   = $reg->get_registered( $slug );
		if ( $p && ! empty( $p['content'] ) ) {
			return $p['content'];
		}
		// Fallback: render the pattern file directly.
		$file = get_theme_file_path( 'patterns/' . substr( $slug, strlen( 'iamfamous/' ) ) . '.php' );
		if ( ! is_readable( $file ) ) {
			return '';
		}
		ob_start();
		include $file;
		return (string) ob_get_clean();
	}

	public static function page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$log = array();
		if ( isset( $_POST[ self::ACTION ] ) && check_admin_referer( self::ACTION ) ) {
			$opt = array_map( 'sanitize_key', array_keys( (array) wp_unslash( $_POST['ifm'] ?? array() ) ) );
			$log = self::run( array_fill_keys( $opt, true ) );
			update_option( 'ifm_setup_done', gmdate( 'c' ) );
		}
		$m = self::manifest();
		echo '<div class="wrap"><h1>' . esc_html__( 'IamFamous Setup', 'iamfamous' ) . '</h1>';
		echo '<p>' . esc_html( sprintf( /* translators: 1: pages, 2: posts */ __( 'Builds %1$d pages from the theme patterns (plus %2$d starter articles when the theme ships them). Take a backup first. Everything created here can be edited in the block editor afterwards.', 'iamfamous' ), count( $m['pages'] ) + 1, count( $m['posts'] ) ) ) . '</p>';
		if ( ! class_exists( 'IFM_Shortcode' ) ) {
			echo '<div class="notice notice-warning inline"><p>' . esc_html__( 'The IamFamous Core plugin is not active. Activate it so package cards, prices, forms and breadcrumbs appear on the pages.', 'iamfamous' ) . '</p></div>';
		}
		echo '<form method="post">';
		wp_nonce_field( self::ACTION );
		$opts = array(
			'pages'     => array( true, __( 'Create the pages: Home, Services, all 20 service pages, Free growth check, Contact and Insights (the blog index)', 'iamfamous' ) ),
			'overwrite' => array( false, __( 'Overwrite pages previously created by this screen with the latest design (tick this when updating the theme; your own pages are never touched)', 'iamfamous' ) ),
			'posts'     => array( true, __( 'Create the starter articles shipped with this theme version, if any', 'iamfamous' ) ),
			'reading'   => array( true, __( 'Set Home as the front page and Insights as the posts page', 'iamfamous' ) ),
			'brand'     => array( true, __( 'Upload the logo and site icon, set the site title and tagline', 'iamfamous' ) ),
			'legacy'    => array( true, __( 'Move pages from the previous site to Draft (nothing is deleted). WooCommerce pages and pages created by this screen are left alone. Old addresses are redirected by the IamFamous Core plugin.', 'iamfamous' ) ),
			'woo'       => array( true, __( 'WooCommerce for services: pounds sterling (£), account required at checkout (needed for the project brief), no tax calculation (IamFamous is not VAT registered)', 'iamfamous' ) ),
			'templates' => array( false, __( 'Use the theme files for header, footer and templates: move any copies saved in the Site Editor to the Trash (restore them from the Trash if needed). Tick this if the menu or a template does not match the latest theme.', 'iamfamous' ) ),
			'bacs'      => array( false, __( 'Enable "Direct bank transfer" as a payment method so test orders can be placed (orders stay On hold until you confirm payment). Connect Stripe / PayPal separately, in test mode first.', 'iamfamous' ) ),
		);
		echo '<table class="form-table" role="presentation"><tbody>';
		foreach ( $opts as $key => $o ) {
			echo '<tr><td><label><input type="checkbox" name="ifm[' . esc_attr( $key ) . ']" value="1"' . checked( $o[0], true, false ) . '> ' . esc_html( $o[1] ) . '</label></td></tr>';
		}
		echo '</tbody></table>';
		submit_button( __( 'Build the site', 'iamfamous' ), 'primary', self::ACTION );
		echo '</form>';
		if ( $log ) {
			echo '<h2>' . esc_html__( 'Result', 'iamfamous' ) . '</h2><pre style="background:#fff;padding:12px;max-height:520px;overflow:auto;white-space:pre-wrap">' . esc_html( implode( "\n", $log ) ) . '</pre>';
			echo '<p><a class="button button-primary" href="' . esc_url( home_url( '/' ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'View site', 'iamfamous' ) . '</a></p>';
		}
		echo '</div>';
	}

	/**
	 * @param array<string,bool> $o Options.
	 * @return string[] Log lines.
	 */
	public static function run( array $o ): array {
		$log = array();
		$m   = self::manifest();
		$ids = array();

		if ( ! empty( $o['pages'] ) ) {
			$pages   = $m['pages'];
			$pages[] = array( 'slug' => 'insights', 'title' => 'Insights', 'parent' => '', 'template' => '', 'excerpt' => 'Plain-English guides for UK small businesses on getting found on Google, running a website and posting on social media.', 'pattern' => '', 'order' => 9, 'seo_title' => 'Marketing Guides for UK Small Businesses | IamFamous' );
			// Parents first.
			usort( $pages, static fn( $a, $b ) => ( '' === $a['parent'] ? 0 : 1 ) <=> ( '' === $b['parent'] ? 0 : 1 ) );
			foreach ( $pages as $p ) {
				$parent_id = $p['parent'] ? ( $ids[ $p['parent'] ] ?? self::page_id( $p['parent'] ) ) : 0;
				$existing  = self::page_id( $p['slug'], $parent_id );
				$content   = $p['pattern'] ? self::pattern( $p['pattern'] ) : '';
				if ( $p['pattern'] && '' === $content ) {
					$log[] = "SKIP  {$p['slug']}: pattern {$p['pattern']} not found";
					continue;
				}
				if ( $existing && ! get_post_meta( $existing, self::META, true ) ) {
					if ( empty( $o['legacy'] ) || in_array( $p['slug'], self::KEEP, true ) ) {
						$ids[ $p['slug'] ] = $existing;
						$log[]             = "KEEP  /{$p['slug']}/ already exists and was not created here, left unchanged (tick the previous-site option to replace it)";
						continue;
					}
					// A page from the previous site holds this address: park it as a draft under a new address.
					wp_update_post(
						array(
							'ID'          => $existing,
							'post_status' => 'draft',
							'post_name'   => $p['slug'] . '-old',
						)
					);
					$log[]    = "DRAFT previous page /{$p['slug']}/ (kept as a draft, address changed to {$p['slug']}-old)";
					$existing = 0;
				}
				if ( $existing && empty( $o['overwrite'] ) ) {
					$ids[ $p['slug'] ] = $existing;
					$log[]             = "KEEP  /{$p['slug']}/ exists (tick Overwrite to rebuild it)";
					continue;
				}
				$data = array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => $p['title'],
					'post_name'    => $p['slug'],
					'post_parent'  => $parent_id,
					'post_excerpt' => $p['excerpt'],
					'post_content' => wp_slash( $content ),
					'menu_order'   => (int) $p['order'],
				);
				if ( $existing ) {
					$data['ID'] = $existing;
				}
				$id = wp_insert_post( $data, true );
				if ( is_wp_error( $id ) ) {
					$log[] = "ERROR {$p['slug']}: " . $id->get_error_message();
					continue;
				}
				update_post_meta( $id, self::META, IFM_THEME_VERSION );
				update_post_meta( $id, '_wp_page_template', $p['template'] ? $p['template'] : 'default' );
				if ( ! empty( $p['seo_title'] ) ) {
					update_post_meta( $id, '_ifm_seo_title', $p['seo_title'] );
				}
				$ids[ $p['slug'] ] = $id;
				$log[]             = ( $existing ? 'UPDATE ' : 'CREATE ' ) . wp_make_link_relative( get_permalink( $id ) );
			}
		}

		if ( ! empty( $o['posts'] ) ) {
			foreach ( $m['posts'] as $p ) {
				$existing_post = get_page_by_path( $p['slug'], OBJECT, 'post' );
				if ( $existing_post ) {
					if ( ! empty( $o['overwrite'] ) && get_post_meta( $existing_post->ID, self::META, true ) ) {
						wp_update_post(
							array(
								'ID'           => $existing_post->ID,
								'post_title'   => $p['title'],
								'post_excerpt' => $p['excerpt'],
								'post_content' => wp_slash( self::pattern( $p['pattern'] ) ),
							)
						);
						$cat = term_exists( $p['category'], 'category' );
						$cat = $cat ? (int) $cat['term_id'] : (int) wp_insert_term( $p['category'], 'category' )['term_id'];
						wp_set_post_categories( $existing_post->ID, array( $cat ) );
						$log[] = "UPDATE article {$p['slug']}";
					} else {
						$log[] = "KEEP  article {$p['slug']} exists";
					}
					continue;
				}
				$cat = term_exists( $p['category'], 'category' );
				$cat = $cat ? (int) $cat['term_id'] : (int) wp_insert_term( $p['category'], 'category' )['term_id'];
				$id  = wp_insert_post(
					array(
						'post_type'     => 'post',
						'post_status'   => 'publish',
						'post_title'    => $p['title'],
						'post_name'     => $p['slug'],
						'post_excerpt'  => $p['excerpt'],
						'post_content'  => wp_slash( self::pattern( $p['pattern'] ) ),
						'post_category' => array( $cat ),
					),
					true
				);
				if ( ! is_wp_error( $id ) ) {
					update_post_meta( $id, self::META, IFM_THEME_VERSION );
					$log[] = 'CREATE article ' . wp_make_link_relative( get_permalink( $id ) );
				}
			}
			// Cover images for the starter articles (existing or new).
			foreach ( $m['posts'] as $p ) {
				$post = get_page_by_path( $p['slug'], OBJECT, 'post' );
				if ( $post && ! has_post_thumbnail( $post ) ) {
					$img = self::attach( 'covers/' . $p['slug'] . '.webp', $p['title'] );
					if ( $img ) {
						set_post_thumbnail( $post, $img );
						$log[] = 'SET   cover image for ' . $p['slug'];
					}
				}
			}
			// The default "Hello world!" post makes the blog look unfinished.
			$hello = get_page_by_path( 'hello-world', OBJECT, 'post' );
			if ( $hello && 'publish' === $hello->post_status ) {
				wp_update_post( array( 'ID' => $hello->ID, 'post_status' => 'draft' ) );
				$log[] = 'DRAFT default "Hello world!" post';
			}
		}

		if ( ! empty( $o['reading'] ) ) {
			$home     = $ids['home'] ?? self::page_id( 'home' );
			$insights = $ids['insights'] ?? self::page_id( 'insights' );
			if ( $home ) {
				update_option( 'show_on_front', 'page' );
				update_option( 'page_on_front', $home );
				$log[] = 'SET   front page = Home';
			}
			if ( $insights ) {
				update_option( 'page_for_posts', $insights );
				$log[] = 'SET   posts page = Insights';
			}
			if ( '' === get_option( 'permalink_structure' ) ) {
				update_option( 'permalink_structure', '/%postname%/' );
				$log[] = 'SET   permalinks = /%postname%/';
			}
		}

		if ( ! empty( $o['brand'] ) ) {
			update_option( 'blogname', 'IamFamous' );
			update_option( 'blogdescription', 'Get seen. Get chosen. Get famous.' );
			$logo = self::attach( 'logo.png', 'IamFamous logo' );
			if ( $logo ) {
				set_theme_mod( 'custom_logo', $logo );
				update_option( 'site_logo', $logo );
				$log[] = 'SET   logo';
			}
			$icon = self::attach( 'site-icon.png', 'IamFamous site icon' );
			if ( $icon ) {
				update_option( 'site_icon', $icon );
				$log[] = 'SET   site icon';
			}
		}

		if ( ! empty( $o['legacy'] ) ) {
			$keep = array_filter( array_map( 'intval', array( get_option( 'page_on_front' ), get_option( 'page_for_posts' ), get_option( 'wp_page_for_privacy_policy' ), get_option( 'woocommerce_cart_page_id' ), get_option( 'woocommerce_checkout_page_id' ), get_option( 'woocommerce_myaccount_page_id' ), get_option( 'woocommerce_shop_page_id' ), get_option( 'woocommerce_terms_page_id' ) ) ) );
			$old  = get_posts(
				array(
					'post_type'      => 'page',
					'post_status'    => 'publish',
					'posts_per_page' => 200,
					'post__not_in'   => $keep, // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
					'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						array(
							'key'     => self::META,
							'compare' => 'NOT EXISTS',
						),
					),
				)
			);
			foreach ( $old as $page ) {
				if ( in_array( $page->post_name, self::KEEP, true ) ) {
					continue;
				}
				wp_update_post( array( 'ID' => $page->ID, 'post_status' => 'draft' ) );
				$log[] = "DRAFT previous page /{$page->post_name}/ (\"{$page->post_title}\")";
			}
			if ( ! $old ) {
				$log[] = 'OK    No pages from the previous site are still published';
			}
		}

		if ( ! empty( $o['woo'] ) && class_exists( 'WooCommerce' ) ) {
			update_option( 'woocommerce_enable_guest_checkout', 'no' );
			update_option( 'woocommerce_enable_checkout_login_reminder', 'yes' );
			update_option( 'woocommerce_enable_signup_and_login_from_checkout', 'yes' );
			update_option( 'woocommerce_registration_generate_password', 'yes' );
			update_option( 'woocommerce_currency', 'GBP' );
			update_option( 'woocommerce_currency_pos', 'left' );
			update_option( 'woocommerce_default_country', 'GB' );
			// Not VAT registered (owner, 12 Nov 2026): no tax is calculated or shown.
			update_option( 'woocommerce_calc_taxes', 'no' );
			$terms = $ids['terms'] ?? self::page_id( 'terms' );
			if ( $terms ) {
				update_option( 'woocommerce_terms_page_id', $terms );
			}
			$privacy = $ids['privacy-policy'] ?? self::page_id( 'privacy-policy' );
			if ( $privacy ) {
				update_option( 'wp_page_for_privacy_policy', $privacy );
			}
			$log[] = 'SET   WooCommerce: pounds sterling, account required at checkout, tax calculation off';
		}

		if ( ! empty( $o['bacs'] ) && class_exists( 'WooCommerce' ) ) {
			$bacs                = (array) get_option( 'woocommerce_bacs_settings', array() );
			$bacs['enabled']     = 'yes';
			$bacs['title']       = $bacs['title'] ?? __( 'Bank transfer', 'iamfamous' );
			$bacs['description'] = $bacs['description'] ?? __( 'Pay by bank transfer. Use your order number as the reference. Work starts once payment is received.', 'iamfamous' );
			update_option( 'woocommerce_bacs_settings', $bacs );
			$log[] = 'SET   Direct bank transfer enabled (add your bank details in WooCommerce → Settings → Payments)';
		}

		if ( ! empty( $o['templates'] ) ) {
			$saved = get_posts(
				array(
					'post_type'      => array( 'wp_template', 'wp_template_part' ),
					'post_status'    => array( 'publish', 'draft', 'auto-draft' ),
					'posts_per_page' => -1,
					'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
						array(
							'taxonomy' => 'wp_theme',
							'field'    => 'name',
							'terms'    => get_stylesheet(),
						),
					),
				)
			);
			foreach ( $saved as $t ) {
				wp_trash_post( $t->ID );
				$log[] = "RESET {$t->post_type} '{$t->post_name}': Site Editor copy moved to Trash, theme file is used again";
			}
			if ( ! $saved ) {
				$log[] = 'OK    No Site Editor copies of templates found; theme files are already in use';
			}
		}

		flush_rewrite_rules();
		$log[] = 'Done.';
		return $log;
	}

	private static function page_id( string $slug, int $parent = -1 ): int {
		$args = array(
			'post_type'      => 'page',
			'name'           => $slug,
			'post_status'    => array( 'publish', 'draft', 'private', 'pending' ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		);
		if ( $parent >= 0 ) {
			$args['post_parent'] = $parent;
		}
		$q = get_posts( $args );
		return $q ? (int) $q[0] : 0;
	}

	/** Copy a theme image into the media library once; returns attachment ID. */
	private static function attach( string $file, string $title ): int {
		$existing = get_posts(
			array(
				'post_type'   => 'attachment',
				'meta_key'    => '_ifm_asset', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'  => $file, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'fields'      => 'ids',
				'numberposts' => 1,
			)
		);
		if ( $existing ) {
			return (int) $existing[0];
		}
		$src = get_theme_file_path( 'assets/images/' . $file );
		if ( ! is_readable( $src ) ) {
			return 0;
		}
		$up = wp_upload_bits( 'iamfamous-' . basename( $file ), null, (string) file_get_contents( $src ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( ! empty( $up['error'] ) ) {
			return 0;
		}
		$id = wp_insert_attachment(
			array(
				'post_title'     => $title,
				'post_mime_type' => str_ends_with( $file, '.webp' ) ? 'image/webp' : 'image/png',
				'post_status'    => 'inherit',
			),
			$up['file']
		);
		if ( is_wp_error( $id ) || ! $id ) {
			return 0;
		}
		require_once ABSPATH . 'wp-admin/includes/image.php';
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $up['file'] ) );
		update_post_meta( $id, '_wp_attachment_image_alt', $title );
		update_post_meta( $id, '_ifm_asset', $file );
		return (int) $id;
	}
}

IFM_Setup::init();
