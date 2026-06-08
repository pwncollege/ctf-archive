<?php

namespace WPForms\Admin\Education;

/**
 * Helpers class.
 *
 * @since 1.8.5
 */
class Helpers {

	/**
	 * Get badge HTML.
	 *
	 * @since 1.8.5
	 * @since 1.8.6 Added `$icon` parameter.
	 *
	 * @param string $text     Badge text.
	 * @param string $size     Badge size.
	 * @param string $position Badge position.
	 * @param string $color    Badge color.
	 * @param string $shape    Badge shape.
	 * @param string $icon     Badge icon name in Font Awesome "format", e.g. `fa-check`, defaults to empty string.
	 *
	 * @return string
	 */
	public static function get_badge(
		string $text,
		string $size = 'sm',
		string $position = 'inline',
		string $color = 'titanium',
		string $shape = 'rounded',
		string $icon = ''
	): string {

		if ( ! empty( $icon ) ) {
			$icon = self::get_inline_icon( $icon );
		}

		return sprintf(
			'<span class="wpforms-badge wpforms-badge-%1$s wpforms-badge-%2$s wpforms-badge-%3$s wpforms-badge-%4$s">%5$s%6$s</span>',
			esc_attr( $size ),
			esc_attr( $position ),
			esc_attr( $color ),
			esc_attr( $shape ),
			wp_kses(
				$icon,
				[
					'i' => [
						'class'       => [],
						'aria-hidden' => [],
					],
				]
			),
			esc_html( $text )
		);
	}

	/**
	 * Print badge HTML.
	 *
	 * @since 1.8.5
	 * @since 1.8.6 Added `$icon` parameter.
	 *
	 * @param string $text     Badge text.
	 * @param string $size     Badge size.
	 * @param string $position Badge position.
	 * @param string $color    Badge color.
	 * @param string $shape    Badge shape.
	 * @param string $icon     Badge icon name in Font Awesome "format", e.g. `fa-check`, defaults to empty string.
	 */
	public static function print_badge(
		string $text,
		string $size = 'sm',
		string $position = 'inline',
		string $color = 'titanium',
		string $shape = 'rounded',
		string $icon = ''
	) {

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo self::get_badge( $text, $size, $position, $color, $shape, $icon );
	}

	/**
	 * Get addon badge HTML.
	 *
	 * @since 1.8.9
	 *
	 * @param array $addon Addon data.
	 *
	 * @return string
	 */
	public static function get_addon_badge( array $addon ): string {

		// List of possible badges.
		$badges = [
			'recommended' => [
				'text'  => esc_html__( 'Recommended', 'wpforms-lite' ),
				'color' => 'green',
				'icon'  => 'fa-star',
			],
			'new'         => [
				'text'  => esc_html__( 'New', 'wpforms-lite' ),
				'color' => 'blue',
			],
			'featured'    => [
				'text'  => esc_html__( 'Featured', 'wpforms-lite' ),
				'color' => 'orange',
			],
		];

		$badge = [];

		// Get first badge that exists.
		foreach ( $badges as $key => $value ) {
			if ( ! empty( $addon[ $key ] ) ) {
				$badge = $value;

				break;
			}
		}

		if ( empty( $badge ) ) {
			return '';
		}

		return self::get_badge( $badge['text'], 'sm', 'inline', $badge['color'], 'rounded', $badge['icon'] ?? '' );
	}

	/**
	 * Get inline icon HTML.
	 *
	 * @since 1.8.6
	 *
	 * @param string $name Font Awesome icon name, e.g. `fa-check`.
	 *
	 * @return string HTML markup for the icon element.
	 */
	public static function get_inline_icon( string $name ): string {

		return sprintf( '<i class="fa %1$s" aria-hidden="true"></i>', esc_attr( $name ) );
	}

	/**
	 * Get available education addons.
	 *
	 * @since 1.9.4
	 *
	 * @return array
	 */
	public static function get_edu_addons(): array {

		static $addons = null;

		if ( $addons !== null ) {
			return $addons;
		}

		$addons_obj = wpforms()->obj( 'addons' );

		if ( ! $addons_obj ) {
			$addons = [];

			return $addons;
		}

		$addons = $addons_obj->get_available();

		return $addons;
	}
}
