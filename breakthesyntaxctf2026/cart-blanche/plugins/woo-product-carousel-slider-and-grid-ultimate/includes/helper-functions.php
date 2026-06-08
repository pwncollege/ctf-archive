<?php
/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wcpcsu_pagination' ) ) {
	/**
	 * Prints pagination for custom post
	 * @param object|WP_Query $custom_post_query
	 * @param int $paged
	 *
	 * @return string
	 */
	function wcpcsu_pagination( $custom_post_query, $paged = 1 ) {
		$navigation = '';
		$largeNumber = 999999999; // we need a large number here
		$links = paginate_links( array(
			'base'      => str_replace( $largeNumber, '%#%', esc_url( get_pagenum_link( $largeNumber ) ) ),
			'format'    => '?paged=%#%',
			'current'   => max( 1, $paged ),
			'total'     => $custom_post_query->max_num_pages,
			'prev_text' => apply_filters( 'wcpcsu_pagination_prev_text', '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="6" height="100%" preserveAspectRatio="xMidYMid meet" viewBox="0 0 576 1024" style="-ms-transform: rotate(360deg); -webkit-transform: rotate(360deg); transform: rotate(360deg);"><path d="M528 0q14 0 24 10q4 3 6 7t3 8.5t1 9t-1 8.5t-3 8t-6 8L96 515l450 450q6 6 8.5 15t0 18t-8.5 15q-10 10-24.5 10t-24.5-10L23 539q-10-10-10-24t10-24L504 10q10-10 24-10z"/><rect x="0" y="0" width="576" height="1024" fill="rgba(0, 0, 0, 0)" /></svg>'),
			'next_text' => apply_filters( 'wcpcsu_pagination_next_text', '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="6" height="100%" preserveAspectRatio="xMidYMid meet" viewBox="0 0 576 1024" style="-ms-transform: rotate(360deg); -webkit-transform: rotate(360deg); transform: rotate(360deg);"><path d="M48 1023q-14 0-24.5-10T13 989t10-24l457-457L30 59q-6-7-8.5-16t0-17.5T30 10Q40 0 54.5 0T79 10l473 474q5 5 7.5 11.5t2.5 13t-2.5 12.5t-7.5 11L72 1013q-5 5-11.5 7.5T48 1023z"/><rect x="0" y="0" width="576" height="1024" fill="rgba(0, 0, 0, 0)" /></svg>'),
		) );

		if ( $links ) {
			$navigation = _navigation_markup( $links, 'pagination', ' ' );
		}

		return apply_filters('wcpcsu_pagination', $navigation, $links, $custom_post_query, $paged);
	}
}

if ( ! function_exists( 'wcpcsu_get_paged_num' ) ) {
	/**
	 * Get current page number for the pagination.
	 *
	 * @since    1.0.0
	 *
	 * @return    int    $paged    The current page number for the pagination.
	 */
	function wcpcsu_get_paged_num() {
		global $paged;

		if ( get_query_var( 'paged' ) ) {
			$paged = get_query_var( 'paged' );
		} elseif ( get_query_var( 'page' ) ) {
			$paged = get_query_var( 'page' );
		} else {
			$paged = 1;
		}

		return absint( $paged );
	}
}

if ( ! function_exists('wpcsu_load_dependencies') ) :
	function wpcsu_load_dependencies( $files = 'all', $directory = WCPCSU_INC_DIR, $ext = '.php' ) {
		if ( ! file_exists( $directory ) ) {
			return; // vail if the directory does not exist
		}

		switch( $files ) {
			case is_array( $files ) && 'all' !== strtolower( $files[0] ):
				// include one or more file looping through the $files array
				load_some_file( $files, $directory );
				break;
			case ! is_array( $files ) && 'all' !== $files:
				//load a single file here
				( file_exists( $directory . $files . $ext ) ) ? require_once $directory . $files . $ext : null;
				break;
			case 'all' == $files || 'all' == strtolower( $files[0] ):
				// load all php file here
				load_all_files( $directory );
				break;
		}

		return false;

	}
