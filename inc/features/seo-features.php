<?php

/**
 * Çok dilli SEO meta etiketlerini aktif et
 */
function gufte_activate_multilingual_seo() {
    // Mevcut meta description fonksiyonunu çok dilli sayfalar için devre dışı bırak
    if (is_singular() && isset($_GET['lang'])) {
        $lang = sanitize_text_field(wp_unslash($_GET['lang']));

        if (gufte_is_valid_language($lang)) {
            remove_action('wp_head', 'gufte_add_meta_description', 1);
            add_action('wp_head', 'gufte_multilingual_seo_meta', 1);
        }
    }
}
add_action('wp_head', 'gufte_activate_multilingual_seo', 0);

/**
 * Çok dilli başlık filtresi aktif et
 */
add_filter('pre_get_document_title', 'gufte_multilingual_page_title', 10, 1);
add_filter('document_title_parts', 'gufte_adjust_document_title_parts', 20, 1);

/**
 * Geçerli dilleri kontrol et
 */
function gufte_is_valid_language($lang) {
    $settings = gufte_get_language_settings();
    return array_key_exists($lang, $settings['language_map']);
}

/**
 * Şarkıcı bilgisini güvenli şekilde al
 */
function gufte_get_singer_name($post_id = null) {
    $post_id = $post_id ?: get_the_ID();
    $singer_terms = get_the_terms($post_id, 'singer');
    
    if ($singer_terms && !is_wp_error($singer_terms)) {
        return $singer_terms[0]->name;
    }
    
    return '';
}

/**
 * Geliştirilmiş Çok Dilli SEO Fonksiyonları
 * functions.php dosyasındaki mevcut çok dilli fonksiyonları değiştirin
 */

/**
 * Dil ayarları ve haritaları (güncellenmiş)
 */
function gufte_get_language_settings() {
    return [
        'language_map' => [
            'english' => 'English Translation',
            'spanish' => 'Traducción al Español', 
            'turkish' => 'Türkçe Çevirisi',
            'german' => 'Deutsche Übersetzung',
            'arabic' => 'الترجمة العربية',
            'french' => 'Traduction en Français',
            'italian' => 'Traduzione in Italiano',
            'portuguese' => 'Tradução em Português',
            'russian' => 'Русский перевод',
            'japanese' => '日本語翻訳',
        ],
        'iso_map' => [
            'english' => 'en',
            'spanish' => 'es', 
            'turkish' => 'tr',
            'german' => 'de',
            'arabic' => 'ar',
            'french' => 'fr',
            'italian' => 'it',
            'portuguese' => 'pt',
            'russian' => 'ru',
            'japanese' => 'ja',
        ],
        'native_titles' => [
            'english' => 'Lyrics and English Translation',
            'spanish' => 'Letras y Traducción al Español',
            'turkish' => 'Şarkı Sözleri ve Türkçe Çevirisi',
            'german' => 'Liedtext und Deutsche Übersetzung',
            'arabic' => 'كلمات الأغنية والترجمة العربية',
            'french' => 'Paroles et Traduction en Français',
            'italian' => 'Testo e Traduzione in Italiano',
            'portuguese' => 'Letras e Tradução em Português',
            'russian' => 'Текст песни и русский перевод',
            'japanese' => '歌詞と日本語翻訳',
        ],
        'og_locale_map' => [
            'english' => 'en_US',
            'spanish' => 'es_ES',
            'turkish' => 'tr_TR',
            'german' => 'de_DE',
            'arabic' => 'ar_AR',
            'french' => 'fr_FR',
            'italian' => 'it_IT',
            'portuguese' => 'pt_PT',
            'russian' => 'ru_RU',
            'japanese' => 'ja_JP',
        ],
    ];
}

/**
 * Başlık için dil bazlı suffix oluştur
 */
