<?php

namespace WPForms\Forms;

/**
 * Form preview.
 *
 * @since 1.5.1
 */
class Preview {

	/**
	 * Form data.
	 *
	 * @since 1.5.1
	 *
	 * @var array
	 */
	public $form_data;

	/**
	 * Post type.
	 *
	 * @since 1.8.8
	 *
	 * @var string
	 */
	private $post_type;

	/**
	 * Whether this is a form template.
	 *
	 * @since 1.8.8
	 *
	 * @var bool
	 */
	private $is_form_template;

	/**
	 * Constructor.
	 *
	 * @since 1.5.1
	 */
	public function __construct() {

		if ( ! $this->is_preview_page() ) {
			return;
		}

		$this->hooks();
	}

	/**
	 * Check if current page request meets requirements for form preview page.
	 *
	 * @since 1.5.1
	 *
	 * @return bool
	 */
	public function is_preview_page(): bool {

		// Only proceed for the form preview page.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $_GET['wpforms_form_preview'] ) ) {
			return false;
		}

		// Only logged-in users can access the preview page.
		if ( ! is_user_logged_in() ) {
			return false;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$form_id = absint( $_GET['wpforms_form_preview'] );

		// Make sure the user is allowed to preview the form.
		if ( ! wpforms_current_user_can( 'view_form_single', $form_id ) ) {
			return false;
		}

		// Fetch form details.
		$this->form_data = wpforms()->obj( 'form' )->get( $form_id, [ 'content_only' => true ] );

		// Get the post type for preview item.
		$this->post_type = get_post_type( $form_id );

		// Check if this is a form template.
		$this->is_form_template = $this->post_type === 'wpforms-template';

		// Check valid form was found.
		if ( empty( $this->form_data ) || empty( $this->form_data['id'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Hooks.
	 *
	 * @since 1.5.1
	 */
	public function hooks() {

		add_filter( 'wpforms_frontend_assets_header_force_load', '__return_true' );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'pre_get_posts', [ $this, 'pre_get_posts' ] );
		add_filter( 'the_title', [ $this, 'the_title' ], 100, 1 );
		add_filter( 'the_content', [ $this, 'the_content' ], 999 );
		add_filter( 'get_the_excerpt', [ $this, 'the_content' ], 999 );
		add_filter( 'home_template_hierarchy', [ $this, 'force_page_template_hierarchy' ] );
		add_filter( 'frontpage_template_hierarchy', [ $this, 'force_page_template_hierarchy' ] );
		add_filter( 'wpforms_smarttags_process_page_title_value', [ $this, 'smart_tags_process_page_title_value' ], 10, 5 );
		add_filter( 'post_thumbnail_html', '__return_empty_string' );
	}

	/**
	 * Enqueue additional form preview styles.
	 *
	 * @since 1.8.8
	 */
	public function enqueue_assets() {

		$min = wpforms_get_min_suffix();

		// Enqueue the form preview styles.
		wp_enqueue_style(
			'wpforms-preview',
			WPFORMS_PLUGIN_URL . "assets/css/frontend/wpforms-form-preview{$min}.css",
			[],
			WPFORMS_VERSION
		);
	}

	/**
	 * Modify query, limit to one post.
	 *
	 * @since 1.5.1
	 * @since 1.7.0 Added `page_id`, `post_type` and `post__in` query variables.
	 *
	 * @param \WP_Query $query The WP_Query instance.
	 */
	public function pre_get_posts( $query ) {

		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$query->set( 'page_id', '' );
		$query->set( 'post_type', $this->post_type ?? 'wpforms' );
		$query->set( 'post__in', empty( $this->form_data['id'] ) ? [] : [ (int) $this->form_data['id'] ] );
		$query->set( 'posts_per_page', 1 );

		// The preview page reads as the home page and as an non-singular posts page, neither of which are actually the case.
		// So we hardcode the correct values for those properties in the query.
		$query->is_home     = false;
		$query->is_singular = true;
		$query->is_single   = true;
	}

	/**
	 * Customize form preview page title.
	 *
	 * @since 1.5.1
	 *
	 * @param string $title Page title.
	 *
	 * @return string
	 */
	public function the_title( $title ) {

		if ( ! in_the_loop() ) {
			return $title;
		}

		if ( $this->is_form_template ) {
			return sprintf( /* translators: %s - form name. */
				esc_html__( '%s Template Preview', 'wpforms-lite' ),
				! empty( $this->form_data['settings']['form_title'] ) ? sanitize_text_field( $this->form_data['settings']['form_title'] ) : esc_html__( 'Form Template', 'wpforms-lite' )
			);
		}

		return sprintf( /* translators: %s - form name. */
			esc_html__( '%s Preview', 'wpforms-lite' ),
			! empty( $this->form_data['settings']['form_title'] ) ? sanitize_text_field( $this->form_data['settings']['form_title'] ) : esc_html__( 'Form', 'wpforms-lite' )
		);
	}

	/**
	 * Customize form preview page content.
	 *
	 * @since 1.5.1
	 *
	 * @return string
	 */
	public function the_content() {

		if ( ! isset( $this->form_data['id'] ) ) {
			return '';
		}

		if ( ! wpforms_current_user_can( 'view_form_single', $this->form_data['id'] ) ) {
			return '';
		}

		$admin_url = admin_url( 'admin.php' );

		$links = [];

		if ( wpforms_current_user_can( 'edit_form_single', $this->form_data['id'] ) ) {
			$links[] = [
				'url'  => esc_url(
					add_query_arg(
						[
							'page'    => 'wpforms-builder',
							'view'    => 'fields',
							'form_id' => absint( $this->form_data['id'] ),
						],
						$admin_url
					)
				),
				'text' => $this->is_form_template ? esc_html__( 'Edit Form Template', 'wpforms-lite' ) : esc_html__( 'Edit Form', 'wpforms-lite' ),
			];
		}

		if ( wpforms()->is_pro() && wpforms_current_user_can( 'view_entries_form_single', $this->form_data['id'] ) ) {
			$links[] = [
				'url'  => esc_url(
					add_query_arg(
						[
							'page'    => 'wpforms-entries',
							'view'    => 'list',
							'form_id' => absint( $this->form_data['id'] ),
						],
						$admin_url
					)
				),
				'text' => esc_html__( 'View Entries', 'wpforms-lite' ),
			];
		}

		if (
			! $this->is_form_template &&
			wpforms_current_user_can( wpforms_get_capability_manage_options(), $this->form_data['id'] ) &&
			wpforms()->obj( 'payment' )->get_by( 'form_id', $this->form_data['id'] )
		) {
				$links[] = [
					'url'  => esc_url(
						add_query_arg(
							[
								'page'    => 'wpforms-payments',
								'form_id' => absint( $this->form_data['id'] ),
							],
							$admin_url
						)
					),
					'text' => esc_html__( 'View Payments', 'wpforms-lite' ),
				];
		}

		if ( ! empty( $_GET['new_window'] ) ) { // phpcs:ignore
			$links[] = [
				'url'  => 'javascript:window.close();',
				'text' => esc_html__( 'Close this window', 'wpforms-lite' ),
			];
		}

		$content = '';

		$content .= $this->add_preview_notice();

		$content .= '<p>';
		$content .= $this->is_form_template ?
			esc_html__( 'This is a preview of the latest saved revision of your form template. If this preview does not match your template, save your changes and then refresh this page. This template preview is not publicly accessible.', 'wpforms-lite' ) :
			esc_html__( 'This is a preview of the latest saved revision of your form. If this preview does not match your form, save your changes and then refresh this page. This form preview is not publicly accessible.', 'wpforms-lite' );

		if ( ! empty( $links ) ) {
			$content .= '<br>';
			$content .= '<span class="wpforms-preview-notice-links">';

			foreach ( $links as $key => $link ) {
				$content .= '<a href="' . $link['url'] . '">' . $link['text'] . '</a>';
				$l        = array_keys( $links );

				if ( end( $l ) !== $key ) {
					$content .= ' <span style="display:inline-block;margin:0 6px;opacity: 0.5">|</span> ';
				}
			}

			$content .= '</span>';
		}
		$content .= '</p>';

		$content .= '<p>';
		$content .= sprintf(
			wp_kses(
				/* translators: %s - WPForms doc link. */
				__( 'For form testing tips, check out our <a href="%s" target="_blank" rel="noopener noreferrer">complete guide!</a>', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'target' => [],
						'rel'    => [],
					],
				]
			),
			esc_url(
				wpforms_utm_link(
					'https://wpforms.com/docs/how-to-properly-test-your-wordpress-forms-before-launching-checklist/',
					$this->is_form_template ? 'Form Template Preview' : 'Form Preview',
					'Form Testing Tips Documentation'
				)
			)
		);
		$content .= '</p>';

		$content .= do_shortcode( '[wpforms id="' . absint( $this->form_data['id'] ) . '"]' );

		return $content;
	}

	/**
	 * Add preview notice.
	 *
	 * @since 1.8.8
	 *
	 * @return string HTML content.
	 */
	private function add_preview_notice(): string {

		if ( ! $this->is_form_template ) {
			return '';
		}

		$content  = '<div class="wpforms-preview-notice">';
		$content .= sprintf(
			'<strong>%s</strong> %s',
			esc_html__( 'Heads up!', 'wpforms-lite' ),
			esc_html__( 'You\'re viewing a preview of a form template.', 'wpforms-lite' )
		);

		if ( wpforms()->is_pro() ) {
			/** This filter is documented in wpforms/src/Pro/Tasks/Actions/PurgeTemplateEntryTask.php */
			$delay = (int) apply_filters( 'wpforms_pro_tasks_actions_purge_template_entry_task_delay', DAY_IN_SECONDS ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

			$message = sprintf( /* translators: %s - time period, e.g. 24 hours. */
				__( 'Entries are automatically deleted after %s.', 'wpforms-lite' ),
				// The `- 1` hack is to avoid the "1 day" message in favor of "24 hours".
				human_time_diff( time(), time() + $delay - 1 )
			);

			$content .= sprintf( '<p>%s</p>', esc_html( $message ) );
		}

		$content .= '</div>';

		return wp_kses_post( $content );
	}

	/**
	 * Force page template types.
	 *
	 * @since 1.7.2
	 *
	 * @param array $templates A list of template candidates, in descending order of priority.
	 *
	 * @return array
	 */
	public function force_page_template_hierarchy( $templates ) {

		return [ 'page.php', 'single.php', 'index.php' ];
	}

	/**
	 * Adjust value of the {page_title} smart tag.
	 *
	 * @since 1.7.7
	 *
	 * @param string $content          Content.
	 * @param array  $form_data        Form data.
	 * @param array  $fields           List of fields.
	 * @param string $entry_id         Entry ID.
	 * @param object $smart_tag_object The smart tag object or the Generic object for those cases when class unregistered.
	 *
	 * @return string
	 */
	public function smart_tags_process_page_title_value( $content, $form_data, $fields, $entry_id, $smart_tag_object ) {

		return sprintf( /* translators: %s - form name. */
			esc_html__( '%s Preview', 'wpforms-lite' ),
			! empty( $form_data['settings']['form_title'] ) ? sanitize_text_field( $form_data['settings']['form_title'] ) : esc_html__( 'Form', 'wpforms-lite' )
		);
	}
}
