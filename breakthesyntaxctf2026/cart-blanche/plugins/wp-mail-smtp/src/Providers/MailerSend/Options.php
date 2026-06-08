<?php

namespace WPMailSMTP\Providers\MailerSend;

use WPMailSMTP\ConnectionInterface;
use WPMailSMTP\Providers\OptionsAbstract;
use WPMailSMTP\Helpers\UI;

/**
 * Class Options.
 *
 * @since 4.5.0
 */
class Options extends OptionsAbstract {

	/**
	 * Mailer slug.
	 *
	 * @since 4.5.0
	 *
	 * @var string
	 */
	const SLUG = 'mailersend';

	/**
	 * Options constructor.
	 *
	 * @since 4.5.0
	 *
	 * @param ConnectionInterface $connection The Connection object.
	 */
	public function __construct( $connection = null ) {

		if ( is_null( $connection ) ) {
			$connection = wp_mail_smtp()->get_connections_manager()->get_primary_connection();
		}

		$description = sprintf(
			wp_kses(
			/* translators: %1$s - URL to mailersend.com; %2$s - URL to MailerSend documentation on wpmailsmtp.com. */
				__( '<a href="%1$s" target="_blank" rel="noopener noreferrer">MailerSend</a> is a reliable transactional email provider with powerful features. They offer 12,000 emails per month for free and have affordable plans for higher volumes. Their modern API and excellent deliverability make them a great choice for WordPress sites.<br><br>To get started, read our <a href="%2$s" target="_blank" rel="noopener noreferrer">MailerSend documentation</a>.', 'wp-mail-smtp' ),
				[
					'strong' => [],
					'br'     => [],
					'a'      => [
						'href'   => [],
						'rel'    => [],
						'target' => [],
					],
				]
			),
			esc_url( 'https://mailersend.com/' ),
			// phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
			esc_url( wp_mail_smtp()->get_utm_url( 'https://wpmailsmtp.com/docs/how-to-set-up-the-mailersend-mailer-in-wp-mail-smtp/', 'MailerSend Documentation' ) )
		);

		parent::__construct(
			[
				'logo_url'    => wp_mail_smtp()->assets_url . '/images/providers/mailersend.svg',
				'slug'        => self::SLUG,
				'title'       => esc_html__( 'MailerSend', 'wp-mail-smtp' ),
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
	 * @since 4.5.0
	 */
	public function display_options() {

		$get_api_key_url = 'https://app.mailersend.com/api-tokens';
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
					<?php $this->display_const_set_message( 'WPMS_MAILERSEND_API_KEY' ); ?>
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
						esc_html__( 'Follow this link to get an API Key from MailerSend: %s.', 'wp-mail-smtp' ),
						'<a href="' . esc_url( $get_api_key_url ) . '" target="_blank" rel="noopener noreferrer">' .
						esc_html__( 'Get API Key', 'wp-mail-smtp' ) .
						'</a>'
					);
					?>
				</p>
			</div>
		</div>

		<!-- Professional Plan Features -->
		<div id="wp-mail-smtp-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-has_pro_plan" class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-checkbox-toggle wp-mail-smtp-clear">
			<div class="wp-mail-smtp-setting-label">
				<label for="wp-mail-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-has_pro_plan">
					<?php esc_html_e( 'Professional Plan', 'wp-mail-smtp' ); ?>
				</label>
			</div>
			<div class="wp-mail-smtp-setting-field">
				<?php
				UI::toggle(
					[
						'name'     => 'wp-mail-smtp[' . $this->get_slug() . '][has_pro_plan]',
						'id'       => 'wp-mail-smtp-setting-' . $this->get_slug() . '-has_pro_plan',
						'value'    => 'true',
						'checked'  => (bool) $this->connection_options->get( $this->get_slug(), 'has_pro_plan' ),
						'disabled' => $this->connection_options->is_const_defined( $this->get_slug(), 'has_pro_plan' ),
					]
				);
				?>
				<p class="desc">
					<?php
					printf(
						/* translators: %s - MailerSend pricing page URL. */
						esc_html__( 'Activate if you have a Professional or higher plan with MailerSend. This allows you to use custom headers. For more information about MailerSend plans, check their %s.', 'wp-mail-smtp' ),
						'<a href="https://www.mailersend.com/pricing" target="_blank" rel="noopener noreferrer">' .
						esc_html__( 'pricing page', 'wp-mail-smtp' ) .
						'</a>'
					);
					?>
				</p>
			</div>
		</div>

		<?php
	}
}
