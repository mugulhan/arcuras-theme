<?php
/**
 * Template part for displaying lyrics card
 *
 * @package Gufte
 * @since 1.9.5
 *
 * Available variables:
 * @var int    $post_id          Post ID
 * @var string $card_type        Card type: 'hero' (default), 'compact', 'grid'
 * @var bool   $show_singer      Show singer info (default: true)
 * @var bool   $show_languages   Show language badges (default: true)
 * @var bool   $show_cta         Show "View Lyrics" button (default: true)
 */

// Set defaults
$post_id = isset($post_id) ? $post_id : get_the_ID();
$card_type = isset($card_type) ? $card_type : 'hero';
$show_singer = isset($show_singer) ? $show_singer : true;
$show_languages = isset($show_languages) ? $show_languages : true;
$show_cta = isset($show_cta) ? $show_cta : true;

// Determine loading strategy - eager for first 4 items, lazy for rest
global $wp_query;
$current_index = $wp_query->current_post;
$loading_attr = ($current_index < 4) ? 'eager' : 'lazy';

// Get post data
// Create a simple SVG placeholder if no featured image
$default_placeholder = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="400" height="400"%3E%3Crect width="400" height="400" fill="%23667eea"/%3E%3Cpath d="M200 120 L200 200 M160 160 L240 160" stroke="%23fff" stroke-width="8" stroke-linecap="round"/%3E%3Ccircle cx="200" cy="240" r="60" fill="none" stroke="%23fff" stroke-width="8"/%3E%3C/svg%3E';

$post_thumb = has_post_thumbnail($post_id)
    ? get_the_post_thumbnail_url($post_id, 'large')
    : $default_placeholder;

$singers = get_the_terms($post_id, 'singer');
$singer = !empty($singers) && !is_wp_error($singers) ? reset($singers) : null;

$singer_image = '';
if ($singer && function_exists('get_term_meta')) {
    $singer_image_id = get_term_meta($singer->term_id, 'singer_image_id', true);
    if ($singer_image_id) {
        $singer_image = wp_get_attachment_image($singer_image_id, 'thumbnail', false, array('class' => 'w-full h-full object-cover'));
    }
}

// Get language data
$post_obj = get_post($post_id);
$raw_content = $post_obj->post_content;
$lyrics_languages = array('original' => '', 'translations' => array());

if (function_exists('gufte_get_lyrics_languages')) {
    $lyrics_languages = gufte_get_lyrics_languages($raw_content);
}

$translation_count = !empty($lyrics_languages['translations']) ? count($lyrics_languages['translations']) : 0;

// Count lines in lyrics from Gutenberg blocks
$line_count = 0;
if (!empty($raw_content)) {
    // Check if content has Gutenberg blocks
    if (has_blocks($raw_content)) {
        $blocks = parse_blocks($raw_content);

        foreach ($blocks as $block) {
            // Look for lyrics-translations block
            if ($block['blockName'] === 'arcuras/lyrics-translations' && !empty($block['attrs']['languages'])) {
                foreach ($block['attrs']['languages'] as $lang) {
                    if (isset($lang['isOriginal']) && $lang['isOriginal'] && !empty($lang['lyrics'])) {
                        // Count non-empty lines in original lyrics
                        $lines = array_filter(explode("\n", $lang['lyrics']), function($line) {
                            return trim($line) !== '';
                        });
                        $line_count = count($lines);
                        break 2; // Exit both loops
                    }
                }
            }
        }
    } else {
        // Fallback for classic editor content
        $clean_content = strip_tags(strip_shortcodes($raw_content));
        $lines = array_filter(explode("\n", $clean_content), function($line) {
            return trim($line) !== '';
        });
        $line_count = count($lines);
    }
}

// Card classes based on type - optimized for scroll
$card_classes = array(
    'hero' => 'hero-card bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 group',
    'compact' => 'compact-card group bg-white rounded-lg shadow-md overflow-hidden border border-gray-200',
    'grid' => 'grid-card group bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100'
);

$current_card_class = isset($card_classes[$card_type]) ? $card_classes[$card_type] : $card_classes['hero'];
?>

