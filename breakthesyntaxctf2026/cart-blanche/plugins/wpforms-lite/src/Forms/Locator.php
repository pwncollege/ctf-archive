<?php

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace WPForms\Forms;

use WP_Post;
use WPForms\Tasks\Actions\FormsLocatorScanTask;

/**
 * Class Locator.
 *
 * @since 1.7.4
 */
class Locator {

	/**
	 * Column name on Forms Overview admin page.
	 *
	 * @since 1.7.4
	 */
	const COLUMN_NAME = 'locations';

	/**
	 * Locations meta key.
	 *
	 * @since 1.7.4
	 */
	const LOCATIONS_META = 'wpforms_form_locations';

	/**
	 * WPForms widget name.
	 *
	 * @since 1.7.4
	 */
	const WPFORMS_WIDGET_NAME = 'wpforms-widget';

	/**
	 * WPForms widget prefix.
	 *
	 * @since 1.7.4
	 */
	const WPFORMS_WIDGET_PREFIX = self::WPFORMS_WIDGET_NAME . '-';

	/**
	 * WPForms widgets option name.
	 *
	 * @since 1.7.4
	 */
	const WPFORMS_WIDGET_OPTION = 'widget_' . self::WPFORMS_WIDGET_NAME;

	/**
	 * Text widget name.
	 *
	 * @since 1.7.4
	 */
	const TEXT_WIDGET_NAME = 'text';

	/**
	 * Text widget prefix.
	 *
	 * @since 1.7.4
	 */
	const TEXT_WIDGET_PREFIX = self::TEXT_WIDGET_NAME . '-';

	/**
	 * Text widgets option name.
	 *
	 * @since 1.7.4
	 */
	const TEXT_WIDGET_OPTION = 'widget_' . self::TEXT_WIDGET_NAME;

	/**
	 * Block widget name.
	 *
	 * @since 1.7.4
	 */
	const BLOCK_WIDGET_NAME = 'block';

	/**
	 * Block widget prefix.
	 *
	 * @since 1.7.4
	 */
	const BLOCK_WIDGET_PREFIX = self::BLOCK_WIDGET_NAME . '-';

	/**
	 * Block widgets' option name.
	 *
	 * @since 1.7.4
	 */
	const BLOCK_WIDGET_OPTION = 'widget_' . self::BLOCK_WIDGET_NAME;

	/**
	 * Location type for widget.
	 * For a page/post, the location type is the post type.
	 *
	 * @since 1.7.4
	 */
	const WIDGET = 'widget';

	/**
	 * WP template post type.
	 *
	 * @since 1.7.4
	 */
	const WP_TEMPLATE = 'wp_template';

	/**
	 * WP template post type.
	 *
	 * @since 1.7.4.1
	 */
	const WP_TEMPLATE_PART = 'wp_template_part';

	/**
	 * Standalone location types.
	 *
	 * @since 1.8.7
	 */
	const STANDALONE_LOCATION_TYPES = [ 'form_pages', 'conversational_forms' ];

	/**
	 * Default title for WPForms widget.
	 * For WPForms widget, we extract title from the widget. If it is empty, we use the default one.
	 *
	 * @since 1.7.4
	 *
	 * @var string
	 */
	private $wpforms_widget_title = '';

	/**
	 * Default title for text widget.
	 * For text widget, we extract title from the widget. If it is empty, we use the default one.
	 *
	 * @since 1.7.4
	 *
	 * @var string
	 */
	private $text_widget_title = '';

	/**
	 * Fixed title for block widget.
	 *
	 * @since 1.7.4
	 *
	 * @var string
	 */
	private $block_widget_title = '';

	/**
	 * Home url.
	 *
	 * @since 1.7.4
	 *
	 * @var string
	 */
	private $home_url;

	/**
	 * Scan status.
	 *
	 * @since 1.7.4
	 *
	 * @var string
	 */
	private $scan_status;

