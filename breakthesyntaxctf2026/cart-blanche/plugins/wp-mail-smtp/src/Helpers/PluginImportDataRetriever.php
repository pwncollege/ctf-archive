<?php

namespace WPMailSMTP\Helpers;

/**
 * Class for preparing import data from other SMTP plugins.
 *
 * @since 2.6.0
 */
class PluginImportDataRetriever {

	/**
	 * The slug of the SMTP plugin to prepare the data for.
	 *
	 * @since 2.6.0
	 *
	 * @var string
	 */
	private $slug;

	/**
	 * PluginImportDataRetriever constructor.
	 *
	 * @since 2.6.0
	 *
	 * @param string $slug The SMTP plugin slug.
	 */
	public function __construct( $slug ) {

		$this->slug = $slug;
	}

	/**
	 * Get the data for the current plugin slug.
	 *
	 * @since 2.6.0
	 *
	 * @return false|array
	 */
	public function get() {

		$method_name = preg_replace( '/[\-]/', '_', sanitize_key( "get_$this->slug" ) );

		if ( method_exists( $this, $method_name ) ) {
			return $this->$method_name();
		}

		return false;
	}

	/**
	 * Check if Easy WP SMTP plugin settings are present and extract them.
	 *
	 * @since 2.6.0
	 *
	 * @return array
	 */
	private function get_easy_smtp() {

		$options = get_option( 'swpsmtp_options' );

		if ( empty( $options ) ) {
			return [];
		}

		return [
			'mail' => [
				'mailer'          => 'smtp',
				'from_email'      => isset( $options['from_email_field'] ) ? $options['from_email_field'] : '',
				'from_name'       => isset( $options['from_name_field'] ) ? $options['from_name_field'] : '',
				'from_name_force' => isset( $options['force_from_name_replace'] ) ? $options['force_from_name_replace'] : false,
			],
			'smtp' => [
				'host'       => isset( $options['smtp_settings']['host'] ) ? $options['smtp_settings']['host'] : '',
				'encryption' => isset( $options['smtp_settings']['type_encryption'] ) ? $options['smtp_settings']['type_encryption'] : 'none',
				'port'       => isset( $options['smtp_settings']['port'] ) ? $options['smtp_settings']['port'] : 25,
				'auth'       => isset( $options['smtp_settings']['autentication'] ) ? $options['smtp_settings']['autentication'] : true,
				'user'       => isset( $options['smtp_settings']['username'] ) ? $options['smtp_settings']['username'] : '',
				'pass'       => '',
				'autotls'    => true,
			],
		];
	}

