<?php
/**
 * Translation SEO Handler
 * Handles SEO meta tags for translation URLs (/post-slug/tr/, /post-slug/es/)
 *
 * @package Arcuras
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Language settings with native names
 */
function arcuras_get_language_seo_data() {
    // Get SEO settings from database
    $db_seo_settings = get_option('arcuras_language_seo_settings', array());

    // Get language term data (includes all languages with their names)
    if (function_exists('arcuras_get_language_term_data')) {
        $language_data = arcuras_get_language_term_data();
        $merged_data = array();

        foreach ($language_data as $lang_key => $lang_info) {
            $iso_code = isset($lang_info['iso_code']) ? $lang_info['iso_code'] : $lang_key;

            // Check if we have database SEO settings for this language
            if (isset($db_seo_settings[$iso_code]) && !empty($db_seo_settings[$iso_code]['original_suffix'])) {
                $merged_data[$iso_code] = array(
                    'name' => isset($lang_info['name']) ? $lang_info['name'] : $lang_key,
                    'native_name' => isset($lang_info['name']) ? $lang_info['name'] : $lang_key,
                    'title_suffix' => isset($db_seo_settings[$iso_code]['translation_suffix'])
                        ? $db_seo_settings[$iso_code]['translation_suffix']
                        : 'Translation',
                    'original_suffix' => $db_seo_settings[$iso_code]['original_suffix'],
                    'meta_template' => isset($db_seo_settings[$iso_code]['translation_meta_suffix'])
                        ? $db_seo_settings[$iso_code]['translation_meta_suffix']
                        : 'Translated lyrics with original text and annotations'
                );
            }
        }
    }

    // Hardcoded fallback data (for backward compatibility)
    $fallback_data = array(
        'en' => array(
            'name' => 'English',
            'native_name' => 'English',
            'title_suffix' => 'Lyrics and English Translation',
            'original_suffix' => 'Lyrics, Translations and Annotations',
            'meta_template' => 'Discover the lyrics of %s%s in English. Read, translate and enjoy the lyrics of this song.'
        ),
        'es' => array(
            'name' => 'Spanish',
            'native_name' => 'Español',
            'title_suffix' => 'Letras y Traducción al Español',
            'original_suffix' => 'Letras, Traducciones y Anotaciones',
            'meta_template' => 'Descubre la letra de %s%s en español. Lee, traduce y disfruta de la letra de esta canción.'
        ),
        'tr' => array(
            'name' => 'Turkish',
            'native_name' => 'Türkçe',
            'title_suffix' => 'Şarkı Sözleri ve Türkçe Çevirisi',
            'original_suffix' => 'Şarkı Sözleri, Çeviriler ve Açıklamalar',
            'meta_template' => '%s%s şarkı sözleri ve Türkçe çevirisi. Şarkı sözlerini okuyun, çevirin ve keyfini çıkarın.'
        ),
        'de' => array(
            'name' => 'German',
            'native_name' => 'Deutsch',
            'title_suffix' => 'Liedtext und Deutsche Übersetzung',
            'original_suffix' => 'Liedtext, Übersetzungen und Anmerkungen',
            'meta_template' => 'Entdecken Sie den Text von %s%s auf Deutsch. Lesen, übersetzen und genießen Sie den Text dieses Liedes.'
        ),
        'fr' => array(
            'name' => 'French',
            'native_name' => 'Français',
            'title_suffix' => 'Paroles et Traduction en Français',
            'original_suffix' => 'Paroles, Traductions et Annotations',
            'meta_template' => 'Découvrez les paroles de %s%s en français. Lisez, traduisez et profitez des paroles de cette chanson.'
        ),
        'ar' => array(
            'name' => 'Arabic',
            'native_name' => 'العربية',
            'title_suffix' => 'كلمات الأغنية والترجمة العربية',
            'original_suffix' => 'كلمات الأغنية والترجمات والتعليقات',
            'meta_template' => 'اكتشف كلمات %s%s بالعربية. اقرأ وترجم واستمتع بكلمات هذه الأغنية.'
        ),
        'it' => array(
            'name' => 'Italian',
            'native_name' => 'Italiano',
            'title_suffix' => 'Testo e Traduzione in Italiano',
            'original_suffix' => 'Testo, Traduzioni e Annotazioni',
            'meta_template' => 'Scopri il testo di %s%s in italiano. Leggi, traduci e goditi il testo di questa canzone.'
        ),
        'pt' => array(
            'name' => 'Portuguese',
            'native_name' => 'Português',
            'title_suffix' => 'Letras e Tradução em Português',
            'original_suffix' => 'Letras, Traduções e Anotações',
            'meta_template' => 'Descubra a letra de %s%s em português. Leia, traduza e aproveite a letra desta música.'
        ),
        'ru' => array(
            'name' => 'Russian',
            'native_name' => 'Русский',
            'title_suffix' => 'Текст песни и русский перевод',
            'original_suffix' => 'Текст песни, переводы и комментарии',
            'meta_template' => 'Откройте для себя текст %s%s на русском языке. Читайте, переводите и наслаждайтесь текстом этой песни.'
        ),
        'ja' => array(
            'name' => 'Japanese',
            'native_name' => '日本語',
            'title_suffix' => '歌詞と日本語翻訳',
            'original_suffix' => '歌詞、翻訳、注釈',
            'meta_template' => '%s%sの歌詞を日本語で発見してください。この曲の歌詞を読み、翻訳し、楽しんでください。'
        ),
        'ko' => array(
            'name' => 'Korean',
            'native_name' => '한국어',
            'title_suffix' => '가사 및 한국어 번역',
            'original_suffix' => '가사, 번역 및 주석',
            'meta_template' => '%s%s의 가사를 한국어로 확인하세요. 이 노래의 가사를 읽고, 번역하고, 즐기세요.'
        ),
        'zh' => array(
            'name' => 'Chinese',
            'native_name' => '中文',
            'title_suffix' => '歌词和中文翻译',
            'original_suffix' => '歌词、翻译和注释',
            'meta_template' => '发现%s%s的中文歌词。阅读、翻译并享受这首歌的歌词。'
        ),
        'hi' => array(
            'name' => 'Hindi',
            'native_name' => 'हिन्दी',
            'title_suffix' => 'गीत और हिंदी अनुवाद',
            'original_suffix' => 'गीत, अनुवाद और टिप्पणियाँ',
            'meta_template' => '%s%s के गीत हिंदी में खोजें। इस गीत के बोल पढ़ें, अनुवाद करें और आनंद लें।'
        ),
        'nl' => array(
            'name' => 'Dutch',
            'native_name' => 'Nederlands',
            'title_suffix' => 'Songtekst en Nederlandse Vertaling',
            'original_suffix' => 'Songteksten, Vertalingen en Annotaties',
            'meta_template' => 'Ontdek de tekst van %s%s in het Nederlands. Lees, vertaal en geniet van de songtekst.'
        ),
        'pl' => array(
            'name' => 'Polish',
            'native_name' => 'Polski',
            'title_suffix' => 'Tekst Piosenki i Polskie Tłumaczenie',
            'original_suffix' => 'Teksty Piosenek, Tłumaczenia i Adnotacje',
            'meta_template' => 'Odkryj tekst %s%s po polsku. Czytaj, tłumacz i ciesz się tekstem tej piosenki.'
        ),
        'sv' => array(
            'name' => 'Swedish',
            'native_name' => 'Svenska',
            'title_suffix' => 'Låttext och Svensk Översättning',
            'original_suffix' => 'Texter, Översättningar och Kommentarer',
            'meta_template' => 'Upptäck texten till %s%s på svenska. Läs, översätt och njut av låttexten.'
        ),
        'no' => array(
            'name' => 'Norwegian',
            'native_name' => 'Norsk',
            'title_suffix' => 'Sangtekst og Norsk Oversettelse',
            'original_suffix' => 'Tekster, Oversettelser og Merknader',
            'meta_template' => 'Oppdag teksten til %s%s på norsk. Les, oversett og nyt sangteksten.'
        ),
        'da' => array(
            'name' => 'Danish',
            'native_name' => 'Dansk',
            'title_suffix' => 'Sangtekst og Dansk Oversættelse',
            'original_suffix' => 'Tekster, Oversættelser og Annotationer',
            'meta_template' => 'Opdag teksten til %s%s på dansk. Læs, oversæt og nyd sangteksten.'
        ),
        'fi' => array(
            'name' => 'Finnish',
            'native_name' => 'Suomi',
            'title_suffix' => 'Sanoitukset ja Suomenkielinen Käännös',
            'original_suffix' => 'Sanoitukset, Käännökset ja Huomautukset',
            'meta_template' => 'Löydä %s%s -kappaleen sanat suomeksi. Lue, käännä ja nauti kappaleen sanoituksista.'
        ),
        'el' => array(
            'name' => 'Greek',
            'native_name' => 'Ελληνικά',
            'title_suffix' => 'Στίχοι και Ελληνική Μετάφραση',
            'original_suffix' => 'Στίχοι, Μεταφράσεις και Σημειώσεις',
            'meta_template' => 'Ανακαλύψτε τους στίχους του %s%s στα ελληνικά. Διαβάστε, μεταφράστε και απολαύστε τους στίχους αυτού του τραγουδιού.'
        ),
        'he' => array(
            'name' => 'Hebrew',
            'native_name' => 'עברית',
            'title_suffix' => 'מילים ותרגום לעברית',
            'original_suffix' => 'מילים, תרגומים והערות',
            'meta_template' => 'גלה את המילים של %s%s בעברית. קרא, תרגם ותהנה מהמילים של השיר.'
        ),
        'uk' => array(
            'name' => 'Ukrainian',
            'native_name' => 'Українська',
            'title_suffix' => 'Текст пісні та український переклад',
            'original_suffix' => 'Тексти пісень, переклади та коментарі',
            'meta_template' => 'Відкрийте для себе текст %s%s українською мовою. Читайте, перекладайте та насолоджуйтеся текстом цієї пісні.'
        ),
        'cs' => array(
            'name' => 'Czech',
            'native_name' => 'Čeština',
            'title_suffix' => 'Text Písně a Český Překlad',
            'original_suffix' => 'Texty, Překlady a Poznámky',
            'meta_template' => 'Objevte text %s%s v češtině. Čtěte, překládejte a užívejte si text této písně.'
        ),
        'ro' => array(
            'name' => 'Romanian',
            'native_name' => 'Română',
            'title_suffix' => 'Versuri și Traducere în Română',
            'original_suffix' => 'Versuri, Traduceri și Adnotări',
            'meta_template' => 'Descoperă versurile %s%s în română. Citește, traduce și bucură-te de versurile acestui cântec.'
        ),
        'hu' => array(
            'name' => 'Hungarian',
            'native_name' => 'Magyar',
            'title_suffix' => 'Dalszöveg és Magyar Fordítás',
            'original_suffix' => 'Dalszövegek, Fordítások és Megjegyzések',
            'meta_template' => 'Fedezd fel %s%s dalszövegét magyarul. Olvasd, fordítsd és élvezd a dalszöveget.'
        ),
        'th' => array(
            'name' => 'Thai',
            'native_name' => 'ไทย',
            'title_suffix' => 'เนื้อเพลงและแปลภาษาไทย',
            'original_suffix' => 'เนื้อเพลง, การแปล และคำอธิบาย',
            'meta_template' => 'ค้นพบเนื้อเพลง %s%s เป็นภาษาไทย อ่าน แปล และเพลิดเพลินกับเนื้อเพลงนี้'
        ),
        'vi' => array(
            'name' => 'Vietnamese',
            'native_name' => 'Tiếng Việt',
            'title_suffix' => 'Lời Bài Hát và Bản Dịch Tiếng Việt',
            'original_suffix' => 'Lời bài hát, Bản dịch và Chú thích',
            'meta_template' => 'Khám phá lời bài hát %s%s bằng tiếng Việt. Đọc, dịch và thưởng thức lời bài hát này.'
        ),
        'id' => array(
            'name' => 'Indonesian',
            'native_name' => 'Bahasa Indonesia',
            'title_suffix' => 'Lirik dan Terjemahan Bahasa Indonesia',
            'original_suffix' => 'Lirik, Terjemahan dan Anotasi',
            'meta_template' => 'Temukan lirik %s%s dalam bahasa Indonesia. Baca, terjemahkan dan nikmati lirik lagu ini.'
        )
    );

    // Merge database data with fallback data (database takes priority)
    if (isset($merged_data) && !empty($merged_data)) {
        return array_merge($fallback_data, $merged_data);
    }

    return $fallback_data;
}

