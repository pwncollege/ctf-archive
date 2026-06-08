<?php

namespace WPForms\Admin\Addons;

/**
 * Addons data handler.
 *
 * @since 1.6.6
 */
class Addons {

	/**
	 * Basic license.
	 *
	 * @since 1.8.2
	 */
	const BASIC = 'basic';

	/**
	 * Plus license.
	 *
	 * @since 1.8.2
	 */
	const PLUS = 'plus';

	/**
	 * Pro license.
	 *
	 * @since 1.8.2
	 */
	const PRO = 'pro';

	/**
	 * Elite license.
	 *
	 * @since 1.8.2
	 */
	const ELITE = 'elite';

	/**
	 * Agency license.
	 *
	 * @since 1.8.2
	 */
	const AGENCY = 'agency';

	/**
	 * Ultimate license.
	 *
	 * @since 1.8.2
	 */
	const ULTIMATE = 'ultimate';

	/**
	 * Addons cache object.
	 *
	 * @since 1.6.6
	 *
	 * @var AddonsCache
	 */
	private $cache;

	/**
	 * All Addons data.
	 *
	 * @since 1.6.6
	 *
	 * @var array
	 */
	private $addons;

	/**
	 * WPForms addons text domains.
	 *
	 * @since 1.9.2
	 *
	 * @var array
	 */
	private $addons_text_domains = [];

	/**
	 * WPForms addons titles.
	 *
	 * @since 1.9.2
	 *
	 * @var array
	 */
	private $addons_titles = [];

	/**
	 * Determine if the class is allowed to load.
	 *
	 * @since 1.6.6
	 *
	 * @return bool
	 */
	public function allow_load() {

		global $pagenow;

		$has_permissions = wpforms_current_user_can( [ 'create_forms', 'edit_forms' ] );
		$allowed_pages   = in_array( $pagenow ?? '', [ 'plugins.php', 'update-core.php', 'plugin-install.php' ], true );
		$allowed_ajax    = $pagenow === 'admin-ajax.php' && isset( $_POST['action'] ) && $_POST['action'] === 'update-plugin'; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$allowed_requests = $allowed_pages || $allowed_ajax || wpforms_is_admin_ajax() || wpforms_is_admin_page() || wpforms_is_admin_page( 'builder' );

		return $has_permissions && $allowed_requests;
	}

