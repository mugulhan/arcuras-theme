<?php
/**
 * Template Name: Lyrics Page
 *
 * Gelişmiş filtreleme seçenekleriyle şarkı sözlerini listeleyen sayfa
 * Kategoriler, çeviri dilleri, şarkıcılar, albümler ve tarih filtrelemeleri içerir
 *
 * @package Gufte
 */

get_header();

// ---- URL Parametreleri ----
$paged = max(1, get_query_var('paged') ? get_query_var('paged') : (get_query_var('page') ?: 1));
$q = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
$sort = isset($_GET['sort']) ? sanitize_text_field(wp_unslash($_GET['sort'])) : 'date_desc';
$per_page = isset($_GET['per_page']) ? max(12, min(60, intval($_GET['per_page']))) : 24;
$letter = isset($_GET['letter']) ? strtoupper(sanitize_text_field(wp_unslash($_GET['letter']))) : '';

// Filtreleme parametreleri
$filter_category = isset($_GET['category']) ? intval($_GET['category']) : '';
$filter_singer = isset($_GET['singer']) ? intval($_GET['singer']) : '';
$filter_album = isset($_GET['album']) ? intval($_GET['album']) : '';
$filter_translation = isset($_GET['translation']) ? sanitize_text_field($_GET['translation']) : '';
$filter_year = isset($_GET['year']) ? intval($_GET['year']) : '';
$filter_genre = isset($_GET['genre']) ? sanitize_text_field($_GET['genre']) : '';
$filter_platform = isset($_GET['platform']) ? sanitize_text_field($_GET['platform']) : '';

// ---- WP_Query için temel argümanlar ----
$args = array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'posts_per_page' => $per_page,
    'paged' => $paged,
);

// Arama
if ($q !== '') {
    $args['s'] = $q;
}

// Sıralama
switch ($sort) {
    case 'title_asc':
        $args['orderby'] = 'title';
        $args['order'] = 'ASC';
        break;
    case 'title_desc':
        $args['orderby'] = 'title';
        $args['order'] = 'DESC';
        break;
    case 'date_asc':
        $args['orderby'] = 'date';
        $args['order'] = 'ASC';
        break;
    case 'views_desc':
        $args['orderby'] = 'meta_value_num';
        $args['meta_key'] = 'post_views_count';
        $args['order'] = 'DESC';
        break;
    case 'comment_count':
        $args['orderby'] = 'comment_count';
        $args['order'] = 'DESC';
        break;
    case 'release_date':
        $args['orderby'] = 'meta_value';
        $args['meta_key'] = '_release_date';
        $args['meta_type'] = 'DATE';
        $args['order'] = 'DESC';
        break;
    case 'date_desc':
    default:
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
        break;
}

// Taksonomi ve Meta Query'leri oluştur
$tax_query = array();
$meta_query = array();

// Kategori filtresi
if ($filter_category) {
    $tax_query[] = array(
        'taxonomy' => 'category',
        'field' => 'term_id',
        'terms' => $filter_category,
    );
}

// Şarkıcı filtresi
if ($filter_singer) {
    $tax_query[] = array(
        'taxonomy' => 'singer',
        'field' => 'term_id',
        'terms' => $filter_singer,
    );
}

// Albüm filtresi
if ($filter_album) {
    $tax_query[] = array(
        'taxonomy' => 'album',
        'field' => 'term_id',
        'terms' => $filter_album,
    );
}

// Çeviri filtresi
if ($filter_translation === 'has_translation') {
    $meta_query[] = array(
        'key' => '_available_languages',
        'compare' => 'EXISTS'
    );
} elseif ($filter_translation === 'no_translation') {
    $meta_query[] = array(
        'key' => '_available_languages',
        'compare' => 'NOT EXISTS'
    );
} elseif (!empty($filter_translation) && $filter_translation !== 'all') {
    $meta_query[] = array(
        'key' => '_available_languages',
        'value' => $filter_translation,
        'compare' => 'LIKE'
    );
}

// Platform filtresi
if ($filter_platform === 'spotify') {
    $meta_query[] = array(
        'key' => 'spotify_url',
        'compare' => 'EXISTS'
    );
} elseif ($filter_platform === 'youtube') {
    $meta_query[] = array(
        'key' => 'youtube_url',
        'compare' => 'EXISTS'
    );
} elseif ($filter_platform === 'apple_music') {
    $meta_query[] = array(
        'key' => 'apple_music_url',
        'compare' => 'EXISTS'
    );
} elseif ($filter_platform === 'video') {
    $meta_query[] = array(
        'relation' => 'OR',
        array(
            'key' => 'music_video_url',
            'compare' => 'EXISTS'
        ),
        array(
            'key' => 'music_video_embed',
            'compare' => 'EXISTS'
        )
    );
}

