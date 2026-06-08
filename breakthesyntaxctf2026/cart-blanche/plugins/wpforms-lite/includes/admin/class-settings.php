<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


use WPForms\Admin\Notice;
use WPForms\Migrations\Migrations as LiteMigration;
use WPForms\Pro\Migrations\Migrations;
use WPForms\Admin\Settings\Payments;

/**
 * Settings class.
 *
 * @since 1.0.0
 */
class WPForms_Settings {

	/**
	 * The current active tab.
	 *
	 * @since 1.3.9
	 *
	 * @var string
	 */
	public $view;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.8.5.4
	 */
	private function hooks() {

		// Maybe load settings page.
		add_action( 'admin_init', [ $this, 'init' ] );
	}

	/**
	 * Determine if the user is viewing the settings page, if so, party on.
	 *
	 * @since 1.0.0
	 */
	public function init() { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		// Only load if we are actually on the settings page.
		if ( ! wpforms_is_admin_page( 'settings' ) ) {
			return;
		}

		// Include API callbacks and functions.
		require_once WPFORMS_PLUGIN_DIR . 'includes/admin/settings-api.php';

		// Show downgraded notice.
		$this->maybe_display_downgraded_notice();

		// Watch for triggered save.
		$this->save_settings();

		// Determine the current active settings tab.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$this->view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : 'general';

		$this->modify_url();

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueues' ] );
		add_action( 'wpforms_admin_page', [ $this, 'output' ] );

		// Monitor custom tables.
		$this->monitor_custom_tables();

		// Hook for addons.
		do_action( 'wpforms_settings_init', $this );
	}

	/**
	 * Remove `wpforms-integration` query arg from URL.
	 * The `wpforms-integration` query arg is used to highlight a specific provider on the Integrations page.
	 *
	 * @since 1.8.5.4
	 */
	private function modify_url() {

		if ( $this->view !== 'integrations' ) {
			return;
		}

		$_SERVER['REQUEST_URI'] = remove_query_arg( 'wpforms-integration' );
	}

	/**
	 * Display admin notice about using a downgraded version of WPForms.
	 *
	 * @since 1.8.5.4
	 */
	private function maybe_display_downgraded_notice() {

		if ( ! $this->is_downgraded_version() ) {
			return;
		}

		$notice = sprintf(
			wp_kses( /* translators: %1$s - WPForms.com doc page URL; %2$s - button text. */
				__(
					'It looks like you\'ve downgraded to an older version of WPForms. We recommend always using the latest version as some features may not function as expected in older versions. <a href="%1$s" target="_blank" rel="noopener">%2$s</a>',
					'wpforms-lite'
				),
				[
					'a' => [
						'href'   => [],
						'target' => [],
						'rel'    => [],
					],
				]
			),
			esc_url( wpforms_utm_link( 'https://wpforms.com/docs/why-you-should-always-use-the-latest-version-of-wpforms/', 'Settings', 'Downgrade notice' ) ),
			esc_html__( 'Learn More', 'wpforms-lite' )
		);

		Notice::warning(
			$notice,
			[
				'dismiss' => Notice::DISMISS_GLOBAL,
				'slug'    => 'wpforms_is_downgraded',
			]
		);
	}

	/**
	 * Check if plugin was downgraded.
	 *
	 * @since 1.8.5.4
	 *
	 * @return bool
	 */
	private function is_downgraded_version(): bool {

		// Get all installed versions.
		$installed_versions = wpforms()->is_pro() ?
			(array) get_option( Migrations::MIGRATED_OPTION_NAME, [] ) :
			(array) get_option( LiteMigration::MIGRATED_OPTION_NAME, [] );

		// Get the most recent installed version.
		$db_latest = array_keys( $installed_versions )[ count( $installed_versions ) - 1 ];

		// Check if downgrade happened.
		return version_compare( $db_latest, WPFORMS_VERSION, '>' );
	}

