<?php
/**
 * Template Helper Functions
 *
 * @package Gufte
 * @since 1.9.5
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display lyrics card
 *
 * @param int|WP_Post $post           Post object or ID
 * @param array       $args           Optional arguments
 *   @type string     $card_type      Card type: 'hero', 'compact', 'grid' (default: 'hero')
 *   @type bool       $show_singer    Show singer info (default: true)
 *   @type bool       $show_languages Show language badges (default: true)
 *   @type bool       $show_cta       Show "View Lyrics" button (default: true)
 */
if (!function_exists('arcuras_lyrics_card')) {
    function arcuras_lyrics_card($post = null, $args = array()) {
    $post = get_post($post);

    if (!$post) {
        return;
    }

    // Default arguments
    $defaults = array(
        'card_type' => 'hero',
        'show_singer' => true,
        'show_languages' => true,
        'show_cta' => true,
    );

    $args = wp_parse_args($args, $defaults);

    // Extract variables for template
    $post_id = $post->ID;
    $card_type = $args['card_type'];
    $show_singer = $args['show_singer'];
    $show_languages = $args['show_languages'];
    $show_cta = $args['show_cta'];

    // Load template
    get_template_part('template-parts/content', 'lyrics-card', compact(
        'post_id',
        'card_type',
        'show_singer',
        'show_languages',
        'show_cta'
    ));
    }
}

/**
 * Display lyrics grid with optional filters
 *
 * @param array $query_args WP_Query arguments
 * @param array $display_args Display options
 *   @type string $card_type      Card type (default: 'compact')
 *   @type string $columns        Grid columns: '2', '3', '4', '5', '6' (default: '6')
 *   @type bool   $show_singer    Show singer (default: true)
 *   @type bool   $show_languages Show languages (default: true)
 *   @type bool   $show_cta       Show CTA (default: false for compact grids)
 */
if (!function_exists('arcuras_lyrics_grid')) {
    function arcuras_lyrics_grid($query_args = array(), $display_args = array()) {
    // Default query args
    $default_query = array(
        'post_type' => 'lyrics',
        'post_status' => 'publish',
        'posts_per_page' => 12,
    );

    $query_args = wp_parse_args($query_args, $default_query);

    // Default display args
    $default_display = array(
        'card_type' => 'compact',
        'columns' => '6',
        'show_singer' => true,
        'show_languages' => true,
        'show_cta' => false,
    );

    $display_args = wp_parse_args($display_args, $default_display);

    // Column classes
    $column_classes = array(
        '2' => 'grid grid-cols-2 gap-4',
        '3' => 'grid grid-cols-2 sm:grid-cols-3 gap-4',
        '4' => 'grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4',
        '5' => 'grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4',
        '6' => 'grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4',
    );

    $grid_class = isset($column_classes[$display_args['columns']])
        ? $column_classes[$display_args['columns']]
        : $column_classes['6'];

    // Run query
    $lyrics_query = new WP_Query($query_args);

    if (!$lyrics_query->have_posts()) {
        echo '<div class="bg-white rounded-xl border border-gray-200 p-8 text-center">';
        echo '<span class="block mb-3 text-6xl">🎵</span>';
        echo '<p class="text-gray-600">' . esc_html__('No lyrics found.', 'gufte') . '</p>';
        echo '</div>';
        return;
    }

    echo '<div class="' . esc_attr($grid_class) . '">';

    while ($lyrics_query->have_posts()) {
        $lyrics_query->the_post();

        arcuras_lyrics_card(get_the_ID(), array(
            'card_type' => $display_args['card_type'],
            'show_singer' => $display_args['show_singer'],
            'show_languages' => $display_args['show_languages'],
            'show_cta' => $display_args['show_cta'],
        ));
    }

    echo '</div>';

    wp_reset_postdata();
    }
}

/**
 * Display lyrics slider (Swiper)
 *
 * @param array $query_args WP_Query arguments
 * @param array $slider_args Slider options
 */
