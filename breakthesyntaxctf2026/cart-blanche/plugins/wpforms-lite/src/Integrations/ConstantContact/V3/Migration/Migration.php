<?php

namespace WPForms\Integrations\ConstantContact\V3\Migration;

use WP_Post;
use RuntimeException;
use WPForms_Constant_Contact;
use WPForms\Integrations\ConstantContact\V3\Core;
use WPForms\Integrations\ConstantContact\V3\Auth;
use WPForms\Integrations\ConstantContact\V3\Api\Api;
use WPForms\Integrations\ConstantContact\V3\ConstantContact;

/**
 * Migration class.
 *
 * The loader for the rest of classes in the namespace and manager
 * of the migration process.
 *
 * @since 1.9.3
 */
class Migration {

	/**
	 * List of migrated list ids in v2 => v3 format.
	 *
	 * @since 1.9.3
	 *
	 * @var array
	 */
	private $lists = [];

	/**
	 * New account data.
	 *
	 * @since 1.9.3
	 *
	 * @var array
	 */
	private $new_account;

	/**
	 * Form data and settings.
	 *
	 * @since 1.9.3
	 *
	 * @var array
	 */
	private $form_data;

	/**
	 * Index of the first name custom field in the new account.
	 *
	 * @since 1.9.3
	 *
	 * @var int|null
	 */
	private $first_name_index;

	/**
	 * Index of the last name custom field in the new account.
	 *
	 * @since 1.9.3
	 *
	 * @var int|null
	 */
	private $last_name_index;

	/**
	 * Init.
	 *
	 * @since 1.9.3
	 */
	public function init() {

		$this->force_migration();

		if ( ConstantContact::get_current_version() >= 3 ) {
			return;
		}

		$this->display_prompt();
		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.9.3
	 */
	private function hooks() {

		// Add ajax action.
		add_action( 'wp_ajax_wpforms_constant_contact_migration_prompt', [ $this, 'ajax_start_migration' ] );
		add_action( 'update_option_wpforms_providers', [ $this, 'update_providers_options_after' ], 10, 2 );

		add_filter( 'wpforms_integrations_constant_contact_v3_auth_create_account_data', [ $this, 'migrate_account_finish' ] );
	}

	/**
	 * Force migration.
	 *
	 * @since 1.9.3
	 */
	private function force_migration() {

		if ( ! wpforms_is_admin_page( 'settings', 'integrations' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$key = 'constant_contact-force-migration';

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET[ $key ] ) ) {
			return;
		}

		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$_SERVER['REQUEST_URI'] = remove_query_arg( $key, wp_unslash( $_SERVER['REQUEST_URI'] ) );
		}

		delete_option( ConstantContact::VERSION_OPTION );
	}

	/**
	 * Display migration prompt.
	 *
	 * @since 1.9.3
	 */
	private function display_prompt() {

		if ( ! wpforms_is_admin_page( 'settings', 'integrations' ) ) {
			return;
		}

		if ( $this->migrated_accounts_exist() ) {
			return;
		}

		$notice_obj = wpforms()->obj( 'notice' );

		if ( ! $notice_obj ) {
			return;
		}

		$notice_obj::error(
			wp_kses(
				sprintf(
				/* translators: %1$s - link to the migration page, %2$s - closing HTML tag. */
					__( 'You need to migrate your existing forms to the new version of the Constant Contact addon. Please %1$s click here%2$s to start the migration.', 'wpforms-lite' ),
					'<a href="#" rel="noopener noreferrer" id="wpforms-settings-constant-contact-v3-migration-prompt-link">',
					'</a>'
				),
				[
					'a' => [
						'href' => [],
						'rel'  => [],
						'id'   => [],
					],
				]
			)
		);
	}

	/**
	 * Replace account ID if it was migrated.
	 *
	 * @since 1.9.3
	 *
	 * @param array $new_account New account data.
	 *
	 * @return array
	 */
	public function migrate_account_finish( array $new_account ): array {

		$accounts = wpforms_get_providers_options( Core::SLUG );

		foreach ( $accounts as $account_id => $account ) {
			if (
				$account['email'] === $new_account['email']
				&& ! empty( $account['accounts'] )
			) {
				$new_account['id'] = $account_id;
				$this->new_account = $new_account;

				$this->migrate_forms( $account );

				break;
			}
		}

		return $new_account;
	}

	/**
	 * Finish migration by setting the version to 3.
	 *
	 * @since 1.9.3
	 */
	public static function finish_migration() {

		update_option( ConstantContact::VERSION_OPTION, 3 );
	}

