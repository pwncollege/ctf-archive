<?php

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpIllegalPsrClassPathInspection */

// phpcs:ignore Universal.Namespaces.DisallowCurlyBraceSyntax.Forbidden
namespace WPForms {

	use AllowDynamicProperties;
	use stdClass;
	use WPForms\Helpers\DB;
	use WPForms_Form_Handler;
	use WPForms_Process;
	use WPForms_Settings;

	/**
	 * Main WPForms class.
	 *
	 * @since 1.0.0
	 */
	#[AllowDynamicProperties]
	final class WPForms {

		/**
		 * List of screen IDs where heartbeat requests are allowed.
		 *
		 * @since 1.9.3
		 *
		 * @var string[]
		 */
		private const HEARTBEAT_ALLOWED_SCREEN_IDS = [
			'wpforms_page_wpforms-entries',
		];

		/**
		 * One is the loneliest number that you'll ever do.
		 *
		 * @since 1.0.0
		 *
		 * @var WPForms
		 */
		private static $instance;

		/**
		 * Plugin version for enqueueing, etc.
		 * The value is got from WPFORMS_VERSION constant.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		public $version = '';

		/**
		 * Classes registry.
		 *
		 * @since 1.5.7
		 *
		 * @var array
		 */
		private $registry = [];

		/**
		 * List of legacy public properties.
		 *
		 * @since 1.6.8
		 *
		 * @var string[]
		 */
		private $legacy_properties = [
			'form',
			'entry',
			'entry_fields',
			'entry_meta',
			'frontend',
			'process',
			'smart_tags',
			'license',
		];

		/**
		 * Paid returns true, free (Lite) returns false.
		 *
		 * @since 1.3.9
		 * @since 1.7.3 changed to private.
		 *
		 * @var bool
		 */
		private $pro = false;

		/**
		 * Backward compatibility method for accessing the class registry in an old way,
		 * e.g. 'wpforms()->form' or 'wpforms()->entry'.
		 *
		 * @since 1.5.7
		 *
		 * @param string $name Name of the object to get.
		 *
		 * @return mixed|null
		 * @noinspection MagicMethodsValidityInspection
		 * @noinspection PhpDeprecationInspection
		 */
		public function __get( $name ) {

			if ( $name === 'smart_tags' ) {
				_deprecated_argument(
					'wpforms()->smart_tags',
					'1.6.7 of the WPForms plugin',
					"Please use `wpforms()->obj( 'smart_tags' )` instead."
				);
			}

			if ( $name === 'pro' ) {
				_deprecated_argument(
					'wpforms()->pro',
					'1.8.2.2 of the WPForms plugin',
					'Please use `wpforms()->is_pro()` instead.'
				);

				return wpforms()->is_pro();
			}

			return $this->get( $name );
		}

		/**
		 * Main WPForms Instance.
		 *
		 * Only one instance of WPForms exists in memory at any one time.
		 * Also, prevent the need to define globals all over the place.
		 *
		 * @since 1.0.0
		 *
		 * @return WPForms
		 */
		public static function instance(): WPForms {

			if ( self::$instance === null || ! self::$instance instanceof self ) {
				self::$instance = new self();

				self::$instance->init();
			}

			return self::$instance;
		}

		/**
		 * Initialize the plugin.
		 *
		 * @since 1.9.3
		 *
		 * @noinspection UsingInclusionOnceReturnValueInspection
		 */
		private function init(): void {

			if ( self::is_restricted_heartbeat() ) {
				return;
			}

			$this->constants();
			$this->includes();

			// Load Pro or Lite specific files.
			if ( $this->is_pro() ) {
				$this->registry['pro'] = require_once WPFORMS_PLUGIN_DIR . 'pro/wpforms-pro.php';
			} else {
				require_once WPFORMS_PLUGIN_DIR . 'lite/wpforms-lite.php';
			}

			$this->hooks();
		}

		/**
		 * Setup plugin constants.
		 * All the path/URL-related constants are defined in the main plugin file.
		 *
		 * @since 1.0.0
		 */
		private function constants(): void {

			$this->version = WPFORMS_VERSION;

			// Plugin Slug - Determine a plugin type and set slug accordingly.
			// This filter is documented in \WPForms\WPForms::is_pro.
			if ( apply_filters( 'wpforms_allow_pro_version', file_exists( WPFORMS_PLUGIN_DIR . 'pro/wpforms-pro.php' ) ) ) {
				$this->pro = true;

				/**
				 * Pro plugin slug.
				 *
				 * @since 1.5.0
				 */
				define( 'WPFORMS_PLUGIN_SLUG', 'wpforms' );
			} else {
				/**
				 * Lite plugin slug.
				 *
				 * @since 1.5.0
				 */
				define( 'WPFORMS_PLUGIN_SLUG', 'wpforms-lite' );
			}
		}

