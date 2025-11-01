<?php
/**
 * Template for displaying songs by language
 * Dosya adı: language-single.php
 *
 * @package Gufte
 */

get_header();

$lang_slug = get_query_var('lang_slug');
$language_stats = gufte_get_language_statistics();
$current_language = isset($language_stats[$lang_slug]) ? $language_stats[$lang_slug] : null;

if (!$current_language) {
    // Dil bulunamadı, 404 sayfasına yönlendir
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part('404');
    get_footer();
    exit;
}

// Sayfalama için
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$posts_per_page = 24;

// Bu dildeki şarkıları al
$target_language = $current_language['name'];
$args = array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'posts_per_page' => $posts_per_page,
    'paged' => $paged,
    'meta_query' => array(
        array(
            'key' => '_available_languages',
            'value' => $target_language,
            'compare' => 'LIKE'
        )
    ),
    'orderby' => 'date',
    'order' => 'DESC'
);

$language_query = new WP_Query($args);
?>

<div class="site-content-wrapper flex flex-col md:flex-row">

    <?php get_template_part('template-parts/arcuras-sidebar'); ?>

    <main id="primary" class="site-main flex-1 px-4 sm:px-6 lg:px-8 py-8 overflow-x-hidden">

        <!-- Language Header -->
        <div class="language-header mb-8 p-6 bg-gradient-to-r from-primary-50 to-accent-50 rounded-xl border border-primary-100">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                <!-- Language Flag & Info -->
                <div class="flex-shrink-0 text-center">
                    <div class="language-flag text-6xl mb-2">
                        <?php echo $current_language['flag']; ?>
                    </div>
                    <div class="text-sm text-gray-600">
                        <?php echo esc_html($current_language['name']); ?>
                    </div>
                </div>

                <!-- Language Details -->
                <div class="flex-1 text-center md:text-left">
                    <h1 class="text-3xl md:text-4xl font-extrabold text-gray-800 mb-4">
                        <?php printf(esc_html__('Songs in %s', 'gufte'), esc_html($current_language['native_name'])); ?>
                    </h1>
                    
                    <div class="language-stats flex flex-wrap justify-center md:justify-start gap-4 mb-4">
                        <div class="stat-item bg-white/60 backdrop-blur-sm rounded-full px-4 py-2 border border-primary-200/50">
                            <span class="iconify mr-2 text-primary-600" data-icon="mdi:music-note-multiple"></span>
                            <span class="font-semibold text-gray-800"><?php echo number_format_i18n($current_language['count']); ?></span>
                            <span class="text-gray-600 ml-1"><?php echo _n('song', 'songs', $current_language['count'], 'gufte'); ?></span>
                        </div>
                        
                        <div class="stat-item bg-white/60 backdrop-blur-sm rounded-full px-4 py-2 border border-primary-200/50">
                            <span class="iconify mr-2 text-primary-600" data-icon="mdi:chart-line"></span>
                            <span class="font-semibold text-gray-800"><?php echo number_format($current_language['percentage'], 1); ?>%</span>
                            <span class="text-gray-600 ml-1"><?php esc_html_e('of all songs', 'gufte'); ?></span>
                        </div>
                    </div>

                    <p class="text-gray-700 leading-relaxed">
                        <?php printf(
                            esc_html__('Discover and enjoy song lyrics translated into %s. Browse through our collection of %s songs with high-quality translations.', 'gufte'),
                            esc_html($current_language['native_name']),
                            number_format_i18n($current_language['count'])
                        ); ?>
                    </p>
                </div>

                <!-- Back Link -->
                <div class="flex-shrink-0">
                    <a href="<?php echo esc_url(home_url('/languages/')); ?>" class="inline-flex items-center bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg transition-colors duration-300">
                        <span class="iconify mr-2" data-icon="mdi:arrow-left"></span>
                        <?php esc_html_e('All Languages', 'gufte'); ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Breadcrumb -->
        <nav class="breadcrumb mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 text-sm text-gray-600">
                <li><a href="<?php echo esc_url(home_url('/')); ?>" class="hover:text-primary-600 transition-colors duration-300"><?php esc_html_e('Home', 'gufte'); ?></a></li>
                <li><span class="iconify mx-2 text-gray-400" data-icon="mdi:chevron-right"></span></li>
                <li><a href="<?php echo esc_url(home_url('/languages/')); ?>" class="hover:text-primary-600 transition-colors duration-300"><?php esc_html_e('Languages', 'gufte'); ?></a></li>
                <li><span class="iconify mx-2 text-gray-400" data-icon="mdi:chevron-right"></span></li>
                <li class="font-medium text-gray-800"><?php echo esc_html($current_language['native_name']); ?></li>
            </ol>
        </nav>

        <!-- Filter & Sort Options -->
        <div class="filter-controls mb-6 p-4 bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="filter-info flex items-center">
                    <span class="iconify mr-2 text-primary-600" data-icon="mdi:filter"></span>
                    <span class="text-gray-700">
                        <?php printf(
                            esc_html__('Showing %1$s - %2$s of %3$s songs', 'gufte'),
                            number_format_i18n((($paged - 1) * $posts_per_page) + 1),
                            number_format_i18n(min($paged * $posts_per_page, $current_language['count'])),
                            number_format_i18n($current_language['count'])
                        ); ?>
                    </span>
                </div>

                <div class="filter-options flex items-center gap-3">
                    <label for="sort-order" class="text-sm text-gray-600"><?php esc_html_e('Sort by:', 'gufte'); ?></label>
                    <select id="sort-order" class="border border-gray-300 rounded-md px-3 py-1 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="newest"><?php esc_html_e('Newest First', 'gufte'); ?></option>
                        <option value="oldest"><?php esc_html_e('Oldest First', 'gufte'); ?></option>
                        <option value="title"><?php esc_html_e('Title A-Z', 'gufte'); ?></option>
                        <option value="popular"><?php esc_html_e('Most Popular', 'gufte'); ?></option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Songs Grid -->
        <?php if ($language_query->have_posts()) : ?>
        <div class="songs-grid grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 mb-8">
            <?php
            while ($language_query->have_posts()) :
                $language_query->the_post();
                
                $singers = get_the_terms(get_the_ID(), 'singer');
                $singer = !empty($singers) && !is_wp_error($singers) ? reset($singers) : null;
                
                // Çeviri sayısını al
                $raw_content = get_the_content();
                $lyrics_languages = array('original' => '', 'translations' => array());
                if (function_exists('gufte_get_lyrics_languages')) {
                    $lyrics_languages = gufte_get_lyrics_languages($raw_content);
                }
                $translation_count = !empty($lyrics_languages['translations']) ? count($lyrics_languages['translations']) : 0;
                
                // Bu dilin URL'sini oluştur
                $song_url = add_query_arg('lang', $lang_slug, get_permalink());
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('song-card group bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-gray-300'); ?>>
                <a href="<?php echo esc_url($song_url); ?>" class="block relative aspect-square overflow-hidden bg-gray-100">
                    <?php if (has_post_thumbnail()) : ?>
                        <?php the_post_thumbnail('medium', array('class' => 'w-full h-full object-cover group-hover:scale-110 transition-transform duration-500')); ?>
                    <?php else : ?>
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                            <span class="iconify text-5xl text-gray-400 group-hover:text-primary-500 transition-colors duration-300" data-icon="mdi:music-note"></span>
                        </div>
                    <?php endif; ?>

                    <!-- Language Flag Badge -->
                    <div class="absolute top-2 left-2 text-2xl">
                        <?php echo $current_language['flag']; ?>
                    </div>

                    <?php if ($translation_count > 1) : ?>
                    <div class="absolute top-2 right-2 bg-primary-500/90 backdrop-blur-sm text-white text-xs font-bold px-1.5 py-0.5 rounded-full flex items-center">
                        <span class="iconify mr-0.5 text-xs" data-icon="mdi:translate"></span>
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
                        <a href="<?php echo esc_url($song_url); ?>" class="text-gray-800 hover:text-primary-600 transition-colors duration-300">
                            <?php the_title(); ?>
                        </a>
                    </h2>

                    <?php if ($singer) : ?>
                    <div class="text-xs text-gray-500 truncate mb-1">
                        <a href="<?php echo esc_url(get_term_link($singer)); ?>" class="hover:text-primary-600 transition-colors duration-300">
                            <?php echo esc_html($singer->name); ?>
                        </a>
                    </div>
                    <?php endif; ?>

                    <div class="text-xs text-gray-400">
                        <time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date(); ?></time>
                    </div>
                </div>
            </article>
            <?php
            endwhile;
            wp_reset_postdata();
            ?>
        </div>

        <!-- Pagination -->
        <?php
        $pagination = paginate_links(array(
            'total' => $language_query->max_num_pages,
            'current' => $paged,
            'format' => '?paged=%#%',
            'show_all' => false,
            'type' => 'array',
            'end_size' => 3,
            'mid_size' => 3,
            'prev_next' => true,
            'prev_text' => '<span class="iconify" data-icon="mdi:chevron-left"></span> ' . esc_html__('Previous', 'gufte'),
            'next_text' => esc_html__('Next', 'gufte') . ' <span class="iconify" data-icon="mdi:chevron-right"></span>',
        ));

        if ($pagination) :
        ?>
        <nav class="pagination-nav" aria-label="Posts pagination">
            <ul class="pagination flex flex-wrap justify-center items-center gap-2">
                <?php foreach ($pagination as $page) : ?>
                <li><?php echo $page; ?></li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <?php endif; ?>

        <?php else : ?>
            <div class="no-songs text-center py-12">
                <div class="text-6xl mb-4"><?php echo $current_language['flag']; ?></div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">
                    <?php esc_html_e('No songs found', 'gufte'); ?>
                </h2>
                <p class="text-gray-600 mb-6">
                    <?php printf(esc_html__('There are no songs available in %s yet.', 'gufte'), esc_html($current_language['native_name'])); ?>
                </p>
                <a href="<?php echo esc_url(home_url('/languages/')); ?>" class="inline-flex items-center bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg transition-colors duration-300">
                    <span class="iconify mr-2" data-icon="mdi:arrow-left"></span>
                    <?php esc_html_e('Browse Other Languages', 'gufte'); ?>
                </a>
            </div>
        <?php endif; ?>

    </main>
