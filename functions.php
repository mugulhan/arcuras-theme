<?php
/**
 * Gufte Theme Functions
 *
 * @package Gufte
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// ngrok URL support - dinamik olarak URL'i ayarla
$is_ngrok = (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'ngrok') !== false) ||
             (isset($_SERVER['HTTP_X_FORWARDED_HOST']) && strpos($_SERVER['HTTP_X_FORWARDED_HOST'], 'ngrok') !== false);

if ($is_ngrok) {
    $ngrok_host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST'];
    $ngrok_url = 'https://' . $ngrok_host;
    define('WP_HOME', $ngrok_url);
    define('WP_SITEURL', $ngrok_url);
    define('WP_CONTENT_URL', $ngrok_url . '/wp-content');

    // GUFTE_URI'yi ngrok URL ile override et (bu satır GUFTE_URI tanımlanmadan önce olmalı!)
    add_filter('template_directory_uri', function($template_dir_uri) use ($ngrok_url) {
        $template_dir_uri = str_replace('http://localhost:8888', $ngrok_url, $template_dir_uri);
        $template_dir_uri = str_replace('https://localhost:8888', $ngrok_url, $template_dir_uri);
        return $template_dir_uri;
    });
    add_filter('stylesheet_directory_uri', function($stylesheet_dir_uri) use ($ngrok_url) {
        $stylesheet_dir_uri = str_replace('http://localhost:8888', $ngrok_url, $stylesheet_dir_uri);
        $stylesheet_dir_uri = str_replace('https://localhost:8888', $ngrok_url, $stylesheet_dir_uri);
        return $stylesheet_dir_uri;
    });

    // Asset URL'leri de düzelt
    add_filter('script_loader_src', function($src) use ($ngrok_url) {
        $src = str_replace('http://localhost:8888', $ngrok_url, $src);
        $src = str_replace('https://localhost:8888', $ngrok_url, $src);
        return $src;
    });
    add_filter('style_loader_src', function($src) use ($ngrok_url) {
        $src = str_replace('http://localhost:8888', $ngrok_url, $src);
        $src = str_replace('https://localhost:8888', $ngrok_url, $src);
        return $src;
    });
    add_filter('wp_get_attachment_url', function($url) use ($ngrok_url) {
        $url = str_replace('http://localhost:8888', $ngrok_url, $url);
        $url = str_replace('https://localhost:8888', $ngrok_url, $url);
        return $url;
    });
    add_filter('upload_dir', function($uploads) use ($ngrok_url) {
        $uploads['baseurl'] = str_replace('http://localhost:8888', $ngrok_url, $uploads['baseurl']);
        $uploads['baseurl'] = str_replace('https://localhost:8888', $ngrok_url, $uploads['baseurl']);
        return $uploads;
    });

    // ngrok'ta lazy load Tailwind'i devre dışı bırak
    remove_action('wp_head', 'gufte_optimize_tailwind', 100);
}

if (!function_exists('gufte_get_lyrics_languages')) {
    function gufte_get_lyrics_languages($content) {
        $languages = array();
        $original_language = '';

        // Önce yeni Gutenberg block formatını kontrol et
        if (has_block('arcuras/lyrics-translations', $content)) {
            // Use WordPress's parse_blocks function
            $blocks = parse_blocks($content);

            foreach ($blocks as $block) {
                if ($block['blockName'] === 'arcuras/lyrics-translations') {
                    $attrs = isset($block['attrs']) ? $block['attrs'] : array();

                    if (!empty($attrs['languages']) && is_array($attrs['languages'])) {
                        // Iterate through languages to find original and translations
                        foreach ($attrs['languages'] as $lang) {
                            if (isset($lang['name']) && $lang['name'] !== '') {
                                // Check if this is the original language (handle both boolean and string values)
                                $is_original = isset($lang['isOriginal']) && ($lang['isOriginal'] === true || $lang['isOriginal'] === 'true' || $lang['isOriginal'] === 1);

                                if ($is_original) {
                                    $original_language = $lang['name'];
                                } else {
                                    // This is a translation (or no isOriginal flag = treat as translation)
                                    if (!in_array($lang['name'], $languages)) {
                                        $languages[] = $lang['name'];
                                    }
                                }
                            }
                        }

                        // If no language marked as original, treat first language as original
                        if (empty($original_language) && !empty($attrs['languages'])) {
                            if (!empty($attrs['languages'][0]['name'])) {
                                $original_language = $attrs['languages'][0]['name'];
                                // Remove it from translations if it was added
                                $languages = array_diff($languages, array($original_language));
                            }
                        }
                    }

                    break; // Found the block, exit loop
                }
            }
        }

        // Fallback: Eski tablo formatı için (geriye dönük uyumluluk)
        if (empty($original_language)) {
            preg_match_all('/<figure class="wp-block-table">.*?<table.*?>(.*?)<\/table>.*?<\/figure>/s', $content, $table_matches);

            if (!empty($table_matches[1])) {
                foreach ($table_matches[1] as $table_content) {
                    preg_match('/<thead>(.*?)<\/thead>/s', $table_content, $header_matches);

                    if (!empty($header_matches)) {
                        preg_match_all('/<th>(.*?)<\/th>/s', $header_matches[1], $column_matches);
                        if (!empty($column_matches[1])) {
                            if (isset($column_matches[1][0]) && !empty($column_matches[1][0])) {
                                $original_language = trim(strip_tags($column_matches[1][0]));
                            }

                            $column_count = count($column_matches[1]);
                            for ($i = 1; $i < $column_count; $i++) {
                                $lang = trim(strip_tags($column_matches[1][$i]));
                                if (!empty($lang) && !in_array($lang, $languages, true)) {
                                    $languages[] = $lang;
                                }
                            }
                        }
                    }
                }
            }
        }

        return array(
            'original' => $original_language,
            'translations' => $languages,
        );
    }
}

// Disable core WordPress sitemap endpoints so the theme generator can take over
add_filter('wp_sitemaps_enabled', '__return_false', 99);

/**
 * Define constants
 */
define('GUFTE_VERSION', '2.13.6');
define('GUFTE_DIR', get_template_directory());

// ngrok modunda URI'yi düzelt
if ($is_ngrok) {
    $template_uri = get_template_directory_uri();
    $template_uri = str_replace('http://localhost:8888', $ngrok_url, $template_uri);
    $template_uri = str_replace('https://localhost:8888', $ngrok_url, $template_uri);
    define('GUFTE_URI', $template_uri);
} else {
    define('GUFTE_URI', get_template_directory_uri());
}

/**
 * Include feature modules
 */
// Temporarily disabled - Docker volume mount cache issue
// require_once get_template_directory() . '/inc/features/lyric-image-download.php';
// require_once get_template_directory() . '/inc/features/auto-alt-text.php';
// require_once get_template_directory() . '/inc/features/seo-features.php';

// Block-based line export feature
require_once get_template_directory() . '/inc/features/block-line-export.php';

// AI Translation
require_once get_template_directory() . '/inc/ai-translation.php';
require_once get_template_directory() . '/inc/gemini-ocr.php';

require_once get_template_directory() . '/inc/schema/single-post-schema.php';
require_once get_template_directory() . '/inc/admin/translation-feedback-post-type.php';
require_once get_template_directory() . '/inc/lyrics-post-type.php';
require_once get_template_directory() . '/inc/lyrics-translations-meta.php';
require_once get_template_directory() . '/inc/admin-bulk-image-fetch.php';
require_once get_template_directory() . '/inc/admin-bulk-release-date-fetch.php';
require_once get_template_directory() . '/inc/admin-bulk-artist-album-fetch.php';
require_once get_template_directory() . '/inc/admin-regenerate-thumbnails.php';
require_once get_template_directory() . '/inc/admin-fix-avif-images.php';
require_once get_template_directory() . '/inc/template-functions.php';
require_once get_template_directory() . '/inc/language-taxonomy.php';
require_once get_template_directory() . '/inc/translation-url-handler.php';
require_once get_template_directory() . '/inc/translation-seo.php';
require_once get_template_directory() . '/inc/translation-sitemap.php';
require_once get_template_directory() . '/inc/migrate-tables-to-blocks.php';
require_once get_template_directory() . '/theme-settings.php';

/**
 * Load theme text domain for translations
 */
function gufte_load_textdomain() {
    load_theme_textdomain('gufte', get_template_directory() . '/languages');
}
add_action('after_setup_theme', 'gufte_load_textdomain');

/**
 * Create Lyrics page automatically on theme activation
 */
function gufte_create_lyrics_page() {
    // Check if Lyrics page already exists
    $lyrics_page = get_page_by_path('lyrics');

    if (!$lyrics_page) {
        // Create the Lyrics page
        $lyrics_page_id = wp_insert_post(array(
            'post_title'     => __('Lyrics', 'gufte'),
            'post_name'      => 'lyrics',
            'post_content'   => '',
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'post_author'    => 1,
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
        ));

        if ($lyrics_page_id && !is_wp_error($lyrics_page_id)) {
            // Set the page template
            update_post_meta($lyrics_page_id, '_wp_page_template', 'templates/index.php');

            // Set as posts page (blog page)
            update_option('page_for_posts', $lyrics_page_id);
        }
    }
}
add_action('after_switch_theme', 'gufte_create_lyrics_page');

/**
 * Apple Music'ten albüm kapak görseli almak için fonksiyonlar
 * Bu kodu functions.php dosyasına ekleyin
 */

// Apple Music kapak görseli için meta kutusu oluştur
function gufte_add_music_cover_art_meta_box() {
    add_meta_box(
        'gufte_music_cover_art',
        __('Apple Music Kapak Görseli', 'gufte'),
        'gufte_music_cover_art_meta_box_callback',
        'post',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'gufte_add_music_cover_art_meta_box');

// Meta kutusu içeriği
function gufte_music_cover_art_meta_box_callback($post) {
    // Nonce alanı oluştur
    wp_nonce_field('gufte_music_cover_art_nonce', 'gufte_music_cover_art_nonce');
    
    // Kaydedilmiş URL'yi al
    $apple_music_id = get_post_meta($post->ID, '_apple_music_id', true);
    
    // Meta kutusu içeriği
    ?>
    <p>
        <label for="apple_music_id">Apple Music URL or ID:</label>
        <input type="text" id="apple_music_id" name="apple_music_id"
               value="<?php echo esc_attr($apple_music_id); ?>" style="width: 100%;"
               placeholder="https://music.apple.com/us/song/song-name/1234567890 or just 1234567890">
        <span class="description">
            Paste the full Apple Music URL or just the song ID.<br>
            Example URL: <code>https://music.apple.com/us/song/song-name/1234567890</code><br>
            Example ID: <code>1234567890</code>
        </span>
    </p>
    
    <div style="margin-top: 10px;">
        <button type="button" id="fetch_apple_music_cover" class="button">
            Apple Music Kapağını Al
        </button>
        
        <div id="cover_art_status" style="margin-top: 5px;"></div>
    </div>
    
    <div style="margin-top: 10px; color: #606060;">
        <p><strong>Not:</strong> ID'yi kendim bulamıyorum derseniz, Apple Music URL'sini (i= ile başlayan kısmı) veya embed kodunu girin. Kapak görseliniz yazıyı kaydettikten sonra otomatik alınacaktır.</p>
    </div>
    
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Apple Music kapak görseli alma
        $('#fetch_apple_music_cover').on('click', function() {
            var apple_music_id = $('#apple_music_id').val();
            if (!apple_music_id) {
                $('#cover_art_status').html('<div style="color: red;">Apple Music ID boş olamaz!</div>');
                return;
            }
            
            $('#cover_art_status').html('<div>İşleniyor...</div>');
            
            // AJAX isteği gönder
            $.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'fetch_apple_music_cover_art',
                    post_id: <?php echo $post->ID; ?>,
                    apple_music_id: apple_music_id,
                    nonce: $('#gufte_music_cover_art_nonce').val()
                },
                success: function(response) {
                    console.log('Apple Music Fetch Response:', response);

                    if (response.success) {
                        var message = response.data.message || 'Başarılı!';

                        if (response.data.artist_debug) {
                            console.log('Artist Debug Info:', response.data.artist_debug);
                        }

                        if (response.data.artist_saved) {
                            message += ' Sanatçı kaydedildi.';
                        } else {
                            message += ' UYARI: Sanatçı kaydedilemedi!';
                            console.warn('Artist not saved. Debug:', response.data.artist_debug);
                        }

                        $('#cover_art_status').html('<div style="color: ' + (response.data.artist_saved ? 'green' : 'orange') + ';">' + message + ' Sayfa yenileniyor...</div>');

                        // Sayfayı yenile
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        $('#cover_art_status').html('<div style="color: red;">' + response.data + '</div>');
                    }
                },
                error: function() {
                    $('#cover_art_status').html('<div style="color: red;">Hata oluştu!</div>');
                }
            });
        });
    });
    </script>
    <?php
}

// Meta kutusu verilerini kaydet
function gufte_save_music_cover_art_meta_data($post_id) {
    // Nonce kontrolü
    if (!isset($_POST['gufte_music_cover_art_nonce']) || 
        !wp_verify_nonce($_POST['gufte_music_cover_art_nonce'], 'gufte_music_cover_art_nonce')) {
        return;
    }
    
    // Otomatik kayıt kontrolü
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Yetki kontrolü
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Apple Music ID'yi kaydet
    if (isset($_POST['apple_music_id'])) {
        update_post_meta($post_id, '_apple_music_id', sanitize_text_field($_POST['apple_music_id']));
    }
}
add_action('save_post', 'gufte_save_music_cover_art_meta_data');

// Apple Music API ile kapak görseli alma (AJAX işleyici)
function gufte_ajax_fetch_apple_music_cover_art() {
    // Nonce kontrolü
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'gufte_music_cover_art_nonce')) {
        wp_send_json_error('Güvenlik doğrulaması başarısız.');
        return;
    }
    
    // Post ID ve Apple Music ID kontrolü
    if (!isset($_POST['post_id']) || !isset($_POST['apple_music_id'])) {
        wp_send_json_error('Gerekli parametreler eksik.');
        return;
    }
    
    $post_id = intval($_POST['post_id']);
    $apple_music_id = sanitize_text_field($_POST['apple_music_id']);
    
    // ID'yi kaydet
    update_post_meta($post_id, '_apple_music_id', $apple_music_id);
    
    // Embed URL'den albüm bilgilerini al
    $apple_music_url = "https://embed.music.apple.com/tr/album/track/{$apple_music_id}";
    
    $response = wp_remote_get($apple_music_url);
    
    if (is_wp_error($response)) {
        wp_send_json_error('Apple Music bağlantı hatası: ' . $response->get_error_message());
        return;
    }
    
    $body = wp_remote_retrieve_body($response);

    // iTunes API'den artist bilgisini al (her zaman)
    $itunes_url = "https://itunes.apple.com/lookup?id={$apple_music_id}&entity=song";
    $itunes_response = wp_remote_get($itunes_url);

    $artist_saved = false;
    $artist_debug = array();

    // Debug: iTunes API URL'sini logla
    error_log('iTunes API URL: ' . $itunes_url);

    if (!is_wp_error($itunes_response)) {
        $itunes_body = json_decode(wp_remote_retrieve_body($itunes_response), true);
        $artist_debug['itunes_response'] = $itunes_body;

        // Debug: iTunes yanıtını logla
        error_log('iTunes API Response: ' . print_r($itunes_body, true));

        if (isset($itunes_body['results'][0]['artistName'])) {
            $artist_name = sanitize_text_field($itunes_body['results'][0]['artistName']);
            update_post_meta($post_id, '_artist_name', $artist_name);

            $artist_debug['artist_name'] = $artist_name;

            // Singer taxonomy'sine ekle
            $singer_term = term_exists($artist_name, 'singer');
            if (!$singer_term) {
                $singer_term = wp_insert_term($artist_name, 'singer');
            }

            $artist_debug['singer_term'] = $singer_term;

            if (!is_wp_error($singer_term)) {
                $term_set_result = wp_set_object_terms($post_id, (int)$singer_term['term_id'], 'singer', false);
                $artist_debug['term_set_result'] = $term_set_result;
                $artist_saved = true;
            } else {
                $artist_debug['singer_term_error'] = $singer_term->get_error_message();
            }
        } else {
            $artist_debug['error'] = 'artistName not found in iTunes response';
        }
    } else {
        $artist_debug['itunes_error'] = $itunes_response->get_error_message();
    }

    // OG veya meta etiketlerinden görsel URL'sini çıkar
    preg_match('/<meta property="og:image" content="([^"]+)"/', $body, $matches);

    if (empty($matches[1])) {
        // Alternatif olarak meta image etiketini kontrol et
        preg_match('/<meta name="twitter:image" content="([^"]+)"/', $body, $matches);
        
        if (empty($matches[1])) {
            // Apple Music'ten görsel alamadık, şimdi alternatif bir yaklaşım deneyelim
            // iTunes API'sini deneyelim (bu resmi bir API değil ama genellikle çalışır)
            $itunes_url = "https://itunes.apple.com/lookup?id={$apple_music_id}&entity=song";
            $itunes_response = wp_remote_get($itunes_url);
            
            if (is_wp_error($itunes_response)) {
                wp_send_json_error('iTunes API bağlantı hatası: ' . $itunes_response->get_error_message());
                return;
            }
            
            $itunes_body = json_decode(wp_remote_retrieve_body($itunes_response), true);

            if (isset($itunes_body['results'][0]['artworkUrl100'])) {
                $small_cover_url = $itunes_body['results'][0]['artworkUrl100'];
                // 100x100 yerine daha büyük bir sürümü almak için URL'yi değiştir
                $cover_url = str_replace('100x100', '1400x1400', $small_cover_url);
            } else {
                wp_send_json_error('Apple Music kapak görseli bulunamadı.');
                return;
            }
        } else {
            $cover_url = $matches[1];
        }
    } else {
        $cover_url = $matches[1];
    }
    
    // URL'yi daha yüksek çözünürlüklü bir versiyona dönüştür (varsa)
    // Apple Music görselleri genellikle belirli boyutlarda gelir, daha büyük sürümleri almak için URL'yi düzenleyebiliriz
    $cover_url = str_replace('/200x200', '/1400x1400', $cover_url);
    
    // WordPress yükleme işlemlerini kullanabilmek için gerekli dosyaları dahil et
    if (!function_exists('media_sideload_image')) {
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
    }
    
    // Görseli indir ve media library'e ekle
    $description = 'Apple Music albüm kapağı - ' . $apple_music_id;
    $image_id = media_sideload_image($cover_url, $post_id, $description, 'id');
    
    if (is_wp_error($image_id)) {
        wp_send_json_error('Kapak görseli indirilemedi: ' . $image_id->get_error_message());
        return;
    }
    
    // Featured image olarak ayarla
    set_post_thumbnail($post_id, $image_id);

    $success_message = 'Kapak görseli başarıyla ayarlandı.';
    if ($artist_saved) {
        $success_message .= ' Sanatçı bilgisi de kaydedildi.';
    }

    wp_send_json_success(array(
        'message' => $success_message,
        'artist_debug' => $artist_debug,
        'artist_saved' => $artist_saved
    ));
}
add_action('wp_ajax_fetch_apple_music_cover_art', 'gufte_ajax_fetch_apple_music_cover_art');

// Otomatik olarak Apple Music URL'sinden veya embed kodundan albüm kapağını almaya çalış
function gufte_auto_fetch_music_cover_art($post_id) {
    // Zaten featured image varsa veya otomatik kayıtsa atla
    if (has_post_thumbnail($post_id) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
        return;
    }
    
    // Apple Music URL kontrolü
    $apple_music_url = get_post_meta($post_id, 'apple_music_url', true);
    if (!empty($apple_music_url)) {
        // Apple Music ID'sini URL'den çıkar
        preg_match('/i=(\d+)/', $apple_music_url, $matches);
        
        if (!empty($matches[1])) {
            $apple_music_id = $matches[1];
            update_post_meta($post_id, '_apple_music_id', $apple_music_id);
            
            // Kapak görselini almak için ajax fonksiyonunu manuel olarak çalıştır
            $_POST['post_id'] = $post_id;
            $_POST['apple_music_id'] = $apple_music_id;
            $_POST['nonce'] = wp_create_nonce('gufte_music_cover_art_nonce');
            
            // Görseli al (output'u engelle)
            ob_start();
            gufte_ajax_fetch_apple_music_cover_art();
            ob_end_clean();
            
            return;
        }
    }
    
    // Apple Music embed kontrolü
    $apple_music_embed = get_post_meta($post_id, 'apple_music_embed', true);
    if (!empty($apple_music_embed)) {
        // Embed kodundan Apple Music ID'sini çıkar
        preg_match('/i=(\d+)/', $apple_music_embed, $matches);
        
        if (!empty($matches[1])) {
            $apple_music_id = $matches[1];
            update_post_meta($post_id, '_apple_music_id', $apple_music_id);
            
            // Kapak görselini almak için ajax fonksiyonunu manuel olarak çalıştır
            $_POST['post_id'] = $post_id;
            $_POST['apple_music_id'] = $apple_music_id;
            $_POST['nonce'] = wp_create_nonce('gufte_music_cover_art_nonce');
            
            // Görseli al (output'u engelle)
            ob_start();
            gufte_ajax_fetch_apple_music_cover_art();
            ob_end_clean();
            
            return;
        }
    }
    
    // Hiçbir müzik bilgisi bulunamadıysa, YouTube yöntemine dön
    if (function_exists('scan_post_content_for_youtube')) {
        scan_post_content_for_youtube($post_id);
    }
}

// Post kaydedildiğinde otomatik kapak görseli almayı dene
add_action('save_post', 'gufte_auto_fetch_music_cover_art', 20); // YouTube işleminden sonra çalışacak şekilde önceliği 20 olarak ayarladık


// Credits System (Producer, Songwriter, Composer)
require get_template_directory() . '/inc/features/credits-system.php';
// Awards System
require get_template_directory() . '/inc/features/awards-system.php';
// Historical Releases
require_once get_template_directory() . '/inc/features/historical-releases.php';
// functions.php'ye ekleyin:
require_once get_template_directory() . '/inc/schema/single-post-schema.php';
// Schema dosyalarını yükle
require_once get_template_directory() . '/inc/schema/singer-taxonomy-schema.php';
// Album Schema dosyalarını yükle
require_once get_template_directory() . '/inc/schema/album-taxonomy-schema.php';
// Inline Icons Helper
require_once get_template_directory() . '/inc/utilities/inline-icons.php';

/**
 * Theme setup
 */
function gufte_setup() {
    // Add default posts and comments RSS feed links to head
    add_theme_support('automatic-feed-links');

    // Let WordPress manage the document title
    add_theme_support('title-tag');

    // Enable support for Post Thumbnails on posts and pages
    add_theme_support('post-thumbnails');

    // Register nav menus
    register_nav_menus(
        array(
            'primary' => esc_html__('Primary Menu', 'gufte'),
            'footer'  => esc_html__('Footer Menu', 'gufte'),
        )
    );

    // Switch default core markup to output valid HTML5
    add_theme_support(
        'html5',
        array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script',
        )
    );

    // Add theme support for Custom Logo
    add_theme_support(
        'custom-logo',
        array(
            'height'      => 250,
            'width'       => 250,
            'flex-width'  => true,
            'flex-height' => true,
        )
    );

    // Add support for Block Styles
    add_theme_support('wp-block-styles');

    // Add support for editor styles
    add_theme_support('editor-styles');

    // Enqueue editor styles
    add_editor_style('assets/css/editor-style.css');
}
add_action('after_setup_theme', 'gufte_setup');

/**
 * Enqueue scripts and styles
 * CDN'ler header.php'de doğrudan yüklenecek
 */
function gufte_scripts() {
    // Google Fonts - Inter for modern typography
    wp_enqueue_style(
        'google-fonts-inter',
        'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap',
        array(),
        null
    );

    // Theme main CSS - Preload for non-blocking
    wp_enqueue_style(
        'gufte-style',
        get_stylesheet_uri(),
        array('google-fonts-inter'),
        GUFTE_VERSION,
        'all'
    );

    // Theme main JS
    wp_enqueue_script(
        'gufte-script',
        GUFTE_URI . '/assets/js/main.js',
        array('jquery'),
        GUFTE_VERSION,
        true
    );

    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }

    if (is_singular('post')) {
        wp_enqueue_script(
            'gufte-translation-switcher',
            GUFTE_URI . '/assets/js/translation-switcher.js',
            array(),
            GUFTE_VERSION,
            true
        );

        $current_lang = isset($_GET['lang']) ? sanitize_text_field(wp_unslash($_GET['lang'])) : '';

        $post_id = get_queried_object_id();

        wp_localize_script(
            'gufte-translation-switcher',
            'gufteTranslation',
            array(
                'restUrl'   => esc_url_raw(rest_url('gufte/v1/lyrics/')),
                'postId'    => $post_id,
                'permalink' => get_permalink($post_id),
                'currentLang' => $current_lang,
                'selectors' => array(
                    'content'   => '.lyrics-content .entry-content',
                    'container' => '.lyrics-content'
                ),
            )
        );
    }
}
add_action('wp_enqueue_scripts', 'gufte_scripts');

/**
 * Add preload for stylesheet
 */
function gufte_add_preload_styles() {
    ?>
    <link rel="preload" href="<?php echo get_stylesheet_uri(); ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="<?php echo get_stylesheet_uri(); ?>"></noscript>
    <?php
}
add_action('wp_head', 'gufte_add_preload_styles', 1);

/**
 * Remove original stylesheet link (we're using preload instead)
 */
function gufte_remove_blocking_styles() {
    wp_dequeue_style('gufte-style');
}
add_action('wp_enqueue_scripts', 'gufte_remove_blocking_styles', 100);

/**
 * Add Tailwind CSS classes to WordPress elements
 */

// Add Tailwind classes to menu items
function gufte_nav_menu_css_class($classes, $item, $args, $depth) {
    if (isset($args->theme_location)) {
        if ($args->theme_location === 'primary') {
            $classes[] = 'inline-block px-4 py-2 text-gray-800 hover:text-gray-600 transition-colors duration-300';
        }

        if ($args->theme_location === 'footer') {
            $classes[] = 'inline-block mr-4 text-gray-500 hover:text-gray-700 transition-colors duration-300';
        }
    }
    
    return $classes;
}
add_filter('nav_menu_css_class', 'gufte_nav_menu_css_class', 10, 4);

// Add Tailwind classes to comment form fields
function gufte_comment_form_defaults($defaults) {
    if (isset($defaults['comment_field'])) {
        $defaults['comment_field'] = str_replace(
            'textarea',
            'textarea class="w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"',
            $defaults['comment_field']
        );
    }
    
    return $defaults;
}
add_filter('comment_form_defaults', 'gufte_comment_form_defaults');

function gufte_comment_form_fields($fields) {
    if (is_array($fields)) {
        foreach ($fields as &$field) {
            $field = str_replace(
                'input',
                'input class="w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"',
                $field
            );
        }
    }
    
    return $fields;
}
add_filter('comment_form_default_fields', 'gufte_comment_form_fields');

// Add Tailwind classes to buttons
function gufte_add_button_classes($content) {
    if (is_string($content)) {
        $content = str_replace('wp-element-button', 'wp-element-button inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-300', $content);
    }
    
    return $content;
}
add_filter('the_content', 'gufte_add_button_classes');

/**
 * Register widget area.
 */
function gufte_widgets_init() {
    register_sidebar(
        array(
            'name'          => esc_html__( 'Sidebar', 'gufte' ),
            'id'            => 'sidebar-1',
            'description'   => esc_html__( 'Add widgets here to appear in your sidebar.', 'gufte' ),
            'before_widget' => '<section id="%1$s" class="widget mb-8 %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h2 class="widget-title text-xl font-bold mb-4 text-gray-900">',
            'after_title'   => '</h2>',
        )
    );

    register_sidebar(
        array(
            'name'          => esc_html__( 'Footer 1', 'gufte' ),
            'id'            => 'footer-1',
            'description'   => esc_html__( 'Add widgets here to appear in first footer column.', 'gufte' ),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h2 class="widget-title text-lg font-bold mb-4 text-white">',
            'after_title'   => '</h2>',
        )
    );

    register_sidebar(
        array(
            'name'          => esc_html__( 'Footer 2', 'gufte' ),
            'id'            => 'footer-2',
            'description'   => esc_html__( 'Add widgets here to appear in second footer column.', 'gufte' ),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h2 class="widget-title text-lg font-bold mb-4 text-white">',
            'after_title'   => '</h2>',
        )
    );

    register_sidebar(
        array(
            'name'          => esc_html__( 'Footer 3', 'gufte' ),
            'id'            => 'footer-3',
            'description'   => esc_html__( 'Add widgets here to appear in third footer column.', 'gufte' ),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h2 class="widget-title text-lg font-bold mb-4 text-white">',
            'after_title'   => '</h2>',
        )
    );

    register_sidebar(
        array(
            'name'          => esc_html__( 'Footer 4', 'gufte' ),
            'id'            => 'footer-4',
            'description'   => esc_html__( 'Add widgets here to appear in fourth footer column.', 'gufte' ),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h2 class="widget-title text-lg font-bold mb-4 text-white">',
            'after_title'   => '</h2>',
        )
    );
}
add_action( 'widgets_init', 'gufte_widgets_init' );


/**
 * Şarkıcı ve Albüm taksonomilerini kaydet
 */
function gufte_register_music_taxonomies() {
    // Şarkıcı taksonomisi
    $singer_labels = array(
        'name'                       => _x('Artists', 'taxonomy general name', 'gufte'),
        'singular_name'              => _x('Artist', 'taxonomy singular name', 'gufte'),
        'search_items'               => __('Search Artists', 'gufte'),
        'popular_items'              => __('Popular Artists', 'gufte'),
        'all_items'                  => __('All Artists', 'gufte'),
        'edit_item'                  => __('Edit Artist', 'gufte'),
        'update_item'                => __('Update Artist', 'gufte'),
        'add_new_item'               => __('Add New Artist', 'gufte'),
        'new_item_name'              => __('New Artist Name', 'gufte'),
        'separate_items_with_commas' => __('Separate artists with commas', 'gufte'),
        'add_or_remove_items'        => __('Add or Remove Artists', 'gufte'),
        'choose_from_most_used'      => __('Choose from most used artists', 'gufte'),
        'menu_name'                  => __('Artists', 'gufte'),
    );

// Şarkıcı taksonomisi - rewrite parametresini güncelle
$singer_args = array(
    'labels'            => $singer_labels,
    'hierarchical'      => false,
    'public'            => true,
    'show_ui'           => true,
    'show_admin_column' => true,
    'show_in_nav_menus' => true,
    'show_tagcloud'     => true,
    'query_var'         => true,
    'rewrite'           => array('slug' => 'singer', 'with_front' => true),
    'show_in_rest'      => true,
);


    register_taxonomy('singer', 'lyrics', $singer_args);

    // Albüm taksonomisi
    $album_labels = array(
        'name'                       => _x('Albums', 'taxonomy general name', 'gufte'),
        'singular_name'              => _x('Album', 'taxonomy singular name', 'gufte'),
        'search_items'               => __('Search Albums', 'gufte'),
        'popular_items'              => __('Popular Albums', 'gufte'),
        'all_items'                  => __('All Albums', 'gufte'),
        'edit_item'                  => __('Edit Album', 'gufte'),
        'update_item'                => __('Update Album', 'gufte'),
        'add_new_item'               => __('Add New Album', 'gufte'),
        'new_item_name'              => __('New Album Name', 'gufte'),
        'separate_items_with_commas' => __('Separate albums with commas', 'gufte'),
        'add_or_remove_items'        => __('Add or Remove Albums', 'gufte'),
        'choose_from_most_used'      => __('Choose from most used albums', 'gufte'),
        'menu_name'                  => __('Albums', 'gufte'),
    );

// Albüm taksonomisi - rewrite parametresini güncelle
$album_args = array(
    'labels'            => $album_labels,
    'hierarchical'      => false,
    'public'            => true,
    'show_ui'           => true,
    'show_admin_column' => true,
    'show_in_nav_menus' => true,
    'show_tagcloud'     => true,
    'query_var'         => true,
    'rewrite'           => array('slug' => 'album', 'with_front' => true),
    'show_in_rest'      => true,
);

    register_taxonomy('album', 'lyrics', $album_args);
    
    // Albüm taksonomisine özel alanlar ekle
    register_term_meta('album', 'album_year', array(
        'type' => 'string',
        'description' => 'Albümün yayınlanma yılı',
        'single' => true,
        'show_in_rest' => true,
    ));
}
add_action('init', 'gufte_register_music_taxonomies');



/**
 * Albüm taksonomisine "Yıl" özel alanı ekle
 */
function gufte_add_album_custom_fields($term) {
    // Eğer bu albüm taksonomisi ise
    if ('album' !== $term->taxonomy) {
        return;
    }
    
    // Mevcut değeri al
    $album_year = get_term_meta($term->term_id, 'album_year', true);
    ?>
    <tr class="form-field">
        <th scope="row">
            <label for="album_year"><?php _e('Albüm Yılı', 'gufte'); ?></label>
        </th>
        <td>
            <input type="text" name="album_year" id="album_year" value="<?php echo esc_attr($album_year); ?>" />
            <p class="description"><?php _e('Albümün yayınlanma yılını girin (örn. 2023)', 'gufte'); ?></p>
        </td>
    </tr>
    <?php
}
add_action('album_edit_form_fields', 'gufte_add_album_custom_fields');

// Yeni albüm ekleme formuna da özel alan ekle
function gufte_add_new_album_custom_fields() {
    ?>
    <div class="form-field">
        <label for="album_year"><?php _e('Albüm Yılı', 'gufte'); ?></label>
        <input type="text" name="album_year" id="album_year" />
        <p class="description"><?php _e('Albümün yayınlanma yılını girin (örn. 2023)', 'gufte'); ?></p>
    </div>
    <?php
}
add_action('album_add_form_fields', 'gufte_add_new_album_custom_fields');

// Albüm özel alanlarını kaydet
function gufte_save_album_custom_fields($term_id) {
    if (isset($_POST['album_year'])) {
        update_term_meta(
            $term_id,
            'album_year',
            sanitize_text_field($_POST['album_year'])
        );
    }
}
add_action('created_album', 'gufte_save_album_custom_fields');
add_action('edited_album', 'gufte_save_album_custom_fields');

/**
 * Şarkıcı taksonomisine görsel ekleme özelliği
 */

// Şarkıcı taksonomisine görsel alanı ekle
function gufte_add_singer_image_field($term) {
    if ('singer' !== $term->taxonomy) {
        return;
    }
    
    // Mevcut görsel ID'sini al
    $image_id = get_term_meta($term->term_id, 'singer_image_id', true);
    $image_url = '';
    
    if ($image_id) {
        $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
    }
    ?>
    <tr class="form-field term-singer-image-wrap">
        <th scope="row">
            <label for="singer-image"><?php _e('Şarkıcı Görseli', 'gufte'); ?></label>
        </th>
        <td>
            <div class="singer-image-container">
                <?php if ($image_url) : ?>
                <div class="singer-image-preview" style="margin-bottom: 10px;">
                    <img src="<?php echo esc_url($image_url); ?>" style="max-width: 150px; height: auto;" />
                </div>
                <?php endif; ?>
                
                <input type="hidden" id="singer_image_id" name="singer_image_id" value="<?php echo esc_attr($image_id); ?>" />
                <button type="button" class="button button-secondary singer-upload-image" id="singer-upload-image">
                    <?php _e('Görsel Seç', 'gufte'); ?>
                </button>
                
                <?php if ($image_id) : ?>
                <button type="button" class="button button-secondary singer-remove-image" id="singer-remove-image">
                    <?php _e('Görseli Kaldır', 'gufte'); ?>
                </button>
                <?php endif; ?>
            </div>
            
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Media Uploader
                var file_frame;
                
                // Görsel seçme butonu tıklaması
                $('#singer-upload-image').on('click', function(e) {
                    e.preventDefault();
                    
                    // Eğer Media Uploader örneği zaten varsa, tekrar açalım
                    if (file_frame) {
                        file_frame.open();
                        return;
                    }
                    
                    // Yeni bir Media Uploader örneği oluşturalım
                    file_frame = wp.media.frames.file_frame = wp.media({
                        title: '<?php _e('Şarkıcı Görseli Seç', 'gufte'); ?>',
                        button: {
                            text: '<?php _e('Görseli Kullan', 'gufte'); ?>'
                        },
                        multiple: false
                    });
                    
                    // Bir dosya seçildiğinde
                    file_frame.on('select', function() {
                        var attachment = file_frame.state().get('selection').first().toJSON();
                        
                        // Görseli önizleme olarak göster
                        if ($('.singer-image-preview').length) {
                            $('.singer-image-preview img').attr('src', attachment.sizes.thumbnail.url);
                        } else {
                            $('.singer-image-container').prepend('<div class="singer-image-preview" style="margin-bottom: 10px;"><img src="' + attachment.sizes.thumbnail.url + '" style="max-width: 150px; height: auto;" /></div>');
                        }
                        
                        // Görsel ID'sini gizli alana yaz
                        $('#singer_image_id').val(attachment.id);
                        
                        // Görseli kaldır butonunu göster
                        if (!$('#singer-remove-image').length) {
                            $('#singer-upload-image').after('<button type="button" class="button button-secondary singer-remove-image" id="singer-remove-image"><?php _e('Görseli Kaldır', 'gufte'); ?></button>');
                        }
                    });
                    
                    // Media Uploader'ı aç
                    file_frame.open();
                });
                
                // Görseli kaldır butonu (delegated event)
                $(document).on('click', '#singer-remove-image', function(e) {
                    e.preventDefault();
                    
                    // Önizleme görseli ve gizli ID'yi temizle
                    $('.singer-image-preview').remove();
                    $('#singer_image_id').val('');
                    $(this).remove();
                });
            });
            </script>
        </td>
    </tr>
    <?php
}
add_action('singer_edit_form_fields', 'gufte_add_singer_image_field');

// Yeni şarkıcı ekleme formuna da görsel alanı ekle
function gufte_add_new_singer_image_field() {
    ?>
    <div class="form-field term-singer-image-wrap">
        <label for="singer-image"><?php _e('Şarkıcı Görseli', 'gufte'); ?></label>
        
        <div class="singer-image-container">
            <input type="hidden" id="singer_image_id" name="singer_image_id" value="" />
            <button type="button" class="button button-secondary singer-upload-image" id="singer-upload-image">
                <?php _e('Görsel Seç', 'gufte'); ?>
            </button>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Media Uploader
            var file_frame;
            
            // Görsel seçme butonu tıklaması
            $('#singer-upload-image').on('click', function(e) {
                e.preventDefault();
                
                // Eğer Media Uploader örneği zaten varsa, tekrar açalım
                if (file_frame) {
                    file_frame.open();
                    return;
                }
                
                // Yeni bir Media Uploader örneği oluşturalım
                file_frame = wp.media.frames.file_frame = wp.media({
                    title: '<?php _e('Şarkıcı Görseli Seç', 'gufte'); ?>',
                    button: {
                        text: '<?php _e('Görseli Kullan', 'gufte'); ?>'
                    },
                    multiple: false
                });
                
                // Bir dosya seçildiğinde
                file_frame.on('select', function() {
                    var attachment = file_frame.state().get('selection').first().toJSON();
                    
                    // Görseli önizleme olarak göster
                    if ($('.singer-image-preview').length) {
                        $('.singer-image-preview img').attr('src', attachment.sizes.thumbnail.url);
                    } else {
                        $('.singer-image-container').prepend('<div class="singer-image-preview" style="margin-bottom: 10px;"><img src="' + attachment.sizes.thumbnail.url + '" style="max-width: 150px; height: auto;" /></div>');
                    }
                    
                    // Görsel ID'sini gizli alana yaz
                    $('#singer_image_id').val(attachment.id);
                    
                    // Görseli kaldır butonunu göster
                    if (!$('#singer-remove-image').length) {
                        $('#singer-upload-image').after('<button type="button" class="button button-secondary singer-remove-image" id="singer-remove-image"><?php _e('Görseli Kaldır', 'gufte'); ?></button>');
                    }
                });
                
                // Media Uploader'ı aç
                file_frame.open();
            });
            
            // Görseli kaldır butonu (delegated event)
            $(document).on('click', '#singer-remove-image', function(e) {
                e.preventDefault();
                
                // Önizleme görseli ve gizli ID'yi temizle
                $('.singer-image-preview').remove();
                $('#singer_image_id').val('');
                $(this).remove();
            });
        });
        </script>
    </div>
    <?php
}
add_action('singer_add_form_fields', 'gufte_add_new_singer_image_field');

// Media Uploader için gerekli script'leri yükle
function gufte_admin_enqueue_media_scripts() {
    $screen = get_current_screen();
    
    // Sadece şarkıcı/albüm taksonomi sayfalarında yükle
    if (!$screen || !in_array($screen->taxonomy, array('singer', 'album'))) {
        return;
    }
    
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'gufte_admin_enqueue_media_scripts');

// Şarkıcı görsel bilgisini kaydet
function gufte_save_singer_image_field($term_id) {
    if (isset($_POST['singer_image_id'])) {
        update_term_meta(
            $term_id,
            'singer_image_id',
            absint($_POST['singer_image_id'])
        );
    }
}
add_action('created_singer', 'gufte_save_singer_image_field');
add_action('edited_singer', 'gufte_save_singer_image_field');

// Şarkıcı görselini getirmek için yardımcı fonksiyon
function gufte_get_singer_image($term_id, $size = 'thumbnail') {
    $image_id = get_term_meta($term_id, 'singer_image_id', true);
    
    if ($image_id) {
        return wp_get_attachment_image($image_id, $size, false, array('class' => 'singer-image'));
    }
    
    return '';
}

/**
 * Şarkıcı taksonomisine gerçek ad alanı ekle
 */
function gufte_add_singer_real_name_field() {
    // Şarkıcı taksonomisine gerçek ad meta alanı
    register_term_meta('singer', 'real_name', array(
        'type' => 'string',
        'description' => 'Şarkıcının gerçek adı',
        'single' => true,
        'show_in_rest' => true,
    ));
}
add_action('init', 'gufte_add_singer_real_name_field');

/**
 * Şarkıcı düzenleme formuna gerçek ad alanı ekle
 */
function gufte_add_singer_real_name_edit_field($term) {
    // Sadece şarkıcı taksonomisi için
    if ('singer' !== $term->taxonomy) {
        return;
    }
    
    // Mevcut değeri al
    $real_name = get_term_meta($term->term_id, 'real_name', true);
    ?>
    <tr class="form-field">
        <th scope="row">
            <label for="real_name"><?php _e('Gerçek Adı', 'gufte'); ?></label>
        </th>
        <td>
            <input type="text" name="real_name" id="real_name" value="<?php echo esc_attr($real_name); ?>" />
            <p class="description"><?php _e('Şarkıcının gerçek adını girin (varsa)', 'gufte'); ?></p>
        </td>
    </tr>
    <?php
}
add_action('singer_edit_form_fields', 'gufte_add_singer_real_name_edit_field');

/**
 * Yeni şarkıcı ekleme formuna da gerçek ad alanı ekle
 */
function gufte_add_new_singer_real_name_field() {
    ?>
    <div class="form-field">
        <label for="real_name"><?php _e('Gerçek Adı', 'gufte'); ?></label>
        <input type="text" name="real_name" id="real_name" />
        <p class="description"><?php _e('Şarkıcının gerçek adını girin (varsa)', 'gufte'); ?></p>
    </div>
    <?php
}
add_action('singer_add_form_fields', 'gufte_add_new_singer_real_name_field');

/**
 * Şarkıcı gerçek ad bilgisini kaydet
 */
function gufte_save_singer_real_name_field($term_id) {
    if (isset($_POST['real_name'])) {
        update_term_meta(
            $term_id,
            'real_name',
            sanitize_text_field($_POST['real_name'])
        );
    }
}
add_action('created_singer', 'gufte_save_singer_real_name_field');
add_action('edited_singer', 'gufte_save_singer_real_name_field');

/**
 * Albüm taksonomisine şarkıcı seçme alanı ekle
 */
function gufte_add_album_singer_field($term) {
    // Eğer bu albüm taksonomisi ise
    if ('album' !== $term->taxonomy) {
        return;
    }
    
    // Mevcut şarkıcı ilişkisini al
    $album_singers = get_term_meta($term->term_id, 'album_singers', true);
    if (!is_array($album_singers)) {
        $album_singers = array();
    }
    
    // Tüm şarkıcıları al
    $all_singers = get_terms(array(
        'taxonomy' => 'singer',
        'hide_empty' => false,
    ));
    
    if (empty($all_singers) || is_wp_error($all_singers)) {
        return;
    }
    ?>
    <tr class="form-field">
        <th scope="row">
            <label for="album_singers"><?php _e('Albüm Şarkıcıları', 'gufte'); ?></label>
        </th>
        <td>
            <select name="album_singers[]" id="album_singers" class="postform" multiple="multiple" style="width: 95%; height: 150px;">
                <?php foreach ($all_singers as $singer) : ?>
                <option value="<?php echo esc_attr($singer->term_id); ?>" <?php echo in_array($singer->term_id, $album_singers) ? 'selected="selected"' : ''; ?>>
                    <?php echo esc_html($singer->name); ?>
                </option>
                <?php endforeach; ?>
            </select>
            <p class="description"><?php _e('Bu albüme ait şarkıcıları seçin. Birden fazla seçmek için CTRL tuşunu basılı tutarak tıklayın.', 'gufte'); ?></p>
            <p class="description"><?php _e('Bu ilişki, albüm sayfasında şarkıcıları listelemek ve şarkıcılara göre filtreleme yapmak için kullanılacaktır.', 'gufte'); ?></p>
        </td>
    </tr>
    <?php
}
add_action('album_edit_form_fields', 'gufte_add_album_singer_field');

// Yeni albüm ekleme formuna da şarkıcı seçme alanı ekle
function gufte_add_new_album_singer_field() {
    // Tüm şarkıcıları al
    $all_singers = get_terms(array(
        'taxonomy' => 'singer',
        'hide_empty' => false,
    ));
    
    if (empty($all_singers) || is_wp_error($all_singers)) {
        return;
    }
    ?>
    <div class="form-field">
        <label for="album_singers"><?php _e('Albüm Şarkıcıları', 'gufte'); ?></label>
        <select name="album_singers[]" id="album_singers" class="postform" multiple="multiple" style="width: 95%; height: 150px;">
            <?php foreach ($all_singers as $singer) : ?>
            <option value="<?php echo esc_attr($singer->term_id); ?>">
                <?php echo esc_html($singer->name); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <p class="description"><?php _e('Bu albüme ait şarkıcıları seçin. Birden fazla seçmek için CTRL tuşunu basılı tutarak tıklayın.', 'gufte'); ?></p>
    </div>
    <?php
}
add_action('album_add_form_fields', 'gufte_add_new_album_singer_field');

// Albüm şarkıcı ilişkisini kaydet
function gufte_save_album_singer_field($term_id) {
    if (isset($_POST['album_singers'])) {
        $album_singers = array_map('intval', (array) $_POST['album_singers']);
        update_term_meta($term_id, 'album_singers', $album_singers);
    } else {
        // Eğer hiçbir şarkıcı seçilmemişse, boş dizi kaydet
        update_term_meta($term_id, 'album_singers', array());
    }
}
add_action('created_album', 'gufte_save_album_singer_field');
add_action('edited_album', 'gufte_save_album_singer_field');

/**
 * Albümün şarkıcılarını getirir (taxonomy-album.php ve page-albums.php için)
 * Albümle ilişkili şarkıların singer taksonomisinden şarkıcıları çeker
 *
 * @param int $album_id Albümün term ID'si
 * @return array Şarkıcıların (term) dizisi
 */
function gufte_get_album_singers($album_id) {
    // Albümle ilişkili şarkıları al
    $singer_query = new WP_Query(array(
        'post_type' => 'post',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'tax_query' => array(
            array(
                'taxonomy' => 'album',
                'field' => 'term_id',
                'terms' => $album_id,
            ),
        ),
        'no_found_rows' => true,
    ));

    $singer_ids = array();
    if ($singer_query->have_posts()) {
        foreach ($singer_query->posts as $post_id) {
            $singers = get_the_terms($post_id, 'singer');
            if ($singers && !is_wp_error($singers)) {
                foreach ($singers as $singer) {
                    $singer_ids[$singer->term_id] = $singer;
                }
            }
        }
    }

    wp_reset_postdata();
    return array_values($singer_ids);
}

/**
 * Şarkıcı sayfasında ilgili albümleri gösterme (taxonomy-singer.php)
 * Bu fonksiyonu taxonomy-singer.php şablonunda kullanabilirsiniz
 */
function gufte_get_singer_albums($singer_id) {
    // Tüm albümleri al
    $all_albums = get_terms(array(
        'taxonomy' => 'album',
        'hide_empty' => false,
    ));
    
    $singer_albums = array();
    
    if (!empty($all_albums) && !is_wp_error($all_albums)) {
        foreach ($all_albums as $album) {
            $album_singers = get_term_meta($album->term_id, 'album_singers', true);
            
            // Eğer bu şarkıcı, albümün şarkıcıları arasında ise
            if (is_array($album_singers) && in_array($singer_id, $album_singers)) {
                $singer_albums[] = $album;
            }
        }
    }
    
    return $singer_albums;
}

/**
 * Albüm düzenleme/ekleme formu için CSS ekle
 */
function gufte_taxonomy_admin_styles() {
    $screen = get_current_screen();
    
    // Sadece albüm taksonomi sayfasında yükle
    if (!$screen || 'album' !== $screen->taxonomy) {
        return;
    }
    
    ?>
    <style type="text/css">
    .form-field select[multiple] {
        height: 150px !important;
    }
    </style>
    <?php
}
add_action('admin_head', 'gufte_taxonomy_admin_styles');

/**
 * Şarkıcı taksonomisine doğum ve ölüm tarihi, doğum yeri alanları ekle
 */
function gufte_add_singer_date_fields() {
    // Şarkıcı taksonomisine doğum tarihi meta alanı
    register_term_meta('singer', 'birth_date', array(
        'type' => 'string',
        'description' => 'Şarkıcının doğum tarihi',
        'single' => true,
        'show_in_rest' => true,
    ));
    
    // Şarkıcı taksonomisine ölüm tarihi meta alanı (varsa)
    register_term_meta('singer', 'death_date', array(
        'type' => 'string',
        'description' => 'Şarkıcının ölüm tarihi (varsa)',
        'single' => true,
        'show_in_rest' => true,
    ));
    
    // Şarkıcı taksonomisine doğum yeri meta alanı
    register_term_meta('singer', 'birth_place', array(
        'type' => 'string',
        'description' => 'Şarkıcının doğum yeri (şehir/kasaba)',
        'single' => true,
        'show_in_rest' => true,
    ));
    
    // Şarkıcı taksonomisine doğduğu ülke meta alanı
    register_term_meta('singer', 'birth_country', array(
        'type' => 'string',
        'description' => 'Şarkıcının doğduğu ülke',
        'single' => true,
        'show_in_rest' => true,
    ));
}
add_action('init', 'gufte_add_singer_date_fields');

/**
 * Şarkıcı düzenleme formuna doğum/ölüm tarihi ve doğum yeri alanları ekle
 */
function gufte_add_singer_date_edit_fields($term) {
    // Sadece şarkıcı taksonomisi için
    if ('singer' !== $term->taxonomy) {
        return;
    }
    
    // Mevcut değerleri al
    $birth_date = get_term_meta($term->term_id, 'birth_date', true);
    $death_date = get_term_meta($term->term_id, 'death_date', true);
    $birth_place = get_term_meta($term->term_id, 'birth_place', true);
    $birth_country = get_term_meta($term->term_id, 'birth_country', true);
    ?>
    <tr class="form-field">
        <th scope="row">
            <label for="birth_date"><?php _e('Doğum Tarihi', 'gufte'); ?></label>
        </th>
        <td>
            <input type="date" name="birth_date" id="birth_date" value="<?php echo esc_attr($birth_date); ?>" />
            <p class="description"><?php _e('Şarkıcının doğum tarihini girin (YYYY-MM-DD formatında)', 'gufte'); ?></p>
        </td>
    </tr>
    
    <tr class="form-field">
        <th scope="row">
            <label for="birth_place"><?php _e('Doğum Yeri', 'gufte'); ?></label>
        </th>
        <td>
            <input type="text" name="birth_place" id="birth_place" value="<?php echo esc_attr($birth_place); ?>" />
            <p class="description"><?php _e('Şarkıcının doğduğu şehir/kasaba', 'gufte'); ?></p>
        </td>
    </tr>
    
    <tr class="form-field">
        <th scope="row">
            <label for="birth_country"><?php _e('Doğduğu Ülke', 'gufte'); ?></label>
        </th>
        <td>
            <input type="text" name="birth_country" id="birth_country" value="<?php echo esc_attr($birth_country); ?>" />
            <p class="description"><?php _e('Şarkıcının doğduğu ülke', 'gufte'); ?></p>
        </td>
    </tr>
    
    <tr class="form-field">
        <th scope="row">
            <label for="death_date"><?php _e('Ölüm Tarihi', 'gufte'); ?></label>
        </th>
        <td>
            <input type="date" name="death_date" id="death_date" value="<?php echo esc_attr($death_date); ?>" />
            <p class="description"><?php _e('Şarkıcı vefat ettiyse, ölüm tarihini girin (YYYY-MM-DD formatında)', 'gufte'); ?></p>
        </td>
    </tr>
    <?php
}
add_action('singer_edit_form_fields', 'gufte_add_singer_date_edit_fields');

/**
 * Yeni şarkıcı ekleme formuna doğum/ölüm tarihi ve doğum yeri alanları ekle
 */
function gufte_add_new_singer_date_fields() {
    ?>
    <div class="form-field">
        <label for="birth_date"><?php _e('Doğum Tarihi', 'gufte'); ?></label>
        <input type="date" name="birth_date" id="birth_date" />
        <p class="description"><?php _e('Şarkıcının doğum tarihini girin (YYYY-MM-DD formatında)', 'gufte'); ?></p>
    </div>
    
    <div class="form-field">
        <label for="birth_place"><?php _e('Doğum Yeri', 'gufte'); ?></label>
        <input type="text" name="birth_place" id="birth_place" />
        <p class="description"><?php _e('Şarkıcının doğduğu şehir/kasaba', 'gufte'); ?></p>
    </div>
    
    <div class="form-field">
        <label for="birth_country"><?php _e('Doğduğu Ülke', 'gufte'); ?></label>
        <input type="text" name="birth_country" id="birth_country" />
        <p class="description"><?php _e('Şarkıcının doğduğu ülke', 'gufte'); ?></p>
    </div>
    
    <div class="form-field">
        <label for="death_date"><?php _e('Ölüm Tarihi', 'gufte'); ?></label>
        <input type="date" name="death_date" id="death_date" />
        <p class="description"><?php _e('Şarkıcı vefat ettiyse, ölüm tarihini girin (YYYY-MM-DD formatında)', 'gufte'); ?></p>
    </div>
    <?php
}
add_action('singer_add_form_fields', 'gufte_add_new_singer_date_fields');

/**
 * Şarkıcı bilgilerini kaydet
 */
function gufte_save_singer_date_fields($term_id) {
    if (isset($_POST['birth_date'])) {
        update_term_meta(
            $term_id,
            'birth_date',
            sanitize_text_field($_POST['birth_date'])
        );
    }
    
    if (isset($_POST['death_date'])) {
        update_term_meta(
            $term_id,
            'death_date',
            sanitize_text_field($_POST['death_date'])
        );
    }
    
    if (isset($_POST['birth_place'])) {
        update_term_meta(
            $term_id,
            'birth_place',
            sanitize_text_field($_POST['birth_place'])
        );
    }
    
    if (isset($_POST['birth_country'])) {
        update_term_meta(
            $term_id,
            'birth_country',
            sanitize_text_field($_POST['birth_country'])
        );
    }
}
add_action('created_singer', 'gufte_save_singer_date_fields');
add_action('edited_singer', 'gufte_save_singer_date_fields');

/**
 * Şarkıcının yaşını hesaplayan yardımcı fonksiyon
 */
function gufte_calculate_singer_age($birth_date, $death_date = '') {
    // Tarih formatını kontrol et
    if (empty($birth_date)) {
        return false;
    }
    
    // Doğum tarihini DateTime nesnesine dönüştür
    $birth = new DateTime($birth_date);
    
    // Eğer ölüm tarihi varsa, onunla hesapla
    if (!empty($death_date)) {
        $end_date = new DateTime($death_date);
        $interval = $birth->diff($end_date);
        return $interval->y; // Yıl cinsinden yaş
    }
    
    // Hayattaysa, bugünkü tarihle hesapla
    $today = new DateTime('now');
    $interval = $birth->diff($today);
    return $interval->y; // Yıl cinsinden yaş
}

/**
 * Returns the singer's life status and age in formatted form
 */
function gufte_get_singer_lifespan($term_id) {
    $birth_date = get_term_meta($term_id, 'birth_date', true);
    $death_date = get_term_meta($term_id, 'death_date', true);
    
    if (empty($birth_date)) {
        return '';
    }
    
    // Format birth date
    $birth_year = date('Y', strtotime($birth_date));
    
    // If death date exists
    if (!empty($death_date)) {
        $death_year = date('Y', strtotime($death_date));
        $age = gufte_calculate_singer_age($birth_date, $death_date);
        
        return sprintf(
            __('%s - %s (died at age %d)', 'gufte'),
            $birth_year,
            $death_year,
            $age
        );
    }
    
    // If still alive
    $age = gufte_calculate_singer_age($birth_date);
    return sprintf(
        __('Born %s (age %d)', 'gufte'),
        $birth_year,
        $age
    );
}


/**
 * Robots.txt'ye sitemap linkini ekle
 */
function gufte_add_multilingual_sitemap_to_robots($output, $public) {
    if ($public) {
        $output .= "\n# Multilingual Sitemap\n";
        $output .= "Sitemap: " . home_url('/?multilingual_sitemap=1') . "\n";
    }
    
    return $output;
}
add_filter('robots_txt', 'gufte_add_multilingual_sitemap_to_robots', 10, 2);

/**
 * Google Search Console için çok dilli URL bildirimi
 */
function gufte_ping_search_engines_for_multilingual() {
    $sitemap_url = home_url('/?multilingual_sitemap=1');
    
    // Google'a bildir
    $google_ping = 'http://www.google.com/ping?sitemap=' . urlencode($sitemap_url);
    wp_remote_get($google_ping, array('timeout' => 30));
    
    // Bing'e bildir
    $bing_ping = 'http://www.bing.com/ping?sitemap=' . urlencode($sitemap_url);
    wp_remote_get($bing_ping, array('timeout' => 30));
}

// Post güncellendiğinde arama motorlarını bilgilendir
add_action('save_post', function($post_id) {
    if (get_post_type($post_id) === 'post') {
        wp_schedule_single_event(time() + 300, 'gufte_ping_search_engines_for_multilingual');
    }
});

add_action('gufte_ping_search_engines_for_multilingual', 'gufte_ping_search_engines_for_multilingual');

/**
 * Dil seçeneklerini admin panelde göster (geliştirilmiş)
 */
function gufte_enhanced_language_meta_box_callback($post) {
    $settings = gufte_get_language_settings();
    $available_langs = get_post_meta($post->ID, '_available_languages', true) ?: [];
    
    wp_nonce_field('gufte_language_meta', 'gufte_language_nonce');
    
    echo '<div class="gufte-language-options">';
    echo '<p><strong>Bu şarkı için mevcut çeviri dilleri:</strong></p>';
    echo '<p class="description">Seçili diller sitemap\'e dahil edilecek ve arama motorları tarafından dizinlenecektir.</p>';
    
    echo '<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin: 15px 0;">';
    
    foreach ($settings['language_map'] as $lang_code => $lang_name) {
        $checked = in_array($lang_code, $available_langs) ? 'checked' : '';
        $preview_url = ($lang_code === 'english') 
            ? get_permalink($post->ID) 
            : add_query_arg('lang', $lang_code, get_permalink($post->ID));
        
        echo '<label style="display: flex; align-items: center; padding: 8px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">';
        echo '<input type="checkbox" name="available_languages[]" value="' . esc_attr($lang_code) . '" ' . $checked . ' style="margin-right: 8px;"> ';
        echo '<span style="flex: 1;">' . esc_html($lang_name) . '</span>';
        echo '<a href="' . esc_url($preview_url) . '" target="_blank" style="margin-left: 8px; text-decoration: none; color: #0073aa;">👁️</a>';
        echo '</label>';
    }
    
    echo '</div>';
    
    echo '<div style="margin-top: 15px; padding: 10px; background: #e7f3ff; border-left: 4px solid #0073aa;">';
    echo '<strong>SEO Bilgisi:</strong><br>';
    echo '• Seçili her dil için ayrı bir URL oluşturulacaktır<br>';
    echo '• Her dil sayfası kendi meta verilerine sahip olacaktır<br>';
    echo '• Sitemap\'e dahil edilecek ve arama motorları tarafından ayrı ayrı dizinlenecektir';
    echo '</div>';
    
    echo '</div>';
}

// Mevcut callback'i güncelle

/**
 * Çok dilli tabloları modern UI bileşeni olarak dönüştüren fonksiyon
 * - Global kebab menü (body içinde, portal yaklaşımı)
 * - Overflow/z-index sorunlarına tam bağışıklık
 * - Download/Copy hizalama fix
 */
function gufte_replace_multilingual_tables($matches) {
    // Tablo içeriğini al
    $table_content = $matches[1];

    // Post ID'yi al ve mevcut dilleri kontrol et
    $post_id = get_the_ID();
    $available_languages = get_post_meta($post_id, '_available_languages', true);
    if (!is_array($available_languages)) {
        $available_languages = array();
    }

    // Başlıkları (th) çıkar
    preg_match('/<thead>(.*?)<\/thead>/s', $table_content, $header_matches);
    $header = '';
    $language_names = [];

    if (!empty($header_matches)) {
        preg_match_all('/<th>(.*?)<\/th>/s', $header_matches[1], $column_matches);
        if (!empty($column_matches[1])) {
            $language_names = $column_matches[1];

            // available_languages boşsa tablodan algıla
            if (empty($available_languages) && count($language_names) > 1) {
                $detected_languages = array();
                foreach ($language_names as $index => $lang_name) {
                    if ($index > 0) {
                        $lang_slug = sanitize_title($lang_name);
                        $detected_languages[] = $lang_slug;
                    }
                }
                update_post_meta($post_id, '_available_languages', $detected_languages);
                $available_languages = $detected_languages;
            }

            $header = '<div class="language-tabs flex border-b mb-4">';
            foreach ($language_names as $index => $lang) {
                $lang_slug = sanitize_title($lang);
                if ($index > 0 && !empty($available_languages) && !in_array($lang_slug, $available_languages)) {
                    continue;
                }

                $current_lang = isset($_GET['lang']) ? sanitize_text_field($_GET['lang']) : '';
                $is_active = ($index === 0 && empty($current_lang)) || ($lang_slug === $current_lang);
                $active_class = $is_active ? 'border-b-2 border-primary-600 text-primary-600' : 'text-gray-600 hover:text-primary-600';
                $tab_url = ($index === 0) ? get_permalink() : add_query_arg('lang', $lang_slug, get_permalink());

                $header .= '<a href="' . esc_url($tab_url) . '" class="language-tab py-2 px-4 font-medium mr-4 ' . $active_class . '" 
                               data-lang-index="' . $index . '" 
                               data-lang-slug="' . esc_attr($lang_slug) . '">' . $lang . '</a>';
            }
            $header .= '</div>';
        }
    }

    // Tbody içeriğini çıkar
    preg_match('/<tbody>(.*?)<\/tbody>/s', $table_content, $body_matches);
    $content = '';

    if (!empty($body_matches)) {
        $rows = [];
        preg_match_all('/<tr>(.*?)<\/tr>/s', $body_matches[1], $row_matches);

        if (!empty($row_matches[1])) {
            foreach ($row_matches[1] as $row) {
                $columns = [];
                preg_match_all('/<td>(.*?)<\/td>/s', $row, $col_matches);
                if (!empty($col_matches[1])) {
                    $columns = $col_matches[1];
                }
                $rows[] = $columns;
            }
        }

        // Mevcut dil
        $current_lang = isset($_GET['lang']) ? sanitize_text_field($_GET['lang']) : '';
        $current_lang_index = 0;
        if (!empty($current_lang)) {
            if (!empty($available_languages) && !in_array($current_lang, $available_languages)) {
                wp_redirect(get_permalink());
                exit;
            }
            foreach ($language_names as $index => $lang_name) {
                if (sanitize_title($lang_name) === $current_lang) {
                    $current_lang_index = $index;
                    break;
                }
            }
        }

        $original_lang_index = 0;

        $content .= '<div class="language-content-wrapper">';
        $content .= '<div class="language-content block" data-lang-index="' . $current_lang_index . '">';

        foreach ($rows as $row_index => $row) {
            if (!isset($row[$current_lang_index])) continue;

            $line_id = 'lyric-line-' . $current_lang_index . '-' . $row_index;

            // Şarkıcılar / başlık
            $singers = get_the_terms(get_the_ID(), 'singer');
            $singer_name = '';
            if ($singers && !is_wp_error($singers)) {
                $singer_name = $singers[0]->name;
            }
            $post_title = get_the_title();

            $feedback_allowed = ($current_lang_index !== $original_lang_index) && !empty(trim((string) $row[$current_lang_index]));

            $content .= '<div class="lyric-line mb-4" id="' . $line_id . '">';
            $content .= '<div class="flex justify-between items-start">';

            // Üst metin
            if ($current_lang_index === $original_lang_index) {
                $content .= '<p class="text-lg mb-1 lyric-main-text">' . $row[$current_lang_index] . '</p>';
            } else {
                if (isset($row[$original_lang_index])) {
                    $content .= '<p class="text-lg mb-1 lyric-main-text">' . $row[$original_lang_index] . '</p>';
                }
            }

            // Tüm şarkıcı adları
            $all_singers = get_the_terms(get_the_ID(), 'singer');
            $all_singer_names = '';
            if ($all_singers && !is_wp_error($all_singers)) {
                $names = array();
                foreach ($all_singers as $s) { $names[] = $s->name; }
                $all_singer_names = implode(', ', $names);
            } else {
                $all_singer_names = $singer_name;
            }

            // Sadece kebab butonu (menü body'e çizilecek)
            $content .= '<div class="ml-2">
                            <button type="button"
                                class="kebab-menu-trigger text-primary-600 hover:text-primary-700 transition-colors focus:outline-none"
                                aria-haspopup="true" aria-expanded="false" title="Seçenekler"
                                data-line-id="' . $line_id . '"
                                data-text="' . esc_attr($row[$current_lang_index]) . '"
                                data-lang="' . esc_attr($language_names[$current_lang_index]) . '"
                                data-lang-slug="' . esc_attr(sanitize_title($language_names[$current_lang_index])) . '"
                                data-singer="' . esc_attr($all_singer_names) . '"
                                data-title="' . esc_attr($post_title) . '"
                                data-post-id="' . get_the_ID() . '"
                                data-feedback-enabled="' . ($feedback_allowed ? 'true' : 'false') . '">
                                ' . gufte_get_icon('dots-vertical', '') . '
                            </button>
                         </div>';

            $content .= '</div>'; // üst flex

            // Alt çeviri metni
            if ($feedback_allowed) {
                $content .= '<p class="text-sm text-gray-600">' . $row[$current_lang_index] . '</p>';
            }

            $content .= '</div>'; // lyric-line
        }

        $content .= '</div>'; // language-content
        $content .= '</div>'; // wrapper
    }

    // CSS + JS (global panel)
    // Get inline SVG icons
    $download_icon = str_replace(array("\n", "\r"), '', gufte_get_icon('download', 'mi'));
    $copy_icon = str_replace(array("\n", "\r"), '', gufte_get_icon('content-copy', 'mi'));
    $positive_icon = str_replace(array("\n", "\r"), '', gufte_get_icon('thumb-up', 'mi'));
    $negative_icon = str_replace(array("\n", "\r"), '', gufte_get_icon('thumb-down', 'mi'));
    $feedback_nonce = wp_create_nonce('gufte_feedback_nonce');
    $ajax_url = admin_url('admin-ajax.php');

    $feedback_buttons_html = '';
    if (is_user_logged_in()) {
        $feedback_buttons_html = '
                <div class="gl-feedback-section">
                    <div class="gl-separator"></div>
                    <button type="button" class="item gl-feedback gl-feedback-like" data-feedback-type="positive" aria-label="' . esc_attr__('Helpful Translation', 'gufte') . '">
                        ' . addslashes($positive_icon) . '
                        <span>' . esc_html__('Helpful Translation', 'gufte') . '</span>
                    </button>
                    <button type="button" class="item gl-feedback gl-feedback-dislike" data-feedback-type="negative" aria-label="' . esc_attr__('Needs Improvement', 'gufte') . '">
                        ' . addslashes($negative_icon) . '
                        <span>' . esc_html__('Needs Improvement', 'gufte') . '</span>
                    </button>
                </div>
        ';
    }

    $script = '
    <style>
        /* Global menü paneli (body içinde) */
        .gl-lyric-menu {
            position: fixed;
            top: 0; left: 0;
            transform: translate(-9999px, -9999px);
            background: #ffffff;
            border: 1px solid #E5E7EB; /* gray-200 */
            border-radius: 0.5rem; /* rounded-lg */
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
            min-width: 11rem;
            z-index: 2147483000; /* çok yüksek */
            visibility: hidden;
        }
        .gl-lyric-menu.open {
            visibility: visible;
        }
        .gl-lyric-menu .mi {
            width: 1.25rem; height: 1.25rem; flex: 0 0 1.25rem;
            display:inline-block;
        }
        .gl-lyric-menu .item {
            display: flex; align-items: center; gap: .5rem;
            width: 100%; padding: .625rem 1rem;
            line-height: 1.25rem;
            color: #0B1220; background: transparent; border: 0; cursor: pointer;
            text-align: left;
            font-size: 0.95rem;
        }
        .gl-lyric-menu .item:hover { background: #F3F4F6; }
        .gl-lyric-menu .gl-feedback-section.is-hidden { display: none; }
        .gl-lyric-menu .gl-feedback-section .gl-separator { margin: 0.25rem 0; }
        .gl-lyric-menu .gl-feedback-like .mi { color: #16a34a; }
        .gl-lyric-menu .gl-feedback-dislike .mi { color: #dc2626; }
        .kebab-menu-trigger:focus { outline: none !important; box-shadow: none !important; }
        .gl-lyric-menu .gl-separator { height: 1px; background: #E5E7EB; margin: 0.25rem 0; }
        .gufte-feedback-toast {
            position: fixed; bottom: 24px; right: 24px;
            background: rgba(17, 24, 39, 0.95); color: #fff;
            padding: 12px 18px; border-radius: 8px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.25);
            font-size: 14px; z-index: 2147483646;
            opacity: 0; transform: translateY(10px);
            transition: opacity 0.2s ease, transform 0.2s ease;
        }
        .gufte-feedback-toast.show { opacity: 1; transform: translateY(0); }
        .gufte-feedback-toast.success { background: rgba(22, 163, 74, 0.95); }
        .gufte-feedback-toast.error { background: rgba(220, 38, 38, 0.95); }
    </style>
    <script>
    (function(){
        // Tek bir global panel oluştur
        let panel = document.querySelector(".gl-lyric-menu");
        if (!panel) {
            panel = document.createElement("div");
            panel.className = "gl-lyric-menu";
            panel.innerHTML = `
                <button type="button" class="item gl-download">
                    ' . addslashes($download_icon) . '
                    <span>Download</span>
                </button>
                <button type="button" class="item gl-copy">
                    ' . addslashes($copy_icon) . '
                    <span>Copy</span>
                </button>
                ' . $feedback_buttons_html . '
            `;
            document.body.appendChild(panel);
        }

        const feedbackEnabled = ' . (is_user_logged_in() ? 'true' : 'false') . ';
        const feedbackNonce = "' . esc_js($feedback_nonce) . '";
        const feedbackAjaxUrl = "' . esc_url_raw($ajax_url) . '";
        let feedbackToastTimeout = null;
        const feedbackSection = panel.querySelector(".gl-feedback-section");
        const feedbackButtons = feedbackSection ? Array.from(feedbackSection.querySelectorAll(".gl-feedback")) : [];

        function showFeedbackToast(message, isSuccess) {
            let toast = document.querySelector(".gufte-feedback-toast");
            if (!toast) {
                toast = document.createElement("div");
                toast.className = "gufte-feedback-toast";
                toast.setAttribute("role", "alert");
                toast.setAttribute("aria-live", "polite");
                toast.setAttribute("aria-atomic", "true");
                document.body.appendChild(toast);
            }

            toast.textContent = message;
            toast.classList.remove("success", "error");
            toast.classList.add(isSuccess ? "success" : "error");

            clearTimeout(feedbackToastTimeout);
            toast.classList.add("show");
            feedbackToastTimeout = setTimeout(function () {
                toast.classList.remove("show");
            }, 3200);
        }

        function submitFeedback(type) {
            if (!feedbackEnabled || !activeData || !activeData.feedbackAllowed) {
                return;
            }

            const payload = new URLSearchParams();
            payload.append("action", "gufte_submit_translation_feedback");
            payload.append("nonce", feedbackNonce);
            payload.append("post_id", activeData.postId || "");
            payload.append("line_id", activeData.lineId || "");
            payload.append("lang", activeData.langSlug || "");
            payload.append("line_text", activeData.text || "");
            payload.append("feedback_type", type);

            fetch(feedbackAjaxUrl, {
                method: "POST",
                credentials: "same-origin",
                headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
                body: payload.toString()
            })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data && data.success) {
                    const msg = data.data && data.data.message ? data.data.message : "Thank you for your feedback!";
                    showFeedbackToast(msg, true);
                } else {
                    const msg = data && data.data && data.data.message ? data.data.message : "Unable to send feedback.";
                    showFeedbackToast(msg, false);
                }
            })
            .catch(function () {
                showFeedbackToast("Network error. Please try again shortly.", false);
            })
            .finally(function () {
                if (activeData && activeData.trigger) {
                    activeData.trigger.setAttribute("aria-expanded", "false");
                }
                closePanel();
            });
        }

        // Aktif satır verileri
        let activeData = null;

        // Paneli belli koordinata taşı
        function openPanelAt(x, y, data){
            const allowFeedback = feedbackEnabled && !!data.feedbackAllowed;
            activeData = data;
            activeData.feedbackAllowed = allowFeedback;

            if (feedbackSection) {
                feedbackSection.classList.toggle("is-hidden", !allowFeedback);
                feedbackButtons.forEach(function(btn){
                    btn.disabled = !allowFeedback;
                });
            }

            panel.style.transform = "translate(" + x + "px, " + y + "px)";
            panel.classList.add("open");
        }
        function closePanel(){
            panel.classList.remove("open");
            activeData = null;
        }

        // Tetikleyiciler
        document.addEventListener("click", function(e){
            const t = e.target.closest(".kebab-menu-trigger");
            if (t) {
                e.preventDefault();
                e.stopPropagation();

                // Diğer açık panelleri kapat
                closePanel();

                // Butonun ekran konumu
                const r = t.getBoundingClientRect();
                const gap = 8; // buton ile panel arası
                const x = Math.min(window.innerWidth - panel.offsetWidth - 8, r.right - panel.offsetWidth + 24);
                const y = Math.min(window.innerHeight - panel.offsetHeight - 8, r.bottom + gap);
                const feedbackAttr = t.getAttribute("data-feedback-enabled");
                const feedbackAllowed = feedbackAttr === null ? true : feedbackAttr === "true";

                // İlgili veri
                openPanelAt(x, y, {
                    lineId: t.getAttribute("data-line-id"),
                    text: t.getAttribute("data-text"),
                    lang: t.getAttribute("data-lang"),
                    langSlug: t.getAttribute("data-lang-slug"),
                    singer: t.getAttribute("data-singer"),
                    title: t.getAttribute("data-title"),
                    postId: t.getAttribute("data-post-id"),
                    trigger: t,
                    feedbackAllowed: feedbackAllowed
                });

                t.setAttribute("aria-expanded","true");
                return;
            }

            // Panel dışı tıklandıysa kapat
            if (!e.target.closest(".gl-lyric-menu")) {
                closePanel();
                document.querySelectorAll(".kebab-menu-trigger[aria-expanded=\'true\']").forEach(function(b){
                    b.setAttribute("aria-expanded","false");
                });
            }
        });

        // Scroll/resize olursa paneli gizle
        ["scroll","resize"].forEach(function(evt){
            window.addEventListener(evt, function(){
                if (panel.classList.contains("open")) closePanel();
                document.querySelectorAll(".kebab-menu-trigger[aria-expanded=\'true\']").forEach(function(b){
                    b.setAttribute("aria-expanded","false");
                });
            }, { passive: true });
        });

        // İŞLEMLER
        panel.querySelector(".gl-download").addEventListener("click", function(e){
            e.preventDefault(); e.stopPropagation();
            if (!activeData) return;
            try {
                createAndDownloadLyricImage(
                    activeData.lineId,
                    activeData.text,
                    activeData.lang,
                    activeData.singer,
                    activeData.title
                );
            } finally {
                if (activeData.trigger) activeData.trigger.setAttribute("aria-expanded","false");
                closePanel();
            }
        });

        panel.querySelector(".gl-copy").addEventListener("click", function(e){
            e.preventDefault(); e.stopPropagation();
            if (!activeData) return;

            const text = activeData.text;
            const finish = function(){
                if (activeData.trigger) activeData.trigger.setAttribute("aria-expanded","false");
                closePanel();
            };

            try {
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(text).then(finish).catch(function(){
                        // Fallback
                        const ta = document.createElement("textarea");
                        ta.value = text; document.body.appendChild(ta); ta.select();
                        document.execCommand("copy"); document.body.removeChild(ta);
                        finish();
                    });
                } else {
                    const ta = document.createElement("textarea");
                    ta.value = text; document.body.appendChild(ta); ta.select();
                    document.execCommand("copy"); document.body.removeChild(ta);
                    finish();
                }
            } catch(err){ console.error("Kopyalama hatası:", err); finish(); }
        });

        if (feedbackEnabled && feedbackButtons.length) {
            feedbackButtons.forEach(function(btn){
                btn.addEventListener("click", function(e){
                    e.preventDefault(); e.stopPropagation();
                    submitFeedback(this.getAttribute("data-feedback-type") || "positive");
                });
            });
        }
    })();
    </script>
    ';

    // Çıktı
    $output  = '<div class="multilingual-lyrics bg-white p-4 rounded-lg shadow-sm mb-8">';
    $output .= $header;
    $output .= $content;
    $output .= '</div>';
    $output .= $script;

    return $output;
}


// İçerikteki tabloları işle
function gufte_process_content_tables($content) {
    if (is_string($content)) {
        // Tabloları modern UI bileşenleriyle değiştirmek için
        $content = preg_replace_callback(
            '/<figure class="wp-block-table">.*?<table.*?>(.*?)<\/table>.*?<\/figure>/s',
            'gufte_replace_multilingual_tables',
            $content
        );
    }
    
    return $content;
}
add_filter('the_content', 'gufte_process_content_tables', 20);


/**
 * Çok dilli tablo stilleri ekle
 */
function gufte_add_multilingual_table_styles() {
    if (is_singular()) {
        echo '<style>
        .multilingual-lyrics {
            background: linear-gradient(145deg, #ffffff 0%, #f8faff 100%);
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(5, 5, 90, 0.03);
            transition: all 0.35s cubic-bezier(0.21, 1.02, 0.73, 1);
            border: 1px solid rgba(240, 245, 255, 0.8);
            margin: 2.5rem 0;
            overflow: hidden;
            position: relative;
        }
        
        .multilingual-lyrics::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #0ea5e9, #7dd3fc, #bae6fd);
            z-index: 1;
        }
        
        .multilingual-lyrics:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.07), 0 5px 15px rgba(5, 5, 90, 0.04);
            transform: translateY(-2px);
        }
        
        .language-tabs {
            display: flex;
            border-bottom: 1px solid rgba(220, 235, 250, 0.7);
            margin-bottom: 1.5rem;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 0.75rem 1.25rem 0;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            position: sticky;
            top: 0;
            z-index: 10;
            backdrop-filter: blur(8px);
        }
        
        .language-tabs::-webkit-scrollbar {
            display: none;
        }
        
        .language-tab {
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            margin-right: 0.75rem;
            cursor: pointer;
            transition: all 0.25s ease;
            white-space: nowrap;
            border-radius: 0.5rem 0.5rem 0 0;
            position: relative;
            bottom: -1px;
            letter-spacing: 0.01em;
        }
        
        .language-tab:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.3);
        }
        
        .language-tab.text-primary-600 {
            color: #0284c7;
            background-color: #fff;
            border: 1px solid rgba(220, 235, 250, 0.8);
            border-bottom-color: #fff;
        }
        
        .language-tab.text-primary-600::after {
            content: "";
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #0ea5e9, #7dd3fc);
        }
        
        .language-tab.text-gray-600 {
            color: #4b5563;
            background-color: rgba(245, 250, 255, 0.7);
        }
        
        .language-tab.text-gray-600:hover {
            color: #0ea5e9;
            background-color: rgba(250, 252, 255, 0.9);
        }
        
        .language-content-wrapper {
            padding: 0.75rem 1rem 1.5rem;
        }
        
        .lyric-line {
            margin-bottom: 1.25rem;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            background-color: rgba(250, 252, 255, 0.9);
            border: 1px solid rgba(230, 240, 250, 0.7);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .lyric-line:hover {
            background-color: #fff;
            border-color: rgba(210, 225, 250, 0.9);
            transform: translateY(-3px) scale(1.01);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.04), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
        }
        
        .lyric-line::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #0ea5e9, transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .lyric-line:hover::after {
            opacity: 1;
        }
        
        .lyric-line:last-child {
            margin-bottom: 0;
        }
        
        .lyric-line p.text-lg {
            font-size: 1.15rem;
            line-height: 1.75rem;
            color: #1e293b;
            font-weight: 500;
        }
        
        .lyric-line p.text-sm {
            font-size: 0.9rem;
            line-height: 1.35rem;
            color: #64748b;
            margin-top: 0.625rem;
            font-style: italic;
            border-top: 1px dashed rgba(220, 230, 250, 0.7);
            padding-top: 0.625rem;
        }
        
        /* İndirme düğmesi için stil */
        .download-lyric-btn {
            opacity: 0.5;
            transition: opacity 0.3s ease, transform 0.2s ease;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 0.25rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .lyric-line:hover .download-lyric-btn {
            opacity: 1;
        }
        
        .download-lyric-btn:hover {
            background-color: rgba(14, 165, 233, 0.1);
            transform: scale(1.1);
        }
        
        .download-lyric-btn:active {
            transform: scale(0.95);
        }
        
        /* Canvas konteyner stili */
        #lyricImageCanvas {
            position: fixed;
            top: -9999px;
            left: -9999px;
            z-index: -1;
        }
        
        /* Mobil düzende küçük ayarlamalar */
        @media (max-width: 640px) {
            .multilingual-lyrics {
                border-radius: 0.75rem;
                margin: 1.5rem 0;
            }
            
            .language-tabs {
                padding: 0.5rem 1rem 0;
            }
            
            .language-tab {
                padding: 0.625rem 1.125rem;
                font-size: 0.9rem;
            }
            
            .language-content-wrapper {
                padding: 0.5rem 0.75rem 1rem;
            }
            
            .lyric-line {
                padding: 0.625rem 0.75rem;
                margin-bottom: 1rem;
                border-radius: 0.625rem;
            }
            
            .lyric-line p.text-lg {
                font-size: 1.05rem;
                line-height: 1.5rem;
            }
            
            .lyric-line p.text-sm {
                font-size: 0.8rem;
                line-height: 1.25rem;
            }
        }
        
        /* Müzik Embed Stilleri */
        .music-embeds {
            margin-top: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .music-embeds iframe {
            width: 100%;
            max-width: 100%;
            border-radius: 0.75rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05), 0 1px 5px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
        }
        .music-embeds iframe:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08), 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        .apple-music-embed {
            background: linear-gradient(145deg, #fff 0%, #f6f6f8 100%);
            border-radius: 1rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .spotify-embed {
            background: linear-gradient(145deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 1rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .youtube-embed {
            background: linear-gradient(145deg, #fafafa 0%, #f5f5f5 100%);
            border-radius: 1rem;
            overflow: hidden;
            position: relative;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
            height: 0;
            transition: all 0.3s ease;
        }
        .youtube-embed iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 0;
        }
        </style>';
    }
}
add_action('wp_head', 'gufte_add_multilingual_table_styles');

/**
 * Müzik platformu bağlantıları için özel alanlar ekler
 */

/**
 * Yazı düzenleme ekranına müzik platformu bağlantıları için meta kutusu ekler
 */
function gufte_add_music_links_meta_box() {
    add_meta_box(
        'gufte_music_links',
        __('Music Platform Links', 'gufte'),
        'gufte_music_links_meta_box_callback',
        'post',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'gufte_add_music_links_meta_box');

/**
 * Meta kutusu içeriğini görüntüler
 */
function gufte_music_links_meta_box_callback($post) {
    // Nonce alanı oluştur
    wp_nonce_field('gufte_music_links_nonce', 'gufte_music_links_nonce');
    
    // Kayıtlı değerleri al
    $spotify_url = get_post_meta($post->ID, 'spotify_url', true);
    $youtube_url = get_post_meta($post->ID, 'youtube_url', true);
    $apple_music_url = get_post_meta($post->ID, 'apple_music_url', true);
    
    // Meta kutusu içeriği
    ?>
    <p>
        <label for="spotify_url" style="display: block; font-weight: bold; margin-bottom: 5px;">
            <span style="color: #1DB954;">Spotify URL</span>
        </label>
        <input type="url" id="spotify_url" name="spotify_url" value="<?php echo esc_attr($spotify_url); ?>" style="width: 100%;" placeholder="https://open.spotify.com/track/..." />
    </p>
    
    <p>
        <label for="youtube_url" style="display: block; font-weight: bold; margin-bottom: 5px;">
            <span style="color: #FF0000;">YouTube Music URL</span>
        </label>
        <input type="url" id="youtube_url" name="youtube_url" value="<?php echo esc_attr($youtube_url); ?>" style="width: 100%;" placeholder="https://music.youtube.com/watch?v=..." />
    </p>
    
    <p>
        <label for="apple_music_url" style="display: block; font-weight: bold; margin-bottom: 5px;">
            <span style="color: #FA243C;">Apple Music URL</span>
        </label>
        <input type="url" id="apple_music_url" name="apple_music_url" value="<?php echo esc_attr($apple_music_url); ?>" style="width: 100%;" placeholder="https://music.apple.com/..." />
    </p>
    <?php
}

/**
 * Yazı kaydedildiğinde özel alan değerlerini kaydeder
 */
function gufte_save_music_links_meta_box_data($post_id) {
    // Nonce kontrolü
    if (!isset($_POST['gufte_music_links_nonce']) || !wp_verify_nonce($_POST['gufte_music_links_nonce'], 'gufte_music_links_nonce')) {
        return;
    }
    
    // Otomatik kayıt kontrolü
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Yetki kontrolü
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Spotify URL'i kaydet
    if (isset($_POST['spotify_url'])) {
        $spotify_url = sanitize_url($_POST['spotify_url']);
        update_post_meta($post_id, 'spotify_url', $spotify_url);
    }
    
    // YouTube URL'i kaydet
    if (isset($_POST['youtube_url'])) {
        $youtube_url = sanitize_url($_POST['youtube_url']);
        update_post_meta($post_id, 'youtube_url', $youtube_url);
    }
    
    // Apple Music URL'i kaydet
    if (isset($_POST['apple_music_url'])) {
        $apple_music_url = sanitize_url($_POST['apple_music_url']);
        update_post_meta($post_id, 'apple_music_url', $apple_music_url);
    }
}
add_action('save_post', 'gufte_save_music_links_meta_box_data');

/**
 * Custom template tags for this theme - Check if exists first
 */
$template_tags_path = GUFTE_DIR . '/inc/template-tags.php';
if (file_exists($template_tags_path)) {
    require $template_tags_path;
}

/**
 * Functions which enhance the theme by hooking into WordPress - Check if exists first
 */
$template_functions_path = GUFTE_DIR . '/inc/template-functions.php';
if (file_exists($template_functions_path)) {
    require $template_functions_path;
}

/**
 * WordPress'te YouTube URL'lerinden thumbnail'leri featured image olarak ayarlar
 * functions.php dosyasına ekleyin
 */

// Meta kutusu oluştur (sidebar'da YouTube URL alanı)
function add_youtube_thumbnail_meta_box() {
    add_meta_box(
        'youtube_thumbnail_meta_box',           // ID
        'YouTube Thumbnail',                    // Başlık
        'youtube_thumbnail_meta_box_callback',  // Callback fonksiyonu
        'post',                                 // Post tipi
        'side',                                 // Konum (side = sağ sidebar)
        'high'                                  // Öncelik
    );
}
add_action('add_meta_boxes', 'add_youtube_thumbnail_meta_box');

// Meta kutusu içeriği
function youtube_thumbnail_meta_box_callback($post) {
    // Nonce alanı oluştur
    wp_nonce_field('youtube_thumbnail_save_meta_box', 'youtube_thumbnail_nonce');
    
    // Kaydedilmiş YouTube URL'sini al
    $youtube_url = get_post_meta($post->ID, '_youtube_thumbnail_url', true);
    
    // YouTube URL alanını oluştur
    ?>
    <p>
        <label for="youtube_thumbnail_url">YouTube URL:</label>
        <input type="text" id="youtube_thumbnail_url" name="youtube_thumbnail_url" 
               value="<?php echo esc_attr($youtube_url); ?>" style="width: 100%;" 
               placeholder="https://www.youtube.com/watch?v=...">
    </p>
    <p>
        <button type="button" id="youtube_fetch_thumbnail" class="button">Thumbnail Al</button>
        <span id="youtube_thumbnail_status" style="display: inline-block; margin-left: 10px;"></span>
    </p>
    
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Thumbnail al butonu tıklandığında
        $('#youtube_fetch_thumbnail').on('click', function() {
            var youtube_url = $('#youtube_thumbnail_url').val();
            if (!youtube_url) {
                $('#youtube_thumbnail_status').html('<span style="color: red;">URL boş olamaz!</span>');
                return;
            }
            
            $('#youtube_thumbnail_status').html('<span>İşleniyor...</span>');
            
            // AJAX isteği gönder
            $.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'fetch_youtube_thumbnail',
                    post_id: <?php echo $post->ID; ?>,
                    youtube_url: youtube_url,
                    nonce: $('#youtube_thumbnail_nonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        $('#youtube_thumbnail_status').html('<span style="color: green;">Başarılı! Sayfa yenileniyor...</span>');
                        // Sayfayı yenile
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        $('#youtube_thumbnail_status').html('<span style="color: red;">' + response.data + '</span>');
                    }
                },
                error: function() {
                    $('#youtube_thumbnail_status').html('<span style="color: red;">Hata oluştu!</span>');
                }
            });
        });
    });
    </script>
    <?php
}

// Meta kutusu verilerini kaydet
function save_youtube_thumbnail_meta_box($post_id) {
    // Nonce kontrolü
    if (!isset($_POST['youtube_thumbnail_nonce']) || 
        !wp_verify_nonce($_POST['youtube_thumbnail_nonce'], 'youtube_thumbnail_save_meta_box')) {
        return;
    }
    
    // Otomatik kayıt kontrolü
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Yetki kontrolü
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // YouTube URL'sini kaydet
    if (isset($_POST['youtube_thumbnail_url'])) {
        update_post_meta($post_id, '_youtube_thumbnail_url', sanitize_text_field($_POST['youtube_thumbnail_url']));
        
        // URL girilmişse ve featured image yoksa thumbnail'i al
        $youtube_url = sanitize_text_field($_POST['youtube_thumbnail_url']);
        if (!empty($youtube_url) && !has_post_thumbnail($post_id)) {
            fetch_and_set_youtube_thumbnail($post_id, $youtube_url);
        }
    }
}
add_action('save_post', 'save_youtube_thumbnail_meta_box');

// AJAX işlemi için fonksiyon
function ajax_fetch_youtube_thumbnail() {
    // Nonce kontrolü
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'youtube_thumbnail_save_meta_box')) {
        wp_send_json_error('Güvenlik doğrulaması başarısız.');
        return;
    }
    
    // Post ID ve YouTube URL kontrolü
    if (!isset($_POST['post_id']) || !isset($_POST['youtube_url'])) {
        wp_send_json_error('Gerekli parametreler eksik.');
        return;
    }
    
    $post_id = intval($_POST['post_id']);
    $youtube_url = sanitize_text_field($_POST['youtube_url']);
    
    // URL'yi kaydet
    update_post_meta($post_id, '_youtube_thumbnail_url', $youtube_url);
    
    // Thumbnail'i al ve ayarla
    $result = fetch_and_set_youtube_thumbnail($post_id, $youtube_url);
    
    if ($result === true) {
        wp_send_json_success('Thumbnail başarıyla ayarlandı.');
    } else {
        wp_send_json_error($result);
    }
}
add_action('wp_ajax_fetch_youtube_thumbnail', 'ajax_fetch_youtube_thumbnail');

// YouTube URL'sinden thumbnail'i al ve featured image olarak ayarla
function fetch_and_set_youtube_thumbnail($post_id, $youtube_url) {
    // YouTube ID'sini al
    $youtube_id = get_youtube_id_from_url($youtube_url);
    
    if (!$youtube_id) {
        return 'Geçerli bir YouTube URL\'si değil.';
    }
    
    // Thumbnail URL'lerini oluştur
    $thumbnail_urls = array(
        'maxresdefault' => 'https://img.youtube.com/vi/' . $youtube_id . '/maxresdefault.jpg',
        'hqdefault'     => 'https://img.youtube.com/vi/' . $youtube_id . '/hqdefault.jpg',
        'mqdefault'     => 'https://img.youtube.com/vi/' . $youtube_id . '/mqdefault.jpg',
        'default'       => 'https://img.youtube.com/vi/' . $youtube_id . '/default.jpg'
    );
    
    // WordPress yükleme işlemlerini kullanabilmek için gerekli dosyaları dahil et
    if (!function_exists('media_sideload_image')) {
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
    }
    
    // Her bir thumbnail kalitesini dene
    foreach ($thumbnail_urls as $quality => $url) {
        $upload = media_sideload_image($url, $post_id, 'YouTube thumbnail - ' . $youtube_id, 'id');
        
        if (!is_wp_error($upload)) {
            // Featured image olarak ayarla
            set_post_thumbnail($post_id, $upload);
            return true;
        }
    }
    
    return 'Thumbnail indirilemedi. YouTube videosu mevcut olmayabilir veya sunucu hatası oluşmuş olabilir.';
}

// YouTube URL'sinden ID çıkarma fonksiyonu
function get_youtube_id_from_url($url) {
    $patterns = array(
        '#https?://(?:www\.)?youtube\.com/watch\?v=([A-Za-z0-9\-_]+)#i',
        '#https?://(?:www\.)?youtu\.be/([A-Za-z0-9\-_]+)#i',
        '#https?://(?:www\.)?youtube\.com/embed/([A-Za-z0-9\-_]+)#i',
        '#https?://(?:www\.)?youtube\.com/shorts/([A-Za-z0-9\-_]+)#i'
    );
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
    }
    
    return false;
}

// Eski koddan kalan fonksiyonu da tutalım (içerik tarama)
function scan_post_content_for_youtube($post_id) {
    // Zaten featured image varsa atla
    if (has_post_thumbnail($post_id)) return;
    
    // Kaydedilmiş YouTube URL'si varsa kullan
    $youtube_url = get_post_meta($post_id, '_youtube_thumbnail_url', true);
    if (!empty($youtube_url)) {
        fetch_and_set_youtube_thumbnail($post_id, $youtube_url);
        return;
    }
    
    // YouTube URL'si yoksa içeriği tara
    $content = get_post_field('post_content', $post_id);
    
    // YouTube URL paternlerini bul
    $patterns = array(
        '#https?://(?:www\.)?youtube\.com/watch\?v=([A-Za-z0-9\-_]+)#i',
        '#https?://(?:www\.)?youtu\.be/([A-Za-z0-9\-_]+)#i',
        '#https?://(?:www\.)?youtube\.com/embed/([A-Za-z0-9\-_]+)#i',
        '#https?://(?:www\.)?youtube\.com/shorts/([A-Za-z0-9\-_]+)#i'
    );
    
    $youtube_id = '';
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $content, $matches)) {
            $youtube_id = $matches[1];
            $youtube_url = $matches[0];
            update_post_meta($post_id, '_youtube_thumbnail_url', $youtube_url);
            break;
        }
    }
    
    if (!empty($youtube_id)) {
        fetch_and_set_youtube_thumbnail($post_id, $youtube_url);
    }
}

// Post kaydedildiğinde içeriği tara (yedek yöntem)
add_action('save_post', 'scan_post_content_for_youtube');

/**
 * Müzik platformu embed kodları için özel alanlar ekler
 */

/**
 * Yazı düzenleme ekranına müzik embed kodları için meta kutusu ekler
 */
function gufte_add_music_embeds_meta_box() {
    add_meta_box(
        'gufte_music_embeds',
        __('Music Embeds', 'gufte'),
        'gufte_music_embeds_meta_box_callback',
        'post',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'gufte_add_music_embeds_meta_box');

/**
 * Meta kutusu içeriğini görüntüler
 */
function gufte_music_embeds_meta_box_callback($post) {
    // Nonce alanı oluştur
    wp_nonce_field('gufte_music_embeds_nonce', 'gufte_music_embeds_nonce');
    
    // Kayıtlı değerleri al
    $spotify_embed = get_post_meta($post->ID, 'spotify_embed', true);
    $youtube_embed = get_post_meta($post->ID, 'youtube_embed', true);
    $apple_music_embed = get_post_meta($post->ID, 'apple_music_embed', true);
    
    // Meta kutusu içeriği
    ?>
    <p class="description"><?php _e('Şarkı/albüm embed kodlarını aşağıya yapıştırın.', 'gufte'); ?></p>
    
    <p>
        <label for="apple_music_embed" style="display: block; font-weight: bold; margin-bottom: 5px;">
            <span style="color: #FA243C;">Apple Music Embed Kodu</span>
        </label>
        <textarea id="apple_music_embed" name="apple_music_embed" style="width: 100%; height: 80px;" placeholder='<iframe allow="autoplay *; encrypted-media *;" frameborder="0" height="175" style="width:100%;" src="https://embed.music.apple.com/..."></iframe>'><?php echo esc_textarea($apple_music_embed); ?></textarea>
    </p>
    
    <p>
        <label for="spotify_embed" style="display: block; font-weight: bold; margin-bottom: 5px;">
            <span style="color: #1DB954;">Spotify Embed Kodu</span>
        </label>
        <textarea id="spotify_embed" name="spotify_embed" style="width: 100%; height: 80px;" placeholder='<iframe src="https://open.spotify.com/embed/track/..." width="100%" height="352" frameborder="0" allowtransparency="true" allow="encrypted-media"></iframe>'><?php echo esc_textarea($spotify_embed); ?></textarea>
    </p>
    
    <p>
        <label for="youtube_embed" style="display: block; font-weight: bold; margin-bottom: 5px;">
            <span style="color: #FF0000;">YouTube Embed Kodu</span>
        </label>
        <textarea id="youtube_embed" name="youtube_embed" style="width: 100%; height: 80px;" placeholder='<iframe width="100%" height="315" src="https://www.youtube.com/embed/..." frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>'><?php echo esc_textarea($youtube_embed); ?></textarea>
    </p>
    <?php
}

/**
 * Yazı kaydedildiğinde özel alan değerlerini kaydeder
 */
function gufte_save_music_embeds_meta_box_data($post_id) {
    // Nonce kontrolü
    if (!isset($_POST['gufte_music_embeds_nonce']) || !wp_verify_nonce($_POST['gufte_music_embeds_nonce'], 'gufte_music_embeds_nonce')) {
        return;
    }
    
    // Otomatik kayıt kontrolü
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Yetki kontrolü
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Apple Music embed kodunu kaydet
    if (isset($_POST['apple_music_embed'])) {
        $apple_music_embed = wp_kses($_POST['apple_music_embed'], array(
            'iframe' => array(
                'allow' => true,
                'frameborder' => true,
                'height' => true,
                'style' => true,
                'sandbox' => true,
                'src' => true,
                'width' => true
            )
        ));
        update_post_meta($post_id, 'apple_music_embed', $apple_music_embed);
    }
    
    // Spotify embed kodunu kaydet
    if (isset($_POST['spotify_embed'])) {
        $spotify_embed = wp_kses($_POST['spotify_embed'], array(
            'iframe' => array(
                'src' => true,
                'width' => true,
                'height' => true,
                'frameborder' => true,
                'allowtransparency' => true,
                'allow' => true,
                'style' => true
            )
        ));
        update_post_meta($post_id, 'spotify_embed', $spotify_embed);
    }
    
    // YouTube embed kodunu kaydet
    if (isset($_POST['youtube_embed'])) {
        $youtube_embed = wp_kses($_POST['youtube_embed'], array(
            'iframe' => array(
                'width' => true,
                'height' => true,
                'src' => true,
                'frameborder' => true,
                'allow' => true,
                'allowfullscreen' => true,
                'style' => true
            )
        ));
        update_post_meta($post_id, 'youtube_embed', $youtube_embed);
    }
}
add_action('save_post', 'gufte_save_music_embeds_meta_box_data');

/**
 * Bir taksonomiye ait yazı sayısını hesaplayan fonksiyon
 */
function wp_count_posts_by_term($post_type = 'post', $taxonomy = '', $term_id = 0) {
    if (empty($taxonomy) || empty($term_id)) {
        return 0;
    }
    
    // Taksonomi terimini doğrula
    $term = get_term($term_id, $taxonomy);
    if (is_wp_error($term) || !$term) {
        return 0;
    }
    
    // Yazıları sorgula
    $args = array(
        'post_type' => $post_type,
        'post_status' => 'publish',
        'posts_per_page' => -1, // Tüm yazıları getir
        'fields' => 'ids', // Sadece ID'leri getir (daha hızlı)
        'tax_query' => array(
            array(
                'taxonomy' => $taxonomy,
                'field' => 'term_id',
                'terms' => $term_id,
            ),
        ),
    );
    
    $query = new WP_Query($args);
    
    // Toplam yazı sayısını döndür
    return $query->found_posts;
}



/**
 * Gufte teması için SEO meta verilerini özelleştirme
 * Bu kodu functions.php dosyanıza ekleyin
 */

/**
 * WordPress başlık (title) etiketini özelleştirme
 */
function gufte_custom_meta_title($title) {
    // Eğer tekil bir yazı (post) sayfasındaysak
    if (is_single() && get_post_type() == 'post') {
        global $post;
        
        // Şarkı başlığını al
        $song_title = get_the_title($post);
        
        // Şarkıcı bilgisini al
        $singer_name = '';
        $singers = get_the_terms($post->ID, 'singer');
        if ($singers && !is_wp_error($singers)) {
            $singer = reset($singers); // İlk şarkıcıyı al
            $singer_name = $singer->name;
        }
        
        // Eğer şarkıcı varsa, şarkıcı adını ekle
        if (!empty($singer_name)) {
            $title = "$song_title by $singer_name - Lyrics and Translations";
        } else {
            $title = "$song_title - Lyrics and Translations";
        }
    }
    // Eğer kategori sayfasındaysak
    elseif (is_category()) {
        $category = get_queried_object();
        $title = "{$category->name} Songs - Lyrics and Translations | " . get_bloginfo('name');
    }
    // Eğer şarkıcı taksonomi sayfasındaysak
    elseif (is_tax('singer')) {
        $singer = get_queried_object();
        $title = "{$singer->name} - Songs, Lyrics and Translations | " . get_bloginfo('name');
    }
    // Eğer albüm taksonomi sayfasındaysak
    elseif (is_tax('album')) {
        $album = get_queried_object();
        $title = "{$album->name} Album - Lyrics and Translations | " . get_bloginfo('name');
    }
    
    return $title;
}
add_filter('pre_get_document_title', 'gufte_custom_meta_title', 10);

/**
 * Meta açıklama (description) oluşturma
 */
function gufte_add_meta_description() {
    $description = '';
    
    // Tekil yazı (post) sayfası için
    if (is_single() && get_post_type() == 'post') {
        global $post;
        
        // Şarkı başlığını al
        $song_title = get_the_title($post);
        
        // Şarkıcı bilgisini al
        $singer_name = '';
        $singers = get_the_terms($post->ID, 'singer');
        if ($singers && !is_wp_error($singers)) {
            $singer = reset($singers); // İlk şarkıcıyı al
            $singer_name = $singer->name;
        }
        
        // Albüm bilgisini al
        $album_name = '';
        $albums = get_the_terms($post->ID, 'album');
        if ($albums && !is_wp_error($albums)) {
            $album = reset($albums); // İlk albümü al
            $album_name = $album->name;
        }
        
        // Çok dilli şarkı sözleri bilgisini al
        $languages_info = '';
        if (function_exists('gufte_get_lyrics_languages')) {
            $lyrics_languages = gufte_get_lyrics_languages(get_the_content());
            
            if (!empty($lyrics_languages['original'])) {
                $languages_info .= $lyrics_languages['original'];
                
                if (!empty($lyrics_languages['translations']) && count($lyrics_languages['translations']) > 0) {
                    $languages_info .= ' with translations in ' . implode(', ', array_slice($lyrics_languages['translations'], 0, 3));
                    
                    if (count($lyrics_languages['translations']) > 3) {
                        $languages_info .= ' and more';
                    }
                }
            }
        }
        
        // Meta açıklamayı oluştur
        if (!empty($singer_name)) {
            $description = "Discover lyrics and translations for $song_title by $singer_name";
            
            if (!empty($album_name)) {
                $description .= " from the album $album_name";
            }
            
            if (!empty($languages_info)) {
                $description .= ". Available in $languages_info";
            }
            
            $description .= ". Read, translate and enjoy the song lyrics.";
        } else {
            $description = "Discover lyrics and translations for $song_title";
            
            if (!empty($album_name)) {
                $description .= " from the album $album_name";
            }
            
            if (!empty($languages_info)) {
                $description .= ". Available in $languages_info";
            }
            
            $description .= ". Read, translate and enjoy the song lyrics.";
        }
    }
    // Kategori sayfası için
    elseif (is_category()) {
        $category = get_queried_object();
        $description = "Explore {$category->name} song lyrics and translations. Find all {$category->name} songs with lyrics and translations in multiple languages. Read, translate and enjoy the song lyrics.";
    }
    // Şarkıcı taksonomi sayfası için
    elseif (is_tax('singer')) {
        $singer = get_queried_object();
        
        // Şarkıcının gerçek adını al (varsa)
        $real_name = get_term_meta($singer->term_id, 'real_name', true);
        $real_name_text = !empty($real_name) ? " ($real_name)" : "";
        
        $description = "Explore {$singer->name}{$real_name_text} song lyrics and translations. Find all {$singer->name} songs with original lyrics and translations in multiple languages. Read, translate and enjoy!";
    }
    // Albüm taksonomi sayfası için
    elseif (is_tax('album')) {
        $album = get_queried_object();
        
        // Albüm yılını al
        $album_year = get_term_meta($album->term_id, 'album_year', true);
        $album_year_text = !empty($album_year) ? " ($album_year)" : "";
        
        // Albüm şarkıcılarını al
        $album_singers_text = '';
        $album_singers = gufte_get_album_singers($album->term_id);
        if (!empty($album_singers)) {
            $singers_names = array_map(function($singer) {
                return $singer->name;
            }, array_slice($album_singers, 0, 2));
            
            $album_singers_text = " by " . implode(', ', $singers_names);
            
            if (count($album_singers) > 2) {
                $album_singers_text .= ' and others';
            }
        }
        
        $description = "Explore {$album->name}{$album_year_text} album{$album_singers_text}. Find all songs from {$album->name} with original lyrics and translations in multiple languages. Read, translate and enjoy!";
    }
    // Ana sayfa için
    elseif (is_home() || is_front_page()) {
        $description = get_bloginfo('description');
        
        if (empty($description)) {
            $description = "Discover song lyrics and translations in multiple languages. Find original lyrics and translations for your favorite songs. Read, translate and enjoy the music!";
        }
    }
    
    // Açıklamayı kontrol ve kısalt
    if (!empty($description)) {
        $description = wp_trim_words($description, 30, '...');
        // Maksimum 160 karakter sınırı
        if (strlen($description) > 160) {
            $description = substr($description, 0, 157) . '...';
        }
        
        echo '<meta name="description" content="' . esc_attr($description) . '" />' . "\n";
    }
}
add_action('wp_head', 'gufte_add_meta_description', 1);

/**
 * Open Graph meta etiketleri ekleme
 */
function gufte_add_og_meta_tags() {
    // Skip for lyrics post type - it has its own OG tags in single-lyrics.php
    if (is_singular('lyrics')) {
        return;
    }

    // Skip for album taxonomy - it has its own OG tags in taxonomy-album.php
    if (is_tax('album')) {
        return;
    }

    global $post;

    $og_type = 'website';
    $og_title = get_bloginfo('name');
    $og_description = get_bloginfo('description');
    $og_url = home_url('/');
    $og_image = '';

    // Anasayfa için
    if (is_front_page() || is_home()) {
        $og_title = get_bloginfo('name');
        $og_description = get_bloginfo('description') ?: __('Discover song lyrics and translations in multiple languages', 'gufte');

        // Site logosu veya varsayılan görsel
        $custom_logo_id = get_theme_mod('custom_logo');
        if ($custom_logo_id) {
            $og_image = wp_get_attachment_image_url($custom_logo_id, 'full');
        }
    }
    // Tekil yazı (post) sayfası için
    elseif (is_single() && get_post_type() == 'post') {
        $og_type = 'article';
        
        // Şarkı başlığını al
        $song_title = get_the_title($post);
        
        // Şarkıcı bilgisini al
        $singer_name = '';
        $singers = get_the_terms($post->ID, 'singer');
        if ($singers && !is_wp_error($singers)) {
            $singer = reset($singers); // İlk şarkıcıyı al
            $singer_name = $singer->name;
        }
        
        if (!empty($singer_name)) {
            $og_title = "$song_title by $singer_name - Lyrics and Translations";
        } else {
            $og_title = "$song_title - Lyrics and Translations";
        }
        
        // Meta açıklamayı oluştur - kısa versiyon
        $og_description = wp_trim_words(get_the_excerpt(), 25, '...');
        
        $og_url = get_permalink();
        
        // Öne çıkan görsel varsa kullan
        if (has_post_thumbnail()) {
            $og_image = get_the_post_thumbnail_url($post->ID, 'large');
        }
    }
    // Kategori sayfası için
    elseif (is_category()) {
        $category = get_queried_object();
        $og_title = "{$category->name} Songs - Lyrics and Translations";
        $og_description = "Explore {$category->name} song lyrics and translations in multiple languages.";
        $og_url = get_term_link($category);
    }
    // Şarkıcı taksonomi sayfası için
    elseif (is_tax('singer')) {
        $singer = get_queried_object();
        $og_title = "{$singer->name} - Songs, Lyrics and Translations";
        $og_description = "Explore {$singer->name} song lyrics and translations in multiple languages.";
        $og_url = get_term_link($singer);
        
        // Şarkıcı görseli varsa kullan
        $singer_image_id = get_term_meta($singer->term_id, 'singer_image_id', true);
        if ($singer_image_id) {
            $og_image = wp_get_attachment_image_url($singer_image_id, 'large');
        }
    }
    // Albüm taksonomi sayfası için
    elseif (is_tax('album')) {
        $album = get_queried_object();
        $album_year = get_term_meta($album->term_id, 'album_year', true);
        $album_year_text = !empty($album_year) ? " ($album_year)" : "";
        
        $og_title = "{$album->name}{$album_year_text} Album - Lyrics and Translations";
        $og_description = "Explore {$album->name} album songs with lyrics and translations in multiple languages.";
        $og_url = get_term_link($album);
        
        // Albüme ait yazıları kullanarak görsel bul
        $album_posts = get_posts(array(
            'post_type' => 'post',
            'tax_query' => array(
                array(
                    'taxonomy' => 'album',
                    'field' => 'term_id',
                    'terms' => $album->term_id,
                ),
            ),
            'posts_per_page' => 1,
        ));
        
        if (!empty($album_posts) && has_post_thumbnail($album_posts[0]->ID)) {
            $og_image = get_the_post_thumbnail_url($album_posts[0]->ID, 'large');
        }
    }
    
    // Open Graph meta etiketlerini yazdır
    echo '<meta property="og:type" content="' . esc_attr($og_type) . '" />' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($og_title) . '" />' . "\n";
    
    if (!empty($og_description)) {
        echo '<meta property="og:description" content="' . esc_attr($og_description) . '" />' . "\n";
    }
    
    echo '<meta property="og:url" content="' . esc_url($og_url) . '" />' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '" />' . "\n";
    
    if (!empty($og_image)) {
        echo '<meta property="og:image" content="' . esc_url($og_image) . '" />' . "\n";
    }
}
add_action('wp_head', 'gufte_add_og_meta_tags', 2);

/**
 * Anasayfa için Schema.org JSON-LD
 */
function gufte_add_homepage_schema() {
    // Sadece anasayfa için
    if (!is_front_page() && !is_home()) {
        return;
    }

    $schema = array(
        '@context' => 'https://schema.org',
        '@graph' => array(
            // WebSite schema
            array(
                '@type' => 'WebSite',
                '@id' => home_url('/') . '#website',
                'url' => home_url('/'),
                'name' => get_bloginfo('name'),
                'description' => get_bloginfo('description'),
                'inLanguage' => get_bloginfo('language'),
                'potentialAction' => array(
                    '@type' => 'SearchAction',
                    'target' => array(
                        '@type' => 'EntryPoint',
                        'urlTemplate' => home_url('/?s={search_term_string}'),
                    ),
                    'query-input' => 'required name=search_term_string',
                ),
            ),
            // Organization schema
            array(
                '@type' => 'Organization',
                '@id' => home_url('/') . '#organization',
                'name' => get_bloginfo('name'),
                'url' => home_url('/'),
                'logo' => array(
                    '@type' => 'ImageObject',
                    'url' => get_theme_mod('custom_logo') ? wp_get_attachment_image_url(get_theme_mod('custom_logo'), 'full') : '',
                ),
            ),
        ),
    );

    // Logo yoksa logo özelliğini kaldır
    if (empty($schema['@graph'][1]['logo']['url'])) {
        unset($schema['@graph'][1]['logo']);
    }

    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
}
add_action('wp_head', 'gufte_add_homepage_schema', 1);

/**
 * Twitter Card meta etiketleri ekleme
 */
function gufte_add_twitter_card_meta_tags() {
    // Skip for lyrics post type - it has its own Twitter Card tags in single-lyrics.php
    if (is_singular('lyrics')) {
        return;
    }

    // Skip for album taxonomy - it has its own Twitter Card tags in taxonomy-album.php
    if (is_tax('album')) {
        return;
    }

    // Temel Twitter kart tipi
    echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";

    // Sitenizin Twitter hesabı varsa ekleyebilirsiniz
    // echo '<meta name="twitter:site" content="@YourTwitterHandle" />' . "\n";

    // Open Graph meta etiketlerini Twitter da kullanır, bu yüzden
    // sadece kart tipini belirtmek genellikle yeterlidir
}
add_action('wp_head', 'gufte_add_twitter_card_meta_tags', 3);


function gufte_add_robots_meta() {
    // Arama sonuçları ve arşiv sayfaları için
    if (is_search() || is_archive()) {
        echo '<meta name="robots" content="noindex, follow" />' . "\n";
    }
    
    // 404 sayfaları için
    if (is_404()) {
        echo '<meta name="robots" content="noindex, nofollow" />' . "\n";
    }
    
    // Özel sayfalar için
    if (is_single()) {
        // İçeriği olmayan sayfalar için
        $content = get_the_content();
        if (strlen($content) < 100) {
            echo '<meta name="robots" content="noindex, follow" />' . "\n";
        } else {
            echo '<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />' . "\n";
        }
    }
}
add_action('wp_head', 'gufte_add_robots_meta', 4);

/**
 * ====================================
 * 14. SAYFA HIZI OPTİMİZASYONLARI (SEO İÇİN)
 * ====================================
 */

// Gereksiz meta tagları kaldır
function gufte_remove_unnecessary_meta() {
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wp_shortlink_wp_head');
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
}
add_action('init', 'gufte_remove_unnecessary_meta');

// DNS Prefetch ekle
function gufte_add_dns_prefetch() {
    $prefetch_domains = array(
        '//fonts.googleapis.com',
        '//fonts.gstatic.com',
        '//img.youtube.com',
        '//i.ytimg.com',
        '//open.spotify.com',
        '//embed.music.apple.com',
        '//www.google-analytics.com',
        '//www.googletagmanager.com'
    );
    
    foreach ($prefetch_domains as $domain) {
        echo '<link rel="dns-prefetch" href="' . $domain . '">' . "\n";
    }
}
add_action('wp_head', 'gufte_add_dns_prefetch', 1);



/**
 * WordPress Admin Dashboard'ına Çeviri Sayısı Sütunu Ekleme
 * Bu kodu functions.php dosyanıza ekleyin
 */

// Old translation count column removed - using flag-based version from lyrics-translations-meta.php

/**
 * Dil kodlarını kısa forma çevir - BASİTLEŞTİRİLMİŞ
 */
function gufte_get_language_short_code($lang) {
    $short_codes = array(
        'english' => 'EN',
        'spanish' => 'ES',
        'turkish' => 'TR',
        'german' => 'DE',
        'arabic' => 'AR',
        'french' => 'FR',
        'italian' => 'IT',
        'portuguese' => 'PT',
        'russian' => 'RU',
        'japanese' => 'JA',
        'korean' => 'KO',
        'persian' => 'FA',
    );
    
    return isset($short_codes[$lang]) ? $short_codes[$lang] : strtoupper(substr($lang, 0, 2));
}

/**
 * Çeviri sayısı sütununu sıralanabilir yap
 */
function gufte_make_translation_column_sortable($columns) {
    $columns['translation_count'] = 'translation_count';
    return $columns;
}
add_filter('manage_edit-post_sortable_columns', 'gufte_make_translation_column_sortable');

/**
 * Çeviri sayısına göre sıralama sorgusu
 */
function gufte_sort_posts_by_translation_count($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }
    
    if ($query->get('orderby') === 'translation_count') {
        $query->set('meta_key', '_available_languages');
        $query->set('orderby', 'meta_value');
        
        // Özel sıralama için meta query ekle
        $meta_query = array(
            'relation' => 'OR',
            array(
                'key' => '_available_languages',
                'compare' => 'EXISTS'
            ),
            array(
                'key' => '_available_languages',
                'compare' => 'NOT EXISTS'
            )
        );
        $query->set('meta_query', $meta_query);
    }
}
add_action('pre_get_posts', 'gufte_sort_posts_by_translation_count');

/**
 * Admin CSS stilleri ekle
 */
function gufte_add_admin_translation_column_styles() {
    $screen = get_current_screen();
    
    if ($screen && $screen->id === 'edit-post') {
        ?>
        <style type="text/css">
        .column-translation_count {
            width: 120px;
        }
        
        .translation-info {
            font-size: 12px;
            line-height: 1.3;
        }
        
        .translation-count {
            margin-bottom: 4px;
            font-weight: 500;
        }
        
        .count-original {
            color: #2271b1;
            font-weight: 600;
        }
        
        .count-translations {
            color: #00a32a;
            font-weight: 600;
        }
        
        .language-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 2px;
        }
        
        .lang-badge {
            display: inline-block;
            padding: 1px 4px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 2px;
            text-transform: uppercase;
            line-height: 1.2;
        }
        
        .lang-badge.original {
            background-color: #2271b1;
            color: white;
        }
        
        .lang-badge.translation {
            background-color: #00a32a;
            color: white;
        }
        
        .no-translations {
            color: #8c8f94;
            font-style: italic;
        }
        
        /* Responsive tasarım */
        @media screen and (max-width: 782px) {
            .column-translation_count {
                display: none !important;
            }
        }
        
        /* Sütun başlığı ikonu */
        .manage-column .dashicons-translation {
            color: #50575e;
            font-size: 16px;
            vertical-align: middle;
        }
        
        /* Hover efektleri */
        .lang-badge:hover {
            opacity: 0.8;
            cursor: help;
        }
        
        /* Sıralama ok simgesi */
        .manage-column.sortable a:hover,
        .manage-column.sorted a:hover {
            color: #2271b1;
        }
                .column-translation_count {
            width: 100px;
        }
        
        .translation-info {
            font-size: 12px;
            line-height: 1.3;
        }
        
        .lang-badge {
            display: inline-block;
            padding: 1px 4px;
            font-size: 9px !important;
            font-weight: bold;
            border-radius: 2px;
            text-transform: uppercase;
            line-height: 1.2;
            margin: 0 1px;
        }
        
        .no-translations {
            color: #999;
            font-style: italic;
        }
        
        @media screen and (max-width: 782px) {
            .column-translation_count {
                display: none !important;
            }
        }
        </style>
        <?php
    }
}
add_action('admin_head', 'gufte_add_admin_translation_column_styles');

/**
 * Toplu işlemler için çeviri durumu filtreleri ekle
 */
function gufte_add_translation_filter_dropdown() {
    global $typenow;

    if ($typenow === 'lyrics') {
        $selected_filter = isset($_GET['translation_filter']) ? $_GET['translation_filter'] : '';

        echo '<select name="translation_filter" id="translation-filter">';
        echo '<option value="">Tüm çeviriler</option>';
        echo '<option value="has_translations"' . selected($selected_filter, 'has_translations', false) . '>Çevirisi olan</option>';
        echo '<option value="no_translations"' . selected($selected_filter, 'no_translations', false) . '>Çevirisi olmayan</option>';
        echo '<option value="multiple_translations"' . selected($selected_filter, 'multiple_translations', false) . '>Birden fazla çeviri</option>';
        echo '</select>';
    }
}
add_action('restrict_manage_posts', 'gufte_add_translation_filter_dropdown');

/**
 * Çeviri filtrelerini uygula
 */
function gufte_apply_translation_filters($query) {
    global $pagenow, $typenow;

    if ($pagenow === 'edit.php' && $typenow === 'lyrics' && isset($_GET['translation_filter']) && !empty($_GET['translation_filter'])) {
        $filter = $_GET['translation_filter'];
        
        switch ($filter) {
            case 'has_translations':
                $query->set('meta_query', array(
                    array(
                        'key' => '_available_languages',
                        'compare' => 'EXISTS'
                    )
                ));
                break;
                
            case 'no_translations':
                $query->set('meta_query', array(
                    array(
                        'key' => '_available_languages',
                        'compare' => 'NOT EXISTS'
                    )
                ));
                break;
                
            case 'multiple_translations':
                $query->set('meta_query', array(
                    array(
                        'key' => '_available_languages',
                        'value' => 'a:2:', // Serialized array with at least 2 items
                        'compare' => 'LIKE'
                    )
                ));
                break;
        }
    }
}
add_action('pre_get_posts', 'gufte_apply_translation_filters');

/**
 * Toplu işlem: Tüm dilleri otomatik tespit et ve ayarla
 */
function gufte_bulk_detect_translations() {
    // Bu fonksiyon admin panelde "Araçlar" menüsüne eklenebilir
    if (isset($_POST['detect_all_translations']) && current_user_can('manage_options')) {
        $posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => -1,
            'meta_query' => array(
                array(
                    'key' => '_available_languages',
                    'compare' => 'NOT EXISTS'
                )
            )
        ));
        
        $updated_count = 0;
        
        foreach ($posts as $post) {
            $detected_languages = gufte_detect_languages_from_content($post->ID);
            
            if (!empty($detected_languages)) {
                update_post_meta($post->ID, '_available_languages', $detected_languages);
                $updated_count++;
            }
        }
        
        add_action('admin_notices', function() use ($updated_count) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . sprintf('%d yazı için çeviri dilleri otomatik olarak tespit edildi ve güncellendi.', $updated_count) . '</p>';
            echo '</div>';
        });
    }
}
add_action('admin_init', 'gufte_bulk_detect_translations');

/**
 * İçerikten dilleri tespit et - SADECE AJAX veya MANUEL ÇAĞRI İÇİN
 * Admin listesinde OTOMATİK ÇALIŞMAZ
 */
function gufte_detect_languages_from_content($post_id) {
    // Bu fonksiyon sadece manuel olarak çağrıldığında çalışır
    // Admin post listesinde otomatik çalışmaz
    
    if (!$post_id || !is_numeric($post_id)) {
        return array('english');
    }
    
    // Cache kontrol
    $cache_key = 'detected_langs_' . $post_id;
    $cached = wp_cache_get($cache_key, 'gufte_languages');
    
    if ($cached !== false) {
        return $cached;
    }
    
    $content = get_post_field('post_content', $post_id);
    
    if (empty($content)) {
        return array('english');
    }
    
    $detected_languages = array();
    
    // Basit pattern matching
    if (strpos($content, '<table') !== false) {
        preg_match('/<thead[^>]*>(.*?)<\/thead>/is', $content, $thead_match);
        
        if (!empty($thead_match[1])) {
            preg_match_all('/<th[^>]*>(.*?)<\/th>/i', $thead_match[1], $headers);
            
            if (!empty($headers[1])) {
                $language_patterns = array(
                    'english' => array('english', 'ingilizce', 'original'),
                    'spanish' => array('spanish', 'español', 'espanol'),
                    'turkish' => array('turkish', 'türkçe', 'turkce'),
                    'german' => array('german', 'deutsch', 'almanca'),
                    'arabic' => array('arabic', 'عربي', 'arapça'),
                    'french' => array('french', 'français', 'francais'),
                    'italian' => array('italian', 'italiano'),
                    'portuguese' => array('portuguese', 'português'),
                    'russian' => array('russian', 'русский'),
                    'japanese' => array('japanese', '日本語'),
                );
                
                foreach ($headers[1] as $header) {
                    $header_lower = strtolower(strip_tags($header));
                    
                    foreach ($language_patterns as $lang_key => $patterns) {
                        foreach ($patterns as $pattern) {
                            if (stripos($header_lower, $pattern) !== false) {
                                $detected_languages[] = $lang_key;
                                break 2;
                            }
                        }
                    }
                }
            }
        }
    }
    
    if (empty($detected_languages)) {
        $detected_languages = array('english');
    }
    
    // Cache'e kaydet (1 saat)
    wp_cache_set($cache_key, $detected_languages, 'gufte_languages', 3600);
    
    return $detected_languages;
}


/**
 * Batch update için AJAX handler - Sadece manuel tetikleme için
 */
function gufte_ajax_detect_all_languages() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    $posts = get_posts(array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'numberposts' => 100, // Batch olarak işle
        'offset' => intval($_POST['offset'] ?? 0),
        'fields' => 'ids'
    ));
    
    $updated = 0;
    foreach ($posts as $post_id) {
        $existing = get_post_meta($post_id, '_available_languages', true);
        
        if (empty($existing)) {
            $detected = gufte_detect_languages_from_content($post_id);
            if (!empty($detected)) {
                update_post_meta($post_id, '_available_languages', $detected);
                $updated++;
            }
        }
    }
    
    wp_send_json_success(array(
        'updated' => $updated,
        'processed' => count($posts)
    ));
}
add_action('wp_ajax_gufte_detect_languages', 'gufte_ajax_detect_all_languages');

/**
 * Admin sayfası yüklendiğinde cache temizle (performans için)
 */
add_action('load-edit.php', function() {
    if (isset($_GET['post_type']) && $_GET['post_type'] === 'post') {
        // Her 30 dakikada bir cache temizle
        $last_clear = get_transient('gufte_admin_cache_cleared');
        if (!$last_clear) {
            wp_cache_flush_group('gufte_admin');
            set_transient('gufte_admin_cache_cleared', time(), 1800);
        }
    }
});

/**
 * Müzik Videosu için özel alanlar ve meta kutusu
 * Bu kodu functions.php dosyanıza ekleyin
 */

/**
 * Yazı düzenleme ekranına müzik videosu için meta kutusu ekler
 */
function gufte_add_music_video_meta_box() {
    add_meta_box(
        'gufte_music_video',
        __('Music Video', 'gufte'),
        'gufte_music_video_meta_box_callback',
        'post',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'gufte_add_music_video_meta_box');

/**
 * Meta kutusu içeriğini görüntüler
 */
function gufte_music_video_meta_box_callback($post) {
    // Nonce alanı oluştur
    wp_nonce_field('gufte_music_video_nonce', 'gufte_music_video_nonce');
    
    // Kayıtlı değerleri al
    $music_video_url = get_post_meta($post->ID, 'music_video_url', true);
    $music_video_embed = get_post_meta($post->ID, 'music_video_embed', true);
    $video_title = get_post_meta($post->ID, 'video_title', true);
    $video_description = get_post_meta($post->ID, 'video_description', true);
    
    // Meta kutusu içeriği
    ?>
    <div class="music-video-fields">
        <p class="description"><?php _e('Şarkının müzik videosunu eklemek için aşağıdaki alanları kullanın.', 'gufte'); ?></p>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="music_video_url" style="font-weight: bold;">
                        <span style="color: #FF0000;">📹 Video URL</span>
                    </label>
                </th>
                <td>
                    <input type="url" id="music_video_url" name="music_video_url" 
                           value="<?php echo esc_attr($music_video_url); ?>" 
                           style="width: 100%;" 
                           placeholder="https://www.youtube.com/watch?v=... veya https://vimeo.com/..." />
                    <p class="description">YouTube, Vimeo veya diğer video platformlarının URL'sini girin.</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="music_video_embed" style="font-weight: bold;">
                        <span style="color: #FF0000;">🎬 Embed Kodu</span>
                    </label>
                </th>
                <td>
                    <textarea id="music_video_embed" name="music_video_embed" 
                              style="width: 100%; height: 120px;" 
                              placeholder='<iframe width="560" height="315" src="https://www.youtube.com/embed/..." frameborder="0" allowfullscreen></iframe>'><?php echo esc_textarea($music_video_embed); ?></textarea>
                    <p class="description">URL yerine embed kodu kullanmak isterseniz buraya yapıştırın. Embed kodu varsa URL göz ardı edilir.</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="video_title" style="font-weight: bold;">
                        <span style="color: #333;">🏷️ Video Başlığı</span>
                    </label>
                </th>
                <td>
                    <input type="text" id="video_title" name="video_title" 
                           value="<?php echo esc_attr($video_title); ?>" 
                           style="width: 100%;" 
                           placeholder="Official Music Video, Live Performance, vb." />
                    <p class="description">Video için özel bir başlık belirlemek isterseniz girin (isteğe bağlı).</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="video_description" style="font-weight: bold;">
                        <span style="color: #333;">📝 Video Açıklaması</span>
                    </label>
                </th>
                <td>
                    <textarea id="video_description" name="video_description" 
                              style="width: 100%; height: 80px;" 
                              placeholder="Video hakkında kısa açıklama..."><?php echo esc_textarea($video_description); ?></textarea>
                    <p class="description">Video için açıklama eklemek isterseniz girin (isteğe bağlı).</p>
                </td>
            </tr>
        </table>
        
        <div style="margin-top: 15px; padding: 10px; background: #e7f3ff; border-left: 4px solid #0073aa;">
            <strong>💡 İpuçları:</strong><br>
            • YouTube URL'leri otomatik olarak embed'e dönüştürülür<br>
            • Vimeo, Dailymotion gibi platformlar da desteklenir<br>
            • Responsive tasarım için video otomatik olarak ayarlanır<br>
            • Video başlığı boşsa, şarkı başlığı kullanılır
        </div>
        
        <?php if (!empty($music_video_url) || !empty($music_video_embed)) : ?>
        <div style="margin-top: 15px; padding: 10px; background: #d4edda; border-left: 4px solid #155724;">
            <strong>✅ Video Önizlemesi:</strong><br>
            <a href="<?php echo esc_url(get_permalink($post->ID)); ?>" target="_blank">
                Sayfayı görüntüle ve videoyu kontrol et
            </a>
        </div>
        <?php endif; ?>
    </div>
    
    <style>
    .music-video-fields .form-table th {
        width: 200px;
        vertical-align: top;
        padding-top: 15px;
    }
    .music-video-fields .form-table td {
        padding-top: 10px;
    }
    .music-video-fields input[type="url"],
    .music-video-fields input[type="text"],
    .music-video-fields textarea {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 8px;
    }
    .music-video-fields input[type="url"]:focus,
    .music-video-fields input[type="text"]:focus,
    .music-video-fields textarea:focus {
        border-color: #0073aa;
        box-shadow: 0 0 0 1px #0073aa;
        outline: none;
    }
    </style>
    <?php
}

/**
 * Yazı kaydedildiğinde özel alan değerlerini kaydeder
 */
function gufte_save_music_video_meta_box_data($post_id) {
    // Nonce kontrolü
    if (!isset($_POST['gufte_music_video_nonce']) || !wp_verify_nonce($_POST['gufte_music_video_nonce'], 'gufte_music_video_nonce')) {
        return;
    }
    
    // Otomatik kayıt kontrolü
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Yetki kontrolü
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Video URL'sini kaydet
    if (isset($_POST['music_video_url'])) {
        $video_url = sanitize_url($_POST['music_video_url']);
        update_post_meta($post_id, 'music_video_url', $video_url);
    }
    
    // Video embed kodunu kaydet
    if (isset($_POST['music_video_embed'])) {
        $video_embed = wp_kses($_POST['music_video_embed'], array(
            'iframe' => array(
                'width' => true,
                'height' => true,
                'src' => true,
                'frameborder' => true,
                'allowfullscreen' => true,
                'allow' => true,
                'style' => true,
                'class' => true,
                'title' => true
            ),
            'video' => array(
                'width' => true,
                'height' => true,
                'src' => true,
                'controls' => true,
                'preload' => true,
                'poster' => true,
                'class' => true,
                'style' => true
            ),
            'source' => array(
                'src' => true,
                'type' => true
            )
        ));
        update_post_meta($post_id, 'music_video_embed', $video_embed);
    }
    
    // Video başlığını kaydet
    if (isset($_POST['video_title'])) {
        $video_title = sanitize_text_field($_POST['video_title']);
        update_post_meta($post_id, 'video_title', $video_title);
    }
    
    // Video açıklamasını kaydet
    if (isset($_POST['video_description'])) {
        $video_description = sanitize_textarea_field($_POST['video_description']);
        update_post_meta($post_id, 'video_description', $video_description);
    }
}
add_action('save_post', 'gufte_save_music_video_meta_box_data');

/**
 * URL'den embed kodu oluşturma fonksiyonu
 */
function gufte_normalize_video_embed($embed_html, $fallback_title = '') {
    if (empty($embed_html)) {
        return '';
    }

    $default_title = $fallback_title ? $fallback_title : __('Embedded video', 'gufte');

    $processed = preg_replace_callback(
        '/<iframe\b([^>]*)>/i',
        function ($matches) use ($default_title) {
            $attr_string = $matches[1];
            $attr_data   = wp_kses_hair($attr_string, wp_allowed_protocols());

            $attributes = array();
            foreach ($attr_data as $attr) {
                $name                = strtolower($attr['name']);
                $attributes[$name]   = $attr['value'];
            }

            if (!empty($attributes['src'])) {
                $src       = $attributes['src'];
                $src_parts = wp_parse_url($src);

                if (!empty($src_parts['host']) && false !== strpos($src_parts['host'], 'youtube')) {
                    $video_id = '';

                    if (!empty($src_parts['path'])) {
                        if (preg_match('#/embed/([^/]+)#', $src_parts['path'], $id_match)) {
                            $video_id = $id_match[1];
                        } elseif (preg_match('#/([^/]+)$#', $src_parts['path'], $id_match)) {
                            $video_id = $id_match[1];
                        }
                    }

                    if (!$video_id && !empty($src)) {
                        if (preg_match('/[?&]v=([^&]+)/', $src, $id_match)) {
                            $video_id = $id_match[1];
                        }
                    }

                    if ($video_id) {
                        $video_id = preg_replace('/[^a-zA-Z0-9_\-]/', '', $video_id);

                        $existing_params = array();
                        if (!empty($src_parts['query'])) {
                            parse_str($src_parts['query'], $existing_params);
                        }

                        $existing_params['rel']            = '0';
                        $existing_params['modestbranding'] = '1';
                        $existing_params['playsinline']    = isset($existing_params['playsinline']) ? $existing_params['playsinline'] : '1';
                        if (!isset($existing_params['enablejsapi'])) {
                            $existing_params['enablejsapi'] = '0';
                        }

                        $param_string          = http_build_query($existing_params, '', '&', PHP_QUERY_RFC3986);
                        $attributes['src']     = 'https://www.youtube-nocookie.com/embed/' . $video_id . ($param_string ? '?' . $param_string : '');
                        $attributes['referrerpolicy'] = isset($attributes['referrerpolicy']) ? $attributes['referrerpolicy'] : 'strict-origin-when-cross-origin';
                    }
                }
            }

            if (empty($attributes['title'])) {
                $attributes['title'] = $default_title;
            }

            if (!empty($attributes['referrerpolicy'])) {
                $attributes['referrerpolicy'] = $attributes['referrerpolicy'];
            } else {
                $attributes['referrerpolicy'] = 'strict-origin-when-cross-origin';
            }

            $attributes['loading'] = 'lazy';

            if (empty($attributes['allow'])) {
                $attributes['allow'] = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
            }

            if (!array_key_exists('allowfullscreen', $attributes)) {
                $attributes['allowfullscreen'] = 'allowfullscreen';
            }

            if (!empty($attributes['width'])) {
                $attributes['width'] = (string) (int) $attributes['width'];
            }

            if (!empty($attributes['height'])) {
                $attributes['height'] = (string) (int) $attributes['height'];
            }

            $attribute_html = '';
            foreach ($attributes as $name => $value) {
                if ($value === '') {
                    $attribute_html .= ' ' . $name;
                } else {
                    $attribute_html .= ' ' . $name . '="' . esc_attr($value) . '"';
                }
            }

            return '<iframe' . $attribute_html . '>';
        },
        $embed_html
    );

    return $processed;
}

function gufte_convert_video_url_to_embed($url, $width = 560, $height = 315) {
    if (empty($url)) {
        return '';
    }
    
    $embed_code = '';
    
    // YouTube URL'leri
    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
        $video_id = $matches[1];
        $params    = array(
            'rel'            => '0',
            'modestbranding' => '1',
            'playsinline'    => '1',
            'enablejsapi'    => '0',
        );

        $param_string = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $src          = 'https://www.youtube-nocookie.com/embed/' . rawurlencode($video_id) . '?' . $param_string;

        $embed_code = sprintf(
            '<iframe width="%1$s" height="%2$s" src="%3$s" title="%4$s" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen="allowfullscreen"></iframe>',
            esc_attr($width),
            esc_attr($height),
            esc_url($src),
            esc_attr__('YouTube video player', 'gufte')
        );
    }
    // Vimeo URL'leri
    elseif (preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
        $video_id = $matches[1];
        $src        = 'https://player.vimeo.com/video/' . rawurlencode($video_id);
        $embed_code = sprintf(
            '<iframe src="%1$s" width="%2$s" height="%3$s" frameborder="0" loading="lazy" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen="allowfullscreen" title="%4$s" referrerpolicy="strict-origin-when-cross-origin"></iframe>',
            esc_url($src),
            esc_attr($width),
            esc_attr($height),
            esc_attr__('Vimeo video player', 'gufte')
        );
    }
    // Dailymotion URL'leri
    elseif (preg_match('/dailymotion\.com\/video\/([^_]+)/', $url, $matches)) {
        $video_id = $matches[1];
        $src        = 'https://www.dailymotion.com/embed/video/' . rawurlencode($video_id);
        $embed_code = sprintf(
            '<iframe src="%1$s" width="%2$s" height="%3$s" frameborder="0" loading="lazy" allow="autoplay" allowfullscreen="allowfullscreen" title="%4$s" referrerpolicy="strict-origin-when-cross-origin"></iframe>',
            esc_url($src),
            esc_attr($width),
            esc_attr($height),
            esc_attr__('Dailymotion video player', 'gufte')
        );
    }
    
    return gufte_normalize_video_embed($embed_code, '');
}

/**
 * Video görüntüleme fonksiyonu (single.php'de kullanılacak)
 */
function gufte_display_music_video($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    // Video bilgilerini al
    $video_embed = get_post_meta($post_id, 'music_video_embed', true);
    $video_url = get_post_meta($post_id, 'music_video_url', true);
    $video_title = get_post_meta($post_id, 'video_title', true);
    $video_description = get_post_meta($post_id, 'video_description', true);
    
    // Önce embed kodu varsa onu kullan, yoksa URL'yi embed'e çevir
    $final_embed = '';
    if (!empty($video_embed)) {
        $final_embed = $video_embed;
    } elseif (!empty($video_url)) {
        $final_embed = gufte_convert_video_url_to_embed($video_url);
    }
    
    // Video yoksa hiçbir şey gösterme
    if (empty($final_embed)) {
        return '';
    }
    
    // Video başlığı yoksa şarkı başlığını kullan
    if (empty($video_title)) {
        $video_title = get_the_title($post_id) . ' - Music Video';
    }

    $final_embed = gufte_normalize_video_embed($final_embed, $video_title);
    
    // HTML çıktısını oluştur
    ob_start();
    ?>
    <div class="music-video-section mb-8 p-6 bg-white rounded-lg shadow-md border border-gray-200">
        <h2 class="text-2xl font-bold text-gray-800 mb-4 pb-4 border-b border-gray-200 flex items-center">
            <span class="iconify mr-3 text-red-600 text-3xl" data-icon="mdi:play-circle"></span>
            <?php echo esc_html($video_title); ?>
        </h2>
        
        <?php if (!empty($video_description)) : ?>
        <p class="text-gray-600 mb-4 text-sm"><?php echo esc_html($video_description); ?></p>
        <?php endif; ?>
        
        <div class="video-container relative">
            <div class="video-wrapper aspect-video w-full rounded-lg overflow-hidden shadow-lg bg-black">
                <?php 
                // Embed kodunu responsive hale getir
                $responsive_embed = str_replace(
                    array('width="560"', 'width="640"', 'width="480"', 'height="315"', 'height="360"', 'height="270"'),
                    array('width="100%"', 'width="100%"', 'width="100%"', 'height="100%"', 'height="100%"', 'height="100%"'),
                    $final_embed
                );
                
                // Iframe'e responsive class ekle
                $responsive_embed = str_replace('<iframe', '<iframe class="absolute top-0 left-0 w-full h-full"', $responsive_embed);
                
                echo $responsive_embed;
                ?>
            </div>
        </div>
        
        <div class="video-info mt-4 flex items-center justify-between text-sm text-gray-500">
            <span class="flex items-center">
                <span class="iconify mr-1" data-icon="mdi:video"></span>
                <?php esc_html_e('Official Music Video', 'gufte'); ?>
            </span>
            <span class="flex items-center">
                <span class="iconify mr-1" data-icon="mdi:eye"></span>
                <?php esc_html_e('Click to watch', 'gufte'); ?>
            </span>
        </div>
    </div>
    
    <style>
    .music-video-section .video-wrapper {
        position: relative;
        padding-bottom: 56.25%; /* 16:9 aspect ratio */
        height: 0;
        overflow: hidden;
    }
    
    .music-video-section .video-wrapper iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: 0;
    }
    
    @media (max-width: 640px) {
        .music-video-section {
            margin-left: -1rem;
            margin-right: -1rem;
            border-radius: 0;
        }
    }
    </style>
    <?php
    
    return ob_get_clean();
}

/**
 * Shortcode oluştur (içerikte [music_video] şeklinde kullanılabilir)
 */
function gufte_music_video_shortcode($atts) {
    $atts = shortcode_atts(array(
        'post_id' => get_the_ID()
    ), $atts);
    
    return gufte_display_music_video($atts['post_id']);
}
add_shortcode('music_video', 'gufte_music_video_shortcode');
/**
 * Admin panelde posts listesine video sütunu ekleme
 * Bu kodu functions.php dosyanıza ekleyin (isteğe bağlı)
 */

/**
 * Posts listesine "Video" sütunu ekle
 */
function gufte_add_video_column($columns) {
    // Video sütununu tarih sütunundan önce ekle
    $new_columns = array();
    
    foreach ($columns as $key => $value) {
        if ($key === 'date') {
            // Tarih sütunundan önce video sütununu ekle
            $new_columns['music_video'] = '<span class="dashicons dashicons-video-alt3" title="Müzik Videosu"></span> Video';
        }
        $new_columns[$key] = $value;
    }
    
    return $new_columns;
}
add_filter('manage_lyrics_posts_columns', 'gufte_add_video_column');

/**
 * Video sütununun içeriğini doldur
 */
function gufte_display_video_column($column, $post_id) {
    if ($column === 'music_video') {
        $video_url = get_post_meta($post_id, 'music_video_url', true);
        $video_embed = get_post_meta($post_id, 'music_video_embed', true);
        $video_title = get_post_meta($post_id, 'video_title', true);
        
        if (!empty($video_embed) || !empty($video_url)) {
            echo '<div class="video-status-indicator">';
            
            // Video türünü belirle
            $video_platform = '';
            if (!empty($video_url)) {
                if (strpos($video_url, 'youtube.com') !== false || strpos($video_url, 'youtu.be') !== false) {
                    $video_platform = 'YouTube';
                    $platform_color = '#FF0000';
                } elseif (strpos($video_url, 'vimeo.com') !== false) {
                    $video_platform = 'Vimeo';
                    $platform_color = '#1AB7EA';
                } elseif (strpos($video_url, 'dailymotion.com') !== false) {
                    $video_platform = 'Dailymotion';
                    $platform_color = '#0066CC';
                } else {
                    $video_platform = 'Video';
                    $platform_color = '#666666';
                }
            } else {
                $video_platform = 'Embed';
                $platform_color = '#666666';
            }
            
            echo '<div class="video-badge" style="display: inline-flex; align-items: center; background-color: ' . $platform_color . '; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px; font-weight: bold; margin-bottom: 2px;">';
            echo '<span class="dashicons dashicons-video-alt3" style="font-size: 12px; margin-right: 3px;"></span>';
            echo esc_html($video_platform);
            echo '</div>';
            
            if (!empty($video_title)) {
                echo '<div class="video-title" style="font-size: 11px; color: #666; margin-top: 2px;" title="' . esc_attr($video_title) . '">';
                echo esc_html(wp_trim_words($video_title, 5, '...'));
                echo '</div>';
            }
            
            // Video URL'sini kısalt ve göster
            if (!empty($video_url)) {
                $short_url = strlen($video_url) > 30 ? substr($video_url, 0, 30) . '...' : $video_url;
                echo '<div class="video-url" style="font-size: 10px; color: #999; margin-top: 1px;" title="' . esc_attr($video_url) . '">';
                echo esc_html($short_url);
                echo '</div>';
            }
            
            echo '</div>';
        } else {
            echo '<span class="no-video" style="color: #ccc; font-style: italic; font-size: 11px;">Video yok</span>';
        }
    }
}
add_action('manage_posts_custom_column', 'gufte_display_video_column', 10, 2);

/**
 * Video sütununu sıralanabilir yap
 */
function gufte_make_video_column_sortable($columns) {
    $columns['music_video'] = 'music_video';
    return $columns;
}
add_filter('manage_edit-post_sortable_columns', 'gufte_make_video_column_sortable');

/**
 * Video sütunu sıralaması
 */
function gufte_sort_posts_by_video($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }
    
    if ($query->get('orderby') === 'music_video') {
        $query->set('meta_key', 'music_video_url');
        $query->set('orderby', 'meta_value');
        
        // Video olan/olmayan yazıları sırala
        $meta_query = array(
            'relation' => 'OR',
            array(
                'key' => 'music_video_url',
                'compare' => 'EXISTS'
            ),
            array(
                'key' => 'music_video_embed',
                'compare' => 'EXISTS'
            ),
            array(
                'key' => 'music_video_url',
                'compare' => 'NOT EXISTS'
            )
        );
        $query->set('meta_query', $meta_query);
    }
}
add_action('pre_get_posts', 'gufte_sort_posts_by_video');

/**
 * Video sütunu için CSS stilleri
 */
function gufte_add_admin_video_column_styles() {
    $screen = get_current_screen();
    
    if ($screen && $screen->id === 'edit-post') {
        ?>
        <style type="text/css">
        .column-music_video {
            width: 120px;
        }
        
        .video-status-indicator {
            font-size: 12px;
            line-height: 1.2;
        }
        
        .video-badge {
            white-space: nowrap;
        }
        
        .video-title {
            max-width: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .video-url {
            max-width: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        /* Responsive tasarım */
        @media screen and (max-width: 782px) {
            .column-music_video {
                display: none !important;
            }
        }
        
        /* Sütun başlığı ikonu */
        .manage-column .dashicons-video-alt3 {
            color: #50575e;
            font-size: 16px;
            vertical-align: middle;
        }
        </style>
        <?php
    }
}
add_action('admin_head', 'gufte_add_admin_video_column_styles');

/**
 * Video filtreleri ekle
 */
function gufte_add_video_filter_dropdown() {
    global $typenow;

    if ($typenow === 'lyrics') {
        $selected_filter = isset($_GET['video_filter']) ? $_GET['video_filter'] : '';

        echo '<select name="video_filter" id="video-filter">';
        echo '<option value="">Tüm videolar</option>';
        echo '<option value="has_video"' . selected($selected_filter, 'has_video', false) . '>Videosu olan</option>';
        echo '<option value="no_video"' . selected($selected_filter, 'no_video', false) . '>Videosu olmayan</option>';
        echo '<option value="youtube"' . selected($selected_filter, 'youtube', false) . '>YouTube videoları</option>';
        echo '<option value="vimeo"' . selected($selected_filter, 'vimeo', false) . '>Vimeo videoları</option>';
        echo '</select>';
    }
}
add_action('restrict_manage_posts', 'gufte_add_video_filter_dropdown');

/**
 * Video filtrelerini uygula
 */
function gufte_apply_video_filters($query) {
    global $pagenow, $typenow;

    if ($pagenow === 'edit.php' && $typenow === 'lyrics' && isset($_GET['video_filter']) && !empty($_GET['video_filter'])) {
        $filter = $_GET['video_filter'];
        
        switch ($filter) {
            case 'has_video':
                $query->set('meta_query', array(
                    'relation' => 'OR',
                    array(
                        'key' => 'music_video_url',
                        'compare' => 'EXISTS'
                    ),
                    array(
                        'key' => 'music_video_embed',
                        'compare' => 'EXISTS'
                    )
                ));
                break;
                
            case 'no_video':
                $query->set('meta_query', array(
                    'relation' => 'AND',
                    array(
                        'key' => 'music_video_url',
                        'compare' => 'NOT EXISTS'
                    ),
                    array(
                        'key' => 'music_video_embed',
                        'compare' => 'NOT EXISTS'
                    )
                ));
                break;
                
            case 'youtube':
                $query->set('meta_query', array(
                    array(
                        'key' => 'music_video_url',
                        'value' => 'youtube',
                        'compare' => 'LIKE'
                    )
                ));
                break;
                
            case 'vimeo':
                $query->set('meta_query', array(
                    array(
                        'key' => 'music_video_url',
                        'value' => 'vimeo',
                        'compare' => 'LIKE'
                    )
                ));
                break;
        }
    }
}
add_action('pre_get_posts', 'gufte_apply_video_filters');
/**
 * Release Date Sistemi - Apple Music API Entegrasyonu
 * Bu kodu functions.php dosyanıza ekleyin
 */

/**
 * Release Date için meta kutusu ekle
 */
function gufte_add_release_date_meta_box() {
    add_meta_box(
        'gufte_release_date',
        __('Release Date Information', 'gufte'),
        'gufte_release_date_meta_box_callback',
        'lyrics',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'gufte_add_release_date_meta_box');

/**
 * Release Date meta kutusu içeriği
 */
function gufte_release_date_meta_box_callback($post) {
    // Nonce alanı oluştur
    wp_nonce_field('gufte_release_date_nonce', 'gufte_release_date_nonce');
    
    // Mevcut değerleri al
    $release_date = get_post_meta($post->ID, '_release_date', true);
    $music_genre = get_post_meta($post->ID, '_music_genre', true);
    $apple_music_id = get_post_meta($post->ID, '_apple_music_id', true);
    $auto_fetch_enabled = get_post_meta($post->ID, '_auto_fetch_release_date', true);
    
    // Apple Music URL'den ID çıkarmaya çalış
    $apple_music_url = get_post_meta($post->ID, 'apple_music_url', true);
    if (empty($apple_music_id) && !empty($apple_music_url)) {
        preg_match('/i=(\d+)/', $apple_music_url, $matches);
        if (!empty($matches[1])) {
            $apple_music_id = $matches[1];
            update_post_meta($post->ID, '_apple_music_id', $apple_music_id);
        }
    }
    
    ?>
    <div class="release-date-fields">
        <!-- Manual Release Date -->
        <p>
            <label for="release_date" style="display: block; font-weight: bold; margin-bottom: 5px;">
                <span class="iconify" data-icon="mdi:calendar" style="color: #666;"></span>
                Release Date
            </label>
            <input type="date" id="release_date" name="release_date" 
                   value="<?php echo esc_attr($release_date); ?>" 
                   style="width: 100%;" />
            <span class="description" style="font-size: 11px; color: #666;">
                Manual entry or auto-fetched from Apple Music
            </span>
        </p>

        <!-- Music Genre -->
        <p>
            <label for="music_genre" style="display: block; font-weight: bold; margin-bottom: 5px;">
                <span class="iconify" data-icon="mdi:music-note" style="color: #666;"></span>
                Music Genre
            </label>
            <input type="text" id="music_genre" name="music_genre" 
                   value="<?php echo esc_attr($music_genre); ?>" 
                   style="width: 100%;" 
                   placeholder="Pop, Rock, Hip-Hop, etc." />
            <span class="description" style="font-size: 11px; color: #666;">
                Manual entry or auto-fetched from Apple Music
            </span>
        </p>

        <!-- Apple Music ID -->
        <p>
            <label for="apple_music_track_id" style="display: block; font-weight: bold; margin-bottom: 5px;">
                <span class="iconify" data-icon="mdi:apple" style="color: #FA243C;"></span>
                Apple Music Track ID
            </label>
            <input type="text" id="apple_music_track_id" name="apple_music_track_id" 
                   value="<?php echo esc_attr($apple_music_id); ?>" 
                   style="width: 100%;" 
                   placeholder="1234567890" />
            <span class="description" style="font-size: 11px; color: #666;">
                Auto-detected from Apple Music URL or enter manually
            </span>
        </p>

        <!-- Auto Fetch Toggle -->
        <p>
            <label style="display: flex; align-items: center; margin: 10px 0;">
                <input type="checkbox" name="auto_fetch_release_date" value="1" 
                       <?php checked($auto_fetch_enabled, 1); ?> 
                       style="margin-right: 8px;" />
                <span style="font-weight: bold;">
                    <span class="iconify" data-icon="mdi:refresh-auto" style="color: #0073aa;"></span>
                    Auto-fetch from Apple Music
                </span>
            </label>
            <span class="description" style="font-size: 11px; color: #666; margin-left: 20px;">
                Automatically fetch release date and genre when Apple Music ID is available
            </span>
        </p>

        <!-- Fetch Button -->
        <div style="margin: 15px 0;">
            <button type="button" id="fetch_release_date" class="button button-secondary" style="width: 100%;">
                <span class="iconify" data-icon="mdi:download"></span>
                Fetch Data from Apple Music
            </button>
            <div id="release_date_status" style="margin-top: 8px; font-size: 12px;"></div>
        </div>

        <!-- Current Info Display -->
        <?php if (!empty($release_date) || !empty($music_genre)) : ?>
        <div style="background: #e7f3ff; padding: 10px; border-left: 4px solid #0073aa; margin: 10px 0;">
            <?php if (!empty($release_date)) : ?>
            <strong>📅 Release Date:</strong><br>
            <span style="font-size: 14px;"><?php echo esc_html(date('F j, Y', strtotime($release_date))); ?></span>
            <?php
            $time_ago = human_time_diff(strtotime($release_date), current_time('timestamp'));
            echo '<br><span style="font-size: 11px; color: #666;">(' . $time_ago . ' ago)</span>';
            ?>
            <?php endif; ?>
            
            <?php if (!empty($music_genre)) : ?>
            <?php if (!empty($release_date)) echo '<br><br>'; ?>
            <strong>🎵 Genre:</strong><br>
            <span style="font-size: 14px;"><?php echo esc_html($music_genre); ?></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Instructions -->
        <div style="background: #f0f0f1; padding: 10px; border-radius: 4px; margin-top: 15px;">
            <strong>💡 How to use:</strong><br>
            <small style="color: #666;">
                • Enter Apple Music Track ID manually<br>
                • Or paste Apple Music URL in the Music Links section<br>
                • Enable auto-fetch to get release date and genre automatically<br>
                • Manual entries override auto-fetch data
            </small>
        </div>
    </div>

    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Apple Music URL'den ID çıkarma
        function extractAppleMusicId(url) {
            var match = url.match(/i=(\d+)/);
            return match ? match[1] : null;
        }

        // Apple Music URL değişikliklerini dinle
        $(document).on('change', 'input[name="apple_music_url"]', function() {
            var url = $(this).val();
            var id = extractAppleMusicId(url);
            if (id) {
                $('#apple_music_track_id').val(id);
                $('#release_date_status').html('<span style="color: #0073aa;">Apple Music ID detected: ' + id + '</span>');
            }
        });

        // Release date fetch butonu
        $('#fetch_release_date').on('click', function() {
            var appleMusicId = $('#apple_music_track_id').val().trim();
            var button = $(this);
            
            if (!appleMusicId) {
                $('#release_date_status').html('<span style="color: #d63638;">Please enter Apple Music Track ID first.</span>');
                return;
            }
            
            button.prop('disabled', true).text('Fetching...');
            $('#release_date_status').html('<span style="color: #0073aa;">Fetching data from Apple Music...</span>');
            
            // AJAX isteği
            $.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'fetch_apple_music_release_date',
                    post_id: <?php echo $post->ID; ?>,
                    apple_music_id: appleMusicId,
                    nonce: $('#gufte_release_date_nonce').val()
                },
                success: function(response) {
                    button.prop('disabled', false).html('<span class="iconify" data-icon="mdi:download"></span> Fetch Data from Apple Music');
                    
                    if (response.success) {
                        if (response.data.date) {
                            $('#release_date').val(response.data.date);
                        }
                        if (response.data.genre) {
                            $('#music_genre').val(response.data.genre);
                        }
                        
                        var statusMessage = '✓ Successfully fetched: ';
                        var fetchedItems = [];
                        if (response.data.formatted_date) fetchedItems.push('Release date (' + response.data.formatted_date + ')');
                        if (response.data.genre) fetchedItems.push('Genre (' + response.data.genre + ')');
                        
                        statusMessage += fetchedItems.join(', ');
                        $('#release_date_status').html('<span style="color: #00a32a;">' + statusMessage + '</span>');
                        
                    } else {
                        $('#release_date_status').html('<span style="color: #d63638;">✗ ' + response.data + '</span>');
                    }
                },
                error: function() {
                    button.prop('disabled', false).html('<span class="iconify" data-icon="mdi:download"></span> Fetch Data from Apple Music');
                    $('#release_date_status').html('<span style="color: #d63638;">Connection error occurred.</span>');
                }
            });
        });

        // Auto-fetch checkbox değişikliklerini dinle
        $('input[name="auto_fetch_release_date"]').on('change', function() {
            if ($(this).is(':checked')) {
                var appleMusicId = $('#apple_music_track_id').val().trim();
                if (appleMusicId && !$('#release_date').val()) {
                    $('#fetch_release_date').click();
                }
            }
        });
    });
    </script>

    <style>
    .release-date-fields input[type="date"],
    .release-date-fields input[type="text"] {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 6px 8px;
    }
    
    .release-date-fields input[type="date"]:focus,
    .release-date-fields input[type="text"]:focus {
        border-color: #0073aa;
        box-shadow: 0 0 0 1px #0073aa;
        outline: none;
    }
    
    .release-date-fields .button {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }
    
    .iconify {
        width: 16px;
        height: 16px;
    }
    </style>
    <?php
}

/**
 * Release Date verilerini kaydet
 */
function gufte_save_release_date_meta_data($post_id) {
    // Nonce kontrolü
    if (!isset($_POST['gufte_release_date_nonce']) || 
        !wp_verify_nonce($_POST['gufte_release_date_nonce'], 'gufte_release_date_nonce')) {
        return;
    }
    
    // Otomatik kayıt kontrolü
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Yetki kontrolü
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Release Date'i kaydet
    if (isset($_POST['release_date'])) {
        $release_date = sanitize_text_field($_POST['release_date']);
        update_post_meta($post_id, '_release_date', $release_date);
    }
    
    // Music Genre'yi kaydet
    if (isset($_POST['music_genre'])) {
        $music_genre = sanitize_text_field($_POST['music_genre']);
        update_post_meta($post_id, '_music_genre', $music_genre);
    }
    
    // Apple Music Track ID'yi kaydet
    if (isset($_POST['apple_music_track_id'])) {
        $apple_music_id = sanitize_text_field($_POST['apple_music_track_id']);
        update_post_meta($post_id, '_apple_music_id', $apple_music_id);
    }
    
    // Auto-fetch ayarını kaydet
    $auto_fetch = isset($_POST['auto_fetch_release_date']) ? 1 : 0;
    update_post_meta($post_id, '_auto_fetch_release_date', $auto_fetch);
    
    // Eğer auto-fetch etkinse ve release date boşsa, otomatik çekmeyi dene
    if ($auto_fetch && empty($release_date) && !empty($_POST['apple_music_track_id'])) {
        gufte_fetch_release_date_from_apple_music($post_id, sanitize_text_field($_POST['apple_music_track_id']));
    }
}
add_action('save_post', 'gufte_save_release_date_meta_data');

/**
 * Apple Music'ten release date çekme (AJAX handler)
 */
function gufte_ajax_fetch_apple_music_release_date() {
    // Nonce kontrolü
    if (!isset($_POST['nonce']) || 
        !wp_verify_nonce($_POST['nonce'], 'gufte_release_date_nonce')) {
        wp_send_json_error('Security verification failed.');
        return;
    }
    
    // Parametreleri kontrol et
    if (!isset($_POST['post_id']) || !isset($_POST['apple_music_id'])) {
        wp_send_json_error('Required parameters missing.');
        return;
    }
    
    $post_id = intval($_POST['post_id']);
    $apple_music_id = sanitize_text_field($_POST['apple_music_id']);
    
    // Apple Music'ten release date çek
    $result = gufte_fetch_release_date_from_apple_music($post_id, $apple_music_id);
    
    if ($result['success']) {
        $response_data = array(
            'date' => $result['date'],
            'formatted_date' => date('F j, Y', strtotime($result['date']))
        );
        
        if (!empty($result['genre'])) {
            $response_data['genre'] = $result['genre'];
        }
        
        wp_send_json_success($response_data);
    } else {
        wp_send_json_error($result['message']);
    }
}
add_action('wp_ajax_fetch_apple_music_release_date', 'gufte_ajax_fetch_apple_music_release_date');

/**
 * Apple Music API'den release date çekme fonksiyonu
 */
function gufte_fetch_release_date_from_apple_music($post_id, $apple_music_id) {
    // iTunes Search API kullan (Apple Music verilerine erişim için)
    $api_url = "https://itunes.apple.com/lookup?id=" . urlencode($apple_music_id) . "&entity=song";
    
    // API isteği gönder
    $response = wp_remote_get($api_url, array(
        'timeout' => 30,
        'headers' => array(
            'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url()
        )
    ));
    
    if (is_wp_error($response)) {
        return array(
            'success' => false,
            'message' => 'Failed to connect to Apple Music API: ' . $response->get_error_message()
        );
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (empty($data) || !isset($data['results']) || empty($data['results'])) {
        return array(
            'success' => false,
            'message' => 'No data found for this Apple Music ID.'
        );
    }
    
    $track_info = $data['results'][0];
    
    // Release date'i al
    if (!isset($track_info['releaseDate'])) {
        return array(
            'success' => false,
            'message' => 'Release date not available for this track.'
        );
    }
    
    $release_date = date('Y-m-d', strtotime($track_info['releaseDate']));
    
    // Genre bilgisini al
    $genre = isset($track_info['primaryGenreName']) ? $track_info['primaryGenreName'] : '';
    
    // Meta verileri kaydet
    update_post_meta($post_id, '_release_date', $release_date);
    update_post_meta($post_id, '_apple_music_id', $apple_music_id);
    
    if (!empty($genre)) {
        update_post_meta($post_id, '_music_genre', $genre);
    }
    
    // Ek bilgileri de kaydet (varsa)
    if (isset($track_info['trackName'])) {
        update_post_meta($post_id, '_apple_music_track_name', $track_info['trackName']);
    }
    
    if (isset($track_info['artistName'])) {
        $artist_name = sanitize_text_field($track_info['artistName']);
        update_post_meta($post_id, '_apple_music_artist_name', $artist_name);
        update_post_meta($post_id, '_artist_name', $artist_name);

        // Singer taxonomy'sine ekle
        $singer_term = term_exists($artist_name, 'singer');
        if (!$singer_term) {
            $singer_term = wp_insert_term($artist_name, 'singer');
        }

        if (!is_wp_error($singer_term)) {
            wp_set_object_terms($post_id, (int)$singer_term['term_id'], 'singer', false);
        }
    }

    if (isset($track_info['collectionName'])) {
        $album_name = sanitize_text_field($track_info['collectionName']);
        update_post_meta($post_id, '_apple_music_album_name', $album_name);

        // Album taxonomy'sine ekle
        $album_term = term_exists($album_name, 'album');
        if (!$album_term) {
            $album_term = wp_insert_term($album_name, 'album');
        }

        if (!is_wp_error($album_term)) {
            wp_set_object_terms($post_id, (int)$album_term['term_id'], 'album', false);
        }
    }

    if (isset($track_info['primaryGenreName'])) {
        update_post_meta($post_id, '_apple_music_genre', $track_info['primaryGenreName']);
    }
    
    $result_data = array(
        'success' => true,
        'date' => $release_date,
        'message' => 'Data successfully fetched from Apple Music.'
    );
    
    if (!empty($genre)) {
        $result_data['genre'] = $genre;
    }
    
    return $result_data;
}

/**
 * Apple Music URL'den otomatik ID çıkarma ve release date çekme
 */
function gufte_auto_extract_apple_music_data($post_id) {
    // Sadece yeni kayıtlarda veya release date yoksa çalış
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    $existing_date = get_post_meta($post_id, '_release_date', true);
    $existing_genre = get_post_meta($post_id, '_music_genre', true);
    $auto_fetch_enabled = get_post_meta($post_id, '_auto_fetch_release_date', true);
    
    // Auto-fetch etkin değilse veya zaten veriler varsa atla
    if (!$auto_fetch_enabled || (!empty($existing_date) && !empty($existing_genre))) {
        return;
    }
    
    // Apple Music URL'den ID çıkarmaya çalış
    $apple_music_url = get_post_meta($post_id, 'apple_music_url', true);
    $apple_music_embed = get_post_meta($post_id, 'apple_music_embed', true);
    
    $apple_music_id = '';
    
    // URL'den ID çıkar
    if (!empty($apple_music_url)) {
        preg_match('/i=(\d+)/', $apple_music_url, $matches);
        if (!empty($matches[1])) {
            $apple_music_id = $matches[1];
        }
    }
    
    // Embed kodundan ID çıkar (fallback)
    if (empty($apple_music_id) && !empty($apple_music_embed)) {
        preg_match('/i=(\d+)/', $apple_music_embed, $matches);
        if (!empty($matches[1])) {
            $apple_music_id = $matches[1];
        }
    }
    
    // ID bulunduysa release date çek
    if (!empty($apple_music_id)) {
        update_post_meta($post_id, '_apple_music_id', $apple_music_id);
        gufte_fetch_release_date_from_apple_music($post_id, $apple_music_id);
    }
}

// Post kaydedildiğinde otomatik çalıştır
add_action('save_post', 'gufte_auto_extract_apple_music_data', 25);

/**
 * Admin posts listesine Release Date sütunu ekle
 */
function gufte_add_release_date_admin_column($columns) {
    $new_columns = array();
    
    foreach ($columns as $key => $value) {
        if ($key === 'date') {
            $new_columns['release_date'] = '<span class="dashicons dashicons-calendar-alt" title="Release Date"></span> Release';
        }
        $new_columns[$key] = $value;
    }
    
    return $new_columns;
}
add_filter('manage_lyrics_posts_columns', 'gufte_add_release_date_admin_column');

/**
 * Release Date sütunu içeriğini göster
 */
function gufte_display_release_date_admin_column($column, $post_id) {
    if ($column === 'release_date') {
        $release_date = get_post_meta($post_id, '_release_date', true);
        $music_genre = get_post_meta($post_id, '_music_genre', true);
        $apple_music_id = get_post_meta($post_id, '_apple_music_id', true);
        $auto_fetch = get_post_meta($post_id, '_auto_fetch_release_date', true);
        
        if (!empty($release_date) || !empty($music_genre)) {
            echo '<div class="release-date-info">';
            
            // Release Date
            if (!empty($release_date)) {
                $formatted_date = date('M j, Y', strtotime($release_date));
                $time_ago = human_time_diff(strtotime($release_date), current_time('timestamp'));
                
                echo '<strong style="color: #0073aa;">' . esc_html($formatted_date) . '</strong><br>';
                echo '<span style="font-size: 11px; color: #666;">' . esc_html($time_ago) . ' ago</span>';
            }
            
            // Genre
            if (!empty($music_genre)) {
                if (!empty($release_date)) echo '<br>';
                echo '<span class="genre-badge" style="display: inline-block; background: #f0f0f1; color: #3c434a; padding: 1px 6px; border-radius: 3px; font-size: 10px; margin-top: 2px;">';
                echo '🎵 ' . esc_html($music_genre);
                echo '</span>';
            }
            
            // Apple Music badge
            if (!empty($apple_music_id)) {
                echo '<br><span class="apple-music-badge" style="display: inline-flex; align-items: center; background: #FA243C; color: white; padding: 1px 4px; border-radius: 2px; font-size: 9px; margin-top: 2px;">';
                echo '<span class="iconify" data-icon="mdi:apple" style="width: 10px; height: 10px; margin-right: 2px;"></span>';
                echo 'AM</span>';
            }
            
            // Auto-fetch indicator
            if ($auto_fetch) {
                echo ' <span style="color: #00a32a; font-size: 10px;" title="Auto-fetch enabled">🔄</span>';
            }
            
            echo '</div>';
        } else {
            echo '<span style="color: #d63638; font-style: italic; font-size: 11px;">No data</span>';
            
            if (!empty($apple_music_id)) {
                echo '<br><span style="font-size: 10px; color: #666;">Apple ID: ' . esc_html($apple_music_id) . '</span>';
            }
        }
    }
}
add_action('manage_posts_custom_column', 'gufte_display_release_date_admin_column', 10, 2);

/**
 * Add a Last Modified column to the posts listing.
 */
function gufte_add_modified_date_admin_column($columns) {
    $new_columns = array();

    foreach ($columns as $key => $label) {
        $new_columns[$key] = $label;

        if ('date' === $key) {
            $new_columns['modified_date'] = '<span class="dashicons dashicons-update" title="' . esc_attr__('Last Modified', 'gufte') . '"></span> ' . esc_html__('Modified', 'gufte');
        }
    }

    if (!isset($new_columns['modified_date'])) {
        $new_columns['modified_date'] = esc_html__('Modified', 'gufte');
    }

    return $new_columns;
}
add_filter('manage_lyrics_posts_columns', 'gufte_add_modified_date_admin_column', 12);

/**
 * Render the Last Modified column content.
 */
function gufte_display_modified_date_admin_column($column, $post_id) {
    if ('modified_date' !== $column) {
        return;
    }

    $modified_timestamp = get_post_modified_time('U', false, $post_id, true);

    if (!$modified_timestamp) {
        echo '<span style="color: #666; font-size: 11px;">' . esc_html__('—', 'gufte') . '</span>';
        return;
    }

    $formatted_date = get_post_modified_time('M j, Y', false, $post_id, true);
    $time_diff      = human_time_diff($modified_timestamp, current_time('timestamp'));

    echo '<div class="modified-date-info">';
    echo '<strong style="color:#0073aa;">' . esc_html($formatted_date) . '</strong><br>';
    echo '<span style="font-size:11px;color:#666;">' . esc_html(sprintf(esc_html__('%s ago', 'gufte'), $time_diff)) . '</span>';
    echo '</div>';
}
add_action('manage_posts_custom_column', 'gufte_display_modified_date_admin_column', 10, 2);


/**
 * Release Date sütununu sıralanabilir yap
 */
function gufte_make_release_date_column_sortable($columns) {
    $columns['release_date'] = 'release_date';
    $columns['modified_date'] = 'modified';
    return $columns;
}
add_filter('manage_edit-lyrics_sortable_columns', 'gufte_make_release_date_column_sortable');

/**
 * Release Date sıralaması
 */
function gufte_sort_posts_by_release_date($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }
    
    if ($query->get('orderby') === 'release_date') {
        $query->set('meta_key', '_release_date');
        $query->set('orderby', 'meta_value');
        $query->set('meta_type', 'DATE');
    }
}
add_action('pre_get_posts', 'gufte_sort_posts_by_release_date');

/**
 * Release Date admin CSS
 */
function gufte_add_release_date_admin_styles() {
    $screen = get_current_screen();

    if ($screen && $screen->id === 'edit-lyrics') {
        ?>
        <style type="text/css">
        .column-release_date,
        .column-modified_date {
            width: 120px;
        }

        .release-date-info,
        .modified-date-info {
            font-size: 12px;
            line-height: 1.3;
        }

        .release-date-info strong,
        .modified-date-info strong {
            display: block;
        }

        .release-date-info span,
        .modified-date-info span {
            display: block;
        }

        .apple-music-badge {
            font-weight: bold;
            text-transform: uppercase;
        }

        @media screen and (max-width: 782px) {
            .column-release_date,
            .column-modified_date {
                display: none !important;
            }
        }
        </style>
        <?php
    }
}
add_action('admin_head', 'gufte_add_release_date_admin_styles');

/**
 * Release Date helper functions
 */

// Release date'i formatlanmış olarak al
function gufte_get_formatted_release_date($post_id = null, $format = 'F j, Y') {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $release_date = get_post_meta($post_id, '_release_date', true);
    
    if (empty($release_date)) {
        return '';
    }
    
    return date($format, strtotime($release_date));
}

// Release date'in ne kadar önce olduğunu al
function gufte_get_release_date_time_ago($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $release_date = get_post_meta($post_id, '_release_date', true);
    
    if (empty($release_date)) {
        return '';
    }
    
    return human_time_diff(strtotime($release_date), current_time('timestamp')) . ' ago';
}

// Release date'in yılını al
function gufte_get_release_year($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $release_date = get_post_meta($post_id, '_release_date', true);
    
    if (empty($release_date)) {
        return '';
    }
    
    return date('Y', strtotime($release_date));
}

// Music genre'yi al
function gufte_get_music_genre($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    return get_post_meta($post_id, '_music_genre', true);
}

// Apple Music verilerini toplu olarak al
function gufte_get_apple_music_data($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    return array(
        'release_date' => get_post_meta($post_id, '_release_date', true),
        'genre' => get_post_meta($post_id, '_music_genre', true),
        'apple_music_id' => get_post_meta($post_id, '_apple_music_id', true),
        'track_name' => get_post_meta($post_id, '_apple_music_track_name', true),
        'artist_name' => get_post_meta($post_id, '_apple_music_artist_name', true),
        'album_name' => get_post_meta($post_id, '_apple_music_album_name', true)
    );
}
/**
 * Functions.php dosyanıza eklenecek kod
 * 
 * Önceki sitemap kodlarını kaldırıp, bunun yerine sadece bu kısa kodu ekleyin
 */

/**
 * Gufte Sitemap Generator'ı yükle
 */
function gufte_load_sitemap_generator() {
    $sitemap_file = get_template_directory() . '/inc/utilities/sitemap-generator.php';
    
    if (file_exists($sitemap_file)) {
        require_once $sitemap_file;
    }
}
add_action('after_setup_theme', 'gufte_load_sitemap_generator');

/**
 * Sitemap URL'ler için permalink flush (tema aktifleştirildiğinde)
 */
function gufte_sitemap_flush_rewrite_rules($old_name = null) {
    // Sitemap Generator'ı yükle
    gufte_load_sitemap_generator();
    
    // Permalink'leri yenile
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'gufte_sitemap_flush_rewrite_rules');

/**
 * Tema deaktifleştirildiğinde permalink'leri temizle
 */
function gufte_sitemap_deactivation($new_name = null, $new_theme = null) {
    flush_rewrite_rules();
}
add_action('switch_theme', 'gufte_sitemap_deactivation');

/**
 * Flush sitemap rewrite kuralları (sürüm güncellendiğinde bir kere çalışır)
 */
function gufte_maybe_flush_sitemap_rules() {
    $marker = '1.7.4';
    if (get_option('gufte_sitemap_rules_version') !== $marker) {
        gufte_load_sitemap_generator();
        flush_rewrite_rules();
        update_option('gufte_sitemap_rules_version', $marker);
    }
}
add_action('init', 'gufte_maybe_flush_sitemap_rules', 11);
/**
 * Gufte Dashboard İstatistikleri Widget'ı
 * Bu kodu functions.php dosyanızın sonuna ekleyin
 */

/**
 * Dashboard widget'ını kaydet
 */
function gufte_add_dashboard_widgets() {
    wp_add_dashboard_widget(
        'gufte_stats_widget',           // Widget ID
        '🎵 Gufte Theme Statistics', // Widget title
        'gufte_dashboard_widget_content', // Content function
        'gufte_dashboard_widget_control'  // Settings function (optional)
    );
}
add_action('wp_dashboard_setup', 'gufte_add_dashboard_widgets');

/**
 * Dashboard widget içeriği
 */
function gufte_dashboard_widget_content() {
    // İstatistikleri topla - hata yakalama ile
    try {
        $stats = gufte_get_dashboard_stats();

        // Eğer stats boşsa varsayılan değerler kullan
        if (empty($stats)) {
            $stats = array(
                'posts_count' => 0,
                'pages_count' => 0,
                'categories_count' => 0,
                'singers_count' => 0,
                'albums_count' => 0,
                'posts_with_translations' => 0,
                'translation_percentage' => 0,
                'total_languages' => 0,
                'total_translations' => 0,
                'videos_count' => 0,
                'spotify_count' => 0,
                'youtube_count' => 0,
                'apple_music_count' => 0,
                'recent_posts' => array(),
                'language_breakdown' => array()
            );
        }
    } catch (Exception $e) {
        echo '<div class="notice notice-error"><p>Dashboard istatistikleri yüklenirken hata oluştu: ' . esc_html($e->getMessage()) . '</p></div>';
        return;
    }
    ?>
    <div class="gufte-dashboard-stats">
        <!-- Genel İstatistikler -->
        <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 15px; margin-bottom: 20px;">
            
            <!-- Posts -->
            <div class="stat-card posts" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; border-radius: 8px; text-align: center;">
                <div class="stat-icon" style="font-size: 24px; margin-bottom: 8px;">📝</div>
                <div class="stat-number" style="font-size: 24px; font-weight: bold; margin-bottom: 4px;"><?php echo number_format($stats['posts_count']); ?></div>
                <div class="stat-label" style="font-size: 12px; opacity: 0.9;">Lyrics</div>
            </div>

            <!-- Singers -->
            <div class="stat-card singers" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 15px; border-radius: 8px; text-align: center;">
                <div class="stat-icon" style="font-size: 24px; margin-bottom: 8px;">🎤</div>
                <div class="stat-number" style="font-size: 24px; font-weight: bold; margin-bottom: 4px;"><?php echo number_format($stats['singers_count']); ?></div>
                <div class="stat-label" style="font-size: 12px; opacity: 0.9;">Singers</div>
            </div>

            <!-- Albums -->
            <div class="stat-card albums" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 15px; border-radius: 8px; text-align: center;">
                <div class="stat-icon" style="font-size: 24px; margin-bottom: 8px;">💿</div>
                <div class="stat-number" style="font-size: 24px; font-weight: bold; margin-bottom: 4px;"><?php echo number_format($stats['albums_count']); ?></div>
                <div class="stat-label" style="font-size: 12px; opacity: 0.9;">Albums</div>
            </div>

            <!-- Categories -->
            <div class="stat-card categories" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 15px; border-radius: 8px; text-align: center;">
                <div class="stat-icon" style="font-size: 24px; margin-bottom: 8px;">📂</div>
                <div class="stat-number" style="font-size: 24px; font-weight: bold; margin-bottom: 4px;"><?php echo number_format($stats['categories_count']); ?></div>
                <div class="stat-label" style="font-size: 12px; opacity: 0.9;">Categories</div>
            </div>
            
        </div>
        
        <!-- Translation Statistics -->
        <div class="translation-stats" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <h4 style="margin: 0 0 12px 0; color: #495057; display: flex; align-items: center;">
                <span style="margin-right: 8px;">🌍</span>
                Translation Statistics
            </h4>

            <!-- General Statistics -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px; margin-bottom: 15px;">
                <div style="text-align: center;">
                    <div style="font-size: 20px; font-weight: bold; color: #28a745;"><?php echo number_format($stats['posts_count']); ?></div>
                    <div style="font-size: 12px; color: #6c757d;">Total Songs</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 20px; font-weight: bold; color: #17a2b8;"><?php echo $stats['translation_percentage']; ?>%</div>
                    <div style="font-size: 12px; color: #6c757d;">Translation Rate</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 20px; font-weight: bold; color: #ffc107;"><?php echo $stats['total_languages']; ?></div>
                    <div style="font-size: 12px; color: #6c757d;">Total Languages</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 20px; font-weight: bold; color: #6f42c1;"><?php echo number_format($stats['total_translations']); ?></div>
                    <div style="font-size: 12px; color: #6c757d;">Total Translations</div>
                </div>
            </div>
            
            <!-- Language Breakdown -->
            <?php if (!empty($stats['language_breakdown'])): ?>
            <div style="border-top: 1px solid #dee2e6; padding-top: 12px;">
                <h5 style="margin: 0 0 8px 0; font-size: 13px; color: #6c757d; display: flex; align-items: center;">
                    <span style="margin-right: 6px;">📊</span>
                    Translation Distribution by Language
                </h5>
                <div class="language-breakdown" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 8px;">
                    <?php foreach ($stats['language_breakdown'] as $lang_code => $data): ?>
                    <div class="lang-item" style="background: white; border: 1px solid #e9ecef; border-radius: 6px; padding: 8px; text-align: center; position: relative; overflow: hidden;">
                        <!-- Progress bar background -->
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 3px; background: #e9ecef;">
                            <div style="height: 100%; background: <?php echo $data['color']; ?>; width: <?php echo $data['percentage']; ?>%; transition: all 0.3s ease;"></div>
                        </div>
                        
                        <div class="lang-flag" style="font-size: 16px; margin-bottom: 4px;"><?php echo $data['flag']; ?></div>
                        <div class="lang-name" style="font-size: 11px; font-weight: bold; color: #495057; margin-bottom: 2px;"><?php echo esc_html($data['name']); ?></div>
                        <div class="lang-count" style="font-size: 14px; font-weight: bold; color: <?php echo $data['color']; ?>;"><?php echo number_format($data['count']); ?></div>
                        <div class="lang-percent" style="font-size: 10px; color: #6c757d;"><?php echo $data['percentage']; ?>%</div>
                    </div>
                    <?php endforeach; ?>
                </div>
        </div>
        <?php endif; ?>
    </div>

        <!-- Platform Statistics -->
        <div class="platform-stats" style="background: #e9ecef; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <h4 style="margin: 0 0 12px 0; color: #495057; display: flex; align-items: center;">
                <span style="margin-right: 8px;">🎬</span>
                Platform Statistics
            </h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 8px;">
                <div style="text-align: center; padding: 8px; background: white; border-radius: 4px;">
                    <div style="font-size: 16px; font-weight: bold; color: #dc3545;"><?php echo number_format($stats['videos_count']); ?></div>
                    <div style="font-size: 11px; color: #6c757d;">Video</div>
                </div>
                <div style="text-align: center; padding: 8px; background: white; border-radius: 4px;">
                    <div style="font-size: 16px; font-weight: bold; color: #1db954;"><?php echo number_format($stats['spotify_count']); ?></div>
                    <div style="font-size: 11px; color: #6c757d;">Spotify</div>
                </div>
                <div style="text-align: center; padding: 8px; background: white; border-radius: 4px;">
                    <div style="font-size: 16px; font-weight: bold; color: #ff0000;"><?php echo number_format($stats['youtube_count']); ?></div>
                    <div style="font-size: 11px; color: #6c757d;">YouTube</div>
                </div>
                <div style="text-align: center; padding: 8px; background: white; border-radius: 4px;">
                    <div style="font-size: 16px; font-weight: bold; color: #fa243c;"><?php echo number_format($stats['apple_music_count']); ?></div>
                    <div style="font-size: 11px; color: #6c757d;">Apple Music</div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="recent-activity" style="background: white; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
            <h4 style="margin: 0 0 12px 0; color: #495057; display: flex; align-items: center;">
                <span style="margin-right: 8px;">⚡</span>
                Recent Activity
            </h4>
            <div class="activity-list">
                <?php foreach ($stats['recent_posts'] as $post) : ?>
                <div style="display: flex; justify-content: between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f8f9fa; last-child:border-bottom: none;">
                    <div style="flex: 1;">
                        <strong style="font-size: 13px;"><?php echo esc_html(wp_trim_words($post->post_title, 6)); ?></strong>
                        <div style="font-size: 11px; color: #6c757d; margin-top: 2px;">
                            <?php echo human_time_diff(strtotime($post->post_modified), current_time('timestamp')); ?> ago
                        </div>
                    </div>
                    <div style="font-size: 11px; color: #6c757d;">
                        <?php echo ucfirst($post->post_status); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Hızlı Erişim -->
        <div class="quick-access" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px;">
            <a href="<?php echo admin_url('post-new.php'); ?>" class="quick-link" style="display: block; background: #007cba; color: white; padding: 12px; border-radius: 6px; text-decoration: none; text-align: center; font-size: 13px; transition: all 0.3s ease;">
                <div style="font-size: 18px; margin-bottom: 4px;">➕</div>
                Yeni Şarkı Ekle
            </a>
            <a href="<?php echo admin_url('edit-tags.php?taxonomy=singer'); ?>" class="quick-link" style="display: block; background: #d63638; color: white; padding: 12px; border-radius: 6px; text-decoration: none; text-align: center; font-size: 13px; transition: all 0.3s ease;">
                <div style="font-size: 18px; margin-bottom: 4px;">🎤</div>
                Şarkıcılar
            </a>
            <a href="<?php echo admin_url('edit-tags.php?taxonomy=album'); ?>" class="quick-link" style="display: block; background: #00a32a; color: white; padding: 12px; border-radius: 6px; text-decoration: none; text-align: center; font-size: 13px; transition: all 0.3s ease;">
                <div style="font-size: 18px; margin-bottom: 4px;">💿</div>
                Albümler
            </a>
            <a href="<?php echo admin_url('tools.php?page=gufte-sitemap-generator'); ?>" class="quick-link" style="display: block; background: #8224e3; color: white; padding: 12px; border-radius: 6px; text-decoration: none; text-align: center; font-size: 13px; transition: all 0.3s ease;">
                <div style="font-size: 18px; margin-bottom: 4px;">🗺️</div>
                Sitemap
            </a>
        </div>
        
        <!-- Footer -->
        <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #dee2e6; text-align: center;">
            <p style="margin: 0; font-size: 12px; color: #6c757d;">
                <span style="margin-right: 10px;">📊 Veriler <?php echo date('H:i'); ?> itibarıyla güncel</span>
                <a href="<?php echo admin_url('tools.php?page=gufte-sitemap-generator'); ?>" style="color: #007cba; text-decoration: none;">Detaylı İstatistikler →</a>
            </p>
        </div>
    </div>
    
    <style>
    .gufte-dashboard-stats .quick-link:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }
    
    .gufte-dashboard-stats .stat-card {
        transition: all 0.3s ease;
    }
    
    .gufte-dashboard-stats .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    
    @media (max-width: 768px) {
        .gufte-dashboard-stats .stats-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
        
        .gufte-dashboard-stats .quick-access {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }
    </style>
    
    <?php
}

/**
 * Dashboard widget ayarları (isteğe bağlı)
 */
function gufte_dashboard_widget_control() {
    if (isset($_POST['submit'])) {
        // Widget ayarlarını kaydet
        update_option('gufte_dashboard_widget_options', array(
            'show_translations' => isset($_POST['show_translations']),
            'show_platforms' => isset($_POST['show_platforms']),
            'show_recent' => isset($_POST['show_recent'])
        ));
    }
    
    $options = get_option('gufte_dashboard_widget_options', array(
        'show_translations' => true,
        'show_platforms' => true,
        'show_recent' => true
    ));
    ?>
    <p>
        <label for="show_translations">
            <input type="checkbox" name="show_translations" id="show_translations" value="1" <?php checked($options['show_translations']); ?> />
            Çeviri istatistiklerini göster
        </label>
    </p>
    <p>
        <label for="show_platforms">
            <input type="checkbox" name="show_platforms" id="show_platforms" value="1" <?php checked($options['show_platforms']); ?> />
            Platform istatistiklerini göster
        </label>
    </p>
    <p>
        <label for="show_recent">
            <input type="checkbox" name="show_recent" id="show_recent" value="1" <?php checked($options['show_recent']); ?> />
            Son aktiviteleri göster
        </label>
    </p>
    <?php
}

/**
 * Dashboard istatistiklerini topla
 */
function gufte_get_dashboard_stats() {
    // Temel sayılar - hem 'post' hem 'lyrics' post type'larını say
    $posts_count_obj = wp_count_posts('post');
    $posts_count = isset($posts_count_obj->publish) ? $posts_count_obj->publish : 0;

    $lyrics_count_obj = wp_count_posts('lyrics');
    $lyrics_count = isset($lyrics_count_obj->publish) ? $lyrics_count_obj->publish : 0;

    $total_posts_count = $posts_count + $lyrics_count;

    $pages_count_obj = wp_count_posts('page');
    $pages_count = isset($pages_count_obj->publish) ? $pages_count_obj->publish : 0;

    $categories_count = wp_count_terms(array('taxonomy' => 'category', 'hide_empty' => false));
    $categories_count = is_wp_error($categories_count) ? 0 : $categories_count;

    $singers_count = wp_count_terms(array('taxonomy' => 'singer', 'hide_empty' => false));
    $singers_count = is_wp_error($singers_count) ? 0 : $singers_count;

    $albums_count = wp_count_terms(array('taxonomy' => 'album', 'hide_empty' => false));
    $albums_count = is_wp_error($albums_count) ? 0 : $albums_count;

    // Çeviri istatistikleri - hem 'post' hem 'lyrics' post type'larını kontrol et
    $posts_with_translations = get_posts(array(
        'post_type' => array('post', 'lyrics'),
        'post_status' => 'publish',
        'numberposts' => -1,
        'meta_query' => array(
            array(
                'key' => '_available_languages',
                'compare' => 'EXISTS'
            )
        ),
        'fields' => 'ids'
    ));
    
    $posts_with_translations_count = 0;
    $total_translations = 0;
    $all_languages = array();

    foreach ($posts_with_translations as $post_id) {
        $languages = get_post_meta($post_id, '_available_languages', true);

        if (!is_array($languages)) {
            continue;
        }

        $languages = array_map('sanitize_text_field', $languages);
        $languages = array_filter($languages, function ($lang) {
            return '' !== trim((string) $lang);
        });

        if (!empty($languages)) {
            $posts_with_translations_count++;
            $total_translations += count($languages);
            $all_languages = array_merge($all_languages, $languages);
        }
    }

    $translation_percentage = $total_posts_count > 0
        ? round(($posts_with_translations_count / $total_posts_count) * 100, 1)
        : 0;

    $all_languages_sanitized = array_map('sanitize_title', $all_languages);
    $total_languages = count(array_unique($all_languages_sanitized));

    // Platform istatistikleri - hem 'post' hem 'lyrics' post type'larını kontrol et
    $videos_count = count(get_posts(array(
        'post_type' => array('post', 'lyrics'),
        'post_status' => 'publish',
        'numberposts' => -1,
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key' => 'music_video_url',
                'compare' => 'EXISTS'
            ),
            array(
                'key' => 'music_video_embed',
                'compare' => 'EXISTS'
            )
        ),
        'fields' => 'ids'
    )));

    $spotify_count = count(get_posts(array(
        'post_type' => array('post', 'lyrics'),
        'post_status' => 'publish',
        'numberposts' => -1,
        'meta_query' => array(
            array(
                'key' => 'spotify_url',
                'compare' => 'EXISTS'
            )
        ),
        'fields' => 'ids'
    )));

    $youtube_count = count(get_posts(array(
        'post_type' => array('post', 'lyrics'),
        'post_status' => 'publish',
        'numberposts' => -1,
        'meta_query' => array(
            array(
                'key' => 'youtube_url',
                'compare' => 'EXISTS'
            )
        ),
        'fields' => 'ids'
    )));

    $apple_music_count = count(get_posts(array(
        'post_type' => array('post', 'lyrics'),
        'post_status' => 'publish',
        'numberposts' => -1,
        'meta_query' => array(
            array(
                'key' => 'apple_music_url',
                'compare' => 'EXISTS'
            )
        ),
        'fields' => 'ids'
    )));
    
    // Son yazılar - hem 'post' hem 'lyrics' post type'larını göster
    $recent_posts = get_posts(array(
        'post_type' => array('post', 'lyrics'),
        'numberposts' => 5,
        'orderby' => 'modified',
        'order' => 'DESC'
    ));
    
    $stats = array(
        'posts_count' => $total_posts_count, // Toplam şarkı sözleri (post + lyrics)
        'pages_count' => $pages_count,
        'categories_count' => $categories_count,
        'singers_count' => $singers_count,
        'albums_count' => $albums_count,
        'posts_with_translations' => $posts_with_translations_count,
        'translation_percentage' => $translation_percentage,
        'total_languages' => $total_languages,
        'total_translations' => $total_translations,
        'videos_count' => $videos_count,
        'spotify_count' => $spotify_count,
        'youtube_count' => $youtube_count,
        'apple_music_count' => $apple_music_count,
        'recent_posts' => $recent_posts,
    );

    $stats['language_breakdown'] = gufte_build_language_breakdown($all_languages);

    return $stats;
}

function gufte_build_language_breakdown($languages) {
    $breakdown = array();

    if (empty($languages) || !is_array($languages)) {
        return $breakdown;
    }

    // Get language settings safely
    $language_map = array();
    if (function_exists('gufte_get_language_settings')) {
        $settings = gufte_get_language_settings();
        $language_map = isset($settings['language_map']) ? $settings['language_map'] : array();
    }

    $counts = array_count_values(array_map('sanitize_title', $languages));
    unset($counts['english'], $counts['']);

    $total = array_sum($counts);
    if ($total <= 0) {
        return $breakdown;
    }

    foreach ($counts as $lang_key => $count) {
        $flag = '';
        $color = '#00a0d2';
        $native = isset($language_map[$lang_key]) ? $language_map[$lang_key] : ucfirst($lang_key);

        if (function_exists('gufte_get_language_info')) {
            $info = gufte_get_language_info($lang_key);
            if (!empty($info['flag'])) {
                $flag = $info['flag'];
            }
            if (!empty($info['color'])) {
                $color = $info['color'];
            }
            if (!empty($info['native_name'])) {
                $native = $info['native_name'];
            }
        }

        $breakdown[$lang_key] = array(
            'count' => $count,
            'percentage' => round(($count / $total) * 100, 1),
            'flag' => $flag,
            'color' => $color,
            'name' => $native,
        );
    }

    uasort($breakdown, function ($a, $b) {
        return $b['count'] <=> $a['count'];
    });

    return $breakdown;
}

/**
 * Dashboard widget'ının konumunu ayarla (isteğe bağlı)
 */
function gufte_dashboard_widget_order() {
    global $wp_meta_boxes;
    
    // Widget'ı en üste taşı
    $gufte_widget = $wp_meta_boxes['dashboard']['normal']['core']['gufte_stats_widget'];
    unset($wp_meta_boxes['dashboard']['normal']['core']['gufte_stats_widget']);
    $wp_meta_boxes['dashboard']['normal']['high']['gufte_stats_widget'] = $gufte_widget;
}
add_action('wp_dashboard_setup', 'gufte_dashboard_widget_order', 20);

/**
 * Dashboard'da diğer gereksiz widget'ları gizle (isteğe bağlı)
 */
function gufte_remove_unnecessary_dashboard_widgets() {
    // WordPress haberleri ve etkinlikleri gizle (isteğe bağlı)
    // remove_meta_box('dashboard_primary', 'dashboard', 'side');
    // remove_meta_box('dashboard_secondary', 'dashboard', 'side');
    
    // WordPress hoş geldiniz panelini gizle (isteğe bağlı)
    // remove_action('welcome_panel', 'wp_welcome_panel');
}
add_action('wp_dashboard_setup', 'gufte_remove_unnecessary_dashboard_widgets');

/**
 * AJAX ile istatistikleri yenile (isteğe bağlı gelişmiş özellik)
 */
function gufte_refresh_dashboard_stats_ajax() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    $stats = gufte_get_dashboard_stats();
    wp_send_json_success($stats);
}
add_action('wp_ajax_gufte_refresh_stats', 'gufte_refresh_dashboard_stats_ajax');

/**
 * Dashboard widget'ına yenileme butonu ekle (isteğe bağlı)
 */
function gufte_add_refresh_button_to_widget() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Widget başlığına yenileme butonu ekle
        $('#gufte_stats_widget .hndle').append(
            '<button type="button" class="button-link" id="refresh-gufte-stats" style="float: right; margin-top: -2px;" title="İstatistikleri Yenile">' +
            '<span class="dashicons dashicons-update" style="font-size: 16px;"></span>' +
            '</button>'
        );
        
        // Yenileme butonuna tıklama event'i
        $('#refresh-gufte-stats').on('click', function() {
            var button = $(this);
            var icon = button.find('.dashicons');
            
            // Loading animasyonu
            icon.addClass('spin');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'gufte_refresh_stats'
                },
                success: function(response) {
                    if (response.success) {
                        // Sayfayı yenile (basit yöntem)
                        location.reload();
                    }
                },
                complete: function() {
                    icon.removeClass('spin');
                }
            });
        });
    });
    </script>
    
    <style>
    .dashicons.spin {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    </style>
    <?php
}
add_action('admin_footer-index.php', 'gufte_add_refresh_button_to_widget');


/**
 * Sosyal medya paylaşım bağlantıları oluştur
 */
function gufte_get_social_share_links($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $url = get_permalink($post_id);
    $title = get_the_title($post_id);
    $excerpt = wp_strip_all_tags(get_the_excerpt($post_id));
    
    // Şarkıcı bilgisini al
    $singers = get_the_terms($post_id, 'singer');
    $singer_name = '';
    if ($singers && !is_wp_error($singers)) {
        $singer_name = $singers[0]->name;
    }
    
    // Paylaşım metni oluştur
    $share_text = $title;
    if (!empty($singer_name)) {
        $share_text = $title . ' by ' . $singer_name;
    }
    
    $share_links = array(
        'facebook' => array(
            'url' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($url),
            'icon' => 'mdi:facebook',
            'color' => '#1877f2',
            'label' => 'Facebook'
        ),
        'twitter' => array(
            'url' => 'https://twitter.com/intent/tweet?text=' . urlencode($share_text) . '&url=' . urlencode($url),
            'icon' => 'mdi:twitter',
            'color' => '#1da1f2',
            'label' => 'Twitter'
        ),
        'whatsapp' => array(
            'url' => 'https://wa.me/?text=' . urlencode($share_text . ' ' . $url),
            'icon' => 'mdi:whatsapp',
            'color' => '#25d366',
            'label' => 'WhatsApp'
        ),
        'telegram' => array(
            'url' => 'https://t.me/share/url?url=' . urlencode($url) . '&text=' . urlencode($share_text),
            'icon' => 'mdi:telegram',
            'color' => '#0088cc',
            'label' => 'Telegram'
        ),
        'pinterest' => array(
            'url' => 'https://pinterest.com/pin/create/button/?url=' . urlencode($url) . '&description=' . urlencode($share_text),
            'icon' => 'mdi:pinterest',
            'color' => '#bd081c',
            'label' => 'Pinterest'
        ),
        'linkedin' => array(
            'url' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode($url),
            'icon' => 'mdi:linkedin',
            'color' => '#0077b5',
            'label' => 'LinkedIn'
        ),
        'reddit' => array(
            'url' => 'https://reddit.com/submit?url=' . urlencode($url) . '&title=' . urlencode($share_text),
            'icon' => 'mdi:reddit',
            'color' => '#ff4500',
            'label' => 'Reddit'
        ),
        'email' => array(
            'url' => 'mailto:?subject=' . rawurlencode($share_text) . '&body=' . rawurlencode($share_text . "\n\n" . $url),
            'icon' => 'mdi:email',
            'color' => '#666666',
            'label' => 'Email'
        ),
        'copy' => array(
            'url' => '#',
            'icon' => 'mdi:content-copy',
            'color' => '#333333',
            'label' => 'Copy Link',
            'data-url' => $url
        )
    );
    
    return $share_links;
}
/**
 * Dil bilgilerini getiren yardımcı fonksiyon
 * Bu kodu functions.php dosyanızın sonuna ekleyin
 */
function gufte_get_language_info($language) {
    $language_map = array(
        'english' => array(
            'flag' => '🇺🇸',
            'native_name' => 'English',
            'english_name' => 'English',
            'code' => 'en',
            'color' => '#1f2937'
        ),
        'spanish' => array(
            'flag' => '🇪🇸',
            'native_name' => 'Español',
            'english_name' => 'Spanish',
            'code' => 'es',
            'color' => '#dc2626'
        ),
        'turkish' => array(
            'flag' => '🇹🇷',
            'native_name' => 'Türkçe',
            'english_name' => 'Turkish',
            'code' => 'tr',
            'color' => '#dc2626'
        ),
        'german' => array(
            'flag' => '🇩🇪',
            'native_name' => 'Deutsch',
            'english_name' => 'German',
            'code' => 'de',
            'color' => '#1f2937'
        ),
        'arabic' => array(
            'flag' => '🇸🇦',
            'native_name' => 'العربية',
            'english_name' => 'Arabic',
            'code' => 'ar',
            'color' => '#059669'
        ),
        'french' => array(
            'flag' => '🇫🇷',
            'native_name' => 'Français',
            'english_name' => 'French',
            'code' => 'fr',
            'color' => '#1e40af'
        ),
        'italian' => array(
            'flag' => '🇮🇹',
            'native_name' => 'Italiano',
            'english_name' => 'Italian',
            'code' => 'it',
            'color' => '#059669'
        ),
        'portuguese' => array(
            'flag' => '🇧🇷',
            'native_name' => 'Português',
            'english_name' => 'Portuguese',
            'code' => 'pt',
            'color' => '#059669'
        ),
        'russian' => array(
            'flag' => '🇷🇺',
            'native_name' => 'Русский',
            'english_name' => 'Russian',
            'code' => 'ru',
            'color' => '#dc2626'
        ),
        'japanese' => array(
            'flag' => '🇯🇵',
            'native_name' => '日本語',
            'english_name' => 'Japanese',
            'code' => 'ja',
            'color' => '#dc2626'
        ),
        'korean' => array(
            'flag' => '🇰🇷',
            'native_name' => '한국어',
            'english_name' => 'Korean',
            'code' => 'ko',
            'color' => '#1f2937'
        ),
        'persian' => array(
            'flag' => '🇮🇷',
            'native_name' => 'فارسی',
            'english_name' => 'Persian',
            'code' => 'fa',
            'color' => '#059669'
        ),
        'chinese' => array(
            'flag' => '🇨🇳',
            'native_name' => '中文',
            'english_name' => 'Chinese',
            'code' => 'zh',
            'color' => '#dc2626'
        ),
        'hindi' => array(
            'flag' => '🇮🇳',
            'native_name' => 'हिन्दी',
            'english_name' => 'Hindi',
            'code' => 'hi',
            'color' => '#ea580c'
        )
    );

    $language_key = strtolower(str_replace(' ', '', $language));

    if (isset($language_map[$language_key])) {
        return $language_map[$language_key];
    }

    // Varsayılan değer
    return array(
        'flag' => '🌐',
        'native_name' => ucfirst($language),
        'english_name' => ucfirst($language),
        'code' => strtolower(substr($language, 0, 2)),
        'color' => '#6b7280'
    );
}

/**
 * Hero alanı için özel JavaScript ve CSS stilleri
 */
function gufte_add_hero_translation_assets() {
    if (is_home() || is_front_page()) {
        ?>
        <style>
        /* Çeviri dropdown stilleri */
        .translations-dropdown {
            position: relative;
        }

        .translations-dropdown:hover .translations-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .translations-dropdown:hover .dropdown-arrow {
            transform: rotate(180deg);
        }

        .translations-menu {
            max-height: 300px;
            overflow-y: auto;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            backdrop-filter: blur(8px);
        }

        .translations-menu::-webkit-scrollbar {
            width: 4px;
        }

        .translations-menu::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .translations-menu::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 2px;
        }

        .translations-menu::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Mobil responsiveness */
        @media (max-width: 768px) {
            .translations-menu {
                position: fixed;
                top: 50% !important;
                left: 50% !important;
                transform: translate(-50%, -50%) !important;
                width: 90vw;
                max-width: 280px;
            }
            
            .translations-menu::before {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.3);
                z-index: -1;
            }
        }

        /* Hover animasyonları */
        .language-badge {
            cursor: pointer;
            user-select: none;
        }

        .language-badge:hover {
            transform: translateY(-1px) scale(1.02);
        }

        .language-badge:active {
            transform: translateY(0) scale(0.98);
        }

        /* Focus durumları (accessibility) */
        .translations-trigger:focus {
            outline: none;
            ring: 2px;
            ring-color: rgba(59, 130, 246, 0.5);
            ring-offset: 2px;
        }

        .translations-menu a:focus {
            background-color: rgba(59, 130, 246, 0.1);
            outline: none;
        }
        </style>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Çeviri dropdown'larını yönet
            const translationDropdowns = document.querySelectorAll('.translations-dropdown');
            
            translationDropdowns.forEach(dropdown => {
                const trigger = dropdown.querySelector('.translations-trigger');
                const menu = dropdown.querySelector('.translations-menu');
                let timeoutId;

                // Mouse enter - menüyü aç
                dropdown.addEventListener('mouseenter', () => {
                    clearTimeout(timeoutId);
                    menu.style.opacity = '1';
                    menu.style.visibility = 'visible';
                    menu.style.transform = 'translateY(0)';
                    trigger.querySelector('.dropdown-arrow').style.transform = 'rotate(180deg)';
                });

                // Mouse leave - menüyü kapat (gecikme ile)
                dropdown.addEventListener('mouseleave', () => {
                    timeoutId = setTimeout(() => {
                        menu.style.opacity = '0';
                        menu.style.visibility = 'hidden';
                        menu.style.transform = 'translateY(-10px)';
                        trigger.querySelector('.dropdown-arrow').style.transform = 'rotate(0deg)';
                    }, 150);
                });

                // Klavye erişimi
                trigger.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        const isOpen = menu.style.opacity === '1';
                        
                        if (isOpen) {
                            menu.style.opacity = '0';
                            menu.style.visibility = 'hidden';
                            menu.style.transform = 'translateY(-10px)';
                        } else {
                            menu.style.opacity = '1';
                            menu.style.visibility = 'visible';
                            menu.style.transform = 'translateY(0)';
                            // İlk menü öğesine focus ver
                            const firstLink = menu.querySelector('a');
                            if (firstLink) {
                                firstLink.focus();
                            }
                        }
                    } else if (e.key === 'Escape') {
                        menu.style.opacity = '0';
                        menu.style.visibility = 'hidden';
                        menu.style.transform = 'translateY(-10px)';
                        trigger.focus();
                    }
                });

                // Menü linklerinde klavye navigasyonu
                const menuLinks = menu.querySelectorAll('a');
                menuLinks.forEach((link, index) => {
                    link.addEventListener('keydown', (e) => {
                        if (e.key === 'ArrowDown' && index < menuLinks.length - 1) {
                            e.preventDefault();
                            menuLinks[index + 1].focus();
                        } else if (e.key === 'ArrowUp' && index > 0) {
                            e.preventDefault();
                            menuLinks[index - 1].focus();
                        } else if (e.key === 'Escape') {
                            e.preventDefault();
                            menu.style.opacity = '0';
                            menu.style.visibility = 'hidden';
                            menu.style.transform = 'translateY(-10px)';
                            trigger.focus();
                        }
                    });
                });

                // Mobil dokunma işlemleri
                if (window.innerWidth <= 768) {
                    trigger.addEventListener('touchstart', (e) => {
                        e.preventDefault();
                        const isOpen = menu.style.opacity === '1';
                        
                        // Tüm diğer açık menüleri kapat
                        document.querySelectorAll('.translations-menu').forEach(otherMenu => {
                            if (otherMenu !== menu) {
                                otherMenu.style.opacity = '0';
                                otherMenu.style.visibility = 'hidden';
                                otherMenu.style.transform = 'translate(-50%, -50%) translateY(-10px)';
                            }
                        });
                        
                        if (isOpen) {
                            menu.style.opacity = '0';
                            menu.style.visibility = 'hidden';
                            menu.style.transform = 'translate(-50%, -50%) translateY(-10px)';
                        } else {
                            menu.style.opacity = '1';
                            menu.style.visibility = 'visible';
                            menu.style.transform = 'translate(-50%, -50%) translateY(0)';
                        }
                    });

                    // Mobilde dış tıklamada menüyü kapat
                    document.addEventListener('touchstart', (e) => {
                        if (!dropdown.contains(e.target)) {
                            menu.style.opacity = '0';
                            menu.style.visibility = 'hidden';
                            menu.style.transform = 'translate(-50%, -50%) translateY(-10px)';
                        }
                    });
                }
            });

            // Analytics tracking (opsiyonel)
            document.querySelectorAll('.translations-menu a').forEach(link => {
                link.addEventListener('click', (e) => {
                    const language = link.querySelector('span:last-child').textContent;
                    
                    // Google Analytics event tracking (eğer GA yüklüyse)
                    if (typeof gtag !== 'undefined') {
                        gtag('event', 'translation_click', {
                            'event_category': 'engagement',
                            'event_label': language,
                            'value': 1
                        });
                    }
                    
                    // Console'da da göster (debug için)
                    console.log('Translation clicked:', language);
                });
            });
        });
        </script>
        <?php
    }
}
add_action('wp_head', 'gufte_add_hero_translation_assets', 20);
/**
 * Dil istatistiklerini getiren fonksiyon
 * Bu kodu functions.php dosyanızın sonuna ekleyin
 */
function gufte_get_language_statistics() {
    // Cache kontrol et (1 saat cache)
    $cache_key = 'gufte_language_stats';
    $cached = wp_cache_get($cache_key);
    
    if ($cached !== false) {
        return $cached;
    }
    
    // Çevirisi olan tüm yazıları al
    $posts_with_translations = get_posts(array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'numberposts' => -1,
        'meta_query' => array(
            array(
                'key' => '_available_languages',
                'compare' => 'EXISTS'
            )
        ),
        'fields' => 'ids'
    ));
    
    if (empty($posts_with_translations)) {
        return array();
    }
    
    $language_counts = array();
    $total_posts = count($posts_with_translations);
    
    // Her yazı için dilleri say
    foreach ($posts_with_translations as $post_id) {
        $languages = get_post_meta($post_id, '_available_languages', true);
        
        if (is_array($languages)) {
            foreach ($languages as $language) {
                $language_key = sanitize_title($language);
                
                if (!isset($language_counts[$language_key])) {
                    $language_counts[$language_key] = array(
                        'original_name' => $language,
                        'count' => 0
                    );
                }
                
                $language_counts[$language_key]['count']++;
            }
        }
    }
    
    // Sonuçları formatla ve sırala
    $formatted_stats = array();
    
    foreach ($language_counts as $lang_key => $data) {
        $language_info = gufte_get_language_info($data['original_name']);
        $percentage = ($data['count'] / $total_posts) * 100;
        
        $formatted_stats[$lang_key] = array(
            'name' => $data['original_name'],
            'native_name' => $language_info['native_name'],
            'flag' => $language_info['flag'],
            'color' => $language_info['color'],
            'count' => $data['count'],
            'percentage' => $percentage
        );
    }
    
    // Şarkı sayısına göre sırala (büyükten küçüğe)
    uasort($formatted_stats, function($a, $b) {
        return $b['count'] - $a['count'];
    });
    
    // Cache'e kaydet
    wp_cache_set($cache_key, $formatted_stats, '', 3600);
    
    return $formatted_stats;
}

/**
 * REST API: Çeviri içeriklerini AJAX ile getirmek için endpoint
 */
function gufte_register_translation_rest_routes() {
    register_rest_route(
        'gufte/v1',
        '/lyrics/(?P<id>\d+)',
        array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'gufte_rest_get_lyrics_translation',
            'permission_callback' => '__return_true',
            'args'                => array(
                'id'   => array(
                    'description' => __( 'Post ID for the song.', 'gufte' ),
                    'type'        => 'integer',
                    'required'    => true,
                ),
                'lang' => array(
                    'description' => __( 'Language slug to load.', 'gufte' ),
                    'type'        => 'string',
                    'required'    => false,
                ),
            ),
        )
    );

    // Language SEO settings endpoint
    register_rest_route(
        'arcuras/v1',
        '/language-seo-settings',
        array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'arcuras_rest_get_language_seo_settings',
            'permission_callback' => '__return_true',
        )
    );

    // FAQ HTML endpoint
    register_rest_route(
        'arcuras/v1',
        '/faq-html',
        array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'arcuras_rest_get_faq_html',
            'permission_callback' => '__return_true',
            'args'                => array(
                'post_id' => array(
                    'description' => __( 'Post ID for the song.', 'arcuras' ),
                    'type'        => 'integer',
                    'required'    => true,
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ),
                'lang' => array(
                    'description' => __( 'Language code (en, es, tr, etc).', 'arcuras' ),
                    'type'        => 'string',
                    'required'    => false,
                    'default'     => 'en',
                    'validate_callback' => function($param) {
                        return in_array($param, array('en', 'es', 'tr', 'fr', 'de', 'it', 'pt', 'ar', 'ja', 'ko'));
                    }
                ),
            ),
        )
    );
}
add_action('rest_api_init', 'gufte_register_translation_rest_routes');

function gufte_rest_get_lyrics_translation(WP_REST_Request $request) {
    $post_id = (int) $request->get_param('id');
    $lang    = $request->get_param('lang');
    $lang    = is_string($lang) ? sanitize_text_field(wp_unslash($lang)) : '';

    $post_obj = get_post($post_id);

    if (!$post_obj || 'post' !== $post_obj->post_type || 'publish' !== $post_obj->post_status) {
        return new WP_Error('gufte_not_found', __('Song not found.', 'gufte'), array('status' => 404));
    }

    $original_lang = isset($_GET['lang']) ? $_GET['lang'] : null;

    if (!empty($lang)) {
        $_GET['lang'] = $lang;
    } else {
        unset($_GET['lang']);
    }

    global $post;
    $previous_post = $post;
    $post          = $post_obj;

    setup_postdata($post);

    $content = apply_filters('the_content', $post->post_content);

    if ($previous_post instanceof WP_Post) {
        $post = $previous_post;
        setup_postdata($post);
    } else {
        wp_reset_postdata();
    }

    if ($original_lang !== null) {
        $_GET['lang'] = $original_lang;
    } else {
        unset($_GET['lang']);
    }

    return array(
        'content' => $content,
        'lang'    => $lang,
        'title'   => get_the_title($post_obj),
    );
}

/**
 * REST API callback: Get language SEO settings from database
 */
function arcuras_rest_get_language_seo_settings(WP_REST_Request $request) {
    $seo_settings = get_option('arcuras_language_seo_settings', array());

    // Get language term data to merge with SEO settings
    if (function_exists('arcuras_get_language_term_data')) {
        $language_data = arcuras_get_language_term_data();

        // Merge language data with SEO settings
        $result = array();
        foreach ($language_data as $lang_key => $lang_info) {
            $iso_code = isset($lang_info['iso_code']) ? $lang_info['iso_code'] : $lang_key;

            // Get SEO settings for this language
            $lang_seo = isset($seo_settings[$iso_code]) ? $seo_settings[$iso_code] : array();

            $result[$iso_code] = array(
                'name' => isset($lang_info['name']) ? $lang_info['name'] : '',
                'original_suffix' => isset($lang_seo['original_suffix']) ? $lang_seo['original_suffix'] : '',
                'meta_suffix' => isset($lang_seo['meta_suffix']) ? $lang_seo['meta_suffix'] : '',
                'translation_suffix' => isset($lang_seo['translation_suffix']) ? $lang_seo['translation_suffix'] : '',
                'translation_meta_suffix' => isset($lang_seo['translation_meta_suffix']) ? $lang_seo['translation_meta_suffix'] : '',
            );
        }

        return rest_ensure_response($result);
    }

    return rest_ensure_response($seo_settings);
}

/**
 * REST API callback: Get FAQ HTML for a specific language
 */
function arcuras_rest_get_faq_html(WP_REST_Request $request) {
    $post_id = (int) $request->get_param('post_id');
    $lang_code = sanitize_text_field($request->get_param('lang'));

    // Validate post
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'lyrics') {
        return new WP_Error('invalid_post', 'Invalid post ID or not a lyrics post', array('status' => 404));
    }

    // Temporarily set REQUEST_URI to simulate the language URL for FAQ generation
    $original_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $_SERVER['REQUEST_URI'] = "/lyrics/" . $post->post_name . "/" . $lang_code . "/";

    // Load FAQ functions if not already loaded
    if (!function_exists('gufte_generate_auto_faq')) {
        $faq_functions_path = get_template_directory() . '/inc/faq-functions.php';
        if (file_exists($faq_functions_path)) {
            require_once $faq_functions_path;
        }
    }

    // Setup post context
    global $post;
    $post = get_post($post_id);
    setup_postdata($post);

    ob_start();

    // Check if function exists and call it
    if (function_exists('gufte_generate_auto_faq')) {
        echo gufte_generate_auto_faq($post_id);
    } else {
        // Fallback: return simple message
        echo '<div class="auto-faq-section">FAQ functions not found</div>';
    }

    $faq_html = ob_get_clean();
    wp_reset_postdata();

    // Restore original REQUEST_URI
    $_SERVER['REQUEST_URI'] = $original_uri;

    return array(
        'html' => $faq_html,
        'lang' => $lang_code,
        'post_id' => $post_id
    );
}

/**
 * Dil bazlı şarkıları getiren fonksiyon
 */
function gufte_get_songs_by_language($language_slug, $limit = -1) {
    // Language slug'ından orijinal dil adını bul
    $language_stats = gufte_get_language_statistics();
    $target_language = '';
    
    foreach ($language_stats as $slug => $data) {
        if ($slug === $language_slug) {
            $target_language = $data['name'];
            break;
        }
    }
    
    if (empty($target_language)) {
        return array();
    }
    
    // Bu dilde çevirisi olan yazıları al
    $args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
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
    
    return get_posts($args);
}

/**
 * URL rewrite rules ekle
 */
function gufte_add_language_rewrite_rules() {
    add_rewrite_rule(
        '^language/([^/]+)/?$',
        'index.php?language_page=1&lang_slug=$matches[1]',
        'top'
    );
    
    add_rewrite_rule(
        '^languages/?$',
        'index.php?languages_page=1',
        'top'
    );
}
add_action('init', 'gufte_add_language_rewrite_rules');

/**
 * Query vars ekle
 */
function gufte_add_language_query_vars($vars) {
    $vars[] = 'language_page';
    $vars[] = 'lang_slug';
    $vars[] = 'languages_page';
    return $vars;
}
add_filter('query_vars', 'gufte_add_language_query_vars');

/**
 * Template redirect
 */
function gufte_language_template_redirect() {
    global $wp_query;
    
    if (get_query_var('language_page')) {
        $lang_slug = get_query_var('lang_slug');
        
        if (!empty($lang_slug)) {
            // Tek dil sayfası
            include(get_template_directory() . '/page-templates/language-single.php');
            exit;
        }
    }

    if (get_query_var('languages_page')) {
        // Tüm diller sayfası
        include(get_template_directory() . '/page-templates/languages.php');
        exit;
    }
}
add_action('template_redirect', 'gufte_language_template_redirect');

/**
 * Cache'i temizle (post kayıt/güncelleme durumunda)
 */
function gufte_clear_language_stats_cache($post_id) {
    if (get_post_type($post_id) === 'post') {
        wp_cache_delete('gufte_language_stats');
    }
}
add_action('save_post', 'gufte_clear_language_stats_cache');
add_action('delete_post', 'gufte_clear_language_stats_cache');

/**
 * Permalink flush (tema aktif olduğunda)
 */
function gufte_flush_rewrite_rules_on_activation() {
    gufte_add_language_rewrite_rules();
    flush_rewrite_rules();
}
add_action('after_setup_theme', 'gufte_flush_rewrite_rules_on_activation');
/**
 * YouTube Privacy Enhanced Mode ve Cookie Optimizations
 * functions.php dosyanıza ekleyin
 */

// YouTube URL'lerini privacy-enhanced mode'a çevir
function convert_youtube_to_privacy_mode($content) {
    // YouTube embed URL'lerini youtube-nocookie.com'a çevir
    $content = str_replace('youtube.com/embed/', 'youtube-nocookie.com/embed/', $content);
    $content = str_replace('www.youtube.com/embed/', 'www.youtube-nocookie.com/embed/', $content);
    
    // Eski YouTube URL formatlarını da yakala
    $content = preg_replace(
        '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',
        'youtube-nocookie.com/embed/$1',
        $content
    );
    
    return $content;
}

// YouTube embed'lerini otomatik olarak privacy mode'a çevir
add_filter('the_content', 'convert_youtube_to_privacy_mode');
add_filter('widget_text', 'convert_youtube_to_privacy_mode');

// YouTube embed parametrelerini optimize et
function optimize_youtube_embeds($content) {
    // YouTube iframe'lerine privacy ve performans parametreleri ekle
    $content = preg_replace_callback(
        '/<iframe[^>]*src="([^"]*youtube-nocookie\.com\/embed\/[^"]*)"[^>]*><\/iframe>/i',
        function($matches) {
            $src = $matches[1];
            
            // Mevcut parametreleri kontrol et
            $has_params = strpos($src, '?') !== false;
            $separator = $has_params ? '&' : '?';
            
            // Privacy ve performans parametreleri
            $params = array(
                'rel=0',                    // İlgili videoları gösterme
                'modestbranding=1',         // YouTube logosunu minimize et
                'iv_load_policy=3',         // Annotations'ları kapatçimdi
                'enablejsapi=0',            // JS API'yi kapat (gereksizse)
                'fs=1',                     // Fullscreen'e izin ver
                'playsinline=1',            // iOS'ta inline oynat
                'controls=1',               // Kontrolleri göster
                'disablekb=0',              // Keyboard kısayollarını aktif tut
                'hl=tr',                    // Türkçe dil
                'cc_lang_pref=tr',          // Altyazı dili
                'autoplay=0',               // Otomatik oynatma kapalı
                'mute=0',                   // Sessiz başlatma
                'loop=0',                   // Döngü kapalı
                'start=0',                  // Başlangıç zamanı
                'widget_referrer=' . urlencode(home_url()) // Referrer bilgisi
            );
            
            // Parametreleri src'ye ekle
            $optimized_src = $src . $separator . implode('&', $params);
            
            // Loading ve sandbox attributeleri ile optimize edilmiş iframe
            return sprintf(
                '<iframe src="%s" frameborder="0" allowfullscreen loading="lazy" sandbox="allow-scripts allow-same-origin allow-presentation" width="560" height="315" title="YouTube video player" style="width: 100%%; height: auto; aspect-ratio: 16/9;"></iframe>',
                esc_url($optimized_src)
            );
        },
        $content
    );
    
    return $content;
}

add_filter('the_content', 'optimize_youtube_embeds', 20);

// Cookie consent banner ekle (isteğe bağlı)
function add_cookie_consent_banner() {
    if (!isset($_COOKIE['cookie_consent'])) {
        ?>
        <div id="cookie-consent-banner" class="fixed bottom-0 left-0 right-0 bg-gray-900 text-white p-4 z-50">
            <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-sm">
                    <?php esc_html_e('We use cookies to enhance your experience. By continuing to visit this site you agree to our use of cookies.', 'arcuras'); ?>
                    <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>" class="underline hover:no-underline">
                        <?php esc_html_e('Learn more', 'arcuras'); ?>
                    </a>
                </p>
                <div class="flex gap-2">
                    <button id="accept-cookies" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded text-sm">
                        <?php esc_html_e('Accept', 'arcuras'); ?>
                    </button>
                    <button id="decline-cookies" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 rounded text-sm">
                        <?php esc_html_e('Decline', 'arcuras'); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <script>
        (function() {
            const banner = document.getElementById('cookie-consent-banner');
            const acceptBtn = document.getElementById('accept-cookies');
            const declineBtn = document.getElementById('decline-cookies');
            
            if (acceptBtn) {
                acceptBtn.addEventListener('click', function() {
                    setCookie('cookie_consent', 'accepted', 365);
                    banner.remove();
                });
            }
            
            if (declineBtn) {
                declineBtn.addEventListener('click', function() {
                    setCookie('cookie_consent', 'declined', 365);
                    banner.remove();
                    // Third-party embed'leri kaldır veya uyar
                    removeThirdPartyEmbeds();
                });
            }
            
            function setCookie(name, value, days) {
                const expires = new Date(Date.now() + days * 24 * 60 * 60 * 1000).toUTCString();
                document.cookie = name + '=' + value + '; expires=' + expires + '; path=/; SameSite=Lax';
            }
            
            function removeThirdPartyEmbeds() {
                // YouTube iframe'lerini uyarı mesajı ile değiştir
                const youtubeIframes = document.querySelectorAll('iframe[src*="youtube"]');
                youtubeIframes.forEach(function(iframe) {
                    const warning = document.createElement('div');
                    warning.className = 'bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded';
                    warning.innerHTML = '<?php echo esc_js(__("This content uses third-party cookies. Please accept cookies to view this content.", "arcuras")); ?>';
                    iframe.parentNode.replaceChild(warning, iframe);
                });
            }
        })();
        </script>
        <?php
    }
}
add_action('wp_footer', 'add_cookie_consent_banner');

// Embed lazy loading
function add_embed_lazy_loading($content) {
    // Iframe'lere loading="lazy" ekle
    $content = preg_replace(
        '/<iframe(?![^>]*loading=)/i',
        '<iframe loading="lazy"',
        $content
    );
    
    return $content;
}
add_filter('the_content', 'add_embed_lazy_loading');

// Meta tag'leri optimize et
function add_seo_security_meta() {
    ?>
    <!-- SEO ve Güvenlik Meta Tags -->
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="googlebot" content="index, follow">
    <meta name="format-detection" content="telephone=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="theme-color" content="#2563eb">
    
    <!-- DNS Prefetch -->
    <link rel="dns-prefetch" href="//www.youtube-nocookie.com">
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    
    <!-- Preconnect -->
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    
    <!-- Resource Hints -->
    <link rel="prefetch" href="<?php echo esc_url(home_url('/wp-content/themes/' . get_template() . '/style.css')); ?>">
    <?php
}
add_action('wp_head', 'add_seo_security_meta', 1);
/**
 * Accessibility İyileştirmeleri - functions.php dosyanıza ekleyin
 */

// Accessibility CSS düzenlemeleri
function add_accessibility_improvements() {
    ?>
    <style>
    /* Comment Form Accessibility İyileştirmeleri */
    .comment-reply-title {
        color: #1f2937 !important; /* gray-800 - yüksek kontrast */
    }
    
    .comment-notes {
        color: #4b5563 !important; /* gray-600 - daha koyu */
    }
    
    .comment-notes-after {
        color: #4b5563 !important; /* gray-600 - daha koyu */
    }
    
    .comment-form label {
        color: #1f2937 !important; /* gray-800 - yüksek kontrast */
        font-weight: 600 !important;
    }
    
    .comment-form input[type="text"],
    .comment-form input[type="email"],
    .comment-form input[type="url"],
    .comment-form textarea {
        border: 2px solid #d1d5db !important;
        padding: 12px 16px !important;
        font-size: 16px !important;
        min-height: 44px !important; /* Touch target minimum */
        border-radius: 6px !important;
        color: #111827 !important;
        background-color: #ffffff !important;
    }
    
    .comment-form input[type="text"]:focus,
    .comment-form input[type="email"]:focus,
    .comment-form input[type="url"]:focus,
    .comment-form textarea:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
        outline: none !important;
    }
    
    .comment-form .submit {
        background-color: #2563eb !important; /* blue-600 */
        color: white !important;
        border: none !important;
        padding: 12px 24px !important;
        min-height: 44px !important; /* Touch target minimum */
        min-width: 44px !important;
        font-size: 16px !important;
        font-weight: 600 !important;
        border-radius: 6px !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
    }
    
    .comment-form .submit:hover {
        background-color: #1d4ed8 !important; /* blue-700 */
        transform: translateY(-1px) !important;
    }
    
    .comment-form .submit:focus {
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.3) !important;
        outline: none !important;
    }
    
    /* Footer Accessibility İyileştirmeleri */
    .site-footer {
        background-color: #f9fafb !important; /* gray-50 - daha açık */
        color: #374151 !important; /* gray-700 - daha koyu */
    }
    
    .site-footer h3 {
        color: #111827 !important; /* gray-900 - en koyu */
    }
    
    .site-footer a {
        color: #1f2937 !important; /* gray-800 - koyu */
        transition: color 0.2s ease !important;
    }
    
    .site-footer a:hover {
        color: #2563eb !important; /* blue-600 - mavi hover */
    }
    
    /* Touch Target İyileştirmeleri */
    .social-share-btn-floating {
        min-width: 44px !important;
        min-height: 44px !important;
    }
    
    .platform-tab {
        min-height: 44px !important;
        min-width: 44px !important;
        padding: 8px 16px !important;
        font-size: 14px !important;
    }
    
    /* Floating Player Toggle */
    #toggle-player {
        min-height: 44px !important;
        min-width: 44px !important;
        padding: 10px 16px !important;
    }
    
    /* Navigation Links */
    .post-navigation a {
        min-height: 44px !important;
        padding: 12px 16px !important;
        display: flex !important;
        align-items: center !important;
    }
    
    /* Music Platform Links */
    .music-platform-link {
        min-height: 44px !important;
        min-width: 44px !important;
        padding: 10px 16px !important;
        font-size: 14px !important;
        font-weight: 600 !important;
    }
    
    /* Category and Tag Links */
    .post-categories a,
    .tags-links a {
        min-height: 32px !important;
        padding: 6px 12px !important;
        font-size: 12px !important;
        display: inline-flex !important;
        align-items: center !important;
        color: #1f2937 !important; /* gray-800 */
        background-color: #e5e7eb !important; /* gray-200 */
        border: 1px solid #d1d5db !important;
    }
    
    .post-categories a:hover,
    .tags-links a:hover {
        color: #111827 !important; /* gray-900 */
        background-color: #d1d5db !important; /* gray-300 */
        border-color: #9ca3af !important;
    }
    
    /* Heading Hierarchy Fix */
    .entry-header h1 {
        font-size: 1.875rem !important; /* 30px */
        line-height: 1.2 !important;
    }
    
    .music-meta-grid h3 {
        font-size: 0.75rem !important; /* 12px */
        font-weight: 600 !important;
        color: #374151 !important; /* gray-700 */
    }
    
    .lyrics-content h2 {
        font-size: 1.5rem !important; /* 24px */
        color: #111827 !important; /* gray-900 */
    }
    
    /* Focus Indicators */
    a:focus,
    button:focus,
    input:focus,
    textarea:focus,
    select:focus {
        outline: 2px solid #2563eb !important;
        outline-offset: 2px !important;
    }
    
    /* Skip to main content link */
    .skip-link {
        position: absolute !important;
        top: -40px !important;
        left: 6px !important;
        background: #2563eb !important;
        color: white !important;
        padding: 8px !important;
        text-decoration: none !important;
        border-radius: 4px !important;
        z-index: 9999 !important;
    }
    
    .skip-link:focus {
        top: 6px !important;
    }
    
    /* Iframe Title Fix */
    iframe {
        border: 1px solid #e5e7eb !important;
        border-radius: 8px !important;
    }
    
    /* Screen Reader Only Content */
    .sr-only {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important;
    }
    
    /* High Contrast Mode Support */
    @media (prefers-contrast: high) {
        .music-platform-link,
        .social-share-btn-inline,
        .social-share-btn-end,
        .social-share-btn-floating {
            border: 2px solid currentColor !important;
        }
    }
    
    /* Reduced Motion Support */
    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }
    
    /* Mobile Improvements */
    @media (max-width: 640px) {
        .music-platform-link {
            min-width: 48px !important;
            min-height: 48px !important;
            font-size: 16px !important;
            padding: 12px 16px !important;
        }
        
        .social-share-btn-inline,
        .social-share-btn-end {
            min-width: 48px !important;
            min-height: 48px !important;
            font-size: 16px !important;
            padding: 12px 16px !important;
        }
        
        .comment-form input,
        .comment-form textarea {
            font-size: 16px !important; /* iOS zoom önleme */
        }
    }
    </style>
    <?php
}
add_action('wp_head', 'add_accessibility_improvements', 100);

// Skip to main content link ekle
function add_skip_link() {
    echo '<a class="skip-link screen-reader-text" href="#primary">' . esc_html__('Skip to main content', 'gufte') . '</a>';
}
add_action('wp_body_open', 'add_skip_link');

// YouTube embed'lerine title ekle
function add_youtube_iframe_titles($content) {
    // YouTube iframe'lerini bul ve title ekle
    $content = preg_replace_callback(
        '/<iframe[^>]*src="([^"]*youtube[^"]*)"[^>]*>/i',
        function($matches) {
            $iframe = $matches[0];
            
            // Eğer zaten title varsa dokunma
            if (strpos($iframe, 'title=') !== false) {
                return $iframe;
            }
            
            // Title ekle
            $title = 'title="YouTube video player"';
            $iframe = str_replace('>', ' ' . $title . '>', $iframe);
            
            return $iframe;
        },
        $content
    );
    
    // Spotify iframe'lerine title ekle
    $content = preg_replace_callback(
        '/<iframe[^>]*src="([^"]*spotify[^"]*)"[^>]*>/i',
        function($matches) {
            $iframe = $matches[0];
            
            if (strpos($iframe, 'title=') !== false) {
                return $iframe;
            }
            
            $title = 'title="Spotify player"';
            $iframe = str_replace('>', ' ' . $title . '>', $iframe);
            
            return $iframe;
        },
        $content
    );
    
    // Apple Music iframe'lerine title ekle
    $content = preg_replace_callback(
        '/<iframe[^>]*src="([^"]*music\.apple[^"]*)"[^>]*>/i',
        function($matches) {
            $iframe = $matches[0];
            
            if (strpos($iframe, 'title=') !== false) {
                return $iframe;
            }
            
            $title = 'title="Apple Music player"';
            $iframe = str_replace('>', ' ' . $title . '>', $iframe);
            
            return $iframe;
        },
        $content
    );
    
    return $content;
}
add_filter('the_content', 'add_youtube_iframe_titles');

// Footer link descriptive text düzeltmeleri
function improve_footer_links($content) {
    // "Privacy Policy" ve "Terms of Service" linklerini descriptive hale getir
    $content = str_replace(
        'Privacy Policy',
        'Privacy Policy - Learn how we protect your data',
        $content
    );
    
    $content = str_replace(
        'Terms of Service', 
        'Terms of Service - Our usage terms and conditions',
        $content
    );
    
    return $content;
}
add_filter('wp_footer', 'improve_footer_links', 5);
/**
 * WordPress Performance Optimization
 * Render-blocking kaynakları optimize eder
 * Bu kodu functions.php dosyanızın sonuna ekleyin
 */

/**
 * 1. CRITICAL CSS - İlk görünüm için kritik CSS'i inline olarak ekle
 */
function gufte_add_critical_css() {
    if (!is_admin()) {
        ?>
        <style id="critical-css">
            /* Critical CSS - Above the fold content */
            *{margin:0;padding:0;box-sizing:border-box}
            body{font-family:system-ui,-apple-system,sans-serif;line-height:1.5;color:#1f2937;background:#fff}
            .container{width:100%;max-width:1280px;margin:0 auto;padding:0 1rem}
            header{background:#fff;border-bottom:1px solid #e5e7eb}
            .hero-section{min-height:400px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%)}
            .post-header-area{background:#fff;border-radius:0.5rem;box-shadow:0 1px 3px rgba(0,0,0,0.1)}
            img{max-width:100%;height:auto;display:block}
            .skeleton{background:linear-gradient(90deg,#f0f0f0 25%,#e0e0e0 50%,#f0f0f0 75%);background-size:200% 100%;animation:loading 1.5s infinite}
            @keyframes loading{0%{background-position:200% 0}100%{background-position:-200% 0}}
            
            /* Prevent layout shift */
            .post-thumbnail img{aspect-ratio:1/1;object-fit:cover}
            .nav-menu{min-height:60px}
            .content-area{min-height:60vh}
            
            /* Hide elements until styles load */
            .requires-js{display:none}
            .js-loaded .requires-js{display:block}
        </style>
        <?php
    }
}
add_action('wp_head', 'gufte_add_critical_css', 1);

/**
 * 2. DEFER/ASYNC JavaScript yükleme
 */
function gufte_defer_scripts($tag, $handle, $src) {
    // Admin panelde çalışmasın
    if (is_admin()) {
        return $tag;
    }
    
    // jQuery ve kritik scriptler hariç tümünü defer yap
    $critical_scripts = array('jquery-core', 'jquery-migrate');
    
    if (!in_array($handle, $critical_scripts)) {
        // Defer attribute ekle
        return str_replace(' src', ' defer="defer" src', $tag);
    }
    
    return $tag;
}
add_filter('script_loader_tag', 'gufte_defer_scripts', 10, 3);

/**
 * 3. PRELOAD kritik kaynaklar
 */
function gufte_add_resource_hints() {
    ?>
    <!-- DNS Prefetch -->
    <link rel="dns-prefetch" href="//cdn.tailwindcss.com">
    <link rel="dns-prefetch" href="//code.iconify.design">
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    
    <!-- Preconnect -->
    <link rel="preconnect" href="https://cdn.tailwindcss.com" crossorigin>
    <link rel="preconnect" href="https://code.iconify.design" crossorigin>
    
    <!-- Preload kritik fontlar -->
    <?php
    $font_path = get_template_directory() . '/assets/fonts/main-font.woff2';
    if (file_exists($font_path)) :
        ?>
        <link rel="preload" as="font" type="font/woff2" href="<?php echo esc_url(get_template_directory_uri() . '/assets/fonts/main-font.woff2'); ?>" crossorigin>
    <?php endif; ?>
    
    <!-- Preload hero image (eğer varsa) -->
    <?php if (is_single() && has_post_thumbnail()) : ?>
        <link rel="preload" as="image" href="<?php echo get_the_post_thumbnail_url(null, 'medium'); ?>" fetchpriority="high">
    <?php endif; ?>
    <?php
}
add_action('wp_head', 'gufte_add_resource_hints', 2);

/**
 * 4. TAILWIND CSS Optimizasyonu - Lazy load with fallback
 */
function gufte_optimize_tailwind() {
    ?>
    <script>
        // Tailwind CSS'i lazy load et
        (function() {
            // Critical CSS zaten yüklendi, Tailwind'i lazy load et
            var tailwindLink = document.createElement('link');
            tailwindLink.rel = 'stylesheet';
            tailwindLink.href = 'https://cdn.tailwindcss.com/3.4.17';
            tailwindLink.media = 'print';
            tailwindLink.onload = function() {
                this.media = 'all';
                document.body.classList.add('tailwind-loaded');
            };
            document.head.appendChild(tailwindLink);
            
            // Fallback for noscript
            var noscript = document.createElement('noscript');
            noscript.innerHTML = '<link rel="stylesheet" href="https://cdn.tailwindcss.com/3.4.17">';
            document.head.appendChild(noscript);
        })();
    </script>
    <?php
}
add_action('wp_head', 'gufte_optimize_tailwind', 100);

/**
 * 5. ICONIFY Optimizasyonu - Lazy load with IntersectionObserver
 */
function gufte_optimize_iconify() {
    ?>
    <script>
        // Iconify'ı lazy load et
        (function() {
            var iconifyLoaded = false;
            
            function loadIconify() {
                if (iconifyLoaded) return;
                iconifyLoaded = true;
                
                var script = document.createElement('script');
                script.src = 'https://code.iconify.design/2.2.1/iconify.min.js';
                script.async = true;
                document.head.appendChild(script);
            }
            
            // İlk icon görünür olduğunda yükle
            if ('IntersectionObserver' in window) {
                var observer = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            loadIconify();
                            observer.disconnect();
                        }
                    });
                });
                
                // Iconify span'larını gözlemle
                document.addEventListener('DOMContentLoaded', function() {
                    var icons = document.querySelectorAll('.iconify, [data-icon]');
                    if (icons.length > 0) {
                        icons.forEach(function(icon) {
                            observer.observe(icon);
                        });
                    } else {
                        // Icon yoksa 3 saniye sonra yükle (fallback)
                        setTimeout(loadIconify, 3000);
                    }
                });
            } else {
                // IntersectionObserver yoksa 1 saniye sonra yükle
                setTimeout(loadIconify, 1000);
            }
        })();
    </script>
    <?php
}
add_action('wp_footer', 'gufte_optimize_iconify', 5);

/**
 * 6. jQuery Optimizasyonu
 */
function gufte_optimize_jquery() {
    if (!is_admin()) {
        $scripts = wp_scripts();

        // jQuery'yi footer'a taşı
        if (isset($scripts->registered['jquery'])) {
            $scripts->add_data('jquery', 'group', 1);
        }

        if (isset($scripts->registered['jquery-core'])) {
            $scripts->add_data('jquery-core', 'group', 1);
        }

        if (isset($scripts->registered['jquery-migrate'])) {
            unset($scripts->registered['jquery-migrate']);
        }
    }
}
add_action('wp_enqueue_scripts', 'gufte_optimize_jquery');

/**
 * 7. CSS Delivery Optimization
 */
function gufte_optimize_css_delivery($html, $handle, $href, $media) {
    // Admin'de çalışmasın
    if (is_admin()) {
        return $html;
    }
    
    // Block library CSS'i optimize et
    if ('wp-block-library' === $handle) {
        $html = '<link rel="preload" as="style" href="' . $href . '" onload="this.onload=null;this.rel=\'stylesheet\'">';
        $html .= '<noscript><link rel="stylesheet" href="' . $href . '"></noscript>';
    }
    
    return $html;
}
add_filter('style_loader_tag', 'gufte_optimize_css_delivery', 10, 4);

/**
 * 8. Remove Unused CSS/JS
 */
function gufte_remove_unused_scripts() {
    // Emoji scripts'i kaldır
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
    
    // oEmbed'i kaldır (gerekli değilse)
    wp_deregister_script('wp-embed');
    
    // Block library CSS'i sadece block içeren sayfalarda yükle
    if (!is_singular()) {
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
    }
}
add_action('wp_enqueue_scripts', 'gufte_remove_unused_scripts', 100);

/**
 * 9. Resource Hints
 */
function gufte_resource_hints($hints, $relation_type) {
    if ('dns-prefetch' === $relation_type) {
        $hints[] = '//cdn.tailwindcss.com';
        $hints[] = '//code.iconify.design';
        $hints[] = '//cdn.jsdelivr.net';
    } elseif ('preconnect' === $relation_type) {
        $hints[] = [
            'href' => 'https://cdn.tailwindcss.com',
            'crossorigin'
        ];
        $hints[] = [
            'href' => 'https://code.iconify.design',
            'crossorigin'
        ];
        $hints[] = [
            'href' => 'https://embed.music.apple.com',
            'crossorigin'
        ];
    }
    return $hints;
}
add_filter('wp_resource_hints', 'gufte_resource_hints', 10, 2);

/**
 * 10. Inline Small CSS
 */
function gufte_inline_small_css() {
    // Theme style.css inline et (küçükse)
    $theme_css_path = get_template_directory() . '/style.css';
    if (file_exists($theme_css_path)) {
        $css_size = filesize($theme_css_path);
        
        // 10KB'dan küçükse inline et
        if ($css_size < 10240) {
            $css_content = file_get_contents($theme_css_path);
            $css_content = preg_replace('/\s+/', ' ', $css_content); // Minify
            echo '<style id="theme-inline-css">' . $css_content . '</style>';
            
            // Original style.css'i dequeue et
            add_action('wp_enqueue_scripts', function() {
                wp_dequeue_style('gufte-style');
            }, 200);
        }
    }
}
add_action('wp_head', 'gufte_inline_small_css', 5);

/**
 * 11. Lazy Load Images Native
 */
function gufte_add_lazy_loading($content) {
    // loading="lazy" ekle
    $content = str_replace('<img', '<img loading="lazy" decoding="async"', $content);
    
    // İlk görünen resme eager loading ekle
    $content = preg_replace('/<img([^>]*?)loading="lazy"/', '<img$1loading="eager"', $content, 1);
    
    return $content;
}
add_filter('the_content', 'gufte_add_lazy_loading');

/**
 * 12. Preload LCP Image
 */
function gufte_preload_lcp_image() {
    if (is_single() && has_post_thumbnail()) {
        $thumbnail_id = get_post_thumbnail_id();
        $thumbnail_url = wp_get_attachment_image_url($thumbnail_id, 'large');
        
        if ($thumbnail_url) {
            echo '<link rel="preload" as="image" href="' . esc_url($thumbnail_url) . '" fetchpriority="high">' . "\n";
        }
    }
}
add_action('wp_head', 'gufte_preload_lcp_image', 3);

/**
 * 13. Optimize Web Fonts
 */
function gufte_optimize_google_fonts() {
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"></noscript>
    <?php
}
add_action('wp_head', 'gufte_optimize_google_fonts', 4);

/**
 * 14. Service Worker for Caching (Optional - Advanced)
 */
function gufte_register_service_worker() {
    ?>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').catch(function(err) {
                    console.log('ServiceWorker registration failed: ', err);
                });
            });
        }
    </script>
    <?php
}
// add_action('wp_footer', 'gufte_register_service_worker'); // Uncomment if you have sw.js

/**
 * 15. HTTP/2 Push Headers
 */
function gufte_add_http2_push_headers() {
    if (!is_admin()) {
        $css_url = get_template_directory_uri() . '/style.css';
        header("Link: <{$css_url}>; rel=preload; as=style", false);
    }
}
add_action('send_headers', 'gufte_add_http2_push_headers');
/**
 * WordPress Özel Login Sayfası Tasarımı
 * Bu kodu temanızın functions.php dosyasına ekleyin
 */

// Login sayfasına özel CSS ekle
function custom_login_styles() {
    ?>
    <style type="text/css">
        body.login {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background: radial-gradient(circle at top, rgba(79, 70, 229, 0.92), rgba(30, 64, 175, 0.95)) !important;
            color: #0f172a;
            font-family: 'Inter', 'Segoe UI', sans-serif;
        }

        body.login #login {
            margin: 0 !important;
            width: 100%;
            max-width: 420px;
        }

        .login h1 a {
            background-image: url('<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.svg' ); ?>') !important;
            background-size: contain !important;
            background-position: center !important;
            width: 200px !important;
            height: 80px !important;
            margin: 0 auto 24px;
        }

        .login form {
            background: rgba(255, 255, 255, 0.96);
            border-radius: 20px;
            padding: 40px 36px;
            box-shadow: 0 25px 60px rgba(15, 23, 42, 0.25);
            border: 1px solid rgba(148, 163, 184, 0.2);
            position: relative;
            overflow: hidden;
            color: #0f172a;
        }

        .login form::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, rgba(102, 126, 234, 0.12), rgba(118, 75, 162, 0.08));
            pointer-events: none;
        }

        .login label {
            color: #334155;
            font-weight: 600;
            font-size: 14px;
        }

        .login input[type="text"],
        .login input[type="password"],
        .login input[type="email"] {
            width: 100%;
            border-radius: 12px;
            border: 1px solid #cbd5f5;
            padding: 12px 14px;
            margin-top: 6px;
            background: #f8fafc;
            transition: all 0.25s ease;
            box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .login input[type="text"]:focus,
        .login input[type="password"]:focus,
        .login input[type="email"]:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2);
            background: #fff;
        }

        .login .button-primary {
            margin-top: 16px;
            border-radius: 12px;
            border: none;
            padding: 12px 16px;
            font-weight: 600;
            font-size: 15px;
            letter-spacing: 0.02em;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            box-shadow: 0 12px 30px rgba(99, 102, 241, 0.35);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .login .button-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 40px rgba(99, 102, 241, 0.45);
        }

        .login .button-primary:focus {
            outline: none;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.35);
        }

        .login #nav,
        .login #backtoblog {
            text-align: center;
        }

        .login #nav a,
        .login #backtoblog a {
            color: rgba(15, 23, 42, 0.75);
            font-size: 14px;
        }

        .login #nav a:hover,
        .login #backtoblog a:hover {
            color: #0f172a;
        }

        .login .message,
        .login .success,
        .login #login_error {
            border-radius: 12px;
            border-left: 4px solid #6366f1;
            background: #fff;
            color: #0f172a;
        }

        @media (max-width: 480px) {
            body.login {
                align-items: flex-start;
            }

            .login form {
                padding: 32px 24px;
            }
        }
    </style>
    <?php
}

add_action('login_enqueue_scripts', 'custom_login_styles');

/**
 * Update the logo URL on the login screen to point to the site front page.
 *
 * @return string Front page URL.
 */
function custom_login_logo_url() {
    return home_url('/');
}

add_filter('login_headerurl', 'custom_login_logo_url');

// Logo title metnini değiştir
function custom_login_logo_url_title() {
    return get_bloginfo('name') . ' - ' . get_bloginfo('description');
}
add_filter('login_headertext', 'custom_login_logo_url_title');

// Login sayfasına özel JavaScript ekle (opsiyonel animasyonlar için)
function custom_login_scripts() {
    ?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            // Form submit edildiğinde loading class ekle
            var loginForm = document.getElementById('loginform');
            if (loginForm) {
                loginForm.addEventListener('submit', function() {
                    this.classList.add('loading');
                });
            }
            
            // Input alanlarına focus olduğunda etiketlere animasyon ekle
            var inputs = document.querySelectorAll('input[type="text"], input[type="password"], input[type="email"]');
            inputs.forEach(function(input) {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                input.addEventListener('blur', function() {
                    if (this.value === '') {
                        this.parentElement.classList.remove('focused');
                    }
                });
            });
            
            // Sayfa yüklendiğinde form animasyonu
            var loginContainer = document.getElementById('login');
            if (loginContainer) {
                loginContainer.style.opacity = '0';
                setTimeout(function() {
                    loginContainer.style.transition = 'opacity 0.5s ease';
                    loginContainer.style.opacity = '1';
                }, 100);
            }
        });
    </script>
    <?php
}
add_action('login_enqueue_scripts', 'custom_login_scripts');

// Ek güvenlik: Login sayfasında WordPress versiyonunu gizle
function remove_login_version() {
    return '';
}
add_filter('the_generator', 'remove_login_version');

// Remember Me checkbox'ını varsayılan olarak işaretli yap (opsiyonel)
function login_checked_remember_me() {
    add_filter('login_footer', function() {
        echo "<script>document.getElementById('rememberme').checked = true;</script>";
    });
}
add_action('init', 'login_checked_remember_me');

/**
 * Contributor Application System
 */

// Custom database table oluştur
function create_contributor_applications_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'contributor_applications';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        full_name varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        country varchar(2) NOT NULL,
        native_language varchar(10) NOT NULL,
        contribution_languages text NOT NULL,
        experience text,
        motivation text NOT NULL,
        status varchar(20) DEFAULT 'pending',
        applied_date datetime DEFAULT CURRENT_TIMESTAMP,
        reviewed_date datetime,
        reviewed_by bigint(20),
        notes text,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY status (status)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_setup_theme', 'create_contributor_applications_table');

// AJAX handler for application submission
function handle_contributor_application() {
    // Nonce kontrolü
    if (!isset($_POST['contributor_nonce']) || !wp_verify_nonce($_POST['contributor_nonce'], 'contributor_application')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    // Kullanıcı giriş yapmış mı?
    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in to apply');
        return;
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'contributor_applications';
    $user_id = get_current_user_id();
    
    // Daha önce başvuru yapmış mı?
    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE user_id = %d AND status != 'rejected'",
        $user_id
    ));
    
    if ($existing) {
        wp_send_json_error('You have already submitted an application');
        return;
    }
    
    // Form verilerini al ve temizle
    $data = array(
        'user_id' => $user_id,
        'full_name' => sanitize_text_field($_POST['full_name']),
        'email' => sanitize_email($_POST['email']),
        'country' => sanitize_text_field($_POST['country']),
        'native_language' => sanitize_text_field($_POST['native_language']),
        'contribution_languages' => json_encode($_POST['contribution_languages']),
        'experience' => sanitize_textarea_field($_POST['experience']),
        'motivation' => sanitize_textarea_field($_POST['motivation']),
        'status' => 'pending',
        'applied_date' => current_time('mysql')
    );
    
    // Veritabanına kaydet
    $result = $wpdb->insert($table_name, $data);
    
    if ($result) {
        // Admin'e email gönder
        $admin_email = get_option('admin_email');
        $subject = 'New Contributor Application - ' . $data['full_name'];
        $message = sprintf(
            "New contributor application received:\n\n" .
            "Name: %s\n" .
            "Email: %s\n" .
            "Country: %s\n" .
            "Native Language: %s\n" .
            "Languages: %s\n\n" .
            "Review at: %s",
            $data['full_name'],
            $data['email'],
            $data['country'],
            $data['native_language'],
            implode(', ', $_POST['contribution_languages']),
            admin_url('admin.php?page=contributor-applications')
        );
        
        wp_mail($admin_email, $subject, $message);
        
        wp_send_json_success('Application submitted successfully');
    } else {
        wp_send_json_error('Failed to submit application');
    }
}
add_action('wp_ajax_submit_contributor_application', 'handle_contributor_application');

// Admin menu ekle
function add_contributor_admin_menu() {
    add_menu_page(
        'Contributor Applications',
        'Contributors',
        'manage_options',
        'contributor-applications',
        'display_contributor_applications',
        'dashicons-groups',
        30
    );
}
add_action('admin_menu', 'add_contributor_admin_menu');

// Admin sayfası
function display_contributor_applications() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'contributor_applications';
    
    // Status update işlemi
    if (isset($_POST['update_status']) && isset($_POST['application_id'])) {
        $wpdb->update(
            $table_name,
            array(
                'status' => sanitize_text_field($_POST['status']),
                'reviewed_date' => current_time('mysql'),
                'reviewed_by' => get_current_user_id(),
                'notes' => sanitize_textarea_field($_POST['notes'])
            ),
            array('id' => intval($_POST['application_id']))
        );
        
        echo '<div class="notice notice-success"><p>Application updated!</p></div>';
    }
    
    // Tüm başvuruları getir
    $applications = $wpdb->get_results("SELECT * FROM $table_name ORDER BY applied_date DESC");
    ?>
    
    <div class="wrap">
        <h1>Contributor Applications</h1>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Country</th>
                    <th>Native Language</th>
                    <th>Contribution Languages</th>
                    <th>Status</th>
                    <th>Applied Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $app): ?>
                <tr>
                    <td><?php echo esc_html($app->full_name); ?></td>
                    <td><?php echo esc_html($app->email); ?></td>
                    <td><?php echo esc_html($app->country); ?></td>
                    <td><?php echo esc_html($app->native_language); ?></td>
                    <td><?php echo esc_html(implode(', ', json_decode($app->contribution_languages))); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo esc_attr($app->status); ?>">
                            <?php echo esc_html($app->status); ?>
                        </span>
                    </td>
                    <td><?php echo esc_html($app->applied_date); ?></td>
                    <td>
                        <button class="button view-application" data-id="<?php echo $app->id; ?>">View</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Application Detail Modal -->
    <div id="application-modal" style="display:none;">
        <!-- Modal içeriği AJAX ile yüklenecek -->
    </div>
    
    <style>
    .status-badge {
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: bold;
    }
    .status-pending { background: #f0ad4e; color: white; }
    .status-approved { background: #5cb85c; color: white; }
    .status-rejected { background: #d9534f; color: white; }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        $('.view-application').on('click', function() {
            var id = $(this).data('id');
            // AJAX ile detayları getir ve modal aç
            // Bu kısım gerekirse genişletilebilir
        });
    });
    </script>
    <?php
}
/**
 * Şarkıcı Taksonomisine Müzik Platformu Profil Linkleri Ekleme
 * Bu kodu functions.php dosyanızın singer taxonomy bölümüne ekleyin
 */

/**
 * Şarkıcı taksonomisine platform profil linkleri için meta alanları ekle
 */
function gufte_register_singer_platform_fields() {
    // Spotify Artist URL
    register_term_meta('singer', 'spotify_artist_url', array(
        'type' => 'string',
        'description' => 'Spotify artist profile URL',
        'single' => true,
        'show_in_rest' => true,
    ));
    
    // YouTube Music Artist URL
    register_term_meta('singer', 'youtube_music_artist_url', array(
        'type' => 'string',
        'description' => 'YouTube Music artist channel URL',
        'single' => true,
        'show_in_rest' => true,
    ));
    
    // Apple Music Artist URL
    register_term_meta('singer', 'apple_music_artist_url', array(
        'type' => 'string',
        'description' => 'Apple Music artist profile URL',
        'single' => true,
        'show_in_rest' => true,
    ));
    
    // Apple Music Artist ID
    register_term_meta('singer', 'apple_music_artist_id', array(
        'type' => 'string',
        'description' => 'Apple Music artist ID',
        'single' => true,
        'show_in_rest' => true,
    ));
    
    // Deezer Artist URL
    register_term_meta('singer', 'deezer_artist_url', array(
        'type' => 'string',
        'description' => 'Deezer artist profile URL',
        'single' => true,
        'show_in_rest' => true,
    ));
    
    // SoundCloud Artist URL
    register_term_meta('singer', 'soundcloud_artist_url', array(
        'type' => 'string',
        'description' => 'SoundCloud artist profile URL',
        'single' => true,
        'show_in_rest' => true,
    ));
}
add_action('init', 'gufte_register_singer_platform_fields');

/**
 * Şarkıcı düzenleme formuna platform profil alanları ekle
 */
function gufte_add_singer_platform_edit_fields($term) {
    if ('singer' !== $term->taxonomy) {
        return;
    }
    
    // Mevcut değerleri al
    $spotify_url = get_term_meta($term->term_id, 'spotify_artist_url', true);
    $youtube_music_url = get_term_meta($term->term_id, 'youtube_music_artist_url', true);
    $apple_music_url = get_term_meta($term->term_id, 'apple_music_artist_url', true);
    $apple_music_id = get_term_meta($term->term_id, 'apple_music_artist_id', true);
    $deezer_url = get_term_meta($term->term_id, 'deezer_artist_url', true);
    $soundcloud_url = get_term_meta($term->term_id, 'soundcloud_artist_url', true);
    ?>
    
    <!-- Platform Profil Linkleri Başlık -->
    <tr class="form-field">
        <th colspan="2">
            <h3 style="margin: 20px 0 10px 0; padding: 10px 0; border-bottom: 2px solid #ddd;">
                🎵 Müzik Platformu Profil Linkleri
            </h3>
        </th>
    </tr>
    
    <!-- Spotify Artist URL -->
    <tr class="form-field">
        <th scope="row">
            <label for="spotify_artist_url">
                <span style="color: #1DB954;">🎵</span> Spotify Artist Profile
            </label>
        </th>
        <td>
            <input type="url" name="spotify_artist_url" id="spotify_artist_url" 
                   value="<?php echo esc_attr($spotify_url); ?>" 
                   style="width: 95%;" 
                   placeholder="https://open.spotify.com/artist/..." />
            <p class="description">Sanatçının Spotify profil sayfası URL'si</p>
        </td>
    </tr>
    
    <!-- YouTube Music Artist URL -->
    <tr class="form-field">
        <th scope="row">
            <label for="youtube_music_artist_url">
                <span style="color: #FF0000;">📺</span> YouTube Music Channel
            </label>
        </th>
        <td>
            <input type="url" name="youtube_music_artist_url" id="youtube_music_artist_url" 
                   value="<?php echo esc_attr($youtube_music_url); ?>" 
                   style="width: 95%;" 
                   placeholder="https://music.youtube.com/channel/..." />
            <p class="description">Sanatçının YouTube Music kanal URL'si</p>
        </td>
    </tr>
    
    <!-- Apple Music Artist URL -->
    <tr class="form-field">
        <th scope="row">
            <label for="apple_music_artist_url">
                <span style="color: #FA243C;">🍎</span> Apple Music Artist
            </label>
        </th>
        <td>
            <input type="url" name="apple_music_artist_url" id="apple_music_artist_url" 
                   value="<?php echo esc_attr($apple_music_url); ?>" 
                   style="width: 95%;" 
                   placeholder="https://music.apple.com/artist/..." />
            <p class="description">Sanatçının Apple Music profil URL'si</p>
            
            <?php if (!empty($apple_music_id)) : ?>
            <p style="margin-top: 5px;">
                <small style="color: #666;">Apple Music ID: <strong><?php echo esc_html($apple_music_id); ?></strong></small>
            </p>
            <?php endif; ?>
        </td>
    </tr>
    
    <!-- Deezer Artist URL -->
    <tr class="form-field">
        <th scope="row">
            <label for="deezer_artist_url">
                <span style="color: #FF6D00;">🎧</span> Deezer Artist
            </label>
        </th>
        <td>
            <input type="url" name="deezer_artist_url" id="deezer_artist_url" 
                   value="<?php echo esc_attr($deezer_url); ?>" 
                   style="width: 95%;" 
                   placeholder="https://www.deezer.com/artist/..." />
            <p class="description">Sanatçının Deezer profil URL'si</p>
        </td>
    </tr>
    
    <!-- SoundCloud Artist URL -->
    <tr class="form-field">
        <th scope="row">
            <label for="soundcloud_artist_url">
                <span style="color: #FF8800;">☁️</span> SoundCloud Artist
            </label>
        </th>
        <td>
            <input type="url" name="soundcloud_artist_url" id="soundcloud_artist_url" 
                   value="<?php echo esc_attr($soundcloud_url); ?>" 
                   style="width: 95%;" 
                   placeholder="https://soundcloud.com/..." />
            <p class="description">Sanatçının SoundCloud profil URL'si</p>
        </td>
    </tr>
    
    <!-- Otomatik Çekme Butonu -->
    <tr class="form-field">
        <th scope="row"></th>
        <td>
            <button type="button" id="fetch_artist_profiles" class="button button-secondary">
                <span class="dashicons dashicons-download" style="vertical-align: middle;"></span>
                Platform Bilgilerini Otomatik Çek
            </button>
            <span id="fetch_status" style="margin-left: 10px;"></span>
            <p class="description" style="margin-top: 10px;">
                Sanatçının platform profillerini otomatik olarak bulmaya çalışır (Apple Music ID gerekli)
            </p>
        </td>
    </tr>
    
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#fetch_artist_profiles').on('click', function() {
            var button = $(this);
            var artistName = '<?php echo esc_js($term->name); ?>';
            
            button.prop('disabled', true);
            $('#fetch_status').html('<span style="color: #0073aa;">Aranıyor...</span>');
            
            // AJAX ile sanatçı bilgilerini çek
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'fetch_artist_platform_profiles',
                    artist_name: artistName,
                    term_id: <?php echo $term->term_id; ?>,
                    nonce: '<?php echo wp_create_nonce('fetch_artist_profiles'); ?>'
                },
                success: function(response) {
                    button.prop('disabled', false);
                    
                    if (response.success) {
                        $('#fetch_status').html('<span style="color: #46b450;">✓ Bilgiler güncellendi!</span>');
                        
                        // URL alanlarını güncelle
                        if (response.data.apple_music_url) {
                            $('#apple_music_artist_url').val(response.data.apple_music_url);
                        }
                        if (response.data.spotify_url) {
                            $('#spotify_artist_url').val(response.data.spotify_url);
                        }
                        
                        // 3 saniye sonra mesajı temizle
                        setTimeout(function() {
                            $('#fetch_status').html('');
                        }, 3000);
                    } else {
                        $('#fetch_status').html('<span style="color: #dc3232;">✗ ' + response.data + '</span>');
                    }
                },
                error: function() {
                    button.prop('disabled', false);
                    $('#fetch_status').html('<span style="color: #dc3232;">Bağlantı hatası</span>');
                }
            });
        });
    });
    </script>
    <?php
}
add_action('singer_edit_form_fields', 'gufte_add_singer_platform_edit_fields', 15);

/**
 * Yeni şarkıcı ekleme formuna platform alanları ekle
 */
function gufte_add_new_singer_platform_fields() {
    ?>
    <div class="form-field">
        <h3>🎵 Müzik Platformu Profil Linkleri</h3>
    </div>
    
    <div class="form-field">
        <label for="spotify_artist_url">
            <span style="color: #1DB954;">🎵</span> Spotify Artist Profile
        </label>
        <input type="url" name="spotify_artist_url" id="spotify_artist_url" 
               placeholder="https://open.spotify.com/artist/..." />
        <p>Sanatçının Spotify profil sayfası URL'si</p>
    </div>
    
    <div class="form-field">
        <label for="youtube_music_artist_url">
            <span style="color: #FF0000;">📺</span> YouTube Music Channel
        </label>
        <input type="url" name="youtube_music_artist_url" id="youtube_music_artist_url" 
               placeholder="https://music.youtube.com/channel/..." />
        <p>Sanatçının YouTube Music kanal URL'si</p>
    </div>
    
    <div class="form-field">
        <label for="apple_music_artist_url">
            <span style="color: #FA243C;">🍎</span> Apple Music Artist
        </label>
        <input type="url" name="apple_music_artist_url" id="apple_music_artist_url" 
               placeholder="https://music.apple.com/artist/..." />
        <p>Sanatçının Apple Music profil URL'si</p>
    </div>
    
    <div class="form-field">
        <label for="deezer_artist_url">
            <span style="color: #FF6D00;">🎧</span> Deezer Artist
        </label>
        <input type="url" name="deezer_artist_url" id="deezer_artist_url" 
               placeholder="https://www.deezer.com/artist/..." />
        <p>Sanatçının Deezer profil URL'si</p>
    </div>
    
    <div class="form-field">
        <label for="soundcloud_artist_url">
            <span style="color: #FF8800;">☁️</span> SoundCloud Artist
        </label>
        <input type="url" name="soundcloud_artist_url" id="soundcloud_artist_url" 
               placeholder="https://soundcloud.com/..." />
        <p>Sanatçının SoundCloud profil URL'si</p>
    </div>
    <?php
}
add_action('singer_add_form_fields', 'gufte_add_new_singer_platform_fields', 15);

/**
 * Platform profil bilgilerini kaydet
 */
function gufte_save_singer_platform_fields($term_id) {
    // Spotify URL
    if (isset($_POST['spotify_artist_url'])) {
        update_term_meta($term_id, 'spotify_artist_url', 
            sanitize_url($_POST['spotify_artist_url']));
    }
    
    // YouTube Music URL
    if (isset($_POST['youtube_music_artist_url'])) {
        update_term_meta($term_id, 'youtube_music_artist_url', 
            sanitize_url($_POST['youtube_music_artist_url']));
    }
    
    // Apple Music URL
    if (isset($_POST['apple_music_artist_url'])) {
        update_term_meta($term_id, 'apple_music_artist_url', 
            sanitize_url($_POST['apple_music_artist_url']));
        
        // Apple Music ID'yi URL'den çıkar
        if (preg_match('/artist\/([^\/]+)\/(\d+)/', $_POST['apple_music_artist_url'], $matches)) {
            update_term_meta($term_id, 'apple_music_artist_id', $matches[2]);
        }
    }
    
    // Deezer URL
    if (isset($_POST['deezer_artist_url'])) {
        update_term_meta($term_id, 'deezer_artist_url', 
            sanitize_url($_POST['deezer_artist_url']));
    }
    
    // SoundCloud URL
    if (isset($_POST['soundcloud_artist_url'])) {
        update_term_meta($term_id, 'soundcloud_artist_url', 
            sanitize_url($_POST['soundcloud_artist_url']));
    }
}
add_action('created_singer', 'gufte_save_singer_platform_fields');
add_action('edited_singer', 'gufte_save_singer_platform_fields');

/**
 * AJAX handler - Sanatçı platform profillerini otomatik çek
 */
function gufte_ajax_fetch_artist_platform_profiles() {
    // Nonce kontrolü
    if (!wp_verify_nonce($_POST['nonce'], 'fetch_artist_profiles')) {
        wp_send_json_error('Güvenlik kontrolü başarısız');
    }
    
    $artist_name = sanitize_text_field($_POST['artist_name']);
    $term_id = intval($_POST['term_id']);
    
    if (empty($artist_name)) {
        wp_send_json_error('Sanatçı adı bulunamadı');
    }
    
    // iTunes Search API ile sanatçıyı ara
    $search_url = 'https://itunes.apple.com/search?' . http_build_query(array(
        'term' => $artist_name,
        'entity' => 'musicArtist',
        'limit' => 1
    ));
    
    $response = wp_remote_get($search_url);
    
    if (is_wp_error($response)) {
        wp_send_json_error('API bağlantı hatası');
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    $result = array();
    
    if (!empty($data['results'][0])) {
        $artist_data = $data['results'][0];
        
        // Apple Music Artist URL
        if (isset($artist_data['artistLinkUrl'])) {
            $apple_url = $artist_data['artistLinkUrl'];
            update_term_meta($term_id, 'apple_music_artist_url', $apple_url);
            $result['apple_music_url'] = $apple_url;
        }
        
        // Apple Music Artist ID
        if (isset($artist_data['artistId'])) {
            update_term_meta($term_id, 'apple_music_artist_id', $artist_data['artistId']);
            $result['apple_music_id'] = $artist_data['artistId'];
        }
        
        // Spotify'da ara (Web API gerekir - basit arama için)
        // Not: Gerçek implementasyon için Spotify Web API credential'ları gerekir
        // Bu örnek sadece URL formatını gösteriyor
        $spotify_search_url = 'https://open.spotify.com/search/' . urlencode($artist_name);
        $result['spotify_search_url'] = $spotify_search_url;
    }
    
    if (!empty($result)) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error('Sanatçı bilgileri bulunamadı');
    }
}
add_action('wp_ajax_fetch_artist_platform_profiles', 'gufte_ajax_fetch_artist_platform_profiles');

/**
 * Şarkı kaydedildiğinde sanatçı bilgilerini otomatik güncelle
 */
function gufte_auto_update_singer_profiles($post_id) {
    // Sadece post tipinde çalış
    if (get_post_type($post_id) !== 'post') {
        return;
    }
    
    // Apple Music'ten veri çekilmişse
    $artist_name = get_post_meta($post_id, '_apple_music_artist_name', true);
    
    if (empty($artist_name)) {
        return;
    }
    
    // Bu sanatçının taksonomide var olup olmadığını kontrol et
    $singer_term = get_term_by('name', $artist_name, 'singer');
    
    if (!$singer_term) {
        // Sanatçı yoksa oluştur
        $term_result = wp_insert_term($artist_name, 'singer');
        if (!is_wp_error($term_result)) {
            $singer_term = get_term($term_result['term_id'], 'singer');
        }
    }
    
    if ($singer_term && !is_wp_error($singer_term)) {
        // Apple Music Artist bilgilerini ara
        $search_url = 'https://itunes.apple.com/search?' . http_build_query(array(
            'term' => $artist_name,
            'entity' => 'musicArtist',
            'limit' => 1
        ));
        
        $response = wp_remote_get($search_url);
        
        if (!is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            if (!empty($data['results'][0])) {
                $artist_data = $data['results'][0];
                
                // Apple Music bilgilerini kaydet
                if (isset($artist_data['artistLinkUrl'])) {
                    update_term_meta($singer_term->term_id, 'apple_music_artist_url', 
                        $artist_data['artistLinkUrl']);
                }
                
                if (isset($artist_data['artistId'])) {
                    update_term_meta($singer_term->term_id, 'apple_music_artist_id', 
                        $artist_data['artistId']);
                }
            }
        }
        
        // Yazıya sanatçıyı ata
        wp_set_post_terms($post_id, array($singer_term->term_id), 'singer', true);
    }
}
add_action('save_post', 'gufte_auto_update_singer_profiles', 30);

/**
 * Helper function - Sanatçının platform linklerini getir
 */
function gufte_get_singer_platform_links($term_id) {
    return array(
        'spotify' => get_term_meta($term_id, 'spotify_artist_url', true),
        'youtube_music' => get_term_meta($term_id, 'youtube_music_artist_url', true),
        'apple_music' => get_term_meta($term_id, 'apple_music_artist_url', true),
        'deezer' => get_term_meta($term_id, 'deezer_artist_url', true),
        'soundcloud' => get_term_meta($term_id, 'soundcloud_artist_url', true)
    );
}

/**
 * Apple Music'ten Albüm Bilgilerini Otomatik Çekme
 * Bu kodu functions.php dosyanızın sonuna ekleyin
 */

/**
 * Albüm taksonomisine Apple Music ID meta alanı ekle
 */
function gufte_register_album_apple_music_fields() {
    register_term_meta('album', 'apple_music_album_id', array(
        'type' => 'string',
        'description' => 'Apple Music Album ID',
        'single' => true,
        'show_in_rest' => true,
    ));
    
    register_term_meta('album', 'apple_music_album_url', array(
        'type' => 'string',
        'description' => 'Apple Music Album URL',
        'single' => true,
        'show_in_rest' => true,
    ));
    
    register_term_meta('album', 'album_release_date', array(
        'type' => 'string',
        'description' => 'Album Release Date',
        'single' => true,
        'show_in_rest' => true,
    ));
    
    register_term_meta('album', 'album_track_count', array(
        'type' => 'integer',
        'description' => 'Number of tracks in album',
        'single' => true,
        'show_in_rest' => true,
    ));
}
add_action('init', 'gufte_register_album_apple_music_fields');

/**
 * Post kaydedildiğinde Apple Music'ten albüm bilgilerini otomatik çek
 */
function gufte_auto_fetch_and_create_album($post_id) {
    // Sadece post tipinde çalış
    if (get_post_type($post_id) !== 'post') {
        return;
    }
    
    // Autosave kontrolü
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Apple Music ID'yi kontrol et
    $apple_music_id = get_post_meta($post_id, '_apple_music_id', true);
    
    if (empty($apple_music_id)) {
        return;
    }
    
    // iTunes API'den track bilgilerini al
    $api_url = "https://itunes.apple.com/lookup?id=" . urlencode($apple_music_id) . "&entity=song";
    $response = wp_remote_get($api_url, array('timeout' => 30));
    
    if (is_wp_error($response)) {
        return;
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (empty($data['results'][0])) {
        return;
    }
    
    $track_info = $data['results'][0];
    
    // Albüm bilgileri varsa işle
    if (isset($track_info['collectionName']) && !empty($track_info['collectionName'])) {
        $album_name = $track_info['collectionName'];
        
        // Albümün zaten var olup olmadığını kontrol et
        $existing_album = get_term_by('name', $album_name, 'album');
        
        if (!$existing_album) {
            // Albüm yoksa oluştur
            $album_result = wp_insert_term($album_name, 'album');
            
            if (!is_wp_error($album_result)) {
                $album_term_id = $album_result['term_id'];
                
                // Albüm meta bilgilerini kaydet
                if (isset($track_info['collectionId'])) {
                    update_term_meta($album_term_id, 'apple_music_album_id', $track_info['collectionId']);
                    
                    // Albüm detaylarını çek
                    gufte_fetch_album_details($album_term_id, $track_info['collectionId']);
                }
                
                if (isset($track_info['collectionViewUrl'])) {
                    update_term_meta($album_term_id, 'apple_music_album_url', $track_info['collectionViewUrl']);
                }
                
                if (isset($track_info['releaseDate'])) {
                    $release_year = date('Y', strtotime($track_info['releaseDate']));
                    update_term_meta($album_term_id, 'album_year', $release_year);
                    update_term_meta($album_term_id, 'album_release_date', date('Y-m-d', strtotime($track_info['releaseDate'])));
                }
                
                if (isset($track_info['trackCount'])) {
                    update_term_meta($album_term_id, 'album_track_count', $track_info['trackCount']);
                }
                
                // Sanatçı bilgisi varsa albüme ekle
                if (isset($track_info['artistName'])) {
                    $singer_term = get_term_by('name', $track_info['artistName'], 'singer');
                    if ($singer_term) {
                        $existing_singers = get_term_meta($album_term_id, 'album_singers', true);
                        if (!is_array($existing_singers)) {
                            $existing_singers = array();
                        }
                        if (!in_array($singer_term->term_id, $existing_singers)) {
                            $existing_singers[] = $singer_term->term_id;
                            update_term_meta($album_term_id, 'album_singers', $existing_singers);
                        }
                    }
                }
                
                // Yazıya albümü ata
                wp_set_post_terms($post_id, array($album_term_id), 'album', true);
                
            }
        } else {
            // Albüm zaten varsa, sadece yazıya ata
            $album_term_id = $existing_album->term_id;
            
            // Albüm bilgilerini güncelle (eğer eksikse)
            if (isset($track_info['collectionId'])) {
                $existing_album_id = get_term_meta($album_term_id, 'apple_music_album_id', true);
                if (empty($existing_album_id)) {
                    update_term_meta($album_term_id, 'apple_music_album_id', $track_info['collectionId']);
                    
                    // Albüm detaylarını çek
                    gufte_fetch_album_details($album_term_id, $track_info['collectionId']);
                }
            }
            
            // Yazıya albümü ata
            wp_set_post_terms($post_id, array($album_term_id), 'album', true);
        }
    }
}
add_action('save_post', 'gufte_auto_fetch_and_create_album', 35);

/**
 * Birden Fazla Sanatçıyı Otomatik Ayırma ve Ekleme
 * Bu kodu mevcut gufte_auto_fetch_and_create_album fonksiyonunun altına ekleyin
 */

/**
 * Sanatçı adlarını ayır ve taksonomiye ekle
 */
function gufte_parse_and_create_artists($artist_string, $post_id = null) {
    if (empty($artist_string)) {
        return array();
    }
    
    // Ayırıcı karakterleri tanımla
    $separators = array(
        ' & ',
        ' and ',
        ' AND ',
        ' feat. ',
        ' ft. ',
        ' Feat. ',
        ' Ft. ',
        ' featuring ',
        ' Featuring ',
        ' with ',
        ' With ',
        ' x ',
        ' X ',
        ' vs. ',
        ' VS. ',
        ' versus ',
        ', ',
        '; '
    );
    
    // Orijinal stringi koru
    $original_string = $artist_string;
    
    // Tüm ayırıcıları virgülle değiştir
    foreach ($separators as $separator) {
        $artist_string = str_replace($separator, ',', $artist_string);
    }
    
    // Virgülle ayır ve temizle
    $artists = explode(',', $artist_string);
    $artist_terms = array();
    
    foreach ($artists as $artist_name) {
        // Baş ve sondaki boşlukları temizle
        $artist_name = trim($artist_name);
        
        // Boş değilse işle
        if (!empty($artist_name)) {
            // Parantez içindeki ek bilgileri temizle (opsiyonel)
            $artist_name = preg_replace('/\([^)]*\)/', '', $artist_name);
            $artist_name = trim($artist_name);
            
            if (!empty($artist_name)) {
                // Sanatçının mevcut olup olmadığını kontrol et
                $existing_term = get_term_by('name', $artist_name, 'singer');
                
                if (!$existing_term) {
                    // Sanatçı yoksa oluştur
                    $term_result = wp_insert_term($artist_name, 'singer');
                    
                    if (!is_wp_error($term_result)) {
                        $artist_terms[] = $term_result['term_id'];
                        
                        // Sanatçı için Apple Music bilgilerini ara
                        gufte_search_artist_on_apple_music($term_result['term_id'], $artist_name);
                    }
                } else {
                    $artist_terms[] = $existing_term->term_id;
                }
            }
        }
    }
    
    // Eğer post_id verilmişse, sanatçıları yazıya ata
    if ($post_id && !empty($artist_terms)) {
        wp_set_post_terms($post_id, $artist_terms, 'singer', false);
    }
    
    return $artist_terms;
}

/**
 * Apple Music'te sanatçı ara ve bilgilerini kaydet
 */
function gufte_search_artist_on_apple_music($term_id, $artist_name) {
    // iTunes Search API ile sanatçıyı ara
    $search_url = 'https://itunes.apple.com/search?' . http_build_query(array(
        'term' => $artist_name,
        'entity' => 'musicArtist',
        'limit' => 1
    ));
    
    $response = wp_remote_get($search_url, array('timeout' => 30));
    
    if (!is_wp_error($response)) {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!empty($data['results'][0])) {
            $artist_data = $data['results'][0];
            
            // Apple Music bilgilerini kaydet
            if (isset($artist_data['artistLinkUrl'])) {
                update_term_meta($term_id, 'apple_music_artist_url', $artist_data['artistLinkUrl']);
            }
            
            if (isset($artist_data['artistId'])) {
                update_term_meta($term_id, 'apple_music_artist_id', $artist_data['artistId']);
            }
            
            if (isset($artist_data['primaryGenreName'])) {
                update_term_meta($term_id, 'artist_genre', $artist_data['primaryGenreName']);
            }
        }
    }
}

/**
 * Güncellenen auto fetch album fonksiyonu - birden fazla sanatçı desteği ile
 */
function gufte_auto_fetch_and_create_album_with_multiple_artists($post_id) {
    // Sadece post tipinde çalış
    if (get_post_type($post_id) !== 'post') {
        return;
    }
    
    // Autosave kontrolü
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Apple Music ID'yi kontrol et
    $apple_music_id = get_post_meta($post_id, '_apple_music_id', true);
    
    if (empty($apple_music_id)) {
        return;
    }
    
    // iTunes API'den track bilgilerini al
    $api_url = "https://itunes.apple.com/lookup?id=" . urlencode($apple_music_id) . "&entity=song";
    $response = wp_remote_get($api_url, array('timeout' => 30));
    
    if (is_wp_error($response)) {
        return;
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (empty($data['results'][0])) {
        return;
    }
    
    $track_info = $data['results'][0];
    
    // Sanatçıları işle (birden fazla olabilir)
    if (isset($track_info['artistName'])) {
        $artist_term_ids = gufte_parse_and_create_artists($track_info['artistName'], $post_id);
    }
    
    // Albüm bilgileri varsa işle
    if (isset($track_info['collectionName']) && !empty($track_info['collectionName'])) {
        $album_name = $track_info['collectionName'];
        
        // Albümün zaten var olup olmadığını kontrol et
        $existing_album = get_term_by('name', $album_name, 'album');
        
        if (!$existing_album) {
            // Albüm yoksa oluştur
            $album_result = wp_insert_term($album_name, 'album');
            
            if (!is_wp_error($album_result)) {
                $album_term_id = $album_result['term_id'];
                
                // Albüm meta bilgilerini kaydet
                if (isset($track_info['collectionId'])) {
                    update_term_meta($album_term_id, 'apple_music_album_id', $track_info['collectionId']);
                    
                    // Albüm detaylarını çek
                    gufte_fetch_album_details_with_artists($album_term_id, $track_info['collectionId']);
                }
                
                if (isset($track_info['collectionViewUrl'])) {
                    update_term_meta($album_term_id, 'apple_music_album_url', $track_info['collectionViewUrl']);
                }
                
                if (isset($track_info['releaseDate'])) {
                    $release_year = date('Y', strtotime($track_info['releaseDate']));
                    update_term_meta($album_term_id, 'album_year', $release_year);
                    update_term_meta($album_term_id, 'album_release_date', date('Y-m-d', strtotime($track_info['releaseDate'])));
                }
                
                if (isset($track_info['trackCount'])) {
                    update_term_meta($album_term_id, 'album_track_count', $track_info['trackCount']);
                }
                
                // Sanatçıları albüme ekle
                if (!empty($artist_term_ids)) {
                    update_term_meta($album_term_id, 'album_singers', $artist_term_ids);
                }
                
                // Yazıya albümü ata
                wp_set_post_terms($post_id, array($album_term_id), 'album', true);
            }
        } else {
            // Albüm zaten varsa
            $album_term_id = $existing_album->term_id;
            
            // Albüm bilgilerini güncelle (eğer eksikse)
            if (isset($track_info['collectionId'])) {
                $existing_album_id = get_term_meta($album_term_id, 'apple_music_album_id', true);
                if (empty($existing_album_id)) {
                    update_term_meta($album_term_id, 'apple_music_album_id', $track_info['collectionId']);
                    gufte_fetch_album_details_with_artists($album_term_id, $track_info['collectionId']);
                }
            }
            
            // Mevcut sanatçıları güncelle
            if (!empty($artist_term_ids)) {
                $existing_singers = get_term_meta($album_term_id, 'album_singers', true);
                if (!is_array($existing_singers)) {
                    $existing_singers = array();
                }
                
                // Yeni sanatçıları ekle
                $updated_singers = array_unique(array_merge($existing_singers, $artist_term_ids));
                update_term_meta($album_term_id, 'album_singers', $updated_singers);
            }
            
            // Yazıya albümü ata
            wp_set_post_terms($post_id, array($album_term_id), 'album', true);
        }
    }
}

// Eski fonksiyonu kaldır ve yenisiyle değiştir
remove_action('save_post', 'gufte_auto_fetch_and_create_album', 35);
add_action('save_post', 'gufte_auto_fetch_and_create_album_with_multiple_artists', 35);

/**
 * Albüm detaylarını çekerken sanatçıları da işle
 */
function gufte_fetch_album_details_with_artists($album_term_id, $collection_id) {
    // iTunes API'den albüm bilgilerini al
    $api_url = "https://itunes.apple.com/lookup?id=" . urlencode($collection_id) . "&entity=album";
    $response = wp_remote_get($api_url, array('timeout' => 30));
    
    if (is_wp_error($response)) {
        return;
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (empty($data['results'][0])) {
        return;
    }
    
    $album_info = $data['results'][0];
    
    // Albüm bilgilerini güncelle
    if (isset($album_info['collectionName'])) {
        wp_update_term($album_term_id, 'album', array(
            'name' => $album_info['collectionName']
        ));
    }
    
    // Sanatçıları işle
    if (isset($album_info['artistName'])) {
        $artist_term_ids = gufte_parse_and_create_artists($album_info['artistName']);
        
        if (!empty($artist_term_ids)) {
            update_term_meta($album_term_id, 'album_singers', $artist_term_ids);
        }
    }
    
    // Diğer albüm bilgileri
    if (isset($album_info['collectionViewUrl'])) {
        update_term_meta($album_term_id, 'apple_music_album_url', $album_info['collectionViewUrl']);
    }
    
    if (isset($album_info['releaseDate'])) {
        $release_year = date('Y', strtotime($album_info['releaseDate']));
        update_term_meta($album_term_id, 'album_year', $release_year);
        update_term_meta($album_term_id, 'album_release_date', date('Y-m-d', strtotime($album_info['releaseDate'])));
    }
    
    if (isset($album_info['trackCount'])) {
        update_term_meta($album_term_id, 'album_track_count', $album_info['trackCount']);
    }
    
    if (isset($album_info['primaryGenreName'])) {
        update_term_meta($album_term_id, 'album_genre', $album_info['primaryGenreName']);
    }
    
    if (isset($album_info['copyright'])) {
        update_term_meta($album_term_id, 'album_copyright', $album_info['copyright']);
    }
    
    // Albüm kapağını indir ve kaydet
    if (isset($album_info['artworkUrl100'])) {
        $artwork_url = str_replace('100x100', '1400x1400', $album_info['artworkUrl100']);
        gufte_save_album_artwork($album_term_id, $artwork_url);
    }
}

/**
 * Test fonksiyonu - Sanatçı ayırma işlemini test et
 */
function gufte_test_artist_parsing() {
    $test_cases = array(
        "Taylor Swift & Ed Sheeran",
        "Ariana Grande, Justin Bieber",
        "Drake feat. Future",
        "Post Malone ft. 21 Savage",
        "Maroon 5 featuring Cardi B",
        "The Weeknd, Kendrick Lamar",
        "BTS & Halsey",
        "Eminem vs Machine Gun Kelly",
        "Lady Gaga and Bradley Cooper",
        "Travis Scott x Kid Cudi"
    );
    
    foreach ($test_cases as $artist_string) {
        echo "Original: " . $artist_string . "<br>";
        $artists = gufte_parse_and_create_artists($artist_string);
        echo "Parsed: " . implode(', ', $artists) . "<br><br>";
    }
}

/**
 * Apple Music'ten albüm detaylarını çek
 */
function gufte_fetch_album_details($album_term_id, $collection_id) {
    // iTunes API'den albüm bilgilerini al
    $api_url = "https://itunes.apple.com/lookup?id=" . urlencode($collection_id) . "&entity=album";
    $response = wp_remote_get($api_url, array('timeout' => 30));
    
    if (is_wp_error($response)) {
        return;
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (empty($data['results'][0])) {
        return;
    }
    
    $album_info = $data['results'][0];
    
    // Albüm bilgilerini güncelle
    if (isset($album_info['collectionName'])) {
        wp_update_term($album_term_id, 'album', array(
            'name' => $album_info['collectionName']
        ));
    }
    
    if (isset($album_info['collectionViewUrl'])) {
        update_term_meta($album_term_id, 'apple_music_album_url', $album_info['collectionViewUrl']);
    }
    
    if (isset($album_info['releaseDate'])) {
        $release_year = date('Y', strtotime($album_info['releaseDate']));
        update_term_meta($album_term_id, 'album_year', $release_year);
        update_term_meta($album_term_id, 'album_release_date', date('Y-m-d', strtotime($album_info['releaseDate'])));
    }
    
    if (isset($album_info['trackCount'])) {
        update_term_meta($album_term_id, 'album_track_count', $album_info['trackCount']);
    }
    
    if (isset($album_info['primaryGenreName'])) {
        update_term_meta($album_term_id, 'album_genre', $album_info['primaryGenreName']);
    }
    
    if (isset($album_info['copyright'])) {
        update_term_meta($album_term_id, 'album_copyright', $album_info['copyright']);
    }
    
    // Albüm kapağını indir ve kaydet (opsiyonel)
    if (isset($album_info['artworkUrl100'])) {
        $artwork_url = str_replace('100x100', '1400x1400', $album_info['artworkUrl100']);
        gufte_save_album_artwork($album_term_id, $artwork_url);
    }
}

/**
 * Albüm kapağını indir ve term meta olarak kaydet
 */
function gufte_save_album_artwork($album_term_id, $artwork_url) {
    if (!function_exists('media_sideload_image')) {
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
    }
    
    // Görseli indir
    $tmp = download_url($artwork_url);
    
    if (is_wp_error($tmp)) {
        return;
    }
    
    $file_array = array(
        'name' => 'album-' . $album_term_id . '-artwork.jpg',
        'tmp_name' => $tmp
    );
    
    // Media library'e yükle
    $attachment_id = media_handle_sideload($file_array, 0);
    
    if (!is_wp_error($attachment_id)) {
        // Albüm için görsel ID'sini kaydet
        update_term_meta($album_term_id, 'album_artwork_id', $attachment_id);
    }
    
    @unlink($tmp);
}

/**
 * Albüm düzenleme formuna Apple Music alanları ekle
 */
function gufte_add_album_apple_music_edit_fields($term) {
    if ('album' !== $term->taxonomy) {
        return;
    }
    
    $apple_music_id = get_term_meta($term->term_id, 'apple_music_album_id', true);
    $apple_music_url = get_term_meta($term->term_id, 'apple_music_album_url', true);
    $release_date = get_term_meta($term->term_id, 'album_release_date', true);
    $track_count = get_term_meta($term->term_id, 'album_track_count', true);
    $album_genre = get_term_meta($term->term_id, 'album_genre', true);
    ?>
    
    <tr class="form-field">
        <th colspan="2">
            <h3 style="margin: 20px 0 10px 0; padding: 10px 0; border-bottom: 2px solid #ddd;">
                🍎 Apple Music Albüm Bilgileri
            </h3>
        </th>
    </tr>
    
    <tr class="form-field">
        <th scope="row">
            <label for="apple_music_album_id">Apple Music Album ID</label>
        </th>
        <td>
            <input type="text" name="apple_music_album_id" id="apple_music_album_id" 
                   value="<?php echo esc_attr($apple_music_id); ?>" />
            <p class="description">Apple Music albüm ID'si</p>
        </td>
    </tr>
    
    <tr class="form-field">
        <th scope="row">
            <label for="apple_music_album_url">Apple Music Album URL</label>
        </th>
        <td>
            <input type="url" name="apple_music_album_url" id="apple_music_album_url" 
                   value="<?php echo esc_attr($apple_music_url); ?>" style="width: 95%;" />
            <p class="description">Apple Music albüm sayfası URL'si</p>
        </td>
    </tr>
    
    <?php if (!empty($release_date)) : ?>
    <tr class="form-field">
        <th scope="row">Release Date</th>
        <td>
            <strong><?php echo esc_html(date('F j, Y', strtotime($release_date))); ?></strong>
            <?php if (!empty($track_count)) : ?>
            <br><small>Track Count: <?php echo esc_html($track_count); ?></small>
            <?php endif; ?>
            <?php if (!empty($album_genre)) : ?>
            <br><small>Genre: <?php echo esc_html($album_genre); ?></small>
            <?php endif; ?>
        </td>
    </tr>
    <?php endif; ?>
    
    <tr class="form-field">
        <th scope="row"></th>
        <td>
            <button type="button" id="fetch_album_details" class="button button-secondary">
                <span class="dashicons dashicons-download"></span>
                Apple Music'ten Albüm Bilgilerini Çek
            </button>
            <span id="fetch_album_status" style="margin-left: 10px;"></span>
        </td>
    </tr>
    
    <script>
    jQuery(document).ready(function($) {
        $('#fetch_album_details').on('click', function() {
            var albumId = $('#apple_music_album_id').val();
            if (!albumId) {
                $('#fetch_album_status').html('<span style="color: red;">Lütfen Apple Music Album ID girin</span>');
                return;
            }
            
            var button = $(this);
            button.prop('disabled', true);
            $('#fetch_album_status').html('Çekiliyor...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'fetch_album_details_ajax',
                    album_id: albumId,
                    term_id: <?php echo $term->term_id; ?>,
                    nonce: '<?php echo wp_create_nonce('fetch_album_details'); ?>'
                },
                success: function(response) {
                    button.prop('disabled', false);
                    if (response.success) {
                        $('#fetch_album_status').html('<span style="color: green;">✓ Bilgiler güncellendi!</span>');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        $('#fetch_album_status').html('<span style="color: red;">' + response.data + '</span>');
                    }
                }
            });
        });
    });
    </script>
    <?php
}
add_action('album_edit_form_fields', 'gufte_add_album_apple_music_edit_fields', 20);

/**
 * AJAX handler - Albüm detaylarını çek
 */
function gufte_ajax_fetch_album_details() {
    if (!wp_verify_nonce($_POST['nonce'], 'fetch_album_details')) {
        wp_send_json_error('Güvenlik kontrolü başarısız');
    }
    
    $album_id = sanitize_text_field($_POST['album_id']);
    $term_id = intval($_POST['term_id']);
    
    gufte_fetch_album_details($term_id, $album_id);
    
    wp_send_json_success('Albüm bilgileri güncellendi');
}
add_action('wp_ajax_fetch_album_details_ajax', 'gufte_ajax_fetch_album_details');
// WordPress Admin Dashboard - Güvenli Modern Tema

// Admin CSS ekle
add_action('admin_enqueue_scripts', 'safe_modern_admin_styles');
function safe_modern_admin_styles() {
    wp_add_inline_style('wp-admin', '
        :root {
            --modern-primary: #5e72e4;
            --modern-secondary: #825ee4;
            --modern-success: #2dce89;
            --modern-info: #11cdef;
            --modern-warning: #fb6340;
            --modern-dark: #32325d;
        }
        
        /* Admin Bar - Hafif gradient */
        #wpadminbar {
            background: linear-gradient(90deg, #2c3e50 0%, #34495e 100%) !important;
        }
        
        /* Sol Menü - Yumuşak stiller */
        #adminmenu, #adminmenuback, #adminmenuwrap {
            background-color: #2c3e50;
        }
        
        #adminmenu a {
            color: #fff;
            transition: all 0.2s ease;
        }
        
        #adminmenu .wp-submenu {
            background: #34495e;
        }
        
        #adminmenu li.menu-top:hover,
        #adminmenu li.opensub > a.menu-top,
        #adminmenu li.current a.menu-top,
        #adminmenu li.wp-has-current-submenu a.wp-has-current-submenu {
            background: var(--modern-primary);
            color: #fff;
        }
        
        #adminmenu li a:hover {
            background-color: var(--modern-primary);
            color: #fff;
        }
        
        #adminmenu div.wp-menu-arrow {
            display: none;
        }
        
        /* Butonlar - Sadece stil */
        .wp-core-ui .button {
            border-radius: 4px;
            transition: all 0.2s ease;
            font-weight: 500;
        }
        
        .wp-core-ui .button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .wp-core-ui .button-primary {
            background: var(--modern-primary);
            border-color: var(--modern-primary);
            box-shadow: 0 2px 4px rgba(94, 114, 228, 0.3);
        }
        
        .wp-core-ui .button-primary:hover {
            background: #4c63d2;
            border-color: #4c63d2;
        }
        
        /* Input alanları */
        input[type="text"],
        input[type="password"],
        input[type="email"],
        input[type="number"],
        input[type="search"],
        select,
        textarea {
            border-radius: 4px;
            border-color: #ddd;
            transition: all 0.2s ease;
        }
        
        input[type="text"]:focus,
        input[type="password"]:focus,
        input[type="email"]:focus,
        input[type="number"]:focus,
        input[type="search"]:focus,
        select:focus,
        textarea:focus {
            border-color: var(--modern-primary);
            box-shadow: 0 0 0 1px var(--modern-primary);
        }
        
        /* Tablolar - Hafif dokunuş */
        .wp-list-table {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .wp-list-table thead th,
        .wp-list-table tfoot th {
            background: #f8f9fa;
            color: var(--modern-dark);
            font-weight: 600;
        }
        
        .wp-list-table tr:hover {
            background-color: #f8f9ff;
        }
        
        .wp-list-table .column-cb {
            padding: 8px 10px;
        }
        
        /* Postbox - Widget kutuları */
        .postbox {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .postbox .hndle {
            border-bottom: 1px solid #e5e7eb;
            background: #f8f9fa;
            border-radius: 8px 8px 0 0;
        }
        
        .postbox .handlediv .toggle-indicator {
            color: var(--modern-primary);
        }
        
        /* Bildirimler */
        div.notice {
            border-radius: 4px;
            border-left-width: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .notice-success {
            border-left-color: var(--modern-success);
            background-color: rgba(45, 206, 137, 0.05);
        }
        
        .notice-error {
            border-left-color: var(--modern-warning);
            background-color: rgba(251, 99, 64, 0.05);
        }
        
        .notice-warning {
            border-left-color: #f0ad4e;
            background-color: rgba(240, 173, 78, 0.05);
        }
        
        .notice-info {
            border-left-color: var(--modern-info);
            background-color: rgba(17, 205, 239, 0.05);
        }
        
        /* Dashboard widget başlıkları */
        #dashboard-widgets h2,
        #dashboard-widgets h3 {
            color: var(--modern-dark);
            font-weight: 600;
        }
        
        /* Media Modal */
        .media-modal-content {
            border-radius: 8px;
        }
        
        .media-frame-title h1 {
            color: var(--modern-dark);
        }
        
        /* Sadece hover efektleri */
        .subsubsub a:hover {
            color: var(--modern-primary);
        }
        
        .row-actions a:hover {
            color: var(--modern-primary);
        }
        
        /* Sayfalama */
        .tablenav .tablenav-pages a:hover,
        .tablenav .tablenav-pages a:focus {
            background: var(--modern-primary);
            color: #fff;
            border-color: var(--modern-primary);
        }
        
        /* Admin Footer - Minimal değişiklik */
        #wpfooter {
            color: #666;
            padding: 15px 20px;
        }
        
        /* Scroll bar - Opsiyonel */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #999;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #777;
        }
        
        /* Responsive düzenlemeler */
        @media screen and (max-width: 782px) {
            .wp-list-table {
                border-radius: 0;
            }
            
            .postbox {
                border-radius: 0;
            }
        }
    ');
}

// Opsiyonel: Admin footer metni (isterseniz silebilirsiniz)
add_filter('admin_footer_text', function() {
    return 'WordPress ' . get_bloginfo('version');
});

// Opsiyonel: Hoşgeldin paneli rengi (isterseniz silebilirsiniz)
add_action('admin_head', function() {
    echo '<style>
        .welcome-panel {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .welcome-panel-content h2 {
            color: var(--modern-dark);
        }
    </style>';
});

/**
 * Awards özet bilgilerini hazırla
 */
function gufte_prepare_awards_summary($awards) {
    $summary = array(
        'total' => count($awards),
        'winners' => 0,
        'nominees' => 0,
        'mentions' => 0
    );
    
    foreach ($awards as $award) {
        switch ($award['result']) {
            case 'winner':
                $summary['winners']++;
                break;
            case 'nominee':
                $summary['nominees']++;
                break;
            case 'honorable_mention':
                $summary['mentions']++;
                break;
        }
    }
    
    return $summary;
}

/**
 * Award result konfigürasyonları
 */
function gufte_get_award_result_config($result) {
    $configs = array(
        'winner' => array(
            'label' => __('Winner', 'gufte'),
            'icon' => 'mdi:trophy',
            'color' => '#059669',
            'gradient' => 'linear-gradient(135deg, #059669, #10b981)'
        ),
        'nominee' => array(
            'label' => __('Nominee', 'gufte'),
            'icon' => 'mdi:star',
            'color' => '#2563eb',
            'gradient' => 'linear-gradient(135deg, #2563eb, #3b82f6)'
        ),
        'honorable_mention' => array(
            'label' => __('Honorable Mention', 'gufte'),
            'icon' => 'mdi:medal',
            'color' => '#7c3aed',
            'gradient' => 'linear-gradient(135deg, #7c3aed, #8b5cf6)'
        )
    );
    
    return isset($configs[$result]) ? $configs[$result] : $configs['nominee'];
}

/**
 * Subscriber kullanıcılar için admin bar'ı gizle ve dashboard erişimini engelle
 */

// 1. Subscriber rolü için admin bar'ı gizle
add_action('after_setup_theme', 'gufte_hide_admin_bar_for_subscribers');
function gufte_hide_admin_bar_for_subscribers() {
    if (current_user_can('subscriber') && !current_user_can('edit_posts')) {
        show_admin_bar(false);
    }
}

// 2. CSS ile admin bar'ı tamamen gizle (ekstra güvenlik)
add_action('wp_head', 'gufte_hide_admin_bar_css');
function gufte_hide_admin_bar_css() {
    if (current_user_can('subscriber') && !current_user_can('edit_posts')) {
        echo '<style type="text/css">
            #wpadminbar { display: none !important; }
            html { margin-top: 0 !important; }
            * html body { margin-top: 0 !important; }
        </style>';
    }
}

// 3. Dashboard erişimini engelle - admin sayfalarına yönlendirme
add_action('admin_init', 'gufte_restrict_admin_access');
function gufte_restrict_admin_access() {
    // Eğer subscriber ise ve AJAX değilse
    if (current_user_can('subscriber') && !current_user_can('edit_posts') && !wp_doing_ajax()) {
        // Profile.php ve admin-ajax.php hariç tüm admin sayfalarını engelle
        global $pagenow;
        
        // İzin verilen sayfalar
        $allowed_pages = array(
            'profile.php',      // Profil düzenleme
            'admin-ajax.php',   // AJAX istekleri
            'admin-post.php'    // Form gönderimler
        );
        
        if (!in_array($pagenow, $allowed_pages)) {
            // Ana sayfaya yönlendir
            wp_redirect(home_url());
            exit;
        }
    }
}

// 4. Admin menüsünden gereksiz öğeleri kaldır (subscriber giriş yaparsa)
add_action('admin_menu', 'gufte_remove_admin_menu_items_for_subscribers');
function gufte_remove_admin_menu_items_for_subscribers() {
    if (current_user_can('subscriber') && !current_user_can('edit_posts')) {
        // Dashboard
        remove_menu_page('index.php');
        
        // Posts
        remove_menu_page('edit.php');
        
        // Media
        remove_menu_page('upload.php');
        
        // Pages
        remove_menu_page('edit.php?post_type=page');
        
        // Comments
        remove_menu_page('edit-comments.php');
        
        // Appearance
        remove_menu_page('themes.php');
        
        // Plugins
        remove_menu_page('plugins.php');
        
        // Users
        remove_menu_page('users.php');
        
        // Tools
        remove_menu_page('tools.php');
        
        // Settings
        remove_menu_page('options-general.php');
    }
}

// 5. WordPress admin bar linklerini temizle
add_action('wp_before_admin_bar_render', 'gufte_remove_admin_bar_links');
function gufte_remove_admin_bar_links() {
    if (current_user_can('subscriber') && !current_user_can('edit_posts')) {
        global $wp_admin_bar;
        
        // Admin bar'dan linkleri kaldır
        $wp_admin_bar->remove_menu('dashboard');
        $wp_admin_bar->remove_menu('new-content');
        $wp_admin_bar->remove_menu('edit');
        $wp_admin_bar->remove_menu('comments');
        $wp_admin_bar->remove_menu('customize');
        $wp_admin_bar->remove_menu('updates');
    }
}

// 6. wp-admin URL'lerine direkt erişimi engelle
add_action('init', 'gufte_prevent_wp_admin_access');
function gufte_prevent_wp_admin_access() {
    // Admin sayfasında ve subscriber ise
    if (is_admin() && current_user_can('subscriber') && !current_user_can('edit_posts') && !wp_doing_ajax()) {
        global $pagenow;
        
        // İzin verilen sayfalar
        $allowed_pages = array(
            'profile.php',
            'admin-ajax.php',
            'admin-post.php'
        );
        
        if (!in_array($pagenow, $allowed_pages)) {
            wp_redirect(home_url());
            exit;
        }
    }
}

// 7. Login sonrası yönlendirme - subscriber'ları dashboard'a değil ana sayfaya yönlendir
add_filter('login_redirect', 'gufte_login_redirect_for_subscribers', 10, 3);
function gufte_login_redirect_for_subscribers($redirect_to, $request, $user) {
    // Eğer user objesi varsa ve subscriber ise
    if (isset($user->roles) && is_array($user->roles)) {
        if (in_array('subscriber', $user->roles) && !user_can($user, 'edit_posts')) {
            return home_url();
        }
    }
    
    return $redirect_to;
}

// 8. Dashboard widget'larını gizle (eğer subscriber bir şekilde dashboard'a erişirse)
add_action('wp_dashboard_setup', 'gufte_remove_dashboard_widgets_for_subscribers');
function gufte_remove_dashboard_widgets_for_subscribers() {
    if (current_user_can('subscriber') && !current_user_can('edit_posts')) {
        global $wp_meta_boxes;
        
        // Tüm dashboard widget'larını kaldır
        unset($wp_meta_boxes['dashboard']['normal']['core']);
        unset($wp_meta_boxes['dashboard']['side']['core']);
        unset($wp_meta_boxes['dashboard']['normal']['high']);
        unset($wp_meta_boxes['dashboard']['side']['high']);
    }
}

// 9. Admin footer'ı temizle
add_filter('admin_footer_text', 'gufte_remove_admin_footer_for_subscribers');
function gufte_remove_admin_footer_for_subscribers($text) {
    if (current_user_can('subscriber') && !current_user_can('edit_posts')) {
        return '';
    }
    return $text;
}

// 10. Ek güvenlik: REST API erişimini sınırla (isteğe bağlı)
add_filter('rest_authentication_errors', 'gufte_restrict_rest_api_for_subscribers');
function gufte_restrict_rest_api_for_subscribers($result) {
    if (!is_user_logged_in()) {
        return $result;
    }
    
    if (current_user_can('subscriber') && !current_user_can('edit_posts')) {
        // Sadece okuma işlemlerine izin ver
        if (!in_array($_SERVER['REQUEST_METHOD'], array('GET', 'HEAD'))) {
            return new WP_Error(
                'rest_forbidden',
                __('You do not have permission to perform this action.', 'gufte'),
                array('status' => 403)
            );
        }
    }
    
    return $result;
}
/**
 * Şarkıcı Awards Entegrasyonu
 * 
 * Bu fonksiyonları functions.php dosyanıza ekleyin
 */

/**
 * Şarkıcının ödül istatistiklerini getir
 */
function gufte_get_singer_awards_stats($singer_term_id) {
    // Şarkıcının şarkılarını al
    $singer_songs = get_posts(array(
        'post_type' => 'post',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'tax_query' => array(
            array(
                'taxonomy' => 'singer',
                'field' => 'term_id',
                'terms' => $singer_term_id,
            ),
        ),
        'meta_query' => array(
            array(
                'key' => '_award_results',
                'compare' => 'EXISTS'
            )
        )
    ));

    $total_awards = 0;
    $total_wins = 0;
    $award_breakdown = array();
    
    foreach ($singer_songs as $song_id) {
        $award_results = get_post_meta($song_id, '_award_results', true);
        if (!is_array($award_results)) continue;
        
        foreach ($award_results as $key => $value) {
            // _notes alanlarını skip et
            if (strpos($key, '_notes') !== false) continue;
            
            $total_awards++;
            
            if ($value === 'winner') {
                $total_wins++;
            }
            
            // Award tipine göre breakdown
            $award_term = get_term($key, 'awards');
            if ($award_term && !is_wp_error($award_term)) {
                $award_type = get_term_meta($key, 'award_type', true);
                if (!isset($award_breakdown[$award_type])) {
                    $award_breakdown[$award_type] = array('total' => 0, 'wins' => 0);
                }
                $award_breakdown[$award_type]['total']++;
                if ($value === 'winner') {
                    $award_breakdown[$award_type]['wins']++;
                }
            }
        }
    }
    
    return array(
        'total_awards' => $total_awards,
        'total_wins' => $total_wins,
        'award_breakdown' => $award_breakdown
    );
}

/**
 * Şarkıcının detaylı ödül listesini getir
 */
function gufte_get_singer_awards_detailed($singer_term_id) {
    // Şarkıcının şarkılarını al
    $singer_songs = get_posts(array(
        'post_type' => 'post',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'singer',
                'field' => 'term_id',
                'terms' => $singer_term_id,
            ),
        ),
        'meta_query' => array(
            array(
                'key' => '_award_results',
                'compare' => 'EXISTS'
            )
        )
    ));

    $awards_list = array();
    
    foreach ($singer_songs as $song_id) {
        $song_title = get_the_title($song_id);
        $award_results = get_post_meta($song_id, '_award_results', true);
        if (!is_array($award_results)) continue;
        
        foreach ($award_results as $key => $value) {
            // _notes alanlarını skip et
            if (strpos($key, '_notes') !== false) continue;
            
            $award_term = get_term($key, 'awards');
            if (!$award_term || is_wp_error($award_term)) continue;
            
            $notes = isset($award_results[$key . '_notes']) ? $award_results[$key . '_notes'] : '';
            
            // Hiyerarşi bilgilerini al
            $hierarchy_info = gufte_parse_award_hierarchy($award_term);
            $award_type = get_term_meta($key, 'award_type', true);
            
            $awards_list[] = array(
                'song_id' => $song_id,
                'song_title' => $song_title,
                'award_term' => $award_term,
                'result' => $value,
                'notes' => $notes,
                'year' => $hierarchy_info['year'],
                'organization' => $hierarchy_info['organization'],
                'type' => $award_type,
                'full_name' => gufte_get_full_award_hierarchy($award_term)
            );
        }
    }
    
    // Yıla göre sırala (yeniden eskiye)
    usort($awards_list, function($a, $b) {
        $ya = isset($a['year']) ? (int) preg_replace('/\D+/', '', $a['year']) : 0;
        $yb = isset($b['year']) ? (int) preg_replace('/\D+/', '', $b['year']) : 0;
        return $yb <=> $ya;
    });
    
    return $awards_list;
}

/**
 * Şarkıcı kartı için ödül badge'i render et
 */
function gufte_render_singer_awards_badge($awards_stats) {
    if (!isset($awards_stats['total_awards']) || $awards_stats['total_awards'] == 0) {
        return '';
    }
    
    $total_awards = $awards_stats['total_awards'];
    $total_wins = $awards_stats['total_wins'];
    
    if ($total_wins > 0) {
        return '<div class="absolute top-2 right-2 bg-gradient-to-r from-yellow-400 to-amber-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow-lg z-10">
                    🏆 ' . $total_wins . '
                </div>';
    } else {
        return '<div class="absolute top-2 right-2 bg-gray-600 text-white text-xs font-bold px-2 py-1 rounded-full shadow-lg z-10">
                    🎯 ' . $total_awards . '
                </div>';
    }
}

/**
 * Şarkıcı kartı için ödül tooltip'i render et
 */
function gufte_render_singer_awards_tooltip($awards_stats) {
    if (!isset($awards_stats['total_awards']) || $awards_stats['total_awards'] == 0) {
        return '';
    }
    
    $total_awards = $awards_stats['total_awards'];
    $total_wins = $awards_stats['total_wins'];
    
    return '<div class="absolute top-12 right-2 bg-black text-white text-xs px-2 py-1 rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-20 whitespace-nowrap">
                ' . sprintf(__('%d Awards, %d Wins', 'gufte'), $total_awards, $total_wins) . '
            </div>';
}

/**
 * Şarkıcı sayfası için ödül bölümünü render et
 */
function gufte_render_singer_awards_section($singer_term_id) {
    $awards_stats = gufte_get_singer_awards_stats($singer_term_id);
    
    if ($awards_stats['total_awards'] == 0) {
        return '';
    }
    
    $awards_detailed = gufte_get_singer_awards_detailed($singer_term_id);
    
    ob_start();
    ?>
    <div class="singer-awards mt-6 border-t border-gray-100 pt-4">
        <h3 class="text-lg font-semibold text-gray-700 mb-3 flex items-center">
            <span class="iconify mr-2 text-primary-600" data-icon="mdi:trophy-variant"></span>
            <?php esc_html_e('Awards & Nominations', 'gufte'); ?>
            <span class="ml-2 bg-primary-100 text-primary-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                <?php echo esc_html($awards_stats['total_awards']); ?>
            </span>
        </h3>
        
        <!-- Awards İstatistikleri -->
        <div class="awards-stats grid grid-cols-3 gap-4 mb-6">
            <div class="stat-card bg-gradient-to-r from-yellow-50 to-amber-50 border border-yellow-200 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-yellow-700"><?php echo esc_html($awards_stats['total_wins']); ?></div>
                <div class="text-sm text-yellow-600"><?php esc_html_e('Wins', 'gufte'); ?></div>
            </div>
            <div class="stat-card bg-gradient-to-r from-gray-50 to-slate-50 border border-gray-200 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-gray-700"><?php echo esc_html($awards_stats['total_awards'] - $awards_stats['total_wins']); ?></div>
                <div class="text-sm text-gray-600"><?php esc_html_e('Nominations', 'gufte'); ?></div>
            </div>
            <div class="stat-card bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-blue-700"><?php echo esc_html($awards_stats['total_awards']); ?></div>
                <div class="text-sm text-blue-600"><?php esc_html_e('Total', 'gufte'); ?></div>
            </div>
        </div>
        
        <!-- Awards Listesi -->
        <?php if (!empty($awards_detailed)) : ?>
        <div class="awards-list space-y-3">
            <?php 
            $grouped_awards = array();
            foreach ($awards_detailed as $award) {
                $year = $award['year'] ?: 'Unknown';
                if (!isset($grouped_awards[$year])) {
                    $grouped_awards[$year] = array();
                }
                $grouped_awards[$year][] = $award;
            }
            
            foreach ($grouped_awards as $year => $year_awards) : ?>
                <div class="year-group">
                    <h4 class="text-sm font-semibold text-gray-600 mb-2 flex items-center">
                        <span class="iconify mr-1" data-icon="mdi:calendar"></span>
                        <?php echo esc_html($year); ?>
                    </h4>
                    <div class="space-y-2 ml-4">
                        <?php foreach ($year_awards as $award) : 
                            $result_class = '';
                            $result_icon = '';
                            $result_text = '';
                            
                            switch ($award['result']) {
                                case 'winner':
                                    $result_class = 'bg-yellow-50 border-yellow-200 text-yellow-800';
                                    $result_icon = '🏆';
                                    $result_text = __('Winner', 'gufte');
                                    break;
                                case 'nominee':
                                    $result_class = 'bg-gray-50 border-gray-200 text-gray-800';
                                    $result_icon = '🎯';
                                    $result_text = __('Nominee', 'gufte');
                                    break;
                                case 'honorable_mention':
                                    $result_class = 'bg-blue-50 border-blue-200 text-blue-800';
                                    $result_icon = '⭐';
                                    $result_text = __('Honorable Mention', 'gufte');
                                    break;
                            }
                        ?>
                        <div class="award-item <?php echo esc_attr($result_class); ?> border rounded-lg p-3">
                            <div class="flex items-start justify-between">
                                <div class="flex-grow">
                                    <div class="flex items-center mb-1">
                                        <span class="mr-2"><?php echo $result_icon; ?></span>
                                        <span class="font-medium text-sm"><?php echo esc_html($result_text); ?></span>
                                        <?php if (!empty($award['notes'])) : ?>
                                            <span class="ml-2 text-xs opacity-75">(<?php echo esc_html($award['notes']); ?>)</span>
                                        <?php endif; ?>
                                    </div>
                                    <h5 class="font-semibold text-sm mb-1"><?php echo esc_html($award['full_name']); ?></h5>
                                    <div class="text-xs opacity-75">
                                        <?php esc_html_e('For:', 'gufte'); ?> 
                                        <a href="<?php echo esc_url(get_permalink($award['song_id'])); ?>" class="hover:underline font-medium">
                                            <?php echo esc_html($award['song_title']); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
/**
 * Şarkıcı Sosyal Medya Profilleri Entegrasyonu
 * Bu kodu functions.php dosyanıza ekleyin
 */

/**
 * Şarkıcı taksonomisine sosyal medya alanları ekle
 */
function gufte_register_singer_social_media_fields() {
    // Instagram
    register_term_meta('singer', 'instagram_url', array(
        'type' => 'string',
        'description' => 'Instagram profile URL',
        'single' => true,
        'show_in_rest' => true,
    ));
    
    // Twitter/X
    register_term_meta('singer', 'twitter_url', array(
        'type' => 'string',
        'description' => 'Twitter/X profile URL',
        'single' => true,
        'show_in_rest' => true,
    ));
    
    // Facebook
    register_term_meta('singer', 'facebook_url', array(
        'type' => 'string',
        'description' => 'Facebook page URL',
        'single' => true,
        'show_in_rest' => true,
    ));
    
    // TikTok
    register_term_meta('singer', 'tiktok_url', array(
        'type' => 'string',
        'description' => 'TikTok profile URL',
        'single' => true,
        'show_in_rest' => true,
    ));
    
    // YouTube Kanalı
    register_term_meta('singer', 'youtube_channel_url', array(
        'type' => 'string',
        'description' => 'YouTube channel URL',
        'single' => true,
        'show_in_rest' => true,
    ));
    
    // Official Website
    register_term_meta('singer', 'official_website_url', array(
        'type' => 'string',
        'description' => 'Official website URL',
        'single' => true,
        'show_in_rest' => true,
    ));
}
add_action('init', 'gufte_register_singer_social_media_fields');

/**
 * Şarkıcı düzenleme formuna sosyal medya alanları ekle
 */
function gufte_add_singer_social_media_edit_fields($term) {
    if ('singer' !== $term->taxonomy) {
        return;
    }
    
    // Mevcut değerleri al
    $instagram_url = get_term_meta($term->term_id, 'instagram_url', true);
    $twitter_url = get_term_meta($term->term_id, 'twitter_url', true);
    $facebook_url = get_term_meta($term->term_id, 'facebook_url', true);
    $tiktok_url = get_term_meta($term->term_id, 'tiktok_url', true);
    $youtube_channel_url = get_term_meta($term->term_id, 'youtube_channel_url', true);
    $official_website_url = get_term_meta($term->term_id, 'official_website_url', true);
    ?>
    
    <!-- Sosyal Medya Profilleri Başlık -->
    <tr class="form-field">
        <th colspan="2">
            <h3 style="margin: 20px 0 10px 0; padding: 10px 0; border-bottom: 2px solid #ddd;">
                📱 Sosyal Medya Profilleri
            </h3>
        </th>
    </tr>
    
    <!-- Instagram -->
    <tr class="form-field">
        <th scope="row">
            <label for="instagram_url">
                <span style="color: #E4405F;">📷</span> Instagram
            </label>
        </th>
        <td>
            <input type="url" name="instagram_url" id="instagram_url" 
                   value="<?php echo esc_attr($instagram_url); ?>" 
                   style="width: 95%;" 
                   placeholder="https://www.instagram.com/username/" />
            <p class="description">Sanatçının Instagram profil URL'si</p>
        </td>
    </tr>
    
    <!-- Twitter/X -->
    <tr class="form-field">
        <th scope="row">
            <label for="twitter_url">
                <span style="color: #1DA1F2;">🐦</span> Twitter/X
            </label>
        </th>
        <td>
            <input type="url" name="twitter_url" id="twitter_url" 
                   value="<?php echo esc_attr($twitter_url); ?>" 
                   style="width: 95%;" 
                   placeholder="https://twitter.com/username" />
            <p class="description">Sanatçının Twitter/X profil URL'si</p>
        </td>
    </tr>
    
    <!-- Facebook -->
    <tr class="form-field">
        <th scope="row">
            <label for="facebook_url">
                <span style="color: #1877F2;">📘</span> Facebook
            </label>
        </th>
        <td>
            <input type="url" name="facebook_url" id="facebook_url" 
                   value="<?php echo esc_attr($facebook_url); ?>" 
                   style="width: 95%;" 
                   placeholder="https://www.facebook.com/username" />
            <p class="description">Sanatçının Facebook sayfası URL'si</p>
        </td>
    </tr>
    
    <!-- TikTok -->
    <tr class="form-field">
        <th scope="row">
            <label for="tiktok_url">
                <span style="color: #FF0050;">🎵</span> TikTok
            </label>
        </th>
        <td>
            <input type="url" name="tiktok_url" id="tiktok_url" 
                   value="<?php echo esc_attr($tiktok_url); ?>" 
                   style="width: 95%;" 
                   placeholder="https://www.tiktok.com/@username" />
            <p class="description">Sanatçının TikTok profil URL'si</p>
        </td>
    </tr>
    
    <!-- YouTube Kanalı -->
    <tr class="form-field">
        <th scope="row">
            <label for="youtube_channel_url">
                <span style="color: #FF0000;">📺</span> YouTube Kanalı
            </label>
        </th>
        <td>
            <input type="url" name="youtube_channel_url" id="youtube_channel_url" 
                   value="<?php echo esc_attr($youtube_channel_url); ?>" 
                   style="width: 95%;" 
                   placeholder="https://www.youtube.com/@username" />
            <p class="description">Sanatçının YouTube kanalı URL'si</p>
        </td>
    </tr>
    
    <!-- Resmi Website -->
    <tr class="form-field">
        <th scope="row">
            <label for="official_website_url">
                <span style="color: #666666;">🌐</span> Resmi Website
            </label>
        </th>
        <td>
            <input type="url" name="official_website_url" id="official_website_url" 
                   value="<?php echo esc_attr($official_website_url); ?>" 
                   style="width: 95%;" 
                   placeholder="https://www.artistname.com" />
            <p class="description">Sanatçının resmi web sitesi URL'si</p>
        </td>
    </tr>
    
    <?php
}
add_action('singer_edit_form_fields', 'gufte_add_singer_social_media_edit_fields', 25);

/**
 * Yeni şarkıcı ekleme formuna sosyal medya alanları ekle
 */
function gufte_add_new_singer_social_media_fields() {
    ?>
    <div class="form-field">
        <h3>📱 Sosyal Medya Profilleri</h3>
    </div>
    
    <div class="form-field">
        <label for="instagram_url">
            <span style="color: #E4405F;">📷</span> Instagram
        </label>
        <input type="url" name="instagram_url" id="instagram_url" 
               placeholder="https://www.instagram.com/username/" />
        <p>Sanatçının Instagram profil URL'si</p>
    </div>
    
    <div class="form-field">
        <label for="twitter_url">
            <span style="color: #1DA1F2;">🐦</span> Twitter/X
        </label>
        <input type="url" name="twitter_url" id="twitter_url" 
               placeholder="https://twitter.com/username" />
        <p>Sanatçının Twitter/X profil URL'si</p>
    </div>
    
    <div class="form-field">
        <label for="facebook_url">
            <span style="color: #1877F2;">📘</span> Facebook
        </label>
        <input type="url" name="facebook_url" id="facebook_url" 
               placeholder="https://www.facebook.com/username" />
        <p>Sanatçının Facebook sayfası URL'si</p>
    </div>
    
    <div class="form-field">
        <label for="tiktok_url">
            <span style="color: #FF0050;">🎵</span> TikTok
        </label>
        <input type="url" name="tiktok_url" id="tiktok_url" 
               placeholder="https://www.tiktok.com/@username" />
        <p>Sanatçının TikTok profil URL'si</p>
    </div>
    
    <div class="form-field">
        <label for="youtube_channel_url">
            <span style="color: #FF0000;">📺</span> YouTube Kanalı
        </label>
        <input type="url" name="youtube_channel_url" id="youtube_channel_url" 
               placeholder="https://www.youtube.com/@username" />
        <p>Sanatçının YouTube kanalı URL'si</p>
    </div>
    
    <div class="form-field">
        <label for="official_website_url">
            <span style="color: #666666;">🌐</span> Resmi Website
        </label>
        <input type="url" name="official_website_url" id="official_website_url" 
               placeholder="https://www.artistname.com" />
        <p>Sanatçının resmi web sitesi URL'si</p>
    </div>
    <?php
}
add_action('singer_add_form_fields', 'gufte_add_new_singer_social_media_fields', 25);

/**
 * Sosyal medya bilgilerini kaydet
 */
function gufte_save_singer_social_media_fields($term_id) {
    // Instagram
    if (isset($_POST['instagram_url'])) {
        update_term_meta($term_id, 'instagram_url', 
            sanitize_url($_POST['instagram_url']));
    }
    
    // Twitter
    if (isset($_POST['twitter_url'])) {
        update_term_meta($term_id, 'twitter_url', 
            sanitize_url($_POST['twitter_url']));
    }
    
    // Facebook
    if (isset($_POST['facebook_url'])) {
        update_term_meta($term_id, 'facebook_url', 
            sanitize_url($_POST['facebook_url']));
    }
    
    // TikTok
    if (isset($_POST['tiktok_url'])) {
        update_term_meta($term_id, 'tiktok_url', 
            sanitize_url($_POST['tiktok_url']));
    }
    
    // YouTube Kanalı
    if (isset($_POST['youtube_channel_url'])) {
        update_term_meta($term_id, 'youtube_channel_url', 
            sanitize_url($_POST['youtube_channel_url']));
    }
    
    // Resmi Website
    if (isset($_POST['official_website_url'])) {
        update_term_meta($term_id, 'official_website_url', 
            sanitize_url($_POST['official_website_url']));
    }
}
add_action('created_singer', 'gufte_save_singer_social_media_fields');
add_action('edited_singer', 'gufte_save_singer_social_media_fields');

/**
 * Helper function - Şarkıcının sosyal medya linklerini getir
 */
function gufte_get_singer_social_media_links($term_id) {
    return array(
        'instagram' => get_term_meta($term_id, 'instagram_url', true),
        'twitter' => get_term_meta($term_id, 'twitter_url', true),
        'facebook' => get_term_meta($term_id, 'facebook_url', true),
        'tiktok' => get_term_meta($term_id, 'tiktok_url', true),
        'youtube_channel' => get_term_meta($term_id, 'youtube_channel_url', true),
        'official_website' => get_term_meta($term_id, 'official_website_url', true)
    );
}

/**
 * Şarkıcı sayfalarında sosyal medya linklerini göster
 * Bunu taxonomy-singer.php dosyasında kullanabilirsiniz
 */
function gufte_render_singer_social_media($term_id) {
    $social_media_links = gufte_get_singer_social_media_links($term_id);
    
    // En az bir sosyal medya linki var mı kontrol et
    $has_social_links = false;
    foreach ($social_media_links as $platform => $url) {
        if (!empty($url)) {
            $has_social_links = true;
            break;
        }
    }
    
    if (!$has_social_links) {
        return '';
    }
    
    ob_start();
    ?>
    <div class="singer-social-media mt-6 pt-4 border-t border-gray-100">
        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
            <span class="iconify mr-2 text-primary-600" data-icon="mdi:share-variant"></span>
            <?php esc_html_e('Follow the Artist', 'gufte'); ?>
        </h4>
        <div class="flex flex-wrap gap-3">
            <?php 
            // Instagram
            if (!empty($social_media_links['instagram'])) : ?>
                <a href="<?php echo esc_url($social_media_links['instagram']); ?>" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="inline-flex items-center px-3 py-2 bg-gradient-to-r from-purple-500 via-pink-500 to-red-500 hover:from-purple-600 hover:via-pink-600 hover:to-red-600 text-white text-xs font-medium rounded-full transition-all duration-300 hover:scale-105">
                    <span class="iconify mr-1.5" data-icon="mdi:instagram"></span>
                    Instagram
                </a>
            <?php endif; ?>
            
            <?php 
            // Twitter/X
            if (!empty($social_media_links['twitter'])) : ?>
                <a href="<?php echo esc_url($social_media_links['twitter']); ?>" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="inline-flex items-center px-3 py-2 bg-black hover:bg-gray-800 text-white text-xs font-medium rounded-full transition-all duration-300 hover:scale-105">
                    <span class="iconify mr-1.5" data-icon="mdi:twitter"></span>
                    X/Twitter
                </a>
            <?php endif; ?>
            
            <?php 
            // Facebook
            if (!empty($social_media_links['facebook'])) : ?>
                <a href="<?php echo esc_url($social_media_links['facebook']); ?>" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="inline-flex items-center px-3 py-2 bg-[#1877F2] hover:bg-[#166FE5] text-white text-xs font-medium rounded-full transition-all duration-300 hover:scale-105">
                    <span class="iconify mr-1.5" data-icon="mdi:facebook"></span>
                    Facebook
                </a>
            <?php endif; ?>
            
            <?php 
            // TikTok
            if (!empty($social_media_links['tiktok'])) : ?>
                <a href="<?php echo esc_url($social_media_links['tiktok']); ?>" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="inline-flex items-center px-3 py-2 bg-black hover:bg-gray-800 text-white text-xs font-medium rounded-full transition-all duration-300 hover:scale-105">
                    <span class="iconify mr-1.5" data-icon="ic:baseline-tiktok"></span>
                    TikTok
                </a>
            <?php endif; ?>
            
            <?php 
            // YouTube Kanalı
            if (!empty($social_media_links['youtube_channel'])) : ?>
                <a href="<?php echo esc_url($social_media_links['youtube_channel']); ?>" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="inline-flex items-center px-3 py-2 bg-[#FF0000] hover:bg-[#CC0000] text-white text-xs font-medium rounded-full transition-all duration-300 hover:scale-105">
                    <span class="iconify mr-1.5" data-icon="mdi:youtube"></span>
                    YouTube
                </a>
            <?php endif; ?>
            
            <?php 
            // Resmi Website
            if (!empty($social_media_links['official_website'])) : ?>
                <a href="<?php echo esc_url($social_media_links['official_website']); ?>" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="inline-flex items-center px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-xs font-medium rounded-full transition-all duration-300 hover:scale-105">
                    <span class="iconify mr-1.5" data-icon="mdi:web"></span>
                    Website
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Şarkıcı listesi sayfasında da sosyal medya ikonlarını göster (opsiyonel)
 * Bu kodu page-singers.php içinde kullanabilirsiniz
 */
function gufte_render_singer_social_icons($term_id, $compact = false) {
    $social_media_links = gufte_get_singer_social_media_links($term_id);
    
    // En az bir sosyal medya linki var mı kontrol et
    $has_social_links = false;
    foreach ($social_media_links as $platform => $url) {
        if (!empty($url)) {
            $has_social_links = true;
            break;
        }
    }
    
    if (!$has_social_links) {
        return '';
    }
    
    if ($compact) {
        // Compact görünüm - sadece ikonlar
        ob_start();
        ?>
        <div class="singer-social-icons flex items-center gap-1 mt-2">
            <?php foreach ($social_media_links as $platform => $url) : 
                if (empty($url)) continue;
                
                $platform_config = array(
                    'instagram' => array('icon' => 'mdi:instagram', 'color' => 'text-pink-500'),
                    'twitter' => array('icon' => 'mdi:twitter', 'color' => 'text-black'),
                    'facebook' => array('icon' => 'mdi:facebook', 'color' => 'text-blue-600'),
                    'tiktok' => array('icon' => 'ic:baseline-tiktok', 'color' => 'text-black'),
                    'youtube_channel' => array('icon' => 'mdi:youtube', 'color' => 'text-red-500'),
                    'official_website' => array('icon' => 'mdi:web', 'color' => 'text-gray-600')
                );
                
                if (!isset($platform_config[$platform])) continue;
                $config = $platform_config[$platform];
            ?>
                <a href="<?php echo esc_url($url); ?>" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="<?php echo $config['color']; ?> hover:scale-110 transition-transform">
                    <span class="iconify" data-icon="<?php echo $config['icon']; ?>" style="width: 16px; height: 16px;"></span>
                </a>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    } else {
        // Normal görünüm
        return gufte_render_singer_social_media($term_id);
    }
}

/**
 * Set user's preferred locale based on their language preference
 * This is the WordPress standard way to handle per-user language preferences
 *
 * @param string $locale The current locale
 * @return string Modified locale based on user preference
 */
function gufte_set_user_locale($locale) {
    // Only apply for logged-in users
    if (!is_user_logged_in()) {
        return $locale;
    }

    // Get user's language preference
    $user_id = get_current_user_id();
    $user_language = get_user_meta($user_id, 'user_language', true);

    // Map our simple language codes to WordPress locales
    $language_map = array(
        'en' => 'en_US',
        'tr' => 'tr_TR',
    );

    // Return the mapped locale if it exists, otherwise return original
    if (!empty($user_language) && isset($language_map[$user_language])) {
        return $language_map[$user_language];
    }

    return $locale;
}
add_filter('locale', 'gufte_set_user_locale');

/**
 * Reject comments containing links or adult/promotional keywords.
 */
function gufte_reject_spammy_comment_content($commentdata) {
    if (empty($commentdata['comment_content']) || !empty($commentdata['comment_type']) && 'comment' !== $commentdata['comment_type']) {
        return $commentdata;
    }

    $content = $commentdata['comment_content'];

    $patterns = apply_filters('gufte_comment_block_patterns', array(
        '/https?:\/\//i',
        '/www\./i',
        '/\bsex\b/i',
        '/\bporn\b/i',
        '/\bcasino\b/i',
        '/\bviagra\b/i',
        '/\badult\b/i'
    ));

    foreach ($patterns as $pattern) {
        if (@preg_match($pattern, '') === false) {
            continue;
        }

        if (preg_match($pattern, $content)) {
            wp_die(
                esc_html__('Your comment appears to contain links or inappropriate content. Please remove them and try again.', 'gufte'),
                esc_html__('Comment Blocked', 'gufte'),
                array('back_link' => true)
            );
        }
    }

    return $commentdata;
}
add_filter('preprocess_comment', 'gufte_reject_spammy_comment_content');

function gufte_submit_translation_feedback() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => esc_html__('You must be logged in to submit feedback.', 'gufte')), 401);
    }

    check_ajax_referer('gufte_feedback_nonce', 'nonce');

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $line_id = isset($_POST['line_id']) ? sanitize_text_field(wp_unslash($_POST['line_id'])) : '';
    $lang_slug = isset($_POST['lang']) ? sanitize_title(wp_unslash($_POST['lang'])) : '';
    $feedback_type = isset($_POST['feedback_type']) && 'negative' === wp_unslash($_POST['feedback_type']) ? 'negative' : 'positive';
    $line_text = isset($_POST['line_text']) ? wp_strip_all_tags(wp_unslash($_POST['line_text'])) : '';

    if (!$post_id || empty($line_id)) {
        wp_send_json_error(array('message' => esc_html__('Invalid feedback payload.', 'gufte')));
    }

    $post = get_post($post_id);
    if (!$post || 'post' !== $post->post_type || 'publish' !== $post->post_status) {
        wp_send_json_error(array('message' => esc_html__('The referenced translation could not be found.', 'gufte')));
    }

    $user_id = get_current_user_id();
    $user = wp_get_current_user();
    $display_name = $user->display_name ? $user->display_name : $user->user_login;

    $meta = get_post_meta($post_id, '_gufte_translation_feedback', true);
    if (!is_array($meta)) {
        $meta = array(
            'counts' => array('positive' => 0, 'negative' => 0),
            'per_lang' => array(),
            'user_history' => array(),
            'entries' => array(),
        );
    }

    if (!isset($meta['counts']['positive'], $meta['counts']['negative'])) {
        $meta['counts'] = array_merge(array('positive' => 0, 'negative' => 0), (array) $meta['counts']);
    }

    if (empty($lang_slug)) {
        $lang_slug = 'general';
    }

    if (!isset($meta['per_lang'][$lang_slug])) {
        $meta['per_lang'][$lang_slug] = array('positive' => 0, 'negative' => 0);
    }

    if (!isset($meta['user_history']) || !is_array($meta['user_history'])) {
        $meta['user_history'] = array();
    }

    $history_key = $user_id . '|' . $lang_slug . '|' . $line_id;
    if (isset($meta['user_history'][$history_key])) {
        $previous = $meta['user_history'][$history_key];
        if ($previous === $feedback_type) {
            wp_send_json_error(array('message' => esc_html__('You already gave feedback for this line.', 'gufte')));
        }

        if (isset($meta['counts'][$previous]) && $meta['counts'][$previous] > 0) {
            $meta['counts'][$previous]--;
        }
        if (isset($meta['per_lang'][$lang_slug][$previous]) && $meta['per_lang'][$lang_slug][$previous] > 0) {
            $meta['per_lang'][$lang_slug][$previous]--;
        }
    }

    $meta['counts'][$feedback_type] = isset($meta['counts'][$feedback_type]) ? $meta['counts'][$feedback_type] + 1 : 1;
    $meta['per_lang'][$lang_slug][$feedback_type] = isset($meta['per_lang'][$lang_slug][$feedback_type]) ? $meta['per_lang'][$lang_slug][$feedback_type] + 1 : 1;
    $meta['user_history'][$history_key] = $feedback_type;

    if (!isset($meta['entries']) || !is_array($meta['entries'])) {
        $meta['entries'] = array();
    }

    $entry_payload = array(
        'user_id' => $user_id,
        'user_display' => $display_name,
        'line_id' => $line_id,
        'lang' => $lang_slug,
        'type' => $feedback_type,
        'excerpt' => wp_html_excerpt($line_text, 160, '...'),
        'time' => current_time('timestamp'),
    );
    $meta['entries'][] = $entry_payload;

    if (count($meta['entries']) > 50) {
        $meta['entries'] = array_slice($meta['entries'], -50);
    }

    update_post_meta($post_id, '_gufte_translation_feedback', $meta);

    if (function_exists('gufte_store_feedback_entry')) {
        gufte_store_feedback_entry($post_id, $entry_payload);
    }

    $message = ('positive' === $feedback_type)
        ? esc_html__('Thanks for confirming the translation!', 'gufte')
        : esc_html__('Thanks for letting us know. We will review this translation.', 'gufte');

    wp_send_json_success(array(
        'message' => $message,
        'counts'  => $meta['counts'],
        'lang'    => $meta['per_lang'][$lang_slug],
    ));
}
add_action('wp_ajax_gufte_submit_translation_feedback', 'gufte_submit_translation_feedback');
add_action('wp_ajax_nopriv_gufte_submit_translation_feedback', function () {
    wp_send_json_error(array('message' => esc_html__('Please log in to submit feedback.', 'gufte')), 401);
});

/**
 * Render search modal markup in the footer so it sits above all content.
 */
function gufte_render_search_modal() {
    if (is_admin()) {
        return;
    }

    $recent_searches = gufte_get_recent_searches();
    $recent_visits   = gufte_get_recent_visits();
    $has_history     = !empty($recent_searches) || !empty($recent_visits);
    ?>
    <div class="search-modal fixed inset-0 z-[9999] hidden flex items-center justify-center min-h-screen px-3 py-6 sm:px-4 sm:py-10" data-search-modal role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="searchModalTitle" tabindex="-1">
        <div class="absolute inset-0 z-[100] bg-gray-900/70 backdrop-blur-sm transition-opacity" data-search-modal-close></div>
        <div class="relative z-[120] w-full max-w-3xl rounded-2xl bg-white shadow-2xl ring-1 ring-gray-100 max-h-[calc(100vh-2rem)] overflow-hidden flex flex-col">
                <div class="flex items-start justify-between border-b border-gray-100 px-6 py-4">
                    <div>
                        <h2 id="searchModalTitle" class="text-lg font-semibold text-gray-900"><?php esc_html_e('Search the lyrics catalog', 'gufte'); ?></h2>
                        <p class="mt-1 text-sm text-gray-500"><?php esc_html_e('Find songs, albums, singers or translations in seconds.', 'gufte'); ?></p>
                    </div>
                    <button type="button" class="flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:text-primary-600 hover:border-primary-400 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500" data-search-modal-close aria-label="<?php esc_attr_e('Close search', 'gufte'); ?>">
                        <?php gufte_icon('close', 'w-5 h-5'); ?>
                    </button>
                </div>

                <div class="px-6 py-5 overflow-y-auto">
                    <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="relative">
                        <label for="modal-search-field" class="sr-only"><?php esc_html_e('Search', 'gufte'); ?></label>
                        <div class="flex items-center rounded-xl border border-gray-200 bg-gray-50 shadow-inner">
                            <span class="pl-4 text-gray-400">
                                <?php gufte_icon('magnify', 'w-5 h-5'); ?>
                            </span>
                            <input
                                type="search"
                                id="modal-search-field"
                                name="s"
                                value="<?php echo esc_attr(get_search_query()); ?>"
                                placeholder="<?php echo esc_attr_x('Search for a song, lyric, album or artist…', 'search placeholder', 'gufte'); ?>"
                                class="w-full bg-transparent py-4 px-3 text-base text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-0 focus:border-none focus-visible:ring-0 focus-visible:border-none"
                                autocomplete="off"
                                data-search-input
                                style="outline: none !important; box-shadow: none !important;"
                            />
                            <button type="submit" class="mr-3 inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                                <?php gufte_icon('arrow-right', 'w-4 h-4 mr-1.5'); ?>
                                <span><?php esc_html_e('Search', 'gufte'); ?></span>
                            </button>
                        </div>
                    </form>

                    <?php if ($has_history) : ?>
                        <div class="mt-6 grid gap-6 lg:grid-cols-2">
                            <?php if (!empty($recent_searches)) : ?>
                                <div>
                                    <h3 class="mb-2 flex items-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        <?php gufte_icon('history', 'mr-2 w-4 h-4 text-gray-400'); ?>
                                        <?php esc_html_e('Recent searches', 'gufte'); ?>
                                    </h3>
                                    <ul class="space-y-2">
                                        <?php foreach ($recent_searches as $search) : ?>
                                            <li>
                                                <a href="<?php echo esc_url(add_query_arg('s', urlencode($search), home_url('/'))); ?>" class="group flex items-center justify-between rounded-lg border border-transparent px-3 py-2 text-sm text-gray-600 transition hover:border-primary-100 hover:bg-primary-50/60 hover:text-primary-700">
                                                    <span class="flex items-center">
                                                        <?php gufte_icon('magnify', 'mr-2 w-4 h-4 text-primary-500 group-hover:scale-105 transition-transform'); ?>
                                                        <?php echo esc_html($search); ?>
                                                    </span>
                                                    <?php gufte_icon('arrow-top-right', 'w-4 h-4 text-gray-400 group-hover:text-primary-500'); ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($recent_visits)) : ?>
                                <div>
                                    <h3 class="mb-2 flex items-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        <?php gufte_icon('music', 'mr-2 w-4 h-4 text-gray-400'); ?>
                                        <?php esc_html_e('Recent visits', 'gufte'); ?>
                                    </h3>
                                    <ul class="space-y-2">
                                        <?php foreach ($recent_visits as $post_id) : ?>
                                            <li>
                                                <a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="group flex items-center justify-between rounded-lg border border-transparent px-3 py-2 text-sm text-gray-600 transition hover:border-primary-100 hover:bg-primary-50/60 hover:text-primary-700">
                                                    <span class="flex items-center">
                                                        <?php gufte_icon('play-circle', 'mr-2 w-4 h-4 text-primary-500 group-hover:scale-105 transition-transform'); ?>
                                                        <?php echo esc_html(get_the_title($post_id)); ?>
                                                    </span>
                                                    <?php gufte_icon('arrow-top-right', 'w-4 h-4 text-gray-400 group-hover:text-primary-500'); ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else : ?>
                        <div class="mt-6 rounded-lg border border-dashed border-gray-200 bg-gray-50 p-6 text-sm text-gray-500">
                            <p><?php esc_html_e('Start typing to discover songs, albums, singers and translations. We will remember your latest searches for quicker access.', 'gufte'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}
add_action('wp_footer', 'gufte_render_search_modal');

/**
 * Ensure the custom "Translator" role exists with the expected capabilities.
 */
function gufte_register_translator_role() {
    $capabilities = array(
        'read'                   => true,
        'edit_posts'             => true,
        'edit_published_posts'   => true,
        'upload_files'           => true,
        'delete_posts'           => false,
        'publish_posts'          => false,
        'edit_others_posts'      => false,
        'delete_published_posts' => false,
    );

    $role = get_role('translator');

    if (!$role) {
        add_role('translator', __('Translator', 'gufte'), $capabilities);
        $role = get_role('translator');
    }

    if ($role) {
        foreach ($capabilities as $cap => $grant) {
            if ($grant) {
                $role->add_cap($cap);
            } else {
                $role->remove_cap($cap);
            }
        }
    }
}
add_action('after_switch_theme', 'gufte_register_translator_role');
add_action('init', 'gufte_register_translator_role');

/**
 * Get Apple Music Preview URL from iTunes API
 *
 * @param string $apple_music_id Apple Music Song ID
 * @return string|false Preview URL or false if not found
 */
function arcuras_get_apple_music_preview($apple_music_id) {
    if (empty($apple_music_id)) {
        return false;
    }

    // Extract ID if URL is provided
    $song_id = arcuras_extract_apple_music_id($apple_music_id);
    if (!$song_id) {
        return false;
    }

    // Check cache first (transient for 7 days)
    $cache_key = 'apple_preview_' . md5($song_id);
    $cached = get_transient($cache_key);

    if ($cached !== false) {
        return $cached;
    }

    // iTunes Search API endpoint
    $api_url = sprintf(
        'https://itunes.apple.com/lookup?id=%s&entity=song',
        urlencode($song_id)
    );

    // Fetch data from iTunes API
    $response = wp_remote_get($api_url, array(
        'timeout' => 10,
        'headers' => array(
            'Accept' => 'application/json'
        )
    ));

    if (is_wp_error($response)) {
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (empty($data['results'][0]['previewUrl'])) {
        return false;
    }

    $preview_url = $data['results'][0]['previewUrl'];

    // Cache for 7 days
    set_transient($cache_key, $preview_url, 7 * DAY_IN_SECONDS);

    return $preview_url;
}

/**
 * Get song artwork from iTunes API
 *
 * @param string $apple_music_id Apple Music Song ID
 * @return array|false Array with artwork URLs or false
 */
function arcuras_get_apple_music_artwork($apple_music_id) {
    if (empty($apple_music_id)) {
        return false;
    }

    // Extract ID if URL is provided
    $song_id = arcuras_extract_apple_music_id($apple_music_id);
    if (!$song_id) {
        return false;
    }

    // Check cache
    $cache_key = 'apple_artwork_' . md5($song_id);
    $cached = get_transient($cache_key);

    if ($cached !== false) {
        return $cached;
    }

    // iTunes Search API
    $api_url = sprintf(
        'https://itunes.apple.com/lookup?id=%s&entity=song',
        urlencode($song_id)
    );

    $response = wp_remote_get($api_url, array('timeout' => 10));

    if (is_wp_error($response)) {
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (empty($data['results'][0])) {
        return false;
    }

    $result = $data['results'][0];

    $artwork = array(
        'small' => str_replace('100x100', '60x60', $result['artworkUrl100']),
        'medium' => $result['artworkUrl100'],
        'large' => str_replace('100x100', '600x600', $result['artworkUrl100']),
        'track_name' => $result['trackName'],
        'artist_name' => $result['artistName'],
        'album_name' => $result['collectionName']
    );

    // Cache for 7 days
    set_transient($cache_key, $artwork, 7 * DAY_IN_SECONDS);

    return $artwork;
}

/**
 * Extract Apple Music Song ID from URL
 *
 * @param string $input Apple Music URL or ID
 * @return string|false Song ID or false if not found
 */
function arcuras_extract_apple_music_id($input) {
    if (empty($input)) {
        return false;
    }

    // If it's already just a numeric ID, return it
    if (is_numeric($input)) {
        return $input;
    }

    // Extract ID from various Apple Music URL formats:
    // https://music.apple.com/us/song/song-name/1234567890
    // https://music.apple.com/tr/album/album-name/1234567890?i=9876543210
    // https://embed.music.apple.com/us/song/1234567890

    // Pattern 1: /song/ID or /album/ID?i=ID
    if (preg_match('/[?&]i=(\d+)/', $input, $matches)) {
        return $matches[1];
    }

    // Pattern 2: /song/name/ID or /album/name/ID
    if (preg_match('/\/(song|album)\/[^\/]+\/(\d+)/', $input, $matches)) {
        return $matches[2];
    }

    // Pattern 3: embed URLs
    if (preg_match('/embed\.music\.apple\.com\/[^\/]+\/song\/(\d+)/', $input, $matches)) {
        return $matches[1];
    }

    return false;
}

/**
 * Register Custom Gutenberg Blocks
 */
function arcuras_register_blocks() {
    // Register block type using block.json
    // The render callback is now defined in block.json as "render": "file:./render.php"
    register_block_type(
        get_template_directory() . '/blocks/lyrics-translations'
    );
}

/**
 * Auto-detect sections in lyrics based on repetition patterns
 */
function arcuras_auto_detect_sections($lyrics) {
    if (empty($lyrics)) {
        return array();
    }

    $lines = explode("\n", $lyrics);

    // Split into blocks separated by empty lines
    $blocks = array();
    $current_block = array();
    $line_index = 0;

    foreach ($lines as $line) {
        if (trim($line) === '') {
            if (!empty($current_block)) {
                $blocks[] = array(
                    'lines' => $current_block,
                    'start_index' => $line_index - count($current_block),
                    'normalized' => strtolower(trim(implode(' ', $current_block)))
                );
                $current_block = array();
            }
        } else {
            $current_block[] = $line;
        }
        $line_index++;
    }

    // Add last block if exists
    if (!empty($current_block)) {
        $blocks[] = array(
            'lines' => $current_block,
            'start_index' => $line_index - count($current_block),
            'normalized' => strtolower(trim(implode(' ', $current_block)))
        );
    }

    // Find repeating blocks (chorus candidates)
    $block_counts = array();
    foreach ($blocks as $idx => $block) {
        $normalized = $block['normalized'];
        if (!isset($block_counts[$normalized])) {
            $block_counts[$normalized] = array();
        }
        $block_counts[$normalized][] = $idx;
    }

    // Assign section types
    $sections = array();
    $verse_counter = 1;
    $chorus_counter = 1;

    foreach ($blocks as $idx => $block) {
        $normalized = $block['normalized'];
        $occurrences = count($block_counts[$normalized]);

        // If this block appears more than once, it's likely a chorus
        if ($occurrences >= 2) {
            // Check if we already labeled this as chorus
            $already_labeled = false;
            foreach ($sections as $sec) {
                if ($sec['normalized'] === $normalized && $sec['type'] === 'chorus') {
                    $already_labeled = true;
                    break;
                }
            }

            if (!$already_labeled) {
                $section_name = $occurrences > 2 ? 'Chorus' : 'Chorus ' . $chorus_counter;
                $chorus_counter++;
            } else {
                $section_name = 'Chorus';
            }

            $sections[] = array(
                'type' => 'chorus',
                'name' => $section_name,
                'start_index' => $block['start_index'],
                'normalized' => $normalized
            );
        } else {
            // Unique block, likely a verse
            $sections[] = array(
                'type' => 'verse',
                'name' => 'Verse ' . $verse_counter,
                'start_index' => $block['start_index'],
                'normalized' => $normalized
            );
            $verse_counter++;
        }
    }

    return $sections;
}

/**
 * PHP Render Callback for Lyrics & Translations Block
 * This ensures consistent HTML output and avoids validation errors
 */
function arcuras_render_lyrics_block($attributes) {
    if (empty($attributes['languages']) || !is_array($attributes['languages'])) {
        return '';
    }

    $languages = $attributes['languages'];
    $auto_detect = isset($attributes['autoDetectSections']) ? $attributes['autoDetectSections'] : true;

    // Find original languages (marked with isOriginal: true) - can be multiple
    $original_langs = array();
    $translation_langs = array();

    foreach ($languages as $lang) {
        if (isset($lang['isOriginal']) && $lang['isOriginal']) {
            $original_langs[] = $lang;
        } else {
            $translation_langs[] = $lang;
        }
    }

    // If no original is marked, use first language
    if (empty($original_langs) && count($languages) > 0) {
        $original_langs[] = $languages[0];
        $translation_langs = array_slice($languages, 1);
    }

    if (empty($original_langs)) {
        return '';
    }

    // Use first original for structure (backwards compatibility)
    $original_lang = $original_langs[0];

    // Prepare lines arrays
    // Fix any escaped newlines that may exist in the content
    $original_lyrics = isset($original_lang['lyrics']) ? $original_lang['lyrics'] : '';
    // If lyrics contain literal \n (backslash+n), convert to real newlines
    if (strpos($original_lyrics, '\n') !== false && strpos($original_lyrics, "\n") === false) {
        $original_lyrics = str_replace('\n', "\n", $original_lyrics);
    }
    $original_lines = explode("\n", $original_lyrics);

    $translation_lines_map = array();

    foreach ($translation_langs as $lang) {
        $trans_lyrics = isset($lang['lyrics']) ? $lang['lyrics'] : '';
        // Fix escaped newlines in translations too
        if (strpos($trans_lyrics, '\n') !== false && strpos($trans_lyrics, "\n") === false) {
            $trans_lyrics = str_replace('\n', "\n", $trans_lyrics);
        }
        $translation_lines_map[$lang['code']] = explode("\n", $trans_lyrics);
    }

    // Auto-detect sections if enabled
    $detected_sections = array();
    if ($auto_detect) {
        $detected_sections = arcuras_auto_detect_sections($original_lang['lyrics']);
    }

    // Get section comments
    $section_comments = isset($attributes['sectionComments']) ? $attributes['sectionComments'] : array();

    // Calculate statistics for each language
    $stats = array();
    foreach ($languages as $lang) {
        $lyrics = isset($lang['lyrics']) ? $lang['lyrics'] : '';
        $lines = array_filter(explode("\n", $lyrics), function($line) {
            return trim($line) !== '';
        });
        $words = array_filter(preg_split('/\s+/', $lyrics), function($word) {
            return trim($word) !== '';
        });

        // Count sections for this language
        $lang_sections = array();
        if ($auto_detect && $lang['isOriginal']) {
            $lang_sections = $detected_sections;
        }

        // Count verse and chorus
        $verse_count = 0;
        $chorus_count = 0;
        foreach ($lang_sections as $section) {
            if ($section['type'] === 'verse') {
                $verse_count++;
            } elseif ($section['type'] === 'chorus') {
                $chorus_count++;
            }
        }

        // Count annotations (section comments) for this language
        $annotation_count = 0;
        if (!empty($section_comments)) {
            foreach ($section_comments as $key => $comment) {
                // Key format: "Verse 1__en", "Chorus__tr"
                $parts = explode('__', $key);
                if (count($parts) === 2 && $parts[1] === $lang['code']) {
                    $annotation_count++;
                }
            }
        }

        $stats[$lang['code']] = array(
            'lines' => count($lines),
            'words' => count($words),
            'verses' => $verse_count,
            'choruses' => $chorus_count,
            'annotations' => $annotation_count
        );
    }

    ob_start();
    ?>
    <div class="wp-block-arcuras-lyrics-translations lyrics-block-frontend" data-languages='<?php echo esc_attr(wp_json_encode($languages)); ?>' data-section-comments='<?php echo esc_attr(wp_json_encode($section_comments)); ?>'>
        <div class="lyrics-container">
            <!-- Always show language selector bar, even if only original language -->
            <div class="language-selector-bar">
                <div class="language-buttons">
                    <?php
                    // Show all original languages first
                    $is_first = true;
                    foreach ($original_langs as $orig_lang):
                    ?>
                        <button class="lang-button <?php echo $is_first ? 'active' : ''; ?> original-lang-button" data-lang="<?php echo esc_attr($orig_lang['code']); ?>" data-original="true">
                            <span class="lang-name"><?php echo esc_html($orig_lang['name']); ?></span>
                            <span class="original-badge">Original</span>
                        </button>
                    <?php
                        $is_first = false;
                    endforeach;
                    ?>
                    <?php foreach ($translation_langs as $lang): ?>
                        <button class="lang-button" data-lang="<?php echo esc_attr($lang['code']); ?>" data-original="false">
                            <?php echo esc_html($lang['name']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Statistics Bar -->
            <div class="lyrics-stats-bar">
                <?php
                // Show stats for all original languages
                $is_first_stat = true;
                foreach ($original_langs as $orig_lang):
                ?>
                <div class="stats-container <?php echo $is_first_stat ? 'active' : ''; ?>" data-lang="<?php echo esc_attr($orig_lang['code']); ?>" <?php echo !$is_first_stat ? 'style="display: none;"' : ''; ?>>
                    <span class="stat-item">
                        <svg class="stat-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                        <strong><?php echo $stats[$orig_lang['code']]['lines']; ?></strong> Lines
                    </span>
                    <span class="stat-separator">•</span>
                    <span class="stat-item">
                        <svg class="stat-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 7V4h16v3M9 20h6M12 4v16"></path>
                        </svg>
                        <strong><?php echo $stats[$orig_lang['code']]['words']; ?></strong> Words
                    </span>
                    <?php if ($auto_detect && ($stats[$orig_lang['code']]['verses'] > 0 || $stats[$orig_lang['code']]['choruses'] > 0)): ?>
                        <?php if ($stats[$orig_lang['code']]['verses'] > 0): ?>
                            <span class="stat-separator">•</span>
                            <span class="stat-item">
                                <svg class="stat-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                </svg>
                                <strong><?php echo $stats[$orig_lang['code']]['verses']; ?></strong> Verse<?php echo $stats[$orig_lang['code']]['verses'] > 1 ? 's' : ''; ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($stats[$orig_lang['code']]['choruses'] > 0): ?>
                            <span class="stat-separator">•</span>
                            <span class="stat-item">
                                <svg class="stat-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 18V5l12-2v13M9 13l12-2"></path>
                                    <circle cx="6" cy="18" r="3"></circle>
                                    <circle cx="18" cy="16" r="3"></circle>
                                </svg>
                                <strong><?php echo $stats[$orig_lang['code']]['choruses']; ?></strong> Chorus<?php echo $stats[$orig_lang['code']]['choruses'] > 1 ? 'es' : ''; ?>
                            </span>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($stats[$orig_lang['code']]['annotations'] > 0): ?>
                        <span class="stat-separator">•</span>
                        <span class="stat-item">
                            <svg class="stat-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                            <strong><?php echo $stats[$orig_lang['code']]['annotations']; ?></strong> Annotation<?php echo $stats[$orig_lang['code']]['annotations'] > 1 ? 's' : ''; ?>
                        </span>
                    <?php endif; ?>
                </div>
                <?php
                    $is_first_stat = false;
                endforeach;
                ?>
                <?php foreach ($translation_langs as $lang): ?>
                    <div class="stats-container" data-lang="<?php echo esc_attr($lang['code']); ?>" style="display: none;">
                        <span class="stat-item">
                            <svg class="stat-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <line x1="3" y1="12" x2="21" y2="12"></line>
                                <line x1="3" y1="18" x2="21" y2="18"></line>
                            </svg>
                            <strong><?php echo $stats[$lang['code']]['lines']; ?></strong> Lines
                        </span>
                        <span class="stat-separator">•</span>
                        <span class="stat-item">
                            <svg class="stat-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 7V4h16v3M9 20h6M12 4v16"></path>
                            </svg>
                            <strong><?php echo $stats[$lang['code']]['words']; ?></strong> Words
                        </span>
                        <?php if ($stats[$lang['code']]['annotations'] > 0): ?>
                            <span class="stat-separator">•</span>
                            <span class="stat-item">
                                <svg class="stat-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                                <strong><?php echo $stats[$lang['code']]['annotations']; ?></strong> Annotation<?php echo $stats[$lang['code']]['annotations'] > 1 ? 's' : ''; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="lyrics-content-wrapper">
                <?php
                // Display line by line - original followed by translation
                foreach ($original_lines as $line_index => $original_line):
                    $has_content = trim($original_line) !== '';

                    // Check if line is a manual section marker (only in manual mode)
                    $is_manual_section = !$auto_detect && preg_match('/^\[(.+?)\]$/i', trim($original_line), $section_matches);

                    // Skip rendering manual section markers
                    if ($is_manual_section) {
                        continue;
                    }

                    // Check if this line is the start of a detected section (auto-detect mode)
                    // Use $line_index which matches the index used in arcuras_auto_detect_sections
                    $section_at_this_line = null;
                    if ($auto_detect) {
                        foreach ($detected_sections as $section) {
                            if ($section['start_index'] === $line_index) {
                                $section_at_this_line = $section;
                                break;
                            }
                        }
                    }

                    // Render section header if this is a section start
                    if ($section_at_this_line):
                ?>
                        <div class="lyrics-section-header" data-section-name="<?php echo esc_attr($section_at_this_line['name']); ?>">
                            <span class="section-divider-left"></span>
                            <span class="section-name"><?php echo esc_html($section_at_this_line['name']); ?></span>
                            <span class="section-divider-right"></span>
                        </div>
                <?php endif; ?>

                        <!-- Regular Lyrics Line -->
                        <div class="lyrics-line-group<?php echo !$has_content ? ' is-empty-line' : ''; ?>" data-line-index="<?php echo $line_index; ?>">
                            <div class="original-line" data-lang="<?php echo esc_attr($original_lang['code']); ?>">
                                <?php if ($has_content): ?>
                                    <p class="lyrics-line-text"><?php echo nl2br(esc_html($original_line)); ?></p>
                                <?php else: ?>
                                    <br class="stanza-break">
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($translation_langs)): ?>
                                <?php foreach ($translation_langs as $lang): ?>
                                    <?php
                                    $trans_lines = isset($translation_lines_map[$lang['code']]) ? $translation_lines_map[$lang['code']] : array();
                                    $trans_line = isset($trans_lines[$line_index]) ? $trans_lines[$line_index] : '';
                                    $has_trans_content = trim($trans_line) !== '';

                                    // Check if translation line is also a section marker
                                    $trans_is_section = preg_match('/^\[(.+?)\]$/i', trim($trans_line));
                                    ?>
                                    <div class="translation-line" data-lang="<?php echo esc_attr($lang['code']); ?>" style="display: none;">
                                        <?php if ($has_trans_content && !$trans_is_section): ?>
                                            <p class="lyrics-line-text translation-text"><?php echo nl2br(esc_html($trans_line)); ?></p>
                                        <?php else: ?>
                                            <br>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

add_action('init', 'arcuras_register_blocks');

/**
 * Enqueue block frontend assets
 */
function arcuras_enqueue_block_assets() {
    // Frontend JavaScript for lyrics tab switching
    wp_enqueue_script(
        'arcuras-lyrics-frontend',
        get_template_directory_uri() . '/blocks/lyrics-translations/frontend.js',
        array(),
        GUFTE_VERSION,
        true
    );
}
add_action('wp_enqueue_scripts', 'arcuras_enqueue_block_assets');

/**
 * Register REST API endpoint for saving lyric lines
 */
function arcuras_register_save_lyric_line_endpoint() {
    register_rest_route(
        'arcuras/v1',
        '/save-lyric-line',
        array(
            'methods'             => 'POST',
            'callback'            => 'arcuras_save_lyric_line_callback',
            'permission_callback' => function() {
                return is_user_logged_in();
            },
            'args'                => array(
                'post_id' => array(
                    'required'          => true,
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ),
                'post_title' => array(
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'line_index' => array(
                    'required'          => true,
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ),
                'original_text' => array(
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field',
                ),
                'translation_text' => array(
                    'required'          => false,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field',
                ),
                'language_code' => array(
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'language_name' => array(
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'featured_image' => array(
                    'required'          => false,
                    'type'              => 'string',
                    'sanitize_callback' => 'esc_url_raw',
                ),
            ),
        )
    );
}
add_action('rest_api_init', 'arcuras_register_save_lyric_line_endpoint');

/**
 * Callback for deleting saved lyric line
 */
function arcuras_delete_lyric_line_callback($request) {
    $user_id = get_current_user_id();

    if (!$user_id) {
        return new WP_Error(
            'unauthorized',
            __('You must be logged in to delete saved lines.', 'gufte'),
            array('status' => 401)
        );
    }

    $line_key = $request->get_param('line_key');

    // Get user's saved lines
    $saved_lines = get_user_meta($user_id, 'arcuras_saved_lyric_lines', true);
    if (!is_array($saved_lines)) {
        return new WP_Error(
            'no_saved_lines',
            __('No saved lines found.', 'gufte'),
            array('status' => 404)
        );
    }

    // Check if line exists
    if (!isset($saved_lines[$line_key])) {
        return new WP_Error(
            'line_not_found',
            __('Saved line not found.', 'gufte'),
            array('status' => 404)
        );
    }

    // Remove the line
    unset($saved_lines[$line_key]);

    // Update user meta
    $updated = update_user_meta($user_id, 'arcuras_saved_lyric_lines', $saved_lines);

    if ($updated === false) {
        return new WP_Error(
            'delete_failed',
            __('Failed to delete line. Please try again.', 'gufte'),
            array('status' => 500)
        );
    }

    return array(
        'success' => true,
        'message' => __('Line deleted successfully!', 'gufte'),
        'data'    => array(
            'remaining' => count($saved_lines),
        ),
    );
}

/**
 * Callback for saving lyric line
 */
function arcuras_save_lyric_line_callback($request) {
    $user_id = get_current_user_id();

    if (!$user_id) {
        return new WP_Error(
            'unauthorized',
            __('You must be logged in to save lines.', 'gufte'),
            array('status' => 401)
        );
    }

    // Get sanitized parameters
    $post_id          = $request->get_param('post_id');
    $post_title       = $request->get_param('post_title');
    $line_index       = $request->get_param('line_index');
    $original_text    = $request->get_param('original_text');
    $translation_text = $request->get_param('translation_text');
    $language_code    = $request->get_param('language_code');
    $language_name    = $request->get_param('language_name');
    $featured_image   = $request->get_param('featured_image');

    // Verify post exists
    $post = get_post($post_id);
    if (!$post) {
        return new WP_Error(
            'invalid_post',
            __('Invalid post ID.', 'gufte'),
            array('status' => 404)
        );
    }

    // Get user's saved lines (stored in user meta)
    $saved_lines = get_user_meta($user_id, 'arcuras_saved_lyric_lines', true);
    if (!is_array($saved_lines)) {
        $saved_lines = array();
    }

    // Create line data
    $line_data = array(
        'post_id'          => $post_id,
        'post_title'       => $post_title,
        'line_index'       => $line_index,
        'original_text'    => $original_text,
        'translation_text' => $translation_text,
        'language_code'    => $language_code,
        'language_name'    => $language_name,
        'featured_image'   => $featured_image,
        'saved_at'         => current_time('mysql'),
    );

    // Create unique key for this line
    $line_key = $post_id . '_' . $line_index . '_' . $language_code;

    // Check if line already saved
    if (isset($saved_lines[$line_key])) {
        return array(
            'success' => false,
            'message' => __('This line is already saved.', 'gufte'),
            'data'    => array('already_saved' => true),
        );
    }

    // Add to saved lines
    $saved_lines[$line_key] = $line_data;

    // Update user meta
    $updated = update_user_meta($user_id, 'arcuras_saved_lyric_lines', $saved_lines);

    if ($updated === false) {
        return new WP_Error(
            'save_failed',
            __('Failed to save line. Please try again.', 'gufte'),
            array('status' => 500)
        );
    }

    return array(
        'success' => true,
        'message' => __('Line saved successfully!', 'gufte'),
        'data'    => array(
            'line_key'   => $line_key,
            'total_saved' => count($saved_lines),
        ),
    );

    // Delete line endpoint
    register_rest_route(
        'arcuras/v1',
        '/delete-lyric-line',
        array(
            'methods'             => 'POST',
            'callback'            => 'arcuras_delete_lyric_line_callback',
            'permission_callback' => function() {
                return is_user_logged_in();
            },
            'args'                => array(
                'line_key' => array(
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        )
    );
}


/**
 * Include FAQ Functions
 */
require_once get_template_directory() . '/inc/faq-functions.php';

/**
 * Include Admin Tools
 */
require_once get_template_directory() . '/inc/admin-tools.php';

/**
 * Include SEO Settings Admin Page
 */
require_once get_template_directory() . '/inc/admin-seo-settings.php';

/**
 * Post görüntülenme sayacı
 */
function gufte_set_post_views($post_id) {
    $count_key = 'post_views_count';
    $count = get_post_meta($post_id, $count_key, true);
    
    if ($count == '') {
        $count = 0;
        delete_post_meta($post_id, $count_key);
        add_post_meta($post_id, $count_key, '0');
    } else {
        $count++;
        update_post_meta($post_id, $count_key, $count);
    }
}

// Görüntülenme sayacını tetikle
add_action('wp_head', function() {
    if (is_single()) {
        gufte_set_post_views(get_the_ID());
    }
});

/**
 * Custom Login Page Integration
 * Redirect wp-login.php to custom /sign-in/ page
 *
 * @since 2.4.9
 */

// 1. Redirect wp-login.php to custom sign-in page (for non-logged-in users)
add_action('init', 'gufte_redirect_to_custom_login_page');
function gufte_redirect_to_custom_login_page() {
    global $pagenow;

    $sign_in_page = home_url('/sign-in/');

    // Check if we're on wp-login.php
    if ($pagenow == 'wp-login.php') {
        // Allow access with special parameter for admin access
        if (isset($_GET['admin_access']) && $_GET['admin_access'] == '1') {
            return; // Don't redirect if admin_access parameter is present
        }

        // Only redirect if user is not logged in and not processing login/logout
        if (!is_user_logged_in() &&
            !isset($_POST['log']) &&
            !isset($_GET['action']) &&
            !isset($_GET['loggedout'])) {
            wp_redirect($sign_in_page);
            exit;
        }
    }
}

// 2. Handle failed login - redirect to custom sign-in with error
add_action('wp_login_failed', 'gufte_custom_login_failed_redirect');
function gufte_custom_login_failed_redirect($username) {
    $sign_in_page = home_url('/sign-in/');
    $redirect_url = add_query_arg('login', 'failed', $sign_in_page);
    wp_redirect($redirect_url);
    exit;
}

// 3. Handle empty credentials
add_filter('authenticate', 'gufte_custom_authenticate_empty_check', 30, 3);
function gufte_custom_authenticate_empty_check($user, $username, $password) {
    // Only check if this is actually a login attempt (form was submitted)
    if (isset($_POST['log']) && (empty($username) || empty($password))) {
        $sign_in_page = home_url('/sign-in/');
        $redirect_url = add_query_arg('login', 'empty', $sign_in_page);
        wp_redirect($redirect_url);
        exit;
    }
    return $user;
}

// 4. Redirect after logout to custom sign-in page
add_action('wp_logout', 'gufte_custom_logout_redirect');
function gufte_custom_logout_redirect() {
    $sign_in_page = home_url('/sign-in/');
    $redirect_url = add_query_arg('loggedout', 'true', $sign_in_page);
    wp_redirect($redirect_url);
    exit;
}

// 5. Block wp-admin access for non-admin users (subscribers, contributors, etc.)
add_action('admin_init', 'gufte_block_wp_admin_for_subscribers');
function gufte_block_wp_admin_for_subscribers() {
    // Allow AJAX requests
    if (defined('DOING_AJAX') && DOING_AJAX) {
        return;
    }

    // Get current user
    $current_user = wp_get_current_user();

    // If user is logged in but doesn't have edit_posts capability (i.e., subscriber)
    if (is_user_logged_in() && !current_user_can('edit_posts')) {
        // Redirect to home page
        wp_redirect(home_url());
        exit;
    }
}

/**
 * Custom Registration Handler
 * Process custom registration form
 *
 * @since 2.4.9
 */
add_action('admin_post_nopriv_gufte_custom_register', 'gufte_handle_custom_registration');
add_action('admin_post_gufte_custom_register', 'gufte_handle_custom_registration');
function gufte_handle_custom_registration() {
    $register_page = home_url('/register/');

    // Verify nonce
    if (!isset($_POST['gufte_register_nonce']) || !wp_verify_nonce($_POST['gufte_register_nonce'], 'gufte_register_action')) {
        wp_redirect(add_query_arg('error', 'invalid_nonce', $register_page));
        exit;
    }

    // Check if registration is enabled
    if (!get_option('users_can_register')) {
        wp_redirect($register_page);
        exit;
    }

    // Get form data
    $username = isset($_POST['user_login']) ? sanitize_user($_POST['user_login']) : '';
    $email = isset($_POST['user_email']) ? sanitize_email($_POST['user_email']) : '';
    $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';

    // Validate required fields
    if (empty($username) || empty($email)) {
        $redirect_url = add_query_arg(array(
            'error' => 'empty_fields',
            'username' => urlencode($username),
            'email' => urlencode($email),
            'first_name' => urlencode($first_name),
            'last_name' => urlencode($last_name)
        ), $register_page);
        wp_redirect($redirect_url);
        exit;
    }

    // Validate email
    if (!is_email($email)) {
        $redirect_url = add_query_arg(array(
            'error' => 'invalid_email',
            'username' => urlencode($username),
            'first_name' => urlencode($first_name),
            'last_name' => urlencode($last_name)
        ), $register_page);
        wp_redirect($redirect_url);
        exit;
    }

    // Check if username exists
    if (username_exists($username)) {
        $redirect_url = add_query_arg(array(
            'error' => 'username_exists',
            'email' => urlencode($email),
            'first_name' => urlencode($first_name),
            'last_name' => urlencode($last_name)
        ), $register_page);
        wp_redirect($redirect_url);
        exit;
    }

    // Check if email exists
    if (email_exists($email)) {
        $redirect_url = add_query_arg(array(
            'error' => 'email_exists',
            'username' => urlencode($username),
            'first_name' => urlencode($first_name),
            'last_name' => urlencode($last_name)
        ), $register_page);
        wp_redirect($redirect_url);
        exit;
    }

    // Generate random password
    $password = wp_generate_password(12, false);

    // Create user
    $user_id = wp_create_user($username, $password, $email);

    if (is_wp_error($user_id)) {
        $redirect_url = add_query_arg(array(
            'error' => 'registration_failed',
            'username' => urlencode($username),
            'email' => urlencode($email),
            'first_name' => urlencode($first_name),
            'last_name' => urlencode($last_name)
        ), $register_page);
        wp_redirect($redirect_url);
        exit;
    }

    // Update user meta
    if (!empty($first_name)) {
        update_user_meta($user_id, 'first_name', $first_name);
    }
    if (!empty($last_name)) {
        update_user_meta($user_id, 'last_name', $last_name);
    }

    // Send notification email to user
    wp_new_user_notification($user_id, null, 'both');

    // Redirect to sign-in page with success message
    $sign_in_page = home_url('/sign-in/');
    $redirect_url = add_query_arg('registered', 'true', $sign_in_page);
    wp_redirect($redirect_url);
    exit;
}

/**
 * Google OAuth Integration
 * Handle Google Sign-In
 *
 * @since 2.4.9
 */

// Handle Google OAuth login initiation
add_action('init', 'gufte_handle_google_oauth_routes');
function gufte_handle_google_oauth_routes() {
    // Login route
    if ($_SERVER['REQUEST_URI'] == '/oauth/google/login') {
        gufte_google_oauth_login();
        exit;
    }

    // Callback route
    if ($_SERVER['REQUEST_URI'] == '/oauth/google/callback' || strpos($_SERVER['REQUEST_URI'], '/oauth/google/callback') === 0) {
        gufte_google_oauth_callback();
        exit;
    }
}

// Initiate Google OAuth login
function gufte_google_oauth_login() {
    $client_id = get_option('arcuras_google_client_id');
    $enabled = get_option('arcuras_google_oauth_enabled');

    if ($enabled != '1' || empty($client_id)) {
        wp_redirect(home_url('/sign-in/?error=oauth_disabled'));
        exit;
    }

    $redirect_uri = home_url('/oauth/google/callback');
    $state = wp_create_nonce('google_oauth_state');
    set_transient('google_oauth_state_' . $state, true, 600); // 10 minutes

    $auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query(array(
        'client_id' => $client_id,
        'redirect_uri' => $redirect_uri,
        'response_type' => 'code',
        'scope' => 'openid email profile',
        'state' => $state,
        'access_type' => 'online',
        'prompt' => 'select_account'
    ));

    wp_redirect($auth_url);
    exit;
}

// Handle Google OAuth callback
function gufte_google_oauth_callback() {
    $sign_in_page = home_url('/sign-in/');

    // Check for errors
    if (isset($_GET['error'])) {
        wp_redirect(add_query_arg('login', 'google_error', $sign_in_page));
        exit;
    }

    // Verify state
    $state = isset($_GET['state']) ? sanitize_text_field($_GET['state']) : '';
    if (empty($state) || !get_transient('google_oauth_state_' . $state)) {
        wp_redirect(add_query_arg('login', 'invalid_state', $sign_in_page));
        exit;
    }
    delete_transient('google_oauth_state_' . $state);

    // Get authorization code
    $code = isset($_GET['code']) ? sanitize_text_field($_GET['code']) : '';
    if (empty($code)) {
        wp_redirect(add_query_arg('login', 'no_code', $sign_in_page));
        exit;
    }

    // Exchange code for token
    $client_id = get_option('arcuras_google_client_id');
    $client_secret = get_option('arcuras_google_client_secret');
    $redirect_uri = home_url('/oauth/google/callback');

    $token_response = wp_remote_post('https://oauth2.googleapis.com/token', array(
        'body' => array(
            'code' => $code,
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri' => $redirect_uri,
            'grant_type' => 'authorization_code'
        )
    ));

    if (is_wp_error($token_response)) {
        wp_redirect(add_query_arg('login', 'token_error', $sign_in_page));
        exit;
    }

    $token_data = json_decode(wp_remote_retrieve_body($token_response), true);
    if (!isset($token_data['access_token'])) {
        wp_redirect(add_query_arg('login', 'no_token', $sign_in_page));
        exit;
    }

    // Get user info from Google
    $userinfo_response = wp_remote_get('https://www.googleapis.com/oauth2/v2/userinfo', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $token_data['access_token']
        )
    ));

    if (is_wp_error($userinfo_response)) {
        wp_redirect(add_query_arg('login', 'userinfo_error', $sign_in_page));
        exit;
    }

    $user_data = json_decode(wp_remote_retrieve_body($userinfo_response), true);
    if (!isset($user_data['email'])) {
        wp_redirect(add_query_arg('login', 'no_email', $sign_in_page));
        exit;
    }

    // Check if user exists
    $user = get_user_by('email', $user_data['email']);

    if (!$user) {
        // Create new user
        $username = sanitize_user($user_data['email'], true);
        $username = explode('@', $username)[0];

        // Make username unique
        $base_username = $username;
        $counter = 1;
        while (username_exists($username)) {
            $username = $base_username . $counter;
            $counter++;
        }

        $user_id = wp_create_user(
            $username,
            wp_generate_password(20, false),
            $user_data['email']
        );

        if (is_wp_error($user_id)) {
            wp_redirect(add_query_arg('login', 'user_creation_failed', $sign_in_page));
            exit;
        }

        // Update user meta
        if (isset($user_data['given_name'])) {
            update_user_meta($user_id, 'first_name', sanitize_text_field($user_data['given_name']));
        }
        if (isset($user_data['family_name'])) {
            update_user_meta($user_id, 'last_name', sanitize_text_field($user_data['family_name']));
        }
        if (isset($user_data['picture'])) {
            update_user_meta($user_id, 'google_profile_picture', esc_url($user_data['picture']));
        }
        update_user_meta($user_id, 'google_id', sanitize_text_field($user_data['id']));

        $user = get_user_by('id', $user_id);
    }

    // Log the user in
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID, true);
    do_action('wp_login', $user->user_login, $user);

    // Redirect to home
    wp_redirect(home_url());
    exit;
}

/**
 * Google One Tap Sign-In
 * Add Google One Tap to site header for non-logged-in users
 *
 * @since 2.5.0
 */
add_action('wp_head', 'gufte_google_one_tap_signin');
function gufte_google_one_tap_signin() {
    // Only show for non-logged-in users
    if (is_user_logged_in()) {
        return;
    }

    // Check if Google OAuth is enabled
    $google_oauth_enabled = get_option('arcuras_google_oauth_enabled', '0');
    $google_client_id = get_option('arcuras_google_client_id', '');
    $one_tap_enabled = get_option('arcuras_google_one_tap_enabled', '1');

    if ($google_oauth_enabled != '1' || empty($google_client_id) || $one_tap_enabled != '1') {
        return;
    }

    $login_uri = home_url('/oauth/google/onetap-callback');
    ?>
    <!-- Google One Tap Sign-In -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script>
        function handleCredentialResponse(response) {
            // Send the credential to our server
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo esc_url($login_uri); ?>';

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'credential';
            input.value = response.credential;

            const nonce = document.createElement('input');
            nonce.type = 'hidden';
            nonce.name = 'one_tap_nonce';
            nonce.value = '<?php echo wp_create_nonce("google_one_tap"); ?>';

            form.appendChild(input);
            form.appendChild(nonce);
            document.body.appendChild(form);
            form.submit();
        }

        // Initialize Google One Tap when script loads
        function initGoogleOneTap() {
            if (typeof google !== 'undefined' && google.accounts && google.accounts.id) {
                google.accounts.id.initialize({
                    client_id: '<?php echo esc_js($google_client_id); ?>',
                    callback: handleCredentialResponse,
                    auto_select: false,
                    cancel_on_tap_outside: true
                });

                google.accounts.id.prompt();
            } else {
                setTimeout(initGoogleOneTap, 500);
            }
        }

        // Start initialization when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initGoogleOneTap);
        } else {
            initGoogleOneTap();
        }
    </script>
    <?php
}

// Handle Google One Tap callback
add_action('init', 'gufte_handle_google_one_tap_callback');
function gufte_handle_google_one_tap_callback() {
    if ($_SERVER['REQUEST_URI'] == '/oauth/google/onetap-callback' && isset($_POST['credential'])) {
        $sign_in_page = home_url('/sign-in/');

        // Verify nonce
        if (!isset($_POST['one_tap_nonce']) || !wp_verify_nonce($_POST['one_tap_nonce'], 'google_one_tap')) {
            wp_redirect(add_query_arg('login', 'invalid_nonce', $sign_in_page));
            exit;
        }

        // Decode the credential (JWT)
        $credential = sanitize_text_field($_POST['credential']);
        $parts = explode('.', $credential);

        if (count($parts) !== 3) {
            wp_redirect(add_query_arg('login', 'invalid_credential', $sign_in_page));
            exit;
        }

        // Decode the payload (second part of JWT)
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

        if (!$payload || !isset($payload['email'])) {
            wp_redirect(add_query_arg('login', 'invalid_payload', $sign_in_page));
            exit;
        }

        // Verify the token with Google
        $client_id = get_option('arcuras_google_client_id');
        if (!isset($payload['aud']) || $payload['aud'] !== $client_id) {
            wp_redirect(add_query_arg('login', 'invalid_audience', $sign_in_page));
            exit;
        }

        // Check token expiration
        if (!isset($payload['exp']) || $payload['exp'] < time()) {
            wp_redirect(add_query_arg('login', 'token_expired', $sign_in_page));
            exit;
        }

        // Extract user data
        $email = sanitize_email($payload['email']);
        $first_name = isset($payload['given_name']) ? sanitize_text_field($payload['given_name']) : '';
        $last_name = isset($payload['family_name']) ? sanitize_text_field($payload['family_name']) : '';
        $picture = isset($payload['picture']) ? esc_url($payload['picture']) : '';
        $google_id = isset($payload['sub']) ? sanitize_text_field($payload['sub']) : '';

        // Check if user exists
        $user = get_user_by('email', $email);

        if (!$user) {
            // Create new user
            $username = sanitize_user($email, true);
            $username = explode('@', $username)[0];

            // Make username unique
            $base_username = $username;
            $counter = 1;
            while (username_exists($username)) {
                $username = $base_username . $counter;
                $counter++;
            }

            $user_id = wp_create_user(
                $username,
                wp_generate_password(20, false),
                $email
            );

            if (is_wp_error($user_id)) {
                wp_redirect(add_query_arg('login', 'user_creation_failed', $sign_in_page));
                exit;
            }

            // Update user meta
            if (!empty($first_name)) {
                update_user_meta($user_id, 'first_name', $first_name);
            }
            if (!empty($last_name)) {
                update_user_meta($user_id, 'last_name', $last_name);
            }
            if (!empty($picture)) {
                update_user_meta($user_id, 'google_profile_picture', $picture);
            }
            if (!empty($google_id)) {
                update_user_meta($user_id, 'google_id', $google_id);
            }

            $user = get_user_by('id', $user_id);
        }

        // Log the user in
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);
        do_action('wp_login', $user->user_login, $user);

        // Redirect to current page or home
        $redirect_to = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : home_url();
        wp_redirect($redirect_to);
        exit;
    }
}

/**
 * Track User Login Activity
 * Records last login time, IP address, and device information
 *
 * @since 2.5.0
 */
add_action('wp_login', 'arcuras_track_user_login', 10, 2);
function arcuras_track_user_login($user_login, $user) {
    // Get user agent and parse device info
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $device_info = arcuras_parse_user_agent($user_agent);

    // Get IP address
    $ip_address = arcuras_get_user_ip();

    // Get location from IP
    $location_info = arcuras_get_location_from_ip($ip_address);

    // Update user meta
    update_user_meta($user->ID, 'last_login', current_time('mysql'));
    update_user_meta($user->ID, 'last_login_ip', $ip_address);
    update_user_meta($user->ID, 'last_login_device', $device_info['device']);
    update_user_meta($user->ID, 'last_login_browser', $device_info['browser']);
    update_user_meta($user->ID, 'last_login_os', $device_info['os']);
    update_user_meta($user->ID, 'last_login_country', $location_info['country']);
    update_user_meta($user->ID, 'last_login_country_code', $location_info['country_code']);
    update_user_meta($user->ID, 'last_login_city', $location_info['city']);
}

/**
 * Parse User Agent to get device, browser, and OS information
 *
 * @param string $user_agent User agent string
 * @return array Device information
 */
function arcuras_parse_user_agent($user_agent) {
    $device = 'Desktop';
    $browser = 'Unknown';
    $os = 'Unknown';

    // Detect device type
    if (preg_match('/mobile/i', $user_agent)) {
        $device = 'Mobile';
    } elseif (preg_match('/tablet|ipad/i', $user_agent)) {
        $device = 'Tablet';
    }

    // Detect browser
    if (preg_match('/Chrome/i', $user_agent) && !preg_match('/Edge|Edg/i', $user_agent)) {
        $browser = 'Chrome';
    } elseif (preg_match('/Safari/i', $user_agent) && !preg_match('/Chrome/i', $user_agent)) {
        $browser = 'Safari';
    } elseif (preg_match('/Firefox/i', $user_agent)) {
        $browser = 'Firefox';
    } elseif (preg_match('/Edge|Edg/i', $user_agent)) {
        $browser = 'Edge';
    } elseif (preg_match('/MSIE|Trident/i', $user_agent)) {
        $browser = 'Internet Explorer';
    } elseif (preg_match('/Opera|OPR/i', $user_agent)) {
        $browser = 'Opera';
    }

    // Detect OS
    if (preg_match('/Windows NT 10/i', $user_agent)) {
        $os = 'Windows 10/11';
    } elseif (preg_match('/Windows NT 6.3/i', $user_agent)) {
        $os = 'Windows 8.1';
    } elseif (preg_match('/Windows NT 6.2/i', $user_agent)) {
        $os = 'Windows 8';
    } elseif (preg_match('/Windows NT 6.1/i', $user_agent)) {
        $os = 'Windows 7';
    } elseif (preg_match('/Windows/i', $user_agent)) {
        $os = 'Windows';
    } elseif (preg_match('/Macintosh|Mac OS X/i', $user_agent)) {
        $os = 'macOS';
    } elseif (preg_match('/Linux/i', $user_agent)) {
        $os = 'Linux';
    } elseif (preg_match('/Android/i', $user_agent)) {
        $os = 'Android';
    } elseif (preg_match('/iPhone|iPad|iPod/i', $user_agent)) {
        $os = 'iOS';
    }

    return array(
        'device' => $device,
        'browser' => $browser,
        'os' => $os
    );
}

/**
 * Get user IP address
 *
 * @return string IP address
 */
function arcuras_get_user_ip() {
    $ip = '';

    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    return sanitize_text_field($ip);
}

/**
 * Get location information from IP address
 *
 * @param string $ip_address IP address
 * @return array Location information (country, country_code, city)
 */
function arcuras_get_location_from_ip($ip_address) {
    // Default values
    $default = array(
        'country' => '',
        'country_code' => '',
        'city' => ''
    );

    // Skip for localhost/private IPs
    if (empty($ip_address) ||
        $ip_address == '127.0.0.1' ||
        $ip_address == '::1' ||
        strpos($ip_address, '192.168.') === 0 ||
        strpos($ip_address, '10.') === 0) {
        return $default;
    }

    // Check cache first (cache for 24 hours)
    $cache_key = 'ip_location_' . md5($ip_address);
    $cached = get_transient($cache_key);
    if ($cached !== false) {
        return $cached;
    }

    // Call IP geolocation API
    $api_url = 'http://ip-api.com/json/' . $ip_address . '?fields=status,country,countryCode,city';
    $response = wp_remote_get($api_url, array(
        'timeout' => 5,
        'sslverify' => false
    ));

    if (is_wp_error($response)) {
        return $default;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!$data || !isset($data['status']) || $data['status'] !== 'success') {
        return $default;
    }

    $location_info = array(
        'country' => isset($data['country']) ? $data['country'] : '',
        'country_code' => isset($data['countryCode']) ? strtolower($data['countryCode']) : '',
        'city' => isset($data['city']) ? $data['city'] : ''
    );

    // Cache for 24 hours
    set_transient($cache_key, $location_info, DAY_IN_SECONDS);

    return $location_info;
}

/**
 * Convert country code to flag emoji
 *
 * @param string $country_code Two-letter country code (e.g., 'us', 'tr', 'gb')
 * @return string Flag emoji
 */
function arcuras_get_flag_emoji($country_code) {
    if (empty($country_code) || strlen($country_code) !== 2) {
        return '';
    }

    $country_code = strtoupper($country_code);

    // Convert country code to regional indicator symbols
    // A = U+1F1E6 (Regional Indicator Symbol Letter A)
    $codepoints = array();
    for ($i = 0; $i < 2; $i++) {
        $codepoints[] = 0x1F1E6 + ord($country_code[$i]) - ord('A');
    }

    // Convert codepoints to UTF-8
    $flag = '';
    foreach ($codepoints as $codepoint) {
        if (function_exists('mb_chr')) {
            $flag .= mb_chr($codepoint);
        } else {
            // Fallback for older PHP versions
            $flag .= html_entity_decode('&#' . $codepoint . ';', ENT_QUOTES, 'UTF-8');
        }
    }

    return $flag;
}

/**
 * Add custom columns to Users table
 */
add_filter('manage_users_columns', 'arcuras_add_user_columns');
function arcuras_add_user_columns($columns) {
    $columns['last_login'] = 'Last Login';
    $columns['login_device'] = 'Device';
    $columns['login_location'] = 'Location';
    return $columns;
}

/**
 * Display custom column content in Users table
 */
add_action('manage_users_custom_column', 'arcuras_show_user_column_content', 10, 3);
function arcuras_show_user_column_content($value, $column_name, $user_id) {
    if ($column_name == 'last_login') {
        $last_login = get_user_meta($user_id, 'last_login', true);
        if ($last_login) {
            $datetime = new DateTime($last_login);
            $now = new DateTime();
            $diff = $now->diff($datetime);

            if ($diff->days == 0) {
                if ($diff->h == 0) {
                    if ($diff->i == 0) {
                        $time_ago = 'Just now';
                    } elseif ($diff->i == 1) {
                        $time_ago = '1 minute ago';
                    } else {
                        $time_ago = $diff->i . ' minutes ago';
                    }
                } elseif ($diff->h == 1) {
                    $time_ago = '1 hour ago';
                } else {
                    $time_ago = $diff->h . ' hours ago';
                }
            } elseif ($diff->days == 1) {
                $time_ago = 'Yesterday';
            } elseif ($diff->days < 7) {
                $time_ago = $diff->days . ' days ago';
            } else {
                $time_ago = $datetime->format('M d, Y');
            }

            return '<span title="' . esc_attr($datetime->format('M d, Y H:i:s')) . '">' . $time_ago . '</span>';
        } else {
            return '<span style="color: #999;">Never</span>';
        }
    }

    if ($column_name == 'login_device') {
        $device = get_user_meta($user_id, 'last_login_device', true);
        $browser = get_user_meta($user_id, 'last_login_browser', true);
        $os = get_user_meta($user_id, 'last_login_os', true);

        if ($device || $browser || $os) {
            $device_icon = $device == 'Mobile' ? '📱' : ($device == 'Tablet' ? '📱' : '💻');
            $output = $device_icon . ' ';

            if ($browser && $os) {
                $output .= $browser . ' on ' . $os;
            } elseif ($browser) {
                $output .= $browser;
            } elseif ($os) {
                $output .= $os;
            } else {
                $output .= $device;
            }

            return $output;
        } else {
            return '<span style="color: #999;">—</span>';
        }
    }

    if ($column_name == 'login_location') {
        $country = get_user_meta($user_id, 'last_login_country', true);
        $country_code = get_user_meta($user_id, 'last_login_country_code', true);
        $city = get_user_meta($user_id, 'last_login_city', true);
        $ip = get_user_meta($user_id, 'last_login_ip', true);

        if ($country || $city) {
            $output = '';

            // Add flag emoji if country code exists
            if ($country_code) {
                $flag = arcuras_get_flag_emoji($country_code);
                $output .= $flag . ' ';
            }

            // Add city and country
            if ($city && $country) {
                $output .= $city . ', ' . $country;
            } elseif ($country) {
                $output .= $country;
            } elseif ($city) {
                $output .= $city;
            }

            // Add IP below
            if ($ip) {
                $output .= '<br><small style="color: #666;">' . esc_html($ip) . '</small>';
            }

            return $output;
        } else {
            // Show only IP if no location data
            if ($ip) {
                return '<small style="color: #666;">' . esc_html($ip) . '</small>';
            }
            return '<span style="color: #999;">—</span>';
        }
    }

    return $value;
}

/**
 * Make custom columns sortable
 */
add_filter('manage_users_sortable_columns', 'arcuras_make_user_columns_sortable');
function arcuras_make_user_columns_sortable($columns) {
    $columns['last_login'] = 'last_login';
    return $columns;
}

/**
 * Handle sorting by custom columns
 */
add_action('pre_get_users', 'arcuras_sort_users_by_last_login');
function arcuras_sort_users_by_last_login($query) {
    if (!is_admin()) {
        return;
    }

    $orderby = $query->get('orderby');

    if ($orderby == 'last_login') {
        $query->set('meta_key', 'last_login');
        $query->set('orderby', 'meta_value');
    }
}

/**
 * Filter lyrics by genre parameter
 */
add_action('pre_get_posts', 'arcuras_filter_lyrics_by_genre');
function arcuras_filter_lyrics_by_genre($query) {
    // Only on main query, not admin, and when genre parameter is set
    if (!is_admin() && $query->is_main_query() && isset($_GET['genre']) && !empty($_GET['genre'])) {
        $genre_slug = sanitize_text_field($_GET['genre']);

        // Convert slug back to genre name (e.g., 'hip-hop-rap' -> 'Hip-Hop/Rap')
        $genre_name_map = array(
            'pop' => 'Pop',
            'alternative' => 'Alternative',
            'hip-hop-rap' => 'Hip-Hop/Rap',
            'rock' => 'Rock',
            'rb-soul' => 'R&B/Soul',
            'urbano-latino' => 'Urbano latino',
            'electronic' => 'Electronic',
            'country' => 'Country',
            'k-pop' => 'K-Pop',
            'soundtrack' => 'Soundtrack',
        );

        // Get genre name from slug, or use slug with proper case
        $genre_name = isset($genre_name_map[$genre_slug])
            ? $genre_name_map[$genre_slug]
            : ucwords(str_replace('-', ' ', $genre_slug));

        // Add meta query to filter by genre
        $meta_query = array(
            array(
                'key' => '_music_genre',
                'value' => $genre_name,
                'compare' => '='
            )
        );

        $query->set('meta_query', $meta_query);
        $query->set('post_type', 'lyrics');
    }
}

/**
 * Filter lyrics archive by first letter
 */
add_action('pre_get_posts', 'gufte_filter_lyrics_by_letter');
function gufte_filter_lyrics_by_letter($query) {
    // Only for main query on lyrics archive
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('lyrics')) {

        // Check if letter filter is set
        if (isset($_GET['letter']) && !empty($_GET['letter'])) {
            $letter = strtoupper(sanitize_text_field(wp_unslash($_GET['letter'])));

            // Get all posts first (we'll filter by title in PHP)
            // This is necessary because WP doesn't support title first-letter filtering natively
            add_filter('posts_where', function($where) use ($letter) {
                global $wpdb;

                if ($letter === '#') {
                    // Non-letter characters (numbers, special chars)
                    $where .= " AND (
                        UPPER(SUBSTRING({$wpdb->posts}.post_title, 1, 1)) NOT REGEXP '^[A-ZÇĞ İÖŞÜ]'
                    )";
                } else {
                    // Specific letter
                    $where .= $wpdb->prepare(" AND UPPER(SUBSTRING({$wpdb->posts}.post_title, 1, 1)) = %s", $letter);
                }

                return $where;
            }, 10, 1);
        }
    }
}

/**
 * Set custom posts per page for lyrics archive
 */
add_action('pre_get_posts', 'gufte_set_lyrics_posts_per_page');
function gufte_set_lyrics_posts_per_page($query) {
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('lyrics')) {
        // Get per_page from URL, default to 17
        $per_page = isset($_GET['per_page']) ? max(6, min(60, intval($_GET['per_page']))) : 17;
        $query->set('posts_per_page', $per_page);
    }
}


/**
 * GitHub Theme Update Checker
 * Enables automatic updates from GitHub releases
 * 
 * @since 2.11.0
 */
require get_template_directory() . '/inc/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$arcurasUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/mugulhan/arcuras-theme/',
    __FILE__,
    'arcuras'
);

// Set the branch to track for updates (main branch)
$arcurasUpdateChecker->setBranch('main');

// Optional: Enable release assets (if you want to use GitHub releases instead of branch)
$arcurasUpdateChecker->getVcsApi()->enableReleaseAssets();
