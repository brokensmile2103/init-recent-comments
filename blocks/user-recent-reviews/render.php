<?php
/**
 * Dynamic render cho block init-recent-comments/user-recent-reviews.
 *
 * $attributes, $content, $block được WordPress tự inject khi dùng "render"
 * trong block.json (file được include bên trong scope của một hàm).
 *
 * @package InitRecentComments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$init_plugin_suite_irc_atts = array(
	'number'    => isset( $attributes['number'] ) ? (string) absint( $attributes['number'] ) : '5',
	'status'    => isset( $attributes['status'] ) ? (string) $attributes['status'] : 'approved',
	'maxheight' => isset( $attributes['maxHeight'] ) ? (string) $attributes['maxHeight'] : '',
	'theme'     => isset( $attributes['theme'] ) ? (string) $attributes['theme'] : '',
);

if ( ! empty( $attributes['userId'] ) ) {
	$init_plugin_suite_irc_atts['user_id'] = (string) absint( $attributes['userId'] );
}

if ( ! empty( $attributes['paged'] ) ) {
	$init_plugin_suite_irc_atts['paged'] = (string) absint( $attributes['paged'] );
}

// init_plugin_suite_recent_comments_render_user_reviews__prefixed() renders
// the exact same markup as [init_plugin_suite_user_recent_reviews] and
// already escapes everything internally — no extra wrapper here. Returns an
// empty string if no valid user_id is given or Init Review System isn't
// active, same as the shortcode.
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo init_plugin_suite_recent_comments_render_user_reviews__prefixed( $init_plugin_suite_irc_atts );