	/**
	 * Sanitize and save settings.
	 *
	 * @since 1.3.9
	 */
	public function save_settings() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded, Generic.Metrics.NestingLevel.MaxExceeded

		// Check nonce and other various security checks.
		if ( ! isset( $_POST['wpforms-settings-submit'] ) || empty( $_POST['nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wpforms-settings-nonce' ) ) {
			return;
		}

		if ( ! wpforms_current_user_can() ) {
			return;
		}

		if ( empty( $_POST['view'] ) ) {
			return;
		}

		$current_view = sanitize_key( $_POST['view'] );

		// Get registered fields and current settings.
		$fields            = $this->get_registered_settings( $current_view );
		$settings          = get_option( 'wpforms_settings', [] );
		$original_settings = $settings;

		// Views excluded from saving list.
		$exclude_views = apply_filters( 'wpforms_settings_exclude_view', [], $fields, $settings );

		if ( is_array( $exclude_views ) && in_array( $current_view, $exclude_views, true ) ) {
			// Run a custom save processing for excluded views.
			do_action( 'wpforms_settings_custom_process', $current_view, $fields, $settings );

			return;
		}

		if ( empty( $fields ) || ! is_array( $fields ) ) {
			return;
		}

		// Sanitize and prep each field.
		foreach ( $fields as $id => $field ) {

			// Certain field types are not valid for saving and are skipped.
			$exclude = apply_filters( 'wpforms_settings_exclude_type', [ 'content', 'license', 'providers' ] );

			if ( empty( $field['type'] ) || in_array( $field['type'], $exclude, true ) ) {
				continue;
			}

			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$value      = isset( $_POST[ $id ] ) ? wp_unslash( $_POST[ $id ] ) : false;
			$value_prev = isset( $settings[ $id ] ) ? $settings[ $id ] : false;

			// Trim all string values.
			if ( is_string( $value ) ) {
				$value = trim( $value );
			}

			// Custom filter can be provided for sanitizing, otherwise use defaults.
			if ( ! empty( $field['filter'] ) && is_callable( $field['filter'] ) ) {

				$value = call_user_func( $field['filter'], $value, $id, $field, $value_prev );

			} else {

				switch ( $field['type'] ) {
					case 'checkbox':
					case 'toggle':
						$value = (bool) $value;
						break;

					case 'image':
						$value = esc_url_raw( $value );
						break;

					case 'color':
						$value = wpforms_sanitize_hex_color( $value );
						break;

					case 'color_scheme':
						$value = array_map( 'wpforms_sanitize_hex_color', $value );
						break;

					case 'number':
						$value = (float) $value;
						break;

					case 'radio':
					case 'select':
						$value = $this->validate_field_with_options( $field, $value, $value_prev );
						break;

					case 'text':
					default:
						$value = sanitize_text_field( $value );
						break;
				}
			}

			// Add to settings.
			$settings[ $id ] = $value;
		}

		// Save settings.
		wpforms_update_settings( $settings );

		Notice::success( esc_html__( 'Settings were successfully saved.', 'wpforms-lite' ) );

		if ( isset( $original_settings['currency'], $settings['currency'] ) && $original_settings['currency'] !== $settings['currency'] ) {

			Notice::warning( esc_html__( "You've changed your currency. Please double-check the product prices in your forms and verify that they're correct.", 'wpforms-lite' ) );
		}
	}

	/**
	 * Enqueue assets for the settings page.
	 *
	 * @since 1.0.0
	 */
	public function enqueues() {

		do_action( 'wpforms_settings_enqueue' );
	}

