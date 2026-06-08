<?php

namespace WPForms\Integrations\PayPalCommerce\Traits;

use WPForms\Integrations\PayPalCommerce\Helpers;

/**
 * Connections methods trait.
 *
 * @since 1.10.0
 */
trait ConnectionTrait {

	/**
	 * Update connections DB data.
	 *
	 * @since 1.10.0
	 *
	 * @param array $connections Connections.
	 */
	private function update_connections( array $connections ): void {

		update_option( 'wpforms_paypal_commerce_connections', $connections );
	}

	/**
	 * Retrieve a connection mode.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	public function get_mode(): string {

		return $this->is_live_mode ? Helpers::PRODUCTION : Helpers::SANDBOX;
	}

	/**
	 * Save connection data into DB.
	 *
	 * @since 1.10.0
	 */
	public function save(): void {

		$connections = $this->get_connections();

		$connections[ $this->get_mode() ] = $this->get_data();

		$this->update_connections( $connections );
	}

	/**
	 * Delete connection data from DB.
	 *
	 * @since 1.10.0
	 */
	public function delete(): void {

		$connections = $this->get_connections();

		unset( $connections[ $this->get_mode() ] );

		empty( $connections ) ? delete_option( 'wpforms_paypal_commerce_connections' ) : $this->update_connections( $connections );
	}

	/**
	 * Retrieve SDK client token.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	public function get_sdk_client_token(): string {

		return $this->sdk_client_token;
	}

	/**
	 * Set the SDK client token.
	 *
	 * @since 1.10.0
	 *
	 * @param string $token Token.
	 *
	 * @return self
	 */
	public function set_sdk_client_token( string $token ) {

		$this->sdk_client_token = $token;

		return $this;
	}

	/**
	 * Set SDK client token expires in time.
	 *
	 * @since 1.10.0
	 *
	 * @param int $expires_in Expires in time.
	 *
	 * @return self
	 */
	public function set_sdk_client_token_expires_in( int $expires_in ) {

		$this->sdk_client_token_expires_in = $expires_in;

		return $this;
	}

	/**
	 * Determine whether the SDK client token is expired.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	public function is_sdk_client_token_expired(): bool {

		return time() > $this->sdk_client_token_expires_in;
	}

	/**
	 * Retrieve access token.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	public function get_access_token(): string {

		return $this->access_token ?? '';
	}

	/**
	 * Get access token expires in time.
	 *
	 * @since 1.10.0
	 *
	 * @return int
	 */
	public function get_access_token_expires_in(): int {

		return $this->access_token_expires_in ?? 0;
	}

	/**
	 * Retrieve client token.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	public function get_client_token(): string {

		return $this->client_token ?? '';
	}

	/**
	 * Determine whether a client token is expired.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	public function is_client_token_expired(): bool {

		return time() > $this->client_token_expires_in;
	}

	/**
	 * Retrieve a client ID.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	public function get_client_id(): string {

		return $this->client_id ?? '';
	}

	/**
	 * Retrieve an ID of the partner merchant.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	public function get_partner_merchant_id(): string {

		return $this->partner_merchant_id ?? '';
	}

	/**
	 * Retrieve an ID of the partner.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	public function get_partner_id(): string {

		return self::PARTNER_ID;
	}

	/**
	 * Retrieve an ID of the authorized merchant.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	public function get_merchant_id(): string {

		return $this->merchant_id ?? '';
	}

	/**
	 * Validate granted permissions.
	 *
	 * @since 1.10.0
	 *
	 * @param array $permissions Permissions.
	 *
	 * @return string
	 */
	public function validate_permissions( array $permissions ): string {

		if (
			empty( $permissions ) ||
			empty( $permissions['payments_receivable'] ) ||
			empty( $permissions['primary_email_confirmed'] )
		) {
			return self::STATUS_INVALID;
		}

		$credit_card_valid     = false;
		$paypal_checkout_valid = false;

		foreach ( $permissions['products'] as $product ) {

			if ( ! isset( $product['vetting_status'] ) || $product['vetting_status'] !== 'SUBSCRIBED' ) {
				continue;
			}

			if ( $product['name'] === 'PPCP_STANDARD' ) {
				$credit_card_valid = true;
			}

			if ( $product['name'] === 'PPCP_CUSTOM' ) {
				$paypal_checkout_valid = true;
			}
		}

		return $credit_card_valid && $paypal_checkout_valid ? self::STATUS_VALID : self::STATUS_INVALID;
	}

	/**
	 * Determine whether a connection is configured fully.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	public function is_configured(): bool {

		return ! empty( $this->access_token ) && ! empty( $this->client_token ) && ! empty( $this->client_id ) && ! empty( $this->merchant_id );
	}

	/**
	 * Determine whether a connection is ready for use.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	public function is_usable(): bool {

		// First party connection is not allowed to be used without Pro addon.
		if ( Helpers::is_legacy() && ! Helpers::is_pro() ) {
			return false;
		}

		return $this->is_configured() && $this->is_valid() && ! $this->is_access_token_expired() && $this->get_sdk_client_token();
	}

	/**
	 * Determine whether a connection is valid.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	public function is_valid(): bool {

		return $this->status === self::STATUS_VALID;
	}

	/**
	 * Determine whether the access token is expired.
	 *
	 * @since 1.10.0
	 *
	 * @return bool
	 */
	public function is_access_token_expired(): bool {

		return time() > $this->get_access_token_expires_in();
	}
}
