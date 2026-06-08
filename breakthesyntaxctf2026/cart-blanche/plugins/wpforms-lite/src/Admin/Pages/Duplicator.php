<?php

namespace WPForms\Admin\Pages;

/**
 * Duplicator Subpage.
 *
 * @since 1.9.8.6
 */
class Duplicator extends Page {

	/**
	 * Admin menu page slug.
	 *
	 * @since 1.9.8.6
	 *
	 * @var string
	 */
	public const SLUG = 'wpforms-duplicator';

	/**
	 * Configuration.
	 *
	 * @since 1.9.8.6
	 *
	 * @var array
	 */
	protected $config = [
		'lite_plugin'           => 'duplicator/duplicator.php',
		'lite_wporg_url'        => 'https://wordpress.org/plugins/duplicator/',
		'lite_download_url'     => 'https://downloads.wordpress.org/plugin/duplicator.zip',
		'pro_plugin'            => 'duplicator-pro/duplicator-pro.php',
		'duplicator_addon'      => 'duplicator-pro/duplicator-pro.php',
		'duplicator_addon_page' => 'https://duplicator.com/?utm_source=wpformsplugin&utm_medium=link&utm_campaign=duplicator-page',
		'duplicator_onboarding' => 'admin.php?page=duplicator',
	];

	/**
	 * Constructor.
	 *
	 * @since 1.9.8.6
	 */
	public function __construct() {

		// Set the correct onboarding page based on the active version.
		if ( $this->is_pro_active() ) {
			$this->config['duplicator_onboarding'] = 'admin.php?page=duplicator-pro';
		}

		parent::__construct();
	}

	/**
	 * Get the plugin name for use in IDs, CSS classes, and config keys.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Plugin name.
	 */
	protected static function get_plugin_name(): string {

		return 'duplicator';
	}

	/**
	 * Get heading title text.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Heading title.
	 */
	protected function get_heading_title(): string {

		return esc_html__( 'WPForms Collects It. Duplicator Protects It.', 'wpforms-lite' );
	}

	/**
	 * Get heading alt text for logo.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Heading alt text.
	 */
	protected function get_heading_alt_text(): string {

		return esc_attr__( 'WPForms ♥ Duplicator', 'wpforms-lite' );
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
			esc_html__( 'Every form entry lives in your database. One bad update, one crash, and it\'s gone. Duplicator backs up your entire site automatically so you can restore everything with one click.', 'wpforms-lite' ),
			esc_html__( 'Trusted by over 1.5 million websites.', 'wpforms-lite' ),
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
			'Back up your entire site automatically: forms, entries, everything.',
			'Restore your site with one click if anything goes wrong.',
			'Store backups safely in Google Drive, Dropbox, or Amazon S3.',
			'Schedule daily backups so you never have to think about it.',
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

		return esc_attr__( 'Duplicator screenshot', 'wpforms-lite' );
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
			esc_html__( 'Set Up Scheduled Cloud Backups', 'wpforms-lite' ),
			esc_html__( 'Keep your data safe forever with automatic daily backups to Google Drive, Dropbox, or Amazon S3.', 'wpforms-lite' ),
			esc_attr( $step['button_class'] ),
			esc_url( admin_url( $this->is_pro_active() ? 'admin.php?page=duplicator-pro-schedules' : 'admin.php?page=duplicator-schedules' ) ),
			esc_html( $step['button_text'] )
		);
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

		$count          = $this->get_package_count();
		$schedule_count = 0;

		if ( $count && class_exists( '\Duplicator\Models\ScheduleEntity' ) && $this->is_pro_active() ) {
			$schedule_count = \Duplicator\Models\ScheduleEntity::count(); // phpcs:ignore WPForms.PHP.BackSlash.RemoveBackslash, WPForms.PHP.BackSlash.UseShortSyntax
		}

		return $count && $schedule_count;
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
			esc_html__( 'Because the data you collect with WPForms is too valuable to lose.', 'wpforms-lite' )
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

		$count = $this->get_package_count();

		$data = [
			'section_class' => $count ? '' : 'grey',
			'button_class'  => ! $count ? 'grey disabled' : 'button-primary',
			'icon'          => 'step-3.svg',
			'button_text'   => esc_html__( 'Set Up Cloud Backups', 'wpforms-lite' ),
		];

		if ( $count && class_exists( '\Duplicator\Models\ScheduleEntity' ) && $this->is_pro_active() ) {
			$schedule_count = \Duplicator\Models\ScheduleEntity::count(); // phpcs:ignore WPForms.PHP.BackSlash.RemoveBackslash, WPForms.PHP.BackSlash.UseShortSyntax

			$data['section_class'] = '';
			$data['button_class']  = 'button-primary';

			if ( $schedule_count ) {
				$data['icon']         = 'step-complete.svg';
				$data['button_class'] = 'grey disabled';
				$data['button_text']  = esc_html__( 'Cloud Backups Set Up', 'wpforms-lite' );
			}
		}

