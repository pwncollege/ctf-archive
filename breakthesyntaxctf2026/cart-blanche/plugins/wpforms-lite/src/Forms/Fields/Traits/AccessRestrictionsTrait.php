<?php

namespace WPForms\Forms\Fields\Traits;

/**
 * Access restrictions trait.
 *
 * @since 1.9.8
 */
trait AccessRestrictionsTrait {

	/**
	 * User roles.
	 *
	 * @since 1.9.8
	 *
	 * @var array
	 */
	private $user_roles = [];

	/**
	 * Add access restrictions options to the field.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field Field data and settings.
	 */
	private function access_restrictions_options( array $field ) {

		$access_restrictions = $this->field_element(
			'toggle',
			$field,
			[
				'slug'    => 'is_restricted',
				'value'   => ! empty( $field['is_restricted'] ) ? 1 : '',
				'desc'    => esc_html__( 'Enable File Access Restrictions', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Choose who can access the uploaded files.', 'wpforms-lite' ),
				'class'   => $this->get_access_restrictions_toggle_class(),
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'access_restrictions',
				'attrs'   => $this->get_access_restrictions_options_attrs(),
				'content' => $access_restrictions,
			]
		);

		// User Restriction.
		$this->user_restriction_options( $field );

		// Password Protection.
		$this->password_protection_options( $field );
	}

	/**
	 * Get access restrictions toggle class.
	 *
	 * @since 1.9.8
	 *
	 * @return string
	 */
	protected function get_access_restrictions_toggle_class(): string {

		return 'wpforms-file-upload-access-restrictions';
	}

	/**
	 * Get access restrictions options attributes.
	 *
	 * @since 1.9.8
	 *
	 * @return array
	 */
	protected function get_access_restrictions_options_attrs(): array {

		return [];
	}

	/**
	 * Add user restrictions options to the field.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field Field data and settings.
	 */
	private function user_restriction_options( array $field ) {

		$user_restrictions_value = $this->get_user_restrictions_value( $field );

		$this->add_user_restrictions_select( $field, $user_restrictions_value );

		$hide_user_restrictions = $this->should_hide_user_restrictions( $user_restrictions_value, $field );

		$this->add_user_roles_restrictions( $field, $hide_user_restrictions );
		$this->add_user_names_restrictions( $field, $hide_user_restrictions );
	}

	/**
	 * Get user restrictions value.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field Field data and settings.
	 *
	 * @return string
	 */
	private function get_user_restrictions_value( array $field ): string {

		return ! empty( $field['user_restrictions'] ) ? $field['user_restrictions'] : 'none';
	}

	/**
	 * Add user restrictions select to the field.
	 *
	 * @since 1.9.8
	 *
	 * @param array  $field                   Field data and settings.
	 * @param string $user_restrictions_value User restrictions value.
	 */
	private function add_user_restrictions_select( array $field, string $user_restrictions_value ) {

		$label = $this->field_element(
			'label',
			$field,
			[
				'slug'  => 'user_restrictions',
				'value' => esc_html__( 'User Restriction', 'wpforms-lite' ),
			],
			false
		);

		$select = $this->field_element(
			'select',
			$field,
			[
				'slug'    => 'user_restrictions',
				'value'   => $user_restrictions_value,
				'options' => [
					'none'   => esc_html__( 'None', 'wpforms-lite' ),
					'logged' => esc_html__( 'Logged-in Users', 'wpforms-lite' ),
				],
				'class'   => 'wpforms-file-upload-user-restrictions',
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'user_restrictions',
				'content' => $label . $select,
				'class'   => $this->is_restricted( $field ) ? '' : 'wpforms-hidden',
			]
		);
	}

	/**
	 * Check if user restrictions should be hidden.
	 *
	 * @since 1.9.8
	 *
	 * @param string $user_restrictions_value User restrictions value.
	 * @param array  $field                   Field data and settings.
	 *
	 * @return bool
	 */
	private function should_hide_user_restrictions( string $user_restrictions_value, array $field ): bool {

		return $user_restrictions_value === 'none' || ! $this->is_restricted( $field );
	}

	/**
	 * Add user roles restrictions to the field.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field                  Field data and settings.
	 * @param bool  $hide_user_restrictions Should user restrictions be hidden.
	 */
	private function add_user_roles_restrictions( array $field, bool $hide_user_restrictions ) {

		$label = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'user_roles_restrictions',
				'value'   => esc_html__( 'User Roles', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Select the user roles that can access the uploaded files.', 'wpforms-lite' ),
			],
			false
		);

		$select = $this->field_element(
			'select-multiple',
			$field,
			[
				'slug'      => 'user_roles_restrictions',
				'value'     => $this->get_selected_roles( $field ),
				'desc'      => esc_html__( 'All users with selected roles will be able to access the uploaded files.', 'wpforms-lite' ),
				'options'   => $this->get_user_roles(),
				'choicesjs' => false,
				'class'     => 'wpforms-file-upload-user-roles-select',
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'user_roles_restrictions',
				'content' => $label . $select,
				'class'   => $hide_user_restrictions ? 'wpforms-hidden' : '',
			]
		);
	}

