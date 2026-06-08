<?php

namespace WPMailSMTP\Admin\Pages;

use WPMailSMTP\Admin\Area;
use WPMailSMTP\Admin\DebugEvents\DebugEvents;
use WPMailSMTP\Admin\DebugEvents\Migration;
use WPMailSMTP\Admin\DebugEvents\Table;
use WPMailSMTP\Admin\PageAbstract;
use WPMailSMTP\Admin\ParentPageAbstract;
use WPMailSMTP\Helpers\UI;
use WPMailSMTP\Options;
use WPMailSMTP\WP;

/**
 * Debug Events settings page.
 *
 * @since 3.0.0
 */
class DebugEventsTab extends PageAbstract {

	/**
	 * Part of the slug of a tab.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	protected $slug = 'debug-events';

	/**
	 * Tab priority.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	protected $priority = 40;

	/**
	 * Debug events list table.
	 *
	 * @since 3.0.0
	 *
	 * @var Table
	 */
	protected $table = null;

	/**
	 * Plugin options.
	 *
	 * @since 3.0.0
	 *
	 * @var Options
	 */
	protected $options;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param ParentPageAbstract $parent_page Tab parent page.
	 */
	public function __construct( $parent_page = null ) {

		$this->options = Options::init();

		parent::__construct( $parent_page );

		// Remove unnecessary $_GET parameters and prevent url duplications in _wp_http_referer input.
		$this->remove_get_parameters();
	}

