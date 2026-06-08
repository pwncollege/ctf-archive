<?php

namespace WPForms\Admin\Tools\Views;

use WP_Error;
use WPForms\Helpers\File;
use WPForms\Admin\Tools\Importers;
use WPForms\Admin\Tools\Tools;
use WPForms_Form_Handler;
use WPForms\Admin\Notice;

/**
 * Class Import.
 *
 * @since 1.6.6
 */
class Import extends View {

	/**
	 * View slug.
	 *
	 * @since 1.6.6
	 *
	 * @var string
	 */
	protected $slug = 'import';

	/**
	 * Registered importers.
	 *
	 * @since 1.6.6
	 *
	 * @var array
	 */
	public $importers = [];

	/**
	 * Checking user capability to view.
	 *
	 * @since 1.6.6
	 *
	 * @return bool
	 */
	public function check_capability() {

		return wpforms_current_user_can( 'create_forms' );
	}

	/**
	 * Determine whether user has the "unfiltered_html" capability.
	 *
	 * By default, the "unfiltered_html" permission is only given to
	 * Super Admins, Administrators and Editors.
	 *
	 * @since 1.7.9
	 *
	 * @return bool
	 */
	private function check_unfiltered_html_capability() {

		return current_user_can( 'unfiltered_html' );
	}

	/**
	 * Init view.
	 *
	 * @since 1.6.6
	 */
	public function init() {

		// Bail early, in case the current user lacks the `unfiltered_html` capability.
		if ( ! $this->check_unfiltered_html_capability() ) {
			$this->error_unfiltered_html_import_message();

			return;
		}

		$this->hooks();

		$this->importers = ( new Importers() )->get_importers();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.7.9
	 */
	private function hooks() {

		add_action( 'wpforms_tools_init', [ $this, 'import_process' ] );
	}

	/**
	 * Get view label.
	 *
	 * @since 1.6.6
	 *
	 * @return string
	 */
	public function get_label() {

		return esc_html__( 'Import', 'wpforms-lite' );
	}

	/**
	 * Import process.
	 *
	 * @since 1.6.6
	 */
	public function import_process() {

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if (
			empty( $_POST['action'] ) ||
			$_POST['action'] !== 'import_form' ||
			empty( $_FILES['file']['tmp_name'] ) ||
			! isset( $_POST['submit-import'] ) ||
			! $this->verify_nonce()
		) {
			return;
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$this->process();
	}

	/**
	 * Import view content.
	 *
	 * @since 1.6.6
	 */
	public function display() {

		// Bail early, in case the current user lacks the `unfiltered_html` capability.
		if ( ! $this->check_unfiltered_html_capability() ) {
			return;
		}

		$this->success_import_message();
		$this->wpforms_block();
		$this->other_forms_block();
	}

	/**
	 * Success import message.
	 *
	 * @since 1.6.6
	 */
	private function success_import_message() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['wpforms_notice'] ) && $_GET['wpforms_notice'] === 'forms-imported' ) {
			?>
			<div class="updated notice is-dismissible">
				<p>
					<?php esc_html_e( 'Import was successfully finished.', 'wpforms-lite' ); ?>
					<?php
					if ( wpforms_current_user_can( 'view_forms' ) ) {
						printf(
							wp_kses( /* translators: %s - forms list page URL. */
								__( 'You can go and <a href="%s">check your forms</a>.', 'wpforms-lite' ),
								[ 'a' => [ 'href' => [] ] ]
							),
							esc_url( admin_url( 'admin.php?page=wpforms-overview' ) )
						);
					}
					?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Error message for users with no `unfiltered_html` permission.
	 *
	 * @since 1.7.9
	 */
	private function error_unfiltered_html_import_message() {

		Notice::error(
			sprintf(
				wp_kses( /* translators: %s - WPForms contact page URL. */
					__( 'You can’t import forms because you don’t have unfiltered HTML permissions. Please contact your site administrator or <a href="%s" target="_blank" rel="noopener noreferrer">reach out to our support team</a>.', 'wpforms-lite' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
						],
					]
				),
				'https://wpforms.com/contact/'
			)
		);
	}

