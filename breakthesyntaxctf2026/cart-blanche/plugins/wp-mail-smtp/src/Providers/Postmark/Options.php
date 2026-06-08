<?php

namespace WPMailSMTP\Providers\Postmark;

use WPMailSMTP\ConnectionInterface;
use WPMailSMTP\Helpers\UI;
use WPMailSMTP\Providers\OptionsAbstract;

/**
 * Class Options.
 *
 * @since 3.1.0
 */
class Options extends OptionsAbstract {

	/**
	 * Mailer slug.
	 *
	 * @since 3.1.0
	 */
	const SLUG = 'postmark';

	/**
	 * Options constructor.
	 *
	 * @since 3.1.0
	 *
	 * @param ConnectionInterface $connection The Connection object.
	 */
	public function __construct( $connection = null ) {

		$description = sprintf(
			wp_kses( /* translators: %1$s - URL to postmarkapp.com site. */
				__( '<a href="%1$s" target="_blank" rel="noopener noreferrer">Postmark</a> is a transactional email provider that offers great deliverability and accessible pricing for any business. You can start out with the free trial that allows you to send 100 test emails each month via its secure API.', 'wp-mail-smtp' ) .
				'<br><br>' .
				/* translators: %2$s - URL to wpmailsmtp.com doc. */
				__( 'To get started, read our <a href="%2$s" target="_blank" rel="noopener noreferrer">Postmark documentation</a>.', 'wp-mail-smtp' ),
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
			'https://postmarkapp.com',
			esc_url( wp_mail_smtp()->get_utm_url( 'https://wpmailsmtp.com/docs/how-to-set-up-the-postmark-mailer-in-wp-mail-smtp/', 'Postmark documentation' ) )
		);

		parent::__construct(
			[
				'logo_url'    => wp_mail_smtp()->assets_url . '/images/providers/postmark.svg',
				'slug'        => self::SLUG,
				'title'       => esc_html__( 'Postmark', 'wp-mail-smtp' ),
				'php'         => '5.6',
				'description' => $description,
				'supports'    => [
					'from_email'       => true,
					'from_name'        => true,
					'return_path'      => false,
					'from_email_force' => true,
					'from_name_force'  => true,
				],
				'recommended' => false,
			],
			$connection
		);
	}

	/**
	 * Output the mailer provider options.
	 *
	 * @since 3.1.0
	 */
	public function display_options() {

		// Do not display options if PHP version is not correct.
		if ( ! $this->is_php_correct() ) {
			$this->display_php_warning();

			return;
		}
		?>

		<!-- Server API Token -->
		<div id="wp-mail-smtp-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-server_api_token" class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-text wp-mail-smtp-clear">
			<div class="wp-mail-smtp-setting-label">
				<label for="wp-mail-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-server_api_token"><?php esc_html_e( 'Server API Token', 'wp-mail-smtp' ); ?></label>
			</div>
			<div class="wp-mail-smtp-setting-field">
				<?php if ( $this->connection_options->is_const_defined( $this->get_slug(), 'server_api_token' ) ) : ?>
					<input type="text" disabled value="****************************************"
						   id="wp-mail-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-server_api_token"/>
					<?php $this->display_const_set_message( 'WPMS_POSTMARK_SERVER_API_TOKEN' ); ?>
				<?php else : ?>
					<?php
					$slug  = $this->get_slug();
					$value = $this->connection_options->get( $this->get_slug(), 'server_api_token' );

					UI::hidden_password_field(
						[
							'name'       => "wp-mail-smtp[{$slug}][server_api_token]",
							'id'         => "wp-mail-smtp-setting-{$slug}-server_api_token",
							'value'      => $value,
							'clear_text' => esc_html__( 'Remove Server API Token', 'wp-mail-smtp' ),
						]
					);
					?>
				<?php endif; ?>
				<p class="desc">
					<?php
					printf( /* translators: %s - Server API Token link. */
						esc_html__( 'Follow this link to get a Server API Token from Postmark: %s.', 'wp-mail-smtp' ),
						'<a href="https://account.postmarkapp.com/api_tokens" target="_blank" rel="noopener noreferrer">' .
							esc_html__( 'Get Server API Token', 'wp-mail-smtp' ) .
						'</a>'
					);
					?>
				</p>
			</div>
		</div>

		<!-- Message Stream ID -->
		<div id="wp-mail-smtp-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-message_stream" class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-text wp-mail-smtp-clear">
			<div class="wp-mail-smtp-setting-label">
				<label for="wp-mail-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-message_stream"><?php esc_html_e( 'Message Stream ID', 'wp-mail-smtp' ); ?></label>
			</div>
			<div class="wp-mail-smtp-setting-field">
				<input name="wp-mail-smtp[<?php echo esc_attr( $this->get_slug() ); ?>][message_stream]" type="text"
					   value="<?php echo esc_attr( $this->connection_options->get( $this->get_slug(), 'message_stream' ) ); ?>"
					   <?php echo $this->connection_options->is_const_defined( $this->get_slug(), 'message_stream' ) ? 'disabled' : ''; ?>
					   id="wp-mail-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-message_stream" spellcheck="false"/>
				<?php
				if ( $this->connection_options->is_const_defined( $this->get_slug(), 'message_stream' ) ) {
					$this->display_const_set_message( 'WPMS_POSTMARK_MESSAGE_STREAM' );
				}
				?>
				<p class="desc">
					<?php
					printf(
						wp_kses(
						/* translators: %s - URL to Postmark documentation on wpmailsmtp.com */
							__( 'Message Stream ID is <strong>optional</strong>. By default <strong>outbound</strong> (Default Transactional Stream) will be used. More information can be found in our <a href="%s" target="_blank" rel="noopener noreferrer">Postmark documentation</a>.', 'wp-mail-smtp' ),
							[
								'strong' => [],
								'a'      => [
									'href'   => [],
									'rel'    => [],
									'target' => [],
								],
							]
						),
						esc_url( wp_mail_smtp()->get_utm_url( 'https://wpmailsmtp.com/docs/how-to-set-up-the-postmark-mailer-in-wp-mail-smtp/#message-stream', 'Postmark documentation - message stream' ) )
					);
					?>
				</p>
			</div>
		</div>

		<?php
	}
}
