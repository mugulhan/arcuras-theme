<?php
/**
 * Template Name: Producers Page
 *
 * 'producer' taxonomy'sindeki terimleri modern bir grid arayüzde
 * arama, sıralama, baş harf filtresi ve sayfalama ile listeler.
 *
 * @package Gufte
 */

get_header();

// ---- URL Parametreleri ----
$paged     = max(1, get_query_var('paged') ? get_query_var('paged') : (get_query_var('page') ?: 1));
$q         = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
$sort      = isset($_GET['sort']) ? sanitize_text_field(wp_unslash($_GET['sort'])) : 'name_asc';
$per_page  = isset($_GET['per_page']) ? max(6, min(60, intval($_GET['per_page']))) : 24;
$letter    = isset($_GET['letter']) ? strtoupper(sanitize_text_field(wp_unslash($_GET['letter']))) : '';

// ---- get_terms() için temel argümanlar ----
$args = array(
    'taxonomy'   => 'producer',
    'hide_empty' => true,
    'number'     => $per_page,
    'offset'     => ($paged - 1) * $per_page,
);

// Arama (isimde arama)
if ($q !== '') {
    $args['search'] = $q; // get_terms name/slug içinde arar
}

// Baş harf filtresi (A–Z veya # = A–Z dışı)
if ($letter !== '') {
    // get_terms ilk harfe göre filtrelemediği için tüm sonuçları çekip PHP ile süzeceğiz
    $args['number'] = 0; // hepsini al, sonra slice
}

