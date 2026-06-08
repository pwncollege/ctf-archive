<?php

namespace WPForms\Lite\Integrations\Gutenberg;

use WPForms\Integrations\Gutenberg\ThemesData as ThemesDataBase;

/**
 * Themes data for Gutenberg block for Lite.
 *
 * @since 1.8.8
 */
class ThemesData extends ThemesDataBase {

	/**
	 * WPForms themes JSON file path.
	 *
	 * Relative to WPForms plugin directory.
	 *
	 * @since 1.8.8
	 *
	 * @var string
	 */
	const THEMES_WPFORMS_JSON_PATH = 'assets/lite/js/integrations/gutenberg/themes.json';
}