/**
 * Get original language code from lyrics block
 */
function arcuras_get_original_language_code($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $post = get_post($post_id);
    if (!$post || !has_blocks($post->post_content)) {
        return 'en'; // Default to English
    }

    $blocks = parse_blocks($post->post_content);

    foreach ($blocks as $block) {
        if ($block['blockName'] === 'arcuras/lyrics-translations' && !empty($block['attrs']['languages'])) {
            foreach ($block['attrs']['languages'] as $lang) {
                // Handle both boolean and string values for isOriginal
                $is_original = isset($lang['isOriginal']) &&
                              ($lang['isOriginal'] === true ||
                               $lang['isOriginal'] === 'true' ||
                               $lang['isOriginal'] === 1 ||
                               $lang['isOriginal'] === '1');

                if ($is_original && !empty($lang['code'])) {
                    return $lang['code'];
                }
            }
        }
    }

    return 'en'; // Default to English
}

/**
 * Get meta description suffix based on original language
 */
function arcuras_get_meta_description_suffix($original_lang = 'en') {
    $suffixes = array(
        'en' => 'Read lyrics, discover translations in multiple languages, and explore detailed annotations',
        'es' => 'Lee las letras, descubre traducciones en varios idiomas y explora anotaciones detalladas',
        'tr' => 'Şarkı sözlerini okuyun, birden fazla dilde çevirileri keşfedin ve detaylı açıklamaları inceleyin',
        'de' => 'Lesen Sie die Texte, entdecken Sie Übersetzungen in mehreren Sprachen und erkunden Sie detaillierte Anmerkungen',
        'fr' => 'Lisez les paroles, découvrez les traductions en plusieurs langues et explorez les annotations détaillées',
        'ar' => 'اقرأ الكلمات، اكتشف الترجمات بعدة لغات، واستكشف التعليقات التفصيلية',
        'it' => 'Leggi i testi, scopri le traduzioni in più lingue ed esplora le annotazioni dettagliate',
        'pt' => 'Leia as letras, descubra traduções em vários idiomas e explore anotações detalhadas',
        'ru' => 'Читайте тексты, открывайте переводы на несколько языков и изучайте подробные комментарии',
        'ja' => '歌詞を読み、複数言語の翻訳を発見し、詳細な注釈を探索してください',
        'ko' => '가사를 읽고, 여러 언어로 된 번역을 발견하고, 자세한 주석을 살펴보세요'
    );

    return isset($suffixes[$original_lang]) ? $suffixes[$original_lang] : $suffixes['en'];
}

