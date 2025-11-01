<?php
/**
 * Admin Tools for Arcuras Theme
 *
 * Provides admin tools for:
 * - Deleting old posts (post type)
 * - Exporting/Importing lyrics custom post type
 *
 * @package Arcuras
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add admin menu for tools
 */
function arcuras_add_admin_tools_menu() {
    add_management_page(
        'Arcuras Tools',
        'Arcuras Tools',
        'manage_options',
        'arcuras-tools',
        'arcuras_tools_page'
    );
}
add_action('admin_menu', 'arcuras_add_admin_tools_menu');

/**
 * Register AJAX actions for export
 */
add_action('wp_ajax_arcuras_export_lyrics', 'arcuras_export_lyrics');

/**
 * Tools page content
 */
function arcuras_tools_page() {
    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Handle form submissions
    if (isset($_POST['arcuras_delete_old_posts_nonce']) && wp_verify_nonce($_POST['arcuras_delete_old_posts_nonce'], 'arcuras_delete_old_posts')) {
        arcuras_delete_old_posts();
    }

    if (isset($_POST['arcuras_export_lyrics_nonce']) && wp_verify_nonce($_POST['arcuras_export_lyrics_nonce'], 'arcuras_export_lyrics')) {
        arcuras_export_lyrics();
    }

    if (isset($_POST['arcuras_import_lyrics_nonce']) && wp_verify_nonce($_POST['arcuras_import_lyrics_nonce'], 'arcuras_import_lyrics') && isset($_FILES['lyrics_import_file'])) {
        arcuras_import_lyrics();
    }

    if (isset($_POST['arcuras_delete_all_lyrics_nonce']) && wp_verify_nonce($_POST['arcuras_delete_all_lyrics_nonce'], 'arcuras_delete_all_lyrics')) {
        arcuras_delete_all_lyrics();
    }

    ?>
    <div class="wrap">
        <h1>🛠️ Arcuras Theme Tools</h1>
        <p>Tema yönetim araçları. Dikkatli kullanın!</p>

        <!-- Delete Old Posts Tool -->
        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2>🗑️ Eski Post'ları Sil</h2>
            <p><strong>Dikkat:</strong> Bu araç <code>post</code> tipindeki tüm şarkıları kalıcı olarak siler. Geri alınamaz!</p>
            <p>Şu an sistemde <strong><?php echo wp_count_posts('post')->publish; ?></strong> adet yayınlanmış post var.</p>

            <form method="post" onsubmit="return confirm('⚠️ TÜM POST TİPİNDEKİ YAZILAR SİLİNECEK! Emin misiniz?');">
                <?php wp_nonce_field('arcuras_delete_old_posts', 'arcuras_delete_old_posts_nonce'); ?>
                <p>
                    <button type="submit" class="button button-danger" style="background: #dc3545; border-color: #dc3545; color: white;">
                        Tüm Eski Post'ları Sil
                    </button>
                </p>
            </form>
        </div>

        <!-- Export Lyrics Tool -->
        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2>📦 Lyrics Export</h2>
            <p>Tüm <code>lyrics</code> custom post type'ını JSON formatında export edin.</p>
            <p>Şu an sistemde <strong><?php echo wp_count_posts('lyrics')->publish; ?></strong> adet yayınlanmış lyrics var.</p>

            <p>
                <a href="<?php echo admin_url('admin-ajax.php?action=arcuras_export_lyrics'); ?>"
                   class="button button-primary"
                   download="lyrics-export-<?php echo date('Y-m-d-H-i-s'); ?>.json">
                    📥 Lyrics Export Et (JSON)
                </a>
            </p>
        </div>

        <!-- Import Lyrics Tool -->
        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2>📥 Lyrics Import</h2>
            <p>Daha önce export ettiğiniz JSON dosyasını yükleyin.</p>
            <p><strong>Önemli:</strong> Import sırasında mevcut lyrics'ler korunur, sadece yeni olanlar eklenir.</p>

            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('arcuras_import_lyrics', 'arcuras_import_lyrics_nonce'); ?>
                <p>
                    <input type="file" name="lyrics_import_file" accept=".json" required>
                </p>
                <p style="color: #666; font-size: 12px; margin-top: 8px;">
                    <strong>📁 Maksimum dosya boyutu:</strong> <?php echo ini_get('upload_max_filesize'); ?>
                    <?php
                    $max_size_bytes = wp_convert_hr_to_bytes(ini_get('upload_max_filesize'));
                    if ($max_size_bytes < 3 * 1024 * 1024) { // 3MB'dan küçükse uyarı
                        echo '<span style="color: #dc3545;"> ⚠️ Limit düşük! Büyük dosyalar için limiti artırın.</span>';
                    }
                    ?>
                </p>
                <p>
                    <button type="submit" class="button button-primary">
                        Lyrics Import Et
                    </button>
                </p>
            </form>
        </div>

        <!-- Delete All Lyrics Tool -->
        <div class="card" style="max-width: 800px; margin-top: 20px; border-left: 4px solid #dc3545;">
            <h2>🗑️ Tüm Lyrics'leri Sil</h2>
            <p><strong>⚠️ ÇOK TEHLİKELİ:</strong> Bu araç <code>lyrics</code> custom post type'ındaki <strong>TÜM</strong> şarkıları kalıcı olarak siler!</p>
            <p style="color: #dc3545;"><strong>⚠️ Bu işlem GERİ ALINMAZ!</strong> Silmeden önce mutlaka export edin!</p>
            <p>Şu an sistemde <strong><?php echo wp_count_posts('lyrics')->publish; ?></strong> adet yayınlanmış lyrics var.</p>

            <form method="post" onsubmit="return confirm('⚠️⚠️⚠️ TÜM LYRICS\'LER SİLİNECEK!\n\nBu işlem geri alınamaz!\n\nExport aldınız mı?\n\nDevam etmek istediğinizden EMİN MİSİNİZ?');">
                <?php wp_nonce_field('arcuras_delete_all_lyrics', 'arcuras_delete_all_lyrics_nonce'); ?>
                <p>
                    <button type="submit" class="button" style="background: #dc3545; border-color: #dc3545; color: white;">
                        🗑️ TÜM Lyrics'leri Kalıcı Olarak Sil
                    </button>
                </p>
            </form>
        </div>

        <!-- Stats -->
        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2>📊 İstatistikler</h2>
            <table class="widefat">
                <tr>
                    <td><strong>Post (Eski Sistem):</strong></td>
                    <td><?php echo wp_count_posts('post')->publish; ?> yayınlanmış</td>
                </tr>
                <tr>
                    <td><strong>Lyrics (Yeni Sistem):</strong></td>
                    <td><?php echo wp_count_posts('lyrics')->publish; ?> yayınlanmış</td>
                </tr>
                <tr>
                    <td><strong>Singers:</strong></td>
                    <td><?php echo wp_count_terms('singer'); ?> şarkıcı</td>
                </tr>
                <tr>
                    <td><strong>Producers:</strong></td>
                    <td><?php echo wp_count_terms('producer'); ?> yapımcı</td>
                </tr>
                <tr>
                    <td><strong>Songwriters:</strong></td>
                    <td><?php echo wp_count_terms('songwriter'); ?> söz yazarı</td>
                </tr>
            </table>
        </div>
    </div>
    <?php
}

