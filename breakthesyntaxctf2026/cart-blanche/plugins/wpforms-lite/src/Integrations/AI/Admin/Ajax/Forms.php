<?php

namespace WPForms\Integrations\AI\Admin\Ajax;

use WPForms\Integrations\AI\API\Forms as FormsAPI;
use WPForms_Template_Blank;

/**
 * Forms AJAX class.
 *
 * @since 1.9.2
 */
class Forms extends Base {

	/**
	 * The addons required for the AI form generator.
	 *
	 * @since 1.9.2
	 *
	 * @var array
	 */
	public const FORM_GENERATOR_REQUIRED_ADDONS = [ 'surveys-polls', 'signatures', 'coupons', 'calculations', 'quiz' ];

	/**
	 * The addon fields.
	 *
	 * @since 1.9.4
	 *
	 * @var array
	 */
	public const FORM_GENERATOR_ADDON_FIELDS = [
		'likert_scale'       => 'surveys-polls',
		'net_promoter_score' => 'surveys-polls',
		'signature'          => 'signatures',
		'payment-coupon'     => 'coupons',
	];

	/**
	 * Forms API instance.
	 *
	 * @since 1.9.2
	 *
	 * @var FormsAPI
	 */
	private $forms_api;

	/**
	 * Initialize.
	 *
	 * @since 1.9.2
	 */
	public function init() {

		parent::init();

		$this->forms_api = new FormsAPI();

		$this->forms_api->init();
		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.9.2
	 */
	private function hooks(): void {

		add_action( 'wp_ajax_wpforms_get_ai_form', [ $this, 'get_form' ] );
		add_action( 'wp_ajax_wpforms_get_ai_form_field_preview', [ $this, 'get_field_preview' ] );
		add_action( 'wp_ajax_wpforms_use_ai_form', [ $this, 'use_form' ] );
		add_action( 'wp_ajax_wpforms_dismiss_ai_form', [ $this, 'dismiss' ] );
	}

	/**
	 * "Get form" AJAX callback.
	 *
	 * @since 1.9.2
	 */
	public function get_form(): void {

		if ( ! $this->validate_nonce() ) {
			wp_send_json_error(
				[ 'error' => esc_html__( 'Your session expired. Please reload the builder.', 'wpforms-lite' ) ]
			);
		}

		$prompt = $this->get_post_data( 'prompt' );

		if ( empty( $prompt ) && $prompt !== '0' ) {
			wp_send_json_error(
				[ 'error' => esc_html__( 'Empty prompt.', 'wpforms-lite' ) ]
			);
		}

		$session_id = $this->get_post_data( 'session_id' );
		$form       = $this->forms_api->form( $prompt, $session_id );

		$form['fieldsOrder'] = array_keys( $form['fields'] ?? [] );

		/**
		 * Filters the form data before outputting it.
		 *
		 * @since 1.9.7
		 *
		 * @param array  $form       Form data.
		 * @param string $session_id Session ID.
		 */
		$form = apply_filters( 'wpforms_integrations_ai_admin_ajax_forms_get_form_before_send', $form, $session_id );

		wp_send_json_success( $form );
	}

	/**
	 * Get form field preview.
	 *
	 * @since 1.9.2
	 */
	public function get_field_preview(): void {

		if ( ! $this->validate_nonce() ) {
			wp_send_json_error(
				[ 'error' => esc_html__( 'Your session expired. Please reload the builder.', 'wpforms-lite' ) ]
			);
		}

		$field = $this->prepare_field_data(
			$this->get_post_data( 'field', 'array' )
		);

		if ( empty( $field ) ) {
			wp_send_json_error(
				[ 'error' => esc_html__( 'Empty field data.', 'wpforms-lite' ) ]
			);
		}

		$field_type = $field['type'] ?? '';

		// Check if the field type is available.
		if ( has_action( "wpforms_display_field_{$field_type}" ) ) {

			ob_start();

			// Generate field preview.
			/** This action is documented in includes/admin/builder/panels/class-fields.php. */
			do_action( "wpforms_builder_fields_previews_{$field_type}", $field ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

			$preview = ob_get_clean();
		}

		wp_send_json_success( $preview ?? '' );
	}

	/**
	 * Prepare the form fields data.
	 *
	 * @since 1.9.2
	 *
	 * @param array $form_data Form data.
	 *
	 * @return array
	 */
	private function prepare_fields_data( array $form_data ): array {

		$fields_data  = $form_data['fields'] ?? [];
		$fields_order = $form_data['fieldsOrder'] ?? [];
		$fields       = [];

		foreach ( $fields_order as $id ) {
			$fields[ $id ] = $this->prepare_field_data( $fields_data[ $id ] );
		}

		return $fields;
	}

	/**
	 * Prepare the field data.
	 *
	 * @since 1.9.2
	 *
	 * @param array $field_data Field data.
	 *
	 * @return array
	 */
	private function prepare_field_data( array $field_data ): array {

		$field_type = $field_data['type'] ?? '';

		if ( $field_type === 'content' ) {
			$field_data['content'] = htmlspecialchars_decode( $field_data['content'] ?? '' );
		}

		if ( $field_type === 'html' ) {
			$field_data['code'] = htmlspecialchars_decode( $field_data['code'] ?? '' );
		}

		$field_data['description'] = htmlspecialchars_decode( $field_data['description'] ?? '' );

		if ( ! empty( $field_data['conditionals'] ) ) {
			$field_data['conditionals'] = $this->prepare_field_cl( $field_data );
		}

		return $field_data;
	}

	/**
	 * Prepare the form settings.
	 *
	 * @since 1.9.2
	 *
	 * @param array $form_data Form data.
	 *
	 * @return array
	 */
	private function prepare_form_settings( array $form_data ): array {

		// Prepare the notifications.
		if ( isset( $form_data['settings']['notifications']['1'] ) ) {
			$form_data['settings']['notifications']['1']['subject'] = sprintf( /* translators: %s - form name. */
				esc_html__( 'New Entry: %s', 'wpforms-lite' ),
				esc_html( $form_data['form_title'] )
			);
		}

		// Quiz settings.
		$form_data = $this->prepare_quiz_settings( $form_data );

		return $form_data['settings'];
	}

	/**
	 * Prepare the field conditional logic.
	 *
	 * @since 1.9.2
	 *
	 * @param array $field Field data.
	 *
	 * @return array
	 */
	private function prepare_field_cl( array $field ): array {

		if ( empty( $field['conditionals'] ) ) {
			return [];
		}

		// Loop groups.
		foreach ( $field['conditionals'] as $group_key => $group ) {

			// Loop rules.
			foreach ( $group as $rule_key => $rule ) {
				// Fix `operator` value for choice-based fields.
				$rule['operator'] = htmlspecialchars_decode( $rule['operator'] );
				$rule['value']    = htmlspecialchars_decode( $rule['value'] );

				$field['conditionals'][ $group_key ][ $rule_key ] = $rule;
			}
		}

		return $field['conditionals'];
	}

	/**
	 * Use form checks and prepare data.
	 *
	 * @since 1.9.2
	 *
	 * @return array Form ID and the generated form data.
	 */
	private function use_form_check_data(): array {

		if ( ! $this->validate_nonce() ) {
			wp_send_json_error(
				[ 'error' => esc_html__( 'Your session expired. Please reload the builder.', 'wpforms-lite' ) ]
			);
		}

		$form_id   = $this->get_post_data( 'formId', 'int' );
		$form_data = $this->get_post_data( 'formData', 'array' );

		if ( empty( $form_data ) ) {
			wp_send_json_error(
				[ 'error' => esc_html__( 'Empty form data.', 'wpforms-lite' ) ]
			);
		}

		if ( empty( $form_id ) && ! wpforms_current_user_can( 'create_forms' ) ) {
			wp_send_json_error(
				[ 'error' => esc_html__( 'Sorry, you are not allowed to create new forms.', 'wpforms-lite' ) ]
			);
		}

		if ( ! empty( $form_id ) && ! wpforms_current_user_can( 'edit_form_single', $form_id ) ) {
			wp_send_json_error(
				[ 'error' => esc_html__( 'Sorry, you are not allowed to edit this form.', 'wpforms-lite' ) ]
			);
		}

		$form_obj = wpforms()->obj( 'form' );

		if ( ! $form_obj ) {
			wp_send_json_error(
				[ 'error' => esc_html__( 'Form database object not found.', 'wpforms-lite' ) ]
			);
		}

		$form_post = ! empty( $form_id ) ? $form_obj->get( $form_id ) : null;

		if (
			( empty( $form_post ) && ! empty( $form_id ) ) ||
			( ! empty( $form_post->post_status ) && $form_post->post_status === 'trash' )
		) {
			wp_send_json_error(
				[ 'error' => esc_html__( 'It looks like the form you are trying to access is no longer available.', 'wpforms-lite' ) ]
			);
		}

		$session_id       = $this->get_post_data( 'sessionId' );
		$response_history = $this->get_post_data( 'responseHistory', 'array' );
		$chat_html        = $this->get_post_data( 'chatHtml', 'string' );

		return [ $form_id, $form_data, $session_id, $response_history, $chat_html, $form_obj ];
	}

	/**
	 * Use form.
	 *
	 * @since 1.9.2
	 */
	public function use_form(): void {

		[ $form_id, $form_data, $session_id, $response_history, $chat_html, $form_obj ] = $this->use_form_check_data();

		// Save the chat history in the user mata data.
		$user_meta = [
			'chatHtml'        => $chat_html,
			'responseHistory' => $response_history,
		];

		update_user_meta( get_current_user_id(), 'wpforms_builder_ai_form_chat_' . $session_id, $user_meta );

		// Prepare the new form data.
		$form_data['fields']   = $this->prepare_fields_data( $form_data );
		$form_data['settings'] = $this->prepare_form_settings( $form_data );
		$form_data['field_id'] = count( $form_data['fields'] ) + 1;

		$meta               = [];
		$meta['template']   = 'generate';
		$meta['sessionId']  = $form_data['sessionId'];
		$meta['responseId'] = $form_data['responseId'];

		// Unset unrelated data.
		unset(
			$form_data['fieldsOrder'],
			$form_data['explanation'],
			$form_data['sessionId'],
			$form_data['responseId'],
			$form_data['processingData']
		);

		// Add a new form if it is a new form.
		if ( empty( $form_id ) ) {
			$form_id = $form_obj->add( $form_data['form_title'] );
		}

		// Check if the form was created.
		if ( empty( $form_id ) ) {
			wp_send_json_error(
				[ 'error' => esc_html__( 'Form could not be created.', 'wpforms-lite' ) ]
			);
		}

		// Get the blank template form data.
		$blank_form_data = WPForms_Template_Blank::get_data();

		// Merge the blank form data with the new form data.
		// In this way, we can keep the default settings of the blank form.
		$form_data = array_replace_recursive( $blank_form_data, $form_data );

		// Update the form ID.
		$form_data['id'] = $form_id;

		// Update the form.
		$form_obj->update( $form_id, $form_data, [ 'skip_revision' => 1 ] );
		$form_obj->update_meta( $form_id, 'template', $meta['template'], [ 'skip_revision' => 1 ] );
		$form_obj->update_meta( $form_id, 'sessionId', $meta['sessionId'], [ 'skip_revision' => 1 ] );
		$form_obj->update_meta( $form_id, 'responseId', $meta['responseId'], [ 'skip_revision' => 1 ] );

		// Result.
		wp_send_json_success(
			[
				'id'       => $form_id,
				'redirect' => add_query_arg(
					[
						'view'    => 'fields',
						'form_id' => $form_id,
						'session' => $session_id,
					],
					admin_url( 'admin.php?page=wpforms-builder' )
				),
			]
		);
	}

	/**
	 * Ajax handler for dismissing.
	 *
	 * @since 1.9.2
	 */
	public function dismiss(): void {

		if ( ! $this->validate_nonce() ) {
			wp_send_json_error(
				[ 'error' => esc_html__( 'Your session expired. Please reload the builder.', 'wpforms-lite' ) ]
			);
		}

		// Identifier of the dismissible element.
		$element = $this->get_post_data( 'element', 'string' );

		// Dismiss or de-dismiss.
		$dismiss = $this->get_post_data( 'dismiss', 'bool' );

		if ( empty( $element ) ) {
			wp_send_json_error(
				[ 'error' => esc_html__( 'Please specify an element.', 'wpforms-lite' ) ]
			);
		}

		// Check for permissions.
		if ( ! wpforms_current_user_can( 'edit_forms' ) ) {
			wp_send_json_error(
				[ 'error' => esc_html__( 'Sorry, you are not allowed to dismiss.', 'wpforms-lite' ) ]
			);
		}

		$user_id   = get_current_user_id();
		$dismissed = get_user_meta( $user_id, 'wpforms_dismissed', true );

		if ( empty( $dismissed ) ) {
			$dismissed = [];
		}

		if ( $dismiss ) {
			$dismissed[ 'ai-forms-' . $element ] = time();
		} else {
			unset( $dismissed[ 'ai-forms-' . $element ] );
		}

		update_user_meta( $user_id, 'wpforms_dismissed', $dismissed );
		wp_send_json_success();
	}

	/**
	 * Prepare the quiz settings.
	 *
	 * @since 1.9.9
	 *
	 * @param array $form_data Form data.
	 *
	 * @return array
	 */
	private function prepare_quiz_settings( array $form_data ): array {

		if ( empty( $form_data['settings']['quiz']['enabled'] ) ) {
			return $form_data;
		}

		$outcomes = (array) ( $form_data['settings']['quiz']['outcomes'] ?? [] );

		$default_outcome = [
			'graded_message'      => '',
			'personality_message' => '',
			'weighted_message'    => '',
			'conditionals'        => [],
		];

		// Decode the outcome messages.
		foreach ( $outcomes as $key => $outcome ) {
			$outcome = wp_parse_args( $outcome, $default_outcome );

			$outcome['graded_message']      = htmlspecialchars_decode( $outcome['graded_message'] );
			$outcome['personality_message'] = htmlspecialchars_decode( $outcome['personality_message'] );
			$outcome['weighted_message']    = htmlspecialchars_decode( $outcome['weighted_message'] );
			$outcome['conditionals']        = $this->prepare_field_cl( $outcome );

			$outcomes[ $key ] = $outcome;
		}

		$form_data['settings']['quiz']['outcomes'] = $outcomes;

		return $form_data;
	}
}
