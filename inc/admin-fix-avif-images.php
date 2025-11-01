<?php
/**
 * Fix AVIF Images Tool
 * Replaces AVIF featured images with JPG versions from Apple Music
 *
 * @package Gufte
 * @since 1.9.5
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add admin menu page
 */
function arcuras_add_fix_avif_page() {
    add_submenu_page(
        'tools.php',
        __('Fix AVIF Images', 'gufte'),
        __('Fix AVIF Images', 'gufte'),
        'manage_options',
        'fix-avif-images',
        'arcuras_render_fix_avif_page'
    );
}
add_action('admin_menu', 'arcuras_add_fix_avif_page');

/**
 * Render the admin page
 */
function arcuras_render_fix_avif_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Fix AVIF Featured Images', 'gufte'); ?></h1>
        <p><?php _e('This tool finds all posts with AVIF featured images and replaces them with JPG versions from Apple Music.', 'gufte'); ?></p>

        <div class="card" style="max-width: 800px;">
            <h2><?php _e('Scan Posts', 'gufte'); ?></h2>

            <button type="button" id="scan-avif" class="button button-primary">
                <?php _e('Scan for AVIF Images', 'gufte'); ?>
            </button>

            <div id="scan-results" style="margin-top: 20px;"></div>
        </div>

        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2><?php _e('Replace Images', 'gufte'); ?></h2>

            <button type="button" id="start-fix" class="button button-primary" disabled>
                <?php _e('Start Fixing', 'gufte'); ?>
            </button>

            <button type="button" id="stop-fix" class="button" style="display: none;">
                <?php _e('Stop', 'gufte'); ?>
            </button>

            <div id="progress-container" style="margin-top: 20px; display: none;">
                <div style="background: #f0f0f1; border-radius: 4px; height: 30px; position: relative; overflow: hidden;">
                    <div id="progress-bar" style="background: #2271b1; height: 100%; width: 0%; transition: width 0.3s;"></div>
                    <div id="progress-text" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-weight: bold; color: #1d2327;">
                        0%
                    </div>
                </div>
                <p id="progress-status" style="margin-top: 10px; font-weight: bold;"></p>
            </div>

            <div id="fix-results" style="margin-top: 20px; max-height: 400px; overflow-y: auto;"></div>
        </div>
    </div>

    <style>
        .result-item {
            padding: 10px;
            margin-bottom: 5px;
            border-left: 4px solid #ddd;
            background: #f9f9f9;
        }
        .result-item.success {
            border-left-color: #46b450;
            background: #f0f9f0;
        }
        .result-item.error {
            border-left-color: #dc3232;
            background: #f9f0f0;
        }
    </style>

    <script>
    jQuery(document).ready(function($) {
        let postsToFix = [];
        let currentIndex = 0;
        let isProcessing = false;

        $('#scan-avif').on('click', function() {
            const $button = $(this);
            $button.prop('disabled', true).text('<?php _e('Scanning...', 'gufte'); ?>');
            $('#scan-results').html('<p><?php _e('Scanning for AVIF images...', 'gufte'); ?></p>');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'scan_avif_images',
                    nonce: '<?php echo wp_create_nonce('fix_avif_images'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        postsToFix = data.posts_with_avif;

                        let html = '<h3><?php _e('Scan Results', 'gufte'); ?></h3>';
                        html += '<p><strong><?php _e('Posts with AVIF images:', 'gufte'); ?></strong> ' + data.posts_with_avif.length + '</p>';

                        if (data.posts_with_avif.length > 0) {
                            html += '<ul>';
                            data.posts_with_avif.slice(0, 10).forEach(function(post) {
                                html += '<li>' + post.title + ' (ID: ' + post.id + ') - ' + post.image_url + '</li>';
                            });
                            if (data.posts_with_avif.length > 10) {
                                html += '<li><em>... and ' + (data.posts_with_avif.length - 10) + ' more</em></li>';
                            }
                            html += '</ul>';

                            $('#start-fix').prop('disabled', false);
                        } else {
                            html += '<p style="color: #46b450; font-weight: bold;"><?php _e('No AVIF images found!', 'gufte'); ?></p>';
                        }

                        $('#scan-results').html(html);
                    }
                },
                complete: function() {
                    $button.prop('disabled', false).text('<?php _e('Scan for AVIF Images', 'gufte'); ?>');
                }
            });
        });

        $('#start-fix').on('click', function() {
            currentIndex = 0;
            isProcessing = true;
            $('#start-fix').hide();
            $('#stop-fix').show();
            $('#progress-container').show();
            $('#fix-results').html('');
            processNextPost();
        });

        $('#stop-fix').on('click', function() {
            isProcessing = false;
            $(this).hide();
            $('#start-fix').show();
            $('#progress-status').html('<?php _e('Stopped', 'gufte'); ?>');
        });

        function processNextPost() {
            if (!isProcessing || currentIndex >= postsToFix.length) {
                if (currentIndex >= postsToFix.length) {
                    $('#stop-fix').hide();
                    $('#start-fix').show();
                    $('#progress-status').html('<?php _e('All done!', 'gufte'); ?>');
                }
                return;
            }

            const post = postsToFix[currentIndex];
            const progress = Math.round(((currentIndex + 1) / postsToFix.length) * 100);

            $('#progress-bar').css('width', progress + '%');
            $('#progress-text').text(progress + '%');
            $('#progress-status').html('Processing: ' + post.title);

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'fix_single_avif_image',
                    nonce: '<?php echo wp_create_nonce('fix_avif_images'); ?>',
                    post_id: post.id,
                    apple_music_id: post.apple_music_id
                },
                success: function(response) {
                    let resultClass = response.success ? 'success' : 'error';
                    let resultText = (response.success ? '✓ ' : '✗ ') + post.title + ': ' + response.data;

                    $('#fix-results').prepend(
                        '<div class="result-item ' + resultClass + '">' + resultText + '</div>'
                    );
                },
                complete: function() {
                    currentIndex++;
                    setTimeout(processNextPost, 1500);
                }
            });
        }
    });
    </script>
    <?php
}

