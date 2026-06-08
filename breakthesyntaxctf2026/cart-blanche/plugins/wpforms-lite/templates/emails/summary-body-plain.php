<?php
/**
 * Email Summary body template (plain text).
 *
 * This template can be overridden by copying it to yourtheme/wpforms/emails/summary-body-plain.php.
 *
 * @since 1.5.4
 * @since 1.8.8 Added `$overview`, and `$notification_block` parameters.
 *
 * @var array $overview           Form entries overview data.
 * @var array $entries            Form entries data to loop through.
 * @var array $notification_block Notification block shown before the Info block.
 * @var array $info_block         Info block shown at the end of the email.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Used to separate strings in the email.
$separator = '   |   ';

echo esc_html__( 'Hi there!', 'wpforms-lite' ) . "\n\n";

echo esc_html__( 'Let’s see how your forms performed in the past week.', 'wpforms-lite' ) . "\n\n";

if ( ! wpforms()->is_pro() ) {
	echo esc_html__( 'Below is the total number of submissions for each form, however actual entries are not stored in WPForms Lite.', 'wpforms-lite' ) . "\n\n";
	echo esc_html__( 'To view future entries inside your WordPress dashboard, and get more detailed reports, consider upgrading to Pro:', 'wpforms-lite' );
	echo '&nbsp;';
	echo 'https://wpforms.com/lite-upgrade/?utm_source=WordPress&utm_medium=Weekly%20Summary%20Email&utm_campaign=liteplugin&utm_content=Upgrade&utm_locale=' . wpforms_sanitize_key( get_locale() );
	echo "\n\n\n";
}

if ( isset( $overview['total'] ) ) {
	echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
	printf( /* translators: %1$d - number of entries. */
		esc_html__( '%1$d Total', 'wpforms-lite' ),
		absint( $overview['total'] )
	);

	if ( isset( $overview['trends'] ) ) {
		echo esc_html( $separator ) . ( (int) $overview['trends'] >= 0 ? '↑' : '↓' ) . esc_html( $overview['trends'] ) . "\n\n";
		echo wp_kses( _n( 'Entry This Week', 'Entries This Week', absint( $overview['total'] ), 'wpforms-lite' ), [] );
	}

	echo "\n\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
}

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo esc_html__( 'Form', 'wpforms-lite' ) . esc_html( $separator ) . esc_html__( 'Entries', 'wpforms-lite' );

echo "\n\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

foreach ( $entries as $row ) {
	echo ( isset( $row['title'] ) ? esc_html( $row['title'] ) : '' ) . esc_html( $separator ) . ( isset( $row['count'] ) ? absint( $row['count'] ) : '' );

	if ( isset( $row['trends'] ) ) {
		echo esc_html( $separator ) . ( (int) $row['trends'] >= 0 ? '↑' : '↓' ) . esc_html( $row['trends'] );
	}

	echo "\n\n";
}

if ( empty( $entries ) ) {
	echo esc_html__( 'It appears you do not have any form entries yet.', 'wpforms-lite' ) . "\n\n";
}

if ( ! empty( $notification_block ) ) {
	echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n\n";

	if ( ! empty( $notification_block['title'] ) ) {
		echo esc_html( $notification_block['title'] ) . "\n\n";
	}

	if ( ! empty( $notification_block['content'] ) ) {
		echo wp_kses_post( $notification_block['content'] ) . "\n\n";
	}

	if ( ! empty( $notification_block['btns']['main']['url'] ) && ! empty( $notification_block['btns']['main']['text'] ) ) {
		echo esc_html( $notification_block['btns']['main']['text'] ) . ': ' . esc_url( $notification_block['btns']['main']['url'] ) . "\n\n";
	}

	if ( ! empty( $notification_block['btns']['alt']['url'] ) && ! empty( $notification_block['btns']['alt']['text'] ) ) {
		echo esc_html( $notification_block['btns']['alt']['text'] ) . ': ' . esc_url( $notification_block['btns']['alt']['url'] ) . "\n\n";
	}
}

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n\n";

if ( ! empty( $info_block['title'] ) ) {
	echo esc_html( $info_block['title'] ) . "\n\n";
}

if ( ! empty( $info_block['content'] ) ) {
	echo wp_kses_post( $info_block['content'] ) . "\n\n";
}

if ( ! empty( $info_block['button'] ) && ! empty( $info_block['url'] ) ) {
	echo esc_html( $info_block['button'] ) . ': ' . esc_url( $info_block['url'] ) . "\n\n";
}
