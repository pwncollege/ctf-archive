<?php

namespace WPForms\Admin\Traits;

use WPForms\Admin\Addons\Addons;
use WPForms\Admin\Builder\Templates;

/**
 * Form Templates trait.
 *
 * @since 1.7.7
 */
trait FormTemplates {

	/**
	 * Addons data handler class instance.
	 *
	 * @since 1.7.7
	 *
	 * @var Addons
	 */
	private $addons_obj;

	/**
	 * Is addon templates available?
	 *
	 * @since 1.7.7
	 *
	 * @var bool
	 */
	private $is_addon_templates_available = false;

	/**
	 * Is custom templates available?
	 *
	 * @since 1.7.7
	 *
	 * @var bool
	 */
	private $is_custom_templates_available = false;

	/**
	 * Prepared templates list.
	 *
	 * @since 1.7.7
	 *
	 * @var array
	 */
	private $prepared_templates = [];

	/**
	 * Output templates content section.
	 *
	 * @since 1.7.7
	 */
	private function output_templates_content() {

		$templates_hash        = wpforms()->obj( 'builder_templates' )->get_hash();
		$templates_hash_option = get_option( Templates::TEMPLATES_HASH_OPTION, '' );

		// Compare the current hash and the previous one to detect changes in the template list.
		if ( $templates_hash !== $templates_hash_option ) {
			// Update the hash in the option.
			update_option( Templates::TEMPLATES_HASH_OPTION, $templates_hash );

			// Wipe both caches - for the admin page and for the Form Builder.
			wpforms()->obj( 'builder_templates_cache' )->wipe_content_cache();
		}

		// Attempt to get cached content.
		$content = wpforms()->obj( 'builder_templates_cache' )->get_content_cache();

		if ( empty( $content ) ) {
			$content = $this->generate_templates_content_cache();
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $content;
	}

	/**
	 * Generate and save cached templates content.
	 *
	 * @since 1.8.6
	 *
	 * @retur string
	 */
	public function generate_templates_content_cache() {

		$this->prepare_templates_data();

		ob_start();
		?>

		<div class="wpforms-setup-templates">
			<div class="wpforms-setup-templates-sidebar">

				<div class="wpforms-setup-templates-search-wrap">
					<i class="fa fa-search"></i>
					<label>
						<input type="text" id="wpforms-setup-template-search" value="" placeholder="<?php esc_attr_e( 'Search Templates', 'wpforms-lite' ); ?>">
					</label>
				</div>

				<ul class="wpforms-setup-templates-categories">
					<?php $this->template_categories(); ?>
				</ul>

			</div>

			<div id="wpforms-setup-templates-list">
				<div class="list">
					<?php $this->template_select_options(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<div class="wpforms-templates-no-results">
					<p>
						<?php esc_html_e( "Sorry, we didn't find any templates that match your criteria.", 'wpforms-lite' ); ?>
					</p>
				</div>
				<div class="wpforms-user-templates-empty-state wpforms-hidden">
					<?php $this->user_template_empty_state(); ?>
				</div>
			</div>
		</div>
		<?php

		$content = ob_get_clean();

		wpforms()->obj( 'builder_templates_cache' )->save_content_cache( $content );

		return $content;
	}

	/**
	 * Prepare templates data for output.
	 *
	 * @since 1.7.7
	 */
	private function prepare_templates_data() {

		$templates = wpforms()->obj( 'builder_templates' )->get_templates();

		if ( empty( $templates ) ) {
			return;
		}

		wpforms()->obj( 'builder_templates' )->update_favorites_list();

		// Loop through each available template.
		foreach ( $templates as $id => $template ) {

			$this->prepared_templates[ $id ] = $this->prepare_template_render_arguments( $template );
		}
	}

	/**
	 * Generate and display categories menu.
	 *
	 * @since 1.7.7
	 */
	private function template_categories() {

		$templates_count = $this->get_count_in_categories();

		$generic_categories = [
			'all' => esc_html__( 'All Templates', 'wpforms-lite' ),
		];

		if ( isset( $templates_count['all'], $templates_count['available'] ) && $templates_count['all'] !== $templates_count['available'] ) {
			$generic_categories['available'] = esc_html__( 'Available Templates', 'wpforms-lite' );
		}

		$generic_categories['favorites'] = esc_html__( 'Favorite Templates', 'wpforms-lite' );
		$generic_categories['new']       = esc_html__( 'New Templates', 'wpforms-lite' );
		$generic_categories['user']      = esc_html__( 'My Templates', 'wpforms-lite' );

		$this->output_categories( $generic_categories, $templates_count );

		printf( '<li class="divider"></li>' );

		$common_categories = [];

		if ( $this->is_custom_templates_available ) {
			$common_categories['custom'] = esc_html__( 'Custom Templates', 'wpforms-lite' );
		}

		if ( $this->is_addon_templates_available ) {
			$common_categories['addons'] = esc_html__( 'Addon Templates', 'wpforms-lite' );
		}

		$categories = array_merge(
			$common_categories,
			wpforms()->obj( 'builder_templates' )->get_categories()
		);

		$this->output_categories( $categories, $templates_count );
	}

	/**
	 * Output categories list.
	 *
	 * @since 1.7.7
	 *
	 * @param array $categories      Categories list.
	 * @param array $templates_count Templates count by categories.
	 *
	 * @noinspection HtmlUnknownAttribute*/
	private function output_categories( $categories, $templates_count ) {

		$all_subcategories = wpforms()->obj( 'builder_templates' )->get_subcategories();

		foreach ( $categories as $slug => $name ) {

			$class = '';

			if ( $slug === 'all' ) {
				$class = 'class="active"';
			} elseif ( empty( $templates_count[ $slug ] ) && $slug !== 'user' ) { // WPForms user templates are always available.
				$class = 'class="wpforms-hidden"';
			}

			$count = $templates_count[ $slug ] ?? '0';

			printf(
				'<li data-category="%1$s" %2$s data-count="%4$s"><div>%3$s<span>%4$s</span><i class="fa fa-chevron-down chevron"></i></div>%5$s</li>',
				esc_attr( $slug ),
				$class, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				esc_html( $name ),
				esc_html( $count ),
				$this->output_subcategories( $all_subcategories, $slug, $templates_count['subcategories'] ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			);
		}
	}

	/**
	 * Output subcategories list.
	 *
	 * @since 1.8.4
	 *
	 * @param array $all_subcategories   Subcategories list.
	 * @param array $parent_slug         Parent category slug.
	 * @param array $subcategories_count Subcategories count.
	 */
	private function output_subcategories( $all_subcategories, $parent_slug, $subcategories_count ) {

		$subcategories = [];
		$output        = '';

		foreach ( $all_subcategories as $subcategory_slug => $subcategory ) {
			if ( $subcategory['parent'] === $parent_slug ) {
				$subcategories[ $subcategory_slug ] = $subcategory;
			}
		}

		if ( ! empty( $subcategories ) ) {
			$output .= '<ul class="wpforms-setup-templates-subcategories">';

			foreach ( $subcategories as $slug => $subcategory ) {
				$count = $subcategories_count[ $slug ] ?? '0';

				$output .= sprintf(
					'<li data-subcategory="%1$s"><span>%2$s</span><span>%3$s</span></li>',
					esc_attr( $slug ),
					esc_html( $subcategory['name'] ),
					esc_html( $count )
				);
			}

			$output .= '</ul>';
		}

		return $output;
	}

	/**
	 * Generate a block of templates to choose from.
	 *
	 * @since 1.7.7
	 *
	 * @param array  $templates Deprecated.
	 * @param string $slug      Deprecated.
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function template_select_options( $templates = [], $slug = '' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		/**
		 * Action hook before the list of form templates.
		 *
		 * @since 1.9.3
		 *
		 * @param array $templates List of form templates.
		 */
		do_action( 'wpforms_admin_form_templates_list_before', $templates );

		/**
		 * Filter the number of templates to display.
		 *
		 * Useful for speeding up the setup panel loading while debugging.
		 *
		 * @since 1.9.2
		 *
		 * @param int|bool $limit Number of templates to display.
		 */
		$limit = apply_filters( 'wpforms_builder_setup_templates_limit', false );

		if ( $limit ) {
			$this->prepared_templates = array_slice( $this->prepared_templates, 0, (int) $limit, true );
		}

		foreach ( $this->prepared_templates as $template ) {

			echo wpforms_render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'builder/templates-item',
				$template,
				true
			);
		}
	}

	/**
	 * Output user templates empty state.
	 *
	 * @since 1.8.8
	 */
	private function user_template_empty_state() {

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render( 'admin/empty-states/no-user-templates' );
	}

	/**
	 * Prepare arguments for rendering template item.
	 *
	 * @since 1.7.7
	 *
	 * @param array $template Template data.
	 *
	 * @return array Arguments.
	 */
	private function prepare_template_render_arguments( $template ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		$template['plugin_dir'] = $template['plugin_dir'] ?? '';
		$template['source']     = $this->get_template_source( $template );
		$template['url']        = ! empty( $template['url'] ) ? $template['url'] : '';
		$template['has_access'] = ! empty( $template['license'] ) ? $template['has_access'] : true;
		$template['favorite']   = $template['favorite'] ?? wpforms()->obj( 'builder_templates' )->is_favorite( $template['slug'] );

		$args = [];

		$args['template_id']   = ! empty( $template['id'] ) ? $template['id'] : $template['slug'];
		$args['categories']    = $this->get_template_categories( $template );
		$args['subcategories'] = $this->get_template_subcategories( $template );
		$args['fields']        = $this->get_template_fields( $template );
		$args['demo_url']      = '';

		if ( ! empty( $template['url'] ) ) {
			$medium           = wpforms_is_admin_page( 'templates' ) ? 'Form Templates Subpage' : 'builder-templates';
			$args['demo_url'] = wpforms_utm_link( $template['url'], $medium, $template['name'] );
		}

		$template_license = ! empty( $template['license'] ) ? $template['license'] : '';
		$template_name    = sprintf( /* translators: %s - form template name. */
			esc_html__( '%s template', 'wpforms-lite' ),
			esc_html( $template['name'] )
		);

		$args['badge_text']           = '';
		$args['license_class']        = '';
		$args['education_class']      = '';
		$args['education_attributes'] = '';

		if ( $template['source'] === 'wpforms-addon' ) {
			$args['badge_text'] = esc_html__( 'Addon', 'wpforms-lite' );

			// At least one addon template available.
			$this->is_addon_templates_available = true;
		}

		if ( $template['source'] === 'wpforms-custom' ) {
			$args['badge_text'] = esc_html__( 'Custom', 'wpforms-lite' );

			// At least one custom template available.
			$this->is_custom_templates_available = true;
		}

		$args['create_url']       = '';
		$args['edit_url']         = '';
		$args['edit_action_text'] = '';
		$args['is_open']          = false;

		$args['can_create'] = wpforms_current_user_can( 'create_forms' );
		$args['can_edit']   = wpforms_current_user_can( 'edit_forms' );
		$args['can_delete'] = wpforms_current_user_can( 'delete_forms' );
		$args['post_id']    = ! empty( $template['post_id'] ) ? $template['post_id'] : '';

		if ( $template['source'] === 'wpforms-user-template' ) {

			$args['create_url']       = esc_url( $template['create_url'] );
			$args['edit_url']         = esc_url( $template['edit_url'] );
			$args['edit_action_text'] = $template['edit_action_text'];

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$args['is_open'] = wpforms_is_admin_page( 'builder' ) && isset( $_GET['form_id'] ) && (int) $_GET['form_id'] === $template['post_id'];

			$ownership = get_current_user_id() === (int) get_post_field( 'post_author', $args['post_id'] ) ? 'own' : 'others';

			$args['can_edit']   = wpforms_current_user_can( "edit_{$ownership}_forms", $args['post_id'] );
			$args['can_delete'] = wpforms_current_user_can( "delete_{$ownership}_forms", $args['post_id'] );
		}

		$args['action_text'] = $this->get_action_button_text( $template );

		if ( empty( $template['has_access'] ) ) {
			$args['license_class']        = ' pro';
			$args['badge_text']           = $template_license;
			$args['education_class']      = ' education-modal';
			$args['education_attributes'] = sprintf(
				' data-name="%1$s" data-license="%2$s" data-action="upgrade"',
				esc_attr( $template_name ),
				esc_attr( $template_license )
			);
		}

		$args['addons_attributes'] = $this->prepare_addons_attributes( $template );

		$args['selected']    = ! empty( $this->form_data['meta']['template'] ) && $this->form_data['meta']['template'] === $args['template_id'];
		$args['badge_class'] = ! empty( $args['badge_text'] ) ? ' badge' : '';
		$args['template']    = $template;

		return $args;
	}

	/**
	 * Get action button text.
	 *
	 * @since 1.7.7
	 *
	 * @param array $template Template data.
	 *
	 * @return string
	 */
	private function get_action_button_text( $template ) {

		if ( ! empty( $template['action_text'] ) ) {
			return $template['action_text'];
		}

		if ( $template['slug'] === 'blank' ) {
			return __( 'Create Blank Form', 'wpforms-lite' );
		}

		if ( wpforms_is_admin_page( 'templates' ) ) {
			return __( 'Create Form', 'wpforms-lite' );
		}

		return __( 'Use Template', 'wpforms-lite' );
	}

	/**
	 * Generate addon attributes.
	 *
	 * @since 1.7.7
	 *
	 * @param array $template Template data.
	 *
	 * @return string Addon attributes.
	 */
	private function prepare_addons_attributes( $template ) {

		$addons_attributes = '';
		$required_addons   = false;
		$already_installed = [];

		if ( ! empty( $template['addons'] ) && is_array( $template['addons'] ) ) {
			$required_addons = $this->addons_obj->get_by_slugs( $template['addons'] );

			foreach ( $required_addons as $i => $addon ) {
				if (
					! isset( $addon['action'], $addon['title'], $addon['slug'] ) ||
					! in_array( $addon['action'], [ 'install', 'activate' ], true )
				) {
					unset( $required_addons[ $i ] );
				}

				if ( $addon['action'] === 'activate' ) {
					$already_installed[] = $addon['slug'];
				}
			}
		}

		if ( ! empty( $required_addons ) ) {
			$addons_names    = implode( ', ', wp_list_pluck( $required_addons, 'title' ) );
			$addons_slugs    = implode( ',', wp_list_pluck( $required_addons, 'slug' ) );
			$installed_slugs = implode( ',', $already_installed );

			$addons_attributes = sprintf(
				' data-addons-names="%1$s" data-addons="%2$s" data-installed="%3$s"',
				esc_attr( $addons_names ),
				esc_attr( $addons_slugs ),
				esc_attr( $installed_slugs )
			);
		}

		return $addons_attributes;
	}

	/**
	 * Determine a template source.
	 *
	 * @since 1.7.7
	 *
	 * @param array $template Template data.
	 *
	 * @return string Template source.
	 */
	private function get_template_source( $template ) {

		if ( ! empty( $template['source'] ) ) {
			return $template['source'];
		}

		$source = 'wpforms-addon';

		static $addons = null;

		if ( $addons === null ) {
			$addons = array_keys( $this->addons_obj->get_all() );
		}

		if ( $template['plugin_dir'] === 'wpforms' || $template['plugin_dir'] === 'wpforms-lite' ) {
			$source = 'wpforms-core';
		}

		if ( $source !== 'wpforms-core' && ! in_array( $template['plugin_dir'], $addons, true ) ) {
			$source = 'wpforms-custom';
		}

		return $source;
	}

	/**
	 * Determine template categories.
	 *
	 * @since 1.7.7
	 *
	 * @param array $template Template data.
	 *
	 * @return string Template categories coma separated.
	 */
	private function get_template_categories( $template ) {

		$categories = ! empty( $template['categories'] ) ? (array) $template['categories'] : [];
		$source     = $this->get_template_source( $template );

		if ( $source === 'wpforms-addon' ) {
			$categories[] = 'addons';
		}

		if ( $source === 'wpforms-custom' ) {
			$categories[] = 'custom';
		}

		if ( isset( $template['created_at'] ) && strtotime( $template['created_at'] ) > strtotime( '-3 Months' ) ) {
			$categories[] = 'new';
		}

		return implode( ',', $categories );
	}

	/**
	 * Determine template subcategories.
	 *
	 * @since 1.8.4
	 *
	 * @param array $template Template data.
	 *
	 * @return string Template subcategories coma separated.
	 */
	private function get_template_subcategories( $template ) {

		$subcategories = ! empty( $template['subcategories'] ) ? (array) $template['subcategories'] : [];
		$subcategories = array_keys( $subcategories );

		return implode( ',', $subcategories );
	}

	/**
	 * Determine template fields.
	 *
	 * @since 1.8.6
	 *
	 * @param array $template Template data.
	 *
	 * @return string Template fields, comma separated.
	 */
	private function get_template_fields( array $template ): string {

		$fields = ! empty( $template['fields'] ) ? (array) $template['fields'] : [];

		/**
		 * Filter template fields.
		 *
		 * @since 1.8.6
		 *
		 * @param array $fields Template fields.
		 */
		$fields = (array) apply_filters( 'wpforms_setup_template_fields', $fields );

		return implode( ',', $fields );
	}

	/**
	 * Get categories templates count.
	 *
	 * @since 1.7.7
	 *
	 * @return array
	 */
	private function get_count_in_categories() {

		$all_categories            = [];
		$available_templates_count = 0;
		$favorites_templates_count = 0;
		$user_templates_count      = 0;

		foreach ( $this->prepared_templates as $template_data ) {

			$template   = $template_data['template'];
			$categories = explode( ',', $template_data['categories'] );

			if ( $template['has_access'] ) {
				++$available_templates_count;
			}

			if ( $template['favorite'] ) {
				++$favorites_templates_count;
			}

			if ( $template['source'] === 'wpforms-user-template' ) {
				++$user_templates_count;
			}

			if ( is_array( $categories ) ) {
				array_push( $all_categories, ...$categories );
				continue;
			}

			$all_categories[] = $categories;
		}

		$categories_count                  = array_count_values( $all_categories );
		$categories_count['all']           = count( $this->prepared_templates );
		$categories_count['available']     = $available_templates_count;
		$categories_count['favorites']     = $favorites_templates_count;
		$categories_count['user']          = $user_templates_count;
		$categories_count['subcategories'] = $this->get_count_in_subcategories();

		return $categories_count;
	}

	/**
	 * Get subcategories templates count.
	 *
	 * @since 1.8.7
	 *
	 * @return array
	 */
	private function get_count_in_subcategories(): array {

		$all_subcategories = [];

		foreach ( $this->prepared_templates as $template_data ) {

			$subcategories = explode( ',', $template_data['subcategories'] );

			if ( is_array( $subcategories ) ) {
				array_push( $all_subcategories, ...$subcategories );
				continue;
			}

			$all_subcategories[] = $subcategories;
		}

		return array_count_values( $all_subcategories );
	}
}
