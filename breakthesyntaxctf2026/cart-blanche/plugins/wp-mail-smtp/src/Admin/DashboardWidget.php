<?php

namespace WPMailSMTP\Admin;

use WPMailSMTP\Admin\DebugEvents\DebugEvents;
use WPMailSMTP\Helpers\Helpers;
use WPMailSMTP\Options;
use WPMailSMTP\WP;
use WPMailSMTP\Reports\Reports;
use WPMailSMTP\Reports\Emails\Summary as SummaryReportEmail;

/**
 * Dashboard Widget shows the number of sent emails in WP Dashboard.
 *
 * @since 2.9.0
 */
class DashboardWidget {

	/**
	 * Instance slug.
	 *
	 * @since 2.9.0
	 *
	 * @const string
	 */
	const SLUG = 'dash_widget_lite';

	/**
	 * The WP option key for storing the total number of sent emails.
	 *
	 * @since 2.9.0
	 * @since 3.0.0 Constant moved to Reports class.
	 *
	 * @const string
	 */
	const SENT_EMAILS_COUNTER_OPTION_KEY = Reports::SENT_EMAILS_COUNTER_OPTION_KEY;

	/**
	 * Constructor.
	 *
	 * @since 2.9.0
	 */
	public function __construct() {}

	/**
	 * Init class.
	 *
	 * @since 2.9.0
	 */
	public function init() {

		// Prevent the class initialization, if the dashboard widget hidden setting is enabled.
		if ( Options::init()->get( 'general', 'dashboard_widget_hidden' ) ) {
			return;
		}

		add_action(
			'admin_init',
			function() {

				// This widget should be displayed for certain high-level users only.
				if ( ! current_user_can( wp_mail_smtp()->get_capability_manage_options() ) ) {
					return;
				}

				/**
				 * Filters whether the initialization of the dashboard widget should be allowed.
				 *
				 * @since 2.9.0
				 *
				 * @param bool $var If the dashboard widget should be initialized.
				 */
				if ( ! apply_filters( 'wp_mail_smtp_admin_dashboard_widget', '__return_true' ) ) {
					return;
				}

				$this->hooks();
			}
		);
	}

	/**
	 * Widget hooks.
	 *
	 * @since 2.9.0
	 */
	public function hooks() {

		add_action( 'admin_enqueue_scripts', [ $this, 'widget_scripts' ] );
		add_action( 'wp_dashboard_setup', [ $this, 'widget_register' ] );

		add_action( 'wp_ajax_wp_mail_smtp_' . static::SLUG . '_save_widget_meta', [ $this, 'save_widget_meta_ajax' ] );
		add_action(
			'wp_ajax_wp_mail_smtp_' . static::SLUG . '_enable_summary_report_email',
			[
				$this,
				'enable_summary_report_email_ajax',
			]
		);
	}

	/**
	 * Load widget-specific scripts.
	 * Load them only on the admin dashboard page.
	 *
	 * @since 2.9.0
	 */
	public function widget_scripts() {

		$screen = get_current_screen();

		if ( ! isset( $screen->id ) || 'dashboard' !== $screen->id ) {
			return;
		}

		$min = WP::asset_min();

		wp_enqueue_style(
			'wp-mail-smtp-dashboard-widget',
			wp_mail_smtp()->assets_url . '/css/dashboard-widget.min.css',
			[],
			WPMS_PLUGIN_VER
		);

		wp_enqueue_script(
			'wp-mail-smtp-chart',
			wp_mail_smtp()->assets_url . '/js/vendor/chart.min.js',
			[ 'moment' ],
			'4.4.9',
			true
		);

		wp_enqueue_script(
			'wp-mail-smtp-chart-adapter',
			wp_mail_smtp()->assets_url . '/js/vendor/chartjs-adapter-moment.min.js',
			[ 'moment', 'wp-mail-smtp-chart' ],
			'1.0.1',
			true
		);

		wp_enqueue_script(
			'wp-mail-smtp-dashboard-widget',
			wp_mail_smtp()->assets_url . "/js/smtp-dashboard-widget{$min}.js",
			[ 'jquery', 'wp-mail-smtp-chart', 'wp-mail-smtp-chart-adapter' ],
			WPMS_PLUGIN_VER,
			true
		);

		wp_localize_script(
			'wp-mail-smtp-dashboard-widget',
			'wp_mail_smtp_dashboard_widget',
			[
				'slug'  => static::SLUG,
				'nonce' => wp_create_nonce( 'wp_mail_smtp_' . static::SLUG . '_nonce' ),
			]
		);
	}

