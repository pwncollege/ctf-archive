<?php

namespace WPMailSMTP\Admin;

use WPMailSMTP\ConnectionInterface;
use WPMailSMTP\Debug;
use WPMailSMTP\Helpers\UI;
use WPMailSMTP\Options;

/**
 * Class ConnectionSettings.
 *
 * @since 3.7.0
 */
class ConnectionSettings {

	/**
	 * The Connection object.
	 *
	 * @since 3.7.0
	 *
	 * @var ConnectionInterface
	 */
	private $connection;

	/**
	 * After process scroll to anchor.
	 *
	 * @since 3.7.0
	 *
	 * @var false|string
	 */
	private $scroll_to = false;

	/**
	 * Constructor.
	 *
	 * @since 3.7.0
	 *
	 * @param ConnectionInterface $connection The Connection object.
	 */
	public function __construct( $connection ) {

		$this->connection = $connection;
	}

	/**
	 * Display connection settings.
	 *
	 * @since 3.7.0
	 */
	public function display() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded, Generic.Metrics.NestingLevel.MaxExceeded

		$mailer             = $this->connection->get_mailer_slug();
		$connection_options = $this->connection->get_options();

		$hide_from_email = false;
		$disabled_email  = in_array( $mailer, [ 'zoho' ], true ) ? 'disabled' : '';
		$disabled_name   = in_array( $mailer, [ 'outlook' ], true ) ? 'disabled' : '';

		if ( empty( $mailer ) || ! in_array( $mailer, Options::$mailers, true ) ) {
			$mailer = 'mail';
		}

		if (
			$mailer === 'sendlayer' &&
			$connection_options->get( 'sendlayer', 'quick_connect' ) &&
			$connection_options->get( 'sendlayer', 'is_shared_domain' )
		) {
			// For SendLayer, hide the From Email setting since it's managed from the SendLayer dashboard.
			$hide_from_email = true;
		}

		$mailer_supported_settings = wp_mail_smtp()->get_providers()->get_options( $mailer )->get_supports();
		?>
		<!-- From Email -->
		<div class="wp-mail-smtp-setting-group js-wp-mail-smtp-setting-from_email" style="display: <?php echo ( empty( $mailer_supported_settings['from_email'] ) || $hide_from_email ) ? 'none' : 'block'; ?>;">
			<div id="wp-mail-smtp-setting-row-from_email" class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-email wp-mail-smtp-clear">
				<div class="wp-mail-smtp-setting-label">
					<label for="wp-mail-smtp-setting-from_email"><?php esc_html_e( 'From Email', 'wp-mail-smtp' ); ?></label>
				</div>
				<div class="wp-mail-smtp-setting-field">
					<input name="wp-mail-smtp[mail][from_email]" type="email"
								 value="<?php echo esc_attr( $connection_options->get( 'mail', 'from_email' ) ); ?>"
								 id="wp-mail-smtp-setting-from_email" spellcheck="false"
								 placeholder="<?php echo esc_attr( wp_mail_smtp()->get_processor()->get_default_email() ); ?>"
								 <?php disabled( $connection_options->is_const_defined( 'mail', 'from_email' ) || ! empty( $disabled_email ) ); ?>
					/>

