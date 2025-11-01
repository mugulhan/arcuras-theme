<?php
/**
 * Gemini Vision OCR - Extract text from images using Google Gemini
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX handler for Gemini OCR
 */
function arcuras_gemini_ocr() {
    // Verify nonce
    check_ajax_referer('arcuras_gemini_ocr_nonce', 'nonce');

    // Check permissions
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Unauthorized');
    }

    // Get Gemini API key
    $gemini_api_key = get_option('arcuras_gemini_api_key');

    if (empty($gemini_api_key)) {
        wp_send_json_error('Gemini API key not configured. Please add it in Theme Settings → AI Translation.');
    }

    // Get image data
    $image_data = isset($_POST['image']) ? $_POST['image'] : '';

    if (empty($image_data)) {
        wp_send_json_error('No image data provided');
    }

    // Remove data:image/...;base64, prefix if present
    if (strpos($image_data, 'base64,') !== false) {
        $image_data = explode('base64,', $image_data)[1];
    }

    // Validate base64
    if (!base64_decode($image_data, true)) {
        wp_send_json_error('Invalid image data');
    }

    // Prepare Gemini Vision API request
    $api_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key=' . $gemini_api_key;

    $prompt = "Extract all text from this image. This is a song lyrics image. Please:
1. Extract ALL text exactly as it appears
2. Preserve line breaks between lines of lyrics
3. Add an empty line between different sections (verses, chorus, bridge, etc.) if there's visible spacing
4. Do NOT add any explanations, labels, or extra text
5. Do NOT translate or modify the text
6. Output ONLY the extracted lyrics text

Important: If the image contains structured lyrics with clear sections, preserve that structure with empty lines between sections.";

    $response = wp_remote_post($api_url, array(
        'timeout' => 60,
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array(
            'contents' => array(
                array(
                    'parts' => array(
                        array(
                            'text' => $prompt
                        ),
                        array(
                            'inline_data' => array(
                                'mime_type' => 'image/jpeg',
                                'data' => $image_data
                            )
                        )
                    )
                )
            ),
            'generationConfig' => array(
                'temperature' => 0.2,
                'maxOutputTokens' => 2000,
            )
        )),
    ));

    if (is_wp_error($response)) {
        wp_send_json_error('API request failed: ' . $response->get_error_message());
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($body['error'])) {
        wp_send_json_error('Gemini API error: ' . $body['error']['message']);
    }

    if (!isset($body['candidates'][0]['content']['parts'][0]['text'])) {
        wp_send_json_error('Invalid API response');
    }

    $extracted_text = trim($body['candidates'][0]['content']['parts'][0]['text']);

    wp_send_json_success(array(
        'text' => $extracted_text,
        'model' => 'gemini-2.0-flash-exp',
        'provider' => 'Gemini Vision',
    ));
}
add_action('wp_ajax_arcuras_gemini_ocr', 'arcuras_gemini_ocr');

/**
 * Enqueue Gemini OCR scripts
 */
function arcuras_enqueue_gemini_ocr_scripts($hook) {
    // Only load in block editor
    if ($hook !== 'post.php' && $hook !== 'post-new.php') {
        return;
    }

    // Pass nonce to JavaScript
    wp_localize_script('wp-blocks', 'arcurasGeminiOCR', array(
        'nonce' => wp_create_nonce('arcuras_gemini_ocr_nonce'),
        'ajax_url' => admin_url('admin-ajax.php'),
        'has_api_key' => !empty(get_option('arcuras_gemini_api_key')),
    ));
}
add_action('admin_enqueue_scripts', 'arcuras_enqueue_gemini_ocr_scripts');
