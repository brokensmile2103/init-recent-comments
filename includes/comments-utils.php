<?php
/**
 * Truy vấn comments/reviews (có cache tuỳ chọn) và các helper dùng chung.
 *
 * @package InitRecentComments
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hậu tố "phiên bản dữ liệu" cho cache key của comment.
 *
 * Gắn last_changed của comment và post vào cache key: khi có comment mới,
 * comment được duyệt/xoá, hay bài viết đổi tiêu đề/trạng thái, key cũ tự
 * động coi như miss — bật TTL không còn khiến danh sách bị "đứng" tới khi
 * hết hạn.
 *
 * @return string
 */
function init_plugin_suite_recent_comments_comments_cache_salt() {
	return wp_cache_get_last_changed( 'comment' ) . ':' . wp_cache_get_last_changed( 'posts' );
}

/**
 * Hậu tố "phiên bản dữ liệu" cho cache key của review.
 *
 * Init Review System (2.0.1+) đổi version cache toàn site (post_id = 0) mỗi
 * khi review được thêm/duyệt/từ chối/xoá — dùng lại đúng version đó để cache
 * của plugin này cũng tự invalidate theo. Kèm last_changed của post vì tiêu
 * đề/trạng thái bài viết cũng được dùng khi lọc & hiển thị.
 *
 * @return string
 */
function init_plugin_suite_recent_comments_reviews_cache_salt() {
	$version = function_exists( 'init_plugin_suite_review_system_get_reviews_cache_version' )
		? init_plugin_suite_review_system_get_reviews_cache_version( 0 )
		: '';

	return $version . ':' . wp_cache_get_last_changed( 'posts' );
}

/**
 * Nạp sẵn (batch) cache post, user và comment cha cho một danh sách comment.
 *
 * Template comment-item.php gọi get_the_title(), get_comment_link(),
 * get_avatar_url() và get_comment() (comment cha) cho từng comment — nếu
 * không nạp sẵn, mỗi comment có thể tốn thêm vài query riêng lẻ (N+1).
 * Gọi được nhiều lần: dữ liệu đã có trong cache sẽ được bỏ qua.
 *
 * @param WP_Comment[] $comments Danh sách comment.
 * @return void
 */
function init_plugin_suite_recent_comments_prime_comments( $comments ) {
	if ( empty( $comments ) || ! is_array( $comments ) ) {
		return;
	}

	$post_ids   = array();
	$user_ids   = array();
	$parent_ids = array();

	foreach ( $comments as $comment ) {
		if ( ! $comment instanceof WP_Comment ) {
			continue;
		}

		$post_ids[] = (int) $comment->comment_post_ID;

		if ( (int) $comment->user_id > 0 ) {
			$user_ids[] = (int) $comment->user_id;
		}

		if ( (int) $comment->comment_parent > 0 ) {
			$parent_ids[] = (int) $comment->comment_parent;
		}
	}

	$post_ids   = array_unique( array_filter( $post_ids ) );
	$user_ids   = array_unique( $user_ids );
	$parent_ids = array_unique( $parent_ids );

	if ( $post_ids ) {
		_prime_post_caches( $post_ids, false, false );
	}

	if ( $user_ids ) {
		cache_users( $user_ids );
	}

	if ( $parent_ids ) {
		_prime_comment_caches( $parent_ids, false );
	}
}

/**
 * Lọc danh sách review chỉ giữ review thuộc bài viết công khai.
 *
 * Hàm truy vấn review toàn site / theo user của Init Review System chỉ loại
 * review mồ côi (bài đã bị xoá) chứ không xét trạng thái bài viết — nên review
 * của bài nháp, chờ duyệt hay riêng tư có thể bị hiển thị công khai (lộ tiêu đề,
 * link, nội dung review). Hàm này loại các review đó, đồng thời nạp sẵn (batch)
 * cache post & user cho template review-item.php.
 *
 * Kết quả không phụ thuộc người đang xem (không dùng current_user_can), nên an
 * toàn khi được cache dùng chung cho mọi người.
 *
 * @param array $reviews Danh sách review (ARRAY_A).
 * @return array
 */
