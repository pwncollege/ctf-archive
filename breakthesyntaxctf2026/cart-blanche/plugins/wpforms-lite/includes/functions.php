<?php
/**
 * Global functions used in core plugin and addons.
 *
 * @since 1.0.0
 * @since 1.8.0 Split into multiple files, see `includes/functions/`.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/functions/access.php';
require_once __DIR__ . '/functions/builder.php';
require_once __DIR__ . '/functions/checks.php';
require_once __DIR__ . '/functions/colors.php';
require_once __DIR__ . '/functions/data-presets.php';
require_once __DIR__ . '/functions/date-time.php';
require_once __DIR__ . '/functions/debug.php';
require_once __DIR__ . '/functions/education.php';
require_once __DIR__ . '/functions/escape-sanitize.php';
require_once __DIR__ . '/functions/filesystem-media.php';
require_once __DIR__ . '/functions/form-fields.php';
require_once __DIR__ . '/functions/forms.php';
require_once __DIR__ . '/functions/list.php';
require_once __DIR__ . '/functions/payments.php';
require_once __DIR__ . '/functions/plugins.php';
require_once __DIR__ . '/functions/privacy.php';
require_once __DIR__ . '/functions/providers.php';
require_once __DIR__ . '/functions/utilities.php';