	/**
	 * WPForms section.
	 *
	 * @since 1.6.6
	 */
	private function wpforms_block() {
		?>

		<div class="wpforms-setting-row tools wpforms-settings-row-divider">
			<h4><?php esc_html_e( 'WPForms Import', 'wpforms-lite' ); ?></h4>
			<p><?php esc_html_e( 'Select a WPForms export file.', 'wpforms-lite' ); ?></p>

			<form method="post" enctype="multipart/form-data" action="<?php echo esc_attr( $this->get_link() ); ?>">
				<div class="wpforms-file-upload">
					<input type="file" name="file" id="wpforms-tools-form-import" class="inputfile"
						data-multiple-caption="{count} <?php esc_attr_e( 'files selected', 'wpforms-lite' ); ?>"
						accept=".json" />
					<label for="wpforms-tools-form-import">
						<span class="fld"><span class="placeholder"><?php esc_html_e( 'No file chosen', 'wpforms-lite' ); ?></span></span>
						<strong class="wpforms-btn wpforms-btn-md wpforms-btn-light-grey">
							<i class="fa fa-cloud-upload"></i><?php esc_html_e( 'Choose a File', 'wpforms-lite' ); ?>
						</strong>
					</label>
				</div>
				<input type="hidden" name="action" value="import_form">
				<button name="submit-import" class="wpforms-btn wpforms-btn-md wpforms-btn-orange" id="wpforms-import" aria-disabled="true">
					<?php esc_html_e( 'Import', 'wpforms-lite' ); ?>
				</button>
				<?php $this->nonce_field(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * WPForms section.
	 *
	 * @since 1.6.6
	 */
	private function other_forms_block() {
		?>

		<div class="wpforms-setting-row tools" id="wpforms-importers">
			<h4><?php esc_html_e( 'Import from Other Form Plugins', 'wpforms-lite' ); ?></h4>
			<p><?php esc_html_e( 'Not happy with other WordPress contact form plugins?', 'wpforms-lite' ); ?></p>
			<p><?php esc_html_e( 'WPForms makes it easy for you to switch by allowing you import your third-party forms with a single click.', 'wpforms-lite' ); ?></p>

			<div class="wpforms-importers-wrap">
				<?php if ( empty( $this->importers ) ) { ?>
					<p><?php esc_html_e( 'No form importers are currently enabled.', 'wpforms-lite' ); ?> </p>
				<?php } else { ?>
					<form action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
						<span class="choicesjs-select-wrap">
							<select id="wpforms-tools-form-other-import" class="choicesjs-select" name="provider" data-search="<?php echo esc_attr( wpforms_choices_js_is_search_enabled( $this->importers ) ); ?>" required>
								<option value=""><?php esc_html_e( 'Select previous contact form plugin...', 'wpforms-lite' ); ?></option>
								<?php
								foreach ( $this->importers as $importer ) {
									$status = '';

									if ( empty( $importer['installed'] ) ) {
										$status = esc_html__( 'Not Installed', 'wpforms-lite' );
									} elseif ( empty( $importer['active'] ) ) {
										$status = esc_html__( 'Not Active', 'wpforms-lite' );
									}
									printf(
										'<option value="%s" %s>%s %s</option>',
										esc_attr( $importer['slug'] ),
										! empty( $status ) ? 'disabled' : '',
										esc_html( $importer['name'] ),
										! empty( $status ) ? '(' . esc_html( $status ) . ')' : '' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									);
								}
								?>
							</select>
						</span>
						<input type="hidden" name="page" value="<?php echo esc_attr( Tools::SLUG ); ?>">
						<input type="hidden" name="view" value="importer">
						<button class="wpforms-btn wpforms-btn-md wpforms-btn-orange" id="wpforms-import-other" aria-disabled="true">
							<?php esc_html_e( 'Import', 'wpforms-lite' ); ?>
						</button>
					</form>
				<?php } ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Import processing.
	 *
	 * @since 1.6.6
	 */
	private function process() { // phpcs:ignore WPForms.PHP.HooksMethod.InvalidPlaceForAddingHooks

		// Add filter of the link rel attr to avoid JSON damage.
		add_filter( 'wp_targeted_link_rel', '__return_empty_string', 50, 1 );

		$ext = '';

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( isset( $_FILES['file']['name'] ) ) {
			$ext = strtolower( pathinfo( sanitize_text_field( wp_unslash( $_FILES['file']['name'] ) ), PATHINFO_EXTENSION ) );
		}

		if ( $ext !== 'json' ) {
			wp_die(
				esc_html__( 'Please upload a valid .json form export file.', 'wpforms-lite' ),
				esc_html__( 'Error', 'wpforms-lite' ),
				[
					'response' => 400,
				]
			);
		}

		// The wp_unslash() function breaks upload on Windows.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.NonceVerification.Missing
		$filename = isset( $_FILES['file']['tmp_name'] ) ? sanitize_text_field( $_FILES['file']['tmp_name'] ) : '';

		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$result = self::import_forms( $filename );

		if ( $result !== null ) {
			wp_die(
				esc_html( $result->get_error_message() ),
				esc_html__( 'Error', 'wpforms-lite' ),
				[
					'response' => 400,
				]
			);
		}

		wp_safe_redirect( add_query_arg( [ 'wpforms_notice' => 'forms-imported' ] ) );
		exit;
	}

	/**
	 * Import forms from file.
	 * Should be static for external use.
	 *
	 * @since 1.8.6
	 *
	 * @param string $filename File containing forms to be imported.
	 *
	 * @return null|WP_Error
	 */
	public static function import_forms( string $filename ) {

		if ( ! current_user_can( 'unfiltered_html' ) ) {
			return new WP_Error( 'no_permission', __( 'The unfiltered HTML permissions are required to import form.', 'wpforms-lite' ) );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$forms = json_decode( File::remove_utf8_bom( file_get_contents( $filename ) ), true );

		if ( empty( $forms ) || ! is_array( $forms ) ) {
			return new WP_Error( 'bad_json', __( 'Please upload a valid .json form export file.', 'wpforms-lite' ) );
		}

		if ( ! self::save_forms( $forms ) ) {
			return new WP_Error( 'no_permission', __( 'There was an error saving your form. Please check your file and try again.', 'wpforms-lite' ) );
		}

		return null;
	}

	/**
	 * Save forms.
	 *
	 * @since 1.8.6
	 *
	 * @param array $forms Forms.
	 *
	 * @return bool
	 */
	private static function save_forms( array $forms ): bool {

		foreach ( $forms as $form ) {
			$title  = ! empty( $form['settings']['form_title'] ) ? $form['settings']['form_title'] : '';
			$desc   = ! empty( $form['settings']['form_desc'] ) ? $form['settings']['form_desc'] : '';
			$new_id = wp_insert_post(
				[
					'post_title'   => wp_slash( $title ),
					'post_status'  => 'publish',
					'post_type'    => 'wpforms',
					'post_excerpt' => wp_slash( $desc ),
				]
			);

			// When we cannot insert one form into the DB, or update it,
			// we will have a similar issue with the following form in the JSON file.
			// So, it is better to bail out and inform the user that we cannot proceed.
			if ( ! $new_id ) {
				return false;
			}

			$form['id'] = $new_id;

			if ( ! self::update_form( $form ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Update form.
	 *
	 * @since 1.8.6
	 *
	 * @param array $form Form.
	 *
	 * @return bool
	 */
	private static function update_form( array $form ): bool {

		if ( wpforms_is_form_data_slashing_enabled() ) {
			$form = wp_slash( $form );
		}

		$result = wp_update_post(
			[
				'ID'           => $form['id'],
				'post_content' => wpforms_encode( $form ),
			]
		);

		if ( ! $result ) {
			return false;
		}

		if ( empty( $form['settings']['form_tags'] ) ) {
			return true;
		}

		$result = wp_set_post_terms(
			$form['id'],
			implode( ',', (array) $form['settings']['form_tags'] ),
			WPForms_Form_Handler::TAGS_TAXONOMY
		);

		if ( ! $result ) {
			return false;
		}

		return true;
	}
}