/**
 * AJAX: Scan for AVIF images
 */
function arcuras_ajax_scan_avif_images() {
    check_ajax_referer('fix_avif_images', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    $all_posts = get_posts(array(
        'post_type' => 'lyrics',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids'
    ));

    $posts_with_avif = array();

    foreach ($all_posts as $post_id) {
        if (has_post_thumbnail($post_id)) {
            $thumbnail_id = get_post_thumbnail_id($post_id);
            $image_url = wp_get_attachment_url($thumbnail_id);

            // Check if image is AVIF
            if (preg_match('/\.avif$/i', $image_url)) {
                $apple_music_id = get_post_meta($post_id, '_apple_music_id', true);

                // Try to get from URL if not set
                if (empty($apple_music_id)) {
                    $apple_music_url = get_post_meta($post_id, 'apple_music_url', true);
                    if (!empty($apple_music_url)) {
                        preg_match('/i=(\d+)/', $apple_music_url, $matches);
                        if (!empty($matches[1])) {
                            $apple_music_id = $matches[1];
                        }
                    }
                }

                $posts_with_avif[] = array(
                    'id' => $post_id,
                    'title' => get_the_title($post_id),
                    'image_url' => basename($image_url),
                    'apple_music_id' => $apple_music_id
                );
            }
        }
    }

    wp_send_json_success(array(
        'posts_with_avif' => $posts_with_avif
    ));
}
add_action('wp_ajax_scan_avif_images', 'arcuras_ajax_scan_avif_images');

/**
 * AJAX: Fix single AVIF image
 */
function arcuras_ajax_fix_single_avif_image() {
    check_ajax_referer('fix_avif_images', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    $post_id = intval($_POST['post_id']);
    $apple_music_id = sanitize_text_field($_POST['apple_music_id']);

    if (!$post_id) {
        wp_send_json_error('Invalid post ID');
    }

    if (empty($apple_music_id)) {
        wp_send_json_error('No Apple Music ID found');
    }

    // Delete old AVIF thumbnail
    $old_thumbnail_id = get_post_thumbnail_id($post_id);
    if ($old_thumbnail_id) {
        wp_delete_attachment($old_thumbnail_id, true);
        delete_post_thumbnail($post_id);
    }

    // Fetch new JPG image from iTunes API
    $itunes_url = "https://itunes.apple.com/lookup?id={$apple_music_id}&entity=song";
    $response = wp_remote_get($itunes_url, array('timeout' => 15));

    if (is_wp_error($response)) {
        wp_send_json_error('iTunes API error');
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (empty($data['results']) || !isset($data['results'][0]['artworkUrl100'])) {
        wp_send_json_error('No artwork found');
    }

    // Get JPG artwork URL
    $artwork_url = str_replace('100x100', '3000x3000', $data['results'][0]['artworkUrl100']);
    $artwork_url = preg_replace('/\.(webp|avif|png)$/i', '.jpg', $artwork_url);

    if (!preg_match('/\.(jpg|jpeg)$/i', $artwork_url)) {
        $artwork_url = preg_replace('/bb$/', 'bb.jpg', $artwork_url);
    }

    // Download
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    $tmp = download_url($artwork_url);

    if (is_wp_error($tmp)) {
        wp_send_json_error('Download failed');
    }

    $file_array = array(
        'name' => 'apple-music-' . $apple_music_id . '.jpg',
        'tmp_name' => $tmp
    );

    $attachment_id = media_handle_sideload($file_array, $post_id);

    if (is_wp_error($attachment_id)) {
        @unlink($tmp);
        wp_send_json_error('Upload failed');
    }

    set_post_thumbnail($post_id, $attachment_id);

    wp_send_json_success('Replaced with JPG');
}
add_action('wp_ajax_fix_single_avif_image', 'arcuras_ajax_fix_single_avif_image');
