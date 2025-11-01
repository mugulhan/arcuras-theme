<?php
/**
 * WordPress function stubs for static analysis (Intelephense).
 *
 * Bu dosya yalnızca IDE yardımı içindir; WordPress çekirdeği çalışma zamanında
 * gerçek tanımları sağlar. Uygulamada yüklenmez.
 */

if (!defined('ARRAY_A')) {
    define('ARRAY_A', 'ARRAY_A');
}

if (!class_exists('WP_Post')) {
    class WP_Post {}
}

if (!class_exists('WP_Error')) {
    class WP_Error {}
}

if (!function_exists('esc_html_e')) {
    function esc_html_e(string $text, string $domain = 'default'): void {}
}

if (!function_exists('esc_html__')) {
    function esc_html__(string $text, string $domain = 'default'): string { return $text; }
}

if (!function_exists('esc_attr_e')) {
    function esc_attr_e(string $text, string $domain = 'default'): void {}
}

if (!function_exists('esc_html')) {
    function esc_html(string $text): string { return $text; }
}

if (!function_exists('esc_url')) {
    function esc_url(string $url, array $protocols = null, string $_context = 'display'): string { return $url; }
}

if (!function_exists('wp_kses')) {
    function wp_kses(string $string, array $allowed_html, array $allowed_protocols = []): string { return $string; }
}

if (!function_exists('is_active_sidebar')) {
    function is_active_sidebar(string|int $index): bool { return false; }
}

if (!function_exists('dynamic_sidebar')) {
    function dynamic_sidebar(string|int $index): bool { return false; }
}

if (!function_exists('wp_nav_menu')) {
    function wp_nav_menu(array $args = []): void {}
}

if (!function_exists('wp_get_recent_posts')) {
    function wp_get_recent_posts(array $args = [], int $output = ARRAY_A): array { return []; }
}

if (!function_exists('get_permalink')) {
    function get_permalink(int|\WP_Post $post = 0, bool $leavename = false): string { return ''; }
}

if (!function_exists('wp_reset_postdata')) {
    function wp_reset_postdata(): void {}
}

if (!function_exists('home_url')) {
    function home_url(string $path = '', string|null $scheme = null): string { return '/'; }
}

if (!function_exists('bloginfo')) {
    function bloginfo(string $show = '', string $filter = 'raw'): void {}
}

if (!function_exists('get_bloginfo')) {
    function get_bloginfo(string $show = '', string $filter = 'raw'): string { return ''; }
}

if (!function_exists('date_i18n')) {
    function date_i18n(string $format, int|bool $timestamp = false, bool $gmt = false): string { return ''; }
}

if (!function_exists('is_single')) {
    function is_single(int|string|\WP_Post|null $post = null): bool { return false; }
}

if (!function_exists('get_post_meta')) {
    function get_post_meta(int $post_id, string $key = '', bool $single = false): mixed { return null; }
}

if (!function_exists('get_the_ID')) {
    function get_the_ID(): int { return 0; }
}

if (!function_exists('has_post_thumbnail')) {
    function has_post_thumbnail(int|\WP_Post|null $post = null): bool { return false; }
}

if (!function_exists('the_post_thumbnail')) {
    function the_post_thumbnail(string|int $size = 'post-thumbnail', array|string $attr = ''): void {}
}

if (!function_exists('get_the_title')) {
    function get_the_title(int|\WP_Post $post = 0): string { return ''; }
}

if (!function_exists('the_title')) {
    function the_title(string $before = '', string $after = '', bool $echo = true): string { return ''; }
}

if (!function_exists('get_the_terms')) {
    function get_the_terms(int|\WP_Post $post, string $taxonomy): array|false|\WP_Error { return []; }
}

if (!function_exists('is_wp_error')) {
    function is_wp_error(mixed $thing): bool { return false; }
}

if (!function_exists('wp_footer')) {
    function wp_footer(): void {}
}
