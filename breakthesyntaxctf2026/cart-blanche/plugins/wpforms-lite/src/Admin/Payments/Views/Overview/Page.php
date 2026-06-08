<?php

namespace WPForms\Admin\Payments\Views\Overview;

use WPForms\Admin\Helpers\Datepicker;
use WPForms\Db\Payments\ValueValidator;
use WPForms\Admin\Payments\Payments;
use WPForms\Admin\Payments\Views\PaymentsViewsInterface;
use WPForms\Integrations\Stripe\Helpers as StripeHelpers;
use WPForms\Integrations\Square\Helpers as SquareHelpers;

/**
 * Payments Overview Page class.
 *
 * @since 1.8.2
 */
class Page implements PaymentsViewsInterface {

	/**
	 * Payments table.
	 *
	 * @since 1.8.2
	 *
	 * @var Table
	 */
	private $table;

	/**
	 * Payments chart.
	 *
	 * @since 1.8.2
	 *
	 * @var Chart
	 */
	private $chart;

	/**
	 * Initialize class.
	 *
	 * @since 1.8.2
	 */
	public function init() {

		if ( ! $this->has_any_mode_payment() ) {
			return;
		}

		$this->chart = new Chart();
		$this->table = new Table();

		$this->table->prepare_items();
		$this->clean_request_uri();
		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.8.2
	 */
	private function hooks() {

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Get the tab label.
	 *
	 * @since 1.8.2.2
	 *
	 * @return string
	 */
	public function get_tab_label() {

		return __( 'Overview', 'wpforms-lite' );
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 1.8.2
	 */
	public function enqueue_assets() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			'wpforms-flatpickr',
			WPFORMS_PLUGIN_URL . 'assets/lib/flatpickr/flatpickr.min.css',
			[],
			'4.6.9'
		);

		wp_enqueue_script(
			'wpforms-flatpickr',
			WPFORMS_PLUGIN_URL . 'assets/lib/flatpickr/flatpickr.min.js',
			[ 'jquery' ],
			'4.6.9',
			true
		);

		wp_enqueue_style(
			'wpforms-multiselect-checkboxes',
			WPFORMS_PLUGIN_URL . 'assets/lib/wpforms-multiselect/wpforms-multiselect-checkboxes.min.css',
			[],
			'1.0.0'
		);

		wp_enqueue_script(
			'wpforms-multiselect-checkboxes',
			WPFORMS_PLUGIN_URL . 'assets/lib/wpforms-multiselect/wpforms-multiselect-checkboxes.min.js',
			[],
			'1.0.0',
			true
		);

		wp_enqueue_script(
			'wpforms-chart',
			WPFORMS_PLUGIN_URL . 'assets/lib/chart.min.js',
			[ 'moment' ],
			'4.5.1',
			true
		);

		wp_enqueue_script(
			'wpforms-chart-adapter-moment',
			WPFORMS_PLUGIN_URL . 'assets/lib/chartjs-adapter-moment.min.js',
			[ 'moment', 'wpforms-chart' ],
			'1.0.1',
			true
		);

		wp_enqueue_script(
			'wpforms-admin-payments-overview',
			WPFORMS_PLUGIN_URL . "assets/js/admin/payments/overview{$min}.js",
			[ 'jquery', 'wpforms-flatpickr', 'wpforms-chart' ],
			WPFORMS_VERSION,
			true
		);

		$admin_l10n = [
			'settings'    => $this->chart->get_chart_settings(),
			'locale'      => sanitize_key( wpforms_get_language_code() ),
			'nonce'       => wp_create_nonce( 'wpforms_payments_overview_nonce' ),
			'date_format' => sanitize_text_field( Datepicker::get_wp_date_format_for_momentjs() ),
			'delimiter'   => Datepicker::TIMESPAN_DELIMITER,
			'report'      => Chart::ACTIVE_REPORT,
			'currency'    => sanitize_text_field( wpforms_get_currency() ),
			'decimals'    => absint( wpforms_get_currency_decimals( wpforms_get_currency() ) ),
			'i18n'        => [
				'label'                       => esc_html__( 'Payments', 'wpforms-lite' ),
				'delete_button'               => esc_html__( 'Delete', 'wpforms-lite' ),
				'subscription_delete_confirm' => $this->get_subscription_delete_confirmation_message(),
				'no_dataset'                  => [
					'total_payments'             => esc_html__( 'No payments for selected period', 'wpforms-lite' ),
					'total_sales'                => esc_html__( 'No sales for selected period', 'wpforms-lite' ),
					'total_refunded'             => esc_html__( 'No refunds for selected period', 'wpforms-lite' ),
					'total_subscription'         => esc_html__( 'No new subscriptions for selected period', 'wpforms-lite' ),
					'total_renewal_subscription' => esc_html__( 'No subscription renewals for the selected period', 'wpforms-lite' ),
					'total_coupons'              => esc_html__( 'No coupons applied during the selected period', 'wpforms-lite' ),
				],
			],
			'page_uri'    => $this->get_current_uri(),
		];

		wp_localize_script(
			'wpforms-admin-payments-overview', // Script handle the data will be attached to.
			'wpforms_admin_payments_overview', // Name for the JavaScript object.
			$admin_l10n
		);
	}

	/**
	 * Retrieve a Payment Overview URI.
	 *
	 * @since 1.8.2
	 *
	 * @return string
	 */
	private function get_current_uri() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$query = $_GET;

		unset( $query['mode'], $query['paged'] );

		return add_query_arg( $query, self::get_url() );
	}

