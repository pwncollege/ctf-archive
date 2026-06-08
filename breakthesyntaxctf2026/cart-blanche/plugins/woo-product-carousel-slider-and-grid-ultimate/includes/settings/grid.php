<?php
/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$grid_pagination = ! empty( $grid_pagination ) ? $grid_pagination : 'no';
$g_column        = ! empty( $g_column ) ? $g_column : '4';
$g_tablet        = ! empty( $g_tablet ) ? $g_tablet : '2';
$g_mobile        = ! empty( $g_mobile ) ? $g_mobile : '1';
?>
<!--TAB 3  Grid setting -->
<div id="lcsp-tab-3" class="lcsp-tab-content">
	<div class="cmb2-wrap form-table">
		<div id="cmb2-metabox" class="cmb2-metabox cmb-field-list">

			<!--Select Column for desktop-->
			<div class="cmb-row cmb-type-radio">
				<div class="cmb-th">
					<label for="lcsp_ap"><?php esc_html_e('Select Columns', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
				</div>
				<div class="cmb-td">
					<select id="g_theme" name="wcpscu[g_column]">
						<option value="1" <?php selected( '1', $g_column ) ?>><?php esc_html_e( 'Column-1', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
						<option value="2" <?php selected( '2', $g_column ) ?>><?php esc_html_e( 'Column-2', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
						<option value="3" <?php selected( '3', $g_column ) ?>><?php esc_html_e( 'Column-3', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
						<option value="4" <?php selected( '4', $g_column ) ?>><?php esc_html_e( 'Column-4', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
					</select>
				</div>
			</div>

			<!--Select Column for Tablet-->
			<div class="cmb-row cmb-type-radio">
				<div class="cmb-th">
					<label for="lcsp_ap"><?php esc_html_e('Select Columns for Tablet', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
				</div>
				<div class="cmb-td">
					<select id="g_theme" name="wcpscu[g_tablet]">
						<option value="1" <?php selected( '1', $g_tablet ) ?>><?php esc_html_e( 'Column-1', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
						<option value="2" <?php selected( '2', $g_tablet ) ?>><?php esc_html_e( 'Column-2', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
						<option value="3" <?php selected( '3', $g_tablet ) ?>><?php esc_html_e( 'Column-3', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
						<option value="4" <?php selected( '4', $g_tablet ) ?>><?php esc_html_e( 'Column-4', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
					</select>
				</div>
			</div>

			<!--Select Column for Mobile-->
			<div class="cmb-row cmb-type-radio">
				<div class="cmb-th">
					<label for="lcsp_ap"><?php esc_html_e('Select Columns for Mobile', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
				</div>
				<div class="cmb-td">
					<select id="g_theme" name="wcpscu[g_mobile]">
						<option value="1" <?php selected( '1', $g_mobile ) ?>><?php esc_html_e( 'Column-1', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
						<option value="2" <?php selected( '2', $g_mobile ) ?>><?php esc_html_e( 'Column-2', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
						<option value="3" <?php selected( '3', $g_mobile ) ?>><?php esc_html_e( 'Column-3', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
						<option value="4" <?php selected( '4', $g_mobile ) ?>><?php esc_html_e( 'Column-4', 'woocommerce-product-carousel-slider-and-grid-ultimate' ); ?></option>
					</select>
				</div>
			</div>

			<!--Pagination-->
			<div class="cmb-row cmb-type-radio">
				<div class="cmb-th">
					<label for="lcsp_ap"><?php esc_html_e('Display Pagination', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
				</div>
				<div class="cmb-td">
					<ul class="cmb2-radio-list cmb2-list">
						<li>
							<input type="radio" class="cmb2-option"
								   name="wcpscu[grid_pagination]"
								   id="grid_pagination"
								   value="yes" <?php checked( 'yes', $grid_pagination ); ?>>
							<label for="grid_pagination"><?php esc_html_e('Yes', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
						<li>
							<input type="radio" class="cmb2-option"
								   name="wcpscu[grid_pagination]"
								   id="grid_pagination2"
								   value="no" <?php checked( 'no', $grid_pagination ); ?>>
							<label for="grid_pagination2"><?php esc_html_e('No', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?></label>
						</li>
					</ul>
				</div>
			</div>

			<!-- pagination color -->
			<div class="cmb-row cmb-type-radio">
				<div class="cmb-th">
					<label for="wcpscu[pagi_color]">
						<?php esc_html_e('Pagination Style', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?>
					</label>
				</div>
				<div class="cmb-td">
					<div class="cmb-pagination">
						<div class="cmb-pagination-item">
							<label for="wcpscu[pagi_color]">
								<?php esc_html_e('Pagination Color', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?>
							</label>
							<input type="text" name="wcpscu[pagi_color]" class="cpa-color-picker"
								value="<?php echo esc_attr( ! empty( $pagi_color ) ? $pagi_color : "#333" ); ?>" />
						</div>
						<div class="cmb-pagination-item">
							<label for="wcpscu[pagi_border_color]">
								<?php esc_html_e('Pagination Border Color', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?>
							</label>
							<input type="text" name="wcpscu[pagi_border_color]" class="cpa-color-picker"
								value="<?php echo esc_attr( ! empty( $pagi_border_color ) ? $pagi_border_color : "#e4e4e4" ); ?>" />
						</div>
						<div class="cmb-pagination-item">
							<label for="wcpscu[pagi_back_color]">
								<?php esc_html_e('Pagination Background Color', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?>
							</label>
							<input type="text" name="wcpscu[pagi_back_color]" class="cpa-color-picker"
								value="<?php echo esc_attr( ! empty( $pagi_back_color ) ? $pagi_back_color : "#fff" ); ?>" />
						</div>
						<div class="cmb-pagination-item">
							<label for="wcpscu[pagi_hover_color]">
								<?php esc_html_e('Pagination Hover Color', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?>
							</label>
							<input type="text" name="wcpscu[pagi_hover_color]" class="cpa-color-picker"
								value="<?php echo esc_attr( ! empty( $pagi_hover_color ) ? $pagi_hover_color : "#fff" ); ?>" />
						</div>
						<div class="cmb-pagination-item">
							<label for="wcpscu[pagi_hover_border_color]">
								<?php esc_html_e('Pagination Hover Border Color', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?>
							</label>
							<input type="text" name="wcpscu[pagi_hover_border_color]" class="cpa-color-picker"
								value="<?php echo esc_attr( ! empty( $pagi_hover_border_color ) ? $pagi_hover_border_color : "#ff5500" ); ?>" />
						</div>
						<div class="cmb-pagination-item">
							<label for="wcpscu[pagi_hover_back_color]">
								<?php esc_html_e('Pagination Hover Background Color', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?>
							</label>
							<input type="text" name="wcpscu[pagi_hover_back_color]" class="cpa-color-picker"
								value="<?php echo esc_attr( ! empty( $pagi_hover_back_color ) ? $pagi_hover_back_color : "#ff5500" ); ?>" />
						</div>
						<div class="cmb-pagination-item">
							<label for="wcpscu[pagi_active_color]">
								<?php esc_html_e('Pagination Active Color', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?>
							</label>
							<input type="text" name="wcpscu[pagi_active_color]" class="cpa-color-picker"
								value="<?php echo esc_attr( ! empty( $pagi_active_color ) ? $pagi_active_color : "#fff" ); ?>" />
						</div>
						<div class="cmb-pagination-item">
							<label for="wcpscu[pagi_active_border_color]">
								<?php esc_html_e('Pagination Active Border Color', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?>
							</label>
							<input type="text" name="wcpscu[pagi_active_border_color]" class="cpa-color-picker"
								value="<?php echo esc_attr( ! empty( $pagi_active_border_color ) ? $pagi_active_border_color : "#ff5500" ); ?>" />
						</div>
						<div class="cmb-pagination-item">
							<label for="wcpscu[pagi_active_back_color]">
								<?php esc_html_e('Pagination Active Background Color', 'woocommerce-product-carousel-slider-and-grid-ultimate'); ?>
							</label>
							<input type="text" name="wcpscu[pagi_active_back_color]" class="cpa-color-picker"
								value="<?php echo esc_attr( ! empty( $pagi_active_back_color ) ?  $pagi_active_back_color : "#ff5500" ); ?>" />
						</div>
					</div>
				</div>
			</div>
		</div> <!-- end cmb2-metabox -->
	</div> <!-- end cmb2-wrap -->
</div> <!-- end lcsp-tab-2
