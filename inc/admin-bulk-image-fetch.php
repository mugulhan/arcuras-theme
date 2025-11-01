<?php
/**
 * Bulk Apple Music Image Fetch Tool
 * Fetches featured images for all lyrics posts using Apple Music ID
 *
 * @package Gufte
 * @since 1.9.5
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add admin menu page for bulk image fetch
 */
function arcuras_add_bulk_image_fetch_page() {
    add_submenu_page(
        'tools.php',
        __('Bulk Image Fetch', 'gufte'),
        __('Bulk Image Fetch', 'gufte'),
        'manage_options',
        'bulk-image-fetch',
        'arcuras_render_bulk_image_fetch_page'
    );
}
add_action('admin_menu', 'arcuras_add_bulk_image_fetch_page');

/**
 * Render the bulk image fetch admin page
 */
function arcuras_render_bulk_image_fetch_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Bulk Apple Music Image Fetch', 'gufte'); ?></h1>
        <p><?php _e('This tool will fetch and set featured images for all lyrics posts that have an Apple Music ID but no featured image.', 'gufte'); ?></p>

        <div class="card" style="max-width: 800px;">
            <h2><?php _e('Scan Posts', 'gufte'); ?></h2>
            <p><?php _e('First, let\'s scan all posts to see how many need images:', 'gufte'); ?></p>

            <button type="button" id="scan-posts" class="button button-primary">
                <?php _e('Scan Posts', 'gufte'); ?>
            </button>

            <div id="scan-results" style="margin-top: 20px;"></div>
        </div>

        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2><?php _e('Fetch Images', 'gufte'); ?></h2>
            <p><?php _e('Process posts one by one to fetch missing images:', 'gufte'); ?></p>

            <button type="button" id="start-fetch" class="button button-primary" disabled>
                <?php _e('Start Fetching Images', 'gufte'); ?>
            </button>

            <button type="button" id="stop-fetch" class="button" style="display: none;">
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

            <div id="fetch-results" style="margin-top: 20px; max-height: 400px; overflow-y: auto;"></div>
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
        .result-item.skip {
            border-left-color: #ffb900;
            background: #fffbf0;
        }
    </style>

    <script>
    jQuery(document).ready(function($) {
        let postsToProcess = [];
        let currentIndex = 0;
        let isProcessing = false;

        // Scan posts
        $('#scan-posts').on('click', function() {
            const $button = $(this);
            $button.prop('disabled', true).text('<?php _e('Scanning...', 'gufte'); ?>');
            $('#scan-results').html('<p><?php _e('Scanning posts...', 'gufte'); ?></p>');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bulk_scan_posts',
                    nonce: '<?php echo wp_create_nonce('bulk_image_fetch'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        postsToProcess = data.posts_needing_images;

                        let html = '<h3><?php _e('Scan Results', 'gufte'); ?></h3>';
                        html += '<p><strong><?php _e('Total lyrics posts:', 'gufte'); ?></strong> ' + data.total_posts + '</p>';
                        html += '<p><strong><?php _e('Posts with Apple Music ID:', 'gufte'); ?></strong> ' + data.posts_with_apple_id + '</p>';
                        html += '<p><strong><?php _e('Posts needing images:', 'gufte'); ?></strong> ' + data.posts_needing_images.length + '</p>';

                        if (data.posts_needing_images.length > 0) {
                            html += '<div style="margin-top: 15px; padding: 10px; background: #fff; border: 1px solid #ddd;">';
                            html += '<strong><?php _e('Posts to process:', 'gufte'); ?></strong><ul style="margin-top: 10px;">';
                            data.posts_needing_images.slice(0, 10).forEach(function(post) {
                                html += '<li>' + post.title + ' (ID: ' + post.id + ')</li>';
                            });
                            if (data.posts_needing_images.length > 10) {
                                html += '<li><em>... <?php _e('and', 'gufte'); ?> ' + (data.posts_needing_images.length - 10) + ' <?php _e('more', 'gufte'); ?></em></li>';
                            }
                            html += '</ul></div>';

                            $('#start-fetch').prop('disabled', false);
                        } else {
                            html += '<p style="color: #46b450; font-weight: bold;"><?php _e('All posts already have featured images!', 'gufte'); ?></p>';
                        }

                        $('#scan-results').html(html);
                    } else {
                        $('#scan-results').html('<p style="color: #dc3232;"><?php _e('Error:', 'gufte'); ?> ' + response.data + '</p>');
                    }
                },
                error: function() {
                    $('#scan-results').html('<p style="color: #dc3232;"><?php _e('AJAX error occurred', 'gufte'); ?></p>');
                },
                complete: function() {
                    $button.prop('disabled', false).text('<?php _e('Scan Posts', 'gufte'); ?>');
                }
            });
        });

        // Start fetching
        $('#start-fetch').on('click', function() {
            if (postsToProcess.length === 0) {
                alert('<?php _e('Please scan posts first', 'gufte'); ?>');
                return;
            }

            currentIndex = 0;
            isProcessing = true;
            $('#start-fetch').hide();
            $('#stop-fetch').show();
            $('#progress-container').show();
            $('#fetch-results').html('');

            processNextPost();
        });

        // Stop fetching
        $('#stop-fetch').on('click', function() {
            isProcessing = false;
            $(this).hide();
            $('#start-fetch').show();
            $('#progress-status').html('<?php _e('Stopped by user', 'gufte'); ?>');
        });

        function processNextPost() {
            if (!isProcessing || currentIndex >= postsToProcess.length) {
                if (currentIndex >= postsToProcess.length) {
                    $('#stop-fetch').hide();
                    $('#start-fetch').show();
                    $('#progress-status').html('<?php _e('All posts processed!', 'gufte'); ?>');
                }
                return;
            }

            const post = postsToProcess[currentIndex];
            const progress = Math.round(((currentIndex + 1) / postsToProcess.length) * 100);

            $('#progress-bar').css('width', progress + '%');
            $('#progress-text').text(progress + '%');
            $('#progress-status').html('<?php _e('Processing:', 'gufte'); ?> ' + post.title + ' (' + (currentIndex + 1) + '/' + postsToProcess.length + ')');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bulk_fetch_single_image',
                    nonce: '<?php echo wp_create_nonce('bulk_image_fetch'); ?>',
                    post_id: post.id,
                    apple_music_id: post.apple_music_id
                },
                success: function(response) {
                    let resultClass = 'skip';
                    let resultText = '';

                    if (response.success) {
                        resultClass = 'success';
                        resultText = '✓ ' + post.title + ': ' + response.data;
                    } else {
                        resultClass = 'error';
                        resultText = '✗ ' + post.title + ': ' + response.data;
                    }

                    $('#fetch-results').prepend(
                        '<div class="result-item ' + resultClass + '">' + resultText + '</div>'
                    );
                },
                error: function() {
                    $('#fetch-results').prepend(
                        '<div class="result-item error">✗ ' + post.title + ': AJAX error</div>'
                    );
                },
                complete: function() {
                    currentIndex++;
                    setTimeout(processNextPost, 1500); // 1.5 second delay between requests
                }
            });
        }
    });
    </script>
    <?php
}

