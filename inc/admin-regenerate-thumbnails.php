<?php
/**
 * Regenerate Thumbnails for All Attachments
 * Regenerates image thumbnails for all media library images
 *
 * @package Gufte
 * @since 1.9.5
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add admin menu page for thumbnail regeneration
 */
function arcuras_add_regenerate_thumbnails_page() {
    add_submenu_page(
        'tools.php',
        __('Regenerate Thumbnails', 'gufte'),
        __('Regenerate Thumbnails', 'gufte'),
        'manage_options',
        'regenerate-thumbnails',
        'arcuras_render_regenerate_thumbnails_page'
    );
}
add_action('admin_menu', 'arcuras_add_regenerate_thumbnails_page');

/**
 * Render the regenerate thumbnails admin page
 */
function arcuras_render_regenerate_thumbnails_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Regenerate Thumbnails', 'gufte'); ?></h1>
        <p><?php _e('This tool will regenerate all thumbnail sizes for images in your media library.', 'gufte'); ?></p>

        <div class="card" style="max-width: 800px;">
            <h2><?php _e('Scan Images', 'gufte'); ?></h2>
            <p><?php _e('First, scan to see how many images need thumbnail regeneration:', 'gufte'); ?></p>

            <button type="button" id="scan-images" class="button button-primary">
                <?php _e('Scan Images', 'gufte'); ?>
            </button>

            <div id="scan-results" style="margin-top: 20px;"></div>
        </div>

        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2><?php _e('Regenerate Thumbnails', 'gufte'); ?></h2>
            <p><?php _e('Process images to regenerate all thumbnail sizes:', 'gufte'); ?></p>

            <button type="button" id="start-regenerate" class="button button-primary" disabled>
                <?php _e('Start Regenerating', 'gufte'); ?>
            </button>

            <button type="button" id="stop-regenerate" class="button" style="display: none;">
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

            <div id="regenerate-results" style="margin-top: 20px; max-height: 400px; overflow-y: auto;"></div>
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
        let imagesToProcess = [];
        let currentIndex = 0;
        let isProcessing = false;

        // Scan images
        $('#scan-images').on('click', function() {
            const $button = $(this);
            $button.prop('disabled', true).text('<?php _e('Scanning...', 'gufte'); ?>');
            $('#scan-results').html('<p><?php _e('Scanning images...', 'gufte'); ?></p>');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'scan_images_for_regeneration',
                    nonce: '<?php echo wp_create_nonce('regenerate_thumbnails'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        imagesToProcess = data.images;

                        let html = '<h3><?php _e('Scan Results', 'gufte'); ?></h3>';
                        html += '<p><strong><?php _e('Total images:', 'gufte'); ?></strong> ' + data.total_images + '</p>';
                        html += '<p><strong><?php _e('Images to process:', 'gufte'); ?></strong> ' + data.images.length + '</p>';

                        if (data.images.length > 0) {
                            $('#start-regenerate').prop('disabled', false);
                        } else {
                            html += '<p style="color: #46b450; font-weight: bold;"><?php _e('All thumbnails are up to date!', 'gufte'); ?></p>';
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
                    $button.prop('disabled', false).text('<?php _e('Scan Images', 'gufte'); ?>');
                }
            });
        });

        // Start regenerating
        $('#start-regenerate').on('click', function() {
            if (imagesToProcess.length === 0) {
                alert('<?php _e('Please scan images first', 'gufte'); ?>');
                return;
            }

            currentIndex = 0;
            isProcessing = true;
            $('#start-regenerate').hide();
            $('#stop-regenerate').show();
            $('#progress-container').show();
            $('#regenerate-results').html('');

            processNextImage();
        });

        // Stop regenerating
        $('#stop-regenerate').on('click', function() {
            isProcessing = false;
            $(this).hide();
            $('#start-regenerate').show();
            $('#progress-status').html('<?php _e('Stopped by user', 'gufte'); ?>');
        });

        function processNextImage() {
            if (!isProcessing || currentIndex >= imagesToProcess.length) {
                if (currentIndex >= imagesToProcess.length) {
                    $('#stop-regenerate').hide();
                    $('#start-regenerate').show();
                    $('#progress-status').html('<?php _e('All images processed!', 'gufte'); ?>');
                }
                return;
            }

            const imageId = imagesToProcess[currentIndex];
            const progress = Math.round(((currentIndex + 1) / imagesToProcess.length) * 100);

            $('#progress-bar').css('width', progress + '%');
            $('#progress-text').text(progress + '%');
            $('#progress-status').html('<?php _e('Processing image ID:', 'gufte'); ?> ' + imageId + ' (' + (currentIndex + 1) + '/' + imagesToProcess.length + ')');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'regenerate_single_thumbnail',
                    nonce: '<?php echo wp_create_nonce('regenerate_thumbnails'); ?>',
                    image_id: imageId
                },
                success: function(response) {
                    let resultClass = 'success';
                    let resultText = '';

                    if (response.success) {
                        resultClass = 'success';
                        resultText = '✓ Image ID ' + imageId + ': ' + response.data;
                    } else {
                        resultClass = 'error';
                        resultText = '✗ Image ID ' + imageId + ': ' + response.data;
                    }

                    $('#regenerate-results').prepend(
                        '<div class="result-item ' + resultClass + '">' + resultText + '</div>'
                    );
                },
                error: function() {
                    $('#regenerate-results').prepend(
                        '<div class="result-item error">✗ Image ID ' + imageId + ': AJAX error</div>'
                    );
                },
                complete: function() {
                    currentIndex++;
                    setTimeout(processNextImage, 100); // Small delay between images
                }
            });
        }
    });
    </script>
    <?php
}

