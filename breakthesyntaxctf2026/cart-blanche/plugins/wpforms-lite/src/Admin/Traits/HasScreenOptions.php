<?php

namespace WPForms\Admin\Traits;

/**
 * Enables screen options for the current screen.
 *
 * @since 1.8.8
 */
trait HasScreenOptions {

	/**
	 * A configuration array for the screen options.
	 *
	 * @var array Array of screen options.
	 *
	 * @since 1.8.8
	 */
	private $screen_options;

	/**
	 * Screen options ID.
	 *
	 * @var string Screen options ID.
	 *
	 * @since 1.8.8
	 */
	private $screen_options_id;

	/**
	 * Initialize the screen options.
	 *
	 * This method should be called during init of the class that uses this trait.
	 * If the class itself is allowed to load, it should set $allowed to true.
	 *
	 * @param bool $allowed Whether to allow screen options or not.
	 *
	 * @since 1.8.8
	 */
	public function init_screen_options( bool $allowed = false ) {

		// This should always run.
		$this->filter_screen_options();

		if ( ! $allowed ) {
			return;
		}

		add_action( 'admin_head', [ $this, 'add_screen_options' ] );
		add_filter( 'screen_settings', [ $this, 'render_screen_options' ], 10, 2 );
		add_filter( "set_screen_option_{$this->screen_options_id}", [ $this, 'save_screen_options' ], 10, 2 );
		add_filter( 'screen_options_show_submit', '__return_true' );
	}

	/**
	 * Filter screen options.
	 *
	 * @since 1.8.8
	 */
	public function filter_screen_options() {

		$options = get_user_option( $this->screen_options_id );

		foreach ( $this->screen_options as $group => $options_group ) {
			foreach ( $options_group['options'] as $option ) {
				add_filter(
					"get_user_option_{$this->screen_options_id}_{$group}_{$option['option']}",
					function () use ( $option, $group, $options ) {
						$key = $group . '_' . $option['option'];

						return $options[ $key ] ?? $option['default'];
					}
				);
			}
		}
	}

	/**
	 * Configure screen options.
	 *
	 * @since 1.8.8
	 */
	public function add_screen_options() {

		foreach ( $this->screen_options as $group => $options_group ) {
			foreach ( $options_group['options'] as $option ) {
				add_screen_option(
					$group . '_' . $option['option'],
					[
						'label'   => $option['label'],
						'option'  => $option['option'],
						'default' => $option['default'],
					]
				);
			}
		}
	}

	/**
	 * Save screen options.
	 *
	 * @since 1.8.8
	 *
	 * @param mixed  $status Status of the screen option.
	 * @param string $option Option name.
	 *
	 * @return mixed
	 */
	public function save_screen_options( $status, $option ) { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded

		if ( $option === $this->screen_options_id ) {

			$value = [];

			foreach ( $this->screen_options as $group => $options_group ) {

				$options = $options_group['options'];

				foreach ( $options as $group_option ) {
					$key = $group . '_' . $group_option['option'];

					if ( ! isset( $_POST[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
						$value[ $key ] = false;

						continue;
					}

					$value[ $key ] = ! empty( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : $group_option['default']; // phpcs:ignore WordPress.Security.NonceVerification.Missing
				}
			}

			return $value;
		}

		return $status;
	}

	/**
	 * Render screen options.
	 *
	 * @since 1.8.8
	 *
	 * @param string $screen_settings HTML markup of custom screen settings.
	 *
	 * @return string
	 */
	public function render_screen_options( $screen_settings ) { // phpcs:ignore Generic.Metrics.NestingLevel.MaxExceeded

		foreach ( $this->screen_options as $group => $options_group ) {
			$screen_settings .= '<fieldset>';
			$screen_settings .= '<legend>' . esc_html( $options_group['heading'] ) . '</legend>';

			foreach ( $options_group['options'] as $option ) {
				$option_value = get_user_option( "{$this->screen_options_id}_{$group}_{$option['option']}" );

				$key = $group . '_' . $option['option'];

				switch ( $option['type'] ) {
					case 'checkbox':
						$screen_settings .= sprintf(
							'<label>
								<input type="checkbox" id="%1$s" name="%1$s" value="1" %2$s>%3$s
							</label>',
							$key,
							checked( (bool) $option_value, true, false ),
							esc_html( $option['label'] )
						);
						break;

					case 'number':
						$screen_settings .= sprintf(
							'<label for="%1$s">%2$s</label>
							<input type="number" id="%1$s" name="%1$s" value="%3$s" %4$s>',
							$key,
							esc_html( $option['label'] ),
							esc_attr( $option_value ),
							wpforms_html_attributes( '', [], [], $option['args'] ?? [] )
						);
						break;

					case 'text':
						$screen_settings .= sprintf(
							'<label for="%1$s">%2$s</label>
							<input type="text" id="%1$s" name="%1$s" value="%3$s">',
							$key,
							esc_html( $option['label'] ),
							esc_attr( $option_value )
						);
						break;
				}
			}

			$screen_settings .= sprintf(
				'<input name="wp_screen_options[option]" type="hidden" value="%s"><input name="wp_screen_options[value]" type="hidden" value="">',
				$this->screen_options_id
			);

			$screen_settings .= '</fieldset>';
		}

		return $screen_settings;
	}
}
