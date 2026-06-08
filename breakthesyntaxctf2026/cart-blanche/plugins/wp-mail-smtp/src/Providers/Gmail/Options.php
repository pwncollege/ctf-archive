<?php

namespace WPMailSMTP\Providers\Gmail;

use WPMailSMTP\Admin\ConnectionSettings;
use WPMailSMTP\ConnectionInterface;
use WPMailSMTP\Helpers\UI;
use WPMailSMTP\Providers\OptionsAbstract;

/**
 * Class Option.
 *
 * @since 1.0.0
 */
class Options extends OptionsAbstract {

	/**
	 * Mailer slug.
	 *
	 * @since 1.5.0
	 */
	const SLUG = 'gmail';

	/**
	 * Gmail Options constructor.
	 *
	 * @since 1.0.0
	 * @since 2.3.0 Added supports parameter.
	 *
	 * @param ConnectionInterface $connection The Connection object.
	 */
	public function __construct( $connection = null ) {

		parent::__construct(
			[
				'logo_url'    => wp_mail_smtp()->assets_url . '/images/providers/google.svg',
				'slug'        => self::SLUG,
				'title'       => esc_html__( 'Google / Gmail', 'wp-mail-smtp' ),
				'description' => sprintf(
					wp_kses( /* translators: %s - URL to our Gmail doc. */
						__( 'Our Gmail mailer works with any Gmail or Google Workspace account via the Google API. You can send WordPress emails from your main email address or a Gmail alias, and it\'s more secure than connecting to Gmail using SMTP credentials. We now have a One-Click Setup, which simply asks you to authorize your Google account to use our app and takes care of everything for you. Alternatively, you can connect manually, which involves several steps that are more technical than other mailer options, so we created a detailed guide to walk you through the process.<br><br>To get started, read our <a href="%s" target="_blank" rel="noopener noreferrer">Gmail documentation</a>.', 'wp-mail-smtp' ),
						[
							'br' => [],
							'a'  => [
								'href'   => [],
								'rel'    => [],
								'target' => [],
							],
						]
					),
					esc_url( wp_mail_smtp()->get_utm_url( 'https://wpmailsmtp.com/docs/how-to-set-up-the-gmail-mailer-in-wp-mail-smtp/', 'Gmail documentation' ) )
				),
				'notices'     => [
					'educational' => wp_kses(
						__( 'The Gmail mailer works well for sites that send low numbers of emails. However, Gmail\'s API has rate limitations and a number of additional restrictions that can lead to challenges during setup.<br><br>If you expect to send a high volume of emails, or if you find that your web host is not compatible with the Gmail API restrictions, then we recommend considering a different mailer option.', 'wp-mail-smtp' ),
						[
							'br' => [],
						]
					),
				],
				'php'         => '5.6',
				'supports'    => [
					'from_email'       => true,
					'from_name'        => true,
					'return_path'      => false,
					'from_email_force' => true,
					'from_name_force'  => true,
				],
			],
			$connection
		);
	}