	/**
	 * Determine whether the current user has the capability to view the page.
	 *
	 * @since 1.8.2
	 *
	 * @return bool
	 */
	public function current_user_can() {

		return wpforms_current_user_can();
	}

	/**
	 * Page heading.
	 *
	 * @since 1.8.2
	 */
	public function heading() {

		Helpers::get_default_heading();
	}

	/**
	 * Page content.
	 *
	 * @since 1.8.2
	 */
	public function display() {

		// If there are no payments at all, display an empty state.
		if ( ! $this->has_any_mode_payment() ) {
			$this->display_empty_state();

			return;
		}

		// Display the page content, including the chart and the table.
		$this->chart->display();
		$this->table->display();
	}

	/**
	 * Get the URL of the page.
	 *
	 * @since 1.8.2
	 *
	 * @return string
	 */
	public static function get_url() {

		static $url;

		if ( $url ) {
			return $url;
		}

		$url = add_query_arg(
			[
				'page' => Payments::SLUG,
			],
			admin_url( 'admin.php' )
		);

		return $url;
	}

	/**
	 * Get payment mode.
	 *
	 * Use only for logged-in users. Returns mode from user meta data or from the $_GET['mode'] parameter.
	 *
	 * @since 1.8.2
	 *
	 * @return string
	 */
	public static function get_mode(): string {

		static $mode;

		if ( ! self::is_valid_context_for_mode() ) {
			return 'live';
		}

		if ( $mode ) {
			return $mode;
		}

		$mode     = self::get_mode_from_request();
		$user_id  = get_current_user_id();
		$meta_key = 'wpforms-payments-mode';

		if ( self::is_mode_valid_and_nonce_verified( $mode ) ) {
			update_user_meta( $user_id, $meta_key, $mode );

			return $mode;
		}

		$mode = (string) get_user_meta( $user_id, $meta_key, true );

		if ( empty( $mode ) || ! Helpers::is_test_payment_exists() ) {
			$mode = 'live';
		}

		return $mode;
	}

	/**
	 * Check if the context is valid for payment mode.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	private static function is_valid_context_for_mode(): bool {

		return wpforms_is_admin_ajax() || wpforms_is_admin_page( 'payments' ) || wpforms_is_admin_page( 'entries' );
	}

	/**
	 * Retrieve the payment mode from the request.
	 *
	 * @since 1.9.5
	 *
	 * @return string
	 */
	private static function get_mode_from_request(): string {

		// Nonce is checked in the `is_mode_valid_and_nonce_verified` method.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return isset( $_GET['mode'] ) ? sanitize_key( $_GET['mode'] ) : '';
	}

