<?php

namespace WPForms\Helpers;

use WP_Filesystem_Base; // phpcs:ignore WPForms.PHP.UseStatement.UnusedUseStatement

/**
 * Class File.
 *
 * @since 1.6.5
 */
class File {

	/**
	 * Remove UTF-8 BOM signature if it presents.
	 *
	 * @since 1.6.5
	 *
	 * @param string $str String to process.
	 *
	 * @return string
	 * @noinspection SpellCheckingInspection
	 */
	public static function remove_utf8_bom( $str ): string {

		if ( strpos( bin2hex( $str ), 'efbbbf' ) === 0 ) {
			$str = substr( $str, 3 );
		}

		return $str;
	}

	/**
	 * Get current filesystem.
	 *
	 * @since 1.8.6
	 *
	 * @return WP_Filesystem_Base|null
	 */
	public static function get_filesystem(): ?WP_Filesystem_Base {

		global $wp_filesystem;

		static $is_filesystem_setup;

		if ( $is_filesystem_setup ) {
			return $wp_filesystem;
		}

		// We have to start the buffer to prevent output
		// when the file system is ssh/FTP but not configured.
		ob_start();

		if ( ! function_exists( 'request_filesystem_credentials' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		// The current page URL.
		$url = home_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ) );

		$credentials = request_filesystem_credentials( $url, '', false, false );

		ob_end_clean();

		if ( $credentials === false || ! WP_Filesystem( $credentials ) ) {
			wpforms_log(
				'WP_Filesystem Error',
				'File system isn\'t configured.',
				[ 'type' => [ 'error' ] ]
			);

			return null;
		}

		$is_filesystem_setup = true;

		return $wp_filesystem;
	}

	/**
	 * Get file contents.
	 *
	 * @since 1.8.6
	 *
	 * @param string $file File path.
	 *
	 * @return string|false
	 */
	public static function get_contents( $file ) {

		$filesystem = self::get_filesystem();

		if (
			! $filesystem
			|| ! $filesystem->is_readable( $file )
			|| $filesystem->is_dir( $file )
		) {
			return false;
		}

		return $filesystem->size( $file ) > 0 ? $filesystem->get_contents( $file ) : '';
	}

	/**
	 * Save file contents.
	 *
	 * @since 1.8.6
	 *
	 * @param string $file    File path.
	 * @param string $content File content.
	 *
	 * @return bool
	 */
	public static function put_contents( $file, $content ): bool {

		$filesystem = self::get_filesystem();

		if ( ! $filesystem ) {
			return false;
		}

		return $filesystem->put_contents( $file, $content );
	}

	/**
	 * Determine whether a file or directory exists.
	 *
	 * @since 1.9.1
	 *
	 * @param string $path Path to a file or directory.
	 *
	 * @return bool Whether $path exists or not.
	 */
	public static function exists( string $path ): bool {

		$filesystem = self::get_filesystem();

		if ( ! $filesystem ) {
			return false;
		}

		return $filesystem->exists( $path );
	}

