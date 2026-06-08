<?php

namespace WPMailSMTP\Admin\Pages;

use WPMailSMTP\Admin\ConnectionSettings;
use WPMailSMTP\Admin\PageAbstract;
use WPMailSMTP\Admin\SetupWizard;
use WPMailSMTP\Options;
use WPMailSMTP\WP;

/**
 * Class SettingsTab is part of Area, displays general settings of the plugin.
 *
 * @since 1.0.0
 */
class SettingsTab extends PageAbstract {

	/**
	 * Settings constructor.
	 *
	 * @since 1.5.0
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'wp_mail_smtp_admin_pages_settings_license_key', array( __CLASS__, 'display_license_key_field_content' ) );
	}

	/**
	 * @var string Slug of a tab.
	 */
	protected $slug = 'settings';

	/**
	 * @inheritdoc
	 */
	public function get_label() {
		return esc_html__( 'General', 'wp-mail-smtp' );
	}

	/**
	 * @inheritdoc
	 */
	public function get_title() {
		return $this->get_label();
	}

	/**
	 * @inheritdoc
	 */
	public function display() {

		$options = Options::init();
		?>

		<form method="POST" action="" autocomplete="off" class="wp-mail-smtp-connection-settings-form">
			<?php $this->wp_nonce_field(); ?>

			<?php ob_start(); ?>

			<!-- License Section Title -->
			<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-content wp-mail-smtp-clear section-heading" id="wp-mail-smtp-setting-row-license-heading">
				<div class="wp-mail-smtp-setting-field">
					<h2><?php esc_html_e( 'License', 'wp-mail-smtp' ); ?></h2>

					<p class="desc">
						<?php esc_html_e( 'Your license key provides access to updates and support.', 'wp-mail-smtp' ); ?>
					</p>
				</div>
			</div>

			<?php if ( ! wp_mail_smtp()->is_pro() ) : ?>
				<div class="wp-mail-smtp-upgrade-license-banner">
					<p><?php echo wp_kses( __( 'You\'re using <strong>WP Mail SMTP Lite</strong> - no license needed. Enjoy!', 'wp-mail-smtp' ), [ 'strong' => [] ] ); ?> 🙂</p>

					<p class="wp-mail-smtp-upgrade-license-banner__discount-line">
						<?php
						printf(
							wp_kses( /* Translators: %s - discount value $50 */
								__( 'As a valued WP Mail SMTP Lite user, you can enjoy an exclusive <strong>%s discount</strong>, automatically applied at checkout to unlock even more features!', 'wp-mail-smtp' ),
								[
									'strong' => [],
									'br'     => [],
								]
							),
							'$50'
						);
						?>
					</p>

					<a href="<?php echo esc_url( wp_mail_smtp()->get_upgrade_link( 'general-license-key' ) ); ?>" target="_blank" rel="noopener noreferrer" class="wp-mail-smtp-btn wp-mail-smtp-btn-md wp-mail-smtp-btn-secondary wp-mail-smtp-upgrade-license-banner__upgrade-btn"><?php esc_html_e( 'Upgrade to Pro', 'wp-mail-smtp' ); ?></a>
				</div>
			<?php endif; ?>

			<!-- License Key -->
			<div id="wp-mail-smtp-setting-row-license_key" class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-license_key wp-mail-smtp-clear">
				<div class="wp-mail-smtp-setting-label">
					<label for="wp-mail-smtp-setting-license_key"><?php esc_html_e( 'License Key', 'wp-mail-smtp' ); ?></label>
				</div>
				<div class="wp-mail-smtp-setting-field">
					<?php do_action( 'wp_mail_smtp_admin_pages_settings_license_key', $options ); ?>
				</div>
			</div>

			<!-- Mail Section Title -->
			<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-content wp-mail-smtp-clear section-heading no-desc">
				<div class="wp-mail-smtp-setting-field">
					<h2><?php esc_html_e( 'Primary Connection', 'wp-mail-smtp' ); ?></h2>
				</div>
			</div>

			<?php if ( ! is_network_admin() ) : ?>
				<!-- Setup Wizard button -->
				<div id="wp-mail-smtp-setting-row-setup-wizard-button" class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-email wp-mail-smtp-clear">
					<div class="wp-mail-smtp-setting-label">
						<label for="wp-mail-smtp-setting-from_email"><?php esc_html_e( 'Setup Wizard', 'wp-mail-smtp' ); ?></label>
					</div>
					<div class="wp-mail-smtp-setting-field">
						<a href="<?php echo esc_url( SetupWizard::get_site_url() ); ?>" class="wp-mail-smtp-btn wp-mail-smtp-btn-md wp-mail-smtp-btn-blueish">
							<?php esc_html_e( 'Launch Setup Wizard', 'wp-mail-smtp' ); ?>
						</a>

						<p class="desc">
							<?php esc_html_e( 'We\'ll guide you through each step needed to get WP Mail SMTP fully set up on your site.', 'wp-mail-smtp' ); ?>
						</p>
					</div>
				</div>
			<?php endif; ?>

			<?php
			$connection          = wp_mail_smtp()->get_connections_manager()->get_primary_connection();
			$connection_settings = new ConnectionSettings( $connection );

			// Display connection settings.
			$connection_settings->display();
			?>

			<?php $this->display_backup_connection_education(); ?>

			<?php
			$settings_content = apply_filters( 'wp_mail_smtp_admin_settings_tab_display', ob_get_clean() );
			echo $settings_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>

			<?php $this->display_save_btn(); ?>

		</form>

		<?php
		$this->display_wpforms();
		$this->display_pro_banner();
	}

