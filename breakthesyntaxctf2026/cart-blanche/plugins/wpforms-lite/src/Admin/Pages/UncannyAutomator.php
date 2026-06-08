<?php

namespace WPForms\Admin\Pages;

use Uncanny_Automator\Automator_Load;
use Uncanny_Automator_Pro\Automator_Pro_Load;

/**
 * Uncanny Automator Subpage.
 *
 * @since 1.9.8.6
 */
class UncannyAutomator extends Page {

	/**
	 * Admin menu page slug.
	 *
	 * @since 1.9.8.6
	 *
	 * @var string
	 */
	public const SLUG = 'wpforms-uncanny-automator';

	/**
	 * Configuration.
	 *
	 * @since 1.9.8.6
	 *
	 * @var array
	 */
	protected $config = [
		'lite_plugin'                  => 'uncanny-automator/uncanny-automator.php',
		'lite_wporg_url'               => 'https://wordpress.org/plugins/uncanny-automator/',
		'lite_download_url'            => 'https://downloads.wordpress.org/plugin/uncanny-automator.zip',
		'pro_plugin'                   => 'uncanny-automator-pro/uncanny-automator-pro.php',
		'uncanny-automator_addon'      => 'uncanny-automator-pro/uncanny-automator-pro.php',
		'uncanny-automator_addon_page' => 'https://automatorplugin.com/?utm_source=wpformsplugin&utm_medium=link&utm_campaign=uncanny-automator-page',
		'uncanny-automator_onboarding' => 'post-new.php?post_type=uo-recipe',
	];

	/**
	 * Get the plugin name for use in IDs, CSS classes, and config keys.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Plugin name.
	 */
	protected static function get_plugin_name(): string {

		return 'uncanny-automator';
	}

	/**
	 * Get heading title text.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Heading title.
	 */
	protected function get_heading_title(): string {

		return esc_html__( 'Let Your Site Handle the Busywork.', 'wpforms-lite' );
	}

	/**
	 * Get heading alt text for logo.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Heading alt text.
	 */
	protected function get_heading_alt_text(): string {

		return esc_attr__( 'WPForms ♥ Uncanny Automator', 'wpforms-lite' );
	}

	/**
	 * Get heading description strings.
	 *
	 * @since 1.9.8.6
	 *
	 * @return array Array of description strings.
	 */
	protected function get_heading_strings(): array {

		return [
			esc_html__( 'Automate tasks, save time, and keep everything running smoothly. Uncanny Automator connects your favorite tools so your site works smarter. No code. No stress.', 'wpforms-lite' ),
		];
	}

	/**
	 * Get screenshot features list.
	 *
	 * @since 1.9.8.6
	 *
	 * @return array Array of feature strings.
	 */
	protected function get_screenshot_features(): array {

		return [
			'Connect 200+ plugins and apps automatically: social media, memberships, courses, WooCommerce, CRMs, team chat, and much more.',
			'Create users, assign access, and enroll in courses with no manual work.',
			'Build multi-step workflows with delays and conditional logic, no code required.',
			'Unlimited automations with no per-task fees.',
		];
	}

	/**
	 * Get screenshot alt text.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Alt text for screenshot image.
	 */
	protected function get_screenshot_alt_text(): string {

		return esc_attr__( 'Uncanny Automator screenshot', 'wpforms-lite' );
	}

	/**
	 * Generate and output step 'Result' section HTML.
	 *
	 * @since 1.9.8.6
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
			esc_html__( 'Save and Test Your Automation', 'wpforms-lite' ),
			esc_html__( 'Click Save Recipe, run a test, and watch your workflow run on its own, no code needed.', 'wpforms-lite' ),
			esc_attr( $step['button_class'] ),
			esc_url( $step['button_url'] ),
			esc_html( $step['button_text'] )
		);
	}

	/**
	 * Step 'Result' data.
	 *
	 * @since 1.9.8.6
	 *
	 * @return array Step data.
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
				$step['button_url']   = $this->config['uncanny-automator_addon_page'];
				$step['button_class'] = $this->output_data['plugin_setup'] ? 'button-primary' : 'grey disabled';
				break;

			case 'pro':
				$addon_installed      = array_key_exists( $this->config['uncanny-automator_addon'], $this->output_data['all_plugins'] );
				$step['button_text']  =
					$addon_installed
						? esc_html__( 'Uncanny Automator Pro Installed & Activated', 'wpforms-lite' )
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

		if ( isset( $this->output_data['plugin_activated'] ) ) {
			// Check if premium features are available.
			if ( defined( 'AUTOMATOR_PRO_PLUGIN_VERSION' ) || class_exists( Automator_Pro_Load::class ) ) {
				$plugin_license_level = 'pro';
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
	 * Get the heading for the setup step.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Setup step heading.
	 */
	protected function get_setup_heading(): string {

		return esc_html__( 'Create Your First Automation (Recipe)', 'wpforms-lite' );
	}

