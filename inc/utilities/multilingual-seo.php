<?php
/**
 * Çok Dilli SEO İşlevleri
 * Gufte Theme - Multilingual SEO Functions
 * 
 * Bu dosya çok dilli sayfalar için SEO optimizasyonu sağlar
 */

// Doğrudan erişimi engelle
if (!defined('ABSPATH')) {
    exit;
}

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
        ]
    ];
}

/**
 * Çok dilli sayfa başlığı (güncellenmiş - her dil için native başlık)
 */
function gufte_multilingual_page_title($title) {
    if (!is_singular('post') || !isset($_GET['lang'])) {
        return $title;
    }
    
    $lang = sanitize_text_field($_GET['lang']);
    
    if (!gufte_is_valid_language($lang)) {
        return $title;
    }
    
    $settings = gufte_get_language_settings();
    $post_title = get_the_title();
    $singer_name = gufte_get_singer_name();
    
    // Her dil için native başlık oluştur (native_titles array'inden al)
    $suffix = isset($settings['native_titles'][$lang]) ? $settings['native_titles'][$lang] : 'Lyrics and Translation';

    if (!empty($singer_name)) {
        $title = "{$post_title} - {$singer_name} | {$suffix}";
    } else {
        $title = "{$post_title} | {$suffix}";
    }
    
    return $title;
}

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
 * Çok dilli SEO meta etiketleri (güncellenmiş)
 */
function gufte_multilingual_seo_meta() {
    if (!is_singular('post') || !isset($_GET['lang'])) {
        return;
    }
    
    $lang = sanitize_text_field($_GET['lang']);
    
    if (!gufte_is_valid_language($lang)) {
        return;
    }
    
    $settings = gufte_get_language_settings();
    $post_id = get_the_ID();

    // Use clean URL structure instead of query params
    $canonical_url = gufte_get_translation_url($post_id, $lang);

    // Canonical URL
    echo '<link rel="canonical" href="' . esc_url($canonical_url) . '" />' . "\n";

    // Şarkının orijinal dilini tespit et
    $raw_content = get_post_field('post_content', $post_id);
    $lyrics_languages = array('original' => '', 'translations' => array());
    if (function_exists('gufte_get_lyrics_languages')) {
        $lyrics_languages = gufte_get_lyrics_languages($raw_content);
    }

    // Orijinal dili normalize et (boşlukları kaldır, küçük harfe çevir)
    $original_language = 'english'; // Default
    if (!empty($lyrics_languages['original'])) {
        $original_language = strtolower(trim($lyrics_languages['original']));
    }

    // Hreflang etiketleri - use clean URL structure
    $permalink = get_permalink($post_id);
    foreach ($settings['iso_map'] as $lang_slug => $iso_code) {
        // Use clean URL structure instead of query params
        if ($lang_slug === $original_language) {
            $alternate_url = $permalink; // Original language - no suffix
        } else {
            $alternate_url = gufte_get_translation_url($post_id, $lang_slug);
        }
        echo '<link rel="alternate" hreflang="' . esc_attr($iso_code) . '" href="' . esc_url($alternate_url) . '" />' . "\n";
    }

    // x-default (orijinal sayfa - her zaman parametresiz)
    echo '<link rel="alternate" hreflang="x-default" href="' . esc_url($permalink) . '" />' . "\n";
    
    // Meta bilgileri
    $post_title = get_the_title($post_id);
    $singer_name = gufte_get_singer_name($post_id);
    
    // Native dilde meta açıklama
    $meta_description = gufte_generate_native_meta_description($post_id, $lang, $singer_name);
    
    // Native dilde OG title (native_titles array'inden al)
    $suffix = isset($settings['native_titles'][$lang]) ? $settings['native_titles'][$lang] : 'Lyrics and Translation';

    $og_title = !empty($singer_name)
        ? "{$post_title} - {$singer_name} | {$suffix}"
        : "{$post_title} | {$suffix}";
    
    // Meta etiketleri
    echo '<meta name="description" content="' . esc_attr($meta_description) . '" />' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($og_title) . '" />' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($meta_description) . '" />' . "\n";
    echo '<meta property="og:url" content="' . esc_url($canonical_url) . '" />' . "\n";
    echo '<meta property="og:type" content="article" />' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '" />' . "\n";
    echo '<meta property="og:locale" content="' . esc_attr($settings['iso_map'][$lang]) . '" />' . "\n";
    
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
    
    // JSON-LD structured data
    gufte_output_multilingual_json_ld($post_id, $lang, $canonical_url, $singer_name, $meta_description);
}

