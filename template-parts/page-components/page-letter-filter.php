<?php
/**
 * Reusable A-Z Letter Filter Component
 *
 * @package Arcuras
 * @since 2.7.0
 *
 * Usage:
 * set_query_var('letter_filter_config', array(
 *     'current_letter' => $letter,          // Currently selected letter
 *     'base_url' => get_permalink(),        // Base URL for filter links
 *     'preserve_params' => array(           // URL parameters to preserve
 *         'q' => $q,
 *         'sort' => $sort,
 *         'per_page' => $per_page
 *     ),
 *     'letters' => array('A', 'B', 'C'...), // Optional: custom letter array
 *     'show_all_button' => true,            // Optional: show "All" button (default: true)
 *     'all_button_text' => __('All', 'gufte'), // Optional: custom "All" button text
 * ));
 * get_template_part('template-parts/page-components/page-letter-filter');
 */

$letter_config = get_query_var('letter_filter_config', array());

if (empty($letter_config)) {
    return;
}

// Config değerleri
$current_letter = isset($letter_config['current_letter']) ? $letter_config['current_letter'] : '';
$base_url = isset($letter_config['base_url']) ? $letter_config['base_url'] : '';
$preserve_params = isset($letter_config['preserve_params']) ? $letter_config['preserve_params'] : array();
$show_all_button = isset($letter_config['show_all_button']) ? $letter_config['show_all_button'] : true;
$all_button_text = isset($letter_config['all_button_text']) ? $letter_config['all_button_text'] : __('All', 'gufte');

// Varsayılan harf dizisi (A-Z + Türkçe karakterler + #)
$default_letters = array_merge(range('A', 'Z'), array('Ç', 'Ğ', 'İ', 'Ö', 'Ş', 'Ü', '#'));
$letters = isset($letter_config['letters']) ? $letter_config['letters'] : $default_letters;

// Base args - korunacak parametreleri hazırla
$base_args = array();
foreach ($preserve_params as $key => $value) {
    if ($value !== '' && $value !== null) {
        $base_args[$key] = $value;
    }
}
?>

<div class="page-letter-filter px-4 sm:px-6 lg:px-8 py-4 bg-white border-b border-gray-200">
    <div class="flex flex-wrap items-center gap-2">
        <?php if ($show_all_button) : ?>
            <?php
            // "All" butonu için URL
            $all_url = add_query_arg(array_merge($base_args, array('letter' => false, 'paged' => false)), $base_url);
            $is_all_active = ($current_letter === '');
            ?>
            <a href="<?php echo esc_url($all_url); ?>"
               class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium border transition-all duration-200 <?php echo $is_all_active ? 'bg-primary-600 text-white border-primary-600 shadow-md' : 'bg-white text-gray-800 border-gray-300 hover:bg-gray-50 hover:border-gray-400'; ?>"
               <?php echo $is_all_active ? 'aria-current="page"' : ''; ?>>
                <?php echo esc_html($all_button_text); ?>
            </a>
        <?php endif; ?>

        <?php foreach ($letters as $letter) :
            $is_active = ($current_letter === $letter);
            $letter_url = add_query_arg(array_merge($base_args, array('letter' => rawurlencode($letter), 'paged' => false)), $base_url);
            ?>
            <a href="<?php echo esc_url($letter_url); ?>"
               class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-sm font-semibold border transition-all duration-200 <?php echo $is_active ? 'bg-primary-600 text-white border-primary-600 shadow-md scale-110' : 'bg-white text-gray-800 border-gray-300 hover:bg-gray-50 hover:border-gray-400 hover:scale-105'; ?>"
               <?php echo $is_active ? 'aria-current="page"' : ''; ?>
               title="<?php echo esc_attr(sprintf(__('Filter by letter: %s', 'gufte'), $letter)); ?>">
                <?php echo esc_html($letter); ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>
