<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Abilities API (WordPress 6.9+). Bail out silently on older versions so this
// file is always safe to require regardless of the host site's WP version.
if ( ! function_exists( 'wp_register_ability' ) ) {
	return;
}

// Register the ability category used by this plugin.
add_action( 'wp_abilities_api_categories_init', 'init_plugin_suite_recent_comments_register_ability_categories' );
function init_plugin_suite_recent_comments_register_ability_categories() {
	if ( function_exists( 'wp_has_ability_category' ) && wp_has_ability_category( 'init-recent-comments' ) ) {
		return;
	}

	wp_register_ability_category(
		'init-recent-comments',
		[
			'label'       => __( 'Init Recent Comments', 'init-recent-comments' ),
			'description' => __( 'Abilities exposed by the Init Recent Comments plugin.', 'init-recent-comments' ),
		]
	);
}

// Register the abilities themselves. All 4 map 1:1 to the plugin's 4
// shortcodes and are pure reads — this plugin has no REST API and no
// write/submit actions at all, so there is nothing to exclude here (unlike
// Init Review System, which deliberately keeps vote/submit/react out of its
// Abilities API).
add_action( 'wp_abilities_api_init', 'init_plugin_suite_recent_comments_register_abilities' );
function init_plugin_suite_recent_comments_register_abilities() {
	wp_register_ability(
		'init-recent-comments/get-recent-comments',
		[
			'label'               => __( 'Get Recent Comments', 'init-recent-comments' ),
			'description'         => __( 'Returns the most recent approved comments site-wide, the same data shown by the [init_recent_comments] shortcode.', 'init-recent-comments' ),
			'category'            => 'init-recent-comments',
			'input_schema'        => [
				'type'       => 'object',
				'properties' => [
					'number' => [
						'type'        => 'integer',
						'description' => __( 'Number of comments to return.', 'init-recent-comments' ),
						'default'     => 5,
						'minimum'     => 1,
						'maximum'     => 50,
					],
					'paged'  => [
						'type'        => 'integer',
						'description' => __( 'Page number.', 'init-recent-comments' ),
						'default'     => 1,
						'minimum'     => 1,
					],
				],
			],
			'output_schema'       => init_plugin_suite_recent_comments_ability_comment_list_schema(),
			'execute_callback'    => 'init_plugin_suite_recent_comments_ability_get_recent_comments',
			'permission_callback' => '__return_true',
			'meta'                => [
				'show_in_rest' => true,
				'annotations'  => [
					'readonly' => true,
				],
			],
		]
	);

	wp_register_ability(
		'init-recent-comments/get-recent-reviews',
		[
			'label'               => __( 'Get Recent Reviews', 'init-recent-comments' ),
			'description'         => __( 'Returns the most recent approved reviews site-wide, the same data shown by the [init_recent_reviews] shortcode. Requires Init Review System to be active; returns an empty list otherwise.', 'init-recent-comments' ),
			'category'            => 'init-recent-comments',
			'input_schema'        => [
				'type'       => 'object',
				'properties' => [
					'number' => [
						'type'        => 'integer',
						'description' => __( 'Number of reviews to return.', 'init-recent-comments' ),
						'default'     => 5,
						'minimum'     => 1,
						'maximum'     => 50,
					],
					'paged'  => [
						'type'        => 'integer',
						'description' => __( 'Page number.', 'init-recent-comments' ),
						'default'     => 1,
						'minimum'     => 1,
					],
				],
			],
			'output_schema'       => init_plugin_suite_recent_comments_ability_review_list_schema(),
			'execute_callback'    => 'init_plugin_suite_recent_comments_ability_get_recent_reviews',
			'permission_callback' => '__return_true',
			'meta'                => [
				'show_in_rest' => true,
				'annotations'  => [
					'readonly' => true,
				],
			],
		]
	);

	wp_register_ability(
		'init-recent-comments/get-user-recent-comments',
		[
			'label'               => __( 'Get User Recent Comments', 'init-recent-comments' ),
			'description'         => __( 'Returns the most recent approved comments by a specific user, the same data shown by the [init_user_recent_comments] shortcode.', 'init-recent-comments' ),
			'category'            => 'init-recent-comments',
			'input_schema'        => [
				'type'       => 'object',
				'properties' => [
					'user_id'    => [
						'type'        => 'integer',
						'description' => __( 'Registered user ID. Takes priority over user_login/user_email.', 'init-recent-comments' ),
						'minimum'     => 1,
					],
					'user_login' => [
						'type'        => 'string',
						'description' => __( 'Username. Used if user_id is not provided.', 'init-recent-comments' ),
					],
					'user_email' => [
						'type'        => 'string',
						'description' => __( 'Email address, for matching guest comments. Used if neither user_id nor user_login is provided.', 'init-recent-comments' ),
					],
					'number'     => [
						'type'        => 'integer',
						'description' => __( 'Number of comments to return.', 'init-recent-comments' ),
						'default'     => 5,
						'minimum'     => 1,
						'maximum'     => 50,
					],
					'paged'      => [
						'type'        => 'integer',
						'description' => __( 'Page number.', 'init-recent-comments' ),
						'default'     => 1,
						'minimum'     => 1,
					],
				],
			],
			'output_schema'       => init_plugin_suite_recent_comments_ability_comment_list_schema(),
			'execute_callback'    => 'init_plugin_suite_recent_comments_ability_get_user_recent_comments',
			'permission_callback' => '__return_true',
			'meta'                => [
				'show_in_rest' => true,
				'annotations'  => [
					'readonly' => true,
				],
			],
		]
	);

	wp_register_ability(
		'init-recent-comments/get-user-recent-reviews',
		[
			'label'               => __( 'Get User Recent Reviews', 'init-recent-comments' ),
			'description'         => __( 'Returns the most recent reviews submitted by a specific user, the same data shown by the [init_user_recent_reviews] shortcode. Requires Init Review System to be active; returns an empty list otherwise.', 'init-recent-comments' ),
			'category'            => 'init-recent-comments',
			'input_schema'        => [
				'type'       => 'object',
				'properties' => [
					'user_id' => [
						'type'        => 'integer',
						'description' => __( 'Registered user ID.', 'init-recent-comments' ),
						'minimum'     => 1,
					],
					'number'  => [
						'type'        => 'integer',
						'description' => __( 'Number of reviews to return.', 'init-recent-comments' ),
						'default'     => 5,
						'minimum'     => 1,
						'maximum'     => 50,
					],
					'paged'   => [
						'type'        => 'integer',
						'description' => __( 'Page number.', 'init-recent-comments' ),
						'default'     => 1,
						'minimum'     => 1,
					],
					'status'  => [
						'type'        => 'string',
						'description' => __( 'Review moderation status.', 'init-recent-comments' ),
						'default'     => 'approved',
					],
				],
				'required'   => [ 'user_id' ],
			],
			'output_schema'       => init_plugin_suite_recent_comments_ability_review_list_schema(),
			'execute_callback'    => 'init_plugin_suite_recent_comments_ability_get_user_recent_reviews',
			'permission_callback' => '__return_true',
			'meta'                => [
				'show_in_rest' => true,
				'annotations'  => [
					'readonly' => true,
				],
			],
		]
	);
}

