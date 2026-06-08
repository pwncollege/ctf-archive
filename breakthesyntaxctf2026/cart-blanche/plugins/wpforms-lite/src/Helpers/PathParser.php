<?php

namespace WPForms\Helpers;

/**
 * Helper to handle folder path parsing and processing.
 *
 * @since 1.10.0
 */
class PathParser {

	/**
	 * Split the folder path by "/" while preserving smart tags intact.
	 *
	 * Smart tags like {entry_date format="d/m/Y"} contain "/" in attributes
	 * which should not be treated as path separators.
	 *
	 * Examples:
	 * - /uploads/wpforms/tmp
	 * - /uploads/wpforms/{date format="d/m/Y"}
	 * - /uploads/wpforms/{entry_date format="d-m-Y"}
	 *
	 * @since 1.10.0
	 *
	 * @param string $folder_path Folder path with forward slashes.
	 *
	 * @return array Array of folder path parts with smart tags preserved.
	 */
	public static function split_folder( string $folder_path ): array {

		$parts         = [];
		$current_part  = '';
		$inside_braces = 0;
		$length        = strlen( $folder_path );

		for ( $i = 0; $i < $length; $i++ ) {
			$char = $folder_path[ $i ];

			if ( $char === '{' ) {
				++$inside_braces;
			} elseif ( $char === '}' ) {
				--$inside_braces;
			}

			if ( $char === '/' && $inside_braces === 0 ) {
				$trimmed = trim( $current_part );

				if ( ! wpforms_is_empty_string( $trimmed ) ) {
					$parts[] = $trimmed;
				}

				$current_part = '';

				continue;
			}

			$current_part .= $char;
		}

		$trimmed = trim( $current_part );

		if ( ! wpforms_is_empty_string( $trimmed ) ) {
			$parts[] = $trimmed;
		}

		return $parts;
	}
}