/**
 * Modify page title for translation URLs
 */
function arcuras_translation_title($title) {
    if (!is_singular(array('post', 'lyrics'))) {
        return $title;
    }

    $lang = gufte_get_current_translation_lang();
    $post_title = get_the_title();
    $singer_name = arcuras_get_singer_name();
    $lang_data = arcuras_get_language_seo_data();

    // If no translation language, show original with enhanced title based on original language
    if (empty($lang)) {
        $original_lang = arcuras_get_original_language_code();
        $original_suffix = isset($lang_data[$original_lang]['original_suffix'])
            ? $lang_data[$original_lang]['original_suffix']
            : 'Lyrics, Translations and Annotations';

        if (!empty($singer_name)) {
            return "{$post_title} - {$singer_name} | {$original_suffix}";
        } else {
            return "{$post_title} | {$original_suffix}";
        }
    }

    if (!isset($lang_data[$lang])) {
        return $title;
    }

    $title_suffix = $lang_data[$lang]['title_suffix'];

    if (!empty($singer_name)) {
        return "{$post_title} - {$singer_name} | {$title_suffix}";
    } else {
        return "{$post_title} | {$title_suffix}";
    }
}
add_filter('pre_get_document_title', 'arcuras_translation_title', 20);
add_filter('wp_title', 'arcuras_translation_title', 20);