	/**
	 * Return registered settings tabs.
	 *
	 * @since 1.3.9
	 *
	 * @return array
	 */
	public function get_tabs() {

		$tabs = [
			'general'      => [
				'name'   => esc_html__( 'General', 'wpforms-lite' ),
				'form'   => true,
				'submit' => esc_html__( 'Save Settings', 'wpforms-lite' ),
			],
			'validation'   => [
				'name'   => esc_html__( 'Validation', 'wpforms-lite' ),
				'form'   => true,
				'submit' => esc_html__( 'Save Settings', 'wpforms-lite' ),
			],
			'integrations' => [
				'name'   => esc_html__( 'Integrations', 'wpforms-lite' ),
				'form'   => false,
				'submit' => false,
			],
			'geolocation'  => [
				'name'   => esc_html__( 'Geolocation', 'wpforms-lite' ),
				'form'   => false,
				'submit' => false,
			],
			'misc'         => [
				'name'   => esc_html__( 'Misc', 'wpforms-lite' ),
				'form'   => true,
				'submit' => esc_html__( 'Save Settings', 'wpforms-lite' ),
			],
		];

		return apply_filters( 'wpforms_settings_tabs', $tabs );
	}

	/**
	 * Output tab navigation area.
	 *
	 * @since 1.3.9
	 */
	public function tabs() {

		$tabs = $this->get_tabs();

		echo '<ul class="wpforms-admin-tabs">';
		foreach ( $tabs as $id => $tab ) {

			$active = $id === $this->view ? 'active' : '';
			$link   = add_query_arg( 'view', $id, admin_url( 'admin.php?page=wpforms-settings' ) );

			echo '<li><a href="' . esc_url_raw( $link ) . '" class="' . esc_attr( $active ) . '">' . esc_html( $tab['name'] ) . '</a></li>';
		}
		echo '</ul>';
	}

