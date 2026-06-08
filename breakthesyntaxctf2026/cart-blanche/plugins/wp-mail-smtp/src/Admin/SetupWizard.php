<?php

namespace WPMailSMTP\Admin;

use Plugin_Upgrader;
use WPMailSMTP\Admin\Pages\TestTab;
use WPMailSMTP\Connect;
use WPMailSMTP\Helpers\Helpers;
use WPMailSMTP\Helpers\PluginImportDataRetriever;
use WPMailSMTP\Options;
use WPMailSMTP\UsageTracking\UsageTracking;
use WPMailSMTP\WP;
use WPMailSMTP\Reports\Emails\Summary as SummaryReportEmail;
use WPMailSMTP\Tasks\Reports\SummaryEmailTask as SummaryReportEmailTask;

/**
 * Class for the plugin's Setup Wizard.
 *
 * @since 2.6.0
 */
class SetupWizard {

	/**
	 * The WP Option key for storing setup wizard stats.
	 *
	 * @since 3.1.0
	 */
	const STATS_OPTION_KEY = 'wp_mail_smtp_setup_wizard_stats';

	/**
	 * Run all the hooks needed for the Setup Wizard.
	 *
	 * @since 2.6.0
	 */
	public function hooks() {

		add_action( 'admin_init', [ $this, 'maybe_load_wizard' ] );
		add_action( 'admin_init', [ $this, 'maybe_redirect_after_activation' ], 9999 );
		add_action( 'admin_menu', [ $this, 'add_dashboard_page' ], 20 );
		add_filter( 'removable_query_args', [ $this, 'maybe_disable_automatic_query_args_removal' ] );

		// API AJAX callbacks.
		add_action( 'wp_ajax_wp_mail_smtp_vue_wizard_steps_started', [ $this, 'wizard_steps_started' ] );
		add_action( 'wp_ajax_wp_mail_smtp_vue_get_settings', [ $this, 'get_settings' ] );
		add_action( 'wp_ajax_wp_mail_smtp_vue_update_settings', [ $this, 'update_settings' ] );
		add_action( 'wp_ajax_wp_mail_smtp_vue_import_settings', [ $this, 'import_settings' ] );
		add_action( 'wp_ajax_wp_mail_smtp_vue_get_oauth_url', [ $this, 'get_oauth_url' ] );
		add_action( 'wp_ajax_wp_mail_smtp_vue_remove_oauth_connection', [ $this, 'remove_oauth_connection' ] );
		add_action( 'wp_ajax_wp_mail_smtp_vue_get_connected_data', [ $this, 'get_connected_data' ] );
		add_action( 'wp_ajax_wp_mail_smtp_vue_install_plugin', [ $this, 'install_plugin' ] );
		add_action( 'wp_ajax_wp_mail_smtp_vue_get_partner_plugins_info', [ $this, 'get_partner_plugins_info' ] );
		add_action( 'wp_ajax_wp_mail_smtp_vue_subscribe_to_newsletter', [ $this, 'subscribe_to_newsletter' ] );
		add_action( 'wp_ajax_wp_mail_smtp_vue_upgrade_plugin', [ $this, 'upgrade_plugin' ] );
		add_action( 'wp_ajax_wp_mail_smtp_vue_check_mailer_configuration', [ $this, 'check_mailer_configuration' ] );
		add_action( 'wp_ajax_wp_mail_smtp_vue_send_feedback', [ $this, 'send_feedback' ] );
	}

	/**
	 * Get the URL of the Setup Wizard page.
	 *
	 * @since 2.6.0
	 *
	 * @return string
	 */
	public static function get_site_url() {

		return wp_mail_smtp()->get_admin()->get_admin_page_url() . '-setup-wizard';
	}

	/**
	 * Checks if the Wizard should be loaded in current context.
	 *
	 * @since 2.6.0
	 */
	public function maybe_load_wizard() {

		// Check for wizard-specific parameter
		// Allow plugins to disable the setup wizard
		// Check if current user is allowed to save settings.
		if (
			! (
				isset( $_GET['page'] ) && // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				Area::SLUG . '-setup-wizard' === $_GET['page'] && // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$this->should_setup_wizard_load() &&
				current_user_can( wp_mail_smtp()->get_capability_manage_options() )
			)
		) {
			return;
		}

		// Don't load the interface if doing an ajax call.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		set_current_screen();

		// Remove an action in the Gutenberg plugin ( not core Gutenberg ) which throws an error.
		remove_action( 'admin_print_styles', 'gutenberg_block_editor_admin_print_styles' );

		// Remove hooks for deprecated functions in WordPress 6.4.0.
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_head', 'wp_admin_bar_header' );

		$this->load_setup_wizard();
	}

	/**
	 * Maybe redirect to the setup wizard after plugin activation on a new install.
	 *
	 * @since 2.6.0
	 */
	public function maybe_redirect_after_activation() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		// Only users who can activate plugins should be redirected.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		// Check if we should consider redirection.
		if ( ! get_transient( 'wp_mail_smtp_activation_redirect' ) ) {
			return;
		}

		delete_transient( 'wp_mail_smtp_activation_redirect' );

		// Check option to disable setup wizard redirect.
		if ( get_option( 'wp_mail_smtp_activation_prevent_redirect' ) ) {
			return;
		}

		// Only do this for single site installs if Network Wide setting is not enabled.
		if ( isset( $_GET['activate-multi'] ) || is_network_admin() || WP::use_global_plugin_settings() ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		// Don't redirect if the Setup Wizard is disabled.
		if ( ! $this->should_setup_wizard_load() ) {
			return;
		}

		// Initial install.
		if ( get_option( 'wp_mail_smtp_initial_version' ) === WPMS_PLUGIN_VER ) {
			update_option( 'wp_mail_smtp_activation_prevent_redirect', true );
			wp_safe_redirect( self::get_site_url() );
			exit;
		}
	}

	/**
	 * Register page through WordPress's hooks.
	 *
	 * Create a dummy admin page, where the Setup Wizard app can be displayed,
	 * but it's not visible in the admin dashboard menu.
	 *
	 * @since 2.6.0
	 */
	public function add_dashboard_page() {

		if ( ! $this->should_setup_wizard_load() ) {
			return;
		}

		add_submenu_page( '', '', '', wp_mail_smtp()->get_capability_manage_options(), Area::SLUG . '-setup-wizard', '' );
	}

	/**
	 * Load the Setup Wizard template.
	 *
	 * @since 2.6.0
	 */
	private function load_setup_wizard() {

		/**
		 * Before setup wizard load.
		 *
		 * @since 2.8.0
		 *
		 * @param \WPMailSMTP\Admin\SetupWizard  $setup_wizard SetupWizard instance.
		 */
		do_action( 'wp_mail_smtp_admin_setup_wizard_load_setup_wizard_before', $this );

		$this->enqueue_scripts();

		$this->setup_wizard_header();
		$this->setup_wizard_content();
		$this->setup_wizard_footer();

		/**
		 * After setup wizard load.
		 *
		 * @since 2.8.0
		 *
		 * @param \WPMailSMTP\Admin\SetupWizard  $setup_wizard SetupWizard instance.
		 */
		do_action( 'wp_mail_smtp_admin_setup_wizard_load_setup_wizard_after', $this );

		exit;
	}

	/**
	 * Load the scripts needed for the Setup Wizard.
	 *
	 * @since 2.6.0
	 */
	public function enqueue_scripts() {

		if ( ! defined( 'WPMS_VUE_LOCAL_DEV' ) || ! WPMS_VUE_LOCAL_DEV ) {
			$rtl = is_rtl() ? '.rtl' : '';
			wp_enqueue_style( 'wp-mail-smtp-vue-style', wp_mail_smtp()->assets_url . '/vue/css/wizard' . $rtl . '.min.css', [], WPMS_PLUGIN_VER );
		}

		wp_enqueue_script( 'wp-mail-smtp-vue-vendors', wp_mail_smtp()->assets_url . '/vue/js/chunk-vendors.min.js', [], WPMS_PLUGIN_VER, true );
		wp_enqueue_script( 'wp-mail-smtp-vue-script', wp_mail_smtp()->assets_url . '/vue/js/wizard.min.js', [ 'wp-mail-smtp-vue-vendors' ], WPMS_PLUGIN_VER, true );

		wp_localize_script(
			'wp-mail-smtp-vue-script',
			'wp_mail_smtp_vue',
			[
				'ajax_url'           => admin_url( 'admin-ajax.php' ),
				'nonce'              => wp_create_nonce( 'wpms-admin-nonce' ),
				'is_multisite'       => is_multisite(),
				'translations'       => WP::get_jed_locale_data( 'wp-mail-smtp' ),
				'exit_url'           => wp_mail_smtp()->get_admin()->get_admin_page_url(),
				'email_test_tab_url' => add_query_arg( 'tab', 'test', wp_mail_smtp()->get_admin()->get_admin_page_url( Area::SLUG . '-tools' ) ),
				'is_pro'             => wp_mail_smtp()->is_pro(),
				'is_ssl'             => is_ssl(),
				'license_exists'     => apply_filters( 'wp_mail_smtp_admin_setup_wizard_license_exists', false ),
				'plugin_version'     => WPMS_PLUGIN_VER,
				'other_smtp_plugins' => $this->detect_other_smtp_plugins(),
				'mailer_options'     => $this->prepare_mailer_options(),
				'defined_constants'  => $this->prepare_defined_constants(),
				'upgrade_link'       => wp_mail_smtp()->get_upgrade_link( 'setup-wizard' ),
				'versions'           => $this->prepare_versions_data(),
				'public_url'         => wp_mail_smtp()->assets_url . '/vue/',
				'current_user_email' => wp_get_current_user()->user_email,
				'completed_time'     => self::get_stats()['completed_time'],
				'sendlayer'          => [
					'connect_nonce' => wp_create_nonce( 'wp-mail-smtp-sendlayer-connect' ),
					'return_url'    => self::get_site_url() . '#/step/configure_mailer/sendlayer',
				],
				'education'          => [
					'upgrade_text'   => esc_html__( 'We\'re sorry, the %mailer% mailer is not available on your plan. Please upgrade to the PRO plan to unlock all these awesome features.', 'wp-mail-smtp' ),
					'upgrade_button' => esc_html__( 'Upgrade to Pro', 'wp-mail-smtp' ),
					'upgrade_url'    => add_query_arg( 'discount', 'SMTPLITEUPGRADE', wp_mail_smtp()->get_upgrade_link( [ 'medium' => 'setup-wizard' ] ) ),
					'upgrade_bonus'  => sprintf(
						wp_kses( /* Translators: %s - discount value $50 */
							__( '<strong>Bonus:</strong> WP Mail SMTP users get <span class="highlight">%s off</span> regular price,<br>applied at checkout.', 'wp-mail-smtp' ),
							[
								'strong' => [],
								'span'   => [
									'class' => [],
								],
								'br'     => [],
							]
						),
						'$50'
					),
					'upgrade_doc'       => sprintf(
						'<a href="%1$s" target="_blank" rel="noopener noreferrer" class="already-purchased">%2$s</a>',
						// phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
						esc_url( wp_mail_smtp()->get_utm_url( 'https://wpmailsmtp.com/docs/how-to-upgrade-wp-mail-smtp-to-pro-version/', [ 'medium' => 'setup-wizard', 'content' => 'Wizard Pro Mailer Popup - Already purchased' ] ) ),
						esc_html__( 'Already purchased?', 'wp-mail-smtp' )
					)
				],
			]
		);
	}

