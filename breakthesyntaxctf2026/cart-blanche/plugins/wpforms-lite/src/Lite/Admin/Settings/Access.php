<?php

namespace WPForms\Lite\Admin\Settings;

use WPForms\Admin\Education\Helpers;

/**
 * Settings Access tab.
 *
 * @since 1.5.8
 */
class Access {

	/**
	 * View slug.
	 *
	 * @since 1.5.8
	 *
	 * @var string
	 */
	const SLUG = 'access';

	/**
	 * Constructor.
	 *
	 * @since 1.5.8
	 */
	public function __construct() {

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.5.8
	 */
	public function hooks() {

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueues' ] );
		add_filter( 'wpforms_settings_tabs', [ $this, 'add_tab' ] );
		add_filter( 'wpforms_settings_defaults', [ $this, 'add_section' ] );
	}

	/**
	 * Enqueues.
	 *
	 * @since 1.5.8
	 */
	public function enqueues() {

		if ( ! wpforms_is_admin_page( 'settings', self::SLUG ) ) {
			return;
		}

		// Lity.
		wp_enqueue_style(
			'wpforms-lity',
			WPFORMS_PLUGIN_URL . 'assets/lib/lity/lity.min.css',
			null,
			'3.0.0'
		);

		wp_enqueue_script(
			'wpforms-lity',
			WPFORMS_PLUGIN_URL . 'assets/lib/lity/lity.min.js',
			[ 'jquery' ],
			'3.0.0',
			true
		);
	}

	/**
	 * Add Access tab.
	 *
	 * @since 1.5.8
	 *
	 * @param array $tabs Array of tabs.
	 *
	 * @return array Array of tabs.
	 */
	public function add_tab( $tabs ) {

		$tab = [
			self::SLUG => [
				'name'   => esc_html__( 'Access', 'wpforms-lite' ),
				'form'   => false,
				'submit' => false,
			],
		];

		return wpforms_list_insert_after( $tabs, 'geolocation', $tab );
	}

	/**
	 * Add Access settings section.
	 *
	 * @since 1.5.8
	 *
	 * @param array $settings Settings sections.
	 *
	 * @return array
	 */
	public function add_section( $settings ) {

		$settings[ self::SLUG ][ self::SLUG . '-page' ] = [
			'id'       => self::SLUG . '-page',
			'content'  => wpforms_render( 'education/admin/page', $this->template_data(),true ),
			'type'     => 'content',
			'no_label' => true,
		];

		return $settings;
	}

	/**
	 * Get the template data.
	 *
	 * @since 1.8.6
	 *
	 * @return array
	 */
	private function template_data(): array {

		$images_url = WPFORMS_PLUGIN_URL . 'assets/images/lite-settings-access/';

		return [
			'features'             => [
				__( 'Create Forms', 'wpforms-lite' ),
				__( 'Delete Forms', 'wpforms-lite' ),
				__( 'Edit Forms Entries', 'wpforms-lite' ),
				__( 'Edit Forms', 'wpforms-lite' ),
				__( 'Delete Others Forms', 'wpforms-lite' ),
				__( 'Edit Others Forms Entries', 'wpforms-lite' ),
				__( 'Edit Others Forms', 'wpforms-lite' ),
				__( 'View Forms Entries', 'wpforms-lite' ),
				__( 'Delete Forms Entries', 'wpforms-lite' ),
				__( 'View Forms', 'wpforms-lite' ),
				__( 'View Others Forms Entries', 'wpforms-lite' ),
				__( 'Delete Others Forms Entries', 'wpforms-lite' ),
				__( 'View Others Forms', 'wpforms-lite' ),
			],
			'images'               => [
				[
					'url'   => $images_url . 'screenshot-access-controls.png',
					'url2x' => $images_url . 'screenshot-access-controls@2x.png',
					'title' => __( 'Simple Built-in Controls', 'wpforms-lite' ),
				],
				[
					'url'   => $images_url . 'screenshot-members.png',
					'url2x' => $images_url . 'screenshot-members@2x.png',
					'title' => __( 'Members Integration', 'wpforms-lite' ),
				],
				[
					'url'   => $images_url . 'screenshot-user-role-editor.png',
					'url2x' => $images_url . 'screenshot-user-role-editor@2x.png',
					'title' => __( 'User Role Editor Integration', 'wpforms-lite' ),
				],
			],
			'utm_medium'           => 'Settings - Access',
			'utm_content'          => 'Access Controls',
			'heading_title'        => __( 'Access Controls', 'wpforms-lite' ),
			'heading_description'  => sprintf(
				'<p>%1$s</p>',
				__( 'Access controls allows you to manage and customize access to WPForms functionality. You can easily grant or restrict access using the simple built-in controls, or use our official integrations with Members and User Role Editor plugins.', 'wpforms-lite' )
			),
			'badge'                => __( 'Pro', 'wpforms-lite' ),
			'features_description' => __( 'Custom access to the following capabilities…', 'wpforms-lite' ),
		];
	}
}
