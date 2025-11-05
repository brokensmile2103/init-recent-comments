<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $review ) || ! is_array( $review ) ) {
	return;
}

// --- NEW: resolve author from user_id ---
$user_id = isset( $review['user_id'] ) ? absint( $review['user_id'] ) : 0;
$author  = __( 'Anonymous', 'init-recent-comments' );

if ( $user_id > 0 ) {
	$user_obj = get_userdata( $user_id );
	if ( $user_obj && ! is_wp_error( $user_obj ) ) {
		$author = $user_obj->display_name ?: ( $user_obj->user_nicename ?: $user_obj->user_login );
	}
}

// Post info
$post_id    = isset( $review['post_id'] ) ? absint( $review['post_id'] ) : 0;
$post_title = $post_id ? get_the_title( $post_id ) : '';
$permalink  = $post_id ? get_permalink( $post_id ) : '';
$comment_link = $permalink ? esc_url( $permalink ) . '#init-review' : '#';

// Avatar (ưu tiên theo user_id, fallback default)
$avatar_url = $user_id > 0
	? get_avatar_url( $user_id, [ 'size' => 42, 'default' => 'mm' ] )
	: get_avatar_url( 0,       [ 'size' => 42, 'default' => 'mm' ] );

// Time diff
$created_at_ts = ! empty( $review['created_at'] ) ? strtotime( $review['created_at'] ) : 0;
$time_diff     = $created_at_ts ? human_time_diff( $created_at_ts, current_time( 'timestamp' ) ) : '';

// Content & criteria
$content  = ! empty( $review['review_content'] ) ? wp_trim_words( $review['review_content'], 20, '...' ) : '';
$criteria = ! empty( $review['criteria_scores'] ) && is_array( $review['criteria_scores'] ) ? $review['criteria_scores'] : [];
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
							<?php echo esc_html( $score ); ?> / 5
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
</div>
