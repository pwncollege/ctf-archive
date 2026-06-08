<?php
/**
 * The error handler to suppress error messages from vendor directories.
 */

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedClassInspection */

namespace WPForms;

use QM_Collectors;

/**
 * Class ErrorHandler.
 *
 * @since 1.8.5
 */
class ErrorHandler {

	/**
	 * Directories from where errors should be suppressed.
	 *
	 * @since 1.8.5
	 *
	 * @var string[]
	 */
	protected $dirs;

	/**
	 * Previous error handler.
	 *
	 * @since 1.8.6
	 *
	 * @var callable|null
	 */
	private $previous_error_handler;

	/**
	 * Error levels to suppress.
	 *
	 * @since 1.8.6
	 *
	 * @var int
	 */
	protected $levels;

	/**
	 * Whether the error handler is handling an error.
	 *
	 * @since 1.9.2
	 *
	 * @var bool
	 */
	private $handling = false;

	/**
	 * Class constructor.
	 *
	 * @since 1.9.3
	 *
	 * @param array $dirs   Directories from where errors should be suppressed.
	 * @param int   $levels Error levels to suppress.
	 */
	public function __construct( array $dirs = [], int $levels = 0 ) {

		$this->dirs   = $dirs;
		$this->levels = $levels;
	}

	/**
	 * Init class.
	 *
	 * @since 1.8.5
	 *
	 * @return void
	 * @noinspection PhpUndefinedConstantInspection
	 */
	public function init() {

		if ( defined( 'WPFORMS_DISABLE_ERROR_HANDLER' ) && WPFORMS_DISABLE_ERROR_HANDLER ) {
			return;
		}

		$this->dirs = [
			// WPForms.
			WPFORMS_PLUGIN_DIR . 'vendor/',
			WPFORMS_PLUGIN_DIR . 'vendor_prefixed/',
			// Addons.
			WP_PLUGIN_DIR . '/wpforms-activecampaign/vendor/',
			WP_PLUGIN_DIR . '/wpforms-authorize-net/vendor/',
			WP_PLUGIN_DIR . '/wpforms-aweber/deprecated/',
			WP_PLUGIN_DIR . '/wpforms-aweber/vendor/',
			WP_PLUGIN_DIR . '/wpforms-calculations/vendor/',
			WP_PLUGIN_DIR . '/wpforms-campaign-monitor/vendor/',
			WP_PLUGIN_DIR . '/wpforms-captcha/vendor/',
			WP_PLUGIN_DIR . '/wpforms-conversational-forms/vendor/',
			WP_PLUGIN_DIR . '/wpforms-convertkit/vendor/',
			WP_PLUGIN_DIR . '/wpforms-convertkit/vendor_prefixed/',
			WP_PLUGIN_DIR . '/wpforms-coupons/vendor/',
			WP_PLUGIN_DIR . '/wpforms-drip/vendor/',
			WP_PLUGIN_DIR . '/wpforms-e2e-helpers/vendor/',
			WP_PLUGIN_DIR . '/wpforms-entry-automation/vendor/',
			WP_PLUGIN_DIR . '/wpforms-form-abandonment/vendor/',
			WP_PLUGIN_DIR . '/wpforms-form-locker/vendor/',
			WP_PLUGIN_DIR . '/wpforms-form-pages/vendor/',
			WP_PLUGIN_DIR . '/wpforms-geolocation/vendor/',
			WP_PLUGIN_DIR . '/wpforms-getresponse/vendor/',
			WP_PLUGIN_DIR . '/wpforms-google-sheets/vendor/',
			WP_PLUGIN_DIR . '/wpforms-hubspot/vendor/',
			WP_PLUGIN_DIR . '/wpforms-lead-forms/vendor/',
			WP_PLUGIN_DIR . '/wpforms-mailchimp/vendor/',
			WP_PLUGIN_DIR . '/wpforms-mailerlite/vendor/',
			WP_PLUGIN_DIR . '/wpforms-offline-forms/vendor/',
			WP_PLUGIN_DIR . '/wpforms-paypal-commerce/vendor/',
			WP_PLUGIN_DIR . '/wpforms-paypal-standard/vendor/',
			WP_PLUGIN_DIR . '/wpforms-post-submissions/vendor/',
			WP_PLUGIN_DIR . '/wpforms-salesforce/vendor/',
			WP_PLUGIN_DIR . '/wpforms-salesforce/vendor_prefixed/',
			WP_PLUGIN_DIR . '/wpforms-save-resume/vendor/',
			WP_PLUGIN_DIR . '/wpforms-sendinblue/vendor/',
			WP_PLUGIN_DIR . '/wpforms-signatures/vendor/',
			WP_PLUGIN_DIR . '/wpforms-square/vendor/', // Backward compatibility.
			WP_PLUGIN_DIR . '/wpforms-stripe/vendor/',
			WP_PLUGIN_DIR . '/wpforms-surveys-polls/vendor/',
			WP_PLUGIN_DIR . '/wpforms-user-journey/vendor/',
			WP_PLUGIN_DIR . '/wpforms-user-registration/vendor/',
			WP_PLUGIN_DIR . '/wpforms-webhooks/vendor/',
			WP_PLUGIN_DIR . '/wpforms-zapier/vendor/',
		];

		/**
		 * Allow modifying the list of dirs to suppress messages from.
		 *
		 * @since 1.8.6
		 *
		 * @param array $dirs The list of dirs to suppress messages from.
		 */
		$this->dirs = (array) apply_filters( 'wpforms_error_handler_dirs', $this->dirs );

		$this->normalize_dirs();

		/**
		 * Allow modifying the levels of messages to suppress.
		 *
		 * @since 1.8.6
		 *
		 * @param int $levels Error levels of messages to suppress.
		 */
		$this->levels = (int) apply_filters(
			'wpforms_error_handler_levels',
			E_WARNING | E_NOTICE | E_USER_WARNING | E_USER_NOTICE | E_DEPRECATED | E_USER_DEPRECATED
		);

		$this->hooks();
	}

