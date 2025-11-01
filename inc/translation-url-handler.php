<?php
/**
 * Translation URL Handler
 * Handles SEO-friendly translation URLs like /post-slug/tr/, /post-slug/es/
 *
 * @package Gufte
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Language code mapping
 */
function gufte_get_language_codes() {
    return array(
        'en' => 'English',
        'es' => 'Spanish',
        'tr' => 'Turkish',
        'fr' => 'French',
        'de' => 'German',
        'it' => 'Italian',
        'pt' => 'Portuguese',
        'ar' => 'Arabic',
        'ja' => 'Japanese',
        'ko' => 'Korean',
    );
}

/**
 * Add rewrite rules for translation URLs
 */
function gufte_add_translation_rewrite_rules() {
    $lang_codes = array_keys(gufte_get_language_codes());
    $lang_pattern = implode('|', $lang_codes);

    // Add rewrite rule for: /lyrics/post-slug/LANG/
    add_rewrite_rule(
        '^lyrics/([^/]+)/(' . $lang_pattern . ')/?$',
        'index.php?lyrics=$matches[1]&translation_lang=$matches[2]',
        'top'
    );
}
add_action('init', 'gufte_add_translation_rewrite_rules');

/**
 * Add query var for translation language
 */
function gufte_add_translation_query_var($vars) {
    $vars[] = 'translation_lang';
    return $vars;
}
add_filter('query_vars', 'gufte_add_translation_query_var');

/**
 * Get current translation language from URL
 */
function gufte_get_current_translation_lang() {
    return get_query_var('translation_lang', '');
}

/**
 * Get available translations from block content
 */
function gufte_get_block_translations($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $post = get_post($post_id);
    if (!$post) {
        return array();
    }

    $translations = array();

    // Parse blocks to find lyrics-translations block
    if (has_blocks($post->post_content)) {
        $blocks = parse_blocks($post->post_content);

        foreach ($blocks as $block) {
            if ($block['blockName'] === 'arcuras/lyrics-translations' && !empty($block['attrs']['languages'])) {
                foreach ($block['attrs']['languages'] as $lang) {
                    if (!empty($lang['code']) && !empty($lang['lyrics'])) {
                        $translations[$lang['code']] = array(
                            'name' => $lang['name'],
                            'lyrics' => $lang['lyrics']
                        );
                    }
                }
                break; // Only process first lyrics block
            }
        }
    }

    return $translations;
}

/**
 * Get translation URL for a specific language
 */
function gufte_get_translation_url($post_id, $lang_code) {
    $permalink = get_permalink($post_id);

    if (empty($lang_code)) {
        return $permalink;
    }

    // Remove trailing slash and add language code
    $permalink = untrailingslashit($permalink);
    return $permalink . '/' . $lang_code . '/';
}

/**
 * Modify post content to show specific translation
 */
function gufte_filter_translation_content($content) {
    if (!is_singular(array('post', 'lyrics'))) {
        return $content;
    }

    $lang = gufte_get_current_translation_lang();
    if (empty($lang)) {
        return $content;
    }

    $translations = gufte_get_block_translations();

    if (empty($translations[$lang])) {
        return $content;
    }

    // We don't need to modify content here
    // The frontend JavaScript will handle tab switching based on URL
    return $content;
}
add_filter('the_content', 'gufte_filter_translation_content', 999);

/**
 * Add hreflang tags for SEO
 */
function gufte_add_translation_hreflang() {
    if (!is_singular(array('post', 'lyrics'))) {
        return;
    }

    $post_id = get_the_ID();
    $translations = gufte_get_block_translations($post_id);

    if (empty($translations)) {
        return;
    }

    // Original URL (without language code)
    $original_url = get_permalink($post_id);
    echo '<link rel="alternate" hreflang="x-default" href="' . esc_url($original_url) . '" />' . "\n";

    // Translation URLs
    foreach ($translations as $code => $data) {
        // English is the original - use base URL without /en/
        if (strtolower($code) === 'en') {
            echo '<link rel="alternate" hreflang="en" href="' . esc_url($original_url) . '" />' . "\n";
        } else {
            $trans_url = gufte_get_translation_url($post_id, $code);
            echo '<link rel="alternate" hreflang="' . esc_attr($code) . '" href="' . esc_url($trans_url) . '" />' . "\n";
        }
    }
}
add_action('wp_head', 'gufte_add_translation_hreflang');

/**
 * Flush rewrite rules on theme activation
 */
function gufte_flush_translation_rewrites() {
    gufte_add_translation_rewrite_rules();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'gufte_flush_translation_rewrites');
