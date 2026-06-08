<?php
/**
 * WPForms_Lite class file.
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection AutoloadingIssuesInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPForms\Admin\Builder\TemplatesCache;
use WPForms\Db\Payments\Meta as PaymentsMeta;
use WPForms\Db\Payments\Payment;
use WPForms\Lite\Integrations\LiteConnect\Integration as LiteConnectIntegration;
use WPForms\Lite\Integrations\LiteConnect\LiteConnect;
use WPForms\Logger\Repository;
use WPForms\Tasks\Meta as TasksMeta;

/**
 * WPForms Lite. Load Lite-specific features/functionality.
 *
 * @since 1.2.0
 */
class WPForms_Lite {

	/**
	 * Custom tables and their handlers.
	 *
	 * @since 1.9.0
	 */
	public const CUSTOM_TABLES = [
		'wpforms_payments'     => Payment::class,
		'wpforms_payment_meta' => PaymentsMeta::class,
		'wpforms_tasks_meta'   => TasksMeta::class,
		'wpforms_logs'         => Repository::class,
	];

	/**
	 * Primary class constructor.
	 *
	 * @since 1.2.2
	 */
	public function __construct() {

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.8.9
	 */
	private function hooks() {

		add_action( 'wpforms_install', [ $this, 'install' ] );
		add_action( 'wpforms_form_settings_notifications', [ $this, 'form_settings_notifications' ], 8 );
		add_action( 'wpforms_form_settings_confirmations', [ $this, 'form_settings_confirmations' ] );
		add_action( 'wpforms_builder_enqueues_before', [ $this, 'builder_enqueues' ] );
		add_action( 'wpforms_admin_page', [ $this, 'entries_page' ] );
		add_action( 'wpforms_admin_settings_after', [ $this, 'settings_cta' ] );
		add_action( 'wp_ajax_wpforms_lite_settings_upgrade', [ $this, 'settings_cta_dismiss' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueues' ] );
		add_filter( 'wpforms_helpers_templates_get_theme_template_paths', [ $this, 'add_templates' ] );

		// Entries count logging for WPForms Lite.
		add_action( 'wpforms_process_entry_saved', [ $this, 'entry_submit' ], 10, 5 );
		add_action( 'wpforms_process_entry_saved', [ $this, 'update_entry_count' ], 10, 5 );

		// Upgrade to Pro WPForms menu bar item.
		add_action( 'admin_bar_menu', [ $this, 'upgrade_to_pro_menu' ], 1000 );
	}

	/**
	 * Form notification settings, supports multiple notifications.
	 *
	 * @since 1.2.3
	 *
	 * @param object $settings Settings.
	 *
	 * @noinspection HtmlUnknownTarget
	 */
	public function form_settings_notifications( $settings ) {

		$cc         = wpforms_setting( 'email-carbon-copy' );
		$from_email = '{admin_email}';
		$from_name  = sanitize_text_field( get_option( 'blogname' ) );

		// phpcs:disable WPForms.PHP.ValidateHooks.InvalidHookName
		/**
		 * Allow filtering of text after the `From Name` field.
		 *
		 * @since 1.2.3
		 * @since 1.7.6 Added $form_data and $id arguments.
		 *
		 * @param string $value     Value to be filtered.
		 * @param array  $form_data Form data.
		 * @param int    $id        Notification ID.
		 */
		$from_name_after = apply_filters( 'wpforms_builder_notifications_from_name_after', '', $settings->form_data, 1 );

		/**
		 * Allow filtering of a text after the `From Email` field.
		 *
		 * @since 1.2.3
		 * @since 1.7.6 Added $form_data and $id arguments.
		 *
		 * @param array $value     Value to be filtered.
		 * @param array $form_data Form data.
		 * @param int   $id        Notification ID.
		 */
		$from_email_after = apply_filters( 'wpforms_builder_notifications_from_email_after', '', $settings->form_data, 1 );
		// phpcs:enable WPForms.PHP.ValidateHooks.InvalidHookName

		// Handle backwards compatibility.
		if ( empty( $settings->form_data['settings']['notifications'] ) ) {
			$settings->form_data['settings']['notifications'][1]['subject']        = ! empty( $settings->form_data['settings']['notification_subject'] ) ?
				$settings->form_data['settings']['notification_subject'] :
				sprintf( /* translators: %s - form name. */
					esc_html__( 'New %s Entry', 'wpforms-lite' ),
					$settings->form->post_title
				);
			$settings->form_data['settings']['notifications'][1]['email']          = ! empty( $settings->form_data['settings']['notification_email'] ) ? $settings->form_data['settings']['notification_email'] : '{admin_email}';
			$settings->form_data['settings']['notifications'][1]['sender_name']    = ! empty( $settings->form_data['settings']['notification_fromname'] ) ? $settings->form_data['settings']['notification_fromname'] : $from_name;
			$settings->form_data['settings']['notifications'][1]['sender_address'] = ! empty( $settings->form_data['settings']['notification_fromaddress'] ) ? $settings->form_data['settings']['notification_fromaddress'] : $from_email;
			$settings->form_data['settings']['notifications'][1]['replyto']        = ! empty( $settings->form_data['settings']['notification_replyto'] ) ? $settings->form_data['settings']['notification_replyto'] : '';
		}

		$id = 1;

		echo '<div class="wpforms-panel-content-section-title">';
			echo '<span id="wpforms-builder-settings-notifications-title">';
				esc_html_e( 'Notifications', 'wpforms-lite' );
			echo '</span>';
			echo '<button class="wpforms-builder-settings-block-add education-modal"
					data-utm-content="Multiple notifications"
					data-name="' . esc_attr__( 'Multiple notifications', 'wpforms-lite' ) . '">';
				esc_html_e( 'Add New Notification', 'wpforms-lite' );
			echo '</button>';
		echo '</div>';

		$dismissed = get_user_meta( get_current_user_id(), 'wpforms_dismissed', true );

		if ( empty( $dismissed['edu-builder-notifications-description'] ) ) {
			echo '<div class="wpforms-panel-content-section-description wpforms-dismiss-container wpforms-dismiss-out">';
			echo '<button type="button" class="wpforms-dismiss-button" title="' . esc_attr__( 'Dismiss this message.', 'wpforms-lite' ) . '" data-section="builder-notifications-description"></button>';
			echo '<p>';
			printf(
				wp_kses( /* translators: %s - link to the WPForms.com doc article. */
					__( 'Notifications are emails sent when a form is submitted. By default, these emails include entry details. For setup and customization options, including a video overview, please <a href="%s" target="_blank" rel="noopener noreferrer">see our tutorial</a>.', 'wpforms-lite' ),
					[
						'a' => [
							'href'   => [],
							'rel'    => [],
							'target' => [],
						],
					]
				),
				esc_url( wpforms_utm_link( 'https://wpforms.com/docs/setup-form-notification-wpforms/', 'Builder Notifications',  'Form Notifications Documentation' ) )
			);
			echo '</p>';
			echo '<p>';
			printf(
				wp_kses( /* translators: 1$s, %2$s - links to the WPForms.com doc articles. */
					__( 'After saving these settings, be sure to <a href="%1$s" target="_blank" rel="noopener noreferrer">test a form submission</a>. This lets you see how emails will look, and to ensure that they <a href="%2$s" target="_blank" rel="noopener noreferrer">are delivered successfully</a>.', 'wpforms-lite' ),
					[
						'a'  => [
							'href'   => [],
							'rel'    => [],
							'target' => [],
						],
						'br' => [],
					]
				),
				esc_url( wpforms_utm_link( 'https://wpforms.com/docs/how-to-properly-test-your-wordpress-forms-before-launching-checklist/', 'Builder Notifications', 'Testing A Form Documentation' ) ),
				esc_url( wpforms_utm_link( 'https://wpforms.com/docs/troubleshooting-email-notifications/', 'Builder Notifications', 'Troubleshoot Notifications Documentation' ) )
			);
			echo '</p>';
			echo '</div>';
		}

		wpforms_panel_field(
			'toggle',
			'settings',
			'notification_enable',
			$settings->form_data,
			esc_html__( 'Enable Notifications', 'wpforms-lite' )
		);
		?>

		<div class="wpforms-notification wpforms-builder-settings-block">

			<div class="wpforms-builder-settings-block-header">
				<span><?php esc_html_e( 'Default Notification', 'wpforms-lite' ); ?></span>
			</div>

			<div class="wpforms-builder-settings-block-content">

				<?php
				wpforms_panel_field(
					'text',
					'notifications',
					'email',
					$settings->form_data,
					esc_html__( 'Send To Email Address', 'wpforms-lite' ),
					[
						'default'     => '{admin_email}',
						'tooltip'     => esc_html__( 'Enter the email address to receive form entry notifications. For multiple notifications, separate email addresses with a comma.', 'wpforms-lite' ),
						'smarttags'   => [
							'type'    => 'all',
							'fields'  => 'email',
							'allowed' => 'admin_email,user_email',
						],
						'parent'      => 'settings',
						'subsection'  => $id,
						'class'       => 'email-recipient',
						'input_class' => 'wpforms-smart-tags-enabled',
					]
				);
				if ( $cc ) :
					wpforms_panel_field(
						'text',
						'notifications',
						'carboncopy',
						$settings->form_data,
						esc_html__( 'CC', 'wpforms-lite' ),
						[
							'smarttags'   => [
								'type'    => 'all',
								'fields'  => 'email',
								'allowed' => 'admin_email,user_email',
							],
							'parent'      => 'settings',
							'subsection'  => $id,
							'input_class' => 'wpforms-smart-tags-enabled',
						]
					);
				endif;
				wpforms_panel_field(
					'text',
					'notifications',
					'subject',
					$settings->form_data,
					esc_html__( 'Email Subject Line', 'wpforms-lite' ),
					[
						'default'     => sprintf( /* translators: %s - form name. */
							esc_html__( 'New Entry: %s', 'wpforms-lite' ),
							$settings->form->post_title
						),
						'smarttags'   => [
							'type' => 'all',
						],
						'parent'      => 'settings',
						'subsection'  => $id,
						'input_class' => 'wpforms-smart-tags-enabled',
					]
				);
				wpforms_panel_field(
					'text',
					'notifications',
					'sender_name',
					$settings->form_data,
					esc_html__( 'From Name', 'wpforms-lite' ),
					// phpcs:disable WPForms.PHP.ValidateHooks.InvalidHookName
					/**
					 * Allow modifying the "From Name" field settings in the builder on Settings > Notifications panel.
					 *
					 * @since 1.7.6
					 *
					 * @param array $args      Field settings.
					 * @param array $form_data Form data.
					 * @param int   $id        Notification ID.
					 */
					apply_filters(
						'wpforms_builder_notifications_sender_name_settings',
						[
							'default'     => $from_name,
							'smarttags'   => [
								'type'   => 'fields',
								'fields' => 'name,text',
							],
							'parent'      => 'settings',
							'subsection'  => $id,
							'input_class' => 'wpforms-smart-tags-enabled',
						],
						$settings->form_data,
						$id
					)
				// phpcs:enable WPForms.PHP.ValidateHooks.InvalidHookName
				);
				wpforms_panel_field(
					'text',
					'notifications',
					'sender_address',
					$settings->form_data,
					esc_html__( 'From Email', 'wpforms-lite' ),
					// phpcs:disable WPForms.PHP.ValidateHooks.InvalidHookName
					/**
					 * Allow modifying the "From Email" field settings in the builder on the Settings > Notifications panel.
					 *
					 * @since 1.7.6
					 *
					 * @param array $args      Field settings.
					 * @param array $form_data Form data.
					 * @param int   $id        Notification ID.
					 */
					apply_filters(
						'wpforms_builder_notifications_sender_address_settings',
						[
							'default'     => $from_email,
							'smarttags'   => [
								'type'    => 'all',
								'fields'  => 'email',
								'allowed' => 'admin_email,user_email',
							],
							'parent'      => 'settings',
							'subsection'  => $id,
							'input_class' => 'wpforms-smart-tags-enabled',
						],
						$settings->form_data,
						$id
					)
					// phpcs:enable WPForms.PHP.ValidateHooks.InvalidHookName
				);
				wpforms_panel_field(
					'text',
					'notifications',
					'replyto',
					$settings->form_data,
					esc_html__( 'Reply-To', 'wpforms-lite' ),
					[
						'tooltip'     => esc_html(
							sprintf( /* translators: %s - <email@example.com>. */
								__( 'Enter the email address or email address with recipient\'s name in "First Last %s" format.', 'wpforms-lite' ),
								// &#8203 is a zero-width space character. Without it, Tooltipster thinks it's an HTML tag
								// and closes it at the end of the string, hiding everything after this value.
								'<&#8203;email@example.com&#8203;>'
							)
						),
						'smarttags'   => [
							'type'    => 'all',
							'fields'  => 'email,name',
							'allowed' => 'admin_email,user_email',
						],
						'parent'      => 'settings',
						'subsection'  => $id,
						'input_class' => 'wpforms-smart-tags-enabled',
					]
				);
				wpforms_panel_field(
					'textarea',
					'notifications',
					'message',
					$settings->form_data,
					esc_html__( 'Email Message', 'wpforms-lite' ),
					[
						'rows'        => 6,
						'default'     => '{all_fields}',
						'smarttags'   => [
							'type' => 'all',
						],
						'parent'      => 'settings',
						'subsection'  => $id,
						'class'       => 'email-msg',
						'input_class' => 'wpforms-smart-tags-enabled',
						'after'       => '<p class="note">' .
											sprintf(
											/* translators: %s - {all_fields} Smart Tag. */
												esc_html__( 'To display all form fields, use the %s Smart Tag.', 'wpforms-lite' ),
												'<code>{all_fields}</code>'
											) .
											'</p>',
					]
				);

				/**
				 * Fires after notification block content on the lite version.
				 *
				 * @since 1.7.7
				 *
				 * @param array $settings Current confirmation data.
				 * @param int   $id       Notification id.
				 */
				do_action( 'wpforms_lite_form_settings_notifications_block_content_after', $settings, $id );
				?>
			</div>
		</div>

		<?php
		/**
		 * Fires after settings notification block.
		 *
		 * @since 1.5.8
		 *
		 * @param string $type     Settings block type.
		 * @param array  $settings Settings.
		 */
		do_action( 'wpforms_builder_settings_notifications_after', 'notifications', $settings ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		// phpcs:disable WPForms.PHP.ValidateHooks.InvalidHookName

		/**
		 * Fires after notification block.
		 *
		 * @since 1.7.6
		 *
		 * @param array $settings Current confirmation data.
		 * @param int   $id       Notification id.
		 */
		do_action( 'wpforms_form_settings_notifications_single_after', $settings, 1 );

		// phpcs:enable WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Lite admin scripts and styles.
	 *
	 * @since 1.5.7
	 */
	public function admin_enqueues() {

		if ( ! wpforms_is_admin_page() ) {
			return;
		}

		$min = wpforms_get_min_suffix();

		// Admin styles.
		wp_enqueue_style(
			'wpforms-lite-admin',
			WPFORMS_PLUGIN_URL . "assets/lite/css/admin{$min}.css",
			[],
			WPFORMS_VERSION
		);

		// Entries assets.
		wp_register_style(
			'wpforms-admin-entry-list',
			WPFORMS_PLUGIN_URL . "assets/lite/css/admin/entries/entry-list{$min}.css",
			[],
			WPFORMS_VERSION
		);

		wp_register_script(
			'wpforms-admin-entry-list',
			WPFORMS_PLUGIN_URL . "assets/lite/js/admin/entries/entry-list{$min}.js",
			[ 'jquery' ],
			WPFORMS_VERSION,
			true
		);

		wp_register_style(
			'wpforms-admin-view-entry',
			WPFORMS_PLUGIN_URL . "assets/lite/css/admin/entries/view-entry{$min}.css",
			[],
			WPFORMS_VERSION
		);

		wp_register_script(
			'wpforms-admin-view-entry',
			WPFORMS_PLUGIN_URL . "assets/lite/js/admin/entries/view-entry{$min}.js",
			[ 'jquery' ],
			WPFORMS_VERSION,
			true
		);
	}

	/**
	 * Form confirmation settings, supports multiple confirmations.
	 *
	 * @since 1.4.8
	 *
	 * @param WPForms_Builder_Panel_Settings $settings Builder panel settings.
	 */
	public function form_settings_confirmations( $settings ) {

		wp_enqueue_editor();

		// Handle backwards compatibility.
		if ( empty( $settings->form_data['settings']['confirmations'] ) ) {
			$settings->form_data['settings']['confirmations'][1]['type']           = ! empty( $settings->form_data['settings']['confirmation_type'] ) ? $settings->form_data['settings']['confirmation_type'] : 'message';
			$settings->form_data['settings']['confirmations'][1]['message']        = ! empty( $settings->form_data['settings']['confirmation_message'] ) ? $settings->form_data['settings']['confirmation_message'] : esc_html__( 'Thanks for contacting us! We will be in touch with you shortly.', 'wpforms-lite' );
			$settings->form_data['settings']['confirmations'][1]['message_scroll'] = ! empty( $settings->form_data['settings']['confirmation_message_scroll'] ) ? $settings->form_data['settings']['confirmation_message_scroll'] : 1;
			$settings->form_data['settings']['confirmations'][1]['page']           = ! empty( $settings->form_data['settings']['confirmation_page'] ) ? $settings->form_data['settings']['confirmation_page'] : '';
			$settings->form_data['settings']['confirmations'][1]['redirect']       = ! empty( $settings->form_data['settings']['confirmation_redirect'] ) ? $settings->form_data['settings']['confirmation_redirect'] : '';
		}
		$field_id = 1;

		echo '<div class="wpforms-panel-content-section-title">';
			esc_html_e( 'Confirmations', 'wpforms-lite' );
			echo '<button class="wpforms-builder-settings-block-add education-modal"
					data-utm-content="Multiple confirmations"
					data-name="' . esc_attr__( 'Multiple confirmations', 'wpforms-lite' ) . '">';
				esc_html_e( 'Add New Confirmation', 'wpforms-lite' );
			echo '</button>';
		echo '</div>';
		?>

		<div class="wpforms-confirmation wpforms-builder-settings-block">

			<div class="wpforms-builder-settings-block-header">
				<span><?php esc_html_e( 'Default Confirmation', 'wpforms-lite' ); ?></span>
			</div>

			<div class="wpforms-builder-settings-block-content">

				<?php
				/**
				 * Fires before each confirmation to add custom fields.
				 *
				 * @since 1.6.9
				 *
				 * @param WPForms_Builder_Panel_Settings $settings Builder panel settings.
				 * @param int                            $field_id Field ID.
				 */
				do_action( 'wpforms_lite_form_settings_confirmations_single_before', $settings, $field_id );

				wpforms_panel_field(
					'select',
					'confirmations',
					'type',
					$settings->form_data,
					esc_html__( 'Confirmation Type', 'wpforms-lite' ),
					[
						'default'     => 'message',
						'options'     => [
							'message'  => esc_html__( 'Message', 'wpforms-lite' ),
							'page'     => esc_html__( 'Show Page', 'wpforms-lite' ),
							'redirect' => esc_html__( 'Go to URL (Redirect)', 'wpforms-lite' ),
						],
						'class'       => 'wpforms-panel-field-confirmations-type-wrap',
						'input_class' => 'wpforms-panel-field-confirmations-type',
						'parent'      => 'settings',
						'subsection'  => $field_id,
					]
				);
				wpforms_panel_field(
					'textarea',
					'confirmations',
					'message',
					$settings->form_data,
					esc_html__( 'Confirmation Message', 'wpforms-lite' ),
					[
						'default'     => esc_html__( 'Thanks for contacting us! We will be in touch with you shortly.', 'wpforms-lite' ),
						'tinymce'     => [
							'editor_height' => '200',
						],
						'input_id'    => 'wpforms-panel-field-confirmations-message-' . $field_id,
						'input_class' => 'wpforms-panel-field-confirmations-message',
						'parent'      => 'settings',
						'subsection'  => $field_id,
						'class'       => 'wpforms-panel-field-tinymce',
						'smarttags'   => [
							'type' => 'all',
						],
					]
				);
				wpforms_panel_field(
					'toggle',
					'confirmations',
					'message_scroll',
					$settings->form_data,
					esc_html__( 'Automatically scroll to the confirmation message', 'wpforms-lite' ),
					[
						'input_class' => 'wpforms-panel-field-confirmations-message_scroll',
						'parent'      => 'settings',
						'subsection'  => $field_id,
					]
				);

				wpforms_panel_field(
					'select',
					'confirmations',
					'page',
					$settings->form_data,
					esc_html__( 'Confirmation Page', 'wpforms-lite' ),
					[
						'class'       => 'wpforms-panel-field-confirmations-page-choicesjs',
						'options'     => wpforms_builder_form_settings_confirmation_get_pages( $settings->form_data, $field_id ),
						'input_class' => 'wpforms-panel-field-confirmations-page',
						'parent'      => 'settings',
						'subsection'  => $field_id,
						'choicesjs'   => [
							'use_ajax'    => true,
							'callback_fn' => 'select_pages',
						],
					]
				);

				wpforms_panel_field(
					'text',
					'confirmations',
					'page_url_parameters',
					$settings->form_data,
					esc_html__( 'URL Parameters', 'wpforms-lite' ),
					[
						'input_id'    => 'wpforms-panel-field-confirmations-page-url-parameters-' . $field_id,
						'input_class' => 'wpforms-panel-field-confirmations-page-url-parameters',
						'parent'      => 'settings',
						'subsection'  => $field_id,
						'tooltip'     => esc_html__( 'Add query string parameters to append to the URL when the form is submitted. Separate multiple parameters with an ampersand (&).', 'wpforms-lite' ),
					]
				);

				wpforms_panel_field(
					'text',
					'confirmations',
					'redirect',
					$settings->form_data,
					esc_html__( 'Confirmation Redirect URL', 'wpforms-lite' ) . ' <span class="required">*</span>',
					[
						'input_class' => 'wpforms-panel-field-confirmations-redirect',
						'parent'      => 'settings',
						'subsection'  => $field_id,
					]
				);

				wpforms_panel_field(
					'toggle',
					'confirmations',
					'redirect_new_tab',
					$settings->form_data,
					esc_html__( 'Open confirmation in new tab', 'wpforms-lite' ),
					[
						'input_id'    => 'wpforms-panel-field-confirmations-redirect_new_tab-' . $field_id,
						'input_class' => 'wpforms-panel-field-confirmations-redirect_new_tab',
						'parent'      => 'settings',
						'subsection'  => $field_id,
					]
				);

				/**
				 * Fires after each confirmation to add custom fields.
				 *
				 * @since 1.6.9
				 *
				 * @param WPForms_Builder_Panel_Settings $settings Builder panel settings.
				 * @param int                            $field_id Field ID.
				 */
				do_action( 'wpforms_lite_form_settings_confirmations_single_after', $settings, $field_id );
				?>
			</div>
		</div>

		<?php
		/**
		 * Fires after builder settings confirmation block.
		 *
		 * @since 1.5.8
		 *
		 * @param string $type     Settings block type.
		 * @param array  $settings Settings.
		 */
		do_action( 'wpforms_builder_settings_confirmations_after', 'confirmations', $settings ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Load assets for the lite version with the admin builder.
	 *
	 * @since 1.0.0
	 *
	 * @noinspection HtmlUnknownTarget
	 */
	public function builder_enqueues() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-builder-lite',
			WPFORMS_PLUGIN_URL . "assets/lite/js/admin/builder/admin-builder-lite{$min}.js",
			[ 'jquery', 'jquery-confirm' ],
			WPFORMS_VERSION,
			false
		);

		$strings = [
			'disable_notifications' => sprintf(
				wp_kses( /* translators: %s - WPForms.com docs page URL. */
					__( 'You\'ve just turned off notification emails for this form. Since entries are not stored in WPForms Lite, notification emails are recommended for collecting entry details. For setup steps, <a href="%s" target="_blank" rel="noopener noreferrer">please see our notification tutorial</a>.', 'wpforms-lite' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
						],
					]
				),
				esc_url( wpforms_utm_link( 'https://wpforms.com/docs/setup-form-notification-wpforms/', 'Builder Notifications', 'Disable Notifications Alert' ) )
			),
		];

		$strings = apply_filters( 'wpforms_lite_builder_strings', $strings );

		wp_localize_script(
			'wpforms-builder-lite',
			'wpforms_builder_lite',
			$strings
		);
	}

	/**
	 * Display upgrade notice at the bottom on the plugin settings pages.
	 *
	 * @since 1.4.7
	 *
	 * @param string $view Current view inside the plugin settings page.
	 */
	public function settings_cta( $view ) {

		if ( get_option( 'wpforms_lite_settings_upgrade', false ) || apply_filters( 'wpforms_lite_settings_upgrade', false ) ) {
			return;
		}
		?>
		<div class="settings-lite-cta">
			<a href="#" class="dismiss" title="<?php esc_attr_e( 'Dismiss this message', 'wpforms-lite' ); ?>"><i class="fa fa-times-circle" aria-hidden="true"></i></a>
			<h5><?php esc_html_e( 'Get WPForms Pro and Unlock all the Powerful Features', 'wpforms-lite' ); ?></h5>
			<p><?php esc_html_e( 'Thanks for being a loyal WPForms Lite user. Upgrade to WPForms Pro to unlock all the awesome features and experience why WPForms is consistently rated the best WordPress form builder.', 'wpforms-lite' ); ?></p>
			<p>
				<?php
				printf(
					wp_kses( /* translators: %s - star icons. */
						__( 'We know that you will truly love WPForms. It has over 13,000+ five star ratings (%s) and is active on over 6 million websites.', 'wpforms-lite' ),
						[
							'i' => [
								'class'       => [],
								'aria-hidden' => [],
							],
						]
					),
					str_repeat( '<i class="fa fa-star" aria-hidden="true"></i>', 5 ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				);
				?>
			</p>
			<h6><?php esc_html_e( 'Pro Features:', 'wpforms-lite' ); ?></h6>
			<div class="list">
				<ul>
					<li>
						<?php
						printf( /* translators: %s - number of templates. */
							esc_html__( '%s customizable form templates', 'wpforms-lite' ),
							'2100+'
						);
						?>
					</li>
					<li><?php esc_html_e( 'Store and manage form entries in WordPress', 'wpforms-lite' ); ?></li>
					<li><?php esc_html_e( 'Unlock all fields & features, including smart conditional logic', 'wpforms-lite' ); ?></li>
					<li><?php esc_html_e( 'Create powerful custom calculation forms', 'wpforms-lite' ); ?></li>
					<li><?php esc_html_e( 'Make surveys and generate reports', 'wpforms-lite' ); ?></li>
					<li><?php esc_html_e( 'Accept user-submitted content with the Post Submissions addon', 'wpforms-lite' ); ?></li>
				</ul>
				<ul>
					<li><?php esc_html_e( '9000+ integrations with marketing and payment services', 'wpforms-lite' ); ?></li>
					<li><?php esc_html_e( 'Let users save & resume submissions to prevent abandonment', 'wpforms-lite' ); ?></li>
					<li><?php esc_html_e( 'Take payments with Stripe, PayPal, Square, & Authorize.Net', 'wpforms-lite' ); ?></li>
					<li><?php esc_html_e( 'Export entries to Google Sheets, Excel, and CSV', 'wpforms-lite' ); ?></li>
					<li><?php esc_html_e( 'Collect signatures, geolocation data, and file uploads', 'wpforms-lite' ); ?></li>
					<li><?php esc_html_e( 'Create user registration and login forms', 'wpforms-lite' ); ?></li>
				</ul>
			</div>
			<p>
				<?php $utm_content = ucwords( $view ) . ' Tab'; ?>
				<a href="<?php echo esc_url( wpforms_admin_upgrade_link( 'settings-upgrade', $utm_content ) ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Get WPForms Pro Today and Unlock all the Powerful Features »', 'wpforms-lite' ); ?>
				</a>
			</p>
			<p>
				<?php
				echo wp_kses(
					__( '<strong>Bonus:</strong> WPForms Lite users get <span class="green">50% off regular price</span>, automatically applied at checkout.', 'wpforms-lite' ),
					[
						'strong' => [],
						'span'   => [
							'class' => [],
						],
					]
				);
				?>
			</p>
		</div>
		<script type="text/javascript">
			jQuery( function ( $ ) {
				$( document ).on( 'click', '.settings-lite-cta .dismiss', function ( event ) {
					event.preventDefault();
					$.post( ajaxurl, {
						action: 'wpforms_lite_settings_upgrade',
						nonce: '<?php echo esc_html( wp_create_nonce( 'wpforms_settings_cta_dismiss' ) ); ?>'
					} );
					$( '.settings-lite-cta' ).remove();
				} );
			} );
		</script>
		<?php
	}

	/**
	 * Dismiss upgrade notice at the bottom on the plugin settings pages.
	 *
	 * @since 1.4.7
	 */
	public function settings_cta_dismiss() {

		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'wpforms_settings_cta_dismiss' ) ) {
			wp_send_json_error( esc_html__( 'Security check failed. Please try again.', 'wpforms-lite' ) );
		}

		if ( ! wpforms_current_user_can() ) {
			wp_send_json_error();
		}

		update_option( 'wpforms_lite_settings_upgrade', time() );

		wp_send_json_success();
	}

	/**
	 * Display sample data and notify user that entries is a pro feature.
	 *
	 * @since 1.0.0
	 */
	public function entries_page() {

		if ( wpforms_is_admin_page( 'entries', 'sample' ) ) {
			$this->entry_single_page();

			return;
		}

		if ( wpforms_is_admin_page( 'entries' ) ) {
			$this->entries_list_page();
		}
	}

	/**
	 * Display the Entries List page with sample data.
	 *
	 * @since 1.8.9
	 */
	private function entries_list_page() {

		$is_lite_connect_enabled = LiteConnect::is_enabled();
		$is_lite_connect_allowed = LiteConnect::is_allowed();

		wp_enqueue_style( 'wpforms-admin-entry-list' );
		wp_enqueue_script( 'wpforms-admin-entry-list' );

		echo wpforms_render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'admin/entries/overview/entry-list',
			[
				'is_lite_connect_enabled' => $is_lite_connect_enabled,
				'is_lite_connect_allowed' => $is_lite_connect_allowed,
				'entries_count'           => LiteConnectIntegration::get_new_entries_count(),
				'enabled_since'           => LiteConnectIntegration::get_enabled_since(),
				'sample_entries'          => $this->get_entries_list_data(),
				'utm'                     => $this->get_entries_utm(),
			],
			true
		);
	}

	/**
	 * Display the Single Entry page with sample data.
	 *
	 * @since 1.8.9
	 */
	private function entry_single_page() {

		wp_enqueue_style( 'wpforms-admin-view-entry' );
		wp_enqueue_script( 'wpforms-admin-view-entry' );

		echo wpforms_render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'admin/entries/single/entry',
			[
				'utm' => $this->get_entries_utm(),
			],
			true
		);
	}

	/**
	 * Increase entries count once a form is submitted.
	 *
	 * @since 1.5.9
	 * @since 1.8.2 Added Payment ID.
	 *
	 * @param array $fields     Set of form fields.
	 * @param array $entry      Entry contents.
	 * @param array $form_data  Form data.
	 * @param int   $entry_id   Entry ID.
	 * @param int   $payment_id Payment ID for the payment form.
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function update_entry_count( $fields, $entry, $form_data, $entry_id, $payment_id ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		if ( ! empty( $form_data['spam_reason'] ) ) {
			return;
		}

		global $wpdb;

		/**
		 * Filters whether to allow counting entries for Lite users.
		 *
		 * @since 1.5.9
		 *
		 * @param bool $allow_entries_count True to allow, false to disallow. Default: true.
		 */
		if ( ! apply_filters( 'wpforms_dash_widget_allow_entries_count_lite', true ) ) { // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
			return;
		}

		$form_id = absint( $form_data['id'] );

		if ( empty( $form_id ) ) {
			return;
		}

		if ( wpforms_is_form_template( $form_id ) ) {
			return;
		}

		if ( add_post_meta( $form_id, 'wpforms_entries_count', 1, true ) ) {
			return;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE $wpdb->postmeta
					SET meta_value = meta_value + 1
					WHERE post_id = %d AND meta_key = 'wpforms_entries_count'",
				$form_id
			)
		);
	}

	/**
	 * Submit entry to the Lite Connect API.
	 *
	 * @since 1.7.4
	 * @since 1.8.2 Added Payment ID.
	 *
	 * @param array $fields     Set of form fields.
	 * @param array $entry      Entry contents.
	 * @param array $form_data  Form data.
	 * @param int   $entry_id   Entry ID.
	 * @param int   $payment_id Payment ID for the payment form.
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function entry_submit( $fields, $entry, $form_data, $entry_id, $payment_id ) {

		$submission = wpforms()->obj( 'submission' );

		$submission->register( $fields, $entry, $form_data['id'], $form_data );

		// Prepare the entry args.
		$entry_args = $submission->prepare_entry_data();

		if ( $payment_id ) {
			$entry_args['type']       = 'payment';
			$entry_args['payment_id'] = $payment_id;
		}

		if ( ! empty( $form_data['spam_reason'] ) ) {
			$entry_args['status'] = 'spam';
		}

		// Submit entry args and form data to the Lite Connect API.
		if (
			! empty( $entry_args ) &&
			LiteConnect::is_allowed() &&
			LiteConnect::is_enabled()
		) {
			( new LiteConnectIntegration() )->submit( $entry_args, $form_data );
		}
	}

	/**
	 * Add Lite-specific templates to the list of searchable template paths.
	 *
	 * @since 1.6.6
	 *
	 * @param array $paths Paths to templates.
	 *
	 * @return array
	 */
	public function add_templates( $paths ) {

		$paths = (array) $paths;

		$paths[102] = trailingslashit( __DIR__ . '/templates' );

		return $paths;
	}

	/**
	 * Render Upgrade to Pro admin bar menu item.
	 *
	 * @since 1.7.4
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WordPress Admin Bar object.
	 */
	public function upgrade_to_pro_menu( WP_Admin_Bar $wp_admin_bar ) {

		$current_screen      = is_admin() ? get_current_screen() : null;
		$upgrade_utm_content = $current_screen === null ? 'Upgrade to Pro' : 'Upgrade to Pro - ' . $current_screen->base;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$upgrade_utm_content = empty( $_GET['view'] ) ? $upgrade_utm_content : $upgrade_utm_content . ': ' . sanitize_key( $_GET['view'] );

		$wp_admin_bar->add_menu(
			[
				'parent' => 'wpforms-menu',
				'id'     => 'wpforms-upgrade',
				'title'  => esc_html__( 'Upgrade to Pro', 'wpforms-lite' ),
				'href'   => esc_url( $this->admin_upgrade_link( 'admin-bar', $upgrade_utm_content ) ),
				'meta'   => [
					'target' => '_blank',
					'rel'    => 'noopener noreferrer',
				],
			]
		);
	}

	/**
	 * Upgrade link used within the various admin pages.
	 *
	 * TODO: This is a duplicate of the function in the WPForms class. We should refactor this to use the same function.
	 *
	 * @since 1.8.5.1
	 *
	 * @param string $medium  URL parameter: utm_medium.
	 * @param string $content URL parameter: utm_content.
	 *
	 * @return string
	 */
	private function admin_upgrade_link( string $medium = 'link', string $content = '' ): string {

		$url = 'https://wpforms.com/lite-upgrade/';

		if ( wpforms()->is_pro() ) {
			$license_key = wpforms_get_license_key();
			$url         = add_query_arg(
				'license_key',
				sanitize_text_field( $license_key ),
				'https://wpforms.com/pricing/'
			);
		}

		$upgrade = wpforms_utm_link( $url, apply_filters( 'wpforms_upgrade_link_medium', $medium ), $content ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName, WPForms.Comments.PHPDocHooks.RequiredHookDocumentation

		/**
		 * Modify upgrade link.
		 *
		 * @since 1.5.1
		 *
		 * @param string $upgrade Upgrade links.
		 */
		return apply_filters( 'wpforms_upgrade_link', $upgrade ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Handle plugin installation upon activation.
	 *
	 * @since 1.7.4
	 */
	public function install() {

		// Restart the import flags for Lite Connect if needed.
		if ( class_exists( LiteConnectIntegration::class ) ) {
			LiteConnectIntegration::maybe_restart_import_flag();
		}

		// Wipe templates content cache.
		if ( class_exists( TemplatesCache::class ) ) {
			( new TemplatesCache() )->wipe_content_cache();
		}
	}

	/**
	 * Retrieve UTM parameters for Entries pages.
	 *
	 * @since 1.8.9
	 *
	 * @return array
	 */
	private function get_entries_utm(): array {

		return [
			'entries_list_button' => 'https://wpforms.com/lite-upgrade/?utm_campaign=liteplugin&utm_source=WordPress&utm_medium=entries&utm_content=Upgrade%20Now%20-%20Entries%20list',
			'entries_list_link'   => 'https://wpforms.com/lite-upgrade/?utm_campaign=liteplugin&utm_source=WordPress&utm_medium=entries&utm_content=Upgrade%20to%20Pro%20-%20Entries%20list',
			'entry_single_button' => 'https://wpforms.com/lite-upgrade/?utm_campaign=liteplugin&utm_source=WordPress&utm_medium=entries&utm_content=Upgrade%20to%20Pro%20-%20Single%20Entry',
			'entry_single_link'   => 'https://wpforms.com/lite-upgrade/?utm_campaign=liteplugin&utm_source=WordPress&utm_medium=entries&utm_content=Upgrade%20to%20Pro%20-%20Single%20Entry',
		];
	}

	/**
	 * Retrieve dummy data for the Entries List page.
	 *
	 * @since 1.8.9
	 *
	 * return array
	 */
	private function get_entries_list_data(): array {

		return [
			[
				'name' => 'Michael Johnson',
				'read' => true,
			],
			[
				'name' => 'David Thompson',
				'read' => true,
			],
			[
				'name' => 'Sarah Parker',
				'read' => true,
			],
			[
				'name' => 'Brian Anderson',
				'read' => true,
				'star' => true,
			],
			[
				'name' => 'Emily Davis',
				'read' => true,
				'star' => true,
			],
			[
				'name' => 'Laura White',
				'read' => true,
			],
			[
				'name' => 'Kevin Wilson',
				'read' => true,
			],
			[
				'name' => 'Megan Clark',
				'read' => true,
			],
			[
				'name' => 'Nicole Allen',
				'read' => true,
				'star' => true,
			],
			[
				'name' => 'Jason Miller',
			],
			[
				'name' => 'Rachel Moore',
			],
			[
				'name' => 'Chris Taylor',
				'star' => true,
			],
		];
	}
}

new WPForms_Lite();
