<?php
/**
 * Gufte Theme - Multilingual Sitemap Generator
 * 
 * Bu dosyayı tema klasörünüze sitemap-generator.php olarak kaydedin
 * 
 * @package Gufte
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Ana Sitemap Generator Sınıfı
 */
class Gufte_Sitemap_Generator {
    
    /**
     * Dil ayarları
     */
    private $language_settings;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->language_settings = $this->get_language_settings();
        $this->init_hooks();
    }
    
    /**
     * WordPress hook'larını başlat
     */
    private function init_hooks() {
        // Disable WordPress core sitemaps completely
        add_filter('wp_sitemaps_enabled', '__return_false', 1);

        // Sitemap endpoint'lerini kaydet
        add_action('init', array($this, 'add_sitemap_endpoints'));

        // Flush rewrite rules on theme activation
        add_action('after_switch_theme', array($this, 'flush_sitemap_rewrite_rules'));
        
        // Query vars ekle
        add_filter('query_vars', array($this, 'add_sitemap_query_vars'));
        
        // Template redirect
        add_action('template_redirect', array($this, 'handle_sitemap_requests'));
        
        // Robots.txt'ye sitemap ekle
        add_filter('robots_txt', array($this, 'add_sitemaps_to_robots'), 10, 2);
        
        // ========== GELİŞTİRİLMİŞ TETİKLEMELER ==========
        
        // POST DEĞİŞİKLİKLERİ
        add_action('save_post', array($this, 'trigger_sitemap_update'));
        add_action('delete_post', array($this, 'trigger_sitemap_update'));
        add_action('wp_trash_post', array($this, 'trigger_sitemap_update'));
        add_action('untrash_post', array($this, 'trigger_sitemap_update'));
        add_action('transition_post_status', array($this, 'handle_post_status_change'), 10, 3);
        
        // META DEĞİŞİKLİKLERİ
        add_action('updated_post_meta', array($this, 'handle_meta_update'), 10, 4);
        add_action('added_post_meta', array($this, 'handle_meta_update'), 10, 4);
        add_action('deleted_post_meta', array($this, 'handle_meta_update'), 10, 4);
        
        // TAKSONOMİ DEĞİŞİKLİKLERİ
        add_action('created_term', array($this, 'handle_taxonomy_change'), 10, 3);
        add_action('edited_term', array($this, 'handle_taxonomy_change'), 10, 3);
        add_action('delete_term', array($this, 'handle_taxonomy_change'), 10, 3);
        
        // POST-TERM İLİŞKİLERİ
        add_action('set_object_terms', array($this, 'handle_term_relationship_change'), 10, 6);
        
        // FEATURED IMAGE DEĞİŞİKLİKLERİ
        add_action('updated_post_meta', array($this, 'handle_thumbnail_change'), 10, 4);
        
        // DİL BİLGİLERİ DEĞİŞİKLİKLERİ
        add_action('updated_post_meta', array($this, 'handle_language_change'), 10, 4);
        
        // BULK İŞLEMLER
        add_action('wp_ajax_bulk-edit', array($this, 'handle_bulk_edit'));
        
        // YORUM DEĞİŞİKLİKLERİ (sayfa yoğunluğunu etkileyebilir)
        add_action('comment_post', array($this, 'handle_comment_change'));
        add_action('wp_set_comment_status', array($this, 'handle_comment_change'));
        
        // Admin hooks
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_init', array($this, 'handle_admin_actions'));
            
            // Admin'de manuel tetikleme
            add_action('admin_post_trigger_sitemap_update', array($this, 'manual_trigger_sitemap_update'));
        }
    }
    
    /**
     * Post durum değişikliklerini handle et
     */
    public function handle_post_status_change($new_status, $old_status, $post) {
        if ($post->post_type === 'lyrics') { // Changed from 'post' to 'lyrics'
            // Yayın durumu değişen postlar için sitemap güncelle
            if ($old_status !== $new_status && 
                ($new_status === 'publish' || $old_status === 'publish')) {
                $this->trigger_sitemap_update($post->ID);
            }
        }
    }

    /**
     * Meta değişikliklerini handle et
     */
    public function handle_meta_update($meta_id, $post_id, $meta_key, $meta_value) {
        // Sadece lyrics post tipinde olanları işle
        if (get_post_type($post_id) !== 'lyrics') { // Changed from 'post' to 'lyrics'
            return;
        }
        
        // SEO/Sitemap için önemli meta keyler
        $important_meta_keys = array(
            '_available_languages',    // Dil bilgileri
            '_apple_music_id',        // Apple Music ID
            '_release_date',          // Çıkış tarihi
            '_music_genre',           // Müzik türü
            'spotify_url',            // Spotify URL
            'youtube_url',            // YouTube URL
            'apple_music_url',        // Apple Music URL
            'music_video_url',        // Video URL
            '_yoast_wpseo_title',     // Yoast SEO başlık
            '_yoast_wpseo_metadesc',  // Yoast SEO açıklama
            '_thumbnail_id'           // Featured image
        );
        
        if (in_array($meta_key, $important_meta_keys)) {
            $this->trigger_sitemap_update($post_id, 'meta_update');
        }
    }

    /**
     * Taksonomi değişikliklerini handle et
     */
    public function handle_taxonomy_change($term_id, $tt_id, $taxonomy) {
        // Müzik ile ilgili taksonomiler
        $music_taxonomies = array('singer', 'album', 'category', 'post_tag', 'songwriter', 'producer');
        
        if (in_array($taxonomy, $music_taxonomies)) {
            $this->trigger_sitemap_update(null, 'taxonomy_change');
        }
    }

    /**
     * Term-post ilişki değişikliklerini handle et
     */
    public function handle_term_relationship_change($object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids) {
        if (get_post_type($object_id) === 'lyrics') {
            $this->trigger_sitemap_update($object_id, 'term_relationship');
        }
    }

    /**
     * Featured image değişikliklerini handle et
     */
    public function handle_thumbnail_change($meta_id, $post_id, $meta_key, $meta_value) {
        if ($meta_key === '_thumbnail_id' && get_post_type($post_id) === 'lyrics') {
            $this->trigger_sitemap_update($post_id, 'thumbnail_change');
        }
    }

    /**
     * Dil bilgisi değişikliklerini handle et
     */
    public function handle_language_change($meta_id, $post_id, $meta_key, $meta_value) {
        if ($meta_key === '_available_languages' && get_post_type($post_id) === 'lyrics') {
            // Dil değişikliği çok dilli sitemap'i özellikle etkiler
            $this->trigger_sitemap_update($post_id, 'language_change');
            
            // Özel olarak çok dilli sitemap cache'ini temizle
            wp_cache_delete('gufte_sitemap_multilingual', 'gufte_sitemaps');
        }
    }

    /**
     * Bulk edit işlemlerini handle et
     */
    public function handle_bulk_edit() {
        // Bulk edit sonrası sitemap güncelle
        $this->trigger_sitemap_update(null, 'bulk_edit');
    }

    /**
     * Yorum değişikliklerini handle et
     */
    public function handle_comment_change($comment_id) {
        $comment = get_comment($comment_id);
        if ($comment && get_post_type($comment->comment_post_ID) === 'lyrics') {
            // Yorumlar çok sık değiştiği için 10 dakika delay ekle
            wp_schedule_single_event(time() + 600, 'gufte_delayed_sitemap_update', array($comment->comment_post_ID));
        }
    }

    /**
     * Geliştirilmiş sitemap tetikleme
     */
    public function trigger_sitemap_update($post_id = null, $reason = 'unknown') {
        // Debug log (geliştirme aşamasında)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Gufte Sitemap: Triggered update - Post ID: {$post_id}, Reason: {$reason}");
        }
        
        // Cache temizle
        $this->clear_sitemap_cache($post_id);
        
        // Rate limiting - çok sık tetiklenmesini önle
        $last_trigger = get_transient('gufte_sitemap_last_trigger');
        
        if (!$last_trigger || (time() - $last_trigger) > 60) { // 1 dakika minimum aralık
            // Arama motorlarına ping gönder (5 dakika sonra)
            wp_schedule_single_event(time() + 300, 'gufte_ping_search_engines');
            
            // Son tetikleme zamanını kaydet
            set_transient('gufte_sitemap_last_trigger', time(), 300); // 5 dakika cache
            
            // Admin notice (sadece admin panelde)
            if (is_admin() && current_user_can('manage_options')) {
                set_transient('gufte_sitemap_updated_notice', $reason, 10);
            }
        }
    }

    /**
     * Manuel sitemap tetikleme (Admin için)
     */
    public function manual_trigger_sitemap_update() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        check_admin_referer('manual_sitemap_update');
        
        $this->trigger_sitemap_update(null, 'manual_admin_trigger');
        
        wp_redirect(add_query_arg(array(
            'page' => 'gufte-sitemap-generator',
            'updated' => 'manual_trigger'
        ), admin_url('tools.php')));
        exit;
    }
    
    /**
     * Dil ayarlarını al
     */
    private function get_language_settings() {
        return array(
            'language_map' => array(
                'english' => 'English Translation',
                'spanish' => 'Traducción al Español', 
                'turkish' => 'Türkçe Çevirisi',
                'german' => 'Deutsche Übersetzung',
                'arabic' => 'الترجمة العربية',
                'french' => 'Traduction en Français',
                'italian' => 'Traduzione in Italiano',
                'portuguese' => 'Tradução em Português',
                'russian' => 'Русский перевод',
                'japanese' => '日本語翻訳',
            ),
            'iso_map' => array(
                'english' => 'en',
                'spanish' => 'es', 
                'turkish' => 'tr',
                'german' => 'de',
                'arabic' => 'ar',
                'french' => 'fr',
                'italian' => 'it',
                'portuguese' => 'pt',
                'russian' => 'ru',
                'japanese' => 'ja',
            )
        );
    }
    
    /**
     * Sitemap endpoint'lerini ekle
     */
    public function add_sitemap_endpoints() {
        // Ana sitemap index - çakışmayı önlemek için farklı isim
        add_rewrite_rule('^gufte-sitemap\.xml$', 'index.php?gufte_sitemap=index', 'top');
        add_rewrite_rule('^sitemap\.xml$', 'index.php?gufte_sitemap=index', 'top');
        add_rewrite_rule('^sitemap_index\.xml$', 'index.php?gufte_sitemap=index', 'top');
        add_rewrite_rule('^sitemap-index\.xml$', 'index.php?gufte_sitemap=index', 'top');
        add_rewrite_rule('^wp-sitemap\.xml$', 'index.php?gufte_sitemap=index', 'top');
        
        // Post sitemap'i
        add_rewrite_rule('^gufte-sitemap-posts\.xml$', 'index.php?gufte_sitemap=posts', 'top');
        
        // Çok dilli sitemap
        add_rewrite_rule('^gufte-sitemap-multilingual\.xml$', 'index.php?gufte_sitemap=multilingual', 'top');
        
        // Sayfa sitemap'i
        add_rewrite_rule('^gufte-sitemap-pages\.xml$', 'index.php?gufte_sitemap=pages', 'top');
        
        // Kategori sitemap'i
        add_rewrite_rule('^gufte-sitemap-categories\.xml$', 'index.php?gufte_sitemap=categories', 'top');
        
        // Şarkıcı sitemap'i
        add_rewrite_rule('^gufte-sitemap-singers\.xml$', 'index.php?gufte_sitemap=singers', 'top');
        
        // Albüm sitemap'i
        add_rewrite_rule('^gufte-sitemap-albums\.xml$', 'index.php?gufte_sitemap=albums', 'top');
        
        // Songwriter sitemap'i
        add_rewrite_rule('^gufte-sitemap-songwriters\.xml$', 'index.php?gufte_sitemap=songwriters', 'top');
        
        //Producer sitemap'i
        add_rewrite_rule('^gufte-sitemap-producers\.xml$',   'index.php?gufte_sitemap=producers',   'top');

    }

    /**
     * Flush rewrite rules for sitemap endpoints
     */
    public function flush_sitemap_rewrite_rules() {
        $this->add_sitemap_endpoints();
        flush_rewrite_rules();
    }

    /**
     * Query vars ekle
     */
    public function add_sitemap_query_vars($vars) {
        $vars[] = 'gufte_sitemap';
        return $vars;
    }
    
    /**
     * Sitemap isteklerini işle
     */
    public function handle_sitemap_requests() {
        $sitemap_type = get_query_var('gufte_sitemap');
        
        if (empty($sitemap_type)) {
            return;
        }
        
        // Cache kontrol et
        $cache_key = 'gufte_sitemap_' . $sitemap_type;
        $cached_sitemap = wp_cache_get($cache_key, 'gufte_sitemaps');
        
        if ($cached_sitemap !== false) {
            $this->output_sitemap($cached_sitemap);
            return;
        }
        
        // Sitemap oluştur
        $sitemap_content = '';
        
        switch ($sitemap_type) {
            case 'index':
                $sitemap_content = $this->generate_index_sitemap();
                break;
            case 'posts':
                $sitemap_content = $this->generate_posts_sitemap();
                break;
            case 'multilingual':
                $sitemap_content = $this->generate_multilingual_sitemap();
                break;
            case 'pages':
                $sitemap_content = $this->generate_pages_sitemap();
                break;
            case 'categories':
                $sitemap_content = $this->generate_categories_sitemap();
                break;
            case 'singers':
                $sitemap_content = $this->generate_singers_sitemap();
                break;
            case 'albums':
                $sitemap_content = $this->generate_albums_sitemap();
                break;
            case 'songwriters':
                $sitemap_content = $this->generate_songwriters_sitemap();
                break;
            case 'producers':
                $sitemap_content = $this->generate_producers_sitemap();
                break;

            default:
                wp_die('Invalid sitemap type', 'Sitemap Error', array('response' => 404));
        }
        
        // Cache'e kaydet (1 saat)
        wp_cache_set($cache_key, $sitemap_content, 'gufte_sitemaps', 3600);
        
        $this->output_sitemap($sitemap_content);
    }
    
    /**
     * Index sitemap oluştur
     */
    private function generate_index_sitemap() {
        $sitemaps = array(
            array(
                'loc' => home_url('/gufte-sitemap-posts.xml'),
                'lastmod' => $this->get_posts_lastmod()
            ),
            // Multilingual sitemap removed - translations now included in posts sitemap
            // array(
            //     'loc' => home_url('/gufte-sitemap-multilingual.xml'),
            //     'lastmod' => $this->get_posts_lastmod()
            // ),
            array(
                'loc' => home_url('/gufte-sitemap-pages.xml'),
                'lastmod' => $this->get_pages_lastmod()
            ),
            array(
                'loc' => home_url('/gufte-sitemap-categories.xml'),
                'lastmod' => $this->get_posts_lastmod()
            ),
            array(
                'loc' => home_url('/gufte-sitemap-singers.xml'),
                'lastmod' => $this->get_posts_lastmod()
            ),
            array(
                'loc' => home_url('/gufte-sitemap-albums.xml'),
                'lastmod' => $this->get_posts_lastmod()
            ),
            array(
                'loc' => home_url('/gufte-sitemap-songwriters.xml'),
                'lastmod' => $this->get_posts_lastmod()
            ),
            array(
            'loc' => home_url('/gufte-sitemap-producers.xml'),
            'lastmod' => $this->get_posts_lastmod()
        ),

        );
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        foreach ($sitemaps as $sitemap) {
            $xml .= "\t<sitemap>\n";
            $xml .= "\t\t<loc>" . esc_url($sitemap['loc']) . "</loc>\n";
            $xml .= "\t\t<lastmod>" . $sitemap['lastmod'] . "</lastmod>\n";
            $xml .= "\t</sitemap>\n";
        }
        
        $xml .= '</sitemapindex>';
        
        return $xml;
    }
    
    /**
     * Post sitemap oluştur (lyrics post type)
     */
    private function generate_posts_sitemap() {
        $posts = get_posts(array(
            'post_type' => 'lyrics', // Changed from 'post' to 'lyrics'
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'modified',
            'order' => 'DESC'
        ));

        $xml = $this->get_sitemap_header();

        foreach ($posts as $post) {
            $permalink = get_permalink($post->ID);
            $lastmod = get_the_modified_date('c', $post->ID);

            // Original URL
            $xml .= "\t<url>\n";
            $xml .= "\t\t<loc>" . esc_url($permalink) . "</loc>\n";
            $xml .= "\t\t<lastmod>" . $lastmod . "</lastmod>\n";
            $xml .= "\t\t<changefreq>weekly</changefreq>\n";
            $xml .= "\t\t<priority>0.9</priority>\n";
            $xml .= "\t</url>\n";

            // Add translation URLs
            $available_languages = $this->get_post_languages($post->ID);

            foreach ($available_languages as $lang) {
                // Skip English (original)
                if ($lang === 'english') continue;

                $lang_code = $this->language_settings['iso_map'][$lang];
                $lang_url = untrailingslashit($permalink) . '/' . $lang_code . '/';

                $xml .= "\t<url>\n";
                $xml .= "\t\t<loc>" . esc_url($lang_url) . "</loc>\n";
                $xml .= "\t\t<lastmod>" . $lastmod . "</lastmod>\n";
                $xml .= "\t\t<changefreq>weekly</changefreq>\n";
                $xml .= "\t\t<priority>0.8</priority>\n";
                $xml .= "\t</url>\n";
            }
        }

        $xml .= '</urlset>';

        return $xml;
    }
    
    /**
     * Çok dilli sitemap oluştur (lyrics post type)
     */
    private function generate_multilingual_sitemap() {
        $posts = get_posts(array(
            'post_type' => 'lyrics', // Changed from 'post' to 'lyrics'
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'modified',
            'order' => 'DESC'
        ));
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
        
        foreach ($posts as $post) {
            $permalink = get_permalink($post->ID);
            $lastmod = get_the_modified_date('c', $post->ID);
            
            // Mevcut dilleri al
            $available_languages = $this->get_post_languages($post->ID);
            
            // Orijinal URL (English)
            $xml .= "\t<url>\n";
            $xml .= "\t\t<loc>" . esc_url($permalink) . "</loc>\n";
            $xml .= "\t\t<lastmod>" . $lastmod . "</lastmod>\n";
            $xml .= "\t\t<changefreq>weekly</changefreq>\n";
            $xml .= "\t\t<priority>0.9</priority>\n";
            
            // Hreflang alternatifleri (using new URL structure /tr/, /es/)
            foreach ($available_languages as $lang) {
                $hreflang = $this->language_settings['iso_map'][$lang];

                // Use new URL format: /lyrics/song-name/tr/
                if ($lang === 'english') {
                    $lang_url = $permalink;
                } else {
                    $lang_code = $this->language_settings['iso_map'][$lang];
                    $lang_url = untrailingslashit($permalink) . '/' . $lang_code . '/';
                }

                $xml .= "\t\t<xhtml:link rel=\"alternate\" hreflang=\"" . $hreflang . "\" href=\"" . esc_url($lang_url) . "\" />\n";
            }

            $xml .= "\t</url>\n";

            // Her dil için ayrı URL (English hariç) - using new URL structure
            foreach ($available_languages as $lang) {
                if ($lang === 'english') continue;

                $lang_code = $this->language_settings['iso_map'][$lang];
                $lang_url = untrailingslashit($permalink) . '/' . $lang_code . '/';

                $xml .= "\t<url>\n";
                $xml .= "\t\t<loc>" . esc_url($lang_url) . "</loc>\n";
                $xml .= "\t\t<lastmod>" . $lastmod . "</lastmod>\n";
                $xml .= "\t\t<changefreq>weekly</changefreq>\n";
                $xml .= "\t\t<priority>0.8</priority>\n";

                // Hreflang alternatifleri
                foreach ($available_languages as $alt_lang) {
                    $alt_hreflang = $this->language_settings['iso_map'][$alt_lang];

                    if ($alt_lang === 'english') {
                        $alt_url = $permalink;
                    } else {
                        $alt_lang_code = $this->language_settings['iso_map'][$alt_lang];
                        $alt_url = untrailingslashit($permalink) . '/' . $alt_lang_code . '/';
                    }

                    $xml .= "\t\t<xhtml:link rel=\"alternate\" hreflang=\"" . $alt_hreflang . "\" href=\"" . esc_url($alt_url) . "\" />\n";
                }

                $xml .= "\t</url>\n";
            }
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }
    
    /**
     * Sayfa sitemap oluştur
     */
    private function generate_pages_sitemap() {
        $pages = get_pages(array(
            'post_status' => 'publish',
            'sort_column' => 'post_modified'
        ));
        
        $xml = $this->get_sitemap_header();
        
        foreach ($pages as $page) {
            $xml .= "\t<url>\n";
            $xml .= "\t\t<loc>" . esc_url(get_permalink($page->ID)) . "</loc>\n";
            $xml .= "\t\t<lastmod>" . get_the_modified_date('c', $page->ID) . "</lastmod>\n";
            $xml .= "\t\t<changefreq>monthly</changefreq>\n";
            $xml .= "\t\t<priority>0.8</priority>\n";
            $xml .= "\t</url>\n";
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }
    
    /**
     * Kategori sitemap oluştur (translated_language and original_language taxonomies)
     */
    private function generate_categories_sitemap() {
        $xml = $this->get_sitemap_header();

        // Get translated language terms (e.g., /translation/spanish/)
        $translated_langs = get_terms(array(
            'taxonomy' => 'translated_language',
            'hide_empty' => true,
            'orderby' => 'count',
            'order' => 'DESC'
        ));

        if (!is_wp_error($translated_langs) && !empty($translated_langs)) {
            foreach ($translated_langs as $term) {
                $xml .= "\t<url>\n";
                $xml .= "\t\t<loc>" . esc_url(get_term_link($term)) . "</loc>\n";
                $xml .= "\t\t<lastmod>" . date('c') . "</lastmod>\n";
                $xml .= "\t\t<changefreq>weekly</changefreq>\n";
                $xml .= "\t\t<priority>0.7</priority>\n";
                $xml .= "\t</url>\n";
            }
        }

        // Get original language terms (e.g., /original/english/)
        $original_langs = get_terms(array(
            'taxonomy' => 'original_language',
            'hide_empty' => true,
            'orderby' => 'count',
            'order' => 'DESC'
        ));

        if (!is_wp_error($original_langs) && !empty($original_langs)) {
            foreach ($original_langs as $term) {
                $xml .= "\t<url>\n";
                $xml .= "\t\t<loc>" . esc_url(get_term_link($term)) . "</loc>\n";
                $xml .= "\t\t<lastmod>" . date('c') . "</lastmod>\n";
                $xml .= "\t\t<changefreq>weekly</changefreq>\n";
                $xml .= "\t\t<priority>0.7</priority>\n";
                $xml .= "\t</url>\n";
            }
        }

        $xml .= '</urlset>';

        return $xml;
    }
    
    /**
     * Şarkıcı sitemap oluştur
     */
    private function generate_singers_sitemap() {
        $singers = get_terms(array(
            'taxonomy' => 'singer',
            'hide_empty' => true,
            'orderby' => 'count',
            'order' => 'DESC'
        ));
        
        if (is_wp_error($singers) || empty($singers)) {
            return $this->get_empty_sitemap();
        }
        
        $xml = $this->get_sitemap_header();
        
        foreach ($singers as $singer) {
            $xml .= "\t<url>\n";
            $xml .= "\t\t<loc>" . esc_url(get_term_link($singer)) . "</loc>\n";
            $xml .= "\t\t<lastmod>" . date('c') . "</lastmod>\n";
            $xml .= "\t\t<changefreq>weekly</changefreq>\n";
            $xml .= "\t\t<priority>0.8</priority>\n";
            $xml .= "\t</url>\n";
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }
    
    /**
     * Albüm sitemap oluştur
     */
    private function generate_albums_sitemap() {
        $albums = get_terms(array(
            'taxonomy' => 'album',
            'hide_empty' => true,
            'orderby' => 'count',
            'order' => 'DESC'
        ));
        
        if (is_wp_error($albums) || empty($albums)) {
            return $this->get_empty_sitemap();
        }
        
        $xml = $this->get_sitemap_header();
        
        foreach ($albums as $album) {
            $xml .= "\t<url>\n";
            $xml .= "\t\t<loc>" . esc_url(get_term_link($album)) . "</loc>\n";
            $xml .= "\t\t<lastmod>" . date('c') . "</lastmod>\n";
            $xml .= "\t\t<changefreq>monthly</changefreq>\n";
            $xml .= "\t\t<priority>0.7</priority>\n";
            $xml .= "\t</url>\n";
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }
    
    /**
 * Songwriter sitemap oluştur
 */
private function generate_songwriters_sitemap() {
    $songwriters = get_terms(array(
        'taxonomy'   => 'songwriter',
        'hide_empty' => true,
        'orderby'    => 'count',
        'order'      => 'DESC'
    ));

    if (is_wp_error($songwriters) || empty($songwriters)) {
        return $this->get_empty_sitemap();
    }

    $xml = $this->get_sitemap_header();

    foreach ($songwriters as $songwriter) {
        $xml .= "\t<url>\n";
        $xml .= "\t\t<loc>" . esc_url(get_term_link($songwriter)) . "</loc>\n";
        $xml .= "\t\t<lastmod>" . date('c') . "</lastmod>\n";
        $xml .= "\t\t<changefreq>weekly</changefreq>\n";
        $xml .= "\t\t<priority>0.8</priority>\n";
        $xml .= "\t</url>\n";
    }

    $xml .= '</urlset>';
    return $xml;
}

/**
 * Producer sitemap oluştur
 */
private function generate_producers_sitemap() {
    $producers = get_terms(array(
        'taxonomy'   => 'producer',
        'hide_empty' => true,
        'orderby'    => 'count',
        'order'      => 'DESC'
    ));

    if (is_wp_error($producers) || empty($producers)) {
        return $this->get_empty_sitemap();
    }

    $xml = $this->get_sitemap_header();

    foreach ($producers as $producer) {
        $xml .= "\t<url>\n";
        $xml .= "\t\t<loc>" . esc_url(get_term_link($producer)) . "</loc>\n";
        $xml .= "\t\t<lastmod>" . date('c') . "</lastmod>\n";
        $xml .= "\t\t<changefreq>monthly</changefreq>\n";
        $xml .= "\t\t<priority>0.7</priority>\n";
        $xml .= "\t</url>\n";
    }

    $xml .= '</urlset>';
    return $xml;
}

    
    /**
     * Post'un mevcut dillerini al
     */
    private function get_post_languages($post_id) {
        $available_languages = get_post_meta($post_id, '_available_languages', true);

        if (empty($available_languages) || !is_array($available_languages)) {
            $available_languages = $this->detect_languages_from_content($post_id);

            if (!empty($available_languages)) {
                update_post_meta($post_id, '_available_languages', $available_languages);
            }
        }

        // Convert ISO codes to full names if needed
        $code_to_name = array(
            'en' => 'english',
            'es' => 'spanish',
            'tr' => 'turkish',
            'de' => 'german',
            'ar' => 'arabic',
            'fr' => 'french',
            'it' => 'italian',
            'pt' => 'portuguese',
            'ru' => 'russian',
            'ja' => 'japanese',
            'ko' => 'korean',
            'hu' => 'hungarian',
        );

        $converted_languages = array();
        foreach ((array)$available_languages as $lang) {
            $lang_lower = strtolower(trim($lang));

            // If it's an ISO code (2 chars), convert to full name
            if (isset($code_to_name[$lang_lower])) {
                $converted_languages[] = $code_to_name[$lang_lower];
            }
            // If it's already a full name, keep it
            elseif (array_key_exists($lang, $this->language_settings['language_map'])) {
                $converted_languages[] = $lang;
            }
        }

        $available_languages = array_unique($converted_languages);

        // En azından English ekle
        if (empty($available_languages)) {
            $available_languages = array('english');
        } elseif (!in_array('english', $available_languages)) {
            array_unshift($available_languages, 'english');
        }

        return $available_languages;
    }
    
    /**
     * İçerikten dilleri tespit et (Gutenberg blocks + legacy tables)
     */
    private function detect_languages_from_content($post_id) {
        $content = get_post_field('post_content', $post_id);
        $detected_languages = array();

        // First, try to detect from Gutenberg block (arcuras/lyrics-translations)
        if (strpos($content, 'wp:arcuras/lyrics-translations') !== false) {
            if (preg_match('/<!-- wp:arcuras\/lyrics-translations\s+(\{.*?\})\s*(\/)?-->/s', $content, $matches)) {
                $json_str = $matches[1];
                $block_attrs = json_decode($json_str, true);

                if ($block_attrs && isset($block_attrs['languages']) && is_array($block_attrs['languages'])) {
                    // Language code mapping: 'es' -> 'spanish', 'tr' -> 'turkish', 'en' -> 'english'
                    $code_to_name = array(
                        'en' => 'english',
                        'es' => 'spanish',
                        'tr' => 'turkish',
                        'de' => 'german',
                        'ar' => 'arabic',
                        'fr' => 'french',
                        'it' => 'italian',
                        'pt' => 'portuguese',
                        'ru' => 'russian',
                        'ja' => 'japanese',
                    );

                    foreach ($block_attrs['languages'] as $lang) {
                        if (isset($lang['code'])) {
                            $lang_code = strtolower($lang['code']);
                            if (isset($code_to_name[$lang_code])) {
                                $detected_languages[] = $code_to_name[$lang_code];
                            }
                        }
                    }
                }
            }
        }

        // Fallback: Legacy table detection
        if (empty($detected_languages) && strpos($content, '<table') !== false) {
            preg_match_all('/<th[^>]*>(.*?)<\/th>/i', $content, $headers);

            if (!empty($headers[1])) {
                $language_patterns = array(
                    'english' => array('english', 'ingilizce', 'en', 'eng', 'original'),
                    'spanish' => array('spanish', 'español', 'espanol', 'ispanyolca', 'es', 'spa'),
                    'turkish' => array('turkish', 'türkçe', 'turkce', 'tr', 'tur'),
                    'german' => array('german', 'deutsch', 'almanca', 'de', 'ger', 'deu'),
                    'arabic' => array('arabic', 'عربي', 'arapça', 'ar', 'ara'),
                    'french' => array('french', 'français', 'francais', 'fransızca', 'fr', 'fra'),
                    'italian' => array('italian', 'italiano', 'italyanca', 'it', 'ita'),
                    'portuguese' => array('portuguese', 'português', 'portugues', 'portekizce', 'pt', 'por'),
                    'russian' => array('russian', 'русский', 'rusça', 'ru', 'rus'),
                    'japanese' => array('japanese', '日本語', 'japonca', 'ja', 'jpn'),
                );

                foreach ($headers[1] as $header) {
                    $header = trim(strip_tags($header));
                    if (empty($header)) continue;

                    foreach ($language_patterns as $lang_key => $patterns) {
                        foreach ($patterns as $pattern) {
                            if (stripos($header, $pattern) !== false) {
                                if (!in_array($lang_key, $detected_languages)) {
                                    $detected_languages[] = $lang_key;
                                }
                                break 2;
                            }
                        }
                    }
                }
            }
        }

        // Default to english if nothing detected
        if (empty($detected_languages)) {
            $detected_languages[] = 'english';
        } elseif (!in_array('english', $detected_languages)) {
            array_unshift($detected_languages, 'english');
        }

        return $detected_languages;
    }
    
    /**
     * Sitemap header
     */
    private function get_sitemap_header() {
        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
               '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    }
    
    /**
     * Boş sitemap
     */
    private function get_empty_sitemap() {
        return $this->get_sitemap_header() . '</urlset>';
    }
    
    /**
     * Sitemap çıktısı ver
     */
    private function output_sitemap($content) {
        // Tüm output buffer'ları temizle
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Headers gönder
        if (!headers_sent()) {
            header_remove(); // Önceki header'ları temizle
            status_header(200);
            header('Content-Type: text/xml; charset=utf-8');
            header('X-Robots-Tag: noindex, follow');
        }
        
        echo $content;
        exit;
    }
    
    /**
     * Post'ların son değişiklik tarihini al
     */
    private function get_posts_lastmod() {
        global $wpdb;
        
        $lastmod = $wpdb->get_var(
            "SELECT post_modified_gmt FROM {$wpdb->posts} 
             WHERE post_status = 'publish' AND post_type = 'lyrics' 
             ORDER BY post_modified_gmt DESC LIMIT 1"
        );
        
        return $lastmod ? date('c', strtotime($lastmod)) : date('c');
    }
    
    /**
     * Sayfa'ların son değişiklik tarihini al
     */
    private function get_pages_lastmod() {
        global $wpdb;
        
        $lastmod = $wpdb->get_var(
            "SELECT post_modified_gmt FROM {$wpdb->posts} 
             WHERE post_status = 'publish' AND post_type = 'page' 
             ORDER BY post_modified_gmt DESC LIMIT 1"
        );
        
        return $lastmod ? date('c', strtotime($lastmod)) : date('c');
    }
    
    /**
     * Robots.txt'ye sitemap'leri ekle
     */
    public function add_sitemaps_to_robots($output, $public) {
        if ($public) {
            $output .= "\n# Gufte Theme Sitemaps\n";
            $output .= "Sitemap: " . home_url('/sitemap.xml') . "\n";
            // Multilingual sitemap removed - translations now in posts sitemap
            // $output .= "Sitemap: " . home_url('/gufte-sitemap-multilingual.xml') . "\n";
        }
        
        return $output;
    }
    
    /**
     * Sitemap cache'ini temizle
     */
    public function clear_sitemap_cache($post_id = null) {
        $cache_keys = array(
            'gufte_sitemap_index',
            'gufte_sitemap_posts',
            'gufte_sitemap_multilingual',
            'gufte_sitemap_pages',
            'gufte_sitemap_categories',
            'gufte_sitemap_singers',
            'gufte_sitemap_albums',
            'gufte_sitemap_songwriters',
            'gufte_sitemap_producers'
        );
        
        foreach ($cache_keys as $key) {
            wp_cache_delete($key, 'gufte_sitemaps');
        }
        
        // Arama motorlarını bilgilendir (5 dakika sonra)
        wp_schedule_single_event(time() + 300, 'gufte_ping_search_engines');
    }
    
    /**
     * Admin menü ekle
     */
    public function add_admin_menu() {
        add_management_page(
            'Gufte Sitemap Generator',
            'Sitemap Generator',
            'manage_options',
            'gufte-sitemap-generator',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Admin işlemlerini yönet
     */
    public function handle_admin_actions() {
        if (isset($_POST['clear_sitemap_cache']) && current_user_can('manage_options')) {
            check_admin_referer('gufte_sitemap_clear_cache');
            $this->clear_sitemap_cache();
            
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible">';
                echo '<p>Sitemap cache temizlendi ve yeniden oluşturuldu.</p>';
                echo '</div>';
            });
        }
        
        if (isset($_POST['update_all_languages']) && current_user_can('manage_options')) {
            check_admin_referer('gufte_sitemap_update_languages');
            $this->bulk_update_languages();
        }
    }
    
    /**
     * Toplu dil güncelleme
     */
    private function bulk_update_languages() {
        $posts = get_posts(array(
            'post_type' => 'lyrics', // Changed from 'post' to 'lyrics'
            'post_status' => 'publish',
            'numberposts' => -1
        ));
        
        $updated_count = 0;
        
        foreach ($posts as $post) {
            $detected_languages = $this->detect_languages_from_content($post->ID);
            $current_languages = get_post_meta($post->ID, '_available_languages', true);
            
            if (!empty($detected_languages) && $detected_languages !== $current_languages) {
                update_post_meta($post->ID, '_available_languages', $detected_languages);
                $updated_count++;
            }
        }
        
        $this->clear_sitemap_cache();
        
        add_action('admin_notices', function() use ($updated_count) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . sprintf('%d yazı için dil bilgileri güncellendi ve sitemap yeniden oluşturuldu.', $updated_count) . '</p>';
            echo '</div>';
        });
    }
    
    /**
     * Admin sayfa içeriği
     */
    public function admin_page() {
        // Count lyrics posts
        $base_posts_count = wp_count_posts('lyrics')->publish;

        // Count translation URLs (each post can have multiple translations)
        $translation_urls_count = 0;
        $posts = get_posts(array(
            'post_type' => 'lyrics',
            'post_status' => 'publish',
            'numberposts' => -1,
            'fields' => 'ids'
        ));

        foreach ($posts as $post_id) {
            $available_languages = $this->get_post_languages($post_id);
            // Count non-English languages (English is original, not a translation URL)
            foreach ($available_languages as $lang) {
                if ($lang !== 'english') {
                    $translation_urls_count++;
                }
            }
        }

        // Total URLs in sitemap = base posts + translation URLs
        $posts_count = $base_posts_count + $translation_urls_count;

        $pages_count = wp_count_posts('page')->publish;

        // Categories sitemap includes both translated_language and original_language taxonomies
        $translated_count = wp_count_terms(array('taxonomy' => 'translated_language', 'hide_empty' => true));
        $original_count = wp_count_terms(array('taxonomy' => 'original_language', 'hide_empty' => true));
        $categories_count = $translated_count + $original_count;

        $singers_count = wp_count_terms(array('taxonomy' => 'singer', 'hide_empty' => false));
        $albums_count = wp_count_terms(array('taxonomy' => 'album', 'hide_empty' => false));
        $songwriters_count = wp_count_terms(array('taxonomy' => 'songwriter', 'hide_empty' => false));
        $producers_count   = wp_count_terms(array('taxonomy' => 'producer',   'hide_empty' => false));
        
        include get_template_directory() . '/inc/admin/admin-sitemap-page.php';
    }
}

// Delayed sitemap update handler
add_action('gufte_delayed_sitemap_update', function($post_id) {
    // Sitemap generator instance'ını al ve güncelle
    if (class_exists('Gufte_Sitemap_Generator')) {
        $generator = new Gufte_Sitemap_Generator();
        $generator->trigger_sitemap_update($post_id, 'delayed_trigger');
    }
});

// Admin notice göster
add_action('admin_notices', function() {
    $reason = get_transient('gufte_sitemap_updated_notice');
    if ($reason && current_user_can('manage_options')) {
        echo '<div class="notice notice-info is-dismissible">';
        echo '<p><strong>Sitemap Updated:</strong> Automatically triggered due to ' . esc_html($reason) . '</p>';
        echo '</div>';
        delete_transient('gufte_sitemap_updated_notice');
    }
});

// Arama motorlarına ping gönder
add_action('gufte_ping_search_engines', function() {
    $sitemap_urls = array(
        home_url('/sitemap.xml'),
        home_url('/gufte-sitemap.xml')
        // Multilingual sitemap removed - translations now in posts sitemap
        // home_url('/gufte-sitemap-multilingual.xml')
    );
    
    foreach ($sitemap_urls as $sitemap_url) {
        // Google
        wp_remote_get('http://www.google.com/ping?sitemap=' . urlencode($sitemap_url), array('timeout' => 30));
        
        // Bing
        wp_remote_get('http://www.bing.com/ping?sitemap=' . urlencode($sitemap_url), array('timeout' => 30));
    }
});

// Sitemap Generator'ı başlat
new Gufte_Sitemap_Generator();
