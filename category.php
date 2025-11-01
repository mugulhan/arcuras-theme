<?php
/**
 * The template for displaying category archive pages
 * Sidebar entegrasyonu ile güncellendi.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#category
 *
 * @package Gufte
 */

get_header();
$category = get_queried_object(); // Görüntülenen kategori (term) bilgisini al

// Gerekli fonksiyonların varlığını kontrol et (varsa)
if (!function_exists('gufte_get_lyrics_languages')) {
    // Bu fonksiyonu functions.php'ye taşımak daha iyi olabilir
    function gufte_get_lyrics_languages($content) {
         $languages = array(); $original_language = '';
         preg_match_all('/<figure class="wp-block-table">.*?<table.*?>(.*?)<\/table>.*?<\/figure>/s', $content, $table_matches);
         if (!empty($table_matches[1])) {
             foreach ($table_matches[1] as $table_content) {
                 preg_match('/<thead>(.*?)<\/thead>/s', $table_content, $header_matches);
                 if (!empty($header_matches)) {
                     preg_match_all('/<th>(.*?)<\/th>/s', $header_matches[1], $column_matches);
                     if (!empty($column_matches[1])) {
                         if (isset($column_matches[1][0]) && !empty($column_matches[1][0])) $original_language = strip_tags($column_matches[1][0]);
                         for ($i = 1; $i < count($column_matches[1]); $i++) { $lang = strip_tags($column_matches[1][$i]); if (!empty($lang) && !in_array($lang, $languages)) $languages[] = $lang; }
                     }
                 }
             }
         }
         return array('original' => $original_language, 'translations' => $languages);
     }
}

?>

