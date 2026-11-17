<?php
/**
 * Post-purchase project brief: My Account endpoint, conditional fields per plan type,
 * protected uploads, admin view, reminders.
 *
 * @package IamFamousCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Project brief attached to service orders.
 */
class IFM_Brief {

	const ENDPOINT   = 'ifm-brief';
	const UPLOAD_DIR = 'ifm-briefs';
	const MAX_BYTES  = 20971520; // 20 MB.
	const MAX_FILES  = 10;

	/** Allowed upload extensions => MIME types. SVG deliberately excluded (script risk). */
	const MIMES = array(
		'pdf'  => 'application/pdf',
		'png'  => 'image/png',
		'jpg'  => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'webp' => 'image/webp',
		'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'csv'  => 'text/csv',
		'txt'  => 'text/plain',
		'zip'  => 'application/zip',
	);

	/**
	 * Fields per brief profile. key => [label, type, required, help].
	 * Types: url, urls (one per line), text, textarea, email.
	 * Never add password fields: access is granted through delegated invites only.
	 */
	/**
	 * Fields per brief profile. key => [label, type, required, help, options].
	 * Types: url, urls (one per line), text, textarea, email, select (options in index 4).
	 * Used on the service details page (before checkout) and in the post-purchase brief.
	 * Never add password fields: access is granted through delegated invites only.
	 */
	public static function profiles(): array {
		$common = array(
			'goals' => array( __( 'Anything else we should know?', 'iamfamous-core' ), 'textarea', false, __( 'Deadlines, previous work, things to avoid.', 'iamfamous-core' ) ),
		);
		$platforms = array( '' => __( 'Choose…', 'iamfamous-core' ), 'wordpress' => 'WordPress', 'woocommerce' => 'WooCommerce', 'shopify' => 'Shopify', 'wix' => 'Wix / Squarespace', 'custom' => __( 'Custom / other', 'iamfamous-core' ), 'none' => __( 'No website yet', 'iamfamous-core' ) );
		return array(
			'offpage' => array(
				'target_urls'   => array( __( 'Website / target URL(s)', 'iamfamous-core' ), 'urls', true, __( 'One per line. Usually your homepage plus up to 3 key pages.', 'iamfamous-core' ) ),
				'keywords'      => array( __( 'Main keywords or topics', 'iamfamous-core' ), 'textarea', true, __( 'One per line. Used to choose relevant sites and natural anchor text.', 'iamfamous-core' ) ),
				'anchors'       => array( __( 'Anchor text preference', 'iamfamous-core' ), 'select', true, '', array( 'natural' => __( 'Natural mix (recommended)', 'iamfamous-core' ), 'branded' => __( 'Mostly brand name and URL', 'iamfamous-core' ), 'custom' => __( 'I will list exact anchors below', 'iamfamous-core' ) ) ),
				'business_name' => array( __( 'Business name (exactly as it should appear)', 'iamfamous-core' ), 'text', true, '' ),
				'niche_geo'     => array( __( 'Industry and target country / city', 'iamfamous-core' ), 'text', true, __( 'e.g. "Dental clinic, Manchester UK"', 'iamfamous-core' ) ),
				'contact_email' => array( __( 'Email for listing verification', 'iamfamous-core' ), 'email', true, __( 'An address you control. Platforms send confirmation emails here.', 'iamfamous-core' ) ),
				'business_addr' => array( __( 'Business address (for citations)', 'iamfamous-core' ), 'textarea', false, __( 'Leave blank if you are online-only.', 'iamfamous-core' ) ),
				'business_tel'  => array( __( 'Business phone (for citations)', 'iamfamous-core' ), 'text', false, '' ),
				'description'   => array( __( 'Short business description', 'iamfamous-core' ), 'textarea', false, __( '50–150 words. We can write it for you if you leave this blank.', 'iamfamous-core' ) ),
				'avoid'         => array( __( 'Sites or profiles to avoid', 'iamfamous-core' ), 'textarea', false, __( 'Existing listings or competitors\' sites.', 'iamfamous-core' ) ),
			) + $common,
			'local'   => array(
				'business_name' => array( __( 'Business name (exactly as it should appear)', 'iamfamous-core' ), 'text', true, '' ),
				'business_addr' => array( __( 'Business address', 'iamfamous-core' ), 'textarea', true, __( 'If you travel to customers and want the address hidden, add "hide address".', 'iamfamous-core' ) ),
				'business_tel'  => array( __( 'Business phone number', 'iamfamous-core' ), 'text', true, '' ),
				'hours'         => array( __( 'Opening hours', 'iamfamous-core' ), 'textarea', true, __( 'e.g. Mon to Fri 8am to 6pm, Sat 9am to 1pm.', 'iamfamous-core' ) ),
				'services'      => array( __( 'Main services', 'iamfamous-core' ), 'textarea', true, __( 'One per line, in the words your customers use.', 'iamfamous-core' ) ),
				'areas'         => array( __( 'Towns or postcodes you cover', 'iamfamous-core' ), 'textarea', true, '' ),
				'contact_email' => array( __( 'Email for listing confirmations', 'iamfamous-core' ), 'email', true, __( 'An address you can check. Directories send confirmation emails here.', 'iamfamous-core' ) ),
				'site_url'      => array( __( 'Website address', 'iamfamous-core' ), 'url', false, __( 'Leave blank if you do not have one.', 'iamfamous-core' ) ),
				'gbp'           => array( __( 'Google Business Profile', 'iamfamous-core' ), 'select', false, __( 'We will send our email address so you can add us as a manager. Never send passwords.', 'iamfamous-core' ), array( 'have' => __( 'I have one and will add you as a manager', 'iamfamous-core' ), 'unsure' => __( 'I am not sure whether one exists', 'iamfamous-core' ), 'none' => __( 'I do not have one yet', 'iamfamous-core' ) ) ),
				'description'   => array( __( 'Short business description', 'iamfamous-core' ), 'textarea', false, __( 'A few sentences. Leave blank and we will write it.', 'iamfamous-core' ) ),
				'competitors'   => array( __( 'Local competitors', 'iamfamous-core' ), 'textarea', false, __( 'Businesses that appear on the map for your trade.', 'iamfamous-core' ) ),
			) + $common,
			'web'     => array(
				'business_name' => array( __( 'Business name', 'iamfamous-core' ), 'text', true, '' ),
				'site_url'      => array( __( 'Current website (if any)', 'iamfamous-core' ), 'url', false, '' ),
				'pages'         => array( __( 'Pages you need', 'iamfamous-core' ), 'textarea', true, __( 'One per line, e.g. Home, About, Services, Contact.', 'iamfamous-core' ) ),
				'content'       => array( __( 'Is your text ready?', 'iamfamous-core' ), 'select', true, '', array( 'ready' => __( 'Yes, all text is ready', 'iamfamous-core' ), 'partial' => __( 'Partly ready', 'iamfamous-core' ), 'need' => __( 'No, I need copywriting', 'iamfamous-core' ) ) ),
				'references'    => array( __( 'Websites you like', 'iamfamous-core' ), 'urls', false, __( 'One per line, and what you like about each.', 'iamfamous-core' ) ),
				'features'      => array( __( 'Features needed', 'iamfamous-core' ), 'textarea', false, __( 'Forms, booking, newsletter, languages, shop…', 'iamfamous-core' ) ),
				'brand'         => array( __( 'Brand colours and fonts', 'iamfamous-core' ), 'text', false, __( 'Or "use my logo colours". Upload files after checkout.', 'iamfamous-core' ) ),
				'hosting'       => array( __( 'Domain and hosting', 'iamfamous-core' ), 'select', true, '', array( 'have' => __( 'I have hosting and a domain', 'iamfamous-core' ), 'domain' => __( 'I only have a domain', 'iamfamous-core' ), 'none' => __( 'I need help choosing both', 'iamfamous-core' ) ) ),
			) + $common,
			'fix'     => array(
				'site_url' => array( __( 'Website URL', 'iamfamous-core' ), 'url', true, '' ),
				'platform' => array( __( 'Platform', 'iamfamous-core' ), 'select', true, '', $platforms ),
				'problem'  => array( __( 'What is wrong, or what do you need?', 'iamfamous-core' ), 'textarea', true, __( 'Steps to reproduce, error messages and when it started.', 'iamfamous-core' ) ),
				'urgency'  => array( __( 'How urgent is it?', 'iamfamous-core' ), 'select', true, '', array( 'down' => __( 'Site is down or checkout broken', 'iamfamous-core' ), 'soon' => __( 'Needs fixing this week', 'iamfamous-core' ), 'normal' => __( 'Normal priority', 'iamfamous-core' ) ) ),
				'host'     => array( __( 'Hosting company', 'iamfamous-core' ), 'text', false, '' ),
				'access'   => array( __( 'Access', 'iamfamous-core' ), 'select', true, __( 'Never send passwords. We will email you our user address to invite.', 'iamfamous-core' ), array( 'invite' => __( 'I will invite you as an admin user', 'iamfamous-core' ), 'help' => __( 'I need help creating a user', 'iamfamous-core' ) ) ),
			) + $common,
			'content' => array(
				'site_url' => array( __( 'Website the content is for', 'iamfamous-core' ), 'url', true, '' ),
				'topics'   => array( __( 'Topic(s) or working titles', 'iamfamous-core' ), 'textarea', true, __( 'One per line. For batches, you can leave this to us.', 'iamfamous-core' ) ),
				'keywords' => array( __( 'Target keywords', 'iamfamous-core' ), 'textarea', false, __( 'One per line.', 'iamfamous-core' ) ),
				'audience' => array( __( 'Who is it for?', 'iamfamous-core' ), 'text', true, __( 'e.g. "Small business owners in the UK"', 'iamfamous-core' ) ),
				'tone'     => array( __( 'Tone of voice', 'iamfamous-core' ), 'select', true, '', array( 'professional' => __( 'Professional', 'iamfamous-core' ), 'friendly' => __( 'Friendly and conversational', 'iamfamous-core' ), 'technical' => __( 'Technical / expert', 'iamfamous-core' ), 'match' => __( 'Match my existing content', 'iamfamous-core' ) ) ),
				'english'  => array( __( 'English variant', 'iamfamous-core' ), 'select', true, '', array( 'uk' => __( 'UK English', 'iamfamous-core' ), 'us' => __( 'US English', 'iamfamous-core' ) ) ),
				'publish'  => array( __( 'Delivery', 'iamfamous-core' ), 'select', false, '', array( 'doc' => __( 'Google Doc / Word file', 'iamfamous-core' ), 'wp' => __( 'Publish to my WordPress (invite needed)', 'iamfamous-core' ) ) ),
			) + $common,
			'design'  => array(
				'business_name' => array( __( 'Brand / business name', 'iamfamous-core' ), 'text', true, '' ),
				'site_url'      => array( __( 'Website or social profile', 'iamfamous-core' ), 'url', false, '' ),
				'formats'       => array( __( 'What do you need and where will it be used?', 'iamfamous-core' ), 'textarea', true, __( 'e.g. "5 Instagram posts, 1080×1080" or "logo for website and signage".', 'iamfamous-core' ) ),
				'copy'          => array( __( 'Text to include', 'iamfamous-core' ), 'textarea', false, '' ),
				'style'         => array( __( 'Style direction', 'iamfamous-core' ), 'select', true, '', array( 'minimal' => __( 'Clean and minimal', 'iamfamous-core' ), 'bold' => __( 'Bold and colourful', 'iamfamous-core' ), 'premium' => __( 'Premium / luxury', 'iamfamous-core' ), 'playful' => __( 'Playful', 'iamfamous-core' ), 'unsure' => __( 'Not sure, suggest options', 'iamfamous-core' ) ) ),
				'references'    => array( __( 'Designs you like', 'iamfamous-core' ), 'urls', false, __( 'Links, one per line. Upload brand files after checkout.', 'iamfamous-core' ) ),
			) + $common,
			'seo'     => array(
				'site_url'    => array( __( 'Website URL', 'iamfamous-core' ), 'url', true, '' ),
				'platform'    => array( __( 'Platform', 'iamfamous-core' ), 'select', true, '', $platforms ),
				'market'      => array( __( 'Target market', 'iamfamous-core' ), 'text', true, __( 'Country / city, and B2B or B2C.', 'iamfamous-core' ) ),
				'priority'    => array( __( 'Priority pages', 'iamfamous-core' ), 'urls', false, __( 'One per line.', 'iamfamous-core' ) ),
				'competitors' => array( __( 'Main competitors', 'iamfamous-core' ), 'urls', false, __( 'One per line.', 'iamfamous-core' ) ),
				'access'      => array( __( 'Search Console / Analytics access', 'iamfamous-core' ), 'select', false, __( 'Never send passwords.', 'iamfamous-core' ), array( 'invite' => __( 'I will invite you as a user', 'iamfamous-core' ), 'none' => __( 'I do not have them set up', 'iamfamous-core' ) ) ),
			) + $common,
			'ads'     => array(
				'site_url'  => array( __( 'Website or landing page', 'iamfamous-core' ), 'url', true, '' ),
				'offer'     => array( __( 'What are you advertising?', 'iamfamous-core' ), 'textarea', true, __( 'Product, service or offer, and what makes it different.', 'iamfamous-core' ) ),
				'goal'      => array( __( 'Main goal', 'iamfamous-core' ), 'select', true, '', array( 'leads' => __( 'Leads (calls, forms, bookings)', 'iamfamous-core' ), 'sales' => __( 'Online sales', 'iamfamous-core' ), 'traffic' => __( 'Website visits', 'iamfamous-core' ), 'awareness' => __( 'Brand awareness', 'iamfamous-core' ) ) ),
				'budget'    => array( __( 'Monthly ad budget', 'iamfamous-core' ), 'select', true, __( 'Paid directly to the ad platform, not to us.', 'iamfamous-core' ), array( 'u500' => __( 'Under £500', 'iamfamous-core' ), '500-2k' => '£500 – £2,000', '2k-5k' => '£2,000 – £5,000', '5k+' => __( '£5,000+', 'iamfamous-core' ) ) ),
				'locations' => array( __( 'Target locations', 'iamfamous-core' ), 'text', true, __( 'Countries, cities or radius.', 'iamfamous-core' ) ),
				'audience'  => array( __( 'Ideal customer', 'iamfamous-core' ), 'textarea', false, '' ),
				'account'   => array( __( 'Ad account', 'iamfamous-core' ), 'select', true, __( 'We will send our access request. Never share passwords.', 'iamfamous-core' ), array( 'have' => __( 'I have an account and will grant access', 'iamfamous-core' ), 'new' => __( 'Please help me set one up', 'iamfamous-core' ) ) ),
			) + $common,
			'social'  => array(
				'business_name' => array( __( 'Brand / business name', 'iamfamous-core' ), 'text', true, '' ),
				'platforms'     => array( __( 'Platforms', 'iamfamous-core' ), 'text', true, __( 'e.g. Instagram and LinkedIn. Add profile links.', 'iamfamous-core' ) ),
				'audience'      => array( __( 'Who are you trying to reach?', 'iamfamous-core' ), 'text', true, '' ),
				'tone'          => array( __( 'Tone of voice', 'iamfamous-core' ), 'select', true, '', array( 'professional' => __( 'Professional', 'iamfamous-core' ), 'friendly' => __( 'Friendly', 'iamfamous-core' ), 'bold' => __( 'Bold and playful', 'iamfamous-core' ), 'premium' => __( 'Premium', 'iamfamous-core' ) ) ),
				'topics'        => array( __( 'Products, services or topics to feature', 'iamfamous-core' ), 'textarea', true, '' ),
				'examples'      => array( __( 'Accounts you like', 'iamfamous-core' ), 'urls', false, __( 'One per line. Brand files can be uploaded after checkout.', 'iamfamous-core' ) ),
			) + $common,
			'generic' => array(
				'site_url' => array( __( 'Website URL', 'iamfamous-core' ), 'url', false, '' ),
				'details'  => array( __( 'What do you need?', 'iamfamous-core' ), 'textarea', true, '' ),
			) + $common,
		);
	}

