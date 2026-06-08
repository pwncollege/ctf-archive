<?php

namespace WPForms\Integrations\Stripe\Admin\Builder\Traits;

use WPForms\Integrations\Stripe\Helpers;
use WPForms\Integrations\Stripe\Admin\Notices;

/**
 * Payment builder settings content trait.
 *
 * @since 1.8.2
 */
trait ContentTrait {

	/**
	 * Display content inside the panel content area.
	 *
	 * @since 1.8.2
	 */
	public function builder_content() {

		if ( $this->builder_alerts() ) {
			return;
		}

		$hide_class = ! Helpers::has_stripe_field( $this->form_data ) ? 'wpforms-hidden' : '';

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo Notices::get_fee_notice( $hide_class );

		if ( Helpers::is_legacy_payment_settings( $this->form_data ) ) {
			$this->legacy_builder_content();

			return;
		}

		$this->maybe_convert_legacy_settings();

		echo '<div id="wpforms-panel-content-section-payment-stripe" class="' . esc_attr( $hide_class ) . '">';

		if ( ! Helpers::is_pro() ) {
			$this->builder_content_one_time();
			$this->builder_content_recurring();
		} else {
			parent::builder_content();
		}

		echo '</div>';
	}

	/**
	 * Convert legacy settings if they exist.
	 *
	 * @since 1.8.4
	 */
	private function maybe_convert_legacy_settings() {

		// Enable one-time payments if they were active.
		if ( ! empty( $this->form_data['payments']['stripe']['enable'] ) ) {
			unset( $this->form_data['payments']['stripe']['enable'] );

			$this->form_data['payments']['stripe']['enable_one_time'] = 1;
		}

		// Convert subscription settings if they exist and disabled to new default plan.
		if (
			empty( $this->form_data['payments']['stripe']['recurring'] ) ||
			! empty( $this->form_data['payments']['stripe']['enable_recurring'] )
		) {
			return;
		}

		$stripe_recurring_settings = $this->form_data['payments']['stripe']['recurring'];

		unset( $this->form_data['payments']['stripe']['recurring'] );

		if (
			! empty( $stripe_recurring_settings['enable'] ) ||
			array_filter( $stripe_recurring_settings, 'is_array' ) === $stripe_recurring_settings
		) {
			return;
		}

		// Preserve all settings (name, period, email, and CL).
		$this->form_data['payments']['stripe']['recurring'][] = $stripe_recurring_settings;
	}

	/**
	 * Display legacy content inside the panel content area.
	 *
	 * @since 1.8.4
	 */
	private function legacy_builder_content() {

		$this->enable_payments_toggle();

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->content_section_body();
	}

