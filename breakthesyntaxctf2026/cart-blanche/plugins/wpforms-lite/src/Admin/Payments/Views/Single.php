<?php

namespace WPForms\Admin\Payments\Views;

use WPForms\Admin\Payments\ScreenOptions;
use WPForms\Admin\Payments\Views\Overview\Helpers;
use WPForms\Db\Payments\ValueValidator;

/**
 * Payments Overview Page class.
 *
 * @since 1.8.2
 */
class Single implements PaymentsViewsInterface {

	/**
	 * Abort. Bail on proceeding to process the page.
	 *
	 * @since 1.8.2
	 *
	 * @var bool
	 */
	private $abort = false;

	/**
	 * The human readable error message.
	 *
	 * @since 1.8.2
	 *
	 * @var string
	 */
	private $abort_message;

	/**
	 * Payment object.
	 *
	 * @since 1.8.2
	 *
	 * @var object
	 */
	private $payment;

	/**
	 * Payment meta.
	 *
	 * @since 1.8.2
	 *
	 * @var array
	 */
	private $payment_meta;

	/**
	 * Subscription object, if applicable.
	 *
	 * @since 1.8.4
	 *
	 * @var object
	 */
	private $subscription;

	/**
	 * Subscription meta, if applicable.
	 *
	 * @since 1.8.4
	 *
	 * @var array
	 */
	private $subscription_meta;

	/**
	 * Subscription renewal payments, if applicable.
	 * This is an array of payment objects.
	 *
	 * @since 1.8.4
	 *
	 * @var array
	 */
	private $renewals = [];

	/**
	 * Initialize class.
	 *
	 * @since 1.8.2
	 */
	public function init() {

		$this->setup();
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

		return '';
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 1.8.2
	 */
	public function enqueue_assets() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			'tooltipster',
			WPFORMS_PLUGIN_URL . 'assets/lib/jquery.tooltipster/jquery.tooltipster.min.css',
			null,
			'4.2.6'
		);

		wp_enqueue_script(
			'tooltipster',
			WPFORMS_PLUGIN_URL . 'assets/lib/jquery.tooltipster/jquery.tooltipster.min.js',
			[ 'jquery' ],
			'4.2.6',
			true
		);

		wp_enqueue_script(
			'wpforms-admin-payments-single',
			WPFORMS_PLUGIN_URL . "assets/js/admin/payments/single{$min}.js",
			[ 'tooltipster' ],
			WPFORMS_VERSION,
			true
		);