	/**
	 * Check if FluentSMTP plugin settings are present and extract them.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	private function get_fluent_smtp() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded

		$options = get_option( 'fluentmail-settings' );

		if ( empty( $options ) ) {
			return [];
		}

		if ( empty( $options['misc']['default_connection'] ) || empty( $options['connections'][ $options['misc']['default_connection'] ]['provider_settings'] ) ) {
			return [];
		}

		$fluent_data = $options['connections'][ $options['misc']['default_connection'] ]['provider_settings'];

		$allowed_mailers = [
			'smtp'       => 'smtp',
			'ses'        => 'amazonses',
			'mailgun'    => 'mailgun',
			'sendgrid'   => 'sendgrid',
			'sendinblue' => 'sendinblue',
			'sparkpost'  => 'sparkpost',
			'postmark'   => 'postmark',
			'outlook'    => 'outlook',
		];

		if ( empty( $fluent_data['provider'] ) || ! in_array( $fluent_data['provider'], array_keys( $allowed_mailers ), true ) ) {
			return [];
		}

		$data = [
			'mail' => [
				'mailer'           => $allowed_mailers[ $fluent_data['provider'] ],
				'from_email'       => isset( $fluent_data['sender_email'] ) ? $fluent_data['sender_email'] : '',
				'from_name'        => isset( $fluent_data['sender_name'] ) ? $fluent_data['sender_name'] : '',
				'from_email_force' => isset( $fluent_data['force_from_email'] ) && $fluent_data['force_from_email'] === 'yes',
				'from_name_force'  => isset( $fluent_data['force_from_name'] ) && $fluent_data['force_from_name'] === 'yes',
			],
		];

		switch ( $data['mail']['mailer'] ) {
			case 'smtp':
				$data['smtp'] = [
					'host'       => isset( $fluent_data['host'] ) ? $fluent_data['host'] : '',
					'encryption' => isset( $fluent_data['encryption'] ) && in_array( $fluent_data['encryption'], [ 'none', 'ssl', 'tls' ], true ) ? $fluent_data['encryption'] : 'none',
					'port'       => isset( $fluent_data['port'] ) ? $fluent_data['port'] : 25,
					'auth'       => isset( $fluent_data['auth'] ) && $fluent_data['auth'] === 'yes',
					'user'       => isset( $fluent_data['username'] ) ? $fluent_data['username'] : '',
					'pass'       => isset( $fluent_data['password'] ) ? $fluent_data['password'] : '',
					'autotls'    => isset( $fluent_data['auto_tls'] ) && $fluent_data['auto_tls'] === 'yes',
				];
				break;

			case 'amazonses':
				$data['amazonses'] = [
					'client_id'     => isset( $fluent_data['access_key'] ) ? $fluent_data['access_key'] : '',
					'client_secret' => isset( $fluent_data['secret_key'] ) ? $fluent_data['secret_key'] : '',
					'region'        => isset( $fluent_data['region'] ) ? $fluent_data['region'] : '',
				];
				break;

			case 'mailgun':
				$data['mailgun'] = [
					'api_key' => isset( $fluent_data['api_key'] ) ? $fluent_data['api_key'] : '',
					'domain'  => isset( $fluent_data['domain_name'] ) ? $fluent_data['domain_name'] : '',
					'region'  => isset( $fluent_data['region'] ) && in_array( $fluent_data['region'], [ 'us', 'eu' ], true ) ? strtoupper( $fluent_data['region'] ) : '',
				];
				break;

			case 'sendgrid':
				$data['sendgrid'] = [
					'api_key' => isset( $fluent_data['api_key'] ) ? $fluent_data['api_key'] : '',
				];
				break;

			case 'sendinblue':
				$data['sendinblue'] = [
					'api_key' => isset( $fluent_data['api_key'] ) ? $fluent_data['api_key'] : '',
				];
				break;

			case 'sparkpost':
				$data['sparkpost'] = [
					'api_key' => isset( $fluent_data['api_key'] ) ? $fluent_data['api_key'] : '',
				];
				break;

			case 'postmark':
				$data['postmark'] = [
					'api_key'        => isset( $fluent_data['api_key'] ) ? $fluent_data['api_key'] : '',
					'message_stream' => isset( $fluent_data['message_stream'] ) ? $fluent_data['message_stream'] : '',
				];
				break;

			case 'outlook':
				$data['outlook'] = [
					'client_id'     => isset( $fluent_data['client_id'] ) ? $fluent_data['client_id'] : '',
					'client_secret' => isset( $fluent_data['client_secret'] ) ? $fluent_data['client_secret'] : '',
				];
				break;
		}

		return $data;
	}

	/**
	 * Check if Post SMTP Mailer plugin settings are present and extract them.
	 *
	 * @since 2.6.0
	 *
	 * @return array
	 */
	private function get_post_smtp_mailer() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded

		$options = get_option( 'postman_options' );

		if ( empty( $options ) ) {
			return [];
		}

		$allowed_mailers = [
			'smtp'         => 'smtp',
			'gmail_api'    => 'gmail',
			'sendgrid_api' => 'sendgrid',
			'mailgun_api'  => 'mailgun',
		];

