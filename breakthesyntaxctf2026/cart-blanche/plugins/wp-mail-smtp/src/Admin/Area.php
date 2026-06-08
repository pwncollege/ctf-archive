<?php

namespace WPMailSMTP\Admin;

use WPMailSMTP\Options;
use WPMailSMTP\WP;

/**
 * Class Area registers and process all wp-admin display functionality.
 *
 * @since 1.0.0
 */
class Area {

	/**
	 * Slug of the admin area page.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const SLUG = 'wp-mail-smtp';

	/**
	 * Admin page unique hook.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $hook;

	/**
	 * List of admin area pages.
	 *
	 * @since 1.0.0
	 *
	 * @var PageAbstract[]
	 */
	private $pages;

	/**
	 * List of official registered pages.
	 *
	 * @since 1.5.0
	 *
	 * @var array
	 */
	public static $pages_registered = [ 'general', 'logs', 'about', 'tools', 'reports', 'alerts' ];

	/**
	 * Area constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {}

	/**
	 * Assign all hooks to proper places.
	 *
	 * @since 1.0.0
	 * @since 4.0.0 Changed visibility to public.
	 */
	public function hooks() {

		// Add the Settings link to a plugin on Plugins page.
		add_filter( 'plugin_action_links_' . plugin_basename( WPMS_PLUGIN_FILE ), [ $this, 'add_plugin_action_link' ], 10, 1 );

		// Add the options page.
		add_action( 'admin_menu', [ $this, 'add_admin_options_page' ] );

		// Add inline styles for "Upgrade to Pro" left sidebar menu item.
		add_action( 'admin_head', [ $this, 'style_upgrade_pro_link' ] );

		// Add WPMS network-wide setting page for product education.
		add_action( 'network_admin_menu', [ $this, 'add_wpms_network_wide_setting_product_education_page' ] );

		// Register on load Email Log admin menu hook.
		add_action( 'load-wp-mail-smtp_page_wp-mail-smtp-logs', [ $this, 'maybe_redirect_email_log_menu_to_email_log_settings_tab' ] );

		// Admin footer text.
		add_filter( 'admin_footer_text', [ $this, 'get_admin_footer' ], 1, 2 );

		// Enqueue admin area scripts and styles.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );

		// Process the admin page forms actions.
		add_action( 'admin_init', [ $this, 'process_actions' ] );

		// Display custom notices based on the error/success codes.
		add_action( 'admin_init', [ $this, 'display_custom_auth_notices' ] );

		// Display notice instructing the user to complete plugin setup.
		add_action( 'admin_init', [ $this, 'display_setup_notice' ] );

		// Display notice explaining removal of "Email Test" tab.
		add_action( 'admin_init', [ $this, 'display_email_test_tab_removal_notice' ] );

		// Outputs the plugin admin header.
		add_action( 'in_admin_header', [ $this, 'display_admin_header' ], 100 );

		// Outputs the plugin promotional admin footer.
		add_action( 'in_admin_footer', [ $this, 'display_admin_footer' ] );

		// Outputs the plugin version in the admin footer.
		add_filter( 'update_footer', [ $this, 'display_update_footer' ], PHP_INT_MAX );

		// Hide all unrelated to the plugin notices on the plugin admin pages.
		add_action( 'admin_print_scripts', [ $this, 'hide_unrelated_notices' ] );

		// Process all AJAX requests.
		add_action( 'wp_ajax_wp_mail_smtp_ajax', [ $this, 'process_ajax' ] );

		// Init parent admin pages.
		if ( WP::in_wp_admin() || WP::is_doing_self_ajax() ) {
			add_action( 'init', [ $this, 'get_parent_pages' ] );
		}

		( new Review() )->hooks();
		( new Education() )->hooks();
		( new SetupWizard() )->hooks();
		( new FlyoutMenu() )->hooks();
	}

