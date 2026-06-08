<?php

namespace WPForms\Forms\Fields\Traits;

/**
 * Trait MultiFieldMenu.
 *
 * Methods for multi-field menu functionality.
 *
 * @since 1.9.9
 */
trait MultiFieldMenu {

	/**
	 * Generate multi-field actions menu HTML.
	 *
	 * @since 1.9.9
	 *
	 * @return string Multi-field menu HTML.
	 */
	public function get_multi_field_menu_html(): string {

		$items = [
			'duplicate-multi' => [
				'icon'  => 'fa-files-o',
				'label' => __( 'Duplicate Fields', 'wpforms-lite' ),
			],
			'delete-multi'    => [
				'icon'  => 'fa-trash-o',
				'label' => __( 'Delete Fields', 'wpforms-lite' ),
				'last'  => true,
			],
		];

		$divider = '<li class="wpforms-context-menu-list-divider"></li>';

		$html  = '<div class="wpforms-field-multi-field-menu">';
		$html .= '<ul class="wpforms-context-menu-list">';

		foreach ( $items as $action => $item ) {
			$html .= sprintf(
				'<li class="wpforms-context-menu-list-item" data-action="%1$s">
					<span class="wpforms-context-menu-list-item-icon">
						<i class="fa %2$s" aria-hidden="true"></i>
					</span>
					<span class="wpforms-context-menu-list-item-text">%3$s</span>
				</li>
				%4$s',
				esc_attr( $action ),
				esc_attr( $item['icon'] ),
				esc_html( $item['label'] ),
				empty( $item['last'] ) ? $divider : ''
			);
		}

		$html .= '</ul>';
		$html .= '</div>';

		return $html;
	}
}
