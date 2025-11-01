<?php
/**
 * Template Name: Lyrics Translation Category
 *
 * Main archive page for all translated languages
 *
 * @package Arcuras
 */

get_header();
?>

<div class="site-content-wrapper flex flex-col md:flex-row">

    <?php get_template_part('template-parts/arcuras-sidebar'); ?>

    <main id="primary" class="site-main flex-1 bg-white overflow-x-hidden">

        <?php
        // Breadcrumb
        set_query_var('breadcrumb_items', array(
            array('label' => 'Home', 'url' => home_url('/')),
            array('label' => __('Translations', 'gufte'))
        ));
        get_template_part('template-parts/page-components/page-breadcrumb');

        // Hero
        set_query_var('hero_title', __('Translations', 'gufte'));
        set_query_var('hero_icon', 'translate');
        set_query_var('hero_description', __('Explore translated lyrics and discover songs in different languages. Find your favorite songs translated from their original language.', 'gufte'));
        set_query_var('hero_meta', array());
        get_template_part('template-parts/page-components/page-hero');
        ?>

        <div class="px-4 sm:px-6 lg:px-8 py-6">

        <?php
        // Get all translated languages with posts
        $languages = get_terms(array(
            'taxonomy' => 'translated_language',
            'hide_empty' => true,
            'orderby' => 'count',
            'order' => 'DESC',
        ));

        if (!empty($languages) && !is_wp_error($languages)) :
        ?>
            <!-- Languages Grid -->
            <div class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2 sm:gap-3 mb-12 sm:mb-16">
                <?php foreach ($languages as $language) : ?>
                    <a href="<?php echo esc_url(get_term_link($language)); ?>"
                       class="group flex items-center justify-between gap-2 sm:gap-3 px-3 sm:px-4 py-2.5 sm:py-3 bg-white rounded-lg border border-gray-200 hover:border-purple-400 hover:shadow-md transition-all duration-200">
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-gray-900 group-hover:text-purple-600 transition-colors text-xs sm:text-sm truncate">
                                <?php echo esc_html($language->name); ?>
                            </div>
                            <div class="text-xs text-gray-500">
                                <?php echo number_format($language->count); ?>
                            </div>
                        </div>
                        <svg class="w-3 h-3 text-gray-400 group-hover:text-purple-600 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Recent Translated Lyrics by Language -->
            <section class="mt-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-3xl font-bold text-gray-900">
                        Recent Translations
                    </h2>
                </div>

                <?php foreach (array_slice($languages, 0, 5) as $language) :
                    // Get recent posts for this language
                    $recent_posts = get_posts(array(
                        'post_type' => 'lyrics',
                        'posts_per_page' => 7,
                        'post_status' => 'publish',
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'translated_language',
                                'field' => 'term_id',
                                'terms' => $language->term_id,
                            )
                        )
                    ));

                    if (empty($recent_posts)) continue;

                    $post_ids = wp_list_pluck($recent_posts, 'ID');
                ?>
                    <div class="mb-6 md:mb-8 relative">
                        <!-- Language Header -->
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-2xl font-bold text-gray-900">
                                <?php echo esc_html($language->name); ?>
                            </h3>
                            <a href="<?php echo esc_url(get_term_link($language)); ?>"
                               class="text-sm font-medium text-purple-600 hover:text-purple-700 flex items-center gap-1">
                                View All
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>

                        <!-- Slider -->
                        <?php
                        arcuras_lyrics_slider(
                            array(
                                'post_type' => 'lyrics',
                                'post__in' => $post_ids,
                                'posts_per_page' => 7,
                                'orderby' => 'post__in',
                            ),
                            array(
                                'slider_id' => 'translated-' . $language->slug . '-slider',
                                'show_navigation' => true,
                                'show_pagination' => false,
                                'card_type' => 'hero',
                            )
                        );
                        ?>
                    </div>
                <?php endforeach; ?>
            </section>

        <?php else : ?>
            <!-- No Languages Found -->
            <div class="text-center py-20">
                <div class="text-6xl mb-6">🌍</div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">
                    No Translated Lyrics Yet
                </h2>
                <p class="text-gray-600 mb-8">
                    Check back later for translated lyrics!
                </p>
                <a href="<?php echo home_url('/'); ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Back to Home
                </a>
            </div>
        <?php endif; ?>

        </div><!-- .px-4 -->

    </main>

</div><!-- .site-content-wrapper -->

<?php
get_footer();
