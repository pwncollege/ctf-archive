<?php

use WPForms\Helpers\Templates;
use WPForms\Tasks\Actions\EntryEmailsTask;

/**
 * Emails.
 *
 * This class handles all (notification) emails sent by WPForms.
 *
 * Heavily influenced by the great AffiliateWP plugin by Pippin Williamson.
 * https://github.com/AffiliateWP/AffiliateWP/blob/master/includes/emails/class-affwp-emails.php
 *
 * Note that this mailer class is no longer in active use and has been replaced with the "WPForms\Emails\Notifications" class.
 * Please refer to the new mailer wrapper extension to extend or add further customizations.
 *
 * @deprecated 1.8.5
 *
 * @since 1.1.3
 */
class WPForms_WP_Emails {

	/**
	 * Store the from address.
	 *
	 * @since 1.1.3
	 *
	 * @var string
	 */
	private $from_address;

	/**
	 * Store the from name.
	 *
	 * @since 1.1.3
	 *
	 * @var string
	 */
	private $from_name;

	/**
	 * Store the reply-to address.
	 *
	 * @since 1.1.3
	 *
	 * @var bool|string
	 */
	private $reply_to = false;

	/**
	 * Store the reply-to name.
	 *
	 * @since 1.7.9
	 *
	 * @var bool|string
	 */
	private $reply_to_name = false;

	/**
	 * Store the carbon copy addresses.
	 *
	 * @since 1.3.1
	 *
	 * @var string
	 */
	private $cc = false;

	/**
	 * Store the email content type.
	 *
	 * @since 1.1.3
	 *
	 * @var string
	 */
	private $content_type;

	/**
	 * Store the email headers.
	 *
	 * @since 1.1.3
	 *
	 * @var string
	 */
	private $headers;

	/**
	 * Whether to send email in HTML.
	 *
	 * @since 1.1.3
	 *
	 * @var bool
	 */
	private $html = true;

	/**
	 * The email template to use.
	 *
	 * @since 1.1.3
	 *
	 * @var string
	 */
	private $template;

	/**
	 * Form data and settings.
	 *
	 * @since 1.1.3
	 *
	 * @var array
	 */
	public $form_data = [];

	/**
	 * Fields, formatted, and sanitized.
	 *
	 * @since 1.1.3
	 *
	 * @var array
	 */
	public $fields = [];

	/**
	 * Entry ID.
	 *
	 * @since 1.2.3
	 *
	 * @var int
	 */
	public $entry_id = '';

	/**
	 * Notification ID that is currently being processed.
	 *
	 * @since 1.5.7
	 *
	 * @var int
	 */
	public $notification_id = '';

	/**
	 * Context data to be passed to the tag.
	 *
	 * @since 1.9.9.2
	 *
	 * @var array|array[]
	 */
	private $context_data = [];

	/**
	 * Get things going.
	 *
	 * @since 1.1.3
	 */
	public function __construct() {

		if ( 'none' === $this->get_template() ) {
			$this->html = false;
		}

		add_action( 'wpforms_email_send_before', [ $this, 'send_before' ] );
		add_action( 'wpforms_email_send_after', [ $this, 'send_after' ] );
	}

	/**
	 * Set a property.
	 *
	 * @since 1.1.3
	 *
	 * @param string $key   Object property key.
	 * @param mixed  $value Object property value.
	 */
	public function __set( $key, $value ) {

		$this->$key = $value;
	}

	/**
	 * Get the email from name.
	 *
	 * @since 1.1.3
	 *
	 * @return string The email from name
	 */
	public function get_from_name() {

		if ( ! empty( $this->from_name ) ) {
			$this->from_name = $this->process_tag( $this->from_name );
		} else {
			$this->from_name = get_bloginfo( 'name' );
		}

		return apply_filters( 'wpforms_email_from_name', wpforms_decode_string( $this->from_name ), $this );
	}

	/**
	 * Get the email from address.
	 *
	 * @since 1.1.3
	 *
	 * @return string The email from address.
	 */
	public function get_from_address() {

		if ( ! empty( $this->from_address ) ) {
			$this->from_address = $this->process_tag( $this->from_address );
		} else {
			$this->from_address = get_option( 'admin_email' );
		}

		return apply_filters( 'wpforms_email_from_address', wpforms_decode_string( $this->from_address ), $this );
	}