	/**
	 * Outputs the simplified header used for the Setup Wizard.
	 *
	 * @since 2.6.0
	 */
	public function setup_wizard_header() {
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width"/>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			<title><?php esc_html_e( 'WP Mail SMTP &rsaquo; Setup Wizard', 'wp-mail-smtp' ); ?></title>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_print_scripts' ); ?>
			<?php do_action( 'admin_head' ); ?>
		</head>
		<body class="wp-mail-smtp-setup-wizard">
		<?php
	}

	/**
	 * Outputs the content of the current step.
	 *
	 * @since 2.6.0
	 */
	public function setup_wizard_content() {
		$admin_url = is_network_admin() ? network_admin_url() : admin_url();

		$this->settings_error_page( 'wp-mail-smtp-vue-setup-wizard', '<a href="' . $admin_url . '">' . esc_html__( 'Go back to the Dashboard', 'wp-mail-smtp' ) . '</a>' );
		$this->settings_inline_js();
	}

	/**
	 * Outputs the simplified footer used for the Setup Wizard.
	 *
	 * @since 2.6.0
	 */
	public function setup_wizard_footer() {
		?>
		<?php wp_print_scripts( 'wp-mail-smtp-vue-script' ); ?>
		</body>
		</html>
		<?php
	}

	/**
	 * Error page HTML
	 *
	 * @since 2.6.0
	 *
	 * @param string $id     The HTML ID attribute of the main container div.
	 * @param string $footer The centered footer content.
	 */
	private function settings_error_page( $id = 'wp-mail-smtp-vue-site-settings', $footer = '' ) {

		$inline_logo_image = 'data:image/svg+xml;base64,PHN2ZyBpZD0iTGF5ZXJfMSIgZGF0YS1uYW1lPSJMYXllciAxIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNDIgNjAiPjxkZWZzPjxzdHlsZT4uY2xzLTExLC5jbHMtMTJ7ZmlsbC1ydWxlOmV2ZW5vZGR9LmNscy00e2ZpbGw6bm9uZX0uY2xzLTExe2ZpbGw6Izg2YTE5Nn0uY2xzLTEye2ZpbGw6I2ZmZn08L3N0eWxlPjwvZGVmcz48cGF0aCBkPSJNNjkuMDYgMTEuMTFMNjQuNyAyMy40OWgtLjA2bC0xLjg5LTYuNTUtMi02LjE0aC0zLjEzdi40NGw2IDE3Ljg5aDEuNjZsNC4zOS0xMS42N2guMDZsNC4zOSAxMS42N2gxLjU2bDYuMDYtMTcuODl2LS40NGgtMy4ybC0xLjkyIDYuMTQtMS44MiA2LjUyaC0uMDZsLTQuMzItMTIuMzV6TTg3LjY4IDI5aC0zVjEwLjhoNy41NGE2LjE3IDYuMTcgMCAwMTYuNDIgNi40MiA2LjE0IDYuMTQgMCAwMS02LjQyIDYuMzJoLTQuNXptLS4wNS04LjExaDQuNTVhMy41NCAzLjU0IDAgMDAzLjUxLTMuNzUgMy40OSAzLjQ5IDAgMDAtMy41MS0zLjcxaC00LjU1em0yOS0uNzNsLTcuNDEtOS40MWgtMS4xMVYyOWgzVjE3LjYxbDUuMjggNi43NGguNDFsNS4yNS02Ljc0VjI5aDMuMDVWMTAuNzVIMTI0em0yNC4xMS0yLjc4djcuODhjMCAxLjE0IDAgMS44NyAxLjM1IDEuNzR2MS45MmMtMS44LjM0LTMuNjQuMTMtMy42NC0ydi0uNTJhNC41NyA0LjU3IDAgMDEtNC4zMiAyLjczYy0zLjgyIDAtNS42Ny0zLjA3LTUuNjctNi42LjA4LTQuMTYgMy4wNS02LjQ1IDcuMTMtNi4zN2ExMi42MiAxMi42MiAwIDAxNS4xNiAxLjIyek0xMzggMjIuNzFWMTlhNi40OSA2LjQ5IDAgMDAtMi42My0uNTJjLTIuMzkgMC00IDEuMzctNC4wOCA0LjA4IDAgMi4yOSAxLjEyIDQuMDggMy40MyA0LjA4IDIuMTMuMDIgMy4yMi0xLjY0IDMuMjgtMy45M3ptNi41Ny0xMC4xMmExLjY1IDEuNjUgMCAwMDEuNzUgMS42OSAxLjYxIDEuNjEgMCAwMDEuNjgtMS42OSAxLjcxIDEuNzEgMCAwMC0zLjQxIDB6bTMuMTIgNGgtMi44M1YyOWgyLjgzek0xNTEuMyAxMHYxNC41M2MwIDQuMTggMS43NyA1LjE3IDUuNjIgNC41NWwtLjExLTIuMTljLTIuMTUuMzQtMi43LS4zMS0yLjctMi4zOVYxMHptMTMuNDcgMTMuODZjLjA4IDMuODIgMy44IDUuNTkgNy4zNiA1LjUxIDMuNCAwIDcuMTctMS41MSA3LjE3LTUuNTEgMC00LjE5LTMuMzgtNC45Mi03LjA3LTUuMzMtMi4xLS4yOS00LjE2LS41NS00LjE2LTIuNnMyLjE2LTIuNzMgMy44Mi0yLjczIDMuODUuNjIgNCAyLjU3aDIuODZjLS4wOC0zLjcyLTMuMzUtNS4yOC02LjgxLTUuMjhzLTYuODQgMS43Ny02Ljg0IDUuNTEgMy4zIDQuNzEgNi42MyA1YzIuMTEuMiA0LjU4LjQ0IDQuNTggMi44M3MtMi4yOSAyLjgzLTQuMjEgMi44My00LjIyLS43NS00LjM1LTIuOHptMjYuNDQtMy42N2wtNy40MS05LjQxaC0xLjEyVjI5aDNWMTcuNjFsNS4zMiA2Ljc0aC40Mmw1LjI1LTYuNzRWMjloM1YxMC43NWgtMS4wN3ptMTYuNTQtNi42OFYyOWgzVjEzLjQ4SDIxNlYxMC44aC0xMy41M3YyLjY4em0xNCAxNS41MmgtM1YxMC44aDcuNTRhNi4xNyA2LjE3IDAgMDE2LjQyIDYuNDIgNi4xNCA2LjE0IDAgMDEtNi40MiA2LjMyaC00LjV6bTAtOC4xMWg0LjU1YTMuNTQgMy41NCAwIDAwMy41MS0zLjc1IDMuNDkgMy40OSAwIDAwLTMuNTEtMy43MWgtNC41NXoiIGZpbGwtcnVsZT0iZXZlbm9kZCIgZmlsbD0iIzIzMjgyYyIvPjxwYXRoIGQ9Ik05NC4xOCAzOC4wOWEuNDYuNDYgMCAwMS4wOS4xOSAxLjE1IDEuMTUgMCAwMTAgLjJ2LjE4YTEuMzMgMS4zMyAwIDAxLS4wOC4yNCAxLjA5IDEuMDkgMCAwMS0uMjEuMzcuNTguNTggMCAwMS0uNDYgMCAuMy4zIDAgMDAtLjE3IDAgMS41OSAxLjU5IDAgMDAtLjM0LS4wNmgtLjM1YTEuNyAxLjcgMCAwMC0uNTUuMDggMS4xMiAxLjEyIDAgMDAtLjQ3LjI5IDEuNzIgMS43MiAwIDAwLS4zNC42IDMuMzQgMy4zNCAwIDAwLS4xNiAxdjEuMTNoMi4xcTAgLjM5LS4wNi42M2EyLjEgMi4xIDAgMDEtLjEuNC42MS42MSAwIDAxLS4xNS4yMiAxLjI2IDEuMjYgMCAwMS0uMjMuMTNoLS4yM2E1LjM1IDUuMzUgMCAwMS0uNjEgMGgtLjc1djcuMTFhMS4xMiAxLjEyIDAgMDEwIC4yNC4yNS4yNSAwIDAxLS4yLjIxIDYuMDggNi4wOCAwIDAxLS42Ni4wN2gtLjUzYTMuMTUgMy4xNSAwIDAxLS42MS0uMDYgMS40IDEuNCAwIDAxMC0uMjNWNDMuN2EyLjE5IDIuMTkgMCAwMS0xLjE3LS4zMWMwLS4xOSAwLS4zNS4wOC0uNDZhLjY5LjY5IDAgMDEuMS0uMjcuNjEuNjEgMCAwMS4xNy0uMTUuODYuODYgMCAwMS4yNS0uMWwuMjItLjA2LjM1LS4wN3YtLjczYTYuMjYgNi4yNiAwIDAxLjA2LS42NSAzLjc5IDMuNzkgMCAwMS40My0xLjYxIDMuMTYgMy4xNiAwIDAxLjg1LTEgMy4yNCAzLjI0IDAgMDExLjA5LS40OSA0LjQgNC40IDAgMDExLjEtLjE1IDMuMiAzLjIgMCAwMTEgLjEzIDEuMzYgMS4zNiAwIDAxLjUzLjI3em05LjgyIDUuNjhhMi4wOCAyLjA4IDAgMDAtLjcxLjEyIDEuNjUgMS42NSAwIDAwLS41OS4zOHY2YTIuNTQgMi41NCAwIDAxMCAuNDEuOTEuOTEgMCAwMS0uMTYuMzcgMS4wNSAxLjA1IDAgMDEtLjI0LjE1IDEuMyAxLjMgMCAwMS0uMzMuMDZoLTEuMjd2LTdhMy44OCAzLjg4IDAgMDAtLjA3LS44MSA0LjczIDQuNzMgMCAwMC0uMTgtLjYzIDEuNjYgMS42NiAwIDAxLjMxLS4yMyAzLjY2IDMuNjYgMCAwMS41Mi0uMjUuNTYuNTYgMCAwMS4xNSAwaC4xMmEuODkuODkgMCAwMS40NS4wOS43Ni43NiAwIDAxLjI0LjMgMy41NyAzLjU3IDAgMDEuNTYtLjMzYy4yLS4wOS4zOS0uMTcuNTctLjIzYTMgMyAwIDAxLjU1LS4xNCAxLjUxIDEuNTEgMCAwMS41NiAwYy41OC4wNi45LjI0IDEgLjUzYTIuODkgMi44OSAwIDAxLS4wNy41NyAxLjQ2IDEuNDYgMCAwMS0uMjcuNjRoLS44N2EuNTYuNTYgMCAwMC0uMTUgMHptNy45MSA3LjU2aC0xLjY3di01Ljg5YTMuMiAzLjIgMCAwMC0uMjQtMS40Ny45LjkgMCAwMC0uODYtLjQzIDEuNjcgMS42NyAwIDAwLS44LjIgMi40MSAyLjQxIDAgMDAtLjYzLjQ5djYuMTRhMi4zNyAyLjM3IDAgMDEwIC40MS42NC42NCAwIDAxLS40My40NyAxLjk0IDEuOTQgMCAwMS0uMzIuMDcgMy4yOCAzLjI4IDAgMDEtLjQ5IDBoLS43NnYtNy4wMWEzLjk0IDMuOTQgMCAwMC0uMDctLjgxIDQuODIgNC44MiAwIDAwLS4xNi0uNjMgMi4yMyAyLjIzIDAgMDEuODMtLjQ4LjU2LjU2IDAgMDEuMTUgMGguMDlhLjguOCAwIDAxLjQ1LjEzLjg2Ljg2IDAgMDEuMjYuMjggNC4zOSA0LjM5IDAgMDExLjE0LS41OCAzLjg0IDMuODQgMCAwMTEuMjQtLjE5aC4zOWEyLjcgMi43IDAgMDExIC4yNyAyLjI2IDIuMjYgMCAwMS42OC41MyAyLjU4IDIuNTggMCAwMS42MS0uMzZjLjIzLS4xLjQ0LS4xOS42My0uMjVhMy42NSAzLjY1IDAgMDExLjIxLS4xOWguNGEyLjkxIDIuOTEgMCAwMTEuMTIuMyAxLjkgMS45IDAgMDEuNjkuNjkgMyAzIDAgMDEuMzQgMSA3Ljc0IDcuNzQgMCAwMS4xIDEuMzN2NS42OWExIDEgMCAwMS0uMjUuMTYgMS40IDEuNCAwIDAxLS4zNS4wOGgtMS4zYTMuMDUgMy4wNSAwIDAxLS4xMy0uMzIgMS42MSAxLjYxIDAgMDEwLS4zOXYtNS4xMWEzLjQ1IDMuNDUgMCAwMC0uMjMtMS40OC44OS44OSAwIDAwLS44NS0uNDIgMS42NCAxLjY0IDAgMDAtLjgxLjIyIDMuNDggMy40OCAwIDAwLS42Ny41M2wuMDYtLjA2djYuNjhjLjAzLjItLjEuMzQtLjM1LjR6TTEyMC42IDQyaC41NmEzLjA1IDMuMDUgMCAwMTEuMzYuMzZjLjI5LjE5LjQ2LjM2LjUuNWExLjI5IDEuMjkgMCAwMS0uMTIuNDggMi42MSAyLjYxIDAgMDEtLjI3LjVoLS4xOGEuODguODggMCAwMS0uMiAwIDMgMyAwIDAwLS4zMi0uMDYgMS41OCAxLjU4IDAgMDEtLjMxLS4wOSAyLjMyIDIuMzIgMCAwMC0uODctLjE3IDEuMTUgMS4xNSAwIDAwLS43OS4yNS43Ny43NyAwIDAwLS4zLjYzIDEgMSAwIDAwLjEuNDQgMS41NCAxLjU0IDAgMDAuNDIuNDZsLjM2LjI3LjQ0LjMxLjU3LjQyLjU1LjRhMy42MyAzLjYzIDAgMDEuOSAxIDIuMjggMi4yOCAwIDAxLjI4IDEuMTNBMi42NSAyLjY1IDAgMDExMjMgNTBhMi41NyAyLjU3IDAgMDEtLjcyLjg1IDMuMTkgMy4xOSAwIDAxLTEuMDguNTIgNC41OSA0LjU5IDAgMDEtMS4zLjE3IDQuNzEgNC43MSAwIDAxLTEuNjYtLjI2IDEuMzggMS4zOCAwIDAxLS44OS0uNjYgMS41NyAxLjU3IDAgMDEuMS0uNTEgMS44NiAxLjg2IDAgMDEuMjgtLjUyaC4yN2ExLjIxIDEuMjEgMCAwMS41OC4xNyAzLjkzIDMuOTMgMCAwMC42Ni4yMiAyLjg2IDIuODYgMCAwMC43LjA5aC4zNGExIDEgMCAwMC42OS0uMzIgMSAxIDAgMDAuMjQtLjcyIDEuMTYgMS4xNiAwIDAwLS4xNy0uNiAxLjgzIDEuODMgMCAwMC0uNTYtLjU1bC0uMTctLjExYy0uMDctLjA1LS4wOS0uMDctLjA2IDAtLjIyLS4xNi0uNDUtLjMyLS42OS0uNTFsLS42Mi0uNTEtLjQ4LS4zOGEyLjYyIDIuNjIgMCAwMS0uODgtMS44NiAyLjExIDIuMTEgMCAwMS44NC0xLjc5IDMuNjYgMy42NiAwIDAxMi4xOC0uNzJ6bS0yMC41MSA0LjdhNi43OSA2Ljc5IDAgMDEtLjI5IDIuMTIgNC4zIDQuMyAwIDAxLS44IDEuNTIgMy40MyAzLjQzIDAgMDEtMS4yMi45MyAzLjg3IDMuODcgMCAwMS0xLjUzLjMgMy41OCAzLjU4IDAgMDEtMi44My0xLjEyIDUuMzkgNS4zOSAwIDAxLTEtMy42NCA2LjgyIDYuODIgMCAwMS4yOS0yLjEzIDQuMjUgNC4yNSAwIDAxLjgtMS41MSAzLjIxIDMuMjEgMCAwMTEuMjMtLjg4IDQuMTggNC4xOCAwIDAxMS41Ny0uMjkgMy40MSAzLjQxIDAgMDEyLjg0IDEuMTkgNS41NSA1LjU1IDAgMDEuOTQgMy41em0tNS41NCAwYTguMTcgOC4xNyAwIDAwLjEzIDEuNjEgMy4zNyAzLjM3IDAgMDAuMzcgMSAxLjQ1IDEuNDUgMCAwMC41NC41OCAxLjQgMS40IDAgMDAuNy4xN0ExLjMgMS4zIDAgMDA5NyA1MGExLjUxIDEuNTEgMCAwMC41My0uNTggMy4zIDMuMyAwIDAwLjM3LTEgOCA4IDAgMDAuMTMtMS42IDUuMDcgNS4wNyAwIDAwLS40Ni0yLjU1IDEuNDMgMS40MyAwIDAwLTEuMjctLjc1IDEuMjggMS4yOCAwIDAwLS42NS4xOCAxLjU1IDEuNTUgMCAwMC0uNTMuNTcgMy4zNCAzLjM0IDAgMDAtLjM4IDEgNy4zNiA3LjM2IDAgMDAtLjE5IDEuNDZ6IiBmaWxsPSIjNWY1ZTVlIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiLz48cGF0aCBkPSJNNzMuNTIgNTAuNjZ2LjA4YS4xMS4xMSAwIDAxMCAuMDcgMi42OCAyLjY4IDAgMDEtLjE1LjM5LjUyLjUyIDAgMDEtLjI5LjIyaC0uMzdjLS4xOSAwLS40NiAwLS44Mi4wNWEyLjE0IDIuMTQgMCAwMS0uMzctLjQ0IDEuNzMgMS43MyAwIDAxLS4yNC0uNWwtLjMtMS4wOS0uMzgtMS4yNGMtLjEzLS40NS0uMjUtLjktLjM4LTEuMzdzLS4yNS0uOTEtLjM2LTEuMzVjLS4yMi0uOC0uNC0xLjQ1LS41My0xLjk0YTUuNjMgNS42MyAwIDAwLS4zLS45Mi45Mi45MiAwIDAxLjU4LS4zNSA0LjgyIDQuODIgMCAwMTEuMzItLjEzLjg5Ljg5IDAgMDEuMjQuMyAxLjc0IDEuNzQgMCAwMS4xMy4zNmMuMTYuNzQuMzUgMS41Mi41NSAyLjM0LjA3LjI1LjEyLjUxLjE3Ljc2cy4wOS41LjE0LjcxLjEuNTEuMTMuNjguMDcuMzMuMS40NSAwIC4yNC4wNy4zNC4wNS4yMS4wOC4zNGMuMDctLjI1LjE0LS41NS4yMi0uOTJzLjE4LS43Ny4yOC0xLjE4YzAtLjExLjA4LS4zMy4xNy0uNjVsLjI1LTFjLjA4LS4zNy4xNi0uNzEuMjMtMXMuMTMtLjU0LjE2LS42NWEuNzMuNzMgMCAwMDAtLjE2di0uMjVhLjkzLjkzIDAgMDEuMjItLjA4IDMuNjEgMy42MSAwIDAxLjQ1LS4xMmwuNTEtLjA3YTEgMSAwIDAxLjM5IDAgLjg5Ljg5IDAgMDEuMjIuMzEgMy4wNyAzLjA3IDAgMDEuMTQuNDdsLjM2IDEuNTJjLjEzLjU1LjI3IDEuMTIuNDEgMS43MiAwIC4yMi4xLjQzLjE0LjY0YTUuNjEgNS42MSAwIDAwLjEzLjU5bC4wOS4zN3YuMTdhLjI3LjI3IDAgMDEwIC4xMnYuMTljLjEzLS40Ni4yNS0xIC4zOC0xLjU3cy4yNS0xLjE5LjM5LTEuODNjLjExLS40Ny4yMi0uOTEuMzEtMS4zMXMuMTYtLjc3LjIzLTEuMTFhLjI0LjI0IDAgMDAwLS4xMy40OC40OCAwIDAxLjA5LS4xOS40My40MyAwIDAxLjIxLS4xMWguNTNsLjQuMDVoLjM2YS40OS40OSAwIDAxLjE5IDAgLjIuMiAwIDAxLjA5LjA5IDEgMSAwIDAwMCAuMS41NC41NCAwIDAxMCAuMjVjMCAuMDkgMCAuMi0uMDguMzRsLS4wOC4xOWMtLjEyLjM4LS4yNC44MS0uMzYgMS4yOEw3OS4zOSA0NmMtLjIxLjc5LS40NCAxLjYtLjY2IDIuNDJzLS40NCAxLjU3LS42NCAyLjIydi4xNWEyIDIgMCAwMS0uMTMuMzkuNTYuNTYgMCAwMS0uMjkuMjJoLS4zOWMtLjE5IDAtLjQ2IDAtLjguMDVhMi4xNSAyLjE1IDAgMDEtLjM4LS40NCAyLjExIDIuMTEgMCAwMS0uMjUtLjVjMC0uMTQtLjA4LS4zLS4xMy0uNDZzLS4wOS0uMzEtLjEyLS40NSAwLS4xOS0uMDctLjI3YTEuMjUgMS4yNSAwIDAwLS4wNy0uMjNjLS4xMS0uNDgtLjI0LTEtLjM2LTEuNTdzLS4yNS0xLjA5LS4zNS0xLjU3Yy0uMTEuNTEtLjI1IDEuMDYtLjQgMS42NnMtLjMgMS4xNS0uNDQgMS42OHptOS4xIDQuMzRhMS4zOSAxLjM5IDAgMDEtLjQyLjEzIDMuMjggMy4yOCAwIDAxLTEuNDMgMCA2IDYgMCAwMTAtLjczVjQ0LjMxYTQuNyA0LjcgMCAwMC0uMDYtLjc5IDQuODcgNC44NyAwIDAwLS4xNS0uNjMuNzQuNzQgMCAwMS4yOS0uMjZsLjUxLS4yNGguMjVhLjc0Ljc0IDAgMDEuNDQuMTMuOC44IDAgMDEuMjcuMjggNS42NCA1LjY0IDAgMDExLjE1LS41OHEuMjgtLjA5LjU3LS4xNWEyLjkgMi45IDAgMDEuNjItLjA2IDMuODcgMy44NyAwIDAxMS4zMy4yMyAyLjc4IDIuNzggMCAwMTEuMS43NCAzLjYzIDMuNjMgMCAwMS43NCAxLjMgNi4zIDYuMyAwIDAxLjE3IDEuODcgNy42NSA3LjY1IDAgMDEtLjQ4IDIuOTQgNC4yOCA0LjI4IDAgMDEtMS4yNCAxLjc0IDIuNzYgMi43NiAwIDAxLTEuMDYuNTkgNC4yNCA0LjI0IDAgMDEtMSAuMTQgMyAzIDAgMDEtMS41LS4zMnYzLjMyYTEuNjkgMS42OSAwIDAxMCAuMzh6bTEuMzItNC45YTEuNTEgMS41MSAwIDAwLjY1LS4xNiAxLjY2IDEuNjYgMCAwMC42My0uNTkgMy41NSAzLjU1IDAgMDAuNDgtMS4xNyA3LjY0IDcuNjQgMCAwMC4yLTEuODkgMy43MyAzLjczIDAgMDAtLjQ1LTIuMTEgMS40MSAxLjQxIDAgMDAtMS4yNC0uNjQgMS44IDEuOCAwIDAwLS44MS4yIDMuNzYgMy43NiAwIDAwLS42Ny40NnY1LjM5YTEuODIgMS44MiAwIDAwLjU0LjM2IDEuNTYgMS41NiAwIDAwLjY3LjE1eiIgZmlsbD0iI2I4NWExYiIgZmlsbC1ydWxlPSJldmVub2RkIi8+PHBhdGggY2xhc3M9ImNscy00IiBkPSJNLTYuMjUgMGg2MHY2MGgtNjB6Ii8+PHBhdGggZD0iTTE2LjY2IDguMTRhMTUuNDMgMTUuNDMgMCAwMC03LjkxIDEwLjE3IDIzLjUxIDIzLjUxIDAgMTAzMCAwIDE1LjQxIDE1LjQxIDAgMDAtOS4zNy0xMC44MyAzLjQgMy40IDAgMDAtMi4wOC0yLjY5IDQuNjMgNC42MyAwIDAwLTguODYtMS42NSAyNC40MSAyNC40MSAwIDAwLTEuNzggNXoiIGZpbGw9IiMzOTUzNjAiIGZpbGwtcnVsZT0iZXZlbm9kZCIvPjxwYXRoIGZpbGw9IiNmYmFhNmYiIGQ9Ik0xOCAyNmgxMnYxNEgxOHoiLz48cGF0aCBkPSJNMjUuODcgMzMuMThsLS4xMi0uMDhhMS40MiAxLjQyIDAgMTExLjY3LTIuMyAxLjg3IDEuODcgMCAwMC0xLjIyLjgxIDEuODUgMS44NSAwIDAwLS4zMyAxLjU3em0tNC40OCAwYTEuOCAxLjggMCAwMC0uMzktMS41NCAxLjkxIDEuOTEgMCAwMC0xLjIzLS44MSAxLjQyIDEuNDIgMCAwMTEuNjcgMi4zLjU3LjU3IDAgMDEtLjA1LjA1ek0yOC42MSAzMGguNTNsLTEuMDcgNC44Mi0yLjE0IDYuNDNoLTQuMjlsLTMuMjEtNS4zNiAxLjA3LTMuMjFjMS4wNyAxLjQzIDEuNzkgMi4zMiAyLjE0IDIuNjguNTQuNTMgMi42OC41MyAzLjc1LS41NEEyNi4xNyAyNi4xNyAwIDAwMjguNjEgMzB6IiBmaWxsPSIjZGM3ZjNjIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiLz48cGF0aCBkPSJNOS43NCAyOUgxNXYtOWgtNC4wNmExMyAxMyAwIDAxNy41LTEwcTEuMTQtNSAyLjcxLTYuNzVsLjE4LS4xNy4xMS0uMWEyLjI1IDIuMjUgMCAwMTEuMDgtLjQ3IDIuMzIgMi4zMiAwIDAxMi4xNSAzLjc3aC0uMDZhMS42NCAxLjY0IDAgMDEtLjMuMjlBMTUgMTUgMCAwMDIzIDguMTRhNSA1IDAgMDEzLTEuNSAxLjQgMS40IDAgMDEuNjYuMTYgMS4zMyAxLjMzIDAgMDEuNTEgMS43OSAxLjI5IDEuMjkgMCAwMS0uNi41NiAxMyAxMyAwIDAxMTAuMTQgMTFsLjEyLjg3SDMzdjhoNC44M2wxLjc5IDEzLjQzcS02LjMzIDMuOTMtMTUuODUgMy45M1Q4IDQyLjQ0em0xNS4xMyA5LjM5cTMuODctNi4zOSAzLjg3LTcuNjFjMC0yLjIzLTMuMjUtNC4wNi00Ljg3LTQuMDZTMTkgMjguNTQgMTkgMzAuNzhxMCAxLjIyIDMuODEgNy42MmExLjI0IDEuMjQgMCAwMDEuMDYuNTcgMS4wOCAxLjA4IDAgMDAxLS41NnoiIGZpbGw9IiNiZGNmYzgiIGZpbGwtcnVsZT0iZXZlbm9kZCIvPjxwYXRoIGNsYXNzPSJjbHMtNCIgZD0iTTE4Ljk2IDMxLjA3aDkuNjVMMjcgNDcuMTRoLTYuNDNsLTEuNjEtMTYuMDd6Ii8+PHBhdGggZD0iTTM5LjgxIDQ4LjgyYTIwIDIwIDAgMDEtMzIuMDkgMGwuODQtNi4xMWEyLjY4IDIuNjggMCAwMDEgLjE5IDIuODMgMi44MyAwIDAwMi44MS0yLjQzdjEuMjJhMi44NCAyLjg0IDAgMDA1LjY4IDB2MS42MmEyLjg1IDIuODUgMCAwMDUuNjkgMCAyLjg0IDIuODQgMCAwMDUuNjggMHYtMS41N2EyLjg0IDIuODQgMCAxMDUuNjggMHYtMS4yMkEyLjg0IDIuODQgMCAwMDM4IDQzYTIuODcgMi44NyAwIDAwMS0uMThsLjgxIDZ6IiBmaWxsPSIjODA5ZWIwIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiLz48cGF0aCBkPSJNOC4zIDQ0LjY1bC4yNi0xLjg5YTIuNjggMi42OCAwIDAwMSAuMTkgMi44MyAyLjgzIDAgMDAyLjgxLTIuNDN2MS4yMmEyLjg0IDIuODQgMCAwMDUuNjggMHYxLjYyYTIuODUgMi44NSAwIDAwNS42OSAwIDIuODQgMi44NCAwIDAwNS42OCAwdi0xLjYyYTIuODQgMi44NCAwIDEwNS42OCAwdi0xLjIyQTIuODQgMi44NCAwIDAwMzggNDNhMi44NyAyLjg3IDAgMDAxLS4xOGwuMjUgMS44OWEyLjg1IDIuODUgMCAwMS00LjA3LTIuMTR2MS4yMmEyLjg0IDIuODQgMCAxMS01LjY4IDB2MS42MmEyLjg0IDIuODQgMCAwMS01LjY4IDAgMi44NSAyLjg1IDAgMDEtNS42OSAwdi0xLjY3YTIuODQgMi44NCAwIDAxLTUuNjggMHYtMS4yMkEyLjgzIDIuODMgMCAwMTkuNTggNDVhMi45IDIuOSAwIDAxLTEuMjgtLjN6IiBmaWxsPSIjNzM4ZTllIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiLz48cGF0aCBjbGFzcz0iY2xzLTExIiBkPSJNMzcuNzggMjIuMzljLTEtMi44Ny0zLTQuNjktNC43Mi00LjUxLTIuMi4yMy0yLjc0IDMuNzYtMi4yOSA4czEuNyA3LjU2IDMuOSA3LjMzIDQtMy44OSAzLjY4LThjLS4wOCAxLjIzLS41MiAyLjI4LTEuMzkgMi4zNS0xLjEyLjEtMS40NC0xLjE5LTEuNTktMi44MnMtLjE0LTMgMS0zLjA4YTEuNTEgMS41MSAwIDAxMS40MS43M3oiLz48cGF0aCBjbGFzcz0iY2xzLTEyIiBkPSJNMzcgMjEuNzVjLS42My0xLjIxLTEuNS0xLjk1LTIuMzktMS44NS0xLjUxLjE1LTEuODcgMi41Ny0xLjU3IDUuNDdzMS4xNyA1LjE4IDIuNjcgNWMxLjExLS4xMiAxLjkzLTEuNSAyLjE2LTMuMzhhMS4xNiAxLjE2IDAgMDEtLjg5LjU3Yy0xLjEyLjEtMS40NC0xLjE5LTEuNTktMi44MnMtLjE0LTMgMS0zLjA4YTEuNjEgMS42MSAwIDAxLjYxLjA5eiIvPjxwYXRoIGNsYXNzPSJjbHMtMTEiIGQ9Ik05LjYgMjIuMzljMS0yLjg3IDMtNC42OSA0LjcyLTQuNTEgMi4yLjIzIDIuNzQgMy43NiAyLjI5IDhzLTEuNyA3LjU2LTMuOSA3LjMzLTQtMy44OS0zLjY4LThjLjA4IDEuMjMuNTEgMi4yOCAxLjM5IDIuMzUgMS4xMi4xIDEuNDQtMS4xOSAxLjU4LTIuODJzLjE1LTMtMS0zLjA4YTEuNTEgMS41MSAwIDAwLTEuNDMuNzF6Ii8+PHBhdGggY2xhc3M9ImNscy0xMiIgZD0iTTEwLjM3IDIxLjc1Yy42My0xLjIxIDEuNTEtMS45NSAyLjQtMS44NSAxLjUuMTUgMS44NyAyLjU3IDEuNTYgNS40N3MtMS4xNiA1LjE4LTIuNjcgNWMtMS4xMS0uMTItMS45My0xLjUtMi4xNi0zLjM4YTEuMTggMS4xOCAwIDAwLjkuNTdjMS4xMS4xIDEuNDQtMS4xOSAxLjU4LTIuODJzLjE0LTMtMS0zLjA4YTEuNjggMS42OCAwIDAwLS42NC4wN3oiLz48cGF0aCBkPSJNMTkgMjguNjNhNS4zNCA1LjM0IDAgMDEwLS42OWMwLTIuNDcgMS4yMS01LjI4IDQuODctNS4yOHM0Ljg3IDIuODEgNC44NyA1LjI4YTQuNCA0LjQgMCAwMS0uMTMgMWMtLjgtMS4zNS0yLjMtMi4xOC00LjgtMi4xOC0yLjM3LjAzLTMuOTEuNzItNC44MSAxLjg3eiIgZmlsbD0iI2Y0ZjhmZiIgZmlsbC1ydWxlPSJldmVub2RkIi8+PHBhdGggY2xhc3M9ImNscy0xMSIgZD0iTTI2LjUyIDkuMTZMMjMuMzQgOWwzLjkzLTEuMTZhMS4zNSAxLjM1IDAgMDEtLjc1IDEuMzJ6TTIzIDguMTRsLTEuMzIgMWExNi43NyAxNi43NyAwIDAwMi0zLjcyQTYuNTYgNi41NiAwIDAwMjQgMi43NSAyLjM2IDIuMzYgMCAwMTI1LjIxIDVhMi40MyAyLjQzIDAgMDEtLjc1IDEuNTFBMTUgMTUgMCAwMDIzIDguMTR6Ii8+PHBhdGggZD0iTTEyOS41OCA1My43OXYtOS4zNWgxLjQ3di45M2EyLjcyIDIuNzIgMCAwMTIuMTgtMS4wOWMxLjc1IDAgMyAxLjMxIDMgMy41NHMtMS4yNCAzLjU2LTMgMy41NmEyLjY3IDIuNjcgMCAwMS0yLjE4LTEuMTF2My41MnptMy4yMS04LjIxYTIuMjIgMi4yMiAwIDAwLTEuNzQuOTF2Mi42OGEyLjI1IDIuMjUgMCAwMDEuNzQuOTEgMiAyIDAgMDAxLjkxLTIuMjYgMiAyIDAgMDAtMS45MS0yLjI0em00LjkxLTEuMTRoMS40N3YxYTIuODkgMi44OSAwIDAxMi4yLTEuMTV2MS40NmEyIDIgMCAwMC0uNDYgMCAyLjM2IDIuMzYgMCAwMC0xLjc0Ljg5djQuNjFoLTEuNDd6bTQuNDQgMy4zOGEzLjQ4IDMuNDggMCAxMTMuNDcgMy41NiAzLjM4IDMuMzggMCAwMS0zLjQ3LTMuNTZ6bTUuNDQgMGEyIDIgMCAxMC0yIDIuMjYgMiAyIDAgMDAyLTIuMjZ6bTcuNzYgMi40N2EyLjczIDIuNzMgMCAwMS0yLjE3IDEuMDljLTEuNzMgMC0zLTEuMzItMy0zLjU1czEuMjYtMy41NSAzLTMuNTVhMi43MSAyLjcxIDAgMDEyLjE3IDEuMXYtMy41MWgxLjQ4djkuMzRoLTEuNDh6bTAtMy44YTIuMjIgMi4yMiAwIDAwLTEuNzUtLjkxIDIgMiAwIDAwLTEuOSAyLjI1IDIgMiAwIDAwMS45IDIuMjUgMi4yMiAyLjIyIDAgMDAxLjc1LS45em03Ljk0IDMuODJhMy4yMyAzLjIzIDAgMDEtMi4zOSAxLjA3IDEuOTIgMS45MiAwIDAxLTIuMTctMi4xNHYtNC44aDEuNDd2NC4yNmMwIDEgLjUzIDEuMzggMS4zNiAxLjM4YTIuMjIgMi4yMiAwIDAwMS43My0uODl2LTQuNzVoMS40N3Y2Ljc3aC0xLjQ3em02LjQ2LTYuMDNhMy4wNSAzLjA1IDAgMDEyLjU5IDEuMmwtMSAuOWExLjc5IDEuNzkgMCAwMC0xLjU1LS44IDIuMjYgMi4yNiAwIDAwMCA0LjUgMS44NyAxLjg3IDAgMDAxLjU1LS44bDEgLjg5YTMgMyAwIDAxLTIuNTkgMS4yMSAzLjU1IDMuNTUgMCAwMTAtNy4xem00LjE3IDUuMzZ2LTMuOTFoLTEuMTJ2LTEuMjloMS4xMnYtMS44NWgxLjQ3djEuODVoMS4zN3YxLjI5aC0xLjM3djMuNTVjMCAuNDYuMjIuOC42NC44YTEgMSAwIDAwLjY2LS4yNGwuMzUgMS4xYTEuOTEgMS45MSAwIDAxLTEuMzkuNDQgMS41NiAxLjU2IDAgMDEtMS43My0xLjc0em0tMTExLjcxLjg0YTIuODcgMi44NyAwIDAxLTIuMTkuOSAyLjI1IDIuMjUgMCAwMS0yLjM1LTIuMjQgMi4xOCAyLjE4IDAgMDEyLjM0LTIuMiAyLjggMi44IDAgMDEyLjE5Ljg2di0xYzAtLjc5LS42NC0xLjI2LTEuNTgtMS4yNmEyLjc5IDIuNzkgMCAwMC0yIC44NWwtLjYtMWE0LjA1IDQuMDUgMCAwMTIuODUtMS4wOWMxLjQ5IDAgMi44MS42MyAyLjgxIDIuNDV2NC40OEg2Mi4yem0wLTEuODNhMiAyIDAgMDAtMS42MS0uNyAxLjIzIDEuMjMgMCAxMDAgMi40MiAyIDIgMCAwMDEuNjEtLjd6IiBmaWxsPSIjOTk5Ii8+PC9zdmc+';

		if ( ! wp_mail_smtp()->is_pro() ) {
			$contact_url = 'https://wordpress.org/support/plugin/wp-mail-smtp/';
		} else {
			// phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
			$contact_url = esc_url( wp_mail_smtp()->get_utm_url( 'https://wpmailsmtp.com/contact/', [ 'medium' => 'setup-wizard', 'content' => 'Contact Us' ] ) );
		}

		?>
		<style type="text/css">
			#wp-mail-smtp-settings-area {
				visibility: hidden;
				animation: loadWpMailSMTPSettingsNoJSView 0s 2s forwards;
			}

