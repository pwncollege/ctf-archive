<?php

namespace WPForms\Integrations\PayPalCommerce\Admin\Builder\Traits;

use WPForms\Integrations\PayPalCommerce\Admin\Notices;
use WPForms\Integrations\PayPalCommerce\Connection;
use WPForms\Integrations\PayPalCommerce\Helpers;
use WPForms\Integrations\PayPalCommerce\PayPalCommerce;

/**
 * Payment builder settings content trait.
 *
 * @since 1.10.0
 */
trait Content {

	/**
	 * Display content inside the panel content area.
	 *
	 * @since 1.10.0
	 */
	public function builder_content(): void {

		if ( $this->builder_alerts() ) {
			return;
		}

		$hide_class = ! Helpers::has_paypal_commerce_field( $this->form_data ) || $this->is_paypal_standard_enabled() ? 'wpforms-hidden' : '';

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo Notices::get_fee_notice( $hide_class );

		$this->maybe_convert_legacy_settings();

		echo '<div id="wpforms-panel-content-section-payment-paypal-commerce" class="' . esc_attr( $hide_class ) . '">';

		if ( ! Helpers::is_pro() ) {
			$this->builder_content_one_time();
			$this->builder_content_recurring();
		} else {
			parent::builder_content();
		}

		echo '</div>';
	}

