<?php

/**
 * Base panel class.
 *
 * @since 1.0.0
 */
abstract class WPForms_Builder_Panel {

	/**
	 * Full name of the panel.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Slug.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $slug;

	/**
	 * Font Awesome Icon used for the editor button, eg "fa-list".
	 *
	 * @since 1.0.0
	 *
	 * @var mixed
	 */
	public $icon = false;

	/**
	 * Priority order the field button should show inside the "Add Fields" tab.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	public $order = 50;

	/**
	 * If panel contains a sidebar element or is full width.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	public $sidebar = false;

	/**
	 * Determine whether the panel content will be loaded on demand.
	 *
	 * @since 1.8.6
	 *
	 * @var bool
	 */
	public $on_demand = false;

	/**
	 * Contain form object if we have one.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public $form;

	/**
	 * Contain array of the form data (post_content).
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $form_data;

	/**
	 * Class instance.
	 *
	 * @since 1.7.7
	 *
	 * @var static
	 */
	private static $instance;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Load form if found.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$form_id    = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : false;
		$this->form = wpforms()->obj( 'form' )->get( $form_id );

		$this->form_data = $this->form ? wpforms_decode( $this->form->post_content ) : false;

		// Get current revision, if available.
		$revision = wpforms()->obj( 'revisions' )->get_revision();

		// If we're viewing a valid revision, replace the form data so the Form Builder shows correct state.
		if ( $revision && isset( $revision->post_content ) ) {
			$this->form_data = wpforms_decode( $revision->post_content );
		}

		// Bootstrap.
		$this->init();

		// Save instance.
		self::$instance = $this;

		// Primary panel button.
		add_action( 'wpforms_builder_panel_buttons', [ $this, 'button' ], $this->order, 2 );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$is_active = $this->slug === sanitize_key( $_GET['view'] ?? 'setup' );

		if ( $this->on_demand && ! $is_active ) {
			// Load panel loader enqueues.
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueues_loader' ] );
		}


		// Load payments panel enqueues.
		add_action( 'wpforms_builder_enqueues', [ $this, 'enqueues_payments' ] );

		// Load panel specific enqueues.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueues' ], 15 );

