<?php

namespace WPForms\Integrations\ConstantContact\V3;

use Exception;
use RuntimeException;
use WPForms\Tasks\Meta;
use WPForms\Integrations\ConstantContact\V3\Api\Api;
use WPForms\Providers\Provider\Process as ProcessAbstract;
use WPForms\Integrations\ConstantContact\V3\Settings\FieldMapping;

/**
 * Class Process.
 *
 * @since 1.9.3
 */
class Process extends ProcessAbstract {

	/**
	 * Async task name.
	 *
	 * @since 1.9.3
	 */
	const TASK_NAME = 'wpforms_constant_contact_v3_process';

	/**
	 * Connection data.
	 *
	 * @since 1.9.3
	 *
	 * @var array
	 */
	private $connection;

	/**
	 * API client.
	 *
	 * @since 1.9.3
	 *
	 * @var Api
	 */
	private $api;

	/**
	 * Process constructor.
	 *
	 * @since 1.9.3
	 *
	 * @param Core $core Core instance of the provider class.
	 */
	public function __construct( Core $core ) {

		parent::__construct( $core );

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.9.3
	 */
	public function hooks() {

		add_action( self::TASK_NAME, [ $this, 'task_async_action_trigger' ] );
	}

	/**
	 * Process the form.
	 *
	 * @since 1.9.3
	 *
	 * @param array $fields    Submitted fields.
	 * @param array $entry     Saved entry data.
	 * @param array $form_data Form data and settings.
	 * @param int   $entry_id  Saved entry ID.
	 */
	public function process( $fields, $entry, $form_data, $entry_id ) {

		if ( empty( $form_data['providers'][ $this->core->slug ] ) ) {
			return;
		}

		$this->fields    = $fields;
		$this->entry     = $entry;
		$this->form_data = $form_data;
		$this->entry_id  = $entry_id;

		foreach ( $this->form_data['providers'][ $this->core->slug ] as $connection ) {
			$this->connection = $connection;

			if ( ! $this->process_conditionals( $this->fields, $this->form_data, $connection ) ) {
				$this->log_errors(
					sprintf(
						'The Constant Contact connection %s was not processed due to conditional logic.',
						$connection['name'] ?? ''
					)
				);

				continue;
			}

			if ( empty( $this->connection['action'] ) ) {
				continue;
			}

			$this->create_connection_async_task();
		}
	}

	/**
	 * Create an async task for a specific connection.
	 *
	 * @since 1.9.3
	 */
	private function create_connection_async_task() {

		$tasks = wpforms()->obj( 'tasks' );

		if ( ! $tasks ) {
			return;
		}

		$tasks
			->create( self::TASK_NAME )->async()
			->params( $this->connection, $this->fields, $this->form_data, $this->entry_id )
			->register();
	}

	/**
	 * Process the addon async tasks.
	 *
	 * @since 1.9.3
	 *
	 * @param int|mixed $meta_id Task meta ID.
	 */
	public function task_async_action_trigger( $meta_id ) {

		$meta = $this->get_task_meta( (int) $meta_id );

		// We expect a certain type and number of params.
		if ( count( $meta ) !== 4 ) {
			return;
		}

		// We expect a certain metadata structure for this task.
		list( $this->connection, $this->fields, $this->form_data, $this->entry_id ) = $meta;

		try {
			$this->process_action();
		} catch ( Exception $e ) {
			$this->log_errors( $e->getMessage() );
		}
	}

	/**
	 * Processes single action.
	 *
	 * @since 1.9.3
	 *
	 * @throws Exception If something went wrong.
	 *
	 * @uses Api::unsubscribe_contact()
	 * @uses Api::delete_contact()
	 * @uses Api::subscribe_contact()
	 */
	private function process_action() {

		$this->api    = $this->get_api_client();
		$contact_data = $this->prepare_contact_data();
		$api_method   = $this->connection['action'] . '_contact';

		if ( ! method_exists( $this->api, $api_method ) ) {
			return;
		}

		$response = $this->api->$api_method( $contact_data );

		/**
		 * Fire when request was sent successfully or not.
		 *
		 * @since 1.9.3
		 *
		 * @param array $response   Response data.
		 * @param array $connection Connection data.
		 * @param array $args       Additional arguments.
		 */
		do_action(
			'wpforms_integrations_constant_contact_v3_process_completed',
			$response,
			$this->connection,
			[
				'form_data' => $this->form_data,
				'fields'    => $this->fields,
				'entry'     => $this->entry,
			]
		);
	}

	/**
	 * Prepare contact data.
	 *
	 * @since 1.9.3
	 *
	 * @return array
	 */
	private function prepare_contact_data(): array {

		$field_mapping = new FieldMapping( $this->connection, $this->fields );

		if ( $this->connection['action'] === 'subscribe' ) {
			return array_filter(
				[
					'email_address'    => $field_mapping->get_field( 'email' ),
					'first_name'       => $field_mapping->get_meta_field( 'first_name' ),
					'last_name'        => $field_mapping->get_meta_field( 'last_name' ),
					'job_title'        => $field_mapping->get_meta_field( 'job_title' ),
					'company_name'     => $field_mapping->get_meta_field( 'company_name' ),
					'phone_number'     => $field_mapping->get_meta_field( 'phone' ),
					'street_address'   => $field_mapping->get_street_address(),
					'list_memberships' => [ $field_mapping->get_list_id() ],
					'custom_fields'    => $field_mapping->get_custom_fields( $this->api->get_custom_fields( 'type', 'custom_field_id' ) ),
				]
			);
		}

		if ( $this->connection['action'] === 'unsubscribe' ) {
			return [
				'email_address'  => $field_mapping->get_field( 'email' ),
				'opt_out_reason' => $field_mapping->get_field( 'opt_out_reason' ),
			];
		}

		return [
			'email_address' => $field_mapping->get_field( 'email' ),
		];
	}

	/**
	 * Get task meta data.
	 *
	 * @since 1.9.3
	 *
	 * @param int $meta_id Task meta ID.
	 *
	 * @return array
	 */
	private function get_task_meta( int $meta_id ): array {

		$task_meta = new Meta();
		$meta      = $task_meta->get( $meta_id );

		// We should actually receive something.
		if ( empty( $meta ) || empty( $meta->data ) ) {
			return [];
		}

		return (array) $meta->data;
	}

	/**
	 * Get the API client based on connection and provider options.
	 *
	 * @since 1.9.3
	 *
	 * @return Api
	 *
	 * @throws RuntimeException If account ID is missing or account doesn't exist.
	 */
	private function get_api_client(): Api {

		if ( empty( $this->connection['account_id'] ) ) {
			throw new RuntimeException( 'Account ID is missing in connection.' );
		}

		$provider_settings = wpforms_get_providers_options( $this->core->slug );

		return new Api( $provider_settings[ $this->connection['account_id'] ] ?? [] );
	}

	/**
	 * Log an API-related error with all the data.
	 *
	 * @since 1.9.3
	 *
	 * @param string $error_message Error message.
	 */
	private function log_errors( string $error_message ) {

		wpforms_log(
			'Submission Constant Contact failed (#' . $this->entry_id . ')',
			[
				'message'    => $error_message,
				'connection' => $this->connection,
			],
			[
				'type'    => [ 'provider', 'error' ],
				'parent'  => $this->entry_id,
				'form_id' => $this->form_data['id'],
			]
		);
	}
}
