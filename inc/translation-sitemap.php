<?php
/**
 * Translation Sitemap Handler
 * Adds translation URLs to WordPress sitemap
 *
 * @package Arcuras
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add translation URLs to sitemap
 * Note: arcuras_get_post_translations() is defined in lyrics-translations-meta.php
 */
function arcuras_add_translation_urls_to_sitemap($url_list, $post_type) {
    // Only for lyrics post type
    if ($post_type !== 'lyrics') {
        return $url_list;
    }

    $new_urls = array();

    foreach ($url_list as $url_item) {
        // Add original URL
        $new_urls[] = $url_item;

        // Get post ID from loc
        $post_id = url_to_postid($url_item['loc']);

        if (!$post_id) {
            continue;
        }

        // Get translations for this post
        $translations = arcuras_get_post_translations($post_id);

        if (empty($translations)) {
            continue;
        }

        // Add URL for each translation
        foreach ($translations as $lang) {
            // Skip if no language code
            if (empty($lang['code'])) {
                continue;
            }

            // Skip original language
            if (isset($lang['isOriginal']) && $lang['isOriginal']) {
                continue;
            }

            // Check if translation has content
            if (empty($lang['lyrics']) || trim($lang['lyrics']) === '') {
                continue;
            }

            // Generate translation URL
            $base_url = get_permalink($post_id);
            $translation_url = trailingslashit($base_url) . $lang['code'] . '/';

            $new_urls[] = array(
                'loc' => $translation_url,
                'lastmod' => $url_item['lastmod'],
                'priority' => 0.8,
                'changefreq' => 'monthly'
            );
        }
    }

    return $new_urls;
}
add_filter('wp_sitemaps_posts_url_list', 'arcuras_add_translation_urls_to_sitemap', 10, 2);

/**
 * Add custom sitemap provider for translations (alternative method)
 */
function arcuras_register_translation_sitemap_provider($provider, $name) {
    // This is an alternative approach if the filter above doesn't work well
    // We can create a completely custom sitemap provider for translations
    return $provider;
}
add_filter('wp_sitemaps_add_provider', 'arcuras_register_translation_sitemap_provider', 10, 2);

/**
 * Modify sitemap max URLs to accommodate translations
 */
function arcuras_increase_sitemap_max_urls($max_urls) {
    // Increase limit to handle multiple translations per post
    return $max_urls * 5; // Allow 5x more URLs for translations
}
add_filter('wp_sitemaps_max_urls', 'arcuras_increase_sitemap_max_urls');

/**
 * Add hreflang to sitemap (for better multilingual SEO)
 */
function arcuras_sitemap_add_hreflang($entry, $post) {
    if ($post->post_type !== 'lyrics' && $post->post_type !== 'post') {
        return $entry;
    }

    $translations = arcuras_get_post_translations($post->ID);

    if (empty($translations)) {
        return $entry;
    }

    // Add hreflang attributes (this would need XML modification)
    // For now, we're adding them via wp_head instead
    return $entry;
}
add_filter('wp_sitemaps_posts_entry', 'arcuras_sitemap_add_hreflang', 10, 2);
