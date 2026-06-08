<?php

namespace WPMailSMTP\Providers\ElasticEmail;

use WPMailSMTP\Helpers\UI;
use WPMailSMTP\Providers\OptionsAbstract;

/**
 * Class Options.
 *
 * @since 4.3.0
 */
class Options extends OptionsAbstract {

	/**
	 * Mailer slug.
	 *
	 * @since 4.3.0
	 */
	const SLUG = 'elasticemail';

	/**
	 * Options constructor.
	 *
	 * @since 4.3.0
	 *
	 * @param ConnectionInterface $connection The Connection object.
	 */
	public function __construct( $connection = null ) {

		if ( is_null( $connection ) ) {
			$connection = wp_mail_smtp()->get_connections_manager()->get_primary_connection();
		}

		$description = sprintf(
			wp_kses( /* translators: %1$s - URL to ElasticEmail.com site. */
				__( '<a href="%1$s" target="_blank" rel="noopener noreferrer">Elastic Email</a> is a cloud-based email marketing platform offering tools for email campaigns, automation, transactional emails, and analytics, designed for businesses of all sizes.<br><br>If you\'re just starting out, you can use Elastic Email\'s free plan to send emails to your account address from one of your verified email addresses. You don\'t need to use a credit card to try it out. When you\'re ready, you can upgrade to a higher plan.', 'wp-mail-smtp' ) .
				'<br><br>' .
				/* translators: %2$s - URL to wpmailsmtp.com doc. */
				__( 'To get started, read our <a href="%2$s" target="_blank" rel="noopener noreferrer">Elastic Email documentation</a>.', 'wp-mail-smtp' ),
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
			'https://www.elasticemail.com/',
			esc_url( wp_mail_smtp()->get_utm_url( 'https://wpmailsmtp.com/docs/how-to-set-up-the-elastic-email-mailer-in-wp-mail-smtp/', 'Elastic Email documentation' ) )
		);

		parent::__construct(
			[
				'logo_url'    => wp_mail_smtp()->assets_url . '/images/providers/elasticemail.svg',
				'slug'        => self::SLUG,
				'title'       => esc_html__( 'Elastic Email', 'wp-mail-smtp' ),
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
	 * @since 4.3.0
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
					<?php $this->display_const_set_message( 'WPMS_ELASTICEMAIL_API_KEY' ); ?>
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
						esc_html__( 'Follow this link to get an API Key from Elastic Email: %s.', 'wp-mail-smtp' ),
						'<a href="https://app.elasticemail.com/api/settings/manage-api" target="_blank" rel="noopener noreferrer">' .
						esc_html__( 'Get API Key', 'wp-mail-smtp' ) .
						'</a>'
					);
					?>
				</p>
			</div>
		</div>

		<?php
	}
}
