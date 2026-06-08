<?php
/**
 * Single Payment page - Payment details template for single and subscription data.
 *
 * @since 1.8.2
 *
 * @var array  $entry_fields   Entry object.
 * @var array  $form_data      Form data.
 * @var int    $entry_id       Entry ID.
 * @var string $entry_id_title Entry title id.
 * @var string $entry_url      Entry page URL.
 * @var string $entry_status   Entry status.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="wpforms-payment-entry-fields" class="postbox">

	<div class="postbox-header">
		<h2 class="hndle">
			<span><?php echo esc_html__( 'Entry Summary', 'wpforms-lite' ); ?></span>
			<?php if ( ! empty( $entry_id_title ) ) : ?>
			<span class="wpforms-payment-entry-id"><?php echo esc_html( $entry_id_title ); ?></span>
			<?php endif; ?>
		</h2>
	</div>

	<div class="inside">
		<?php
		foreach ( $entry_fields as $key => $field ) {

			$field_type = $field['type'];

			if ( in_array( $field_type, [ 'repeater', 'layout' ], true ) && wpforms()->is_pro() ) {
				if ( $field_type === 'repeater' ) {
					echo wpforms_render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'admin/payments/single/repeater',
						[
							'field'        => $field,
							'form_data'    => $form_data,
							'entry_fields' => $entry_fields,
						],
						true
					);
				}

				if ( $field_type === 'layout' ) {
					echo wpforms_render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'admin/payments/single/layout',
						[
							'field'        => $field,
							'form_data'    => $form_data,
							'entry_fields' => $entry_fields,
						],
						true
					);
				}
			} else {
				echo wpforms_render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'admin/payments/single/field',
					[
						'field' => $field,
					],
					true
				);
			}
		}
		?>
	</div>

	<?php if ( $entry_id_title && $entry_status !== 'trash' ) : ?>
		<div class="wpforms-payment-actions">
			<div class="status"></div>
			<div class="actions">
				<a class="button" href="<?php echo esc_url( $entry_url ); ?>">
					<?php echo esc_html__( 'View Entry', 'wpforms-lite' ); ?>
				</a>
				<div class="clear"></div>
			</div>
		</div>
	<?php endif; ?>
</div>
