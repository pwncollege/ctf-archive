<?php

namespace WPMailSMTP;

/**
 * Class Upgrade helps upgrade plugin options and similar tasks when the
 * occasion arises.
 *
 * @since 1.1.0
 */
class Upgrade {

	/**
	 * Upgrade constructor.
	 *
	 * @since 1.1.0
	 */
	public function __construct() { }

	/**
	 * Run upgrades.
	 *
	 * @since 4.0.0
	 */
	public function run() {

		$upgrades = $this->upgrades();

		if ( empty( $upgrades ) ) {
			return;
		}

		// Run any available upgrades.
		foreach ( $upgrades as $upgrade ) {
			if ( is_callable( $upgrade ) ) {
				$upgrade();
			}
		}

		// Update version post upgrade(s).
		update_option( 'wp_mail_smtp_version', WPMS_PLUGIN_VER );
	}

	/**
	 * Whether we need to perform an upgrade.
	 *
	 * @since 1.1.0
	 *
	 * @return array
	 */
	protected function upgrades() {

		$version = get_option( 'wp_mail_smtp_version' );

		/**
		 * Filters the list of upgrade callbacks to run.
		 *
		 * @since 4.4.0
		 *
		 * @param array  $upgrades List of upgrade callbacks to run.
		 * @param string $version  Latest installed version of the plugin.
		 */
		$upgrades = apply_filters( 'wp_mail_smtp_upgrade_upgrades', [], $version );

		// Version 1.1.0 upgrade; prior to this the option was not available.
		if ( empty( $version ) ) {
			$upgrades[] = [ $this, 'v110_upgrade' ];
		}

		return $upgrades;
	}

	/**
	 * Upgrade routine for v1.1.0.
	 *
	 * Set SMTPAutoTLS to true.
	 *
	 * @since 1.1.0
	 */
	public function v110_upgrade() {

		// Enable SMTPAutoTLS option.
		$values = [
			'smtp' => [
				'autotls' => true,
			],
		];

		Options::init()->set( $values, false, false );
	}
}
