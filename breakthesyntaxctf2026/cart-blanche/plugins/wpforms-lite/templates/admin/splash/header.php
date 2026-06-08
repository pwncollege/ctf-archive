<?php
/**
 * What's New modal header.
 *
 * @since 1.8.7
 *
 * @var string $title Header title.
 * @var string $image Logo URL.
 * @var string $description Header content.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<header>
	<img src="<?php echo esc_url( $image ); ?>" alt="">
	<div class="wpforms-splash-header-content">
		<h2>
			<?php echo esc_html( $title ); ?>
		</h2>
		<p><?php echo esc_html( $description ); ?></p>
	</div>
</header>
