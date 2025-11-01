<?php
/**
 * Şarkıcı Sayfası Yapılandırılmış Veri (Structured Data) Oluşturucu
 * 
 * Şarkıcı sayfaları için gelişmiş Schema.org markup'ları oluşturur
 * 
 * @package Gufte
 */

// Güvenlik kontrolü
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Şarkıcı için gelişmiş yapılandırılmış veri oluştur
 * 
 * @param int $singer_term_id Şarkıcı term ID
 * @return string JSON-LD script tag
 */
function gufte_generate_singer_structured_data($singer_term_id) {
    $term = get_term($singer_term_id, 'singer');
    if (is_wp_error($term) || !$term) {
        return '';
    }

    // Temel veriler
    $term_link = get_term_link($term);
    if (is_wp_error($term_link)) {
        $term_link = home_url('/singers/');
    }

    // Meta veriler
    $real_name = get_term_meta($singer_term_id, 'real_name', true);
    $birth_place = get_term_meta($singer_term_id, 'birth_place', true);
    $birth_country = get_term_meta($singer_term_id, 'birth_country', true);
    $birth_date = get_term_meta($singer_term_id, 'birth_date', true);
    $death_date = get_term_meta($singer_term_id, 'death_date', true);
    $description = wp_strip_all_tags(term_description($singer_term_id, 'singer'));

    // Görsel URL'si
    $singer_image_url = '';
    $singer_image_id = get_term_meta($singer_term_id, 'singer_image_id', true);
    if ($singer_image_id) {
        $singer_image_url = wp_get_attachment_image_url($singer_image_id, 'full');
    } elseif (function_exists('gufte_get_singer_image')) {
        $img_html = gufte_get_singer_image($singer_term_id, 'full');
        if ($img_html && preg_match('/src="([^"]+)"/', $img_html, $m)) {
            $singer_image_url = $m[1];
        }
    }

    // Platform linklerini al
    $sameAs = array();
    if (function_exists('gufte_get_singer_platform_links')) {
        $platform_links = gufte_get_singer_platform_links($singer_term_id);
        foreach ($platform_links as $platform => $url) {
            if (!empty($url)) {
                $sameAs[] = $url;
            }
        }
    }

    // Albüm bilgileri
    $albums = array();
    if (function_exists('gufte_get_singer_albums')) {
        $albums = gufte_get_singer_albums($singer_term_id);
    }

    // Şarkı bilgileri
    $songs = get_posts(array(
        'post_type' => 'post',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'tax_query' => array(
            array(
                'taxonomy' => 'singer',
                'field' => 'term_id',
                'terms' => $singer_term_id,
            ),
        ),
    ));

    // Türler
    $genres = get_terms(array(
        'taxonomy' => 'genre',
        'object_ids' => $songs,
        'hide_empty' => true,
    ));

    // Ana yapılandırılmış veri dizisi
    $schema_graph = array();

    // 1. MusicGroup Schema
    $music_group_schema = array(
        '@type' => 'MusicGroup',
        '@id' => trailingslashit($term_link) . '#musicgroup',
        'name' => $term->name,
        'url' => $term_link,
        'mainEntityOfPage' => $term_link,
        'description' => $description ?: sprintf('Listen to %s songs and view lyrics on our website.', $term->name),
    );

    // Gerçek isim ekle
    if (!empty($real_name) && $real_name !== $term->name) {
        $music_group_schema['alternateName'] = $real_name;
    }

    // Görsel ekle
    if (!empty($singer_image_url)) {
        $music_group_schema['image'] = array(
            '@type' => 'ImageObject',
            'url' => esc_url_raw($singer_image_url),
            'caption' => sprintf('%s photo', $term->name)
        );
    }

    // Doğum yeri
    if (!empty($birth_place) || !empty($birth_country)) {
        $birthplace = '';
        if (!empty($birth_place)) { $birthplace = $birth_place; }
        if (!empty($birth_country)) { $birthplace .= (!empty($birthplace) ? ', ' : '') . $birth_country; }
        
        $music_group_schema['foundingLocation'] = array(
            '@type' => 'Place',
            'name' => $birthplace
        );
    }

    // Doğum ve ölüm tarihleri
    if (!empty($birth_date)) {
        $music_group_schema['foundingDate'] = date('Y-m-d', strtotime($birth_date));
    }
    if (!empty($death_date)) {
        $music_group_schema['dissolutionDate'] = date('Y-m-d', strtotime($death_date));
    }

    // Platform linklerini sameAs olarak ekle
    if (!empty($sameAs)) {
        $music_group_schema['sameAs'] = $sameAs;
    }

    // Müzik türleri
    if (!is_wp_error($genres) && !empty($genres)) {
        $genre_names = array_map(function($genre) {
            return $genre->name;
        }, array_slice($genres, 0, 5));
        $music_group_schema['genre'] = $genre_names;
    }

    // Albümler
    if (!empty($albums)) {
        $album_schemas = array();
        foreach (array_slice($albums, 0, 10) as $album) { // İlk 10 albüm
            $album_year = get_term_meta($album->term_id, 'album_year', true);
            $album_link = get_term_link($album);
            
            // Albüm kapak görseli (ilk şarkıdan)
            $first_song_query = new WP_Query(array(
                'post_type' => 'post',
                'posts_per_page' => 1,
                'fields' => 'ids',
                'tax_query' => array(
                    'relation' => 'AND',
                    array('taxonomy' => 'album', 'field' => 'term_id', 'terms' => $album->term_id),
                    array('taxonomy' => 'singer', 'field' => 'term_id', 'terms' => $singer_term_id)
                ),
                'no_found_rows' => true
            ));
            
            $album_image_url = '';
            if ($first_song_query->have_posts()) {
                $first_song_id = $first_song_query->posts[0];
                if (has_post_thumbnail($first_song_id)) {
                    $album_image_url = get_the_post_thumbnail_url($first_song_id, 'full');
                }
            }
            wp_reset_postdata();

            $album_schema = array(
                '@type' => 'MusicAlbum',
                '@id' => (!is_wp_error($album_link) ? trailingslashit($album_link) : '#') . 'album',
                'name' => $album->name,
                'byArtist' => array('@id' => trailingslashit($term_link) . '#musicgroup'),
            );

            if (!is_wp_error($album_link)) {
                $album_schema['url'] = $album_link;
            }

            if (!empty($album_year)) {
                $album_schema['datePublished'] = $album_year . '-01-01';
            }

            if (!empty($album_image_url)) {
                $album_schema['image'] = array(
                    '@type' => 'ImageObject',
                    'url' => esc_url_raw($album_image_url),
                    'caption' => sprintf('%s album cover', $album->name)
                );
            }

            $album_schemas[] = $album_schema;
        }
        $music_group_schema['album'] = $album_schemas;
    }

    // Ana şemayı grafiğe ekle
    $schema_graph[] = $music_group_schema;

    // 2. BreadcrumbList Schema
    $breadcrumb_schema = array(
        '@type' => 'BreadcrumbList',
        '@id' => trailingslashit($term_link) . '#breadcrumb',
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
                'name' => 'Artists',
                'item' => home_url('/singers/')
            ),
            array(
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $term->name,
                'item' => $term_link
            )
        )
    );
    $schema_graph[] = $breadcrumb_schema;

    // 3. WebPage Schema
    $webpage_schema = array(
        '@type' => 'WebPage',
        '@id' => trailingslashit($term_link) . '#webpage',
        'url' => $term_link,
        'name' => sprintf('%s - Artist Profile', $term->name),
        'description' => $description ?: sprintf('Discover %s songs, albums, and biography. Listen to music and read lyrics.', $term->name),
        'mainEntity' => array('@id' => trailingslashit($term_link) . '#musicgroup'),
        'breadcrumb' => array('@id' => trailingslashit($term_link) . '#breadcrumb'),
        'inLanguage' => 'en-US',
        'isPartOf' => array(
            '@type' => 'WebSite',
            '@id' => home_url('/') . '#website',
            'name' => get_bloginfo('name'),
            'url' => home_url('/')
        )
    );
    $schema_graph[] = $webpage_schema;

    // 4. Organization Schema (Website Publisher)
    $organization_schema = array(
        '@type' => 'Organization',
        '@id' => home_url('/') . '#organization',
        'name' => get_bloginfo('name'),
        'url' => home_url('/'),
        'description' => get_bloginfo('description') ?: 'Music lyrics and artist information website',
        'sameAs' => array()
    );

    // Site logosu varsa ekle
    $custom_logo_id = get_theme_mod('custom_logo');
    if ($custom_logo_id) {
        $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
        if ($logo_url) {
            $organization_schema['logo'] = array(
                '@type' => 'ImageObject',
                'url' => esc_url_raw($logo_url),
                'caption' => get_bloginfo('name') . ' logo'
            );
        }
    }
    $schema_graph[] = $organization_schema;

    // 5. CollectionPage Schema (Şarkılar için)
    if (!empty($songs)) {
        $collection_schema = array(
            '@type' => 'CollectionPage',
            '@id' => trailingslashit($term_link) . '#collection',
            'name' => sprintf('Songs by %s', $term->name),
            'description' => sprintf('Complete collection of %s songs with lyrics', $term->name),
            'mainEntity' => array(
                '@type' => 'ItemList',
                'numberOfItems' => count($songs),
                'itemListElement' => array()
            )
        );

        // İlk 10 şarkıyı ekle
        $featured_songs = array_slice($songs, 0, 10);
        foreach ($featured_songs as $index => $song_id) {
            $song_link = get_permalink($song_id);
            $collection_schema['mainEntity']['itemListElement'][] = array(
                '@type' => 'ListItem',
                'position' => $index + 1,
                'item' => array(
                    '@type' => 'MusicRecording',
                    '@id' => trailingslashit($song_link) . '#recording',
                    'name' => get_the_title($song_id),
                    'url' => $song_link,
                    'byArtist' => array('@id' => trailingslashit($term_link) . '#musicgroup')
                )
            );
        }
        $schema_graph[] = $collection_schema;
    }

    // 6. Person Schema (eğer gerçek kişi ise)
    if (!empty($real_name) || !empty($birth_date)) {
        $person_schema = array(
            '@type' => 'Person',
            '@id' => trailingslashit($term_link) . '#person',
            'name' => !empty($real_name) ? $real_name : $term->name,
            'alternateName' => $term->name,
            'description' => $description ?: sprintf('Musical artist known as %s', $term->name),
            'sameAs' => $sameAs
        );

        if (!empty($singer_image_url)) {
            $person_schema['image'] = array(
                '@type' => 'ImageObject',
                'url' => esc_url_raw($singer_image_url),
                'caption' => sprintf('%s photo', $term->name)
            );
        }

        if (!empty($birth_date)) {
            $person_schema['birthDate'] = date('Y-m-d', strtotime($birth_date));
        }

        if (!empty($death_date)) {
            $person_schema['deathDate'] = date('Y-m-d', strtotime($death_date));
        }

        if (!empty($birth_place) || !empty($birth_country)) {
            $birthplace = '';
            if (!empty($birth_place)) { $birthplace = $birth_place; }
            if (!empty($birth_country)) { $birthplace .= (!empty($birthplace) ? ', ' : '') . $birth_country; }
            
            $person_schema['birthPlace'] = array(
                '@type' => 'Place',
                'name' => $birthplace
            );
        }

        $person_schema['jobTitle'] = 'Musical Artist';
        $person_schema['knowsAbout'] = array('Music', 'Singing', 'Performance');

        $schema_graph[] = $person_schema;
    }

    // Final JSON-LD output
    $schema_output = array(
        '@context' => 'https://schema.org',
        '@graph' => $schema_graph
    );

    return '<script type="application/ld+json">' .
           wp_json_encode($schema_output, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) .
           '</script>';
}

