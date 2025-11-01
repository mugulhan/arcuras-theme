<?php
/**
 * Arcuras Sidebar Template
 *
 * Renders the left sidebar for the site.
 * Visible on desktop and tablet views, hidden on mobile devices.
 * Logo has been removed, sticky feature is maintained.
 * Accent color (#c025d3 / accent-600) is used for active menu items.
 * Includes 'Recently Viewed' and conditional 'My Profile' links.
 *
 * @package Gufte
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Helper variables to determine the active section
$is_singer_section     = is_tax('singer');
$is_album_section      = is_tax('album');
$is_category_section   = is_category();
$is_tag_section        = is_tag();

// NEW: Producer & Songwriter sections (taxonomy/single checks)
$is_producer_section   = is_tax('producer');
$is_songwriter_section = is_tax('songwriter');

$current_term_id = (is_tax() || is_category() || is_tag()) ? get_queried_object_id() : 0;

// --- IMPORTANT: Update these slugs to match your actual page slugs in WordPress ---
$lyrics_page_slug        = 'lyrics';        // <— Lyrics liste sayfanızın slug'ı (örn. /lyrics/ veya /sarki-sozleri/)
$all_categories_page_slug = 'categories';   // Example: /categories/
$all_tags_page_slug       = 'tags';         // Example: /tags/
$popular_page_slug        = 'popular';      // Example: /popular/
$recent_views_page_slug   = 'recent-views'; // Slug for Recently Viewed page
$my_profile_page_slug     = 'my-profile';   // User profile page slug

// NEW: listing pages for producers & songwriters
$singers_page_slug        = 'singers';      // /singers/
$producers_page_slug      = 'producers';    // /producers/
$songwriters_page_slug    = 'songwriters';  // /songwriters/
// --- End of slugs to update ---

// Page checks
$is_all_categories_page = is_page($all_categories_page_slug);
$is_all_tags_page       = is_page($all_tags_page_slug);
$is_popular_page        = is_page($popular_page_slug);
$is_recent_views_page   = is_page($recent_views_page_slug);
$is_my_profile_page     = is_page($my_profile_page_slug);

// NEW: page checks to highlight menu when on those pages
$is_singers_page        = is_page($singers_page_slug);
$is_producers_page       = is_page($producers_page_slug);
$is_songwriters_page     = is_page($songwriters_page_slug);

// NEW: Lyrics page checks (hem sayfa hem de şablon adına bakar)
$is_lyrics_page = is_page($lyrics_page_slug)
    || is_page_template('lyrics-page.php')
    || is_page_template('template-lyrics.php')
    || is_page_template('page-lyrics.php');

// Lyrics sekmesi şarkı detay (single post) sayfalarında da aktif olsun istiyoruz:
$is_lyrics_section = $is_lyrics_page || is_singular('post');

// Determine if the categories submenu should be open initially
$is_categories_submenu_open = $is_category_section || $is_all_categories_page;

// Color classes for highlighting
$active_text_color          = 'text-accent-600';
$active_bg_color            = 'bg-accent-50';
$inactive_text_color        = 'text-gray-700';
$inactive_hover_text_color  = 'hover:text-accent-600';
$inactive_hover_bg_color    = 'hover:bg-gray-50';

// URLs (you can override if needed)
$singers_archive_url     = home_url('/singers/');
$albums_archive_url      = home_url('/albums/');
$producers_archive_url   = home_url('/producers/');
$songwriters_archive_url = home_url('/songwriters/');

// Lyrics URL: Önce sayfa var mı kontrol et, yoksa blog arşivini kullan
$lyrics_page_by_path = get_page_by_path( $lyrics_page_slug );
if ( $lyrics_page_by_path ) {
    $lyrics_page_url = get_permalink( $lyrics_page_by_path );
} else {
    // Eğer 'lyrics' sayfası yoksa, blog arşiv sayfasını kullan
    $blog_page_id = get_option('page_for_posts');
    if ($blog_page_id) {
        $lyrics_page_url = get_permalink($blog_page_id);
    } else {
        $lyrics_page_url = home_url('/lyrics/');
    }
}
?>

<aside id="arcuras-sidebar" class="arcuras-sidebar hidden md:block w-64 bg-white border-r border-gray-200 h-screen sticky top-0 overflow-y-auto flex flex-col">

    <?php // Logo and Site Title section removed ?>

    <nav class="sidebar-nav p-4 flex-grow" aria-label="<?php esc_attr_e('Sidebar Menu', 'gufte'); ?>">
        <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">
            <?php esc_html_e('Menu', 'gufte'); ?>
        </h2>

        <ul class="space-y-1">
            <?php // Home Link ?>
            <li>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center px-3 py-2 rounded-md transition-colors duration-200 <?php echo is_front_page() ? "{$active_text_color} {$active_bg_color} font-medium" : "{$inactive_text_color} {$inactive_hover_text_color} {$inactive_hover_bg_color}"; ?>" <?php if(is_front_page()) echo 'aria-current="page"'; ?>>
                    <?php gufte_icon('home', 'mr-3 w-5 h-5'); ?>
                    <?php esc_html_e('Home', 'gufte'); ?>
                </a>
            </li>

            <?php // NEW: Lyrics with Dropdown (Original & Translated) ?>
            <?php if ($lyrics_page_url): ?>
            <li>
                <div class="flex items-center gap-1">
                    <a href="<?php echo esc_url($lyrics_page_url); ?>" class="flex-1 flex items-center px-3 py-2 rounded-md transition-colors duration-200 <?php echo $is_lyrics_section ? "{$active_text_color} {$active_bg_color} font-medium" : "{$inactive_text_color} {$inactive_hover_text_color} {$inactive_hover_bg_color}"; ?>" <?php if($is_lyrics_section) echo 'aria-current="page"'; ?>>
                        <?php gufte_icon('music', 'mr-3 w-5 h-5'); ?>
                        <?php esc_html_e('Lyrics', 'gufte'); ?>
                    </a>
                    <button class="px-2 py-2 rounded-md transition-colors duration-200 <?php echo $is_lyrics_section ? "{$active_text_color}" : "{$inactive_text_color} {$inactive_hover_text_color}"; ?>" onclick="toggleSubmenu('lyrics-submenu')" aria-controls="lyrics-submenu" aria-expanded="false">
                        <?php gufte_icon('chevron-down', 'submenu-icon transform transition-transform duration-200 w-5 h-5'); ?>
                    </button>
                </div>

                <ul id="lyrics-submenu" class="pl-6 mt-1 space-y-1 hidden">
                    <li>
                        <a href="<?php echo esc_url(home_url('/lyrics/original/')); ?>" class="flex items-center text-sm py-1.5 rounded-md transition-colors duration-200 group text-gray-600 <?php echo $inactive_hover_text_color . ' ' . $inactive_hover_bg_color; ?>">
                            <?php gufte_icon("circle-medium", "mr-3 w-3 h-3 transition-colors duration-200 text-gray-400 group-hover:text-accent-500"); ?>
                            <span class="flex-grow"><?php esc_html_e('Original', 'gufte'); ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo esc_url(home_url('/lyrics/translation/')); ?>" class="flex items-center text-sm py-1.5 rounded-md transition-colors duration-200 group text-gray-600 <?php echo $inactive_hover_text_color . ' ' . $inactive_hover_bg_color; ?>">
                            <?php gufte_icon("circle-medium", "mr-3 w-3 h-3 transition-colors duration-200 text-gray-400 group-hover:text-accent-500"); ?>
                            <span class="flex-grow"><?php esc_html_e('Translation', 'gufte'); ?></span>
                        </a>
                    </li>
                </ul>
            </li>
            <?php endif; ?>

            <?php // Albums Link ?>
            <?php if (taxonomy_exists('album')) : ?>
            <li>
                <a href="<?php echo esc_url($albums_archive_url); ?>" class="flex items-center px-3 py-2 rounded-md transition-colors duration-200 <?php echo $is_album_section ? "{$active_text_color} {$active_bg_color} font-medium" : "{$inactive_text_color} {$inactive_hover_text_color} {$inactive_hover_bg_color}"; ?>" <?php if($is_album_section) echo 'aria-current="true"'; ?>>
                    <?php gufte_icon('album', 'mr-3 w-5 h-5'); ?>
                    <?php esc_html_e('Albums', 'gufte'); ?>
                </a>
            </li>
            <?php endif; ?>

            <?php // Singers Link ?>
            <?php if (taxonomy_exists('singer')) : ?>
            <li>
                <a href="<?php echo esc_url($singers_archive_url); ?>" class="flex items-center px-3 py-2 rounded-md transition-colors duration-200 <?php echo ($is_singer_section || $is_singers_page) ? "{$active_text_color} {$active_bg_color} font-medium" : "{$inactive_text_color} {$inactive_hover_text_color} {$inactive_hover_bg_color}"; ?>" <?php if($is_singer_section || $is_singers_page) echo 'aria-current="true"'; ?>>
                    <?php gufte_icon('microphone', 'mr-3 w-5 h-5'); ?>
                    <?php esc_html_e('Singers', 'gufte'); ?>
                </a>
            </li>
            <?php endif; ?>

            <?php // NEW: Songwriters Link ?>
            <li>
                <a href="<?php echo esc_url($songwriters_archive_url); ?>" class="flex items-center px-3 py-2 rounded-md transition-colors duration-200 <?php echo ($is_songwriter_section || $is_songwriters_page) ? "{$active_text_color} {$active_bg_color} font-medium" : "{$inactive_text_color} {$inactive_hover_text_color} {$inactive_hover_bg_color}"; ?>" <?php if($is_songwriter_section || $is_songwriters_page) echo 'aria-current="true"'; ?>>
                    <?php gufte_icon('pen', 'mr-3 w-5 h-5'); ?>
                    <?php esc_html_e('Songwriters', 'gufte'); ?>
                </a>
            </li>

            <?php // NEW: Producers Link ?>
            <li>
                <a href="<?php echo esc_url($producers_archive_url); ?>" class="flex items-center px-3 py-2 rounded-md transition-colors duration-200 <?php echo ($is_producer_section || $is_producers_page) ? "{$active_text_color} {$active_bg_color} font-medium" : "{$inactive_text_color} {$inactive_hover_text_color} {$inactive_hover_bg_color}"; ?>" <?php if($is_producer_section || $is_producers_page) echo 'aria-current="true"'; ?>>
                    <?php gufte_icon('console', 'mr-3 w-5 h-5'); ?>
                    <?php esc_html_e('Producers', 'gufte'); ?>
                </a>
            </li>

            <?php // --- Conditional Links for Logged-in Users --- ?>
            <?php if ( is_user_logged_in() ) : ?>

                <?php // Recently Viewed Link (Only if user is logged in) ?>
                <?php $recent_views_page_url = get_permalink( get_page_by_path( $recent_views_page_slug ) ); ?>
                <?php if ($recent_views_page_url): ?>
                <li>
                    <a href="<?php echo esc_url($recent_views_page_url); ?>" class="flex items-center px-3 py-2 rounded-md transition-colors duration-200 <?php echo $is_recent_views_page ? "{$active_text_color} {$active_bg_color} font-medium" : "{$inactive_text_color} {$inactive_hover_text_color} {$inactive_hover_bg_color}"; ?>" <?php if($is_recent_views_page) echo 'aria-current="page"'; ?>>
                        <?php gufte_icon("history", "mr-3 w-5 h-5"); ?>
                        <?php esc_html_e('Recently Viewed', 'gufte'); ?>
                    </a>
                </li>
                <?php endif; ?>

                <?php // My Profile Link (Only if user is logged in) ?>
                <?php $my_profile_page_url = get_permalink( get_page_by_path( $my_profile_page_slug ) ); ?>
                <?php if ($my_profile_page_url): ?>
                <li>
                    <a href="<?php echo esc_url($my_profile_page_url); ?>" class="flex items-center px-3 py-2 rounded-md transition-colors duration-200 <?php echo $is_my_profile_page ? "{$active_text_color} {$active_bg_color} font-medium" : "{$inactive_text_color} {$inactive_hover_text_color} {$inactive_hover_bg_color}"; ?>" <?php if($is_my_profile_page) echo 'aria-current="page"'; ?>>
                        <?php gufte_icon("account", "mr-3 w-5 h-5"); ?>
                        <?php esc_html_e('My Profile', 'gufte'); ?>
                    </a>
                </li>
                <?php endif; ?>

            <?php endif; // End is_user_logged_in() ?>
            <?php // --- End Conditional Links --- ?>

        </ul>
    </nav>

    <div class="sidebar-search p-4 border-t border-gray-100">
        <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
            <?php esc_html_e('Search Songs', 'gufte'); ?>
        </h2>
        <form role="search" method="get" class="search-form relative flex items-center" action="<?php echo esc_url(home_url('/')); ?>">
            <label for="sidebar-search-field" class="sr-only"><?php esc_html_e('Search Songs', 'gufte'); ?></label>
            <?php gufte_icon("magnify", "absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 z-10 w-5 h-5"); ?>
            <input type="search" id="sidebar-search-field"
                   class="w-full rounded-md border-gray-300 focus:border-accent-300 focus:ring focus:ring-accent-200 focus:ring-opacity-50 text-sm pl-10 pr-3 py-2"
                   placeholder="<?php esc_attr_e('Song, singer...', 'gufte'); ?>"
                   value="<?php echo get_search_query(); ?>"
                   name="s" />
            <button type="submit" class="sr-only"><?php esc_html_e('Search', 'gufte'); ?></button>
        </form>
    </div>

    <div class="sidebar-footer p-4 border-t border-gray-200 text-xs text-gray-500 mt-auto text-center">
        <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. <?php esc_html_e('All rights reserved.', 'gufte'); ?></p>
    </div>
</aside>

<script>
function toggleSubmenu(id) {
    const submenu = document.getElementById(id);
    if (!submenu) return;
    const button = submenu.previousElementSibling;
    const icon = button ? button.querySelector('.submenu-icon') : null;
    const isHidden = submenu.classList.contains('hidden');
    submenu.classList.toggle('hidden');
    if (isHidden) {
         if (icon) icon.classList.add('rotate-180');
         if (button) button.setAttribute('aria-expanded', 'true');
    } else {
        if (icon) icon.classList.remove('rotate-180');
        if (button) button.setAttribute('aria-expanded', 'false');
    }
}
document.addEventListener('DOMContentLoaded', function() {
    const initiallyOpenButton = document.querySelector('.sidebar-nav button[aria-expanded="true"]');
    if (initiallyOpenButton) {
         const submenuId = initiallyOpenButton.getAttribute('aria-controls');
         const submenu = document.getElementById(submenuId);
         const icon = initiallyOpenButton.querySelector('.submenu-icon');
         if (submenu && submenu.classList.contains('hidden')){ submenu.classList.remove('hidden'); }
         if(icon && !icon.classList.contains('rotate-180')) { icon.classList.add('rotate-180'); }
    }
    const activeSubmenuLink = document.querySelector('.sidebar-nav ul ul a[aria-current="page"]');
    if (activeSubmenuLink) {
        const submenu = activeSubmenuLink.closest('ul[id$="-submenu"]');
        if (submenu && submenu.classList.contains('hidden')) {
             const button = submenu.previousElementSibling;
             if (button && button.getAttribute('aria-expanded') === 'false') { toggleSubmenu(submenu.id); }
        }
    }
});
</script>