	/**
	 * Return all the default registered settings fields.
	 *
	 * @since 1.3.9
	 *
	 * @param string $view The current view (tab) on Settings page.
	 *
	 * @return array
	 */
	public function get_registered_settings( $view = '' ) {

		$defaults = [
			// General Settings tab.
			'general'      => [
				'license-heading' => [
					'id'       => 'license-heading',
					'content'  => '<h4>' . esc_html__( 'License', 'wpforms-lite' ) . '</h4><p>' . esc_html__( 'Your license key provides access to updates and addons.', 'wpforms-lite' ) . '</p>',
					'type'     => 'content',
					'no_label' => true,
					'class'    => [ 'section-heading' ],
				],
				'license-key'     => [
					'id'   => 'license-key',
					'name' => esc_html__( 'License Key', 'wpforms-lite' ),
					'type' => 'license',
				],
				'general-heading' => [
					'id'       => 'general-heading',
					'content'  => '<h4>' . esc_html__( 'General', 'wpforms-lite' ) . '</h4>',
					'type'     => 'content',
					'no_label' => true,
					'class'    => [ 'section-heading', 'no-desc' ],
				],
				'disable-css'     => [
					'id'        => 'disable-css',
					'name'      => esc_html__( 'Include Form Styling', 'wpforms-lite' ),
					'desc'      => sprintf(
						wp_kses( /* translators: %s - WPForms.com form styling setting URL. */
							__( 'Determines which CSS files to load and use for the site. "Base and Form Theme Styling" is recommended, unless you are experienced with CSS or instructed by support to change settings. <a href="%s" target="_blank" rel="noopener noreferrer" class="wpforms-learn-more">Learn More</a>', 'wpforms-lite' ),
							[
								'a' => [
									'href'   => [],
									'target' => [],
									'rel'    => [],
									'class'  => [],
								],
							]
						),
						esc_url( wpforms_utm_link( 'https://wpforms.com/docs/how-to-choose-an-include-form-styling-setting/', 'settings-license', 'Form Styling Documentation' ) )
					),
					'type'      => 'select',
					'choicesjs' => true,
					'default'   => 1,
					'options'   => [
						1 => esc_html__( 'Base and form theme styling', 'wpforms-lite' ),
						2 => esc_html__( 'Base styling only', 'wpforms-lite' ),
						3 => esc_html__( 'No styling', 'wpforms-lite' ),
					],
				],
				'global-assets'   => [
					'id'     => 'global-assets',
					'name'   => esc_html__( 'Load Assets Globally', 'wpforms-lite' ),
					'desc'   => esc_html__( 'Load WPForms assets site-wide. Only check if your site is having compatibility issues or instructed to by support.', 'wpforms-lite' ),
					'type'   => 'toggle',
					'status' => true,
				],
				'gdpr-heading'    => [
					'id'       => 'GDPR',
					'content'  => '<h4>' . esc_html__( 'GDPR', 'wpforms-lite' ) . '</h4>',
					'type'     => 'content',
					'no_label' => true,
					'class'    => [ 'section-heading', 'no-desc' ],
				],
				'gdpr'            => [
					'id'     => 'gdpr',
					'name'   => esc_html__( 'GDPR Enhancements', 'wpforms-lite' ),
					'desc'   => sprintf(
						wp_kses( /* translators: %s - WPForms.com GDPR documentation URL. */
							__( 'Enable GDPR related features and enhancements. <a href="%s" target="_blank" rel="noopener noreferrer" class="wpforms-learn-more">Learn More</a>', 'wpforms-lite' ),
							[
								'a' => [
									'href'   => [],
									'target' => [],
									'rel'    => [],
									'class'  => [],
								],
							]
						),
						esc_url( wpforms_utm_link( 'https://wpforms.com/docs/how-to-create-gdpr-compliant-forms/', 'settings-license', 'GDPR Documentation' ) )
					),
					'type'   => 'toggle',
					'status' => true,
				],
			],
			// Validation messages settings tab.
			'validation'   => [
				'validation-heading'              => [
					'id'       => 'validation-heading',
					'content'  => sprintf( /* translators: %s - WPForms.com smart tags documentation URL. */
						esc_html__( '%1$s These messages are displayed to the users as they fill out a form in real-time. Messages can include plain text and/or %2$sSmart Tags%3$s.', 'wpforms-lite' ),
						'<h4>' . esc_html__( 'Validation Messages', 'wpforms-lite' )
						. '</h4><p>',
						'<a href="' . esc_url( wpforms_utm_link( 'https://wpforms.com/docs/how-to-use-smart-tags-in-wpforms/#smart-tags', 'Settings - Validation', 'Smart Tag Documentation' ) ) . '" target="_blank" rel="noopener noreferrer">',
						'</a>'
					),
					'type'     => 'content',
					'no_label' => true,
					'class'    => [ 'section-heading' ],
				],
				'validation-required'             => [
					'id'      => 'validation-required',
					'name'    => esc_html__( 'Required', 'wpforms-lite' ),
					'type'    => 'text',
					'default' => esc_html__( 'This field is required.', 'wpforms-lite' ),
				],
				'validation-email'                => [
					'id'      => 'validation-email',
					'name'    => esc_html__( 'Email', 'wpforms-lite' ),
					'type'    => 'text',
					'default' => esc_html__( 'Please enter a valid email address.', 'wpforms-lite' ),
				],
				'validation-email-suggestion'     => [
					'id'      => 'validation-email-suggestion',
					'name'    => esc_html__( 'Email Suggestion', 'wpforms-lite' ),
					'type'    => 'text',
					'default' => sprintf( /* translators: %s - suggested email address. */
						esc_html__( 'Did you mean %s?', 'wpforms-lite' ),
						'{suggestion}'
					),
				],
				'validation-email-restricted'     => [
					'id'      => 'validation-email-restricted',
					'name'    => esc_html__( 'Email Restricted', 'wpforms-lite' ),
					'type'    => 'text',
					'default' => esc_html__( 'This email address is not allowed.', 'wpforms-lite' ),
				],
				'validation-number'               => [
					'id'      => 'validation-number',
					'name'    => esc_html__( 'Number', 'wpforms-lite' ),
					'type'    => 'text',
					'default' => esc_html__( 'Please enter a valid number.', 'wpforms-lite' ),
				],
				'validation-number-positive'      => [
					'id'      => 'validation-number-positive',
					'name'    => esc_html__( 'Number Positive', 'wpforms-lite' ),
					'type'    => 'text',
					'default' => esc_html__( 'Please enter a valid positive number.', 'wpforms-lite' ),
				],
				'validation-minimum-price'        => [
					'id'      => 'validation-minimum-price',
					'name'    => esc_html__( 'Minimum Price', 'wpforms-lite' ),
					'type'    => 'text',
					'default' => esc_html__( 'Amount entered is less than the required minimum.', 'wpforms-lite' ),
				],
				'validation-confirm'              => [
					'id'      => 'validation-confirm',
					'name'    => esc_html__( 'Confirm Value', 'wpforms-lite' ),
					'type'    => 'text',
					'default' => esc_html__( 'Field values do not match.', 'wpforms-lite' ),
				],
				'validation-inputmask-incomplete' => [
					'id'      => 'validation-inputmask-incomplete',
					'name'    => esc_html__( 'Input Mask Incomplete', 'wpforms-lite' ),
					'type'    => 'text',
					'default' => esc_html__( 'Please fill out the field in required format.', 'wpforms-lite' ),
				],
				'validation-check-limit'          => [
					'id'      => 'validation-check-limit',
					'name'    => esc_html__( 'Checkbox Selection Limit', 'wpforms-lite' ),
					'type'    => 'text',
					'default' => esc_html__( 'You have exceeded the number of allowed selections: {#}.', 'wpforms-lite' ),
				],
				'validation-character-limit'      => [
					'id'      => 'validation-character-limit',
					'name'    => esc_html__( 'Character Limit', 'wpforms-lite' ),
					'type'    => 'text',
					'default' => sprintf( /* translators: %1$s - characters limit, %2$s - number of characters left. */
						esc_html__( 'Limit is %1$s characters. Characters remaining: %2$s.', 'wpforms-lite' ),
						'{limit}',
						'{remaining}'
					),
				],
				'validation-word-limit'           => [
					'id'      => 'validation-word-limit',
					'name'    => esc_html__( 'Word Limit', 'wpforms-lite' ),
					'type'    => 'text',
					'default' => sprintf( /* translators: %1$s - words limit, %2$s - number of words left. */
						esc_html__( 'Limit is %1$s words. Words remaining: %2$s.', 'wpforms-lite' ),
						'{limit}',
						'{remaining}'
					),
				],
				'validation-requiredpayment'      => [
					'id'      => 'validation-requiredpayment',
					'name'    => esc_html__( 'Payment Required', 'wpforms-lite' ),
					'type'    => 'text',
					'default' => esc_html__( 'Payment is required.', 'wpforms-lite' ),
				],
				'validation-creditcard'           => [
					'id'      => 'validation-creditcard',
					'name'    => esc_html__( 'Credit Card', 'wpforms-lite' ),
					'type'    => 'text',
					'default' => esc_html__( 'Please enter a valid credit card number.', 'wpforms-lite' ),
				],
				'validation-min'                  => [
					'id'      => 'validation-min',
					'name'    => esc_html__( 'Minimum Value', 'wpforms-lite' ),
					'type'    => 'text',
					'default' => sprintf( /* translators: %s - value to compare with. */
						esc_html__( 'Please enter a value greater than or equal to %s.', 'wpforms-lite' ),
						'{value}'
					),
				],
				'validation-max'                  => [
					'id'      => 'validation-max',
					'name'    => esc_html__( 'Maximum Value', 'wpforms-lite' ),
					'type'    => 'text',
					'default' => sprintf( /* translators: %s - value to compare with. */
						esc_html__( 'Please enter a value less than or equal to %s.', 'wpforms-lite' ),
						'{value}'
					),
				],
			],
			// Provider integrations settings tab.
			'integrations' => [
				'integrations-heading'   => [
					'id'       => 'integrations-heading',
					'content'  => '<h4>' . esc_html__( 'Integrations', 'wpforms-lite' ) . '</h4><p>' . esc_html__( 'Manage integrations with popular providers such as Constant Contact, Mailchimp, Zapier, and more.', 'wpforms-lite' ) . '</p>',
					'type'     => 'content',
					'no_label' => true,
					'class'    => [ 'section-heading' ],
				],
				'integrations-providers' => [
					'id'      => 'integrations-providers',
					'content' => '<h4>' . esc_html__( 'Integrations', 'wpforms-lite' ) . '</h4><p>' . esc_html__( 'Manage integrations with popular providers such as Constant Contact, Mailchimp, Zapier, and more.', 'wpforms-lite' ) . '</p>',
					'type'    => 'providers',
					'wrap'    => 'none',
				],
			],
			// Misc. settings tab.
			'misc'         => [
				'misc-heading'       => [
					'id'       => 'misc-heading',
					'content'  => '<h4>' . esc_html__( 'Miscellaneous', 'wpforms-lite' ) . '</h4>',
					'type'     => 'content',
					'no_label' => true,
					'class'    => [ 'section-heading', 'no-desc' ],
				],
				'delete-spam-entries' => [
					'id'        => 'delete-spam-entries',
					'name'      => esc_html__( 'Delete Spam Entries', 'wpforms-lite' ),
					'desc'      => esc_html__( 'Choose the frequency spam entries are automatically deleted.', 'wpforms-lite' ),
					'type'      => 'select',
					'default'   => 90,
					'is_hidden' => ! $this->show_spam_entries_setting(),
					'options'   => [
						7  => esc_html__( '7 Days', 'wpforms-lite' ),
						15 => esc_html__( '15 Days', 'wpforms-lite' ),
						30 => esc_html__( '30 Days', 'wpforms-lite' ),
						90 => esc_html__( '90 Days', 'wpforms-lite' ),
					],
				],
				'hide-announcements' => [
					'id'     => 'hide-announcements',
					'name'   => esc_html__( 'Hide Announcements', 'wpforms-lite' ),
					'desc'   => esc_html__( 'Hide plugin announcements and update details.', 'wpforms-lite' ),
					'type'   => 'toggle',
					'status' => true,
				],
				'hide-admin-bar'     => [
					'id'     => 'hide-admin-bar',
					'name'   => esc_html__( 'Hide Admin Bar Menu', 'wpforms-lite' ),
					'desc'   => esc_html__( 'Hide the WPForms admin bar menu.', 'wpforms-lite' ),
					'type'   => 'toggle',
					'status' => true,
				],
				'uninstall-data'     => [
					'id'     => 'uninstall-data',
					'name'   => esc_html__( 'Uninstall WPForms', 'wpforms-lite' ),
					'desc'   => $this->get_uninstall_desc(),
					'type'   => 'toggle',
					'status' => true,
				],
			],
		];

		$defaults = apply_filters( 'wpforms_settings_defaults', $defaults );

		// Take care of invalid views.
		if ( ! empty( $view ) && ! array_key_exists( $view, $defaults ) ) {
			$this->view = key( $defaults );

			return reset( $defaults );
		}

		return empty( $view ) ? $defaults : $defaults[ $view ];
	}

