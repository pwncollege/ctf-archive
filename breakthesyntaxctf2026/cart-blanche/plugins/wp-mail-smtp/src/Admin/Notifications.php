<?php

namespace WPMailSMTP\Admin;

use WPMailSMTP\Helpers\Helpers;
use WPMailSMTP\Options;
use WPMailSMTP\WP;

/**
 * Notifications.
 *
 * @since 2.3.0
 */
class Notifications {

	/**
	 * Source of notifications content.
	 *
	 * @since 2.3.0
	 *
	 * @var string
	 */
	const SOURCE_URL = 'https://wpmailsmtpapi.com/feeds/v1/notifications';

	/**
	 * The WP option key for storing the notification options.
	 *
	 * @since 2.3.0
	 *
	 * @var string
	 */
	const OPTION_KEY = 'wp_mail_smtp_notifications';

	/**
	 * Option value.
	 *
	 * @since 2.3.0
	 *
	 * @var bool|array
	 */
	public $option = false;

	/**
	 * Initialize class.
	 *
	 * @since 2.3.0
	 */
	public function init() {

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.3.0
	 */
	public function hooks() {

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'wp_mail_smtp_admin_pages_before_content', [ $this, 'output' ] );
		add_action( 'wp_ajax_wp_mail_smtp_notification_dismiss', [ $this, 'dismiss' ] );
	}

	/**
	 * Check if notifications are enabled.
	 *
	 * @since 4.3.0
	 *
	 * @return bool
	 */
	public function is_enabled() {

		return ! Options::init()->get( 'general', 'am_notifications_hidden' );
	}

	/**
	 * Check if user has access and is enabled.
	 *
	 * @since 2.3.0
	 *
	 * @return bool
	 */
	public function has_access() {

		$access = false;

		if (
			current_user_can( wp_mail_smtp()->get_capability_manage_options() ) &&
			$this->is_enabled()
		) {
			$access = true;
		}

		return apply_filters( 'wp_mail_smtp_admin_notifications_has_access', $access );
	}

	/**
	 * Get option value.
	 *
	 * @since 2.3.0
	 *
	 * @param bool $cache Reference property cache if available.
	 *
	 * @return array
	 */
	public function get_option( $cache = true ) {

		if ( $this->option && $cache ) {
			return $this->option;
		}

		$option = get_option( self::OPTION_KEY, [] );

		$this->option = [
			'update'    => ! empty( $option['update'] ) ? $option['update'] : 0,
			'events'    => ! empty( $option['events'] ) ? $option['events'] : [],
			'feed'      => ! empty( $option['feed'] ) ? $option['feed'] : [],
			'dismissed' => ! empty( $option['dismissed'] ) ? $option['dismissed'] : [],
		];

		return $this->option;
	}

	/**
	 * Fetch notifications from feed.
	 *
	 * @since 2.3.0
	 *
	 * @return array
	 */
	protected function fetch_feed() {

		$feed_url = self::SOURCE_URL . '/' . wp_mail_smtp()->get_license_type();

		$response = wp_remote_get(
			$feed_url,
			[
				'user-agent' => Helpers::get_default_user_agent(),
			]
		);

		if ( is_wp_error( $response ) ) {
			return [];
		}

		$body = wp_remote_retrieve_body( $response );

		if ( empty( $body ) ) {
			return [];
		}

		return $this->verify( json_decode( $body, true ) );
	}

