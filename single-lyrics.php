<?php
/**
 * The template for displaying single lyrics posts
 * Template for 'lyrics' custom post type
 * Sidebar entegrasyonu ile güncellendi.
 * Accessibility iyileştirmeleri eklendi.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Gufte
 */

get_header();

// Build Schema.org JSON-LD for MusicRecording
if (have_posts()) {
    while (have_posts()) {
        the_post();

        // Get all metadata
        $post_id = get_the_ID();
        $title = get_the_title();
        $permalink = get_permalink();
        $excerpt = get_the_excerpt();
        $thumbnail = get_the_post_thumbnail_url($post_id, 'large');

        // Taxonomies
        $singers = get_the_terms($post_id, 'singer');
        $albums = get_the_terms($post_id, 'album');
        $songwriters = get_the_terms($post_id, 'songwriter');
        $producers = get_the_terms($post_id, 'producer');

        // Meta
        $release_date = get_post_meta($post_id, '_release_date', true);
        $music_genre = get_post_meta($post_id, '_music_genre', true);
        $track_number = get_post_meta($post_id, '_track_number', true);
        $spotify_url = get_post_meta($post_id, 'spotify_url', true);
        $apple_music_url = get_post_meta($post_id, 'apple_music_url', true);
        $youtube_url = get_post_meta($post_id, 'youtube_url', true);

        // Build schema
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'MusicRecording',
            'name' => $title,
            'url' => $permalink,
        );

        if ($excerpt) {
            $schema['description'] = $excerpt;
        }

        if ($thumbnail) {
            $schema['image'] = $thumbnail;
        }

        // Artists (performers)
        if ($singers && !is_wp_error($singers)) {
            $schema['byArtist'] = array();
            foreach ($singers as $singer) {
                $schema['byArtist'][] = array(
                    '@type' => 'MusicGroup',
                    'name' => $singer->name,
                    'url' => get_term_link($singer),
                );
            }
            // If single artist, make it object instead of array
            if (count($schema['byArtist']) === 1) {
                $schema['byArtist'] = $schema['byArtist'][0];
            }
        }

        // Album
        if ($albums && !is_wp_error($albums)) {
            $album = $albums[0];
            $album_year = get_term_meta($album->term_id, 'album_year', true);

            $schema['inAlbum'] = array(
                '@type' => 'MusicAlbum',
                'name' => $album->name,
                'url' => get_term_link($album),
            );

            if ($album_year) {
                $schema['inAlbum']['datePublished'] = $album_year;
            }
        }

        // Composers (songwriters)
        if ($songwriters && !is_wp_error($songwriters)) {
            $schema['composer'] = array();
            foreach ($songwriters as $sw) {
                $schema['composer'][] = array(
                    '@type' => 'Person',
                    'name' => $sw->name,
                    'url' => get_term_link($sw),
                );
            }
            if (count($schema['composer']) === 1) {
                $schema['composer'] = $schema['composer'][0];
            }
        }

        // Producers
        if ($producers && !is_wp_error($producers)) {
            $schema['producer'] = array();
            foreach ($producers as $pr) {
                $schema['producer'][] = array(
                    '@type' => 'Person',
                    'name' => $pr->name,
                    'url' => get_term_link($pr),
                );
            }
            if (count($schema['producer']) === 1) {
                $schema['producer'] = $schema['producer'][0];
            }
        }

        // Release date
        if ($release_date) {
            $schema['datePublished'] = $release_date;
        }

        // Genre
        if ($music_genre) {
            $schema['genre'] = $music_genre;
        }

        // Track number
        if ($track_number) {
            $schema['position'] = intval($track_number);
        }

        // Duration (if available)
        $duration = get_post_meta($post_id, '_duration', true);
        if ($duration) {
            $schema['duration'] = $duration; // Should be in ISO 8601 format (PT3M45S)
        }

        // External links
        $same_as = array();
        if ($spotify_url) $same_as[] = $spotify_url;
        if ($apple_music_url) $same_as[] = $apple_music_url;
        if ($youtube_url) $same_as[] = $youtube_url;

        if (!empty($same_as)) {
            $schema['sameAs'] = $same_as;
        }

        // Publisher (website)
        $schema['publisher'] = array(
            '@type' => 'Organization',
            'name' => get_bloginfo('name'),
            'url' => home_url('/'),
        );

        // Output JSON-LD
        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";

        // Output Open Graph meta tags
        echo '<meta property="og:type" content="music.song" />' . "\n";
        echo '<meta property="og:title" content="' . esc_attr($title) . '" />' . "\n";
        echo '<meta property="og:url" content="' . esc_url($permalink) . '" />' . "\n";
        echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '" />' . "\n";

        if ($excerpt) {
            echo '<meta property="og:description" content="' . esc_attr($excerpt) . '" />' . "\n";
        }

        if ($thumbnail) {
            echo '<meta property="og:image" content="' . esc_url($thumbnail) . '" />' . "\n";
            echo '<meta property="og:image:width" content="1024" />' . "\n";
            echo '<meta property="og:image:height" content="1024" />' . "\n";
        }

        // Music-specific Open Graph tags
        if ($singers && !is_wp_error($singers)) {
            foreach ($singers as $singer) {
                echo '<meta property="music:musician" content="' . esc_url(get_term_link($singer)) . '" />' . "\n";
            }
        }

        if ($albums && !is_wp_error($albums)) {
            $album = $albums[0];
            echo '<meta property="music:album" content="' . esc_url(get_term_link($album)) . '" />' . "\n";
            if ($track_number) {
                echo '<meta property="music:album:track" content="' . esc_attr($track_number) . '" />' . "\n";
            }
        }

        if ($release_date) {
            echo '<meta property="music:release_date" content="' . esc_attr($release_date) . '" />' . "\n";
        }

        // Twitter Card meta tags
        echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr($title) . '" />' . "\n";

        if ($excerpt) {
            echo '<meta name="twitter:description" content="' . esc_attr($excerpt) . '" />' . "\n";
        }

        if ($thumbnail) {
            echo '<meta name="twitter:image" content="' . esc_url($thumbnail) . '" />' . "\n";
        }

        // Meta description for SEO
        if ($excerpt) {
            echo '<meta name="description" content="' . esc_attr($excerpt) . '" />' . "\n";
        } else {
            $singers_list = $singers && !is_wp_error($singers) ? $singers[0]->name : '';
            $desc = $title;
            if ($singers_list) {
                $desc .= ' by ' . $singers_list;
            }

            // Get original language and use appropriate suffix
            $original_lang = function_exists('arcuras_get_original_language_code')
                ? arcuras_get_original_language_code(get_the_ID())
                : 'en';
            $meta_suffix = function_exists('arcuras_get_meta_description_suffix')
                ? arcuras_get_meta_description_suffix($original_lang)
                : 'Read lyrics, discover translations in multiple languages, and explore detailed annotations';

            $desc .= ' - ' . $meta_suffix;
            echo '<meta name="description" content="' . esc_attr($desc) . '" />' . "\n";
        }

        // Reset post data for the loop below
        rewind_posts();
        break;
    }
}

/**
 * İçerikteki çok dilli tablo bilgilerini getir
 * Bu fonksiyon functions.php'de tanımlıysa buradan kaldırılabilir.
 */
if (!function_exists('gufte_get_lyrics_languages')) {
    function gufte_get_lyrics_languages($content) {
        $languages = array();
        $original_language = '';
        preg_match_all('/<figure class="wp-block-table">.*?<table.*?>(.*?)<\/table>.*?<\/figure>/s', $content, $table_matches);
        if (!empty($table_matches[1])) {
            foreach ($table_matches[1] as $table_content) {
                preg_match('/<thead>(.*?)<\/thead>/s', $table_content, $header_matches);
                if (!empty($header_matches)) {
                    preg_match_all('/<th>(.*?)<\/th>/s', $header_matches[1], $column_matches);
                    if (!empty($column_matches[1])) {
                        if (isset($column_matches[1][0]) && !empty($column_matches[1][0])) {
                            $original_language = strip_tags($column_matches[1][0]);
                        }
                        for ($i = 1; $i < count($column_matches[1]); $i++) {
                            $lang = strip_tags($column_matches[1][$i]);
                            if (!empty($lang) && !in_array($lang, $languages)) {
                                $languages[] = $lang;
                            }
                        }
                    }
                }
            }
        }
        return array('original' => $original_language, 'translations' => $languages);
    }
}
?>

