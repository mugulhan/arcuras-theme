<?php
/**
 * The sidebar containing the latest lyrics and categories
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Gufte
 */
?>

<aside id="secondary" class="widget-area bg-white p-6 rounded-lg shadow-lg border border-gray-200">
    
    <!-- Latest Lyrics Section -->
    <div class="latest-lyrics mb-8">
        <h2 class="widget-title text-xl font-bold text-gray-800 flex items-center mb-4">
            <span class="iconify mr-3 text-primary-400 text-2xl" data-icon="mdi:playlist-music"></span>
            Latest Lyrics
        </h2>
        
        <?php
        // Get the latest 5 posts
        $latest_lyrics = new WP_Query(array(
            'posts_per_page' => 5,
            'post_status' => 'publish'
        ));
        
        if ($latest_lyrics->have_posts()) :
        ?>
            <ul class="latest-lyrics-list divide-y divide-gray-200">
                <?php 
                while ($latest_lyrics->have_posts()) :
                    $latest_lyrics->the_post();
                    
                    // Get translation count
                    $raw_content = get_the_content();
                    $lyrics_languages = array('original' => '', 'translations' => array());
                    
                    if (function_exists('gufte_get_lyrics_languages')) {
                        $lyrics_languages = gufte_get_lyrics_languages($raw_content);
                    }
                    
                    $translation_count = !empty($lyrics_languages['translations']) ? count($lyrics_languages['translations']) : 0;
                    
                    // Get singer info
                    $singers = get_the_terms(get_the_ID(), 'singer');
                    $singer = !empty($singers) && !is_wp_error($singers) ? reset($singers) : null;
                ?>
                    <li class="py-3 first:pt-0 last:pb-0">
                        <a href="<?php the_permalink(); ?>" class="flex items-start gap-3 group">
                            <div class="flex-shrink-0 relative">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php the_post_thumbnail('thumbnail', array('class' => 'w-14 h-14 object-cover rounded-md')); ?>
                                <?php else : ?>
                                    <div class="w-14 h-14 bg-gray-200 flex items-center justify-center rounded-md">
                                        <span class="iconify text-xl text-gray-500" data-icon="mdi:music-note"></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($translation_count > 0) : ?>
                                <div class="absolute -top-2 -right-2 bg-primary-500/80 backdrop-blur-sm text-white text-xs font-bold px-1.5 py-0.5 rounded-full flex items-center">
                                    <span class="iconify mr-0.5" data-icon="mdi:translate"></span>
                                    <?php echo esc_html($translation_count); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex-grow min-w-0">
                                <h3 class="text-gray-800 font-medium group-hover:text-primary-600 transition-colors duration-300 line-clamp-2">
                                    <?php the_title(); ?>
                                </h3>
                                
                                <?php if ($singer) : ?>
                                <div class="mt-1 text-sm text-primary-400 flex items-center">
                                    <span class="iconify mr-1" data-icon="mdi:microphone"></span>
                                    <span class="truncate"><?php echo esc_html($singer->name); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </a>
                    </li>
                <?php 
                endwhile;
                wp_reset_postdata();
                ?>
            </ul>
            
            <div class="mt-4">
                <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts'))); ?>" class="inline-flex items-center text-sm text-primary-400 hover:text-primary-300 transition-colors duration-300">
                    View All Lyrics
                    <span class="iconify ml-1" data-icon="mdi:arrow-right"></span>
                </a>
            </div>
        <?php else : ?>
            <p class="text-gray-500">No lyrics have been added yet.</p>
        <?php endif; ?>
    </div>
    
<!-- Categories Section -->
<div class="categories-widget mb-8">
    <h2 class="widget-title text-xl font-bold text-gray-800 flex items-center mb-4">
        <span class="iconify mr-3 text-primary-400 text-2xl" data-icon="mdi:folder"></span>
        Categories
    </h2>
    
    <?php
    $categories = get_categories(array(
        'orderby' => 'count',
        'order' => 'DESC',
        'hide_empty' => true
    ));
    
    if (!empty($categories)) :
    ?>
        <ul class="categories-list space-y-2">
            <?php foreach ($categories as $category) : ?>
                <li>
                    <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>" class="flex items-center justify-between py-2 px-3 rounded-lg bg-gray-100 hover:bg-gray-200 transition-colors duration-300 group">
                        <span class="text-gray-700 group-hover:text-gray-900 transition-colors duration-300 flex items-center">
                            <span class="iconify mr-2 text-primary-400" data-icon="mdi:folder"></span>
                            <?php echo esc_html($category->name); ?>
                        </span>
                        <span class="bg-primary-500/20 text-primary-300 text-xs font-semibold px-2 py-1 rounded-full">
                            <?php echo number_format_i18n($category->count); ?>
                        </span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <p class="text-gray-500">No categories found.</p>
    <?php endif; ?>
</div>
    
</aside><!-- #secondary -->