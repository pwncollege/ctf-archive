<?php

namespace WPMailSMTP\Admin\Pages;

use WPMailSMTP\Admin\PageAbstract;
use WPMailSMTP\Helpers\UI;
use WPMailSMTP\WP;

/**
 * Class ControlTab is a placeholder for Pro Email Control tab settings.
 * Displays an upsell.
 *
 * @since 1.6.0
 */
class ControlTab extends PageAbstract {

	/**
	 * Slug of a tab.
	 *
	 * @since 1.6.0
	 *
	 * @var string
	 */
	protected $slug = 'control';

	/**
	 * Link label of a tab.
	 *
	 * @since 1.6.0
	 *
	 * @return string
	 */
	public function get_label() {

		return esc_html__( 'Email Controls', 'wp-mail-smtp' );
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
	 * Get the list of all available emails that we can manage.
	 *
	 * @see   https://github.com/johnbillion/wp_mail Apr 12th 2019.
	 *
	 * @since 3.1.0
	 *
	 * @return array
	 */
	public static function get_controls() {

		return [
			'comments'         => [
				'title'  => esc_html__( 'Comments', 'wp-mail-smtp' ),
				'emails' => [
					'dis_comments_awaiting_moderation' => [
						'label' => esc_html__( 'Awaiting Moderation', 'wp-mail-smtp' ),
						'desc'  => esc_html__( 'Comment is awaiting moderation. Sent to the site admin and post author if they can edit comments.', 'wp-mail-smtp' ),
					],
					'dis_comments_published'           => [
						'label' => esc_html__( 'Published', 'wp-mail-smtp' ),
						'desc'  => esc_html__( 'Comment has been published. Sent to the post author.', 'wp-mail-smtp' ),
					],
				],
			],
			'admin_email'      => [
				'title'  => esc_html__( 'Change of Admin Email', 'wp-mail-smtp' ),
				'emails' => [
					'dis_admin_email_attempt'         => [
						'label' => esc_html__( 'Site Admin Email Change Attempt', 'wp-mail-smtp' ),
						'desc'  => esc_html__( 'Change of site admin email address was attempted. Sent to the proposed new email address.', 'wp-mail-smtp' ),
					],
					'dis_admin_email_changed'         => [
						'label' => esc_html__( 'Site Admin Email Changed', 'wp-mail-smtp' ),
						'desc'  => esc_html__( 'Site admin email address was changed. Sent to the old site admin email address.', 'wp-mail-smtp' ),
					],
					'dis_admin_email_network_attempt' => [
						'label' => esc_html__( 'Network Admin Email Change Attempt', 'wp-mail-smtp' ),
						'desc'  => esc_html__( 'Change of network admin email address was attempted. Sent to the proposed new email address.', 'wp-mail-smtp' ),
					],
					'dis_admin_email_network_changed' => [
						'label' => esc_html__( 'Network Admin Email Changed', 'wp-mail-smtp' ),
						'desc'  => esc_html__( 'Network admin email address was changed. Sent to the old network admin email address.', 'wp-mail-smtp' ),
					],
				],
			],
			'user_details'     => [
				'title'  => esc_html__( 'Change of User Email or Password', 'wp-mail-smtp' ),
				'emails' => [
					'dis_user_details_password_reset_request' => [
						'label' => esc_html__( 'Reset Password Request', 'wp-mail-smtp' ),
						'desc'  => esc_html__( 'User requested a password reset via "Lost your password?". Sent to the user.', 'wp-mail-smtp' ),
					],
					'dis_user_details_password_reset'         => [
						'label' => esc_html__( 'Password Reset Successfully', 'wp-mail-smtp' ),
						'desc'  => esc_html__( 'User reset their password from the password reset link. Sent to the site admin.', 'wp-mail-smtp' ),
					],
					'dis_user_details_password_changed'       => [
						'label' => esc_html__( 'Password Changed', 'wp-mail-smtp' ),
						'desc'  => esc_html__( 'User changed their password. Sent to the user.', 'wp-mail-smtp' ),
					],
					'dis_user_details_email_change_attempt'   => [
						'label' => esc_html__( 'Email Change Attempt', 'wp-mail-smtp' ),
						'desc'  => esc_html__( 'User attempted to change their email address. Sent to the proposed new email address.', 'wp-mail-smtp' ),
					],
					'dis_user_details_email_changed'          => [
						'label' => esc_html__( 'Email Changed', 'wp-mail-smtp' ),
						'desc'  => esc_html__( 'User changed their email address. Sent to the user.', 'wp-mail-smtp' ),
					],
				],
			],
			'personal_data'    => [
				'title'  => esc_html__( 'Personal Data Requests', 'wp-mail-smtp' ),
				'emails' => [
					'dis_personal_data_user_confirmed'   => [
						'label' => esc_html__( 'User Confirmed Export / Erasure Request', 'wp-mail-smtp' ),
						'desc'  => esc_html__( 'User clicked a confirmation link in personal data export or erasure request email. Sent to the site or network admin.', 'wp-mail-smtp' ),
					],
					'dis_personal_data_erased_data'      => [
						'label' => esc_html__( 'Admin Erased Data', 'wp-mail-smtp' ),
						'desc'  => esc_html__( 'Site admin clicked "Erase Personal Data" button next to a confirmed data erasure request. Sent to the requester email address.', 'wp-mail-smtp' ),
					],
					'dis_personal_data_sent_export_link' => [
						'label' => esc_html__( 'Admin Sent Link to Export Data', 'wp-mail-smtp' ),
						'desc'  => esc_html__( 'Site admin clicked "Email Data" button next to a confirmed data export request. Sent to the requester email address.', 'wp-mail-smtp' ) . '<br>' .
											 '<strong>' . esc_html__( 'Disabling this option will block users from being able to export their personal data, as they will not receive an email with a link.', 'wp-mail-smtp' ) . '</strong>',
					],
				],
			],
			'auto_updates'     => [
				'title'  => esc_html__( 'Automatic Updates', 'wp-mail-smtp' ),
				'emails' => [
					'dis_auto_updates_plugin_status' => [
						'label' => esc_html__( 'Plugin Status', 'wp-mail-smtp' ),
						'desc'  => esc_html__( 'Completion or failure of a background automatic plugin update. Sent to the site or network admin.', 'wp-mail-smtp' ),
					],
					'dis_auto_updates_theme_status'  => [
						'label' => esc_html__( 'Theme Status', 'wp-mail-smtp' ),
						'desc'  => esc_html__( 'Completion or failure of a background automatic theme update. Sent to the site or network admin.', 'wp-mail-smtp' ),
					],
					'dis_auto_updates_status'        => [
						'label' => esc_html__( 'WP Core Status', 'wp-mail-smtp' ),
						'desc'  => esc_html__( 'Completion or failure of a background automatic core update. Sent to the site or network admin.', 'wp-mail-smtp' ),
					],
					'dis_auto_updates_full_log'      => [
						'label' => esc_html__( 'Full Log', 'wp-mail-smtp' ),
						'desc'  => esc_html__( 'Full log of background update results which includes information about WordPress core, plugins, themes, and translations updates. Only sent when you are using a development version of WordPress. Sent to the site or network admin.', 'wp-mail-smtp' ),
					],
				],
			],
			'new_user'         => [
				'title'  => esc_html__( 'New User', 'wp-mail-smtp' ),
				'emails' => [
					'dis_new_user_created_to_admin'        => [
						'label' => esc_html__( 'Created (Admin)', 'wp-mail-smtp' ),
						'desc'  => esc_html__( 'A new user was created. Sent to the site admin.', 'wp-mail-smtp' ),
					],
					'dis_new_user_created_to_user'         => [
						'label' => esc_html__( 'Created (User)', 'wp-mail-smtp' ),
						'desc'  => esc_html__( 'A new user was created. Sent to the new user.', 'wp-mail-smtp' ),
					],
					'dis_new_user_invited_to_site_network' => [
						'label' => esc_html__( 'Invited To Site', 'wp-mail-smtp' ),
						'desc'  => esc_html__( 'A new user was invited to a site from Users -> Add New -> Add New User. Sent to the invited user.', 'wp-mail-smtp' ),
					],
					'dis_new_user_created_network'         => [
						'label' => esc_html__( 'Created On Site', 'wp-mail-smtp' ),
						'desc'  => esc_html__( 'A new user account was created. Sent to Network Admin.', 'wp-mail-smtp' ),
					],
					'dis_new_user_added_activated_network' => [
						'label' => esc_html__( 'Added / Activated on Site', 'wp-mail-smtp' ),
						'desc'  => esc_html__( 'A user has been added, or their account activation has been successful. Sent to the user, that has been added/activated.', 'wp-mail-smtp' ),
					],
				],
			],
			'network_new_site' => [
				'title'  => esc_html__( 'New Site', 'wp-mail-smtp' ),
				'emails' => [
					'dis_new_site_user_registered_site_network'                  => [
						'label' => esc_html__( 'User Created Site', 'wp-mail-smtp' ),
						'desc'  => esc_html__( 'User registered for a new site. Sent to the site admin.', 'wp-mail-smtp' ),
					],
					'dis_new_site_user_added_activated_site_in_network_to_admin' => [
						'label' => esc_html__( 'Network Admin: User Activated / Added Site', 'wp-mail-smtp' ),
						'desc'  => esc_html__( 'User activated their new site, or site was added from Network Admin -> Sites -> Add New. Sent to Network Admin.', 'wp-mail-smtp' ),
					],
					'dis_new_site_user_added_activated_site_in_network_to_site'  => [
						'label' => esc_html__( 'Site Admin: Activated / Added Site', 'wp-mail-smtp' ),
						'desc'  => esc_html__( 'User activated their new site, or site was added from Network Admin -> Sites -> Add New. Sent to Site Admin.', 'wp-mail-smtp' ),
					],
				],
			],
		];
	}

	/**
	 * Output HTML of the email controls settings preview.
	 *
	 * @since 1.6.0
	 * @since 2.1.0 Replaced images with SVGs.
	 * @since 3.1.0 Updated layout to inactive settings preview.
	 */
	public function display() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		$top_upgrade_button_url    = add_query_arg(
			[ 'discount' => 'LITEUPGRADE' ],
			wp_mail_smtp()->get_upgrade_link(
				[
					'medium'  => 'Email Controls',
					'content' => 'Upgrade to WP Mail SMTP Pro Button Top',
				]
			)
		);
		$bottom_upgrade_button_url = add_query_arg(
			[ 'discount' => 'LITEUPGRADE' ],
			wp_mail_smtp()->get_upgrade_link(
				[
					'medium'  => 'Email Controls',
					'content' => 'Upgrade to WP Mail SMTP Pro Button',
				]
			)
		);
		?>

		<div id="wp-mail-smtp-email-controls-product-education" class="wp-mail-smtp-product-education">
			<div class="wp-mail-smtp-product-education__row">
				<h4 class="wp-mail-smtp-product-education__heading">
					<?php esc_html_e( 'Email Controls', 'wp-mail-smtp' ); ?>
				</h4>
				<p class="wp-mail-smtp-product-education__description">
					<?php
					esc_html_e( 'Email controls allow you to manage the automatic notifications you receive from your WordPress website. With the flick of a switch, you can reduce inbox clutter and focus on the alerts that matter the most. It\'s easy to disable emails about comments, email or password changes, WordPress updates, user registrations, and personal data requests.', 'wp-mail-smtp' );
					?>
				</p>

				<a href="<?php echo esc_url( $top_upgrade_button_url ); ?>" target="_blank" rel="noopener noreferrer" class="wp-mail-smtp-product-education__upgrade-btn wp-mail-smtp-product-education__upgrade-btn--top wp-mail-smtp-btn wp-mail-smtp-btn-upgrade wp-mail-smtp-btn-orange">
					<?php esc_html_e( 'Upgrade to WP Mail SMTP Pro', 'wp-mail-smtp' ); ?>
				</a>
			</div>

			<div class="wp-mail-smtp-product-education__row wp-mail-smtp-product-education__row--inactive">
				<?php
				foreach ( static::get_controls() as $section_id => $section ) :
					if ( empty( $section['emails'] ) ) {
						continue;
					}

					if ( $this->is_it_for_multisite( sanitize_key( $section_id ) ) && ! WP::use_global_plugin_settings() ) {
						continue;
					}
					?>
					<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-content wp-mail-smtp-clear section-heading no-desc">
						<div class="wp-mail-smtp-setting-field">
							<h5><?php echo esc_html( $section['title'] ); ?></h5>
						</div>
					</div>

					<?php
					foreach ( $section['emails'] as $email_id => $email ) :
						$email_id = sanitize_key( $email_id );

						if ( empty( $email_id ) || empty( $email['label'] ) ) {
							continue;
						}

						if ( $this->is_it_for_multisite( sanitize_key( $email_id ) ) && ! WP::use_global_plugin_settings() ) {
							continue;
						}
						?>
						<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-checkbox-toggle wp-mail-smtp-clear">
							<div class="wp-mail-smtp-setting-label">
								<label><?php echo esc_html( $email['label'] ); ?></label>
							</div>
							<div class="wp-mail-smtp-setting-field">
								<?php
								UI::toggle( [ 'checked' => true ] );
								?>
								<?php if ( ! empty( $email['desc'] ) ) : ?>
									<p class="desc">
										<?php echo $email['desc']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									</p>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</div>

			<a href="<?php echo esc_url( $bottom_upgrade_button_url ); ?>" target="_blank" rel="noopener noreferrer" class="wp-mail-smtp-product-education__upgrade-btn wp-mail-smtp-product-education__upgrade-btn--bottom wp-mail-smtp-btn wp-mail-smtp-btn-upgrade wp-mail-smtp-btn-orange">
				<?php esc_html_e( 'Upgrade to WP Mail SMTP Pro', 'wp-mail-smtp' ); ?>
			</a>
		</div>
		<?php
	}

	/**
	 * Whether this key dedicated to MultiSite environment.
	 *
	 * @since 1.5.0
	 *
	 * @param string $key Email unique key.
	 *
	 * @return bool
	 */
	protected function is_it_for_multisite( $key ) {

		return strpos( $key, 'network' ) !== false;
	}

	/**
	 * Not used as we display an upsell.
	 *
	 * @since 1.6.0
	 *
	 * @param array $data Post data specific for the plugin.
	 */
	public function process_post( $data ) { }
}
