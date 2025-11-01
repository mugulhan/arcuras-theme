<?php
/**
 * Section Header Component
 * Reusable section header with consistent styling
 *
 * @package Arcuras
 *
 * Variables:
 * @var string $title - Section title (required)
 * @var string $icon - Icon name from gufte_icon() (optional)
 * @var string $link_url - URL for "View All" link (optional)
 * @var string $link_text - Text for link button (optional, default: 'View All')
 * @var string $classes - Additional CSS classes (optional)
 */

$title = get_query_var('section_title', '');
$icon = get_query_var('section_icon', '');
$link_url = get_query_var('section_link_url', '');
$link_text = get_query_var('section_link_text', __('View All', 'gufte'));
$classes = get_query_var('section_header_classes', '');

if (empty($title)) {
    return;
}
?>

<div class="section-header flex justify-between items-center mb-4 md:mb-6 pr-2 md:pr-4 <?php echo esc_attr($classes); ?>">
    <h2 class="text-lg md:text-4xl font-bold text-gray-900 flex items-center">
        <?php if ($icon) : ?>
            <?php gufte_icon($icon, 'mr-2 md:mr-3 text-primary-600 w-5 h-5 md:w-9 md:h-9'); ?>
        <?php endif; ?>
        <span><?php echo esc_html($title); ?></span>
    </h2>

    <?php if ($link_url) : ?>
        <a href="<?php echo esc_url($link_url); ?>"
           class="text-primary-600 hover:text-primary-700 flex items-center transition-all duration-300 bg-gradient-to-r from-primary-50 to-blue-50 hover:from-primary-100 hover:to-blue-100 px-3 py-2 md:px-4 md:py-2.5 rounded-xl border border-primary-200/50 hover:border-primary-300 text-xs md:text-sm whitespace-nowrap hover:shadow-lg hover:-translate-y-0.5 font-medium">
            <?php echo esc_html($link_text); ?>
            <?php gufte_icon('arrow-right', 'ml-1.5 w-3 h-3 md:w-4 md:h-4'); ?>
        </a>
    <?php endif; ?>
</div>