endif;

if ( ! function_exists('load_all_files') ):
	function load_all_files( $dir = '', $ext = '.php' )
	{
		if ( ! file_exists( $dir ) ) return;
		foreach ( scandir( $dir ) as $file ) {
			// require once all the files with the given ext. eg. .php
			if ( preg_match( "/{$ext}$/i", $file ) ) {
				require_once( $dir . $file );
			}
		}
	}
endif;

if ( ! function_exists('load_some_file') ):

	/**
	 * It loads one or more files but not all files that has the $ext from the $dir
	 * @param string|array $files the array of files that should be loaded
	 * @param string $dir Name of the directory
	 * @param string $ext Name of the extension of the files to be loaded
	 */
	function load_some_file( $files = array(), $dir = '', $ext = '.php' )
	{
		if ( ! file_exists( $dir ) ) return; // vail if directory does not exist

		if ( is_array( $files ) ) {  // if the given files is an array then
			$files_to_loads = array_map( function ( $i ) use ( $ext ) {
				return $i . $ext;
			}, $files );// add '.php' to the end of all files
			$found_files = scandir( $dir ); // get the list of all the files in the given $dir
			foreach ( $files_to_loads as $file_to_load ) {
				in_array( $file_to_load, $found_files ) ? require_once $dir . $file_to_load : null;
			}
		}

	}
endif;

