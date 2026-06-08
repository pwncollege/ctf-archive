<?php
/**
 * Error statistics tracking.
 *
 * Handles tracking of email sending errors for usage stats.
 *
 * @since 4.8.0
 */

namespace WPMailSMTP\UsageTracking;

use WPMailSMTP\Helpers\Helpers;
use WPMailSMTP\MailCatcherInterface;
use WPMailSMTP\Options;

/**
 * Class ErrorStats.
 *
 * Tracks email sending errors in memory during a request,
 * then flushes to the database on shutdown if any errors were recorded.
 *
 * @since 4.8.0
 */
class ErrorStats {

	/**
	 * Option name to store error stats.
	 *
	 * @since 4.8.0
	 *
	 * @var string
	 */
	const OPTION_NAME = 'wp_mail_smtp_email_sending_errors_stat';

	/**
	 * Max length for error code string.
	 *
	 * @since 4.8.0
	 *
	 * @var int
	 */
	const MAX_ERROR_CODE_LENGTH = 50;

	/**
	 * Error stats accumulated during the current request.
	 *
	 * @since 4.8.0
	 *
	 * @var array
	 */
	private $pending_stats = [];

	/**
	 * Whether the shutdown flush hook has been registered.
	 *
	 * @since 4.8.0
	 *
	 * @var bool
	 */
	private $shutdown_registered = false;

	/**
	 * Register hooks.
	 *
	 * @since 4.8.0
	 */
	public function hooks() {

		// Track email sending errors.
		add_action( 'wp_mail_smtp_mailcatcher_send_failed', [ $this, 'track_send_failed' ], 10, 5 );
		// Track email delivery failures (webhooks, delivery verification).
		add_action( 'wp_mail_smtp_email_delivery_failed', [ $this, 'track_delivery_failed' ], 10, 3 );
		// Add data to usage tracking.
		add_filter( 'wp_mail_smtp_usage_tracking_get_data', [ $this, 'add_usage_stats' ] );
	}

	/**
	 * Track email sending error from MailCatcher.
	 *
	 * @since 4.8.0
	 *
	 * @param string               $error_message Error message.
	 * @param MailCatcherInterface $mailcatcher   The MailCatcher object.
	 * @param string               $mailer_slug   Current mailer name.
	 * @param string               $error_code    Error code.
	 * @param int                  $response_code HTTP response code.
	 */
	public function track_send_failed( $error_message, $mailcatcher, $mailer_slug, $error_code = '', $response_code = 0 ) {

		$mailer_slug = $this->resolve_mailer_slug( $mailer_slug );
		$prefix      = $response_code > 0 ? (string) $response_code : '';
		$error_key   = $this->build_error_key( $prefix, (string) $error_code, (string) $error_message );

		$this->track_error( $mailer_slug, $error_key );
	}

	/**
	 * Track email delivery failure from webhooks or delivery verification.
	 *
	 * @since 4.8.0
	 *
	 * @param string $mailer_slug   Current mailer name.
	 * @param string $error_code    Error code.
	 * @param string $error_message Error message.
	 */
	public function track_delivery_failed( $mailer_slug, $error_code, $error_message = '' ) {

		$error_key = $this->build_error_key( 'delivery', (string) $error_code, (string) $error_message );

		$this->track_error( $mailer_slug, $error_key );
	}

	/**
	 * Track email sending error.
	 *
	 * Accumulates error in memory. All accumulated stats are flushed
	 * to the database on shutdown.
	 *
	 * @since 4.8.0
	 *
	 * @param string $mailer_slug Current mailer name.
	 * @param string $error_key   Normalized error key.
	 */
	private function track_error( $mailer_slug, $error_key ) { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		if ( ! isset( $this->pending_stats[ $mailer_slug ] ) ) {
			$this->pending_stats[ $mailer_slug ] = [];
		}

		if ( ! isset( $this->pending_stats[ $mailer_slug ][ $error_key ] ) ) {
			$this->pending_stats[ $mailer_slug ][ $error_key ] = 0;
		}

		$this->pending_stats[ $mailer_slug ][ $error_key ]++;

		// Register shutdown flush lazily on first tracked error.
		if ( ! $this->shutdown_registered ) {
			add_action( 'shutdown', [ $this, 'flush' ] );
			$this->shutdown_registered = true;
		}
	}