	/**
	 * Copies a file.
	 *
	 * @since 1.9.1
	 *
	 * @param string $source      Path to the source file.
	 * @param string $destination Path to the destination file.
	 * @param bool   $overwrite   Optional. Whether to overwrite the destination file if it exists.
	 *                            Default false.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function copy( string $source, string $destination, bool $overwrite = false ): bool {

		$filesystem = self::get_filesystem();

		if ( ! $filesystem ) {
			return false;
		}

		return $filesystem->copy( $source, $destination, $overwrite );
	}

	/**
	 * Move a file or files from source to destination.
	 *
	 * @since 1.8.8
	 *
	 * @param string $source      Source file or glob pattern.
	 * @param string $destination Destination file or directory.
	 *
	 * @return bool
	 */
	public static function move( string $source, string $destination ): bool {

		$filesystem = self::get_filesystem();

		if ( ! $filesystem ) {
			return false;
		}

		foreach ( glob( $source ) as $filename ) {
			$move = $filesystem->move( $filename, $destination . basename( $filename ), true );

			if ( ! $move ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Delete a file or directory.
	 *
	 * @since 1.8.8
	 *
	 * @param string $file Path to the file or directory.
	 *
	 * @return bool
	 */
	public static function delete( string $file ): bool {

		$filesystem = self::get_filesystem();

		if ( ! $filesystem ) {
			return false;
		}

		return $filesystem->delete( $file, true );
	}

	/**
	 * Create a directory.
	 *
	 * @since 1.8.8
	 *
	 * @param string $dir Path directory.
	 *
	 * @return bool True on success, false on failure. If the directory already exists, this method will return true.
	 */
	public static function mkdir( string $dir ): bool {

		$filesystem = self::get_filesystem();

		if ( ! $filesystem ) {
			return false;
		}

		if ( $filesystem->is_dir( $dir ) ) {
			return true;
		}

		return $filesystem->mkdir( $dir );
	}

	/**
	 * Gets details for files in a directory or a specific file.
	 *
	 * @since 1.8.8
	 *
	 * @param string $dir Path directory.
	 *
	 * @return array|bool
	 */
	public static function dirlist( string $dir ) {

		$filesystem = self::get_filesystem();

		if ( ! $filesystem || ! $filesystem->is_dir( $dir ) ) {
			return false;
		}

		return $filesystem->dirlist( $dir, false );
	}

	/**
	 * Get the upload directory path.
	 *
	 * @since 1.8.7
	 *
	 * @return string
	 */
	public static function get_upload_dir(): string {

		static $upload_dir;

		if ( $upload_dir ) {
			/**
			 * Since wpforms_upload_dir() relies on hooks, and hooks can be added unpredictably,
			 * we need to cache the result of this method.
			 * Otherwise, it is a risk to save a cache file to one dir and try to get from another.
			 */
			return $upload_dir;
		}

		$wpforms_upload_dir  = wpforms_upload_dir();
		$wpforms_upload_path = ! empty( $wpforms_upload_dir['path'] )
			? $wpforms_upload_dir['path']
			: WP_CONTENT_DIR . '/uploads/wpforms';
		$upload_dir          = trailingslashit( wp_normalize_path( $wpforms_upload_path ) );

		return $upload_dir;
	}

	/**
	 * Get the upload directory URL.
	 *
	 * @since 1.9.7.3
	 *
	 * @return string
	 */
	public static function get_upload_url(): string {

		static $upload_url;

		if ( $upload_url ) {
			/**
			 * Since wpforms_upload_dir() relies on hooks, and hooks can be added unpredictably,
			 * we need to cache the result of this method.
			 * Otherwise, it is a risk to save a cache file to one dir and try to get from another.
			 */
			return $upload_url;
		}

		$wpforms_upload_dir = wpforms_upload_dir();

		return ! empty( $wpforms_upload_dir['url'] )
			? $wpforms_upload_dir['url']
			: WP_CONTENT_URL . '/uploads/wpforms';
	}

	/**
	 * Get the cache directory path.
	 *
	 * @since 1.8.6
	 *
	 * @return string
	 */
	public static function get_cache_dir(): string {

		static $cache_dir;

		if ( $cache_dir ) {
			/**
			 * Since wpforms_upload_dir() relies on hooks, and hooks can be added unpredictably,
			 * we need to cache the result of this method.
			 * Otherwise, it is a risk to save a cache file to one dir and try to get from another.
			 */
			return $cache_dir;
		}

		$cache_dir = self::get_upload_dir() . 'cache/';

		return $cache_dir;
	}

	/**
	 * Check whether the file is already updated.
	 *
	 * @since 1.8.7
	 *
	 * @param string $filename  Filename.
	 * @param string $cache_key Cache key.
	 *
	 * @return bool
	 */
	public static function is_file_updated( string $filename, string $cache_key = '' ): bool {

		$filename  = wp_normalize_path( $filename );
		$cache_key = $cache_key ? $cache_key : 'wpforms_' . $filename . '_file';

		if ( ! is_file( $filename ) ) {
			return false;
		}

		$cached_stat = Transient::get( $cache_key );
		$stat        = array_intersect_key(
			stat( $filename ),
			[
				'size'  => 0,
				'mtime' => 0,
				'ctime' => 0,
			]
		);

		if ( $cached_stat === $stat ) {
			return true;
		}

		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.unlink_unlink
		@unlink( $filename );

		return false;
	}

	/**
	 * Save file updated stat.
	 *
	 * @since 1.8.7
	 *
	 * @param string $filename  Filename.
	 * @param string $cache_key Cache key.
	 *
	 * @return void
	 */
	public static function save_file_updated_stat( string $filename, string $cache_key = '' ): void {

		$filename  = wp_normalize_path( $filename );
		$cache_key = $cache_key ? $cache_key : 'wpforms_' . $filename . '_file';

		clearstatcache( true, $filename );

		$stat = array_intersect_key(
			stat( $filename ),
			[
				'size'  => 0,
				'mtime' => 0,
				'ctime' => 0,
			]
		);

		Transient::set( $cache_key, $stat );
	}
}
