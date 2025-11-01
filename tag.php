<?php
/**
 * The template for displaying tag archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package HealthCare
 */

get_header();

// Get current tag
$tag = get_queried_object();
$tag_id = $tag->term_id;
$tag_name = $tag->name;
$tag_description = $tag->description;
$tag_count = $tag->count;

// Get related icon based on tag name
$icon = 'mdi:tag'; // Default tag icon
$tag_name_lower = strtolower($tag_name);

// Common health-related terms that might be used as tags
$health_icons = array(
    // Nutrition related
    'food' => 'mdi:food-apple',
    'diet' => 'mdi:food-apple',
    'nutrition' => 'mdi:food-apple',
    'vitamin' => 'mdi:bottle-tonic-plus',
    'protein' => 'mdi:food-steak',
    'carb' => 'mdi:bread-slice',
    'fat' => 'mdi:oil',
    'sugar' => 'mdi:cube-outline',
    
    // Fitness related
    'exercise' => 'mdi:run',
    'workout' => 'mdi:dumbbell',
    'gym' => 'mdi:dumbbell',
    'fitness' => 'mdi:run',
    'strength' => 'mdi:arm-flex',
    'cardio' => 'mdi:heart-pulse',
    'yoga' => 'mdi:yoga',
    'stretching' => 'mdi:human-handsup',
    
    // Mental health
    'stress' => 'mdi:brain',
    'anxiety' => 'mdi:head-question',
    'depression' => 'mdi:emoticon-sad-outline',
    'mental' => 'mdi:brain',
    'mindful' => 'mdi:meditation',
    'meditation' => 'mdi:meditation',
    'therapy' => 'mdi:account-voice',
    'psychology' => 'mdi:brain',
    
    // Sleep
    'sleep' => 'mdi:sleep',
    'insomnia' => 'mdi:sleep-off',
    'rest' => 'mdi:sleep',
    'fatigue' => 'mdi:sleep-off',
    
    // Medical
    'doctor' => 'medical-icon:i-medical-doctor',
    'health' => 'medical-icon:health-care',
    'hospital' => 'medical-icon:i-ambulance',
    'medical' => 'medical-icon:i-medical-records',
    'disease' => 'medical-icon:i-infectious',
    'drug' => 'mdi:pill',
    'medicine' => 'mdi:pill',
    'treatment' => 'mdi:medical-bag',
    'symptom' => 'mdi:clipboard-text-outline',
    'diagnosis' => 'mdi:stethoscope',
    'cancer' => 'medical-icon:i-oncology',
    'heart' => 'medical-icon:i-cardiology',
    'diabetes' => 'medical-icon:i-internal-medicine',
    'blood' => 'mdi:blood-bag',
    'surgery' => 'mdi:medical-bag',
    
    // Wellness
    'wellness' => 'mdi:lotus',
    'lifestyle' => 'mdi:account-heart',
    'holistic' => 'mdi:lotus',
    'alternative' => 'mdi:flower',
    'natural' => 'mdi:spa',
    'organic' => 'mdi:leaf',
    'herb' => 'mdi:leaf',
    'supplement' => 'mdi:pill',
    'massage' => 'mdi:hand-heart',
    'spa' => 'mdi:spa',
    
    // Age groups
    'child' => 'mdi:human-child',
    'kid' => 'mdi:human-child',
    'teen' => 'mdi:human-male-child',
    'adult' => 'mdi:human-male',
    'senior' => 'mdi:human-male-board',
    'elderly' => 'mdi:human-cane',
    'aging' => 'mdi:human-cane',
    
    // Prevention
    'prevention' => 'mdi:shield-check',
    'vaccine' => 'mdi:needle',
    'screening' => 'mdi:magnify',
    'checkup' => 'mdi:clipboard-check',
);

// Check if any keywords from the tag name match our icon list
foreach ($health_icons as $keyword => $keyword_icon) {
    if (strpos($tag_name_lower, $keyword) !== false) {
        $icon = $keyword_icon;
        break;
    }
}
?>

