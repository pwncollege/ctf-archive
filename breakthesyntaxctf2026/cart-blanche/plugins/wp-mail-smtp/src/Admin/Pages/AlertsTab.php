<?php

namespace WPMailSMTP\Admin\Pages;

use WPMailSMTP\Admin\PageAbstract;
use WPMailSMTP\Helpers\UI;

/**
 * Class AlertsTab is a placeholder for Pro alerts feature.
 * Displays product education.
 *
 * @since 3.5.0
 */
class AlertsTab extends PageAbstract {

	/**
	 * Part of the slug of a tab.
	 *
	 * @since 3.5.0
	 *
	 * @var string
	 */
	protected $slug = 'alerts';

	/**
	 * Tab priority.
	 *
	 * @since 3.5.0
	 *
	 * @var int
	 */
	protected $priority = 20;

	/**
	 * Link label of a tab.
	 *
	 * @since 3.5.0
	 *
	 * @return string
	 */
	public function get_label() {

		return esc_html__( 'Alerts', 'wp-mail-smtp' );
	}

	/**
	 * Title of a tab.
	 *
	 * @since 3.5.0
	 *
	 * @return string
	 */
	public function get_title() {

		return $this->get_label();
	}

	/**
	 * Output HTML of the alerts settings preview.
	 *
	 * @since 3.5.0
	 */
	public function display() {

		$top_upgrade_button_url    = wp_mail_smtp()->get_upgrade_link(
			[
				'medium'  => 'Alerts Settings',
				'content' => 'Upgrade to WP Mail SMTP Pro Button Top',
			]
		);
		$bottom_upgrade_button_url = wp_mail_smtp()->get_upgrade_link(
			[
				'medium'  => 'Alerts Settings',
				'content' => 'Upgrade to WP Mail SMTP Pro Button',
			]
		);
		?>
		<div class="wp-mail-smtp-product-education">
			<div class="wp-mail-smtp-product-education__row">
				<h4 class="wp-mail-smtp-product-education__heading">
					<?php esc_html_e( 'Alerts', 'wp-mail-smtp' ); ?>
				</h4>
				<p class="wp-mail-smtp-product-education__description">
					<?php
					esc_html_e( 'Configure at least one of these integrations to receive notifications when email fails to send from your site. Alert notifications will contain the following important data: email subject, email Send To address, the error message, and helpful links to help you fix the issue.', 'wp-mail-smtp' );
					?>
				</p>

				<a href="<?php echo esc_url( $top_upgrade_button_url ); ?>" target="_blank" rel="noopener noreferrer" class="wp-mail-smtp-product-education__upgrade-btn wp-mail-smtp-product-education__upgrade-btn--top wp-mail-smtp-btn wp-mail-smtp-btn-upgrade wp-mail-smtp-btn-orange">
					<?php esc_html_e( 'Upgrade to WP Mail SMTP Pro', 'wp-mail-smtp' ); ?>
				</a>
			</div>

			<div class="wp-mail-smtp-product-education__row wp-mail-smtp-product-education__row--inactive">
				<div id="wp-mail-smtp-setting-row-alert_event_types" class="wp-mail-smtp-setting-row wp-mail-smtp-clear">
					<div class="wp-mail-smtp-setting-label">
						<label for="wp-mail-smtp-setting-debug_event_types">
							<?php esc_html_e( 'Notify when', 'wp-mail-smtp' ); ?>
						</label>
					</div>
					<div class="wp-mail-smtp-setting-field">
						<?php
						UI::toggle(
							[
								'label'    => esc_html__( 'The initial email sending request fails', 'wp-mail-smtp' ),
								'checked'  => true,
								'disabled' => true,
							]
						);
						?>
						<p class="desc">
							<?php esc_html_e( 'This option is always enabled and will notify you about instant email sending failures.', 'wp-mail-smtp' ); ?>
						</p>
						<hr class="wp-mail-smtp-setting-mid-row-sep">

						<?php
						UI::toggle(
							[
								'label'    => esc_html__( 'The deliverability verification process detects a hard bounce', 'wp-mail-smtp' ),
								'disabled' => true,
							]
						);
						?>
						<p class="desc">
							<?php esc_html_e( 'Get notified about emails that were successfully sent, but have hard bounced on delivery attempt. A hard bounce is an email that has failed to deliver for permanent reasons, such as the recipient\'s email address being invalid.', 'wp-mail-smtp' ); ?>
						</p>
					</div>
				</div>
			</div>

			<div class="wp-mail-smtp-product-education__row wp-mail-smtp-product-education__row--inactive">
				<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-alert">
					<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-content section-heading">
						<div class="wp-mail-smtp-setting-field">
							<h3><?php esc_html_e( 'Email', 'wp-mail-smtp' ); ?></h3>
							<p class="desc"><?php esc_html_e( 'Enter the email addresses (3 max) you’d like to use to receive alerts when email sending fails. Read our documentation on setting up email alerts.', 'wp-mail-smtp' ); ?></p>
						</div>
					</div>
					<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-checkbox-toggle">
						<div class="wp-mail-smtp-setting-label">
							<label><?php esc_html_e( 'Email Alerts', 'wp-mail-smtp' ); ?></label>
						</div>
						<div class="wp-mail-smtp-setting-field">
							<?php
							UI::toggle();
							?>
						</div>
					</div>
					<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-alert-options">
						<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-alert-connection-options">
							<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-text">
								<div class="wp-mail-smtp-setting-label">
									<label><?php esc_html_e( 'Send To', 'wp-mail-smtp' ); ?></label>
								</div>
								<div class="wp-mail-smtp-setting-field"><input type="text"></div>
							</div>
						</div>
					</div>
				</div>

				<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-alert">
					<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-content section-heading">
						<div class="wp-mail-smtp-setting-field">
							<h3><?php esc_html_e( 'Slack', 'wp-mail-smtp' ); ?></h3>
							<p class="desc"><?php esc_html_e( 'Paste in the Slack webhook URL you’d like to use to receive alerts when email sending fails. Read our documentation on setting up Slack alerts.', 'wp-mail-smtp' ); ?></p>
						</div>
					</div>
					<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-checkbox-toggle">
						<div class="wp-mail-smtp-setting-label">
							<label><?php esc_html_e( 'Slack Alerts', 'wp-mail-smtp' ); ?></label>
						</div>
						<div class="wp-mail-smtp-setting-field">
							<?php
							UI::toggle();
							?>
						</div>
					</div>
					<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-alert-options">
						<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-alert-connection-options">
							<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-text">
								<div class="wp-mail-smtp-setting-label">
									<label><?php esc_html_e( 'Webhook URL', 'wp-mail-smtp' ); ?></label>
								</div>
								<div class="wp-mail-smtp-setting-field"><input type="text"></div>
							</div>
						</div>
					</div>
				</div>

				<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-alert">
					<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-content section-heading">
						<div class="wp-mail-smtp-setting-field">
							<h3><?php esc_html_e( 'Discord', 'wp-mail-smtp' ); ?></h3>
							<p class="desc"><?php esc_html_e( 'Paste in the Discord webhook URL you’d like to use to receive alerts when email sending fails. Read our documentation on setting up Discord alerts.', 'wp-mail-smtp' ); ?></p>
						</div>
					</div>
					<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-checkbox-toggle">
						<div class="wp-mail-smtp-setting-label">
							<label><?php esc_html_e( 'Discord Alerts', 'wp-mail-smtp' ); ?></label>
						</div>
						<div class="wp-mail-smtp-setting-field">
							<?php
							UI::toggle();
							?>
						</div>
					</div>
					<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-alert-options">
						<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-alert-connection-options">
							<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-text">
								<div class="wp-mail-smtp-setting-label">
									<label><?php esc_html_e( 'Webhook URL', 'wp-mail-smtp' ); ?></label>
								</div>
								<div class="wp-mail-smtp-setting-field"><input type="text"></div>
							</div>
						</div>
					</div>
				</div>

				<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-alert">
					<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-content section-heading">
						<div class="wp-mail-smtp-setting-field">
							<h3><?php esc_html_e( 'Microsoft Teams', 'wp-mail-smtp' ); ?></h3>
							<p class="desc"><?php esc_html_e( 'Paste in the Microsoft Teams webhook URL you’d like to use to receive alerts when email sending fails. Read our documentation on setting up Microsoft Teams alerts.', 'wp-mail-smtp' ); ?></p>
						</div>
					</div>
					<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-checkbox-toggle">
						<div class="wp-mail-smtp-setting-label">
							<label><?php esc_html_e( 'Microsoft Teams Alerts', 'wp-mail-smtp' ); ?></label>
						</div>
						<div class="wp-mail-smtp-setting-field">
							<?php
							UI::toggle();
							?>
						</div>
					</div>
					<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-alert-options">
						<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-alert-connection-options">
							<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-text">
								<div class="wp-mail-smtp-setting-label">
									<label><?php esc_html_e( 'Webhook URL', 'wp-mail-smtp' ); ?></label>
								</div>
								<div class="wp-mail-smtp-setting-field"><input type="text"></div>
							</div>
						</div>
					</div>
				</div>

				<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-alert">
					<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-content section-heading">
						<div class="wp-mail-smtp-setting-field">
							<h3><?php esc_html_e( 'SMS via Twilio', 'wp-mail-smtp' ); ?></h3>
							<p class="desc"><?php esc_html_e( 'To receive SMS alerts, you’ll need a Twilio account. Read our documentation to learn how to set up Twilio SMS, then enter your connection details below.', 'wp-mail-smtp' ); ?></p>
						</div>
					</div>
					<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-checkbox-toggle">
						<div class="wp-mail-smtp-setting-label">
							<label><?php esc_html_e( 'SMS via Twilio Alerts', 'wp-mail-smtp' ); ?></label>
						</div>
						<div class="wp-mail-smtp-setting-field">
							<?php
							UI::toggle();
							?>
						</div>
					</div>
					<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-alert-options">
						<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-alert-connection-options">
							<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-text">
								<div class="wp-mail-smtp-setting-label">
									<label><?php esc_html_e( 'Twilio Account ID', 'wp-mail-smtp' ); ?></label>
								</div>
								<div class="wp-mail-smtp-setting-field"><input type="text"></div>
							</div>
							<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-text">
								<div class="wp-mail-smtp-setting-label">
									<label><?php esc_html_e( 'Twilio Auth Token', 'wp-mail-smtp' ); ?></label>
								</div>
								<div class="wp-mail-smtp-setting-field"><input type="text"></div>
							</div>
							<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-text">
								<div class="wp-mail-smtp-setting-label">
									<label><?php esc_html_e( 'From Phone Number', 'wp-mail-smtp' ); ?></label>
								</div>
								<div class="wp-mail-smtp-setting-field"><input type="text"></div>
							</div>
							<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-text">
								<div class="wp-mail-smtp-setting-label">
									<label><?php esc_html_e( 'To Phone Number', 'wp-mail-smtp' ); ?></label>
								</div>
								<div class="wp-mail-smtp-setting-field"><input type="text"></div>
							</div>
						</div>
					</div>
				</div>

				<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-alert">
					<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-content section-heading">
						<div class="wp-mail-smtp-setting-field">
							<h3><?php esc_html_e( 'Webhook', 'wp-mail-smtp' ); ?></h3>
							<p class="desc"><?php esc_html_e( 'Paste in the webhook URL you’d like to use to receive alerts when email sending fails. Read our documentation on setting up webhook alerts.', 'wp-mail-smtp' ); ?></p>
						</div>
					</div>
					<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-checkbox-toggle">
						<div class="wp-mail-smtp-setting-label">
							<label><?php esc_html_e( 'Webhook Alerts', 'wp-mail-smtp' ); ?></label>
						</div>
						<div class="wp-mail-smtp-setting-field">
							<?php
							UI::toggle();
							?>
						</div>
					</div>
					<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-alert-options">
						<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-alert-connection-options">
							<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-text">
								<div class="wp-mail-smtp-setting-label">
									<label><?php esc_html_e( 'Webhook URL', 'wp-mail-smtp' ); ?></label>
								</div>
								<div class="wp-mail-smtp-setting-field"><input type="text"></div>
							</div>
						</div>
					</div>
				</div>

				<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-alert">
					<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-content section-heading">
						<div class="wp-mail-smtp-setting-field">
							<h3><?php esc_html_e( 'Push Notifications', 'wp-mail-smtp' ); ?></h3>
							<p class="desc"><?php esc_html_e( 'To receive push notifications on this device, you\'ll need to allow our plugin to send notifications via this browser. Read our documentation on setting up Push Notification alerts.', 'wp-mail-smtp' ); ?></p>
						</div>
					</div>
					<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-checkbox-toggle">
						<div class="wp-mail-smtp-setting-label">
							<label><?php esc_html_e( 'Push Notification Alerts', 'wp-mail-smtp' ); ?></label>
						</div>
						<div class="wp-mail-smtp-setting-field">
							<?php
							UI::toggle();
							?>
						</div>
					</div>
					<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-alert-options">
						<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-alert-connection-options">
							<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-text">
								<div class="wp-mail-smtp-setting-label">
									<label><?php esc_html_e( 'Connection Name', 'wp-mail-smtp' ); ?></label>
								</div>
								<div class="wp-mail-smtp-setting-field"><input type="text"></div>
							</div>
						</div>
					</div>
				</div>

				<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-alert">
					<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-content section-heading">
						<div class="wp-mail-smtp-setting-field">
							<h3><?php esc_html_e( 'WhatsApp', 'wp-mail-smtp' ); ?></h3>
							<p class="desc"><?php esc_html_e( 'Enter your WhatsApp Cloud API credentials to receive alerts when email sending fails. You\'ll need to create a Meta developer account and set up WhatsApp Business Platform.', 'wp-mail-smtp' ); ?></p>
						</div>
					</div>
					<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-checkbox-toggle">
						<div class="wp-mail-smtp-setting-label">
							<label><?php esc_html_e( 'WhatsApp Alerts', 'wp-mail-smtp' ); ?></label>
						</div>
						<div class="wp-mail-smtp-setting-field">
							<?php
							UI::toggle();
							?>
						</div>
					</div>
					<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-alert-options">
						<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-alert-connection-options">
							<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-text">
								<div class="wp-mail-smtp-setting-label">
									<label><?php esc_html_e( 'Access Token', 'wp-mail-smtp' ); ?></label>
								</div>
								<div class="wp-mail-smtp-setting-field"><input type="password"></div>
							</div>
							<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-text">
								<div class="wp-mail-smtp-setting-label">
									<label><?php esc_html_e( 'WhatsApp Business Account ID', 'wp-mail-smtp' ); ?></label>
								</div>
								<div class="wp-mail-smtp-setting-field"><input type="text"></div>
							</div>
							<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-text">
								<div class="wp-mail-smtp-setting-label">
									<label><?php esc_html_e( 'Phone Number ID', 'wp-mail-smtp' ); ?></label>
								</div>
								<div class="wp-mail-smtp-setting-field"><input type="text"></div>
							</div>
							<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-text">
								<div class="wp-mail-smtp-setting-label">
									<label><?php esc_html_e( 'To Phone Number', 'wp-mail-smtp' ); ?></label>
								</div>
								<div class="wp-mail-smtp-setting-field"><input type="text"></div>
							</div>
						</div>
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
