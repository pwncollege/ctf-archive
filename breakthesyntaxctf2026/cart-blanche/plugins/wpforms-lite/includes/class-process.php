<?php

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection AutoloadingIssuesInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

// phpcs:ignore WPForms.PHP.UseStatement.UnusedUseStatement
use WPForms\Emails\Mailer;
use WPForms\Emails\Notifications;

/**
 * Process and validate form entries.
 *
 * @since 1.0.0
 */
class WPForms_Process {

	/**
	 * Store errors.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $errors;

	/**
	 * Store spam errors.
	 *
	 * @since 1.8.3
	 *
	 * @var array
	 */
	public $spam_errors;

	/**
	 * Spam reason.
	 *
	 * @since 1.8.3
	 *
	 * @var string
	 */
	public $spam_reason;

	/**
	 * Confirmation message.
	 *
	 * @since 1.5.3
	 *
	 * @var string
	 */
	public $confirmation_message;

	/**
	 * Current confirmation.
	 *
	 * @since 1.6.9
	 *
	 * @var array
	 */
	private $confirmation;

	/**
	 * Store formatted fields.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $fields;

	/**
	 * Store the ID of a successful entry.
	 *
	 * @since 1.2.3
	 *
	 * @var int
	 */
	public $entry_id = 0;

	/**
	 * Form data and settings.
	 *
	 * @since 1.4.5
	 *
	 * @var array
	 */
	public $form_data;

	/**
	 * If a valid return has been processed.
	 *
	 * @since 1.4.5
	 *
	 * @var bool
	 */
	public $valid_hash = false;

	/**
	 * Email handler.
	 *
	 * @since 1.9.4
	 *
	 * @var mixed|Mailer|WPForms_WP_Emails|null
	 */
	private $email_handler;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.8.3
	 */
	private function hooks() {

		add_action( 'wp', [ $this, 'listen' ] );
		add_action( 'wp_ajax_wpforms_submit', [ $this, 'ajax_submit' ] );
		add_action( 'wp_ajax_nopriv_wpforms_submit', [ $this, 'ajax_submit' ] );
		add_filter( 'wpforms_ajax_submit_redirect', [ $this, 'maybe_open_in_new_tab' ] );
		add_filter( 'wpforms_smarttags_process_value', [ Notifications::class, 'filter_smarttags_process_value' ], PHP_INT_MAX, 6 );
	}