	/**
	 * Verify notification data before it is saved.
	 *
	 * @since 2.3.0
	 *
	 * @param array $notifications Array of notification items to verify.
	 *
	 * @return array
	 */
	protected function verify( $notifications ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		$data = [];

		if ( ! is_array( $notifications ) || empty( $notifications ) ) {
			return $data;
		}

		$option = $this->get_option();

		foreach ( $notifications as $notification ) {

			// The message and license should never be empty, if they are, ignore.
			if ( empty( $notification['content'] ) || empty( $notification['type'] ) ) {
				continue;
			}

			// Ignore if license type does not match.
			if ( ! in_array( wp_mail_smtp()->get_license_type(), $notification['type'], true ) ) {
				continue;
			}

			// Ignore if expired.
			if ( ! empty( $notification['end'] ) && time() > strtotime( $notification['end'] ) ) {
				continue;
			}

			// Ignore if notification has already been dismissed.
			if ( ! empty( $option['dismissed'] ) && in_array( $notification['id'], $option['dismissed'] ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				continue;
			}

			// Ignore if notification existed before installing WPForms.
			// Prevents bombarding the user with notifications after activation.
			$activated = get_option( 'wp_mail_smtp_activated_time' );

			if (
				! empty( $activated ) &&
				! empty( $notification['start'] ) &&
				$activated > strtotime( $notification['start'] )
			) {
				continue;
			}

			$data[] = $notification;
		}

		return $data;
	}

	/**
	 * Verify saved notification data for active notifications.
	 *
	 * @since 2.3.0
	 *
	 * @param array $notifications Array of notification items to verify.
	 *
	 * @return array
	 */
	protected function verify_active( $notifications ) {

		if ( ! is_array( $notifications ) || empty( $notifications ) ) {
			return [];
		}

		// Remove notifications that are not active.
		foreach ( $notifications as $key => $notification ) {
			if (
				( ! empty( $notification['start'] ) && time() < strtotime( $notification['start'] ) ) ||
				( ! empty( $notification['end'] ) && time() > strtotime( $notification['end'] ) )
			) {
				unset( $notifications[ $key ] );
			}
		}

		return $notifications;
	}

	/**
	 * Get notification data.
	 *
	 * @since 2.3.0
	 * @since 3.9.0 Make the AS a recurring task.
	 *
	 * @return array
	 */
	public function get() {

		if ( ! $this->has_access() ) {
			return [];
		}

		$option = $this->get_option();
		$events = ! empty( $option['events'] ) ? $this->verify_active( $option['events'] ) : [];
		$feed   = ! empty( $option['feed'] ) ? $this->verify_active( $option['feed'] ) : [];

		return array_merge( $events, $feed );
	}

	/**
	 * Get the update notifications interval.
	 *
	 * @since 3.9.0
	 *
	 * @return int
	 */
	public function get_notification_update_task_interval() {

		/**
		 * Filters the interval for the notifications update task.
		 *
		 * @since 3.9.0
		 *
		 * @param int $interval The interval in seconds. Default to a day (in seconds).
		 */
		return (int) apply_filters( 'wp_mail_smtp_admin_notifications_get_notification_update_task_interval', DAY_IN_SECONDS );
	}

	/**
	 * Get notification count.
	 *
	 * @since 2.3.0
	 *
	 * @return int
	 */
	public function get_count() {

		return count( $this->get() );
	}

	/**
	 * Add a manual notification event.
	 *
	 * @since 2.3.0
	 *
	 * @param array $notification Notification data.
	 */
	public function add( $notification ) {

		if ( empty( $notification['id'] ) ) {
			return;
		}

		$option = $this->get_option();

		if ( in_array( $notification['id'], $option['dismissed'] ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			return;
		}

		foreach ( $option['events'] as $item ) {
			if ( $item['id'] === $notification['id'] ) {
				return;
			}
		}

		$notification = $this->verify( [ $notification ] );

		update_option(
			self::OPTION_KEY,
			[
				'update'    => $option['update'],
				'feed'      => $option['feed'],
				'events'    => array_merge( $notification, $option['events'] ),
				'dismissed' => $option['dismissed'],
			]
		);
	}

	/**
	 * Update notification data from feed.
	 *
	 * @since 2.3.0
	 */
	public function update() {

		$option = $this->get_option();

		// Bail if feed was updated less than an interval ago.
		if ( time() - (int) $option['update'] < $this->get_notification_update_task_interval() ) {
			return;
		}

		$feed = $this->fetch_feed();

		update_option(
			self::OPTION_KEY,
			[
				'update'    => time(),
				'feed'      => $feed,
				'events'    => $option['events'],
				'dismissed' => $option['dismissed'],
			]
		);
	}

	/**
	 * Admin area assets.
	 *
	 * @since 2.3.0
	 *
	 * @param string $hook Hook suffix for the current admin page.
	 */
	public function enqueue_assets( $hook ) {

		if ( strpos( $hook, Area::SLUG ) === false ) {
			return;
		}

		if ( ! $this->has_access() ) {
			return;
		}

		$notifications = $this->get();

		if ( empty( $notifications ) ) {
			return;
		}

		wp_enqueue_style(
			'wp-mail-smtp-admin-notifications',
			wp_mail_smtp()->assets_url . '/css/admin-notifications.min.css',
			[],
			WPMS_PLUGIN_VER
		);

		wp_enqueue_script(
			'wp-mail-smtp-admin-notifications',
			wp_mail_smtp()->assets_url . '/js/smtp-notifications' . WP::asset_min() . '.js',
			[ 'jquery' ],
			WPMS_PLUGIN_VER,
			true
		);
	}

	/**
	 * Output notifications.
	 *
	 * @since 2.3.0
	 */
	public function output() { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded

		$notifications = $this->get();

		if ( empty( $notifications ) ) {
			return;
		}

		$notifications_html   = '';
		$current_class        = ' current';
		$content_allowed_tags = [
			'em'     => [],
			'i'      => [],
			'strong' => [],
			'span'   => [
				'style' => [],
			],
			'a'      => [
				'href'   => [],
				'target' => [],
				'rel'    => [],
			],
			'br'     => [],
			'p'      => [
				'id'    => [],
				'class' => [],
			],
		];

		foreach ( $notifications as $notification ) {

			// Buttons HTML.
			$buttons_html = '';
			if ( ! empty( $notification['btns'] ) && is_array( $notification['btns'] ) ) {
				foreach ( $notification['btns'] as $btn_type => $btn ) {
					if ( empty( $btn['text'] ) ) {
						continue;
					}
					$buttons_html .= sprintf(
						'<a href="%1$s" class="button button-%2$s"%3$s>%4$s</a>',
						! empty( $btn['url'] ) ? esc_url( $btn['url'] ) : '',
						$btn_type === 'main' ? 'primary' : 'secondary',
						! empty( $btn['target'] ) && $btn['target'] === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '',
						sanitize_text_field( $btn['text'] )
					);
				}
				$buttons_html = ! empty( $buttons_html ) ? '<div class="wp-mail-smtp-notifications-buttons">' . $buttons_html . '</div>' : '';
			}

			// Notification HTML.
			$notifications_html .= sprintf(
				'<div class="wp-mail-smtp-notifications-message%5$s" data-message-id="%4$s">
					<h3 class="wp-mail-smtp-notifications-title">%1$s</h3>
					<div class="wp-mail-smtp-notifications-content">%2$s</div>
					%3$s
				</div>',
				! empty( $notification['title'] ) ? sanitize_text_field( $notification['title'] ) : '',
				! empty( $notification['content'] ) ? wp_kses( wpautop( $notification['content'] ), $content_allowed_tags ) : '',
				$buttons_html,
				! empty( $notification['id'] ) ? esc_attr( sanitize_text_field( $notification['id'] ) ) : 0,
				$current_class
			);

			// Only first notification is current.
			$current_class = '';
		}
		?>

		<div id="wp-mail-smtp-notifications">

			<div class="wp-mail-smtp-notifications-header">
				<div class="wp-mail-smtp-notifications-bell">
					<svg width="16" height="20" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path fill-rule="evenodd" clip-rule="evenodd" d="M12.8173 8.92686C12.4476 9.01261 12.0624 9.05794 11.6666 9.05794C8.86542 9.05794 6.59455 6.78729 6.59419 3.98616C4.22974 4.54931 2.51043 6.64293 2.51043 9.23824C2.51043 12.4695 1.48985 13.6147 0.849845 14.3328C0.796894 14.3922 0.746548 14.4487 0.699601 14.5032C0.505584 14.707 0.408575 14.9787 0.440912 15.2165C0.440912 15.7939 0.828946 16.3034 1.47567 16.3034H13.8604C14.5071 16.3034 14.8952 15.7939 14.9275 15.2165C14.9275 14.9787 14.8305 14.707 14.6365 14.5032C14.5895 14.4487 14.5392 14.3922 14.4862 14.3328C13.8462 13.6147 12.8257 12.4695 12.8257 9.23824C12.8257 9.13361 12.8229 9.02979 12.8173 8.92686ZM9.72139 17.3904C9.72139 18.6132 8.81598 19.5643 7.68421 19.5643C6.52011 19.5643 5.6147 18.6132 5.6147 17.3904H9.72139Z" fill="#777777"/>
						<path d="M11.6666 7.60868C13.6677 7.60868 15.2898 5.98653 15.2898 3.9855C15.2898 1.98447 13.6677 0.36232 11.6666 0.36232C9.66561 0.36232 8.04346 1.98447 8.04346 3.9855C8.04346 5.98653 9.66561 7.60868 11.6666 7.60868Z" fill="#d63638"/>
					</svg>
				</div>
				<div class="wp-mail-smtp-notifications-title"><?php esc_html_e( 'Notifications', 'wp-mail-smtp' ); ?></div>
			</div>

			<div class="wp-mail-smtp-notifications-body">
				<a class="dismiss" title="<?php echo esc_attr__( 'Dismiss this message', 'wp-mail-smtp' ); ?>"><i class="dashicons dashicons-dismiss" aria-hidden="true"></i></a>

				<?php if ( count( $notifications ) > 1 ) : ?>
					<div class="navigation">
						<a class="prev">
							<span class="screen-reader-text"><?php esc_attr_e( 'Previous message', 'wp-mail-smtp' ); ?></span>
							<span aria-hidden="true">‹</span>
						</a>
						<a class="next">
							<span class="screen-reader-text"><?php esc_attr_e( 'Next message', 'wp-mail-smtp' ); ?>"></span>
							<span aria-hidden="true">›</span>
						</a>
					</div>
				<?php endif; ?>

				<div class="wp-mail-smtp-notifications-messages">
					<?php echo $notifications_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Dismiss notification via AJAX.
	 *
	 * @since 2.3.0
	 */
	public function dismiss() {

		// Run a security check.
		check_ajax_referer( 'wp-mail-smtp-admin', 'nonce' );

		// Check for access and required param.
		if ( ! current_user_can( wp_mail_smtp()->get_capability_manage_options() ) || empty( $_POST['id'] ) ) {
			wp_send_json_error();
		}

		$id     = sanitize_text_field( wp_unslash( $_POST['id'] ) );
		$option = $this->get_option();
		$type   = is_numeric( $id ) ? 'feed' : 'events';

		$option['dismissed'][] = $id;
		$option['dismissed']   = array_unique( $option['dismissed'] );

		// Remove notification.
		if ( is_array( $option[ $type ] ) && ! empty( $option[ $type ] ) ) {
			foreach ( $option[ $type ] as $key => $notification ) {
				if ( $notification['id'] == $id ) { // phpcs:ignore WordPress.PHP.StrictComparisons
					unset( $option[ $type ][ $key ] );
					break;
				}
			}
		}

		update_option( self::OPTION_KEY, $option );

		wp_send_json_success();
	}
}
