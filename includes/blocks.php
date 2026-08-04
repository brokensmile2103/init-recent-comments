<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ============================================================================
// Block Editor integration
// ----------------------------------------------------------------------------
// 4 block tương ứng 1-1 với 4 shortcode đã có (dùng tag mới, "prefixed"):
// [init_plugin_suite_recent_comments], [init_plugin_suite_recent_reviews],
// [init_plugin_suite_user_recent_comments], [init_plugin_suite_user_recent_reviews].
// Mỗi block dùng "render" trong block.json (PHP, từ WP 6.1+) trỏ tới file
// render.php — file này chỉ gọi lại đúng hàm shortcode gốc (đã là hàm có tên
// sẵn trong includes/shortcodes.php), nên KHÔNG có logic hiển thị nào bị lặp
// lại/lệch so với shortcode.
//
// Phần JS (assets/js/blocks-editor.js) là vanilla JS thuần, không build step,
// dùng ServerSideRender để preview ngay trong Block Editor.
//
// Kiến trúc này đồng bộ với Init View Count, Init Live Search, và Init Review
// System (block.json + render.php + 1 file JS chung cho toàn bộ block).
// ============================================================================

add_filter( 'block_categories_all', 'init_plugin_suite_recent_comments_block_category', 10, 2 );
/**
 * Thêm 1 category riêng trong block inserter cho gọn, thay vì rơi vào "Widgets".
 *
 * @param array                   $categories     Danh sách category hiện có.
 * @param WP_Block_Editor_Context $editor_context Context hiện tại của editor (không dùng tới,
 *                                                nhưng bắt buộc phải khai báo theo đúng chữ ký
 *                                                mà hook 'block_categories_all' truyền vào).
 * @return array
 */
// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
function init_plugin_suite_recent_comments_block_category( $categories, $editor_context ) {
	return array_merge(
		[
			[
				'slug'  => 'init-recent-comments',
				'title' => __( 'Init Recent Comments', 'init-recent-comments' ),
				'icon'  => 'admin-comments',
			],
		],
		$categories
	);
}

add_action( 'init', 'init_plugin_suite_recent_comments_register_style_handle', 5 );
/**
 * Đăng ký (không enqueue) handle CSS front-end, để block.json của cả 4 block
 * có thể tham chiếu qua "style" — WordPress sẽ tự enqueue đúng lúc, đúng chỗ
 * (cả trong Block Editor lẫn ngoài front-end) khi block thực sự được dùng.
 *
 * Handle này cũng được enqueue độc lập ở file plugin chính (tuỳ theo option
 * "disable_css") — đăng ký lại ở đây với cùng src là an toàn (WordPress chỉ
 * ghi đè bằng dữ liệu giống hệt), và đảm bảo handle luôn tồn tại sớm cho
 * riêng cơ chế "style" của block.json, kể cả khi trang chỉ dùng block mà CSS
 * mặc định đã bị tắt qua Settings.
 *
 * Ưu tiên chạy trước (priority 5) hàm đăng ký block bên dưới.
 *
 * @return void
 */
function init_plugin_suite_recent_comments_register_style_handle() {
	wp_register_style(
		'init-recent-comments-style',
		INIT_PLUGIN_SUITE_IRC_ASSETS_URL . 'css/style.css',
		[],
		INIT_PLUGIN_SUITE_IRC_VERSION
	);
}

add_action( 'init', 'init_plugin_suite_recent_comments_register_blocks', 10 );
/**
 * Đăng ký script cho Block Editor và 4 block type.
 *
 * @return void
 */
function init_plugin_suite_recent_comments_register_blocks() {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	wp_register_script(
		'init-recent-comments-blocks-editor',
		INIT_PLUGIN_SUITE_IRC_ASSETS_URL . 'js/blocks-editor.js',
		[
			'wp-blocks',
			'wp-element',
			'wp-block-editor',
			'wp-components',
			'wp-i18n',
			'wp-server-side-render',
		],
		INIT_PLUGIN_SUITE_IRC_VERSION,
		true
	);

	if ( function_exists( 'wp_set_script_translations' ) ) {
		wp_set_script_translations(
			'init-recent-comments-blocks-editor',
			'init-recent-comments',
			INIT_PLUGIN_SUITE_IRC_PATH . 'languages'
		);
	}

	register_block_type( INIT_PLUGIN_SUITE_IRC_PATH . 'blocks/recent-comments' );
	register_block_type( INIT_PLUGIN_SUITE_IRC_PATH . 'blocks/recent-reviews' );
	register_block_type( INIT_PLUGIN_SUITE_IRC_PATH . 'blocks/user-recent-comments' );
	register_block_type( INIT_PLUGIN_SUITE_IRC_PATH . 'blocks/user-recent-reviews' );
}
