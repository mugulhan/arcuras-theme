<?php
/**
 * Language Taxonomy
 * Register and manage language taxonomy for lyrics
 *
 * @package Arcuras
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Original Languages taxonomy (like categories)
 */
function arcuras_register_original_languages_taxonomy() {
    $labels = array(
        'name'              => _x('Original Languages', 'taxonomy general name', 'gufte'),
        'singular_name'     => _x('Original Language', 'taxonomy singular name', 'gufte'),
        'search_items'      => __('Search Original Languages', 'gufte'),
        'all_items'         => __('All Original Languages', 'gufte'),
        'parent_item'       => __('Parent Language', 'gufte'),
        'parent_item_colon' => __('Parent Language:', 'gufte'),
        'edit_item'         => __('Edit Original Language', 'gufte'),
        'update_item'       => __('Update Original Language', 'gufte'),
        'add_new_item'      => __('Add New Original Language', 'gufte'),
        'new_item_name'     => __('New Original Language Name', 'gufte'),
        'menu_name'         => __('Original Languages', 'gufte'),
    );

    $args = array(
        'hierarchical'      => true, // Like categories
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'query_var'         => true,
        'rewrite'           => array(
            'slug' => 'lyrics/original',
            'with_front' => false,
        ),
        'public'            => true,
        'show_in_nav_menus' => true,
        'show_tagcloud'     => false,
    );

    register_taxonomy('original_language', array('lyrics'), $args);
}
add_action('init', 'arcuras_register_original_languages_taxonomy', 0);

/**
 * Register Translated Languages taxonomy (like tags)
 */
function arcuras_register_translated_languages_taxonomy() {
    $labels = array(
        'name'                       => _x('Translated Languages', 'taxonomy general name', 'gufte'),
        'singular_name'              => _x('Translated Language', 'taxonomy singular name', 'gufte'),
        'search_items'               => __('Search Translated Languages', 'gufte'),
        'popular_items'              => __('Popular Translated Languages', 'gufte'),
        'all_items'                  => __('All Translated Languages', 'gufte'),
        'edit_item'                  => __('Edit Translated Language', 'gufte'),
        'update_item'                => __('Update Translated Language', 'gufte'),
        'add_new_item'               => __('Add New Translated Language', 'gufte'),
        'new_item_name'              => __('New Translated Language Name', 'gufte'),
        'separate_items_with_commas' => __('Separate languages with commas', 'gufte'),
        'add_or_remove_items'        => __('Add or remove languages', 'gufte'),
        'choose_from_most_used'      => __('Choose from the most used languages', 'gufte'),
        'not_found'                  => __('No translated languages found.', 'gufte'),
        'menu_name'                  => __('Translated Languages', 'gufte'),
    );

    $args = array(
        'hierarchical'      => false, // Like tags
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'query_var'         => true,
        'rewrite'           => array(
            'slug' => 'lyrics/translation',
            'with_front' => false,
        ),
        'public'            => true,
        'show_in_nav_menus' => true,
        'show_tagcloud'     => true,
    );

    register_taxonomy('translated_language', array('lyrics'), $args);
}
add_action('init', 'arcuras_register_translated_languages_taxonomy', 0);

/**
 * Add custom rewrite rules for language archive pages
 */
function arcuras_language_archive_rewrite_rules() {
    add_rewrite_rule(
        '^lyrics/original/?$',
        'index.php?arcuras_language_archive=original',
        'top'
    );

    add_rewrite_rule(
        '^lyrics/translation/?$',
        'index.php?arcuras_language_archive=translation',
        'top'
    );
}
add_action('init', 'arcuras_language_archive_rewrite_rules');

/**
 * Add query vars for language archives
 */
function arcuras_language_archive_query_vars($vars) {
    $vars[] = 'arcuras_language_archive';
    return $vars;
}
add_filter('query_vars', 'arcuras_language_archive_query_vars');

/**
 * Template redirect for language archives
 */
function arcuras_language_archive_template_redirect() {
    $archive_type = get_query_var('arcuras_language_archive');

    if ($archive_type === 'original') {
        include(get_template_directory() . '/page-templates/template-lyrics-original-category.php');
        exit;
    } elseif ($archive_type === 'translation') {
        include(get_template_directory() . '/page-templates/template-lyrics-translation-category.php');
        exit;
    }
}
add_action('template_redirect', 'arcuras_language_archive_template_redirect');

/**
 * Get language term data
 */
