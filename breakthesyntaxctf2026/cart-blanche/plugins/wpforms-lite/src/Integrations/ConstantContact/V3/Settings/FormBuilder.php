<?php

namespace WPForms\Integrations\ConstantContact\V3\Settings;

use Exception;
use WPForms\Integrations\ConstantContact\V3\Api\Api;
use WPForms\Integrations\ConstantContact\V3\Auth;
use WPForms\Integrations\ConstantContact\V3\ConstantContact;
use WPForms\Providers\Provider\Settings\FormBuilder as FormBuilderAbstract;

/**
 * Class FormBuilder.
 *
 * @since 1.9.3
 */
class FormBuilder extends FormBuilderAbstract {


	/**
	 * Register all hooks (actions and filters) here.
	 *
	 * @since 1.9.3
	 */
	protected function init_hooks() { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		parent::init_hooks();

		add_filter(
			'wpforms_providers_settings_builder_ajax_connections_get_' . $this->core->slug,
			[ $this, 'ajax_connections_get' ]
		);

		if ( is_admin() ) {
			add_filter(
				"wpforms_providers_provider_settings_formbuilder_display_content_default_screen_{$this->core->slug}",
				[ $this, 'builder_settings_default_content' ]
			);
		}

		add_filter( 'wpforms_save_form_args', [ $this, 'save_form' ], 11, 3 );
	}

	/**
	 * Display content inside the panel sidebar area.
	 *
	 * @since 1.9.3
	 */
	public function display_sidebar() {

		if ( ConstantContact::get_current_version() !== 3 ) {
			return;
		}

		parent::display_sidebar();
	}

