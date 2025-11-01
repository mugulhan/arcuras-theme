<?php
/**
 * Bulk Artist & Album Fetch Tool
 * Fetches and assigns artist and album taxonomies for all lyrics posts using Apple Music ID
 *
 * @package Gufte
 * @since 2.4.6
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add admin menu page for bulk artist & album fetch
 */
function arcuras_add_bulk_artist_album_fetch_page() {
    add_submenu_page(
        'tools.php',
        __('Bulk Artist & Album Fetch', 'gufte'),
        __('Bulk Artist & Album Fetch', 'gufte'),
        'manage_options',
        'bulk-artist-album-fetch',
        'arcuras_render_bulk_artist_album_fetch_page'
    );
}
add_action('admin_menu', 'arcuras_add_bulk_artist_album_fetch_page');

/**
 * Render the bulk artist & album fetch admin page
 */
function arcuras_render_bulk_artist_album_fetch_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Bulk Artist & Album Fetch', 'gufte'); ?></h1>
        <p><?php _e('This tool will fetch artist and album information from Apple Music API and assign them to taxonomy for all lyrics posts that have an Apple Music ID.', 'gufte'); ?></p>

        <div class="card" style="max-width: 800px;">
            <h2><?php _e('Scan Posts', 'gufte'); ?></h2>
            <p><?php _e('First, let\'s scan all posts to see how many have Apple Music IDs:', 'gufte'); ?></p>

            <button type="button" id="scan-posts" class="button button-primary">
                <span class="dashicons dashicons-search" style="margin-top: 3px;"></span>
                <?php _e('Scan Posts', 'gufte'); ?>
            </button>

            <div id="scan-results" style="margin-top: 20px;"></div>
        </div>

        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2><?php _e('Fetch Artist & Album Data', 'gufte'); ?></h2>
            <p><?php _e('Process posts one by one to fetch artist and album information from iTunes/Apple Music API:', 'gufte'); ?></p>

            <button type="button" id="start-fetch" class="button button-primary" disabled>
                <span class="dashicons dashicons-download" style="margin-top: 3px;"></span>
                <?php _e('Start Fetching Data', 'gufte'); ?>
            </button>

            <button type="button" id="stop-fetch" class="button" style="display: none;">
                <span class="dashicons dashicons-no" style="margin-top: 3px;"></span>
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
        .result-item strong {
            display: block;
            margin-bottom: 5px;
        }
        .result-item .details {
            font-size: 12px;
            color: #666;
        }
    </style>

    <script>
    jQuery(document).ready(function($) {
        let postsToProcess = [];
        let currentIndex = 0;
        let isProcessing = false;

        // Scan posts
        $('#scan-posts').on('click', function() {
            const button = $(this);
            button.prop('disabled', true).html('<span class="dashicons dashicons-update spin" style="margin-top: 3px;"></span> <?php _e('Scanning...', 'gufte'); ?>');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'scan_posts_for_artist_album',
                    nonce: '<?php echo wp_create_nonce('bulk_artist_album_fetch'); ?>'
                },
                success: function(response) {
                    button.prop('disabled', false).html('<span class="dashicons dashicons-search" style="margin-top: 3px;"></span> <?php _e('Scan Posts', 'gufte'); ?>');

                    if (response.success) {
                        postsToProcess = response.data.posts;

                        let html = '<div style="padding: 15px; background: #e7f3ff; border: 1px solid #2271b1; border-radius: 4px; margin-bottom: 15px;">';
                        html += '<p style="font-size: 16px; margin: 0 0 10px 0;"><strong>' + response.data.total + '</strong> <?php _e('posts found with Apple Music IDs', 'gufte'); ?></p>';

                        // Statistics
                        html += '<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 10px;">';

                        html += '<div style="background: #fff; padding: 10px; border-radius: 4px; text-align: center;">';
                        html += '<div style="font-size: 24px; font-weight: bold; color: #dc3232;">' + response.data.missing_artist + '</div>';
                        html += '<div style="font-size: 12px; color: #666;">🎤 Missing Artist</div>';
                        html += '</div>';

                        html += '<div style="background: #fff; padding: 10px; border-radius: 4px; text-align: center;">';
                        html += '<div style="font-size: 24px; font-weight: bold; color: #dc3232;">' + response.data.missing_album + '</div>';
                        html += '<div style="font-size: 12px; color: #666;">💿 Missing Album</div>';
                        html += '</div>';

                        html += '<div style="background: #fff; padding: 10px; border-radius: 4px; text-align: center;">';
                        html += '<div style="font-size: 24px; font-weight: bold; color: #d63638;">' + response.data.missing_both + '</div>';
                        html += '<div style="font-size: 12px; color: #666;">❌ Missing Both</div>';
                        html += '</div>';

                        html += '</div>';
                        html += '</div>';

                        // List posts with missing data
                        if (response.data.missing_artist > 0 || response.data.missing_album > 0) {
                            html += '<div style="margin-top: 15px; padding: 15px; background: #fff; border: 1px solid #ddd; border-radius: 4px; max-height: 300px; overflow-y: auto;">';
                            html += '<h3 style="margin: 0 0 10px 0; font-size: 14px;">Posts with Missing Data:</h3>';
                            html += '<ul style="margin: 0; padding-left: 20px;">';

                            response.data.posts.forEach(function(post) {
                                if (post.missing.length > 0) {
                                    html += '<li style="margin-bottom: 5px;">';
                                    html += '<strong>' + post.title + '</strong> - ';
                                    html += '<span style="color: #dc3232;">Missing: ' + post.missing.join(', ') + '</span>';
                                    html += '</li>';
                                }
                            });

                            html += '</ul>';
                            html += '</div>';
                        }

                        $('#scan-results').html(html);

                        if (postsToProcess.length > 0) {
                            $('#start-fetch').prop('disabled', false);
                        }
                    } else {
                        $('#scan-results').html('<div class="notice notice-error"><p>' + response.data + '</p></div>');
                    }
                },
                error: function() {
                    button.prop('disabled', false).html('<span class="dashicons dashicons-search" style="margin-top: 3px;"></span> <?php _e('Scan Posts', 'gufte'); ?>');
                    $('#scan-results').html('<div class="notice notice-error"><p><?php _e('Connection error occurred.', 'gufte'); ?></p></div>');
                }
            });
        });

        // Start fetching
        $('#start-fetch').on('click', function() {
            if (postsToProcess.length === 0) {
                alert('<?php _e('Please scan posts first.', 'gufte'); ?>');
                return;
            }

            isProcessing = true;
            currentIndex = 0;

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
                    $('#progress-status').html('✓ <?php _e('All posts processed!', 'gufte'); ?>');
                    $('#stop-fetch').hide();
                    $('#start-fetch').show();
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
                    action: 'fetch_artist_album_for_post',
                    post_id: post.id,
                    apple_music_id: post.apple_music_id,
                    nonce: '<?php echo wp_create_nonce('bulk_artist_album_fetch'); ?>'
                },
                success: function(response) {
                    let resultClass = 'success';
                    let resultHtml = '';

                    if (response.success) {
                        resultHtml = '<div class="result-item ' + resultClass + '">';
                        resultHtml += '<strong>✓ ' + post.title + '</strong>';
                        resultHtml += '<div class="details">';

                        if (response.data.artist) {
                            resultHtml += '🎤 Artist: ' + response.data.artist + '<br>';
                        }
                        if (response.data.album) {
                            resultHtml += '💿 Album: ' + response.data.album + '<br>';
                        }

                        resultHtml += '</div>';
                        resultHtml += '</div>';
                    } else {
                        resultClass = 'error';
                        resultHtml = '<div class="result-item ' + resultClass + '">';
                        resultHtml += '<strong>✗ ' + post.title + '</strong>';
                        resultHtml += '<div class="details">Error: ' + response.data + '</div>';
                        resultHtml += '</div>';
                    }

                    $('#fetch-results').prepend(resultHtml);

                    currentIndex++;
                    setTimeout(processNextPost, 500); // 500ms delay between requests
                },
                error: function() {
                    const resultHtml = '<div class="result-item error">';
                    resultHtml += '<strong>✗ ' + post.title + '</strong>';
                    resultHtml += '<div class="details">Connection error</div>';
                    resultHtml += '</div>';

                    $('#fetch-results').prepend(resultHtml);

                    currentIndex++;
                    setTimeout(processNextPost, 500);
                }
            });
        }
    });
    </script>

    <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .spin {
            animation: spin 1s linear infinite;
        }
    </style>
    <?php
}