					<?php if ( ! in_array( $mailer, [ 'zoho' ], true ) ) : ?>
						<p class="desc">
							<?php esc_html_e( 'The email address that emails are sent from.', 'wp-mail-smtp' ); ?>
						</p>
						<p class="desc">
							<?php esc_html_e( 'If you\'re using an email provider (Yahoo, Outlook.com, etc) this should be your email address for that account.', 'wp-mail-smtp' ); ?>
						</p>
						<p class="desc">
							<?php esc_html_e( 'Please note that other plugins can change this, to prevent this use the setting below.', 'wp-mail-smtp' ); ?>
						</p>
					<?php endif; ?>
				</div>
			</div>
			<div id="wp-mail-smtp-setting-row-from_email_force" class="wp-mail-smtp-setting-row wp-mail-smtp-clear js-wp-mail-smtp-setting-from_email_force" style="display: <?php echo empty( $mailer_supported_settings['from_email_force'] ) ? 'none' : 'block'; ?>;">
				<div class="wp-mail-smtp-setting-label">
					<label for="wp-mail-smtp-setting-from_email_force"><?php esc_html_e( 'Force From Email', 'wp-mail-smtp' ); ?></label>
				</div>
				<div class="wp-mail-smtp-setting-field">
					<?php
					UI::toggle(
						[
							'name'     => 'wp-mail-smtp[mail][from_email_force]',
							'id'       => 'wp-mail-smtp-setting-from_email_force',
							'value'    => 'true',
							'checked'  => (bool) $connection_options->get( 'mail', 'from_email_force' ),
							'disabled' => $connection_options->is_const_defined( 'mail', 'from_email_force' ) || ! empty( $disabled_email ),
						]
					);
					?>

					<?php if ( ! empty( $disabled_email ) ) : ?>
						<p class="desc">
							<?php esc_html_e( 'Current provider will automatically force From Email to be the email address that you use to set up the OAuth connection below.', 'wp-mail-smtp' ); ?>
						</p>
					<?php else : ?>
						<p class="desc">
							<?php esc_html_e( 'If checked, the From Email setting above will be used for all emails, ignoring values set by other plugins.', 'wp-mail-smtp' ); ?>
						</p>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<?php
		/**
		 * Fires after the From Email Address setting row.
		 *
		 * @since 4.8.0
		 *
		 * @param ConnectionInterface $connection         The Connection object.
		 * @param Options             $connection_options The connection options instance.
		 * @param string              $mailer             The current mailer slug.
		 */
		do_action( 'wp_mail_smtp_admin_connection_settings_display_after_from_email_setting_row', $this->connection, $connection_options, $mailer );
		?>

		<!-- From Name -->
		<div class="wp-mail-smtp-setting-group js-wp-mail-smtp-setting-from_name"  style="display: <?php echo empty( $mailer_supported_settings['from_name'] ) ? 'none' : 'block'; ?>;">
			<div id="wp-mail-smtp-setting-row-from_name" class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-text wp-mail-smtp-clear ">
				<div class="wp-mail-smtp-setting-label">
					<label for="wp-mail-smtp-setting-from_name"><?php esc_html_e( 'From Name', 'wp-mail-smtp' ); ?></label>
				</div>
				<div class="wp-mail-smtp-setting-field">
					<input name="wp-mail-smtp[mail][from_name]" type="text"
								 value="<?php echo esc_attr( $connection_options->get( 'mail', 'from_name' ) ); ?>"
								 id="wp-mail-smtp-setting-from_name" spellcheck="false"
								 placeholder="<?php echo esc_attr( wp_mail_smtp()->get_processor()->get_default_name() ); ?>"
								 <?php disabled( $connection_options->is_const_defined( 'mail', 'from_name' ) || ! empty( $disabled_name ) ); ?>
					/>

					<?php if ( empty( $disabled_name ) ) : ?>
						<p class="desc">
							<?php esc_html_e( 'The name that emails are sent from.', 'wp-mail-smtp' ); ?>
						</p>
					<?php endif; ?>
				</div>
			</div>
			<div id="wp-mail-smtp-setting-row-from_name_force" class="wp-mail-smtp-setting-row wp-mail-smtp-clear js-wp-mail-smtp-setting-from_name_force" style="display: <?php echo empty( $mailer_supported_settings['from_name_force'] ) ? 'none' : 'block'; ?>;">
				<div class="wp-mail-smtp-setting-label">
					<label for="wp-mail-smtp-setting-from_name_force"><?php esc_html_e( 'Force From Name', 'wp-mail-smtp' ); ?></label>
				</div>
				<div class="wp-mail-smtp-setting-field">
					<?php
					UI::toggle(
						[
							'name'     => 'wp-mail-smtp[mail][from_name_force]',
							'id'       => 'wp-mail-smtp-setting-from_name_force',
							'value'    => 'true',
							'checked'  => (bool) $connection_options->get( 'mail', 'from_name_force' ),
							'disabled' => $connection_options->is_const_defined( 'mail', 'from_name_force' ) || ! empty( $disabled_name ),
						]
					);
					?>

