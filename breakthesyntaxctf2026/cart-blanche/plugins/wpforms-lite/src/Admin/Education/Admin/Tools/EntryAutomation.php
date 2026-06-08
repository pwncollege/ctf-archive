<?php

namespace WPForms\Admin\Education\Admin\Tools;

/**
 * Entry Automation Education class.
 *
 * @since 1.9.6.1
 */
class EntryAutomation {

	/**
	 * Education init.
	 *
	 * @since 1.9.6.1
	 */
	public function init(): void {

		$this->hooks();
	}

	/**
	 * Load hooks.
	 *
	 * @since 1.9.6.1
	 */
	private function hooks(): void {

		add_action( 'wpforms_admin_tools_views_entry_automation_display', [ $this, 'display' ] );
	}

	/**
	 * Get the template data.
	 *
	 * @since 1.9.6.1
	 *
	 * @return array
	 */
	private function get_template_data(): array {

		$images_url   = WPFORMS_PLUGIN_URL . 'assets/images/entry-automation/';
		$utm_medium   = 'Tools - Entry Automation';
		$utm_content  = 'Entry Automation Addon';
		$addon        = wpforms()->obj( 'addons' )->get_addon( 'entry-automation' );
		$upgrade_link = $addon['action'] === 'upgrade'
			? sprintf( /* translators: %1$s - WPForms.com Upgrade page URL. */
				' <strong><a href="%1$s" target="_blank" rel="noopener noreferrer" class="wpforms-upgrade-link">%2$s</a></strong>',
				esc_url( wpforms_admin_upgrade_link( $utm_medium, $utm_content ) ),
				esc_html__( 'Upgrade to WPForms Elite', 'wpforms-lite' )
			)
			: '';

		$params = [
			'features'             => [
				__( 'Automated Task Scheduling', 'wpforms-lite' ),
				__( 'Scheduled Exports', 'wpforms-lite' ),
				__( 'Task Chaining', 'wpforms-lite' ),
				__( 'Automated Deletions', 'wpforms-lite' ),
				__( 'Enhanced Data Management', 'wpforms-lite' ),
				__( 'Robust Failsafes', 'wpforms-lite' ),
			],
			'images'               => [
				[
					'url'   => $images_url . 'education.png',
					'url2x' => $images_url . 'education.png',
					'title' => '',
				],
			],
			'utm_medium'           => $utm_medium,
			'utm_content'          => $utm_content,
			'upgrade_link_text'    => esc_html__( 'Upgrade to WPForms Elite', 'wpforms-lite' ),
			'heading_title'        => __( 'Tired of manually exporting and deleting entries? Wish you could schedule these actions for optimal efficiency?', 'wpforms-lite' ),
			/* translators: %1$s - WPForms.com Upgrade page URL. */
			'heading_description'  => '<p>' . esc_html__( 'Entry Automation introduces powerful, automated task chaining, allowing you to seamlessly schedule exports and deletions, ensuring your data is managed precisely how you need it. Chain multiple tasks together for complex workflows – export to CSV, then automatically delete after a specified period, for example. We\'ve built robust failsafes to guarantee data integrity, so you can automate with confidence, knowing your valuable entries are always protected. Take back your time and let our addon handle the heavy lifting, keeping your WPForms data organized and secure, automatically.', 'wpforms-lite' ) . '</p>'
			. '<p>' . wp_kses(
                $upgrade_link,
                [
					'a'      => [
						'href'   => [],
						'rel'    => [],
						'target' => [],
						'class'  => [],
					],
					'strong' => [],
				]
            ) . '</p>',
			'features_description' => __( 'Powerful Automation Features', 'wpforms-lite' ),
		];

		return isset( $addon ) ? array_merge( $params, $addon ) : $params;
	}

	/**
	 * Check if the addon is active.
	 *
	 * @since 1.9.6.1
	 *
	 * @return bool
	 */
	private function is_addon_active(): bool {

		/**
		 * Check if the addon is active.
		 *
		 * @since 1.9.6.1
		 *
		 * @param bool $is_active Whether the addon is active.
		 */
		return (bool) apply_filters(
			'wpforms_admin_education_admin_tools_entry_automation_is_addon_active',
			wpforms()->obj( 'addons' )->is_active( 'entry-automation' )
		);
	}

	/**
	 * Display education content.
	 *
	 * @since 1.9.6.1
	 */
	public function display(): void {

		// Display the education content only if the addon is not active.
		if ( $this->is_addon_active() ) {
			return;
		}

		$this->enqueue();

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render( 'education/admin/page', $this->get_template_data(), true );
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 1.9.6.1
	 */
	private function enqueue(): void {

		// Lity.
		wp_enqueue_style(
			'wpforms-lity',
			WPFORMS_PLUGIN_URL . 'assets/lib/lity/lity.min.css',
			null,
			'3.0.0'
		);

		wp_enqueue_script(
			'wpforms-lity',
			WPFORMS_PLUGIN_URL . 'assets/lib/lity/lity.min.js',
			[ 'jquery' ],
			'3.0.0',
			true
		);
	}
}
