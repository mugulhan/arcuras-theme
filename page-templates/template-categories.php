<?php
/**
 * Template Name: All Categories
 * Template Post Type: page
 * 
 * Tüm kategorileri listeleyen özel sayfa şablonu.
 * Dosya adı: template-categories.php
 *
 * @package Gufte
 */

get_header();

// Yapılandırılmış veri için kategorileri hazırla
$all_categories_for_schema = get_categories(array(
    'hide_empty' => false,
    'parent' => 0,
    'orderby' => 'count',
    'order' => 'DESC'
));

// Schema.org JSON-LD yapılandırılmış veri
$schema_data = array(
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => get_the_title(),
    'description' => get_the_excerpt() ? get_the_excerpt() : __('All music categories and genres collection', 'gufte'),
    'url' => get_permalink(),
    'isPartOf' => array(
        '@type' => 'WebSite',
        'name' => get_bloginfo('name'),
        'url' => home_url()
    ),
    'breadcrumb' => array(
        '@type' => 'BreadcrumbList',
        'itemListElement' => array(
            array(
                '@type' => 'ListItem',
                'position' => 1,
                'name' => __('Home', 'gufte'),
                'item' => home_url()
            ),
            array(
                '@type' => 'ListItem',
                'position' => 2,
                'name' => get_the_title(),
                'item' => get_permalink()
            )
        )
    ),
    'mainEntity' => array(
        '@type' => 'ItemList',
        'numberOfItems' => count($all_categories_for_schema),
        'itemListElement' => array()
    )
);

// Her kategori için ListItem oluştur
$position = 1;
foreach ($all_categories_for_schema as $cat) {
    $category_url = get_category_link($cat->term_id);
    $recent_posts_in_cat = get_posts(array(
        'category' => $cat->term_id,
        'numberposts' => 5,
        'orderby' => 'date',
        'order' => 'DESC'
    ));
    
    $songs_list = array();
    foreach ($recent_posts_in_cat as $post) {
        $songs_list[] = array(
            '@type' => 'MusicRecording',
            'name' => $post->post_title,
            'url' => get_permalink($post)
        );
    }
    
    $schema_data['mainEntity']['itemListElement'][] = array(
        '@type' => 'ListItem',
        'position' => $position,
        'item' => array(
            '@type' => 'MusicPlaylist',
            'name' => $cat->name,
            'description' => $cat->description ? $cat->description : sprintf(__('Collection of %s songs', 'gufte'), $cat->count),
            'url' => $category_url,
            'numTracks' => $cat->count,
            'track' => $songs_list
        )
    );
    
    $position++;
    
    // Performans için ilk 20 kategori yeterli
    if ($position > 20) break;
}

// Aggregated Rating (eğer yorum/rating sisteminiz varsa)
if (function_exists('get_comments_number')) {
    $total_comments = wp_count_comments();
    if ($total_comments->approved > 0) {
        $schema_data['aggregateRating'] = array(
            '@type' => 'AggregateRating',
            'ratingValue' => '4.5', // Bu değeri dinamik yapabilirsiniz
            'reviewCount' => $total_comments->approved
        );
    }
}