if (!function_exists('arcuras_lyrics_slider')) {
    function arcuras_lyrics_slider($query_args = array(), $slider_args = array()) {
    // Default query args
    $default_query = array(
        'post_type' => 'lyrics',
        'post_status' => 'publish',
        'posts_per_page' => 9,
    );

    $query_args = wp_parse_args($query_args, $default_query);

    // Default slider args
    $default_slider = array(
        'slider_id' => 'lyrics-slider-' . uniqid(),
        'show_navigation' => true,
        'show_pagination' => true,
        'card_type' => 'hero',
    );

    $slider_args = wp_parse_args($slider_args, $default_slider);

    $lyrics_query = new WP_Query($query_args);

    if (!$lyrics_query->have_posts()) {
        return;
    }

    $slider_id = $slider_args['slider_id'];
    ?>
    <div class="custom-slider-container" id="<?php echo esc_attr($slider_id); ?>" style="position: relative; width: 100%; overflow: hidden;">
        <!-- Slider Wrapper -->
        <div class="custom-slider-wrapper" style="display: flex; align-items: flex-start; overflow-x: auto; scroll-snap-type: x mandatory; scroll-behavior: smooth; -webkit-overflow-scrolling: touch; scrollbar-width: none; -ms-overflow-style: none; gap: 0;">
            <?php while ($lyrics_query->have_posts()) : $lyrics_query->the_post(); ?>
            <div class="custom-slide" style="flex: 0 0 auto; width: 280px; scroll-snap-align: start; padding: 8px; box-sizing: border-box; align-self: flex-start;">
                <div style="height: 100%;">
                    <?php arcuras_lyrics_card(get_the_ID(), array('card_type' => $slider_args['card_type'])); ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <?php if ($slider_args['show_navigation']) : ?>
        <!-- Navigation Buttons -->
        <button type="button" class="custom-slider-prev" onclick="scrollSlider('<?php echo esc_js($slider_id); ?>', -1)" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); z-index: 10; width: 48px; height: 48px; border-radius: 50%; background: white; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid #e5e7eb; display: none; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s;">
            <?php gufte_icon("chevron-left", "w-6 h-6"); ?>
        </button>
        <button type="button" class="custom-slider-next" onclick="scrollSlider('<?php echo esc_js($slider_id); ?>', 1)" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); z-index: 10; width: 48px; height: 48px; border-radius: 50%; background: white; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid #e5e7eb; display: none; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s;">
            <?php gufte_icon("chevron-right", "w-6 h-6"); ?>
        </button>
        <?php endif; ?>
    </div>

    <script>
    (function() {
        const container = document.getElementById('<?php echo esc_js($slider_id); ?>');
        if (!container) return;

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

    <style>
    /* Hide scrollbar */
    .custom-slider-wrapper::-webkit-scrollbar {
        display: none;
    }
    .custom-slider-wrapper {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* Button hover effects */
    .custom-slider-prev:hover,
    .custom-slider-next:hover {
        background: #667eea !important;
    }
    .custom-slider-prev:hover svg,
    .custom-slider-next:hover svg {
        color: white !important;
    }

    /* Hide buttons on mobile/tablet - show only on desktop */
    .custom-slider-prev,
    .custom-slider-next {
        display: none !important;
    }
    /* Responsive slide heights - width is fixed at 280px via inline style */
    #<?php echo esc_attr($slider_id); ?> .custom-slide > div {
        height: auto !important;
    }

    #<?php echo esc_attr($slider_id); ?> .hero-card {
        height: auto !important;
    }

    /* Desktop optimization */
    @media (min-width: 1024px) {
        #<?php echo esc_attr($slider_id); ?> .custom-slider-wrapper {
            padding: 16px 0;
        }

        #<?php echo esc_attr($slider_id); ?> .custom-slider-prev,
        #<?php echo esc_attr($slider_id); ?> .custom-slider-next {
            width: 56px !important;
            height: 56px !important;
            display: flex !important;
        }
    }
    </style>
    <?php

    wp_reset_postdata();
    }
}

/**
 * Display genre card
 *
 * @param int|WP_Term $genre Genre term object or ID
 * @param array       $args  Optional arguments
 */
