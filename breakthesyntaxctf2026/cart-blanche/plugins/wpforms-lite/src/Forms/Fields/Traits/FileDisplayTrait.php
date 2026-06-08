<?php

namespace WPForms\Forms\Fields\Traits;

/**
 * File Entry Preview Trait.
 *
 * Contains common methods for displaying file uploads in entries and emails.
 *
 * @since 1.9.8
 */
trait FileDisplayTrait {

	/**
	 * Format field value for display in Entries.
	 *
	 * @since 1.9.8
	 *
	 * @param string|mixed $val       Field value.
	 * @param array        $field     Field data.
	 * @param array        $form_data Form data.
	 * @param string       $context   Display context.
	 *
	 * @return string
	 */
	public function html_field_value( $val, array $field, array $form_data = [], string $context = '' ): string {

		$val = (string) $val;

		if ( $field['type'] !== $this->type ) {
			return $val;
		}

		$field = $this->entry_preview_prepare_field_value( $field, $form_data, $context );

		// Return early if there is no value at all.
		if ( empty( $field['value'] ) && empty( $field['value_raw'] ) ) {
			return $val;
		}

		// Process modern uploader.
		if ( ! empty( $field['value_raw'] ) ) {
			return $this->process_modern_uploader( $field, $context );
		}

		// Process classic uploader.
		if ( $this->is_entry_preview( $context ) ) {
			return $this->entry_preview_file_link_html( $field, $this->get_file_url( $field ) );
		}

		return $this->get_file_link_html( $field, $context );
	}

	/**
	 * Get file link HTML.
	 *
	 * @since 1.9.8
	 *
	 * @param array  $file    File data.
	 * @param string $context Value display context.
	 *
	 * @return string
	 * @noinspection HtmlUnknownTarget
	 */
	private function get_file_link_html( array $file, string $context ): string {

		$html = in_array( $context, [ 'email-html', 'entry-single' ], true ) ? $this->file_icon_html( $file ) : '';

		$html .= sprintf(
			'<a href="%s" rel="noopener noreferrer" target="_blank" style="%s">%s</a>',
			esc_url( $this->get_file_url( $file ) ),
			$context === 'email-html' ? 'padding-left:10px;' : '',
			esc_html( $this->get_file_name( $file ) )
		);

		return $html;
	}

	/**
	 * Get the URL of a file.
	 *
	 * @since 1.9.8
	 *
	 * @param array $file File data.
	 * @param array $args Additional query arguments.
	 *
	 * @return string
	 */
	public function get_file_url( array $file, array $args = [] ): string {

		$file_url = $file['value'] ?? '';

		if ( ! empty( $file['protection_hash'] ) ) {
			$args = wp_parse_args(
				$args,
				[
					'wpforms_uploaded_file' => $file['protection_hash'],
				]
			);

			$file_url = add_query_arg( $args, home_url() );
		}

		/**
		 * Allow modifying the URL of a file.
		 *
		 * @since 1.9.8
		 *
		 * @param string $file_url File URL.
		 * @param array  $file     File data.
		 */
		return (string) apply_filters( 'wpforms_pro_fields_file_upload_get_file_url', $file_url, $file );
	}

	/**
	 * Get the name of a file.
	 *
	 * @since 1.9.8
	 *
	 * @param array $file File data.
	 *
	 * @return string
	 */
	public function get_file_name( array $file ): string {

		if ( ! $this->is_file_protected( $file ) ) {
			return $file['file_original'];
		}

		$ext = $file['ext'] ?? '';

		return sprintf( '%s.%s', hash( 'crc32b', $file['file_original'] ), $ext );
	}

	/**
	 * Check if the file is protected.
	 *
	 * @since 1.9.8
	 *
	 * @param array $file_data File data.
	 *
	 * @return bool True if the file is protected, false otherwise.
	 */
	private function is_file_protected( array $file_data ): bool {

		return ! empty( $file_data['protection_hash'] );
	}

