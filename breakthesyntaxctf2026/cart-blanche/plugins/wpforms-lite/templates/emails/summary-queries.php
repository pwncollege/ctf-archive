<?php
/**
 * Summary media queries style template.
 *
 * This template can be overridden by copying it to yourtheme/wpforms/emails/summary-queries.php.
 *
 * Note: To override the existing styles of the template in this file, ensure that all
 * overriding styles are declared as !important to take precedence over the default styles.
 *
 * @since 1.8.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$min = wpforms_get_min_suffix();

require WPFORMS_PLUGIN_DIR . "assets/css/emails/partials/summary_media_queries{$min}.css";