<?php // Ana İçerik Sarmalayıcısı (Sidebar + Main) ?>
<div class="site-content-wrapper flex flex-col md:flex-row" role="main"> <?php // index.php'deki ile aynı yapı ?>

    <?php
    // Arcuras Sidebar'ı çağır
    get_template_part('template-parts/arcuras-sidebar');
    ?>

    <?php // Ana İçerik Alanı (Sağ Sütun) ?>
    <main id="primary" class="site-main flex-1 pt-6 pb-12 px-4 sm:px-6 lg:px-8 overflow-x-hidden" role="main">
        <?php
        while ( have_posts() ) :
            the_post();

            // İçeriği alıp tablo dil bilgilerini çıkar
            $raw_content = get_the_content();
            $lyrics_languages = gufte_get_lyrics_languages($raw_content);

            // Post meta (Spotify, Youtube vb.)
            $spotify_url = get_post_meta(get_the_ID(), 'spotify_url', true);
            $youtube_url = get_post_meta(get_the_ID(), 'youtube_url', true);
            $apple_music_url = get_post_meta(get_the_ID(), 'apple_music_url', true);
            $spotify_embed = get_post_meta(get_the_ID(), 'spotify_embed', true);
            $youtube_embed = get_post_meta(get_the_ID(), 'youtube_embed', true);
            $apple_music_embed = get_post_meta(get_the_ID(), 'apple_music_embed', true);

            // Taxonomies
            $singers = get_the_terms(get_the_ID(), 'singer');
            $albums = get_the_terms(get_the_ID(), 'album');
            $categories = get_the_category();
            $songwriters = get_the_terms(get_the_ID(), 'songwriter');
            $producers   = get_the_terms(get_the_ID(), 'producer');
        ?>

<?php // Yazı Başlık ve Meta Alanı (Sağ sütun içinde) ?>
<div class="post-header-area mb-8 bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
    <div class="flex flex-col md:flex-row">
        <?php if ( has_post_thumbnail() ) : ?>
        <div class="post-thumbnail md:w-1/3 lg:w-1/4 flex items-center justify-center bg-gray-50 p-4 border-r border-gray-100">
            <div class="aspect-square w-full relative flex items-center justify-center">
                <?php
                $thumbnail_id = get_post_thumbnail_id();
                $medium_img = wp_get_attachment_image_src($thumbnail_id, 'medium');
                if ($medium_img) :
                    $alt_text = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
                    if (empty($alt_text)) { $alt_text = get_the_title(); }
                    ?>
                    <img src="<?php echo esc_url($medium_img[0]); ?>"
                         width="<?php echo esc_attr($medium_img[1]); ?>"
                         height="<?php echo esc_attr($medium_img[2]); ?>"
                         alt="<?php echo esc_attr($alt_text); ?>"
                         class="max-w-full max-h-full object-contain rounded-md shadow-sm"
                         fetchpriority="high"
                         decoding="async" />
                <?php else :
                    the_post_thumbnail('medium', array(
                        'class' => 'max-w-full max-h-full object-contain rounded-md shadow-sm',
                        'fetchpriority' => 'high',
                        'decoding' => 'async'
                    ));
                endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php // Başlık ve Meta (Sağ taraf) ?>
        <div class="<?php echo has_post_thumbnail() ? 'md:w-2/3 lg:w-3/4' : 'w-full'; ?> p-4 md:p-6 flex flex-col justify-center">
            <header class="entry-header">
                <?php if ( $categories && !is_wp_error($categories) ) : ?>
                <div class="post-categories mb-2 flex flex-wrap gap-x-2" role="navigation" aria-label="<?php esc_attr_e('Post Categories', 'gufte'); ?>">
                    <?php
                    foreach ($categories as $category) {
                        echo '<a href="' . esc_url(get_category_link($category->term_id)) . '" class="inline-block px-2 py-0.5 bg-gray-200 hover:bg-gray-300 text-gray-800 hover:text-gray-900 rounded text-xs font-medium transition-colors duration-200">' . esc_html($category->name) . '</a>';
                    }
                    ?>
                </div>
                <?php endif; ?>

                <?php
                // Başlık ve Düzenle Linki
                echo '<h1 class="entry-title text-2xl lg:text-3xl font-bold text-gray-800 mb-3">';
                the_title();
                if (current_user_can('edit_post', get_the_ID())) {
                    echo ' <a href="' . esc_url(get_edit_post_link()) . '" class="inline-flex items-center justify-center ml-2 text-gray-600 hover:text-gray-800 transition-colors duration-300" title="' . esc_attr__('Düzenle', 'gufte') . '" aria-label="' . esc_attr__('Edit this post', 'gufte') . '">';
                    gufte_icon('pencil', 'text-lg');
                    echo '</a>';
                }
                echo '</h1>';
                ?>

                <?php // Müzik Meta Grid - HİYERARŞİ DÜZELTİLDİ (H3 yerine div kullanıldı) ?>
                <div class="music-meta-grid grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2 mb-4 text-sm">
                    <?php // Şarkıcı ?>
                    <?php if ($singers && !is_wp_error($singers)) : ?>
                        <div class="singers-section">
                            <div class="text-xs font-semibold text-gray-700 flex items-center mb-1" id="singers-heading">
                                <?php gufte_icon('microphone', 'mr-1.5 text-base text-gray-600'); ?>
                                <span><?php esc_html_e('Singer(s):', 'gufte'); ?></span>
                            </div>
                            <div class="flex flex-wrap gap-1" role="list" aria-labelledby="singers-heading">
                                <?php foreach ($singers as $singer) : ?>
                                    <a href="<?php echo esc_url(get_term_link($singer)); ?>" 
                                       class="singer-chip inline-flex items-center bg-blue-100 hover:bg-blue-200 text-blue-900 rounded-full px-2.5 py-0.5 transition-colors duration-200 group"
                                       role="listitem"
                                       aria-label="<?php echo esc_attr(sprintf(__('View lyrics by %s', 'gufte'), $singer->name)); ?>">
                                        <span class="font-medium"><?php echo esc_html($singer->name); ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php // Söz Yazarı (Songwriter) ?>
<?php if ($songwriters && !is_wp_error($songwriters)) : ?>
    <div class="songwriters-section">
        <div class="text-xs font-semibold text-gray-700 flex items-center mb-1" id="songwriters-heading">
            <?php gufte_icon('pen', 'mr-1.5 text-base text-gray-600'); ?>
            <span><?php esc_html_e('Songwriter(s):', 'gufte'); ?></span>
        </div>
        <div class="flex flex-wrap gap-1" role="list" aria-labelledby="songwriters-heading">
            <?php foreach ($songwriters as $sw) : ?>
                <a href="<?php echo esc_url(get_term_link($sw)); ?>" 
                   class="songwriter-chip inline-flex items-center bg-amber-100 hover:bg-amber-200 text-amber-900 rounded-full px-2.5 py-0.5 transition-colors duration-200 group"
                   role="listitem"
                   aria-label="<?php echo esc_attr(sprintf(__('View songs written by %s', 'gufte'), $sw->name)); ?>">
                    <span class="font-medium"><?php echo esc_html($sw->name); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php // Yapımcı (Producer) ?>
