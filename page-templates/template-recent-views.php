<?php
/**
 * Template Name: Recent Views Page
 *
 * Displays the posts recently viewed by the logged-in user.
 * Uses the transient data saved by functions in header.php.
 * Includes the Arcuras Sidebar on the left.
 *
 * @package Gufte
 */

get_header();

// Ensure the function to get recent visits exists (defined in header.php or functions.php)
if (!function_exists('gufte_get_recent_visits')) {
    // Fallback function or error handling if needed
    function gufte_get_recent_visits() {
        // error_log('gufte_get_recent_visits function not found!'); // Optional logging
        return array(); // Return empty array if function doesn't exist
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

        <?php
        // Breadcrumb
        set_query_var('breadcrumb_items', array(
            array('label' => 'Home', 'url' => home_url('/')),
            array('label' => __('Recently Viewed', 'gufte'))
        ));
        get_template_part('template-parts/page-components/page-breadcrumb');

        // Hero
        if (have_posts()) : while (have_posts()) : the_post();
            $hero_description = get_the_content() ? get_the_content() : '';
        endwhile; endif;
        rewind_posts();

        set_query_var('hero_title', get_the_title());
        set_query_var('hero_icon', 'history');
        set_query_var('hero_description', $hero_description);
        set_query_var('hero_meta', array());
        get_template_part('template-parts/page-components/page-hero');
        ?>

        <div class="recent-views-content px-4 sm:px-6 lg:px-8 py-6">

            <?php if ( ! is_user_logged_in() ) : // Check if user is logged in ?>

                <div class="notice bg-yellow-50 border border-yellow-300 text-yellow-800 p-4 rounded-lg text-center">
                    <p><?php esc_html_e('You need to be logged in to see your recently viewed lyrics.', 'gufte'); ?></p>
                     <?php // Optional: Add a login link ?>
                     <p class="mt-2">
                         <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="inline-block px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-md text-sm font-medium transition-colors duration-300">
                             <?php esc_html_e('Log In', 'gufte'); ?>
                         </a>
                     </p>
                </div>

            <?php else : // User is logged in ?>

                <?php
                // Get the recently viewed post IDs from the transient
                $recent_visit_ids = gufte_get_recent_visits();

                if ( ! empty( $recent_visit_ids ) ) :

                    // Query the posts based on the retrieved IDs
                    $recent_views_query = new WP_Query( array(
                        'post_type'      => 'lyrics', // Lyrics custom post type
                        'posts_per_page' => count($recent_visit_ids), // Get all saved IDs (usually max 5)
                        'post__in'       => $recent_visit_ids,
                        'orderby'        => 'post__in', // Maintain the order from the transient (most recent first)
                        'ignore_sticky_posts' => 1,
                    ) );

                    if ( $recent_views_query->have_posts() ) :
                ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php while ( $recent_views_query->have_posts() ) : $recent_views_query->the_post(); ?>
                             <?php // Use a standard post card structure ?>
                             <article id="post-<?php the_ID(); ?>" <?php post_class('post-card bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-primary-300 flex flex-col'); ?>>
                                 <?php if (has_post_thumbnail()) : ?>
                                    <a href="<?php the_permalink(); ?>" class="block relative group h-40 overflow-hidden">
                                        <?php the_post_thumbnail('medium', array('class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300')); ?>
                                    </a>
                                 <?php else: ?>
                                     <a href="<?php the_permalink(); ?>" class="block relative group h-40 bg-gray-100 flex items-center justify-center">
                                         <span class="iconify text-4xl text-gray-300 group-hover:text-primary-400 transition-colors duration-300" data-icon="mdi:music-note"></span>
                                     </a>
                                 <?php endif; ?>
                                 <div class="p-4 flex-grow flex flex-col">
                                     <h2 class="text-base font-bold mb-2">
                                         <a href="<?php the_permalink(); ?>" class="text-gray-800 hover:text-primary-600 transition-colors duration-300 line-clamp-2">
                                             <?php the_title(); ?>
                                         </a>
                                     </h2>
                                     <?php // Singer Info ?>
                                      <?php
                                        if (taxonomy_exists('singer')) {
                                            $singers = get_the_terms(get_the_ID(), 'singer');
                                            if ($singers && !is_wp_error($singers)):
                                                $singer = reset($singers);
                                      ?>
                                      <div class="text-xs text-gray-500 mb-2 flex items-center">
                                           <span class="iconify mr-1" data-icon="mdi:microphone-variant"></span>
                                           <a href="<?php echo esc_url(get_term_link($singer)); ?>" class="hover:text-primary-500">
                                               <?php echo esc_html($singer->name); ?>
                                           </a>
                                      </div>
                                      <?php endif; } ?>

                                     <div class="mt-auto pt-3 border-t border-gray-100 flex justify-between items-center text-xs text-gray-500">
                                         <span class="publish-date flex items-center">
                                             <span class="iconify mr-1" data-icon="mdi:calendar-blank"></span>
                                             <time datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>"><?php echo esc_html(get_the_date()); ?></time>
                                         </span>
                                         <a href="<?php the_permalink(); ?>" class="read-more-button bg-primary-50 hover:bg-primary-100 text-primary-600 px-2.5 py-1 rounded-full inline-flex items-center transition-all duration-300 font-medium">
                                            <?php esc_html_e('View', 'gufte'); ?>
                                            <span class="iconify ml-1" data-icon="mdi:arrow-right"></span>
                                         </a>
                                     </div>
                                 </div>
                             </article>
                        <?php endwhile; ?>
                        <?php wp_reset_postdata(); // Reset post data after custom query ?>
                    </div><?php else : // WP_Query didn't find posts (maybe posts were deleted?) ?>
                        <div class="notice bg-blue-50 border border-blue-300 text-blue-800 p-4 rounded-lg text-center">
                            <p><?php esc_html_e('Could not retrieve recently viewed items. They might have been removed.', 'gufte'); ?></p>
                        </div>
                    <?php endif; // $recent_views_query->have_posts() ?>

                <?php else : // $recent_visit_ids is empty ?>
                     <div class="notice bg-blue-50 border border-blue-300 text-blue-800 p-4 rounded-lg text-center">
                         <p><?php esc_html_e('You haven\'t viewed any lyrics yet. Start exploring!', 'gufte'); ?></p>
                         <?php // Optional: Link to the homepage or main archive ?>
                          <p class="mt-2">
                             <a href="<?php echo esc_url(home_url('/')); ?>" class="inline-block px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-md text-sm font-medium transition-colors duration-300">
                                 <?php esc_html_e('Explore Lyrics', 'gufte'); ?>
                             </a>
                         </p>
                     </div>
                <?php endif; // ! empty( $recent_visit_ids ) ?>

            <?php endif; // is_user_logged_in() ?>

        </div><!-- .recent-views-content -->

    </main>
</div><!-- .site-content-wrapper -->

<?php
get_footer();