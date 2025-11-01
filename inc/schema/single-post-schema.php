<?php
/**
 * Single Post Schema Generator
 * 
 * Dosya: /inc/schema/single-post-schema.php
 * Kullanım: Tekil şarkı sözü sayfaları için yapılandırılmış veri
 * 
 * @package Gufte
 */

// Güvenlik kontrolü
if (!defined('ABSPATH')) {
    exit;
}

class Gufte_Single_Post_Schema {
    
    private $post_id;
    private $post;
    private $singers;
    private $albums;
    private $categories;
    private $available_languages = [];
    
    public function __construct($post_id = null) {
        $this->post_id = $post_id ?: get_the_ID();
        $this->post = get_post($this->post_id);
        $this->load_taxonomy_data();
        $this->load_available_languages();
    }
    
    /**
     * Taksonomi verilerini yükle
     */
    private function load_taxonomy_data() {
        $this->singers = get_the_terms($this->post_id, 'singer');
        $this->albums = get_the_terms($this->post_id, 'album');
        $this->categories = get_the_category($this->post_id);
        
        // WP_Error kontrolü
        if (is_wp_error($this->singers)) $this->singers = false;
        if (is_wp_error($this->albums)) $this->albums = false;
        if (is_wp_error($this->categories)) $this->categories = false;
    }
    
    /**
     * Mevcut çeviri dillerini yükle
     */
    private function load_available_languages() {
        $available = get_post_meta($this->post_id, '_available_languages', true);
        
        if (!is_array($available)) {
            $available = [];
        }
        
        $this->available_languages = array_map('sanitize_text_field', $available);
    }
    
    /**
     * Ana schema oluştur
     */
    public function generate_schema() {
        $current_lang = $this->get_current_language();
        
        $schema = [
            '@context' => 'https://schema.org',
            '@graph' => [
                $this->get_website_schema(),
                $this->get_webpage_schema($current_lang),
                $this->get_song_schema($current_lang),
                $this->get_breadcrumb_schema(),
                $this->get_faq_schema()
            ]
        ];
        
        // Awards varsa ekle
        $awards_schema = $this->get_awards_schema();
        if ($awards_schema) {
            $schema['@graph'][] = $awards_schema;
        }
        
        return $schema;
    }
    