	/**
	 * Link label of a tab.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_label() {

		return esc_html__( 'Debug Events', 'wp-mail-smtp' );
	}

	/**
	 * Title of a tab.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_title() {

		return $this->get_label();
	}

	/**
	 * Register hooks.
	 *
	 * @since 3.0.0
	 */
	public function hooks() {

		add_action( 'wp_mail_smtp_admin_area_enqueue_assets', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Enqueue required JS and CSS.
	 *
	 * @since 3.0.0
	 */
	public function enqueue_assets() {

		$min = WP::asset_min();

		wp_enqueue_style(
			'wp-mail-smtp-flatpickr',
			wp_mail_smtp()->assets_url . '/css/vendor/flatpickr.min.css',
			[],
			'4.6.9'
		);
		wp_enqueue_script(
			'wp-mail-smtp-flatpickr',
			wp_mail_smtp()->assets_url . '/js/vendor/flatpickr.min.js',
			[ 'jquery' ],
			'4.6.9',
			true
		);

		wp_enqueue_script(
			'wp-mail-smtp-tools-debug-events',
			wp_mail_smtp()->assets_url . "/js/smtp-tools-debug-events{$min}.js",
			[ 'jquery', 'wp-mail-smtp-flatpickr' ],
			WPMS_PLUGIN_VER,
			true
		);

		wp_localize_script(
			'wp-mail-smtp-tools-debug-events',
			'wp_mail_smtp_tools_debug_events',
			[
				'lang_code'  => sanitize_key( WP::get_language_code() ),
				'plugin_url' => wp_mail_smtp()->plugin_url,
				'loader'     => wp_mail_smtp()->prepare_loader( 'blue' ),
				'texts'      => [
					'delete_all_notice' => esc_html__( 'Are you sure you want to permanently delete all debug events?', 'wp-mail-smtp' ),
					'cancel'            => esc_html__( 'Cancel', 'wp-mail-smtp' ),
					'close'             => esc_html__( 'Close', 'wp-mail-smtp' ),
					'yes'               => esc_html__( 'Yes', 'wp-mail-smtp' ),
					'ok'                => esc_html__( 'OK', 'wp-mail-smtp' ),
					'notice_title'      => esc_html__( 'Heads up!', 'wp-mail-smtp' ),
					'error_occurred'    => esc_html__( 'An error occurred!', 'wp-mail-smtp' ),
				],
			]
		);
	}

	/**
	 * Get email logs list table.
	 *
	 * @since 3.0.0
	 *
	 * @return Table
	 */
	public function get_table() {

		if ( $this->table === null ) {
			$this->table = new Table();
		}

		return $this->table;
	}

	/**
	 * Display scheduled actions table.
	 *
	 * @since 3.0.0
	 */
	public function display() {

		?>
		<?php if( WP::use_global_plugin_settings() && ! current_user_can( 'manage_network_options' ) ) : ?>
			<!-- Debug Events Section Title -->
			<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-content wp-mail-smtp-clear section-heading" id="wp-mail-smtp-setting-row-email-heading">
				<div class="wp-mail-smtp-setting-field">
					<h2><?php esc_html_e( 'Debug Events', 'wp-mail-smtp' ); ?></h2>
				</div>
				<p>
					<?php esc_html_e( 'On this page, you can view different plugin debugging events and email sending errors.', 'wp-mail-smtp' ); ?>
				</p>
			</div>
		<?php else: ?>
			<form method="POST" action="<?php echo esc_url( $this->get_link() ); ?>">
				<?php $this->wp_nonce_field(); ?>

				<!-- Debug Events Section Title -->
				<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-content wp-mail-smtp-clear section-heading wp-mail-smtp-section-heading--has-divider">
					<div class="wp-mail-smtp-setting-field">
						<h2><?php esc_html_e( 'Debug Events', 'wp-mail-smtp' ); ?></h2>
					</div>
					<p>
						<?php esc_html_e( 'On this page, you can view and configure different plugin debugging events. View email sending errors and enable debugging events, allowing you to detect email sending issues.', 'wp-mail-smtp' ); ?>
					</p>
				</div>

				<!-- Debug Events -->
				<div id="wp-mail-smtp-setting-row-debug_event_types" class="wp-mail-smtp-setting-row wp-mail-smtp-clear">
					<div class="wp-mail-smtp-setting-label">
						<label for="wp-mail-smtp-setting-debug_event_types">
							<?php esc_html_e( 'Event Types', 'wp-mail-smtp' ); ?>
						</label>
					</div>
					<div class="wp-mail-smtp-setting-field">
						<?php
						UI::toggle(
							[
								'name'     => 'wp-mail-smtp[debug_events][email_errors]',
								'id'       => 'wp-mail-smtp-setting-debug_events_email_errors',
								'value'    => 'true',
								'checked'  => true,
								'disabled' => true,
							]
						);
						?>
						<p class="desc">
							<?php esc_html_e( 'Email Sending Errors', 'wp-mail-smtp' ); ?>
						</p>
						<p class="desc">
							<?php esc_html_e( 'This debug event is always enabled and will record any email sending errors in the table below.', 'wp-mail-smtp' ); ?>
						</p>
						<hr class="wp-mail-smtp-setting-mid-row-sep">
						<?php
						UI::toggle(
							[
								'name'    => 'wp-mail-smtp[debug_events][email_debug]',
								'id'      => 'wp-mail-smtp-setting-debug_events_email_debug',
								'value'   => 'true',
								'checked' => (bool) $this->options->get( 'debug_events', 'email_debug' ),
							]
						);
						?>
						<p class="desc">
							<?php esc_html_e( 'Debug Email Sending', 'wp-mail-smtp' ); ?>
						</p>
						<p class="desc">
							<?php esc_html_e( 'Check this if you would like to debug the email sending process. Once enabled, all debug events will be logged in the table below. This setting should only be enabled for shorter debugging periods and disabled afterwards.', 'wp-mail-smtp' ); ?>
						</p>
					</div>
				</div>

				<div id="wp-mail-smtp-setting-row-debug_events_retention_period" class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-select wp-mail-smtp-clear">
					<div class="wp-mail-smtp-setting-label">
						<label for="wp-mail-smtp-setting-debug_events_retention_period">
							<?php esc_html_e( 'Events Retention Period', 'wp-mail-smtp' ); ?>
						</label>
					</div>
					<div class="wp-mail-smtp-setting-field">
						<select name="wp-mail-smtp[debug_events][retention_period]" id="wp-mail-smtp-setting-debug_events_retention_period"
							<?php disabled( $this->options->is_const_defined( 'debug_events', 'retention_period' ) ); ?>>
							<option value=""><?php esc_html_e( 'Forever', 'wp-mail-smtp' ); ?></option>
							<?php foreach ( $this->get_debug_events_retention_period_options() as $value => $label ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $this->options->get( 'debug_events', 'retention_period' ), $value ); ?>>
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p class="desc">
							<?php
							esc_html_e( 'Debug events older than the selected period will be permanently deleted from the database.', 'wp-mail-smtp' );

							if ( $this->options->is_const_defined( 'debug_events', 'retention_period' ) ) {
								//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo '<br>' . $this->options->get_const_set_message( 'WPMS_DEBUG_EVENTS_RETENTION_PERIOD' );
							}
							?>
						</p>
					</div>
				</div>

				<?php $this->display_save_btn(); ?>
			</form>
		<?php endif; ?>
		<?php

		if ( ! DebugEvents::is_valid_db() ) {
			$this->display_debug_events_not_installed();
		} else {
			$table = $this->get_table();
			$table->prepare_items();

			?>
			<form action="<?php echo esc_url( $this->get_link() ); ?>" method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( Area::SLUG . '-tools' ); ?>" />
				<input type="hidden" name="tab" value="<?php echo esc_attr( $this->get_slug() ); ?>" />
			<?php

			// State of status filter for submission with other filters.
			if ( $table->get_filtered_types() !== false ) {
				printf( '<input type="hidden" name="type" value="%s">', esc_attr( $table->get_filtered_types() ) );
			}

			if ( $this->get_filters_html() ) {
				?>
				<div id="wp-mail-smtp-reset-filter">
					<?php
					$type = $table->get_filtered_types();

					echo wp_kses(
						sprintf( /* translators: %1$s - number of debug events found; %2$s - filtered type. */
							_n(
								'Found <strong>%1$s %2$s event</strong>',
								'Found <strong>%1$s %2$s events</strong>',
								absint( $table->get_pagination_arg( 'total_items' ) ),
								'wp-mail-smtp'
							),
							absint( $table->get_pagination_arg( 'total_items' ) ),
							$type !== false && isset( $table->get_types()[ $type ] ) ? $table->get_types()[ $type ] : ''
						),
						[
							'strong' => [],
						]
					);
					?>

					<?php foreach ( $this->get_filters_html() as $id => $html ) : ?>
						<?php
						echo wp_kses(
							$html,
							[ 'em' => [] ]
						);
						?>
						<i class="reset dashicons dashicons-dismiss" data-scope="<?php echo esc_attr( $id ); ?>"></i>
					<?php endforeach; ?>
				</div>
				<?php
			}

			$table->search_box(
				esc_html__( 'Search Events', 'wp-mail-smtp' ),
				Area::SLUG . '-debug-events-search-input'
			);

			$table->views();
			$table->display();
			?>
			</form>
			<?php
		}
	}

	/**
	 * Process tab form submission ($_POST ).
	 *
	 * @since 3.0.0
	 *
	 * @param array $data Post data specific for the plugin.
	 */
	public function process_post( $data ) {

		if ( WP::use_global_plugin_settings() && ! current_user_can( 'manage_network_options' ) ) {
			wp_die( esc_html__( 'You don\'t have the capability to perform this action.', 'wp-mail-smtp' ) );
		}

		// Unchecked checkboxes doesn't exist in $_POST, so we need to ensure we actually have them in data to save.
		if ( empty( $data['debug_events']['email_debug'] ) ) {
			$data['debug_events']['email_debug'] = false;
		}

		// All the sanitization is done there.
		$this->options->set( $data, false, false );

		WP::add_admin_notice(
			esc_html__( 'Settings were successfully saved.', 'wp-mail-smtp' ),
			WP::ADMIN_NOTICE_SUCCESS
		);
	}

	/**
	 * Return an array with information (HTML and id) for each filter for this current view.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	private function get_filters_html() {

		$filters = [
			'.search-box'               => $this->get_filter_search_html(),
			'.wp-mail-smtp-filter-date' => $this->get_filter_date_html(),
		];

		return array_filter( $filters );
	}

	/**
	 * Return HTML with information about the search filter.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	private function get_filter_search_html() {

		$table = $this->get_table();
		$term  = $table->get_filtered_search();

		if ( $term === false ) {
			return '';
		}

		return sprintf( /* translators: %s The searched term. */
			__( 'where event contains "%s"', 'wp-mail-smtp' ),
			'<em>' . esc_html( $term ) . '</em>'
		);
	}

