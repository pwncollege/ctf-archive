<?php

namespace WPForms\Admin\Education\Pointers;

/**
 * Abstract class representing Pointers functionality.
 *
 * This abstract class provides a foundation for implementing pointers in WPForms.
 * Child classes should extend this class and implement the necessary methods to set properties and allow loading.
 *
 * The class separates concerns by implementing methods for different functionalities such as initializing pointers,
 * handling interactions, printing scripts, etc., which enhances code maintainability and security.
 * Additionally, the class is designed to be abstract, allowing for customization and extension while enforcing certain security measures in child classes.
 *
 * @since 1.8.8
 */
abstract class Pointer {

	/**
	 * Unique ID for the pointer.
	 *
	 * @since 1.8.8
	 *
	 * @var string
	 */
	protected $pointer_id;

	/**
	 * Selector for the pointer.
	 *
	 * @since 1.8.8
	 *
	 * @var string
	 */
	protected $selector;

	/**
	 * Arguments for the pointer.
	 *
	 * @since 1.8.8
	 *
	 * @var array
	 */
	protected $args;

	/**
	 * Top-level menu selector.
	 *
	 * @since 1.8.8
	 *
	 * @var string
	 */
	private $top_level_menu = '#toplevel_page_wpforms-overview';

	/**
	 * Determines whether the pointer should be visible outside the "WPForms" primary menu.
	 * Note that setting this property to true will display the pointer on other dashboard pages as well.
	 *
	 * @since 1.8.8
	 *
	 * @var string
	 */
	protected $top_level_visible = false;

	/**
	 * Option name for storing interactions with pointers.
	 *
	 * @since 1.8.8
	 */
	private const OPTION_NAME = 'wpforms_pointers';

	/**
	 * Initialize the pointer.
	 *
	 * @since 1.8.8
	 */
	public function init(): void {

		// If loading is not allowed, or if the pointer is already dismissed, return.
		if ( ! $this->allow_display() || ! $this->allow_load() ) {
			return;
		}

		// Set initial arguments.
		$this->set_initial_args();

		// Register hooks.
		$this->hooks();
	}

