<?php
/**
 * Template: một review (có thể override trong theme tại
 * {theme}/init-recent-comments/review-item.php).
 *
 * Vars:
 * - $review (array) Dữ liệu review từ Init Review System (ARRAY_A).
 *
 * Template luôn được include bên trong scope của một hàm, nên các biến dưới
 * đây là biến cục bộ, không phải biến global.
 *
 * @package InitRecentComments
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound, WordPress.WP.GlobalVariablesOverride.Prohibited

defined( 'ABSPATH' ) || exit;

if ( empty( $review ) || ! is_array( $review ) ) {
	return;
}

// Resolve author from user_id.
$user_id = isset( $review['user_id'] ) ? absint( $review['user_id'] ) : 0;
$author  = __( 'Anonymous', 'init-recent-comments' );

if ( $user_id > 0 ) {
	$user_obj = get_userdata( $user_id );
	if ( $user_obj ) {
		if ( '' !== $user_obj->display_name ) {
			$author = $user_obj->display_name;
		} elseif ( '' !== $user_obj->user_nicename ) {
			$author = $user_obj->user_nicename;
		} else {
			$author = $user_obj->user_login;
		}
	}
}

// Post info.
$post_id      = isset( $review['post_id'] ) ? absint( $review['post_id'] ) : 0;
$post_title   = $post_id ? get_the_title( $post_id ) : '';
$permalink    = $post_id ? get_permalink( $post_id ) : '';
$comment_link = $permalink ? $permalink . '#init-review' : '#';

// Avatar (ưu tiên theo user_id, fallback default).
$avatar_url = get_avatar_url(
	$user_id,
	array(
		'size'    => 42,
		'default' => 'mm',
	)
);

// Time diff (created_at lưu theo giờ site — đổi sang GMT để so với time()).
$created_at_ts = ! empty( $review['created_at'] ) ? (int) get_gmt_from_date( $review['created_at'], 'U' ) : 0;
$time_diff     = $created_at_ts > 0 ? human_time_diff( $created_at_ts, time() ) : '';

// Content & criteria.
$content  = ! empty( $review['review_content'] ) ? wp_trim_words( $review['review_content'], 20, '...' ) : '';
$criteria = ! empty( $review['criteria_scores'] ) && is_array( $review['criteria_scores'] ) ? $review['criteria_scores'] : array();
?>

<div class="init-comment-item">
	<img
		src="<?php echo esc_url( $avatar_url ); ?>"
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
				<?php if ( $time_diff ) : ?>
					<span class="init-comment-time">
						<?php echo esc_html( $time_diff ) . ' ' . esc_html__( 'ago', 'init-recent-comments' ); ?>
					</span>
				<?php endif; ?>
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
			<?php if ( $content ) : ?>
				<?php echo wp_kses_post( $content ); ?>
			<?php endif; ?>

			<?php if ( ! empty( $criteria ) ) : ?>
				<ul class="init-review-criteria-list">
					<?php foreach ( $criteria as $label => $score ) : ?>
						<li class="init-review-criteria-item">
							<strong><?php echo esc_html( $label ); ?>:</strong>
							<?php echo esc_html( is_scalar( $score ) ? $score : '' ); ?> / 5
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
</div>
