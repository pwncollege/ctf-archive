<?php

namespace WPForms\Admin\Pages;

/**
 * Privacy Compliance Subpage.
 *
 * @since 1.9.7.3
 */
class PrivacyCompliance extends Page {

	/**
	 * Admin menu page slug.
	 *
	 * @since 1.9.7.3
	 *
	 * @var string
	 */
	public const SLUG = 'wpforms-wpconsent';

	/**
	 * Configuration.
	 *
	 * @since 1.9.7.3
	 *
	 * @var array
	 */
	protected $config = [
		'lite_plugin'          => 'wpconsent-cookies-banner-privacy-suite/wpconsent.php',
		'lite_wporg_url'       => 'https://wordpress.org/plugins/wpconsent-cookies-banner-privacy-suite/',
		'lite_download_url'    => 'https://downloads.wordpress.org/plugin/wpconsent-cookies-banner-privacy-suite.zip',
		'pro_plugin'           => 'wpconsent-premium/wpconsent-premium.php',
		'wpconsent_addon'      => 'wpconsent-premium/wpconsent-premium.php',
		'wpconsent_addon_page' => 'https://wpconsent.com/?utm_source=wpformsplugin&utm_medium=link&utm_campaign=privacy-compliance-page',
		'wpconsent_onboarding' => 'admin.php?page=wpconsent-onboarding',
	];

	/**
	 * Get the plugin name for use in IDs, CSS classes, and config keys.
	 *
	 * @since 1.9.7.3
	 *
	 * @return string Plugin name.
	 */
	protected static function get_plugin_name(): string {

		return 'wpconsent';
	}

	/**
	 * Hooks.
	 *
	 * @since 1.9.7.3
	 */
	public function hooks(): void {

		if ( wp_doing_ajax() ) {
			remove_action( 'admin_init', 'wpconsent_maybe_redirect_onboarding', 9999 );
		}

		parent::hooks();
	}

	/**
	 * Get heading image URL.
	 *
	 * @since 1.9.7.3
	 *
	 * @return string Heading image URL.
	 */
	protected function get_heading_image_url(): string {

		return WPFORMS_PLUGIN_URL . 'assets/images/wpconsent/wpforms-wpconsent.svg';
	}

	/**
	 * Get heading title text.
	 *
	 * @since 1.9.7.3
	 *
	 * @return string Heading title.
	 */
	protected function get_heading_title(): string {

		return esc_html__( 'Make Your Website Privacy-Compliant in Minutes', 'wpforms-lite' );
	}

	/**
	 * Get heading alt text for logo.
	 *
	 * @since 1.9.7.3
	 *
	 * @return string Heading alt text.
	 */
	protected function get_heading_alt_text(): string {

		return esc_attr__( 'WPForms ♥ WPConsent', 'wpforms-lite' );
	}

	/**
	 * Get heading description strings.
	 *
	 * @since 1.9.7.3
	 *
	 * @return array Array of description strings.
	 */
	protected function get_heading_strings(): array {

		return [
			esc_html__( 'Build trust with clear, compliant privacy practices. WPConsent adds clean, professional banners and handles the technical side for you.', 'wpforms-lite' ),
			esc_html__( 'Built for transparency. Designed for ease.', 'wpforms-lite' ),
		];
	}

	/**
	 * Get screenshot features list.
	 *
	 * @since 1.9.7.3
	 *
	 * @return array Array of feature strings.
	 */
	protected function get_screenshot_features(): array {

		return [
			esc_html__( 'A professional banner that fits your site.', 'wpforms-lite' ),
			esc_html__( 'Tools like Google Analytics and Facebook Pixel paused until consent.', 'wpforms-lite' ),
			esc_html__( 'Peace of mind knowing you’re aligned with global laws.', 'wpforms-lite' ),
			esc_html__( 'Self-hosted. Your data remains on your site.', 'wpforms-lite' ),
		];
	}

	/**
	 * Get screenshot alt text.
	 *
	 * @since 1.9.7.3
	 *
	 * @return string Alt text for screenshot image.
	 */
	protected function get_screenshot_alt_text(): string {

		return esc_attr__( 'WPConsent screenshot', 'wpforms-lite' );
	}

