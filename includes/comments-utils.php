<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get recent comments with optional cache (TTL filter).
 *
 * @param array $args Optional query args.
 * @return array Array of WP_Comment objects.
 */
function init_plugin_suite_recent_comments_get_comments( $args = [] ) {
	$defaults = [
		'number'      => 5,
		'paged'       => 1, // hỗ trợ phân trang
		'status'      => 'approve',
		'type'        => 'comment',
	];

	$args = wp_parse_args( $args, $defaults );

	// Cho phép override args bằng filter
	$args = apply_filters( 'init_plugin_suite_recent_comments_query_args', $args );

	// Tính offset thủ công vì get_comments không hỗ trợ 'paged'
	$args['offset'] = ( max( 1, absint( $args['paged'] ) ) - 1 ) * absint( $args['number'] );
	unset( $args['paged'] );

	// Cache group & key
	$cache_group = 'init_recent_comments';
	$cache_key   = 'irc_' . md5( maybe_serialize( $args ) );

	// TTL mặc định = 0 (tắt cache), có thể bật qua filter
	$ttl = apply_filters( 'init_plugin_suite_recent_comments_ttl', 0 );

	// Chỉ dùng cache nếu TTL > 0
	if ( $ttl > 0 ) {
		$cached = wp_cache_get( $cache_key, $cache_group );
		if ( false !== $cached ) {
			return $cached;
		}
	}

	// Gọi get_comments gốc
	$comments = get_comments( $args );

	// Cache nếu TTL hợp lệ
	if ( $ttl > 0 ) {
		wp_cache_set( $cache_key, $comments, $cache_group, absint( $ttl ) );
	}

	return $comments;
}

/**
 * Get recent reviews with optional cache (TTL filter).
 *
 * @param int $post_id ID bài viết (0 = global reviews).
 * @param int $paged   Trang hiện tại.
 * @param int $number  Số lượng mỗi trang.
 * @return array Mảng các review.
 */
function init_plugin_suite_recent_comments_get_reviews( $post_id = 0, $paged = 1, $number = 5 ) {
	$cache_group = 'init_recent_reviews';
	$cache_key   = sprintf( 'reviews_%d_%d_%d', $post_id, $paged, $number );

	// TTL mặc định = 0 (tắt cache), có thể bật qua filter
	$ttl = apply_filters( 'init_plugin_suite_recent_reviews_ttl', 0 );

	if ( $ttl > 0 ) {
		$cached = wp_cache_get( $cache_key, $cache_group );
		if ( false !== $cached ) {
			return $cached;
		}
	}

	if ( defined( 'INIT_PLUGIN_SUITE_RS_VERSION' ) && function_exists( 'init_plugin_suite_review_system_get_reviews_by_post_id' ) ) {
		$reviews = init_plugin_suite_review_system_get_reviews_by_post_id( absint( $post_id ), absint( $paged ), absint( $number ) );
	} else {
		$reviews = [];
	}

	if ( $ttl > 0 ) {
		wp_cache_set( $cache_key, $reviews, $cache_group, absint( $ttl ) );
	}

	return $reviews;
}

/**
 * Get total approved comments (fast & optimized) for specified post types.
 *
 * @param array|string $post_types One or more post types. Default 'post'.
 * @return int
 */
function init_plugin_suite_recent_comments_get_total_comments( $post_types = 'post' ) {
    global $wpdb;

    // Chuẩn hoá post_types -> array, sanitize & sort để key cache ổn định
    $post_types = (array) $post_types;
    $post_types = array_map( 'sanitize_key', $post_types );
    sort( $post_types );

    // Cache key để tránh query trùng lặp
    $cache_group = 'init_comment_totals';
    $cache_key   = 'init_total_comments_' . md5( wp_json_encode( $post_types ) );

    $cached_count = wp_cache_get( $cache_key, $cache_group );
    if ( false !== $cached_count ) {
        return (int) $cached_count;
    }

    // Tạo placeholders cho prepared statement
    $placeholders        = array_fill( 0, count( $post_types ), '%s' );
    $placeholders_string = implode( ', ', $placeholders );

    // Query
    $query_template = "SELECT COUNT(*) FROM {$wpdb->comments} AS c
        JOIN {$wpdb->posts} AS p ON p.ID = c.comment_post_ID
        WHERE c.comment_approved = '1'
        AND p.post_status = 'publish'
        AND p.post_type IN ( {$placeholders_string} )";

    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Template chỉ chứa placeholders
    $query = $wpdb->prepare( $query_template, ...$post_types );

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.NotPrepared
    $count = (int) $wpdb->get_var( $query );

    // TTL mặc định 5 phút, có thể đổi qua filter
    $default_ttl = 5 * MINUTE_IN_SECONDS;
    $ttl = (int) apply_filters( 'init_plugin_suite_total_comments_ttl', $default_ttl, $post_types );

    if ( $ttl > 0 ) {
        wp_cache_set( $cache_key, $count, $cache_group, $ttl );
    }

    return $count;
}

/**
 * Get total pages of recent comments.
 *
 * @param int $per Comments per page.
 * @param array|string $post_types Post types to count.
 * @return int
 */
function init_plugin_suite_recent_comments_get_total_pages( $per = 10, $post_types = 'post' ) {
	$total = init_plugin_suite_recent_comments_get_total_comments( $post_types );
	return (int) ceil( $total / max( 1, absint( $per ) ) );
}

/**
 * Get total approved comments for multiple posts (optimized).
 *
 * @param array $post_ids Array of post IDs.
 * @return int Total approved comment count across the given posts.
 */
function init_plugin_suite_recent_comments_get_total_by_posts( $post_ids = [] ) {
	global $wpdb;
	// Validate input
	if ( empty( $post_ids ) || ! is_array( $post_ids ) ) {
		return 0;
	}
	// Sanitize and filter valid IDs
	$post_ids = array_filter( array_map( 'absint', $post_ids ) );
	if ( empty( $post_ids ) ) {
		return 0;
	}
	// Prepare cache key
	sort( $post_ids );
	$cache_group = 'init_comment_totals';
	$cache_key   = 'init_total_by_posts_' . md5( wp_json_encode( $post_ids ) );
	// Try cache first
	$cached_total = wp_cache_get( $cache_key, $cache_group );
	if ( false !== $cached_total ) {
		return (int) $cached_total;
	}
	// Build placeholders
	$placeholders = implode( ', ', array_fill( 0, count( $post_ids ), '%d' ) );
	
	// Execute with proper prepare - safe because $post_ids are sanitized via absint above
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
	$count = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*)
			FROM {$wpdb->comments}
			WHERE comment_post_ID IN ({$placeholders})
			  AND comment_approved = '1'",
			...$post_ids
		)
	);
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
	
	// Apply TTL filter (same style as others)
	$default_ttl = 5 * MINUTE_IN_SECONDS;
	$ttl = (int) apply_filters( 'init_plugin_suite_total_by_posts_ttl', $default_ttl, $post_ids );
	if ( $ttl > 0 ) {
		wp_cache_set( $cache_key, $count, $cache_group, $ttl );
	}
	return $count;
}