<?php // Ana İçerik Sarmalayıcısı (Sidebar + Main) ?>
<div class="site-content-wrapper flex flex-col md:flex-row">

    <?php
    // Arcuras Sidebar'ı çağır
    get_template_part('template-parts/arcuras-sidebar');
    ?>

    <?php // Ana İçerik Alanı (Sağ Sütun) ?>
    <main id="primary" class="site-main flex-1 pt-8 pb-12 px-4 sm:px-6 lg:px-8 overflow-x-hidden">

        <?php // Kategori Bilgi Alanı (Header) ?>
        <header class="page-header mb-8 bg-white p-4 md:p-6 rounded-lg shadow-md border border-gray-200">
            <h1 class="page-title text-3xl font-bold text-gray-800 mb-3 flex items-center">
                <span class="iconify mr-3 text-3xl text-primary-600" data-icon="mdi:folder-music-outline"></span> <?php // İkon güncellendi ?>
                <?php echo single_cat_title('', false); // Kategori başlığını gösterir ?>
            </h1>

            <?php // Kategori Açıklaması ?>
            <?php if (category_description()) : ?>
                <div class="taxonomy-description text-gray-600 prose prose-sm max-w-none mb-4"> <?php // Stil ayarlandı ?>
                    <?php echo category_description(); ?>
                </div>
            <?php endif; ?>

            <?php // Kategori Meta Bilgileri ?>
            <div class="category-meta flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-gray-600 border-t border-gray-100 pt-3">
                 <?php // Yazı Sayısı ?>
                <div class="post-count flex items-center">
                    <span class="iconify mr-1.5 text-base text-gray-400" data-icon="mdi:file-document-multiple-outline"></span> <?php // İkon güncellendi ?>
                    <?php printf( esc_html(_n('%s Song', '%s Songs', $category->count, 'gufte')), number_format_i18n($category->count) ); ?>
                </div>

                <?php // İsteğe bağlı: Bu kategorideki popüler şarkıcılar (eğer singer taxonomy'si varsa) ?>
                <?php
                if (taxonomy_exists('singer')) {
                    // Bu kategorideki yazıları alıp şarkıcılarını saymak daha doğru olur,
                    // ama basitlik için tüm sitedeki popülerleri gösterelim (taxonomy-singer.php'deki gibi)
                     $category_singers_query_args = array(
                         'post_type' => 'post',
                         'posts_per_page' => 50, // Performans için makul bir sınır
                         'fields' => 'ids',       // Sadece ID'leri al
                         'tax_query' => array(
                             array(
                                 'taxonomy' => 'category',
                                 'field' => 'term_id',
                                 'terms' => $category->term_id,
                             ),
                         ),
                         'no_found_rows' => true,
                     );
                     $category_posts_ids = get_posts($category_singers_query_args);
                     $singers_in_category = array();
                     if (!empty($category_posts_ids)) {
                         $singers_in_category = wp_get_object_terms($category_posts_ids, 'singer', array('orderby' => 'count', 'order' => 'DESC', 'number' => 5)); // Bu kategorideki şarkıcıları saydır
                     }

                    if (!empty($singers_in_category) && !is_wp_error($singers_in_category)) : ?>
                        <div class="category-singers flex items-center gap-1">
                            <span class="iconify mr-1.5 text-base text-gray-400" data-icon="mdi:microphone-variant"></span>
                            <span class="font-medium mr-1"><?php esc_html_e('Top Singers:', 'gufte'); ?></span>
                            <div class="flex flex-wrap gap-1.5">
                                <?php foreach ($singers_in_category as $singer) : ?>
                                <a href="<?php echo esc_url(get_term_link($singer)); ?>" class="px-2 py-0.5 bg-primary-50 text-primary-700 hover:bg-primary-100 rounded-full text-xs transition-colors duration-200">
                                    <?php echo esc_html($singer->name); ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif;
                }?>
                 <?php // Alt kategoriler (varsa)
                    $child_categories = get_categories( array( 'parent' => $category->term_id, 'hide_empty' => true ) );
                    if ( $child_categories ) : ?>
                    <div class="child-categories flex items-center gap-1">
                         <span class="iconify mr-1.5 text-base text-gray-400" data-icon="mdi:folder-multiple-outline"></span>
                         <span class="font-medium mr-1"><?php esc_html_e( 'Subcategories:', 'gufte' ); ?></span>
                         <div class="flex flex-wrap gap-1.5">
                            <?php foreach ( $child_categories as $child_cat ) : ?>
                                <a href="<?php echo esc_url( get_category_link( $child_cat->term_id ) ); ?>" class="px-2 py-0.5 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-full text-xs transition-colors duration-200">
                                    <?php echo esc_html( $child_cat->name ); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
            </div>
        </header><?php // Yazı Listesi ?>
        <div class="lyrics-listing mt-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (have_posts()) : ?>
                    <?php while (have_posts()) : the_post(); ?>
                        <?php // Yazı Kartı (index.php veya taxonomy-singer.php'deki gibi) ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class('bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-primary-300 flex flex-col'); ?>>
                             <?php if (has_post_thumbnail()) : ?>
                                <a href="<?php the_permalink(); ?>" class="block relative group h-40 overflow-hidden">
                                    <?php the_post_thumbnail('medium', array('class' => 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300')); ?>
                                     <?php // İsteğe bağlı: Çeviri sayısı badge'i ?>
                                     <?php
                                        /*
                                        $lyrics_languages = function_exists('gufte_get_lyrics_languages') ? gufte_get_lyrics_languages(get_the_content()) : array('translations' => array());
                                        $translation_count = !empty($lyrics_languages['translations']) ? count($lyrics_languages['translations']) : 0;
                                        if ($translation_count > 0) : ?>
                                        <div class="absolute top-2 right-2 bg-primary-500/80 backdrop-blur-sm text-white text-xs font-bold px-2 py-1 rounded-full flex items-center">
                                            <span class="iconify mr-1" data-icon="mdi:translate"></span><?php echo esc_html($translation_count); ?>
                                        </div>
                                        <?php endif; */
                                    ?>
                                </a>
                             <?php else: ?>
                                 <a href="<?php the_permalink(); ?>" class="block relative group h-40 bg-gray-100 flex items-center justify-center">
                                     <span class="iconify text-4xl text-gray-300 group-hover:text-primary-400 transition-colors duration-300" data-icon="mdi:music-note"></span>
                                      <?php // İsteğe bağlı: Çeviri sayısı badge'i ?>
                                 </a>
                             <?php endif; ?>
                             <div class="p-4 flex-grow flex flex-col">
                                 <h2 class="text-base font-bold mb-2">
                                     <a href="<?php the_permalink(); ?>" class="text-gray-800 hover:text-primary-600 transition-colors duration-300 line-clamp-2">
                                         <?php the_title(); ?>
                                     </a>
                                 </h2>
                                  <?php // Şarkıcı bilgisi ?>
                                  <?php
                                    if (taxonomy_exists('singer')) {
                                        $singers = get_the_terms(get_the_ID(), 'singer');
                                        if ($singers && !is_wp_error($singers)):
                                            $singer = reset($singers);
                                  ?>
                                  <div class="text-xs text-gray-500 mb-2 flex items-center">
                                       <span class="iconify mr-1" data-icon="mdi:microphone-variant"></span>
                                       <a href="<?php echo esc_url(get_term_link($singer)); ?>" class="hover:text-primary-500">
                                           <?php echo esc_html($singer->name); ?>
                                       </a>
                                  </div>
                                  <?php endif; } ?>

                                 <div class="mt-auto pt-3 border-t border-gray-100 flex justify-between items-center text-xs text-gray-500">
                                     <span class="publish-date flex items-center">
                                         <span class="iconify mr-1" data-icon="mdi:calendar-blank"></span>
                                         <time datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>"><?php echo esc_html(get_the_date()); ?></time>
                                     </span>
                                     <a href="<?php the_permalink(); ?>" class="read-more-button bg-primary-50 hover:bg-primary-100 text-primary-600 px-2.5 py-1 rounded-full inline-flex items-center transition-all duration-300 font-medium">
                                        <?php esc_html_e('View', 'gufte'); ?>
                                        <span class="iconify ml-1" data-icon="mdi:arrow-right"></span>
                                     </a>
                                 </div>
                             </div>
                         </article>
                    <?php endwhile; ?>

                     <?php // Sayfalama ?>
                     <div class="pagination col-span-full mt-8">
                        <?php
                           the_posts_pagination( array(
                                'mid_size' => 2,
                                'prev_text' => '<span class="iconify mr-1" data-icon="mdi:chevron-left"></span><span class="sr-only">' . __( 'Previous', 'gufte' ) . '</span>',
                                'next_text' => '<span class="sr-only">' . __( 'Next', 'gufte' ) . '</span><span class="iconify ml-1" data-icon="mdi:chevron-right"></span>',
                                'screen_reader_text' => __( 'Posts navigation', 'gufte' ),
                                // Tailwind ile uyumlu hale getirmek için özel walker gerekebilir veya temel stiller yeterli olabilir.
                           ) );
                        ?>
                     </div>

                <?php else : ?>
                    <?php // Bu kategoride hiç yazı bulunamazsa ?>
                     <div class="col-span-full">
                        <p class="text-center text-lg text-gray-500 py-12">
                            <?php esc_html_e( 'No songs found in this category.', 'gufte' ); ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div></div></main></div><?php
get_footer();