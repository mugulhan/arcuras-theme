<?php
/**
 * Template for displaying all languages
 * Dosya adı: languages.php
 *
 * @package Gufte
 */

get_header();

$language_stats = gufte_get_language_statistics();
$total_songs = wp_count_posts('post')->publish;
$total_translations = 0;

// Toplam çeviri sayısını hesapla
foreach ($language_stats as $lang_data) {
    $total_translations += $lang_data['count'];
}
?>

<div class="site-content-wrapper flex flex-col md:flex-row">

    <?php get_template_part('template-parts/arcuras-sidebar'); ?>

    <main id="primary" class="site-main flex-1 px-4 sm:px-6 lg:px-8 py-8 overflow-x-hidden">

        <!-- Page Header -->
        <div class="languages-header mb-8 p-8 bg-gradient-to-r from-primary-50 via-accent-50 to-primary-50 rounded-xl border border-primary-100 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-primary-100/20 to-transparent"></div>
            <div class="relative z-10">
                <div class="text-center mb-6">
                    <div class="languages-flags text-6xl mb-4 flex justify-center items-center space-x-2">
                        🌍 🎵 🌐
                    </div>
                    <h1 class="text-4xl md:text-5xl font-extrabold text-gray-800 mb-4">
                        <?php esc_html_e('Browse by Language', 'gufte'); ?>
                    </h1>
                    <p class="text-lg text-gray-700 leading-relaxed max-w-3xl mx-auto">
                        <?php esc_html_e('Discover song lyrics in multiple languages. Our collection features high-quality translations to help you understand and enjoy music from around the world.', 'gufte'); ?>
                    </p>
                </div>

                <!-- Statistics -->
                <div class="stats-grid grid grid-cols-1 md:grid-cols-3 gap-6 max-w-2xl mx-auto">
                    <div class="stat-card bg-white/60 backdrop-blur-sm rounded-xl p-4 border border-primary-200/50 text-center">
                        <div class="iconify text-3xl text-primary-600 mb-2" data-icon="mdi:translate"></div>
                        <div class="text-2xl font-bold text-gray-800"><?php echo count($language_stats); ?></div>
                        <div class="text-sm text-gray-600"><?php esc_html_e('Languages', 'gufte'); ?></div>
                    </div>
                    <div class="stat-card bg-white/60 backdrop-blur-sm rounded-xl p-4 border border-primary-200/50 text-center">
                        <div class="iconify text-3xl text-primary-600 mb-2" data-icon="mdi:music-note-multiple"></div>
                        <div class="text-2xl font-bold text-gray-800"><?php echo number_format_i18n($total_songs); ?></div>
                        <div class="text-sm text-gray-600"><?php esc_html_e('Total Songs', 'gufte'); ?></div>
                    </div>
                    <div class="stat-card bg-white/60 backdrop-blur-sm rounded-xl p-4 border border-primary-200/50 text-center">
                        <div class="iconify text-3xl text-primary-600 mb-2" data-icon="mdi:earth"></div>
                        <div class="text-2xl font-bold text-gray-800"><?php echo number_format_i18n($total_translations); ?></div>
                        <div class="text-sm text-gray-600"><?php esc_html_e('Total Translations', 'gufte'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breadcrumb -->
        <nav class="breadcrumb mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 text-sm text-gray-600">
                <li><a href="<?php echo esc_url(home_url('/')); ?>" class="hover:text-primary-600 transition-colors duration-300"><?php esc_html_e('Home', 'gufte'); ?></a></li>
                <li><span class="iconify mx-2 text-gray-400" data-icon="mdi:chevron-right"></span></li>
                <li class="font-medium text-gray-800"><?php esc_html_e('Languages', 'gufte'); ?></li>
            </ol>
        </nav>

        <?php if (!empty($language_stats)) : ?>

        <!-- Search and Filter -->
        <div class="search-filter mb-8 p-6 bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <label for="language-search" class="sr-only"><?php esc_html_e('Search languages', 'gufte'); ?></label>
                    <div class="relative">
                        <span class="iconify absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" data-icon="mdi:magnify"></span>
                        <input type="text" id="language-search" placeholder="<?php esc_attr_e('Search languages...', 'gufte'); ?>" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <select id="sort-languages" class="border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="popular"><?php esc_html_e('Most Popular', 'gufte'); ?></option>
                        <option value="alphabetical"><?php esc_html_e('Alphabetical', 'gufte'); ?></option>
                        <option value="percentage"><?php esc_html_e('Highest Percentage', 'gufte'); ?></option>
                    </select>
                    
                    <button id="view-toggle" class="border border-gray-300 rounded-lg px-4 py-3 hover:bg-gray-50 transition-colors duration-300" data-view="grid" title="<?php esc_attr_e('Toggle view', 'gufte'); ?>">
                        <span class="iconify" data-icon="mdi:view-grid"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Languages Grid -->
        <div id="languages-container" class="languages-grid grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6">
            <?php foreach ($language_stats as $lang_slug => $data) : 
                $language_url = home_url("/language/{$lang_slug}/");
            ?>
            <a href="<?php echo esc_url($language_url); ?>" 
               class="language-card group relative bg-white rounded-xl p-6 border border-gray-200 hover:border-primary-400 transition-all duration-300 hover:-translate-y-2 hover:shadow-xl text-center"
               data-name="<?php echo esc_attr(strtolower($data['native_name'])); ?>"
               data-count="<?php echo esc_attr($data['count']); ?>"
               data-percentage="<?php echo esc_attr($data['percentage']); ?>">
               
                <!-- Background Pattern -->
                <div class="language-pattern absolute inset-0 rounded-xl overflow-hidden opacity-5">
                    <div class="w-full h-full flex items-center justify-center">
                        <span style="font-size: 6rem; opacity: 0.3;"><?php echo $data['flag']; ?></span>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="relative z-10">
                    <!-- Flag -->
                    <div class="language-flag text-4xl mb-3 transform group-hover:scale-110 transition-transform duration-300">
                        <?php echo $data['flag']; ?>
                    </div>
                    
                    <!-- Language Name -->
                    <h3 class="language-name font-bold text-gray-800 group-hover:text-primary-600 transition-colors duration-300 mb-2">
                        <?php echo esc_html($data['native_name']); ?>
                    </h3>
                    
                    <!-- Original Name (if different) -->
                    <?php if ($data['name'] !== $data['native_name']) : ?>
                    <div class="text-xs text-gray-500 mb-3">
                        <?php echo esc_html($data['name']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Song Count -->
                    <div class="language-count mb-3">
                        <span class="text-2xl font-bold text-primary-600 group-hover:text-primary-700 transition-colors duration-300">
                            <?php echo number_format_i18n($data['count']); ?>
                        </span>
                        <div class="text-sm text-gray-500 mt-1">
                            <?php echo _n('song', 'songs', $data['count'], 'gufte'); ?>
                        </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="progress-container mb-3">
                        <div class="progress-bar bg-gray-100 rounded-full h-2 overflow-hidden">
                            <div class="progress-fill h-full rounded-full transition-all duration-500 group-hover:duration-300" 
                                 style="width: <?php echo min($data['percentage'], 100); ?>%; background: <?php echo $data['color']; ?>;">
                            </div>
                        </div>
                        <div class="text-xs text-gray-400 mt-1">
                            <?php echo number_format($data['percentage'], 1); ?>% <?php esc_html_e('of all songs', 'gufte'); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Hover Effect -->
                <div class="absolute inset-0 rounded-xl bg-gradient-to-t from-primary-50/0 to-primary-50/0 group-hover:from-primary-50/30 group-hover:to-primary-50/10 transition-all duration-300 pointer-events-none"></div>
                
                <!-- Popular Badge -->
                <?php if (array_search($lang_slug, array_keys($language_stats)) < 3) : ?>
                <div class="absolute -top-2 -right-2 bg-accent-500 text-white text-xs font-bold px-2 py-1 rounded-full transform rotate-12 shadow-md">
                    <?php esc_html_e('Popular', 'gufte'); ?>
                </div>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- No Results Message (initially hidden) -->
        <div id="no-results" class="hidden text-center py-12">
            <div class="text-6xl mb-4">🔍</div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">
                <?php esc_html_e('No languages found', 'gufte'); ?>
            </h2>
            <p class="text-gray-600">
                <?php esc_html_e('Try adjusting your search terms or filters.', 'gufte'); ?>
            </p>
        </div>

        <!-- Popular Languages Highlight -->
        <div class="popular-highlight mt-12 p-8 bg-gradient-to-r from-accent-50 to-primary-50 rounded-xl border border-accent-200">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center flex items-center justify-center">
                <span class="iconify mr-3 text-accent-600" data-icon="mdi:star-circle"></span>
                <?php esc_html_e('Most Popular Languages', 'gufte'); ?>
            </h2>
            
            <div class="popular-languages-list grid grid-cols-1 md:grid-cols-3 gap-4">
                <?php 
                $top_languages = array_slice($language_stats, 0, 3, true);
                foreach ($top_languages as $lang_slug => $data) : 
                    $language_url = home_url("/language/{$lang_slug}/");
                ?>
                <a href="<?php echo esc_url($language_url); ?>" class="popular-lang-item group flex items-center bg-white/80 backdrop-blur-sm hover:bg-white border border-accent-200/50 hover:border-accent-300 rounded-xl p-4 transition-all duration-300 hover:scale-105 hover:shadow-lg">
                    <div class="flex-shrink-0 mr-4">
                        <div class="text-3xl"><?php echo $data['flag']; ?></div>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-gray-800 group-hover:text-accent-700 transition-colors duration-300">
                            <?php echo esc_html($data['native_name']); ?>
                        </h3>
                        <div class="text-sm text-gray-600">
                            <?php printf(
                                esc_html__('%s songs (%s%%)', 'gufte'),
                                number_format_i18n($data['count']),
                                number_format($data['percentage'], 1)
                            ); ?>
                        </div>
                    </div>
                    <div class="flex-shrink-0 ml-4">
                        <span class="iconify text-accent-600 group-hover:translate-x-1 transition-transform duration-300" data-icon="mdi:arrow-right"></span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php else : ?>
            <!-- Empty State -->
            <div class="empty-state text-center py-16">
                <div class="text-8xl mb-6">🌐</div>
                <h2 class="text-3xl font-bold text-gray-800 mb-4">
                    <?php esc_html_e('No translations available yet', 'gufte'); ?>
                </h2>
                <p class="text-gray-600 mb-8 max-w-md mx-auto">
                    <?php esc_html_e('We are working on adding multilingual support. Check back soon for translated song lyrics!', 'gufte'); ?>
                </p>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="inline-flex items-center bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg transition-colors duration-300">
                    <span class="iconify mr-2" data-icon="mdi:home"></span>
                    <?php esc_html_e('Back to Home', 'gufte'); ?>
                </a>
            </div>
        <?php endif; ?>

    </main>
</div>

<style>
.language-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    min-height: 200px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.language-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
}

/* List View Styles */
.languages-list .language-card {
    min-height: auto;
    flex-direction: row;
    text-align: left;
    padding: 1rem 1.5rem;
}

.languages-list .language-card .language-flag {
    font-size: 2rem;
    margin-bottom: 0;
    margin-right: 1rem;
}

.languages-list .language-card .relative {
    display: flex;
    align-items: center;
    width: 100%;
}

.languages-list .language-card .language-count {
    margin-left: auto;
    text-align: right;
    margin-bottom: 0;
}

.languages-list .progress-container {
    margin-bottom: 0;
    margin-left: 1rem;
    width: 100px;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .languages-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    
    .language-card {
        min-height: 160px;
        padding: 1rem;
    }
}

/* Search highlight */
.search-highlight {
    background-color: yellow;
    font-weight: bold;
}

/* Sort dropdown styling */
#sort-languages {
    appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 12px center;
    background-repeat: no-repeat;
    background-size: 16px;
    padding-right: 40px;
}

