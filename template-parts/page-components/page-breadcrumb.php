<?php
/**
 * Reusable Breadcrumb Navigation Component
 *
 * @package Arcuras
 * @since 2.7.0
 *
 * Usage:
 * set_query_var('breadcrumb_items', array(
 *     array('label' => 'Home', 'url' => home_url('/')),
 *     array('label' => 'Albums', 'url' => home_url('/albums/')),
 *     array('label' => 'Current Page') // no URL = current page
 * ));
 * get_template_part('template-parts/page-breadcrumb');
 */

// Get breadcrumb items
$breadcrumb_items = get_query_var('breadcrumb_items', array());

if (empty($breadcrumb_items)) {
    return;
}
?>

<nav class="page-breadcrumb bg-gray-50 border-b border-gray-200" aria-label="Breadcrumb">
    <div class="px-4 sm:px-6 lg:px-8 py-3">
        <ol class="flex items-center space-x-2 text-sm">
            <?php foreach ($breadcrumb_items as $index => $item) : ?>
                <?php if ($index > 0) : ?>
                    <li class="text-gray-400 mx-2">/</li>
                <?php endif; ?>

                <li class="flex items-center">
                    <?php if (isset($item['url'])) : ?>
                        <a href="<?php echo esc_url($item['url']); ?>" class="text-gray-500 hover:text-primary-600 transition-colors flex items-center">
                            <?php if ($index === 0) : ?>
                                <?php gufte_icon('home', 'w-4 h-4'); ?>
                            <?php else : ?>
                                <?php echo esc_html($item['label']); ?>
                            <?php endif; ?>
                        </a>
                    <?php else : ?>
                        <span class="text-gray-900 font-medium">
                            <?php echo esc_html($item['label']); ?>
                        </span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</nav>
