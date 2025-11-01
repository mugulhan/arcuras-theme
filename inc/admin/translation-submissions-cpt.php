<?php
/**
 * Translation Submissions Custom Post Type
 *
 * Manages user-submitted translations for lyrics.
 *
 * @package Gufte
 */

// Register Custom Post Type
function gufte_register_translation_submission_cpt() {
    $labels = array(
        'name'                  => 'Translation Submissions',
        'singular_name'         => 'Translation Submission',
        'menu_name'             => 'Translations',
        'all_items'             => 'All Submissions',
        'add_new'               => 'Add New',
        'add_new_item'          => 'Add New Submission',
        'edit_item'             => 'Review Submission',
        'view_item'             => 'View Submission',
        'search_items'          => 'Search Submissions',
        'not_found'             => 'No submissions found',
        'not_found_in_trash'    => 'No submissions found in Trash',
    );

    $args = array(
        'labels'                => $labels,
        'public'                => true,
        'publicly_queryable'    => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'query_var'             => true,
        'rewrite'               => array('slug' => 'translation-submission'),
        'capability_type'       => 'post',
        'has_archive'           => false,
        'hierarchical'          => false,
        'menu_position'         => 26,
        'menu_icon'             => 'dashicons-translation',
        'supports'              => array('title', 'author'),
    );

    register_post_type('translation_submission', $args);
}
add_action('init', 'gufte_register_translation_submission_cpt', 99);

/**
 * Add custom columns to admin list
 */
function gufte_translation_submission_columns($columns) {
    $new_columns = array(
        'cb' => $columns['cb'],
        'title' => __('Submission', 'gufte'),
        'original_song' => __('Original Song', 'gufte'),
        'target_language' => __('Target Language', 'gufte'),
        'contributor' => __('Contributor', 'gufte'),
        'status' => __('Status', 'gufte'),
        'date' => __('Submitted', 'gufte'),
        'actions' => __('Actions', 'gufte'),
    );
    return $new_columns;
}
add_filter('manage_translation_submission_posts_columns', 'gufte_translation_submission_columns');

/**
 * Populate custom columns
 */
function gufte_translation_submission_column_content($column, $post_id) {
    switch ($column) {
        case 'original_song':
            $original_post_id = get_post_meta($post_id, '_original_post_id', true);
            if ($original_post_id) {
                $song_title = get_the_title($original_post_id);
                $song_url = get_permalink($original_post_id);
                echo '<a href="' . esc_url($song_url) . '" target="_blank" class="row-title">';
                echo esc_html($song_title);
                echo '</a>';
            } else {
                echo '<span class="text-gray-500">—</span>';
            }
            break;

        case 'target_language':
            $target_lang = get_post_meta($post_id, '_target_language', true);
            $lang_names = array(
                'english' => 'English 🇬🇧',
                'spanish' => 'Español 🇪🇸',
                'turkish' => 'Türkçe 🇹🇷',
                'german' => 'Deutsch 🇩🇪',
                'french' => 'Français 🇫🇷',
                'italian' => 'Italiano 🇮🇹',
                'portuguese' => 'Português 🇵🇹',
                'russian' => 'Русский 🇷🇺',
                'arabic' => 'العربية 🇸🇦',
                'japanese' => '日本語 🇯🇵',
            );
            $lang_display = isset($lang_names[$target_lang]) ? $lang_names[$target_lang] : ucfirst($target_lang);
            echo '<strong>' . esc_html($lang_display) . '</strong>';
            break;

        case 'contributor':
            $author_id = get_post_field('post_author', $post_id);
            $author = get_userdata($author_id);
            if ($author) {
                echo get_avatar($author_id, 32, '', '', array('class' => 'rounded-full inline-block mr-2'));
                echo '<a href="' . esc_url(get_edit_user_link($author_id)) . '">';
                echo esc_html($author->display_name);
                echo '</a>';
            }
            break;

        case 'status':
            $post_status = get_post_status($post_id);
            $status_colors = array(
                'pending' => 'orange',
                'publish' => 'green',
                'draft' => 'gray',
                'trash' => 'red',
            );
            $status_labels = array(
                'pending' => __('⏳ Pending Review', 'gufte'),
                'publish' => __('✅ Approved', 'gufte'),
                'draft' => __('📝 Draft', 'gufte'),
                'trash' => __('🗑️ Rejected', 'gufte'),
            );
            $color = isset($status_colors[$post_status]) ? $status_colors[$post_status] : 'gray';
            $label = isset($status_labels[$post_status]) ? $status_labels[$post_status] : ucfirst($post_status);

            echo '<span style="padding: 4px 8px; border-radius: 4px; background: ' . $color . '; color: white; font-size: 11px; font-weight: 600;">';
            echo esc_html($label);
            echo '</span>';
            break;

        case 'actions':
            $edit_url = get_edit_post_link($post_id);
            echo '<a href="' . esc_url($edit_url) . '" class="button button-small">' . __('Review', 'gufte') . '</a>';
            break;
    }
}
add_action('manage_translation_submission_posts_custom_column', 'gufte_translation_submission_column_content', 10, 2);