<div class="<?php echo esc_attr($current_card_class); ?>" style="display: flex; flex-direction: column; height: 100%;">
    <!-- Card Image -->
    <div class="card-image" style="position: relative; width: 100%; padding-bottom: 100%; height: 0; background-color: #f3f4f6; overflow: hidden; flex-shrink: 0;">
        <a href="<?php echo esc_url(get_permalink($post_id)); ?>" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: block;">
            <img src="<?php echo esc_url($post_thumb); ?>"
                 alt="<?php echo esc_attr(get_the_title($post_id)); ?>"
                 class="w-full h-full object-cover object-center"
                 style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;"
                 width="400"
                 height="400"
                 loading="<?php echo esc_attr($loading_attr); ?>"
                 <?php if ($loading_attr === 'eager') : ?>fetchpriority="high"<?php endif; ?>
                 decoding="async">

            <?php if ($translation_count > 0) : ?>
            <div class="absolute top-2 right-2 bg-primary-500/90 backdrop-blur-sm text-white text-xs font-bold px-1.5 py-0.5 rounded-full flex items-center" style="z-index: 2;">
                <?php gufte_icon("translate", "mr-0.5 text-xs w-3 h-3"); ?>
                <?php echo esc_html($translation_count); ?>
            </div>
            <?php endif; ?>
        </a>
    </div>

    <!-- Card Content -->
    <div class="card-content p-3" style="display: flex; flex-direction: column; overflow: hidden; flex-shrink: 0; height: 155px !important; box-sizing: border-box !important;">
        <!-- Title -->
        <h3 class="text-sm md:text-base font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-primary-600 transition-colors">
            <a href="<?php echo esc_url(get_permalink($post_id)); ?>">
                <?php echo esc_html(get_the_title($post_id)); ?>
            </a>
        </h3>

        <!-- Singer badge -->
        <?php if ($show_singer && $singer) : ?>
        <div class="flex items-center mb-2" style="display: flex; align-items: center; overflow: hidden; min-width: 0;">
            <?php if (!empty($singer_image)) : ?>
                <div class="w-5 h-5 rounded-full overflow-hidden mr-1.5 flex-shrink-0">
                    <?php echo $singer_image; ?>
                </div>
            <?php else : ?>
                <?php gufte_icon("microphone", "text-gray-500 mr-1.5 w-4 h-4 flex-shrink-0"); ?>
            <?php endif; ?>
            <span style="flex: 1 1 0%; min-width: 0; overflow: hidden;">
                <a href="<?php echo esc_url(get_term_link($singer)); ?>" class="text-xs text-gray-600 hover:text-primary-600 transition" style="display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?php echo esc_html($singer->name); ?></a>
            </span>
        </div>
        <?php endif; ?>

        <!-- Language badges -->
        <?php if ($show_languages && (!empty($lyrics_languages['original']) || $translation_count > 0)) : ?>
        <div class="lyrics-languages flex items-center gap-1 mb-2 overflow-hidden" style="max-height: 22px;">
            <?php if (!empty($lyrics_languages['original'])) : ?>
            <span class="inline-flex items-center text-[10px] font-medium text-gray-700 bg-gray-100 px-2 py-0.5 rounded-full flex-shrink-0" style="white-space: nowrap;">
                <?php gufte_icon("file-document", "mr-1 w-3 h-3"); ?>
                <?php echo esc_html($lyrics_languages['original']); ?>
            </span>
            <?php endif; ?>

            <?php if ($translation_count > 0) : ?>
            <span class="inline-flex items-center text-[10px] font-medium text-primary-700 bg-primary-50 px-2 py-0.5 rounded-full flex-shrink-0" style="white-space: nowrap;">
                <?php gufte_icon("translate", "mr-1 w-3 h-3"); ?>
                <?php printf(esc_html(_n('%s translation', '%s translations', $translation_count, 'gufte')), number_format_i18n($translation_count)); ?>
            </span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Bottom Info: Line count and CTA -->
        <div class="flex items-center justify-between mt-auto">
            <!-- Line count -->
            <?php if ($line_count > 0) : ?>
            <span class="inline-flex items-center text-[10px] text-gray-500">
                <?php gufte_icon("counter", "mr-1 w-3 h-3"); ?>
                <?php echo esc_html($line_count); ?> <?php esc_html_e('lines', 'gufte'); ?>
            </span>
            <?php else : ?>
            <span></span>
            <?php endif; ?>

            <!-- CTA Button -->
            <?php if ($show_cta) : ?>
            <a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="inline-flex items-center text-[10px] font-semibold text-primary-600 hover:text-primary-700 transition-colors group">
                <span><?php esc_html_e('View Lyrics', 'gufte'); ?></span>
                <?php gufte_icon("arrow-right", "ml-0.5 w-2.5 h-2.5 group-hover:translate-x-1 transition-transform"); ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Re-enable smooth hover only on desktop */
@media (hover: hover) and (pointer: fine) {
    .hero-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .hero-card:hover {
        transform: scale(1.02);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
}
</style>
