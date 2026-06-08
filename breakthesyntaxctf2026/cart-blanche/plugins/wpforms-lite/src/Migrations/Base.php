<?php

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection AutoloadingIssuesInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace WPForms\Migrations;

use ReflectionClass;
use WPForms\Helpers\DB;

/**
 * Class Migrations handles both Lite and Pro plugin upgrade routines.
 *
 * @since 1.7.5
 */
abstract class Base {

	/**
	 * WP option name to store the migration versions.
	 * Must have 'versions' in the name defined in extending classes,
	 * like 'wpforms_versions', 'wpforms_versions_lite, 'wpforms_stripe_versions', etc.
	 *
	 * @since 1.7.5
	 */
	protected const MIGRATED_OPTION_NAME = '';

	/**
	 * Current plugin version.
	 *
	 * @since 1.7.5
	 */
	protected const CURRENT_VERSION = WPFORMS_VERSION;

	/**
	 * WP option name to store the upgraded from version number.
	 *
	 * @since      1.8.8
	 * @deprecated 1.9.8
	 *
	 * @todo       Delete this option later. There is no sense to creating a separate migration for it.
	 * @noinspection PhpUnusedPrivateFieldInspection
	 */
	private const UPGRADED_FROM_OPTION_NAME = 'wpforms_version_upgraded_from';

	/**
	 * WP option name to store the previous plugin version.
	 *
	 * @since 1.8.8
	 */
	public const PREVIOUS_CORE_VERSION_OPTION_NAME = 'wpforms_version_previous';

	/**
	 * Name of the core plugin used in log messages.
	 *
	 * @since 1.7.5
	 */
	protected const PLUGIN_NAME = '';

	/**
	 * Upgrade classes.
	 *
	 * @since 1.7.5
	 */
	protected const UPGRADE_CLASSES = [];

	/**
	 * Migration started status.
	 *
	 * @since 1.7.5
	 */
	private const STARTED = - 1;

	/**
	 * Migration failed status.
	 *
	 * @since 1.7.5
	 */
	private const FAILED = - 2;

	/**
	 * Initial fake version for comparisons.
	 *
	 * @since 1.7.5
	 */
	private const INITIAL_FAKE_VERSION = '0.0.1';

	/**
	 * Reflection class instance.
	 *
	 * @since 1.7.5
	 *
	 * @var ReflectionClass
	 */
	protected $reflector;

	/**
	 * Migrated versions.
	 *
	 * @since 1.7.5
	 *
	 * @var string[]
	 */
	protected $migrated = [];

	/**
	 * Whether tables' check was done.
	 *
	 * @since 1.8.7
	 *
	 * @var bool
	 */
	private $tables_check_done;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.7.5
	 */
	public function __construct() {

		$this->reflector = new ReflectionClass( $this );
	}

	/**
	 * Class init.
	 *
	 * @since 1.7.5
	 */
	public function init(): void {

		if ( ! $this->is_allowed() ) {
			return;
		}

		$this->maybe_convert_migration_option();
		$this->hooks();
	}

	/**
	 * General hooks.
	 *
	 * @since 1.7.5
	 */
	protected function hooks(): void {

		$priority = $this->is_core_plugin() ? - 9999 : 100;

		add_action( 'wpforms_loaded', [ $this, 'migrate' ], $priority );
		add_action( 'wpforms_loaded', [ $this, 'update_versions' ], $priority + 1 );
	}

	/**
	 * Run the migrations of the core plugin for a specific version.
	 *
	 * @since 1.7.5
	 *
	 * @noinspection NotOptimalIfConditionsInspection
	 */
	public function migrate(): void { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		$classes   = $this->get_upgrade_classes();
		$namespace = $this->reflector->getNamespaceName() . '\\';

		foreach ( $classes as $class ) {
			$upgrade_version = $this->get_upgrade_version( $class );
			$plugin_name     = $this->get_plugin_name( $class );
			$class           = $namespace . $class;

			if (
				( isset( $this->migrated[ $upgrade_version ] ) && $this->migrated[ $upgrade_version ] >= 0 ) ||
				version_compare( $upgrade_version, static::CURRENT_VERSION, '>' ) ||
				! class_exists( $class )
			) {
				continue;
			}

			$this->maybe_create_tables();

			if ( ! isset( $this->migrated[ $upgrade_version ] ) ) {
				$this->migrated[ $upgrade_version ] = self::STARTED;

				$this->log( sprintf( 'Migration of %1$s to %2$s started.', $plugin_name, $upgrade_version ) );
			}

			// Run upgrade.
			$migrated = ( new $class( $this ) )->run();

			// Some migration methods can be called several times to support AS action,
			// so do not log their completion here.
			if ( $migrated === null ) {
				continue;
			}

			$this->migrated[ $upgrade_version ] = $migrated ? time() : self::FAILED;

			$this->log_migration_message( $migrated, $plugin_name, $upgrade_version );
		}
	}

