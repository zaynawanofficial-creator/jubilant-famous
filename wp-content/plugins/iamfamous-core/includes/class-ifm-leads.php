<?php
/**
 * Contact and quote forms: [ifm_lead_form type="contact|quote"].
 * Leads are stored privately (wp-admin → Leads) and emailed to the site admin.
 * Spam protection: nonce, honeypot, minimum fill time, per-IP rate limit. No third-party services.
 *
 * @package IamFamousCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lead capture.
 */
class IFM_Leads {

	const CPT       = 'ifm_lead';
	const MIN_SECS  = 3;
	const RATE_MAX  = 5;

	public static function services(): array {
		return array(
			'monthly-seo'         => __( 'Monthly SEO', 'iamfamous-core' ),
			'off-page-seo'        => __( 'Off-Page SEO & Link Building', 'iamfamous-core' ),
			'local-seo'           => __( 'Local SEO', 'iamfamous-core' ),
			'ecommerce-seo'       => __( 'Ecommerce SEO', 'iamfamous-core' ),
			'on-page-seo'         => __( 'On-Page SEO', 'iamfamous-core' ),
			'ai-seo'              => __( 'AI SEO', 'iamfamous-core' ),
			'white-label-seo'     => __( 'White Label SEO', 'iamfamous-core' ),
			'seo-consultancy'     => __( 'SEO Consultancy', 'iamfamous-core' ),
			'website-migration'   => __( 'Website Migration', 'iamfamous-core' ),
			'ecommerce-website-development' => __( 'Ecommerce Website', 'iamfamous-core' ),
			'facebook-ads'        => __( 'Facebook Ads', 'iamfamous-core' ),
			'tiktok-ads'          => __( 'TikTok Ads', 'iamfamous-core' ),
			'social-media-management' => __( 'Social Media Management', 'iamfamous-core' ),
			'seo-audit'           => __( 'SEO Audit', 'iamfamous-core' ),
			'technical-seo'       => __( 'Technical SEO', 'iamfamous-core' ),
			'website-development' => __( 'Website Development', 'iamfamous-core' ),
			'website-maintenance' => __( 'Website Maintenance & Fixes', 'iamfamous-core' ),
			'content-writing'     => __( 'Content Writing', 'iamfamous-core' ),
			'graphic-design'      => __( 'Graphic Design & Branding', 'iamfamous-core' ),
			'ppc-management'      => __( 'PPC Management', 'iamfamous-core' ),
			'analytics'           => __( 'Analytics & Tracking', 'iamfamous-core' ),
			'email'               => __( 'Email Marketing', 'iamfamous-core' ),
			'automation'          => __( 'Marketing Automation', 'iamfamous-core' ),
			'other'               => __( 'Something else / not sure', 'iamfamous-core' ),
		);
	}