	/**
	 * Init class.
	 *
	 * @since 1.7.4
	 */
	public function init() {

		$this->home_url    = home_url();
		$this->scan_status = (string) get_option( FormsLocatorScanTask::SCAN_STATUS );

		$this->wpforms_widget_title = __( 'WPForms Widget', 'wpforms-lite' );
		$this->text_widget_title    = __( 'Text Widget', 'wpforms-lite' );
		$this->block_widget_title   = __( 'Block Widget', 'wpforms-lite' );

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.7.4
	 */
	private function hooks() {

		// View hooks.
		add_filter( 'wpforms_admin_forms_table_facades_columns_data', [ $this, 'add_column_data' ] );
		add_filter( 'wpforms_overview_table_column_value', [ $this, 'column_value' ], 10, 3 );
		add_filter( 'wpforms_overview_row_actions', [ $this, 'row_actions_all' ], 10, 2 );
		add_action( 'wpforms_overview_enqueue', [ $this, 'localize_overview_script' ] );

		// Monitoring hooks.
		add_action( 'save_post', [ $this, 'save_post' ], 10, 3 );
		add_action( 'post_updated', [ $this, 'post_updated' ], 10, 3 );
		add_action( 'wp_trash_post', [ $this, 'trash_post' ] );
		add_action( 'untrash_post', [ $this, 'untrash_post' ] );
		add_action( 'delete_post', [ $this, 'trash_post' ] );
		add_action( 'permalink_structure_changed', [ $this, 'permalink_structure_changed' ], 10, 2 );

		$wpforms_widget_option = self::WPFORMS_WIDGET_OPTION;
		$text_widget_option    = self::TEXT_WIDGET_OPTION;
		$block_widget_option   = self::BLOCK_WIDGET_OPTION;

		add_action( "update_option_{$wpforms_widget_option}" , [ $this, 'update_option' ], 10, 3 );
		add_action( "update_option_{$text_widget_option}" , [ $this, 'update_option' ], 10, 3 );
		add_action( "update_option_{$block_widget_option}", [ $this, 'update_option' ], 10, 3 );
	}

	/**
	 * Add locations' column to the table columns data.
	 *
	 * @since 1.8.6
	 *
	 * @param array|mixed $columns Columns data.
	 *
	 * @return array
	 */
	public function add_column_data( $columns ): array {

		$columns                      = (array) $columns;
		$columns[ self::COLUMN_NAME ] = [
			'label'      => esc_html__( 'Locations', 'wpforms-lite' ),
			'label_html' => sprintf(
				'<span class="wpforms-locations-column-title">%1$s</span>' .
				'<span class="wpforms-locations-column-icon" title="%2$s"></span>',
				esc_html__( 'Locations', 'wpforms-lite' ),
				esc_html__( 'Form locations', 'wpforms-lite' )
			),
		];

		return $columns;
	}

	/**
	 * Display column value.
	 *
	 * @since 1.7.4
	 *
	 * @param mixed   $value       Column value.
	 * @param WP_Post $form        Form.
	 * @param string  $column_name Column name.
	 *
	 * @return mixed
	 */
	public function column_value( $value, $form, $column_name ) {

		if ( $column_name !== self::COLUMN_NAME ) {
			return $value;
		}

		$form_locations = get_post_meta( $form->ID, self::LOCATIONS_META, true );

		if ( $form_locations === '' ) {
			$empty_values = [
				'' => '—',
				FormsLocatorScanTask::SCAN_STATUS_IN_PROGRESS => '...',
				FormsLocatorScanTask::SCAN_STATUS_COMPLETED => '0',
			];

			return $empty_values[ $this->scan_status ];
		}

		$values = $this->get_location_rows( $form_locations );

		if ( ! $values ) {
			return '0';
		}

		$column_value = sprintf(
			'<span class="wpforms-locations-count"><a href="#" title="%s">%d</a></span>',
			esc_attr__( 'View form locations', 'wpforms-lite' ),
			count( $values )
		);

		$column_value .= '<p class="locations-list">' . implode( '', $values ) . '</p>';

		return $column_value;
	}

	/**
	 * Row actions for view "All".
	 *
	 * @since 1.7.4
	 *
	 * @param array   $row_actions Row actions.
	 * @param WP_Post $form        Form object.
	 *
	 * @return array
	 */
	public function row_actions_all( $row_actions, $form ) {

		$form_locations = get_post_meta( $form->ID, self::LOCATIONS_META, true );

		if ( ! $form_locations ) {
			return $row_actions;
		}

		$locations = [
			'locations' => sprintf(
				'<a href="#" title="%s">%s</a>',
				esc_attr__( 'View form locations', 'wpforms-lite' ),
				esc_html__( 'Locations', 'wpforms-lite' )
			),
		];

		// Insert Locations action before the first available position in the positions' list or at the end of $row_actions.
		$positions = [
			'preview_',
			'duplicate',
			'trash',
		];

		$keys = array_keys( $row_actions );

		foreach ( $positions as $position ) {
			$pos = array_search( $position, $keys, true );

			if ( $pos !== false ) {
				break;
			}
		}

		$pos = $pos === false ? count( $row_actions ) : $pos;

		return array_slice( $row_actions, 0, $pos ) + $locations + array_slice( $row_actions, $pos );
	}

	/**
	 * Localize the overview script to pass translation strings.
	 *
	 * @since 1.7.4
	 */
	public function localize_overview_script() {

		wp_localize_script(
			'wpforms-admin-forms-overview',
			'wpforms_forms_locator',
			[
				'paneTitle' => __( 'Form Locations', 'wpforms-lite' ),
				'close'     => __( 'Close', 'wpforms-lite' ),
			]
		);
	}

	/**
	 * Get id of the sidebar where the widget is positioned.
	 *
	 * @since 1.7.4
	 *
	 * @param string $widget_id Widget id.
	 *
	 * @return string
	 */
	private function get_widget_sidebar_id( $widget_id ) {

		$sidebars_widgets = wp_get_sidebars_widgets();

		foreach ( $sidebars_widgets as $sidebar_id => $sidebar_widgets ) {
			foreach ( $sidebar_widgets as $sidebar_widget ) {
				if ( $widget_id === $sidebar_widget ) {
					return (string) $sidebar_id;
				}
			}
		}

		return '';
	}

	/**
	 * Get the name of the sidebar where the widget is positioned.
	 *
	 * @since 1.7.4
	 *
	 * @param string $widget_id Widget id.
	 *
	 * @return string
	 */
	private function get_widget_sidebar_name( $widget_id ) {

		$sidebar_id = $this->get_widget_sidebar_id( $widget_id );

		if ( ! $sidebar_id ) {
			return '';
		}

		$sidebar = $this->get_sidebar( $sidebar_id );

		return isset( $sidebar['name'] ) ? (string) $sidebar['name'] : '';
	}

	/**
	 * Retrieves the registered sidebar with the given ID.
	 *
	 * @since 1.7.4
	 *
	 * @global array $wp_registered_sidebars The registered sidebars.
	 *
	 * @param string $id The sidebar ID.
	 *
	 * @return array|null The discovered sidebar, or null if it is not registered.
	 */
	private function get_sidebar( $id ) {

		if ( function_exists( 'wp_get_sidebar' ) ) {
			return wp_get_sidebar( $id );
		}

		global $wp_registered_sidebars;

		if ( ! $wp_registered_sidebars ) {
			return null;
		}

		foreach ( $wp_registered_sidebars as $sidebar ) {
			if ( $sidebar['id'] === $id ) {
				return $sidebar;
			}
		}

		if ( $id === 'wp_inactive_widgets' ) {
			return [
				'id'   => 'wp_inactive_widgets',
				'name' => __( 'Inactive widgets', 'wpforms-lite' ),
			];
		}

		return null;
	}

	/**
	 * Get post location title.
	 *
	 * @since 1.7.4
	 *
	 * @param array $form_location Form location.
	 *
	 * @return string
	 */
	private function get_post_location_title( $form_location ) {

		$title = $form_location['title'];

		if ( $this->is_wp_template( $form_location['type'] ) ) {
			return __( 'Site editor template', 'wpforms-lite' ) . ': ' . $title;
		}

		return $title;
	}

	/**
	 * Whether locations' type is WP Template.
	 *
	 * @since 1.7.4.1
	 *
	 * @param string $location_type Location type.
	 *
	 * @return bool
	 */
	private function is_wp_template( $location_type ) {

		return in_array( $location_type, [ self::WP_TEMPLATE, self::WP_TEMPLATE_PART ], true );
	}

	/**
	 * Whether a location type is standalone.
	 *
	 * @since 1.8.7
	 *
	 * @param string $location_type Location type.
	 *
	 * @return bool
	 */
	private function is_standalone( string $location_type ): bool {

		return in_array( $location_type, self::STANDALONE_LOCATION_TYPES, true );
	}

	/**
	 * Get location title.
	 *
	 * @since 1.7.4
	 *
	 * @param array $form_location Form location.
	 *
	 * @return string
	 */
	private function get_location_title( $form_location ) {

		if ( $form_location['type'] !== self::WIDGET ) {
			return $this->get_post_location_title( $form_location );
		}

		$sidebar_name = $this->get_widget_sidebar_name( $form_location['id'] );

		if ( ! $sidebar_name ) {
			// The widget is not found.
			return '';
		}

		$title = $form_location['title'];

		if ( ! $title ) {
			if ( strpos( $form_location['id'], self::WPFORMS_WIDGET_PREFIX ) === 0 ) {
				$title = $this->wpforms_widget_title;
			}

			if ( strpos( $form_location['id'], 'text-' ) === 0 ) {
				$title = $this->text_widget_title;
			}
		}

		return $sidebar_name . ': ' . $title;
	}

	/**
	 * Get location url.
	 *
	 * @since 1.7.4
	 *
	 * @param array $form_location Form location.
	 *
	 * @return string
	 */
	private function get_location_url( $form_location ) {

		// Get widget or wp_template url.
		if ( $form_location['type'] === self::WIDGET || $this->is_wp_template( $form_location['type'] ) ) {
			return '';
		}

		// Get standalone url.
		if ( $this->is_standalone( $form_location['type'] ) ) {
			return $form_location['url'];
		}

		// Get post url.
		if ( ! $this->is_post_visible( $form_location ) ) {
			return '';
		}

		return $form_location['url'];
	}

	/**
	 * Get location edit url.
	 *
	 * @since 1.7.4
	 *
	 * @param array $form_location Form location.
	 *
	 * @return string
	 */
	private function get_location_edit_url( array $form_location ): string {

		// Get widget url.
		if ( $form_location['type'] === self::WIDGET ) {
			return current_user_can( 'edit_theme_options' ) ? admin_url( 'widgets.php' ) : '';
		}

		// Get standalone url.
		if ( $this->is_standalone( $form_location['type'] ) ) {
			return add_query_arg(
				[
					'page'    => 'wpforms-builder',
					'view'    => 'settings',
					'form_id' => $form_location['form_id'],
				],
				admin_url( 'admin.php' )
			);
		}

		// Get post url.
		if ( ! $this->is_post_visible( $form_location ) ) {
			return '';
		}

		if ( $this->is_wp_template( $form_location['type'] ) ) {
			return add_query_arg(
				[
					'postType' => $form_location['type'],
					'postId'   => get_stylesheet() . '//' . str_replace( '/', '', $form_location['url'] ),
				],
				admin_url( 'site-editor.php' )
			);
		}

		return (string) get_edit_post_link( $form_location['id'], '' );
	}


	/**
	 * Get location information to output as a row in the location pane.
	 *
	 * @since 1.7.4
	 *
	 * @param array $form_location Form location.
	 *
	 * @return string
	 * @noinspection PhpTernaryExpressionCanBeReducedToShortVersionInspection
	 * @noinspection ElvisOperatorCanBeUsedInspection
	 */
	private function get_location_row( $form_location ) {

		$title = $this->get_location_title( $form_location );

		$title = $title ? $title : __( '(no title)', 'wpforms-lite' );

		$location_url  = $this->get_location_url( $form_location );
		$location_link = '';

		if ( $location_url ) {
			$location_full_url = $this->home_url . $location_url;

			// phpcs:ignore Generic.Commenting.DocComment.MissingShort
			/** @noinspection HtmlUnknownTarget */
			$location_link = sprintf(
				' <a href="%1$s" target="_blank" class="wpforms-locations-link">%2$s <i class="fa fa-external-link" aria-hidden="true"></i></a>',
				esc_url( $location_full_url ),
				esc_url( $location_url )
			);
		}

		$location_edit_url = $this->get_location_edit_url( $form_location );
		$location_edit_url = $location_edit_url ? $location_edit_url : '#';

		// phpcs:ignore Generic.Commenting.DocComment.MissingShort
		/** @noinspection HtmlUnknownTarget */
		$location_edit_link = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( $location_edit_url ),
			esc_html( $title )
		);

		// Escaped above.
		return sprintf(
			'<span class="wpforms-locations-list-item">%s</span>',
			$location_edit_link . wp_kses_post( urldecode( $location_link ) )
		);
	}

