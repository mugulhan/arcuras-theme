<?php
/**
 * Historical Releases Functions
 * 
 * @package Ashina Theme
 * @path /inc/historical-releases.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get monthly releases with caching and optimization
 * 
 * @return array|false
 */
function gufte_get_monthly_releases_optimized() {
    $current_month = date('m');
    $current_month_name = date('F');
    
    // Cache key oluştur
    $cache_key = 'gufte_monthly_releases_' . $current_month;
    
    // Cache'den kontrol et (24 saat cache)
    $cached_data = get_transient($cache_key);
    if (false !== $cached_data) {
        return $cached_data;
    }
    
    global $wpdb;
    
    // Optimized query: Date range kullan MONTH() yerine
    $query = $wpdb->prepare("
        SELECT p.ID, pm.meta_value as release_date,
               YEAR(pm.meta_value) as release_year
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE p.post_status = 'publish'
        AND p.post_type = 'post'
        AND pm.meta_key = '_release_date'
        AND pm.meta_value REGEXP %s
        ORDER BY pm.meta_value DESC
        LIMIT 15
    ", '^[0-9]{4}-' . $current_month . '-[0-9]{2}$');
    
    $results = $wpdb->get_results($query);
    
    if (empty($results)) {
        // Boş sonucu da cache'le (6 saat)
        set_transient($cache_key, array(), HOUR_IN_SECONDS * 6);
        return array();
    }
    
    $post_ids = wp_list_pluck($results, 'ID');
    
    // Basic post data'yı al (meta query'ler olmadan)
    $posts_data = array();
    
    foreach ($post_ids as $post_id) {
        $post = get_post($post_id);
        if (!$post) continue;
        
        // Temel verileri topla
        $post_data = array(
            'ID' => $post_id,
            'title' => get_the_title($post_id),
            'permalink' => get_permalink($post_id),
            'thumbnail' => get_the_post_thumbnail_url($post_id, 'thumbnail'),
            'release_date' => get_post_meta($post_id, '_release_date', true),
            'music_genre' => get_post_meta($post_id, '_music_genre', true),
        );

        // Singer bilgisi (optimize edilmiş)
        $post_data['singer'] = gufte_get_post_singer_data($post_id);

        // Lyrics languages (çeviri dilleri)
        $post_data['lyrics_languages'] = gufte_get_post_lyrics_languages($post_id);

        // Translation count (optimize edilmiş)
        $post_data['translation_count'] = !empty($post_data['lyrics_languages']['translations']) ? count($post_data['lyrics_languages']['translations']) : 0;

        // Categories (optimize edilmiş)
        $post_data['categories'] = gufte_get_post_categories_data($post_id);
        
        $posts_data[] = $post_data;
    }
    
    $final_data = array(
        'month_name' => $current_month_name,
        'posts' => $posts_data,
        'total_count' => count($posts_data)
    );
    
    // 24 saat cache et
    set_transient($cache_key, $final_data, DAY_IN_SECONDS);
    
    return $final_data;
}

/**
 * Get singer data for a post (optimized)
 * 
 * @param int $post_id
 * @return array|null
 */
function gufte_get_post_singer_data($post_id) {
    static $singer_cache = array();
    
    if (isset($singer_cache[$post_id])) {
        return $singer_cache[$post_id];
    }
    
    $singers = get_the_terms($post_id, 'singer');
    if (empty($singers) || is_wp_error($singers)) {
        $singer_cache[$post_id] = null;
        return null;
    }
    
    $singer = reset($singers);
    $singer_data = array(
        'name' => $singer->name,
        'link' => get_term_link($singer),
        'image' => ''
    );
    
    // Singer image (cache term meta)
    $singer_image_id = get_term_meta($singer->term_id, 'singer_image_id', true);
    if ($singer_image_id) {
        $singer_data['image'] = wp_get_attachment_image_url($singer_image_id, 'thumbnail');
    }
    
    $singer_cache[$post_id] = $singer_data;
    return $singer_data;
}

/**
 * Get lyrics languages for a post (optimized)
 *
 * @param int $post_id
 * @return array
 */
function gufte_get_post_lyrics_languages($post_id) {
    static $lyrics_cache = array();

    if (isset($lyrics_cache[$post_id])) {
        return $lyrics_cache[$post_id];
    }

    $lyrics_languages = array('original' => '', 'translations' => array());

    if (function_exists('gufte_get_lyrics_languages')) {
        $content = get_post_field('post_content', $post_id);
        $lyrics_languages = gufte_get_lyrics_languages($content);
    }

    $lyrics_cache[$post_id] = $lyrics_languages;
    return $lyrics_languages;
}

/**
 * Get categories for a post (optimized)
 *
 * @param int $post_id
 * @return array
 */
function gufte_get_post_categories_data($post_id) {
    static $categories_cache = array();

    if (isset($categories_cache[$post_id])) {
        return $categories_cache[$post_id];
    }

    $categories = get_the_category($post_id);
    $categories_data = array();

    if (!empty($categories) && !is_wp_error($categories)) {
        // Uncategorized hariç tüm kategorileri al (max 3)
        foreach ($categories as $category) {
            if ($category->slug !== 'uncategorized' && count($categories_data) < 3) {
                $categories_data[] = array(
                    'name' => $category->name,
                    'link' => get_category_link($category->term_id),
                    'slug' => $category->slug
                );
            }
        }
    }

    $categories_cache[$post_id] = $categories_data;
    return $categories_data;
}

/**
 * Get translation count for a post (optimized)
 *
 * @param int $post_id
 * @return int
 */
function gufte_get_post_translation_count($post_id) {
    static $translation_cache = array();
    
    if (isset($translation_cache[$post_id])) {
        return $translation_cache[$post_id];
    }
    
    $translation_count = 0;
    
    // Cache'den translation count'u al
    $cached_count = get_post_meta($post_id, '_cached_translation_count', true);
    if ($cached_count !== '') {
        $translation_cache[$post_id] = (int) $cached_count;
        return (int) $cached_count;
    }
    
    // İlk kez hesaplama gerekiyorsa
    if (function_exists('gufte_get_lyrics_languages')) {
        $content = get_post_field('post_content', $post_id);
        $languages = gufte_get_lyrics_languages($content);
        $translation_count = !empty($languages['translations']) ? count($languages['translations']) : 0;
        
        // Sonucu cache'le
        update_post_meta($post_id, '_cached_translation_count', $translation_count);
    }
    
    $translation_cache[$post_id] = $translation_count;
    return $translation_count;
}

/**
 * Clear monthly releases cache when post is saved/deleted
 *
 * @param int $post_id
 */
function gufte_clear_monthly_releases_cache($post_id) {
    if (get_post_type($post_id) !== 'post') {
        return;
    }

    $release_date = get_post_meta($post_id, '_release_date', true);
    if (!empty($release_date)) {
        $month = date('m', strtotime($release_date));
        delete_transient('gufte_monthly_releases_' . $month);

        // Translation count cache'ini de temizle
        delete_post_meta($post_id, '_cached_translation_count');
    }

    // Mevcut ayın cache'ini de temizle (güvenlik için)
    delete_transient('gufte_monthly_releases_' . date('m'));
}

/**
 * Clear cache when categories are updated
 *
 * @param int $object_id
 * @param array $terms
 * @param array $tt_ids
 * @param string $taxonomy
 */
function gufte_clear_cache_on_category_update($object_id, $terms, $tt_ids, $taxonomy) {
    if ($taxonomy === 'category' && get_post_type($object_id) === 'post') {
        gufte_clear_monthly_releases_cache($object_id);
    }
}

/**
 * Clear all monthly releases caches (admin function)
 */
function gufte_clear_all_monthly_releases_cache() {
    for ($i = 1; $i <= 12; $i++) {
        $month = str_pad($i, 2, '0', STR_PAD_LEFT);
        delete_transient('gufte_monthly_releases_' . $month);
    }
}

/**
 * Get cache status for debugging
 * 
 * @return array
 */
function gufte_get_monthly_releases_cache_status() {
    $status = array();
    
    for ($i = 1; $i <= 12; $i++) {
        $month = str_pad($i, 2, '0', STR_PAD_LEFT);
        $cache_key = 'gufte_monthly_releases_' . $month;
        $cached_data = get_transient($cache_key);
        
        $status[$month] = array(
            'month_name' => date('F', mktime(0, 0, 0, $i, 1)),
            'cached' => $cached_data !== false,
            'count' => $cached_data !== false && isset($cached_data['total_count']) ? $cached_data['total_count'] : 0
        );
    }
    
    return $status;
}

/**
 * Render historical releases section
 * 
 * @return void
 */
function gufte_render_historical_releases_section() {
    $monthly_releases_data = gufte_get_monthly_releases_optimized();
    
    if (empty($monthly_releases_data['posts'])) {
        return; // Hiçbir şey gösterme
    }
    
    // Template'i include et
    get_template_part('template-parts/historical-releases');
}

// Hook'lar
add_action('save_post', 'gufte_clear_monthly_releases_cache');
add_action('before_delete_post', 'gufte_clear_monthly_releases_cache');
add_action('set_object_terms', 'gufte_clear_cache_on_category_update', 10, 4);

// Apple Music data değiştiğinde cache temizle
add_action('updated_post_meta', function($meta_id, $post_id, $meta_key) {
    if ($meta_key === '_release_date' || $meta_key === '_music_genre') {
        gufte_clear_monthly_releases_cache($post_id);
    }
}, 10, 3);

// Admin için cache temizleme
if (is_admin()) {
    add_action('wp_ajax_clear_monthly_releases_cache', function() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        gufte_clear_all_monthly_releases_cache();
        wp_send_json_success('All monthly releases caches cleared.');
    });
}

/**
 * Add admin notice for cache debugging (only for admins)
 */
function gufte_monthly_releases_cache_debug_notice() {
    if (!current_user_can('manage_options') || !isset($_GET['debug_monthly_cache'])) {
        return;
    }
    
    $status = gufte_get_monthly_releases_cache_status();
    ?>
    <div class="notice notice-info">
        <p><strong>Monthly Releases Cache Status:</strong></p>
        <ul>
            <?php foreach ($status as $month => $data) : ?>
            <li>
                <?php echo esc_html($data['month_name']); ?>: 
                <?php echo $data['cached'] ? '✅ Cached' : '❌ Not cached'; ?>
                (<?php echo esc_html($data['count']); ?> songs)
            </li>
            <?php endforeach; ?>
        </ul>
        <p>
            <a href="<?php echo admin_url('admin-ajax.php?action=clear_monthly_releases_cache'); ?>" class="button">
                Clear All Caches
            </a>
        </p>
    </div>
    <?php
}
add_action('admin_notices', 'gufte_monthly_releases_cache_debug_notice');
?>