// Genre filtresi
if (!empty($filter_genre)) {
    $meta_query[] = array(
        'key' => '_music_genre',
        'value' => $filter_genre,
        'compare' => 'LIKE'
    );
}

// Yıl filtresi
if ($filter_year) {
    $meta_query[] = array(
        'key' => '_release_date',
        'value' => array(
            $filter_year . '-01-01',
            $filter_year . '-12-31'
        ),
        'type' => 'DATE',
        'compare' => 'BETWEEN'
    );
}

// Baş harf filtresi
if ($letter !== '') {
    global $wpdb;
    $post_ids = array();
    
    if ($letter === '#') {
        // Harf olmayan karakterlerle başlayanlar
        $post_ids = $wpdb->get_col("
            SELECT ID FROM {$wpdb->posts} 
            WHERE post_type = 'post' 
            AND post_status = 'publish'
            AND UPPER(LEFT(post_title, 1)) NOT REGEXP '^[A-ZÇĞİÖŞÜ]'
        ");
    } else {
        // Belirli harfle başlayanlar
        $post_ids = $wpdb->get_col($wpdb->prepare("
            SELECT ID FROM {$wpdb->posts} 
            WHERE post_type = 'post' 
            AND post_status = 'publish'
            AND UPPER(LEFT(post_title, 1)) = %s
        ", $letter));
    }
    
    if (!empty($post_ids)) {
        $args['post__in'] = $post_ids;
    } else {
        $args['post__in'] = array(0); // Hiç sonuç döndürme
    }
}

// Query'leri ekle
if (!empty($tax_query)) {
    $args['tax_query'] = $tax_query;
}

if (!empty($meta_query)) {
    $args['meta_query'] = $meta_query;
}

// Ana query'yi çalıştır
$lyrics_query = new WP_Query($args);

// İstatistikler için veriler
$total_posts = wp_count_posts('post')->publish;
$categories = get_categories(array('hide_empty' => true));
$singers = get_terms(array('taxonomy' => 'singer', 'hide_empty' => true));
$albums = get_terms(array('taxonomy' => 'album', 'hide_empty' => true));

// Çeviri dilleri istatistikleri
$translation_stats = array();
if (function_exists('gufte_get_language_statistics')) {
    $translation_stats = gufte_get_language_statistics();
}

// Genre listesi (meta_value'lardan unique değerler)
global $wpdb;
$genres = $wpdb->get_col("
    SELECT DISTINCT meta_value 
    FROM {$wpdb->postmeta} 
    WHERE meta_key = '_music_genre' 
    AND meta_value != ''
    ORDER BY meta_value ASC
");

// Yıl listesi
$years = $wpdb->get_col("
    SELECT DISTINCT YEAR(meta_value) as year
    FROM {$wpdb->postmeta} 
    WHERE meta_key = '_release_date' 
    AND meta_value != ''
    ORDER BY year DESC
");

// A-Z harfleri
$letters = array_merge(range('A', 'Z'), array('Ç','Ğ','İ','Ö','Ş','Ü','#'));

?>

<div class="site-content-wrapper flex flex-col md:flex-row">

    <?php get_template_part('template-parts/arcuras-sidebar'); ?>

    <main id="primary" class="site-main flex-1 bg-white overflow-x-hidden">

        <?php
        // Breadcrumb
        set_query_var('breadcrumb_items', array(
            array('label' => 'Home', 'url' => home_url('/')),
            array('label' => __('Lyrics', 'gufte'))
        ));
        get_template_part('template-parts/page-components/page-breadcrumb');

        // Hero
        if (have_posts()) : while (have_posts()) : the_post();
            $hero_description = get_the_content() ? get_the_content() : '';
        endwhile; endif;

        set_query_var('hero_title', get_the_title());
        set_query_var('hero_icon', 'music');
        set_query_var('hero_description', $hero_description);
        set_query_var('hero_meta', array(
            __('Total Lyrics', 'gufte') => number_format_i18n($lyrics_query->found_posts)
        ));
        get_template_part('template-parts/page-components/page-hero');

        // Filters
        $results_text = sprintf(_n('%s result', '%s results', $lyrics_query->found_posts, 'gufte'), number_format_i18n($lyrics_query->found_posts));
        if ($letter) {
            $results_text .= ' — ' . sprintf(__('letter: %s', 'gufte'), $letter);
        }

        set_query_var('filter_config', array(
            'search' => array(
                'enabled' => true,
                'name' => 'q',
                'value' => $q,
                'label' => __('Search lyrics', 'gufte'),
                'placeholder' => __('Song title, lyrics, artist...', 'gufte'),
                'id' => 'lyrics-search'
            ),
            'sort' => array(
                'enabled' => true,
                'name' => 'sort',
                'value' => $sort,
                'label' => __('Sort by', 'gufte'),
                'id' => 'lyrics-sort',
                'options' => array(
                    'date_desc' => __('Newest first', 'gufte'),
                    'date_asc' => __('Oldest first', 'gufte'),
                    'title_asc' => __('Title (A→Z)', 'gufte'),
                    'title_desc' => __('Title (Z→A)', 'gufte'),
                    'views_desc' => __('Most viewed', 'gufte'),
                    'comment_count' => __('Most commented', 'gufte'),
                    'release_date' => __('Release date', 'gufte'),
                )
            ),
            'per_page' => array(
                'enabled' => true,
                'name' => 'per_page',
                'value' => $per_page,
                'label' => __('Per page', 'gufte'),
                'id' => 'lyrics-per-page',
                'options' => array(12, 24, 36, 48, 60)
            ),
            'results_text' => $results_text,
            'search_query' => $q,
            'action_url' => get_permalink()
        ));
        get_template_part('template-parts/page-components/page-filters');

        // Letter Filter
        set_query_var('letter_filter_config', array(
            'current_letter' => $letter,
            'base_url' => get_permalink(),
            'preserve_params' => array(
                'q' => $q,
                'sort' => $sort,
                'per_page' => $per_page
            ),
            'letters' => $letters,
            'show_all_button' => true,
            'all_button_text' => __('All', 'gufte')
        ));
        get_template_part('template-parts/page-components/page-letter-filter');
        ?>

        <!-- Şarkı Listesi -->
        <section class="lyrics-listing px-4 sm:px-6 lg:px-8 py-6" id="lyrics-container">
            <?php if ($lyrics_query->have_posts()) : ?>
                <div class="lyrics-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6" data-view="grid">
                    <?php while ($lyrics_query->have_posts()) : $lyrics_query->the_post(); 
                        // Şarkı bilgilerini al
                        $singers = get_the_terms(get_the_ID(), 'singer');
                        $albums = get_the_terms(get_the_ID(), 'album');
                        $categories = get_the_category();
                        $available_languages = get_post_meta(get_the_ID(), '_available_languages', true);
                        $release_date = get_post_meta(get_the_ID(), '_release_date', true);
                        $music_genre = get_post_meta(get_the_ID(), '_music_genre', true);
                        
                        // Platform linkleri
                        $spotify_url = get_post_meta(get_the_ID(), 'spotify_url', true);
                        $youtube_url = get_post_meta(get_the_ID(), 'youtube_url', true);
                        $apple_music_url = get_post_meta(get_the_ID(), 'apple_music_url', true);
                        $has_video = get_post_meta(get_the_ID(), 'music_video_url', true) || get_post_meta(get_the_ID(), 'music_video_embed', true);
                    ?>
                        <article class="lyrics-card group bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden">
                            <!-- Görsel -->
                            <a href="<?php the_permalink(); ?>" class="block relative aspect-square overflow-hidden bg-gray-100">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php the_post_thumbnail('medium', array(
                                        'class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-500',
                                        'loading' => 'lazy'
                                    )); ?>
                                <?php else : ?>
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-primary-100 to-primary-200">
                                        <?php gufte_icon('music-note', 'text-6xl text-primary-400'); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Overlay bilgiler -->
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                    <div class="absolute bottom-0 left-0 right-0 p-4 text-white">
                                        <!-- Platform ikonları -->
                                        <div class="flex items-center gap-2 mb-2">
                                            <?php if ($spotify_url) : ?>
                                                <?php gufte_icon('spotify', 'text-green-400'); ?>
                                            <?php endif; ?>
                                            <?php if ($youtube_url) : ?>
                                                <?php gufte_icon('youtube', 'text-red-400'); ?>
                                            <?php endif; ?>
                                            <?php if ($apple_music_url) : ?>
                                                <?php gufte_icon('apple', 'text-gray-300'); ?>
                                            <?php endif; ?>
                                            <?php if ($has_video) : ?>
                                                <?php gufte_icon('play-circle', 'text-blue-400'); ?>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="text-sm font-medium">
                                            <?php esc_html_e('View Details', 'gufte'); ?> →
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Çeviri badge'i -->
                                <?php if (is_array($available_languages) && !empty($available_languages)) : ?>
                                    <div class="absolute top-2 right-2 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                                        <?php echo count($available_languages); ?> <?php esc_html_e('Lang', 'gufte'); ?>
                                    </div>
                                <?php endif; ?>
                            </a>
                            
                            <!-- İçerik -->
                            <div class="p-4">
                                <!-- Başlık -->
                                <h2 class="text-lg font-semibold mb-2 line-clamp-2">
                                    <a href="<?php the_permalink(); ?>" class="text-gray-900 hover:text-primary-600 transition">
                                        <?php the_title(); ?>
                                    </a>
                                </h2>
                                
                                <!-- Şarkıcı -->
                                <?php if ($singers && !is_wp_error($singers)) : ?>
                                    <div class="text-sm text-gray-600 mb-2 flex items-center">
                                        <?php gufte_icon('microphone', 'inline text-xs mr-1'); ?>
                                        <span>
                                        <?php
                                        $singer_links = array();
                                        foreach ($singers as $singer) {
                                            $singer_links[] = '<a href="' . esc_url(get_term_link($singer)) . '" class="hover:text-primary-600">' . esc_html($singer->name) . '</a>';
                                        }
                                        echo implode(', ', $singer_links);
                                        ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Albüm -->
                                <?php if ($albums && !is_wp_error($albums)) : ?>
                                    <div class="text-sm text-gray-500 mb-2 flex items-center">
                                        <?php gufte_icon('album', 'inline text-xs mr-1'); ?>
                                        <span>
                                        <?php
                                        $album = reset($albums);
                                        $album_year = get_term_meta($album->term_id, 'album_year', true);
                                        ?>
                                        <a href="<?php echo esc_url(get_term_link($album)); ?>" class="hover:text-primary-600">
                                            <?php echo esc_html($album->name); ?>
                                            <?php if ($album_year) echo ' (' . esc_html($album_year) . ')'; ?>
                                        </a>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Meta bilgiler -->
                                <div class="flex flex-wrap gap-2 mt-3">
                                    <?php if ($music_genre) : ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-purple-100 text-purple-800">
                                            <?php echo esc_html($music_genre); ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($release_date) : ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">
                                            <?php echo esc_html(date('Y', strtotime($release_date))); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Kategori ve Çeviri Dilleri -->
                                <div class="mt-3 pt-3 border-t border-gray-100">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <?php
                                        // Orijinal kategori
                                        if ($categories && !is_wp_error($categories)) :
                                            $category = reset($categories);
                                        ?>
                                            <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>" class="language-badge original flex items-center bg-white/90 backdrop-blur-sm rounded-full px-2 py-1 border border-accent-300/40 hover:border-accent-500/60 transition-all duration-300 hover:scale-105 hover:shadow-lg group text-xs">
                                                <?php gufte_icon('file-document', 'mr-1 text-sm text-accent-600 group-hover:text-accent-700 transition-colors duration-300 w-4 h-4'); ?>
                                                <span class="font-medium text-gray-800 group-hover:text-accent-700 transition-colors duration-300"><?php echo esc_html($category->name); ?></span>
                                            </a>
                                        <?php endif; ?>

                                        <?php
                                        // Çeviriler varsa dropdown göster (orijinal dil hariç)
                                        if (is_array($available_languages) && count($available_languages) > 0) :
                                            // Orijinal dili çıkar - kategori adını kullan
                                            $original_language = '';
                                            if ($categories && !is_wp_error($categories)) {
                                                $category = reset($categories);
                                                $original_language = strtolower($category->name); // "English" -> "english"
                                            }

                                            // available_languages içinden orijinal dili çıkar
                                            $translations_only = array_filter($available_languages, function($lang) use ($original_language) {
                                                return strtolower($lang) !== $original_language;
                                            });

                                            // Sadece gerçek çeviriler varsa dropdown göster
                                            if (count($translations_only) > 0) :
                                        ?>
                                            <div class="translations-dropdown-mini relative">
                                                <button type="button" class="translations-trigger-mini language-badge translations-trigger flex items-center bg-white/90 backdrop-blur-sm rounded-full px-2 py-1 border border-primary-300/40 hover:border-primary-500/60 transition-all duration-300 hover:scale-105 hover:shadow-lg group cursor-pointer text-xs">
                                                    <?php gufte_icon('translate', 'mr-1 text-sm text-primary-600 group-hover:text-primary-700 transition-colors duration-300 w-4 h-4'); ?>
                                                    <span class="font-medium text-gray-800 group-hover:text-primary-700 transition-colors duration-300">
                                                        <?php echo count($translations_only); ?> <?php echo _n('translation', 'translations', count($translations_only), 'gufte'); ?>
                                                    </span>
                                                    <?php gufte_icon('chevron-down', 'ml-1 text-xs text-gray-600 group-hover:text-primary-700 transition-all duration-300 dropdown-arrow w-3 h-3'); ?>
                                                </button>

                                                <div class="translations-menu-mini absolute top-full left-0 mt-2 bg-white rounded-lg shadow-xl border border-gray-200 py-2 min-w-48 z-50 opacity-0 invisible transform translate-y-[-10px] transition-all duration-300">
                                                    <?php foreach ($translations_only as $lang) :
                                                        $lang_info = gufte_get_language_info($lang);
                                                    ?>
                                                        <a href="<?php echo esc_url(add_query_arg('lang', $lang, get_permalink())); ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-700 transition-colors duration-200">
                                                            <span class="text-lg mr-3"><?php echo $lang_info['flag']; ?></span>
                                                            <span class="font-medium"><?php echo esc_html($lang_info['native_name']); ?></span>
                                                            <span class="ml-auto text-xs text-gray-500"><?php echo esc_html($lang_info['english_name']); ?></span>
                                                        </a>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; endif; ?>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
            <?php else : ?>
                <div class="bg-white border border-gray-200 rounded-xl p-10 text-center">
                    <?php gufte_icon('music-note-off', 'text-6xl text-gray-300 mb-4'); ?>
                    <p class="text-lg text-gray-600 mb-2">
                        <?php esc_html_e('No songs found.', 'gufte'); ?>
                    </p>
                    <p class="text-sm text-gray-500">
                        <?php esc_html_e('Try adjusting your filters or search terms.', 'gufte'); ?>
                    </p>
                    <a href="<?php echo esc_url(get_permalink()); ?>" class="inline-block mt-4 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                        <?php esc_html_e('Clear Filters', 'gufte'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </section>

        <?php
        // Pagination
        if ($lyrics_query->max_num_pages > 1) {
            set_query_var('pagination_config', array(
                'total_pages' => $lyrics_query->max_num_pages,
                'current_page' => $paged,
                'base_url' => get_permalink(),
                'preserve_params' => array(
                    'q' => $q,
                    'sort' => $sort,
                    'per_page' => $per_page,
                    'category' => $filter_category,
                    'singer' => $filter_singer,
                    'album' => $filter_album,
                    'translation' => $filter_translation,
                    'year' => $filter_year,
                    'genre' => $filter_genre,
                    'platform' => $filter_platform,
                    'letter' => $letter
                ),
                'mid_size' => 2,
                'prev_text' => __('Previous', 'gufte'),
                'next_text' => __('Next', 'gufte'),
            ));
            get_template_part('template-parts/page-components/page-pagination');
        }

        wp_reset_postdata();
        ?>

    </main>
</div>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Görünüm değiştirme
    const viewBtns = document.querySelectorAll('.view-btn');
    const container = document.getElementById('lyrics-container');
    
    viewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const view = this.dataset.view;
            const grid = container.querySelector('.lyrics-grid');
            
            // Aktif butonu güncelle
            viewBtns.forEach(b => b.classList.remove('active', 'bg-primary-100', 'border-primary-600'));
            this.classList.add('active', 'bg-primary-100', 'border-primary-600');
            
            // Grid sınıflarını güncelle
            if (view === 'list') {
                grid.classList.remove('grid-cols-1', 'sm:grid-cols-2', 'lg:grid-cols-3', 'xl:grid-cols-4');
                grid.classList.add('grid-cols-1', 'max-w-3xl', 'mx-auto');
                grid.dataset.view = 'list';
            } else {
                grid.classList.remove('grid-cols-1', 'max-w-3xl', 'mx-auto');
                grid.classList.add('grid-cols-1', 'sm:grid-cols-2', 'lg:grid-cols-3', 'xl:grid-cols-4');
                grid.dataset.view = 'grid';
            }
            
            // Tercihi localStorage'a kaydet
            localStorage.setItem('lyrics_view', view);
        });
    });
    
    // Kayıtlı görünümü uygula
    const savedView = localStorage.getItem('lyrics_view');
    if (savedView === 'list') {
        document.querySelector('[data-view="list"]').click();
    }
    
    // Form auto-submit on filter change (opsiyonel)
    const filterSelects = document.querySelectorAll('#lyrics-filter-form select:not(#lyrics-sort):not(#lyrics-per-page)');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            // Otomatik gönderme için bekle
            setTimeout(() => {
                document.getElementById('lyrics-filter-form').submit();
            }, 500);
        });
    });
});

