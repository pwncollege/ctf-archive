<?php

namespace WPForms\Integrations\PayPalCommerce\PaymentMethods\ApplePay;

use WPForms\Integrations\PayPalCommerce\Connection;
use WPForms\Integrations\PayPalCommerce\PaymentMethods\ColorMapInterface;
use WPForms\Integrations\PayPalCommerce\PayPalCommerce;

/**
 * Handles builder functionalities for rendering Apple Pay button in the form builder.
 *
 * @since 1.10.0
 */
class Builder {

	/**
	 * Color mapper instance for handling button and logo color mappings.
	 *
	 * @since 1.10.0
	 *
	 * @var ColorMapInterface
	 */
	private $color_map;

	/**
	 * Domain manager instance responsible for handling domain-related operations.
	 *
	 * @since 1.10.0
	 *
	 * @var DomainManager
	 */
	private $domain_manager;

	/**
	 * Class constructor.
	 *
	 * @since 1.10.0
	 *
	 * @param ColorMapInterface $color_map      An instance of ColorMapInterface used for color mapping.
	 * @param DomainManager     $domain_manager An instance of DomainManager used for domain management.
	 */
	public function __construct( ColorMapInterface $color_map, DomainManager $domain_manager ) {

		$this->color_map      = $color_map;
		$this->domain_manager = $domain_manager;
	}

	/**
	 * Registers the necessary hooks for the builder.
	 *
	 * @since 1.10.0
	 */
	final public function hooks(): void {

		add_action( 'wpforms_integrations_paypal_commerce_fields_paypal_commerce_builder_submit_button', [ $this, 'render_button_container' ] );
		add_filter( 'wpforms_builder_strings', [ $this, 'javascript_strings' ] );
	}

	/**
	 * Renders Apple Pay button container in the form builder.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field PayPal Commerce field data.
	 */
	public function render_button_container( $field ): void {

		$field = (array) $field;

		$button_color = $this->color_map->get_button_color( $field['color'] );

		printf(
			'<div class="wpforms-paypal-commerce-button applepay-button" data-button-color="%1$s" data-button-shape="%2$s">
				<span class="wpforms-paypal-commerce-button-logo applepay-logo" data-logo-color="%3$s"></span>
			</div>',
			esc_attr( $button_color ),
			esc_attr( $field['shape'] ),
			esc_attr( $this->color_map->get_logo_color( $button_color ) )
		);
	}

	/**
	 * Modifies and returns an array of JavaScript strings with additional data for Apple Pay.
	 *
	 * @since 1.10.0
	 *
	 * @param array $strings An array of JavaScript strings to be modified.
	 *
	 * @return array The modified array of JavaScript strings including the Apple Pay data.
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function javascript_strings( $strings ): array {

		$strings = (array) $strings;

		$strings['paypal_commerce_methods']             = $strings['paypal_commerce_methods'] ?? [];
		$strings['paypal_commerce_methods']['applepay'] = [
			'buttonColors' => $this->color_map->get_button_map(),
			'logoColors'   => $this->color_map->get_logo_map(),
		];

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput
		$is_new_form = (int) ( $_GET['newform'] ?? 0 );

		if ( ! $is_new_form ) {
			return $strings;
		}

		$connection = Connection::get();

		if ( ! $connection ) {
			return $strings;
		}

		$api = PayPalCommerce::get_api( $connection );

		if ( $this->domain_manager->is_domain_registered( $api ) ) {
			return $strings;
		}

		$strings['paypal_commerce_methods']['applepay']['domainNotice'] = $this->domain_manager->get_notice_message();

		return $strings;
	}
}
