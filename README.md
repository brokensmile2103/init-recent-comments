# Init Recent Comments – Templated, Modern, Minimal

> Display recent comments using clean templates and minimal CSS, with Block Editor & Abilities API support. Developer-friendly, fast, and built for modern WordPress.

**No widgets. No jQuery. No bloat — just clean, templated output.**

[![Version](https://img.shields.io/badge/stable-v2.0.0-blue.svg)](https://wordpress.org/plugins/init-recent-comments/)
[![License](https://img.shields.io/badge/license-GPLv2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
![Made with ❤️ in HCMC](https://img.shields.io/badge/Made%20with-%E2%9D%A4%EF%B8%8F%20in%20HCMC-blue)

## Overview

**Init Recent Comments** is a lightweight, developer-oriented plugin that displays the latest comments (and reviews) anywhere on your site — through a simple shortcode, or natively as a Block Editor block.

Built with flexibility and simplicity in mind — clean markup, minimal CSS, and template overrides directly from your theme.

Ideal for blogs, news sites, or any project that values both performance and clean design.

## What's New in v2.0.0

- **Block Editor (Gutenberg) support**: four dynamic blocks — Recent Comments, Recent Reviews, User Recent Comments, User Recent Reviews — grouped under their own **Init Recent Comments** category in the block inserter. Each block is registered via `block.json` with a PHP `render.php` that calls the exact same shortcode function as its shortcode counterpart, so output never diverges. A single no-build-step vanilla JS file powers the editor integration, with `wp.serverSideRender` for live preview
- **Abilities API support (WordPress 6.9+)**: registers four read-only abilities — `get-recent-comments`, `get-recent-reviews`, `get-user-recent-comments`, `get-user-recent-reviews` — mapping 1:1 to all four shortcodes. This plugin has no write actions at all (it's a pure display plugin), so all of its functionality is safely exposed. Discoverable and executable via PHP, `wp_get_abilities()`, and the `wp-abilities/v1` REST namespace when a site opts in. Review-related abilities gracefully return an empty list if Init Review System isn't active. Fully optional: on WordPress versions older than 6.9, this silently does nothing
- **Requires at least** raised from 5.5 to 6.9 to support the Abilities API. `Requires PHP` stays at 7.4

## Features

- Block Editor (Gutenberg) blocks: Recent Comments, Recent Reviews, User Recent Comments, User Recent Reviews — each with a live server-side preview in the editor
- Abilities API (WordPress 6.9+): all 4 shortcodes' data exposed as discoverable, executable abilities
- Shortcode: `[init_plugin_suite_recent_comments]` (legacy alias: `[init_recent_comments]`)
- Shortcode for recent reviews: `[init_plugin_suite_recent_reviews]` (legacy alias: `[init_recent_reviews]`)
- Shortcode for a specific user's recent comments: `[init_plugin_suite_user_recent_comments]` (legacy alias: `[init_user_recent_comments]`)
- Shortcode for a specific user's recent reviews: `[init_plugin_suite_user_recent_reviews]` (legacy alias: `[init_user_recent_reviews]`)
- Template-based rendering (`comment-item.php`, `wrapper.php`, `review-item.php`, `review-wrapper.php`)
- Disable built-in CSS and use your own styling
- No widgets, no jQuery, no frontend dependencies
- Developer-ready — filters, REST endpoints, and lazy loading (planned)
- Translation-ready (`.pot` file included)

## Block Editor (Gutenberg)

Four dynamic blocks are available under their own **Init Recent Comments** category in the block inserter — no shortcodes needed if you prefer working entirely in the editor:

| Block | Equivalent shortcode |
|---|---|
| **Recent Comments** | `[init_plugin_suite_recent_comments]` |
| **Recent Reviews** | `[init_plugin_suite_recent_reviews]` |
| **User Recent Comments** | `[init_plugin_suite_user_recent_comments]` |
| **User Recent Reviews** | `[init_plugin_suite_user_recent_reviews]` |

Each block shares the exact same rendering code as its shortcode, so switching between the Block Editor and shortcodes never changes the output. Block settings map directly to shortcode attributes, and a live preview is shown right in the editor as you configure it.

## Abilities API (WordPress 6.9+)

On WordPress 6.9 and above, Init Recent Comments registers four read-only abilities under the `init-recent-comments` category, mirroring the plugin's four shortcodes exactly:

- `init-recent-comments/get-recent-comments` — most recent approved comments site-wide
- `init-recent-comments/get-recent-reviews` — most recent approved reviews site-wide (requires Init Review System)
- `init-recent-comments/get-user-recent-comments` — most recent approved comments by a specific user
- `init-recent-comments/get-user-recent-reviews` — most recent reviews submitted by a specific user (requires Init Review System)

This plugin has no write actions at all — it's a pure display plugin — so all of its functionality can be safely exposed without exclusions. These abilities are discoverable and executable via PHP (`wp_get_abilities()`), and — for sites that opt in — the `wp-abilities/v1` REST namespace. This integration is fully optional: on WordPress versions older than 6.9, it silently does nothing.

## Usage

Prefer working in the Block Editor? Each shortcode below has an equivalent block — see [Block Editor (Gutenberg)](#block-editor-gutenberg) above.

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
3. Insert `[init_plugin_suite_recent_comments]` in any page, post, or widget — or add the equivalent block in the Block Editor
4. (Optional) Disable built-in CSS via **Settings → Init Recent Comments**

## Requirements

- WordPress 6.9 or later (raised from 5.5 in v2.0.0, to support the Abilities API integration)
- PHP 7.4 or later

## License

GPLv2 or later — open source, minimal, developer-first.

## Part of Init Plugin Suite

Init Recent Comments is part of the [Init Plugin Suite](https://en.inithtml.com/init-plugin-suite-minimalist-powerful-and-free-wordpress-plugins/) — a collection of blazing-fast, no-bloat plugins made for WordPress developers who care about quality and speed.
