<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Gufte
 */

get_header();

/**
 * İçerikteki çok dilli tablo bilgilerini getir
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
<div class="site-content-wrapper flex flex-col md:flex-row">

    <?php
    // Arcuras Sidebar'ı çağır
    get_template_part('template-parts/arcuras-sidebar');
    ?>

    <?php // Ana İçerik Alanı (Sağ Sütun) ?>
    <main id="primary" class="site-main flex-1 bg-white overflow-x-hidden">

        <?php if (have_posts()) :
            // Arşiv başlığını belirleme
            if (is_category()) {
                $category = get_queried_object();
                $archive_title = $category->name;
                $archive_description = $category->description;
                $icon = 'music';
                $breadcrumb_label = $category->name;
            } elseif (is_tag()) {
                $tag = get_queried_object();
                $archive_title = __('Tag: ', 'gufte') . $tag->name;
                $archive_description = $tag->description;
                $icon = 'tag';
                $breadcrumb_label = __('Tag: ', 'gufte') . $tag->name;
            } elseif (is_author()) {
                $author = get_queried_object();
                $archive_title = __('Author: ', 'gufte') . $author->display_name;
                $archive_description = get_the_author_meta('description', $author->ID);
                $icon = 'account';
                $breadcrumb_label = __('Author: ', 'gufte') . $author->display_name;
            } elseif (is_year()) {
                $archive_title = __('Year: ', 'gufte') . get_the_date(_x('Y', 'yearly archives date format', 'gufte'));
                $archive_description = '';
                $icon = 'calendar';
                $breadcrumb_label = get_the_date('Y');
            } elseif (is_month()) {
                $archive_title = get_the_date(_x('F Y', 'monthly archives date format', 'gufte'));
                $archive_description = '';
                $icon = 'calendar';
                $breadcrumb_label = get_the_date('F Y');
            } elseif (is_day()) {
                $archive_title = get_the_date(_x('F j, Y', 'daily archives date format', 'gufte'));
                $archive_description = '';
                $icon = 'calendar';
                $breadcrumb_label = get_the_date('F j, Y');
            } else {
                $archive_title = __('All Lyrics', 'gufte');
                $archive_description = '';
                $icon = 'music';
                $breadcrumb_label = __('Lyrics', 'gufte');
            }

            // Breadcrumb
            set_query_var('breadcrumb_items', array(
                array('label' => 'Home', 'url' => home_url('/')),
                array('label' => $breadcrumb_label)
            ));
            get_template_part('template-parts/page-components/page-breadcrumb');

            // Hero
            set_query_var('hero_title', $archive_title);
            set_query_var('hero_icon', $icon);
            set_query_var('hero_description', $archive_description);
            set_query_var('hero_meta', array());
            get_template_part('template-parts/page-components/page-hero');
        ?>

            <?php
            // Filters - Get URL parameters
            $q = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
            $sort = isset($_GET['sort']) ? sanitize_text_field(wp_unslash($_GET['sort'])) : 'date_desc';
            $per_page = isset($_GET['per_page']) ? max(6, min(60, intval($_GET['per_page']))) : 17;
            $letter = isset($_GET['letter']) ? strtoupper(sanitize_text_field(wp_unslash($_GET['letter']))) : '';

            global $wp_query;
            $found_posts = $wp_query->found_posts;

            // Build results text including letter filter
            $results_text = sprintf(_n('%s result', '%s results', $found_posts, 'gufte'), number_format_i18n($found_posts));
            if ($letter) {
                $results_text .= ' — ' . sprintf(__('letter: %s', 'gufte'), $letter);
            }

            set_query_var('filter_config', array(
                'search' => array(
                    'enabled' => true,
                    'name' => 'q',
                    'value' => $q,
                    'label' => __('Search lyrics', 'gufte'),
                    'placeholder' => __('Type song name…', 'gufte'),
                    'id' => 'lyrics-search'
                ),
                'sort' => array(
                    'enabled' => true,
                    'name' => 'sort',
                    'value' => $sort,
                    'label' => __('Sort by', 'gufte'),
                    'id' => 'lyrics-sort',
                    'options' => array(
                        'date_desc' => __('Latest', 'gufte'),
                        'date_asc' => __('Oldest', 'gufte'),
                        'title_asc' => __('Title (A→Z)', 'gufte'),
                        'title_desc' => __('Title (Z→A)', 'gufte'),
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
                'action_url' => get_pagenum_link(1)
            ));
            get_template_part('template-parts/page-components/page-filters');

            // Letter Filter - sadece lyrics archive için
            if (!is_category() && !is_tag() && !is_author() && !is_date()) {
                $letters = array_merge(range('A', 'Z'), array('Ç', 'Ğ', 'İ', 'Ö', 'Ş', 'Ü', '#'));

                set_query_var('letter_filter_config', array(
                    'current_letter' => $letter,
                    'base_url' => get_pagenum_link(1),
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
            }
            ?>

            <?php // Ana İçerik Grid ?>
            <div class="archive-posts-container px-4 sm:px-6 lg:px-8 py-6">
                <div class="archive-posts-grid grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">

                <?php
                /* Start the Loop */
                while (have_posts()) :
                    the_post();

                    // Şarkıcı bilgisini al
                    $singers = get_the_terms(get_the_ID(), 'singer');
                    $singer = !empty($singers) && !is_wp_error($singers) ? reset($singers) : null;

                    // Şarkı sözü dil bilgilerini al
                    $raw_content = get_the_content();
                    $lyrics_languages = array('original' => '', 'translations' => array());
                    if (function_exists('gufte_get_lyrics_languages')) {
                        $lyrics_languages = gufte_get_lyrics_languages($raw_content);
                    }
                    $translation_count = !empty($lyrics_languages['translations']) ? count($lyrics_languages['translations']) : 0;
                ?>

                <article id="post-<?php the_ID(); ?>" <?php post_class('compact-card group bg-white rounded-lg shadow-md border border-gray-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-gray-300'); ?> style="overflow: visible;">
                    <a href="<?php the_permalink(); ?>" class="block relative aspect-square overflow-hidden bg-gray-100">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('medium', array('class' => 'w-full h-full object-cover group-hover:scale-110 transition-transform duration-500')); ?>
                        <?php else : ?>
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                                <span class="iconify text-5xl text-gray-400 group-hover:text-primary-500 transition-colors duration-300" data-icon="mdi:music-note"></span>
                            </div>
                        <?php endif; ?>

                        <?php if ($translation_count > 0) : ?>
                        <div class="absolute top-2 right-2 bg-primary-500/90 backdrop-blur-sm text-white text-xs font-bold px-1.5 py-0.5 rounded-full flex items-center">
                            <span class="iconify mr-0.5 text-xs" data-icon="mdi:translate"></span>
                            <?php echo esc_html($translation_count); ?>
                        </div>
                        <?php endif; ?>

                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <div class="absolute bottom-0 left-0 right-0 p-3 text-white">
                                <p class="text-xs font-medium line-clamp-2"><?php the_title(); ?></p>
                                <?php if ($singer) : ?>
                                <p class="text-xs mt-1 opacity-90"><?php echo esc_html($singer->name); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>

                    <div class="p-3">
                        <h2 class="entry-title text-sm font-semibold mb-1.5 line-clamp-2 min-h-[2.5rem]">
                            <a href="<?php the_permalink(); ?>" class="text-gray-800 hover:text-primary-600 transition-colors duration-300">
                                <?php the_title(); ?>
                            </a>
                        </h2>

                        <?php if ($singer) : ?>
                        <div class="text-xs text-gray-500 truncate mb-2">
                            <a href="<?php echo esc_url(get_term_link($singer)); ?>" class="hover:text-primary-600 transition-colors duration-300">
                                <?php echo esc_html($singer->name); ?>
                            </a>
                        </div>
                        <?php endif; ?>

                        <?php
                        // Get original language
                        $original_languages = wp_get_post_terms(get_the_ID(), 'original_language');
                        if ($original_languages && !is_wp_error($original_languages) && !empty($original_languages)) :
                            $original_lang = $original_languages[0];
                            $original_lang_info = arcuras_get_language_info_by_slug($original_lang->slug);
                            $original_flag = $original_lang_info ? $original_lang_info['flag'] : '🌐';
                        ?>
                            <div class="text-xs text-gray-500 mb-2">
                                <a href="<?php echo esc_url(get_term_link($original_lang)); ?>" class="hover:text-primary-600 transition-colors duration-300 inline-flex items-center gap-1">
                                    <?php gufte_icon('file-document', 'w-3 h-3'); ?>
                                    <span><?php echo esc_html($original_lang->name); ?></span>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php
                        // Get translations
                        $translated_languages = wp_get_post_terms(get_the_ID(), 'translated_language');
                        if ($translated_languages && !is_wp_error($translated_languages) && !empty($translated_languages)) :
                            $total_translations = count($translated_languages);
                        ?>
                            <div style="margin-top: 4px;">
                                <div class="dropdown-wrapper" style="position: relative; display: inline-block;" onmouseenter="const dropdown = this.querySelector('.dropdown-menu'); const card = this.closest('article'); document.querySelectorAll('article').forEach(c => c.style.zIndex = '1'); document.querySelectorAll('.dropdown-menu').forEach(d => d.classList.add('hidden')); dropdown.classList.remove('hidden'); card.style.zIndex = '1001';" onmouseleave="setTimeout(() => { const dropdown = this.querySelector('.dropdown-menu'); const card = this.closest('article'); if (dropdown && !dropdown.matches(':hover')) { dropdown.classList.add('hidden'); card.style.zIndex = '1'; } }, 100);">
                                    <button type="button" class="dropdown-toggle" onclick="event.preventDefault(); event.stopPropagation();" style="font-size: 10px; padding: 3px 6px; background: #f3f4f6; color: #374151; border-radius: 4px; border: none; cursor: pointer; font-weight: 500; transition: all 0.2s; display: flex; align-items: center; gap: 3px;" onmouseover="this.style.background='#e5e7eb';" onmouseout="this.style.background='#f3f4f6';">
                                        <span>+<?php echo $total_translations; ?> <?php echo $total_translations === 1 ? 'Translation' : 'Translations'; ?></span>
                                        <svg style="width: 10px; height: 10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    <div class="dropdown-menu hidden" style="position: absolute; top: calc(100% + 4px); left: 0; background: white; border: 1px solid #e5e7eb; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); padding: 6px; min-width: 120px; z-index: 1002; max-height: 180px; overflow-y: auto;" onmouseleave="this.classList.add('hidden'); this.closest('article').style.zIndex = '1';">
                                        <?php foreach ($translated_languages as $lang) :
                                            $trans_lang_info = arcuras_get_language_info_by_slug($lang->slug);
                                            $trans_flag = $trans_lang_info ? $trans_lang_info['flag'] : '🌐';
                                            $lang_code = $trans_lang_info ? $trans_lang_info['iso_code'] : $lang->slug;
                                            $base_url = rtrim(get_the_permalink(get_the_ID()), '/');
                                            $translation_url = $base_url . '/' . $lang_code . '/';
                                            ?>
                                            <a href="<?php echo esc_url($translation_url); ?>" onclick="event.stopPropagation();" style="display: flex; align-items: center; gap: 4px; font-size: 11px; padding: 4px 6px; color: #374151; text-decoration: none; font-weight: 500; border-radius: 4px; transition: all 0.2s; margin-bottom: 2px; white-space: nowrap;" onmouseover="this.style.background='#f3f4f6';" onmouseout="this.style.background='transparent';">
                                                <?php gufte_icon('translate', 'w-3 h-3'); ?>
                                                <?php echo esc_html($lang->name); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>

                <?php endwhile; ?>

                </div><!-- .archive-posts-grid -->
            </div><!-- .archive-posts-container -->

            <?php
            // Pagination
            global $wp_query;
            if ($wp_query->max_num_pages > 1) {
                set_query_var('pagination_config', array(
                    'total_pages' => $wp_query->max_num_pages,
                    'current_page' => max(1, get_query_var('paged')),
                    'base_url' => get_pagenum_link(1),
                    'preserve_params' => array(
                        'q' => $q,
                        'sort' => $sort,
                        'per_page' => $per_page,
                        'letter' => $letter
                    ),
                    'mid_size' => 2,
                    'prev_text' => __('Previous', 'gufte'),
                    'next_text' => __('Next', 'gufte'),
                ));
                get_template_part('template-parts/page-components/page-pagination');
            }
            ?>

        <?php else : ?>

            <?php // İçerik bulunamadı ?>
            <div class="no-results bg-white rounded-lg shadow-md p-8 md:p-12 border border-gray-200 text-center">
                <div class="no-results-icon mb-6">
                    <span class="iconify text-6xl text-gray-300" data-icon="mdi:music-note-off"></span>
                </div>
                
                <h1 class="page-title text-2xl md:text-3xl font-bold text-gray-800 mb-4">
                    <?php esc_html_e('Nothing Found', 'gufte'); ?>
                </h1>
                
                <div class="page-content text-gray-600 leading-relaxed max-w-2xl mx-auto">
                    <p class="mb-4">
                        <?php esc_html_e('It seems we can\'t find what you\'re looking for. Perhaps searching can help.', 'gufte'); ?>
                    </p>
                    
                    <?php get_search_form(); ?>
                    
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4"><?php esc_html_e('Suggestions:', 'gufte'); ?></h3>
                        <ul class="text-left list-disc list-inside space-y-2 text-sm">
                            <li><?php esc_html_e('Check if the spelling is correct', 'gufte'); ?></li>
                            <li><?php esc_html_e('Try different keywords', 'gufte'); ?></li>
                            <li><?php esc_html_e('Browse our categories', 'gufte'); ?></li>
                            <li><?php esc_html_e('Visit the homepage', 'gufte'); ?></li>
                        </ul>
                    </div>
                    
                    <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="inline-flex items-center justify-center bg-primary-600 hover:bg-primary-700 text-white font-medium px-6 py-3 rounded-lg transition-all duration-300 hover:translate-y-[-2px] shadow-lg">
                            <span class="iconify mr-2" data-icon="mdi:home"></span>
                            <?php esc_html_e('Go Home', 'gufte'); ?>
                        </a>
                        
                        <a href="<?php echo esc_url(home_url('/singer/')); ?>" class="inline-flex items-center justify-center bg-gray-600 hover:bg-gray-700 text-white font-medium px-6 py-3 rounded-lg transition-all duration-300 hover:translate-y-[-2px] shadow-lg">
                            <span class="iconify mr-2" data-icon="mdi:microphone-variant"></span>
                            <?php esc_html_e('Browse Singers', 'gufte'); ?>
                        </a>
                    </div>
                </div>
            </div>

        <?php endif; ?>

    </main>

