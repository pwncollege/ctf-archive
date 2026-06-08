<?php
/**
 * Helper functions to work with form fields, generic and specific to certain field types.
 *
 * @since 1.8.0
 */

/**
 * Determine if we should show the "Show Values" toggle for checkbox, radio, or
 * select fields in form builder. Legacy.
 *
 * @since 1.5.0
 *
 * @return bool
 */
function wpforms_show_fields_options_setting(): bool {

	/**
	 * Filter to show or hide the "Show Values" toggle for checkbox, radio, or select fields in form builder.
	 *
	 * @since 1.5.0
	 *
	 * @param bool $show Show or hide the "Show Values" toggle.
	 */
	return (bool) apply_filters( 'wpforms_fields_show_options_setting', false );
}

/**
 * Return field choice properties for field configured with dynamic choices.
 *
 * @since 1.4.5
 *
 * @param array $field     Field settings.
 * @param int   $form_id   Form ID.
 * @param array $form_data Form data and settings.
 *
 * @return false|array
 */
function wpforms_get_field_dynamic_choices( $field, $form_id, $form_data = [] ) {

	if ( empty( $field['dynamic_choices'] ) ) {
		return false;
	}

	$choices = [];

	if ( $field['dynamic_choices'] === 'post_type' ) {

		if ( empty( $field['dynamic_post_type'] ) ) {
			return false;
		}

		$posts = wpforms_get_hierarchical_object(
		/**
		 * Filter the arguments used to retrieve posts for dynamic choices.
		 *
		 * @since 1.4.5
		 *
		 * @param array $args    Array of arguments for retrieving posts.
		 * @param array $field   Field settings.
		 * @param int   $form_id Form ID.
		 */
			apply_filters(
				'wpforms_dynamic_choice_post_type_args',
				[
					'post_type'      => $field['dynamic_post_type'],
					'posts_per_page' => -1,
					'orderby'        => 'title',
					'order'          => 'ASC',
				],
				$field,
				$form_id
			),
			true
		);

		foreach ( $posts as $post ) {
			$choices[] = [
				'value' => $post->ID,
				'label' => wpforms_get_post_title( $post ),
				'depth' => isset( $post->depth ) ? absint( $post->depth ) : 1,
			];
		}
	} elseif ( $field['dynamic_choices'] === 'taxonomy' ) {

		if ( empty( $field['dynamic_taxonomy'] ) ) {
			return false;
		}

		$terms = wpforms_get_hierarchical_object(
		/**
		 * Filter the arguments used to retrieve terms for dynamic choices.
		 *
		 * @since 1.4.5
		 *
		 * @param array $args      Array of arguments for retrieving terms.
		 * @param array $field     Field settings.
		 * @param array $form_data Form data.
		 */
			apply_filters(
				'wpforms_dynamic_choice_taxonomy_args',
				[
					'taxonomy'   => $field['dynamic_taxonomy'],
					'hide_empty' => false,
				],
				$field,
				$form_data
			),
			true
		);

		foreach ( $terms as $term ) {
			$choices[] = [
				'value' => $term->term_id,
				'label' => wpforms_get_term_name( $term ),
				'depth' => isset( $term->depth ) ? absint( $term->depth ) : 1,
			];
		}
	}

	return $choices;
}

/**
 * Build and return either a taxonomy or post type object nested to accommodate any hierarchy.
 *
 * @since 1.3.9
 * @since 1.5.0 Return an array only. Empty array of no data.
 *
 * @param array $args Object arguments to pass to data retrieval function.
 * @param bool  $flat Preserve hierarchy or not. False by default - preserve it.
 *
 * @return array
 */
