<?php
/**
 * Stripe Settings - Custom Metadata table template.
 *
 * @since 1.9.6
 *
 * @var array  $custom_metadata Saved Metadata.
 * @var string $subsection      Current subsection.
 * @var string $slug            Field slug.
 * @var array  $form_data       Form data.
 * @var array  $fields          Allowed fields.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="wpforms-panel-field-stripe-custom-metadata">

	<p>
		<?php esc_html_e( 'Custom Meta', 'wpforms-lite' ); ?>
		<i class="fa fa-question-circle-o wpforms-help-tooltip" title="<?php esc_html_e( 'Map custom meta to form field values.', 'wpforms-lite' ); ?>"></i>
	</p>

	<table class="wpforms-panel-content-section-stripe-custom-metadata-table">
		<thead>
		<tr>
			<th>
				<?php esc_html_e( 'Object Type', 'wpforms-lite' ); ?>
			</th>
			<th>
				<?php esc_html_e( 'Meta Key', 'wpforms-lite' ); ?>
			</th>
			<th colspan="3">
				<?php esc_html_e( 'Meta Value', 'wpforms-lite' ); ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ( $custom_metadata as $key => $value ) :
			$is_hidden            = ! $key ? 'hidden' : '';
			$is_meta_key_valid    = ! empty( $value['meta_key'] ) || empty( $value['object_type'] ) || empty( $value['meta_value'] );
			$meta_key_input_class = [
				'wpforms-panel-field-stripe-custom-metadata-meta-key',
				$is_meta_key_valid ? '' : 'wpforms-required-field-error',
			];
			?>
			<tr data-key="<?php echo esc_attr( $key ); ?>">
				<td>
					<?php
					wpforms_panel_field(
						'select',
						$slug,
						'object_type',
						$form_data,
						'',
						[
							'parent'      => 'payments',
							'subsection'  => $subsection,
							'index'       => $key,
							'placeholder' => esc_html__( '--- Select Object Type ---', 'wpforms-lite' ),
							'options'     => [
								'customer' => esc_html__( 'Customer', 'wpforms-lite' ),
								'payment'  => esc_html__( 'Payment', 'wpforms-lite' ),
							],
							'input_class' => 'wpforms-panel-field-stripe-custom-metadata-object-type',
						]
					);
					?>
				</td>
				<td>
					<?php
					wpforms_panel_field(
						'text',
						$slug,
						'meta_key',
						$form_data,
						'',
						[
							'parent'      => 'payments',
							'subsection'  => $subsection,
							'index'       => $key,
							'input_class' => implode( ' ', $meta_key_input_class ),
						]
					);
					?>
				</td>
				<td>
					<?php
					wpforms_panel_field(
						'select',
						$slug,
						'meta_value',
						$form_data,
						'',
						[
							'parent'      => 'payments',
							'subsection'  => $subsection,
							'index'       => $key,
							'field_map'   => $fields,
							'placeholder' => esc_html__( '--- Select Meta Value ---', 'wpforms-lite' ),
							'input_class' => 'wpforms-panel-field-stripe-custom-metadata-meta-value',
						]
					);
					?>
				</td>
				<td class="add">
					<button class="button-secondary wpforms-panel-content-section-stripe-custom-metadata-add" title="<?php esc_attr_e( 'Add Another', 'wpforms-lite' ); ?>">
						<i class="fa fa-plus-circle"></i>
					</button>
				</td>
				<td class="delete">
					<button class="button-secondary wpforms-panel-content-section-stripe-custom-metadata-delete <?php echo esc_attr( $is_hidden ); ?>" title="<?php esc_attr_e( 'Remove', 'wpforms-lite' ); ?>">
						<i class="fa fa-minus-circle"></i>
					</button>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