	/**
	 * Get location information to output as rows in the location pane.
	 *
	 * @since 1.7.4
	 *
	 * @param array $form_locations Form locations.
	 *
	 * @return array
	 */
	private function get_location_rows( $form_locations ) {

		$rows = [];

		foreach ( $form_locations as $form_location ) {
			$rows[] = $this->get_location_row( $form_location );
		}

		$rows = array_unique( array_filter( $rows ) );

		uasort(
			$rows,
			static function ( $a, $b ) {
				$pattern = '/href=".+widgets.php">(.+?)</i';

				$widget_title_a = preg_match( $pattern, $a, $ma ) ? $ma[1] : '';
				$widget_title_b = preg_match( $pattern, $b, $mb ) ? $mb[1] : '';

				return strcmp( $widget_title_a, $widget_title_b );
			}
		);

		return $rows;
	}

	/**
	 * Update form location on save_post action.
	 *
	 * @since 1.7.4
	 *
	 * @param int     $post_ID Post ID.
	 * @param WP_Post $post    Post object.
	 * @param bool    $update  Whether this is an existing post being updated.
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function save_post( $post_ID, $post, $update ) {

		if (
			$update ||
			! in_array( $post->post_type, $this->get_post_types(), true ) ||
			! in_array( $post->post_status, $this->get_post_statuses(), true )
		) {
			return;
		}

		$form_ids = $this->get_form_ids( $post->post_content );

		$this->update_form_locations_metas( null, $post, [], $form_ids );
	}

	/**
	 * Update form location on post_updated action.
	 *
	 * @since 1.7.4
	 *
	 * @param int     $post_id     Post id.
	 * @param WP_Post $post_after  Post after the update.
	 * @param WP_Post $post_before Post before the update.
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function post_updated( $post_id, $post_after, $post_before ) {

		if (
			! in_array( $post_after->post_type, $this->get_post_types(), true ) ||
			! in_array( $post_after->post_status, $this->get_post_statuses(), true )
		) {
			return;
		}

		$form_ids_before = $this->get_form_ids( $post_before->post_content );
		$form_ids_after  = $this->get_form_ids( $post_after->post_content );

		$this->update_form_locations_metas( $post_before, $post_after, $form_ids_before, $form_ids_after );
	}

	/**
	 * Update form locations on trash_post action.
	 *
	 * @since 1.7.4
	 *
	 * @param int $post_id Post id.
	 */
	public function trash_post( $post_id ) {

		$post            = get_post( $post_id );
		$form_ids_before = $this->get_form_ids( $post->post_content );
		$form_ids_after  = [];

		$this->update_form_locations_metas( null, $post, $form_ids_before, $form_ids_after );
	}

