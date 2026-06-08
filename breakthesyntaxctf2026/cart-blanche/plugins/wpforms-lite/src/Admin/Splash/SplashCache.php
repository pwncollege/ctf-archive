<?php

namespace WPForms\Admin\Splash;

use WPForms\Helpers\CacheBase;

/**
 * Splash cache handler.
 *
 * @since 1.8.7
 */
class SplashCache extends CacheBase {

	use SplashTrait;

	/**
	 * Remote source URL.
	 *
	 * @since 1.8.7
	 *
	 * @var string
	 */
	public const REMOTE_SOURCE = 'https://wpformsapi.com/feeds/v1/splash/';

	/**
	 * Determine if the class is allowed to load.
	 *
	 * @since 1.8.7
	 *
	 * @return bool
	 */
	protected function allow_load(): bool {

		return is_admin() || wp_doing_cron() || wpforms_doing_wp_cli();
	}

	/**
	 * Provide settings.
	 *
	 * @since 1.8.7
	 *
	 * @return array Settings array.
	 */
	protected function setup(): array {

		return [

			// Remote source URL.
			'remote_source' => $this->get_remote_source(),

			// Splash cache file name.
			'cache_file'    => 'splash.json',

			/**
			 * Time-to-live of the splash cache file in seconds.
			 *
			 * This applies to `uploads/wpforms/cache/splash.json` file.
			 *
			 * @since 1.8.7
			 *
			 * @param integer $cache_ttl Cache time-to-live, in seconds.
			 *                           Default value: WEEK_IN_SECONDS.
			 */
			'cache_ttl'     => (int) apply_filters( 'wpforms_admin_splash_cache_ttl', WEEK_IN_SECONDS ),
		];
	}

	/**
	 * Get remote source URL.
	 *
	 * @since 1.8.7
	 *
	 * @return string
	 */
	protected function get_remote_source(): string {

		return defined( 'WPFORMS_SPLASH_REMOTE_SOURCE' ) ? WPFORMS_SPLASH_REMOTE_SOURCE : self::REMOTE_SOURCE;
	}

	/**
	 * Prepare splash modal data.
	 *
	 * @since 1.8.7
	 *
	 * @param array $data Splash modal data.
	 */
	protected function prepare_cache_data( $data ): array {

		if ( empty( $data ) || ! is_array( $data ) ) {
			return [];
		}

		$blocks = $this->prepare_blocks( $data );

		if ( empty( $blocks ) ) {
			return [];
		}

		$prepared_data['blocks'] = $blocks;

		return $prepared_data;
	}

	/**
	 * Prepare blocks.
	 *
	 * @since 1.8.7
	 *
	 * @param array $data Splash modal data.
	 *
	 * @return array Prepared blocks.
	 */
	private function prepare_blocks( array $data ): array {

		$user_license = $this->get_user_license();
		$user_version = $this->get_user_version();

		// Filter data by plugin version.
		$blocks = array_filter(
			$data,
			static function ( $block ) use ( $user_license ) {

				// Return only blocks that match the user license.
				return in_array( $user_license, $block['type'] ?? [], true );
			}
		);

		// Get the latest 10 blocks.
		$blocks = array_slice( $blocks, 0, 10 );

		// Reset indexes.
		$blocks = array_values( $blocks );

		return array_map(
			function ( $block ) use ( $user_version ) {

				$block_version = $block['version'] ?? '';

				// Prepare buttons URLs.
				$block['buttons'] = $this->prepare_buttons( $block['btns'] ?? [] );

				// Change main button URL if the block version is greater than the user version.
				if ( version_compare( $block_version, $user_version, '>' ) ) {
					$block['buttons']['main'] = [
						'url'  => $this->get_update_url(),
						'text' => __( 'Update Now', 'wpforms-lite' ),
					];
				}

				// If the block version is less than the user version, set 'new' to false.
				if ( version_compare( $block_version, $user_version, '<' ) ) {
					$block['new'] = false;
				}

				// Set layout based on an image type.
				$block['layout'] = $this->get_block_layout( $block['img'] );

				unset( $block['btns'] );

				return $block;
			},
			$blocks,
			array_keys( $blocks )
		) ?? [];
	}
}
