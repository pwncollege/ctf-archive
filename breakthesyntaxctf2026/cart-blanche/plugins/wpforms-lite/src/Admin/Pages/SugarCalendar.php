<?php

namespace WPForms\Admin\Pages;

/**
 * Sugar Calendar Subpage.
 *
 * @since 1.9.8.6
 */
class SugarCalendar extends Page {

	/**
	 * Admin menu page slug.
	 *
	 * @since 1.9.8.6
	 *
	 * @var string
	 */
	public const SLUG = 'wpforms-sugar-calendar';

	/**
	 * Configuration.
	 *
	 * @since 1.9.8.6
	 *
	 * @var array
	 */
	protected $config = [
		'lite_plugin'               => 'sugar-calendar-lite/sugar-calendar-lite.php',
		'lite_wporg_url'            => 'https://wordpress.org/plugins/sugar-calendar-lite/',
		'lite_download_url'         => 'https://downloads.wordpress.org/plugin/sugar-calendar-lite.zip',
		'pro_plugin'                => 'sugar-calendar/sugar-calendar.php',
		'sugar-calendar_addon'      => 'sugar-calendar/sugar-calendar.php',
		'sugar-calendar_addon_page' => 'https://sugarcalendar.com/?utm_source=wpformsplugin&utm_medium=link&utm_campaign=sugar-calendar-page',
		'sugar-calendar_onboarding' => 'post-new.php?post_type=sc_event',
	];

	/**
	 * Hooks.
	 *
	 * @since 1.9.8.6
	 */
	public function hooks(): void {

		if ( wp_doing_ajax() ) {
			add_filter( 'default_option_sugar_calendar_prevent_redirect', '__return_true' );
		}

		parent::hooks();
	}

	/**
	 * Get the plugin name for use in IDs, CSS classes, and config keys.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Plugin name.
	 */
	protected static function get_plugin_name(): string {

		return 'sugar-calendar';
	}

	/**
	 * Get heading title text.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Heading title.
	 */
	protected function get_heading_title(): string {

		return esc_html__( 'Taking Bookings? Put Them on a Calendar', 'wpforms-lite' );
	}