function init_plugin_suite_recent_comments_filter_reviews( $reviews ) {
	if ( empty( $reviews ) || ! is_array( $reviews ) ) {
		return array();
	}

	$post_ids = array();
	$user_ids = array();

	foreach ( $reviews as $review ) {
		if ( ! is_array( $review ) ) {
			continue;
		}
		if ( ! empty( $review['post_id'] ) ) {
			$post_ids[] = absint( $review['post_id'] );
		}
		if ( ! empty( $review['user_id'] ) ) {
			$user_ids[] = absint( $review['user_id'] );
		}
	}

	$post_ids = array_unique( array_filter( $post_ids ) );
	$user_ids = array_unique( array_filter( $user_ids ) );

	if ( $post_ids ) {
		_prime_post_caches( $post_ids, false, false );
	}

	if ( $user_ids ) {
		cache_users( $user_ids );
	}

	$visible = array();

	foreach ( $reviews as $review ) {
		if ( ! is_array( $review ) || empty( $review['post_id'] ) ) {
			continue;
		}

		$post = get_post( absint( $review['post_id'] ) );
		if ( ! $post || ! is_post_publicly_viewable( $post ) ) {
			continue;
		}

		$visible[] = $review;
	}

	/**
	 * Filter: danh sách review sau khi lọc theo quyền xem công khai.
	 *
	 * @since 2.0.1
	 *
	 * @param array $visible Review được phép hiển thị.
	 * @param array $reviews Danh sách review gốc trước khi lọc.
	 */
	return (array) apply_filters( 'init_plugin_suite_recent_comments_visible_reviews', $visible, $reviews );
}

/**
 * Get recent comments with optional cache (TTL filter).
 *
 * @param array $args Optional query args.
 * @return array Array of WP_Comment objects.
 */
function init_plugin_suite_recent_comments_get_comments( $args = array() ) {
	$defaults = array(
		'number'                    => 5,
		'paged'                     => 1, // Hỗ trợ phân trang.
		'status'                    => 'approve',
		'type'                      => 'comment',
		// Chỉ lấy comment của bài đã xuất bản — giống widget Recent Comments
		// của core, tránh lộ comment của bài nháp/riêng tư. Có thể override
		// qua filter 'init_plugin_suite_recent_comments_query_args'.
		'post_status'               => 'publish',
		// Nạp sẵn post của các comment trong cùng 1 query (dùng cho tiêu đề/link).
		'update_comment_post_cache' => true,
	);

	$args = wp_parse_args( $args, $defaults );

	// Cho phép override args bằng filter.
	$args = apply_filters( 'init_plugin_suite_recent_comments_query_args', $args );

	// Tính offset thủ công vì get_comments không hỗ trợ 'paged'.
	$args['offset'] = ( max( 1, absint( $args['paged'] ) ) - 1 ) * absint( $args['number'] );
	unset( $args['paged'] );

	// Cache group.
	$cache_group = 'init_recent_comments';
	$cache_key   = '';

	// TTL mặc định = 0 (tắt cache), có thể bật qua filter.
	$ttl = (int) apply_filters( 'init_plugin_suite_recent_comments_ttl', 0 );

	// Chỉ dùng cache nếu TTL > 0.
	if ( $ttl > 0 ) {
		$cache_key = 'irc_' . md5( maybe_serialize( $args ) . init_plugin_suite_recent_comments_comments_cache_salt() );
		$cached    = wp_cache_get( $cache_key, $cache_group );
		if ( false !== $cached ) {
			return $cached;
		}
	}

	// Gọi get_comments gốc.
	$comments = get_comments( $args );

	// Cache nếu TTL hợp lệ.
	if ( $ttl > 0 ) {
		wp_cache_set( $cache_key, $comments, $cache_group, $ttl );
	}

	return $comments;
}

/**
 * Helper: Lấy recent comments của 1 user (có cache, TTL qua filter).
 *
 * @param array $args {
 *     Tham số truy vấn.
 *
 *     @type int    $number      Số comment.
 *     @type int    $paged       Trang hiện tại.
 *     @type int    $user_id     ID user (ưu tiên nếu >0).
 *     @type string $user_email  Email user (fallback nếu không có user_id).
 * }
 * @return array Array of WP_Comment objects.
 */
