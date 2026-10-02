<?php
/**
 * Template: khung danh sách comment (có thể override trong theme tại
 * {theme}/init-recent-comments/wrapper.php).
 *
 * Vars:
 * - $comments        (WP_Comment[]) Danh sách comment.
 * - $container_class (string)       CSS class của container.
 * - $container_style (string)       Inline style của container (maxheight), có thể rỗng.
 *
 * Template luôn được include bên trong scope của một hàm, nên các biến dưới
 * đây là biến cục bộ, không phải biến global.
 *
 * @package InitRecentComments
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound, WordPress.WP.GlobalVariablesOverride.Prohibited

defined( 'ABSPATH' ) || exit;

$item_template = init_plugin_suite_recent_comments_locate_template( 'comment-item.php' );
?>
<div class="<?php echo esc_attr( $container_class ); ?>"<?php echo ! empty( $container_style ) ? ' style="' . esc_attr( $container_style ) . '"' : ''; ?>>
	<?php foreach ( $comments as $comment ) : ?>
		<?php
		if ( $item_template ) {
			include $item_template;
		}
		?>
	<?php endforeach; ?>
</div>
