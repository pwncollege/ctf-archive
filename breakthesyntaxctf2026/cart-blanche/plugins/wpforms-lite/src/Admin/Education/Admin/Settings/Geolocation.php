<?php

namespace WPForms\Admin\Education\Admin\Settings;

use WPForms\Admin\Education\AddonsItemBase;

/**
 * Admin/Settings/Geolocation Education feature for Lite and Pro.
 *
 * @since 1.6.6
 */
class Geolocation extends AddonsItemBase {

	/**
	 * Slug.
	 *
	 * @since 1.6.6
	 */
	const SLUG = 'geolocation';

	/**
	 * Hooks.
	 *
	 * @since 1.6.6
	 */
	public function hooks() {

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueues' ] );
		add_filter( 'wpforms_settings_defaults', [ $this, 'add_sections' ] );
	}

	/**
	 * Indicate if current Education feature is allowed to load.
	 *
	 * @since 1.6.6
	 *
	 * @return bool
	 */
	public function allow_load() {

		return wpforms_is_admin_page( 'settings', 'geolocation' );
	}

	/**
	 * Enqueues.
	 *
	 * @since 1.6.6
	 */
	public function enqueues() {

		// Lity - lightbox for images.
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
	 * Preview of education features for customers with not enough permissions.
	 *
	 * @since 1.6.6
	 *
	 * @param array $settings Settings sections.
	 *
	 * @return array
	 */
	public function add_sections( $settings ) {

		$addon = $this->addons->get_addon( 'geolocation' );

		if (
			empty( $addon ) ||
			empty( $addon['status'] ) ||
			empty( $addon['action'] )
		) {
			return $settings;
		}

		$settings[ self::SLUG ][ self::SLUG . '-page' ] = [
			'id'       => self::SLUG . '-page',
			'content'  => wpforms_render( 'education/admin/page', $this->template_data(), true ),
			'type'     => 'content',
			'no_label' => true,
			'class'    => [ 'wpforms-education-container-page' ],
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

		$addon      = $this->addons->get_addon( 'geolocation' );
		$images_url = WPFORMS_PLUGIN_URL . 'assets/images/geolocation-education/';
		$params     = [
			'features'             => [
				__( 'City', 'wpforms-lite' ),
				__( 'Latitude/Longitude', 'wpforms-lite' ),
				__( 'Google Places API', 'wpforms-lite' ),
				__( 'Country', 'wpforms-lite' ),
				__( 'Address Autocomplete', 'wpforms-lite' ),
				__( 'Mapbox API', 'wpforms-lite' ),
				__( 'Postal/Zip Code', 'wpforms-lite' ),
				__( 'Embedded Map in Forms', 'wpforms-lite' ),
			],
			'images'               => [
				[
					'url'   => $images_url . 'entry-location.jpg',
					'url2x' => $images_url . 'entry-location@2x.jpg',
					'title' => __( 'Location Info in Entries', 'wpforms-lite' ),
				],
				[
					'url'   => $images_url . 'address-autocomplete.jpg',
					'url2x' => $images_url . 'address-autocomplete@2x.jpg',
					'title' => __( 'Address Autocomplete Field', 'wpforms-lite' ),
				],
				[
					'url'   => $images_url . 'smart-address-field.jpg',
					'url2x' => $images_url . 'smart-address-field@2x.jpg',
					'title' => __( 'Smart Address Field', 'wpforms-lite' ),
				],
			],
			'utm_medium'           => 'Settings - Geolocation',
			'utm_content'          => 'Geolocation Addon',
			'heading_title'        => __( 'Geolocation', 'wpforms-lite' ),
			'heading_description'  => sprintf(
				'<p>%1$s</p>',
				__( 'Do you want to learn more about visitors who fill out your online forms? Our geolocation addon allows you to collect and store your website visitors geolocation data along with their form submission. This insight can help you to be better informed and turn more leads into customers. Furthermore, add a smart address field that autocompletes using the Google Places API.', 'wpforms-lite' )
			),
			'badge'                => __( 'Pro', 'wpforms-lite' ),
			'features_description' => __( 'Powerful location-based insights and features…', 'wpforms-lite' ),
		];

		return array_merge( $params, $addon );
	}
}
