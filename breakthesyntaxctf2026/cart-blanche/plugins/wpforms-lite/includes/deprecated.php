<?php

// phpcs:ignoreFile Generic.Files.OneObjectStructurePerFile.MultipleFound

namespace WPForms {
	/**
	 * The removed class helps prevent fatal errors for clients
	 * that use some of the classes we are about to remove.
	 * Use the class extending instead of class_alias function.
	 *
	 * @since 1.8.0
	 */
	class Removed {

		/**
		 * List of removed classes in the next format:
		 * Fully Qualified Class Name => version.
		 *
		 * @since 1.8.0
		 */
		const CLASSES = [
			'WPForms\Pro\Admin\Entries\DefaultScreen'  => '1.8.2',
			'WPForms\Pro\Integrations\AI\AI'           => '1.9.4',
			'WPForms\Pro\Integrations\AI\Helpers'      => '1.9.4',
			'WPForms_Install_Skin'                     => '1.9.5',
			'WPForms_Install_Silent_Skin'              => '1.5.6.1',
			'WPForms\Helpers\PluginSilentUpgraderSkin' => '1.9.5',
		];

		/**
		 * Inform clients that the class is removed.
		 *
		 * @since 1.8.0
		 */
		public function __construct() {

			self::trigger_error();
		}

		/**
		 * Inform clients that the class is removed.
		 *
		 * @since 1.8.0
		 *
		 * @param string $name Property name.
		 */
		public function __get( $name ) {

			self::trigger_error( $name );
		}

		/**
		 * Inform clients that the class is removed.
		 *
		 * @since 1.8.0
		 *
		 * @param string $name  Property name.
		 * @param mixed  $value Property value.
		 */
		public function __set( $name, $value ) {

			self::trigger_error( $name );
		}

		/**
		 * Inform clients that the class is removed.
		 *
		 * @since 1.8.0
		 *
		 * @param string $name Property name.
		 */
		public function __isset( $name ) {

			self::trigger_error( $name );
		}

		/**
		 * Inform clients that the class is removed.
		 *
		 * @since 1.8.0
		 *
		 * @param string $name      Method name.
		 * @param array  $arguments List of arguments.
		 */
		public function __call( $name, $arguments ) {

			self::trigger_error( $name );
		}

		/**
		 * Inform clients that the class is removed.
		 *
		 * @since 1.8.0
		 *
		 * @param string $name      Method name.
		 * @param array  $arguments List of arguments.
		 */
		public static function __callStatic( $name, $arguments ) {

			self::trigger_error( $name );
		}

		/**
		 * Inform clients that the class is removed.
		 *
		 * @since 1.8.0
		 *
		 * @param string $element_name Property or method name.
		 */
		private static function trigger_error( $element_name = '' ) {

			$current_class   = static::class;
			$removed_element = $current_class;

			if ( $element_name ) {
				$removed_element .= '::' . $element_name;
			}

			$version = ! empty( self::CLASSES[ $current_class ] ) ? self::CLASSES[ $current_class ] : WPFORMS_VERSION;

			trigger_error(
				sprintf(
					'%1$s has been removed in %2$s of the WPForms plugin',
					esc_html( $removed_element ),
					esc_html( $version )
				),
				E_USER_WARNING
			);
		}
	}
}

namespace WPForms\Forms {

	use WPForms\Removed;

	class Loader extends Removed {}
}

namespace {
	/**
	 * Legacy `WPForms_Addons` class was refactored and moved to the new `WPForms\Pro\Admin\Pages\Addons` class.
	 * This alias is a safeguard to those developers who use our internal class WPForms_Addons,
	 * which we deleted.
	 *
	 * @since 1.6.7
	 */
	class_alias( wpforms()->is_pro() ? 'WPForms\Pro\Admin\Pages\Addons' : 'WPForms\Lite\Admin\Pages\Addons', 'WPForms_Addons' );

	/**
	 * This alias is a safeguard to those developers who decided to use our internal class WPForms_Smart_Tags,
	 * which we deleted.
	 *
	 * @since 1.6.7
	 */
	class_alias( wpforms()->is_pro() ? 'WPForms\Pro\SmartTags\SmartTags' : 'WPForms\SmartTags\SmartTags', 'WPForms_Smart_Tags' );

