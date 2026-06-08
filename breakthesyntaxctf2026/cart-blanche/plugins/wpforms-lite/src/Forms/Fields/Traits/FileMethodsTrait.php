<?php

namespace WPForms\Forms\Fields\Traits;

/**
 * File methods trait.
 *
 * @since 1.9.8
 */
trait FileMethodsTrait {

	/**
	 * File extensions that are not allowed.
	 *
	 * @since 1.9.8
	 *
	 * @var array
	 */
	private $denylist = [
		'ade',
		'adp',
		'app',
		'asp',
		'bas',
		'bat',
		'cer',
		'cgi',
		'chm',
		'cmd',
		'com',
		'cpl',
		'crt',
		'csh',
		'csr',
		'dll',
		'drv',
		'exe',
		'fxp',
		'flv',
		'hlp',
		'hta',
		'htaccess',
		'htm',
		'html',
		'htpasswd',
		'inf',
		'ins',
		'isp',
		'jar',
		'js',
		'jse',
		'jsp',
		'ksh',
		'lnk',
		'mdb',
		'mde',
		'mdt',
		'mdw',
		'msc',
		'msi',
		'msp',
		'mst',
		'ops',
		'pcd',
		'php',
		'pif',
		'pl',
		'prg',
		'ps1',
		'ps2',
		'py',
		'rb',
		'reg',
		'scr',
		'sct',
		'sh',
		'shb',
		'shs',
		'sys',
		'swf',
		'tmp',
		'torrent',
		'url',
		'vb',
		'vbe',
		'vbs',
		'vbscript',
		'wsc',
		'wsf',
		'wsf',
		'wsh',
		'dfxp',
		'onetmp',
	];

	/**
	 * Get all allowed extensions.
	 * Check against user-entered extensions.
	 *
	 * @since 1.9.8
	 *
	 * @return array
	 */
	protected function get_extensions(): array {

		// Allowed file extensions by default.
		$default_extensions = $this->get_default_extensions();

		// Allowed file extensions.
		$extensions = ! empty( $this->field_data['extensions'] ) ? explode( ',', $this->field_data['extensions'] ) : $default_extensions;

		return wpforms_chain( $extensions )
			->map(
				static function ( $ext ) {

					return strtolower( preg_replace( '/[^A-Za-z0-9_-]/', '', $ext ) );
				}
			)
			->array_filter()
			->array_intersect( $default_extensions )
			->value();
	}

	/**
	 * Determine the max-allowed file size in bytes as per field options.
	 *
	 * @since 1.9.8
	 *
	 * @return int Number of bytes allowed.
	 */
	public function max_file_size(): int {

		if ( ! empty( $this->field_data['max_size'] ) ) {

			// Strip any suffix provided (e.g., M, MB, etc.), which leaves us with the raw MB value.
			$max_size = preg_replace( '/[^0-9.]/', '', $this->field_data['max_size'] );

			return wpforms_size_to_bytes( $max_size . 'M' );
		}

		return (int) wpforms_max_upload( true );
	}

	/**
	 * Get default extensions supported by WordPress
	 * without those that we manually denylist.
	 *
	 * @since 1.9.8
	 *
	 * @return array
	 */
	protected function get_default_extensions(): array {

		return wpforms_chain( get_allowed_mime_types() )
			->array_keys()
			->implode( '|' )
			->explode( '|' )
			->array_diff( $this->denylist )
			->value();
	}
}