	/**
	 * @inheritdoc
	 */
	public function display_options() {

		// Do not display options if PHP version is not correct.
		if ( ! $this->is_php_correct() ) {
			$this->display_php_warning();

			return;
		}
		?>

		<?php if ( ! wp_mail_smtp()->is_pro() ) : ?>
			<div id="wp-mail-smtp-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-one_click_setup_enabled-lite" class="wp-mail-smtp-setting-row">
				<div class="wp-mail-smtp-setting-label">
					<label for="wp-mail-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-one_click_setup_enabled-lite">
						<?php esc_html_e( 'One-Click Setup', 'wp-mail-smtp' ); ?>
					</label>
				</div>
				<div class="wp-mail-smtp-setting-field">
					<?php
					UI::toggle(
						[
							'id' => 'wp-mail-smtp-setting-' . esc_attr( $this->get_slug() ) . '-one_click_setup_enabled-lite',
						]
					);
					?>
					<p class="desc">
						<?php esc_html_e( 'Provides a quick and easy way to connect to Google that doesn\'t require creating your own app.', 'wp-mail-smtp' ); ?>
					</p>
				</div>
			</div>
		<?php endif; ?>

		<!-- Client ID -->
		<div id="wp-mail-smtp-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-client_id"
			class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-text wp-mail-smtp-clear">
			<div class="wp-mail-smtp-setting-label">
				<label for="wp-mail-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-client_id"><?php esc_html_e( 'Client ID', 'wp-mail-smtp' ); ?></label>
			</div>
			<div class="wp-mail-smtp-setting-field">
				<input name="wp-mail-smtp[<?php echo esc_attr( $this->get_slug() ); ?>][client_id]" type="text"
					value="<?php echo esc_attr( $this->connection_options->get( $this->get_slug(), 'client_id' ) ); ?>"
					<?php echo $this->connection_options->is_const_defined( $this->get_slug(), 'client_id' ) ? 'disabled' : ''; ?>
					id="wp-mail-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-client_id" spellcheck="false"
				/>
			</div>
		</div>

		<!-- Client Secret -->
		<div id="wp-mail-smtp-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-client_secret"
			class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-text wp-mail-smtp-clear">
			<div class="wp-mail-smtp-setting-label">
				<label for="wp-mail-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-client_secret"><?php esc_html_e( 'Client Secret', 'wp-mail-smtp' ); ?></label>
			</div>
			<div class="wp-mail-smtp-setting-field">
				<?php if ( $this->connection_options->is_const_defined( $this->get_slug(), 'client_secret' ) ) : ?>
					<input type="text" disabled value="****************************************"
						id="wp-mail-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-client_secret"
					/>
					<?php $this->display_const_set_message( 'WPMS_GMAIL_CLIENT_SECRET' ); ?>
				<?php else : ?>
					<?php
					$slug  = $this->get_slug();
					$value = $this->connection_options->get( $this->get_slug(), 'client_secret' );

					UI::hidden_password_field(
						[
							'name'       => "wp-mail-smtp[{$slug}][client_secret]",
							'id'         => "wp-mail-smtp-setting-{$slug}-client_secret",
							'value'      => $value,
							'clear_text' => esc_html__( 'Remove Client Secret', 'wp-mail-smtp' ),
						]
					);
					?>
				<?php endif; ?>
			</div>
		</div>

		<!-- Authorized redirect URI -->
		<div id="wp-mail-smtp-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-client_redirect"
			class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-text wp-mail-smtp-clear">
			<div class="wp-mail-smtp-setting-label">
				<label for="wp-mail-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-client_redirect"><?php esc_html_e( 'Authorized redirect URI', 'wp-mail-smtp' ); ?></label>
			</div>
			<div class="wp-mail-smtp-setting-field">
				<input type="text" readonly="readonly" onfocus="this.select();"
					value="<?php echo esc_attr( Auth::get_oauth_redirect_url() ); ?>"
					id="wp-mail-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-client_redirect"
				/>
				<button type="button" class="wp-mail-smtp-btn wp-mail-smtp-btn-md wp-mail-smtp-btn-grey wp-mail-smtp-setting-copy"
					title="<?php esc_attr_e( 'Copy URL to clipboard', 'wp-mail-smtp' ); ?>"
					data-source_id="wp-mail-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-client_redirect">
					<span class="dashicons dashicons-admin-page"></span>
				</button>
				<p class="desc">
					<?php esc_html_e( 'Please copy this URL into the "Authorized redirect URIs" field of your Google web application.', 'wp-mail-smtp' ); ?>
				</p>
			</div>
		</div>

		<!-- Auth users button -->
		<div id="wp-mail-smtp-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-authorize"
			class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-text wp-mail-smtp-clear">
			<div class="wp-mail-smtp-setting-label">
				<label><?php esc_html_e( 'Authorization', 'wp-mail-smtp' ); ?></label>
			</div>
			<div class="wp-mail-smtp-setting-field">
				<?php $this->display_auth_setting_action(); ?>
			</div>
		</div>

		<?php
	}