// Sıralama: name_asc | name_desc | count_desc | count_asc
switch ($sort) {
    case 'name_desc':
        $args['orderby'] = 'name';
        $args['order']   = 'DESC';
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
$total_args['fields'] = 'all';
$total_args['number'] = 0;
$total_args['offset'] = 0;

$all_terms = get_terms($total_args);
if (is_wp_error($all_terms)) {
    $all_terms = array();
}

// Baş harf filtresi
if ($letter !== '') {
    $all_terms = array_filter($all_terms, function($t) use ($letter) {
        $name  = remove_accents($t->name);
        $first = strtoupper(mb_substr(trim($name), 0, 1, 'UTF-8'));
        if ($letter === '#') {
            // A–Z (ve TR genişletmeleri) dışındakiler
            return !preg_match('/[A-ZÇĞİÖŞÜ]/u', $first);
        }
        return $first === $letter;
    });
}

$total_items = count($all_terms);

if ($letter !== '') {
    // PHP tarafında filtrelediğimiz için sıralamayı tekrar uygula
    usort($all_terms, function($a, $b) use ($sort) {
        switch ($sort) {
            case 'name_desc':
                return strcasecmp($b->name, $a->name);
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
    $producers = array_slice($all_terms, $offset, $per_page);
} else {
    // Letter yoksa, sayfalı veriyi direkt al
    $page_args = $args;
    $page_args['fields'] = 'all';
    $producers = get_terms($page_args);
    if (is_wp_error($producers)) {
        $producers = array();
    }

    // Toplamı doğru almak için
    $count_args = $args;
    $count_args['number'] = 0;
    $count_args['offset'] = 0;
    $count_args['fields'] = 'ids';
    $count_ids = get_terms($count_args);
    $total_items = is_wp_error($count_ids) ? 0 : count($count_ids);
}

// A–Z barı (TR harfleri dahil)
$letters = array_merge(range('A', 'Z'), array('Ç','Ğ','İ','Ö','Ş','Ü','#'));

?>
<div class="site-content-wrapper flex flex-col md:flex-row">

    <?php get_template_part('template-parts/arcuras-sidebar'); ?>

    <main id="primary" class="site-main flex-1 bg-white overflow-x-hidden">

        <?php
        // Breadcrumb
        set_query_var('breadcrumb_items', array(
            array('label' => 'Home', 'url' => home_url('/')),
            array('label' => __('Producers', 'gufte'))
        ));
        get_template_part('template-parts/page-components/page-breadcrumb');

        // Hero
        if (have_posts()) : while (have_posts()) : the_post();
            $hero_description = get_the_content() ? get_the_content() : '';
        endwhile; endif;

        set_query_var('hero_title', get_the_title());
        set_query_var('hero_icon', 'console');
        set_query_var('hero_description', $hero_description);
        set_query_var('hero_meta', array());
        get_template_part('template-parts/page-components/page-hero');
        ?>

        <?php
        // Filters - build results text including letter filter
        $results_text = sprintf(_n('%s producer', '%s producers', $total_items, 'gufte'), number_format_i18n($total_items));
        if ($letter) {
            $results_text .= ' — ' . sprintf(__('letter: %s', 'gufte'), $letter);
        }

        set_query_var('filter_config', array(
            'search' => array(
                'enabled' => true,
                'name' => 'q',
                'value' => $q,
                'label' => __('Search producers', 'gufte'),
                'placeholder' => __('Type producer name…', 'gufte'),
                'id' => 'producers-search'
            ),
            'sort' => array(
                'enabled' => true,
                'name' => 'sort',
                'value' => $sort,
                'label' => __('Sort by', 'gufte'),
                'id' => 'producers-sort',
                'options' => array(
                    'name_asc' => __('Name (A→Z)', 'gufte'),
                    'name_desc' => __('Name (Z→A)', 'gufte'),
                    'count_desc' => __('Productions (most)', 'gufte'),
                    'count_asc' => __('Productions (fewest)', 'gufte'),
                )
            ),
            'per_page' => array(
                'enabled' => true,
                'name' => 'per_page',
                'value' => $per_page,
                'label' => __('Per page', 'gufte'),
                'id' => 'producers-per-page',
                'options' => array(12, 24, 36, 48, 60)
            ),
            'results_text' => $results_text,
            'search_query' => $q,
            'action_url' => get_permalink()
        ));
        get_template_part('template-parts/page-components/page-filters');

        // Letter Filter
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

        <!-- Producer Grid -->
        <section class="producers-listing px-4 sm:px-6 lg:px-8 py-6">
            <?php if (!empty($producers)) : ?>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 md:gap-6">
                    <?php foreach ($producers as $pr) :
                        $pr_link   = get_term_link($pr);
                        $prod_cnt  = isset($pr->count) ? intval($pr->count) : 0;

                        // Görsel: öncelik term metasındaki 'profile_image_id'
                        $img_url = '';
                        $img_id  = get_term_meta($pr->term_id, 'profile_image_id', true);
                        if ($img_id) {
                            $img_url = wp_get_attachment_image_url($img_id, 'medium');
                        } elseif (function_exists('gufte_get_producer_image')) {
                            $maybe = gufte_get_producer_image($pr->term_id);
                            if (!empty($maybe)) $img_url = $maybe;
                        }

                        // Alt başlık: gerçek ad veya ülke
                        $real_name = get_term_meta($pr->term_id, 'real_name', true);
                        $country   = get_term_meta($pr->term_id, 'country', true);
                        $subtitle  = !empty($real_name) ? $real_name : (!empty($country) ? $country : '');
                        ?>
                        <a href="<?php echo esc_url($pr_link); ?>"
                           class="group relative bg-white border border-gray-200 rounded-2xl p-4 flex flex-col items-center text-center shadow-sm hover:shadow-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <div class="relative mb-3">
                                <div class="w-24 h-24 md:w-28 md:h-28 rounded-full overflow-hidden ring-4 ring-gray-100 group-hover:ring-primary-100 transition">
                                    <?php if ($img_url) : ?>
                                        <img src="<?php echo esc_url($img_url); ?>"
                                             alt="<?php echo esc_attr($pr->name); ?>"
                                             loading="lazy"
                                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                                    <?php else : ?>
                                        <div class="w-full h-full flex items-center justify-center bg-gray-100">
                                            <?php gufte_icon('console-line', 'text-4xl text-gray-400 group-hover:text-primary-600 transition'); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if ($prod_cnt > 0) : ?>
                                    <span class="absolute -bottom-1 -right-1 inline-flex items-center justify-center w-7 h-7 rounded-full bg-primary-600 text-white text-xs font-bold shadow ring-2 ring-white group-hover:scale-110 transition">
                                        <?php echo esc_html(number_format_i18n($prod_cnt)); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <h2 class="font-semibold text-sm md:text-base text-gray-900 group-hover:text-primary-700 transition line-clamp-2">
                                <?php echo esc_html($pr->name); ?>
                            </h2>

                            <?php if (!empty($subtitle)) : ?>
                                <p class="mt-1 text-xs text-gray-600 line-clamp-1">
                                    <?php echo esc_html($subtitle); ?>
                                </p>
                            <?php endif; ?>

                            <div class="mt-3 inline-flex items-center text-xs text-gray-500">
                                <?php gufte_icon('console-line', 'mr-1'); ?>
                                <?php esc_html_e('Producer', 'gufte'); ?>
                            </div>

                            <span class="absolute inset-x-0 bottom-2 mx-auto w-max text-xs font-medium text-primary-700 opacity-0 group-hover:opacity-100 transition">
                                <?php esc_html_e('View productions', 'gufte'); ?>
                                <?php gufte_icon('arrow-right', 'ml-1'); ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="bg-white border border-gray-200 rounded-xl p-10 text-center">
                    <p class="text-lg text-gray-600">
                        <?php esc_html_e('No producers found.', 'gufte'); ?>
                    </p>
                </div>
            <?php endif; ?>
        </section>

        <?php
        // Pagination
        if ($total_items > $per_page) {
            $total_pages = (int) ceil($total_items / $per_page);

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
get_footer();
