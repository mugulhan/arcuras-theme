<?php
/**
 * The template for displaying songwriter taxonomy archive pages
 *
 * @package Gufte
 */

get_header();
$term = get_queried_object();

// Schema Markup
$permalink = get_term_link($term);
$songwriter_image_url = '';
$profile_image_id = get_term_meta($term->term_id, 'profile_image_id', true);
if ($profile_image_id) {
    $songwriter_image_url = wp_get_attachment_image_url($profile_image_id, 'full');
}

$real_name = get_term_meta($term->term_id, 'real_name', true);
$birth_place = get_term_meta($term->term_id, 'birth_place', true);
$birth_country = get_term_meta($term->term_id, 'birth_country', true);
$website_url = get_term_meta($term->term_id, 'website_url', true);

// Schema.org Person markup
$songwriter_schema = array(
    '@type' => 'Person',
    '@id' => trailingslashit($permalink) . '#songwriter',
    'name' => $term->name,
    'url' => $permalink,
    'description' => strip_tags(term_description($term->term_id, 'songwriter')) ?: $term->name . ' - Songwriter',
    'jobTitle' => 'Songwriter',
);

if ($real_name) {
    $songwriter_schema['alternateName'] = $real_name;
}
if ($songwriter_image_url) {
    $songwriter_schema['image'] = $songwriter_image_url;
}
if ($website_url) {
    $songwriter_schema['sameAs'][] = $website_url;
}

// Social media links for sameAs
$social_links = gufte_get_credit_social_links($term->term_id);
foreach ($social_links as $platform => $url) {
    if (!empty($url) && $platform !== 'website') {
        $songwriter_schema['sameAs'][] = $url;
    }
}