// Shared output schema for the two comment-list abilities.
function init_plugin_suite_recent_comments_ability_comment_list_schema() {
	return [
		'type'  => 'array',
		'items' => [
			'type'       => 'object',
			'properties' => [
				'id'          => [ 'type' => 'integer' ],
				'author'      => [ 'type' => 'string' ],
				'avatar_url'  => [ 'type' => 'string' ],
				'content'     => [ 'type' => 'string' ],
				'post_id'     => [ 'type' => 'integer' ],
				'post_title'  => [ 'type' => 'string' ],
				'comment_url' => [ 'type' => 'string' ],
				'date'        => [ 'type' => 'string' ],
			],
		],
	];
}

// Shared output schema for the two review-list abilities.
function init_plugin_suite_recent_comments_ability_review_list_schema() {
	return [
		'type'  => 'array',
		'items' => [
			'type'       => 'object',
			'properties' => [
				'id'              => [ 'type' => 'integer' ],
				'post_id'         => [ 'type' => 'integer' ],
				'user_id'         => [ 'type' => 'integer' ],
				'display_name'    => [ 'type' => 'string' ],
				'avatar_url'      => [ 'type' => 'string' ],
				'criteria_scores' => [ 'type' => 'object' ],
				'avg_score'       => [ 'type' => 'number' ],
				'review_content'  => [ 'type' => 'string' ],
				'created_at'      => [ 'type' => 'string' ],
			],
		],
	];
}