	/**
	 * If an upgrade has occurred, update a version option in the database.
	 *
	 * @since 1.7.5
	 */
	public function update_versions(): void {

		$this->update_previous_core_version();

		// Retrieve the last migrated versions.
		$last_migrated = get_option( static::MIGRATED_OPTION_NAME, [] );
		$migrated      = array_merge( $last_migrated, $this->migrated );

		/**
		 * Store the current version upgrade timestamp even if there were no migrations to it.
		 * We need it in wpforms_get_upgraded_timestamp() for further usage in Event Driven Plugin Notifications.
		 */
		$migrated[ static::CURRENT_VERSION ] = $migrated[ static::CURRENT_VERSION ] ?? time();

		uksort( $last_migrated, 'version_compare' );
		uksort( $migrated, 'version_compare' );

		if ( $migrated === $last_migrated ) {
			return;
		}

		update_option( static::MIGRATED_OPTION_NAME, $migrated );

		$fully_completed = array_reduce(
			$migrated,
			static function ( $carry, $status ) {

				return $carry && ( $status >= 0 );
			},
			true
		);

		if ( ! $fully_completed ) {
			return;
		}

		$this->log(
			sprintf( 'Migration of %1$s to %2$s is fully completed.', static::PLUGIN_NAME, static::CURRENT_VERSION )
		);
	}

	/**
	 * Update previous core version.
	 *
	 * @since 1.9.8
	 *
	 * @return void
	 */
	private function update_previous_core_version(): void {

		if ( ! $this->is_core_plugin() ) {
			return;
		}

		// Retrieve the last migrated versions.
		$last_migrated         = get_option( static::MIGRATED_OPTION_NAME, [] );
		$previous_core_version = $this->get_max_version( $last_migrated );

		if (
			$previous_core_version === self::INITIAL_FAKE_VERSION ||
			version_compare( $previous_core_version, static::CURRENT_VERSION, '>=' )
		) {
			return;
		}

		// Store the previous core version in the option.
		update_option( self::PREVIOUS_CORE_VERSION_OPTION_NAME, $previous_core_version );

		/**
		 * Fires after the core plugin has been upgraded.
		 * Please note: some of the migrations that run via Active Scheduler can be not completed yet.
		 *
		 * @since 1.8.8
		 *
		 * @param string $previous_core_version The core version from which the plugin was upgraded.
		 * @param Base   $migration_obj         The migration class instance.
		 */
		do_action( 'wpforms_migrations_base_core_upgraded', $previous_core_version, $this );
	}

	/**
	 * Get upgrade classes.
	 *
	 * @since 1.7.5
	 *
	 * @return string[]
	 */
	protected function get_upgrade_classes(): array {

		$classes = static::UPGRADE_CLASSES;

		sort( $classes );

		return $classes;
	}

	/**
	 * Get an upgrade version from the class name.
	 *
	 * @since 1.7.5
	 *
	 * @param string $class_name Class name.
	 *
	 * @return string
	 */
	public function get_upgrade_version( string $class_name ): string {

		// Find only the digits and underscores to get the version number.
		if ( ! preg_match( '/(\d_?)+/', $class_name, $matches ) ) {
			return '';
		}

		$raw_version = $matches[0];

		if ( strpos( $raw_version, '_' ) ) {
			// Modern notation: 1_10_0_3 means 1.10.0.3 version.
			return str_replace( '_', '.', $raw_version );
		}

		// Legacy notation, with 1-digit subversion numbers: 1751 means 1.7.5.1 version.
		return implode( '.', str_split( $raw_version ) );
	}

	/**
	 * Get a plugin /addon name.
	 *
	 * @since 1.7.5
	 *
	 * @param string $class_name Upgrade class name.
	 *
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected function get_plugin_name( string $class_name ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found

		return static::PLUGIN_NAME;
	}

	/**
	 * Force log message to WPForms logger.
	 *
	 * @since 1.7.5
	 *
	 * @param string $message The error message that should be logged.
	 */
	protected function log( string $message ): void {

		wpforms_log(
			'Migration',
			$message,
			[
				'type'  => 'log',
				'force' => true,
			]
		);
	}