// Translations dropdown mini - click handler
document.addEventListener('DOMContentLoaded', function() {
    const dropdowns = document.querySelectorAll('.translations-dropdown-mini');

    dropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('.translations-trigger-mini');
        const menu = dropdown.querySelector('.translations-menu-mini');

        if (trigger && menu) {
            // Toggle dropdown on click
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Close other dropdowns
                document.querySelectorAll('.translations-dropdown-mini').forEach(other => {
                    if (other !== dropdown) {
                        other.classList.remove('active');
                        const otherMenu = other.querySelector('.translations-menu-mini');
                        if (otherMenu) {
                            otherMenu.style.opacity = '0';
                            otherMenu.style.visibility = 'hidden';
                            otherMenu.style.transform = 'translateY(-10px)';
                        }
                    }
                });

                // Toggle current dropdown
                dropdown.classList.toggle('active');

                if (dropdown.classList.contains('active')) {
                    menu.style.opacity = '1';
                    menu.style.visibility = 'visible';
                    menu.style.transform = 'translateY(0)';
                } else {
                    menu.style.opacity = '0';
                    menu.style.visibility = 'hidden';
                    menu.style.transform = 'translateY(-10px)';
                }
            });

            // Close on outside click
            document.addEventListener('click', function(e) {
                if (!dropdown.contains(e.target)) {
                    dropdown.classList.remove('active');
                    menu.style.opacity = '0';
                    menu.style.visibility = 'hidden';
                    menu.style.transform = 'translateY(-10px)';
                }
            });
        }
    });
});
</script>