	/**
	 * Update form locations on untrash_post action.
	 *
	 * @since 1.7.4
	 *
	 * @param int $post_id Post id.
	 */
	public function untrash_post( $post_id ) {

		$post            = get_post( $post_id );
		$form_ids_before = [];
		$form_ids_after  = $this->get_form_ids( $post->post_content );

		$this->update_form_locations_metas( null, $post, $form_ids_before, $form_ids_after );
	}

	/**
	 * Prepare widgets for further search.
	 *
	 * @since 1.7.4
	 *
	 * @param array|null $widgets Widgets.
	 * @param string     $type    Widget type.
	 *
	 * @return array
	 */
	private function prepare_widgets( $widgets, $type ) {

		$params = [
			'wpforms' => [
				'option'  => self::WPFORMS_WIDGET_OPTION,
				'content' => 'form_id',
			],
			'text'    => [
				'option'  => self::TEXT_WIDGET_OPTION,
				'content' => 'text',
			],
			'block'   => [
				'option'  => self::BLOCK_WIDGET_OPTION,
				'content' => 'content',
			],
		];

		if ( ! array_key_exists( $type, $params ) ) {
			return [];
		}

		$option  = $params[ $type ]['option'];
		$content = $params[ $type ]['content'];

		$widgets = $widgets ?? (array) get_option( $option, [] );

		return array_filter(
			$widgets,
			static function ( $widget ) use ( $content ) {

				return isset( $widget[ $content ] );
			}
		);
	}

