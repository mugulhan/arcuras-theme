<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Arcuras
 */

?>
    </div><!-- #content -->

    <footer id="colophon" class="site-footer bg-gray-100 text-gray-600 pt-12 pb-6" role="contentinfo">
        <div class="max-w-6xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
                
                <div class="footer-widget">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4"><?php esc_html_e('About Us', 'arcuras'); ?></h3>
                    <div class="text-sm leading-relaxed">
                        <?php
                        if ( is_active_sidebar( 'footer-1' ) ) {
                            dynamic_sidebar( 'footer-1' );
                        } else {
                            echo '<p>' . esc_html__('The gateway to lyrics for the world.', 'arcuras') . '</p>';
                        }
                        ?>
                    </div>
                </div>
                
                <div class="footer-widget">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4"><?php esc_html_e('Quick Links', 'arcuras'); ?></h3>
                    <ul class="text-sm space-y-2">
                        <li>
                            <a href="<?php echo esc_url( get_permalink( get_page_by_path('lyrics') ) ); ?>" class="hover:text-primary-600 transition-colors duration-200 flex items-center">
                                <?php gufte_icon('music', 'mr-2 w-4 h-4 text-primary-500'); ?>
                                <?php esc_html_e('Lyrics', 'arcuras'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo esc_url( home_url('/singers/') ); ?>" class="hover:text-primary-600 transition-colors duration-200 flex items-center">
                                <?php gufte_icon('microphone', 'mr-2 w-4 h-4 text-primary-500'); ?>
                                <?php esc_html_e('Singers', 'arcuras'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo esc_url( home_url('/songwriters/') ); ?>" class="hover:text-primary-600 transition-colors duration-200 flex items-center">
                                <?php gufte_icon('pen', 'mr-2 w-4 h-4 text-primary-500'); ?>
                                <?php esc_html_e('Songwriters', 'arcuras'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo esc_url( home_url('/albums/') ); ?>" class="hover:text-primary-600 transition-colors duration-200 flex items-center">
                                <?php gufte_icon('album', 'mr-2 w-4 h-4 text-primary-500'); ?>
                                <?php esc_html_e('Albums', 'arcuras'); ?>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="footer-widget">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4"><?php esc_html_e('Recent Posts', 'arcuras'); ?></h3>
                    <?php
                    if ( is_active_sidebar( 'footer-3' ) ) {
                        dynamic_sidebar( 'footer-3' );
                    } else {
                        $recent_posts = wp_get_recent_posts(array(
                            'numberposts' => 4,
                            'post_status' => 'publish'
                        ));
                        
                        if ($recent_posts) {
                            echo '<ul class="text-sm space-y-2">';
                            foreach ($recent_posts as $post) {
                                echo '<li><a href="' . get_permalink($post['ID']) . '" class="hover:text-blue-600 transition-colors duration-300">' . $post['post_title'] . '</a></li>';
                            }
                            echo '</ul>';
                            wp_reset_postdata();
                        }
                    }
                    ?>
                </div>
                
                <div class="footer-widget">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4"><?php esc_html_e('Contact Us', 'arcuras'); ?></h3>
                    <div class="text-sm leading-relaxed">
                        <?php
                        if ( is_active_sidebar( 'footer-4' ) ) {
                            dynamic_sidebar( 'footer-4' );
                        } else {
                            ?>
                            <p class="mb-4"><?php esc_html_e('Have questions or feedback? Get in touch with our team.', 'arcuras'); ?></p>
                            <div class="flex items-center mb-2">
                                <?php gufte_icon('email', 'text-blue-600 mr-2 w-5 h-5'); ?>
                                <a href="mailto:contact@arcuras.com" class="hover:text-blue-700 transition-colors duration-300">contact@arcuras.com</a>
                            </div>
                            <form class="flex mt-4" action="#" method="post" role="form" aria-label="<?php esc_attr_e('Newsletter signup', 'arcuras'); ?>">
                                <label for="footer-email" class="sr-only"><?php esc_html_e('Your email address', 'arcuras'); ?></label>
                                <input type="email"
                                       id="footer-email"
                                       name="email"
                                       placeholder="<?php esc_attr_e('Your email address', 'arcuras'); ?>"
                                       class="flex-grow px-3 py-2 bg-white border border-gray-300 text-gray-800 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       required>
                                <button type="submit"
                                        class="px-4 py-2 bg-blue-600 text-white rounded-r-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-300"
                                        aria-label="<?php esc_attr_e('Subscribe to newsletter', 'arcuras'); ?>">
                                    <?php gufte_icon('arrow-right', 'w-5 h-5'); ?>
                                </button>
                            </form>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                
            </div>
            
            <div class="border-t border-gray-200 pt-6 flex flex-col md:flex-row md:justify-between md:items-center">
                <div class="footer-info mb-4 md:mb-0">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="text-lg font-bold text-gray-800 hover:text-blue-600 transition-colors duration-300">
                        <?php bloginfo( 'name' ); ?>
                    </a>
                    <p class="text-sm mt-2">
                        <?php
                        /* translators: %1$s: Current year, %2$s: Blog name */
                        printf( esc_html__( '© %1$s %2$s. All rights reserved.', 'arcuras' ), date_i18n( 'Y' ), get_bloginfo( 'name', 'display' ) );
                        ?>
                    </p>
                    <div class="footer-legal-links mt-2 text-xs space-x-4">
                        <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>" class="hover:text-blue-600 transition-colors duration-300"><?php esc_html_e('Privacy Policy', 'arcuras'); ?></a>
                        <a href="<?php echo esc_url(home_url('/terms-of-service/')); ?>" class="hover:text-blue-600 transition-colors duration-300"><?php esc_html_e('Terms of Service', 'arcuras'); ?></a>
                    </div>
                </div>
                
                <div class="footer-social">
                    <div class="flex space-x-4" role="list" aria-label="<?php esc_attr_e('Social media links', 'arcuras'); ?>">
                        <a href="https://www.facebook.com/people/Arcuras/61574983734689/"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="text-gray-500 hover:text-blue-600 transition-colors duration-300"
                           role="listitem"
                           aria-label="<?php esc_attr_e('Follow us on Facebook (opens in new window)', 'arcuras'); ?>">
                            <?php gufte_icon('facebook', 'w-5 h-5'); ?>
                        </a>
                        <a href="https://www.instagram.com/arcurasofficial/"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="text-gray-500 hover:text-blue-600 transition-colors duration-300"
                           role="listitem"
                           aria-label="<?php esc_attr_e('Follow us on Instagram (opens in new window)', 'arcuras'); ?>">
                            <?php gufte_icon('instagram', 'w-5 h-5'); ?>
                        </a>
                        <a href="http://tiktok.com/@arcurasapp"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="text-gray-500 hover:text-blue-600 transition-colors duration-300"
                           role="listitem"
                           aria-label="<?php esc_attr_e('Follow us on TikTok (opens in new window)', 'arcuras'); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5"><path fill="currentColor" d="M16.6 5.82s.51.5 0 0A4.278 4.278 0 0 1 15.54 3h-3.09v12.4a2.592 2.592 0 0 1-2.59 2.5c-1.42 0-2.6-1.16-2.6-2.6c0-1.72 1.66-3.01 3.37-2.48V9.66c-3.45-.46-6.47 2.22-6.47 5.64c0 3.33 2.76 5.7 5.69 5.7c3.14 0 5.69-2.55 5.69-5.7V9.01a7.35 7.35 0 0 0 4.3 1.38V7.3s-1.88.09-3.24-1.48z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer><!-- #colophon -->
</div><!-- #page -->

<?php
// Sticky Player - Fixed music player for single posts
if (is_single()) :
    // Get Apple Music URL or ID
    $apple_music_input = get_post_meta(get_the_ID(), '_apple_music_id', true);

    // Extract clean song ID
    $apple_music_id = false;
    if ($apple_music_input && function_exists('arcuras_extract_apple_music_id')) {
        $apple_music_id = arcuras_extract_apple_music_id($apple_music_input);
    }

    // Get preview URL and artwork from iTunes API
    $preview_url = false;
    $artwork = false;

    if ($apple_music_id && function_exists('arcuras_get_apple_music_preview')) {
        $preview_url = arcuras_get_apple_music_preview($apple_music_input);
        $artwork = arcuras_get_apple_music_artwork($apple_music_input);
    }

    // Fallback to embed codes if no Apple Music ID
    $spotify_embed = get_post_meta(get_the_ID(), 'spotify_embed', true);
    $youtube_embed = get_post_meta(get_the_ID(), 'youtube_embed', true);
    $apple_music_embed = get_post_meta(get_the_ID(), 'apple_music_embed', true);

    // Add title attribute to iframes for accessibility
    $song_title = get_the_title();
    if ($apple_music_embed && strpos($apple_music_embed, '<iframe') !== false) {
        // Add title to Apple Music iframe if not present
        if (strpos($apple_music_embed, 'title=') === false) {
            $apple_music_embed = str_replace(
                '<iframe',
                '<iframe title="' . esc_attr(sprintf(__('Apple Music player for %s', 'gufte'), $song_title)) . '"',
                $apple_music_embed
            );
        }
    }

    if ($spotify_embed && strpos($spotify_embed, '<iframe') !== false) {
        // Add title to Spotify iframe if not present
        if (strpos($spotify_embed, 'title=') === false) {
            $spotify_embed = str_replace(
                '<iframe',
                '<iframe title="' . esc_attr(sprintf(__('Spotify player for %s', 'gufte'), $song_title)) . '"',
                $spotify_embed
            );
        }
    }

    if ($youtube_embed && strpos($youtube_embed, '<iframe') !== false) {
        // Add title to YouTube iframe if not present
        if (strpos($youtube_embed, 'title=') === false) {
            $youtube_embed = str_replace(
                '<iframe',
                '<iframe title="' . esc_attr(sprintf(__('YouTube player for %s', 'gufte'), $song_title)) . '"',
                $youtube_embed
            );
        }
    }

    // Show player if preview URL or embed codes exist
    if ($preview_url || $apple_music_embed || $spotify_embed || $youtube_embed) :
?>

<?php if ($preview_url) : ?>
<!-- Modern Apple Music Preview Player -->
<div id="apple-preview-player" class="fixed bottom-0 left-0 right-0 shadow-2xl z-50" style="background: linear-gradient(to bottom, rgba(30, 41, 59, 0.65), rgba(30, 41, 59, 0.95)); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-top: 1px solid rgba(148, 163, 184, 0.2);" role="complementary" aria-label="<?php esc_attr_e('Apple Music Preview Player', 'arcuras'); ?>">
    <div class="max-w-7xl mx-auto px-3 sm:px-4 py-3 sm:py-4">
        <div class="flex flex-col gap-3">

            <!-- Top Row: Album Art, Song Info, and Play Button -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Album Art -->
                <?php if ($artwork && isset($artwork['medium'])) : ?>
                <img
                    src="<?php echo esc_url($artwork['medium']); ?>"
                    alt="<?php echo esc_attr($artwork['track_name']); ?>"
                    class="w-12 h-12 sm:w-14 sm:h-14 rounded-lg shadow-xl flex-shrink-0"
                />
                <?php elseif (has_post_thumbnail()) : ?>
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-lg overflow-hidden shadow-xl flex-shrink-0">
                    <?php the_post_thumbnail('thumbnail', array('class' => 'w-full h-full object-cover')); ?>
                </div>
                <?php endif; ?>

                <!-- Song Info (grows to fill available space) -->
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-sm sm:text-base truncate" style="color: #ffffff;">
                        <?php echo esc_html($artwork && isset($artwork['track_name']) ? $artwork['track_name'] : get_the_title()); ?>
                    </div>
                    <div class="text-xs sm:text-sm truncate" style="color: #94a3b8;">
                        <?php
                        if ($artwork && isset($artwork['artist_name'])) {
                            echo esc_html($artwork['artist_name']);
                        } else {
                            $singers = get_the_terms(get_the_ID(), 'singer');
                            if ($singers && !is_wp_error($singers)) {
                                echo esc_html($singers[0]->name);
                            }
                        }
                        ?>
                    </div>
                </div>

                <!-- Mobile: Play Button & Apple Music Icon -->
                <div class="sm:hidden flex items-center gap-2 flex-shrink-0">
                    <button id="preview-play-btn" class="w-10 h-10 rounded-full bg-white hover:bg-gray-100 flex items-center justify-center transition-all shadow-xl" aria-label="Play">
                        <svg class="w-5 h-5 text-black" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </button>
                    <a href="https://music.apple.com/song/<?php echo esc_attr($apple_music_id); ?>"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="w-10 h-10 rounded-full bg-white hover:bg-gray-100 flex items-center justify-center transition-all shadow-xl"
                       aria-label="Open in Apple Music">
                        <?php gufte_icon('apple', 'w-5 h-5 text-black'); ?>
                    </a>
                </div>
            </div>

            <!-- Desktop Controls Row -->
            <div class="hidden sm:flex items-center gap-4">
                <!-- Play Button -->
                <button id="preview-play-btn" class="w-12 h-12 rounded-full bg-white hover:bg-gray-100 flex items-center justify-center transition-all shadow-xl hover:scale-105 flex-shrink-0" aria-label="Play">
                    <svg class="w-6 h-6 text-black" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                </button>

                <!-- Progress Bar -->
                <div class="flex items-center gap-3 flex-1">
                    <span id="current-time" class="text-xs font-medium w-10 text-right" style="color: #cbd5e1;">0:00</span>
                    <div class="flex-1 h-2 bg-gray-800 rounded-full cursor-pointer relative group" id="progress-bar">
                        <div id="progress-fill" class="h-full bg-white rounded-full transition-all shadow-lg" style="width: 0%"></div>
                    </div>
                    <span id="duration" class="text-xs font-medium w-10" style="color: #cbd5e1;">0:30</span>
                </div>

                <!-- Volume Control -->
                <div class="flex items-center gap-2 flex-shrink-0">
                    <svg class="w-5 h-5" style="color: #cbd5e1;" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02z"/>
                    </svg>
                    <input type="range" id="volume-slider" min="0" max="100" value="70" class="w-24 h-2 bg-gray-800 rounded-full appearance-none cursor-pointer">
                </div>

                <!-- Apple Music Link -->
                <a href="https://music.apple.com/song/<?php echo esc_attr($apple_music_id); ?>"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="px-4 py-2 bg-white hover:bg-gray-100 text-black text-xs font-semibold rounded-full transition-all flex items-center gap-2 shadow-lg hover:shadow-xl flex-shrink-0">
                    <?php gufte_icon('apple', 'w-4 h-4'); ?>
                    <span>Open in Apple Music</span>
                </a>
            </div>

            <!-- Mobile Progress Bar -->
            <div class="sm:hidden flex items-center gap-2">
                <span id="current-time-mobile" class="text-xs font-medium w-10 text-right flex-shrink-0" style="color: #cbd5e1;">0:00</span>
                <div class="flex-1 h-2 bg-gray-800 rounded-full cursor-pointer relative" id="progress-bar-mobile">
                    <div id="progress-fill-mobile" class="h-full bg-white rounded-full transition-all shadow-lg" style="width: 0%"></div>
                </div>
                <span id="duration-mobile" class="text-xs font-medium w-10 flex-shrink-0" style="color: #cbd5e1;">0:30</span>
            </div>
        </div>
    </div>

    <!-- Hidden audio element -->
    <audio id="preview-audio" preload="metadata">
        <source src="<?php echo esc_url($preview_url); ?>" type="audio/mp4">
    </audio>
</div>

<script>
(function() {
    const audio = document.getElementById('preview-audio');
    const playBtns = document.querySelectorAll('#preview-play-btn');

    // Desktop elements
    const progressBar = document.getElementById('progress-bar');
    const progressFill = document.getElementById('progress-fill');
    const currentTimeEl = document.getElementById('current-time');
    const durationEl = document.getElementById('duration');

    // Mobile elements
    const progressBarMobile = document.getElementById('progress-bar-mobile');
    const progressFillMobile = document.getElementById('progress-fill-mobile');
    const currentTimeElMobile = document.getElementById('current-time-mobile');
    const durationElMobile = document.getElementById('duration-mobile');

    const volumeSlider = document.getElementById('volume-slider');

    if (!audio) return;

    let isPlaying = false;

    // Set initial volume
    audio.volume = 0.7;

    // Play/Pause toggle for all buttons
    playBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            if (isPlaying) {
                audio.pause();
            } else {
                audio.play();
            }
        });
    });

    audio.addEventListener('play', () => {
        isPlaying = true;
        const pauseIcon = '<svg class="w-5 h-5 sm:w-6 sm:h-6 text-black" fill="currentColor" viewBox="0 0 24 24"><path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/></svg>';
        playBtns.forEach(btn => btn.innerHTML = pauseIcon);
    });

    audio.addEventListener('pause', () => {
        isPlaying = false;
        const playIcon = '<svg class="w-5 h-5 sm:w-6 sm:h-6 text-black" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>';
        playBtns.forEach(btn => btn.innerHTML = playIcon);
    });

    // Update progress
    audio.addEventListener('timeupdate', () => {
        const progress = (audio.currentTime / audio.duration) * 100;

        if (progressFill) progressFill.style.width = progress + '%';
        if (progressFillMobile) progressFillMobile.style.width = progress + '%';

        const timeStr = formatTime(audio.currentTime);
        if (currentTimeEl) currentTimeEl.textContent = timeStr;
        if (currentTimeElMobile) currentTimeElMobile.textContent = timeStr;
    });

    audio.addEventListener('loadedmetadata', () => {
        const durationStr = formatTime(audio.duration);
        if (durationEl) durationEl.textContent = durationStr;
        if (durationElMobile) durationElMobile.textContent = durationStr;
    });

    // Seek functionality for both progress bars
    function handleSeek(bar, e) {
        const rect = bar.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const percentage = x / rect.width;
        audio.currentTime = percentage * audio.duration;
    }

    if (progressBar) {
        progressBar.addEventListener('click', (e) => handleSeek(progressBar, e));
    }

    if (progressBarMobile) {
        progressBarMobile.addEventListener('click', (e) => handleSeek(progressBarMobile, e));
    }

    // Volume control
    if (volumeSlider) {
        volumeSlider.addEventListener('input', (e) => {
            audio.volume = e.target.value / 100;
        });
    }

    // Format time helper
    function formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return mins + ':' + (secs < 10 ? '0' : '') + secs;
    }
})();
</script>

