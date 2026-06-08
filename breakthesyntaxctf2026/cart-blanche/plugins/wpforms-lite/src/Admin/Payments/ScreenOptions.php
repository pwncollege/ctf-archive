<?php

namespace WPForms\Admin\Payments;

use WP_Screen;

/**
 * Payments screen options.
 *
 * @since 1.8.2
 */
class ScreenOptions {

	/**
	 * Screen id.
	 *
	 * @since 1.8.2
	 */
	const SCREEN_ID = 'wpforms_page_wpforms-payments';

	/**
	 * Screen option name.
	 *
	 * @since 1.8.2
	 */
	const PER_PAGE = 'wpforms_payments_per_page';

	/**
	 * Screen option name.
	 *
	 * @since 1.8.2
	 */
	const SINGLE = 'wpforms_payments_single';

	/**
	 * Initialize.
	 *
	 * @since 1.8.2
	 */
	public function init() {

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.8.2
	 */
	private function hooks() {

		// Setup screen options - this needs to run early.
		add_action( 'load-wpforms_page_wpforms-payments', [ $this, 'screen_options' ] );
		add_filter( 'screen_settings', [ $this, 'single_screen_settings' ], 10, 2 );
		add_filter( 'set-screen-option', [ $this, 'screen_options_set' ], 10, 3 );
		add_filter( 'set_screen_option_wpforms_payments_per_page', [ $this, 'screen_options_set' ], 10, 3 );
		add_filter( 'set_screen_option_wpforms_payments_single', [ $this, 'screen_options_set' ], 10, 3 );
	}

	/**
	 * Add per-page screen option to the Payments table.
	 *
	 * @since 1.8.2
	 */
	public function screen_options() {

		$screen = get_current_screen();

		if ( ! isset( $screen->id ) || $screen->id !== self::SCREEN_ID ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['view'] ) && $_GET['view'] !== 'payments' ) {
			return;
		}

		/**
		 * Filter the number of payments per page default value.
		 *
		 * Notice, the filter will be applied to default value in Screen Options only and still will be able to provide other value.
		 * If you want to change the number of payments per page, use the `wpforms_payments_per_page` filter.
		 *
		 * @since 1.8.2
		 *
		 * @param int $per_page Number of payments per page.
		 */
		$per_page = (int) apply_filters( 'wpforms_admin_payments_screen_options_per_page_default', 20 );

		add_screen_option(
			'per_page',
			[
				'label'   => esc_html__( 'Number of payments per page:', 'wpforms-lite' ),
				'option'  => self::PER_PAGE,
				'default' => $per_page,
			]
		);
	}

	/**
	 * Returns the screen options markup for the payment single page.
	 *
	 * @since 1.8.2
	 *
	 * @param string    $status The current screen settings.
	 * @param WP_Screen $args   WP_Screen object.
	 *
	 * @return string
	 */
	public function single_screen_settings( $status, $args ) {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $args->id !== self::SCREEN_ID || empty( $_GET['view'] ) || $_GET['view'] !== 'payment' ) {
			return $status;
		}

		$screen_options   = self::get_single_page_options();
		$advanced_options = [
			'advanced' => __( 'Advanced details', 'wpforms-lite' ),
			'log'      => __( 'Log', 'wpforms-lite' ),
		];

		$output  = '<fieldset class="metabox-prefs">';
		$output .= '<legend>' . esc_html__( 'Additional information', 'wpforms-lite' ) . '</legend>';
		$output .= '<div>';

		foreach ( $advanced_options as $key => $label ) {
			$output .= sprintf(
				'<input name="%1$s" type="checkbox" id="%1$s" value="true" %2$s /><label for="%1$s">%3$s</label>',
				esc_attr( $key ),
				! empty( $screen_options[ $key ] ) ? 'checked="checked"' : '',
				esc_html( $label )
			);
		}

		$output .= '</div></fieldset>';
		$output .= '<p class="submit">';
		$output .= '<input type="hidden" name="wp_screen_options[option]" value="wpforms_payments_single">';
		$output .= '<input type="hidden" name="wp_screen_options[value]" value="true">';
		$output .= '<input type="submit" name="screen-options-apply" id="screen-options-apply" class="button button-primary" value="' . esc_html__( 'Apply', 'wpforms-lite' ) . '">';
		$output .= wp_nonce_field( 'screen-options-nonce', 'screenoptionnonce', false, false );
		$output .= '</p>';

		return $output;
	}

	/**
	 * Get single page screen options.
	 *
	 * @since 1.8.2
	 *
	 * @return false|mixed
	 */
	public static function get_single_page_options() {

		return get_user_option( self::SINGLE );
	}

	/**
	 * Payments table per-page screen option value.
	 *
	 * @since 1.8.2
	 *
	 * @param mixed  $status The value to save instead of the option value.
	 * @param string $option Screen option name.
	 * @param mixed  $value  Screen option value.
	 *
	 * @return mixed
	 */
	public function screen_options_set( $status, $option, $value ) {

		if ( $option === self::PER_PAGE ) {
			return $value;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( $option === self::SINGLE ) {
			return [
				'advanced' => isset( $_POST['advanced'] ) && (bool) $_POST['advanced'],
				'log'      => isset( $_POST['log'] ) && (bool) $_POST['log'],
			];
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		return $status;
	}
}
