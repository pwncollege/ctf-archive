<?php

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection PhpUndefinedClassInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace WPForms\SmartTags;

use ActionScheduler_Action;
use WPForms\SmartTags\SmartTag\Generic;
use WPForms\SmartTags\SmartTag\SmartTag;

/**
 * Class SmartTags.
 *
 * @since 1.6.7
 */
class SmartTags {

	/**
	 * List of smart tags.
	 *
	 * @since 1.6.7
	 *
	 * @var array
	 */
	protected $smart_tags = [];

	/**
	 * AS task action arguments.
	 * Temporarily store them to use in the filter.
	 *
	 * @since 1.9.4
	 *
	 * @var array|null
	 */
	private $action_args;

	/**
	 * Fallback for entry meta.
	 * Temporary store callback to remove it after AS task execution.
	 *
	 * @since 1.9.4
	 *
	 * @var callable|null
	 */
	private $fallback;

	/**
	 * Hooks.
	 *
	 * @since 1.6.7
	 */
	public function hooks(): void {

		add_filter( 'wpforms_process_smart_tags', [ $this, 'process' ], 10, 6 );
		add_filter( 'wpforms_builder_enqueues_smart_tags', [ $this, 'builder' ] );
		add_filter( 'wpforms_builder_strings', [ $this, 'add_builder_strings' ], 10, 2 );

		add_action(
			'wpforms_process_entry_saved',
			function () {

				// Save super globals only after successes processing.
				add_filter( 'wpforms_tasks_task_register_async_args', [ $this, 'save_smart_tags_tasks_meta' ] );
			}
		);

		add_action( 'wpforms_tasks_start_executing', [ $this, 'maybe_add_entry_meta_fallback_value' ], 1, 2 );
		add_action( 'wpforms_tasks_stop_executing', [ $this, 'maybe_remove_entry_meta_fallback_value' ], 1 );
	}

	/**
	 * Get the list of smart tags.
	 *
	 * @since 1.6.7
	 *
	 * @return array
	 */
	public function get_smart_tags(): array {

		if ( ! empty( $this->smart_tags ) ) {
			return $this->smart_tags;
		}

		/**
		 * Modify the smart tags' list.
		 *
		 * @since 1.4.0
		 *
		 * @param array $tags The list of smart tags.
		 */
		$this->smart_tags = (array) apply_filters( // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
			'wpforms_smart_tags',
			$this->smart_tags_list()
		);

		return $this->smart_tags;
	}

	/**
	 * Get the list of registered smart tags.
	 *
	 * @since 1.6.7
	 *
	 * @return array
	 */
	protected function smart_tags_list() {

		return [
			'admin_email'       => esc_html__( 'Site Administrator Email', 'wpforms-lite' ),
			'field_id'          => esc_html__( 'Field ID', 'wpforms-lite' ),
			'field_html_id'     => esc_html__( 'Field HTML ID', 'wpforms-lite' ),
			'field_value_id'    => esc_html__( 'Field Value', 'wpforms-lite' ),
			'form_id'           => esc_html__( 'Form ID', 'wpforms-lite' ),
			'form_name'         => esc_html__( 'Form Name', 'wpforms-lite' ),
			'page_title'        => esc_html__( 'Embedded Post/Page Title', 'wpforms-lite' ),
			'page_url'          => esc_html__( 'Embedded Post/Page URL', 'wpforms-lite' ),
			'page_id'           => esc_html__( 'Embedded Post/Page ID', 'wpforms-lite' ),
			'date'              => esc_html__( 'Date', 'wpforms-lite' ),
			'query_var'         => esc_html__( 'Query String Variable', 'wpforms-lite' ),
			'user_ip'           => esc_html__( 'User IP Address', 'wpforms-lite' ),
			'user_id'           => esc_html__( 'User ID', 'wpforms-lite' ),
			'user_display'      => esc_html__( 'User Display Name', 'wpforms-lite' ),
			'user_full_name'    => esc_html__( 'User Full Name', 'wpforms-lite' ),
			'user_first_name'   => esc_html__( 'User First Name', 'wpforms-lite' ),
			'user_last_name'    => esc_html__( 'User Last Name', 'wpforms-lite' ),
			'user_email'        => esc_html__( 'Logged-in User\'s Email', 'wpforms-lite' ),
			'user_meta'         => esc_html__( 'User Meta', 'wpforms-lite' ),
			'author_id'         => esc_html__( 'Author ID', 'wpforms-lite' ),
			'author_display'    => esc_html__( 'Author Name', 'wpforms-lite' ),
			'author_email'      => esc_html__( 'Author Email', 'wpforms-lite' ),
			'url_referer'       => esc_html__( 'Referrer URL', 'wpforms-lite' ),
			'url_login'         => esc_html__( 'Login URL', 'wpforms-lite' ),
			'url_logout'        => esc_html__( 'Logout URL', 'wpforms-lite' ),
			'url_register'      => esc_html__( 'Register URL', 'wpforms-lite' ),
			'url_lost_password' => esc_html__( 'Lost Password URL', 'wpforms-lite' ),
			'unique_value'      => esc_html__( 'Unique Value', 'wpforms-lite' ),
			'site_name'         => esc_html__( 'Site Name', 'wpforms-lite' ),
			'order_summary'     => esc_html__( 'Order Summary', 'wpforms-lite' ),
		];
	}

