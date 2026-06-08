<?php
/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcode class.
 */
class WCPCSU_Shortcode {

	const SHORTCODE_TAG = 'wcpcsu';

	public function __construct () {
		add_shortcode( self::SHORTCODE_TAG, array( $this,'register' ) );
		add_action( 'wp_ajax_ajax_quick_view', array( $this, 'ajax_quick_view' ) );
		add_action( 'wp_ajax_nopriv_ajax_quick_view', array( $this, 'ajax_quick_view') );
	}

	public function register( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'id' 						=>  0,
			'layout'                    => '',
            'theme'                     => '',
            'total_products'            => '',
            'h_title_show'              => '',
            'header_title'              => '',
            'header_position'           => '',
            'display_title'             => '',
            'products_type'             => '',
            'display_sale_ribbon'       => '',
            'sale_ribbon_text'          => '',
            'sale_ribbon_position'      => '',
            'display_featured_ribbon'   => '',
            'feature_ribbon_text'       => '',
            'featured_ribbon_position'  => '',
            'display_sold_out_ribbon'   => '',
            'sold_out_ribbon_text'      => '',
            'sold_out_ribbon_position'  => '',
            'display_discount_ribbon'   => '',
            'discount_ribbon_position'  => '',
            'display_price'             => '',
            'display_ratings'           => '',
            'display_cart'              => '',
            'img_crop'                  => '',
            'crop_image_width'          => '',
            'crop_image_height'         => '',
            'auto_play'                 => '',
            'repeat_product'            => '',
            'stop_hover'                => '',
            'c_desktop'                 => '',
            'c_desktop_small'           => '',
            'c_tablet'                  => '',
            'c_mobile'                  => '',
            'slide_speed'               => '',
            'slide_time'                => '',
            'nav_show'                  => '',
            'nav_position'              => '',
            'nav_arrow_color'           => '',
            'nav_back_color'            => '',
            'nav_border_color'          => '',
            'nav_arrow_hover_color'     => '',
            'nav_back_hover_color'      => '',
            'nav_border_hover'          => '',
            'carousel_pagination'       => '',
            'dots_color'                => '',
            'dots_active_color'         => '',
            'g_column'                  => '',
            'g_tablet'                  => '',
            'g_mobile'                  => '',
            'grid_pagination'           => '',
            'pagi_color'                => '',
            'pagi_border_color'         => '',
            'pagi_back_color'           => '',
            'pagi_hover_color'          => '',
            'pagi_hover_border_color'   => '',
            'pagi_hover_back_color'     => '',
            'pagi_active_border_color'  => '',
            'pagi_active_back_color'    => '',
            'header_font_size'          => '',
            'header_font_color'         => '',
            'title_font_size'           => '',
            'title_font_color'          => '',
            'title_hover_font_color'    => '',
            'price_font_size'           => '',
            'price_font_color'          => '',
            'ratings_size'              => '',
            'ratings_color'             => '',
            'cart_font_color'           => '',
            'cart_bg_color'             => '',
            'cart_button_hover_color'   => '',
            'cart_button_hover_font_color'    => '',
            'ribbon_bg_color'          	=> '',
		), $atts, self::SHORTCODE_TAG );
		
		$post_id = absint( $atts['id'] );
		

		$this->wcpcsu_style_files();

		// get the array of data from the post meta
		$enc_data   = get_post_meta( $post_id, 'wcpscu', true );
		$data_array = Woocmmerce_Product_carousel_slider_ultimate::json_decoded( $enc_data );
		
		$value      = is_array( $data_array ) ? $data_array : array();

		extract( $value );

		$rand_id                 = rand();
		$total_products          = ! empty( $total_products ) ? intval( $total_products ) : 6;
		$layout                  = ! empty( $layout ) ? $layout : 'carousel';
		$products_type           = ! empty( $products_type ) ? $products_type : 'latest';
		$img_crop                = ! empty( $img_crop ) ? $img_crop : 'yes';
		$crop_image_width        = ! empty( $crop_image_width ) ? intval( $crop_image_width ) : 300;
		$crop_image_height       = ! empty( $crop_image_height ) ? intval( $crop_image_height ) : 300;
		$display_price           = ! empty( $display_price ) ? $display_price : 'yes';

		$display_cart            = ! empty( $display_cart ) ? $display_cart : 'yes';
		$ribbon                  = ! empty( $ribbon ) ? $ribbon : 'discount';
		$header_position         = ! empty( $header_position ) ? $header_position : 'middle';
		$h_title_show            = ! empty( $h_title_show ) ? $h_title_show : 'no';
		$header_font_size        = ! empty( $header_font_size ) ? $header_font_size : '24px';
		$header_font_color       = ! empty( $header_font_color ) ? $header_font_color : '#303030';
		$display_full_title      = ! empty( $display_full_title ) ? $display_full_title : 'no';
		$g_column                = ! empty( $g_column ) ? intval( $g_column ) : 3;
		$g_tablet                = ! empty( $g_tablet ) ? intval( $g_tablet ) : 2;
		$g_mobile                = ! empty( $g_mobile ) ? intval( $g_mobile ) : 1;
		$grid_pagination         = ! empty( $grid_pagination ) ? $grid_pagination : 'no';
		$slide_time              = ! empty( $slide_time )  ? $slide_time : '2000' ;

		$display_title           = ! empty( $display_title ) ? $display_title : 'yes';
		$display_cart            = ! empty( $display_cart ) ? $display_cart : 'yes';
		$display_price           = ! empty( $display_price ) ? $display_price : 'yes';
		$display_ratings         = ! empty( $display_ratings ) ? $display_ratings : 'yes';
		$quick_view              = ! empty( $quick_view ) ? $quick_view : 'yes';

		$theme                          = ! empty( $theme ) ? $theme : 'theme_1';
		$title_font_size                = ! empty( $title_font_size[ $theme ] ) ? $title_font_size[ $theme ] : '16';
		$title_font_color               = ! empty( $title_font_color[ $theme ] ) ? $title_font_color[ $theme ] : '#363940';
		$title_hover_font_color         = ! empty( $title_hover_font_color[ $theme ] ) ? $title_hover_font_color[ $theme ] : '#ff5500';
		$price_font_size                = ! empty( $price_font_size[ $theme ] ) ? $price_font_size[ $theme ] : '14';
		$price_font_color               = ! empty( $price_font_color[ $theme ] ) ? $price_font_color[ $theme ] : '#ff5500';
		$ratings_size                   = ! empty( $ratings_size[ $theme ] ) ? $ratings_size[ $theme ] : '16';
		$ratings_color                  = ! empty( $ratings_color[ $theme ] ) ? $ratings_color[ $theme ] : '#FEB507';
		$cart_font_color                = ! empty( $cart_font_color[ $theme ] ) ? $cart_font_color[ $theme ] : '#ffffff';
		$cart_bg_color                  = ! empty( $cart_bg_color[ $theme ] ) ? $cart_bg_color[ $theme ] : '#ff5500';
		$cart_button_hover_color        = ! empty( $cart_button_hover_color[ $theme ] ) ? $cart_button_hover_color[ $theme ] : '#9A9A9A';
		$cart_button_hover_font_color   = ! empty( $cart_button_hover_font_color[ $theme ] ) ? $cart_button_hover_font_color[ $theme ] : '#ffffff';
		$ribbon_bg_color                = ! empty( $ribbon_bg_color[ $theme ] ) ? $ribbon_bg_color[ $theme ] : '#A4C741';
		$quick_view_icon_color          = ! empty( $quick_view_icon_color[ $theme ] ) ? $quick_view_icon_color[ $theme ] : '#ffffff';
		$quick_view_icon_back_color     = ! empty( $quick_view_icon_back_color[ $theme ] ) ? $quick_view_icon_back_color[ $theme ] : '#ff5500';

		$paged                          =  wcpcsu_get_paged_num();
		$paged                          = ! empty( $paged ) ? $paged : '';
		$loop                           = $this->parse_query( $data_array, $atts );

		// carousel settings
		$slide_speed                = ! empty( $slide_speed ) ? $slide_speed : '2000';
		$A_play                     = ! empty( $A_play ) ? $A_play : 'yes';
		$repeat_product             = ! empty( $repeat_product ) ? $repeat_product : 'yes';
		$stop_hover                 = ! empty( $stop_hover ) ? $stop_hover : true;
		

		// carousel navigation settings
		$nav_show                   = ! empty( $nav_show ) ? $nav_show : 'yes';
		$nav_position               = ! empty( $nav_position ) ? $nav_position : 'middle';
		$nav_arrow_color            = ! empty( $nav_arrow_color ) ? $nav_arrow_color : '#333';
		$nav_back_color             = ! empty( $nav_back_color ) ? $nav_back_color : '#fff';
		$nav_border_color           = ! empty( $nav_border_color ) ? $nav_border_color : '#e4e4ed';
		$nav_arrow_hover_color      = ! empty( $nav_arrow_hover_color ) ? $nav_arrow_hover_color : '#fff';
		$nav_back_hover_color       = ! empty( $nav_back_hover_color ) ? $nav_back_hover_color : '#ff5500';
		$nav_border_hover           = ! empty( $nav_border_hover ) ? $nav_border_hover : '#ff5500';

		// carousel pagination settings
		$carousel_pagination        = ! empty( $carousel_pagination ) ? $carousel_pagination : 'no';
		$dots_color                 = ! empty( $dots_color ) ? $dots_color : '#333';
		$dots_active_color          = ! empty( $dots_active_color ) ? $dots_active_color : '#fff';
		//grid pagination settings
		$pagi_color                 = ! empty( $pagi_color ) ? $pagi_color : '#333';
		$pagi_border_color          = ! empty( $pagi_border_color ) ? $pagi_border_color : '#e4e4e4';
		$pagi_back_color            = ! empty( $pagi_back_color ) ? $pagi_back_color : '#fff';

		$pagi_hover_color           = ! empty( $pagi_hover_color ) ? $pagi_hover_color : '#fff';
		$pagi_hover_border_color    = ! empty( $pagi_hover_border_color ) ? $pagi_hover_border_color : '#ff5500';
		$pagi_hover_back_color      = ! empty( $pagi_hover_back_color ) ? $pagi_hover_back_color : '#ff5500';

		$pagi_active_color          = ! empty( $pagi_active_color ) ? $pagi_active_color : '#fff';
		$pagi_active_border_color   = ! empty( $pagi_active_border_color ) ? $pagi_active_border_color : '#ff5500';
		$pagi_active_back_color     = ! empty( $pagi_active_back_color ) ? $pagi_active_back_color : '#ff5500';

		$header_title     			= ! empty( $header_title ) ? $header_title : '';

		// shortcode attribute
        $layout                     = ! empty( $atts['layout'] ) ? $atts['layout'] : $layout;
        $theme                      = ! empty( $atts['theme'] ) ? $atts['theme'] : $theme;
        $total_products             = ! empty( $atts['total_products'] ) ? $atts['total_products'] : $total_products;
        $h_title_show               = ! empty( $atts['h_title_show'] ) ? $atts['h_title_show'] : $h_title_show;
        $header_title               = ! empty( $atts['header_title'] ) ? $atts['header_title'] : $header_title;
        $header_position            = ! empty( $atts['header_position'] ) ? $atts['header_position'] : $header_position;
        $display_title              = ! empty( $atts['display_title'] ) ? $atts['display_title'] : $display_title;
        $products_type              = ! empty( $atts['products_type'] ) ? $atts['products_type'] : $products_type;
        $display_sale_ribbon        = ! empty( $atts['display_sale_ribbon'] ) ? $atts['display_sale_ribbon'] : $display_sale_ribbon;
        $sale_ribbon_text           = ! empty( $atts['sale_ribbon_text'] ) ? $atts['sale_ribbon_text'] : $sale_ribbon_text;
        $sale_ribbon_position       = ! empty( $atts['sale_ribbon_position'] ) ? $atts['sale_ribbon_position'] : $sale_ribbon_position;
        $display_featured_ribbon    = ! empty( $atts['display_featured_ribbon'] ) ? $atts['display_featured_ribbon'] : $display_featured_ribbon;
        $feature_ribbon_text        = ! empty( $atts['feature_ribbon_text'] ) ? $atts['feature_ribbon_text'] : $feature_ribbon_text;
        $featured_ribbon_position   = ! empty( $atts['featured_ribbon_position'] ) ? $atts['featured_ribbon_position'] : $featured_ribbon_position;
        $display_sold_out_ribbon    = ! empty( $atts['display_sold_out_ribbon'] ) ? $atts['display_sold_out_ribbon'] : $display_sold_out_ribbon;
        $sold_out_ribbon_text       = ! empty( $atts['sold_out_ribbon_text'] ) ? $atts['sold_out_ribbon_text'] : $sold_out_ribbon_text;
        $sold_out_ribbon_position   = ! empty( $atts['sold_out_ribbon_position'] ) ? $atts['sold_out_ribbon_position'] : $sold_out_ribbon_position;
        $display_discount_ribbon    = ! empty( $atts['display_discount_ribbon'] ) ? $atts['display_discount_ribbon'] : $display_discount_ribbon;
        $discount_ribbon_position   = ! empty( $atts['discount_ribbon_position'] ) ? $atts['discount_ribbon_position'] : $discount_ribbon_position;
        $display_price              = ! empty( $atts['display_price'] ) ? $atts['display_price'] : $display_price;
        $display_ratings            = ! empty( $atts['display_ratings'] ) ? $atts['display_ratings'] : $display_ratings;
        $display_cart               = ! empty( $atts['display_cart'] ) ? $atts['display_cart'] : $display_cart;
        $img_crop                   = ! empty( $atts['img_crop'] ) ? $atts['img_crop'] : $img_crop;
        $crop_image_width           = ! empty( $atts['crop_image_width'] ) ? $atts['crop_image_width'] : $crop_image_width;
        $crop_image_height          = ! empty( $atts['crop_image_height'] ) ? $atts['crop_image_height'] : $crop_image_height;
        $A_play                     = ! empty( $atts['auto_play'] ) ? $atts['auto_play'] : $A_play;
        $repeat_product             = ! empty( $atts['repeat_product'] ) ? $atts['repeat_product'] : $repeat_product;
        $stop_hover                 = ! empty( $atts['stop_hover'] ) ? $atts['stop_hover'] : $stop_hover;
        $c_desktop                  = ! empty( $atts['c_desktop'] ) ? $atts['c_desktop'] : $c_desktop;
        $c_desktop_small            = ! empty( $atts['c_desktop_small'] ) ? $atts['c_desktop_small'] : $c_desktop_small;
        $c_tablet                   = ! empty( $atts['c_tablet'] ) ? $atts['c_tablet'] : $c_tablet;
        $c_mobile                   = ! empty( $atts['c_mobile'] ) ? $atts['c_mobile'] : $c_mobile;
        $slide_speed                = ! empty( $atts['slide_speed'] ) ? $atts['slide_speed'] : $slide_speed;
        $slide_time                 = ! empty( $atts['slide_time'] ) ? $atts['slide_time'] : $slide_time;
        $nav_show                   = ! empty( $atts['nav_show'] ) ? $atts['nav_show'] : $nav_show;
        $nav_position               = ! empty( $atts['nav_position'] ) ? $atts['nav_position'] : $nav_position;
        $nav_arrow_color            = ! empty( $atts['nav_arrow_color'] ) ? $atts['nav_arrow_color'] : $nav_arrow_color;
        $nav_back_color             = ! empty( $atts['nav_back_color'] ) ? $atts['nav_back_color'] : $nav_back_color;
        $nav_border_color           = ! empty( $atts['nav_border_color'] ) ? $atts['nav_border_color'] : $nav_border_color;
        $nav_arrow_hover_color      = ! empty( $atts['nav_arrow_hover_color'] ) ? $atts['nav_arrow_hover_color'] : $nav_arrow_hover_color;
        $nav_back_hover_color       = ! empty( $atts['nav_back_hover_color'] ) ? $atts['nav_back_hover_color'] : $nav_back_hover_color;
        $nav_border_hover           = ! empty( $atts['nav_border_hover'] ) ? $atts['nav_border_hover'] : $nav_border_hover;
        $carousel_pagination        = ! empty( $atts['carousel_pagination'] ) ? $atts['carousel_pagination'] : $carousel_pagination;
        $dots_color                 = ! empty( $atts['dots_color'] ) ? $atts['dots_color'] : $dots_color;
        $dots_active_color          = ! empty( $atts['dots_active_color'] ) ? $atts['dots_active_color'] : $dots_active_color;
        $g_column                   = ! empty( $atts['g_column'] ) ? $atts['g_column'] : $g_column;
        $g_tablet                   = ! empty( $atts['g_tablet'] ) ? $atts['g_tablet'] : $g_tablet;
        $g_mobile                   = ! empty( $atts['g_mobile'] ) ? $atts['g_mobile'] : $g_mobile;
        $grid_pagination            = ! empty( $atts['grid_pagination'] ) ? $atts['grid_pagination'] : $grid_pagination;
        $pagi_color                 = ! empty( $atts['pagi_color'] ) ? $atts['pagi_color'] : $pagi_color;
        $pagi_border_color          = ! empty( $atts['pagi_border_color'] ) ? $atts['pagi_border_color'] : $pagi_border_color;
        $pagi_back_color            = ! empty( $atts['pagi_back_color'] ) ? $atts['pagi_back_color'] : $pagi_back_color;
        $pagi_hover_color           = ! empty( $atts['pagi_hover_color'] ) ? $atts['pagi_hover_color'] : $pagi_hover_color;
        $pagi_hover_border_color    = ! empty( $atts['pagi_hover_border_color'] ) ? $atts['pagi_hover_border_color'] : $pagi_hover_border_color;
        $pagi_hover_back_color      = ! empty( $atts['pagi_hover_back_color'] ) ? $atts['pagi_hover_back_color'] : $pagi_hover_back_color;
        $pagi_active_border_color   = ! empty( $atts['pagi_active_border_color'] ) ? $atts['pagi_active_border_color'] : $pagi_active_border_color;
        $pagi_active_back_color     = ! empty( $atts['pagi_active_back_color'] ) ? $atts['pagi_active_back_color'] : $pagi_active_back_color;

		//style shortcode act 
        $header_font_size                       = ! empty( $atts['header_font_size'] ) ? $atts['header_font_size'] : $header_font_size;
        $header_font_color                      = ! empty( $atts['header_font_color'] ) ? $atts['header_font_color'] : $header_font_color;
        $title_font_size                        = ! empty( $atts['title_font_size'] ) ? $atts['title_font_size'] : $title_font_size;
        $title_font_color                       = ! empty( $atts['title_font_color'] ) ? $atts['title_font_color'] : $title_font_color;
        $title_hover_font_color                 = ! empty( $atts['title_hover_font_color'] ) ? $atts['title_hover_font_color'] : $title_hover_font_color;
        $price_font_size                        = ! empty( $atts['price_font_size'] ) ? $atts['price_font_size'] : $price_font_size;
        $price_font_color                       = ! empty( $atts['price_font_color'] ) ? $atts['price_font_color'] : $price_font_color;
        $ratings_size                           = ! empty( $atts['ratings_size'] ) ? $atts['ratings_size'] : $ratings_size;
        $ratings_color                          = ! empty( $atts['ratings_color'] ) ? $atts['ratings_color'] : $ratings_color;
        $cart_font_color                        = ! empty( $atts['cart_font_color'] ) ? $atts['cart_font_color'] : $cart_font_color;
        $cart_bg_color                          = ! empty( $atts['cart_bg_color'] ) ? $atts['cart_bg_color'] : $cart_bg_color;
        $cart_button_hover_color                = ! empty( $atts['cart_button_hover_color'] ) ? $atts['cart_button_hover_color'] : $cart_button_hover_color;
        $cart_button_hover_font_color           = ! empty( $atts['cart_button_hover_font_color'] ) ? $atts['cart_button_hover_font_color'] : $cart_button_hover_font_color;
        $ribbon_bg_color                        = ! empty( $atts['ribbon_bg_color'] ) ? $atts['ribbon_bg_color'] : $ribbon_bg_color;

		$display_sale_ribbon        = ! empty( $display_sale_ribbon ) ? $display_sale_ribbon : 'no';
        $sale_ribbon_text           = ! empty( $sale_ribbon_text ) ? $sale_ribbon_text : '';
        $sale_ribbon_position       = ! empty( $sale_ribbon_position ) ? $sale_ribbon_position : 'top_left';
        $display_featured_ribbon    = ! empty( $display_featured_ribbon ) ? $display_featured_ribbon : 'no';
        $feature_ribbon_text        = ! empty( $feature_ribbon_text ) ? $feature_ribbon_text : '';
        $featured_ribbon_position   = ! empty( $featured_ribbon_position ) ? $featured_ribbon_position : 'top_right';
        $display_sold_out_ribbon    = ! empty( $display_sold_out_ribbon ) ? $display_sold_out_ribbon : 'no';
        $sold_out_ribbon_text       = ! empty( $sold_out_ribbon_text ) ? $sold_out_ribbon_text : '';
        $sold_out_ribbon_position   = ! empty( $sold_out_ribbon_position ) ? $sold_out_ribbon_position : 'bottom_left';
        $display_discount_ribbon    = ! empty( $display_discount_ribbon ) ? $display_discount_ribbon : 'no';
        $discount_ribbon_position   = ! empty( $discount_ribbon_position ) ? $discount_ribbon_position : 'bottom_right';

		$ribbon_args = array(
			'display_sale_ribbon'        => $display_sale_ribbon,
            'sale_ribbon_text'           => $sale_ribbon_text,
            'sale_ribbon_position'       => $sale_ribbon_position,
            'display_featured_ribbon'    => $display_featured_ribbon,
            'feature_ribbon_text'        => $feature_ribbon_text,
            'featured_ribbon_position'   => $featured_ribbon_position,
            'display_sold_out_ribbon'    => $display_sold_out_ribbon,
            'sold_out_ribbon_text'       => $sold_out_ribbon_text,
            'sold_out_ribbon_position'   => $sold_out_ribbon_position,
            'display_discount_ribbon'    => $display_discount_ribbon,
            'discount_ribbon_position'   => $discount_ribbon_position,
		);

		$carousel_desktop_column    = ! empty( $c_desktop ) ? intval( $c_desktop ) : 4;
		$carousel_laptop_column     = ! empty( $c_desktop_small ) ? intval( $c_desktop_small ) : 3;
		$carousel_tablet_column     = ! empty( $c_tablet ) ? intval( $c_tablet ) : 2;
		$carousel_mobile_column     = ! empty( $c_mobile ) ? intval( $c_mobile ) : 1;

		$header_class = '';

		if( 'middle' == $header_position ) {
			$header_class = 'wpcu-products__header--middle';
		} elseif( 'right' == $header_position ) {
			$header_class = 'wpcu-products__header--right';
		}

		ob_start();

		if ( $loop->have_posts() ) { ?>

		<?php if ( 'yes' == $h_title_show && ! empty( $header_title ) ) { ?>
		<div class="wpcu-products__header <?php echo esc_attr( $header_class ); ?>" style="
		--wpcu-headerFontSize: <?php echo esc_attr( $header_font_size ); ?>px;
		--wpcu-headerFontColor: <?php echo esc_attr( $header_font_color ); ?>;
		"> <!-- .wpcu-products__header--middle /.wpcu-products__header--right -->
			<h2><?php echo esc_html( $header_title ); ?></h2>
		</div>
		<?php } ?>

		<div
		class="wpcu-products wpcu-<?php echo esc_attr( $theme ); ?> wpcu-lazy-load <?php echo ( 'carousel' == $layout ) ? 'wpcu-carousel' : ''; ?>"
		style="
			--wpcu-productTitleSize: <?php echo esc_attr( $title_font_size ); ?>px;
			--wpcu-productTitleColor: <?php echo esc_attr( $title_font_color ); ?>;
			--wpcu-productTitleColorHover: <?php echo esc_attr( $title_hover_font_color ); ?>;
			--wpcu-productPriceSize: <?php echo esc_attr( $price_font_size ); ?>px;
			--wpcu-productPriceColor: <?php echo esc_attr( $price_font_color ); ?>;
			--wpcu-productRatingSize: <?php echo esc_attr( $ratings_size ); ?>px;
			--wpcu-productRatingColor: <?php echo esc_attr( $ratings_color ); ?>;
			--wpcu-buttonColor: <?php echo esc_attr( $cart_font_color ); ?>;
			--wpcu-buttonColorHover: <?php echo esc_attr( $cart_button_hover_font_color ); ?>;
			--wpcu-buttonBgColor: <?php echo esc_attr( $cart_bg_color ); ?>;
			--wpcu-buttonBgColorHover: <?php echo esc_attr( $cart_button_hover_color ); ?>;
			--wpcu-ribbonBgColor: <?php echo esc_attr( $ribbon_bg_color ); ?>;
			--wpcu-qvIconColor: <?php echo esc_attr( $quick_view_icon_color ); ?>;
			--wpcu-qvBgColor: <?php echo esc_attr( $quick_view_icon_back_color ); ?>;
		"
		<?php if ( 'carousel' == $layout ) { ?>
		data-wpcu-items="4"
		data-wpcu-margin="30"
		data-wpcu-loop="<?php echo ( 'yes' == $repeat_product ) ? 'true' : 'false'; ?>"
		data-wpcu-perslide="1"
		data-wpcu-speed="<?php echo esc_attr( $slide_speed ); ?>"
		data-wpcu-autoplay='
		<?php if( 'yes' == $A_play ) { ?>
		{
			"delay": "<?php echo esc_attr( $slide_time ); ?>",
			"pauseOnMouseEnter": <?php echo ( 'true' == $stop_hover ) ? "true" : "false"; ?>,
			"disableOnInteraction": false,
			"stopOnLastSlide": true
		}
		<?php } else { ?>
			false
		<?php } ?>
		'
		data-wpcu-responsive='{
			"0": {"slidesPerView": "<?php echo esc_attr( $carousel_mobile_column ); ?>", "spaceBetween": "20"},
			"768": {"slidesPerView": "<?php echo esc_attr( $carousel_tablet_column ); ?>", "spaceBetween": "30"},
			"992": {"slidesPerView": "<?php echo esc_attr( $carousel_laptop_column ); ?>", "spaceBetween": "30"},
			"1200": {"slidesPerView": "<?php echo esc_attr( $carousel_desktop_column ); ?>", "spaceBetween": "30"}
		}'
		<?php } ?>
		>
		<?php if( ( 'carousel' == $layout && 'yes' == $nav_show ) && ( 'top-left' == $nav_position || 'top-right' == $nav_position || 'top-middle' == $nav_position || 'middle' == $nav_position )  ) {
				include WCPCSU_INC_DIR . 'template/navigation.php';
		} ?>
		<div class="<?php echo ( 'carousel' == $layout ) ? 'swiper-wrapper' : 'wpcu-row wpcu-column-' . esc_attr( $g_column ) . ' wpcu-column-md-' . esc_attr( $g_tablet ) . ' wpcu-column-sm-' . esc_attr( $g_mobile ) . ''; ?>">
		<?php
			
			while( $loop->have_posts() ) : $loop->the_post();

				global $post, $product;

				$thumb = get_post_thumbnail_id();

				// crop the image if the cropping is enabled.
				if ('yes' === $img_crop){
					$wpcsu_img = wpcsu_image_cropping( $thumb, $crop_image_width, $crop_image_height, true, 100 )['url'];
				}else{
					$aazz_thumb = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'large' );
					$wpcsu_img = $aazz_thumb['0'];
				}

				$wpcsu_img  = ! empty( $wpcsu_img ) ? $wpcsu_img : wc_placeholder_img_src();
				$sale_price = $product->get_sale_price();
				$ratings    = ( ( $product->get_average_rating() / 5 ) * 100 );

				include WCPCSU_INC_DIR . 'template/theme/' . $theme . '.php';

			endwhile;
			
			wp_reset_postdata();
			?>

			</div>
			<?php if( ( 'carousel' == $layout && 'yes' == $nav_show ) && ( 'bottom-left' == $nav_position || 'bottom-right' == $nav_position || 'bottom-middle' == $nav_position ) ) {
				include WCPCSU_INC_DIR . 'template/navigation.php';
			 } ?>
			 <?php if( ( 'grid' == $layout && 'yes' == $grid_pagination ) || ( 'carousel' == $layout && 'yes' ==  $carousel_pagination ) ) {
				include WCPCSU_INC_DIR . 'template/pagination.php';
			 } ?>
		</div><!-- ends: .wpcu-products -->
		<?php
			if( 'theme_2' == $theme ){
				include WCPCSU_INC_DIR . 'template/quick-view.php';
			}
		?>
		<?php

		} else {

			esc_html_e( 'No products found', 'woocommerce-product-carousel-slider-and-grid-ultimate' );
		}

		return ob_get_clean();
	}

	public function parse_query( $data_array, $atts ) {
		$value = is_array( $data_array ) ? $data_array : array();
		extract( $value );
		$paged                          =  wcpcsu_get_paged_num();
		$paged                          = ! empty( $paged ) ? $paged : '';
		$total_products                 = ! empty( $atts['total_products'] ) ? $atts['total_products'] : $total_products;
        $products_type                  = ! empty( $atts['products_type'] ) ? $atts['products_type'] : $products_type;
		$layout                         = ! empty( $atts['layout'] ) ? $atts['layout'] : $layout;
        $grid_pagination                = ! empty( $atts['grid_pagination'] ) ? $atts['grid_pagination'] : $grid_pagination;
		$common_args = array(
			'post_type'      => 'product',
			'posts_per_page' => ! empty( $total_products ) ? intval( $total_products ) : 12,
			'post_status'    => 'publish',
		);
		if( 'grid' == $layout && 'yes' == $grid_pagination ) {
			$common_args['paged']    = $paged;
		}
		if( $products_type == "latest" ) {
			$args = $common_args;
		}
		elseif( $products_type == "older" ) {
			$older_args = array(
				'orderby'     => 'date',
				'order'       => 'ASC'
			);
			$args = array_merge( $common_args, $older_args );
		}
		elseif( $products_type == "featured" ) {
			$meta_query  = WC()->query->get_meta_query();
			$tax_query   = WC()->query->get_tax_query();

			$tax_query[] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => 'featured',
				'operator' => 'IN',
			);
			$featured_args = array(
				'meta_query' => $meta_query,
				'tax_query' => $tax_query,
			);
			$args = array_merge( $common_args, $featured_args );
		}

		else {
			$args = $common_args;
		}
		$loop = new WP_Query( $args );

		return $loop;
	}

	function aazz_show_discount_percentage() {

		global $product;

		if ( $product->is_on_sale() ) {

			if ( ! $product->is_type( 'variable' ) ) {
				if( 0 < $product->get_regular_price() && 0 < $product->get_sale_price()) {
					$max_percentage = ( ( $product->get_regular_price() - $product->get_sale_price() ) / $product->get_regular_price() ) * 100;
				}
			} else {

				$max_percentage = 0;
				$percentage = '';

				foreach ( $product->get_children() as $child_id ) {
					$variation = wc_get_product( $child_id );
					$price = $variation->get_regular_price();
					$sale = $variation->get_sale_price();
					if ( $price != 0 && ! empty( $sale ) ) $percentage = ( $price - $sale ) / $price * 100;
					if ( $percentage > $max_percentage ) {
						$max_percentage = $percentage;
					}
				}

			}

			return ! empty( $max_percentage ) ? round( $max_percentage ) . "%" : '';

		}

	}

	function get_total_reviews_count(){
		return get_comments(array(
			'status'   => 'approve',
			'post_status' => 'publish',
			'post_type'   => 'product',
			'count' => true
		));
	}

	public function wcpcsu_style_files () {
		wp_enqueue_style( 'wcpcsu-main' );
		wp_enqueue_style( 'wcpcsu-style' );
		wp_enqueue_style( 'wcpcsu-swmodal' );
		wp_enqueue_style( 'wcpcsu-swiper' );

		wp_enqueue_script( 'wcpcsu-swmodal-js' );
		wp_enqueue_script( 'wcpcsu-swiper-js' );
		wp_enqueue_script( 'wcpcsu-main-js' );
	}

	public function ajax_quick_view() {
		$nonce          = ! empty( $_POST['nonce'] ) ? $_POST['nonce'] : '';
		$product_id     = ! empty( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : '';

		if( ! wp_verify_nonce( $nonce, 'wcpcsu_quick_view_' . $product_id ) ) {
			wp_send_json_error();
		}

		if ( empty( $product_id ) ) {
			wp_send_json_error();
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return;
		}

		$wpcsu_img = wp_get_attachment_image_url( get_post_thumbnail_id( $product_id ), 'large' );

		ob_start();
		?>
		<div>
			<h2 class='wpcu-modal__product-title'><?php echo esc_html( get_the_title( $product_id ) );?></h2>
			<?php if ( ! empty( $wpcsu_img ) ) { ?>
			<div class='wpcu-modal__product-image'>
				<img src="<?php echo esc_url( $wpcsu_img ); ?>" alt="<?php echo esc_attr( get_the_title( $product_id ) );?>">
			</div>
			<?php } ?>
			<div class='wpcu-modal__product-description'>
				<?php echo wp_kses_post( $product->get_description() ); ?>
			</div>
			<div class='wpcu-modal__product-price'><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
			<div class='wpcu-modal__product-action'><?php echo wp_kses_post( do_shortcode('[add_to_cart id="' . $product_id . '" show_price = "false"]') ); ?></div>
		</div>
		<?php
		$template = ob_get_clean();

		wp_send_json( $template );
	}
}
