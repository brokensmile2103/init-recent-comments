# Init Recent Comments – Templated, Modern, Minimal

> Display recent comments using clean templates and minimal CSS. Developer-friendly, fast, and built for modern WordPress.

**No widgets. No jQuery. No bloat — just clean, templated output.**

[![Version](https://img.shields.io/badge/stable-v1.5-blue.svg)](https://wordpress.org/plugins/init-recent-comments/)
[![License](https://img.shields.io/badge/license-GPLv2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
![Made with ❤️ in HCMC](https://img.shields.io/badge/Made%20with-%E2%9D%A4%EF%B8%8F%20in%20HCMC-blue)

## Overview

**Init Recent Comments** is a lightweight, developer-oriented plugin that displays the latest comments (and reviews) anywhere on your site through a simple shortcode.

Built with flexibility and simplicity in mind — clean markup, minimal CSS, and template overrides directly from your theme.

Ideal for blogs, news sites, or any project that values both performance and clean design.

## Features

- Shortcode: `[init_plugin_suite_recent_comments]` (legacy alias: `[init_recent_comments]`)
- Shortcode for recent reviews: `[init_plugin_suite_recent_reviews]` (legacy alias: `[init_recent_reviews]`)
- Shortcode for a specific user's recent comments: `[init_plugin_suite_user_recent_comments]` (legacy alias: `[init_user_recent_comments]`)
- Shortcode for a specific user's recent reviews: `[init_plugin_suite_user_recent_reviews]` (legacy alias: `[init_user_recent_reviews]`)
- Template-based rendering (`comment-item.php`, `wrapper.php`, `review-item.php`, `review-wrapper.php`)
- Disable built-in CSS and use your own styling
- No widgets, no jQuery, no frontend dependencies
- Developer-ready — filters, REST endpoints, and lazy loading (planned)
- Translation-ready (`.pot` file included)

## Usage

Use the shortcode anywhere:

```shortcode
[init_plugin_suite_recent_comments number="5" maxheight="400px"]
```

Recent reviews:

```shortcode
[init_plugin_suite_recent_reviews number="5"]
```

Recent comments of a specific user:

```shortcode
[init_plugin_suite_user_recent_comments user_id="123"]
```

Recent reviews of a specific user:

```shortcode
[init_plugin_suite_user_recent_reviews user_id="123"]
```

The legacy tags `[init_recent_comments]`, `[init_recent_reviews]`, `[init_user_recent_comments]`, and `[init_user_recent_reviews]` still work exactly the same way, for backward compatibility with existing content.

**Attributes:**

- `number` – Total items to display (default: 5)
- `maxheight` – Optional scrollable height (e.g. `300px`)
- `paged` – Optional pagination
- `theme` – Light or dark (e.g. `theme="dark"`)
- For user shortcodes: `user_id`, `user_login`, or `user_email` can be used to target the user

**Template overrides:**

Place custom templates inside your theme:

```
your-theme/
└── init-recent-comments/
    ├── wrapper.php
    ├── review-wrapper.php
    ├── comment-item.php
    └── review-item.php
```

## Developer Filters

This plugin provides multiple filters for customizing cache duration and performance of comment/review queries.

By default, comment and review queries are **not cached** (TTL = 0) so newly posted comments always show up immediately. Total comment count queries default to a 5-minute TTL instead, since counts are less time-sensitive. You can enable caching for the other queries yourself via the filters below if your site has heavy traffic and can tolerate a short delay.

| Filter | Description | Applies To | Params |
|---------|-------------|-------------|---------|
| `init_plugin_suite_recent_comments_ttl` | Control TTL (in seconds) for recent comments | Recent Comments Query | `int $ttl` |
| `init_plugin_suite_recent_comments_query_args` | Allow developers to modify/extend the WP_Comment query args before execution | Recent Comments Query | `array $args` |
| `init_plugin_suite_recent_reviews_ttl` | Control TTL (in seconds) for recent reviews | Recent Reviews Query | `int $ttl` |
| `init_plugin_suite_user_recent_comments_ttl` | Control TTL (in seconds) for a specific user's recent comments | `[init_user_recent_comments]` | `int $ttl`, `array $args` |
| `init_plugin_suite_user_recent_reviews_ttl` | Control TTL (in seconds) for a specific user's recent reviews | `[init_user_recent_reviews]` | `int $ttl`, `array $args` |
| `init_plugin_suite_total_comments_ttl` | Control TTL (in seconds) for total approved comment counts by post type | Total Comments Query | `int $ttl`, `array $post_types` |
| `init_plugin_suite_total_by_posts_ttl` | Control TTL (in seconds) for total comments across multiple post IDs | Total by Post IDs Query | `int $ttl`, `array $post_ids` |

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate it under **Plugins → Init Recent Comments**
3. Insert `[init_plugin_suite_recent_comments]` in any page, post, or widget
4. (Optional) Disable built-in CSS via **Settings → Init Recent Comments**

## License

GPLv2 or later — open source, minimal, developer-first.

## Part of Init Plugin Suite

Init Recent Comments is part of the [Init Plugin Suite](https://en.inithtml.com/init-plugin-suite-minimalist-powerful-and-free-wordpress-plugins/) — a collection of blazing-fast, no-bloat plugins made for WordPress developers who care about quality and speed.
