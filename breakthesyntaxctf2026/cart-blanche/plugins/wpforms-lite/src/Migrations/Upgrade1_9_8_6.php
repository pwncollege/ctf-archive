<?php

namespace WPForms\Migrations;

/**
 * Class upgrade for 1.9.8.6 release.
 *
 * @since 1.9.8.6
 */
class Upgrade1_9_8_6 extends UpgradeBase {

	/**
	 * Run upgrade.
	 *
	 * @since 1.9.8.6
	 */
	public function run(): bool {

		$activated_plugins = [];

		$this->check_wpconsent_activation( $activated_plugins );
		$this->check_sugar_calendar_activation( $activated_plugins );
		$this->check_duplicator_activation( $activated_plugins );
		$this->check_uncanny_automator_activation( $activated_plugins );

		add_option( 'wpforms_rotation_activated_plugins', $activated_plugins );

		return true;
	}

	/**
	 * Check WPConsent plugin activation time.
	 *
	 * @since 1.9.8.6
	 *
	 * @param array $activated_plugins Reference to activated plugins array.
	 */
	private function check_wpconsent_activation( array &$activated_plugins ): void {

		$wpconsent = get_option( 'wpconsent_activated' );

		$wpconsent_time = $wpconsent['wpconsent'] ?? null;

		if ( empty( $wpconsent_time ) ) {
			$wpconsent_time = $wpconsent['wpconsent_pro'] ?? null;
		}

		if ( ! empty( $wpconsent_time ) ) {
			$activated_plugins['wpconsent'] = $wpconsent_time;
		}
	}

	/**
	 * Check Sugar Calendar plugin activation time.
	 *
	 * @since 1.9.8.6
	 *
	 * @param array $activated_plugins Reference to activated plugins array.
	 */
	private function check_sugar_calendar_activation( array &$activated_plugins ): void {

		$sugar_calendar_activated_time = get_option( 'sugar_calendar_activated_time' );

		if ( ! empty( $sugar_calendar_activated_time ) ) {
			$activated_plugins['sugar-calendar'] = (int) $sugar_calendar_activated_time;
		}
	}

	/**
	 * Check Duplicator plugin activation time.
	 *
	 * @since 1.9.8.6
	 *
	 * @param array $activated_plugins Reference to activated plugins array.
	 */
	private function check_duplicator_activation( array &$activated_plugins ): void {

		$duplicator_install_info = get_option( 'duplicator_install_info' );

		$duplicator_time = $duplicator_install_info['time'] ?? null;

		if ( empty( $duplicator_time ) ) {
			$duplicator_pro_install_info = get_option( 'duplicator_pro_install_info' );

			$duplicator_time = $duplicator_pro_install_info['time'] ?? null;
		}

		if ( ! empty( $duplicator_time ) ) {
			$activated_plugins['duplicator'] = $duplicator_time;
		}
	}

	/**
	 * Check Uncanny Automator plugin activation time.
	 *
	 * @since 1.9.8.6
	 *
	 * @param array $activated_plugins Reference to activated plugins array.
	 */
	private function check_uncanny_automator_activation( array &$activated_plugins ): void {

		$uncanny_automator_v6_options_migrated = get_option( 'uncanny_automator_v6_options_migrated' );

		if ( ! empty( $uncanny_automator_v6_options_migrated ) ) {
			$activated_plugins['uncanny-automator'] = (int) $uncanny_automator_v6_options_migrated;
		}
	}
}