function arcuras_get_language_term_data() {
    $default_languages = array(
        'english' => array(
            'name' => 'English',
            'slug' => 'english',
            'description' => 'English language lyrics',
            'flag' => '🇬🇧',
            'iso_code' => 'en'
        ),
        'spanish' => array(
            'name' => 'Spanish',
            'slug' => 'spanish',
            'description' => 'Spanish language lyrics and translations',
            'flag' => '🇪🇸',
            'iso_code' => 'es'
        ),
        'turkish' => array(
            'name' => 'Turkish',
            'slug' => 'turkish',
            'description' => 'Turkish language lyrics and translations',
            'flag' => '🇹🇷',
            'iso_code' => 'tr'
        ),
        'german' => array(
            'name' => 'German',
            'slug' => 'german',
            'description' => 'German language lyrics and translations',
            'flag' => '🇩🇪',
            'iso_code' => 'de'
        ),
        'french' => array(
            'name' => 'French',
            'slug' => 'french',
            'description' => 'French language lyrics and translations',
            'flag' => '🇫🇷',
            'iso_code' => 'fr'
        ),
        'italian' => array(
            'name' => 'Italian',
            'slug' => 'italian',
            'description' => 'Italian language lyrics and translations',
            'flag' => '🇮🇹',
            'iso_code' => 'it'
        ),
        'portuguese' => array(
            'name' => 'Portuguese',
            'slug' => 'portuguese',
            'description' => 'Portuguese language lyrics and translations',
            'flag' => '🇵🇹',
            'iso_code' => 'pt'
        ),
        'arabic' => array(
            'name' => 'Arabic',
            'slug' => 'arabic',
            'description' => 'Arabic language lyrics and translations',
            'flag' => '🇸🇦',
            'iso_code' => 'ar'
        ),
        'russian' => array(
            'name' => 'Russian',
            'slug' => 'russian',
            'description' => 'Russian language lyrics and translations',
            'flag' => '🇷🇺',
            'iso_code' => 'ru'
        ),
        'japanese' => array(
            'name' => 'Japanese',
            'slug' => 'japanese',
            'description' => 'Japanese language lyrics and translations',
            'flag' => '🇯🇵',
            'iso_code' => 'ja'
        ),
        'korean' => array(
            'name' => 'Korean',
            'slug' => 'korean',
            'description' => 'Korean language lyrics and translations',
            'flag' => '🇰🇷',
            'iso_code' => 'ko'
        ),
        'chinese' => array(
            'name' => 'Chinese',
            'slug' => 'chinese',
            'description' => 'Chinese language lyrics and translations',
            'flag' => '🇨🇳',
            'iso_code' => 'zh'
        ),
        'hindi' => array(
            'name' => 'Hindi',
            'slug' => 'hindi',
            'description' => 'Hindi language lyrics and translations',
            'flag' => '🇮🇳',
            'iso_code' => 'hi'
        ),
        'dutch' => array(
            'name' => 'Dutch',
            'slug' => 'dutch',
            'description' => 'Dutch language lyrics and translations',
            'flag' => '🇳🇱',
            'iso_code' => 'nl'
        ),
        'polish' => array(
            'name' => 'Polish',
            'slug' => 'polish',
            'description' => 'Polish language lyrics and translations',
            'flag' => '🇵🇱',
            'iso_code' => 'pl'
        ),
        'swedish' => array(
            'name' => 'Swedish',
            'slug' => 'swedish',
            'description' => 'Swedish language lyrics and translations',
            'flag' => '🇸🇪',
            'iso_code' => 'sv'
        ),
        'norwegian' => array(
            'name' => 'Norwegian',
            'slug' => 'norwegian',
            'description' => 'Norwegian language lyrics and translations',
            'flag' => '🇳🇴',
            'iso_code' => 'no'
        ),
        'danish' => array(
            'name' => 'Danish',
            'slug' => 'danish',
            'description' => 'Danish language lyrics and translations',
            'flag' => '🇩🇰',
            'iso_code' => 'da'
        ),
        'finnish' => array(
            'name' => 'Finnish',
            'slug' => 'finnish',
            'description' => 'Finnish language lyrics and translations',
            'flag' => '🇫🇮',
            'iso_code' => 'fi'
        ),
        'greek' => array(
            'name' => 'Greek',
            'slug' => 'greek',
            'description' => 'Greek language lyrics and translations',
            'flag' => '🇬🇷',
            'iso_code' => 'el'
        ),
        'hebrew' => array(
            'name' => 'Hebrew',
            'slug' => 'hebrew',
            'description' => 'Hebrew language lyrics and translations',
            'flag' => '🇮🇱',
            'iso_code' => 'he'
        ),
        'ukrainian' => array(
            'name' => 'Ukrainian',
            'slug' => 'ukrainian',
            'description' => 'Ukrainian language lyrics and translations',
            'flag' => '🇺🇦',
            'iso_code' => 'uk'
        ),
        'czech' => array(
            'name' => 'Czech',
            'slug' => 'czech',
            'description' => 'Czech language lyrics and translations',
            'flag' => '🇨🇿',
            'iso_code' => 'cs'
        ),
        'romanian' => array(
            'name' => 'Romanian',
            'slug' => 'romanian',
            'description' => 'Romanian language lyrics and translations',
            'flag' => '🇷🇴',
            'iso_code' => 'ro'
        ),
        'hungarian' => array(
            'name' => 'Hungarian',
            'slug' => 'hungarian',
            'description' => 'Hungarian language lyrics and translations',
            'flag' => '🇭🇺',
            'iso_code' => 'hu'
        ),
        'thai' => array(
            'name' => 'Thai',
            'slug' => 'thai',
            'description' => 'Thai language lyrics and translations',
            'flag' => '🇹🇭',
            'iso_code' => 'th'
        ),
        'vietnamese' => array(
            'name' => 'Vietnamese',
            'slug' => 'vietnamese',
            'description' => 'Vietnamese language lyrics and translations',
            'flag' => '🇻🇳',
            'iso_code' => 'vi'
        ),
        'indonesian' => array(
            'name' => 'Indonesian',
            'slug' => 'indonesian',
            'description' => 'Indonesian language lyrics and translations',
            'flag' => '🇮🇩',
            'iso_code' => 'id'
        ),
        'bashkir' => array(
            'name' => 'Bashkir',
            'slug' => 'bashkir',
            'description' => 'Bashkir language lyrics and translations',
            'flag' => '🇷🇺',
            'iso_code' => 'ba'
        ),
    );

    // Get custom languages from database
    $custom_languages = get_option('arcuras_custom_languages', array());

    // Merge default and custom languages
    return array_merge($default_languages, $custom_languages);
}