	/** One form field, shared by the order form and the brief. */
	public static function field_html( string $name, string $id, array $def, string $value ): string {
		$req  = $def[2] ? ' required aria-required="true"' : '';
		$desc = $def[3] ? ' aria-describedby="' . esc_attr( $id ) . '-help"' : '';
		$html = '<p class="form-row form-row-wide ifm-field ifm-field--' . esc_attr( $def[1] ) . '"><label for="' . esc_attr( $id ) . '">' . esc_html( $def[0] ) . ( $def[2] ? ' <abbr class="required" title="required">*</abbr>' : '' ) . '</label>';
		if ( in_array( $def[1], array( 'textarea', 'urls' ), true ) ) {
			$html .= '<textarea class="input-text" rows="' . ( 'urls' === $def[1] ? 2 : 3 ) . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"' . $req . $desc . '>' . esc_textarea( $value ) . '</textarea>';
		} elseif ( 'select' === $def[1] ) {
			$html .= '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"' . $req . $desc . '>';
			$opts  = $def[4] ?? array();
			if ( ! isset( $opts[''] ) ) {
				$html .= '<option value="">' . esc_html__( 'Choose…', 'iamfamous-core' ) . '</option>';
			}
			foreach ( $opts as $k => $label ) {
				$html .= '<option value="' . esc_attr( (string) $k ) . '"' . selected( $value, (string) $k, false ) . '>' . esc_html( $label ) . '</option>';
			}
			$html .= '</select>';
		} else {
			$type  = in_array( $def[1], array( 'url', 'email' ), true ) ? $def[1] : 'text';
			$html .= '<input class="input-text" type="' . esc_attr( $type ) . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '"' . $req . $desc . ( 'url' === $type ? ' placeholder="https://"' : '' ) . '>';
		}
		if ( $def[3] ) {
			$html .= '<span class="description ifm-field__help" id="' . esc_attr( $id ) . '-help">' . esc_html( $def[3] ) . '</span>';
		}
		return $html . '</p>';
	}

