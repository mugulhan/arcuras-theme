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
        <div class="hero-posts mb-6 md:mb-8 relative">
            <?php
            // Section Header
            set_query_var('section_title', __('Featured Lyrics', 'gufte'));
            set_query_var('section_icon', 'star');
            set_query_var('section_link_url', '');
            get_template_part('template-parts/components/section-header');
            ?>

            <?php
            // Use the new reusable slider component
            arcuras_lyrics_slider(
                array(
                    'post_type' => 'lyrics',
                    'posts_per_page' => 9, // Show 9 featured lyrics
                    'post_status' => 'publish'
                ),
                array(
                    'slider_id' => 'hero-slider',
                    'show_navigation' => true,
                    'show_pagination' => false,
                    'card_type' => 'hero'
                )
            );
            ?>
        </div>
        <?php
        endif;
        ?>

        <!-- Featured Genres Section -->
        <div class="featured-genres mb-12 md:mb-16 relative">
            <div class="absolute -top-20 -left-20 w-96 h-96 bg-purple-200/20 rounded-full blur-3xl -z-10"></div>

            <?php
            arcuras_genre_grid(array(
                'title' => __('Explore Lyrics by Genre', 'gufte'),
                'icon' => 'view-grid',
                'show_navigation' => true,
                'limit' => 8, // Reduced from 10 for performance
            ));
            ?>
        </div>

        <!-- Original Languages Section (Tabbed) -->
        <div id="original-languages" class="original-languages mb-12 md:mb-16 relative">
            <div class="absolute -top-20 right-20 w-80 h-80 bg-blue-200/20 rounded-full blur-3xl -z-10"></div>

            <div class="mb-4">
                <?php
                // Section Header
                set_query_var('section_title', __('Original Languages', 'gufte'));
                set_query_var('section_icon', 'music-note');
                set_query_var('section_link_url', home_url('/lyrics/original/'));
                set_query_var('section_link_text', __('View All', 'gufte'));
                get_template_part('template-parts/components/section-header');
                ?>

                <!-- Tabs -->
                <div class="flex flex-wrap gap-2 border-b border-gray-200 pb-2">
                    <button onclick="switchTab('original', 'english')" id="tab-original-english" class="tab-button px-3 py-1.5 font-medium text-sm transition-colors duration-200 rounded-t-lg border-b-2 border-primary-600 text-primary-600 bg-primary-50">
                        <?php esc_html_e('English', 'gufte'); ?>
                    </button>
                    <button onclick="switchTab('original', 'turkish')" id="tab-original-turkish" class="tab-button px-3 py-1.5 font-medium text-sm transition-colors duration-200 rounded-t-lg border-b-2 border-transparent text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                        <?php esc_html_e('Turkish', 'gufte'); ?>
                    </button>
                    <button onclick="switchTab('original', 'spanish')" id="tab-original-spanish" class="tab-button px-3 py-1.5 font-medium text-sm transition-colors duration-200 rounded-t-lg border-b-2 border-transparent text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                        <?php esc_html_e('Spanish', 'gufte'); ?>
                    </button>
                    <button onclick="switchTab('original', 'french')" id="tab-original-french" class="tab-button px-3 py-1.5 font-medium text-sm transition-colors duration-200 rounded-t-lg border-b-2 border-transparent text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                        <?php esc_html_e('French', 'gufte'); ?>
                    </button>
                    <button onclick="switchTab('original', 'italian')" id="tab-original-italian" class="tab-button px-3 py-1.5 font-medium text-sm transition-colors duration-200 rounded-t-lg border-b-2 border-transparent text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                        <?php esc_html_e('Italian', 'gufte'); ?>
                    </button>
                    <button onclick="switchTab('original', 'korean')" id="tab-original-korean" class="tab-button px-3 py-1.5 font-medium text-sm transition-colors duration-200 rounded-t-lg border-b-2 border-transparent text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                        <?php esc_html_e('Korean', 'gufte'); ?>
                    </button>
                    <button onclick="switchTab('original', 'japanese')" id="tab-original-japanese" class="tab-button px-3 py-1.5 font-medium text-sm transition-colors duration-200 rounded-t-lg border-b-2 border-transparent text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                        <?php esc_html_e('Japanese', 'gufte'); ?>
                    </button>
                </div>
            </div>

            <?php
            // Get all posts once
            $all_posts = get_posts(array(
                'post_type' => 'lyrics',
                'posts_per_page' => 50,
                'post_status' => 'publish'
            ));

            // English posts
            $english_post_ids = array();
            foreach ($all_posts as $post) {
                $langs = gufte_get_lyrics_languages($post->post_content);
                if (!empty($langs['original']) && $langs['original'] === 'English') {
                    $english_post_ids[] = $post->ID;
                    if (count($english_post_ids) >= 10) break;
                }
            }

            // Turkish posts
            $turkish_post_ids = array();
            // Spanish posts
            $spanish_post_ids = array();
            // French posts
            $french_post_ids = array();
            // Italian posts
            $italian_post_ids = array();
            // Korean posts
            $korean_post_ids = array();
            // Japanese posts
            $japanese_post_ids = array();

            foreach ($all_posts as $post) {
                $langs = gufte_get_lyrics_languages($post->post_content);
                if (!empty($langs['original'])) {
                    $original = $langs['original'];

                    // Turkish
                    if (strpos($original, 'Turkish') !== false || strpos($original, 'Türkçe') !== false) {
                        if (count($turkish_post_ids) < 10) $turkish_post_ids[] = $post->ID;
                    }
                    // Spanish
                    else if (strpos($original, 'Spanish') !== false || strpos($original, 'Español') !== false) {
                        if (count($spanish_post_ids) < 10) $spanish_post_ids[] = $post->ID;
                    }
                    // French
                    else if (strpos($original, 'French') !== false || strpos($original, 'Français') !== false) {
                        if (count($french_post_ids) < 10) $french_post_ids[] = $post->ID;
                    }
                    // Italian
                    else if (strpos($original, 'Italian') !== false || strpos($original, 'Italiano') !== false) {
                        if (count($italian_post_ids) < 10) $italian_post_ids[] = $post->ID;
                    }
                    // Korean
                    else if (strpos($original, 'Korean') !== false || strpos($original, '한국어') !== false) {
                        if (count($korean_post_ids) < 10) $korean_post_ids[] = $post->ID;
                    }
                }
            }

            // BETTER APPROACH: Use taxonomy queries (same data as template pages)
            // This ensures homepage shows same posts as /original-language/{lang}/

            // Override with taxonomy-based queries
            $english_posts_tax = get_posts(array(
                'post_type' => 'lyrics',
                'posts_per_page' => 10,
                'post_status' => 'publish',
                'tax_query' => array(array(
                    'taxonomy' => 'original_language',
                    'field' => 'slug',
                    'terms' => 'english'
                ))
            ));
            if (!empty($english_posts_tax)) {
                $english_post_ids = wp_list_pluck($english_posts_tax, 'ID');
            }

            $turkish_posts_tax = get_posts(array(
                'post_type' => 'lyrics',
                'posts_per_page' => 10,
                'post_status' => 'publish',
                'tax_query' => array(array(
                    'taxonomy' => 'original_language',
                    'field' => 'slug',
                    'terms' => 'turkish'
                ))
            ));
            if (!empty($turkish_posts_tax)) {
                $turkish_post_ids = wp_list_pluck($turkish_posts_tax, 'ID');
            }

            $spanish_posts_tax = get_posts(array(
                'post_type' => 'lyrics',
                'posts_per_page' => 10,
                'post_status' => 'publish',
                'tax_query' => array(array(
                    'taxonomy' => 'original_language',
                    'field' => 'slug',
                    'terms' => 'spanish'
                ))
            ));
            if (!empty($spanish_posts_tax)) {
                $spanish_post_ids = wp_list_pluck($spanish_posts_tax, 'ID');
            }

            $french_posts_tax = get_posts(array(
                'post_type' => 'lyrics',
                'posts_per_page' => 10,
                'post_status' => 'publish',
                'tax_query' => array(array(
                    'taxonomy' => 'original_language',
                    'field' => 'slug',
                    'terms' => 'french'
                ))
            ));
            if (!empty($french_posts_tax)) {
                $french_post_ids = wp_list_pluck($french_posts_tax, 'ID');
            }

            $italian_posts_tax = get_posts(array(
                'post_type' => 'lyrics',
                'posts_per_page' => 10,
                'post_status' => 'publish',
                'tax_query' => array(array(
                    'taxonomy' => 'original_language',
                    'field' => 'slug',
                    'terms' => 'italian'
                ))
            ));
            if (!empty($italian_posts_tax)) {
                $italian_post_ids = wp_list_pluck($italian_posts_tax, 'ID');
            }

            $korean_posts_tax = get_posts(array(
                'post_type' => 'lyrics',
                'posts_per_page' => 10,
                'post_status' => 'publish',
                'tax_query' => array(array(
                    'taxonomy' => 'original_language',
                    'field' => 'slug',
                    'terms' => 'korean'
                ))
            ));
            if (!empty($korean_posts_tax)) {
                $korean_post_ids = wp_list_pluck($korean_posts_tax, 'ID');
            }

            $japanese_posts_tax = get_posts(array(
                'post_type' => 'lyrics',
                'posts_per_page' => 10,
                'post_status' => 'publish',
                'tax_query' => array(array(
                    'taxonomy' => 'original_language',
                    'field' => 'slug',
                    'terms' => 'japanese'
                ))
            ));
            if (!empty($japanese_posts_tax)) {
                $japanese_post_ids = wp_list_pluck($japanese_posts_tax, 'ID');
            }
            ?>

            <!-- English Tab Content -->
            <div id="content-original-english" class="tab-content">
                <div class="custom-slider-container" style="position: relative; width: 100%; overflow: hidden;">
                    <div class="custom-slider-wrapper" style="display: flex; align-items: flex-start; overflow-x: auto; scroll-snap-type: x mandatory; scroll-behavior: smooth; -webkit-overflow-scrolling: touch; scrollbar-width: none; -ms-overflow-style: none; gap: 0;">
                        <?php if (!empty($english_post_ids)) :
                            $english_query = new WP_Query(array(
                                'post_type' => 'lyrics',
                                'post__in' => $english_post_ids,
                                'posts_per_page' => 10,
                                'post_status' => 'publish',
                                'orderby' => 'post__in'
                            ));
                            while ($english_query->have_posts()) : $english_query->the_post(); ?>
                                <div class="custom-slide" style="flex: 0 0 auto; width: 280px; scroll-snap-align: start; padding: 8px; box-sizing: border-box; align-self: flex-start;">
                                    <div style="height: 100%;">
                                        <?php arcuras_lyrics_card(get_the_ID(), array('card_type' => 'compact')); ?>
                                    </div>
                                </div>
                            <?php endwhile;
                            wp_reset_postdata();
                        endif; ?>

                        <!-- Discover Card -->
                        <div class="custom-slide" style="flex: 0 0 auto; width: 280px; scroll-snap-align: start; padding: 8px; box-sizing: border-box; align-self: flex-start;">
                            <?php
                            $english_term = get_term_by('slug', 'english', 'original_language');
                            $english_url = $english_term ? get_term_link($english_term) : home_url('/lyrics/original/english/');
                            ?>
                            <a href="<?php echo esc_url($english_url); ?>" style="display: block; height: 100%; text-decoration: none;">
                                <div style="height: 100%; min-height: 300px; background: linear-gradient(to bottom right, #6366f1, #4338ca); border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); overflow: hidden; border: 1px solid #4f46e5; transition: all 0.3s; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1.5rem; text-align: center; color: white;">
                                    <div style="font-size: 4rem; margin-bottom: 1rem;">
                                        <?php gufte_icon("music-note", "w-16 h-16 mx-auto"); ?>
                                    </div>
                                    <h3 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 0.5rem; color: white;"><?php esc_html_e('Discover English Songs', 'gufte'); ?></h3>
                                    <p style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 1rem; color: white;"><?php esc_html_e('Explore all lyrics in original English language', 'gufte'); ?></p>
                                    <span style="display: inline-flex; align-items: center; font-size: 0.875rem; font-weight: 600; color: white;">
                                        <?php esc_html_e('View All', 'gufte'); ?>
                                        <span style="margin-left: 0.5rem;">→</span>
                                    </span>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <button type="button" class="custom-slider-prev" onclick="scrollSlider('english-slider-custom', -1)" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); z-index: 10; width: 48px; height: 48px; border-radius: 50%; background: white; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid #e5e7eb; display: none; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s;">
                        <?php gufte_icon("chevron-left", "w-6 h-6"); ?>
                    </button>
                    <button type="button" class="custom-slider-next" onclick="scrollSlider('english-slider-custom', 1)" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); z-index: 10; width: 48px; height: 48px; border-radius: 50%; background: white; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid #e5e7eb; display: none; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s;">
                        <?php gufte_icon("chevron-right", "w-6 h-6"); ?>
                    </button>
                </div>
                <script>
                (function() {
                    const container = document.querySelector('#content-original-english .custom-slider-container');
                    if (!container) return;
                    container.id = 'english-slider-custom';
                    const wrapper = container.querySelector('.custom-slider-wrapper');
                    const prevBtn = container.querySelector('.custom-slider-prev');
                    const nextBtn = container.querySelector('.custom-slider-next');

                    function updateButtons() {
                        if (!wrapper) return;
                        const isAtStart = wrapper.scrollLeft <= 10;
                        const isAtEnd = wrapper.scrollLeft >= wrapper.scrollWidth - wrapper.clientWidth - 10;
                        if (prevBtn) prevBtn.style.display = isAtStart ? 'none' : 'flex';
                        if (nextBtn) nextBtn.style.display = isAtEnd ? 'none' : 'flex';
                    }

                    if (wrapper) {
                        wrapper.addEventListener('scroll', updateButtons);
                        updateButtons();
                    }
                })();
                </script>
            </div>

            <!-- Turkish Tab Content -->
            <div id="content-original-turkish" class="tab-content hidden">
                <div class="custom-slider-container" style="position: relative; width: 100%; overflow: hidden;">
                    <div class="custom-slider-wrapper" style="display: flex; align-items: flex-start; overflow-x: auto; scroll-snap-type: x mandatory; scroll-behavior: smooth; -webkit-overflow-scrolling: touch; scrollbar-width: none; -ms-overflow-style: none; gap: 0;">
                        <?php if (!empty($turkish_post_ids)) :
                            $turkish_query = new WP_Query(array(
                                'post_type' => 'lyrics',
                                'post__in' => $turkish_post_ids,
                                'posts_per_page' => 10,
                                'post_status' => 'publish',
                                'orderby' => 'post__in'
                            ));
                            while ($turkish_query->have_posts()) : $turkish_query->the_post(); ?>
                                <div class="custom-slide" style="flex: 0 0 auto; width: 280px; scroll-snap-align: start; padding: 8px; box-sizing: border-box; align-self: flex-start;">
                                    <div style="height: 100%;">
                                        <?php arcuras_lyrics_card(get_the_ID(), array('card_type' => 'compact')); ?>
                                    </div>
                                </div>
                            <?php endwhile;
                            wp_reset_postdata();
                        endif; ?>

                        <!-- Discover Card -->
                        <div class="custom-slide" style="flex: 0 0 auto; width: 280px; scroll-snap-align: start; padding: 8px; box-sizing: border-box; align-self: flex-start;">
                            <?php
                            $turkish_term = get_term_by('slug', 'turkish', 'original_language');
                            $turkish_url = $turkish_term ? get_term_link($turkish_term) : home_url('/lyrics/original/turkish/');
                            ?>
                            <a href="<?php echo esc_url($turkish_url); ?>" style="display: block; height: 100%; text-decoration: none;">
                                <div style="height: 100%; min-height: 300px; background: linear-gradient(to bottom right, #ef4444, #b91c1c); border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); overflow: hidden; border: 1px solid #dc2626; transition: all 0.3s; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1.5rem; text-align: center; color: white;">
                                    <div style="font-size: 4rem; margin-bottom: 1rem;">
                                        <?php gufte_icon("music-note", "w-16 h-16 mx-auto"); ?>
                                    </div>
                                    <h3 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 0.5rem; color: white;"><?php esc_html_e('Discover Turkish Songs', 'gufte'); ?></h3>
                                    <p style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 1rem; color: white;"><?php esc_html_e('Explore all lyrics in original Turkish language', 'gufte'); ?></p>
                                    <span style="display: inline-flex; align-items: center; font-size: 0.875rem; font-weight: 600; color: white;">
                                        <?php esc_html_e('View All', 'gufte'); ?>
                                        <span style="margin-left: 0.5rem;">→</span>
                                    </span>
                                </div>
                            </a>
                        </div>
                    </div>

                    <button type="button" class="custom-slider-prev" onclick="scrollSlider('turkish-slider-custom', -1)" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); z-index: 10; width: 48px; height: 48px; border-radius: 50%; background: white; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid #e5e7eb; display: none; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s;">
                        <?php gufte_icon("chevron-left", "w-6 h-6"); ?>
                    </button>
                    <button type="button" class="custom-slider-next" onclick="scrollSlider('turkish-slider-custom', 1)" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); z-index: 10; width: 48px; height: 48px; border-radius: 50%; background: white; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid #e5e7eb; display: none; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s;">
                        <?php gufte_icon("chevron-right", "w-6 h-6"); ?>
                    </button>
                </div>
                <script>
                (function() {
                    const container = document.querySelector('#content-original-turkish .custom-slider-container');
                    if (!container) return;
                    container.id = 'turkish-slider-custom';
                    const wrapper = container.querySelector('.custom-slider-wrapper');
                    const prevBtn = container.querySelector('.custom-slider-prev');
                    const nextBtn = container.querySelector('.custom-slider-next');

                    function updateButtons() {
                        if (!wrapper) return;
                        const isAtStart = wrapper.scrollLeft <= 10;
                        const isAtEnd = wrapper.scrollLeft >= wrapper.scrollWidth - wrapper.clientWidth - 10;
                        if (prevBtn) prevBtn.style.display = isAtStart ? 'none' : 'flex';
                        if (nextBtn) nextBtn.style.display = isAtEnd ? 'none' : 'flex';
                    }

                    if (wrapper) {
                        wrapper.addEventListener('scroll', updateButtons);
                        updateButtons();
                    }
                })();
                </script>
            </div>

            <!-- Spanish Tab Content -->
            <div id="content-original-spanish" class="tab-content hidden">
                <div class="custom-slider-container" style="position: relative; width: 100%; overflow: hidden;">
                    <div class="custom-slider-wrapper" style="display: flex; align-items: flex-start; overflow-x: auto; scroll-snap-type: x mandatory; scroll-behavior: smooth; -webkit-overflow-scrolling: touch; scrollbar-width: none; -ms-overflow-style: none; gap: 0;">
                        <?php if (!empty($spanish_post_ids)) :
                            $spanish_query = new WP_Query(array(
                                'post_type' => 'lyrics',
                                'post__in' => $spanish_post_ids,
                                'posts_per_page' => 10,
                                'post_status' => 'publish',
                                'orderby' => 'post__in'
                            ));
                            while ($spanish_query->have_posts()) : $spanish_query->the_post(); ?>
                                <div class="custom-slide" style="flex: 0 0 auto; width: 280px; scroll-snap-align: start; padding: 8px; box-sizing: border-box; align-self: flex-start;">
                                    <div style="height: 100%;">
                                        <?php arcuras_lyrics_card(get_the_ID(), array('card_type' => 'compact')); ?>
                                    </div>
                                </div>
                            <?php endwhile;
                            wp_reset_postdata();
                        endif; ?>

                        <!-- Discover Card -->
                        <div class="custom-slide" style="flex: 0 0 auto; width: 280px; scroll-snap-align: start; padding: 8px; box-sizing: border-box; align-self: flex-start;">
                            <?php
                            $spanish_term = get_term_by('slug', 'spanish', 'original_language');
                            $spanish_url = $spanish_term ? get_term_link($spanish_term) : home_url('/lyrics/original/spanish/');
                            ?>
                            <a href="<?php echo esc_url($spanish_url); ?>" style="display: block; height: 100%; text-decoration: none;">
                                <div style="height: 100%; min-height: 300px; background: linear-gradient(to bottom right, #eab308, #ea580c); border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); overflow: hidden; border: 1px solid #ea580c; transition: all 0.3s; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1.5rem; text-align: center; color: white;">
                                    <div style="font-size: 4rem; margin-bottom: 1rem;">
                                        <?php gufte_icon("music-note", "w-16 h-16 mx-auto"); ?>
                                    </div>
                                    <h3 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 0.5rem; color: white;"><?php esc_html_e('Discover Spanish Songs', 'gufte'); ?></h3>
                                    <p style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 1rem; color: white;"><?php esc_html_e('Explore all lyrics in original Spanish language', 'gufte'); ?></p>
                                    <span style="display: inline-flex; align-items: center; font-size: 0.875rem; font-weight: 600; color: white;">
                                        <?php esc_html_e('View All', 'gufte'); ?>
                                        <span style="margin-left: 0.5rem;">→</span>
                                    </span>
                                </div>
                            </a>
                        </div>
                    </div>

                    <button type="button" class="custom-slider-prev" onclick="scrollSlider('spanish-slider-custom', -1)" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); z-index: 10; width: 48px; height: 48px; border-radius: 50%; background: white; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid #e5e7eb; display: none; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s;">
                        <?php gufte_icon("chevron-left", "w-6 h-6"); ?>
                    </button>
                    <button type="button" class="custom-slider-next" onclick="scrollSlider('spanish-slider-custom', 1)" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); z-index: 10; width: 48px; height: 48px; border-radius: 50%; background: white; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid #e5e7eb; display: none; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s;">
                        <?php gufte_icon("chevron-right", "w-6 h-6"); ?>
                    </button>
                </div>
                <script>
                (function() {
                    const container = document.querySelector('#content-original-spanish .custom-slider-container');
                    if (!container) return;
                    container.id = 'spanish-slider-custom';
                    const wrapper = container.querySelector('.custom-slider-wrapper');
                    const prevBtn = container.querySelector('.custom-slider-prev');
                    const nextBtn = container.querySelector('.custom-slider-next');

                    function updateButtons() {
                        if (!wrapper) return;
                        const isAtStart = wrapper.scrollLeft <= 10;
                        const isAtEnd = wrapper.scrollLeft >= wrapper.scrollWidth - wrapper.clientWidth - 10;
                        if (prevBtn) prevBtn.style.display = isAtStart ? 'none' : 'flex';
                        if (nextBtn) nextBtn.style.display = isAtEnd ? 'none' : 'flex';
                    }

                    if (wrapper) {
                        wrapper.addEventListener('scroll', updateButtons);
                        updateButtons();
                    }
                })();
                </script>
            </div>

            <!-- French Tab Content -->
            <div id="content-original-french" class="tab-content hidden">
                <div class="custom-slider-container" style="position: relative; width: 100%; overflow: hidden;">
                    <div class="custom-slider-wrapper" style="display: flex; align-items: flex-start; overflow-x: auto; scroll-snap-type: x mandatory; scroll-behavior: smooth; -webkit-overflow-scrolling: touch; scrollbar-width: none; -ms-overflow-style: none; gap: 0;">
                        <?php if (!empty($french_post_ids)) :
                            $french_query = new WP_Query(array(
                                'post_type' => 'lyrics',
                                'post__in' => $french_post_ids,
                                'posts_per_page' => 10,
                                'post_status' => 'publish',
                                'orderby' => 'post__in'
                            ));
                            while ($french_query->have_posts()) : $french_query->the_post(); ?>
                                <div class="custom-slide" style="flex: 0 0 auto; width: 280px; scroll-snap-align: start; padding: 8px; box-sizing: border-box; align-self: flex-start;">
                                    <div style="height: 100%;">
                                        <?php arcuras_lyrics_card(get_the_ID(), array('card_type' => 'compact')); ?>
                                    </div>
                                </div>
                            <?php endwhile;
                            wp_reset_postdata();
                        endif; ?>

                        <!-- Discover Card -->
                        <div class="custom-slide" style="flex: 0 0 auto; width: 280px; scroll-snap-align: start; padding: 8px; box-sizing: border-box; align-self: flex-start;">
                            <?php
                            $french_term = get_term_by('slug', 'french', 'original_language');
                            $french_url = $french_term ? get_term_link($french_term) : home_url('/lyrics/original/french/');
                            ?>
                            <a href="<?php echo esc_url($french_url); ?>" style="display: block; height: 100%; text-decoration: none;">
                                <div style="height: 100%; min-height: 300px; background: linear-gradient(to bottom right, #3b82f6, #4f46e5); border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); overflow: hidden; border: 1px solid #4f46e5; transition: all 0.3s; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1.5rem; text-align: center; color: white;">
                                    <div style="font-size: 4rem; margin-bottom: 1rem;">
                                        <?php gufte_icon("music-note", "w-16 h-16 mx-auto"); ?>
                                    </div>
                                    <h3 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 0.5rem; color: white;"><?php esc_html_e('Discover French Songs', 'gufte'); ?></h3>
                                    <p style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 1rem; color: white;"><?php esc_html_e('Explore all lyrics in original French language', 'gufte'); ?></p>
                                    <span style="display: inline-flex; align-items: center; font-size: 0.875rem; font-weight: 600; color: white;">
                                        <?php esc_html_e('View All', 'gufte'); ?>
                                        <span style="margin-left: 0.5rem;">→</span>
                                    </span>
                                </div>
                            </a>
                        </div>
                    </div>

                    <button type="button" class="custom-slider-prev" onclick="scrollSlider('french-slider-custom', -1)" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); z-index: 10; width: 48px; height: 48px; border-radius: 50%; background: white; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid #e5e7eb; display: none; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s;">
                        <?php gufte_icon("chevron-left", "w-6 h-6"); ?>
                    </button>
                    <button type="button" class="custom-slider-next" onclick="scrollSlider('french-slider-custom', 1)" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); z-index: 10; width: 48px; height: 48px; border-radius: 50%; background: white; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid #e5e7eb; display: none; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s;">
                        <?php gufte_icon("chevron-right", "w-6 h-6"); ?>
                    </button>
                </div>
                <script>
                (function() {
                    const container = document.querySelector('#content-original-french .custom-slider-container');
                    if (!container) return;
                    container.id = 'french-slider-custom';
                    const wrapper = container.querySelector('.custom-slider-wrapper');
                    const prevBtn = container.querySelector('.custom-slider-prev');
                    const nextBtn = container.querySelector('.custom-slider-next');

                    function updateButtons() {
                        if (!wrapper) return;
                        const isAtStart = wrapper.scrollLeft <= 10;
                        const isAtEnd = wrapper.scrollLeft >= wrapper.scrollWidth - wrapper.clientWidth - 10;
                        if (prevBtn) prevBtn.style.display = isAtStart ? 'none' : 'flex';
                        if (nextBtn) nextBtn.style.display = isAtEnd ? 'none' : 'flex';
                    }

                    if (wrapper) {
                        wrapper.addEventListener('scroll', updateButtons);
                        updateButtons();
                    }
                })();
                </script>
            </div>

            <!-- Italian Tab Content -->
            <div id="content-original-italian" class="tab-content hidden">
                <div class="custom-slider-container" style="position: relative; width: 100%; overflow: hidden;">
                    <div class="custom-slider-wrapper" style="display: flex; align-items: flex-start; overflow-x: auto; scroll-snap-type: x mandatory; scroll-behavior: smooth; -webkit-overflow-scrolling: touch; scrollbar-width: none; -ms-overflow-style: none; gap: 0;">
                        <?php if (!empty($italian_post_ids)) :
                            $italian_query = new WP_Query(array(
                                'post_type' => 'lyrics',
                                'post__in' => $italian_post_ids,
                                'posts_per_page' => 10,
                                'post_status' => 'publish',
                                'orderby' => 'post__in'
                            ));
                            while ($italian_query->have_posts()) : $italian_query->the_post(); ?>
                                <div class="custom-slide" style="flex: 0 0 auto; width: 280px; scroll-snap-align: start; padding: 8px; box-sizing: border-box; align-self: flex-start;">
                                    <div style="height: 100%;">
                                        <?php arcuras_lyrics_card(get_the_ID(), array('card_type' => 'compact')); ?>
                                    </div>
                                </div>
                            <?php endwhile;
                            wp_reset_postdata();
                        endif; ?>

                        <!-- Discover Card -->
                        <div class="custom-slide" style="flex: 0 0 auto; width: 280px; scroll-snap-align: start; padding: 8px; box-sizing: border-box; align-self: flex-start;">
                            <?php
                            $italian_term = get_term_by('slug', 'italian', 'original_language');
                            $italian_url = $italian_term ? get_term_link($italian_term) : home_url('/lyrics/original/italian/');
                            ?>
                            <a href="<?php echo esc_url($italian_url); ?>" style="display: block; height: 100%; text-decoration: none;">
                                <div style="height: 100%; min-height: 300px; background: linear-gradient(to bottom right, #10b981, #047857); border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); overflow: hidden; border: 1px solid #059669; transition: all 0.3s; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1.5rem; text-align: center; color: white;">
                                    <div style="font-size: 4rem; margin-bottom: 1rem;">
                                        <?php gufte_icon("music-note", "w-16 h-16 mx-auto"); ?>
                                    </div>
                                    <h3 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 0.5rem; color: white;"><?php esc_html_e('Discover Italian Songs', 'gufte'); ?></h3>
                                    <p style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 1rem; color: white;"><?php esc_html_e('Explore all lyrics in original Italian language', 'gufte'); ?></p>
                                    <span style="display: inline-flex; align-items: center; font-size: 0.875rem; font-weight: 600; color: white;">
                                        <?php esc_html_e('View All', 'gufte'); ?>
                                        <span style="margin-left: 0.5rem;">→</span>
                                    </span>
                                </div>
                            </a>
                        </div>
                    </div>

                    <button type="button" class="custom-slider-prev" onclick="scrollSlider('italian-slider-custom', -1)" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); z-index: 10; width: 48px; height: 48px; border-radius: 50%; background: white; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid #e5e7eb; display: none; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s;">
                        <?php gufte_icon("chevron-left", "w-6 h-6"); ?>
                    </button>
                    <button type="button" class="custom-slider-next" onclick="scrollSlider('italian-slider-custom', 1)" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); z-index: 10; width: 48px; height: 48px; border-radius: 50%; background: white; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid #e5e7eb; display: none; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s;">
                        <?php gufte_icon("chevron-right", "w-6 h-6"); ?>
                    </button>
                </div>
                <script>
                (function() {
                    const container = document.querySelector('#content-original-italian .custom-slider-container');
                    if (!container) return;
                    container.id = 'italian-slider-custom';
                    const wrapper = container.querySelector('.custom-slider-wrapper');
                    const prevBtn = container.querySelector('.custom-slider-prev');
                    const nextBtn = container.querySelector('.custom-slider-next');

                    function updateButtons() {
                        if (!wrapper) return;
                        const isAtStart = wrapper.scrollLeft <= 10;
                        const isAtEnd = wrapper.scrollLeft >= wrapper.scrollWidth - wrapper.clientWidth - 10;
                        if (prevBtn) prevBtn.style.display = isAtStart ? 'none' : 'flex';
                        if (nextBtn) nextBtn.style.display = isAtEnd ? 'none' : 'flex';
                    }

                    if (wrapper) {
                        wrapper.addEventListener('scroll', updateButtons);
                        updateButtons();
                    }
                })();
                </script>
            </div>

            <!-- Korean Tab Content -->
            <div id="content-original-korean" class="tab-content hidden">
                <div class="custom-slider-container" style="position: relative; width: 100%; overflow: hidden;">
                    <div class="custom-slider-wrapper" style="display: flex; align-items: flex-start; overflow-x: auto; scroll-snap-type: x mandatory; scroll-behavior: smooth; -webkit-overflow-scrolling: touch; scrollbar-width: none; -ms-overflow-style: none; gap: 0;">
                        <?php if (!empty($korean_post_ids)) :
                            $korean_query = new WP_Query(array(
                                'post_type' => 'lyrics',
                                'post__in' => $korean_post_ids,
                                'posts_per_page' => 10,
                                'post_status' => 'publish',
                                'orderby' => 'post__in'
                            ));
                            while ($korean_query->have_posts()) : $korean_query->the_post(); ?>
                                <div class="custom-slide" style="flex: 0 0 auto; width: 280px; scroll-snap-align: start; padding: 8px; box-sizing: border-box; align-self: flex-start;">
                                    <div style="height: 100%;">
                                        <?php arcuras_lyrics_card(get_the_ID(), array('card_type' => 'compact')); ?>
                                    </div>
                                </div>
                            <?php endwhile;
                            wp_reset_postdata();
                        endif; ?>

                        <!-- Discover Card -->
                        <div class="custom-slide" style="flex: 0 0 auto; width: 280px; scroll-snap-align: start; padding: 8px; box-sizing: border-box; align-self: flex-start;">
                            <?php
                            $korean_term = get_term_by('slug', 'korean', 'original_language');
                            $korean_url = $korean_term ? get_term_link($korean_term) : home_url('/lyrics/original/korean/');
                            ?>
                            <a href="<?php echo esc_url($korean_url); ?>" style="display: block; height: 100%; text-decoration: none;">
                                <div style="height: 100%; min-height: 300px; background: linear-gradient(to bottom right, #ec4899, #e11d48); border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); overflow: hidden; border: 1px solid #e11d48; transition: all 0.3s; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1.5rem; text-align: center; color: white;">
                                    <div style="font-size: 4rem; margin-bottom: 1rem;">
                                        <?php gufte_icon("music-note", "w-16 h-16 mx-auto"); ?>
                                    </div>
                                    <h3 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 0.5rem; color: white;"><?php esc_html_e('Discover Korean Songs', 'gufte'); ?></h3>
                                    <p style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 1rem; color: white;"><?php esc_html_e('Explore all lyrics in original Korean language', 'gufte'); ?></p>
                                    <span style="display: inline-flex; align-items: center; font-size: 0.875rem; font-weight: 600; color: white;">
                                        <?php esc_html_e('View All', 'gufte'); ?>
                                        <span style="margin-left: 0.5rem;">→</span>
                                    </span>
                                </div>
                            </a>
                        </div>
                    </div>

                    <button type="button" class="custom-slider-prev" onclick="scrollSlider('korean-slider-custom', -1)" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); z-index: 10; width: 48px; height: 48px; border-radius: 50%; background: white; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid #e5e7eb; display: none; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s;">
                        <?php gufte_icon("chevron-left", "w-6 h-6"); ?>
                    </button>
                    <button type="button" class="custom-slider-next" onclick="scrollSlider('korean-slider-custom', 1)" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); z-index: 10; width: 48px; height: 48px; border-radius: 50%; background: white; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid #e5e7eb; display: none; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s;">
                        <?php gufte_icon("chevron-right", "w-6 h-6"); ?>
                    </button>
                </div>
                <script>
                (function() {
                    const container = document.querySelector('#content-original-korean .custom-slider-container');
                    if (!container) return;
                    container.id = 'korean-slider-custom';
                    const wrapper = container.querySelector('.custom-slider-wrapper');
                    const prevBtn = container.querySelector('.custom-slider-prev');
                    const nextBtn = container.querySelector('.custom-slider-next');

                    function updateButtons() {
                        if (!wrapper) return;
                        const isAtStart = wrapper.scrollLeft <= 10;
                        const isAtEnd = wrapper.scrollLeft >= wrapper.scrollWidth - wrapper.clientWidth - 10;
                        if (prevBtn) prevBtn.style.display = isAtStart ? 'none' : 'flex';
                        if (nextBtn) nextBtn.style.display = isAtEnd ? 'none' : 'flex';
                    }

                    if (wrapper) {
                        wrapper.addEventListener('scroll', updateButtons);
                        updateButtons();
                    }
                })();
                </script>
            </div>

            <!-- Japanese Tab Content -->
            <div id="content-original-japanese" class="tab-content hidden">
                <div class="custom-slider-container" style="position: relative; width: 100%; overflow: hidden;">
                    <div class="custom-slider-wrapper" style="display: flex; align-items: flex-start; overflow-x: auto; scroll-snap-type: x mandatory; scroll-behavior: smooth; -webkit-overflow-scrolling: touch; scrollbar-width: none; -ms-overflow-style: none; gap: 0;">
                        <?php if (!empty($japanese_post_ids)) :
                            $japanese_query = new WP_Query(array(
                                'post_type' => 'lyrics',
                                'post__in' => $japanese_post_ids,
                                'posts_per_page' => 10,
                                'post_status' => 'publish',
                                'orderby' => 'post__in'
                            ));
                            while ($japanese_query->have_posts()) : $japanese_query->the_post(); ?>
                                <div class="custom-slide" style="flex: 0 0 auto; width: 280px; scroll-snap-align: start; padding: 8px; box-sizing: border-box; align-self: flex-start;">
                                    <div style="height: 100%;">
                                        <?php arcuras_lyrics_card(get_the_ID(), array('card_type' => 'compact')); ?>
                                    </div>
                                </div>
                            <?php endwhile;
                            wp_reset_postdata();
                        endif; ?>

                        <!-- Discover Card -->
                        <div class="custom-slide" style="flex: 0 0 auto; width: 280px; scroll-snap-align: start; padding: 8px; box-sizing: border-box; align-self: flex-start;">
                            <?php
                            $japanese_term = get_term_by('slug', 'japanese', 'original_language');
                            $japanese_url = $japanese_term ? get_term_link($japanese_term) : home_url('/lyrics/original/japanese/');
                            ?>
                            <a href="<?php echo esc_url($japanese_url); ?>" style="display: block; height: 100%; text-decoration: none;">
                                <div style="height: 100%; min-height: 300px; background: linear-gradient(to bottom right, #a855f7, #7c3aed); border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); overflow: hidden; border: 1px solid #7c3aed; transition: all 0.3s; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1.5rem; text-align: center; color: white;">
                                    <div style="font-size: 4rem; margin-bottom: 1rem;">
                                        <?php gufte_icon("music-note", "w-16 h-16 mx-auto"); ?>
                                    </div>
                                    <h3 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 0.5rem; color: white;"><?php esc_html_e('Discover Japanese Songs', 'gufte'); ?></h3>
                                    <p style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 1rem; color: white;"><?php esc_html_e('Explore all lyrics in original Japanese language', 'gufte'); ?></p>
                                    <span style="display: inline-flex; align-items: center; font-size: 0.875rem; font-weight: 600; color: white;">
                                        <?php esc_html_e('View All', 'gufte'); ?>
                                        <span style="margin-left: 0.5rem;">→</span>
                                    </span>
                                </div>
                            </a>
                        </div>
                    </div>

                    <button type="button" class="custom-slider-prev" onclick="scrollSlider('japanese-slider-custom', -1)" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); z-index: 10; width: 48px; height: 48px; border-radius: 50%; background: white; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid #e5e7eb; display: none; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s;">
                        <?php gufte_icon("chevron-left", "w-6 h-6"); ?>
                    </button>
                    <button type="button" class="custom-slider-next" onclick="scrollSlider('japanese-slider-custom', 1)" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); z-index: 10; width: 48px; height: 48px; border-radius: 50%; background: white; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid #e5e7eb; display: none; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s;">
                        <?php gufte_icon("chevron-right", "w-6 h-6"); ?>
                    </button>
                </div>
                <script>
                (function() {
                    const container = document.querySelector('#content-original-japanese .custom-slider-container');
                    if (!container) return;
                    container.id = 'japanese-slider-custom';
                    const wrapper = container.querySelector('.custom-slider-wrapper');
                    const prevBtn = container.querySelector('.custom-slider-prev');
                    const nextBtn = container.querySelector('.custom-slider-next');

                    function updateButtons() {
                        if (!wrapper) return;
                        const isAtStart = wrapper.scrollLeft <= 10;
                        const isAtEnd = wrapper.scrollLeft >= wrapper.scrollWidth - wrapper.clientWidth - 10;
                        if (prevBtn) prevBtn.style.display = isAtStart ? 'none' : 'flex';
                        if (nextBtn) nextBtn.style.display = isAtEnd ? 'none' : 'flex';
                    }

                    if (wrapper) {
                        wrapper.addEventListener('scroll', updateButtons);
                        updateButtons();
                    }
                })();
                </script>
            </div>
        </div>

        <!-- Translated Lyrics Section -->
        <div id="translated-lyrics" class="translated-lyrics mb-12 md:mb-16 relative">
            <div class="absolute -top-20 right-20 w-80 h-80 bg-purple-200/20 rounded-full blur-3xl -z-10"></div>

            <?php
            // Section Header
            set_query_var('section_title', __('Translated Lyrics', 'gufte'));
            set_query_var('section_icon', 'translate');
            set_query_var('section_link_url', home_url('/lyrics/translation/'));
            set_query_var('section_link_text', __('View All', 'gufte'));
            get_template_part('template-parts/components/section-header');
            ?>

            <?php
            // Get posts with translations (has at least one translation)
            $translated_post_ids = array();
            foreach ($all_posts as $post) {
                $langs = gufte_get_lyrics_languages($post->post_content);
                if (!empty($langs['translations']) && count($langs['translations']) > 0) {
                    $translated_post_ids[] = $post->ID;
                    if (count($translated_post_ids) >= 10) break;
                }
            }

            if (!empty($translated_post_ids)) :
                arcuras_lyrics_slider(
                    array(
                        'post_type' => 'lyrics',
                        'post__in' => $translated_post_ids,
                        'posts_per_page' => 10,
                        'post_status' => 'publish',
                        'orderby' => 'post__in'
                    ),
                    array(
                        'slider_id' => 'translated-slider',
                        'show_navigation' => true,
                        'show_pagination' => false,
                        'card_type' => 'compact'
                    )
                );
            endif;
            ?>
        </div>

        <script>
        function switchTab(group, tab) {
            // Get all tabs and contents for this group
            const tabButtons = document.querySelectorAll(`[id^="tab-${group}-"]`);
            const tabContents = document.querySelectorAll(`[id^="content-${group}-"]`);

            // Remove active state from all tabs
            tabButtons.forEach(btn => {
                btn.classList.remove('border-primary-600', 'text-primary-600');
                btn.classList.add('border-transparent', 'text-gray-600');
            });

            // Hide all tab contents
            tabContents.forEach(content => {
                content.classList.add('hidden');
            });

            // Activate clicked tab
            const activeTab = document.getElementById(`tab-${group}-${tab}`);
            if (activeTab) {
                activeTab.classList.remove('border-transparent', 'text-gray-600');
                activeTab.classList.add('border-primary-600', 'text-primary-600');
            }

            // Show selected content
            const activeContent = document.getElementById(`content-${group}-${tab}`);
            if (activeContent) {
                activeContent.classList.remove('hidden');
            }
        }

        function scrollSlider(sliderId, direction) {
            const container = document.getElementById(sliderId);
            if (!container) return;

            const wrapper = container.querySelector('.custom-slider-wrapper');
            if (!wrapper) return;

            const slideWidth = wrapper.querySelector('.custom-slide')?.offsetWidth || 0;
            const scrollAmount = slideWidth * 2 * direction;

            wrapper.scrollBy({
                left: scrollAmount,
                behavior: 'smooth'
            });
        }
        </script>

        <!-- Latest Posts with Compact Grid -->
        <div class="latest-posts mt-12 mb-12 md:mb-16 section-animate relative">
            <div class="absolute -top-20 left-20 w-80 h-80 bg-purple-200/20 rounded-full blur-3xl -z-10"></div>

            <?php
            // Blog arşiv sayfası için URL
            $blog_page_id = get_option('page_for_posts');
            $lyrics_archive_url = $blog_page_id ? get_permalink($blog_page_id) : home_url('/lyrics/');

            // Section Header
            set_query_var('section_title', __('Latest Lyrics', 'gufte'));
            set_query_var('section_icon', 'clock');
            set_query_var('section_link_url', $lyrics_archive_url);
            set_query_var('section_link_text', __('View All', 'gufte'));
            get_template_part('template-parts/components/section-header');
            ?>

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