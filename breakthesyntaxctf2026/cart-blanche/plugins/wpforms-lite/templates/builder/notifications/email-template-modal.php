<?php
/**
 * Email Template modal content.
 *
 * This template is used for rendering the email template modal content
 * and is injected into the DOM via JS. The JS backbone template is used to render loop iterations.
 *
 * @since 1.8.5
 *
 * @var string $pro_badge Pro badge HTML.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<script type="text/html" id="tmpl-wpforms-email-template-modal">
	<div class="wpforms-modal-header">
		<h1>
			<?php esc_html_e( 'Choose a Template', 'wpforms-lite' ); ?>
		</h1>
		<p>
			<?php esc_html_e( 'Browse through our collection of email notification templates.', 'wpforms-lite' ); ?>
		</p>
	</div>
	<div class="wpforms-email-template-modal-content wpforms-modal-content">
		<div class="wpforms-card-image-group">
			<div class="wpforms-setting-field">
				<# _.each( data.templates, function( template, key ) { #>
					<div class="wpforms-card-image<# if ( ! data.is_pro && template.is_pro ) { #> education-modal<# } #>"<# if ( ! data.is_pro && template.is_pro ) { #> data-name="<?php esc_attr_e( 'Email Templates', 'wpforms-lite' ); ?>" data-plural="1" data-action="upgrade"<# } #>>
						<input type="radio" name="wpforms-email-template-modal-choice" id="wpforms-email-template-modal-choice-{{ data.id }}-{{ key }}" value="{{ key }}"<# if ( key === data.selected ) { #> checked="checked"<# } #> />
						<label for="wpforms-email-template-modal-choice-{{ data.id }}-{{ key }}" class="option-{{ key }}">
							{{ template.name }}
							<# if ( ! data.is_pro && template.is_pro ) { #>
							<?php echo wp_kses( $pro_badge, [ 'span' => [ 'class' => [] ] ] ); ?>
							<# } #>
							<span class="wpforms-card-image-overlay">
								<span class="wpforms-btn-choose wpforms-btn wpforms-btn-md wpforms-btn-orange">
									<?php esc_html_e( 'Choose', 'wpforms-lite' ); ?>
								</span>
								<# if ( template.preview ) { #>
									<a href="{{{ template.preview }}}" target="_blank" class="wpforms-btn-preview wpforms-btn wpforms-btn-md wpforms-btn-light-grey">
										<?php esc_html_e( 'Preview', 'wpforms-lite' ); ?>
									</a>
								<# } #>
							</span>
						</label>
					</div>
				<# } ); #>
			</div>
		</div>
	</div>
</script>

<?php
/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
