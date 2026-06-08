<?php

namespace WPForms\SmartTags\SmartTag;

use WP_User;

/**
 * Class SmartTag.
 *
 * @since 1.6.7
 */
abstract class SmartTag {

	/**
	 * Full smart tag.
	 * For example, {smart_tag attr="1" attr2="true"}.
	 *
	 * @since 1.6.7
	 *
	 * @var string
	 */
	protected $smart_tag;

	/**
	 * Context.
	 *
	 * @since 1.8.7
	 *
	 * @var string
	 */
	public $context;

	/**
	 * Context data.
	 *
	 * @since 1.9.9.2
	 *
	 * @var array
	 */
	public $context_data;

	/**
	 * List of attributes.
	 *
	 * @since 1.6.7
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * SmartTag constructor.
	 *
	 * @since 1.6.7
	 * @since 1.8.7 Added $context parameter.
	 *
	 * @param string $smart_tag    Full smart tag.
	 * @param string $context      Context.
	 * @param array  $context_data Context data.
	 */
	public function __construct( $smart_tag, $context = '', array $context_data = [] ) {

		$this->smart_tag    = $smart_tag;
		$this->context      = $context;
		$this->context_data = $context_data;
	}

	/**
	 * Get smart tag value.
	 *
	 * @since 1.6.7
	 *
	 * @param array  $form_data Form data.
	 * @param array  $fields    List of fields.
	 * @param string $entry_id  Entry ID.
	 *
	 * @return string
	 */
	abstract public function get_value( $form_data, $fields = [], $entry_id = '' );

	/**
	 * Get a list of smart tag attributes.
	 *
	 * @since 1.6.7
	 *
	 * @return array
	 */
	public function get_attributes() {

		if ( ! empty( $this->attributes ) ) {
			return $this->attributes;
		}

		/**
		 * (\w+) an attribute name and also the first capturing group. Lowercase or uppercase letters, digits, underscore.
		 * = the equal sign.
		 * (["\']) single or double quote, the second capturing group.
		 * (.+?) an attribute value within the quotes, and also the third capturing group. Any number of any characters except the new line. Lazy mode - match as few characters as possible to allow multiple attributes on one line.
		 * \2 - repeat the second capturing group.
		 */
		preg_match_all( '/(\w+)=(["\'])(.+?)\2/', $this->smart_tag, $attributes );
		$this->attributes = array_combine( $attributes[1], $attributes[3] );

		return $this->attributes;
	}

	/**
	 * Get current user.
	 *
	 * @since 1.8.7
	 *
	 * @param string|int $entry_id Entry ID.
	 *
	 * @return WP_User|string
	 */
	public function get_user( $entry_id ) {

		$user = $this->get_entry_user( $entry_id );

		if ( ! empty( $user ) ) {
			return $user;
		}

		return ! wpforms_doing_scheduled_action() && is_user_logged_in() ? wp_get_current_user() : '';
	}

	/**
	 * Get user from the entry.
	 *
	 * @since 1.8.8
	 *
	 * @param string|int $entry_id Entry ID.
	 *
	 * @return WP_User|string
	 */
	private function get_entry_user( $entry_id ) {

		$entry_user_id = $this->get_entry_user_id( $entry_id );

		if ( empty( $entry_user_id ) ) {
			return '';
		}

		$user = get_user_by( 'id', $entry_user_id );

		return $user instanceof WP_User ? $user : '';
	}

	/**
	 * Retrieve user ID from entry meta or AS task.
	 *
	 * @since 1.9.4
	 *
	 * @param int|string $entry_id Entry ID.
	 *
	 * @return int
	 */
	private function get_entry_user_id( $entry_id ): int {

		if ( empty( $entry_id ) ) {
			return (int) $this->get_meta( 0, 'user_id' );
		}

		$entry = wpforms()->obj( 'entry' );

		if ( empty( $entry ) ) {
			return 0;
		}

		$entry_data = $entry->get( $entry_id );

		return $entry_data && isset( $entry_data->user_id ) ? (int) $entry_data->user_id : 0;
	}

	/**
	 * Get author.
	 *
	 * @since 1.8.7
	 *
	 * @param int $post_id Submitted post ID.
	 *
	 * @return WP_User|false WP_User object on success, false on failure.
	 */
	public function get_author( $post_id ) {

		$author_id = get_post_field( 'post_author', $post_id );

		return get_user_by( 'id', $author_id );
	}

	/**
	 * Get author property.
	 *
	 * @since 1.8.8
	 *
	 * @param int|string $entry_id Entry ID.
	 * @param string     $meta_key User property.
	 *
	 * @return string
	 */
	protected function get_author_meta( $entry_id, string $meta_key ): string {

		$page_id = $this->get_meta( $entry_id, 'page_id' );

		if ( empty( $page_id ) ) {
			return '';
		}

		$author = $this->get_author( $page_id );

		if ( ! $author ) {
			return '';
		}

		return $author->{$meta_key} ?? '';
	}

