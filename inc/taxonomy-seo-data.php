<?php
/**
 * Taxonomy SEO Data - Title and Description for Language Terms
 *
 * This file contains SEO-optimized titles and descriptions for:
 * - Original Language taxonomy (original_language)
 * - Translated Language taxonomy (translated_language)
 *
 * @package Arcuras
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Original Language SEO Data
 */
function arcuras_get_original_language_seo_data() {
    return array(
        'english' => array(
            'title' => 'English Song Lyrics | Original English Music',
            'description' => 'Discover original English song lyrics from your favorite artists. Browse our extensive collection of English music lyrics with translations.',
        ),
        'turkish' => array(
            'title' => 'Türkçe Şarkı Sözleri | Orijinal Türkçe Müzik',
            'description' => 'Sevdiğiniz sanatçıların orijinal Türkçe şarkı sözlerini keşfedin. Çevirileri ile birlikte geniş Türkçe müzik sözleri koleksiyonumuza göz atın.',
        ),
        'spanish' => array(
            'title' => 'Letras de Canciones en Español | Música Original en Español',
            'description' => 'Descubre letras de canciones originales en español de tus artistas favoritos. Explora nuestra extensa colección de letras de música en español con traducciones.',
        ),
        'french' => array(
            'title' => 'Paroles de Chansons en Français | Musique Française Originale',
            'description' => 'Découvrez les paroles originales de chansons françaises de vos artistes préférés. Parcourez notre vaste collection de paroles de musique française avec traductions.',
        ),
        'italian' => array(
            'title' => 'Testi di Canzoni in Italiano | Musica Italiana Originale',
            'description' => 'Scopri i testi originali delle canzoni italiane dei tuoi artisti preferiti. Sfoglia la nostra vasta collezione di testi musicali italiani con traduzioni.',
        ),
        'korean' => array(
            'title' => '한국어 노래 가사 | 오리지널 K-Pop 음악',
            'description' => '좋아하는 아티스트의 오리지널 한국어 노래 가사를 발견하세요. 번역과 함께 한국 음악 가사의 광범위한 컬렉션을 탐색하세요.',
        ),
        'japanese' => array(
            'title' => '日本語の歌詞 | オリジナル日本音楽',
            'description' => 'お気に入りのアーティストのオリジナル日本語歌詞を発見してください。翻訳付きの日本音楽歌詞の広範なコレクションを閲覧してください。',
        ),
        'german' => array(
            'title' => 'Deutsche Liedtexte | Originale Deutsche Musik',
            'description' => 'Entdecken Sie originale deutsche Liedtexte Ihrer Lieblingsartisten. Durchsuchen Sie unsere umfangreiche Sammlung deutscher Musiktexte mit Übersetzungen.',
        ),
        'portuguese' => array(
            'title' => 'Letras de Músicas em Português | Música Portuguesa Original',
            'description' => 'Descubra letras originais de músicas em português dos seus artistas favoritos. Navegue por nossa extensa coleção de letras de música portuguesa com traduções.',
        ),
        'russian' => array(
            'title' => 'Русские Тексты Песен | Оригинальная Русская Музыка',
            'description' => 'Откройте для себя оригинальные тексты русских песен ваших любимых артистов. Просмотрите нашу обширную коллекцию текстов русской музыки с переводами.',
        ),
        'arabic' => array(
            'title' => 'كلمات الأغاني العربية | الموسيقى العربية الأصلية',
            'description' => 'اكتشف كلمات الأغاني العربية الأصلية من فنانيك المفضلين. تصفح مجموعتنا الواسعة من كلمات الموسيقى العربية مع الترجمات.',
        ),
        'hindi' => array(
            'title' => 'हिंदी गाने के बोल | मूल हिंदी संगीत',
            'description' => 'अपने पसंदीदा कलाकारों के मूल हिंदी गाने के बोल खोजें। अनुवाद के साथ हमारे व्यापक हिंदी संगीत बोल संग्रह को ब्राउज़ करें।',
        ),
    );
}

/**
 * Translated Language SEO Data
 */