/**
 * Converts a list of WP_Comment objects into plain, JSON-friendly arrays.
 * Shared by both comment-list ability callbacks.
 *
 * @param WP_Comment[] $comments Comments as returned by get_comments().
 * @return array
 */
function init_plugin_suite_recent_comments_ability_build_comment_items( $comments ) {
	$items = [];

	foreach ( $comments as $comment ) {
		if ( ! $comment instanceof WP_Comment ) {
			continue;
		}

		$items[] = [
			'id'          => (int) $comment->comment_ID,
			'author'      => get_comment_author( $comment ),
			'avatar_url'  => get_avatar_url( $comment, [ 'size' => 42 ] ),
			'content'     => wp_trim_words( get_comment_text( $comment ), 20, '...' ),
			'post_id'     => (int) $comment->comment_post_ID,
			'post_title'  => get_the_title( $comment->comment_post_ID ),
			'comment_url' => get_comment_link( $comment ),
			'date'        => $comment->comment_date,
		];
	}

	return $items;
}

/**
 * Converts a list of Init Review System review rows into plain,
 * JSON-friendly arrays with display_name/avatar_url resolved. Shared by both
 * review-list ability callbacks.
 *
 * @param array $reviews Review rows as returned by the Init Review System helpers.
 * @return array
 */
function init_plugin_suite_recent_comments_ability_build_review_items( $reviews ) {
	$items = [];

	foreach ( (array) $reviews as $review ) {
		$user_id = isset( $review['user_id'] ) ? (int) $review['user_id'] : 0;

		if ( $user_id > 0 ) {
			$user         = get_userdata( $user_id );
			$display_name = $user ? $user->display_name : __( 'Anonymous', 'init-recent-comments' );
		} else {
			$display_name = __( 'Anonymous', 'init-recent-comments' );
		}

		// Same convention as templates/review-item.php: rely on WP's built-in
		// "mm" (mystery man) default avatar instead of a bundled image asset.
		$avatar_url = get_avatar_url(
			$user_id,
			[
				'size'    => 48,
				'default' => 'mm',
			]
		);

		$items[] = [
			'id'              => isset( $review['id'] ) ? (int) $review['id'] : 0,
			'post_id'         => isset( $review['post_id'] ) ? (int) $review['post_id'] : 0,
			'user_id'         => $user_id,
			'display_name'    => $display_name,
			'avatar_url'      => $avatar_url,
			'criteria_scores' => isset( $review['criteria_scores'] ) ? $review['criteria_scores'] : [],
			'avg_score'       => isset( $review['avg_score'] ) ? (float) $review['avg_score'] : 0.0,
			'review_content'  => isset( $review['review_content'] ) ? $review['review_content'] : '',
			'created_at'      => isset( $review['created_at'] ) ? $review['created_at'] : '',
		];
	}

	return $items;
}