/**
 * AJAX: Scan posts for Apple Music IDs
 */
function arcuras_ajax_scan_posts_for_artist_album() {
    check_ajax_referer('bulk_artist_album_fetch', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions.');
        return;
    }

    $args = array(
        'post_type' => 'lyrics',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_apple_music_id',
                'compare' => 'EXISTS'
            )
        ),
        'fields' => 'ids'
    );

    $query = new WP_Query($args);
    $posts_data = array();
    $missing_artist = 0;
    $missing_album = 0;
    $missing_both = 0;

    if ($query->have_posts()) {
        foreach ($query->posts as $post_id) {
            $apple_music_id = get_post_meta($post_id, '_apple_music_id', true);

            if (!empty($apple_music_id)) {
                // Check if artist and album exist
                $singers = get_the_terms($post_id, 'singer');
                $albums = get_the_terms($post_id, 'album');

                $has_singer = !empty($singers) && !is_wp_error($singers);
                $has_album = !empty($albums) && !is_wp_error($albums);

                $missing = array();
                if (!$has_singer) {
                    $missing[] = 'artist';
                    $missing_artist++;
                }
                if (!$has_album) {
                    $missing[] = 'album';
                    $missing_album++;
                }

                if (!$has_singer && !$has_album) {
                    $missing_both++;
                }

                $posts_data[] = array(
                    'id' => $post_id,
                    'title' => get_the_title($post_id),
                    'apple_music_id' => $apple_music_id,
                    'has_singer' => $has_singer,
                    'has_album' => $has_album,
                    'missing' => $missing
                );
            }
        }
    }

    wp_send_json_success(array(
        'total' => count($posts_data),
        'posts' => $posts_data,
        'missing_artist' => $missing_artist,
        'missing_album' => $missing_album,
        'missing_both' => $missing_both
    ));
}
add_action('wp_ajax_scan_posts_for_artist_album', 'arcuras_ajax_scan_posts_for_artist_album');

