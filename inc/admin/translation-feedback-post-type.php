<?php
/**
 * Translation Feedback custom post type and admin helpers.
 *
 * @package Gufte
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the translation feedback custom post type.
 */
function gufte_register_translation_feedback_cpt() {
    $labels = array(
        'name'                  => __('Translation Feedback', 'gufte'),
        'singular_name'         => __('Translation Feedback', 'gufte'),
        'menu_name'             => __('Feedback', 'gufte'),
        'name_admin_bar'        => __('Feedback', 'gufte'),
        'add_new'               => __('Add New', 'gufte'),
        'add_new_item'          => __('Add New Feedback', 'gufte'),
        'edit_item'             => __('Edit Feedback', 'gufte'),
        'new_item'              => __('New Feedback', 'gufte'),
        'view_item'             => __('View Feedback', 'gufte'),
        'search_items'          => __('Search Feedback', 'gufte'),
        'not_found'             => __('No feedback found.', 'gufte'),
        'not_found_in_trash'    => __('No feedback found in Trash.', 'gufte'),
        'all_items'             => __('All Feedback', 'gufte'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'menu_position'      => 25,
        'menu_icon'          => 'dashicons-feedback',
        'hierarchical'       => false,
        'supports'           => array('title'),
        'capability_type'    => 'post',
        'map_meta_cap'       => true,
        'capabilities'       => array(
            'create_posts' => 'do_not_allow',
        ),
        'rewrite'            => false,
        'query_var'          => false,
    );

    register_post_type('gufte_feedback', $args);
}
add_action('init', 'gufte_register_translation_feedback_cpt');

/**
 * Remove "Add New" links for the feedback post type.
 */
function gufte_feedback_remove_add_new() {
    remove_submenu_page('edit.php?post_type=gufte_feedback', 'post-new.php?post_type=gufte_feedback');
}
add_action('admin_menu', 'gufte_feedback_remove_add_new', 100);

/**
 * Hide the "Add New" button on the feedback list screen.
 */
function gufte_feedback_hide_add_new_button() {
    $screen = get_current_screen();
    if ($screen && 'gufte_feedback' === $screen->post_type) {
        echo '<style>.post-type-gufte_feedback .page-title-action{display:none;}</style>';
    }
}
add_action('admin_head', 'gufte_feedback_hide_add_new_button');

/**
 * Customize admin columns for the feedback post type.
 *
 * @param array $columns Existing columns.
 * @return array
 */
function gufte_feedback_admin_columns($columns) {
    $new = array();
    $new['cb'] = isset($columns['cb']) ? $columns['cb'] : '<input type="checkbox" />';
    $new['title'] = __('Feedback', 'gufte');
    $new['related_post'] = __('Song', 'gufte');
    $new['language'] = __('Language', 'gufte');
    $new['type'] = __('Type', 'gufte');
    $new['line_id'] = __('Line ID', 'gufte');
    $new['excerpt'] = __('Excerpt', 'gufte');
    $new['submitted_by'] = __('Submitted By', 'gufte');
    $new['date'] = isset($columns['date']) ? $columns['date'] : __('Date', 'gufte');

    return $new;
}
add_filter('manage_gufte_feedback_posts_columns', 'gufte_feedback_admin_columns');

/**
 * Render custom column values.
 *
 * @param string $column  Column name.
 * @param int    $post_id Current post ID.
 */
function gufte_feedback_admin_column_content($column, $post_id) {
    switch ($column) {
        case 'related_post':
            $related_id = intval(get_post_meta($post_id, '_gufte_feedback_post_id', true));
            if ($related_id && get_post($related_id)) {
                $edit_link = get_edit_post_link($related_id);
                $title = get_the_title($related_id);
                echo $edit_link
                    ? '<a href="' . esc_url($edit_link) . '">' . esc_html($title) . '</a>'
                    : esc_html($title);
            } else {
                echo '—';
            }
            break;

        case 'language':
            $lang = sanitize_text_field(get_post_meta($post_id, '_gufte_feedback_lang', true));
            if (empty($lang)) {
                echo '—';
                break;
            }

            $label = strtoupper($lang);
            if (function_exists('gufte_get_language_info')) {
                $info = gufte_get_language_info($lang);
                if (!empty($info['native_name'])) {
                    $label = $info['native_name'];
                }
                if (!empty($info['flag'])) {
                    echo esc_html($info['flag']) . ' ';
                }
            }

            echo esc_html($label);
            break;

        case 'type':
            $type = sanitize_text_field(get_post_meta($post_id, '_gufte_feedback_type', true));
            if ('negative' === $type) {
                echo '<span class="gufte-feedback-negative">' . esc_html__('Needs Improvement', 'gufte') . '</span>';
            } else {
                echo '<span class="gufte-feedback-positive">' . esc_html__('Helpful Translation', 'gufte') . '</span>';
            }
            break;

        case 'line_id':
            $line_id = sanitize_text_field(get_post_meta($post_id, '_gufte_feedback_line_id', true));
            echo $line_id ? esc_html($line_id) : '—';
            break;

        case 'excerpt':
            $excerpt = wp_strip_all_tags(get_post_meta($post_id, '_gufte_feedback_excerpt', true));
            if (empty($excerpt)) {
                echo '—';
            } else {
                echo esc_html(wp_trim_words($excerpt, 16, '…'));
            }
            break;

        case 'submitted_by':
            $user_id = intval(get_post_meta($post_id, '_gufte_feedback_user_id', true));
            $user_label = sanitize_text_field(get_post_meta($post_id, '_gufte_feedback_user_display', true));

            if ($user_id) {
                $user = get_user_by('id', $user_id);
                if ($user) {
                    $profile_link = get_edit_user_link($user_id);
                    $display = !empty($user_label) ? $user_label : $user->display_name;
                    echo '<a href="' . esc_url($profile_link) . '">' . esc_html($display) . '</a>';
                    break;
                }
            }

            echo $user_label ? esc_html($user_label) : '—';
            break;
    }
}
add_action('manage_gufte_feedback_posts_custom_column', 'gufte_feedback_admin_column_content', 10, 2);

/**
 * Add a direct link to the related post in row actions.
 *
 * @param array   $actions Existing row actions.
 * @param WP_Post $post    Current post object.
 * @return array
 */
function gufte_feedback_row_actions($actions, $post) {
    if ('gufte_feedback' !== $post->post_type) {
        return $actions;
    }

    $related_id = intval(get_post_meta($post->ID, '_gufte_feedback_post_id', true));
    if ($related_id && get_post($related_id)) {
        $actions['gufte_feedback_view_post'] = '<a href="' . esc_url(get_edit_post_link($related_id)) . '">' . esc_html__('Edit Song', 'gufte') . '</a>';
    }

    unset($actions['view']);
    unset($actions['inline hide-if-no-js']);

    return $actions;
}
add_filter('post_row_actions', 'gufte_feedback_row_actions', 10, 2);

/**
 * Persist feedback details to the custom post type.
 *
 * @param int   $post_id The related song/post ID.
 * @param array $entry   Feedback payload.
 */
function gufte_store_feedback_entry($post_id, $entry) {
    if (!post_type_exists('gufte_feedback') || empty($post_id)) {
        return;
    }

    $post_id = intval($post_id);
    $song_title = get_the_title($post_id);
    if ('' === $song_title) {
        $song_title = sprintf(__('Post #%d', 'gufte'), $post_id);
    }

    $type = isset($entry['type']) && 'negative' === $entry['type'] ? 'negative' : 'positive';
    $lang = isset($entry['lang']) ? sanitize_text_field($entry['lang']) : '';

    $type_label = ('negative' === $type)
        ? __('Needs Improvement', 'gufte')
        : __('Helpful Translation', 'gufte');

    $lang_label = $lang ? strtoupper($lang) : __('General', 'gufte');
    if ($lang && function_exists('gufte_get_language_info')) {
        $info = gufte_get_language_info($lang);
        if (!empty($info['native_name'])) {
            $lang_label = $info['native_name'];
        }
    }

    $title = sprintf('%s – %s (%s)', $type_label, $song_title, $lang_label);

    $feedback_post = array(
        'post_type'   => 'gufte_feedback',
        'post_status' => 'publish',
        'post_title'  => wp_strip_all_tags($title),
        'post_author' => !empty($entry['user_id']) ? intval($entry['user_id']) : 0,
    );

    if (!empty($entry['time'])) {
        $timestamp = intval($entry['time']);
        if (function_exists('wp_date')) {
            $feedback_post['post_date'] = wp_date('Y-m-d H:i:s', $timestamp);
        } else {
            $feedback_post['post_date'] = date_i18n('Y-m-d H:i:s', $timestamp);
        }
        $feedback_post['post_date_gmt'] = gmdate('Y-m-d H:i:s', $timestamp);
    }

    $grant_caps = function ($allcaps, $caps, $args, $user) {
        $needed = array('edit_posts', 'publish_posts', 'create_posts', 'edit_gufte_feedbacks', 'publish_gufte_feedbacks', 'edit_gufte_feedback');
        foreach ($caps as $cap) {
            if (in_array($cap, $needed, true)) {
                $allcaps[$cap] = true;
            }
        }
        return $allcaps;
    };
    add_filter('user_has_cap', $grant_caps, 10, 4);

    try {
        $feedback_id = wp_insert_post($feedback_post, true);
    } finally {
        remove_filter('user_has_cap', $grant_caps, 10);
    }

    if (is_wp_error($feedback_id)) {
        return;
    }

    update_post_meta($feedback_id, '_gufte_feedback_post_id', $post_id);
    update_post_meta($feedback_id, '_gufte_feedback_type', $type);
    update_post_meta($feedback_id, '_gufte_feedback_lang', $lang);
    update_post_meta($feedback_id, '_gufte_feedback_line_id', isset($entry['line_id']) ? sanitize_text_field($entry['line_id']) : '');
    update_post_meta($feedback_id, '_gufte_feedback_excerpt', isset($entry['excerpt']) ? wp_strip_all_tags($entry['excerpt']) : '');
    update_post_meta($feedback_id, '_gufte_feedback_user_id', !empty($entry['user_id']) ? intval($entry['user_id']) : 0);
    update_post_meta($feedback_id, '_gufte_feedback_user_display', isset($entry['user_display']) ? sanitize_text_field($entry['user_display']) : '');
    update_post_meta($feedback_id, '_gufte_feedback_submitted_at', current_time('timestamp'));
}

/**
 * Add subtle styling for feedback list.
 */
function gufte_feedback_admin_styles() {
    $screen = get_current_screen();
    if ($screen && 'gufte_feedback' === $screen->post_type) {
        echo '<style>
            .column-type .gufte-feedback-positive{color:#16a34a;font-weight:600;}
            .column-type .gufte-feedback-negative{color:#dc2626;font-weight:600;}
        </style>';
    }
}
add_action('admin_head', 'gufte_feedback_admin_styles');
