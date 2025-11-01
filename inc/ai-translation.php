<?php
/**
 * AI Translation Functions
 * DeepSeek API integration for lyrics translation
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX handler for AI translation
 */
function arcuras_ai_translate_lyrics() {
    // Verify nonce
    check_ajax_referer('arcuras_ai_translate_nonce', 'nonce');

    // Check permissions
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Unauthorized');
    }

    // Get parameters
    $text = isset($_POST['text']) ? wp_unslash($_POST['text']) : '';
    $target_lang = isset($_POST['target_lang']) ? sanitize_text_field($_POST['target_lang']) : 'tr';
    $source_lang = isset($_POST['source_lang']) ? sanitize_text_field($_POST['source_lang']) : 'en';

    if (empty($text)) {
        wp_send_json_error('No text provided');
    }

    // Get Gemini API key
    $gemini_api_key = get_option('arcuras_gemini_api_key');

    if (empty($gemini_api_key)) {
        wp_send_json_error('Gemini API key not configured. Please add it in Theme Settings → AI Translation.');
    }

    // Get selected model from settings
    $selected_model = get_option('arcuras_ai_model', 'gemini-2.0-flash-exp');

    // Available Gemini models
    $gemini_models = array(
        'gemini-2.0-flash-exp' => 'Gemini 2.0 Flash',
        'gemini-1.5-pro' => 'Gemini 1.5 Pro',
        'gemini-1.5-flash' => 'Gemini 1.5 Flash',
    );

    // Validate model
    if (!isset($gemini_models[$selected_model])) {
        $selected_model = 'gemini-2.0-flash-exp';
    }

    // Language mapping
    $lang_names = array(
        'en' => 'English',
        'tr' => 'Turkish',
        'es' => 'Spanish',
        'fr' => 'French',
        'de' => 'German',
        'it' => 'Italian',
        'pt' => 'Portuguese',
        'ar' => 'Arabic',
        'ru' => 'Russian',
        'ja' => 'Japanese',
        'ko' => 'Korean',
        'zh' => 'Chinese',
        'ba' => 'Bashkir',
    );

    $source_language = isset($lang_names[$source_lang]) ? $lang_names[$source_lang] : 'English';
    $target_language = isset($lang_names[$target_lang]) ? $lang_names[$target_lang] : 'Turkish';

    // Get custom instructions
    $custom_instructions = get_option('arcuras_ai_custom_instructions', '');

    // Prepare prompt with custom instructions
    $default_instructions = "- Preserve the poetic and emotional tone
- Keep rhyme and rhythm where possible
- Maintain [Chorus], [Verse], [Bridge] tags exactly as they are
- Keep line breaks exactly as they appear
- Focus on meaning rather than literal word-for-word translation
- Adapt cultural references if needed
- Translate slang and colloquial expressions naturally
- Keep the authentic emotion and rawness of the original";

    $instructions = !empty($custom_instructions) ? $custom_instructions : $default_instructions;

    $prompt = "You are a professional lyrics translator specializing in music translation. Your goal is to capture the emotion, flow, and cultural context of the lyrics, not just literal word-for-word translation.

Translate the following song lyrics from {$source_language} to {$target_language}.

CRITICAL TRANSLATION RULES:
{$instructions}

IMPORTANT REMINDERS:
- Slang should be translated to equivalent slang, not formal language
- Sexual/explicit content should be translated accurately, maintaining the same level of explicitness
- Avoid over-literal translations that sound unnatural
- The translation should sound like a real song in {$target_language}, not a dictionary translation

Song Lyrics:
{$text}

Now provide ONLY the {$target_language} translation (no explanations, no notes):";

    // Call Gemini API
    $api_url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $selected_model . ':generateContent?key=' . $gemini_api_key;

    $system_instruction = 'You are a professional lyrics translator who specializes in capturing emotion, cultural nuances, and maintaining the artistic flow of songs. You translate meaning and feeling, not just words.';
    $full_prompt = $system_instruction . "\n\n" . $prompt;

    $response = wp_remote_post($api_url, array(
        'timeout' => 60,
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array(
            'contents' => array(
                array(
                    'parts' => array(
                        array('text' => $full_prompt)
                    )
                )
            ),
            'generationConfig' => array(
                'temperature' => 0.8,
                'maxOutputTokens' => 3000,
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

    $translated_text = trim($body['candidates'][0]['content']['parts'][0]['text']);
    $model_display = isset($gemini_models[$selected_model]) ? $gemini_models[$selected_model] : $selected_model;

    wp_send_json_success(array(
        'translated_text' => $translated_text,
        'source_lang' => $source_language,
        'target_lang' => $target_language,
        'model' => $selected_model,
        'model_display' => $model_display,
        'provider' => 'Gemini',
    ));
}
add_action('wp_ajax_arcuras_ai_translate', 'arcuras_ai_translate_lyrics');

/**
 * Enqueue AI translation scripts in Gutenberg editor
 */
function arcuras_enqueue_ai_translation_scripts($hook) {
    // Only load in block editor
    if ($hook !== 'post.php' && $hook !== 'post-new.php') {
        return;
    }

    // Get model information
    $selected_model = get_option('arcuras_ai_model', 'gemini-2.0-flash-exp');

    $model_display_names = array(
        'gemini-2.0-flash-exp' => 'Gemini 2.0 Flash',
        'gemini-1.5-pro' => 'Gemini 1.5 Pro',
        'gemini-1.5-flash' => 'Gemini 1.5 Flash',
    );

    // Pass nonce to JavaScript
    wp_localize_script('wp-blocks', 'arcurasAI', array(
        'nonce' => wp_create_nonce('arcuras_ai_translate_nonce'),
        'ajax_url' => admin_url('admin-ajax.php'),
        'has_api_key' => !empty(get_option('arcuras_gemini_api_key')),
        'model' => $selected_model,
        'model_display' => isset($model_display_names[$selected_model]) ? $model_display_names[$selected_model] : $selected_model,
        'provider' => 'Gemini',
    ));
}
add_action('admin_enqueue_scripts', 'arcuras_enqueue_ai_translation_scripts');
