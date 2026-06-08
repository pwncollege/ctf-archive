<?php

namespace WPMailSMTP\Admin;

use WPMailSMTP\Options;

/**
 * Class for admin notice requesting plugin review.
 *
 * @since 2.1.0
 */
class Review {

	/**
	 * The name of the WP option for the review notice data.
	 *
	 * Data attributes:
	 * - time
	 * - dismissed
	 *
	 * @since 2.1.0
	 */
	const NOTICE_OPTION = 'wp_mail_smtp_review_notice';

	/**
	 * Days the plugin waits before displaying a review request.
	 *
	 * @since 2.1.0
	 */
	const WAIT_PERIOD = 14;

	/**
	 * Initialize hooks.
	 *
	 * @since 2.1.0
	 */
	public function hooks() {

		add_action( 'admin_init', [ $this, 'admin_notices' ] );
		add_action( 'wp_ajax_wp_mail_smtp_review_dismiss', array( $this, 'review_dismiss' ) );
	}

	/**
	 * Display notices only in Network Admin if in Multisite.
	 * Otherwise, display in Admin Dashboard.
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	public function admin_notices() { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		if ( is_multisite() ) {
			add_action( 'network_admin_notices', [ $this, 'review_request' ] );
		} else {
			add_action( 'admin_notices', [ $this, 'review_request' ] );
		}
	}

	/**
	 * Add admin notices as needed for reviews.
	 *
	 * @since 2.1.0
	 */
	public function review_request() {

		// Only consider showing the review request to admin users.
		if ( ! is_super_admin() ) {
			return;
		}

		// Verify that we can do a check for reviews.
		$review = get_option( self::NOTICE_OPTION );
		$time   = time();
		$load   = false;

		if ( empty( $review ) ) {
			$review = [
				'time'      => $time,
				'dismissed' => false,
			];
			update_option( self::NOTICE_OPTION, $review );
		} else {
			// Check if it has been dismissed or not.
			if ( isset( $review['dismissed'] ) && ! $review['dismissed'] ) {
				$load = true;
			}
		}

		// If we cannot load, return early.
		if ( ! $load ) {
			return;
		}

		$this->review();
	}