	/**
	 * Build error key in format: {prefix}:{error_code}:{sanitized_message}.
	 *
	 * Uses "-" for missing parts to keep the format parseable.
	 *
	 * @since 4.8.0
	 *
	 * @param string $prefix        First segment — HTTP response code or category (e.g. "401", "delivery").
	 * @param string $error_code    API-specific error code.
	 * @param string $error_message Error message text.
	 *
	 * @return string Normalized error key.
	 */
	private function build_error_key( $prefix, $error_code, $error_message ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		$part_response = ! empty( $prefix ) ? $prefix : '-';
		$part_code     = ! empty( $error_code ) && $error_code !== 'unknown' && (string) $error_code !== $part_response ? $error_code : '-';
		$part_message  = ! empty( $error_message ) ? $this->sanitize_message( $error_message ) : '-';

		// Remove response code and error code from message to save space.
		if ( $part_response !== '-' ) {
			$part_message = str_replace( sanitize_title( $part_response ), '', $part_message );
		}

		if ( $part_code !== '-' ) {
			$part_message = str_replace( sanitize_title( $part_code ), '', $part_message );
		}

		$part_message = preg_replace( '/-{2,}/', '-', $part_message );
		$part_message = trim( $part_message, '-' );

		if ( empty( $part_message ) ) {
			$part_message = '-';
		}

		$key = $part_response . ':' . $part_code . ':' . $part_message;

		if ( ! function_exists( 'mb_strlen' ) ) {
			Helpers::include_mbstring_polyfill();
		}

		if ( mb_strlen( $key ) > self::MAX_ERROR_CODE_LENGTH ) {
			$key = mb_substr( $key, 0, self::MAX_ERROR_CODE_LENGTH );

			// Cut at last hyphen to avoid partial words.
			$last_hyphen = strrpos( $key, '-' );

			if ( $last_hyphen !== false && $last_hyphen > strrpos( $key, ':' ) ) {
				$key = substr( $key, 0, $last_hyphen );
			}
		}

		return $key;
	}

	/**
	 * Sanitize error message into a short aggregatable slug.
	 *
	 * Strips dynamic content (emails, domains, URLs, UUIDs, quoted strings),
	 * takes first ~5 words, and slugifies.
	 *
	 * @since 4.8.0
	 *
	 * @param string $message Error message.
	 *
	 * @return string Sanitized message slug.
	 */
	private function sanitize_message( $message ) {

		// Strip HTML entities.
		$message = html_entity_decode( $message, ENT_QUOTES, 'UTF-8' );

		// Strip emails.
		$message = preg_replace( '/\S+@\S+\.\S+/', '', $message );

		// Strip URLs.
		$message = preg_replace( '#https?://\S+#i', '', $message );

		// Strip dot-separated strings (domains, namespaces, etc.).
		$message = preg_replace( '/\b\S+\.\S+\b/', '', $message );

		// Strip UUIDs.
		$message = preg_replace( '/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i', '', $message );

		// Strip quoted strings.
		$message = preg_replace( '/["\'][^"\']*["\']/', '', $message );

		// Collapse whitespace and trim.
		$message = trim( preg_replace( '/\s+/', ' ', $message ) );

		return sanitize_title( $message );
	}

	/**
	 * Resolve mailer slug, distinguishing one-click setups.
	 *
	 * @since 4.8.0
	 *
	 * @param string $mailer_slug Original mailer slug.
	 *
	 * @return string Resolved mailer slug.
	 */
	private function resolve_mailer_slug( $mailer_slug ) {

		if ( $mailer_slug === 'gmail' && Options::init()->get( 'gmail', 'one_click_setup_enabled' ) ) {
			return 'gmail_one_click';
		}

		if ( $mailer_slug === 'outlook' && Options::init()->get( 'outlook', 'one_click_setup_enabled' ) ) {
			return 'outlook_one_click';
		}

		return $mailer_slug;
	}

	/**
	 * Flush accumulated stats to the database.
	 *
	 * Reads current stored stats, merges with pending stats, and saves.
	 * Bails early if no errors were tracked during this request.
	 *
	 * @since 4.8.0
	 */
	public function flush() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( empty( $this->pending_stats ) ) {
			return;
		}

		$stored = get_option( self::OPTION_NAME, [] );

		if ( ! is_array( $stored ) ) {
			$stored = [];
		}

		foreach ( $this->pending_stats as $mailer_slug => $error_codes ) {
			if ( ! isset( $stored[ $mailer_slug ] ) ) {
				$stored[ $mailer_slug ] = [];
			}

			foreach ( $error_codes as $error_code => $count ) {
				// Apply overflow limit against the merged state.
				if (
					count( $stored[ $mailer_slug ] ) >= 50 &&
					! isset( $stored[ $mailer_slug ][ $error_code ] )
				) {
					$error_code = 'overflow';
				}

				if ( ! isset( $stored[ $mailer_slug ][ $error_code ] ) ) {
					$stored[ $mailer_slug ][ $error_code ] = 0;
				}

				$stored[ $mailer_slug ][ $error_code ] += $count;
			}
		}

		update_option( self::OPTION_NAME, $stored, false );

		$this->pending_stats = [];
	}

	/**
	 * Add usage stats data.
	 *
	 * @since 4.8.0
	 *
	 * @param array $data Usage data.
	 *
	 * @return array
	 */
	public function add_usage_stats( $data ) {

		// Flush any pending stats before reading.
		$this->flush();

		$stats = get_option( self::OPTION_NAME, [] );

		$data['email_sending_errors_stat'] = is_array( $stats ) ? $stats : [];

		// Reset after collecting.
		delete_option( self::OPTION_NAME );

		return $data;
	}
}