function gufte_build_multilingual_title($post_title, $singer_name, $lang) {
    $settings = gufte_get_language_settings();

    if (isset($settings['native_titles'][$lang])) {
        $suffix = $settings['native_titles'][$lang];
    } elseif (isset($settings['language_map'][$lang])) {
        $suffix = $settings['language_map'][$lang];
    } else {
        $suffix = ucfirst($lang) . ' Translation';
    }

    if (!empty($singer_name)) {
        return "{$post_title} - {$singer_name} | {$suffix}";
    }

    return "{$post_title} | {$suffix}";
}

function gufte_get_multilingual_title_value($lang, $post_id = null) {
    if (empty($lang) || !gufte_is_valid_language($lang)) {
        return null;
    }

    $post_id = $post_id ?: get_queried_object_id();
    if (!$post_id) {
        return null;
    }

    $post_title = get_the_title($post_id);
    $singer_name = gufte_get_singer_name($post_id);

    return gufte_build_multilingual_title($post_title, $singer_name, $lang);
}


/**
 * Çok dilli sayfa başlığı (güncellenmiş - her dil için native başlık)
 */
function gufte_multilingual_page_title($title) {
    if (!is_singular() || !isset($_GET['lang'])) {
        return $title;
    }
    
    $lang = sanitize_text_field(wp_unslash($_GET['lang']));
    
    if (!gufte_is_valid_language($lang)) {
        return $title;
    }

    $settings = gufte_get_language_settings();
    $post_title = get_the_title();
    $singer_name = gufte_get_singer_name();

    return gufte_build_multilingual_title($post_title, $singer_name, $lang);
}

/**
 * Başlık parçalarını güncelle
 */
function gufte_adjust_document_title_parts($parts) {
    if (!is_singular() || !isset($_GET['lang'])) {
        return $parts;
    }

    $lang = sanitize_text_field(wp_unslash($_GET['lang']));

    $translated_title = gufte_get_multilingual_title_value($lang);
    if ($translated_title) {
        $parts['title'] = $translated_title;
    }

    return $parts;
}

/**
 * Yoast SEO başlık uyarlaması
 */
function gufte_adjust_wpseo_title($title) {
    if (!is_singular() || !isset($_GET['lang'])) {
        return $title;
    }

    $lang = sanitize_text_field(wp_unslash($_GET['lang']));
    $translated_title = gufte_get_multilingual_title_value($lang);

    return $translated_title ?: $title;
}
add_filter('wpseo_title', 'gufte_adjust_wpseo_title', 20, 1);

/**
 * Rank Math başlık uyarlaması
 */
function gufte_adjust_rank_math_title($title) {
    if (!is_singular() || !isset($_GET['lang'])) {
        return $title;
    }

    $lang = sanitize_text_field(wp_unslash($_GET['lang']));
    $translated_title = gufte_get_multilingual_title_value($lang);

    return $translated_title ?: $title;
}
add_filter('rank_math/frontend/title', 'gufte_adjust_rank_math_title', 20, 1);

/**
 * All in One SEO başlık uyarlaması
 */
function gufte_adjust_aioseo_title($title) {
    if (!is_singular() || !isset($_GET['lang'])) {
        return $title;
    }

    $lang = sanitize_text_field(wp_unslash($_GET['lang']));
    $translated_title = gufte_get_multilingual_title_value($lang);

    return $translated_title ?: $title;
}
add_filter('aioseo_title', 'gufte_adjust_aioseo_title', 20, 1);

/**
 * Çekirdek wp_get_document_title için son kontrol
 */
function gufte_filter_document_title($title) {
    if (!is_singular() || !isset($_GET['lang'])) {
        return $title;
    }

    $lang = sanitize_text_field(wp_unslash($_GET['lang']));
    $translated_title = gufte_get_multilingual_title_value($lang);

    return $translated_title ?: $title;
}
add_filter('wp_get_document_title', 'gufte_filter_document_title', 50, 1);

/**
 * Başlığı manuel render et (çeviriler için güvenli)
 */
