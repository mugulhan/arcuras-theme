<?php
/**
 * Automatic Alt Text System
 *
 * This file handles automatic generation of alt text for featured images
 * with multi-language support and context-aware descriptions
 *
 * @package Gufte
 * @since 1.5.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Featured image için otomatik alt text oluşturma
 */
function gufte_generate_auto_alt_text($post_id = null, $context = 'default', $language = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    // Post bilgilerini al
    $post_title = get_the_title($post_id);
    $post_type = get_post_type($post_id);

    // Eğer post değilse standart alt text döndür
    if ($post_type !== 'post') {
        return $post_title;
    }

    // Şarkıcı bilgilerini al
    $singers = get_the_terms($post_id, 'singer');
    $singer_names = array();
    $primary_singer = '';

    if ($singers && !is_wp_error($singers)) {
        foreach ($singers as $singer) {
            $singer_names[] = $singer->name;
        }
        $primary_singer = $singer_names[0];
    }

    // Albüm bilgilerini al
    $albums = get_the_terms($post_id, 'album');
    $album_name = '';
    $album_year = '';

    if ($albums && !is_wp_error($albums)) {
        $album = reset($albums);
        $album_name = $album->name;
        $album_year = get_term_meta($album->term_id, 'album_year', true);
    }

    // Kategori bilgilerini al
    $categories = get_the_category($post_id);
    $category_name = '';

    if (!empty($categories)) {
        $category_name = $categories[0]->name;
    }

    // Release date bilgisini al
    $release_date = get_post_meta($post_id, '_release_date', true);
    $release_year = '';
    if (!empty($release_date)) {
        $release_year = date('Y', strtotime($release_date));
    }

    // Genre bilgisini al
    $music_genre = get_post_meta($post_id, '_music_genre', true);

    // Alt text şablonları
    $alt_text_templates = array();

    // Context'e göre alt text oluştur
    switch ($context) {
        case 'archive':
            // Arşiv sayfaları için kısa alt text
            if (!empty($primary_singer)) {
                $alt_text_templates[] = sprintf('%s by %s album cover', $post_title, $primary_singer);
                $alt_text_templates[] = sprintf('%s - %s album artwork', $post_title, $primary_singer);
            } else {
                $alt_text_templates[] = sprintf('%s album cover image', $post_title);
                $alt_text_templates[] = sprintf('%s song artwork', $post_title);
            }
            break;

        case 'single':
            // Tekil yazı sayfası için detaylı alt text
            if (!empty($primary_singer) && !empty($album_name)) {
                if (!empty($album_year)) {
                    $alt_text_templates[] = sprintf('%s by %s from the album %s (%s) - Album cover artwork',
                        $post_title, $primary_singer, $album_name, $album_year);
                } else {
                    $alt_text_templates[] = sprintf('%s by %s from the album %s - Album cover artwork',
                        $post_title, $primary_singer, $album_name);
                }
            } elseif (!empty($primary_singer)) {
                if (!empty($release_year)) {
                    $alt_text_templates[] = sprintf('%s by %s (%s) - Song cover artwork featuring the artist',
                        $post_title, $primary_singer, $release_year);
                } else {
                    $alt_text_templates[] = sprintf('%s by %s - Official song artwork and album cover',
                        $post_title, $primary_singer);
                }
            } else {
                $alt_text_templates[] = sprintf('%s - Song lyrics cover image and artwork', $post_title);
            }

            // Genre varsa ekle
            if (!empty($music_genre)) {
                $alt_text_templates[] = sprintf('%s - %s music album cover featuring %s',
                    $post_title, $music_genre, $primary_singer ?: 'the artist');
            }
            break;

        case 'social':
            // Sosyal medya paylaşımları için
            if (!empty($primary_singer)) {
                $alt_text_templates[] = sprintf('Listen to %s by %s - Song lyrics and translations available',
                    $post_title, $primary_singer);
            } else {
                $alt_text_templates[] = sprintf('%s - Lyrics and translations on %s',
                    $post_title, get_bloginfo('name'));
            }
            break;

        case 'translation':
            // Çeviri sayfaları için dile özel alt text
            if ($language) {
                // Dile özel alt text şablonları
                switch($language) {
                    case 'turkish':
                        if (!empty($primary_singer)) {
                            $alt_text_templates[] = sprintf('%s - %s Türkçe çeviri albüm kapağı',
                                $post_title, $primary_singer);
                            $alt_text_templates[] = sprintf('%s şarkısının Türkçe sözleri - %s albüm görseli',
                                $post_title, $primary_singer);
                        } else {
                            $alt_text_templates[] = sprintf('%s Türkçe şarkı sözleri albüm kapağı',
                                $post_title);
                        }
                        break;

                    case 'spanish':
                        if (!empty($primary_singer)) {
                            $alt_text_templates[] = sprintf('%s por %s - Traducción al español portada del álbum',
                                $post_title, $primary_singer);
                            $alt_text_templates[] = sprintf('Letra de %s en español - %s imagen del álbum',
                                $post_title, $primary_singer);
                        } else {
                            $alt_text_templates[] = sprintf('%s letra en español portada del álbum',
                                $post_title);
                        }
                        break;

                    case 'russian':
                        if (!empty($primary_singer)) {
                            $alt_text_templates[] = sprintf('%s - %s русский перевод обложка альбома',
                                $post_title, $primary_singer);
                            $alt_text_templates[] = sprintf('Текст песни %s на русском - %s изображение альбома',
                                $post_title, $primary_singer);
                        } else {
                            $alt_text_templates[] = sprintf('%s текст песни на русском обложка альбома',
                                $post_title);
                        }
                        break;

                    case 'german':
                        if (!empty($primary_singer)) {
                            $alt_text_templates[] = sprintf('%s von %s - Deutsche Übersetzung Albumcover',
                                $post_title, $primary_singer);
                            $alt_text_templates[] = sprintf('%s Liedtext auf Deutsch - %s Albumbild',
                                $post_title, $primary_singer);
                        } else {
                            $alt_text_templates[] = sprintf('%s deutscher Liedtext Albumcover',
                                $post_title);
                        }
                        break;

                    case 'french':
                        if (!empty($primary_singer)) {
                            $alt_text_templates[] = sprintf('%s par %s - Traduction française pochette album',
                                $post_title, $primary_singer);
                            $alt_text_templates[] = sprintf('Paroles de %s en français - %s image album',
                                $post_title, $primary_singer);
                        } else {
                            $alt_text_templates[] = sprintf('%s paroles en français pochette album',
                                $post_title);
                        }
                        break;

                    case 'italian':
                        if (!empty($primary_singer)) {
                            $alt_text_templates[] = sprintf('%s di %s - Traduzione italiana copertina album',
                                $post_title, $primary_singer);
                            $alt_text_templates[] = sprintf('Testo di %s in italiano - %s immagine album',
                                $post_title, $primary_singer);
                        } else {
                            $alt_text_templates[] = sprintf('%s testo in italiano copertina album',
                                $post_title);
                        }
                        break;

                    case 'portuguese':
                        if (!empty($primary_singer)) {
                            $alt_text_templates[] = sprintf('%s por %s - Tradução em português capa do álbum',
                                $post_title, $primary_singer);
                            $alt_text_templates[] = sprintf('Letra de %s em português - %s imagem do álbum',
                                $post_title, $primary_singer);
                        } else {
                            $alt_text_templates[] = sprintf('%s letra em português capa do álbum',
                                $post_title);
                        }
                        break;

                    case 'arabic':
                        if (!empty($primary_singer)) {
                            $alt_text_templates[] = sprintf('%s - %s ترجمة عربية غلاف الألبوم',
                                $post_title, $primary_singer);
                            $alt_text_templates[] = sprintf('كلمات أغنية %s بالعربية - %s صورة الألبوم',
                                $post_title, $primary_singer);
                        } else {
                            $alt_text_templates[] = sprintf('%s كلمات الأغنية بالعربية غلاف الألبوم',
                                $post_title);
                        }
                        break;

                    case 'japanese':
                        if (!empty($primary_singer)) {
                            $alt_text_templates[] = sprintf('%s - %s 日本語訳 アルバムカバー',
                                $post_title, $primary_singer);
                            $alt_text_templates[] = sprintf('%sの日本語歌詞 - %s アルバム画像',
                                $post_title, $primary_singer);
                        } else {
                            $alt_text_templates[] = sprintf('%s 日本語歌詞 アルバムカバー',
                                $post_title);
                        }
                        break;

                    case 'korean':
                        if (!empty($primary_singer)) {
                            $alt_text_templates[] = sprintf('%s - %s 한국어 번역 앨범 커버',
                                $post_title, $primary_singer);
                            $alt_text_templates[] = sprintf('%s 한국어 가사 - %s 앨범 이미지',
                                $post_title, $primary_singer);
                        } else {
                            $alt_text_templates[] = sprintf('%s 한국어 가사 앨범 커버',
                                $post_title);
                        }
                        break;

                    case 'persian':
                        if (!empty($primary_singer)) {
                            $alt_text_templates[] = sprintf('%s - %s ترجمه فارسی جلد آلبوم',
                                $post_title, $primary_singer);
                            $alt_text_templates[] = sprintf('متن آهنگ %s به فارسی - %s تصویر آلبوم',
                                $post_title, $primary_singer);
                        } else {
                            $alt_text_templates[] = sprintf('%s متن آهنگ فارسی جلد آلبوم',
                                $post_title);
                        }
                        break;

                    case 'english':
                    default:
                        if (!empty($primary_singer)) {
                            $alt_text_templates[] = sprintf('%s by %s - English lyrics album cover',
                                $post_title, $primary_singer);
                            $alt_text_templates[] = sprintf('%s song lyrics in English - %s album artwork',
                                $post_title, $primary_singer);
                        } else {
                            $alt_text_templates[] = sprintf('%s English lyrics album cover image',
                                $post_title);
                        }
                        break;
                }
            }
            break;

        case 'singer':
            // Şarkıcı arşiv sayfası için
            if (!empty($primary_singer)) {
                $alt_text_templates[] = sprintf('%s artist photo - %s album artwork',
                    $primary_singer, $post_title);
                $alt_text_templates[] = sprintf('%s performing %s - Artist promotional image',
                    $primary_singer, $post_title);
            }
            break;

        case 'album':
            // Albüm arşiv sayfası için
            if (!empty($album_name) && !empty($primary_singer)) {
                $alt_text_templates[] = sprintf('%s album by %s - %s track cover',
                    $album_name, $primary_singer, $post_title);
            } elseif (!empty($album_name)) {
                $alt_text_templates[] = sprintf('%s album - %s song artwork',
                    $album_name, $post_title);
            }
            break;

        case 'search':
            // Arama sonuçları için
            $alt_text_templates[] = sprintf('Search result: %s%s',
                $post_title,
                !empty($primary_singer) ? ' by ' . $primary_singer : '');
            break;

        case 'related':
            // İlgili yazılar için
            if (!empty($primary_singer)) {
                $alt_text_templates[] = sprintf('Related song: %s by %s',
                    $post_title, $primary_singer);
            } else {
                $alt_text_templates[] = sprintf('Related: %s album cover', $post_title);
            }
            break;

        default:
            // Varsayılan alt text
            if (!empty($primary_singer)) {
                $alt_text_templates[] = sprintf('%s by %s - Song album cover',
                    $post_title, $primary_singer);
            } else {
                $alt_text_templates[] = sprintf('%s - Music album cover image', $post_title);
            }
            break;
    }

    // Rastgele bir template seç (çeşitlilik için)
    if (!empty($alt_text_templates)) {
        $selected_template = $alt_text_templates[array_rand($alt_text_templates)];
        return $selected_template;
    }

    // Fallback
    return $post_title . ' - Album Cover';
}