<?php if ($producers && !is_wp_error($producers)) : ?>
    <div class="producers-section">
        <div class="text-xs font-semibold text-gray-700 flex items-center mb-1" id="producers-heading">
            <?php gufte_icon('console', 'mr-1.5 text-base text-gray-600'); ?>
            <span><?php esc_html_e('Producer(s):', 'gufte'); ?></span>
        </div>
        <div class="flex flex-wrap gap-1" role="list" aria-labelledby="producers-heading">
            <?php foreach ($producers as $pr) : ?>
                <a href="<?php echo esc_url(get_term_link($pr)); ?>" 
                   class="producer-chip inline-flex items-center bg-purple-100 hover:bg-purple-200 text-purple-900 rounded-full px-2.5 py-0.5 transition-colors duration-200 group"
                   role="listitem"
                   aria-label="<?php echo esc_attr(sprintf(__('View productions by %s', 'gufte'), $pr->name)); ?>">
                    <span class="font-medium"><?php echo esc_html($pr->name); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>


                    <?php // Albüm ?>
                    <?php if ($albums && !is_wp_error($albums)) : ?>
                        <div class="albums-section">
                            <div class="text-xs font-semibold text-gray-700 flex items-center mb-1" id="albums-heading">
                                <?php gufte_icon('album', 'mr-1.5 text-base text-gray-600'); ?>
                                <span><?php esc_html_e('Album(s):', 'gufte'); ?></span>
                            </div>
                            <div class="flex flex-wrap gap-1" role="list" aria-labelledby="albums-heading">
                                <?php foreach ($albums as $album) :
                                    $album_year = get_term_meta($album->term_id, 'album_year', true); ?>
                                    <a href="<?php echo esc_url(get_term_link($album)); ?>" 
                                       class="album-chip inline-flex items-center bg-purple-100 hover:bg-purple-200 text-purple-900 rounded-full px-2.5 py-0.5 transition-colors duration-200 group"
                                       role="listitem"
                                       aria-label="<?php echo esc_attr(sprintf(__('View lyrics from album %s', 'gufte'), $album->name)); ?>">
                                        <span class="font-medium"><?php echo esc_html($album->name); ?></span>
                                        <?php if (!empty($album_year)) : ?>
                                            <span class="text-xs text-purple-700 ml-1">(<?php echo esc_html($album_year); ?>)</span>
                                        <?php endif; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php // Release Date, Track Number ve Genre ?>
                    <?php
                    $release_date = get_post_meta(get_the_ID(), '_release_date', true);
                    $music_genre = get_post_meta(get_the_ID(), '_music_genre', true);
                    $track_number = get_post_meta(get_the_ID(), '_track_number', true);
                    ?>
                    <?php if (!empty($release_date) || !empty($music_genre) || !empty($track_number)) : ?>
                        <div class="release-genre-section">
                            <?php // Release Date ?>
                            <?php if (!empty($release_date)) : ?>
                            <div class="release-date mb-3">
                                <div class="text-xs font-semibold text-gray-700 flex items-center mb-1" id="release-heading">
                                    <?php gufte_icon('calendar-music', 'mr-1.5 text-base text-gray-600'); ?>
                                    <span><?php esc_html_e('Release Date:', 'gufte'); ?></span>
                                </div>
                                <div class="flex flex-wrap gap-1" aria-labelledby="release-heading">
                                    <span class="release-date-chip inline-flex items-center bg-indigo-100 text-indigo-900 rounded-full px-2.5 py-0.5 border border-indigo-300">
                                        <span class="font-medium"><?php echo esc_html(date('F j, Y', strtotime($release_date))); ?></span>
                                        <span class="text-xs text-indigo-700 ml-1">(<?php echo esc_html(human_time_diff(strtotime($release_date), current_time('timestamp'))); ?> ago)</span>
                                    </span>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php // Track Number ?>
                            <?php if (!empty($track_number)) : ?>
                            <div class="track-number mb-3">
                                <div class="text-xs font-semibold text-gray-700 flex items-center mb-1" id="track-heading">
                                    <?php gufte_icon('list-numbered', 'mr-1.5 text-base text-gray-600'); ?>
                                    <span><?php esc_html_e('Track Number:', 'gufte'); ?></span>
                                </div>
                                <div class="flex flex-wrap gap-1" aria-labelledby="track-heading">
                                    <span class="track-chip inline-flex items-center bg-green-100 text-green-900 rounded-full px-2.5 py-0.5 border border-green-300">
                                        <span class="font-medium">#<?php echo esc_html($track_number); ?></span>
                                    </span>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php // Music Genre ?>
                            <?php if (!empty($music_genre)) : ?>
                            <div class="music-genre">
                                <div class="text-xs font-semibold text-gray-700 flex items-center mb-1" id="genre-heading">
                                    <?php gufte_icon('music-note', 'mr-1.5 text-base text-gray-600'); ?>
                                    <span><?php esc_html_e('Genre:', 'gufte'); ?></span>
                                </div>
                                <div class="flex flex-wrap gap-1" aria-labelledby="genre-heading">
                                    <span class="genre-chip inline-flex items-center bg-orange-100 text-orange-900 rounded-full px-2.5 py-0.5 border border-orange-300">
                                        <span class="font-medium"><?php echo esc_html($music_genre); ?></span>
                                    </span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

<?php // Diller (Orijinal ve Çeviri) - Güncellenmiş versiyon ?>
<?php if (!empty($lyrics_languages['original']) || !empty($lyrics_languages['translations'])) : ?>
    <div class="languages-section col-span-1 <?php echo ($singers && $albums) ? 'sm:col-span-2' : ''; // Eğer hem şarkıcı hem albüm varsa tam satır kaplasın ?>">
        
        <?php // Orijinal Dil ?>
        <?php if (!empty($lyrics_languages['original'])) : ?>
            <div class="original-language mb-3">
                <div class="text-xs font-semibold text-gray-700 flex items-center mb-1" id="original-lang-heading">
                    <?php gufte_icon('file-document', 'mr-1.5 text-base text-gray-600'); ?>
                    <span><?php esc_html_e('Original Language:', 'gufte'); ?></span>
                </div>
                <div class="flex flex-wrap gap-1" aria-labelledby="original-lang-heading">
                    <span class="original-lang-chip inline-flex items-center bg-green-100 text-green-900 rounded-full px-2.5 py-0.5 border border-green-300">
                        <span class="font-medium"><?php echo esc_html($lyrics_languages['original']); ?></span>
                        <span class="text-xs text-green-700 ml-1">(Original)</span>
                    </span>
                </div>
            </div>
        <?php endif; ?>
        
        <?php // Çeviri Dilleri ?>
        <?php if (!empty($lyrics_languages['translations'])) : ?>
            <div class="translation-languages">
                <div class="text-xs font-semibold text-gray-700 flex items-center mb-1" id="translations-heading">
                    <?php gufte_icon('translate', 'mr-1.5 text-base text-gray-600'); ?>
                    <span><?php esc_html_e('Translations:', 'gufte'); ?></span>
                </div>
                <div class="flex flex-wrap gap-1" role="list" aria-labelledby="translations-heading">
                    <?php foreach ($lyrics_languages['translations'] as $language) : ?>
                        <span class="trans-lang-chip inline-flex items-center bg-emerald-100 text-emerald-900 rounded-full px-2.5 py-0.5 border border-emerald-300" role="listitem">
                            <span class="font-medium"><?php echo esc_html($language); ?></span>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
<?php endif; ?>

<?php
// Modern Awards Badge - music-meta-grid içinde
$awards = function_exists('gufte_get_post_awards') ? gufte_get_post_awards(get_the_ID()) : array();
$awards_summary = array(); // Initialize summary

if ($awards) :
    $awards_summary = gufte_prepare_awards_summary($awards);
?>
<div class="awards-section">
    <div class="text-xs font-semibold text-gray-700 flex items-center mb-1" id="awards-heading">
        <?php gufte_icon('trophy', 'mr-1.5 text-base text-gray-600'); ?>
        <span><?php esc_html_e('Awards:', 'gufte'); ?></span>
    </div>
    
    <div class="awards-summary-container relative">
        <button 
            type="button"
            class="awards-summary-badge group inline-flex items-center bg-gradient-to-r from-yellow-100 to-amber-100 hover:from-yellow-200 hover:to-amber-200 text-amber-900 rounded-full px-3 py-1.5 transition-all duration-300 border border-amber-300 hover:border-amber-400 hover:shadow-md cursor-pointer focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-1"
            data-awards-toggle="awards-details-<?php echo get_the_ID(); ?>"
            aria-expanded="false"
            aria-controls="awards-details-<?php echo get_the_ID(); ?>"
            title="Click to view all awards">
            
            <?php gufte_icon('trophy-award', 'text-amber-600 mr-1.5 group-hover:scale-110 transition-transform duration-200'); ?>
            
            <span class="font-semibold mr-1"><?php echo count($awards); ?></span>
            <span class="text-sm"><?php echo _n('Award', 'Awards', count($awards), 'gufte'); ?></span>
            
            <?php if ($awards_summary['winners'] > 0) : ?>
                <span class="ml-2 px-2 py-0.5 bg-green-200 text-green-800 rounded-full text-xs font-medium">
                    <?php echo $awards_summary['winners']; ?> <?php echo _n('Win', 'Wins', $awards_summary['winners'], 'gufte'); ?>
                </span>
            <?php endif; ?>
            
            <?php gufte_icon('chevron-down', 'ml-1.5 text-amber-600 group-hover:rotate-180 transition-transform duration-300'); ?>
        </button>
        
<!-- Hover Tooltip -->
<div class="awards-tooltip absolute z-50 invisible opacity-0 group-hover:visible group-hover:opacity-100 transition-all duration-200 bg-gray-900 text-white text-xs rounded-lg px-3 py-2 mt-1 left-0 min-w-max shadow-lg">
    <div class="space-y-1">
        <!-- Awards Summary -->
        <?php if ($awards_summary['winners'] > 0) : ?>
            <div class="text-green-300"><?php echo $awards_summary['winners']; ?> Winners</div>
        <?php endif; ?>
        <?php if ($awards_summary['nominees'] > 0) : ?>
            <div class="text-blue-300"><?php echo $awards_summary['nominees']; ?> Nominees</div>
        <?php endif; ?>
        <?php if ($awards_summary['mentions'] > 0) : ?>
            <div class="text-purple-300"><?php echo $awards_summary['mentions']; ?> Mentions</div>
        <?php endif; ?>
        
        <!-- Awards Details -->
        <?php if (!empty($awards)) : ?>
            <div class="border-t border-gray-700 pt-1 mt-1">
                <?php foreach (array_slice($awards, 0, 3) as $award) : // İlk 3 ödülü göster ?>
                    <div class="text-gray-300 text-[10px] leading-tight">
                        <?php 
                        $org_name = '';
                        if (!empty($award['organization'])) {
                            $org_name = $award['organization'];
                        } elseif (!empty($award['type_label'])) {
                            $org_name = $award['type_label'];
                        }
                        
                        $result_emoji = ($award['result'] === 'winner') ? '🏆' : (($award['result'] === 'honorable_mention') ? '🌟' : '🎯');
                        
                        echo $result_emoji . ' ' . esc_html($award['category']);
                        if (!empty($org_name)) {
                            echo ' - ' . esc_html($org_name);
                        }
                        if (!empty($award['year'])) {
                            echo ' (' . esc_html($award['year']) . ')';
                        }
                        ?>
                    </div>
                <?php endforeach; ?>
                
                <?php if (count($awards) > 3) : ?>
                    <div class="text-gray-400 text-[10px] mt-1">
                        +<?php echo (count($awards) - 3); ?> more...
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="absolute top-0 left-4 -translate-y-1 w-2 h-2 bg-gray-900 rotate-45"></div>
</div>
    </div>