/* Loading states */
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.loading .language-card {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('language-search');
    const sortSelect = document.getElementById('sort-languages');
    const viewToggle = document.getElementById('view-toggle');
    const container = document.getElementById('languages-container');
    const noResults = document.getElementById('no-results');
    let currentView = 'grid';

    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            filterLanguages(searchTerm);
        });
    }

    // Sort functionality
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            sortLanguages(this.value);
        });
    }

    // View toggle functionality
    if (viewToggle) {
        viewToggle.addEventListener('click', function() {
            currentView = currentView === 'grid' ? 'list' : 'grid';
            this.dataset.view = currentView;
            
            if (currentView === 'list') {
                container.classList.remove('languages-grid');
                container.classList.add('languages-list', 'space-y-4');
                this.innerHTML = '<span class="iconify" data-icon="mdi:view-list"></span>';
            } else {
                container.classList.remove('languages-list', 'space-y-4');
                container.classList.add('languages-grid');
                this.innerHTML = '<span class="iconify" data-icon="mdi:view-grid"></span>';
            }
        });
    }

    // Filter languages based on search
    function filterLanguages(searchTerm) {
        const cards = container.querySelectorAll('.language-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const name = card.dataset.name;
            const isVisible = name.includes(searchTerm);
            
            if (isVisible) {
                card.style.display = '';
                visibleCount++;
                
                // Highlight search term
                const nameElement = card.querySelector('.language-name');
                if (searchTerm && nameElement) {
                    const originalText = nameElement.textContent;
                    const regex = new RegExp(`(${searchTerm})`, 'gi');
                    nameElement.innerHTML = originalText.replace(regex, '<span class="search-highlight">$1</span>');
                }
            } else {
                card.style.display = 'none';
            }
        });

        // Show/hide no results message
        if (visibleCount === 0) {
            container.style.display = 'none';
            noResults.classList.remove('hidden');
        } else {
            container.style.display = '';
            noResults.classList.add('hidden');
        }
    }

    // Sort languages
    function sortLanguages(sortBy) {
        const cards = Array.from(container.querySelectorAll('.language-card'));
        
        cards.sort((a, b) => {
            switch (sortBy) {
                case 'alphabetical':
                    return a.dataset.name.localeCompare(b.dataset.name);
                case 'percentage':
                    return parseFloat(b.dataset.percentage) - parseFloat(a.dataset.percentage);
                case 'popular':
                default:
                    return parseInt(b.dataset.count) - parseInt(a.dataset.count);
            }
        });

        // Re-append sorted cards
        cards.forEach(card => container.appendChild(card));
    }

    // Analytics tracking
    if (typeof gtag !== 'undefined') {
        gtag('event', 'page_view', {
            'page_title': 'Languages Directory',
            'page_location': window.location.href,
            'custom_map': {'custom_parameter_1': 'languages_directory'}
        });

        // Track language clicks
        document.querySelectorAll('.language-card').forEach(card => {
            card.addEventListener('click', function() {
                const languageName = this.querySelector('.language-name').textContent;
                gtag('event', 'language_selection', {
                    'event_category': 'engagement',
                    'event_label': languageName,
                    'value': 1
                });
            });
        });
    }
});
</script>

<?php
get_footer();