/**
 * Featured image'a otomatik alt text ekle
 */
function gufte_auto_add_alt_text($html, $post_id, $post_thumbnail_id, $size, $attr) {
    // Context'i belirle
    $context = 'default';
    $language = null;

    // Dil kontrolü - URL parametresinden veya query var'dan al
    if (isset($_GET['lang'])) {
        $language = sanitize_text_field($_GET['lang']);
        $context = 'translation';
    } elseif (get_query_var('lang')) {
        $language = sanitize_text_field(get_query_var('lang'));
        $context = 'translation';
    }

    // Eğer dil parametresi yoksa normal context belirleme
    if (!$language) {
        if (is_single()) {
            $context = 'single';
        } elseif (is_archive()) {
            if (is_tax('singer')) {
                $context = 'singer';
            } elseif (is_tax('album')) {
                $context = 'album';
            } else {
                $context = 'archive';
            }
        } elseif (is_search()) {
            $context = 'search';
        }
    }

    // Dile özel cache key oluştur
    $cache_key = '_wp_attachment_image_alt';
    if ($language) {
        $cache_key .= '_' . $language;
    }

    // Dile özel alt text'i kontrol et
    $alt_text = get_post_meta($post_thumbnail_id, $cache_key, true);

    // Eğer dile özel alt text yoksa oluştur
    if (empty($alt_text)) {
        $alt_text = gufte_generate_auto_alt_text($post_id, $context, $language);

        // Dile özel olarak kaydet (cache için)
        if (!empty($alt_text)) {
            update_post_meta($post_thumbnail_id, $cache_key, $alt_text);
        }
    }

    // HTML'e alt attribute ekle veya güncelle
    if (strpos($html, 'alt=') === false) {
        $html = str_replace('<img', '<img alt="' . esc_attr($alt_text) . '"', $html);
    } else {
        // Mevcut alt text'i güncelle
        $html = preg_replace('/alt="[^"]*"/', 'alt="' . esc_attr($alt_text) . '"', $html);
    }

    return $html;
}
add_filter('post_thumbnail_html', 'gufte_auto_add_alt_text', 10, 5);