	/**
	 * Maybe show review request.
	 *
	 * @since 2.1.0
	 */
	private function review() {

		// Get the currently selected mailer.
		$mailer = Options::init()->get( 'mail', 'mailer' );

		// Skip if no or the default mailer is selected.
		if ( empty( $mailer ) || $mailer === 'mail' ) {
			return;
		}

		// Fetch when plugin was initially activated.
		$activated = get_option( 'wp_mail_smtp_activated_time' );

		// Skip if the plugin activated time is not set.
		if ( empty( $activated ) ) {
			return;
		}

		$mailer_object = wp_mail_smtp()
			->get_providers()
			->get_mailer( $mailer, wp_mail_smtp()->get_processor()->get_phpmailer() );

		// Check if mailer setup is complete.
		$mailer_setup_complete = ! empty( $mailer_object ) ? $mailer_object->is_mailer_complete() : false;

		// Skip if the mailer is not set or the plugin is active for less then a defined number of days.
		if ( ! $mailer_setup_complete || ( $activated + ( DAY_IN_SECONDS * self::WAIT_PERIOD ) ) > time() ) {
			return;
		}

		// We have a candidate! Output a review message.
		?>
		<div class="notice notice-info is-dismissible wp-mail-smtp-review-notice">
			<div class="wp-mail-smtp-review-step wp-mail-smtp-review-step-1">
				<p><?php esc_html_e( 'Are you enjoying WP Mail SMTP?', 'wp-mail-smtp' ); ?></p>
				<p>
					<a href="#" class="wp-mail-smtp-review-switch-step" data-step="3"><?php esc_html_e( 'Yes', 'wp-mail-smtp' ); ?></a>&nbsp;&bull;&nbsp;
					<a href="#" class="wp-mail-smtp-review-switch-step" data-step="2"><?php esc_html_e( 'No', 'wp-mail-smtp' ); ?></a>
				</p>
			</div>
			<div class="wp-mail-smtp-review-step wp-mail-smtp-review-step-2" style="display: none">
				<p><?php esc_html_e( 'We\'re sorry to hear you aren\'t enjoying WP Mail SMTP. We would love a chance to improve. Could you take a minute and let us know what we can do better?', 'wp-mail-smtp' ); ?></p>
				<p>
					<?php
					printf(
						'<a href="%1$s" class="wp-mail-smtp-dismiss-review-notice wp-mail-smtp-review-out" target="_blank" rel="noopener noreferrer">%2$s</a>',
						// phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
						esc_url( wp_mail_smtp()->get_utm_url( 'https://wpmailsmtp.com/plugin-feedback/', [ 'medium' => 'review-notice', 'content' => 'Provide Feedback' ] ) ),
						esc_html__( 'Provide Feedback', 'wp-mail-smtp' )
					);
					?>
					&nbsp;&bull;&nbsp;
					<a href="#" class="wp-mail-smtp-dismiss-review-notice" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'No thanks', 'wp-mail-smtp' ); ?>
					</a>
				</p>
			</div>
			<div class="wp-mail-smtp-review-step wp-mail-smtp-review-step-3" style="display: none">
				<p><?php esc_html_e( 'That\'s fantastic! Would you consider giving it a 5-star rating on WordPress.org? It will help other users with email issues and it will mean the world to us!', 'wp-mail-smtp' ); ?></p>
				<p>
					<a href="https://wordpress.org/support/plugin/wp-mail-smtp/reviews/#new-post" class="wp-mail-smtp-dismiss-review-notice wp-mail-smtp-review-out" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Yes, I\'ll rate it with 5-stars', 'wp-mail-smtp' ); ?>
					</a>&nbsp;&bull;&nbsp;
					<a href="#" class="wp-mail-smtp-dismiss-review-notice" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'No, maybe later', 'wp-mail-smtp' ); ?></a>&nbsp;&bull;&nbsp;
					<a href="#" class="wp-mail-smtp-dismiss-review-notice" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'I already did', 'wp-mail-smtp' ); ?></a>
				</p>
			</div>
		</div>
		<script type="text/javascript">
			jQuery( document ).ready( function ( $ ) {
				$( document ).on( 'click', '.wp-mail-smtp-dismiss-review-notice, .wp-mail-smtp-review-notice button', function( e ) {
					if ( ! $( this ).hasClass( 'wp-mail-smtp-review-out' ) ) {
						e.preventDefault();
					}
					$.post( ajaxurl, { action: 'wp_mail_smtp_review_dismiss' } );
					$( '.wp-mail-smtp-review-notice' ).remove();
				} );

				$( document ).on( 'click', '.wp-mail-smtp-review-switch-step', function( e ) {
					e.preventDefault();
					var target = parseInt( $( this ).attr( 'data-step' ), 10 );

					if ( target ) {
						var $notice = $( this ).closest( '.wp-mail-smtp-review-notice' );
						var $review_step = $notice.find( '.wp-mail-smtp-review-step-' + target );

						if ( $review_step.length > 0 ) {
							$notice.find( '.wp-mail-smtp-review-step:visible' ).fadeOut( function() {
								$review_step.fadeIn();
							} );
						}
					}
				} );
			} );
		</script>
		<?php
	}

	/**
	 * Dismiss the review admin notice.
	 *
	 * @since 2.1.0
	 */
	public function review_dismiss() {

		$review              = get_option( self::NOTICE_OPTION, [] );
		$review['time']      = time();
		$review['dismissed'] = true;
		update_option( self::NOTICE_OPTION, $review );

		if ( is_super_admin() && is_multisite() ) {
			$site_list = get_sites();
			foreach ( (array) $site_list as $site ) {
				switch_to_blog( $site->blog_id );

				update_option( self::NOTICE_OPTION, $review );

				restore_current_blog();
			}
		}

		wp_send_json_success();
	}
}