	/**
	 * Search forms in WPForms widgets.
	 *
	 * @since 1.7.4
	 *
	 * @param array $widgets Widgets.
	 *
	 * @return array
	 */
	private function search_in_wpforms_widgets( $widgets = null ) {

		$widgets = $this->prepare_widgets( $widgets, 'wpforms' );

		$locations = [];

		foreach ( $widgets as $id => $widget ) {
			$locations[] = [
				'type'    => self::WIDGET,
				'title'   => $widget['title'],
				'form_id' => $widget['form_id'],
				'id'      => self::WPFORMS_WIDGET_PREFIX . $id,
			];
		}

		return $locations;
	}

	/**
	 * Search forms in text widgets.
	 *
	 * @since 1.7.4
	 *
	 * @param array $widgets Widgets.
	 *
	 * @return array
	 */
	private function search_in_text_widgets( $widgets = null ) {

		$widgets = $this->prepare_widgets( $widgets, 'text' );

		$locations = [];

		foreach ( $widgets as $id => $widget ) {
			$form_ids = $this->get_form_ids( $widget['text'] );

			foreach ( $form_ids as $form_id ) {
				$locations[] = [
					'type'    => self::WIDGET,
					'title'   => $widget['title'],
					'form_id' => $form_id,
					'id'      => self::TEXT_WIDGET_PREFIX . $id,
				];
			}
		}

		return $locations;
	}

	/**
	 * Search forms in block widgets.
	 *
	 * @since 1.7.4
	 *
	 * @param array $widgets Widgets.
	 *
	 * @return array
	 */
	private function search_in_block_widgets( $widgets = null ) {

		$widgets = $this->prepare_widgets( $widgets, 'block' );

		$locations = [];

		foreach ( $widgets as $id => $widget ) {
			$form_ids = $this->get_form_ids( $widget['content'] );

			foreach ( $form_ids as $form_id ) {
				$locations[] = [
					'type'    => self::WIDGET,
					'title'   => $this->block_widget_title,
					'form_id' => $form_id,
					'id'      => self::BLOCK_WIDGET_PREFIX . $id,
				];
			}
		}

		return $locations;
	}