    /**
     * Mevcut dili belirle
     */
    private function get_current_language() {
        if (isset($_GET['lang'])) {
            $lang = sanitize_text_field(wp_unslash($_GET['lang']));
            
            if ($this->is_valid_language($lang) && $this->is_language_available($lang)) {
                return $lang;
            }
        }
        return 'english';
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
    private function get_webpage_schema($current_lang) {
        $url = $this->get_canonical_url($current_lang);
        $title = $this->get_page_title($current_lang);
        $description = $this->get_meta_description($current_lang);
        
        $webpage = [
            '@type' => 'WebPage',
            '@id' => $url . '#webpage',
            'url' => $url,
            'name' => $title,
            'description' => $description,
            'inLanguage' => $this->get_language_iso_code($current_lang),
            'datePublished' => get_the_date('c', $this->post_id),
            'dateModified' => get_the_modified_date('c', $this->post_id),
            'isPartOf' => ['@id' => home_url() . '#website'],
            'breadcrumb' => ['@id' => $url . '#breadcrumb'],
            'mainEntity' => ['@id' => $url . '#song']
        ];
        
        // Resim varsa ekle
        if (has_post_thumbnail($this->post_id)) {
            $webpage['primaryImageOfPage'] = [
                '@type' => 'ImageObject',
                'url' => get_the_post_thumbnail_url($this->post_id, 'large')
            ];
        }
        
        return $webpage;
    }
    
    /**
     * Song/MusicRecording Schema
     */
    private function get_song_schema($current_lang) {
        $url = $this->get_canonical_url($current_lang);
        $song_title = get_the_title($this->post_id);
        $description = $this->get_meta_description($current_lang);
        
        $song = [
            '@type' => 'MusicRecording',
            '@id' => $url . '#song',
            'name' => $song_title,
            'url' => $url,
            'description' => $description,
            'inLanguage' => $this->get_language_iso_code($current_lang),
            'datePublished' => get_the_date('c', $this->post_id),
            'dateModified' => get_the_modified_date('c', $this->post_id)
        ];
        
        // Sanatçı bilgileri
        $artists_schema = $this->get_artists_schema();
        if (!empty($artists_schema)) {
            if (count($artists_schema) === 1) {
                $song['byArtist'] = $artists_schema[0];
                $song['composer'] = $artists_schema[0];
                $song['lyricist'] = $artists_schema[0];
            } else {
                $song['byArtist'] = $artists_schema;
                $song['composer'] = $artists_schema;
                $song['lyricist'] = $artists_schema;
            }
        }
        
        // Albüm bilgisi
        $album_schema = $this->get_album_schema();
        if ($album_schema) {
            $song['inAlbum'] = $album_schema;
        }
        
        // Genre bilgileri
        $genres = $this->get_genres();
        if (!empty($genres)) {
            $song['genre'] = $genres;
        }
        
        // Görsel
        if (has_post_thumbnail($this->post_id)) {
            $thumbnail_url = get_the_post_thumbnail_url($this->post_id, 'large');
            $song['image'] = [
                '@type' => 'ImageObject',
                'url' => $thumbnail_url,
                'caption' => $this->get_image_alt_text()
            ];
            $song['thumbnailUrl'] = $thumbnail_url;
        }
        
        // Platform linkleri
        $platform_links = $this->get_platform_links();
        if (!empty($platform_links)) {
            $song['sameAs'] = $platform_links;
            
            // Apple Music varsa dinleme aksiyonu ekle
            $apple_music_url = get_post_meta($this->post_id, 'apple_music_url', true);
            if (!empty($apple_music_url)) {
                $song['potentialAction'] = [
                    '@type' => 'ListenAction',
                    'target' => [
                        '@type' => 'EntryPoint',
                        'urlTemplate' => $apple_music_url,
                        'actionPlatform' => ['https://schema.org/DesktopWebPlatform', 'https://schema.org/MobileWebPlatform']
                    ]
                ];
            }
        }
        
        // Release date
        $release_date = get_post_meta($this->post_id, '_release_date', true);
        if (!empty($release_date)) {
            $song['datePublished'] = $release_date;
        }
        
        // Müzik video
        $video_url = get_post_meta($this->post_id, 'music_video_url', true);
        if (!empty($video_url)) {
            $song['video'] = [
                '@type' => 'VideoObject',
                'url' => $video_url,
                'name' => $song_title . ' - Music Video',
                'description' => 'Official music video for ' . $song_title
            ];
        }
        
        // ISRC kodu (varsa)
        $isrc = get_post_meta($this->post_id, '_isrc_code', true);
        if (!empty($isrc)) {
            $song['isrcCode'] = $isrc;
        }
        
        // Süre bilgisi (varsa)
        $duration = get_post_meta($this->post_id, '_song_duration', true);
        if (!empty($duration)) {
            $song['duration'] = 'PT' . $duration . 'S'; // ISO 8601 format
        }
        
        return $song;
    }
    
    /**
     * Sanatçı schema'larını oluştur
     */
    private function get_artists_schema() {
        if (!$this->singers) {
            return [];
        }
        
        $artists = [];
        foreach ($this->singers as $singer) {
            $artist_type = $this->determine_artist_type($singer->name);
            $real_name = get_term_meta($singer->term_id, 'real_name', true);
            
            $artist = [
                '@type' => $artist_type,
                'name' => $singer->name,
                'url' => get_term_link($singer)
            ];
            
            if (!empty($real_name)) {
                $artist['alternateName'] = $real_name;
            }
            
            // Person tipinde ise doğum bilgileri
            if ($artist_type === 'Person') {
                $birth_date = get_term_meta($singer->term_id, 'birth_date', true);
                if (!empty($birth_date)) {
                    $artist['birthDate'] = $birth_date;
                }
                
                $birth_place = get_term_meta($singer->term_id, 'birth_place', true);
                $birth_country = get_term_meta($singer->term_id, 'birth_country', true);
                
                if (!empty($birth_place) || !empty($birth_country)) {
                    $birth_location = ['@type' => 'Place'];
                    if (!empty($birth_place) || !empty($birth_country)) {
                        $address = ['@type' => 'PostalAddress'];
                        if (!empty($birth_place)) $address['addressLocality'] = $birth_place;
                        if (!empty($birth_country)) $address['addressCountry'] = $birth_country;
                        $birth_location['address'] = $address;
                    }
                    $artist['birthPlace'] = $birth_location;
                }
            }
            
            // Platform ve sosyal medya linkleri
            $singer_links = $this->get_singer_external_links($singer->term_id);
            if (!empty($singer_links)) {
                $artist['sameAs'] = $singer_links;
            }
            
            $artists[] = $artist;
        }
        
        return $artists;
    }
    
    /**
     * Albüm schema'sını oluştur
     */
    private function get_album_schema() {
        if (!$this->albums) {
            return null;
        }
        
        $album = reset($this->albums);
        $album_year = get_term_meta($album->term_id, 'album_year', true);
        $album_release_date = get_term_meta($album->term_id, 'album_release_date', true);
        $track_count = get_term_meta($album->term_id, 'album_track_count', true);
        
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
        
        if (!empty($track_count)) {
            $album_schema['numTracks'] = (int) $track_count;
        }
        
        // Albüm sanatçıları
        if ($this->singers) {
            $artists_schema = $this->get_artists_schema();
            if (!empty($artists_schema)) {
                $album_schema['byArtist'] = count($artists_schema) === 1 ? $artists_schema[0] : $artists_schema;
            }
        }
        
        return $album_schema;
    }
    
    /**
     * Breadcrumb Schema
     */
    private function get_breadcrumb_schema() {
        $url = get_permalink($this->post_id);
        $breadcrumbs = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => get_bloginfo('name'),
                'item' => home_url()
            ]
        ];
        