	/**
	 * Determine if migration is allowed.
	 *
	 * @since 1.7.5
	 */
	private function is_allowed(): bool {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['service-worker'] ) ) {
			return false;
		}

		return wp_doing_cron() || is_admin() || wpforms_doing_wp_cli();
	}

	/**
	 * Maybe create custom plugin tables.
	 *
	 * @since 1.7.6
	 */
	public function maybe_create_tables(): void {

		if ( $this->tables_check_done ) {
			/**
			 * We should do table check only once - when the first migration has been started.
			 * The DB::get_existing_custom_tables() without caching causes performance issue
			 * on huge multisite with thousands of tables.
			 */
			return;
		}

		DB::create_custom_tables( true );

		$this->tables_check_done = true;
	}

	/**
	 * Maybe convert the migration option format.
	 *
	 * @since 1.7.5
	 */
	private function maybe_convert_migration_option(): void {

		/**
		 * Retrieve the migration option and check its format.
		 * Old format: a string 'x.y.z' containing the last migrated version.
		 * New format: [ 'x.y.z' => {status}, 'x1.y1.z1' => {status}... ],
		 * where {status} is a migration status.
		 * Negative means some status (-1 for 'started' etc.),
		 * zero means completed earlier at an unknown time,
		 * positive means completion timestamp.
		 */
		$this->migrated = get_option( static::MIGRATED_OPTION_NAME );

		// If the option is an array, it means that it is already converted to the new format.
		if ( is_array( $this->migrated ) ) {
			return;
		}

		/**
		 * Convert the option to the new format.
		 *
		 * Old option names contained 'version',
		 * like 'wpforms_version', 'wpforms_version_lite', 'wpforms_stripe_version', etc.
		 * We preserve old options for downgrade cases.
		 * New option names should contain 'versions' and be like 'wpforms_versions', etc.
		 */
		$this->migrated = get_option(
			str_replace( 'versions', 'version', static::MIGRATED_OPTION_NAME )
		);

		$version        = $this->migrated === false ? self::INITIAL_FAKE_VERSION : (string) $this->migrated;
		$timestamp      = $version === static::CURRENT_VERSION ? time() : 0;
		$this->migrated = [ $version => $timestamp ];
		$max_version    = $this->get_max_version( $this->migrated );

		foreach ( $this->get_upgrade_classes() as $upgrade_class ) {
			$upgrade_version = $this->get_upgrade_version( $upgrade_class );

			if (
				! isset( $this->migrated[ $upgrade_version ] ) &&
				version_compare( $upgrade_version, $max_version, '<' )
			) {
				$this->migrated[ $upgrade_version ] = 0;
			}
		}

		unset( $this->migrated[ self::INITIAL_FAKE_VERSION ] );

		ksort( $this->migrated );

		update_option( static::MIGRATED_OPTION_NAME, $this->migrated );
	}

	/**
	 * Get the max version.
	 *
	 * @since 1.7.5
	 *
	 * @param array $versions Versions.
	 *
	 * @return string
	 */
	private function get_max_version( array $versions ): string {

		// phpcs:ignore WPForms.Formatting.EmptyLineBeforeReturn.RemoveEmptyLineBeforeReturnStatement
		return array_reduce(
			array_keys( $versions ),
			static function ( $carry, $version ) {

				return version_compare( $version, $carry, '>' ) ? $version : $carry;
			},
			self::INITIAL_FAKE_VERSION
		);
	}

	/**
	 * Determine if it is the core plugin (Lite or Pro).
	 *
	 * @since 1.7.5
	 *
	 * @return bool True if it is the core plugin.
	 */
	protected function is_core_plugin(): bool {

		// phpcs:ignore WPForms.Formatting.EmptyLineBeforeReturn.RemoveEmptyLineBeforeReturnStatement
		return strpos( static::MIGRATED_OPTION_NAME, 'wpforms_versions' ) === 0;
	}

	/**
	 * Log migration message.
	 *
	 * @since 1.8.2.3
	 *
	 * @param bool   $migrated        Migration status.
	 * @param string $plugin_name     Plugin name.
	 * @param string $upgrade_version Upgrade version.
	 *
	 * @return void
	 */
	private function log_migration_message( bool $migrated, string $plugin_name, string $upgrade_version ): void {

		$message = $migrated ?
			sprintf( 'Migration of %1$s to %2$s completed.', $plugin_name, $upgrade_version ) :
			sprintf( 'Migration of %1$s to %2$s failed.', $plugin_name, $upgrade_version );

		$this->log( $message );
	}
}
