<?php
/**
 * Lyrics Translations Meta Box
 * Auto-detects available translations from lyrics block and displays them
 *
 * @package Arcuras
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add translations meta box
 */
function arcuras_add_translations_meta_box() {
    add_meta_box(
        'arcuras_translations_meta',
        __('Available Translations', 'gufte'),
        'arcuras_render_translations_meta_box',
        'lyrics',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'arcuras_add_translations_meta_box');

/**
 * Render translations meta box
 */
function arcuras_render_translations_meta_box($post) {
    // Get translations from lyrics block
    $translations = arcuras_get_post_translations($post->ID);

    if (empty($translations)) {
        echo '<p style="color: #666; font-style: italic;">' . __('No translations detected. Add lyrics with translations using the Lyrics & Translations block.', 'gufte') . '</p>';
        return;
    }

    echo '<div class="arcuras-translations-list" style="display: flex; flex-direction: column; gap: 8px;">';

    foreach ($translations as $lang) {
        $is_original = isset($lang['isOriginal']) && $lang['isOriginal'];
        $line_count = count(explode("\n", $lang['lyrics']));

        $bg_color = $is_original ? '#667eea' : '#f3f4f6';
        $text_color = $is_original ? '#ffffff' : '#1f2937';
        $border = $is_original ? 'none' : '1px solid #e5e7eb';

        echo '<div style="
            padding: 12px;
            background: ' . esc_attr($bg_color) . ';
            color: ' . esc_attr($text_color) . ';
            border: ' . esc_attr($border) . ';
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        ">';

        echo '<div style="display: flex; flex-direction: column; gap: 4px;">';
        echo '<strong style="font-size: 14px;">' . esc_html($lang['name']) . '</strong>';

        if ($is_original) {
            echo '<span style="
                font-size: 10px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                opacity: 0.9;
                font-weight: 600;
            ">Original</span>';
        }

        echo '</div>';

        echo '<span style="
            font-size: 12px;
            opacity: 0.8;
        ">' . sprintf(_n('%d line', '%d lines', $line_count, 'gufte'), $line_count) . '</span>';

        echo '</div>';
    }

    echo '</div>';

    // Add info text
    echo '<p style="margin-top: 12px; font-size: 12px; color: #666; font-style: italic;">';
    echo __('Translations are automatically detected from the Lyrics & Translations block.', 'gufte');
    echo '</p>';
}

/**
 * Get translations from post content
 */
function arcuras_get_post_translations($post_id) {
    $post = get_post($post_id);

    if (!$post || !has_blocks($post->post_content)) {
        return array();
    }

    $blocks = parse_blocks($post->post_content);

    foreach ($blocks as $block) {
        if ($block['blockName'] === 'arcuras/lyrics-translations' && !empty($block['attrs']['languages'])) {
            return $block['attrs']['languages'];
        }
    }

    return array();
}

/**
 * Save translations as post meta when post is saved
 * This allows for easier querying and filtering
 */
function arcuras_save_translations_meta($post_id) {
    // Check if this is an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check post type
    $post_type = get_post_type($post_id);
    if ($post_type !== 'lyrics' && $post_type !== 'post') {
        return;
    }

    // Get translations
    $translations = arcuras_get_post_translations($post_id);

    if (empty($translations)) {
        delete_post_meta($post_id, '_available_translations');
        delete_post_meta($post_id, '_original_language');
        return;
    }

    // Extract language codes
    $lang_codes = array();
    $original_langs = array(); // Support multiple original languages

    foreach ($translations as $lang) {
        $lang_codes[] = $lang['code'];

        if (isset($lang['isOriginal']) && $lang['isOriginal']) {
            $original_langs[] = $lang['code'];
        }
    }

    // Save as post meta
    update_post_meta($post_id, '_available_translations', $lang_codes);
    update_post_meta($post_id, '_available_languages', $lang_codes); // Also save as _available_languages for taxonomy system

    // Save original languages as array (backwards compatible - if only 1, save as string)
    if (count($original_langs) === 1) {
        update_post_meta($post_id, '_original_language', $original_langs[0]);
    } elseif (count($original_langs) > 1) {
        update_post_meta($post_id, '_original_language', $original_langs);
    } else {
        update_post_meta($post_id, '_original_language', '');
    }

    // Trigger language taxonomy assignment after meta is saved
    if (function_exists('arcuras_auto_assign_language_terms')) {
        arcuras_auto_assign_language_terms($post_id);
    }

    // Trigger custom sitemap regeneration
    // WordPress core sitemap gets updated automatically on post save
    // but we can trigger custom actions if needed
    do_action('arcuras_lyrics_updated', $post_id);
}
add_action('save_post', 'arcuras_save_translations_meta', 10, 1);
add_action('save_post_lyrics', 'arcuras_save_translations_meta', 10, 1);

// Also hook into REST API for Gutenberg editor
add_action('rest_after_insert_lyrics', 'arcuras_save_translations_meta_rest', 10, 2);
add_action('rest_after_insert_post', 'arcuras_save_translations_meta_rest', 10, 2);

/**
 * Handle translations update from REST API (Gutenberg editor)
 */
function arcuras_save_translations_meta_rest($post, $request) {
    arcuras_save_translations_meta($post->ID);
}

/**
 * Add admin column for translations
 * DISABLED: We now use Original Languages and Translated Languages taxonomies instead
 */
/*
function arcuras_add_translations_column($columns) {
    $new_columns = array();

    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;

        // Add translations column after title
        if ($key === 'title') {
            $new_columns['translations'] = '<span class="dashicons dashicons-translation"></span> ' . __('Translations', 'gufte');
        }
    }

    return $new_columns;
}
add_filter('manage_lyrics_posts_columns', 'arcuras_add_translations_column');
*/

/**
 * Render translations column content
 * DISABLED: We now use Original Languages and Translated Languages taxonomies instead
 */
/*
function arcuras_render_translations_column($column, $post_id) {
    if ($column === 'translations') {
        $translations = arcuras_get_post_translations($post_id);

        if (empty($translations)) {
            echo '<span style="color: #999;">—</span>';
            return;
        }

        $flags = array(
            'en' => '🇬🇧',
            'es' => '🇪🇸',
            'tr' => '🇹🇷',
            'fr' => '🇫🇷',
            'de' => '🇩🇪',
            'it' => '🇮🇹',
            'pt' => '🇵🇹',
            'ar' => '🇸🇦',
            'ja' => '🇯🇵',
            'ko' => '🇰🇷',
            'ru' => '🇷🇺'
        );

        echo '<div style="display: flex; gap: 4px; align-items: center; flex-wrap: wrap;">';

        foreach ($translations as $lang) {
            $flag = isset($flags[$lang['code']]) ? $flags[$lang['code']] : '🌐';
            $is_original = isset($lang['isOriginal']) && $lang['isOriginal'];

            echo '<span style="
                font-size: 18px;
                line-height: 1;
                ' . ($is_original ? 'border: 2px solid #667eea; border-radius: 4px; padding: 2px;' : '') . '
            " title="' . esc_attr($lang['name']) . ($is_original ? ' (Original)' : '') . '">' . $flag . '</span>';
        }

        echo '</div>';
    }
}
add_action('manage_lyrics_posts_custom_column', 'arcuras_render_translations_column', 10, 2);
*/

/**
 * Make translations column sortable
 * DISABLED: We now use Original Languages and Translated Languages taxonomies instead
 */
/*
function arcuras_make_translations_column_sortable($columns) {
    $columns['translations'] = 'translations';
    return $columns;
}
add_filter('manage_edit-lyrics_sortable_columns', 'arcuras_make_translations_column_sortable');
*/
