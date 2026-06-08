<?php

namespace WPMailSMTP;

use WPMailSMTP\Helpers\Helpers;

/**
 * Class WP provides WordPress shortcuts.
 *
 * @since 1.0.0
 */
class WP {

	/**
	 * The "queue" of notices.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected static $admin_notices = [];

	/**
	 * CSS class for a success notice.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const ADMIN_NOTICE_SUCCESS = 'notice-success';

	/**
	 * CSS class for an error notice.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const ADMIN_NOTICE_ERROR = 'notice-error';

	/**
	 * CSS class for an info notice.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const ADMIN_NOTICE_INFO = 'notice-info';

	/**
	 * CSS class for a warning notice.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const ADMIN_NOTICE_WARNING = 'notice-warning';

	/**
	 * Cross-platform line break.
	 *
	 * @since 3.4.0
	 *
	 * @var string
	 */
	const EOL = "\r\n";

	/**
	 * True if WP is processing an AJAX call.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function is_doing_ajax() {

		if ( function_exists( 'wp_doing_ajax' ) ) {
			return wp_doing_ajax();
		}

		return ( defined( 'DOING_AJAX' ) && DOING_AJAX );
	}

	/**
	 * True if I am in the Admin Panel, not doing AJAX.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function in_wp_admin() {

		return ( is_admin() && ! self::is_doing_ajax() );
	}

	/**
	 * Add a notice to the "queue of notices".
	 *
	 * @since 1.0.0
	 * @since 1.5.0 Added `$is_dismissible` param.
	 *
	 * @param string $message        Message text (HTML is OK).
	 * @param string $class          Display class (severity).
	 * @param bool   $is_dismissible Whether the message should be dismissible.
	 * @param string $key            Unique key for the notice. If defined, dismissible notice will be dismissed permanently.
	 */
	public static function add_admin_notice( $message, $class = self::ADMIN_NOTICE_INFO, $is_dismissible = true, $key = '', $error_code = '' ) {

		self::$admin_notices[] = [
			'message'        => $message,
			'class'          => $class,
			'is_dismissible' => (bool) $is_dismissible,
			'key'            => sanitize_key( $key ),
			'error_code'     => $error_code,
		];
	}

	/**
	 * Display all notices.
	 *
	 * @since 1.0.0
	 * @since 1.5.0 Allow the notice to be dismissible, remove the id attribute, which is not unique.
	 */
	public static function display_admin_notices() {

		$has_notices = false;

		foreach ( (array) self::$admin_notices as $notice ) :
			$is_dismissible = $notice['is_dismissible'];
			$dismissible    = $is_dismissible ? 'is-dismissible' : '';

			if (
				$is_dismissible &&
				! empty( $notice['key'] ) &&
				(bool) get_user_meta( get_current_user_id(), "wp_mail_smtp_notice_{$notice['key']}_dismissed", true )
			) {
				continue;
			}

			$has_notices = true;
			?>

			<div class="notice wp-mail-smtp-notice <?php echo esc_attr( $notice['class'] ); ?> <?php echo esc_attr( $dismissible ); ?>" <?php echo ! empty( $notice['key'] ) ? 'data-notice="' . esc_attr( $notice['key'] ) . '"' : ''; ?>>
				<p>
					<?php echo wp_kses_post( $notice['message'] ); ?>
				</p>
				<?php if ( ! empty( $notice['error_code'] ) ) : ?>
					<div class="wp-mail-smtp-notice__error-code">
						<code><?php echo esc_html( $notice['error_code'] ); ?></code>
						<button type="button" class="wp-mail-smtp-notice__copy-btn" title="<?php esc_attr_e( 'Copy error code', 'wp-mail-smtp' ); ?>">
							<svg class="wp-mail-smtp-notice__icon-copy" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M433.941 65.941l-51.882-51.882A48 48 0 0 0 348.118 0H176c-26.51 0-48 21.49-48 48v48H48c-26.51 0-48 21.49-48 48v320c0 26.51 21.49 48 48 48h224c26.51 0 48-21.49 48-48v-48h80c26.51 0 48-21.49 48-48V99.882a48 48 0 0 0-14.059-33.941zM266 464H54a6 6 0 0 1-6-6V150a6 6 0 0 1 6-6h74v224c0 26.51 21.49 48 48 48h96v42a6 6 0 0 1-6 6zm128-96H182a6 6 0 0 1-6-6V54a6 6 0 0 1 6-6h106v88c0 13.255 10.745 24 24 24h88v202a6 6 0 0 1-6 6zm6-256h-64V48h9.632c1.591 0 3.117.632 4.243 1.757l48.368 48.368a6 6 0 0 1 1.757 4.243V112z"/></svg>
							<svg class="wp-mail-smtp-notice__icon-check" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" style="display:none;"><path fill="#00A32A" d="M256 512c141.4 0 256-114.6 256-256S397.4 0 256 0S0 114.6 0 256S114.6 512 256 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"/></svg>
						</button>
					</div>
				<?php endif; ?>
			</div>

			<?php
		endforeach;

		if ( $has_notices ) {
			wp_enqueue_script(
				'wp-mail-smtp-admin-notices',
				wp_mail_smtp()->assets_url . '/js/smtp-admin-notices' . self::asset_min() . '.js',
				[ 'jquery' ],
				WPMS_PLUGIN_VER,
				true
			);

			wp_localize_script(
				'wp-mail-smtp-admin-notices',
				'wp_mail_smtp_admin_notices',
				[
					'nonce' => wp_create_nonce( 'wp-mail-smtp-admin' ),
				]
			);
		}
	}

