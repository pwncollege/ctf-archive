<?php

namespace WPForms\Integrations\AI\Admin\Pages;

use WPForms\Integrations\LiteConnect\LiteConnect;

/**
 * Enqueue assets on the Form Templates admin page.
 *
 * @since 1.9.2
 */
class Templates {

	/**
	 * Initialize.
	 *
	 * @since 1.9.2
	 */
	public function init(): void {

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.9.2
	 */
	private function hooks(): void {

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueues' ] );
		add_action( 'wpforms_admin_form_templates_list_before', [ $this, 'output_card' ] );
	}

	/**
	 * Enqueue styles and scripts.
	 *
	 * @since 1.9.2
	 */
	public function enqueues(): void {

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			'wpforms-ai-forms-admin',
			WPFORMS_PLUGIN_URL . "assets/css/integrations/ai/form-templates-page$min.css",
			[],
			WPFORMS_VERSION
		);
	}

	/**
	 * Output Generate with AI card.
	 *
	 * @since 1.9.3
	 *
	 * @noinspection HtmlUnknownTarget
	 * @noinspection HtmlUnknownAttribute
	 */
	public function output_card(): void {

		$button_class = 'wpforms-template-generate';
		$button_attr  = '';

		// In Lite, we should disable the button in the case Lite Connect is not allowed.
		if ( ! LiteConnect::is_allowed() && ! wpforms()->is_pro() ) {
			$button_class .= ' wpforms-inactive wpforms-help-tooltip';
			$button_attr   = sprintf(
				'data-tooltip-position="top" title="%1$s"',
				esc_html__( 'WPForms AI is not available on local sites.', 'wpforms-lite' )
			);
		}

		printf(
			'<div class="wpforms-template" id="wpforms-template-generate">
				<div class="wpforms-template-thumbnail">
					<div class="wpforms-template-thumbnail-placeholder">
						<img src="%1$s" alt="%2$s" loading="lazy">
					</div>
				</div>
				<div class="wpforms-template-name-wrap">
					<h3 class="wpforms-template-name categories has-access favorite slug subcategories fields" data-categories="all,new" data-subcategories="" data-fields="" data-has-access="1" data-favorite="" data-slug="generate">
						%2$s
					</h3>
					<span class="wpforms-badge wpforms-badge-sm wpforms-badge-inline wpforms-badge-purple wpforms-badge-rounded">%3$s</span>
				</div>
				<p class="wpforms-template-desc">
					%4$s
				</p>
				<div class="wpforms-template-buttons">
					<a href="#" class="%5$s wpforms-btn wpforms-btn-md wpforms-btn-purple-dark" %6$s>
						%7$s
					</a>
				</div>
			</div>',
			esc_url( WPFORMS_PLUGIN_URL ) . 'assets/images/integrations/ai/ai-feature-icon.svg',
			esc_html__( 'Generate With AI', 'wpforms-lite' ),
			esc_html__( 'NEW!', 'wpforms-lite' ),
			esc_html__( 'Write simple prompts to create complex forms catered to your specific needs.', 'wpforms-lite' ),
			esc_attr( $button_class ),
			$button_attr, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_html__( 'Generate Form', 'wpforms-lite' )
		);
	}
}
