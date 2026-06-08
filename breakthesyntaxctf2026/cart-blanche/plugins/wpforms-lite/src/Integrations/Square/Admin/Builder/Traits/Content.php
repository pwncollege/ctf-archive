<?php

namespace WPForms\Integrations\Square\Admin\Builder\Traits;

use WPForms\Integrations\Square\Admin\Notices;
use WPForms\Integrations\Square\Connection;
use WPForms\Integrations\Square\Helpers;

/**
 * Payment builder settings content trait.
 *
 * @since 1.9.5
 */
trait Content {

	/**
	 * Display content inside the panel content area.
	 *
	 * @since 1.9.5
	 */
	public function builder_content() {

		if ( $this->builder_alerts() ) {
			return;
		}

		$hide_class = ! Helpers::has_square_field( $this->form_data ) ? 'wpforms-hidden' : '';

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo Notices::get_fee_notice( $hide_class );

		$this->maybe_convert_legacy_settings();

		echo '<div id="wpforms-panel-content-section-payment-square" class="' . esc_attr( $hide_class ) . '">';

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
	 * @since 1.9.5
	 */
	private function maybe_convert_legacy_settings() {

		if ( empty( $this->form_data['payments']['square']['enable'] ) ) {
			return;
		}

		// Enable one-time payments if they were active.
		unset( $this->form_data['payments']['square']['enable'] );

		$this->form_data['payments']['square']['enable_one_time'] = 1;
	}

	/**
	 * Get content inside the one time payment area.
	 *
	 * @since 1.9.5
	 *
	 * @return string
	 */
	protected function get_builder_content_one_time_content(): string {

		$content = wpforms_panel_field(
			'text',
			'square',
			'payment_description',
			$this->form_data,
			esc_html__( 'Payment Description', 'wpforms-lite' ),
			[
				'parent'  => 'payments',
				'tooltip' => esc_html__( 'Enter your payment description. Eg: Donation for the soccer team.', 'wpforms-lite' ),
			],
			false
		);

		$content .= wpforms_panel_field(
			'select',
			'square',
			'buyer_email',
			$this->form_data,
			esc_html__( 'Buyer Email', 'wpforms-lite' ),
			[
				'parent'      => 'payments',
				'field_map'   => [ 'email' ],
				'placeholder' => esc_html__( '--- Select Buyer Email ---', 'wpforms-lite' ),
				'tooltip'     => esc_html__( "Select the field that contains the buyer's email address. This field is optional.", 'wpforms-lite' ),
			],
			false
		);

		$content .= wpforms_panel_field(
			'select',
			'square',
			'billing_name',
			$this->form_data,
			esc_html__( 'Billing Name', 'wpforms-lite' ),
			[
				'parent'      => 'payments',
				'field_map'   => [ 'name' ],
				'placeholder' => esc_html__( '--- Select Billing Name ---', 'wpforms-lite' ),
				'tooltip'     => esc_html__( "Select the field that contains the billing's name. This field is optional.", 'wpforms-lite' ),
			],
			false
		);

		$content .= $this->get_address_panel_fields();
		$content .= $this->single_payments_conditional_logic_section();

		return $content;
	}

	/**
	 * Get content inside the recurring payment area.
	 *
	 * @since 1.9.5
	 *
	 * @param string $plan_id Plan id.
	 *
	 * @return string
	 */
	protected function get_builder_content_recurring_payment_content( $plan_id ): string {

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
			'phase_cadence',
			$this->form_data,
			esc_html__( 'Phase Cadence', 'wpforms-lite' ),
			[
				'parent'     => 'payments',
				'subsection' => 'recurring',
				'index'      => $plan_id,
				'default'    => 'yearly',
				'options'    => wp_list_pluck( Helpers::get_subscription_cadences(), 'name', 'slug' ),
				'tooltip'    => esc_html__( 'How often you would like the charge to recur.', 'wpforms-lite' ),
			],
			false
		);

		$is_empty_email = isset( $this->form_data['payments'][ $this->slug ]['recurring'][ $plan_id ]['customer_email'] ) && empty( $this->form_data['payments'][ $this->slug ]['recurring'][ $plan_id ]['customer_email'] );

		$content .= wpforms_panel_field(
			'select',
			$this->slug,
			'customer_email',
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

		$is_empty_name = isset( $this->form_data['payments'][ $this->slug ]['recurring'][ $plan_id ]['customer_name'] ) && empty( $this->form_data['payments'][ $this->slug ]['recurring'][ $plan_id ]['customer_name'] );

		$content .= wpforms_panel_field(
			'select',
			$this->slug,
			'customer_name',
			$this->form_data,
			esc_html__( 'Customer Name', 'wpforms-lite' ),
			[
				'parent'      => 'payments',
				'subsection'  => 'recurring',
				'index'       => $plan_id,
				'input_class' => $is_empty_name ? 'wpforms-required-field-error' : '',
				'field_map'   => [ 'name' ],
				'placeholder' => esc_html__( '--- Select Name ---', 'wpforms-lite' ),
				'tooltip'     => esc_html__( "Select the field that contains the customer's name. This field is required.", 'wpforms-lite' ),
			],
			false
		);

		$content .= $this->get_address_panel_fields( $plan_id );
		$content .= $this->recurring_payments_conditional_logic_section( $plan_id );

		return $content;
	}

	/**
	 * Display Single payment content inside the panel content area.
	 *
	 * @since 1.9.5
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
	 * @since 1.9.5
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
	 * @since 1.9.5
	 */
	private function add_plan_education() {

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

		$addon = wpforms()->obj( 'addons' )->get_addon( 'wpforms-square' );

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
				data-name="' . esc_attr__( 'Square Pro', 'wpforms-lite' ) . '"
			>' . esc_html( $label ) . '</a>';
	}

	/**
	 * Get address panel fields.
	 *
	 * @since 1.9.5
	 *
	 * @param string|null $plan_id Plan ID.
	 *
	 * @return string
	 */
	private function get_address_panel_fields( $plan_id = null ): string {

		$args = [
			'parent'    => 'payments',
			'field_map' => [ 'address' ],
		];

		$is_pro = wpforms()->is_pro();

		if ( ! $is_pro ) {
			$args['pro_badge']   = true;
			$args['data']        = [
				'action'      => 'upgrade',
				'name'        => esc_html__( 'Customer Address', 'wpforms-lite' ),
				'utm-content' => 'Builder Square Address Field',
				'licence'     => 'pro',
			];
			$args['input_class'] = 'education-modal';
			$args['readonly']    = true;
		} else {
			$args['tooltip'] = esc_html__( 'Select the field that contains the customer\'s Address. This field is optional.', 'wpforms-lite' );
		}

		// Check if subscription.
		if ( ! is_null( $plan_id ) ) {
			$args['placeholder'] = esc_html__( '--- Select Address ---', 'wpforms-lite' );
			$args['subsection']  = 'recurring';
			$args['index']       = $plan_id;

			return wpforms_panel_field(
				'select',
				$this->slug,
				'customer_address',
				$this->form_data,
				esc_html__( 'Customer Address', 'wpforms-lite' ),
				$args,
				false
			);
		}

		if ( ! $is_pro ) {
			$args['data']['name'] = esc_html__( 'Billing Address', 'wpforms-lite' );
		} else {
			$args['tooltip'] = esc_html__( 'Select the field that contains the billing\'s address. This field is optional.', 'wpforms-lite' );
		}

		$args['placeholder'] = esc_html__( '--- Select Billing Address ---', 'wpforms-lite' );

		return wpforms_panel_field(
			'select',
			$this->slug,
			'billing_address',
			$this->form_data,
			esc_html__( 'Billing Address', 'wpforms-lite' ),
			$args,
			false
		);
	}

	/**
	 * Check if connection exists and ready to use.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	private function builder_alerts(): bool {

		$connection = Connection::get();

		if ( ! $connection ) {
			$this->alert_content(
				__( 'Heads up! Square payments can\'t be enabled yet.', 'wpforms-lite' ),
				sprintf(
					wp_kses( /* translators: %s - Admin area Payments settings page URL. */
						__( "First, please connect to your Square account on the <a href='%s'>WPForms Settings</a> page.", 'wpforms-lite' ),
						[
							'a' => [
								'href' => [],
							],
						]
					),
					esc_url( Helpers::get_settings_page_url() . '#wpforms-setting-row-square-heading' )
				)
			);

			return true;
		}

		if ( ! $connection->is_usable() ) {

			$this->alert_content(
				__( 'Square payments can\'t be processed because there\'s a problem with the account connection.', 'wpforms-lite' ),
				sprintf(
					wp_kses( /* translators: %s - the WPForms Payments settings page URL. */
						__( "First, please resolve the connection issue on the <a href='%2\$s'>Payment Settings</a> page.", 'wpforms-lite' ),
						[
							'a' => [
								'href' => [],
							],
						]
					),
					Helpers::is_sandbox_mode() ? esc_html__( 'Sandbox', 'wpforms-lite' ) : esc_html__( 'Production', 'wpforms-lite' ),
					esc_url( Helpers::get_settings_page_url() . '#wpforms-setting-row-square-heading' )
				)
			);

			return true;
		}

		if ( $connection->is_expired() ) {

			$this->alert_content(
				__( 'Heads up! Square account connection is expired.', 'wpforms-lite' ),
				sprintf(
					wp_kses( /* translators: %s - the WPForms Payments settings page URL. */
						__( "Tokens must be refreshed. Please refresh them on the <a href='%2\$s'>WPForms Settings</a> page.", 'wpforms-lite' ),
						[
							'a' => [
								'href' => [],
							],
						]
					),
					Helpers::is_sandbox_mode() ? esc_html__( 'Sandbox', 'wpforms-lite' ) : esc_html__( 'Production', 'wpforms-lite' ),
					esc_url( Helpers::get_settings_page_url() . '#wpforms-setting-row-square-heading' )
				)
			);

			return true;
		}

		$this->credit_card_alert();

		return false;
	}

	/**
	 * Display alert content.
	 *
	 * @since 1.9.5
	 *
	 * @param string $title   Alert title.
	 * @param string $message Alert message.
	 */
	private function alert_content( string $title, string $message ) {
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

	/**
	 * Display alert if Square Credit Card field is not added to the form.
	 *
	 * @since 1.9.5
	 */
	private function credit_card_alert() {

		$hide_class = Helpers::has_square_field( $this->form_data ) ? 'wpforms-hidden' : '';
		?>

		<div id="wpforms-<?php echo esc_attr( $this->slug ); ?>-credit-card-alert" class="wpforms-alert wpforms-alert-info <?php echo esc_attr( $hide_class ); ?>">
			<?php
				$this->alert_content(
					'',
					esc_html__( 'To use Square, first add the Square payment field to your form.', 'wpforms-lite' )
				);
			?>
		</div>

		<?php
	}

	/**
	 * Alert icon.
	 *
	 * @since 1.9.5
	 */
	private function alert_icon() {

		printf(
			'<img src="%1$s" class="wpforms-builder-payment-settings-alert-icon" alt="%2$s">',
			esc_url( $this->icon ),
			esc_attr__( 'Connect WPForms to Square.', 'wpforms-lite' )
		);
	}

	/**
	 * Learn more link.
	 *
	 * @since 1.9.5
	 *
	 * @return string
	 */
	private function learn_more_link(): string {

		return sprintf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer" class="secondary-text">%2$s</a>',
			esc_url( wpforms_utm_link( 'https://wpforms.com/docs/how-to-install-and-use-the-square-addon-with-wpforms/', 'builder-payments', 'Square Documentation' ) ),
			esc_html__( 'Learn more about our Square integration.', 'wpforms-lite' )
		);
	}
}
