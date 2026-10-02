<?php
/**
 * Shortcodes hiển thị recent comments/reviews.
 *
 * @package InitRecentComments
 */

defined( 'ABSPATH' ) || exit;

/**
 * Shortcode tags (new, prefixed)
 * - Primary (khuyến nghị dùng): init_plugin_suite_recent_comments
 * - Primary (khuyến nghị dùng): init_plugin_suite_recent_reviews
 *
 * Backward-compat fallback (vẫn hỗ trợ):
 * - init_recent_comments
 * - init_recent_reviews
 */
add_shortcode( 'init_plugin_suite_recent_comments', 'init_plugin_suite_recent_comments_render_static__prefixed' );
add_shortcode( 'init_plugin_suite_recent_reviews', 'init_plugin_suite_recent_comments_render_reviews__prefixed' );
add_shortcode( 'init_plugin_suite_user_recent_comments', 'init_plugin_suite_recent_comments_render_user__prefixed' );
add_shortcode( 'init_plugin_suite_user_recent_reviews', 'init_plugin_suite_recent_comments_render_user_reviews__prefixed' );
add_shortcode( 'init_recent_comments', 'init_plugin_suite_recent_comments_render_static' ); // Fallback.
add_shortcode( 'init_recent_reviews', 'init_plugin_suite_recent_comments_render_reviews' ); // Fallback.
add_shortcode( 'init_user_recent_comments', 'init_plugin_suite_recent_comments_render_user' ); // Fallback.
add_shortcode( 'init_user_recent_reviews', 'init_plugin_suite_recent_comments_render_user_reviews' ); // Fallback.

/**
 * Wrapper cho shortcode mới (comments) để áp dụng filter name theo tag mới.
 *
 * @param array|string $atts Shortcode attributes.
 * @return string
 */
function init_plugin_suite_recent_comments_render_static__prefixed( $atts = array() ) {
	// Gọi hàm gốc nhưng dùng filter tag tương ứng với shortcode mới.
	return init_plugin_suite_recent_comments_render_static( $atts, 'init_plugin_suite_recent_comments' );
}

/**
 * Wrapper cho shortcode mới (reviews) để áp dụng filter name theo tag mới.
 *
 * @param array|string $atts Shortcode attributes.
 * @return string
 */
function init_plugin_suite_recent_comments_render_reviews__prefixed( $atts = array() ) {
	return init_plugin_suite_recent_comments_render_reviews( $atts, 'init_plugin_suite_recent_reviews' );
}

/**
 * Wrapper cho shortcode mới (user recent comments) để áp dụng filter name theo tag mới.
 *
 * @param array|string $atts Shortcode attributes.
 * @return string
 */
function init_plugin_suite_recent_comments_render_user__prefixed( $atts = array() ) {
	// Gọi hàm gốc nhưng dùng filter tag tương ứng với shortcode mới.
	return init_plugin_suite_recent_comments_render_user( $atts, 'init_plugin_suite_user_recent_comments' );
}

/**
 * Wrapper theo tag mới để map filter name chính xác.
 *
 * @param array|string $atts Shortcode attributes.
 * @return string
 */
function init_plugin_suite_recent_comments_render_user_reviews__prefixed( $atts = array() ) {
	return init_plugin_suite_recent_comments_render_user_reviews( $atts, 'init_plugin_suite_user_recent_reviews' );
}

/**
 * Tìm file template: ưu tiên bản override trong theme
 * ({theme}/init-recent-comments/{name}), fallback về template của plugin.
 *
 * Kết quả được nhớ trong request hiện tại — template item không còn bị
 * locate_template() (vài lần file_exists) lặp lại cho TỪNG comment/review.
 *
 * @param string $name Tên file template, ví dụ 'wrapper.php'.
 * @return string Đường dẫn tuyệt đối, hoặc chuỗi rỗng nếu không tìm thấy.
 */