	/**
	 * Get entry meta.
	 *
	 * @since 1.8.7
	 *
	 * @param string|int $entry_id Entry ID.
	 * @param string     $meta_key Meta key.
	 *
	 * @return string Meta value.
	 */
	public function get_meta( $entry_id, string $meta_key ): string {

		$meta_data = '';

		if ( ! empty( $entry_id ) ) {
			$entry_meta = wpforms()->obj( 'entry_meta' );

			if ( $entry_meta ) {
				$meta = $entry_meta->get_meta(
					[
						'entry_id' => $entry_id,
						'type'     => $meta_key,
						'number'   => 1,
					]
				);

				$meta_data = isset( $meta[0]->data ) ? (string) $meta[0]->data : '';
			}
		}

		/**
		 * Allow modifying the entry meta-value.
		 *
		 * @since 1.9.4
		 *
		 * @param string     $meta_data Meta value.
		 * @param string     $meta_key  Meta key.
		 * @param string|int $entry_id  Entry ID.
		 * @param SmartTag   $smart_tag Smart tag object.
		 *
		 * @return string
		 */
		return (string) apply_filters( 'wpforms_smart_tags_smart_tag_get_meta_value', $meta_data, $meta_key, $entry_id, $this ); //phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Get a formatted field value.
	 *
	 * @since 1.8.9
	 *
	 * @param int    $field_id  Field ID.
	 * @param array  $fields    List of fields.
	 * @param string $field_key Field key to get value from.
	 * @param array  $form_data Form data.
	 *
	 * @return string
	 */
	protected function get_formatted_field_value( int $field_id, array $fields, string $field_key, array $form_data = [] ): string {

		$value = $fields[ $field_id ][ $field_key ] ?? '';

		/**
		 * Allow modifying the formatted field value.
		 *
		 * @since 1.9.0
		 *
		 * @param string $value     Field value.
		 * @param int    $field_id  Field ID.
		 * @param array  $fields    List of fields.
		 * @param string $field_key Field key to get value from.
		 * @param array  $form_data Form data.
		 *
		 * @return string
		 */
		$value = (string) apply_filters( 'wpforms_smart_tags_formatted_field_value', $value, $field_id, $fields, $field_key, $form_data ); //phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		if ( ! wpforms_is_repeated_field( $field_id, $fields ) ) {
			return $value;
		}

		return $this->get_repeated_field_value( $value, $field_id, $fields, $field_key );
	}

	/**
	 * Get repeated fields value.
	 *
	 * @since 1.8.9
	 *
	 * @param string $value     Field value.
	 * @param int    $field_id  Field ID.
	 * @param array  $fields    List of fields.
	 * @param string $field_key Field key to get value from.
	 *
	 * @return string
	 */
	private function get_repeated_field_value( string $value, int $field_id, array $fields, string $field_key ): string {

		$comma_separated_contexts = [ 'notification-send-to-email', 'notification-carboncopy' ];
		$prefix                   = $field_id . '_';
		$separator                = in_array( $this->context, $comma_separated_contexts, true ) ? ',' : "\n";

		foreach ( $fields as $key => $field ) {
			if ( strpos( $key, $prefix ) !== 0 ) {
				continue;
			}

			if ( ! isset( $field[ $field_key ] ) ) {
				continue;
			}

			$value .= $separator . $field[ $field_key ];
		}

		return $value;
	}

	/**
	 * Check if a user has capabilities to get the smart tag value.
	 *
	 * @since 1.9.9.2
	 *
	 * @return bool
	 */
	protected function has_cap(): bool {

		switch ( $this->context ) {
			case 'notification':
			case 'notification-carboncopy':
			case 'notification-from':
			case 'notification-reply-to':
			case 'email':
				$cap = $this->recipient_has_cap();
				break;

			case 'confirmation':
				$cap = current_user_can( 'manage_options' );
				break;

			default:
				$cap = true;
		}

		return $cap;
	}

	/**
	 * Check if the notification recipient is allowed to view the author email.
	 *
	 * @since 1.9.9.2
	 *
	 * @return bool
	 */
	private function recipient_has_cap(): bool {

		$emails = $this->context_data['to_email'] ?? [];
		$emails = array_unique( array_filter( array_map( 'trim', $emails ) ) );

		if ( ! $emails ) {
			return false;
		}

		return array_reduce(
			$emails,
			static function ( $carry, $email ) {

				$user = get_user_by( 'email', $email );

				return $carry && $user && user_can( $user, 'manage_options' );
			},
			true
		);
	}
}