		return $data;
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

		$count = $this->get_package_count();

		return $count > 0;
	}

	/**
	 * Get the number of packages in the database.
	 *
	 * @since 1.9.8.6
	 *
	 * @return int Number of packages.
	 */
	protected function get_package_count(): int {

		/**
		 * Check if the plugin is available.
		 * Since we are using a direct query to the database to get the number of records instead of built-in methods,
		 * there is a chance of getting a non-zero value even if the plugin is turned off.
		 */
		if ( ! $this->is_plugin_available() ) {
			return 0;
		}

		// Check if Duplicator has been configured with basic settings.
		global $wpdb;

		// Check for the Duplicator packages table.
		$packages_table = $this->is_pro_active() ? $wpdb->prefix . 'duplicator_backups' : $wpdb->prefix . 'duplicator_packages';

		// Use object caching to minimize direct DB queries here, as there is no core API
		// to check custom plugin table existence or its contents.
		$blog_id                 = function_exists( 'get_current_blog_id' ) ? get_current_blog_id() : 0;
		$table_exists_cache_key  = "wpforms_dup_table_exists_{$blog_id}";
		$package_count_cache_key = "wpforms_dup_package_count_{$blog_id}";

		$table_exists = wp_cache_get( $table_exists_cache_key, 'wpforms' );

		if ( $table_exists === false ) {
			// PHPCS: We must use a direct DB query because no WP API exists for custom tables.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $packages_table ) ) );

			wp_cache_set( $table_exists_cache_key, $table_exists, 'wpforms', 60 );
		}

		$package_count = 0;

		if ( $table_exists === $packages_table ) {
			$package_count = wp_cache_get( $package_count_cache_key, 'wpforms' );

			if ( $package_count === false ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$package_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$packages_table}" );

				wp_cache_set( $package_count_cache_key, $package_count, 'wpforms', 60 );
			}
		}

		return (int) $package_count;
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
			( defined( 'DUPLICATOR_VERSION' ) || class_exists( 'Duplicator\Plugin' ) || class_exists( 'Duplicator\Pro\Requirements' ) ) &&
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

		return class_exists( 'Duplicator\Plugin' ) || defined( 'DUPLICATOR_VERSION' ) || class_exists( 'DUP_PRO_Plugin' ) || defined( 'DUPLICATOR_PRO_VERSION' );
	}

	/**
	 * Whether pro version is active.
	 *
	 * @since 1.9.8.6
	 *
	 * @return bool True if pro version is active.
	 */
	protected function is_pro_active(): bool {

		return class_exists( 'DUP_PRO_Plugin' ) || defined( 'DUPLICATOR_PRO_VERSION' );
	}

	/**
	 * Get the heading for the install step.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Install step heading.
	 */
	protected function get_install_heading(): string {

		return esc_html__( 'Install and Activate Duplicator', 'wpforms-lite' );
	}

	/**
	 * Get the description for the install step.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Install step description.
	 */
	protected function get_install_description(): string {

		return esc_html__( 'Your first step toward bulletproof backups.', 'wpforms-lite' );
	}

	/**
	 * Get the plugin title.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Plugin title.
	 */
	protected function get_plugin_title(): string {

		return esc_html__( 'Duplicator', 'wpforms-lite' );
	}

	/**
	 * Get the install button text.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Install button text.
	 */
	protected function get_install_button_text(): string {

		return esc_html__( 'Install Duplicator', 'wpforms-lite' );
	}

	/**
	 * Get the text when a plugin is installed and activated.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Installed & activated text.
	 */
	protected function get_installed_activated_text(): string {

		return esc_html__( 'Duplicator Installed & Activated', 'wpforms-lite' );
	}

	/**
	 * Get the activate button text.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Activate button text.
	 */
	protected function get_activate_text(): string {

		return esc_html__( 'Activate Duplicator', 'wpforms-lite' );
	}

	/**
	 * Get the heading for the setup step.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Setup step heading.
	 */
	protected function get_setup_heading(): string {

		return esc_html__( 'Create Your First Backup', 'wpforms-lite' );
	}

	/**
	 * Get the description for the setup step.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Setup step description.
	 */
	protected function get_setup_description(): string {

		return esc_html__( 'Back up your site — forms, entries, settings, everything — in just one click.', 'wpforms-lite' );
	}

	/**
	 * Get the setup button text.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Setup button text.
	 */
	protected function get_setup_button_text(): string {

		return esc_html__( 'Create First Backup', 'wpforms-lite' );
	}

	/**
	 * Get the text when setup is completed.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Setup completed text.
	 */
	protected function get_setup_completed_text(): string {

		return esc_html__( 'Backup Created', 'wpforms-lite' );
	}

	/**
	 * Get the text when a pro-version is installed and activated.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Pro installed and activated text.
	 */
	protected function get_pro_installed_activated_text(): string {

		return esc_html__( 'Duplicator Pro Installed & Activated', 'wpforms-lite' );
	}
}