		wp_localize_script(
			'wpforms-admin-payments-single',
			'wpforms_admin_payments_single',
			[
				'payment_delete_confirm' => esc_html__( 'Are you sure you want to delete this payment and all its information (details, notes, etc.)?', 'wpforms-lite' ),
				'payment_refund_confirm' => esc_html__( 'Are you sure you want to refund this payment?', 'wpforms-lite' ),
				'payment_cancel_confirm' => esc_html__( 'Are you sure you want to cancel this subscription?', 'wpforms-lite' ),
				'payment_refund_success' => esc_html__( 'Payment was successfully refunded!', 'wpforms-lite' ),
				'payment_cancel_success' => esc_html__( 'Subscription was successfully canceled!', 'wpforms-lite' ),
			]
		);
	}

	/**
	 * Setup data.
	 *
	 * @since 1.8.2
	 */
	private function setup() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$payment_id = ! empty( $_GET['payment_id'] ) ? absint( $_GET['payment_id'] ) : 0;

		if ( ! $payment_id ) {
			$this->abort_message = esc_html__( 'It looks like the provided payment ID is not valid.', 'wpforms-lite' );
			$this->abort         = true;

			return;
		}

		$this->payment = wpforms()->obj( 'payment' )->get( $payment_id );

		// No payment was found.
		if ( empty( $this->payment ) ) {
			$this->abort_message = esc_html__( 'It looks like the payment you are trying to access is no longer available.', 'wpforms-lite' );
			$this->abort         = true;

			return;
		}

		// Payment in the Trash.
		if ( ! $this->payment->is_published ) {
			$this->abort_message = esc_html__( "You can't edit this payment because it's in the trash.", 'wpforms-lite' );
			$this->abort         = true;

			return;
		}

		$this->payment_meta = wpforms()->obj( 'payment_meta' )->get_all( $payment_id );

		// Retrieve the subscription renewal payments, if applicable.
		if ( ! empty( $this->payment->subscription_id ) ) {
			// Assign renewals to reduce queries and reuse later.
			list( $this->subscription, $this->renewals ) = wpforms()->obj( 'payment_queries' )->get_subscription_payment_history( $this->payment->subscription_id, $this->payment->currency );

			if ( ! empty( $this->subscription ) ) {
				$this->subscription_meta = wpforms()->obj( 'payment_meta' )->get_all( $this->subscription->id );
			}
		}
	}

	/**
	 * Check if the current user has the capability to view the page.
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

		if ( $this->abort ) {
			return;
		}

		$payment_prev = wpforms()->obj( 'payment_queries' )->get_prev( $this->payment->id, [ 'mode' => $this->payment->mode ] );
		$payment_next = wpforms()->obj( 'payment_queries' )->get_next( $this->payment->id, [ 'mode' => $this->payment->mode ] );
		$prev_url     = ! empty( $payment_prev ) ? add_query_arg(
			[
				'page'       => 'wpforms-payments',
				'view'       => 'payment',
				'payment_id' => (int) $payment_prev->id,
			],
			admin_url( 'admin.php' )
		) : '';
		$next_url     = ! empty( $payment_next ) ? add_query_arg(
			[
				'page'       => 'wpforms-payments',
				'view'       => 'payment',
				'payment_id' => (int) $payment_next->id,
			],
			admin_url( 'admin.php' )
		) : '';

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render(
			'admin/payments/single/heading-navigation',
			[
				'count'        => (int) wpforms()->obj( 'payment_queries' )->count_all( [ 'mode' => $this->payment->mode ] ),
				'prev_count'   => (int) wpforms()->obj( 'payment_queries' )->get_prev_count( $this->payment->id, [ 'mode' => $this->payment->mode ] ),
				'prev_url'     => $prev_url,
				'prev_class'   => empty( $payment_prev ) ? 'inactive' : '',
				'next_url'     => $next_url,
				'next_class'   => empty( $payment_next ) ? 'inactive' : '',
				'overview_url' => add_query_arg(
					[
						'page' => 'wpforms-payments',
					],
					admin_url( 'admin.php' )
				),
			],
			true
		);
	}

	/**
	 * Page content.
	 *
	 * @since 1.8.2
	 */
	public function display() {

		if ( $this->abort ) {

			echo '<div class="wpforms-admin-content">';
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo wpforms_render(
					'admin/payments/single/no-payment',
					[
						'message' => $this->abort_message,
					],
					true
				);
			echo '</div>';

			return;
		}

		$screen_options = ScreenOptions::get_single_page_options();

		echo '<div id="poststuff"><div id="post-body" class="metabox-holder columns-2">';
			echo '<div id="post-body-content">';

				$this->payment_details();
				$this->education_details();
				$this->subscription_details();
				$this->subscription_payment_history();

				if ( ! empty( $screen_options['advanced'] ) ) {
					$this->advanced_details();
				}

				$this->entry_details();
			echo '</div>';
			echo '<div id="postbox-container-1" class="postbox-container">';
				$this->details();

				if ( ! empty( $screen_options['log'] ) ) {
					$this->log();
				}
		echo '</div></div></div>';
	}

	/**
	 * Payment details output.
	 *
	 * @since 1.8.2
	 */
	private function payment_details() {

		$payment_type_class = ! empty( $this->payment->subscription_id ) ? 'subscription' : 'one-time';

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render(
			'admin/payments/single/payment-details',
			[
				'id'                  => 'wpforms-payment-info',
				'class'               => 'payment-details',
				'title'               => __( 'Payment Details', 'wpforms-lite' ),
				'payment_id'          => "#{$this->payment->id}",
				'gateway_link'        => $this->get_gateway_transaction_link(),
				'gateway_text'        => sprintf( /* translators: %s - payment gateway name. */
					__( 'View in %s', 'wpforms-lite' ),
					$this->get_gateway_name()
				),
				'gateway_name'        => $this->payment->gateway,
				'gateway_action_text' => __( 'Refund', 'wpforms-lite' ),
				'gateway_action_slug' => 'refund',
				'gateway_action_link' => $this->get_gateway_action_link( 'refund' ),
				'payment_id_raw'      => $this->payment->id,
				'status'              => $this->payment->status,
				'status_label'        => $this->get_status_label(),
				'disabled'            => $this->payment->status === 'refunded',
				'stat_cards'          => [
					'total'  => [
						'label'          => esc_html__( 'Total', 'wpforms-lite' ),
						'value'          => wpforms_format_amount( wpforms_sanitize_amount( $this->payment->total_amount, $this->payment->currency ), true, $this->payment->currency ),
						'button_classes' => [
							'total',
							'is-amount',
						],
					],
					'type'   => [
						'label'          => esc_html__( 'Type', 'wpforms-lite' ),
						'value'          => $this->get_payment_type(),
						'button_classes' => [
							$payment_type_class,
						],
					],
					'method' => [
						'label'          => esc_html__( 'Method', 'wpforms-lite' ),
						'value'          => $this->get_payment_method(),
						'button_classes' => [
							'method',
						],
						'tooltip'        => $this->get_payment_method_details(),
					],
					'coupon' => [
						'label'          => esc_html__( 'Coupon', 'wpforms-lite' ),
						'value'          => $this->get_coupon_value(),
						'button_classes' => [
							'coupon',
							'upsell',
						],
						'tooltip'        => nl2br( $this->get_coupon_info() ),
					],
				],
			],
			true
		);
	}

	/**
	 * Subscription details output.
	 *
	 * @since 1.8.2
	 */
	private function subscription_details() {

		if ( empty( $this->subscription ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render(
			'admin/payments/single/payment-details',
			[
				'id'                  => 'wpforms-subscription-details',
				'class'               => 'subscription-details',
				'title'               => __( 'Subscription Details', 'wpforms-lite' ),
				'gateway_link'        => $this->get_gateway_subscription_link(),
				'gateway_text'        => sprintf( /* translators: %s - payment gateway name. */
					__( 'View in %s', 'wpforms-lite' ),
					$this->get_gateway_name()
				),
				'gateway_name'        => $this->payment->gateway,
				'gateway_action_text' => __( 'Cancel', 'wpforms-lite' ),
				'gateway_action_slug' => 'cancel',
				'gateway_action_link' => $this->get_gateway_action_link( 'cancel' ),
				'payment_id_raw'      => $this->subscription->id,
				'status'              => $this->subscription->subscription_status,
				'status_label'        => ValueValidator::get_allowed_subscription_statuses()[ $this->subscription->subscription_status ],
				'disabled'            => in_array( $this->subscription->subscription_status, [ 'cancelled', 'completed' ], true ),
				'stat_cards'          => [
					'total'   => [
						'label'          => esc_html__( 'Lifetime Total', 'wpforms-lite' ),
						'value'          => $this->get_subscription_lifetime_total(),
						'button_classes' => [
							'lifetime-total',
							'is-amount',
						],
					],
					'cycle'   => [
						'label'          => esc_html__( 'Billing Cycle', 'wpforms-lite' ),
						'value'          => $this->get_subscription_cycle(),
						'button_classes' => [
							'cycle',
						],
					],
					'billed'  => [
						'label'          => esc_html__( 'Times Billed', 'wpforms-lite' ),
						'value'          => $this->get_subscription_times_billed(),
						'button_classes' => [
							'cycle',
						],
					],
					'renewal' => [
						'label'          => esc_html__( 'Renewal Date', 'wpforms-lite' ),
						'value'          => $this->get_renewal_date(),
						'button_classes' => [
							'date',
						],
					],
				],
			],
			true
		);
	}

	/**
	 * Subscription payment history output.
	 *
	 * @since 1.8.4
	 */
	private function subscription_payment_history() {

		// Early bail if no subscription ID.
		if ( empty( $this->payment->subscription_id ) ) {
			return;
		}

		// Early bail if no subscription or renewals found.
		// "$this->renewals" is set in the "setup" method.
		if ( empty( $this->renewals ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render(
			'admin/payments/single/payment-history',
			[
				'title'               => __( 'Payment History', 'wpforms-lite' ),
				'renewals'            => $this->renewals,
				'types'               => ValueValidator::get_allowed_subscription_types(),
				'statuses'            => ValueValidator::get_allowed_statuses(),
				'placeholder_na_text' => Helpers::get_placeholder_na_text( false ),
				'single_url'          => add_query_arg(
					[
						'page' => 'wpforms-payments',
						'view' => 'payment',
					],
					admin_url( 'admin.php' )
				),
            ],
			true
		);
	}

	/**
	 * Get Subscription cycle.
	 *
	 * @since 1.8.2
	 *
	 * @return string
	 */
	private function get_subscription_cycle() {

		$allowed_intervals = ValueValidator::get_allowed_subscription_intervals();

		if ( isset( $this->subscription_meta['subscription_period']->value, $allowed_intervals[ $this->subscription_meta['subscription_period']->value ] ) ) {
			$amount   = wpforms_format_amount( wpforms_sanitize_amount( $this->payment->total_amount, $this->payment->currency ), true, $this->payment->currency );
			$interval = $allowed_intervals[ $this->subscription_meta['subscription_period']->value ];

			return "{$amount} / {$interval}";
		}

		return Helpers::get_placeholder_na_text( false );
	}

	/**
	 * Get Subscription lifetime total.
	 *
	 * @since 1.8.4
	 *
	 * @return string
	 */
	private function get_subscription_lifetime_total() {

		return wpforms_format_amount( (float) $this->subscription->total_amount + array_sum( array_column( $this->renewals, 'total_amount' ) ), true, $this->payment->currency );
	}

	/**
	 * Get Subscription times billed.
	 *
	 * @since 1.8.4
	 *
	 * @return int|string
	 */
	private function get_subscription_times_billed() {

		// Display "N/A", in case no subscription ID is found.
		if ( empty( $this->payment->subscription_id ) ) {
			return Helpers::get_placeholder_na_text( false );
		}

		// Add the initial subscription payment object to the renewal array.
		// The "+1" has to be added, because the initial subscription payment is not included in the renewals array.
		if ( ! empty( $this->subscription ) ) {
			$this->renewals[] = $this->subscription;
		}

		return count( $this->renewals );
	}

	/**
	 * Get Subscription renewal date.
	 *
	 * @since 1.8.2
	 *
	 * @return string
	 */
	private function get_renewal_date() {

		if (
			$this->payment->subscription_status === 'cancelled'
			|| $this->is_renewal_of_cancelled_subscription()
		) {
			return Helpers::get_placeholder_na_text( false );
		}

		$converted_periods = [
			'daily'      => '+1 day',
			'weekly'     => '+1 week',
			'monthly'    => '+1 month',
			'quarterly'  => '+3 month',
			'semiyearly' => '+6 month',
			'yearly'     => '+1 year',
		];

		if ( ! isset( $this->subscription_meta['subscription_period']->value, $converted_periods[ $this->subscription_meta['subscription_period']->value ] ) ) {
			return '';
		}

		return gmdate( 'M d, Y', strtotime( $this->payment->date_updated_gmt . $converted_periods[ $this->subscription_meta['subscription_period']->value ] ) );
	}

	/**
	 * Is renewal of cancelled subscription.
	 *
	 * @since 1.8.4
	 *
	 * @return bool
	 */
	private function is_renewal_of_cancelled_subscription() {

		return $this->payment->type === 'renewal'
			&& $this->subscription->subscription_status === 'cancelled';
	}

	/**
	 * Get payment type name.
	 * i.e. One-time, Subscription, etc.
	 *
	 * @since 1.8.4
	 *
	 * @return string
	 */
	private function get_payment_type() {

		if ( isset( $this->payment->type ) && ValueValidator::is_valid( $this->payment->type, 'type' ) ) {
			return ValueValidator::get_allowed_types()[ $this->payment->type ];
		}

		return Helpers::get_placeholder_na_text( false );
	}

	/**
	 * Get payment method type.
	 *
	 * @since 1.8.2
	 *
	 * @return string
	 */
	private function get_payment_method(): string {

		$method = isset( $this->payment_meta['credit_card_method'] ) ? ucfirst( $this->payment_meta['credit_card_method']->value ) : '';

		if ( $method ) {
			return $method;
		}

		if ( ! isset( $this->payment_meta['method_type'] ) ) {
			return Helpers::get_placeholder_na_text( false );
		}

		return $this->get_formatted_payment_method();
	}

	/**
	 * Get payment method details.
	 *
	 * @since 1.8.2
	 *
	 * @return string
	 */
	private function get_payment_method_details(): string {

		if ( empty( $this->payment_meta['credit_card_last4'] ) ) {
			return '';
		}

		$rows = [];

		// 1. Credit Card Name.
		if ( ! empty( $this->payment_meta['credit_card_name']->value ) ) {
			$rows[] = $this->payment_meta['credit_card_name']->value;
		}

		// 2. Credit Card Last 4 digits.
		$rows[] = "xxxx xxxx xxxx {$this->payment_meta['credit_card_last4']->value}";

		// 3. Credit Card Expiry Date.
		if ( ! empty( $this->payment_meta['credit_card_expires']->value ) ) {
			$rows[] = sprintf( /* translators: %s - credit card expiry date. */
				__( 'Expires %s', 'wpforms-lite' ),
				$this->payment_meta['credit_card_expires']->value
			);
		}

		// 4. Payment Method Type.
		if ( ! empty( $this->payment_meta['method_type']->value ) ) {
			$rows[] = sprintf( /* translators: %s - credit card expiry date. */
				__( 'Method: %s', 'wpforms-lite' ),
				$this->get_formatted_payment_method()
			);
		}

		// Escape all rows.
		$rows = array_map( 'esc_html', $rows );
		// Wrap each row in a span tag.
		$output  = '<div><span>';
		$output .= implode( '</span></br><span>', $rows );

		return $output . '</span></div>';
	}

	/**
	 * Retrieves the formatted payment method name.
	 *
	 * Converts the payment method type from a stored format (e.g., snake_case or kebab-case)
	 * into a human-readable string with each word capitalized.
	 *
	 * @since 1.10.0
	 *
	 * @return string The formatted payment method name.
	 */
	private function get_formatted_payment_method(): string {

		$method_type = $this->payment_meta['method_type']->value;
		$parts       = preg_split( '/[-_]/', $method_type );
		$parts       = array_map( 'ucfirst', $parts );

		return implode( ' ', $parts );
	}

	/**
	 * Get coupon info.
	 *
	 * @since 1.8.2.2
	 *
	 * @return string
	 */
	private function get_coupon_info() {

		$coupon_info = ! empty( $this->payment_meta['coupon_info']->value ) ? $this->payment_meta['coupon_info']->value : '';

		/**
		 * Allow modifying coupon info.
		 *
		 * @since 1.8.2.2
		 *
		 * @param string $coupon_info  Coupon info.
		 * @param object $payment      Payment object.
		 * @param array  $payment_meta Payment meta.
		 */
		return apply_filters( 'wpforms_admin_payments_views_single_get_coupon_info', $coupon_info, $this->payment, $this->payment_meta );
	}

	/**
	 * Get coupon value.
	 *
	 * @since 1.8.2.2
	 *
	 * @return string
	 */
	private function get_coupon_value() {

		return ! empty( $this->payment_meta['coupon_value']->value ) ? sprintf( '-%s', $this->payment_meta['coupon_value']->value ) : '';
	}

	/**
	 * Education notice for lite users output.
	 *
	 * @since 1.8.2
	 */
	private function education_details() {

		if ( in_array( wpforms_get_license_type(), [ 'pro', 'elite', 'agency', 'ultimate' ], true ) ) {
			return;
		}

		$dismissed = get_user_meta( get_current_user_id(), 'wpforms_dismissed', true );

		if ( ! empty( $dismissed['edu-single-payment'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render( 'education/admin/payments/single-page' );
	}

	/**
	 * Advanced details output.
	 *
	 * @since 1.8.2
	 */
	private function advanced_details() {

		/**
		 * Allow to modify a single payment page advanced details list.
		 *
		 * @since 1.8.2
		 *
		 * @param array  $list    Advanced details to show.
		 * @param object $payment Payment object.
		 */
		$details_list = (array) apply_filters(
			'wpforms_admin_payments_views_single_advanced_details_list',
			[
				'transaction_id'  => [
					'label' => __( 'Transaction ID', 'wpforms-lite' ),
					'link'  => $this->get_gateway_transaction_link(),
					'value' => $this->payment->transaction_id,
				],
				'subscription_id' => [
					'label' => __( 'Subscription ID', 'wpforms-lite' ),
					'link'  => $this->get_gateway_subscription_link(),
					'value' => $this->payment->subscription_id,
				],
				'customer_id'     => [
					'label' => __( 'Customer ID', 'wpforms-lite' ),
					'link'  => $this->get_gateway_customer_link(),
					'value' => $this->payment->customer_id,
				],
				'customer_ip'     => [
					'label' => __( 'Customer IP Address', 'wpforms-lite' ),
					'value' => ! empty( $this->payment_meta['ip_address']->value ) ? $this->payment_meta['ip_address']->value : false,
				],
				'payment_method'  => [
					'label' => __( 'Payment Method', 'wpforms-lite' ),
					'value' => $this->get_payment_method_details(),
				],
				'coupon_info'     => [
					'label' => __( 'Coupon', 'wpforms-lite' ),
					'value' => $this->get_coupon_info(),
				],
			],
			$this->payment
		);

		// Skip empty details.
		$details_list = array_filter(
			$details_list,
			static function ( $item ) {

				return ! empty( $item['value'] );
			}
		);

		// Return early if there are no details.
		if ( empty( $details_list ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render(
			'admin/payments/single/advanced-details',
			[
				'details_list' => $details_list,
			],
			true
		);
	}

	/**
	 * Entry details output.
	 *
	 * @since 1.8.2
	 */
	private function entry_details() { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		$entry_id_title = '';
		$fields         = '';
		$entry_status   = '';

		// Grab submitted values from the entry if it exists.
		if ( ! empty( $this->payment->entry_id ) && wpforms()->is_pro() ) {
			$entry = wpforms()->obj( 'entry' )->get( $this->payment->entry_id );

			if ( $entry ) {
				$fields          = wpforms_decode( $entry->fields );
				$entry_id_title .= "#{$this->payment->entry_id}";
				$entry_status    = $entry->status;
			}
		}

		// Otherwise, grab submitted values from the payment meta if it exists.
		if ( empty( $fields ) && ! empty( $this->payment_meta['fields'] ) ) {
			$fields = wpforms_decode( $this->payment_meta['fields']->value );
		}

		// Bail early if there are submitted values.
		if ( empty( $fields ) ) {
			return;
		}

		/**
		 * Allow modifying the form data before rendering the entry details.
		 *
		 * @since 1.8.9
		 *
		 * @param array $form_data Form data.
		 * @param array $fields    Entry fields.
		 */
		$form_data = apply_filters(
			'wpforms_admin_payments_views_single_form_data',
			wpforms()->obj( 'form' )->get( $this->payment->form_id, [ 'content_only' => true ] ),
			$fields
		);

		add_filter( 'wp_kses_allowed_html', [ $this, 'modify_allowed_tags_payment_field_value' ], 10, 2 );

		/**
		 * Allow modifying the entry fields before rendering the entry details.
		 *
		 * @since 1.8.9
		 *
		 * @param array $entry_fields Entry fields.
		 * @param array $form_data    Form data.
		 */
		$entry_fields = apply_filters(
			'wpforms_admin_payments_views_single_fields',
			$this->prepare_entry_fields( $fields, $form_data ),
			$form_data
		);

		$entry_output = wpforms_render(
			'admin/payments/single/entry-details',
			[
				'entry_fields'   => $entry_fields,
				'form_data'      => $form_data,
				'entry_id_title' => $entry_id_title,
				'entry_id'       => $this->payment->entry_id,
				'entry_status'   => $entry_status,
				'entry_url'      => add_query_arg(
					[
						'page'     => 'wpforms-entries',
						'view'     => 'details',
						'entry_id' => $this->payment->entry_id,
					],
					admin_url( 'admin.php' )
				),
			],
			true
		);

		remove_filter( 'wp_kses_allowed_html', [ $this, 'modify_allowed_tags_payment_field_value' ] );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $entry_output;
	}

	/**
	 * Prepare entry fields.
	 *
	 * @since 1.8.2
	 *
	 * @param array $fields    Entry fields.
	 * @param array $form_data Form data.
	 *
	 * @return array
	 */
	private function prepare_entry_fields( $fields, $form_data ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( empty( $form_data['fields'] ) || empty( $fields ) ) {
			return [];
		}

		$prepared_fields = [];

		// Display the fields and their values.
		foreach ( $form_data['fields'] as $key => $field_data ) {

			if ( empty( $field_data['type'] ) ) {
				continue;
			}

			$field_type = $field_data['type'];

			// Add repeater and layout fields as is.
			if ( in_array( $field_type, [ 'repeater', 'layout' ], true ) && wpforms()->is_pro() ) {
				$prepared_fields[ $key ] = $field_data;

				continue;
			}

			$field = $fields[ $field_data['id'] ] ?? [];

			if ( empty( $field ) || ! isset( $field['id'] ) ) {
				continue;
			}

			// phpcs:disable WPForms.PHP.ValidateHooks.InvalidHookName
			/** This filter is documented in /src/Pro/Admin/Entries/Edit.php */
			if ( $this->payment->entry_id && ! (bool) apply_filters( "wpforms_pro_admin_entries_edit_is_field_displayable_{$field_type}", true, $field, $form_data ) ) {
				continue;
			}

			$field_value = isset( $field['value'] ) ? $field['value'] : '';
			/** This filter is documented in src/SmartTags/SmartTag/FieldHtmlId.php.*/
			$prepared_fields[ $key ]['field_value'] = apply_filters( 'wpforms_html_field_value', wp_strip_all_tags( $field_value ), $field, $form_data, 'payment-single' );
			// phpcs:enable WPForms.PHP.ValidateHooks.InvalidHookName

			$prepared_fields[ $key ]['field_class'] = sanitize_html_class( 'wpforms-field-' . $field_type );
			$prepared_fields[ $key ]['type']        = $field_type;
			$prepared_fields[ $key ]['id']          = $field_data['id'];
			$prepared_fields[ $key ]['field_name']  = ! empty( $field['name'] )
				? $field['name']
				: sprintf( /* translators: %d - field ID. */
					esc_html__( 'Field ID #%d', 'wpforms-lite' ),
					absint( $field['id'] )
				);

			$is_empty_value    = wpforms_is_empty_string( $field_value );
			$is_empty_quantity = isset( $field['quantity'] ) && ! $field['quantity'];

			if ( $is_empty_value ) {
				$prepared_fields[ $key ]['field_value'] = esc_html__( 'Empty', 'wpforms-lite' );
			}

			if ( $is_empty_value || $is_empty_quantity ) {
				$prepared_fields[ $key ]['field_class'] .= ' empty';
			}
		}

		return $prepared_fields;
	}

	/**
	 * Allow additional tags for the wp_kses_post function.
	 *
	 * @since 1.8.2
	 *
	 * @param array  $allowed_html List of allowed HTML.
	 * @param string $context      Context name.
	 *
	 * @return array
	 */
	public function modify_allowed_tags_payment_field_value( $allowed_html, $context ) {

		if ( $context !== 'post' ) {
			return $allowed_html;
		}

		$allowed_html['iframe'] = [
			'data-src' => [],
			'class'    => [],
		];

		return $allowed_html;
	}

	/**
	 * Details metabox output.
	 *
	 * @since 1.8.2
	 */
	private function details() {

		$form_edit_link = $this->get_form_edit_link();
		$date           = sprintf( /* translators: %1$s - date, %2$s - time when item was created, e.g. "Oct 22, 2022 at 11:11 am". */
			__( '%1$s at %2$s', 'wpforms-lite' ),
			wpforms_date_format( $this->payment->date_created_gmt, 'M j, Y', true ),
			wpforms_time_format( $this->payment->date_created_gmt, '', true )
		);

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render(
			'admin/payments/single/details',
			[
				'payment'        => $this->payment,
				'submitted'      => $date,
				'gateway_name'   => $this->get_gateway_name(),
				'gateway_link'   => $this->get_gateway_dashboard_link(),
				'form_edit_link' => ! empty( $form_edit_link ) ? $form_edit_link : Helpers::get_placeholder_na_text(),
				'test_mode'      => $this->payment->mode === 'test',
				'delete_link'    => wp_nonce_url(
					add_query_arg(
						[
							'page'       => 'wpforms-payments',
							'action'     => 'delete',
							'payment_id' => $this->payment->id,
						],
						admin_url( 'admin.php' )
					),
					'bulk-wpforms_page_wpforms-payments'
				),
			],
			true
		);
	}

	/**
	 * Logs metabox output.
	 *
	 * @since 1.8.2
	 */
	private function log() {

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render(
			'admin/payments/single/log',
			[
				'logs' => wpforms()->obj( 'payment_meta' )->get_all_by( 'log', $this->payment->id ),
			],
			true
		);
	}

	// TODO: Remove hardcoded values in methods below after all payment addons updated to use new filters.
	/**
	 * Get gateway transaction link.
	 *
	 * @since 1.8.2
	 *
	 * @return string
	 */
	private function get_gateway_transaction_link() {

		/**
		 * Allow to modify a single payment page gateway transaction link.
		 *
		 * @since 1.8.2
		 *
		 * @param string $link    Gateway transaction link.
		 * @param object $payment Payment object.
		 */
		$link = apply_filters( 'wpforms_admin_payments_views_single_gateway_transaction_link', '', $this->payment );

		if ( $link ) {
			return $link;
		}

		if ( ! $this->payment->transaction_id ) {
			return '';
		}

		switch ( $this->payment->gateway ) {
			case 'stripe':
				$link = 'payments/';
				break;

			case 'paypal_standard':
			case 'paypal_commerce':
				$link = 'activity/payment/';
				break;

			case 'square':
				$link = 'sales/transactions/';
				break;

			default:
				$link = '';
				break;
		}

		if ( ! $link ) {
			return $this->get_gateway_dashboard_link();
		}

		return $this->get_gateway_dashboard_link() . $link . $this->payment->transaction_id;
	}

	/**
	 * Get gateway subscription link.
	 *
	 * @since 1.8.2
	 *
	 * @return string
	 */
	private function get_gateway_subscription_link() {

		/**
		 * Allow to modify a single payment page gateway subscription link.
		 *
		 * @since 1.8.2
		 *
		 * @param string $link    Gateway subscription link.
		 * @param object $payment Payment object.
		 */
		$link = apply_filters( 'wpforms_admin_payments_views_single_gateway_subscription_link', '', $this->payment );

		if ( $link ) {
			return $link;
		}

		if ( $this->payment->gateway === 'paypal_commerce' ) {
			return $this->get_paypal_subscription_link();
		}

		switch ( $this->payment->gateway ) {
			case 'square':
			case 'stripe':
				$link = 'subscriptions/';
				break;

			default:
				$link = '';
				break;
		}

		if ( ! $link ) {
			return $this->get_gateway_dashboard_link();
		}

		return $this->get_gateway_dashboard_link() . $link . $this->payment->subscription_id;
	}

	/**
	 * Generates the PayPal subscription link based on payment metadata.
	 *
	 * @since 1.10.0
	 *
	 * @return string PayPal subscription link.
	 */
	private function get_paypal_subscription_link(): string {

		$dashboard = $this->get_gateway_dashboard_link();

		if ( ! isset( $this->payment_meta['processor_type'] ) ) {
			return $dashboard . 'billing/subscriptions/' . $this->payment->subscription_id;
		}

		$url = $dashboard . 'unifiedtransactions/';

		if ( empty( $this->payment_meta['payer_email'] ) ) {
			return $url;
		}

		return add_query_arg(
			[
				'filter' => '1',
				'query'  => rawurlencode( $this->payment_meta['payer_email']->value ),
			],
			$url
		);
	}

	/**
	 * Get gateway customer link.
	 *
	 * @since 1.8.2
	 *
	 * @return string
	 */
	private function get_gateway_customer_link() {

		/**
		 * Allow to modify a single payment page gateway customer link.
		 *
		 * @since 1.8.2
		 *
		 * @param string $link    Gateway customer link.
		 * @param object $payment Payment object.
		 */
		$link = apply_filters( 'wpforms_admin_payments_views_single_gateway_customer_link', '', $this->payment );

		if ( $link ) {
			return $link;
		}

		if ( in_array( $this->payment->gateway, [ 'paypal_commerce', 'paypal_standard' ], true ) ) {
			return $this->get_gateway_dashboard_link() . 'unifiedtransactions/customers/';
		}

		switch ( $this->payment->gateway ) {
			case 'stripe':
				$link = 'customers/';
				break;

			case 'square':
				$link = 'customers/directory/customer/';
				break;

			default:
				$link = '';
				break;
		}

		if ( ! $link ) {
			return $this->get_gateway_dashboard_link();
		}

		return $this->get_gateway_dashboard_link() . $link . $this->payment->customer_id;
	}

	/**
	 * Get gateway dashboard link.
	 *
	 * @since 1.8.2
	 *
	 * @return string
	 */
	private function get_gateway_dashboard_link() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		/**
		 * Allow to modify a single payment page gateway dashboard link.
		 *
		 * @since 1.8.2
		 *
		 * @param string $link    Gateway dashboard link.
		 * @param object $payment Payment object.
		 */
		$link = apply_filters( 'wpforms_admin_payments_views_single_gateway_dashboard_link', '', $this->payment );

		if ( $link ) {
			return $link;
		}

		$is_test_mode = $this->payment->mode === 'test';

		// Backward compatibility until all addons has been updated.
		switch ( $this->payment->gateway ) {
			case 'stripe':
				$link = $is_test_mode ? 'https://dashboard.stripe.com/test/' : 'https://dashboard.stripe.com/';
				break;

			case 'paypal_standard':
			case 'paypal_commerce':
				$link = $is_test_mode ? 'https://www.sandbox.paypal.com/' : 'https://www.paypal.com/';
				break;

			case 'authorize_net':
				$link = $is_test_mode ? 'https://sandbox.authorize.net/' : 'https://account.authorize.net/';
				break;

			case 'square':
				$link = $is_test_mode ? 'https://squareupsandbox.com/dashboard/' : 'https://squareup.com/t/cmtp_performance/pr_developers/d_partnerships/p_0010L00001tJz7nQAC/?route=dashboard/';
				break;

			default:
				$link = '';
				break;
		}

		return $link;
	}

	/**
	 * Get gateway action link.
	 *
	 * @since 1.8.2
	 *
	 * @param string $action Action.
	 *
	 * @return string
	 */
	private function get_gateway_action_link( $action ) {

		/**
		 * Allow to modify a single payment page gateway action link.
		 *
		 * @since 1.8.2
		 *
		 * @param string $link    Gateway action link.
		 * @param string $action  Action to perform.
		 * @param object $payment Payment object.
		 */
		$link = apply_filters( 'wpforms_admin_payments_views_single_gateway_action_link', '', $action, $this->payment );

		if ( $link ) {
			return $link;
		}

		// Backward compatibility until all addons has been updated.
		if ( $action === 'refund' ) {
			return $this->get_gateway_transaction_link();
		}

		return $this->get_gateway_subscription_link();
	}

	/**
	 * Retrieve a readable payment gateway name.
	 *
	 * @since 1.8.2
	 *
	 * @return string
	 */
	private function get_gateway_name() {

		$gateway_name = Helpers::get_placeholder_na_text( false );

		if ( isset( $this->payment->gateway ) && ValueValidator::is_valid( $this->payment->gateway, 'gateway' ) ) {
			$gateway_name = ValueValidator::get_allowed_gateways()[ $this->payment->gateway ];
		}

		return $gateway_name;
	}

	/**
	 * Retrieve a readable payment status label.
	 *
	 * @since 1.8.4
	 *
	 * @return string
	 */
	private function get_status_label() {

		$label = ValueValidator::get_allowed_one_time_statuses()[ $this->payment->status ];

		if ( $this->payment->status !== 'partrefund' ) {
			return $label;
		}

		$refunded_amount = isset( $this->payment_meta['refunded_amount']->value ) ? wpforms_sanitize_amount( $this->payment_meta['refunded_amount']->value, $this->payment->currency ) : 0;

		$label .= ' <span>(';
		$label .= wpforms_format_amount( $refunded_amount, true, $this->payment->currency );
		$label .= ')</span>';

		return $label;
	}

	/**
	 * If the form is still available, return a link to edit it.
	 * Otherwise, return an empty string.
	 *
	 * @since 1.8.4
	 *
	 * @return string
	 */
	private function get_form_edit_link() {

		// Leave early if no form ID is found.
		if ( ! $this->payment->form_id ) {
			return '';
		}

		$form = wpforms()->obj( 'form' )->get( $this->payment->form_id );

		// Leave early if form is no longer available.
		if ( ! $form || $form->post_status !== 'publish' ) {
			return '';
		}

		$name = ! empty( $form->post_title ) ? $form->post_title : $form->post_name;
		$url  = add_query_arg(
			[
				'view'    => 'fields',
				'page'    => 'wpforms-builder',
				'form_id' => $this->payment->form_id,
			],
			admin_url( 'admin.php' )
		);

		return sprintf( '<a href="%1$s" class="wpforms-link">%2$s</a>', esc_url( $url ), wp_kses_post( $name ) );
	}
}