/**
 * Şarkıcı sayfası için tüm yapılandırılmış verileri render et
 * 
 * @param int $singer_term_id Şarkıcı term ID
 * @return void
 */
function gufte_render_singer_all_structured_data($singer_term_id) {
    // Ana yapılandırılmış veriyi echo et
    echo gufte_generate_singer_structured_data($singer_term_id);
    
    // FAQ yapılandırılmış verisini de ekle
    if (function_exists('gufte_generate_singer_faq')) {
        $faq_items = gufte_generate_singer_faq($singer_term_id);
        if (!empty($faq_items)) {
            echo gufte_render_faq_structured_data($faq_items);
        }
    }
}

/**
 * Şarkı detayları için MusicRecording schema oluştur
 * 
 * @param int $post_id Şarkı post ID
 * @param int $singer_term_id Şarkıcı term ID
 * @return string JSON-LD script tag
 */
function gufte_generate_song_structured_data($post_id, $singer_term_id = null) {
    $post = get_post($post_id);
    if (!$post) {
        return '';
    }

    $song_link = get_permalink($post_id);
    
    // Şarkıcı bilgilerini al
    $singers = get_the_terms($post_id, 'singer');
    $main_singer = null;
    
    if ($singer_term_id) {
        $main_singer = get_term($singer_term_id, 'singer');
    } elseif (!empty($singers) && !is_wp_error($singers)) {
        $main_singer = $singers[0];
    }

    // Albüm bilgilerini al
    $albums = get_the_terms($post_id, 'album');
    $album = (!empty($albums) && !is_wp_error($albums)) ? $albums[0] : null;

    // Türleri al
    $genres = get_the_terms($post_id, 'genre');

    $recording_schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'MusicRecording',
        '@id' => trailingslashit($song_link) . '#recording',
        'name' => $post->post_title,
        'url' => $song_link,
        'description' => wp_strip_all_tags($post->post_excerpt ?: $post->post_content),
        'datePublished' => get_the_date('Y-m-d', $post_id),
        'inLanguage' => 'en-US'
    );

    // Şarkıcı bilgisi
    if ($main_singer) {
        $singer_link = get_term_link($main_singer);
        if (!is_wp_error($singer_link)) {
            $recording_schema['byArtist'] = array(
                '@type' => 'MusicGroup',
                '@id' => trailingslashit($singer_link) . '#musicgroup',
                'name' => $main_singer->name,
                'url' => $singer_link
            );
        }
    }

    // Albüm bilgisi
    if ($album) {
        $album_link = get_term_link($album);
        $album_year = get_term_meta($album->term_id, 'album_year', true);
        
        $album_schema = array(
            '@type' => 'MusicAlbum',
            'name' => $album->name
        );
        
        if (!is_wp_error($album_link)) {
            $album_schema['@id'] = trailingslashit($album_link) . '#album';
            $album_schema['url'] = $album_link;
        }
        
        if ($album_year) {
            $album_schema['datePublished'] = $album_year . '-01-01';
        }
        
        $recording_schema['inAlbum'] = $album_schema;
    }

    // Türler
    if (!empty($genres) && !is_wp_error($genres)) {
        $genre_names = array_map(function($genre) {
            return $genre->name;
        }, $genres);
        $recording_schema['genre'] = $genre_names;
    }

    // Şarkı görseli
    if (has_post_thumbnail($post_id)) {
        $image_url = get_the_post_thumbnail_url($post_id, 'full');
        $recording_schema['image'] = array(
            '@type' => 'ImageObject',
            'url' => esc_url_raw($image_url),
            'caption' => sprintf('%s cover art', $post->post_title)
        );
    }

    return '<script type="application/ld+json">' .
           wp_json_encode($recording_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) .
           '</script>';
}