// Yapılandırılmış veriyi head'e ekle
?>
<script type="application/ld+json">
<?php echo wp_json_encode($schema_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>
</script>

<?php
?>

<?php // Ana İçerik Sarmalayıcısı (Sidebar + Main) ?>
<div class="site-content-wrapper flex flex-col md:flex-row">

    <?php
    // Arcuras Sidebar'ı çağır
    get_template_part('template-parts/arcuras-sidebar');
    ?>

    <?php // Ana İçerik Alanı (Sağ Sütun) ?>
    <main id="primary" class="site-main flex-1 pt-8 pb-12 px-4 sm:px-6 lg:px-8 overflow-x-hidden">

        <?php // Sayfa Başlığı ?>
        <header class="page-header mb-8 bg-white p-4 md:p-6 rounded-lg shadow-md border border-gray-200">
            <h1 class="page-title text-3xl font-bold text-gray-800 mb-3 flex items-center">
                <span class="iconify mr-3 text-3xl text-primary-600" data-icon="mdi:folder-multiple"></span>
                <?php the_title(); ?>
            </h1>

            <?php // Sayfa içeriği varsa göster ?>
            <?php if (have_posts()) : ?>
                <?php while (have_posts()) : the_post(); ?>
                    <?php if (get_the_content()) : ?>
                        <div class="page-description text-gray-600 prose prose-sm max-w-none">
                            <?php the_content(); ?>
                        </div>
                    <?php endif; ?>
                <?php endwhile; ?>
            <?php endif; ?>

            <?php 
            // Toplam kategori sayısı
            $categories = get_categories(array(
                'hide_empty' => false,
                'parent' => 0
            ));
            $total_categories = count($categories);
            
            // Boş olmayan kategoriler
            $non_empty_categories = get_categories(array(
                'hide_empty' => true,
                'parent' => 0
            ));
            $active_categories = count($non_empty_categories);
            ?>

            <?php // İstatistikler ?>
            <div class="category-stats flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-gray-600 border-t border-gray-100 pt-3 mt-4">
                <div class="stat-item flex items-center">
                    <span class="iconify mr-1.5 text-base text-gray-400" data-icon="mdi:folder-outline"></span>
                    <span class="font-medium"><?php esc_html_e('Total Categories:', 'gufte'); ?></span>
                    <span class="ml-1 text-gray-800"><?php echo number_format_i18n($total_categories); ?></span>
                </div>
                <div class="stat-item flex items-center">
                    <span class="iconify mr-1.5 text-base text-gray-400" data-icon="mdi:folder-music"></span>
                    <span class="font-medium"><?php esc_html_e('Active Categories:', 'gufte'); ?></span>
                    <span class="ml-1 text-gray-800"><?php echo number_format_i18n($active_categories); ?></span>
                </div>
            </div>
        </header>

        <?php // Filtre ve Sıralama ?>
        <div class="categories-controls mb-6 bg-white p-4 rounded-lg shadow-md border border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <?php // Sol taraf - Filtreler ?>
                <div class="filter-buttons flex flex-wrap gap-2">
                    <button type="button" class="filter-btn active px-4 py-2 bg-primary-600 text-white rounded-full text-sm font-medium transition-all duration-200" data-filter="all">
                        <?php esc_html_e('All Categories', 'gufte'); ?>
                    </button>
                    <button type="button" class="filter-btn px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-full text-sm font-medium transition-all duration-200" data-filter="parent">
                        <?php esc_html_e('Main Categories', 'gufte'); ?>
                    </button>
                    <button type="button" class="filter-btn px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-full text-sm font-medium transition-all duration-200" data-filter="has-posts">
                        <?php esc_html_e('With Content', 'gufte'); ?>
                    </button>
                </div>

                <?php // Sağ taraf - Görünüm Seçici ?>
                <div class="view-switcher flex gap-2">
                    <button type="button" class="view-btn active p-2 bg-primary-100 text-primary-600 rounded" data-view="grid" title="<?php esc_attr_e('Grid View', 'gufte'); ?>">
                        <span class="iconify text-xl" data-icon="mdi:view-grid"></span>
                    </button>
                    <button type="button" class="view-btn p-2 bg-gray-100 text-gray-600 hover:bg-gray-200 rounded" data-view="list" title="<?php esc_attr_e('List View', 'gufte'); ?>">
                        <span class="iconify text-xl" data-icon="mdi:view-list"></span>
                    </button>
                </div>
            </div>
        </div>

        <?php // Ana Kategoriler Listesi ?>
        <div class="categories-container" id="categoriesContainer">
            <?php
            // Tüm kategorileri al (parent olanlar)
            $parent_categories = get_categories(array(
                'hide_empty' => false,
                'parent' => 0,
                'orderby' => 'name',
                'order' => 'ASC'
            ));

            if ($parent_categories) : ?>
                <div class="categories-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="categoriesGrid">
                    <?php foreach ($parent_categories as $category) : 
                        // Alt kategorileri al
                        $child_categories = get_categories(array(
                            'parent' => $category->term_id,
                            'hide_empty' => false,
                            'number' => 5 // İlk 5 alt kategoriyi göster
                        ));
                        
                        // Son yazıları al
                        $recent_posts = get_posts(array(
                            'category' => $category->term_id,
                            'numberposts' => 3,
                            'orderby' => 'date',
                            'order' => 'DESC'
                        ));
                        
                        $has_posts = $category->count > 0 ? 'has-posts' : 'no-posts';
                        $is_parent = count($child_categories) > 0 ? 'is-parent' : 'no-children';
                    ?>
                        <div class="category-card bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-primary-300" 
                             data-category-type="parent <?php echo esc_attr($has_posts); ?> <?php echo esc_attr($is_parent); ?>"
                             data-post-count="<?php echo esc_attr($category->count); ?>">
                            
                            <?php // Kategori Başlığı ?>
                            <div class="card-header bg-gradient-to-r from-primary-50 to-primary-100 p-4 border-b border-primary-200">
                                <h2 class="category-name text-lg font-bold mb-2">
                                    <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>" 
                                       class="text-gray-800 hover:text-primary-600 transition-colors duration-200 flex items-center">
                                        <span class="iconify mr-2 text-xl text-primary-500" data-icon="mdi:folder-music-outline"></span>
                                        <?php echo esc_html($category->name); ?>
                                    </a>
                                </h2>
                                
                                <?php // Yazı sayısı badge ?>
                                <div class="category-meta flex items-center gap-3 text-sm">
                                    <span class="post-count flex items-center text-gray-600">
                                        <span class="iconify mr-1 text-base" data-icon="mdi:file-document-outline"></span>
                                        <?php printf(
                                            esc_html(_n('%s Song', '%s Songs', $category->count, 'gufte')),
                                            '<strong>' . number_format_i18n($category->count) . '</strong>'
                                        ); ?>
                                    </span>
                                    <?php if (count($child_categories) > 0) : ?>
                                        <span class="subcategory-count flex items-center text-gray-600">
                                            <span class="iconify mr-1 text-base" data-icon="mdi:folder-multiple-outline"></span>
                                            <?php echo number_format_i18n(count($child_categories)); ?> <?php esc_html_e('Subcategories', 'gufte'); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php // Kategori İçeriği ?>
                            <div class="card-body p-4">
                                <?php // Kategori açıklaması ?>
                                <?php if ($category->description) : ?>
                                    <div class="category-description text-sm text-gray-600 mb-3 line-clamp-2">
                                        <?php echo esc_html($category->description); ?>
                                    </div>
                                <?php endif; ?>

                                <?php // Alt kategoriler ?>
                                <?php if ($child_categories) : ?>
                                    <div class="child-categories mb-3">
                                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                                            <?php esc_html_e('Subcategories:', 'gufte'); ?>
                                        </h3>
                                        <div class="flex flex-wrap gap-1">
                                            <?php foreach ($child_categories as $child) : ?>
                                                <a href="<?php echo esc_url(get_category_link($child->term_id)); ?>" 
                                                   class="inline-block px-2 py-1 bg-gray-100 hover:bg-primary-100 text-gray-700 hover:text-primary-700 rounded text-xs transition-all duration-200"
                                                   title="<?php printf(esc_attr(_n('%s Song', '%s Songs', $child->count, 'gufte')), number_format_i18n($child->count)); ?>">
                                                    <?php echo esc_html($child->name); ?>
                                                    <?php if ($child->count > 0) : ?>
                                                        <span class="text-gray-500">(<?php echo number_format_i18n($child->count); ?>)</span>
                                                    <?php endif; ?>
                                                </a>
                                            <?php endforeach; ?>
                                            
                                            <?php 
                                            // Daha fazla alt kategori varsa göster
                                            $total_children = count(get_categories(array('parent' => $category->term_id, 'hide_empty' => false)));
                                            if ($total_children > 5) : ?>
                                                <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>" 
                                                   class="inline-block px-2 py-1 bg-primary-50 hover:bg-primary-100 text-primary-600 rounded text-xs font-medium transition-all duration-200">
                                                    +<?php echo number_format_i18n($total_children - 5); ?> <?php esc_html_e('more', 'gufte'); ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php // Son yazılar ?>
                                <?php if ($recent_posts) : ?>
                                    <div class="recent-posts">
                                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                                            <?php esc_html_e('Recent Songs:', 'gufte'); ?>
                                        </h3>
                                        <ul class="space-y-1">
                                            <?php foreach ($recent_posts as $post) : ?>
                                                <li class="text-sm">
                                                    <a href="<?php echo esc_url(get_permalink($post)); ?>" 
                                                       class="text-gray-700 hover:text-primary-600 transition-colors duration-200 flex items-start">
                                                        <span class="iconify mr-1 mt-0.5 text-gray-400 flex-shrink-0" data-icon="mdi:music-note"></span>
                                                        <span class="line-clamp-1"><?php echo esc_html($post->post_title); ?></span>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php // Kart Footer ?>
                            <div class="card-footer bg-gray-50 px-4 py-3 border-t border-gray-200">
                                <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>" 
                                   class="inline-flex items-center text-sm font-medium text-primary-600 hover:text-primary-700 transition-colors duration-200">
                                    <?php esc_html_e('View Category', 'gufte'); ?>
                                    <span class="iconify ml-1" data-icon="mdi:arrow-right"></span>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php // Liste Görünümü (Başlangıçta gizli) ?>
                <div class="categories-list hidden" id="categoriesList">
                    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <?php esc_html_e('Category', 'gufte'); ?>
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <?php esc_html_e('Songs', 'gufte'); ?>
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">
                                        <?php esc_html_e('Subcategories', 'gufte'); ?>
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <?php esc_html_e('Action', 'gufte'); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($parent_categories as $category) : 
                                    $child_count = count(get_categories(array('parent' => $category->term_id, 'hide_empty' => false)));
                                ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center">
                                                <span class="iconify mr-2 text-lg text-primary-500" data-icon="mdi:folder-music-outline"></span>
                                                <div>
                                                    <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>" 
                                                       class="text-sm font-medium text-gray-900 hover:text-primary-600">
                                                        <?php echo esc_html($category->name); ?>
                                                    </a>
                                                    <?php if ($category->description) : ?>
                                                        <p class="text-xs text-gray-500 mt-0.5 line-clamp-1">
                                                            <?php echo esc_html($category->description); ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?php echo $category->count > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'; ?>">
                                                <?php echo number_format_i18n($category->count); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center hidden md:table-cell">
                                            <?php if ($child_count > 0) : ?>
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    <?php echo number_format_i18n($child_count); ?>
                                                </span>
                                            <?php else : ?>
                                                <span class="text-gray-400">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>" 
                                               class="inline-flex items-center text-sm text-primary-600 hover:text-primary-700 font-medium">
                                                <?php esc_html_e('View', 'gufte'); ?>
                                                <span class="iconify ml-1" data-icon="mdi:arrow-right"></span>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php else : ?>
                <div class="no-categories text-center py-12">
                    <span class="iconify text-6xl text-gray-300 mb-4" data-icon="mdi:folder-off-outline"></span>
                    <p class="text-lg text-gray-500">
                        <?php esc_html_e('No categories found.', 'gufte'); ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>

    </main>
</div>

<?php // JavaScript ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filtre butonları
    const filterButtons = document.querySelectorAll('.filter-btn');
    const categoryCards = document.querySelectorAll('.category-card');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Aktif sınıfını güncelle
            filterButtons.forEach(btn => {
                btn.classList.remove('active', 'bg-primary-600', 'text-white');
                btn.classList.add('bg-gray-100', 'text-gray-700');
            });
            this.classList.remove('bg-gray-100', 'text-gray-700');
            this.classList.add('active', 'bg-primary-600', 'text-white');
            
            // Filtreleme
            const filter = this.dataset.filter;
            categoryCards.forEach(card => {
                if (filter === 'all') {
                    card.style.display = '';
                } else if (filter === 'parent') {
                    card.style.display = card.dataset.categoryType.includes('is-parent') ? '' : 'none';
                } else if (filter === 'has-posts') {
                    card.style.display = card.dataset.categoryType.includes('has-posts') ? '' : 'none';
                }
            });
        });
    });
    
    // Görünüm değiştirici
    const viewButtons = document.querySelectorAll('.view-btn');
    const gridView = document.getElementById('categoriesGrid');
    const listView = document.getElementById('categoriesList');
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Aktif sınıfını güncelle
            viewButtons.forEach(btn => {
                btn.classList.remove('active', 'bg-primary-100', 'text-primary-600');
                btn.classList.add('bg-gray-100', 'text-gray-600');
            });
            this.classList.remove('bg-gray-100', 'text-gray-600');
            this.classList.add('active', 'bg-primary-100', 'text-primary-600');
            
            // Görünümü değiştir
            const view = this.dataset.view;
            if (view === 'grid') {
                gridView.classList.remove('hidden');
                listView.classList.add('hidden');
            } else {
                gridView.classList.add('hidden');
                listView.classList.remove('hidden');
            }
        });
    });
});
</script>

<?php
get_footer();
?>