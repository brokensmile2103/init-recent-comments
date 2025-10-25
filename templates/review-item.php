<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $review ) || ! is_array( $review ) ) {
	return;
}

$author      = $review['author_name'] ?? __( 'Anonymous', 'init-recent-comments' );
$post_id     = intval( $review['post_id'] );
$post_title  = get_the_title( $post_id );
$avatar_url  = get_avatar_url( 0, [ 'size' => 42, 'default' => 'mm' ] );
$time_diff   = human_time_diff( strtotime( $review['created_at'] ), current_time( 'timestamp' ) );
$permalink   = get_permalink( $post_id );
$comment_link = $permalink ? esc_url( $permalink ) . '#init-review' : '#';
$content     = ! empty( $review['review_content'] ) ? wp_trim_words( $review['review_content'], 20, '...' ) : '';
$criteria    = ! empty( $review['criteria_scores'] ) && is_array( $review['criteria_scores'] ) ? $review['criteria_scores'] : [];
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