		if ( $is_active || ! $this->on_demand ) {
			// Output.
			add_action( 'wpforms_builder_panels', [ $this, 'panel_output' ], $this->order, 2 );
		}
	}

	/**
	 * Get class instance.
	 *
	 * @since 1.7.7
	 *
	 * @return static
	 */
	public static function instance() {

		if ( self::$instance === null || ! self::$instance instanceof static ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * All systems go. Used by children.
	 *
	 * @since 1.0.0
	 */
	public function init() {
	}

	/**
	 * Enqueue assets for the builder. Used by children.
	 *
	 * @since 1.0.0
	 */
	public function enqueues() {
	}

	/**
	 * Enqueue panel loader assets.
	 *
	 * @since 1.8.6
	 */
	public function enqueues_loader() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-builder-panel-loader',
			WPFORMS_PLUGIN_URL . "assets/js/admin/builder/panel-loader{$min}.js",
			[ 'wpforms-builder' ],
			WPFORMS_VERSION,
			true
		);
	}

	/**
	 * Enqueue assets for the payments panel.
	 *
	 * @since 1.9.5
	 */
	public function enqueues_payments() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-builder-payments-utils',
			WPFORMS_PLUGIN_URL . "assets/js/admin/builder/payments-utils{$min}.js",
			[ 'wpforms-builder' ],
			WPFORMS_VERSION,
			true
		);

		$strings = [
			'payments_plan_placeholder'   => esc_html__( 'Plan Name', 'wpforms-lite' ),
			'payments_disabled_recurring' => esc_html__( 'You can only use one payment type at a time. If you\'d like to enable Recurring Payments, please disable One-Time Payments.', 'wpforms-lite' ),
			'payments_disabled_one_time'  => esc_html__( 'You can only use one payment type at a time. If you\'d like to enable One-Time Payments, please disable Recurring Payments.', 'wpforms-lite' ),
		];

		wp_localize_script(
			'wpforms-builder-payments-utils',
			'wpforms_builder_payments_utils',
			$strings
		);
	}

	/**
	 * Primary panel button in the left panel navigation.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $form
	 * @param string $view
	 */
	public function button( $form, $view ) {

		$active = $view === $this->slug ? 'active' : '';
		?>

		<button class="wpforms-panel-<?php echo esc_attr( $this->slug ); ?>-button <?php echo esc_attr( $active ); ?>" data-panel="<?php echo esc_attr( $this->slug ); ?>">
			<i class="fa <?php echo esc_attr( $this->icon ); ?>"></i>
			<span><?php echo esc_html( $this->name ); ?></span>
		</button>

		<?php
	}

	/**
	 * Output the contents of the panel.
	 *
	 * @since 1.0.0
	 *
	 * @param object $form Current form object.
	 * @param string $view Active Form Builder view (panel).
	 */
	public function panel_output( $form, $view ) {

		$wrap    = $this->sidebar ? 'wpforms-panel-sidebar-content' : 'wpforms-panel-full-content';
		$classes = [ 'wpforms-panel' ];

		// Determine whether the form data is corrupted and a dedicated alert message needs to be shown,
		// keep the revisions panel to be able to restore the form.
		$is_form_corrupted = is_array( $this->form_data ) && empty( $this->form_data ) && $this->slug !== 'revisions';

		if ( in_array( $this->slug, [ 'fields', 'revisions' ], true ) ) {
			$classes[] = 'wpforms-panel-fields';
		}

		if ( $view === $this->slug ) {
			$classes[] = 'active';
		}

		if ( $is_form_corrupted ) {
			$classes[] = 'wpforms-panel-corrupted-data';
		}

		printf( '<div class="%s" id="wpforms-panel-%s">', wpforms_sanitize_classes( $classes, true ), esc_attr( $this->slug ) );

		printf( '<div class="%s">', esc_attr( $wrap ) );

		if ( $this->sidebar === true && ! $is_form_corrupted ) {

			if ( $this->slug === 'fields' ) {
				echo '<div class="wpforms-panel-sidebar-toggle"><div class="wpforms-panel-sidebar-toggle-vertical-line"></div><div class="wpforms-panel-sidebar-toggle-icon"><i class="fa fa-angle-left"></i></div></div>';
			}

			echo '<div class="wpforms-panel-sidebar">';

			do_action( 'wpforms_builder_before_panel_sidebar', $this->form, $this->slug );

			$this->panel_sidebar();

			do_action( 'wpforms_builder_after_panel_sidebar', $this->form, $this->slug );

			echo '</div>';

			/**
			 * Allow adding custom content after the panel sidebar in the Form Builder.
			 *
			 * @since 1.9.7
			 *
			 * @param object $form Current form object.
			 * @param string $slug Current panel slug.
			 */
			do_action( 'wpforms_builder_panel_sidebar_after', $this->form, $this->slug );
		}

		echo '<div class="wpforms-panel-content-wrap">';

		echo '<div class="wpforms-panel-content">';

		if ( $is_form_corrupted ) {
			$this->form_corrupted_message();
		} else {

			/**
			 * Allow adding custom content before the panel content in the Form Builder.
			 *
			 * @since 1.0.0
			 *
			 * @param object $form Current form object.
			 * @param string $slug Current panel slug.
			 */
			do_action( 'wpforms_builder_before_panel_content', $this->form, $this->slug ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

			$this->panel_content();

			/**
			 * Allow adding custom content after the panel content in the Form Builder.
			 *
			 * @since 1.0.0
			 *
			 * @param object $form Current form object.
			 * @param string $slug Current panel slug.
			 */
			do_action( 'wpforms_builder_after_panel_content', $this->form, $this->slug ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
		}

		echo '</div>';

		echo '</div>';

		echo '</div>';

		echo '</div>';
	}

	/**
	 * Output the panel's sidebar if we have one.
	 *
	 * @since 1.0.0
	 */
	public function panel_sidebar() {
	}

	/**
	 * Output panel sidebar sections.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Sidebar section name.
	 * @param string $slug Sidebar section slug.
	 * @param string $icon Sidebar section icon.
	 */
	public function panel_sidebar_section( $name, $slug, $icon = '' ) {

		$default_classes = [
			'wpforms-panel-sidebar-section',
			'wpforms-panel-sidebar-section-' . $slug,
		];

		if ( $slug === 'default' ) {
			$default_classes[] = 'default';
		}

		if ( ! empty( $icon ) ) {
			$default_classes[] = 'icon';
		}

		/**
		 * Allow adding custom CSS classes to a sidebar section in the Form Builder.
		 *
		 * @since 1.7.7.2
		 *
		 * @param array  $classes Sidebar section classes.
		 * @param string $name    Sidebar section name.
		 * @param string $slug    Sidebar section slug.
		 * @param string $icon    Sidebar section icon.
		 */
		$classes = (array) apply_filters( 'wpforms_builder_panel_sidebar_section_classes', [], $name, $slug, $icon );
		$classes = array_merge( $default_classes, $classes );

		echo '<a href="#" class="' . wpforms_sanitize_classes( $classes, true ) . '" data-section="' . esc_attr( $slug ) . '">';

		if ( ! empty( $icon ) ) {
			echo '<img src="' . esc_url( $icon ) . '">';
		}

		echo esc_html( $name );

		echo '<i class="fa fa-angle-right wpforms-toggle-arrow"></i>';

		echo '</a>';
	}

	/**
	 * Output the panel's primary content.
	 *
	 * @since 1.0.0
	 */
	public function panel_content() {
	}

	/**
	 * Error message for a corrupted form.
	 *
	 * @since 1.9.7
	 */
	private function form_corrupted_message(): void {
		?>

		<div class="wpforms-builder-preview-corrupted-data-content">
			<div class="wpforms-builder-corrupted-data-title">
				<h2>
					<?php esc_html_e( 'Corrupted Form Data', 'wpforms-lite' ); ?>
				</h2>
			</div>
			<div>
				<p>
					<?php
					printf(
						wp_kses(
							__( 'A critical error has occurred, preventing your form from loading. This issue may arise from incorrect code in a third-party theme or plugin, or from invalid characters in your form. You can attempt to restore a previous version of your form using <a href="#" class="wpforms-panel-content-revisions-link">Form Revisions</a>.', 'wpforms-lite' ),
							[
								'a' => [
									'href'  => [],
									'class' => [],
								],
							]
						)
					);
					?>
				</p>
				<br>
				<p>
					<?php
					printf(
						wp_kses( /* translators: %s - WPForms contact support link. */
							__( 'If the issue persists, <a href="%s" target="_blank" rel="noopener noreferrer">please contact support</a>.', 'wpforms-lite' ),
							[
								'a' => [
									'href'   => [],
									'target' => [],
									'rel'    => [],
								],
							]
						),
						esc_url( wpforms_utm_link( 'https://wpforms.com/account/support/', 'Corrupted Form Data' ) )
					);
					?>
				</p>
			</div>
		</div>
		<?php
	}
}
