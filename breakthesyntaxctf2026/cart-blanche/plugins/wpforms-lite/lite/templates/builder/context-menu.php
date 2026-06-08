<?php
/**
 * WPForms Builder Context Menu (top) Template, Lite version.
 *
 * @since 1.8.8
 *
 * @var int  $form_id          The form ID.
 * @var bool $is_form_template Whether it's a form template (`wpforms-template`), or form (`wpforms`).
 * @var bool $has_payments     Whether the form has payments.
 */

use WPForms\Admin\Education\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
?>

<div class="wpforms-context-menu wpforms-context-menu-dropdown" id="wpforms-context-menu">
	<ul class="wpforms-context-menu-list">

		<?php if ( $is_form_template ) : ?>

			<li class="wpforms-context-menu-list-item"
				data-action="duplicate-template"
				data-action-url="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'action' => 'duplicate', 'form_id' => $form_id ] ), 'wpforms_duplicate_form_nonce' ) ); ?>"
			>
				<span class="wpforms-context-menu-list-item-icon">
					<i class="fa fa-copy"></i>
				</span>

				<span class="wpforms-context-menu-list-item-text">
					<?php esc_html_e( 'Duplicate Template', 'wpforms-lite' ); ?>
				</span>
			</li>

		<?php else : ?>

			<li class="wpforms-context-menu-list-item"
				data-action="duplicate-form"
				data-action-url="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'action' => 'duplicate', 'form_id' => $form_id ] ), 'wpforms_duplicate_form_nonce' ) ); ?>"
			>
				<span class="wpforms-context-menu-list-item-icon">
					<i class='fa fa-copy'></i>
				</span>

				<span class="wpforms-context-menu-list-item-text">
					<?php esc_html_e( 'Duplicate Form', 'wpforms-lite' ); ?>
				</span>
			</li>

			<li class="wpforms-context-menu-list-item"
				data-action="save-as-template"
				data-action-url="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'action' => 'save_as_template', 'form_id' => $form_id ] ), 'wpforms_save_as_template_form_nonce' ) ); ?>"
			>
				<span class="wpforms-context-menu-list-item-icon">
					<i class="fa fa-file-text-o"></i>
				</span>

				<span class="wpforms-context-menu-list-item-text">
					<?php esc_html_e( 'Save as Template', 'wpforms-lite' ); ?>
				</span>
			</li>

		<?php endif; ?>

		<li class='wpforms-context-menu-list-divider'></li>

		<li class="wpforms-context-menu-list-item education-modal"
			data-action="upgrade"
			data-license="pro"
			data-name="Entries"
			data-utm-content="Upgrade to Pro - Entries Context Menu Item"
		>
			<span class="wpforms-context-menu-list-item-icon">
				<i class="fa fa-envelope-o"></i>
			</span>

			<span class="wpforms-context-menu-list-item-text">
				<?php esc_html_e( 'View Entries', 'wpforms-lite' ); ?>
			</span>

			<?php Helpers::print_badge( 'Pro', 'sm', 'inline', 'stone' ); ?>
		</li>

		<li class="<?php echo esc_attr( $has_payments ? 'wpforms-context-menu-list-item' : 'wpforms-context-menu-list-item wpforms-context-menu-list-item-inactive' ); ?>"
			data-action="view-payments"
			data-action-url="<?php echo $has_payments ? esc_url( admin_url( 'admin.php?page=wpforms-payments&form_id=' . $form_id ) ) : ''; ?>"
		>
			<span class="wpforms-context-menu-list-item-icon">
				<i class="fa fa-money"></i>
			</span>

			<span class="wpforms-context-menu-list-item-text">
				<?php esc_html_e( 'View Payments', 'wpforms-lite' ); ?>
			</span>
		</li>

		<li class="wpforms-context-menu-list-divider"></li>

		<li class="wpforms-context-menu-list-item"
				data-action="whats-new"
			>
			<span class="wpforms-context-menu-list-item-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="17" height="20" viewBox="0 0 17 20" fill="none"><path d="M13.6014 1.63137L14.7985 6.09878C15.4146 6.22486 16.0232 6.80589 16.2497 7.65107C16.4762 8.49626 16.2477 9.33393 15.7771 9.75119L16.9661 14.1884C17.0712 14.5808 16.9268 15.0077 16.605 15.2557C16.2833 15.5037 15.8364 15.5264 15.4919 15.3275L13.8079 14.3552C11.9708 13.2946 9.79038 13.0053 7.73779 13.5553L7.4963 13.62L8.53158 17.4837C8.67717 18.027 8.33762 18.571 7.82447 18.7085L5.89262 19.2261C5.34929 19.3717 4.81346 19.0623 4.66788 18.519L3.6326 14.6553C2.54594 14.9465 1.47428 14.3277 1.18311 13.2411L0.406655 10.3433C0.123572 9.28681 0.734202 8.18497 1.82087 7.8938L5.92605 6.79382C7.97865 6.24383 9.7223 4.9031 10.783 3.06598L11.7633 1.41214C11.9622 1.06769 12.3605 0.863896 12.7632 0.917765C13.1659 0.971634 13.5044 1.26915 13.6014 1.63137ZM12.2924 4.47327C10.9482 6.58047 8.85851 8.07862 6.44369 8.72567L6.20221 8.79037L6.97867 11.6882L7.22015 11.6234C9.63496 10.9764 12.2019 11.2592 14.4195 12.412L12.2924 4.47327Z" fill="#646970"/></svg>
			</span>

			<span class="wpforms-context-menu-list-item-text">
				<?php esc_html_e( 'What\'s New', 'wpforms-lite' ); ?>
			</span>
		</li>

		<li class="wpforms-context-menu-list-item"
			data-action="keyboard-shortcuts"
		>
			<span class="wpforms-context-menu-list-item-icon">
				<i class="fa fa-keyboard-o"></i>
			</span>

			<span class="wpforms-context-menu-list-item-text">
				<?php esc_html_e( 'Keyboard Shortcuts', 'wpforms-lite' ); ?>
			</span>
		</li>
	</ul>
</div>