/**
 * Albüm için MusicAlbum schema oluştur
 * 
 * @param int $album_term_id Albüm term ID
 * @return string JSON-LD script tag
 */
function gufte_generate_album_structured_data($album_term_id) {
    $album = get_term($album_term_id, 'album');
    if (is_wp_error($album) || !$album) {
        return '';
    }

    $album_link = get_term_link($album);
    if (is_wp_error($album_link)) {
        return '';
    }

    $album_year = get_term_meta($album_term_id, 'album_year', true);
    $description = wp_strip_all_tags(term_description($album_term_id, 'album'));

    // Albümdeki şarkıları al
    $songs = get_posts(array(
        'post_type' => 'post',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'album',
                'field' => 'term_id',
                'terms' => $album_term_id,
            ),
        ),
        'orderby' => 'menu_order',
        'order' => 'ASC'
    ));

    // Ana sanatçıyı bul (en çok şarkısı olan)
    $singer_counts = array();
    foreach ($songs as $song) {
        $singers = get_the_terms($song->ID, 'singer');
        if (!empty($singers) && !is_wp_error($singers)) {
            foreach ($singers as $singer) {
                $singer_counts[$singer->term_id] = ($singer_counts[$singer->term_id] ?? 0) + 1;
            }
        }
    }
    
    $main_singer_id = !empty($singer_counts) ? array_key_first(array_slice(arsort($singer_counts) ? $singer_counts : $singer_counts, 0, 1, true)) : null;
    $main_singer = $main_singer_id ? get_term($main_singer_id, 'singer') : null;

    $album_schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'MusicAlbum',
        '@id' => trailingslashit($album_link) . '#album',
        'name' => $album->name,
        'url' => $album_link,
        'description' => $description ?: sprintf('Music album %s with song lyrics', $album->name),
        'numberOfTracks' => count($songs)
    );

    if ($album_year) {
        $album_schema['datePublished'] = $album_year . '-01-01';
    }

    // Ana sanatçı
    if ($main_singer) {
        $singer_link = get_term_link($main_singer);
        if (!is_wp_error($singer_link)) {
            $album_schema['byArtist'] = array(
                '@type' => 'MusicGroup',
                '@id' => trailingslashit($singer_link) . '#musicgroup',
                'name' => $main_singer->name,
                'url' => $singer_link
            );
        }
    }

    // Albüm kapağı (ilk şarkının görseli)
    if (!empty($songs) && has_post_thumbnail($songs[0]->ID)) {
        $image_url = get_the_post_thumbnail_url($songs[0]->ID, 'full');
        $album_schema['image'] = array(
            '@type' => 'ImageObject',
            'url' => esc_url_raw($image_url),
            'caption' => sprintf('%s album cover', $album->name)
        );
    }

    // Şarkı listesi
    if (!empty($songs)) {
        $track_list = array();
        foreach ($songs as $index => $song) {
            $song_link = get_permalink($song->ID);
            $track_list[] = array(
                '@type' => 'MusicRecording',
                '@id' => trailingslashit($song_link) . '#recording',
                'name' => $song->post_title,
                'url' => $song_link,
                'position' => $index + 1,
                'inAlbum' => array('@id' => trailingslashit($album_link) . '#album')
            );
        }
        $album_schema['track'] = $track_list;
    }

    return '<script type="application/ld+json">' .
           wp_json_encode($album_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) .
           '</script>';
}