function gufte_render_custom_title_tag() {
    $title = '';

    if (is_singular() && isset($_GET['lang'])) {
        $lang = sanitize_text_field(wp_unslash($_GET['lang']));
        $translated = gufte_get_multilingual_title_value($lang);
        if (!empty($translated)) {
            $title = $translated;
        }
    }

    if (empty($title)) {
        $title = wp_get_document_title();
    }

    echo '<title>' . esc_html($title) . '</title>' . "\n";
}

function gufte_override_title_rendering() {
    if (!current_theme_supports('title-tag')) {
        return;
    }

    remove_action('wp_head', '_wp_render_title_tag', 1);
    add_action('wp_head', 'gufte_render_custom_title_tag', 1);
}
add_action('template_redirect', 'gufte_override_title_rendering', 0);

/**
 * Native dilde meta açıklama oluştur
 */
function gufte_generate_native_meta_description($post_id, $lang, $singer_name = '') {
    $post_title = get_the_title($post_id);
    
    switch($lang) {
        case 'spanish':
            if (!empty($singer_name)) {
                return "Descubre la letra de {$post_title} de {$singer_name} en español. Lee, traduce y disfruta de la letra de esta canción.";
            } else {
                return "Descubre la letra de {$post_title} en español. Lee, traduce y disfruta de la letra de esta canción.";
            }
            
        case 'turkish':
            if (!empty($singer_name)) {
                return "{$singer_name} - {$post_title} şarkısının Türkçe çevirisini keşfedin. Şarkı sözlerini okuyun ve çevirin.";
            } else {
                return "{$post_title} şarkısının Türkçe çevirisini keşfedin. Şarkı sözlerini okuyun ve çevirin.";
            }
            
        case 'german':
            if (!empty($singer_name)) {
                return "Entdecken Sie den Liedtext von {$post_title} von {$singer_name} auf Deutsch. Lesen, übersetzen und genießen Sie den Songtext.";
            } else {
                return "Entdecken Sie den Liedtext von {$post_title} auf Deutsch. Lesen, übersetzen und genießen Sie den Songtext.";
            }
            
        case 'arabic':
            if (!empty($singer_name)) {
                return "اكتشف كلمات أغنية {$post_title} للفنان {$singer_name} باللغة العربية. اقرأ وترجم واستمتع بكلمات الأغنية.";
            } else {
                return "اكتشف كلمات أغنية {$post_title} باللغة العربية. اقرأ وترجم واستمتع بكلمات الأغنية.";
            }
            
        case 'french':
            if (!empty($singer_name)) {
                return "Découvrez les paroles de {$post_title} de {$singer_name} en français. Lisez, traduisez et profitez des paroles de cette chanson.";
            } else {
                return "Découvrez les paroles de {$post_title} en français. Lisez, traduisez et profitez des paroles de cette chanson.";
            }
            
        case 'italian':
            if (!empty($singer_name)) {
                return "Scopri il testo di {$post_title} di {$singer_name} in italiano. Leggi, traduci e goditi il testo di questa canzone.";
            } else {
                return "Scopri il testo di {$post_title} in italiano. Leggi, traduci e goditi il testo di questa canzone.";
            }
            
        case 'portuguese':
            if (!empty($singer_name)) {
                return "Descubra a letra de {$post_title} de {$singer_name} em português. Leia, traduza e aproveite a letra desta música.";
            } else {
                return "Descubra a letra de {$post_title} em português. Leia, traduza e aproveite a letra desta música.";
            }
            
        case 'russian':
            if (!empty($singer_name)) {
                return "Откройте для себя текст песни {$post_title} исполнителя {$singer_name} на русском языке. Читайте, переводите и наслаждайтесь текстом песни.";
            } else {
                return "Откройте для себя текст песни {$post_title} на русском языке. Читайте, переводите и наслаждайтесь текстом песни.";
            }
            
        case 'japanese':
            if (!empty($singer_name)) {
                return "{$singer_name}の{$post_title}の日本語歌詞を発見してください。歌詞を読み、翻訳し、楽しんでください。";
            } else {
                return "{$post_title}の日本語歌詞を発見してください。歌詞を読み、翻訳し、楽しんでください。";
            }
            
        case 'english':
        default:
            if (!empty($singer_name)) {
                return "Discover the lyrics of {$post_title} by {$singer_name} in English. Read, translate and enjoy the song lyrics.";
            } else {
                return "Discover the lyrics of {$post_title} in English. Read, translate and enjoy the song lyrics.";
            }
    }
}

