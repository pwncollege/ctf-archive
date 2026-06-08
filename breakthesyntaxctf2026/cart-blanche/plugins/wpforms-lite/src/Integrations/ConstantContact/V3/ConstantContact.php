<?php

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedConstantInspection */

namespace WPForms\Integrations\ConstantContact\V3;

use WPForms\Providers\Providers;
use WPForms\Integrations\ConstantContact\V3\Migration\Migration;
use WPForms\Integrations\IntegrationInterface;

/**
 * Class ConstantContact.
 *
 * @since 1.9.3
 */
class ConstantContact implements IntegrationInterface {

	/**
	 * Current integration version.
	 *
	 * @since 1.9.3
	 */
	const VERSION_OPTION = 'wpforms_constant_contact_version';

	/**
	 * API key.
	 *
	 * @since 1.9.3
	 *
	 * @var string
	 */
	const API_KEY = '551ccf74-4e2d-4649-8f58-e5a973789b94';

	/**
	 * API URL.
	 *
	 * @since 1.9.3
	 */
	const API_URL = 'https://api.cc.email/';

	/**
	 * Sign up URL.
	 *
	 * @since 1.9.3
	 */
	const SIGN_UP = 'https://authz.constantcontact.com/oauth2/default/v1/authorize';

	/**
	 * Indicate if current integration is allowed to load.
	 *
	 * @since 1.9.3
	 *
	 * @return bool
	 */
	public function allow_load(): bool {

		return true;
	}

	/**
	 * Load the integration.
	 *
	 * @since 1.9.3
	 */
	public function load() {

		( new Migration() )->init();
		( new Auth() )->hooks();

		if (
			self::get_current_version() !== 3 &&
			empty( wpforms_get_providers_options( Core::SLUG ) )
		) {
			return;
		}

		Providers::get_instance()->register(
			Core::get_instance()
		);
	}

	/**
	 * Return an actual working constant contact version.
	 * By default, it is 2.
	 *
	 * @since 1.9.3
	 *
	 * @return int
	 */
	public static function get_current_version(): int {

		$current_version = get_option( self::VERSION_OPTION, false );

		if ( $current_version !== false ) {
			return (int) $current_version;
		}

		$current_version = empty( wpforms_get_providers_options( 'constant-contact' ) ) ? 3 : 2;

		update_option( self::VERSION_OPTION, $current_version );

		return $current_version;
	}

	/**
	 * Get the API key.
	 *
	 * @since 1.9.3
	 *
	 * @return string
	 */
	public static function get_api_key(): string {

		return defined( 'WPFORMS_CONSTANT_CONTACT_API_KEY' )
			? (string) WPFORMS_CONSTANT_CONTACT_API_KEY
			: self::API_KEY;
	}

	/**
	 * Get the API URL.
	 *
	 * @since 1.9.3
	 *
	 * @return string
	 */
	public static function get_api_url(): string {

		return self::API_URL;
	}

	/**
	 * Get the redirect URI.
	 *
	 * @since 1.9.3
	 *
	 * @return string
	 */
	public static function get_middleware_url(): string {

		return defined( 'WPFORMS_CONSTANT_CONTACT_MIDDLEWARE_URL' ) && WPFORMS_CONSTANT_CONTACT_MIDDLEWARE_URL
			? WPFORMS_CONSTANT_CONTACT_MIDDLEWARE_URL
			: 'https://wpforms.com/oauth/constant-contact/';
	}

	/**
	 * Get the list of predefined custom fields.
	 *
	 * @since 1.9.3
	 *
	 * @return array
	 */
	public static function get_predefined_custom_fields(): array {

		$fields = [
			'first_name'   => __( 'First Name', 'wpforms-lite' ),
			'last_name'    => __( 'Last Name', 'wpforms-lite' ),
			'phone'        => __( 'Phone', 'wpforms-lite' ),
			'job_title'    => __( 'Job Title', 'wpforms-lite' ),
			'company_name' => __( 'Company Name', 'wpforms-lite' ),
		];

		if ( wpforms()->is_pro() ) {
			$fields['address'] = __( 'Address', 'wpforms-lite' );
		}

		return $fields;
	}
}