// Execute callback for init-recent-comments/get-recent-comments.
function init_plugin_suite_recent_comments_ability_get_recent_comments( $input ) {
	$number = isset( $input['number'] ) ? absint( $input['number'] ) : 5;
	$paged  = isset( $input['paged'] ) ? max( 1, absint( $input['paged'] ) ) : 1;
	$number = $number > 0 ? min( $number, 50 ) : 5;

	$comments = init_plugin_suite_recent_comments_get_comments(
		[
			'number' => $number,
			'paged'  => $paged,
		]
	);

	return init_plugin_suite_recent_comments_ability_build_comment_items( $comments );
}

// Execute callback for init-recent-comments/get-recent-reviews.
function init_plugin_suite_recent_comments_ability_get_recent_reviews( $input ) {
	$number = isset( $input['number'] ) ? absint( $input['number'] ) : 5;
	$paged  = isset( $input['paged'] ) ? max( 1, absint( $input['paged'] ) ) : 1;
	$number = $number > 0 ? min( $number, 50 ) : 5;

	$reviews = init_plugin_suite_recent_comments_get_reviews( 0, $paged, $number );

	return init_plugin_suite_recent_comments_ability_build_review_items( $reviews );
}

// Execute callback for init-recent-comments/get-user-recent-comments.
function init_plugin_suite_recent_comments_ability_get_user_recent_comments( $input ) {
	$user_id    = isset( $input['user_id'] ) ? absint( $input['user_id'] ) : 0;
	$user_login = isset( $input['user_login'] ) ? sanitize_user( $input['user_login'] ) : '';
	$user_email = isset( $input['user_email'] ) ? sanitize_email( $input['user_email'] ) : '';

	$target_user_id    = 0;
	$target_user_email = '';

	if ( $user_id > 0 ) {
		$target_user_id = $user_id;
	} elseif ( '' !== $user_login ) {
		$user = get_user_by( 'login', $user_login );
		if ( $user && ! is_wp_error( $user ) ) {
			$target_user_id = (int) $user->ID;
		}
	} elseif ( '' !== $user_email ) {
		$target_user_email = $user_email;
	}

	if ( $target_user_id <= 0 && '' === $target_user_email ) {
		return new WP_Error(
			'init_recent_comments_missing_user',
			__( 'One of user_id, user_login, or user_email is required.', 'init-recent-comments' )
		);
	}

	$number = isset( $input['number'] ) ? absint( $input['number'] ) : 5;
	$paged  = isset( $input['paged'] ) ? max( 1, absint( $input['paged'] ) ) : 1;
	$number = $number > 0 ? min( $number, 50 ) : 5;

	$comments = init_plugin_suite_recent_comments_get_user_comments(
		[
			'number'     => $number,
			'paged'      => $paged,
			'user_id'    => $target_user_id,
			'user_email' => $target_user_email,
		]
	);

	return init_plugin_suite_recent_comments_ability_build_comment_items( $comments );
}

// Execute callback for init-recent-comments/get-user-recent-reviews.
function init_plugin_suite_recent_comments_ability_get_user_recent_reviews( $input ) {
	$user_id = isset( $input['user_id'] ) ? absint( $input['user_id'] ) : 0;

	if ( ! $user_id ) {
		return new WP_Error(
			'init_recent_comments_invalid_user_id',
			__( 'A valid user_id is required.', 'init-recent-comments' )
		);
	}

	$number = isset( $input['number'] ) ? absint( $input['number'] ) : 5;
	$paged  = isset( $input['paged'] ) ? max( 1, absint( $input['paged'] ) ) : 1;
	$number = $number > 0 ? min( $number, 50 ) : 5;
	$status = isset( $input['status'] ) ? sanitize_key( $input['status'] ) : 'approved';

	$reviews = init_plugin_suite_recent_comments_get_user_reviews(
		[
			'user_id'  => $user_id,
			'paged'    => $paged,
			'per_page' => $number,
			'status'   => $status,
		]
	);

	return init_plugin_suite_recent_comments_ability_build_review_items( $reviews );
}
