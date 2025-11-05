<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcode tags (new, prefixed)
 * - Primary (khuyến nghị dùng): init_plugin_suite_recent_comments
 * - Primary (khuyến nghị dùng): init_plugin_suite_recent_reviews
 *
 * Backward-compat fallback (vẫn hỗ trợ):
 * - init_recent_comments
 * - init_recent_reviews
 */
add_shortcode( 'init_plugin_suite_recent_comments', 	 'init_plugin_suite_recent_comments_render_static__prefixed' );
add_shortcode( 'init_plugin_suite_recent_reviews',  	 'init_plugin_suite_recent_comments_render_reviews__prefixed' );
add_shortcode( 'init_plugin_suite_user_recent_comments', 'init_plugin_suite_recent_comments_render_user__prefixed' );
add_shortcode( 'init_plugin_suite_user_recent_reviews', 'init_plugin_suite_recent_comments_render_user_reviews__prefixed' );
add_shortcode( 'init_recent_comments', 					 'init_plugin_suite_recent_comments_render_static' ); // fallback
add_shortcode( 'init_recent_reviews',  					 'init_plugin_suite_recent_comments_render_reviews' ); // fallback
add_shortcode( 'init_user_recent_comments', 			 'init_plugin_suite_recent_comments_render_user' ); // fallback
add_shortcode( 'init_user_recent_reviews',               'init_plugin_suite_recent_comments_render_user_reviews' ); // fallback

/**
 * Wrapper cho shortcode mới (comments) để áp dụng filter name theo tag mới.
 */
function init_plugin_suite_recent_comments_render_static__prefixed( $atts = [] ) {
	// Gọi hàm gốc nhưng dùng filter tag tương ứng với shortcode mới
	return init_plugin_suite_recent_comments_render_static( $atts, 'init_plugin_suite_recent_comments' );
}

/**
 * Wrapper cho shortcode mới (reviews) để áp dụng filter name theo tag mới.
 */
function init_plugin_suite_recent_comments_render_reviews__prefixed( $atts = [] ) {
	return init_plugin_suite_recent_comments_render_reviews( $atts, 'init_plugin_suite_recent_reviews' );
}

/**
 * Wrapper cho shortcode mới (user recent comments) để áp dụng filter name theo tag mới.
 */
function init_plugin_suite_recent_comments_render_user__prefixed( $atts = [] ) {
	// Gọi hàm gốc nhưng dùng filter tag tương ứng với shortcode mới
	return init_plugin_suite_recent_comments_render_user( $atts, 'init_plugin_suite_user_recent_comments' );
}

/**
 * Wrapper theo tag mới để map filter name chính xác.
 */
function init_plugin_suite_recent_comments_render_user_reviews__prefixed( $atts = [] ) {
	return init_plugin_suite_recent_comments_render_user_reviews( $atts, 'init_plugin_suite_user_recent_reviews' );
}

/**
 * Render recent comments (static shortcode).
 *
 * @param array  $atts       Shortcode attributes.
 * @param string $filter_tag Tên tag dùng cho hook "shortcode_atts_{$tag}" (giữ BC cho tag cũ & hỗ trợ tag mới).
 * @return string HTML output.
 */
function init_plugin_suite_recent_comments_render_static( $atts = [], $filter_tag = 'init_recent_comments' ) {
	$atts = shortcode_atts( [
		'number'    => 5,
		'maxheight' => '',
		'theme'     => '',
		'paged'     => '', // chỉ dùng nếu truyền rõ ràng
	], $atts, $filter_tag );

	$paged = ( $atts['paged'] !== '' ) ? max( 1, absint( $atts['paged'] ) ) : 1;

	$comments = init_plugin_suite_recent_comments_get_comments( [
		'number' => absint( $atts['number'] ),
		'paged'  => $paged,
	] );

	if ( empty( $comments ) ) {
		return '';
	}

	// CSS class wrapper
	$container_class = 'init-recent-comments';
	if ( $atts['maxheight'] !== '' ) {
		$container_class .= ' disable-scrollbar';
	}
	if ( $atts['theme'] === 'dark' ) {
		$container_class .= ' dark';
	}

	ob_start();

	// Biến cho template
	$irc_container_class = $container_class;
	$irc_paged           = $atts['paged'] !== '' ? $paged : false;

	$template = locate_template( 'init-recent-comments/wrapper.php' );
	if ( ! $template ) {
		$template = INIT_PLUGIN_SUITE_IRC_TEMPLATES_PATH . 'wrapper.php';
	}

	include $template;

	return ob_get_clean();
}