	/**
	 * License key text for a Lite version of the plugin.
	 *
	 * @since 1.5.0
	 *
	 * @param Options $options
	 */
	public static function display_license_key_field_content( $options ) {
		?>
		<p>
			<?php esc_html_e( 'Already purchased? Simply enter your license key below to connect with WP Mail SMTP Pro!', 'wp-mail-smtp' ); ?>
		</p>

		<p>
			<input type="password" id="wp-mail-smtp-setting-upgrade-license-key" class="wp-mail-smtp-not-form-input" placeholder="<?php esc_attr_e( 'Paste license key here', 'wp-mail-smtp' ); ?>" value="" />
			<button type="button" class="wp-mail-smtp-btn wp-mail-smtp-btn-md wp-mail-smtp-btn-orange" id="wp-mail-smtp-setting-upgrade-license-button">
				<?php esc_attr_e( 'Connect', 'wp-mail-smtp' ); ?>
			</button>
		</p>

		<?php
	}

	/**
	 * Display a WPForms related message.
	 *
	 * @since 1.3.0
	 * @since 1.4.0 Display only to site admins.
	 * @since 1.5.0 Do nothing.
	 */
	protected function display_wpforms() {
		/*
		 * Used to have this check:
		 *
		 * $is_dismissed = get_user_meta( get_current_user_id(), 'wp_mail_smtp_wpforms_dismissed', true );
		 */
	}