function init_plugin_suite_recent_comments_locate_template( $name ) {
	static $found = array();

	$name = sanitize_file_name( $name );

	if ( isset( $found[ $name ] ) ) {
		return $found[ $name ];
	}

	$template = locate_template( 'init-recent-comments/' . $name );
	if ( ! $template ) {
		$template = INIT_PLUGIN_SUITE_IRC_TEMPLATES_PATH . $name;
	}

	$found[ $name ] = file_exists( $template ) ? $template : '';

	return $found[ $name ];
}

/**
 * CSS class cho container (dùng chung cho cả 4 shortcode).
 *
 * @param array $atts Shortcode attributes (đã qua shortcode_atts()).
 * @return string
 */
function init_plugin_suite_recent_comments_container_class( $atts ) {
	$container_class = 'init-recent-comments';

	if ( '' !== (string) $atts['maxheight'] ) {
		$container_class .= ' disable-scrollbar';
	}

	if ( 'dark' === $atts['theme'] ) {
		$container_class .= ' dark';
	}

	return $container_class;
}

/**
 * Inline style cho container từ thuộc tính maxheight.
 *
 * Trước 2.0.1 giá trị maxheight bị bỏ qua (luôn là 650px trong CSS). Giờ giá
 * trị hợp lệ (số + đơn vị px/em/rem/vh/vw/%) được áp dụng; giá trị không hợp
 * lệ vẫn giữ hành vi cũ (650px từ CSS).
 *
 * @param string $maxheight Giá trị thuộc tính maxheight.
 * @return string Chuỗi CSS đã an toàn (chỉ gồm số & đơn vị), hoặc chuỗi rỗng.
 */
function init_plugin_suite_recent_comments_container_style( $maxheight ) {
	$maxheight = strtolower( trim( (string) $maxheight ) );

	if ( '' === $maxheight || ! preg_match( '/^\d{1,5}(\.\d{1,3})?(px|em|rem|vh|vw|%)$/', $maxheight ) ) {
		return '';
	}

	return 'max-height:' . $maxheight . ';';
}

/**
 * Render recent comments (static shortcode).
 *
 * @param array|string $atts       Shortcode attributes.
 * @param string       $filter_tag Tên tag dùng cho hook "shortcode_atts_{$tag}" (giữ BC cho tag cũ & hỗ trợ tag mới).
 * @return string HTML output.
 */
function init_plugin_suite_recent_comments_render_static( $atts = array(), $filter_tag = 'init_recent_comments' ) {
	$atts = shortcode_atts(
		array(
			'number'    => 5,
			'maxheight' => '',
			'theme'     => '',
			'paged'     => '', // Chỉ dùng nếu truyền rõ ràng.
		),
		$atts,
		$filter_tag
	);

	$paged = ( '' !== (string) $atts['paged'] ) ? max( 1, absint( $atts['paged'] ) ) : 1;

	$comments = init_plugin_suite_recent_comments_get_comments(
		array(
			'number' => absint( $atts['number'] ),
			'paged'  => $paged,
		)
	);

	if ( empty( $comments ) ) {
		return '';
	}

	$template = init_plugin_suite_recent_comments_locate_template( 'wrapper.php' );
	if ( ! $template ) {
		return '';
	}

	init_plugin_suite_recent_comments_prime_comments( $comments );

	// Biến cho template wrapper.php.
	$container_class = init_plugin_suite_recent_comments_container_class( $atts );
	$container_style = init_plugin_suite_recent_comments_container_style( $atts['maxheight'] );

	ob_start();
	include $template;
	return ob_get_clean();
}

/**
 * Render recent reviews (static shortcode).
 *
 * @param array|string $atts       Shortcode attributes.
 * @param string       $filter_tag Tên tag dùng cho hook "shortcode_atts_{$tag}".
 * @return string HTML output.
 */
