<?php

namespace WPForms\Admin\Tools\Views;

/**
 * Class Export.
 *
 * @since 1.6.6
 */
class Export extends View {

	/**
	 * View slug.
	 *
	 * @since 1.6.6
	 *
	 * @var string
	 */
	protected $slug = 'export';

	/**
	 * Template code if generated.
	 *
	 * @since 1.6.6
	 *
	 * @var string
	 */
	private $template = '';

	/**
	 * Existed forms.
	 *
	 * @since 1.6.6
	 *
	 * @var []
	 */
	private $forms = [];

	/**
	 * Init view.
	 *
	 * @since 1.6.6
	 */
	public function init() {

		add_action( 'wpforms_tools_init', [ $this, 'process' ] );
	}

	/**
	 * Get view label.
	 *
	 * @since 1.6.6
	 *
	 * @return string
	 */
	public function get_label() {

		return esc_html__( 'Export', 'wpforms-lite' );
	}

	/**
	 * Export process.
	 *
	 * @since 1.6.6
	 */
	public function process() {

		if (
			empty( $_POST['action'] ) || //phpcs:ignore WordPress.Security.NonceVerification
			! isset( $_POST['submit-export'] ) || //phpcs:ignore WordPress.Security.NonceVerification
			! $this->verify_nonce()
		) {
			return;
		}

		if ( $_POST['action'] === 'export_form' && ! empty( $_POST['forms'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification
			$this->process_form();
		}

		if ( $_POST['action'] === 'export_template' && ! empty( $_POST['form'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification
			$this->process_template();
		}
	}

	/**
	 * Checking user capability to view.
	 *
	 * @since 1.6.6
	 *
	 * @return bool
	 */
	public function check_capability() {

		return wpforms_current_user_can( [ 'edit_forms', 'view_entries' ] );
	}

	/**
	 * Get available forms.
	 *
	 * @since 1.6.6
	 *
	 * @return array
	 */
	public function get_forms() {

		$forms = wpforms()->obj( 'form' )->get( '', [ 'orderby' => 'title' ] );

		return ! empty( $forms ) ? $forms : [];
	}

	/**
	 * Export view content.
	 *
	 * @since 1.6.6
	 */
	public function display() {

		$this->forms = $this->get_forms();

		if ( empty( $this->forms ) ) {

			echo wpforms_render( 'admin/empty-states/no-forms' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			return;
		}

		do_action( 'wpforms_admin_tools_export_top' );

		$this->forms_export_block();

		$this->form_template_export_block();

		do_action( 'wpforms_admin_tools_export_bottom' );
	}

	/**
	 * Forms export block.
	 *
	 * @since 1.6.6
	 */
	private function forms_export_block() {
		?>

		<div class="wpforms-setting-row tools wpforms-settings-row-divider">

			<h4 id="form-export"><?php esc_html_e( 'Export Forms', 'wpforms-lite' ); ?></h4>

			<p><?php esc_html_e( 'Use form export files to create a backup of your forms or to import forms to another site.', 'wpforms-lite' ); ?></p>

			<?php if ( ! empty( $this->forms ) ) { ?>

				<form method="post" action="<?php echo esc_attr( $this->get_link() ); ?>">
					<?php $this->forms_select_html( 'wpforms-tools-form-export', 'forms[]', esc_html__( 'Select Form(s)', 'wpforms-lite' ) ); ?>
					<input type="hidden" name="action" value="export_form">
					<?php $this->nonce_field(); ?>
					<button name="submit-export" class="wpforms-btn wpforms-btn-md wpforms-btn-orange" id="wpforms-export-form" aria-disabled="true">
						<?php esc_html_e( 'Export', 'wpforms-lite' ); ?>
					</button>
				</form>
			<?php } else { ?>
				<p><?php esc_html_e( 'You need to create a form before you can use form export.', 'wpforms-lite' ); ?></p>
			<?php } ?>
		</div>
	<?php
	}

	/**
	 * Forms export block.
	 *
	 * @since 1.6.6
	 */
	private function form_template_export_block() {
		?>

		<div class="wpforms-setting-row tools">

			<h4 id="template-export"><?php esc_html_e( 'Export a Form Template', 'wpforms-lite' ); ?></h4>

			<?php
			if ( $this->template ) {

				$doc_link = sprintf(
					wp_kses( /* translators: %s - WPForms.com docs URL. */
						__( 'For more information <a href="%s" target="_blank" rel="noopener noreferrer">see our documentation</a>.', 'wpforms-lite' ),
						[
							'a' => [
								'href'   => [],
								'target' => [],
								'rel'    => [],
							],
						]
					),
					'https://wpforms.com/docs/how-to-create-a-custom-form-template/'
				);
			?>
			<p><?php esc_html_e( 'The following code can be used to register your custom form template. Copy and paste the following code to your theme\'s functions.php file or include it within an external file.', 'wpforms-lite' ); ?><p>
			<p><?php echo $doc_link; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><p>
			<textarea class="info-area" readonly><?php echo esc_textarea( $this->template ); ?></textarea>
			<?php
			}
			?>

			<p><?php esc_html_e( 'Select a form to generate PHP code that can be used to register a custom form template.', 'wpforms-lite' ); ?></p>

			<?php if ( ! empty( $this->forms ) ) { ?>
				<form method="post" action="<?php echo esc_attr( $this->get_link() ); ?>">
					<?php $this->forms_select_html( 'wpforms-tools-form-template', 'form', esc_html__( 'Select a Template', 'wpforms-lite' ), false ); ?>
					<input type="hidden" name="action" value="export_template">
					<?php $this->nonce_field(); ?>
					<button name="submit-export" class="wpforms-btn wpforms-btn-md wpforms-btn-orange" id="wpforms-export-template" aria-disabled="true">
						<?php esc_html_e( 'Export Template', 'wpforms-lite' ); ?>
					</button>
				</form>
			<?php } else { ?>
				<p><?php esc_html_e( 'You need to create a form before you can generate a template.', 'wpforms-lite' ); ?></p>
			<?php } ?>
		</div>
	<?php
	}

	/**
	 * Forms selector.
	 *
	 * @since 1.6.6
	 *
	 * @param string $select_id   Select id.
	 * @param string $select_name Select name.
	 * @param string $placeholder Placeholder.
	 * @param bool   $multiple    Is multiple select.
	 */
	private function forms_select_html( $select_id, $select_name, $placeholder, $multiple = true ) {
		?>

		<span class="choicesjs-select-wrap">
			<select id="<?php echo esc_attr( $select_id ); ?>" class="choicesjs-select" name="<?php echo esc_attr( $select_name ); ?>" <?php if ( $multiple ) { //phpcs:ignore ?> multiple size="1" <?php } ?> data-search="<?php echo esc_attr( wpforms_choices_js_is_search_enabled( $this->forms ) ); ?>">
				<option value=""><?php echo esc_attr( $placeholder ); ?></option>
				<?php foreach ( $this->forms as $form ) { ?>
					<option value="<?php echo absint( $form->ID ); ?>"><?php echo esc_html( $form->post_title ); ?></option>
				<?php } ?>
			</select>
		</span>
		<?php
	}

	/**
	 * Export processing.
	 *
	 * @since 1.6.6
	 */
	private function process_form() {

		$export = [];
		$forms  = get_posts(
			[
				'post_type' => 'wpforms',
				'nopaging'  => true,
				'post__in'  => isset( $_POST['forms'] ) ? array_map( 'intval', $_POST['forms'] ) : [], //phpcs:ignore WordPress.Security.NonceVerification
			]
		);

		foreach ( $forms as $form ) {
			$export[] = wpforms_decode( $form->post_content );
		}

		ignore_user_abort( true );

		wpforms_set_time_limit();

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=wpforms-form-export-' . current_time( 'm-d-Y' ) . '.json' );
		header( 'Expires: 0' );

		echo wp_json_encode( $export );
		exit;
	}

	/**
	 * Export template processing.
	 *
	 * @since 1.6.6
	 */
	private function process_template(): void {

		// Nonce is checked in the caller: process() method.
		//phpcs:ignore WordPress.Security.NonceVerification.Missing
		$form_id  = isset( $_POST['form'] ) ? absint( $_POST['form'] ) : 0;
		$form_obj = wpforms()->obj( 'form' );

		if ( ! $form_obj || ! $form_id ) {
			return;
		}

		$form_data = $form_obj->get( $form_id, [ 'content_only' => true ] );

		// Define basic data with strict validation.
		$name = sanitize_text_field( $form_data['settings']['form_title'] ?? '' );
		$desc = sanitize_text_field( $form_data['settings']['form_desc'] ?? '' );
		$slug = sanitize_key( str_replace( [ ' ', '-' ], '_', trim( $name ) ) );

		if ( ! $slug ) {
			// Slug is always empty when the $form_data is not valid.
			return;
		}

		$class = 'WPForms_Template_' . $slug;
		$data  = $this->get_template_data( $slug, $form_data );

		// Build the final template string.
		$this->template = <<<EOT
if ( class_exists( 'WPForms_Template', false ) ) :
/**
 * {$name}
 * Template for WPForms.
 */
class {$class} extends WPForms_Template {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Template name
		\$this->name = '{$name}';

		// Template slug
		\$this->slug = '{$slug}';

		// Template description
		\$this->description = '{$desc}';

		// Template field and settings
		\$this->data = {$data};
	}
}
new {$class}();
endif;
EOT;
	}

	/**
	 * Get template data.
	 *
	 * @since 1.9.5
	 *
	 * @param string      $slug      Template slug.
	 * @param array|mixed $form_data Form data.
	 *
	 * @return string
	 */
	private function get_template_data( string $slug, $form_data ): string {

		// Format template field and settings data.
		$data                     = [];
		$data['meta']['template'] = $slug;
		$data['fields']           = isset( $form_data['fields'] ) && is_array( $form_data['fields'] )
			? wpforms_array_remove_empty_strings( $form_data['fields'] )
			: [];
		$data['settings']         = isset( $form_data['settings'] ) && is_array( $form_data['settings'] )
			? wpforms_array_remove_empty_strings( $form_data['settings'] )
			: [];

		$template_data = (string) var_export( $data, true ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
		$template_data = str_replace( '  ', "\t", $template_data );

		return preg_replace( '/([\t\r\n]+?)array/', 'array', $template_data );
	}
}