	/**
	 * Display WP Mail SMTP Pro upgrade banner.
	 *
	 * @since 1.5.0
	 */
	protected function display_pro_banner() {

		// Display only to site admins. Only site admins can install plugins.
		if ( ! is_super_admin() ) {
			return;
		}

		// Do not display if WP Mail SMTP Pro already installed.
		if ( wp_mail_smtp()->is_pro() ) {
			return;
		}

		$is_dismissed = get_user_meta( get_current_user_id(), 'wp_mail_smtp_pro_banner_dismissed', true );

		// Do not display if user dismissed.
		if ( (bool) $is_dismissed === true ) {
			return;
		}

		$assets_url  = wp_mail_smtp()->assets_url;
		$screenshots = [
			[
				'url'           => $assets_url . '/images/logs/archive.png',
				'url_thumbnail' => $assets_url . '/images/logs/archive-thumbnail.png',
				'title'         => __( 'Email Logs', 'wp-mail-smtp' ),
			],
			[
				'url'           => $assets_url . '/images/logs/single.png',
				'url_thumbnail' => $assets_url . '/images/logs/single-thumbnail.png',
				'title'         => __( 'Individual Email Log', 'wp-mail-smtp' ),
			],
			[
				'url'           => $assets_url . '/images/email-reports/screenshot-01.png',
				'url_thumbnail' => $assets_url . '/images/email-reports/thumbnail-01.png',
				'title'         => __( 'Email Reports', 'wp-mail-smtp' ),
			],
		];
		?>

		<div id="wp-mail-smtp-pro-banner" class="wp-mail-smtp-upgrade-banner">
			<span class="wp-mail-smtp-pro-banner-dismiss">
				<button id="wp-mail-smtp-pro-banner-dismiss">
					<img src="<?php echo esc_url( wp_mail_smtp()->assets_url . '/images/icons/close.svg' ); ?>" alt="<?php esc_attr_e( 'Close', 'wp-mail-smtp' ); ?>">
				</button>
			</span>

			<div class="wp-mail-smtp-upgrade-banner__row">
				<h3 class="wp-mail-smtp-upgrade-banner__heading">
					<?php esc_html_e( 'Level Up Your Email Game - Get Pro Features Now', 'wp-mail-smtp' ); ?>
				</h3>
				<p class="wp-mail-smtp-upgrade-banner__subheading">
					<?php echo wp_kses( __( 'Upgrade and join over <strong>4,000,000</strong> websites!', 'wp-mail-smtp' ), [ 'strong' => [] ] ); ?>
				</p>
			</div>

			<div class="wp-mail-smtp-upgrade-banner__row">
				<h3 class="wp-mail-smtp-upgrade-banner__heading">
					<?php esc_html_e( 'Key Features You’ll Unlock:', 'wp-mail-smtp' ); ?>
				</h3>

				<ul class="wp-mail-smtp-upgrade-banner__features">
					<li>
						<h5><?php esc_html_e( 'Peace of Mind - Never wonder about your email status', 'wp-mail-smtp' ); ?></h5>
						<p><?php esc_html_e( 'Email Logging, Alerts, and Backup Connection', 'wp-mail-smtp' ); ?></p>
					</li>
					<li>
						<h5><?php esc_html_e( 'Professional Email Services - Access enterprise-grade email providers', 'wp-mail-smtp' ); ?></h5>
						<p><?php esc_html_e( 'Gmail one-click setup, Microsoft 365 / Outlook, Amazon SES, and Zoho Mail', 'wp-mail-smtp' ); ?></p>
					</li>
					<li>
						<h5><?php esc_html_e( 'Effortless Management - Control your email experience', 'wp-mail-smtp' ); ?></h5>
						<p><?php esc_html_e( 'Smart Routing, Multisite Support, and Manage Notifications', 'wp-mail-smtp' ); ?></p>
					</li>
					<li>
						<h5><?php esc_html_e( 'White Glove Setup - Sit back while we handle everything', 'wp-mail-smtp' ); ?></h5>
						<p><?php esc_html_e( 'Professional Setup and World-Class Support', 'wp-mail-smtp' ); ?></p>
					</li>
				</ul>
			</div>

			<div class="wp-mail-smtp-upgrade-banner__row">
				<a href="<?php echo esc_url( wp_mail_smtp()->get_upgrade_link( 'general-cta' ) ); ?>" target="_blank" rel="noopener noreferrer" class="wp-mail-smtp-btn wp-mail-smtp-btn-secondary wp-mail-smtp-upgrade-banner__upgrade-btn">
					<?php esc_html_e( 'Upgrade to WP Mail SMTP Pro', 'wp-mail-smtp' ); ?>
				</a>

				<div class="wp-mail-smtp-upgrade-banner__discount-line">
					<img src="<?php echo esc_url( wp_mail_smtp()->assets_url . '/images/icons/badge-percent.svg' ); ?>" alt="<?php esc_attr_e( 'Discount', 'wp-mail-smtp' ); ?>">
					<p>
						<?php
						printf(
							wp_kses( /* Translators: %s - discount value $50. */
								__( '<strong>%s OFF</strong> for WP Mail SMTP users, applied at checkout.', 'wp-mail-smtp' ),
								[
									'strong' => [],
								]
							),
							'$50'
						);
						?>
					</p>
				</div>
			</div>

			<div class="wp-mail-smtp-upgrade-banner__row">
				<div class="wp-mail-smtp-product-education__screenshots wp-mail-smtp-product-education__screenshots--three">
					<?php foreach ( $screenshots as $screenshot ) : ?>
						<div>
							<a href="<?php echo esc_url( $screenshot['url'] ); ?>" data-lity data-lity-desc="<?php echo esc_attr( $screenshot['title'] ); ?>">
								<img src="<?php echo esc_url( $screenshot['url_thumbnail'] ); ?>" alt="<?php esc_attr( $screenshot['title'] ); ?>">
							</a>
							<span><?php echo esc_html( $screenshot['title'] ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Display backup connection education section.
	 *
	 * @since 3.7.0
	 */
	private function display_backup_connection_education() {

		if ( wp_mail_smtp()->is_pro() ) {
			return;
		}

		$upgrade_link_url = wp_mail_smtp()->get_upgrade_link(
			[
				'medium'  => 'Backup Connection Settings',
				'content' => 'Upgrade to WP Mail SMTP Pro Link',
			]
		);
		?>
		<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-content wp-mail-smtp-clear section-heading">
			<div class="wp-mail-smtp-setting-field">
				<h4 class="wp-mail-smtp-product-education__heading">
					<?php esc_html_e( 'Backup Connection', 'wp-mail-smtp' ); ?>
				</h4>
				<p class="wp-mail-smtp-product-education__description">
					<?php
					echo wp_kses(
						sprintf( /* translators: %s - WPMailSMTP.com Upgrade page URL. */
							__( 'Don’t worry about losing emails. Add an additional connection, then set it as your Backup Connection. Emails that fail to send with the Primary Connection will be sent via the selected Backup Connection. <a href="%s" target="_blank" rel="noopener noreferrer">Upgrade to WP Mail SMTP Pro!</a>', 'wp-mail-smtp' ),
							esc_url( $upgrade_link_url )
						),
						[
							'a' => [
								'href'   => [],
								'rel'    => [],
								'target' => [],
							],
						]
					);
					?>
				</p>
			</div>
		</div>
		<div class="wp-mail-smtp-setting-row wp-mail-smtp-clear">
			<div class="wp-mail-smtp-setting-label">
				<label>
					<?php esc_html_e( 'Backup Connection', 'wp-mail-smtp' ); ?>
				</label>
			</div>
			<div class="wp-mail-smtp-setting-field">
				<div class="wp-mail-smtp-connection-selector">
					<label>
						<input type="radio" checked/>
						<span><?php esc_attr_e( 'None', 'wp-mail-smtp' ); ?></span>
					</label>
				</div>
				<p class="desc">
					<?php
					echo wp_kses(
						sprintf( /* translators: %s - Smart routing settings page url. */
							__( 'Once you add an <a href="%s">additional connection</a>, you can select it here.', 'wp-mail-smtp' ),
							add_query_arg(
								[
									'tab' => 'connections',
								],
								wp_mail_smtp()->get_admin()->get_admin_page_url()
							)
						),
						[
							'a' => [
								'href'   => [],
								'target' => [],
								'rel'    => [],
							],
						]
					);
					?>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Process tab form submission ($_POST ).
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Post data specific for the plugin.
	 */
	public function process_post( $data ) {

		$connection          = wp_mail_smtp()->get_connections_manager()->get_primary_connection();
		$connection_settings = new ConnectionSettings( $connection );

		$old_data = $connection->get_options()->get_all();

		$data = $connection_settings->process( $data, $old_data );

		/**
		 * Filters mail settings before save.
		 *
		 * @since 2.2.1
		 *
		 * @param array $data Settings data.
		 */
		$data = apply_filters( 'wp_mail_smtp_settings_tab_process_post', $data );

		// All the sanitization is done in Options class.
		Options::init()->set( $data, false, false );

		$connection_settings->post_process( $data, $old_data );

		if ( $connection_settings->get_scroll_to() !== false ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			wp_safe_redirect( sanitize_text_field( wp_unslash( $_POST['_wp_http_referer'] ) ) . $connection_settings->get_scroll_to() );
			exit;
		}

		WP::add_admin_notice(
			esc_html__( 'Settings were successfully saved.', 'wp-mail-smtp' ),
			WP::ADMIN_NOTICE_SUCCESS
		);
	}
}