/**
 * Create default language terms on theme activation
 */
function arcuras_create_default_language_terms() {
    $languages = arcuras_get_language_term_data();

    foreach ($languages as $key => $language) {
        // Create in Original Languages taxonomy
        $term = term_exists($language['slug'], 'original_language');
        if (!$term) {
            wp_insert_term(
                $language['name'],
                'original_language',
                array(
                    'slug' => $language['slug'],
                    'description' => $language['description']
                )
            );
        }

        // Create in Translated Languages taxonomy
        $term = term_exists($language['slug'], 'translated_language');
        if (!$term) {
            wp_insert_term(
                $language['name'],
                'translated_language',
                array(
                    'slug' => $language['slug'],
                    'description' => $language['description']
                )
            );
        }
    }
}
add_action('after_switch_theme', 'arcuras_create_default_language_terms');

/**
 * Extract language data from lyrics block content
 */
function arcuras_extract_languages_from_block($post_id) {
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'lyrics') {
        return array('original' => array(), 'translations' => array());
    }

    $content = $post->post_content;

    // Check if content has blocks
    if (!has_blocks($content)) {
        return array('original' => array(), 'translations' => array());
    }

    $blocks = parse_blocks($content);
    $original_langs = array();
    $translation_langs = array();

    foreach ($blocks as $block) {
        // Check if this is the lyrics-translations block
        if ($block['blockName'] === 'arcuras/lyrics-translations' && !empty($block['attrs']['languages'])) {
            $languages = $block['attrs']['languages'];

            foreach ($languages as $lang) {
                if (empty($lang['code'])) {
                    continue;
                }

                $lang_code = $lang['code'];

                // Check if this is marked as original
                if (isset($lang['isOriginal']) && $lang['isOriginal'] === true) {
                    $original_langs[] = $lang_code;
                } else {
                    $translation_langs[] = $lang_code;
                }
            }

            // We found the block, no need to continue
            break;
        }
    }

    return array(
        'original' => array_unique($original_langs),
        'translations' => array_unique($translation_langs)
    );
}

/**
 * Auto-assign language terms when post is saved
 */