	/**
	 * Register the widget.
	 *
	 * @since 2.9.0
	 */
	public function widget_register() {

		global $wp_meta_boxes;

		$widget_key = 'wp_mail_smtp_reports_widget_lite';

		wp_add_dashboard_widget(
			$widget_key,
			esc_html__( 'WP Mail SMTP', 'wp-mail-smtp' ),
			[ $this, 'widget_content' ]
		);

		// Attempt to place the widget at the top.
		$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];

		if ( isset( $normal_dashboard[ $widget_key ] ) ) {
			$widget_instance = [ $widget_key => $normal_dashboard[ $widget_key ] ];

			unset( $normal_dashboard[ $widget_key ] );

			$sorted_dashboard = array_merge( $widget_instance, $normal_dashboard );

			//phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
		}
	}

	/**
	 * Save a widget meta for a current user using AJAX.
	 *
	 * @since 2.9.0
	 */
	public function save_widget_meta_ajax() {

		check_admin_referer( 'wp_mail_smtp_' . static::SLUG . '_nonce' );

		if ( ! current_user_can( wp_mail_smtp()->get_capability_manage_options() ) ) {
			wp_send_json_error();
		}

		$meta  = ! empty( $_POST['meta'] ) ? sanitize_key( $_POST['meta'] ) : '';
		$value = ! empty( $_POST['value'] ) ? sanitize_key( $_POST['value'] ) : 0;

		$this->widget_meta( 'set', $meta, $value );

		wp_send_json_success();
	}

	/**
	 * Enable summary report email using AJAX.
	 *
	 * @since 3.0.0
	 */
	public function enable_summary_report_email_ajax() {

		check_admin_referer( 'wp_mail_smtp_' . static::SLUG . '_nonce' );

		if ( ! current_user_can( wp_mail_smtp()->get_capability_manage_options() ) ) {
			wp_send_json_error();
		}

		$options = Options::init();

		$data = [
			'general' => [
				SummaryReportEmail::SETTINGS_SLUG => false,
			],
		];

		$options->set( $data, false, false );

		wp_send_json_success();
	}

	/**
	 * Load widget content.
	 *
	 * @since 2.9.0
	 */
	public function widget_content() {

		echo '<div class="wp-mail-smtp-dash-widget wp-mail-smtp-dash-widget--lite">';

		$this->widget_content_html();

		echo '</div>';
	}

	/**
	 * Increment the number of total emails sent by 1.
	 *
	 * @deprecated 3.0.0
	 *
	 * @since 2.9.0
	 */
	public function increment_sent_email_counter() {

		_deprecated_function( __METHOD__, '3.0.0' );
	}

	/**
	 * Widget content HTML.
	 *
	 * @since 2.9.0
	 */
	private function widget_content_html() {

		$hide_graph = (bool) $this->widget_meta( 'get', 'hide_graph' );
		?>

		<?php if ( ! $hide_graph ) : ?>
		<div class="wp-mail-smtp-dash-widget-chart-block-container">
			<div class="wp-mail-smtp-dash-widget-block wp-mail-smtp-dash-widget-chart-block">
				<canvas id="wp-mail-smtp-dash-widget-chart" width="554" height="291"></canvas>
				<div class="wp-mail-smtp-dash-widget-chart-upgrade">
					<div class="wp-mail-smtp-dash-widget-modal">
						<a href="#" class="wp-mail-smtp-dash-widget-dismiss-chart-upgrade">
							<span class="dashicons dashicons-no-alt"></span>
						</a>
						<h2><?php esc_html_e( 'View Detailed Email Stats', 'wp-mail-smtp' ); ?></h2>
						<p><?php esc_html_e( 'Automatically keep track of every email sent from your WordPress site and view valuable statistics right here in your dashboard.', 'wp-mail-smtp' ); ?></p>
						<p>
							<a href="<?php echo esc_url( wp_mail_smtp()->get_upgrade_link( [ 'medium' => 'dashboard-widget', 'content' => 'upgrade-to-wp-mail-smtp-pro' ] ) ); // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound ?>" target="_blank" rel="noopener noreferrer" class="button button-primary button-hero">
								<?php esc_html_e( 'Upgrade to WP Mail SMTP Pro', 'wp-mail-smtp' ); ?>
							</a>
						</p>
					</div>
				</div>
				<div class="wp-mail-smtp-dash-widget-overlay"></div>
			</div>
		</div>
		<?php endif; ?>

		<div class="wp-mail-smtp-dash-widget-block wp-mail-smtp-dash-widget-block-settings">
			<div>
				<?php $this->email_types_select_html(); ?>
			</div>
			<div>
				<?php
					$this->timespan_select_html();
					$this->widget_settings_html();
				?>
			</div>
		</div>

		<div id="wp-mail-smtp-dash-widget-email-stats-block" class="wp-mail-smtp-dash-widget-block wp-mail-smtp-dash-widget-email-stats-block">
			<?php $this->email_stats_block(); ?>
		</div>

		<?php
		$this->display_after_email_stats_block_content();
	}

	/**
	 * Display the content after the email stats block.
	 *
	 * @since 3.9.0
	 *
	 * @return void
	 */
	private function display_after_email_stats_block_content() {

		if ( empty( $this->widget_meta( 'get', 'hide_email_alerts_banner' ) ) ) {
			// Check if we have error debug events.
			$error_debug_events_count = DebugEvents::get_error_debug_events_count();

			if ( ! is_wp_error( $error_debug_events_count ) && ! empty( $error_debug_events_count ) ) {
				$this->show_email_alerts_banner( $error_debug_events_count );

				return;
			}
		}

		$hide_summary_report_email_block = (bool) $this->widget_meta( 'get', 'hide_summary_report_email_block' );

		if ( SummaryReportEmail::is_disabled() && ! $hide_summary_report_email_block ) {
			$this->show_summary_report_email_block();
		}

		$this->show_upgrade_footer();
	}

	/**
	 * Display the email alerts banner.
	 *
	 * @since 3.9.0
	 *
	 * @param int $error_count The number of debug events error.
	 *
	 * @return void
	 */
	private function show_email_alerts_banner( $error_count ) {

		?>
		<div id="wp-mail-smtp-dash-widget-email-alerts-education" class="wp-mail-smtp-dash-widget-block wp-mail-smtp-dash-widget-email-alerts-education">
			<div class="wp-mail-smtp-dash-widget-email-alerts-education-error-icon">
				<?php
				printf(
					'<img src="%s" alt="%s"/>',
					esc_url( wp_mail_smtp()->assets_url . '/images/dash-widget/error-icon.svg' ),
					esc_attr__( 'Error icon', 'wp-mail-smtp' )
				);
				?>
			</div>
			<div class="wp-mail-smtp-dash-widget-email-alerts-education-content">
				<?php
				$error_title = sprintf(
					/* translators: %d - number of failed emails. */
					_n(
						'We detected %d failed email in the last 30 days.',
						'We detected %d failed emails in the last 30 days.',
						$error_count,
						'wp-mail-smtp'
					),
					$error_count
				);

				$error_content = sprintf(
					/* translators: %s - URL to WPMailSMTP.com. */
					__( '<a href="%s" target="_blank" rel="noopener noreferrer">Upgrade to Pro</a> and get instant alert notifications when they fail.', 'wp-mail-smtp' ),
					esc_url( wp_mail_smtp()->get_upgrade_link( [ 'medium' => 'dashboard-widget', 'content' => 'alerts-promo-upgrade-to-pro' ] ) ) // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
				);
				?>
				<p>
					<strong><?php echo esc_html( $error_title ); ?></strong><br />
					<?php
					echo wp_kses(
						$error_content,
						[
							'a' => [
								'href'   => [],
								'target' => [],
								'rel'    => [],
							],
						]
					);
					?>
				</p>
			</div>

			<button type="button" id="wp-mail-smtp-dash-widget-dismiss-email-alert-block" class="wp-mail-smtp-dash-widget-dismiss-email-alert-block" title="<?php esc_attr_e( 'Dismiss email alert block', 'wp-mail-smtp' ); ?>">
				<span class="dashicons dashicons-no-alt"></span>
			</button>
		</div>
		<?php
	}

	/**
	 * Show the summary report email block.
	 *
	 * @since 3.9.0
	 *
	 * @return void
	 */
	private function show_summary_report_email_block() {

		?>
		<div id="wp-mail-smtp-dash-widget-summary-report-email-block" class="wp-mail-smtp-dash-widget-block wp-mail-smtp-dash-widget-summary-report-email-block">
			<div>
				<div class="wp-mail-smtp-dash-widget-summary-report-email-block-setting">
					<label for="wp-mail-smtp-dash-widget-summary-report-email-enable">
						<input type="checkbox" id="wp-mail-smtp-dash-widget-summary-report-email-enable">
						<i class="wp-mail-smtp-dash-widget-loader"></i>
						<span>
							<?php
							echo wp_kses(
								__( '<b>NEW!</b> Enable Weekly Email Summaries', 'wp-mail-smtp' ),
								[
									'b' => [],
								]
							);
							?>
						</span>
					</label>
					<a href="<?php echo esc_url( SummaryReportEmail::get_preview_link() ); ?>" target="_blank">
						<?php esc_html_e( 'View Example', 'wp-mail-smtp' ); ?>
					</a>
					<i class="dashicons dashicons-dismiss wp-mail-smtp-dash-widget-summary-report-email-dismiss"></i>
				</div>
				<div class="wp-mail-smtp-dash-widget-summary-report-email-block-applied hidden">
					<i class="wp-mail-smtp-dashicons-yes-alt-green"></i>
					<span><?php esc_attr_e( 'Weekly Email Summaries have been enabled', 'wp-mail-smtp' ); ?></span>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Show the upgrade footer.
	 *
	 * @since 3.9.0
	 *
	 * @return void
	 */
	private function show_upgrade_footer() {

		$hide_graph = (bool) $this->widget_meta( 'get', 'hide_graph' );
		?>
		<div id="wp-mail-smtp-dash-widget-upgrade-footer" class="wp-mail-smtp-dash-widget-block wp-mail-smtp-dash-widget-upgrade-footer wp-mail-smtp-dash-widget-upgrade-footer--<?php echo ! $hide_graph ? 'hide' : 'show'; ?>">
			<p>
				<?php
				printf(
					wp_kses( /* translators: %s - URL to WPMailSMTP.com. */
						__( '<a href="%s" target="_blank" rel="noopener noreferrer">Upgrade to Pro</a> for detailed stats, email logs, and more!', 'wp-mail-smtp' ),
						[
							'a' => [
								'href'   => [],
								'rel'    => [],
								'target' => [],
							],
						]
					),
					// phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
					esc_url( wp_mail_smtp()->get_upgrade_link( [ 'medium' => 'dashboard-widget', 'content' => 'upgrade-to-pro' ] ) )
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Timespan select HTML.
	 *
	 * @since 2.9.0
	 */
	private function timespan_select_html() {

		?>
		<select id="wp-mail-smtp-dash-widget-timespan" class="wp-mail-smtp-dash-widget-select-timespan" title="<?php esc_attr_e( 'Select timespan', 'wp-mail-smtp' ); ?>">
			<option value="all">
				<?php esc_html_e( 'All Time', 'wp-mail-smtp' ); ?>
			</option>
			<?php foreach ( [ 7, 14, 30 ] as $option ) : ?>
				<option value="<?php echo absint( $option ); ?>" disabled>
					<?php /* translators: %d - Number of days. */ ?>
					<?php echo esc_html( sprintf( _n( 'Last %d day', 'Last %d days', absint( $option ), 'wp-mail-smtp' ), absint( $option ) ) ); ?>
				</option>
			<?php endforeach; ?>
		</select>

		<?php
	}

	/**
	 * Email types select HTML.
	 *
	 * @since 2.9.0
	 */
	private function email_types_select_html() {

		$options = [
			'delivered' => esc_html__( 'Confirmed Emails', 'wp-mail-smtp' ),
			'sent'      => esc_html__( 'Unconfirmed Emails', 'wp-mail-smtp' ),
			'unsent'    => esc_html__( 'Failed Emails', 'wp-mail-smtp' ),
		];

		if ( Helpers::mailer_without_send_confirmation() ) {
			unset( $options['sent'] );
			$options['delivered'] = esc_html__( 'Sent Emails', 'wp-mail-smtp' );
		}

		?>
		<select id="wp-mail-smtp-dash-widget-email-type" class="wp-mail-smtp-dash-widget-select-email-type" title="<?php esc_attr_e( 'Select email type', 'wp-mail-smtp' ); ?>">
			<option value="all">
				<?php esc_html_e( 'All Emails', 'wp-mail-smtp' ); ?>
			</option>
			<?php foreach ( $options as $key => $title ) : ?>
				<option value="<?php echo sanitize_key( $key ); ?>" disabled>
					<?php echo esc_html( $title ); ?>
				</option>
			<?php endforeach; ?>
		</select>

		<?php
	}

	/**
	 * Widget settings HTML.
	 *
	 * @since 2.9.0
	 */
	private function widget_settings_html() {

		?>
		<div class="wp-mail-smtp-dash-widget-settings-container">
			<button id="wp-mail-smtp-dash-widget-settings-button" class="wp-mail-smtp-dash-widget-settings-button button" type="button">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 19 19">
					<path d="M18,11l-2.18,0c-0.17,0.7 -0.44,1.35 -0.81,1.93l1.54,1.54l-2.1,2.1l-1.54,-1.54c-0.58,0.36 -1.23,0.63 -1.91,0.79l0,2.18l-3,0l0,-2.18c-0.68,-0.16 -1.33,-0.43 -1.91,-0.79l-1.54,1.54l-2.12,-2.12l1.54,-1.54c-0.36,-0.58 -0.63,-1.23 -0.79,-1.91l-2.18,0l0,-2.97l2.17,0c0.16,-0.7 0.44,-1.35 0.8,-1.94l-1.54,-1.54l2.1,-2.1l1.54,1.54c0.58,-0.37 1.24,-0.64 1.93,-0.81l0,-2.18l3,0l0,2.18c0.68,0.16 1.33,0.43 1.91,0.79l1.54,-1.54l2.12,2.12l-1.54,1.54c0.36,0.59 0.64,1.24 0.8,1.94l2.17,0l0,2.97Zm-8.5,1.5c1.66,0 3,-1.34 3,-3c0,-1.66 -1.34,-3 -3,-3c-1.66,0 -3,1.34 -3,3c0,1.66 1.34,3 3,3Z"></path>
				</svg>
			</button>
			<div class="wp-mail-smtp-dash-widget-settings-menu">
				<div class="wp-mail-smtp-dash-widget-settings-menu--style">
					<h4><?php esc_html_e( 'Graph Style', 'wp-mail-smtp' ); ?></h4>
					<div>
						<div class="wp-mail-smtp-dash-widget-settings-menu-item">
							<input type="radio" id="wp-mail-smtp-dash-widget-settings-style-bar" name="style" value="bar" disabled>
							<label for="wp-mail-smtp-dash-widget-settings-style-bar"><?php esc_html_e( 'Bar', 'wp-mail-smtp' ); ?></label>
						</div>
						<div class="wp-mail-smtp-dash-widget-settings-menu-item">
							<input type="radio" id="wp-mail-smtp-dash-widget-settings-style-line" name="style" value="line" checked disabled>
							<label for="wp-mail-smtp-dash-widget-settings-style-line"><?php esc_html_e( 'Line', 'wp-mail-smtp' ); ?></label>
						</div>
					</div>
				</div>
				<div class="wp-mail-smtp-dash-widget-settings-menu--color">
					<h4><?php esc_html_e( 'Color Scheme', 'wp-mail-smtp' ); ?></h4>
					<div>
						<div class="wp-mail-smtp-dash-widget-settings-menu-item">
							<input type="radio" id="wp-mail-smtp-dash-widget-settings-color-smtp" name="color" value="smtp" disabled>
							<label for="wp-mail-smtp-dash-widget-settings-color-smtp"><?php esc_html_e( 'WP Mail SMTP', 'wp-mail-smtp' ); ?></label>
						</div>
						<div class="wp-mail-smtp-dash-widget-settings-menu-item">
							<input type="radio" id="wp-mail-smtp-dash-widget-settings-color-wp" name="color" value="wp" checked disabled>
							<label for="wp-mail-smtp-dash-widget-settings-color-wp"><?php esc_html_e( 'WordPress', 'wp-mail-smtp' ); ?></label>
						</div>
					</div>
				</div>
				<button type="button" class="button wp-mail-smtp-dash-widget-settings-menu-save" disabled><?php esc_html_e( 'Save Changes', 'wp-mail-smtp' ); ?></button>
			</div>
		</div>
		<?php
	}

	/**
	 * Email statistics block.
	 *
	 * @since 2.9.0
	 */
	private function email_stats_block() {

		$output_data = $this->get_email_stats_data();
		?>

		<table id="wp-mail-smtp-dash-widget-email-stats-table" cellspacing="0">
			<tr>
				<?php
				$count   = 0;
				$per_row = 2;

				foreach ( array_values( $output_data ) as $stats ) :
					if ( ! is_array( $stats ) ) {
						continue;
					}

					if ( ! isset( $stats['icon'], $stats['title'] ) ) {
						continue;
					}

					// Make some exceptions for mailers without send confirmation functionality.
					if ( Helpers::mailer_without_send_confirmation() ) {
						$per_row = 3;
					}

					// Create new row after every $per_row cells.
					if ( $count !== 0 && $count % $per_row === 0 ) {
						echo '</tr><tr>';
					}

					$count++;
					?>
					<td class="wp-mail-smtp-dash-widget-email-stats-table-cell wp-mail-smtp-dash-widget-email-stats-table-cell--<?php echo esc_attr( $stats['type'] ); ?> wp-mail-smtp-dash-widget-email-stats-table-cell--3">
						<div class="wp-mail-smtp-dash-widget-email-stats-table-cell-container">
							<img src="<?php echo esc_url( $stats['icon'] ); ?>" alt="<?php esc_attr_e( 'Table cell icon', 'wp-mail-smtp' ); ?>">
							<span>
								<?php echo esc_html( $stats['title'] ); ?>
							</span>
						</div>
					</td>
				<?php endforeach; ?>
			</tr>
		</table>

		<?php
	}

	/**
	 * Prepare the email stats data.
	 * The text and counts of the email stats.
	 *
	 * @since 2.9.0
	 *
	 * @return array[]
	 */
	private function get_email_stats_data() {

		$reports    = new Reports();
		$total_sent = $reports->get_total_emails_sent();

		$output_data = [
			'all'       => [
				'type'  => 'all',
				'icon'  => wp_mail_smtp()->assets_url . '/images/dash-widget/wp/total.svg',
				/* translators: %d number of total emails sent. */
				'title' => esc_html( sprintf( esc_html__( '%d total', 'wp-mail-smtp' ), $total_sent ) ),
			],
			'delivered' => [
				'type'  => 'delivered',
				'icon'  => wp_mail_smtp()->assets_url . '/images/dash-widget/wp/delivered.svg',
				/* translators: %s fixed string of 'N/A'. */
				'title' => esc_html( sprintf( esc_html__( 'Confirmed %s', 'wp-mail-smtp' ), 'N/A' ) ),
			],
			'sent'      => [
				'type'  => 'sent',
				'icon'  => wp_mail_smtp()->assets_url . '/images/dash-widget/wp/sent.svg',
				/* translators: %s fixed string of 'N/A'. */
				'title' => esc_html( sprintf( esc_html__( 'Unconfirmed %s', 'wp-mail-smtp' ), 'N/A' ) ),
			],
			'unsent'    => [
				'type'  => 'unsent',
				'icon'  => wp_mail_smtp()->assets_url . '/images/dash-widget/wp/unsent.svg',
				/* translators: %s fixed string of 'N/A'. */
				'title' => esc_html( sprintf( esc_html__( 'Failed %s', 'wp-mail-smtp' ), 'N/A' ) ),
			],
		];

		if ( Helpers::mailer_without_send_confirmation() ) {

			// Skip the 'unconfirmed sent' section.
			unset( $output_data['sent'] );

			// Change the 'confirmed sent' section into a general 'sent' section.
			$output_data['delivered']['title'] = esc_html( /* translators: %s fixed string of 'N/A'. */
				sprintf( esc_html__( 'Sent %s', 'wp-mail-smtp' ), 'N/A' )
			);
		}

		return $output_data;
	}

	/**
	 * Get/set a widget meta.
	 *
	 * @since 2.9.0
	 *
	 * @param string $action Possible value: 'get' or 'set'.
	 * @param string $meta   Meta name.
	 * @param int    $value  Value to set.
	 *
	 * @return mixed
	 */
	protected function widget_meta( $action, $meta, $value = 0 ) {

		$allowed_actions = [ 'get', 'set' ];

		if ( ! in_array( $action, $allowed_actions, true ) ) {
			return false;
		}

		if ( $action === 'get' ) {
			return $this->get_widget_meta( $meta );
		}

		$meta_key = $this->get_widget_meta_key( $meta );
		$value    = sanitize_key( $value );

		if ( 'set' === $action && ! empty( $value ) ) {
			return update_user_meta( get_current_user_id(), $meta_key, $value );
		}

		if ( 'set' === $action && empty( $value ) ) {
			return delete_user_meta( get_current_user_id(), $meta_key );
		}

		return false;
	}

	/**
	 * Get the widget meta value.
	 *
	 * @since 3.9.0
	 *
	 * @param string $meta Meta name.
	 *
	 * @return mixed
	 */
	private function get_widget_meta( $meta ) {

		$defaults = [
			'hide_graph'                      => 0,
			'hide_summary_report_email_block' => 0,
			'hide_email_alerts_banner'        => 0,
		];

		$meta_value = get_user_meta( get_current_user_id(), $this->get_widget_meta_key( $meta ), true );

		if ( ! empty( $meta_value ) ) {
			return $meta_value;
		}

		if ( isset( $defaults[ $meta ] ) ) {
			return $defaults[ $meta ];
		}

		return null;
	}

	/**
	 * Retrieve the meta key.
	 *
	 * @since 3.9.0
	 *
	 * @param string $meta Meta name.
	 *
	 * @return string
	 */
	private function get_widget_meta_key( $meta ) {

		return 'wp_mail_smtp_' . static::SLUG . '_' . $meta;
	}
}