/**
 * Delete all old posts (post type)
 */
function arcuras_delete_old_posts() {
    global $wpdb;

    // Get all post IDs
    $post_ids = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE post_type = 'post'");

    $deleted = 0;
    foreach ($post_ids as $post_id) {
        if (wp_delete_post($post_id, true)) { // true = force delete (bypass trash)
            $deleted++;
        }
    }

    echo '<div class="notice notice-success"><p><strong>✅ Başarılı!</strong> ' . $deleted . ' adet post silindi.</p></div>';
}

/**
 * Export lyrics to JSON
 */
function arcuras_export_lyrics() {
    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to export lyrics.'));
    }

    global $wpdb;

    // Get lyrics directly from database to ensure we get full content
    $lyrics = $wpdb->get_results(
        "SELECT ID, post_title, post_content, post_excerpt, post_status, post_date, post_name
         FROM {$wpdb->posts}
         WHERE post_type = 'lyrics'
         AND post_status IN ('publish', 'draft', 'pending')
         ORDER BY post_date DESC"
    );

    $export_data = array();

    foreach ($lyrics as $lyric) {
        // Get taxonomies safely (only if they exist)
        $singers = taxonomy_exists('singer') ? wp_get_post_terms($lyric->ID, 'singer', array('fields' => 'names')) : array();
        $producers = taxonomy_exists('producer') ? wp_get_post_terms($lyric->ID, 'producer', array('fields' => 'names')) : array();
        $songwriters = taxonomy_exists('songwriter') ? wp_get_post_terms($lyric->ID, 'songwriter', array('fields' => 'names')) : array();
        $genres = taxonomy_exists('genre') ? wp_get_post_terms($lyric->ID, 'genre', array('fields' => 'names')) : array();
        $moods = taxonomy_exists('mood') ? wp_get_post_terms($lyric->ID, 'mood', array('fields' => 'names')) : array();
        $albums = taxonomy_exists('album') ? wp_get_post_terms($lyric->ID, 'album', array('fields' => 'names')) : array();
        $original_languages = taxonomy_exists('original_language') ? wp_get_post_terms($lyric->ID, 'original_language', array('fields' => 'names')) : array();
        $translated_languages = taxonomy_exists('translated_language') ? wp_get_post_terms($lyric->ID, 'translated_language', array('fields' => 'names')) : array();

        // Convert WP_Error to empty array
        $singers = is_wp_error($singers) ? array() : $singers;
        $producers = is_wp_error($producers) ? array() : $producers;
        $songwriters = is_wp_error($songwriters) ? array() : $songwriters;
        $genres = is_wp_error($genres) ? array() : $genres;
        $moods = is_wp_error($moods) ? array() : $moods;
        $albums = is_wp_error($albums) ? array() : $albums;
        $original_languages = is_wp_error($original_languages) ? array() : $original_languages;
        $translated_languages = is_wp_error($translated_languages) ? array() : $translated_languages;

        // Fix content: convert escaped \n in Gutenberg blocks to real newlines for clean export
        $content = $lyric->post_content;

        // If content has Gutenberg block with escaped newlines, fix them for export
        if (strpos($content, '<!-- wp:arcuras/lyrics-translations') !== false) {
            // Parse block and fix newlines in lyrics
            $content = preg_replace_callback(
                '/<!-- wp:arcuras\/lyrics-translations\s+(.*?)\s*(\/)?-->/s',
                function($matches) {
                    $json_str = $matches[1];

                    // Decode JSON
                    $block_attrs = json_decode($json_str, true);

                    if ($block_attrs && isset($block_attrs['languages'])) {
                        // Fix newlines in each language's lyrics
                        foreach ($block_attrs['languages'] as &$lang) {
                            if (isset($lang['lyrics'])) {
                                // Convert any escaped newlines to real newlines
                                // This handles both \\n (in JSON) and \n (literal backslash+n)
                                $lyrics = $lang['lyrics'];

                                // If contains literal \n (backslash + n), convert to real newline
                                if (strpos($lyrics, '\n') !== false) {
                                    $lyrics = str_replace('\n', "\n", $lyrics);
                                }

                                $lang['lyrics'] = $lyrics;
                            }
                        }

                        // Re-encode with proper newlines for clean export
                        $fixed_json = json_encode($block_attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        return '<!-- wp:arcuras/lyrics-translations ' . $fixed_json . ' /-->';
                    }

                    return $matches[0];
                },
                $content
            );
        }

        $lyric_data = array(
            'title' => $lyric->post_title,
            'content' => $content, // Cleaned content with real newlines
            'excerpt' => $lyric->post_excerpt,
            'status' => $lyric->post_status,
            'date' => $lyric->post_date,
            'slug' => $lyric->post_name,

            // Taxonomies
            'singers' => $singers,
            'producers' => $producers,
            'songwriters' => $songwriters,
            'genres' => $genres,
            'moods' => $moods,
            'albums' => $albums,
            'original_languages' => $original_languages,
            'translated_languages' => $translated_languages,
            'tags' => wp_get_post_terms($lyric->ID, 'post_tag', array('fields' => 'names')),
            'categories' => wp_get_post_terms($lyric->ID, 'category', array('fields' => 'names')),

            // Meta data
            'meta' => get_post_meta($lyric->ID),

            // Featured image
            'featured_image_url' => get_the_post_thumbnail_url($lyric->ID, 'full'),
        );

        $export_data[] = $lyric_data;
    }

    // Generate filename
    $filename = 'lyrics-export-' . date('Y-m-d-H-i-s') . '.json';

    // Send headers
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');

    // Output JSON with proper encoding
    $json = json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    // Check for JSON encoding errors
    if ($json === false) {
        header('Content-Type: text/plain');
        echo 'JSON Encoding Error: ' . json_last_error_msg();
        exit;
    }

    echo $json;
    exit;
}