/**
 * Get singer name from taxonomy
 */
function arcuras_get_singer_name($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $singers = get_the_terms($post_id, 'singer');

    if (!empty($singers) && !is_wp_error($singers)) {
        return $singers[0]->name;
    }

    return '';
}

/**
 * Add/modify meta description for translation URLs
 */
function arcuras_translation_meta_description() {
    if (!is_singular(array('post', 'lyrics'))) {
        return;
    }

    $lang = gufte_get_current_translation_lang();

    if (empty($lang)) {
        return;
    }

    $lang_data = arcuras_get_language_seo_data();

    if (!isset($lang_data[$lang])) {
        return;
    }

    $post_title = get_the_title();
    $singer_name = arcuras_get_singer_name();

    // Format: "song title by artist name" or just "song title"
    $singer_part = !empty($singer_name) ? " by {$singer_name}" : '';

    $description = sprintf(
        $lang_data[$lang]['meta_template'],
        $post_title,
        $singer_part
    );

    echo '<meta name="description" content="' . esc_attr($description) . '" />' . "\n";
}
add_action('wp_head', 'arcuras_translation_meta_description', 1);

/**
 * Add Open Graph tags for translations
 */
function arcuras_translation_og_tags() {
    if (!is_singular(array('post', 'lyrics'))) {
        return;
    }

    $lang = gufte_get_current_translation_lang();

    if (empty($lang)) {
        return;
    }

    $lang_data = arcuras_get_language_seo_data();

    if (!isset($lang_data[$lang])) {
        return;
    }

    $post_id = get_the_ID();
    $post_title = get_the_title();
    $singer_name = arcuras_get_singer_name();
    $current_url = gufte_get_translation_url($post_id, $lang);

    $og_title = !empty($singer_name)
        ? "{$post_title} - {$singer_name} | {$lang_data[$lang]['title_suffix']}"
        : "{$post_title} | {$lang_data[$lang]['title_suffix']}";

    $singer_part = !empty($singer_name) ? " by {$singer_name}" : '';
    $og_description = sprintf(
        $lang_data[$lang]['meta_template'],
        $post_title,
        $singer_part
    );

    echo '<meta property="og:title" content="' . esc_attr($og_title) . '" />' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($og_description) . '" />' . "\n";
    echo '<meta property="og:url" content="' . esc_url($current_url) . '" />' . "\n";
    echo '<meta property="og:locale" content="' . esc_attr($lang . '_' . strtoupper($lang)) . '" />' . "\n";

    // Add article tags
    echo '<meta property="og:type" content="article" />' . "\n";

    // Add thumbnail if exists
    if (has_post_thumbnail($post_id)) {
        $thumbnail_url = get_the_post_thumbnail_url($post_id, 'large');
        echo '<meta property="og:image" content="' . esc_url($thumbnail_url) . '" />' . "\n";
    }

    // Twitter Card
    echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($og_title) . '" />' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($og_description) . '" />' . "\n";
}
add_action('wp_head', 'arcuras_translation_og_tags', 2);

