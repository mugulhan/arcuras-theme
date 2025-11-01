<?php
/**
 * Template Name: Contribute Translation
 *
 * Allows users to contribute translations for songs.
 *
 * @package Gufte
 */

// Redirect non-logged-in users
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(home_url('/contribute-translation/')));
    exit;
}

// Get parameters
$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
$target_language = isset($_GET['target_language']) ? sanitize_text_field($_GET['target_language']) : '';

// If no parameters, show info page instead of redirecting
$show_info_page = false;
if (!$post_id || !get_post($post_id)) {
    $show_info_page = true;
}

// Get post data
$song_post = get_post($post_id);
$song_title = get_the_title($post_id);
$singer_name = function_exists('gufte_get_singer_name') ? gufte_get_singer_name($post_id) : '';

// Get original lyrics
$raw_content = get_post_field('post_content', $post_id);
$lyrics_languages = function_exists('gufte_get_lyrics_languages')
    ? gufte_get_lyrics_languages($raw_content)
    : array('original' => 'English', 'translations' => array());

// Parse original lyrics (extract from table)
$original_lyrics_array = gufte_extract_original_lyrics($raw_content);

// Check if user can translate to this language
$current_user_id = get_current_user_id();
$user_known_languages = get_user_meta($current_user_id, 'user_known_languages', true);

if (empty($target_language) || !in_array($target_language, (array)$user_known_languages)) {
    wp_redirect(get_permalink($post_id));
    exit;
}

// Language names
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

// Handle form submission
$submission_message = '';
$submission_error = '';

if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['submit_translation'])) {
    // Verify nonce
    if (!isset($_POST['translation_nonce']) || !wp_verify_nonce($_POST['translation_nonce'], 'submit_translation_' . $post_id)) {
        $submission_error = __('Security check failed. Please try again.', 'gufte');
    } else {
        // Get translation lines
        $translation_lines = isset($_POST['translation_lines']) && is_array($_POST['translation_lines'])
            ? array_map('sanitize_textarea_field', $_POST['translation_lines'])
            : array();

        if (empty($translation_lines) || count(array_filter($translation_lines)) === 0) {
            $submission_error = __('Please provide at least one translation line.', 'gufte');
        } else {
            // Create submission post
            $submission_data = array(
                'post_title' => sprintf(__('Translation: %s to %s by %s', 'gufte'),
                    $song_title,
                    $target_language_display,
                    wp_get_current_user()->display_name
                ),
                'post_type' => 'translation_submission',
                'post_status' => 'pending',
                'post_author' => $current_user_id,
            );

            $submission_id = wp_insert_post($submission_data);

            if ($submission_id && !is_wp_error($submission_id)) {
                // Save metadata
                update_post_meta($submission_id, '_original_post_id', $post_id);
                update_post_meta($submission_id, '_target_language', $target_language);
                update_post_meta($submission_id, '_translation_lines', $translation_lines);
                update_post_meta($submission_id, '_original_lines', $original_lyrics_array);
                update_post_meta($submission_id, '_submission_date', current_time('mysql'));

                // Redirect to success page
                wp_redirect(add_query_arg(array(
                    'submission' => 'success',
                    'submission_id' => $submission_id
                ), get_permalink()));
                exit;
            } else {
                $submission_error = __('Failed to submit translation. Please try again.', 'gufte');
            }
        }
    }
}

// Check if submission was successful
if (isset($_GET['submission']) && $_GET['submission'] === 'success') {
    $submission_message = __('Translation submitted successfully! Our team will review it soon.', 'gufte');
}

get_header();
?>

