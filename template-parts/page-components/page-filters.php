<?php
/**
 * Reusable Page Filters Component
 *
 * @package Arcuras
 * @since 2.7.0
 *
 * Usage:
 * set_query_var('filter_config', array(
 *     'search' => array(
 *         'enabled' => true,
 *         'name' => 'q',
 *         'value' => $q,
 *         'label' => __('Search albums', 'gufte'),
 *         'placeholder' => __('Type album name…', 'gufte'),
 *         'id' => 'albums-search'
 *     ),
 *     'sort' => array(
 *         'enabled' => true,
 *         'name' => 'sort',
 *         'value' => $sort,
 *         'label' => __('Sort by', 'gufte'),
 *         'id' => 'albums-sort',
 *         'options' => array(
 *             'name_asc' => __('Name (A→Z)', 'gufte'),
 *             'name_desc' => __('Name (Z→A)', 'gufte'),
 *             'date_desc' => __('Newest', 'gufte'),
 *             'date_asc' => __('Oldest', 'gufte'),
 *         )
 *     ),
 *     'per_page' => array(
 *         'enabled' => true,
 *         'name' => 'per_page',
 *         'value' => $per_page,
 *         'label' => __('Per page', 'gufte'),
 *         'id' => 'albums-per-page',
 *         'options' => array(12, 20, 30, 40, 60)
 *     ),
 *     'results_text' => sprintf(_n('%s result', '%s results', $total, 'gufte'), number_format_i18n($total)),
 *     'search_query' => $q, // optional, for "searching for X" text
 *     'action_url' => get_permalink()
 * ));
 * get_template_part('template-parts/page-components/page-filters');
 */

$filter_config = get_query_var('filter_config', array());

if (empty($filter_config)) {
    return;
}

$search_config = isset($filter_config['search']) ? $filter_config['search'] : array('enabled' => false);
$sort_config = isset($filter_config['sort']) ? $filter_config['sort'] : array('enabled' => false);
$per_page_config = isset($filter_config['per_page']) ? $filter_config['per_page'] : array('enabled' => false);
$results_text = isset($filter_config['results_text']) ? $filter_config['results_text'] : '';
$search_query = isset($filter_config['search_query']) ? $filter_config['search_query'] : '';
$action_url = isset($filter_config['action_url']) ? $filter_config['action_url'] : '';
?>

<div class="page-filters sticky top-0 z-10 py-3 bg-gradient-to-b from-white/95 to-white/60 backdrop-blur supports-[backdrop-filter]:backdrop-blur-md border-b border-gray-200">
    <form method="get" action="<?php echo esc_url($action_url); ?>" class="px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">

            <?php if (!empty($search_config['enabled'])) : ?>
                <!-- Search Field -->
                <div class="<?php echo (!empty($sort_config['enabled']) || !empty($per_page_config['enabled'])) ? 'md:col-span-2' : 'md:col-span-4'; ?>">
                    <label for="<?php echo esc_attr($search_config['id']); ?>" class="block text-sm font-medium text-gray-700 mb-1">
                        <?php echo esc_html($search_config['label']); ?>
                    </label>
                    <div class="relative">
                        <input
                            id="<?php echo esc_attr($search_config['id']); ?>"
                            type="search"
                            name="<?php echo esc_attr($search_config['name']); ?>"
                            value="<?php echo esc_attr($search_config['value']); ?>"
                            placeholder="<?php echo esc_attr($search_config['placeholder']); ?>"
                            class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500 pr-10"
                        />
                        <?php gufte_icon('magnify', 'absolute right-3 top-1/2 -translate-y-1/2 text-gray-400'); ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($sort_config['enabled'])) : ?>
                <!-- Sort Field -->
                <div>
                    <label for="<?php echo esc_attr($sort_config['id']); ?>" class="block text-sm font-medium text-gray-700 mb-1">
                        <?php echo esc_html($sort_config['label']); ?>
                    </label>
                    <select id="<?php echo esc_attr($sort_config['id']); ?>" name="<?php echo esc_attr($sort_config['name']); ?>" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                        <?php foreach ($sort_config['options'] as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($sort_config['value'], $value); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <?php if (!empty($per_page_config['enabled'])) : ?>
                <!-- Per Page Field -->
                <div>
                    <label for="<?php echo esc_attr($per_page_config['id']); ?>" class="block text-sm font-medium text-gray-700 mb-1">
                        <?php echo esc_html($per_page_config['label']); ?>
                    </label>
                    <select id="<?php echo esc_attr($per_page_config['id']); ?>" name="<?php echo esc_attr($per_page_config['name']); ?>" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                        <?php foreach ($per_page_config['options'] as $option) : ?>
                            <option value="<?php echo esc_attr($option); ?>" <?php selected($per_page_config['value'], $option); ?>>
                                <?php echo esc_html($option); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <!-- Results Info & Submit -->
            <div class="md:col-span-4 flex items-center justify-between gap-3 flex-wrap">
                <?php if ($results_text) : ?>
                    <p class="text-sm text-gray-600" aria-live="polite">
                        <?php echo esc_html($results_text); ?>
                        <?php if ($search_query) : ?>
                            <?php echo ' — '; ?>
                            <?php printf(esc_html__('for "%s"', 'gufte'), esc_html($search_query)); ?>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>

                <div class="flex items-center gap-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                        <?php gufte_icon('filter', 'mr-2 w-4 h-4'); ?>
                        <?php esc_html_e('Apply Filters', 'gufte'); ?>
                    </button>

                    <?php if ($search_query || (isset($sort_config['value']) && $sort_config['value']) || (isset($per_page_config['value']) && $per_page_config['value'])) : ?>
                        <a href="<?php echo esc_url($action_url); ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                            <?php gufte_icon('close', 'mr-2 w-4 h-4'); ?>
                            <?php esc_html_e('Clear', 'gufte'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </form>
</div>
