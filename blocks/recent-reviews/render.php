<?php
// Dynamic render cho block init-recent-comments/recent-reviews.
// $attributes, $content, $block được WordPress tự inject khi dùng "render" trong block.json.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$atts = [
	'number'    => isset( $attributes['number'] ) ? (string) absint( $attributes['number'] ) : '5',
	'maxheight' => isset( $attributes['maxHeight'] ) ? (string) $attributes['maxHeight'] : '',
	'theme'     => isset( $attributes['theme'] ) ? (string) $attributes['theme'] : '',
];

if ( ! empty( $attributes['paged'] ) ) {
	$atts['paged'] = (string) absint( $attributes['paged'] );
}

// init_plugin_suite_recent_comments_render_reviews__prefixed() renders the
// exact same markup as [init_plugin_suite_recent_reviews] and already
// escapes everything internally (it includes review-wrapper.php, same as
// the shortcode does) — no extra wrapper here. Returns an empty string if
// Init Review System isn't active, same as the shortcode.
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo init_plugin_suite_recent_comments_render_reviews__prefixed( $atts );
