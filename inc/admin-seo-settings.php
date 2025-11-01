<?php
/**
 * SEO Settings Admin Page
 *
 * Displays dynamically generated SEO titles and descriptions
 * for lyrics posts based on their original language
 *
 * @package Arcuras
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add SEO Settings menu to admin
 */
function arcuras_add_seo_settings_menu() {
    // Main menu
    add_menu_page(
        'SEO Settings',           // Page title
        'SEO Settings',           // Menu title
        'manage_options',         // Capability
        'arcuras-seo-settings',   // Menu slug
        'arcuras_seo_overview_page', // Callback function
        'dashicons-search',       // Icon
        30                        // Position
    );

    // Overview submenu (will replace main menu item)
    add_submenu_page(
        'arcuras-seo-settings',      // Parent slug
        'SEO Overview',              // Page title
        'Overview',                  // Menu title
        'manage_options',            // Capability
        'arcuras-seo-settings',      // Menu slug (same as parent to replace)
        'arcuras_seo_overview_page'  // Callback function
    );

    // All Lyrics submenu
    add_submenu_page(
        'arcuras-seo-settings',      // Parent slug
        'All Lyrics SEO',            // Page title
        'All Lyrics',                // Menu title
        'manage_options',            // Capability
        'arcuras-seo-all-lyrics',    // Menu slug
        'arcuras_seo_settings_page'  // Callback function (old main page)
    );

    // Original Language SEO submenu
    add_submenu_page(
        'arcuras-seo-settings',            // Parent slug
        'Original Language SEO',           // Page title
        'Original Language SEO',           // Menu title
        'manage_options',                  // Capability
        'arcuras-seo-languages',           // Menu slug
        'arcuras_seo_languages_page'       // Callback function
    );

    // Translation Language SEO submenu
    add_submenu_page(
        'arcuras-seo-settings',            // Parent slug
        'Translation Language SEO',        // Page title
        'Translation Language SEO',        // Menu title
        'manage_options',                  // Capability
        'arcuras-seo-translations',        // Menu slug
        'arcuras_seo_translations_page'    // Callback function
    );

    // Missing SEO submenu
    add_submenu_page(
        'arcuras-seo-settings',         // Parent slug
        'Missing SEO Lyrics',           // Page title
        'Missing SEO',                  // Menu title
        'manage_options',               // Capability
        'arcuras-seo-missing',          // Menu slug
        'arcuras_seo_missing_page'      // Callback function
    );

    // Manage Languages submenu
    add_submenu_page(
        'arcuras-seo-settings',         // Parent slug
        'Manage Languages',             // Page title
        'Manage Languages',             // Menu title
        'manage_options',               // Capability
        'arcuras-manage-languages',     // Menu slug
        'arcuras_manage_languages_page' // Callback function
    );
}
add_action('admin_menu', 'arcuras_add_seo_settings_menu');

/**
 * AJAX handler for updating language SEO settings
 */
function arcuras_update_language_seo() {
    // Check nonce
    check_ajax_referer('update_language_seo', 'nonce');

    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    $lang_code = sanitize_text_field($_POST['lang_code']);
    $type = sanitize_text_field($_POST['type']);
    $title_suffix = sanitize_text_field($_POST['title_suffix']);
    $meta_suffix = sanitize_textarea_field($_POST['meta_suffix']);

    // Get or create the custom SEO settings array
    $seo_settings = get_option('arcuras_language_seo_settings', array());

    // Update the settings for this language
    if (!isset($seo_settings[$lang_code])) {
        $seo_settings[$lang_code] = array();
    }

    $seo_settings[$lang_code] = array(
        'original_suffix' => $title_suffix,
        'meta_suffix' => $meta_suffix
    );

    // Save to database
    update_option('arcuras_language_seo_settings', $seo_settings);

    // Clear all WordPress caches
    wp_cache_flush();
    wp_cache_delete('arcuras_language_seo_settings', 'options');

    wp_send_json_success('SEO settings updated successfully');
}
add_action('wp_ajax_update_language_seo', 'arcuras_update_language_seo');

/**
 * AJAX handler for adding a new language
 */
function arcuras_add_new_language() {
    // Check nonce
    check_ajax_referer('add_new_language', 'nonce');

    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    $lang_name = sanitize_text_field($_POST['lang_name']);
    $lang_code = sanitize_text_field($_POST['lang_code']);
    $lang_flag = sanitize_text_field($_POST['lang_flag']);

    // Original Language SEO
    $original_title_suffix = sanitize_text_field($_POST['original_title_suffix']);
    $original_meta_suffix = sanitize_textarea_field($_POST['original_meta_suffix']);

    // Translation Language SEO
    $translation_title_suffix = sanitize_text_field($_POST['translation_title_suffix']);
    $translation_meta_suffix = sanitize_textarea_field($_POST['translation_meta_suffix']);

    // Validate inputs
    if (empty($lang_name) || empty($lang_code)) {
        wp_send_json_error('Language name and code are required');
        return;
    }

    // Get existing custom languages
    $custom_languages = get_option('arcuras_custom_languages', array());

    // Check if language code already exists
    if (isset($custom_languages[$lang_code])) {
        wp_send_json_error('Language code already exists');
        return;
    }

    // Add new language
    $slug = sanitize_title($lang_name);
    $custom_languages[$lang_code] = array(
        'name' => $lang_name,
        'slug' => $slug,
        'description' => $lang_name . ' language lyrics and translations',
        'flag' => $lang_flag ?: '🌐',
        'iso_code' => $lang_code
    );

    // Save custom languages
    update_option('arcuras_custom_languages', $custom_languages);

    // Create taxonomy terms
    wp_insert_term(
        $lang_name,
        'original_language',
        array(
            'slug' => $slug,
            'description' => $lang_name . ' language lyrics'
        )
    );

    wp_insert_term(
        $lang_name,
        'translated_language',
        array(
            'slug' => $slug,
            'description' => $lang_name . ' language translations'
        )
    );

    // Add SEO settings with smart defaults
    $seo_settings = get_option('arcuras_language_seo_settings', array());

    // Generate smart defaults using language name
    $default_translation_suffix = !empty($lang_name) ? "{$lang_name} Translation" : 'Translation';
    $default_translation_meta = !empty($lang_name)
        ? "Translated lyrics in {$lang_name} with original text and annotations"
        : 'Translated lyrics with original text and annotations';

    $seo_settings[$lang_code] = array(
        // Original Language SEO (if left empty, use English template)
        'original_suffix' => !empty($original_title_suffix)
            ? $original_title_suffix
            : 'Lyrics, Translations and Annotations',
        'meta_suffix' => !empty($original_meta_suffix)
            ? $original_meta_suffix
            : 'Read lyrics, discover translations in multiple languages, and explore detailed annotations',

        // Translation Language SEO (if left empty, generate from language name)
        'translation_suffix' => !empty($translation_title_suffix)
            ? $translation_title_suffix
            : $default_translation_suffix,
        'translation_meta_suffix' => !empty($translation_meta_suffix)
            ? $translation_meta_suffix
            : $default_translation_meta
    );
    update_option('arcuras_language_seo_settings', $seo_settings);

    // Clear all WordPress caches to ensure new language appears immediately
    wp_cache_flush();

    // Clear transients
    delete_transient('arcuras_all_languages');

    // If using object cache, delete cached options
    wp_cache_delete('arcuras_custom_languages', 'options');
    wp_cache_delete('arcuras_language_seo_settings', 'options');

    wp_send_json_success(array(
        'message' => 'Language added successfully',
        'language' => $custom_languages[$lang_code]
    ));
}
add_action('wp_ajax_add_new_language', 'arcuras_add_new_language');

/**
 * SEO Settings page content
 */
