<?php
/**
 * Reusable Pagination Component
 *
 * @package Arcuras
 * @since 2.7.0
 *
 * Usage:
 * set_query_var('pagination_config', array(
 *     'total_pages' => $total_pages,           // Required: Total number of pages
 *     'current_page' => $current_page,         // Required: Current page number
 *     'base_url' => get_permalink(),           // Required: Base URL for pagination
 *     'format' => '?paged=%#%',                // Optional: URL format (default: ?paged=%#%)
 *     'preserve_params' => array(              // Optional: URL parameters to preserve
 *         'q' => $q,
 *         'sort' => $sort,
 *         'per_page' => $per_page,
 *         'letter' => $letter
 *     ),
 *     'mid_size' => 2,                         // Optional: How many page numbers to show on each side (default: 2)
 *     'end_size' => 1,                         // Optional: How many page numbers at the beginning and end (default: 1)
 *     'prev_text' => __('Previous', 'gufte'),  // Optional: Previous button text
 *     'next_text' => __('Next', 'gufte'),      // Optional: Next button text
 *     'show_all' => false,                     // Optional: Show all pages (default: false)
 * ));
 * get_template_part('template-parts/page-components/page-pagination');
 */

$pagination_config = get_query_var('pagination_config', array());

if (empty($pagination_config)) {
    return;
}

// Required values
$total_pages = isset($pagination_config['total_pages']) ? intval($pagination_config['total_pages']) : 0;
$current_page = isset($pagination_config['current_page']) ? intval($pagination_config['current_page']) : 1;
$base_url = isset($pagination_config['base_url']) ? $pagination_config['base_url'] : '';

// Don't show pagination if only 1 page or no pages
if ($total_pages <= 1 || empty($base_url)) {
    return;
}

// Optional values with defaults
$format = isset($pagination_config['format']) ? $pagination_config['format'] : '?paged=%#%';
$preserve_params = isset($pagination_config['preserve_params']) ? $pagination_config['preserve_params'] : array();
$mid_size = isset($pagination_config['mid_size']) ? intval($pagination_config['mid_size']) : 2;
$end_size = isset($pagination_config['end_size']) ? intval($pagination_config['end_size']) : 1;
$prev_text = isset($pagination_config['prev_text']) ? $pagination_config['prev_text'] : __('Previous', 'gufte');
$next_text = isset($pagination_config['next_text']) ? $pagination_config['next_text'] : __('Next', 'gufte');
$show_all = isset($pagination_config['show_all']) ? (bool)$pagination_config['show_all'] : false;

// Build preserved parameters array
$add_args = array();
foreach ($preserve_params as $key => $value) {
    if ($value !== '' && $value !== null && $value !== false) {
        $add_args[$key] = $value;
    }
}

// Prepare base URL
$big = 999999999;
$base = str_replace($big, '%#%', esc_url(add_query_arg(array_merge($add_args, array('paged' => $big)), $base_url)));

// Generate pagination links using paginate_links
$pagination_args = array(
    'base' => $base,
    'format' => $format,
    'total' => $total_pages,
    'current' => $current_page,
    'mid_size' => $mid_size,
    'end_size' => $end_size,
    'prev_text' => $prev_text,
    'next_text' => $next_text,
    'type' => 'array',
    'add_args' => false, // Already added to base
    'show_all' => $show_all,
);

// Add icons to prev/next text
ob_start();
gufte_icon('chevron-left', 'w-4 h-4');
$prev_icon = ob_get_clean();

ob_start();
gufte_icon('chevron-right', 'w-4 h-4');
$next_icon = ob_get_clean();

$pagination_args['prev_text'] = '<span class="inline-flex items-center gap-1">' . $prev_icon . '<span>' . $prev_text . '</span></span>';
$pagination_args['next_text'] = '<span class="inline-flex items-center gap-1"><span>' . $next_text . '</span>' . $next_icon . '</span>';

$links = paginate_links($pagination_args);

if (empty($links)) {
    return;
}
?>

<nav class="page-pagination px-4 sm:px-6 lg:px-8 py-6" aria-label="<?php esc_attr_e('Pagination', 'gufte'); ?>">
    <ul class="flex flex-wrap justify-center items-center gap-2">
        <?php foreach ($links as $link) : ?>
            <li>
                <?php
                // Style the pagination links
                if (strpos($link, 'current') !== false) {
                    // Current page - highlight
                    $link = str_replace(
                        '<span',
                        '<span class="inline-flex items-center justify-center min-w-[2.5rem] h-10 px-3 rounded-lg bg-primary-600 text-white font-semibold shadow-md border border-primary-600"',
                        $link
                    );
                } elseif (strpos($link, 'dots') !== false) {
                    // Dots - no hover effect
                    $link = str_replace(
                        '<span',
                        '<span class="inline-flex items-center justify-center min-w-[2.5rem] h-10 px-3 text-gray-400"',
                        $link
                    );
                } else {
                    // Regular page link
                    $link = str_replace(
                        '<a',
                        '<a class="inline-flex items-center justify-center min-w-[2.5rem] h-10 px-3 rounded-lg bg-white text-gray-700 font-medium border border-gray-300 hover:bg-gray-50 hover:border-gray-400 hover:text-primary-600 transition-all duration-200"',
                        $link
                    );
                }
                echo $link;
                ?>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
