<?php
/**
 * The template for displaying album taxonomy archive pages (modern responsive design)
 *
 * @package Arcuras
 */

get_header();

/** @var WP_Term $term */
$term = get_queried_object();

// Meta: Album year and cover
$album_year      = get_term_meta($term->term_id, 'album_year', true);
$album_cover_id  = get_term_meta($term->term_id, 'album_cover_id', true);
$album_cover_url = $album_cover_id ? wp_get_attachment_image_url($album_cover_id, 'large') : '';

// Fallback: Get cover from first post with thumbnail
if (!$album_cover_url) {
    $first_with_thumb = get_posts(array(
        'post_type'      => 'lyrics',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'tax_query'      => array(
            array(
                'taxonomy' => 'album',
                'field'    => 'term_id',
                'terms'    => $term->term_id,
            ),
        ),
        'meta_query'     => array(
            array(
                'key'     => '_thumbnail_id',
                'compare' => 'EXISTS',
            ),
        ),
        'no_found_rows'  => true,
    ));
    if (!empty($first_with_thumb)) {
        $thumb = get_the_post_thumbnail_url($first_with_thumb[0], 'large');
        if ($thumb) $album_cover_url = $thumb;
    }
}

// Get album singers for meta tags
$album_singers = function_exists('gufte_get_album_singers')
    ? (array) gufte_get_album_singers($term->term_id)
    : array();

// Output Open Graph meta tags
add_action('wp_head', function() use ($term, $album_cover_url, $album_year, $album_singers) {
    $album_url = get_term_link($term);
    $description = $term->description ? wp_strip_all_tags($term->description) : sprintf(__('Listen to %s album', 'gufte'), $term->name);

    // Basic Open Graph tags
    echo '<meta property="og:type" content="music.album" />' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($term->name) . '" />' . "\n";
    echo '<meta property="og:url" content="' . esc_url($album_url) . '" />' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '" />' . "\n";

    if (!empty($description)) {
        echo '<meta property="og:description" content="' . esc_attr($description) . '" />' . "\n";
    }

    if (!empty($album_cover_url)) {
        echo '<meta property="og:image" content="' . esc_url($album_cover_url) . '" />' . "\n";
        echo '<meta property="og:image:secure_url" content="' . esc_url($album_cover_url) . '" />' . "\n";
    }

    // Music-specific Open Graph tags
    if (!empty($album_singers)) {
        foreach ($album_singers as $singer) {
            echo '<meta property="music:musician" content="' . esc_url(get_term_link($singer)) . '" />' . "\n";
        }
    }

    if (!empty($album_year)) {
        echo '<meta property="music:release_date" content="' . esc_attr($album_year) . '" />' . "\n";
    }

    // Twitter Card tags
    echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($term->name) . '" />' . "\n";

    if (!empty($description)) {
        echo '<meta name="twitter:description" content="' . esc_attr($description) . '" />' . "\n";
    }

    if (!empty($album_cover_url)) {
        echo '<meta name="twitter:image" content="' . esc_url($album_cover_url) . '" />' . "\n";
    }
}, 5);

// Query parameters
$paged = max(1, (int) (get_query_var('paged') ?: get_query_var('page') ?: 1));

// Build query
$query_args = array(
    'post_type'      => 'lyrics',
    'posts_per_page' => 50,
    'paged'          => $paged,
    'orderby'        => array(
        'meta_value_num' => 'ASC',
        'date'           => 'ASC',
    ),
    'meta_query'     => array(
        'relation' => 'OR',
        array(
            'key'     => '_track_number',
            'compare' => 'EXISTS',
        ),
        array(
            'key'     => '_track_number',
            'compare' => 'NOT EXISTS',
        ),
    ),
    'tax_query'      => array(
        array(
            'taxonomy' => 'album',
            'field'    => 'term_id',
            'terms'    => $term->term_id,
        ),
    ),
);

$album_query = new WP_Query($query_args);
$total_posts = (int) $album_query->found_posts;
?>