	/**
	 * Enqueue JavaScript and CSS files if needed.
	 * When extending - include the `parent::enqueue_assets();` not to break things!
	 *
	 * @since 1.9.3
	 */
	public function enqueue_assets() {

		parent::enqueue_assets();

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-constant-contact-v3-builder',
			WPFORMS_PLUGIN_URL . "assets/js/integrations/constant-contact-v3/builder{$min}.js",
			[ 'underscore', 'wpforms-admin-builder-providers', 'wpforms-constant-contact-v3-auth' ],
			WPFORMS_VERSION,
			true
		);
	}


	/**
	 * Pre-process provider data before saving it in form_data when editing a form.
	 *
	 * @since 1.9.3
	 *
	 * @param array|mixed $form Form array which is usable with `wp_update_post()`.
	 * @param array       $data Data retrieved from $_POST and processed.
	 * @param array       $args Empty by default. May have custom data not intended to be saved, but used for processing.
	 *
	 * @return array
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function save_form( $form, $data, $args ): array {

		$form = (array) $form;

		// Get a filtered (or modified by another addon) form content.
		$form_data = json_decode( stripslashes( $form['post_content'] ), true );

		// Provider exists.
		if ( ! empty( $form_data['providers'][ $this->core->slug ] ) ) {
			$modified_post_content = $this->modify_form_data( $form_data );

			if ( ! empty( $modified_post_content ) ) {
				$form['post_content'] = wpforms_encode( $modified_post_content );

				return $form;
			}
		}

		/*
		 * This part works when modification is locked or current filter was called on NOT a Providers panel.
		 * Then we need to restore provider connections from the previous form content.
		 */

		// Get a "previous" form content (current content is still not saved).
		$prev_form = ! empty( $data['id'] )
			? wpforms()->obj( 'form' )->get( $data['id'], [ 'content_only' => true ] )
			: [];

		if ( ! empty( $prev_form['providers'][ $this->core->slug ] ) ) {
			$provider = $prev_form['providers'][ $this->core->slug ];

			if ( ! isset( $form_data['providers'] ) ) {
				$form_data = array_merge( $form_data, [ 'providers' => [] ] );
			}

			$form_data['providers'] = array_merge( (array) $form_data['providers'], [ $this->core->slug => $provider ] );
			$form['post_content']   = wpforms_encode( $form_data );
		}

		return $form;
	}

	/**
	 * Prepare modifications for form content if it's not locked.
	 *
	 * @since 1.9.3
	 *
	 * @param array $form_data Form content.
	 *
	 * @return array|null
	 */
	protected function modify_form_data( array $form_data ) {

		/**
		 * The connection is locked.
		 * Why? User clicked the "Save" button when one of the AJAX requests
		 * for data retrieval from API was in progress or failed.
		 */
		if (
			isset( $form_data['providers'][ $this->core->slug ]['__lock__'] ) &&
			absint( $form_data['providers'][ $this->core->slug ]['__lock__'] ) === 1
		) {
			return null;
		}

		// Modify content as we need, done by reference.
		foreach ( $form_data['providers'][ $this->core->slug ] as $connection_id => $connection ) {
			if ( $connection_id === '__lock__' ) {
				unset( $form_data['providers'][ $this->core->slug ]['__lock__'] );
			}
		}

		return $form_data;
	}

	/**
	 * Rewrite the Add New Account button to trigger Auth popup instead of default authorization flow.
	 *
	 * @since 1.9.3
	 */
	protected function display_content_header() {

		if ( ! empty( wpforms_get_providers_options( $this->core->slug ) ) ) {
			parent::display_content_header();

			return;
		}

		?>

		<div class="wpforms-builder-provider-title wpforms-panel-content-section-title">
			<?php echo esc_html( $this->core->name ); ?>

			<button type="button" class="wpforms-builder-provider-title-add wpforms-builder-constant-contact-v3-provider-sign-up">
				<?php esc_html_e( 'Add New Account', 'wpforms-lite' ); ?>
			</button>
		</div>
		<?php
	}

	/**
	 * Get the list of all saved connections.
	 *
	 * @since 1.9.3
	 *
	 * @return array Return null on any kind of error. Array of data otherwise.
	 */
	public function ajax_connections_get(): array {

		$data = [
			'actions'        => [
				'subscribe'   => __( 'Subscribe', 'wpforms-lite' ),
				'unsubscribe' => __( 'Unsubscribe', 'wpforms-lite' ),
				'delete'      => __( 'Delete subscriber', 'wpforms-lite' ),
			],
			'actions_fields' => [
				'subscribe'   => [
					'email'         => [
						'label'    => __( 'Email', 'wpforms-lite' ),
						'type'     => 'select',
						'map'      => 'email',
						'required' => true,
					],
					'list'          => [
						'label'       => __( 'Select List', 'wpforms-lite' ),
						'type'        => 'select',
						'required'    => true,
						'placeholder' => __( '--- Select Mailing List ---', 'wpforms-lite' ),
					],
					'custom_fields' => [
						'label'    => __( 'Custom Fields', 'wpforms-lite' ),
						'type'     => 'custom-fields',
						'required' => false,
					],
				],
				'unsubscribe' => [
					'email'          => [
						'label'       => __( 'Email', 'wpforms-lite' ),
						'type'        => 'select',
						'map'         => 'email',
						'required'    => true,
						'placeholder' => __( '--- Select Form Field ---', 'wpforms-lite' ),
					],
					'opt_out_reason' => [
						'label'       => __( 'Reason', 'wpforms-lite' ),
						'type'        => 'select',
						'required'    => false,
						'placeholder' => __( '--- Select Form Field ---', 'wpforms-lite' ),
					],
				],
				'delete'      => [
					'email' => [
						'label'    => __( 'Email', 'wpforms-lite' ),
						'type'     => 'select',
						'map'      => 'email',
						'required' => true,
					],
				],
			],
			'connections'    => isset( $this->form_data['providers'][ $this->core->slug ] )
				? array_reverse( $this->form_data['providers'][ $this->core->slug ], true )
				: [],
			'conditionals'   => [],
		];

		foreach ( $data['connections'] as $connection ) {

			if ( empty( $connection['id'] ) ) {
				continue;
			}

			// This will either return an empty placeholder or complete set of rules, as a DOM.
			$data['conditionals'][ $connection['id'] ] = wpforms()->is_pro()
				? wpforms_conditional_logic()->builder_block(
					[
						'form'       => $this->form_data,
						'type'       => 'panel',
						'parent'     => 'providers',
						'panel'      => $this->core->slug,
						'subsection' => $connection['id'],
					],
					false
				)
				: '';
		}

		return array_merge( $data, $this->get_accounts_data() );
	}

	/**
	 * Get accounts data.
	 *
	 * @since 1.9.3
	 *
	 * @return array
	 */
	private function get_accounts_data(): array {

		$accounts = wpforms_get_providers_options( $this->core->slug );

		$data = [
			'accounts'      => $accounts,
			'custom_fields' => [],
			'lists'         => [],
		];

		if ( empty( $accounts ) ) {
			return $data;
		}

		$predefined_custom_fields = ConstantContact::get_predefined_custom_fields();

		foreach ( $accounts as $account_id => $account ) {
			try {
				$api = new Api( $account );

				$data['lists'][ $account_id ]         = $api->get_contact_list();
				$data['custom_fields'][ $account_id ] = array_merge( $predefined_custom_fields, $api->get_custom_fields( 'label', 'custom_field_id' ) );
			} catch ( Exception $e ) {
				continue;
			}
		}

		return $data;
	}

	/**
	 * Builder custom templates.
	 *
	 * @since 1.9.3
	 */
	public function builder_custom_templates() {

		$templates = [
			'connection',
			'error',
			'select-field',
		];

		foreach ( $templates as $template ) {
			$template_name = ucwords( str_replace( '-', ' ', $template ) );
			$script_id     = 'tmpl-wpforms-' . esc_attr( $this->core->slug ) . '-builder-content-connection';

			if ( $template !== 'connection' ) {
				$script_id .= '-' . $template;
			}
			?>
			<!-- Single Constant Contact connection block: <?php echo esc_attr( $template_name ); ?>. -->
			<script type="text/html" id="<?php echo esc_attr( $script_id ); ?>">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo wpforms_render(
					'integrations/constant-contact-v3/builder/' . $template,
					[
						'slug' => $this->core->slug,
					],
					true
				);
				?>
			</script>
		<?php
		}
	}

	/**
	 * Default content for the provider settings panel in the form builder.
	 *
	 * @since 1.9.3
	 *
	 * @param string $content Default content.
	 *
	 * @return string
	 * @noinspection HtmlUnknownTarget
	 */
	public function builder_settings_default_content( string $content ): string {

		ob_start();
		?>
		<p>
			<a
					href="<?php echo esc_url( Auth::get_auth_url() ); ?>"
					class="wpforms-btn wpforms-btn-md wpforms-btn-orange wpforms-builder-constant-contact-v3-provider-sign-up"
					target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Try Constant Contact for Free', 'wpforms-lite' ); ?>
			</a>
		</p>
		<p>
			<?php
			printf(
				'<a href="%1$s" target="_blank" rel="noopener noreferrer" class="secondary-text">%2$s</a>',
				esc_url( admin_url( 'admin.php?page=wpforms-page&view=constant-contact' ) ),
				esc_html__( 'Learn more about the power of email marketing.', 'wpforms-lite' )
			);
			?>
		</p>
		<?php

		return $content . ob_get_clean();
	}
}