        // Kategori ekle
        if ($this->categories) {
            $category = reset($this->categories);
            $breadcrumbs[] = [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => $category->name,
                'item' => get_category_link($category->term_id)
            ];
        }
        
        // Şarkıcı ekle
        if ($this->singers) {
            $singer = reset($this->singers);
            $position = count($breadcrumbs) + 1;
            $breadcrumbs[] = [
                '@type' => 'ListItem',
                'position' => $position,
                'name' => $singer->name,
                'item' => get_term_link($singer)
            ];
        }
        
        // Mevcut sayfa
        $breadcrumbs[] = [
            '@type' => 'ListItem',
            'position' => count($breadcrumbs) + 1,
            'name' => get_the_title($this->post_id),
            'item' => $url
        ];
        
        return [
            '@type' => 'BreadcrumbList',
            '@id' => $url . '#breadcrumb',
            'itemListElement' => $breadcrumbs
        ];
    }
    
    /**
     * FAQ Schema
     */
    private function get_faq_schema() {
        $song_title = get_the_title($this->post_id);
        $singer_name = $this->singers ? $this->singers[0]->name : '';
        $album_name = $this->albums ? $this->albums[0]->name : '';
        $genres = $this->get_genres();
        
        $faq_items = [
            [
                'question' => "Who is the singer of {$song_title}?",
                'answer' => !empty($singer_name) ? $singer_name : 'Information not available.'
            ],
            [
                'question' => "What is the original language of {$song_title}?",
                'answer' => 'English'
            ]
        ];
        
        $release_date = get_post_meta($this->post_id, '_release_date', true);
        if (!empty($release_date)) {
            $timestamp = strtotime($release_date);
            if ($timestamp) {
                $faq_items[] = [
                    'question' => "When was {$song_title} released?",
                    'answer' => date_i18n(get_option('date_format'), $timestamp)
                ];
            }
        }
        
        if (!empty($album_name)) {
            $album_year = get_term_meta($this->albums[0]->term_id, 'album_year', true);
            $album_text = $album_name;
            if ($album_year) {
                $album_text .= " ({$album_year})";
            }
            
            $faq_items[] = [
                'question' => "Which album is {$song_title} from?",
                'answer' => $album_text
            ];
        }
        
        if (!empty($genres)) {
            $faq_items[] = [
                'question' => "What genre is {$song_title}?",
                'answer' => implode(' / ', $genres)
            ];
        }
        
        // Platform bilgisi
        $apple_music_url = get_post_meta($this->post_id, 'apple_music_url', true);
        $spotify_url = get_post_meta($this->post_id, 'spotify_url', true);
        
        if (!empty($apple_music_url) || !empty($spotify_url)) {
            $platforms = [];
            if (!empty($apple_music_url)) $platforms[] = 'Apple Music';
            if (!empty($spotify_url)) $platforms[] = 'Spotify';
            
            $faq_items[] = [
                'question' => "Where can I listen to {$song_title}?",
                'answer' => 'You can listen on ' . implode(', ', $platforms) . ' and other streaming platforms.'
            ];
        }
        
        $faq_items[] = [
            'question' => "Where can I find the complete lyrics of {$song_title}?",
            'answer' => 'The lyrics and translations are available on this page.'
        ];
        
        $main_entity = [];
        foreach ($faq_items as $item) {
            $main_entity[] = [
                '@type' => 'Question',
                'name' => $item['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $item['answer']
                ]
            ];
        }
        
        return [
            '@type' => 'FAQPage',
            '@id' => get_permalink($this->post_id) . '#faq',
            'mainEntity' => $main_entity,
            'about' => $this->build_faq_about_schema($song_title, $singer_name)
        ];
    }
    
    /**
     * Awards Schema (varsa)
     */
    private function get_awards_schema() {
        if (!function_exists('gufte_get_post_awards')) {
            return null;
        }
        
        $awards = gufte_get_post_awards($this->post_id);
        if (empty($awards)) {
            return null;
        }
        
        $award_items = [];
        foreach ($awards as $award) {
            $award_item = [
                '@type' => 'Award',
                'name' => $award['name']
            ];
            
            if (!empty($award['description'])) {
                $award_item['description'] = $award['description'];
            }
            
            if (!empty($award['date'])) {
                $award_item['dateReceived'] = $award['date'];
            }
            
            $award_items[] = $award_item;
        }
        
        return [
            '@type' => 'ItemList',
            '@id' => get_permalink($this->post_id) . '#awards',
            'name' => 'Awards and Nominations',
            'itemListElement' => $award_items
        ];
    }
    
    /**
     * Yardımcı fonksiyonlar
     */
    private function get_supported_languages() {
        return [
            'english' => 'en',
            'spanish' => 'es',
            'turkish' => 'tr',
            'german' => 'de',
            'arabic' => 'ar',
            'french' => 'fr',
            'italian' => 'it',
            'portuguese' => 'pt',
            'russian' => 'ru',
            'japanese' => 'ja'
        ];
    }
    
    private function is_valid_language($lang) {
        if (function_exists('gufte_is_valid_language')) {
            return gufte_is_valid_language($lang);
        }
        
        $supported_languages = $this->get_supported_languages();
        return array_key_exists($lang, $supported_languages);
    }
    
    private function is_language_available($lang) {
        if ($lang === 'english') {
            return true;
        }
        
        if (empty($this->available_languages)) {
            return $this->is_valid_language($lang);
        }
        
        return in_array($lang, $this->available_languages, true);
    }
    
    private function build_faq_about_schema($song_title, $singer_name) {
        $about = [
            '@type' => 'MusicRecording',
            'name' => $song_title,
            'url' => get_permalink($this->post_id)
        ];
        
        if (!empty($singer_name)) {
            $about['byArtist'] = [
                '@type' => $this->determine_artist_type($singer_name),
                'name' => $singer_name
            ];
        }
        
        return $about;
    }
    
    private function determine_artist_type($artist_name) {
        $group_indicators = [
            'band', 'group', 'collective', 'crew', 'ensemble', 'orchestra',
            'the ', ' and ', ' & ', ' + ', ' feat.', ' ft.', ' featuring'
        ];
        
        $name_lower = strtolower($artist_name);
        
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
    
    private function get_current_canonical_url() {
        $current_lang = $this->get_current_language();
        return $this->get_canonical_url($current_lang);
    }
    
    private function get_canonical_url($lang) {
        $base_url = get_permalink($this->post_id);
        
        if ($lang !== 'english' && $this->is_language_available($lang)) {
            return add_query_arg('lang', $lang, $base_url);
        }
        
        return $base_url;
    }
    
    private function get_page_title($lang) {
        $title = get_the_title($this->post_id);
        $singer_name = $this->singers ? $this->singers[0]->name : '';
        
        $lang_suffix = '';
        switch ($lang) {
            case 'spanish':
                $lang_suffix = ' | Letras en Español';
                break;
            case 'turkish':
                $lang_suffix = ' | Türkçe Şarkı Sözleri';
                break;
            case 'german':
                $lang_suffix = ' | Liedtext auf Deutsch';
                break;
            default:
                $lang_suffix = ' | Lyrics and Translations';
                break;
        }
        
        if (!empty($singer_name)) {
            return "{$title} by {$singer_name}{$lang_suffix}";
        }
        
        return "{$title}{$lang_suffix}";
    }
    
    private function get_meta_description($lang) {
        $title = get_the_title($this->post_id);
        $singer_name = $this->singers ? $this->singers[0]->name : '';
        
        if (!empty($singer_name)) {
            return "Discover the lyrics of {$title} by {$singer_name}. Read, translate and enjoy the song lyrics.";
        }
        
        return "Discover the lyrics of {$title}. Read, translate and enjoy the song lyrics.";
    }
    
    private function get_language_iso_code($lang) {
        $iso_map = $this->get_supported_languages();
        return $iso_map[$lang] ?? 'en';
    }
    
    private function get_genres() {
        $genres = [];
        $language_exclusions = $this->get_language_category_exclusions();
        
        // Kategorilerden genre al
        if ($this->categories) {
            foreach ($this->categories as $category) {
                $name_lower = strtolower($category->name);
                $slug_lower = strtolower($category->slug);
                
                if (in_array($name_lower, $language_exclusions, true) || in_array($slug_lower, $language_exclusions, true)) {
                    continue;
                }
                
                if (!in_array($category->name, $genres, true)) {
                    $genres[] = $category->name;
                }
            }
        }
        
        // Apple Music'ten genre
        $music_genre = get_post_meta($this->post_id, '_music_genre', true);
        if (!empty($music_genre) && !in_array($music_genre, $genres)) {
            $genres[] = $music_genre;
        }
        
        return $genres;
    }
    
    private function get_platform_links() {
        $links = [];
        
        $platforms = [
            'apple_music_url',
            'spotify_url', 
            'youtube_url',
            'deezer_url',
            'soundcloud_url'
        ];
        
        foreach ($platforms as $platform) {
            $url = get_post_meta($this->post_id, $platform, true);
            if (!empty($url)) {
                $links[] = $url;
            }
        }
        
        return $links;
    }
    
    private function get_singer_external_links($singer_id) {
        $links = [];
        
        // Müzik platformları
        $music_platforms = [
            'spotify_artist_url',
            'apple_music_artist_url',
            'youtube_music_artist_url',
            'deezer_artist_url',
            'soundcloud_artist_url'
        ];
        
        foreach ($music_platforms as $platform) {
            $url = get_term_meta($singer_id, $platform, true);
            if (!empty($url)) {
                $links[] = $url;
            }
        }
        
        // Sosyal medya
        $social_platforms = [
            'instagram_url',
            'twitter_url',
            'facebook_url',
            'tiktok_url',
            'youtube_channel_url',
            'official_website_url'
        ];
        
        foreach ($social_platforms as $platform) {
            $url = get_term_meta($singer_id, $platform, true);
            if (!empty($url)) {
                $links[] = $url;
            }
        }
        
        return $links;
    }
    
    private function get_language_category_exclusions() {
        $language_keys = array_keys($this->get_supported_languages());
        $language_labels = array_map('strtolower', $language_keys);
        
        if (function_exists('gufte_get_language_settings')) {
            $settings = gufte_get_language_settings();
            
            if (!empty($settings['language_map']) && is_array($settings['language_map'])) {
                foreach ($settings['language_map'] as $key => $label) {
                    $language_labels[] = strtolower($key);
                    $language_labels[] = strtolower($label);
                }
            }
        }
        
        return array_unique($language_labels);
    }
    
    private function get_image_alt_text() {
        $thumbnail_id = get_post_thumbnail_id($this->post_id);
        if (!$thumbnail_id) return '';
        
        return get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true) ?: '';
    }
}

/**
 * Single post schema'yı render et
 */
function gufte_render_single_post_schema() {
    if (!is_single() || get_post_type() !== 'post') {
        return;
    }
    
    $schema_generator = new Gufte_Single_Post_Schema();
    $schema = $schema_generator->generate_schema();
    
    echo '<script type="application/ld+json">' . "\n";
    echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    echo "\n" . '</script>' . "\n";
}

// Hook ekle
add_action('wp_head', 'gufte_render_single_post_schema', 5);
?>
