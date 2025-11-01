<?php
/**
 * Lyrics Custom Post Type
 * Registers 'lyrics' post type for song lyrics
 *
 * @package Gufte
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Lyrics Post Type
 */
function arcuras_register_lyrics_post_type() {
    $labels = array(
        'name'                  => _x('Lyrics', 'Post type general name', 'gufte'),
        'singular_name'         => _x('Lyric', 'Post type singular name', 'gufte'),
        'menu_name'             => _x('Lyrics', 'Admin Menu text', 'gufte'),
        'name_admin_bar'        => _x('Lyric', 'Add New on Toolbar', 'gufte'),
        'add_new'               => __('Add New', 'gufte'),
        'add_new_item'          => __('Add New Lyric', 'gufte'),
        'new_item'              => __('New Lyric', 'gufte'),
        'edit_item'             => __('Edit Lyric', 'gufte'),
        'view_item'             => __('View Lyric', 'gufte'),
        'all_items'             => __('All Lyrics', 'gufte'),
        'search_items'          => __('Search Lyrics', 'gufte'),
        'parent_item_colon'     => __('Parent Lyrics:', 'gufte'),
        'not_found'             => __('No lyrics found.', 'gufte'),
        'not_found_in_trash'    => __('No lyrics found in Trash.', 'gufte'),
        'featured_image'        => _x('Featured Image', 'Overrides the "Featured Image" phrase', 'gufte'),
        'set_featured_image'    => _x('Set featured image', 'Overrides the "Set featured image" phrase', 'gufte'),
        'remove_featured_image' => _x('Remove featured image', 'Overrides the "Remove featured image" phrase', 'gufte'),
        'use_featured_image'    => _x('Use as featured image', 'Overrides the "Use as featured image" phrase', 'gufte'),
        'archives'              => _x('Lyric archives', 'The post type archive label used in nav menus', 'gufte'),
        'insert_into_item'      => _x('Insert into lyric', 'Overrides the "Insert into post" phrase', 'gufte'),
        'uploaded_to_this_item' => _x('Uploaded to this lyric', 'Overrides the "Uploaded to this post" phrase', 'gufte'),
        'filter_items_list'     => _x('Filter lyrics list', 'Screen reader text for the filter links', 'gufte'),
        'items_list_navigation' => _x('Lyrics list navigation', 'Screen reader text for the pagination', 'gufte'),
        'items_list'            => _x('Lyrics list', 'Screen reader text for the items list', 'gufte'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'lyrics'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-format-audio',
        'show_in_rest'       => true, // Enable Gutenberg editor
        'supports'           => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'revisions', 'custom-fields'),
        // Don't use default category/tags - we have custom taxonomies (singer, album, language, etc.)
    );

    register_post_type('lyrics', $args);
}
add_action('init', 'arcuras_register_lyrics_post_type');

/**
 * Register existing taxonomies to lyrics post type
 */
function arcuras_register_lyrics_taxonomies() {
    // Register existing custom taxonomies to lyrics post type
    $taxonomies = array('singer', 'album', 'songwriter', 'producer', 'original_language', 'translated_language');

    foreach ($taxonomies as $taxonomy) {
        if (taxonomy_exists($taxonomy)) {
            register_taxonomy_for_object_type($taxonomy, 'lyrics');
        }
    }
}
add_action('init', 'arcuras_register_lyrics_taxonomies', 20);

/**
 * Add meta boxes to lyrics post type
 */
function arcuras_add_lyrics_meta_boxes() {
    $meta_boxes = array(
        'gufte_music_cover_art',
        'gufte_music_links',
        'gufte_music_video',
        'gufte_release_date'
    );

    foreach ($meta_boxes as $meta_box_id) {
        // Get the meta box if it was added to 'post'
        global $wp_meta_boxes;
        if (isset($wp_meta_boxes['post']) && !empty($wp_meta_boxes['post'])) {
            foreach ($wp_meta_boxes['post'] as $context => $priority_boxes) {
                foreach ($priority_boxes as $priority => $boxes) {
                    if (isset($boxes[$meta_box_id])) {
                        $box = $boxes[$meta_box_id];
                        add_meta_box(
                            $box['id'],
                            $box['title'],
                            $box['callback'],
                            'lyrics',
                            $context,
                            $priority,
                            isset($box['args']) ? $box['args'] : null
                        );
                    }
                }
            }
        }
    }
}
add_action('add_meta_boxes', 'arcuras_add_lyrics_meta_boxes', 20);

/**
 * Flush rewrite rules on theme activation
 */
function arcuras_lyrics_flush_rewrites() {
    arcuras_register_lyrics_post_type();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'arcuras_lyrics_flush_rewrites');