/**
 * Çok dilli JSON-LD yapılandırılmış veri (güncellenmiş)
 */
function gufte_output_multilingual_json_ld($post_id, $lang, $url, $singer_name = '', $description = '') {
    $settings = gufte_get_language_settings();
    $post_title = get_the_title($post_id);
    
    // Native dilde başlık oluştur (native_titles array'inden al)
    $suffix = isset($settings['native_titles'][$lang]) ? $settings['native_titles'][$lang] : 'Lyrics and Translation';

    $structured_title = !empty($singer_name)
        ? "{$post_title} - {$singer_name} | {$suffix}"
        : "{$post_title} | {$suffix}";
    
    $json_ld = [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => $structured_title,
        'description' => $description,
        'url' => $url,
        'inLanguage' => $settings['iso_map'][$lang],
        'datePublished' => get_the_date('c', $post_id),
        'dateModified' => get_the_modified_date('c', $post_id),
        'isPartOf' => [
            '@type' => 'WebSite',
            'name' => get_bloginfo('name'),
            'url' => home_url()
        ]
    ];
    
    // Şarkıcı varsa MusicComposition ekle
    if (!empty($singer_name)) {
        $json_ld['mainEntity'] = [
            '@type' => 'MusicComposition',
            'name' => $post_title,
            'inLanguage' => $settings['iso_map'][$lang],
            'composer' => [
                '@type' => 'Person',
                'name' => $singer_name
            ]
        ];
        
        // Şarkıcı bilgilerini genişlet
        $singers = get_the_terms($post_id, 'singer');
        if ($singers && !is_wp_error($singers)) {
            $singer_term = reset($singers);
            $json_ld['mainEntity']['composer']['url'] = get_term_link($singer_term);
            
            // Şarkıcının gerçek adı varsa ekle
            $real_name = get_term_meta($singer_term->term_id, 'real_name', true);
            if (!empty($real_name)) {
                $json_ld['mainEntity']['composer']['alternateName'] = $real_name;
            }
        }
    }
    
    // Breadcrumb ekle
    $json_ld['breadcrumb'] = [
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => get_bloginfo('name'),
                'item' => home_url()
            ],
            [
                '@type' => 'ListItem', 
                'position' => 2,
                'name' => 'Lyrics',
                'item' => home_url('/lyrics/')
            ]
        ]
    ];
    
    // Şarkıcı varsa breadcrumb'a ekle
    if (!empty($singer_name)) {
        $singers = get_the_terms($post_id, 'singer');
        if ($singers && !is_wp_error($singers)) {
            $singer_term = reset($singers);
            $json_ld['breadcrumb']['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $singer_name,
                'item' => get_term_link($singer_term)
            ];
        }
    }
    
    // Mevcut sayfa
    $json_ld['breadcrumb']['itemListElement'][] = [
        '@type' => 'ListItem',
        'position' => count($json_ld['breadcrumb']['itemListElement']) + 1,
        'name' => $structured_title,
        'item' => $url
    ];
    
    // Resim varsa ekle
    if (has_post_thumbnail($post_id)) {
        $json_ld['image'] = [
            '@type' => 'ImageObject',
            'url' => get_the_post_thumbnail_url($post_id, 'large'),
            'width' => 1200,
            'height' => 630
        ];
    }
    
    echo '<script type="application/ld+json">' . "\n";
    echo wp_json_encode($json_ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    echo "\n" . '</script>' . "\n";
}

/**
 * Çok dilli SEO meta etiketlerini aktif et
 */
function gufte_activate_multilingual_seo() {
    // Mevcut meta description fonksiyonunu çok dilli sayfalar için devre dışı bırak
    if (is_singular('post') && isset($_GET['lang'])) {
        remove_action('wp_head', 'gufte_add_meta_description', 1);
        add_action('wp_head', 'gufte_multilingual_seo_meta', 1);
    }
}

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
 * SEO dostu URL'ler için query var ekle
 */
function gufte_add_lang_query_vars($vars) {
    $vars[] = 'lang';
    return $vars;
}

/**
 * Featured image'a otomatik alt text ekle
 */
