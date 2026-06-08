<?php

namespace WPForms\Lite\Integrations\Elementor;

use WPForms\Integrations\Elementor\ThemesData as ThemesDataBase;

/**
 * Themes data for Gutenberg block for Lite.
 *
 * @since 1.9.6
 */
class ThemesData extends ThemesDataBase {

	/**
	 * WPForms themes JSON file path.
	 *
	 * Relative to the WPForms plugin directory.
	 *
	 * @since 1.9.6
	 *
	 * @var string
	 */
	protected const THEMES_WPFORMS_JSON_PATH = 'assets/lite/js/integrations/elementor/themes.json';
}