	public static function budgets(): array {
		return array(
			''          => __( 'Select a range (optional)', 'iamfamous-core' ),
			'under-100' => __( 'Under £100', 'iamfamous-core' ),
			'100-500'   => '£100 – £500',
			'500-1500'  => '£500 – £1,500',
			'1500-plus' => __( '£1,500+', 'iamfamous-core' ),
		);
	}

	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_shortcode( 'ifm_lead_form', array( __CLASS__, 'render' ) );
		add_action( 'template_redirect', array( __CLASS__, 'handle' ) );
		add_filter( 'manage_' . self::CPT . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . self::CPT . '_posts_custom_column', array( __CLASS__, 'column' ), 10, 2 );
		add_action( 'add_meta_boxes_' . self::CPT, array( __CLASS__, 'meta_box' ) );
	}

	public static function register(): void {
		register_post_type(
			self::CPT,
			array(
				'labels'          => array(
					'name'          => __( 'Leads', 'iamfamous-core' ),
					'singular_name' => __( 'Lead', 'iamfamous-core' ),
					'edit_item'     => __( 'Lead', 'iamfamous-core' ),
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => true,
				'menu_icon'       => 'dashicons-email-alt',
				'menu_position'   => 56,
				'supports'        => array( 'title' ),
				'capability_type' => 'page',
				'map_meta_cap'    => true,
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
				'show_in_rest'    => false,
			)
		);
	}

	private static function ip_hash(): string {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		return substr( hash_hmac( 'sha256', $ip, wp_salt( 'nonce' ) ), 0, 20 );
	}

	public static function render( $atts ): string {
		$atts = shortcode_atts( array( 'type' => 'contact' ), $atts, 'ifm_lead_form' );
		$type = 'quote' === $atts['type'] ? 'quote' : 'contact';
		// Never serve a cached copy of a page with a form (stale nonces).
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
		do_action( 'litespeed_control_set_nocache', 'ifm lead form' );
		if ( ! headers_sent() ) {
			nocache_headers();
		}

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- display only.
		$state  = isset( $_GET['ifm_lead'] ) ? sanitize_key( wp_unslash( $_GET['ifm_lead'] ) ) : '';
		$token  = isset( $_GET['ifm_t'] ) ? sanitize_key( wp_unslash( $_GET['ifm_t'] ) ) : '';
		$pre    = array(
			'service' => isset( $_GET['service'] ) ? sanitize_key( wp_unslash( $_GET['service'] ) ) : '',
			'package' => isset( $_GET['package'] ) ? sanitize_text_field( wp_unslash( $_GET['package'] ) ) : '',
		);
		// phpcs:enable
		$errors = array();
		$values = array();
		if ( 'error' === $state && $token ) {
			$saved  = get_transient( 'ifm_lead_err_' . $token );
			$errors = $saved['errors'] ?? array();
			$values = $saved['values'] ?? array();
		}
		$v = static fn( $k ) => esc_attr( $values[ $k ] ?? '' );

		ob_start();
		echo '<div id="ifm-form" class="ifm-form-wrap">';
		if ( 'sent' === $state ) {
			echo '<div class="ifm-form__msg ifm-form__msg--ok" role="status">' . esc_html( 'quote' === $type ? __( 'Thank you. We have your request and will reply by email within one working day.', 'iamfamous-core' ) : __( 'Thank you. Your message has been sent and we will reply by email.', 'iamfamous-core' ) ) . '</div>';
		}
		if ( $errors ) {
			echo '<div class="ifm-form__msg ifm-form__msg--err" role="alert"><ul>';
			foreach ( $errors as $e ) {
				echo '<li>' . esc_html( $e ) . '</li>';
			}
			echo '</ul></div>';
		}
		$req = ' <span class="ifm-form__req" aria-hidden="true">*</span>';
		echo '<form class="ifm-form" method="post" action="' . esc_url( get_permalink() ) . '#ifm-form" novalidate>';
		wp_nonce_field( 'ifm_lead_' . $type, 'ifm_lead_nonce' );
		echo '<input type="hidden" name="ifm_lead_type" value="' . esc_attr( $type ) . '">';
		echo '<input type="hidden" name="ifm_lead_ts" value="' . esc_attr( (string) time() ) . '">';
		echo '<input type="hidden" name="ifm_lead_page" value="' . esc_attr( (string) get_the_ID() ) . '">';
		echo '<div class="ifm-form__hp" aria-hidden="true"><label for="ifm-hp-' . esc_attr( $type ) . '">Leave empty</label><input type="text" id="ifm-hp-' . esc_attr( $type ) . '" name="ifm_hp" tabindex="-1" autocomplete="off"></div>';
		echo '<div class="ifm-form__row">';
		echo '<p><label for="ifm-name">' . esc_html__( 'Your name', 'iamfamous-core' ) . $req . '</label><input type="text" id="ifm-name" name="ifm_name" required autocomplete="name" value="' . $v( 'name' ) . '"></p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup.
		echo '<p><label for="ifm-email">' . esc_html__( 'Email', 'iamfamous-core' ) . $req . '</label><input type="email" id="ifm-email" name="ifm_email" required autocomplete="email" value="' . $v( 'email' ) . '"></p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup.
		echo '</div><div class="ifm-form__row">';
		echo '<p><label for="ifm-website">' . esc_html__( 'Website (optional)', 'iamfamous-core' ) . '</label><input type="url" id="ifm-website" name="ifm_website" placeholder="https://" autocomplete="url" value="' . $v( 'website' ) . '"></p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in $v.
		$sel = $values['service'] ?? $pre['service'];
		echo '<p><label for="ifm-service">' . esc_html__( 'Service', 'iamfamous-core' ) . ( 'quote' === $type ? $req : '' ) . '</label><select id="ifm-service" name="ifm_service"' . ( 'quote' === $type ? ' required' : '' ) . '><option value="">' . esc_html__( 'Choose a service', 'iamfamous-core' ) . '</option>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup.
		foreach ( self::services() as $k => $label ) {
			echo '<option value="' . esc_attr( $k ) . '"' . selected( $sel, $k, false ) . '>' . esc_html( $label ) . '</option>';
		}
		echo '</select></p></div>';
		if ( 'quote' === $type ) {
			$pkg = $values['package'] ?? $pre['package'];
			echo '<div class="ifm-form__row">';
			echo '<p><label for="ifm-budget">' . esc_html__( 'Budget', 'iamfamous-core' ) . '</label><select id="ifm-budget" name="ifm_budget">';
			foreach ( self::budgets() as $k => $label ) {
				echo '<option value="' . esc_attr( $k ) . '"' . selected( $values['budget'] ?? '', $k, false ) . '>' . esc_html( $label ) . '</option>';
			}
			echo '</select></p>';
			echo '<p><label for="ifm-package">' . esc_html__( 'Package of interest (optional)', 'iamfamous-core' ) . '</label><input type="text" id="ifm-package" name="ifm_package" value="' . esc_attr( $pkg ) . '"></p></div>';
		}
		echo '<p><label for="ifm-message">' . esc_html( 'quote' === $type ? __( 'What do you need?', 'iamfamous-core' ) : __( 'Message', 'iamfamous-core' ) ) . $req . '</label><textarea id="ifm-message" name="ifm_message" required rows="6" placeholder="' . esc_attr( 'quote' === $type ? __( 'Goals, pages, quantities, deadlines. Please do not include passwords.', 'iamfamous-core' ) : __( 'How can we help? Please do not include passwords.', 'iamfamous-core' ) ) . '">' . esc_textarea( $values['message'] ?? '' ) . '</textarea></p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup.
		$privacy = get_privacy_policy_url();
		echo '<p><label class="ifm-form__consent"><input type="checkbox" name="ifm_consent" value="1" required> <span>' . wp_kses_post( sprintf( /* translators: %s: privacy policy link */ $privacy ? __( 'I agree that IamFamous may store this information to reply to my request, as described in the %s.', 'iamfamous-core' ) : __( 'I agree that IamFamous may store this information to reply to my request. It is not used for anything else or passed to anyone else.%s', 'iamfamous-core' ), $privacy ? '<a href="' . esc_url( $privacy ) . '">' . esc_html__( 'privacy policy', 'iamfamous-core' ) . '</a>' : '' ) ) . '</span></label></p>';
		echo '<button type="submit" name="ifm_lead_submit" value="1">' . esc_html( 'quote' === $type ? __( 'Send my request', 'iamfamous-core' ) : __( 'Send message', 'iamfamous-core' ) ) . '</button>';
		echo '</form></div>';
		return (string) ob_get_clean();
	}

	public static function handle(): void {
		if ( empty( $_POST['ifm_lead_submit'] ) || empty( $_POST['ifm_lead_type'] ) ) {
			return;
		}
		$type = 'quote' === sanitize_key( wp_unslash( $_POST['ifm_lead_type'] ) ) ? 'quote' : 'contact';
		$page = isset( $_POST['ifm_lead_page'] ) ? absint( $_POST['ifm_lead_page'] ) : 0;
		$back = $page ? get_permalink( $page ) : home_url( '/' );
		if ( ! isset( $_POST['ifm_lead_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ifm_lead_nonce'] ) ), 'ifm_lead_' . $type ) ) {
			self::fail( $back, array( __( 'Your session expired. Please submit the form again.', 'iamfamous-core' ) ), array() );
		}
		$values = array(
			'name'    => sanitize_text_field( wp_unslash( $_POST['ifm_name'] ?? '' ) ),
			'email'   => sanitize_email( wp_unslash( $_POST['ifm_email'] ?? '' ) ),
			'website' => esc_url_raw( wp_unslash( $_POST['ifm_website'] ?? '' ), array( 'http', 'https' ) ),
			'service' => sanitize_key( wp_unslash( $_POST['ifm_service'] ?? '' ) ),
			'budget'  => sanitize_key( wp_unslash( $_POST['ifm_budget'] ?? '' ) ),
			'package' => sanitize_text_field( wp_unslash( $_POST['ifm_package'] ?? '' ) ),
			'message' => mb_substr( sanitize_textarea_field( wp_unslash( $_POST['ifm_message'] ?? '' ) ), 0, 5000 ),
		);
		// Silent drop for bots: honeypot filled or form submitted too fast.
		$ts = isset( $_POST['ifm_lead_ts'] ) ? absint( $_POST['ifm_lead_ts'] ) : 0;
		if ( ! empty( $_POST['ifm_hp'] ) || ( time() - $ts ) < self::MIN_SECS ) {
			wp_safe_redirect( add_query_arg( 'ifm_lead', 'sent', $back ) . '#ifm-form' );
			exit;
		}
		$errors = array();
		if ( '' === $values['name'] ) {
			$errors[] = __( 'Please enter your name.', 'iamfamous-core' );
		}
		if ( ! is_email( $values['email'] ) ) {
			$errors[] = __( 'Please enter a valid email address.', 'iamfamous-core' );
		}
		if ( 'quote' === $type && ! array_key_exists( $values['service'], self::services() ) ) {
			$errors[] = __( 'Please choose a service.', 'iamfamous-core' );
		}
		if ( mb_strlen( $values['message'] ) < 10 ) {
			$errors[] = __( 'Please add a few words about what you need.', 'iamfamous-core' );
		}
		if ( empty( $_POST['ifm_consent'] ) ) {
			$errors[] = __( 'Please tick the consent box so we can reply.', 'iamfamous-core' );
		}
		$rate_key = 'ifm_lead_rate_' . self::ip_hash();
		$count    = (int) get_transient( $rate_key );
		if ( $count >= self::RATE_MAX ) {
			$errors[] = __( 'Too many messages from your connection. Please email us instead.', 'iamfamous-core' );
		}
		if ( $errors ) {
			self::fail( $back, $errors, $values );
		}
		set_transient( $rate_key, $count + 1, HOUR_IN_SECONDS );

		$services = self::services();
		$label    = $services[ $values['service'] ] ?? '';
		$id       = wp_insert_post(
			array(
				'post_type'   => self::CPT,
				'post_status' => 'private',
				'post_title'  => sprintf( '%s – %s%s', 'quote' === $type ? 'Quote' : 'Contact', $values['name'], $label ? ' – ' . $label : '' ),
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			foreach ( $values as $k => $val ) {
				update_post_meta( $id, '_ifm_' . $k, $val );
			}
			update_post_meta( $id, '_ifm_type', $type );
			update_post_meta( $id, '_ifm_consent_at', gmdate( 'c' ) );
			update_post_meta( $id, '_ifm_source', $back );
		}

		$to      = apply_filters( 'ifm_lead_email', get_option( 'admin_email' ) );
		$subject = sprintf( '[%s] %s: %s', get_bloginfo( 'name' ), 'quote' === $type ? 'New quote request' : 'New message', $values['name'] );
		$body    = '';
		foreach ( array( 'name', 'email', 'website', 'service', 'budget', 'package', 'message' ) as $k ) {
			if ( '' !== $values[ $k ] ) {
				$body .= ucfirst( $k ) . ': ' . ( 'service' === $k ? $label : $values[ $k ] ) . "\n";
			}
		}
		$body .= "\nLead in wp-admin: " . admin_url( 'post.php?post=' . (int) $id . '&action=edit' ) . "\n";
		wp_mail( $to, $subject, $body, array( 'Reply-To: ' . $values['name'] . ' <' . $values['email'] . '>' ) );

		wp_safe_redirect( add_query_arg( 'ifm_lead', 'sent', $back ) . '#ifm-form' );
		exit;
	}

	private static function fail( string $back, array $errors, array $values ): void {
		$token = strtolower( wp_generate_password( 12, false ) );
		set_transient( 'ifm_lead_err_' . $token, array( 'errors' => $errors, 'values' => $values ), 10 * MINUTE_IN_SECONDS );
		wp_safe_redirect( add_query_arg( array( 'ifm_lead' => 'error', 'ifm_t' => $token ), $back ) . '#ifm-form' );
		exit;
	}

	public static function columns( array $cols ): array {
		return array(
			'cb'         => $cols['cb'] ?? '',
			'title'      => __( 'Lead', 'iamfamous-core' ),
			'ifm_email'   => __( 'Email', 'iamfamous-core' ),
			'ifm_type'    => __( 'Type', 'iamfamous-core' ),
			'ifm_service' => __( 'Service', 'iamfamous-core' ),
			'date'       => __( 'Received', 'iamfamous-core' ),
		);
	}

	public static function column( string $col, int $id ): void {
		$val = get_post_meta( $id, '_' . $col, true );
		if ( 'ifm_email' === $col ) {
			echo '<a href="mailto:' . esc_attr( $val ) . '">' . esc_html( $val ) . '</a>';
		} elseif ( 'ifm_service' === $col ) {
			echo esc_html( self::services()[ $val ] ?? $val );
		} else {
			echo esc_html( (string) $val );
		}
	}

	public static function meta_box(): void {
		add_meta_box(
			'ifm_lead_details',
			__( 'Lead details', 'iamfamous-core' ),
			static function ( WP_Post $post ) {
				echo '<table class="widefat striped"><tbody>';
				foreach ( array( 'type', 'name', 'email', 'website', 'service', 'budget', 'package', 'message', 'consent_at', 'source' ) as $k ) {
					$val = (string) get_post_meta( $post->ID, '_ifm_' . $k, true );
					if ( 'service' === $k ) {
						$val = self::services()[ $val ] ?? $val;
					}
					echo '<tr><th style="width:160px">' . esc_html( ucfirst( str_replace( '_', ' ', $k ) ) ) . '</th><td>' . nl2br( esc_html( $val ) ) . '</td></tr>';
				}
				echo '</tbody></table>';
				$email = (string) get_post_meta( $post->ID, '_ifm_email', true );
				if ( $email ) {
					echo '<p><a class="button button-primary" href="mailto:' . esc_attr( $email ) . '">' . esc_html__( 'Reply by email', 'iamfamous-core' ) . '</a></p>';
				}
			},
			self::CPT,
			'normal',
			'high'
		);
	}
}