function init_plugin_suite_recent_comments_get_user_comments( $args = array() ) {
	$defaults = array(
		'number'     => 5,
		'paged'      => 1,
		'status'     => 'approve',
		'type'       => 'comment',
		'user_id'    => 0,
		'user_email' => '',
	);

	$args = wp_parse_args( $args, $defaults );

	// Chuẩn hoá & bổ sung constraint user vào query gốc của IRC.
	$query_args = array(
		'number'                    => absint( $args['number'] ),
		'status'                    => $args['status'],
		'type'                      => $args['type'],
		// Chỉ comment của bài đã xuất bản (xem init_plugin_suite_recent_comments_get_comments()).
		'post_status'               => 'publish',
		'update_comment_post_cache' => true,
	);

	// Áp offset (get_comments không hỗ trợ 'paged').
	$query_args['offset'] = ( max( 1, absint( $args['paged'] ) ) - 1 ) * max( 1, absint( $args['number'] ) );

	if ( absint( $args['user_id'] ) > 0 ) {
		$query_args['user_id'] = absint( $args['user_id'] ); // Comment của user đã đăng ký.
	} elseif ( '' !== $args['user_email'] ) {
		$query_args['author_email'] = sanitize_email( $args['user_email'] ); // Comment guest theo email.
	}

	/**
	 * Allow devs tùy biến query cho user recent comments.
	 *
	 * Ví dụ: add_filter( 'init_plugin_suite_user_recent_comments_query_args', function( $qa ){ ... } );
	 */
	$query_args = apply_filters( 'init_plugin_suite_user_recent_comments_query_args', $query_args, $args );

	// Cache (key gồm cả định danh user).
	$cache_group = 'init_user_recent_comments';
	$cache_key   = '';

	// TTL mặc định = 0 (tắt), bật qua filter nếu muốn.
	$ttl = (int) apply_filters( 'init_plugin_suite_user_recent_comments_ttl', 0, $query_args, $args );

	if ( $ttl > 0 ) {
		$cache_key = 'iurc_' . md5( maybe_serialize( $query_args ) . init_plugin_suite_recent_comments_comments_cache_salt() );
		$cached    = wp_cache_get( $cache_key, $cache_group );
		if ( false !== $cached ) {
			return $cached;
		}
	}

	$comments = get_comments( $query_args );

	if ( $ttl > 0 ) {
		wp_cache_set( $cache_key, $comments, $cache_group, $ttl );
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
	$post_id = absint( $post_id );
	$paged   = absint( $paged );
	$number  = absint( $number );

	$cache_group = 'init_recent_reviews';
	$cache_key   = '';

	// TTL mặc định = 0 (tắt cache), có thể bật qua filter.
	$ttl = (int) apply_filters( 'init_plugin_suite_recent_reviews_ttl', 0 );

	if ( $ttl > 0 ) {
		$cache_key = sprintf( 'reviews_%d_%d_%d_', $post_id, $paged, $number ) . md5( init_plugin_suite_recent_comments_reviews_cache_salt() );
		$cached    = wp_cache_get( $cache_key, $cache_group );
		if ( false !== $cached ) {
			return $cached;
		}
	}

	if ( defined( 'INIT_PLUGIN_SUITE_RS_VERSION' ) && function_exists( 'init_plugin_suite_review_system_get_reviews_by_post_id' ) ) {
		$reviews = init_plugin_suite_review_system_get_reviews_by_post_id( $post_id, $paged, $number );
		$reviews = init_plugin_suite_recent_comments_filter_reviews( $reviews );
	} else {
		$reviews = array();
	}

	if ( $ttl > 0 ) {
		wp_cache_set( $cache_key, $reviews, $cache_group, $ttl );
	}

	return $reviews;
}

/**
 * Helper: lấy recent reviews theo user (ARRAY_A), có cache.
 *
 * @param array $args {
 *     Tham số truy vấn.
 *
 *     @type int    $user_id   ID user.
 *     @type int    $paged     Trang hiện tại.
 *     @type int    $per_page  0 = lấy toàn bộ.
 *     @type string $status    'approved' | 'pending' | ...
 * }
 * @return array
 */
function init_plugin_suite_recent_comments_get_user_reviews( $args = array() ) {
	$defaults = array(
		'user_id'  => 0,
		'paged'    => 1,
		'per_page' => 5,
		'status'   => 'approved',
	);
	$args     = wp_parse_args( $args, $defaults );

	// Cho phép dev override args nếu cần.
	$args = apply_filters( 'init_plugin_suite_user_recent_reviews_args', $args );

	// Yêu cầu plugin Review System có sẵn hàm lấy theo user_id.
	if ( ! function_exists( 'init_plugin_suite_review_system_get_reviews_by_user_id' ) ) {
		return array();
	}

	$cache_group = 'init_user_recent_reviews';
	$cache_key   = '';

	// TTL mặc định 0 (off) – có thể bật qua filter.
	$ttl = (int) apply_filters( 'init_plugin_suite_user_recent_reviews_ttl', 0, $args );

	if ( $ttl > 0 ) {
		$cache_key = 'iurr_' . md5( maybe_serialize( $args ) . init_plugin_suite_recent_comments_reviews_cache_salt() );
		$cached    = wp_cache_get( $cache_key, $cache_group );
		if ( false !== $cached ) {
			return $cached;
		}
	}

	$reviews = init_plugin_suite_review_system_get_reviews_by_user_id(
		absint( $args['user_id'] ),
		max( 1, absint( $args['paged'] ) ),
		absint( $args['per_page'] ),
		sanitize_key( $args['status'] )
	);
	$reviews = init_plugin_suite_recent_comments_filter_reviews( $reviews );

	if ( $ttl > 0 ) {
		wp_cache_set( $cache_key, $reviews, $cache_group, $ttl );
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

	// Chuẩn hoá post_types -> array, sanitize & sort để key cache ổn định.
	$post_types = array_values( array_unique( array_filter( array_map( 'sanitize_key', (array) $post_types ) ) ) );
	if ( empty( $post_types ) ) {
		return 0;
	}
	sort( $post_types );

	// Cache key để tránh query trùng lặp.
	$cache_group = 'init_comment_totals';
	$cache_key   = 'init_total_comments_' . md5( wp_json_encode( $post_types ) );

	$cached_count = wp_cache_get( $cache_key, $cache_group );
	if ( false !== $cached_count ) {
		return (int) $cached_count;
	}

	// Tạo placeholders cho prepared statement.
	$placeholders = implode( ', ', array_fill( 0, count( $post_types ), '%s' ) );

	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
	$count = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->comments} AS c
			JOIN {$wpdb->posts} AS p ON p.ID = c.comment_post_ID
			WHERE c.comment_approved = '1'
			AND p.post_status = 'publish'
			AND p.post_type IN ( {$placeholders} )",
			...$post_types
		)
	);
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare

	// TTL mặc định 5 phút, có thể đổi qua filter.
	$ttl = (int) apply_filters( 'init_plugin_suite_total_comments_ttl', 5 * MINUTE_IN_SECONDS, $post_types );

	if ( $ttl > 0 ) {
		wp_cache_set( $cache_key, $count, $cache_group, $ttl );
	}

	return $count;
}

