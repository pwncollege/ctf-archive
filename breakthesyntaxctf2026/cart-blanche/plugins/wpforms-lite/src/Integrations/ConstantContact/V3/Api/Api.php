<?php

namespace WPForms\Integrations\ConstantContact\V3\Api;

use RuntimeException;
use InvalidArgumentException;
use WPForms\Integrations\ConstantContact\V3\Core;
use WPForms\Integrations\ConstantContact\V3\ConstantContact;
use WPForms\Integrations\ConstantContact\V3\Api\Http\Request;

/**
 * Class Api.
 *
 * @since 1.9.3
 */
class Api {

	/**
	 * Account data.
	 *
	 * @since 1.9.3
	 *
	 * @var array
	 */
	private $account;

	/**
	 * Request object.
	 *
	 * @since 1.9.3
	 *
	 * @var Request
	 */
	private $request;

	/**
	 * API Constructor.
	 *
	 * @since 1.9.3
	 *
	 * @param array $account Account data.
	 *
	 * @throws InvalidArgumentException If arguments are invalid.
	 */
	public function __construct( array $account ) {

		if ( empty( $account['access_token'] ) ) {
			throw new InvalidArgumentException( 'Access token cannot be empty.' );
		}

		if ( empty( $account['refresh_token'] ) ) {
			throw new InvalidArgumentException( 'Refresh token cannot be empty.' );
		}

		if ( empty( $account['id'] ) ) {
			throw new InvalidArgumentException( 'Account ID cannot be empty.' );
		}

		$this->account = $account;

		$this->refresh_access_token();

		$this->request = new Request( $this->account['access_token'] );
	}

	/**
	 * Get custom fields in a specific format based on provided arguments.
	 *
	 * @since 1.9.3
	 *
	 * @param string|null $field     The field to extract from each custom field. If null, returns all custom fields.
	 * @param string|null $index_key The key to index the returned array by. If null, returns a numerically indexed array.
	 *
	 * @return array
	 */
	public function get_custom_fields( ?string $field = null, ?string $index_key = null ): array {

		$custom_fields = $this->request->get( 'v3/contact_custom_fields', [ 'limit' => 100 ] );
		$custom_fields = $custom_fields->get_body();

		if ( empty( $custom_fields['custom_fields'] ) || ! is_array( $custom_fields['custom_fields'] ) ) {
			return [];
		}

		$custom_fields = $custom_fields['custom_fields'];

		if ( is_null( $field ) ) {
			return $custom_fields;
		}

		// Return plucked fields based on provided arguments.

		return wp_list_pluck( $custom_fields, $field, $index_key );
	}

	/**
	 * Register a custom field.
	 *
	 * @since 1.9.3
	 *
	 * @param string $name Name of the custom field.
	 *
	 * @return string
	 */
	public function register_custom_field( string $name ): string {

		$body = [
			'label' => $name,
			'type'  => 'string',
		];

		$response = $this->request->post( 'v3/contact_custom_fields', $body );

		return $response->get_body()['custom_field_id'] ?? '';
	}

	/**
	 * Get account summary.
	 *
	 * @since 1.9.3
	 *
	 * @return array
	 *
	 * @throws RuntimeException A request was failed.
	 */
	public function get_account_summary(): array {

		$response = $this->request->get( 'v3/account/summary' );

		if ( $response->has_errors() ) {
			throw new RuntimeException( esc_html( $response::get_error_message() ) );
		}

		return $response->get_body();
	}

	/**
	 * Search contact.
	 *
	 * @since 1.9.3
	 *
	 * @param array $contact_data Contact data.
	 *
	 * @return array Contact data array.
	 *
	 * @throws RuntimeException A request was failed.
	 */
	private function search_contact( array $contact_data ): array {

		$this->validate_contact_email( $contact_data );

		$args = [
			'limit' => 1,
			'email' => $contact_data['email_address'],
		];

		$response = $this->request->get( 'v3/contacts', $args );

		$body = $response->get_body();

		if ( empty( $body['contacts'][0] ) || ! is_array( $body['contacts'][0] ) ) {
			throw new RuntimeException( 'Contact not found.' );
		}

		return $body['contacts'][0];
	}