/**
 * Import lyrics from JSON
 */
function arcuras_import_lyrics() {
    if (!isset($_FILES['lyrics_import_file'])) {
        echo '<div class="notice notice-error"><p>Dosya seçilmedi!</p></div>';
        return;
    }

    $file = $_FILES['lyrics_import_file'];

    // Check for errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo '<div class="notice notice-error"><p>Dosya yükleme hatası!</p></div>';
        return;
    }

    // Read file content
    $json_content = file_get_contents($file['tmp_name']);
    $import_data = json_decode($json_content, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($import_data) || empty($import_data)) {
        echo '<div class="notice notice-error"><p>Geçersiz JSON dosyası! Hata: ' . json_last_error_msg() . '</p></div>';
        return;
    }

    $imported = 0;
    $skipped = 0;
    $errors = 0;
    $images_imported = 0;
    $error_messages = array();

    foreach ($import_data as $index => $lyric_data) {
        // Validate required fields
        if (empty($lyric_data['title']) || empty($lyric_data['content'])) {
            $errors++;
            $error_messages[] = 'Satır ' . ($index + 1) . ': Başlık veya içerik eksik';
            continue;
        }

        // Check if lyric already exists (by slug or title)
        if (!empty($lyric_data['slug'])) {
            $existing = get_page_by_path($lyric_data['slug'], OBJECT, 'lyrics');
            if ($existing) {
                $skipped++;
                continue;
            }
        }

        // Sanitize post data
        // CRITICAL: Handle newlines properly for Gutenberg blocks
        // wp_insert_post() strips one level of escaping, so we need \\\\n to get \\n in DB
        $content = $lyric_data['content'];

        if (strpos($content, '<!-- wp:arcuras/lyrics-translations') !== false) {
            // Parse and fix Gutenberg block - match both --> and /--> endings
            $content = preg_replace_callback(
                '/<!-- wp:arcuras\/lyrics-translations\s+(\{.*?\})\s*(\/)?-->/s',
                function($matches) {
                    $json_str = $matches[1];

                    // Decode the JSON to get block attributes
                    $block_attrs = json_decode($json_str, true);

                    if ($block_attrs && isset($block_attrs['languages'])) {
                        // Convert real newlines (from exported JSON) to \n strings
                        foreach ($block_attrs['languages'] as &$lang) {
                            if (isset($lang['lyrics'])) {
                                // Replace real newlines with \n string
                                $lang['lyrics'] = str_replace("\n", "\\n", $lang['lyrics']);
                            }
                        }

                        // Encode back to JSON
                        $fixed_json = json_encode($block_attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                        // CRITICAL: Double the backslashes for wp_insert_post
                        // \n becomes \\n (which wp_insert_post will save as \n in DB)
                        $fixed_json = str_replace("\\n", "\\\\n", $fixed_json);

                        return '<!-- wp:arcuras/lyrics-translations ' . $fixed_json . ' /-->';
                    }

                    return $matches[0];
                },
                $content
            );
        }

        $post_data = array(
            'post_title'   => wp_strip_all_tags($lyric_data['title']),
            'post_content' => $content, // Keep content as-is (may contain HTML)
            'post_excerpt' => isset($lyric_data['excerpt']) ? wp_strip_all_tags($lyric_data['excerpt']) : '',
            'post_status'  => in_array($lyric_data['status'], array('publish', 'draft', 'pending', 'private')) ? $lyric_data['status'] : 'draft',
            'post_type'    => 'lyrics',
            'post_date'    => isset($lyric_data['date']) ? $lyric_data['date'] : current_time('mysql'),
        );

        // Add slug if exists
        if (!empty($lyric_data['slug'])) {
            $post_data['post_name'] = sanitize_title($lyric_data['slug']);
        }

        // Create post
        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            $errors++;
            $error_messages[] = 'Satır ' . ($index + 1) . ' (' . $lyric_data['title'] . '): ' . $post_id->get_error_message();
            continue;
        }

        // Set taxonomies
        if (!empty($lyric_data['singers'])) {
            wp_set_post_terms($post_id, $lyric_data['singers'], 'singer');
        }
        if (!empty($lyric_data['producers'])) {
            wp_set_post_terms($post_id, $lyric_data['producers'], 'producer');
        }
        if (!empty($lyric_data['songwriters'])) {
            wp_set_post_terms($post_id, $lyric_data['songwriters'], 'songwriter');
        }
        if (!empty($lyric_data['genres'])) {
            wp_set_post_terms($post_id, $lyric_data['genres'], 'genre');
        }
        if (!empty($lyric_data['moods'])) {
            wp_set_post_terms($post_id, $lyric_data['moods'], 'mood');
        }
        if (!empty($lyric_data['albums'])) {
            wp_set_post_terms($post_id, $lyric_data['albums'], 'album');
        }
        if (!empty($lyric_data['original_languages'])) {
            wp_set_post_terms($post_id, $lyric_data['original_languages'], 'original_language');
        }
        if (!empty($lyric_data['translated_languages'])) {
            wp_set_post_terms($post_id, $lyric_data['translated_languages'], 'translated_language');
        }
        if (!empty($lyric_data['tags'])) {
            wp_set_post_terms($post_id, $lyric_data['tags'], 'post_tag');
        }
        if (!empty($lyric_data['categories'])) {
            wp_set_post_terms($post_id, $lyric_data['categories'], 'category');
        }

        // Set meta data
        if (!empty($lyric_data['meta']) && is_array($lyric_data['meta'])) {
            foreach ($lyric_data['meta'] as $meta_key => $meta_values) {
                // Skip protected meta keys
                if (is_protected_meta($meta_key, 'post')) {
                    continue;
                }

                if (is_array($meta_values)) {
                    if (count($meta_values) === 1) {
                        // Single value - maybe_unserialize handles serialized data
                        update_post_meta($post_id, $meta_key, maybe_unserialize($meta_values[0]));
                    } else {
                        // Multiple values
                        delete_post_meta($post_id, $meta_key);
                        foreach ($meta_values as $meta_value) {
                            add_post_meta($post_id, $meta_key, maybe_unserialize($meta_value));
                        }
                    }
                } else {
                    // Direct value (shouldn't happen but handle it)
                    update_post_meta($post_id, $meta_key, maybe_unserialize($meta_values));
                }
            }
        }

        // Import featured image from URL if available
        if (!empty($lyric_data['featured_image_url'])) {
            $attachment_id = arcuras_import_featured_image($post_id, $lyric_data['featured_image_url'], $lyric_data['title']);
            if ($attachment_id) {
                $images_imported++;
            }
        }

        $imported++;
    }

    // Show results
    $message_type = ($errors > 0) ? 'notice-warning' : 'notice-success';
    echo '<div class="notice ' . $message_type . '"><p><strong>✅ Import Tamamlandı!</strong><br>';
    echo 'İçe aktarılan: ' . $imported . '<br>';
    echo 'Atlanan (mevcut): ' . $skipped . '<br>';
    echo 'Featured image import: ' . $images_imported . '<br>';
    echo 'Hata: ' . $errors . '</p>';

    // Show error details if any
    if (!empty($error_messages)) {
        echo '<p><strong>Hata Detayları:</strong></p><ul style="list-style: disc; margin-left: 20px;">';
        foreach ($error_messages as $error_msg) {
            echo '<li>' . esc_html($error_msg) . '</li>';
        }
        echo '</ul>';
    }

    echo '</div>';
}