/**
 * get_the_post_thumbnail fonksiyonu için alt text override
 * Bu daha erken aşamada çalışır
 */
function gufte_override_thumbnail_attr($attr, $attachment, $size) {
    // Sadece frontend'de çalış
    if (is_admin()) {
        return $attr;
    }

    // Post ID'yi al
    global $post;
    if (!$post) {
        return $attr;
    }

    $post_id = $post->ID;
    $thumbnail_id = $attachment->ID;

    // Dil kontrolü
    $language = null;
    $context = 'default';

    if (isset($_GET['lang'])) {
        $language = sanitize_text_field($_GET['lang']);
        $context = 'translation';
    } elseif (get_query_var('lang')) {
        $language = sanitize_text_field(get_query_var('lang'));
        $context = 'translation';
    }

    // Context belirleme
    if (!$language) {
        if (is_single()) {
            $context = 'single';
        } elseif (is_archive()) {
            if (is_tax('singer')) {
                $context = 'singer';
            } elseif (is_tax('album')) {
                $context = 'album';
            } else {
                $context = 'archive';
            }
        } elseif (is_search()) {
            $context = 'search';
        }
    }

    // Alt text oluştur
    $alt_text = gufte_generate_auto_alt_text($post_id, $context, $language);

    // Alt text'i attribute'a ekle
    if (!empty($alt_text)) {
        $attr['alt'] = $alt_text;
    }

    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'gufte_override_thumbnail_attr', 20, 3);