/**
 * AJAX handler: Scan all posts
 */
function arcuras_ajax_bulk_scan_posts() {
    check_ajax_referer('bulk_image_fetch', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    // Get all lyrics posts
    $all_posts = get_posts(array(
        'post_type' => 'lyrics',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids'
    ));

    $posts_with_apple_id = 0;
    $posts_needing_images = array();

    foreach ($all_posts as $post_id) {
        $apple_music_id = get_post_meta($post_id, '_apple_music_id', true);

        // Try to extract from URL if ID is empty
        if (empty($apple_music_id)) {
            $apple_music_url = get_post_meta($post_id, 'apple_music_url', true);
            if (!empty($apple_music_url)) {
                preg_match('/i=(\d+)/', $apple_music_url, $matches);
                if (!empty($matches[1])) {
                    $apple_music_id = $matches[1];
                    update_post_meta($post_id, '_apple_music_id', $apple_music_id);
                }
            }
        }

        if (!empty($apple_music_id)) {
            $posts_with_apple_id++;

            // Check if post has featured image
            if (!has_post_thumbnail($post_id)) {
                $posts_needing_images[] = array(
                    'id' => $post_id,
                    'title' => get_the_title($post_id),
                    'apple_music_id' => $apple_music_id
                );
            }
        }
    }

    wp_send_json_success(array(
        'total_posts' => count($all_posts),
        'posts_with_apple_id' => $posts_with_apple_id,
        'posts_needing_images' => $posts_needing_images
    ));
}
add_action('wp_ajax_bulk_scan_posts', 'arcuras_ajax_bulk_scan_posts');

/**
 * AJAX handler: Fetch single image
 */
function arcuras_ajax_bulk_fetch_single_image() {
    check_ajax_referer('bulk_image_fetch', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    $post_id = intval($_POST['post_id']);
    $apple_music_id = sanitize_text_field($_POST['apple_music_id']);

    if (!$post_id || !$apple_music_id) {
        wp_send_json_error('Invalid post ID or Apple Music ID');
    }

    // Check if already has thumbnail
    if (has_post_thumbnail($post_id)) {
        wp_send_json_success('Already has featured image');
    }

    // Try iTunes API first (more reliable)
    $itunes_url = "https://itunes.apple.com/lookup?id={$apple_music_id}&entity=song";
    $response = wp_remote_get($itunes_url, array('timeout' => 15));

    if (is_wp_error($response)) {
        wp_send_json_error('iTunes API error: ' . $response->get_error_message());
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (empty($data['results']) || !isset($data['results'][0]['artworkUrl100'])) {
        wp_send_json_error('No artwork found in iTunes API response');
    }

    // Get the highest quality artwork in JPG format (not AVIF)
    $artwork_url = str_replace('100x100', '3000x3000', $data['results'][0]['artworkUrl100']);

    // Force JPG format by removing any format extensions and ensuring .jpg
    $artwork_url = preg_replace('/\.(webp|avif|png)$/i', '.jpg', $artwork_url);

    // If URL doesn't end with image extension, add .jpg
    if (!preg_match('/\.(jpg|jpeg)$/i', $artwork_url)) {
        $artwork_url = preg_replace('/bb$/', 'bb.jpg', $artwork_url);
    }

    // Download and attach image
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    $tmp = download_url($artwork_url);

    if (is_wp_error($tmp)) {
        wp_send_json_error('Download error: ' . $tmp->get_error_message());
    }

    $file_array = array(
        'name' => 'apple-music-' . $apple_music_id . '.jpg',
        'tmp_name' => $tmp
    );

    $attachment_id = media_handle_sideload($file_array, $post_id, 'Apple Music album artwork - ' . $apple_music_id);

    if (is_wp_error($attachment_id)) {
        @unlink($tmp);
        wp_send_json_error('Attachment error: ' . $attachment_id->get_error_message());
    }

    // Set as featured image
    set_post_thumbnail($post_id, $attachment_id);

    wp_send_json_success('Featured image set successfully');
}
add_action('wp_ajax_bulk_fetch_single_image', 'arcuras_ajax_bulk_fetch_single_image');