/**
 * Make columns sortable
 */
function gufte_translation_submission_sortable_columns($columns) {
    $columns['original_song'] = 'original_song';
    $columns['target_language'] = 'target_language';
    $columns['contributor'] = 'author';
    return $columns;
}
add_filter('manage_edit-translation_submission_sortable_columns', 'gufte_translation_submission_sortable_columns');

/**
 * Custom meta box for review
 */
function gufte_translation_submission_meta_boxes() {
    add_meta_box(
        'translation_review',
        __('Translation Review', 'gufte'),
        'gufte_translation_review_meta_box_callback',
        'translation_submission',
        'normal',
        'high'
    );

    add_meta_box(
        'translation_actions',
        __('Quick Actions', 'gufte'),
        'gufte_translation_actions_meta_box_callback',
        'translation_submission',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'gufte_translation_submission_meta_boxes');

/**
 * Translation review meta box content
 */
function gufte_translation_review_meta_box_callback($post) {
    $original_post_id = get_post_meta($post->ID, '_original_post_id', true);
    $target_language = get_post_meta($post->ID, '_target_language', true);
    $translation_lines = get_post_meta($post->ID, '_translation_lines', true);
    $original_lines = get_post_meta($post->ID, '_original_lines', true);

    if (!$original_post_id) {
        echo '<p>' . __('Error: Original post not found.', 'gufte') . '</p>';
        return;
    }

    $song_title = get_the_title($original_post_id);
    $song_url = get_permalink($original_post_id);

    $lang_names = array(
        'english' => 'English',
        'spanish' => 'Español',
        'turkish' => 'Türkçe',
        'german' => 'Deutsch',
        'french' => 'Français',
        'italian' => 'Italiano',
        'portuguese' => 'Português',
        'russian' => 'Русский',
        'arabic' => 'العربية',
        'japanese' => '日本語',
    );
    $target_language_display = isset($lang_names[$target_language]) ? $lang_names[$target_language] : ucfirst($target_language);
    ?>

    <div style="margin: 20px 0;">
        <p style="font-size: 14px; margin-bottom: 10px;">
            <strong><?php _e('Original Song:', 'gufte'); ?></strong>
            <a href="<?php echo esc_url($song_url); ?>" target="_blank" style="color: #2271b1;">
                <?php echo esc_html($song_title); ?>
            </a>
        </p>
        <p style="font-size: 14px; margin-bottom: 20px;">
            <strong><?php _e('Target Language:', 'gufte'); ?></strong>
            <?php echo esc_html($target_language_display); ?>
        </p>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; background: #f9f9f9; padding: 20px; border-radius: 8px;">
            <!-- Original -->
            <div>
                <h4 style="margin: 0 0 15px; padding-bottom: 10px; border-bottom: 2px solid #ddd; color: #333;">
                    <?php _e('Original Lyrics', 'gufte'); ?>
                </h4>
                <div style="max-height: 500px; overflow-y: auto;">
                    <?php
                    if (!empty($original_lines) && is_array($original_lines)) :
                        foreach ($original_lines as $index => $line) :
                            $line_number = $index + 1;
                    ?>
                    <div style="margin-bottom: 10px; padding: 10px; background: white; border-radius: 4px; border-left: 3px solid #999;">
                        <span style="display: inline-block; width: 24px; height: 24px; line-height: 24px; text-align: center; background: #e0e0e0; color: #666; border-radius: 50%; font-size: 12px; font-weight: bold; margin-right: 10px;">
                            <?php echo $line_number; ?>
                        </span>
                        <span style="color: #333;"><?php echo esc_html($line); ?></span>
                    </div>
                    <?php
                        endforeach;
                    else :
                        echo '<p style="color: #999; font-style: italic;">' . __('No original lyrics found.', 'gufte') . '</p>';
                    endif;
                    ?>
                </div>
            </div>

            <!-- Translation -->
            <div>
                <h4 style="margin: 0 0 15px; padding-bottom: 10px; border-bottom: 2px solid #2271b1; color: #2271b1;">
                    <?php _e('Submitted Translation', 'gufte'); ?>
                </h4>
                <div style="max-height: 500px; overflow-y: auto;">
                    <?php
                    if (!empty($translation_lines) && is_array($translation_lines)) :
                        foreach ($translation_lines as $index => $line) :
                            $line_number = $index + 1;
                            $is_empty = empty(trim($line));
                    ?>
                    <div style="margin-bottom: 10px; padding: 10px; background: <?php echo $is_empty ? '#fff3cd' : 'white'; ?>; border-radius: 4px; border-left: 3px solid <?php echo $is_empty ? '#ffc107' : '#2271b1'; ?>;">
                        <span style="display: inline-block; width: 24px; height: 24px; line-height: 24px; text-align: center; background: #2271b1; color: white; border-radius: 50%; font-size: 12px; font-weight: bold; margin-right: 10px;">
                            <?php echo $line_number; ?>
                        </span>
                        <span style="color: #333;">
                            <?php
                            if ($is_empty) {
                                echo '<em style="color: #856404;">' . __('(Empty line)', 'gufte') . '</em>';
                            } else {
                                echo esc_html($line);
                            }
                            ?>
                        </span>
                    </div>
                    <?php
                        endforeach;
                    else :
                        echo '<p style="color: #999; font-style: italic;">' . __('No translation found.', 'gufte') . '</p>';
                    endif;
                    ?>
                </div>
            </div>
        </div>
    </div>

    <?php
}

/**
 * Quick actions meta box
 */
function gufte_translation_actions_meta_box_callback($post) {
    $original_post_id = get_post_meta($post->ID, '_original_post_id', true);
    $target_language = get_post_meta($post->ID, '_target_language', true);

    wp_nonce_field('approve_translation_' . $post->ID, 'approve_translation_nonce');
    ?>

    <div style="padding: 10px 0;">
        <p style="margin-bottom: 15px; padding: 10px; background: #e7f3ff; border-left: 4px solid #2271b1; font-size: 13px;">
            <strong><?php _e('Review this translation and choose an action:', 'gufte'); ?></strong>
        </p>

        <div style="margin-bottom: 10px;">
            <button type="button" id="approve-translation-btn" class="button button-primary button-large" style="width: 100%; margin-bottom: 5px;">
                ✅ <?php _e('Approve & Add to Song', 'gufte'); ?>
            </button>
        </div>

        <div style="margin-bottom: 10px;">
            <button type="button" id="reject-translation-btn" class="button button-large" style="width: 100%; background: #dc3232; color: white; border-color: #dc3232;">
                ❌ <?php _e('Reject Translation', 'gufte'); ?>
            </button>
        </div>

        <p style="font-size: 12px; color: #666; margin-top: 15px;">
            <?php _e('Approving will automatically add this translation to the original song post.', 'gufte'); ?>
        </p>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('#approve-translation-btn').on('click', function() {
            if (!confirm('<?php _e('Are you sure you want to approve this translation? It will be added to the song.', 'gufte'); ?>')) {
                return;
            }

            const data = {
                action: 'approve_translation_submission',
                submission_id: <?php echo $post->ID; ?>,
                nonce: $('#approve_translation_nonce').val()
            };

            $.post(ajaxurl, data, function(response) {
                if (response.success) {
                    alert('<?php _e('Translation approved successfully!', 'gufte'); ?>');
                    window.location.reload();
                } else {
                    alert('<?php _e('Error:', 'gufte'); ?> ' + response.data.message);
                }
            });
        });

        $('#reject-translation-btn').on('click', function() {
            if (!confirm('<?php _e('Are you sure you want to reject this translation?', 'gufte'); ?>')) {
                return;
            }

            const data = {
                action: 'reject_translation_submission',
                submission_id: <?php echo $post->ID; ?>,
                nonce: $('#approve_translation_nonce').val()
            };

            $.post(ajaxurl, data, function(response) {
                if (response.success) {
                    alert('<?php _e('Translation rejected.', 'gufte'); ?>');
                    window.location.href = 'edit.php?post_type=translation_submission';
                } else {
                    alert('<?php _e('Error:', 'gufte'); ?> ' + response.data.message);
                }
            });
        });
    });
    </script>
    <?php
}