<div class="site-content-wrapper flex flex-col md:flex-row">
    <?php get_template_part('template-parts/arcuras-sidebar'); ?>

    <main id="primary" class="site-main flex-1 pt-6 pb-12 px-4 sm:px-6 lg:px-8 overflow-x-hidden">

        <?php if ($show_info_page) : ?>
        <!-- Info Page: No Translation Selected -->
        <header class="page-header mb-8 bg-white rounded-lg shadow-md p-6 border border-gray-200">
            <h1 class="text-3xl font-bold text-gray-800 mb-2 flex items-center">
                <?php gufte_icon("translate", "mr-3 text-primary-600 w-8 h-8"); ?>
                <?php esc_html_e('Contribute Translations', 'gufte'); ?>
            </h1>
            <p class="text-gray-600">
                <?php esc_html_e('Help us translate songs into different languages!', 'gufte'); ?>
            </p>
        </header>

        <div class="bg-white rounded-lg shadow-md p-8 border border-gray-200">
            <div class="max-w-3xl mx-auto">
                <div class="text-center mb-8">
                    <div class="w-24 h-24 mx-auto mb-4 bg-primary-100 rounded-full flex items-center justify-center">
                        <?php gufte_icon("google-translate", "text-primary-600 w-12 h-12"); ?>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">
                        <?php esc_html_e('How to Contribute Translations', 'gufte'); ?>
                    </h2>
                    <p class="text-gray-600">
                        <?php esc_html_e('Follow these simple steps to start contributing:', 'gufte'); ?>
                    </p>
                </div>

                <div class="space-y-6">
                    <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg">
                        <div class="flex-shrink-0 w-10 h-10 bg-primary-600 text-white rounded-full flex items-center justify-center font-bold">1</div>
                        <div>
                            <h3 class="font-semibold text-gray-800 mb-1"><?php esc_html_e('Set Your Languages', 'gufte'); ?></h3>
                            <p class="text-sm text-gray-600 mb-2"><?php esc_html_e('Go to your profile and select the languages you can translate.', 'gufte'); ?></p>
                            <a href="<?php echo esc_url(home_url('/my-profile/')); ?>" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                                <?php esc_html_e('Go to Profile →', 'gufte'); ?>
                            </a>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg">
                        <div class="flex-shrink-0 w-10 h-10 bg-primary-600 text-white rounded-full flex items-center justify-center font-bold">2</div>
                        <div>
                            <h3 class="font-semibold text-gray-800 mb-1"><?php esc_html_e('Find a Song', 'gufte'); ?></h3>
                            <p class="text-sm text-gray-600"><?php esc_html_e('Browse songs and look for "Help Translate This Song" button on songs missing your language.', 'gufte'); ?></p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg">
                        <div class="flex-shrink-0 w-10 h-10 bg-primary-600 text-white rounded-full flex items-center justify-center font-bold">3</div>
                        <div>
                            <h3 class="font-semibold text-gray-800 mb-1"><?php esc_html_e('Translate', 'gufte'); ?></h3>
                            <p class="text-sm text-gray-600"><?php esc_html_e('Click the translate button for your language and submit your translation line by line.', 'gufte'); ?></p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg">
                        <div class="flex-shrink-0 w-10 h-10 bg-primary-600 text-white rounded-full flex items-center justify-center font-bold">4</div>
                        <div>
                            <h3 class="font-semibold text-gray-800 mb-1"><?php esc_html_e('Review & Publish', 'gufte'); ?></h3>
                            <p class="text-sm text-gray-600"><?php esc_html_e('Our team will review your translation and publish it. You\'ll get credit for your contribution!', 'gufte'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 text-center">
                    <a href="<?php echo esc_url(home_url()); ?>" class="inline-flex items-center px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition-all duration-300">
                        <?php gufte_icon("music", "mr-2 w-5 h-5"); ?>
                        <?php esc_html_e('Browse Songs', 'gufte'); ?>
                    </a>
                </div>
            </div>
        </div>

        <?php else : ?>

        <!-- Translation Form Page -->
        <header class="page-header mb-8 bg-white rounded-lg shadow-md p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-800 mb-2 flex items-center">
                        <?php gufte_icon("translate", "mr-3 text-primary-600 w-8 h-8"); ?>
                        <?php esc_html_e('Contribute Translation', 'gufte'); ?>
                    </h1>
                    <p class="text-gray-600 mb-2">
                        <?php echo esc_html(sprintf(__('Translating "%s" to %s', 'gufte'), $song_title, $target_language_display)); ?>
                    </p>
                    <?php if ($singer_name) : ?>
                    <p class="text-sm text-gray-500">
                        <?php echo esc_html(sprintf(__('by %s', 'gufte'), $singer_name)); ?>
                    </p>
                    <?php endif; ?>
                </div>
                <a href="<?php echo esc_url(get_permalink($post_id)); ?>"
                   class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                    <?php gufte_icon("arrow-left", "mr-2 w-5 h-5"); ?>
                    <?php esc_html_e('Back to Song', 'gufte'); ?>
                </a>
            </div>
        </header>

        <!-- Messages -->
        <?php if (!empty($submission_message)) : ?>
        <div class="success-message mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center">
            <?php gufte_icon("check-circle", "mr-3 text-green-600 w-6 h-6"); ?>
            <div>
                <p class="font-medium"><?php echo esc_html($submission_message); ?></p>
                <p class="text-sm mt-1"><?php esc_html_e('You can close this page or translate another song.', 'gufte'); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($submission_error)) : ?>
        <div class="error-message mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg flex items-center">
            <?php gufte_icon("alert-circle", "mr-3 text-red-600 w-6 h-6"); ?>
            <?php echo esc_html($submission_error); ?>
        </div>
        <?php endif; ?>

        <!-- Instructions -->
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <h3 class="text-lg font-semibold text-blue-900 mb-2 flex items-center">
                <?php gufte_icon("information", "mr-2 text-blue-600 w-5 h-5"); ?>
                <?php esc_html_e('How to Contribute', 'gufte'); ?>
            </h3>
            <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                <li><?php esc_html_e('Translate each line of the original lyrics to the target language.', 'gufte'); ?></li>
                <li><?php esc_html_e('Try to keep the meaning and emotional tone of the original.', 'gufte'); ?></li>
                <li><?php esc_html_e('Your translation will be reviewed by our team before being published.', 'gufte'); ?></li>
                <li><?php esc_html_e('You can leave a line empty if you\'re unsure about the translation.', 'gufte'); ?></li>
            </ul>
        </div>

        <!-- Translation Form -->
        <form method="post" id="translation-form" class="bg-white rounded-lg shadow-md border border-gray-200">
            <?php wp_nonce_field('submit_translation_' . $post_id, 'translation_nonce'); ?>
            <input type="hidden" name="submit_translation" value="1">

            <div class="p-6">
                <!-- Header -->
                <div class="mb-6 pb-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center">
                                <?php gufte_icon("music-note", "mr-2 text-gray-600 w-6 h-6"); ?>
                                <span class="text-sm font-medium text-gray-700">
                                    <?php esc_html_e('Original', 'gufte'); ?>:
                                    <span class="text-gray-900"><?php echo esc_html($lyrics_languages['original']); ?></span>
                                </span>
                            </div>
                            <div class="w-px h-6 bg-gray-300"></div>
                            <div class="flex items-center">
                                <?php gufte_icon("translate", "mr-2 text-primary-600 w-6 h-6"); ?>
                                <span class="text-sm font-medium text-gray-700">
                                    <?php esc_html_e('Translation', 'gufte'); ?>:
                                    <span class="text-primary-700"><?php echo esc_html($target_language_display); ?></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Line by line translation -->
                <div class="space-y-6">
                    <?php
                    if (!empty($original_lyrics_array)) :
                        foreach ($original_lyrics_array as $index => $line) :
                            $line_number = $index + 1;
                    ?>
                    <div class="translation-pair border border-gray-200 rounded-lg p-4 hover:border-primary-300 transition-colors">
                        <!-- Line number -->
                        <div class="flex items-center mb-3">
                            <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 text-gray-700 text-sm font-bold rounded-full mr-3">
                                <?php echo $line_number; ?>
                            </span>
                            <div class="flex-1 h-px bg-gray-200"></div>
                        </div>

                        <!-- Original line -->
                        <div class="mb-3">
                            <label class="block text-xs font-medium text-gray-500 mb-1 uppercase tracking-wide">
                                <?php esc_html_e('Original', 'gufte'); ?>
                            </label>
                            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <p class="text-gray-800 leading-relaxed"><?php echo esc_html($line); ?></p>
                            </div>
                        </div>

                        <!-- Translation input -->
                        <div>
                            <label class="block text-xs font-medium text-primary-600 mb-1 uppercase tracking-wide">
                                <?php esc_html_e('Your Translation', 'gufte'); ?>
                            </label>
                            <textarea
                                name="translation_lines[<?php echo $index; ?>]"
                                rows="2"
                                placeholder="<?php esc_attr_e('Enter your translation here...', 'gufte'); ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-300 resize-vertical"
                            ><?php echo isset($_POST['translation_lines'][$index]) ? esc_textarea($_POST['translation_lines'][$index]) : ''; ?></textarea>
                        </div>
                    </div>
                    <?php
                        endforeach;
                    else :
                    ?>
                    <div class="text-center py-8">
                        <p class="text-gray-500 italic"><?php esc_html_e('No lyrics found.', 'gufte'); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Submit Section -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg flex items-center justify-between">
                <p class="text-sm text-gray-600">
                    <?php esc_html_e('By submitting, you agree that your translation may be published after review.', 'gufte'); ?>
                </p>
                <button type="submit"
                        class="inline-flex items-center px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition-all duration-300 hover:shadow-lg">
                    <?php gufte_icon("check", "mr-2 w-5 h-5"); ?>
                    <?php esc_html_e('Submit Translation', 'gufte'); ?>
                </button>
            </div>
        </form>

        <?php endif; ?>

    </main>
