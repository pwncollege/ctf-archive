<?php

namespace WPMailSMTP\Admin\Pages;

use WPMailSMTP\Admin\PageAbstract;

/**
 * Versus tab.
 *
 * @since 2.9.0
 */
class VersusTab extends PageAbstract {

	/**
	 * Part of the slug of a tab.
	 *
	 * @since 2.9.0
	 *
	 * @var string
	 */
	protected $slug = 'versus';

	/**
	 * Tab priority.
	 *
	 * @since 2.9.0
	 *
	 * @var int
	 */
	protected $priority = 40;

	/**
	 * Link label of a tab.
	 *
	 * @since 2.9.0
	 *
	 * @return string
	 */
	public function get_label() {

		return esc_html__( 'Lite vs Pro', 'wp-mail-smtp' );
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
	 * Tab content.
	 *
	 * @since 2.9.0
	 */
	public function display() {

		$license = wp_mail_smtp()->get_license_type();
		?>

		<div class="wp-mail-smtp-admin-about-section wp-mail-smtp-admin-about-section-squashed">
			<h1 class="centered">
				<strong>
					<?php
					printf(
					/* translators: %s - plugin current license type. */
						esc_html__( '%s vs Pro', 'wp-mail-smtp' ),
						esc_html( ucfirst( $license ) )
					);
					?>
				</strong>
			</h1>

			<p class="centered <?php echo( $license === 'pro' ? 'hidden' : '' ); ?>">
				<?php esc_html_e( 'Get the most out of WP Mail SMTP by upgrading to Pro and unlocking all of the powerful features.', 'wp-mail-smtp' ); ?>
			</p>
		</div>

		<div class="wp-mail-smtp-admin-about-section wp-mail-smtp-admin-about-section-squashed wp-mail-smtp-admin-about-section-hero wp-mail-smtp-admin-about-section-table">

			<div class="wp-mail-smtp-admin-about-section-hero-main wp-mail-smtp-admin-columns">
				<div class="wp-mail-smtp-admin-column-33">
					<h3 class="no-margin">
						<?php esc_html_e( 'Feature', 'wp-mail-smtp' ); ?>
					</h3>
				</div>
				<div class="wp-mail-smtp-admin-column-33">
					<h3 class="no-margin">
						<?php echo esc_html( ucfirst( $license ) ); ?>
					</h3>
				</div>
				<div class="wp-mail-smtp-admin-column-33">
					<h3 class="no-margin">
						<?php esc_html_e( 'Pro', 'wp-mail-smtp' ); ?>
					</h3>
				</div>
			</div>
			<div class="wp-mail-smtp-admin-about-section-hero-extra no-padding wp-mail-smtp-admin-columns">

				<table>
					<?php
					foreach ( $this->get_license_features() as $slug => $name ) {
						$current = $this->get_license_data( $slug, $license );
						$pro     = $this->get_license_data( $slug, 'pro' );
						?>
						<tr class="wp-mail-smtp-admin-columns">
							<td class="wp-mail-smtp-admin-column-33">
								<p><?php echo esc_html( $name ); ?></p>
							</td>
							<td class="wp-mail-smtp-admin-column-33">
								<p class="features-<?php echo esc_attr( $current['status'] ); ?>">
									<?php echo implode( '<br>', $current['text'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</p>
							</td>
							<td class="wp-mail-smtp-admin-column-33">
								<p class="features-full">
									<?php echo implode( '<br>', $pro['text'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</p>
							</td>
						</tr>
						<?php
					}
					?>
				</table>

			</div>

		</div>

		<?php if ( 'lite' === $license ) : ?>
			<div class="wp-mail-smtp-admin-about-section wp-mail-smtp-admin-about-section-hero">
				<div class="wp-mail-smtp-admin-about-section-hero-main no-border">
					<h3 class="call-to-action centered">
						<a href="<?php echo esc_url( wp_mail_smtp()->get_upgrade_link( 'lite-vs-pro' ) ); ?>" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'Get WP Mail SMTP Pro Today and Unlock all of these Powerful Features', 'wp-mail-smtp' ); ?>
						</a>
					</h3>

					<p class="centered">
						<?php
						printf(
							wp_kses( /* Translators: %s - discount value $50. */
								__( 'Bonus: WP Mail SMTP Lite users get <span class="price-off">%s off regular price</span>, automatically applied at checkout.', 'wp-mail-smtp' ),
								[
									'span' => [
										'class' => [],
									],
								]
							),
							'$50'
						);
						?>
					</p>
				</div>
			</div>
		<?php endif; ?>

		<?php
	}

	/**
	 * Get the list of features for all licenses.
	 *
	 * @since 2.9.0
	 *
	 * @return array
	 */
	private function get_license_features() {

		return [
			'log'       => esc_html__( 'Email Log', 'wp-mail-smtp' ),
			'control'   => esc_html__( 'Email Controls', 'wp-mail-smtp' ),
			'mailers'   => esc_html__( 'Mailer Options', 'wp-mail-smtp' ),
			'multisite' => esc_html__( 'WordPress Multisite', 'wp-mail-smtp' ),
			'support'   => esc_html__( 'Customer Support', 'wp-mail-smtp' ),
		];
	}

	/**
	 * Get the array of data that compared the license data.
	 *
	 * @since 2.9.0
	 *
	 * @param string $feature Feature name.
	 * @param string $license License type to get data for.
	 *
	 * @return array|false
	 */
	private function get_license_data( $feature, $license ) {

		$data = [
			'log'       => [
				'lite' => [
					'status' => 'none',
					'text'   => [
						'<strong>' . esc_html__( 'Emails are not logged', 'wp-mail-smtp' ) . '</strong>',
					],
				],
				'pro'  => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'Access to all Email Logging options right inside WordPress', 'wp-mail-smtp' ) . '</strong>',
					],
				],
			],
			'control'   => [
				'lite' => [
					'status' => 'none',
					'text'   => [
						'<strong>' . esc_html__( 'No controls over whether default WordPress emails are sent', 'wp-mail-smtp' ) . '</strong>',
					],
				],
				'pro'  => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'Complete Email Controls management for most default WordPress emails', 'wp-mail-smtp' ) . '</strong>',
					],
				],
			],
			'mailers'   => [
				'lite' => [
					'status' => 'none',
					'text'   => [
						'<strong>' . esc_html__( 'Limited Mailers', 'wp-mail-smtp' ) . '</strong><br>' . esc_html__( 'Access is limited to standard mailer options only', 'wp-mail-smtp' ),
					],
				],
				'pro'  => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'Additional Mailer Options', 'wp-mail-smtp' ) . '</strong><br>' . esc_html__( 'Microsoft Outlook (with Office365 support), Amazon SES and Zoho Mail', 'wp-mail-smtp' ),
					],
				],
			],
			'multisite' => [
				'lite' => [
					'status' => 'none',
					'text'   => [
						'<strong>' . esc_html__( 'No Global Network Settings', 'wp-mail-smtp' ) . '</strong>',
					],
				],
				'pro'  => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'All Global Network Settings', 'wp-mail-smtp' ) . '</strong><br>' . esc_html__( 'Optionally configure settings at the network level or manage separately for each subsite', 'wp-mail-smtp' ),
					],
				],
			],
			'support'   => [
				'lite' => [
					'status' => 'none',
					'text'   => [
						'<strong>' . esc_html__( 'Limited Support', 'wp-mail-smtp' ) . '</strong>',
					],
				],
				'pro'  => [
					'status' => 'full',
					'text'   => [
						'<strong>' . esc_html__( 'Priority Support', 'wp-mail-smtp' ) . '</strong>',
					],
				],
			],
		];

		// Wrong feature?
		if ( ! isset( $data[ $feature ] ) ) {
			return false;
		}

		// Wrong license type?
		if ( ! isset( $data[ $feature ][ $license ] ) ) {
			return false;
		}

		return $data[ $feature ][ $license ];
	}
}
