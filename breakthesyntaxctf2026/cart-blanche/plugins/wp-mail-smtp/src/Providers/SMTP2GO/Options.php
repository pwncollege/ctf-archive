<?php

namespace WPMailSMTP\Providers\SMTP2GO;

use WPMailSMTP\ConnectionInterface;
use WPMailSMTP\Helpers\UI;
use WPMailSMTP\Providers\OptionsAbstract;

/**
 * Class Options.
 *
 * @since 4.1.0
 */
class Options extends OptionsAbstract {

	/**
	 * Mailer slug.
	 *
	 * @since 4.1.0
	 */
	const SLUG = 'smtp2go';

	/**
	 * Options constructor.
	 *
	 * @since 4.1.0
	 *
	 * @param ConnectionInterface $connection The Connection object.
	 */
	public function __construct( $connection = null ) {

		if ( is_null( $connection ) ) {
			$connection = wp_mail_smtp()->get_connections_manager()->get_primary_connection();
		}

		$description = sprintf(
			wp_kses( /* translators: %1$s - URL to SMTP2GO.com site. */
				__( '<a href="%1$s" target="_blank" rel="noopener noreferrer">SMTP2GO</a> provides a robust and reliable email delivery service with global infrastructure, real-time analytics, and advanced security features. If you\'re just starting out, you can use SMTP2GO\'s free plan to send up to 1000 emails per month.', 'wp-mail-smtp' ) .
				'<br><br>' .
				/* translators: %2$s - URL to wpmailsmtp.com doc. */
				__( 'To get started, read our <a href="%2$s" target="_blank" rel="noopener noreferrer">SMTP2GO documentation</a>.', 'wp-mail-smtp' ),
				[
					'strong' => true,
					'br'     => true,
					'a'      => [
						'href'   => true,
						'rel'    => true,
						'target' => true,
					],
				]
			),
			'https://www.smtp2go.com/',
			esc_url( wp_mail_smtp()->get_utm_url( 'https://wpmailsmtp.com/docs/how-to-set-up-the-smtp2go-mailer-in-wp-mail-smtp/', 'SMTP2GO documentation' ) )
		);

		parent::__construct(
			[
				'logo_url'    => wp_mail_smtp()->assets_url . '/images/providers/smtp2go.svg',
				'slug'        => self::SLUG,
				'title'       => esc_html__( 'SMTP2GO', 'wp-mail-smtp' ),
				'php'         => '5.6',
				'description' => $description,
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
	 * Output the mailer provider options.
	 *
	 * @since 4.1.0
	 */
	public function display_options() {

		// Do not display options if PHP version is not correct.
		if ( ! $this->is_php_correct() ) {
			$this->display_php_warning();

			return;
		}
		?>

		<!-- API Key -->
		<div id="wp-mail-smtp-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-api_key"
		     class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-text wp-mail-smtp-clear">
			<div class="wp-mail-smtp-setting-label">
				<label for="wp-mail-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-api_key"><?php esc_html_e( 'API Key', 'wp-mail-smtp' ); ?></label>
			</div>
			<div class="wp-mail-smtp-setting-field">
				<?php if ( $this->connection_options->is_const_defined( $this->get_slug(), 'api_key' ) ) : ?>
					<input type="text" disabled value="****************************************"
					       id="wp-mail-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-api_key"
					/>
					<?php $this->display_const_set_message( 'WPMS_SMTP2GO_API_KEY' ); ?>
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
					printf( /* translators: %s - link to get an API Key. */
						esc_html__( 'Generate an API key on the Sending &rarr; API Keys page in your %s.', 'wp-mail-smtp' ),
						'<a href="https://app.smtp2go.com/" target="_blank" rel="noopener noreferrer">' .
						esc_html__( 'control panel', 'wp-mail-smtp' ) .
						'</a>'
					);
					?>
				</p>
			</div>
		</div>

		<?php
	}
}
