<?php
/**
 * Template Name: Albums Page
 * Description: Albüm taksonomisindeki tüm albümleri modern bir grid arayüzde arama, sıralama ve sayfalama ile listeler.
 *
 * @package Gufte
 */

get_header();

// ---- URL Parametreleri ----
$paged      = max(1, get_query_var('paged') ? get_query_var('paged') : (get_query_var('page') ?: 1));
$q          = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
$sort       = isset($_GET['sort']) ? sanitize_text_field(wp_unslash($_GET['sort'])) : 'name_asc';
$per_page   = isset($_GET['per_page']) ? max(6, min(60, intval($_GET['per_page']))) : 20;
$letter     = isset($_GET['letter']) ? strtoupper(sanitize_text_field(wp_unslash($_GET['letter']))) : '';

// ---- get_terms() için temel argümanlar ----
$args = array(
    'taxonomy'   => 'album',
    'hide_empty' => true,
    'number'     => $per_page,
    'offset'     => ($paged - 1) * $per_page,
);

// Arama
if ($q !== '') {
    $args['search'] = $q;
    // Varsayılan olarak 'search' joker karakter araması yapar; performans için '*' eklemeye gerek yok.
}

// Baş harf filtresi (A–Z veya # = A–Z dışı)
if ($letter !== '') {
    // get_terms ilk harfe göre filtrelemediği için tüm sonuçları çekip PHP ile süzeceğiz
    $args['number'] = 0; // hepsini al, sonra slice
}

// Sıralama
// name_asc | name_desc | year_desc | year_asc | count_desc | count_asc
switch ($sort) {
    case 'name_desc':
        $args['orderby'] = 'name';
        $args['order']   = 'DESC';
        break;
    case 'year_desc':
        $args['meta_key'] = 'album_year';
        $args['orderby']  = 'meta_value_num';
        $args['order']    = 'DESC';
        break;
    case 'year_asc':
        $args['meta_key'] = 'album_year';
        $args['orderby']  = 'meta_value_num';
        $args['order']    = 'ASC';
        break;
    case 'count_desc':
        $args['orderby'] = 'count';
        $args['order']   = 'DESC';
        break;
    case 'count_asc':
        $args['orderby'] = 'count';
        $args['order']   = 'ASC';
        break;
    case 'name_asc':
    default:
        $args['orderby'] = 'name';
        $args['order']   = 'ASC';
        break;
}

// ---- Toplam sonuç sayısı ve veriler ----
$total_args = $args;
$total_args['fields'] = 'all'; // isimlere erişebilmek için terim objeleri gerekli (baş harf filtresi için)
$total_args['number'] = 0;
$total_args['offset'] = 0;

$all_terms = get_terms($total_args);
if (is_wp_error($all_terms)) {
    $all_terms = array();
}

// Baş harf filtresini uygula (gerekliyse)
if ($letter !== '') {
    $all_terms = array_filter($all_terms, function($t) use ($letter) {
        $name = remove_accents($t->name);
        $first = strtoupper(mb_substr(trim($name), 0, 1, 'UTF-8'));
        if ($letter === '#') {
            // Harf değilse (#)
            return !preg_match('/[A-ZÇĞİÖŞÜ]/u', $first);
        }
        return $first === $letter;
    });
}

// Arama + harf filtresi sonrası toplam
$total_albums = count($all_terms);

// Sayfalama için dilimle (letter seçiliyken veya aramada zaten all_terms hazır)
if ($letter !== '') {
    // Sort tekrar uygulansın (çünkü get_terms sıralaması letter sonrası bozulabilir)
    usort($all_terms, function($a, $b) use ($sort) {
        switch ($sort) {
            case 'name_desc':
                return strcasecmp($b->name, $a->name);
            case 'year_desc':
                $year_a = get_term_meta($a->term_id, 'album_year', true);
                $year_b = get_term_meta($b->term_id, 'album_year', true);
                return ($year_b <=> $year_a);
            case 'year_asc':
                $year_a = get_term_meta($a->term_id, 'album_year', true);
                $year_b = get_term_meta($b->term_id, 'album_year', true);
                return ($year_a <=> $year_b);
            case 'count_desc':
                return ($b->count <=> $a->count);
            case 'count_asc':
                return ($a->count <=> $b->count);
            case 'name_asc':
            default:
                return strcasecmp($a->name, $b->name);
        }
    });

    $offset = ($paged - 1) * $per_page;
    $albums = array_slice($all_terms, $offset, $per_page);
} else {
    // Letter yoksa, daha önce belirlenen $args ile sayfalı veriyi çekebiliriz
    $page_args = $args;
    $page_args['fields'] = 'all';
    $albums = get_terms($page_args);
    if (is_wp_error($albums)) {
        $albums = array();
    }

    // Toplamı doğru almak için, letter yoksa da total hesapla:
    if (empty($q)) {
        // Arama yoksa basit count
        $total_albums = wp_count_terms('album', array('hide_empty' => true));
    } else {
        // Arama varsa all_terms kullan
        $total_albums = count($all_terms);
    }
}
?>