	/**
	 * Listen to see if this is a return callback or a posted form entry.
	 *
	 * @since 1.0.0
	 */
	public function listen() {

		// Catch the post_max_size overflow.
		if ( $this->post_max_size_overflow() ) {
			return;
		}

		// phpcs:disable WordPress.Security.NonceVerification
		if ( ! empty( $_GET['wpforms_return'] ) ) {
			// Additional redirect trigger for addons.
			$this->entry_confirmation_redirect( '', sanitize_text_field( wp_unslash( $_GET['wpforms_return'] ) ) );
		}

		$form_id = ! empty( $_POST['wpforms']['id'] ) ? absint( $_POST['wpforms']['id'] ) : 0;

		if ( ! $form_id ) {
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$this->process( wp_unslash( $_POST['wpforms'] ) );
		// phpcs:enable WordPress.Security.NonceVerification

		/**
		 * Runs right after the processing form entry.
		 *
		 * @since 1.9.0
		 *
		 * @param array  $fields    Entry fields data.
		 * @param array  $entry_id  Entry ID.
		 * @param array  $form_data Form data.
		 */
		do_action( 'wpforms_process_after', $this->fields, $this->entry_id, $this->form_data );

		if ( ! wpforms_is_amp() ) {
			return;
		}

		// Send 400 Bad Request when there are errors.
		if ( empty( $this->errors[ $form_id ] ) ) {
			$this->entry_confirmation_redirect( $this->form_data );
			wp_send_json(
				[
					'message' => $this->get_confirmation_message( $this->form_data, $this->fields, $this->entry_id ),
				],
				200
			);

			return;
		}

		$message_parts = [ $this->errors[ $form_id ]['header'] ];

		if ( ! empty( $this->errors[ $form_id ]['recaptcha'] ) ) {
			$message_parts[] = $this->errors[ $form_id ]['recaptcha'];
		}

		if ( ! empty( $this->errors[ $form_id ]['footer'] ) ) {
			$message_parts[] = $this->errors[ $form_id ]['footer'];
		}

		wp_send_json(
			[
				'message' => implode( '<br>', $message_parts ),
			],
			400
		);
	}

	/**
	 * Process the form entry.
	 *
	 * @since 1.0.0
	 * @since 1.6.4 Added hCaptcha support.
	 *
	 * @param array $entry Form submission raw data ($_POST).
	 *
	 * @noinspection HtmlUnknownTarget
	 */
	public function process( $entry ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded, Generic.Metrics.NestingLevel.MaxExceeded

		$this->errors = [];
		$this->fields = [];

		$form_id = absint( $entry['id'] );
		$form    = wpforms()->obj( 'form' )->get( $form_id );

		// Validate form is real and active (published).
		if ( ! $form || $form->post_status !== 'publish' ) {
			$this->errors[ $form_id ]['header'] = esc_html__( 'Invalid form.', 'wpforms-lite' );

			return;
		}

		/**
		 * Filter form data obtained during the form process.
		 *
		 * @since 1.5.3
		 *
		 * @param array $form_data Form data.
		 * @param array $entry     Form entry.
		 */
		$this->form_data = (array) apply_filters( 'wpforms_process_before_form_data', wpforms_decode( $form->post_content ), $entry );

		if ( ! empty( $this->form_data['settings']['ajax_submit'] ) && ! $this->is_valid_ajax_submit_action() ) {
			wpforms_log(
				'Attempt to submit corrupted post data.',
				wp_unslash( $_POST ),
				[
					'type'    => [ 'error', 'entry' ],
					'form_id' => $this->form_data['id'],
				]
			);

			$this->errors[ $form_id ]['header'] = esc_html__( 'Attempt to submit corrupted post data.', 'wpforms-lite' );

			/**
			 * Fires when corrupted form submission is detected.
			 *
			 * @since 1.9.8
			 *
			 * @param array $form_data Form data.
			 */
			do_action( 'wpforms_process_submission_corrupted', $this->form_data );

			return;
		}

		$store_spam_entries = ! empty( $this->form_data['settings']['store_spam_entries'] );

		/**
		 * Check the modern Anti-Spam (v3) protection.
		 *
		 * Run as early as possible to remove the honeypot field from the entry to prevent unnecessary field processing.
		 * Bail early if the form is marked as spam and storing spam entries is disabled.
		 *
		 * Important! We should check first on modern Anti-Spam because it is skipped in case $store_spam_entries === true.
		 */
		// phpcs:ignore Generic.Commenting.DocComment.MissingShort
		/** @noinspection NotOptimalIfConditionsInspection */
		if ( ! $this->modern_anti_spam_check( $entry ) && ! $store_spam_entries ) {
			return;
		}

		if ( ! isset( $this->form_data['fields'], $this->form_data['id'] ) ) {
			$error_id = uniqid( '', true );

			// Logs missing form data.
			wpforms_log(
				/* translators: %s - error unique ID. */
				sprintf( esc_html__( 'Missing form data on form submission process %s', 'wpforms-lite' ), $error_id ),
				esc_html__( 'Form data is not an array in `\WPForms_Process::process()`. It might be caused by incorrect data returned by `wpforms_process_before_form_data` filter. Verify whether you have a custom code using this filter and debug value it is returning.', 'wpforms-lite' ),
				[
					'type'    => [ 'error', 'entry' ],
					'form_id' => $form_id,
				]
			);

			$error_messages[] = esc_html__( 'Your form has not been submitted because data is missing from the entry.', 'wpforms-lite' );

			if ( wpforms_setting( 'logs-enable' ) && wpforms_current_user_can( wpforms_get_capability_manage_options() ) ) {
				$error_messages[] = sprintf(
					wp_kses( /* translators: %s - URL to the WForms Logs admin page. */
						__( 'Check the WPForms &raquo; Tools &raquo; <a href="%s">Logs</a> for more details.', 'wpforms-lite' ),
						[ 'a' => [ 'href' => [] ] ]
					),
					esc_url(
						add_query_arg(
							[
								'page' => 'wpforms-tool',
								'view' => 'logs',
							],
							admin_url( 'admin.php' )
						)
					)
				);

				/* translators: %s - error unique ID. */
				$error_messages[] = sprintf( esc_html__( 'Error ID: %s.', 'wpforms-lite' ), $error_id );
			}

			$errors[ $form_id ]['header'] = implode( '<br>', $error_messages );
			$this->errors                 = $errors;

			return;
		}

		/**
		 * Filter form entry before processing.
		 * Data are not validated or cleaned yet, so use them with caution.
		 *
		 * @since 1.4.0
		 *
		 * @param array $entry     Form submission raw data ($_POST).
		 * @param array $form_data Form data.
		 */
		$entry = apply_filters( 'wpforms_process_before_filter', $entry, $this->form_data );

		/**
		 * Pre-process hook.
		 *
		 * @since 1.4.0
		 *
		 * @param array $entry     Form submission raw data ($_POST).
		 * @param array $form_data Form data.
		 */
		do_action( 'wpforms_process_before', $entry, $this->form_data );

		/**
		 * Pre-process hook by form ID.
		 *
		 * @since 1.4.0
		 *
		 * @param array $entry     Form submission raw data ($_POST).
		 * @param array $form_data Form data.
		 */
		do_action( "wpforms_process_before_{$form_id}", $entry, $this->form_data );

		// Validate fields.
		foreach ( $this->form_data['fields'] as $field_properties ) {

			$field_id     = $field_properties['id'];
			$field_type   = $field_properties['type'];
			$field_submit = $entry['fields'][ $field_id ] ?? '';

			/**
			 * Field type validation hook.
			 *
			 * @since 1.4.0
			 *
			 * @param string|int $field_id     Field ID as a numeric string.
			 * @param mixed      $field_submit Submitted field value (raw data).
			 * @param array      $form_data    Form data.
			 */
			do_action( "wpforms_process_validate_{$field_type}", $field_id, $field_submit, $this->form_data );
		}

		// Check if combined upload size exceeds allowed maximum.
		$this->validate_combined_upload_size( $form );

		/**
		 * Filter initial errors.
		 * Don't proceed if there are any errors thus far.
		 * We provide a filter so that other features, such as conditional logic, can adjust blocking errors.
		 *
		 * @since 1.4.0
		 *
		 * @param array $errors     List of errors.
		 * @param array $form_data  Form data.
		 */
		$errors = apply_filters( 'wpforms_process_initial_errors', $this->errors, $this->form_data );

		if ( isset( $_POST['__amp_form_verify'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( empty( $errors[ $form_id ] ) ) {
				wp_send_json( [], 200 );
			} else {
				$verify_errors = [];

				foreach ( $errors[ $form_id ] as $field_id => $error_fields ) {
					$field            = $this->form_data['fields'][ $field_id ];
					$field_properties = wpforms()->obj( 'frontend' )->get_field_properties( $field, $this->form_data );

					if ( is_string( $error_fields ) ) {
						$name = '';

						if ( $field['type'] === 'checkbox' || $field['type'] === 'radio' || $field['type'] === 'select' ) {
							$first = current( $field_properties['inputs'] );
							$name  = $first['attr']['name'];
						} elseif ( isset( $field_properties['inputs']['primary']['attr']['name'] ) ) {
							$name = $field_properties['inputs']['primary']['attr']['name'];
						}

						$verify_errors[] = [
							'name'    => $name,
							'message' => $error_fields,
						];
					} else {
						foreach ( $error_fields as $error_field => $error_message ) {
							$name = $field_properties['inputs'][ $error_field ]['attr']['name'] ?? '';

							$verify_errors[] = [
								'name'    => $name,
								'message' => $error_message,
							];
						}
					}
				}

				wp_send_json(
					[
						'verifyErrors' => $verify_errors,
					],
					400
				);
			}

			return;
		}

		if ( ! empty( $errors[ $form_id ] ) ) {

			if ( empty( $errors[ $form_id ]['header'] ) && empty( $errors[ $form_id ]['footer'] ) ) {
				$errors[ $form_id ]['header'] = esc_html__( 'Form has not been submitted, please see the errors below.', 'wpforms-lite' );
			}

			$this->errors = $errors;

			return;
		}

		// If a logged-in user fails the nonce check, we want to log the entry, disable the errors and fail silently.
		// Please note that logs may be disabled, and in this case, nothing will be logged or reported.
		if (
			is_user_logged_in() &&
			( empty( $entry['nonce'] ) || ! wp_verify_nonce( $entry['nonce'], "wpforms::form_{$form_id}" ) )
		) {
			// Logs XSS attempt depending on log levels set.
			wpforms_log(
				'Cross-site scripting attempt ' . uniqid( '', true ),
				[ true, $entry ],
				[
					'type'    => [ 'security' ],
					'form_id' => $this->form_data['id'],
				]
			);

			$this->errors[ $this->form_data['id'] ]['footer_styled'] = esc_html__( 'The form could not be submitted due to a security issue.', 'wpforms-lite' );

			return;
		}

		// Format fields.
		foreach ( (array) $this->form_data['fields'] as $field_properties ) {

			$field_id     = $field_properties['id'];
			$field_type   = $field_properties['type'];
			$field_submit = $entry['fields'][ $field_id ] ?? '';

			/**
			 * Format field by type.
			 *
			 * @since 1.4.0
			 *
			 * @param string $field_id     Field ID.
			 * @param string $field_submit Submitted field value.
			 * @param array  $form_data    Form data and settings.
			 */
			do_action( "wpforms_process_format_{$field_type}", $field_id, $field_submit, $this->form_data );
		}

		$honeypot = wpforms()->obj( 'honeypot' )->validate( $this->form_data, $this->fields, $entry );

		// If we trigger the honey pot, we want to log the entry, disable the errors, and fail silently.
		if ( $honeypot ) {

			$this->log_spam_entry( $entry, $honeypot );

			// Fail silently.
			return;
		}

		$token = wpforms()->obj( 'token' )->validate( $this->form_data, $this->fields, $entry );

		// If spam - return early.
		// For antispam, we want to make sure that we have a value, we are not using AMP, and the value is an error string.
		if ( $token && is_string( $token ) && ! wpforms_is_amp() ) {
			$this->errors[ $this->form_data['id'] ]['header'] = $token;

			$this->log_spam_entry( $entry, $token );

			return;
		}

		// Detect direct POST requests when the AJAX submission is enabled.
		$this->direct_post_request_check( $entry );

		$is_pro = wpforms()->is_pro();

		if ( ! $this->is_bypass_spam_check( $entry ) ) {
			// Store spam entries detected by filtering.
			if ( $is_pro && ! empty( $this->form_data['settings']['anti_spam']['filtering_store_spam'] ) ) {
				$this->country_filter_check( $entry, $form_id );
				$this->keyword_filter_check( $entry, $form_id );
			}

			// Check if the form was submitted too quickly.
			$this->time_limit_check();

			// Check for spam.
			$this->process_spam_check( $entry );
		}

		// Convert spam errors to form errors if spam entries are not stored.
		if ( ! $store_spam_entries && ! empty( $this->spam_errors ) ) {
			$this->errors = $this->spam_errors;
		}

		// Store spam reason.
		if ( $this->spam_reason ) {
			$this->form_data['spam_reason'] = $this->spam_reason;
		}

		// Pass the form creation date into the form data.
		$this->form_data['created'] = $form->post_date;

		/**
		 * Format form data after all fields have been processed.
		 * This hook is for internal purposes and should not be leveraged.
		 *
		 * @since 1.4.0
		 *
		 * @param array $form_data Form data and settings.
		 */
		do_action( 'wpforms_process_format_after', $this->form_data );

		/**
		 * Filter fields before processing.
		 * Process hooks/filter - this is where most addons should hook
		 * because at this point we have completed all field validations and formatted the data.
		 *
		 * @since 1.4.0
		 *
		 * @param array $fields    Form fields.
		 * @param array $entry     Form submission raw data ($_POST).
		 * @param array $form_data Form data and settings.
		 */
		$this->fields = apply_filters( 'wpforms_process_filter', $this->fields, $entry, $this->form_data );
		/**
		 * Process form fields.
		 *
		 * @since 1.4.0
		 *
		 * @param array $fields    Form fields.
		 * @param array $entry     Form submission raw data ($_POST).
		 * @param array $form_data Form data and settings.
		 */
		do_action( 'wpforms_process', $this->fields, $entry, $this->form_data );

		/**
		 * Process form fields by form ID.
		 *
		 * @since 1.4.0
		 *
		 * @param array $fields    Form fields.
		 * @param array $entry     Form submission raw data ($_POST).
		 * @param array $form_data Form data and settings.
		 */
		do_action( "wpforms_process_{$form_id}", $this->fields, $entry, $this->form_data );

		/**
		 * Filter fields after processing.
		 *
		 * @since 1.4.0
		 *
		 * @param array $fields    Form fields.
		 * @param array $entry     Form submission raw data ($_POST).
		 * @param array $form_data Form data and settings.
		 */
		$this->fields = apply_filters( 'wpforms_process_after_filter', $this->fields, $entry, $this->form_data );

		// One last error check - don't proceed if there are any errors.
		if ( ! empty( $this->errors[ $form_id ] ) ) {

			if ( empty( $this->errors[ $form_id ]['header'] ) && empty( $this->errors[ $form_id ]['footer'] ) ) {
				$this->errors[ $form_id ]['header'] = esc_html__( 'Form has not been submitted, please see the errors below.', 'wpforms-lite' );
			}

			return;
		}

		// Set raw post data.
		$this->form_data['post_data_raw'] = [
			'page_url' => isset( $_POST['page_url'] ) ? esc_url_raw( wp_unslash( $_POST['page_url'] ) ) : '',
		];

		// Success - add entry to a database.
		$this->entry_id = $this->entry_save( $this->fields, $entry, $this->form_data['id'], $this->form_data );

		// Add payment to a database.
		$payment_id = $this->payment_save( $entry );

		$this->form_data['entry_meta'] = [
			'page_url'    => isset( $_POST['page_url'] ) ? esc_url_raw( wp_unslash( $_POST['page_url'] ) ) : '',
			'page_title'  => isset( $_POST['page_title'] ) ? sanitize_text_field( wp_unslash( $_POST['page_title'] ) ) : '',
			'page_id'     => isset( $_POST['page_id'] ) ? absint( $_POST['page_id'] ) : '',
			'url_referer' => isset( $_POST['url_referer'] ) ? esc_url_raw( wp_unslash( $_POST['url_referer'] ) ) : '',
			'user_id'     => get_current_user_id(),
		];

		// Save meta data.
		$this->save_meta( $this->entry_id, $this->form_data['id'] );

		/**
		 * Runs right after adding an entry to the database.
		 *
		 * @since 1.7.7
		 * @since 1.8.2 Added Payment ID param.
		 *
		 * @param array $fields     Fields data.
		 * @param array $entry      User submitted data.
		 * @param array $form_data  Form data.
		 * @param int   $entry_id   Entry ID.
		 * @param int   $payment_id Payment ID.
		 */
		do_action( 'wpforms_process_entry_saved', $this->fields, $entry, $this->form_data, $this->entry_id, $payment_id );

		// Fire the logic to send notification emails.
		$this->entry_email( $this->fields, $entry, $this->form_data, $this->entry_id, 'entry' );

		// Pass completed and formatted fields in POST.
		$_POST['wpforms']['complete'] = $this->fields;

		// Pass entry ID in POST.
		$_POST['wpforms']['entry_id'] = $this->entry_id;

		// Logs entry depending on log levels set.
		if ( $is_pro ) {
			wpforms_log(
				$this->entry_id ? "Entry {$this->entry_id}" : 'Entry',
				$this->fields,
				[
					'type'    => [ 'entry' ],
					'parent'  => $this->entry_id,
					'form_id' => $this->form_data['id'],
				]
			);
		}

		// Mark the submission as spam if one of the spam checks failed and spam entries are stored.
		$marked_as_spam = $this->spam_reason && $store_spam_entries;

		// Proceed if the entry is not marked as spam.
		if ( ! $marked_as_spam ) {
			$this->process_complete( $form_id, $this->form_data, $this->fields, $entry, $this->entry_id );
		} else {
			/**
			 * Fires in the case the entry was marked as spam during the form submission.
			 *
			 * @since 1.9.8.1
			 *
			 * @param int $entry_id Entry ID.
			 * @param int $form_id  Form ID.
			 */
			do_action( 'wpforms_process_anti_spam_entry_marked_as_spam', $this->entry_id, $form_id );
		}

		$this->entry_confirmation_redirect( $this->form_data );
	}

	/**
	 * Run the modern Anti-Spam check.
	 *
	 * @since 1.9.0
	 *
	 * @param array $entry Form submission raw data ($_POST).
	 *
	 * @return bool True if the modern Anti-Spam check was passed, false otherwise.
	 */
	private function modern_anti_spam_check( array &$entry ): bool {

		// Skip if spam was already detected.
		if ( $this->spam_reason ) {
			return false;
		}

		/**
		 * Allow bypassing the modern Anti-Spam check.
		 *
		 * @since 1.9.0
		 *
		 * @param bool  $bypass    Whether to bypass the modern Anti-Spam check, default false.
		 * @param array $form_data Form data.
		 * @param array $entry     Form submission raw data ($_POST).
		 *
		 * @return bool
		 */
		if ( apply_filters( 'wpforms_process_anti_spam_honeypot_bypass', false, $this->form_data, $entry ) ) {
			return true;
		}

		// Skip if the modern Anti-Spam check was passed.
		if ( wpforms()->obj( 'anti_spam' )->validate( $this->form_data, $this->fields, $entry ) ) {
			return true;
		}

		$form_id = $this->form_data['id'] ?? 0;

		$this->spam_errors[ $form_id ]['header'] = esc_html__( 'Anti-spam Honeypot V2 verification was failed, please try again later.', 'wpforms-lite' );

		$this->spam_reason = 'Honeypot V2';

		// Log the spam entry depending on log levels set.
		$this->log_spam_entry(
			$entry,
			'Anti-spam Honeypot V2 verification was failed.'
		);

		return false;
	}

	/**
	 * Detect direct POST requests when the AJAX submission is enabled.
	 * For Anti-spam Modern (v3) enabled forms only.
	 *
	 * @since 1.9.0
	 *
	 * @param array $entry Form submission raw data ($_POST).
	 */
	private function direct_post_request_check( array $entry ) {

		if (
			// Skip if spam was already detected.
			$this->spam_reason ||

			// Skip if the Anti-spam Modern (v3) is not enabled.
			empty( $this->form_data['settings']['antispam_v3'] )
		) {
			return;
		}

		/**
		 * Allow bypassing the direct POST request check.
		 *
		 * @since 1.9.0
		 *
		 * @param bool  $bypass    Whether to bypass the direct POST request check, default is false.
		 * @param array $form_data Form data.
		 * @param array $entry     Form entry.
		 */
		if ( apply_filters( 'wpforms_process_anti_spam_direct_post_bypass', false, $this->form_data, $entry ) ) {
			return;
		}

		// phpcs:disable WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
		$is_ajax_form   = ! empty( $this->form_data['settings']['ajax_submit'] );
		$is_post        = ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) === 'POST';
		$is_direct_post = ! wpforms_is_frontend_ajax() && $is_post;
		// phpcs:enable WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput

		if ( ! ( $is_ajax_form && $is_direct_post ) ) {
			return;
		}

		$form_id = $this->form_data['id'] ?? 0;

		$this->spam_errors[ $form_id ]['header'] = esc_html__( 'Direct POST requests are not allowed when the AJAX submission is enabled.', 'wpforms-lite' );

		$this->spam_reason = esc_html__( 'Direct POST request', 'wpforms-lite' );

		// Log the spam entry depending on log levels set.
		$this->log_spam_entry(
			$entry,
			'Direct POST request form submission'
		);
	}

	/**
	 * Run Country filter check.
	 *
	 * @since 1.9.2
	 *
	 * @param array $entry   Form submission raw data ($_POST).
	 * @param int   $form_id Form ID.
	 */
	private function country_filter_check( array $entry, int $form_id ) {

		// Skip if spam was already detected.
		if ( $this->spam_reason ) {
			return;
		}

		$country_filter = wpforms()->obj( 'antispam_country_filter' );

		// Skip if the Country check was passed.
		if ( $country_filter->is_valid( $this->form_data ) ) {
			return;
		}

		$error_message = $country_filter->get_error_message( $this->form_data );

		if ( $this->is_block_submission_by_spam_filtering_enabled() ) {
			$this->errors[ $form_id ]['footer'] = $error_message;

			return;
		}

		$this->spam_errors[ $form_id ]['footer'] = $error_message;

		$this->spam_reason = 'Country Filter';

		// Log the spam entry depending on log levels set.
		$this->log_spam_entry(
			$entry,
			'Country filter verification was failed.'
		);
	}

	/**
	 * Run Keyword filter check.
	 *
	 * @since 1.9.2
	 *
	 * @param array $entry   Form submission raw data ($_POST).
	 * @param int   $form_id Form ID.
	 */
	private function keyword_filter_check( array $entry, int $form_id ) {

		// Skip if spam was already detected.
		if ( $this->spam_reason ) {
			return;
		}

		$keyword_filter = wpforms()->obj( 'antispam_keyword_filter' );

		// Skip if the Keyword check was passed.
		if ( $keyword_filter->is_valid( $this->form_data, $this->fields ) ) {
			return;
		}

		$error_message = $keyword_filter->get_error_message( $this->form_data );

		if ( $this->is_block_submission_by_spam_filtering_enabled() ) {
			$this->errors[ $form_id ]['footer'] = $error_message;

			return;
		}

		$this->spam_errors[ $form_id ]['footer'] = $error_message;

		$this->spam_reason = 'Keyword Filter';

		// Log the spam entry depending on log levels set.
		$this->log_spam_entry(
			$entry,
			'Keyword filter verification was failed.'
		);
	}

	/**
	 * Save entry meta data.
	 *
	 * @since 1.8.7
	 *
	 * @param int $entry_id Entry ID.
	 * @param int $form_id  Form ID.
	 */
	protected function save_meta( $entry_id, $form_id ) {

		if ( ! wpforms()->is_pro() ) {
			return;
		}

		$meta_data  = $this->form_data['entry_meta'];
		$entry_meta = wpforms()->obj( 'entry_meta' );

		foreach ( $meta_data as $type => $value ) {
			$entry_meta->add(
				[
					'entry_id' => $entry_id,
					'form_id'  => $form_id,
					'user_id'  => get_current_user_id(),
					'type'     => $type,
					'data'     => $value,
				],
				'entry_meta'
			);
		}
	}

	/**
	 * Log spam entry.
	 *
	 * @since 1.8.3
	 *
	 * @param array  $entry   Form submission raw data ($_POST).
	 * @param string $message Spam message.
	 */
	private function log_spam_entry( $entry, $message ) { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		// Log the spam entry message after processing the entry when the entry ID is generated.
		add_action(
			'wpforms_process_after',
			static function ( $fields, $entry_id, $form_data ) use ( $entry, $message ) {

				wpforms_log(
					'Spam Entry ' . uniqid( '', true ),
					[ $message, $entry ],
					[
						'type'    => [ 'spam' ],
						'form_id' => $form_data['id'] ?? 0,
						'parent'  => $entry_id,
					]
				);
			},
			10,
			3
		);
	}

	/**
	 * Check if the form was submitted too quickly.
	 *
	 * @since 1.8.3
	 */
	private function time_limit_check() {

		/**
		 * Allow bypassing the time limit check.
		 *
		 * @since 1.8.3
		 *
		 * @param bool  $bypass    Whether to bypass the time limit check, default false.
		 * @param array $form_data Form data.
		 *
		 * @return bool
		 */
		if ( apply_filters( 'wpforms_process_time_limit_check_bypass', false, $this->form_data ) ) {
			return;
		}

		$settings   = $this->form_data['settings'];
		$time_limit = ! empty( $settings['anti_spam']['time_limit'] ) ? $settings['anti_spam']['time_limit'] : [];

		$enabled  = ! empty( $time_limit['enable'] );
		$duration = ! empty( $time_limit['duration'] ) ? absint( $time_limit['duration'] ) : 0;

		if ( ! $enabled || $duration <= 0 ) {
			return;
		}

		//phpcs:disable WordPress.Security.NonceVerification.Missing
		$start = ! empty( $_POST['start_timestamp'] ) ? absint( $_POST['start_timestamp'] ) : 0;
		$end   = ! empty( $_POST['end_timestamp'] ) ? absint( $_POST['end_timestamp'] ) : 0;
		//phpcs:enable WordPress.Security.NonceVerification.Missing

		// Filter out empty fields.
		$fields = array_filter(
			$this->fields,
			static function ( $field ) {

				return ! empty( $field['value'] );
			}
		);

		// Skip the time limit check if the form was submitted with prefilled values.
		if ( $start === 0 && ! empty( $fields ) ) {
			return;
		}

		// If the form was submitted too quickly, add an error.
		if ( ( $end - $start ) < $duration || $start === 0 ) {
			$this->errors[ $this->form_data['id'] ]['header'] = esc_html__( 'Please wait a little longer before submitting. We’re running a quick security check.', 'wpforms-lite' );
		}
	}

	/**
	 * Process complete.
	 *
	 * @since 1.8.3
	 *
	 * @param int   $form_id   Form ID.
	 * @param array $form_data Form data and settings.
	 * @param array $fields    Fields data.
	 * @param array $entry     Form submission raw data ($_POST).
	 * @param int   $entry_id  Entry ID.
	 */
	public function process_complete( $form_id, $form_data, $fields, $entry, $entry_id ) {
		/**
		 * Runs right after the form has been successfully submitted.
		 *
		 * @since 1.0.0
		 * @since 1.8.3 Added $entry parameter.
		 *
		 * @param array  $fields    Fields data.
		 * @param array  $entry     Form submission raw data ($_POST).
		 * @param array  $form_data Form data.
		 * @param int    $entry_id  Entry ID.
		 */
		do_action( 'wpforms_process_complete', $fields, $entry, $form_data, $entry_id );

		/**
		 * Runs right after the form has been successfully submitted by form ID.
		 *
		 * @since 1.0.0
		 * @since 1.8.3 Added $entry parameter.
		 *
		 * @param array  $fields    Fields data.
		 * @param array  $entry     Form submission raw data ($_POST).
		 * @param array  $form_data Form data.
		 * @param int    $entry_id  Entry ID.
		 */
		do_action( "wpforms_process_complete_{$form_id}", $fields, $entry, $form_data, $entry_id );
	}

	/**
	 * Check for spam.
	 *
	 * @since 1.8.3
	 *
	 * @param array $entry Form submission raw data ($_POST).
	 */
	public function process_spam_check( $entry ) {

		// CAPTCHA check.
		$this->process_captcha( $entry );

		if ( $this->spam_reason ) {
			return;
		}

		$akismet = wpforms()->obj( 'akismet' )->validate( $this->form_data, $entry );

		// If Akismet marks the entry as spam, we want to log the entry and fail silently.
		if ( $akismet ) {

			$this->spam_errors[ $this->form_data['id'] ]['header'] = $akismet;

			// Log the spam entry depending on log levels set.
			$this->log_spam_entry( $entry, $akismet );

			$this->spam_reason = esc_html__( 'Akismet', 'wpforms-lite' );
		}
	}

	/**
	 * Is bypass spam check.
	 *
	 * @since 1.8.3
	 *
	 * @param array $entry Form submission raw data ($_POST).
	 *
	 * @return bool
	 */
	protected function is_bypass_spam_check( $entry ) {

		/**
		 * Filter to bypass CAPTCHA check.
		 *
		 * @since 1.6.6
		 *
		 * @param bool  $bypass_captcha Whether to bypass CAPTCHA check.
		 * @param array $entry          Form submission raw data ($_POST).
		 * @param array $form_data      Form data.
		 */
		return apply_filters( 'wpforms_process_bypass_captcha', false, $entry, $this->form_data );
	}

	/**
	 * Process captcha.
	 *
	 * @since 1.8.0
	 * @since 1.8.3 Removed $captcha_settings parameter.
	 *
	 * @param array $entry Form submission raw data ($_POST).
	 *
	 * @return void
	 */
	private function process_captcha( $entry ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		// Skip if spam was already detected.
		if ( $this->spam_reason ) {
			return;
		}

		$captcha_settings = wpforms_get_captcha_settings();

		if ( ! $this->allow_process_captcha( $entry, $captcha_settings ) ) {
			return;
		}

		$provider = $captcha_settings['provider'];

		$current_captcha = $this->get_captcha( $provider );

		if ( empty( $current_captcha ) ) {
			return;
		}

		$verify_url_raw   = $current_captcha['verify_url_raw'];
		$captcha_provider = $current_captcha['provider'];
		$post_key         = $current_captcha['post_key'];

		/* translators: %s - The CAPTCHA provider name. */
		$error = wpforms_setting( "{$provider}-fail-msg", sprintf( esc_html__( '%s verification failed, please try again later.', 'wpforms-lite' ), $captcha_provider ) );

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.NonceVerification.Missing
		$token = ! empty( $_POST[ $post_key ] ) ? $_POST[ $post_key ] : false;

		$is_recaptcha_v3 = $provider === 'recaptcha' && $captcha_settings['recaptcha_type'] === 'v3';

		if ( $is_recaptcha_v3 ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.NonceVerification.Missing
			$token = ! empty( $_POST['wpforms']['recaptcha'] ) ? $_POST['wpforms']['recaptcha'] : false;
		}

		$verify_query_arg = [
			'secret'   => $captcha_settings['secret_key'],
			'response' => $token,
			'remoteip' => wpforms_get_ip(),
		];

		if ( ! $token ) {
			$this->errors[ $this->form_data['id'] ]['recaptcha'] = $error;

			return;
		}

		/*
		 * hCaptcha uses user IP to better detect bots and their attacks on a form.
		 * Majority of our users have GDPR disabled.
		 * So we remove this data from the request only when it's unnecessary,
		 * depending on wpforms_is_collecting_ip_allowed($this->form_data) check.
		 */
		if ( ! wpforms_is_collecting_ip_allowed( $this->form_data ) ) {
			unset( $verify_query_arg['remoteip'] );
		}

		/**
		 * Change query arguments for remote call to the captcha API.
		 *
		 * @since 1.8.0
		 *
		 * @param array $verify_query_arg The query arguments for verify URL.
		 * @param array $form_data        Form data and settings.
		 */
		$verify_query_arg = apply_filters( 'wpforms_process_captcha_verify_query_arg', $verify_query_arg, $this->form_data );

		/**
		 * Filter the CAPTCHA verify URL.
		 *
		 * @since 1.6.4
		 * @since 1.8.0 Added $form_data argument.
		 *
		 * @param string $verify_url       The full `CAPTCHA verify URL`.
		 * @param string $verify_url_raw   The `CAPTCHA verify URL` without query.
		 * @param array  $verify_query_arg The query arguments for verify URL.
		 * @param array  $form_data        Form data and settings.
		 */
		$verify_url = apply_filters( 'wpforms_process_captcha_verify_url', $verify_url_raw, $verify_url_raw, $verify_query_arg, $this->form_data );

		$response = wp_safe_remote_post( $verify_url, [ 'body' => $verify_query_arg ] );

		$response_body = json_decode( wp_remote_retrieve_body( $response ), false );

		if (
			empty( $response_body->success ) ||
			( $is_recaptcha_v3 && $response_body->score <= wpforms_setting( 'recaptcha-v3-threshold', '0.4' ) )
		) {
			if ( $is_recaptcha_v3 && isset( $response_body->score ) ) {
				$error .= ' (' . esc_html( $response_body->score ) . ')';
			}

			$this->spam_errors[ $this->form_data['id'] ]['recaptcha'] = $error;

			$this->log_spam_entry( $entry, $error );

			$this->spam_reason = $captcha_provider;
		}
	}

	/**
	 * Check if CAPTCHA processing is allowed.
	 *
	 * @since 1.8.3
	 *
	 * @param array $entry            Form entry data.
	 * @param array $captcha_settings CAPTCHA settings.
	 *
	 * @return bool
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	private function allow_process_captcha( $entry, $captcha_settings ) {

		// Skip captcha processing if an AMP form.
		if ( isset( $_POST['__amp_form_verify'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return false;
		}

		// Skip captcha processing if the provider is not set.
		if ( empty( $captcha_settings['provider'] ) ) {
			return false;
		}

		$provider = $captcha_settings['provider'];

		// Skip captcha processing if the provider is set to none.
		if ( $provider === 'none' ) {
			return false;
		}

		// Skip captcha processing if a site key or secret key is empty.
		if ( empty( $captcha_settings['site_key'] ) || empty( $captcha_settings['secret_key'] ) ) {
			return false;
		}

		$form_data_settings = $this->form_data['settings'] ?? [];
		$is_recaptcha       = isset( $form_data_settings['recaptcha'] ) && (int) $form_data_settings['recaptcha'] === 1;

		// Skip captcha processing if reCAPTCHA is disabled for this form.
		if ( ! $is_recaptcha ) {
			return false;
		}

		$recaptcha_type  = $captcha_settings['recaptcha_type'];
		$is_recaptcha_v3 = $provider === 'recaptcha' && $recaptcha_type === 'v3';

		// Skip captcha processing on AMP if not using reCAPTCHA v3. AMP requires Google reCAPTCHA v3.
		return $is_recaptcha_v3 || ! wpforms_is_amp();
	}

	/**
	 * Get all available CAPTCHA providers.
	 *
	 * @since 1.8.3
	 *
	 * @return array
	 */
	private function get_captcha_providers() {

		/**
		 * Filter the CAPTCHA providers.
		 *
		 * @since 1.8.3
		 *
		 * @param array $providers The CAPTCHA providers.
		 */
		return apply_filters(
			'wpforms_process_captcha_providers',
			[
				'hcaptcha'  => [
					'verify_url_raw' => 'https://hcaptcha.com/siteverify',
					'provider'       => 'hCaptcha',
					'post_key'       => 'h-captcha-response',
				],
				'recaptcha' => [
					'verify_url_raw' => 'https://www.google.com/recaptcha/api/siteverify',
					'provider'       => 'Google reCAPTCHA',
					'post_key'       => 'g-recaptcha-response',
				],
				'turnstile' => [
					'verify_url_raw' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
					'provider'       => 'Cloudflare Turnstile',
					'post_key'       => 'cf-turnstile-response', // The key is specified by the API.
				],
			]
		);
	}

	/**
	 * Get CAPTCHA provider data.
	 *
	 * @since 1.8.3
	 *
	 * @param string $provider CAPTCHA provider.
	 *
	 * @return array
	 */
	private function get_captcha( $provider ) {

		$captcha_providers = $this->get_captcha_providers();

		return $captcha_providers[ $provider ] ?? [];
	}

	/**
	 * Check if combined upload size exceeds allowed maximum.
	 *
	 * @since 1.6.0
	 *
	 * @param WP_Post $form Form post object.
	 */
	public function validate_combined_upload_size( $form ): void {

		$form_id       = (int) $form->ID;
		$upload_fields = wpforms_get_form_fields( $form, [ 'file-upload' ] );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! empty( $upload_fields ) && ! empty( $_FILES ) ) {

			// Get $_FILES keys generated by WPForms only.
			$files_keys = preg_filter( '/^/', 'wpforms_' . $form_id . '_', array_keys( $upload_fields ) );

			// Filter uploads without errors. Individual errors are handled by WPForms\Pro\Forms\Fields\FileUpload\Field class.
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$files          = wp_list_filter( wp_array_slice_assoc( $_FILES, $files_keys ), [ 'error' => 0 ] );
			$files_size     = array_sum( wp_list_pluck( $files, 'size' ) );
			$files_size_max = wpforms_max_upload( true );

			if ( $files_size > $files_size_max ) {

				// Add a new header error preserving previous ones.
				$this->errors[ $form_id ]['header']  = ! empty( $this->errors[ $form_id ]['header'] ) ? $this->errors[ $form_id ]['header'] . '<br>' : '';
				$this->errors[ $form_id ]['header'] .= esc_html__( 'Uploaded files combined size exceeds allowed maximum.', 'wpforms-lite' );
			}
		}
	}

	/**
	 * Validate the form return hash.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hash Base64-encoded hash of form and entry IDs.
	 *
	 * @return array|false False for invalid or form id.
	 */
	public function validate_return_hash( $hash = '' ) {

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$query_args = base64_decode( $hash );

		parse_str( $query_args, $output );

		// Verify hash matches.
		if ( wp_hash( $output['form_id'] . ',' . $output['entry_id'] ) !== $output['hash'] ) {
			return false;
		}

		// Get lead and verify it is attached to the form we received with it.
		$entry = wpforms()->obj( 'entry' )->get( $output['entry_id'], [ 'cap' => false ] );

		if ( empty( $entry->form_id ) ) {
			return false;
		}

		if ( $output['form_id'] !== $entry->form_id ) {
			return false;
		}

		return [
			'form_id'  => absint( $output['form_id'] ),
			'entry_id' => absint( $output['form_id'] ),
			'fields'   => $entry !== null && isset( $entry->fields ) ? $entry->fields : [],
		];
	}

	/**
	 * Check if the confirmation data are valid.
	 *
	 * @since 1.6.4
	 *
	 * @param array $data The confirmation data.
	 *
	 * @return bool
	 */
	protected function is_valid_confirmation( $data ) {

		if ( empty( $data['type'] ) ) {
			return false;
		}

		// Confirmation type: redirect, page or message.
		$type = $data['type'];

		return isset( $data[ $type ] ) && ! wpforms_is_empty_string( $data[ $type ] );
	}

	/**
	 * Redirect the user to a page or URL specified in the form confirmation settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $form_data Form data and settings.
	 * @param string $hash      Base64-encoded hash of form and entry IDs.
	 */
	public function entry_confirmation_redirect( $form_data = [], $hash = '' ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh, WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		// Maybe process return hash.
		if ( ! empty( $hash ) ) {

			$hash_data = $this->validate_return_hash( $hash );

			if ( ! $hash_data || ! is_array( $hash_data ) ) {
				return;
			}

			$this->valid_hash = true;
			$this->entry_id   = absint( $hash_data['entry_id'] );
			$this->fields     = json_decode( $hash_data['fields'], true );
			$this->form_data  = wpforms()->obj( 'form' )->get(
				absint( $hash_data['form_id'] ),
				[
					'content_only' => true,
				]
			);

		} else {

			$this->form_data = $form_data;
		}

		// Backward compatibility.
		if ( empty( $this->form_data['settings']['confirmations'] ) ) {
			$this->form_data['settings']['confirmations'][1]['type']           = ! empty( $this->form_data['settings']['confirmation_type'] ) ? $this->form_data['settings']['confirmation_type'] : 'message';
			$this->form_data['settings']['confirmations'][1]['message']        = ! empty( $this->form_data['settings']['confirmation_message'] ) ? $this->form_data['settings']['confirmation_message'] : esc_html__( 'Thanks for contacting us! We will be in touch with you shortly.', 'wpforms-lite' );
			$this->form_data['settings']['confirmations'][1]['message_scroll'] = ! empty( $this->form_data['settings']['confirmation_message_scroll'] ) ? $this->form_data['settings']['confirmation_message_scroll'] : 1;
			$this->form_data['settings']['confirmations'][1]['page']           = ! empty( $this->form_data['settings']['confirmation_page'] ) ? $this->form_data['settings']['confirmation_page'] : '';
			$this->form_data['settings']['confirmations'][1]['redirect']       = ! empty( $this->form_data['settings']['confirmation_redirect'] ) ? $this->form_data['settings']['confirmation_redirect'] : '';
		}

		if ( empty( $this->form_data['settings']['confirmations'] ) || ! is_array( $this->form_data['settings']['confirmations'] ) ) {
			return;
		}

		$confirmations = $this->form_data['settings']['confirmations'];

		/**
		 * Filter confirmations before processing.
		 *
		 * Allows addons to replace confirmations with their own data.
		 *
		 * @since 1.9.8.6
		 *
		 * @param array $confirmations Confirmations data.
		 * @param array $form_data     Form data and settings.
		 * @param array $fields        Submitted form fields.
		 * @param int   $entry_id      Entry ID.
		 */
		$confirmations = (array) apply_filters( 'wpforms_process_entry_confirmation_redirect_confirmations', $confirmations, $this->form_data, $this->fields, $this->entry_id );

		// Reverse sort confirmations by id to process newer ones first.
		krsort( $confirmations );

		$confirmation_id = $this->get_confirmation_id( $confirmations );

		$this->confirmation = $confirmations[ $confirmation_id ] ?? [];

		$url = '';
		// Redirect, if needed, to either a page or URL, after form processing.
		if ( ! empty( $confirmations[ $confirmation_id ]['type'] ) && $confirmations[ $confirmation_id ]['type'] !== 'message' ) {

			if ( $confirmations[ $confirmation_id ]['type'] === 'redirect' ) {

				$rawurlencode_callback = static function ( $value ) {
					return $value === null ? null : rawurlencode( $value );
				};

				add_filter( 'wpforms_smarttags_process_field_id_value', $rawurlencode_callback );

				$url = wpforms_process_smart_tags(
					$confirmations[ $confirmation_id ]['redirect'],
					$this->form_data,
					$this->fields,
					$this->entry_id,
					'confirmation_redirect'
				);

				remove_filter( 'wpforms_smarttags_process_field_id_value', $rawurlencode_callback );
			}

			if ( $confirmations[ $confirmation_id ]['type'] === 'page' ) {
				$url = $this->get_confirmation_redirect_page( (array) $confirmations[ $confirmation_id ] );
			}
		}

		if ( ! empty( $url ) ) {
			// phpcs:ignore WPForms.Comments.PHPDocHooks.RequiredHookDocumentation
			$url = apply_filters( 'wpforms_process_redirect_url', $url, $this->form_data['id'], $this->fields, $this->form_data, $this->entry_id );

			if ( wpforms_is_amp() ) {
				/** This filter is documented in wp-includes/pluggable.php */
				// phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName, WPForms.Comments.PHPDocHooks.RequiredHookDocumentation
				$url = apply_filters( 'wp_redirect', $url, 302 );
				$url = wp_sanitize_redirect( $url );

				header( sprintf( 'AMP-Redirect-To: %s', $url ) );
				header( 'Access-Control-Expose-Headers: AMP-Redirect-To', false );
				wp_send_json(
					[
						'message'     => __( 'Redirecting…', 'wpforms-lite' ),
						'redirecting' => true,
					],
					200
				);
			} else {
				wp_redirect( esc_url_raw( $url ) ); // phpcs:ignore
			}

			// phpcs:ignore WPForms.Comments.PHPDocHooks.RequiredHookDocumentation
			do_action( 'wpforms_process_redirect', $this->form_data['id'] );

			// phpcs:ignore WPForms.Comments.PHPDocHooks.RequiredHookDocumentation
			do_action( "wpforms_process_redirect_{$this->form_data['id']}", $this->form_data['id'] );

			exit;
		}

		// Pass a message to a frontend if no redirection happened.
		if ( ! empty( $confirmations[ $confirmation_id ]['type'] ) && $confirmations[ $confirmation_id ]['type'] === 'message' ) {
			$this->confirmation_message = $confirmations[ $confirmation_id ]['message'];

			if ( ! empty( $confirmations[ $confirmation_id ]['message_scroll'] ) ) {
				wpforms()->obj( 'frontend' )->confirmation_message_scroll = true;
			}
		}
	}

	/**
	 * Get redirect URL for page type confirmation.
	 *
	 * @since 1.9.8
	 *
	 * @param array $confirmation Confirmation.
	 *
	 * @return string
	 */
	private function get_confirmation_redirect_page( array $confirmation ): string {

		if ( empty( $confirmation['page'] ) ) {
			return '';
		}

		if ( $confirmation['page'] !== 'previous_page' ) {
			$url = (string) get_permalink( (int) $confirmation['page'] );

			return $this->add_url_params_page_confirmation( $confirmation, $url );
		}

		$url = wpforms_process_smart_tags(
			'{url_referer}',
			$this->form_data,
			$this->fields,
			$this->entry_id,
			'confirmation_redirect'
		);

		/**
		 * Filter the previous page URL for the redirect confirmation.
		 *
		 * @since 1.9.8
		 *
		 * @param string $url          Previous page URL.
		 * @param array  $confirmation Confirmation data.
		 * @param array  $form_data    Form data and settings.
		 */
		$url = (string) apply_filters( 'wpforms_process_confirmation_previous_page_url', $url, $confirmation, $this->form_data );

		// Double-check if the referer exists and it's not an external website.
		if ( $url && wp_validate_redirect( $url ) ) {
			return $this->add_url_params_page_confirmation( $confirmation, $url );
		}

		/**
		 * Filter the fallback URL when the previous page URL is invalid.
		 *
		 * @since 1.9.8
		 *
		 * @param string $fallback_url Default homepage URL.
		 * @param array  $confirmation Confirmation data.
		 * @param array  $form_data    Form data and settings.
		 */
		$fallback_url = (string) apply_filters( 'wpforms_process_confirmation_fallback_url', home_url(), $confirmation, $this->form_data );

		return $this->add_url_params_page_confirmation( $confirmation, $fallback_url );
	}

	/**
	 * Determine which confirmation to process.
	 *
	 * @since 1.9.2
	 *
	 * @param array $confirmations List of confirmations.
	 *
	 * @return int Confirmation ID.
	 */
	private function get_confirmation_id( array $confirmations ) {

		$default_confirmation_key = min( array_keys( $confirmations ) );

		$confirmation_id = 0;

		foreach ( $confirmations as $confirmation_id => $confirmation ) {
			// Last confirmation should execute in any case.
			if ( $default_confirmation_key === $confirmation_id ) {
				break;
			}

			if ( ! $this->is_valid_confirmation( $confirmation ) ) {
				continue;
			}

			// phpcs:disable WPForms.PHP.ValidateHooks.InvalidHookName

			/**
			 * Process confirmation filter.
			 *
			 * @since 1.4.8
			 *
			 * @param bool  $process   Whether to process the logic or not.
			 * @param array $fields    List of submitted fields.
			 * @param array $form_data Form data and settings.
			 * @param int   $id        Confirmation ID.
			 */
			$process_confirmation = apply_filters( 'wpforms_entry_confirmation_process', true, $this->fields, $this->form_data, $confirmation_id );
			// phpcs:enable WPForms.PHP.ValidateHooks.InvalidHookName

			if ( $process_confirmation ) {
				break;
			}
		}

		return $confirmation_id;
	}

	/**
	 * Add URL parameters to the page confirmation URL.
	 *
	 * @since 1.9.8
	 *
	 * @param array  $confirmation Confirmation data.
	 * @param string $url          Page URL.
	 *
	 * @return string Modified URL with parameters.
	 */
	private function add_url_params_page_confirmation( array $confirmation, string $url ): string {

		if ( empty( $confirmation['page_url_parameters'] ) ) {
			return $url;
		}

		parse_str( $confirmation['page_url_parameters'], $url_params );

		if ( empty( $url_params ) ) {
			return $url;
		}

		/**
		 * Filter the URL parameters before adding them to the URL for page confirmation.
		 *
		 * @since 1.9.8
		 *
		 * @param array $url_params   Array of URL parameters.
		 * @param array $confirmation Confirmation data.
		 * @param array $form_data    Form data and settings.
		 */
		$url_params = apply_filters( 'wpforms_process_confirmation_url_parameters', $url_params, $confirmation, $this->form_data );

		return add_query_arg( $url_params, $url );
	}

	/**
	 * Get a confirmation message.
	 *
	 * @since 1.5.3
	 *
	 * @param array $form_data Form data and settings.
	 * @param array $fields    Sanitized field data.
	 * @param int   $entry_id  Entry id.
	 *
	 * @return string Confirmation message.
	 */
	public function get_confirmation_message( $form_data, $fields, $entry_id ) {

		if ( empty( $this->confirmation_message ) ) {
			return '';
		}

		$confirmation_message = wpforms_process_smart_tags( $this->confirmation_message, $form_data, $fields, $entry_id, 'confirmation' );

		// phpcs:ignore WPForms.Comments.PHPDocHooks.RequiredHookDocumentation, WPForms.PHP.ValidateHooks.InvalidHookName
		return apply_filters( 'wpforms_frontend_confirmation_message', wpautop( $confirmation_message ), $form_data, $fields, $entry_id );
	}

	/**
	 * Get current confirmation.
	 *
	 * @since 1.6.9
	 *
	 * @return array
	 */
	public function get_current_confirmation() {

		return ! empty( $this->confirmation ) ? $this->confirmation : [];
	}

	/**
	 * Catch the post_max_size overflow.
	 *
	 * @since 1.5.2
	 *
	 * @return bool
	 */
	public function post_max_size_overflow() {

		// phpcs:disable WordPress.Security.NonceVerification
		if ( empty( $_SERVER['CONTENT_LENGTH'] ) || empty( $_GET['wpforms_form_id'] ) ) {
			return false;
		}

		$form_id       = (int) $_GET['wpforms_form_id'];
		$total_size    = (int) $_SERVER['CONTENT_LENGTH'];
		$post_max_size = wpforms_size_to_bytes( ini_get( 'post_max_size' ) );

		if ( ! ( $total_size > $post_max_size && empty( $_POST ) && $form_id > 0 ) ) {
			return false;
		}
		// phpcs:enable WordPress.Security.NonceVerification

		$error_msg  = esc_html__( 'Form has not been submitted, please see the errors below.', 'wpforms-lite' );
		$error_msg .= '<br>' . sprintf( /* translators: %1$.3f - total size of the selected files in megabytes, %2$.3f - allowed file upload limit in megabytes.*/
			esc_html__( 'The total size of the selected files %1$.3f MB exceeds the allowed limit %2$.3f MB.', 'wpforms-lite' ),
			esc_html( $total_size / 1048576 ),
			esc_html( $post_max_size / 1048576 )
		);

		$this->errors[ $form_id ]['header'] = $error_msg;

		return true;
	}

	/**
	 * Send entry email notifications.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $fields    List of fields.
	 * @param array  $entry     Submitted form entry.
	 * @param array  $form_data Form data and settings.
	 * @param int    $entry_id  Saved entry id.
	 * @param string $context   In which context this email is sent.
	 */
	public function entry_email( $fields, $entry, $form_data, $entry_id, $context = '' ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		// Check that the form was configured for email notifications.
		if ( empty( $form_data['settings']['notification_enable'] ) ) {
			return;
		}

		/**
		 * Allow entry email notifications to be disabled.
		 *
		 * @since 1.0.0
		 *
		 * @param bool   $enabled   Whether to send the email.
		 * @param array  $fields    List of fields.
		 * @param array  $entry     Form submission raw data.
		 * @param array  $form_data Form data and settings.
		 */
		if ( ! apply_filters( 'wpforms_entry_email', true, $fields, $entry, $form_data ) ) { // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
			return;
		}

		// Make sure we have an entry id.
		if ( empty( $this->entry_id ) ) {
			$this->entry_id = (int) $entry_id;
		}

		/**
		 * Filter entry email notifications data.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $fields    List of fields.
		 * @param array  $entry     Form submission raw data.
		 * @param array  $form_data Form data and settings.
		 */
		$fields = apply_filters( 'wpforms_entry_email_data', $fields, $entry, $form_data ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		// Backwards compatibility for notifications before v1.4.3.
		if ( empty( $form_data['settings']['notifications'] ) && ! empty( $form_data['settings']['notification_email'] ) ) {
			$notifications[1] = [
				'email'          => $form_data['settings']['notification_email'],
				'subject'        => $form_data['settings']['notification_subject'],
				'sender_name'    => $form_data['settings']['notification_fromname'],
				'sender_address' => $form_data['settings']['notification_fromaddress'],
				'replyto'        => $form_data['settings']['notification_replyto'],
				'message'        => '{all_fields}',
			];
		} else {
			$notifications = $form_data['settings']['notifications'];
		}

		$notifications_count = count( $notifications );
		$is_pro              = wpforms()->is_pro();

		foreach ( $notifications as $notification_id => $notification ) :

			if ( empty( $notification['email'] ) ) {
				continue;
			}

			// You can disable the email notification for a specific notification only if there are more than one notification.
			// BC: The notification should be enabled even when the `enabled` key doesn't exist.
			// The key is missed for old forms or forms created using the Lite version.
			if ( $is_pro && $notifications_count > 1 && isset( $notification['enable'] ) && (int) $notification['enable'] === 0 ) {
				continue;
			}

			/**
			 * Allow entry email notifications to be disabled for a specific notification.
			 *
			 * @since 1.0.0
			 *
			 * @param bool   $enabled         Whether to send the email.
			 * @param array  $fields          List of fields.
			 * @param array  $form_data       Form data and settings.
			 * @param int    $notification_id Notification ID.
			 * @param string $context         In which context this email is sent.
			 */
			$process_email = apply_filters( 'wpforms_entry_email_process', true, $fields, $form_data, $notification_id, $context ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

			if ( ! $process_email ) {
				continue;
			}

			$email                 = [];
			$is_carboncopy_enabled = wpforms_setting( 'email-carbon-copy' );

			// Setup email properties.
			$email['subject']        = ! empty( $notification['subject'] ) ?
				$notification['subject'] :
				sprintf( /* translators: %s - form name. */
					esc_html__( 'New %s Entry', 'wpforms-lite' ),
					$form_data['settings']['form_title']
				);
			$email['address']        = explode( ',', wpforms_process_smart_tags( $notification['email'], $form_data, $fields, $this->entry_id, 'notification-send-to-email' ) );
			$email['address']        = array_filter( array_map( 'sanitize_email', $email['address'] ) );
			$email['sender_address'] = ! empty( $notification['sender_address'] ) ? $notification['sender_address'] : get_option( 'admin_email' );
			$email['sender_name']    = ! empty( $notification['sender_name'] ) ? $notification['sender_name'] : get_bloginfo( 'name' );
			$email['replyto']        = ! empty( $notification['replyto'] ) ? $notification['replyto'] : false;
			$email['message']        = ! empty( $notification['message'] ) ? $notification['message'] : '{all_fields}';
			$email['template']       = ! empty( $notification['template'] ) ? $notification['template'] : '';

			if ( $is_carboncopy_enabled && ! empty( $notification['carboncopy'] ) ) {
				$email['carboncopy'] = explode(
					',',
					wpforms_process_smart_tags(
						$notification['carboncopy'],
						$form_data,
						$fields,
						$this->entry_id,
						'notification-carboncopy',
						[ 'to_email' => $email['address'] ]
					)
				);
				$email['carboncopy'] = array_filter( array_map( 'sanitize_email', $email['carboncopy'] ) );
			}

			/**
			 * Filter entry email notifications attributes.
			 *
			 * @since 1.0.0
			 *
			 * @param array  $email           Email attributes.
			 * @param array  $fields          List of fields.
			 * @param array  $entry           Form submission raw data.
			 * @param array  $form_data       Form data and settings.
			 * @param int    $notification_id Notification ID.
			 */
			$email = apply_filters( 'wpforms_entry_email_atts', $email, $fields, $entry, $form_data, $notification_id ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

			// Create a new email.
			$emails = ( new Notifications() )->init( $email['template'] );

			$emails->__set( 'form_data', $form_data );
			$emails->__set( 'fields', $fields );
			$emails->__set( 'notification_id', $notification_id );
			$emails->__set( 'entry_id', $this->entry_id );
			$emails->__set( 'from_name', $email['sender_name'] );
			$emails->__set( 'from_address', $email['sender_address'] );
			$emails->__set( 'reply_to', $email['replyto'] );

			// Reset headers to support multiple notifications. They will be set on sending.
			$emails->__set( 'headers', null );

			// Maybe include CC.
			if ( $is_carboncopy_enabled && ! empty( $email['carboncopy'] ) ) {
				$emails->__set( 'cc', $email['carboncopy'] );
			}

			/**
			 * Filter entry email notifications before sending.
			 *
			 * @since 1.0.0
			 *
			 * @param object $emails WPForms_WP_Emails instance.
			 */
			$emails = apply_filters( 'wpforms_entry_email_before_send', $emails ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

			$this->email_handler = $emails;

			// Go.
			foreach ( $email['address'] as $address ) {
				$emails->send( trim( $address ), $email['subject'], $email['message'] );
			}
		endforeach;
	}

	/**
	 * Save entry to a database.
	 *
	 * @since 1.0.0
	 *
	 * @param array $fields    List of form fields.
	 * @param array $entry     User submitted data.
	 * @param int   $form_id   Form ID.
	 * @param array $form_data Prepared form settings.
	 *
	 * @return int
	 */
	public function entry_save( $fields, $entry, $form_id, $form_data = [] ) {

		$fields = $this->remove_raw_data_before_save( $fields );

		/**
		 * Fires on entry save.
		 *
		 * @since 1.0.0
		 *
		 * @param array $fields    List of form fields.
		 * @param array $entry     Form submission raw data.
		 * @param int   $form_id   Form ID.
		 * @param array $form_data Prepared form settings.
		 */
		do_action( 'wpforms_process_entry_save', $fields, $entry, $form_id, $form_data );

		return $this->entry_id;
	}

	/**
	 * Remove raw data from fields before saving.
	 * This is needed to prevent raw password data from being saved to the database.
	 *
	 * @since 1.8.6
	 *
	 * @param array $fields List of form fields.
	 *
	 * @return array
	 */
	private function remove_raw_data_before_save( array $fields ): array {

		foreach ( $fields as $key => $field ) {
			if ( ! empty( $field['type'] ) && $field['type'] === 'password' ) {
				unset( $fields[ $key ]['value_raw'] );
			}
		}

		return $fields;
	}

	/**
	 * Save payment to the database.
	 *
	 * @since 1.8.2
	 *
	 * @param array $entry User submitted data.
	 *
	 * @return int Payment ID.
	 */
	private function payment_save( $entry ) {

		if ( ! wpforms_has_payment( 'entry', $this->fields ) ) {
			return 0;
		}

		$entry['entry_id'] = $this->entry_id;

		$form_submission = wpforms()->obj( 'submission' )->register( $this->fields, $entry, $this->form_data['id'], $this->form_data );

		// Prepare the payment data.
		$payment_data = $form_submission->prepare_payment_data();

		// Bail early in case the payment field exists,
		// but no payment data was provided (e.g., old payment addon is used).
		if ( empty( $payment_data['gateway'] ) ) {
			return 0;
		}

		// Create payment.
		$payment_id = wpforms()->obj( 'payment' )->add( $payment_data );

		if ( ! $payment_id ) {
			return 0;
		}

		// Insert payment meta.
		wpforms()->obj( 'payment_meta' )->bulk_add( $payment_id, $form_submission->prepare_payment_meta() );

		/**
		 * Fire after payment was saved to a database.
		 *
		 * @since 1.8.2
		 *
		 * @param int    $payment_id Payment id.
		 * @param array  $fields     Form fields.
		 * @param array  $form_data  Form data.
		 */
		do_action( 'wpforms_process_payment_saved', $payment_id, $this->fields, $this->form_data );

		return $payment_id;
	}

	/**
	 * Process AJAX form submit.
	 *
	 * @since 1.5.3
	 */
	public function ajax_submit() { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$form_id = isset( $_POST['wpforms']['id'] ) ? absint( $_POST['wpforms']['id'] ) : 0;

		if ( empty( $form_id ) ) {
			wp_send_json_error();
		}

		if ( isset( $_POST['wpforms']['post_id'] ) ) {
			// We don't have a global $post when processing ajax requests.
			// Therefore, it's necessary to set a global $post manually for compatibility with functions used in smart tag processing.
			global $post;
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$post = WP_Post::get_instance( absint( $_POST['wpforms']['post_id'] ) );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		add_filter( 'wp_redirect', [ $this, 'ajax_process_redirect' ], 999 );

		// phpcs:ignore WPForms.Comments.PHPDocHooks.RequiredHookDocumentation, WPForms.PHP.ValidateHooks.InvalidHookName
		do_action( 'wpforms_ajax_submit_before_processing', $form_id );

		// If redirect happens in listen(), ajax_process_redirect() gets executed because of the filter on `wp_redirect`.
		// The code, that is below listen(), runs only if no redirect happened.
		$this->listen();

		$form_data = $this->form_data;

		if ( empty( $form_data ) ) {
			$form_data = wpforms()->obj( 'form' )->get( $form_id, [ 'content_only' => true ] );

			// phpcs:ignore WPForms.Comments.PHPDocHooks.RequiredHookDocumentation, WPForms.PHP.ValidateHooks.InvalidHookName
			$form_data = apply_filters( 'wpforms_frontend_form_data', $form_data );
		}

		if ( ! empty( $this->errors[ $form_id ] ) ) {
			$this->ajax_process_errors( $form_id, $form_data );
			wp_send_json_error();
		}

		ob_start();

		wpforms()->obj( 'frontend' )->confirmation( $form_data );

		// phpcs:ignore WPForms.Comments.PHPDocHooks.RequiredHookDocumentation, WPForms.PHP.ValidateHooks.InvalidHookName
		$response = apply_filters( 'wpforms_ajax_submit_success_response', [ 'confirmation' => ob_get_clean() ], $form_id, $form_data );

		// phpcs:ignore WPForms.Comments.PHPDocHooks.RequiredHookDocumentation, WPForms.PHP.ValidateHooks.InvalidHookName
		do_action( 'wpforms_ajax_submit_completed', $form_id, $response );

		wp_send_json_success( $response );
	}

	/**
	 * Process AJAX errors.
	 *
	 * @since 1.5.3
	 *
	 * @todo This should be re-used/combined for AMP verify-xhr requests.
	 *
	 * @param int   $form_id   Form ID.
	 * @param array $form_data Form data and settings.
	 */
	protected function ajax_process_errors( $form_id, $form_data ) {

		$errors = $this->errors[ $form_id ] ?? [];

		// phpcs:ignore WPForms.Comments.PHPDocHooks.RequiredHookDocumentation, WPForms.PHP.ValidateHooks.InvalidHookName
		$errors = apply_filters( 'wpforms_ajax_submit_errors', $errors, $form_id, $form_data );

		if ( empty( $errors ) ) {
			wp_send_json_error();
		}

		// General errors are errors that cannot be populated with the jQuery Validate plugin.
		$general_errors = array_intersect_key( $errors, array_flip( [ 'header', 'footer', 'header_styled', 'footer_styled', 'recaptcha' ] ) );

		foreach ( $general_errors as $key => $error ) {
			ob_start();
			wpforms()->obj( 'frontend' )->form_error( $key, $error, $form_data );
			$general_errors[ $key ] = ob_get_clean();
		}

		$fields = $form_data['fields'] ?? [];

		// Get registered fields errors only.
		$field_errors = array_intersect_key( $errors, $fields );

		// Transform field ids to field names for jQuery Validate plugin.
		foreach ( $field_errors as $key => $error ) {

			$name = $this->ajax_error_field_name( $fields[ $key ], $form_data, $error );

			if ( $name ) {
				$field_errors[ $name ] = $error;
			}

			unset( $field_errors[ $key ] );
		}

		$response = [];

		if ( $general_errors ) {
			$response['errors']['general'] = $general_errors;
		}

		if ( $field_errors ) {
			$response['errors']['field'] = $field_errors;
		}

		// phpcs:ignore WPForms.Comments.PHPDocHooks.RequiredHookDocumentation, WPForms.PHP.ValidateHooks.InvalidHookName
		$response = apply_filters( 'wpforms_ajax_submit_errors_response', $response, $form_id, $form_data );

		// phpcs:ignore WPForms.Comments.PHPDocHooks.RequiredHookDocumentation, WPForms.PHP.ValidateHooks.InvalidHookName
		do_action( 'wpforms_ajax_submit_completed', $form_id, $response );

		wp_send_json_error( $response );
	}

	/**
	 * Get a field name for an ajax error message.
	 *
	 * @since 1.6.3
	 *
	 * @param array           $field     Field settings.
	 * @param array           $form_data Form data and settings.
	 * @param string|string[] $error     Error message.
	 *
	 * @return string
	 */
	private function ajax_error_field_name( array $field, array $form_data, $error ): string {

		$props = wpforms()->obj( 'frontend' )->get_field_properties( $field, $form_data );

		/**
		 * Filter the field name for an ajax error message.
		 *
		 * @since 1.6.3
		 *
		 * @param string          $name  Error field name.
		 * @param array           $field Field.
		 * @param array           $props Field properties.
		 * @param string|string[] $error Error message.
		 */
		return (string) apply_filters( 'wpforms_process_ajax_error_field_name', '', $field, $props, $error );
	}

	/**
	 * Process AJAX redirect.
	 *
	 * @since 1.5.3
	 *
	 * @param string $url Redirect URL.
	 */
	public function ajax_process_redirect( $url ) {

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$form_id = isset( $_POST['wpforms']['id'] ) ? absint( $_POST['wpforms']['id'] ) : 0;

		if ( empty( $form_id ) ) {
			wp_send_json_error();
		}

		$response = [
			'form_id'      => $form_id,
			'redirect_url' => $url,
		];

		// phpcs:ignore WPForms.Comments.PHPDocHooks.RequiredHookDocumentation, WPForms.PHP.ValidateHooks.InvalidHookName
		$response = apply_filters( 'wpforms_ajax_submit_redirect', $response, $form_id, $url );

		// phpcs:ignore WPForms.Comments.PHPDocHooks.RequiredHookDocumentation, WPForms.PHP.ValidateHooks.InvalidHookName
		do_action( 'wpforms_ajax_submit_completed', $form_id, $response );

		wp_send_json_success( $response );
	}

	/**
	 * Conditionally add a new_tab key to the AJAX response.
	 *
	 * @since 1.9.2
	 *
	 * @param array $response AJAX response.
	 *
	 * @return array AJAX response.
	 */
	public function maybe_open_in_new_tab( array $response ): array {

		$open_in_new_tab = $this->confirmation['redirect_new_tab'] ?? false;

		if ( $open_in_new_tab ) {
			$response['new_tab'] = true;
		}

		return $response;
	}

	/**
	 * Validate action value for ajax form submission.
	 *
	 * @since 1.9.3
	 *
	 * @return bool
	 */
	private function is_valid_ajax_submit_action(): bool {

		// In the case of AMP form submission, the action is not set.
		if ( wpforms_is_amp( false ) ) {
			return true;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		return ! empty( $_POST['action'] ) && $_POST['action'] === 'wpforms_submit';
	}

	/**
	 * Get current email handler.
	 *
	 * @since 1.9.4
	 *
	 * @return mixed|Mailer|WPForms_WP_Emails|null
	 */
	public function get_email_handler() {

		return $this->email_handler;
	}

	/**
	 * Determines if blocking submissions by spam filtering is enabled.
	 *
	 * @since 1.9.9
	 *
	 * @return bool True if blocking submissions by spam filtering is enabled, false otherwise.
	 */
	private function is_block_submission_by_spam_filtering_enabled(): bool {

		/**
		 * Determines if blocking submissions by spam filtering should be forced.
		 *
		 * @since 1.9.9
		 *
		 * @param bool $enabled True if blocking submissions should be forced.
		 */
		return (bool) apply_filters( 'wpforms_process_is_block_submission_by_spam_filtering_enabled', false );
	}
}
