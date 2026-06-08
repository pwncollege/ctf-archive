<?php

namespace WPForms\Integrations\Square;

use WPForms\Vendor\Square\Environment;
use WPForms\Helpers\Crypto;

/**
 * Connection class.
 *
 * @since 1.9.5
 */
class Connection {

	/**
	 * Valid connection status.
	 *
	 * @since 1.9.5
	 */
	const STATUS_VALID = 'valid';

	/**
	 * Invalid connection status.
	 *
	 * @since 1.9.5
	 */
	const STATUS_INVALID = 'invalid';

	/**
	 * Determine if a connection for production mode.
	 *
	 * @since 1.9.5
	 *
	 * @var bool
	 */
	private $live_mode;

	/**
	 * Access token.
	 *
	 * @since 1.9.5
	 *
	 * @var string
	 */
	private $access_token;

	/**
	 * Refresh token.
	 *
	 * @since 1.9.5
	 *
	 * @var string
	 */
	private $refresh_token;

	/**
	 * Square-issued ID of an application.
	 *
	 * @since 1.9.5
	 *
	 * @var string
	 */
	private $client_id;

	/**
	 * Square-issued ID of the merchant.
	 *
	 * @since 1.9.5
	 *
	 * @var string
	 */
	private $merchant_id;

	/**
	 * Currency associated with a merchant account.
	 *
	 * @since 1.9.5
	 *
	 * @var string
	 */
	private $currency;

	/**
	 * Connection status.
	 *
	 * @since 1.9.5
	 *
	 * @var string
	 */
	private $status;

	/**
	 * Date when tokens should be renewed.
	 *
	 * @since 1.9.5
	 *
	 * @var int
	 */
	private $renew_at;

	/**
	 * Determine if connection tokens are encrypted.
	 *
	 * @since 1.9.5
	 *
	 * @var bool
	 */
	private $encrypted;

	/**
	 * Determine if scopes were updated.
	 *
	 * @since 1.9.5
	 *
	 * @var int
	 */
	private $scopes_updated = 0;

	/**
	 * Connection constructor.
	 *
	 * @since 1.9.5
	 *
	 * @param array $data      Connection data.
	 * @param bool  $encrypted Optional. Default true. Use false when connection tokens were not encrypted.
	 */
	public function __construct( array $data, bool $encrypted = true ) {

		$data = (array) $data;

		if ( ! empty( $data['access_token'] ) ) {
			$this->access_token = $data['access_token'];
		}

		if ( ! empty( $data['refresh_token'] ) ) {
			$this->refresh_token = $data['refresh_token'];
		}

		if ( ! empty( $data['client_id'] ) ) {
			$this->client_id = $data['client_id'];
		}

		if ( ! empty( $data['merchant_id'] ) ) {
			$this->merchant_id = $data['merchant_id'];
		}

		if ( ! empty( $data['scopes_updated'] ) ) {
			$this->scopes_updated = $data['scopes_updated'];
		}

		$this->set_status( empty( $data['status'] ) ? self::STATUS_VALID : $data['status'] );

		$this->currency  = empty( $data['currency'] ) ? '' : strtoupper( (string) $data['currency'] );
		$this->renew_at  = empty( $data['renew_at'] ) ? time() : (int) $data['renew_at'];
		$this->live_mode = ! empty( $data['live_mode'] );
		$this->encrypted = (bool) $encrypted;
	}

	/**
	 * Retrieve a connection instance if it exists.
	 *
	 * @since 1.9.5
	 *
	 * @param string $mode      Square mode.
	 * @param bool   $encrypted Optional. Default true. Use false when connection tokens were not encrypted.
	 *
	 * @return Connection|null
	 */
	public static function get( string $mode = '', bool $encrypted = true ) {

		$mode        = Helpers::validate_mode( $mode );
		$connections = (array) get_option( 'wpforms_square_connections', [] );

		if ( empty( $connections[ $mode ] ) ) {
			return null;
		}

		return new self( (array) $connections[ $mode ], $encrypted );
	}

	/**
	 * Save connection data into DB.
	 *
	 * @since 1.9.5
	 */
	public function save() {

		$connections = (array) get_option( 'wpforms_square_connections', [] );

		$connections[ $this->get_mode() ] = $this->get_data();

		update_option( 'wpforms_square_connections', $connections );
	}

	/**
	 * Delete connection data from DB.
	 *
	 * @since 1.9.5
	 */
	public function delete() {

		$connections = (array) get_option( 'wpforms_square_connections', [] );

		unset( $connections[ $this->get_mode() ] );

		empty( $connections ) ? delete_option( 'wpforms_square_connections' ) : update_option( 'wpforms_square_connections', $connections );
	}

	/**
	 * Revoke tokens from DB.
	 *
	 * @since 1.9.5
	 */
	public function revoke_tokens() {

		$connections = (array) get_option( 'wpforms_square_connections', [] );
		$mode        = $this->get_mode();

		$connections[ $mode ]                  = $this->get_data();
		$connections[ $mode ]['access_token']  = '';
		$connections[ $mode ]['refresh_token'] = '';

		update_option( 'wpforms_square_connections', $connections );
	}

	/**
	 * Retrieve true if a connection for production mode.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	public function get_live_mode(): bool {

		return $this->live_mode;
	}

	/**
	 * Retrieve a connection mode.
	 *
	 * @since 1.9.5
	 *
	 * @return string
	 */
	public function get_mode(): string {

		return $this->live_mode ? Environment::PRODUCTION : Environment::SANDBOX;
	}

	/**
	 * Retrieve an un-encrypted access token.
	 *
	 * @since 1.9.5
	 *
	 * @return string
	 */
	public function get_access_token(): string {

		return $this->encrypted ? Crypto::decrypt( $this->access_token ) : $this->access_token;
	}

