<?php

namespace WPMailSMTP\Admin\Pages;

use WPMailSMTP\Admin\PageAbstract;

/**
 * Class ExportTab is a placeholder for Pro email logs export.
 * Displays product education.
 *
 * @since 2.9.0
 */
class ExportTab extends PageAbstract {

	/**
	 * Part of the slug of a tab.
	 *
	 * @since 2.9.0
	 *
	 * @var string
	 */
	protected $slug = 'export';

	/**
	 * Tab priority.
	 *
	 * @since 2.9.0
	 *
	 * @var int
	 */
	protected $priority = 20;

	/**
	 * Link label of a tab.
	 *
	 * @since 2.9.0
	 *
	 * @return string
	 */
	public function get_label() {

		return esc_html__( 'Export', 'wp-mail-smtp' );
	}

	/**
	 * Title of a tab.
	 *
	 * @since 2.9.0
	 *
	 * @return string
	 */
	public function get_title() {

		return $this->get_label();
	}

	/**
	 * Output HTML of the email logs export form preview.
	 *
	 * @since 2.9.0
	 */
	public function display() {

		$top_upgrade_button_url    = wp_mail_smtp()->get_upgrade_link(
			[
				'medium'  => 'tools-export',
				'content' => 'upgrade-to-wp-mail-smtp-pro-button-top',
			]
		);
		$bottom_upgrade_button_url = wp_mail_smtp()->get_upgrade_link(
			[
				'medium'  => 'tools-export',
				'content' => 'upgrade-to-wp-mail-smtp-pro-button',
			]
		);
		?>
		<div id="wp-mail-smtp-tools-export-email-logs-product-education" class="wp-mail-smtp-product-education">
			<div class="wp-mail-smtp-product-education__row">
				<h4 class="wp-mail-smtp-product-education__heading">
					<?php esc_html_e( 'Export Email Logs', 'wp-mail-smtp' ); ?>
				</h4>
				<p class="wp-mail-smtp-product-education__description">
					<?php
					esc_html_e( 'Easily export your logs to CSV or Excel. Filter the logs before you export and only download the data you need. This feature lets you easily create your own deliverability reports. You can also use the data in 3rd party dashboards to track deliverability along with your other website statistics.', 'wp-mail-smtp' );
					?>
				</p>

				<a href="<?php echo esc_url( $top_upgrade_button_url ); ?>" target="_blank" rel="noopener noreferrer" class="wp-mail-smtp-product-education__upgrade-btn wp-mail-smtp-product-education__upgrade-btn--top wp-mail-smtp-btn wp-mail-smtp-btn-upgrade wp-mail-smtp-btn-orange">
					<?php esc_html_e( 'Upgrade to WP Mail SMTP Pro', 'wp-mail-smtp' ); ?>
				</a>
			</div>

			<div class="wp-mail-smtp-product-education__row wp-mail-smtp-product-education__row--inactive">
				<section class="wp-clearfix">
					<h5><?php esc_html_e( 'Export Type', 'wp-mail-smtp' ); ?></h5>
					<label>
						<input type="radio" checked><?php esc_html_e( 'Export in CSV (.csv)', 'wp-mail-smtp' ); ?>
					</label>
					<label>
						<input type="radio"><?php esc_html_e( 'Export in Microsoft Excel (.xlsx)', 'wp-mail-smtp' ); ?>
					</label>
					<label>
						<input type="radio"><?php esc_html_e( 'Export in EML (.eml)', 'wp-mail-smtp' ); ?>
					</label>
				</section>

				<section class="wp-clearfix">
					<h5><?php esc_html_e( 'Common Information', 'wp-mail-smtp' ); ?></h5>
					<label><input type="checkbox" checked><?php esc_html_e( 'To Address', 'wp-mail-smtp' ); ?></label>
					<label><input type="checkbox" checked><?php esc_html_e( 'From Address', 'wp-mail-smtp' ); ?></label>
					<label><input type="checkbox" checked><?php esc_html_e( 'From Name', 'wp-mail-smtp' ); ?></label>
					<label><input type="checkbox" checked><?php esc_html_e( 'Subject', 'wp-mail-smtp' ); ?></label>
					<label><input type="checkbox" checked><?php esc_html_e( 'Body', 'wp-mail-smtp' ); ?></label>
					<label><input type="checkbox" checked><?php esc_html_e( 'Created Date', 'wp-mail-smtp' ); ?></label>
					<label><input type="checkbox" checked><?php esc_html_e( 'Number of Attachments', 'wp-mail-smtp' ); ?></label>
					<label><input type="checkbox" checked><?php esc_html_e( 'Attachments', 'wp-mail-smtp' ); ?></label>
				</section>

				<section class="wp-clearfix">
					<h5><?php esc_html_e( 'Additional Information', 'wp-mail-smtp' ); ?></h5>
					<label><input type="checkbox"><?php esc_html_e( 'Status', 'wp-mail-smtp' ); ?></label>
					<label><input type="checkbox"><?php esc_html_e( 'Carbon Copy (CC)', 'wp-mail-smtp' ); ?></label>
					<label><input type="checkbox"><?php esc_html_e( 'Blind Carbon Copy (BCC)', 'wp-mail-smtp' ); ?></label>
					<label><input type="checkbox"><?php esc_html_e( 'Headers', 'wp-mail-smtp' ); ?></label>
					<label><input type="checkbox"><?php esc_html_e( 'Mailer', 'wp-mail-smtp' ); ?></label>
					<label><input type="checkbox"><?php esc_html_e( 'Error Details', 'wp-mail-smtp' ); ?></label>
					<label><input type="checkbox"><?php esc_html_e( 'Email log ID', 'wp-mail-smtp' ); ?></label>
					<label><input type="checkbox"><?php esc_html_e( 'Source', 'wp-mail-smtp' ); ?></label>
				</section>

				<section class="wp-clearfix">
					<h5><?php esc_html_e( 'Custom Date Range', 'wp-mail-smtp' ); ?></h5>
					<input type="text" class="wp-mail-smtp-date-selector" placeholder="<?php esc_html_e( 'Select a date range', 'wp-mail-smtp' ); ?>">
				</section>

				<section class="wp-clearfix">
					<h5><?php esc_html_e( 'Search', 'wp-mail-smtp' ); ?></h5>
					<select class="wp-mail-smtp-search-box-field">
						<option><?php esc_html_e( 'Email Addresses', 'wp-mail-smtp' ); ?></option>
					</select>
					<input type="text" class="wp-mail-smtp-search-box-term">
				</section>
			</div>

			<a href="<?php echo esc_url( $bottom_upgrade_button_url ); ?>" target="_blank" rel="noopener noreferrer" class="wp-mail-smtp-product-education__upgrade-btn wp-mail-smtp-product-education__upgrade-btn--bottom wp-mail-smtp-btn wp-mail-smtp-btn-upgrade wp-mail-smtp-btn-orange">
				<?php esc_html_e( 'Upgrade to WP Mail SMTP Pro', 'wp-mail-smtp' ); ?>
			</a>
		</div>
		<?php
	}
}
