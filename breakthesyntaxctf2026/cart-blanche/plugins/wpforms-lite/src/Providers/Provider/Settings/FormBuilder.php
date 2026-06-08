<?php

namespace WPForms\Providers\Provider\Settings;

use WPForms\Providers\Provider\Core;
use WPForms\Providers\Provider\Status;

/**
 * Class FormBuilder handles functionality inside the form builder.
 *
 * @since 1.4.7
 */
abstract class FormBuilder implements FormBuilderInterface {

	/**
	 * Get the Core loader class of a provider.
	 *
	 * @since 1.4.7
	 *
	 * @var Core
	 */
	protected $core;

	/**
	 * Most Marketing providers will have a 'connection' type.
	 * Payment providers may have (or not) something different.
	 *
	 * @since 1.4.7
	 *
	 * @var string
	 */
	protected $type = 'connection';

	/**
	 * Form data and settings.
	 *
	 * @since 1.4.7
	 *
	 * @var array
	 */
	protected $form_data = [];

	/**
	 * Integrations constructor.
	 *
	 * @since 1.4.7
	 *
	 * @param Core $core Core provider class.
	 */
	public function __construct( Core $core ) {

		$this->core = $core;

		$form_obj = wpforms()->obj( 'form' );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;

		if ( $form_obj && $form_id ) {
			$this->form_data = $form_obj->get( $form_id, [ 'content_only' => true ] );

			// Form ID isn't defined for newly created forms.
			if ( empty( $this->form_data['id'] ) && is_array( $this->form_data ) ) {
				$this->form_data['id'] = $form_id;
			}
		}

		$this->init_hooks();
	}

	/**
	 * Register all hooks (actions and filters) here.
	 *
	 * @since 1.4.7
	 */
	protected function init_hooks() { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		// Register builder HTML template(s).
		add_action( 'wpforms_builder_print_footer_scripts', [ $this, 'builder_templates' ] );
		add_action( 'wpforms_builder_print_footer_scripts', [ $this, 'builder_custom_templates' ], 11 );

		// Process builder AJAX requests.
		add_action( "wp_ajax_wpforms_builder_provider_ajax_{$this->core->slug}", [ $this, 'process_ajax' ] );

		/*
		 * Enqueue assets.
		 */
		if (
			( ! empty( $_GET['page'] ) && $_GET['page'] === 'wpforms-builder' ) && // phpcs:ignore
			! empty( $_GET['form_id'] ) && // phpcs:ignore
			is_admin()
		) {
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		}

		add_filter( 'wpforms_save_form_args', [ $this, 'remove_connection_locks' ], 1, 3 );
	}

