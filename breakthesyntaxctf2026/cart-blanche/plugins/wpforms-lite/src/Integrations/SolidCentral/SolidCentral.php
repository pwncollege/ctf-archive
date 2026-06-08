<?php

namespace WPForms\Integrations\SolidCentral;

/**
 * Class SolidCentral.
 *
 * @since 1.9.2
 */
class SolidCentral {

	/**
	 * Do not allow SolidCentral to set WP_ADMIN to true.
	 *
	 * @since 1.9.2
	 *
	 * @return void
	 */
	public function init() {

		if ( ! defined( 'ITHEMES_SYNC_SKIP_SET_IS_ADMIN_TO_TRUE' ) ) {
			// phpcs:ignore WPForms.Comments.PHPDocDefine.MissPHPDoc, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
			define( 'ITHEMES_SYNC_SKIP_SET_IS_ADMIN_TO_TRUE', true );

			return;
		}

		if ( ! defined( 'WP_ADMIN' ) ) {
			// phpcs:ignore WPForms.Comments.PHPDocDefine.MissPHPDoc, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
			define( 'WP_ADMIN', false );
		}
		// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
	}
}