function arcuras_seo_settings_page() {
    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Get all published lyrics
    $args = array(
        'post_type'      => 'lyrics',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC'
    );

    $lyrics_query = new WP_Query($args);

    ?>
    <div class="wrap">
        <h1>🔍 SEO Settings</h1>
        <p>Bu sayfada şarkıların original language için dinamik olarak oluşturulan SEO title ve description'ları görüntüleyebilirsiniz.</p>

        <div class="card" style="max-width: 100% !important; width: 100%; margin-top: 20px;">
            <h2>📊 İstatistikler</h2>
            <p>
                <strong>Toplam Lyrics:</strong> <?php echo $lyrics_query->found_posts; ?> adet<br>
                <strong>Sistemdeki Diller:</strong>
                <?php
                $available_languages = get_terms(array(
                    'taxonomy' => 'original_language',
                    'hide_empty' => true
                ));
                if ($available_languages && !is_wp_error($available_languages)) {
                    echo count($available_languages) . ' dil';
                } else {
                    echo '0 dil';
                }
                ?>
            </p>
        </div>

        <?php if ($lyrics_query->have_posts()) : ?>
            <div class="card" style="max-width: 100% !important; width: 100%; margin-top: 20px;">
                <h2>📝 Original Language SEO Preview</h2>
                <p class="description">Her şarkının original language için oluşturulan SEO bilgileri</p>

                <table class="wp-list-table widefat fixed striped" style="margin-top: 15px; width: 100%; max-width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Şarkı Adı</th>
                            <th style="width: 15%;">Original Language</th>
                            <th style="width: 30%;">SEO Title</th>
                            <th style="width: 30%;">SEO Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($lyrics_query->have_posts()) :
                            $lyrics_query->the_post();
                            $post_id = get_the_ID();
                            $post_title = get_the_title();

                            // Get block content to find original language
                            $content = get_the_content();
                            $original_lang = arcuras_get_original_language_from_content($content);

                            if (!$original_lang) {
                                continue; // Skip if no original language found
                            }

                            // Generate SEO data
                            $seo_data = arcuras_generate_seo_data($original_lang, $post_title);

                            ?>
                            <tr>
                                <td>
                                    <strong>
                                        <a href="<?php echo get_edit_post_link($post_id); ?>" target="_blank">
                                            <?php echo esc_html($post_title); ?>
                                        </a>
                                    </strong>
                                    <div style="margin-top: 5px;">
                                        <a href="<?php echo get_permalink($post_id); ?>" target="_blank" style="font-size: 12px; color: #666;">
                                            View Post →
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <span style="padding: 3px 8px; background: #667eea; color: white; border-radius: 3px; font-size: 11px; font-weight: 600;">
                                        <?php echo esc_html($original_lang['name']); ?> (<?php echo esc_html($original_lang['code']); ?>)
                                    </span>
                                </td>
                                <td>
                                    <div style="color: #1a0dab; font-size: 18px; font-weight: 400; line-height: 1.3;">
                                        <?php echo esc_html($seo_data['title']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="color: #545454; font-size: 13px; line-height: 1.4;">
                                        <?php echo esc_html($seo_data['description']); ?>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                        ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <div class="notice notice-info" style="margin-top: 20px;">
                <p>Henüz yayınlanmış lyrics bulunamadı.</p>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Get original language from post content (Gutenberg block)
 */
function arcuras_get_original_language_from_content($content) {
    // Match the Gutenberg block
    if (preg_match('/<!-- wp:arcuras\/lyrics-translations\s+(\{.*?\})\s*(\/)?-->/s', $content, $matches)) {
        $json_str = $matches[1];
        $block_attrs = json_decode($json_str, true);

        if ($block_attrs && isset($block_attrs['languages']) && is_array($block_attrs['languages'])) {
            // Find the language marked as original
            foreach ($block_attrs['languages'] as $lang) {
                if (isset($lang['isOriginal']) && $lang['isOriginal'] === true) {
                    return array(
                        'code' => $lang['code'],
                        'name' => $lang['name']
                    );
                }
            }

            // If no language is marked as original, use the first one
            if (count($block_attrs['languages']) > 0) {
                $first_lang = $block_attrs['languages'][0];
                return array(
                    'code' => $first_lang['code'],
                    'name' => $first_lang['name']
                );
            }
        }
    }

    return null;
}

/**
 * Generate SEO data based on language and post title
 */
function arcuras_generate_seo_data($lang, $post_title) {
    // SEO data mapping (same as in editor.js)
    $seo_map = array(
        'en' => array(
            'original_suffix' => 'Lyrics, Translations and Annotations',
            'meta_suffix' => 'Read lyrics, discover translations in multiple languages, and explore detailed annotations'
        ),
        'es' => array(
            'original_suffix' => 'Letras, Traducciones y Anotaciones',
            'meta_suffix' => 'Lee las letras, descubre traducciones en varios idiomas y explora anotaciones detalladas'
        ),
        'tr' => array(
            'original_suffix' => 'Şarkı Sözleri, Çeviriler ve Açıklamalar',
            'meta_suffix' => 'Şarkı sözlerini okuyun, birden fazla dilde çevirileri keşfedin ve detaylı açıklamaları inceleyin'
        ),
        'de' => array(
            'original_suffix' => 'Liedtext, Übersetzungen und Anmerkungen',
            'meta_suffix' => 'Lesen Sie die Texte, entdecken Sie Übersetzungen in mehreren Sprachen und erkunden Sie detaillierte Anmerkungen'
        ),
        'fr' => array(
            'original_suffix' => 'Paroles, Traductions et Annotations',
            'meta_suffix' => 'Lisez les paroles, découvrez les traductions en plusieurs langues et explorez les annotations détaillées'
        ),
        'ar' => array(
            'original_suffix' => 'كلمات الأغنية والترجمات والتعليقات',
            'meta_suffix' => 'اقرأ الكلمات، اكتشف الترجمات بعدة لغات، واستكشف التعليقات التفصيلية'
        ),
        'it' => array(
            'original_suffix' => 'Testo, Traduzioni e Annotazioni',
            'meta_suffix' => 'Leggi i testi, scopri le traduzioni in più lingue ed esplora le annotazioni dettagliate'
        ),
        'pt' => array(
            'original_suffix' => 'Letras, Traduções e Anotações',
            'meta_suffix' => 'Leia as letras, descubra traduções em vários idiomas e explore anotações detalhadas'
        ),
        'ru' => array(
            'original_suffix' => 'Текст песни, переводы и комментарии',
            'meta_suffix' => 'Читайте тексты, открывайте переводы на несколько языков и изучайте подробные комментарии'
        ),
        'ja' => array(
            'original_suffix' => '歌詞、翻訳、注釈',
            'meta_suffix' => '歌詞を読み、複数言語の翻訳を発見し、詳細な注釈を探索してください'
        ),
        'ko' => array(
            'original_suffix' => '가사, 번역 및 주석',
            'meta_suffix' => '가사를 읽고, 여러 언어로 된 번역을 발견하고, 자세한 주석을 살펴보세요'
        ),
        'zh' => array(
            'original_suffix' => '歌词、翻译和注释',
            'meta_suffix' => '阅读歌词，发现多语言翻译，探索详细注释'
        ),
        'hi' => array(
            'original_suffix' => 'गीत, अनुवाद और टिप्पणियाँ',
            'meta_suffix' => 'गीत पढ़ें, कई भाषाओं में अनुवाद खोजें और विस्तृत टिप्पणियों का अन्वेषण करें'
        ),
        'nl' => array(
            'original_suffix' => 'Songteksten, Vertalingen en Annotaties',
            'meta_suffix' => 'Lees songteksten, ontdek vertalingen in meerdere talen en verken gedetailleerde annotaties'
        ),
        'pl' => array(
            'original_suffix' => 'Teksty Piosenek, Tłumaczenia i Adnotacje',
            'meta_suffix' => 'Czytaj teksty, odkrywaj tłumaczenia w wielu językach i przeglądaj szczegółowe adnotacje'
        ),
        'sv' => array(
            'original_suffix' => 'Texter, Översättningar och Kommentarer',
            'meta_suffix' => 'Läs texter, upptäck översättningar på flera språk och utforska detaljerade kommentarer'
        ),
        'no' => array(
            'original_suffix' => 'Tekster, Oversettelser og Merknader',
            'meta_suffix' => 'Les tekster, oppdag oversettelser på flere språk og utforsk detaljerte merknader'
        ),
        'da' => array(
            'original_suffix' => 'Tekster, Oversættelser og Annotationer',
            'meta_suffix' => 'Læs tekster, opdag oversættelser på flere sprog og udforsk detaljerede annotationer'
        ),
        'fi' => array(
            'original_suffix' => 'Sanoitukset, Käännökset ja Huomautukset',
            'meta_suffix' => 'Lue sanoituksia, löydä käännöksiä useilla kielillä ja tutustu yksityiskohtaisiin huomautuksiin'
        ),
        'el' => array(
            'original_suffix' => 'Στίχοι, Μεταφράσεις και Σημειώσεις',
            'meta_suffix' => 'Διαβάστε στίχους, ανακαλύψτε μεταφράσεις σε πολλές γλώσσες και εξερευνήστε λεπτομερείς σημειώσεις'
        ),
        'he' => array(
            'original_suffix' => 'מילים, תרגומים והערות',
            'meta_suffix' => 'קרא מילים, גלה תרגומים במספר שפות וחקור הערות מפורטות'
        ),
        'uk' => array(
            'original_suffix' => 'Тексти пісень, переклади та коментарі',
            'meta_suffix' => 'Читайте тексти, відкривайте переклади кількома мовами та вивчайте детальні коментарі'
        ),
        'cs' => array(
            'original_suffix' => 'Texty, Překlady a Poznámky',
            'meta_suffix' => 'Čtěte texty, objevujte překlady v několika jazycích a prozkoumávejte podrobné poznámky'
        ),
        'ro' => array(
            'original_suffix' => 'Versuri, Traduceri și Adnotări',
            'meta_suffix' => 'Citește versuri, descoperă traduceri în mai multe limbi și explorează adnotări detaliate'
        ),
        'hu' => array(
            'original_suffix' => 'Dalszövegek, Fordítások és Megjegyzések',
            'meta_suffix' => 'Olvasson dalszövegeket, fedezzen fel fordításokat több nyelven és fedezze fel a részletes megjegyzéseket'
        ),
        'th' => array(
            'original_suffix' => 'เนื้อเพลง, การแปล และคำอธิบาย',
            'meta_suffix' => 'อ่านเนื้อเพลง ค้นพบการแปลหลายภาษา และสำรวจคำอธิบายโดยละเอียด'
        ),
        'vi' => array(
            'original_suffix' => 'Lời bài hát, Bản dịch và Chú thích',
            'meta_suffix' => 'Đọc lời bài hát, khám phá bản dịch bằng nhiều ngôn ngữ và khám phá chú thích chi tiết'
        ),
        'id' => array(
            'original_suffix' => 'Lirik, Terjemahan dan Anotasi',
            'meta_suffix' => 'Baca lirik, temukan terjemahan dalam berbagai bahasa dan jelajahi anotasi terperinci'
        ),
        'ba' => array(
            'original_suffix' => 'Lyrics, Translations and Annotations',
            'meta_suffix' => 'Read lyrics, discover translations in multiple languages, and explore detailed annotations'
        )
    );

    $lang_code = $lang['code'];

    // Check for custom SEO settings first
    $custom_settings = get_option('arcuras_language_seo_settings', array());

    if (isset($custom_settings[$lang_code]) && !empty($custom_settings[$lang_code]['original_suffix'])) {
        // Use custom settings
        $seo_config = $custom_settings[$lang_code];
    } else {
        // Use default settings
        $seo_config = isset($seo_map[$lang_code]) ? $seo_map[$lang_code] : $seo_map['en'];
    }

    // Generate title and description
    $title = $post_title . ' | ' . $seo_config['original_suffix'];
    $description = $post_title . ' - ' . $seo_config['meta_suffix'];

    return array(
        'title' => $title,
        'description' => $description
    );
}

/**
 * SEO Overview page - Shows which languages have SEO and which don't
 */
function arcuras_seo_overview_page() {
    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Get all published lyrics
    $args = array(
        'post_type'      => 'lyrics',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC'
    );

    $lyrics_query = new WP_Query($args);

    // Count languages and their usage
    $language_stats = array();
    $without_seo = array();

    if ($lyrics_query->have_posts()) {
        while ($lyrics_query->have_posts()) {
            $lyrics_query->the_post();
            $post_id = get_the_ID();
            $post_title = get_the_title();
            $content = get_the_content();

            // Check if has original language
            $original_lang = arcuras_get_original_language_from_content($content);

            if ($original_lang) {
                $lang_code = $original_lang['code'];

                if (!isset($language_stats[$lang_code])) {
                    $language_stats[$lang_code] = array(
                        'code' => $lang_code,
                        'name' => $original_lang['name'],
                        'count' => 0,
                        'posts' => array()
                    );
                }

                $language_stats[$lang_code]['count']++;
                $language_stats[$lang_code]['posts'][] = array(
                    'id' => $post_id,
                    'title' => $post_title
                );
            } else {
                $without_seo[] = array(
                    'id' => $post_id,
                    'title' => $post_title
                );
            }
        }
        wp_reset_postdata();
    }

    // Sort languages by count (descending)
    usort($language_stats, function($a, $b) {
        return $b['count'] - $a['count'];
    });

    $total_with_lang = array_sum(array_column($language_stats, 'count'));
    $total = $total_with_lang + count($without_seo);
    $with_percentage = $total > 0 ? round(($total_with_lang / $total) * 100, 1) : 0;
    $without_percentage = $total > 0 ? round((count($without_seo) / $total) * 100, 1) : 0;

    ?>
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <div class="wrap">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">🔍 SEO Overview by Language</h1>
            <p class="text-gray-600">View SEO status by language - which languages exist and how many posts use them.</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Total Lyrics Card -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-3">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Lyrics</div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo number_format($total); ?></div>
                <div class="text-sm text-gray-500">All published</div>
            </div>

            <!-- Languages Card -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-3">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Languages</div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo number_format(count($language_stats)); ?></div>
                <div class="text-sm text-gray-500">Different languages</div>
            </div>

            <!-- With SEO Card -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-3">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">With SEO</div>
                    <div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center">
                        <svg class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo number_format($total_with_lang); ?></div>
                <div class="text-sm text-gray-500"><?php echo $with_percentage; ?>% of total</div>
            </div>

            <!-- Missing SEO Card -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-3">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Missing SEO</div>
                    <div class="w-5 h-5 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-3 h-3 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo number_format(count($without_seo)); ?></div>
                <div class="text-sm text-gray-500"><?php echo $without_percentage; ?>% of total</div>
            </div>
        </div>

        <!-- Quick Link to Original Language SEO -->
        <?php if (count($language_stats) > 0) : ?>
            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Original Language SEO</h3>
                        <p class="text-sm text-gray-600"><?php echo count($language_stats); ?> languages with SEO formats defined</p>
                    </div>
                    <a href="<?php echo admin_url('admin.php?page=arcuras-seo-languages'); ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                        View Languages
                        <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Quick Link to Missing SEO -->
        <?php if (count($without_seo) > 0) : ?>
            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Missing SEO Lyrics</h3>
                        <p class="text-sm text-gray-600"><?php echo count($without_seo); ?> lyrics without original language defined</p>
                    </div>
                    <a href="<?php echo admin_url('admin.php?page=arcuras-seo-missing'); ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                        View Missing SEO
                        <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($total === 0) : ?>
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                <p class="text-blue-700">No published lyrics found yet.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- DataTables CSS & JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.tailwindcss.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

    <script>
    jQuery(document).ready(function($) {
        // Initialize DataTables on both tables
        $('table').each(function() {
            if (!$(this).closest('.dataTable').length) {
                $(this).DataTable({
                    pageLength: 25,
                    order: [[0, 'asc']],
                    language: {
                        search: "Search:",
                        lengthMenu: "Show _MENU_ entries",
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        infoEmpty: "Showing 0 to 0 of 0 entries",
                        infoFiltered: "(filtered from _TOTAL_ total entries)",
                        paginate: {
                            first: "First",
                            last: "Last",
                            next: "Next",
                            previous: "Previous"
                        }
                    },
                    dom: '<"flex flex-col md:flex-row justify-between items-center mb-4"lf>rtip',
                    responsive: true
                });
            }
        });
    });
    </script>

    <style>
        /* Custom DataTables styling with Tailwind */
        .dataTables_wrapper {
            padding: 1.5rem;
        }

        .dataTables_filter input {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
            margin-left: 0.5rem;
        }

        .dataTables_length select {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.5rem;
            margin: 0 0.5rem;
        }

        .dataTables_paginate .paginate_button {
            padding: 0.5rem 0.75rem;
            margin: 0 0.125rem;
            border-radius: 0.375rem;
            border: 1px solid #e5e7eb;
            color: #6b7280 !important;
        }

        .dataTables_paginate .paginate_button.current {
            background: #111827;
            color: white !important;
            border-color: #111827;
        }

        .dataTables_paginate .paginate_button:hover {
            background: #f9fafb;
            border-color: #d1d5db;
            color: #111827 !important;
        }

        .dataTables_paginate .paginate_button.disabled {
            color: #d1d5db !important;
        }
    </style>
    <?php
}

/**
 * Missing SEO Lyrics page - Shows only lyrics without SEO
 */
function arcuras_seo_missing_page() {
    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Get all published lyrics
    $args = array(
        'post_type'      => 'lyrics',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC'
    );

    $lyrics_query = new WP_Query($args);

    // Find lyrics without SEO
    $without_seo = array();

    if ($lyrics_query->have_posts()) {
        while ($lyrics_query->have_posts()) {
            $lyrics_query->the_post();
            $post_id = get_the_ID();
            $post_title = get_the_title();
            $content = get_the_content();

            // Check if has original language
            $original_lang = arcuras_get_original_language_from_content($content);

            if (!$original_lang) {
                $without_seo[] = array(
                    'id' => $post_id,
                    'title' => $post_title,
                    'date' => get_the_date('Y-m-d H:i:s')
                );
            }
        }
        wp_reset_postdata();
    }

    ?>
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <div class="wrap">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Missing SEO Lyrics</h1>
            <p class="text-gray-600">Lyrics that don't have original language defined.</p>
        </div>

        <!-- Statistics Card -->
        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Total Missing</div>
                    <div class="text-3xl font-bold text-gray-900"><?php echo number_format(count($without_seo)); ?></div>
                </div>
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Missing SEO Table -->
        <?php if (count($without_seo) > 0) : ?>
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden mb-8">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                    <h2 class="text-xl font-semibold text-gray-900 mb-1">Lyrics Without SEO (<?php echo count($without_seo); ?>)</h2>
                    <p class="text-gray-600 text-sm">These songs need original language configuration.</p>
                </div>

                <div class="overflow-x-auto">
                <table class="w-full" id="missing-seo-table">
                    <thead class="bg-gray-50 border-b-2 border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">#</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Song Title</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Published Date</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($without_seo as $index => $item) : ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-gray-500"><?php echo $index + 1; ?></td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">
                                        <?php echo esc_html($item['title']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-500">
                                        <?php echo date('M d, Y', strtotime($item['date'])); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="<?php echo get_edit_post_link($item['id']); ?>" target="_blank"
                                           class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </a>
                                        <a href="<?php echo get_permalink($item['id']); ?>" target="_blank"
                                           class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>

                <!-- How to Fix Info -->
                <div class="bg-gray-50 border-t border-gray-200 p-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-900 mb-2">How to Fix</h3>
                            <ul class="text-sm text-gray-600 space-y-1">
                                <li>• Click "Edit" button to open the song editor</li>
                                <li>• Find the Lyrics & Translations block</li>
                                <li>• Toggle "Set as Original Language" for one language</li>
                                <li>• Save the post and return here to verify</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php else : ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                <div class="flex">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">All lyrics have SEO configured!</p>
                        <p class="text-sm text-green-700 mt-1">Every published lyric has an original language defined.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- DataTables CSS & JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.tailwindcss.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

    <script>
    jQuery(document).ready(function($) {
        $('#missing-seo-table').DataTable({
            pageLength: 25,
            order: [[0, 'asc']],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _TOTAL_ total entries)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            },
            responsive: true
        });
    });
    </script>

    <style>
        /* Custom DataTables styling */
        .dataTables_wrapper {
            padding: 1.5rem;
        }

        .dataTables_filter input {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
            margin-left: 0.5rem;
        }

        .dataTables_length select {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.5rem;
            margin: 0 0.5rem;
        }

        .dataTables_paginate .paginate_button {
            padding: 0.5rem 0.75rem;
            margin: 0 0.125rem;
            border-radius: 0.375rem;
            border: 1px solid #e5e7eb;
            color: #6b7280 !important;
        }

        .dataTables_paginate .paginate_button.current {
            background: #111827;
            color: white !important;
            border-color: #111827;
        }

        .dataTables_paginate .paginate_button:hover {
            background: #f9fafb;
            border-color: #d1d5db;
            color: #111827 !important;
        }

        .dataTables_paginate .paginate_button.disabled {
            color: #d1d5db !important;
        }
    </style>
    <?php
}

/**
 * Original Language SEO page callback
 */
function arcuras_seo_languages_page() {
    // Get all supported languages from language-taxonomy.php
    $all_languages = arcuras_get_language_term_data();

    // Initialize stats with all languages (count = 0)
    $language_stats = array();
    foreach ($all_languages as $lang_key => $lang_info) {
        $language_stats[$lang_info['iso_code']] = array(
            'name' => $lang_info['name'],
            'count' => 0
        );
    }

    // Get all lyrics posts and count usage
    $all_lyrics_query = new WP_Query(array(
        'post_type' => 'lyrics',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ));

    if ($all_lyrics_query->have_posts()) {
        while ($all_lyrics_query->have_posts()) {
            $all_lyrics_query->the_post();
            $post_id = get_the_ID();
            $content = get_the_content();

            $original_lang = arcuras_get_original_language_from_content($content);

            if ($original_lang) {
                $lang_code = $original_lang['code'];

                // Increment count if language exists in our supported list
                if (isset($language_stats[$lang_code])) {
                    $language_stats[$lang_code]['count']++;
                }
            }
        }
        wp_reset_postdata();
    }

    // Sort by post count descending, then by language name
    uasort($language_stats, function($a, $b) {
        if ($b['count'] === $a['count']) {
            return strcmp($a['name'], $b['name']);
        }
        return $b['count'] - $a['count'];
    });

    // Load Thickbox for modal
    add_thickbox();

    ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <div class="wrap">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Original Language SEO</h1>
            <p class="text-gray-600">SEO title and description formats are defined for these languages.</p>
        </div>

        <!-- Languages Table -->
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <table id="languages-table" class="display" style="width:100%; max-width: 100% !important;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Language</th>
                            <th>Code</th>
                            <th>Posts</th>
                            <th>SEO Title Format</th>
                            <th>SEO Description Format</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $counter = 1;
                        foreach ($language_stats as $lang_code => $lang_data) {
                            $seo_data = arcuras_generate_seo_data(
                                array('code' => $lang_code, 'name' => $lang_data['name']),
                                '[Song Title]'
                            );
                            ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                        <?php echo esc_html($lang_data['name']); ?>
                                    </span>
                                </td>
                                <td>
                                    <code class="text-xs font-semibold text-gray-700"><?php echo strtoupper(esc_html($lang_code)); ?></code>
                                </td>
                                <td>
                                    <span class="text-gray-900 font-medium"><?php echo $lang_data['count']; ?></span>
                                </td>
                                <td>
                                    <code class="text-xs bg-gray-50 px-2 py-1 rounded border border-gray-200">
                                        <?php echo esc_html($seo_data['title']); ?>
                                    </code>
                                </td>
                                <td>
                                    <code class="text-xs bg-gray-50 px-2 py-1 rounded border border-gray-200">
                                        <?php echo esc_html($seo_data['description']); ?>
                                    </code>
                                </td>
                                <td>
                                    <button class="button button-small edit-seo-btn"
                                            data-lang-code="<?php echo esc_attr($lang_code); ?>"
                                            data-lang-name="<?php echo esc_attr($lang_data['name']); ?>"
                                            data-type="original"
                                            data-title="<?php echo esc_attr($seo_data['title']); ?>"
                                            data-description="<?php echo esc_attr($seo_data['description']); ?>">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>

    <!-- Edit SEO Modal -->
    <div id="seo-edit-modal" style="display:none;">
        <div style="padding: 20px;">
            <h2 id="modal-title" style="margin-top: 0;">Edit SEO Settings</h2>
            <form id="seo-edit-form">
                <input type="hidden" id="edit-lang-code" name="lang_code">
                <input type="hidden" id="edit-type" name="type">

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="edit-title-suffix">Title Suffix</label></th>
                        <td>
                            <input type="text" id="edit-title-suffix" name="title_suffix" class="regular-text" style="width: 100%;">
                            <p class="description">Text that appears after the song title in SEO title.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="edit-meta-suffix">Meta Description Suffix</label></th>
                        <td>
                            <textarea id="edit-meta-suffix" name="meta_suffix" rows="3" class="large-text" style="width: 100%;"></textarea>
                            <p class="description">Text that appears after the song title in meta description.</p>
                        </td>
                    </tr>
                </table>

                <p style="margin-top: 20px;">
                    <button type="submit" class="button button-primary">Save Changes</button>
                    <button type="button" class="button" id="cancel-edit">Cancel</button>
                </p>
            </form>
        </div>
    </div>

    <script>
        jQuery(document).ready(function($) {
            $('#languages-table').DataTable({
                pageLength: 25,
                order: [[3, 'desc']],
                language: {
                    search: "Search languages:",
                    lengthMenu: "Show _MENU_ languages per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ languages",
                    infoEmpty: "No languages found",
                    infoFiltered: "(filtered from _MAX_ total languages)"
                }
            });

            // Open modal on edit button click
            $('.edit-seo-btn').on('click', function() {
                var langCode = $(this).data('lang-code');
                var langName = $(this).data('lang-name');
                var type = $(this).data('type');
                var title = $(this).data('title');
                var description = $(this).data('description');

                // Extract suffixes from current SEO data
                var titleSuffix = title.replace('[Song Title]', '').trim();
                if (titleSuffix.startsWith('|')) {
                    titleSuffix = titleSuffix.substring(1).trim();
                }

                var metaSuffix = description.replace('[Song Title]', '').trim();
                if (metaSuffix.startsWith('-')) {
                    metaSuffix = metaSuffix.substring(1).trim();
                }

                $('#modal-title').text('Edit SEO Settings - ' + langName + ' (' + langCode.toUpperCase() + ')');
                $('#edit-lang-code').val(langCode);
                $('#edit-type').val(type);
                $('#edit-title-suffix').val(titleSuffix);
                $('#edit-meta-suffix').val(metaSuffix);

                // Show modal using WordPress's modal API
                tb_show('Edit SEO Settings', '#TB_inline?width=600&height=400&inlineId=seo-edit-modal');
            });

            // Cancel button
            $('#cancel-edit').on('click', function() {
                tb_remove();
            });

            // Form submission
            $('#seo-edit-form').on('submit', function(e) {
                e.preventDefault();

                var formData = {
                    action: 'update_language_seo',
                    nonce: '<?php echo wp_create_nonce("update_language_seo"); ?>',
                    lang_code: $('#edit-lang-code').val(),
                    type: $('#edit-type').val(),
                    title_suffix: $('#edit-title-suffix').val(),
                    meta_suffix: $('#edit-meta-suffix').val()
                };

                $.post(ajaxurl, formData, function(response) {
                    if (response.success) {
                        alert('SEO settings updated successfully!');
                        tb_remove();
                        location.reload();
                    } else {
                        alert('Error: ' + (response.data || 'Unknown error'));
                    }
                });
            });
        });
        </script>
    </div>

    <style>
        /* Force full width for table card */
        .bg-white.border.border-gray-200.rounded-lg.p-6 {
            max-width: 100% !important;
            width: 100%;
        }

        table.dataTable {
            max-width: 100% !important;
            width: 100% !important;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            color: #374151;
            font-size: 0.875rem;
        }

        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.375rem 0.75rem;
            margin-left: 0.5rem;
        }

        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.375rem 0.5rem;
            margin: 0 0.5rem;
        }

        table.dataTable thead th {
            background-color: #f9fafb;
            color: #111827;
            font-weight: 600;
            border-bottom: 2px solid #e5e7eb;
            padding: 0.75rem 1rem;
        }

        table.dataTable tbody td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f3f4f6;
        }

        table.dataTable tbody tr:hover {
            background-color: #f9fafb;
        }

        .dataTables_paginate .paginate_button {
            padding: 0.375rem 0.75rem;
            margin: 0 0.125rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            background: white;
            color: #6b7280 !important;
        }

        .dataTables_paginate .paginate_button.current {
            background: #111827;
            color: white !important;
            border-color: #111827;
        }

        .dataTables_paginate .paginate_button:hover {
            background: #f9fafb;
            border-color: #d1d5db;
            color: #111827 !important;
        }

        .dataTables_paginate .paginate_button.disabled {
            color: #d1d5db !important;
        }
    </style>
    <?php
}