<?php // Ana İçerik Sarmalayıcısı (Sidebar + Main) ?>
<div class="site-content-wrapper flex flex-col md:flex-row">

    <?php get_template_part('template-parts/arcuras-sidebar'); ?>

    <?php // Ana İçerik Alanı (Sağ Sütun) ?>
    <main id="primary" class="site-main flex-1 bg-white overflow-x-hidden">

        <?php
        // Breadcrumb
        set_query_var('breadcrumb_items', array(
            array('label' => 'Home', 'url' => home_url('/')),
            array('label' => __('Albums', 'gufte'))
        ));
        get_template_part('template-parts/page-components/page-breadcrumb');

        // Hero
        if (have_posts()) : while (have_posts()) : the_post();
            $hero_description = get_the_content() ? get_the_content() : '';
        endwhile; endif;

        set_query_var('hero_title', get_the_title());
        set_query_var('hero_icon', 'album');
        set_query_var('hero_description', $hero_description);
        get_template_part('template-parts/page-components/page-hero');
        ?>

        <?php
        // Filters
        set_query_var('filter_config', array(
            'search' => array(
                'enabled' => true,
                'name' => 'q',
                'value' => $q,
                'label' => __('Search albums', 'gufte'),
                'placeholder' => __('Type album name…', 'gufte'),
                'id' => 'albums-search'
            ),
            'sort' => array(
                'enabled' => true,
                'name' => 'sort',
                'value' => $sort,
                'label' => __('Sort by', 'gufte'),
                'id' => 'albums-sort',
                'options' => array(
                    'name_asc' => __('Name (A→Z)', 'gufte'),
                    'name_desc' => __('Name (Z→A)', 'gufte'),
                    'year_desc' => __('Year (newest)', 'gufte'),
                    'year_asc' => __('Year (oldest)', 'gufte'),
                    'count_desc' => __('Tracks (most)', 'gufte'),
                    'count_asc' => __('Tracks (fewest)', 'gufte'),
                )
            ),
            'per_page' => array(
                'enabled' => true,
                'name' => 'per_page',
                'value' => $per_page,
                'label' => __('Per page', 'gufte'),
                'id' => 'albums-per-page',
                'options' => array(12, 20, 30, 40, 60)
            ),
            'results_text' => sprintf(_n('%s result', '%s results', $total_albums, 'gufte'), number_format_i18n($total_albums)),
            'search_query' => $q,
            'action_url' => get_permalink()
        ));
        get_template_part('template-parts/page-components/page-filters');

        // Letter Filter
        $letters = array_merge(range('A', 'Z'), array('Ç', 'Ğ', 'İ', 'Ö', 'Ş', 'Ü', '#'));
        $results_text_with_letter = sprintf(_n('%s result', '%s results', $total_albums, 'gufte'), number_format_i18n($total_albums));
        if ($letter) {
            $results_text_with_letter .= ' — ' . sprintf(__('letter: %s', 'gufte'), $letter);
        }

        set_query_var('letter_filter_config', array(
            'current_letter' => $letter,
            'base_url' => get_permalink(),
            'preserve_params' => array(
                'q' => $q,
                'sort' => $sort,
                'per_page' => $per_page
            ),
            'letters' => $letters,
            'show_all_button' => true,
            'all_button_text' => __('All', 'gufte')
        ));
        get_template_part('template-parts/page-components/page-letter-filter');
        ?>

        <!-- Albüm Grid'i -->
        <section class="albums-listing px-4 sm:px-6 lg:px-8 py-6">
            <?php if (!empty($albums) && !is_wp_error($albums)) : ?>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 md:gap-6">
                    <?php foreach ($albums as $album) :
                        $album_name = $album->name;
                        $album_link = get_term_link($album);
                        $album_year = get_term_meta($album->term_id, 'album_year', true);
                        $track_count = isset($album->count) ? intval($album->count) : 0;

                        // Kapak görseli: Önce term metasındaki 'album_cover_id'
                        $album_cover_id  = get_term_meta($album->term_id, 'album_cover_id', true);
                        $album_image_url = '';
                        if ($album_cover_id) {
                            $album_image_url = wp_get_attachment_image_url($album_cover_id, 'medium');
                        }

                        // Fallback: İlgili ilk lyrics gönderisinin öne çıkan görseli
                        if (!$album_image_url) {
                            $first = get_posts(array(
                                'post_type'      => 'lyrics',
                                'posts_per_page' => 1,
                                'fields'         => 'ids',
                                'tax_query'      => array(
                                    array(
                                        'taxonomy' => 'album',
                                        'field'    => 'term_id',
                                        'terms'    => $album->term_id,
                                    ),
                                ),
                                'no_found_rows'  => true,
                                'orderby'        => 'date',
                                'order'          => 'DESC',
                            ));
                            if (!empty($first)) {
                                $thumb = get_the_post_thumbnail_url($first[0], 'medium');
                                if ($thumb) {
                                    $album_image_url = $thumb;
                                }
                            }
                        }

                        // Albümün şarkıcıları - album'e ait lyrics'lerden çek
                        $singers_display = '';
                        $album_singers = array();

                        // Önce helper fonksiyonunu dene
                        if (function_exists('gufte_get_album_singers')) {
                            $album_singers = gufte_get_album_singers($album->term_id);
                        }

                        // Eğer helper çalışmazsa, album'e ait lyrics'lerin singer'larını çek
                        if (empty($album_singers)) {
                            $album_lyrics = get_posts(array(
                                'post_type'      => 'lyrics',
                                'posts_per_page' => 5,
                                'fields'         => 'ids',
                                'tax_query'      => array(
                                    array(
                                        'taxonomy' => 'album',
                                        'field'    => 'term_id',
                                        'terms'    => $album->term_id,
                                    ),
                                ),
                                'no_found_rows'  => true,
                            ));

                            if (!empty($album_lyrics)) {
                                $singer_ids = array();
                                foreach ($album_lyrics as $lyric_id) {
                                    $lyric_singers = wp_get_post_terms($lyric_id, 'singer', array('fields' => 'ids'));
                                    if (!empty($lyric_singers)) {
                                        $singer_ids = array_merge($singer_ids, $lyric_singers);
                                    }
                                }

                                // Unique singer IDs
                                $singer_ids = array_unique($singer_ids);

                                if (!empty($singer_ids)) {
                                    $album_singers = get_terms(array(
                                        'taxonomy' => 'singer',
                                        'include'  => $singer_ids,
                                        'orderby'  => 'name',
                                        'order'    => 'ASC',
                                    ));
                                }
                            }
                        }

                        // Display formatı
                        if (!empty($album_singers) && !is_wp_error($album_singers)) {
                            $names = array_map(function($s) {
                                return '<a href="'. esc_url(get_term_link($s)) .'" class="hover:text-primary-600 underline-offset-2 hover:underline">'. esc_html($s->name) .'</a>';
                            }, $album_singers);
                            $names = array_slice($names, 0, 2);
                            $singers_display = implode(', ', $names);
                        }
                        ?>
                        <a href="<?php echo esc_url($album_link); ?>"
                           class="group relative flex flex-col bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <div class="relative">
                                <div class="aspect-square w-full bg-gray-100 overflow-hidden">
                                    <?php if ($album_image_url) : ?>
                                        <img src="<?php echo esc_url($album_image_url); ?>"
                                             alt="<?php echo esc_attr($album_name); ?>"
                                             loading="lazy"
                                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                                    <?php else : ?>
                                        <div class="w-full h-full flex items-center justify-center">
                                            <?php gufte_icon('album', 'text-5xl text-gray-300'); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Üst sağ: Yıl rozeti -->
                                <?php if (!empty($album_year)) : ?>
                                    <span class="absolute top-2 right-2 inline-flex items-center rounded-full bg-black/70 text-white text-xs font-semibold px-2 py-1 shadow">
                                        <?php gufte_icon('calendar', 'mr-1 text-sm'); ?>
                                        <?php echo esc_html($album_year); ?>
                                    </span>
                                <?php endif; ?>

                                <!-- Alt sağ: Parça sayısı -->
                                <?php if ($track_count > 0) : ?>
                                    <span class="absolute bottom-2 right-2 inline-flex items-center rounded-full bg-primary-600 text-white text-xs font-semibold px-2 py-1 shadow group-hover:scale-105 transition">
                                        <?php gufte_icon('music', 'mr-1 text-sm'); ?>
                                        <?php echo esc_html(number_format_i18n($track_count)); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="p-3 md:p-4 flex-1 flex flex-col">
                                <h3 class="text-sm md:text-base font-bold text-gray-900 line-clamp-1 group-hover:text-primary-700 transition-colors">
                                    <?php echo esc_html($album_name); ?>
                                </h3>

                                <?php if ($singers_display) : ?>
                                    <p class="mt-1 text-xs text-gray-600 line-clamp-1">
                                        <?php echo wp_kses_post($singers_display); ?>
                                        <?php
                                        if (function_exists('gufte_get_album_singers')) {
                                            // Basitçe “ve diğerleri” mantığı
                                            $total_singers = count((array) $album_singers);
                                            if ($total_singers > 2) {
                                                echo ' ' . esc_html__('and others', 'gufte');
                                            }
                                        }
                                        ?>
                                    </p>
                                <?php endif; ?>

                                <div class="mt-3 flex items-center justify-between">
                                    <span class="inline-flex items-center text-xs text-gray-500">
                                        <?php gufte_icon('album', 'mr-1 text-sm'); ?>
                                        <?php esc_html_e('Album', 'gufte'); ?>
                                    </span>
                                    <span class="inline-flex items-center text-xs font-medium text-primary-700 opacity-0 group-hover:opacity-100 transition">
                                        <?php esc_html_e('View details', 'gufte'); ?>
                                        <?php gufte_icon('arrow-right', 'ml-1'); ?>
                                    </span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="bg-white border border-gray-200 rounded-xl p-10 text-center">
                    <p class="text-lg text-gray-600">
                        <?php esc_html_e('No albums found.', 'gufte'); ?>
                    </p>
                </div>
            <?php endif; ?>
        </section>

        <?php
        // Pagination
        if ($total_albums > $per_page) {
            $total_pages = (int) ceil($total_albums / $per_page);

            set_query_var('pagination_config', array(
                'total_pages' => $total_pages,
                'current_page' => $paged,
                'base_url' => get_permalink(),
                'preserve_params' => array(
                    'q' => $q,
                    'sort' => $sort,
                    'per_page' => $per_page,
                    'letter' => $letter
                ),
                'mid_size' => 2,
                'prev_text' => __('Previous', 'gufte'),
                'next_text' => __('Next', 'gufte'),
            ));
            get_template_part('template-parts/page-components/page-pagination');
        }
        ?>

    </main>