if ( ! function_exists('wpcsu_ribbon_badge') ) :
    function wpcsu_ribbon_badge( $ribbon_args, $discount )
    {
        global $product;
        $value = is_array( $ribbon_args ) ? $ribbon_args : array();
        extract( $value );


        if( ( 'yes' == $display_sale_ribbon && 'top_left' == $sale_ribbon_position && $product->is_on_sale() ) || ( 'yes' == $display_featured_ribbon && 'top_left' == $featured_ribbon_position && $product->is_featured() ) || ( 'yes' == $display_sold_out_ribbon && 'top_left' == $sold_out_ribbon_position && ! $product->is_in_stock() ) || ( 'yes' == $display_discount_ribbon && 'top_left' == $discount_ribbon_position && $product->get_sale_price() ) ) { ?>
            <div class="wpcu-product__cover-content wpcu-product__cover-content--top-left">

                <?php if( 'yes' == $display_sale_ribbon && 'top_left' == $sale_ribbon_position && $product->is_on_sale() ) { ?>
                <span class="wpcu-badge wpcu-sale wpcu-badge--primary wpcu-badge--text-lg wpcu-badge--rounded-circle"><?php echo ! empty( $sale_ribbon_text ) ? $sale_ribbon_text : ''; ?></span>
                <?php } ?>

                <?php if( 'yes' == $display_featured_ribbon && 'top_left' == $featured_ribbon_position && $product->is_featured() ) { ?>
                <span class="wpcu-badge wpcu-feature wpcu-badge--primary wpcu-badge--text-lg wpcu-badge--rounded-circle"><?php echo ! empty( $feature_ribbon_text ) ? $feature_ribbon_text : ''; ?></span>
                <?php } ?>

                <?php if( 'yes' == $display_sold_out_ribbon && 'top_left' == $sold_out_ribbon_position && ! $product->is_in_stock() ) { ?>
                <span class="wpcu-badge wpcu-sold_out wpcu-badge--primary wpcu-badge--text-lg wpcu-badge--rounded-circle"><?php echo ! empty( $sold_out_ribbon_text ) ? $sold_out_ribbon_text : ''; ?></span>
                <?php } ?>

                <?php if( 'yes' == $display_discount_ribbon && 'top_left' == $discount_ribbon_position && $product->get_sale_price() ) { ?>
                <span class="wpcu-badge wpcu-discount wpcu-badge--primary wpcu-badge--text-lg wpcu-badge--rounded-circle"><?php echo ! empty( $discount ) ? '-'.$discount : ''; ?></span>
                <?php } ?>

            </div>
            <?php } ?>

            <?php if( ( 'yes' == $display_sale_ribbon && 'top_right' == $sale_ribbon_position && $product->is_on_sale() ) || ( 'yes' == $display_featured_ribbon && 'top_right' == $featured_ribbon_position && $product->is_featured() ) || ( 'yes' == $display_sold_out_ribbon && 'top_right' == $sold_out_ribbon_position && ! $product->is_in_stock() ) || ( 'yes' == $display_discount_ribbon && 'top_right' == $discount_ribbon_position ) && $product->get_sale_price() ) { ?>
            <div class="wpcu-product__cover-content wpcu-product__cover-content--top-right">

                <?php if( 'yes' == $display_sale_ribbon && 'top_right' == $sale_ribbon_position && $product->is_on_sale() ) { ?>
                <span class="wpcu-badge wpcu-sale wpcu-badge--primary wpcu-badge--text-lg wpcu-badge--rounded-circle"><?php echo ! empty( $sale_ribbon_text ) ? $sale_ribbon_text : ''; ?></span>
                <?php } ?>

                <?php if( 'yes' == $display_featured_ribbon && 'top_right' == $featured_ribbon_position && $product->is_featured() ) { ?>
                <span class="wpcu-badge wpcu-feature wpcu-badge--primary wpcu-badge--text-lg wpcu-badge--rounded-circle"><?php echo ! empty( $feature_ribbon_text ) ? $feature_ribbon_text : ''; ?></span>
                <?php } ?>

                <?php if( 'yes' == $display_sold_out_ribbon && 'top_right' == $sold_out_ribbon_position && ! $product->is_in_stock() ) { ?>
                <span class="wpcu-badge wpcu-sold_out wpcu-badge--primary wpcu-badge--text-lg wpcu-badge--rounded-circle"><?php echo ! empty( $sold_out_ribbon_text ) ? $sold_out_ribbon_text : ''; ?></span>
                <?php } ?>

                <?php if( 'yes' == $display_discount_ribbon && 'top_right' == $discount_ribbon_position && $product->get_sale_price() ) { ?>
                <span class="wpcu-badge wpcu-discount wpcu-badge--primary wpcu-badge--text-lg wpcu-badge--rounded-circle"><?php echo ! empty( $discount ) ? '-'.$discount : ''; ?></span>
                <?php } ?>

            </div>
            <?php } ?>

            <?php if( ( 'yes' == $display_sale_ribbon && 'bottom_left' == $sale_ribbon_position && $product->is_on_sale() ) || ( 'yes' == $display_featured_ribbon && 'bottom_left' == $featured_ribbon_position && $product->is_featured() ) || ( 'yes' == $display_sold_out_ribbon && 'bottom_left' == $sold_out_ribbon_position && ! $product->is_in_stock() ) || ( 'yes' == $display_discount_ribbon && 'bottom_left' == $discount_ribbon_position ) && $product->get_sale_price() ) { ?>
            <div class="wpcu-product__cover-content wpcu-product__cover-content--bottom-left">

                <?php if( 'yes' == $display_sale_ribbon && 'bottom_left' == $sale_ribbon_position && $product->is_on_sale() ) { ?>
                <span class="wpcu-badge wpcu-sale wpcu-badge--primary wpcu-badge--text-lg wpcu-badge--rounded-circle"><?php echo ! empty( $sale_ribbon_text ) ? $sale_ribbon_text : ''; ?></span>
                <?php } ?>

                <?php if( 'yes' == $display_featured_ribbon && 'bottom_left' == $featured_ribbon_position && $product->is_featured() ) { ?>
                <span class="wpcu-badge wpcu-feature wpcu-badge--primary wpcu-badge--text-lg wpcu-badge--rounded-circle"><?php echo ! empty( $feature_ribbon_text ) ? $feature_ribbon_text : ''; ?></span>
                <?php } ?>

                <?php if( 'yes' == $display_sold_out_ribbon && 'bottom_left' == $sold_out_ribbon_position && ! $product->is_in_stock() ) { ?>
                <span class="wpcu-badge wpcu-sold_out wpcu-badge--primary wpcu-badge--text-lg wpcu-badge--rounded-circle"><?php echo ! empty( $sold_out_ribbon_text ) ? $sold_out_ribbon_text : ''; ?></span>
                <?php } ?>

                <?php if( 'yes' == $display_discount_ribbon && 'bottom_left' == $discount_ribbon_position && $product->get_sale_price() ) { ?>
                <span class="wpcu-badge wpcu-discount wpcu-badge--primary wpcu-badge--text-lg wpcu-badge--rounded-circle"><?php echo ! empty( $discount ) ? '-'.$discount : ''; ?></span>
                <?php } ?>

            </div>
            <?php } ?>

            <?php if( ( 'yes' == $display_sale_ribbon && 'bottom_right' == $sale_ribbon_position && $product->is_on_sale() ) || ( 'yes' == $display_featured_ribbon && 'bottom_right' == $featured_ribbon_position && $product->is_featured() ) || ( 'yes' == $display_sold_out_ribbon && 'bottom_right' == $sold_out_ribbon_position && ! $product->is_in_stock() ) || ( 'yes' == $display_discount_ribbon && 'bottom_right' == $discount_ribbon_position ) && $product->get_sale_price()) { ?>
            <div class="wpcu-product__cover-content wpcu-product__cover-content--bottom-right">

                <?php if( 'yes' == $display_sale_ribbon && 'bottom_right' == $sale_ribbon_position && $product->is_on_sale() ) { ?>
                <span class="wpcu-badge wpcu-sale wpcu-badge--primary wpcu-badge--text-lg wpcu-badge--rounded-circle"><?php echo ! empty( $sale_ribbon_text ) ? $sale_ribbon_text : ''; ?></span>
                <?php } ?>

                <?php if( 'yes' == $display_featured_ribbon && 'bottom_right' == $featured_ribbon_position && $product->is_featured() ) { ?>
                <span class="wpcu-badge wpcu-feature wpcu-badge--primary wpcu-badge--text-lg wpcu-badge--rounded-circle"><?php echo ! empty( $feature_ribbon_text ) ? $feature_ribbon_text : ''; ?></span>
                <?php } ?>

                <?php if( 'yes' == $display_sold_out_ribbon && 'bottom_right' == $sold_out_ribbon_position && ! $product->is_in_stock() ) { ?>
                <span class="wpcu-badge wpcu-sold_out wpcu-badge--primary wpcu-badge--text-lg wpcu-badge--rounded-circle"><?php echo ! empty( $sold_out_ribbon_text ) ? $sold_out_ribbon_text : ''; ?></span>
                <?php } ?>

                <?php if( 'yes' == $display_discount_ribbon && 'bottom_right' == $discount_ribbon_position && $product->get_sale_price() ) { ?>
                <span class="wpcu-badge wpcu-discount wpcu-badge--primary wpcu-badge--text-lg wpcu-badge--rounded-circle"><?php echo ! empty( $discount ) ? '-'.$discount : ''; ?></span>
                <?php } ?>

            </div>
            <?php }


    }
endif;

if ( ! function_exists( 'wcpcsu_sanitize_array' ) ) {
	/**
	 * It sanitize a multi-dimensional array
	 * @param array &$array The array of the data to sanitize
	 * @return mixed
	 */
	function wcpcsu_sanitize_array( &$array ) {
		foreach ( $array as &$value ) {
			if ( ! is_array( $value ) ) {
				// sanitize if value is not an array
				$value = sanitize_text_field( $value );
			} else {
				// go inside this function again
				wcpcsu_sanitize_array( $value );
			}
		}
		return $array;
	}
}

/**
 * Checks if a string is a valid JSON-encoded string.
 *
 * @param string $data The string to be checked for JSON encoding.
 *
 * @return bool Returns true if the string is a valid JSON-encoded string, false otherwise.
 */
if ( ! function_exists( 'is_json_encoded' ) ) {
	function is_json_encoded( $data ) {
		json_decode( $data );
		return (json_last_error() == JSON_ERROR_NONE);
	}
}
