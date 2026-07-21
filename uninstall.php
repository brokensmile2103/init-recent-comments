<?php
/**
 * Uninstall handler for Init Recent Comments
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Xóa option settings chính
// Lưu ý: uninstall.php chạy độc lập, KHÔNG include file chính của plugin,
// nên các constant như INIT_PLUGIN_SUITE_IRC_OPTION sẽ KHÔNG tồn tại ở đây.
// Phải dùng đúng tên option key thực tế (khớp với hàm register_setting() trong settings-page.php).
delete_option( 'init_plugin_suite_init_recent_comments_settings' );
