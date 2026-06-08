<?php

namespace WPMailSMTP\Providers\Sendgrid;

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
	 * Options constructor.
	 *
	 * @since 1.0.0
	 * @since 2.3.0 Added supports parameter.
	 *
	 * @param ConnectionInterface $connection The Connection object.
	 */
	public function __construct( $connection = null ) {

		parent::__construct(
			[
				'logo_url'    => wp_mail_smtp()->assets_url . '/images/providers/sendgrid.svg',
				'slug'        => 'sendgrid',
				'title'       => esc_html__( 'SendGrid', 'wp-mail-smtp' ),
				'description' => sprintf(
					wp_kses(
						/* translators: %1$s - URL to sendgrid.com; %2$s - URL to Sendgrid documentation on wpmailsmtp.com */
						__( '<a href="%1$s" target="_blank" rel="noopener noreferrer">SendGrid</a> is a popular transactional email provider that sends more than 35 billion emails every month. If you\'re just starting out, the free plan allows you to send up to 100 emails each day without entering your credit card details.<br><br>To get started, read our <a href="%2$s" target="_blank" rel="noopener noreferrer">SendGrid documentation</a>.', 'wp-mail-smtp' ),
						[
							'br' => [],
							'a'  => [
								'href'   => [],
								'rel'    => [],
								'target' => [],
							],
						]
					),
					'https://sendgrid.com',
					esc_url( wp_mail_smtp()->get_utm_url( 'https://wpmailsmtp.com/docs/how-to-set-up-the-sendgrid-mailer-in-wp-mail-smtp/', 'SendGrid documentation' ) )
				),
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
		?>

		<!-- API Key -->
		<div id="wp-mail-smtp-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-api_key" class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-text wp-mail-smtp-clear">
			<div class="wp-mail-smtp-setting-label">
				<label for="wp-mail-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-api_key"><?php esc_html_e( 'API Key', 'wp-mail-smtp' ); ?></label>
			</div>
			<div class="wp-mail-smtp-setting-field">
				<?php if ( $this->connection_options->is_const_defined( $this->get_slug(), 'api_key' ) ) : ?>
					<input type="text" disabled value="****************************************"
						id="wp-mail-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-api_key"
					/>
					<?php $this->display_const_set_message( 'WPMS_SENDGRID_API_KEY' ); ?>
				<?php else : ?>
					<?php
					$slug  = $this->get_slug();
					$value = $this->connection_options->get( $this->get_slug(), 'api_key' );

					UI::hidden_password_field(
						[
							'name'       => "wp-mail-smtp[{$slug}][api_key]",
							'id'         => "wp-mail-smtp-setting-{$slug}-api_key",
							'value'      => $value,
							'clear_text' => esc_html__( 'Remove API Key', 'wp-mail-smtp' ),
						]
					);
					?>
				<?php endif; ?>
				<p class="desc">
					<?php
					printf(
						/* translators: %s - API key link. */
						esc_html__( 'Follow this link to get an API Key from SendGrid: %s.', 'wp-mail-smtp' ),
						'<a href="https://app.sendgrid.com/settings/api_keys" target="_blank" rel="noopener noreferrer">' .
						esc_html__( 'Create API Key', 'wp-mail-smtp' ) .
						'</a>'
					);
					?>
					<br/>
					<?php
					printf(
						/* translators: %s - SendGrid access level. */
						esc_html__( 'To send emails you will need only a %s access level for this API key.', 'wp-mail-smtp' ),
						'<code>Mail Send</code>'
					);
					?>
				</p>
			</div>
		</div>

		<!-- Sending Domain -->
		<div id="wp-mail-smtp-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-domain" class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-text wp-mail-smtp-clear">
			<div class="wp-mail-smtp-setting-label">
				<label for="wp-mail-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-domain"><?php esc_html_e( 'Sending Domain', 'wp-mail-smtp' ); ?></label>
			</div>
			<div class="wp-mail-smtp-setting-field">
				<input name="wp-mail-smtp[<?php echo esc_attr( $this->get_slug() ); ?>][domain]" type="text"
					   value="<?php echo esc_attr( $this->connection_options->get( $this->get_slug(), 'domain' ) ); ?>"
					<?php echo $this->connection_options->is_const_defined( $this->get_slug(), 'domain' ) ? 'disabled' : ''; ?>
					   id="wp-mail-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-domain" spellcheck="false"
				/>
				<p class="desc">
					<?php
					printf(
						wp_kses(
							/* translators: %s - URL to SendGrid documentation on wpmailsmtp.com */
							__( 'Please input the sending domain/subdomain you configured in your SendGrid dashboard. More information can be found in our <a href="%s" target="_blank" rel="noopener noreferrer">SendGrid documentation</a>.', 'wp-mail-smtp' ),
							[
								'br' => [],
								'a'  => [
									'href'   => [],
									'rel'    => [],
									'target' => [],
								],
							]
						),
						esc_url( wp_mail_smtp()->get_utm_url( 'https://wpmailsmtp.com/docs/how-to-set-up-the-sendgrid-mailer-in-wp-mail-smtp/#setup', 'SendGrid documentation - setup' ) )
					);
					?>
				</p>
			</div>
		</div>

		<?php
	}
}
