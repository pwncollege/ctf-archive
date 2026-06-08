<?php
/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom post type registration.
 */
class WCPCSU_Custom_Post {
	
	public function __construct () {
		add_action( 'init', array( $this,'register' ) );
		add_filter( 'manage_' . WCPCSU_CUSTOM_POST_TYPE . '_posts_columns', array( $this,'register_columns' ) );
		add_action( 'manage_' . WCPCSU_CUSTOM_POST_TYPE . '_posts_custom_column', array( $this, 'add_column_content' ), 10, 2 );
	}

	public function register() {
		$labels = array(
			'name'               => _x( 'Woocommerce Product Ultimate', 'woocommerce-product-carousel-slider-and-grid-ultimate' ),
			'singular_name'      => _x( 'Woocommerce Product Ultimate', 'woocommerce-product-carousel-slider-and-grid-ultimate' ),
			'menu_name'          => _x( 'Woocommerce Product Ultimate', 'woocommerce-product-carousel-slider-and-grid-ultimate' ),
			'name_admin_bar'     => _x( 'Woocommerce Product Ultimate', 'woocommerce-product-carousel-slider-and-grid-ultimate' ),
			'add_new'            => _x( 'Add New', 'woocommerce-product-carousel-slider-and-grid-ultimate' ),
			'add_new_item'       => __( 'Add New', 'woocommerce-product-carousel-slider-and-grid-ultimate' ),
			'new_item'           => __( 'Add New', 'woocommerce-product-carousel-slider-and-grid-ultimate' ),
			'edit_item'          => __( 'Edit Woocommerce Product Grid Carousel Slider Ultimate', 'woocommerce-product-carousel-slider-and-grid-ultimate' ),
			'view_item'          => __( 'View Woocommerce Product Grid Carousel Slider Ultimate', 'woocommerce-product-carousel-slider-and-grid-ultimate' ),
			'all_items'          => __( 'All Woocommerce Product Ultimate', 'woocommerce-product-carousel-slider-and-grid-ultimate' ),
			'search_items'       => __( 'Search Woocommerce Product Grid Carousel Slider Ultimate', 'woocommerce-product-carousel-slider-and-grid-ultimate' ),
			'parent_item_colon'  => __( 'Parent Woocommerce Product Grid Carousel Slider Ultimate:', 'woocommerce-product-carousel-slider-and-grid-ultimate' ),
			'not_found'          => __( 'No Woocommerce Product Grid Carousel Slider Ultimate found.', 'woocommerce-product-carousel-slider-and-grid-ultimate' ),
			'not_found_in_trash' => __( 'No Woocommerce Product Grid Carousel Slider Ultimate found in Trash.', 'woocommerce-product-carousel-slider-and-grid-ultimate' )
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'woocommerce-product-carousel-slider-and-grid-ultimate' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => WCPCSU_CUSTOM_POST_TYPE ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title' ),
			'menu_icon'          => 'dashicons-images-alt'
		);

		register_post_type( WCPCSU_CUSTOM_POST_TYPE, $args );

		flush_rewrite_rules();
	}

	public function register_columns( $columns ) {
		$columns = array();
		$columns['cb']                     = '<input type="checkbox" />';
		$columns['title']                  = esc_html__( 'All Titles', 'woocommerce-product-carousel-slider-and-grid-ultimate' );
		$columns['wpcsp_shortcode_column'] = esc_html__( 'All Shortcodes', 'woocommerce-product-carousel-slider-and-grid-ultimate' );
		$columns['date']                   = esc_html__( 'Created at', 'woocommerce-product-carousel-slider-and-grid-ultimate' );
		
		return $columns;
	}

	public function add_column_content( $column_name, $post_id ) {
		if ( $column_name === 'wpcsp_shortcode_column' ) {
			printf(
				'<input type="text" style="text-align: center; font-family: monospace;" onClick="this.select();" value="%s">',
				esc_attr( '[wcpcsu id="' . intval( $post_id ) . '"]' )
			);
		}
	}
}
