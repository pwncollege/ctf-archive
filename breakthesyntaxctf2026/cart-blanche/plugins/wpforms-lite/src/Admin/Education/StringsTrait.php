<?php

namespace WPForms\Admin\Education;

/**
 * Strings trait.
 *
 * @since 1.8.8
 */
trait StringsTrait {

	/**
	 * Localize common strings.
	 *
	 * @since 1.6.6
	 *
	 * @return array
	 */
	protected function get_js_strings(): array {

		$strings = [];
		$name    = '%name%';

		$strings['ok']               = esc_html__( 'Ok', 'wpforms-lite' );
		$strings['cancel']           = esc_html__( 'Cancel', 'wpforms-lite' );
		$strings['close']            = esc_html__( 'Close', 'wpforms-lite' );
		$strings['ajax_url']         = admin_url( 'admin-ajax.php' );
		$strings['nonce']            = wp_create_nonce( 'wpforms-education' );
		$strings['activate_prompt']  = '<p>' . esc_html(
			sprintf( /* translators: %s - addon name. */
				__( 'The %s is installed but not activated. Would you like to activate it?', 'wpforms-lite' ),
				$name
			)
		) . '</p>';
		$strings['activate_confirm'] = esc_html__( 'Yes, Activate', 'wpforms-lite' );
		$strings['addon_activated']  = esc_html__( 'Addon activated', 'wpforms-lite' );
		$strings['plugin_activated'] = esc_html__( 'Plugin activated', 'wpforms-lite' );
		$strings['activating']       = esc_html__( 'Activating', 'wpforms-lite' );
		$strings['install_prompt']   = '<p>' . esc_html(
			sprintf( /* translators: %s - addon name. */
				__( 'The %s is not installed. Would you like to install and activate it?', 'wpforms-lite' ),
				$name
			)
		) . '</p>';
		$strings['install_confirm']  = esc_html__( 'Yes, Install and Activate', 'wpforms-lite' );
		$strings['installing']       = esc_html__( 'Installing', 'wpforms-lite' );
		$strings['save_prompt']      = esc_html__( 'Almost done! Would you like to save and refresh the form builder?', 'wpforms-lite' );
		$strings['save_confirm']     = esc_html__( 'Yes, save and refresh', 'wpforms-lite' );
		$strings['saving']           = esc_html__( 'Saving ...', 'wpforms-lite' );

		// Check if the user can install addons.
		// Includes license check.
		$can_install_addons = wpforms_can_install( 'addon' );

		// Check if the user can install plugins.
		// Only checks if the user has the capability.
		// Needed to display the correct message for non-admin users.
		$can_install_plugins = current_user_can( 'install_plugins' );

		$strings['can_install_addons'] = $can_install_addons && $can_install_plugins;

		if ( ! $can_install_addons ) {
			$strings['install_prompt'] = '<p>' . esc_html(
				sprintf( /* translators: %s - addon name. */
					__( 'The %s is not installed. Please install and activate it to use this feature.', 'wpforms-lite' ),
					$name
				)
			) . '</p>';
		}

		if ( ! $can_install_plugins ) {
			/* translators: %s - addon name. */
			$strings['install_prompt'] = '<p>' . esc_html(
				sprintf( /* translators: %s - addon name. */
					__( 'The %s is not installed. Please contact the site administrator.', 'wpforms-lite' ),
					$name
				)
			) . '</p>';
		}

		// Check if the user can activate plugins.
		$can_activate_plugins           = current_user_can( 'activate_plugins' );
		$strings['can_activate_addons'] = $can_activate_plugins;

		if ( ! $can_activate_plugins ) {
			/* translators: %s - addon name. */
			$strings['activate_prompt'] = '<p>' . esc_html( sprintf( __( 'The %s is not activated. Please contact the site administrator.', 'wpforms-lite' ), $name ) ) . '</p>';
		}

		$upgrade_utm_medium = wpforms_is_admin_page() ? 'Settings - Integration' : 'Builder - Settings';

		if ( wpforms_is_block_editor() ) {
			$upgrade_utm_medium = 'gutenberg';
		}

		$strings['upgrade'] = [
			'pro'   => $this->get_upgrade_strings( 'Pro', $name, $upgrade_utm_medium ),
			'elite' => $this->get_upgrade_strings( 'Elite', $name, $upgrade_utm_medium ),
		];

		$strings['upgrade_bonus'] = wpautop(
			wp_kses(
				__( '<strong>Bonus:</strong> WPForms Lite users get <span>50% off</span> regular price, automatically applied at checkout.', 'wpforms-lite' ),
				[
					'strong' => [],
					'span'   => [],
				]
			)
		);

		$strings['thanks_for_interest'] = esc_html__( 'Thanks for your interest in WPForms Pro!', 'wpforms-lite' );

		/**
		 * Filters the education strings.
		 *
		 * @since 1.6.6
		 *
		 * @param array $strings Education strings.
		 *
		 * @return array
		 */
		return (array) apply_filters( 'wpforms_admin_education_strings', $strings );
	}