function arcuras_get_translated_language_seo_data() {
    return array(
        'english' => array(
            'title' => 'Song Lyrics Translated to English | English Translations',
            'description' => 'Browse songs translated into English from various languages. Find accurate English translations of your favorite international music lyrics.',
        ),
        'turkish' => array(
            'title' => 'Türkçe Çevrilmiş Şarkı Sözleri | Türkçe Çeviriler',
            'description' => 'Çeşitli dillerden Türkçeye çevrilmiş şarkılara göz atın. En sevdiğiniz uluslararası müzik sözlerinin doğru Türkçe çevirilerini bulun.',
        ),
        'spanish' => array(
            'title' => 'Letras Traducidas al Español | Traducciones al Español',
            'description' => 'Explora canciones traducidas al español desde varios idiomas. Encuentra traducciones precisas al español de las letras de tu música internacional favorita.',
        ),
        'french' => array(
            'title' => 'Paroles Traduites en Français | Traductions Françaises',
            'description' => 'Parcourez les chansons traduites en français depuis diverses langues. Trouvez des traductions françaises précises des paroles de vos musiques internationales préférées.',
        ),
        'italian' => array(
            'title' => 'Testi Tradotti in Italiano | Traduzioni Italiane',
            'description' => 'Sfoglia canzoni tradotte in italiano da varie lingue. Trova traduzioni italiane accurate dei testi delle tue musiche internazionali preferite.',
        ),
        'korean' => array(
            'title' => '한국어로 번역된 노래 가사 | 한국어 번역',
            'description' => '다양한 언어에서 한국어로 번역된 노래를 탐색하세요. 좋아하는 해외 음악 가사의 정확한 한국어 번역을 찾아보세요.',
        ),
        'japanese' => array(
            'title' => '日本語に翻訳された歌詞 | 日本語翻訳',
            'description' => '様々な言語から日本語に翻訳された曲を閲覧してください。お気に入りの国際音楽歌詞の正確な日本語翻訳を見つけてください。',
        ),
        'german' => array(
            'title' => 'Ins Deutsche Übersetzte Liedtexte | Deutsche Übersetzungen',
            'description' => 'Durchsuchen Sie aus verschiedenen Sprachen ins Deutsche übersetzte Lieder. Finden Sie genaue deutsche Übersetzungen Ihrer internationalen Lieblingsmusiktexte.',
        ),
        'portuguese' => array(
            'title' => 'Letras Traduzidas para Português | Traduções em Português',
            'description' => 'Navegue por músicas traduzidas para português de vários idiomas. Encontre traduções precisas em português das letras de suas músicas internacionais favoritas.',
        ),
        'russian' => array(
            'title' => 'Тексты Песен Переведенные на Русский | Русские Переводы',
            'description' => 'Просмотрите песни, переведенные на русский язык с различных языков. Найдите точные русские переводы текстов вашей любимой международной музыки.',
        ),
        'arabic' => array(
            'title' => 'كلمات الأغاني المترجمة إلى العربية | الترجمات العربية',
            'description' => 'تصفح الأغاني المترجمة إلى العربية من لغات مختلفة. اعثر على ترجمات عربية دقيقة لكلمات موسيقاك الدولية المفضلة.',
        ),
        'hindi' => array(
            'title' => 'हिंदी में अनुवादित गाने के बोल | हिंदी अनुवाद',
            'description' => 'विभिन्न भाषाओं से हिंदी में अनुवादित गानों को ब्राउज़ करें। अपने पसंदीदा अंतर्राष्ट्रीय संगीत बोल के सटीक हिंदी अनुवाद खोजें।',
        ),
    );
}

/**
 * Update taxonomy term meta with SEO data
 * This function should be called once to populate the SEO data
 */
function arcuras_update_taxonomy_seo_meta() {
    // Update Original Language terms
    $original_data = arcuras_get_original_language_seo_data();
    foreach ($original_data as $slug => $seo) {
        $term = get_term_by('slug', $slug, 'original_language');
        if ($term && !is_wp_error($term)) {
            update_term_meta($term->term_id, 'seo_title', $seo['title']);
            update_term_meta($term->term_id, 'seo_description', $seo['description']);
        }
    }

    // Update Translated Language terms
    $translated_data = arcuras_get_translated_language_seo_data();
    foreach ($translated_data as $slug => $seo) {
        $term = get_term_by('slug', $slug, 'translated_language');
        if ($term && !is_wp_error($term)) {
            update_term_meta($term->term_id, 'seo_title', $seo['title']);
            update_term_meta($term->term_id, 'seo_description', $seo['description']);
        }
    }

    return true;
}

/**
 * Hook into wp_head to output custom SEO meta tags for taxonomy pages
 */
add_action('wp_head', 'arcuras_taxonomy_seo_meta_tags', 1);
function arcuras_taxonomy_seo_meta_tags() {
    if (!is_tax('original_language') && !is_tax('translated_language')) {
        return;
    }

    $term = get_queried_object();
    if (!$term || is_wp_error($term)) {
        return;
    }

    $seo_title = get_term_meta($term->term_id, 'seo_title', true);
    $seo_description = get_term_meta($term->term_id, 'seo_description', true);

    if ($seo_title) {
        echo '<meta property="og:title" content="' . esc_attr($seo_title) . '" />' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr($seo_title) . '" />' . "\n";
    }

    if ($seo_description) {
        echo '<meta name="description" content="' . esc_attr($seo_description) . '" />' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($seo_description) . '" />' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr($seo_description) . '" />' . "\n";
    }
}

/**
 * Filter the document title for taxonomy pages
 */
add_filter('document_title_parts', 'arcuras_taxonomy_seo_title', 10, 1);
function arcuras_taxonomy_seo_title($title) {
    if (!is_tax('original_language') && !is_tax('translated_language')) {
        return $title;
    }

    $term = get_queried_object();
    if (!$term || is_wp_error($term)) {
        return $title;
    }

    $seo_title = get_term_meta($term->term_id, 'seo_title', true);

    if ($seo_title) {
        $title['title'] = $seo_title;
    }

    return $title;
}

/**
 * Initialize SEO data on theme activation or when option doesn't exist
 */
add_action('after_setup_theme', 'arcuras_init_taxonomy_seo_data');
function arcuras_init_taxonomy_seo_data() {
    // Check if SEO data has already been initialized
    $initialized = get_option('arcuras_taxonomy_seo_initialized', false);

    if (!$initialized) {
        arcuras_update_taxonomy_seo_meta();
        update_option('arcuras_taxonomy_seo_initialized', true);
    }
}
