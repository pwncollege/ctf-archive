<?php

namespace WPForms\Admin\Builder;

use WPForms\Requirements\Requirements;

/**
 * Addons class.
 *
 * @since 1.9.2
 */
class Addons {

	/**
	 * List of addon options.
	 *
	 * @since 1.9.2
	 */
	private const FIELD_OPTIONS = [
		'calculations'  => [
			'calculation_code',
			'calculation_code_js',
			'calculation_code_php',
			'calculation_is_enabled',
		],
		'form-locker'   => [
			'unique_answer',
		],
		'geolocation'   => [
			'display_map',
			'enable_address_autocomplete',
			'map_position',
		],
		'surveys-polls' => [
			'survey',
		],
		'quiz'          => [
			'quiz_enabled',
			'choices' => [
				'quiz_personality',
				'quiz_weight',
			],
		],
	];

	/**
	 * Initialize.
	 *
	 * @since 1.9.2
	 *
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 */
	public function init() {

		$this->hooks();
	}

	/**
	 * Add hooks.
	 *
	 * @since 1.9.2
	 */
	private function hooks(): void {

		add_filter( 'wpforms_save_form_args', [ $this, 'save_disabled_addons_options' ], 10, 3 );
	}


	/**
	 * Field's options added by an addon can be deleted when the addon is deactivated or have incompatible status.
	 * The options are fully controlled by the addon when addon is active and compatible.
	 *
	 * @since 1.9.2
	 *
	 * @param array|mixed $post_data Post data.
	 *
	 * @return array
	 */
	public function save_disabled_addons_options( $post_data ): array {

		$post_data = (array) $post_data;
		$form_obj  = wpforms()->obj( 'form' );
		$form_data = json_decode( stripslashes( $post_data['post_content'] ?? '' ), true );
		$form_id   = $form_data['id'] ?? '';

		if ( ! $form_obj || ! $form_id ) {
			return $post_data;
		}

		$previous_form_data   = $form_obj->get( $form_id, [ 'content_only' => true ] );
		$not_validated_addons = Requirements::get_instance()->get_not_validated_addons();

		if ( empty( $previous_form_data ) || empty( $not_validated_addons ) ) {
			return $post_data;
		}

		foreach ( $not_validated_addons as $path ) {
			$slug = str_replace( 'wpforms-', '', basename( $path, '.php' ) );

			$this->preserve_addon( $slug, $form_data, $previous_form_data );
		}

		$this->preserve_providers( $form_data, $previous_form_data );
		$this->preserve_payments( $form_data, $previous_form_data );

		$post_data['post_content'] = wpforms_encode( $form_data );

		return $post_data;
	}

	/**
	 * Preserve addon fields, settings, panels, notifications, etc.
	 *
	 * @since 1.9.3
	 *
	 * @param string $slug               Addon slug.
	 * @param array  $form_data          Form data.
	 * @param array  $previous_form_data Previous form data.
	 *
	 * @return void
	 */
	private function preserve_addon( string $slug, array &$form_data, array $previous_form_data ): void {

		if ( ! empty( $form_data['fields'] ) && ! empty( $previous_form_data['fields'] ) ) {
			$this->preserve_addon_fields_settings( $slug, $form_data['fields'], $previous_form_data['fields'] );
		}

		$this->preserve_addon_panel( $slug, $form_data, $previous_form_data );

		if ( ! empty( $form_data['settings'] ) && ! empty( $previous_form_data['settings'] ) ) {
			$this->preserve_addon_settings( $slug, $form_data['settings'], $previous_form_data['settings'] );
		}

		if ( ! empty( $form_data['settings']['notifications'] ) && ! empty( $previous_form_data['settings']['notifications'] ) ) {
			$this->preserve_addon_notifications(
				$slug,
				$form_data['settings']['notifications'],
				$previous_form_data['settings']['notifications']
			);
		}
	}

	/**
	 * Preserve addon fields.
	 *
	 * @since 1.9.5
	 *
	 * @param string $slug            Addon slug.
	 * @param array  $new_fields      Form fields settings.
	 * @param array  $previous_fields Previous form fields settings.
	 *
	 * @return void
	 */
	private function preserve_addon_fields_settings( string $slug, array &$new_fields, array $previous_fields ): void {

		foreach ( $previous_fields as $field_id => $previous_field ) {
			$new_field = $new_fields[ $field_id ] ?? [];

			if ( empty( $new_field ) ) {
				continue;
			}

			$this->preserve_addon_field_settings( $slug, $new_field, $previous_field );

			$new_fields[ $field_id ] = $new_field;
		}
	}

	/**
	 * Preserve addon field.
	 *
	 * @since 1.9.5
	 *
	 * @param string $slug           Addon slug.
	 * @param array  $new_field      Previous form fields settings.
	 * @param array  $previous_field Form fields settings.
	 *
	 * @return void
	 */
	private function preserve_addon_field_settings( string $slug, array &$new_field, array $previous_field ): void {

		$prefix           = $this->prepare_prefix( $slug );
		$changed_settings = array_diff_key( $previous_field, $new_field );
		$preserve_fields  = self::FIELD_OPTIONS[ $slug ] ?? [];

		foreach ( $changed_settings as $setting_name => $setting_value ) {
			if (
				strpos( $setting_name, $prefix ) === 0 ||
				in_array( $setting_name, $preserve_fields, true )
			) {
				$new_field[ $setting_name ] = $setting_value;
			}
		}

		if (
			! empty( $preserve_fields['choices'] ) &&
			is_array( $preserve_fields['choices'] ) &&
			! empty( $new_field['choices'] ) &&
			is_array( $new_field['choices'] )
		) {
			$this->preserve_addon_field_choices_settings( $preserve_fields['choices'], $new_field, $previous_field );
		}
	}

