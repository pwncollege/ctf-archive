<?php

namespace WPMailSMTP\Providers\SMTPcom;

use WPMailSMTP\ConnectionInterface;
use WPMailSMTP\Helpers\UI;
use WPMailSMTP\Providers\OptionsAbstract;

/**
 * Class Options.
 *
 * @since 2.0.0
 */
class Options extends OptionsAbstract {

	/**
	 * Mailer slug.
	 *
	 * @since 2.0.0
	 */
	const SLUG = 'smtpcom';

	/**
	 * Options constructor.
	 *
	 * @since 2.0.0
	 * @since 2.3.0 Added supports parameter.
	 *
	 * @param ConnectionInterface $connection The Connection object.
	 */
	public function __construct( $connection = null ) {

		if ( is_null( $connection ) ) {
			$connection = wp_mail_smtp()->get_connections_manager()->get_primary_connection();
		}

		$allowed_kses_html = array(
			'strong' => array(),
			'br'     => array(),
			'a'      => array(
				'href'   => array(),
				'rel'    => array(),
				'target' => array(),
			),
		);

		$description  = sprintf(
			wp_kses( /* translators: %s - URL to smtp.com site. */
				__( '<strong><a href="%s" target="_blank" rel="noopener noreferrer">SMTP.com</a> is one of our recommended mailers.</strong> It\'s a transactional email provider that\'s currently used by 100,000+ businesses. SMTP.com is an established brand that\'s been offering email services for more than 20 years.<br><br>SMTP.com offers a free 30-day trial that allows you to send up to 50,000 emails.', 'wp-mail-smtp' ),
				$allowed_kses_html
			),
			'https://wpmailsmtp.com/go/smtp/'
		);
		$description .= '<br><br>';
		$description .= sprintf(
			wp_kses( /* translators: %s - URL to wpmailsmtp.com doc page for stmp.com. */
				__( 'To get started, read our <a href="%s" target="_blank" rel="noopener noreferrer">SMTP.com documentation</a>.', 'wp-mail-smtp' ),
				$allowed_kses_html
			),
			esc_url( wp_mail_smtp()->get_utm_url( 'https://wpmailsmtp.com/docs/how-to-set-up-the-smtp-com-mailer-in-wp-mail-smtp/', 'SMTP.com documentation' ) )
		);

		$mailer_options = $connection->get_options()->get_group( self::SLUG );

		if ( empty( $mailer_options['api_key'] ) && empty( $mailer_options['channel'] ) ) {
			$description .= sprintf(
				'</p><p class="buttonned"><a href="%1$s" target="_blank" rel="noopener noreferrer" class="wp-mail-smtp-btn wp-mail-smtp-btn-md wp-mail-smtp-btn-blueish">%2$s</a></p>',
				'https://wpmailsmtp.com/go/smtp/',
				esc_html__( 'Get Started with SMTP.com', 'wp-mail-smtp' )
			);
		}

		$description .= '<p class="wp-mail-smtp-tooltip">' .
			esc_html__( 'Transparency and Disclosure', 'wp-mail-smtp' ) .
			'<span class="wp-mail-smtp-tooltip-text">' .
			esc_html__( 'We believe in full transparency. The SMTP.com links above are tracking links as part of our partnership with SMTP (j2 Global). We can recommend just about any SMTP service, but we only recommend products that we believe will add value to our users.', 'wp-mail-smtp' ) .
			'</span></p>';

		parent::__construct(
			[
				'logo_url'    => wp_mail_smtp()->assets_url . '/images/providers/smtp-com.svg',
				'slug'        => self::SLUG,
				'title'       => esc_html__( 'SMTP.com', 'wp-mail-smtp' ),
				'description' => $description,
				'recommended' => true,
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
					<?php $this->display_const_set_message( 'WPMS_SMTPCOM_API_KEY' ); ?>
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
					printf( /* translators: %s - API key link. */
						esc_html__( 'Follow this link to get an API Key from SMTP.com: %s.', 'wp-mail-smtp' ),
						'<a href="https://my.smtp.com/account?tab=manage_api_keys" target="_blank" rel="noopener noreferrer">' .
						esc_html__( 'Get API Key', 'wp-mail-smtp' ) .
						'</a>'
					);
					?>
				</p>
			</div>
		</div>

		<!-- Channel/Sender -->
		<div id="wp-mail-smtp-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-channel" class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-text wp-mail-smtp-clear">
			<div class="wp-mail-smtp-setting-label">
				<label for="wp-mail-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-channel"><?php esc_html_e( 'Sender Name', 'wp-mail-smtp' ); ?></label>
			</div>
			<div class="wp-mail-smtp-setting-field">
				<input name="wp-mail-smtp[<?php echo esc_attr( $this->get_slug() ); ?>][channel]" type="text"
					value="<?php echo esc_attr( $this->connection_options->get( $this->get_slug(), 'channel' ) ); ?>"
					<?php echo $this->connection_options->is_const_defined( $this->get_slug(), 'channel' ) ? 'disabled' : ''; ?>
					id="wp-mail-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-channel" spellcheck="false"
				/>
				<?php
				if ( $this->connection_options->is_const_defined( $this->get_slug(), 'channel' ) ) {
					$this->display_const_set_message( 'WPMS_SMTPCOM_CHANNEL' );
				}
				?>
				<p class="desc">
					<?php
					printf( /* translators: %s - Channel/Sender Name link for smtp.com documentation. */
						esc_html__( 'Follow this link to get a Sender Name from SMTP.com: %s.', 'wp-mail-smtp' ),
						'<a href="https://my.smtp.com/account?tab=manage_channels" target="_blank" rel="noopener noreferrer">' .
						esc_html__( 'Get Sender Name', 'wp-mail-smtp' ) .
						'</a>'
					);
					?>
				</p>
			</div>
		</div>

		<?php
	}
}
