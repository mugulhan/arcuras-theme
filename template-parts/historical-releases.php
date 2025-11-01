<?php
/**
 * Template part for displaying historical releases
 * 
 * @package Ashina Theme
 * @path /template-parts/historical-releases.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Data'yı al (bu template fonksiyon tarafından çağrılır)
global $monthly_releases_data;
if (empty($monthly_releases_data)) {
    $monthly_releases_data = gufte_get_monthly_releases_optimized();
}

if (empty($monthly_releases_data['posts'])) {
    return;
}
?>

<!-- Historical Releases Section -->
<div class="historical-releases-list mb-12 mt-12 md:mt-16">
    <div class="section-header flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800 flex items-center">
            <span class="iconify mr-3 text-primary-600 text-3xl" data-icon="mdi:calendar-month-outline"></span>
            <?php printf(esc_html__('%s Through the Years', 'gufte'), $monthly_releases_data['month_name']); ?>
        </h2>
        <div class="flex items-center space-x-3">
            <div class="text-sm text-gray-600 bg-primary-50 px-3 py-1.5 rounded-full border border-primary-200">
                <?php printf(esc_html(_n('%d song found', '%d songs found', $monthly_releases_data['total_count'], 'gufte')), $monthly_releases_data['total_count']); ?>
            </div>
            <?php if (current_user_can('manage_options')) : ?>
            <div class="text-xs text-gray-500 bg-green-50 px-2 py-1 rounded-full border border-green-200" title="Cached data - Admin View">
                <span class="iconify mr-1 text-green-600" data-icon="mdi:cached"></span>
                Cached
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Music List Container -->
    <div class="music-list-container bg-white rounded-xl shadow-sm border border-gray-200 overflow-visible">
        <!-- List Header -->
        <div class="list-header bg-gray-50 px-2 sm:px-4 py-3 border-b border-gray-200">
            <div class="flex items-center text-sm font-medium text-gray-600">
                <div class="w-8 sm:w-12 text-center flex-shrink-0">#</div>
                <div class="flex-1 flex items-center ml-2 sm:ml-4">
                    <span class="iconify mr-2" data-icon="mdi:music-note"></span>
                    <?php esc_html_e('Title', 'gufte'); ?>
                </div>
                <div class="hidden md:block w-32 text-center">
                    <span class="iconify mr-1" data-icon="mdi:calendar"></span>
                    <?php esc_html_e('Release', 'gufte'); ?>
                </div>
                <div class="hidden lg:block w-24 text-center">
                    <span class="iconify mr-1" data-icon="mdi:music-box"></span>
                    <?php esc_html_e('Genre', 'gufte'); ?>
                </div>
            </div>
        </div>

        <!-- Songs List -->
        <div class="songs-list">
            <?php 
            foreach ($monthly_releases_data['posts'] as $index => $song) :
                $release_year = !empty($song['release_date']) ? date('Y', strtotime($song['release_date'])) : '';
                $release_date_formatted = !empty($song['release_date']) ? date('M j, Y', strtotime($song['release_date'])) : '';
                $current_year = date('Y');
            ?>
            <div class="song-row group flex items-center px-2 sm:px-4 py-2 sm:py-3 hover:bg-gray-50 transition-colors duration-200 cursor-pointer border-b border-gray-100 last:border-b-0" data-href="<?php echo esc_url($song['permalink']); ?>">
                <!-- Track Number -->
                <div class="track-number w-8 sm:w-12 text-center flex-shrink-0">
                    <span class="track-num-text text-sm sm:text-base text-gray-500"><?php echo $index + 1; ?></span>
                    <span class="iconify track-num-icon hidden text-primary-600 text-base sm:text-lg" data-icon="mdi:play"></span>
                </div>

                <!-- Track Info -->
                <div class="track-info flex-1 flex items-start sm:items-center ml-2 sm:ml-4 min-w-0">
                    <!-- Album Art -->
                    <div class="album-art w-10 h-10 sm:w-12 sm:h-12 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0 mr-2 sm:mr-4">
                        <?php if (!empty($song['thumbnail'])) : ?>
                            <img src="<?php echo esc_url($song['thumbnail']); ?>" alt="<?php echo esc_attr($song['title']); ?>" class="w-full h-full object-cover">
                        <?php else : ?>
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                                <span class="iconify text-gray-400 text-sm sm:text-base" data-icon="mdi:music-note"></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Title & Artist -->
                    <div class="track-details flex-1 min-w-0" style="overflow: visible;">
                        <h3 class="track-title text-sm sm:text-base font-medium text-gray-900 line-clamp-1 sm:line-clamp-none group-hover:text-primary-600 transition-colors duration-200">
                            <?php echo esc_html($song['title']); ?>
                        </h3>

                        <!-- Artist Info -->
                        <div class="artist-info flex flex-wrap items-center gap-1 sm:gap-2 mt-0.5 sm:mt-1">
                            <?php if ($song['singer']) : ?>
                                <div class="flex items-center min-w-0">
                                    <?php if (!empty($song['singer']['image'])) : ?>
                                    <div class="w-3 h-3 sm:w-4 sm:h-4 rounded-full overflow-hidden mr-1 sm:mr-2 flex-shrink-0">
                                        <img src="<?php echo esc_url($song['singer']['image']); ?>" alt="<?php echo esc_attr($song['singer']['name']); ?>" class="w-full h-full object-cover">
                                    </div>
                                    <?php endif; ?>
                                    <a href="<?php echo esc_url($song['singer']['link']); ?>" class="text-xs sm:text-sm text-gray-600 truncate hover:text-primary-600 transition-colors duration-200">
                                        <?php echo esc_html($song['singer']['name']); ?>
                                    </a>
                                </div>
                            <?php else : ?>
                                <span class="text-xs sm:text-sm text-gray-500"><?php esc_html_e('Unknown Artist', 'gufte'); ?></span>
                            <?php endif; ?>

                            <?php if (!empty($release_year) && $release_year != $current_year) : ?>
                            <span class="year-badge text-xs bg-primary-100 text-primary-700 px-1.5 sm:px-2 py-0.5 rounded-full font-medium flex-shrink-0">
                                <?php echo esc_html($release_year); ?>
                            </span>
                            <?php endif; ?>
                        </div>

                        <!-- Categories & Languages (Mobile & Desktop) -->
                        <?php if (!empty($song['categories']) || !empty($song['translation_count'])) : ?>
                        <div class="track-meta flex flex-wrap items-center gap-1 sm:gap-1.5 mt-1 sm:mt-2">
                            <?php
                            // Kategorileri göster (max 1 mobile, 2 tablet, 3 desktop)
                            if (!empty($song['categories'])) :
                                $show_count = 0;
                                foreach ($song['categories'] as $category) :
                                    $show_count++;
                                    // Mobile: 1 kategori, Tablet: 2 kategori, Desktop: 3 kategori
                                    if ($show_count == 1) {
                                        $hidden_class = 'inline-flex';
                                    } elseif ($show_count == 2) {
                                        $hidden_class = 'hidden sm:inline-flex';
                                    } else {
                                        $hidden_class = 'hidden lg:inline-flex';
                                    }
                            ?>
                            <a href="<?php echo esc_url($category['link']); ?>" class="<?php echo $hidden_class; ?> items-center text-xs bg-gray-50 text-gray-700 hover:bg-gray-100 hover:border-gray-300 border border-gray-200 px-1.5 sm:px-2 py-0.5 rounded-full transition-colors duration-200">
                                <span class="iconify mr-0.5 sm:mr-1 text-xs" data-icon="mdi:music-box"></span>
                                <span class="truncate max-w-[60px] sm:max-w-none"><?php echo esc_html($category['name']); ?></span>
                            </a>
                            <?php
                                endforeach;
                            endif;
                            ?>

                            <?php if (!empty($song['translation_count']) && $song['translation_count'] > 0) : ?>
                            <div class="translations-dropdown-mini relative inline-flex items-center">
                                <button type="button" class="translations-trigger-mini inline-flex items-center text-xs bg-gray-50 text-gray-700 hover:bg-gray-100 hover:border-gray-300 border border-gray-200 px-1.5 sm:px-2 py-0.5 rounded-full flex-shrink-0 transition-all duration-200 cursor-pointer">
                                    <span class="iconify mr-1 text-xs" data-icon="mdi:translate"></span>
                                    <span>
                                        <?php echo esc_html($song['translation_count']); ?> <?php echo _n('translation', 'translations', $song['translation_count'], 'gufte'); ?>
                                    </span>
                                    <svg class="ml-1 w-3 h-3 dropdown-arrow-mini transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <?php
                                // Eğer lyrics_languages verisi yoksa, şimdi al
                                if (empty($song['lyrics_languages']) && function_exists('gufte_get_lyrics_languages')) {
                                    $content = get_post_field('post_content', $song['ID']);
                                    $song['lyrics_languages'] = gufte_get_lyrics_languages($content);
                                }
                                ?>
                                <?php if (!empty($song['lyrics_languages']['translations'])) : ?>
                                <div class="translations-menu-mini absolute top-full left-0 mt-2 bg-white rounded-lg shadow-xl border border-gray-200 py-2 min-w-48 opacity-0 invisible transform -translate-y-2 transition-all duration-300">
                                    <?php foreach ($song['lyrics_languages']['translations'] as $translation) :
                                        $translation_slug = sanitize_title($translation);
                                        $translation_url = add_query_arg('lang', $translation_slug, $song['permalink']);

                                        $language_info = array('flag' => '🌐', 'native_name' => $translation);
                                        if (function_exists('gufte_get_language_info')) {
                                            $language_info = gufte_get_language_info($translation);
                                        }
                                    ?>
                                    <a href="<?php echo esc_url($translation_url); ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors duration-200" onclick="event.stopPropagation()">
                                        <span class="text-lg mr-3"><?php echo $language_info['flag']; ?></span>
                                        <span class="font-medium"><?php echo esc_html($language_info['native_name']); ?></span>
                                        <span class="ml-auto text-xs text-gray-500"><?php echo esc_html($translation); ?></span>
                                    </a>
                                    <?php endforeach; ?>

                                    <div class="border-t border-gray-100 mt-2 pt-2">
                                        <a href="<?php echo esc_url($song['permalink']); ?>" class="flex items-center px-4 py-2 text-sm text-gray-900 hover:bg-gray-100 font-medium transition-colors duration-200" onclick="event.stopPropagation()">
                                            <svg class="mr-3 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <?php esc_html_e('View All Languages', 'gufte'); ?>
                                        </a>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                </div>

                <!-- Release Date -->
                <div class="release-date hidden md:block w-32 text-center">
                    <?php if (!empty($release_date_formatted)) : ?>
                    <span class="text-sm text-gray-600"><?php echo esc_html($release_date_formatted); ?></span>
                    <?php else : ?>
                    <span class="text-sm text-gray-400">-</span>
                    <?php endif; ?>
                </div>

                <!-- Genre -->
                <div class="genre hidden lg:block w-24 text-center">
                    <?php if (!empty($song['music_genre'])) : ?>
                    <span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-full font-medium">
                        <?php echo esc_html($song['music_genre']); ?>
                    </span>
                    <?php else : ?>
                    <span class="text-sm text-gray-400">-</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- List Footer -->
        <div class="list-footer bg-gray-50 px-4 py-3 border-t border-gray-200">
            <div class="flex items-center justify-between text-sm text-gray-600">
                <div class="flex items-center">
                    <span class="iconify mr-2 text-primary-600" data-icon="mdi:information-outline"></span>
                    <?php printf(esc_html__('Songs released in %s across different years', 'gufte'), $monthly_releases_data['month_name']); ?>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if (current_user_can('manage_options')) : ?>
                    <span class="text-xs text-green-600">
                        <span class="iconify mr-1" data-icon="mdi:clock-fast"></span>
                        Cached for 24h
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.historical-releases-list .song-row {
    transition: all 0.2s ease;
}

.historical-releases-list .song-row:hover {
    background-color: rgba(249, 250, 251, 0.8);
    transform: translateX(2px);
}

.historical-releases-list .album-art {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease;
}

.historical-releases-list .song-row:hover .album-art {
    transform: scale(1.05);
}

.historical-releases-list .track-number {
    font-variant-numeric: tabular-nums;
}

.historical-releases-list .song-row:hover .track-num-text {
    display: none;
}

.historical-releases-list .song-row:hover .track-num-icon {
    display: inline-block;
}

.historical-releases-list .track-meta a {
    transition: all 0.2s ease;
}

.historical-releases-list .track-meta a:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Mobile optimizations */
@media (max-width: 640px) {
    .historical-releases-list .song-row {
        min-height: 60px;
    }

    .historical-releases-list .track-details {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .historical-releases-list .track-title {
        font-size: 0.875rem;
        line-height: 1.25rem;
    }

    .historical-releases-list .artist-info {
        font-size: 0.75rem;
    }

    .historical-releases-list .track-meta {
        margin-top: 0.375rem;
    }

    /* Badge spacing mobilde daha kompakt */
    .historical-releases-list .track-meta > * {
        font-size: 0.625rem;
    }
}

/* Tablet optimizations */
@media (min-width: 640px) and (max-width: 768px) {
    .historical-releases-list .album-art {
        width: 2.75rem;
        height: 2.75rem;
    }
}

/* Text overflow handling */
.historical-releases-list .track-title,
.historical-releases-list .artist-info a {
    overflow-wrap: break-word;
    word-wrap: break-word;
    word-break: break-word;
}

/* Line clamping for mobile */
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Translations Dropdown */
.translations-dropdown-mini {
    position: relative;
    display: inline-flex;
    align-items: center;
}

.translations-trigger-mini {
    position: relative;
    z-index: 1;
}

.translations-menu-mini {
    position: absolute;
    max-height: 300px;
    overflow-y: auto;
    pointer-events: none;
    z-index: 99999 !important;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.translations-dropdown-mini:hover .translations-menu-mini,
.translations-dropdown-mini.active .translations-menu-mini {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
    pointer-events: auto !important;
}

.translations-menu-mini a {
    pointer-events: auto !important;
    cursor: pointer;
    position: relative;
    z-index: 10000;
}

.translations-dropdown-mini:hover .dropdown-arrow-mini,
.translations-dropdown-mini.active .dropdown-arrow-mini {
    transform: rotate(180deg);
}

/* Hover area genişlet - dropdown ile button arası boşluğu doldur */
.translations-dropdown-mini::before {
    content: '';
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    height: 10px;
    z-index: 98;
}

/* Song row'un overflow'unu düzelt */
.historical-releases-list .song-row {
    position: relative;
    overflow: visible !important;
    z-index: 1;
}

.historical-releases-list .song-row:has(.translations-dropdown-mini.active),
.historical-releases-list .song-row:has(.translations-dropdown-mini:hover) {
    z-index: 1001;
}

.historical-releases-list .songs-list {
    overflow: visible !important;
}

.historical-releases-list .music-list-container {
    overflow: visible !important;
}

.historical-releases-list .track-meta {
    position: relative;
    overflow: visible !important;
}

.historical-releases-list .track-info {
    overflow: visible !important;
}

.historical-releases-list .track-details {
    overflow: visible !important;
}

.historical-releases-list .track-title {
    overflow: hidden;
    text-overflow: ellipsis;
}

.historical-releases-list .artist-info {
    overflow: hidden;
}

.historical-releases-list .year-badge {
    position: relative;
    z-index: 0;
}

/* Kategorilerin z-index'i düşük olsun ki dropdown üstte kalsın */
.historical-releases-list .track-meta > a {
    position: relative;
    z-index: 0;
}

/* Dropdown container en üstte */
.translations-dropdown-mini {
    z-index: 1000 !important;
}

.translations-menu-mini::-webkit-scrollbar {
    width: 6px;
}

.translations-menu-mini::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.translations-menu-mini::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.translations-menu-mini::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Song row click handler
    document.querySelectorAll('.song-row').forEach(row => {
        row.addEventListener('click', function(e) {
            // Dropdown menü içindeki linklere izin ver
            if (e.target.closest('.translations-menu-mini a')) {
                return;
            }

            // Dropdown, link veya button'a tıklanırsa normal davranış
            if (e.target.closest('a') || e.target.closest('button') || e.target.closest('.translations-dropdown-mini')) {
                return;
            }
            window.location.href = this.dataset.href;
        });
    });

    // Translation dropdown toggle (mobile için)
    document.querySelectorAll('.translations-trigger-mini').forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();

            const dropdown = this.closest('.translations-dropdown-mini');
            const isActive = dropdown.classList.contains('active');

            // Tüm açık dropdown'ları kapat
            document.querySelectorAll('.translations-dropdown-mini.active').forEach(d => {
                d.classList.remove('active');
            });

            // Bu dropdown'ı toggle et
            if (!isActive) {
                dropdown.classList.add('active');
            }
        });
    });

    // Dropdown menu linkleri için özel handler
    document.querySelectorAll('.translations-menu-mini a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.stopPropagation();
            // Link normal çalışsın - prevent etmeyin
        });
    });

    // Dropdown dışına tıklandığında kapat
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.translations-dropdown-mini')) {
            document.querySelectorAll('.translations-dropdown-mini.active').forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }
    });
});
</script>
