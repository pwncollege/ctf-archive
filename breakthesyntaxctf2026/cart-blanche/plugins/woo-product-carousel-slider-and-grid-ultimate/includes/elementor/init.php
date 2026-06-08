<?php
if ( ! function_exists('register_woo_ultimate_widget') ) {
	function register_woo_ultimate_widget( $widgets_manager ) {

		require_once WCPCSU_INC_DIR . 'elementor/widget.php';

		$widgets_manager->register( new Elementor_Woo_Ultimate_Widget() );

	}
}
add_action( 'elementor/widgets/register', 'register_woo_ultimate_widget' );