	/**
	 * Create or update contact.
	 *
	 * @since 1.9.3
	 *
	 * @param array $contact_data Contact data.
	 *
	 * @return array
	 *
	 * @throws RuntimeException A request was failed.
	 */
	public function subscribe_contact( array $contact_data ): array {

		$this->validate_subscribe_contact( $contact_data );

		$response = $this->request->post( 'v3/contacts/sign_up_form', $contact_data );

		$body = $response->get_body();

		if ( $response->has_errors() ) {
			throw new RuntimeException( esc_html( $response::get_error_message() ) );
		}

		if ( empty( $body['contact_id'] ) || empty( $body['action'] ) ) {
			throw new RuntimeException( 'Account was not created.' );
		}

		return $body;
	}

	/**
	 * Validate fields for subscribing action.
	 *
	 * @since 1.9.3
	 *
	 * @param array $contact_data Contact data.
	 *
	 * @throws InvalidArgumentException If the email address is empty.
	 */
	private function validate_subscribe_contact( array $contact_data ) {

		$this->validate_contact_email( $contact_data );

		foreach ( [ 'first_name', 'last_name', 'job_title', 'company_name', 'phone_number' ] as $key ) {
			if ( isset( $contact_data[ $key ] ) && ! is_string( $contact_data[ $key ] ) ) {
				throw new InvalidArgumentException( sprintf( 'The "%s" argument should be a string.', esc_html( $key ) ) );
			}
		}

		if ( isset( $contact_data['street_address'] ) ) {
			foreach ( (array) $contact_data['street_address'] as $key => $value ) {
				if ( ! is_string( $value ) ) {
					throw new InvalidArgumentException( sprintf( 'The "%s" argument should be a string.', esc_html( $key ) ) );
				}
			}
		}
	}

	/**
	 * Validate contact email.
	 *
	 * @since 1.9.3
	 *
	 * @param array $contact_data Contact data.
	 *
	 * @throws InvalidArgumentException If the email address is empty.
	 */
	private function validate_contact_email( array $contact_data ) {

		if ( empty( $contact_data['email_address'] ) || ! is_email( $contact_data['email_address'] ) ) {
			throw new InvalidArgumentException( 'Email address is required.' );
		}
	}

	/**
	 * Delete contact.
	 *
	 * @since 1.9.3
	 *
	 * @param array $contact_data Contact data.
	 *
	 * @return array Array with contact ID and action, empty array if no contact was found.
	 *
	 * @throws RuntimeException A request was failed.
	 */
	public function delete_contact( array $contact_data ): array {

		$contact = $this->search_contact( $contact_data );

		if ( empty( $contact['contact_id'] ) ) {
			throw new RuntimeException( 'Contact not found.' );
		}

		$endpoint = 'v3/contacts/' . $contact['contact_id'];
		$response = $this->request->delete( $endpoint );

		if ( $response->has_errors() ) {
			throw new RuntimeException( esc_html( $response::get_error_message() ) );
		}

		return [
			'contact_id' => $contact['contact_id'],
			'action'     => 'delete',
			'response'   => $response->get_body(),
		];
	}

