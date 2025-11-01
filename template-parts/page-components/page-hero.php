<?php
/**
 * Reusable Page Hero Component
 *
 * @package Arcuras
 * @since 2.7.0
 *
 * Usage:
 * set_query_var('hero_title', 'Page Title');
 * set_query_var('hero_icon', 'album'); // optional
 * set_query_var('hero_description', 'Page description'); // optional
 * set_query_var('hero_meta', array('key' => 'value')); // optional
 * get_template_part('template-parts/page-hero');
 */

// Get variables
$hero_title = get_query_var('hero_title', '');
$hero_icon = get_query_var('hero_icon', '');
$hero_description = get_query_var('hero_description', '');
$hero_meta = get_query_var('hero_meta', array());

if (empty($hero_title)) {
    return;
}
?>

<header class="page-hero bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
    <div class="px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
        <div class="flex flex-col gap-4">
            <!-- Title with Icon -->
            <div class="flex items-center gap-3">
                <?php if ($hero_icon) : ?>
                    <?php gufte_icon($hero_icon, 'text-3xl text-primary-600 flex-shrink-0'); ?>
                <?php endif; ?>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-gray-900 leading-tight">
                    <?php echo wp_kses_post($hero_title); ?>
                </h1>
            </div>

            <!-- Description -->
            <?php if ($hero_description) : ?>
                <div class="text-sm leading-relaxed text-gray-600 max-w-2xl">
                    <?php echo wp_kses_post($hero_description); ?>
                </div>
            <?php endif; ?>

            <!-- Meta Information -->
            <?php if (!empty($hero_meta)) : ?>
                <div class="flex flex-wrap gap-4 text-sm">
                    <?php foreach ($hero_meta as $label => $value) : ?>
                        <?php if ($value) : ?>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-500"><?php echo esc_html($label); ?>:</span>
                                <span class="font-semibold text-gray-900"><?php echo wp_kses_post($value); ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>
