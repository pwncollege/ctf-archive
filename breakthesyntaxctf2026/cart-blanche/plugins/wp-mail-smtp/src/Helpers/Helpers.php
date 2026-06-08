<?php

namespace WPMailSMTP\Helpers;

use WPMailSMTP\Options;
use WPMailSMTP\WP;
use WP_Error;

/**
 * Class with all the misc helper functions that don't belong elsewhere.
 *
 * @since 3.0.0
 */
class Helpers {

	/**
	 * Check if the current active mailer has email send confirmation functionality.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	public static function mailer_without_send_confirmation() {

		return ! in_array(
			Options::init()->get( 'mail', 'mailer' ),
			[
				'sendlayer',
				'smtpcom',
				'sendinblue',
				'mailgun',
				'postmark',
				'sparkpost',
				'elasticemail',
				'smtp2go',
				'mailjet',
				'mailersend',
				'mandrill',
				'resend',
			],
			true
		);
	}

	/**
	 * Include mbstring polyfill.
	 *
	 * @since 3.1.0
	 */
	public static function include_mbstring_polyfill() {

		static $included = false;

		if ( $included === true ) {
			return;
		}

		require_once wp_mail_smtp()->plugin_path . '/vendor_prefixed/symfony/polyfill-mbstring/Mbstring.php';
		require_once wp_mail_smtp()->plugin_path . '/vendor_prefixed/symfony/polyfill-mbstring/bootstrap.php';

		$included = true;
	}

	/**
	 * Test if the REST API is accessible.
	 *
	 * @since 3.3.0
	 *
	 * @return true|\WP_Error
	 */
	public static function test_rest_availability() {

		$headers = [
			'Cache-Control' => 'no-cache',
		];

		/** This filter is documented in wp-includes/class-wp-http-streams.php */
		$sslverify = apply_filters( 'https_local_ssl_verify', false );

		$url = rest_url( 'wp-mail-smtp/v1' );

		$response = wp_remote_get(
			$url,
			[
				'headers'   => $headers,
				'sslverify' => $sslverify,
			]
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		} elseif ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return new WP_Error( wp_remote_retrieve_response_code( $response ), wp_remote_retrieve_body( $response ) );
		}

		return true;
	}

	/**
	 * Get string size in bytes.
	 *
	 * @since 3.4.0
	 *
	 * @param string $str String.
	 *
	 * @return int
	 */
	public static function strsize( $str ) {

		if ( ! function_exists( 'mb_strlen' ) ) {
			self::include_mbstring_polyfill();
		}

		return mb_strlen( $str, '8bit' );
	}

	/**
	 * Format error message.
	 *
	 * @since 3.4.0
	 *
	 * @param string $message     Error message.
	 * @param string $code        Error code.
	 * @param string $description Error description.
	 *
	 * @return string
	 */
	public static function format_error_message( $message, $code = '', $description = '' ) {

		$error_text = '';

		if ( ! empty( $code ) ) {
			$error_text .= $code . ': ';
		}

		if ( ! is_string( $message ) ) {
			$error_text .= wp_json_encode( $message );
		} else {
			$error_text .= $message;
		}

		if ( ! empty( $description ) ) {
			$error_text .= WP::EOL . $description;
		}

		return $error_text;
	}

	/**
	 * Get the default user agent.
	 *
	 * @since 3.9.0
	 *
	 * @return string
	 */
	public static function get_default_user_agent() {

		$license_type = wp_mail_smtp()->get_license_type();

		return 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ) . '; WPMailSMTP/' . $license_type . '-' . WPMS_PLUGIN_VER;
	}

	/**
	 * Import Plugin_Upgrader class from core.
	 *
	 * @since 3.11.0
	 */
	public static function include_plugin_upgrader() {

		/** \WP_Upgrader class */
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		/** \Plugin_Upgrader class */
		require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';
	}

	/**
	 * Whether the current request is a WP CLI request.
	 *
	 * @since 4.0.0
	 */
	public static function is_wp_cli() {

		return defined( 'WP_CLI' ) && WP_CLI;
	}
}