/**
 * AJAX handler: Scan images
 */
function arcuras_ajax_scan_images_for_regeneration() {
    check_ajax_referer('regenerate_thumbnails', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    $args = array(
        'post_type' => 'attachment',
        'post_mime_type' => 'image',
        'post_status' => 'inherit',
        'posts_per_page' => -1,
        'fields' => 'ids'
    );

    $images = get_posts($args);

    wp_send_json_success(array(
        'total_images' => count($images),
        'images' => $images
    ));
}
add_action('wp_ajax_scan_images_for_regeneration', 'arcuras_ajax_scan_images_for_regeneration');

/**
 * AJAX handler: Regenerate single thumbnail
 */
function arcuras_ajax_regenerate_single_thumbnail() {
    check_ajax_referer('regenerate_thumbnails', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    $image_id = intval($_POST['image_id']);

    if (!$image_id) {
        wp_send_json_error('Invalid image ID');
    }

    // Get the file path
    $file_path = get_attached_file($image_id);

    if (!$file_path || !file_exists($file_path)) {
        wp_send_json_error('File not found');
    }

    // Require image functions
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    // Delete old thumbnails
    $metadata = wp_get_attachment_metadata($image_id);
    if (isset($metadata['sizes']) && is_array($metadata['sizes'])) {
        $upload_dir = wp_upload_dir();
        $upload_path = $upload_dir['basedir'];

        foreach ($metadata['sizes'] as $size => $size_data) {
            $thumbnail_path = path_join($upload_path, dirname($metadata['file']));
            $thumbnail_path = path_join($thumbnail_path, $size_data['file']);

            if (file_exists($thumbnail_path)) {
                @unlink($thumbnail_path);
            }
        }
    }

    // Regenerate thumbnails
    $new_metadata = wp_generate_attachment_metadata($image_id, $file_path);

    if (is_wp_error($new_metadata)) {
        wp_send_json_error('Failed to regenerate: ' . $new_metadata->get_error_message());
    }

    if (empty($new_metadata)) {
        wp_send_json_error('Failed to generate metadata');
    }

    // Update metadata
    wp_update_attachment_metadata($image_id, $new_metadata);

    $sizes_count = isset($new_metadata['sizes']) ? count($new_metadata['sizes']) : 0;

    wp_send_json_success('Regenerated ' . $sizes_count . ' thumbnail sizes');
}
add_action('wp_ajax_regenerate_single_thumbnail', 'arcuras_ajax_regenerate_single_thumbnail');