</div>
<?php endif; ?>

<style>
/* Awards System Styles */
.awards-summary-container { 
    position: relative; 
    display: inline-block; 
}

.awards-summary-container:hover .awards-tooltip {
    visibility: visible !important;
    opacity: 1 !important;
}

.awards-tooltip { 
    pointer-events: none; 
    z-index: 1000;
    position: absolute;
    visibility: hidden;
    opacity: 0;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.awards-details-section { 
    animation: slideDown 0.3s ease-out; 
}

.awards-details-section.hidden { 
    animation: slideUp 0.2s ease-in; 
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.award-item:hover .award-icon { 
    transform: scale(1.05); 
    transition: transform 0.2s ease; 
}

.stat-card { 
    transition: all 0.2s ease; 
}

.stat-card:hover { 
    transform: translateY(-2px); 
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); 
}

@media (max-width: 640px) {
    .awards-stats { 
        grid-template-columns: repeat(2, 1fr); 
    }
    .award-item { 
        flex-direction: column; 
        text-align: center; 
    }
    .award-icon { 
        align-self: center; 
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Awards toggle functionality
    const toggleButtons = document.querySelectorAll('[data-awards-toggle]');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('data-awards-toggle');
            const targetElement = document.getElementById(targetId);
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            if (targetElement) {
                if (isExpanded) {
                    this.setAttribute('aria-expanded', 'false');
                    targetElement.classList.add('hidden');
                    
                    const chevron = this.querySelector('[data-icon="mdi:chevron-up"], [data-icon="mdi:chevron-down"]');
                    if (chevron) chevron.setAttribute('data-icon', 'mdi:chevron-down');
                } else {
                    this.setAttribute('aria-expanded', 'true');
                    targetElement.classList.remove('hidden');
                    
                    const chevron = this.querySelector('[data-icon="mdi:chevron-up"], [data-icon="mdi:chevron-down"]');
                    if (chevron) chevron.setAttribute('data-icon', 'mdi:chevron-up');
                    
                    setTimeout(() => {
                        targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 100);
                }
            }
        });
    });
});
</script>

<?php // Yayınlanma Tarihi, Güncelleme Tarihi ve Yorum Sayısı ?>
<div class="meta-section col-span-1 <?php echo (!$singers || !$albums) ? 'sm:col-span-2' : ''; // Eğer şarkıcı veya albüm yoksa tam satır kaplasın ?>">
    <div class="text-xs font-semibold text-gray-700 flex items-center mb-1" id="post-meta-heading">
        <?php gufte_icon('information-outline', 'mr-1.5 text-base text-gray-600'); ?>
        <span><?php esc_html_e('Info:', 'gufte'); ?></span>
    </div>
    <div class="flex flex-wrap gap-x-3 gap-y-1 items-center text-xs text-gray-700" aria-labelledby="post-meta-heading">
        <span class="publish-date flex items-center">
            <?php gufte_icon('calendar-blank', 'mr-1'); ?>
            <span class="font-semibold">Published:</span>
            <time datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>" class="ml-1"><?php echo esc_html(get_the_date()); ?></time>
        </span>
        <span class="modified-date flex items-center">
            <?php gufte_icon('calendar-edit', 'mr-1'); ?>
            <span class="font-semibold">Modified:</span>
            <time datetime="<?php echo esc_attr(get_the_modified_date(DATE_W3C)); ?>" class="ml-1"><?php echo esc_html(get_the_modified_date()); ?></time>
        </span>
        <?php if ( get_comments_number() ) : ?>
        <span class="comments-link flex items-center">
            <?php gufte_icon('comment-text-outline', 'mr-1'); ?>
            <a href="<?php comments_link(); ?>" class="hover:text-gray-900 transition-colors duration-200" aria-label="<?php echo esc_attr(sprintf(__('View %s comments', 'gufte'), get_comments_number())); ?>">
                <?php printf(esc_html(_n('%s Comment', '%s Comments', get_comments_number(), 'gufte')), number_format_i18n(get_comments_number())); ?>
            </a>
        </span>
        <?php endif; ?>
    </div>
</div>
</div><?php // music-meta-grid kapanış div'i ?>

<?php // Müzik Platformu Bağlantıları ?>
<?php
// Check if video exists
$video_embed = get_post_meta(get_the_ID(), 'music_video_embed', true);
$video_url = get_post_meta(get_the_ID(), 'music_video_url', true);
$has_video = !empty($video_embed) || !empty($video_url);

if ($spotify_url || $youtube_url || $apple_music_url || $has_video) :
?>
<div class="music-links mt-3">
    <div class="flex flex-wrap gap-2" role="list" aria-label="<?php esc_attr_e('Music streaming platforms', 'gufte'); ?>">
        <?php if ($spotify_url) : ?>
        <a href="<?php echo esc_url($spotify_url); ?>"
           target="_blank"
           rel="noopener noreferrer"
           class="music-platform-link flex items-center px-3 py-2 bg-[#1DB954] hover:bg-[#1ed760] text-white rounded-md transition-colors duration-300 shadow text-sm font-medium min-h-[44px]"
           role="listitem"
           aria-label="<?php esc_attr_e('Listen on Spotify (opens in new window)', 'gufte'); ?>">
            <?php gufte_icon('spotify', 'mr-1.5'); ?>
            <span class="font-medium">Spotify</span>
        </a>
        <?php endif; ?>
        <?php if ($youtube_url) : ?>
        <a href="<?php echo esc_url($youtube_url); ?>"
           target="_blank"
           rel="noopener noreferrer"
           class="music-platform-link flex items-center px-3 py-2 bg-[#FF0000] hover:bg-[#ff3333] text-white rounded-md transition-colors duration-300 shadow text-sm font-medium min-h-[44px]"
           role="listitem"
           aria-label="<?php esc_attr_e('Watch on YouTube (opens in new window)', 'gufte'); ?>">
            <?php gufte_icon('youtube', 'mr-1.5'); ?>
            <span class="font-medium">YouTube</span>
        </a>
        <?php endif; ?>
        <?php if ($apple_music_url) : ?>
        <a href="<?php echo esc_url($apple_music_url); ?>"
           target="_blank"
           rel="noopener noreferrer"
           class="music-platform-link flex items-center px-3 py-2 bg-[#FA243C] hover:bg-[#fa3e52] text-white rounded-md transition-colors duration-300 shadow text-sm font-medium min-h-[44px]"
           role="listitem"
           aria-label="<?php esc_attr_e('Listen on Apple Music (opens in new window)', 'gufte'); ?>">
            <?php gufte_icon('apple', 'mr-1.5'); ?>
            <span class="font-medium">Apple Music</span>
        </a>
        <?php endif; ?>
        <?php if ($has_video) : ?>
        <button type="button"
           class="music-video-trigger music-platform-link flex items-center px-3 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-md transition-all duration-300 shadow text-sm font-medium min-h-[44px]"
           role="listitem"
           aria-label="<?php esc_attr_e('Watch Music Video', 'gufte'); ?>">
            <?php gufte_icon('play-circle', 'mr-1.5'); ?>
            <span class="font-medium">Music Video</span>
        </button>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
                        

                        
                        <?php // Sosyal Medya Paylaşım Butonları - Inline ?>
<div class="social-share-inline mt-4 pt-4 border-t border-gray-200">
    <div class="flex items-center gap-3">
        <span class="text-xs font-semibold text-gray-700 flex items-center">
            <?php gufte_icon('share-variant', 'mr-1.5 text-base'); ?>
            SHARE:
        </span>
                                <div class="flex flex-wrap gap-2" role="list" aria-label="<?php esc_attr_e('Share on social media', 'gufte'); ?>">
            <?php
            $social_links = gufte_get_social_share_links();
            $main_platforms = array('facebook', 'twitter', 'whatsapp', 'telegram', 'copy');
            
            foreach ($main_platforms as $platform) :
                if (isset($social_links[$platform])) :
                    $link = $social_links[$platform];
                    if ($platform === 'copy') : ?>
                        <button 
                            class="social-share-btn-inline copy-link-btn flex items-center gap-1 px-4 py-2 rounded-md text-white text-sm font-medium transition-all duration-300 hover:scale-105 min-h-[44px] min-w-[44px]"
                            style="background-color: <?php echo esc_attr($link['color']); ?>;"
                            data-url="<?php echo esc_attr($link['data-url']); ?>"
                            title="<?php echo esc_attr($link['label']); ?>"
                            role="listitem"
                            aria-label="<?php echo esc_attr($link['label']); ?>">
                            <?php
                                $icon_name = str_replace('mdi:', '', $link['icon']);
                                gufte_icon($icon_name, '');
                            ?>
                            <span class="hidden sm:inline"><?php echo esc_html($link['label']); ?></span>
                        </button>
                    <?php else : ?>
                        <a href="<?php echo esc_url($link['url']); ?>"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="social-share-btn-inline flex items-center gap-1 px-4 py-2 rounded-md text-white text-sm font-medium transition-all duration-300 hover:scale-105 min-h-[44px] min-w-[44px]"
                           style="background-color: <?php echo esc_attr($link['color']); ?>;"
                           role="listitem"
                           aria-label="Share on <?php echo esc_attr($link['label']); ?> (opens in new window)">
                            <?php
                                $icon_name = str_replace('mdi:', '', $link['icon']);
                                gufte_icon($icon_name, '');
                            ?>
                            <span class="hidden sm:inline"><?php echo esc_html($link['label']); ?></span>
                        </a>
                    <?php endif;
                endif;
            endforeach; ?>
        </div>
    </div>