function init_plugin_suite_recent_comments_render_reviews( $atts = array(), $filter_tag = 'init_recent_reviews' ) {
	$atts = shortcode_atts(
		array(
			'number'    => 5,
			'maxheight' => '',
			'theme'     => '',
			'paged'     => '',
		),
		$atts,
		$filter_tag
	);

	$paged = ( '' !== (string) $atts['paged'] ) ? max( 1, absint( $atts['paged'] ) ) : 1;

	// Dùng helper có cache.
	$reviews = init_plugin_suite_recent_comments_get_reviews( 0, $paged, absint( $atts['number'] ) );

	if ( empty( $reviews ) ) {
		return '';
	}

	$template = init_plugin_suite_recent_comments_locate_template( 'review-wrapper.php' );
	if ( ! $template ) {
		return '';
	}

	// Biến cho template review-wrapper.php (giữ nguyên contract).
	$container_class     = init_plugin_suite_recent_comments_container_class( $atts );
	$irr_container_class = $container_class;
	$irr_container_style = init_plugin_suite_recent_comments_container_style( $atts['maxheight'] );
	$irr_reviews         = $reviews;
	$irr_paged           = '' !== (string) $atts['paged'] ? $paged : false;

	ob_start();
	include $template;
	return ob_get_clean();
}

/**
 * Render recent comments của 1 user cụ thể (static shortcode).
 *
 * @param array|string $atts       Shortcode attributes.
 * @param string       $filter_tag Tên tag dùng cho hook "shortcode_atts_{$tag}" (giữ BC cho tag cũ & hỗ trợ tag mới).
 * @return string HTML output.
 */
function init_plugin_suite_recent_comments_render_user( $atts = array(), $filter_tag = 'init_user_recent_comments' ) {
	$atts = shortcode_atts(
		array(
			// Xác định user.
			'user_id'    => '',
			'user_login' => '',
			'user_email' => '',
			// Hiển thị.
			'number'     => 5,
			'maxheight'  => '',
			'theme'      => '',
			'paged'      => '', // Chỉ dùng nếu truyền rõ ràng.
		),
		$atts,
		$filter_tag
	);

	// Resolve user theo ưu tiên: user_id > user_login > user_email.
	$target_user_id    = 0;
	$target_user_email = '';

	if ( '' !== (string) $atts['user_id'] ) {
		$target_user_id = absint( $atts['user_id'] );
	} elseif ( '' !== (string) $atts['user_login'] ) {
		$user = get_user_by( 'login', sanitize_user( $atts['user_login'] ) );
		if ( $user ) {
			$target_user_id = (int) $user->ID;
		}
	} elseif ( '' !== (string) $atts['user_email'] ) {
		$target_user_email = sanitize_email( $atts['user_email'] );
	}

	// Không có định danh user thì thôi.
	if ( $target_user_id <= 0 && '' === $target_user_email ) {
		return '';
	}

	$paged = ( '' !== (string) $atts['paged'] ) ? max( 1, absint( $atts['paged'] ) ) : 1;

	// Lấy comments của user (tái sử dụng query & cache style IRC).
	$comments = init_plugin_suite_recent_comments_get_user_comments(
		array(
			'number'     => absint( $atts['number'] ),
			'paged'      => $paged,
			'user_id'    => $target_user_id,
			'user_email' => $target_user_email,
		)
	);

	if ( empty( $comments ) ) {
		return '';
	}

	// Nếu vì lý do gì đó không thấy template thì trả rỗng cho an toàn.
	$template = init_plugin_suite_recent_comments_locate_template( 'wrapper.php' );
	if ( ! $template ) {
		return '';
	}

	init_plugin_suite_recent_comments_prime_comments( $comments );

	// Biến cho template wrapper.php.
	$container_class = init_plugin_suite_recent_comments_container_class( $atts );
	$container_style = init_plugin_suite_recent_comments_container_style( $atts['maxheight'] );

	ob_start();
	include $template;
	return ob_get_clean();
}

/**
 * Render recent reviews của một user.
 *
 * @param array|string $atts       Shortcode attributes.
 * @param string       $filter_tag Tên tag dùng cho hook "shortcode_atts_{$tag}".
 * @return string HTML
 */
