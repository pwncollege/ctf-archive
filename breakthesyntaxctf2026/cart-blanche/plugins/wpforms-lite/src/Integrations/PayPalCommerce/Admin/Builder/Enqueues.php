<?php

namespace WPForms\Integrations\PayPalCommerce\Admin\Builder;

use WPForms\Integrations\PayPalCommerce\Helpers;

/**
 * Script enqueues for the PayPalCommerce Builder settings panel.
 *
 * @since 1.10.0
 */
class Enqueues {

	/**
	 * Initialize.
	 *
	 * @since 1.10.0
	 */
	public function init(): void {

		$this->hooks();
	}

	/**
	 * Builder hooks.
	 *
	 * @since 1.10.0
	 */
	private function hooks(): void {

		add_action( 'wpforms_builder_enqueues', [ $this, 'enqueues' ] );
		add_filter( 'wpforms_builder_strings',  [ $this, 'javascript_strings' ] );
	}

	/**
	 * Enqueue assets.
	 *
	 * @since 1.10.0
	 */
	public function enqueues(): void {

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-builder-paypal-commerce',
			WPFORMS_PLUGIN_URL . "assets/js/integrations/paypal-commerce/builder-paypal-commerce{$min}.js",
			[ 'wpforms-builder' ],
			WPFORMS_VERSION,
			true
		);

		wp_enqueue_style(
			'wpforms-builder-paypal-commerce',
			WPFORMS_PLUGIN_URL . "assets/css/integrations/paypal-commerce/builder-paypal-commerce{$min}.css",
			[],
			WPFORMS_VERSION
		);
	}

	/**
	 * Add localized strings.
	 *
	 * @since 1.10.0
	 *
	 * @param array $strings Form builder JS strings.
	 *
	 * @return array
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function javascript_strings( $strings ): array {

		$strings = (array) $strings;

		$strings['paypal_commerce_connection_required'] = wp_kses(
			__( '<p>You must connect to PayPal Commerce before using the PayPal Commerce field.</p><p>To connect your account, please go to <strong>WPForms Settings » Payments » PayPal Commerce</strong> and press <strong>Connect with PayPal Commerce</strong> button.</p>', 'wpforms-lite' ),
			[
				'p'      => [],
				'strong' => [],
			]
		);

		$strings['paypal_commerce_payments_enabled_required'] = wp_kses(
			__( '<p>PayPal Commerce must be enabled for this form when using the PayPal Commerce field.</p><p>To proceed, go to <strong><a href="#" class="wpforms-no-fetch-link" data-panel="payments" data-section="paypal_commerce">Payments » PayPal Commerce</a></strong> and select <strong>payment type</strong>.</p>', 'wpforms-lite' ),
			[
				'p'      => [],
				'strong' => [],
				'a'      => [
					'href'         => [],
					'class'        => [],
					'data-panel'   => [],
					'data-section' => [],
				],
			]
		);

		$strings['paypal_commerce_ajax_required'] = wp_kses(
			__( '<p>AJAX form submissions are required when using the PayPal Commerce field.</p><p>To proceed, please go to <strong>Settings » General</strong> and check <strong>Enable AJAX form submission</strong>.</p>', 'wpforms-lite' ),
			[
				'p'      => [],
				'strong' => [],
			]
		);

		$strings['paypal_commerce_plan_name_disabled']       = esc_html__( 'The plan name can’t be changed once you save it. Please create a new plan.', 'wpforms-lite' );
		$strings['paypal_commerce_product_type_disabled']    = esc_html__( 'The product type can’t be changed once you save it. Please create a new plan.', 'wpforms-lite' );
		$strings['paypal_commerce_recurring_times_disabled'] = esc_html__( 'The recurring plan can’t be changed once you save it. Please create a new plan.', 'wpforms-lite' );
		$strings['paypal_commerce_fastlane_cc_warning']      = esc_html__( 'Credit Card and Fastlane cannot be enabled at the same time.', 'wpforms-lite' );
		$strings['paypal_commerce_is_pro']                   = Helpers::is_pro();

		return $strings;
	}
}
