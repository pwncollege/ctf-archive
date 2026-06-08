<?php

namespace WPMailSMTP;

use WPMailSMTP\Helpers\Crypto;
use WPMailSMTP\Reports\Emails\Summary as SummaryReportEmail;
use WPMailSMTP\UsageTracking\UsageTracking;

/**
 * Class Options to handle all options management.
 * WordPress does all the heavy work for caching get_option() data,
 * so we don't have to do that. But we want to minimize cyclomatic complexity
 * of calling a bunch of WP functions, thus we will cache them in a class as well.
 *
 * @since 1.0.0
 */
class Options {

	/**
	 * All the options keys.
	 *
	 * @since 1.3.0
	 * @since 1.4.0 Added Mailgun:region.
	 * @since 1.5.0 Added Outlook/AmazonSES.
	 * @since 1.8.0 Added Pepipost API.
	 * @since 2.0.0 Added SMTP.com API.
	 *
	 * @var array Map of all the default options of the plugin.
	 */
	private static $map = [
		'mail'                     => [
			'from_name',
			'from_email',
			'mailer',
			'return_path',
			'from_name_force',
			'from_email_force',
		],
		'smtp'                     => [
			'host',
			'port',
			'encryption',
			'autotls',
			'auth',
			'user',
			'pass',
		],
		'gmail'                    => [
			'one_click_setup_enabled',
			'client_id',
			'client_secret',
		],
		'outlook'                  => [
			'one_click_setup_enabled',
			'client_id',
			'client_secret',
		],
		'zoho'                     => [
			'domain',
			'client_id',
			'client_secret',
		],
		'amazonses'                => [
			'client_id',
			'client_secret',
			'region',
		],
		'mailgun'                  => [
			'api_key',
			'domain',
			'region',
		],
		'mailjet'                  => [
			'api_key',
			'secret_key',
		],
		'mailersend'               => [
			'api_key',
			'has_pro_plan',
		],
		'mandrill'                 => [
			'api_key',
		],
		'sendgrid'                 => [
			'api_key',
			'domain',
		],
		'sparkpost'                => [
			'api_key',
			'region',
		],
		'postmark'                 => [
			'server_api_token',
			'message_stream',
		],
		'smtpcom'                  => [
			'api_key',
			'channel',
		],
		'sendinblue'               => [
			'api_key',
			'domain',
		],
		'sendlayer'                => [
			'api_key',
		],
		'elasticemail'             => [
			'api_key',
		],
		'smtp2go'                  => [
			'api_key',
		],
		'resend'                   => [
			'api_key',
		],
		'pepipostapi'              => [
			'api_key',
		],
		'pepipost'                 => [
			'host',
			'port',
			'encryption',
			'auth',
			'user',
			'pass',
		],
		'license'                  => [
			'key',
		],
		'alert_email'              => [
			'enabled',
			'connections',
		],
		'alert_slack_webhook'      => [
			'enabled',
			'connections',
		],
		'alert_discord_webhook'    => [
			'enabled',
			'connections',
		],
		'alert_twilio_sms'         => [
			'enabled',
			'connections',
		],
		'alert_custom_webhook'     => [
			'enabled',
			'connections',
		],
		'alert_push_notifications' => [
			'enabled',
			'connections',
		],
		'alert_whatsapp'           => [
			'enabled',
			'connections',
		],
		'alert_events'             => [
			'email_hard_bounced',
		],
	];

	/**
	 * List of all mailers (except PHP default mailer 'mail').
	 *
	 * @since 3.3.0
	 *
	 * @var string[]
	 */
	public static $mailers = [
		'sendlayer',
		'smtpcom',
		'sendinblue',
		'amazonses',
		'gmail',
		'mailgun',
		'mailjet',
		'mailersend',
		'mandrill',
		'outlook',
		'postmark',
		'resend',
		'sendgrid',
		'sparkpost',
		'zoho',
		'elasticemail',
		'smtp2go',
		'smtp',
		'pepipost',
		'pepipostapi',
	];

	/**
	 * That's where plugin options are saved in wp_options table.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const META_KEY = 'wp_mail_smtp';

	/**
	 * All instances of Options class that should be notified about options update.
	 *
	 * @since 3.7.0
	 *
	 * @var Options[]
	 */
	protected static $update_observers;

	/**
	 * Options data.
	 *
	 * @since 3.7.0
	 *
	 * @var array
	 */
	protected $options = [];

	/**
	 * Init the Options class.
	 * TODO: add a flag to process without retrieving const values.
	 *
	 * @since 1.0.0
	 * @since 3.3.0 Deprecated instantiation via new keyword. `Options::init()` must be used.
	 */
	public function __construct() {

		// Store all class instances that will be notified about options update.
		static::$update_observers[] = $this;

		$this->populate_options();
	}