</div>

<?php // CSS for archive styling ?>
<style>
.archive-posts-grid .compact-card {
    break-inside: avoid;
}

.archive-pagination .nav-links {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.archive-pagination .nav-links .page-numbers {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2.5rem;
    height: 2.5rem;
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    background-color: #ffffff;
    color: #374151;
    text-decoration: none;
    border-radius: 0.375rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.archive-pagination .nav-links .page-numbers:hover {
    background-color: #f3f4f6;
    border-color: #9ca3af;
}

.archive-pagination .nav-links .page-numbers.current {
    background-color: #2563eb;
    border-color: #2563eb;
    color: #ffffff;
}

.archive-pagination .nav-links .page-numbers.prev,
.archive-pagination .nav-links .page-numbers.next {
    padding: 0.5rem 1rem;
    min-width: auto;
}

.archive-pagination .nav-links .page-numbers.dots {
    border: none;
    background: none;
    color: #9ca3af;
    cursor: default;
}

.archive-pagination .nav-links .page-numbers.dots:hover {
    background: none;
    border: none;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .archive-header {
        padding: 1rem;
    }
    
    .archive-header h1 {
        font-size: 1.5rem;
        flex-direction: column;
        text-align: center;
    }
    
    .archive-header h1 .iconify {
        margin-right: 0;
        margin-bottom: 0.5rem;
    }
    
    .archive-filters {
        padding: 1rem;
    }
    
    .archive-posts-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }
}

/* Search form styling in no-results */
.no-results .search-form {
    max-width: 400px;
    margin: 0 auto;
}

.no-results .search-form .search-field {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 1rem;
    margin-bottom: 1rem;
}

.no-results .search-form .search-submit {
    width: 100%;
    padding: 0.75rem 1.5rem;
    background-color: #2563eb;
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.no-results .search-form .search-submit:hover {
    background-color: #1d4ed8;
}

/* Line clamp utility */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<script>
// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.dropdown-wrapper')) {
        document.querySelectorAll('.dropdown-menu').forEach(function(dropdown) {
            dropdown.classList.add('hidden');
        });
        document.querySelectorAll('article').forEach(function(card) {
            card.style.zIndex = '1';
        });
    }
});
</script>

<?php
get_footer();
?>