function wpforms_get_hierarchical_object( $args = [], $flat = false ): array { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh, Generic.Metrics.NestingLevel.MaxExceeded

	if ( empty( $args['taxonomy'] ) && empty( $args['post_type'] ) ) {
		return [];
	}

	$children   = [];
	$parents    = [];
	$ref_parent = '';
	$ref_name   = '';
	$number     = 0;

	if ( ! empty( $args['post_type'] ) ) {

		$defaults   = [
			'posts_per_page' => - 1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		];
		$args       = wp_parse_args( $args, $defaults );
		$items      = get_posts( $args );
		$ref_parent = 'post_parent';
		$ref_id     = 'ID';
		$ref_name   = 'post_title';
		$number     = ! empty( $args['posts_per_page'] ) ? $args['posts_per_page'] : 0;

	} elseif ( ! empty( $args['taxonomy'] ) ) {

		$defaults   = [
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		];
		$args       = wp_parse_args( $args, $defaults );
		$items      = get_terms( $args );
		$ref_parent = 'parent';
		$ref_id     = 'term_id';
		$ref_name   = 'name';
		$number     = ! empty( $args['number'] ) ? $args['number'] : 0;
	}

	if ( empty( $items ) || is_wp_error( $items ) ) {
		return [];
	}

	foreach ( $items as $item ) {
		if ( $item->{$ref_parent} ) {
			$children[ $item->{$ref_id} ]     = $item;
			$children[ $item->{$ref_id} ]->ID = (int) $item->{$ref_id};
		} else {
			$parents[ $item->{$ref_id} ]     = $item;
			$parents[ $item->{$ref_id} ]->ID = (int) $item->{$ref_id};
		}
	}

	$children_count = count( $children );
	$is_limited     = $number > 1;

	// We can't guarantee that all children have a parent if there is a limit in the request.
	// Hence, we have to make sure that there is a parent for every child.
	if ( $is_limited && $children_count ) {
		foreach ( $children as $child ) {
			// The current WP_Post or WP_Term object to operate on.
			$current = $child;

			// The current object's parent is already in the list of parents or children.
			if ( ! empty( $parents[ $child->{$ref_parent} ] ) || ! empty( $children[ $child->{$ref_parent} ] ) ) {
				continue;
			}

			do {
				// Set the current object to the previous iteration's parent object.
				$current = ! empty( $args['post_type'] ) ? get_post( $current->{$ref_parent} ) : get_term( $current->{$ref_parent} );

				if ( $current->{$ref_parent} === 0 ) {
					// We've reached the top of the hierarchy.
					$parents[ $current->{$ref_id} ]     = $current;
					$parents[ $current->{$ref_id} ]->ID = (int) $current->{$ref_id};
				} else {
					// We're still in the middle of the hierarchy.
					$children[ $current->{$ref_id} ]     = $current;
					$children[ $current->{$ref_id} ]->ID = (int) $current->{$ref_id};
				}
			} while ( $current->{$ref_parent} > 0 );
		}
	}

	while ( $children_count >= 1 ) {
		foreach ( $children as $child ) {
			_wpforms_get_hierarchical_object_search( $child, $parents, $children, $ref_parent );

			// $children is modified by reference, so we need to recount to make sure we met the limits.
			$children_count = count( $children );
		}
	}

	// Sort nested child objects alphabetically using natural order, applies only
	// to ordering by entry title or term name.
	if ( in_array( $args['orderby'], [ 'title', 'name' ], true ) ) {
		_wpforms_sort_hierarchical_object( $parents, $args['orderby'], $args['order'] );
	}

	if ( $flat ) {
		$parents_flat = [];

		_wpforms_get_hierarchical_object_flatten( $parents, $parents_flat, $ref_name );

		$parents = $parents_flat;
	}

	return $is_limited ? array_slice( $parents, 0, $number ) : $parents;
}

/**
 * Sort a nested array of objects.
 *
 * @since 1.6.5
 *
 * @param array  $objects An array of objects to sort.
 * @param string $orderby The object field to order by.
 * @param string $order   Order direction.
 */
function _wpforms_sort_hierarchical_object( $objects, $orderby, $order ) {

	// Map WP_Query/WP_Term_Query orderby to WP_Post/WP_Term property.
	$map = [
		'title' => 'post_title',
		'name'  => 'name',
	];

	foreach ( $objects as $object ) {
		if ( ! isset( $object->children ) ) {
			continue;
		}

		uasort(
			$object->children,
			static function ( $a, $b ) use ( $map, $orderby, $order ) {

				/**
				 * This covers most cases and works for most languages.
				 * For some – e.g.,
				 * European languages that use extended latin charset (Polish, German, etc.)
				 * it will sort the objects into two groups – base and extended, properly sorted within each group.
				 * Making it even more robust requires either additional PHP extensions to be installed on the server
				 * or using heavy (and slow) conversions and computations.
				 */
				return $order === 'ASC' ?
					strnatcasecmp( $a->{$map[ $orderby ]}, $b->{$map[ $orderby ]} ) :
					strnatcasecmp( $b->{$map[ $orderby ]}, $a->{$map[ $orderby ]} );
			}
		);

		_wpforms_sort_hierarchical_object( $object->children, $orderby, $order );
	}
}