	/**
	 * Unsubscribe contact.
	 *
	 * @since 1.9.3
	 *
	 * @param array $contact_data Contact data.
	 *
	 * @return array
	 *
	 * @throws InvalidArgumentException If some arguments are used wrong.
	 * @throws RuntimeException A request was failed.
	 */
	public function unsubscribe_contact( array $contact_data ): array {

		if ( isset( $contact_data['opt_out_reason'] ) && ! is_string( $contact_data['opt_out_reason'] ) ) {
			throw new InvalidArgumentException( sprintf( 'The "%s" argument should be a string.', 'opt_out_reason' ) );
		}

		$contact = $this->search_contact( $contact_data );

		if ( empty( $contact['contact_id'] ) ) {
			throw new RuntimeException( 'Contact not found.' );
		}

		$request_data = wp_parse_args(
			$contact,
			[
				'first_name'     => '',
				'last_name'      => '',
				'company_name'   => '',
				'job_title'      => '',
				'street_address' => '',
			]
		);

		$request_data['email_address'] = [
			'address'            => $contact['email_address']['address'] ?? '',
			'permission_to_send' => 'unsubscribed',
			'opt_out_reason'     => $contact_data['opt_out_reason'] ?? '',
		];

		$request_data['update_source'] = 'Contact';

		$response = $this->request->put( "v3/contacts/{$contact['contact_id']}", $request_data );

		if ( $response->has_errors() ) {
			throw new RuntimeException( esc_html( $response::get_error_message() ) );
		}

		return [
			'contact_id' => $contact['contact_id'],
			'action'     => 'unsubscribe',
			'response'   => $response->get_body(),
		];
	}

	/**
	 * Check if the access token is expired.
	 *
	 * @since 1.9.3
	 *
	 * @return bool
	 */
	private function is_expired_token(): bool {

		$expires_in = $this->account['expires_in'] ?? 0;

		/**
		 * Adding one minute to cover a very rare case when a few seconds are left,
		 * and the site runs multiple API requests.
		 * The last one could be outdated.
		 */
		return ( time() + MINUTE_IN_SECONDS ) > $expires_in;
	}

	/**
	 * Refresh access token.
	 *
	 * @since 1.9.3
	 *
	 * @throws InvalidArgumentException If the token cannot be refreshed.
	 */
	private function refresh_access_token() {

		if ( ! $this->is_expired_token() ) {
			return;
		}

		$response = wp_safe_remote_get(
			add_query_arg(
				[
					'api-version'   => 'v3',
					'refresh_token' => $this->account['refresh_token'],
				],
				ConstantContact::get_middleware_url()
			)
		);

		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $response_body['access_token'] ) ) {
			throw new InvalidArgumentException( esc_html__( 'Cannot refresh the token.', 'wpforms-lite' ) );
		}

		$this->account = array_merge(
			$this->account,
			[
				'access_token'  => $response_body['access_token'],
				'refresh_token' => $response_body['refresh_token'] ?? '',
				'expires_in'    => time() + (int) ( $response_body['expires_in'] ?? 0 ),
			]
		);

		wpforms_update_providers_options( Core::SLUG, $this->account, $this->account['id'] );
	}

	/**
	 * Get a contact list.
	 *
	 * @since 1.9.3
	 *
	 * @return array
	 */
	public function get_contact_list(): array {

		$response = $this->request->get( 'v3/contact_lists', [ 'limit' => 1000 ] );

		$body = $response->get_body();

		$lists = $body['lists'] ?? [];

		// Replace in lists key list_id with id.
		return array_map(
			static function ( $contact_list ) {

				return [
					'id'    => $contact_list['list_id'] ?? '',
					'label' => $contact_list['name'] ?? '',
				];
			},
			$lists
		);
	}

	/**
	 * Get list ids in v2 to v3 format.
	 *
	 * @since 1.9.3
	 *
	 * @param array $lists List received from Constant Contact v2.
	 *
	 * @return array
	 */
	public function get_contact_list_xrefs( array $lists ): array {

		$ids      = implode( ',', wp_list_pluck( $lists, 'id' ) );
		$response = $this->request->get(
			'v3/contact_lists/list_id_xrefs',
			[
				'sequence_ids' => $ids,
				'limit'        => 1000,
			]
		);

		$body = $response->get_body();

		$lists = $body['xrefs'] ?? [];

		return wp_list_pluck( $lists, 'list_id', 'sequence_id' );
	}
}