/**
 * Attachment upload edildiğinde otomatik alt text ekle
 */
function gufte_set_attachment_alt_on_upload($attachment_id) {
    // Parent post ID'yi al
    $parent_id = wp_get_post_parent_id($attachment_id);

    if ($parent_id) {
        // Parent post'un tipini kontrol et
        if (get_post_type($parent_id) === 'post') {
            // Alt text oluştur
            $alt_text = gufte_generate_auto_alt_text($parent_id, 'default');

            // Alt text'i kaydet
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt_text);
        }
    }
}
add_action('add_attachment', 'gufte_set_attachment_alt_on_upload');

/**
 * Featured image set edildiğinde alt text güncelle
 */
function gufte_update_alt_on_featured_image_set($meta_id, $post_id, $meta_key, $meta_value) {
    if ($meta_key === '_thumbnail_id' && $meta_value) {
        // Alt text oluştur ve kaydet
        $alt_text = gufte_generate_auto_alt_text($post_id, 'single');
        update_post_meta($meta_value, '_wp_attachment_image_alt', $alt_text);
    }
}
add_action('added_post_meta', 'gufte_update_alt_on_featured_image_set', 10, 4);
add_action('updated_post_meta', 'gufte_update_alt_on_featured_image_set', 10, 4);

/**
 * Mevcut featured image'lar için toplu alt text güncelleme
 */