<?php else : ?>
<div id="sticky-player" class="sticky-player fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg transform translate-y-full transition-transform duration-300 z-50" role="complementary" aria-label="<?php esc_attr_e('Music Player', 'arcuras'); ?>">
    <div class="player-toggle absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-full">
        <button id="toggle-player"
                class="flex items-center justify-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-t-lg shadow-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-400"
                aria-expanded="false"
                aria-controls="sticky-player-content"
                aria-label="<?php esc_attr_e('Toggle music player', 'arcuras'); ?>">
            <?php gufte_icon('music', 'mr-1 w-4 h-4'); ?>
            <span class="text-sm font-medium">Player</span>
            <span id="player-icon"><?php gufte_icon('chevron-up', 'ml-1 w-4 h-4'); ?></span>
        </button>
    </div>
    
    <div id="sticky-player-content" class="sticky-player-inner max-w-6xl mx-auto px-4 py-4">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="player-info flex items-center w-full md:w-1/3">
                <div class="player-thumbnail w-14 h-14 rounded-md overflow-hidden bg-gray-100 flex-shrink-0 mr-3">
                    <?php if (has_post_thumbnail()) : ?>
                        <?php the_post_thumbnail('thumbnail', array(
                            'class' => 'w-full h-full object-cover',
                            'alt' => get_the_title() . ' - Album Art'
                        )); ?>
                    <?php else : ?>
                        <div class="w-full h-full flex items-center justify-center bg-blue-500 text-white" role="img" aria-label="<?php esc_attr_e('Default music icon', 'arcuras'); ?>">
                            <?php gufte_icon('music', 'text-2xl w-8 h-8'); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="player-meta overflow-hidden">
                    <h3 class="text-gray-800 font-medium text-sm md:text-base truncate"><?php the_title(); ?></h3>
                    <?php
                    // Get artist info from singers taxonomy
                    $singers = get_the_terms(get_the_ID(), 'singer');
                    if ($singers && !is_wp_error($singers)) {
                        $singer = reset($singers);
                        echo '<p class="text-gray-500 text-xs truncate">' . esc_html($singer->name) . '</p>';
                    }
                    ?>
                </div>
            </div>
            
            <div class="player-tabs w-full md:w-2/3 flex justify-center md:justify-end flex-wrap gap-3" role="tablist" aria-label="<?php esc_attr_e('Music platforms', 'arcuras'); ?>">
                <?php if ($apple_music_embed) : ?>
                <button class="platform-tab active flex items-center px-3 py-1 bg-gray-200 text-gray-800 rounded-md transition-colors duration-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                        data-player="apple"
                        role="tab"
                        aria-selected="true"
                        aria-controls="apple-player"
                        id="apple-tab">
                    <?php gufte_icon('apple', 'mr-1 w-4 h-4'); ?>
                    <span class="font-medium">Apple Music</span>
                </button>
                <?php endif; ?>

                <?php if ($spotify_embed) : ?>
                <button class="platform-tab flex items-center px-3 py-1 bg-gray-200 text-gray-800 rounded-md transition-colors duration-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                        data-player="spotify"
                        role="tab"
                        aria-selected="false"
                        aria-controls="spotify-player"
                        id="spotify-tab">
                    <?php gufte_icon('spotify', 'mr-1 w-4 h-4'); ?>
                    <span class="font-medium">Spotify</span>
                </button>
                <?php endif; ?>

                <?php if ($youtube_embed) : ?>
                <button class="platform-tab flex items-center px-3 py-1 bg-gray-200 text-gray-800 rounded-md transition-colors duration-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                        data-player="youtube"
                        role="tab"
                        aria-selected="false"
                        aria-controls="youtube-player"
                        id="youtube-tab">
                    <?php gufte_icon('youtube', 'mr-1 w-4 h-4'); ?>
                    <span class="font-medium">YouTube</span>
                </button>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="embed-container mt-4" role="tabpanel">
            <?php if ($apple_music_embed) : ?>
            <div class="player-embed apple-player active" 
                 id="apple-player" 
                 role="tabpanel" 
                 aria-labelledby="apple-tab"
                 aria-label="<?php esc_attr_e('Apple Music Player', 'arcuras'); ?>">
                <?php echo wp_kses($apple_music_embed, array(
                    'iframe' => array(
                        'src' => array(),
                        'width' => array(),
                        'height' => array(),
                        'frameborder' => array(),
                        'allowfullscreen' => array(),
                        'allow' => array(),
                        'loading' => array(),
                        'title' => array()
                    )
                )); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($spotify_embed) : ?>
            <div class="player-embed spotify-player" 
                 id="spotify-player" 
                 role="tabpanel" 
                 aria-labelledby="spotify-tab"
                 aria-label="<?php esc_attr_e('Spotify Player', 'arcuras'); ?>"
                 aria-hidden="true">
                <?php echo wp_kses($spotify_embed, array(
                    'iframe' => array(
                        'src' => array(),
                        'width' => array(),
                        'height' => array(),
                        'frameborder' => array(),
                        'allowfullscreen' => array(),
                        'allow' => array(),
                        'loading' => array(),
                        'title' => array()
                    )
                )); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($youtube_embed) : ?>
            <div class="player-embed youtube-player" 
                 id="youtube-player" 
                 role="tabpanel" 
                 aria-labelledby="youtube-tab"
                 aria-label="<?php esc_attr_e('YouTube Player', 'arcuras'); ?>"
                 aria-hidden="true">
                <?php echo wp_kses($youtube_embed, array(
                    'iframe' => array(
                        'src' => array(),
                        'width' => array(),
                        'height' => array(),
                        'frameborder' => array(),
                        'allowfullscreen' => array(),
                        'allow' => array(),
                        'loading' => array(),
                        'title' => array()
                    )
                )); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
    endif; // end of preview_url check
    endif; // end of embed check