function arcuras_auto_assign_language_terms($post_id) {
    // Skip autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check post type
    $post_type = get_post_type($post_id);
    if ($post_type !== 'lyrics') {
        return;
    }

    // Extract languages from block content
    $extracted_langs = arcuras_extract_languages_from_block($post_id);

    // Update meta fields with extracted data
    if (!empty($extracted_langs['original'])) {
        update_post_meta($post_id, '_original_language', $extracted_langs['original']);
    }
    if (!empty($extracted_langs['translations'])) {
        update_post_meta($post_id, '_available_languages', $extracted_langs['translations']);
    }

    // Get original language and available languages (now updated from block)
    $original_language = !empty($extracted_langs['original']) ? $extracted_langs['original'] : get_post_meta($post_id, '_original_language', true);
    $available_languages = !empty($extracted_langs['translations']) ? $extracted_langs['translations'] : get_post_meta($post_id, '_available_languages', true);

    if (empty($original_language) && empty($available_languages)) {
        return;
    }

    $original_terms = array();
    $translated_terms = array();
    $language_data = arcuras_get_language_term_data();

    // Add original language(s) - support both string and array
    if (!empty($original_language)) {
        $original_langs = is_array($original_language) ? $original_language : array($original_language);

        foreach ($original_langs as $orig_lang) {
            // Try to find language info by ISO code first (e.g., 'en'), then by full name (e.g., 'english')
            $lang_info = null;

            // If it's a 2-letter code, try ISO lookup
            if (strlen($orig_lang) === 2) {
                $lang_info = arcuras_get_language_info_by_iso_code($orig_lang);
            }

            // If not found, try by sanitized key
            if (!$lang_info) {
                $original_key = sanitize_title($orig_lang);
                if (isset($language_data[$original_key])) {
                    $lang_info = $language_data[$original_key];
                }
            }

            // If we found the language info, get the term
            if ($lang_info && isset($lang_info['slug'])) {
                $term = get_term_by('slug', $lang_info['slug'], 'original_language');
                if ($term) {
                    $original_terms[] = (int) $term->term_id;
                }
            }
        }
    }

    // Add translated languages (exclude original languages)
    if (!empty($available_languages) && is_array($available_languages)) {
        // Convert original languages to slugs for comparison
        $original_langs_array = is_array($original_language) ? $original_language : array($original_language);
        $original_slugs = array();

        foreach ($original_langs_array as $orig_lang) {
            // Get language info (handles both ISO codes and full names)
            $lang_info = null;
            if (strlen($orig_lang) === 2) {
                $lang_info = arcuras_get_language_info_by_iso_code($orig_lang);
            }
            if (!$lang_info) {
                $original_key = sanitize_title($orig_lang);
                if (isset($language_data[$original_key])) {
                    $lang_info = $language_data[$original_key];
                }
            }

            if ($lang_info && isset($lang_info['slug'])) {
                $original_slugs[] = $lang_info['slug'];
            }
        }

        foreach ($available_languages as $lang) {
            // Try to find language info by ISO code first (e.g., 'en'), then by full name (e.g., 'english')
            $lang_info = null;

            // If it's a 2-letter code, try ISO lookup
            if (strlen($lang) === 2) {
                $lang_info = arcuras_get_language_info_by_iso_code($lang);
            }

            // If not found, try by sanitized key
            if (!$lang_info) {
                $lang_key = sanitize_title($lang);
                if (isset($language_data[$lang_key])) {
                    $lang_info = $language_data[$lang_key];
                }
            }

            // If we found the language info, check if it's not original and get the term
            if ($lang_info && isset($lang_info['slug'])) {
                $lang_slug = $lang_info['slug'];

                // Skip if this language's slug is in the original languages
                if (in_array($lang_slug, $original_slugs)) {
                    continue;
                }

                $term = get_term_by('slug', $lang_slug, 'translated_language');
                if ($term) {
                    $translated_terms[] = (int) $term->term_id;
                }
            }
        }
    }

    // Assign original language terms
    if (!empty($original_terms)) {
        wp_set_object_terms($post_id, $original_terms, 'original_language', false);
    } else {
        wp_set_object_terms($post_id, array(), 'original_language', false);
    }

    // Assign translated language terms
    if (!empty($translated_terms)) {
        wp_set_object_terms($post_id, $translated_terms, 'translated_language', false);
    } else {
        wp_set_object_terms($post_id, array(), 'translated_language', false);
    }
}
add_action('save_post', 'arcuras_auto_assign_language_terms', 20);
add_action('save_post_lyrics', 'arcuras_auto_assign_language_terms', 20);