	/**
	 * Search forms in widgets.
	 *
	 * @since 1.7.4
	 *
	 * @return array
	 */
	public function search_in_widgets() {

		return array_merge(
			$this->search_in_wpforms_widgets(),
			$this->search_in_text_widgets(),
			$this->search_in_block_widgets()
		);
	}

	/**
	 * Get the difference of two arrays containing locations.
	 *
	 * @since 1.7.4
	 *
	 * @param array $locations1 Locations to subtract from.
	 * @param array $locations2 Locations to subtract.
	 *
	 * @return array
	 */
	private function array_udiff( $locations1, $locations2 ) {

		return array_udiff(
			$locations1,
			$locations2,
			static function ( $a, $b ) {

				return ( $a === $b ) ? 0 : - 1;
			}
		);
	}

	/**
	 * Remove locations from metas.
	 *
	 * @since 1.7.4
	 *
	 * @param array $locations_to_remove Locations to remove.
	 *
	 * @return void
	 */
	private function remove_locations( $locations_to_remove ) {

		foreach ( $locations_to_remove as $location_to_remove ) {
			$locations = get_post_meta( $location_to_remove['form_id'], self::LOCATIONS_META, true );

			if ( ! $locations ) {
				continue;
			}

			foreach ( $locations as $key => $location ) {
				if ( $location['id'] === $location_to_remove['id'] ) {
					unset( $locations[ $key ] );
				}
			}

			update_post_meta( $location_to_remove['form_id'], self::LOCATIONS_META, $locations );
		}
	}

	/**
	 * Add locations to metas.
	 *
	 * @since 1.7.4
	 *
	 * @param array $locations_to_add Locations to add.
	 *
	 * @return void
	 */
	private function add_locations( $locations_to_add ) {

		foreach ( $locations_to_add as $location_to_add ) {
			$locations = get_post_meta( $location_to_add['form_id'], self::LOCATIONS_META, true );

			if ( ! $locations ) {
				$locations = [];
			}

			$locations[] = $location_to_add;

			update_post_meta( $location_to_add['form_id'], self::LOCATIONS_META, $locations );
		}
	}

	/**
	 * Update form locations on widget update.
	 *
	 * @since 1.7.4
	 *
	 * @param mixed  $old_value The old option value.
	 * @param mixed  $value     The new option value.
	 * @param string $option    Option name.
	 */
	public function update_option( $old_value, $value, $option ) {

		switch ( $option ) {
			case self::WPFORMS_WIDGET_OPTION:
				$old_locations = $this->search_in_wpforms_widgets( $old_value );
				$new_locations = $this->search_in_wpforms_widgets( $value );
				break;

			case self::TEXT_WIDGET_OPTION:
				$old_locations = $this->search_in_text_widgets( $old_value );
				$new_locations = $this->search_in_text_widgets( $value );
				break;

			case self::BLOCK_WIDGET_OPTION:
				$old_locations = $this->search_in_block_widgets( $old_value );
				$new_locations = $this->search_in_block_widgets( $value );
				break;

			default:
				// phpcs:ignore WPForms.Formatting.EmptyLineBeforeReturn.AddEmptyLineBeforeReturnStatement
				return;
		}

		$this->remove_locations( $this->array_udiff( $old_locations, $new_locations ) );
		$this->add_locations( $this->array_udiff( $new_locations, $old_locations ) );
	}