	/**
	 * This alias is a safeguard to those developers who decided to use our internal class \WPForms\Providers\Loader,
	 * which we deleted.
	 *
	 * @since 1.7.3
	 */
	class_alias( '\WPForms\Providers\Providers', '\WPForms\Providers\Loader' );

	/**
	 * Legacy `\WPForms\Admin\Notifications` class was refactored and moved to the new `\WPForms\Admin\Notifications\Notifications` class.
	 * This alias is a safeguard to those developers who use our internal class \WPForms\Admin\Notifications,
	 * which we deleted.
	 *
	 * @since 1.7.5
	 */
	class_alias( '\WPForms\Admin\Notifications\Notifications', '\WPForms\Admin\Notifications' );

	/**
	 * Legacy `\WPForms_Field_Payment_Checkbox` class was refactored and moved to the new `\WPForms\Forms\Fields\PaymentCheckbox\Field` class.
	 * This alias is a safeguard to those developers who use our internal class \WPForms_Field_Payment_Checkbox,
	 * which we deleted.
	 *
	 * @since 1.8.2
	 */
	class_alias( '\WPForms\Forms\Fields\PaymentCheckbox\Field', '\WPForms_Field_Payment_Checkbox' );

	/**
	 * Legacy `\WPForms_Field_Payment_Multiple` class was refactored and moved to the new `\WPForms\Forms\Fields\PaymentMultiple\Field` class.
	 * This alias is a safeguard to those developers who use our internal class \WPForms_Field_Payment_Multiple,
	 * which we deleted.
	 *
	 * @since 1.8.2
	 */
	class_alias( '\WPForms\Forms\Fields\PaymentMultiple\Field', '\WPForms_Field_Payment_Multiple' );

	/**
	 * Legacy `\WPForms_Field_Payment_Single` class was refactored and moved to the new `\WPForms\Forms\Fields\PaymentSingle\Field` class.
	 * This alias is a safeguard to those developers who use our internal class \WPForms_Field_Payment_Single,
	 * which we deleted.
	 *
	 * @since 1.8.2
	 */
	class_alias( '\WPForms\Forms\Fields\PaymentSingle\Field', '\WPForms_Field_Payment_Single' );

	/**
	 * Legacy `\WPForms_Field_Payment_Total` class was refactored and moved to the new `\WPForms\Forms\Fields\PaymentTotal\Field` class.
	 * This alias is a safeguard to those developers who use our internal class \WPForms_Field_Payment_Total,
	 * which we deleted.
	 *
	 * @since 1.8.2
	 */
	class_alias( '\WPForms\Forms\Fields\PaymentTotal\Field', '\WPForms_Field_Payment_Total' );

	/**
	 * Legacy `\WPForms_Field_Payment_Select` class was refactored and moved to the new `\WPForms\Forms\Fields\PaymentSelect\Field` class.
	 * This alias is a safeguard to those developers who use our internal class \WPForms_Field_Payment_Select,
	 * which we deleted.
	 *
	 * @since 1.8.2
	 */
	class_alias( '\WPForms\Forms\Fields\PaymentSelect\Field', '\WPForms_Field_Payment_Select' );

	/**
	 * Legacy `\WPForms\Migrations` class was refactored and moved to the new `\WPForms\Migrations\Migrations` class.
	 * This alias is a safeguard to those developers who use our internal class \WPForms\Migrations, which we deleted.
	 *
	 * @since 1.7.5
	 */
	class_alias( '\WPForms\Migrations\Migrations', '\WPForms\Migrations' );

	/**
	 * Legacy `\WPForms_Frontend` class was refactored and moved to the new `\WPForms\Frontend\Frontend` class.
	 * This alias is a safeguard to those developers who use our internal class \WPForms_Frontend, which we deleted.
	 *
	 * @since 1.8.1
	 */
	class_alias( '\WPForms\Frontend\Frontend', '\WPForms_Frontend' );

	/**
	 * This alias is a safeguard to those developers who use our internal class \WPForms_Overview, which we deleted.
	 *
	 * @since 1.8.6
	 */
	class_alias( '\WPForms\Admin\Forms\Page', '\WPForms_Overview' );

	/**
	 * This alias is a safeguard to those developers who use our internal class \WPForms_Overview_Table, which we deleted.
	 *
	 * @since 1.8.6
	 */
	class_alias( '\WPForms\Admin\Forms\ListTable', '\WPForms_Overview_Table' );