</div>

                    </header></div>
            </div>
        
        



        
        
        
        
<?php // Ana İçerik (Şarkı Sözleri vs.) ?>
<article id="post-<?php the_ID(); ?>" <?php post_class('lyrics-content bg-white rounded-lg shadow-md overflow-hidden border border-gray-200'); ?> role="article" aria-labelledby="lyrics-heading">
    <div class="p-6 md:p-8">

        <!-- Ana Başlık - Tüm genişlikte -->
        <div class="mb-6 pb-4 border-b border-gray-200">
            <h2 id="lyrics-heading" class="text-2xl font-bold text-gray-800 flex items-center">
                <?php gufte_icon('text-long', 'mr-3 text-blue-600 text-3xl'); ?>
                <?php esc_html_e('Lyrics & Translations', 'gufte'); ?>
            </h2>
        </div>

        <!-- 2 sütunlu yerleşim: lg ve üstü 3/1 oran -->
        <div class="lyrics-layout grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">

            <!-- SOL: Sözler / Çeviriler -->
            <div class="lg:col-span-8 xl:col-span-9">
                <div class="entry-content prose prose-gray max-w-none" data-translation-content="true">
                    <?php
                    // İçeriği göster (WP filtreleri uygulanmış)
                    echo apply_filters('the_content', $raw_content);

                    // Çok sayfalı içerik için sayfalama
                    wp_link_pages(
                        array(
                            'before' => '<nav class="page-links mt-8 pt-6 border-t border-gray-200" aria-label="' . esc_attr__('Pagination', 'gufte') . '"><span class="text-gray-700 font-medium mr-3">' . esc_html__( 'Pages:', 'gufte' ) . '</span>',
                            'after'  => '</nav>',
                            'link_before' => '<span class="page-number">',
                            'link_after' => '</span>',
                        )
                    );
                    ?>
                </div>

                <?php
                // Çeviri katkı butonu - Sadece giriş yapmış kullanıcılara göster
                $is_logged_in = is_user_logged_in();
                $current_user_id = $is_logged_in ? get_current_user_id() : 0;
                $user_known_languages = $is_logged_in ? get_user_meta($current_user_id, 'user_known_languages', true) : array();

                if ($is_logged_in) :
                    // Show message if no languages selected
                    if (empty($user_known_languages) || !is_array($user_known_languages)) :
                ?>
                <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="flex items-start space-x-3">
                        <?php gufte_icon('information', 'text-blue-600 text-2xl flex-shrink-0 w-6 h-6 mt-1'); ?>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-blue-800 mb-1">
                                <?php esc_html_e('Want to Contribute Translations?', 'gufte'); ?>
                            </h3>
                            <p class="text-sm text-blue-700 mb-3">
                                <?php esc_html_e('Select the languages you know in your profile to start contributing translations!', 'gufte'); ?>
                            </p>
                            <a href="<?php echo esc_url(home_url('/my-profile/')); ?>"
                               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all duration-300">
                                <?php gufte_icon('account-edit', 'mr-2 w-4 h-4'); ?>
                                <?php esc_html_e('Go to Profile', 'gufte'); ?>
                            </a>
                        </div>
                    </div>
                </div>
                <?php
                    elseif (!empty($user_known_languages) && is_array($user_known_languages)) :
                        // Mevcut çevirileri al
                        $existing_translations = array();
                        if (!empty($lyrics_languages['original'])) {
                            $existing_translations[] = strtolower(trim($lyrics_languages['original']));
                        }
                        if (!empty($lyrics_languages['translations'])) {
                            foreach ($lyrics_languages['translations'] as $trans) {
                                $existing_translations[] = strtolower(trim($trans));
                            }
                        }

                        // Kullanıcının çevirebileceği eksik dilleri bul
                        $missing_languages = array_diff($user_known_languages, $existing_translations);

                        if (!empty($missing_languages)) :
                ?>
                <div class="mt-6 p-4 bg-gradient-to-r from-primary-50 to-accent-50 rounded-lg border border-primary-200">
                    <div class="flex items-start space-x-3">
                        <?php gufte_icon('translate', 'text-primary-600 text-2xl flex-shrink-0 w-6 h-6 mt-1'); ?>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-800 mb-1">
                                <?php esc_html_e('Help Translate This Song', 'gufte'); ?>
                            </h3>
                            <p class="text-sm text-gray-600 mb-3">
                                <?php esc_html_e('This song is missing translations in languages you know. Contribute and help others enjoy this song!', 'gufte'); ?>
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <?php
                                $lang_names = array(
                                    'english' => 'English',
                                    'spanish' => 'Español',
                                    'turkish' => 'Türkçe',
                                    'german' => 'Deutsch',
                                    'french' => 'Français',
                                    'italian' => 'Italiano',
                                    'portuguese' => 'Português',
                                    'russian' => 'Русский',
                                    'arabic' => 'العربية',
                                    'japanese' => '日本語',
                                );

                                foreach ($missing_languages as $lang) :
                                    $lang_display = isset($lang_names[$lang]) ? $lang_names[$lang] : ucfirst($lang);
                                    $contribute_url = add_query_arg(array(
                                        'contribute_translation' => '1',
                                        'target_language' => $lang,
                                        'post_id' => get_the_ID()
                                    ), home_url('/contribute-translation/'));
                                ?>
                                <a href="<?php echo esc_url($contribute_url); ?>"
                                   class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-all duration-300 hover:shadow-lg">
                                    <?php gufte_icon('plus-circle', 'mr-2 w-4 h-4'); ?>
                                    <?php echo esc_html(sprintf(__('Translate to %s', 'gufte'), $lang_display)); ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                        endif;
                    endif;
                endif;
                ?>
            </div>

            <!-- SAĞ: Related Lyrics (sadece masaüstü) -->
            <aside class="lg:col-span-4 xl:col-span-3 hidden lg:block" aria-label="<?php esc_attr_e('Related Lyrics', 'gufte'); ?>">
                <div class="bg-gray-50 rounded-lg border border-gray-200 sticky top-6">
                    <!-- Related başlık -->
                    <div class="px-4 py-3 border-b border-gray-200 bg-white rounded-t-lg">
                        <h3 class="text-base font-semibold text-gray-800 flex items-center">
                            <?php gufte_icon('music-box-multiple', 'mr-2 text-blue-600 text-lg'); ?>
                            <?php esc_html_e('Related Lyrics', 'gufte'); ?>
                        </h3>
                    </div>

                    <!-- Related içerik -->
                    <div class="p-4">
                        <?php
                        // --- Related Query (güçlü fallback'lerle) ---
                        $related_post_id     = get_the_ID();
                        $related_categories  = wp_get_post_categories($related_post_id);
                        $related_singers     = wp_get_post_terms($related_post_id, 'singer', array('fields' => 'ids'));
                        
                        $related_args_right  = array(
                            'post_type'           => 'lyrics',
                            'post_status'         => 'publish',
                            'post__not_in'        => array($related_post_id),
                            'posts_per_page'      => 5,
                            'orderby'             => 'date',
                            'order'               => 'DESC',
                            'ignore_sticky_posts' => true,
                        );

                        // Kategoriye göre filtre
                        if (!empty($related_categories)) {
                            $related_args_right['category__in'] = $related_categories;
                        }

                        // Kategori yoksa, şarkıcıya göre dene
                        if (empty($related_categories) && !empty($related_singers) && !is_wp_error($related_singers)) {
                            $related_args_right['tax_query'] = array(
                                array(
                                    'taxonomy' => 'singer',
                                    'field'    => 'term_id',
                                    'terms'    => $related_singers,
                                )
                            );
                        }

                        $related_query_right = new WP_Query($related_args_right);

                        // Fallback: Eğer sonuç yoksa, en son şarkıları göster
                        if (!$related_query_right->have_posts()) {
                            $related_args_right = array(
                                'post_type'           => 'lyrics',
                                'post_status'         => 'publish',
                                'post__not_in'        => array($related_post_id),
                                'posts_per_page'      => 5,
                                'orderby'             => 'date',
                                'order'               => 'DESC',
                                'ignore_sticky_posts' => true,
                            );
                            $related_query_right = new WP_Query($related_args_right);
                        }
                        ?>

                        <?php if ($related_query_right->have_posts()) : ?>
                            <div class="space-y-3" role="list">
                                <?php while ($related_query_right->have_posts()) : $related_query_right->the_post(); 
                                    $rel_title        = get_the_title();
                                    $rel_permalink    = get_permalink();
                                    $rel_singers      = get_the_terms(get_the_ID(), 'singer');
                                    $rel_singer_name  = ($rel_singers && !is_wp_error($rel_singers)) ? $rel_singers[0]->name : '';
                                    $thumb_id         = get_post_thumbnail_id();
                                    $thumb_alt        = $thumb_id ? get_post_meta($thumb_id, '_wp_attachment_image_alt', true) : '';
                                    if (empty($thumb_alt)) { $thumb_alt = $rel_title; }
                                ?>
                                <article class="group" role="listitem">
                                    <a href="<?php echo esc_url($rel_permalink); ?>" 
                                       class="flex items-start gap-3 rounded-lg p-2 -m-2 hover:bg-white transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                                        
                                        <!-- Thumbnail -->
                                        <div class="w-14 h-14 rounded-lg overflow-hidden border border-gray-200 flex-shrink-0 bg-white shadow-sm">
                                            <?php if (has_post_thumbnail()) : ?>
                                                <div class="w-full h-full">
                                                    <?php the_post_thumbnail('thumbnail', array(
                                                        'class'         => 'w-full h-full object-cover',
                                                        'alt'           => esc_attr($thumb_alt),
                                                        'loading'       => 'lazy',
                                                        'decoding'      => 'async',
                                                        'fetchpriority' => 'low'
                                                    )); ?>
                                                </div>
                                            <?php else : ?>
                                                <div class="w-full h-full grid place-items-center bg-gray-100 text-gray-400">
                                                    <?php gufte_icon('music-note', 'text-2xl'); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- İçerik -->
                                        <div class="min-w-0 flex-1">
                                            <h4 class="font-medium text-sm text-gray-800 group-hover:text-blue-600 line-clamp-2 transition-colors duration-200">
                                                <?php echo esc_html($rel_title); ?>
                                            </h4>
                                            
                                            <?php if ($rel_singer_name) : ?>
                                            <p class="text-xs text-gray-600 mt-1 flex items-center">
                                                <?php gufte_icon('microphone', 'mr-1 text-gray-500'); ?>
                                                <?php echo esc_html($rel_singer_name); ?>
                                            </p>
                                            <?php endif; ?>
                                            
                                            <time class="text-[11px] text-gray-500 mt-1 block" datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>">
                                                <?php gufte_icon('clock-outline', 'inline-block mr-1'); ?>
                                                <?php echo esc_html( human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ' . __('ago', 'gufte') ); ?>
                                            </time>
                                        </div>
                                    </a>
                                </article>
                                <?php endwhile; wp_reset_postdata(); ?>
                            </div>

                            <!-- Tümünü gör linki -->
                            <?php if ($related_query_right->post_count >= 5) : ?>
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <?php 
                                $cat_link = !empty($related_categories) ? get_category_link($related_categories[0]) : get_post_type_archive_link('post');
                                ?>
                                <a href="<?php echo esc_url($cat_link); ?>" 
                                   class="text-sm font-medium text-blue-600 hover:text-blue-700 flex items-center justify-center py-2 px-3 rounded-lg hover:bg-white transition-colors duration-200">
                                    <?php esc_html_e('View All Related', 'gufte'); ?>
                                    <?php gufte_icon('arrow-right', 'ml-1'); ?>
                                </a>
                            </div>
                            <?php endif; ?>

                        <?php else : ?>
                            <div class="text-center py-8">
                                <?php gufte_icon('music-note-off', 'text-4xl text-gray-300 mb-3 block'); ?>
                                <p class="text-sm text-gray-500"><?php esc_html_e('No related lyrics found.', 'gufte'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </aside>

        </div> <!-- /lyrics-layout -->

        <!-- Etiketler -->
        <?php if ( has_tag() ) : ?>
        <footer class="entry-footer mt-8 pt-6 border-t border-gray-200" role="contentinfo" aria-label="<?php esc_attr_e('Post Tags', 'gufte'); ?>">
            <div class="tags-links">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-semibold text-gray-700 mr-1">
                        <?php gufte_icon('tag-multiple', 'inline-block mr-1 text-gray-600'); ?>
                        <?php esc_html_e('Tags:', 'gufte'); ?>
                    </span>
                    <?php
                    $tags_list = get_the_tag_list('', ',');
                    if ($tags_list && !is_wp_error($tags_list)) {
                        $tags = explode(',', $tags_list);
                        foreach ($tags as $tag_html) {
                            // A etiketine güvenli şekilde sınıf ekle
                            $tag_html = preg_replace(
                                '/<a\s+href=/',
                                '<a class="inline-flex items-center text-xs px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 hover:text-gray-900 rounded-full transition-all duration-200 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1" href=',
                                $tag_html,
                                1
                            );
                            echo $tag_html;
                        }
                    }
                    ?>
                </div>
            </div>
        </footer>
        <?php endif; ?>

    </div>
</article>

<style>
/* Sticky sidebar için maksimum yükseklik hesaplaması */
@media (min-width: 1024px) {
    .lyrics-layout {
        align-items: start;
    }
    
    /* Sticky pozisyon için fallback */
    .related-lyrics-sticky {
        position: sticky !important;
        position: -webkit-sticky !important;
        top: 80px !important;
    }
}

/* Custom scrollbar için stil */
.custom-scrollbar {
    max-height: calc(80vh - 120px);
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f3f4f6;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

/* Firefox için scrollbar */
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #d1d5db #f3f4f6;
}

/* Sayfalama linkleri için stil */
.page-links .page-number {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    margin: 0 0.25rem;
    background: #f3f4f6;
    color: #374151;
    border-radius: 0.375rem;
    transition: all 0.2s;
}

.page-links .page-number:hover {
    background: #e5e7eb;
    color: #1f2937;
}

.page-links .current .page-number {
    background: #3b82f6;
    color: white;
}

/* Prose içerik stilleri */
.prose h2 { @apply text-xl font-bold mt-6 mb-3; }
.prose h3 { @apply text-lg font-semibold mt-5 mb-2; }
.prose p { @apply mb-4 leading-relaxed; }
.prose ul, .prose ol { @apply mb-4 pl-6; }
.prose li { @apply mb-2; }
.prose blockquote { @apply border-l-4 border-blue-500 pl-4 my-4 italic; }

/* Debug için - eğer hala çalışmazsa */
.lyrics-content {
    position: relative;
}

.lyrics-layout > * {
    position: relative;
}
</style>
<?php // Önceki/Sonraki Yazı Navigasyonu ?>

        
<?php // Mobil için İlgili Yazılar - Swiper ?>
<div class="related-posts-mobile lg:hidden mt-8">
    <div class="bg-white rounded-lg shadow-md border border-gray-200 p-4">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
            <?php gufte_icon('music-box-multiple', 'mr-2 text-blue-600'); ?>
            <?php esc_html_e('Related Lyrics', 'gufte'); ?>
        </h3>
        
        <?php
        $mobile_query = new WP_Query($related_args_right);
        if ($mobile_query->have_posts()) : ?>
            <div class="scroll-wrapper" style="overflow-x: auto; overflow-y: hidden; -webkit-overflow-scrolling: touch; overscroll-behavior-x: contain; touch-action: pan-x; scroll-behavior: auto;">
                <div class="scroll-content" style="display: flex; gap: 12px; padding: 0; will-change: auto;">
                    <?php
                    // Önce tüm görselleri preload için toplayalım
                    $posts_data = array();
                    while ($mobile_query->have_posts()) : $mobile_query->the_post();
                        $related_singers = get_the_terms(get_the_ID(), 'singer');
                        $singer_name = ($related_singers && !is_wp_error($related_singers)) ? $related_singers[0]->name : '';
                        $thumb_url = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail');

                        $posts_data[] = array(
                            'id' => get_the_ID(),
                            'title' => get_the_title(),
                            'permalink' => get_permalink(),
                            'singer' => $singer_name,
                            'thumb' => $thumb_url
                        );
                    endwhile;
                    wp_reset_postdata();

                    // Kartları render et
                    foreach ($posts_data as $post_data) :
                    ?>
                    <div class="scroll-card" style="flex: 0 0 200px; height: 80px;">
                        <a href="<?php echo esc_url($post_data['permalink']); ?>" style="display: block; background: #f9fafb; border-radius: 8px; padding: 12px; text-decoration: none; height: 100%; border: 1px solid #e5e7eb;">
                            <h4 style="font-size: 14px; font-weight: 600; color: #1f2937; margin: 0 0 6px 0; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?php echo esc_html($post_data['title']); ?></h4>
                            <?php if ($post_data['singer']) : ?>
                            <p style="font-size: 12px; color: #6b7280; margin: 0; font-weight: 500;"><?php echo esc_html($post_data['singer']); ?></p>
                            <?php endif; ?>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <style>
            /* Touch scroll optimizasyonu */
            .scroll-wrapper {
                scrollbar-width: none;
                -ms-overflow-style: none;
                -webkit-transform: translateZ(0);
                transform: translateZ(0);
            }
            .scroll-wrapper::-webkit-scrollbar {
                display: none;
            }
            .scroll-content {
                -webkit-backface-visibility: hidden;
                backface-visibility: hidden;
            }
            .scroll-card {
                -webkit-transform: translateZ(0);
                transform: translateZ(0);
                -webkit-backface-visibility: hidden;
                backface-visibility: hidden;
            }
            .scroll-card img {
                pointer-events: none;
                user-select: none;
                -webkit-user-drag: none;
            }
            </style>

        <?php endif; ?>
    </div>
</div>
        
<?php // İçerik Sonu Paylaşım Çağrısı ?>
<div class="content-end-share mt-8 p-6 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg border border-blue-200">
    <div class="text-center">
        <h3 class="text-lg font-bold text-gray-800 mb-2 flex items-center justify-center">
            <?php gufte_icon('heart', 'mr-2 text-2xl text-blue-600'); ?>
            <?php esc_html_e('Did you enjoy these lyrics?', 'gufte'); ?>
        </h3>
        <p class="text-sm text-gray-700 mb-4">
            <?php esc_html_e('Share with your friends and spread the music!', 'gufte'); ?>
        </p>
        
        <div class="flex flex-wrap justify-center gap-2" role="list" aria-label="<?php esc_attr_e('Share options', 'gufte'); ?>">
            <?php
            $social_links = gufte_get_social_share_links();
            foreach ($social_links as $platform => $link) :
                if ($platform === 'copy') : ?>
                    <button 
                        class="social-share-btn-end copy-link-btn flex items-center gap-1.5 px-5 py-3 rounded-full bg-white border-2 transition-all duration-300 hover:scale-105 text-gray-900 hover:text-black min-h-[48px]"
                        style="border-color: <?php echo esc_attr($link['color']); ?>;"
                        data-url="<?php echo esc_attr($link['data-url']); ?>"
                        title="<?php echo esc_attr($link['label']); ?>"
                        role="listitem"
                        aria-label="<?php echo esc_attr($link['label']); ?>">
                        <?php
                            $icon_name = str_replace('mdi:', '', $link['icon']);
                            gufte_icon($icon_name, 'text-lg');
                        ?>
                        <span class="font-medium text-sm"><?php echo esc_html($link['label']); ?></span>
                    </button>
                <?php else : ?>
                    <a href="<?php echo esc_url($link['url']); ?>"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="social-share-btn-end flex items-center gap-1.5 px-5 py-3 rounded-full bg-white border-2 transition-all duration-300 hover:scale-105 text-gray-900 hover:text-black min-h-[48px]"
                       style="border-color: <?php echo esc_attr($link['color']); ?>;"
                       role="listitem"
                       aria-label="Share on <?php echo esc_attr($link['label']); ?> (opens in new window)">
                        <?php
                            $icon_name = str_replace('mdi:', '', $link['icon']);
                            gufte_icon($icon_name, 'text-lg');
                        ?>
                        <span class="font-medium text-sm"><?php echo esc_html($link['label']); ?></span>
                    </a>
                <?php endif;
            endforeach; ?>
        </div>
    </div>
</div>

<?php
// Detaylı Awards Section - FAQ'dan önce ekleyin
if ($awards) : ?>
<section id="awards-details-<?php echo get_the_ID(); ?>" 
         class="awards-details-section hidden mt-8 p-6 bg-gradient-to-br from-amber-50 to-yellow-50 rounded-xl border border-amber-200 shadow-lg"
         aria-labelledby="awards-details-heading">
    
    <!-- Section Header -->
    <div class="flex items-center justify-between mb-6 pb-4 border-b border-amber-200">
        <h3 id="awards-details-heading" class="text-xl font-bold text-gray-800 flex items-center">
            <?php gufte_icon('trophy-variant', 'mr-3 text-amber-600 text-2xl'); ?>
            <?php printf(esc_html__('Awards & Recognition for %s', 'gufte'), get_the_title()); ?>
        </h3>
        
        <button type="button" 
                class="awards-close-btn text-gray-500 hover:text-gray-700 transition-colors duration-200"
                data-awards-toggle="awards-details-<?php echo get_the_ID(); ?>"
                aria-label="Close awards section">
            <?php gufte_icon('close', 'text-xl'); ?>
        </button>
    </div>
    
    <!-- Awards Statistics -->
    <div class="awards-stats grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="stat-card bg-white rounded-lg p-4 text-center border border-amber-100 shadow-sm">
            <div class="text-2xl font-bold text-amber-600"><?php echo count($awards); ?></div>
            <div class="text-sm text-gray-600">Total Awards</div>
        </div>
        
        <?php if ($awards_summary['winners'] > 0) : ?>
        <div class="stat-card bg-white rounded-lg p-4 text-center border border-green-100 shadow-sm">
            <div class="text-2xl font-bold text-green-600"><?php echo $awards_summary['winners']; ?></div>
            <div class="text-sm text-gray-600">Winners</div>
        </div>
        <?php endif; ?>
        
        <?php if ($awards_summary['nominees'] > 0) : ?>
        <div class="stat-card bg-white rounded-lg p-4 text-center border border-blue-100 shadow-sm">
            <div class="text-2xl font-bold text-blue-600"><?php echo $awards_summary['nominees']; ?></div>
            <div class="text-sm text-gray-600">Nominees</div>
        </div>
        <?php endif; ?>
        
        <?php if ($awards_summary['mentions'] > 0) : ?>
        <div class="stat-card bg-white rounded-lg p-4 text-center border border-purple-100 shadow-sm">
            <div class="text-2xl font-bold text-purple-600"><?php echo $awards_summary['mentions']; ?></div>
            <div class="text-sm text-gray-600">Mentions</div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Awards by Year -->
    <div class="awards-by-year">
        <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <?php gufte_icon('calendar-star', 'mr-2 text-amber-600'); ?>
            Awards Timeline
        </h4>
        
        <div class="space-y-4">
            <?php 
            $awards_by_year = array();
            foreach ($awards as $award) {
                $year = !empty($award['year']) ? $award['year'] : 'Unknown';
                if (!isset($awards_by_year[$year])) {
                    $awards_by_year[$year] = array();
                }
                $awards_by_year[$year][] = $award;
            }
            
            krsort($awards_by_year);
            
            foreach ($awards_by_year as $year => $year_awards) :
            ?>
            <div class="year-group bg-white rounded-lg p-5 border border-gray-200 shadow-sm">
                <div class="year-header mb-4">
                    <h5 class="text-lg font-bold text-gray-800 flex items-center">
                        <?php gufte_icon('calendar', 'mr-2 text-blue-600'); ?>
                        <?php echo esc_html($year); ?>
                        <span class="ml-2 text-sm font-normal text-gray-500">
                            (<?php echo count($year_awards); ?> <?php echo _n('award', 'awards', count($year_awards), 'gufte'); ?>)
                        </span>
                    </h5>
                </div>
                
                <div class="year-awards space-y-3">
                    <?php foreach ($year_awards as $award) : 
                        $result_config = gufte_get_award_result_config($award['result']);
                    ?>
                    <div class="award-item flex items-start gap-4 p-4 bg-gray-50 rounded-lg border border-gray-100 hover:bg-gray-100 transition-colors duration-200">
                        <div class="award-icon flex-shrink-0">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-xl shadow-md"
                                 style="background: <?php echo $result_config['gradient']; ?>">
                                <?php
                                    $icon_name = str_replace('mdi:', '', $result_config['icon']);
                                    gufte_icon($icon_name, '');
                                ?>
                            </div>
                        </div>
                        
                        <div class="award-content flex-1 min-w-0">
                            <div class="award-title font-semibold text-gray-800 mb-1">
                                <?php echo esc_html($award['category']); ?>
                            </div>
                            
<div class="award-organization text-sm text-gray-600 mb-2">
    <?php 
    if (!empty($award['organization'])) {
        echo esc_html($award['organization']); // Hiyerarşiden gelen organization
    } elseif (!empty($award['type_label'])) {
        echo esc_html($award['type_label']); // Meta'dan gelen type
    } else {
        echo esc_html__('Awards', 'gufte'); // Fallback
    }
    ?>
</div>
                            
                            <div class="award-result flex items-center gap-2">
                                <span class="result-badge inline-flex items-center px-2 py-1 rounded-full text-xs font-medium text-white"
                                      style="background: <?php echo $result_config['color']; ?>">
                                    <?php
                                        $icon_name = str_replace('mdi:', '', $result_config['icon']);
                                        gufte_icon($icon_name, 'mr-1');
                                    ?>
                                    <?php echo esc_html($result_config['label']); ?>
                                </span>
                                
                                <?php if (!empty($award['notes'])) : ?>
                                <span class="award-notes text-xs text-gray-500 italic">
                                    <?php echo esc_html($award['notes']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
        // FAQ Bölümünü ekle - şarkı sözleri bölümünden sonra
        echo gufte_generate_auto_faq();
        
        ?>
        <?php
            $prev_post = get_previous_post();
            $next_post = get_next_post();
            if ($prev_post || $next_post) :
        ?>
        <nav class="post-navigation my-8 p-6 bg-white rounded-lg shadow-md border border-gray-200" aria-label="<?php esc_attr_e('Post Navigation', 'gufte'); ?>">
            <div class="flex flex-col sm:flex-row justify-between items-stretch gap-4"> <?php // items-stretch eklendi ?>
                <div class="previous-post flex-1 <?php echo !$next_post ? 'sm:text-left' : 'sm:text-left'; ?>"> <?php // Hizalama ayarlandı ?>
                    <?php if ( ! empty( $prev_post ) ) :
                        $prev_singers = get_the_terms($prev_post->ID, 'singer');
                        $prev_singer_name = ($prev_singers && !is_wp_error($prev_singers)) ? reset($prev_singers)->name : '';
                        $prev_singer_image_id = ($prev_singers && !is_wp_error($prev_singers)) ? get_term_meta(reset($prev_singers)->term_id, 'singer_image_id', true) : '';
                        $prev_singer_image = $prev_singer_image_id ? wp_get_attachment_image_url($prev_singer_image_id, 'thumbnail') : '';
                    ?>
                        <span class="text-xs text-gray-600 block mb-1"><?php esc_html_e('Previous Lyrics', 'gufte'); ?></span>
                        <a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>" 
                           class="text-base font-medium text-blue-700 hover:text-blue-800 transition-colors duration-200 flex items-center group"
                           aria-label="<?php echo esc_attr(sprintf(__('Previous post: %s', 'gufte'), get_the_title($prev_post->ID))); ?>">
                            <?php gufte_icon('chevron-left', 'mr-2 text-xl group-hover:-translate-x-1 transition-transform duration-200'); ?>
                             <?php if (!empty($prev_singer_image)) : ?>
                                <img src="<?php echo esc_url($prev_singer_image); ?>" 
                                     alt="<?php echo esc_attr($prev_singer_name); ?>" 
                                     class="w-6 h-6 rounded-full object-cover mr-2 border border-gray-200 flex-shrink-0">
                            <?php endif; ?>
                            <div class="truncate">
                                <span class="block leading-tight"><?php echo esc_html( get_the_title( $prev_post->ID ) ); ?></span>
                                <?php if (!empty($prev_singer_name)) : ?>
                                    <span class="text-gray-600 text-xs block"><?php echo esc_html($prev_singer_name); ?></span>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php else: ?>
                         <div class="h-full flex items-center justify-center text-gray-500 text-sm italic">
                             <?php esc_html_e('No previous lyrics.', 'gufte'); ?>
                         </div>
                    <?php endif; ?>
                </div>

                 <?php if ($prev_post && $next_post): // Sadece ikisi de varken ayırıcı göster ?>
                 <div class="border-l border-gray-200 hidden sm:block"></div>
                 <?php endif; ?>

                <div class="next-post flex-1 <?php echo !$prev_post ? 'sm:text-right' : 'sm:text-right'; ?>"> <?php // Hizalama ayarlandı ?>
                    <?php if ( ! empty( $next_post ) ) :
                         $next_singers = get_the_terms($next_post->ID, 'singer');
                         $next_singer_name = ($next_singers && !is_wp_error($next_singers)) ? reset($next_singers)->name : '';
                         $next_singer_image_id = ($next_singers && !is_wp_error($next_singers)) ? get_term_meta(reset($next_singers)->term_id, 'singer_image_id', true) : '';
                         $next_singer_image = $next_singer_image_id ? wp_get_attachment_image_url($next_singer_image_id, 'thumbnail') : '';
                    ?>
                        <span class="text-xs text-gray-600 block mb-1 text-right"><?php esc_html_e('Next Lyrics', 'gufte'); ?></span>
                        <a href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>" 
                           class="text-base font-medium text-blue-700 hover:text-blue-800 transition-colors duration-200 flex items-center justify-end group"
                           aria-label="<?php echo esc_attr(sprintf(__('Next post: %s', 'gufte'), get_the_title($next_post->ID))); ?>">
                            <div class="truncate text-right">
                                <span class="block leading-tight"><?php echo esc_html( get_the_title( $next_post->ID ) ); ?></span>
                                <?php if (!empty($next_singer_name)) : ?>
                                    <span class="text-gray-600 text-xs block"><?php echo esc_html($next_singer_name); ?></span>
                                <?php endif; ?>
                            </div>
                             <?php if (!empty($next_singer_image)) : ?>
                                <img src="<?php echo esc_url($next_singer_image); ?>" 
                                     alt="<?php echo esc_attr($next_singer_name); ?>" 
                                     class="w-6 h-6 rounded-full object-cover ml-2 border border-gray-200 flex-shrink-0">
                            <?php endif; ?>
                            <?php gufte_icon('chevron-right', 'ml-2 text-xl group-hover:translate-x-1 transition-transform duration-200'); ?>
                        </a>
                     <?php else: ?>
                         <div class="h-full flex items-center justify-center text-gray-500 text-sm italic">
                              <?php esc_html_e('No next lyrics.', 'gufte'); ?>
                         </div>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
        <?php endif; // End if $prev_post || $next_post ?>


        <?php
        // Yorumlar Alanı
        if ( comments_open() || get_comments_number() ) :
             echo '<div class="comments-area my-8 p-6 bg-white rounded-lg shadow-md border border-gray-200">';
             comments_template();
             echo '</div>';
        endif;
        ?>

        <?php endwhile; // End of the loop. ?>
    </main></div><?php
// Footer.php'de müzik embed kodları kullanılacaksa bu değişkenler orada global olarak erişilebilir olmalı
// Veya footer'ı çağırmadan önce bu değişkenleri bir şekilde footer'a iletmek gerekir (örn: action hook)
global $spotify_embed, $youtube_embed, $apple_music_embed;

// Music Video Modal
$video_embed = get_post_meta(get_the_ID(), 'music_video_embed', true);
$video_url = get_post_meta(get_the_ID(), 'music_video_url', true);

if (!empty($video_embed) || !empty($video_url)) :
    // Get final embed code
    $final_embed = '';
    if (!empty($video_embed)) {
        $final_embed = $video_embed;
    } elseif (!empty($video_url)) {
        $final_embed = gufte_convert_video_url_to_embed($video_url);
    }

    $video_title = get_post_meta(get_the_ID(), 'video_title', true);
    if (empty($video_title)) {
        $video_title = get_the_title() . ' - Music Video';
    }

    $final_embed = gufte_normalize_video_embed($final_embed, $video_title);
?>
<!-- Music Video Modal -->
<div id="musicVideoModal" class="music-video-modal" role="dialog" aria-modal="true" aria-labelledby="videoModalTitle">
    <div class="music-video-modal-overlay"></div>
    <div class="music-video-modal-content">
        <div class="music-video-modal-header">
            <h3 id="videoModalTitle" class="music-video-modal-title">
                <?php echo esc_html($video_title); ?>
            </h3>
            <button type="button" class="music-video-modal-close" aria-label="Close video">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="music-video-modal-body">
            <div class="video-wrapper aspect-video w-full bg-black">
                <?php echo $final_embed; ?>
            </div>
        </div>
    </div>
</div>

<!-- Music Video Modal CSS -->
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/music-video-modal.css?ver=<?php echo GUFTE_VERSION; ?>">

<!-- Music Video Modal JS -->
<script src="<?php echo get_template_directory_uri(); ?>/assets/js/music-video-modal.js?ver=<?php echo GUFTE_VERSION; ?>"></script>
<?php endif; ?>

<!-- Lyrics Block Frontend JS (for FAQ accordion) -->
<script src="<?php echo get_template_directory_uri(); ?>/blocks/lyrics-translations/frontend.js?ver=<?php echo GUFTE_VERSION; ?>"></script>

<!-- Line Export CSS & JS -->
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/line-export.css?ver=<?php echo GUFTE_VERSION; ?>">
<script>
var arcurasLineExport = {
    siteUrl: '<?php echo get_site_url(); ?>',
    siteName: '<?php echo get_bloginfo('name'); ?>',
    postTitle: '<?php echo esc_js(get_the_title()); ?>',
    postAuthor: '<?php echo esc_js(get_post_meta(get_the_ID(), '_artist_name', true)); ?>',
    featuredImage: '<?php echo esc_js(get_the_post_thumbnail_url(get_the_ID(), 'medium')); ?>',
    nonce: '<?php echo wp_create_nonce('wp_rest'); ?>',
    isUserLoggedIn: <?php echo is_user_logged_in() ? 'true' : 'false'; ?>
};
</script>
<script src="<?php echo get_template_directory_uri(); ?>/assets/js/line-export.js?ver=<?php echo GUFTE_VERSION; ?>"></script>

<?php get_footer(); ?>
