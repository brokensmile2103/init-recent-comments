<?php
// Dynamic render cho block init-recent-comments/user-recent-comments.
// $attributes, $content, $block được WordPress tự inject khi dùng "render" trong block.json.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$atts = [
	'number'    => isset( $attributes['number'] ) ? (string) absint( $attributes['number'] ) : '5',
	'maxheight' => isset( $attributes['maxHeight'] ) ? (string) $attributes['maxHeight'] : '',
	'theme'     => isset( $attributes['theme'] ) ? (string) $attributes['theme'] : '',
];

if ( ! empty( $attributes['userId'] ) ) {
	$atts['user_id'] = (string) absint( $attributes['userId'] );
} elseif ( ! empty( $attributes['userLogin'] ) ) {
	$atts['user_login'] = (string) $attributes['userLogin'];
} elseif ( ! empty( $attributes['userEmail'] ) ) {
	$atts['user_email'] = (string) $attributes['userEmail'];
}

if ( ! empty( $attributes['paged'] ) ) {
	$atts['paged'] = (string) absint( $attributes['paged'] );
}

// init_plugin_suite_recent_comments_render_user__prefixed() renders the
// exact same markup as [init_plugin_suite_user_recent_comments] and already
// escapes everything internally — no extra wrapper here. Returns an empty
// string if no user identifier resolves, same as the shortcode.
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo init_plugin_suite_recent_comments_render_user__prefixed( $atts );