/**
 * Search a given array and find the parent of the provided object.
 *
 * @since 1.3.9
 *
 * @param object $child      Current child.
 * @param array  $parents    Parents list.
 * @param array  $children   Children list.
 * @param string $ref_parent Parent reference.
 */
function _wpforms_get_hierarchical_object_search( $child, &$parents, &$children, $ref_parent ) {

	foreach ( $parents as $parent ) {
		if ( $parent->ID === $child->{$ref_parent} ) {
			$parent->children               = $parent->children ?? [];
			$parent->children[ $child->ID ] = $child;

			unset( $children[ $child->ID ] );
		} elseif ( ! empty( $parent->children ) && is_array( $parent->children ) ) {
			_wpforms_get_hierarchical_object_search( $child, $parent->children, $children, $ref_parent );
		}
	}
}

/**
 * Flatten a hierarchical object.
 *
 * @since 1.3.9
 *
 * @param array  $h_array  Hierarchical array to process.
 * @param array  $output   Processed output.
 * @param string $ref_name Name reference.
 * @param int    $level    Nesting level.
 */
function _wpforms_get_hierarchical_object_flatten( $h_array, &$output, $ref_name = 'name', $level = 0 ) {

	/**
	 * Filter the hierarchical object indicator.
	 *
	 * @since 1.3.9
	 *
	 * @param string $indicator Hierarchical object indicator.
	 */
	$indicator = (string) apply_filters( 'wpforms_hierarchical_object_indicator', '&mdash;' );

	foreach ( $h_array as $item ) {
		$item->{$ref_name}   = str_repeat( $indicator, $level ) . ' ' . $item->{$ref_name};
		$item->depth         = $level + 1;
		$output[ $item->ID ] = $item;

		if ( ! empty( $item->children ) ) {
			_wpforms_get_hierarchical_object_flatten( $item->children, $output, $ref_name, $level + 1 );
			unset( $output[ $item->ID ]->children );
		}
	}
}

/**
 * Get sanitized post title or "no title" placeholder.
 *
 * The placeholder is prepended with the post ID.
 *
 * @since 1.7.6
 *
 * @param WP_Post|mixed $post Post object.
 *
 * @return string Post title.
 */
function wpforms_get_post_title( $post ): string {

	return (
		wpforms_is_empty_string( trim( $post->post_title ) )
			/* translators: %d - post ID. */
			? sprintf( __( '#%d (no title)', 'wpforms-lite' ), absint( $post->ID ) )
			: $post->post_title
	);
}

/**
 * Get a sanitized term name or "no name" placeholder.
 *
 * The placeholder is prepended with the term ID.
 *
 * @since 1.7.6
 *
 * @param WP_Term $term Term object.
 *
 * @return string Term name.
 */
function wpforms_get_term_name( WP_Term $term ): string {

	return (
		wpforms_is_empty_string( trim( $term->name ) )
			/* translators: %d - taxonomy term ID. */
			? sprintf( __( '#%d (no name)', 'wpforms-lite' ), absint( $term->term_id ) )
			: trim( $term->name )
	);
}

/**
 * Return information about pages if the form has multiple pages.
 *
 * @since 1.3.7
 *
 * @param WP_Post|array $form Form data.
 *
 * @return false|array Page Break details or false.
 */
function wpforms_get_pagebreak_details( $form = false ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

	if ( ! wpforms()->is_pro() ) {
		return false;
	}

	$details = [];
	$pages   = 1;

	if ( is_object( $form ) && ! empty( $form->post_content ) ) {
		$form_data = wpforms_decode( $form->post_content );
	} elseif ( is_array( $form ) ) {
		$form_data = $form;
	}

	if ( empty( $form_data['fields'] ) ) {
		return false;
	}

	foreach ( $form_data['fields'] as $field ) {

		if ( $field['type'] !== 'pagebreak' ) {
			continue;
		}

		if ( empty( $field['position'] ) ) {
			++$pages;

			$details['total']   = $pages;
			$details['pages'][] = $field;
		} elseif ( $field['position'] === 'top' ) {
			$details['top'] = $field;
		} elseif ( $field['position'] === 'bottom' ) {
			$details['bottom'] = $field;
		}
	}

	if ( ! empty( $details ) ) {
		$details['top']     = empty( $details['top'] ) ? [] : $details['top'];
		$details['bottom']  = empty( $details['bottom'] ) ? [] : $details['bottom'];
		$details['current'] = 1;

		return $details;
	}

	return false;
}