	/**
	 * Check whether WP_DEBUG is active.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function is_debug() {

		return defined( 'WP_DEBUG' ) && WP_DEBUG;
	}

	/**
	 * Shortcut to global $wpdb.
	 *
	 * @since 1.0.0
	 *
	 * @return \wpdb
	 */
	public static function wpdb() {
		global $wpdb;

		return $wpdb;
	}

	/**
	 * Get the postfix for assets files - ".min" or empty.
	 * ".min" if in production mode.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public static function asset_min() {

		$min = '.min';

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$min = '';
		}

		return $min;
	}

	/**
	 * Check whether the string is a JSON or not.
	 *
	 * @since 1.5.0
	 *
	 * @param string $string String we want to test if it's json.
	 *
	 * @return bool
	 */
	public static function is_json( $string ) {

		return is_string( $string ) && is_array( json_decode( $string, true ) ) && ( json_last_error() === JSON_ERROR_NONE ) ? true : false;
	}

	/**
	 * Get the full date format as per WP options.
	 *
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public static function datetime_format() {

		return sprintf( /* translators: %1$s - date, \a\t - specially escaped "at", %2$s - time. */
			esc_html__( '%1$s \a\t %2$s', 'wp-mail-smtp' ),
			get_option( 'date_format' ),
			get_option( 'time_format' )
		);
	}

	/**
	 * Get the full date form as per MySQL format.
	 *
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public static function datetime_mysql_format() {

		return 'Y-m-d H:i:s';
	}

	/**
	 * Sanitize the value, similar to `sanitize_text_field()`, but a bit differently.
	 * It preserves `<` and `>` for non-HTML tags.
	 *
	 * @since 1.5.0
	 *
	 * @param string $value String we want to sanitize.
	 *
	 * @return string
	 */
	public static function sanitize_value( $value ) {

		// Remove HTML tags.
		$filtered = wp_strip_all_tags( $value, false );
		// Remove multi-lines/tabs.
		$filtered = preg_replace( '/[\r\n\t ]+/', ' ', $filtered );
		// Remove whitespaces.
		$filtered = trim( $filtered );

		// Remove octets.
		$found = false;
		while ( preg_match( '/%[a-f0-9]{2}/i', $filtered, $match ) ) {
			$filtered = str_replace( $match[0], '', $filtered );
			$found    = true;
		}

		if ( $found ) {
			// Strip out the whitespace that may now exist after removing the octets.
			$filtered = trim( preg_replace( '/ +/', ' ', $filtered ) );
		}

		return $filtered;
	}

	/**
	 * Get default email address.
	 *
	 * This is the same code as used in WP core for getting the default email address.
	 *
	 * @see https://github.com/WordPress/WordPress/blob/master/wp-includes/pluggable.php#L332
	 *
	 * @since 2.2.0
	 * @since 2.3.0 In WP 5.5 the core code changed and is now using `network_home_url`.
	 *
	 * @return string
	 */
	public static function get_default_email() {

		if ( version_compare( get_bloginfo( 'version' ), '5.5-alpha', '<' ) ) {
			$sitename = ! empty( $_SERVER['SERVER_NAME'] ) ?
				strtolower( sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) ) :
				wp_parse_url( get_home_url( get_current_blog_id() ), PHP_URL_HOST );
		} else {
			$sitename = wp_parse_url( network_home_url(), PHP_URL_HOST );
		}

		if ( 'www.' === substr( $sitename, 0, 4 ) ) {
			$sitename = substr( $sitename, 4 );
		}

		return 'wordpress@' . $sitename;
	}

	/**
	 * Wrapper for the WP `admin_url` method that should be used in the plugin.
	 *
	 * We can filter into it, to maybe call `network_admin_url` for multisite support.
	 *
	 * @since 2.2.0
	 *
	 * @param string $path   Optional path relative to the admin URL.
	 * @param string $scheme The scheme to use. Default is 'admin', which obeys force_ssl_admin() and is_ssl().
	 *                       'http' or 'https' can be passed to force those schemes.
	 *
	 * @return string Admin URL link with optional path appended.
	 */
	public static function admin_url( $path = '', $scheme = 'admin' ) {

		return apply_filters( 'wp_mail_smtp_admin_url', \admin_url( $path, $scheme ), $path, $scheme );
	}

	/**
	 * Check if the global plugin option in a multisite should be used.
	 * If the global plugin option "multisite" is set and true.
	 *
	 * @since 2.2.0
	 *
	 * @return bool
	 */
	public static function use_global_plugin_settings() {

		if ( ! is_multisite() ) {
			return false;
		}

		$main_site_options = get_blog_option( get_main_site_id(), Options::META_KEY, [] );

		return ! empty( $main_site_options['general']['network_wide'] );
	}

	/**
	 * Returns Jed-formatted localization data.
	 * This code was taken from a function removed from WP core: `wp_get_jed_locale_data`.
	 *
	 * @since 2.6.0
	 *
	 * @param string $domain Translation domain.
	 *
	 * @return array
	 */
	public static function get_jed_locale_data( $domain ) {

		$translations = get_translations_for_domain( $domain );

		$locale = array(
			'' => array(
				'domain' => $domain,
				'lang'   => is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale(),
			),
		);

		if ( ! empty( $translations->headers['Plural-Forms'] ) ) {
			$locale['']['plural_forms'] = $translations->headers['Plural-Forms'];
		}

		foreach ( $translations->entries as $entry ) {
			$locale[ $entry->singular ] = $entry->translations;
		}

		return $locale;
	}

	/**
	 * Check if plugins is activated.
	 * Replacement for is_plugin_active function as it works only in admin area
	 *
	 * @since 2.8.0
	 *
	 * @param string $plugin_slug Plugin slug.
	 *
	 * @return bool
	 */
	public static function is_plugin_activated( $plugin_slug ) {

		static $active_plugins;

		if ( ! isset( $active_plugins ) ) {
			$active_plugins = (array) get_option( 'active_plugins', [] );

			if ( is_multisite() ) {
				$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', [] ) );
			}
		}

		return ( in_array( $plugin_slug, $active_plugins, true ) || array_key_exists( $plugin_slug, $active_plugins ) );
	}

	/**
	 * Get the ISO 639-2 Language Code from user/site locale.
	 *
	 * @see   http://www.loc.gov/standards/iso639-2/php/code_list.php
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public static function get_language_code() {

		$default_lang = 'en';
		$locale       = get_user_locale();

		if ( ! empty( $locale ) ) {
			$lang = explode( '_', $locale );
			if ( ! empty( $lang ) && is_array( $lang ) ) {
				$default_lang = strtolower( $lang[0] );
			}
		}

		return $default_lang;
	}

	/**
	 * Get the certain date of a specified day in a specified format.
	 *
	 * @since 2.8.0
	 *
	 * @param string $period         Supported values: start, end.
	 * @param string $timestamp      Default is the current timestamp, if left empty.
	 * @param string $format         Default is a MySQL format.
	 * @param bool   $use_gmt_offset Use GTM offset.
	 *
	 * @return string
	 */
	public static function get_day_period_date( $period, $timestamp = '', $format = 'Y-m-d H:i:s', $use_gmt_offset = false ) {

		$date = '';

		if ( empty( $timestamp ) ) {
			$timestamp = time();
		}

		$offset_sec = $use_gmt_offset ? get_option( 'gmt_offset' ) * 3600 : 0;

		switch ( $period ) {
			case 'start_of_day':
				$date = gmdate( $format, strtotime( 'today', $timestamp ) - $offset_sec );
				break;

			case 'end_of_day':
				$date = gmdate( $format, strtotime( 'tomorrow', $timestamp ) - 1 - $offset_sec );
				break;
		}

		return $date;
	}

	/**
	 * Returns extracted domain from email address.
	 *
	 * @since 2.8.0
	 *
	 * @param string $email Email address.
	 *
	 * @return string
	 */
	public static function get_email_domain( $email ) {

		return substr( strrchr( $email, '@' ), 1 );
	}

	/**
	 * Wrapper for set_time_limit to see if it is enabled.
	 *
	 * @since 2.8.0
	 *
	 * @param int $limit Time limit.
	 */
	public static function set_time_limit( $limit = 0 ) {

		if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) { // phpcs:ignore PHPCompatibility.IniDirectives.RemovedIniDirectives.safe_modeDeprecatedRemoved
			@set_time_limit( $limit ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		}
	}

	/**
	 * Recursive arguments parsing.
	 *
	 * @since 2.8.0
	 *
	 * @param array $args     Arguments.
	 * @param array $defaults Defaults.
	 *
	 * @return array
	 */
	public static function parse_args_r( &$args, $defaults ) {

		$args     = (array) $args;
		$defaults = (array) $defaults;
		$r        = $defaults;

		foreach ( $args as $k => &$v ) {
			if ( is_array( $v ) && isset( $r[ $k ] ) ) {
				$r[ $k ] = self::parse_args_r( $v, $r[ $k ] );
			} else {
				$r[ $k ] = $v;
			}
		}

		return $r;
	}

	/**
	 * True if WP is processing plugin related AJAX call.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	public static function is_doing_self_ajax() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action = isset( $_REQUEST['action'] ) ? sanitize_key( $_REQUEST['action'] ) : false;

		return self::is_doing_ajax() && $action && substr( $action, 0, 12 ) === 'wp_mail_smtp';
	}

	/**
	 * Get the name of the plugin/theme/wp-core that initiated the desired function call.
	 *
	 * @since 3.0.0
	 *
	 * @param string $file_path The absolute path of a file that that called the desired function.
	 *
	 * @return string
	 */
	public static function get_initiator_name( $file_path ) {

		return self::get_initiator( $file_path )['name'];
	}

	/**
	 * Get the info of the plugin/theme/wp-core function.
	 *
	 * @since 3.5.0
	 *
	 * @param string $file_path The absolute path of the function location.
	 *
	 * @return array
	 */
	public static function get_initiator( $file_path ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		$cache_key = 'wp_mail_smtp_initiators_data';

		// Mainly we have several initiators and we can cache them for better performance.
		$initiators_cache = get_transient( $cache_key );
		$initiators_cache = is_array( $initiators_cache ) ? $initiators_cache : [];

		if ( isset( $initiators_cache[ $file_path ] ) ) {
			return $initiators_cache[ $file_path ];
		}

		$initiator = self::get_initiator_plugin( $file_path );

		// Change the initiator name if the email was sent from the reloaded method in the email controls.
		if (
			! empty( $initiator ) &&
			strpos( str_replace( '\\', '/', $file_path ), 'src/Pro/Emails/Control/Reload.php' )
		) {
			$initiator['name'] = sprintf( /* translators: %s - plugin name. */
				esc_html__( 'WP Core (%s)', 'wp-mail-smtp' ),
				$initiator['name']
			);
		}

		if ( empty( $initiator ) ) {
			$initiator = self::get_initiator_plugin( $file_path, true );
		}

		if ( empty( $initiator ) ) {
			$initiator = self::get_initiator_theme( $file_path );
		}

		if ( empty( $initiator ) ) {
			$initiator = self::get_initiator_wp_core( $file_path );
		}

		if ( empty( $initiator ) ) {
			$initiator         = [];
			$initiator['name'] = esc_html__( 'N/A', 'wp-mail-smtp' );
			$initiator['slug'] = '';
			$initiator['type'] = 'unknown';
		}

		$initiators_cache[ $file_path ] = $initiator;

		set_transient( $cache_key, $initiators_cache, HOUR_IN_SECONDS );

		return $initiator;
	}

	/**
	 * Get the initiator's data, if it's a plugin (or mu plugin).
	 *
	 * @since 3.0.0
	 *
	 * @param string $file_path       The absolute path of a file.
	 * @param bool   $check_mu_plugin Whether to check for mu plugins or not.
	 *
	 * @return false|array
	 */
	private static function get_initiator_plugin( $file_path, $check_mu_plugin = false ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh, Generic.Metrics.CyclomaticComplexity.MaxExceeded

		$constant = empty( $check_mu_plugin ) ? 'WP_PLUGIN_DIR' : 'WPMU_PLUGIN_DIR';

		if ( ! defined( $constant ) ) {
			return false;
		}

		$root      = basename( constant( $constant ) );
		$separator = defined( 'DIRECTORY_SEPARATOR' ) ? '\\' . DIRECTORY_SEPARATOR : '\/';

		preg_match( "/$separator$root$separator(.[^$separator]+)($separator|\.php)/", $file_path, $result );

		if ( ! empty( $result[1] ) ) {
			if ( ! function_exists( 'get_plugins' ) ) {
				include ABSPATH . '/wp-admin/includes/plugin.php';
			}

			$all_plugins = empty( $check_mu_plugin ) ? get_plugins() : get_mu_plugins();
			$plugin_slug = $result[1];

			foreach ( $all_plugins as $plugin => $plugin_data ) {
				if (
					1 === preg_match( "/^$plugin_slug(\/|\.php)/", $plugin ) &&
					isset( $plugin_data['Name'] )
				) {
					return [
						'name' => $plugin_data['Name'],
						'slug' => $plugin,
						'type' => $check_mu_plugin ? 'mu-plugin' : 'plugin',
					];
				}
			}

			return [
				'name' => $result[1],
				'slug' => '',
				'type' => $check_mu_plugin ? 'mu-plugin' : 'plugin',
			];
		}

		return false;
	}

	/**
	 * Get the initiator's data, if it's a theme.
	 *
	 * @since 3.0.0
	 *
	 * @param string $file_path The absolute path of a file.
	 *
	 * @return false|array
	 */
	private static function get_initiator_theme( $file_path ) {

		if ( ! defined( 'WP_CONTENT_DIR' ) ) {
			return false;
		}

		$root      = basename( WP_CONTENT_DIR );
		$separator = defined( 'DIRECTORY_SEPARATOR' ) ? '\\' . DIRECTORY_SEPARATOR : '\/';

		preg_match( "/$separator$root{$separator}themes{$separator}(.[^$separator]+)/", $file_path, $result );

		if ( ! empty( $result[1] ) ) {
			$theme = wp_get_theme( $result[1] );

			return [
				'name' => method_exists( $theme, 'get' ) ? $theme->get( 'Name' ) : $result[1],
				'slug' => $result[1],
				'type' => 'theme',
			];
		}

		return false;
	}

	/**
	 * Return WP Core if the file path is from WP Core (wp-admin or wp-includes folders).
	 *
	 * @since 3.1.0
	 *
	 * @param string $file_path The absolute path of a file.
	 *
	 * @return false|array
	 */
	private static function get_initiator_wp_core( $file_path ) {

		if ( ! defined( 'ABSPATH' ) ) {
			return false;
		}

		$wp_includes = defined( 'WPINC' ) ? trailingslashit( ABSPATH . WPINC ) : false;
		$wp_admin    = trailingslashit( ABSPATH . 'wp-admin' );

		if (
			strpos( $file_path, $wp_includes ) === 0 ||
			strpos( $file_path, $wp_admin ) === 0
		) {
			return [
				'name' => esc_html__( 'WP Core', 'wp-mail-smtp' ),
				'slug' => 'wp-core',
				'type' => 'wp-core',
			];
		}

		return false;
	}

	/**
	 * Retrieves the timezone from site settings as a `DateTimeZone` object.
	 *
	 * Timezone can be based on a PHP timezone string or a ±HH:MM offset.
	 *
	 * We use `wp_timezone()` when it's available (WP 5.3+),
	 * otherwise fallback to the same code, copy-pasted.
	 *
	 * @since 3.0.2
	 *
	 * @return \DateTimeZone Timezone object.
	 */
	public static function wp_timezone() {

		if ( function_exists( 'wp_timezone' ) ) {
			return wp_timezone();
		}

		return new \DateTimeZone( self::wp_timezone_string() );
	}

	/**
	 * Retrieves the timezone from site settings as a string.
	 *
	 * Uses the `timezone_string` option to get a proper timezone if available,
	 * otherwise falls back to an offset.
	 *
	 * We use `wp_timezone_string()` when it's available (WP 5.3+),
	 * otherwise fallback to the same code, copy-pasted.
	 *
	 * @since 3.0.2
	 *
	 * @return string PHP timezone string or a ±HH:MM offset.
	 */
	public static function wp_timezone_string() {

		if ( function_exists( 'wp_timezone_string' ) ) {
			return wp_timezone_string();
		}

		$timezone_string = get_option( 'timezone_string' );

		if ( $timezone_string ) {
			return $timezone_string;
		}

		$offset  = (float) get_option( 'gmt_offset' );
		$hours   = (int) $offset;
		$minutes = ( $offset - $hours );

		$sign      = ( $offset < 0 ) ? '-' : '+';
		$abs_hour  = abs( $hours );
		$abs_mins  = abs( $minutes * 60 );
		$tz_offset = sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );

		return $tz_offset;
	}

	/**
	 * Get wp remote response error message.
	 *
	 * @since 3.4.0
	 *
	 * @param array $response Response array.
	 */
	public static function wp_remote_get_response_error_message( $response ) {

		if ( is_wp_error( $response ) ) {
			return '';
		}

		$body        = wp_remote_retrieve_body( $response );
		$message     = wp_remote_retrieve_response_message( $response );
		$code        = wp_remote_retrieve_response_code( $response );
		$description = '';

		if ( ! empty( $body ) ) {
			$description = is_string( $body ) ? $body : wp_json_encode( $body );
		}

		return Helpers::format_error_message( $message, $code, $description );
	}

	/**
	 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
	 * Non-string values are ignored.
	 *
	 * @since 3.7.0
	 *
	 * @param string|array $var Data to sanitize.
	 *
	 * @return string|array
	 */
	public static function sanitize_text( $var ) {

		if ( is_array( $var ) ) {
			return array_map( [ __CLASS__, 'sanitize_text' ], $var );
		} else {
			return is_string( $var ) ? sanitize_text_field( $var ) : $var;
		}
	}

	/**
	 * Get the current site URL,
	 * or the network URL if using network-wide settings.
	 *
	 * @since 4.4.0
	 *
	 * @return string
	 */
	public static function get_site_url() {

		$site_id = null;

		if ( self::use_global_plugin_settings() ) {
			$site_id = get_main_site_id();
		}

		/**
		 * Whether to return the unfiltered site URL.
		 *
		 * @since 4.6.0
		 *
		 * @param bool $unfiltered Whether to return the unfiltered site URL.
		 *
		 * @return bool
		 */
		if ( apply_filters( 'wp_mail_smtp_wp_get_site_url_unfiltered', false ) ) {
			return self::get_raw_site_url( $site_id );
		}

		return get_site_url( $site_id );
	}

	/**
	 * Get the raw/unfiltered site URL.
	 *
	 * @since 4.6.0
	 *
	 * @param int $site_id The site ID.
	 *
	 * @return string
	 */
	private static function get_raw_site_url( $site_id ) {

		if ( empty( $site_id ) || ! is_multisite() ) {
			$url = get_option( 'siteurl' );
		} else {
			switch_to_blog( $site_id );

			$url = get_option( 'siteurl' );

			restore_current_blog();
		}

		$url = set_url_scheme( $url );

		return $url;
	}
}
