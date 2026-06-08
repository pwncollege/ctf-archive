<?php

namespace WPForms\Emails;

/**
 * Class Preview.
 * Handles previewing email templates.
 *
 * @since 1.8.5
 */
class Preview {

	/**
	 * List of preview fields.
	 *
	 * @since 1.8.5
	 *
	 * @var array
	 */
	private $fields = [];

	/**
	 * Current email template.
	 *
	 * @since 1.8.5
	 *
	 * @var string
	 */
	private $current_template;

	/**
	 * Field template.
	 *
	 * @since 1.8.5
	 *
	 * @var string
	 */
	private $field_template;

	/**
	 * Content is plain text type.
	 *
	 * @since 1.8.5
	 *
	 * @var bool
	 */
	private $plain_text;

	/**
	 * Preview nonce name.
	 *
	 * @since 1.8.5
	 *
	 * @var string
	 */
	const PREVIEW_NONCE_NAME = 'wpforms_email_preview';

	/**
	 * XOR key.
	 *
	 * The encryption key is a critical element in encryption algorithms,
	 * playing a crucial role in XOR encryption as employed in the WPFormsXOR plugin class.
	 * This key serves to govern the transformation of data during both encryption and decryption processes.
	 *
	 * The default and placeholder value for the key, as defined in the plugin class, is set to 42.
	 * If you wish to employ a different key (any numerical value is acceptable), you must provide
	 * that specific number to the plugin instance. It's essential to use the exact same key for
	 * both encrypting and decrypting data in the PHP environment as well.
	 *
	 * @since 1.8.6
	 *
	 * @var int
	 */
	const XOR_KEY = 42;