</div>

<style>
.pagination a, .pagination span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0 12px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.pagination a:hover {
    background-color: #f3f4f6;
    border-color: #d1d5db;
    transform: translateY(-1px);
}

.pagination .current {
    background-color: #2563eb;
    border-color: #2563eb;
    color: white;
}

.pagination .dots {
    border: none;
    background: none;
    color: #9ca3af;
}

.song-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.song-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Sort dropdown styling */
#sort-order {
    appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 8px center;
    background-repeat: no-repeat;
    background-size: 16px;
    padding-right: 32px;
}

/* Loading animation for future AJAX implementation */
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.loading .song-card {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sort functionality (for future AJAX implementation)
    const sortSelect = document.getElementById('sort-order');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            // Future: Implement AJAX sorting
            console.log('Sort changed to:', this.value);
            
            // For now, redirect with sort parameter
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('sort', this.value);
            window.location.href = currentUrl.toString();
        });
        
        // Set current sort value from URL
        const urlParams = new URLSearchParams(window.location.search);
        const currentSort = urlParams.get('sort');
        if (currentSort) {
            sortSelect.value = currentSort;
        }
    }
    
    // Track language page views (optional analytics)
    if (typeof gtag !== 'undefined') {
        const languageName = document.querySelector('.language-header h1').textContent;
        gtag('event', 'page_view', {
            'page_title': languageName,
            'page_location': window.location.href,
            'custom_map': {'custom_parameter_1': 'language_page'}
        });
    }
});
</script>

<?php
get_footer();