<?php

namespace WPForms\Admin\Builder;

/**
 * Form Builder Keyboard Shortcuts modal content.
 *
 * @since 1.6.9
 */
class Shortcuts {

	/**
	 * Initialize class.
	 *
	 * @since 1.6.9
	 */
	public function init(): void {

		// Terminate initialization if not in the builder.
		if ( ! wpforms_is_admin_page( 'builder' ) ) {
			return;
		}

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.6.9
	 */
	private function hooks(): void {

		add_filter( 'wpforms_builder_strings', [ $this, 'builder_strings' ] );
		add_action( 'wpforms_admin_page', [ $this, 'output' ], 30 );
	}

	/**
	 * Get a shortcut list.
	 *
	 * @since 1.6.9
	 *
	 * @return array
	 */
	private function get_list(): array {

		return [
			'left'  => [
				'ctrl s' => __( 'Save Form', 'wpforms-lite' ),
				'ctrl p' => __( 'Preview Form', 'wpforms-lite' ),
				'ctrl b' => __( 'Embed Form', 'wpforms-lite' ),
				'ctrl f' => __( 'Search Fields', 'wpforms-lite' ),
				'ctrl c' => __( 'Copy Fields', 'wpforms-lite' ),
				'ctrl v' => __( 'Paste Fields', 'wpforms-lite' ),
				'd'      => __( 'Duplicate Fields', 'wpforms-lite' ),
			],
			'right' => [
				'ctrl z'       => __( 'Undo', 'wpforms-lite' ),
				'ctrl shift z' => __( 'Redo', 'wpforms-lite' ),
				'ctrl h'       => __( 'Open Help', 'wpforms-lite' ),
				'ctrl t'       => __( 'Toggle Sidebar', 'wpforms-lite' ), // It is 'alt s' on Windows/Linux, dynamically changed in the modal in admin-builder.js openKeyboardShortcutsModal().
				'ctrl e'       => __( 'View Entries', 'wpforms-lite' ),
				'ctrl q'       => __( 'Close Builder', 'wpforms-lite' ),
				'delete'       => __( 'Delete Fields', 'wpforms-lite' ),
			],
		];
	}

	/**
	 * Add Form builder strings.
	 *
	 * @since 1.6.9
	 *
	 * @param array|mixed $strings Form Builder strings.
	 *
	 * @return array
	 */
	public function builder_strings( $strings ): array {

		$strings = (array) $strings;

		$strings['shortcuts_modal_title'] = esc_html__( 'Keyboard Shortcuts', 'wpforms-lite' );
		$strings['shortcuts_modal_msg']   = esc_html__( 'Handy shortcuts for common actions in the builder.', 'wpforms-lite' );

		return $strings;
	}

	/**
	 * Generate and output shortcuts modal content as the wp.template.
	 *
	 * @since 1.6.9
	 */
	public function output(): void {

		echo '
		<script type="text/html" id="tmpl-wpforms-builder-keyboard-shortcuts">
			<div class="wpforms-columns wpforms-columns-2">';

			foreach ( $this->get_list() as $list ) {

				echo "<ul class='wpforms-column'>";

				foreach ( $list as $key => $label ) {

					$key_parts = explode( ' ', $key );

					if ( count( $key_parts ) > 1 ) {
						printf(
							'<li>
								%1$s
								<span class="shortcut-key shortcut-key-%2$s">
									<i>%3$s</i><i>%4$s</i><i>%5$s</i>
								</span>
							</li>',
							esc_html( $label ),
							esc_html( str_replace( ' ', '-', $key ) ),
							esc_html( $key_parts[0] ),
							esc_html( $key_parts[1] ?? '' ),
							esc_html( $key_parts[2] ?? '' )
						);
					} else {
						// Single key like 'delete' or 'd'.
						printf(
							'<li>
								%1$s
								<span class="shortcut-key shortcut-key-%2$s">
									<i>%2$s</i>
								</span>
							</li>',
							esc_html( $label ),
							esc_html( $key )
						);
					}
				}

				echo '</ul>';
			}

		echo '
			</div>
		</script>';
	}
}
