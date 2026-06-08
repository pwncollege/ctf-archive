<?php

namespace WPMailSMTP\Helpers;

/**
 * Reusable interface components.
 *
 * @since 3.10.0
 */
class UI {

	/**
	 * Toggle component.
	 *
	 * @since 3.10.0
	 *
	 * @param array $args {
	 *     Toggle parameters.
	 *
	 *     @type string          $name     Name attribute of the toggle's input element. Default ''.
	 *     @type string          $value    Value attribute of the toggle's input element. Default 'yes'.
	 *     @type string|string[] $label    Single label, or a 2-elements array of on/off labels. Default ''.
	 *     @type string          $id       ID attribute of the toggle's container element. Default ''.
	 *     @type string          $class    Class attribute of the toggle's container element. Default ''.
	 *     @type bool            $checked  Checked attribute of the toggle's input element. Default false.
	 *     @type bool            $disabled Disabled attribute of the toggle's input element. Default false.
	 * }
	 */
	public static function toggle( $args = [] ) {

		$args = wp_parse_args(
			$args,
			[
				'name'     => '',
				'value'    => 'yes',
				'label'    => [
					esc_html__( 'On', 'wp-mail-smtp' ),
					esc_html__( 'Off', 'wp-mail-smtp' ),
				],
				'id'       => '',
				'class'    => '',
				'checked'  => false,
				'disabled' => false,
			]
		);
		?>
		<label class="wp-mail-smtp-toggle">
			<input type="checkbox"
				   name="<?php echo esc_attr( $args['name'] ); ?>"
				   <?php echo empty( $args['class'] ) ? '' : ' class="' . esc_attr( $args['class'] ) . '"'; ?>
				   <?php echo empty( $args['id'] ) ? '' : ' id="' . esc_attr( $args['id'] ) . '"'; ?>
				   value="<?php echo esc_attr( $args['value'] ); ?>"
				   <?php checked( (bool) $args['checked'] ); ?>
				   <?php disabled( (bool) $args['disabled'] ); ?> />
			<span class="wp-mail-smtp-toggle__switch"></span>
			<?php if ( is_array( $args['label'] ) ) : ?>
				<?php if ( count( $args['label'] ) > 0 ) : ?>
					<span class="wp-mail-smtp-toggle__label wp-mail-smtp-toggle__label--checked"><?php echo esc_html( $args['label'][0] ); ?></span>
				<?php endif; ?>
				<?php if ( count( $args['label'] ) > 1 ) : ?>
					<span class="wp-mail-smtp-toggle__label wp-mail-smtp-toggle__label--unchecked"><?php echo esc_html( $args['label'][1] ); ?></span>
				<?php endif; ?>
			<?php else : ?>
				<span class="wp-mail-smtp-toggle__label wp-mail-smtp-toggle__label--static"><?php echo esc_html( $args['label'] ); ?></span>
			<?php endif; ?>
		</label>
		<?php
	}

	/**
	 * Output an obfuscated password field.
	 *
	 * @since 4.1.0
	 *
	 * @param array $args Field attributes.
	 *
	 * @return void
	 */
	public static function hidden_password_field( $args ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		$args = wp_parse_args(
			$args,
			[
				'name'       => '',
				'id'         => '',
				'value'      => '',
				'clear_text' => esc_html__( 'Remove', 'wp-mail-smtp' ),
				'clear_url'  => '',
			]
		);

		$value = str_repeat( '*', strlen( $args['value'] ) );

		// phpcs:disable Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace
		?>

		<div class="wp-mail-smtp-input-btn-row">

			<input type="password"
				   spellcheck="false"
				   autocomplete="new-password"
				   <?php if ( ! empty( $value ) ) : ?>disabled<?php endif; ?>
				   <?php if ( ! empty( $args['name'] && empty( $value ) ) ) : ?>name="<?php echo esc_attr( $args['name'] ); ?>"<?php endif; ?>
				   <?php if ( ! empty( $args['name'] ) ) : ?>data-name="<?php echo esc_attr( $args['name'] ); ?>"<?php endif; ?>
				   <?php if ( ! empty( $args['id'] ) ) : ?>id="<?php echo esc_attr( $args['id'] ); ?>"<?php endif; ?>
				   <?php if ( ! empty( $value ) ) : ?>value="<?php echo esc_attr( $value ); ?>"<?php endif; ?>/>

			<?php if ( ! empty( $value ) && ! empty( $args['clear_url'] ) ) : ?>
				<a href="<?php echo esc_url( $args['clear_url'] ); ?>"
				   class="wp-mail-smtp-btn wp-mail-smtp-btn-md wp-mail-smtp-btn-grey"><?php echo esc_html( $args['clear_text'] ); ?></a>
			<?php elseif ( ! empty( $value ) ) : ?>
				<button type="button"
						class="wp-mail-smtp-btn wp-mail-smtp-btn-md wp-mail-smtp-btn-grey"
						data-clear-field="<?php echo esc_attr( $args['id'] ); ?>"><?php echo esc_html( $args['clear_text'] ); ?></button>
			<?php endif; ?>
		</div>
		<?php
		// phpcs:enable Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace
	}
}
