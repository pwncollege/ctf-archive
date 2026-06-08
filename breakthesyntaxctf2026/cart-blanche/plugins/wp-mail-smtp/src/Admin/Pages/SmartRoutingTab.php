<?php

namespace WPMailSMTP\Admin\Pages;

use WPMailSMTP\Admin\PageAbstract;
use WPMailSMTP\Helpers\UI;
use WPMailSMTP\WP;

/**
 * Class SmartRoutingTab is a placeholder for Pro smart routing feature.
 * Displays product education.
 *
 * @since 3.7.0
 */
class SmartRoutingTab extends PageAbstract {

	/**
	 * Part of the slug of a tab.
	 *
	 * @since 3.7.0
	 *
	 * @var string
	 */
	protected $slug = 'routing';

	/**
	 * Constructor.
	 *
	 * @since 3.7.0
	 *
	 * @param PageAbstract $parent_page Parent page object.
	 */
	public function __construct( $parent_page = null ) {

		parent::__construct( $parent_page );

		if ( wp_mail_smtp()->get_admin()->get_current_tab() === $this->slug && ! wp_mail_smtp()->is_pro() ) {
			$this->hooks();
		}
	}

	/**
	 * Link label of a tab.
	 *
	 * @since 3.7.0
	 *
	 * @return string
	 */
	public function get_label() {

		return esc_html__( 'Smart Routing', 'wp-mail-smtp' );
	}