	/**
	 * Preserve addon field choices settings.
	 *
	 * @since 1.9.9
	 *
	 * @param array $choice_settings Choice settings.
	 * @param array $new_field       Previous form fields settings.
	 * @param array $previous_field  Form fields settings.
	 *
	 * @return void
	 */
	private function preserve_addon_field_choices_settings( array $choice_settings, array &$new_field, array $previous_field ): void {

		if ( ! isset( $previous_field['choices'] ) || ! is_array( $previous_field['choices'] ) ) {
			return;
		}

		$previous_choices = $previous_field['choices'];

		foreach ( $new_field['choices'] as $choice_id => $choice ) {
			foreach ( $choice_settings as $setting_name ) {
				if ( isset( $previous_choices[ $choice_id ][ $setting_name ] ) ) {
					$new_field['choices'][ $choice_id ][ $setting_name ] = $previous_choices[ $choice_id ][ $setting_name ];
				}
			}
		}
	}

	/**
	 * Preserve addon panel.
	 *
	 * @since 1.9.3
	 *
	 * @param string $slug               Addon slug.
	 * @param array  $form_data          Form data.
	 * @param array  $previous_form_data Previous form data.
	 */
	private function preserve_addon_panel( string $slug, array &$form_data, array $previous_form_data ): void {

		$panel = $this->prepare_prefix( $slug );

		// The addon settings stored its own panel, e.g., $form_data[lead_forms], $form_data[webhooks], etc.
		if ( ! empty( $previous_form_data[ $panel ] ) ) {
			$form_data[ $panel ] = $previous_form_data[ $panel ];
		}
	}

	/**
	 * Preserve addon settings stored inside the settings panel with a specific prefix.
	 * e.g. $form_data[settings][{$prefix}_enabled], $form_data[settings][{$prefix}_email], etc.
	 *
	 * @since 1.9.4
	 *
	 * @param string $slug              Addon option prefix.
	 * @param array  $new_settings      Form settings.
	 * @param array  $previous_settings Previous form settings.
	 */
	private function preserve_addon_settings( string $slug, array &$new_settings, array $previous_settings ): void {

		$prefix = $this->prepare_prefix( $slug );

		static $legacy_options = [
			'offline_forms'     => [ 'offline_form' ],
			'user_registration' => [ 'user_login_hide', 'user_reset_hide' ],
			'surveys_polls'     => [ 'survey_enable', 'poll_enable' ],
		];

		// BC: User Registration addon has `registration_` prefix instead of `user_registration`.
		if ( $prefix === 'user_registration' ) {
			$prefix = 'registration';
		}

		foreach ( $previous_settings as $setting_name => $value ) {
			if ( strpos( $setting_name, $prefix ) === 0 ) {
				$new_settings[ $setting_name ] = $value;

				continue;
			}

			// BC: The options don't have a prefix and hard-coded in the `$legacy_options` variable.
			if ( isset( $legacy_options[ $prefix ] ) && in_array( $setting_name, $legacy_options[ $prefix ], true ) ) {
				$new_settings[ $setting_name ] = $value;
			}
		}
	}

	/**
	 * Preserve addon notifications.
	 *
	 * @since 1.9.4
	 *
	 * @param string $slug                   Addon slug.
	 * @param array  $new_notifications      List of form notifications.
	 * @param array  $previous_notifications Previously saved list of form notifications.
	 *
	 * @return void
	 */
	private function preserve_addon_notifications( string $slug, array &$new_notifications, array $previous_notifications ): void {

		$prefix = $this->prepare_prefix( $slug );

		foreach ( $previous_notifications as $notification_id => $notification_settings ) {
			if ( empty( $new_notifications[ $notification_id ] ) ) {
				continue;
			}

			$changed_notification_settings = array_diff_key( $notification_settings, $new_notifications[ $notification_id ] );

			foreach ( $changed_notification_settings as $setting_name => $value ) {
				if ( strpos( $setting_name, $prefix ) === 0 ) {
					$new_notifications[ $notification_id ][ $setting_name ] = $value;
				}
			}
		}
	}

	/**
	 * Preserve Providers that are not active.
	 *
	 * @since 1.9.4
	 *
	 * @param array $form_data          Form data.
	 * @param array $previous_form_data Previous form data.
	 */
	private function preserve_providers( array &$form_data, array $previous_form_data ): void {

		if ( empty( $previous_form_data['providers'] ) ) {
			return;
		}

		$active_providers = wpforms_get_providers_available();

		foreach ( $previous_form_data['providers'] as $slug => $provider ) {
			if ( ! empty( $active_providers[ $slug ] ) ) {
				continue;
			}

			$form_data['providers'][ $slug ] = $provider;
		}
	}

	/**
	 * Preserve Payments providers that are not active.
	 *
	 * @since 1.9.4
	 *
	 * @param array $form_data          Form data.
	 * @param array $previous_form_data Previous form data.
	 */
	private function preserve_payments( array &$form_data, array $previous_form_data ): void {

		if ( empty( $previous_form_data['payments'] ) ) {
			return;
		}

		foreach ( $previous_form_data['payments'] as $slug => $value ) {
			if ( ! empty( $form_data['payments'][ $slug ] ) ) {
				continue;
			}

			$form_data['payments'][ $slug ] = $value;
		}
	}

	/**
	 * Convert slug to a addon prefix.
	 *
	 * @since 1.9.4
	 *
	 * @param string $slug Addon slug.
	 *
	 * @return string
	 */
	private function prepare_prefix( string $slug ): string {

		return str_replace( '-', '_', $slug );
	}
}
