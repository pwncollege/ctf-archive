<?php
/**
 * WPForms Builder Field Context Menu (right click) Template.
 *
 * @since 1.8.6
 */

use WPForms\Admin\Education\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="wpforms-context-menu" id="wpforms-field-context-menu">
	<ul class="wpforms-context-menu-list">
		<li class="wpforms-context-menu-list-item" data-action="edit">
			<span class="wpforms-context-menu-list-item-icon">
				<i class="fa fa-pencil-square-o"></i>
			</span>

			<span class="wpforms-context-menu-list-item-text">
				<?php esc_html_e( 'Edit', 'wpforms-lite' ); ?>
			</span>
		</li>

		<li class="wpforms-context-menu-list-item" data-action="duplicate">
			<span class="wpforms-context-menu-list-item-icon">
				<i class="fa fa-files-o"></i>
			</span>

			<span class="wpforms-context-menu-list-item-text">
				<?php esc_html_e( 'Duplicate', 'wpforms-lite' ); ?>
			</span>
		</li>

		<li class="wpforms-context-menu-list-item" data-action="delete">
			<span class="wpforms-context-menu-list-item-icon">
				<i class="fa fa-trash-o"></i>
			</span>

			<span class="wpforms-context-menu-list-item-text">
				<?php esc_html_e( 'Delete', 'wpforms-lite' ); ?>
			</span>
		</li>

		<li class="wpforms-context-menu-list-divider" data-visibility="required, label, field-size"></li>

		<li class="wpforms-context-menu-list-item wpforms-context-menu-list-item-selective" data-action="required">
			<span class="wpforms-context-menu-list-item-icon">
				<i class="fa fa-asterisk"></i>
			</span>

			<span class="wpforms-context-menu-list-item-text" data-active-text="<?php esc_html_e( 'Mark as Optional', 'wpforms-lite' ); ?>">
				<?php esc_html_e( 'Mark as Required', 'wpforms-lite' ); ?>
			</span>
		</li>

		<li class="wpforms-context-menu-list-item wpforms-context-menu-list-item-selective" data-action="label">
			<span class="wpforms-context-menu-list-item-icon">
				<i class="fa fa-tag"></i>
			</span>

			<span class="wpforms-context-menu-list-item-text" data-active-text="<?php esc_html_e( 'Show Label', 'wpforms-lite' ); ?>">
				<?php esc_html_e( 'Hide Label', 'wpforms-lite' ); ?>
			</span>
		</li>

		<li class="wpforms-context-menu-list-item wpforms-context-menu-list-item-has-child" data-action="field-size">
			<span class="wpforms-context-menu-list-item-icon">
				<i class="fa fa-arrows-h"></i>
			</span>

			<span class="wpforms-context-menu-list-item-text">
				<?php esc_html_e( 'Field Size', 'wpforms-lite' ); ?>
			</span>

			<ul class="wpforms-context-menu-list wpforms-context-menu-list-selective">
				<li class="wpforms-context-menu-list-item wpforms-context-menu-list-item-selective" data-action="field-size" data-value="small">
					<span class="wpforms-context-menu-list-item-icon">
						<i class="fa fa-check"></i>
					</span>

					<span class="wpforms-context-menu-list-item-text">
						<?php esc_html_e( 'Small', 'wpforms-lite' ); ?>
					</span>
				</li>

				<li class="wpforms-context-menu-list-item wpforms-context-menu-list-item-selective" data-action="field-size" data-value="medium">
					<span class="wpforms-context-menu-list-item-icon">
						<i class="fa fa-check"></i>
					</span>

					<span class="wpforms-context-menu-list-item-text">
						<?php esc_html_e( 'Medium', 'wpforms-lite' ); ?>
					</span>
				</li>

				<li class="wpforms-context-menu-list-item wpforms-context-menu-list-item-selective" data-action="field-size" data-value="large">
					<span class="wpforms-context-menu-list-item-icon">
						<i class="fa fa-check"></i>
					</span>

					<span class="wpforms-context-menu-list-item-text">
						<?php esc_html_e( 'Large', 'wpforms-lite' ); ?>
					</span>
				</li>
			</ul>
		</li>

		<li class="wpforms-context-menu-list-divider" data-visibility="smart-logic"></li>

		<li class="wpforms-context-menu-list-item" data-action="smart-logic">
			<span class="wpforms-context-menu-list-item-icon">
				<i class="fa fa-random"></i>
			</span>

			<span class="wpforms-context-menu-list-item-text">
				<?php esc_html_e( 'Edit Smart Logic', 'wpforms-lite' ); ?>
			</span>

			<?php if ( ! wpforms()->is_pro() ) : ?>
				<?php Helpers::print_badge( 'Pro', 'sm', 'inline', 'stone' ); ?>
			<?php endif; ?>
		</li>
	</ul>
</div>