					<?php if ( ! empty( $disabled_name ) ) : ?>
						<p class="desc">
							<?php esc_html_e( 'Current provider doesn\'t support setting and forcing From Name. Emails will be sent on behalf of the account name used to setup the OAuth connection below.', 'wp-mail-smtp' ); ?>
						</p>
					<?php else : ?>
						<p class="desc">
							<?php esc_html_e( 'If checked, the From Name setting above will be used for all emails, ignoring values set by other plugins.', 'wp-mail-smtp' ); ?>
						</p>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Return Path -->
		<div id="wp-mail-smtp-setting-row-return_path" class="wp-mail-smtp-setting-row wp-mail-smtp-clear js-wp-mail-smtp-setting-return_path" style="display: <?php echo empty( $mailer_supported_settings['return_path'] ) ? 'none' : 'block'; ?>;">
			<div class="wp-mail-smtp-setting-label">
				<label for="wp-mail-smtp-setting-return_path"><?php esc_html_e( 'Return Path', 'wp-mail-smtp' ); ?></label>
			</div>
			<div class="wp-mail-smtp-setting-field">
				<?php
				UI::toggle(
					[
						'name'     => 'wp-mail-smtp[mail][return_path]',
						'id'       => 'wp-mail-smtp-setting-return_path',
						'value'    => 'true',
						'checked'  => (bool) $connection_options->get( 'mail', 'return_path' ),
						'disabled' => $connection_options->is_const_defined( 'mail', 'return_path' ),
					]
				);
				?>

				<p class="desc">
					<?php esc_html_e( 'Return Path indicates where non-delivery receipts - or bounce messages - are to be sent.', 'wp-mail-smtp' ); ?><br/>
					<?php esc_html_e( 'If unchecked, bounce messages may be lost.', 'wp-mail-smtp' ); ?>
				</p>
			</div>
		</div>

		<!-- Mailer -->
		<div id="wp-mail-smtp-setting-row-mailer" class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-mailer wp-mail-smtp-clear">
			<div class="wp-mail-smtp-setting-label">
				<label for="wp-mail-smtp-setting-mailer"><?php esc_html_e( 'Mailer', 'wp-mail-smtp' ); ?></label>
			</div>
			<div class="wp-mail-smtp-setting-field">
				<div class="wp-mail-smtp-mailers">

					<?php foreach ( wp_mail_smtp()->get_providers()->get_options_all( $this->connection ) as $provider ) : ?>

						<div class="wp-mail-smtp-mailer wp-mail-smtp-mailer-<?php echo esc_attr( $provider->get_slug() ); ?> <?php echo $mailer === $provider->get_slug() ? 'active' : ''; ?>">

							<div class="wp-mail-smtp-mailer-image <?php echo $provider->is_recommended() ? 'is-recommended' : ''; ?>">
								<img src="<?php echo esc_url( $provider->get_logo_url() ); ?>"
										 alt="<?php echo esc_attr( $provider->get_title() ); ?>">
							</div>

							<div class="wp-mail-smtp-mailer-text">
								<?php if ( $provider->is_disabled() ) : ?>
									<input type="radio" name="wp-mail-smtp[mail][mailer]" disabled
												 data-title="<?php echo esc_attr( $provider->get_title() ); ?>"
												 class="js-wp-mail-smtp-setting-mailer-radio-input educate"
												 id="wp-mail-smtp-setting-mailer-<?php echo esc_attr( $provider->get_slug() ); ?>"
												 value="<?php echo esc_attr( $provider->get_slug() ); ?>"
									/>
								<?php else : ?>
									<input id="wp-mail-smtp-setting-mailer-<?php echo esc_attr( $provider->get_slug() ); ?>"
												 data-title="<?php echo esc_attr( $provider->get_title() ); ?>"
												 type="radio" name="wp-mail-smtp[mail][mailer]"
												 value="<?php echo esc_attr( $provider->get_slug() ); ?>"
												 class="js-wp-mail-smtp-setting-mailer-radio-input<?php echo $provider->is_disabled() ? ' educate' : ''; ?>"
												 <?php checked( $provider->get_slug(), $mailer ); ?>
												 <?php disabled( $connection_options->is_const_defined( 'mail', 'mailer' ) || $provider->is_disabled() ); ?>
									/>
								<?php endif; ?>
								<label for="wp-mail-smtp-setting-mailer-<?php echo esc_attr( $provider->get_slug() ); ?>">
									<?php echo esc_html( $provider->get_title() ); ?>
								</label>
							</div>
						</div>

