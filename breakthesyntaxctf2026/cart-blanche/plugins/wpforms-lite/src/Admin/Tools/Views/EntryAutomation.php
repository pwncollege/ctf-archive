<?php

namespace WPForms\Admin\Tools\Views;

use WPForms\Admin\Education\Admin\Tools\EntryAutomation as Education;

/**
 * Class EntryAutomation.
 *
 * @since 1.9.6.1
 */
class EntryAutomation extends View {

	/**
	 * View slug.
	 *
	 * @since 1.9.6.1
	 *
	 * @var string
	 */
	protected $slug = 'entry-automation';

	/**
	 * Init view.
	 *
	 * @since 1.9.6.1
	 */
	public function init(): void {

		( new Education() )->init();

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.9.6.1
	 */
	private function hooks(): void {

		add_filter( 'admin_body_class', [ $this, 'body_classes' ] );
	}

	/**
	 * Add body classes for the view.
	 *
	 * @since 1.9.6.1
	 *
	 * @param string $classes Existing body classes.
	 *
	 * @return string
	 */
	public function body_classes( string $classes ): string {

		if ( ! wpforms_is_admin_page( 'tools', 'entry-automation' ) ) {
			return $classes;
		}

		return $classes . ' wpforms-admin-tools-view-entry-automation';
	}

	/**
	 * Get view label.
	 *
	 * @since 1.9.6.1
	 *
	 * @return string
	 */
	public function get_label(): string {

		return esc_html__( 'Entry Automation', 'wpforms-lite' );
	}

	/**
	 * Checking user capability to view.
	 *
	 * @since 1.9.6.1
	 *
	 * @return bool
	 */
	public function check_capability(): bool {

		/**
		 * Check if the user has the capability to view this entry automation.
		 *
		 * @since 1.9.6.1
		 *
		 * @param bool $capability Whether the user has the capability to view this entry automation.
		 */
		return (bool) apply_filters(
			'wpforms_admin_tools_views_entry_automation_check_capability',
			wpforms_current_user_can() && wpforms()->obj( 'addons' )->get_addon( 'entry-automation' )
		);
	}

	/**
	 * Display view content.
	 *
	 * @since 1.9.6.1
	 */
	public function display(): void {
		?>
		<div class="tools wpforms-settings-row-entry-automation">
			<div class="wpforms-entry-automation-content">
				<?php
					/**
					 * Display the content.
					 *
					 * @since 1.9.6.1
					 */
					do_action( 'wpforms_admin_tools_views_entry_automation_display' );
				?>
			</div>
		</div>
		<?php
	}
}