<div class="tag-archive bg-gray-100 pb-12">
    <!-- Tag Header -->
    <div class="tag-header bg-gradient-to-r from-gray-700 to-gray-600 text-white py-8 md:py-12 mb-8">
        <div class="container max-w-6xl mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center md:items-start justify-between">
                <div class="text-center md:text-left mb-6 md:mb-0">
                    <div class="flex items-center justify-center md:justify-start mb-3">
                        <span class="iconify text-white text-3xl mr-3 bg-gray-800 p-2 rounded-full" data-icon="<?php echo $icon; ?>"></span>
                        <h1 class="text-3xl md:text-4xl font-bold">
                            <?php echo esc_html($tag_name); ?>
                        </h1>
                    </div>
                    
                    <?php if ($tag_description) : ?>
                        <div class="tag-description text-gray-100 max-w-2xl mb-3">
                            <?php echo wpautop($tag_description); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="post-count text-gray-200">
                        <?php printf(_n('%s Article', '%s Articles', $tag_count, 'healthcare'), number_format_i18n($tag_count)); ?>
                    </div>
                </div>
                
                <div class="tag-filters">
                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-3">
                        <div class="sort-options flex items-center">
                            <span class="text-sm font-medium mr-3"><?php esc_html_e('Sort by:', 'healthcare'); ?></span>
                            <select id="tag-sort" class="bg-transparent border-0 text-white focus:outline-none text-sm font-medium cursor-pointer">
                                <option value="newest"><?php esc_html_e('Newest First', 'healthcare'); ?></option>
                                <option value="oldest"><?php esc_html_e('Oldest First', 'healthcare'); ?></option>
                                <option value="popular"><?php esc_html_e('Most Popular', 'healthcare'); ?></option>
                            </select>
                            <script>
                                document.getElementById('tag-sort').addEventListener('change', function() {
                                    let currentUrl = new URL(window.location.href);
                                    currentUrl.searchParams.set('sort', this.value);
                                    window.location.href = currentUrl.toString();
                                });
                                
                                // Set selected option based on URL parameter
                                (function() {
                                    const urlParams = new URLSearchParams(window.location.search);
                                    const sortParam = urlParams.get('sort');
                                    if (sortParam) {
                                        document.getElementById('tag-sort').value = sortParam;
                                    }
                                })();
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tag Content -->
    <div class="container max-w-6xl mx-auto px-4">
        <?php if (have_posts()) : ?>
            <!-- Related Tags -->
            <?php
            $related_tags = get_tags(array(
                'exclude' => $tag_id,
                'orderby' => 'count',
                'order' => 'DESC',
                'number' => 8,
            ));
            if (!empty($related_tags)) :
            ?>
            <div class="related-tags mb-8">
                <div class="flex flex-wrap items-center">
                    <span class="text-gray-700 font-medium mr-4 mb-2"><?php esc_html_e('Related Tags:', 'healthcare'); ?></span>
                    <?php foreach ($related_tags as $related_tag) : ?>
                        <a href="<?php echo esc_url(get_tag_link($related_tag->term_id)); ?>" class="tag-pill flex items-center mb-2 mr-2 whitespace-nowrap px-3 py-1 bg-white border border-gray-200 rounded-full text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 hover:border-gray-300 shadow-sm transition-all duration-300">
                            <span class="iconify mr-1 text-gray-500" data-icon="mdi:tag"></span>
                            <?php echo esc_html($related_tag->name); ?>
                            <span class="ml-1 text-xs text-gray-500">(<?php echo esc_html($related_tag->count); ?>)</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Posts List View -->
            <div class="posts-list">
                <?php while (have_posts()) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('bg-white rounded-lg shadow-md overflow-hidden transition-transform duration-300 hover:shadow-lg mb-6 border border-gray-200'); ?>>
                    <div class="md:flex">
                        <?php if (has_post_thumbnail()) : ?>
                        <div class="md:w-1/3 flex-shrink-0">
                            <a href="<?php the_permalink(); ?>" class="block overflow-hidden h-full">
                                <?php the_post_thumbnail('medium', array(
                                    'class' => 'w-full h-64 md:h-full object-cover transition-transform duration-500 hover:scale-105',
                                    'alt' => get_the_title()
                                )); ?>
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <div class="p-6 md:p-8 <?php echo has_post_thumbnail() ? 'md:w-2/3' : ''; ?>">
                            <?php
                            // Get post categories
                            $categories = get_the_category();
                            if (!empty($categories)) :
                                $category = $categories[0];
                            ?>
                            <div class="mb-3">
                                <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>" class="inline-block text-xs font-semibold text-health-600 uppercase tracking-wider bg-health-50 px-2 py-1 rounded">
                                    <?php echo esc_html($category->name); ?>
                                </a>
                            </div>
                            <?php endif; ?>
                            
                            <h2 class="text-xl md:text-2xl font-bold mb-3 text-gray-900 hover:text-health-600 transition-colors">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            
                            <div class="text-gray-700 mb-4">
                                <?php the_excerpt(); ?>
                            </div>
                            
                            <div class="flex items-center text-sm text-gray-500 mb-4 flex-wrap">
                                <div class="flex items-center mr-4 mb-2">
                                    <span class="iconify mr-1" data-icon="mdi:calendar"></span>
                                    <time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date(); ?></time>
                                </div>
                                
                                <div class="flex items-center mr-4 mb-2">
                                    <span class="iconify mr-1" data-icon="mdi:account"></span>
                                    <?php the_author(); ?>
                                </div>
                                
                                <?php if (get_comments_number() > 0) : ?>
                                <div class="flex items-center mb-2">
                                    <span class="iconify mr-1" data-icon="mdi:comment"></span>
                                    <?php comments_number('0 comments', '1 comment', '% comments'); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Get other tags for this post -->
                            <?php
                            $post_tags = get_the_tags();
                            if ($post_tags) :
                                // Filter out the current tag
                                $filtered_tags = array_filter($post_tags, function($t) use ($tag_id) {
                                    return $t->term_id != $tag_id;
                                });
                                
                                if (!empty($filtered_tags)) :
                            ?>
                            <div class="post-tags flex flex-wrap mb-4">
                                <?php foreach ($filtered_tags as $post_tag) : ?>
                                <a href="<?php echo esc_url(get_tag_link($post_tag->term_id)); ?>" class="text-xs text-gray-600 bg-gray-100 rounded-full px-2 py-1 mr-2 mb-2 hover:bg-gray-200 transition-colors">
                                    #<?php echo esc_html($post_tag->name); ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; endif; ?>
                            
                            <a href="<?php the_permalink(); ?>" class="inline-block px-4 py-2 bg-health-600 hover:bg-health-700 text-white rounded-lg font-medium transition-colors duration-300">
                                <?php esc_html_e('Read Full Article', 'healthcare'); ?>
                            </a>
                        </div>
                    </div>
                </article>
                <?php endwhile; ?>
            </div>
            
            <!-- Pagination -->
            <?php if (get_the_posts_pagination()) : ?>
            <div class="pagination-container mt-12">
                <nav class="pagination flex justify-center">
                    <?php
                    echo paginate_links(array(
                        'prev_text' => '<span class="iconify" data-icon="mdi:chevron-left"></span> ' . __('Previous', 'healthcare'),
                        'next_text' => __('Next', 'healthcare') . ' <span class="iconify" data-icon="mdi:chevron-right"></span>',
                        'type' => 'list',
                        'end_size' => 2,
                        'mid_size' => 2
                    ));
                    ?>
                </nav>
            </div>
            <?php endif; ?>
            
        <?php else : ?>
            <div class="empty-content bg-white p-8 rounded-lg shadow-md text-center">
                <span class="iconify text-gray-400 text-5xl mb-4" data-icon="mdi:tag-off"></span>
                <h3 class="text-xl font-semibold text-gray-800 mb-2"><?php esc_html_e('No Articles Found', 'healthcare'); ?></h3>
                <p class="text-gray-600 mb-6"><?php esc_html_e('We haven\'t published any articles with this tag yet. Please check back soon for new content.', 'healthcare'); ?></p>
                <a href="<?php echo esc_url(home_url()); ?>" class="inline-block px-4 py-2 bg-health-600 hover:bg-health-700 text-white rounded-lg font-medium transition-colors duration-300">
                    <?php esc_html_e('Browse All Content', 'healthcare'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Health Topics Section -->
    <div class="health-topics-section bg-white py-10 mt-12 border-t border-gray-200">
        <div class="container max-w-6xl mx-auto px-4">
            <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center"><?php esc_html_e('Popular Health Topics', 'healthcare'); ?></h3>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php
                // Get popular categories
                $popular_categories = get_categories(array(
                    'orderby' => 'count',
                    'order' => 'DESC',
                    'number' => 8,
                    'hide_empty' => true
                ));
                
                foreach ($popular_categories as $category) :
                    // Get category icon
                    $cat_icon = 'mdi:heart-pulse'; // Default health icon
                    $cat_name = strtolower($category->name);
                    if (strpos($cat_name, 'nutrition') !== false || strpos($cat_name, 'food') !== false || strpos($cat_name, 'diet') !== false) {
                        $cat_icon = 'mdi:food-apple';
                    } elseif (strpos($cat_name, 'fitness') !== false || strpos($cat_name, 'exercise') !== false || strpos($cat_name, 'workout') !== false) {
                        $cat_icon = 'mdi:run';
                    } elseif (strpos($cat_name, 'mental') !== false || strpos($cat_name, 'mind') !== false || strpos($cat_name, 'stress') !== false) {
                        $cat_icon = 'mdi:brain';
                    } elseif (strpos($cat_name, 'sleep') !== false || strpos($cat_name, 'rest') !== false) {
                        $cat_icon = 'mdi:sleep';
                    } elseif (strpos($cat_name, 'wellness') !== false || strpos($cat_name, 'yoga') !== false) {
                        $cat_icon = 'mdi:yoga';
                    } elseif (strpos($cat_name, 'medical') !== false || strpos($cat_name, 'health') !== false) {
                        $cat_icon = 'medical-icon:i-medical-records';
                    }
                ?>
                <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>" class="health-topic-card p-4 border border-gray-200 rounded-lg bg-white hover:shadow-md transition-all duration-300 flex flex-col items-center text-center">
                    <span class="iconify text-health-600 text-3xl mb-2" data-icon="<?php echo $cat_icon; ?>"></span>
                    <h4 class="font-medium text-gray-900"><?php echo esc_html($category->name); ?></h4>
                    <span class="text-xs text-gray-500 mt-1"><?php echo esc_html($category->count); ?> <?php esc_html_e('articles', 'healthcare'); ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Find by Tags Section -->
    <div class="tags-cloud-section py-10">
        <div class="container max-w-6xl mx-auto px-4">
            <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center"><?php esc_html_e('Browse by Health Tags', 'healthcare'); ?></h3>
            
            <div class="tags-cloud bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
                <div class="flex flex-wrap justify-center">
                    <?php
                    // Get popular tags
                    $tags = get_tags(array(
                        'orderby' => 'count',
                        'order' => 'DESC',
                        'number' => 30,
                        'hide_empty' => true
                    ));
                    
                    if ($tags) :
                        foreach ($tags as $tag) :
                            // Determine font size based on count (min: 0.75rem, max: 1.25rem)
                            $min_count = 1;
                            $max_count = max(array_map(function($t) { return $t->count; }, $tags));
                            $font_size = 0.75 + (($tag->count - $min_count) / max(1, $max_count - $min_count)) * 0.5;
                            
                            // Highlight current tag
                            $is_current = $tag->term_id == $tag_id;
                    ?>
                        <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>" class="tag-cloud-item inline-block px-3 py-1 m-1 rounded-full <?php echo $is_current ? 'bg-health-600 text-white' : 'bg-gray-50 text-gray-700 hover:bg-gray-100'; ?> transition-colors" style="font-size: <?php echo $font_size; ?>rem;">
                            <?php echo esc_html($tag->name); ?>
                        </a>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Newsletter Section -->
    <div class="newsletter-section bg-gradient-to-r from-gray-700 to-gray-800 text-white py-12 mt-12">
        <div class="container max-w-4xl mx-auto px-4 text-center">
            <h3 class="text-2xl md:text-3xl font-bold mb-4"><?php esc_html_e('Stay Updated with Health Insights', 'healthcare'); ?></h3>
            <p class="text-gray-300 mb-6 max-w-2xl mx-auto"><?php esc_html_e('Subscribe to receive the latest health content and research findings delivered straight to your inbox.', 'healthcare'); ?></p>
            
            <form class="max-w-md mx-auto">
                <div class="flex flex-col sm:flex-row gap-3">
                    <input type="email" placeholder="<?php esc_attr_e('Your email address', 'healthcare'); ?>" class="flex-grow px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-white text-gray-900">
                    <button type="submit" class="bg-white text-gray-800 hover:bg-gray-100 transition-colors px-6 py-3 rounded-lg font-medium"><?php esc_html_e('Subscribe', 'healthcare'); ?></button>
                </div>
                <p class="text-xs mt-3 text-gray-400"><?php esc_html_e('We respect your privacy. Unsubscribe at any time.', 'healthcare'); ?></p>
            </form>
        </div>
    </div>
</div>

<style>
/* Tag Archive Page Specific Styles */
.pagination .page-numbers {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0 12px;
    margin: 0 2px;
    border-radius: 6px;
    font-weight: 500;
    font-size: 15px;
    color: #475569;
    background-color: #fff;
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
}

.pagination .page-numbers:hover {
    background-color: #f8fafc;
    border-color: #cbd5e1;
    color: #0ea5e9;
}

.pagination .page-numbers.current {
    background-color: #0ea5e9;
    border-color: #0ea5e9;
    color: #fff;
}

.pagination .page-numbers.dots {
    background-color: transparent;
    border-color: transparent;
}

.pagination .page-numbers.next,
.pagination .page-numbers.prev {
    display: inline-flex;
    align-items: center;
    padding: 0 15px;
}

.pagination .page-numbers.next .iconify,
.pagination .page-numbers.prev .iconify {
    margin: 0 5px;
}

/* Make sure iconify icons align properly */
.iconify {
    vertical-align: middle;
    display: inline-flex;
}

/* Sort dropdown styling */
#tag-sort {
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='white'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.5rem center;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
}

@media (max-width: 768px) {
    .tag-header {
        padding: 30px 0;
    }
    
    .tag-header h1 {
        font-size: 1.875rem;
    }
    
    .sort-options {
        justify-content: center;
        margin-top: 10px;
    }
    
    .pagination .page-numbers {
        min-width: 36px;
        height: 36px;
        font-size: 14px;
    }
    
    .health-topic-card {
        padding: 10px;
    }
    
    .health-topic-card .iconify {
        font-size: 1.5rem;
    }
    
    .health-topic-card h4 {
        font-size: 0.875rem;
    }
}
</style>

<?php
get_footer();