	/**
	 * Check if connection exists and ready to use.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	private function builder_alerts(): bool {

		$connection = Connection::get();

		if ( ! $connection ) {
			?>

			<?php $this->alert_icon(); ?>
			<div class="wpforms-builder-payment-settings-default-content">
				<p><?php esc_html_e( 'Connect to your PayPal account and start receiving payments today.', 'wpforms-lite' ); ?></p>
				<p class="wpforms-builder-payment-settings-learn-more"><?php echo $this->learn_more_link(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
				<?php
				printf(
					'<a href="%s" target="_blank" rel="noopener noreferrer" class="wpforms-btn wpforms-btn-md wpforms-btn-orange wpforms-paypal-commerce-auth">%s</a>',
					esc_url( Helpers::get_settings_page_url() . '#wpforms-setting-row-paypal-commerce-heading' ),
					esc_html__( 'Connect to PayPal', 'wpforms-lite' )
				);
				?>
			</div>
			<?php

			return true;
		}

		$connection = $connection->is_valid() ? $connection->refresh_expired_tokens() : $connection;

		if ( ! $connection->is_usable() ) {

			echo '<p class="wpforms-alert wpforms-alert-info">';
			printf(
				wp_kses( /* translators: %s - the WPForms Payments settings page URL. */
					__( "Heads up! PayPal Commerce payments can't be processed because there's a problem with the connection to PayPal. Please visit the <a href='%2\$s'>WPForms Settings</a> page to resolve the issue before trying again.", 'wpforms-lite' ),
					[
						'a' => [
							'href' => [],
						],
					]
				),
				Helpers::is_sandbox_mode() ? esc_html__( 'Sandbox', 'wpforms-lite' ) : esc_html__( 'Production', 'wpforms-lite' ),
				esc_url( Helpers::get_settings_page_url() . '#wpforms-setting-row-paypal-commerce-heading' )
			);
			echo '</p>';

			return true;
		}

		$this->alert_payment_content();
		$this->alert_paypal_standard();

		return false;
	}

	/**
	 * Display alert payment content inside the panel content area.
	 *
	 * @since 1.10.0
	 */
	private function alert_payment_content(): void {

		$hide_class = Helpers::has_paypal_commerce_field( $this->form_data ) || $this->is_paypal_standard_enabled() ? 'wpforms-hidden' : '';

		?>
		<div id="wpforms-paypal-commerce-credit-card-alert" class="wpforms-alert wpforms-alert-info <?php echo esc_attr( $hide_class ); ?>">

			<?php $this->alert_icon(); ?>
			<div class="wpforms-builder-payment-settings-default-content">
				<p><?php esc_html_e( 'To use PayPal Commerce, first add the PayPal Commerce field to your form.', 'wpforms-lite' ); ?></p>
				<p><?php echo $this->learn_more_link(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Display alert PayPal Standard content inside the panel content area.
	 *
	 * @since 1.10.0
	 */
	private function alert_paypal_standard(): void {

		$hide_class = $this->is_paypal_standard_enabled() ? '' : 'wpforms-hidden';

		?>
		<p id="wpforms-paypal-commerce-paypal-standard-alert" class="wpforms-alert wpforms-alert-warning <?php echo esc_attr( $hide_class ); ?>">
			<?php esc_html_e( 'The PayPal Commerce addon can\'t be activated while PayPal Standard is in use. Please deactivate the PayPal Standard addon and try again.', 'wpforms-lite' ); ?>
		</p>
		<?php
	}

	/**
	 * Check if PayPal standard enabled.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	private function is_paypal_standard_enabled(): bool {

		if ( empty( $this->form_data['payments']['paypal_standard']['enable'] ) ) {
			return false;
		}

		return class_exists( 'WPForms_Paypal_Standard' ) || class_exists( '\WPFormsPaypalStandard\Plugin' );
	}

	/**
	 * Convert legacy settings if they exist.
	 *
	 * @since 1.10.0
	 */
	private function maybe_convert_legacy_settings(): void {

		if ( empty( $this->form_data['payments'][ PayPalCommerce::SLUG ]['enable'] ) ) {
			return;
		}

		// Enable one-time payments if they were active.
		unset( $this->form_data['payments'][ PayPalCommerce::SLUG ]['enable'] );

		$this->form_data['payments'][ PayPalCommerce::SLUG ]['enable_one_time'] = 1;
	}

	/**
	 * Get content inside the one-time payment area.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	protected function get_builder_content_one_time_content(): string {

		$content = wpforms_panel_field(
			'select',
			PayPalCommerce::SLUG,
			'name',
			$this->form_data,
			esc_html__( 'Name', 'wpforms-lite' ),
			[
				'parent'      => 'payments',
				'field_map'   => [ 'name' ],
				'placeholder' => esc_html__( '--- Select a Field ---', 'wpforms-lite' ),
				'tooltip'     => esc_html__( "Select the field that contains the buyer's name. This setting is optional and only used when Card Holder Name is disabled.", 'wpforms-lite' ),
			],
			false
		);

		$content .= $this->get_address_panel_fields();
		$content .= $this->get_shipping_address_name_field();
		$content .= $this->get_shipping_email_field();
		$content .= wpforms_panel_field(
			'text',
			PayPalCommerce::SLUG,
			'payment_description',
			$this->form_data,
			esc_html__( 'Payment Description', 'wpforms-lite' ),
			[
				'parent'  => 'payments',
				'tooltip' => esc_html__( 'Enter your payment description. Eg: Donation for the soccer team.', 'wpforms-lite' ),
			],
			false
		);
		$content .= $this->single_payments_conditional_logic_section();

		return $content;
	}

	/**
	 * Get content inside the recurring payment area.
	 *
	 * @since 1.10.0
	 *
	 * @param string $plan_id Plan id.
	 *
	 * @return string
	 */
	protected function get_builder_content_recurring_payment_content( $plan_id ): string {

		$content = wpforms_panel_field(
			'text',
			PayPalCommerce::SLUG,
			'pp_product_id',
			$this->form_data,
			'',
			[
				'parent'     => 'payments',
				'subsection' => 'recurring',
				'index'      => $plan_id,
				'class'      => 'wpforms-hidden',
			],
			false
		);

		$content .= wpforms_panel_field(
			'text',
			PayPalCommerce::SLUG,
			'pp_plan_id',
			$this->form_data,
			'',
			[
				'parent'     => 'payments',
				'subsection' => 'recurring',
				'index'      => $plan_id,
				'class'      => 'wpforms-hidden',
			],
			false
		);

		$content .= wpforms_panel_field(
			'text',
			PayPalCommerce::SLUG,
			'name',
			$this->form_data,
			esc_html__( 'Plan Name', 'wpforms-lite' ),
			[
				'parent'     => 'payments',
				'subsection' => 'recurring',
				'index'      => $plan_id,
				'tooltip'    => esc_html__( 'Enter a name for the recurring plan. Leave this field blank to use the default name.', 'wpforms-lite' ),
				'class'      => 'wpforms-panel-content-section-payment-plan-name',
			],
			false
		);

		$content .= wpforms_panel_field(
			'select',
			PayPalCommerce::SLUG,
			'product_type',
			$this->form_data,
			esc_html__( 'Product Type', 'wpforms-lite' ),
			[
				'parent'     => 'payments',
				'subsection' => 'recurring',
				'index'      => $plan_id,
				'default'    => 'digital',
				'options'    => [
					'digital'  => esc_html__( 'Digital', 'wpforms-lite' ),
					'physical' => esc_html__( 'Physical', 'wpforms-lite' ),
					'service'  => esc_html__( 'Service', 'wpforms-lite' ),
				],
				'tooltip'    => esc_html__( 'Select the type of product that this subscription is for.', 'wpforms-lite' ),
			],
			false
		);

		$content .= wpforms_panel_field(
			'select',
			PayPalCommerce::SLUG,
			'recurring_times',
			$this->form_data,
			esc_html__( 'Recurring Times', 'wpforms-lite' ),
			[
				'parent'     => 'payments',
				'subsection' => 'recurring',
				'index'      => $plan_id,
				'default'    => 'yearly',
				'options'    => [
					'daily'       => esc_html__( 'Daily', 'wpforms-lite' ),
					'weekly'      => esc_html__( 'Weekly', 'wpforms-lite' ),
					'monthly'     => esc_html__( 'Monthly', 'wpforms-lite' ),
					'quarterly'   => esc_html__( 'Quarterly', 'wpforms-lite' ),
					'semi-yearly' => esc_html__( 'Semi-Yearly', 'wpforms-lite' ),
					'yearly'      => esc_html__( 'Yearly', 'wpforms-lite' ),
				],
				'tooltip'    => esc_html__( 'Select how often you would like the charge to recur.', 'wpforms-lite' ),
			],
			false
		);

		$content .= wpforms_panel_field(
			'select',
			PayPalCommerce::SLUG,
			'total_cycles',
			$this->form_data,
			esc_html__( 'Total Cycles', 'wpforms-lite' ),
			[
				'parent'     => 'payments',
				'subsection' => 'recurring',
				'index'      => $plan_id,
				'default'    => '0',
				'options'    => array_merge( [ '0' => esc_html__( 'Infinite', 'wpforms-lite' ) ], range( 1, 99 ) ),
				'tooltip'    => esc_html__( 'Select how often you would like the charge to recur.', 'wpforms-lite' ),
			],
			false
		);

		$content .= $this->get_address_panel_fields( $plan_id );
		$content .= $this->get_shipping_address_name_field( $plan_id );

		$content .= wpforms_panel_field(
			'toggle',
			PayPalCommerce::SLUG,
			'bill_retry',
			$this->form_data,
			esc_html__( 'Try to bill the customer again if the payment fails on the first attempt.', 'wpforms-lite' ),
			[
				'parent'     => 'payments',
				'subsection' => 'recurring',
				'class'      => 'wpforms-builder-payment-settings-recurring-bill-retry',
				'index'      => $plan_id,
			],
			false
		);

		$content .= $this->recurring_payments_conditional_logic_section( $plan_id );

		return $content;
	}

	/**
	 * Get the shipping address name field.
	 *
	 * @since 1.10.0
	 *
	 * @param string|null $plan_id Plan id.
	 *
	 * @return string
	 */
	private function get_shipping_address_name_field( $plan_id = null ): string {

		$is_pro = wpforms()->is_pro();
		$label  = esc_html__( 'Shipping Name', 'wpforms-lite' );

		$args = [
			'parent'      => 'payments',
			'field_map'   => [ 'name' ],
			'placeholder' => esc_html__( '--- Select a Field ---', 'wpforms-lite' ),
		];

		// Add the subsection and index for recurring payments.
		if ( ! is_null( $plan_id ) ) {
			$args['subsection'] = 'recurring';
			$args['index']      = $plan_id;
		}

		// Configure Pro-specific settings or education modal.
		if ( $is_pro ) {
			$args['tooltip'] = esc_html__( "Select the field that contains the buyer's shipping name. This setting is optional.", 'wpforms-lite' );
		} else {
			$args['pro_badge']   = true;
			$args['input_class'] = 'education-modal';
			$args['readonly']    = true;
			$args['data']        = [
				'action'      => 'upgrade',
				'name'        => $label,
				'utm-content' => 'Builder PayPal Commerce Name Field',
				'licence'     => 'pro',
			];
		}

		return wpforms_panel_field(
			'select',
			PayPalCommerce::SLUG,
			'shipping_name',
			$this->form_data,
			esc_html__( 'Shipping Name', 'wpforms-lite' ),
			$args,
			false
		);
	}

	/**
	 * Get the shipping email field.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	private function get_shipping_email_field(): string {

		$is_pro = wpforms()->is_pro();
		$label  = esc_html__( 'Shipping Email', 'wpforms-lite' );

		$args = [
			'parent'      => 'payments',
			'field_map'   => [ 'email' ],
			'placeholder' => esc_html__( '--- Select a Field ---', 'wpforms-lite' ),
		];

		// Configure Pro-specific settings or education modal.
		if ( $is_pro ) {
			$args['tooltip'] = esc_html__( "Select the field that contains the buyer's shipping email. This setting is optional.", 'wpforms-lite' );
		} else {
			$args['pro_badge']   = true;
			$args['input_class'] = 'education-modal';
			$args['readonly']    = true;
			$args['data']        = [
				'action'      => 'upgrade',
				'name'        => $label,
				'utm-content' => 'Builder PayPal Commerce Email Field',
				'licence'     => 'pro',
			];
		}

		return wpforms_panel_field(
			'select',
			PayPalCommerce::SLUG,
			'shipping_email',
			$this->form_data,
			esc_html__( 'Shipping Email', 'wpforms-lite' ),
			$args,
			false
		);
	}

	/**
	 * Display Single payment content inside the panel content area.
	 *
	 * @since 1.10.0
	 */
	private function builder_content_one_time(): void {
		?>

		<div class="wpforms-panel-content-section-payment">
			<h2 class="wpforms-panel-content-section-payment-subtitle">
				<?php esc_html_e( 'One-Time Payments', 'wpforms-lite' ); ?>
			</h2>
			<?php
			wpforms_panel_field(
				'toggle',
				PayPalCommerce::SLUG,
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
	 * @since 1.10.0
	 */
	private function builder_content_recurring(): void {
		?>

		<div class="wpforms-panel-content-section-payment">
			<h2 class="wpforms-panel-content-section-payment-subtitle">
				<?php esc_html_e( 'Recurring Payments ', 'wpforms-lite' ); ?>
			</h2>
			<?php

			$this->add_plan_education();

			wpforms_panel_field(
				'toggle',
				PayPalCommerce::SLUG,
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
				$this->builder_content_recurring_payment_before_content();

				if ( empty( $this->form_data['payments'][ PayPalCommerce::SLUG ]['recurring'] ) ) {
					$this->form_data['payments'][ PayPalCommerce::SLUG ]['recurring'][] = [];
				}

				foreach ( $this->form_data['payments'][ PayPalCommerce::SLUG ]['recurring'] as $plan_id => $plan_settings ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo wpforms_render(
						'builder/payment/recurring/item',
						[
							'plan_id' => $plan_id,
							'content' => $this->get_builder_content_recurring_payment_content( $plan_id ),
						],
						true
					);

					break;
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Display content before the recurring payment area.
	 *
	 * @since 1.10.0
	 */
	public function builder_content_recurring_payment_before_content(): void {

		printf(
			'<p class="wpforms-alert wpforms-alert-warning">%s</p>',
			esc_html__( 'Fastlane, alternative payment methods and credit card fields are not supported for subscriptions and will not display on your form.', 'wpforms-lite' )
		);

		if ( ! Helpers::is_pro() ) {
			return;
		}

		printf(
			'<p class="wpforms-alert wpforms-alert-warning">%s</p>',
			esc_html__( 'It\'s not possible to process multiple plans at the same time. If your conditional logic matches more than one plan, the form will process the first plan that matches your conditions.', 'wpforms-lite' )
		);
	}

	/**
	 * Add new plan education modals.
	 *
	 * @since 1.10.0
	 */
	private function add_plan_education(): void {

		$label = __( 'Add New Plan', 'wpforms-lite' );

		if ( ! Helpers::is_allowed_license_type() ) {
			echo '<a
				href="#"
				class="wpforms-panel-content-section-payment-button wpforms-panel-content-section-payment-button-add-plan education-modal"
				data-action="upgrade"
				data-name="' . esc_attr__( 'Multiple Subscriptions', 'wpforms-lite' ) . '"
			>' . esc_html( $label ) . '</a>';

			return;
		}

		$addon = wpforms()->obj( 'addons' )->get_addon( 'paypal-commerce' );

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
				data-name="' . esc_attr__( 'PayPalCommerce Pro', 'wpforms-lite' ) . '"
			>' . esc_html( $label ) . '</a>';
	}

	/**
	 * Get address panel fields.
	 *
	 * @since 1.10.0
	 *
	 * @param string|null $plan_id Plan ID.
	 *
	 * @return string
	 */
	private function get_address_panel_fields( $plan_id = null ): string {

		// Recurring payments: only show Shipping Address.
		if ( ! is_null( $plan_id ) ) {
			return $this->create_address_field( 'shipping', $plan_id );
		}

		// One-time payments: show both Billing and Shipping Address.
		return $this->create_address_field( 'billing' ) .
			$this->create_address_field( 'shipping' );
	}

	/**
	 * Create an address field (billing or shipping).
	 *
	 * @since 1.10.0
	 *
	 * @param string      $type    Field type: 'billing' or 'shipping'.
	 * @param string|null $plan_id Plan ID for recurring payments.
	 *
	 * @return string
	 */
	private function create_address_field( string $type, $plan_id = null ): string {

		$is_shipping = $type === 'shipping';
		$label       = $is_shipping ?
			esc_html__( 'Shipping Address', 'wpforms-lite' ) :
			esc_html__( 'Billing Address', 'wpforms-lite' );

		$field_key = $type . '_address';
		$args      = $this->build_address_field_args( $type, $plan_id );

		return wpforms_panel_field(
			'select',
			$this->slug,
			$field_key,
			$this->form_data,
			$label,
			$args,
			false
		);
	}

	/**
	 * Build arguments for an address field.
	 *
	 * @since 1.10.0
	 *
	 * @param string      $type    Field type: 'billing' or 'shipping'.
	 * @param string|null $plan_id Plan ID for recurring payments.
	 *
	 * @return array
	 */
	private function build_address_field_args( string $type, $plan_id = null ): array {

		$is_pro      = wpforms()->is_pro();
		$is_shipping = $type === 'shipping';
		$label       = $is_shipping ?
			esc_html__( 'Shipping Address', 'wpforms-lite' ) :
			esc_html__( 'Billing Address', 'wpforms-lite' );

		$args = [
			'parent'      => 'payments',
			'field_map'   => [ 'address' ],
			'placeholder' => esc_html__( '--- Select a Field ---', 'wpforms-lite' ),
		];

		// Add the subsection and index for recurring payments.
		if ( ! is_null( $plan_id ) ) {
			$args['subsection'] = 'recurring';
			$args['index']      = $plan_id;
		}

		// Configure Pro-specific settings or education modal.
		if ( $is_pro ) {
			$args['tooltip'] = $is_shipping ?
				esc_html__( "Select the field that contains the buyer's shipping address. This setting is optional.", 'wpforms-lite' ) :
				esc_html__( "Select the field that contains the buyer's address. This setting is optional.", 'wpforms-lite' );
		} else {
			$args['pro_badge']   = true;
			$args['input_class'] = 'education-modal';
			$args['readonly']    = true;
			$args['data']        = [
				'action'      => 'upgrade',
				'name'        => $label,
				'utm-content' => 'Builder PayPal Commerce Address Field',
				'licence'     => 'pro',
			];
		}

		return $args;
	}

	/**
	 * Alert icon.
	 *
	 * @since 1.10.0
	 */
	private function alert_icon(): void {

		printf(
			'<img src="%1$s" class="wpforms-builder-payment-settings-alert-icon" alt="%2$s">',
			esc_url( $this->icon ),
			esc_attr__( 'Connect WPForms to PayPal Commerce.', 'wpforms-lite' )
		);
	}

	/**
	 * Learn more link.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	private function learn_more_link(): string {

		return sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer" class="secondary-text">%s</a>',
			esc_url( wpforms_utm_link( 'https://wpforms.com/docs/paypal-commerce-addon/#install', 'builder-payments', 'PayPal Commerce Documentation' ) ),
			esc_html__( 'Learn more about our PayPal Commerce integration.', 'wpforms-lite' )
		);
	}
}