/**
 * Modify canonical URL for translations
 */
function arcuras_translation_canonical() {
    if (!is_singular(array('post', 'lyrics'))) {
        return;
    }

    $lang = gufte_get_current_translation_lang();

    if (empty($lang)) {
        return;
    }

    $post_id = get_the_ID();
    $canonical_url = gufte_get_translation_url($post_id, $lang);

    // Remove default canonical
    remove_action('wp_head', 'rel_canonical');

    // Add translation canonical
    echo '<link rel="canonical" href="' . esc_url($canonical_url) . '" />' . "\n";
}
add_action('wp_head', 'arcuras_translation_canonical', 1);

/**
 * Register REST API endpoint for translation meta
 */
function arcuras_register_translation_meta_api() {
    register_rest_route('arcuras/v1', '/translation-meta', array(
        'methods' => 'GET',
        'callback' => 'arcuras_get_translation_meta_api',
        'permission_callback' => '__return_true',
        'args' => array(
            'post_id' => array(
                'required' => true,
                'type' => 'integer',
                'sanitize_callback' => 'absint'
            ),
            'lang' => array(
                'required' => false,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field'
            )
        )
    ));
}
add_action('rest_api_init', 'arcuras_register_translation_meta_api');

/**
 * API callback to get translation meta data
 */
function arcuras_get_translation_meta_api($request) {
    $post_id = $request->get_param('post_id');
    $lang = $request->get_param('lang');

    $post = get_post($post_id);

    if (!$post || !in_array($post->post_type, array('post', 'lyrics'))) {
        return new WP_Error('invalid_post', 'Invalid post ID', array('status' => 404));
    }

    $lang_data = arcuras_get_language_seo_data();

    // If no language specified or language not valid, return original
    if (empty($lang) || !isset($lang_data[$lang])) {
        $singer_name = arcuras_get_singer_name($post_id);
        $title = !empty($singer_name)
            ? get_the_title($post_id) . ' - ' . $singer_name
            : get_the_title($post_id);

        return array(
            'title' => $title,
            'description' => get_the_excerpt($post_id),
            'canonical' => get_permalink($post_id),
            'og_title' => $title,
            'og_description' => get_the_excerpt($post_id),
            'og_url' => get_permalink($post_id)
        );
    }

    // Get translation meta
    $singer_name = arcuras_get_singer_name($post_id);
    $post_title = get_the_title($post_id);

    $title_suffix = $lang_data[$lang]['title_suffix'];
    $title = !empty($singer_name)
        ? "{$post_title} - {$singer_name} | {$title_suffix}"
        : "{$post_title} | {$title_suffix}";

    $singer_part = !empty($singer_name) ? " by {$singer_name}" : '';
    $description = sprintf(
        $lang_data[$lang]['meta_template'],
        $post_title,
        $singer_part
    );

    $translation_url = gufte_get_translation_url($post_id, $lang);

    return array(
        'title' => $title,
        'description' => $description,
        'canonical' => $translation_url,
        'og_title' => $title,
        'og_description' => $description,
        'og_url' => $translation_url,
        'lang' => $lang,
        'lang_name' => $lang_data[$lang]['name']
    );
}
