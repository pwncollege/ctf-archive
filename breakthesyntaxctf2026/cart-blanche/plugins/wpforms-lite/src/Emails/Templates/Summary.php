<?php

namespace WPForms\Emails\Templates;

/**
 * Email Summaries email template class.
 *
 * @since 1.5.4
 */
class Summary extends General {

	/**
	 * Template slug.
	 *
	 * @since 1.5.4
	 *
	 * @var string
	 */
	const TEMPLATE_SLUG = 'summary';

	/**
	 * Initialize class.
	 *
	 * @since 1.8.8
	 *
	 * @param string $message Optional. Message.
	 */
	public function __construct( $message = '' ) {

		parent::__construct( $message );

		// Maybe revert (override) the default background color value.
		$this->set_args( $this->maybe_revert_background_color() );
	}

	/**
	 * Get header image URL from settings.
	 *
	 * @since 1.5.4
	 *
	 * @return array
	 */
	protected function get_header_image() {

		$legacy_header_image = $this->maybe_revert_header_image();

		// Bail early, if legacy behavior is enabled.
		if ( ! empty( $legacy_header_image ) ) {
			return $legacy_header_image;
		}

		// Set specific WPForms logo width in pixels for MS Outlook and old email clients.
		return [
			'url_light' => WPFORMS_PLUGIN_URL . 'assets/images/logo.png',
			'url_dark'  => WPFORMS_PLUGIN_URL . 'assets/images/logo-negative.png',
		];
	}

	/**
	 * Checks if legacy header image overrides should be applied.
	 *
	 * @since 1.8.8
	 *
	 * @return array
	 */
	private function maybe_revert_header_image() {

		/**
		 * This filter is designed to restore the legacy behavior, reverting the WPForms logo and template background color
		 * to values defined in the WPForms → Settings → Email tab.
		 *
		 * @since 1.8.8
		 *
		 * @param bool $revert_legacy_style_overrides Whether to apply legacy style overrides.
		 */
		if ( ! (bool) apply_filters( 'wpforms_emails_templates_summary_revert_legacy_style_overrides', false ) ) {
			return [];
		}

		$header_image = wpforms_setting( 'email-header-image' );

		// Bail early, if no custom header image if set.
		if ( empty( $header_image ) ) {
			return [];
		}

		return [ 'url_light' => esc_url( $header_image ) ];
	}

	/**
	 * Checks if legacy background color overrides should be applied.
	 *
	 * @since 1.8.8
	 *
	 * @return array
	 */
	private function maybe_revert_background_color() {

		/**
		 * This filter is designed to restore the legacy behavior, reverting the WPForms logo and template background color
		 * to values defined in the WPForms → Settings → Email tab.
		 *
		 * @since 1.8.8
		 *
		 * @param bool $revert_legacy_style_overrides Whether to apply legacy style overrides.
		 */
		if ( ! (bool) apply_filters( 'wpforms_emails_templates_summary_revert_legacy_style_overrides', false ) ) {
			return [ 'style' => [ 'email_background_color' => '' ] ];
		}

		return [
			'style' => [
				'email_background_color' => wpforms_setting( 'email-background-color', '#e9eaec' ),
			],
		];
	}
}