	/**
	 * Register hooks.
	 *
	 * @since 3.7.0
	 */
	public function hooks() {

		add_action( 'wp_mail_smtp_admin_area_enqueue_assets', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Enqueue required JS and CSS.
	 *
	 * @since 3.7.0
	 */
	public function enqueue_assets() {

		wp_enqueue_style(
			'wp-mail-smtp-smart-routing',
			wp_mail_smtp()->plugin_url . '/assets/css/smtp-smart-routing.min.css',
			[],
			WPMS_PLUGIN_VER
		);
	}

	/**
	 * Output HTML of smart routing education.
	 *
	 * @since 3.7.0
	 */
	public function display() {

		$top_upgrade_button_url    = wp_mail_smtp()->get_upgrade_link(
			[
				'medium'  => 'Smart Routing Settings',
				'content' => 'Upgrade to WP Mail SMTP Pro Button Top',
			]
		);
		$bottom_upgrade_button_url = wp_mail_smtp()->get_upgrade_link(
			[
				'medium'  => 'Smart Routing Settings',
				'content' => 'Upgrade to WP Mail SMTP Pro Button',
			]
		);
		?>
		<div id="wp-mail-smtp-smart-routing-product-education" class="wp-mail-smtp-product-education">
			<div class="wp-mail-smtp-product-education__row wp-mail-smtp-product-education__row--no-border">
				<h4 class="wp-mail-smtp-product-education__heading">
					<?php esc_html_e( 'Smart Routing', 'wp-mail-smtp' ); ?>
				</h4>
				<p class="wp-mail-smtp-product-education__description">
					<?php
					esc_html_e( 'Send emails from different additional connections based on your configured conditions. Emails that do not match any of the conditions below will be sent via your Primary Connection.', 'wp-mail-smtp' );
					?>
				</p>

				<a href="<?php echo esc_url( $top_upgrade_button_url ); ?>" target="_blank" rel="noopener noreferrer" class="wp-mail-smtp-product-education__upgrade-btn wp-mail-smtp-product-education__upgrade-btn--top wp-mail-smtp-btn wp-mail-smtp-btn-upgrade wp-mail-smtp-btn-orange">
					<?php esc_html_e( 'Upgrade to WP Mail SMTP Pro', 'wp-mail-smtp' ); ?>
				</a>
			</div>
			<div class="wp-mail-smtp-product-education__row wp-mail-smtp-product-education__row--inactive wp-mail-smtp-product-education__row--no-border wp-mail-smtp-product-education__row--no-padding wp-mail-smtp-product-education__row--full-width">
				<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-no-border">
					<?php
					UI::toggle(
						[
							'label'   => esc_html__( 'Enable Smart Routing', 'wp-mail-smtp' ),
							'class'   => 'wp-mail-smtp-smart-routing-toggle',
							'checked' => true,
						]
					);
					?>
				</div>
				<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-no-border wp-mail-smtp-setting-row-no-padding">
					<div class="wp-mail-smtp-smart-routing-routes">
						<div class="wp-mail-smtp-smart-routing-route">
							<div class="wp-mail-smtp-smart-routing-route__header">
								<span><?php esc_html_e( 'Send with', 'wp-mail-smtp' ); ?></span>
								<select class="wp-mail-smtp-smart-routing-route__connection">
									<option><?php esc_html_e( 'WooCommerce Emails (SendLayer)', 'wp-mail-smtp' ); ?></option>
								</select>
								<span><?php esc_html_e( 'if the following conditions are met...', 'wp-mail-smtp' ); ?></span>
								<div class="wp-mail-smtp-smart-routing-route__actions">
									<div class="wp-mail-smtp-smart-routing-route__order">
										<button class="wp-mail-smtp-smart-routing-route__order-btn wp-mail-smtp-smart-routing-route__order-btn--up">
											<img src="<?php echo esc_url( wp_mail_smtp()->assets_url . '/images/icons/arrow-up.svg' ); ?>" alt="<?php esc_attr_e( 'Arrow Up', 'wp-mail-smtp' ); ?>">
										</button>
										<button class="wp-mail-smtp-smart-routing-route__order-btn wp-mail-smtp-smart-routing-route__order-btn--down">
											<img src="<?php echo esc_url( wp_mail_smtp()->assets_url . '/images/icons/arrow-up.svg' ); ?>" alt="<?php esc_attr_e( 'Arrow Down', 'wp-mail-smtp' ); ?>">
										</button>
									</div>
									<button class="wp-mail-smtp-smart-routing-route__delete">
										<i class="dashicons dashicons-trash"></i>
									</button>
								</div>
							</div>
							<div class="wp-mail-smtp-smart-routing-route__main">
								<div class="wp-mail-smtp-conditional">
									<div class="wp-mail-smtp-conditional__group">
										<table>
											<tbody>
											<tr class="wp-mail-smtp-conditional__row">
												<td class="wp-mail-smtp-conditional__property-col">
													<select>
														<option><?php esc_html_e( 'Subject', 'wp-mail-smtp' ); ?></option>
													</select>
												</td>
												<td class="wp-mail-smtp-conditional__operator-col">
													<select class="wp-mail-smtp-conditional__operator">
														<option><?php esc_html_e( 'Contains', 'wp-mail-smtp' ); ?></option>
													</select>
												</td>
												<td class="wp-mail-smtp-conditional__value-col">
													<input type="text" value="<?php esc_html_e( 'Order', 'wp-mail-smtp' ); ?>" class="wp-mail-smtp-conditional__value">
												</td>
												<td class="wp-mail-smtp-conditional__actions">
													<button class="wp-mail-smtp-conditional__add-rule wp-mail-smtp-btn wp-mail-smtp-btn-md wp-mail-smtp-btn-grey">
														<?php esc_html_e( 'And', 'wp-mail-smtp' ); ?>
													</button>
													<button class="wp-mail-smtp-conditional__delete-rule">
														<i class="dashicons dashicons-trash" aria-hidden="true"></i>
													</button>
												</td>
											</tr>
											<tr class="wp-mail-smtp-conditional__row">
												<td class="wp-mail-smtp-conditional__property-col">
													<select class="wp-mail-smtp-conditional__property">
														<option><?php esc_html_e( 'From Email', 'wp-mail-smtp' ); ?></option>
													</select>
												</td>
												<td class="wp-mail-smtp-conditional__operator-col">
													<select class="wp-mail-smtp-conditional__operator">
														<option><?php esc_html_e( 'Is', 'wp-mail-smtp' ); ?></option>
													</select>
												</td>
												<td class="wp-mail-smtp-conditional__value-col">
													<input type="text" value="shop@wpmailsmtp.com" class="wp-mail-smtp-conditional__value">
												</td>
												<td class="wp-mail-smtp-conditional__actions">
													<button class="wp-mail-smtp-conditional__add-rule wp-mail-smtp-btn wp-mail-smtp-btn-md wp-mail-smtp-btn-grey">
														<?php esc_html_e( 'And', 'wp-mail-smtp' ); ?>
													</button>
													<button class="wp-mail-smtp-conditional__delete-rule">
														<i class="dashicons dashicons-trash" aria-hidden="true"></i>
													</button>
												</td>
											</tr>
											</tbody>
										</table>
										<div class="wp-mail-smtp-conditional__group-delimiter"><?php esc_html_e( 'or', 'wp-mail-smtp' ); ?></div>
									</div>
									<div class="wp-mail-smtp-conditional__group">
										<table>
											<tbody>
											<tr class="wp-mail-smtp-conditional__row">
												<td class="wp-mail-smtp-conditional__property-col">
													<select class="wp-mail-smtp-conditional__property">
														<option><?php esc_html_e( 'From Email', 'wp-mail-smtp' ); ?></option>
													</select>
												</td>
												<td class="wp-mail-smtp-conditional__operator-col">
													<select class="wp-mail-smtp-conditional__operator">
														<option><?php esc_html_e( 'Is', 'wp-mail-smtp' ); ?></option>
													</select>
												</td>
												<td class="wp-mail-smtp-conditional__value-col">
													<input type="text" value="returns@wpmailsmtp.com" class="wp-mail-smtp-conditional__value">
												</td>
												<td class="wp-mail-smtp-conditional__actions">
													<button class="wp-mail-smtp-conditional__add-rule wp-mail-smtp-btn wp-mail-smtp-btn-md wp-mail-smtp-btn-grey">
														<?php esc_html_e( 'And', 'wp-mail-smtp' ); ?>
													</button>
													<button class="wp-mail-smtp-conditional__delete-rule">
														<i class="dashicons dashicons-trash" aria-hidden="true"></i>
													</button>
												</td>
											</tr>
											</tbody>
										</table>
										<div class="wp-mail-smtp-conditional__group-delimiter"><?php esc_html_e( 'or', 'wp-mail-smtp' ); ?></div>
									</div>
									<button class="wp-mail-smtp-conditional__add-group wp-mail-smtp-btn wp-mail-smtp-btn-md wp-mail-smtp-btn-grey">
										<?php esc_html_e( 'Add New Group', 'wp-mail-smtp' ); ?>
									</button>
								</div>
							</div>
						</div>
						<div class="wp-mail-smtp-smart-routing-route">
							<div class="wp-mail-smtp-smart-routing-route__header">
								<span><?php esc_html_e( 'Send with', 'wp-mail-smtp' ); ?></span>
								<select class="wp-mail-smtp-smart-routing-route__connection">
									<option><?php esc_html_e( 'Contact Emails (SMTP.com)', 'wp-mail-smtp' ); ?></option>
								</select>
								<span><?php esc_html_e( 'if the following conditions are met...', 'wp-mail-smtp' ); ?></span>
								<div class="wp-mail-smtp-smart-routing-route__actions">
									<div class="wp-mail-smtp-smart-routing-route__order">
										<button class="wp-mail-smtp-smart-routing-route__order-btn wp-mail-smtp-smart-routing-route__order-btn--up">
											<img src="<?php echo esc_url( wp_mail_smtp()->assets_url . '/images/icons/arrow-up.svg' ); ?>" alt="<?php esc_attr_e( 'Arrow Up', 'wp-mail-smtp' ); ?>">
										</button>
										<button class="wp-mail-smtp-smart-routing-route__order-btn wp-mail-smtp-smart-routing-route__order-btn--down">
											<img src="<?php echo esc_url( wp_mail_smtp()->assets_url . '/images/icons/arrow-up.svg' ); ?>" alt="<?php esc_attr_e( 'Arrow Down', 'wp-mail-smtp' ); ?>">
										</button>
									</div>
									<button class="wp-mail-smtp-smart-routing-route__delete">
										<i class="dashicons dashicons-trash"></i>
									</button>
								</div>
							</div>
							<div class="wp-mail-smtp-smart-routing-route__main">
								<div class="wp-mail-smtp-conditional">
									<div class="wp-mail-smtp-conditional__group">
										<table>
											<tbody>
											<tr class="wp-mail-smtp-conditional__row">
												<td class="wp-mail-smtp-conditional__property-col">
													<select>
														<option><?php esc_html_e( 'Initiator', 'wp-mail-smtp' ); ?></option>
													</select>
												</td>
												<td class="wp-mail-smtp-conditional__operator-col">
													<select class="wp-mail-smtp-conditional__operator">
														<option><?php esc_html_e( 'Is', 'wp-mail-smtp' ); ?></option>
													</select>
												</td>
												<td class="wp-mail-smtp-conditional__value-col">
													<input type="text" value="<?php esc_html_e( 'WPForms', 'wp-mail-smtp' ); ?>" class="wp-mail-smtp-conditional__value">
												</td>
												<td class="wp-mail-smtp-conditional__actions">
													<button class="wp-mail-smtp-conditional__add-rule wp-mail-smtp-btn wp-mail-smtp-btn-md wp-mail-smtp-btn-grey">
														<?php esc_html_e( 'And', 'wp-mail-smtp' ); ?>
													</button>
													<button class="wp-mail-smtp-conditional__delete-rule">
														<i class="dashicons dashicons-trash" aria-hidden="true"></i>
													</button>
												</td>
											</tr>
											</tbody>
										</table>
										<div class="wp-mail-smtp-conditional__group-delimiter"><?php esc_html_e( 'or', 'wp-mail-smtp' ); ?></div>
									</div>
									<button class="wp-mail-smtp-conditional__add-group wp-mail-smtp-btn wp-mail-smtp-btn-md wp-mail-smtp-btn-grey">
										<?php esc_html_e( 'Add New Group', 'wp-mail-smtp' ); ?>
									</button>
								</div>
							</div>
						</div>
					</div>
					<div class="wp-mail-smtp-smart-routing-routes-note">
						<img src="<?php echo esc_url( wp_mail_smtp()->assets_url . '/images/icons/lightbulb.svg' ); ?>" alt="<?php esc_attr_e( 'Light bulb icon', 'wp-mail-smtp' ); ?>">
						<?php esc_html_e( 'Friendly reminder, your Primary Connection will be used for all emails that do not match the conditions above.', 'wp-mail-smtp' ); ?>
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