	/**
	 * Generate and output step 'Result' section HTML.
	 *
	 * @since 1.9.7.3
	 *
	 * @noinspection HtmlUnknownTarget
	 */
	protected function output_section_step_result(): void {

		$step = $this->get_data_step_result();

		if ( empty( $step ) ) {
			return;
		}

		printf(
			'<section class="step step-result %1$s">
				<aside class="num">
					<img src="%2$s" alt="%3$s" />
					<i class="loader hidden"></i>
				</aside>
				<div>
					<h2>%4$s</h2>
					<p>%5$s</p>
					<button class="button %6$s" data-url="%7$s">%8$s</button>
				</div>
			</section>',
			esc_attr( $step['section_class'] ),
			esc_url( WPFORMS_PLUGIN_URL . 'assets/images/' . $step['icon'] ),
			esc_attr__( 'Step 3', 'wpforms-lite' ),
			esc_html__( 'Get Advanced Cookie Consent Features', 'wpforms-lite' ),
			esc_html__( 'With WPConsent Pro you can access advanced features like geolocation, popup layout, records of consent, multilanguage support, and more.', 'wpforms-lite' ),
			esc_attr( $step['button_class'] ),
			esc_url( $step['button_url'] ),
			esc_html( $step['button_text'] )
		);
	}

	/**
	 * Step 'Result' data.
	 *
	 * @since 1.9.7.3
	 *
	 * @return array Step data.
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	protected function get_data_step_result(): array { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		$step = [];

		$step['icon']          = 'step-3.svg';
		$step['section_class'] = $this->output_data['plugin_setup'] ? '' : 'grey';
		$step['button_text']   = esc_html__( 'Learn More', 'wpforms-lite' );
		$step['button_class']  = 'grey disabled';
		$step['button_url']    = '';

		$plugin_license_level = $this->get_license_level();

		switch ( $plugin_license_level ) {
			case 'lite':
				$step['button_url']   = $this->config['wpconsent_addon_page'];
				$step['button_class'] = $this->output_data['plugin_setup'] ? 'button-primary' : 'grey disabled';
				break;

			case 'pro':
				$addon_installed      = array_key_exists( $this->config['wpconsent_addon'], $this->output_data['all_plugins'] );
				$step['button_text']  =
					$addon_installed
						? esc_html__( 'WPConsent Pro Installed & Activated', 'wpforms-lite' )
						: esc_html__( 'Install Now', 'wpforms-lite' );
				$step['button_class'] = $this->output_data['plugin_setup'] ? 'grey disabled' : 'button-primary';
				$step['icon']         = $addon_installed ? 'step-complete.svg' : 'step-3.svg';
				break;
		}

		return $step;
	}

	/**
	 * Retrieve the license level of the plugin.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string The plugin license level ('lite' or 'pro').
	 */
	protected function get_license_level(): string {

		$plugin_license_level = 'lite';

		// Check if premium features are available.
		if ( function_exists( 'wpconsent' ) ) {
			$wpconsent = wpconsent();

			if ( isset( $wpconsent->license ) && method_exists( $wpconsent->license, 'is_active' ) ) {
				$plugin_license_level = $wpconsent->license->is_active() ? 'pro' : 'lite';
			}
		}

		return $plugin_license_level;
	}

	/**
	 * Whether the plugin is finished setup or not.
	 *
	 * @since 1.9.8.6
	 */
	protected function is_plugin_finished_setup(): bool {

		if ( ! $this->is_plugin_configured() ) {
			return false;
		}

		return $this->get_license_level() === 'pro';
	}

	/**
	 * Set the source of the plugin installation.
	 *
	 * @since 1.9.8
	 * @deprecated 1.9.8.6
	 *
	 * @param string $plugin_basename The basename of the plugin.
	 */
	public function privacy_compliance_activated( string $plugin_basename ): void {

		$this->plugin_activated( $plugin_basename );
	}

	/**
	 * Whether a plugin is configured or not.
	 *
	 * @since 1.9.7.3
	 *
	 * @return bool True if plugin is configured properly.
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	protected function is_plugin_configured(): bool {

		if ( ! $this->is_plugin_activated() ) {
			return false;
		}

		// Check if WPConsent has been configured with basic settings.
		// The plugin is considered configured if the consent banner is enabled.
		if ( function_exists( 'wpconsent' ) ) {
			$wpconsent = wpconsent();

			if ( isset( $wpconsent->settings ) ) {
				$enable_consent_banner = $wpconsent->settings->get_option( 'enable_consent_banner', 0 );

				return ! empty( $enable_consent_banner );
			}
		}

		return false;
	}

	/**
	 * Whether a plugin is active or not.
	 *
	 * @since 1.9.7.3
	 *
	 * @return bool True if plugin is active.
	 */
	protected function is_plugin_activated(): bool {

		return (
			function_exists( 'wpconsent' ) &&
			(
				is_plugin_active( $this->config['lite_plugin'] ) ||
				is_plugin_active( $this->config['pro_plugin'] )
			)
		);
	}

