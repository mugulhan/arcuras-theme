<?php
/**
 * The template for displaying singer taxonomy archive pages
 * Sidebar entegrasyonu ile güncellendi.
 * Awards entegrasyonu eklendi.
 * Schema işlemleri singer-taxonomy-schema.php dosyasına taşındı.
 *
 * @package Gufte
 */

get_header();
$term = get_queried_object(); // Görüntülenen şarkıcı (term) bilgisini al
?>

<?php // Ana İçerik Sarmalayıcısı (Sidebar + Main) ?>
<div class="site-content-wrapper flex flex-col md:flex-row">

    <?php
    // Arcuras Sidebar'ı çağır
    get_template_part('template-parts/arcuras-sidebar');
    ?>

    <?php // Ana İçerik Alanı (Sağ Sütun) ?>
    <main id="primary" class="site-main flex-1 pt-8 pb-12 px-4 sm:px-6 lg:px-8 overflow-x-hidden">

        <?php // Şarkıcı Bilgi Alanı (Header) ?>
        <header class="page-header mb-8 bg-white p-4 md:p-6 rounded-lg shadow-md border border-gray-200">
            <div class="flex flex-col md:flex-row gap-6">
                <?php
                // Şarkıcı görseli varsa göster
                $singer_image = '';
                if (function_exists('gufte_get_singer_image')) {
                    $singer_image = gufte_get_singer_image($term->term_id, 'medium');
                }
                if (!empty($singer_image)) : ?>
                <div class="singer-image-container md:w-1/4 lg:w-1/5 flex-shrink-0">
                    <div class="rounded-lg overflow-hidden shadow-sm border border-gray-200 aspect-square">
                        <?php echo str_replace('<img ', '<img class="singer-image w-full h-full object-cover" ', $singer_image); ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="singer-info flex-grow">
                    <h1 class="page-title text-3xl font-bold text-gray-800 mb-4 flex items-center">
                        <?php gufte_icon('microphone-variant', 'mr-3 text-3xl text-primary-600'); ?>
                        <?php echo esc_html($term->name); ?>
                    </h1>

                    <?php // Şarkıcı Meta Bilgileri ?>
                    <div class="singer-meta space-y-2 mb-4 text-sm text-gray-600">
                        <?php
                        // Gerçek adı
                        $real_name = get_term_meta($term->term_id, 'real_name', true);
                        if (!empty($real_name)) : ?>
                        <div class="singer-realname flex items-center">
                            <?php gufte_icon('account-outline', 'mr-2 text-base text-gray-400 w-5 text-center'); ?>
                            <span><?php echo esc_html($real_name); ?></span>
                        </div>
                        <?php endif; ?>

                        <?php
                        // Yaşam süresi
                        $lifespan = '';
                         if (function_exists('gufte_get_singer_lifespan')) {
                             $lifespan = gufte_get_singer_lifespan($term->term_id);
                         }
                        if (!empty($lifespan)) : ?>
                        <div class="singer-lifespan flex items-center">
                            <?php gufte_icon('calendar-range', 'mr-2 text-base text-gray-400 w-5 text-center'); ?>
                            <span><?php echo $lifespan; ?></span>
                        </div>
                        <?php endif; ?>

                        <?php
                        // Doğum yeri
                        $birth_place = get_term_meta($term->term_id, 'birth_place', true);
                        $birth_country = get_term_meta($term->term_id, 'birth_country', true);
                        $birthplace = '';
                        if (!empty($birth_place)) { $birthplace = $birth_place; }
                        if (!empty($birth_country)) { $birthplace .= (!empty($birthplace) ? ', ' : '') . $birth_country; }

                        if (!empty($birthplace)) : ?>
                        <div class="singer-birthplace flex items-center">
                            <?php gufte_icon('map-marker-outline', 'mr-2 text-base text-gray-400 w-5 text-center'); ?>
                            <span><?php echo esc_html($birthplace); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php 
                    // Müzik Platformu Profil Linkleri
                    $platform_links = array();
                    if (function_exists('gufte_get_singer_platform_links')) {
                        $platform_links = gufte_get_singer_platform_links($term->term_id);
                    }

                    // En az bir platform linki varsa göster
                    $has_platform_links = false;
                    foreach ($platform_links as $platform => $url) {
                        if (!empty($url)) {
                            $has_platform_links = true;
                            break;
                        }
                    }

                    if ($has_platform_links) : ?>
                    <div class="singer-platform-links mt-4 pt-4 border-t border-gray-100">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                            <?php gufte_icon('music-circle-outline', 'mr-2 text-primary-600'); ?>
                            <?php esc_html_e('Listen on Music Platforms', 'gufte'); ?>
                        </h4>
                        <div class="flex flex-wrap gap-2">
                            <?php 
                            // Spotify
                            if (!empty($platform_links['spotify'])) : ?>
                                <a href="<?php echo esc_url($platform_links['spotify']); ?>"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-flex items-center px-3 py-1.5 bg-[#1DB954] hover:bg-[#1aa34a] text-white text-xs font-medium rounded-full transition-all duration-300 hover:scale-105">
                                    <?php gufte_icon('spotify', 'mr-1.5'); ?>
                                    Spotify
                                </a>
                            <?php endif; ?>
                            
                            <?php 
                            // YouTube Music
                            if (!empty($platform_links['youtube_music'])) : ?>
                                <a href="<?php echo esc_url($platform_links['youtube_music']); ?>"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-flex items-center px-3 py-1.5 bg-[#FF0000] hover:bg-[#CC0000] text-white text-xs font-medium rounded-full transition-all duration-300 hover:scale-105">
                                    <?php gufte_icon('youtube', 'mr-1.5'); ?>
                                    YouTube Music
                                </a>
                            <?php endif; ?>

                            <?php
                            // Apple Music
                            if (!empty($platform_links['apple_music'])) : ?>
                                <a href="<?php echo esc_url($platform_links['apple_music']); ?>"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-[#FA243C] to-[#FC3850] hover:from-[#E91E3A] hover:to-[#EA2845] text-white text-xs font-medium rounded-full transition-all duration-300 hover:scale-105">
                                    <?php gufte_icon('apple', 'mr-1.5'); ?>
                                    Apple Music
                                </a>
                            <?php endif; ?>

                            <?php
                            // Deezer
                            if (!empty($platform_links['deezer'])) : ?>
                                <a href="<?php echo esc_url($platform_links['deezer']); ?>"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-[#FF6D00] to-[#FF8800] hover:from-[#E55D00] hover:to-[#E56600] text-white text-xs font-medium rounded-full transition-all duration-300 hover:scale-105">
                                    <?php gufte_icon('simple-icons:deezer', 'mr-1.5'); ?>
                                    Deezer
                                </a>
                            <?php endif; ?>

                            <?php
                            // SoundCloud
                            if (!empty($platform_links['soundcloud'])) : ?>
                                <a href="<?php echo esc_url($platform_links['soundcloud']); ?>"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-[#FF8800] to-[#FF6600] hover:from-[#E57700] hover:to-[#E55500] text-white text-xs font-medium rounded-full transition-all duration-300 hover:scale-105">
                                    <?php gufte_icon('soundcloud', 'mr-1.5'); ?>
                                    SoundCloud
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; // has_platform_links ?>
                    
                    <?php
                    // Sosyal medya linklerini göster
                    if (function_exists('gufte_render_singer_social_media')) {
                        echo gufte_render_singer_social_media($term->term_id);
                    }
                    ?>
                    
                     <?php // Şarkı Sayısı
                       if ($term->count > 0) : ?>
                        <div class="singer-song-count mt-4 text-xs text-gray-500 border-t border-gray-100 pt-2">
                             <?php printf(esc_html(_n('%s song found.', '%s songs found.', $term->count, 'gufte')), number_format_i18n($term->count)); ?>
                        </div>
                       <?php endif; ?>
                </div>
            </div>

            <?php // Biyografi Alanı ?>
            <?php if ($term->description) : ?>
            <div class="singer-biography mt-6 border-t border-gray-100 pt-4">
                <h3 class="text-lg font-semibold text-gray-700 mb-2 flex items-center">
                    <?php gufte_icon('text-box-outline', 'mr-2 text-primary-600'); ?>
                    <?php esc_html_e('Biography', 'gufte'); ?>
                </h3>
                <div class="taxonomy-description text-gray-700 prose prose-sm max-w-none bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <?php echo wpautop(wp_kses_post($term->description)); ?>
                </div>
            </div>
            <?php endif; ?>

            <?php
            // Awards Bölümünü Ekle
            if (function_exists('gufte_render_singer_awards_section')) {
                echo gufte_render_singer_awards_section($term->term_id);
            }
            ?>

             <?php
                // Şarkıcının yer aldığı albümleri göster
                 $singer_albums = array();
                 if (function_exists('gufte_get_singer_albums')) {
                     $singer_albums = gufte_get_singer_albums($term->term_id);
                 }

                 if (!empty($singer_albums)) : ?>
                 <div class="singer-albums mt-6 border-t border-gray-100 pt-4">
                     <h3 class="text-lg font-semibold text-gray-700 mb-3 flex items-center">
                          <?php gufte_icon('album', 'mr-2 text-primary-600'); ?>
                          <?php esc_html_e('Albums by this Artist', 'gufte'); ?>
                     </h3>
                      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                         <?php foreach ($singer_albums as $album) :
                             $album_year = get_term_meta($album->term_id, 'album_year', true);
                             // Albüme ait şarkı sayısı (sadece bu şarkıcıya ait olanlar)
                             $album_song_count_query = new WP_Query(array(
                                 'post_type' => 'lyrics',
                                 'posts_per_page' => -1, // Tümünü say
                                 'fields' => 'ids',     // Sadece ID'leri al (daha hızlı)
                                 'tax_query' => array(
                                     'relation' => 'AND',
                                     array(
                                         'taxonomy' => 'album',
                                         'field' => 'term_id',
                                         'terms' => $album->term_id,
                                     ),
                                     array(
                                         'taxonomy' => 'singer',
                                         'field' => 'term_id',
                                         'terms' => $term->term_id, // Sadece bu şarkıcının
                                     ),
                                 ),
                                 'no_found_rows' => true, // Sayfalama yapma
                             ));
                             $album_song_count = $album_song_count_query->post_count;

                             // Albüm kapak görseli (ilk şarkıdan)
                              $first_song_query = new WP_Query(array(
                                 'post_type' => 'lyrics',
                                 'posts_per_page' => 1,
                                 'fields' => 'ids',
                                 'tax_query' => array(
                                      'relation' => 'AND',
                                     array('taxonomy' => 'album','field' => 'term_id','terms' => $album->term_id),
                                     array('taxonomy' => 'singer','field' => 'term_id','terms' => $term->term_id)
                                 ),
                                 'no_found_rows' => true
                             ));
                             $album_image = '';
                             if ($first_song_query->have_posts()) {
                                 $first_song_id = $first_song_query->posts[0];
                                 if (has_post_thumbnail($first_song_id)) {
                                     $album_image = get_the_post_thumbnail_url($first_song_id, 'thumbnail'); // Thumbnail yeterli
                                 }
                             }
                             wp_reset_postdata(); // WP_Query sonrası reset
                         ?>
                         <a href="<?php echo esc_url(get_term_link($album)); ?>" class="album-card flex items-center bg-white rounded-lg overflow-hidden border border-gray-200 transition-all duration-300 hover:shadow-lg hover:border-primary-300 group p-3">
                              <?php if (!empty($album_image)) : ?>
                                 <img src="<?php echo esc_url($album_image); ?>" alt="<?php echo esc_attr($album->name); ?>" class="w-12 h-12 object-cover rounded-md mr-3 flex-shrink-0 shadow-sm">
                             <?php else : ?>
                                 <div class="w-12 h-12 bg-gray-100 rounded-md mr-3 flex items-center justify-center flex-shrink-0">
                                     <?php gufte_icon('album', 'text-2xl text-gray-400'); ?>
                                 </div>
                             <?php endif; ?>
                             <div class="flex-grow truncate">
                                 <h4 class="text-sm font-bold text-gray-800 group-hover:text-primary-600 transition-colors duration-200 truncate">
                                     <?php echo esc_html($album->name); ?>
                                     <?php if (!empty($album_year)) : ?>
                                         <span class="text-gray-500 font-normal">(<?php echo esc_html($album_year); ?>)</span>
                                     <?php endif; ?>
                                 </h4>
                                 <?php if ($album_song_count > 0): ?>
                                 <p class="text-xs text-gray-500">
                                      <?php printf(esc_html(_n('%s song', '%s songs', $album_song_count, 'gufte')), number_format_i18n($album_song_count)); ?>
                                 </p>
                                 <?php endif; ?>
                             </div>
                         </a>
                         <?php endforeach; ?>
                     </div>
                 </div>
                 <?php endif; // !empty($singer_albums) ?>

        </header>
        
        <?php // Şarkı Listesi ?>
        <div class="lyrics-listing mt-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <?php gufte_icon('music-note-outline', 'mr-3 text-2xl text-primary-600'); ?>
                <?php printf(esc_html__('Songs by %s', 'gufte'), esc_html($term->name)); ?>
            </h2>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php if (have_posts()) : ?>
                    <?php while (have_posts()) : the_post(); ?>
                         <article id="post-<?php the_ID(); ?>" <?php post_class('bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-primary-300 flex flex-col'); ?>>
                             <?php if (has_post_thumbnail()) : ?>
                                <a href="<?php the_permalink(); ?>" class="block relative group aspect-square overflow-hidden">
                                    <?php the_post_thumbnail('medium', array('class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300')); ?>
                                </a>
                             <?php else: ?>
                                 <a href="<?php the_permalink(); ?>" class="block relative group aspect-square bg-gray-100 flex items-center justify-center">
                                     <?php gufte_icon('music-note', 'text-4xl text-gray-300 group-hover:text-primary-400 transition-colors duration-300'); ?>
                                 </a>
                             <?php endif; ?>
                             <div class="p-4 flex-grow flex flex-col">
                                 <h3 class="text-base font-bold mb-2">
                                     <a href="<?php the_permalink(); ?>" class="text-gray-800 hover:text-primary-600 transition-colors duration-300 line-clamp-2">
                                         <?php the_title(); ?>
                                     </a>
                                 </h3>
                                  <?php // Albüm bilgisi
                                    $song_albums = get_the_terms(get_the_ID(), 'album');
                                    if ($song_albums && !is_wp_error($song_albums)):
                                        $song_album = reset($song_albums);
                                        $song_album_year = get_term_meta($song_album->term_id, 'album_year', true);
                                  ?>
                                  <div class="text-xs text-gray-500 mb-2 flex items-center">
                                       <?php gufte_icon('album', 'mr-1'); ?>
                                       <a href="<?php echo esc_url(get_term_link($song_album)); ?>" class="hover:text-primary-500">
                                           <?php echo esc_html($song_album->name); ?>
                                           <?php if($song_album_year) echo ' (' . esc_html($song_album_year) . ')'; ?>
                                       </a>
                                  </div>
                                  <?php endif; ?>

                                 <?php
                                 // Kategori ve Çeviri Bilgileri - Gutenberg block'tan al
                                 $categories = get_the_category();
                                 $post_content = get_post_field('post_content', get_the_ID());
                                 $lyrics_languages = array('original' => '', 'translations' => array());

                                 if (function_exists('gufte_get_lyrics_languages')) {
                                     $lyrics_languages = gufte_get_lyrics_languages($post_content);
                                 }

                                 $translation_count = !empty($lyrics_languages['translations']) ? count($lyrics_languages['translations']) : 0;

                                 // Original language taxonomy'sini al
                                 $original_langs = get_the_terms(get_the_ID(), 'original_language');

                                 // Count lines in lyrics from Gutenberg blocks
                                 $line_count = 0;
                                 if (!empty($post_content)) {
                                     if (has_blocks($post_content)) {
                                         $blocks = parse_blocks($post_content);
                                         foreach ($blocks as $block) {
                                             if ($block['blockName'] === 'arcuras/lyrics-translations' && !empty($block['attrs']['languages'])) {
                                                 foreach ($block['attrs']['languages'] as $lang) {
                                                     if (isset($lang['isOriginal']) && $lang['isOriginal'] && !empty($lang['lyrics'])) {
                                                         $lines = array_filter(explode("\n", $lang['lyrics']), function($line) {
                                                             return trim($line) !== '';
                                                         });
                                                         $line_count = count($lines);
                                                         break 2;
                                                     }
                                                 }
                                             }
                                         }
                                     } else {
                                         $clean_content = strip_tags(strip_shortcodes($post_content));
                                         $lines = array_filter(explode("\n", $clean_content), function($line) {
                                             return trim($line) !== '';
                                         });
                                         $line_count = count($lines);
                                     }
                                 }
                                 ?>
                                 <?php if (($original_langs && !is_wp_error($original_langs)) || $translation_count > 0) : ?>
                                 <div class="mb-3 pb-3 border-b border-gray-100">
                                     <div class="flex flex-wrap items-center gap-2">
                                         <?php
                                         // Orijinal dil badge'i göster
                                         if ($original_langs && !is_wp_error($original_langs)) :
                                             $original_lang = reset($original_langs);
                                         ?>
                                             <a href="<?php echo esc_url(get_term_link($original_lang)); ?>" class="language-badge original flex items-center bg-white/90 backdrop-blur-sm rounded-full px-2 py-1 border border-accent-300/40 hover:border-accent-500/60 transition-all duration-300 hover:scale-105 hover:shadow-lg group text-xs">
                                                 <?php gufte_icon('file-document', 'mr-1 text-sm text-accent-600 group-hover:text-accent-700 transition-colors duration-300 w-4 h-4'); ?>
                                                 <span class="font-medium text-gray-800 group-hover:text-accent-700 transition-colors duration-300"><?php echo esc_html($original_lang->name); ?></span>
                                             </a>
                                         <?php endif; ?>

                                         <?php
                                         // Çeviri sayısı badge'i (link yok, sadece bilgi)
                                         if ($translation_count > 0) :
                                         ?>
                                             <span class="inline-flex items-center text-[10px] font-medium text-primary-700 bg-primary-50 px-2 py-0.5 rounded-full">
                                                 <?php gufte_icon('translate', 'mr-1 w-3 h-3'); ?>
                                                 <?php printf(esc_html(_n('%s translation', '%s translations', $translation_count, 'gufte')), number_format_i18n($translation_count)); ?>
                                             </span>
                                         <?php endif; ?>
                                     </div>
                                 </div>
                                 <?php endif; ?>

                                 <div class="mt-auto pt-3 border-t border-gray-100 flex justify-between items-center text-xs text-gray-500">
                                     <?php if ($line_count > 0) : ?>
                                     <span class="inline-flex items-center">
                                         <?php gufte_icon('counter', 'mr-1.5 w-3.5 h-3.5'); ?>
                                         <?php echo esc_html($line_count); ?> <?php esc_html_e('lines', 'gufte'); ?>
                                     </span>
                                     <?php else : ?>
                                     <span></span>
                                     <?php endif; ?>
                                      <a href="<?php the_permalink(); ?>" class="read-more-button bg-primary-50 hover:bg-primary-100 text-primary-600 px-2.5 py-1 rounded-full inline-flex items-center transition-all duration-300 font-medium">
                                        <?php esc_html_e('View', 'gufte'); ?>
                                        <?php gufte_icon('arrow-right', 'ml-1'); ?>
                                      </a>
                                 </div>
                             </div>
                         </article>
                    <?php endwhile; ?>

                     <?php // Sayfalama
                        the_posts_pagination( array(
                             'mid_size' => 2,
                             'prev_text' => gufte_get_icon('chevron-left') . '<span class="sr-only">' . __( 'Previous', 'gufte' ) . '</span>',
                             'next_text' => '<span class="sr-only">' . __( 'Next', 'gufte' ) . '</span>' . gufte_get_icon('chevron-right'),
                             'screen_reader_text' => __( 'Posts navigation', 'gufte' ),
                             'before_page_number' => '<span class="border border-gray-300 px-3 py-1 rounded">',
                              'after_page_number'  => '</span>'
                        ) );
                    ?>

                <?php else : ?>
                    <div class="col-span-full">
                        <p class="text-center text-lg text-gray-500 py-12">
                             <?php esc_html_e('No songs found for this singer.', 'gufte'); ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
    </main>
</div>

<script>
// Translations dropdown mini - click handler
document.addEventListener('DOMContentLoaded', function() {
    const dropdowns = document.querySelectorAll('.translations-dropdown-mini');

    dropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('.translations-trigger-mini');
        const menu = dropdown.querySelector('.translations-menu-mini');

        if (trigger && menu) {
            // Toggle dropdown on click
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Close other dropdowns
                document.querySelectorAll('.translations-dropdown-mini').forEach(other => {
                    if (other !== dropdown) {
                        other.classList.remove('active');
                        const otherMenu = other.querySelector('.translations-menu-mini');
                        if (otherMenu) {
                            otherMenu.style.opacity = '0';
                            otherMenu.style.visibility = 'hidden';
                            otherMenu.style.transform = 'translateY(-10px)';
                        }
                    }
                });

                // Toggle current dropdown
                dropdown.classList.toggle('active');

                if (dropdown.classList.contains('active')) {
                    menu.style.opacity = '1';
                    menu.style.visibility = 'visible';
                    menu.style.transform = 'translateY(0)';
                } else {
                    menu.style.opacity = '0';
                    menu.style.visibility = 'hidden';
                    menu.style.transform = 'translateY(-10px)';
                }
            });

            // Close on outside click
            document.addEventListener('click', function(e) {
                if (!dropdown.contains(e.target)) {
                    dropdown.classList.remove('active');
                    menu.style.opacity = '0';
                    menu.style.visibility = 'hidden';
                    menu.style.transform = 'translateY(-10px)';
                }
            });
        }
    });
});
</script>

<style>
/* Translations dropdown mini styles */
.lyrics-card,
article {
    position: relative;
    overflow: visible !important;
}

.translations-dropdown-mini {
    position: relative;
    z-index: 10;
}

.translations-dropdown-mini.active {
    z-index: 1001;
}

.translations-menu-mini {
    position: absolute;
    top: 100%;
    left: 0;
    margin-top: 0.5rem;
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e7eb;
    padding: 0.5rem 0;
    min-width: 12rem;
    z-index: 9999 !important;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    pointer-events: none;
}

.translations-dropdown-mini.active .translations-menu-mini {
    opacity: 1 !important;
    visibility: visible !important;
    transform: translateY(0) !important;
    pointer-events: auto;
}

.dropdown-arrow {
    transition: transform 0.3s ease;
}

.translations-dropdown-mini.active .dropdown-arrow {
    transform: rotate(180deg);
}

/* Ensure parent container doesn't clip dropdown */
.lyrics-listing .grid {
    overflow: visible !important;
}
</style>

<?php
get_footer();
?>