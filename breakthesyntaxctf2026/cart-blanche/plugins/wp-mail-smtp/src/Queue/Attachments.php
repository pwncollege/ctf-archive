<?php

namespace WPMailSMTP\Queue;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use WPMailSMTP\Uploads;

/**
 * Class Attachments.
 *
 * @since 4.0.0
 */
class Attachments {

	/**
	 * Process a list of file attachments.
	 *
	 * @since 4.0.0
	 *
	 * @param array $attachments List of attachments.
	 *
	 * @return array List of attachments.
	 */
	public function process_attachments( $attachments ) {

		$attachments = array_map(
			function( $attachment ) {
				[ $path, , $name, , , $is_string_attachment ] = $attachment;

				$path = $this->process_attachment( $path, $name, $is_string_attachment );

				if ( ! empty( $path ) ) {
					$attachment[0] = $path;
				}

				return $attachment;
			},
			$attachments
		);

		return $attachments;
	}

	/**
	 * Process an attachment,obfuscating its path
	 * and storing its file on disk.
	 *
	 * @since 4.0.0
	 *
	 * @param string $path                 The path to obfuscate.
	 * @param string $name                 The name of the file at $path.
	 * @param bool   $is_string_attachment Whether this attachment is a string attachment.
	 *
	 * @return string|false New path of the attachment, or false for no path.
	 */
	private function process_attachment( $path, $name = '', $is_string_attachment = false ) {

		$file_content = $this->get_attachment_file_content( $path, $is_string_attachment );

		if ( $file_content === false ) {
			return false;
		}

		if ( ! $is_string_attachment && $name === '' ) {
			$name = wp_basename( $path );
		}

		$name            = sanitize_file_name( $name );
		$obfuscated_path = $this->store_file( $file_content, $name );

		if ( empty( $obfuscated_path ) ) {
			return $path;
		}

		return $obfuscated_path;
	}

	/**
	 * Return the contents of a given file.
	 *
	 * @since 4.0.0
	 *
	 * @param string $path                 The file's path.
	 * @param bool   $is_string_attachment Whether this file is a string attachment.
	 *
	 * @return string File contents.
	 */
	private function get_attachment_file_content( $path, $is_string_attachment ) {

		if ( ! $is_string_attachment ) {
			if ( ! file_exists( $path ) ) {
				return false;
			}

			return file_get_contents( $path );
		}

		return $path;
	}

	/**
	 * Store a file.
	 *
	 * @since 4.0.0
	 *
	 * @param string $file_content      The file's contents.
	 * @param string $original_filename The original file's name.
	 *
	 * @return string The file's path.
	 */
	private function store_file( $file_content, $original_filename ) {

		$uploads_directory = $this->get_uploads_directory();

		if ( is_wp_error( $uploads_directory ) ) {
			return false;
		}

		if ( ! is_dir( $uploads_directory ) ) {
			wp_mkdir_p( $uploads_directory );

			// Check if the .htaccess exists in the root upload directory, if not - create it.
			Uploads::create_upload_dir_htaccess_file();

			// Check if the index.html exists in the directories, if not - create them.
			Uploads::create_index_html_file( Uploads::upload_dir()['path'] );
			Uploads::create_index_html_file( $uploads_directory );
		}

		$file_extension    = pathinfo( $original_filename, PATHINFO_EXTENSION );
		$filename          = wp_unique_filename( $uploads_directory, wp_generate_password( 32, false, false ) . '.' . $file_extension );
		$uploads_directory = trailingslashit( $uploads_directory );

		if ( ! is_writeable( $uploads_directory ) ) {
			return false;
		}

		$upload_path = $uploads_directory . $filename;

		if ( file_put_contents( $upload_path, $file_content ) !== false ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
			return $upload_path;
		}

		return false;
	}

	/**
	 * Delete attachments, removing their files from disk.
	 *
	 * @since 4.0.0
	 *
	 * @param null|array    $attachments     List of attachments to cleanup, or null for all attachments.
	 * @param null|DateTime $before_datetime The datetime attachments should be older than
	 *                                       to be removed, or null for all attachments.
	 *
	 * @return void.
	 */
	public function delete_attachments( $attachments = null, $before_datetime = null ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		$uploads_directory = $this->get_uploads_directory();

		if (
			is_wp_error( $uploads_directory ) ||
			! is_dir( $uploads_directory )
		) {
			return;
		}

		$files = [];

		// If no attachment list is provided, just iterate over all files in our uploads directory.
		if ( is_null( $attachments ) ) {
			$nodes = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $uploads_directory, RecursiveDirectoryIterator::SKIP_DOTS ),
				RecursiveIteratorIterator::CHILD_FIRST
			);
			$files = [];

			foreach ( $nodes as $fileinfo ) {
				if ( ! $fileinfo->isDir() ) {
					$files[] = $fileinfo->getRealPath();
				}
			}
		} else {
			// Map attachments to their paths.
			$files = wp_list_pluck( $attachments, 0 );

			// Exclude any files that aren't in our uploads directory.
			$files = array_filter(
				$files,
				function( $file ) use ( $uploads_directory ) {
					return trailingslashit( dirname( $file ) ) === $uploads_directory;
				}
			);
		}

		// Skip any file that doesn't exist.
		$files = array_filter(
			$files,
			function( $file ) {
				return file_exists( $file );
			}
		);

		if ( ! is_null( $before_datetime ) ) {
			// Skip any file that isn't older than the provided datetime.
			$before_timestamp = $before_datetime->getTimestamp();
			$files            = array_filter(
				$files,
				function( $file ) use ( $before_timestamp ) {
					return (
						filemtime( $file ) !== false &&
						filemtime( $file ) < $before_timestamp
					);
				}
			);
		}

		foreach ( $files as $file ) {
			@unlink( $file );
		}
	}

	/**
	 * Get the upload directory path.
	 *
	 * @since 4.0.0
	 *
	 * @return string|WP_Error The upload directory path.
	 */
	private function get_uploads_directory() {

		$uploads_directory = Uploads::upload_dir();

		if ( is_wp_error( $uploads_directory ) ) {
			return $uploads_directory;
		}

		return trailingslashit( trailingslashit( $uploads_directory['path'] ) . 'queue_attachments' );
	}
}
