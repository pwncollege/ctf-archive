<?php

namespace WPForms\Admin\Pages;

/**
 * Abstract class for admin pages.
 *
 * @since 1.9.8.6
 */
abstract class Page {

	/**
	 * Admin menu page slug.
	 *
	 * @since 1.9.8.6
	 *
	 * @var string
	 */
	public const SLUG = '';

	/**
	 * Configuration.
	 *
	 * @since 1.9.8.6
	 *
	 * @var array
	 */
	protected $config = [];

	/**
	 * Runtime data used for generating page HTML.
	 *
	 * @since 1.9.8.6
	 *
	 * @var array
	 */
	protected $output_data = [];

	/**
	 * Constructor.
	 *
	 * @since 1.9.8.6
	 */
	public function __construct() {

		if ( ! wpforms_current_user_can() ) {
			return;
		}

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.9.8.6
	 */
	public function hooks(): void {

		$plugin = static::get_plugin_name();

		if ( wp_doing_ajax() ) {
			add_action( "wp_ajax_wpforms_page_check_{$plugin}_status", [ $this, 'ajax_check_plugin_status' ] );
			add_action( 'wpforms_plugin_activated', [ $this, 'plugin_activated' ] );
		}

		// Check what page we are on.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';

		// Only load if we are actually on the correct page.
		if ( $page !== static::SLUG ) {
			return;
		}

		add_filter( 'wpforms_admin_header', '__return_false' );
		add_action( 'wpforms_admin_page', [ $this, 'output' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );

		/**
		 * Hook for addons.
		 *
		 * @since 1.9.8.6
		 */
		do_action( 'wpforms_admin_pages_page_' . static::get_plugin_name() . '_hooks' );
	}

	/**
	 * Enqueue JS and CSS files.
	 *
	 * @since 1.9.8.6
	 */
	public function enqueue_assets(): void {

		$min = wpforms_get_min_suffix();

		// Lity.
		wp_enqueue_style(
			'wpforms-lity',
			WPFORMS_PLUGIN_URL . 'assets/lib/lity/lity.min.css',
			null,
			'3.0.0'
		);

		wp_enqueue_script(
			'wpforms-lity',
			WPFORMS_PLUGIN_URL . 'assets/lib/lity/lity.min.js',
			[ 'jquery' ],
			'3.0.0',
			true
		);

		// Custom styles for Lity image size limitation.
		wp_add_inline_style(
			'wpforms-lity',
			'
			.lity-image .lity-container {
				max-width: 1040px !important;
			}
			.lity-image img {
				max-width: 1040px !important;
				width: 100%;
				height: auto;
			}
			'
		);

		wp_enqueue_script(
			'wpforms-admin-page-' . static::get_plugin_name(),
			WPFORMS_PLUGIN_URL . "assets/js/admin/pages/common{$min}.js",
			[ 'jquery' ],
			WPFORMS_VERSION,
			true
		);

		wp_localize_script(
			'wpforms-admin-page-' . static::get_plugin_name(),
			'wpforms_pluginlanding',
			$this->get_js_strings()
		);
	}

	/**
	 * Generate and output page HTML.
	 *
	 * @since 1.9.8.6
	 */
	public function output(): void {

		echo '<div id="wpforms-admin-' . esc_attr( static::get_plugin_name() ) . '" class="wrap wpforms-admin-wrap wpforms-admin-plugin-landing">';

		$this->output_section_heading();
		$this->output_section_screenshot();
		$this->output_section_footer();
		$this->output_section_step_install();
		$this->output_section_step_setup();
		$this->output_section_step_result();

		echo '</div>';
	}

	/**
	 * Generate and output step 'Install' section HTML.
	 *
	 * @since 1.9.8.6
	 *
	 * @noinspection HtmlUnknownTarget
	 */
	protected function output_section_step_install(): void {

		$step = $this->get_data_step_install();

		if ( empty( $step ) ) {
			return;
		}

		$button_format       = '<button class="button %3$s" data-plugin="%1$s" data-action="%4$s" data-provider="%5$s">%2$s</button>';
		$button_allowed_html = [
			'button' => [
				'class'         => true,
				'data-plugin'   => true,
				'data-action'   => true,
				'data-provider' => true,
			],
		];

		if (
			! $this->output_data['plugin_installed'] &&
			! $this->output_data['pro_plugin_installed'] &&
			! wpforms_can_install( 'plugin' )
		) {
			$button_format       = '<a class="link" href="%1$s" target="_blank" rel="nofollow noopener">%2$s <span aria-hidden="true" class="dashicons dashicons-external"></span></a>';
			$button_allowed_html = [
				'a'    => [
					'class'  => true,
					'href'   => true,
					'target' => true,
					'rel'    => true,
				],
				'span' => [
					'class'       => true,
					'aria-hidden' => true,
				],
			];
		}

		$plugin_attr = wpforms_is_url( $step['plugin'] ) ? esc_url( $step['plugin'] ) : esc_attr( $step['plugin'] );
		$button      = sprintf( $button_format, $plugin_attr, esc_html( $step['button_text'] ), esc_attr( $step['button_class'] ), esc_attr( $step['button_action'] ), esc_attr( static::get_plugin_name() ) );

		printf(
			'<section class="step step-install">
				<aside class="num">
					<img src="%1$s" alt="%2$s" />
					<i class="loader hidden"></i>
				</aside>
				<div>
					<h2>%3$s</h2>
					<p>%4$s</p>
					%5$s
				</div>
			</section>',
			esc_url( WPFORMS_PLUGIN_URL . 'assets/images/' . $step['icon'] ),
			esc_attr__( 'Step 1', 'wpforms-lite' ),
			esc_html( $step['heading'] ),
			esc_html( $step['description'] ),
			wp_kses( $button, $button_allowed_html )
		);
	}

	/**
	 * Generate and output step 'Setup' section HTML.
	 *
	 * @since 1.9.8.6
	 *
	 * @noinspection HtmlUnknownTarget
	 */
	protected function output_section_step_setup(): void {

		$step = $this->get_data_step_setup();

		if ( empty( $step ) ) {
			return;
		}

		printf(
			'<section class="step step-setup %1$s">
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
			esc_attr__( 'Step 2', 'wpforms-lite' ),
			esc_html( $step['heading'] ),
			esc_html( $step['description'] ),
			esc_attr( $step['button_class'] ),
			esc_url( admin_url( $this->config[ static::get_plugin_name() . '_onboarding' ] ) ),
			esc_html( $step['button_text'] )
		);
	}

	/**
	 * Generate and output footer section HTML.
	 *
	 * @since 1.9.8.6
	 */
	protected function output_section_footer(): void {
		// Default implementation - can be overridden by child classes.
	}

	/**
	 * Step 'Install' data.
	 *
	 * @since 1.9.8.6
	 *
	 * @return array Step data.
	 */
	protected function get_data_step_install(): array {

		$step                = [];
		$step['heading']     = $this->get_install_heading();
		$step['description'] = $this->get_install_description();

		$this->output_data['all_plugins']          = get_plugins();
		$this->output_data['plugin_installed']     = array_key_exists( $this->config['lite_plugin'], $this->output_data['all_plugins'] );
		$this->output_data['plugin_activated']     = false;
		$this->output_data['pro_plugin_installed'] = array_key_exists( $this->config['pro_plugin'], $this->output_data['all_plugins'] );
		$this->output_data['pro_plugin_activated'] = false;

		if ( ! $this->output_data['plugin_installed'] && ! $this->output_data['pro_plugin_installed'] ) {
			$step['icon']          = 'step-1.svg';
			$step['button_text']   = $this->get_install_button_text();
			$step['button_class']  = 'button-primary';
			$step['button_action'] = 'install';
			$step['plugin']        = $this->config['lite_download_url'];

			if ( ! wpforms_can_install( 'plugin' ) ) {
				$step['heading']     = $this->get_plugin_title();
				$step['description'] = '';
				$step['button_text'] = $this->get_plugin_title() . ' on WordPress.org';
				$step['plugin']      = $this->config['lite_wporg_url'];
			}
		} else {
			$this->output_data['plugin_activated'] =
				is_plugin_active( $this->config['lite_plugin'] ) || is_plugin_active( $this->config['pro_plugin'] );
			$step['icon']                          = $this->output_data['plugin_activated'] ? 'step-complete.svg' : 'step-1.svg';
			$step['button_text']                   =
				$this->output_data['plugin_activated']
					? $this->get_installed_activated_text()
					: $this->get_activate_text();
			$step['button_class']                  = $this->output_data['plugin_activated']
				? 'grey disabled'
				: 'button-primary';
			$step['button_action']                 = $this->output_data['plugin_activated'] ? '' : 'activate';
			$step['plugin']                        =
				$this->output_data['pro_plugin_installed'] ? $this->config['pro_plugin'] : $this->config['lite_plugin'];
			$step['is_pro']                        = $this->output_data['pro_plugin_installed'];
		}

		return $step;
	}

	/**
	 * Step 'Setup' data.
	 *
	 * @since 1.9.8.6
	 *
	 * @return array Step data.
	 */
	protected function get_data_step_setup(): array {

		$step = [];

		$this->output_data['plugin_setup'] = false;

		if ( $this->output_data['plugin_activated'] ) {
			$this->output_data['plugin_setup'] = $this->is_plugin_configured();
		}

		$step['icon']          = 'step-2.svg';
		$step['section_class'] = $this->output_data['plugin_activated'] ? '' : 'grey';
		$step['heading']       = $this->get_setup_heading();
		$step['description']   = $this->get_setup_description();
		$step['button_text']   = $this->get_setup_button_text();
		$step['button_class']  = 'grey disabled';

		if ( $this->output_data['plugin_setup'] ) {
			$step['icon']          = 'step-complete.svg';
			$step['section_class'] = '';
			$step['button_text']   = $this->get_setup_completed_text();
		} else {
			$step['button_class'] = $this->output_data['plugin_activated'] ? 'button-primary' : 'grey disabled';
		}

		return $step;
	}

	/**
	 * Ajax endpoint. Check plugin setup status.
	 * Used to properly init the step 2 section after completing step 1.
	 *
	 * @since 1.9.8.6
	 */
	public function ajax_check_plugin_status(): void {

		// Security checks.
		if (
			! check_ajax_referer( 'wpforms-admin', 'nonce', false ) ||
			! wpforms_current_user_can()
		) {
			wp_send_json_error(
				[ 'error' => esc_html__( 'You do not have permission.', 'wpforms-lite' ) ]
			);
		}

		$result = [];

		if ( ! $this->is_plugin_available() ) {
			wp_send_json_error(
				[ 'error' => esc_html__( 'Plugin unavailable.', 'wpforms-lite' ) ]
			);
		}

		$result['setup_status'] = (int) $this->is_plugin_configured();

		$result['license_level']    = 'lite';
		$result['step3_button_url'] = $this->config[ static::get_plugin_name() . '_addon_page' ];

		if ( $this->is_pro_active() ) {
			$result['license_level'] = 'pro';
		}

		$result['result_status'] = $this->is_plugin_finished_setup();

		$result['addon_installed'] = (int) array_key_exists( $this->config[ static::get_plugin_name() . '_addon' ], get_plugins() );

		wp_send_json_success( $result );
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

		update_option( static::get_plugin_name() . '_source', $source, false );
		update_option( static::get_plugin_name() . '_date', time(), false );
	}

	/**
	 * JS strings.
	 *
	 * @since 1.9.8.6
	 *
	 * @return array Array of strings.
	 * @noinspection HtmlUnknownTarget
	 */
	protected function get_js_strings(): array {

		$error_could_not_install = sprintf(
			wp_kses( /* translators: %1$s - Lite plugin download URL. */
				__( 'Could not install the plugin automatically. Please <a href="%1$s">download</a> it and install it manually.', 'wpforms-lite' ),
				[
					'a' => [
						'href' => true,
					],
				]
			),
			esc_url( $this->config['lite_download_url'] ?? '' )
		);

		$error_could_not_activate = sprintf(
			wp_kses( /* translators: %1$s - Plugins page URL. */
				__( 'Could not activate the plugin. Please activate it on the <a href="%1$s">Plugins page</a>.', 'wpforms-lite' ),
				[
					'a' => [
						'href' => true,
					],
				]
			),
			esc_url( admin_url( 'plugins.php' ) )
		);

		return [
			'installing'               => esc_html__( 'Installing...', 'wpforms-lite' ),
			'activating'               => esc_html__( 'Activating...', 'wpforms-lite' ),
			'activated'                => $this->get_installed_activated_text(),
			'activated_pro'            => $this->get_pro_installed_activated_text(),
			'install_now'              => esc_html__( 'Install Now', 'wpforms-lite' ),
			'activate_now'             => esc_html__( 'Activate Now', 'wpforms-lite' ),
			'download_now'             => esc_html__( 'Download Now', 'wpforms-lite' ),
			'plugins_page'             => esc_html__( 'Go to Plugins page', 'wpforms-lite' ),
			'error_could_not_install'  => $error_could_not_install,
			'error_could_not_activate' => $error_could_not_activate,
			static::get_plugin_name() . '_manual_install_url' => $this->config['lite_download_url'],
			static::get_plugin_name() . '_manual_activate_url' => admin_url( 'plugins.php' ),
		];
	}

	/**
	 * Get the plugin name for use in IDs, CSS classes, and config keys.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Plugin name.
	 */
	abstract protected static function get_plugin_name(): string;

	/**
	 * Generate and output heading section HTML.
	 *
	 * @since 1.9.8.6
	 *
	 * @noinspection HtmlUnknownTarget
	 */
	public function output_section_heading(): void {

		$strings = $this->get_heading_strings();

		// Heading section.
		printf(
			'<section class="top">
				<img class="img-top" src="%1$s" alt="%2$s"/>
				<h1>%3$s</h1>
				<p>%4$s</p>
			</section>',
			esc_url( $this->get_heading_image_url() ),
			esc_attr( $this->get_heading_alt_text() ),
			esc_html( $this->get_heading_title() ),
			esc_html( implode( ' ', $strings ) )
		);
	}

	/**
	 * Get heading image URL.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Heading image URL.
	 */
	protected function get_heading_image_url(): string {

		return WPFORMS_PLUGIN_URL . 'assets/images/' . static::get_plugin_name() . '/wpforms-' . static::get_plugin_name() . '.svg';
	}

	/**
	 * Get heading title text.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Heading title.
	 */
	abstract protected function get_heading_title(): string;

	/**
	 * Get heading alt text for logo.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Heading alt text.
	 */
	abstract protected function get_heading_alt_text(): string;

	/**
	 * Get heading description strings.
	 *
	 * @since 1.9.8.6
	 *
	 * @return array Array of description strings.
	 */
	abstract protected function get_heading_strings(): array;

	/**
	 * Generate and output screenshot section HTML.
	 *
	 * @since 1.9.8.6
	 */
	protected function output_section_screenshot(): void {

		$features = $this->get_screenshot_features();

		$list = '';

		foreach ( $features as $feature ) {
			$list .= '<li>' . esc_html( $feature ) . '</li>';
		}

		// Screenshot section.
		printf(
			'<section class="screenshot">
				<div class="cont">
					<img src="%1$s" alt="%2$s" srcset="%4$s 2x"/>
					<a href="%3$s" class="hover" data-lity></a>
				</div>
				<ul>%5$s</ul>
			</section>',
			esc_url( WPFORMS_PLUGIN_URL . 'assets/images/' . static::get_plugin_name() . '/screenshot-tnail.png' ),
			esc_attr( $this->get_screenshot_alt_text() ),
			esc_url( WPFORMS_PLUGIN_URL . 'assets/images/' . static::get_plugin_name() . '/screenshot-full@2x.png' ),
			esc_url( WPFORMS_PLUGIN_URL . 'assets/images/' . static::get_plugin_name() . '/screenshot-tnail@2x.png' ),
			wp_kses( $list, [ 'li' => [] ] )
		);
	}

	/**
	 * Get screenshot features list.
	 *
	 * @since 1.9.8.6
	 *
	 * @return array Array of feature strings.
	 */
	abstract protected function get_screenshot_features(): array;

	/**
	 * Get screenshot alt text.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Alt text for screenshot image.
	 */
	abstract protected function get_screenshot_alt_text(): string;

	/**
	 * Generate and output step 'Result' section HTML.
	 *
	 * @since 1.9.8.6
	 */
	abstract protected function output_section_step_result(): void;

	/**
	 * Whether a plugin is configured or not.
	 *
	 * @since 1.9.8.6
	 *
	 * @return bool True if a plugin is configured properly.
	 */
	abstract protected function is_plugin_configured(): bool;

	/**
	 * Whether a plugin is active or not.
	 *
	 * @since 1.9.8.6
	 *
	 * @return bool True if the plugin is active.
	 */
	abstract protected function is_plugin_activated(): bool;

	/**
	 * Whether a plugin is finished setup or not.
	 *
	 * @since 1.9.8.6
	 *
	 * @return bool True if the plugin is finished setup.
	 */
	abstract protected function is_plugin_finished_setup(): bool;

	/**
	 * Whether a plugin is available (class/function exists).
	 *
	 * @since 1.9.8.6
	 *
	 * @return bool True if a plugin is available.
	 */
	abstract protected function is_plugin_available(): bool;

	/**
	 * Whether a pro-version is active.
	 *
	 * @since 1.9.8.6
	 *
	 * @return bool True if a pro-version is active.
	 */
	abstract protected function is_pro_active(): bool;

	/**
	 * Get the heading for the installation step.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Install step heading.
	 */
	abstract protected function get_install_heading(): string;

	/**
	 * Get the description for the installation step.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Install step description.
	 */
	abstract protected function get_install_description(): string;

	/**
	 * Get the plugin title.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Plugin title.
	 */
	abstract protected function get_plugin_title(): string;

	/**
	 * Get the installation button text.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Install button text.
	 */
	abstract protected function get_install_button_text(): string;

	/**
	 * Get the text when a plugin is installed and activated.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Installed & activated text.
	 */
	abstract protected function get_installed_activated_text(): string;

	/**
	 * Get the activate button text.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Activate button text.
	 */
	abstract protected function get_activate_text(): string;

	/**
	 * Get the heading for the setup step.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Setup step heading.
	 */
	abstract protected function get_setup_heading(): string;

	/**
	 * Get the description for the setup step.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Setup step description.
	 */
	abstract protected function get_setup_description(): string;

	/**
	 * Get the setup button text.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Setup button text.
	 */
	abstract protected function get_setup_button_text(): string;

	/**
	 * Get the text when setup is completed.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Setup completed text.
	 */
	abstract protected function get_setup_completed_text(): string;

	/**
	 * Get the text when a pro-version is installed and activated.
	 *
	 * @since 1.9.8.6
	 *
	 * @return string Pro installed and activated text.
	 */
	abstract protected function get_pro_installed_activated_text(): string;
}