	/**
	 * Add the Form Builder strings.
	 *
	 * @since 1.9.5
	 *
	 * @param array   $strings Localized strings.
	 * @param WP_Post $form    Form object.
	 *
	 * @return array
	 * @noinspection HtmlUnknownTarget
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function add_builder_strings( $strings, $form ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		$strings = (array) $strings;

		/**
		 * Smart Tags.
		 *
		 * @since 1.6.7
		 *
		 * @param array $smart_tags Array of smart tags.
		 */
		$smart_tags = (array) apply_filters( 'wpforms_builder_enqueues_smart_tags', $this->get_smart_tags() ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		$st_strings = [
			'smart_tags_dropdown_mce_icon'          => WPFORMS_PLUGIN_URL . 'assets/images/icon-tags.svg',
			'smart_tags'                            => $smart_tags,
			'smart_tags_disabled_for_fields'        => [ 'entry_id' ],
			'smart_tags_edit_ok_button'             => esc_html__( 'Apply changes', 'wpforms-lite' ),
			'smart_tags_delete_button'              => esc_html__( 'Delete smart tag', 'wpforms-lite' ),
			'smart_tags_edit'                       => esc_html__( 'edit', 'wpforms-lite' ),
			'smart_tags_arg'                        => esc_html__( 'argument', 'wpforms-lite' ),
			'smart_tags_unknown_field'              => esc_html__( 'Unknown Field', 'wpforms-lite' ),
			'smart_tags_templates'                  => [
				/* translators: %1$s - field ID, %2$s - field label. */
				'field_id'       => esc_html__( 'Field %1$s', 'wpforms-lite' ),
				/* translators: %1$s - field ID, %2$s - field label. */
				'field_value_id' => esc_html__( 'Field value %1$s', 'wpforms-lite' ),
				/* translators: %1$s - field ID, %2$s - field label. */
				'field_html_id'  => esc_html__( 'Field HTML %1$s', 'wpforms-lite' ),
				/* translators: %1$s - Query String Variable. */
				'query_var'      => esc_html__( 'Query String Variable: %1$s', 'wpforms-lite' ),
				/* translators: %1$s - User meta key. */
				'user_meta'      => esc_html__( 'User Meta: %1$s', 'wpforms-lite' ),
				/* translators: %1$s - Date format. */
				'date'           => esc_html__( 'Date: %1$s', 'wpforms-lite' ),
				/* translators: %1$s - Date format. */
				'entry_date'     => esc_html__( 'Entry Date: %1$s', 'wpforms-lite' ),
			],
			/**
			 * Filters the list of Smart Tags that are disabled for confirmations.
			 *
			 * @since 1.9.3
			 *
			 * @param array $disabled List of disabled Smart Tags.
			 */
			'smart_tags_disabled_for_confirmations' => apply_filters( 'wpforms_builder_smart_tags_disabled_for_confirmations', [] ), // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
		];

		$st_strings['smart_tags_button_tooltip'] = sprintf(
			wp_kses( /* translators: %1$s - link to the WPForms.com doc article. */
				__( 'Easily add dynamic information from various sources with <a href="%1$s" target="_blank" rel="noopener noreferrer">Smart Tags</a>.', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'rel'    => [],
						'target' => [],
					],
				]
			),
			esc_url(
				wpforms_utm_link(
					'https://wpforms.com/docs/how-to-use-smart-tags-in-wpforms/',
					'Builder Settings',
					'Smart Tags Documentation'
				)
			)
		);