	/**
	 * Get the email reply-to.
	 *
	 * @since 1.1.3
	 *
	 * @return string The email reply-to address.
	 */
	public function get_reply_to() {

		if ( ! empty( $this->reply_to ) ) {

			$email = $this->reply_to;

			// Optional custom format with a Reply-to Name specified: John Doe <john@doe.com>
			// - starts with anything,
			// - followed by space,
			// - ends with <anything> (expected to be an email, validated later).
			$regex   = '/^(.+) (<.+>)$/';
			$matches = [];

			if ( preg_match( $regex, $this->reply_to, $matches ) ) {
				$this->reply_to_name = wpforms_decode_string( $this->process_tag( $matches[1] ) );
				$email               = trim( $matches[2], '<> ' );
			}

			$this->reply_to = $this->process_tag( $email );

			if ( ! is_email( $this->reply_to ) ) {
				$this->reply_to      = false;
				$this->reply_to_name = false;
			}
		}

		return apply_filters( 'wpforms_email_reply_to', wpforms_decode_string( $this->reply_to ), $this );
	}

	/**
	 * Get the email carbon copy addresses.
	 *
	 * @since 1.3.1
	 *
	 * @return string The email reply-to address.
	 */
	public function get_cc() {

		if ( is_array( $this->cc ) ) {
			$this->cc = implode( ',', $this->cc );
		}

		if ( ! empty( $this->cc ) ) {

			$this->cc = $this->process_tag( $this->cc );

			$addresses = array_map( 'trim', explode( ',', $this->cc ) );

			foreach ( $addresses as $key => $address ) {
				if ( ! is_email( $address ) ) {
					unset( $addresses[ $key ] );
				}
			}

			$this->cc = implode( ',', $addresses );
		}

		return apply_filters( 'wpforms_email_cc', wpforms_decode_string( $this->cc ), $this );
	}

	/**
	 * Get the email content type.
	 *
	 * @since 1.1.3
	 *
	 * @return string The email content type.
	 */
	public function get_content_type() {

		if ( ! $this->content_type && $this->html ) {
			$this->content_type = apply_filters( 'wpforms_email_default_content_type', 'text/html', $this );
		} elseif ( ! $this->html ) {
			$this->content_type = 'text/plain';
		}

		return apply_filters( 'wpforms_email_content_type', $this->content_type, $this );
	}

	/**
	 * Get the email headers.
	 *
	 * @since 1.1.3
	 *
	 * @return string The email headers.
	 */
	public function get_headers() {

		if ( ! $this->headers ) {
			$this->headers = "From: {$this->get_from_name()} <{$this->get_from_address()}>\r\n";

			if ( $this->get_reply_to() ) {
				$this->headers .= $this->reply_to_name ?
					"Reply-To: {$this->reply_to_name} <{$this->get_reply_to()}>\r\n" :
					"Reply-To: {$this->get_reply_to()}\r\n";
			}

			if ( $this->get_cc() ) {
				$this->headers .= "Cc: {$this->get_cc()}\r\n";
			}

			$this->headers .= "Content-Type: {$this->get_content_type()}; charset=utf-8\r\n";
		}

		return apply_filters( 'wpforms_email_headers', $this->headers, $this );
	}

	/**
	 * Build the email.
	 *
	 * @since 1.1.3
	 *
	 * @param string $message The email message.
	 *
	 * @return string
	 */
	public function build_email( $message ) {

		// Plain text email shortcut.
		if ( false === $this->html ) {
			$message = $this->process_tag( $message );
			$message = str_replace( '{all_fields}', $this->wpforms_html_field_value( false ), $message );

			return apply_filters( 'wpforms_email_message', wpforms_decode_string( $message ), $this );
		}

		/*
		 * Generate an HTML email.
		 */

		ob_start();

		$this->get_template_part( 'header', $this->get_template(), true );

		// Hooks into the email header.
		do_action( 'wpforms_email_header', $this );

		$this->get_template_part( 'body', $this->get_template(), true );

		// Hooks into the email body.
		do_action( 'wpforms_email_body', $this );

		$this->get_template_part( 'footer', $this->get_template(), true );

		// Hooks into the email footer.
		do_action( 'wpforms_email_footer', $this );

		$message = $this->process_tag( $message );
		$message = nl2br( $message );

		$body = ob_get_clean();

		$message = str_replace( '{email}', $message, $body );
		$message = str_replace( '{all_fields}', $this->wpforms_html_field_value( true ), $message );
		$message = make_clickable( $message );

		return apply_filters( 'wpforms_email_message', $message, $this );
	}

