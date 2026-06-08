<?php

namespace WPMailSMTP\Providers\SMTP;

use WPMailSMTP\ConnectionInterface;
use WPMailSMTP\Providers\OptionsAbstract;

/**
 * Class SMTP.
 *
 * @since 1.0.0
 */
class Options extends OptionsAbstract {

	/**
	 * SMTP constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param ConnectionInterface $connection The Connection object.
	 */
	public function __construct( $connection = null ) {

		parent::__construct(
			[
				'logo_url'    => wp_mail_smtp()->assets_url . '/images/providers/smtp.svg',
				'slug'        => 'smtp',
				'title'       => esc_html__( 'Other SMTP', 'wp-mail-smtp' ),
				'description' => sprintf(
					wp_kses(
						/* translators: %s - URL to SMTP documentation. */
						__( 'The Other SMTP option lets you send emails through an SMTP server instead of using a provider\'s API. This is easy and convenient, but it\'s less secure than the other mailers. Please note that your provider may not allow you to send a large number of emails. In that case, please use a different mailer.<br><br>To get started, read our <a href="%s" target="_blank" rel="noopener noreferrer">Other SMTP documentation</a>.', 'wp-mail-smtp' ),
						[
							'br' => [],
							'a'  => [
								'href'   => [],
								'rel'    => [],
								'target' => [],
							],
						]
					),
					esc_url( wp_mail_smtp()->get_utm_url( 'https://wpmailsmtp.com/docs/how-to-set-up-the-other-smtp-mailer-in-wp-mail-smtp/', 'Other SMTP documentation' ) )
				),
			],
			$connection
		);
	}
}
