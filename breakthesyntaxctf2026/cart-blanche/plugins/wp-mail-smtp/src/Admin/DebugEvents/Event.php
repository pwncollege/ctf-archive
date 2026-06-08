<?php

namespace WPMailSMTP\Admin\DebugEvents;

use WPMailSMTP\WP;

/**
 * Debug Event class.
 *
 * @since 3.0.0
 */
class Event {

	/**
	 * This is an error event.
	 *
	 * @since 3.0.0
	 */
	const TYPE_ERROR = 0;

	/**
	 * This is a debug event.
	 *
	 * @since 3.0.0
	 */
	const TYPE_DEBUG = 1;

	/**
	 * The event's ID.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	protected $id = 0;

	/**
	 * The event's content.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	protected $content = '';

	/**
	 * The event's initiator - who called the `wp_mail` function?
	 * JSON encoded string.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	protected $initiator = '';

	/**
	 * The event's type.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	protected $event_type = 0;

	/**
	 * The date and time when this event was created.
	 *
	 * @since 3.0.0
	 *
	 * @var \DateTime
	 */
	protected $created_at;

	/**
	 * Retrieve a particular event when constructing the object.
	 *
	 * @since 3.0.0
	 *
	 * @param int|object $id_or_row The event ID or object with event attributes.
	 */
	public function __construct( $id_or_row = null ) {

		$this->populate_event( $id_or_row );
	}

	/**
	 * Get and prepare the event data.
	 *
	 * @since 3.0.0
	 *
	 * @param int|object $id_or_row The event ID or object with event attributes.
	 */
	private function populate_event( $id_or_row ) {

		$event = null;

		if ( is_numeric( $id_or_row ) ) {
			// Get by ID.
			$collection = new EventsCollection( [ 'id' => (int) $id_or_row ] );
			$events     = $collection->get();

			if ( $events->valid() ) {
				$event = $events->current();
			}
		} elseif (
			is_object( $id_or_row ) &&
			isset(
				$id_or_row->id,
				$id_or_row->content,
				$id_or_row->initiator,
				$id_or_row->event_type,
				$id_or_row->created_at
			)
		) {
			$event = $id_or_row;
		}

		if ( $event !== null ) {
			foreach ( get_object_vars( $event ) as $key => $value ) {
				$this->{$key} = $value;
			}
		}
	}

	/**
	 * Event ID as per our DB table.
	 *
	 * @since 3.0.0
	 *
	 * @return int
	 */
	public function get_id() {

		return (int) $this->id;
	}