	/**
	 * Get heading alt text for logo.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Heading alt text.
	 */
	protected function get_heading_alt_text(): string {

		return esc_attr__( 'WPForms ♥ Sugar Calendar', 'wpforms-lite' );
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
			esc_html__( 'WPForms collects the "yes." Sugar Calendar shows the "when and where."', 'wpforms-lite' ),
			esc_html__( 'Together, they turn bookings into events your visitors can browse, sync, and show up for.', 'wpforms-lite' ),
			esc_html__( 'Simple, elegant, and built for your workflow.', 'wpforms-lite' ),
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
			esc_html__( 'Display events on beautiful calendars visitors can browse and filter.', 'wpforms-lite' ),
			esc_html__( 'Sell tickets with Stripe or WooCommerce integration.', 'wpforms-lite' ),
			esc_html__( 'Visitors can add events to Google, Apple, or Outlook calendars with one click.', 'wpforms-lite' ),
			esc_html__( 'Set up recurring events: daily, weekly, monthly, or custom patterns.', 'wpforms-lite' ),
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

		return esc_attr__( 'Sugar Calendar screenshot', 'wpforms-lite' );
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
			esc_html__( 'Display Events on Your Site', 'wpforms-lite' ),
			esc_html__( 'Use the Calendar block or shortcode [sc_events_calendar] to embed events anywhere on your site.', 'wpforms-lite' ),
			esc_attr( $step['button_class'] ),
			esc_url( $step['button_url'] ),
			esc_html( $step['button_text'] )
		);
	}

	/**
	 * Generate and output footer section HTML.
	 *
	 * @since 1.9.8.6
	 */
	protected function output_section_footer(): void {

		printf(
			'<section class="bottom">
				<p>%s</p>
			</section>',
			esc_html__( 'From the same team trusted by over 6 million sites.', 'wpforms-lite' )
		);
	}

	/**
	 * Step 'Result' data.
	 *
	 * @since 1.9.8.6
	 *
	 * @return array Step data.
	 */
	protected function get_data_step_result(): array {

		$step = $this->get_default_step_data();

		$plugin_license_level = $this->get_plugin_license_level();

		if ( $plugin_license_level === 'lite' ) {
			$this->apply_lite_step_data( $step );
		} elseif ( $plugin_license_level === 'pro' ) {
			$this->apply_pro_step_data( $step );
		}

		return $step;
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

		return $this->get_plugin_license_level() === 'pro';
	}

	/**
	 * Get default step data.
	 *
	 * @since 1.9.8.6
	 *
	 * @return array Default step data.
	 */
	private function get_default_step_data(): array {

		return [
			'icon'          => 'step-3.svg',
			'section_class' => $this->output_data['plugin_setup'] ? '' : 'grey',
			'button_text'   => esc_html__( 'Learn More', 'wpforms-lite' ),
			'button_class'  => 'grey disabled',
			'button_url'    => '',
		];
	}

	/**
	 * Get plugin license level.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string License level ('lite', 'pro') or false if not activated.
	 */
	private function get_plugin_license_level(): string {

		if ( ! function_exists( 'sugar_calendar' ) ) {
			return 'lite';
		}

		$sugar_calendar = sugar_calendar();

		return $sugar_calendar->__get( 'is_pro' ) ? 'pro' : 'lite';
	}

	/**
	 * Apply lite version step data.
	 *
	 * @since 1.9.8.6
	 *
	 * @param array $step Step data array (passed by reference).
	 */
	private function apply_lite_step_data( array &$step ): void {

		$step['button_url']   = $this->config['sugar-calendar_addon_page'];
		$step['button_class'] = $this->output_data['plugin_setup'] ? 'button-primary' : 'grey disabled';
	}

	/**
	 * Apply pro version step data.
	 *
	 * @since 1.9.8.6
	 *
	 * @param array $step Step data array (passed by reference).
	 */
	private function apply_pro_step_data( array &$step ): void {

		$addon_installed = array_key_exists( $this->config['sugar-calendar_addon'], $this->output_data['all_plugins'] );
		$configured      = $this->is_plugin_configured();

		$step['button_text']  = $addon_installed && $configured
			? esc_html__( 'Sugar Calendar Pro Installed & Activated', 'wpforms-lite' )
			: esc_html__( 'Install Now', 'wpforms-lite' );
		$step['button_class'] = $this->output_data['plugin_setup'] || ! $configured ? 'grey disabled' : 'button-primary';
		$step['icon']         = $addon_installed && $configured ? 'step-complete.svg' : 'step-3.svg';
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

		$events = get_posts(
			[
				'post_type'      => 'sc_event',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			]
		);

		return ! empty( $events );
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
			( function_exists( 'sugar_calendar' ) || class_exists( 'Sugar_Calendar\Plugin' ) ) &&
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
	 * @return bool True if plugin is available.
	 */
	protected function is_plugin_available(): bool {

		return class_exists( 'Sugar_Calendar\Plugin' ) || function_exists( 'sugar_calendar' );
	}

	/**
	 * Whether pro version is active.
	 *
	 * @since 1.9.8.6
	 *
	 * @return bool True if pro version is active.
	 */
	protected function is_pro_active(): bool {

		if ( ! function_exists( 'sugar_calendar' ) ) {
			return false;
		}

		return sugar_calendar()->is_pro();
	}

	/**
	 * Get the heading for the install step.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Install step heading.
	 */
	protected function get_install_heading(): string {

		return esc_html__( 'Install and Activate Sugar Calendar', 'wpforms-lite' );
	}

	/**
	 * Get the description for the install step.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Install step description.
	 */
	protected function get_install_description(): string {

		return esc_html__( 'Bring your forms to life. Install Sugar Calendar and start creating events.', 'wpforms-lite' );
	}

	/**
	 * Get the plugin title.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Plugin title.
	 */
	protected function get_plugin_title(): string {

		return esc_html__( 'Sugar Calendar', 'wpforms-lite' );
	}

	/**
	 * Get the install button text.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Install button text.
	 */
	protected function get_install_button_text(): string {

		return esc_html__( 'Install Sugar Calendar', 'wpforms-lite' );
	}

	/**
	 * Get the text when a plugin is installed and activated.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Installed & activated text.
	 */
	protected function get_installed_activated_text(): string {

		return esc_html__( 'Sugar Calendar Installed & Activated', 'wpforms-lite' );
	}

	/**
	 * Get the activate button text.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Activate button text.
	 */
	protected function get_activate_text(): string {

		return esc_html__( 'Activate Sugar Calendar', 'wpforms-lite' );
	}

	/**
	 * Get the heading for the setup step.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Setup step heading.
	 */
	protected function get_setup_heading(): string {

		return esc_html__( 'Create Your First Event', 'wpforms-lite' );
	}

	/**
	 * Get the description for the setup step.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Setup step description.
	 */
	protected function get_setup_description(): string {

		return esc_html__( 'Add your first booking or class to your calendar in seconds. Clean, simple, and built right into WordPress.', 'wpforms-lite' );
	}

	/**
	 * Get the setup button text.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Setup button text.
	 */
	protected function get_setup_button_text(): string {

		return esc_html__( 'Add First Event', 'wpforms-lite' );
	}

	/**
	 * Get the text when setup is completed.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Setup completed text.
	 */
	protected function get_setup_completed_text(): string {

		return esc_html__( 'Event Created', 'wpforms-lite' );
	}

	/**
	 * Get the text when a pro-version is installed and activated.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Pro installed and activated text.
	 */
	protected function get_pro_installed_activated_text(): string {

		return esc_html__( 'Sugar Calendar Pro Installed & Activated', 'wpforms-lite' );
	}
}