	/**
	 * Update providers options after migration.
	 *
	 * @since 1.9.3
	 *
	 * @param mixed $old_value Old providers options.
	 * @param mixed $new_value New providers options.
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function update_providers_options_after( $old_value, $new_value ) {

		if ( empty( wpforms_get_providers_options( 'constant-contact' ) ) ) {
			self::finish_migration();

			return;
		}

		if ( ! is_array( $new_value ) || empty( $new_value[ Core::SLUG ] ) ) {
			return;
		}

		if ( $this->migrated_accounts_exist() ) {
			return;
		}

		self::finish_migration();
	}

	/**
	 * Check if some migrated accounts have been already created.
	 *
	 * @since 1.9.3
	 *
	 * @return bool
	 */
	private function migrated_accounts_exist(): bool {

		$accounts = wpforms_get_providers_options( Core::SLUG );

		foreach ( $accounts as $account ) {
			if ( ! empty( $account['accounts'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Migrate all accounts.
	 *
	 * @since 1.9.3
	 */
	public function ajax_start_migration() {

		check_ajax_referer( Auth::NONCE, 'nonce' );

		if ( ! wpforms_current_user_can() ) {
			wp_send_json_error( esc_html__( 'You do not have permission to perform this action.', 'wpforms-lite' ) );
		}

		$accounts = wpforms_get_providers_options();

		// No accounts to migrate.
		if ( empty( $accounts['constant-contact'] ) ) {
			self::finish_migration();

			wp_send_json_success();
		}

		foreach ( $accounts['constant-contact'] as $account_id => $account ) {
			$this->migrate_account_start( $account_id, $account, $accounts );
		}

		// If no accounts were migrated because v2 accounts were invalid, we switch to the new version.
		if ( empty( $accounts[ Core::SLUG ] ) ) {
			self::finish_migration();

			wp_send_json_success();
		}

		update_option( 'wpforms_providers', $accounts );

		wp_send_json_success();
	}

	/**
	 * Migrate a specific v2 account to v3.
	 *
	 * @since 1.9.3
	 *
	 * @param string $account_id Account ID.
	 * @param array  $account    Current account data.
	 * @param array  $accounts   List of all providers' accounts.
	 */
	private function migrate_account_start( string $account_id, array $account, array &$accounts ) {

		static $migrated_access_tokens = [];

		// It was possible to create an account without an access token.
		if ( empty( $account['access_token'] ) ) {
			return;
		}

		// It was possible to create a few accounts with the same access token.
		// We merge them into one in the new version.
		if ( isset( $migrated_access_tokens[ $account['access_token'] ] ) ) {
			$created_account_id = $migrated_access_tokens[ $account['access_token'] ];

			$accounts['constant-contact-v3'][ $created_account_id ]['accounts'][] = $account_id;

			return;
		}

		$email = $this->get_account_email( $account );

		// We skip an account if we can't receive email, in the case the access_token isn't valid.
		if ( empty( $email ) ) {
			return;
		}

		$migrated_access_tokens[ $account['access_token'] ] = $account_id;

		$accounts['constant-contact-v3'][ $account_id ] = [
			'id'           => $account_id,
			'accounts'     => [ $account_id ],
			'access_token' => $account['access_token'],
			'date'         => 0,
			'label'        => $account['label'] ?? $email,
			'email'        => $email,
		];
	}

	/**
	 * Get email from an account.
	 *
	 * @since 1.9.3
	 *
	 * @param array $account Account data.
	 *
	 * @return string
	 */
	private function get_account_email( array $account ): string {

		$old_provider = new WPForms_Constant_Contact();

		$old_provider->access_token = $account['access_token'];

		$account_info = $old_provider->get_account_information();

		if ( is_wp_error( $account_info ) ) {
			return '';
		}

		return $account_info['email'] ?? '';
	}

	/**
	 * Migrate forms.
	 *
	 * @since 1.9.3
	 *
	 * @param array $old_account Old account.
	 *
	 * @return void
	 */
	private function migrate_forms( array $old_account ) {

		if ( ! isset( $old_account['accounts'], $old_account['access_token'] ) ) {
			return;
		}

		$forms = $this->get_forms( (array) $old_account['accounts'] );

		if ( empty( $forms ) ) {
			return;
		}

		$this->lists = $this->get_lists_xhref( $this->new_account, $old_account['access_token'] );

		foreach ( $forms as $form ) {
			$this->migrate_form( $form );
		}
	}

	/**
	 * Get migrated forms.
	 *
	 * @since 1.9.3
	 *
	 * @param array $old_account_ids Old v2 account ids.
	 *
	 * @return array
	 * @noinspection SqlResolve
	 */
	private function get_forms( array $old_account_ids ): array {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$forms = $wpdb->get_col(
			$wpdb->prepare(
				'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_type = "wpforms" AND post_content REGEXP %s',
				implode( '|', $old_account_ids )
			)
		);

		if ( empty( $forms ) ) {
			return [];
		}

		$form_ids = array_map( 'absint', $forms );
		$form_obj = wpforms()->obj( 'form' );

		if ( ! $form_obj ) {
			return [];
		}

		return (array) $form_obj->get(
			'',
			[
				'numberposts'            => -1,
				'orderby'                => 'post__in',
				'post__in'               => $form_ids,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'no_found_rows'          => true,
			]
		);
	}

	/**
	 * Copy connections from v2 to v3 in proper format.
	 *
	 * @since 1.9.3
	 *
	 * @param WP_Post $form Form object.
	 */
	private function migrate_form( WP_Post $form ) {

		$this->form_data = wpforms_decode( $form->post_content );

		// Nothing to migrate.
		if ( empty( $this->form_data['providers']['constant-contact'] ) ) {
			return;
		}

		$migrated_connections = $this->form_data['providers'][ Core::SLUG ] ?? [];

		// All connections were migrated but account migration was interrupted by timeout or an error.
		if ( count( $this->form_data['providers']['constant-contact'] ) === count( $migrated_connections ) ) {
			return;
		}

		$this->form_data['providers'][ Core::SLUG ] = array_merge( $migrated_connections, $this->get_new_connections() );

		$form_obj = wpforms()->obj( 'form' );

		if ( ! $form_obj ) {
			return;
		}

		$form_obj->update( $this->form_data['id'], $this->form_data );
	}

	/**
	 * Modify v2 connections to v3.
	 *
	 * @since 1.9.3
	 *
	 * @return array
	 */
	private function get_new_connections(): array {

		$old_connections = $this->form_data['providers']['constant-contact'] ?? [];
		$new_connections = [];

		foreach ( $old_connections as $connection_id => $connection ) {
			$new_connection_id = str_replace( 'connection_', '', $connection_id );
			$connection        = wp_parse_args(
				$connection,
				[
					'connection_name'   => '',
					'account_id'        => '',
					'list_id'           => '',
					'fields'            => [],
					'conditional_logic' => '',
					'conditional_type'  => '',
					'conditionals'      => [],
				]
			);

			// The connection is related to another account, skip it.
			if ( $this->new_account['id'] !== $connection['account_id'] ) {
				continue;
			}

			reset( $this->lists );

			$new_connections[ $new_connection_id ] = [
				'id'                => $new_connection_id,
				'name'              => $connection['connection_name'],
				'account_id'        => $connection['account_id'],
				'action'            => 'subscribe',
				'list'              => $this->lists[ $connection['list_id'] ] ?? key( $this->lists ),
				'email'             => explode( '.', $connection['fields']['email'] ?? '' )[0],
				'fields_meta'       => $this->get_connection_custom_fields( $connection['fields'] ),
				'conditional_logic' => $connection['conditional_logic'],
				'conditional_type'  => $connection['conditional_type'],
				'conditionals'      => $connection['conditionals'],
			];
		}

		return $new_connections;
	}

	/**
	 * Get custom fields.
	 *
	 * @since 1.9.3
	 *
	 * @param array $custom_fields Custom fields v2.
	 *
	 * @return array
	 */
	private function get_connection_custom_fields( array $custom_fields ): array {

		$fields_meta   = [];
		$custom_fields = $this->sort_custom_fields( $custom_fields );

		foreach ( $custom_fields as $key => $value ) {
			if ( $key === 'email' ) {
				continue;
			}

			$value_parts = explode( '.', $value );
			$field_id    = $value_parts[0];

			if ( wpforms_is_empty_string( $field_id ) ) {
				continue;
			}

			$fields_meta = $this->update_fields_meta( $fields_meta, $field_id, $key, $value_parts );
		}

		return $fields_meta;
	}

	/**
	 * Move $custom_fields['full_name'] at the beginning of the array.
	 *
	 * Thanks to this, if first name and last name are defined, next iterations
	 * of this array will replace full_name - backward compatibility sustained.
	 *
	 * @since 1.9.3
	 *
	 * @param array $custom_fields Custom fields.
	 *
	 * @return array
	 */
	private function sort_custom_fields( array $custom_fields ): array {

		if ( ! isset( $custom_fields['full_name'] ) || wpforms_is_empty_string( $custom_fields['full_name'] ) ) {
			return $custom_fields;
		}

		$full_name = $custom_fields['full_name'];

		unset( $custom_fields['full_name'] );

		return [ 'full_name' => $full_name ] + $custom_fields;
	}

	/**
	 * Update fields meta.
	 *
	 * @since 1.9.3
	 *
	 * @param array  $fields_meta Fields meta.
	 * @param string $field_id    Field ID.
	 * @param string $key         Key.
	 * @param array  $value_parts Value parts.
	 *
	 * @return array
	 */
	private function update_fields_meta( array $fields_meta, string $field_id, string $key, array $value_parts ): array {

		if ( $this->form_data['fields'][ $field_id ]['type'] === 'name' ) {
			$name_field = $this->handle_name_field( $fields_meta, $field_id, $key, $value_parts );

			if ( is_array( $name_field ) ) {
				return $name_field;
			}

			$field_id = $name_field;
		}

		$keys_to_rename = [
			'work_phone' => 'phone',
			'url'        => $this->get_url_field_id(),
		];

		$new_key = $keys_to_rename[ $key ] ?? $key;

		$fields_meta[ $this->get_meta_next_index( $fields_meta, $new_key ) ] = [
			'name'     => $new_key,
			'field_id' => $field_id,
		];

		return $fields_meta;
	}

	/**
	 * Handle name field.
	 *
	 * @since 1.9.3
	 *
	 * @param array  $fields_meta Fields meta.
	 * @param string $field_id    Field ID.
	 * @param string $key         Key.
	 * @param array  $value_parts Value parts.
	 *
	 * @return string|array
	 */
	private function handle_name_field( array $fields_meta, string $field_id, string $key, array $value_parts ) {

		if ( $value_parts[1] === 'value' ) {
			$value_parts[1] = 'full';
		}

		if ( $key === 'full_name' ) {
			return $this->update_full_name( $fields_meta, $field_id, $value_parts );
		}

		$field_id .= '.' . $value_parts[1];

		return $field_id;
	}

	/**
	 * Update full name meta.
	 *
	 * @since 1.9.3
	 *
	 * @param array  $fields_meta Fields meta.
	 * @param string $field_id    Field ID.
	 * @param array  $value_parts Value parts.
	 *
	 * @return array
	 */
	private function update_full_name( array $fields_meta, string $field_id, array $value_parts ): array {

		$field = $this->form_data['fields'][ $field_id ] ?? [];

		$is_simple = ! isset( $field['format'] ) || $field['format'] === 'simple';

		$first_name_field_id = $is_simple ? $field_id . '.' . $value_parts[1] : $field_id . '.first';

		$fields_meta[] = [
			'name'     => 'first_name',
			'field_id' => $first_name_field_id,
		];

		$this->first_name_index = count( $fields_meta ) - 1;

		if ( $is_simple ) {
			return $fields_meta;
		}

		$last_name_field_id = $field_id . '.last';

		$fields_meta[] = [
			'name'     => 'last_name',
			'field_id' => $last_name_field_id,
		];

		$this->last_name_index = count( $fields_meta ) - 1;

		return $fields_meta;
	}

	/**
	 * Get next index for a custom field.
	 *
	 * @since 1.9.3
	 *
	 * @param array  $fields_meta Fields meta.
	 * @param string $key         Key.
	 */
	private function get_meta_next_index( array $fields_meta, string $key ): int {

		if ( $key === 'first_name' ) {
			return $this->first_name_index ?? count( $fields_meta );
		}

		if ( $key === 'last_name' ) {
			return $this->last_name_index ?? count( $fields_meta );
		}

		return count( $fields_meta );
	}

	/**
	 * Get URL custom field ID from the new account.
	 *
	 * Returns the id in the new format.
	 *
	 * @since 1.9.3
	 *
	 * @return string
	 */
	private function get_url_field_id(): string {

		static $field_id;

		if ( $field_id ) {
			return $field_id;
		}

		$custom_fields = ( new Api( $this->new_account ) )->get_custom_fields( 'custom_field_id', 'name' );

		$field_id = $custom_fields['custom_field_1'] ?? $this->register_url_field();

		return $field_id;
	}

	/**
	 * Get an array of list v2 ids to v3 ids.
	 *
	 * @since 1.9.3
	 *
	 * @param array  $new_account     New account data.
	 * @param string $access_token_v2 Access token for v2.
	 *
	 * @return array
	 *
	 * @throws RuntimeException Can't receive v2 lists and finish migration.
	 */
	private function get_lists_xhref( array $new_account, string $access_token_v2 ): array {

		$old_provider               = new WPForms_Constant_Contact();
		$old_provider->access_token = $access_token_v2;

		$old_lists = $old_provider->api_lists();

		if ( is_wp_error( $old_lists ) ) {
			throw new RuntimeException( esc_html__( 'Can\'t receive v2 lists and finish migration.', 'wpforms-lite' ) );
		}

		return ( new Api( $new_account ) )->get_contact_list_xrefs( (array) $old_lists );
	}

	/**
	 * Register URL custom field.
	 *
	 * @since 1.9.3
	 *
	 * @return string
	 */
	private function register_url_field(): string {

		return ( new Api( $this->new_account ) )->register_custom_field( 'Website / URL' );
	}
}