					<?php endforeach; ?>
				</div>

				<!-- Suggest a mailer -->
				<div class="wp-mail-smtp-suggest-new-mailer">
					<p class="desc">
						<?php esc_html_e( 'Don\'t see what you\'re looking for?', 'wp-mail-smtp' ); ?>
						<?php
						printf(
							'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
							esc_url( wp_mail_smtp()->get_utm_url( 'https://wpmailsmtp.com/suggest-a-mailer/', 'Suggest a Mailer' ) ),
							esc_html__( 'Suggest a Mailer', 'wp-mail-smtp' )
						);
						?>
					</p>
				</div>
			</div>
		</div>

		<!-- Mailer Options -->
		<div class="wp-mail-smtp-setting-group wp-mail-smtp-mailer-options">
			<?php foreach ( wp_mail_smtp()->get_providers()->get_options_all( $this->connection ) as $provider ) : ?>
				<?php $provider_desc = $provider->get_description(); ?>
				<div class="wp-mail-smtp-mailer-option wp-mail-smtp-mailer-option-<?php echo esc_attr( $provider->get_slug() ); ?> <?php echo $mailer === $provider->get_slug() ? 'active' : 'hidden'; ?>">

					<?php if ( ! $provider->is_disabled() ) : ?>
						<!-- Mailer Title/Notice/Description -->
						<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-content wp-mail-smtp-clear section-heading <?php echo empty( $provider_desc ) ? 'no-desc' : ''; ?>">
							<div class="wp-mail-smtp-setting-field">
								<h2><?php echo esc_html( $provider->get_title() ); ?></h2>
								<?php
								$provider_edu_notice = $provider->get_notice( 'educational' );
								$is_dismissed        = (bool) get_user_meta( get_current_user_id(), "wp_mail_smtp_notice_educational_for_{$provider->get_slug()}_dismissed", true );

								if ( ! empty( $provider_edu_notice ) && ! $is_dismissed ) :
									?>
									<p class="inline-notice inline-edu-notice"
										 data-notice="educational"
										 data-mailer="<?php echo esc_attr( $provider->get_slug() ); ?>">
										<a href="#" title="<?php esc_attr_e( 'Dismiss this notice', 'wp-mail-smtp' ); ?>"
											 class="wp-mail-smtp-mailer-notice-dismiss js-wp-mail-smtp-mailer-notice-dismiss">
											<span class="dashicons dashicons-dismiss"></span>
										</a>

										<?php echo wp_kses_post( $provider_edu_notice ); ?>
									</p>
								<?php endif; ?>

								<?php if ( ! empty( $provider_desc ) ) : ?>
									<p class="desc"><?php echo wp_kses_post( $provider_desc ); ?></p>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>

