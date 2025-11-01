<?php
/**
 * The template for displaying search results pages.
 * Updated with sidebar integration, English localization,
 * and taxonomy results for Singers, Albums, Producers, Songwriters.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package Gufte
 */

get_header();

// Get the search query
$search_query = get_search_query();

// Helper: safe get_terms() that returns [] on error
function gufte_safe_get_terms($args){
    $terms = get_terms($args);
    if (is_wp_error($terms)) return array();
    return $terms;
}

// -----------------------
// Taxonomy: Singer
// -----------------------
$singer_results = array();
$singer_count   = 0;

if (taxonomy_exists('singer') && $search_query !== '') {
    $singer_results = gufte_safe_get_terms(array(
        'taxonomy'   => 'singer',
        'name__like' => $search_query,
        'hide_empty' => true,
        'number'     => 10,
    ));
    $count_tmp = get_terms(array(
        'taxonomy'   => 'singer',
        'name__like' => $search_query,
        'hide_empty' => true,
        'fields'     => 'count',
    ));
    $singer_count = (is_wp_error($count_tmp) || !is_numeric($count_tmp)) ? 0 : (int)$count_tmp;
}

// -----------------------
// Taxonomy: Album
// -----------------------
$album_results = array();
$album_count   = 0;

if (taxonomy_exists('album') && $search_query !== '') {
    $album_results = gufte_safe_get_terms(array(
        'taxonomy'   => 'album',
        'name__like' => $search_query,
        'hide_empty' => true,
        'number'     => 10,
    ));
    $count_tmp = get_terms(array(
        'taxonomy'   => 'album',
        'name__like' => $search_query,
        'hide_empty' => true,
        'fields'     => 'count',
    ));
    $album_count = (is_wp_error($count_tmp) || !is_numeric($count_tmp)) ? 0 : (int)$count_tmp;
}

// -----------------------
// Taxonomy: Producer
// -----------------------
$producer_results = array();
$producer_count   = 0;

if (taxonomy_exists('producer') && $search_query !== '') {
    $producer_results = gufte_safe_get_terms(array(
        'taxonomy'   => 'producer',
        'name__like' => $search_query,
        'hide_empty' => true,
        'number'     => 10,
    ));
    $count_tmp = get_terms(array(
        'taxonomy'   => 'producer',
        'name__like' => $search_query,
        'hide_empty' => true,
        'fields'     => 'count',
    ));
    $producer_count = (is_wp_error($count_tmp) || !is_numeric($count_tmp)) ? 0 : (int)$count_tmp;
}

// -----------------------
// Taxonomy: Songwriter
// -----------------------
$songwriter_results = array();
$songwriter_count   = 0;

if (taxonomy_exists('songwriter') && $search_query !== '') {
    $songwriter_results = gufte_safe_get_terms(array(
        'taxonomy'   => 'songwriter',
        'name__like' => $search_query,
        'hide_empty' => true,
        'number'     => 10,
    ));
    $count_tmp = get_terms(array(
        'taxonomy'   => 'songwriter',
        'name__like' => $search_query,
        'hide_empty' => true,
        'fields'     => 'count',
    ));
    $songwriter_count = (is_wp_error($count_tmp) || !is_numeric($count_tmp)) ? 0 : (int)$count_tmp;
}

// Get the count of posts found by the main query (lyrics/posts)
$post_count = $wp_query->found_posts;

// Calculate total results (posts + each taxonomy count)
$total_results = $post_count + $singer_count + $album_count + $producer_count + $songwriter_count;

?>