/**
 * Render recent reviews (static shortcode).
 *
 * @param array  $atts       Shortcode attributes.
 * @param string $filter_tag Tên tag dùng cho hook "shortcode_atts_{$tag}".
 * @return string HTML output.
 */
function init_plugin_suite_recent_comments_render_reviews( $atts = [], $filter_tag = 'init_recent_reviews' ) {
	$atts = shortcode_atts( [
		'number'    => 5,
		'maxheight' => '',
		'theme'     => '',
		'paged'     => '',
	], $atts, $filter_tag );

	$paged = ( $atts['paged'] !== '' ) ? max( 1, absint( $atts['paged'] ) ) : 1;

	// Dùng helper có cache
	$reviews = init_plugin_suite_recent_comments_get_reviews( 0, $paged, absint( $atts['number'] ) );

	if ( empty( $reviews ) ) {
		return '';
	}

	// CSS class wrapper
	$container_class = 'init-recent-comments';
	if ( $atts['maxheight'] !== '' ) {
		$container_class .= ' disable-scrollbar';
	}
	if ( $atts['theme'] === 'dark' ) {
		$container_class .= ' dark';
	}

	ob_start();

	// Biến cho template
	$irr_container_class = $container_class;
	$irr_reviews         = $reviews;
	$irr_paged           = $atts['paged'] !== '' ? $paged : false;

	$template = locate_template( 'init-recent-comments/review-wrapper.php' );
	if ( ! $template ) {
		$template = INIT_PLUGIN_SUITE_IRC_TEMPLATES_PATH . 'review-wrapper.php';
	}

	include $template;

	return ob_get_clean();
}

/**
 * Render recent comments của 1 user cụ thể (static shortcode).
 *
 * @param array  $atts       Shortcode attributes.
 * @param string $filter_tag Tên tag dùng cho hook "shortcode_atts_{$tag}" (giữ BC cho tag cũ & hỗ trợ tag mới).
 * @return string HTML output.
 */
function init_plugin_suite_recent_comments_render_user( $atts = [], $filter_tag = 'init_user_recent_comments' ) {
	$atts = shortcode_atts( [
		// Xác định user
		'user_id'    => '',
		'user_login' => '',
		'user_email' => '',
		// Hiển thị
		'number'     => 5,
		'maxheight'  => '',
		'theme'      => '',
		'paged'      => '', // chỉ dùng nếu truyền rõ ràng
	], $atts, $filter_tag );

	// Resolve user theo ưu tiên: user_id > user_login > user_email
	$target_user_id    = 0;
	$target_user_email = '';

	if ( $atts['user_id'] !== '' ) {
		$target_user_id = absint( $atts['user_id'] );
	} elseif ( $atts['user_login'] !== '' ) {
		$user = get_user_by( 'login', sanitize_user( $atts['user_login'] ) );
		if ( $user && ! is_wp_error( $user ) ) {
			$target_user_id = (int) $user->ID;
		}
	} elseif ( $atts['user_email'] !== '' ) {
		$target_user_email = sanitize_email( $atts['user_email'] );
	}

	// Không có định danh user thì thôi.
	if ( $target_user_id <= 0 && $target_user_email === '' ) {
		return '';
	}

	$paged = ( $atts['paged'] !== '' ) ? max( 1, absint( $atts['paged'] ) ) : 1;

	// Lấy comments của user (tái sử dụng query & cache style IRC)
	$comments = init_plugin_suite_recent_comments_get_user_comments( [
		'number'     => absint( $atts['number'] ),
		'paged'      => $paged,
		'user_id'    => $target_user_id,
		'user_email' => $target_user_email,
	] );

	if ( empty( $comments ) ) {
		return '';
	}

	// CSS class wrapper (tái sử dụng logic)
	$container_class = 'init-recent-comments';
	if ( $atts['maxheight'] !== '' ) {
		$container_class .= ' disable-scrollbar';
	}
	if ( $atts['theme'] === 'dark' ) {
		$container_class .= ' dark';
	}

	ob_start();

	// Biến cho template (tái sử dụng wrapper.php của init_recent_comments)
	$irc_container_class = $container_class;
	$irc_paged           = $atts['paged'] !== '' ? $paged : false;

	$template = locate_template( 'init-recent-comments/wrapper.php' );
	if ( ! $template ) {
		$template = defined( 'INIT_PLUGIN_SUITE_IRC_TEMPLATES_PATH' )
			? INIT_PLUGIN_SUITE_IRC_TEMPLATES_PATH . 'wrapper.php'
			: ''; // đề phòng hờ
	}

	// Nếu vì lý do gì đó không thấy template thì trả rỗng cho an toàn
	if ( ! $template || ! file_exists( $template ) ) {
		return '';
	}

	include $template;

	return ob_get_clean();
}

