<?php

namespace WPForms\Admin\Education;

use WPForms\Admin\Addons\Addons;
use WPForms\Requirements\Requirements;

/**
 * Base class for all "addon item" type Education features.
 *
 * @since 1.6.6
 */
abstract class AddonsItemBase implements EducationInterface {

	/**
	 * Instance of the Education\Core class.
	 *
	 * @since 1.6.6
	 *
	 * @var Core
	 */
	protected $education;

	/**
	 * Instance of the Education\Addons class.
	 *
	 * @since 1.6.6
	 *
	 * @var Addons
	 */
	protected $addons;

	/**
	 * Template name for rendering single addon item.
	 *
	 * @since 1.6.6
	 *
	 * @var string
	 */
	protected $single_addon_template;

	/**
	 * Indicate if the current Education feature is allowed to load.
	 * Should be called from the child feature class.
	 *
	 * @since 1.6.6
	 *
	 * @return bool
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 */
	abstract public function allow_load();

	/**
	 * Init.
	 *
	 * @since 1.6.6
	 *
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 */
	public function init() {

		if ( ! $this->allow_load() ) {
			return;
		}

		// Store the instance of the Education core class.
		$this->education = wpforms()->obj( 'education' );

		// Store the instance of the Education\Addons class.
		$this->addons = wpforms()->obj( 'addons' );

		// Define hooks.
		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.6.6
	 */
	abstract public function hooks();

	/**
	 * Display single addon item.
	 *
	 * @since 1.6.6
	 *
	 * @param array $addon Addon data.
	 *
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 * @noinspection PhpMissingParamTypeInspection
	 */
	protected function display_single_addon( $addon ) {

		/**
		 * Filter to disallow addons to be displayed in the Education feature.
		 *
		 * @since 1.8.2
		 *
		 * @param bool   $display Whether to hide the addon.
		 * @param array  $slug    Addon data.
		 */
		$is_disallowed = (bool) apply_filters( 'wpforms_admin_education_addons_item_base_display_single_addon_hide', false, $addon );

		if ( empty( $addon ) || $is_disallowed ) {
			return;
		}

		echo wpforms_render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$this->single_addon_template,
			$addon,
			true
		);
	}

	/**
	 * Prepare field data-attributes for the education actions.
	 * E.g., install, activate, incompatible.
	 *
	 * @since 1.9.4
	 *
	 * @param array $addon Current addon information.
	 *
	 * @return array
	 */
	protected function prepare_field_action_data( array $addon ): array {

		if ( empty( $addon['plugin_allow'] ) ) {
			return [];
		}

		if ( $addon['action'] === 'install' ) {
			return [
				'data'  => [
					'action'  => 'install',
					'name'    => $addon['modal_name'],
					'url'     => $addon['url'],
					'nonce'   => wp_create_nonce( 'wpforms-admin' ),
					'license' => $addon['license_level'],
				],
				'class' => 'education-modal',
			];
		}

		if ( $addon['action'] === 'activate' ) {
			return [
				'data'  => [
					'action' => 'activate',
					'name'   => sprintf( /* translators: %s - addon name. */
						esc_html__( '%s addon', 'wpforms-lite' ),
						$addon['name']
					),
					'path'   => $addon['path'],
					'nonce'  => wp_create_nonce( 'wpforms-admin' ),
				],
				'class' => 'education-modal',
			];
		}

		if ( $addon['action'] === 'incompatible' ) {
			return [
				'data'  => [
					'action'  => 'incompatible',
					'message' => Requirements::get_instance()->get_notice( $addon['path'] ),
				],
				'class' => 'education-modal',
			];
		}

		return [];
	}
}