	/**
	 * Send the email.
	 *
	 * @since 1.1.3
	 *
	 * @param string $to          The To address.
	 * @param string $subject     The subject line of the email.
	 * @param string $message     The body of the email.
	 * @param array  $attachments Attachments to the email.
	 *
	 * @return bool
	 */
	public function send( $to, $subject, $message, $attachments = [] ) {

		if ( ! did_action( 'init' ) && ! did_action( 'admin_init' ) ) {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'You cannot send emails with WPForms_WP_Emails() until init/admin_init has been reached.', 'wpforms-lite' ), null );

			return false;
		}

		// Don't send anything if emails have been disabled.
		if ( $this->is_email_disabled() ) {
			return false;
		}

		// Don't send if email address is invalid.
		if ( ! is_email( $to ) ) {
			return false;
		}

		$this->context_data = [ 'to_email' => (array) $to ];

		// Hooks before email is sent.
		do_action( 'wpforms_email_send_before', $this );

		// Deprecated filter for $attachments.
		$attachments = apply_filters_deprecated(
			'wpforms_email_attachments',
			[ $attachments, $this ],
			'1.5.7 of the WPForms plugin',
			'wpforms_emails_send_email_data'
		);

		/*
		 * Allow to filter data on per-email basis,
		 * useful for localizations based on recipient email address, form settings,
		 * or for specific notifications - whatever available in WPForms_WP_Emails class.
		 */
		$data = apply_filters(
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

		// Update context data, as 'to' email address could be changed by the filter above.
		$this->context_data = [ 'to_email' => (array) $data['to'] ];

		$entry_obj = wpforms()->obj( 'entry' );

		// phpcs:ignore WPForms.Comments.PHPDocHooks.RequiredHookDocumentation, WPForms.PHP.ValidateHooks.InvalidHookName
		$send_same_process = apply_filters(
			'wpforms_tasks_entry_emails_trigger_send_same_process',
			false,
			$this->fields,
			$entry_obj ? $entry_obj->get( $this->entry_id ) : [],
			$this->form_data,
			$this->entry_id,
			'entry'
		);

		if (
			$send_same_process ||
			! empty( $this->form_data['settings']['disable_entries'] )
		) {
			// Let's do this NOW.
			$result = wp_mail(
				$data['to'],
				$this->get_prepared_subject( $data['subject'] ),
				$this->build_email( $data['message'] ),
				$data['headers'],
				$data['attachments']
			);
		} else {
			// Schedule the email.
			$result = (bool) ( new EntryEmailsTask() )
				->params(
					$data['to'],
					$this->get_prepared_subject( $data['subject'] ),
					$this->build_email( $data['message'] ),
					$data['headers'],
					$data['attachments']
				)
				->register();
		}

		/**
		 * Hooks after the email is sent.
		 *
		 * @since 1.1.3
		 *
		 * @param WPForms_WP_Emails $this Current instance of this object.
		 */
		do_action( 'wpforms_email_send_after', $this ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		return $result;
	}

	/**
	 * Add filters/actions before the email is sent.
	 *
	 * @since 1.1.3
	 */
	public function send_before() {

		add_filter( 'wp_mail_from', [ $this, 'get_from_address' ] );
		add_filter( 'wp_mail_from_name', [ $this, 'get_from_name' ] );
		add_filter( 'wp_mail_content_type', [ $this, 'get_content_type' ] );
	}

	/**
	 * Remove filters/actions after the email is sent.
	 *
	 * @since 1.1.3
	 */
	public function send_after() {

		remove_filter( 'wp_mail_from', [ $this, 'get_from_address' ] );
		remove_filter( 'wp_mail_from_name', [ $this, 'get_from_name' ] );
		remove_filter( 'wp_mail_content_type', [ $this, 'get_content_type' ] );
	}

	/**
	 * Convert text formatted HTML. This is primarily for turning line breaks
	 * into <p> and <br/> tags.
	 *
	 * @since 1.1.3
	 *
	 * @param string $message Text to convert.
	 *
	 * @return string
	 */
	public function text_to_html( $message ) {

		if ( 'text/html' === $this->content_type || true === $this->html ) {
			$message = wpautop( $message );
		}

		return $message;
	}

	/**
	 * Process a smart tag.
	 * Decodes entities and sanitized (keeping line breaks) by default.
	 *
	 * @uses  wpforms_decode_string()
	 *
	 * @since 1.1.3
	 * @since 1.6.0 Deprecated 2 params: $sanitize, $linebreaks.
	 *
	 * @param string $content String that may contain tags.
	 *
	 * @return string|mixed
	 */
	public function process_tag( $content = '' ) {

		return wpforms_process_smart_tags( $content, $this->form_data, $this->fields, $this->entry_id, 'email', $this->context_data );
	}

	/**
	 * Process the all fields smart tag if present.
	 *
	 * @since 1.1.3
	 *
	 * @param bool $is_html_email Toggle to use HTML or plaintext.
	 *
	 * @return string
	 */
	public function wpforms_html_field_value( $is_html_email = true ) { // phpcs:ignore

		if ( empty( $this->fields ) ) {
			return '';
		}

		if ( empty( $this->form_data['fields'] ) ) {
			$is_html_email = false;
		}

		$message = '';

		if ( $is_html_email ) {
			/*
			 * HTML emails.
			 */
			ob_start();

			// Hooks into the email field.
			do_action( 'wpforms_email_field', $this );

			$this->get_template_part( 'field', $this->get_template(), true );

			$field_template = ob_get_clean();

			// Check to see if user has added support for field type.
			$other_fields = apply_filters( 'wpforms_email_display_other_fields', [], $this );

			$x = 1;

			foreach ( $this->form_data['fields'] as $field_id => $field ) {

				$field_name = '';
				$field_val  = '';

				// If the field exists in the form_data but not in the final
				// field data, then it's a non-input based field, "other fields".
				if ( empty( $this->fields[ $field_id ] ) ) {

					// Check if the field type is in $other_fields, otherwise skip.
					// Skip if the field is conditionally hidden.
					if (
						empty( $other_fields ) ||
						! in_array( $field['type'], $other_fields, true ) ||
						(
							wpforms()->is_pro() &&
							wpforms_conditional_logic_fields()->field_is_hidden( $this->form_data, $field_id )
						)
					) {
						continue;
					}

					if ( $field['type'] === 'divider' ) {
						$field_name = ! empty( $field['label'] ) ? str_repeat( '&mdash;', 3 ) . ' ' . $field['label'] . ' ' . str_repeat( '&mdash;', 3 ) : null;
						$field_val  = ! empty( $field['description'] ) ? $field['description'] : '';
					} elseif ( $field['type'] === 'pagebreak' ) {
						if ( ! empty( $field['position'] ) && $field['position'] === 'bottom' ) {
							continue;
						}
						$title      = ! empty( $field['title'] ) ? $field['title'] : esc_html__( 'Page Break', 'wpforms-lite' );
						$field_name = str_repeat( '&mdash;', 6 ) . ' ' . $title . ' ' . str_repeat( '&mdash;', 6 );
					} elseif ( $field['type'] === 'html' ) {

						$field_name = ! empty( $field['name'] ) ? $field['name'] : esc_html__( 'HTML / Code Block', 'wpforms-lite' );
						$field_val  = $field['code'];
					} elseif ( $field['type'] === 'content' ) {

						$field_name = esc_html__( 'Content', 'wpforms-lite' );
						$field_val  = $field['content'];
					}
				} else {

					if (
						! apply_filters( 'wpforms_email_display_empty_fields', false ) &&
						( ! isset( $this->fields[ $field_id ]['value'] ) || (string) $this->fields[ $field_id ]['value'] === '' )
					) {
						/** This filter is documented in wpforms/includes/emails/class-emails.php */
						$message .= apply_filters( 'wpforms_wp_emails_html_field_value_message_html', '' , $field, $this->form_data );

						continue;
					}

					if ( $field['type'] === 'payment-total' ) {

						$field_name = isset( $this->fields[ $field_id ]['name'] ) ? $this->fields[ $field_id ]['name'] : '';

						// Replace the payment total value if an order summary is enabled.
						// Ideally, it could be done through the `wpforms_html_field_value` filter,
						// but needed data is missed there, e.g. entry data ($this->fields).
						if ( ! empty( $field['summary'] ) ) {
							$field_val = $this->process_tag( '{order_summary}' );
						} else {
							$field_val = $this->fields[ $field_id ]['value'];
						}
					} else {
						$field_name = isset( $this->fields[ $field_id ]['name'] ) ? $this->fields[ $field_id ]['name'] : '';
						$field_val  = empty( $this->fields[ $field_id ]['value'] ) && ! is_numeric( $this->fields[ $field_id ]['value'] ) ? '<em>' . esc_html__( '(empty)', 'wpforms-lite' ) . '</em>' : $this->fields[ $field_id ]['value'];
					}
				}

				if ( empty( $field_name ) && null !== $field_name ) {
					$field_name = sprintf( /* translators: %d - field ID. */
						esc_html__( 'Field ID #%s', 'wpforms-lite' ),
						wpforms_validate_field_id( $field['id'] )
					);
				}

				$field_item = $field_template;

				if ( 1 === $x ) {
					$field_item = str_replace( 'border-top:1px solid #dddddd;', '', $field_item );
				}

				/**
				 * Filter the field name before it is added to the email message.
				 *
				 * @since 1.9.1
				 *
				 * @param string $field_name Field name.
				 * @param array  $field      Field data.
				 * @param array  $form_data  Form data and settings.
				 * @param string $context    Context of the field name.
				 */
				$field_name = apply_filters( // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
					'wpforms_html_field_name',
					$field_name,
					$this->fields[ $field_id ] ?? $field,
					$this->form_data,
					'email-html'
				);

				$field_item = str_replace( '{field_name}', $field_name, $field_item );
				$field_item = str_replace(
					'{field_value}',
					apply_filters(
						'wpforms_html_field_value',
						$field_val,
						isset( $this->fields[ $field_id ] ) ? $this->fields[ $field_id ] : $field,
						$this->form_data,
						'email-html'
					),
					$field_item
				);

				/**
				 * Filter the field item before it is added to the email message.
				 *
				 * @since 1.9.3
				 *
				 * @param string $field_message Field message.
				 * @param array  $field         Field data.
				 * @param array  $form_data     Form data and settings.
				 */
				$message .= apply_filters( 'wpforms_wp_emails_html_field_value_message_html', wpautop( $field_item ), $field, $this->form_data );

				$x ++;
			}
		} else {
			/*
			 * Plain Text emails.
			 */
			foreach ( $this->fields as $field ) {

				if (
					! apply_filters( 'wpforms_email_display_empty_fields', false ) &&
					( ! isset( $field['value'] ) || (string) $field['value'] === '' )
				) {
					continue;
				}

				$field_val  = empty( $field['value'] ) && ! is_numeric( $field['value'] ) ? esc_html__( '(empty)', 'wpforms-lite' ) : $field['value'];
				$field_name = $field['name'];

				if ( empty( $field_name ) ) {
					$field_name = sprintf( /* translators: %d - field ID. */
						esc_html__( 'Field ID #%s', 'wpforms-lite' ),
						wpforms_validate_field_id( $field['id'] )
					);
				}

				$message    .= '--- ' . $field_name . " ---\r\n\r\n";
				$field_value = $field_val . "\r\n\r\n";
				$message    .= apply_filters( 'wpforms_plaintext_field_value', $field_value, $field, $this->form_data );
			}
		}

		if ( empty( $message ) ) {
			$empty_message = esc_html__( 'An empty form was submitted.', 'wpforms-lite' );
			$message       = $is_html_email ? wpautop( $empty_message ) : $empty_message;
		}

		return $message;
	}

	/**
	 * Email kill switch if needed.
	 *
	 * @since 1.1.3
	 *
	 * @return bool
	 */
	public function is_email_disabled() {

		return (bool) apply_filters( 'wpforms_disable_all_emails', false, $this );
	}

	/**
	 * Get the enabled email template.
	 *
	 * @since 1.1.3
	 *
	 * @return string When filtering return 'none' to switch to text/plain email.
	 */
	public function get_template() {

		if ( ! $this->template ) {
			$this->template = wpforms_setting( 'email-template', 'default' );
		}

		return apply_filters( 'wpforms_email_template', $this->template );
	}

	/**
	 * Retrieve a template part. Taken from bbPress.
	 *
	 * @since 1.1.3
	 *
	 * @param string $slug Template file slug.
	 * @param string $name Optional. Default null.
	 * @param bool   $load Maybe load.
	 *
	 * @return string
	 */
	public function get_template_part( $slug, $name = null, $load = true ) {

		// Setup possible parts.
		$templates = [];

		if ( isset( $name ) ) {
			$templates[] = $slug . '-' . $name . '.php';
		}

		$templates[] = $slug . '.php';

		// Return the part that is found.
		return $this->locate_template( $templates, $load, false );
	}

	/**
	 * Retrieve the name of the highest priority template file that exists.
	 *
	 * Search in the STYLESHEETPATH before TEMPLATEPATH so that themes which
	 * inherit from a parent theme can just overload one file. If the template is
	 * not found in either of those, it looks in the theme-compat folder last.
	 *
	 * Taken from bbPress.
	 *
	 * @since 1.1.3
	 *
	 * @param string|array $template_names Template file(s) to search for, in order.
	 * @param bool         $load           If true the template file will be loaded if it is found.
	 * @param bool         $require_once   Whether to require_once or require. Default true.
	 *                                     Has no effect if $load is false.
	 *
	 * @return string The template filename if one is located.
	 */
	public function locate_template( $template_names, $load = false, $require_once = true ) {

		// No file found yet.
		$located = false;

		// Try to find a template file.
		foreach ( (array) $template_names as $template_name ) {

			// Continue if template is empty.
			if ( empty( $template_name ) ) {
				continue;
			}

			// Trim off any slashes from the template name.
			$template_name = ltrim( $template_name, '/' );

			// Try locating this template file by looping through the template paths.
			foreach ( $this->get_theme_template_paths() as $template_path ) {
				$validated_path = Templates::validate_safe_path(
					$template_path . $template_name,
					[ 'theme', 'plugins' ]
				);

				if ( $validated_path ) {
					$located = $validated_path;

					break;
				}
			}
		}

		if ( ( true === $load ) && ! empty( $located ) ) {
			load_template( $located, $require_once );
		}

		return $located;
	}

	/**
	 * Return a list of paths to check for template locations
	 *
	 * @since 1.1.3
	 *
	 * @return array
	 */
	public function get_theme_template_paths() {

		$template_dir = 'wpforms-email';

		$file_paths = [
			1   => trailingslashit( get_stylesheet_directory() ) . $template_dir,
			10  => trailingslashit( get_template_directory() ) . $template_dir,
			100 => WPFORMS_PLUGIN_DIR . 'includes/emails/templates',
		];

		$file_paths = apply_filters( 'wpforms_email_template_paths', $file_paths );

		// Sort the file paths based on priority.
		ksort( $file_paths, SORT_NUMERIC );

		return array_map( 'trailingslashit', $file_paths );
	}

	/**
	 * Perform email subject preparation: process tags, remove new lines, etc.
	 *
	 * @since 1.6.1
	 *
	 * @param string $subject Email subject to post-process.
	 *
	 * @return string
	 */
	private function get_prepared_subject( $subject ) {

		$subject = $this->process_tag( $subject );

		$subject = trim( str_replace( [ "\r\n", "\r", "\n" ], ' ', $subject ) );

		return wpforms_decode_string( $subject );
	}
}