function gufte_bulk_update_alt_texts() {
    // Tüm post'ları al
    $posts = get_posts(array(
        'post_type' => 'post',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ));

    $updated_count = 0;

    foreach ($posts as $post) {
        $thumbnail_id = get_post_thumbnail_id($post->ID);

        if ($thumbnail_id) {
            // Mevcut alt text'i kontrol et
            $existing_alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);

            if (empty($existing_alt)) {
                // Alt text oluştur
                $alt_text = gufte_generate_auto_alt_text($post->ID, 'single');

                // Kaydet
                update_post_meta($thumbnail_id, '_wp_attachment_image_alt', $alt_text);
                $updated_count++;
            }
        }
    }

    return $updated_count;
}

/**
 * Admin menüye alt text yönetim sayfası ekle
 */
function gufte_add_alt_text_admin_page() {
    add_management_page(
        'Alt Text Manager',
        'Alt Text Manager',
        'manage_options',
        'alt-text-manager',
        'gufte_alt_text_manager_page'
    );
}
add_action('admin_menu', 'gufte_add_alt_text_admin_page');

/**
 * Alt text yönetim sayfası içeriği
 */
function gufte_alt_text_manager_page() {
    // Toplu güncelleme işlemi
    if (isset($_POST['bulk_update']) && wp_verify_nonce($_POST['alt_text_nonce'], 'bulk_update_alt_text')) {
        $updated = gufte_bulk_update_alt_texts();
        echo '<div class="notice notice-success"><p>' . sprintf('Successfully updated %d alt texts.', $updated) . '</p></div>';
    }

    // İstatistikleri al
    global $wpdb;

    $total_images = $wpdb->get_var("
        SELECT COUNT(DISTINCT pm.meta_value)
        FROM {$wpdb->postmeta} pm
        WHERE pm.meta_key = '_thumbnail_id'
        AND pm.meta_value != ''
    ");

    $images_with_alt = $wpdb->get_var("
        SELECT COUNT(DISTINCT pm1.meta_value)
        FROM {$wpdb->postmeta} pm1
        INNER JOIN {$wpdb->postmeta} pm2 ON pm1.meta_value = pm2.post_id
        WHERE pm1.meta_key = '_thumbnail_id'
        AND pm2.meta_key = '_wp_attachment_image_alt'
        AND pm2.meta_value != ''
    ");

    $images_without_alt = $total_images - $images_with_alt;
    $percentage = $total_images > 0 ? round(($images_with_alt / $total_images) * 100, 1) : 0;

    ?>
    <div class="wrap">
        <h1>🖼️ Alt Text Manager</h1>

        <div class="card">
            <h2>Statistics</h2>
            <ul>
                <li><strong>Total Featured Images:</strong> <?php echo number_format($total_images); ?></li>
                <li><strong>Images with Alt Text:</strong> <?php echo number_format($images_with_alt); ?> (<?php echo $percentage; ?>%)</li>
                <li><strong>Images without Alt Text:</strong> <?php echo number_format($images_without_alt); ?></li>
            </ul>

            <div style="background: #f0f0f1; height: 20px; border-radius: 10px; overflow: hidden; margin: 20px 0;">
                <div style="background: #00a32a; height: 100%; width: <?php echo $percentage; ?>%; transition: width 0.5s ease;"></div>
            </div>
        </div>

        <div class="card">
            <h2>Bulk Update Alt Texts</h2>
            <p>This will automatically generate alt texts for all featured images that don't have one.</p>

            <form method="post">
                <?php wp_nonce_field('bulk_update_alt_text', 'alt_text_nonce'); ?>
                <p class="submit">
                    <input type="submit" name="bulk_update" class="button-primary" value="Generate Missing Alt Texts"
                           <?php echo $images_without_alt == 0 ? 'disabled' : ''; ?> />
                </p>
            </form>
        </div>

        <div class="card">
            <h2>Recent Images Without Alt Text</h2>
            <?php
            // Son 10 alt text'siz resmi göster
            $recent_without_alt = $wpdb->get_results("
                SELECT p.ID, p.post_title, pm.meta_value as thumbnail_id
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                LEFT JOIN {$wpdb->postmeta} alt ON pm.meta_value = alt.post_id AND alt.meta_key = '_wp_attachment_image_alt'
                WHERE pm.meta_key = '_thumbnail_id'
                AND pm.meta_value != ''
                AND (alt.meta_value IS NULL OR alt.meta_value = '')
                AND p.post_status = 'publish'
                AND p.post_type = 'post'
                ORDER BY p.ID DESC
                LIMIT 10
            ");

            if ($recent_without_alt) {
                echo '<table class="wp-list-table widefat fixed striped">';
                echo '<thead><tr><th>Post Title</th><th>Generated Alt Text</th><th>Action</th></tr></thead>';
                echo '<tbody>';

                foreach ($recent_without_alt as $item) {
                    $generated_alt = gufte_generate_auto_alt_text($item->ID, 'single');
                    echo '<tr>';
                    echo '<td><a href="' . get_edit_post_link($item->ID) . '">' . esc_html($item->post_title) . '</a></td>';
                    echo '<td>' . esc_html($generated_alt) . '</td>';
                    echo '<td><button class="button generate-single-alt" data-post-id="' . $item->ID . '" data-thumbnail-id="' . $item->thumbnail_id . '">Generate</button></td>';
                    echo '</tr>';
                }

                echo '</tbody></table>';
            } else {
                echo '<p>All images have alt texts! 🎉</p>';
            }
            ?>
        </div>

        <div class="card">
            <h2>Alt Text Templates</h2>
            <p>The system automatically generates alt texts based on these contexts:</p>
            <ul>
                <li><strong>Single Post:</strong> Detailed alt text with song, artist, album information</li>
                <li><strong>Archive Pages:</strong> Shorter alt text for list views</li>
                <li><strong>Translation Pages:</strong> Language-specific alt text</li>
                <li><strong>Singer Pages:</strong> Artist-focused alt text</li>
                <li><strong>Album Pages:</strong> Album-focused alt text</li>
                <li><strong>Search Results:</strong> Search-optimized alt text</li>
                <li><strong>Related Posts:</strong> Contextual alt text for related content</li>
                <li><strong>Social Sharing:</strong> Engagement-focused alt text</li>
            </ul>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('.generate-single-alt').on('click', function() {
            var button = $(this);
            var postId = button.data('post-id');
            var thumbnailId = button.data('thumbnail-id');

            button.prop('disabled', true).text('Generating...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'generate_single_alt_text',
                    post_id: postId,
                    thumbnail_id: thumbnailId,
                    nonce: '<?php echo wp_create_nonce("generate_alt_text"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        button.text('✓ Generated').css('color', 'green');
                    } else {
                        button.text('Error').css('color', 'red');
                    }
                },
                error: function() {
                    button.text('Error').css('color', 'red');
                }
            });
        });
    });
    </script>
    <?php
}

