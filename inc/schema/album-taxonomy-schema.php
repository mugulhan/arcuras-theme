<?php
/**
 * Album Taxonomy Schema Generator
 * 
 * Dosya: /inc/schema/album-taxonomy-schema.php
 * Kullanım: Album taksonomi sayfaları için yapılandırılmış veri
 * 
 * @package Gufte
 */

// Güvenlik kontrolü
if (!defined('ABSPATH')) {
    exit;
}

class Gufte_Album_Taxonomy_Schema {
    
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
                $this->get_music_album_schema(),
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
            '@type' => 'CollectionPage',
            '@id' => $this->permalink . '#webpage',
            'url' => $this->permalink,
            'name' => $title,
            'description' => $description,
            'inLanguage' => 'en',
            'dateModified' => current_time('c'),
            'isPartOf' => ['@id' => home_url() . '#website'],
            'breadcrumb' => ['@id' => $this->permalink . '#breadcrumb'],
            'mainEntity' => ['@id' => $this->permalink . '#musicalbum']
        ];
        
        // Album kapak görseli varsa ekle
        $image_url = $this->get_album_cover_url();
        if ($image_url) {
            $webpage['primaryImageOfPage'] = [
                '@type' => 'ImageObject',
                'url' => $image_url
            ];
        }
        
        return $webpage;
    }
    
    /**
     * MusicAlbum Schema
     */
    private function get_music_album_schema() {
        $description = $this->get_meta_description();
        $album_year = get_term_meta($this->term_id, 'album_year', true);
        $album_release_date = get_term_meta($this->term_id, 'album_release_date', true);
        
        $album = [
            '@type' => 'MusicAlbum',
            '@id' => $this->permalink . '#musicalbum',
            'name' => $this->term->name,
            'url' => $this->permalink,
            'description' => $description,
            'genre' => 'Music'
        ];
        
        // Yayın tarihi
        if (!empty($album_release_date)) {
            $album['datePublished'] = $album_release_date;
        } elseif (!empty($album_year)) {
            $album['datePublished'] = $album_year . '-01-01';
        }
        
        // Şarkı sayısı
        if ($this->term->count > 0) {
            $album['numTracks'] = (int) $this->term->count;
        }
        
        // Album kapak görseli
        $image_url = $this->get_album_cover_url();
        if ($image_url) {
            $album['image'] = [
                '@type' => 'ImageObject',
                'url' => $image_url,
                'caption' => $this->term->name . ' - Album Cover'
            ];
        }
        
        // Sanatçılar
        $artists = $this->get_album_artists();
        if (!empty($artists)) {
            $album['byArtist'] = count($artists) === 1 ? $artists[0] : $artists;
        }
        
        // Şarkı listesi (ilk 20 şarkı)
        $tracks = $this->get_album_tracks();
        if (!empty($tracks)) {
            $album['track'] = $tracks;
        }
        
        // Record label (eğer varsa)
        $record_label = get_term_meta($this->term_id, 'record_label', true);
        if (!empty($record_label)) {
            $album['recordLabel'] = [
                '@type' => 'Organization',
                'name' => $record_label
            ];
        }
        
        return $album;
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
                'name' => 'Albums',
                'item' => home_url('/albums/')
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
    
    private function get_page_title() {
        $album_year = get_term_meta($this->term_id, 'album_year', true);
        $year_text = $album_year ? " ({$album_year})" : '';
        return $this->term->name . $year_text . ' - Album | ' . get_bloginfo('name');
    }
    
    private function get_meta_description() {
        if (!empty($this->term->description)) {
            return wp_strip_all_tags($this->term->description);
        }
        
        $track_count = $this->term->count;
        $count_text = $track_count > 0 ? " with {$track_count} tracks" : '';
        $album_year = get_term_meta($this->term_id, 'album_year', true);
        $year_text = $album_year ? " released in {$album_year}" : '';
        
        return "Discover all songs from the album {$this->term->name}{$year_text}{$count_text}. Read lyrics and translations.";
    }
    
    private function get_album_cover_url() {
        // Album kapak görseli
        $album_cover_id = get_term_meta($this->term_id, 'album_cover_id', true);
        if ($album_cover_id) {
            return wp_get_attachment_image_url($album_cover_id, 'large');
        }
        
        // Albümdeki ilk şarkının görseli
        $first_post = get_posts([
            'post_type' => 'post',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'tax_query' => [
                [
                    'taxonomy' => 'album',
                    'field' => 'term_id',
                    'terms' => $this->term_id,
                ],
            ],
            'meta_query' => [
                [
                    'key' => '_thumbnail_id',
                    'compare' => 'EXISTS',
                ],
            ],
            'no_found_rows' => true,
        ]);
        
        if (!empty($first_post)) {
            return get_the_post_thumbnail_url($first_post[0], 'large');
        }
        
        return null;
    }
    
    private function get_album_artists() {
        if (!function_exists('gufte_get_album_singers')) {
            return [];
        }
        
        $singers = gufte_get_album_singers($this->term_id);
        $artist_schemas = [];
        
        foreach ($singers as $singer) {
            $artist_type = $this->determine_artist_type($singer->name);
            
            $artist = [
                '@type' => $artist_type,
                'name' => $singer->name,
                'url' => get_term_link($singer)
            ];
            
            // Gerçek ad (varsa)
            $real_name = get_term_meta($singer->term_id, 'real_name', true);
            if (!empty($real_name)) {
                $artist['alternateName'] = $real_name;
            }
            
            // Platform linkleri
            $platform_links = $this->get_singer_platform_links($singer->term_id);
            if (!empty($platform_links)) {
                $artist['sameAs'] = $platform_links;
            }
            
            $artist_schemas[] = $artist;
        }
        
        return $artist_schemas;
    }
    
    private function get_album_tracks() {
        $tracks_query = new WP_Query([
            'post_type' => 'post',
            'posts_per_page' => 20, // İlk 20 şarkı
            'orderby' => 'date',
            'order' => 'ASC',
            'tax_query' => [
                [
                    'taxonomy' => 'album',
                    'field' => 'term_id',
                    'terms' => $this->term_id,
                ],
            ],
            'no_found_rows' => true,
        ]);
        
        $track_schemas = [];
        $position = 1;
        
        while ($tracks_query->have_posts()) {
            $tracks_query->the_post();
            
            $track = [
                '@type' => 'MusicRecording',
                'name' => get_the_title(),
                'url' => get_permalink(),
                'position' => $position++
            ];
            
            // Şarkı süresi (varsa)
            $duration = get_post_meta(get_the_ID(), '_song_duration', true);
            if (!empty($duration)) {
                $track['duration'] = 'PT' . $duration . 'S';
            }
            
            // Şarkıcılar
            $song_singers = get_the_terms(get_the_ID(), 'singer');
            if ($song_singers && !is_wp_error($song_singers)) {
                $track_artists = [];
                foreach ($song_singers as $singer) {
                    $track_artists[] = [
                        '@type' => $this->determine_artist_type($singer->name),
                        'name' => $singer->name,
                        'url' => get_term_link($singer)
                    ];
                }
                $track['byArtist'] = count($track_artists) === 1 ? $track_artists[0] : $track_artists;
            }
            
            $track_schemas[] = $track;
        }
        
        wp_reset_postdata();
        return $track_schemas;
    }
    
    private function determine_artist_type($artist_name) {
        $name_lower = strtolower($artist_name);
        
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
    
    private function get_singer_platform_links($singer_id) {
        $links = [];
        
        // Müzik platformları
        $platforms = [
            'spotify_artist_url',
            'apple_music_artist_url',
            'youtube_music_artist_url',
            'deezer_artist_url',
            'soundcloud_artist_url'
        ];
        
        foreach ($platforms as $platform) {
            $url = get_term_meta($singer_id, $platform, true);
            if (!empty($url)) {
                $links[] = $url;
            }
        }
        
        return $links;
    }
}

/**
 * Album taxonomy schema'yı render et
 */
function gufte_render_album_taxonomy_schema() {
    if (!is_tax('album')) {
        return;
    }
    
    $schema_generator = new Gufte_Album_Taxonomy_Schema();
    $schema = $schema_generator->generate_schema();
    
    echo '<script type="application/ld+json">' . "\n";
    echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    echo "\n" . '</script>' . "\n";
}

// Hook ekle
add_action('wp_head', 'gufte_render_album_taxonomy_schema', 5);
?>