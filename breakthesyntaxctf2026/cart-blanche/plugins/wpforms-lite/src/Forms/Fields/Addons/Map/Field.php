<?php

namespace WPForms\Forms\Fields\Addons\Map;

use WPForms\Forms\Fields\Traits\ProField as ProFieldTrait;
use WPForms_Field;
use WPFormsGeolocation\Admin\Settings\Settings;

/**
 * Map field.
 *
 * @since 1.10.0
 */
class Field extends WPForms_Field {

	/**
	 * Find Nearby Locations option key.
	 *
	 * @since 1.10.0
	 */
	protected const NEARBY_LOCATIONS_KEY = 'wpforms_geolocation_find_nearby_locations';

	/**
	 * Search Radius option key.
	 *
	 * @since 1.10.0
	 */
	protected const NEARBY_LOCATIONS_RADIUS_KEY = 'wpforms_geolocation_search_radius';

	/**
	 * Default search radius.
	 *
	 * @since 1.10.0
	 */
	protected const DEFAULT_SEARCH_RADIUS = 25;

	use ProFieldTrait;

	/**
	 * Whether the addon is active.
	 *
	 * @since 1.10.0
	 *
	 * @var bool
	 */
	private $is_addon_active = false;

	/**
	 * Determine if we should display the field options notice.
	 *
	 * @since 1.10.0
	 *
	 * @var bool
	 */
	protected $display_field_options_notice = true;

	/**
	 * Init class.
	 *
	 * @since 1.10.0
	 *
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 */
	public function init() {

		// Define field type information.
		$this->name             = esc_html__( 'Map', 'wpforms-lite' );
		$this->keywords         = esc_html__( 'map', 'wpforms-lite' );
		$this->type             = 'map';
		$this->icon             = 'fa-map-location-dot';
		$this->order            = 75;
		$this->group            = 'fancy';
		$this->addon_slug       = 'geolocation';
		$this->allow_read_only  = false;
		$this->default_settings = [
			'hide_full_screen'        => '1',
			'hide_map_type'           => '1',
			'hide_location_info'      => '1',
			'hide_street_view'        => '1',
			'hide_camera_control'     => '1',
			'disable_mouse_zooming'   => '1',
			'show_in_entry'           => '1',
			'show_thumbnail_in_entry' => '1',
			'search_radius'           => self::DEFAULT_SEARCH_RADIUS,
		];

		$this->is_addon_active = function_exists( 'wpforms_' . $this->addon_slug );

		$this->init_pro_field();
		$this->hooks();
	}

	/**
	 * Define field hooks.
	 *
	 * @since 1.10.0
	 */
	protected function hooks(): void {}

	/**
	 * Define additional field options.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field Field data and settings.
	 *
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 */
	public function field_options( $field ) {

		$this->basic_field_options( (array) $field );
		$this->advanced_field_options( (array) $field );
	}

