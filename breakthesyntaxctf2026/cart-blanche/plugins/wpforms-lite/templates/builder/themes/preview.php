<?php
/**
 * Form Builder themes preview panel template.
 *
 * @since 1.9.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div id="wpforms-builder-themes-preview">
	<div class="wpforms-container wpforms-container-full">
		<div id="builder-themes-form-preview-wrapper">
			<form>
				<div class="wpforms-field-container">
					<div id="builder-themes-preview-default-name-container" class="wpforms-field wpforms-field-name">
						<fieldset>
							<legend class="wpforms-field-label"><?php esc_html_e( 'Name', 'wpforms-lite' ); ?></legend>
							<div class="wpforms-field-row wpforms-field-medium">
								<div class="wpforms-field-row-block wpforms-first wpforms-one-half">
									<input type="text" id="builder-themes-preview-default-name-first" class="wpforms-field-name-first">
									<label for="builder-themes-preview-default-name-first" class="wpforms-field-sublabel after"><?php esc_html_e( 'First Name', 'wpforms-lite' ); ?></label>
								</div>
								<div class="wpforms-field-row-block wpforms-one-half">
									<input type="text" id="builder-themes-preview-default-name-last" class="wpforms-field-name-last">
									<label for="builder-themes-preview-default-name-last" class="wpforms-field-sublabel after"><?php esc_html_e( 'Last Name', 'wpforms-lite' ); ?></label>
								</div>
							</div>
						</fieldset>
					</div>
					<div id="builder-themes-preview-default-email-container" class="wpforms-field wpforms-field-email">
						<label class="wpforms-field-label" for="builder-themes-preview-default-email"><?php esc_html_e( 'Email', 'wpforms-lite' ); ?></label>
						<input type="email" id="builder-themes-preview-default-email" class="wpforms-field-medium">
					</div>
					<div id="builder-themes-preview-default-message-container" class="wpforms-field wpforms-field-textarea">
						<label class="wpforms-field-label" for="builder-themes-preview-default-message"><?php esc_html_e( 'Message', 'wpforms-lite' ); ?></label>
						<textarea id="builder-themes-preview-default-message" class="wpforms-field-medium"></textarea>
					</div>
					<div id="builder-themes-preview-default-multiple-choice-container" class="wpforms-field wpforms-field-radio">
						<fieldset>
							<legend class="wpforms-field-label"><?php esc_html_e( 'Multiple Choice', 'wpforms-lite' ); ?></legend>
							<ul id="builder-themes-preview-default-multiple-choice">
								<li class="wpforms-selected">
									<input type="radio" id="builder-themes-preview-default-multiple-choice-1" value="<?php esc_attr_e( 'First Choice', 'wpforms-lite' ); ?>" checked="checked">
									<label class="wpforms-field-label-inline" for="builder-themes-preview-default-multiple-choice-1"><?php esc_html_e( 'First Choice', 'wpforms-lite' ); ?></label>
								</li>
								<li>
									<input type="radio" id="builder-themes-preview-default-multiple-choice-2" value="<?php esc_attr_e( 'Second Choice', 'wpforms-lite' ); ?>">
									<label class="wpforms-field-label-inline" for="builder-themes-preview-default-multiple-choice-2"><?php esc_html_e( 'Second Choice', 'wpforms-lite' ); ?></label>
								</li>
								<li>
									<input type="radio" id="builder-themes-preview-default-multiple-choice-3" value="<?php esc_attr_e( 'Third Choice', 'wpforms-lite' ); ?>">
									<label class="wpforms-field-label-inline" for="builder-themes-preview-default-multiple-choice-3"><?php esc_html_e( 'Third Choice', 'wpforms-lite' ); ?></label>
								</li>
							</ul>
						</fieldset>
					</div>
					<div id="builder-themes-preview-default-checkboxes-container" class="wpforms-field wpforms-field-checkbox">
						<fieldset>
							<legend class="wpforms-field-label"><?php esc_html_e( 'Checkboxes', 'wpforms-lite' ); ?></legend>
							<ul id="builder-themes-preview-default-checkboxes">
								<li class="wpforms-selected">
									<input type="checkbox" id="builder-themes-preview-default-checkboxes-1" value="<?php esc_attr_e( 'First Choice', 'wpforms-lite' ); ?>" checked="checked">
									<label class="wpforms-field-label-inline" for="builder-themes-preview-default-checkboxes-1"><?php esc_html_e( 'First Choice', 'wpforms-lite' ); ?></label>
								</li>
								<li>
									<input type="checkbox" id="builder-themes-preview-default-checkboxes-2" value="<?php esc_attr_e( 'Second Choice', 'wpforms-lite' ); ?>">
									<label class="wpforms-field-label-inline" for="builder-themes-preview-default-checkboxes-2"><?php esc_html_e( 'Second Choice', 'wpforms-lite' ); ?></label>
								</li>
								<li>
									<input type="checkbox" id="builder-themes-preview-default-checkboxes-3" value="<?php esc_attr_e( 'Third Choice', 'wpforms-lite' ); ?>">
									<label class="wpforms-field-label-inline" for="builder-themes-preview-default-checkboxes-3"><?php esc_html_e( 'Third Choice', 'wpforms-lite' ); ?></label>
								</li>
							</ul>
						</fieldset>
					</div>
				</div><!-- .wpforms-field-container -->

				<div class="wpforms-submit-container">
					<button type="submit" class="wpforms-submit" value="wpforms-submit" onclick="return false;">
						<?php esc_html_e( 'Submit', 'wpforms-lite' ); ?>
					</button>
				</div>
			</form>
		</div>
	</div>
</div>