	/**
	 * Used to register generic templates for all providers inside form builder.
	 *
	 * @since 1.4.7
	 * @since 1.6.2 Added sub-templates for conditional logic based on provider.
	 */
	public function builder_templates(): void {

		$cl_builder_block =
			wpforms()->is_pro() ?
				wpforms_conditional_logic()->builder_block(
					[
						'form'       => $this->form_data,
						'type'       => 'panel',
						'parent'     => 'providers',
						'panel'      => esc_attr( $this->core->slug ),
						'subsection' => '%connection_id%',
					],
					false
				) :
				'';
		?>

		<!-- Single connection block sub-template: FIELDS -->
		<script type="text/html" id="tmpl-wpforms-providers-builder-content-connection-fields">
			<div class="wpforms-builder-provider-connection-block wpforms-builder-provider-connection-fields">
				<h4><?php esc_html_e( 'Custom Fields', 'wpforms-lite' ); ?></h4>
				<table class="wpforms-builder-provider-connection-fields-table wpforms-undo-redo-container">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Custom Field Name', 'wpforms-lite' ); ?></th>
							<th colspan="3"><?php esc_html_e( 'Form Field Value', 'wpforms-lite' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<# if ( ! _.isEmpty( data.connection.fields_meta ) ) { #>
							<# _.each( data.connection.fields_meta, function( item, meta_id ) { #>
								<tr class="wpforms-builder-provider-connection-fields-table-row">
									<td>
										<?php
											// data.hideCustomMetaInput property is used when there are no registered custom fields,
											// but select field should be shown instead of input.
										?>
										<# if ( data.hideCustomMetaInput || ! _.isEmpty( data.provider.fields ) ) { #>
											<select class="wpforms-builder-provider-connection-field-name"
												name="providers[{{ data.provider.slug }}][{{ data.connection.id }}][fields_meta][{{ meta_id }}][name]"
												<# if ( _.isEmpty( data.provider.fields ) ) { #>disabled<# } #>>
												<option value=""><# if ( ! _.isEmpty( data.provider.placeholder ) ) { #>{{ data.provider.placeholder }}<# } else { #><?php esc_html_e( '--- Select Field ---', 'wpforms-lite' ); ?><# } #></option>

												<# _.each( data.provider.fields, function( field_name, field_id ) { #>
													<option value="{{ field_id }}"
														<# if ( field_id === item.name ) { #>selected="selected"<# } #>
													>
														{{ field_name }}
													</option>
												<# } ); #>

											</select>
										<# } else { #>
											<input type="text" value="{{ item.name }}"
												class="wpforms-builder-provider-connection-field-name"
												name="providers[{{ data.provider.slug }}][{{ data.connection.id }}][fields_meta][{{ meta_id }}][name]"
												placeholder="<?php esc_attr_e( 'Field Name', 'wpforms-lite' ); ?>"
											/>
										<# } #>
									</td>
									<td>
										<select class="wpforms-builder-provider-connection-field-value" data-support-subfields="{{ data.isSupportSubfields }}"
											name="providers[{{ data.provider.slug }}][{{ data.connection.id }}][fields_meta][{{ meta_id }}][field_id]">
											<option value=""><?php esc_html_e( '--- Select Form Field ---', 'wpforms-lite' ); ?></option>

											<# _.each( data.fields, function( field, key ) {
												const fieldId = field.id.toString();
												const itemId  = item.field_id.toString();
												isSelected    = fieldId === itemId
													<?php // BC: Previously saved name fields don't have the `.full` suffix in DB. ?>
													|| ( ! itemId.includes('.') && fieldId === itemId + '.full' );
												#>
												<option value="{{ fieldId }}"<# if ( isSelected ) { #> selected="selected"<# } #>>
													<# if ( ! _.isUndefined( field.label ) && field.label.toString().trim() !== '' ) { #>
														{{ field.label.toString().trim() }}
													<# } else { #>
														{{ wpforms_builder.field + ' #' + key }}
													<# } #>
												</option>
											<# } ); #>
										</select>
									</td>
									<td class="add">
										<button class="button-secondary js-wpforms-builder-provider-connection-fields-add <# if ( _.isEmpty( data.provider.fields ) ) { #>wpforms-disabled<# } #>"
										        title="<?php esc_attr_e( 'Add Another', 'wpforms-lite' ); ?>">
											<i class="fa fa-plus-circle"></i>
										</button>
									</td>
									<td class="delete">
										<button class="button js-wpforms-builder-provider-connection-fields-delete <# if ( meta_id === 0 ) { #>hidden<# } #>"
										        title="<?php esc_attr_e( 'Remove', 'wpforms-lite' ); ?>">
											<i class="fa fa-minus-circle"></i>
										</button>
									</td>
								</tr>
							<# } ); #>
						<# } else { #>
							<tr class="wpforms-builder-provider-connection-fields-table-row">
								<td>
									<# if ( data.hideCustomMetaInput || ! _.isEmpty( data.provider.fields ) ) { #>
										<select class="wpforms-builder-provider-connection-field-name"
											name="providers[{{ data.provider.slug }}][{{ data.connection.id }}][fields_meta][0][name]"
											<# if ( _.isEmpty( data.provider.fields ) ) { #>disabled<# } #>>
											<option value=""><# if ( ! _.isEmpty( data.provider.placeholder ) ) { #>{{ data.provider.placeholder }}<# } else { #><?php esc_html_e( '--- Select Field ---', 'wpforms-lite' ); ?><# } #></option>

											<# _.each( data.provider.fields, function( field_name, field_id ) { #>
												<option value="{{ field_id }}">
													{{ field_name }}
												</option>
											<# } ); #>

										</select>
									<# } else { #>
										<input type="text" value=""
											class="wpforms-builder-provider-connection-field-name"
											name="providers[{{ data.provider.slug }}][{{ data.connection.id }}][fields_meta][0][name]"
											placeholder="<?php esc_attr_e( 'Field Name', 'wpforms-lite' ); ?>"
										/>
									<# } #>
								</td>
								<td>
									<select class="wpforms-builder-provider-connection-field-value"
										name="providers[{{ data.provider.slug }}][{{ data.connection.id }}][fields_meta][0][field_id]">
										<option value=""><?php esc_html_e( '--- Select Form Field ---', 'wpforms-lite' ); ?></option>

										<# _.each( data.fields, function( field, key ) { #>
											<option value="{{ field.id }}">
												<# if ( ! _.isUndefined( field.label ) && field.label.toString().trim() !== '' ) { #>
													{{ field.label.toString().trim() }}
												<# } else { #>
													{{ wpforms_builder.field + ' #' + key }}
												<# } #>
											</option>
										<# } ); #>
									</select>
								</td>
								<td class="add">
									<button class="button-secondary js-wpforms-builder-provider-connection-fields-add <# if ( _.isEmpty( data.provider.fields ) ) { #>wpforms-disabled<# } #>"
									        title="<?php esc_attr_e( 'Add Another', 'wpforms-lite' ); ?>">
										<i class="fa fa-plus-circle"></i>
									</button>
								</td>
								<td class="delete">
									<button class="button js-wpforms-builder-provider-connection-fields-delete hidden"
									        title="<?php esc_attr_e( 'Delete', 'wpforms-lite' ); ?>">
										<i class="fa fa-minus-circle"></i>
									</button>
								</td>
							</tr>
						<# } #>
					</tbody>
				</table><!-- /.wpforms-builder-provider-connection-fields-table -->

				<p class="description">
					<?php esc_html_e( 'Map custom fields (or properties) to form fields values.', 'wpforms-lite' ); ?>
				</p>

			</div><!-- /.wpforms-builder-provider-connection-fields -->
		</script>

		<!-- Single connection block sub-template: CONDITIONAL LOGIC -->
		<script type="text/html" id="tmpl-wpforms-<?php echo esc_attr( $this->core->slug ); ?>-builder-content-connection-conditionals">
			<?php echo $cl_builder_block; // phpcs:ignore ?>
		</script>

		<!-- DEPRECATED: Should be removed when we make changes in our addons. -->
		<script type="text/html" id="tmpl-wpforms-providers-builder-content-connection-conditionals">
			<?php echo $cl_builder_block; // phpcs:ignore ?>
		</script>
		<?php

		$this->builder_error_template();
	}

	/**
	 * Enqueue the JavaScript and CSS files if needed.
	 * When extending - include the `parent::enqueue_assets();` not to break things!
	 *
	 * @since 1.4.7
	 */
	public function enqueue_assets() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-admin-builder-templates',
			WPFORMS_PLUGIN_URL . "assets/js/admin/builder/templates{$min}.js",
			[ 'wp-util' ],
			WPFORMS_VERSION,
			true
		);