function gufte_auto_add_alt_text($html, $post_id, $post_thumbnail_id, $size, $attr) {
    // Context'i belirle
    $context = 'default';
    $language = null;
    
    // Dil kontrolü - URL parametresinden veya query var'dan al
    if (isset($_GET['lang'])) {
        $language = sanitize_text_field($_GET['lang']);
        $context = 'translation';
    } elseif (get_query_var('lang')) {
        $language = sanitize_text_field(get_query_var('lang'));
        $context = 'translation';
    }
    
    // Eğer dil parametresi yoksa normal context belirleme
    if (!$language) {
        if (is_single()) {
            $context = 'single';
        } elseif (is_archive()) {
            if (is_tax('singer')) {
                $context = 'singer';
            } elseif (is_tax('album')) {
                $context = 'album';
            } else {
                $context = 'archive';
            }
        } elseif (is_search()) {
            $context = 'search';
        }
    }
    
    // Dile özel cache key oluştur
    $cache_key = '_wp_attachment_image_alt';
    if ($language) {
        $cache_key .= '_' . $language;
    }
    
    // Dile özel alt text'i kontrol et
    $alt_text = get_post_meta($post_thumbnail_id, $cache_key, true);
    
    // Eğer dile özel alt text yoksa oluştur
    if (empty($alt_text)) {
        $alt_text = gufte_generate_auto_alt_text($post_id, $context, $language);
        
        // Dile özel olarak kaydet (cache için)
        if (!empty($alt_text)) {
            update_post_meta($post_thumbnail_id, $cache_key, $alt_text);
        }
    }
    
    // HTML'e alt attribute ekle veya güncelle
    if (strpos($html, 'alt=') === false) {
        $html = str_replace('<img', '<img alt="' . esc_attr($alt_text) . '"', $html);
    } else {
        // Mevcut alt text'i güncelle
        $html = preg_replace('/alt="[^"]*"/', 'alt="' . esc_attr($alt_text) . '"', $html);
    }
    
    return $html;
}
add_filter('post_thumbnail_html', 'gufte_auto_add_alt_text', 10, 5);

/**
 * get_the_post_thumbnail fonksiyonu için alt text override
 * Bu daha erken aşamada çalışır
 */
function gufte_override_thumbnail_attr($attr, $attachment, $size) {
    // Sadece frontend'de çalış
    if (is_admin()) {
        return $attr;
    }
    
    // Post ID'yi al
    global $post;
    if (!$post) {
        return $attr;
    }
    
    $post_id = $post->ID;
    $thumbnail_id = $attachment->ID;
    
    // Dil kontrolü
    $language = null;
    $context = 'default';
    
    if (isset($_GET['lang'])) {
        $language = sanitize_text_field($_GET['lang']);
        $context = 'translation';
    } elseif (get_query_var('lang')) {
        $language = sanitize_text_field(get_query_var('lang'));
        $context = 'translation';
    }
    
    // Context belirleme
    if (!$language) {
        if (is_single()) {
            $context = 'single';
        } elseif (is_archive()) {
            if (is_tax('singer')) {
                $context = 'singer';
            } elseif (is_tax('album')) {
                $context = 'album';
            } else {
                $context = 'archive';
            }
        } elseif (is_search()) {
            $context = 'search';
        }
    }
    
    // Alt text oluştur
    $alt_text = gufte_generate_auto_alt_text($post_id, $context, $language);
    
    // Alt text'i attribute'a ekle
    if (!empty($alt_text)) {
        $attr['alt'] = $alt_text;
    }
    
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'gufte_override_thumbnail_attr', 20, 3);

/**
 * Attachment upload edildiğinde otomatik alt text ekle
 */
function gufte_set_attachment_alt_on_upload($attachment_id) {
    // Parent post ID'yi al
    $parent_id = wp_get_post_parent_id($attachment_id);
    
    if ($parent_id) {
        // Parent post'un tipini kontrol et
        if (get_post_type($parent_id) === 'post') {
            // Alt text oluştur
            $alt_text = gufte_generate_auto_alt_text($parent_id, 'default');
            
            // Alt text'i kaydet
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt_text);
        }
    }
}
add_action('add_attachment', 'gufte_set_attachment_alt_on_upload');

// Hook'ları kaydet
add_filter('pre_get_document_title', 'gufte_multilingual_page_title', 10, 1);
add_action('wp_head', 'gufte_activate_multilingual_seo', 0);
add_filter('query_vars', 'gufte_add_lang_query_vars');
?>