/**
 * Return available builder fields.
 *
 * @since 1.8.5
 *
 * @param string $group Group name.
 *
 * @return array
 */
function wpforms_get_builder_fields( string $group = '' ): array {

	$fields = [
		'standard' => [
			'group_name' => esc_html__( 'Standard Fields', 'wpforms-lite' ),
			'fields'     => [],
		],
		'fancy'    => [
			'group_name' => esc_html__( 'Fancy Fields', 'wpforms-lite' ),
			'fields'     => [],
		],
		'payment'  => [
			'group_name' => esc_html__( 'Payment Fields', 'wpforms-lite' ),
			'fields'     => [],
		],
	];

	/**
	 * Allows developers to modify the content of the Add Field tab.
	 *
	 * With this filter, developers can add their own fields or even fields groups.
	 *
	 * @since 1.4.0
	 *
	 * @param array $fields {
	 *     Fields data multidimensional array.
	 *
	 *     @param array $standard Standard fields group.
	 *         @param string $group_name Group name.
	 *         @param array  $fields     Fields array.
	 *
	 *     @param array $fancy    Fancy fields group.
	 *         @param string $group_name Group name.
	 *         @param array  $fields     Fields array.
	 *
	 *     @param array $payment  Payment fields group.
	 *         @param string $group_name Group name.
	 *         @param array  $fields     Fields array.
	 * }
	 */
	$fields = apply_filters( 'wpforms_builder_fields_buttons', $fields ); // phpcs:ignore WPForms.Comments.ParamTagHooks.InvalidParamTagsQuantity

	// If a group is not specified, return all fields.
	if ( empty( $group ) ) {
		return $fields;
	}

	// If a group is specified, return only fields from that group.
	if ( isset( $fields[ $group ] ) ) {
		return $fields[ $group ]['fields'];
	}

	return [];
}

/**
 * Get payments fields.
 *
 * @since 1.8.5
 *
 * @return array
 */
function wpforms_get_payments_fields(): array {

	// Some fields are added dynamically only when the corresponding payment add-on is active.
	// However, we need to be aware of all possible payment fields, even if they are not currently available.
	return [
		'payment-single',
		'payment-multiple',
		'payment-checkbox',
		'payment-select',
		'payment-total',
		'payment-coupon',
		'credit-card', // Legacy Credit Card field.
		'authorize_net',
		'paypal-commerce',
		'square',
		'stripe-credit-card',
	];
}

/**
 * Validate field ID for the repeater field.
 *
 * @since 1.8.9
 *
 * @param mixed $field_id Field ID.
 *
 * @return int|string
 */
function wpforms_validate_field_id( $field_id ) {

	return (
		wpforms_is_repeater_child_field( $field_id ) ?
			preg_replace( '/[^0-9_]/', '', $field_id ) :
			absint( $field_id )
	);
}

/**
 * Check if field ID is a repeater field.
 *
 * @since 1.8.9
 *
 * @param int|string|array $field Field.
 *
 * @return bool
 */
function wpforms_is_repeater_child_field( $field ): bool {

	$field_id = (string) ( $field['id'] ?? $field );

	$pattern = '/^(\d+_\d+)(_\d+)*$/';

	return preg_match( $pattern, $field_id ) === 1;
}

/**
 * Get repeater field IDs.
 *
 * @since 1.8.9
 *
 * @param int|string|array $field Field ID.
 *
 * @return int[]
 */
function wpforms_get_repeater_field_ids( $field ): array {

	$field_id     = (string) ( is_array( $field ) ? $field['id'] : $field );
	$field_id_arr = explode( '_', $field_id );
	$original_id  = (int) $field_id_arr[0];
	$index_id     = (int) ( $field_id_arr[1] ?? 0 );

	return compact( 'original_id', 'index_id' );
}

/**
 * Get the correct value for field with raw value available.
 *
 * @since 1.8.9
 *
 * @param array $field     Entry field.
 * @param array $form_data Form data and settings.
 *
 * @return string
 */
