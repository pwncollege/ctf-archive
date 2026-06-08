<?php

namespace WPForms\Integrations\PayPalCommerce;

use WPForms\Integrations\PayPalCommerce\Admin\Connect;
use WPForms\Integrations\PayPalCommerce\Traits\ConnectionTrait;

/**
 * Connection class.
 *
 * @since 1.10.0
 */
class Connection {

	use ConnectionTrait;

	/**
	 * Valid connection status.
	 *
	 * @since 1.10.0
	 */
	private const STATUS_VALID = 'valid';

	/**
	 * Invalid connection status.
	 *
	 * @since 1.10.0
	 */
	public const STATUS_INVALID = 'invalid';

	/**
	 * Third-party connection type.
	 *
	 * @since 1.10.0
	 */
	public const TYPE_THIRD_PARTY = 'third_party';

	/**
	 * Option name used to store PayPal Commerce connection data.
	 *
	 * @since 1.10.0
	 */
	private const OPTION_NAME_CONNECTIONS = 'wpforms_paypal_commerce_connections';

	/**
	 * Partner ID.
	 *
	 * @since 1.10.0
	 */
	private const PARTNER_ID = 'AwesomeMotive_SP_PPCP';

	/**
	 * Determine if a connection for production mode.
	 *
	 * @since 1.10.0
	 *
	 * @var bool
	 */
	private $is_live_mode;

	/**
	 * Client access token.
	 *
	 * @since 1.10.0
	 *
	 * @var string
	 */
	private $access_token = '';

	/**
	 * Date when access tokens should be renewed.
	 *
	 * @since 1.10.0
	 *
	 * @var int
	 */
	private $access_token_expires_in = 0;

	/**
	 * Client token.
	 *
	 * @since 1.10.0
	 *
	 * @var string
	 */
	private $client_token = '';

	/**
	 * Date when client tokens should be renewed.
	 *
	 * @since 1.10.0
	 *
	 * @var int
	 */
	private $client_token_expires_in = 0;

	/**
	 * SDK client token.
	 *
	 * @since 1.10.0
	 *
	 * @var string
	 */
	private $sdk_client_token = '';

	/**
	 * Date when SDK client tokens should be renewed.
	 *
	 * @since 1.10.0
	 *
	 * @var int
	 */
	private $sdk_client_token_expires_in = 0;

	/**
	 * Connection status.
	 *
	 * @since 1.10.0
	 *
	 * @var string
	 */
	private $status = '';

	/**
	 * ID of an application.
	 *
	 * @since 1.10.0
	 *
	 * @var string
	 */
	private $client_id = '';

	/**
	 * ID of the partner merchant.
	 *
	 * @since 1.10.0
	 *
	 * @var string
	 */
	private $partner_merchant_id = '';

	/**
	 * ID of the merchant.
	 *
	 * @since 1.10.0
	 *
	 * @var string
	 */
	private $merchant_id = '';

	/**
	 * Secret of the merchant.
	 *
	 * @since 1.10.0
	 *
	 * @var string
	 */
	private $secret = '';

	/**
	 * Type of the connection.
	 *
	 * @since 1.10.0
	 *
	 * @var string
	 */
	private $type = '';

	/**
	 * Connection constructor.
	 *
	 * @since 1.10.0
	 *
	 * @param array $data Connection data.
	 */
	public function __construct( array $data ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( ! empty( $data['access_token'] ) ) {
			$this->access_token = $data['access_token'];
		}

		if ( ! empty( $data['access_token_expires_in'] ) ) {
			$this->access_token_expires_in = $data['access_token_expires_in'];
		}

		if ( ! empty( $data['client_token'] ) ) {
			$this->client_token = $data['client_token'];
		}

		if ( ! empty( $data['client_token_expires_in'] ) ) {
			$this->client_token_expires_in = $data['client_token_expires_in'];
		}

		if ( ! empty( $data['sdk_client_token'] ) ) {
			$this->sdk_client_token = $data['sdk_client_token'];
		}

		if ( ! empty( $data['sdk_client_token_expires_in'] ) ) {
			$this->sdk_client_token_expires_in = $data['sdk_client_token_expires_in'];
		}

		if ( ! empty( $data['client_id'] ) ) {
			$this->client_id = $data['client_id'];
		}

		if ( ! empty( $data['partner_merchant_id'] ) ) {
			$this->partner_merchant_id = $data['partner_merchant_id'];
		}

		if ( ! empty( $data['merchant_id'] ) ) {
			$this->merchant_id = $data['merchant_id'];
		}

		if ( ! empty( $data['type'] ) ) {
			$this->type = $data['type'];
		}

		if ( ! empty( $data['secret'] ) ) {
			$this->secret = $data['secret'];
		}

		$this->is_live_mode = Helpers::is_production_mode();

		$this->set_status( empty( $data['status'] ) ? self::STATUS_VALID : $data['status'] );
	}

