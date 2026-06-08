<?php
/**
 * What's New modal footer.
 *
 * @since 1.8.7
 *
 * @var string $title Footer title.
 * @var string $description Footer content.
 * @var array $upgrade Upgrade link.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<footer>
	<div class="wpforms-splash-footer-content">
		<h2><?php echo esc_html( $title ); ?></h2>
		<p><?php echo esc_html( $description ); ?></p>
	</div>
	<a href="<?php echo esc_url( $upgrade['url'] ); ?>" class="wpforms-btn wpforms-btn-green" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $upgrade['text'] ); ?></a>
</footer>
