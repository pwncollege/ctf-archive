<?php

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpDeprecationInspection */

namespace WPForms\Emails;

use DOMDocument;
use WPForms\SmartTags\SmartTag\SmartTag;
use WPForms_WP_Emails;
use WPForms\Tasks\Actions\EntryEmailsTask;
use WPForms\Emails\Templates\General; // phpcs:ignore WPForms.PHP.UseStatement.UnusedUseStatement
use WPForms\Pro\Emails\Templates\Modern;
use WPForms\Pro\Emails\Templates\Elegant;
use WPForms\Pro\Emails\Templates\Tech;

/**
 * Class Notifications.
 * Used to send email notifications.
 *
 * @since 1.8.5
 */
class Notifications extends Mailer {

	/**
	 * List of submitted fields.
	 *
	 * @since 1.8.5
	 *
	 * @var array
	 */
	public $fields = [];

	/**
	 * Form data.
	 *
	 * @since 1.8.5
	 *
	 * @var array
	 */
	public $form_data = [];

	/**
	 * Entry id.
	 *
	 * @since 1.8.5
	 *
	 * @var int
	 */
	public $entry_id;

	/**
	 * Notification ID that is currently being processed.
	 *
	 * @since 1.8.5
	 *
	 * @var int
	 */
	public $notification_id = '';

	/**
	 * Current email template.
	 *
	 * @since 1.8.5
	 *
	 * @var string
	 */
	private $current_template;

	/**
	 * Field template.
	 *
	 * @since 1.8.5
	 *
	 * @var string
	 */
	protected $field_template = '';

	/**
	 * Default email template name.
	 *
	 * @since 1.8.5
	 *
	 * @var string
	 */
	public const DEFAULT_TEMPLATE = 'classic';

	/**
	 * Plain/Text email template name.
	 *
	 * @since 1.8.5
	 *
	 * @var string
	 */
	public const PLAIN_TEMPLATE = 'none';

	/**
	 * Legacy email template name.
	 *
	 * @since 1.8.5
	 *
	 * @var string
	 */
	public const LEGACY_TEMPLATE = 'default';

	/**
	 * Whether the email is being sent to a PDF.
	 *
	 * @since 1.9.7.3
	 *
	 * @var string
	 */
	public $rendering_context;