endif; // end of is_single() check
?>

<?php wp_footer(); ?>

<!-- Custom Scripts -->
<script>
(function() {
    'use strict';
    
    // Mobile Menu Toggle
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.querySelector('.menu-toggle');
        const primaryMenuWrapper = document.querySelector('[id="primary-menu-wrapper"]');
        
        if (menuToggle && primaryMenuWrapper) {
            menuToggle.addEventListener('click', function() {
                primaryMenuWrapper.classList.toggle('hidden');
                const expanded = menuToggle.getAttribute('aria-expanded') === 'true' || false;
                menuToggle.setAttribute('aria-expanded', !expanded);
            });
        }
        
        // Sticky Player
        const stickyPlayer = document.querySelector('[id="sticky-player"]');
        const togglePlayer = document.querySelector('[id="toggle-player"]');
        const playerIcon = document.querySelector('[id="player-icon"]');
        const platformTabs = document.querySelectorAll('.platform-tab');
        const playerEmbeds = document.querySelectorAll('.player-embed');
        
        if (togglePlayer && stickyPlayer) {
            // Show/hide player
            togglePlayer.addEventListener('click', function() {
                const isVisible = !stickyPlayer.classList.contains('translate-y-full');

                if (isVisible) {
                    // Hide player
                    stickyPlayer.classList.add('translate-y-full');
                    playerIcon.innerHTML = '<?php gufte_icon('chevron-up', 'ml-1 w-4 h-4'); ?>';
                    togglePlayer.setAttribute('aria-expanded', 'false');
                } else {
                    // Show player
                    stickyPlayer.classList.remove('translate-y-full');
                    playerIcon.innerHTML = '<?php gufte_icon('chevron-down', 'ml-1 w-4 h-4'); ?>';
                    togglePlayer.setAttribute('aria-expanded', 'true');
                }
            });
        }
        
        // Manage platform tabs
        if (platformTabs.length > 0) {
            platformTabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    // Deactivate all tabs
                    platformTabs.forEach(function(t) {
                        t.classList.remove('active', 'bg-blue-500', 'text-white');
                        t.classList.add('bg-gray-200', 'text-gray-800');
                        t.setAttribute('aria-selected', 'false');
                    });
                    
                    // Activate clicked tab
                    this.classList.add('active', 'bg-blue-500', 'text-white');
                    this.classList.remove('bg-gray-200', 'text-gray-800');
                    this.setAttribute('aria-selected', 'true');
                    
                    // Show relevant player
                    const playerId = this.getAttribute('data-player');
                    
                    // Hide all players
                    playerEmbeds.forEach(function(embed) {
                        embed.classList.remove('active');
                        embed.setAttribute('aria-hidden', 'true');
                    });
                    
                    // Show relevant player
                    const targetPlayer = document.querySelector('.' + playerId + '-player');
                    if (targetPlayer) {
                        targetPlayer.classList.add('active');
                        targetPlayer.setAttribute('aria-hidden', 'false');
                    }
                });
            });

            // Activate first platform tab on load
            platformTabs[0].classList.add('active', 'bg-blue-500', 'text-white');
            platformTabs[0].classList.remove('bg-gray-200', 'text-gray-800');
            platformTabs[0].setAttribute('aria-selected', 'true');
        }
        
        // Show first player on load
        if (playerEmbeds.length > 0) {
            playerEmbeds[0].classList.add('active');
            playerEmbeds[0].setAttribute('aria-hidden', 'false');
            
            // Hide other players
            for (let i = 1; i < playerEmbeds.length; i++) {
                playerEmbeds[i].setAttribute('aria-hidden', 'true');
            }
        }
    });
})();
</script>

<!-- Footer Menu Styles -->
<style>
.footer-menu {
    display: block !important;
}

.footer-menu li {
    display: block !important;
    width: 100% !important;
    margin-bottom: 10px !important;
}

.footer-menu li a {
    display: block !important;
    padding: 5px 0 !important;
    transition: color 0.3s ease;
}

.footer-menu li a:hover {
    color: #2563eb !important; /* blue-600 */
}

/* Player Embed Styles */
.player-embed {
    display: none;
}

.player-embed.active {
    display: block;
}

.player-embed iframe {
    width: 100%;
    height: 152px;
    border: none;
    border-radius: 8px;
}

/* Tab Focus Styles */
.platform-tab:focus {
    outline: 2px solid #60a5fa;
    outline-offset: 2px;
}

/* Screen reader only content */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}
</style>

</body>
</html>