<div class="site-content-wrapper flex flex-col md:flex-row">
    <?php get_template_part('template-parts/arcuras-sidebar'); ?>

    <main id="primary" class="site-main flex-1 bg-white">

        <!-- Breadcrumb Navigation -->
        <nav class="bg-gray-50 border-b border-gray-200" aria-label="Breadcrumb">
            <div class="px-4 sm:px-6 lg:px-8 py-3">
                <ol class="flex items-center space-x-2 text-sm">
                    <li>
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="text-gray-500 hover:text-primary-600 transition-colors">
                            <?php gufte_icon('home', 'w-4 h-4'); ?>
                        </a>
                    </li>
                    <li class="flex items-center">
                        <span class="text-gray-400 mx-2">/</span>
                        <a href="<?php echo esc_url(home_url('/albums/')); ?>" class="text-gray-500 hover:text-primary-600 transition-colors">
                            <?php esc_html_e('Albums', 'gufte'); ?>
                        </a>
                    </li>
                    <li class="flex items-center">
                        <span class="text-gray-400 mx-2">/</span>
                        <span class="text-gray-900 font-medium">
                            <?php echo esc_html($term->name); ?>
                        </span>
                    </li>
                </ol>
            </div>
        </nav>

        <!-- Album Hero Header -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
            <div class="px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
                <div class="flex flex-col md:flex-row gap-6 items-center md:items-start">

                    <!-- Album Cover - Left Side -->
                    <div class="flex-shrink-0">
                        <div class="w-40 h-40 sm:w-48 sm:h-48 lg:w-56 lg:h-56 rounded-xl overflow-hidden shadow-2xl border-4 border-white">
                            <?php if ($album_cover_url) : ?>
                                <img src="<?php echo esc_url($album_cover_url); ?>"
                                     alt="<?php echo esc_attr($term->name); ?>"
                                     class="w-full h-full object-cover">
                            <?php else : ?>
                                <div class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-100 flex items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Album Info - Right Side -->
                    <div class="flex-1 text-center md:text-left">
                        <p class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-2">Album</p>
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-gray-900 mb-3 leading-tight">
                            <?php echo esc_html($term->name); ?>
                        </h1>

                        <?php if (!empty($album_singers)) : ?>
                            <div class="mb-3">
                                <span class="text-sm text-gray-500 mr-2">Artist:</span>
                                <?php foreach ($album_singers as $index => $singer) : ?>
                                    <a href="<?php echo esc_url(get_term_link($singer)); ?>"
                                       class="text-lg font-semibold text-blue-600 hover:text-blue-800 hover:underline transition">
                                        <?php echo esc_html($singer->name); ?>
                                    </a>
                                    <?php if ($index < count($album_singers) - 1) echo ', '; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="flex flex-wrap items-center justify-start gap-2 text-sm text-gray-600 mb-3">
                            <?php if (!empty($album_year)) : ?>
                                <span class="font-medium text-gray-900"><?php echo esc_html($album_year); ?></span>
                                <span class="text-gray-300">•</span>
                            <?php endif; ?>
                            <span class="text-gray-700"><?php echo number_format_i18n($total_posts); ?> <?php echo _n('song', 'songs', $total_posts, 'gufte'); ?></span>
                        </div>

                        <?php if ($term->description) : ?>
                            <div class="text-sm leading-relaxed text-gray-600 max-w-2xl">
                                <?php echo wp_kses_post($term->description); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Songs List -->
        <div class="px-4 sm:px-6 lg:px-8 py-6">

            <?php if ($album_query->have_posts()) : ?>

                <h2 class="text-lg font-semibold text-gray-900 mb-4">Songs</h2>

                <div class="space-y-2">
                    <?php while ($album_query->have_posts()) : $album_query->the_post();
                        $singers = get_the_terms(get_the_ID(), 'singer');
                        $singer_name = ($singers && !is_wp_error($singers)) ? $singers[0]->name : '';
                        $track_number = get_post_meta(get_the_ID(), '_track_number', true);
                    ?>
                        <a href="<?php the_permalink(); ?>"
                           class="flex items-center gap-3 p-3 bg-white rounded-lg border border-gray-200 hover:border-blue-300 hover:shadow-sm transition">
                            <?php if ($track_number) : ?>
                                <div class="flex-shrink-0 w-10 text-center">
                                    <span class="text-lg font-bold text-gray-400"><?php echo esc_html($track_number); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="flex-shrink-0 w-14 h-14 rounded overflow-hidden bg-gray-100">
                                    <?php the_post_thumbnail('thumbnail', array('class' => 'w-full h-full object-cover')); ?>
                                </div>
                            <?php else : ?>
                                <div class="flex-shrink-0 w-14 h-14 rounded bg-gray-100 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                                    </svg>
                                </div>
                            <?php endif; ?>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-medium text-gray-900 text-sm truncate">
                                    <?php the_title(); ?>
                                </h3>
                                <?php if ($singer_name) : ?>
                                    <p class="text-xs text-gray-500 truncate">
                                        <?php echo esc_html($singer_name); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <svg class="flex-shrink-0 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination -->
                <?php if ($album_query->max_num_pages > 1) : ?>
                    <div class="mt-8 flex justify-center">
                        <?php
                        $links = paginate_links(array(
                            'total'     => $album_query->max_num_pages,
                            'current'   => $paged,
                            'mid_size'  => 2,
                            'prev_text' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>',
                            'next_text' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>',
                            'type'      => 'array',
                        ));

                        if (!empty($links)) : ?>
                            <nav class="inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <?php foreach ($links as $link) :
                                    if (strpos($link, 'current') !== false) {
                                        $link = preg_replace('/<span/', '<span class="relative inline-flex items-center px-4 py-2 border border-blue-600 bg-blue-600 text-sm font-medium text-white"', $link);
                                    } else {
                                        $link = preg_replace('/<a /', '<a class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"', $link);
                                    }
                                    echo $link;
                                endforeach; ?>
                            </nav>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php else : ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">No songs found</h3>
                    <p class="mt-2 text-sm text-gray-500">This album doesn't have any songs yet.</p>
                </div>
            <?php endif; ?>

        </div>
    </main>
</div>

<?php
wp_reset_postdata();
get_footer();
?>