		return array_merge( $strings, $st_strings );
	}

	/**
	 * Process smart tags.
	 *
	 * @since 1.6.7
	 * @since 1.8.7 Added `$context` parameter.
	 *
	 * @param string $content   Content.
	 * @param array  $form_data Form data.
	 * @param array  $fields    List of fields.
	 * @param string $entry_id  Entry ID.
	 * @param string $context   Context.
	 *
	 * @return string
	 */
	public function process( $content, $form_data, $fields = [], $entry_id = '', $context = '', array $context_data = [] ) {

		// We shouldn't process smart tags in different WordPress editors
		// since it produce unexpected results.
		if ( wpforms_is_editor_page() ) {
			return $content;
		}

		$smart_tags = wpforms_get_all_smart_tags( $content );

		if ( empty( $smart_tags ) ) {
			return $content;
		}

		foreach ( $smart_tags as $smart_tag => $tag_name ) {
			$class_name       = $this->get_smart_tag_class_name( $tag_name );
			$smart_tag_object = new $class_name( $smart_tag, $context, $context_data );
			$value            = $smart_tag_object->get_value( $form_data, $fields, $entry_id );
			$field_id         = $smart_tag_object->get_attributes()['field_id'] ?? 0;
			$field_id         = (int) explode( '|', $field_id )[0];

			if (
				$context === 'confirmation_redirect' &&
				$field_id > 0 &&
				in_array(
					$fields[ $field_id ]['type'],
					wpforms_get_multi_fields(),
					true
				)
			) {
				// Protect from the case where the user already placed a pipe in the value.
				$value = str_replace(
					[ "\r\n", "\r", "\n", '|' ],
					[ rawurlencode( '|' ), '|', '|', '|' ],
					$value
				);
			}

			/**
			 * Modify the smart tag value.
			 *
			 * @since 1.6.7
			 * @since 1.6.7.1 Added the 5th argument.
			 * @since 1.9.0 Added the 6th argument.
			 *
			 * @param scalar|null $value            Smart Tag value.
			 * @param array       $form_data        Form data.
			 * @param array       $fields           List of fields.
			 * @param int         $entry_id         Entry ID.
			 * @param SmartTag    $smart_tag_object The smart tag object or the Generic object for those cases when class unregistered.
			 * @param string      $context          Context.
			 */
			$value = apply_filters( // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
				"wpforms_smarttags_process_{$tag_name}_value",
				$value,
				$form_data,
				$fields,
				$entry_id,
				$smart_tag_object,
				$context
			);

			/**
			 * Modify a smart tag value.
			 *
			 * @since 1.6.7.1
			 * @since 1.9.7.3 Added the 7th argument.
			 *
			 * @param scalar|null $value            Smart Tag value.
			 * @param string      $tag_name         Smart tag name.
			 * @param array       $form_data        Form data.
			 * @param array       $fields           List of fields.
			 * @param int         $entry_id         Entry ID.
			 * @param SmartTag    $smart_tag_object The smart tag object or the Generic object for those cases when class unregistered.
			 * @param string      $context          Context.
			 */
			$value = apply_filters( // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
				'wpforms_smarttags_process_value',
				$value,
				$tag_name,
				$form_data,
				$fields,
				$entry_id,
				$smart_tag_object,
				$context
			);

			if ( $value !== null ) {
				$content = $this->replace( $smart_tag, $value, $content );
			}

			/**
			 * Modify content with smart tags.
			 *
			 * @since      1.4.0
			 * @since      1.6.7.1 Added 3rd, 4th, 5th, 6th arguments.
			 *
			 * @param string   $content          Content of the Smart Tag.
			 * @param string   $tag_name         Tag name of the Smart Tag.
			 * @param array    $form_data        Form data.
			 * @param string   $fields           List of fields.
			 * @param int      $entry_id         Entry ID.
			 * @param SmartTag $smart_tag_object The smart tag object or the Generic object for those cases when class unregistered.
			 */
			$content = (string) apply_filters( // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
				'wpforms_smart_tag_process',
				$content,
				$tag_name,
				$form_data,
				$fields,
				$entry_id,
				$smart_tag_object
			);
		}

		return $content;
	}

	/**
	 * Determine if the smart tag is registered.
	 *
	 * @since 1.6.7
	 *
	 * @param string $smart_tag_name Smart tag name.
	 *
	 * @return bool
	 */
	protected function has_smart_tag( $smart_tag_name ) {

		return array_key_exists( $smart_tag_name, $this->get_smart_tags() );
	}

	/**
	 * Get a smart tag class name.
	 *
	 * @since 1.6.7
	 *
	 * @param string $smart_tag_name Smart tag name.
	 *
	 * @return string
	 */
	protected function get_smart_tag_class_name( $smart_tag_name ) {

		if ( ! $this->has_smart_tag( $smart_tag_name ) ) {
			return Generic::class;
		}

		$class_name = str_replace( ' ', '', ucwords( str_replace( '_', ' ', $smart_tag_name ) ) );

		$full_class_name = '\\WPForms\\SmartTags\\SmartTag\\' . $class_name;

		if ( class_exists( $full_class_name ) ) {
			return $full_class_name;
		}

		/**
		 * Modify a smart tag class name that describes the smart tag logic.
		 *
		 * @since 1.6.7
		 *
		 * @param string $class_name     The value.
		 * @param string $smart_tag_name Smart tag name.
		 */
		$full_class_name = apply_filters( 'wpforms_smarttags_get_smart_tag_class_name', '', $smart_tag_name ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		return class_exists( $full_class_name ) ? $full_class_name : Generic::class;
	}

	/**
	 * Retrieve the builder's special tags.
	 *
	 * @since 1.6.7
	 *
	 * @return array
	 */
	protected function get_replacement_builder_tags() {

		return [
			'date'      => 'date format="m/d/Y"',
			'query_var' => 'query_var key=""',
			'user_meta' => 'user_meta key=""',
		];
	}

	/**
	 * Hide smart tags in the builder.
	 *
	 * @since 1.6.7
	 *
	 * @return array
	 */
	protected function get_hidden_builder_tags() {

		return [
			'field_id',
			'field_html_id',
			'field_value_id',
		];
	}

	/**
	 * Builder tags.
	 *
	 * @since 1.6.7
	 *
	 * @return array
	 */
	public function builder() {

		$smart_tags       = $this->get_smart_tags();
		$replacement_tags = $this->get_replacement_builder_tags();
		$hidden_tags      = $this->get_hidden_builder_tags();

		foreach ( $replacement_tags as $tag => $replacement_tag ) {
			$smart_tags = wpforms_array_insert( $smart_tags, [ $replacement_tag => $smart_tags[ $tag ] ], $tag );

			unset( $smart_tags[ $tag ] );
		}

		foreach ( $hidden_tags as $hidden_tag ) {
			unset( $smart_tags[ $hidden_tag ] );
		}

		return $smart_tags;
	}

	/**
	 * Replace a found smart tag with the final value.
	 *
	 * @since 1.6.7
	 *
	 * @param string $tag     The tag.
	 * @param string $value   The value.
	 * @param string $content Content.
	 *
	 * @return string
	 */
	private function replace( $tag, $value, $content ) {

		return str_replace( $tag, strip_shortcodes( $value ), $content );
	}

	/**
	 * Filter arguments passed to the async task.
	 *
	 * @since 1.9.4
	 *
	 * @param array|mixed $args Arguments passed to the async task.
	 */
	public function save_smart_tags_tasks_meta( $args ): array {

		$args    = (array) $args;
		$process = wpforms()->obj( 'process' );

		if ( ! $process || empty( $process->form_data['entry_meta'] ) ) {
			return $args;
		}

		$args['entry_meta'] = $process->form_data['entry_meta'];

		return $args;
	}

	/**
	 * Maybe add a fallback for entry meta for WPForms Action Scheduler tasks meta.
	 *
	 * @since 1.9.4
	 *
	 * @param int|mixed              $action_id Action ID.
	 * @param ActionScheduler_Action $action    Action Scheduler action object.
	 *
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function maybe_add_entry_meta_fallback_value( $action_id, $action ): void { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		$this->action_args = $action->get_args();
		$this->fallback    = function ( $value, $var_name ) {

			if ( ! wpforms_is_empty_string( $value ) ) {
				return $value;
			}

			return $this->action_args['entry_meta'][ $var_name ] ?? $value;
		};

		add_filter( 'wpforms_smart_tags_smart_tag_get_meta_value', $this->fallback, 10, 2 );
	}

	/**
	 * Maybe remove a fallback for entry meta for WPForms Action Scheduler tasks meta.
	 *
	 * @since 1.9.4
	 */
	public function maybe_remove_entry_meta_fallback_value(): void { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		if ( ! $this->fallback ) {
			return;
		}

		remove_filter( 'wpforms_smart_tags_smart_tag_get_meta_value', $this->fallback );
	}
}