function init_plugin_suite_recent_comments_render_user_reviews( $atts = array(), $filter_tag = 'init_user_recent_reviews' ) {
	$atts = shortcode_atts(
		array(
			'user_id'   => '',        // BẮT BUỘC > 0.
			'number'    => 5,         // Per page.
			'status'    => 'approved',
			'maxheight' => '',
			'theme'     => '',
			'paged'     => '',        // Chỉ dùng nếu truyền rõ ràng.
		),
		$atts,
		$filter_tag
	);

	$user_id = absint( $atts['user_id'] );
	if ( $user_id <= 0 ) {
		return '';
	}

	$paged    = ( '' !== (string) $atts['paged'] ) ? max( 1, absint( $atts['paged'] ) ) : 1;
	$per_page = absint( $atts['number'] );

	// Lấy reviews của user (có cache, TTL bật qua filter).
	$reviews = init_plugin_suite_recent_comments_get_user_reviews(
		array(
			'user_id'  => $user_id,
			'paged'    => $paged,
			'per_page' => $per_page,
			'status'   => sanitize_key( $atts['status'] ),
		)
	);

	if ( empty( $reviews ) ) {
		return '';
	}

	$template = init_plugin_suite_recent_comments_locate_template( 'review-wrapper.php' );
	if ( ! $template ) {
		return '';
	}

	// Biến cho template review-wrapper.php (giữ nguyên contract).
	$container_class     = init_plugin_suite_recent_comments_container_class( $atts );
	$irr_container_class = $container_class;
	$irr_container_style = init_plugin_suite_recent_comments_container_style( $atts['maxheight'] );
	$irr_reviews         = $reviews;
	$irr_paged           = '' !== (string) $atts['paged'] ? $paged : false;

	ob_start();
	include $template;
	return ob_get_clean();
}

add_action( 'admin_enqueue_scripts', 'init_plugin_suite_recent_comments_admin_enqueue' );

/**
 * Admin assets cho trang Settings (Shortcode Builder).
 *
 * @param string $hook Hook suffix của trang admin hiện tại.
 * @return void
 */
function init_plugin_suite_recent_comments_admin_enqueue( $hook ) {
	if ( 'settings_page_' . INIT_PLUGIN_SUITE_IRC_SLUG !== $hook ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	wp_enqueue_script(
		'init-recent-comments-shortcode-builder',
		INIT_PLUGIN_SUITE_IRC_ASSETS_URL . 'js/init-shortcode-builder.js',
		array(),
		INIT_PLUGIN_SUITE_IRC_VERSION,
		true
	);

	wp_localize_script(
		'init-recent-comments-shortcode-builder',
		'InitRecentCommentsShortcodeBuilder',
		array(
			'i18n' => array(
				'copy'                 => __( 'Copy', 'init-recent-comments' ),
				'copied'               => __( 'Copied!', 'init-recent-comments' ),
				'close'                => __( 'Close', 'init-recent-comments' ),
				'shortcode_preview'    => __( 'Shortcode Preview', 'init-recent-comments' ),
				'shortcode_builder'    => __( 'Shortcode Builder', 'init-recent-comments' ),
				'init_recent_comments' => __( 'Init Recent Comments', 'init-recent-comments' ),
				'number'               => __( 'Number of Comments', 'init-recent-comments' ),
				'paged'                => __( 'Pagination (optional)', 'init-recent-comments' ),
				'maxheight'            => __( 'Max Height (e.g. 300px)', 'init-recent-comments' ),
				'theme'                => __( 'Theme', 'init-recent-comments' ),
			),
		)
	);

	wp_enqueue_script(
		'init-recent-comments-admin-panel',
		INIT_PLUGIN_SUITE_IRC_ASSETS_URL . 'js/shortcodes.js',
		array( 'init-recent-comments-shortcode-builder' ),
		INIT_PLUGIN_SUITE_IRC_VERSION,
		true
	);
}