		wp_enqueue_script(
			'wpforms-admin-builder-providers',
			WPFORMS_PLUGIN_URL . "assets/js/admin/builder/providers{$min}.js",
			[ 'wpforms-utils', 'wpforms-builder', 'wpforms-admin-builder-templates' ],
			WPFORMS_VERSION,
			true
		);
	}

	/**
	 * Process the Builder AJAX requests.
	 *
	 * @since 1.4.7
	 */
	public function process_ajax(): void {

		// Run a security check.
		check_ajax_referer( 'wpforms-builder', 'nonce' );

		// Check for permissions.
		if ( ! wpforms_current_user_can( 'edit_forms' ) ) {
			wp_send_json_error(
				[
					'error' => esc_html__( 'You do not have permission to perform this action.', 'wpforms-lite' ),
				]
			);
		}

		// Process required values.
		$error = [ 'error' => esc_html__( 'Something went wrong while performing an AJAX request.', 'wpforms-lite' ) ];

		if (
			empty( $_POST['id'] ) ||
			empty( $_POST['task'] )
		) {
			wp_send_json_error( $error );
		}

		$form_id = (int) $_POST['id'];
		$task    = sanitize_key( $_POST['task'] );

		$revisions = wpforms()->obj( 'revisions' );
		$revision  = $revisions ? $revisions->get_revision() : null;

		if ( $revision ) {
			// Set up form data based on the revision_id that we got from AJAX request.
			$this->form_data = wpforms_decode( $revision->post_content );
		} else {
			// Set up form data based on the ID that we got from AJAX request.
			$form_handler    = wpforms()->obj( 'form' );
			$this->form_data = $form_handler ? $form_handler->get( $form_id, [ 'content_only' => true ] ) : [];
		}

		// Do not allow proceeding further, as form_id may be incorrect.
		if ( empty( $this->form_data ) ) {
			wp_send_json_error( $error );
		}

		$data = apply_filters( // phpcs:ignore WPForms.Comments.PHPDocHooks.RequiredHookDocumentation, WPForms.PHP.ValidateHooks.InvalidHookName
			'wpforms_providers_settings_builder_ajax_' . $task . '_' . $this->core->slug,
			null
		);

		if ( ! empty( $data['error_msg'] ) ) {
			wp_send_json_error( [ 'error_msg' => $data['error_msg'] ] );
		}

		if ( $data !== null ) {
			wp_send_json_success( $data );
		}

		wp_send_json_error( $error );
	}

	/**
	 * Display content inside the panel sidebar area.
	 *
	 * @since 1.4.7
	 */
	public function display_sidebar() {

		$configured = '';

		if ( ! empty( $this->form_data['id'] ) && Status::init( $this->core->slug )->is_ready( $this->form_data['id'] ) ) {
			$configured = 'configured';
		}

		$classes = [
			'wpforms-panel-sidebar-section',
			'icon',
			$configured,
			'wpforms-panel-sidebar-section-' . $this->core->slug,
		];
		?>

		<a
				href="#" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
				data-section="<?php echo esc_attr( $this->core->slug ); ?>">

			<img src="<?php echo esc_url( $this->core->icon ); ?>" alt="icon">

			<?php echo esc_html( $this->core->name ); ?>

			<i class="fa fa-angle-right wpforms-toggle-arrow"></i>

			<?php if ( ! empty( $configured ) ) : ?>
				<i class="fa fa-check-circle-o"></i>
			<?php endif; ?>

		</a>

		<?php
	}

	/**
	 * Wrap the builder section content with the required (for tabs switching) markup.
	 *
	 * @since 1.4.7
	 */
	public function display_content() {
		?>

		<div class="wpforms-panel-content-section wpforms-builder-provider wpforms-panel-content-section-<?php echo esc_attr( $this->core->slug ); ?>" id="<?php echo esc_attr( $this->core->slug ); ?>-provider" data-provider="<?php echo esc_attr( $this->core->slug ); ?>" data-provider-name="<?php echo esc_attr( $this->core->name ); ?>">

			<!-- Provider content goes here. -->
			<?php

			$this->display_content_header();

			$form_id = ! empty( $this->form_data['id'] ) ? $this->form_data['id'] : '';

			self::display_content_default_screen(
				Status::init( $this->core->slug )->is_ready( $form_id ),
				$this->core->slug,
				$this->core->name,
				$this->core->icon
			);

			$this->display_lock_field();
			?>

			<div class="wpforms-builder-provider-body">
				<div class="wpforms-provider-connections-wrap wpforms-clear">
					<div class="wpforms-builder-provider-connections"></div>
				</div>
			</div>
		</div>

		<?php
	}

	/**
	 * Display provider default screen.
	 *
	 * @since 1.6.8
	 *
	 * @param bool   $is_connected True if connections are configured.
	 * @param string $slug         Provider slug.
	 * @param string $name         Provider name.
	 * @param string $icon         Provider icon.
	 */
	public static function display_content_default_screen( $is_connected, $slug, $name, $icon ): void {

		// Hide the provider default settings screen when it's already connected.
		$class = $is_connected ? ' wpforms-hidden' : '';

		?>
		<div class="wpforms-builder-provider-connections-default<?php echo esc_attr( $class ); ?>">
			<img src="<?php echo esc_url( $icon ); ?>" alt="">
			<div class="wpforms-builder-provider-settings-default-content">
				<?php
				/*
				 * Allows developers to change the default content of the provider's settings default screen.
				 *
				 * @since 1.6.8
				 *
				 * @param string $content Content of the provider's settings default screen.
				 */
				echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WPForms.Comments.PHPDocHooks.RequiredHookDocumentation, WPForms.PHP.ValidateHooks.InvalidHookName
					"wpforms_providers_provider_settings_formbuilder_display_content_default_screen_{$slug}",
					sprintf( /* translators: %s - provider name. */
						'<p>' . esc_html__( 'Get the most out of WPForms &mdash; use it with an active %s account.', 'wpforms-lite' ) . '</p>',
						esc_html( $name )
					)
				);
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Display the lock field.
	 *
	 * @since 1.8.9
	 */
	protected function display_lock_field(): void {

		if ( ! $this->is_lock_field_required( $this->core->slug ) ) {
			return;
		}

		?>
		<input
				type="hidden" class="wpforms-builder-provider-connections-save-lock" value="1"
				name="providers[<?php echo esc_attr( $this->core->slug ); ?>][__lock__]">
		<?php
	}

	/**
	 * Section content header.
	 *
	 * @since 1.4.7
	 */
	protected function display_content_header() {

		$provider_status = Status::init( $this->core->slug );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;

		$is_configured = $provider_status->is_configured();
		$is_connected  = $provider_status->is_ready( $form_id );
		?>

		<div class="wpforms-builder-provider-title wpforms-panel-content-section-title">

			<?php echo esc_html( $this->core->name ); ?>

			<span class="wpforms-builder-provider-title-spinner <?php echo $is_connected ? '' : 'wpforms-hidden'; ?>">
				<i class="wpforms-loading-spinner wpforms-loading-md wpforms-loading-inline"></i>
			</span>

			<button class="wpforms-builder-provider-title-add js-wpforms-builder-provider-connection-add <?php echo $is_configured ? '' : 'hidden'; ?>"
			        data-form_id="<?php echo esc_attr( $form_id ); ?>"
			        data-provider="<?php echo esc_attr( $this->core->slug ); ?>">
				<?php esc_html_e( 'Add New Connection', 'wpforms-lite' ); ?>
			</button>

			<button class="wpforms-builder-provider-title-add js-wpforms-builder-provider-account-add <?php echo ! $is_configured ? '' : 'hidden'; ?>"
			        data-form_id="<?php echo esc_attr( $form_id ); ?>"
			        data-provider="<?php echo esc_attr( $this->core->slug ); ?>">
				<?php esc_html_e( 'Add New Account', 'wpforms-lite' ); ?>
			</button>

		</div>

		<?php
	}

	/**
	 * Determine whether the lock field is required.
	 *
	 * @WPFormsBackCompat Support Drip v1.7.0 and earlier, support Uncanny Automator.
	 *
	 * @since 1.8.9
	 *
	 * @param string $provider The provider slug.
	 *
	 * @return bool
	 */
	protected function is_lock_field_required( string $provider ): bool {

		// Compatibility with the legacy Drip addon versions where the lock field was unnecessary.
		// Uncanny Automator does not have a lock field.
		if ( in_array( $provider, [ 'uncanny-automator', 'drip' ], true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Temporary fix to remove __lock__ field with value 1 from the form post_content.
	 * In the future, it will be handled in the save_form () method in the core for all providers.
	 *
	 * @since 1.8.9
	 *
	 * @param array|mixed $form Form array, usable with wp_update_post.
	 * @param array       $data Data retrieved from $_POST and processed.
	 * @param array       $args Update form arguments.
	 *
	 * @return array
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function remove_connection_locks( $form, $data, $args ): array {

		$form = (array) $form;

		$form_data = json_decode( stripslashes( $form['post_content'] ), true );

		if ( empty( $form_data['providers'][ $this->core->slug ] ) ) {
			return $form;
		}

		$provider = $form_data['providers'][ $this->core->slug ];
		$lock     = '__lock__';

		// Remove the lock field if it's the only one and it's locked.
		if ( isset( $provider[ $lock ] ) && count( $provider ) === 1 && absint( $provider[ $lock ] ) === 1 ) {
			unset( $form_data['providers'][ $this->core->slug ]['__lock__'] );
			$form['post_content'] = wpforms_encode( $form_data );
		}

		return $form;
	}

	/**
	 * Received field values for fields with multiple choices, e.g., multi-select.
	 * Connection Data has only the last saved field option.
	 * So, we should receive data from super global $_POST and receive all submitted options instead.
	 * WARNING: Sanitization of these values is required.
	 *
	 * @since 1.9.7
	 *
	 * @param string $name            Field name.
	 * @param array  $connection_data Connection data.
	 *
	 * @return array
	 */
	protected function get_multiple_option_field( string $name, array $connection_data ): array {

		// The nonce checked in the `wpforms_save_form` function.
		// phpcs:disable WordPress.Security.NonceVerification
		// When we duplicate a form the `$_POST['data']` is empty,
		// we shouldn't update the field and use copied data.
		if ( empty( $_POST['data'] ) || empty( $connection_data['id'] ) ) {
			return isset( $connection_data[ $name ] ) ? (array) $connection_data[ $name ] : [];
		}

		$connection_id = $connection_data['id'];
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$form_post = json_decode( wp_unslash( $_POST['data'] ), true ) ?? [];
		$full_name = "providers[{$this->core->slug}][$connection_id][$name][]";
		$values    = [];
		// phpcs:enable WordPress.Security.NonceVerification

		foreach ( $form_post as $post_pair ) {
			if ( empty( $post_pair['name'] ) || $post_pair['name'] !== $full_name ) {
				continue;
			}

			$values[] = $post_pair['value'];
		}

		return $values;
	}

	/**
	 * Sanitize custom fields.
	 *
	 * @since 1.9.3
	 *
	 * @param array $connection Connection data.
	 */
	protected function sanitize_connection_fields_meta( array &$connection ): void {

		if ( ! isset( $connection['fields_meta'] ) ) {
			return;
		}

		if ( ! is_array( $connection['fields_meta'] ) ) {
			unset( $connection['fields_meta'] );

			return;
		}

		foreach ( $connection['fields_meta'] as $row_number => $field ) {
			if ( ! isset( $field['field_id'], $field['name'] ) ) {
				unset( $connection['fields_meta'][ $row_number ] );

				continue;
			}

			// Field ID can contain a subfield, e.g. `1.first`.
			$field_id = sanitize_text_field( $field['field_id'] );
			$name     = sanitize_text_field( $field['name'] );

			if ( wpforms_is_empty_string( $field_id ) || wpforms_is_empty_string( $name ) ) {
				unset( $connection['fields_meta'][ $row_number ] );

				continue;
			}

			$connection['fields_meta'][ $row_number ] = [
				'name'     => $name,
				'field_id' => $field_id,
			];
		}

		$connection['fields_meta'] = array_values( $connection['fields_meta'] );
	}

	/**
	 * Sanitize conditional logic connection fields.
	 *
	 * @since 1.9.3
	 *
	 * @param array $connection Connection data.
	 */
	protected function sanitize_connection_conditionals( array &$connection ): void {

		if ( ! isset( $connection['conditionals'] ) ) {
			return;
		}

		if ( ! is_array( $connection['conditionals'] ) ) {
			unset( $connection['conditionals'] );

			return;
		}

		foreach ( $connection['conditionals'] as $group_id => $group ) {
			foreach ( $group as $rule ) {
				$this->sanitize_connection_conditional_rule( $rule );
			}

			$group = array_filter( $group );

			if ( empty( $group ) ) {
				unset( $connection['conditionals'][ $group_id ] );

				continue;
			}

			$connection['conditionals'][ $group_id ] = $group;
		}
	}

	/**
	 * Sanitize conditional logic rule.
	 *
	 * @since 1.9.3
	 *
	 * @param array $rule Conditional logic rule.
	 */
	private function sanitize_connection_conditional_rule( array &$rule ): void {

		if ( ! isset( $rule['field'], $rule['operator'] ) ) {
			$rule = [];

			return;
		}

		$sanitized_rule = [
			'field'    => sanitize_text_field( $rule['field'] ),
			'operator' => sanitize_text_field( $rule['operator'] ),
		];

		if (
			wpforms_is_empty_string( $sanitized_rule['field'] ) ||
			wpforms_is_empty_string( $sanitized_rule['operator'] )
		) {
			$rule = [];

			return;
		}

		if ( isset( $rule['value'] ) ) {
			$sanitized_rule['value'] = sanitize_text_field( $rule['value'] );
		}

		$rule = $sanitized_rule;
	}

	/**
	 * Builder error template.
	 * This generates an HTML template for displaying an error message
	 * when the connection to the provider fails. The message includes
	 * a link to the connection settings page for troubleshooting.
	 *
	 * @since 1.9.5
	 *
	 * @noinspection HtmlUnknownTarget
	 */
	protected function builder_error_template(): void {

		?>
		<script type="text/html" id="tmpl-wpforms-<?php echo esc_attr( $this->core->slug ); ?>-builder-content-connection-default-error">
			<div
				class="wpforms-builder-provider-connections-error wpforms-hidden"
				id="wpforms-<?php echo esc_attr( $this->core->slug ); ?>-builder-provider-error"
			>
				<span class="wpforms-builder-provider-connections-error-message">
					<?php
					printf(
						wp_kses( /* translators: %1$s - Documentation URL. */
							__(
								'Something went wrong, and we can’t connect to the provider. Please check your <a href="%s" target="_blank" rel="noopener noreferrer">connection settings</a>.',
								'wpforms-lite'
							),
							[
								'a' => [
									'href'   => [],
									'target' => [],
									'rel'    => [],
								],
							]
						),
						esc_url( $this->get_settings_url() )
					);
					?>
				</span>
			</div>
		</script>
		<?php
	}

	/**
	 * Retrieves the settings URL for the specific provider.
	 *
	 * @since 1.9.5
	 *
	 * @return string The URL to the settings page for the provider.
	 */
	private function get_settings_url(): string {

		return admin_url(
			sprintf(
				'admin.php?page=wpforms-settings&view=integrations#wpforms-integration-%s',
				$this->core->slug
			)
		);
	}
}