	/**
	 * Initialize class.
	 *
	 * @since 1.8.5
	 */
	public function init() {

		// Leave if user can't access.
		if ( ! wpforms_current_user_can() ) {
			return;
		}

		// Leave early if nonce verification failed.
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), self::PREVIEW_NONCE_NAME ) ) {
			return;
		}

		// Leave early if preview is not requested.
		if ( ! isset( $_GET['wpforms_email_preview'], $_GET['wpforms_email_template'] ) ) {
			return;
		}

		$this->current_template = sanitize_key( $_GET['wpforms_email_template'] );
		$this->plain_text       = $this->current_template === 'none';

		$this->hooks();
		$this->preview();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.8.6
	 */
	private function hooks() {

		add_filter( 'wpforms_emails_templates_notifications_get_header_image', [ $this, 'edit_current_template_header_image' ] );
		add_filter( 'wpforms_emails_helpers_style_overrides_args', [ $this, 'edit_current_template_style_overrides' ] );
	}

	/**
	 * This filter is used to override the current email template header image.
	 *
	 * This is needed to make sure the preview link is able to reflect the
	 * changes made in the email template style settings without saving the settings page.
	 *
	 * @since 1.8.6
	 *
	 * @param array $header_image The current email template header image.
	 *
	 * @return array
	 */
	public function edit_current_template_header_image( $header_image ) {

		// Get style overrides.
		$overrides = $this->get_style_overrides();

		// Leave early if no overrides are passed for the preview.
		if ( empty( $header_image ) || empty( $overrides ) ) {
			return $header_image;
		}

		// Check for the presence of light mode header image in the query string.
		if ( isset( $overrides['email_header_image'] ) ) {
			$header_image['url_light'] = esc_url_raw( $overrides['email_header_image'] );

			// Check for the presence of light mode header image size in the query string.
			if ( ! empty( $overrides['email_header_image_size'] ) ) {
				$header_image['size_light'] = sanitize_text_field( $overrides['email_header_image_size'] );
			}
		}

		// Check for the presence of dark mode header image in the query string.
		if ( isset( $overrides['email_header_image_dark'] ) ) {
			$header_image['url_dark'] = esc_url_raw( $overrides['email_header_image_dark'] );

			if ( ! empty( $overrides['email_header_image_size_dark'] ) ) {
				$header_image['size_dark'] = sanitize_text_field( $overrides['email_header_image_size_dark'] );
			}
		}

		return $header_image;
	}

	/**
	 * This filter is used to override the current email template style overrides.
	 *
	 * This is needed to make sure the preview link is able to reflect the
	 * changes made in the email template style settings without saving the settings page.
	 *
	 * @since 1.8.6
	 *
	 * @param array $styles The current email template styles.
	 *
	 * @return array
	 */
	public function edit_current_template_style_overrides( $styles ) {

		// Get style overrides.
		$overrides = $this->get_style_overrides();

		// Leave early if no overrides are passed for the preview.
		if ( empty( $overrides ) ) {
			return $styles;
		}

		// Check for the presence of light mode background color in the query string.
		if ( ! empty( $overrides['email_background_color'] ) ) {
			$styles['email_background_color'] = sanitize_hex_color( $overrides['email_background_color'] );
		}

		// Check for the presence of dark mode background color in the query string.
		if ( ! empty( $overrides['email_background_color_dark'] ) ) {
			$styles['email_background_color_dark'] = sanitize_hex_color( $overrides['email_background_color_dark'] );
		}

		// Leave early if the user has the Lite version.
		if ( ! wpforms()->is_pro() ) {
			// The only allowed override for the Lite version is the header image size.
			// This is needed to make sure the preview link is able to reflect the
			// changes made in the email template style settings without saving the settings page.
			if ( empty( $overrides['email_header_image_size'] ) ) {
				// Return the styles if no header image size override is passed for the preview.
				return $styles;
			}

			// Override and process the header image size.
			$overrides = [ 'email_header_image_size' => $overrides['email_header_image_size'] ];

			return $this->process_allowed_overrides( $styles, $overrides );
		}

		// Process allowed overrides using a separate function.
		return $this->process_allowed_overrides( $styles, $overrides );
	}

	/**
	 * Get style overrides.
	 *
	 * @since 1.8.6
	 *
	 * @return array
	 */
	private function get_style_overrides() {

		// phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		// Check if the 'wpforms_email_style_overrides' parameter is empty.
		if ( empty( $_GET['wpforms_email_style_overrides'] ) ) {
			return [];
		}

		// Retrieve and unslash the encoded style overrides from the query string.
		$style_overrides = wp_unslash( $_GET['wpforms_email_style_overrides'] );
		// phpcs:enable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		$overrides     = '';
		$overrides_len = strlen( $style_overrides );

		// Decode the overrides.
		// This is needed because the overrides are encoded before being passed in the query string.
		for ( $i = 0; $i < $overrides_len; $i++ ) {
			$overrides .= chr( ord( $style_overrides[ $i ] ) ^ self::XOR_KEY );
		}

		// Return the decoded overrides as an associative array.
		return json_decode( $overrides, true );
	}

	/**
	 * Process allowed style overrides.
	 *
	 * @since 1.8.6
	 *
	 * @param array $styles    Current styles.
	 * @param array $overrides Style overrides.
	 *
	 * @return array Updated styles.
	 */
	private function process_allowed_overrides( $styles, $overrides ) {

		// Leave early if no overrides are passed for the preview.
		if ( empty( $overrides ) ) {
			return $styles;
		}

		// Define an array of allowed query parameters.
		$allowed_overrides = [
			'email_body_color',
			'email_text_color',
			'email_links_color',
			'email_typography',
			'email_header_image_size',
			'email_body_color_dark',
			'email_text_color_dark',
			'email_links_color_dark',
			'email_typography_dark',
			'email_header_image_size_dark',
		];

		// Loop through allowed parameters and update $overrides if present in the query string.
		foreach ( $allowed_overrides as $param ) {
			// Leave early if the parameter is not present in the query string.
			if ( empty( $overrides[ $param ] ) ) {
				continue;
			}

			$styles = $this->process_override( $param, $styles, $overrides );
		}

		return $styles;
	}

	/**
	 * Process a specific style override.
	 *
	 * @since 1.8.6
	 *
	 * @param string $param     Style parameter.
	 * @param array  $styles    Current styles.
	 * @param array  $overrides Style overrides.
	 *
	 * @return array Updated styles.
	 */
	private function process_override( $param, $styles, $overrides ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		// Use a switch to handle specific cases.
		switch ( $param ) {
			case 'email_body_color':
			case 'email_text_color':
			case 'email_links_color':
			case 'email_body_color_dark':
			case 'email_text_color_dark':
			case 'email_links_color_dark':
				$styles[ $param ] = sanitize_hex_color( $overrides[ $param ] );
				break;

			case 'email_typography':
			case 'email_typography_dark':
				$styles[ $param ] = Helpers::get_template_typography( sanitize_text_field( $overrides[ $param ] ) );
				break;

			case 'email_header_image_size':
				$header_image_size                 = Helpers::get_template_header_image_size( sanitize_text_field( $overrides[ $param ] ) );
				$styles['header_image_max_width']  = $header_image_size['width'];
				$styles['header_image_max_height'] = $header_image_size['height'];
				break;

			case 'email_header_image_size_dark':
				$header_image_size_dark                 = Helpers::get_template_header_image_size( sanitize_text_field( $overrides[ $param ] ) );
				$styles['header_image_max_width_dark']  = $header_image_size_dark['width'];
				$styles['header_image_max_height_dark'] = $header_image_size_dark['height'];
				break;
		}

		return $styles;
	}

	/**
	 * Preview email template.
	 *
	 * @since 1.8.5
	 */
	private function preview() {

		$template = Notifications::get_available_templates( $this->current_template );

		/**
		 * Filter the email template to be previewed.
		 *
		 * @since 1.8.5
		 *
		 * @param array $template Email template.
		 */
		$template = (array) apply_filters( 'wpforms_emails_preview_template', $template );

		// Redirect to the email settings page if the template is not set.
		if ( ! isset( $template['path'] ) || ! class_exists( $template['path'] ) ) {
			wp_safe_redirect(
				add_query_arg(
					[
						'page' => 'wpforms-settings',
						'view' => 'email',
					],
					admin_url( 'admin.php' )
				)
			);
			exit;
		}

		// Set the email template, i.e. WPForms\Emails\Templates\Classic.
		$template = new $template['path']( '', true );

		// Set the field template.
		// This is used to replace the placeholders in the email template.
		$this->field_template = $template->get_field_template();

		// Set the email template fields.
		$template->set_field( $this->get_placeholder_message() );

		// Get the email template content.
		$content = $template->get();

		// Return if the template is empty.
		if ( ! $content ) {
			return;
		}

		// Echo the email template content.
		echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		exit; // No need to continue. WordPress will die() after this.
	}

	/**
	 * Get preview content.
	 *
	 * @since 1.8.5
	 *
	 * @return string Placeholder message.
	 */
	private function get_placeholder_message() {

		$this->fields = [
			[
				'type'  => 'name',
				'name'  => __( 'Name', 'wpforms-lite' ),
				'value' => 'Sullie Eloso',
			],
			[
				'type'  => 'email',
				'name'  => __( 'Email', 'wpforms-lite' ),
				'value' => 'sullie@wpforms.com',
			],
			[
				'type'  => 'textarea',
				'name'  => __( 'Comment or Message', 'wpforms-lite' ),
				'value' => "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Odio ut sem nulla pharetra diam sit amet. Sed risus pretium quam vulputate dignissim suspendisse in est ante. Risus ultricies tristique nulla aliquet enim tortor at auctor. Nisl tincidunt eget nullam non nisi est sit amet facilisis. Duis at tellus at urna condimentum mattis pellentesque id nibh. Curabitur vitae nunc sed velit dignissim.\r\n\r\nLeo urna molestie at elementum eu facilisis sed odio. Scelerisque mauris pellentesque pulvinar pellentesque habitant morbi. Volutpat maecenas volutpat blandit aliquam. Libero id faucibus nisl tincidunt. Et malesuada fames ac turpis egestas.",
			],
		];

		// Early return if the template is plain text.
		if ( $this->plain_text ) {
			return $this->process_plain_message();
		}

		return $this->process_html_message();
	}

	/**
	 * Process the HTML email message.
	 *
	 * @since 1.8.5
	 *
	 * @return string
	 */
	private function process_html_message() {

		$message = '';

		foreach ( $this->fields as $field ) {
			$message .= str_replace(
				[ '{field_type}', '{field_name}', '{field_value}', "\r\n" ],
				[ $field['type'], $field['name'], $field['value'], '<br>' ],
				$this->field_template
			);
		}

		return $message;
	}

	/**
	 * Process the plain text email message.
	 *
	 * @since 1.8.5
	 *
	 * @return string
	 */
	private function process_plain_message() {

		$message = '';

		foreach ( $this->fields as $field ) {
			$message .= '--- ' . $field['name'] . " ---\r\n\r\n" . str_replace( [ "\n", "\r" ], '', $field['value'] ) . "\r\n\r\n";
		}

		return nl2br( $message );
	}
}