	/**
	 * Retrieve an un-encrypted refresh token.
	 *
	 * @since 1.9.5
	 *
	 * @return string
	 */
	public function get_refresh_token(): string {

		return $this->encrypted ? Crypto::decrypt( $this->refresh_token ) : $this->refresh_token;
	}

	/**
	 * Retrieve a client ID.
	 *
	 * @since 1.9.5
	 *
	 * @return string
	 */
	public function get_client_id(): string {

		return $this->client_id;
	}

	/**
	 * Retrieve an ID of the authorized merchant.
	 *
	 * @since 1.9.5
	 *
	 * @return string
	 */
	public function get_merchant_id(): string {

		return $this->merchant_id;
	}

	/**
	 * Retrieve a currency code of the authorized merchant.
	 *
	 * @since 1.9.5
	 *
	 * @return string
	 */
	public function get_currency(): string {

		return $this->currency;
	}

	/**
	 * Set a currency code.
	 *
	 * @since 1.9.5
	 *
	 * @param string $code Currency code.
	 *
	 * @return Connection
	 */
	public function set_currency( string $code ) {

		$this->currency = strtoupper( $code );

		return $this;
	}

	/**
	 * Retrieve a connection status.
	 *
	 * @since 1.9.5
	 *
	 * @return string
	 */
	public function get_status(): string {

		return $this->status;
	}

	/**
	 * Set a connection status if it valid.
	 *
	 * @since 1.9.5
	 *
	 * @param string $status The connection status.
	 *
	 * @return Connection
	 */
	public function set_status( string $status ) {

		if ( in_array( $status, $this->get_statuses(), true ) ) {
			$this->status = $status;
		}

		return $this;
	}

	/**
	 * Retrieve a renewal timestamp.
	 *
	 * @since 1.9.5
	 *
	 * @return int
	 */
	public function get_renew_at(): int {

		return $this->renew_at;
	}

	/**
	 * Set/update a renewal timestamp.
	 *
	 * @since 1.9.5
	 *
	 * @return Connection
	 */
	public function set_renew_at() {

		// Tokens must automatically renew every 7 days or less.
		$this->renew_at = time() + wp_rand( 5, 8 ) * DAY_IN_SECONDS;

		return $this;
	}

	/**
	 * Retrieve a scopes updated timestamp.
	 *
	 * @since 1.9.5
	 *
	 * @return int
	 */
	public function get_scopes_updated(): int {

		return $this->scopes_updated;
	}

	/**
	 * Set/update a scopes updated timestamp.
	 *
	 * @since 1.9.5
	 *
	 * @return Connection
	 */
	public function set_scopes_updated() {

		$this->scopes_updated = time();

		return $this;
	}

	/**
	 * Encrypt tokens, if it needed.
	 *
	 * @since 1.9.5
	 *
	 * @return Connection
	 */
	public function encrypt_tokens() {

		// Bail if tokens have already encrypted.
		if ( $this->encrypted ) {
			return $this;
		}

		// Bail if tokens are not passed.
		if ( empty( $this->access_token ) || empty( $this->refresh_token ) ) {
			return $this;
		}

		// Prepare encrypted tokens.
		$encrypted_access_token  = Crypto::encrypt( $this->access_token );
		$encrypted_refresh_token = Crypto::encrypt( $this->refresh_token );

		// Bail if encrypted tokens are invalid.
		if ( empty( $encrypted_access_token ) || empty( $encrypted_refresh_token ) ) {
			return $this;
		}

		$this->encrypted     = true;
		$this->access_token  = $encrypted_access_token;
		$this->refresh_token = $encrypted_refresh_token;

		return $this;
	}

	/**
	 * Retrieve available statuses.
	 *
	 * @since 1.9.5
	 *
	 * @return array
	 */
	private function get_statuses(): array {

		return [ self::STATUS_VALID, self::STATUS_INVALID ];
	}

	/**
	 * Retrieve a connection in array format, simply like `toArray` method.
	 *
	 * @since 1.9.5
	 *
	 * @return array
	 */
	private function get_data(): array {

		return [
			'live_mode'      => $this->live_mode,
			'access_token'   => $this->access_token,
			'refresh_token'  => $this->refresh_token,
			'client_id'      => $this->client_id,
			'merchant_id'    => $this->merchant_id,
			'currency'       => $this->currency,
			'status'         => $this->status,
			'renew_at'       => $this->renew_at,
			'scopes_updated' => $this->scopes_updated,
		];
	}

	/**
	 * Determine whether connection tokens is encrypted.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	private function is_encrypted(): bool {

		return $this->encrypted;
	}

	/**
	 * Determine whether a connection is configured fully.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	public function is_configured(): bool {

		return ! empty( $this->get_access_token() ) && ! empty( $this->get_refresh_token() ) && ! empty( $this->client_id ) && ! empty( $this->merchant_id );
	}

	/**
	 * Determine whether a connection is expired.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	public function is_expired(): bool {

		return ( $this->renew_at - time() ) < HOUR_IN_SECONDS;
	}

	/**
	 * Determine whether a connection currency is matched with WPForms currency.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	public function is_currency_matched(): bool {

		return $this->currency === strtoupper( wpforms_get_currency() );
	}

	/**
	 * Determine whether a connection is valid.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	public function is_valid(): bool {

		return $this->get_status() === self::STATUS_VALID;
	}

	/**
	 * Determine whether a connection is ready for save.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	public function is_saveable(): bool {

		return $this->is_configured() && ! $this->is_expired() && $this->is_encrypted();
	}

	/**
	 * Determine whether a connection is ready for use.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	public function is_usable(): bool {

		return $this->is_configured() && $this->is_valid() && $this->is_currency_matched();
	}
}
