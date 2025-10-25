=== Init Recent Comments – Templated, Modern, Minimal ===
Contributors: brokensmile.2103  
Tags: comments, recent comments, widget, shortcode, template  
Requires at least: 5.5  
Tested up to: 6.8  
Requires PHP: 7.4  
Stable tag: 1.2
License: GPLv2 or later  
License URI: https://www.gnu.org/licenses/gpl-2.0.html  

Display recent comments with customizable templates and clean CSS. Lightweight, flexible, and built for modern WordPress sites.

== Description ==

**Init Recent Comments** is a developer-friendly plugin that lets you display the latest comments anywhere via a simple shortcode. It uses clean HTML, minimal CSS, and fully customizable templates.

No widgets, no bloated options. Just lightweight, extendable code — made for real sites.

Key design goals:

- Templated rendering with override support from themes
- Clean CSS you can disable or replace
- REST-ready architecture (future-proof)
- No jQuery, no nonsense

Perfect for blogs, news sites, or anyone who wants a better way to show active discussions.

This plugin is part of the [Init Plugin Suite](https://en.inithtml.com/init-plugin-suite-minimalist-powerful-and-free-wordpress-plugins/) — a collection of minimalist, fast, and developer-focused tools for WordPress.

== Features ==

- Simple shortcode: `[init_recent_comments]`
- Template-based rendering (`comment-item.php`, `wrapper.php`)
- CSS can be disabled to use your theme's design
- No widgets, no jQuery, no frontend bloat
- Developer-ready: extend with filters, REST, and lazy loading (planned)
- Translation-ready (`.pot` included)

== Usage ==

Use the shortcode anywhere:

`[init_recent_comments number="5" maxheight="400px"]`

Attributes:

- `number` – Total comments to show (default: 5)
- `maxheight` – Optional max height with scroll and hidden scrollbar (example: `300px`)

To override templates, create the folder in your theme:

    your-theme/
    └── init-recent-comments/
        ├── wrapper.php
        └── comment-item.php

== Filters for Developers ==

This plugin provides multiple filters to help developers customize caching behavior and performance for recent comments, reviews, and total comment count queries.

**`init_plugin_suite_recent_comments_ttl`**  
Control the cache TTL (in seconds) for recent comments.  
**Applies to:** Recent Comments Query  
**Params:** `int $ttl`

**`init_plugin_suite_recent_reviews_ttl`**  
Control the cache TTL (in seconds) for recent reviews.  
**Applies to:** Recent Reviews Query  
**Params:** `int $ttl`

**`init_plugin_suite_total_comments_ttl`**  
Control the cache TTL (in seconds) for total approved comment counts by post type.  
**Applies to:** Total Comments Query  
**Params:** `int $ttl`, `array $post_types`

**`init_plugin_suite_total_by_posts_ttl`**  
Control the cache TTL (in seconds) for total approved comment counts across multiple post IDs.  
**Applies to:** Total by Post IDs Query  
**Params:** `int $ttl`, `array $post_ids`

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate via **Plugins → Init Recent Comments**
3. Use the shortcode `[init_recent_comments]` in any page or widget
4. Optional: Visit **Settings → Init Recent Comments** to disable built-in CSS

== Screenshots ==

1. Settings page with CSS toggle

== Frequently Asked Questions ==

= Can I disable the plugin’s CSS? =  
Yes! Go to **Settings → Init Recent Comments** and check the box to disable built-in styling.

= Can I customize the comment HTML? =  
Absolutely. Copy `templates/comment-item.php` and `templates/wrapper.php` to your theme to override the output.

= Will this plugin slow down my site? =  
No. It uses `get_comments()` with sane defaults, no extra queries, no JavaScript.

== Changelog ==

= 1.2 – October 12, 2025 =
- Enhanced caching flexibility for existing functions:
  - Added TTL filter support to init_plugin_suite_recent_comments_get_comments()
  - Added TTL filter support to init_plugin_suite_recent_comments_get_reviews()
  - Added TTL filter support to init_plugin_suite_recent_comments_get_total_comments()
- Introduced new helper function init_plugin_suite_recent_comments_get_total_by_posts() for counting total approved comments across multiple post IDs
- Separated cache group for comment totals (`init_comment_totals`) to improve cache isolation and performance
- Developers can now customize cache durations via filters
- Default TTL values remain unchanged (0 for comments/reviews, 5 minutes for totals)
- Minor internal optimizations for stability and consistency

= 1.1 – October 1, 2025 =  
- Added new shortcode `[init_recent_reviews]` for displaying recent reviews  
- Introduced prefixed shortcodes `[init_plugin_suite_recent_comments]` and `[init_plugin_suite_recent_reviews]` for better naming consistency  
- Maintained backward compatibility: old shortcodes `[init_recent_comments]` and `[init_recent_reviews]` still work  
- Added template override support for review wrapper (`init-recent-comments/review-wrapper.php`)  
- Unified CSS handling and options across both comments and reviews shortcodes  

= 1.0 – June 16, 2025 =  
- Initial release  
- Static shortcode `[init_recent_comments]`  
- Basic settings page to toggle CSS  
- Template override support  
- Clean CSS with disable option

== License ==

This plugin is licensed under the GPLv2 or later.  
You are free to use, modify, and distribute it under the same license.