/**
 * Translation Language SEO page callback
 */
function arcuras_seo_translations_page() {
    // Get all supported languages from language-taxonomy.php
    $all_languages = arcuras_get_language_term_data();

    // Initialize stats with all languages (count = 0)
    $translation_stats = array();
    foreach ($all_languages as $lang_key => $lang_info) {
        $translation_stats[$lang_info['iso_code']] = array(
            'name' => $lang_info['name'],
            'count' => 0
        );
    }

    // Get all lyrics posts and count translation usage
    $all_lyrics_query = new WP_Query(array(
        'post_type' => 'lyrics',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ));

    if ($all_lyrics_query->have_posts()) {
        while ($all_lyrics_query->have_posts()) {
            $all_lyrics_query->the_post();
            $post_id = get_the_ID();
            $content = get_the_content();

            // Parse all languages from the block
            if (preg_match('/<!-- wp:arcuras\/lyrics-translations\s+(\{.*?\})\s*(\/)?-->/s', $content, $matches)) {
                $json_str = $matches[1];
                $block_attrs = json_decode($json_str, true);

                if ($block_attrs && isset($block_attrs['languages'])) {
                    foreach ($block_attrs['languages'] as $lang) {
                        // Only count translation languages (not original)
                        if (!isset($lang['isOriginal']) || $lang['isOriginal'] !== true) {
                            $lang_code = $lang['code'];

                            // Increment count if language exists in our supported list
                            if (isset($translation_stats[$lang_code])) {
                                $translation_stats[$lang_code]['count']++;
                            }
                        }
                    }
                }
            }
        }
        wp_reset_postdata();
    }

    // Sort by post count descending, then by language name
    uasort($translation_stats, function($a, $b) {
        if ($b['count'] === $a['count']) {
            return strcmp($a['name'], $b['name']);
        }
        return $b['count'] - $a['count'];
    });

    // Load Thickbox for modal
    add_thickbox();

    ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <div class="wrap">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Translation Language SEO</h1>
            <p class="text-gray-600">SEO title and description formats for translation languages.</p>
        </div>

        <!-- Translations Table -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <table id="translations-table" class="display" style="width:100%; max-width: 100% !important;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Language</th>
                        <th>Code</th>
                        <th>Translations</th>
                        <th>SEO Title Format</th>
                        <th>SEO Description Format</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $counter = 1;
                    foreach ($translation_stats as $lang_code => $lang_data) {
                        $seo_data = arcuras_generate_seo_data(
                            array('code' => $lang_code, 'name' => $lang_data['name']),
                            '[Song Title]'
                        );
                        ?>
                        <tr>
                            <td><?php echo $counter++; ?></td>
                            <td>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                    <?php echo esc_html($lang_data['name']); ?>
                                </span>
                            </td>
                            <td>
                                <code class="text-xs font-semibold text-gray-700"><?php echo strtoupper(esc_html($lang_code)); ?></code>
                            </td>
                            <td>
                                <span class="text-gray-900 font-medium"><?php echo $lang_data['count']; ?></span>
                            </td>
                            <td>
                                <code class="text-xs bg-gray-50 px-2 py-1 rounded border border-gray-200">
                                    <?php echo esc_html($seo_data['title']); ?>
                                </code>
                            </td>
                            <td>
                                <code class="text-xs bg-gray-50 px-2 py-1 rounded border border-gray-200">
                                    <?php echo esc_html($seo_data['description']); ?>
                                </code>
                            </td>
                            <td>
                                <button class="button button-small edit-seo-btn"
                                        data-lang-code="<?php echo esc_attr($lang_code); ?>"
                                        data-lang-name="<?php echo esc_attr($lang_data['name']); ?>"
                                        data-type="translation"
                                        data-title="<?php echo esc_attr($seo_data['title']); ?>"
                                        data-description="<?php echo esc_attr($seo_data['description']); ?>">
                                    Edit
                                </button>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>

    <!-- Edit SEO Modal -->
    <div id="seo-edit-modal-trans" style="display:none;">
        <div style="padding: 20px;">
            <h2 id="modal-title-trans" style="margin-top: 0;">Edit SEO Settings</h2>
            <form id="seo-edit-form-trans">
                <input type="hidden" id="edit-lang-code-trans" name="lang_code">
                <input type="hidden" id="edit-type-trans" name="type">

                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="edit-title-suffix-trans">Title Suffix</label></th>
                        <td>
                            <input type="text" id="edit-title-suffix-trans" name="title_suffix" class="regular-text" style="width: 100%;">
                            <p class="description">Text that appears after the song title in SEO title.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="edit-meta-suffix-trans">Meta Description Suffix</label></th>
                        <td>
                            <textarea id="edit-meta-suffix-trans" name="meta_suffix" rows="3" class="large-text" style="width: 100%;"></textarea>
                            <p class="description">Text that appears after the song title in meta description.</p>
                        </td>
                    </tr>
                </table>

                <p style="margin-top: 20px;">
                    <button type="submit" class="button button-primary">Save Changes</button>
                    <button type="button" class="button" id="cancel-edit-trans">Cancel</button>
                </p>
            </form>
        </div>
    </div>

    <script>
        jQuery(document).ready(function($) {
            $('#translations-table').DataTable({
                pageLength: 25,
                order: [[3, 'desc']],
                language: {
                    search: "Search languages:",
                    lengthMenu: "Show _MENU_ languages per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ languages",
                    infoEmpty: "No languages found",
                    infoFiltered: "(filtered from _MAX_ total languages)"
                }
            });

            // Open modal on edit button click
            $('.edit-seo-btn').on('click', function() {
                var langCode = $(this).data('lang-code');
                var langName = $(this).data('lang-name');
                var type = $(this).data('type');
                var title = $(this).data('title');
                var description = $(this).data('description');

                // Extract suffixes from current SEO data
                var titleSuffix = title.replace('[Song Title]', '').trim();
                if (titleSuffix.startsWith('|')) {
                    titleSuffix = titleSuffix.substring(1).trim();
                }

                var metaSuffix = description.replace('[Song Title]', '').trim();
                if (metaSuffix.startsWith('-')) {
                    metaSuffix = metaSuffix.substring(1).trim();
                }

                $('#modal-title-trans').text('Edit SEO Settings - ' + langName + ' (' + langCode.toUpperCase() + ')');
                $('#edit-lang-code-trans').val(langCode);
                $('#edit-type-trans').val(type);
                $('#edit-title-suffix-trans').val(titleSuffix);
                $('#edit-meta-suffix-trans').val(metaSuffix);

                // Show modal using WordPress's modal API
                tb_show('Edit SEO Settings', '#TB_inline?width=600&height=400&inlineId=seo-edit-modal-trans');
            });

            // Cancel button
            $('#cancel-edit-trans').on('click', function() {
                tb_remove();
            });

            // Form submission
            $('#seo-edit-form-trans').on('submit', function(e) {
                e.preventDefault();

                var formData = {
                    action: 'update_language_seo',
                    nonce: '<?php echo wp_create_nonce("update_language_seo"); ?>',
                    lang_code: $('#edit-lang-code-trans').val(),
                    type: $('#edit-type-trans').val(),
                    title_suffix: $('#edit-title-suffix-trans').val(),
                    meta_suffix: $('#edit-meta-suffix-trans').val()
                };

                $.post(ajaxurl, formData, function(response) {
                    if (response.success) {
                        alert('SEO settings updated successfully!');
                        tb_remove();
                        location.reload();
                    } else {
                        alert('Error: ' + (response.data || 'Unknown error'));
                    }
                });
            });
        });
        </script>
    </div>

    <style>
        /* Force full width for table card */
        .bg-white.border.border-gray-200.rounded-lg.p-6 {
            max-width: 100% !important;
            width: 100%;
        }

        table.dataTable {
            max-width: 100% !important;
            width: 100% !important;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            color: #374151;
            font-size: 0.875rem;
        }

        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.375rem 0.75rem;
            margin-left: 0.5rem;
        }

        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.375rem 0.5rem;
            margin: 0 0.5rem;
        }

        table.dataTable thead th {
            background-color: #f9fafb;
            color: #111827;
            font-weight: 600;
            border-bottom: 2px solid #e5e7eb;
            padding: 0.75rem 1rem;
        }

        table.dataTable tbody td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f3f4f6;
        }

        table.dataTable tbody tr:hover {
            background-color: #f9fafb;
        }

        .dataTables_paginate .paginate_button {
            padding: 0.375rem 0.75rem;
            margin: 0 0.125rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            background: white;
            color: #6b7280 !important;
        }

        .dataTables_paginate .paginate_button.current {
            background: #111827;
            color: white !important;
            border-color: #111827;
        }

        .dataTables_paginate .paginate_button:hover {
            background: #f9fafb;
            border-color: #d1d5db;
            color: #111827 !important;
        }

        .dataTables_paginate .paginate_button.disabled {
            color: #d1d5db !important;
        }
    </style>
    <?php
}