	/**
	 * This alias is a safeguard to those developers who use our internal
	 * class \WPForms\Emails\FetchInfoBlocksTask, which we deleted.
	 *
	 * @since 1.9.8
	 */
	class_alias( '\WPForms\Emails\Tasks\FetchInfoBlocksTask', '\WPForms\Emails\FetchInfoBlocksTask' );

	// Pro specific aliases.
	if ( wpforms()->is_pro() ) {
		/**
		 * Legacy `\WPForms\Pro\Migrations` class was refactored and moved to the new `\WPForms\Pro\Migrations\Migrations` class.
		 * This alias is a safeguard to those developers who use our internal class \WPForms\Migrations, which we deleted.
		 *
		 * @since 1.7.5
		 */
		class_alias( '\WPForms\Pro\Migrations\Migrations', '\WPForms\Pro\Migrations' );

		/**
		 * Legacy `\WPForms\Pro\Integrations\TranslationsPress\Translations` class was refactored and moved to the new
		 * `\WPForms\Pro\Integrations\Translations\Translations` class.
		 * This alias is a safeguard to those developers who use our internal class \WPForms\Pro\Integrations\TranslationsPress, which we deleted.
		 *
		 * @since 1.8.2.2
		 */
		class_alias( '\WPForms\Pro\Integrations\Translations\Translations', '\WPForms\Pro\Integrations\TranslationsPress\Translations' );

		/**
		 * This alias is a safeguard to those developers who use our internal class \WPForms_Entries_List, which we deleted.
		 *
		 * @since 1.8.6
		 */
		class_alias( '\WPForms\Pro\Admin\Entries\Page', '\WPForms_Entries_List' );

		/**
		 * This alias is a safeguard to those developers who use our internal class \WPForms_Entries_Table, which we deleted.
		 *
		 * @since 1.8.6
		 */
		class_alias( '\WPForms\Pro\Admin\Entries\ListTable', '\WPForms_Entries_Table' );

		/**
		 * This alias is a safeguard to those developers who use our internal class \WPForms_Field_Layout, which we deleted.
		 *
		 * @since 1.8.9
		 */
		class_alias( '\WPForms\Pro\Forms\Fields\Layout\Field', '\WPForms_Field_Layout' );

		/**
		 * These aliases are a safeguard to those developers who use our internal legacy field classes, which we deleted.
		 *
		 * @since 1.9.4
		 */
		class_alias( '\WPForms\Pro\Forms\Fields\Address\Field', '\WPForms_Field_Address' );
		class_alias( '\WPForms\Pro\Forms\Fields\Content\Field', '\WPForms_Field_Content' );
		class_alias( '\WPForms\Pro\Forms\Fields\DateTime\Field', '\WPForms_Field_Date_Time' );
		class_alias( '\WPForms\Pro\Forms\Fields\Divider\Field', '\WPForms_Field_Divider' );
		class_alias( '\WPForms\Pro\Forms\Fields\EntryPreview\Field', '\WPForms_Entry_Preview' );
		class_alias( '\WPForms\Pro\Forms\Fields\FileUpload\Field', '\WPForms_Field_File_Upload' );
		class_alias( '\WPForms\Pro\Forms\Fields\Hidden\Field', '\WPForms_Field_Hidden' );
		class_alias( '\WPForms\Pro\Forms\Fields\Html\Field', '\WPForms_Field_HTML' );
		class_alias( '\WPForms\Pro\Forms\Fields\Pagebreak\Field', '\WPForms_Field_Page_Break' );
		class_alias( '\WPForms\Pro\Forms\Fields\Password\Field', '\WPForms_Field_Password' );
		class_alias( '\WPForms\Pro\Forms\Fields\CreditCard\Field', '\WPForms_Field_CreditCard' );
		class_alias( '\WPForms\Pro\Forms\Fields\Phone\Field', '\WPForms_Field_Phone' );
		class_alias( '\WPForms\Pro\Forms\Fields\Rating\Field', '\WPForms_Rating_Text' );
		class_alias( '\WPForms\Pro\Forms\Fields\Richtext\Field', '\WPForms_Field_Richtext' );
		class_alias( '\WPForms\Pro\Forms\Fields\Url\Field', '\WPForms_Field_URL' );

		/**
		 * This alias is a safeguard to those developers who use our internal
		 * class \WPForms\Pro\Integrations\AI\API\Forms, which we deleted.
		 *
		 * @since 1.9.4
		 */
		class_alias( '\WPForms\Integrations\AI\API\Forms', '\WPForms\Pro\Integrations\AI\API\Forms' );

		/**
		 * This alias is a safeguard to those developers who use our internal
		 * class \WPForms\Pro\Integrations\AI\Admin\Ajax\Forms, which we deleted.
		 *
		 * @since 1.9.4
		 */
		class_alias( '\WPForms\Integrations\AI\Admin\Ajax\Forms', '\WPForms\Pro\Integrations\AI\Admin\Ajax\Forms' );

		/**
		 * This alias is a safeguard to those developers who use our internal
		 * class \WPForms\Pro\Integrations\AI\Admin\Builder\Enqueues, which we deleted.
		 *
		 * @since 1.9.4
		 */
		class_alias( '\WPForms\Integrations\AI\Admin\Builder\Forms', '\WPForms\Pro\Integrations\AI\Admin\Builder\Enqueues' );

		/**
		 * This alias is a safeguard to those developers who use our internal
		 * class \WPForms\Pro\Integrations\AI\Admin\Pages\Templates, which we deleted.
		 *
		 * @since 1.9.4
		 */
		class_alias( '\WPForms\Integrations\AI\Admin\Pages\Templates', '\WPForms\Pro\Integrations\AI\Admin\Pages\Templates' );
	}