		$data = [
			'mail'     => [
				'mailer'     => ( isset( $options['transport_type'] ) && in_array( $options['transport_type'], array_keys( $allowed_mailers ), true ) ) ? $allowed_mailers[ $options['transport_type'] ] : 'mail',
				'from_email' => isset( $options['sender_email'] ) ? $options['sender_email'] : '',
				'from_name'  => isset( $options['sender_name'] ) ? $options['sender_name'] : '',
			],
			'smtp'     => [
				'host'       => isset( $options['hostname'] ) ? $options['hostname'] : '',
				'encryption' => isset( $options['enc_type'] ) ? $options['enc_type'] : 'none',
				'port'       => isset( $options['port'] ) ? $options['port'] : 25,
				'auth'       => isset( $options['auth_type'] ) && $options['auth_type'] !== 'none',
				'user'       => isset( $options['basic_auth_username'] ) ? $options['basic_auth_username'] : '',
				'pass'       => ! empty( $options['basic_auth_password'] ) ? base64_decode( $options['basic_auth_password'] ) : '', // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
				'autotls'    => true,
			],
			'gmail'    => [
				'client_id'     => isset( $options['oauth_client_id'] ) ? $options['oauth_client_id'] : '',
				'client_secret' => isset( $options['oauth_client_secret'] ) ? $options['oauth_client_secret'] : '',
			],
			'sendgrid' => [
				'api_key' => ! empty( $options['sendgrid_api_key'] ) ? base64_decode( $options['sendgrid_api_key'] ) : '', // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
			],
			'mailgun'  => [
				'api_key' => ! empty( $options['mailgun_api_key'] ) ? base64_decode( $options['mailgun_api_key'] ) : '', // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
				'domain'  => isset( $options['mailgun_domain_name'] ) ? $options['mailgun_domain_name'] : '',
				'region'  => ( isset( $options['mailgun_region'] ) && ! empty( $options['mailgun_region'] ) ) ? 'EU' : 'US',
			],
		];

		if ( class_exists( '\PostmanOptions' ) ) {
			$pm_options = \PostmanOptions::getInstance();

			$data['sendgrid']['api_key'] = $pm_options->getSendGridApiKey();
			$data['mailgun']['api_key']  = $pm_options->getMailgunApiKey();
			$data['smtp']['pass']        = $pm_options->getPassword();
		}

		return $data;
	}

	/**
	 * Check if SMTP Mailer plugin settings are present and extract them.
	 *
	 * @since 2.6.0
	 *
	 * @return array
	 */
	private function get_smtp_mailer() {

		$options = get_option( 'smtp_mailer_options' );

		if ( empty( $options ) ) {
			return [];
		}

		return [
			'mail' => [
				'mailer'     => 'smtp',
				'from_email' => isset( $options['from_email'] ) ? $options['from_email'] : '',
				'from_name'  => isset( $options['from_name'] ) ? $options['from_name'] : '',
			],
			'smtp' => [
				'host'       => isset( $options['smtp_host'] ) ? $options['smtp_host'] : '',
				'encryption' => isset( $options['type_of_encryption'] ) ? $options['type_of_encryption'] : 'none',
				'port'       => isset( $options['smtp_port'] ) ? $options['smtp_port'] : 25,
				'auth'       => isset( $options['smtp_auth'] ) && $options['smtp_auth'] === 'true',
				'user'       => isset( $options['smtp_username'] ) ? $options['smtp_username'] : '',
				'pass'       => ! empty( $options['smtp_password'] ) ? base64_decode( $options['smtp_password'] ) : '', // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
				'autotls'    => true,
			],
		];
	}

	/**
	 * Check if WP SMTP plugin settings are present and extract them.
	 *
	 * @since 2.6.0
	 *
	 * @return array
	 */
	private function get_wp_smtp() {

		$options = get_option( 'wp_smtp_options' );

		if ( empty( $options ) ) {
			return [];
		}

		return [
			'mail' => [
				'mailer'     => 'smtp',
				'from_email' => isset( $options['from'] ) ? $options['from'] : '',
				'from_name'  => isset( $options['fromname'] ) ? $options['fromname'] : '',
			],
			'smtp' => [
				'host'       => isset( $options['host'] ) ? $options['host'] : '',
				'encryption' => ! empty( $options['smtpsecure'] ) ? $options['smtpsecure'] : 'none',
				'port'       => isset( $options['port'] ) ? $options['port'] : 25,
				'auth'       => isset( $options['smtpauth'] ) && $options['smtpauth'] === 'yes',
				'user'       => isset( $options['username'] ) ? $options['username'] : '',
				'pass'       => isset( $options['password'] ) ? $options['password'] : '',
				'autotls'    => true,
			],
		];
	}
}