	/**
	 * Get selected roles.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field Field data and settings.
	 *
	 * @return array
	 */
	private function get_selected_roles( array $field ): array {

		$selected_roles = ! empty( $field['user_roles_restrictions'] ) ? json_decode( $field['user_roles_restrictions'], true ) : [];

		array_unshift( $selected_roles, 'administrator' );

		return array_unique( $selected_roles );
	}

	/**
	 * Get user roles.
	 *
	 * @since 1.9.8
	 *
	 * @return array
	 */
	private function get_user_roles(): array {

		if ( empty( $this->user_roles ) ) {
			$roles = get_editable_roles();

			$this->user_roles = array_map(
				static function ( $item ) {

					return $item['name'];
				},
				$roles
			);
		}

		return $this->user_roles;
	}

	/**
	 * Add user names restrictions to the field.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field                  Field data and settings.
	 * @param bool  $hide_user_restrictions Should user restrictions be hidden.
	 */
	private function add_user_names_restrictions( array $field, bool $hide_user_restrictions ) {

		$label = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'user_names_restrictions',
				'value'   => esc_html__( 'Users', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Select the users that can access the uploaded files.', 'wpforms-lite' ),
			],
			false
		);

		$select = $this->field_element(
			'select-multiple',
			$field,
			[
				'slug'      => 'user_names_restrictions',
				'value'     => array_map( 'intval', $this->get_user_ids( $field ) ),
				'options'   => $this->get_user_list( $field ),
				'choicesjs' => false,
				'class'     => 'wpforms-file-upload-user-names-select',
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'user_names_restrictions',
				'content' => $label . $select,
				'class'   => $hide_user_restrictions ? 'wpforms-hidden' : '',
			]
		);
	}

	/**
	 * Get user ids.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field Field data and settings.
	 *
	 * @return array
	 */
	private function get_user_ids( array $field ): array {

		return ! empty( $field['user_names_restrictions'] ) ? json_decode( $field['user_names_restrictions'], true ) : [];
	}

	/**
	 * Get user list.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field Field data and settings.
	 *
	 * @return array
	 */
	private function get_user_list( array $field ): array {

		$user_ids = $this->get_user_ids( $field );

		return $this->get_selected_users( $user_ids );
	}

	/**
	 * Get selected users.
	 *
	 * @since 1.9.8
	 *
	 * @param array $user_ids User IDs.
	 *
	 * @return array
	 */
	private function get_selected_users( array $user_ids ): array {

		$selected_users = [];

		if ( ! empty( $user_ids ) ) {
			$users = get_users(
				[
					'include' => $user_ids,
					'fields'  => [ 'ID', 'display_name' ],
					'orderby' => 'include',
				]
			);

			$selected_users = wp_list_pluck( $users, 'display_name', 'ID' );
		}

		return $selected_users;
	}

	/**
	 * Add password protection options to the field.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field Field data and settings.
	 */
	private function password_protection_options( array $field ) {

		$this->add_password_toggle( $field );
		$this->add_password_label( $field );
		$this->add_password_fields( $field );
	}

	/**
	 * Add password toggle to the field.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field Field data and settings.
	 */
	private function add_password_toggle( array $field ) {

		$password = $this->field_element(
			'toggle',
			$field,
			[
				'slug'    => 'is_protected',
				'value'   => ! empty( $field['is_protected'] ) ? 1 : '',
				'desc'    => esc_html__( 'Password Protection', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Check this option to password protect the uploaded files.', 'wpforms-lite' ),
				'class'   => 'wpforms-file-upload-password-restrictions',
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'password_restrictions',
				'content' => $password,
				'class'   => $this->is_restricted( $field ) ? '' : 'wpforms-hidden',
			]
		);
	}

	/**
	 * Add password label to the field.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field Field data and settings.
	 */
	private function add_password_label( array $field ) {

		$password_label = $this->field_element(
			'label',
			$field,
			[
				'slug'    => 'protection_password_label',
				'value'   => esc_html__( 'Password', 'wpforms-lite' ),
				'tooltip' => esc_html__( 'Set a password to protect the uploaded files.', 'wpforms-lite' ),
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'protection_password_label',
				'content' => $password_label,
				'class'   => $this->is_protected( $field ) ? '' : 'wpforms-hidden',
			]
		);
	}

	/**
	 * Add password fields to the field.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field Field data and settings.
	 */
	private function add_password_fields( array $field ) {

		$password_field_row         = $this->get_password_field( $field );
		$password_confirm_field_row = $this->get_password_confirm_field( $field );

		$password_columns = $this->field_element(
			'row',
			$field,
			[
				'content' => $password_field_row . $password_confirm_field_row,
				'class'   => [
					'wpforms-field-options-columns',
				],
			],
			false
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'protection_password_columns',
				'content' => $password_columns,
				'class'   => $this->is_protected( $field ) ? '' : 'wpforms-hidden',
			]
		);
	}

	/**
	 * Add password field to the field.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field Field data and settings.
	 */
	private function get_password_field( array $field ): string {

		$clean_button = $this->field_element(
			'button',
			$field,
			[
				'slug'  => 'password_restrictions_clean_button',
				'value' => '<i class="fa fa-times-circle fa-lg"></i>',
				'class' => [
					'wpforms-file-upload-password-clean',
					'wpforms-hidden',
				],
				'data'  => [
					'field-id' => $field['id'],
				],
				'attrs' => [
					'tabindex' => '-1',
				],
			],
			false
		);

		$password_field = $this->field_element(
			'text',
			$field,
			[
				'slug'  => 'protection_password',
				'value' => ! empty( $field['protection_password'] ) ? $field['protection_password'] : '',
				'after' => esc_html__( 'Enter Password', 'wpforms-lite' ),
				'type'  => 'password',
				'class' => 'wpforms-file-upload-password',
				'attrs' => [
					'autocomplete' => 'new-password',
				],
			],
			false
		);

		return $this->field_element(
			'row',
			$field,
			[
				'slug'    => 'protection_password',
				'content' => $password_field . $clean_button,
			],
			false
		);
	}

	/**
	 * Add password confirm field to the field.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field Field data and settings.
	 */
	private function get_password_confirm_field( array $field ): string {

		$password_confirm_field = $this->field_element(
			'text',
			$field,
			[
				'slug'  => 'protection_password_confirm',
				'value' => ! empty( $field['protection_password_confirm'] ) ? $field['protection_password_confirm'] : '',
				'after' => esc_html__( 'Confirm Password', 'wpforms-lite' ),
				'type'  => 'password',
				'class' => 'wpforms-file-upload-password-confirm',
			],
			false
		);

		$password_confirm_field_error = $this->field_element(
			'row',
			$field,
			[
				'slug'    => 'protection_password_confirm_error',
				'content' => esc_html__( 'Passwords do not match', 'wpforms-lite' ),
				'class'   => [
					'wpforms-hidden',
					'wpforms-error',
					'wpforms-error-message',
				],
			],
			false
		);

		return $this->field_element(
			'row',
			$field,
			[
				'slug'    => 'protection_password_confirm',
				'content' => $password_confirm_field . $password_confirm_field_error,
			],
			false
		);
	}

	/**
	 * Check if the field has access restrictions enabled.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field Field data and settings.
	 *
	 * @return bool True if the field has access restrictions enabled, false otherwise.
	 */
	private function is_restricted( array $field ): bool {

		return ! empty( $field['is_restricted'] );
	}

	/**
	 * Check if the field has password protection enabled.
	 *
	 * @since 1.9.8
	 *
	 * @param array $field Field data and settings.
	 *
	 * @return bool True if the field has password protection enabled, false otherwise.
	 */
	private function is_protected( array $field ): bool {

		return ! empty( $field['is_protected'] );
	}
}