	/**
	 * Initialize class.
	 *
	 * @since 1.6.6
	 */
	public function init() {

		if ( ! $this->allow_load() ) {
			return;
		}

		$this->cache = wpforms()->obj( 'addons_cache' );

		global $pagenow;

		// Force update addons cache if we are on the update-core.php page.
		// This is necessary to update addons data while checking for all available updates.
		if ( $pagenow === 'update-core.php' ) {
			$this->cache->update( true );
		}

		$this->addons = $this->cache->get();

		$this->populate_addons_data();
		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.6.6
	 */
	protected function hooks() {

		global $pagenow;

		/**
		 * Fire before admin addons init.
		 *
		 * @since 1.6.7
		 */
		do_action( 'wpforms_admin_addons_init' );

		// Filter Gettext only on Plugin list and Updates pages.
		if ( $pagenow === 'update-core.php' || $pagenow === 'plugins.php' ) {
			add_action( 'gettext', [ $this, 'filter_gettext' ], 10, 3 );
		}
	}

	/**
	 * Get all addons data as array.
	 *
	 * @since 1.6.6
	 *
	 * @param bool $force_cache_update Determine if we need to update cache. Default is `false`.
	 *
	 * @return array
	 */
	public function get_all( bool $force_cache_update = false ) {

		if ( ! $this->allow_load() ) {
			return [];
		}

		if ( $force_cache_update ) {
			$this->cache->update( true );

			$this->addons = $this->cache->get();
		}

		// WPForms 1.8.7 core includes Custom Captcha.
		// The Custom Captcha addon will only work on WPForms 1.8.6 and earlier versions.
		unset( $this->addons['wpforms-captcha'] );

		return $this->get_sorted_addons();
	}

	/**
	 * Get sorted addons data.
	 * Recommended addons will be displayed first,
	 * then new addons, then featured addons,
	 * and then all other addons.
	 *
	 * @since 1.8.9
	 *
	 * @return array
	 */
	private function get_sorted_addons(): array {

		if ( empty( $this->addons ) ) {
			return [];
		}

		$recommended = array_filter(
			$this->addons,
			static function ( $addon ) {

				return ! empty( $addon['recommended'] );
			}
		);

		$new = array_filter(
			$this->addons,
			static function ( $addon ) {

				return ! empty( $addon['new'] );
			}
		);

		$featured = array_filter(
			$this->addons,
			static function ( $addon ) {

				return ! empty( $addon['featured'] );
			}
		);

		return array_merge( $recommended, $new, $featured, $this->addons );
	}

	/**
	 * Get filtered addons data.
	 *
	 * Usage:
	 *      ->get_filtered( $this->addons, [ 'category' => 'payments' ] )    - addons for the payments panel.
	 *      ->get_filtered( $this->addons, [ 'license' => 'elite' ] )        - addons available for 'elite' license.
	 *
	 * @since 1.6.6
	 *
	 * @param array $addons Raw addons data.
	 * @param array $args   Arguments array.
	 *
	 * @return array Addons data filtered according to given arguments.
	 */
	private function get_filtered( array $addons, array $args ): array {

		$args = wp_parse_args(
			$args,
			[
				'category' => '',
				'license'  => '',
			]
		);

		$args = array_map( 'strtolower', $args );

		$filtered_addons = [];

		foreach ( $addons as $addon ) {
			foreach ( $args as $arg_key => $arg_value ) {
				$addon_value = wpforms_array_get_by_path( $addon, $arg_key, '' );

				if (
					is_array( $addon_value ) &&
					// We cannot use preg_quote here, as $arg_value could contain regex like 'crm|email-marketing|integration'.
					preg_grep( '/^' . $arg_value . '$/', $addon_value )
				) {
					$filtered_addons[] = $addon;
				}
			}
		}

		return $filtered_addons;
	}

	/**
	 * Get available addons data by category.
	 *
	 * @since 1.6.6
	 *
	 * @param string $category Addon category.
	 *
	 * @return array.
	 */
	public function get_by_category( string $category ) {

		return $this->get_by_path( 'category', $category );
	}

	/**
	 * Get available addons data by path.
	 *
	 * @since 1.9.8.6
	 *
	 * @param string $path  Path in addons multidimensional array.
	 *                      May be 'category' or 'form_builder.category' or 'settings_integrations.category', etc.
	 * @param string $value Addons multidimensional array value we are looking for in the path.
	 *
	 * @return array
	 */
	public function get_by_path( string $path, $value ): array {

		return $this->get_filtered( $this->get_available(), [ $path => $value ] );
	}

	/**
	 * Get available addons data by license.
	 *
	 * @since 1.6.6
	 *
	 * @param string $license Addon license.
	 *
	 * @return array.
	 * @noinspection PhpUnused
	 */
	public function get_by_license( string $license ) {

		return $this->get_filtered( $this->get_available(), [ 'license' => $license ] );
	}

	/**
	 * Get available addons data by slugs.
	 *
	 * @since 1.6.8
	 *
	 * @param array|mixed $slugs Addon slugs.
	 *
	 * @return array
	 */
	public function get_by_slugs( $slugs ) {

		if ( empty( $slugs ) || ! is_array( $slugs ) ) {
			return [];
		}

		$result_addons = [];

		foreach ( $slugs as $slug ) {
			$addon = $this->get_addon( $slug );

			if ( ! empty( $addon ) ) {
				$result_addons[] = $addon;
			}
		}

		return $result_addons;
	}

	/**
	 * Get available addon data by slug.
	 *
	 * @since 1.6.6
	 *
	 * @param string|bool $slug Addon slug can be both "wpforms-drip" and "drip".
	 *
	 * @return array Single addon data. Empty array if addon is not found.
	 */
	public function get_addon( $slug ) {

		$slug = (string) $slug;
		$slug = 'wpforms-' . str_replace( 'wpforms-', '', sanitize_key( $slug ) );

		$addon = $this->get_available()[ $slug ] ?? [];

		// In case if addon is "not available" let's try to get and prepare addon data from all addons.
		if ( empty( $addon ) ) {
			$addon = ! empty( $this->addons[ $slug ] ) ? $this->prepare_addon_data( $this->addons[ $slug ] ) : [];
		}

		return $addon;
	}

	/**
	 * Check if addon is active.
	 *
	 * @since 1.8.9
	 *
	 * @param string $slug Addon slug.
	 *
	 * @return bool
	 */
	public function is_active( string $slug ): bool {

		$addon = $this->get_addon( $slug );

		return isset( $addon['status'] ) && $addon['status'] === 'active';
	}

	/**
	 * Get license level of the addon.
	 *
	 * @since 1.6.6
	 *
	 * @param array|string $addon Addon data array OR addon slug.
	 *
	 * @return string License level: pro | elite.
	 */
	private function get_license_level( $addon ) {

		if ( empty( $addon ) ) {
			return '';
		}

		$levels        = [ self::BASIC, self::PLUS, self::PRO, self::ELITE, self::AGENCY, self::ULTIMATE ];
		$license       = '';
		$addon_license = $this->get_addon_license( $addon );

		foreach ( $levels as $level ) {
			if ( in_array( $level, $addon_license, true ) ) {
				$license = $level;

				break;
			}
		}

		if ( empty( $license ) ) {
			return '';
		}

		return in_array( $license, [ self::BASIC, self::PLUS, self::PRO ], true ) ? self::PRO : self::ELITE;
	}

	/**
	 * Get addon license.
	 *
	 * @since 1.8.2
	 *
	 * @param array|string $addon Addon data array OR addon slug.
	 *
	 * @return array
	 */
	private function get_addon_license( $addon ) {

		$addon = is_string( $addon ) ? $this->get_addon( $addon ) : $addon;

		return $this->default_data( $addon, 'license', [] );
	}

	/**
	 * Determine if a user's license level has access.
	 *
	 * @since 1.6.6
	 *
	 * @param array|string $addon Addon data array OR addon slug.
	 *
	 * @return bool
	 */
	protected function has_access( $addon ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found

		return false;
	}

	/**
	 * Return array of addons available to display. All data is prepared and normalized.
	 * "Available to display" means that addon needs to be displayed as an education item (addon is not installed or not activated).
	 *
	 * @since 1.6.6
	 *
	 * @return array
	 */
	public function get_available() {

		static $available_addons = [];

		if ( $available_addons ) {
			return $available_addons;
		}

		if ( empty( $this->addons ) || ! is_array( $this->addons ) ) {
			return [];
		}

		$available_addons = array_map( [ $this, 'prepare_addon_data' ], $this->addons );
		$available_addons = array_filter(
			$available_addons,
			static function ( $addon ) {

				return isset( $addon['status'], $addon['plugin_allow'] ) && ( $addon['status'] !== 'active' || ! $addon['plugin_allow'] );
			}
		);

		return $available_addons;
	}

	/**
	 * Prepare addon data.
	 *
	 * @since 1.6.6
	 *
	 * @param array|mixed $addon Addon data.
	 *
	 * @return array Extended addon data.
	 */
	protected function prepare_addon_data( $addon ) {

		if ( empty( $addon ) ) {
			return [];
		}

		$addon['title'] = $this->default_data( $addon, 'title', '' );
		$addon['slug']  = $this->default_data( $addon, 'slug', '' );

		// We need the cleared name of the addon, without the 'addon' suffix, for further use.
		$addon['name'] = preg_replace( '/ addon$/i', '', $addon['title'] );

		$addon['modal_name']    = sprintf( /* translators: %s - addon name. */
			esc_html__( '%s addon', 'wpforms-lite' ),
			$addon['name']
		);
		$addon['clear_slug']    = str_replace( 'wpforms-', '', $addon['slug'] );
		$addon['utm_content']   = ucwords( str_replace( '-', ' ', $addon['clear_slug'] ) );
		$addon['license']       = $this->default_data( $addon, 'license', [] );
		$addon['license_level'] = $this->get_license_level( $addon );
		$addon['icon']          = $this->default_data( $addon, 'icon', '' );
		$addon['path']          = sprintf( '%1$s/%1$s.php', $addon['slug'] );
		$addon['video']         = $this->default_data( $addon, 'video', '' );
		$addon['plugin_allow']  = $this->has_access( $addon );
		$addon['status']        = 'missing';
		$addon['action']        = 'upgrade';
		$addon['page_url']      = $this->default_data( $addon, 'url', '' );
		$addon['doc_url']       = $this->default_data( $addon, 'doc', '' );
		$addon['url']           = '';

		static $nonce   = '';
		$nonce          = empty( $nonce ) ? wp_create_nonce( 'wpforms-admin' ) : $nonce;
		$addon['nonce'] = $nonce;

		return $addon;
	}

	/**
	 * Get default data.
	 *
	 * @since 1.8.2
	 *
	 * @param array|mixed $addon        Addon data.
	 * @param string      $key          Key.
	 * @param mixed       $default_data Default data.
	 *
	 * @return array|string|mixed
	 */
	private function default_data( $addon, string $key, $default_data ) {

		if ( is_string( $default_data ) ) {
			return ! empty( $addon[ $key ] ) ? $addon[ $key ] : $default_data;
		}

		if ( is_array( $default_data ) ) {
			return ! empty( $addon[ $key ] ) ? (array) $addon[ $key ] : $default_data;
		}

		return $addon[ $key ] ?? '';
	}

	/**
	 * Populate addons data.
	 *
	 * @since 1.9.2
	 *
	 * @return void
	 */
	private function populate_addons_data() {

		foreach ( $this->addons as $addon ) {
			$this->addons_text_domains[] = $addon['slug'];
			$this->addons_titles[]       = 'WPForms ' . str_replace( ' Addon', '', $addon['title'] );
		}
	}

	/**
	 * Filter Gettext.
	 *
	 * This filter allows us to prevent empty translations from being returned
	 * on the `plugins` page for addon name and description.
	 *
	 * @since 1.9.2
	 *
	 * @param string|mixed $translation Translated text.
	 * @param string|mixed $text        Text to translate.
	 * @param string|mixed $domain      Text domain.
	 *
	 * @return string Translated text.
	 */
	public function filter_gettext( $translation, $text, $domain ): string {

		$translation = (string) $translation;
		$text        = (string) $text;
		$domain      = (string) $domain;

		if ( ! in_array( $domain, $this->addons_text_domains, true ) ) {
			return $translation;
		}

		// Prevent empty translations from being returned and don't translate addon names.
		if ( ! trim( $translation ) || in_array( $text, $this->addons_titles, true ) ) {
			$translation = $text;
		}

		return $translation;
	}
}