/**
 * AJAX handler for single alt text generation
 */
function gufte_ajax_generate_single_alt_text() {
    if (!wp_verify_nonce($_POST['nonce'], 'generate_alt_text')) {
        wp_send_json_error('Invalid nonce');
    }

    $post_id = intval($_POST['post_id']);
    $thumbnail_id = intval($_POST['thumbnail_id']);

    if ($post_id && $thumbnail_id) {
        $alt_text = gufte_generate_auto_alt_text($post_id, 'single');
        update_post_meta($thumbnail_id, '_wp_attachment_image_alt', $alt_text);
        wp_send_json_success(array('alt_text' => $alt_text));
    }

    wp_send_json_error('Invalid parameters');
}
add_action('wp_ajax_generate_single_alt_text', 'gufte_ajax_generate_single_alt_text');

/**
 * SEO için image title attribute ekle
 */
function gufte_add_image_title_attribute($attr, $attachment, $size) {
    if (empty($attr['title'])) {
        $parent_id = wp_get_post_parent_id($attachment->ID);

        if ($parent_id) {
            // Title oluştur
            $post_title = get_the_title($parent_id);
            $singers = get_the_terms($parent_id, 'singer');

            if ($singers && !is_wp_error($singers)) {
                $singer_name = $singers[0]->name;
                $attr['title'] = sprintf('View %s by %s album cover in full size', $post_title, $singer_name);
            } else {
                $attr['title'] = sprintf('View %s album artwork', $post_title);
            }
        }
    }

    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'gufte_add_image_title_attribute', 10, 3);

