<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="<?php echo esc_attr( $irr_container_class ); ?>">
	<?php foreach ( $irr_reviews as $review ) : ?>
		<?php
		$template = locate_template( 'init-recent-comments/review-item.php' );
		if ( ! $template ) {
			$template = INIT_PLUGIN_SUITE_IRC_TEMPLATES_PATH . 'review-item.php';
		}
		include $template;
		?>
	<?php endforeach; ?>
</div>
