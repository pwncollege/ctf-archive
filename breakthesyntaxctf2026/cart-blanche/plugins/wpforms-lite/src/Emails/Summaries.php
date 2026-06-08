<?php
namespace WPForms\Emails;

use Exception;
use WPForms\Emails\Tasks\FetchInfoBlocksTask;

/**
 * Email Summaries main class.
 *
 * @since 1.5.4
 */
class Summaries {

	/**
	 * Constructor.
	 *
	 * @since 1.5.4
	 */
	public function __construct() {

		$this->hooks();

		$summaries_disabled = $this->is_disabled();

		if ( $summaries_disabled && wp_next_scheduled( 'wpforms_email_summaries_cron' ) ) {
			wp_clear_scheduled_hook( 'wpforms_email_summaries_cron' );
		}

		if ( ! $summaries_disabled && ! wp_next_scheduled( 'wpforms_email_summaries_cron' ) ) {
			// Since v1.9.1 we use a single event and manually reoccur it
			// because a recurring event cannot guarantee
			// its firing at the same time during WP_CLI execution.
			wp_schedule_single_event( $this->get_next_launch_time(), 'wpforms_email_summaries_cron' );
		}
	}

	/**
	 * Get the instance of a class and store it in itself.
	 *
	 * @since 1.5.4
	 */
	public static function get_instance() {

		static $instance;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Email Summaries hooks.
	 *
	 * @since 1.5.4
	 */
	public function hooks() {

		add_filter( 'wpforms_settings_defaults', [ $this, 'disable_summaries_setting' ] );
		add_action( 'wpforms_settings_updated', [ $this, 'deregister_fetch_info_blocks_task' ] );

		// Leave early if Email Summaries are disabled in settings.
		if ( $this->is_disabled() ) {
			return;
		}

		add_action( 'init', [ $this, 'preview' ] );
		add_action( 'wpforms_email_summaries_cron', [ $this, 'cron' ] );
		add_filter( 'wpforms_tasks_get_tasks', [ $this, 'register_fetch_info_blocks_task' ] );
	}

	/**
	 * Check if Email Summaries are disabled in settings.
	 *
	 * @since 1.5.4
	 *
	 * @return bool
	 */
	protected function is_disabled(): bool {

		/**
		 * Allows to modify whether Email Summaries are disabled in settings.
		 *
		 * @since 1.5.4
		 *
		 * @param bool $is_disabled True if Email Summaries are disabled in settings. False by default.
		 */
		return (bool) apply_filters( 'wpforms_emails_summaries_is_disabled', (bool) wpforms_setting( 'email-summaries-disable', false ) );
	}

	/**
	 * Add "Disable Email Summaries" to WPForms settings.
	 *
	 * @since 1.5.4
	 *
	 * @param array $settings WPForms settings.
	 *
	 * @return mixed
	 */
	public function disable_summaries_setting( $settings ) {

		/** This filter is documented in wpforms/src/Emails/Summaries.php */
		if ( (bool) apply_filters( 'wpforms_emails_summaries_is_disabled', false ) ) {
			return $settings;
		}

		$url = wp_nonce_url(
			add_query_arg(
				[
					'wpforms_email_template' => 'summary',
					'wpforms_email_preview'  => '1',
				],
				admin_url()
			),
			Preview::PREVIEW_NONCE_NAME
		);

		$desc = esc_html__( 'Disable Email Summaries weekly delivery.', 'wpforms-lite' );

		if ( ! $this->is_disabled() ) {
			$desc .= ' <a href="' . $url . '" target="_blank">' . esc_html__( 'View Email Summary Example', 'wpforms-lite' ) . '</a>.';
		}

		// Get the uninstall data setting.
		$uninstall_data = $settings['misc']['uninstall-data'];

		// Remove the uninstall data setting.
		unset( $settings['misc']['uninstall-data'] );

		// Add the email summaries setting.
		$settings['misc']['email-summaries-disable'] = [
			'id'     => 'email-summaries-disable',
			'name'   => esc_html__( 'Disable Email Summaries', 'wpforms-lite' ),
			'desc'   => $desc,
			'type'   => 'toggle',
			'status' => true,
		];

		// Add the uninstall data setting to the end.
		$settings['misc']['uninstall-data'] = $uninstall_data;

		return $settings;
	}

	/**
	 * Preview Email Summary.
	 *
	 * @since 1.5.4
	 */
	public function preview() {

		// Leave early if the current request is not a preview for the summaries email template.
		if ( ! $this->is_preview() ) {
			return;
		}

		// Get form entries.
		$entries = $this->get_entries();

		$args = [
			'body' => [
				'overview'           => $this->get_calculation_overview( $entries ),
				'entries'            => $this->format_trends_for_display( $entries ),
				'has_trends'         => $this->entries_has_trends( $entries ),
				'notification_block' => ( new NotificationBlocks() )->get_block(),
				'info_block'         => ( new InfoBlocks() )->get_next(),
				'icons'              => $this->get_icons_url(),
			],
		];

		$template = ( new Templates\Summary() )->set_args( $args );

		/**
		 * Filters the summaries email template.
		 *
		 * @since 1.5.4
		 *
		 * @param Templates\Summary $template Default summaries email template.
		 */
		$template = apply_filters( 'wpforms_emails_summaries_template', $template );

		$content = $template->get();

		if ( Helpers::is_plain_text_template() ) {
			$content = wpautop( $content );
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $content;

		exit;
	}

	/**
	 * Get next cron occurrence date.
	 *
	 * @since 1.5.4
	 * @deprecated 1.9.1
	 *
	 * @return int
	 */
	protected function get_first_cron_date_gmt(): int {

		_deprecated_function( __METHOD__, '1.9.1 of the WPForms plugin', __CLASS__ . '::get_next_launch_time()' );

		return $this->get_next_launch_time();
	}

	/**
	 * Get next Monday 2p.m with WordPress offset.
	 *
	 * @since 1.9.1
	 *
	 * @return int
	 */
	protected function get_next_launch_time(): int {

		$datetime      = date_create( 'now', wp_timezone() );
		$now_plus_week = time() + constant( 'WEEK_IN_SECONDS' );

		if ( ! $datetime ) {
			return $now_plus_week;
		}

		$hours = 14;

		// If today is Monday and the current time is less than 2 p.m.,
		// we can launch the cron for today.
		if (
			(int) $datetime->format( 'N' ) !== 1 ||
			(int) $datetime->format( 'H' ) >= $hours
		) {
			try {
				$datetime->modify( 'next monday' );
			} catch ( Exception $e ) {
				return $now_plus_week;
			}
		}

		$datetime->setTime( $hours, 0 );

		$timestamp = $datetime->getTimestamp();

		return $timestamp > 0 ? $timestamp : $now_plus_week;
	}

	/**
	 * Add custom Email Summaries cron schedule.
	 *
	 * @since      1.5.4
	 * @deprecated 1.9.1
	 *
	 * @param array $schedules WP cron schedules.
	 *
	 * @return array
	 */
	public function add_weekly_cron_schedule( $schedules ) {

		_deprecated_function( __METHOD__, '1.9.1 of the WPForms plugin' );

		$schedules['wpforms_email_summaries_weekly'] = [
			'interval' => $this->get_next_launch_time() - time(),
			'display'  => esc_html__( 'Weekly WPForms Email Summaries', 'wpforms-lite' ),
		];

		return $schedules;
	}

	/**
	 * Email Summaries cron callback.
	 *
	 * @since 1.5.4
	 */
	public function cron() {

		$entries = $this->get_entries();

		// Email won't be sent if there are no form entries.
		if ( empty( $entries ) ) {
			return;
		}

		$notification       = new NotificationBlocks();
		$notification_block = $notification->get_block();

		$info_blocks = new InfoBlocks();

		$next_block = $info_blocks->get_next();

		$args = [
			'body' => [
				'overview'           => $this->get_calculation_overview( $entries ),
				'entries'            => $this->format_trends_for_display( $entries ),
				'has_trends'         => $this->entries_has_trends( $entries ),
				'notification_block' => $notification_block,
				'info_block'         => $next_block,
				'icons'              => $this->get_icons_url(),
			],
		];

		$template = ( new Templates\Summary() )->set_args( $args );

		/** This filter is documented in preview() method above. */
		$template = apply_filters( 'wpforms_emails_summaries_template', $template );

		$content = $template->get();

		if ( ! $content ) {
			return;
		}

		$parsed_home_url = wp_parse_url( home_url() );
		$site_domain     = $parsed_home_url['host'];

		if ( is_multisite() && isset( $parsed_home_url['path'] ) ) {
			$site_domain .= $parsed_home_url['path'];
		}

		$subject = sprintf(
			/* translators: %s - site domain. */
			esc_html__( 'Your Weekly WPForms Summary for %s', 'wpforms-lite' ),
			$site_domain
		);

		/**
		 * Filters the summaries email subject.
		 *
		 * @since 1.5.4
		 *
		 * @param string $subject Default summaries email subject.
		 */
		$subject = apply_filters( 'wpforms_emails_summaries_cron_subject', $subject );

		/**
		 * Filters the summaries recipient email address.
		 *
		 * @since 1.5.4
		 *
		 * @param string $option Default summaries recipient email address.
		 */
		$to_email = apply_filters( 'wpforms_emails_summaries_cron_to_email', get_option( 'admin_email' ) );

		$sent = ( new Mailer() )
			->template( $template )
			->subject( $subject )
			->to_email( $to_email )
			->send();

		if ( $sent === true ) {
			$info_blocks->register_sent( $next_block );

			// Cache the notification block shown to avoid showing it again in the future.
			$notification->maybe_remember_shown_block( $notification_block );
		}
	}

	/**
	 * Get form entries.
	 *
	 * @since 1.5.4
	 *
	 * @return array
	 */
	protected function get_entries(): array {

		// The return value is intentionally left empty, as each email summary
		// depending on the plugin edition Lite/Pro will have different implementation.
		return [];
	}

	/**
	 * Get calculation overview.
	 *
	 * @since 1.8.8
	 *
	 * @param array $entries Form entries.
	 *
	 * @return array
	 */
	private function get_calculation_overview( $entries ): array {

		// Check if the entries array is empty.
		if ( empty( $entries ) ) {
			return [];
		}

		// Get the sum of 'count' index in all entries.
		$sum_current = array_sum( array_column( $entries, 'count' ) );

		// Choose a specific 'form_id' to check if 'count_previous_week' index exists.
		$sample_form_id = key( $entries );

		// Check if 'count_previous_week' index doesn't exist and return early.
		if ( ! isset( $entries[ $sample_form_id ]['count_previous_week'] ) ) {
			return [];
		}

		// Get the sum of 'count_previous_week' index in all entries.
		$sum_previous_week = array_sum( array_column( $entries, 'count_previous_week' ) );

		// Check if the sum of counts from the previous week is 0.
		// If so, return the sum of counts from the current week and trends as "+100%".
		if ( $sum_previous_week === 0 ) {
			return [
				'total'  => $sum_current,
				'trends' => $this->format_trends_for_display( $sum_current === 0 ? 0 : 100 ),
			];
		}

		// Calculate trends based on the sum of counts from the current week and the previous week.
		$trends = round( ( $sum_current - $sum_previous_week ) / $sum_previous_week * 100 );

		// Return an array with the total and trends.
		return [
			'total'  => $sum_current,
			'trends' => $this->format_trends_for_display( $trends ),
		];
	}

	/**
	 * Register Action Scheduler task to fetch and cache Info Blocks.
	 *
	 * @since 1.6.4
	 *
	 * @param \WPForms\Tasks\Task[] $tasks List of task classes.
	 *
	 * @return array
	 */
	public static function register_fetch_info_blocks_task( $tasks ): array {

		$tasks[] = FetchInfoBlocksTask::class;

		return $tasks;
	}

	/**
	 * Deregister Action Scheduler task to fetch and cache Info Blocks.
	 *
	 * @since 1.6.4
	 */
	public function deregister_fetch_info_blocks_task() {

		if ( ! $this->is_disabled() ) {
			return;
		}

		// Deregister the task.
		( new FetchInfoBlocksTask() )->cancel();

		// Delete last run time record.
		delete_option( FetchInfoBlocksTask::LAST_RUN );

		// Remove the cache file if it exists.
		$file_name = ( new InfoBlocks() )->get_cache_file_path();

		if ( file_exists( $file_name ) ) {
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.unlink_unlink
			@unlink( $file_name );
		}
	}

	/**
	 * Check if the current request is a preview for the summaries email template.
	 *
	 * @since 1.8.8
	 *
	 * @return bool
	 */
	private function is_preview(): bool {

		// Leave if the current user can't access.
		if ( ! wpforms_current_user_can() ) {
			return false;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		// Leave early if nonce verification failed.
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), Preview::PREVIEW_NONCE_NAME ) ) {
			return false;
		}

		// Leave early if preview is not requested.
		if ( ! isset( $_GET['wpforms_email_preview'], $_GET['wpforms_email_template'] ) ) {
			return false;
		}

		// Leave early if preview is not requested for the summaries template.
		if ( $_GET['wpforms_email_template'] !== 'summary' ) {
			return false;
		}

		// phpcs:enable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		return true;
	}