	/**
	 * Get uninstall description.
	 *
	 * @since 1.8.4
	 *
	 * @return string
	 */
	private function get_uninstall_desc() {

		$desc    = esc_html__( 'Remove ALL WPForms data upon plugin deletion.', 'wpforms-lite' );
		$warning = esc_html__( 'All forms and settings will be unrecoverable.', 'wpforms-lite' );

		if ( wpforms()->is_pro() ) {
			$desc    = esc_html__( 'Remove ALL WPForms data upon plugin deletion.', 'wpforms-lite' );
			$warning = esc_html__( 'All forms, entries, and uploaded files will be unrecoverable.', 'wpforms-lite' );
		}

		return sprintf( '%s <span class="wpforms-settings-warning">%s</span>', $desc, $warning );
	}

	/**
	 * Return array containing markup for all the appropriate settings fields.
	 *
	 * @since 1.3.9
	 *
	 * @param string $view View slug.
	 *
	 * @return array
	 */
	public function get_settings_fields( $view = '' ) {

		$fields   = [];
		$settings = $this->get_registered_settings( $view );

		foreach ( $settings as $id => $args ) {

			$fields[ $id ] = wpforms_settings_output_field( $args );
		}

		return apply_filters( 'wpforms_settings_fields', $fields, $view );
	}