					<?php $provider->display_options(); ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Process connection settings. Should be called before options save.
	 *
	 * @since 3.7.0
	 *
	 * @param array $data     Connection data.
	 * @param array $old_data Old connection data.
	 */
	public function process( $data, $old_data ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded, Generic.Metrics.CyclomaticComplexity.TooHigh

		// When checkbox is unchecked - it's not submitted at all, so we need to define its default false value.
		if ( ! isset( $data['mail']['from_email_force'] ) ) {
			$data['mail']['from_email_force'] = false;
		}
		if ( ! isset( $data['mail']['from_name_force'] ) ) {
			$data['mail']['from_name_force'] = false;
		}
		if ( ! isset( $data['mail']['return_path'] ) ) {
			$data['mail']['return_path'] = false;
		}
		if ( ! isset( $data['smtp']['autotls'] ) ) {
			$data['smtp']['autotls'] = false;
		}
		if ( ! isset( $data['smtp']['auth'] ) ) {
			$data['smtp']['auth'] = false;
		}
		if ( ! isset( $data['mailersend']['has_pro_plan'] ) ) {
			$data['mailersend']['has_pro_plan'] = false;
		}

		// When switching mailers.
		if (
			! empty( $old_data['mail']['mailer'] ) &&
			! empty( $data['mail']['mailer'] ) &&
			$old_data['mail']['mailer'] !== $data['mail']['mailer']
		) {
			// Remove all debug messages when switching mailers.
			Debug::clear();

			// Save correct from email address if Zoho mailer is already configured.
			if (
				in_array( $data['mail']['mailer'], [ 'zoho' ], true ) &&
				! empty( $old_data[ $data['mail']['mailer'] ]['user_details']['email'] )
			) {
				$data['mail']['from_email'] = $old_data[ $data['mail']['mailer'] ]['user_details']['email'];
			}
		}

		// Prevent redirect to setup wizard from settings page after successful auth.
		if (
			! empty( $data['mail']['mailer'] ) &&
			in_array( $data['mail']['mailer'], [ 'gmail', 'outlook', 'zoho' ], true )
		) {
			$data[ $data['mail']['mailer'] ]['is_setup_wizard_auth'] = false;
		}

		/**
		 * Filters connection data.
		 *
		 * @since 3.11.0
		 *
		 * @param array $data     Connection data.
		 * @param array $old_data Old connection data.
		 */
		return apply_filters( 'wp_mail_smtp_admin_connection_settings_process_data', $data, $old_data );
	}

	/**
	 * Post process connection settings. Should be called after options save.
	 *
	 * @since 3.7.0
	 *
	 * @param array $data     Connection data.
	 * @param array $old_data Old connection data.
	 */
	public function post_process( $data, $old_data ) {

		// When switching mailers.
		if (
			! empty( $old_data['mail']['mailer'] ) &&
			! empty( $data['mail']['mailer'] ) &&
			$old_data['mail']['mailer'] !== $data['mail']['mailer']
		) {

			// Save correct from email address if Gmail or Outlook mailer is already configured.
			if ( in_array( $data['mail']['mailer'], [ 'gmail', 'outlook' ], true ) ) {
				$auth      = wp_mail_smtp()->get_providers()->get_auth( $data['mail']['mailer'], $this->connection );
				$user_info = ! $auth->is_auth_required() ? $auth->get_user_info() : false;

				if (
					! empty( $user_info['email'] ) &&
					is_email( $user_info['email'] ) !== false &&
					(
						empty( $data['mail']['from_email'] ) ||
						$data['mail']['from_email'] !== $user_info['email']
					)
				) {
					$data['mail']['from_email'] = $user_info['email'];

					$this->connection->get_options()->set( $data, false, false );
				}
			}
		}
	}

	/**
	 * Get connection settings admin page URL.
	 *
	 * @since 3.7.0
	 *
	 * @return string
	 */
	public function get_admin_page_url() {

		/**
		 * Filters connection settings admin page URL.
		 *
		 * @since 3.7.0
		 *
		 * @param string              $admin_page_url Connection settings admin page URL.
		 * @param ConnectionInterface $connection     The Connection object.
		 */
		return apply_filters(
			'wp_mail_smtp_admin_connection_settings_get_admin_page_url',
			wp_mail_smtp()->get_admin()->get_admin_page_url(),
			$this->connection
		);
	}

	/**
	 * Get after process scroll to anchor. Returns `false` if scroll is not needed.
	 *
	 * @since 3.7.0
	 */
	public function get_scroll_to() {

		return $this->scroll_to;
	}
}
