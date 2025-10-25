<?php
/**
 * Plugin Name: Init Recent Comments
 * Plugin URI: https://inithtml.com/plugin/init-recent-comments/
 * Description: Display recent comments with customizable templates and clean CSS. Lightweight, flexible, and built for modern WordPress sites.
 * Version: 1.2
 * Author: Init HTML
 * Author URI: https://inithtml.com/
 * Text Domain: init-recent-comments
 * Domain Path: /languages
 * Requires at least: 5.5
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined('ABSPATH') || exit;

// ===== CONSTANTS ===== //
define( 'INIT_PLUGIN_SUITE_IRC_VERSION',        '1.2' );
define( 'INIT_PLUGIN_SUITE_IRC_SLUG',           'init-recent-comments' );
define( 'INIT_PLUGIN_SUITE_IRC_OPTION',         'init_plugin_suite_init_recent_comments_settings' );
define( 'INIT_PLUGIN_SUITE_IRC_NAMESPACE',      'initreco/v1' );

define( 'INIT_PLUGIN_SUITE_IRC_URL',            plugin_dir_url( __FILE__ ) );
define( 'INIT_PLUGIN_SUITE_IRC_PATH',           plugin_dir_path( __FILE__ ) );
define( 'INIT_PLUGIN_SUITE_IRC_ASSETS_URL',     INIT_PLUGIN_SUITE_IRC_URL . 'assets/' );
define( 'INIT_PLUGIN_SUITE_IRC_ASSETS_PATH',    INIT_PLUGIN_SUITE_IRC_PATH . 'assets/' );
define( 'INIT_PLUGIN_SUITE_IRC_TEMPLATES_PATH', INIT_PLUGIN_SUITE_IRC_PATH . 'templates/' );
define( 'INIT_PLUGIN_SUITE_IRC_INCLUDES_PATH',  INIT_PLUGIN_SUITE_IRC_PATH . 'includes/' );

// ===== ENQUEUE DEFAULT CSS (CAN BE DISABLED) ===== //
add_action( 'wp_enqueue_scripts', 'init_plugin_suite_recent_comments_enqueue_styles' );
function init_plugin_suite_recent_comments_enqueue_styles() {
	$options     = get_option( INIT_PLUGIN_SUITE_IRC_OPTION );
	$disable_css = isset( $options['disable_css'] ) && $options['disable_css'];

	$disable_css = apply_filters( 'init_plugin_suite_recent_comments_disable_css', $disable_css );

	if ( ! $disable_css ) {
		wp_enqueue_style(
			'init-recent-comments-style',
			INIT_PLUGIN_SUITE_IRC_ASSETS_URL . 'css/style.css',
			[],
			INIT_PLUGIN_SUITE_IRC_VERSION
		);
	}
}

// ===== INCLUDE FUNCTIONALITY ===== //
require_once INIT_PLUGIN_SUITE_IRC_INCLUDES_PATH . 'comments-utils.php';
require_once INIT_PLUGIN_SUITE_IRC_INCLUDES_PATH . 'shortcodes.php';
require_once INIT_PLUGIN_SUITE_IRC_INCLUDES_PATH . 'settings-page.php';