/**
 * Meta açıklama oluştur
 */
function gufte_generate_meta_description($post_id, $lang, $singer_name = '') {
    $post_title = get_the_title($post_id);
    $settings = gufte_get_language_settings();
    $lang_name = $settings['language_map'][$lang] ?? ucfirst($lang) . ' Translation';
    
    if (!empty($singer_name)) {
        return sprintf(
            '%s şarkısının %s çevirisi. %s sanatçısının bu popüler şarkısını farklı dilde keşfedin.',
            $post_title,
            $lang_name,
            $singer_name
        );
    } else {
        return sprintf(
            '%s şarkısının %s çevirisi. Bu popüler şarkıyı farklı dilde keşfedin.',
            $post_title,
            $lang_name
        );
    }
}

/**
 * Çok dilli SEO meta etiketleri (düzeltilmiş versiyon)
 */
function gufte_multilingual_seo_meta() {
    if (!is_singular('post') || !isset($_GET['lang'])) {
        return;
    }
    
    $lang = sanitize_text_field(wp_unslash($_GET['lang']));
    
    if (!gufte_is_valid_language($lang)) {
        return;
    }
    
    $settings = gufte_get_language_settings();
    $post_id = get_the_ID();
    $permalink = get_permalink($post_id);
    // Mevcut çevirileri kontrol et (daha esnek)
    $available_languages = get_post_meta($post_id, '_available_languages', true);
    
    if (!is_array($available_languages)) {
        $available_languages = array();
    }

    $has_translation_list = !empty($available_languages);
    $translation_available = !$has_translation_list || in_array($lang, $available_languages, true);

    // Use clean URL structure instead of query params
    $canonical_url = $translation_available
        ? gufte_get_translation_url($post_id, $lang)
        : $permalink;

    // Eğer available_languages boşsa, tüm dillere izin ver
    // Eğer dolu ise, sadece mevcut dilleri kontrol et
    if (!$translation_available) {
        // Bu dilde çeviri yok, ama yönlendirme yapma
        // Sadece temel meta etiketleri ekle
        echo '<link rel="canonical" href="' . esc_url($canonical_url) . '" />' . "\n";
        echo '<meta name="robots" content="noindex, follow" />' . "\n";
        return;
    }

    // Canonical URL
    echo '<link rel="canonical" href="' . esc_url($canonical_url) . '" />' . "\n";

    // Şarkının orijinal dilini tespit et
    $raw_content = get_post_field('post_content', $post_id);
    $lyrics_languages = array('original' => '', 'translations' => array());
    if (function_exists('gufte_get_lyrics_languages')) {
        $lyrics_languages = gufte_get_lyrics_languages($raw_content);
    }

    // Orijinal dili normalize et
    $original_language = 'english'; // Default
    if (!empty($lyrics_languages['original'])) {
        $original_language = strtolower(trim($lyrics_languages['original']));
    }

    // x-default (orijinal sayfa - her zaman parametresiz)
    echo '<link rel="alternate" hreflang="x-default" href="' . esc_url($permalink) . '" />' . "\n";

    // Orijinal dil için hreflang (parametresiz)
    if (isset($settings['iso_map'][$original_language])) {
        $original_iso = $settings['iso_map'][$original_language];
        echo '<link rel="alternate" hreflang="' . esc_attr($original_iso) . '" href="' . esc_url($permalink) . '" />' . "\n";
    }

    // Hreflang etiketleri - SADECE MEVCUT ÇEVİRİLER İÇİN (orijinal dil hariç)
    if (!empty($available_languages)) {
        foreach ($available_languages as $available_lang) {
            // Orijinal dili atla, zaten yukarıda eklendi
            if ($available_lang === $original_language) {
                continue;
            }

            if (isset($settings['iso_map'][$available_lang])) {
                $iso_code = $settings['iso_map'][$available_lang];
                // Use clean URL structure instead of query params
                $alternate_url = gufte_get_translation_url($post_id, $available_lang);
                echo '<link rel="alternate" hreflang="' . esc_attr($iso_code) . '" href="' . esc_url($alternate_url) . '" />' . "\n";
            }
        }
    }
    
    // Meta bilgileri
    $post_title = get_the_title($post_id);
    $singer_name = gufte_get_singer_name($post_id);
    
    // Native dilde meta açıklama
    $meta_description = gufte_generate_native_meta_description($post_id, $lang, $singer_name);
    
    // Native dilde OG title
    $og_title = gufte_build_multilingual_title($post_title, $singer_name, $lang);
    
    // Meta etiketleri
    echo '<meta name="description" content="' . esc_attr($meta_description) . '" />' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($og_title) . '" />' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($meta_description) . '" />' . "\n";
    echo '<meta property="og:url" content="' . esc_url($canonical_url) . '" />' . "\n";
    echo '<meta property="og:type" content="article" />' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '" />' . "\n";
    $og_locale = $settings['og_locale_map'][$lang] ?? get_locale() ?? 'en_US';
    echo '<meta property="og:locale" content="' . esc_attr($og_locale) . '" />' . "\n";
    
    // Twitter Card
    echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($og_title) . '" />' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($meta_description) . '" />' . "\n";
    
    // Robots meta
    echo '<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />' . "\n";
    
    // Resim varsa ekle
    if (has_post_thumbnail($post_id)) {
        $thumbnail_url = get_the_post_thumbnail_url($post_id, 'large');
        echo '<meta property="og:image" content="' . esc_url($thumbnail_url) . '" />' . "\n";
        echo '<meta name="twitter:image" content="' . esc_url($thumbnail_url) . '" />' . "\n";
    }
}