	/**
	 * Display either an "Allow..." or "Remove..." button.
	 *
	 * @since 1.3.0
	 */
	protected function display_auth_setting_action() {

		// Do the processing on the fly, as having ajax here is too complicated.
		$this->process_provider_remove();

		$auth = new Auth( $this->connection );
		?>

		<?php if ( $auth->is_clients_saved() ) : ?>

			<?php if ( $auth->is_auth_required() ) : ?>

				<a href="<?php echo esc_url( $auth->get_auth_url() ); ?>" class="wp-mail-smtp-btn wp-mail-smtp-btn-md wp-mail-smtp-btn-orange">
					<?php esc_html_e( 'Allow plugin to send emails using your Google account', 'wp-mail-smtp' ); ?>
				</a>
				<p class="desc">
					<?php esc_html_e( 'Click the button above to confirm authorization.', 'wp-mail-smtp' ); ?>
				</p>

			<?php else : ?>

				<a href="<?php echo esc_url( wp_nonce_url( ( new ConnectionSettings( $this->connection ) )->get_admin_page_url(), 'gmail_remove', 'gmail_remove_nonce' ) ); ?>#wp-mail-smtp-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-authorize" class="wp-mail-smtp-btn wp-mail-smtp-btn-md wp-mail-smtp-btn-red js-wp-mail-smtp-provider-remove">
					<?php esc_html_e( 'Remove OAuth Connection', 'wp-mail-smtp' ); ?>
				</a>
				<span class="connected-as">
					<?php
					$user = $auth->get_user_info();

					if ( ! empty( $user['email'] ) ) {
						printf(
							/* translators: %s - email address, as received from Google API. */
							esc_html__( 'Connected as %s', 'wp-mail-smtp' ),
							'<code>' . esc_html( $user['email'] ) . '</code>'
						);
					}
					?>
				</span>
				<p class="desc">
					<?php
					printf(
						wp_kses( /* translators: %s - URL to Google Gmail alias documentation page. */
							__( 'If you want to use a different From Email address you can set up a Google email alias. <a href="%s" target="_blank" rel="noopener noreferrer">Follow these instructions</a> and then select the From Email at the top of this page.', 'wp-mail-smtp' ),
							[
								'a' => [
									'href'   => [],
									'rel'    => [],
									'target' => [],
								],
							]
						),
						esc_url( wp_mail_smtp()->get_utm_url( 'https://wpmailsmtp.com/gmail-send-from-alias-wp-mail-smtp/', 'Gmail aliases description - Follow these instructions' ) )
					);
					?>
				</p>
				<p class="desc">
					<?php esc_html_e( 'You can also send emails with different From Email addresses, by disabling the Force From Email setting and using registered aliases throughout your WordPress site as the From Email addresses.', 'wp-mail-smtp' ); ?>
				</p>
				<p class="desc">
					<?php esc_html_e( 'Removing the OAuth connection will give you an ability to redo the OAuth connection or link to another Google account.', 'wp-mail-smtp' ); ?>
				</p>

			<?php endif; ?>

		<?php else : ?>

			<p class="inline-notice inline-error">
				<?php esc_html_e( 'You need to save settings with Client ID and Client Secret before you can proceed.', 'wp-mail-smtp' ); ?>
			</p>

		<?php
		endif;
	}

	/**
	 * Remove Provider OAuth connection.
	 *
	 * @since 1.3.0
	 */
	public function process_provider_remove() {

		if ( ! current_user_can( wp_mail_smtp()->get_capability_manage_options() ) ) {
			return;
		}

		if (
			! isset( $_GET['gmail_remove_nonce'] ) ||
			! wp_verify_nonce( sanitize_key( $_GET['gmail_remove_nonce'] ), 'gmail_remove' )
		) {
			return;
		}

		if ( $this->connection->get_mailer_slug() !== $this->get_slug() ) {
			return;
		}

		$old_opt = $this->connection_options->get_all_raw();

		unset( $old_opt[ $this->get_slug() ]['access_token'] );
		unset( $old_opt[ $this->get_slug() ]['refresh_token'] );
		unset( $old_opt[ $this->get_slug() ]['user_details'] );
		unset( $old_opt[ $this->get_slug() ]['auth_code'] );

		$this->connection_options->set( $old_opt );
	}
}
