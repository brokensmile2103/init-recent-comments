<?php
/**
 * Template: khung danh sách review (có thể override trong theme tại
 * {theme}/init-recent-comments/review-wrapper.php).
 *
 * Vars:
 * - $irr_reviews         (array)    Danh sách review (ARRAY_A).
 * - $irr_container_class (string)   CSS class của container.
 * - $irr_container_style (string)   Inline style của container (maxheight), có thể rỗng.
 * - $irr_paged           (int|bool) Trang hiện tại, hoặc false nếu không truyền.
 *
 * Template luôn được include bên trong scope của một hàm, nên các biến dưới
 * đây là biến cục bộ, không phải biến global.
 *
 * @package InitRecentComments
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound, WordPress.WP.GlobalVariablesOverride.Prohibited

defined( 'ABSPATH' ) || exit;

$item_template = init_plugin_suite_recent_comments_locate_template( 'review-item.php' );
?>
<div class="<?php echo esc_attr( $irr_container_class ); ?>"<?php echo ! empty( $irr_container_style ) ? ' style="' . esc_attr( $irr_container_style ) . '"' : ''; ?>>
	<?php foreach ( $irr_reviews as $review ) : ?>
		<?php
		if ( $item_template ) {
			include $item_template;
		}
		?>
	<?php endforeach; ?>
</div>
