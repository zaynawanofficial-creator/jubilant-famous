<?php
/**
 * Imports SERVICE_CATALOG.csv into WooCommerce products (upsert by SKU).
 * Admin: Tools → IamFamous catalog. CLI: wp ifm import-catalog [--file=path] [--dry-run].
 *
 * Status rules: fixed price + both approvals + price > 0 + publish=READY → publish;
 *               pricing_model=quote + scope approved + publish=QUOTE → publish (quote button);
 *               everything else → draft.
 *
 * @package IamFamousCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Catalog importer.
 */
class IFM_Importer {

	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'ifm import-catalog', array( __CLASS__, 'cli' ) );
		}
	}

	public static function default_file(): string {
		return IFM_CORE_DIR . 'data/SERVICE_CATALOG.csv';
	}

	public static function menu(): void {
		add_management_page( __( 'IamFamous catalog', 'iamfamous-core' ), __( 'IamFamous catalog', 'iamfamous-core' ), 'manage_woocommerce', 'ifm-catalog', array( __CLASS__, 'page' ) );
	}

	public static function page(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		$report = null;
		if ( isset( $_POST['ifm_import'] ) && check_admin_referer( 'ifm_import' ) ) {
			$report = self::import( self::default_file(), ! empty( $_POST['ifm_dry_run'] ), ! empty( $_POST['ifm_refresh_images'] ) );
		}
		echo '<div class="wrap"><h1>' . esc_html__( 'IamFamous catalog', 'iamfamous-core' ) . '</h1>';
		echo '<p>' . esc_html__( 'Creates or updates service plans from the bundled SERVICE_CATALOG.csv (GBP), matched by SKU. Plans whose price is not approved in the CSV are saved as drafts and stay hidden from the site. Prices you change later in wp-admin are overwritten only if you import again.', 'iamfamous-core' ) . '</p>';
		echo '<form method="post">';
		wp_nonce_field( 'ifm_import' );
		echo '<p><label><input type="checkbox" name="ifm_dry_run" value="1" checked> ' . esc_html__( 'Dry run (show what would change)', 'iamfamous-core' ) . '</label></p>';
		echo '<p><label><input type="checkbox" name="ifm_refresh_images" value="1"> ' . esc_html__( 'Refresh plan images (replaces only images added by this importer, never your own uploads)', 'iamfamous-core' ) . '</label></p>';
		submit_button( __( 'Run import', 'iamfamous-core' ), 'primary', 'ifm_import' );
		echo '</form>';
		if ( $report ) {
			echo '<h2>' . esc_html__( 'Result', 'iamfamous-core' ) . '</h2><pre style="background:#fff;padding:12px;max-height:480px;overflow:auto">' . esc_html( implode( "\n", $report ) ) . '</pre>';
		}
		echo '</div>';
	}

	/**
	 * WP-CLI handler.
	 *
	 * @param array $args       Positional.
	 * @param array $assoc_args Assoc.
	 */
	public static function cli( array $args, array $assoc_args ): void {
		$file = $assoc_args['file'] ?? self::default_file();
		foreach ( self::import( $file, isset( $assoc_args['dry-run'] ) ) as $line ) {
			WP_CLI::log( $line );
		}
	}

	/**
	 * Product page description built from the plan fields, so the single product page explains the plan.
	 *
	 * @param array<string,string> $meta Plan meta.
	 */
	public static function description( array $meta, string $service, string $service_slug ): string {
		$list = static function ( string $title, string $lines ): string {
			$items = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $lines ) ) );
			if ( ! $items ) {
				return '';
			}
			return '<h3>' . esc_html( $title ) . '</h3><ul>' . implode( '', array_map( static fn( $i ) => '<li>' . esc_html( $i ) . '</li>', $items ) ) . '</ul>';
		};
		$html  = '<div class="ifm-product-info">';
		$html .= ! empty( $meta['_ifm_ideal_for'] ) ? '<p><strong>' . esc_html__( 'Ideal for:', 'iamfamous-core' ) . '</strong> ' . esc_html( $meta['_ifm_ideal_for'] ) . '</p>' : '';
		$html .= $list( __( "What's included", 'iamfamous-core' ), $meta['_ifm_deliverables'] ?? '' );
		$html .= $list( __( 'Not included', 'iamfamous-core' ), $meta['_ifm_exclusions'] ?? '' );
		$html .= $list( __( 'What we need from you', 'iamfamous-core' ), $meta['_ifm_requirements'] ?? '' );
		$facts = array_filter(
			array(
				__( 'Delivery', 'iamfamous-core' )  => $meta['_ifm_turnaround'] ?? '',
				__( 'Revisions', 'iamfamous-core' ) => $meta['_ifm_revisions'] ?? '',
			)
		);
		if ( $facts ) {
			$html .= '<h3>' . esc_html__( 'Delivery & revisions', 'iamfamous-core' ) . '</h3><ul>';
			foreach ( $facts as $k => $v ) {
				$html .= '<li><strong>' . esc_html( $k ) . ':</strong> ' . esc_html( $v ) . '</li>';
			}
			$html .= '</ul>';
		}
		$html .= '<p>' . esc_html__( 'After checkout you will complete a short project brief in your account. The delivery time starts when the brief is complete.', 'iamfamous-core' ) . '</p>';
		$html .= '<p><a href="' . esc_url( home_url( '/services/' . $service_slug . '/' ) ) . '">' . esc_html( sprintf( /* translators: %s: service */ __( 'Compare all %s plans', 'iamfamous-core' ), $service ) ) . ' →</a></p>';
		return $html . '</div>';
	}

	/**
	 * Attach data/images/{sku}.webp as the product image, unless the product already has one.
	 */
	public static function attach_image( int $product_id, string $sku, string $title, bool $refresh = false ): void {
		if ( ! function_exists( 'wp_upload_bits' ) ) {
			return;
		}
		$current = (int) get_post_thumbnail_id( $product_id );
		if ( $current ) {
			// Keep the owner's own images; only replace images this importer attached, and only when asked.
			if ( ! $refresh || ! get_post_meta( $current, '_ifm_pkg_image', true ) ) {
				return;
			}
			wp_delete_attachment( $current, true );
		}
		$src = IFM_CORE_DIR . 'data/images/' . $sku . '.webp';
		if ( ! is_readable( $src ) ) {
			return;
		}
		$up = wp_upload_bits( 'ifm-' . $sku . '.webp', null, (string) file_get_contents( $src ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( ! empty( $up['error'] ) ) {
			return;
		}
		$att = wp_insert_attachment( array( 'post_title' => $title, 'post_mime_type' => 'image/webp', 'post_status' => 'inherit' ), $up['file'], $product_id );
		if ( ! $att || is_wp_error( $att ) ) {
			return;
		}
		require_once ABSPATH . 'wp-admin/includes/image.php';
		wp_update_attachment_metadata( $att, wp_generate_attachment_metadata( $att, $up['file'] ) );
		update_post_meta( $att, '_wp_attachment_image_alt', $title );
		update_post_meta( $att, '_ifm_pkg_image', $sku );
		set_post_thumbnail( $product_id, $att );
	}

	/**
	 * Plan copy per group: the shared base, then IamFamous's own wording (data/group-content-ifm*.php) on top.
	 *
	 * @return array<string,array>
	 */
	public static function content(): array {
		$base = require IFM_CORE_DIR . 'data/group-content.php';
		foreach ( array( 'data/group-content-ifm.php', 'data/group-content-ifm-2.php' ) as $file ) {
			$own = IFM_CORE_DIR . $file;
			if ( ! is_readable( $own ) ) {
				continue;
			}
			foreach ( (array) require $own as $k => $v ) {
				// Our own per-package wording replaces the shared lists outright (index-based rules included).
				if ( isset( $v['by_sku'] ) ) {
					unset( $base[ $k ]['deliverables_by_index'], $base[ $k ]['ideal_for_index'] );
				}
				$base[ $k ] = isset( $base[ $k ] ) ? array_replace_recursive( $base[ $k ], $v ) : $v;
			}
		}
		return $base;
	}

	/** Catalogue SKUs carry an "if-" prefix; the copy files are keyed without it. */
	public static function content_key( string $sku ): string {
		return (string) preg_replace( '/^if-/', '', $sku );
	}

	/** @return array<int,array<string,string>> */
	public static function read_csv( string $file ): array {
		$rows = array();
		$fh   = fopen( $file, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		if ( ! $fh ) {
			return $rows;
		}
		$head = fgetcsv( $fh, 0, ',', '"', '\\' );
		while ( ( $r = fgetcsv( $fh, 0, ',', '"', '\\' ) ) !== false ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			if ( count( $r ) === count( $head ) ) {
				$rows[] = array_combine( $head, $r );
			}
		}
		fclose( $fh ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		return $rows;
	}

	private static function term( string $name, string $tax, int $parent = 0 ): int {
		$slug  = sanitize_title( $name );
		// Shown to customers as "Plans" (owner, 17 Nov 2026); the slug keeps the catalogue's original wording.
		$label = (string) preg_replace( '/\bPackages\b/', 'Plans', $name );
		$found = get_term_by( 'slug', $slug, $tax );
		if ( $found ) {
			// Pre-existing term (e.g. created by hand or a theme demo): make sure it sits under the right parent, with the current name.
			$update = array();
			if ( (int) $found->parent !== $parent ) {
				$update['parent'] = $parent;
			}
			if ( $found->name !== $label ) {
				$update['name'] = $label;
			}
			if ( $update ) {
				wp_update_term( (int) $found->term_id, $tax, $update );
			}
			return (int) $found->term_id;
		}
		$res = wp_insert_term( $label, $tax, array( 'slug' => $slug, 'parent' => $parent ) );
		return is_wp_error( $res ) ? 0 : (int) $res['term_id'];
	}

	public static function import( string $file, bool $dry = false, bool $refresh_images = false ): array {
		$rows = self::read_csv( $file );
		if ( ! $rows ) {
			return array( 'No rows read from ' . $file );
		}
		$content = self::content();
		$log     = array();
		$sort    = array();
		$gsort   = 0;
		foreach ( $rows as $row ) {
			$sku      = sanitize_title( $row['package_code'] );
			$group    = sanitize_title( $row['group'] );
			$gc       = $content[ $group ] ?? array();
			$ckey     = self::content_key( $sku );
			$model    = 'quote' === ( $row['pricing_model'] ?? '' ) ? 'quote' : 'fixed';
			$price    = is_numeric( $row['price'] ) ? (string) (float) $row['price'] : '';
			$approved = 'YES' === $row['price_approved'] && 'YES' === $row['scope_approved'];
			$status   = 'draft';
			if ( 'fixed' === $model && $approved && $price && 'READY' === $row['publish'] ) {
				$status = 'publish';
			} elseif ( 'quote' === $model && 'YES' === $row['scope_approved'] && 'QUOTE' === $row['publish'] ) {
				$status = 'publish';
			}
			$existing = wc_get_product_id_by_sku( $sku );
			$log[]    = sprintf( '%s %-55s %-8s %s', $existing ? 'UPDATE' : 'CREATE', $sku, $price ? '£' . $price : $model, $status );
			if ( $dry ) {
				continue;
			}

			$family_id  = self::term( $row['category'], 'product_cat' );
			$service_id = self::term( $row['service'], 'product_cat', $family_id );
			$group_id   = self::term( $row['group'], IFM_Packages::TAX );
			if ( ! isset( $sort[ $group ] ) ) {
				$sort[ $group ] = 0;
				update_term_meta( $group_id, '_ifm_group_sort', ++$gsort );
				if ( ! empty( $gc['intro'] ) ) {
					wp_update_term( $group_id, IFM_Packages::TAX, array( 'description' => $gc['intro'] ) );
				}
			}
			$sort[ $group ] += 10;
			$idx             = (int) ( $sort[ $group ] / 10 ); // 1-based position within the group.

			$product = $existing ? wc_get_product( $existing ) : new WC_Product_Simple();
			$product->set_name( $row['package_title'] );
			$product->set_sku( $sku );
			$product->set_virtual( true );
			$product->set_downloadable( false );
			$product->set_sold_individually( true );
			$product->set_tax_status( 'taxable' );
			$product->set_regular_price( 'fixed' === $model ? $price : '' );
			$product->set_category_ids( array( $service_id ) );
			$product->set_status( $status );
			$qty = (int) $row['quantity'];
			$product->set_short_description( $gc['intro'] ?? '' );
			$meta = array(
				'_ifm_pricing_model'  => $model,
				'_ifm_quantity'       => $qty ? (string) $qty : '',
				'_ifm_unit'           => $row['unit'],
				'_ifm_turnaround'     => 'TBD' === $row['turnaround'] ? '' : $row['turnaround'],
				'_ifm_revisions'      => 'TBD' === $row['revisions'] ? '' : $row['revisions'],
				'_ifm_brief_profile'  => $gc['brief'] ?? 'generic',
				'_ifm_terms_version'  => '2026-11-draft',
				'_ifm_sort'           => (string) $sort[ $group ],
				'_ifm_price_approved' => 'YES' === $row['price_approved'] ? '1' : '',
				'_ifm_scope_approved' => 'YES' === $row['scope_approved'] ? '1' : '',
				'_ifm_featured'       => ( in_array( $qty, $gc['featured'] ?? array(), true ) || in_array( $idx, $gc['featured_index'] ?? array(), true ) ) ? '1' : '',
			);
			foreach ( array( 'deliverables', 'exclusions', 'requirements' ) as $k ) {
				if ( isset( $gc[ $k ] ) ) {
					$meta[ '_ifm_' . $k ] = $gc[ $k ];
				}
			}
			$by_sku = $gc['by_sku'][ $ckey ] ?? array();
			if ( isset( $by_sku['deliverables'] ) ) {
				$meta['_ifm_deliverables'] = $by_sku['deliverables'];
			}
			if ( isset( $by_sku['ideal_for'] ) ) {
				$meta['_ifm_ideal_for'] = $by_sku['ideal_for'];
			}
			if ( ! empty( $by_sku['featured'] ) ) {
				$meta['_ifm_featured'] = '1';
			}
			if ( isset( $gc['deliverables_by_index'][ $idx ] ) ) {
				$meta['_ifm_deliverables'] = $gc['deliverables_by_index'][ $idx ];
			}
			if ( isset( $by_sku['ideal_for'] ) ) {
				$meta['_ifm_ideal_for'] = $by_sku['ideal_for'];
			} elseif ( isset( $gc['ideal_for'][ $qty ] ) ) {
				$meta['_ifm_ideal_for'] = $gc['ideal_for'][ $qty ];
			} elseif ( isset( $gc['ideal_for_index'][ $idx ] ) ) {
				$meta['_ifm_ideal_for'] = $gc['ideal_for_index'][ $idx ];
			}
			foreach ( $meta as $k => $v ) {
				$product->update_meta_data( $k, $v );
			}
			$product->set_description( self::description( $meta, $row['service'], sanitize_title( $row['service'] ) ) );
			IFM_Packages::$bypass_gate = true;
			$id                       = $product->save();
			IFM_Packages::$bypass_gate = false;
			self::attach_image( (int) $id, $sku, $row['package_title'], $refresh_images );
			wp_set_object_terms( $id, array( $group_id ), IFM_Packages::TAX );
		}
		$log[] = $dry ? 'Dry run — nothing saved.' : 'Done.';
		return $log;
	}
}
