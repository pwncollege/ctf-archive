<?php 

if( ! defined( 'ABSPATH' ) ) : exit(); endif; // No direct access allowed

function register_block() {

    wp_enqueue_script( 
        'wcpcsup-gutenberg-js', 
        WCPCSU_URL . 'build/index.js', 
        [
        'wp-block-editor', 
        'wp-blocks', 
        'wp-components', 
        'wp-element', 
        'wp-i18n', 
        'wp-server-side-render'
        ] 
    );

    wp_enqueue_style( 'wcpcsup-main', WCPCSU_URL . 'assets/css/style.css' );
    wp_enqueue_style( 'wcpcsup-swmodal', WCPCSU_URL . 'assets/css/swmodal.css' );
    wp_enqueue_style( 'wcpcsup-swiper', WCPCSU_URL . 'assets/css/swiper-bundle.min.css' );

    wp_enqueue_script( 'wcpcsup-main-js', WCPCSU_URL . 'assets/js/main.js');
    wp_enqueue_script( 'wcpcsup-swmodal-js', WCPCSU_URL . 'assets/js/swmodal.js' );
    wp_enqueue_script( 'wcpcsup-swiper-js', WCPCSU_URL . 'assets/js/swiper-bundle.min.js' );

    wp_localize_script('wcpcsup-swmodal-js','wcpcsu_quick_view',array(
        'ajax_url'           => admin_url( 'admin-ajax.php' ),

    ));
    wp_localize_script('wcpcsup-main-js','main_js',array(
        'handbag_svg'           => WCPCSU_URL .'assets/icons/handbag.svg',

    ));

    $attributes = get_attributes_from_metadata( trailingslashit( __DIR__ ) );

    register_block_type(
        'wcpcsup/block',
        [
            'style'           => 'wcpcsu-main',
            'editor_script'   => 'wcpcsup-gutenberg-js',
            'api_version'     => 2,
            'attributes'      => $attributes,
            'render_callback' => 'render_callback'
        ]
    );
}

function render_callback( $attributes ) {
    $attributes['h_title_show']                 = ! empty( $attributes['h_title_show'] ) ? 'yes' : 'no';
    $attributes['display_title']                = ! empty( $attributes['display_title'] ) ? 'yes' : 'no';
    $attributes['exclude_stock_out']            = ! empty( $attributes['exclude_stock_out'] ) ? 'yes' : 'no';
    $attributes['display_sale_ribbon']          = ! empty( $attributes['display_sale_ribbon'] ) ? 'yes' : 'no';
    $attributes['display_featured_ribbon']      = ! empty( $attributes['display_featured_ribbon'] ) ? 'yes' : 'no';
    $attributes['display_sold_out_ribbon']      = ! empty( $attributes['display_sold_out_ribbon'] ) ? 'yes' : 'no';
    $attributes['display_discount_ribbon']      = ! empty( $attributes['display_discount_ribbon'] ) ? 'yes' : 'no';
    $attributes['display_price']                = ! empty( $attributes['display_price'] ) ? 'yes' : 'no';
    $attributes['display_ratings']              = ! empty( $attributes['display_ratings'] ) ? 'yes' : 'no';
    $attributes['display_cart']                 = ! empty( $attributes['display_cart'] ) ? 'yes' : 'no';
    $attributes['A_play']                       = ! empty( $attributes['A_play'] ) ? 'yes' : 'no';
    $attributes['repeat_product']               = ! empty( $attributes['repeat_product'] ) ? 'yes' : 'no';
    $attributes['stop_hover']                   = ! empty( $attributes['stop_hover'] ) ? 'yes' : 'no';
    $attributes['marquee']                      = ! empty( $attributes['marquee'] ) ? 'yes' : 'no';
    $attributes['nav_show']                     = ! empty( $attributes['nav_show'] ) ? 'yes' : 'no';
    $attributes['carousel_pagination']          = ! empty( $attributes['carousel_pagination'] ) ? 'yes' : 'no';
    $attributes['grid_pagination']              = ! empty( $attributes['grid_pagination'] ) ? 'yes' : 'no';
    $attributes['img_hover_effect']             = ! empty( $attributes['img_hover_effect'] ) ? 'yes' : 'no';

    return run_shortcode( 'wcpcsu', $attributes );
    
}

function get_attributes_from_metadata( $file_or_folder ) {
	$filename      = 'attributes.json';
	$metadata_file = ( substr( $file_or_folder, -strlen( $filename ) ) !== $filename ) ?
		trailingslashit( $file_or_folder ) . $filename :
		$file_or_folder;

	if ( ! file_exists( $metadata_file ) ) {
		return [];
	}

	$metadata = json_decode( file_get_contents( $metadata_file ), true );

	if ( empty( $metadata ) || ! is_array( $metadata )  ) {
		return [];
	}

	return $metadata;
}

function run_shortcode( $shortcode, $atts = [] ) {
    $html = '';

    foreach ( $atts as $key => $value ) {
        $html .= sprintf( ' %s="%s"', $key, esc_html( $value ) );
    }

    $html = sprintf( '[%s%s]', $shortcode, $html );

    return do_shortcode( $html );
}

add_action( 'init', 'register_block' );

function add_rest_method( $endpoints ) {
    if ( is_wp_version_compatible( '5.5' ) ) {
        return $endpoints;
    }

    foreach ( $endpoints as $route => $handler ) {
        if ( isset( $endpoints[ $route ][0] ) ) {
            $endpoints[ $route ][0]['methods'] = [ WP_REST_Server::READABLE, WP_REST_Server::CREATABLE ];
        }
    }

    return $endpoints;
}
//add_filter( 'rest_endpoints', 'add_rest_method');
?>