</div>

<?php
get_footer();

/**
 * Extract original lyrics from post content (removes table structure)
 */
function gufte_extract_original_lyrics($content) {
    $lyrics_lines = array();

    // Try to extract from table structure if exists
    if (preg_match('/<table/i', $content)) {
        // Extract all rows from tbody
        preg_match('/<tbody[^>]*>(.*?)<\/tbody>/is', $content, $tbody_match);

        if (!empty($tbody_match[1])) {
            $tbody = $tbody_match[1];

            // Extract all rows
            preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $tbody, $row_matches);

            if (!empty($row_matches[1])) {
                foreach ($row_matches[1] as $row) {
                    // Extract all cells in this row
                    preg_match_all('/<td[^>]*>(.*?)<\/td>/is', $row, $cell_matches);

                    if (!empty($cell_matches[1])) {
                        // First cell is original lyrics
                        $first_cell = $cell_matches[1][0];
                        $line = strip_tags($first_cell);
                        $line = html_entity_decode($line, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        $line = trim($line);

                        // Skip empty lines and header-like content
                        if (!empty($line) &&
                            stripos($line, 'original') === false &&
                            stripos($line, 'translation') === false &&
                            stripos($line, 'çeviri') === false &&
                            strlen($line) > 1) {
                            $lyrics_lines[] = $line;
                        }
                    }
                }

                if (!empty($lyrics_lines)) {
                    return array_values($lyrics_lines);
                }
            }
        }
    }

    // Fallback: Try to get clean text without table
    // Remove table completely and split by double line breaks
    $clean_content = preg_replace('/<table[^>]*>.*?<\/table>/is', '', $content);
    $clean_content = strip_tags($clean_content);
    $clean_content = html_entity_decode($clean_content, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    // Split by double newlines (verse separators) or single newlines
    $lines = preg_split('/\n\n+|\r\n\r\n+/', $clean_content);

    if (empty($lines) || count($lines) === 1) {
        // Try single newline split
        $lines = preg_split('/\r\n|\r|\n/', $clean_content);
    }

    $lines = array_map('trim', $lines);
    $lines = array_filter($lines, function($line) {
        return !empty($line) && strlen($line) > 1;
    });

    return array_values($lines);
}
