<?php
namespace WPForms\Emails;

use WPForms\Admin\Notifications\Notifications;

/**
 * Notification class.
 * This class is responsible for displaying the notification block in the email summaries.
 *
 * @since 1.8.8
 */
class NotificationBlocks {

	/**
	 * Notifications class instance.
	 *
	 * @since 1.8.8
	 *
	 * @var Notifications
	 */
	private $notifications;

	/**
	 * Class constructor.
	 * Initializes the Notifications class instance.
	 *
	 * @since 1.8.8
	 */
	public function __construct() {

		// Store the instance of the "Notifications" class.
		$this->notifications = wpforms()->obj( 'notifications' );
	}

	/**
	 * Retrieves the notification block from the feed, considering shown notifications and license type.
	 *
	 * @since 1.8.8
	 *
	 * @return array
	 */
	public function get_block(): array {

		// Check if the user has access to notifications.
		// If the user has disabled announcements, return an empty array.
		if ( ! $this->notifications || ! $this->notifications->has_access() ) {
			return [];
		}

		// Get the response array from the notifications.
		$notifications = $this->notifications->get_option();

		// Check if 'feed' key is present and non-empty.
		if ( empty( $notifications['feed'] ) || ! is_array( $notifications['feed'] ) ) {
			return [];
		}

		// Remove items from $feed where their id index is in `shown_notifications` option value.
		$feed = $this->filter_feed( $notifications['feed'] );

		// Sort the array of items using usort and the custom comparison function.
		$feed = $this->sort_feed( $feed );

		// Get the very first item from the $feed.
		$block = reset( $feed );

		// Check if $block is empty.
		if ( empty( $block ) ) {
			return [];
		}

		// Return the notification block.
		return $this->prepare_and_sanitize_content( $block );
	}

	/**
	 * Save the shown notification block if it's not empty.
	 *
	 * @since 1.8.8
	 *
	 * @param array $notification The notification to be saved.
	 */
	public function maybe_remember_shown_block( array $notification ) {

		// Check if the notification or its ID is empty.
		if ( empty( $notification ) || empty( $notification['id'] ) ) {
			// If the notification or its ID is empty, return early.
			return;
		}

		// Get shown notifications from options.
		$shown_notifications = (array) get_option( 'wpforms_email_summaries_shown_notifications', [] );

		// Add the notification id to the $shown_notifications array.
		$shown_notifications[] = (int) $notification['id'];

		// Update the shown notifications in the options.
		// Avoid autoloading the option, as it's not needed.
		update_option( 'wpforms_email_summaries_shown_notifications', $shown_notifications, false );
	}

	/**
	 * Filter the feed to remove shown notifications.
	 *
	 * @since 1.8.8
	 *
	 * @param array $feed The feed to filter.
	 *
	 * @return array
	 */
	private function filter_feed( array $feed ): array {

		$shown_notifications = (array) get_option( 'wpforms_email_summaries_shown_notifications', [] );

		return array_filter(
			$feed,
			static function ( $item ) use ( $shown_notifications ) {

				return ! in_array( $item['id'], $shown_notifications, true );
			}
		);
	}

	/**
	 * Sort the feed in descending order by start date.
	 *
	 * @since 1.8.8
	 *
	 * @param array $feed The feed to sort.
	 *
	 * @return array
	 */
	private function sort_feed( array $feed ): array {

		usort(
			$feed,
			static function ( $a, $b ) {

				return strtotime( $b['start'] ) - strtotime( $a['start'] );
			}
		);

		return $feed;
	}

	/**
	 * Prepare and sanitize content for display.
	 *
	 * @since 1.8.8
	 *
	 * @param string|array $content The content to be prepared and sanitized.
	 *
	 * @return string|array
	 */
	private function prepare_and_sanitize_content( $content ) {

		// If the content is empty, return as is.
		if ( empty( $content ) ) {
			return $content;
		}

		// If the content is already a string, sanitize and return it.
		if ( is_string( $content ) ) {
			// Define allowed HTML tags and attributes.
			$content_allowed_tags = $this->notifications->get_allowed_tags();

			// For design consistency, remove the 'p' tag from the allowed tags.
			unset( $content_allowed_tags['p'] );

			// Apply wp_kses() for sanitization.
			return wp_kses( $content, $content_allowed_tags );
		}

		// If the content is an array with the 'content' index, modify and sanitize it.
		if ( is_array( $content ) && isset( $content['content'] ) ) {
			// Sanitize the content of the array.
			$content['content'] = $this->prepare_and_sanitize_content( $content['content'] );

			// Return the modified array.
			return $content;
		}

		// If the content is not a string or an array with 'content' index, return the content as is.
		return $content;
	}
}