/**
 * Get language info by slug
 */
function arcuras_get_language_info_by_slug($slug) {
    $languages = arcuras_get_language_term_data();

    foreach ($languages as $key => $data) {
        if ($data['slug'] === $slug) {
            return $data;
        }
    }

    return null;
}

/**
 * Get language info by ISO code
 */
function arcuras_get_language_info_by_iso_code($iso_code) {
    $languages = arcuras_get_language_term_data();

    foreach ($languages as $key => $data) {
        if (isset($data['iso_code']) && $data['iso_code'] === $iso_code) {
            return $data;
        }
    }

    return null;
}

/**
 * Display language badges for a post (separated by original and translations)
 */
function arcuras_get_post_language_badges($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $output = '';
    $language_data = arcuras_get_language_term_data();

    // Get Original Languages from taxonomy
    $original_langs = wp_get_post_terms($post_id, 'original_language');

    if (!empty($original_langs) && !is_wp_error($original_langs)) {
        $output .= '<div style="margin-bottom: 16px;">';
        $output .= '<div style="font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">';
        $output .= count($original_langs) > 1 ? 'See More Lyrics In' : 'See More Lyrics In';
        $output .= '</div>';
        $output .= '<div style="display: flex; flex-wrap: wrap; gap: 8px;">';

        foreach ($original_langs as $term) {
            $lang_info = arcuras_get_language_info_by_slug($term->slug);
            $flag = $lang_info ? $lang_info['flag'] : '🌐';

            $output .= sprintf(
                '<a href="%s" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; background: #f9fafb; border: 1.5px solid #e5e7eb; border-radius: 8px; text-decoration: none; transition: all 0.2s; font-size: 14px; font-weight: 500; color: #111827;" onmouseover="this.style.background=\'#f3f4f6\'; this.style.borderColor=\'#d1d5db\';" onmouseout="this.style.background=\'#f9fafb\'; this.style.borderColor=\'#e5e7eb\';">
                    <span style="font-size: 20px; line-height: 1;">%s</span>
                    <span style="color: #374151;">%s</span>
                    <span style="padding: 2px 8px; background: #dbeafe; color: #1e40af; border-radius: 4px; font-size: 11px; font-weight: 600;">Original</span>
                </a>',
                esc_url(get_term_link($term)),
                $flag,
                esc_html($term->name)
            );
        }

        $output .= '</div>';
        $output .= '</div>';
    }

    // Get Translated Languages from taxonomy
    $translated_langs = wp_get_post_terms($post_id, 'translated_language');

    if (!empty($translated_langs) && !is_wp_error($translated_langs)) {
        $output .= '<div>';
        $output .= '<div style="font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">See More Translations</div>';
        $output .= '<div style="display: flex; flex-wrap: wrap; gap: 6px;">';

        foreach ($translated_langs as $term) {
            $lang_info = arcuras_get_language_info_by_slug($term->slug);
            $flag = $lang_info ? $lang_info['flag'] : '🌐';

            $output .= sprintf(
                '<a href="%s" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 12px; background: white; border: 1px solid #e5e7eb; border-radius: 6px; text-decoration: none; transition: all 0.2s; font-size: 13px; font-weight: 500; color: #374151;" onmouseover="this.style.background=\'#fafafa\'; this.style.borderColor=\'#9ca3af\';" onmouseout="this.style.background=\'white\'; this.style.borderColor=\'#e5e7eb\';">
                    <span style="font-size: 16px; line-height: 1;">%s</span>
                    <span>%s</span>
                </a>',
                esc_url(get_term_link($term)),
                $flag,
                esc_html($term->name)
            );
        }

        $output .= '</div>';
        $output .= '</div>';
    }

    return $output;
}

/**
 * Bulk assign language terms to existing posts
 * Run this once to update all existing posts
 */
function arcuras_bulk_assign_language_terms() {
    $posts = get_posts(array(
        'post_type' => 'lyrics',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'fields' => 'ids'
    ));

    $updated = 0;
    $stats = array(
        'total' => count($posts),
        'with_original' => 0,
        'with_translations' => 0,
        'skipped' => 0
    );

    foreach ($posts as $post_id) {
        // Extract languages from block content first
        $extracted_langs = arcuras_extract_languages_from_block($post_id);

        // Count statistics
        if (!empty($extracted_langs['original'])) {
            $stats['with_original']++;
        }
        if (!empty($extracted_langs['translations'])) {
            $stats['with_translations']++;
        }
        if (empty($extracted_langs['original']) && empty($extracted_langs['translations'])) {
            $stats['skipped']++;
        }

        // Assign terms
        arcuras_auto_assign_language_terms($post_id);
        $updated++;
    }

    // Store stats in transient for display
    set_transient('arcuras_language_assignment_stats', $stats, 60);

    return $updated;
}

