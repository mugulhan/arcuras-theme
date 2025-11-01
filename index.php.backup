<?php
/**
 * The main template file - Optimized Card Layout
 *
 * @package Gufte
 */

get_header();

// Çok dilli şarkı sözleri fonksiyonu - Gutenberg block desteği ile
if ( ! function_exists( 'gufte_get_lyrics_languages' ) ) {
    function gufte_get_lyrics_languages($content) {
        $languages = array();
        $original_language = '';

        // Önce yeni Gutenberg block formatını kontrol et
        if (has_block('arcuras/lyrics-translations', $content)) {
            // Use WordPress's parse_blocks function
            $blocks = parse_blocks($content);

            foreach ($blocks as $block) {
                if ($block['blockName'] === 'arcuras/lyrics-translations') {
                    $attrs = isset($block['attrs']) ? $block['attrs'] : array();

                    if (!empty($attrs['languages']) && is_array($attrs['languages'])) {
                        // Iterate through languages to find original and translations
                        foreach ($attrs['languages'] as $lang) {
                            if (isset($lang['name']) && $lang['name'] !== '') {
                                // Check if this is the original language (handle both boolean and string values)
                                $is_original = isset($lang['isOriginal']) && ($lang['isOriginal'] === true || $lang['isOriginal'] === 'true' || $lang['isOriginal'] === 1);

                                // TEMP DEBUG
                                error_log('Lang: ' . $lang['name'] . ', isOriginal isset: ' . (isset($lang['isOriginal']) ? 'yes' : 'no') . ', value: ' . var_export($lang['isOriginal'] ?? 'NOT SET', true) . ', is_original result: ' . ($is_original ? 'TRUE' : 'FALSE'));

                                if ($is_original) {
                                    $original_language = $lang['name'];
                                } else {
                                    // This is a translation (or no isOriginal flag = treat as translation)
                                    if (!in_array($lang['name'], $languages)) {
                                        $languages[] = $lang['name'];
                                    }
                                }
                            }
                        }

                        // If no language marked as original, treat first language as original
                        if (empty($original_language) && !empty($attrs['languages'])) {
                            if (!empty($attrs['languages'][0]['name'])) {
                                $original_language = $attrs['languages'][0]['name'];
                                // Remove it from translations if it was added
                                $languages = array_diff($languages, array($original_language));
                            }
                        }
                    }

                    break; // Found the block, exit loop
                }
            }
        }

        // Fallback: Eski tablo formatı için (geriye dönük uyumluluk)
        if (empty($original_language)) {
            $table_matches = array();
            $header_matches = array();
            $column_matches = array();

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
        }

        return array(
            'original' => $original_language,
            'translations' => $languages
        );
    }
}
?>