/**
 * Manage Languages page - Add new languages from admin
 */
function arcuras_manage_languages_page() {
    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Get all default languages
    $default_languages = arcuras_get_language_term_data();

    // Get custom languages
    $custom_languages = get_option('arcuras_custom_languages', array());

    // Merge them
    $all_languages = array_merge($default_languages, $custom_languages);

    ?>
    <script src="https://cdn.tailwindcss.com"></script>

    <div class="wrap">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Manage Languages</h1>
            <p class="text-gray-600">Add new languages to the system for lyrics and translations.</p>
        </div>

        <!-- Add New Language Form -->
        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">Add New Language</h2>

            <form id="add-language-form">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="lang-code" class="block text-sm font-medium text-gray-700 mb-2">
                            Select Language <span class="text-red-500">*</span>
                        </label>
                        <select id="lang-code" name="lang_code" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                style="width: 100%;">
                            <option value="">-- Select a language --</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Search and select from ISO 639-1 language codes</p>
                    </div>

                    <div>
                        <label for="lang-name" class="block text-sm font-medium text-gray-700 mb-2">
                            Language Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="lang-name" name="lang_name" required readonly
                               class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50"
                               placeholder="Auto-filled when language is selected">
                        <p class="mt-1 text-xs text-gray-500">Auto-filled from selected language</p>
                    </div>

                    <div>
                        <label for="lang-flag" class="block text-sm font-medium text-gray-700 mb-2">
                            Flag Emoji
                        </label>
                        <input type="text" id="lang-flag" name="lang_flag" maxlength="4"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="e.g., 🇦🇿">
                        <p class="mt-1 text-xs text-gray-500">Auto-filled or customize</p>
                    </div>

                </div>

                <!-- Original Language SEO Section -->
                <div class="mt-8 p-6 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center mb-4">
                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-blue-900">Original Language SEO</h3>
                    </div>
                    <p class="text-sm text-blue-700 mb-4">SEO format when this language is the <strong>original language</strong> of the lyrics.</p>

                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="original-title-suffix" class="block text-sm font-medium text-gray-700 mb-2">
                                Title Suffix <span class="text-gray-500 font-normal">(Optional)</span>
                            </label>
                            <input type="text" id="original-title-suffix" name="original_title_suffix"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="Leave empty for default: Lyrics, Translations and Annotations">
                            <p class="mt-1 text-xs text-gray-600">Default: [Song Title] | Lyrics, Translations and Annotations</p>
                        </div>

                        <div>
                            <label for="original-meta-suffix" class="block text-sm font-medium text-gray-700 mb-2">
                                Meta Description Suffix <span class="text-gray-500 font-normal">(Optional)</span>
                            </label>
                            <textarea id="original-meta-suffix" name="original_meta_suffix" rows="2"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                      placeholder="Leave empty for default English template"></textarea>
                            <p class="mt-1 text-xs text-gray-600">Default: Read lyrics, discover translations in multiple languages...</p>
                        </div>
                    </div>
                </div>

                <!-- Translation Language SEO Section -->
                <div class="mt-6 p-6 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center mb-4">
                        <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-green-900">Translation Language SEO</h3>
                    </div>
                    <p class="text-sm text-green-700 mb-4">SEO format when this language is a <strong>translation</strong> of the lyrics.</p>

                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="translation-title-suffix" class="block text-sm font-medium text-gray-700 mb-2">
                                Title Suffix <span class="text-gray-500 font-normal">(Optional)</span>
                            </label>
                            <input type="text" id="translation-title-suffix" name="translation_title_suffix"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white focus:outline-none focus:ring-2 focus:ring-green-500"
                                   placeholder="Leave empty for default: [Language Name] Translation">
                            <p class="mt-1 text-xs text-gray-600">Default: [Song Title] | [Language Name] Translation</p>
                        </div>

                        <div>
                            <label for="translation-meta-suffix" class="block text-sm font-medium text-gray-700 mb-2">
                                Meta Description Suffix <span class="text-gray-500 font-normal">(Optional)</span>
                            </label>
                            <textarea id="translation-meta-suffix" name="translation_meta_suffix" rows="2"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white focus:outline-none focus:ring-2 focus:ring-green-500"
                                      placeholder="Leave empty for: Translated lyrics in [Language Name]..."></textarea>
                            <p class="mt-1 text-xs text-gray-600">Default: Translated lyrics in [Language Name] with original text and annotations</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Language
                    </button>
                </div>
            </form>

            <div id="form-message" class="mt-4 hidden"></div>
        </div>

        <!-- Existing Languages -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">Existing Languages (<?php echo count($all_languages); ?>)</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <?php foreach ($all_languages as $lang_key => $lang_data) : ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition-colors">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-2xl"><?php echo esc_html($lang_data['flag']); ?></span>
                            <?php if (isset($custom_languages[$lang_data['iso_code']])) : ?>
                                <span class="px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded">Custom</span>
                            <?php else : ?>
                                <span class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded">Default</span>
                            <?php endif; ?>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900"><?php echo esc_html($lang_data['name']); ?></h3>
                        <p class="text-xs text-gray-500 mt-1">Code: <code class="font-mono"><?php echo esc_html($lang_data['iso_code']); ?></code></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
    jQuery(document).ready(function($) {
        // ISO 639-1 Language codes with flags
        const languages = [
            {code: 'aa', name: 'Afar', flag: '🇩🇯'},
            {code: 'ab', name: 'Abkhazian', flag: '🇬🇪'},
            {code: 'ae', name: 'Avestan', flag: '🌐'},
            {code: 'af', name: 'Afrikaans', flag: '🇿🇦'},
            {code: 'ak', name: 'Akan', flag: '🇬🇭'},
            {code: 'am', name: 'Amharic', flag: '🇪🇹'},
            {code: 'an', name: 'Aragonese', flag: '🇪🇸'},
            {code: 'ar', name: 'Arabic', flag: '🇸🇦'},
            {code: 'as', name: 'Assamese', flag: '🇮🇳'},
            {code: 'av', name: 'Avaric', flag: '🇷🇺'},
            {code: 'ay', name: 'Aymara', flag: '🇧🇴'},
            {code: 'az', name: 'Azerbaijani', flag: '🇦🇿'},
            {code: 'ba', name: 'Bashkir', flag: '🇷🇺'},
            {code: 'be', name: 'Belarusian', flag: '🇧🇾'},
            {code: 'bg', name: 'Bulgarian', flag: '🇧🇬'},
            {code: 'bh', name: 'Bihari', flag: '🇮🇳'},
            {code: 'bi', name: 'Bislama', flag: '🇻🇺'},
            {code: 'bm', name: 'Bambara', flag: '🇲🇱'},
            {code: 'bn', name: 'Bengali', flag: '🇧🇩'},
            {code: 'bo', name: 'Tibetan', flag: '🇨🇳'},
            {code: 'br', name: 'Breton', flag: '🇫🇷'},
            {code: 'bs', name: 'Bosnian', flag: '🇧🇦'},
            {code: 'ca', name: 'Catalan', flag: '🇪🇸'},
            {code: 'ce', name: 'Chechen', flag: '🇷🇺'},
            {code: 'ch', name: 'Chamorro', flag: '🇬🇺'},
            {code: 'co', name: 'Corsican', flag: '🇫🇷'},
            {code: 'cr', name: 'Cree', flag: '🇨🇦'},
            {code: 'cs', name: 'Czech', flag: '🇨🇿'},
            {code: 'cu', name: 'Church Slavic', flag: '🌐'},
            {code: 'cv', name: 'Chuvash', flag: '🇷🇺'},
            {code: 'cy', name: 'Welsh', flag: '🏴󠁧󠁢󠁷󠁬󠁳󠁿'},
            {code: 'da', name: 'Danish', flag: '🇩🇰'},
            {code: 'de', name: 'German', flag: '🇩🇪'},
            {code: 'dv', name: 'Divehi', flag: '🇲🇻'},
            {code: 'dz', name: 'Dzongkha', flag: '🇧🇹'},
            {code: 'ee', name: 'Ewe', flag: '🇬🇭'},
            {code: 'el', name: 'Greek', flag: '🇬🇷'},
            {code: 'en', name: 'English', flag: '🇬🇧'},
            {code: 'eo', name: 'Esperanto', flag: '🌐'},
            {code: 'es', name: 'Spanish', flag: '🇪🇸'},
            {code: 'et', name: 'Estonian', flag: '🇪🇪'},
            {code: 'eu', name: 'Basque', flag: '🇪🇸'},
            {code: 'fa', name: 'Persian', flag: '🇮🇷'},
            {code: 'ff', name: 'Fulah', flag: '🇳🇬'},
            {code: 'fi', name: 'Finnish', flag: '🇫🇮'},
            {code: 'fj', name: 'Fijian', flag: '🇫🇯'},
            {code: 'fo', name: 'Faroese', flag: '🇫🇴'},
            {code: 'fr', name: 'French', flag: '🇫🇷'},
            {code: 'fy', name: 'Western Frisian', flag: '🇳🇱'},
            {code: 'ga', name: 'Irish', flag: '🇮🇪'},
            {code: 'gd', name: 'Scottish Gaelic', flag: '🏴󠁧󠁢󠁳󠁣󠁴󠁿'},
            {code: 'gl', name: 'Galician', flag: '🇪🇸'},
            {code: 'gn', name: 'Guarani', flag: '🇵🇾'},
            {code: 'gu', name: 'Gujarati', flag: '🇮🇳'},
            {code: 'gv', name: 'Manx', flag: '🇮🇲'},
            {code: 'ha', name: 'Hausa', flag: '🇳🇬'},
            {code: 'he', name: 'Hebrew', flag: '🇮🇱'},
            {code: 'hi', name: 'Hindi', flag: '🇮🇳'},
            {code: 'ho', name: 'Hiri Motu', flag: '🇵🇬'},
            {code: 'hr', name: 'Croatian', flag: '🇭🇷'},
            {code: 'ht', name: 'Haitian', flag: '🇭🇹'},
            {code: 'hu', name: 'Hungarian', flag: '🇭🇺'},
            {code: 'hy', name: 'Armenian', flag: '🇦🇲'},
            {code: 'hz', name: 'Herero', flag: '🇳🇦'},
            {code: 'ia', name: 'Interlingua', flag: '🌐'},
            {code: 'id', name: 'Indonesian', flag: '🇮🇩'},
            {code: 'ie', name: 'Interlingue', flag: '🌐'},
            {code: 'ig', name: 'Igbo', flag: '🇳🇬'},
            {code: 'ii', name: 'Sichuan Yi', flag: '🇨🇳'},
            {code: 'ik', name: 'Inupiaq', flag: '🇺🇸'},
            {code: 'io', name: 'Ido', flag: '🌐'},
            {code: 'is', name: 'Icelandic', flag: '🇮🇸'},
            {code: 'it', name: 'Italian', flag: '🇮🇹'},
            {code: 'iu', name: 'Inuktitut', flag: '🇨🇦'},
            {code: 'ja', name: 'Japanese', flag: '🇯🇵'},
            {code: 'jv', name: 'Javanese', flag: '🇮🇩'},
            {code: 'ka', name: 'Georgian', flag: '🇬🇪'},
            {code: 'kg', name: 'Kongo', flag: '🇨🇩'},
            {code: 'ki', name: 'Kikuyu', flag: '🇰🇪'},
            {code: 'kj', name: 'Kuanyama', flag: '🇦🇴'},
            {code: 'kk', name: 'Kazakh', flag: '🇰🇿'},
            {code: 'kl', name: 'Kalaallisut', flag: '🇬🇱'},
            {code: 'km', name: 'Khmer', flag: '🇰🇭'},
            {code: 'kn', name: 'Kannada', flag: '🇮🇳'},
            {code: 'ko', name: 'Korean', flag: '🇰🇷'},
            {code: 'kr', name: 'Kanuri', flag: '🇳🇬'},
            {code: 'ks', name: 'Kashmiri', flag: '🇮🇳'},
            {code: 'ku', name: 'Kurdish', flag: '🇮🇶'},
            {code: 'kv', name: 'Komi', flag: '🇷🇺'},
            {code: 'kw', name: 'Cornish', flag: '🇬🇧'},
            {code: 'ky', name: 'Kyrgyz', flag: '🇰🇬'},
            {code: 'la', name: 'Latin', flag: '🇻🇦'},
            {code: 'lb', name: 'Luxembourgish', flag: '🇱🇺'},
            {code: 'lg', name: 'Ganda', flag: '🇺🇬'},
            {code: 'li', name: 'Limburgish', flag: '🇳🇱'},
            {code: 'ln', name: 'Lingala', flag: '🇨🇩'},
            {code: 'lo', name: 'Lao', flag: '🇱🇦'},
            {code: 'lt', name: 'Lithuanian', flag: '🇱🇹'},
            {code: 'lu', name: 'Luba-Katanga', flag: '🇨🇩'},
            {code: 'lv', name: 'Latvian', flag: '🇱🇻'},
            {code: 'mg', name: 'Malagasy', flag: '🇲🇬'},
            {code: 'mh', name: 'Marshallese', flag: '🇲🇭'},
            {code: 'mi', name: 'Maori', flag: '🇳🇿'},
            {code: 'mk', name: 'Macedonian', flag: '🇲🇰'},
            {code: 'ml', name: 'Malayalam', flag: '🇮🇳'},
            {code: 'mn', name: 'Mongolian', flag: '🇲🇳'},
            {code: 'mr', name: 'Marathi', flag: '🇮🇳'},
            {code: 'ms', name: 'Malay', flag: '🇲🇾'},
            {code: 'mt', name: 'Maltese', flag: '🇲🇹'},
            {code: 'my', name: 'Burmese', flag: '🇲🇲'},
            {code: 'na', name: 'Nauru', flag: '🇳🇷'},
            {code: 'nb', name: 'Norwegian Bokmål', flag: '🇳🇴'},
            {code: 'nd', name: 'North Ndebele', flag: '🇿🇼'},
            {code: 'ne', name: 'Nepali', flag: '🇳🇵'},
            {code: 'ng', name: 'Ndonga', flag: '🇳🇦'},
            {code: 'nl', name: 'Dutch', flag: '🇳🇱'},
            {code: 'nn', name: 'Norwegian Nynorsk', flag: '🇳🇴'},
            {code: 'no', name: 'Norwegian', flag: '🇳🇴'},
            {code: 'nr', name: 'South Ndebele', flag: '🇿🇦'},
            {code: 'nv', name: 'Navajo', flag: '🇺🇸'},
            {code: 'ny', name: 'Chichewa', flag: '🇲🇼'},
            {code: 'oc', name: 'Occitan', flag: '🇫🇷'},
            {code: 'oj', name: 'Ojibwa', flag: '🇨🇦'},
            {code: 'om', name: 'Oromo', flag: '🇪🇹'},
            {code: 'or', name: 'Oriya', flag: '🇮🇳'},
            {code: 'os', name: 'Ossetian', flag: '🇬🇪'},
            {code: 'pa', name: 'Punjabi', flag: '🇮🇳'},
            {code: 'pi', name: 'Pali', flag: '🌐'},
            {code: 'pl', name: 'Polish', flag: '🇵🇱'},
            {code: 'ps', name: 'Pashto', flag: '🇦🇫'},
            {code: 'pt', name: 'Portuguese', flag: '🇵🇹'},
            {code: 'qu', name: 'Quechua', flag: '🇵🇪'},
            {code: 'rm', name: 'Romansh', flag: '🇨🇭'},
            {code: 'rn', name: 'Rundi', flag: '🇧🇮'},
            {code: 'ro', name: 'Romanian', flag: '🇷🇴'},
            {code: 'ru', name: 'Russian', flag: '🇷🇺'},
            {code: 'rw', name: 'Kinyarwanda', flag: '🇷🇼'},
            {code: 'sa', name: 'Sanskrit', flag: '🇮🇳'},
            {code: 'sc', name: 'Sardinian', flag: '🇮🇹'},
            {code: 'sd', name: 'Sindhi', flag: '🇵🇰'},
            {code: 'se', name: 'Northern Sami', flag: '🇳🇴'},
            {code: 'sg', name: 'Sango', flag: '🇨🇫'},
            {code: 'si', name: 'Sinhala', flag: '🇱🇰'},
            {code: 'sk', name: 'Slovak', flag: '🇸🇰'},
            {code: 'sl', name: 'Slovenian', flag: '🇸🇮'},
            {code: 'sm', name: 'Samoan', flag: '🇼🇸'},
            {code: 'sn', name: 'Shona', flag: '🇿🇼'},
            {code: 'so', name: 'Somali', flag: '🇸🇴'},
            {code: 'sq', name: 'Albanian', flag: '🇦🇱'},
            {code: 'sr', name: 'Serbian', flag: '🇷🇸'},
            {code: 'ss', name: 'Swati', flag: '🇸🇿'},
            {code: 'st', name: 'Southern Sotho', flag: '🇱🇸'},
            {code: 'su', name: 'Sundanese', flag: '🇮🇩'},
            {code: 'sv', name: 'Swedish', flag: '🇸🇪'},
            {code: 'sw', name: 'Swahili', flag: '🇰🇪'},
            {code: 'ta', name: 'Tamil', flag: '🇮🇳'},
            {code: 'te', name: 'Telugu', flag: '🇮🇳'},
            {code: 'tg', name: 'Tajik', flag: '🇹🇯'},
            {code: 'th', name: 'Thai', flag: '🇹🇭'},
            {code: 'ti', name: 'Tigrinya', flag: '🇪🇷'},
            {code: 'tk', name: 'Turkmen', flag: '🇹🇲'},
            {code: 'tl', name: 'Tagalog', flag: '🇵🇭'},
            {code: 'tn', name: 'Tswana', flag: '🇧🇼'},
            {code: 'to', name: 'Tonga', flag: '🇹🇴'},
            {code: 'tr', name: 'Turkish', flag: '🇹🇷'},
            {code: 'ts', name: 'Tsonga', flag: '🇿🇦'},
            {code: 'tt', name: 'Tatar', flag: '🇷🇺'},
            {code: 'tw', name: 'Twi', flag: '🇬🇭'},
            {code: 'ty', name: 'Tahitian', flag: '🇵🇫'},
            {code: 'ug', name: 'Uighur', flag: '🇨🇳'},
            {code: 'uk', name: 'Ukrainian', flag: '🇺🇦'},
            {code: 'ur', name: 'Urdu', flag: '🇵🇰'},
            {code: 'uz', name: 'Uzbek', flag: '🇺🇿'},
            {code: 've', name: 'Venda', flag: '🇿🇦'},
            {code: 'vi', name: 'Vietnamese', flag: '🇻🇳'},
            {code: 'vo', name: 'Volapük', flag: '🌐'},
            {code: 'wa', name: 'Walloon', flag: '🇧🇪'},
            {code: 'wo', name: 'Wolof', flag: '🇸🇳'},
            {code: 'xh', name: 'Xhosa', flag: '🇿🇦'},
            {code: 'yi', name: 'Yiddish', flag: '🇮🇱'},
            {code: 'yo', name: 'Yoruba', flag: '🇳🇬'},
            {code: 'za', name: 'Zhuang', flag: '🇨🇳'},
            {code: 'zh', name: 'Chinese', flag: '🇨🇳'},
            {code: 'zu', name: 'Zulu', flag: '🇿🇦'}
        ];

        // Populate select options
        languages.forEach(function(lang) {
            $('#lang-code').append(new Option(lang.flag + ' ' + lang.name + ' (' + lang.code + ')', lang.code));
        });

        // Initialize Select2
        $('#lang-code').select2({
            placeholder: 'Search for a language...',
            allowClear: true,
            width: '100%'
        });

        // Auto-fill name and flag when language is selected
        $('#lang-code').on('change', function() {
            const selectedCode = $(this).val();
            const selectedLang = languages.find(l => l.code === selectedCode);

            if (selectedLang) {
                $('#lang-name').val(selectedLang.name);
                $('#lang-flag').val(selectedLang.flag);
            } else {
                $('#lang-name').val('');
                $('#lang-flag').val('');
            }
        });

        $('#add-language-form').on('submit', function(e) {
            e.preventDefault();

            var formData = {
                action: 'add_new_language',
                nonce: '<?php echo wp_create_nonce("add_new_language"); ?>',
                lang_name: $('#lang-name').val(),
                lang_code: $('#lang-code').val().toLowerCase(),
                lang_flag: $('#lang-flag').val(),
                original_title_suffix: $('#original-title-suffix').val(),
                original_meta_suffix: $('#original-meta-suffix').val(),
                translation_title_suffix: $('#translation-title-suffix').val(),
                translation_meta_suffix: $('#translation-meta-suffix').val()
            };

            // Show loading
            var $button = $(this).find('button[type="submit"]');
            var originalText = $button.html();
            $button.prop('disabled', true).html('<svg class="animate-spin h-4 w-4 mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Adding...');

            $.post(ajaxurl, formData, function(response) {
                if (response.success) {
                    $('#form-message')
                        .removeClass('hidden bg-red-50 border-red-200 text-red-800')
                        .addClass('bg-green-50 border border-green-200 text-green-800 rounded-lg p-4')
                        .html('<strong>Success!</strong> ' + response.data.message + ' Reloading page...');

                    // Reset form
                    $('#add-language-form')[0].reset();

                    // Reload page after 1.5 seconds
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    $('#form-message')
                        .removeClass('hidden bg-green-50 border-green-200 text-green-800')
                        .addClass('bg-red-50 border border-red-200 text-red-800 rounded-lg p-4')
                        .html('<strong>Error!</strong> ' + response.data);

                    $button.prop('disabled', false).html(originalText);
                }
            });
        });
    });
    </script>

    <style>
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .animate-spin {
            animation: spin 1s linear infinite;
        }
    </style>
    <?php
}