// Admin notice to run bulk assignment
function arcuras_language_taxonomy_admin_notice() {
    if (isset($_GET['arcuras_bulk_assign_languages']) && $_GET['arcuras_bulk_assign_languages'] === 'run') {
        if (!current_user_can('manage_options')) {
            return;
        }

        check_admin_referer('arcuras_bulk_assign_languages');

        $updated = arcuras_bulk_assign_language_terms();
        $stats = get_transient('arcuras_language_assignment_stats');

        echo '<div class="notice notice-success is-dismissible">';
        echo '<p><strong>Language Terms Updated!</strong> ' . $updated . ' posts have been processed.</p>';

        if ($stats) {
            echo '<ul style="list-style: disc; margin-left: 20px;">';
            echo '<li>Total posts: ' . $stats['total'] . '</li>';
            echo '<li>Posts with original languages: ' . $stats['with_original'] . '</li>';
            echo '<li>Posts with translations: ' . $stats['with_translations'] . '</li>';
            if ($stats['skipped'] > 0) {
                echo '<li>Posts skipped (no language data): ' . $stats['skipped'] . '</li>';
            }
            echo '</ul>';

            delete_transient('arcuras_language_assignment_stats');
        }

        echo '</div>';
    }
}
add_action('admin_notices', 'arcuras_language_taxonomy_admin_notice');

// Add admin menu for bulk assignment
function arcuras_language_taxonomy_admin_menu() {
    add_management_page(
        'Assign Language Terms',
        'Assign Languages',
        'manage_options',
        'arcuras-assign-languages',
        'arcuras_language_taxonomy_admin_page'
    );
}
add_action('admin_menu', 'arcuras_language_taxonomy_admin_menu');

function arcuras_language_taxonomy_admin_page() {
    ?>
    <div class="wrap">
        <h1>Assign Language Terms</h1>
        <p>This tool will automatically assign language taxonomy terms to all existing posts based on their <code>_original_language</code> and <code>_available_languages</code> meta data.</p>

        <form method="get" action="">
            <input type="hidden" name="page" value="arcuras-assign-languages">
            <input type="hidden" name="arcuras_bulk_assign_languages" value="run">
            <?php wp_nonce_field('arcuras_bulk_assign_languages'); ?>

            <p>
                <input type="submit" class="button button-primary" value="Assign Language Terms to All Posts">
            </p>
        </form>

        <h2>Original Languages</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Flag</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Count</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $original_languages = get_terms(array(
                    'taxonomy' => 'original_language',
                    'hide_empty' => false,
                ));

                if (!empty($original_languages) && !is_wp_error($original_languages)) {
                    foreach ($original_languages as $language) {
                        $lang_info = arcuras_get_language_info_by_slug($language->slug);
                        $flag = $lang_info ? $lang_info['flag'] : '🌐';
                        ?>
                        <tr>
                            <td style="font-size: 24px;"><?php echo $flag; ?></td>
                            <td><a href="<?php echo esc_url(get_term_link($language)); ?>"><?php echo esc_html($language->name); ?></a></td>
                            <td><code><?php echo esc_html($language->slug); ?></code></td>
                            <td><?php echo $language->count; ?></td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="4">No original languages found. Click the button above to assign them.</td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>

        <h2 style="margin-top: 30px;">Translated Languages</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Flag</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Count</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $translated_languages = get_terms(array(
                    'taxonomy' => 'translated_language',
                    'hide_empty' => false,
                ));

                if (!empty($translated_languages) && !is_wp_error($translated_languages)) {
                    foreach ($translated_languages as $language) {
                        $lang_info = arcuras_get_language_info_by_slug($language->slug);
                        $flag = $lang_info ? $lang_info['flag'] : '🌐';
                        ?>
                        <tr>
                            <td style="font-size: 24px;"><?php echo $flag; ?></td>
                            <td><a href="<?php echo esc_url(get_term_link($language)); ?>"><?php echo esc_html($language->name); ?></a></td>
                            <td><code><?php echo esc_html($language->slug); ?></code></td>
                            <td><?php echo $language->count; ?></td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="4">No translated languages found. Click the button above to assign them.</td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}