			@keyframes loadWpMailSMTPSettingsNoJSView{
				to { visibility: visible; }
			}

			body {
				background: #F1F1F1;
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
				margin: 0;
			}

			#wp-mail-smtp-settings-area .wp-mail-smtp-setup-wizard-header {
				text-align: center;
				border-top: 4px solid #E27730;
			}

			#wp-mail-smtp-settings-area .wp-mail-smtp-setup-wizard-header h1 {
				margin: 0;
			}

			#wp-mail-smtp-settings-area .wp-mail-smtp-logo {
				display: inline-block;
				width: 320px;
				margin-top: 10px;
				padding: 0 10px;
			}

			#wp-mail-smtp-settings-area .wp-mail-smtp-logo img {
				width: 100%;
				height: 100%;
			}

			#wp-mail-smtp-settings-error-loading-area {
				box-sizing: border-box;
				max-width: 90%;
				width: auto;
				margin: 0 auto;
				background: #fff;
				border: 1px solid #DDDDDD;
				border-radius: 6px;
				-webkit-box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.05);
				box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.05);
				padding: 20px 30px;
			}

			#wp-mail-smtp-settings-area .wp-mail-smtp-error-footer {
				text-align: center;
				margin-top: 20px;
				font-size: 14px;
			}

			#wp-mail-smtp-settings-area .wp-mail-smtp-error-footer a {
				color: #999999;
			}

			#wp-mail-smtp-error-js h3 {
				font-size: 24px;
				font-weight: 500;
				line-height: 23px;
				margin: 0 0 15px;
				color: #444444;
			}

			#wp-mail-smtp-error-js p.info,
			#wp-mail-smtp-error-js ul.info {
				color: #777777;
				font-size: 16px;
				line-height: 23px;
				margin: 0 0 10px;
			}

			#wp-mail-smtp-error-js ul.info {
				margin: -10px 0 20px;
			}

			#wp-mail-smtp-error-js a.button {
				display: inline-block;
				background-color: #E27730;
				color: #ffffff;
				line-height: 22px;
				font-size: 16px;
				padding: 14px 30px;
				font-weight: 500;
				border-radius: 3px;
				border: none;
				cursor: pointer;
				text-decoration: none;
				margin-top: 7px;
			}

			#wp-mail-smtp-error-js a.button:hover {
				background-color: #c45e1b;
			}

			#wp-mail-smtp-error-js .medium-bold {
				font-weight: 500;
			}

			#wp-mail-smtp-nojs-error-message > div {
				border: 1px solid #DDDDDD;
				border-left: 4px solid #DC3232;
				color: #777777;
				font-size: 14px;
				padding: 18px 18px 18px 21px;
				font-weight: 300;
				text-align: left;
			}

			@media (min-width: 782px) {
				#wp-mail-smtp-settings-area .wp-mail-smtp-logo {
					margin-top: 50px;
					padding: 0;
				}

				#wp-mail-smtp-settings-error-loading-area {
					width: 650px;
					margin-top: 40px;
					padding: 52px 67px 49px;
				}

				#wp-mail-smtp-settings-area .wp-mail-smtp-error-footer {
					margin-top: 50px;
				}

				#wp-mail-smtp-error-js p.info {
					margin: 0 0 20px;
				}
			}
		</style>
		<!--[if IE]>
		<style>
			#wp-mail-smtp-settings-area{
				visibility: visible !important;
			}
		</style>
		<![endif]-->
		<div id="<?php echo esc_attr( $id ); ?>">
			<div id="wp-mail-smtp-settings-area" class="wp-mail-smtp-settings-area wpms-container">
				<header class="wp-mail-smtp-setup-wizard-header">
					<h1 class="wp-mail-smtp-setup-wizard-logo">
						<div class="wp-mail-smtp-logo">
							<img src="<?php echo esc_attr( $inline_logo_image ); ?>" alt="<?php esc_attr_e( 'WP Mail SMTP logo', 'wp-mail-smtp' ); ?>" class="wp-mail-smtp-logo-img">
						</div>
					</h1>
				</header>
				<div id="wp-mail-smtp-settings-error-loading-area-container">
					<div id="wp-mail-smtp-settings-error-loading-area">
						<div>
							<div id="wp-mail-smtp-error-js">
								<h3><?php esc_html_e( 'Whoops, something\'s not working.', 'wp-mail-smtp' ); ?></h3>
								<p class="info"><?php esc_html_e( 'It looks like something is preventing JavaScript from loading on your website. WP Mail SMTP requires JavaScript in order to give you the best possible experience.', 'wp-mail-smtp' ); ?></p>
								<p class="info">
									<?php esc_html_e( 'In order to fix this issue, please check each of the items below:', 'wp-mail-smtp' ); ?>
								</p>
								<ul class="info">
									<li><?php esc_html_e( 'If you are using an ad blocker, please disable it or whitelist the current page.', 'wp-mail-smtp' ); ?></li>
									<li><?php esc_html_e( 'If you aren\'t already using Chrome, Firefox, Safari, or Edge, then please try switching to one of these popular browsers.', 'wp-mail-smtp' ); ?></li>
									<li><?php esc_html_e( 'Confirm that your browser is updated to the latest version.', 'wp-mail-smtp' ); ?></li>
								</ul>
								<p class="info">
									<?php esc_html_e( 'If you\'ve checked each of these details and are still running into issues, then please get in touch with our support team. We’d be happy to help!', 'wp-mail-smtp' ); ?>
								</p>
								<div style="display: none;" id="wp-mail-smtp-nojs-error-message">
									<div>
										<strong style="font-weight: 500;" id="wp-mail-smtp-alert-message"></strong>
									</div>
									<p style="font-size: 14px;color: #777777;padding-bottom: 15px;"><?php esc_html_e( 'Copy the error message above and paste it in a message to the WP Mail SMTP support team.', 'wp-mail-smtp' ); ?></p>
								</div>
								<a href="<?php echo esc_url( $contact_url ); ?>" target="_blank" class="button" rel="noopener noreferrer">
									<?php esc_html_e( 'Contact Us', 'wp-mail-smtp' ); ?>
								</a>
							</div>
						</div>
					</div>
					<div class="wp-mail-smtp-error-footer">
						<?php echo wp_kses_post( $footer ); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Attempt to catch the js error preventing the Vue app from loading and displaying that message for better support.
	 *
	 * @since 2.6.0
	 */
	private function settings_inline_js() {
		?>
		<script type="text/javascript">
			window.onerror = function myErrorHandler( errorMsg, url, lineNumber ) {
				/* Don't try to put error in container that no longer exists post-vue loading */
				var message_container = document.getElementById( 'wp-mail-smtp-nojs-error-message' );
				if ( ! message_container ) {
					return false;
				}
				var message = document.getElementById( 'wp-mail-smtp-alert-message' );
				message.innerHTML = errorMsg;
				message_container.style.display = 'block';
				return false;
			}
		</script>
		<?php
	}

	/**
	 * Ajax handler for retrieving the plugin settings.
	 *
	 * @since 2.6.0
	 */
	public function get_settings() {

		check_ajax_referer( 'wpms-admin-nonce', 'nonce' );

		if ( ! current_user_can( wp_mail_smtp()->get_capability_manage_options() ) ) {
			wp_send_json_error( esc_html__( 'You don\'t have permission to change options for this WP site!', 'wp-mail-smtp' ) );
		}

		$options = Options::init();

		wp_send_json_success( $options->get_all() );
	}

	/**
	 * Ajax handler for starting the Setup Wizard steps.
	 *
	 * @since 3.1.0
	 */
	public function wizard_steps_started() {

		check_ajax_referer( 'wpms-admin-nonce', 'nonce' );

		if ( ! current_user_can( wp_mail_smtp()->get_capability_manage_options() ) ) {
			wp_send_json_error( esc_html__( 'You don\'t have permission to change options for this WP site!', 'wp-mail-smtp' ) );
		}

		self::update_stats(
			[
				'launched_time' => time(),
			]
		);

		wp_send_json_success();
	}

	/**
	 * Ajax handler for updating the settings.
	 *
	 * @since 2.6.0
	 */
	public function update_settings() {

		check_ajax_referer( 'wpms-admin-nonce', 'nonce' );

		if ( ! current_user_can( wp_mail_smtp()->get_capability_manage_options() ) ) {
			wp_send_json_error();
		}

		$options   = Options::init();
		$overwrite = ! empty( $_POST['overwrite'] );

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$value = isset( $_POST['value'] ) ? wp_slash( json_decode( wp_unslash( $_POST['value'] ), true ) ) : [];

		// Cancel summary report email task if summary report email was disabled.
		if (
			! SummaryReportEmail::is_disabled() &&
			isset( $value['general'][ SummaryReportEmail::SETTINGS_SLUG ] ) &&
			$value['general'][ SummaryReportEmail::SETTINGS_SLUG ] === true
		) {
			( new SummaryReportEmailTask() )->cancel();
		}

		/**
		 * Before updating settings in Setup Wizard.
		 *
		 * @since 3.3.0
		 *
		 * @param array $post POST data.
		 */
		do_action( 'wp_mail_smtp_admin_setup_wizard_update_settings', $value );

		$options->set( $value, false, $overwrite );

		wp_send_json_success();
	}

	/**
	 * Ajax handler for importing settings from other SMTP plugins.
	 *
	 * @since 2.6.0
	 */
	public function import_settings() {

		check_ajax_referer( 'wpms-admin-nonce', 'nonce' );

		if ( ! current_user_can( wp_mail_smtp()->get_capability_manage_options() ) ) {
			wp_send_json_error( esc_html__( 'You don\'t have permission to change options for this WP site!', 'wp-mail-smtp' ) );
		}

		$other_plugin = ! empty( $_POST['value'] ) ? sanitize_text_field( wp_unslash( $_POST['value'] ) ) : '';

		if ( empty( $other_plugin ) ) {
			wp_send_json_error();
		}

		$other_plugin_settings = ( new PluginImportDataRetriever( $other_plugin ) )->get();

		if ( empty( $other_plugin_settings ) ) {
			wp_send_json_error();
		}

		$options = Options::init();

		$options->set( $other_plugin_settings, false, false );

		wp_send_json_success();
	}

	/**
	 * Detect if any other SMTP plugin options are defined.
	 * Other SMTP plugins:
	 * - Easy WP SMTP
	 * - Post SMTP Mailer
	 * - SMTP Mailer
	 * - WP SMTP
	 * - FluentSMTP
	 *
	 * @since 2.6.0
	 * @since 3.2.0 Added FluentSMTP.
	 *
	 * @return array
	 */
	private function detect_other_smtp_plugins() {

		$data = [];

		$plugins = [
			'easy-smtp'        => 'swpsmtp_options',
			'post-smtp-mailer' => 'postman_options',
			'smtp-mailer'      => 'smtp_mailer_options',
			'wp-smtp'          => 'wp_smtp_options',
			'fluent-smtp'      => 'fluentmail-settings',
		];

		foreach ( $plugins as $plugin_slug => $plugin_options ) {
			$options = get_option( $plugin_options );

			if ( ! empty( $options ) ) {
				$data[] = $plugin_slug;
			}
		}

		return $data;
	}

	/**
	 * Prepare mailer options for all mailers.
	 *
	 * @since 2.6.0
	 * @since 3.10.0 Supply WPMS_AMAZONSES_DISPLAY_IDENTITIES constant value to control display of Amazon SES identity list.
	 * @since 3.11.0 Removed WPMS_AMAZONSES_DISPLAY_IDENTITIES constant handling.
	 *
	 * @return array
	 */
	private function prepare_mailer_options() {

		$data = [];

		foreach ( wp_mail_smtp()->get_providers()->get_options_all() as $provider ) {
			$data[ $provider->get_slug() ] = [
				'slug'        => $provider->get_slug(),
				'title'       => $provider->get_title(),
				'description' => $provider->get_description(),
				'edu_notice'  => $provider->get_notice( 'educational' ),
				'min_php'     => $provider->get_php_version(),
				'disabled'    => $provider->is_disabled(),
			];

			if ( $provider->get_slug() === 'gmail' ) {
				$data['gmail']['redirect_uri'] = \WPMailSMTP\Providers\Gmail\Auth::get_oauth_redirect_url();
			}
		}

		return apply_filters( 'wp_mail_smtp_admin_setup_wizard_prepare_mailer_options', $data );
	}

	/**
	 * AJAX callback for getting the oAuth authorization URL.
	 *
	 * @since 2.6.0
	 */
	public function get_oauth_url() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		check_ajax_referer( 'wpms-admin-nonce', 'nonce' );

		if ( ! current_user_can( wp_mail_smtp()->get_capability_manage_options() ) ) {
			wp_send_json_error();
		}

		$data   = [];
		$mailer = ! empty( $_POST['mailer'] ) ? sanitize_text_field( wp_unslash( $_POST['mailer'] ) ) : '';

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$settings = isset( $_POST['settings'] ) ? wp_slash( json_decode( wp_unslash( $_POST['settings'] ), true ) ) : [];

		if ( empty( $mailer ) ) {
			wp_send_json_error();
		}

		$settings = array_merge( $settings, [ 'is_setup_wizard_auth' => true ] );

		$options = Options::init();
		$options->set( [ $mailer => $settings ], false, false );

		switch ( $mailer ) {
			case 'gmail':
				$auth = wp_mail_smtp()->get_providers()->get_auth( 'gmail' );

				if ( $auth->is_clients_saved() && $auth->is_auth_required() ) {
					$data['oauth_url'] = $auth->get_auth_url();
				}
				break;
		}

		$data = apply_filters( 'wp_mail_smtp_admin_setup_wizard_get_oauth_url', $data, $mailer );

		wp_send_json_success( array_merge( [ 'mailer' => $mailer ], $data ) );
	}

	/**
	 * AJAX callback for getting the oAuth connected data.
	 *
	 * @since 2.6.0
	 */
	public function get_connected_data() { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded

		check_ajax_referer( 'wpms-admin-nonce', 'nonce' );

		if ( ! current_user_can( wp_mail_smtp()->get_capability_manage_options() ) ) {
			wp_send_json_error();
		}

		$data   = [];
		$mailer = ! empty( $_POST['mailer'] ) ? sanitize_text_field( wp_unslash( $_POST['mailer'] ) ) : '';

		if ( empty( $mailer ) ) {
			wp_send_json_error();
		}

		switch ( $mailer ) {
			case 'gmail':
				$auth = wp_mail_smtp()->get_providers()->get_auth( 'gmail' );

				if ( $auth->is_clients_saved() && ! $auth->is_auth_required() ) {
					$user_info               = $auth->get_user_info();
					$data['connected_email'] = $user_info['email'];
				}
				break;
		}

		wp_send_json_success( array_merge( [ 'mailer' => $mailer ], $data ) );
	}

	/**
	 * AJAX callback for removing the oAuth authorization connection.
	 *
	 * @since 2.6.0
	 */
	public function remove_oauth_connection() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		check_ajax_referer( 'wpms-admin-nonce', 'nonce' );

		if ( ! current_user_can( wp_mail_smtp()->get_capability_manage_options() ) ) {
			wp_send_json_error();
		}

		$mailer = ! empty( $_POST['mailer'] ) ? sanitize_text_field( wp_unslash( $_POST['mailer'] ) ) : '';

		if ( empty( $mailer ) ) {
			wp_send_json_error();
		}

		$options = Options::init();
		$old_opt = $options->get_all_raw();

		/*
		 * Since Gmail mailer uses the same settings array for both the custom app and One-Click Setup,
		 * we need to make sure we don't remove the wrong settings.
		 */
		if ( $mailer === 'gmail' ) {
			unset( $old_opt[ $mailer ]['access_token'] );
			unset( $old_opt[ $mailer ]['refresh_token'] );
			unset( $old_opt[ $mailer ]['user_details'] );
			unset( $old_opt[ $mailer ]['auth_code'] );
		} else {
			foreach ( $old_opt[ $mailer ] as $key => $value ) {
				// Unset everything except Client ID, Client Secret and Domain (for Zoho).
				if ( ! in_array( $key, [ 'domain', 'client_id', 'client_secret' ], true ) ) {
					unset( $old_opt[ $mailer ][ $key ] );
				}
			}
		}

		$options->set( $old_opt );

		wp_send_json_success();
	}

	/**
	 * AJAX callback for installing a plugin.
	 * Has to contain the `slug` POST parameter.
	 *
	 * @since 2.6.0
	 */
	public function install_plugin() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded

		check_ajax_referer( 'wpms-admin-nonce', 'nonce' );

		// Check for permissions.
		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( esc_html__( 'Could not install the plugin. You don\'t have permission to install plugins.', 'wp-mail-smtp' ) );
		}

		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( esc_html__( 'Could not install the plugin. You don\'t have permission to activate plugins.', 'wp-mail-smtp' ) );
		}

		$slug = ! empty( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';

		if ( empty( $slug ) ) {
			wp_send_json_error( esc_html__( 'Could not install the plugin. Plugin slug is missing.', 'wp-mail-smtp' ) );
		}

		if ( ! in_array( $slug, wp_list_pluck( $this->get_partner_plugins(), 'slug' ), true ) ) {
			wp_send_json_error( esc_html__( 'Could not install the plugin. Plugin is not whitelisted.', 'wp-mail-smtp' ) );
		}

		$url = esc_url_raw( WP::admin_url( 'admin.php?page=' . Area::SLUG . '-setup-wizard' ) );

		/*
		 * The `request_filesystem_credentials` function will output a credentials form in case of failure.
		 * We don't want that, since it will break AJAX response. So just hide output with a buffer.
		 */
		ob_start();
		// phpcs:ignore WPForms.Formatting.EmptyLineAfterAssigmentVariables.AddEmptyLine
		$creds = request_filesystem_credentials( $url, '', false, false, null );
		ob_end_clean();

		// Check for file system permissions.
		if ( false === $creds ) {
			wp_send_json_error( esc_html__( 'Could not install the plugin. Don\'t have file permission.', 'wp-mail-smtp' ) );
		}

		if ( ! WP_Filesystem( $creds ) ) {
			wp_send_json_error( esc_html__( 'Could not install the plugin. Don\'t have file permission.', 'wp-mail-smtp' ) );
		}

		// Do not allow WordPress to search/download translations, as this will break JS output.
		remove_action( 'upgrader_process_complete', [ 'Language_Pack_Upgrader', 'async_upgrade' ], 20 );

		// Import the plugin upgrader.
		Helpers::include_plugin_upgrader();

		// Create the plugin upgrader with our custom skin.
		$installer = new Plugin_Upgrader( new PluginsInstallSkin() );

		// Error check.
		if ( ! method_exists( $installer, 'install' ) || empty( $slug ) ) {
			wp_send_json_error( esc_html__( 'Could not install the plugin. WP Plugin installer initialization failed.', 'wp-mail-smtp' ) );
		}

		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		$api = plugins_api(
			'plugin_information',
			[
				'slug'   => $slug,
				'fields' => [
					'short_description' => false,
					'sections'          => false,
					'requires'          => false,
					'rating'            => false,
					'ratings'           => false,
					'downloaded'        => false,
					'last_updated'      => false,
					'added'             => false,
					'tags'              => false,
					'compatibility'     => false,
					'homepage'          => false,
					'donate_link'       => false,
				],
			]
		);

		if ( is_wp_error( $api ) ) {
			wp_send_json_error( $api->get_error_message() );
		}

		$installer->install( $api->download_link );

		// Flush the cache and return the newly installed plugin basename.
		wp_cache_flush();

		if ( $installer->plugin_info() ) {
			$plugin_basename = $installer->plugin_info();

			// Disable the WPForms redirect after plugin activation.
			if ( $slug === 'wpforms-lite' ) {
				update_option( 'wpforms_activation_redirect', true );
				add_option( 'wpforms_installation_source', 'wp-mail-smtp-setup-wizard' );
			}

			// Disable the AIOSEO redirect after plugin activation.
			if ( $slug === 'all-in-one-seo-pack' ) {
				update_option( 'aioseo_activation_redirect', true );
			}

			// Activate the plugin silently.
			$activated = activate_plugin( $plugin_basename );

			// Disable the RafflePress redirect after plugin activation.
			if ( $slug === 'rafflepress' ) {
				delete_transient( '_rafflepress_welcome_screen_activation_redirect' );
			}

			// Disable the MonsterInsights redirect after plugin activation.
			if ( $slug === 'google-analytics-for-wordpress' ) {
				delete_transient( '_monsterinsights_activation_redirect' );
			}

			// Disable the SeedProd redirect after the plugin activation.
			if ( $slug === 'coming-soon' ) {
				delete_transient( '_seedprod_welcome_screen_activation_redirect' );
			}

			if ( ! is_wp_error( $activated ) ) {
				wp_send_json_success(
					[
						'slug'         => $slug,
						'is_installed' => true,
						'is_activated' => true,
					]
				);
			} else {
				wp_send_json_success(
					[
						'slug'         => $slug,
						'is_installed' => true,
						'is_activated' => false,
					]
				);
			}
		}

		wp_send_json_error( esc_html__( 'Could not install the plugin. WP Plugin installer could not retrieve plugin information.', 'wp-mail-smtp' ) );
	}

	/**
	 * AJAX callback for getting all partner's plugin information.
	 *
	 * @since 2.6.0
	 */
	public function get_partner_plugins_info() {

		check_ajax_referer( 'wpms-admin-nonce', 'nonce' );

		$plugins = $this->get_partner_plugins();

		$contact_form_plugin_already_installed = false;

		$contact_form_basenames = [
			'wpforms-lite/wpforms.php',
			'wpforms/wpforms.php',
			'formidable/formidable.php',
			'formidable/formidable-pro.php',
			'gravityforms/gravityforms.php',
			'ninja-forms/ninja-forms.php',
		];

		$installed_plugins = get_plugins();

		foreach ( $installed_plugins as $basename => $plugin_info ) {
			if ( in_array( $basename, $contact_form_basenames, true ) ) {
				$contact_form_plugin_already_installed = true;
				break;
			}
		}

		// Final check if maybe WPForms is already install and active as a MU plugin.
		if ( class_exists( '\WPForms\WPForms' ) ) {
			$contact_form_plugin_already_installed = true;
		}

		$data = [
			'plugins'                               => $plugins,
			'contact_form_plugin_already_installed' => $contact_form_plugin_already_installed,
		];

		wp_send_json_success( $data );
	}

	/**
	 * Get the partner plugins data.
	 *
	 * @since 3.3.0
	 *
	 * @return array[]
	 */
	private function get_partner_plugins() {

		$installed_plugins = get_plugins();

		return [
			[
				'slug'         => 'wpforms-lite',
				'name'         => esc_html__( 'Contact Forms by WPForms', 'wp-mail-smtp' ),
				'is_activated' => function_exists( 'wpforms' ),
				'is_installed' => array_key_exists( 'wpforms-lite/wpforms.php', $installed_plugins ),
			],
			[
				'slug'         => 'all-in-one-seo-pack',
				'name'         => esc_html__( 'All in One SEO', 'wp-mail-smtp' ),
				'is_activated' => class_exists( 'AIOSEOP_Core' ),
				'is_installed' => array_key_exists( 'all-in-one-seo-pack/all_in_one_seo_pack.php', $installed_plugins ),
			],
			[
				'slug'         => 'google-analytics-for-wordpress',
				'name'         => esc_html__( 'Google Analytics by MonsterInsights', 'wp-mail-smtp' ),
				'is_activated' => function_exists( 'MonsterInsights' ),
				'is_installed' => array_key_exists( 'google-analytics-for-wordpress/googleanalytics.php', $installed_plugins ),
			],
			[
				'slug'         => 'insert-headers-and-footers',
				'name'         => esc_html__( 'Code Snippets by WPCode', 'wp-mail-smtp' ),
				'is_activated' => class_exists( 'InsertHeadersAndFooters' ),
				'is_installed' => array_key_exists( 'insert-headers-and-footers/ihaf.php', $installed_plugins ),
			],
			[
				'slug'         => 'rafflepress',
				'name'         => esc_html__( 'Giveaways by RafflePress', 'wp-mail-smtp' ),
				'is_activated' => defined( 'RAFFLEPRESS_BUILD' ),
				'is_installed' => array_key_exists( 'rafflepress/rafflepress.php', $installed_plugins ),
			],
			[
				'slug'         => 'instagram-feed',
				'name'         => esc_html__( 'Smash Balloon Social Photo Feed', 'wp-mail-smtp' ),
				'is_activated' => function_exists( 'sb_instagram_feed_init' ),
				'is_installed' => array_key_exists( 'instagram-feed/instagram-feed.php', $installed_plugins ),
			],
			[
				'slug'         => 'coming-soon',
				'name'         => esc_html__( 'SeedProd Landing Page Builder', 'wp-mail-smtp' ),
				'is_activated' => defined( 'SEEDPROD_BUILD' ),
				'is_installed' => array_key_exists( 'coming-soon/coming-soon.php', $installed_plugins ),
			],
			[
				'slug'         => 'wp-call-button',
				'name'         => esc_html__( 'WP Call Button', 'wp-mail-smtp' ),
				'is_activated' => defined( 'WP_CALL_BUTTON_VERSION' ),
				'is_installed' => array_key_exists( 'wp-call-button/wp-call-button.php', $installed_plugins ),
			],
		];
	}

	/**
	 * AJAX callback for subscribing an email address to the WP Mail SMTP Drip newsletter.
	 *
	 * @since 2.6.0
	 */
	public function subscribe_to_newsletter() {

		check_ajax_referer( 'wpms-admin-nonce', 'nonce' );

		$email = ! empty( $_POST['email'] ) ? filter_var( wp_unslash( $_POST['email'] ), FILTER_VALIDATE_EMAIL ) : '';

		if ( empty( $email ) ) {
			wp_send_json_error();
		}

		$body = [
			'email' => base64_encode( $email ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		];

		$wpforms_version_type = $this->get_wpforms_version_type();

		if ( ! empty( $wpforms_version_type ) ) {
			$body['wpforms_version_type'] = $wpforms_version_type;
		}

		wp_remote_post(
			'https://connect.wpmailsmtp.com/subscribe/drip/',
			[
				'user-agent' => Helpers::get_default_user_agent(),
				'body' => $body,
			]
		);

		wp_send_json_success();
	}

	/**
	 * Get the WPForms version type if it's installed.
	 *
	 * @since 3.9.0
	 *
	 * @return false|string Return `false` if WPForms is not installed, otherwise return either `lite` or `pro`.
	 */
	private function get_wpforms_version_type() {

		if ( ! function_exists( 'wpforms' ) ) {
			return false;
		}

		if ( method_exists( wpforms(), 'is_pro' ) ) {
			$is_wpforms_pro = wpforms()->is_pro();
		} else {
			$is_wpforms_pro = wpforms()->pro;
		}

		return $is_wpforms_pro ? 'pro' : 'lite';
	}

	/**
	 * AJAX callback for plugin upgrade, from lite to pro.
	 *
	 * @since 2.6.0
	 */
	public function upgrade_plugin() {

		check_ajax_referer( 'wpms-admin-nonce', 'nonce' );

		if ( wp_mail_smtp()->is_pro() ) {
			wp_send_json_success( esc_html__( 'You are already using the WP Mail SMTP PRO version. Please refresh this page and verify your license key.', 'wp-mail-smtp' ) );
		}

		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( esc_html__( 'You don\'t have the permission to perform this action.', 'wp-mail-smtp' ) );
		}

		$license_key = ! empty( $_POST['license_key'] ) ? sanitize_key( $_POST['license_key'] ) : '';

		if ( empty( $license_key ) ) {
			wp_send_json_error( esc_html__( 'Please enter a valid license key!', 'wp-mail-smtp' ) );
		}

		$url = Connect::generate_url(
			$license_key,
			'',
			add_query_arg( 'upgrade-redirect', '1', self::get_site_url() ) . '#/step/license'
		);

		if ( empty( $url ) ) {
			wp_send_json_error( esc_html__( 'Upgrade functionality not available!', 'wp-mail-smtp' ) );
		}

		wp_send_json_success( [ 'redirect_url' => $url ] );
	}

	/**
	 * AJAX callback for checking the mailer configuration.
	 * - Send a test email
	 * - Check the domain setup with the Domain Checker API.
	 *
	 * @since 2.6.0
	 */
	public function check_mailer_configuration() {

		check_ajax_referer( 'wpms-admin-nonce', 'nonce' );

		// Send the test mail.
		$result = wp_mail(
			$this->get_test_email_recipient(),
			'WP Mail SMTP Automatic Email Test',
			TestTab::get_email_message_text(),
			[
				'X-Mailer-Type:WPMailSMTP/Admin/SetupWizard/Test',
			]
		);

		if ( ! $result ) {
			$this->update_completed_stat( false );

			( new UsageTracking() )->send_failed_setup_wizard_usage_tracking_data();

			wp_send_json_error();
		}

		$options    = Options::init();
		$mailer     = $options->get( 'mail', 'mailer' );
		$from_email = $options->get( 'mail', 'from_email' );
		$domain     = '';

		// Add the optional sending domain parameter.
		if ( in_array( $mailer, [ 'mailgun', 'sendinblue', 'sendgrid' ], true ) ) {
			$domain = $options->get( $mailer, 'domain' );
		}

		// Perform the domain checker API test.
		$domain_checker = new DomainChecker( $mailer, $from_email, $domain );

		if ( $domain_checker->has_errors() ) {
			$this->update_completed_stat( false );

			( new UsageTracking() )->send_failed_setup_wizard_usage_tracking_data( $domain_checker );

			wp_send_json_error();
		}

		$this->update_completed_stat( true );

		wp_send_json_success();
	}

	/**
	 * Get the test email recipient.
	 *
	 * @since 4.7.0
	 *
	 * @return string
	 */
	private function get_test_email_recipient() {

		$options    = Options::init();
		$mailer     = $options->get( 'mail', 'mailer' );
		$from_email = $options->get( 'mail', 'from_email' );

		/*
		 * Some mailers in a test mode allows to send emails only to the registered
		 * From email address, so we need to cover this case.
		 */
		$to_email = $from_email;

		$mailer_specific_constant_name = 'WPMS_SETUP_WIZARD_TEST_' . strtoupper( $mailer ) . '_EMAIL_RECIPIENT';

		if (
			defined( $mailer_specific_constant_name ) &&
			is_email( constant( $mailer_specific_constant_name ) )
		) {
			$to_email = constant( $mailer_specific_constant_name );
		} elseif (
			defined( 'WPMS_SETUP_WIZARD_TEST_EMAIL_RECIPIENT' ) &&
			is_email( WPMS_SETUP_WIZARD_TEST_EMAIL_RECIPIENT )
		) {
			$to_email = WPMS_SETUP_WIZARD_TEST_EMAIL_RECIPIENT;
		}

		return $to_email;
	}

	/**
	 * AJAX callback for sending feedback.
	 *
	 * @since 2.6.0
	 */
	public function send_feedback() {

		check_ajax_referer( 'wpms-admin-nonce', 'nonce' );

		if ( ! current_user_can( wp_mail_smtp()->get_capability_manage_options() ) ) {
			wp_send_json_error();
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$data = ! empty( $_POST['data'] ) ? json_decode( wp_unslash( $_POST['data'] ), true ) : [];

		$feedback   = ! empty( $data['feedback'] ) ? sanitize_textarea_field( $data['feedback'] ) : '';
		$permission = ! empty( $data['permission'] );

		wp_remote_post(
			'https://wpmailsmtp.com/wizard-feedback/',
			[
				'user-agent' => Helpers::get_default_user_agent(),
				'body' => [
					'wpforms' => [
						'id'     => 87892,
						'fields' => [
							'1' => $feedback,
							'2' => $permission ? wp_get_current_user()->user_email : '',
							'3' => wp_mail_smtp()->get_license_type(),
							'4' => WPMS_PLUGIN_VER,
						],
					],
				],
			]
		);

		wp_send_json_success();
	}

	/**
	 * Data used for the Vue scripts to display old PHP and WP versions warnings.
	 *
	 * @since 2.6.0
	 */
	private function prepare_versions_data() {

		global $wp_version;

		return array(
			'php_version'          => phpversion(),
			'php_version_below_55' => apply_filters( 'wp_mail_smtp_temporarily_hide_php_under_55_upgrade_warnings', version_compare( phpversion(), '5.5', '<' ) ),
			'php_version_below_56' => apply_filters( 'wp_mail_smtp_temporarily_hide_php_56_upgrade_warnings', version_compare( phpversion(), '5.6', '<' ) ),
			'wp_version'           => $wp_version,
			'wp_version_below_49'  => version_compare( $wp_version, '4.9', '<' ),
		);
	}

	/**
	 * Remove 'error' from the automatic clearing list of query arguments after page loads.
	 * This will fix the issue with missing oAuth 'error' argument for the Setup Wizard.
	 *
	 * @since 2.6.0
	 *
	 * @param array $defaults Array of query arguments to be cleared after page load.
	 *
	 * @return array
	 */
	public function maybe_disable_automatic_query_args_removal( $defaults ) {

		if (
			( isset( $_GET['page'] ) && $_GET['page'] === 'wp-mail-smtp-setup-wizard' ) && // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			( ! empty( $_GET['error'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		) {
			$defaults = array_values( array_diff( $defaults, [ 'error' ] ) );
		}

		return $defaults;
	}

	/**
	 * Check if the Setup Wizard should load.
	 *
	 * @since 2.6.0
	 *
	 * @return bool
	 */
	public function should_setup_wizard_load() {

		return (bool) apply_filters( 'wp_mail_smtp_admin_setup_wizard_load_wizard', true );
	}

	/**
	 * Get the Setup Wizard stats.
	 * - launched_time  -> when the Setup Wizard was last launched.
	 * - completed_time -> when the Setup Wizard was last completed.
	 * - was_successful -> if the Setup Wizard was completed successfully.
	 *
	 * @since 3.1.0
	 *
	 * @return array
	 */
	public static function get_stats() {

		$defaults = [
			'launched_time'  => 0,
			'completed_time' => 0,
			'was_successful' => false,
		];

		return get_option( self::STATS_OPTION_KEY, $defaults );
	}

	/**
	 * Update the Setup Wizard stats.
	 *
	 * @since 3.1.0
	 *
	 * @param array $options Take a look at SetupWizard::get_stats method for the possible array keys.
	 */
	public static function update_stats( $options ) {

		update_option( self::STATS_OPTION_KEY, array_merge( self::get_stats(), $options ) , false );
	}

	/**
	 * Update the completed Setup Wizard stats.
	 *
	 * @since 3.1.0
	 *
	 * @param bool $was_successful If the Setup Wizard was completed successfully.
	 */
	private function update_completed_stat( $was_successful ) {

		self::update_stats(
			[
				'completed_time' => time(),
				'was_successful' => $was_successful,
			]
		);
	}

	/**
	 * Prepare an array of WP Mail SMTP PHP constants in use.
	 * Those that are used in the setup wizard.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	private function prepare_defined_constants() {

		$options = Options::init();

		if ( ! $options->is_const_enabled() ) {
			return [];
		}

		$constants = [
			'WPMS_MAIL_FROM'                     => [ 'mail', 'from_email' ],
			'WPMS_MAIL_FROM_FORCE'               => [ 'mail', 'from_email_force' ],
			'WPMS_MAIL_FROM_NAME'                => [ 'mail', 'from_name' ],
			'WPMS_MAIL_FROM_NAME_FORCE'          => [ 'mail', 'from_name_force' ],
			'WPMS_MAILER'                        => [ 'mail', 'mailer' ],
			'WPMS_SMTPCOM_API_KEY'               => [ 'smtpcom', 'api_key' ],
			'WPMS_SMTPCOM_CHANNEL'               => [ 'smtpcom', 'channel' ],
			'WPMS_SENDINBLUE_API_KEY'            => [ 'sendinblue', 'api_key' ],
			'WPMS_SENDINBLUE_DOMAIN'             => [ 'sendinblue', 'domain' ],
			'WPMS_AMAZONSES_CLIENT_ID'           => [ 'amazonses', 'client_id' ],
			'WPMS_AMAZONSES_CLIENT_SECRET'       => [ 'amazonses', 'client_secret' ],
			'WPMS_AMAZONSES_REGION'              => [ 'amazonses', 'region' ],
			'WPMS_GMAIL_CLIENT_ID'               => [ 'gmail', 'client_id' ],
			'WPMS_GMAIL_CLIENT_SECRET'           => [ 'gmail', 'client_secret' ],
			'WPMS_MAILGUN_API_KEY'               => [ 'mailgun', 'api_key' ],
			'WPMS_MAILGUN_DOMAIN'                => [ 'mailgun', 'domain' ],
			'WPMS_MAILGUN_REGION'                => [ 'mailgun', 'region' ],
			'WPMS_OUTLOOK_CLIENT_ID'             => [ 'outlook', 'client_id' ],
			'WPMS_OUTLOOK_CLIENT_SECRET'         => [ 'outlook', 'client_secret' ],
			'WPMS_POSTMARK_SERVER_API_TOKEN'     => [ 'postmark', 'server_api_token' ],
			'WPMS_POSTMARK_MESSAGE_STREAM'       => [ 'postmark', 'message_stream' ],
			'WPMS_SENDGRID_API_KEY'              => [ 'sendgrid', 'api_key' ],
			'WPMS_SENDGRID_DOMAIN'               => [ 'sendgrid', 'domain' ],
			'WPMS_SPARKPOST_API_KEY'             => [ 'sparkpost', 'api_key' ],
			'WPMS_SPARKPOST_REGION'              => [ 'sparkpost', 'region' ],
			'WPMS_ZOHO_DOMAIN'                   => [ 'zoho', 'domain' ],
			'WPMS_ZOHO_CLIENT_ID'                => [ 'zoho', 'client_id' ],
			'WPMS_ZOHO_CLIENT_SECRET'            => [ 'zoho', 'client_secret' ],
			'WPMS_RESEND_API_KEY'                => [ 'resend', 'api_key' ],
			'WPMS_SMTP_HOST'                     => [ 'smtp', 'host' ],
			'WPMS_SMTP_PORT'                     => [ 'smtp', 'port' ],
			'WPMS_SSL'                           => [ 'smtp', 'encryption' ],
			'WPMS_SMTP_AUTH'                     => [ 'smtp', 'auth' ],
			'WPMS_SMTP_AUTOTLS'                  => [ 'smtp', 'autotls' ],
			'WPMS_SMTP_USER'                     => [ 'smtp', 'user' ],
			'WPMS_SMTP_PASS'                     => [ 'smtp', 'pass' ],
			'WPMS_LOGS_ENABLED'                  => [ 'logs', 'enabled' ],
			'WPMS_SUMMARY_REPORT_EMAIL_DISABLED' => [ 'general', SummaryReportEmail::SETTINGS_SLUG ],
		];

		$defined = [];

		foreach ( $constants as $constant => $group_and_key ) {
			if ( $options->is_const_defined( $group_and_key[0], $group_and_key[1] ) ) {
				$defined[] = $constant;
			}
		}

		return $defined;
	}
}
