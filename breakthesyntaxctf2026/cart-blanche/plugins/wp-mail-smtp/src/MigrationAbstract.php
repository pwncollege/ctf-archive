<?php

namespace WPMailSMTP;

/**
 * Class MigrationAbstract helps migrate plugin options, DB tables and more.
 *
 * @since 3.0.0
 */
abstract class MigrationAbstract {

	/**
	 * Version of the latest migration.
	 *
	 * @since 3.0.0
	 */
	const DB_VERSION = 1;

	/**
	 * Option key where we save the current migration version.
	 *
	 * @since 3.0.0
	 */
	const OPTION_NAME = 'wp_mail_smtp_migration_version';

	/**
	 * Option key where we save any errors while performing migration.
	 *
	 * @since 3.0.0
	 */
	const ERROR_OPTION_NAME = 'wp_mail_smtp_migration_error';

	/**
	 * Current migration version, received from static::OPTION_NAME WP option
	 *
	 * @since 3.0.0
	 *
	 * @var int
	 */
	protected $cur_ver;

	/**
	 * Migration constructor.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		$this->cur_ver = static::get_current_version();
	}

	/**
	 * Initialize migration.
	 *
	 * @since 3.0.0
	 */
	public function init() {

		$this->validate_db();
	}

	/**
	 * Whether migration is enabled.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	public static function is_enabled() {

		return true;
	}

	/**
	 * Static on purpose, to get current DB version without __construct() and validation.
	 *
	 * @since 3.0.0
	 *
	 * @return int
	 */
	public static function get_current_version() {

		return (int) get_option( static::OPTION_NAME, 0 );
	}

	/**
	 * Check DB version and update to the latest one.
	 *
	 * @since 3.0.0
	 */
	protected function validate_db() {

		if ( $this->cur_ver < static::DB_VERSION ) {
			$this->run( static::DB_VERSION );
		}
	}

	/**
	 * Update DB version in options table.
	 *
	 * @since 3.0.0
	 *
	 * @param int $version Version number.
	 */
	protected function update_db_ver( $version = 0 ) {

		$version = (int) $version;

		if ( empty( $version ) ) {
			$version = static::DB_VERSION;
		}

		// Autoload it, because this value is checked all the time
		// and no need to request it separately from all autoloaded options.
		update_option( static::OPTION_NAME, $version, true );
	}

	/**
	 * Prevent running the same migration twice.
	 * Run migration only when required.
	 *
	 * @since 3.0.0
	 *
	 * @param int $version The current migration version.
	 */
	protected function maybe_required_older_migrations( $version ) {

		$version = (int) $version;

		if ( ( $version - $this->cur_ver ) > 1 ) {
			$this->run( $version - 1 );
		}
	}

	/**
	 * Actual migration launcher.
	 *
	 * @since 3.0.0
	 *
	 * @param int $version The specified migration version to run.
	 */
	protected function run( $version ) {

		$version = (int) $version;

		if ( method_exists( $this, 'migrate_to_' . $version ) ) {
			$this->{'migrate_to_' . $version}();
		} else {
			if ( WP::in_wp_admin() ) {
				$message = sprintf( /* translators: %1$s - the DB option name, %2$s - WP Mail SMTP, %3$s - error message. */
					esc_html__( 'There was an error while upgrading the %1$s database. Please contact %2$s support with this information: %3$s.', 'wp-mail-smtp' ),
					static::OPTION_NAME,
					'<strong>WP Mail SMTP</strong>',
					'<code>migration from v' . static::get_current_version() . ' to v' . static::DB_VERSION . ' failed. Plugin version: v' . WPMS_PLUGIN_VER . '</code>'
				);

				WP::add_admin_notice( $message, WP::ADMIN_NOTICE_ERROR );
			}
		}
	}
}