	/**
	 * Basic field options.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field Field settings.
	 *
	 * @return void
	 */
	private function basic_field_options( array $field ): void {

		// Options open markup.
		$this->field_option(
			'basic-options',
			$field,
			[
				'markup'      => 'open',
				'after_title' => $this->display_field_options_notice ? $this->get_field_options_notice() : '',
			]
		);

		$this->field_option( 'label', $field );

		$this->field_option( 'description', $field );

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'choices',
				'class'   => 'wpforms-field-option-row-locations',
				'content' => $this->get_location_options( $field ),
			]
		);

		$current_user_id         = get_current_user_id();
		$find_nearby_locations   = (bool) get_user_meta( $current_user_id, self::NEARBY_LOCATIONS_KEY, true );
		$nearby_locations_radius = (int) get_user_meta( $current_user_id, self::NEARBY_LOCATIONS_RADIUS_KEY, true );
		$nearby_locations_radius = $nearby_locations_radius > 0 ? $nearby_locations_radius : self::DEFAULT_SEARCH_RADIUS;

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'find_nearby_locations',
				'content' => $this->field_element(
					'toggle',
					$field,
					[
						'slug'  => 'find_nearby_locations',
						'value' => $find_nearby_locations ? '1' : '0',
						'desc'  => esc_html__( 'Find Nearby Locations', 'wpforms-lite' ),
					],
					false
				),
			]
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'search_radius',
				'class'   => ! $find_nearby_locations ? 'wpforms-hidden' : '',
				'content' =>
					$this->field_element(
						'label',
						$field,
						[
							'slug'  => 'search_radius',
							'value' => esc_html__( 'Search Radius', 'wpforms-lite' ),
						],
						false
					) .
					$this->field_element(
						'select',
						$field,
						[
							'slug'    => 'search_radius',
							'value'   => $nearby_locations_radius,
							'options' => $this->get_search_radius_km_options(),
							'data'    => [
								'miles-options' => wp_json_encode( $this->get_search_radius_miles_options() ),
							],
						],
						false
					),
			]
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'show_locations_list',
				'content' => $this->field_element(
					'toggle',
					$field,
					[
						'slug'  => 'show_locations_list',
						'value' => isset( $field['show_locations_list'] ) ? '1' : '0',
						'desc'  => esc_html__( 'Show List of Locations', 'wpforms-lite' ),
					],
					false
				),
			]
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'allow_location_selection',
				'content' => $this->field_element(
					'toggle',
					$field,
					[
						'slug'  => 'allow_location_selection',
						'value' => isset( $field['allow_location_selection'] ) ? '1' : '0',
						'desc'  => esc_html__( 'Allow Location Selection', 'wpforms-lite' ),
					],
					false
				),
			]
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'zoom_level',
				'content' =>
					$this->field_element(
						'label',
						$field,
						[
							'slug'  => 'zoom_level',
							'value' => esc_html__( 'Zoom Level', 'wpforms-lite' ),
						],
						false
					) .
					$this->field_element(
						'select',
						$field,
						[
							'class'   => 'wpforms-field-map-settings',
							'data'    => [
								'map-control' => 'zoom',
							],
							'slug'    => 'zoom_level',
							'value'   => ! empty( $field['zoom_level'] ) && $field['zoom_level'] >= 0 && $field['zoom_level'] <= 22 ? (int) $field['zoom_level'] : 15,
							'options' => range( 0, 22 ),
						],
						false
					),
			]
		);

		$this->field_option( 'basic-options', $field, [ 'markup' => 'close' ] );
	}

	/**
	 * Advanced field options.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field Field settings.
	 *
	 * @return void
	 */
	private function advanced_field_options( array $field ): void { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		$is_mapbox = $this->get_active_provider_slug() === 'mapbox-search';

		$this->field_option( 'advanced-options', $field, [ 'markup' => 'open' ] );

		$this->field_option( 'size', $field );
		$this->field_option( 'css', $field );

		printf( '<div class="wpforms-field-option-row-subtitle">%1$s</div>', esc_html__( 'Presentational Settings', 'wpforms-lite' ) );

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'hide_full_screen',
				'content' => $this->field_element(
					'toggle',
					$field,
					[
						'class' => 'wpforms-field-map-settings',
						'data'  => [
							'map-control' => 'fullscreenControl',
						],
						'slug'  => 'hide_full_screen',
						'value' => isset( $field['hide_full_screen'] ) ? '1' : '0',
						'desc'  => esc_html__( 'Hide Full Screen ', 'wpforms-lite' ),
					],
					false
				),
			]
		);

		if ( ! $is_mapbox ) {
			$this->field_element(
				'row',
				$field,
				[
					'slug'    => 'hide_map_type',
					'content' => $this->field_element(
						'toggle',
						$field,
						[
							'class' => 'wpforms-field-map-settings',
							'data'  => [
								'map-control' => 'mapTypeControl',
							],
							'slug'  => 'hide_map_type',
							'value' => isset( $field['hide_map_type'] ) ? '1' : '0',
							'desc'  => esc_html__( 'Hide Map Type ', 'wpforms-lite' ),
						],
						false
					),
				]
			);

			$this->field_element(
				'row',
				$field,
				[
					'slug'    => 'hide_location_info',
					'content' => $this->field_element(
						'toggle',
						$field,
						[
							'slug'  => 'hide_location_info',
							'value' => isset( $field['hide_location_info'] ) ? '1' : '0',
							'desc'  => esc_html__( 'Hide Location Info ', 'wpforms-lite' ),
						],
						false
					),
				]
			);

			$this->field_element(
				'row',
				$field,
				[
					'slug'    => 'hide_street_view',
					'content' => $this->field_element(
						'toggle',
						$field,
						[
							'class' => 'wpforms-field-map-settings',
							'data'  => [
								'map-control' => 'streetViewControl',
							],
							'slug'  => 'hide_street_view',
							'value' => isset( $field['hide_street_view'] ) ? '1' : '0',
							'desc'  => esc_html__( 'Hide Street View ', 'wpforms-lite' ),
						],
						false
					),
				]
			);

			printf( '<div class="wpforms-field-option-row-subtitle">%1$s</div>', esc_html__( 'Interactive Settings', 'wpforms-lite' ) );

			$this->field_element(
				'row',
				$field,
				[
					'slug'    => 'hide_camera_control',
					'content' => $this->field_element(
						'toggle',
						$field,
						[
							'class' => 'wpforms-field-map-settings',
							'data'  => [
								'map-control' => 'cameraControl',
							],
							'slug'  => 'hide_camera_control',
							'value' => isset( $field['hide_camera_control'] ) ? '1' : '0',
							'desc'  => esc_html__( 'Hide Camera Control ', 'wpforms-lite' ),
						],
						false
					),
				]
			);
		}

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'hide_zoom',
				'content' => $this->field_element(
					'toggle',
					$field,
					[
						'class' => 'wpforms-field-map-settings',
						'data'  => [
							'map-control' => 'zoomControl',
						],
						'slug'  => 'hide_zoom',
						'value' => isset( $field['hide_zoom'] ) ? '1' : '0',
						'desc'  => esc_html__( 'Hide Zoom ', 'wpforms-lite' ),
					],
					false
				),
			]
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'disable_dragging',
				'content' => $this->field_element(
					'toggle',
					$field,
					[
						'slug'  => 'disable_dragging',
						'value' => isset( $field['disable_dragging'] ) ? '1' : '0',
						'desc'  => esc_html__( 'Disable Dragging ', 'wpforms-lite' ),
					],
					false
				),
			]
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'disable_mouse_zooming',
				'content' => $this->field_element(
					'toggle',
					$field,
					[
						'slug'  => 'disable_mouse_zooming',
						'value' => isset( $field['disable_mouse_zooming'] ) ? '1' : '0',
						'desc'  => esc_html__( 'Disable Mouse Zooming ', 'wpforms-lite' ),
					],
					false
				),
			]
		);

		printf( '<div class="wpforms-field-option-row-subtitle">%1$s</div>', esc_html__( 'Other', 'wpforms-lite' ) );

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'show_in_entry',
				'content' => $this->field_element(
					'toggle',
					$field,
					[
						'slug'  => 'show_in_entry',
						'value' => isset( $field['show_in_entry'] ) ? '1' : '0',
						'desc'  => esc_html__( 'Show in Entry ', 'wpforms-lite' ),
					],
					false
				),
			]
		);

		$this->field_element(
			'row',
			$field,
			[
				'slug'    => 'show_thumbnail_in_entry',
				'content' => $this->field_element(
					'toggle',
					$field,
					[
						'slug'  => 'show_thumbnail_in_entry',
						'value' => isset( $field['show_thumbnail_in_entry'] ) ? '1' : '0',
						'desc'  => esc_html__( 'Show Thumbnail in Entry ', 'wpforms-lite' ),
					],
					false
				),
			]
		);

		$this->field_option( 'label_hide', $field );
		$this->field_option( 'advanced-options', $field, [ 'markup' => 'close' ] );
	}

	/**
	 * Get active provider slug.
	 *
	 * @since 1.10.0
	 */
	protected function get_active_provider_slug(): string {

		if ( ! class_exists( Settings::class ) ) {
			return '';
		}

		return ( new Settings() )->get_current_provider();
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field Field data.
	 *
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 */
	public function field_preview( $field ) {

		$this->field_preview_option(
			'label',
			$field,
			[
				'label_badge' => $this->get_field_preview_badge(),
			]
		);

		$size     = $field['size'] ?? 'medium';
		$field_id = $field['id'] ?? 0;

		$this->print_map( $size, $field_id );
		$this->print_location_list_preview( $field );

		$this->field_preview_option( 'description', $field );

		$this->field_preview_option( 'hide-remaining', $field );
	}

	/**
	 * Print map HTML.
	 *
	 * @since 1.10.0
	 *
	 * @param string $size     Field size.
	 * @param int    $field_id Field ID.
	 *
	 * @noinspection UnnecessaryCastingInspection
	 * @noinspection PhpCastIsUnnecessaryInspection
	 */
	protected function print_map( string $size, int $field_id ): void {

		printf(
			'<div class="wpforms-field-row wpforms-field-%1$s wpforms-geolocation-map" id="wpforms-field-%2$d-map"></div>',
			esc_attr( $size ),
			(int) $field_id
		);
	}

	/**
	 * Print location list preview.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field Field settings.
	 *
	 * @noinspection PhpUnusedLocalVariableInspection
	 * @noinspection HtmlWrongAttributeValue
	 */
	private function print_location_list_preview( array $field ): void {

		$choices                  = $field['choices'] ?? [];
		$show_locations_list      = ! empty( $field['show_locations_list'] );
		$allow_location_selection = $show_locations_list && ! empty( $field['allow_location_selection'] ) && count( $choices ) > 1;

		printf(
			'<ul class="wpforms-field-map-choices wpforms-field-row%1$s">',
			! $show_locations_list ? ' wpforms-hidden' : ''
		);

		foreach ( $choices as $key => $choice ) {
			echo '<li>';

			printf( '<input type="%1$s">', $allow_location_selection ? 'radio' : 'hidden' );

			echo '<label>';

			printf( '<span class="wpforms-field-map-location-name">%1$s</span>', isset( $choice['name'] ) ? esc_html( $choice['name'] ) : '' );
			printf( '<span class="wpforms-field-map-location-address">%1$s</span>', isset( $choice['address'] ) ? esc_html( $choice['address'] ) : '' );

			echo '</label>';

			echo '</li>';
		}
		echo '</ul>';
	}

	/**
	 * Determine if the current choice is a valid marker.
	 *
	 * @since 1.10.0
	 *
	 * @param array $choice Choice data.
	 */
	protected function is_valid_marker( array $choice ): bool {

		if ( ! isset( $choice['latitude'], $choice['longitude'] ) ) {
			return false;
		}

		if ( wpforms_is_empty_string( $choice['latitude'] ) || wpforms_is_empty_string( $choice['longitude'] ) ) {
			return false;
		}

		if ( ! empty( $choice['marker_type'] ) && $choice['marker_type'] === 'image' && empty( $choice['image'] ) ) {
			return false;
		}

		if (
			( ! isset( $choice['name'] ) || wpforms_is_empty_string( $choice['name'] ) )
			&& ( ! isset( $choice['address'] ) || wpforms_is_empty_string( $choice['address'] ) )
		) {
			return false;
		}

		return true;
	}

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field      Field settings.
	 * @param array $deprecated Deprecated array.
	 * @param array $form_data  Form data and settings.
	 *
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 */
	public function field_display( $field, $deprecated, $form_data ) {
	}

	/**
	 * Get Locations options HTML template.
	 *
	 * @since 1.10.0
	 *
	 * @param array $field Field settings.
	 *
	 * @noinspection PhpCastIsUnnecessaryInspection
	 * @noinspection UnnecessaryCastingInspection
	 *
	 * @return string
	 */
	private function get_location_options( array $field ): string {

		$field_id  = ! empty( $field['id'] ) ? (int) $field['id'] : 0;
		$locations = $field['choices'] ?? [ [] ];
		$next_id   = max( array_keys( $locations ) ) + 1;

		ob_start();

		$this->field_element(
			'label',
			$field,
			[
				'slug'  => 'locations',
				'value' => esc_html__( 'Locations', 'wpforms-lite' ),
			]
		);

		printf(
			'<ul class="choices-list wpforms-undo-redo-container" data-next-id="%1$d" data-field-id="%2$d" data-field-type="location">',
			(int) $next_id,
			(int) $field_id
		);

		foreach ( $locations as $location_index => $location ) {
			$this->print_location_row( $location, (int) $location_index, $field_id );
		}

		echo '</ul>';

		return ob_get_clean();
	}

	/**
	 * Print Locations options row.
	 *
	 * @since 1.10.0
	 *
	 * @param array $location       Location data.
	 * @param int   $location_index Index.
	 * @param int   $field_id       Field ID.
	 *
	 * @return void
	 *
	 * @noinspection HtmlFormInputWithoutLabel
	 */
	private function print_location_row( array $location, int $location_index, int $field_id ): void {

		$location = wp_parse_args(
			array_filter( $location ),
			[
				'name'        => '',
				'address'     => '',
				'description' => '',
				'marker_type' => 'icon',
				'icon'        => 'face-smile',
				'icon_style'  => 'regular',
				'icon_color'  => '#d63638',
				'latitude'    => '',
				'longitude'   => '',
				'image'       => '',
				'size'        => 'small',
			]
		);

		$base      = sprintf( 'fields[%s][choices][%d]', wpforms_validate_field_id( $field_id ), absint( $location_index ) );
		$id_base   = sprintf( 'fields-%s-choices-%d-', wpforms_validate_field_id( $field_id ), absint( $location_index ) );
		$has_image = ! empty( $location['image'] );
		?>
		<li data-key="<?php echo absint( $location_index ); ?>" class="wpforms-geolocation-map-field-location-size-<?php echo esc_attr( $location['size'] ); ?> wpforms-geolocation-map-field-location-<?php echo esc_attr( $location['marker_type'] ); ?>">
			<span class="move"><i class="fa fa-grip-lines"></i></span>
			<input type="text" name="<?php echo esc_attr( $base ); ?>[name]" value="<?php echo esc_attr( $location['name'] ); ?>" data-1p-ignore="true" class="label wpforms-geolocation-map-field-location-name" placeholder="<?php esc_attr_e( 'Name', 'wpforms-lite' ); ?>">

			<a class="add" href="#"><i class="fa fa-plus-circle"></i></a>
			<a class="remove" href="#"><i class="fa fa-minus-circle"></i></a>

			<input type="text" name="<?php echo esc_attr( $base ); ?>[address]" id="<?php echo esc_attr( $id_base ); ?>address" value="<?php echo esc_attr( $location['address'] ); ?>" class="wpforms-geolocation-map-field-location-address" placeholder="<?php esc_attr_e( 'Address', 'wpforms-lite' ); ?>">
			<input type="hidden" name="<?php echo esc_attr( $base ); ?>[latitude]" value="<?php echo esc_attr( $location['latitude'] ); ?>" class="wpforms-geolocation-map-field-location-latitude">
			<input type="hidden" name="<?php echo esc_attr( $base ); ?>[longitude]" value="<?php echo esc_attr( $location['longitude'] ); ?>" class="wpforms-geolocation-map-field-location-longitude">
			<input type="text" name="<?php echo esc_attr( $base ); ?>[description]" value="<?php echo esc_attr( $location['description'] ); ?>" class="wpforms-geolocation-map-field-location-description" placeholder="<?php esc_attr_e( 'Description', 'wpforms-lite' ); ?>">

			<select name="<?php echo esc_attr( $base ); ?>[marker_type]" class="wpforms-geolocation-map-field-location-marker-type">
				<option value="icon" <?php selected( 'icon', $location['marker_type'] ); ?>><?php esc_html_e( 'Icon', 'wpforms-lite' ); ?></option>
				<option value="image" <?php selected( 'image', $location['marker_type'] ); ?>><?php esc_html_e( 'Image', 'wpforms-lite' ); ?></option>
			</select>

            <select name="<?php echo esc_attr( $base ); ?>[size]" class="wpforms-geolocation-map-field-location-size">
                <option value="small" <?php selected( 'small', $location['size'] ); ?>><?php esc_html_e( 'Small', 'wpforms-lite' ); ?></option>
                <option value="medium" <?php selected( 'medium', $location['size'] ); ?>><?php esc_html_e( 'Medium', 'wpforms-lite' ); ?></option>
                <option value="large" <?php selected( 'large', $location['size'] ); ?>><?php esc_html_e( 'Large', 'wpforms-lite' ); ?></option>
            </select>

			<?php // Icon Choice. ?>
			<div class="wpforms-icon-select">
				<i class="ic-fa-preview ic-fa-<?php echo esc_attr( $location['icon_style'] ); ?> ic-fa-<?php echo esc_attr( $location['icon'] ); ?>"></i>
				<span><?php echo esc_html( $location['icon'] ); ?></span>
				<input type="hidden" name="<?php echo esc_attr( $base ); ?>[icon]" value="<?php echo esc_attr( $location['icon'] ); ?>" class="source-icon">
				<input type="hidden" name="<?php echo esc_attr( $base ); ?>[icon_style]" value="<?php echo esc_attr( $location['icon_style'] ); ?>" class="source-icon-style">
			</div>

			<div class="wpforms-geolocation-map-field-location-icon-color wpforms-panel-field-color wpforms-panel-field-colorpicker">
				<input
                        type="text"
                        name="<?php echo esc_attr( $base ); ?>[icon_color]"
                        value="<?php echo esc_attr( $location['icon_color'] ); ?>" class="wpforms-color-picker"
                        data-swatches="#D63638|#E27730|#FFB900|#00A32A|#0399ED|#036AAB|#7A30E2|#E230BB"
                        data-fallback-color="<?php echo esc_attr( $location['icon_color'] ); ?>">
			</div>

			<?php // Image Choice. ?>
			<div class="wpforms-image-upload">
				<button class="wpforms-btn wpforms-btn-sm wpforms-btn-blue wpforms-btn-block wpforms-image-upload-add" data-after-upload="hide"<?php echo $has_image ? ' style="display:none;"' : ''; ?>><?php esc_html_e( 'Upload Image', 'wpforms-lite' ); ?></button>
				<input type="hidden" name="<?php echo esc_attr( $base ); ?>[image]" value="<?php echo esc_url_raw( $location['image'] ); ?>" class="source">
				<div class="preview"><?php if ( $has_image ) { ?>
					<img src="<?php echo esc_url_raw( $location['image'] ); ?>"><a href="#" title="<?php esc_attr_e( 'Remove Image', 'wpforms-lite' ); ?>" class="wpforms-image-upload-remove"><i class="fa fa-trash-o"></i></a>
				<?php } ?></div>
			</div>
		</li>
		<?php
	}

	/**
	 * Get search radius options in kilometers.
	 *
	 * @since 1.10.0
	 *
	 * @return array
	 */
	private function get_search_radius_km_options(): array {

		return [
			10  => esc_html__( '10 km', 'wpforms-lite' ),
			25  => esc_html__( '25 km', 'wpforms-lite' ),
			50  => esc_html__( '50 km', 'wpforms-lite' ),
			100 => esc_html__( '100 km', 'wpforms-lite' ),
		];
	}

	/**
	 * Get search radius options in miles.
	 *
	 * @since 1.10.0
	 *
	 * @return array
	 */
	private function get_search_radius_miles_options(): array {

		return [
			10  => esc_html__( '10 mi', 'wpforms-lite' ),
			25  => esc_html__( '25 mi', 'wpforms-lite' ),
			50  => esc_html__( '50 mi', 'wpforms-lite' ),
			100 => esc_html__( '100 mi', 'wpforms-lite' ),
		];
	}
}
