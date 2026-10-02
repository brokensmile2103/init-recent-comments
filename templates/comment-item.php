<?php
/**
 * Template: một comment (có thể override trong theme tại
 * {theme}/init-recent-comments/comment-item.php).
 *
 * Vars:
 * - $comment (WP_Comment) Comment cần hiển thị.
 *
 * Template luôn được include bên trong scope của một hàm, nên các biến dưới
 * đây là biến cục bộ, không phải biến global.
 *
 * @package InitRecentComments
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound, WordPress.WP.GlobalVariablesOverride.Prohibited

defined( 'ABSPATH' ) || exit;

if ( empty( $comment ) || ! $comment instanceof WP_Comment ) {
	return;
}

$comment_link = get_comment_link( $comment );
$author       = get_comment_author( $comment );
$post_title   = get_the_title( $comment->comment_post_ID );
$avatar       = get_avatar_url( $comment, array( 'size' => 42 ) );

// Thời gian theo GMT so với time(); comment cũ (import) có thể thiếu
// comment_date_gmt thì quy đổi từ comment_date (giờ site).
$comment_ts = ( '0000-00-00 00:00:00' !== $comment->comment_date_gmt )
	? (int) get_comment_time( 'U', true, false, $comment )
	: (int) get_gmt_from_date( $comment->comment_date, 'U' );
$time_diff  = human_time_diff( $comment_ts, time() );

$parent_author = '';
if ( $comment->comment_parent ) {
	$parent_comment = get_comment( $comment->comment_parent );
	if ( $parent_comment ) {
		$parent_author = get_comment_author( $parent_comment );
	}
}
?>

<div class="init-comment-item">
	<img
		src="<?php echo esc_url( $avatar ); ?>"
		alt=""
		class="init-comment-avatar"
		loading="lazy"
	/>
	<div class="init-comment-body">
		<div class="init-comment-meta">
			<div class="init-comment-meta-line1">
				<span class="init-comment-author">
					<?php echo esc_html( $author ); ?>
				</span>
				<span class="init-comment-time">
					<?php echo esc_html( $time_diff ) . ' ' . esc_html__( 'ago', 'init-recent-comments' ); ?>
				</span>
			</div>
			<div class="init-comment-meta-line2">
				<span class="init-comment-in-label">
					<?php esc_html_e( 'In', 'init-recent-comments' ); ?>
				</span>
				<a href="<?php echo esc_url( $comment_link ); ?>" class="init-comment-post-title">
					<?php echo esc_html( $post_title ); ?>
				</a>
			</div>
		</div>
		<div class="init-comment-content">
			<?php echo wp_kses_post( wp_trim_words( get_comment_text( $comment ), 20, '...' ) ); ?>
		</div>

		<?php if ( $parent_author ) : ?>
			<div class="init-comment-replying">
				<span class="init-comment-replying-label">
					<?php esc_html_e( 'Replying to', 'init-recent-comments' ); ?>
				</span>
				<span class="init-comment-replying-author">
					<?php echo esc_html( $parent_author ); ?>
				</span>
			</div>
		<?php endif; ?>
	</div>
</div>