<style>
/* Aktif görünüm butonu */
.view-btn.active {
    background-color: #eff6ff;
    border-color: #2563eb;
    color: #2563eb;
}

/* Liste görünümü için özel stil */
.lyrics-grid[data-view="list"] .lyrics-card {
    display: flex;
    flex-direction: row;
}

.lyrics-grid[data-view="list"] .lyrics-card > a:first-child {
    width: 200px;
    flex-shrink: 0;
}

.lyrics-grid[data-view="list"] .lyrics-card .aspect-square {
    aspect-ratio: 1;
    height: 100%;
}

@media (max-width: 640px) {
    .lyrics-grid[data-view="list"] .lyrics-card {
        flex-direction: column;
    }

    .lyrics-grid[data-view="list"] .lyrics-card > a:first-child {
        width: 100%;
        height: 200px;
    }
}

/* Translations dropdown mini styles */
.lyrics-card {
    position: relative;
    overflow: visible !important;
}

.translations-dropdown-mini {
    position: relative;
    z-index: 10;
}

.translations-dropdown-mini.active {
    z-index: 1001;
}

.translations-menu-mini {
    position: absolute;
    top: 100%;
    left: 0;
    margin-top: 0.5rem;
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e7eb;
    padding: 0.5rem 0;
    min-width: 12rem;
    z-index: 9999 !important;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    pointer-events: none;
}

.translations-dropdown-mini.active .translations-menu-mini {
    opacity: 1 !important;
    visibility: visible !important;
    transform: translateY(0) !important;
    pointer-events: auto;
}

.dropdown-arrow {
    transition: transform 0.3s ease;
}

.translations-dropdown-mini.active .dropdown-arrow {
    transform: rotate(180deg);
}

/* Ensure parent container doesn't clip dropdown */
.lyrics-grid {
    overflow: visible !important;
}

.lyrics-card > div:last-child {
    overflow: visible !important;
}
</style>

<?php
get_footer();