	/**
	 * Return HTML with information about the date filter.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	private function get_filter_date_html() {

		$table = $this->get_table();
		$dates = $table->get_filtered_dates();

		if ( $dates === false ) {
			return '';
		}

		$dates = array_map(
			function ( $date ) {
				return date_i18n( 'M j, Y', strtotime( $date ) );
			},
			$dates
		);

		$html = '';

		switch ( count( $dates ) ) {
			case 1:
				$html = sprintf( /* translators: %s - Date. */
					esc_html__( 'on %s', 'wp-mail-smtp' ),
					'<em>' . $dates[0] . '</em>'
				);
				break;
			case 2:
				$html = sprintf( /* translators: %1$s - Date. %2$s - Date. */
					esc_html__( 'between %1$s and %2$s', 'wp-mail-smtp' ),
					'<em>' . $dates[0] . '</em>',
					'<em>' . $dates[1] . '</em>'
				);
				break;
		}

		return $html;
	}

	/**
	 * Display a message when debug events DB table is missing.
	 *
	 * @since 3.0.0
	 */
	private function display_debug_events_not_installed() {

		$error_message = get_option( Migration::ERROR_OPTION_NAME );

		$create_missing_tables_url = wp_nonce_url(
			add_query_arg(
				[
					'create-missing-db-tables' => 1,
				],
				$this->get_link()
			),
			Area::SLUG . '-create-missing-db-tables'
		);

		$contact_support_url = wp_mail_smtp()->get_utm_url(
			'https://wpmailsmtp.com/account/support/',
			[
				'medium'  => 'debug-events',
				'content' => 'Debug Events not installed correctly',
			]
		);
		?>

		<div class="notice-inline notice-error">
			<h3><?php esc_html_e( 'Debug Events are Not Installed Correctly', 'wp-mail-smtp' ); ?></h3>

			<p>
				<?php
				if ( ! empty( $error_message ) ) {
					echo wp_kses(
						sprintf( /* translators: %1$s - create missing tables link; %2$s - contact support link. */
							__( 'WP Mail SMTP is using custom database tables for some of its features. In order to work properly, the custom tables should be created, and it seems they are missing. Please try to <a href="%1$s">create the missing DB tables by clicking on this link</a>. If this issue persists, please <a href="%2$s" target="_blank" rel="noopener noreferrer">contact our support</a> and provide the error message below:', 'wp-mail-smtp' ),
							esc_url( $create_missing_tables_url ),
							esc_url( $contact_support_url )
						),
						[
							'a' => [
								'href'   => [],
								'target' => [],
								'rel'    => [],
							],
						]
					);
					echo '<br><br>';
					echo '<code>' . esc_html( $error_message ) . '</code>';
				} else {
					echo wp_kses(
						sprintf( /* translators: %1$s - create missing tables link; %2$s - contact support link. */
							__( 'WP Mail SMTP is using custom database tables for some of its features. In order to work properly, the custom tables should be created, and it seems they are missing. Please try to <a href="%1$s">create the missing DB tables by clicking on this link</a>. If this issue persists, please <a href="%2$s" target="_blank" rel="noopener noreferrer">contact our support</a>.', 'wp-mail-smtp' ),
							esc_url( $create_missing_tables_url ),
							esc_url( $contact_support_url )
						),
						[
							'a' => [
								'href'   => [],
								'target' => [],
								'rel'    => [],
							],
						]
					);
				}
				?>
			</p>
		</div>

		<?php
	}

	/**
	 * Remove unnecessary $_GET parameters for shorter URL.
	 *
	 * @since 3.0.0
	 */
	protected function remove_get_parameters() {

		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$_SERVER['REQUEST_URI'] = remove_query_arg(
				[
					'_wp_http_referer',
					'_wpnonce',
					'wp-mail-smtp-debug-events-nonce',
				],
				$_SERVER['REQUEST_URI'] // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			);
		}
	}

	/**
	 * Get debug events retention period options.
	 *
	 * @since 3.6.0
	 *
	 * @return array
	 */
	protected function get_debug_events_retention_period_options() {

		$options = [
			604800   => esc_html__( '1 Week', 'wp-mail-smtp' ),
			2628000  => esc_html__( '1 Month', 'wp-mail-smtp' ),
			7885000  => esc_html__( '3 Months', 'wp-mail-smtp' ),
			15770000 => esc_html__( '6 Months', 'wp-mail-smtp' ),
			31540000 => esc_html__( '1 Year', 'wp-mail-smtp' ),
		];

		$debug_event_retention_period = $this->options->get( 'debug_events', 'retention_period' );

		// Check if defined value already in list and add it if not.
		if (
			! empty( $debug_event_retention_period ) &&
			! isset( $options[ $debug_event_retention_period ] )
		) {
			$debug_event_retention_period_days = floor( $debug_event_retention_period / DAY_IN_SECONDS );

			$options[ $debug_event_retention_period ] = sprintf(
				/* translators: %d - days count. */
				_n( '%d Day', '%d Days', $debug_event_retention_period_days, 'wp-mail-smtp' ),
				$debug_event_retention_period_days
			);

			ksort( $options );
		}

		/**
		 * Filter debug events retention period options.
		 *
		 * @since 3.6.0
		 *
		 * @param array $options Debug Events retention period options.
		 *                       Option key in seconds.
		 */
		return apply_filters(
			'wp_mail_smtp_admin_pages_debug_events_tab_get_debug_events_retention_period_options',
			$options
		);
	}
}