/**
 * AJAX: Approve translation
 */
function gufte_ajax_approve_translation() {
    check_ajax_referer('approve_translation_' . $_POST['submission_id'], 'nonce');

    if (!current_user_can('edit_posts')) {
        wp_send_json_error(array('message' => __('Permission denied.', 'gufte')));
    }

    $submission_id = intval($_POST['submission_id']);
    $original_post_id = get_post_meta($submission_id, '_original_post_id', true);
    $target_language = get_post_meta($submission_id, '_target_language', true);
    $translation_lines = get_post_meta($submission_id, '_translation_lines', true);
    $original_lines = get_post_meta($submission_id, '_original_lines', true);

    if (!$original_post_id || !$target_language || empty($translation_lines)) {
        wp_send_json_error(array('message' => __('Invalid submission data.', 'gufte')));
    }

    // Get current post content
    $post_content = get_post_field('post_content', $original_post_id);

    // Add translation to table
    $updated_content = gufte_add_translation_to_table($post_content, $target_language, $translation_lines, $original_lines);

    if ($updated_content) {
        // Update post content
        wp_update_post(array(
            'ID' => $original_post_id,
            'post_content' => $updated_content,
        ));

        // Mark submission as approved
        wp_update_post(array(
            'ID' => $submission_id,
            'post_status' => 'publish',
        ));

        wp_send_json_success(array('message' => __('Translation approved and added to song.', 'gufte')));
    } else {
        wp_send_json_error(array('message' => __('Failed to add translation to song.', 'gufte')));
    }
}
add_action('wp_ajax_approve_translation_submission', 'gufte_ajax_approve_translation');