	/**
	 * Builder content for one time payments.
	 *
	 * @since 1.8.4
	 */
	private function builder_content_one_time() {
		?>

		<div class="wpforms-panel-content-section-payment">
			<h2 class="wpforms-panel-content-section-payment-subtitle">
				<?php esc_html_e( 'One-Time Payments', 'wpforms-lite' ); ?>
			</h2>
			<?php
			wpforms_panel_field(
				'toggle',
				$this->slug,
				'enable_one_time',
				$this->form_data,
				esc_html__( 'Enable one-time payments', 'wpforms-lite' ),
				[
					'parent'  => 'payments',
					'default' => '0',
					'tooltip' => esc_html__( 'Allow your customers to one-time pay via the form.', 'wpforms-lite' ),
					'class'   => 'wpforms-panel-content-section-payment-toggle wpforms-panel-content-section-payment-toggle-one-time',
				]
			);
			?>
			<div class="wpforms-panel-content-section-payment-one-time wpforms-panel-content-section-payment-toggled-body">
				<?php echo $this->get_builder_content_one_time_content(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Builder content for recurring payments.
	 *
	 * @since 1.8.4
	 */
	private function builder_content_recurring() {
		?>

		<div class="wpforms-panel-content-section-payment">
			<h2 class="wpforms-panel-content-section-payment-subtitle">
				<?php esc_html_e( 'Recurring Payments ', 'wpforms-lite' ); ?>
			</h2>
			<?php

			$this->add_plan_education();

			wpforms_panel_field(
				'toggle',
				$this->slug,
				'enable_recurring',
				$this->form_data,
				esc_html__( 'Enable recurring subscription payments', 'wpforms-lite' ),
				[
					'parent'  => 'payments',
					'default' => '0',
					'tooltip' => esc_html__( 'Allow your customer to pay recurringly via the form.', 'wpforms-lite' ),
					'class'   => 'wpforms-panel-content-section-payment-toggle wpforms-panel-content-section-payment-toggle-recurring',
				]
			);
			?>
			<div class="wpforms-panel-content-section-payment-recurring wpforms-panel-content-section-payment-toggled-body">
				<?php

				if ( empty( $this->form_data['payments'][ $this->slug ]['recurring'] ) ) {
					$this->form_data['payments'][ $this->slug ]['recurring'][] = [];
				}

				foreach ( $this->form_data['payments'][ $this->slug ]['recurring'] as $plan_id => $plan_settings ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo wpforms_render(
						'builder/payment/recurring/item',
						[
							'plan_id' => $plan_id,
							'content' => $this->get_builder_content_recurring_payment_content( $plan_id ),
						],
						true
					);

					// Limit plans if Stripe addon is NOT active.
					break;
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Add new plan education modals.
	 *
	 * @since 1.8.4
	 */
	private function add_plan_education() {

		$label = __( 'Add New Plan', 'wpforms-lite' );

		if ( Helpers::is_allowed_license_type() ) {
			$addon = wpforms()->obj( 'addons' )->get_addon( 'wpforms-stripe' );

			if ( empty( $addon ) ) {
				return;
			}

			echo '<a
				href="#"
				class="wpforms-panel-content-section-payment-button wpforms-panel-content-section-payment-button-add-plan education-modal"
				data-action="' . esc_attr( $addon['action'] ) . '"
				data-path="' . esc_attr( $addon['path'] ) . '"
				data-slug="' . esc_attr( $addon['slug'] ) . '"
				data-url="' . esc_url( $addon['url'] ) . '"
				data-nonce="' . esc_attr( wp_create_nonce( 'wpforms-admin' ) ) . '"
				data-name="' . esc_attr__( 'Stripe Pro', 'wpforms-lite' ) . '"
			>' . esc_html( $label ) . '</a>';

			return;
		}

		echo '<a
				href="#"
				class="wpforms-panel-content-section-payment-button wpforms-panel-content-section-payment-button-add-plan education-modal"
				data-action="upgrade"
				data-name="' . esc_attr__( 'Multiple Subscriptions', 'wpforms-lite' ) . '"
			>' . esc_html( $label ) . '</a>';
	}

	/**
	 * Display alert if Stripe keys are not set.
	 *
	 * @since 1.8.2
	 *
	 * @return bool
	 */
	private function builder_alerts() {

		if ( Helpers::has_stripe_keys() ) {
			if ( Helpers::is_legacy_payment_settings( $this->form_data ) ) {
				Notices::prompt_new_interface();
			}

			$this->stripe_credit_card_alert();

			return false;
		}

		$this->alert_content(
			__( 'Heads up! Stripe payments can\'t be enabled yet.', 'wpforms-lite' ),
			sprintf(
				wp_kses( /* translators: %1$s - admin area Payments settings page URL. */
					__( 'First, please connect to your Stripe account on the <a href="%1$s" class="secondary-text">WPForms Settings</a> page.', 'wpforms-lite' ),
					[
						'a' => [
							'href'  => [],
							'class' => [],
						],
					]
				),
				esc_url( admin_url( 'admin.php?page=wpforms-settings&view=payments' ) )
			)
		);

		return true;
	}

	/**
	 * Display alert if Stripe Credit Card field is not added to the form.
	 *
	 * @since 1.8.2
	 */
	private function stripe_credit_card_alert() {

		$hide_class = Helpers::has_stripe_field( $this->form_data ) ? 'wpforms-hidden' : '';
		?>

		<div id="wpforms-stripe-credit-card-alert" class="wpforms-alert wpforms-alert-info <?php echo esc_attr( $hide_class ); ?>">
			<?php $this->alert_content( '', esc_html__( 'To use Stripe, first add the Stripe payment field to your form.', 'wpforms-lite' ) ); ?>
		</div>

	<?php
	}

	/**
	 * Display toggle to enable Stripe payments.
	 *
	 * @since 1.8.2
	 */
	private function enable_payments_toggle() {

		wpforms_panel_field(
			'toggle',
			'stripe',
			'enable',
			$this->form_data,
			esc_html__( 'Enable Stripe payments', 'wpforms-lite' ),
			[
				'parent'  => 'payments',
				'default' => '0',
			]
		);
	}

	/**
	 * Display content inside the panel content section.
	 *
	 * @since 1.8.4
	 *
	 * @return string Stripe settings builder content section.
	 */
	private function content_section_body() {

		$content  = '<div class="wpforms-panel-content-section-stripe-body">';
		$content .= $this->get_builder_content_one_time_content();
		$content .= sprintf( '<h2>%1$s</h2>', esc_html__( 'Subscriptions', 'wpforms-lite' ) );

		$content .= wpforms_panel_field(
			'toggle',
			'stripe',
			'enable',
			$this->form_data,
			esc_html__( 'Enable recurring subscription payments', 'wpforms-lite' ),
			[
				'parent'     => 'payments',
				'subsection' => 'recurring',
				'default'    => '0',
			],
			false
		);

		$content .= $this->get_builder_content_recurring_payment_content( '' );
		$content .= '</div>';

		return $content;
	}

	/**
	 * Get content inside the one time payment area.
	 *
	 * @since 1.8.4
	 *
	 * @return string
	 */
	protected function get_builder_content_one_time_content() {

		$content = wpforms_panel_field(
			'text',
			$this->slug,
			'payment_description',
			$this->form_data,
			esc_html__( 'Payment Description', 'wpforms-lite' ),
			[
				'parent'  => 'payments',
				'tooltip' => esc_html__( 'Enter your payment description. Eg: Donation for the soccer team. Only used for standard one-time payments.', 'wpforms-lite' ),
			],
			false
		);

		$content .= wpforms_panel_field(
			'select',
			$this->slug,
			'receipt_email',
			$this->form_data,
			esc_html__( 'Stripe Payment Receipt', 'wpforms-lite' ),
			[
				'parent'      => 'payments',
				'field_map'   => [ 'email' ],
				'placeholder' => esc_html__( '--- Select Email ---', 'wpforms-lite' ),
				'tooltip'     => esc_html__( 'If you would like to have Stripe send a receipt after payment, select the email field to use. This is optional but recommended. Only used for standard one-time payments.', 'wpforms-lite' ),
			],
			false
		);

		$content .= wpforms_panel_field(
			'select',
			$this->slug,
			'customer_email',
			$this->form_data,
			esc_html__( 'Customer Email', 'wpforms-lite' ),
			[
				'parent'      => 'payments',
				'field_map'   => [ 'email' ],
				'placeholder' => esc_html__( '--- Select Email ---', 'wpforms-lite' ),
				'tooltip'     => esc_html__( 'Select the field that contains the customer\'s email address. This is optional but recommended.', 'wpforms-lite' ),
			],
			false
		);

		$content .= $this->get_customer_name_panel_field();
		$content .= $this->get_customer_phone_field();
		$content .= $this->get_address_panel_fields();
		$content .= $this->get_custom_metadata_table();
		$content .= $this->single_payments_conditional_logic_section();

		return $content;
	}

	/**
	 * Get content inside the recurring payment area.
	 *
	 * @since 1.8.4
	 *
	 * @param string $plan_id Plan id.
	 *
	 * @return string
	 */
	protected function get_builder_content_recurring_payment_content( $plan_id ) {

		$content = wpforms_panel_field(
			'text',
			$this->slug,
			'name',
			$this->form_data,
			esc_html__( 'Plan Name', 'wpforms-lite' ),
			[
				'parent'     => 'payments',
				'subsection' => 'recurring',
				'index'      => $plan_id,
				'tooltip'    => esc_html__( 'Enter the subscription name. Eg: Email Newsletter. Subscription period and price are automatically appended. If left empty the form name will be used.', 'wpforms-lite' ),
				'class'      => 'wpforms-panel-content-section-payment-plan-name',
			],
			false
		);

		$content .= wpforms_panel_field(
			'select',
			$this->slug,
			'period',
			$this->form_data,
			esc_html__( 'Recurring Period', 'wpforms-lite' ),
			[
				'parent'     => 'payments',
				'subsection' => 'recurring',
				'index'      => $plan_id,
				'default'    => 'yearly',
				'options'    => [
					'daily'      => esc_html__( 'Daily', 'wpforms-lite' ),
					'weekly'     => esc_html__( 'Weekly', 'wpforms-lite' ),
					'monthly'    => esc_html__( 'Monthly', 'wpforms-lite' ),
					'quarterly'  => esc_html__( 'Quarterly', 'wpforms-lite' ),
					'semiyearly' => esc_html__( 'Semi-Yearly', 'wpforms-lite' ),
					'yearly'     => esc_html__( 'Yearly', 'wpforms-lite' ),
				],
				'tooltip'    => esc_html__( 'How often you would like the charge to recur.', 'wpforms-lite' ),
				'class'      => 'wpforms-panel-content-section-payment-plan-period',
			],
			false
		);

		$max_cycles   = $this->get_recurring_max_cycles( $plan_id );
		$range_cycles = range( 1, $max_cycles );

		$content .= wpforms_panel_field(
			'select',
			$this->slug,
			'cycles',
			$this->form_data,
			esc_html__( 'Recurring Cycles', 'wpforms-lite' ),
			[
				'parent'     => 'payments',
				'subsection' => 'recurring',
				'index'      => $plan_id,
				'default'    => 'unlimited',
				'options'    => [ 'unlimited' => esc_html__( 'Unlimited', 'wpforms-lite' ) ] + array_combine( $range_cycles, $range_cycles ),
				'tooltip'    => esc_html__( 'How many times you want the payment to repeat. Stripe supports up to 100 recurrences or a maximum duration of 20 years, whichever comes first.', 'wpforms-lite' ),
				'class'      => 'wpforms-panel-content-section-payment-plan-cycles',
			],
			false
		);

		$is_empty_email = isset( $this->form_data['payments'][ $this->slug ]['recurring'][ $plan_id ]['email'] ) && empty( $this->form_data['payments'][ $this->slug ]['recurring'][ $plan_id ]['email'] );

		$content .= wpforms_panel_field(
			'select',
			$this->slug,
			'email',
			$this->form_data,
			esc_html__( 'Customer Email', 'wpforms-lite' ),
			[
				'parent'      => 'payments',
				'subsection'  => 'recurring',
				'index'       => $plan_id,
				'input_class' => $is_empty_email ? 'wpforms-required-field-error' : '',
				'field_map'   => [ 'email' ],
				'placeholder' => esc_html__( '--- Select Email ---', 'wpforms-lite' ),
				'tooltip'     => esc_html__( "Select the field that contains the customer's email address. This field is required.", 'wpforms-lite' ),
			],
			false
		);

		$content .= $this->get_customer_name_panel_field( $plan_id );
		$content .= $this->get_customer_phone_field( $plan_id );
		$content .= $this->get_address_panel_fields( $plan_id );
		$content .= $this->get_custom_metadata_table( $plan_id );
		$content .= $this->recurring_payments_conditional_logic_section( $plan_id );

		return $content;
	}

	/**
	 * Alert icon.
	 *
	 * @since 1.8.4
	 */
	private function alert_icon() {

		printf(
			'<img src="%1$s" class="wpforms-builder-payment-settings-alert-icon" alt="%2$s">',
			esc_url( WPFORMS_PLUGIN_URL . 'assets/images/addon-icon-stripe.png' ),
			esc_attr__( 'Connect WPForms to Stripe.', 'wpforms-lite' )
		);
	}

	/**
	 * Learn more link.
	 *
	 * @since 1.8.4
	 *
	 * @return string
	 */
	private function learn_more_link() {

		return sprintf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer" class="secondary-text">%2$s</a>',
			esc_url( wpforms_utm_link( 'https://wpforms.com/docs/how-to-install-and-use-the-stripe-addon-with-wpforms/', 'builder-payments', 'Stripe Documentation' ) ),
			esc_html__( 'Learn more about our Stripe integration.', 'wpforms-lite' )
		);
	}

	/**
	 * Get Customer name panel field.
	 *
	 * @since 1.8.6
	 *
	 * @param string|null $plan_id Plan ID.
	 *
	 * @return string
	 */
	private function get_customer_name_panel_field( $plan_id = null ) {

		$args = [
			'parent'      => 'payments',
			'field_map'   => [ 'name' ],
			'placeholder' => esc_html__( '--- Select Name ---', 'wpforms-lite' ),
			'tooltip'     => esc_html__( 'Select the field that contains the customer\'s name. This is optional but recommended.', 'wpforms-lite' ),
		];

		if ( ! is_null( $plan_id ) ) {
			$args['subsection'] = 'recurring';
			$args['index']      = $plan_id;
		}

		return wpforms_panel_field(
			'select',
			$this->slug,
			'customer_name',
			$this->form_data,
			esc_html__( 'Customer Name', 'wpforms-lite' ),
			$args,
			false
		);
	}

	/**
	 * Get address panel fields.
	 *
	 * @since 1.8.8
	 *
	 * @param string|null $plan_id Plan ID.
	 *
	 * @return string
	 */
	private function get_address_panel_fields( $plan_id = null ): string {

		$args = [
			'parent'      => 'payments',
			'field_map'   => [ 'address' ],
			'placeholder' => esc_html__( '--- Select Address ---', 'wpforms-lite' ),
		];

		$is_subscription = ! is_null( $plan_id );

		if ( $is_subscription ) {
			$args['subsection'] = 'recurring';
			$args['index']      = $plan_id;
		}

		$is_pro = wpforms()->is_pro();

		if ( ! $is_pro ) {
			$args['pro_badge']   = true;
			$args['data']        = [
				'action'      => 'upgrade',
				'name'        => esc_html__( 'Customer Address', 'wpforms-lite' ),
				'utm-content' => 'Builder Stripe Address Field',
				'licence'     => 'pro',
			];
			$args['input_class'] = 'education-modal';
			$args['readonly']    = true;
		} else {
			$args['tooltip'] = esc_html__( 'Select the field that contains the customer\'s address. This is optional but required for some regions.', 'wpforms-lite' );
		}

		$output = wpforms_panel_field(
			'select',
			$this->slug,
			'customer_address',
			$this->form_data,
			esc_html__( 'Customer Address', 'wpforms-lite' ),
			$args,
			false
		);

		if ( $is_subscription ) {
			return $output;
		}

		if ( ! $is_pro ) {
			$args['data']['name'] = esc_html__( 'Shipping Address', 'wpforms-lite' );
		} else {
			$args['tooltip'] = esc_html__( 'Select the field that contains the shipping address. This is optional but required for some regions.', 'wpforms-lite' );
		}

		$output .= wpforms_panel_field(
			'select',
			$this->slug,
			'shipping_address',
			$this->form_data,
			esc_html__( 'Shipping Address', 'wpforms-lite' ),
			$args,
			false
		);

		return $output;
	}

	/**
	 * Get the Customer phone panel field.
	 *
	 * @since 1.9.6
	 *
	 * @param string|null $plan_id Plan ID.
	 *
	 * @return string
	 */
	private function get_customer_phone_field( ?string $plan_id = null ): string {

		$args = [
			'parent'      => 'payments',
			'field_map'   => [ 'phone' ],
			'placeholder' => esc_html__( '--- Select Phone ---', 'wpforms-lite' ),
		];

		if ( ! is_null( $plan_id ) ) {
			$args['subsection'] = 'recurring';
			$args['index']      = $plan_id;
		}

		$is_pro = wpforms()->is_pro();

		if ( ! $is_pro ) {
			$args['pro_badge']   = true;
			$args['data']        = [
				'action'      => 'upgrade',
				'name'        => esc_html__( 'Customer Phone', 'wpforms-lite' ),
				'utm-content' => 'Builder Stripe Phone Field',
				'licence'     => 'pro',
			];
			$args['input_class'] = 'education-modal';
			$args['readonly']    = true;
		} else {
			$args['tooltip'] = esc_html__( 'Select the field that contains the customer\'s phone. This is optional but recommended.', 'wpforms-lite' );
		}

		return (string) wpforms_panel_field(
			'select',
			$this->slug,
			'customer_phone',
			$this->form_data,
			esc_html__( 'Customer Phone', 'wpforms-lite' ),
			$args,
			false
		);
	}

	/**
	 * Get custom meta table html.
	 *
	 * @since 1.9.6
	 *
	 * @param string|null $plan_id Plan ID.
	 *
	 * @return string
	 */
	private function get_custom_metadata_table( $plan_id = null ): string {

		$subsection      = ! is_null( $plan_id ) ? 'recurring_custom_metadata_' . $plan_id : 'custom_metadata';
		$custom_metadata = $this->form_data['payments'][ $this->slug ][ $subsection ] ?? [ [] ];

		/**
		 * Filter the allowed fields for custom metadata.
		 *
		 * @since 1.9.6
		 *
		 * @param array $allowed_fields Allowed fields.
		 */
		$allowed_fields = (array) apply_filters( 'wpforms_stripe_custom_metadata_allowed_fields', $this->get_allowed_meta_value_fields() );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		return wpforms_render(
			'integrations/stripe/builder/custom-metadata',
			[
				'custom_metadata' => $custom_metadata,
				'subsection'      => $subsection,
				'slug'            => $this->slug,
				'form_data'       => $this->form_data,
				'fields'          => $allowed_fields,
			],
			true
		);
	}

	/**
	 * Get allowed meta value fields.
	 *
	 * @since 1.9.6
	 *
	 * @return array
	 */
	private function get_allowed_meta_value_fields(): array {

		$fields = [
			'text',
			'textarea',
			'checkbox',
			'radio',
			'select',
			'number',
			'name',
			'email',
			'number-slider',
			'payment-checkbox',
			'payment-multiple',
			'payment-select',
			'payment-single',
			'payment-total',
		];

		if ( ! wpforms()->is_pro() ) {
			return $fields;
		}

		return array_merge(
			$fields,
			[
				'address',
				'date-time',
				'hidden',
				'phone',
				'rating',
			]
		);
	}

	/**
	 * Get recurring max cycles value.
	 *
	 * @param string $plan_id Selected plan id.
	 *
	 * @since 1.9.8
	 *
	 * @return int
	 */
	private function get_recurring_max_cycles( string $plan_id ): int {

		// The API limit is 20 years.
		if ( ! isset( $this->form_data['payments'][ $this->slug ]['recurring'][ $plan_id ]['period'] ) || $this->form_data['payments'][ $this->slug ]['recurring'][ $plan_id ]['period'] === 'yearly' ) {
			return 20;
		}

		// 20 years is 40 semi-years.
		if ( $this->form_data['payments'][ $this->slug ]['recurring'][ $plan_id ]['period'] === 'semiyearly' ) {
			return 40;
		}

		// 20 years is 80 quarters.
		if ( $this->form_data['payments'][ $this->slug ]['recurring'][ $plan_id ]['period'] === 'quarterly' ) {
			return 80;
		}

		return Helpers::recurring_plan_cycles_max();
	}

	/**
	 * Display alert content.
	 *
	 * @since 1.9.9
	 *
	 * @param string $title   Alert title.
	 * @param string $message Alert message.
	 */
	private function alert_content( string $title, string $message ): void {
		?>

		<?php $this->alert_icon(); ?>

		<div class="wpforms-builder-payment-settings-default-content">
			<?php if ( ! empty( $title ) ) : ?>
				<p class="wpforms-builder-payment-settings-error-title">
					<?php echo esc_html( $title ); ?>
				</p>
			<?php endif; ?>
			<p>
				<?php echo $message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</p>
			<p class="wpforms-builder-payment-settings-learn-more">
				<?php echo $this->learn_more_link(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</p>
		</div>

		<?php
	}
}