</div>

<?php
// Yapısal veri (Schema.org JSON-LD)
if (!empty($albums) && !is_wp_error($albums)) {
    $schema_items = array();

    foreach ($albums as $album) {
        $album_name = $album->name;
        $album_link = get_term_link($album);
        $album_year = get_term_meta($album->term_id, 'album_year', true);

        // Album image
        $album_cover_id  = get_term_meta($album->term_id, 'album_cover_id', true);
        $album_image_url = '';
        if ($album_cover_id) {
            $album_image_url = wp_get_attachment_image_url($album_cover_id, 'medium');
        }

        $album_item = array(
            '@type' => 'MusicAlbum',
            'name' => $album_name,
            'url' => is_wp_error($album_link) ? '' : $album_link,
        );

        if ($album_image_url) {
            $album_item['image'] = $album_image_url;
        }

        if ($album_year) {
            $album_item['datePublished'] = $album_year;
        }

        $schema_items[] = $album_item;
    }

    // Schema.org için @graph kullanarak CollectionPage ve BreadcrumbList birlikte
    $schema = array(
        '@context' => 'https://schema.org',
        '@graph' => array(
            // CollectionPage
            array(
                '@type' => 'CollectionPage',
                '@id' => get_permalink() . '#page',
                'name' => get_the_title(),
                'description' => get_the_excerpt() ?: 'Browse our collection of music albums',
                'url' => get_permalink(),
                'breadcrumb' => array(
                    '@id' => get_permalink() . '#breadcrumb'
                ),
                'mainEntity' => array(
                    '@type' => 'ItemList',
                    'numberOfItems' => $total_albums,
                    'itemListElement' => array_map(function($item, $index) {
                        return array(
                            '@type' => 'ListItem',
                            'position' => $index + 1,
                            'item' => $item,
                        );
                    }, $schema_items, array_keys($schema_items))
                )
            ),
            // BreadcrumbList
            array(
                '@type' => 'BreadcrumbList',
                '@id' => get_permalink() . '#breadcrumb',
                'itemListElement' => array(
                    array(
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => 'Home',
                        'item' => home_url('/')
                    ),
                    array(
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => 'Albums',
                        'item' => get_permalink()
                    )
                )
            )
        )
    );
    ?>
    <script type="application/ld+json">
    <?php echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>
    </script>
    <?php
}

get_footer();