/**
 * Initialize SEO settings for all default languages
 * This ensures that all default languages have SEO settings in the database
 */
function arcuras_initialize_default_language_seo_settings() {
    // Get existing SEO settings
    $seo_settings = get_option('arcuras_language_seo_settings', array());

    // Get all language term data (includes all default languages)
    if (!function_exists('arcuras_get_language_term_data')) {
        return;
    }

    $language_data = arcuras_get_language_term_data();

    // SEO data for all languages (matching constants.js)
    $seo_data = array(
        'en' => array('original_suffix' => 'Lyrics, Translations and Annotations', 'meta_suffix' => 'Read lyrics, discover translations in multiple languages, and explore detailed annotations', 'translation_suffix' => 'Translation', 'translation_meta_suffix' => 'Translated lyrics with original text and annotations'),
        'es' => array('original_suffix' => 'Letras, Traducciones y Anotaciones', 'meta_suffix' => 'Lee las letras, descubre traducciones en varios idiomas y explora anotaciones detalladas', 'translation_suffix' => 'Traducción al Español', 'translation_meta_suffix' => 'Letras traducidas al español con texto original y anotaciones'),
        'tr' => array('original_suffix' => 'Şarkı Sözleri, Çeviriler ve Açıklamalar', 'meta_suffix' => 'Şarkı sözlerini okuyun, birden fazla dilde çevirileri keşfedin ve detaylı açıklamaları inceleyin', 'translation_suffix' => 'Türkçe Çeviri', 'translation_meta_suffix' => 'Orijinal metin ve açıklamalarla birlikte Türkçe çeviri'),
        'de' => array('original_suffix' => 'Liedtext, Übersetzungen und Anmerkungen', 'meta_suffix' => 'Lesen Sie die Texte, entdecken Sie Übersetzungen in mehreren Sprachen und erkunden Sie detaillierte Anmerkungen', 'translation_suffix' => 'Deutsche Übersetzung', 'translation_meta_suffix' => 'Übersetzte Texte mit Originaltext und Anmerkungen'),
        'fr' => array('original_suffix' => 'Paroles, Traductions et Annotations', 'meta_suffix' => 'Lisez les paroles, découvrez les traductions en plusieurs langues et explorez les annotations détaillées', 'translation_suffix' => 'Traduction Française', 'translation_meta_suffix' => 'Paroles traduites en français avec texte original et annotations'),
        'ar' => array('original_suffix' => 'كلمات الأغنية والترجمات والتعليقات', 'meta_suffix' => 'اقرأ الكلمات، اكتشف الترجمات بعدة لغات، واستكشف التعليقات التفصيلية', 'translation_suffix' => 'ترجمة عربية', 'translation_meta_suffix' => 'كلمات مترجمة مع النص الأصلي والتعليقات'),
        'it' => array('original_suffix' => 'Testo, Traduzioni e Annotazioni', 'meta_suffix' => 'Leggi i testi, scopri le traduzioni in più lingue ed esplora le annotazioni dettagliate', 'translation_suffix' => 'Traduzione Italiana', 'translation_meta_suffix' => 'Testo tradotto in italiano con testo originale e annotazioni'),
        'pt' => array('original_suffix' => 'Letras, Traduções e Anotações', 'meta_suffix' => 'Leia as letras, descubra traduções em vários idiomas e explore anotações detalhadas', 'translation_suffix' => 'Tradução em Português', 'translation_meta_suffix' => 'Letras traduzidas em português com texto original e anotações'),
        'ru' => array('original_suffix' => 'Текст песни, переводы и комментарии', 'meta_suffix' => 'Читайте тексты, открывайте переводы на несколько языков и изучайте подробные комментарии', 'translation_suffix' => 'Русский перевод', 'translation_meta_suffix' => 'Переведенный текст с оригиналом и комментариями'),
        'ja' => array('original_suffix' => '歌詞、翻訳、注釈', 'meta_suffix' => '歌詞を読み、複数言語の翻訳を発見し、詳細な注釈を探索してください', 'translation_suffix' => '日本語翻訳', 'translation_meta_suffix' => '原文と注釈付きの日本語訳歌詞'),
        'ko' => array('original_suffix' => '가사, 번역 및 주석', 'meta_suffix' => '가사를 읽고, 여러 언어로 된 번역을 발견하고, 상세한 주석을 탐색하세요', 'translation_suffix' => '한국어 번역', 'translation_meta_suffix' => '원문과 주석이 포함된 한국어 번역 가사'),
        'zh' => array('original_suffix' => '歌词、翻译和注释', 'meta_suffix' => '阅读歌词，发现多种语言的翻译，并探索详细的注释', 'translation_suffix' => '中文翻译', 'translation_meta_suffix' => '带有原文和注释的中文翻译歌词'),
        'hi' => array('original_suffix' => 'गीत, अनुवाद और टिप्पणियाँ', 'meta_suffix' => 'गीत पढ़ें, कई भाषाओं में अनुवाद खोजें, और विस्तृत टिप्पणियों का अन्वेषण करें', 'translation_suffix' => 'हिंदी अनुवाद', 'translation_meta_suffix' => 'मूल पाठ और टिप्पणियों के साथ हिंदी अनुवादित गीत'),
        'nl' => array('original_suffix' => 'Songteksten, Vertalingen en Annotaties', 'meta_suffix' => 'Lees songteksten, ontdek vertalingen in meerdere talen en verken gedetailleerde annotaties', 'translation_suffix' => 'Nederlandse Vertaling', 'translation_meta_suffix' => 'Vertaalde songtekst met originele tekst en annotaties'),
        'pl' => array('original_suffix' => 'Teksty Piosenek, Tłumaczenia i Adnotacje', 'meta_suffix' => 'Czytaj teksty, odkrywaj tłumaczenia w wielu językach i eksploruj szczegółowe adnotacje', 'translation_suffix' => 'Polskie Tłumaczenie', 'translation_meta_suffix' => 'Przetłumaczone teksty z oryginalnym tekstem i adnotacjami'),
        'sv' => array('original_suffix' => 'Texter, Översättningar och Kommentarer', 'meta_suffix' => 'Läs texter, upptäck översättningar på flera språk och utforska detaljerade kommentarer', 'translation_suffix' => 'Svensk Översättning', 'translation_meta_suffix' => 'Översatta texter med originaltext och kommentarer'),
        'no' => array('original_suffix' => 'Tekster, Oversettelser og Merknader', 'meta_suffix' => 'Les tekster, oppdag oversettelser på flere språk og utforsk detaljerte merknader', 'translation_suffix' => 'Norsk Oversettelse', 'translation_meta_suffix' => 'Oversatte tekster med originaltekst og merknader'),
        'da' => array('original_suffix' => 'Tekster, Oversættelser og Annotationer', 'meta_suffix' => 'Læs tekster, opdag oversættelser på flere sprog og udforsk detaljerede annotationer', 'translation_suffix' => 'Dansk Oversættelse', 'translation_meta_suffix' => 'Oversatte tekster med originaltekst og annotationer'),
        'fi' => array('original_suffix' => 'Sanoitukset, Käännökset ja Huomautukset', 'meta_suffix' => 'Lue sanoituksia, löydä käännöksiä useilla kielillä ja tutustu yksityiskohtaisiin huomautuksiin', 'translation_suffix' => 'Suomenkielinen Käännös', 'translation_meta_suffix' => 'Käännetyt sanoitukset alkuperäistekstin ja huomautusten kera'),
        'el' => array('original_suffix' => 'Στίχοι, Μεταφράσεις και Σημειώσεις', 'meta_suffix' => 'Διαβάστε στίχους, ανακαλύψτε μεταφράσεις σε πολλές γλώσσες και εξερευνήστε λεπτομερείς σημειώσεις', 'translation_suffix' => 'Ελληνική Μετάφραση', 'translation_meta_suffix' => 'Μεταφρασμένοι στίχοι με πρωτότυπο κείμενο και σημειώσεις'),
        'he' => array('original_suffix' => 'מילים, תרגומים והערות', 'meta_suffix' => 'קרא מילים, גלה תרגומים במספר שפות וחקור הערות מפורטות', 'translation_suffix' => 'תרגום לעברית', 'translation_meta_suffix' => 'מילים מתורגמות עם טקסט מקורי והערות'),
        'uk' => array('original_suffix' => 'Тексти пісень, переклади та коментарі', 'meta_suffix' => 'Читайте тексти, відкривайте переклади кількома мовами та вивчайте детальні коментарі', 'translation_suffix' => 'Український переклад', 'translation_meta_suffix' => 'Перекладений текст з оригіналом та коментарями'),
        'cs' => array('original_suffix' => 'Texty, Překlady a Poznámky', 'meta_suffix' => 'Čtěte texty, objevujte překlady v několika jazycích a prozkoumávejte podrobné poznámky', 'translation_suffix' => 'Český Překlad', 'translation_meta_suffix' => 'Přeložené texty s původním textem a poznámkami'),
        'ro' => array('original_suffix' => 'Versuri, Traduceri și Adnotări', 'meta_suffix' => 'Citește versurile, descoperă traduceri în mai multe limbi și explorează adnotările detaliate', 'translation_suffix' => 'Traducere în Română', 'translation_meta_suffix' => 'Versuri traduse cu text original și adnotări'),
        'hu' => array('original_suffix' => 'Dalszövegek, Fordítások és Megjegyzések', 'meta_suffix' => 'Olvasd a dalszövegeket, fedezd fel a fordításokat több nyelven és fedezd fel a részletes megjegyzéseket', 'translation_suffix' => 'Magyar Fordítás', 'translation_meta_suffix' => 'Lefordított dalszöveg eredeti szöveggel és megjegyzésekkel'),
        'th' => array('original_suffix' => 'เนื้อเพลง, การแปล และคำอธิบาย', 'meta_suffix' => 'อ่านเนื้อเพลง ค้นพบการแปลในหลายภาษา และสำรวจคำอธิบายโดยละเอียด', 'translation_suffix' => 'แปลภาษาไทย', 'translation_meta_suffix' => 'เนื้อเพลงที่แปลพร้อมข้อความต้นฉบับและคำอธิบาย'),
        'vi' => array('original_suffix' => 'Lời bài hát, Bản dịch và Chú thích', 'meta_suffix' => 'Đọc lời bài hát, khám phá bản dịch bằng nhiều ngôn ngữ và khám phá chú thích chi tiết', 'translation_suffix' => 'Bản dịch Tiếng Việt', 'translation_meta_suffix' => 'Lời bài hát được dịch sang tiếng Việt với văn bản gốc và chú thích'),
        'id' => array('original_suffix' => 'Lirik, Terjemahan dan Anotasi', 'meta_suffix' => 'Baca lirik, temukan terjemahan dalam berbagai bahasa dan jelajahi anotasi terperinci', 'translation_suffix' => 'Terjemahan Bahasa Indonesia', 'translation_meta_suffix' => 'Lirik terjemahan bahasa Indonesia dengan teks asli dan anotasi'),
        'ba' => array('original_suffix' => 'Йыр һүҙҙәре, тәржемәләр һәм аңлатмалар', 'meta_suffix' => 'Йыр һүҙҙәрен уҡығыҙ, төрлө телдәрҙәге тәржемәләрҙе табығыҙ һәм тулы аңлатмаларҙы өйрәнегеҙ', 'translation_suffix' => 'Башҡорт тәржемәһе', 'translation_meta_suffix' => 'Башҡорт теленә тәржемә ителгән йыр һүҙҙәре, төп текст һәм аңлатмалар менән'),
        'az' => array('original_suffix' => 'Mahnı sözləri, Tərcümələr və Açıqlamalar', 'meta_suffix' => 'Mahnı sözlərini oxuyun, müxtəlif dillərdə tərcümələri kəşf edin və ətraflı açıqlamaları araşdırın', 'translation_suffix' => 'Azərbaycan dilinə tərcümə', 'translation_meta_suffix' => 'Orijinal mətn və açıqlamalarla birlikdə Azərbaycan dilinə tərcümə edilmiş mahnı sözləri'),
        'kk' => array('original_suffix' => 'Ән сөздері, Аудармалар және Түсініктемелер', 'meta_suffix' => 'Ән сөздерін оқыңыз, әртүрлі тілдердегі аудармаларды табыңыз және толық түсініктемелерді зерттеңіз', 'translation_suffix' => 'Қазақ тіліне аударма', 'translation_meta_suffix' => 'Түпнұсқа мәтін және түсініктемелермен бірге қазақ тіліне аударылған ән сөздері'),
        'alt' => array('original_suffix' => 'Кожоҥ сӧстӧри, Которгон сӧстӧр ла Туружылар', 'meta_suffix' => 'Кожоҥ сӧстӧрин окуп, туужы тилдерде которгон сӧстӧрди табар, ла туружыларды ӧргӧнӧр', 'translation_suffix' => 'Алтай тилге которгон сӧс', 'translation_meta_suffix' => 'Баштапкы текст ла туружылар ла бӧлӧ алтай тилге которгон кожоҥ сӧстӧр'),
        'mn' => array('original_suffix' => 'Дууны үг, Орчуулга ба Тайлбар', 'meta_suffix' => 'Дууны үг уншиж, олон хэл дээрх орчуулга олж, дэлгэрэнгүй тайлбар судлаарай', 'translation_suffix' => 'Монгол орчуулга', 'translation_meta_suffix' => 'Эх бичвэр ба тайлбартай хамт монгол хэл рүү орчуулсан дууны үг'),
        'cv' => array('original_suffix' => 'Юрă сăмахĕсем, Куҫарусем тата Ăнлантарусем', 'meta_suffix' => 'Юрă сăмахĕсене вулăр, нумай чĕлхепе куҫарусене тупăр тата тĕплĕнрех ăнлантарусене пĕлĕр', 'translation_suffix' => 'Юрă сăмахĕсем, Чӑвашла куҫару тата Ăнлантарусем', 'translation_meta_suffix' => 'Чӑваш чĕлхине куҫарнă текста тĕп сăмахĕсемпе тата тĕплĕнрех ăнлантарусемпе пĕрле вулăр'),
    );

    // Add SEO settings for each default language if not already exists
    $updated = false;
    foreach ($language_data as $lang_key => $lang_info) {
        $iso_code = isset($lang_info['iso_code']) ? $lang_info['iso_code'] : $lang_key;

        // Only add if doesn't exist in database AND we have SEO data for it
        if (!isset($seo_settings[$iso_code]) && isset($seo_data[$iso_code])) {
            $seo_settings[$iso_code] = $seo_data[$iso_code];
            $updated = true;
        }
    }

    // Save to database if we added any new settings
    if ($updated) {
        update_option('arcuras_language_seo_settings', $seo_settings);
    }
}

// Run on theme activation/switch
add_action('after_switch_theme', 'arcuras_initialize_default_language_seo_settings');

// Also run on admin_init to catch any missing settings
add_action('admin_init', 'arcuras_initialize_default_language_seo_settings', 5);