	/**
	 * Add hooks.
	 *
	 * @since 1.9.1
	 *
	 * @return void
	 */
	protected function hooks() {

		if ( $this->dirs && $this->levels ) {
			// Set error handler.
			$this->set_error_handler();

			// Some plugins destroy an error handler chain. Set the error handler again upon loading them.
			add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ], 1000 );
		}

		// Suppress the _load_textdomain_just_in_time() notices related the WPForms for WP 6.7+.
		if ( version_compare( $GLOBALS['wp_version'], '6.7', '>=' ) ) {
			add_action( 'doing_it_wrong_run', [ $this,'action_doing_it_wrong_run' ], 0, 3 );
			add_action( 'doing_it_wrong_run', [ $this,'action_doing_it_wrong_run' ], 20, 3 );
			add_filter( 'doing_it_wrong_trigger_error', [ $this, 'filter_doing_it_wrong_trigger_error' ], 10, 4 );
		}
	}

	/**
	 * Set error handler and save original.
	 *
	 * @since 1.9.1
	 */
	public function set_error_handler() {

		// To chain error handlers, we must not specify the second argument and catch all errors in our handler.
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler
		$this->previous_error_handler = set_error_handler( [ $this, 'error_handler' ] );
	}

	/**
	 * The 'plugins_loaded' hook.
	 *
	 * @since 0.32
	 *
	 * @return void
	 */
	public function plugins_loaded() {

		// Constants of plugins that destroy an error handler chain.
		$constants = [
			'QM_VERSION', // Query Monitor.
			'AUTOMATOR_PLUGIN_VERSION', // Uncanny Automator.
		];

		$found = false;

		foreach ( $constants as $constant ) {
			if ( defined( $constant ) ) {
				$found = true;

				break;
			}
		}

		if ( ! $found ) {
			return;
		}

		// Set this error handler after loading a plugin to chain its error handler.
		( new self( $this->dirs, $this->levels ) )->set_error_handler();
	}

	/**
	 * Error handler.
	 *
	 * @since 1.8.5
	 *
	 * @param int    $level   Error level.
	 * @param string $message Error message.
	 * @param string $file    File produced an error.
	 * @param int    $line    Line number.
	 *
	 * @return bool
	 * @noinspection PhpTernaryExpressionCanBeReplacedWithConditionInspection
	 */
	public function error_handler( int $level, string $message, string $file, int $line ): bool { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		if ( $this->handling ) {
			$this->handling = false;

			// Prevent infinite recursion and fallback to standard error handler.
			return false;
		}

		$this->handling = true;

		if ( ( $level & $this->levels ) === 0 ) {
			// Not served error level, use fallback error handler.
			// phpcs:ignore PHPCompatibility.FunctionUse.ArgumentFunctionsReportCurrentValue.NeedsInspection
			return $this->fallback_error_handler( func_get_args() );
		}

		// Process error.
		$normalized_file = str_replace( DIRECTORY_SEPARATOR, '/', $file );

		foreach ( $this->dirs as $dir ) {
			if ( strpos( $normalized_file, $dir ) !== false ) {
				$this->handling = false;

				// Suppress deprecated errors from this directory.
				return true;
			}
		}

		// Not served directory, use fallback error handler.
		// phpcs:ignore PHPCompatibility.FunctionUse.ArgumentFunctionsReportCurrentValue.NeedsInspection
		return $this->fallback_error_handler( func_get_args() );
	}

	/**
	 * Action for _doing_it_wrong() calls.
	 *
	 * @since 1.9.2.2
	 *
	 * @param string $function_name The function that was called.
	 * @param string $message       A message explaining what has been done incorrectly.
	 * @param string $version       The version of WordPress where the message was added.
	 *
	 * @return void
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function action_doing_it_wrong_run( $function_name, $message, $version ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed, WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		global $wp_filter;

		$function_name = (string) $function_name;
		$message       = (string) $message;

		if ( ! class_exists( 'QM_Collectors' ) || ! $this->is_just_in_time_for_wpforms_domain( $function_name, $message ) ) {
			return;
		}

		$qm_collector_doing_it_wrong = QM_Collectors::get( 'doing_it_wrong' );
		$current_priority            = $wp_filter['doing_it_wrong_run']->current_priority();

		if ( $qm_collector_doing_it_wrong === null || $current_priority === false ) {
			return;
		}

		switch ( $current_priority ) {
			case 0:
				remove_action( 'doing_it_wrong_run', [ $qm_collector_doing_it_wrong, 'action_doing_it_wrong_run' ] );
				break;

			case 20:
				add_action( 'doing_it_wrong_run', [ $qm_collector_doing_it_wrong, 'action_doing_it_wrong_run' ], 10, 3 );
				break;

			default:
				break;
		}
	}

	/**
	 * Filter for _doing_it_wrong() calls.
	 *
	 * @since 1.9.2.2
	 *
	 * @param bool|mixed $trigger       Whether to trigger the error for _doing_it_wrong() calls. Default true.
	 * @param string     $function_name The function that was called.
	 * @param string     $message       A message explaining what has been done incorrectly.
	 * @param string     $version       The version of WordPress where the message was added.
	 *
	 * @return bool
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function filter_doing_it_wrong_trigger_error( $trigger, $function_name, $message, $version ): bool { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		$trigger       = (bool) $trigger;
		$function_name = (string) $function_name;
		$message       = (string) $message;

		return $this->is_just_in_time_for_wpforms_domain( $function_name, $message ) ? false : $trigger;
	}

	/**
	 * Filter for gettext.
	 *
	 * @since      1.9.2.2
	 * @deprecated 1.9.3
	 *
	 * @param string|mixed $translation Translated text.
	 * @param string|mixed $text        Text to translate.
	 * @param string|mixed $domain      Text domain. Unique identifier for retrieving translated strings.
	 *
	 * @return string|mixed
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function filter_gettext( $translation, $text, $domain ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		_deprecated_function( __METHOD__, '1.9.3 of the WPForms plugin' );

		return $translation;
	}

	/**
	 * Fallback error handler.
	 *
	 * @since 1.9.2
	 *
	 * @param array $args Arguments.
	 *
	 * @return bool
	 * @noinspection PhpTernaryExpressionCanBeReplacedWithConditionInspection
	 */
	private function fallback_error_handler( array $args ): bool {

		$result = $this->previous_error_handler === null ?
			// Use standard error handler.
			false :
			(bool) call_user_func_array( $this->previous_error_handler, $args );

		$this->handling = false;

		return $result;
	}

	/**
	 * Normalize dirs.
	 *
	 * @since 1.9.2
	 *
	 * @return void
	 */
	private function normalize_dirs() {

		$this->dirs = array_filter(
			array_map(
				static function ( $dir ) {

					return str_replace( DIRECTORY_SEPARATOR, '/', trim( $dir ) );
				},
				$this->dirs
			)
		);
	}

	/**
	 * Whether it is the just_in_time_error for WPForms-related domains.
	 *
	 * @since 1.9.2.2
	 *
	 * @param string $function_name Function name.
	 * @param string $message       Message.
	 *
	 * @return bool
	 */
	protected function is_just_in_time_for_wpforms_domain( string $function_name, string $message ): bool {

		return $function_name === '_load_textdomain_just_in_time' && strpos( $message, '<code>wpforms' ) !== false;
	}
}