	/**
	 * Get the instance of a class.
	 *
	 * @since 1.8.9
	 */
	public static function get_instance() {

		static $instance;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * This method will initialize the class.
	 *
	 * Maybe use the old class for backward compatibility.
	 * The old class might be removed in the future.
	 *
	 * @since 1.8.5
	 *
	 * @param string $template          Email template name.
	 * @param string $rendering_context Where the email is being rendered, 'mail' or 'pdf'.
	 *
	 * @return $this|WPForms_WP_Emails
	 * @noinspection PhpDeprecationInspection
	 */
	public function init( string $template = '', string $rendering_context = 'mail' ) {

		$this->rendering_context = $rendering_context;

		// Add hooks.
		$this->hooks();

		// Assign the current template.
		$this->current_template = Helpers::get_current_template_name( $template );

		// If the old class doesn't exist, return the current class.
		// The old class might be removed in the future.
		if ( ! class_exists( 'WPForms_WP_Emails' ) ) {
			return $this;
		}

		// In case the user is still using the old "Legacy" default template, use the old class.
		// Use the old class if the current template is "Legacy".
		if ( $this->current_template === self::LEGACY_TEMPLATE ) {
			return new WPForms_WP_Emails();
		}

		// Plain text and other HTML templates will use the current class.
		return $this;
	}

	/**
	 * Add hooks.
	 *
	 * @since 1.9.0
	 */
	private function hooks(): void {

		add_filter( 'wpforms_smart_tags_formatted_field_value', [ $this, 'get_multi_field_formatted_value' ], 10, 4 );
		add_filter( 'wpforms_smarttags_process_value', [ self::class, 'filter_smarttags_process_value' ], PHP_INT_MAX, 6 );
	}

	/**
	 * Maybe send an email right away or schedule it.
	 *
	 * @since 1.8.5
	 *
	 * @return bool Whether the email was sent successfully.
	 */
	public function send() {

		// Leave the method if the arguments are empty.
		// We will be looking for 3 arguments: $to, $subject, $message.
		// The primary reason for this method not to take any direct arguments is to make it compatible with the parent class.
		if ( empty( func_get_args() ) || count( func_get_args() ) < 3 ) {
			return false;
		}

		// Don't send anything if emails have been disabled.
		if ( $this->is_email_disabled() ) {
			return false;
		}

		// Set the arguments.
		[ $to, $subject, $message ] = func_get_args();

		// Don't send it if the email address is invalid.
		if ( ! is_email( $to ) ) {
			return false;
		}

		/**
		 * Fires before the email is sent.
		 *
		 * The filter has been ported from "class-emails.php" to maintain backward compatibility
		 * and avoid unintended breaking changes where these hooks may have been used.
		 *
		 * @since 1.8.5.2
		 *
		 * @param Notifications $this An instance of the "Notifications" class.
		 */
		do_action( 'wpforms_email_send_before', $this ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		// Set the attachments to an empty array.
		// We will set the attachments later in the filter.
		$attachments = [];

		/**
		 * Preliminary set the unfiltered recipient email address.
		 * It will be used in get_headers() while resolving smart tags.
		 */
		$this->to_email( $to );

		/**
		 * Filter the email data before sending.
		 *
		 * The filter has been ported from "class-emails.php" to maintain backward compatibility
		 * and avoid unintended breaking changes where these hooks may have been used.
		 *
		 * @since 1.8.5
		 *
		 * @param array         $data Email data.
		 * @param Notifications $this An instance of the "Notifications" class.
		 */
		$data = (array) apply_filters( // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
			'wpforms_emails_send_email_data',
			[
				'to'          => $to,
				'subject'     => $subject,
				'message'     => $message,
				'headers'     => $this->get_headers(),
				'attachments' => $attachments,
			],
			$this
		);

		// Set the recipient email address.
		$this->to_email( $data['to'] );

		// Set the email subject.
		$this->subject( $this->process_subject( $data['subject'] ) );

		// Process the email template.
		$this->process_email_template( $data['message'] );

		// Set the attachments to the email.
		$this->__set( 'attachments', $data['attachments'] );

		$entry_obj = wpforms()->obj( 'entry' );

		/**
		 * Filter whether to send the email in the same process.
		 *
		 * The filter has been ported from "class-emails.php" to maintain backward compatibility
		 * and avoid unintended breaking changes where these hooks may have been used.
		 *
		 * @since 1.8.5
		 *
		 * @param bool   $send_same_process Whether to send the email in the same process.
		 * @param array  $fields            List of submitted fields.
		 * @param array  $entry             Entry data.
		 * @param array  $form_data         Form data.
		 * @param int    $entry_id          Entry ID.
		 * @param string $type              Email type.
		 */
		$send_same_process = (bool) apply_filters( // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName, WPForms.Comments.ParamTagHooks.InvalidParamTagsQuantity
			'wpforms_tasks_entry_emails_trigger_send_same_process',
			false,
			$this->fields,
			$entry_obj ? $entry_obj->get( $this->entry_id ) : [],
			$this->form_data,
			$this->entry_id,
			'entry'
		);

		// Send the email immediately.
		if ( $send_same_process || ! empty( $this->form_data['settings']['disable_entries'] ) ) {
			$results = parent::send();
		} else {
			$results = (bool) ( new EntryEmailsTask() )
				->params(
					$this->__get( 'to_email' ),
					$this->__get( 'subject' ),
					$this->get_message(),
					$this->get_headers(),
					$this->get_attachments()
				)
				->register();
		}

		/**
		 * Fires after the email has been sent.
		 *
		 * The filter has been ported from "class-emails.php" to maintain backward compatibility
		 * and avoid unintended breaking changes where these hooks may have been used.
		 *
		 * @since 1.8.5.2
		 *
		 * @param Notifications $this An instance of the "Notifications" class.
		 */
		do_action( 'wpforms_email_send_after', $this ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		return $results;
	}

	/**
	 * Process the email template.
	 *
	 * @since 1.8.5
	 *
	 * @param string $message Email message.
	 */
	public function process_email_template( string $message ): void {

		$template = self::get_available_templates( $this->current_template );

		// Return if the template is not set.
		// This can happen if the template is not found or if the template class doesn't exist.
		if ( ! isset( $template['path'] ) || ! class_exists( $template['path'] ) ) {
			return;
		}

		// Set the email template, i.e., WPForms\Emails\Templates\Classic.
		$this->template( new $template['path']( '', false, $this->current_template ) );

		/**
		 * Email template.
		 *
		 * @var General $email_template
		 */
		$email_template = $this->__get( 'template' );

		if (
			! method_exists( $email_template, 'get_field_template' ) ||
			! method_exists( $email_template, 'set_field' )
		) {
			return;
		}

		// Set the field template.
		$this->field_template = $email_template->get_field_template();

		// Set the email template fields.
		$email_template->set_field( $this->process_message( $message ) );

		$content = $email_template->get();

		// Return if the template is empty.
		if ( ! $content ) {
			return;
		}

		$this->message( $content );
	}

	/**
	 * Format and process the email subject.
	 *
	 * @since 1.8.5
	 *
	 * @param string $subject Email subject.
	 *
	 * @return string
	 */
	private function process_subject( $subject ) {

		$subject = $this->process_tag( $subject );
		$subject = trim( str_replace( [ "\r\n", "\r", "\n" ], ' ', $subject ) );

		return wpforms_decode_string( $subject );
	}

	/**
	 * Process the email message.
	 *
	 * @since 1.8.5
	 *
	 * @param string $message Email message.
	 *
	 * @return string
	 */
	private function process_message( $message ) {

		$message = $this->process_tag( $message );

		if ( strpos( $message, '{all_fields}' ) !== false ) {
			$message = str_replace( '{all_fields}', $this->process_field_values(), $message );
		}

		/**
		 * Filter and modify the email message content before sending.
		 * This filter allows customizing the email message content for notifications.
		 *
		 * @since 1.8.5
		 *
		 * @param string        $message  The email message to be sent out.
		 * @param string        $template The email template name.
		 * @param Notifications $this     The instance of the "Notifications" class.
		 */
		$message = (string) apply_filters( 'wpforms_emails_notifications_message', $message, $this->current_template, $this );

		$message = $this->fix_table_body_markup( $message );

		// Leave early if the template is set to plain text.
		if ( Helpers::is_plain_text_template( $this->current_template ) ) {
			return $message;
		}

		/**
		 * Filter and modify the processed email message content before sending.
		 * This filter allows customizing the processed email message content for notifications.
		 *
		 * @since 1.9.9
		 *
		 * @param string        $processed_message The processed email message to be sent out.
		 * @param string        $message           The email message before processing.
		 * @param Notifications $this              The instance of the "Notifications" class.
		 *
		 * @return string The processed email message to be sent out.
		 */
		return (string) apply_filters(
			'wpforms_emails_notifications_processed_message',
			make_clickable( str_replace( "\r\n", '<br/>', $message ) ), // TODO: Replacing line breaks may not work as expected. Needs further investigation.
			$message,
			$this
		);
	}

	/**
	 * Process the field values.
	 *
	 * @since 1.8.5
	 *
	 * @return string
	 * @noinspection PhpUnusedLocalVariableInspection
	 */
	private function process_field_values() {

		// If fields are empty, return an empty message.
		if ( empty( $this->fields ) ) {
			return '';
		}

		// If no message was generated, create an empty message.
		$default_message = esc_html__( 'An empty form was submitted.', 'wpforms-lite' );

		/**
		 * Filter whether to display empty fields in the email.
		 *
		 * @since 1.8.5
		 * @deprecated 1.8.5.2
		 *
		 * @param bool $show_empty_fields Whether to display empty fields in the email.
		 */
		$show_empty_fields = apply_filters_deprecated( // phpcs:disable WPForms.Comments.ParamTagHooks.InvalidParamTagsQuantity
			'wpforms_emails_notifications_display_empty_fields',
			[ false ],
			'1.8.5.2 of the WPForms plugin',
			'wpforms_email_display_empty_fields'
		);

		/** This filter is documented in /includes/emails/class-emails.php */
		$show_empty_fields = apply_filters( // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
			'wpforms_email_display_empty_fields',
			false
		);

		// Process either plain text or HTML message based on the template type.
		if ( Helpers::is_plain_text_template( $this->current_template ) ) {
			$message = $this->process_plain_message( $show_empty_fields );
		} else {
			$message = $this->process_html_message( $show_empty_fields );
		}

		/**
		 * Filter the email message content before sending.
		 *
		 * @since 1.9.7.3
		 *
		 * @param string $message  The email message to be sent out.
		 * @param string $template The email template name.
		 * @param Mailer $this     The instance of the "Notifications" class.
		 */
		return empty( $message ) ? $default_message : apply_filters( 'wpforms_emails_notifications_process_field_values_message', $message, $this->current_template, $this );
	}

	/**
	 * Get processed field values.
	 *
	 * @since 1.9.7.3
	 *
	 * @return string
	 */
	public function get_processed_field_values(): string {

		$template = self::get_available_templates( $this->current_template );

		// Return if the template is not set.
		// This can happen if the template is not found or if the template class doesn't exist.
		if ( ! isset( $template['path'] ) || ! class_exists( $template['path'] ) ) {
			return '';
		}

		// Set the email template, i.e., WPForms\Emails\Templates\Classic.
		$this->template( new $template['path']( '', false, $this->current_template ) );

		$email_template = $this->__get( 'template' );

		if (
			! method_exists( $email_template, 'get_field_template' ) ||
			! method_exists( $email_template, 'set_field' )
		) {
			return '';
		}

		$this->field_template = $email_template->get_field_template();
		$field_values         = trim( $this->process_field_values() );

		return make_clickable( $field_values );
	}

	/**
	 * Process the plain text email message.
	 *
	 * @since 1.8.5
	 *
	 * @param bool $show_empty_fields Whether to display empty fields in the email.
	 *
	 * @return string
	 */
	private function process_plain_message( bool $show_empty_fields = false ): string {

		/**
		 * Filter the form data before it is used to generate the email message.
		 *
		 * @since 1.8.9
		 *
		 * @param array $form_data Form data.
		 * @param array $fields    List of submitted fields.
		 */
		$this->form_data = apply_filters( 'wpforms_emails_notifications_form_data', $this->form_data, $this->fields );

		$message = '';

		foreach ( $this->form_data['fields'] as $field ) {
			/**
			 * Filter whether to ignore the field in the email.
			 *
			 * @since 1.9.0
			 *
			 * @param bool  $ignore    Whether to ignore the field in the email.
			 * @param array $field     Field data.
			 * @param array $form_data Form data.
			 */
			if ( apply_filters( 'wpforms_emails_notifications_field_ignored', false, $field, $this->form_data ) ) {
				continue;
			}

			$field_message = $this->get_field_plain( $field, $show_empty_fields );

			/**
			 * Filter the field message before it is added to the email message.
			 *
			 * @since 1.8.9
			 * @since 1.8.9.3 The $notifications parameter was added.
			 *
			 * @param string        $field_message     Field message.
			 * @param array         $field             Field data.
			 * @param bool          $show_empty_fields Whether to display empty fields in the email.
			 * @param array         $form_data         Form data.
			 * @param array         $fields            List of submitted fields.
			 * @param Notifications $notifications     Notifications instance.
			 */
			$message .= apply_filters( 'wpforms_emails_notifications_field_message_plain', $field_message, $field, $show_empty_fields, $this->form_data, $this->fields, $this );
		}

		// Trim the message and return.
		return rtrim( $message, "\r\n" );
	}

	/**
	 * Get a single field plain text markup.
	 *
	 * @since 1.8.9
	 *
	 * @param array $field             Field data.
	 * @param bool  $show_empty_fields Whether to display empty fields in the email.
	 *
	 * @return string
	 */
	public function get_field_plain( array $field, bool $show_empty_fields ): string { // phpcs:ignore Generic.Metrics.CyclomaticComplexity

		$field_id = $field['id'] ?? '';

		$field = $this->fields[ $field_id ] ?? $field;

		$message = '';

		if ( ! $show_empty_fields && ( ! isset( $field['value'] ) || (string) $field['value'] === '' ) ) {
			return $message;
		}

		if ( $this->is_calculated_field_hidden( $field_id ) ) {
			return $message;
		}

		$field_name = $field['name'] ?? '';
		$field_val  = empty( $field['value'] ) && ! is_numeric( $field['value'] ) ? esc_html__( '(empty)', 'wpforms-lite' ) : $field['value'];

		// Add quantity for the field.
		if ( wpforms_payment_has_quantity( $field, $this->form_data ) ) {
			$field_val = wpforms_payment_format_quantity( $field );
		}

		// Set a default field name if empty.
		if ( empty( $field_name ) && $field_name !== null ) {
			$field_name = $this->get_default_field_name( $field['id'] );
		}

		$message    .= '--- ' . $field_name . " ---\r\n\r\n";
		$field_value = wpforms_decode_string( $field_val ) . "\r\n\r\n";

		/**
		 * Filter the field value before it is added to the email message.
		 *
		 * @since      1.8.5
		 * @deprecated 1.8.7
		 *
		 * @param string $field_value Field value.
		 * @param array  $field       Field data.
		 * @param array  $form_data   Form data.
		 */
		$field_value = apply_filters_deprecated( // phpcs:disable WPForms.Comments.ParamTagHooks.InvalidParamTagsQuantity
			'wpforms_emails_notifications_plaintext_field_value',
			[ $field_value, $field, $this->form_data ],
			'1.8.7 of the WPForms plugin',
			'wpforms_plaintext_field_value'
		);

		/** This filter is documented in /includes/emails/class-emails.php */
		$field_value = apply_filters( // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
			'wpforms_plaintext_field_value',
			$field_value,
			$field,
			$this->form_data
		);

		// Append the filtered field value to the message.
		$message .= $field_value;

		return $message;
	}

	/**
	 * Process the HTML email message.
	 *
	 * @since 1.8.5
	 *
	 * @param bool $show_empty_fields Whether to display empty fields in the email.
	 *
	 * @return string
	 * @noinspection PhpUnusedLocalVariableInspection
	 */
	private function process_html_message( $show_empty_fields = false ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity

		$message = '';

		/**
		 * Filter the list of field types to display in the email.
		 *
		 * @since 1.8.5
		 * @deprecated 1.8.5.2
		 *
		 * @param array $other_fields List of field types.
		 * @param array $form_data    Form data.
		 */
		$other_fields = apply_filters_deprecated( // phpcs:disable WPForms.Comments.ParamTagHooks.InvalidParamTagsQuantity
			'wpforms_emails_notifications_display_other_fields',
			[ [], $this->form_data ],
			'1.8.5.2 of the WPForms plugin',
			'wpforms_email_display_other_fields'
		);

		/** This filter is documented in /includes/emails/class-emails.php */
		$other_fields = (array) apply_filters( // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
			'wpforms_email_display_other_fields',
			[],
			$this
		);

		/**
		 * Filter the form data before it is used to generate the email message.
		 *
		 * @since 1.8.8
		 * @since 1.8.9 The $fields parameter was added.
		 *
		 * @param array $form_data Form data.
		 * @param array $fields    List of submitted fields.
		 */
		$this->form_data = apply_filters( 'wpforms_emails_notifications_form_data', $this->form_data, $this->fields );

		foreach ( $this->form_data['fields'] as $field ) {
			/**
			 * Filter whether to ignore the field in the email.
			 *
			 * @since 1.9.0
			 *
			 * @param bool  $ignore    Whether to ignore the field in the email.
			 * @param array $field     Field data.
			 * @param array $form_data Form data.
			 */
			if ( apply_filters( 'wpforms_emails_notifications_field_ignored', false, $field, $this->form_data ) ) {
				continue;
			}

			$field_message = $this->get_field_html( $field, $show_empty_fields, $other_fields );

			/**
			 * Filter the field message before it is added to the email message.
			 *
			 * @since 1.8.9
			 * @since 1.8.9.3 The $notifications parameter was added.
			 *
			 * @param string        $field_message     Field message.
			 * @param array         $field             Field data.
			 * @param bool          $show_empty_fields Whether to display empty fields in the email.
			 * @param array         $other_fields      List of field types.
			 * @param array         $form_data         Form data.
			 * @param array         $fields            List of submitted fields.
			 * @param Notifications $notifications     Notifications instance.
			 */
			$field_message = (string) apply_filters( 'wpforms_emails_notifications_field_message_html', $field_message, $field, $show_empty_fields, $other_fields, $this->form_data, $this->fields, $this );

			$message .= trim( $field_message );
		}

		return $message;
	}

	/**
	 * Get a single field HTML markup.
	 *
	 * @since 1.8.9
	 *
	 * @param array $field             Field data.
	 * @param bool  $show_empty_fields Whether to display empty fields in the email.
	 * @param array $other_fields      List of field types.
	 *
	 * @return string
	 */
	public function get_field_html( array $field, bool $show_empty_fields, array $other_fields ): string { // phpcs:ignore Generic.Metrics.CyclomaticComplexity

		$field_type = ! empty( $field['type'] ) ? $field['type'] : '';
		$field_id   = $field['id'] ?? '';

		// Check if the field is empty in $this->fields.
		if ( empty( $this->fields[ $field_id ] ) ) {
			// Check if the field type is in $other_fields, otherwise skip.
			// Skip if the field is conditionally hidden.
			if (
				empty( $other_fields ) ||
				! in_array( $field_type, $other_fields, true ) ||
				(
					wpforms()->is_pro() &&
					wpforms_conditional_logic_fields()->field_is_hidden( $this->form_data, $field_id )
				)
			) {
				return '';
			}

			// Handle specific field types.
			[ $field_name, $field_val ] = $this->process_special_field_values( $field );
		} else {
			// Handle fields that are not empty in $this->fields.
			if ( ! $show_empty_fields && ( ! isset( $this->fields[ $field_id ]['value'] ) || (string) $this->fields[ $field_id ]['value'] === '' ) ) {
				return '';
			}

			if ( $this->is_calculated_field_hidden( $field_id ) ) {
				return '';
			}

			$field_name = $this->fields[ $field_id ]['name'] ?? '';
			$field_val  = empty( $this->fields[ $field_id ]['value'] ) && ! is_numeric( $this->fields[ $field_id ]['value'] ) ? '<em>' . esc_html__( '(empty)', 'wpforms-lite' ) . '</em>' : $this->fields[ $field_id ]['value'];
		}

		// Set a default field name if empty.
		if ( empty( $field_name ) && $field_name !== null ) {
			$field_name = $this->get_default_field_name( $field_id );
		}

		/**
		 * Filter the field name before it is added to the email message.
		 *
		 * @since 1.9.1
		 *
		 * @param string $field_name Field name.
		 * @param array  $field      Field data.
		 * @param array  $form_data  Form data.
		 * @param string $context    Context of the field name.
		 */
		$field_name = (string) apply_filters( // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
			'wpforms_html_field_name',
			$field_name,
			$this->fields[ $field_id ] ?? $field,
			$this->form_data,
			'email-html'
		);

		/** This filter is documented in src/SmartTags/SmartTag/FieldHtmlId.php.*/
		$field_val = (string) apply_filters( // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
			'wpforms_html_field_value',
			$field_val,
			$this->fields[ $field_id ] ?? $field,
			$this->form_data,
			'email-html'
		);

		$field_val = str_replace( [ "\r\n", "\r", "\n" ], '<br/>', $field_val );

		// Replace the payment total value if an order summary is enabled.
		// Ideally, it could be done through the `wpforms_html_field_value` filter,
		// but necessary data is missed there, e.g., entry data ($this->fields).
		if ( $field_type === 'payment-total' && ! empty( $field['summary'] ) ) {
			$field_val = $this->get_payment_total_value( $field_val );
		}

		// Append the field item to the message.
		return str_replace(
			[ '{field_type}', '{field_name}', '{field_value}' ],
			[ $field_type, $field_name, $field_val ],
			$this->field_template
		);
	}

	/**
	 * Get payment total value.
	 *
	 * @since 1.9.3
	 *
	 * @param string $value Field value.
	 *
	 * @return string
	 */
	private function get_payment_total_value( string $value ): string {

		return $this->process_tag( '{order_summary}' ) . '<span class="wpforms-payment-total">' . $value . '</span>';
	}

	/**
	 * Check if a calculated field is hidden.
	 *
	 * @since 1.8.9.5
	 *
	 * @param int $field_id Field ID.
	 *
	 * @return bool
	 */
	private function is_calculated_field_hidden( $field_id ): bool {

		return ! empty( $this->form_data['fields'][ $field_id ]['calculation_is_enabled'] ) &&
			! empty( $this->form_data['fields'][ $field_id ]['calculation_code_php'] ) &&
			isset( $this->fields[ $field_id ]['visible'] )
			&& ! $this->fields[ $field_id ]['visible'];
	}

	/**
	 * Process a smart tag.
	 *
	 * @since 1.8.5
	 *
	 * @param string $input   Smart tag.
	 * @param string $context Context of the smart tag.
	 *
	 * @return string
	 */
	private function process_tag( $input = '', $context = 'notification' ): string {

		$context_data = [];

		/**
		 * Email(s).
		 *
		 * @var string|string[] $to_email
		 */
		$to_email                 = array_filter( (array) ( $this->__get( 'to_email' ) ?? '' ) );
		$context_data['to_email'] = $to_email;

		return wpforms_process_smart_tags( $input, $this->form_data, $this->fields, (string) $this->entry_id, $context, $context_data );
	}

	/**
	 * Filter the smart tag value for the mailer email addresses.
	 *
	 * @since 1.9.5
	 *
	 * @param string|mixed $value            Smart Tag value.
	 * @param string       $tag_name         Smart tag name.
	 * @param array        $form_data        Form data.
	 * @param array        $fields           List of fields.
	 * @param int          $entry_id         Entry ID.
	 * @param SmartTag     $smart_tag_object The smart tag object or the Generic object for those cases when class
	 *                                       unregistered.
	 *
	 * @return string|null
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public static function filter_smarttags_process_value( $value, $tag_name, $form_data, $fields, $entry_id, $smart_tag_object ): ?string {

		$tag_name = (string) $tag_name;
		$fields   = (array) $fields;

		// Smart tag isn't registered and can be replaced via filters.
		if ( $value === null ) {
			return null;
		}

		$value        = (string) $value;
		$context      = $smart_tag_object->context ?? '';
		$allowed_tags = [
			'admin_email',
			'user_email',
		];

		// In these contexts, we need to check if the smart tag is allowed.
		$address_context = [
			'notification-from',
		];

		// Check if the smart tag is allowed AND if the context is allowed.
		if ( in_array( $tag_name, $allowed_tags, true ) || ! in_array( $context, $address_context, true ) ) {
			return $value;
		}

		return self::validate_notification_email_smart_tags( $value, $tag_name, $fields, $smart_tag_object );
	}

	/**
	 * Validate notification email fields.
	 *
	 * @since 1.9.5
	 *
	 * @param string|mixed $value            Smart Tag value.
	 * @param string       $tag_name         Smart tag name.
	 * @param array        $fields           List of fields.
	 * @param SmartTag     $smart_tag_object The smart tag object or the Generic object for those cases when class unregistered.
	 *
	 * @return string
	 */
	private static function validate_notification_email_smart_tags( string $value, string $tag_name, array $fields, SmartTag $smart_tag_object ): string {

		$field_id = self::get_smart_tag_field_id( $tag_name, $smart_tag_object );

		// Empty value for all non-field smart tags.
		if ( $field_id === null || $field_id === '' || ! isset( $fields[ $field_id ]['type'] ) ) {
			return '';
		}

		$field_type = $fields[ $field_id ]['type'];

		// If the field type is Email, return the value.
		if ( $field_type === 'email' ) {
			return $value;
		}

		// Allow the Name field value in the Reply To setting.
		if ( $field_type === 'name' && $smart_tag_object->context === 'notification-reply-to' ) {
			return $value;
		}

		// Otherwise, return an empty string if the value is not an email.
		return wpforms_is_email( $value ) ? $value : '';
	}

	/**
	 * Get smart tag field ID.
	 *
	 * @since 1.9.5
	 *
	 * @param string   $tag_name         Smart tag name.
	 * @param SmartTag $smart_tag_object The smart tag object or the Generic object for those cases when class unregistered.
	 *
	 * @return mixed|string|null
	 */
	private static function get_smart_tag_field_id( string $tag_name, SmartTag $smart_tag_object ) {

		if ( $tag_name === 'field_value_id' ) {
			return $smart_tag_object->get_attributes()[ $tag_name ] ?? null;
		}

		if ( $tag_name !== 'field_id' ) {
			return null;
		}

		$field_id_parts = explode( '|', $smart_tag_object->get_attributes()['field_id'] ?? '' );

		return $field_id_parts[0] ?? null;
	}

	/**
	 * Process special field types.
	 * This is used for fields such as Page Break, HTML, Content, etc.
	 *
	 * @since 1.8.5
	 *
	 * @param array $field Field data.
	 *
	 * @return array
	 */
	private function process_special_field_values( $field ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity

		$field_name = null;
		$field_val  = null;

		// Use a switch-case statement to handle specific field types.
		switch ( $field['type'] ) {
			case 'divider':
				$field_name = ! empty( $field['label'] ) ? str_repeat( '&mdash;', 3 ) . ' ' . $field['label'] . ' ' . str_repeat( '&mdash;', 3 ) : null;
				$field_val  = ! empty( $field['description'] ) ? $field['description'] : '';
				break;

			case 'pagebreak':
				// Skip if the position is 'bottom'.
				if ( ! empty( $field['position'] ) && $field['position'] === 'bottom' ) {
					break;
				}

				$title      = ! empty( $field['title'] ) ? $field['title'] : esc_html__( 'Page Break', 'wpforms-lite' );
				$field_name = str_repeat( '&mdash;', 6 ) . ' ' . $title . ' ' . str_repeat( '&mdash;', 6 );
				break;

			case 'html':
				$field_name = ! empty( $field['name'] ) ? $field['name'] : esc_html__( 'HTML / Code Block', 'wpforms-lite' );
				$field_val  = $field['code'];
				break;

			case 'content':
				$field_name = esc_html__( 'Content', 'wpforms-lite' );
				$field_val  = wpforms_esc_richtext_field( $field['content'] );
				break;

			default:
				$field_name = '';
				$field_val  = '';
				break;
		}

		return [ $field_name, $field_val ];
	}

	/**
	 * Get the email reply to the address.
	 * This method has been overridden to add support for the Reply-to Name.
	 *
	 * @since 1.8.5
	 *
	 * @return string
	 */
	public function get_reply_to_address() {

		$reply_to      = $this->__get( 'reply_to' );
		$reply_to_name = false;

		if ( ! empty( $reply_to ) ) {

			// \h: With the u modifier escape sequence matches any horizontal whitespace character,
			// which includes the non-breaking and zero width spaces.
			$reply_to = preg_replace( '/\h/u', ' ', $reply_to );

			// Optional custom format with a Reply-to Name specified: John Doe <john@doe.com>
			// - starts with anything,
			// - followed by space,
			// - ends with <anything> (expected to be an email, validated later).
			$regex   = '/^(.+) (<.+>)$/';
			$matches = [];

			if ( preg_match( $regex, $reply_to, $matches ) ) {
				$reply_to_name = $this->sanitize( $matches[1] );
				$reply_to      = trim( $matches[2], '<> ' );
			}

			$reply_to = $this->process_tag( $reply_to, 'notification-reply-to' );

			if ( ! is_email( $reply_to ) ) {
				$reply_to      = false;
				$reply_to_name = false;
			}
		}

		if ( $reply_to_name ) {
			$reply_to = "$reply_to_name <{$reply_to}>";
		}

		/**
		 * Filter the email reply-to address.
		 *
		 * @since 1.8.5
		 *
		 * @param string $reply_to Email reply-to address.
		 * @param object $this     Instance of the Notifications class.
		 */
		return apply_filters( 'wpforms_emails_notifications_get_reply_to_address', $reply_to, $this );
	}

	/**
	 * Sanitize the string.
	 * This method has been overridden to add support for processing smart tags.
	 *
	 * @since 1.8.5
	 *
	 * @param string $input   String to sanitize and process for smart tags.
	 * @param string $context Context of the smart tag.
	 *
	 * @return string
	 */
	public function sanitize( $input = '', $context = 'notification' ): string {

		return wpforms_decode_string( $this->process_tag( $input, $context ) );
	}

	/**
	 * Get the email content type.
	 * This method has been overridden to better declare the email template assigned to each notification.
	 *
	 * @since 1.8.5.2
	 *
	 * @return string
	 */
	public function get_content_type() {

		$content_type = 'text/html';

		if ( Helpers::is_plain_text_template( $this->current_template ) ) {
			$content_type = 'text/plain';
		}

		/**
		 * Filter the email content type.
		 *
		 * @since 1.8.5.2
		 *
		 * @param string        $content_type The email content type.
		 * @param Notifications $this         An instance of the "Notifications" class.
		 */
		$content_type = apply_filters( 'wpforms_emails_notifications_get_content_type', $content_type, $this );

		// Set the content type.
		$this->__set( 'content_type', $content_type );

		// Return the content type.
		return $content_type;
	}

	/**
	 * Check if all emails are disabled.
	 *
	 * @since 1.8.5
	 *
	 * @return bool
	 */
	public function is_email_disabled() {

		/**
		 * Filter to control email disabling.
		 *
		 * The "Notifications" class is designed to mirror the properties and methods
		 * provided by the "WPForms_WP_Emails" class for backward compatibility.
		 *
		 * @since 1.8.5
		 *
		 * @param bool          $is_disabled Whether to disable all emails.
		 * @param Notifications $this        An instance of the "Notifications" class.
		 */
		return (bool) apply_filters( // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
			'wpforms_disable_all_emails',
			false,
			$this
		);
	}

	/**
	 * Get the default field name as a fallback.
	 *
	 * @since 1.8.5
	 *
	 * @param int $field_id Field ID.
	 *
	 * @return string
	 */
	private function get_default_field_name( $field_id ) {

		return sprintf( /* translators: %1$d - field ID. */
			esc_html__( 'Field ID #%1$s', 'wpforms-lite' ),
			wpforms_validate_field_id( $field_id )
		);
	}

	/**
	 * Wrap content in the 'tr' tag on the first level depth.
	 *
	 * @since 1.9.6
	 *
	 * @param string $content Processed smart tag content.
	 *
	 * @return string
	 */
	private function fix_table_body_markup( string $content ): string {

		$content = trim( $content );

		libxml_use_internal_errors( true );

		$dom = new DOMDocument( '1.0', 'UTF-8' );

		// We should encode `<` and `>` symbols to prevent unexpected HTML tags.
		$html = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . wp_pre_kses_less_than( $content ) . '</body></html>';

		$dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR );

		libxml_clear_errors();

		$body = $dom->getElementsByTagName( 'body' )->item( 0 );

		if ( ! $body ) {
			return $this->wrap_content_with_row( $content );
		}

		$modified_content = '';
		$content_to_wrap  = '';

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		foreach ( $body->childNodes as $node ) {
			$node_text = $node->nodeType === XML_TEXT_NODE ? $node->nodeValue : $dom->saveHTML( $node );

			if ( ! property_exists( $node, 'tagName' ) || $node->tagName !== 'tr' ) {
				$content_to_wrap .= $node_text;

				continue;
			}

			// Wrap content before the `tr` tag.
			$modified_content .= $this->wrap_content_with_row( $content_to_wrap );

			// Save the `tr` tag without wrapping.
			$modified_content .= $node_text;

			$content_to_wrap = '';
		}
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		if ( ! wpforms_is_empty_string( $content_to_wrap ) ) {
			$modified_content .= $this->wrap_content_with_row( $content_to_wrap );
		}

		return $modified_content;
	}

	/**
	 * Wrap content to the `tr` tag.
	 *
	 * @since 1.9.6
	 *
	 * @param string $content Content.
	 *
	 * @return string
	 */
	private function wrap_content_with_row( string $content ): string {

		$content = trim( $content );

		if ( wpforms_is_empty_string( $content ) ) {
			return '';
		}

		return sprintf( '<tr class="smart-tag"><td class="field-name field-value" colspan="2">%s</td></tr>', $content );
	}

	/**
	 * Get the list of available email templates.
	 *
	 * Given a template name, this method will return the template data.
	 * If no template name is provided, all available templates will be returned.
	 *
	 * Templates will go through a conditional check to make sure they are available for the current plugin edition.
	 *
	 * @since 1.8.5
	 *
	 * @param string $template Template name. If empty, all available templates will be returned.
	 *
	 * @return array
	 */
	public static function get_available_templates( $template = '' ) {

		$templates = self::get_all_templates();

		// Filter the list of available email templates based on the edition of WPForms.
		if ( ! wpforms()->is_pro() ) {
			$templates = array_filter(
				$templates,
				static function ( $instance ) {

					return ! $instance['is_pro'];
				}
			);
		}

		return $templates[ $template ] ?? $templates;
	}

	/**
	 * Get the list of all email templates.
	 *
	 * Given the name of a template, this method will return the template data.
	 * If the template is not found, all available templates will be returned.
	 *
	 * @since 1.8.5
	 *
	 * @param string $template Template name. If empty, all templates will be returned.
	 *
	 * @return array
	 */
	public static function get_all_templates( $template = '' ) {

		$templates = [
			'classic' => [
				'name'   => esc_html__( 'Classic', 'wpforms-lite' ),
				'path'   => Templates\Classic::class,
				'is_pro' => false,
			],
			'compact' => [
				'name'   => esc_html__( 'Compact', 'wpforms-lite' ),
				'path'   => Templates\Compact::class,
				'is_pro' => false,
			],
			'modern'  => [
				'name'   => esc_html__( 'Modern', 'wpforms-lite' ),
				'path'   => Modern::class,
				'is_pro' => true,
			],
			'elegant' => [
				'name'   => esc_html__( 'Elegant', 'wpforms-lite' ),
				'path'   => Elegant::class,
				'is_pro' => true,
			],
			'tech'    => [
				'name'   => esc_html__( 'Tech', 'wpforms-lite' ),
				'path'   => Tech::class,
				'is_pro' => true,
			],
			'none'    => [
				'name'   => esc_html__( 'Plain Text', 'wpforms-lite' ),
				'path'   => Templates\Plain::class,
				'is_pro' => false,
			],
		];

		// Make sure the current user can preview templates.
		if ( wpforms_current_user_can() ) {
			// Add a preview key to each template.
			foreach ( $templates as $key => &$tmpl ) {
				$tmpl['preview'] = wp_nonce_url(
					add_query_arg(
						[
							'wpforms_email_preview'  => '1',
							'wpforms_email_template' => $key,
						],
						admin_url()
					),
					Preview::PREVIEW_NONCE_NAME
				);
			}

			// Make sure to unset the reference to avoid unintended changes later.
			unset( $tmpl );
		}

		return $templates[ $template ] ?? $templates;
	}

	/**
	 * Get multiple field formatted value.
	 *
	 * @since 1.9.0
	 *
	 * @param string $value     Field value.
	 * @param int    $field_id  Field ID.
	 * @param array  $fields    List of fields.
	 * @param string $field_key Field key to get value from.
	 *
	 * @return string
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function get_multi_field_formatted_value( string $value, int $field_id, array $fields, string $field_key ): string {

		$field_type = $fields[ $field_id ]['type'] ?? '';

		// Leave early if the field type is not a multi-field.
		if ( ! in_array( $field_type, wpforms_get_multi_fields(), true ) ) {
			return $value;
		}

		// Leave early if the template is set to plain text.
		if ( Helpers::is_plain_text_template( $this->current_template ) ) {
			// Replace <br/> tags with line breaks.
			return str_replace( '<br/>', "\r\n", $value );
		}

		return str_replace( [ "\r\n", "\r", "\n" ], '<br/>', $value );
	}

	/**
	 * Get the current template name.
	 *
	 * @since 1.9.3
	 *
	 * @return string
	 */
	public function get_current_template(): string {

		return $this->current_template;
	}

	/**
	 * Get the current field template markup.
	 *
	 * @since 1.9.4
	 *
	 * @return string
	 */
	public function get_current_field_template(): string {

		return $this->field_template;
	}
}
