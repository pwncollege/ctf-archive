<?php

namespace WPMailSMTP\Providers\Outlook;

use WPMailSMTP\WP;

/**
 * Class Provider.
 *
 * @since 4.3.0
 */
class Provider {

	/**
	 * Register hooks.
	 *
	 * @since 4.3.0
	 */
	public function hooks() {

		// Maybe display basic auth deprecation notice.
		add_action( 'admin_init', [ $this, 'maybe_display_basic_auth_notice' ] );
	}

	/**
	 * Display basic auth deprecation notice.
	 *
	 * @since 4.3.0
	 */
	public function maybe_display_basic_auth_notice() {

		$connection = wp_mail_smtp()->get_connections_manager()->get_primary_connection();

		// Bail if Other SMTP is not the current mailer.
		if ( $connection->get_mailer_slug() !== 'smtp' ) {
			return;
		}

		$host        = $connection->get_options()->get( 'smtp', 'host' );
		$host_suffix = strtolower( implode( '.', array_slice( explode( '.', $host ), - 2 ) ) );
		$domains     = [
			'live.com',
			'hotmail.com',
			'outlook.com',
			'office.com',
			'office365.com',
		];

		// Bail if current SMTP host is not Microsoft-related.
		if ( ! in_array( $host_suffix, $domains, true ) ) {
			return;
		}

		$message = wp_kses(
			sprintf( /* translators: %1$s - plugin name; %2$s - documentation link. */
				__( '<strong>%1$s</strong><br>Heads up! Microsoft is <a href="%2$s" target="_blank" rel="noopener noreferrer">discontinuing support for basic SMTP connections</a>. To continue using Outlook or Hotmail, switch to our Outlook mailer for uninterrupted email sending.', 'wp-mail-smtp' ),
				esc_html__( 'WP Mail SMTP', 'wp-mail-smtp' ),
				wp_mail_smtp()->get_utm_url(
					'https://wpmailsmtp.com/microsoft-outlook-smtp-how-to-fix-basic-authentication-error/',
					[
						'medium'  => 'outlook-smtp-notice',
						'content' => 'other-smtp-lite-to-outlook',
					]
				)
			),
			[
				'strong' => [],
				'br'     => [],
				'a'      => [
					'href'   => [],
					'rel'    => [],
					'target' => [],
				],
			]
		);

		if ( ! wp_mail_smtp()->is_pro() ) {
			$message = wp_kses(
				sprintf( /* translators: %1$s - Notice message; %2$s - upgrade link. */
					__( '%1$s <a href="%2$s" target="_blank" rel="noopener noreferrer">Upgrade to Pro now for easy, one-click Outlook setup</a>.', 'wp-mail-smtp' ),
					$message,
					wp_mail_smtp()->get_upgrade_link( [ 'medium' => 'outlook-smtp-notice', 'content' => 'other-smtp-lite-to-outlook' ] ) // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
				),
				[
					'strong' => [],
					'br'     => [],
					'a'      => [
						'href'   => [],
						'rel'    => [],
						'target' => [],
					],
				]
			);
		}

		WP::add_admin_notice(
			$message,
			implode( ' ', [ WP::ADMIN_NOTICE_ERROR, 'microsoft_basic_auth_deprecation_notice' ] ),
			true,
			'microsoft_basic_auth_deprecation'
		);
	}

	/**
	 * Dismiss basic auth deprecation notice.
	 *
	 * @since      4.3.0
	 * @deprecated 4.5.0
	 */
	public function dismiss_basic_auth_notice() {

		_deprecated_function( __METHOD__, '4.5.0' );
	}
}
