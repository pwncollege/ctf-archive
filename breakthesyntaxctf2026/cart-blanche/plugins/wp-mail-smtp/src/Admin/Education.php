<?php

namespace WPMailSMTP\Admin;

/**
 * WP Mail SMTP enhancements to admin pages to educate Lite users on what is available in WP Mail SMTP Pro.
 *
 * @since 2.3.0
 */
class Education {

	/**
	 * The dismissed notice bar user meta key.
	 *
	 * @since 2.3.0
	 */
	const DISMISS_NOTICE_BAR_KEY = 'wp_mail_smtp_edu_notice_bar_dismissed';

	/**
	 * Hooks.
	 *
	 * @since 2.3.0
	 */
	public function hooks() {

		if ( apply_filters( 'wp_mail_smtp_admin_education_notice_bar', true ) ) {
			add_action( 'admin_init', [ $this, 'notice_bar_init' ] );
		}
	}

	/**
	 * Notice bar init.
	 *
	 * @since 2.3.0
	 */
	public function notice_bar_init() {

		add_action( 'wp_mail_smtp_admin_header_before', [ $this, 'notice_bar_display' ] );
		add_action( 'wp_ajax_wp_mail_smtp_notice_bar_dismiss', [ $this, 'notice_bar_ajax_dismiss' ] );
	}

	/**
	 * Notice bar display message.
	 *
	 * @since 2.3.0
	 */
	public function notice_bar_display() {

		// Bail if we're not on a plugin admin page.
		if ( ! wp_mail_smtp()->get_admin()->is_admin_page() ) {
			return;
		}

		$dismissed = get_user_meta( get_current_user_id(), self::DISMISS_NOTICE_BAR_KEY, true );

		if ( ! empty( $dismissed ) ) {
			return;
		}

		$current_screen      = get_current_screen();
		$upgrade_utm_content = $current_screen === null ? 'Upgrade to Pro' : 'Upgrade to Pro - ' . $current_screen->base;
		$upgrade_utm_content = empty( $_GET['tab'] ) ? $upgrade_utm_content : $upgrade_utm_content . ' -- ' . sanitize_key( $_GET['tab'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		printf(
			'<div id="wp-mail-smtp-notice-bar">
				<div class="wp-mail-smtp-notice-bar-container">
				<span class="wp-mail-smtp-notice-bar-message">%s</span>
				<button type="button" class="dismiss" title="%s" />
				</div>
			</div>',
			wp_kses(
				sprintf( /* translators: %s - WPMailSMTP.com Upgrade page URL. */
					__( 'You’re using WP Mail SMTP Lite. To unlock more features, consider <a href="%1$s" target="_blank" rel="noopener noreferrer">upgrading to Pro</a> for <strong>%2$s OFF</strong>.', 'wp-mail-smtp' ),
					wp_mail_smtp()->get_upgrade_link( [ 'medium' => 'notice-bar', 'content' => $upgrade_utm_content ] ), // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound,
					'$50'
				),
				[
					'a' => [
						'href'   => [],
						'rel'    => [],
						'target' => [],
					],
					'strong' => [],
				]
			),
			esc_attr__( 'Dismiss this message.', 'wp-mail-smtp' ),
		);
	}

	/**
	 * Ajax handler for dismissing notices.
	 *
	 * @since 2.3.0
	 */
	public function notice_bar_ajax_dismiss() {

		// Run a security check.
		check_ajax_referer( 'wp-mail-smtp-admin', 'nonce' );

		// Check for permissions.
		if ( ! current_user_can( wp_mail_smtp()->get_capability_manage_options() ) ) {
			wp_send_json_error();
		}

		update_user_meta( get_current_user_id(), self::DISMISS_NOTICE_BAR_KEY, time() );
		wp_send_json_success();
	}
}