/**
 * Delete all lyrics (lyrics custom post type)
 */
function arcuras_delete_all_lyrics() {
    global $wpdb;

    // Get all lyrics IDs (all statuses)
    $lyrics_ids = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE post_type = 'lyrics'");

    $deleted = 0;
    $errors = 0;

    foreach ($lyrics_ids as $lyric_id) {
        // Force delete (bypass trash)
        $result = wp_delete_post($lyric_id, true);

        if ($result) {
            $deleted++;
        } else {
            $errors++;
        }
    }

    if ($errors > 0) {
        echo '<div class="notice notice-warning"><p><strong>⚠️ Kısmi Başarı!</strong><br>';
        echo 'Silinen: ' . $deleted . ' lyrics<br>';
        echo 'Hata: ' . $errors . ' lyrics silinemedi</p></div>';
    } else {
        echo '<div class="notice notice-success"><p><strong>✅ Başarılı!</strong> ' . $deleted . ' adet lyrics kalıcı olarak silindi.</p></div>';
    }
}

/**
 * Import featured image from URL and attach to post
 *
 * @param int $post_id Post ID to attach image to
 * @param string $image_url URL of the image to download
 * @param string $title Title for the image (used as alt text and filename)
 * @return int|false Attachment ID on success, false on failure
 */
function arcuras_import_featured_image($post_id, $image_url, $title = '') {
    // Skip if post already has a featured image
    if (has_post_thumbnail($post_id)) {
        return false;
    }

    // Validate URL
    if (empty($image_url) || !filter_var($image_url, FILTER_VALIDATE_URL)) {
        return false;
    }

    // Include required files for media upload
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    // Download image to temp file
    $temp_file = download_url($image_url);

    if (is_wp_error($temp_file)) {
        return false;
    }

    // Get file extension from URL
    $file_ext = pathinfo(parse_url($image_url, PHP_URL_PATH), PATHINFO_EXTENSION);
    if (empty($file_ext)) {
        $file_ext = 'jpg'; // Default to jpg if no extension found
    }

    // Prepare file array for wp_handle_sideload
    $file_array = array(
        'name' => sanitize_file_name($title) . '-' . time() . '.' . $file_ext,
        'tmp_name' => $temp_file
    );

    // Upload to WordPress media library
    $attachment_id = media_handle_sideload($file_array, $post_id, $title);

    // Clean up temp file
    @unlink($temp_file);

    if (is_wp_error($attachment_id)) {
        return false;
    }

    // Set as featured image
    set_post_thumbnail($post_id, $attachment_id);

    return $attachment_id;
}
