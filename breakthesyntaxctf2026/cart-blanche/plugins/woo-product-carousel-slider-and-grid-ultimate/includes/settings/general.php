<?php
/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$layout                     = ! empty( $layout ) ? $layout : 'carousel';
$h_title_show               = ! empty( $h_title_show ) ? $h_title_show : 'no';
$display_full_title         = ! empty( $display_full_title ) ? $display_full_title : 'yes';
$ribbon                     = ! empty( $ribbon ) ? $ribbon : 'discount';
$header_position            = ! empty( $header_position ) ? $header_position : 'middle';
$total_products_label       = ( ! empty( $layout ) && 'grid' == $layout ) ? __( 'Products Per Page','woocommerce-product-carousel-slider-and-grid-ultimate' ) : __( 'Total Products to Display','woocommerce-product-carousel-slider-and-grid-ultimate' );
$theme                      = ! empty( $theme ) ? $theme : 'theme_1';
$display_sale_ribbon        = ! empty( $display_sale_ribbon ) ? $display_sale_ribbon : 'no';
$sale_ribbon_position       = ! empty( $sale_ribbon_position ) ? $sale_ribbon_position : 'top_left';
$display_featured_ribbon    = ! empty( $display_featured_ribbon ) ? $display_featured_ribbon : 'no';
$featured_ribbon_position   = ! empty( $featured_ribbon_position ) ? $featured_ribbon_position : 'top_right';
$display_sold_out_ribbon    = ! empty( $display_sold_out_ribbon ) ? $display_sold_out_ribbon : 'no';
$sold_out_ribbon_position   = ! empty( $sold_out_ribbon_position ) ? $sold_out_ribbon_position : 'bottom_left';
$display_discount_ribbon    = ! empty( $display_discount_ribbon ) ? $display_discount_ribbon : 'no';
$discount_ribbon_position   = ! empty( $discount_ribbon_position ) ? $discount_ribbon_position : 'bottom_right';
?>
<div id="lcsp-tab-5" class="lcsp-tab-content">
	<div class="cmb2-wrap form-table">
		<div id="cmb2-metabox" class="cmb2-metabox cmb-field-list">

			<div class="cmb-row cmb-type-text-medium">
				<div class="cmb-th">
					<label for="lcsp_slider_title"><?php esc_html_e( 'Layout', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></label>
				</div>
				<div class="cmb-td">
					<div class="cmd-switch">
						<div class="cmd-switch-carousel <?php echo ( 'carousel' == $layout ) ? 'active' : ''; ?>">
							<div class="cmd-switch-item cmd-switch-carousel-img" data-value="carousel">
								<span class="cmd-switch-item-icon">
									<span class="cmd-switch-item-icon__wrapper">
										<img class="svg_compile" src="<?php echo WCPCSU_URL .'assets/icons/square-check-solid.svg' ?>" >
									</span>
								</span>
								<img src="<?php echo WCPCSU_URL .'admin/img/carousel.jpg' ?>" alt="carousel">
								<input type="radio" name="wcpscu[layout]" class="wcpscu_radio_layout wcpscu_carousel_layout" value="carousel" <?php checked( $layout, 'carousel' ); ?>>
							</div>
							<p><?php _e( 'Carousel', 'woocommerce-product-carousel-slider-and-grid-ultimate' );?></p>
						</div>
						<div class="cmd-switch-grid <?php echo ( 'grid' == $layout ) ? 'active' : ''; ?>">
							<div class=" cmd-switch-item cmd-switch-grid-img" data-value="grid">
								<span class="cmd-switch-item-icon">
									<span class="cmd-switch-item-icon__wrapper">
										<img class="svg_compile" src="<?php echo WCPCSU_URL .'assets/icons/square-check-solid.svg' ?>" >
									</span>
								</span>
								<img src="<?php echo WCPCSU_URL .'admin/img/grid.jpg' ?>" alt="grid">
								<input type="radio" name="wcpscu[layout]" class="wcpscu_radio_layout wcpscu_grid_layout" value="grid" <?php checked( $layout, 'grid' ); ?>>
							</div>
							<p><?php esc_html_e( 'Grid', 'woocommerce-product-carousel-slider-and-grid-ultimate' );?></p>
						</div>
					</div>
				</div>
			</div>
			<!--Select theme-->
			<div class="cmb-row cmb-type-radio">
				<div class="cmb-th">
					<label for="lcsp_ap"><?php esc_html_e( 'Select Theme', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></label>
				</div>
				<div class="cmb-td">
					<div class="cmb-theme-wrapper">
						<select id="theme_" class="wcpscu_theme" name="wcpscu[theme]">
							<option value="theme_1" <?php selected( $theme, 'theme_1'); ?> ><?php esc_html_e( 'Theme-1', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
							<option value="theme_2" <?php selected( $theme, 'theme_2'); ?>><?php esc_html_e( 'Theme-2
							', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
							<option value="theme_3" <?php selected( $theme, 'theme_3'); ?>><?php esc_html_e( 'Theme-3
							', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
							<option disabled><?php esc_html_e( 'Theme-4 (Pro)', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
							<option disabled><?php esc_html_e( 'Theme-5 (Pro)', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
							<option disabled><?php esc_html_e( 'Theme-6 (Pro)', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
							<option disabled><?php esc_html_e( 'Theme-7 (Pro)', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
							<option disabled><?php esc_html_e( 'Theme-8 (Pro)', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
							<option disabled><?php esc_html_e( 'Theme-9 (Pro)', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
							<option disabled><?php esc_html_e( 'Theme-10 (Pro)', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
							<option disabled><?php esc_html_e( 'Theme-11 (Pro)', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
							<option disabled><?php esc_html_e( 'Theme-12 (Pro)', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
							<option disabled><?php esc_html_e( 'Theme-13 (Pro)', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
							<option disabled><?php esc_html_e( 'Theme-14 (Pro)', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
							<option disabled><?php esc_html_e( 'Theme-15 (Pro)', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
						</select>
						<a target="_blank" ref="noopener" href="https://demo.wpwax.com/plugin/woocommerce-product-carousel-slider-and-grid-ultimate-demo-slider/"><?php esc_html_e( 'View All Themes', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></a>
					</div>
				</div>
			</div>

			<!-- Total Products -->
			<div class="cmb-row cmb-type-text-medium">
				<div class="cmb-th">
					<label for="wcpscu_total_products"
						id="wcpscu_total_pdt"><?php echo ! empty( $total_products_label ) ? esc_html( $total_products_label ) : esc_html__( 'Total Products to Display','woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></label>
				</div>
				<div class="cmb-td">
					<input type="text" class="cmb2-text-small" name="wcpscu[total_products]" id="wcpscu_total_products"
						value="<?php echo esc_attr( ! empty( $total_products ) ? intval( $total_products ) : 12 ); ?>">
					<p class="cmb2-metabox-description">
						<?php esc_html_e( 'How many products to display in the carousel slider', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?>
					</p>
				</div>
			</div>

			<div class="cmb-row cmb-type-radio bolod">
				<div class="cmb-th">
					<label for="lcsp_ic">
						<?php esc_html_e( 'Display Header', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?>
					</label>
				</div>
				<div class="cmb-td">
					<ul class="cmb2-radio-list cmb2-list cmb2-radio-switch">
						<li>
							<input type="radio" class="cmb2-option cmb2-radio-switch1" name="wcpscu[h_title_show]"
								id="wcpscu[h_title_show]1" value="yes" <?php checked( 'yes', $h_title_show, true );  ?>>
							<label for="wcpscu[h_title_show]1">
								<?php esc_html_e( 'Yes', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?>
							</label>
						</li>
						<li>
							<input type="radio" class="cmb2-option cmb2-radio-switch2" name="wcpscu[h_title_show]"
								id="wcpscu[h_title_show]2" value="no" <?php checked( 'no', $h_title_show, true );  ?>>
							<label for="wcpscu[h_title_show]2">
								<?php esc_html_e( 'No', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?>
							</label>
						</li>
					</ul>
					<div class="cmb-td-medium">
						<label for="lcsp_slider_title"><?php esc_html_e( 'Header Title', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						<input type="text" class="cmb2-text-medium" name="wcpscu[header_title]" id="lcsp_slider_title"
							value="<?php echo ! empty( $header_title ) ? esc_attr( $header_title ) : ''; ?>">
					</div>
					<div class="cmb-td-multicheck">
						<label
							for="wcpscup_products_type"><?php esc_html_e( 'Header Position', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></label>
						<ul class="cmb2-radio-list cmb2-list">
							<li>
								<input type="radio" class="cmb2-option" name="wcpscu[header_position]" id="center" value="center"
								<?php checked( 'Center', $header_position, true );  ?>>
								<label for="center"><?php esc_html_e( 'Center', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></label>
							</li>

							<li>
								<input type="radio" class="cmb2-option" name="wcpscu[header_position]" id="left" value="left"
									<?php checked( 'left', $header_position ); ?>>
								<label for="left"><?php esc_html_e( 'Left', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></label>
							</li>

							<li>
								<input type="radio" class="cmb2-option" name="wcpscu[header_position]" id="right" value="right"
									<?php checked( 'right', $header_position ); ?>>
								<label for="right"><?php esc_html_e( 'Right', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></label>
							</li>

						</ul>
					</div>
				</div>
			</div>

			<!--Display Product Title-->
			<div class="cmb-row cmb-type-radio">
				<div class="cmb-th">
					<label
						for="wcpscu_display_title"><?php esc_html_e( 'Display Product Title', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></label>
				</div>
				<div class="cmb-td">
					<ul class="cmb2-radio-list cmb2-list cmb2-radio-switch">
						<li>
							<input type="radio" class="cmb2-option cmb2-radio-switch1" name="wcpscu[display_title]"
								id="wcpscu_display_title1" value="yes"
								<?php if ( empty( $display_title ) || 'yes' === $display_title ) { echo 'checked'; } ?>>
							<label for="wcpscu_display_title1"><?php esc_html_e( 'Yes', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></label>
						</li>
						<li>
							<input type="radio" class="cmb2-option cmb2-radio-switch2" name="wcpscu[display_title]"
								id="wcpscu_display_title2" value="no"
								<?php if ( ! empty( $display_title ) ) { checked( 'no', $display_title ); } ?>>
							<label for="wcpscu_display_title2"><?php esc_html_e( 'No', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></label>
						</li>
					</ul>
				</div>
			</div>

			<!-- Products type-->
			<div class="cmb-row cmb-type-multicheck">
				<div class="cmb-th">
					<label for="wcpscu_products_type"><?php esc_html_e( 'Products Type', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></label>
				</div>
				<div class="cmb-td">
					<ul class="cmb2-radio-list cmb2-list">
						<li><input type="radio" class="cmb2-option" name="wcpscu[products_type]"
								id="wcpscu_products_type" value="latest"
								<?php if ( ( !empty($products_type) && 'latest' == $products_type) || empty($products_type)) { echo 'checked';} ?>>
							<label
								for="wcpscu_products_type"><?php esc_html_e( 'Latest Products', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></label>
						</li>
						<li><input type="radio" class="cmb2-option" name="wcpscu[products_type]"
								id="wcpscu_products_type9" value="older"
								<?php if( ! empty( $products_type ) ) { checked( 'older', $products_type ); } ?>> <label
								for="wcpscu_products_type9"><?php esc_html_e('Older Products', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
						<li><input type="radio" class="cmb2-option" name="wcpscu[products_type]"
								id="wcpscu_products_type3" value="featured"
								<?php if( ! empty( $products_type ) ) { checked('featured', $products_type ); } ?>> <label
								for="wcpscu_products_type3"><?php esc_html_e('Featured Products', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
					</ul>
					<!-- <p class="cmb2-metabox-description">
						<?php esc_html_e('What type of products to display in the carousel slider', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?>
					</p> -->
					<ul class="cmb2-radio-list-pro">
						<p>Following options
							available in <a
								href="https://wpwax.com/product/woocommerce-product-carousel-slider-grid-ultimate-pro"
								target="_blank"><?php esc_html_e( 'Pro Version', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></a>:</p>
						<li><input disabled type="radio" class="cmb2-option" name="wcpscu_products_type"
								id="wcpscu_ds_products_type" value="onsale"> <label
								for="wcpscu_ds_products_type"><?php esc_html_e('On Sale Products', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
						<li><input disabled type="radio" class="cmb2-option" name="wcpscu_products_type"
								id="wcpscu_ds_products_type" value="bestselling"> <label
								for="wcpscu_ds_products_type"><?php esc_html_e('Best Selling Products', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
						<li><input disabled type="radio" class="cmb2-option" name="wcpscu_products_type"
								id="wcpscu_ds_products_type" value="bestselling"> <label
								for="wcpscu_ds_products_type"><?php esc_html_e('Top Rated Products', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
						<li><input disabled type="radio" class="cmb2-option" name="wcpscu_products_type"
								id="wcpscu_ds_products_type" value="bestselling"> <label
								for="wcpscu_ds_products_type"><?php esc_html_e('Random Products', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
						<li><input disabled type="radio" class="cmb2-option" name="wcpscu_products_type"
								id="wcpscu_ds_products_type" value="category"> <label
								for="wcpscu_ds_products_type"><?php esc_html_e('Category Products', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
						<li class="productsbyidw"><input disabled type="radio" class="cmb2-option"
								name="wcpscu_products_type" id="wcpscu_ds_products_type" value="productsbyid"> <label
								for="wcpscu_ds_products_type"><?php esc_html_e('Products by ID', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
						<li><input disabled type="radio" class="cmb2-option" name="wcpscu_products_type"
								id="wcpscu_ds_products_type" value="productsbysku"> <label
								for="wcpscu_ds_products_type"><?php esc_html_e('Products by SKU', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
						<li><input disabled type="radio" class="cmb2-option" name="wcpscu_products_type"
								id="wcpscu_ds_products_type" value="productsbytag"> <label
								for="wcpscu_ds_products_type"><?php esc_html_e('Products by Tags', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
						<li><input disabled type="radio" class="cmb2-option" name="wcpscu_products_type"
								id="wcpscu_ds_products_type" value="productsbyyear"> <label
								for="wcpscu_ds_products_type"><?php esc_html_e('Products by Year', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
						<li><input disabled type="radio" class="cmb2-option" name="wcpscu_products_type"
								id="wcpscu_ds_products_type" value="productsbymonth"> <label
								for="wcpscu_ds_products_type"><?php esc_html_e('Products by Month', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
					</ul>
				</div>
			</div>

			<!--Show "sale' Ribbon for the product on sale-->
			<div class="cmb-row cmb-type-radio" id="sale_ribbon_wrapper">
				<div class="cmb-th">
					<label
						for="wcpscu_display_sale_ribbon"><?php esc_html_e('Display "Sale" Badge', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
				</div>
				<div class="cmb-td">
					<ul class="cmb2-radio-list cmb2-list cmb2-radio-switch">
						<li><input type="radio" class="cmb2-option cmb2-radio-switch1"
								name="wcpscu[display_sale_ribbon]" id="wcpscu_display_sale_ribbon1" value="yes"
								<?php checked( $display_sale_ribbon, 'yes' ); ?>>
							<label
								for="wcpscu_display_sale_ribbon1"><?php esc_html_e('Yes', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
						<li><input type="radio" class="cmb2-option cmb2-radio-switch2"
								name="wcpscu[display_sale_ribbon]" id="wcpscu_display_sale_ribbon2" value="no"
								<?php checked( $display_sale_ribbon, 'no' ); ?>>
							<label
								for="wcpscu_display_sale_ribbon2"><?php esc_html_e('No', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
					</ul>
					<!-- <p class="cmb2-metabox-description">
						<?php esc_html_e('Whether to show the "Sale" ribbon for On-Sale products or not', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?>
					</p> -->

					<!--Text for the sale ribbon-->
					<div id="sale_ribbon_text_wrapper">
						<label for="wcpscu_sale_ribbon_text">
								<?php esc_html_e( 'Text', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?>
							</label>
						<input type="text" class="cmb2-text-medium" name="wcpscu[sale_ribbon_text]"
							id="wcpscu_sale_ribbon_text"
							value="<?php echo ! empty( $sale_ribbon_text ) ?  esc_attr( $sale_ribbon_text ) : esc_html__('Sale!', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?>"
							placeholder="<?php esc_attr_e( 'e.g. Sale!', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?>">
					</div>

					<!--Position of sale ribbon -->
					<div id="sale_ribbon_text_wrapper">
						<label for="sale_ribbon_position">
								<?php esc_html_e( 'Position', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?>
						</label>
						<select id="sale_ribbon_position" class="sale_ribbon_position" name="wcpscu[sale_ribbon_position]">
							<option value="top_left" <?php selected( $sale_ribbon_position, 'top_left'); ?> >
								<?php esc_html_e( 'Top Left', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?>
							</option>
							<option value="top_right" <?php selected( $sale_ribbon_position, 'top_right'); ?>>
								<?php esc_html_e( 'Top Right', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?>
							</option>
							<option value="bottom_left" <?php selected( $sale_ribbon_position, 'bottom_left'); ?>>
								<?php esc_html_e( 'Top Left', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?>
							</option>
							<option value="bottom_right" <?php selected( $sale_ribbon_position, 'bottom_right'); ?>>
								<?php esc_html_e( 'Top Right', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?> 
							</option>
						</select>
					</div>

				</div>
			</div>



			<!--Display "Featured" Ribbon-->
			<div class="cmb-row cmb-type-radio" id="feature_ribbon_wrapper">
				<div class="cmb-th">
					<label for="wcpscu_display_featured_ribbon"><?php esc_html_e('Display "Featured" Badge', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
				</div>
				<div class="cmb-td">
					<ul class="cmb2-radio-list cmb2-list cmb2-radio-switch">
						<li><input type="radio" class="cmb2-option cmb2-radio-switch1"
								name="wcpscu[display_featured_ribbon]" id="wcpscu_display_featured_ribbon1" value="yes"
								<?php checked( $display_featured_ribbon, 'yes' ); ?>>
							<label
								for="wcpscu_display_featured_ribbon1"><?php esc_html_e('Yes', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
						<li><input type="radio" class="cmb2-option cmb2-radio-switch2"
								name="wcpscu[display_featured_ribbon]" id="wcpscu_display_featured_ribbon2" value="no"
								<?php checked( $display_featured_ribbon, 'no' ); ?>>
							<label
								for="wcpscu_display_featured_ribbon2"><?php esc_html_e('No', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
					</ul>
					<!-- <p class="cmb2-metabox-description">
						<?php esc_html_e('Whether to show the "Featured" ribbon for featured products or not.', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?>
					</p> -->
					<!-- Text for the feature ribbon -->
					<div id="feature_ribbon_text_wrapper">
						<input type="text" class="cmb2-text-medium" name="wcpscu[feature_ribbon_text]"
							id="wcpscu_feature_ribbon_text"
							value="<?php echo ! empty( $feature_ribbon_text ) ? esc_attr( $feature_ribbon_text ) : esc_html__('Featured!', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?>"
							placeholder="<?php esc_attr_e( 'e.g. Featured!', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?>">
						<p class="cmb2-metabox-description">
							<?php esc_html_e('Enter the text for the featured ribbon. Default text is "Featured!".', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?>
						</p>
					</div>

					<!--Position of Featured ribbon -->
					<div id="featured_ribbon_text_wrapper">
						<label for="featured_ribbon_position">
								<?php esc_html_e( 'Position', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?>
						</label>
						<select id="featured_ribbon_position" class="featured_ribbon_position" name="wcpscu[featured_ribbon_position]">
							<option value="top_left" <?php selected( $featured_ribbon_position, 'top_left'); ?> ><?php esc_html_e( 'Top Left', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
							<option value="top_right" <?php selected( $featured_ribbon_position, 'top_right'); ?>><?php esc_html_e( 'Top Right', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
							<option value="bottom_left" <?php selected( $featured_ribbon_position, 'bottom_left'); ?>><?php esc_html_e( 'Bottom Left', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
							<option value="bottom_right" <?php selected( $featured_ribbon_position, 'bottom_right'); ?>><?php esc_html_e( 'Bottom Right', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?>
							</option>
						</select>
					</div>
				</div>
			</div>


			<!--Show ribbon for sold out products-->
			<div class="cmb-row cmb-type-radio" id="sold_out_ribbon_wrapper">
				<div class="cmb-th">
					<label
						for="wcpscu_display_sold_out_ribbon"><?php esc_html_e('Display the "Sold Out" Badge', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
				</div>
				<div class="cmb-td">
					<ul class="cmb2-radio-list cmb2-list cmb2-radio-switch">
						<li><input type="radio" class="cmb2-option cmb2-radio-switch1"
								name="wcpscu[display_sold_out_ribbon]" id="wcpscu_display_sold_out_ribbon1" value="yes"
								<?php checked( $display_sold_out_ribbon, 'yes' ); ?>>
							<label
								for="wcpscu_display_sold_out_ribbon1"><?php esc_html_e('Yes', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
						<li><input type="radio" class="cmb2-option cmb2-radio-switch2"
								name="wcpscu[display_sold_out_ribbon]" id="wcpscu_display_sold_out_ribbon2" value="no"
								<?php checked( $display_sold_out_ribbon, 'no' ); ?>>
							<label
								for="wcpscu_display_sold_out_ribbon2"><?php esc_html_e('No', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
					</ul>
					<!-- <p class="cmb2-metabox-description">
						<?php esc_html_e('Whether to display the "Sold Out" ribbon for out-of-stock products or not', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?>
					</p> -->

					<!-- Text for the sold out ribbon -->

					<div id="sold_out_ribbon_text_wrapper">
						<input type="text" class="cmb2-text-medium" name="wcpscu[sold_out_ribbon_text]"
							id="wcpscu_sold_out_ribbon_text"
							value="<?php echo ! empty( $sold_out_ribbon_text ) ? esc_attr( $sold_out_ribbon_text ) : esc_html__('Sold Out!', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?>"
							placeholder="<?php esc_attr_e( 'e.g. Sold Out!', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?>">
						<p class="cmb2-metabox-description">
							<?php esc_html_e('Enter the text for the sold out ribbon. Default text is "Sold Out!".', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?>
						</p>
					</div>

					<!--Position of Featured ribbon -->
					<div id="sold_out_ribbon_text_wrapper">
						<label for="sold_out_ribbon_position">
								<?php esc_html_e( 'Position', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?>
						</label>
						<select id="sold_out_ribbon_position" class="sold_out_ribbon_position" name="wcpscu[sold_out_ribbon_position]">
							<option value="top_left" <?php selected( $sold_out_ribbon_position, 'top_left'); ?> ><?php esc_html_e( 'Top Left', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
							<option value="top_right" <?php selected( $sold_out_ribbon_position, 'top_right'); ?>><?php esc_html_e( 'Top Right', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
							<option value="bottom_left" <?php selected( $sold_out_ribbon_position, 'bottom_left'); ?>><?php esc_html_e( 'Bottom Left', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
							<option value="bottom_right" <?php selected( $sold_out_ribbon_position, 'bottom_right'); ?>><?php esc_html_e( 'Bottom Right', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?>
							</option>
						</select>
					</div>

				</div>
			</div>

			<!--Show ribbon for sold out products-->
			<div class="cmb-row cmb-type-radio" id="discount_ribbon_wrapper">
				<div class="cmb-th">
					<label
						for="wcpscu_display_discount_ribbon"><?php esc_html_e('Display the "Discount" Badge', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
				</div>
				<div class="cmb-td">
					<ul class="cmb2-radio-list cmb2-list cmb2-radio-switch">
						<li><input type="radio" class="cmb2-option cmb2-radio-switch1"
								name="wcpscu[display_discount_ribbon]" id="wcpscu_display_discount_ribbon1" value="yes"
								<?php checked( $display_discount_ribbon, 'yes' ); ?>>
							<label
								for="wcpscu_display_discount_ribbon1"><?php esc_html_e('Yes', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
						<li><input type="radio" class="cmb2-option cmb2-radio-switch2"
								name="wcpscu[display_discount_ribbon]" id="wcpscu_display_discount_ribbon2" value="no"
								<?php checked( $display_discount_ribbon, 'no' ); ?>>
							<label
								for="wcpscu_display_discount_ribbon2"><?php esc_html_e('No', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
					</ul>
					<!-- <p class="cmb2-metabox-description">
						<?php esc_html_e('Whether to display the "Sold Out" ribbon for out-of-stock products or not', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?>
					</p> -->

					<!--Position of Featured ribbon -->
					<div id="discount_ribbon_text_wrapper">
						<label for="discount_ribbon_position">
								<?php esc_html_e( 'Position', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?>
						</label>
						<select id="discount_ribbon_position" class="discount_ribbon_position" name="wcpscu[discount_ribbon_position]">
							<option value="top_left" <?php selected( $discount_ribbon_position, 'top_left'); ?> ><?php esc_html_e( 'Top Left', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
							<option value="top_right" <?php selected( $discount_ribbon_position, 'top_right'); ?>><?php esc_html_e( 'Top Right', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
							<option value="bottom_left" <?php selected( $discount_ribbon_position, 'bottom_left'); ?>><?php esc_html_e( 'Bottom Left', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
							<option value="bottom_right" <?php selected( $discount_ribbon_position, 'bottom_right'); ?>><?php esc_html_e( 'Bottom Right', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?>
							</option>
						</select>
					</div>

				</div>
			</div>

			<!--Display Product Price-->
			<div class="cmb-row cmb-type-radio">
				<div class="cmb-th">
					<label
						for="wcpscu_display_price"><?php esc_html_e('Display Product Price', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
				</div>
				<div class="cmb-td">
					<ul class="cmb2-radio-list cmb2-list cmb2-radio-switch">
						<li><input type="radio" class="cmb2-option cmb2-radio-switch1" name="wcpscu[display_price]"
								id="wcpscu_display_price1" value="yes"
								<?php if(empty($display_price) || 'yes' === $display_price) { echo 'checked'; } ?>>
							<label for="wcpscu_display_price1"><?php esc_html_e('Yes', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
						<li><input type="radio" class="cmb2-option cmb2-radio-switch2" name="wcpscu[display_price]"
								id="wcpscu_display_price2" value="no"
								<?php if ( ! empty( $display_price ) ) { checked( 'no', $display_price ) ; } ?>>
							<label for="wcpscu_display_price2"><?php esc_html_e('No', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
					</ul>
				</div>
			</div>

			<!--Display Product Ratings-->
			<div class="cmb-row cmb-type-radio">
				<div class="cmb-th">
					<label
						for="wcpscu_display_ratings"><?php esc_html_e('Display Product Ratings', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
				</div>
				<div class="cmb-td">
					<ul class="cmb2-radio-list cmb2-list cmb2-radio-switch">
						<li>
							<input type="radio" class="cmb2-option cmb2-radio-switch1" name="wcpscu[display_ratings]"
								id="wcpscu_display_ratings1" value="yes"
								<?php if(empty($display_ratings) || 'yes' === $display_ratings) { echo 'checked'; } ?>>
							<label for="wcpscu_display_ratings1"><?php esc_html_e('Yes', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
						<li><input type="radio" class="cmb2-option cmb2-radio-switch2" name="wcpscu[display_ratings]"
								id="wcpscu_display_ratings2" value="no"
								<?php if (!empty($display_ratings)) { checked('no', $display_ratings ); } ?>>
							<label for="wcpscu_display_ratings2"><?php _e('No', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
					</ul>
				</div>
			</div>

			<!--Display "Add to Cart" button-->
			<div class="cmb-row cmb-type-radio">
				<div class="cmb-th">
					<label
						for="wcpscu_display_cart"><?php esc_html_e('Display "Add to Cart" Button', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
				</div>
				<div class="cmb-td">
					<ul class="cmb2-radio-list cmb2-list cmb2-radio-switch">
						<li>
							<input type="radio" class="cmb2-option cmb2-radio-switch1" name="wcpscu[display_cart]"
								id="wcpscu_display_cart1" value="yes"
								<?php if(empty($display_cart) || 'yes' === $display_cart) { echo 'checked'; } ?>>
							<label for="wcpscu_display_cart1"><?php esc_html_e('Yes', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
						<li>
							<input type="radio" class="cmb2-option cmb2-radio-switch2" name="wcpscu[display_cart]"
								id="wcpscu_display_cart2" value="no"
								<?php if( ! empty( $display_cart ) ) { checked('no', $display_cart ); } ?>>
							<label for="wcpscu_display_cart2"><?php esc_html_e('No', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
					</ul>
				</div>
			</div>
			<!--Display "Quick View" button-->
			<div class="cmb-row cmb-type-radio theme_2">
				<div class="cmb-th">
					<label
						for="wcpscu_quick_view"><?php esc_html_e('Display "Quick View Icon"', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
				</div>
				<div class="cmb-td">
					<ul class="cmb2-radio-list cmb2-list cmb2-radio-switch">
						<li>
							<input type="radio" class="cmb2-option cmb2-radio-switch1" name="wcpscu[quick_view]"
								id="wcpscu_quick_view1" value="yes"
								<?php if(empty($quick_view) || 'yes' === $quick_view) { echo 'checked'; } ?>>
							<label for="wcpscu_quick_view1"><?php esc_html_e('Yes', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
						<li>
							<input type="radio" class="cmb2-option cmb2-radio-switch2" name="wcpscu[quick_view]"
								id="wcpscu_quick_view2" value="no"
								<?php if( ! empty( $quick_view ) ) { checked( 'no', $quick_view ); } ?>>
							<label for="wcpscu_quick_view2"><?php esc_html_e('No', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
					</ul>
				</div>
			</div>

			<!--Enable image cropping and resizing-->
			<div class="cmb-row cmb-type-radio">
				<div class="cmb-th">
					<label for="wcpscu_img_crop"><?php esc_html_e('Image Resize & Crop', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
				</div>
				<div class="cmb-td">
					<ul class="cmb2-radio-list cmb2-list cmb2-radio-switch">
						<li><input type="radio" class="cmb2-option cmb2-radio-switch1" name="wcpscu[img_crop]"
								id="wcpscu_img_crop1" value="yes"
								<?php if( empty( $img_crop ) || $img_crop === "yes" ) { echo "checked"; } ?>>
							<label for="wcpscu_img_crop1"><?php esc_html_e('Yes', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
						<li><input type="radio" class="cmb2-option cmb2-radio-switch2" name="wcpscu[img_crop]"
								id="wcpscu_img_crop2" value="no"
								<?php if( ! empty( $img_crop ) ) { checked( 'no', $img_crop ); } ?>> <label
								for="wcpscu_img_crop2"><?php esc_html_e('No', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label></li>
					</ul>
					<p class="cmb2-metabox-description">
						<?php esc_html_e('If the product images are not in the same size, this feature is helpful. It automatically resizes and crops.', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?>
					</p>
					<div class="cmb2-metabox-note">
						<p><?php esc_html_e( 'Note: your image must be higher than/equal to the cropping size set below.
							Otherwise, you may need to enable image upscaling feature from the settings below.', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></p>
					</div>
				</div>
			</div>

			<!--Image width-->
			<div class="cmb-row cmb-type-text-medium">
				<div class="cmb-th">
					<label for="wcpscu_crop_image_width"><?php esc_html_e('Image Size', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
				</div>
				<div class="cmb-td">
					<div class="cmb-td-sizes">
						<div class="cmb-td-size cmb-td-size-width">
							<label
								for="wcpscu_crop_image_width"><?php esc_html_e('Width', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
							<input type="text" class="cmb2-text-small" name="wcpscu[crop_image_width]"
								id="wcpscu_crop_image_width"
								value="<?php echo esc_attr( ! empty( $crop_image_width ) ? intval( $crop_image_width ) : 350 ); ?>">
							<p class="cmb2-metabox-description">
								<?php esc_html_e('Image cropping width', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?>
							</p>
						</div>
						<div class="cmb-td-size cmb-td-size-height">
							<label
								for="wcpscu_crop_image_height"><?php esc_html_e('Height', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
							<input type="text" class="cmb2-text-small" name="wcpscu[crop_image_height]"
								id="wcpscu_crop_image_height"
								value="<?php echo esc_attr( ! empty( $crop_image_height ) ? intval( $crop_image_height ) : 250 ); ?>">
							<p class="cmb2-metabox-description">
								<?php esc_html_e('Image cropping height', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div> <!-- end cmb2-metabox -->
</div> <!-- end cmb2-wrap -->
</div> <!-- end lcsp-tab-1 -->