	/**
	 * Determine if the mode is valid and the nonce is verified.
	 *
	 * @since 1.9.5
	 *
	 * @param string $mode Payment mode to validate.
	 *
	 * @return bool
	 */
	private static function is_mode_valid_and_nonce_verified( string $mode ): bool {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return ValueValidator::is_valid( $mode, 'mode' ) &&
			isset( $_GET['_wpnonce'] ) &&
			wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'wpforms_payments_overview_nonce' );
	}

	/**
	 * Display one of the empty states.
	 *
	 * @since 1.8.2
	 */
	private function display_empty_state() {

		// If a payment gateway is configured, output no payments state.
		if ( $this->is_gateway_configured() ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo wpforms_render(
				'admin/empty-states/payments/no-payments',
				[
					'cta_url' => add_query_arg(
						[
							'page' => 'wpforms-overview',
						],
						'admin.php'
					),
				],
				true
			);

			return;
		}

		// Otherwise, output get started state.
		$is_upgraded = StripeHelpers::is_allowed_license_type();
		$message     = __( "First you need to set up a payment gateway. We've partnered with <strong>Stripe and Square</strong> to bring easy payment forms to everyone.&nbsp;", 'wpforms-lite' );
		$message    .= $is_upgraded
			? sprintf( /* translators: %s - WPForms Addons admin page URL. */
				__( 'Other payment gateways such as <strong>PayPal</strong> and <strong>Authorize.Net</strong> can be installed from the <a href="%s">Addons screen</a>.', 'wpforms-lite' ),
				esc_url(
					add_query_arg(
						[
							'page' => 'wpforms-addons',
						],
						admin_url( 'admin.php' )
					)
				)
			)
			: sprintf( /* translators: %s - WPForms.com Upgrade page URL. */
				__( "If you'd like to use another payment gateway, please consider <a href='%s'>upgrading to WPForms Pro</a>.", 'wpforms-lite' ),
				esc_url( wpforms_admin_upgrade_link( 'Payments Dashboard', 'Splash - Upgrade to Pro Text' ) )
			);

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render(
			'admin/empty-states/payments/get-started',
			[
				'message' => $message,
				'version' => $is_upgraded ? 'pro' : 'lite',
				'cta_url' => add_query_arg(
					[
						'page' => 'wpforms-settings',
						'view' => 'payments',
					],
					admin_url( 'admin.php' )
				),
			],
			true
		);
	}

	/**
	 * Determine whether Stripe or Square payment gateway is configured.
	 *
	 * @since 1.8.2
	 *
	 * @return bool
	 */
	private function is_gateway_configured(): bool {

		/**
		 * Allow to modify a status whether Stripe or Square payment gateway is configured.
		 *
		 * @since 1.8.2
		 *
		 * @param bool $is_configured True if Stripe or Square payment gateway is configured.
		 */
		return (bool) apply_filters( 'wpforms_admin_payments_views_overview_page_gateway_is_configured', StripeHelpers::has_stripe_keys() || SquareHelpers::is_square_configured() );
	}

	/**
	 * Determine whether there are payments of any modes.
	 *
	 * @since 1.8.2
	 *
	 * @return bool
	 */
	private function has_any_mode_payment() {

		static $has_any_mode_payment;

		if ( $has_any_mode_payment !== null ) {
			return $has_any_mode_payment;
		}

		$has_any_mode_payment = count(
			wpforms()->obj( 'payment' )->get_payments(
				[
					'mode'   => 'any',
					'number' => 1,
				]
			)
		) > 0;

		// Check on trashed payments.
		if ( ! $has_any_mode_payment ) {
			$has_any_mode_payment = count(
				wpforms()->obj( 'payment' )->get_payments(
					[
						'mode'         => 'any',
						'number'       => 1,
						'is_published' => 0,
					]
				)
			) > 0;
		}

		return $has_any_mode_payment;
	}

	/**
	 * To avoid recursively, remove the previous variables from the REQUEST_URI.
	 *
	 * @since 1.8.2
	 */
	private function clean_request_uri() {

		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended
			$_SERVER['REQUEST_URI'] = remove_query_arg( [ '_wpnonce', '_wp_http_referer', 'action', 'action2', 'payment_id' ], wp_unslash( $_SERVER['REQUEST_URI'] ) );

			if ( empty( $_GET['s'] ) ) {
				$_SERVER['REQUEST_URI'] = remove_query_arg( [ 'search_where', 'search_mode', 's' ], wp_unslash( $_SERVER['REQUEST_URI'] ) );
			}
			// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended
		}
	}

	/**
	 * Get the subscription delete confirmation message.
	 * The returned message is used in the JavaScript file and shown in a "Heads up!" modal.
	 *
	 * @since 1.8.4
	 *
	 * @return string
	 */
	private function get_subscription_delete_confirmation_message() {

		$help_link = wpforms_utm_link(
			'https://wpforms.com/docs/viewing-and-managing-payments/#deleting-parent-subscription',
			'Delete Payment',
			'Learn More'
		);

		return sprintf(
			wp_kses( /* translators: WPForms.com docs page URL. */
				__( 'Deleting one or more selected payments may prevent processing of future subscription renewals. Payment filtering may also be affected. <a href="%1$s" rel="noopener" target="_blank">Learn More</a>', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'rel'    => [],
						'target' => [],
					],
				]
			),
			esc_url( $help_link )
		);
	}
}