<div class="site-content-wrapper flex flex-col md:flex-row">

    <?php get_template_part('template-parts/arcuras-sidebar'); ?>

    <main id="primary" class="site-main flex-1 px-4 sm:px-6 lg:px-8 py-8 overflow-x-hidden bg-gradient-to-br from-gray-50 via-white to-primary-50/30 min-h-screen">

        <?php
        // Hero bölümü
        if (is_home() && is_front_page()) :
        ?>
        <div class="hero-posts mb-12 md:mb-16 relative">
            <!-- Section Header -->
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-3xl font-bold text-gray-900">
                    <?php esc_html_e('Featured Lyrics', 'gufte'); ?>
                </h2>
            </div>

            <?php
            // Use the new reusable slider component
            arcuras_lyrics_slider(
                array(
                    'post_type' => 'lyrics',
                    'posts_per_page' => 7,
                    'post_status' => 'publish'
                ),
                array(
                    'slider_id' => 'hero-slider',
                    'show_navigation' => true,
                    'show_pagination' => true,
                    'card_type' => 'hero'
                )
            );
            ?>
        </div>
        <?php
        endif;
        ?>
        <!-- Popular Singers Section - Modern List -->
        <div class="popular-singers mb-12 md:mb-16 section-animate relative">
            <!-- Decorative gradient -->
            <div class="absolute -top-20 -left-20 w-72 h-72 bg-primary-200/30 rounded-full blur-3xl -z-10"></div>

            <div class="section-header flex justify-between items-center mb-4 md:mb-6">
                <h2 class="text-lg md:text-2xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent flex items-center">
                    <?php gufte_icon("microphone", "mr-2 md:mr-3 text-primary-600 text-xl md:text-3xl w-5 h-5 md:w-8 md:h-8"); ?>
                    <span class="truncate"><?php esc_html_e('Popular Singers', 'gufte'); ?></span>
                </h2>
                <a href="<?php echo esc_url(home_url('/singers/')); ?>" class="text-primary-600 hover:text-primary-700 flex items-center transition-all duration-300 bg-gradient-to-r from-primary-50 to-blue-50 hover:from-primary-100 hover:to-blue-100 px-2 py-1.5 md:px-4 md:py-2 rounded-xl border border-primary-200/50 hover:border-primary-300 text-xs md:text-sm whitespace-nowrap hover:shadow-lg hover:-translate-y-0.5">
                    <?php esc_html_e('View All', 'gufte'); ?>
                    <?php gufte_icon("arrow-right", "ml-1 w-3 h-3 md:w-4 md:h-4"); ?>
                </a>
            </div>

            <?php
            $singers = get_terms(array(
                'taxonomy' => 'singer',
                'orderby' => 'count',
                'order' => 'DESC',
                'number' => 8,
                'hide_empty' => true
            ));

            if (!empty($singers) && !is_wp_error($singers)) {
                echo '<div class="bg-white/80 backdrop-blur-xl rounded-2xl border border-white/20 overflow-hidden shadow-xl hover:shadow-2xl transition-shadow duration-500">';
                echo '<div class="divide-y divide-gray-100/50">';
                
                foreach ($singers as $index => $singer) {
                    $image_id = get_term_meta($singer->term_id, 'singer_image_id', true);
                    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : '';
                    
                    // Awards stats güvenli şekilde al
                    $awards_stats = array('total_awards' => 0);
                    if (function_exists('gufte_get_singer_awards_stats')) {
                        $temp_stats = gufte_get_singer_awards_stats($singer->term_id);
                        if (is_array($temp_stats) && isset($temp_stats['total_awards'])) {
                            $awards_stats = $temp_stats;
                        }
                    }
                    ?>
                    <a href="<?php echo esc_url(get_term_link($singer)); ?>" class="flex items-center p-4 hover:bg-gray-50 transition-colors duration-200 group">
                        <div class="w-8 text-center mr-4">
                            <span class="text-lg font-bold text-gray-400 group-hover:text-primary-600 transition-colors duration-200">
                                <?php echo esc_html($index + 1); ?>
                            </span>
                        </div>
                        
                        <div class="relative mr-4 flex-shrink-0">
                            <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-gray-200 group-hover:border-primary-300 transition-all duration-300 shadow-sm">
                                <?php if ($image_url) { ?>
                                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($singer->name); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                <?php } else { ?>
                                    <div class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                        <?php gufte_icon("microphone", "text-2xl text-gray-400 group-hover:text-primary-600 transition-colors duration-300 w-8 h-8"); ?>
                                    </div>
                                <?php } ?>
                            </div>
                            
                            <?php if ($awards_stats['total_awards'] > 0) { ?>
                                <div class="absolute -top-1 -right-1 w-6 h-6 bg-gradient-to-r from-yellow-400 to-yellow-600 rounded-full flex items-center justify-center text-xs font-bold text-yellow-900 shadow-md"
                                     title="<?php echo esc_attr(sprintf('%d Awards', $awards_stats['total_awards'])); ?>">
                                    <?php gufte_icon("trophy", "text-xs w-4 h-4"); ?>
                                </div>
                            <?php } ?>
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-900 group-hover:text-primary-700 transition-colors duration-200 truncate">
                                <?php echo esc_html($singer->name); ?>
                            </h3>
                            <div class="text-sm text-gray-600 mt-1">
                                <?php echo esc_html(number_format_i18n($singer->count)); ?> songs
                                <?php if ($awards_stats['total_awards'] > 0) { ?>
                                    • <?php echo esc_html($awards_stats['total_awards']); ?> awards
                                <?php } ?>
                            </div>
                        </div>
                        
                        <div class="ml-4 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            <div class="w-10 h-10 bg-primary-600 rounded-full flex items-center justify-center text-white shadow-lg">
                                <?php gufte_icon("play", "w-5 h-5"); ?>
                            </div>
                        </div>
                    </a>
                    <?php
                }
                
                echo '</div>';
                echo '</div>';
            } else {
                echo '<div class="bg-white rounded-xl border border-gray-200 p-8 text-center">';
                echo '<span class="block mb-3"><?php gufte_icon("microphone", "text-4xl text-gray-300 w-16 h-16 mx-auto"); ?></span>';
                echo '<p class="text-gray-600">No singers found yet.</p>';
                echo '</div>';
            }
            ?>
        </div>

        <!-- Popular Songwriters Section - Modern List -->
        <div class="popular-songwriters mb-12 md:mb-16 section-animate relative">
            <div class="absolute -top-20 -right-20 w-72 h-72 bg-amber-200/30 rounded-full blur-3xl -z-10"></div>

            <div class="section-header flex justify-between items-center mb-4 md:mb-6">
                <h2 class="text-lg md:text-2xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent flex items-center">
                    <?php gufte_icon("pen", "mr-2 md:mr-3 text-primary-600 text-xl md:text-3xl w-5 h-5 md:w-8 md:h-8"); ?>
                    <span class="truncate"><?php esc_html_e('Popular Songwriters', 'gufte'); ?></span>
                </h2>
                <a href="<?php echo esc_url(home_url('/songwriters/')); ?>" class="text-primary-600 hover:text-primary-700 flex items-center transition-all duration-300 bg-gradient-to-r from-primary-50 to-blue-50 hover:from-primary-100 hover:to-blue-100 px-2 py-1.5 md:px-4 md:py-2 rounded-xl border border-primary-200/50 hover:border-primary-300 text-xs md:text-sm whitespace-nowrap hover:shadow-lg hover:-translate-y-0.5">
                    <?php esc_html_e('View All', 'gufte'); ?>
                    <?php gufte_icon("arrow-right", "ml-1 w-3 h-3 md:w-4 md:h-4"); ?>
                </a>
            </div>

            <?php
            if (taxonomy_exists('songwriter')) {
                $songwriters = get_terms(array(
                    'taxonomy' => 'songwriter',
                    'orderby' => 'count',
                    'order' => 'DESC',
                    'number' => 6,
                    'hide_empty' => true,
                ));

                if (!empty($songwriters) && !is_wp_error($songwriters)) {
                    echo '<div class="bg-white/80 backdrop-blur-xl rounded-2xl border border-white/20 overflow-hidden shadow-xl hover:shadow-2xl transition-shadow duration-500">';
                    echo '<div class="divide-y divide-gray-100/50">';
                    
                    foreach ($songwriters as $index => $songwriter) {
                        $image_id = get_term_meta($songwriter->term_id, 'profile_image_id', true);
                        $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : '';
                        
                        // Meta bilgiler
                        $real_name = get_term_meta($songwriter->term_id, 'real_name', true);
                        $country = get_term_meta($songwriter->term_id, 'country', true);
                        ?>
                        <a href="<?php echo esc_url(get_term_link($songwriter)); ?>" class="flex items-center p-4 hover:bg-gray-50 transition-colors duration-200 group">
                            <div class="w-8 text-center mr-4">
                                <span class="text-lg font-bold text-gray-400 group-hover:text-primary-600 transition-colors duration-200">
                                    <?php echo esc_html($index + 1); ?>
                                </span>
                            </div>
                            
                            <div class="mr-4 flex-shrink-0">
                                <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-gray-200 group-hover:border-primary-300 transition-all duration-300 shadow-sm">
                                    <?php if ($image_url) { ?>
                                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($songwriter->name); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                    <?php } else { ?>
                                        <div class="w-full h-full bg-gradient-to-br from-amber-100 to-amber-200 flex items-center justify-center">
                                            <?php gufte_icon("pen", "text-2xl text-amber-600 group-hover:text-amber-700 transition-colors duration-300 w-8 h-8"); ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-gray-900 group-hover:text-primary-700 transition-colors duration-200 truncate">
                                    <?php echo esc_html($songwriter->name); ?>
                                </h3>
                                <div class="flex items-center gap-3 mt-1">
                                    <span class="text-sm text-gray-600"><?php echo esc_html(number_format_i18n($songwriter->count)); ?> songs</span>
                                    
                                    <?php if (!empty($country)) { ?>
                                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">
                                            <?php echo esc_html($country); ?>
                                        </span>
                                    <?php } ?>
                                </div>
                                
                                <?php if (!empty($real_name)) { ?>
                                    <div class="text-xs text-gray-500 mt-1"><?php echo esc_html($real_name); ?></div>
                                <?php } ?>
                            </div>
                            
                            <div class="ml-4 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <div class="w-10 h-10 bg-amber-600 rounded-full flex items-center justify-center text-white shadow-lg">
                                    <?php gufte_icon("pen", "w-5 h-5"); ?>
                                </div>
                            </div>
                        </a>
                        <?php
                    }
                    
                    echo '</div>';
                    echo '</div>';
                } else {
                    echo '<div class="bg-white rounded-xl border border-gray-200 p-8 text-center">';
                    echo '<span class="block mb-3"><?php gufte_icon("pen", "text-4xl text-gray-300 w-16 h-16 mx-auto"); ?></span>';
                    echo '<p class="text-gray-600">No songwriters found yet.</p>';
                    echo '</div>';
                }
            }
            ?>
        </div>
        <?php
            endif;
        endif;
        ?>
        
        <!-- Languages Section - Modern List -->
        <div class="languages-section mb-12 md:mb-16 section-animate relative">
            <div class="absolute -top-20 left-1/2 w-72 h-72 bg-emerald-200/30 rounded-full blur-3xl -z-10"></div>

            <div class="section-header flex justify-between items-center mb-4 md:mb-6">
                <h2 class="text-lg md:text-2xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent flex items-center">
                    <?php gufte_icon("translate", "mr-2 md:mr-3 text-primary-600 text-xl md:text-3xl w-5 h-5 md:w-8 md:h-8"); ?>
                    <span class="truncate"><?php esc_html_e('Browse by Language', 'gufte'); ?></span>
                </h2>
                <a href="<?php echo esc_url(home_url('/languages/')); ?>" class="text-primary-600 hover:text-primary-700 flex items-center transition-all duration-300 bg-gradient-to-r from-primary-50 to-blue-50 hover:from-primary-100 hover:to-blue-100 px-2 py-1.5 md:px-4 md:py-2 rounded-xl border border-primary-200/50 hover:border-primary-300 text-xs md:text-sm whitespace-nowrap hover:shadow-lg hover:-translate-y-0.5">
                    <?php esc_html_e('View All', 'gufte'); ?>
                    <?php gufte_icon("arrow-right", "ml-1 w-3 h-3 md:w-4 md:h-4"); ?>
                </a>
            </div>

            <?php
            // Aktif dilleri ve şarkı sayılarını al
            $language_stats = array();
            if (function_exists('gufte_get_language_statistics')) {
                $language_stats = gufte_get_language_statistics();
            }

            if (!empty($language_stats)) :
                echo '<div class="bg-white/80 backdrop-blur-xl rounded-2xl border border-white/20 overflow-hidden shadow-xl hover:shadow-2xl transition-shadow duration-500">';
                echo '<div class="divide-y divide-gray-100/50">';

                $index = 0;
                foreach ($language_stats as $lang_slug => $data) :
                    if ($index >= 8) break; // İlk 8 dili göster

                    $language_url = home_url("/language/{$lang_slug}/");
                    $flag = isset($data['flag']) ? $data['flag'] : '🌐';
                    $native_name = isset($data['native_name']) ? $data['native_name'] : $lang_slug;
                    $count = isset($data['count']) ? $data['count'] : 0;
                    ?>
                    <a href="<?php echo esc_url($language_url); ?>" class="flex items-center p-4 hover:bg-gray-50 transition-colors duration-200 group">
                        <div class="w-8 text-center mr-4">
                            <span class="text-lg font-bold text-gray-400 group-hover:text-primary-600 transition-colors duration-200">
                                <?php echo esc_html($index + 1); ?>
                            </span>
                        </div>

                        <div class="mr-4 flex-shrink-0">
                            <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-gray-200 group-hover:border-primary-300 transition-all duration-300 shadow-sm bg-gradient-to-br from-emerald-50 to-teal-100 flex items-center justify-center">
                                <span class="text-3xl"><?php echo $flag; ?></span>
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-900 group-hover:text-primary-700 transition-colors duration-200 truncate">
                                <?php echo esc_html($native_name); ?>
                            </h3>
                            <div class="text-sm text-gray-600 mt-1">
                                <?php echo esc_html(number_format_i18n($count)); ?>
                                <?php echo _n('song', 'songs', $count, 'gufte'); ?>
                            </div>
                        </div>

                        <div class="ml-4 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            <div class="w-10 h-10 bg-emerald-600 rounded-full flex items-center justify-center text-white shadow-lg">
                                <?php gufte_icon("translate", "w-5 h-5"); ?>
                            </div>
                        </div>
                    </a>
                    <?php
                    $index++;
                endforeach;

                echo '</div>';
                echo '</div>';
            else :
                echo '<div class="bg-white rounded-xl border border-gray-200 p-8 text-center">';
                echo '<span class="block mb-3 text-6xl">🌐</span>';
                echo '<p class="text-gray-600">' . esc_html__('No translated songs found yet.', 'gufte') . '</p>';
                echo '</div>';
            endif;
            ?>
        </div>

        <!-- Category Sections with Compact Cards -->
        <div class="category-sections space-y-12">
            <?php
            $popular_categories = get_terms(array(
                'taxonomy' => 'category',
                'orderby' => 'count',
                'order' => 'DESC',
                'number' => 3,
                'hide_empty' => true,
                'exclude' => 1
            ));

            if (!empty($popular_categories) && !is_wp_error($popular_categories)) :
                foreach ($popular_categories as $category) :
                    $category_posts = new WP_Query(array(
                        'post_type' => 'lyrics',
                        'posts_per_page' => 6,
                        'category_name' => $category->slug,
                        'post_status' => 'publish',
                        'no_found_rows' => true
                    ));

                    if ($category_posts->have_posts()) :
            ?>
            <section class="category-section section-animate relative mb-12 md:mb-16">
                <div class="absolute -top-16 right-10 w-64 h-64 bg-blue-200/20 rounded-full blur-3xl -z-10"></div>

                <div class="section-header flex justify-between items-center mb-4 md:mb-6">
                    <h2 class="text-lg md:text-2xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent flex items-center">
                        <?php gufte_icon("music-box", "mr-2 md:mr-3 text-primary-600 text-xl md:text-3xl w-5 h-5 md:w-8 md:h-8"); ?>
                        <span class="truncate"><?php echo esc_html($category->name); ?></span>
                    </h2>
                    <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>" class="text-primary-600 hover:text-primary-700 flex items-center transition-all duration-300 bg-gradient-to-r from-primary-50 to-blue-50 hover:from-primary-100 hover:to-blue-100 px-2 py-1.5 md:px-4 md:py-2 rounded-xl border border-primary-200/50 hover:border-primary-300 text-xs md:text-sm whitespace-nowrap hover:shadow-lg hover:-translate-y-0.5">
                        <?php esc_html_e('View All', 'gufte'); ?>
                        <?php gufte_icon("arrow-right", "ml-1 w-3 h-3 md:w-4 md:h-4"); ?>
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <?php
                    while ($category_posts->have_posts()) :
                        $category_posts->the_post();
                        $singers = get_the_terms(get_the_ID(), 'singer');
                        $singer = !empty($singers) && !is_wp_error($singers) ? reset($singers) : null;

                        $post = get_post(get_the_ID());
                        $raw_content = $post->post_content;
                        $lyrics_languages = array('original' => '', 'translations' => array());
                        if (function_exists('gufte_get_lyrics_languages')) {
                            $lyrics_languages = gufte_get_lyrics_languages($raw_content);
                        }
                        $translation_count = !empty($lyrics_languages['translations']) ? count($lyrics_languages['translations']) : 0;
                    ?>
                    <div <?php post_class('compact-card group bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-gray-300'); ?>>
                        <a href="<?php the_permalink(); ?>" class="block relative aspect-square overflow-hidden bg-gray-100">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('medium', array('class' => 'w-full h-full object-cover group-hover:scale-110 transition-transform duration-500')); ?>
                            <?php else : ?>
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                                    <?php gufte_icon("music-note", "text-5xl text-gray-400 group-hover:text-primary-500 transition-colors duration-300 w-20 h-20"); ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($translation_count > 0) : ?>
                            <div class="absolute top-2 right-2 bg-primary-500/90 backdrop-blur-sm text-white text-xs font-bold px-1.5 py-0.5 rounded-full flex items-center">
                                <?php gufte_icon("translate", "mr-0.5 text-xs w-3 h-3"); ?>
                                <?php echo esc_html($translation_count); ?>
                            </div>
                            <?php endif; ?>

                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <div class="absolute bottom-0 left-0 right-0 p-3 text-white">
                                    <p class="text-xs font-medium line-clamp-2"><?php the_title(); ?></p>
                                </div>
                            </div>
                        </a>

                        <div class="p-3">
                            <h3 class="text-sm font-semibold mb-1.5 line-clamp-2 min-h-[2.5rem]">
                                <a href="<?php the_permalink(); ?>" class="text-gray-800 hover:text-primary-600 transition-colors duration-300">
                                    <?php the_title(); ?>
                                </a>
                            </h3>

                            <?php if ($singer) : ?>
                            <div class="text-xs text-gray-500 truncate">
                                <a href="<?php echo esc_url(get_term_link($singer)); ?>" class="hover:text-primary-600 transition-colors duration-300">
                                    <?php echo esc_html($singer->name); ?>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                    endwhile;
                    wp_reset_postdata();
                    ?>
                </div>
            </section>
            <?php
                    endif;
                endforeach;
            endif;
            ?>
        </div>

        <!-- Latest Posts with Compact Grid -->
        <div class="latest-posts mt-12 mb-12 md:mb-16 section-animate relative">
            <div class="absolute -top-20 left-20 w-80 h-80 bg-purple-200/20 rounded-full blur-3xl -z-10"></div>

            <div class="section-header flex justify-between items-center mb-4 md:mb-6">
                <h2 class="text-lg md:text-2xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent flex items-center">
                    <?php gufte_icon("clock", "mr-2 md:mr-3 text-primary-600 text-xl md:text-3xl w-5 h-5 md:w-8 md:h-8"); ?>
                    <span class="truncate"><?php esc_html_e('Latest Lyrics', 'gufte'); ?></span>
                </h2>
                <?php
                // Blog arşiv sayfası için URL
                $blog_page_id = get_option('page_for_posts');
                $lyrics_archive_url = $blog_page_id ? get_permalink($blog_page_id) : home_url('/lyrics/');
                ?>
                <a href="<?php echo esc_url($lyrics_archive_url); ?>" class="text-primary-600 hover:text-primary-700 flex items-center transition-all duration-300 bg-gradient-to-r from-primary-50 to-blue-50 hover:from-primary-100 hover:to-blue-100 px-2 py-1.5 md:px-4 md:py-2 rounded-xl border border-primary-200/50 hover:border-primary-300 text-xs md:text-sm whitespace-nowrap hover:shadow-lg hover:-translate-y-0.5">
                    <?php esc_html_e('View All', 'gufte'); ?>
                    <?php gufte_icon("arrow-right", "ml-1 w-3 h-3 md:w-4 md:h-4"); ?>
                </a>
            </div>

            <?php
            $latest_posts_args = array(
                'post_type'           => 'lyrics',
                'posts_per_page'      => 12,
                'post_status'         => 'publish',
                'ignore_sticky_posts' => 1
            );

            $latest_posts = new WP_Query($latest_posts_args);

            if ($latest_posts->have_posts()) :
            ?>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                <?php
                while ($latest_posts->have_posts()) :
                    $latest_posts->the_post();

                    $singers = get_the_terms(get_the_ID(), 'singer');
                    $singer = !empty($singers) && !is_wp_error($singers) ? reset($singers) : null;

                    $post = get_post(get_the_ID());
                    $raw_content = $post->post_content;
                    $lyrics_languages = array('original' => '', 'translations' => array());
                    if (function_exists('gufte_get_lyrics_languages')) {
                        $lyrics_languages = gufte_get_lyrics_languages($raw_content);
                    }
                    $translation_count = !empty($lyrics_languages['translations']) ? count($lyrics_languages['translations']) : 0;
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('compact-card group bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-gray-300'); ?>>
                    <a href="<?php the_permalink(); ?>" class="block relative aspect-square overflow-hidden bg-gray-100">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('medium', array('class' => 'w-full h-full object-cover group-hover:scale-110 transition-transform duration-500')); ?>
                        <?php else : ?>
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                                <?php gufte_icon("music-note", "text-5xl text-gray-400 group-hover:text-primary-500 transition-colors duration-300 w-20 h-20"); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($translation_count > 0) : ?>
                        <div class="absolute top-2 right-2 bg-primary-500/90 backdrop-blur-sm text-white text-xs font-bold px-1.5 py-0.5 rounded-full flex items-center">
                            <?php gufte_icon("translate", "mr-0.5 text-xs w-3 h-3"); ?>
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
                        <div class="text-xs text-gray-500 truncate">
                            <a href="<?php echo esc_url(get_term_link($singer)); ?>" class="hover:text-primary-600 transition-colors duration-300">
                                <?php echo esc_html($singer->name); ?>
                            </a>
                        </div>
                        <?php endif; ?>

                        <div class="text-xs text-gray-400 mt-1">
                            <time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date(); ?></time>
                        </div>
                    </div>
                </article>
                <?php
                endwhile;
                wp_reset_postdata();
                ?>
            </div>
            <?php else : ?>
                <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                    <span class="block mb-3 text-6xl">🎵</span>
                    <p class="text-gray-600"><?php esc_html_e('No lyrics found yet.', 'gufte'); ?></p>
                </div>
            <?php endif; ?>
        </div>

    </main>
</div>

<?php
get_footer();