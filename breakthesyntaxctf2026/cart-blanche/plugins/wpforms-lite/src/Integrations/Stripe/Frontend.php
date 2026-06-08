<?php

namespace WPForms\Integrations\Stripe;

use Elementor\Plugin;

/**
 * Stripe form frontend related functionality.
 *
 * @since 1.8.2
 */
class Frontend {

	/**
	 * Handle name for wp_register_styles handle.
	 *
	 * @since 1.8.2
	 *
	 * @var string
	 */
	const HANDLE = 'wpforms-stripe';

	/**
	 * Api interface.
	 *
	 * @since 1.8.2
	 *
	 * @var Api\ApiInterface
	 */
	private $api;

	/**
	 * Initialize.
	 *
	 * @since 1.8.2
	 *
	 * @param Api\ApiInterface $api Api interface.
	 */
	public function init( $api ) {

		$this->api = $api;

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.8.2
	 */
	private function hooks() {

		add_action( 'wpforms_frontend_container_class', [ $this, 'form_container_class' ], 10, 2 );
		add_action( 'wpforms_wp_footer', [ $this, 'enqueues' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_assets' ] );
		add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'elementor_enqueues' ] );
		add_action( 'enqueue_block_assets', [ $this, 'enqueue_block_assets' ] );
		add_filter( 'register_block_type_args', [ $this, 'register_block_type_args' ], 20, 2 );

		if ( wpforms_is_divi_editor() ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_divi_styles' ], 12 );
		}
	}

	/**
	 * Add class to form container if Stripe is enabled.
	 *
	 * @since 1.8.2
	 *
	 * @param array $class     Array of form classes.
	 * @param array $form_data Form data of current form.
	 *
	 * @return array
	 */
	public function form_container_class( $class, $form_data ) {

		if ( ! Helpers::has_stripe_field( $form_data ) ) {
			return $class;
		}

		if ( ! Helpers::has_stripe_keys() ) {
			return $class;
		}

		if ( Helpers::is_payments_enabled( $form_data ) ) {
			$class[] = 'wpforms-stripe';
		}

		return $class;
	}

	/**
	 * Enqueue assets in the frontend if Stripe is in use on the page.
	 *
	 * @since 1.8.2
	 *
	 * @param array $forms Form data of forms on current page.
	 */
	public function enqueues( $forms ) {

		if (
			! Helpers::has_stripe_enabled( $forms ) ||
			! Helpers::has_stripe_field( $forms, true )
		) {
			return;
		}

		$this->enqueue_assets();
	}

	/**
	 * Enqueue block editor assets.
	 *
	 * @since 1.8.6
	 */
	public function enqueue_block_assets() {

		if ( ! is_admin() ) {
			return;
		}

		$this->enqueue_styles();
	}

	/**
	 * Enqueue assets on the frontend.
	 *
	 * @since 1.8.2
	 */
	public function enqueue_assets() {

		$min = wpforms_get_min_suffix();

		if ( ! Helpers::has_stripe_keys() ) {
			return;
		}

		$config    = $this->api->get_config();
		$in_footer = ! wpforms_is_frontend_js_header_force_load();

		wp_enqueue_script(
			'wpforms-generic-utils',
			WPFORMS_PLUGIN_URL . "assets/js/share/utils{$min}.js",
			[ 'jquery' ],
			WPFORMS_VERSION,
			$in_footer
		);

		wp_enqueue_script(
			'stripe-js',
			$config['remote_js_url'],
			[ 'jquery' ],
			null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			$in_footer
		);

		wp_enqueue_script(
			self::HANDLE,
			$config['local_js_url'],
			[ 'jquery', 'stripe-js', 'wpforms-generic-utils' ],
			WPFORMS_VERSION,
			$in_footer
		);

		wp_localize_script(
			self::HANDLE,
			'wpforms_stripe',
			[
				'publishable_key' => Helpers::get_stripe_key( 'publishable' ),
				'data'            => $config['localize_script'],
				'i18n'            => [
					'empty_details'      => esc_html__( 'Please fill out payment details to continue.', 'wpforms-lite' ),
					'element_load_error' => esc_html__( 'Payment Element failed to load. Stripe API responded with the message:', 'wpforms-lite' ),
					'token_already_used' => esc_html__( 'The security token has expired. Please resubmit the form.', 'wpforms-lite' ),
				],
				'styles_enabled'  => (int) wpforms_setting( 'disable-css', '1' ) !== 3,
			]
		);

		$this->enqueue_styles();
	}

	/**
	 * Set editor style for block type editor.
	 *
	 * @since 1.8.2
	 *
	 * @param array  $args       Array of arguments for registering a block type.
	 * @param string $block_type Block type name including namespace.
	 */
	public function register_block_type_args( $args, $block_type ) {

		if ( $block_type !== 'wpforms/form-selector' || ! is_admin() ) {
			return $args;
		}

		$config = $this->api->get_config();

		if ( ! isset( $config['local_css_url'] ) ) {
			return $args;
		}

		wp_register_style(
			'wpforms-stripe',
			$config['local_css_url'],
			[ $args['editor_style'] ],
			WPFORMS_VERSION
		);

		$args['editor_style'] = self::HANDLE;

		return $args;
	}

	/**
	 * Enqueue styles for Elementor preview.
	 *
	 * @since 1.8.4.1
	 *
	 * @noinspection PhpUndefinedFieldInspection
	 */
	public function elementor_enqueues() {

		if (
			! class_exists( Plugin::class ) ||
			empty( Plugin::instance()->preview ) ||
			! Plugin::instance()->preview->is_preview_mode()
		) {
			return;
		}

		$this->enqueue_styles();
	}

	/**
	 * Enqueue styles.
	 *
	 * @since 1.8.4.1
	 * @since 1.9.4 Become public for the action callback.
	 */
	public function enqueue_styles(): void {

		if ( (int) wpforms_setting( 'disable-css', '1' ) === 3 ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			self::HANDLE,
			WPFORMS_PLUGIN_URL . "assets/css/integrations/stripe/wpforms-stripe{$min}.css",
			[],
			WPFORMS_VERSION
		);
	}

	/**
	 * Enqueue Stripe integration styles for Divi Builder.
	 *
	 * @since 1.9.9
	 */
	public function enqueue_divi_styles(): void {

		if ( (int) wpforms_setting( 'disable-css', '1' ) === 3 ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			self::HANDLE,
			WPFORMS_PLUGIN_URL . "assets/css/integrations/stripe/divi/wpforms-stripe{$min}.css",
			[],
			WPFORMS_VERSION
		);
	}
}