	/**
	 * Delete locations and schedule new rescan on change of permalink structure.
	 *
	 * @since 1.7.4
	 *
	 * @param string $old_permalink_structure The previous permalink structure.
	 * @param string $permalink_structure     The new permalink structure.
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function permalink_structure_changed( $old_permalink_structure, $permalink_structure ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		/**
		 * Run Forms Locator delete action.
		 *
		 * @since 1.7.4
		 */
		do_action( FormsLocatorScanTask::DELETE_ACTION ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName, WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound

		/**
		 * Run Forms Locator scan action.
		 *
		 * @since 1.7.4
		 */
		do_action( FormsLocatorScanTask::RESCAN_ACTION ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName, WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
	}

	/**
	 * Update form locations metas.
	 *
	 * @since 1.7.4
	 * @since 1.8.2.3 Added `$post_before` parameter.
	 *
	 * @param WP_Post|null $post_before     The post before the update.
	 * @param WP_Post      $post_after      The post after the update.
	 * @param array        $form_ids_before Form IDs before the update.
	 * @param array        $form_ids_after  Form IDs after the update.
	 */
	private function update_form_locations_metas( $post_before, $post_after, $form_ids_before, $form_ids_after ) {

		// Determine which locations to remove and which to add.
		$form_ids_to_remove = array_diff( $form_ids_before, $form_ids_after );
		$form_ids_to_add    = array_diff( $form_ids_after, $form_ids_before );

		// Loop through each form ID to remove the locations' meta.
		foreach ( $form_ids_to_remove as $form_id ) {
			update_post_meta(
				$form_id,
				self::LOCATIONS_META,
				$this->get_locations_without_current_post( $form_id, $post_after->ID )
			);
		}

		// Determine the titles and slugs.
		$old_title = $post_before->post_title ?? '';
		$old_slug  = $post_before->post_name ?? '';
		$new_title = $post_after->post_title;
		$new_slug  = $post_after->post_name;

		// If the title and slug are the same and there are no form IDs to add, bail.
		if ( empty( $form_ids_to_add ) && $old_title === $new_title && $old_slug === $new_slug ) {
			return;
		}

		// Merge the form IDs and remove duplicates.
		$form_ids = array_unique( array_merge( $form_ids_to_add, $form_ids_after ) );

		$this->save_location_meta( $form_ids, $post_after->ID, $post_after );
	}

	/**
	 * Save the location meta.
	 *
	 * @since 1.8.2.3
	 *
	 * @param array   $form_ids   Form IDs.
	 * @param int     $post_id    Post ID.
	 * @param WP_Post $post_after Post after the update.
	 */
	private function save_location_meta( $form_ids, $post_id, $post_after ) {

		// Build the URL.
		$url = get_permalink( $post_id );
		$url = ( $url === false || is_wp_error( $url ) ) ? '' : $url;
		$url = str_replace( $this->home_url, '', $url );

		// Loop through each Form ID and save the location meta.
		foreach ( $form_ids as $form_id ) {

			$locations = $this->get_locations_without_current_post( $form_id, $post_id );

			$locations[] = [
				'type'    => $post_after->post_type,
				'title'   => $post_after->post_title,
				'form_id' => $form_id,
				'id'      => $post_id,
				'status'  => $post_after->post_status,
				'url'     => $url,
			];

			update_post_meta( $form_id, self::LOCATIONS_META, $locations );
		}
	}

	/**
	 * Get post types for search in.
	 *
	 * @since 1.7.4
	 *
	 * @return string[]
	 */
	public function get_post_types() {

		$args       = [
			'public'             => true,
			'publicly_queryable' => true,
		];
		$post_types = get_post_types( $args, 'names', 'or' );

		unset( $post_types['attachment'] );

		$post_types[] = self::WP_TEMPLATE;
		$post_types[] = self::WP_TEMPLATE_PART;

		return $post_types;
	}

	/**
	 * Get post statuses for search in.
	 *
	 * @since 1.7.4
	 *
	 * @return string[]
	 */
	public function get_post_statuses() {

		return [ 'publish', 'pending', 'draft', 'future', 'private' ];
	}

	/**
	 * Get form ids from the content.
	 *
	 * @since 1.7.4
	 *
	 * @param string $content Content.
	 *
	 * @return int[]
	 */
	public function get_form_ids( $content ) {

		$form_ids = [];

		if (
			preg_match_all(
				/**
				 * Extract id from conventional wpforms shortcode or wpforms block.
				 * Examples:
				 * [wpforms id="32" title="true" description="true"]
				 * <!-- wp:wpforms/form-selector {"clientId":"b5f8e16a-fc28-435d-a43e-7c77719f074c", "formId":"32","displayTitle":true,"displayDesc":true} /-->
				 * In both, we should find 32.
				 */
				'#\[\s*wpforms.+id\s*=\s*"(\d+?)".*]|<!-- wp:wpforms/form-selector {.*?"formId":"(\d+?)".*?} /-->#',
				$content,
				$matches
			)
		) {
			array_shift( $matches );
			$form_ids = array_map(
				'intval',
				array_unique( array_filter( array_merge( ...$matches ) ) )
			);
		}

		return $form_ids;
	}

	/**
	 * Get form locations without a current post.
	 *
	 * @since 1.7.4
	 *
	 * @param int $form_id Form id.
	 * @param int $post_id Post id.
	 *
	 * @return array
	 */
	private function get_locations_without_current_post( $form_id, $post_id ) {

		$locations = get_post_meta( $form_id, self::LOCATIONS_META, true );

		if ( ! is_array( $locations ) ) {
			$locations = [];
		}

		return array_filter(
			$locations,
			static function ( $location ) use ( $post_id ) {

				return $location['id'] !== $post_id;
			}
		);
	}

	/**
	 * Determine whether a post is visible.
	 *
	 * @since 1.7.4
	 *
	 * @param array $location Post location.
	 *
	 * @return bool
	 */
	private function is_post_visible( $location ) {

		$edit_cap = 'edit_post';
		$read_cap = 'read_post';
		$post_id  = $location['id'];

		if ( ! get_post_type_object( $location['type'] ) ) {
			// Post type is not registered.
			return false;
		}

		$post_status_obj = get_post_status_object( $location['status'] );

		if ( ! $post_status_obj ) {
			// Post status is not registered, assume it's not public.
			return current_user_can( $edit_cap, $post_id );
		}

		if ( $post_status_obj->public ) {
			return true;
		}

		if ( ! is_user_logged_in() ) {
			// User must be logged in to view unpublished posts.
			return false;
		}

		if ( $post_status_obj->protected ) {
			// User must have edit permissions on the draft to preview.
			return current_user_can( $edit_cap, $post_id );
		}

		if ( $post_status_obj->private ) {
			return current_user_can( $read_cap, $post_id );
		}

		return false;
	}

	/**
	 * Build a standalone location.
	 *
	 * @since 1.8.7
	 *
	 * @param int    $form_id   The form ID.
	 * @param array  $form_data Form data.
	 * @param string $status    Form status.
	 *
	 * @return array Location.
	 */
	public function build_standalone_location( int $form_id, array $form_data, string $status = 'publish' ): array {

		if ( empty( $form_id ) || empty( $form_data ) ) {
			return [];
		}

		// Form templates should not have any locations.
		if ( get_post_type( $form_id ) === 'wpforms-template' ) {
			return [];
		}

		foreach ( self::STANDALONE_LOCATION_TYPES as $location_type ) {
			if ( empty( $form_data['settings'][ "{$location_type}_enable" ] ) ) {
				continue;
			}

			return $this->build_standalone_location_type( $location_type, $form_id, $form_data, $status );
		}

		return [];
	}

	/**
	 * Build a standalone location.
	 *
	 * @since 1.8.8
	 *
	 * @param string $location_type Standalone location type.
	 * @param int    $form_id       The form ID.
	 * @param array  $form_data     Form data.
	 * @param string $status        Form status.
	 *
	 * @return array Location.
	 */
	private function build_standalone_location_type( string $location_type, int $form_id, array $form_data, string $status ): array {

		$title_key = "{$location_type}_title";
		$slug_key  = "{$location_type}_page_slug";
		$title     = $form_data['settings'][ $title_key ] ?? '';
		$slug      = $form_data['settings'][ $slug_key ] ?? '';

		// Return the location array.
		return [
			'type'    => $location_type,
			'title'   => $title,
			'form_id' => (int) $form_data['id'],
			'id'      => $form_id,
			'status'  => $status,
			'url'     => '/' . $slug . '/',
		];
	}

	/**
	 * Add standalone form locations to post meta.
	 *
	 * Post meta is used to store all forms' locations,
	 * which is displayed on the WPForms Overview page.
	 *
	 * @since 1.8.7
	 *
	 * @param int   $form_id Form ID.
	 * @param array $data    Form data.
	 */
	public function add_standalone_location_to_locations_meta( int $form_id, array $data ) {

		// Build standalone location.
		$location = $this->build_standalone_location( $form_id, $data );

		// No location? Bail.
		if ( empty( $location ) ) {
			return;
		}

		// Setup data.
		$new_location[] = $location;
		$post_meta      = get_post_meta( $form_id, self::LOCATIONS_META, true );

		// If there is post meta, merge it with the new location.
		if ( ! empty( $post_meta ) ) {

			// Remove any previously set standalone locations.
			$post_meta = $this->remove_standalone_location_from_array( $form_id, $post_meta );

			// Merge locations and remove duplicates.
			$new_location = array_unique( array_merge( $post_meta, $new_location ), SORT_REGULAR );
		}

		// Update post meta.
		update_post_meta( $form_id, self::LOCATIONS_META, $new_location );
	}

	/**
	 * Remove a form page from an array.
	 *
	 * @since 1.8.7
	 *
	 * @param int   $form_id   The form ID.
	 * @param array $post_meta The post meta.
	 *
	 * @return array $post_meta Filtered post meta.
	 */
	private function remove_standalone_location_from_array( int $form_id, array $post_meta ): array {

		// No form ID or post meta? Bail.
		if ( empty( $form_id ) || empty( $post_meta ) ) {
			return [];
		}

		// Loop over all locations.
		foreach ( $post_meta as $key => $location ) {

			// Verify the location keys exist.
			if ( ! isset( $location['form_id'], $location['type'] ) ) {
				continue;
			}

			// If the form ID and location type match.
			if ( $location['form_id'] === $form_id && $this->is_standalone( $location['type'] ) ) {

				// Unset the form page location.
				unset( $post_meta[ $key ] );
			}
		}

		return $post_meta;
	}
}