if (!function_exists('arcuras_genre_card')) {
    function arcuras_genre_card($genre = null, $args = array()) {
        // Handle both WP_Term and stdClass genre objects
        if (is_numeric($genre)) {
            // If numeric, try to get as term
            $genre = get_term($genre, 'genre');
        } elseif (!is_object($genre)) {
            // If not an object, bail
            return;
        }

        if (!$genre || is_wp_error($genre)) {
            return;
        }

        // For stdClass objects (from meta query), count is already included
        // For WP_Term objects, get from term meta
        if ($genre instanceof WP_Term) {
            $lyrics_count = get_term_meta($genre->term_id, 'lyrics_count', true);
            if (!$lyrics_count) {
                // Count if not cached
                $lyrics_count = wp_count_posts_by_term($genre->term_id, 'lyrics', 'genre');
                update_term_meta($genre->term_id, 'lyrics_count', $lyrics_count);
            }
        } else {
            // stdClass object from arcuras_genre_grid() already has count
            $lyrics_count = isset($genre->count) ? $genre->count : 0;
        }

        // Default arguments
        $defaults = array(
            'card_style' => 'default',
        );

        $args = wp_parse_args($args, $defaults);
        $card_style = $args['card_style'];

        // Get featured images from this genre (cached, max 4 for performance)
        $genre_images = array();

        if (isset($genre->slug)) {
            // Check cache first (1 hour)
            $cache_key = 'genre_images_' . $genre->slug;
            $genre_images = get_transient($cache_key);

            if (false === $genre_images) {
                global $wpdb;

                // Optimized query - limit to 3 images for performance
                $image_query = $wpdb->get_results($wpdb->prepare("
                    SELECT p.ID
                    FROM {$wpdb->posts} p
                    INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                    INNER JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id
                    WHERE pm.meta_key = '_music_genre'
                    AND pm.meta_value = %s
                    AND p.post_type = 'lyrics'
                    AND p.post_status = 'publish'
                    AND pm2.meta_key = '_thumbnail_id'
                    AND pm2.meta_value != ''
                    ORDER BY p.post_date DESC
                    LIMIT 3
                ", $genre->name));

                $genre_images = array();
                foreach ($image_query as $img) {
                    $thumb_url = get_the_post_thumbnail_url($img->ID, 'thumbnail'); // smaller size
                    if ($thumb_url) {
                        $genre_images[] = $thumb_url;
                    }
                }

                // Cache for 1 hour
                set_transient($cache_key, $genre_images, HOUR_IN_SECONDS);
            }
        }

        // Set variables for template
        set_query_var('genre', $genre);
        set_query_var('card_style', $card_style);
        set_query_var('lyrics_count', $lyrics_count);
        set_query_var('genre_images', $genre_images);

        // Load template
        get_template_part('template-parts/content', 'genre-card');
    }
}

/**
 * Display genre grid with custom slider
 *
 * @param array $args Display options
 */
if (!function_exists('arcuras_genre_grid')) {
    function arcuras_genre_grid($args = array()) {
        global $wpdb;

        // Default args
        $defaults = array(
            'slider_id' => 'genre-slider-' . uniqid(),
            'title' => __('Featured Genres', 'gufte'),
            'icon' => '',
            'show_navigation' => true,
            'limit' => 10,
        );

        $args = wp_parse_args($args, $defaults);

        // Get unique genres from post meta with count
        $genres_data = $wpdb->get_results($wpdb->prepare("
            SELECT pm.meta_value as name, COUNT(*) as count
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE pm.meta_key = '_music_genre'
            AND pm.meta_value != ''
            AND p.post_type = 'lyrics'
            AND p.post_status = 'publish'
            GROUP BY pm.meta_value
            ORDER BY count DESC
            LIMIT %d
        ", $args['limit']));

        if (empty($genres_data)) {
            echo '<div style="padding: 20px; background: #f0f0f0; border-radius: 8px; text-align: center; margin: 20px 0;">';
            echo '<p style="margin: 0; color: #666;">📊 No genres found. Run "Fetch Release Date & Genre" from admin panel.</p>';
            echo '</div>';
            return;
        }

        // Convert to objects similar to WP_Term
        $genres = array();
        foreach ($genres_data as $index => $genre_data) {
            $genre = new stdClass();
            $genre->term_id = $index + 1; // Fake ID for gradient selection
            $genre->name = $genre_data->name;
            $genre->count = $genre_data->count;
            $genre->slug = sanitize_title($genre_data->name);
            $genres[] = $genre;
        }

        $slider_id = $args['slider_id'];
        ?>
        <div class="genre-grid-section">
            <?php if ($args['title']) :
                // Use section-header component
                set_query_var('section_title', $args['title']);
                set_query_var('section_icon', $args['icon']);
                set_query_var('section_link_url', '');
                get_template_part('template-parts/components/section-header');
            endif; ?>

            <div class="custom-slider-container" id="<?php echo esc_attr($slider_id); ?>" style="position: relative; width: 100%; overflow: hidden;">
                <div class="custom-slider-wrapper" style="display: flex; overflow-x: auto; scroll-snap-type: x mandatory; scroll-behavior: smooth; -webkit-overflow-scrolling: touch; scrollbar-width: none; -ms-overflow-style: none; gap: 12px; padding: 8px 0;">
                    <?php foreach ($genres as $genre) : ?>
                    <div class="custom-slide" style="flex: 0 0 40%; scroll-snap-align: start; box-sizing: border-box;">
                        <?php arcuras_genre_card($genre); ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($args['show_navigation']) : ?>
                <button type="button" class="custom-slider-prev genre-nav-btn" onclick="scrollSlider('<?php echo esc_js($slider_id); ?>', -1)" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); z-index: 10; width: 48px; height: 48px; border-radius: 50%; background: white; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid #e5e7eb; display: none; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s;">
                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M15.41 16.58L10.83 12l4.58-4.59L14 6l-6 6l6 6l1.41-1.42z"></path></svg>
                </button>
                <button type="button" class="custom-slider-next genre-nav-btn" onclick="scrollSlider('<?php echo esc_js($slider_id); ?>', 1)" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); z-index: 10; width: 48px; height: 48px; border-radius: 50%; background: white; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid #e5e7eb; display: none; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s;">
                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M8.59 16.58L13.17 12L8.59 7.41L10 6l6 6l-6 6l-1.41-1.42z"></path></svg>
                </button>
                <?php endif; ?>
            </div>

            <style>
            /* Genre card slider responsive - matches lyrics cards exactly */
            #<?php echo esc_attr($slider_id); ?> .custom-slider-wrapper::-webkit-scrollbar { display: none; }

            /* Mobile: 2.5 cards */
            #<?php echo esc_attr($slider_id); ?> .custom-slide {
                flex: 0 0 40%;
                max-width: 400px;
            }

            /* Small tablet: 2.5 cards */
            @media (min-width: 640px) {
                #<?php echo esc_attr($slider_id); ?> .custom-slide {
                    flex: 0 0 40%;
                    max-width: 350px;
                }
            }

            /* Tablet: 3 cards */
            @media (min-width: 768px) {
                #<?php echo esc_attr($slider_id); ?> .custom-slide {
                    flex: 0 0 33.333%;
                    max-width: 320px;
                }
            }

            /* Desktop: 4 cards - same as lyrics */
            @media (min-width: 1024px) {
                #<?php echo esc_attr($slider_id); ?> .custom-slide {
                    flex: 0 0 25%;
                    max-width: 300px;
                }
                #<?php echo esc_attr($slider_id); ?> .genre-nav-btn { display: flex !important; }
            }

            /* Large desktop: 5 cards - same as lyrics */
            @media (min-width: 1280px) {
                #<?php echo esc_attr($slider_id); ?> .custom-slide {
                    flex: 0 0 20%;
                    max-width: 280px;
                }
            }

            /* Extra large: 6 cards - same as lyrics */
            @media (min-width: 1536px) {
                #<?php echo esc_attr($slider_id); ?> .custom-slide {
                    flex: 0 0 16.666%;
                    max-width: 280px;
                }
            }
            </style>
        </div>
        <?php
    }
}

/**
 * Helper function to count posts by term
 */
if (!function_exists('wp_count_posts_by_term')) {
    function wp_count_posts_by_term($term_id, $post_type = 'post', $taxonomy = 'category') {
        $query = new WP_Query(array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            'tax_query' => array(
                array(
                    'taxonomy' => $taxonomy,
                    'field' => 'term_id',
                    'terms' => $term_id,
                ),
            ),
            'fields' => 'ids',
            'posts_per_page' => -1,
        ));

        return $query->found_posts;
    }
}
