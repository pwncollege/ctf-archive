<?php

namespace WPMailSMTP\Reports\Emails;

use WPMailSMTP\Admin\Area;
use WPMailSMTP\Options;
use WPMailSMTP\Reports\Reports;

/**
 * Class Summary. Summary report email.
 *
 * @since 3.0.0
 */
class Summary {

	/**
	 * The slug that will be used to save the option of summary report email.
	 *
	 * @since 3.0.0
	 */
	const SETTINGS_SLUG = 'summary_report_email_disabled';

	/**
	 * Whether summary report email is disabled.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	public static function is_disabled() {

		$value = Options::init()->get( 'general', self::SETTINGS_SLUG );

		// If option was not already set, then plugin was updated from lower version.
		if (
			( $value === '' || $value === null ) &&
			( is_multisite() || ! wp_mail_smtp()->is_pro() )
		) {
			$value = true;
		}

		/**
		 * Filters whether summary report email is disabled.
		 *
		 * @since 3.0.0
		 *
		 * @param bool $is_disabled
		 */
		$value = apply_filters( 'wp_mail_smtp_reports_emails_summary_is_disabled', $value );

		return (bool) $value;
	}

	/**
	 * Get summary report email preview link.
	 *
	 * @since 3.0.0
	 *
	 * @return string Preview link.
	 */
	public static function get_preview_link() {

		return add_query_arg(
			[ 'mode' => 'summary_report_email_preview' ],
			wp_mail_smtp()->get_admin()->get_admin_page_url()
		);
	}

	/**
	 * Send summary report email.
	 *
	 * @since 3.0.0
	 */
	public function send() {

		if ( $this->is_disabled() ) {
			return;
		}

		$parsed_home_url = wp_parse_url( home_url() );
		$site_domain     = $parsed_home_url['host'];

		if ( is_multisite() && isset( $parsed_home_url['path'] ) ) {
			$site_domain .= $parsed_home_url['path'];
		}

		$subject = sprintf( /* translators: %s - site domain. */
			esc_html__( 'Your Weekly WP Mail SMTP Summary for %s', 'wp-mail-smtp' ),
			$site_domain
		);

		/**
		 * Filters the summaries email subject.
		 *
		 * @since 3.0.0
		 *
		 * @param string $subject Email subject.
		 */
		$subject = apply_filters( 'wp_mail_smtp_reports_emails_summary_send_subject', $subject );

		/**
		 * Filters the summaries recipient email address.
		 *
		 * @since 3.0.0
		 *
		 * @param string $email Recipient email address.
		 */
		$to_email = apply_filters( 'wp_mail_smtp_reports_emails_summary_send_to', get_option( 'admin_email' ) );

		add_filter( 'wp_mail_content_type', [ $this, 'set_html_content_type' ] );

		wp_mail( $to_email, $subject, $this->get_content() );

		remove_filter( 'wp_mail_content_type', [ $this, 'set_html_content_type' ] );
	}

	/**
	 * Get summary report email content.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_content() {

		$content  = $this->get_header_html();
		$content .= $this->get_main_html();
		$content .= $this->get_footer_html();

		return $content;
	}

	/**
	 * Get summary report email header HTML.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	private function get_header_html() {

		ob_start();
		?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
			<!--[if !mso]><!-->
			<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
			<!--<![endif]-->
			<meta name="color-scheme" content="light dark">
			<meta name="supported-color-schemes" content="light dark">
			<title><?php esc_html_e( 'WP Mail SMTP Weekly Email Summary', 'wp-mail-smtp' ); ?></title>
			<style type="text/css">
				<?php include wp_mail_smtp()->plugin_path . '/assets/css/emails/summary-report-email.css'; ?>
			</style>
		</head>
		<body class="dark-body-bg" style="margin: 0;padding: 0;min-width: 100%;-moz-box-sizing: border-box;-webkit-box-sizing: border-box;box-sizing: border-box;background: #f1f1f1;text-align: left;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #444444;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-weight: normal;mso-line-height-rule: exactly;line-height: 140%;font-size: 14px;height: 100% !important;width: 100% !important;-webkit-font-smoothing: antialiased !important;-moz-osx-font-smoothing: grayscale !important;">
		<table class="body dark-body-bg" border="0" cellpadding="0" cellspacing="0" width="100%" height="100%" style="border-collapse: collapse;border-spacing: 0;padding: 0;vertical-align: top;text-align: left;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;margin: 0;min-width: 100%;-moz-box-sizing: border-box;-webkit-box-sizing: border-box;box-sizing: border-box;background: #f1f1f1;color: #444444;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-weight: normal;mso-line-height-rule: exactly;line-height: 140%;font-size: 14px;height: 100% !important;width: 100%;-webkit-font-smoothing: antialiased !important;-moz-osx-font-smoothing: grayscale !important;">
		<tr style="padding: 0;vertical-align: top;text-align: left;">
		<td align="center" valign="top" style="word-wrap: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0;vertical-align: top;text-align: center;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #444444;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-weight: normal;margin: 0;mso-line-height-rule: exactly;line-height: 140%;font-size: 14px;border-collapse: collapse !important;">
		<!-- Container -->
		<table border="0" cellpadding="0" cellspacing="0" class="container" style="border-collapse: collapse;border-spacing: 0;padding: 0;vertical-align: top;text-align: inherit;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;width: 600px;margin: 0 auto 0 auto;">
		<!-- Header -->
		<tr style="padding: 0;vertical-align: top;text-align: left;">
			<td align="center" valign="middle" class="header" style="word-wrap: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 15px 0px;vertical-align: top;text-align: center;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #444444;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-weight: normal;margin: 0;mso-line-height-rule: exactly;line-height: 140%;font-size: 14px;border-collapse: collapse !important;">
				<div class="light-img">
					<img src="<?php echo esc_url( wp_mail_smtp()->assets_url . '/images/reports/email/wp-mail-smtp-logo' . ( wp_mail_smtp()->is_white_labeled() ? '-whitelabel' : '' ) . '.png' ); ?>" width="321" alt="<?php esc_attr_e( 'WP Mail SMTP Logo', 'wp-mail-smtp' ); ?>" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;width: 321px;max-width: 100%;clear: both;display: inline-block !important;height: auto !important;">
				</div>
				<!--[if !mso]><! -->
				<div class="dark-img" style="display:none; overflow:hidden; float:left; width:0px; max-height:0px; max-width:0px; line-height:0px; visibility:hidden;" align="center">
					<img src="<?php echo esc_url( wp_mail_smtp()->assets_url . '/images/reports/email/wp-mail-smtp-logo-dark' . ( wp_mail_smtp()->is_white_labeled() ? '-whitelabel' : '' ) . '.png' ); ?>" width="321" alt="<?php esc_attr_e( 'WP Mail SMTP Logo', 'wp-mail-smtp' ); ?>" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;width: 321px;max-width: 100%;clear: both;display: inline-block !important;height: auto !important;">
				</div>
				<!--<![endif]-->
			</td>
		</tr>
		<!-- Content -->
		<tr style="padding: 0;vertical-align: top;text-align: left;">
		<td align="left" valign="top" class="content dark-content-bg" style="word-wrap: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 60px;vertical-align: top;text-align: left;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #444444;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-weight: normal;margin: 0;mso-line-height-rule: exactly;line-height: 140%;font-size: 14px;background: #ffffff;border-radius: 6px;border-collapse: collapse !important;">
		<?php
		return ob_get_clean();
	}

	/**
	 * Get summary report email footer HTML.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	private function get_footer_html() {

		$settings_link = add_query_arg(
			[ 'tab' => 'misc' ],
			wp_mail_smtp()->get_admin()->get_admin_page_url( Area::SLUG )
		);

		ob_start();
		?>
		</td>
		</tr>
		<!-- Footer -->
		<tr style="padding: 0;vertical-align: top;text-align: left;">
			<td class="footer" align="center" valign="top" style="word-wrap: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 30px 0px;vertical-align: top;text-align: center;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #777777;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-weight: normal;margin: 0;mso-line-height-rule: exactly;line-height: 140%;font-size: 13px;border-collapse: collapse !important;">
				<?php
				echo wp_kses(
					sprintf( /* translators: %1$s - link to a site; %2$s - link to the settings page. */
						__( 'This email was auto-generated and sent from %1$s. Learn %2$s.', 'wp-mail-smtp' ),
						'<a href="' . esc_url( home_url() ) . '" style="-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #72777c;font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;font-weight: normal;padding: 0;margin: 0;text-align: left;mso-line-height-rule: exactly;line-height: 140%;text-decoration: underline;">' . esc_html( wp_specialchars_decode( get_bloginfo( 'name' ) ) ) . '</a>',
						'<a href="' . esc_url( $settings_link ) . '" style="-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #72777c;font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;font-weight: normal;padding: 0;margin: 0;text-align: left;mso-line-height-rule: exactly;line-height: 140%;text-decoration: underline;">' . esc_html__( 'how to disable it', 'wp-mail-smtp' ) . '</a>'
					),
					[
						'a' => [
							'href'  => [],
							'style' => [],
						],
					]
				);
				?>
			</td>
		</tr>
		</table>
		</td>
		</tr>
		</table>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get summary report email general content HTML.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	protected function get_main_html() {

		$reports = new Reports();

		$upgrade_link = wp_mail_smtp()->get_upgrade_link(
			[
				'medium'  => 'weekly-email-summary',
				'content' => 'upgrade-to-wp-mail-smtp-pro-button',
			]
		);

		ob_start();
		?>
		<h6 class="main-heading dark-white-color" style="margin: 0;padding: 0;color: #444444;word-wrap: normal;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-weight: bold;mso-line-height-rule: exactly;line-height: 22px;;text-align: left;font-size: 18px;margin-bottom: 10px;">
			<?php esc_html_e( 'Hi there,', 'wp-mail-smtp' ); ?>
		</h6>
		<p class="main-description dark-white-color" style="margin: 0;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #444444;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-weight: normal;padding: 0;text-align: left;mso-line-height-rule: exactly;line-height: 19px;font-size: 16px;margin-bottom: 40px;">
			<?php esc_html_e( 'Let’s see how many emails you’ve sent with WP Mail SMTP.', 'wp-mail-smtp' ); ?>
		</p>

		<table class="stats-totals-wrapper two" style="border-collapse: collapse;border-spacing: 0;padding: 0;vertical-align: top;text-align: left;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;width: 100%;">
			<tr style="padding: 0;vertical-align: top;text-align: left;">
				<td class="stats-totals-item-wrapper" style="word-wrap: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px;vertical-align: top;text-align: left;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #444444;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-weight: normal;margin: 0;mso-line-height-rule: exactly;line-height: 140%;font-size: 14px;border-collapse: collapse !important;">
					<?php
					echo $this->get_stats_total_html( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						__( 'Total Emails', 'wp-mail-smtp' ),
						'icon-email.png',
						$reports->get_total_emails_sent(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						$reports->get_total_emails_sent() - $reports->get_total_weekly_emails_sent( 'previous' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'#809EB0'
					);
					?>
				</td>
				<td class="stats-totals-item-wrapper" style="word-wrap: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0px;vertical-align: top;text-align: right;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #444444;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-weight: normal;margin: 0;mso-line-height-rule: exactly;line-height: 140%;font-size: 14px;border-collapse: collapse !important;">
					<?php
					echo $this->get_stats_total_html( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						__( 'Last week', 'wp-mail-smtp' ),
						'icon-check.png',
						$reports->get_total_weekly_emails_sent( 'now' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						$reports->get_total_weekly_emails_sent( 'previous' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'#6AA08B'
					);
					?>
				</td>
			</tr>
		</table>

		<div class="spacer-40" style="line-height:40px;height:40px;mso-line-height-rule:exactly;">&nbsp;</div>

		<table style="border-collapse: collapse;border-spacing: 0;padding: 0;vertical-align: top;text-align: center;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;width: 100%;">
			<tr style="padding: 0;vertical-align: top;text-align: left;">
				<td class="dark-bg" style="word-wrap: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;vertical-align: top;text-align: center;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #444444;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-weight: normal;margin: 0;mso-line-height-rule: exactly;line-height: 140%;font-size: 14px;border-collapse: collapse !important;background: #F8F8F8;border-radius: 4px;padding: 30px">
					<img src="<?php echo esc_url( wp_mail_smtp()->assets_url . '/images/reports/icon-note.png' ); ?>" alt="<?php esc_attr_e( 'Reports', 'wp-mail-smtp' ); ?>" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;width: 75px;max-width: 100%;clear: both;display: inline-block !important;height: auto !important;" width="75">
					<h4 class="upgrade-heading dark-white-color" style="padding: 0;color: #444444;word-wrap: normal;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-weight: bold;mso-line-height-rule: exactly;line-height: 24px;text-align: center;font-size: 20px;margin: 20px 0;"><?php esc_attr_e( 'Want More Stats?', 'wp-mail-smtp' ); ?></h4>
					<p class="upgrade-text" style="margin: 0;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #777777;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-weight: normal;padding: 0;text-align: center;mso-line-height-rule: exactly;line-height: 22px;font-size: 15px;margin-bottom: 20px;">
						<?php
						echo wp_kses(
							__( 'Upgrade to <b>WP Mail SMTP Pro</b> and unlock <u>Email Log</u> and advanced <u>Email Reports</u>. Start measuring the success of your emails today!', 'wp-mail-smtp' ),
							[
								'b' => [],
								'u' => [],
							]
						);
						?>
					</p>
					<center style="width: 100%;">
						<table style="border-collapse: collapse;border-spacing: 0;padding: 0;vertical-align: top;text-align: left;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;width: auto;">
							<tr style="padding: 0;vertical-align: top;text-align: left;">
								<td style="word-wrap: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 0;vertical-align: top;text-align: left;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #444444;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-weight: normal;margin: 0;mso-line-height-rule: exactly;line-height: 100%;font-size: 14px;border-collapse: collapse !important;">
									<table style="border-collapse: collapse;border-spacing: 0;padding: 0;vertical-align: top;text-align: left;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
										<tr style="padding: 0;vertical-align: top;text-align: left;">
											<td style="word-wrap: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;vertical-align: top;text-align: center;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #ffffff;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-weight: normal;margin: 0;mso-line-height-rule: exactly;line-height: 100%;font-size: 14px;background: #e27730;border-radius: 3px;border-collapse: collapse !important;padding: 12px 15px 12px 15px;">
												<a href="<?php echo esc_url( $upgrade_link ); ?>" rel="noopener noreferrer" target="_blank" style="-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #ffffff;font-family: Helvetica, Arial, sans-serif;font-weight: bold;padding: 0;margin: 0;text-align: center;mso-line-height-rule: exactly;line-height: 100%;text-decoration: none;font-size: 16px;display: inline-block;">
													<?php esc_html_e( 'Upgrade to Pro', 'wp-mail-smtp' ); ?>
												</a>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</center>
				</td>
			</tr>
		</table>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get stats total block HTML.
	 *
	 * @since 3.0.0
	 *
	 * @param string $title         Heading.
	 * @param string $icon          Icon file.
	 * @param int    $value         Stats value.
	 * @param int    $prev_value    Previous period stats value.
	 * @param string $color         Heading text color.
	 * @param string $wrapper_style Wrapper inline CSS styles.
	 *
	 * @return string
	 */
	protected function get_stats_total_html( $title, $icon, $value, $prev_value, $color, $wrapper_style = '' ) {

		$width = $this->get_stats_total_item_width();

		$percent_change = $this->calc_percent_change( $value, $prev_value );

		$images_dir_url = wp_mail_smtp()->assets_url . '/images/reports/email/';
		$icon_width     = ( $icon === 'icon-email.png' ) ? 34 : 32;

		ob_start();
		?>
		<table class="stats-total-item" style="border-spacing: 0;padding: 0;vertical-align: top;text-align: left;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;width: <?php echo intval( $width ); ?>px;display: inline-table;min-width:<?php echo intval( $width ); ?>px;" width="<?php echo intval( $width ); ?>">
			<tr style="padding: 0;vertical-align: top;text-align: left;">
				<td class="stats-total-item-inner" style="word-wrap: break-word;-webkit-hyphens: auto;-moz-hyphens: auto;hyphens: auto;padding: 15px 5px;vertical-align: top;text-align: center;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #444444;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-weight: normal;margin: 0;mso-line-height-rule: exactly;line-height: 140%;font-size: 14px;border-collapse: collapse !important; width: 100%; min-width:100%; border: 1px solid #DDDDDD;border-radius: 4px;<?php echo esc_attr( $wrapper_style ); ?>">
					<p class="stats-total-item-icon-wrapper" style="margin: 0;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: <?php echo esc_attr( $color ); ?>;padding: 0;text-align: center;mso-line-height-rule: exactly;margin-bottom: 10px;height: 32px;">
						<img class="stats-total-item-icon" src="<?php echo esc_url( $images_dir_url . $icon ); ?>" alt="<?php echo esc_attr( $title ); ?>" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;width: <?php echo esc_attr( $icon_width ); ?>px;height:32px;clear: both;display: inline-block;" width="<?php echo esc_attr( $icon_width ); ?>" height="32">
					</p>
					<p class="stats-total-item-title" style="margin: 0;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: <?php echo esc_attr( $color ); ?>;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-weight: bold;padding: 0;text-align: center;mso-line-height-rule: exactly;line-height: 14px;font-size: 13px;margin-bottom: 11px;white-space: nowrap;">
						<?php echo esc_html( $title ); ?>
					</p>
					<p class="stats-total-item-value dark-white-color" style="margin: 0 0 12px 0;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #444444;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-weight: bold;padding: 0;text-align: center;mso-line-height-rule: exactly;line-height: 28px;font-size: 28px;">
						<?php echo esc_html( $value ); ?>
					</p>
					<p class="stats-total-item-percent" style="margin: 0;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #777777;font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;font-weight: normal;padding: 0;text-align: center;mso-line-height-rule: exactly;line-height: 14px;font-size: 14px;white-space: nowrap;">
						<img src="<?php echo esc_url( $images_dir_url . 'icon-arrow-' . ( $percent_change['positive'] ? 'up' : 'down' ) . '.png' ); ?>" alt="<?php echo esc_attr( $title ); ?>" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;width: 12px;height: 12px;max-width: 100%;clear: both;" width="12" height="12">
						<span style="padding-left: 1px;">
							<!--[if mso]>&nbsp;<![endif]-->
							<?php echo $percent_change['positive'] ? '+' : '-'; ?><?php echo esc_html( $percent_change['value'] ); ?>%
						</span>
					</p>
				</td>
			</tr>
		</table>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get stats total block width in px.
	 *
	 * @since 3.0.0
	 *
	 * @return int
	 */
	protected function get_stats_total_item_width() {

		return 220;
	}

	/**
	 * Calculate two numbers difference in percent.
	 *
	 * @since 3.0.0
	 *
	 * @param int $new Current value.
	 * @param int $old Previous value.
	 *
	 * @return array
	 */
	private function calc_percent_change( $new, $old ) {

		$new = intval( $new );
		$old = intval( $old );

		// Prevent divide by zero.
		if ( $old === 0 ) {
			$old ++;
			$new ++;
		}

		$diff           = $new - $old;
		$percent_change = ( abs( $diff ) / $old ) * 100;

		return [
			'positive' => $diff >= 0,
			'value'    => round( $percent_change, 1 ),
		];
	}

	/**
	 * Set the HTML content type.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function set_html_content_type() {

		return 'text/html';
	}
}