	/**
	 * Display custom notices based on the error/success codes.
	 *
	 * @since 1.0.0
	 */
	public function display_custom_auth_notices() {

		$error   = isset( $_GET['error'] ) ? sanitize_key( $_GET['error'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$success = isset( $_GET['success'] ) ? sanitize_key( $_GET['success'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( empty( $error ) && empty( $success ) ) {
			return;
		}

		if ( ! current_user_can( wp_mail_smtp()->get_capability_manage_options() ) ) {
			return;
		}

		switch ( $error ) {
			case 'oauth_invalid_state':
				WP::add_admin_notice(
					esc_html__( 'There was an error while processing the authentication request. The state key is invalid. Please try again.', 'wp-mail-smtp' ),
					WP::ADMIN_NOTICE_ERROR
				);
				break;

			case 'google_invalid_nonce':
				WP::add_admin_notice(
					esc_html__( 'There was an error while processing the authentication request. The nonce is invalid. Please try again.', 'wp-mail-smtp' ),
					WP::ADMIN_NOTICE_ERROR
				);
				break;

			case 'google_access_denied':
				WP::add_admin_notice( /* translators: %s - error code, returned by Google API. */
					sprintf( esc_html__( 'There was an error while processing the authentication request: %s. Please try again.', 'wp-mail-smtp' ), '<code>' . $error . '</code>' ),
					WP::ADMIN_NOTICE_ERROR
				);
				break;

			case 'google_no_code_scope':
				WP::add_admin_notice(
					esc_html__( 'There was an error while processing the authentication request. Please try again.', 'wp-mail-smtp' ),
					WP::ADMIN_NOTICE_ERROR
				);
				break;

			case 'google_no_clients':
				WP::add_admin_notice(
					esc_html__( 'There was an error while processing the authentication request. Please make sure that you have Client ID and Client Secret both valid and saved.', 'wp-mail-smtp' ),
					WP::ADMIN_NOTICE_ERROR
				);
				break;

			case 'google_unsuccessful_oauth':
				WP::add_admin_notice(
					esc_html__( 'There was an error while processing the authentication request.', 'wp-mail-smtp' ),
					WP::ADMIN_NOTICE_ERROR
				);
				break;
		}

		switch ( $success ) {
			case 'google_site_linked':
				WP::add_admin_notice(
					esc_html__( 'You have successfully linked the current site with your Google API project. Now you can start sending emails through Gmail.', 'wp-mail-smtp' ),
					WP::ADMIN_NOTICE_SUCCESS
				);
				break;
		}
	}

	/**
	 * Display notice instructing the user to complete plugin setup.
	 *
	 * @since 1.3.0
	 */
	public function display_setup_notice() {

		// Bail if we're not on a plugin page.
		if ( ! $this->is_admin_page( 'general' ) ) {
			return;
		}

		$default_options = wp_json_encode( Options::get_defaults() );
		$current_options = wp_json_encode( Options::init()->get_all() );

		// Check if the current settings are the same as the default settings.
		if ( $current_options !== $default_options ) {
			return;
		}

		// Display notice informing user further action is needed.
		WP::add_admin_notice(
			sprintf(
				wp_kses( /* translators: %s - Mailer anchor link. */
					__( 'Thanks for using WP Mail SMTP! To complete the plugin setup and start sending emails, <strong>please select and configure your <a href="%s">Mailer</a></strong>.', 'wp-mail-smtp' ),
					[
						'a'      => [
							'href' => [],
						],
						'strong' => [],
					]
				),
				wp_mail_smtp()->get_admin()->get_admin_page_url( self::SLUG . '#wp-mail-smtp-setting-row-mailer' )
			),
			WP::ADMIN_NOTICE_INFO
		);
	}

	/**
	 * Display notice explaining removal of "Email Test" tab.
	 *
	 * @since 3.9.0
	 */
	public function display_email_test_tab_removal_notice() {

		// Bail if we aren't on a "Settings" page.
		if ( ! $this->is_admin_page( self::SLUG ) ) {
			return;
		}

		// Bail if the notice has been dismissed.
		if ( metadata_exists( 'user', get_current_user_id(), 'wp_mail_smtp_email_test_tab_removal_notice_dismissed' ) ) {
			return;
		}

		/*
		 * Don't display the notice if the user installed a plugin with a new "Email Test"
		 * location (starting from v3.9.0) and is not aware of the old one. Also, don't display
		 * the notice if the `wp_mail_smtp_initial_version` option is not set (it can happen if
		 * the plugin was activated network wise in the multisite installation and plugin
		 * activation hook was not performed on the subsite level).
		 */
		if ( version_compare( get_option( 'wp_mail_smtp_initial_version', '3.9.0' ), '3.9.0', '>=' ) ) {
			return;
		}

		WP::add_admin_notice(
			sprintf(
				wp_kses(
					/* translators: %s: Tools page URL. */
					__( 'The Email Test tab was moved to <a href="%s">WP Mail SMTP > Tools</a>.', 'wp-mail-smtp' ),
					[ 'a' => [ 'href' => [] ] ]
				),
				$this->get_admin_page_url( self::SLUG . '-tools' )
			),
			implode( ' ', [ WP::ADMIN_NOTICE_INFO, 'email_test_tab_removal_notice' ] )
		);
	}

	/**
	 * Get menu item position.
	 *
	 * @since 2.8.0
	 *
	 * @return int
	 */
	public function get_menu_item_position() {

		/**
		 * Filters menu item position.
		 *
		 * @since 2.8.0
		 *
		 * @param int $position Position number.
		 */
		return apply_filters( 'wp_mail_smtp_admin_area_get_menu_item_position', 98 );
	}

	/**
	 * Add admin area menu item.
	 *
	 * @since 1.0.0
	 * @since 1.5.0 Moved the menu to the top level. Added several more pages.
	 */
	public function add_admin_options_page() {

		// Options pages access capability.
		$access_capability = wp_mail_smtp()->get_capability_manage_options();

		$this->hook = add_menu_page(
			esc_html__( 'WP Mail SMTP', 'wp-mail-smtp' ),
			esc_html__( 'WP Mail SMTP', 'wp-mail-smtp' ),
			$access_capability,
			self::SLUG,
			[ $this, 'display' ],
			'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGZpbGw9IiM5ZWEzYTgiIHdpZHRoPSI2NCIgaGVpZ2h0PSI2NCIgdmlld0JveD0iMCAwIDQzIDM0Ij48cGF0aCBkPSJNMC4wMDcsMy41ODVWMjAuNDIxcTAsMy41ODYsMy43NTEsMy41ODVMMjAsMjRWMTlIMzBWMTQuMDE0bDAuOTkxLTFMMzQsMTNWMy41ODVRMzQsMCwzMC4yNDksMEgzLjc1OFEwLjAwNywwLC4wMDcsMy41ODVoMFpNMy41MjQsNi4xNTdhMS40OSwxLjQ5LDAsMCwxLS41MDgtMC45MzUsMS41ODEsMS41ODEsMCwwLDEsLjI3NC0xLjIwOCwxLjQ0OSwxLjQ0OSwwLDAsMSwxLjA5NC0uNjYzLDEuNzU2LDEuNzU2LDAsMCwxLDEuMjUuMzEybDExLjQwOSw3LjcxNkwyOC4zNzQsMy42NjNhMS45NiwxLjk2LDAsMCwxLDEuMjg5LS4zMTIsMS41NDYsMS41NDYsMCwwLDEsMS4wOTQuNjYzLDEuNCwxLjQsMCwwLDEsLjI3MywxLjIwOCwxLjY3LDEuNjcsMCwwLDEtLjU0Ny45MzVMMTcuMDQzLDE3LjIyNVoiLz48cGF0aCBkPSJNMjIsMjhIMzJsLTAuMDA5LDQuNjI0YTEuMTI2LDEuMTI2LDAsMCwwLDEuOTIyLjhsOC4yNS04LjIzNmExLjEyNiwxLjEyNiwwLDAsMCwwLTEuNTk0bC04LjI1LTguMjQxYTEuMTI2LDEuMTI2LDAsMCwwLTEuOTIyLjh2NC44NjZMMjIsMjF2N1oiLz48L3N2Zz4=',
			$this->get_menu_item_position()
		);

		add_submenu_page(
			self::SLUG,
			$this->get_current_tab_title() . ' &lsaquo; ' . \esc_html__( 'Settings', 'wp-mail-smtp' ),
			esc_html__( 'Settings', 'wp-mail-smtp' ),
			$access_capability,
			self::SLUG,
			[ $this, 'display' ]
		);

		add_submenu_page(
			self::SLUG,
			esc_html__( 'Email Log', 'wp-mail-smtp' ),
			esc_html__( 'Email Log', 'wp-mail-smtp' ),
			$this->get_logs_access_capability(),
			self::SLUG . '-logs',
			[ $this, 'display' ]
		);

		foreach ( $this->get_parent_pages() as $page ) {
			add_submenu_page(
				self::SLUG,
				esc_html( $page->get_title() ),
				esc_html( $page->get_label() ),
				$access_capability,
				self::SLUG . '-' . $page->get_slug(),
				[ $this, 'display' ]
			);
		}

		if ( ! wp_mail_smtp()->is_pro() ) {
			add_submenu_page(
				self::SLUG,
				esc_html__( 'Upgrade to Pro', 'wp-mail-smtp' ),
				esc_html__( 'Upgrade to Pro', 'wp-mail-smtp' ),
				$access_capability,
				// phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
				esc_url( wp_mail_smtp()->get_upgrade_link( [ 'medium' => 'admin-menu', 'content' => 'Upgrade to Pro' ] ) )
			);
		}
	}

	/**
	 * Add network admin settings page for the WPMS product education.
	 *
	 * @since 2.5.0
	 */
	public function add_wpms_network_wide_setting_product_education_page() {

		add_menu_page(
			esc_html__( 'WP Mail SMTP', 'wp-mail-smtp' ),
			esc_html__( 'WP Mail SMTP', 'wp-mail-smtp' ),
			wp_mail_smtp()->get_capability_manage_options(),
			self::SLUG,
			[ $this, 'display_network_product_education_page' ],
			'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGZpbGw9IiM5ZWEzYTgiIHdpZHRoPSI2NCIgaGVpZ2h0PSI2NCIgdmlld0JveD0iMCAwIDQzIDM0Ij48cGF0aCBkPSJNMC4wMDcsMy41ODVWMjAuNDIxcTAsMy41ODYsMy43NTEsMy41ODVMMjAsMjRWMTlIMzBWMTQuMDE0bDAuOTkxLTFMMzQsMTNWMy41ODVRMzQsMCwzMC4yNDksMEgzLjc1OFEwLjAwNywwLC4wMDcsMy41ODVoMFpNMy41MjQsNi4xNTdhMS40OSwxLjQ5LDAsMCwxLS41MDgtMC45MzUsMS41ODEsMS41ODEsMCwwLDEsLjI3NC0xLjIwOCwxLjQ0OSwxLjQ0OSwwLDAsMSwxLjA5NC0uNjYzLDEuNzU2LDEuNzU2LDAsMCwxLDEuMjUuMzEybDExLjQwOSw3LjcxNkwyOC4zNzQsMy42NjNhMS45NiwxLjk2LDAsMCwxLDEuMjg5LS4zMTIsMS41NDYsMS41NDYsMCwwLDEsMS4wOTQuNjYzLDEuNCwxLjQsMCwwLDEsLjI3MywxLjIwOCwxLjY3LDEuNjcsMCwwLDEtLjU0Ny45MzVMMTcuMDQzLDE3LjIyNVoiLz48cGF0aCBkPSJNMjIsMjhIMzJsLTAuMDA5LDQuNjI0YTEuMTI2LDEuMTI2LDAsMCwwLDEuOTIyLjhsOC4yNS04LjIzNmExLjEyNiwxLjEyNiwwLDAsMCwwLTEuNTk0bC04LjI1LTguMjQxYTEuMTI2LDEuMTI2LDAsMCwwLTEuOTIyLjh2NC44NjZMMjIsMjF2N1oiLz48L3N2Zz4=',
			$this->get_menu_item_position()
		);
	}

	/**
	 * HTML output for the network admin settings page (for the WPMS product education).
	 *
	 * @since 2.5.0
	 */
	public function display_network_product_education_page() {

		// Skip if not on multisite and not on network admin site.
		if ( ! is_multisite() || ! is_network_admin() ) {
			return;
		}

		?>

		<div class="wrap" id="wp-mail-smtp">
			<div class="wp-mail-smtp-page wp-mail-smtp-page-general wp-mail-smtp-page-nw-product-edu wp-mail-smtp-tab-settings">
				<div class="wp-mail-smtp-page-title">
					<a href="#" class="tab active">
						<?php esc_html_e( 'General', 'wp-mail-smtp' ); ?>
					</a>
				</div>

				<div class="wp-mail-smtp-page-content">
					<h1 class="screen-reader-text">
						<?php esc_html_e( 'General', 'wp-mail-smtp' ); ?>
					</h1>

					<?php do_action( 'wp_mail_smtp_admin_pages_before_content' ); ?>

					<!-- Multisite Section Title -->
					<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-content wp-mail-smtp-clear section-heading no-desc" id="wp-mail-smtp-setting-row-multisite-heading">
						<div class="wp-mail-smtp-setting-field">
							<h2><?php esc_html_e( 'Multisite', 'wp-mail-smtp' ); ?></h2>
							<img src="<?php echo esc_url( wp_mail_smtp()->assets_url . '/images/pro-badge.svg' ); ?>" class="badge" alt="<?php esc_attr_e( 'Pro+ badge icon', 'wp-mail-smtp' ); ?>">
						</div>
						<p>
							<?php esc_html_e( 'Simply enable network-wide settings and every site on your network will inherit the same SMTP settings. Save time and only configure your SMTP provider once.', 'wp-mail-smtp' ); ?>
						</p>
					</div>

					<!-- Network wide setting -->
					<div id="wp-mail-smtp-setting-row-multisite" class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-multisite wp-mail-smtp-clear">
						<div class="wp-mail-smtp-setting-label">
							<label for="wp-mail-smtp-setting-multisite-settings-control"><?php esc_html_e( 'Settings control', 'wp-mail-smtp' ); ?></label>
						</div>
						<div class="wp-mail-smtp-setting-field">
							<input name="wp-mail-smtp[general][nw_product_edu]" type="checkbox" value="true" id="wp-mail-smtp-setting-nw-product-edu" disabled>

							<label for="wp-mail-smtp-setting-nw-product-edu">
								<?php esc_html_e( 'Make the plugin settings global network-wide', 'wp-mail-smtp' ); ?>
							</label>

							<p class="desc">
								<?php esc_html_e( 'If disabled, each subsite of this multisite will have its own WP Mail SMTP settings page that has to be configured separately.', 'wp-mail-smtp' ); ?>
								<br>
								<?php esc_html_e( 'If enabled, these global settings will manage email sending for all subsites of this multisite.', 'wp-mail-smtp' ); ?>
							</p>
						</div>
					</div>

					<div class="wp-mail-smtp-setting-row-no-setting">
						<a href="<?php echo esc_url( wp_mail_smtp()->get_upgrade_link( [ 'medium' => 'network-settings', 'content' => '' ] ) ); // phpcs:ignore ?>" target="_blank" rel="noopener noreferrer" class="wp-mail-smtp-btn wp-mail-smtp-btn-lg wp-mail-smtp-btn-orange">
							<?php esc_html_e( 'Upgrade to WP Mail SMTP Pro', 'wp-mail-smtp' ); ?>
						</a>
					</div>

				</div>
			</div>
		</div>

		<?php
	}

	/**
	 * Redirect the "Email Log" WP menu link to the "Email Log" setting tab for lite version of the plugin.
	 *
	 * @since 2.1.0
	 */
	public function maybe_redirect_email_log_menu_to_email_log_settings_tab() {

		/**
		 * The Email Logs object to be used for loading the Email Log page.
		 *
		 * @var \WPMailSMTP\Admin\PageAbstract $logs
		 */
		$logs = $this->generate_display_logs_object();

		if ( $logs instanceof \WPMailSMTP\Admin\Pages\Logs ) {
			wp_safe_redirect( $logs->get_link() );
			exit;
		}
	}

	/**
	 * Enqueue admin area scripts and styles.
	 *
	 * @since 1.0.0
	 * @since 1.5.0 Added new assets for new pages.
	 * @since 1.7.0 Added jQuery Confirm library css/js files.
	 *
	 * @param string $hook Current hook.
	 */
	public function enqueue_assets( $hook ) {

		if ( strpos( $hook, self::SLUG ) === false ) {
			return;
		}

		// Set general body class.
		add_filter(
			'admin_body_class',
			function ( $classes ) {
				$classes .= ' wp-mail-smtp-admin-page-body';

				if ( wp_mail_smtp()->is_pro() ) {
					$classes .= ' wp-mail-smtp-pro';
				} else {
					$classes .= ' wp-mail-smtp-lite';
				}

				return $classes;
			}
		);

		wp_register_style(
			'wp-mail-smtp-admin-lity',
			wp_mail_smtp()->assets_url . '/css/vendor/lity.min.css',
			[],
			'2.4.1'
		);
		wp_register_script(
			'wp-mail-smtp-admin-lity',
			wp_mail_smtp()->assets_url . '/js/vendor/lity.min.js',
			[],
			'2.4.1'
		);

		// General styles and js.
		wp_enqueue_style(
			'wp-mail-smtp-admin',
			wp_mail_smtp()->assets_url . '/css/smtp-admin.min.css',
			false,
			WPMS_PLUGIN_VER
		);

		wp_enqueue_script( 'underscore' );

		wp_enqueue_script(
			'wp-mail-smtp-admin',
			wp_mail_smtp()->assets_url . '/js/smtp-admin' . WP::asset_min() . '.js',
			[ 'jquery', 'underscore' ],
			WPMS_PLUGIN_VER,
			false
		);

		$script_data = [
			'text_provider_remove'    => esc_html__( 'Are you sure you want to reset the current provider connection? You will need to immediately create a new one to be able to send emails.', 'wp-mail-smtp' ),
			'text_settings_not_saved' => esc_html__( 'Changes that you made to the settings are not saved!', 'wp-mail-smtp' ),
			'default_mailer_notice'   => [
				'title'         => esc_html__( 'Heads up!', 'wp-mail-smtp' ),
				'content'       => wp_kses(
					__( '<p>The Default (PHP) mailer is currently selected, but is not recommended because in most cases it does not resolve email delivery issues.</p><p>Please consider selecting and configuring one of the other mailers.</p>', 'wp-mail-smtp' ),
					[ 'p' => [] ]
				),
				'save_button'   => esc_html__( 'Save Settings', 'wp-mail-smtp' ),
				'cancel_button' => esc_html__( 'Cancel', 'wp-mail-smtp' ),
				'icon_alt'      => esc_html__( 'Warning icon', 'wp-mail-smtp' ),
			],
			'plugin_url'              => wp_mail_smtp()->plugin_url,
			'education'               => [
				'upgrade_icon_lock' => '<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="lock" class="svg-inline--fa fa-lock fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M400 224h-24v-72C376 68.2 307.8 0 224 0S72 68.2 72 152v72H48c-26.5 0-48 21.5-48 48v192c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V272c0-26.5-21.5-48-48-48zm-104 0H152v-72c0-39.7 32.3-72 72-72s72 32.3 72 72v72z"></path></svg>',
				'upgrade_title'     => esc_html__( '%name% is a PRO Feature', 'wp-mail-smtp' ),
				'upgrade_content'   => esc_html__( 'We\'re sorry, the %name% mailer is not available on your plan. Please upgrade to the PRO plan to unlock all these awesome features.', 'wp-mail-smtp' ),
				'upgrade_button'    => esc_html__( 'Upgrade to Pro', 'wp-mail-smtp' ),
				'upgrade_url'       => add_query_arg( 'discount', 'SMTPLITEUPGRADE', wp_mail_smtp()->get_upgrade_link( '' ) ),
				'upgrade_bonus'     => '<p>' .
											sprintf(
												wp_kses( /* Translators: %s - discount value $50. */
													__( '<strong>Bonus:</strong> WP Mail SMTP users get <span>%s off</span> regular price,<br>applied at checkout.', 'wp-mail-smtp' ),
													[
														'strong' => [],
														'span'   => [],
														'br'     => [],
													]
												),
												'$50'
											)
											. '</p>',
				'upgrade_doc'       => sprintf(
					'<a href="%1$s" target="_blank" rel="noopener noreferrer" class="already-purchased">%2$s</a>',
					// phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
					esc_url( wp_mail_smtp()->get_utm_url( 'https://wpmailsmtp.com/docs/how-to-upgrade-wp-mail-smtp-to-pro-version/', [ 'medium' => 'plugin-settings', 'content' => 'Pro Mailer Popup - Already purchased' ] ) ),
					esc_html__( 'Already purchased?', 'wp-mail-smtp' )
				),
				'gmail'             => [
					'one_click_setup_upgrade_title'   => wp_kses( __( 'One-Click Setup for Google Mailer <br> is a Pro Feature', 'wp-mail-smtp' ), [ 'br' => [] ] ),
					'one_click_setup_upgrade_content' => esc_html__( 'We\'re sorry, One-Click Setup for Google Mailer is not available on your plan. Please upgrade to the Pro plan to unlock all these awesome features.', 'wp-mail-smtp' ),
				],
				'rate_limit'        => [
					'upgrade_title'   => wp_kses( __( 'Email Rate Limiting <br> is a Pro Feature', 'wp-mail-smtp' ), [ 'br' => [] ] ),
					'upgrade_content' => esc_html__( 'We\'re sorry, Email Rate Limiting is not available on your plan. Please upgrade to the Pro plan to unlock all these awesome features.', 'wp-mail-smtp' ),
				],
			],
			'all_mailers_supports'    => wp_mail_smtp()->get_providers()->get_supports_all(),
			'nonce'                   => wp_create_nonce( 'wp-mail-smtp-admin' ),
			'is_network_admin'        => is_network_admin(),
			'ajax_url'                => admin_url( 'admin-ajax.php' ),
			'lang_code'               => sanitize_key( WP::get_language_code() ),
			'sendlayer'               => [
				'connect_nonce'   => wp_create_nonce( 'wp-mail-smtp-sendlayer-connect' ),
				'return_url'      => $this->get_admin_page_url(),
				'error_title'     => esc_html__( 'Error', 'wp-mail-smtp' ),
				'error_text'      => esc_html__( 'An error occurred. Please try again.', 'wp-mail-smtp' ),
				'server_error'    => esc_html__( 'A server error occurred. Please try again.', 'wp-mail-smtp' ),
				'connecting_text' => esc_html__( 'Connecting...', 'wp-mail-smtp' ),
			],
		];

		/**
		 * Filters plugin script data.
		 *
		 * @since 2.9.0
		 *
		 * @param array  $script_data Data.
		 * @param string $hook        Current hook.
		 */
		$script_data = apply_filters( 'wp_mail_smtp_admin_area_enqueue_assets_scripts_data', $script_data, $hook );

		wp_localize_script( 'wp-mail-smtp-admin', 'wp_mail_smtp', $script_data );

		/*
		 * jQuery Confirm library v3.3.4.
		 */
		wp_enqueue_style(
			'wp-mail-smtp-admin-jconfirm',
			wp_mail_smtp()->assets_url . '/css/vendor/jquery-confirm.min.css',
			[ 'wp-mail-smtp-admin' ],
			'3.3.4'
		);
		wp_enqueue_script(
			'wp-mail-smtp-admin-jconfirm',
			wp_mail_smtp()->assets_url . '/js/vendor/jquery-confirm.min.js',
			[ 'wp-mail-smtp-admin' ],
			'3.3.4',
			false
		);

		/*
		 * Logs page.
		 */
		if ( $this->is_admin_page( 'logs' ) ) {
			wp_enqueue_style(
				'wp-mail-smtp-admin-logs',
				apply_filters( 'wp_mail_smtp_admin_enqueue_assets_logs_css', '' ),
				[ 'wp-mail-smtp-admin' ],
				WPMS_PLUGIN_VER
			);

			wp_enqueue_script(
				'wp-mail-smtp-admin-logs',
				apply_filters( 'wp_mail_smtp_admin_enqueue_assets_logs_js', '' ),
				[ 'wp-mail-smtp-admin' ],
				WPMS_PLUGIN_VER,
				false
			);
		}

		/*
		 * About page.
		 */
		if ( $this->is_admin_page( 'about' ) ) {

			wp_enqueue_style(
				'wp-mail-smtp-admin-about',
				wp_mail_smtp()->assets_url . '/css/smtp-about.min.css',
				[ 'wp-mail-smtp-admin' ],
				WPMS_PLUGIN_VER
			);

			wp_enqueue_script(
				'wp-mail-smtp-admin-about',
				wp_mail_smtp()->assets_url . '/js/smtp-about' . WP::asset_min() . '.js',
				[ 'wp-mail-smtp-admin' ],
				'0.7.2',
				false
			);

			$settings = [
				'ajax_url'                    => admin_url( 'admin-ajax.php' ),
				'nonce'                       => wp_create_nonce( 'wp-mail-smtp-admin' ),
				// Strings.
				'plugin_activate'             => esc_html__( 'Activate', 'wp-mail-smtp' ),
				'plugin_activated'            => esc_html__( 'Activated', 'wp-mail-smtp' ),
				'plugin_active'               => esc_html__( 'Active', 'wp-mail-smtp' ),
				'plugin_inactive'             => esc_html__( 'Inactive', 'wp-mail-smtp' ),
				'plugin_processing'           => esc_html__( 'Processing...', 'wp-mail-smtp' ),
				'plugin_visit'                => esc_html__( 'Visit Site', 'wp-mail-smtp' ),
				'plugin_install_error'        => esc_html__( 'Could not install a plugin. Please download from WordPress.org and install manually.', 'wp-mail-smtp' ),
				'plugin_install_activate_btn' => esc_html__( 'Install and Activate', 'wp-mail-smtp' ),
				'plugin_activate_btn'         => esc_html__( 'Activate', 'wp-mail-smtp' ),
				'plugin_download_btn'         => esc_html__( 'Download', 'wp-mail-smtp' ),
			];

			wp_localize_script(
				'wp-mail-smtp-admin-about',
				'wp_mail_smtp_about',
				$settings
			);

			wp_enqueue_script(
				'wp-mail-smtp-admin-about-matchheight',
				wp_mail_smtp()->assets_url . '/js/vendor/jquery.matchHeight.min.js',
				[ 'wp-mail-smtp-admin' ],
				'0.7.2',
				false
			);
		}

		if (
			$this->is_admin_page( 'general' ) &&
			! wp_mail_smtp()->is_pro() &&
			! get_user_meta( get_current_user_id(), 'wp_mail_smtp_pro_banner_dismissed', true )
		) {
			wp_enqueue_style( 'wp-mail-smtp-admin-lity' );
			wp_enqueue_script( 'wp-mail-smtp-admin-lity' );
		}

		/**
		 * Fires after enqueue plugin assets.
		 *
		 * @since 1.5.0
		 *
		 * @param string $hook Current hook.
		 */
		do_action( 'wp_mail_smtp_admin_area_enqueue_assets', $hook );
	}

	/**
	 * Outputs the plugin admin header.
	 *
	 * @since 1.0.0
	 */
	public function display_admin_header() {

		// Bail if we're not on a plugin page.
		if ( ! $this->is_admin_page() ) {
			return;
		}

		do_action( 'wp_mail_smtp_admin_header_before' );
		?>

		<div id="wp-mail-smtp-header-temp"></div>
		<div id="wp-mail-smtp-header">
			<!--suppress HtmlUnknownTarget -->
			<img class="wp-mail-smtp-header-logo" src="<?php echo esc_url( wp_mail_smtp()->assets_url ); ?>/images/logo<?php echo wp_mail_smtp()->is_white_labeled() ? '-whitelabel' : ''; ?>.svg" alt="WP Mail SMTP"/>
		</div>

		<?php
	}

	/**
	 * Display a text to ask users to review the plugin on WP.org.
	 *
	 * @since 1.0.0
	 *
	 * @param string $text The default text to display in admin plugin page footer.
	 *
	 * @return string
	 */
	public function get_admin_footer( $text ) {

		if ( $this->is_admin_page() ) {
			$url = 'https://wordpress.org/support/plugin/wp-mail-smtp/reviews/#new-post';

			$text = sprintf(
				wp_kses(
					/* translators: %1$s - WP.org link; %2$s - same WP.org link. */
					__( 'Please rate <strong>WP Mail SMTP</strong> <a href="%1$s" target="_blank" rel="noopener noreferrer">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on <a href="%2$s" target="_blank" rel="noopener noreferrer">WordPress.org</a> to help us spread the word. Thank you from the WP Mail SMTP team!', 'wp-mail-smtp' ),
					array(
						'strong' => array(),
						'a'      => array(
							'href'   => array(),
							'target' => array(),
							'rel'    => array(),
						),
					)
				),
				$url,
				$url
			);
		}

		return $text;
	}

	/**
	 * Display content of the admin area page.
	 *
	 * @since 1.0.0
	 * @since 1.5.0 Rewrite to distinguish between General tabs and separate pages.
	 */
	public function display() { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded

		// Bail if we're not on a plugin page.
		if ( ! $this->is_admin_page() ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = ! empty( $_GET['page'] ) ? \sanitize_key( $_GET['page'] ) : '';
		?>

		<div class="wrap" id="wp-mail-smtp">

				<?php
				switch ( $page ) {
					case self::SLUG:
						?>

						<div class="wp-mail-smtp-page wp-mail-smtp-page-general wp-mail-smtp-tab-<?php echo esc_attr( $this->get_current_tab() ); ?>">
							<?php $this->display_tabs(); ?>
						</div>

						<?php
						break;

					case self::SLUG . '-logs':
						/**
						 * The Email Logs object to be used for loading the Email Log page.
						 *
						 * @var \WPMailSMTP\Admin\PageAbstract $logs
						 */
						$logs = $this->generate_display_logs_object();

						$is_archive = wp_mail_smtp()->is_pro() && wp_mail_smtp()->pro->get_logs()->is_archive();
						?>

						<div class="wp-mail-smtp-page wp-mail-smtp-page-logs <?php echo $is_archive ? 'wp-mail-smtp-page-logs-archive' : 'wp-mail-smtp-page-logs-single'; ?>">
							<?php $logs->display(); ?>
						</div>

						<?php
						break;

					default:
						foreach ( $this->get_parent_pages() as $parent_page ) {
							if ( $page === self::SLUG . '-' . $parent_page->get_slug() ) {
								?>
								<div class="wp-mail-smtp-page wp-mail-smtp-page-<?php echo esc_attr( $parent_page->get_slug() ); ?> wp-mail-smtp-tab-<?php echo esc_attr( $parent_page->get_slug() ); ?>-<?php echo esc_attr( $parent_page->get_current_tab() ); ?>">
									<?php $parent_page->display(); ?>
								</div>
								<?php
								break;
							}
						}
				}
				?>
		</div>

		<?php
	}

	/**
	 * Generate the appropriate Email Log page object used for displaying the Email Log page.
	 *
	 * @since 2.1.0
	 *
	 * @return \WPMailSMTP\Admin\PageAbstract
	 */
	public function generate_display_logs_object() {

		// Store generated object to make sure that it's created only once.
		static $logs_object = null;

		$logs_class = apply_filters( 'wp_mail_smtp_admin_display_get_logs_fqcn', \WPMailSMTP\Admin\Pages\Logs::class );

		if ( $logs_object === null ) {
			$logs_object = new $logs_class();
		}

		return $logs_object;
	}

	/**
	 * Get email logs access capability.
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public function get_logs_access_capability() {

		/**
		 * Filter email logs access capability.
		 *
		 * @since 2.8.0
		 *
		 * @param string $capability Email logs access capability.
		 */
		return apply_filters(
			'wp_mail_smtp_admin_area_get_logs_access_capability',
			wp_mail_smtp()->get_capability_manage_options()
		);
	}

	/**
	 * Display General page tabs.
	 *
	 * @since 1.5.0
	 */
	protected function display_tabs() {

		?>
		<div class="wp-mail-smtp-page-title">
			<?php
			foreach ( $this->get_pages() as $page_slug => $page ) :
				$label = $page->get_label();
				if ( empty( $label ) ) {
					continue;
				}
				$class = $page_slug === $this->get_current_tab() ? 'active' : '';
				?>

				<a href="<?php echo esc_url( $page->get_link() ); ?>" class="tab <?php echo esc_attr( $class ); ?>">
					<?php echo esc_html( $label ); ?>
				</a>

			<?php endforeach; ?>
		</div>

		<div class="wp-mail-smtp-page-content">
			<h1 class="screen-reader-text">
				<?php echo esc_html( $this->get_current_tab_title() ); ?>
			</h1>

			<?php do_action( 'wp_mail_smtp_admin_pages_before_content' ); ?>

			<?php $this->display_current_tab_content(); ?>
		</div>

		<?php
	}

	/**
	 * Get the current tab content.
	 *
	 * @since 1.0.0
	 */
	public function display_current_tab_content() {

		$pages = $this->get_pages();

		if ( ! array_key_exists( $this->get_current_tab(), $pages ) ) {
			return;
		}

		$pages[ $this->get_current_tab() ]->display();
	}

	/**
	 * Get the current admin area tab.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_current_tab() {

		$current = '';

		if ( $this->is_admin_page( 'general' ) ) {
			$current = ! empty( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'settings'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		return $current;
	}

	/**
	 * Get admin parent pages.
	 *
	 * @since 2.8.0
	 *
	 * @return ParentPageAbstract[]
	 */
	public function get_parent_pages() {

		static $pages = null;

		if ( $pages === null ) {
			$pages = [
				'reports' => new Pages\EmailReports(
					[
						'reports' => Pages\EmailReportsTab::class,
					]
				),
				'tools'   => new Pages\Tools(
					[
						'test'             => Pages\TestTab::class,
						'export'           => Pages\ExportTab::class,
						'action-scheduler' => Pages\ActionSchedulerTab::class,
						'debug-events'     => Pages\DebugEventsTab::class,
					]
				),
			];

			if ( ! wp_mail_smtp()->is_white_labeled() ) {
				$about_tabs = [
					'about' => Pages\AboutTab::class,
				];

				if ( wp_mail_smtp()->get_license_type() === 'lite' ) {
					$about_tabs['versus'] = Pages\VersusTab::class;
				}

				$pages['about'] = new Pages\About( $about_tabs );
			}
		}

		/**
		 * Filters admin parent pages.
		 *
		 * @since 2.8.0
		 *
		 * @param ParentPageAbstract[] $pages Parent pages.
		 */
		return apply_filters( 'wp_mail_smtp_admin_area_get_parent_pages', $pages );
	}

	/**
	 * Get the array of default registered tabs for General page admin area.
	 *
	 * @since 1.0.0
	 *
	 * @return PageAbstract[]
	 */
	public function get_pages() {

		if ( empty( $this->pages ) ) {
			$this->pages = [
				'settings'    => new Pages\SettingsTab(),
				'logs'        => new Pages\LogsTab(),
				'alerts'      => new Pages\AlertsTab(),
				'connections' => new Pages\AdditionalConnectionsTab(),
				'routing'     => new Pages\SmartRoutingTab(),
				'control'     => new Pages\ControlTab(),
				'misc'        => new Pages\MiscTab(),
				'auth'        => new Pages\AuthTab(),
			];
		}

		return apply_filters( 'wp_mail_smtp_admin_get_pages', $this->pages );
	}

	/**
	 * Get the current tab title.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_current_tab_title() {

		$pages = $this->get_pages();

		if ( ! array_key_exists( $this->get_current_tab(), $pages ) ) {
			return '';
		}

		return $pages[ $this->get_current_tab() ]->get_title();
	}

	/**
	 * Check whether we are on an admin page.
	 *
	 * @since 1.0.0
	 * @since 1.5.0 Added support for new pages.
	 *
	 * @param array|string $slug ID(s) of a plugin page. Possible values: 'general', 'logs', 'about' or array of them.
	 *
	 * @return bool
	 */
	public function is_admin_page( $slug = array() ) { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$cur_page    = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
		$check       = self::SLUG;
		$pages_equal = false;

		if ( is_string( $slug ) ) {
			$slug = sanitize_key( $slug );

			if (
				in_array( $slug, self::$pages_registered, true ) &&
				$slug !== 'general'
			) {
				$check = self::SLUG . '-' . $slug;
			}

			$pages_equal = $cur_page === $check;
		} elseif ( is_array( $slug ) ) {
			if ( empty( $slug ) ) {
				$slug = array_map(
					function ( $v ) {
						if ( $v === 'general' ) {
							return Area::SLUG;
						}
						return Area::SLUG . '-' . $v;
					},
					self::$pages_registered
				);
			} else {
				$slug = array_map(
					function ( $v ) {
						if ( $v === 'general' ) {
							return Area::SLUG;
						}
						return Area::SLUG . '-' . sanitize_key( $v );
					},
					$slug
				);
			}

			$pages_equal = in_array( $cur_page, $slug, true );
		}

		return is_admin() && $pages_equal;
	}

	/**
	 * Give ability to use either admin area option or a filter to hide error notices about failed email delivery.
	 * Filter has higher priority and overrides an option.
	 *
	 * @since 1.6.0
	 *
	 * @return bool
	 */
	public function is_error_delivery_notice_enabled() {

		$is_hard_enabled = (bool) apply_filters( 'wp_mail_smtp_admin_is_error_delivery_notice_enabled', true );

		// If someone changed the value to false using a filter - disable completely.
		if ( ! $is_hard_enabled ) {
			return false;
		}

		return ! (bool) Options::init()->get( 'general', 'email_delivery_errors_hidden' );
	}

	/**
	 * All possible plugin forms manipulation will be done here.
	 *
	 * @since 1.0.0
	 */
	public function process_actions() {

		// Bail if we're not on a plugin General page.
		if ( ! $this->is_admin_page( 'general' ) ) {
			return;
		}

		$pages = $this->get_pages();

		// Allow to process only own tabs.
		if ( ! array_key_exists( $this->get_current_tab(), $pages ) ) {
			return;
		}

		// Process POST only if it exists.
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		if ( ! empty( $_POST ) && isset( $_POST['wp-mail-smtp-post'] ) ) {
			$current_tab = $pages[ $this->get_current_tab() ];

			// Verify nonce.
			$current_tab->check_admin_referer();

			// Verify capability.
			if ( ! current_user_can( wp_mail_smtp()->get_capability_manage_options() ) ) {
				wp_die( esc_html__( 'You don\'t have the capability to perform this action.', 'wp-mail-smtp' ) );
			}

			if ( ! empty( $_POST['wp-mail-smtp'] ) ) {
				$post = $_POST['wp-mail-smtp'];
			} else {
				$post = [];
			}

			/**
			 * Before process post.
			 *
			 * @since 3.3.0
			 *
			 * @param array  $post      POST data.
			 * @param string $page_slug Current page slug.
			 */
			do_action(
				'wp_mail_smtp_admin_area_process_actions_process_post_before',
				$post,
				$current_tab->get_slug()
			);

			$current_tab->process_post( $post );
		}
		// phpcs:enable

		// This won't do anything for most pages.
		// Works for plugin page only, when GET params are allowed.
		$pages[ $this->get_current_tab() ]->process_auth();
	}

	/**
	 * Process all AJAX requests.
	 *
	 * @since 1.3.0
	 * @since 1.5.0 Added tasks to process plugins management.
	 */
	public function process_ajax() {

		$data = [];

		// Only admins can fire these ajax requests.
		if ( ! current_user_can( wp_mail_smtp()->get_capability_manage_options() ) ) {
			wp_send_json_error( $data );
		}

		// Verify nonce for all AJAX requests.
		check_ajax_referer( 'wp-mail-smtp-admin', 'nonce' );

		if ( empty( $_POST['task'] ) ) {
			wp_send_json_error( $data );
		}

		$task = sanitize_key( $_POST['task'] );

		switch ( $task ) {
			case 'pro_banner_dismiss':
				update_user_meta( get_current_user_id(), 'wp_mail_smtp_pro_banner_dismissed', true );
				$data['message'] = esc_html__( 'WP Mail SMTP Pro related message was successfully dismissed.', 'wp-mail-smtp' );
				break;

			case 'about_plugin_install':
				Pages\AboutTab::ajax_plugin_install();
				break;

			case 'about_plugin_activate':
				Pages\AboutTab::ajax_plugin_activate();
				break;

			case 'notice_dismiss':
				$dismissal_response = $this->dismiss_notice_via_ajax();

				if ( empty( $dismissal_response ) ) {
					break;
				}

				$data['message'] = $dismissal_response;
				break;

			case 'email_test_tab_removal_notice_dismiss':
				update_user_meta( get_current_user_id(), 'wp_mail_smtp_email_test_tab_removal_notice_dismissed', true );
				break;

			default:
				// Allow custom tasks data processing being added here.
				$data = apply_filters( 'wp_mail_smtp_admin_process_ajax_' . $task . '_data', $data );
		}

		// Final ability to rewrite all the data, just in case.
		$data = (array) apply_filters( 'wp_mail_smtp_admin_process_ajax_data', $data, $task );

		if ( empty( $data ) ) {
			wp_send_json_error( $data );
		}

		wp_send_json_success( $data );
	}

	/**
	 * Process the notice dismissal via AJAX call (Post request).
	 *
	 * @since 3.3.0
	 *
	 * @return false|string
	 */
	private function dismiss_notice_via_ajax() {

		// Nonce already verified in process_ajax().

		if ( empty( $_POST['notice'] ) ) {
			return false;
		}

		$notice = sanitize_key( $_POST['notice'] );

		if ( ! empty( $_POST['mailer'] ) ) {
			$mailer = sanitize_key( $_POST['mailer'] );

			update_user_meta( get_current_user_id(), "wp_mail_smtp_notice_{$notice}_for_{$mailer}_dismissed", true );

			return esc_html__( 'Educational notice for this mailer was successfully dismissed.', 'wp-mail-smtp' );
		} else {
			update_user_meta( get_current_user_id(), "wp_mail_smtp_notice_{$notice}_dismissed", true );

			return esc_html__( 'Notice was successfully dismissed.', 'wp-mail-smtp' );
		}
	}

	/**
	 * Add plugin action links on Plugins page (lite version only).
	 *
	 * @since 1.0.0
	 * @since 1.5.0 Added a link to Email Log.
	 * @since 2.0.0 Adjusted links. Process only the Lite plugin.
	 *
	 * @param array $links Existing plugin action links.
	 *
	 * @return array
	 */
	public function add_plugin_action_link( $links ) {

		// Do not register lite plugin action links if on pro version.
		if ( wp_mail_smtp()->is_pro() ) {
			return $links;
		}

		$custom['wp-mail-smtp-pro'] = sprintf(
			'<a href="%1$s" aria-label="%2$s" target="_blank" rel="noopener noreferrer" 
				style="color: #00a32a; font-weight: 700;" 
				onmouseover="this.style.color=\'#008a20\';" 
				onmouseout="this.style.color=\'#00a32a\';"
				>%3$s</a>',
			// phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
			esc_url( wp_mail_smtp()->get_upgrade_link( [ 'medium' => 'all-plugins', 'content' => 'Get WP Mail SMTP Pro' ] ) ),
			esc_attr__( 'Upgrade to WP Mail SMTP Pro', 'wp-mail-smtp' ),
			esc_html__( 'Get WP Mail SMTP Pro', 'wp-mail-smtp' )
		);

		$custom['wp-mail-smtp-settings'] = sprintf(
			'<a href="%s" aria-label="%s">%s</a>',
			esc_url( $this->get_admin_page_url() ),
			esc_attr__( 'Go to WP Mail SMTP Settings page', 'wp-mail-smtp' ),
			esc_html__( 'Settings', 'wp-mail-smtp' )
		);

		$custom['wp-mail-smtp-docs'] = sprintf(
			'<a href="%1$s" target="_blank" aria-label="%2$s" rel="noopener noreferrer">%3$s</a>',
			// phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
			esc_url( wp_mail_smtp()->get_utm_url( 'https://wpmailsmtp.com/docs/', [ 'medium' => 'all-plugins', 'content' => 'Documentation' ] ) ),
			esc_attr__( 'Go to WPMailSMTP.com documentation page', 'wp-mail-smtp' ),
			esc_html__( 'Docs', 'wp-mail-smtp' )
		);

		return array_merge( $custom, (array) $links );
	}

	/**
	 * Get plugin admin area page URL.
	 *
	 * @since 1.0.0
	 * @since 1.5.0 URL is changed to support the top level position of the plugin admin area.
	 *
	 * @param string $page The page slug to add as the page query parameter.
	 *
	 * @return string
	 */
	public function get_admin_page_url( $page = '' ) {

		if ( empty( $page ) ) {
			$page = self::SLUG;
		}

		return add_query_arg(
			'page',
			$page,
			WP::admin_url( 'admin.php' )
		);
	}

	/**
	 * Remove all non-WP Mail SMTP plugin notices from our plugin pages.
	 *
	 * @since 1.0.0
	 */
	public function hide_unrelated_notices() {

		// Bail if we're not on our screen or page.
		if ( ! $this->is_admin_page() ) {
			return;
		}

		$this->remove_unrelated_actions( 'user_admin_notices' );
		$this->remove_unrelated_actions( 'admin_notices' );
		$this->remove_unrelated_actions( 'all_admin_notices' );
		$this->remove_unrelated_actions( 'network_admin_notices' );
	}

	/**
	 * Remove all non-WP Mail SMTP notices from the our plugin pages based on the provided action hook.
	 *
	 * @since 3.0.0
	 *
	 * @param string $action The name of the action.
	 */
	private function remove_unrelated_actions( $action ) {

		global $wp_filter;

		if ( empty( $wp_filter[ $action ]->callbacks ) || ! is_array( $wp_filter[ $action ]->callbacks ) ) {
			return;
		}

		foreach ( $wp_filter[ $action ]->callbacks as $priority => $hooks ) {
			foreach ( $hooks as $name => $arr ) {
				if (
					( // Cover object method callback case.
						is_array( $arr['function'] ) &&
						isset( $arr['function'][0] ) &&
						is_object( $arr['function'][0] ) &&
						strpos( strtolower( get_class( $arr['function'][0] ) ), 'wpmailsmtp' ) !== false
					) ||
					( // Cover class static method callback case.
						! empty( $name ) &&
						strpos( strtolower( $name ), 'wpmailsmtp' ) !== false
					)
				) {
					continue;
				}

				unset( $wp_filter[ $action ]->callbacks[ $priority ][ $name ] );
			}
		}
	}

	/**
	 * Maybe redirect to "Tools -> Email Test" page if old direct URL to "Settings -> Email Test" is accessed.
	 *
	 * @deprecated 3.9.0
	 *
	 * @since 2.8.0
	 */
	public function maybe_redirect_test_tab() {

		_deprecated_function( __METHOD__, '3.9.0' );

		if ( $this->is_admin_page( 'general' ) && $this->get_current_tab() === 'test' ) {
			wp_safe_redirect( add_query_arg( 'tab', 'test', $this->get_admin_page_url( self::SLUG . '-tools' ) ) );
		}
	}

	/**
	 * Define inline styles for "Upgrade to Pro" left sidebar menu item.
	 *
	 * @since 3.4.0
	 */
	public function style_upgrade_pro_link() {

		global $submenu;

		// Bail if plugin menu is not registered.
		if ( ! isset( $submenu[ self::SLUG ] ) ) {
			return;
		}

		$upgrade_link_position = key(
			array_filter(
				$submenu[ self::SLUG ],
				function ( $item ) {
					return strpos( urldecode( $item[2] ), 'wpmailsmtp.com/lite-upgrade' ) !== false;
				}
			)
		);

		// Bail if "Upgrade to Pro" menu item is not registered.
		if ( is_null( $upgrade_link_position ) ) {
			return;
		}

		// Prepare a HTML class.
		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
		if ( isset( $submenu[ self::SLUG ][ $upgrade_link_position ][4] ) ) {
			$submenu[ self::SLUG ][ $upgrade_link_position ][4] .= ' wp-mail-smtp-sidebar-upgrade-pro';
		} else {
			$submenu[ self::SLUG ][ $upgrade_link_position ][] = 'wp-mail-smtp-sidebar-upgrade-pro';
		}

		$current_screen      = get_current_screen();
		$upgrade_utm_content = $current_screen === null ? 'Upgrade to Pro' : 'Upgrade to Pro - ' . $current_screen->base;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$upgrade_utm_content = empty( $_GET['tab'] ) ? $upgrade_utm_content : $upgrade_utm_content . ' -- ' . sanitize_key( $_GET['tab'] );

		// Add the correct utm_content to the menu item.
		$submenu[ self::SLUG ][ $upgrade_link_position ][2] =
			esc_url( wp_mail_smtp()->get_upgrade_link( [ 'medium' => 'admin-menu', 'content' => $upgrade_utm_content ] ) ); // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
		// phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited

		// Output inline styles.
		echo '<style>a.wp-mail-smtp-sidebar-upgrade-pro { background-color: #00a32a !important; color: #fff !important; font-weight: 600 !important; }</style>';
	}

	/**
	 * Display the promotional footer in our plugin pages.
	 *
	 * @since 3.10.0
	 */
	public function display_admin_footer() { //phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		// Bail early on non-plugin pages.
		if ( ! $this->is_admin_page() ) {
			return;
		}

		$title = esc_html__( 'Made with ♥ by the WP Mail SMTP team', 'wp-mail-smtp' );
		$links = [
			[
				'url'    => wp_mail_smtp()->is_pro() ?
					wp_mail_smtp()->get_utm_url(
						'https://wpmailsmtp.com/account/support/',
						[
							'medium'  => 'Plugin Footer',
							'content' => 'Contact Support',
						]
					) : 'https://wordpress.org/support/plugin/wp-mail-smtp/',
				'text'   => esc_html__( 'Support', 'wp-mail-smtp' ),
				'target' => '_blank',
			],
			[
				'url'    => wp_mail_smtp()->get_utm_url(
					'https://wpmailsmtp.com/docs/',
					[
						'medium'  => 'Plugin Footer',
						'content' => 'Plugin Documentation',
					]
				),
				'text'   => esc_html__( 'Docs', 'wp-mail-smtp' ),
				'target' => '_blank',
			],
		];

		if ( ! wp_mail_smtp()->is_white_labeled() ) {
			$links[] = [
				'url'  => $this->get_admin_page_url( self::SLUG . '-about' ),
				'text' => esc_html__( 'Free Plugins', 'wp-mail-smtp' ),
			];
		}

		$links_count = count( $links );
		?>
		<div class="wp-mail-smtp-footer-promotion">
			<p><?php echo esc_html( $title ); ?></p>
			<ul class="wp-mail-smtp-footer-promotion-links">
			<?php foreach ( $links as $key => $item ) : ?>
				<li>
					<?php
					$attrs = 'href="' . esc_url( $item['url'] ) . '"';

					if ( isset( $item['target'] ) ) {
						$attrs .= ' target="' . esc_attr( $item['target'] ) . '"';
						$attrs .= ' rel="noopener noreferrer"';
					}

					$text    = esc_html( $item['text'] );
					$divider = $links_count !== $key + 1 ? '<span>/</span>' : '';

					printf(
						'<a %1$s>%2$s</a>%3$s',
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						$attrs,
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						$text,
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						$divider
					);
					?>
				</li>
			<?php endforeach; ?>
			</ul>
			<ul class="wp-mail-smtp-footer-promotion-social">
				<li>
					<a href="https://www.facebook.com/wpmailsmtp" target="_blank" rel="noopener noreferrer">
						<svg width="16" height="16" aria-hidden="true">
							<path fill="#A7AAAD" d="M16 8.05A8.02 8.02 0 0 0 8 0C3.58 0 0 3.6 0 8.05A8 8 0 0 0 6.74 16v-5.61H4.71V8.05h2.03V6.3c0-2.02 1.2-3.15 3-3.15.9 0 1.8.16 1.8.16v1.98h-1c-1 0-1.31.62-1.31 1.27v1.49h2.22l-.35 2.34H9.23V16A8.02 8.02 0 0 0 16 8.05Z"/>
						</svg>
						<span class="screen-reader-text"><?php echo esc_html( 'Facebook' ); ?></span>
					</a>
				</li>
				<li>
					<a href="https://x.com/wpmailsmtp" target="_blank" rel="noopener noreferrer">
						<svg width="17" height="17" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
							<path fill="#A7AAAD" d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/>
						</svg>
						<span class="screen-reader-text"><?php echo esc_html( 'X' ); ?></span>
					</a>
				</li>
				<li>
					<a href="https://youtube.com/playlist?list=PLt2XcSO7dFmCUMO0ky46Od6U2oSaiNodP" target="_blank" rel="noopener noreferrer">
						<svg width="17" height="16" aria-hidden="true">
							<path fill="#A7AAAD" d="M16.63 3.9a2.12 2.12 0 0 0-1.5-1.52C13.8 2 8.53 2 8.53 2s-5.32 0-6.66.38c-.71.18-1.3.78-1.49 1.53C0 5.2 0 8.03 0 8.03s0 2.78.37 4.13c.19.75.78 1.3 1.5 1.5C3.2 14 8.51 14 8.51 14s5.28 0 6.62-.34c.71-.2 1.3-.75 1.49-1.5.37-1.35.37-4.13.37-4.13s0-2.81-.37-4.12Zm-9.85 6.66V5.5l4.4 2.53-4.4 2.53Z"/>
						</svg>
						<span class="screen-reader-text"><?php echo esc_html( 'YouTube' ); ?></span>
					</a>
				</li>
			</ul>
		</div>
		<?php
	}

	/**
	 * Display the plugin version in the footer of our plugin pages.
	 *
	 * @since 3.10.0
	 *
	 * @param string $text Text of the footer.
	 */
	public function display_update_footer( $text ) {

		if ( $this->is_admin_page() ) {
			return 'WP Mail SMTP ' . WPMS_PLUGIN_VER;
		}

		return $text;
	}
}
