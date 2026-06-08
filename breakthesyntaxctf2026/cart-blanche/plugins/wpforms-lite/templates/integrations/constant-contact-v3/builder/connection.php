<?php
/**
 * Render a connection.
 *
 * @since 1.9.3
 *
 * @var string $slug Provider slug.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wpforms-builder-provider-connection" data-connection_id="{{ data.connection.id }}">
	<input type="hidden" class="wpforms-builder-provider-connection-id"
		name="providers[{{ data.provider }}][{{ data.connection.id }}][id]"
		value="{{ data.connection.id }}">

	<div class="wpforms-builder-provider-connection-title">
		{{ data.connection.name }}
		<button class="wpforms-builder-provider-connection-delete js-wpforms-builder-provider-connection-delete" type="button">
			<i class="fa fa-trash-o"></i>
		</button>
		<input type="hidden"
			id="wpforms-builder-constant-contact-v3-provider-{{ data.connection.id }}-name"
			name="providers[{{ data.provider }}][{{ data.connection.id }}][name]"
			value="{{ data.connection.name }}">
	</div>

	<div class="wpforms-builder-provider-connection-block wpforms-builder-constant-contact-v3-provider-accounts">
		<h4><?php esc_html_e( 'Select Account', 'wpforms-lite' ); ?><span class="required">*</span></h4>

		<select class="js-wpforms-builder-constant-contact-v3-provider-connection-account wpforms-required" name="providers[{{ data.provider }}][{{ data.connection.id }}][account_id]"<# if ( _.isEmpty( data.accounts ) ) { #> disabled<# } #>>
			<option value="" selected disabled>--- <?php esc_html_e( 'Select Account', 'wpforms-lite' ); ?> ---</option>

			<# _.each( data.accounts, function( account, account_id ) { #>
				<option value="{{ account_id }}" data-option_id="{{ account['option_id'] }}"
				<# if ( account_id === data.connection.account_id ) { #> selected<# } #>>
					{{ account.label }}
				</option>
			<# } ); #>
		</select>
	</div>

	<div class="wpforms-builder-provider-connection-block wpforms-builder-constant-contact-v3-provider-actions">
		<h4><?php esc_html_e( 'Action To Perform', 'wpforms-lite' ); ?><span class="required">*</span></h4>

		<select class="js-wpforms-builder-constant-contact-v3-provider-connection-action wpforms-required"
			id="wpforms-builder-constant-contact-v3-provider-{{ data.connection.id }}-action"
			<# if ( _.isEmpty( data.connection.account_id ) ) { #>disabled<# } #>
			name="providers[<?php echo esc_attr( $slug ); ?>][{{ data.connection.id }}][action]">

			<option value=""<# if ( _.isEmpty( data.connection.action ) ) { #> selected<# } #>>
				<?php esc_html_e( '--- Select Action ---', 'wpforms-lite' ); ?>
			</option>

			<# _.each( data.actions, function( label, name ) { #>
				<option value="{{ name }}"<# if ( name === data.connection.action ) { #> selected<# } #>>
					{{ label }}
				</option>
			<# } ); #>
		</select>
	</div>

	<!-- Here is where sub-templates will put its compiled HTML. -->
	<div class="wpforms-builder-constant-contact-v3-provider-actions-data" style="margin-bottom: 20px;"></div>

	{{{ data.conditional }}}
</div>