	/**
	 * Get the description for the setup step.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Setup step description.
	 */
	protected function get_setup_description(): string {

		return esc_html__( 'Open the Automator menu, click Add New, choose your trigger (e.g. form submission), and define your action (e.g. send email, update CRM).', 'wpforms-lite' );
	}

	/**
	 * Whether a plugin is configured or not.
	 *
	 * @since 1.9.8.6
	 *
	 * @return bool True if plugin is configured properly.
	 */
	protected function is_plugin_configured(): bool {

		if ( ! $this->is_plugin_activated() ) {
			return false;
		}

		// Check if Uncanny Automator has been configured with basic settings.
		// The plugin is considered configured if there are recipes created.
		global $wpdb;

		// Check for Uncanny Automator posts (recipes).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$recipes = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status != %s",
				'uo-recipe',
				'trash'
			)
		);

		if ( (int) $recipes > 0 ) {
			return true;
		}

		// Check for basic Automator settings.
		$automator_settings = get_option( 'uap_automator_settings' );

		return ! empty( $automator_settings );
	}

	/**
	 * Whether a plugin is active or not.
	 *
	 * @since 1.9.8.6
	 *
	 * @return bool True if the plugin is active.
	 */
	protected function is_plugin_activated(): bool {

		return (
			( defined( 'AUTOMATOR_PLUGIN_VERSION' ) || class_exists( Automator_Load::class ) ) &&
			(
				is_plugin_active( $this->config['lite_plugin'] ) ||
				is_plugin_active( $this->config['pro_plugin'] )
			)
		);
	}

	/**
	 * Whether a plugin is available (class/function exists).
	 *
	 * @since 1.9.8.6
	 *
	 * @return bool True if a plugin is available.
	 */
	protected function is_plugin_available(): bool {

		return function_exists( 'Automator' ) || defined( 'AUTOMATOR_VERSION' );
	}

	/**
	 * Whether a pro-version is active.
	 *
	 * @since 1.9.8.6
	 *
	 * @return bool True if a pro-version is active.
	 */
	protected function is_pro_active(): bool {

		return class_exists( 'Uncanny_Automator_Pro\Plugin' ) || defined( 'AUTOMATOR_PRO_VERSION' );
	}

	/**
	 * Get the heading for the installation step.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Install step heading.
	 */
	protected function get_install_heading(): string {

		return esc_html__( 'Install and Activate Uncanny Automator', 'wpforms-lite' );
	}

	/**
	 * Get the description for the installation step.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Install step description.
	 */
	protected function get_install_description(): string {

		return esc_html__( 'Connect Automator and start building automations that save hours every week.', 'wpforms-lite' );
	}

	/**
	 * Get the plugin title.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Plugin title.
	 */
	protected function get_plugin_title(): string {

		return esc_html__( 'Uncanny Automator', 'wpforms-lite' );
	}

	/**
	 * Get the installation button text.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Install button text.
	 */
	protected function get_install_button_text(): string {

		return esc_html__( 'Install Uncanny Automator', 'wpforms-lite' );
	}

	/**
	 * Get the text when a plugin is installed and activated.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Installed & activated text.
	 */
	protected function get_installed_activated_text(): string {

		return esc_html__( 'Uncanny Automator Installed & Activated', 'wpforms-lite' );
	}

	/**
	 * Get the activate button text.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Activate button text.
	 */
	protected function get_activate_text(): string {

		return esc_html__( 'Activate Uncanny Automator', 'wpforms-lite' );
	}

	/**
	 * Get the setup button text.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Setup button text.
	 */
	protected function get_setup_button_text(): string {

		return esc_html__( 'Create Your First Recipe', 'wpforms-lite' );
	}

	/**
	 * Get the text when setup is completed.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Setup completed text.
	 */
	protected function get_setup_completed_text(): string {

		return esc_html__( 'Recipe Created', 'wpforms-lite' );
	}

	/**
	 * Get the text when a pro-version is installed and activated.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Pro installed and activated text.
	 */
	protected function get_pro_installed_activated_text(): string {

		return esc_html__( 'Uncanny Automator Pro Installed & Activated', 'wpforms-lite' );
	}

	/**
	 * Set the source of the plugin installation.
	 *
	 * @since 1.9.8.6
	 *
	 * @param string $plugin_basename The basename of the plugin.
	 */
	public function plugin_activated( string $plugin_basename ): void {

		if ( $plugin_basename !== $this->config['lite_plugin'] ) {
			return;
		}

		$source = wpforms()->is_pro() ? 'WPForms' : 'WPForms Lite';

		/**
		 * Rewrite the get_plugin_name() default value.
		 *
		 * Use `uncannyautomator` instead of `uncanny-automator`.
		 * This is necessary for maintaining consistency with the integration and the plugin itself.
		 *
		 * See: src/Integrations/UncannyAutomator/UncannyAutomator.php update_source() method.
		 */
		update_option( 'uncannyautomator_source', $source, false );
		update_option( 'uncannyautomator_date', time(), false );
	}
}