	/** Display value for a stored field (select keys become labels). */
	public static function display_value( array $def, string $value ): string {
		if ( 'select' === $def[1] && isset( $def[4][ $value ] ) ) {
			return (string) $def[4][ $value ];
		}
		return $value;
	}

	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'add_endpoint' ) );
		add_filter( 'woocommerce_get_query_vars', array( __CLASS__, 'query_vars' ) );
		add_action( 'woocommerce_account_' . self::ENDPOINT . '_endpoint', array( __CLASS__, 'render_endpoint' ) );
		add_action( 'template_redirect', array( __CLASS__, 'handle_post' ) );
		add_action( 'template_redirect', array( __CLASS__, 'maybe_serve_file' ) );
		add_action( 'woocommerce_order_details_before_order_table', array( __CLASS__, 'order_banner' ) );
		add_action( 'woocommerce_thankyou', array( __CLASS__, 'thankyou_banner' ), 5 );
		add_action( 'add_meta_boxes', array( __CLASS__, 'admin_meta_box' ) );
		add_action( 'woocommerce_order_status_sb-awaiting-brief', array( __CLASS__, 'schedule_reminders' ) );
		add_action( 'ifm_brief_reminder', array( __CLASS__, 'send_reminder' ) );
		add_filter( 'manage_woocommerce_page_wc-orders_columns', array( __CLASS__, 'admin_column' ) );
		add_action( 'manage_woocommerce_page_wc-orders_custom_column', array( __CLASS__, 'admin_column_value' ), 10, 2 );
	}

	public static function add_endpoint(): void {
		add_rewrite_endpoint( self::ENDPOINT, EP_ROOT | EP_PAGES );
	}

	public static function query_vars( array $vars ): array {
		$vars[ self::ENDPOINT ] = self::ENDPOINT;
		return $vars;
	}

	/** Profiles required by an order (one form section per plan line). */
	public static function lines( WC_Order $order ): array {
		$out = array();
		foreach ( $order->get_items() as $item_id => $item ) {
			if ( ! $item instanceof WC_Order_Item_Product ) {
				continue;
			}
			$profile = $item->get_meta( 'ifm_brief_profile' );
			if ( ! $profile && IFM_Packages::is_package( $item->get_product_id() ) ) {
				$profile = get_post_meta( $item->get_product_id(), '_ifm_brief_profile', true );
			}
			if ( $profile ) {
				$out[ $item_id ] = array(
					'name'    => $item->get_name(),
					'profile' => isset( self::profiles()[ $profile ] ) ? $profile : 'generic',
				);
			}
		}
		return $out;
	}

	public static function url( WC_Order $order ): string {
		return wc_get_endpoint_url( self::ENDPOINT, (string) $order->get_id(), wc_get_page_permalink( 'myaccount' ) );
	}

	/** Current user may view/edit the brief of this order. */
	private static function can_access( WC_Order $order ): bool {
		$uid = get_current_user_id();
		return ( $uid && (int) $order->get_customer_id() === $uid ) || current_user_can( 'edit_shop_orders' );
	}

	public static function status( WC_Order $order ): string {
		return $order->get_meta( '_ifm_brief_status' ) ? $order->get_meta( '_ifm_brief_status' ) : 'pending';
	}

	public static function render_endpoint( $order_id ): void {
		$order = wc_get_order( absint( $order_id ) );
		if ( ! $order || ! self::can_access( $order ) || ! self::lines( $order ) ) {
			wc_print_notice( __( 'Order not found.', 'iamfamous-core' ), 'error' );
			return;
		}
		$data  = (array) $order->get_meta( '_ifm_brief_data' );
		$files = (array) $order->get_meta( '_ifm_brief_files' );
		/* translators: %s: order number */
		echo '<h2>' . esc_html( sprintf( __( 'Project brief for order #%s', 'iamfamous-core' ), $order->get_order_number() ) ) . '</h2>';
		echo '<p>' . esc_html__( 'This tells our team exactly what to deliver. Please never include passwords — we will ask you to invite us as a user if access is needed.', 'iamfamous-core' ) . '</p>';
		echo '<form method="post" enctype="multipart/form-data" class="ifm-brief-form woocommerce-EditAccountForm">';
		wp_nonce_field( 'ifm_brief_' . $order->get_id(), 'ifm_brief_nonce' );
		echo '<input type="hidden" name="ifm_brief_order" value="' . esc_attr( (string) $order->get_id() ) . '">';
		foreach ( self::lines( $order ) as $item_id => $line ) {
			echo '<fieldset class="ifm-brief-line"><legend>' . esc_html( $line['name'] ) . '</legend>';
			foreach ( self::profiles()[ $line['profile'] ] as $key => $def ) {
				echo self::field_html( 'ifm_brief[' . (int) $item_id . '][' . $key . ']', 'ifm-brief-' . (int) $item_id . '-' . $key, $def, (string) ( $data[ $item_id ][ $key ] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in field_html().
			}
			echo '</fieldset>';
		}
		echo '<fieldset><legend>' . esc_html__( 'Files (logos, brand guides, briefs)', 'iamfamous-core' ) . '</legend>';
		if ( $files ) {
			echo '<ul>';
			foreach ( $files as $f ) {
				echo '<li><a href="' . esc_url( self::file_url( $order, $f['name'] ) ) . '">' . esc_html( $f['original'] ) . '</a></li>';
			}
			echo '</ul>';
		}
		/* translators: 1: max files, 2: max MB, 3: extensions */
		echo '<p><label for="ifm-brief-files">' . esc_html( sprintf( __( 'Add files (up to %1$d files, %2$d MB each: %3$s)', 'iamfamous-core' ), self::MAX_FILES, self::MAX_BYTES / 1048576, implode( ', ', array_keys( self::MIMES ) ) ) ) . '</label> <input type="file" id="ifm-brief-files" name="ifm_brief_files[]" multiple accept=".' . esc_attr( implode( ',.', array_keys( self::MIMES ) ) ) . '"></p></fieldset>';
		echo '<p><button type="submit" class="button ifm-btn ifm-btn--primary">' . esc_html__( 'Save brief', 'iamfamous-core' ) . '</button></p></form>';
	}

	public static function handle_post(): void {
		if ( empty( $_POST['ifm_brief_order'] ) || empty( $_POST['ifm_brief_nonce'] ) ) {
			return;
		}
		$order = wc_get_order( absint( $_POST['ifm_brief_order'] ) );
		if ( ! $order || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ifm_brief_nonce'] ) ), 'ifm_brief_' . $order->get_id() ) || ! self::can_access( $order ) ) {
			wc_add_notice( __( 'Sorry, that brief could not be saved. Please reload and try again.', 'iamfamous-core' ), 'error' );
			return;
		}
		$lines    = self::lines( $order );
		$profiles = self::profiles();
		$input    = isset( $_POST['ifm_brief'] ) && is_array( $_POST['ifm_brief'] ) ? wp_unslash( $_POST['ifm_brief'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized per field below.
		$data     = array();
		$missing  = 0;
		foreach ( $lines as $item_id => $line ) {
			foreach ( $profiles[ $line['profile'] ] as $key => $def ) {
				$raw = isset( $input[ $item_id ][ $key ] ) ? (string) $input[ $item_id ][ $key ] : '';
				$val = self::sanitize( $raw, $def );
				if ( $def[2] && '' === $val ) {
					++$missing;
				}
				$data[ $item_id ][ $key ] = $val;
			}
		}
		$order->update_meta_data( '_ifm_brief_data', $data );
		self::handle_uploads( $order );
		$status = $missing ? 'partial' : 'complete';
		$order->update_meta_data( '_ifm_brief_status', $status );
		$order->update_meta_data( '_ifm_brief_submitted_at', gmdate( 'c' ) );
		$order->save();

		if ( 'complete' === $status && $order->has_status( 'ifm-awaiting-brief' ) ) {
			$order->update_status( 'ifm-brief-received', __( 'Customer completed the project brief.', 'iamfamous-core' ) );
			self::notify_admin( $order );
		}
		wc_add_notice(
			$missing
				/* translators: %d: number of missing required fields */
				? sprintf( _n( 'Brief saved. %d required field is still empty.', 'Brief saved. %d required fields are still empty.', $missing, 'iamfamous-core' ), $missing )
				: __( 'Thank you — your brief is complete and our team has been notified.', 'iamfamous-core' ),
			$missing ? 'notice' : 'success'
		);
		wp_safe_redirect( self::url( $order ) );
		exit;
	}

	/**
	 * @param array $def Field definition (type in [1], select options in [4]).
	 */
	public static function sanitize( string $raw, array $def ): string {
		switch ( $def[1] ) {
			case 'select':
				$raw = sanitize_key( $raw );
				return array_key_exists( $raw, $def[4] ?? array() ) ? $raw : '';
			case 'url':
				return esc_url_raw( trim( $raw ), array( 'http', 'https' ) );
			case 'urls':
				$urls = array_filter( array_map( static fn( $u ) => esc_url_raw( trim( $u ), array( 'http', 'https' ) ), preg_split( '/\r\n|\r|\n/', $raw ) ) );
				return implode( "\n", array_slice( $urls, 0, 50 ) );
			case 'email':
				return sanitize_email( $raw );
			case 'textarea':
				return mb_substr( sanitize_textarea_field( $raw ), 0, 5000 );
			default:
				return mb_substr( sanitize_text_field( $raw ), 0, 500 );
		}
	}

	/** Absolute path of the protected upload folder. */
	public static function base_dir(): string {
		$up = wp_upload_dir( null, false );
		return trailingslashit( $up['basedir'] ) . self::UPLOAD_DIR;
	}

	public static function protect_upload_dir(): void {
		$dir = self::base_dir();
		wp_mkdir_p( $dir );
		if ( ! file_exists( $dir . '/.htaccess' ) ) {
			file_put_contents( $dir . '/.htaccess', "Require all denied\nDeny from all\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}
		if ( ! file_exists( $dir . '/index.php' ) ) {
			file_put_contents( $dir . '/index.php', "<?php\n// Silence.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}
	}

	private static function handle_uploads( WC_Order $order ): void {
		// phpcs:disable WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput -- nonce verified in handle_post(); file fields validated below.
		if ( empty( $_FILES['ifm_brief_files']['name'][0] ) ) {
			return;
		}
		self::protect_upload_dir();
		$existing = (array) $order->get_meta( '_ifm_brief_files' );
		$dir      = self::base_dir() . '/' . $order->get_id();
		wp_mkdir_p( $dir );
		$f     = $_FILES['ifm_brief_files'];
		$count = count( (array) $f['name'] );
		for ( $i = 0; $i < $count; $i++ ) {
			if ( count( $existing ) >= self::MAX_FILES ) {
				wc_add_notice( __( 'File limit reached for this order.', 'iamfamous-core' ), 'notice' );
				break;
			}
			if ( UPLOAD_ERR_OK !== (int) $f['error'][ $i ] || ! is_uploaded_file( $f['tmp_name'][ $i ] ) ) {
				continue;
			}
			$original = sanitize_file_name( $f['name'][ $i ] );
			if ( (int) $f['size'][ $i ] > self::MAX_BYTES ) {
				/* translators: %s: file name */
				wc_add_notice( sprintf( __( '%s is larger than 20 MB and was skipped.', 'iamfamous-core' ), $original ), 'error' );
				continue;
			}
			$check = wp_check_filetype_and_ext( $f['tmp_name'][ $i ], $original, self::MIMES );
			if ( empty( $check['ext'] ) || empty( $check['type'] ) ) {
				/* translators: %s: file name */
				wc_add_notice( sprintf( __( '%s is not an allowed file type and was skipped.', 'iamfamous-core' ), $original ), 'error' );
				continue;
			}
			$stored = wp_generate_password( 20, false ) . '.' . $check['ext'];
			if ( move_uploaded_file( $f['tmp_name'][ $i ], $dir . '/' . $stored ) ) {
				$existing[] = array(
					'name'     => $stored,
					'original' => $original,
					'type'     => $check['type'],
					'at'       => gmdate( 'c' ),
				);
			}
		}
		// phpcs:enable
		$order->update_meta_data( '_ifm_brief_files', $existing );
	}

	public static function file_url( WC_Order $order, string $stored ): string {
		return add_query_arg(
			array(
				'ifm_file'  => rawurlencode( $stored ),
				'order'    => $order->get_id(),
				'_wpnonce' => wp_create_nonce( 'ifm_file_' . $order->get_id() ),
			),
			home_url( '/' )
		);
	}

	/** Capability-checked download of a brief file. */
	public static function maybe_serve_file(): void {
		if ( empty( $_GET['ifm_file'] ) || empty( $_GET['order'] ) ) {
			return;
		}
		$order = wc_get_order( absint( $_GET['order'] ) );
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		if ( ! $order || ! wp_verify_nonce( $nonce, 'ifm_file_' . $order->get_id() ) || ! self::can_access( $order ) ) {
			wp_die( esc_html__( 'You do not have access to this file.', 'iamfamous-core' ), '', array( 'response' => 403 ) );
		}
		$stored = sanitize_file_name( wp_unslash( $_GET['ifm_file'] ) );
		foreach ( (array) $order->get_meta( '_ifm_brief_files' ) as $f ) {
			if ( $f['name'] === $stored ) {
				$path = self::base_dir() . '/' . $order->get_id() . '/' . $stored;
				if ( is_readable( $path ) ) {
					nocache_headers();
					header( 'Content-Type: ' . $f['type'] );
					header( 'Content-Disposition: attachment; filename="' . rawurlencode( $f['original'] ) . '"' );
					header( 'X-Content-Type-Options: nosniff' );
					header( 'Content-Length: ' . filesize( $path ) );
					readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
					exit;
				}
			}
		}
		wp_die( esc_html__( 'File not found.', 'iamfamous-core' ), '', array( 'response' => 404 ) );
	}

	/** Banner on the customer's order view. */
	public static function order_banner( WC_Order $order ): void {
		if ( ! self::lines( $order ) || ! $order->is_paid() ) {
			return;
		}
		$status = self::status( $order );
		$label  = 'complete' === $status ? __( 'Brief complete', 'iamfamous-core' ) : __( 'Brief needed', 'iamfamous-core' );
		$text   = 'complete' === $status ? __( 'Edit brief', 'iamfamous-core' ) : __( 'Complete your brief', 'iamfamous-core' );
		echo '<div class="woocommerce-info ifm-brief-banner"><strong>' . esc_html( $label ) . '.</strong> ';
		if ( 'complete' !== $status ) {
			echo esc_html__( 'Work starts as soon as we have your project details.', 'iamfamous-core' ) . ' ';
		}
		echo '<a class="button" href="' . esc_url( self::url( $order ) ) . '">' . esc_html( $text ) . '</a></div>';
	}

	public static function thankyou_banner( $order_id ): void {
		$order = wc_get_order( $order_id );
		if ( ! $order || ! self::lines( $order ) ) {
			return;
		}
		if ( ! $order->get_customer_id() ) {
			echo '<div class="woocommerce-info">' . esc_html__( 'Next step: we will email you a link to share your project details. To submit them online, create an account with the same email address.', 'iamfamous-core' ) . '</div>';
			return;
		}
		self::order_banner( $order );
	}

	public static function admin_meta_box(): void {
		$screen = function_exists( 'wc_get_page_screen_id' ) ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order';
		add_meta_box( 'ifm_brief', __( 'Project brief', 'iamfamous-core' ), array( __CLASS__, 'render_admin_box' ), $screen, 'normal', 'high' );
	}

	public static function render_admin_box( $post_or_order ): void {
		$order = $post_or_order instanceof WC_Order ? $post_or_order : wc_get_order( $post_or_order->ID );
		if ( ! $order ) {
			return;
		}
		$lines = self::lines( $order );
		if ( ! $lines ) {
			echo '<p>' . esc_html__( 'No service plans on this order.', 'iamfamous-core' ) . '</p>';
			return;
		}
		$data = (array) $order->get_meta( '_ifm_brief_data' );
		/* translators: %s: brief status */
		echo '<p><strong>' . esc_html( sprintf( __( 'Status: %s', 'iamfamous-core' ), self::status( $order ) ) ) . '</strong>';
		if ( $order->get_meta( '_ifm_brief_submitted_at' ) ) {
			echo ' · ' . esc_html( $order->get_meta( '_ifm_brief_submitted_at' ) );
		}
		echo '</p>';
		foreach ( $lines as $item_id => $line ) {
			echo '<h4>' . esc_html( $line['name'] ) . '</h4><table class="widefat striped"><tbody>';
			foreach ( self::profiles()[ $line['profile'] ] as $key => $def ) {
				$val = self::display_value( $def, (string) ( $data[ $item_id ][ $key ] ?? '' ) );
				echo '<tr><th style="width:30%">' . esc_html( $def[0] ) . '</th><td>' . ( '' === $val ? '<em>—</em>' : nl2br( esc_html( $val ) ) ) . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inline.
			}
			echo '</tbody></table>';
		}
		$files = (array) $order->get_meta( '_ifm_brief_files' );
		if ( $files ) {
			echo '<h4>' . esc_html__( 'Files', 'iamfamous-core' ) . '</h4><ul>';
			foreach ( $files as $f ) {
				echo '<li><a href="' . esc_url( self::file_url( $order, $f['name'] ) ) . '">' . esc_html( $f['original'] ) . '</a></li>';
			}
			echo '</ul>';
		}
	}

	public static function admin_column( array $cols ): array {
		$cols['ifm_brief'] = __( 'Brief', 'iamfamous-core' );
		return $cols;
	}

	public static function admin_column_value( string $col, $order ): void {
		if ( 'ifm_brief' !== $col || ! $order instanceof WC_Order || ! self::lines( $order ) ) {
			return;
		}
		$s = self::status( $order );
		echo '<mark class="order-status ' . ( 'complete' === $s ? 'status-completed' : 'status-on-hold' ) . '"><span>' . esc_html( ucfirst( $s ) ) . '</span></mark>';
	}

	public static function schedule_reminders( int $order_id ): void {
		if ( ! function_exists( 'as_schedule_single_action' ) ) {
			return;
		}
		foreach ( array( DAY_IN_SECONDS, 3 * DAY_IN_SECONDS ) as $delay ) {
			as_schedule_single_action( time() + $delay, 'ifm_brief_reminder', array( 'order_id' => $order_id ), 'iamfamous' );
		}
	}

	public static function send_reminder( int $order_id ): void {
		$order = wc_get_order( $order_id );
		if ( ! $order || 'complete' === self::status( $order ) || ! $order->has_status( 'ifm-awaiting-brief' ) ) {
			return;
		}
		$link   = $order->get_customer_id() ? self::url( $order ) : wc_get_page_permalink( 'myaccount' );
		$mailer = WC()->mailer();
		$head   = __( 'We need your project details', 'iamfamous-core' );
		$body   = '<p>' . esc_html__( 'Thanks for your order. We are ready to start as soon as you share your project brief.', 'iamfamous-core' ) . '</p><p><a href="' . esc_url( $link ) . '">' . esc_html__( 'Complete your brief', 'iamfamous-core' ) . '</a></p>';
		/* translators: %s: order number */
		$mailer->send( $order->get_billing_email(), sprintf( __( 'Order #%s: please complete your brief', 'iamfamous-core' ), $order->get_order_number() ), $mailer->wrap_message( $head, $body ) );
		$order->add_order_note( __( 'Brief reminder sent.', 'iamfamous-core' ) );
	}

	private static function notify_admin( WC_Order $order ): void {
		$mailer = WC()->mailer();
		/* translators: %s: order number */
		$subject = sprintf( __( 'Brief received for order #%s', 'iamfamous-core' ), $order->get_order_number() );
		$body    = '<p><a href="' . esc_url( $order->get_edit_order_url() ) . '">' . esc_html__( 'Open the order', 'iamfamous-core' ) . '</a></p>';
		$mailer->send( get_option( 'admin_email' ), $subject, $mailer->wrap_message( $subject, $body ) );
	}
}