	/**
	 * Whether a plugin is available (class/function exists).
	 *
	 * @since 1.9.7.3
	 *
	 * @return bool True if plugin is available.
	 */
	protected function is_plugin_available(): bool {

		return function_exists( 'wpconsent' );
	}

	/**
	 * Whether pro version is active.
	 *
	 * @since 1.9.7.3
	 *
	 * @return bool True if pro version is active.
	 * @noinspection PhpUndefinedFunctionInspection
	 */
	protected function is_pro_active(): bool {

		if ( ! function_exists( 'wpconsent' ) ) {
			return false;
		}

		$wpconsent = wpconsent();

		return isset( $wpconsent->license ) && method_exists( $wpconsent->license, 'is_active' ) && $wpconsent->license->is_active();
	}

	/**
	 * Get the heading for the install step.
	 *
	 * @since 1.9.7.3
	 *
	 * @return string Install step heading.
	 */
	protected function get_install_heading(): string {

		return esc_html__( 'Install & Activate WPConsent', 'wpforms-lite' );
	}

	/**
	 * Get the description for the install step.
	 *
	 * @since 1.9.7.3
	 *
	 * @return string Install step description.
	 */
	protected function get_install_description(): string {

		return esc_html__( 'Install WPConsent from the WordPress.org plugin repository.', 'wpforms-lite' );
	}

	/**
	 * Get the plugin title.
	 *
	 * @since 1.9.7.3
	 *
	 * @return string Plugin title.
	 */
	protected function get_plugin_title(): string {

		return esc_html__( 'WPConsent', 'wpforms-lite' );
	}

	/**
	 * Get the install button text.
	 *
	 * @since 1.9.7.3
	 *
	 * @return string Install button text.
	 */
	protected function get_install_button_text(): string {

		return esc_html__( 'Install WPConsent', 'wpforms-lite' );
	}

	/**
	 * Get the text when a plugin is installed and activated.
	 *
	 * @since 1.9.7.3
	 *
	 * @return string Installed & activated text.
	 */
	protected function get_installed_activated_text(): string {

		return esc_html__( 'WPConsent Installed & Activated', 'wpforms-lite' );
	}

	/**
	 * Get the activate button text.
	 *
	 * @since 1.9.7.3
	 *
	 * @return string Activate button text.
	 */
	protected function get_activate_text(): string {

		return esc_html__( 'Activate WPConsent', 'wpforms-lite' );
	}

	/**
	 * Get the heading for the setup step.
	 *
	 * @since 1.9.7.3
	 *
	 * @return string Setup step heading.
	 */
	protected function get_setup_heading(): string {

		return esc_html__( 'Set Up WPConsent', 'wpforms-lite' );
	}

	/**
	 * Get the description for the setup step.
	 *
	 * @since 1.9.7.3
	 *
	 * @return string Setup step description.
	 */
	protected function get_setup_description(): string {

		return esc_html__( 'WPConsent has an intuitive setup wizard to guide you through the cookie consent configuration process.', 'wpforms-lite' );
	}

	/**
	 * Get the setup button text.
	 *
	 * @since 1.9.7.3
	 *
	 * @return string Setup button text.
	 */
	protected function get_setup_button_text(): string {

		return esc_html__( 'Run Setup Wizard', 'wpforms-lite' );
	}

	/**
	 * Get the text when setup is completed.
	 *
	 * @since 1.9.7.3
	 *
	 * @return string Setup completed text.
	 */
	protected function get_setup_completed_text(): string {

		return esc_html__( 'Setup Complete', 'wpforms-lite' );
	}

	/**
	 * Get the text when a pro-version is installed and activated.
	 *
	 * @since 1.9.7.3
	 *
	 * @return string Pro installed and activated text.
	 */
	protected function get_pro_installed_activated_text(): string {

		return esc_html__( 'WPConsent Pro Installed & Activated', 'wpforms-lite' );
	}
}
