<?php

namespace WPForms\Admin\Splash;

use WPForms\Migrations\Base as MigrationsBase;

trait SplashTrait {

	/**
	 * Default plugin version.
	 *
	 * @since 1.9.3
	 *
	 * @var string
	 */
	private $default_plugin_version = '1.8.6'; // The last version before the "What's New?" feature.

	/**
	 * Previous plugin version.
	 *
	 * @since 1.9.3
	 *
	 * @var string
	 */
	private $previous_plugin_version;

	/**
	 * Latest splash version.
	 *
	 * @since 1.9.3
	 *
	 * @var string
	 */
	private $latest_splash_version;

	/**
	 * Get the latest splash version.
	 *
	 * @since 1.8.7
	 *
	 * @return string Splash version.
	 */
	private function get_latest_splash_version(): string {

		if ( $this->latest_splash_version ) {
			return $this->latest_splash_version;
		}

		$this->latest_splash_version = get_option( 'wpforms_splash_version', '' );

		// Create option if it doesn't exist.
		if ( empty( $this->latest_splash_version ) ) {
			$this->latest_splash_version = $this->default_plugin_version;

			update_option( 'wpforms_splash_version', $this->latest_splash_version );
		}

		return $this->latest_splash_version;
	}

	/**
	 * Update option with the latest splash version.
	 *
	 * @since 1.8.7
	 */
	private function update_splash_version(): void {

		update_option( 'wpforms_splash_version', $this->get_major_version( WPFORMS_VERSION ) );
	}

	/**
	 * Get user license type.
	 *
	 * @since 1.8.8
	 *
	 * @return string
	 */
	private function get_user_license(): string {

		$license = wpforms_get_license_type();

		if ( empty( $license ) ) {
			$license = 'lite';
		}

		/**
		 * License type used for splash screen.
		 *
		 * @since 1.8.8
		 *
		 * @param string $license License type.
		 */
		return (string) apply_filters( 'wpforms_admin_splash_splashtrait_get_user_license', $license );
	}

	/**
	 * Get user version.
	 *
	 * @since 1.9.7
	 *
	 * @return string User version.
	 */
	private function get_user_version(): string {

		/**
		 * User version used for splash screen.
		 *
		 * @since 1.9.7
		 *
		 * @param string $version User version.
		 */
		return (string) apply_filters( 'wpforms_admin_splash_splashtrait_get_user_version', $this->get_major_version( WPFORMS_VERSION ) );
	}

	/**
	 * Get default splash modal data.
	 *
	 * @since 1.8.7
	 *
	 * @return array Splash modal data.
	 */
	private function get_default_data(): array {

		return [
			'license'        => $this->get_user_license(),
			'display_notice' => false,
			'update_url'     => $this->get_update_url(),
			'buttons'        => [
				'get_started' => __( 'Get Started', 'wpforms-lite' ),
				'learn_more'  => __( 'Learn More', 'wpforms-lite' ),
			],
			'header'         => [
				'image'       => WPFORMS_PLUGIN_URL . 'assets/images/splash/sullie.svg',
				'title'       => __( 'What’s New in WPForms', 'wpforms-lite' ),
				'description' => __( 'We\'ve added some great new features to help grow your business and generate more leads. Here are the highlights...', 'wpforms-lite' ),
			],
			'footer'         => [
				'title'       => __( 'Start Building Smarter WordPress Forms', 'wpforms-lite' ),
				'description' => __( 'Add advanced form fields and conditional logic, plus offer more payment options, manage entries, and connect to your favorite marketing tools – all when you purchase a premium plan.', 'wpforms-lite' ),
				'upgrade'     => [
					'text' => __( 'Upgrade to Pro Today', 'wpforms-lite' ),
					'url'  => wpforms_admin_upgrade_link( 'splash-modal', 'Upgrade to Pro Today' ),
				],
			],
		];
	}

	/**
	 * Get plugin update URL.
	 *
	 * @since 1.9.7
	 *
	 * @return string Update URL.
	 */
	private function get_update_url(): string {

		$plugin_slug = defined( 'WPFORMS_PLUGIN_DIR' ) ? plugin_basename( WPFORMS_PLUGIN_DIR ) : 'wpforms';
		$plugin_path = $plugin_slug . '/wpforms.php';

		return wp_nonce_url(
			self_admin_url( 'update.php?action=upgrade-plugin&plugin=' . $plugin_path ),
			'upgrade-plugin_' . $plugin_path
		);
	}

	/**
	 * Prepare buttons.
	 *
	 * @since 1.8.7
	 *
	 * @param array $buttons Buttons.
	 *
	 * @return array Prepared buttons.
	 */
	private function prepare_buttons( array $buttons ): array {

		return array_map(
			function ( $button ) {
				return [
					'url'  => $this->prepare_url( $button['url'] ),
					'text' => $button['text'],
				];
			},
			$buttons
		);
	}

	/**
	 * Prepare URL.
	 *
	 * @since 1.8.7
	 *
	 * @param string $url URL.
	 *
	 * @return string Prepared URL.
	 */
	private function prepare_url( string $url ): string {

		$replace_tags = [
			'{admin_url}'   => admin_url(),
			'{license_key}' => wpforms_get_license_key(),
		];

		return str_replace( array_keys( $replace_tags ), array_values( $replace_tags ), $url );
	}

	/**
	 * Get block layout.
	 *
	 * @since 1.8.7
	 *
	 * @param array $image Image data.
	 *
	 * @return string Block layout.
	 */
	private function get_block_layout( array $image ): string {

		$image_type = $image['type'] ?? 'icon';

		switch ( $image_type ) {
			case 'icon':
				$layout = 'one-third-two-thirds';
				break;

			case 'illustration':
				$layout = 'fifty-fifty';
				break;

			default:
				$layout = 'full-width';
				break;
		}

		return $layout;
	}

	/**
	 * Get a major version.
	 *
	 * @since 1.8.7.2
	 *
	 * @param string $version Version.
	 *
	 * @return string Major version.
	 */
	private function get_major_version( $version ): string {

		// Allow only digits and dots.
		$clean_version = preg_replace( '/[^0-9.]/', '.', $version );

		// Get version parts.
		$version_parts = explode( '.', $clean_version );

		// If a version has more than 3 parts - use only first 3. Get block data only for major versions.
		if ( count( $version_parts ) > 3 ) {
			$version = implode( '.', array_slice( $version_parts, 0, 3 ) );
		}

		return $version;
	}

	/**
	 * Get the WPForms plugin previous version.
	 *
	 * @since 1.8.8
	 *
	 * @return string Previous WPForms version.
	 */
	private function get_previous_plugin_version(): string {

		if ( $this->previous_plugin_version ) {
			return $this->previous_plugin_version;
		}

		$this->previous_plugin_version = get_option( MigrationsBase::PREVIOUS_CORE_VERSION_OPTION_NAME, '' );

		if ( empty( $this->previous_plugin_version ) ) {
			$this->previous_plugin_version = $this->default_plugin_version; // The last version before the "What's New?" feature.
		}

		return $this->previous_plugin_version;
	}
}