	/**
	 * Get the event title.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_title() {

		/* translators: %d the event ID. */
		return sprintf( esc_html__( 'Event #%d', 'wp-mail-smtp' ), $this->get_id() );
	}

	/**
	 * Get the content of the event.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_content() {

		return $this->content;
	}

	/**
	 * Get the event's type.
	 *
	 * @since 3.0.0
	 *
	 * @return int
	 */
	public function get_type() {

		return (int) $this->event_type;
	}

	/**
	 * Get the list of all event types.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public static function get_types() {

		return [
			self::TYPE_ERROR => esc_html__( 'Error', 'wp-mail-smtp' ),
			self::TYPE_DEBUG => esc_html__( 'Debug', 'wp-mail-smtp' ),
		];
	}

	/**
	 * Get human readable type name.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_type_name() {

		$types = self::get_types();

		return isset( $types[ $this->get_type() ] ) ? $types[ $this->get_type() ] : '';
	}

	/**
	 * Get the date/time when this event was created.
	 *
	 * @since 3.0.0
	 *
	 * @throws \Exception Emits exception on incorrect date.
	 *
	 * @return \DateTime
	 */
	public function get_created_at() {

		$timezone = new \DateTimeZone( 'UTC' );
		$date     = false;

		if ( ! empty( $this->created_at ) ) {
			$date = \DateTime::createFromFormat( WP::datetime_mysql_format(), $this->created_at, $timezone );
		}

		if ( $date === false ) {
			$date = new \DateTime( 'now', $timezone );
		}

		return $date;
	}

	/**
	 * Get the date/time when this event was created in a nicely formatted string.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_created_at_formatted() {

		try {
			$date = $this->get_created_at();
		} catch ( \Exception $e ) {
			$date = null;
		}

		if ( empty( $date ) ) {
			return esc_html__( 'N/A', 'wp-mail-smtp' );
		}

		return esc_html(
			date_i18n(
				WP::datetime_format(),
				strtotime( get_date_from_gmt( $date->format( WP::datetime_mysql_format() ) ) )
			)
		);
	}

	/**
	 * Get the event's initiator raw data.
	 * Who called the `wp_mail` function?
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function get_initiator_raw() {

		return json_decode( $this->initiator, true );
	}

	/**
	 * Get the event's initiator name.
	 * Which plugin/theme (or WP core) called the `wp_mail` function?
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_initiator() {

		$initiator = (array) $this->get_initiator_raw();

		if ( empty( $initiator['file'] ) ) {
			return '';
		}

		return WP::get_initiator_name( $initiator['file'] );
	}

	/**
	 * Get the event's initiator file path.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_initiator_file_path() {

		$initiator = (array) $this->get_initiator_raw();

		if ( empty( $initiator['file'] ) ) {
			return '';
		}

		return $initiator['file'];
	}

	/**
	 * Get the event's initiator file line.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_initiator_file_line() {

		$initiator = (array) $this->get_initiator_raw();

		if ( empty( $initiator['line'] ) ) {
			return '';
		}

		return $initiator['line'];
	}

	/**
	 * Get the event's initiator backtrace.
	 *
	 * @since 3.6.0
	 *
	 * @return array
	 */
	private function get_initiator_backtrace() {

		$initiator = (array) $this->get_initiator_raw();

		if ( empty( $initiator['backtrace'] ) ) {
			return [];
		}

		return $initiator['backtrace'];
	}

	/**
	 * Get the event preview HTML.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_details_html() {

		$initiator           = $this->get_initiator();
		$initiator_backtrace = $this->get_initiator_backtrace();

		ob_start();
		?>
		<div class="wp-mail-smtp-debug-event-preview">
			<div class="wp-mail-smtp-debug-event-preview-subtitle">
				<span><?php esc_html_e( 'Debug Event Details', 'wp-mail-smtp' ); ?></span>
			</div>
			<div class="wp-mail-smtp-debug-event-row wp-mail-smtp-debug-event-preview-type">
				<span class="debug-event-label"><?php esc_html_e( 'Type', 'wp-mail-smtp' ); ?></span>
				<span class="debug-event-value"><?php echo esc_html( $this->get_type_name() ); ?></span>
			</div>
			<div class="wp-mail-smtp-debug-event-row wp-mail-smtp-debug-event-preview-date">
				<span class="debug-event-label"><?php esc_html_e( 'Date', 'wp-mail-smtp' ); ?></span>
				<span class="debug-event-value"><?php echo esc_html( $this->get_created_at_formatted() ); ?></span>
			</div>
			<div class="wp-mail-smtp-debug-event-row wp-mail-smtp-debug-event-preview-content">
				<span class="debug-event-label"><?php esc_html_e( 'Content', 'wp-mail-smtp' ); ?></span>
				<div class="debug-event-value">
						<?php echo wp_kses( str_replace( [ "\r\n", "\r", "\n" ], '<br>', $this->get_content() ), [ 'br' => [] ] ); ?>
				</div>
			</div>
			<?php if ( ! empty( $initiator ) ) : ?>
			<div class="wp-mail-smtp-debug-event-row wp-mail-smtp-debug-event-preview-caller">
				<span class="debug-event-label"><?php esc_html_e( 'Source', 'wp-mail-smtp' ); ?></span>
				<div class="debug-event-value">
					<span class="debug-event-initiator"><?php echo esc_html( $initiator ); ?></span>
					<p class="debug-event-code">
						<?php
						printf( /* Translators: %1$s the path of a file, %2$s the line number in the file. */
							esc_html__( '%1$s (line: %2$s)', 'wp-mail-smtp' ),
							esc_html( $this->get_initiator_file_path() ),
							esc_html( $this->get_initiator_file_line() )
						);
						?>

						<?php if ( ! empty( $initiator_backtrace ) ) : ?>
							<br><br>
							<b><?php esc_html_e( 'Backtrace:', 'wp-mail-smtp' ); ?></b>
							<br>
							<?php
							foreach ( $initiator_backtrace as $i => $item ) {
								printf(
									/* translators: %1$d - index number; %2$s - function name; %3$s - file path; %4$s - line number. */
									esc_html__( '[%1$d] %2$s called at [%3$s:%4$s]', 'wp-mail-smtp' ),
									$i,
									isset( $item['class'] ) ? esc_html( $item['class'] . $item['type'] . $item['function'] ) : esc_html( $item['function'] ),
									isset( $item['file'] ) ? esc_html( $item['file'] ) : '', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									isset( $item['line'] ) ? esc_html( $item['line'] ) : '' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								);
								echo '<br>';
							}
							?>
						<?php endif; ?>
					</p>
				</div>
			</div>
			<?php endif; ?>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Get the short details about this event (event content and the initiator's name).
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_short_details() {

		$result = [];

		if ( ! empty( $this->get_initiator() ) ) {
			$result[] = sprintf(
				/* Translators: %s - Email initiator/source name. */
				esc_html__( 'Email Source: %s', 'wp-mail-smtp' ),
				esc_html( $this->get_initiator() )
			);
		}

		$result[] = esc_html( $this->get_content() );

		return implode( WP::EOL, $result );
	}

	/**
	 * Save a new or modified event in DB.
	 *
	 * @since 3.0.0
	 *
	 * @throws \Exception When event init fails.
	 *
	 * @return Event New or updated event class instance.
	 */
	public function save() {

		global $wpdb;

		$table = DebugEvents::get_table_name();

		if ( (bool) $this->get_id() ) {
			// Update the existing DB table record.
			$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
				$table,
				[
					'content'    => $this->content,
					'initiator'  => $this->initiator,
					'event_type' => $this->event_type,
					'created_at' => $this->get_created_at()->format( WP::datetime_mysql_format() ),
				],
				[
					'id' => $this->get_id(),
				],
				[
					'%s', // content.
					'%s', // initiator.
					'%s', // type.
					'%s', // created_at.
				],
				[
					'%d',
				]
			);

			$event_id = $this->get_id();
		} else {
			// Create a new DB table record.
			$wpdb->insert(
				$table,
				[
					'content'    => $this->content,
					'initiator'  => $this->initiator,
					'event_type' => $this->event_type,
					'created_at' => $this->get_created_at()->format( WP::datetime_mysql_format() ),
				],
				[
					'%s', // content.
					'%s', // initiator.
					'%s', // type.
					'%s', // created_at.
				]
			);

			$event_id = $wpdb->insert_id;
		}

		try {
			$event = new Event( $event_id );
		} catch ( \Exception $e ) {
			$event = new Event();
		}

		return $event;
	}

	/**
	 * Set the content of this event.
	 *
	 * @since 3.0.0
	 *
	 * @param string|array $content The event's content.
	 */
	public function set_content( $content ) {

		if ( ! is_string( $content ) ) {
			$this->content = wp_json_encode( $content );
		} else {
			$this->content = wp_strip_all_tags( str_replace( '<br>', "\r\n", $content ), false );
		}
	}

	/**
	 * Set the initiator by checking the backtrace for the wp_mail function call.
	 *
	 * @since 3.0.0
	 */
	public function set_initiator() {

		$initiator = wp_mail_smtp()->get_wp_mail_initiator();

		if ( empty( $initiator->get_file() ) ) {
			return;
		}

		$data['file'] = $initiator->get_file();

		if ( ! empty( $initiator->get_line() ) ) {
			$data['line'] = $initiator->get_line();
		}

		if ( DebugEvents::is_debug_enabled() ) {
			$data['backtrace'] = $initiator->get_backtrace();
		}

		$this->initiator = wp_json_encode( $data );
	}

	/**
	 * Set the type of this event.
	 *
	 * @since 3.0.0
	 *
	 * @param int $type The event's type.
	 */
	public function set_type( $type ) {

		$this->event_type = (int) $type;
	}

	/**
	 * Whether the event instance is a valid entity to work with.
	 *
	 * @since 3.0.0
	 */
	public function is_valid() {

		return ! ( empty( $this->id ) || empty( $this->created_at ) );
	}

	/**
	 * Whether this is an error event.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	public function is_error() {

		return self::TYPE_ERROR === $this->get_type();
	}

	/**
	 * Whether this is a debug event.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	public function is_debug() {

		return self::TYPE_DEBUG === $this->get_type();
	}
}

