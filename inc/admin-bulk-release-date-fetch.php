<?php
/**
 * Bulk Apple Music Release Date Fetch Tool
 * Fetches release dates for all lyrics posts using Apple Music ID
 *
 * @package Gufte
 * @since 2.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add admin menu page for bulk release date fetch
 */
function arcuras_add_bulk_release_date_fetch_page() {
    add_submenu_page(
        'tools.php',
        __('Bulk Release Date Fetch', 'gufte'),
        __('Bulk Release Date Fetch', 'gufte'),
        'manage_options',
        'bulk-release-date-fetch',
        'arcuras_render_bulk_release_date_fetch_page'
    );
}
add_action('admin_menu', 'arcuras_add_bulk_release_date_fetch_page');

/**
 * Render the bulk release date fetch admin page
 */
function arcuras_render_bulk_release_date_fetch_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Bulk Apple Music Data Fetch', 'gufte'); ?></h1>
        <p><?php _e('This tool will fetch and set release dates, track numbers, and genres for all lyrics posts that have an Apple Music ID.', 'gufte'); ?></p>

        <div class="card" style="max-width: 800px;">
            <h2><?php _e('Scan Posts', 'gufte'); ?></h2>
            <p><?php _e('First, let\'s scan all posts to see how many need Apple Music data (release date or track number):', 'gufte'); ?></p>

            <button type="button" id="scan-posts" class="button button-primary">
                <?php _e('Scan Posts', 'gufte'); ?>
            </button>

            <div id="scan-results" style="margin-top: 20px;"></div>
        </div>

        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2><?php _e('Fetch Apple Music Data', 'gufte'); ?></h2>
            <p><?php _e('Process posts one by one to fetch missing data (release date, track number, genre):', 'gufte'); ?></p>

            <button type="button" id="start-fetch" class="button button-primary" disabled>
                <?php _e('Start Fetching Data', 'gufte'); ?>
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

        // Scan posts button
        $('#scan-posts').on('click', function() {
            const button = $(this);
            button.prop('disabled', true).text('<?php _e('Scanning...', 'gufte'); ?>');
            $('#scan-results').html('<p><?php _e('Scanning posts...', 'gufte'); ?></p>');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'arcuras_scan_posts_for_release_dates',
                    nonce: '<?php echo wp_create_nonce('arcuras_bulk_release_date_fetch'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        let html = '<div style="background: #fff; padding: 15px; border: 1px solid #c3c4c7; border-radius: 4px;">';
                        html += '<h3><?php _e('Scan Results:', 'gufte'); ?></h3>';
                        html += '<ul style="list-style: disc; padding-left: 20px;">';
                        html += '<li><strong><?php _e('Total lyrics posts:', 'gufte'); ?></strong> ' + data.total + '</li>';
                        html += '<li><strong><?php _e('Posts with Apple Music ID:', 'gufte'); ?></strong> ' + data.with_apple_music_id + '</li>';
                        html += '<li><strong><?php _e('Posts with release date:', 'gufte'); ?></strong> ' + data.with_release_date + '</li>';
                        html += '<li><strong><?php _e('Posts with track number:', 'gufte'); ?></strong> ' + data.with_track_number + '</li>';
                        html += '<li style="color: #d63638;"><strong><?php _e('Posts needing data (release date or track number):', 'gufte'); ?></strong> ' + data.need_fetch + '</li>';
                        html += '</ul>';
                        html += '</div>';

                        $('#scan-results').html(html);

                        if (data.need_fetch > 0) {
                            postsToProcess = data.posts;
                            $('#start-fetch').prop('disabled', false);
                        } else {
                            $('#scan-results').append('<p style="color: #46b450; font-weight: bold;"><?php _e('All posts already have complete data!', 'gufte'); ?></p>');
                        }
                    } else {
                        $('#scan-results').html('<p style="color: #d63638;"><?php _e('Error:', 'gufte'); ?> ' + response.data + '</p>');
                    }

                    button.prop('disabled', false).text('<?php _e('Scan Posts', 'gufte'); ?>');
                },
                error: function() {
                    $('#scan-results').html('<p style="color: #d63638;"><?php _e('Error scanning posts.', 'gufte'); ?></p>');
                    button.prop('disabled', false).text('<?php _e('Scan Posts', 'gufte'); ?>');
                }
            });
        });

        // Start fetch button
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

        // Stop fetch button
        $('#stop-fetch').on('click', function() {
            isProcessing = false;
            $(this).hide();
            $('#start-fetch').show();
            $('#progress-status').text('<?php _e('Stopped by user', 'gufte'); ?>');
        });

        function processNextPost() {
            if (!isProcessing || currentIndex >= postsToProcess.length) {
                if (currentIndex >= postsToProcess.length) {
                    $('#progress-status').html('<?php _e('✅ All done! Processed', 'gufte'); ?> ' + postsToProcess.length + ' <?php _e('posts.', 'gufte'); ?>');
                    $('#stop-fetch').hide();
                    $('#start-fetch').show().prop('disabled', true);
                }
                return;
            }

            const post = postsToProcess[currentIndex];
            const progress = Math.round(((currentIndex + 1) / postsToProcess.length) * 100);

            // Update progress bar
            $('#progress-bar').css('width', progress + '%');
            $('#progress-text').text(progress + '%');
            $('#progress-status').text('<?php _e('Processing:', 'gufte'); ?> ' + post.title + ' (' + (currentIndex + 1) + '/' + postsToProcess.length + ')');

            // Fetch release date for this post
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'arcuras_fetch_single_release_date',
                    nonce: '<?php echo wp_create_nonce('arcuras_bulk_release_date_fetch'); ?>',
                    post_id: post.id
                },
                success: function(response) {
                    let resultClass = 'error';
                    let resultIcon = '❌';
                    let resultText = '';

                    if (response.success) {
                        resultClass = 'success';
                        resultIcon = '✅';
                        resultText = '<strong>' + post.title + '</strong><br>' +
                                   '📅 Release Date: ' + response.data.release_date + '<br>' +
                                   '🎵 Genre: ' + response.data.genre;
                        if (response.data.track_number > 0) {
                            resultText += '<br>🔢 Track #' + response.data.track_number;
                        }
                    } else {
                        resultText = '<strong>' + post.title + '</strong><br>' + response.data;
                    }

                    const resultHtml = '<div class="result-item ' + resultClass + '">' +
                                     resultIcon + ' ' + resultText +
                                     '</div>';

                    $('#fetch-results').prepend(resultHtml);

                    currentIndex++;

                    // Wait 1.5 seconds before next request to avoid rate limiting
                    setTimeout(processNextPost, 1500);
                },
                error: function() {
                    const resultHtml = '<div class="result-item error">❌ <strong>' + post.title + '</strong><br><?php _e('AJAX error occurred', 'gufte'); ?></div>';
                    $('#fetch-results').prepend(resultHtml);

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
 * AJAX handler: Scan posts for missing release dates
 */
function arcuras_ajax_scan_posts_for_release_dates() {
    check_ajax_referer('arcuras_bulk_release_date_fetch', 'nonce');

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

    $total = count($all_posts);
    $with_apple_music_id = 0;
    $with_release_date = 0;
    $with_track_number = 0;
    $need_fetch = 0;
    $posts_to_process = array();

    foreach ($all_posts as $post_id) {
        $apple_music_id = get_post_meta($post_id, '_apple_music_id', true);
        $apple_music_url = get_post_meta($post_id, 'apple_music_url', true);
        $release_date = get_post_meta($post_id, '_release_date', true);
        $track_number = get_post_meta($post_id, '_track_number', true);

        $has_apple_music = !empty($apple_music_id) || !empty($apple_music_url);

        if ($has_apple_music) {
            $with_apple_music_id++;
        }

        if (!empty($release_date)) {
            $with_release_date++;
        }

        if (!empty($track_number)) {
            $with_track_number++;
        }

        // Need fetch: has Apple Music ID but missing release date OR track number
        if ($has_apple_music && (empty($release_date) || empty($track_number))) {
            $need_fetch++;
            $posts_to_process[] = array(
                'id' => $post_id,
                'title' => get_the_title($post_id),
                'apple_music_id' => $apple_music_id,
                'apple_music_url' => $apple_music_url
            );
        }
    }

    wp_send_json_success(array(
        'total' => $total,
        'with_apple_music_id' => $with_apple_music_id,
        'with_release_date' => $with_release_date,
        'with_track_number' => $with_track_number,
        'need_fetch' => $need_fetch,
        'posts' => $posts_to_process
    ));
}
add_action('wp_ajax_arcuras_scan_posts_for_release_dates', 'arcuras_ajax_scan_posts_for_release_dates');

/**
 * AJAX handler: Fetch single post release date from Apple Music
 */
function arcuras_ajax_fetch_single_release_date() {
    check_ajax_referer('arcuras_bulk_release_date_fetch', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    $post_id = intval($_POST['post_id']);

    if (!$post_id) {
        wp_send_json_error('Invalid post ID');
    }

    // Get Apple Music ID
    $apple_music_id = get_post_meta($post_id, '_apple_music_id', true);
    $apple_music_url = get_post_meta($post_id, 'apple_music_url', true);

    // Extract ID from URL if no direct ID
    if (empty($apple_music_id) && !empty($apple_music_url)) {
        if (preg_match('/\/id(\d+)/', $apple_music_url, $matches)) {
            $apple_music_id = $matches[1];
        }
    }

    if (empty($apple_music_id)) {
        wp_send_json_error('No Apple Music ID found');
    }

    // Fetch from iTunes API
    $api_url = 'https://itunes.apple.com/lookup?id=' . urlencode($apple_music_id) . '&entity=song';
    $response = wp_remote_get($api_url, array(
        'timeout' => 15,
        'headers' => array(
            'User-Agent' => 'WordPress/' . get_bloginfo('version')
        )
    ));

    if (is_wp_error($response)) {
        wp_send_json_error('API request failed: ' . $response->get_error_message());
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (empty($data['results']) || !is_array($data['results'])) {
        wp_send_json_error('No results found from Apple Music API');
    }

    $track_info = $data['results'][0];

    // Extract release date
    if (!isset($track_info['releaseDate'])) {
        wp_send_json_error('Release date not available for this track');
    }

    $release_date = date('Y-m-d', strtotime($track_info['releaseDate']));
    $genre = isset($track_info['primaryGenreName']) ? $track_info['primaryGenreName'] : '';
    $track_number = isset($track_info['trackNumber']) ? intval($track_info['trackNumber']) : 0;

    // Save to post meta
    update_post_meta($post_id, '_release_date', $release_date);

    if (!empty($genre)) {
        update_post_meta($post_id, '_music_genre', $genre);
    }

    if ($track_number > 0) {
        update_post_meta($post_id, '_track_number', $track_number);
    }

    // Format for display
    $formatted_date = date('F j, Y', strtotime($release_date));

    wp_send_json_success(array(
        'release_date' => $formatted_date,
        'genre' => $genre,
        'track_number' => $track_number,
        'raw_date' => $release_date
    ));
}
add_action('wp_ajax_arcuras_fetch_single_release_date', 'arcuras_ajax_fetch_single_release_date');