	/**
	 * Check if the pointer is already dismissed or interacted with.
	 *
	 * @since 1.8.8
	 *
	 * @return bool
	 */
	private function allow_display(): bool {

		// If the pointer ID is empty, return.
		// Check if announcements are allowed to be displayed.
		if ( empty( $this->pointer_id ) || wpforms_setting( 'hide-announcements' ) ) {
			return false;
		}

		// Get pointers.
		$pointers = (array) get_option( self::OPTION_NAME, [] );

		// Check if the pointer ID exists in the engagement list.
		if ( isset( $pointers['engagement'] ) && in_array( $this->pointer_id, (array) $pointers['engagement'], true ) ) {
			return false;
		}

		// Check if the pointer ID exists in the dismissed list.
		if ( isset( $pointers['dismiss'] ) && in_array( $this->pointer_id, (array) $pointers['dismiss'], true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Register hooks for the pointer.
	 *
	 * @since 1.8.8
	 */
	private function hooks(): void {

		// Enqueue assets.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );

		// Print the pointer script.
		add_action( 'admin_print_footer_scripts', [ $this, 'print_script' ] );

		// Add Ajax callback for the engagement.
		add_action( 'wp_ajax_wpforms_education_pointers_engagement', [ $this, 'engagement_callback' ] );

		// Add Ajax callback for dismissing the pointer.
		add_action( 'wp_ajax_wpforms_education_pointers_dismiss', [ $this, 'dismiss_callback' ] );
	}

	/**
	 * Enqueue assets for the pointer.
	 *
	 * @since 1.8.8
	 */
	public function enqueue_assets() {

		// Enqueue the pointer CSS.
		wp_enqueue_style( 'wp-pointer' );

		// Enqueue the pointer script.
		wp_enqueue_script( 'wp-pointer' );
	}

	/**
	 * Print the pointer script.
	 *
	 * @since 1.8.8
	 */
	public function print_script(): void {

		// Encode the $args array into JSON format.
		$encoded_args = $this->get_prepared_args();

		if ( empty( $encoded_args ) ) {
			return;
		}

		// Sanitize pointer ID and selector.
		$pointer_id = sanitize_text_field( $this->pointer_id );
		$selector   = sanitize_text_field( $this->get_selector() );

		// Get the admin-ajax URL.
		$ajaxurl = esc_url_raw( admin_url( 'admin-ajax.php' ) );

		// Create nonce for the pointer.
		$nonce = sanitize_text_field( $this->get_nonce_token() );

		// Menu flyout selector.
		$menu_flyout = "{$this->top_level_menu}:not(.wp-menu-open)";

		// Inline CSS style id.
		$inline_css_id = "wpforms-{$pointer_id}-inline-css";

		// The type of echo being used in this PHP code is a HEREDOC syntax.
		// HEREDOC allows you to create strings that span multiple lines without
		// needing to concatenate them with dots (.) as you would with double quotes.

		// phpcs:disable
		echo <<<HTML
		<script type="text/javascript">
		( function( $ ) {
			let options = $encoded_args, setup;

			if ( ! options ) {
				return;
			}

			options = $.extend( options, {
				show: function() {
					if ( ! $( '#$inline_css_id' ).length && $( '$menu_flyout' ).length ) {
						$( '<style id="$inline_css_id">' ).text( '$menu_flyout:after, $menu_flyout .wp-submenu-wrap{ display: none }' ).appendTo( 'head' );
					}
				},
				close: function() {
					$( '#$inline_css_id' ).remove();
					$.post(
						'$ajaxurl',
						{
							pointer_id: '$pointer_id',
							_ajax_nonce: '$nonce',
							action: 'wpforms_education_pointers_dismiss',
						}
					);
				}
			} );

			setup = function() {
				$( '$selector' ).first().pointer( options ).pointer( 'open' );
			};

			if ( options.position && options.position.defer_loading ) {
				$( window ).on( 'load.wp-pointers', setup );
			} else {
				$( function() {
					setup();
				} );
			}
		} )( jQuery );
		</script>
HTML;
		// phpcs:enable
	}

	/**
	 * Callback function for engaging with a pointer.
	 *
	 * This function is triggered via AJAX when a user interacts with a pointer, indicating engagement.
	 *
	 * @since 1.8.8
	 */
	public function engagement_callback(): void {

		check_ajax_referer( $this->pointer_id, '_ajax_nonce' );

		if ( ! wpforms_current_user_can() ) {
			wp_send_json_error();
		}

		[ $pointer_id, $pointers ] = $this->handle_pointer_interaction();

		// Add the current pointer to the engagement list.
		$pointers['engagement'][] = $pointer_id;

		// Update the pointer state.
		update_option( self::OPTION_NAME, $pointers );

		// Indicate that the pointer was engaged.
		wp_send_json_success();
	}

	/**
	 * Ajax callback for dismissing the pointer.
	 *
	 * @since 1.8.8
	 */
	public function dismiss_callback(): void {

		check_ajax_referer( $this->pointer_id, '_ajax_nonce' );

		if ( ! wpforms_current_user_can() ) {
			wp_send_json_error();
		}

		[ $pointer_id, $pointers ] = $this->handle_pointer_interaction();

		// Add the current pointer to the dismissed list.
		$pointers['dismiss'][] = $pointer_id;

		// Update the pointer state.
		update_option( self::OPTION_NAME, $pointers );

		// Indicate that the pointer was dismissed.
		wp_send_json_success();
	}

	/**
	 * Get nonce for the pointer.
	 *
	 * @since 1.8.8
	 *
	 * @return string
	 */
	protected function get_nonce_token(): string {

		return wp_create_nonce( $this->pointer_id );
	}

	/**
	 * Handle pointer interaction via AJAX.
	 *
	 * @since 1.8.8
	 *
	 * @return array Pointer ID and pointers state.
	 */
	private function handle_pointer_interaction(): array {

		// Check if the request is valid.
		check_ajax_referer( $this->pointer_id );

		// Get the pointer ID from the request.
		$pointer_id = isset( $_POST['pointer_id'] ) ? sanitize_key( $_POST['pointer_id'] ) : '';

		// If the pointer ID is empty, return an error response.
		if ( empty( $pointer_id ) ) {
			wp_send_json_error();
		}

		// Get the current pointers state.
		$pointers = (array) get_option(
			self::OPTION_NAME,
			[
				'engagement' => [],
				'dismiss'    => [],
			]
		);

		return [ $pointer_id, $pointers ];
	}

	/**
	 * Set initial arguments to use in a pointer.
	 *
	 * @since 1.8.8
	 */
	private function set_initial_args(): void {

		// Set default arguments.
		$this->args = [
			'content'      => '',
			'pointerWidth' => 395,
			'position'     => [
				'edge'  => 'left',
				'align' => 'center',
			],
		];

		// Set additional arguments for the pointer.
		$this->set_args();
	}

	/**
	 * Retrieves the selector based on conditions.
	 *
	 * @since 1.8.8
	 *
	 * @return string
	 */
	private function get_selector(): string {

		// If the sublevel menu is defined, and it's an admin page, return the combined selector.
		if ( ! empty( $this->selector ) && wpforms_is_admin_page() ) {
			return "{$this->top_level_menu} {$this->selector}";
		}

		// Default returns the top-level menu.
		return $this->top_level_menu;
	}

	/**
	 * Prepare and encode args for the pointer.
	 *
	 * @since 1.8.8
	 *
	 * @return string
	 */
	private function get_prepared_args(): string {

		// Retrieve title and message from an argument array, fallback to empty strings if not set.
		$title   = $this->args['title'] ?? '';
		$message = $this->args['message'] ?? '';

		// Return early if both title and message are empty.
		if ( empty( $message ) ) {
			return '';
		}

		// Pointer markup uses <h3> tag for the title and <p> tag for the message.
		$content  = ! empty( $title ) ? sprintf( '<h3>%s</h3>', esc_html( $title ) ) : '';
		$content .= sprintf( '<p style="font-size:14px">%s</p>', wp_kses( $message, $this->get_allowed_html() ) );

		$this->args['content'] = $content;

		// Unset title and message to clean up an argument array.
		unset( $this->args['title'], $this->args['message'] );

		// If RTL and position edge are 'left', switch it to 'right'.
		if ( ! empty( $this->args['position']['edge'] ) && $this->args['position']['edge'] === 'left' && is_rtl() ) {
			$this->args['position']['edge'] = 'right';
		}

		// Encode arguments array to JSON.
		return wp_json_encode( $this->args );
	}

	/**
	 * Get allowed HTML tags for wp_kses.
	 *
	 * @since 1.8.8
	 *
	 * @return array
	 */
	private function get_allowed_html(): array {

		return [
			'a'      => [
				'id'     => [],
				'class'  => [],
				'href'   => [],
				'target' => [],
				'rel'    => [],
			],
			'strong' => [],
			'em'     => [],
			'br'     => [],
		];
	}

	/**
	 * Check if loading of the pointer is allowed.
	 *
	 * @since 1.8.8
	 *
	 * @return bool
	 */
	abstract protected function allow_load(): bool;

	/**
	 * Set arguments for the pointer.
	 *
	 * @since 1.8.8
	 */
	abstract protected function set_args();
}