/**
 * Shortcode ile manuel alt text üretimi
 * Kullanım: [auto_alt_text] veya [auto_alt_text lang="turkish"]
 */
function gufte_alt_text_shortcode($atts) {
    $atts = shortcode_atts(array(
        'post_id' => get_the_ID(),
        'lang' => isset($_GET['lang']) ? sanitize_text_field($_GET['lang']) : null,
        'context' => 'single'
    ), $atts);

    return gufte_generate_auto_alt_text($atts['post_id'], $atts['context'], $atts['lang']);
}
add_shortcode('auto_alt_text', 'gufte_alt_text_shortcode');

/**
 * Mevcut tüm diller için alt text'leri önceden oluştur
 */
function gufte_pregenerate_multilingual_alt_texts($post_id) {
    $thumbnail_id = get_post_thumbnail_id($post_id);

    if (!$thumbnail_id) {
        return;
    }

    // Desteklenen tüm diller
    $languages = array(
        'english', 'turkish', 'spanish', 'russian', 'german',
        'french', 'italian', 'portuguese', 'arabic', 'japanese',
        'korean', 'persian'
    );

    foreach ($languages as $lang) {
        $cache_key = '_wp_attachment_image_alt_' . $lang;
        $existing = get_post_meta($thumbnail_id, $cache_key, true);

        if (empty($existing)) {
            $alt_text = gufte_generate_auto_alt_text($post_id, 'translation', $lang);
            update_post_meta($thumbnail_id, $cache_key, $alt_text);
        }
    }
}
// Post kayıt/güncelleme sırasında çalıştır
add_action('save_post', 'gufte_pregenerate_multilingual_alt_texts', 20);

/**
 * Admin sütunu ekle - Alt Text durumu
 */