function wpforms_get_choices_value( array $field, array $form_data ): string {

	$show_values = ! empty( $form_data['fields'][ $field['id'] ]['show_values'] );
	$is_dynamic  = ! empty( $field['dynamic'] );
	$value       = $field['value'] ?? '';

	if ( $show_values && ! $is_dynamic && ! wpforms_is_empty_string( $field['value_raw'] ?? '' ) ) {
		$value = $field['value_raw'];
	}

	if ( $is_dynamic ) {
		$value = $field['value_raw'] ?? $value;
	}

	return (string) $value;
}

/**
 * Check whether the field type is in the list of types that support the Show Values option.
 *
 * @since 1.10.0
 *
 * @param array $field Field data.
 *
 * @return bool True if the field type supports Show Values, false otherwise.
 */
function wpforms_is_support_show_values( array $field ): bool {

	static $supported_types = [ 'select', 'radio', 'checkbox' ];

	return in_array( $field['type'] ?? '', $supported_types, true );
}

/**
 * Determine if the field was repeated.
 *
 * @since 1.8.9
 *
 * @param int   $field_id Field ID.
 * @param array $fields   List of fields.
 *
 * @return bool
 */
function wpforms_is_repeated_field( int $field_id, array $fields ): bool {

	$prefix = $field_id . '_';

	foreach ( $fields as $key => $field ) {
		if ( strpos( $key, $prefix ) === 0 ) {
			return true;
		}
	}

	return false;
}

/**
 * Get field types where user can select more than one item.
 *
 * Note: this list does not include the File Upload field, even though it is a multi-field.
 *
 * @since 1.9.0
 *
 * @return array
 */
function wpforms_get_multi_fields(): array {

	return [
		'checkbox',
		'select',
		'payment-checkbox',
	];
}

/**
 * Get column width.
 *
 * @since 1.9.3
 *
 * @param array $column Column data.
 *
 * @return float
 */
function wpforms_get_column_width( array $column ): float {

	$preset_width = ! empty( $column['width_preset'] ) ? (int) $column['width_preset'] : 50;

	if ( $preset_width === 33 ) {
		$preset_width = 33.33333;
	} elseif ( $preset_width === 67 ) {
		$preset_width = 66.66666;
	}

	if ( ! empty( $column['width_custom'] ) ) {
		$preset_width = $column['width_custom'];
	}

	return (float) $preset_width;
}

/**
 * Parse a field ID into its components.
 *
 * @since 1.9.6
 *
 * @param string|int $field_id Field ID to parse.
 *
 * @return array Parsed field components.
 */
function wpforms_parse_field_id( $field_id ): array {

	$field_parts = (array) explode( '.', (string) $field_id );
	$field_id    = (int) $field_parts[0];
	$field_key   = isset( $field_parts[1] ) && $field_parts[1] !== 'full' ? $field_parts[1] : 'value';

	return [
		'id'  => $field_id,
		'key' => $field_key,
	];
}

/**
 * Get icon SVG by its name, style and size.
 *
 * @since 1.10.0
 *
 * @param string $icon  Icon name.
 * @param string $style Icon style.
 * @param int    $size  Icon font size in pixels.
 */
function wpforms_get_icon_svg( string $icon, string $style, int $size ): string {

	// Sanitize inputs.
	$icon  = sanitize_key( $icon );
	$style = sanitize_key( $style );
	$size  = absint( $size );

	if ( $size <= 0 ) {
		$size = 32;
	}

	$upload_dir = wpforms_upload_dir();

	$cache_base_path = $upload_dir['path'] . '/icon-choices';
	$filename        = wp_normalize_path( (string) realpath( "$cache_base_path/svgs/$style/$icon.svg" ) );
	$allowed_dir     = wp_normalize_path( (string) realpath( $cache_base_path . '/svgs' ) );

	// Verify the file is within the allowed directory.
	if ( strpos( $filename, $allowed_dir ) !== 0 ) {
		return '';
	}

	if ( ! is_file( $filename ) || ! is_readable( $filename ) ) {
		return '';
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$svg = (string) file_get_contents( $filename );

	if ( strpos( $svg, '<svg' ) === false ) {
		return '';
	}

	$height = $size;
	$width  = $height * 1.25; // Icon width is equal or 25% larger/smaller than height. We force the largest value for all icons.

	return str_replace( 'viewBox=', 'width="' . $width . '" height="' . $height . '" viewBox=', $svg );
}