	/**
	 * Initialize all the options.
	 *
	 * @since 1.0.0
	 *
	 * @return Options
	 */
	public static function init() {

		static $instance;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Whether current class is a main options.
	 *
	 * @since 3.7.0
	 *
	 * @var bool
	 */
	protected function is_main_options() {

		return true;
	}

	/**
	 * Default options that are saved on plugin activation.
	 *
	 * @since 1.3.0
	 * @since 2.1.0 Set the Force from email to "on" by default.
	 *
	 * @return array
	 */
	public static function get_defaults() {

		$defaults = [
			'mail'    => [
				'from_email'       => get_option( 'admin_email' ),
				'from_name'        => get_bloginfo( 'name' ),
				'mailer'           => 'mail',
				'return_path'      => true,
				'from_email_force' => true,
				'from_name_force'  => false,
			],
			'smtp'    => [
				'autotls' => true,
				'auth'    => true,
			],
			'general' => [
				SummaryReportEmail::SETTINGS_SLUG => ! is_multisite() ? false : true,
			],
		];

		/**
		 * Filters the default options.
		 *
		 * @since 3.11.0
		 *
		 * @param array $defaults Default options.
		 */
		return apply_filters( 'wp_mail_smtp_options_get_defaults', $defaults );
	}

	/**
	 * Retrieve all options of the plugin.
	 *
	 * @since 1.0.0
	 * @since 2.2.0 Added the filter.
	 */
	protected function populate_options() {

		$this->options = apply_filters( 'wp_mail_smtp_populate_options', get_option( static::META_KEY, [] ) );
	}

	/**
	 * Get all the options.
	 *
	 * Options::init()->get_all();
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_all() {

		$options = $this->options;

		foreach ( $options as $group => $g_value ) {
			foreach ( $g_value as $key => $value ) {
				$options[ $group ][ $key ] = $this->get( $group, $key );
			}
		}

		return $this->is_main_options() ? apply_filters( 'wp_mail_smtp_options_get_all', $options ) : $options;
	}

	/**
	 * Get all the options for a group.
	 *
	 * Options::init()->get_group('smtp') - will return the array of options for the group, including defaults and constants.
	 *
	 * @since 1.0.0
	 * @since 1.5.0 Process values through the get() method which is aware of constants.
	 *
	 * @param string $group
	 *
	 * @return array
	 */
	public function get_group( $group ) {

		// Just to feel safe.
		$group = sanitize_key( $group );

		/*
		 * Get the values saved in DB.
		 * If plugin is configured with constants right from the start - this will not have all the values.
		 */
		$options = isset( $this->options[ $group ] ) ? $this->options[ $group ] : [];

		// We need to process certain constants-aware options through actual constants.
		if ( isset( self::$map[ $group ] ) ) {
			foreach ( self::$map[ $group ] as $key ) {
				$options[ $key ] = $this->get( $group, $key );
			}
		}

		return $this->is_main_options() ? apply_filters( 'wp_mail_smtp_options_get_group', $options, $group ) : $options;
	}

	/**
	 * Get options by a group and a key.
	 *
	 * Options::init()->get( 'smtp', 'host' ) - will return only SMTP 'host' option.
	 *
	 * @since 1.0.0
	 * @since 2.5.0 Added $strip_slashes method parameter.
	 *
	 * @param string $group         The option group.
	 * @param string $key           The option key.
	 * @param bool   $strip_slashes If the slashes should be stripped from string values.
	 *
	 * @return mixed|null Null if value doesn't exist anywhere: in constants, in DB, in a map. So it's completely custom or a typo.
	 */
	public function get( $group, $key, $strip_slashes = true ) {

		// Just to feel safe.
		$group = sanitize_key( $group );
		$key   = sanitize_key( $key );
		$value = null;

		// Get the const value if we have one.
		$value = $this->get_const_value( $group, $key, $value );

		// We don't have a const value.
		if ( $value === null ) {
			// Ordinary database or default values.
			if ( isset( $this->options[ $group ] ) ) {
				// Get the options key of a group.
				if ( isset( $this->options[ $group ][ $key ] ) ) {
					$value = $this->get_existing_option_value( $group, $key );
				} else {
					$value = $this->postprocess_key_defaults( $group, $key );
				}
			} else {
				/*
				 * Fallback to default if it doesn't exist in a map.
				 * Allow to retrieve only values from a map.
				 */
				if (
					isset( self::$map[ $group ] ) &&
					in_array( $key, self::$map[ $group ], true )
				) {
					$value = $this->postprocess_key_defaults( $group, $key );
				}
			}
		}

		// Conditionally strip slashes only from values saved in DB. Constants should be processed as is.
		if ( $strip_slashes && is_string( $value ) && ! $this->is_const_defined( $group, $key ) ) {
			$value = stripslashes( $value );
		}

		return $this->is_main_options() ? apply_filters( 'wp_mail_smtp_options_get', $value, $group, $key ) : $value;
	}

	/**
	 * Get the existing cached option value.
	 *
	 * @since 2.5.0
	 *
	 * @param string $group The options group.
	 * @param string $key   The options key.
	 *
	 * @return mixed
	 */
	private function get_existing_option_value( $group, $key ) {

		if ( $group === 'smtp' && $key === 'pass' ) {
			try {
				return Crypto::decrypt( $this->options[ $group ][ $key ] );
			} catch ( \Exception $e ) {
				return $this->options[ $group ][ $key ];
			}
		}

		return $this->options[ $group ][ $key ];
	}

	/**
	 * Some options may be non-empty by default,
	 * so we need to postprocess them to convert.
	 *
	 * @since 1.0.0
	 * @since 1.4.0 Added Mailgun:region.
	 * @since 1.5.0 Added Outlook/AmazonSES, license key support.
	 *
	 * @param string $group
	 * @param string $key
	 *
	 * @return mixed
	 */
	protected function postprocess_key_defaults( $group, $key ) {

		$value = '';

		switch ( $key ) {
			case 'from_email_force':
			case 'from_name_force':
			case 'return_path':
				$value = $group === 'mail' ? false : true;
				break;

			case 'mailer':
				$value = 'mail';
				break;

			case 'encryption':
				$value = in_array( $group, [ 'smtp', 'pepipost' ], true ) ? 'none' : $value;
				break;

			case 'region':
				$value = $group === 'mailgun' || $group === 'sparkpost' ? 'US' : $value;
				break;

			case 'auth':
			case 'autotls':
				$value = in_array( $group, [ 'smtp', 'pepipost' ], true ) ? false : true;
				break;

			case 'pass':
				$value = $this->get_const_value( $group, $key, $value );
				break;

			case 'type':
				$value = $group === 'license' ? 'lite' : '';
				break;
		}

		return apply_filters( 'wp_mail_smtp_options_postprocess_key_defaults', $value, $group, $key );
	}

	/**
	 * Process the options values through the constants check.
	 * If we have defined associated constant - use it instead of a DB value.
	 * Backward compatibility is hard.
	 * General section of options won't have constants, so we are omitting those checks and just return default value.
	 *
	 * @since 1.0.0
	 * @since 1.4.0 Added WPMS_MAILGUN_REGION.
	 * @since 1.5.0 Added Outlook/AmazonSES, license key support.
	 * @since 1.6.0 Added Sendinblue.
	 * @since 1.7.0 Added Do Not Send.
	 * @since 1.8.0 Added Pepipost API.
	 * @since 3.6.0 Added Debug Events Retention Period.
	 *
	 * @param string $group
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return mixed
	 */
	protected function get_const_value( $group, $key, $value ) {

		if ( ! $this->is_const_enabled() ) {
			return $value;
		}

		$return = null;

		// phpcs:disable WPForms.Formatting.Switch.AddEmptyLineBefore, WPForms.Formatting.Switch.RemoveEmptyLineBefore
		switch ( $group ) {
			case 'mail':
				switch ( $key ) {
					case 'from_name':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_MAIL_FROM_NAME : $value;
						break;
					case 'from_email':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_MAIL_FROM : $value;
						break;
					case 'mailer':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_MAILER : $value;
						break;
					case 'return_path':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_SET_RETURN_PATH : $value;
						break;
					case 'from_name_force':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_MAIL_FROM_NAME_FORCE : $value;
						break;
					case 'from_email_force':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_MAIL_FROM_FORCE : $value;
						break;
				}

				break;

			case 'smtp':
				switch ( $key ) {
					case 'host':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_SMTP_HOST : $value;
						break;
					case 'port':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_SMTP_PORT : $value;
						break;
					case 'encryption':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? ( WPMS_SSL === '' ? 'none' : WPMS_SSL ) : $value;
						break;
					case 'auth':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? (bool) WPMS_SMTP_AUTH : $value;
						break;
					case 'autotls':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? (bool) WPMS_SMTP_AUTOTLS : $value;
						break;
					case 'user':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_SMTP_USER : $value;
						break;
					case 'pass':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_SMTP_PASS : $value;
						break;
				}

				break;

			case 'sendlayer':
				switch ( $key ) {
					case 'api_key':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_SENDLAYER_API_KEY : $value;
						break;
				}

				break;

			case 'gmail':
				switch ( $key ) {
					case 'client_id':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_GMAIL_CLIENT_ID : $value;
						break;
					case 'client_secret':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_GMAIL_CLIENT_SECRET : $value;
						break;
				}

				break;

			case 'outlook':
				switch ( $key ) {
					case 'client_id':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_OUTLOOK_CLIENT_ID : $value;
						break;
					case 'client_secret':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_OUTLOOK_CLIENT_SECRET : $value;
						break;
				}

				break;

			case 'zoho':
				switch ( $key ) {
					case 'domain':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_ZOHO_DOMAIN : $value;
						break;
					case 'client_id':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_ZOHO_CLIENT_ID : $value;
						break;
					case 'client_secret':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_ZOHO_CLIENT_SECRET : $value;
						break;
				}

				break;

			case 'amazonses':
				switch ( $key ) {
					case 'client_id':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_AMAZONSES_CLIENT_ID : $value;
						break;
					case 'client_secret':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_AMAZONSES_CLIENT_SECRET : $value;
						break;
					case 'region':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_AMAZONSES_REGION : $value;
						break;
				}

				break;

			case 'mailgun':
				switch ( $key ) {
					case 'api_key':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_MAILGUN_API_KEY : $value;
						break;
					case 'domain':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_MAILGUN_DOMAIN : $value;
						break;
					case 'region':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_MAILGUN_REGION : $value;
						break;
				}

				break;

			case 'mailjet':
				switch ( $key ) {
					case 'api_key':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_MAILJET_API_KEY : $value;
						break;
					case 'secret_key':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_MAILJET_SECRET_KEY : $value;
						break;
				}

				break;

			case 'sendgrid':
				switch ( $key ) {
					case 'api_key':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_SENDGRID_API_KEY : $value;
						break;
					case 'domain':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_SENDGRID_DOMAIN : $value;
						break;
				}

				break;

			case 'sparkpost':
				switch ( $key ) {
					case 'api_key':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_SPARKPOST_API_KEY : $value;
						break;
					case 'region':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_SPARKPOST_REGION : $value;
						break;
				}

				break;

			case 'postmark':
				switch ( $key ) {
					case 'server_api_token':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_POSTMARK_SERVER_API_TOKEN : $value;
						break;
					case 'message_stream':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_POSTMARK_MESSAGE_STREAM : $value;
						break;
				}

				break;

			case 'smtpcom':
				switch ( $key ) {
					case 'api_key':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_SMTPCOM_API_KEY : $value;
						break;
					case 'channel':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_SMTPCOM_CHANNEL : $value;
						break;
				}

				break;

			case 'sendinblue':
				switch ( $key ) {
					case 'api_key':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_SENDINBLUE_API_KEY : $value;
						break;
					case 'domain':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_SENDINBLUE_DOMAIN : $value;
						break;
				}

				break;

			case 'elasticemail':
				switch ( $key ) {
					case 'api_key':
						$return = $this->is_const_defined( $group, $key ) ? WPMS_ELASTICEMAIL_API_KEY : $value;
						break;
				}

				break;

			case 'smtp2go':
				switch ( $key ) {
					case 'api_key':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_SMTP2GO_API_KEY : $value;
						break;
				}

				break;

			case 'resend':
				switch ( $key ) {
					case 'api_key':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_RESEND_API_KEY : $value;
						break;
				}

				break;

			case 'pepipostapi':
				switch ( $key ) {
					case 'api_key':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_PEPIPOST_API_KEY : $value;
						break;
				}

				break;

			case 'alert_email':
				switch ( $key ) {
					case 'connections':
						$return = $this->is_const_defined( $group, $key ) ? [ [ 'send_to' => WPMS_ALERT_EMAIL_SEND_TO ] ] : $value;
						break;
				}

				break;

			case 'alert_slack_webhook':
				switch ( $key ) {
					case 'connections':
						$return = $this->is_const_defined( $group, $key ) ? [ [ 'webhook_url' => WPMS_ALERT_SLACK_WEBHOOK_URL ] ] : $value;
						break;
				}

				break;

			case 'alert_discord_webhook':
				switch ( $key ) {
					case 'connections':
						$return = $this->is_const_defined( $group, $key ) ? [ [ 'webhook_url' => WPMS_ALERT_DISCORD_WEBHOOK_URL ] ] : $value;
						break;
				}

				break;

			case 'alert_teams_webhook':
				switch ( $key ) {
					case 'connections':
						$return = $this->is_const_defined( $group, $key ) ? [ [ 'webhook_url' => WPMS_ALERT_TEAMS_WEBHOOK_URL ] ] : $value;
						break;
				}

				break;

			case 'alert_twilio_sms':
				switch ( $key ) {
					case 'connections':
						if ( $this->is_const_defined( $group, $key ) ) {
							$return = [
								[
									'account_sid'       => WPMS_ALERT_TWILIO_SMS_ACCOUNT_SID,
									'auth_token'        => WPMS_ALERT_TWILIO_SMS_AUTH_TOKEN,
									'from_phone_number' => WPMS_ALERT_TWILIO_SMS_FROM_PHONE_NUMBER,
									'to_phone_number'   => WPMS_ALERT_TWILIO_SMS_TO_PHONE_NUMBER,
								],
							];
						} else {
							$return = $value;
						}
						break;
				}

				break;

			case 'alert_custom_webhook':
				switch ( $key ) {
					case 'connections':
						$return = $this->is_const_defined( $group, $key ) ? [ [ 'webhook_url' => WPMS_ALERT_CUSTOM_WEBHOOK_URL ] ] : $value;
						break;
				}

				break;

			case 'license':
				switch ( $key ) {
					case 'key':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_LICENSE_KEY : $value;
						break;
				}

				break;

			case 'general':
				switch ( $key ) {
					case 'do_not_send':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? WPMS_DO_NOT_SEND : $value;
						break;

					case SummaryReportEmail::SETTINGS_SLUG:
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ?
							$this->parse_boolean( WPMS_SUMMARY_REPORT_EMAIL_DISABLED ) :
							$value;
						break;

					case OptimizedEmailSending::SETTINGS_SLUG:
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ?
							$this->parse_boolean( WPMS_OPTIMIZED_EMAIL_SENDING_ENABLED ) :
							$value;
						break;
				}

				break;

			case 'debug_events':
				switch ( $key ) {
					case 'retention_period':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = $this->is_const_defined( $group, $key ) ? intval( WPMS_DEBUG_EVENTS_RETENTION_PERIOD ) : $value;
						break;
				}

				break;

			case 'mailersend':
				switch ( $key ) {
					case 'api_key':
						$return = $this->is_const_defined( $group, $key ) ? WPMS_MAILERSEND_API_KEY : $value;
						break;

					case 'has_pro_plan':
						$return = $this->is_const_defined( $group, $key ) ? $this->parse_boolean( WPMS_MAILERSEND_HAS_PRO_PLAN ) : $value;
						break;
				}
				break;

			case 'mandrill':
				if ( $key === 'api_key' ) {
					/** No inspection comment @noinspection PhpUndefinedConstantInspection */
					$return = $this->is_const_defined( $group, $key ) ? WPMS_MANDRILL_API_KEY : $value;
				}

				break;

			case 'alert_whatsapp':
				switch ( $key ) {
					case 'connections':
						if ( $this->is_const_defined( $group, $key ) ) {
							$return = [
								[
									'access_token'         => WPMS_ALERT_WHATSAPP_ACCESS_TOKEN,
									'whatsapp_business_id' => WPMS_ALERT_WHATSAPP_BUSINESS_ID,
									'phone_number_id'      => WPMS_ALERT_WHATSAPP_PHONE_NUMBER_ID,
									'to_phone_number'      => WPMS_ALERT_WHATSAPP_TO_PHONE_NUMBER,
									'template_language'    => WPMS_ALERT_WHATSAPP_TEMPLATE_LANGUAGE,
								],
							];
						} else {
							$return = $value;
						}
						break;
				}

				break;

			default:
				// Always return the default value if nothing from above matches the request.
				$return = $value;
		}

		// phpcs:enable WPForms.Formatting.Switch.AddEmptyLineBefore, WPForms.Formatting.Switch.RemoveEmptyLineBefore

		return apply_filters( 'wp_mail_smtp_options_get_const_value', $return, $group, $key, $value );
	}

	/**
	 * Whether constants redefinition is enabled or not.
	 *
	 * @since 1.0.0
	 * @since 1.5.0 Added filter to redefine the value.
	 *
	 * @return bool
	 */
	public function is_const_enabled() {

		$return = defined( 'WPMS_ON' ) && WPMS_ON === true;

		return apply_filters( 'wp_mail_smtp_options_is_const_enabled', $return );
	}

	/**
	 * We need this check to reuse later in admin area,
	 * to distinguish settings fields that were redefined,
	 * and display them differently.
	 *
	 * @since 1.0.0
	 * @since 1.5.0 Added a filter, Outlook/AmazonSES, license key support.
	 * @since 1.6.0 Added Sendinblue.
	 * @since 1.7.0 Added Do Not Send.
	 * @since 1.8.0 Added Pepipost API.
	 *
	 * @param string $group
	 * @param string $key
	 *
	 * @return bool
	 */
	public function is_const_defined( $group, $key ) {

		if ( ! $this->is_const_enabled() ) {
			return false;
		}

		// Just to feel safe.
		$group  = sanitize_key( $group );
		$key    = sanitize_key( $key );
		$return = false;

		// phpcs:disable WPForms.Formatting.Switch.AddEmptyLineBefore, WPForms.Formatting.Switch.RemoveEmptyLineBefore
		switch ( $group ) {
			case 'mail':
				switch ( $key ) {
					case 'from_name':
						$return = defined( 'WPMS_MAIL_FROM_NAME' ) && WPMS_MAIL_FROM_NAME;
						break;
					case 'from_email':
						$return = defined( 'WPMS_MAIL_FROM' ) && WPMS_MAIL_FROM;
						break;
					case 'mailer':
						$return = defined( 'WPMS_MAILER' ) && WPMS_MAILER;
						break;
					case 'return_path':
						$return = defined( 'WPMS_SET_RETURN_PATH' ) && ( WPMS_SET_RETURN_PATH === 'true' || WPMS_SET_RETURN_PATH === true );
						break;
					case 'from_name_force':
						$return = defined( 'WPMS_MAIL_FROM_NAME_FORCE' ) && ( WPMS_MAIL_FROM_NAME_FORCE === 'true' || WPMS_MAIL_FROM_NAME_FORCE === true );
						break;
					case 'from_email_force':
						$return = defined( 'WPMS_MAIL_FROM_FORCE' ) && ( WPMS_MAIL_FROM_FORCE === 'true' || WPMS_MAIL_FROM_FORCE === true );
						break;
				}

				break;

			case 'smtp':
				switch ( $key ) {
					case 'host':
						$return = defined( 'WPMS_SMTP_HOST' ) && WPMS_SMTP_HOST;
						break;
					case 'port':
						$return = defined( 'WPMS_SMTP_PORT' ) && WPMS_SMTP_PORT;
						break;
					case 'encryption':
						$return = defined( 'WPMS_SSL' );
						break;
					case 'auth':
						$return = defined( 'WPMS_SMTP_AUTH' );
						break;
					case 'autotls':
						$return = defined( 'WPMS_SMTP_AUTOTLS' );
						break;
					case 'user':
						$return = defined( 'WPMS_SMTP_USER' ) && WPMS_SMTP_USER;
						break;
					case 'pass':
						$return = defined( 'WPMS_SMTP_PASS' ) && WPMS_SMTP_PASS;
						break;
				}

				break;

			case 'sendlayer':
				switch ( $key ) {
					case 'api_key':
						$return = defined( 'WPMS_SENDLAYER_API_KEY' ) && WPMS_SENDLAYER_API_KEY;
						break;
				}

				break;

			case 'gmail':
				switch ( $key ) {
					case 'client_id':
						$return = defined( 'WPMS_GMAIL_CLIENT_ID' ) && WPMS_GMAIL_CLIENT_ID;
						break;
					case 'client_secret':
						$return = defined( 'WPMS_GMAIL_CLIENT_SECRET' ) && WPMS_GMAIL_CLIENT_SECRET;
						break;
				}

				break;

			case 'outlook':
				switch ( $key ) {
					case 'client_id':
						$return = defined( 'WPMS_OUTLOOK_CLIENT_ID' ) && WPMS_OUTLOOK_CLIENT_ID;
						break;
					case 'client_secret':
						$return = defined( 'WPMS_OUTLOOK_CLIENT_SECRET' ) && WPMS_OUTLOOK_CLIENT_SECRET;
						break;
				}

				break;

			case 'zoho':
				switch ( $key ) {
					case 'domain':
						$return = defined( 'WPMS_ZOHO_DOMAIN' ) && WPMS_ZOHO_DOMAIN;
						break;
					case 'client_id':
						$return = defined( 'WPMS_ZOHO_CLIENT_ID' ) && WPMS_ZOHO_CLIENT_ID;
						break;
					case 'client_secret':
						$return = defined( 'WPMS_ZOHO_CLIENT_SECRET' ) && WPMS_ZOHO_CLIENT_SECRET;
						break;
				}

				break;

			case 'amazonses':
				switch ( $key ) {
					case 'client_id':
						$return = defined( 'WPMS_AMAZONSES_CLIENT_ID' ) && WPMS_AMAZONSES_CLIENT_ID;
						break;
					case 'client_secret':
						$return = defined( 'WPMS_AMAZONSES_CLIENT_SECRET' ) && WPMS_AMAZONSES_CLIENT_SECRET;
						break;
					case 'region':
						$return = defined( 'WPMS_AMAZONSES_REGION' ) && WPMS_AMAZONSES_REGION;
						break;
				}

				break;

			case 'mailgun':
				switch ( $key ) {
					case 'api_key':
						$return = defined( 'WPMS_MAILGUN_API_KEY' ) && WPMS_MAILGUN_API_KEY;
						break;
					case 'domain':
						$return = defined( 'WPMS_MAILGUN_DOMAIN' ) && WPMS_MAILGUN_DOMAIN;
						break;
					case 'region':
						$return = defined( 'WPMS_MAILGUN_REGION' ) && WPMS_MAILGUN_REGION;
						break;
				}

				break;

			case 'mailjet':
				switch ( $key ) {
					case 'api_key':
						$return = defined( 'WPMS_MAILJET_API_KEY' ) && WPMS_MAILJET_API_KEY;
						break;
					case 'secret_key':
						$return = defined( 'WPMS_MAILJET_SECRET_KEY' ) && WPMS_MAILJET_SECRET_KEY;
						break;
				}

				break;

			case 'sendgrid':
				switch ( $key ) {
					case 'api_key':
						$return = defined( 'WPMS_SENDGRID_API_KEY' ) && WPMS_SENDGRID_API_KEY;
						break;
					case 'domain':
						$return = defined( 'WPMS_SENDGRID_DOMAIN' ) && WPMS_SENDGRID_DOMAIN;
						break;
				}

				break;

			case 'sparkpost':
				switch ( $key ) {
					case 'api_key':
						$return = defined( 'WPMS_SPARKPOST_API_KEY' ) && WPMS_SPARKPOST_API_KEY;
						break;
					case 'region':
						$return = defined( 'WPMS_SPARKPOST_REGION' ) && WPMS_SPARKPOST_REGION;
						break;
				}

				break;

			case 'postmark':
				switch ( $key ) {
					case 'server_api_token':
						$return = defined( 'WPMS_POSTMARK_SERVER_API_TOKEN' ) && WPMS_POSTMARK_SERVER_API_TOKEN;
						break;
					case 'message_stream':
						$return = defined( 'WPMS_POSTMARK_MESSAGE_STREAM' ) && WPMS_POSTMARK_MESSAGE_STREAM;
						break;
				}

				break;

			case 'smtpcom':
				switch ( $key ) {
					case 'api_key':
						$return = defined( 'WPMS_SMTPCOM_API_KEY' ) && WPMS_SMTPCOM_API_KEY;
						break;
					case 'channel':
						$return = defined( 'WPMS_SMTPCOM_CHANNEL' ) && WPMS_SMTPCOM_CHANNEL;
						break;
				}

				break;

			case 'sendinblue':
				switch ( $key ) {
					case 'api_key':
						$return = defined( 'WPMS_SENDINBLUE_API_KEY' ) && WPMS_SENDINBLUE_API_KEY;
						break;
					case 'domain':
						$return = defined( 'WPMS_SENDINBLUE_DOMAIN' ) && WPMS_SENDINBLUE_DOMAIN;
						break;
				}

				break;

			case 'elasticemail':
				switch ( $key ) {
					case 'api_key':
						$return = defined( 'WPMS_ELASTICEMAIL_API_KEY' ) && WPMS_ELASTICEMAIL_API_KEY;
						break;
				}

				break;

			case 'smtp2go':
				switch ( $key ) {
					case 'api_key':
						$return = defined( 'WPMS_SMTP2GO_API_KEY' ) && WPMS_SMTP2GO_API_KEY;
						break;
				}

				break;

			case 'resend':
				switch ( $key ) {
					case 'api_key':
						$return = defined( 'WPMS_RESEND_API_KEY' ) && WPMS_RESEND_API_KEY;
						break;
				}

				break;

			case 'pepipostapi':
				switch ( $key ) {
					case 'api_key':
						$return = defined( 'WPMS_PEPIPOST_API_KEY' ) && WPMS_PEPIPOST_API_KEY;
						break;
				}

				break;

			case 'alert_email':
				switch ( $key ) {
					case 'connections':
						$return = defined( 'WPMS_ALERT_EMAIL_SEND_TO' ) && WPMS_ALERT_EMAIL_SEND_TO;
						break;
				}

				break;

			case 'alert_slack_webhook':
				switch ( $key ) {
					case 'connections':
						$return = defined( 'WPMS_ALERT_SLACK_WEBHOOK_URL' ) && WPMS_ALERT_SLACK_WEBHOOK_URL;
						break;
				}

				break;

			case 'alert_discord_webhook':
				switch ( $key ) {
					case 'connections':
						$return = defined( 'WPMS_ALERT_DISCORD_WEBHOOK_URL' ) && WPMS_ALERT_DISCORD_WEBHOOK_URL;
						break;
				}

				break;

			case 'alert_teams_webhook':
				switch ( $key ) {
					case 'connections':
						$return = defined( 'WPMS_ALERT_TEAMS_WEBHOOK_URL' ) && WPMS_ALERT_TEAMS_WEBHOOK_URL;
						break;
				}

				break;

			case 'alert_twilio_sms':
				switch ( $key ) {
					case 'connections':
						$return = defined( 'WPMS_ALERT_TWILIO_SMS_ACCOUNT_SID' ) && WPMS_ALERT_TWILIO_SMS_ACCOUNT_SID &&
						          defined( 'WPMS_ALERT_TWILIO_SMS_AUTH_TOKEN' ) && WPMS_ALERT_TWILIO_SMS_AUTH_TOKEN &&
						          defined( 'WPMS_ALERT_TWILIO_SMS_FROM_PHONE_NUMBER' ) && WPMS_ALERT_TWILIO_SMS_FROM_PHONE_NUMBER &&
						          defined( 'WPMS_ALERT_TWILIO_SMS_TO_PHONE_NUMBER' ) && WPMS_ALERT_TWILIO_SMS_TO_PHONE_NUMBER;
						break;
				}

				break;

			case 'alert_custom_webhook':
				switch ( $key ) {
					case 'connections':
						$return = defined( 'WPMS_ALERT_CUSTOM_WEBHOOK_URL' ) && WPMS_ALERT_CUSTOM_WEBHOOK_URL;
						break;
				}

				break;

			case 'alert_whatsapp':
				switch ( $key ) {
					case 'connections':
						$return = defined( 'WPMS_ALERT_WHATSAPP_ACCESS_TOKEN' ) && WPMS_ALERT_WHATSAPP_ACCESS_TOKEN &&
						          defined( 'WPMS_ALERT_WHATSAPP_BUSINESS_ID' ) && WPMS_ALERT_WHATSAPP_BUSINESS_ID &&
						          defined( 'WPMS_ALERT_WHATSAPP_PHONE_NUMBER_ID' ) && WPMS_ALERT_WHATSAPP_PHONE_NUMBER_ID &&
						          defined( 'WPMS_ALERT_WHATSAPP_TO_PHONE_NUMBER' ) && WPMS_ALERT_WHATSAPP_TO_PHONE_NUMBER &&
						          defined( 'WPMS_ALERT_WHATSAPP_TEMPLATE_LANGUAGE' ) && WPMS_ALERT_WHATSAPP_TEMPLATE_LANGUAGE;
						break;
				}

				break;

			case 'license':
				switch ( $key ) {
					case 'key':
						$return = defined( 'WPMS_LICENSE_KEY' ) && WPMS_LICENSE_KEY;
						break;
				}

				break;

			case 'general':
				switch ( $key ) {
					case 'do_not_send':
						/** No inspection comment @noinspection PhpUndefinedConstantInspection */
						$return = defined( 'WPMS_DO_NOT_SEND' ) && WPMS_DO_NOT_SEND;
						break;

					case SummaryReportEmail::SETTINGS_SLUG:
						$return = defined( 'WPMS_SUMMARY_REPORT_EMAIL_DISABLED' );
						break;

					case OptimizedEmailSending::SETTINGS_SLUG:
						$return = defined( 'WPMS_OPTIMIZED_EMAIL_SENDING_ENABLED' );
						break;
				}

				break;

			case 'debug_events':
				switch ( $key ) {
					case 'retention_period':
						$return = defined( 'WPMS_DEBUG_EVENTS_RETENTION_PERIOD' );
						break;
				}

				break;

			case 'mailersend':
				switch ( $key ) {
					case 'api_key':
						$return = defined( 'WPMS_MAILERSEND_API_KEY' ) && WPMS_MAILERSEND_API_KEY;
						break;

					case 'has_pro_plan':
						$return = defined( 'WPMS_MAILERSEND_HAS_PRO_PLAN' );
						break;
				}
				break;

			case 'mandrill': // phpcs:ignore PSR2.ControlStructures.SwitchDeclaration.BodyOnNextLineCASE

				switch ( $key ) {
					case 'api_key':
						$return = defined( 'WPMS_MANDRILL_API_KEY' ) && WPMS_MANDRILL_API_KEY;
						break;
				}

				break;
		}

		// phpcs:enable WPForms.Formatting.Switch.AddEmptyLineBefore, WPForms.Formatting.Switch.RemoveEmptyLineBefore

		return apply_filters( 'wp_mail_smtp_options_is_const_defined', $return, $group, $key );
	}

	/**
	 * Set plugin options, all at once.
	 *
	 * @since 1.0.0
	 * @since 1.3.0 Added $once argument to save options only if they don't exist already.
	 * @since 1.4.0 Added Mailgun:region.
	 * @since 1.5.0 Added Outlook/AmazonSES, Email Log. Stop saving const values into DB.
	 * @since 2.5.0 Added $overwrite_existing method parameter.
	 *
	 * @param array $options            Plugin options to save.
	 * @param bool  $once               Whether to update existing options or to add these options only once.
	 * @param bool  $overwrite_existing Whether to overwrite existing settings or merge these passed options with existing ones.
	 */
	public function set( $options, $once = false, $overwrite_existing = true ) {

		// Merge existing settings with new values.
		if ( ! $overwrite_existing ) {
			$options = self::array_merge_recursive( $this->get_all_raw(), $options );
		}

		$options = $this->process_generic_options( $options );
		$options = $this->process_mailer_specific_options( $options );
		$options = apply_filters( 'wp_mail_smtp_options_set', $options );

		$this->save_options( $options, $once );

		do_action( 'wp_mail_smtp_options_set_after', $options );
	}

	/**
	 * Save options to DB.
	 *
	 * @since 3.7.0
	 *
	 * @param array $options Options to save.
	 * @param bool  $once    Whether to update existing options or to add these options only once.
	 */
	protected function save_options( $options, $once ) {

		// Whether to update existing options or to add these options only once if they don't exist yet.
		if ( $once ) {
			add_option( static::META_KEY, $options, '', 'no' ); // Do not autoload these options.
		} else {
			if ( is_multisite() && WP::use_global_plugin_settings() ) {
				update_blog_option( get_main_site_id(), static::META_KEY, $options );
			} else {
				update_option( static::META_KEY, $options, 'no' );
			}
		}

		// Now we need to re-cache values of all instances.
		foreach ( static::$update_observers as $observer ) {
			$observer->populate_options();
		}
	}

	/**
	 * Process the generic plugin options.
	 *
	 * @since 2.5.0
	 *
	 * @param array $options The options array.
	 *
	 * @return array
	 */
	protected function process_generic_options( $options ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded, Generic.Metrics.NestingLevel.MaxExceeded

		foreach ( (array) $options as $group => $keys ) {
			foreach ( $keys as $option_name => $option_value ) {
				switch ( $group ) {
					case 'mail':
						switch ( $option_name ) {
							case 'from_name':
								$options[ $group ][ $option_name ] = sanitize_text_field( $option_value );
								break;
							case 'mailer':
								$mailer = sanitize_text_field( $option_value );

								$mailer = in_array( $mailer, self::$mailers, true ) ? $mailer : 'mail';

								$options[ $group ][ $option_name ] = $mailer;
								break;
							case 'from_email':
								if ( filter_var( $option_value, FILTER_VALIDATE_EMAIL ) ) {
									$options[ $group ][ $option_name ] = sanitize_email( $option_value );
								} else {
									$options[ $group ][ $option_name ] = sanitize_email(
										wp_mail_smtp()->get_processor()->get_default_email()
									);
								}
								break;
							case 'return_path':
							case 'from_name_force':
							case 'from_email_force':
								$options[ $group ][ $option_name ] = (bool) $option_value;
								break;
						}
						break;

					case 'general':
						switch ( $option_name ) {
							case 'do_not_send':
							case 'am_notifications_hidden':
							case 'email_delivery_errors_hidden':
							case 'dashboard_widget_hidden':
							case 'uninstall':
							case UsageTracking::SETTINGS_SLUG:
							case SummaryReportEmail::SETTINGS_SLUG:
							case OptimizedEmailSending::SETTINGS_SLUG:
								$options[ $group ][ $option_name ] = (bool) $option_value;
								break;
						}

					case 'debug_events':
						switch ( $option_name ) {
							case 'email_debug':
								$options[ $group ][ $option_name ] = (bool) $option_value;
								break;
							case 'retention_period':
								$options[ $group ][ $option_name ] = (int) $option_value;
								break;
						}
				}
			}
		}

		return $options;
	}

	/**
	 * Process mailers-specific plugin options.
	 *
	 * @since 2.5.0
	 *
	 * @param array $options The options array.
	 *
	 * @return array
	 */
	protected function process_mailer_specific_options( $options ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded, Generic.Metrics.NestingLevel.MaxExceeded

		if (
			! empty( $options['mail']['mailer'] ) &&
			isset( $options[ $options['mail']['mailer'] ] ) &&
			in_array( $options['mail']['mailer'], self::$mailers, true )
		) {

			$mailer = $options['mail']['mailer'];

			foreach ( $options[ $mailer ] as $option_name => $option_value ) {
				switch ( $option_name ) {
					case 'host': // smtp.
					case 'user': // smtp.
					case 'encryption': // smtp.
					case 'region': // mailgun/amazonses/sparkpost.
						$options[ $mailer ][ $option_name ] = $this->is_const_defined( $mailer, $option_name ) ? '' : sanitize_text_field( $option_value );
						break; // smtp.
					case 'port':
						$options[ $mailer ][ $option_name ] = $this->is_const_defined( $mailer, $option_name ) ? 25 : (int) $option_value;
						break;
					case 'auth': // smtp.
					case 'autotls': // smtp.
						$option_value = (bool) $option_value;

						$options[ $mailer ][ $option_name ] = $this->is_const_defined( $mailer, $option_name ) ? false : $option_value;
						break;

					case 'pass': // smtp.
						// Do not process as they may contain certain special characters, but allow to be overwritten using constants.
						$option_value                       = trim( (string) $option_value );
						$options[ $mailer ][ $option_name ] = $this->is_const_defined( $mailer, $option_name ) ? '' : $option_value;

						if ( $mailer === 'smtp' && ! $this->is_const_defined( 'smtp', 'pass' ) ) {
							try {
								$options[ $mailer ][ $option_name ] = Crypto::encrypt( $option_value );
							} catch ( \Exception $e ) {
							} // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch, Squiz.Commenting.EmptyCatchComment.Missing, Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace
						}
						break;

					case 'api_key': // mailgun/sendgrid/sendinblue/pepipostapi/smtpcom/sparkpost/sendlayer/smtp2go/mailjet/elasticemail/resend.
					case 'secret_key': // mailjet.
					case 'domain': // mailgun/zoho/sendgrid/sendinblue.
					case 'client_id': // gmail/outlook/amazonses/zoho.
					case 'client_secret': // gmail/outlook/amazonses/zoho.
					case 'auth_code': // gmail/outlook.
					case 'channel': // smtpcom.
					case 'server_api_token': // postmark.
					case 'message_stream': // postmark.
						$options[ $mailer ][ $option_name ] = $this->is_const_defined( $mailer, $option_name ) ? '' : sanitize_text_field( $option_value );
						break;

					case 'has_pro_plan': // mailersend.
						$options[ $mailer ][ $option_name ] = $this->is_const_defined( $mailer, $option_name ) ? false : (bool) $option_value;
						break;

					case 'quick_connect': // sendlayer.
					case 'is_shared_domain': // sendlayer.
						$options[ $mailer ][ $option_name ] = (bool) $option_value;
						break;

					case 'sender_domain': // sendlayer.
						$options[ $mailer ][ $option_name ] = sanitize_text_field( $option_value );
						break;

					case 'access_token': // gmail/outlook/zoho, is an array.
					case 'user_details': // outlook/zoho, is an array.
						// These options don't support constants.
						$options[ $mailer ][ $option_name ] = $option_value;
						break;
				}
			}
		}

		return $options;
	}

	/**
	 * Merge recursively, including a proper substitution of values in sub-arrays when keys are the same.
	 * It's more like array_merge() and array_merge_recursive() combined.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function array_merge_recursive() {

		$arrays = func_get_args();

		if ( count( $arrays ) < 2 ) {
			return isset( $arrays[0] ) ? $arrays[0] : [];
		}

		$merged = [];

		while ( $arrays ) {
			$array = array_shift( $arrays );

			if ( ! is_array( $array ) ) {
				return [];
			}

			if ( empty( $array ) ) {
				continue;
			}

			foreach ( $array as $key => $value ) {
				if ( is_string( $key ) ) {
					if (
						is_array( $value ) &&
						array_key_exists( $key, $merged ) &&
						is_array( $merged[ $key ] )
					) {
						$merged[ $key ] = call_user_func( __METHOD__, $merged[ $key ], $value );
					} else {
						$merged[ $key ] = $value;
					}
				} else {
					$merged[] = $value;
				}
			}
		}

		return $merged;
	}

	/**
	 * Check whether the site is using Pepipost SMTP or not.
	 *
	 * @since      1.0.0
	 *
	 * @return bool
	 */
	public function is_pepipost_active() {

		_deprecated_function(
			__METHOD__,
			'2.4.0',
			'WPMailSMTP\Options::is_mailer_active()'
		);

		return apply_filters( 'wp_mail_smtp_options_is_pepipost_active', $this->is_mailer_active( 'pepipost' ) );
	}

	/**
	 * Check whether the site is using provided mailer or not.
	 *
	 * @since 2.3.0
	 *
	 * @param string $mailer The mailer slug.
	 *
	 * @return bool
	 */
	public function is_mailer_active( $mailer ) {

		$mailer = sanitize_key( $mailer );

		return apply_filters(
			"wp_mail_smtp_options_is_mailer_active_{$mailer}",
			$this->get( 'mail', 'mailer' ) === $mailer
		);
	}

	/**
	 * Check whether the site is using Pepipost/SMTP as a mailer or not.
	 *
	 * @since 1.1.0
	 *
	 * @return bool
	 */
	public function is_mailer_smtp() {

		return apply_filters( 'wp_mail_smtp_options_is_mailer_smtp', in_array( $this->get( 'mail', 'mailer' ), [ 'pepipost', 'smtp' ], true ) );
	}

	/**
	 * Get all the options, but without stripping the slashes.
	 *
	 * @since 2.5.0
	 *
	 * @return array
	 */
	public function get_all_raw() {

		$options = $this->options;

		foreach ( $options as $group => $g_value ) {
			foreach ( $g_value as $key => $value ) {
				$options[ $group ][ $key ] = $this->get( $group, $key, false );
			}
		}

		return $options;
	}

	/**
	 * Parse boolean value from string.
	 *
	 * @since 2.8.0
	 *
	 * @param string|boolean $value String or boolean value.
	 *
	 * @return boolean
	 */
	public function parse_boolean( $value ) {

		// Return early if it's boolean.
		if ( is_bool( $value ) ) {
			return $value;
		}

		$value = trim( $value );

		return $value === 'true';
	}

	/**
	 * Get a message of a constant that was set inside wp-config.php file.
	 *
	 * @since 2.8.0
	 *
	 * @param string $constant Constant name.
	 *
	 * @return string
	 */
	public function get_const_set_message( $constant ) {

		return sprintf( /* translators: %1$s - constant that was used; %2$s - file where it was used. */
			esc_html__( 'The value of this field was set using a constant %1$s most likely inside %2$s of your WordPress installation.', 'wp-mail-smtp' ),
			'<code>' . esc_html( $constant ) . '</code>',
			'<code>wp-config.php</code>'
		);
	}

	/**
	 * Whether option was changed.
	 * Can be used only before option save to DB.
	 *
	 * @since 3.0.0
	 *
	 * @param string $new_value Submitted value (e.g from $_POST).
	 * @param string $group     Group key.
	 * @param string $key       Option key.
	 *
	 * @return bool
	 */
	public function is_option_changed( $new_value, $group, $key ) {

		$old_value = $this->get( $group, $key );

		return $old_value !== $new_value;
	}

	/**
	 * Whether constant was changed.
	 * Can be used only for insecure options.
	 *
	 * @since 3.0.0
	 *
	 * @param string $group Group key.
	 * @param string $key   Option key.
	 *
	 * @return bool
	 */
	public function is_const_changed( $group, $key ) {

		if ( ! $this->is_const_defined( $group, $key ) ) {
			return false;
		}

		// Prevent double options update on multiple function call for same option.
		static $cache = [];

		$cache_key = $group . '_' . $key;

		if ( isset( $cache[ $cache_key ] ) ) {
			return $cache[ $cache_key ];
		}

		$value = $this->get( $group, $key );

		// Get old value from DB.
		add_filter( 'wp_mail_smtp_options_is_const_enabled', '__return_false', PHP_INT_MAX );
		$old_value = $this->get( $group, $key );
		remove_filter( 'wp_mail_smtp_options_is_const_enabled', '__return_false', PHP_INT_MAX );

		$changed = $value !== $old_value;

		// Save new constant value to DB.
		if ( $changed ) {
			$old_opt = $this->get_all_raw();

			$old_opt[ $group ][ $key ] = $value;
			$this->set( $old_opt );
		}

		$cache[ $cache_key ] = $changed;

		return $changed;
	}
}