/**
 * Render recent reviews của một user.
 *
 * @param array  $atts
 * @param string $filter_tag
 * @return string HTML
 */
function init_plugin_suite_recent_comments_render_user_reviews( $atts = [], $filter_tag = 'init_user_recent_reviews' ) {
	$atts = shortcode_atts( [
		'user_id'   => '',        // BẮT BUỘC >0
		'number'    => 5,         // per_page
		'status'    => 'approved',
		'maxheight' => '',
		'theme'     => '',
		'paged'     => '',        // chỉ dùng nếu truyền rõ ràng
	], $atts, $filter_tag );

	$user_id = absint( $atts['user_id'] );
	if ( $user_id <= 0 ) {
		return '';
	}

	$paged    = ( $atts['paged'] !== '' ) ? max( 1, absint( $atts['paged'] ) ) : 1;
	$per_page = max( 0, absint( $atts['number'] ) );

	// Lấy reviews của user (có cache, TTL bật qua filter)
	$reviews = init_plugin_suite_recent_comments_get_user_reviews( [
		'user_id'  => $user_id,
		'paged'    => $paged,
		'per_page' => $per_page,
		'status'   => sanitize_key( $atts['status'] ),
	] );

	if ( empty( $reviews ) ) {
		return '';
	}

	// CSS class wrapper (re-use)
	$container_class = 'init-recent-comments';
	if ( $atts['maxheight'] !== '' ) { $container_class .= ' disable-scrollbar'; }
	if ( $atts['theme'] === 'dark' ) { $container_class .= ' dark'; }

	ob_start();

	// Biến cho template review-wrapper.php (giữ nguyên contract)
	$irr_container_class = $container_class;
	$irr_reviews         = $reviews;
	$irr_paged           = $atts['paged'] !== '' ? $paged : false;

	$template = locate_template( 'init-recent-comments/review-wrapper.php' );
	if ( ! $template ) {
		$template = defined( 'INIT_PLUGIN_SUITE_IRC_TEMPLATES_PATH' )
			? INIT_PLUGIN_SUITE_IRC_TEMPLATES_PATH . 'review-wrapper.php'
			: '';
	}
	if ( ! $template || ! file_exists( $template ) ) {
		return '';
	}

	include $template;
	return ob_get_clean();
}

/**
 * Admin assets (giữ nguyên).
 */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( $hook !== 'settings_page_' . INIT_PLUGIN_SUITE_IRC_SLUG ) {
		return;
	}

	wp_enqueue_script(
		'init-recent-comments-shortcode-builder',
		INIT_PLUGIN_SUITE_IRC_ASSETS_URL . 'js/init-shortcode-builder.js',
		[],
		INIT_PLUGIN_SUITE_IRC_VERSION,
		true
	);

	wp_localize_script(
		'init-recent-comments-shortcode-builder',
		'InitRecentCommentsShortcodeBuilder',
		[
			'i18n' => [
				'copy'                => __( 'Copy', 'init-recent-comments' ),
				'copied'              => __( 'Copied!', 'init-recent-comments' ),
				'close'               => __( 'Close', 'init-recent-comments' ),
				'shortcode_preview'   => __( 'Shortcode Preview', 'init-recent-comments' ),
				'shortcode_builder'   => __( 'Shortcode Builder', 'init-recent-comments' ),
				'init_recent_comments'=> __( 'Init Recent Comments', 'init-recent-comments' ),
				'number'              => __( 'Number of Comments', 'init-recent-comments' ),
				'paged'               => __( 'Pagination (optional)', 'init-recent-comments' ),
				'maxheight'           => __( 'Max Height (e.g. 300px)', 'init-recent-comments' ),
				'theme'               => __( 'Theme', 'init-recent-comments' ),
			],
		]
	);

	wp_enqueue_script(
		'init-recent-comments-admin-panel',
		INIT_PLUGIN_SUITE_IRC_ASSETS_URL . 'js/shortcodes.js',
		[ 'init-recent-comments-shortcode-builder' ],
		INIT_PLUGIN_SUITE_IRC_VERSION,
		true
	);
} );
