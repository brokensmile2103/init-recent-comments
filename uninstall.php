<?php
/**
 * Uninstall handler for Init Recent Comments
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Xóa option settings chính
if ( defined( 'INIT_PLUGIN_SUITE_IRC_OPTION' ) ) {
	delete_option( INIT_PLUGIN_SUITE_IRC_OPTION );
} else {
	// fallback an toàn nếu constant chưa load
	delete_option( 'init_plugin_suite_irc_settings' );
}
