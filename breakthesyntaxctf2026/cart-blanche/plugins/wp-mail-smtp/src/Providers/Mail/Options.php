<?php

namespace WPMailSMTP\Providers\Mail;

use WPMailSMTP\Admin\SetupWizard;
use WPMailSMTP\Providers\OptionsAbstract;

/**
 * Class Option.
 *
 * @since 1.0.0
 */
class Options extends OptionsAbstract {

	/**
	 * Mail constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		parent::__construct(
			array(
				'logo_url' => wp_mail_smtp()->assets_url . '/images/providers/php.svg',
				'slug'     => 'mail',
				'title'    => esc_html__( 'Default (none)', 'wp-mail-smtp' ),
			)
		);
	}

	/**
	 * @inheritdoc
	 */
	public function display_options() {
		?>

		<blockquote>
			<?php
			printf(
				wp_kses( /* translators: %1$s - URL to all mailer doc page. %2$s - URL to the setup wizard. */
					__( 'You currently have the <strong>Default (none)</strong> mailer selected, which won\'t improve email deliverability. Please select <a href="%1$s" target="_blank" rel="noopener noreferrer">any other email provider</a> and use the easy <a href="%2$s">Setup Wizard</a> to configure it.', 'wp-mail-smtp' ),
					[
						'strong' => [],
						'a'      => [
							'href'   => [],
							'rel'    => [],
							'target' => [],
						],
					]
				),
				esc_url( wp_mail_smtp()->get_utm_url( 'https://wpmailsmtp.com/docs/a-complete-guide-to-wp-mail-smtp-mailers/', 'Default mailer - any other email provider' ) ),
				esc_url( SetupWizard::get_site_url() )
			);
			?>
		</blockquote>

		<?php
	}
}