/**
 * Ana sayfa için hreflang etiketleri ekle
 */
function gufte_add_hreflang_tags() {
    // Sadece tekil yazı sayfalarında çalış
    if (!is_singular()) {
        return;
    }
    
    $post_id = get_the_ID();
    $permalink = get_permalink($post_id);
    
    // Mevcut çevirileri kontrol et
    $available_languages = get_post_meta($post_id, '_available_languages', true);

    $default_locale = str_replace('_', '-', get_locale());
    
    // Eğer çeviri yoksa veya array değilse, sadece x-default ekle
    if (empty($available_languages) || !is_array($available_languages)) {
        if (!empty($default_locale)) {
            echo '<link rel="alternate" hreflang="' . esc_attr($default_locale) . '" href="' . esc_url($permalink) . '" />' . "\n";
        }
        echo '<link rel="alternate" hreflang="x-default" href="' . esc_url($permalink) . '" />' . "\n";
        return;
    }
    
    // Dil ayarlarını al
    $settings = gufte_get_language_settings();

    // Şarkının orijinal dilini tespit et
    $raw_content = get_post_field('post_content', $post_id);
    $lyrics_languages = array('original' => '', 'translations' => array());
    if (function_exists('gufte_get_lyrics_languages')) {
        $lyrics_languages = gufte_get_lyrics_languages($raw_content);
    }

    // Orijinal dili normalize et
    $original_language = 'english'; // Default
    if (!empty($lyrics_languages['original'])) {
        $original_language = strtolower(trim($lyrics_languages['original']));
    }

    // x-default (ana sayfa - orijinal dil)
    echo '<link rel="alternate" hreflang="x-default" href="' . esc_url($permalink) . '" />' . "\n";

    // Orijinal dil için hreflang (parametresiz)
    if (isset($settings['iso_map'][$original_language])) {
        $original_iso = $settings['iso_map'][$original_language];
        echo '<link rel="alternate" hreflang="' . esc_attr($original_iso) . '" href="' . esc_url($permalink) . '" />' . "\n";
    }

    // Her mevcut çeviri için hreflang ekle (sadece çeviriler için ?lang parametresi)
    foreach ($available_languages as $lang) {
        // Orijinal dili atla, zaten yukarıda eklendi
        if ($lang === $original_language) {
            continue;
        }

        if (isset($settings['iso_map'][$lang])) {
            $iso_code = $settings['iso_map'][$lang];
            // Use clean URL structure instead of query params
            $alternate_url = gufte_get_translation_url($post_id, $lang);
            echo '<link rel="alternate" hreflang="' . esc_attr($iso_code) . '" href="' . esc_url($alternate_url) . '" />' . "\n";
        }
    }
}