	/**
	 * Get file icon HTML.
	 *
	 * @since 1.9.8
	 *
	 * @param array $file_data File data.
	 *
	 * @return string
	 * @noinspection HtmlUnknownTarget
	 */
	public function file_icon_html( array $file_data ): string {

		$src       = esc_url( $file_data['value'] );
		$ext_types = wp_get_ext_types();

		if ( $this->is_file_protected( $file_data ) || ! in_array( $file_data['ext'], $ext_types['image'], true ) ) {
			$src = wp_mime_type_icon( wp_ext2type( $file_data['ext'] ) ?? '' );
		} elseif ( ! empty( $file_data['attachment_id'] ) ) {
			$image = wp_get_attachment_image_src( $file_data['attachment_id'], [ 16, 16 ], true );
			$src   = $image ? $image[0] : $src;
		}

		return sprintf( '<span class="file-icon"><img width="16" height="16" src="%s" alt="" /></span>', esc_url( $src ) );
	}

	/**
	 * Prepare field value for entry preview.
	 *
	 * @since 1.9.9
	 *
	 * @param array  $field     Field data.
	 * @param array  $form_data Form data.
	 * @param string $context   Display context.
	 *
	 * @return array
	 */
	private function entry_preview_prepare_field_value( array $field, array $form_data, string $context ): array {

		if ( ! empty( $field['value'] ) || ! empty( $field['value_raw'] ) || ! $this->is_entry_preview( $context ) ) {
			return $field;
		}

		$this->form_id  = absint( $form_data['id'] );
		$this->field_id = absint( $field['id'] );
		$input_name     = $this->get_input_name();

		// Modern uploader: data (JSON) in $_POST.
		$raw_json = isset( $_POST[ $input_name ] ) ? sanitize_text_field( wp_unslash( $_POST[ $input_name ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( $raw_json !== '' && wpforms_is_json( $raw_json ) ) {
			$files = json_decode( $raw_json, true );

			if ( ! empty( $files ) ) {
				$field['value_raw'] = array_map(
					static function ( $file ) {
						$name = $file['name'] ?? '';

						return [
							'value'         => $file['url'] ?? '',
							'file_original' => $name,
							'ext'           => strtolower( pathinfo( $name, PATHINFO_EXTENSION ) ),
						];
					},
					$files
				);
			}

			return $field;
		}

		// Classic uploader: data in $_FILES.
		$files = $_FILES[ $input_name ]['name'] ?? []; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$files = is_array( $files ) ? $files : [ $files ];
		$files = array_filter( array_map( 'sanitize_file_name', $files ) );

		if ( ! empty( $files ) ) {
			$field['value_raw'] = array_map(
				static function ( $file ) {

					return [
						'value'         => '', // No public URL available.
						'file_original' => $file,
						'ext'           => strtolower( pathinfo( $file, PATHINFO_EXTENSION ) ),
					];
				},
				$files
			);
		}

		return $field;
	}

	/**
	 * Process modern uploader.
	 *
	 * @since 1.9.9
	 *
	 * @param array  $field   Field data.
	 * @param string $context Value display context.
	 *
	 * @return string
	 */
	private function process_modern_uploader( array $field, string $context ): string {

		$values           = $context === 'entry-table' ? array_slice( $field['value_raw'], 0, 3, true ) : $field['value_raw'];
		$html             = '';
		$submitted_fields = ! empty( $_POST['wpforms'] ) ? stripslashes_deep( $_POST['wpforms'] ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
		$is_entry_preview = $this->is_entry_preview( $context );

		foreach ( $values as $key => $file ) {

			$src = $this->get_file_url( $file );

			// If the temp file doesn't exist, fallback to submitted field data if set.
			if ( $is_entry_preview && ! file_exists( $src ) && isset( $submitted_fields['complete'][ $field['id'] ]['value_raw'][ $key ] ) ) {
				$file = $submitted_fields['complete'][ $field['id'] ]['value_raw'][ $key ];
				$src  = $this->get_file_url( $file );
			}

			// Normalize structure ( pre-submit uses url/name; post-submit uses value/file_original ).
			if ( empty( $file['value'] ) && ! empty( $file['url'] ) ) {
				$file['value'] = $file['url'];
			}

			if ( empty( $file['file_original'] ) && ! empty( $file['name'] ) ) {
				$file['file_original'] = $file['name'];
			}

			if ( empty( $file['ext'] ) ) {
				$source      = $file['file_original'] ?? ( $file['name'] ?? '' );
				$file['ext'] = strtolower( pathinfo( $source, PATHINFO_EXTENSION ) );
			}

			if ( empty( $file['file_original'] ) ) {
				continue;
			}

			if ( $is_entry_preview ) {
				$html .= $this->entry_preview_file_link_html( $file, $src );

				continue;
			}

			$html .= $this->get_file_link_html( $file, $context ) . '<br/>';
		}

		if ( count( $values ) < count( $field['value_raw'] ) ) {
			$html .= '&hellip;';
		}

		return $html;
	}

	/**
	 * Get file link HTML for entry preview.
	 * Show image previews, non-images as plain text.
	 *
	 * @since 1.9.9
	 *
	 * @param array  $file File data.
	 * @param string $src  File source.
	 *
	 * @return string
	 */
	private function entry_preview_file_link_html( array $file, string $src ): string {

		$filename = esc_html( $this->get_file_name( $file ) );
		$is_image = in_array( $file['ext'] ?? '', wp_get_ext_types()['image'], true );

		// If we have a URL, display an inline thumbnail preview.
		if ( $is_image && ! empty( $src ) ) {
			return sprintf(
				'<span class="wpforms-entry-preview-file is-image"><img src="%1$s" alt="%2$s"/><span class="wpforms-entry-preview-filename">%2$s</span></span>',
				esc_url( $src ),
				esc_html( $filename )
			);
		}

		// Show the file name otherwise.
		return sprintf( '<span class="wpforms-entry-preview-file">%1$s</span>', $filename );
	}

	/**
	 * Check if the context is an entry preview.
	 *
	 * @since 1.9.9
	 *
	 * @param string $context Value display context.
	 *
	 * @return bool True if the context is entry preview, false otherwise.
	 */
	private function is_entry_preview( string $context ): bool {

		return $context === 'entry-preview';
	}

	/**
	 * Get the input name for the field.
	 *
	 * @since 1.9.9
	 *
	 * @return string
	 */
	public function get_input_name(): string {

		return sprintf( 'wpforms_%d_%d', $this->form_id, $this->field_id );
	}

	/**
	 * Format the field value for smart tags.
	 *
	 * @since 1.10.0
	 *
	 * @param string $value     The field value.
	 * @param int    $field_id  The field ID.
	 * @param array  $fields    The form fields.
	 * @param string $field_key The field key.
	 *
	 * @return string
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function smart_tags_formatted_field_value( $value, $field_id, $fields, $field_key ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		$value = (string) $value;
		$field = $fields[ $field_id ] ?? [];

		return $this->get_formatted_value( $value, $field );
	}

	/**
	 * Get file URLs.
	 *
	 * @since 1.10.0
	 *
	 * @param array $values Field values.
	 *
	 * @return array
	 */
	private function get_file_urls( array $values ): array {

		$urls = [];

		foreach ( $values as $file ) {
			$urls[] = $this->get_file_url( $file );
		}

		return $urls;
	}

	/**
	 * Get formatted value.
	 *
	 * @since 1.10.0
	 *
	 * @param string $value Field value.
	 * @param array  $field Field settings.
	 *
	 * @return string
	 */
	private function get_formatted_value( string $value, array $field ): string {

		$type = $field['type'] ?? '';

		if ( $type !== $this->type ) {
			return $value;
		}

		if ( empty( $field['style'] ) ) {
			return $this->get_file_url( $field );
		}

		$values = (array) $field['value_raw'];
		$values = array_filter( $values );

		$urls = $this->get_file_urls( $values );

		return empty( $urls ) ? $value : implode( "\n", $urls );
	}
}