/**
 * AJAX: Fetch artist and album for a single post
 */
function arcuras_ajax_fetch_artist_album_for_post() {
    check_ajax_referer('bulk_artist_album_fetch', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions.');
        return;
    }

    $post_id = intval($_POST['post_id']);
    $apple_music_id = sanitize_text_field($_POST['apple_music_id']);

    if (empty($apple_music_id)) {
        wp_send_json_error('Apple Music ID is missing.');
        return;
    }

    // iTunes API kullan
    $api_url = "https://itunes.apple.com/lookup?id=" . urlencode($apple_music_id) . "&entity=song";

    $response = wp_remote_get($api_url, array(
        'timeout' => 30,
        'headers' => array(
            'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url()
        )
    ));

    if (is_wp_error($response)) {
        wp_send_json_error('Failed to connect to Apple Music API: ' . $response->get_error_message());
        return;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (empty($data) || !isset($data['results']) || empty($data['results'])) {
        wp_send_json_error('No data found for this Apple Music ID.');
        return;
    }

    $track_info = $data['results'][0];
    $result_data = array();

    // Artist bilgisini işle
    if (isset($track_info['artistName'])) {
        $artist_name = sanitize_text_field($track_info['artistName']);
        update_post_meta($post_id, '_artist_name', $artist_name);

        // Singer taxonomy'sine ekle
        $singer_term = term_exists($artist_name, 'singer');
        if (!$singer_term) {
            $singer_term = wp_insert_term($artist_name, 'singer');
        }

        if (!is_wp_error($singer_term)) {
            wp_set_object_terms($post_id, (int)$singer_term['term_id'], 'singer', false);
            $result_data['artist'] = $artist_name;
        }
    }

    // Album bilgisini işle
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
            $result_data['album'] = $album_name;
        }
    }

    if (empty($result_data)) {
        wp_send_json_error('No artist or album data found.');
        return;
    }

    wp_send_json_success($result_data);
}
add_action('wp_ajax_fetch_artist_album_for_post', 'arcuras_ajax_fetch_artist_album_for_post');
