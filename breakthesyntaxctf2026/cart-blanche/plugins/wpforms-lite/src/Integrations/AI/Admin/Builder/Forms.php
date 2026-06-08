<?php

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection AutoloadingIssuesInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace WPForms\Integrations\AI\Admin\Builder;

use WP_Post;
use WPForms\Integrations\AI\Admin\Ajax\Forms as FormsAjax;
use WPForms\Integrations\AI\Helpers;
use WPForms\Integrations\LiteConnect\LiteConnect;

/**
 * Enqueue assets on the Form Builder screen in Pro.
 *
 * @since 1.9.2
 * @since 1.9.4 Moved to the Lite plugin namespace.
 */
class Forms {

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

		add_action( 'wpforms_builder_enqueues', [ $this, 'enqueues' ] );
		add_filter( 'wpforms_integrations_ai_admin_builder_enqueues_localize_chat_strings', [ $this, 'add_localize_chat_data' ] );
		add_filter( 'wpforms_builder_template_active', [ $this, 'template_active' ], 10, 2 );
	}

	/**
	 * Enqueue styles and scripts.
	 *
	 * @since 1.9.2
	 *
	 * @param string|null $view Current view (panel).
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function enqueues( $view ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found

		$this->enqueue_styles();
		$this->enqueue_scripts();
	}

	/**
	 * Enqueue styles.
	 *
	 * @since 1.9.2
	 */
	private function enqueue_styles(): void {

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			'wpforms-ai-forms',
			WPFORMS_PLUGIN_URL . "assets/css/integrations/ai/ai-forms{$min}.css",
			[],
			WPFORMS_VERSION
		);
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.9.2
	 */
	private function enqueue_scripts(): void {

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-ai-form-generator',
			WPFORMS_PLUGIN_URL . "assets/js/integrations/ai/form-generator/form-generator{$min}.js",
			[],
			WPFORMS_VERSION,
			true
		);

		wp_localize_script(
			'wpforms-ai-form-generator',
			'wpforms_ai_form_generator',
			$this->get_localize_form_generator_data()
		);
	}

	/**
	 * Set an active form template.
	 *
	 * @since 1.9.2
	 *
	 * @param array|mixed   $details Details.
	 * @param WP_Post|false $form    Form data.
	 *
	 * @return array
	 */
	public function template_active( $details, $form ): array {

		$details = (array) $details;

		if ( empty( $form ) ) {
			return [];
		}

		$form_data = wpforms_decode( $form->post_content );

		if ( empty( $form_data['meta']['template'] ) || $form_data['meta']['template'] !== 'generate' ) {
			return $details;
		}

		return [
			'name'          => esc_html__( 'Generate With AI', 'wpforms-lite' ),
			'slug'          => 'generate',
			'description'   => '',
			'includes'      => '',
			'icon'          => '',
			'modal'         => '',
			'modal_display' => false,
		];
	}

	/**
	 * Get form generator localize data.
	 *
	 * @since 1.9.2
	 *
	 * @return array
	 * @noinspection HtmlUnknownTarget
	 */
	private function get_localize_form_generator_data(): array {

		$min          = wpforms_get_min_suffix();
		$addons_data  = $this->get_required_addons_data();
		$modules_path = './modules/';

		return [
			'nonce'              => wp_create_nonce( 'wpforms-ai-nonce' ),
			'adminNonce'         => wp_create_nonce( 'wpforms-admin' ),
			'ajaxUrl'            => admin_url( 'admin-ajax.php' ),
			'addonsData'         => $addons_data,
			'addonsAction'       => $this->get_required_addons_action( $addons_data ),
			'addonFields'        => FormsAjax::FORM_GENERATOR_ADDON_FIELDS,
			'dismissed'          => $this->get_dismissed_elements(),
			'isPro'              => wpforms()->is_pro(),
			'isLicenseActive'    => Helpers::is_license_active(),
			'licenseType'        => Helpers::get_license_type(),
			'liteConnectEnabled' => LiteConnect::is_enabled(),
			'liteConnectAllowed' => LiteConnect::is_allowed(),
			'modules'            => [
				'main'    => $modules_path . "main{$min}.js?ver=" . WPFORMS_VERSION,
				'preview' => $modules_path . "preview{$min}.js?ver=" . WPFORMS_VERSION,
				'modals'  => $modules_path . "modals{$min}.js?ver=" . WPFORMS_VERSION,
			],
			'templateCard'       => [
				'imageSrc'              => WPFORMS_PLUGIN_URL . 'assets/images/integrations/ai/ai-feature-icon.svg',
				'name'                  => esc_html__( 'Generate With AI', 'wpforms-lite' ),
				'desc'                  => esc_html__( 'Write simple prompts to create complex forms catered to your specific needs.', 'wpforms-lite' ),
				'buttonTextInit'        => esc_html__( 'Generate Form', 'wpforms-lite' ),
				'buttonTextContinue'    => esc_html__( 'Continue Generating', 'wpforms-lite' ),
				'new'                   => esc_html__( 'NEW!', 'wpforms-lite' ),
				'liteConnectNotAllowed' => esc_html__( 'WPForms AI is not available on local sites.', 'wpforms-lite' ),
			],
			'panel'              => [
				'backToTemplates' => esc_html__( 'Back to Templates', 'wpforms-lite' ),
				'emptyStateTitle' => esc_html__( 'Build Your Form Fast With the Help of AI', 'wpforms-lite' ),
				'emptyStateDesc'  => esc_html__( 'Not sure where to begin? Use our Generative AI tool to get started or take your pick from our wide variety of fields and start building out your form!', 'wpforms-lite' ),
				'submitButton'    => esc_html__( 'Submit', 'wpforms-lite' ),
				'tooltipTitle'    => esc_html__( 'This is just a preview of your form.', 'wpforms-lite' ),
				'tooltipText'     => esc_html__( 'Click "Use This Form" to start editing.', 'wpforms-lite' ),
			],
			'addons'             => [
				'installTitle'              => esc_html__( 'Before We Proceed', 'wpforms-lite' ),
				'installContent'            => esc_html__( 'In order to build the best forms possible, we need to install some addons. Would you like to install the recommended addons?', 'wpforms-lite' ),
				'activateContent'           => esc_html__( 'In order to build the best forms possible, we need to activate some addons. Would you like to activate the recommended addons?', 'wpforms-lite' ),
				'installConfirmButton'      => esc_html__( 'Yes, Install', 'wpforms-lite' ),
				'activateConfirmButton'     => esc_html__( 'Yes, Activate', 'wpforms-lite' ),
				'cancelButton'              => esc_html__( 'No, Thanks', 'wpforms-lite' ),
				'dontShow'                  => esc_html__( 'Don\'t show this again', 'wpforms-lite' ),
				'okay'                      => esc_html__( 'Okay', 'wpforms-lite' ),
				'installing'                => esc_html__( 'Installing...', 'wpforms-lite' ),
				'activating'                => esc_html__( 'Activating...', 'wpforms-lite' ),
				'addonsInstalledTitle'      => esc_html__( 'Addons Installed', 'wpforms-lite' ),
				'addonsActivatedTitle'      => esc_html__( 'Addons Activated', 'wpforms-lite' ),
				'addonsInstalledContent'    => esc_html__( 'You’re all set. We’re going to reload the builder and you can start building your form.', 'wpforms-lite' ),
				'addonsInstallErrorTitle'   => esc_html__( 'Addons Installation Error', 'wpforms-lite' ),
				'addonsActivateErrorTitle'  => esc_html__( 'Addons Activation Error', 'wpforms-lite' ),
				'addonsInstallError'        => esc_html__( 'Can\'t install or activate the required addons.', 'wpforms-lite' ),
				'addonsInstallErrorNetwork' => esc_html__( 'There appears to be a network error.', 'wpforms-lite' ),
				'dismissErrorTitle'         => esc_html__( 'Error', 'wpforms-lite' ),
				'dismissError'              => esc_html__( 'Can\'t dismiss the modal window.', 'wpforms-lite' ),
				'addon'                     => esc_html__( 'Addon', 'wpforms-lite' ),
				'and'                       => esc_html__( 'and', 'wpforms-lite' ),
				'addonInstalledTitle'       => esc_html__( 'Addon Installed', 'wpforms-lite' ),
				'addonActivatedTitle'       => esc_html__( 'Addon Activated', 'wpforms-lite' ),
				'addonInstalledContent'     => esc_html__( 'You’re all set. We’re going to continue building your form.', 'wpforms-lite' ),
			],
			'quiz'               => [
				'modalTitle'   => esc_html__( 'Quiz Detected', 'wpforms-lite' ),
				'modalContent' => sprintf(
					wp_kses( /* translators: %1$s - Quiz addon doc link. */
						__( 'It looks like you\'re trying to create a quiz. Would you like to activate the <a href="%1$s" target="_blank" rel="noopener noreferrer">Quiz Addon</a> and easily create graded, personality, and weighted quizzes?', 'wpforms-lite' ),
						[
							'a' => [
								'href'   => [],
								'rel'    => [],
								'target' => [],
							],
						]
					),
					// @TODO: Confirm the URL.
					esc_url( wpforms_utm_link( 'https://wpforms.com/docs/quiz-addon/', 'builder-modal',  'Quiz Addon Documentation' ) )
				),
			],
			'previewNotice'      => [
				'title'      => esc_html__( 'This Form Would Be Even Better With Fields From', 'wpforms-lite' ),
				'msgUpgrade' => wp_kses( /* translators: %1$s - Upgrade to Pro link attributes. */
					__( '<a href="#">Upgrade to Pro</a> and gain access to all fields and create the best possible forms.', 'wpforms-lite' ),
					[
						'a' => [
							'href' => [],
						],
					]
				),
				'btnUpgrade' => esc_html__( 'Upgrade to Pro', 'wpforms-lite' ),
				'addons'     => esc_html__( 'Addons', 'wpforms-lite' ),
				'dismiss'    => esc_html__( 'Dismiss this notice', 'wpforms-lite' ),
			],
			'misc'               => [
				'warningExistingForm'    => esc_html__( 'You’re about to overwrite your existing form. This will delete all fields and reset external connections. Are you sure you want to continue?', 'wpforms-lite' ),
				'frozenChallengeTooltip' => esc_html__( 'The challenge will continue once AI form generation is complete', 'wpforms-lite' ),
			],
		];
	}

	/**
	 * Add chat element localize data.
	 *
	 * @since 1.9.2
	 *
	 * @param array $strings Strings.
	 *
	 * @return array
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection HtmlUnknownTarget
	 */
	public function add_localize_chat_data( $strings ): array {

		$for_lite = wpforms()->is_pro() ? '' : ' for Lite';

		$strings['forms'] = [
			'title'               => esc_html__( 'Generate a Form', 'wpforms-lite' ),
			'description'         => esc_html__( 'Describe the form you would like to create or use one of the example prompts below to get started.', 'wpforms-lite' ),
			'descrEndDot'         => '',
			'learnMore'           => esc_html__( 'Learn More About WPForms AI', 'wpforms-lite' ),
			'learnMoreUrl'        => wpforms_utm_link( 'https://wpforms.com/features/wpforms-ai/', 'Builder - Settings', 'Learn more - AI Forms' . $for_lite ),
			'inactiveAnswerTitle' => esc_html__( 'Go back to this version of the form', 'wpforms-lite' ),
			'useForm'             => esc_html__( 'Use This Form', 'wpforms-lite' ),
			'placeholder'         => esc_html__( 'What would you like to create?', 'wpforms-lite' ),
			'waiting'             => esc_html__( 'Just a minute...', 'wpforms-lite' ),
			'errors'              => [
				'default'    => esc_html__( 'An error occurred while generating form.', 'wpforms-lite' ),
				'rate_limit' => esc_html__( 'Sorry, you\'ve reached your daily limit for generating forms.', 'wpforms-lite' ),
			],
			'footer'              => [
				esc_html__( 'What do you think of the form I created for you? If you’re happy with it, you can use this form. Otherwise, make changes by entering additional prompts.', 'wpforms-lite' ),
				esc_html__( 'How’s that? Are you ready to use this form?', 'wpforms-lite' ),
				esc_html__( 'Does this look good? Are you ready to implement this form?', 'wpforms-lite' ),
				esc_html__( 'Is this what you had in mind? Are you satisfied with the results?', 'wpforms-lite' ),
				esc_html__( 'Happy with the form? Ready to move forward?', 'wpforms-lite' ),
				esc_html__( 'Is this form a good fit for your needs? Can we proceed?', 'wpforms-lite' ),
				esc_html__( 'Are you pleased with the outcome? Ready to use this form?', 'wpforms-lite' ),
				esc_html__( 'Does this form meet your expectations? Can we move on to the next step?', 'wpforms-lite' ),
				esc_html__( 'Is this form what you were envisioning? Are you ready to use it?', 'wpforms-lite' ),
				esc_html__( 'Satisfied with the form? Let\'s use it!', 'wpforms-lite' ),
				esc_html__( 'Does this form align with your goals? Are you ready to implement it?', 'wpforms-lite' ),
				esc_html__( 'Happy with the results? Let\'s put this form to work!', 'wpforms-lite' ),
			],
			'reasons'             => [
				'default'    => sprintf(
					wp_kses( /* translators: %1$s - Reload link class. */
						__( '<a href="#" class="%1$s">Reload this window</a> and try again.', 'wpforms-lite' ),
						[
							'a' => [
								'href'  => [],
								'class' => [],
							],
						]
					),
					'wpforms-ai-chat-reload-link'
				),
				'rate_limit' => sprintf(
					wp_kses( /* translators: %s - WPForms contact support link. */
						__( 'You may only generate forms 50 times per day. If you believe this is an error, <a href="%s" target="_blank" rel="noopener noreferrer">please contact WPForms support</a>.', 'wpforms-lite' ),
						[
							'a' => [
								'href'   => [],
								'target' => [],
								'rel'    => [],
							],
						]
					),
					wpforms_utm_link( 'https://wpforms.com/account/support/', 'AI Feature' )
				),
			],
			'samplePrompts'       => [
				[
					'icon'  => 'wpforms-ai-chat-sample-restaurant',
					'title' => esc_html__( 'Restaurant customer satisfaction survey', 'wpforms-lite' ),
				],
				[
					'icon'  => 'wpforms-ai-chat-sample-ticket',
					'title' => esc_html__( 'Online event registration', 'wpforms-lite' ),
				],
				[
					'icon'  => 'wpforms-ai-chat-sample-design',
					'title' => esc_html__( 'Job application for a web designer', 'wpforms-lite' ),
				],
				[
					'icon'  => 'wpforms-ai-chat-sample-stop',
					'title' => esc_html__( 'Cancellation survey for a subscription', 'wpforms-lite' ),
				],
				[
					'icon'  => 'wpforms-ai-chat-sample-pizza',
					'title' => esc_html__( 'Takeout order for a pizza store', 'wpforms-lite' ),
				],
				[
					'icon'  => 'wpforms-ai-chat-sample-market',
					'title' => esc_html__( 'Market vendor application', 'wpforms-lite' ),
				],
				[
					'icon'   => 'wpforms-ai-chat-sample-quiz-capitals',
					'title'  => esc_html__( 'How well do you know world capitals?', 'wpforms-lite' ),
					'prompt' => esc_html__( 'Create a graded quiz on the topic of "How well do you know world capitals?" with 10 questions and 3 answers each. Randomize the choices. Collect the user\'s name and email address. Create 4 outcomes set to Graded Quiz type with appropriate text for each grade and utilize the available smart tags. The graded outcomes should be if Quiz Grade is A, if Quiz Grade is B, if Quiz Grade is C, if Quiz Grade is D, and if Quiz Grade is F.', 'wpforms-lite' ),
				],
				[
					'icon'   => 'wpforms-ai-chat-sample-quiz-learn',
					'title'  => esc_html__( 'What is your ideal learning style?', 'wpforms-lite' ),
					'prompt' => esc_html__( 'Create a personality quiz on the topic of "What is your ideal learning style?" with 10 questions and 4 answers each. The personalities are Visual, Auditory, Reading/Writing, and Kinesthetic. Collect the user\'s name and email address. Create 4 outcomes set to Personality Quiz type with neutral text and utilize the available smart tags.', 'wpforms-lite' ),
				],
				[
					'icon'   => 'wpforms-ai-chat-sample-quiz-business',
					'title'  => esc_html__( 'How prepared are you to start a business?', 'wpforms-lite' ),
					'prompt' => esc_html__( 'Create a weighted quiz on the topic of "How prepared are you to start a business?" with 10 questions and 3 answers each. Collect the user\'s name and email address. Create 3 outcomes set to Weighted Quiz type for greater than 74%, less than 75% and greater than 49%, and less than 50%.', 'wpforms-lite' ),
				],
			],
		];

		$user_id = get_current_user_id();

		// Get the chat session stored in user meta.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['session'] ) ) {
			$session_id = sanitize_text_field( wp_unslash( $_GET['session'] ) );
			$meta       = get_user_meta( $user_id, 'wpforms_builder_ai_form_chat_' . $session_id, true );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		// If we have the meta-data, add it to the strings.
		if ( ! empty( $meta ) ) {
			// Remove user meta after using it.
			delete_user_meta( $user_id, 'wpforms_builder_ai_form_chat_' . ( $session_id ?? '' ) );

			$strings['forms']['chatHtml']        = $meta['chatHtml'];
			$strings['forms']['responseHistory'] = $meta['responseHistory'];
		}

		return $strings;
	}

	/**
	 * Get required addons' data.
	 *
	 * @since 1.9.2
	 *
	 * @return array
	 */
	private function get_required_addons_data(): array {

		// The addon installation procedure has floating issues in PHP < 7.4.
		// It's better to skip the installation in this case to avoid addon installation errors.
		if ( PHP_VERSION_ID < 70400 ) {
			return [];
		}

		$addons_obj = wpforms()->obj( 'addons' );

		if ( ! $addons_obj ) {
			return [];
		}

		$data = [];

		// Get the URLs for the required addons.
		foreach ( FormsAjax::FORM_GENERATOR_REQUIRED_ADDONS as $slug ) {
			$addon         = $addons_obj->get_addon( $slug );
			$data[ $slug ] = $this->get_required_addon_data( $addon );
		}

		return array_filter( $data );
	}

	/**
	 * Get required addon data.
	 *
	 * @since 1.9.9
	 *
	 * @param array|mixed $addon Addon data.
	 *
	 * @return array|null
	 */
	private function get_required_addon_data( $addon ): ?array {

		if (
			empty( $addon ) || // Exceptional case when `addons.json` is not loaded.

			// This means that addon is already installed and active.
			( isset( $addon['status'] ) && $addon['status'] === 'active' ) ||

			// This means that addon is not available in the current license.
			// We should skip in this case as it is impossible to install or activate the addon.
			( isset( $addon['action'] ) && $addon['action'] === 'upgrade' )
		) {
			return null;
		}

		return [
			'url'  => $addon['url'] ?? '',
			'path' => $addon['path'] ?? '',
		];
	}

	/**
	 * Get required addons action.
	 *
	 * @since 1.9.2
	 *
	 * @param array $addons_data Addons data.
	 *
	 * @return string
	 */
	private function get_required_addons_action( array $addons_data ): string {

		if ( empty( $addons_data ) ) {
			return '';
		}

		foreach ( $addons_data as $data ) {
			if ( ! empty( $data['url'] ) ) {
				return 'install';
			}
		}

		return 'activate';
	}

	/**
	 * Get dismissed elements data.
	 *
	 * @since 1.9.2
	 *
	 * @return array
	 */
	private function get_dismissed_elements(): array {

		$user_id = get_current_user_id();

		// Dismissed elements.
		$dismissed = get_user_meta( $user_id, 'wpforms_dismissed', true );

		return [
			'installAddons' => ! empty( $dismissed['edu-ai-forms-install-addons-modal'] ),
			'previewNotice' => ! empty( $dismissed['edu-ai-forms-preview-addons-notice'] ),
		];
	}
}