echo '<script type="application/ld+json">' . 
     wp_json_encode(array('@context' => 'https://schema.org', '@graph' => array($songwriter_schema)), 
     JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . 
     '</script>';
?>

<div class="site-content-wrapper flex flex-col md:flex-row">
    <?php get_template_part('template-parts/arcuras-sidebar'); ?>
    
    <main id="primary" class="site-main flex-1 pt-8 pb-12 px-4 sm:px-6 lg:px-8 overflow-x-hidden">
        
        <!-- Songwriter Header -->
        <header class="page-header mb-8 bg-white p-4 md:p-6 rounded-lg shadow-md border border-gray-200">
            <div class="flex flex-col md:flex-row gap-6">
                
                <!-- Profile Image -->
                <?php if ($profile_image_id) : ?>
                <div class="songwriter-image-container md:w-1/4 lg:w-1/5 flex-shrink-0">
                    <div class="rounded-lg overflow-hidden shadow-sm border border-gray-200 aspect-square">
                        <?php echo wp_get_attachment_image($profile_image_id, 'medium', false, 
                            array('class' => 'w-full h-full object-cover')); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="songwriter-info flex-grow">
                    <h1 class="page-title text-3xl font-bold text-gray-800 mb-4 flex items-center">
                        <span class="iconify mr-3 text-3xl text-primary-600" data-icon="mdi:pen"></span>
                        <?php echo esc_html($term->name); ?>
                        <span class="ml-3 text-sm bg-blue-100 text-blue-800 px-3 py-1 rounded-full">Songwriter</span>
                    </h1>
                    
                    <!-- Meta Information -->
                    <div class="songwriter-meta space-y-2 mb-4 text-sm text-gray-600">
                        <?php if ($real_name) : ?>
                        <div class="flex items-center">
                            <span class="iconify mr-2 text-base text-gray-400 w-5 text-center" data-icon="mdi:account-outline"></span>
                            <span><?php echo esc_html($real_name); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (function_exists('gufte_get_credit_lifespan')) :
                            $lifespan = gufte_get_credit_lifespan($term->term_id);
                            if ($lifespan) : ?>
                        <div class="flex items-center">
                            <span class="iconify mr-2 text-base text-gray-400 w-5 text-center" data-icon="mdi:calendar-range"></span>
                            <span><?php echo esc_html($lifespan); ?></span>
                        </div>
                        <?php endif; endif; ?>
                        
                        <?php if ($birth_place || $birth_country) : ?>
                        <div class="flex items-center">
                            <span class="iconify mr-2 text-base text-gray-400 w-5 text-center" data-icon="mdi:map-marker-outline"></span>
                            <span><?php 
                                echo esc_html(trim($birth_place . ($birth_place && $birth_country ? ', ' : '') . $birth_country)); 
                            ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($term->count > 0) : ?>
                        <div class="flex items-center">
                            <span class="iconify mr-2 text-base text-gray-400 w-5 text-center" data-icon="mdi:music-note-multiple"></span>
                            <span><?php printf(_n('%s song written', '%s songs written', $term->count, 'gufte'), 
                                number_format_i18n($term->count)); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Social Links -->
                    <?php if ($social_links) : 
                        $has_social = false;
                        foreach ($social_links as $url) {
                            if (!empty($url)) { $has_social = true; break; }
                        }
                        if ($has_social) : ?>
                    <div class="social-links mt-4 pt-4 border-t border-gray-100">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">Connect</h4>
                        <div class="flex flex-wrap gap-2">
                            <?php if (!empty($social_links['website'])) : ?>
                            <a href="<?php echo esc_url($social_links['website']); ?>" target="_blank" rel="noopener"
                               class="inline-flex items-center px-3 py-1.5 bg-gray-600 hover:bg-gray-700 text-white text-xs font-medium rounded-full transition">
                                <span class="iconify mr-1.5" data-icon="mdi:web"></span>
                                Website
                            </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($social_links['instagram'])) : ?>
                            <a href="<?php echo esc_url($social_links['instagram']); ?>" target="_blank" rel="noopener"
                               class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white text-xs font-medium rounded-full transition">
                                <span class="iconify mr-1.5" data-icon="mdi:instagram"></span>
                                Instagram
                            </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($social_links['twitter'])) : ?>
                            <a href="<?php echo esc_url($social_links['twitter']); ?>" target="_blank" rel="noopener"
                               class="inline-flex items-center px-3 py-1.5 bg-black hover:bg-gray-800 text-white text-xs font-medium rounded-full transition">
                                <span class="iconify mr-1.5" data-icon="mdi:twitter"></span>
                                Twitter/X
                            </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($social_links['linkedin'])) : ?>
                            <a href="<?php echo esc_url($social_links['linkedin']); ?>" target="_blank" rel="noopener"
                               class="inline-flex items-center px-3 py-1.5 bg-[#0077B5] hover:bg-[#006396] text-white text-xs font-medium rounded-full transition">
                                <span class="iconify mr-1.5" data-icon="mdi:linkedin"></span>
                                LinkedIn
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; endif; ?>
                    
                    <!-- Collaborations Stats -->
                    <?php
                    // Find other credits for songs by this songwriter
                    $collab_producers = array();
                    $collab_singers = array();
                    
                    $args = array(
                        'post_type' => 'lyrics',
                        'posts_per_page' => -1,
                        'fields' => 'ids',
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'songwriter',
                                'field' => 'term_id',
                                'terms' => $term->term_id,
                            ),
                        ),
                    );
                    $songwriter_posts = get_posts($args);
                    
                    if (!empty($songwriter_posts)) {
                        foreach ($songwriter_posts as $post_id) {
                            $producers = get_the_terms($post_id, 'producer');
                            if ($producers && !is_wp_error($producers)) {
                                foreach ($producers as $producer) {
                                    $collab_producers[$producer->term_id] = $producer;
                                }
                            }
                            
                            $singers = get_the_terms($post_id, 'singer');
                            if ($singers && !is_wp_error($singers)) {
                                foreach ($singers as $singer) {
                                    $collab_singers[$singer->term_id] = $singer;
                                }
                            }
                        }
                    }
                    
                    if (!empty($collab_producers) || !empty($collab_singers)) : ?>
                    <div class="collaborations mt-4 pt-4 border-t border-gray-100">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Frequent Collaborators</h4>
                        <?php if (!empty($collab_singers) && count($collab_singers) > 0) : ?>
                        <div class="text-xs text-gray-600 mb-2">
                            <span class="font-medium">Artists:</span> 
                            <?php 
                            $singer_names = array();
                            $max_show = 5;
                            $count = 0;
                            foreach ($collab_singers as $singer) {
                                if ($count >= $max_show) break;
                                $singer_names[] = '<a href="' . get_term_link($singer) . '" class="text-primary-600 hover:underline">' . 
                                                 $singer->name . '</a>';
                                $count++;
                            }
                            echo implode(', ', $singer_names);
                            if (count($collab_singers) > $max_show) {
                                echo ' and ' . (count($collab_singers) - $max_show) . ' more';
                            }
                            ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($collab_producers) && count($collab_producers) > 0) : ?>
                        <div class="text-xs text-gray-600">
                            <span class="font-medium">Producers:</span> 
                            <?php 
                            $producer_names = array();
                            $max_show = 3;
                            $count = 0;
                            foreach ($collab_producers as $producer) {
                                if ($count >= $max_show) break;
                                $producer_names[] = '<a href="' . get_term_link($producer) . '" class="text-primary-600 hover:underline">' . 
                                                   $producer->name . '</a>';
                                $count++;
                            }
                            echo implode(', ', $producer_names);
                            if (count($collab_producers) > $max_show) {
                                echo ' and ' . (count($collab_producers) - $max_show) . ' more';
                            }
                            ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Biography -->
            <?php if ($term->description) : ?>
            <div class="songwriter-biography mt-6 border-t border-gray-100 pt-4">
                <h3 class="text-lg font-semibold text-gray-700 mb-2 flex items-center">
                    <span class="iconify mr-2 text-primary-600" data-icon="mdi:text-box-outline"></span>
                    About
                </h3>
                <div class="prose prose-sm max-w-none text-gray-700">
                    <?php echo wpautop(wp_kses_post($term->description)); ?>
                </div>
            </div>
            <?php endif; ?>
        </header>
        
        <!-- Songs List -->
        <div class="songs-listing mt-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <span class="iconify mr-3 text-2xl text-primary-600" data-icon="mdi:music-note-outline"></span>
                Songs written by <?php echo esc_html($term->name); ?>
            </h2>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php if (have_posts()) : ?>
                    <?php while (have_posts()) : the_post(); ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class('bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-primary-300 flex flex-col'); ?>>
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
                                
                                <?php 
                                $singers = get_the_terms(get_the_ID(), 'singer');
                                if ($singers && !is_wp_error($singers)) : ?>
                                <div class="text-xs text-gray-500 mb-2">
                                    <span class="iconify inline mr-1" data-icon="mdi:microphone"></span>
                                    <?php 
                                    $singer_links = array();
                                    foreach($singers as $singer) {
                                        $singer_links[] = '<a href="' . get_term_link($singer) . '" class="hover:text-primary-500">' . 
                                                         esc_html($singer->name) . '</a>';
                                    }
                                    echo implode(', ', $singer_links);
                                    ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php 
                                $producers = get_the_terms(get_the_ID(), 'producer');
                                if ($producers && !is_wp_error($producers)) : ?>
                                <div class="text-xs text-gray-500 mb-2">
                                    <span class="iconify inline mr-1" data-icon="mdi:console-line"></span>
                                    <?php 
                                    $producer_links = array();
                                    foreach($producers as $producer) {
                                        $producer_links[] = '<a href="' . get_term_link($producer) . '" class="hover:text-primary-500">' . 
                                                          esc_html($producer->name) . '</a>';
                                    }
                                    echo 'Produced by ' . implode(', ', $producer_links);
                                    ?>
                                </div>
                                <?php endif; ?>
                                
                                <div class="mt-auto pt-3 border-t border-gray-100 flex justify-between items-center text-xs text-gray-500">
                                    <span class="publish-date flex items-center">
                                        <span class="iconify mr-1" data-icon="mdi:calendar-blank"></span>
                                        <time datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>">
                                            <?php echo esc_html(get_the_date()); ?>
                                        </time>
                                    </span>
                                    <a href="<?php the_permalink(); ?>" class="read-more-button bg-primary-50 hover:bg-primary-100 text-primary-600 px-2.5 py-1 rounded-full inline-flex items-center transition font-medium">
                                        View
                                        <span class="iconify ml-1" data-icon="mdi:arrow-right"></span>
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                    
                    <?php the_posts_pagination(array(
                        'mid_size' => 2,
                        'prev_text' => '<span class="iconify" data-icon="mdi:chevron-left"></span>',
                        'next_text' => '<span class="iconify" data-icon="mdi:chevron-right"></span>',
                    )); ?>
                    
                <?php else : ?>
                    <div class="col-span-full">
                        <p class="text-center text-lg text-gray-500 py-12">
                            No songs found for this songwriter.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
    </main>
</div>

<?php get_footer(); ?>