// Ana sayfa için hreflang'leri ekle (dil parametresi yokken)
add_action('wp_head', function() {
    if (is_singular() && !isset($_GET['lang'])) {
        gufte_add_hreflang_tags();
    }
}, 5);


/**
 * SEO dostu URL'ler için query var ekle
 */
function gufte_add_lang_query_vars($vars) {
    $vars[] = 'lang';
    return $vars;
}
add_filter('query_vars', 'gufte_add_lang_query_vars');

/**
 * Admin panelde dil seçeneklerini göster (isteğe bağlı)
 */
function gufte_add_language_meta_box() {
    add_meta_box(
        'gufte-languages',
        'Çeviri Dilleri',
        'gufte_language_meta_box_callback',
        'post',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'gufte_add_language_meta_box');

function gufte_language_meta_box_callback($post) {
    $settings = gufte_get_language_settings();
    $available_langs = get_post_meta($post->ID, '_available_languages', true);
    if (!is_array($available_langs)) {
        $available_langs = array();
    }

    wp_nonce_field('gufte_language_meta', 'gufte_language_nonce');

    echo '<div class="gufte-language-options">';
    echo '<p><strong>' . esc_html__('Bu şarkı için mevcut çeviri dilleri:', 'gufte') . '</strong></p>';
    echo '<p class="description">' . esc_html__('Seçili diller sitemap\'e dahil edilecek ve arama motorları tarafından dizinlenecektir.', 'gufte') . '</p>';

    echo '<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin: 15px 0;">';

    foreach ($settings['language_map'] as $lang_code => $lang_name) {
        $checked = in_array($lang_code, $available_langs, true) ? 'checked' : '';
        $preview_url = ('english' === $lang_code)
            ? get_permalink($post->ID)
            : add_query_arg('lang', $lang_code, get_permalink($post->ID));

        echo '<label style="display: flex; align-items: center; padding: 8px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">';
        echo '<input type="checkbox" name="available_languages[]" value="' . esc_attr($lang_code) . '" ' . $checked . ' style="margin-right: 8px;">';
        echo '<span style="flex: 1;">' . esc_html($lang_name) . '</span>';
        echo '<a href="' . esc_url($preview_url) . '" target="_blank" style="margin-left: 8px; text-decoration: none; color: #0073aa;">👁️</a>';
        echo '</label>';
    }

    echo '</div>';

    echo '<div style="margin-top: 15px; padding: 10px; background: #e7f3ff; border-left: 4px solid #0073aa;">';
    echo '<strong>' . esc_html__('SEO Bilgisi:', 'gufte') . '</strong><br>';
    echo esc_html__('• Seçili her dil için ayrı bir URL oluşturulacaktır', 'gufte') . '<br>';
    echo esc_html__('• Her dil sayfası kendi meta verilerine sahip olacaktır', 'gufte') . '<br>';
    echo esc_html__('• Sitemap\'e dahil edilecek ve arama motorları tarafından ayrı ayrı dizinlenecektir', 'gufte');
    echo '</div>';

    echo '</div>';
}

function gufte_normalize_language_label($label) {
    $label = remove_accents($label);
    $label = strtolower($label);
    $label = preg_replace('/[^a-z0-9]+/', '', $label);
    return $label;
}

function gufte_save_language_meta($post_id) {
    if (!isset($_POST['gufte_language_nonce']) || !wp_verify_nonce(wp_unslash($_POST['gufte_language_nonce']), 'gufte_language_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $submitted_languages = array();

    if (isset($_POST['available_languages']) && is_array($_POST['available_languages'])) {
        $submitted_languages = array_map('sanitize_text_field', wp_unslash($_POST['available_languages']));
    }

    $settings      = gufte_get_language_settings();
    $language_map  = $settings['language_map'];
    $normalized_map = array();

    foreach ($language_map as $code => $label) {
        $normalized_map[gufte_normalize_language_label($label)] = $code;
        $normalized_map[gufte_normalize_language_label($code)]  = $code;
        $normalized_map[gufte_normalize_language_label(ucwords(str_replace('_', ' ', $code)))] = $code;
    }

    $detected_codes = array();
    $content        = get_post_field('post_content', $post_id);

    if (!empty($content) && function_exists('gufte_get_lyrics_languages')) {
        $lyrics_data = gufte_get_lyrics_languages($content);
        $detected_labels = array();

        if (!empty($lyrics_data['original'])) {
            $detected_labels[] = $lyrics_data['original'];
        }

        if (!empty($lyrics_data['translations']) && is_array($lyrics_data['translations'])) {
            $detected_labels = array_merge($detected_labels, $lyrics_data['translations']);
        }

        foreach ($detected_labels as $label) {
            $normalized = gufte_normalize_language_label($label);
            if (isset($normalized_map[$normalized])) {
                $detected_codes[] = $normalized_map[$normalized];
            }
        }
    }

    $final_languages = array_unique(array_filter(array_merge($submitted_languages, $detected_codes)));

    update_post_meta($post_id, '_available_languages', $final_languages);
}
add_action('save_post', 'gufte_save_language_meta');


/**
 * RSS Feed SEO Optimizasyonu
 * Bu kodu functions.php dosyanıza ekleyin
 */

/**
 * 1. Feed sayfalarına noindex meta etiketi ekle
 */
function gufte_add_feed_noindex() {
    if (is_feed()) {
        echo '<meta name="robots" content="noindex, nofollow" />' . "\n";
    }
}
add_action('wp_head', 'gufte_add_feed_noindex', 1);

/**
 * 2. Feed sayfalarına X-Robots-Tag header ekle
 */
function gufte_add_feed_robots_header() {
    if (is_feed()) {
        header('X-Robots-Tag: noindex, nofollow', true);
    }
}
add_action('template_redirect', 'gufte_add_feed_robots_header');

/**
 * 3. Robots.txt'ye feed disallow kuralları ekle
 */
function gufte_add_feed_robots_txt($output, $public) {
    if ($public) {
        $output .= "\n# RSS Feeds\n";
        $output .= "User-agent: *\n";
        $output .= "Disallow: /feed/\n";
        $output .= "Disallow: /*/feed/\n";
        $output .= "Disallow: /comments/feed/\n";
        $output .= "Disallow: /*/*/feed/\n";
        $output .= "Disallow: /*?feed=*\n";
        $output .= "Disallow: /*&feed=*\n";
        $output .= "\n# Comment Feeds\n";
        $output .= "Disallow: /*/comments/feed/\n";
        $output .= "Disallow: /*/*/comments/feed/\n";
    }
    
    return $output;
}
add_filter('robots_txt', 'gufte_add_feed_robots_txt', 10, 2);

/**
 * 4. Feed URL'lerini canonical etiketlerden çıkar
 */
function gufte_remove_feed_canonical() {
    if (is_feed()) {
        remove_action('wp_head', 'rel_canonical');
    }
}
add_action('template_redirect', 'gufte_remove_feed_canonical');

/**
 * 5. Sitemap'ten feed URL'lerini çıkar (Yoast SEO varsa)
 */
function gufte_exclude_feeds_from_sitemap($url, $type, $object) {
    // Feed URL'lerini sitemap'ten çıkar
    if (strpos($url, '/feed/') !== false || strpos($url, '?feed=') !== false) {
        return false;
    }
    
    return $url;
}
add_filter('wpseo_sitemap_entry', 'gufte_exclude_feeds_from_sitemap', 10, 3);

/**
 * 6. Feed sayfalarını search console'a göndermeme
 */
function gufte_noindex_feed_content($content) {
    if (is_feed()) {
        // Feed içeriğine noindex uyarısı ekle
        $noindex_notice = '<!-- This feed should not be indexed by search engines -->';
        return $noindex_notice . $content;
    }
    
    return $content;
}
add_filter('the_content_feed', 'gufte_noindex_feed_content');
add_filter('the_excerpt_rss', 'gufte_noindex_feed_content');

/**
 * 7. Gereksiz feed linklerini kaldır (isteğe bağlı)
 */
function gufte_remove_unnecessary_feeds() {
    // Yorum feed'lerini kaldır
    remove_action('wp_head', 'feed_links_extra', 3);
    
    // Ana feed linklerini kaldırmak isterseniz (dikkatli olun!)
    // remove_action('wp_head', 'feed_links', 2);
}
add_action('after_setup_theme', 'gufte_remove_unnecessary_feeds');

/**
 * 8. XML-RPC'yi devre dışı bırak (feed güvenliği için)
 */
function gufte_disable_xmlrpc() {
    // XML-RPC'yi tamamen kapat
    add_filter('xmlrpc_enabled', '__return_false');
    
    // XML-RPC methods'ları kaldır
    add_filter('xmlrpc_methods', function($methods) {
        unset($methods['pingback.ping']);
        unset($methods['pingback.extensions.getPingbacks']);
        return $methods;
    });
}
add_action('init', 'gufte_disable_xmlrpc');

/**
 * 9. Feed redirect (isteğe bağlı) - Feed trafiğini ana sayfaya yönlendir
 */
function gufte_redirect_feeds_to_homepage() {
    if (is_feed() && !is_admin()) {
        // 301 redirect ile ana sayfaya yönlendir
        wp_redirect(home_url('/'), 301);
        exit;
    }
}
// Bu satırı aktif etmek isterseniz comment'i kaldırın:
// add_action('template_redirect', 'gufte_redirect_feeds_to_homepage');

/**
 * 10. Google Search Console için ek robots meta
 */
function gufte_enhanced_feed_robots() {
    if (is_feed()) {
        echo '<meta name="googlebot" content="noindex, nofollow, noarchive, nosnippet" />' . "\n";
        echo '<meta name="bingbot" content="noindex, nofollow, noarchive, nosnippet" />' . "\n";
    }
}
add_action('wp_head', 'gufte_enhanced_feed_robots', 1);

/**
 * 11. .htaccess kuralları (manuel olarak eklenecek)
 * 
 * Aşağıdaki kuralları .htaccess dosyanıza manuel olarak ekleyebilirsiniz:
 * 
 * # RSS Feed'leri engellemek için
 * <IfModule mod_rewrite.c>
 * RewriteEngine On
 * 
 * # Bot'lar için feed'leri engelle
 * RewriteCond %{HTTP_USER_AGENT} (googlebot|bingbot|slurp|duckduckbot) [NC]
 * RewriteRule ^(.*)\/feed\/?$ - [F,L]
 * 
 * # Feed header'ları
 * <Files ~ "feed">
 * Header set X-Robots-Tag "noindex, nofollow"
 * </Files>
 * </IfModule>
 */

/**
 * 12. Cache plugin'leri için feed cache'ini temizle
 */
function gufte_clear_feed_cache() {
    // WP Rocket için
    if (function_exists('rocket_clean_domain')) {
        rocket_clean_domain();
    }
    
    // W3 Total Cache için
    if (function_exists('w3tc_flush_all')) {
        w3tc_flush_all();
    }
    
    // WP Super Cache için
    if (function_exists('wp_cache_clear_cache')) {
        wp_cache_clear_cache();
    }
}

// Post güncellendiğinde feed cache'ini temizle
add_action('save_post', 'gufte_clear_feed_cache');
add_action('delete_post', 'gufte_clear_feed_cache');
?>