		/**
		 * Include files.
		 *
		 * @since 1.0.0
		 */
		private function includes(): void {

			$this->error_handler();

			// Action Scheduler requires a special loading procedure.
			require_once WPFORMS_PLUGIN_DIR . 'vendor/woocommerce/action-scheduler/action-scheduler.php';

			// Autoload Composer packages.
			require_once WPFORMS_PLUGIN_DIR . 'vendor/autoload.php';

			// Base class and functions.
			require_once WPFORMS_PLUGIN_DIR . 'includes/class-db.php';
			require_once WPFORMS_PLUGIN_DIR . 'includes/functions.php';
			require_once WPFORMS_PLUGIN_DIR . 'includes/fields/class-base.php';

			$this->includes_magic();

			// Global includes.
			require_once WPFORMS_PLUGIN_DIR . 'includes/class-install.php';
			require_once WPFORMS_PLUGIN_DIR . 'includes/class-form.php';
			require_once WPFORMS_PLUGIN_DIR . 'includes/class-fields.php';
			// TODO: class-templates.php should be loaded in admin area only.
			require_once WPFORMS_PLUGIN_DIR . 'includes/class-templates.php';
			// TODO: class-providers.php should be loaded in admin area only.
			require_once WPFORMS_PLUGIN_DIR . 'includes/class-providers.php';
			require_once WPFORMS_PLUGIN_DIR . 'includes/class-process.php';
			require_once WPFORMS_PLUGIN_DIR . 'includes/class-widget.php';
			require_once WPFORMS_PLUGIN_DIR . 'includes/emails/class-emails.php';
			require_once WPFORMS_PLUGIN_DIR . 'includes/integrations.php';
			require_once WPFORMS_PLUGIN_DIR . 'includes/deprecated.php';

			// Admin/Dashboard only includes, also in ajax.
			if ( is_admin() ) {
				require_once WPFORMS_PLUGIN_DIR . 'includes/admin/admin.php';
				require_once WPFORMS_PLUGIN_DIR . 'includes/admin/class-notices.php';
				require_once WPFORMS_PLUGIN_DIR . 'includes/admin/class-menu.php';
				require_once WPFORMS_PLUGIN_DIR . 'includes/admin/builder/class-builder.php';
				require_once WPFORMS_PLUGIN_DIR . 'includes/admin/builder/functions.php';
				require_once WPFORMS_PLUGIN_DIR . 'includes/admin/class-settings.php';
				require_once WPFORMS_PLUGIN_DIR . 'includes/admin/class-welcome.php';
				require_once WPFORMS_PLUGIN_DIR . 'includes/admin/class-editor.php';
				require_once WPFORMS_PLUGIN_DIR . 'includes/admin/class-review.php';
				require_once WPFORMS_PLUGIN_DIR . 'includes/admin/class-about.php';
				require_once WPFORMS_PLUGIN_DIR . 'includes/admin/ajax-actions.php';
			}
		}

		/**
		 * Hooks.
		 *
		 * @since 1.9.0
		 * @since 1.9.3 No longer static.
		 *
		 * @return void
		 */
		private function hooks(): void {

			add_action( 'plugins_loaded', [ self::$instance, 'objects' ] );
			add_action( 'wpforms_settings_init', [ self::$instance, 'reinstall_custom_tables' ] );
		}

		/**
		 * Include the error handler to suppress deprecated messages from vendor folders.
		 *
		 * @since 1.8.5
		 */
		private function error_handler(): void {

			require_once WPFORMS_PLUGIN_DIR . 'src/ErrorHandler.php';

			( new ErrorHandler() )->init();
		}