	/**
	 * Get upgrade strings.
	 *
	 * @since 1.8.8
	 *
	 * @param string $level              Upgrade level.
	 * @param string $name               Addon name.
	 * @param string $upgrade_utm_medium UTM medium for the upgrade link.
	 *
	 * @return array
	 * @noinspection HtmlUnknownTarget
	 */
	private function get_upgrade_strings( string $level, string $name, string $upgrade_utm_medium ): array {
		// phpcs:ignore WPForms.Formatting.EmptyLineAfterFunctionDeclaration.AddEmptyLineAfterFunctionDeclaration

		return [
			'title'          => esc_html(
				sprintf( /* translators: %s - level name, either Pro or Elite. */
					__( 'is a %s Feature', 'wpforms-lite' ),
					$level
				)
			),
			'title_plural'   => esc_html(
				sprintf( /* translators: %s - level name, either Pro or Elite. */
					__( 'are a %s Feature', 'wpforms-lite' ),
					$level
				)
			),
			'message'        => '<p>' . esc_html(
				sprintf( /* translators: %1$s - addon name, %2$s - level name, either Pro or Elite. */
					__( 'We\'re sorry, the %1$s is not available on your plan. Please upgrade to the %2$s plan to unlock all these awesome features.', 'wpforms-lite' ),
					$name,
					$level
				)
			) . '</p>',
			'message_plural' => '<p>' . esc_html(
				sprintf( /* translators: %1$s - addon name, %2$s - level name, either Pro or Elite. */
					__( 'We\'re sorry, %1$s are not available on your plan. Please upgrade to the %2$s plan to unlock all these awesome features.', 'wpforms-lite' ),
					$name,
					$level
				)
			) . '</p>',
			'doc'            => sprintf(
				'<a href="%1$s" target="_blank" rel="noopener noreferrer" class="already-purchased">%2$s</a>',
				esc_url( wpforms_utm_link( 'https://wpforms.com/docs/upgrade-wpforms-lite-paid-license/#installing-wpforms', $upgrade_utm_medium, '%name%' ) ),
				esc_html__( 'Already purchased?', 'wpforms-lite' )
			),
			'button'         => esc_html(
				sprintf( /* translators: %s - level name, either Pro or Elite. */
					__( 'Upgrade to %s', 'wpforms-lite' ),
					$level
				)
			),
			'url'            => wpforms_admin_upgrade_link( $upgrade_utm_medium ),
			'url_template'   => wpforms_is_admin_page( 'templates' ) ? wpforms_admin_upgrade_link( 'Form Templates Subpage' ) : wpforms_admin_upgrade_link( 'builder-modal-template' ),
			'url_themes'     => wpforms_admin_upgrade_link( 'Builder Themes' ),
			'modal'          => wpforms_get_upgrade_modal_text( strtolower( $level ) ),
		];
	}
}