<?php // Wrapper (Sidebar + Main) ?>
<div class="site-content-wrapper flex flex-col md:flex-row">

    <?php get_template_part('template-parts/arcuras-sidebar'); ?>

    <main id="primary" class="site-main flex-1 pt-8 pb-12 px-4 sm:px-6 lg:px-8 overflow-x-hidden">

        <header class="page-header mb-8">
            <h1 class="page-title text-3xl font-bold text-gray-800 mb-4">
                <?php
                // Check if filtering by genre
                if (isset($_GET['genre']) && !empty($_GET['genre'])) {
                    $genre_slug = sanitize_text_field($_GET['genre']);

                    // Map for display names
                    $genre_name_map = array(
                        'pop' => 'Pop',
                        'alternative' => 'Alternative',
                        'hip-hop-rap' => 'Hip-Hop/Rap',
                        'rock' => 'Rock',
                        'rb-soul' => 'R&B/Soul',
                        'urbano-latino' => 'Urbano Latino',
                        'electronic' => 'Electronic',
                        'country' => 'Country',
                        'k-pop' => 'K-Pop',
                        'soundtrack' => 'Soundtrack',
                    );

                    $genre_display = isset($genre_name_map[$genre_slug])
                        ? $genre_name_map[$genre_slug]
                        : ucwords(str_replace('-', ' ', $genre_slug));

                    echo '<span class="text-primary-600">' . esc_html($genre_display) . '</span> ' . esc_html__('Lyrics', 'gufte');
                } else {
                    printf(
                        esc_html__( 'Search Results for: %s', 'gufte' ),
                        '<span class="text-accent-600">' . esc_html( $search_query ) . '</span>'
                    );
                }
                ?>
            </h1>

            <div class="search-form-container mb-6">
                <form role="search" method="get" class="relative max-w-lg" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <label for="search-page-field" class="sr-only"><?php esc_html_e('Search again', 'gufte'); ?></label>
                    <input
                        type="search"
                        id="search-page-field"
                        class="bg-white border border-gray-300 rounded-lg py-2.5 px-4 pr-12 text-base w-full focus:outline-none focus:ring-2 focus:ring-accent-500 focus:border-transparent transition-all duration-300"
                        placeholder="<?php echo esc_attr_x( 'Search for something else...', 'search placeholder', 'gufte' ); ?>"
                        value="<?php echo esc_attr( $search_query ); ?>"
                        name="s"
                    />
                    <button type="submit" class="absolute right-0 top-0 bottom-0 my-auto mr-4 text-gray-500 hover:text-accent-600 transition-colors duration-300">
                        <span class="iconify w-5 h-5" data-icon="mdi:magnify"></span>
                    </button>
                </form>
            </div>

            <?php if ( $total_results > 0 ) : ?>
            <div class="search-results-count text-gray-500 text-sm">
                <?php
                printf(
                    esc_html( _n( '%s result found.', '%s results found.', $total_results, 'gufte' ) ),
                    number_format_i18n( $total_results )
                );
                ?>
            </div>
            <?php endif; ?>
        </header>

        <?php if ( $post_count > 0 || $singer_count > 0 || $album_count > 0 || $producer_count > 0 || $songwriter_count > 0 ) : ?>

            <?php // --------- Singers --------- ?>
            <?php if ( ! empty( $singer_results ) ) : ?>
            <section class="singer-results-section mb-10">
                <h2 class="section-title text-xl font-bold text-gray-800 mb-4 pb-2 border-b border-gray-200 flex items-center">
                    <span class="iconify mr-2 text-primary-600 text-2xl" data-icon="mdi:microphone-variant"></span>
                    <?php esc_html_e('Singers', 'gufte'); ?>
                    <span class="text-sm font-normal text-gray-500 ml-2">(<?php echo number_format_i18n($singer_count); ?>)</span>
                </h2>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($singer_results as $singer) :
                        $singer_image_url = '';
                        $singer_image_id  = get_term_meta($singer->term_id, 'singer_image_id', true);
                        if ($singer_image_id) {
                            $singer_image_url = wp_get_attachment_image_url($singer_image_id, 'thumbnail');
                        }
                    ?>
                    <a href="<?php echo esc_url(get_term_link($singer)); ?>" class="singer-card flex items-center bg-white rounded-lg overflow-hidden border border-gray-200 transition-all duration-300 hover:shadow-lg hover:border-primary-300 group p-3">
                        <?php if (!empty($singer_image_url)) : ?>
                            <img src="<?php echo esc_url($singer_image_url); ?>" alt="<?php echo esc_attr($singer->name); ?>" class="w-12 h-12 object-cover rounded-full mr-3 flex-shrink-0 shadow-sm border border-gray-100">
                        <?php else : ?>
                            <div class="w-12 h-12 bg-gray-100 rounded-full mr-3 flex items-center justify-center flex-shrink-0">
                                <span class="iconify text-2xl text-gray-400 group-hover:text-primary-500 transition-colors" data-icon="mdi:account-music-outline"></span>
                            </div>
                        <?php endif; ?>
                        <div class="flex-grow truncate">
                            <h3 class="text-base font-bold text-gray-800 group-hover:text-primary-600 transition-colors duration-200 truncate">
                                <?php echo esc_html($singer->name); ?>
                            </h3>
                            <?php if ($singer->count > 0): ?>
                                <p class="text-xs text-gray-500">
                                    <?php printf(esc_html(_n('%s song', '%s songs', $singer->count, 'gufte')), number_format_i18n($singer->count)); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <?php // --------- Albums --------- ?>
            <?php if ( ! empty( $album_results ) ) : ?>
            <section class="album-results-section mb-10">
                <h2 class="section-title text-xl font-bold text-gray-800 mb-4 pb-2 border-b border-gray-200 flex items-center">
                    <span class="iconify mr-2 text-primary-600 text-2xl" data-icon="mdi:album"></span>
                    <?php esc_html_e('Albums', 'gufte'); ?>
                    <span class="text-sm font-normal text-gray-500 ml-2">(<?php echo number_format_i18n($album_count); ?>)</span>
                </h2>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($album_results as $album) :
                        $cover_url   = '';
                        $cover_id    = get_term_meta($album->term_id, 'album_cover_id', true);
                        $album_year  = get_term_meta($album->term_id, 'album_year', true);
                        if ($cover_id) {
                            $cover_url = wp_get_attachment_image_url($cover_id, 'thumbnail');
                        }
                    ?>
                    <a href="<?php echo esc_url(get_term_link($album)); ?>" class="album-card flex items-center bg-white rounded-lg overflow-hidden border border-gray-200 transition-all duration-300 hover:shadow-lg hover:border-primary-300 group p-3">
                        <?php if (!empty($cover_url)) : ?>
                            <img src="<?php echo esc_url($cover_url); ?>" alt="<?php echo esc_attr($album->name); ?>" class="w-12 h-12 object-cover rounded mr-3 flex-shrink-0 shadow-sm border border-gray-100">
                        <?php else : ?>
                            <div class="w-12 h-12 bg-gray-100 rounded mr-3 flex items-center justify-center flex-shrink-0">
                                <span class="iconify text-2xl text-gray-400 group-hover:text-primary-500 transition-colors" data-icon="mdi:album"></span>
                            </div>
                        <?php endif; ?>
                        <div class="flex-grow truncate">
                            <h3 class="text-base font-bold text-gray-800 group-hover:text-primary-600 transition-colors duration-200 truncate">
                                <?php echo esc_html($album->name); ?>
                                <?php if (!empty($album_year)) : ?>
                                    <span class="text-xs font-normal text-gray-500 ml-1">(<?php echo esc_html($album_year); ?>)</span>
                                <?php endif; ?>
                            </h3>
                            <?php if ($album->count > 0): ?>
                                <p class="text-xs text-gray-500">
                                    <?php printf(esc_html(_n('%s track', '%s tracks', $album->count, 'gufte')), number_format_i18n($album->count)); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <?php // --------- Producers --------- ?>
            <?php if ( ! empty( $producer_results ) ) : ?>
            <section class="producer-results-section mb-10">
                <h2 class="section-title text-xl font-bold text-gray-800 mb-4 pb-2 border-b border-gray-200 flex items-center">
                    <span class="iconify mr-2 text-primary-600 text-2xl" data-icon="mdi:console-line"></span>
                    <?php esc_html_e('Producers', 'gufte'); ?>
                    <span class="text-sm font-normal text-gray-500 ml-2">(<?php echo number_format_i18n($producer_count); ?>)</span>
                </h2>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($producer_results as $producer) :
                        $img_url = '';
                        $img_id  = get_term_meta($producer->term_id, 'profile_image_id', true);
                        if ($img_id) {
                            $img_url = wp_get_attachment_image_url($img_id, 'thumbnail');
                        }
                    ?>
                    <a href="<?php echo esc_url(get_term_link($producer)); ?>" class="producer-card flex items-center bg-white rounded-lg overflow-hidden border border-gray-200 transition-all duration-300 hover:shadow-lg hover:border-primary-300 group p-3">
                        <?php if (!empty($img_url)) : ?>
                            <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($producer->name); ?>" class="w-12 h-12 object-cover rounded-full mr-3 flex-shrink-0 shadow-sm border border-gray-100">
                        <?php else : ?>
                            <div class="w-12 h-12 bg-gray-100 rounded-full mr-3 flex items-center justify-center flex-shrink-0">
                                <span class="iconify text-2xl text-gray-400 group-hover:text-primary-500 transition-colors" data-icon="mdi:console-line"></span>
                            </div>
                        <?php endif; ?>
                        <div class="flex-grow truncate">
                            <h3 class="text-base font-bold text-gray-800 group-hover:text-primary-600 transition-colors duration-200 truncate">
                                <?php echo esc_html($producer->name); ?>
                            </h3>
                            <?php if ($producer->count > 0): ?>
                                <p class="text-xs text-gray-500">
                                    <?php printf(esc_html(_n('%s production', '%s productions', $producer->count, 'gufte')), number_format_i18n($producer->count)); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <?php // --------- Songwriters --------- ?>
            <?php if ( ! empty( $songwriter_results ) ) : ?>
            <section class="songwriter-results-section mb-10">
                <h2 class="section-title text-xl font-bold text-gray-800 mb-4 pb-2 border-b border-gray-200 flex items-center">
                    <span class="iconify mr-2 text-primary-600 text-2xl" data-icon="mdi:pen"></span>
                    <?php esc_html_e('Songwriters', 'gufte'); ?>
                    <span class="text-sm font-normal text-gray-500 ml-2">(<?php echo number_format_i18n($songwriter_count); ?>)</span>
                </h2>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($songwriter_results as $songwriter) :
                        $img_url = '';
                        $img_id  = get_term_meta($songwriter->term_id, 'profile_image_id', true);
                        if ($img_id) {
                            $img_url = wp_get_attachment_image_url($img_id, 'thumbnail');
                        }
                    ?>
                    <a href="<?php echo esc_url(get_term_link($songwriter)); ?>" class="songwriter-card flex items-center bg-white rounded-lg overflow-hidden border border-gray-200 transition-all duration-300 hover:shadow-lg hover:border-primary-300 group p-3">
                        <?php if (!empty($img_url)) : ?>
                            <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($songwriter->name); ?>" class="w-12 h-12 object-cover rounded-full mr-3 flex-shrink-0 shadow-sm border border-gray-100">
                        <?php else : ?>
                            <div class="w-12 h-12 bg-gray-100 rounded-full mr-3 flex items-center justify-center flex-shrink-0">
                                <span class="iconify text-2xl text-gray-400 group-hover:text-primary-500 transition-colors" data-icon="mdi:pen"></span>
                            </div>
                        <?php endif; ?>
                        <div class="flex-grow truncate">
                            <h3 class="text-base font-bold text-gray-800 group-hover:text-primary-600 transition-colors duration-200 truncate">
                                <?php echo esc_html($songwriter->name); ?>
                            </h3>
                            <?php if ($songwriter->count > 0): ?>
                                <p class="text-xs text-gray-500">
                                    <?php printf(esc_html(_n('%s song written', '%s songs written', $songwriter->count, 'gufte')), number_format_i18n($songwriter->count)); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <?php // --------- Posts (Lyrics) --------- ?>
            <?php if ( have_posts() ) : ?>
            <section class="lyrics-results-section">
                <h2 class="section-title text-xl font-bold text-gray-800 mb-4 pb-2 border-b border-gray-200 flex items-center">
                    <span class="iconify mr-2 text-primary-600 text-2xl" data-icon="mdi:text-long"></span>
                    <?php esc_html_e('Lyrics', 'gufte'); ?>
                    <span class="text-sm font-normal text-gray-500 ml-2">(<?php echo number_format_i18n($post_count); ?>)</span>
                </h2>

                <div class="content-results grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                    <?php while ( have_posts() ) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('post-card bg-white rounded-lg shadow-md border border-gray-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-primary-300 flex flex-col'); ?> style="overflow: visible;">
                        <?php if (has_post_thumbnail()) : ?>
                            <a href="<?php the_permalink(); ?>" class="block relative group aspect-square overflow-hidden">
                                <?php the_post_thumbnail('medium', array('class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300')); ?>
                            </a>
                        <?php else: ?>
                            <a href="<?php the_permalink(); ?>" class="block relative group aspect-square bg-gray-100 flex items-center justify-center">
                                <span class="iconify text-4xl text-gray-300 group-hover:text-primary-400 transition-colors duration-300" data-icon="mdi:music-note"></span>
                            </a>
                        <?php endif; ?>
                        <div class="p-4 flex-grow flex flex-col">
                            <h3 class="text-base font-bold mb-2">
                                <a href="<?php the_permalink(); ?>" class="text-gray-800 hover:text-primary-600 transition-colors duration-300 line-clamp-2">
                                    <?php the_title(); ?>
                                </a>
                            </h3>

                            <?php // Singer Info (first one) ?>
                            <?php if (taxonomy_exists('singer')) :
                                $singers = get_the_terms(get_the_ID(), 'singer');
                                if ($singers && !is_wp_error($singers)):
                                    $singer = reset($singers); ?>
                                    <div class="text-xs text-gray-500 mb-2 flex items-center">
                                        <span class="iconify mr-1" data-icon="mdi:microphone-variant"></span>
                                        <a href="<?php echo esc_url(get_term_link($singer)); ?>" class="hover:text-primary-500">
                                            <?php echo esc_html($singer->name); ?>
                                        </a>
                                    </div>
                            <?php endif; endif; ?>

                            <?php
                            // Get original language
                            $original_languages = wp_get_post_terms(get_the_ID(), 'original_language');
                            if ($original_languages && !is_wp_error($original_languages) && !empty($original_languages)) :
                                $original_lang = $original_languages[0];
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
                                <div style="margin-top: 4px; margin-bottom: 8px;">
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

                            <div class="mt-auto pt-3 border-t border-gray-100 flex justify-between items-center text-xs text-gray-500">
                                <?php
                                // Count lines in lyrics
                                $post_content = get_the_content();
                                $line_count = 0;
                                if (has_blocks($post_content)) {
                                    $blocks = parse_blocks($post_content);
                                    foreach ($blocks as $block) {
                                        if ($block['blockName'] === 'arcuras/lyrics-translations' && !empty($block['attrs']['languages'])) {
                                            foreach ($block['attrs']['languages'] as $lang) {
                                                if (isset($lang['isOriginal']) && $lang['isOriginal'] && !empty($lang['lyrics'])) {
                                                    $lines = array_filter(explode("\n", $lang['lyrics']), function($line) {
                                                        return trim($line) !== '';
                                                    });
                                                    $line_count = count($lines);
                                                    break 2;
                                                }
                                            }
                                        }
                                    }
                                }
                                ?>
                                <span class="line-count flex items-center">
                                    <span class="iconify mr-1" data-icon="mdi:format-list-numbered"></span>
                                    <?php echo esc_html($line_count); ?> <?php esc_html_e('lines', 'gufte'); ?>
                                </span>
                                <a href="<?php the_permalink(); ?>" class="read-more-button bg-primary-50 hover:bg-primary-100 text-primary-600 px-2.5 py-1 rounded-full inline-flex items-center transition-all duration-300 font-medium text-xs">
                                    <?php esc_html_e('View', 'gufte'); ?>
                                    <span class="iconify ml-1" data-icon="mdi:arrow-right"></span>
                                </a>
                            </div>
                        </div>
                    </article>
                    <?php endwhile; ?>
                </div>

                <div class="pagination-container mt-8">
                    <?php
                    the_posts_pagination( array(
                        'mid_size'           => 2,
                        'prev_text'          => '<span class="iconify mr-1" data-icon="mdi:chevron-left"></span><span class="sr-only">' . __( 'Previous', 'gufte' ) . '</span>',
                        'next_text'          => '<span class="sr-only">' . __( 'Next', 'gufte' ) . '</span><span class="iconify ml-1" data-icon="mdi:chevron-right"></span>',
                        'screen_reader_text' => __( 'Search results navigation', 'gufte' ),
                    ) );
                    ?>
                </div>

            </section>
            <?php endif; ?>

        <?php else : // No results at all ?>

            <div class="no-results-section bg-white p-6 md:p-8 rounded-lg border border-gray-200 shadow-sm text-center">
                <div class="text-6xl text-gray-300 mb-4">
                    <span class="iconify" data-icon="mdi:magnify-remove-outline"></span>
                </div>
                <h2 class="text-xl font-semibold text-gray-800 mb-3"><?php esc_html_e( 'Nothing Found', 'gufte' ); ?></h2>
                <p class="mb-6 text-gray-600"><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'gufte' ); ?></p>

                <div class="search-suggestions mt-6 text-left max-w-md mx-auto">
                    <h3 class="text-base font-medium text-gray-700 mb-2"><?php esc_html_e( 'Suggestions:', 'gufte' ); ?></h3>
                    <ul class="list-disc list-inside text-gray-600 space-y-1 text-sm">
                        <li><?php esc_html_e( 'Make sure that all words are spelled correctly.', 'gufte' ); ?></li>
                        <li><?php esc_html_e( 'Try different keywords.', 'gufte' ); ?></li>
                        <li><?php esc_html_e( 'Try more general terms.', 'gufte' ); ?></li>
                    </ul>
                </div>

                <div class="mt-8">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-flex items-center px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium shadow hover:shadow-md transition-all duration-300">
                        <span class="iconify mr-2" data-icon="mdi:home-outline"></span>
                        <?php esc_html_e( 'Return to Homepage', 'gufte' ); ?>
                    </a>
                </div>
            </div>

        <?php endif; ?>

    </main>
</div>

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

<?php get_footer();
