<?php

namespace WPForms\Forms\Fields\Traits;

/**
 * Trait for rendering page indicators.
 *
 * Provides shared methods for rendering circles and connector indicators
 * to avoid code duplication between Lite (preview) and Pro (frontend) implementations.
 *
 * @since 1.10.0
 */
trait IndicatorRendererTrait {
	/**
	 * Render a single circles indicator item.
	 *
	 * @since 1.10.0
	 *
	 * @param array  $page           Page data with title.
	 * @param int    $page_num       Current page number.
	 * @param string $color          Indicator color.
	 * @param bool   $is_interactive Whether to add accessibility attributes for interactive elements.
	 */
	public function render_circles_indicator_item( array $page, int $page_num, string $color, bool $is_interactive = false ): void {

		$is_first         = $page_num === 1;
		$class            = $is_first ? 'active' : '';
		$background_color = ! empty( $color ) ? $color : '';

		// Build wrapper div attributes.
		$wrapper_attrs = sprintf(
			'class="wpforms-page-indicator-page %1$s wpforms-page-indicator-page-%2$d" data-page="%2$d"',
			sanitize_html_class( $class ),
			absint( $page_num )
		);

		// Add accessibility attributes for interactive elements (frontend).
		if ( $is_interactive ) {
			$wrapper_attrs .= sprintf(
				' role="button" tabindex="0" aria-label="%s"',
				/* translators: %d - page number. */
				esc_attr( sprintf( __( 'Go to page %d', 'wpforms-lite' ), $page_num ) )
			);
		}

		printf( '<div %s>', $wrapper_attrs ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		// Render page number circle.
		printf(
			'<span class="wpforms-page-indicator-page-number" %s data-page="%d">%d</span>',
			$is_first && ! empty( $background_color ) ? 'style="background-color:' . sanitize_hex_color( $background_color ) . '"' : '',
			absint( $page_num ),
			absint( $page_num )
		);

		// Render page title if present.
		if ( ! empty( $page['title'] ) ) {
			printf( '<span class="wpforms-page-indicator-page-title">%s</span>', esc_html( $page['title'] ) );
		}

		echo '</div>';
	}

	/**
	 * Render a single connector indicator item.
	 *
	 * @since 1.10.0
	 *
	 * @param array  $page           Page data with title.
	 * @param int    $page_num       Current page number.
	 * @param string $color          Indicator color.
	 * @param string $width          Width percentage for the connector item.
	 * @param bool   $is_interactive Whether to add accessibility attributes for interactive elements.
	 */
	public function render_connector_indicator_item( array $page, int $page_num, string $color, string $width, bool $is_interactive = false ): void {

		$is_first = $page_num === 1;
		$class    = $is_first ? 'active ' : '';

		// Build wrapper div attributes.
		$wrapper_attrs = sprintf(
			'class="wpforms-page-indicator-page %s wpforms-page-indicator-page-%d" style="min-width:%s;" data-page="%d"',
			sanitize_html_class( $class ),
			absint( $page_num ),
			esc_attr( $width ),
			absint( $page_num )
		);

		// Add accessibility attributes for interactive elements (frontend).
		if ( $is_interactive ) {
			$wrapper_attrs .= sprintf(
				' role="button" tabindex="0" aria-label="%s"',
				/* translators: %d - page number. */
				esc_attr( sprintf( __( 'Go to page %d', 'wpforms-lite' ), $page_num ) )
			);
		}

		printf( '<div %s>', $wrapper_attrs ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		// Render page number with triangle.
		printf(
			'<span class="wpforms-page-indicator-page-number" %s data-page="%d">%d',
			$is_first && ! empty( $color ) ? 'style="background-color:' . sanitize_hex_color( $color ) . '"' : '',
			absint( $page_num ),
			absint( $page_num )
		);

		printf(
			'<span class="wpforms-page-indicator-page-triangle" %s></span></span>',
			$is_first && ! empty( $color ) ? 'style="border-top-color:' . sanitize_hex_color( $color ) . '"' : ''
		);

		// Render page title if present.
		if ( ! empty( $page['title'] ) ) {
			printf( '<span class="wpforms-page-indicator-page-title">%s</span>', esc_html( $page['title'] ) );
		}

		echo '</div>';
	}
}
