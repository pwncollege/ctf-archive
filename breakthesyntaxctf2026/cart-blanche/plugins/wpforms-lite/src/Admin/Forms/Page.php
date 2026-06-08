<?php

namespace WPForms\Admin\Forms;

use WPForms\Admin\Forms\Table\Facades\Columns;
use WPForms\Admin\Traits\HasScreenOptions;

/**
 * Primary overview page inside the admin which lists all forms.
 *
 * @since 1.8.6
 */
class Page {

	use HasScreenOptions;

	/**
	 * Overview Table instance.
	 *
	 * @since 1.8.6
	 *
	 * @var ListTable
	 */
	private $overview_table;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.8.6
	 */
	public function __construct() {

		$this->screen_options_id = 'wpforms_forms_overview_screen_options';

		$this->screen_options = [
			'pagination' => [
				'heading' => esc_html__( 'Pagination', 'wpforms-lite' ),
				'options' => [
					[
						'label'   => esc_html__( 'Number of forms per page:', 'wpforms-lite' ),
						'option'  => 'per_page',
						'default' => wpforms()->obj( 'form' )->get_count_per_page(),
						'type'    => 'number',
						'args'    => [
							'min'       => 1,
							'max'       => 999,
							'step'      => 1,
							'maxlength' => 3,
						],
					],
				],
			],
			'view'       => [
				'heading' => esc_html__( 'View', 'wpforms-lite' ),
				'options' => [
					[
						'label'   => esc_html__( 'Show form templates', 'wpforms-lite' ),
						'option'  => 'show_form_templates',
						'default' => true,
						'type'    => 'checkbox',
						'checked' => true,
					],
				],
			],
		];

		$this->init_screen_options( wpforms_is_admin_page( 'overview' ) );

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.8.6
	 */
	private function hooks() {

		// Reset columns settings.
		add_filter( 'manage_toplevel_page_wpforms-overview_columns', [ $this, 'screen_settings_columns' ] );

		// Rewrite forms per page value from Form Overview page screen options.
		add_filter( 'wpforms_forms_per_page', [ $this, 'get_wpforms_forms_per_page' ] );
	}

	/**
	 * Check if the template visibility option is enabled.
	 *
	 * @since 1.8.8
	 *
	 * @return bool
	 */
	public function overview_show_form_templates() {

		return get_user_option( $this->screen_options_id . '_view_show_form_templates' );
	}

	/**
	 * Get forms per page value from Form Overview page screen options.
	 *
	 * @since 1.8.8
	 *
	 * @return int
	 */
	public function get_wpforms_forms_per_page() {

		return get_user_option( $this->screen_options_id . '_pagination_per_page' );
	}

	/**
	 * Determine if the user is viewing the overview page, if so, party on.
	 *
	 * @since 1.8.6
	 */
	public function init() { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		// Only load if we are actually on the overview page.
		if ( ! wpforms_is_admin_page( 'overview' ) ) {
			return;
		}

		// Avoid recursively include _wp_http_referer in the REQUEST_URI.
		$this->remove_referer();

		add_action( 'current_screen', [ $this, 'init_overview_table' ], 5 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueues' ] );
		add_action( 'wpforms_admin_page', [ $this, 'output' ] );
		add_action( 'wpforms_admin_page', [ $this, 'field_column_setting' ] );

		/**
		 * Fires after the form overview page initialization.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wpforms_overview_init' ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Init overview table class.
	 *
	 * @since 1.8.6
	 */
	public function init_overview_table() {

		$this->overview_table = ListTable::get_instance();
	}

	/**
	 * Remove previous `_wp_http_referer` variable from the REQUEST_URI.
	 *
	 * @since 1.8.6
	 */
	private function remove_referer() {

		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$_SERVER['REQUEST_URI'] = remove_query_arg( '_wp_http_referer', wp_unslash( $_SERVER['REQUEST_URI'] ) );
		}
	}

	/**
	 * Add per-page screen option to the Forms table.
	 *
	 * @since 1.8.6
	 *
	 * @depecated 1.8.8 Use HasScreenOptions trait instead.
	 */
	public function screen_options() {

		_deprecated_function( __METHOD__, '1.8.8 of the WPForms plugin' );
	}

	/**
	 * Filter screen settings columns data.
	 *
	 * @since 1.8.6
	 *
	 * @param array $columns Columns.
	 *
	 * @return array
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function screen_settings_columns( $columns ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found

		return [];
	}

	/**
	 * Enqueue assets for the overview page.
	 *
	 * @since 1.8.6
	 */
	public function enqueues() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-htmx',
			WPFORMS_PLUGIN_URL . 'assets/lib/htmx.min.js',
			[],
			WPFORMS_VERSION,
			true
		);

		wp_enqueue_script(
			'wpforms-admin-forms-overview',
			WPFORMS_PLUGIN_URL . "assets/js/admin/forms/overview{$min}.js",
			[ 'jquery', 'underscore', 'wpforms-htmx' ],
			WPFORMS_VERSION,
			true
		);

		wp_enqueue_style(
			'wpforms-admin-list-table-ext',
			WPFORMS_PLUGIN_URL . "assets/css/admin-list-table-ext{$min}.css",
			[],
			WPFORMS_VERSION
		);

		wp_enqueue_script(
			'wpforms-admin-list-table-ext',
			WPFORMS_PLUGIN_URL . "assets/js/admin/share/list-table-ext{$min}.js",
			[ 'jquery', 'jquery-ui-sortable', 'underscore', 'wpforms-admin', 'wpforms-multiselect-checkboxes' ],
			WPFORMS_VERSION,
			true
		);

		/**
		 * Fires after enqueue the forms overview page assets.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wpforms_overview_enqueue' ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Determine if it is an empty state.
	 *
	 * @since 1.8.6
	 */
	private function is_empty_state() {

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		return empty( $this->overview_table->items ) &&
			! isset( $_GET['search']['term'] ) &&
			! isset( $_GET['status'] ) &&
			! isset( $_GET['tags'] ) &&
			array_sum( wpforms()->obj( 'forms_views' )->get_count() ) === 0;
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Build the output for the overview page.
	 *
	 * @since 1.8.6
	 */
	public function output() {

		?>
		<div id="wpforms-overview" class="wrap wpforms-admin-wrap">

			<h1 class="page-title">
				<?php esc_html_e( 'Forms Overview', 'wpforms-lite' ); ?>
				<?php if ( wpforms_current_user_can( 'create_forms' ) ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpforms-builder&view=setup' ) ); ?>" class="page-title-action wpforms-btn add-new-h2 wpforms-btn-orange" data-action="add">
						<svg viewBox="0 0 14 14" class="page-title-action-icon">
							<path d="M14 5.385v3.23H8.615V14h-3.23V8.615H0v-3.23h5.385V0h3.23v5.385H14Z"/>
						</svg>
						<span class="page-title-action-text"><?php esc_html_e( 'Add New', 'wpforms-lite' ); ?></span>
					</a>
				<?php endif; ?>
			</h1>

			<div class="wpforms-admin-content">

				<?php
				$this->overview_table->prepare_items();

				/**
				 * Fires before forms overview list table output.
				 *
				 * @since 1.6.0.1
				 */
				do_action( 'wpforms_admin_overview_before_table' ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

				if ( $this->is_empty_state() ) {

					// Output no forms screen.
					echo wpforms_render( 'admin/empty-states/no-forms' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

				} else {
				?>
					<form id="wpforms-overview-table" method="get" action="<?php echo esc_url( admin_url( 'admin.php?page=wpforms-overview' ) ); ?>">

						<input type="hidden" name="post_type" value="wpforms" />
						<input type="hidden" name="page" value="wpforms-overview" />

						<?php
							$this->overview_table->search_box( esc_html__( 'Search Forms', 'wpforms-lite' ), 'wpforms-overview-search' );
							$this->overview_table->views();
							$this->overview_table->display();
						?>

					</form>
				<?php } ?>

			</div>

		</div>
		<?php
	}

	/**
	 * Settings for field column personalization.
	 *
	 * @since 1.8.6
	 */
	public function field_column_setting() {

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->get_columns_multiselect();
	}

	/**
	 * Get columns multiselect menu.
	 *
	 * @since 1.8.6
	 *
	 * @return string HTML menu markup.
	 */
	private function get_columns_multiselect(): string {

		$columns       = Columns::get_columns();
		$selected_keys = Columns::get_selected_columns_keys();
		$options       = '';

		$html = '
			<div id="wpforms-list-table-ext-edit-columns-select-container" class="wpforms-hidden wpforms-forms-overview-page">
			<form method="post" action="">
				<input type="hidden" name="action" value="wpforms_admin_forms_overview_save_columns_order"/>
				<select name="fields[]"
					id="wpforms-forms-table-edit-columns-select"
					class="wpforms-forms-table-edit-columns-select wpforms-list-table-ext-edit-columns-select"
					multiple="multiple">
						<optgroup label="' . esc_html__( 'Columns', 'wpforms-lite' ) . '">
							%s
						</optgroup>
				</select>
			</form>
			</div>
		';

		foreach ( $columns as $column ) {
			$selected = in_array( $column->get_id(), $selected_keys, true ) ? 'selected' : '';
			$disabled = $column->is_readonly() ? 'disabled="true"' : '';
			$options .= sprintf( '<option value="%s" %s %s>%s</option>', esc_attr( $column->get_id() ), $selected, $disabled, esc_html( $column->get_label() ) );
		}

		return sprintf( $html, $options );
	}
}
