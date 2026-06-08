<?php

namespace WPMailSMTP\Admin\Pages;

use WPMailSMTP\Admin\PageAbstract;

/**
 * Class EmailTrackingReportsTab is a placeholder for Pro email tracking reports.
 * Displays product education.
 *
 * @since 3.0.0
 */
class EmailReportsTab extends PageAbstract {

	/**
	 * Part of the slug of a tab.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	protected $slug = 'reports';

	/**
	 * Tab priority.
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	protected $priority = 10;

	/**
	 * Link label of a tab.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_label() {

		return esc_html__( 'Email Reports', 'wp-mail-smtp' );
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
	 * Output HTML of the email reports education.
	 *
	 * @since 3.0.0
	 */
	public function display() {

		$top_upgrade_button_url    = wp_mail_smtp()->get_upgrade_link(
			[
				'medium'  => 'email-reports',
				'content' => 'upgrade-to-wp-mail-smtp-pro-button-link-top',
			]
		);
		$bottom_upgrade_button_url = wp_mail_smtp()->get_upgrade_link(
			[
				'medium'  => 'email-reports',
				'content' => 'upgrade-to-wp-mail-smtp-pro-button-link',
			]
		);

		$assets_url  = wp_mail_smtp()->assets_url . '/images/email-reports/';
		$screenshots = [
			[
				'url'           => $assets_url . 'screenshot-01.png',
				'url_thumbnail' => $assets_url . 'thumbnail-01.png',
				'title'         => __( 'Stats at a Glance', 'wp-mail-smtp' ),
			],
			[
				'url'           => $assets_url . 'screenshot-02.png',
				'url_thumbnail' => $assets_url . 'thumbnail-02.png',
				'title'         => __( 'Detailed Stats by Subject Line', 'wp-mail-smtp' ),
			],
			[
				'url'           => $assets_url . 'screenshot-03.png',
				'url_thumbnail' => $assets_url . 'thumbnail-03.png',
				'title'         => __( 'Weekly Email Report', 'wp-mail-smtp' ),
			],
		];
		?>
		<div id="wp-mail-smtp-email-reports-product-education" class="wp-mail-smtp-product-education">
			<div class="wp-mail-smtp-product-education__row">
				<p class="wp-mail-smtp-product-education__description">
					<?php
					esc_html_e( 'Email reports make it easy to track deliverability and engagement at-a-glance. Your open and click-through rates are grouped by subject line, making it easy to review the performance of campaigns or notifications. The report also displays Sent and Failed emails each week so you spot any issues quickly. When you upgrade, we\'ll also add an email report chart right in your WordPress dashboard.', 'wp-mail-smtp' );
					?>
				</p>

				<a href="<?php echo esc_url( $top_upgrade_button_url ); ?>" target="_blank" rel="noopener noreferrer" class="wp-mail-smtp-product-education__upgrade-btn wp-mail-smtp-product-education__upgrade-btn--top wp-mail-smtp-btn wp-mail-smtp-btn-upgrade wp-mail-smtp-btn-orange">
					<?php esc_html_e( 'Upgrade to WP Mail SMTP Pro', 'wp-mail-smtp' ); ?>
				</a>
			</div>

			<div class="wp-mail-smtp-product-education__row">
				<div class="wp-mail-smtp-product-education__screenshots wp-mail-smtp-product-education__screenshots--three">
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
					<h4><?php esc_html_e( 'Unlock these awesome reporting features:', 'wp-mail-smtp' ); ?></h4>
					<div>
						<ul>
							<li><?php esc_html_e( 'Get weekly deliverability reports', 'wp-mail-smtp' ); ?></li>
							<li><?php esc_html_e( 'View stats grouped by subject line', 'wp-mail-smtp' ); ?></li>
						</ul>
						<ul>
							<li><?php esc_html_e( 'Track total emails sent each week', 'wp-mail-smtp' ); ?></li>
							<li><?php esc_html_e( 'Measure open rate and click through rates', 'wp-mail-smtp' ); ?></li>
						</ul>
						<ul>
							<li><?php esc_html_e( 'Spot failed emails quickly', 'wp-mail-smtp' ); ?></li>
							<li><?php esc_html_e( 'See email report graphs in WordPress', 'wp-mail-smtp' ); ?></li>
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
}
