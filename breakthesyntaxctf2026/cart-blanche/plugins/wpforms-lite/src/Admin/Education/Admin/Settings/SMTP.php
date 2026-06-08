<?php

namespace WPForms\Admin\Education\Admin\Settings;

use WPForms\Admin\Education\EducationInterface;

/**
 * SMTP education notice.
 *
 * @since 1.8.1
 */
class SMTP implements EducationInterface {

	/**
	 * Indicate if Education core is allowed to load.
	 *
	 * @since 1.8.1
	 *
	 * @return bool
	 */
	public function allow_load() {

		if ( ! wpforms_can_install( 'plugin' ) || ! wpforms_can_activate( 'plugin' ) ) {
			return false;
		}

		$user_id   = get_current_user_id();
		$dismissed = get_user_meta( $user_id, 'wpforms_dismissed', true );

		if ( ! empty( $dismissed['edu-smtp-notice'] ) ) {
			return false;
		}

		$active_plugins = get_option( 'active_plugins', [] );

		$allowed_plugins = [
			'wp-mail-smtp/wp_mail_smtp.php',
			'wp-mail-smtp-pro/wp_mail_smtp.php',
		];

		return ! array_intersect( $active_plugins, $allowed_plugins );
	}

	/**
	 * Init.
	 *
	 * @since 1.8.1
	 */
	public function init() {
	}

	/**
	 * Get notice template.
	 *
	 * @since 1.8.1
	 *
	 * @return string
	 */
	public function get_template() {

		if ( ! $this->allow_load() ) {
			return '';
		}

		return wpforms_render( 'education/admin/settings/smtp-notice' );
	}
}