	/**
	 * Retrieve a connection instance if it exists.
	 *
	 * @since 1.10.0
	 *
	 * @param string $mode PayPal Commerce mode.
	 *
	 * @return Connection|null
	 */
	public static function get( string $mode = '' ) {

		$mode        = Helpers::validate_mode( $mode );
		$connections = self::get_connections();

		if ( empty( $connections[ $mode ] ) ) {
			return null;
		}

		// Default to the current class.
		$class = static::class;

		if ( Helpers::is_pro() && Helpers::is_legacy() ) {
			// phpcs:ignore WPForms.PHP.BackSlash.UseShortSyntax
			$class = \WPFormsPaypalCommerce\Connection::class;
		}

		return new $class( (array) $connections[ $mode ] );
	}

	/**
	 * Update the connection status for the current PayPal Commerce mode.
	 *
	 * The option is updated only when the status value has changed to avoid
	 * unnecessary database writes and update_option hooks.
	 *
	 * @since 1.10.0
	 *
	 * @param string $status New connection status value.
	 */
	public function update_connection_status( string $status ): void {

		$connections = (array) get_option( 'wpforms_paypal_commerce_connections', [] );
		$mode        = $this->get_mode();

		$current = $connections[ $mode ]['status'] ?? null;

		// Do not update if the value is unchanged.
		if ( $current === $status ) {
			return;
		}

		$connections[ $mode ]['status'] = $status;

		update_option( 'wpforms_paypal_commerce_connections', $connections );
	}

	/**
	 * Retrieve a connection in array format, similar to the `toArray` method.
	 *
	 * @since 1.10.0
	 *
	 * @return array
	 */
	private function get_data(): array {

		return [
			'access_token'                => $this->access_token,
			'access_token_expires_in'     => $this->access_token_expires_in,
			'client_token'                => $this->client_token,
			'client_token_expires_in'     => $this->client_token_expires_in,
			'sdk_client_token'            => $this->sdk_client_token,
			'sdk_client_token_expires_in' => $this->sdk_client_token_expires_in,
			'client_id'                   => $this->client_id,
			'partner_merchant_id'         => $this->partner_merchant_id,
			'merchant_id'                 => $this->merchant_id,
			'secret'                      => $this->secret,
			'status'                      => $this->status,
			'type'                        => $this->type,
		];
	}

	/**
	 * Set an access token.
	 *
	 * @since 1.10.0
	 *
	 * @param string $token Token.
	 *
	 * @return Connection
	 */
	public function set_access_token( string $token ): Connection {

		$this->access_token = $token;

		return $this;
	}

	/**
	 * Set access token expires in time.
	 *
	 * @since 1.10.0
	 *
	 * @param int $expires_in Expires in time.
	 *
	 * @return Connection
	 */
	public function set_access_token_expires_in( int $expires_in ): Connection {

		$this->access_token_expires_in = $expires_in;

		return $this;
	}

	/**
	 * Set the client token.
	 *
	 * @since 1.10.0
	 *
	 * @param string $token Token.
	 *
	 * @return Connection
	 */
	public function set_client_token( string $token ): Connection {

		$this->client_token = $token;

		return $this;
	}

	/**
	 * Set client token expires in time.
	 *
	 * @since 1.10.0
	 *
	 * @param int $expires_in Expires in time.
	 *
	 * @return Connection
	 */
	public function set_client_token_expires_in( int $expires_in ): Connection {

		$this->client_token_expires_in = $expires_in;

		return $this;
	}

	/**
	 * Retrieve a secret of the authorized merchant.
	 *
	 * @since 1.10.0
	 *
	 * @return string
	 */
	public function get_secret(): string {

		return $this->secret;
	}

	/**
	 * Set a connection status.
	 *
	 * @since 1.10.0
	 *
	 * @param string $status The connection status.
	 *
	 * @return Connection
	 */
	public function set_status( string $status ): Connection {

		$this->status = $status;

		return $this;
	}

	/**
	 * Get connections from DB.
	 *
	 * @since 1.10.0
	 *
	 * @return array Connections.
	 */
	private static function get_connections(): array {

		return (array) get_option( self::OPTION_NAME_CONNECTIONS, [] );
	}

	/**
	 * Refresh expired tokens.
	 *
	 * @since 1.10.0
	 */
	public function refresh_expired_tokens(): Connection {

		if ( $this->is_access_token_expired() ) {
			Connect::refresh_access_token( $this );
		}

		if ( $this->is_client_token_expired() ) {
			Connect::refresh_client_token( $this );
		}

		if ( $this->is_sdk_client_token_expired() ) {
			Connect::refresh_sdk_client_token( $this );
		}

		return $this;
	}
}
