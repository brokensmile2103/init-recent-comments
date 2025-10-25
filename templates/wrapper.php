<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="<?php echo esc_attr( $container_class ); ?>">
	<?php foreach ( $comments as $comment ) : ?>
		<?php
		$template = locate_template( 'init-recent-comments/comment-item.php' );
		if ( ! $template ) {
			$template = INIT_PLUGIN_SUITE_IRC_TEMPLATES_PATH . 'comment-item.php';
		}
		include $template;
		?>
	<?php endforeach; ?>
</div>
