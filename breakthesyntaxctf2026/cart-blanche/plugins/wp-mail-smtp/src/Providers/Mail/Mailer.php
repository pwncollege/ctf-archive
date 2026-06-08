<?php

namespace WPMailSMTP\Providers\Mail;

use WPMailSMTP\Providers\MailerAbstract;

/**
 * Class Mailer inherits everything from parent abstract class.
 * This file is required for a proper work of Loader and \ReflectionClass.
 *
 * @package WPMailSMTP\Providers\Mail
 */
class Mailer extends MailerAbstract {

	/**
	 * @inheritdoc
	 */
	public function get_debug_info() {

		$mail_text = array();

		$mail_text[] = '<br><strong>Server:</strong>';

		$disabled_functions = ini_get( 'disable_functions' );
		$disabled           = (array) explode( ',', trim( $disabled_functions ) );

		$mail_text[] = '<strong>PHP.mail():</strong> ' . ( in_array( 'mail', $disabled, true ) || ! function_exists( 'mail' ) ? 'No' : 'Yes' );
		if ( function_exists( 'apache_get_modules' ) ) {
			$modules     = apache_get_modules();
			$mail_text[] = '<strong>Apache.mod_security:</strong> ' . ( in_array( 'mod_security', $modules, true ) || in_array( 'mod_security2', $modules, true ) ? 'Yes' : 'No' );
		}
		if ( function_exists( 'selinux_is_enabled' ) ) {
			$mail_text[] = '<strong>OS.SELinux:</strong> ' . ( selinux_is_enabled() ? 'Yes' : 'No' );
		}
		if ( function_exists( 'grsecurity_is_enabled' ) ) {
			$mail_text[] = '<strong>OS.grsecurity:</strong> ' . ( grsecurity_is_enabled() ? 'Yes' : 'No' );
		}

		return implode( '<br>', $mail_text );
	}

	/**
	 * @inheritdoc
	 */
	public function is_mailer_complete() {

		return true;
	}
}
