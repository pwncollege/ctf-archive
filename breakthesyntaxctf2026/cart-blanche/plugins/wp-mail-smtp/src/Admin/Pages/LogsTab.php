<?php

namespace WPMailSMTP\Admin\Pages;

use WPMailSMTP\Admin\PageAbstract;
use WPMailSMTP\Admin\ParentPageAbstract;

/**
 * Class LogsTab is a placeholder for Lite users and redirects them to Email Log page.
 *
 * @since 1.6.0
 */
class LogsTab extends PageAbstract {

	/**
	 * Part of the slug of a tab.
	 *
	 * @since 1.6.0
	 *
	 * @var string
	 */
	protected $slug = 'logs';

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param ParentPageAbstract $parent_page Tab parent page.
	 */
	public function __construct( $parent_page = null ) {

		parent::__construct( $parent_page );

		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( wp_mail_smtp()->get_admin()->is_admin_page() && $current_tab === 'logs' ) {
			$this->hooks();
		}
	}

	/**
	 * Link label of a tab.
	 *
	 * @since 1.6.0
	 *
	 * @return string
	 */
	public function get_label() {

		return esc_html__( 'Email Log', 'wp-mail-smtp' );
	}

	/**
	 * Title of a tab.
	 *
	 * @since 1.6.0
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

		wp_enqueue_style(
			'wp-mail-smtp-admin-lity',
			wp_mail_smtp()->assets_url . '/css/vendor/lity.min.css',
			[],
			'2.4.1'
		);
		wp_enqueue_script(
			'wp-mail-smtp-admin-lity',
			wp_mail_smtp()->assets_url . '/js/vendor/lity.min.js',
			[],
			'2.4.1',
			false
		);
	}

	/**
	 * Display the upsell content for the Email Log feature.
	 *
	 * @since 1.6.0
	 * @since 2.1.0 Moved the display content from the email log page (WP admin menu "Email Log" page).
	 */
	public function display() {

		$top_upgrade_button_url    = add_query_arg(
			[ 'discount' => 'LITEUPGRADE' ],
			wp_mail_smtp()->get_upgrade_link(
				[
					'medium'  => 'logs',
					'content' => 'Upgrade to Pro Button Top',
				]
			)
		);
		$bottom_upgrade_button_url = add_query_arg(
			[ 'discount' => 'LITEUPGRADE' ],
			wp_mail_smtp()->get_upgrade_link(
				[
					'medium'  => 'logs',
					'content' => 'Upgrade to Pro Button',
				]
			)
		);

		$assets_url  = wp_mail_smtp()->assets_url . '/images/logs/';
		$screenshots = [
			[
				'url'           => $assets_url . 'archive.png',
				'url_thumbnail' => $assets_url . 'archive-thumbnail.png',
				'title'         => __( 'Email Log Index', 'wp-mail-smtp' ),
			],
			[
				'url'           => $assets_url . 'single.png',
				'url_thumbnail' => $assets_url . 'single-thumbnail.png',
				'title'         => __( 'Individual Email Log', 'wp-mail-smtp' ),
			],
		];
		?>

		<div id="wp-mail-smtp-email-logs-product-education" class="wp-mail-smtp-product-education">
			<div class="wp-mail-smtp-product-education__row">
				<h4 class="wp-mail-smtp-product-education__heading">
					<?php esc_html_e( 'Email Log', 'wp-mail-smtp' ); ?>
				</h4>
				<p class="wp-mail-smtp-product-education__description">
					<?php
					esc_html_e( 'Email logging makes it easy to save details about all of the emails sent from your WordPress site. You can search and filter the email log to find specific messages and check the color-coded delivery status. Email logging also allows you to resend emails, save attachments, and export your logs in different formats.', 'wp-mail-smtp' );
					?>
				</p>

				<a href="<?php echo esc_url( $top_upgrade_button_url ); ?>" target="_blank" rel="noopener noreferrer" class="wp-mail-smtp-product-education__upgrade-btn wp-mail-smtp-product-education__upgrade-btn--top wp-mail-smtp-btn wp-mail-smtp-btn-upgrade wp-mail-smtp-btn-orange">
					<?php esc_html_e( 'Upgrade to WP Mail SMTP Pro', 'wp-mail-smtp' ); ?>
				</a>
			</div>

			<div class="wp-mail-smtp-product-education__row">
				<div class="wp-mail-smtp-product-education__screenshots wp-mail-smtp-product-education__screenshots--two">
					<?php foreach ( $screenshots as $screenshot ) : ?>
						<div>
							<a href="<?php echo esc_url( $screenshot['url'] ); ?>" data-lity data-lity-desc="<?php echo esc_attr( $screenshot['title'] ); ?>">
								<img src="<?php echo esc_url( $screenshot['url_thumbnail'] ); ?>" alt="<?php esc_attr( $screenshot['title'] ); ?>">
							</a>
							<span><?php echo esc_html( $screenshot['title'] ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="wp-mail-smtp-product-education__row">
				<div class="wp-mail-smtp-product-education__list">
					<h4><?php esc_html_e( 'Unlock these awesome logging features:', 'wp-mail-smtp' ); ?></h4>
					<div>
						<ul>
							<li><?php esc_html_e( 'Save detailed email headers', 'wp-mail-smtp' ); ?></li>
							<li><?php esc_html_e( 'See sent and failed emails', 'wp-mail-smtp' ); ?></li>
						</ul>
						<ul>
							<li><?php esc_html_e( 'Resend emails and attachments', 'wp-mail-smtp' ); ?></li>
							<li><?php esc_html_e( 'Track email opens and clicks', 'wp-mail-smtp' ); ?></li>
						</ul>
						<ul>
							<li><?php esc_html_e( 'Print email logs or save as PDF', 'wp-mail-smtp' ); ?></li>
							<li><?php esc_html_e( 'Export logs to CSV, XLSX, or EML', 'wp-mail-smtp' ); ?></li>
						</ul>
					</div>
				</div>
			</div>

			<a href="<?php echo esc_url( $bottom_upgrade_button_url ); ?>" target="_blank" rel="noopener noreferrer" class="wp-mail-smtp-product-education__upgrade-btn wp-mail-smtp-product-education__upgrade-btn--bottom wp-mail-smtp-btn wp-mail-smtp-btn-upgrade wp-mail-smtp-btn-orange">
				<?php esc_html_e( 'Upgrade to WP Mail SMTP Pro', 'wp-mail-smtp' ); ?>
			</a>
		</div>
		<?php
	}

	/**
	 * Not used as we are simply redirecting users.
	 *
	 * @since 1.6.0
	 *
	 * @param array $data Post data specific for the plugin.
	 */
	public function process_post( $data ) { }
}
