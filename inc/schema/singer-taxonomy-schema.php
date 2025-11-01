<?php
/**
 * Singer Taxonomy Schema Generator
 * 
 * Dosya: /inc/schema/singer-taxonomy-schema.php
 * Kullanım: Singer (Sanatçı) taksonomi sayfaları için yapılandırılmış veri
 * 
 * @package Gufte
 */

// Güvenlik kontrolü
if (!defined('ABSPATH')) {
    exit;
}

class Gufte_Singer_Taxonomy_Schema {
    
    private $term;
    private $term_id;
    private $permalink;
    
    public function __construct($term = null) {
        $this->term = $term ?: get_queried_object();
        $this->term_id = $this->term->term_id;
        $this->permalink = $this->get_term_permalink();
    }
    
    /**
     * Ana schema oluştur
     */
    public function generate_schema() {
        $schema = [
            '@context' => 'https://schema.org',
            '@graph' => [
                $this->get_website_schema(),
                $this->get_webpage_schema(),
                $this->get_music_group_schema(),
                $this->get_breadcrumb_schema()
            ]
        ];
        
        return $schema;
    }
    
    /**
     * Website Schema
     */
    private function get_website_schema() {
        return [
            '@type' => 'WebSite',
            '@id' => home_url() . '#website',
            'name' => get_bloginfo('name'),
            'alternateName' => get_bloginfo('description'),
            'url' => home_url(),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => home_url('/?s={search_term_string}')
                ],
                'query-input' => 'required name=search_term_string'
            ]
        ];
    }
    
    /**
     * WebPage Schema
     */
    private function get_webpage_schema() {
        $title = $this->get_page_title();
        $description = $this->get_meta_description();
        
        $webpage = [
            '@type' => 'ProfilePage',
            '@id' => $this->permalink . '#webpage',
            'url' => $this->permalink,
            'name' => $title,
            'description' => $description,
            'inLanguage' => 'en',
            'dateModified' => current_time('c'),
            'isPartOf' => ['@id' => home_url() . '#website'],
            'breadcrumb' => ['@id' => $this->permalink . '#breadcrumb'],
            'mainEntity' => ['@id' => $this->permalink . '#musicgroup']
        ];
        
        // Sanatçı görseli varsa ekle
        $image_url = $this->get_singer_image_url();
        if ($image_url) {
            $webpage['primaryImageOfPage'] = [
                '@type' => 'ImageObject',
                'url' => $image_url
            ];
        }
        
        return $webpage;
    }
    
    /**
     * MusicGroup/Person Schema
     */
    private function get_music_group_schema() {
        $artist_type = $this->determine_artist_type();
        $description = $this->get_meta_description();
        
        $artist = [
            '@type' => $artist_type,
            '@id' => $this->permalink . '#musicgroup',
            'name' => $this->term->name,
            'url' => $this->permalink,
            'description' => $description,
            'genre' => 'Music'
        ];
        
        // Alternatif isim (gerçek ad)
        $real_name = get_term_meta($this->term_id, 'real_name', true);
        if (!empty($real_name)) {
            $artist['alternateName'] = $real_name;
        }
        
        // Görsel
        $image_url = $this->get_singer_image_url();
        if ($image_url) {
            $artist['image'] = [
                '@type' => 'ImageObject',
                'url' => $image_url,
                'caption' => $this->term->name . ' - Artist Image'
            ];
        }
        
        // Doğum/kuruluş bilgileri
        $birth_data = $this->get_birth_data();
        if ($birth_data) {
            if ($artist_type === 'Person') {
                if ($birth_data['place']) {
                    $artist['birthPlace'] = [
                        '@type' => 'Place',
                        'name' => $birth_data['place']
                    ];
                }
                if ($birth_data['date']) {
                    $artist['birthDate'] = $birth_data['date'];
                }
                if ($birth_data['death_date']) {
                    $artist['deathDate'] = $birth_data['death_date'];
                }
            } else {
                // MusicGroup için
                if ($birth_data['place']) {
                    $artist['foundingLocation'] = [
                        '@type' => 'Place',
                        'name' => $birth_data['place']
                    ];
                }
                if ($birth_data['date']) {
                    $artist['foundingDate'] = $birth_data['date'];
                }
                if ($birth_data['death_date']) {
                    $artist['dissolutionDate'] = $birth_data['death_date'];
                }
            }
        }
        
        // Platform linkleri
        $platform_links = $this->get_platform_links();
        if (!empty($platform_links)) {
            $artist['sameAs'] = $platform_links;
        }
        
        // Albümler
        $albums = $this->get_artist_albums();
        if (!empty($albums)) {
            $artist['album'] = $albums;
        }
        
        // Şarkı sayısı
        if ($this->term->count > 0) {
            $artist['numberOfTracks'] = (int) $this->term->count;
        }
        
        return $artist;
    }
    
    /**
     * Breadcrumb Schema
     */
    private function get_breadcrumb_schema() {
        $breadcrumbs = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => home_url()
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'Artists',
                'item' => home_url('/singers/')
            ],
            [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $this->term->name,
                'item' => $this->permalink
            ]
        ];
        
        return [
            '@type' => 'BreadcrumbList',
            '@id' => $this->permalink . '#breadcrumb',
            'itemListElement' => $breadcrumbs
        ];
    }
    
    /**
     * Yardımcı fonksiyonlar
     */
    private function get_term_permalink() {
        $term_link = get_term_link($this->term);
        return !is_wp_error($term_link) ? $term_link : home_url();
    }
    
    private function determine_artist_type() {
        $name_lower = strtolower($this->term->name);
        
        $group_indicators = [
            'band', 'group', 'collective', 'crew', 'ensemble', 'orchestra',
            'the ', ' and ', ' & ', ' + ', ' feat.', ' ft.', ' featuring'
        ];
        
        foreach ($group_indicators as $indicator) {
            if (strpos($name_lower, $indicator) !== false) {
                return 'MusicGroup';
            }
        }
        
        if (strpos($name_lower, ',') !== false) {
            return 'MusicGroup';
        }
        
        return 'Person';
    }
    
    private function get_page_title() {
        return $this->term->name . ' - Artist Profile | ' . get_bloginfo('name');
    }
    
    private function get_meta_description() {
        if (!empty($this->term->description)) {
            return wp_strip_all_tags($this->term->description);
        }
        
        $song_count = $this->term->count;
        $count_text = $song_count > 0 ? " with {$song_count} songs" : '';
        
        return "Discover lyrics by {$this->term->name}{$count_text}. Read, translate and enjoy song lyrics from this artist.";
    }
    
    private function get_singer_image_url() {
        if (function_exists('gufte_get_singer_image')) {
            $img_html = gufte_get_singer_image($this->term_id, 'full');
            if ($img_html && preg_match('/src="([^"]+)"/', $img_html, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
    
    private function get_birth_data() {
        $birth_place = get_term_meta($this->term_id, 'birth_place', true);
        $birth_country = get_term_meta($this->term_id, 'birth_country', true);
        $birth_date = get_term_meta($this->term_id, 'birth_date', true);
        $death_date = get_term_meta($this->term_id, 'death_date', true);
        
        $place = '';
        if (!empty($birth_place)) {
            $place = $birth_place;
        }
        if (!empty($birth_country)) {
            $place .= ($place ? ', ' : '') . $birth_country;
        }
        
        // Lifespan'den yıl çıkarma
        if (function_exists('gufte_get_singer_lifespan')) {
            $lifespan = gufte_get_singer_lifespan($this->term_id);
            if (!empty($lifespan) && preg_match('/(\d{4})\s*[-–]\s*(\d{4}|…|\.\.|-)?/u', wp_strip_all_tags($lifespan), $matches)) {
                if (!empty($matches[1]) && empty($birth_date)) {
                    $birth_date = $matches[1];
                }
                if (!empty($matches[2]) && is_numeric($matches[2]) && empty($death_date)) {
                    $death_date = $matches[2];
                }
            }
        }
        
        return [
            'place' => $place,
            'date' => $birth_date,
            'death_date' => $death_date
        ];
    }
    
    private function get_platform_links() {
        $links = [];
        
        if (function_exists('gufte_get_singer_platform_links')) {
            $platform_data = gufte_get_singer_platform_links($this->term_id);
            
            foreach ($platform_data as $platform => $url) {
                if (!empty($url)) {
                    $links[] = $url;
                }
            }
        }
        
        // Sosyal medya linkleri de ekle
        $social_platforms = [
            'instagram_url',
            'twitter_url',
            'facebook_url',
            'tiktok_url',
            'youtube_channel_url',
            'official_website_url'
        ];
        
        foreach ($social_platforms as $platform) {
            $url = get_term_meta($this->term_id, $platform, true);
            if (!empty($url)) {
                $links[] = $url;
            }
        }
        
        return array_filter($links);
    }
    
    private function get_artist_albums() {
        if (!function_exists('gufte_get_singer_albums')) {
            return [];
        }
        
        $albums = gufte_get_singer_albums($this->term_id);
        $album_schemas = [];
        
        foreach (array_slice($albums, 0, 10) as $album) { // İlk 10 albüm
            $album_year = get_term_meta($album->term_id, 'album_year', true);
            $album_release_date = get_term_meta($album->term_id, 'album_release_date', true);
            
            $album_schema = [
                '@type' => 'MusicAlbum',
                'name' => $album->name,
                'url' => get_term_link($album)
            ];
            
            if (!empty($album_release_date)) {
                $album_schema['datePublished'] = $album_release_date;
            } elseif (!empty($album_year)) {
                $album_schema['datePublished'] = $album_year . '-01-01';
            }
            
            // Albümdeki şarkı sayısı
            $song_count = $this->get_album_song_count($album->term_id);
            if ($song_count > 0) {
                $album_schema['numTracks'] = $song_count;
            }
            
            $album_schemas[] = $album_schema;
        }
        
        return $album_schemas;
    }
    
    private function get_album_song_count($album_id) {
        $query = new WP_Query([
            'post_type' => 'post',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'tax_query' => [
                'relation' => 'AND',
                [
                    'taxonomy' => 'album',
                    'field' => 'term_id',
                    'terms' => $album_id,
                ],
                [
                    'taxonomy' => 'singer',
                    'field' => 'term_id',
                    'terms' => $this->term_id,
                ],
            ],
            'no_found_rows' => true,
        ]);
        
        return $query->post_count;
    }
}

/**
 * Singer taxonomy schema'yı render et
 */
function gufte_render_singer_taxonomy_schema() {
    if (!is_tax('singer')) {
        return;
    }
    
    $schema_generator = new Gufte_Singer_Taxonomy_Schema();
    $schema = $schema_generator->generate_schema();
    
    echo '<script type="application/ld+json">' . "\n";
    echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    echo "\n" . '</script>' . "\n";
}

// Hook ekle
add_action('wp_head', 'gufte_render_singer_taxonomy_schema', 5);
?>