	/**
	 * This adds backwards compatibility after scoping the stripe lib and using our own prefix `\WPForms\Vendor\Stripe`.
	 * This alias is a safeguard for the users who update core plugin to 1.8.5 but have an older version of stripe pro addon.
	 * Fire this right before autoloading of legacy classes so that there is no conflict with other stripe libs when aliasing.
	 *
	 * @since 1.8.5
	 */
	spl_autoload_register(
		static function ( $class_name ) {
			static $stripe_check_done = false;

			static $aliases = [
				'\WPForms\Vendor\Stripe\Charge'                  => 'Stripe\Charge',
				'\WPForms\Vendor\Stripe\Customer'                => 'Stripe\Customer',
				'\WPForms\Vendor\Stripe\Subscription'            => 'Stripe\Subscription',
				'\WPForms\Vendor\Stripe\Invoice'                 => 'Stripe\Invoice',
				'\WPForms\Vendor\Stripe\Exception\CardException' => 'Stripe\Exception\CardException',
				'\WPForms\Vendor\Stripe\Source'                  => 'Stripe\Source',
			];

			if ( $stripe_check_done ) {
				return;
			}

			// If class not for aliasing, bail.
			if ( ! in_array( $class_name, $aliases, true ) ) {
				return;
			}

			$stripe_check_done = true;

			// If no Stripe Pro addon bail.
			if ( ! defined( 'WPFORMS_STRIPE_VERSION' ) ) {
				return;
			}

			// Version 3.2.0 has prefixed lib.
			// Versions 2.11.0 and below already have the lib bundled, so they don't require alias.
			if (
				version_compare( WPFORMS_STRIPE_VERSION, '3.2.0', '>=' ) ||
				version_compare( WPFORMS_STRIPE_VERSION, '2.11.0', '<=' )
			) {
				return;
			}

			// We only need to alias if we are using the legacy API version.
			if ( ! \WPFormsStripe\Helpers::is_legacy_api_version() ) {
				return;
			}

			// If a lib is already loaded by a third party plugin,
			// checking the CardException class here as a niche to make sure it is the correct library.
			if ( class_exists( '\Stripe\Exception\CardException', false ) ) {
				return;
			}

			foreach ( $aliases as $prefixed => $alias ) {
				class_alias( $prefixed, '\\' . $alias );
			}
		}
	);
}

namespace WPForms\Pro\Admin\Entries {

	/**
	 * The default Entries screen showed a chart and the form entries stats.
	 * Replaced with "WPForms\Pro\Admin\Entries\Overview".
	 *
	 * @since 1.5.5
	 * @deprecated 1.8.2
	 */
	class DefaultScreen extends \WPForms\Removed {}
}