/**
 * Get total pages of recent comments.
 *
 * @param int          $per        Comments per page.
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
function init_plugin_suite_recent_comments_get_total_by_posts( $post_ids = array() ) {
	global $wpdb;

	// Validate input.
	if ( empty( $post_ids ) || ! is_array( $post_ids ) ) {
		return 0;
	}

	// Sanitize and filter valid IDs.
	$post_ids = array_values( array_unique( array_filter( array_map( 'absint', $post_ids ) ) ) );
	if ( empty( $post_ids ) ) {
		return 0;
	}

	// Prepare cache key.
	sort( $post_ids );
	$cache_group = 'init_comment_totals';
	$cache_key   = 'init_total_by_posts_' . md5( wp_json_encode( $post_ids ) );

	// Try cache first.
	$cached_total = wp_cache_get( $cache_key, $cache_group );
	if ( false !== $cached_total ) {
		return (int) $cached_total;
	}

	// Build placeholders.
	$placeholders = implode( ', ', array_fill( 0, count( $post_ids ), '%d' ) );

	// Safe: $post_ids đã được ép kiểu qua absint ở trên và truyền qua prepare().
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
	$count = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*)
			FROM {$wpdb->comments}
			WHERE comment_post_ID IN ({$placeholders})
			  AND comment_approved = '1'",
			...$post_ids
		)
	);
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare

	// Apply TTL filter (same style as others).
	$ttl = (int) apply_filters( 'init_plugin_suite_total_by_posts_ttl', 5 * MINUTE_IN_SECONDS, $post_ids );
	if ( $ttl > 0 ) {
		wp_cache_set( $cache_key, $count, $cache_group, $ttl );
	}

	return $count;
}