	/**
	 * Build the output for the plugin settings page.
	 *
	 * @since 1.0.0
	 */
	public function output() {

		$tabs   = $this->get_tabs();
		$fields = $this->get_settings_fields( $this->view );
		?>

		<div id="wpforms-settings" class="wrap wpforms-admin-wrap">

			<?php $this->tabs(); ?>

			<h1 class="wpforms-h1-placeholder"></h1>

			<?php
			if ( wpforms()->is_pro() && class_exists( 'WPForms_License', false ) ) {
				wpforms()->obj( 'license' )->notices( true );
			}
			?>

			<div class="wpforms-admin-content wpforms-admin-settings wpforms-admin-content-<?php echo esc_attr( $this->view ); ?> wpforms-admin-settings-<?php echo esc_attr( $this->view ); ?>">

				<?php
				// Some tabs rely on AJAX and do not contain a form, such as Integrations.
				if ( ! empty( $tabs[ $this->view ]['form'] ) ) :
				?>
				<form class="wpforms-admin-settings-form" method="post">
					<input type="hidden" name="action" value="update-settings">
					<input type="hidden" name="view" value="<?php echo esc_attr( $this->view ); ?>">
					<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpforms-settings-nonce' ) ); ?>">
					<?php endif; ?>

					<?php do_action( 'wpforms_admin_settings_before', $this->view, $fields ); ?>

					<?php
					foreach ( $fields as $field ) {
						echo $field; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					?>

					<?php if ( ! empty( $tabs[ $this->view ]['submit'] ) ) : ?>
						<p class="submit">
							<button type="submit" class="wpforms-btn wpforms-btn-md wpforms-btn-orange" name="wpforms-settings-submit">
								<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $tabs[ $this->view ]['submit'];
								?>
							</button>
						</p>
					<?php endif; ?>

					<?php do_action( 'wpforms_admin_settings_after', $this->view, $fields ); ?>

					<?php if ( ! empty( $tabs[ $this->view ]['form'] ) ) : ?>
				</form>
			<?php endif; ?>

			</div>

		</div>

		<?php
	}

	/**
	 * Monitor that all custom tables exist and recreate if missing.
	 * This logic works on Settings > General page only.
	 *
	 * @since 1.6.2
	 */
	public function monitor_custom_tables() {

		// Proceed on Settings plugin admin area page only.
		if ( $this->view !== 'general' ) {
			return;
		}

		/*
		 * Tasks Meta table.
		 */
		$meta = new \WPForms\Tasks\Meta();

		if ( $meta->table_exists() ) {
			return;
		}

		$meta->create_table();
	}

	/**
	 * Validate radio and select fields.
	 *
	 * @since 1.7.5.5
	 *
	 * @param array $field      Field.
	 * @param mixed $value      Value.
	 * @param mixed $value_prev Previous value.
	 *
	 * @return mixed
	 */
	private function validate_field_with_options( $field, $value, $value_prev ) {

		$value = sanitize_text_field( $value );

		if ( isset( $field['options'] ) && array_key_exists( $value, $field['options'] ) ) {
			return $value;
		}

		return isset( $field['default'] ) ? $field['default'] : $value_prev;
	}

	/**
	 * Check if spam entries setting should be shown.
	 *
	 * Show setting only if WPFORMS_DELETE_SPAM_ENTRIES is not defined, and the plugin is Pro.
	 *
	 * @since 1.9.1
	 *
	 * @return bool
	 */
	private function show_spam_entries_setting(): bool {

		return ! defined( 'WPFORMS_DELETE_SPAM_ENTRIES' ) && wpforms()->is_pro();
	}
}

new WPForms_Settings();