		/**
		 * Including the new files with PHP 5.3 style.
		 *
		 * @since 1.4.7
		 */
		private function includes_magic(): void { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

			// Load the class loader.
			$this->register(
				[
					'name' => 'Loader',
					'hook' => false,
				]
			);

			$this->register(
				[
					'name'      => 'Integrations\SolidCentral\SolidCentral',
					'hook'      => 'plugins_loaded',
					'priority'  => 0,
					'condition' => ! empty( $_GET['ithemes-sync-request'] ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				]
			);

			/*
			 * Load admin components. Exclude from the frontend.
			 */
			if ( is_admin() ) {
				add_action( 'wpforms_loaded', [ '\WPForms\Admin\Loader', 'get_instance' ] );
			}

			/*
			 * Properly init the providers' loader that will handle all the related logic and further loading.
			 */
			add_action( 'wpforms_loaded', [ '\WPForms\Providers\Providers', 'get_instance' ] );

			/*
			 * Properly init the integration loader that will handle all the related logic and further loading.
			 */
			add_action( 'wpforms_loaded', [ '\WPForms\Integrations\Loader', 'get_instance' ] );
		}

		/**
		 * Setup objects.
		 *
		 * @since 1.0.0
		 */
		public function objects(): void {

			// Global objects.
			$this->registry['form']    = new WPForms_Form_Handler();
			$this->registry['process'] = new WPForms_Process();

			/**
			 * Executes when all the WPForms stuff was loaded.
			 *
			 * @since 1.4.0
			 */
			do_action( 'wpforms_loaded' );
		}

		/**
		 * Re-create plugin custom tables if they don't exist.
		 *
		 * @since 1.9.0
		 *
		 * @param WPForms_Settings $wpforms_settings WPForms settings object.
		 */
		public function reinstall_custom_tables( WPForms_Settings $wpforms_settings ): void {

			if ( empty( $wpforms_settings->view ) ) {
				return;
			}

			// Proceed on the Settings plugin admin area page only.
			if ( $wpforms_settings->view !== 'general' ) {
				return;
			}

			// Install on the current site only.
			if ( ! DB::custom_tables_exist() ) {
				DB::create_custom_tables();
			}
		}

		/**
		 * Register a class.
		 *
		 * @since 1.5.7
		 *
		 * @param array $class_data Class registration info.
		 *
		 * $class_data array accepts these params: name, id, hook, run, condition.
		 * - name: required -- class name to register.
		 * - id: optional -- class ID to register.
		 * - hook: optional -- hook to register the class on -- default wpforms_loaded.
		 * - run: optional -- method to run on class instantiation -- default init.
		 * - condition: optional -- condition to check before registering the class.
		 *
		 * @noinspection OnlyWritesOnParameterInspection
		 */
		public function register( $class_data ): void { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh, WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

			if ( empty( $class_data['name'] ) || ! is_string( $class_data['name'] ) ) {
				return;
			}

			if ( isset( $class_data['condition'] ) && empty( $class_data['condition'] ) ) {
				return;
			}

			$full_name = $this->is_pro() ? '\WPForms\Pro\\' . $class_data['name'] : '\WPForms\Lite\\' . $class_data['name'];
			$full_name = class_exists( $full_name ) ? $full_name : '\WPForms\\' . $class_data['name'];

			// Register an addon class.
			if ( ! empty( $class_data['addon_class'] ) && ! empty( $class_data['addon_slug'] ) ) {
				$is_initialized = wpforms_is_addon_initialized( $class_data['addon_slug'] ) && $this->is_pro();
				$full_name      = $is_initialized ? $class_data['addon_class'] : $full_name;
				$full_name      = strpos( $full_name, '\\' ) !== 0 ? '\\' . $full_name : $full_name;

				// The core plugin classes have priority 10.
				// Addon classes should be initialized after the core.
				$class_data['priority'] = 100;
			}

			// Bail if the class doesn't exist AND it is not an addon class.
			if ( ! class_exists( $full_name ) && empty( $class_data['addon_class'] ) ) {
				return;
			}

			$id       = $class_data['id'] ?? '';
			$id       = $id ? preg_replace( '/[^a-z_]/', '', (string) $id ) : $id;
			$hook     = isset( $class_data['hook'] ) ? (string) $class_data['hook'] : 'wpforms_loaded';
			$run      = $class_data['run'] ?? 'init';
			$priority = isset( $class_data['priority'] ) && is_int( $class_data['priority'] ) ? $class_data['priority'] : 10;

			$callback = function () use ( $full_name, $id, $run, $hook ) {
				if ( ! class_exists( $full_name ) ) {
					return;
				}

				// Instantiate class.
				$instance = new $full_name();

				$this->register_instance( $id, $instance );

				if ( $run && method_exists( $instance, $run ) ) {
					$instance->{$run}();
				}
			};

			if ( $hook ) {
				add_action( $hook, $callback, $priority );
			} else {
				$callback();
			}
		}

		/**
		 * Register any class instance.
		 *
		 * @since 1.8.6
		 *
		 * @param string $id       Class ID.
		 * @param object $instance Any class instance (object).
		 */
		public function register_instance( $id, $instance ): void {

			if ( $id && is_object( $instance ) && ! array_key_exists( $id, $this->registry ) ) {
				$this->registry[ $id ] = $instance;
			}
		}

		/**
		 * Register classes in bulk.
		 *
		 * @since 1.5.7
		 *
		 * @param array $classes Classes to register.
		 */
		public function register_bulk( $classes ): void {

			if ( ! is_array( $classes ) ) {
				return;
			}

			foreach ( $classes as $class ) {
				$this->register( $class );
			}
		}

		/**
		 * Get a class instance from a registry.
		 * Use \WPForms\WPForms::obj() instead.
		 *
		 * @since 1.5.7
		 * @deprecated 1.9.1
		 *
		 * @param string $name Class name or an alias.
		 *
		 * @return mixed|stdClass|null
		 */
		public function get( $name ) {

			if ( ! empty( $this->registry[ $name ] ) ) {
				return $this->registry[ $name ];
			}

			// Backward compatibility for old public properties.
			// Return null to save old condition for these properties.
			if ( in_array( $name, $this->legacy_properties, true ) ) {
				return $this->{$name} ?? null;
			}

			return new stdClass();
		}

		/**
		 * Get a class instance from a registry.
		 *
		 * @since 1.9.1
		 *
		 * @param string $name Class name or an alias.
		 *
		 * @return object|null
		 */
		public function obj( string $name ): ?object {

			return $this->registry[ $name ] ?? null;
		}

		/**
		 * Get the list of all custom tables starting with `wpforms_*`.
		 *
		 * @since 1.6.3
		 *
		 * @return array List of table names.
		 */
		public function get_existing_custom_tables(): array {

			// phpcs:ignore WPForms.Formatting.EmptyLineBeforeReturn.RemoveEmptyLineBeforeReturnStatement
			return DB::get_existing_custom_tables();
		}

		/**
		 * Whether the current instance of the plugin is a paid version, or free.
		 *
		 * @since 1.7.3
		 *
		 * @return bool
		 */
		public function is_pro(): bool {

			/**
			 * Filters whether the current plugin version is pro.
			 *
			 * @since 1.7.3
			 *
			 * @param bool $pro Whether the current plugin version is pro.
			 */
			return (bool) apply_filters( 'wpforms_allow_pro_version', $this->pro );
		}

		/**
		 * Whether the current request is restricted heartbeat.
		 *
		 * @since 1.9.3
		 *
		 * @return bool
		 */
		public static function is_restricted_heartbeat(): bool {

			// phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$action = $_POST['action'] ?? '';

			if ( $action !== 'heartbeat' || ! wp_doing_ajax() ) {
				return false;
			}

			$screen_id = sanitize_key( $_POST['screen_id'] ?? '' );
			$data      = array_map( 'sanitize_text_field', $_POST['data'] ?? [] );
			// phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			/**
			 * Filters the screen ids where the heartbeat is allowed.
			 *
			 * @since 1.9.3
			 *
			 * @param array $allowed_screen_ids Screen IDs where the heartbeat is allowed.
			 */
			$allowed_screen_ids = (array) apply_filters( 'wpforms_heartbeat_allowed_screen_ids', self::HEARTBEAT_ALLOWED_SCREEN_IDS );

			// Allow heartbeat requests on specific screens.
			if ( in_array( $screen_id, $allowed_screen_ids, true ) ) {
				return false;
			}

			/**
			 * Filters whether the current request is restricted heartbeat.
			 *
			 * @since 1.9.3
			 *
			 * @param bool   $is_restricted Whether the current request is restricted heartbeat.
			 * @param string $screen_id     Screen ID.
			 * @param array  $data          Heartbeat request data.
			 */
			return (bool) apply_filters( 'wpforms_is_restricted_heartbeat', true, $screen_id, $data );
		}
	}
}

// phpcs:ignore Universal.Namespaces.DisallowCurlyBraceSyntax.Forbidden, Universal.Namespaces.DisallowDeclarationWithoutName.Forbidden, Universal.Namespaces.OneDeclarationPerFile.MultipleFound
namespace {

	// Define `wpforms()` function only if it's not the restricted heartbeat request.
	if ( ! WPForms\WPForms::is_restricted_heartbeat() ) {

		/**
		 * The function which returns the one WPForms instance.
		 *
		 * @since 1.0.0
		 *
		 * @return WPForms\WPForms
		 */
		function wpforms(): WPForms\WPForms { // phpcs:ignore Universal.Files.SeparateFunctionsFromOO.Mixed

			return WPForms\WPForms::instance();
		}

		/**
		 * Adding an alias for backward-compatibility with plugins
		 * that still use class_exists( 'WPForms' )
		 * instead of function_exists( 'wpforms' ), which is preferred.
		 *
		 * In 1.5.0 we removed support for PHP 5.2
		 * and moved the former WPForms class to a namespace: WPForms\WPForms.
		 *
		 * @since 1.5.1
		 */
		class_alias( 'WPForms\WPForms', 'WPForms' );
	}
}
