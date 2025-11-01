<?php
/**
 * Translated Language Taxonomy Archive Template
 * Display all lyrics translated to a specific language
 *
 * @package Arcuras
 */

get_header();

$current_term = get_queried_object();
$lang_info = arcuras_get_language_info_by_slug($current_term->slug);
$flag = $lang_info ? $lang_info['flag'] : '🌐';
?>

<div class="site-content-wrapper flex flex-col md:flex-row">

    <?php get_template_part('template-parts/arcuras-sidebar'); ?>

    <main id="primary" class="site-main flex-1 bg-white overflow-x-hidden">

        <?php
        // Breadcrumb
        set_query_var('breadcrumb_items', array(
            array('label' => 'Home', 'url' => home_url('/')),
            array('label' => __('Translations', 'gufte'), 'url' => home_url('/lyrics/translation/')),
            array('label' => $current_term->name)
        ));
        get_template_part('template-parts/page-components/page-breadcrumb');

        // Hero
        $hero_description = $current_term->description ? $current_term->description : sprintf(__('Browse all songs that have been translated to %s', 'gufte'), $current_term->name);

        set_query_var('hero_title', sprintf(__('%s Translations', 'gufte'), $current_term->name));
        set_query_var('hero_icon', 'translate');
        set_query_var('hero_description', $hero_description);
        set_query_var('hero_meta', array());
        get_template_part('template-parts/page-components/page-hero');
        ?>

        <div class="px-4 sm:px-6 lg:px-8 py-6">

        <?php if (have_posts()) : ?>
            <!-- Lyrics Grid -->
            <div class="archive-posts-grid grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                <?php while (have_posts()) : the_post();
                    $singers = get_the_terms(get_the_ID(), 'singer');
                    $singer_name = ($singers && !is_wp_error($singers)) ? $singers[0]->name : '';
                    $thumbnail = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                    ?>

                    <article class="compact-card group bg-white rounded-lg shadow-md border border-gray-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-gray-300" style="overflow: visible;">
                        <a href="<?php the_permalink(); ?>" class="block" style="text-decoration: none; overflow: hidden; border-radius: 0.5rem 0.5rem 0 0;">
                            <!-- Thumbnail -->
                            <div class="aspect-square bg-gradient-to-br from-gray-100 to-gray-200 relative overflow-hidden">
                                <?php if ($thumbnail) : ?>
                                    <img src="<?php echo esc_url($thumbnail); ?>"
                                         alt="<?php the_title_attribute(); ?>"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                <?php else : ?>
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-20 h-20 text-gray-400 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                                        </svg>
                                    </div>
                                <?php endif; ?>

                                <!-- Language Badge Overlay -->
                                <div style="position: absolute; top: 12px; right: 12px;">
                                    <span style="display: inline-block; padding: 6px 12px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(8px); border-radius: 6px; font-size: 13px; font-weight: 600; color: #374151; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                        <?php echo $flag; ?> Translation
                                    </span>
                                </div>
                            </div>
                        </a>

                        <!-- Content -->
                        <div class="p-4" style="overflow: visible;">
                            <a href="<?php the_permalink(); ?>" style="text-decoration: none;">
                                <h2 style="font-size: 16px; font-weight: 600; color: #111827; margin-bottom: 8px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    <?php the_title(); ?>
                                </h2>

                                <?php if ($singer_name) : ?>
                                    <p style="font-size: 13px; color: #6b7280; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        <?php echo esc_html($singer_name); ?>
                                    </p>
                                <?php endif; ?>
                            </a>

                            <!-- Original Languages for this post -->
                            <?php
                            $original_languages = wp_get_post_terms(get_the_ID(), 'original_language');
                            if ($original_languages && !is_wp_error($original_languages)) :
                                // Get the first original language
                                $first_original = $original_languages[0];
                                $first_lang_info = arcuras_get_language_info_by_slug($first_original->slug);
                                $first_flag = $first_lang_info ? $first_lang_info['flag'] : '🌐';
                                $original_url = get_the_permalink(get_the_ID());
                            ?>
                                <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e5e7eb;">
                                    <a href="<?php echo esc_url($original_url); ?>" onclick="event.stopPropagation();" style="display: inline-flex; align-items: center; gap: 6px; font-size: 11px; padding: 4px 8px; background: #f3f4f6; color: #374151; border-radius: 4px; text-decoration: none; font-weight: 500; transition: all 0.2s;" onmouseover="this.style.background='#e5e7eb';" onmouseout="this.style.background='#f3f4f6';">
                                        <?php gufte_icon('file-document', 'w-3.5 h-3.5'); ?>
                                        <span><?php echo esc_html($first_original->name); ?></span>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>

                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <?php
            the_posts_pagination(array(
                'mid_size' => 2,
                'prev_text' => '<svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg> Previous',
                'next_text' => 'Next <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>',
                'class' => 'flex justify-center gap-2',
            ));
            ?>

        <?php else : ?>

            <!-- No Posts Found -->
            <div class="text-center py-20">
                <div class="text-6xl mb-6">🎵</div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    No translations found in <?php echo esc_html($current_term->name); ?>
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mb-8">
                    Check back later for new translations!
                </p>
                <a href="<?php echo home_url('/'); ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Back to Home
                </a>
            </div>

        <?php endif; ?>

        <!-- Other Translated Languages Section -->
        <aside class="mt-16 pt-12 border-t border-gray-200 dark:border-gray-700">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 text-center">
                Browse Other Translation Languages
            </h3>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <?php
                $all_languages = get_terms(array(
                    'taxonomy' => 'translated_language',
                    'hide_empty' => true,
                    'exclude' => array($current_term->term_id),
                ));

                foreach ($all_languages as $language) :
                    $other_lang_info = arcuras_get_language_info_by_slug($language->slug);
                    $other_flag = $other_lang_info ? $other_lang_info['flag'] : '🌐';
                    ?>
                    <a href="<?php echo esc_url(get_term_link($language)); ?>"
                       class="group flex flex-col items-center gap-3 p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                        <span class="text-4xl group-hover:scale-110 transition-transform"><?php echo $other_flag; ?></span>
                        <div class="text-center">
                            <div class="font-medium text-gray-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
                                <?php echo esc_html($language->name); ?>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                <?php echo number_format($language->count); ?> songs
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </aside>

        </div><!-- .px-4 -->

    </main>

</div><!-- .site-content-wrapper -->

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