/**
 * AJAX: Reject translation
 */
function gufte_ajax_reject_translation() {
    check_ajax_referer('approve_translation_' . $_POST['submission_id'], 'nonce');

    if (!current_user_can('edit_posts')) {
        wp_send_json_error(array('message' => __('Permission denied.', 'gufte')));
    }

    $submission_id = intval($_POST['submission_id']);

    // Move to trash
    wp_trash_post($submission_id);

    wp_send_json_success(array('message' => __('Translation rejected.', 'gufte')));
}
add_action('wp_ajax_reject_translation_submission', 'gufte_ajax_reject_translation');

/**
 * Add translation column to existing table
 */
function gufte_add_translation_to_table($content, $language, $translation_lines, $original_lines) {
    // Parse existing table
    if (!preg_match('/<table[^>]*>(.*?)<\/table>/is', $content, $table_match)) {
        return false;
    }

    $table_content = $table_match[1];

    // Check if language column already exists
    if (stripos($table_content, $language) !== false) {
        // Language already exists, update it
        // For simplicity, we'll return false to prevent duplicates
        // In a real scenario, you'd update the existing column
        return false;
    }

    // Extract header row
    preg_match('/<thead[^>]*>(.*?)<\/thead>/is', $table_content, $thead_match);
    $thead = $thead_match[1];

    // Add new language to header
    $new_th = '<th>' . esc_html(ucfirst($language)) . '</th>';
    $thead = str_replace('</tr>', $new_th . '</tr>', $thead);

    // Extract body rows
    preg_match('/<tbody[^>]*>(.*?)<\/tbody>/is', $table_content, $tbody_match);
    $tbody = $tbody_match[1];

    // Add translation cells to each row
    preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $tbody, $row_matches);

    $new_rows = array();
    foreach ($row_matches[1] as $index => $row_content) {
        $translation_text = isset($translation_lines[$index]) ? esc_html($translation_lines[$index]) : '';
        $new_td = '<td>' . $translation_text . '</td>';
        $new_row = str_replace('</tr>', $new_td . '</tr>', '<tr>' . $row_content . '</tr>');
        $new_rows[] = $new_row;
    }

    $new_tbody = '<tbody>' . implode('', $new_rows) . '</tbody>';

    // Reconstruct table
    $new_table = '<table class="lyrics-table"><thead>' . $thead . '</thead>' . $new_tbody . '</table>';

    // Replace in content
    $new_content = str_replace($table_match[0], $new_table, $content);

    return $new_content;
}
