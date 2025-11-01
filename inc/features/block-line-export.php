<?php
/**
 * Block-Based Lyric Line Export Feature
 *
 * Provides per-line image export functionality for the new Gutenberg block system
 * with kebab menu dropdowns and canvas-based image generation
 *
 * @package Gufte
 * @since 1.9.3
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue necessary scripts and styles for line export feature
 */
function arcuras_enqueue_line_export_assets() {
    // Only load on single lyrics posts
    if (!is_singular('lyrics')) {
        return;
    }

    // Register the frontend JavaScript first
    wp_register_script(
        'arcuras-line-export',
        get_template_directory_uri() . '/assets/js/line-export.js',
        array(),
        GUFTE_VERSION,
        true
    );

    // Pass PHP data to JavaScript (must be after register, before enqueue)
    $featured_image = get_the_post_thumbnail_url(get_the_ID(), 'medium');

    // Get singer/artist name from taxonomy
    $singers = wp_get_post_terms(get_the_ID(), 'singer', array('fields' => 'names'));
    $artist_name = !empty($singers) ? $singers[0] : '';

    wp_localize_script('arcuras-line-export', 'arcurasLineExport', array(
        'siteUrl' => get_site_url(),
        'siteName' => get_bloginfo('name'),
        'postTitle' => get_the_title(),
        'artistName' => $artist_name,
        'featuredImage' => $featured_image ? $featured_image : '',
        'nonce' => wp_create_nonce('wp_rest'),
        'isUserLoggedIn' => is_user_logged_in(),
    ));

    // Now enqueue it
    wp_enqueue_script('arcuras-line-export');

    // Enqueue the CSS
    wp_enqueue_style(
        'arcuras-line-export',
        get_template_directory_uri() . '/assets/css/line-export.css',
        array(),
        GUFTE_VERSION
    );

    // Enqueue Music Video Modal assets
    wp_enqueue_script(
        'arcuras-music-video-modal',
        get_template_directory_uri() . '/assets/js/music-video-modal.js',
        array(),
        GUFTE_VERSION,
        true
    );

    wp_enqueue_style(
        'arcuras-music-video-modal',
        get_template_directory_uri() . '/assets/css/music-video-modal.css',
        array(),
        GUFTE_VERSION
    );
}
add_action('wp_enqueue_scripts', 'arcuras_enqueue_line_export_assets');