	/**
	 * Format entries trends for display.
	 *
	 * This function takes an array of entries and formats the 'trends' value for display.
	 *
	 * @since 1.8.8
	 *
	 * @param array|int $input Input data to format.
	 *
	 * @return array|string
	 */
	private function format_trends_for_display( $input ) {

		// If input is a numeric value, format and return it.
		if ( is_numeric( $input ) ) {
			return sprintf( '%s%s%%', $input >= 0 ? '+' : '', $input );
		}

		// Loop through entries and format 'trends' values.
		foreach ( $input as &$form ) {
			// Leave early if 'trends' index doesn't exist.
			if ( ! isset( $form['trends'] ) ) {
				continue;
			}

			// Add percent sign to trends and + sign if value greater than zero.
			$form['trends'] = sprintf( '%s%s%%', $form['trends'] >= 0 ? '+' : '', $form['trends'] );
		}

		return $input;
	}

	/**
	 * Check if trends can be displayed for the given entries.
	 *
	 * @since 1.8.8
	 *
	 * @param array $entries The entries data.
	 *
	 * @return bool
	 */
	private function entries_has_trends( array $entries ): bool {

		// Return false if entries array is empty.
		if ( empty( $entries ) ) {
			return false;
		}

		// Check if at least one array item has the 'trends' key.
		foreach ( $entries as $entry ) {
			if ( isset( $entry['trends'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get icons URL.
	 * Primarily used in the HTML version of the email template.
	 *
	 * @since 1.8.8
	 *
	 * @return array
	 */
	private function get_icons_url(): array {

		$base_url = WPFORMS_PLUGIN_URL . 'assets/images/email/';

		return [
			'overview'           => $base_url . 'icon-overview.png',
			'upward'             => $base_url . 'icon-upward.png',
			'downward'           => $base_url . 'icon-downward.png',
			'notification_block' => $base_url . 'notification-block-icon.png',
			'info_block'         => $base_url . 'info-block-icon.png',
		];
	}
}
