<?php
/**
 * FAQ Functions for Lyrics Posts
 * Auto-generates FAQ sections with multilingual support
 *
 * @package Arcuras
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Single post sayfasında otomatik FAQ oluştur
 */
function gufte_generate_auto_faq($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    // Validate post type
    if (get_post_type($post_id) !== 'lyrics') {
        return '';
    }

    // Detect language from URL using WordPress query var
    $lang_code = 'en'; // Default to English

    // Use the translation_lang query var if available (set by rewrite rules)
    if (function_exists('gufte_get_current_translation_lang')) {
        $detected_lang = gufte_get_current_translation_lang();
        if (!empty($detected_lang)) {
            $lang_code = $detected_lang;
        }
    }

    // Şarkı bilgilerini topla
    $song_title = get_the_title($post_id);
    $singers = get_the_terms($post_id, 'singer');
    $albums = get_the_terms($post_id, 'album');
    $categories = get_the_category($post_id);
    
    // Dil bilgilerini al
    $lyrics_languages = array('original' => '', 'translations' => array());
    if (function_exists('gufte_get_lyrics_languages')) {
        $lyrics_languages = gufte_get_lyrics_languages(get_the_content());
    }
    
    // Müzik platformu bilgileri
    $spotify_url = get_post_meta($post_id, 'spotify_url', true);
    $youtube_url = get_post_meta($post_id, 'youtube_url', true);
    $apple_music_url = get_post_meta($post_id, 'apple_music_url', true);
    
    // Generate FAQ questions
    $faq_items = array();
    
    $release_date_raw = get_post_meta($post_id, '_release_date', true);
    if (!empty($release_date_raw)) {
        $release_timestamp = strtotime($release_date_raw);
        if ($release_timestamp) {
            $formatted_release_date = date_i18n(get_option('date_format'), $release_timestamp);
            $faq_items[] = array(
                'question' => gufte_get_faq_translation('when_released', $lang_code, array($song_title)),
                'answer' => gufte_get_faq_translation('was_released_on', $lang_code, array($song_title, $formatted_release_date))
            );
        }
    }
    
    // 1. Singer question
    if ($singers && !is_wp_error($singers)) {
        $singer_names = array();
        foreach ($singers as $singer) {
            $singer_names[] = $singer->name;
        }
        $singers_text = implode(', ', $singer_names);

        if (count($singer_names) > 1) {
            $question = gufte_get_faq_translation('who_sings', $lang_code, array($song_title));
            $answer = gufte_get_faq_translation('is_performed_by', $lang_code, array($song_title, $singers_text));
        } else {
            $question = gufte_get_faq_translation('who_is_singer', $lang_code, array($song_title));
            $answer = gufte_get_faq_translation('is_sung_by', $lang_code, array($song_title, $singers_text));
        }

        $faq_items[] = array(
            'question' => $question,
            'answer' => $answer
        );
    }
    
    // 2. Original language question
    if (!empty($lyrics_languages['original'])) {
        $faq_items[] = array(
            'question' => gufte_get_faq_translation('what_is_original_lang', $lang_code, array($song_title)),
            'answer' => gufte_get_faq_translation('original_lang_is', $lang_code, array($song_title, $lyrics_languages['original']))
        );
    }

    // 3. Translation languages question
    if (!empty($lyrics_languages['translations']) && count($lyrics_languages['translations']) > 0) {
        $translation_count = count($lyrics_languages['translations']);
        $translations_text = implode(', ', array_slice($lyrics_languages['translations'], 0, 3));

        if ($translation_count > 3) {
            $and_word = gufte_get_faq_translation('and', $lang_code);
            $more_word = gufte_get_faq_translation('more', $lang_code);
            $translations_text .= ' ' . $and_word . ' ' . ($translation_count - 3) . ' ' . $more_word;
        }

        $question = $translation_count > 1
            ? gufte_get_faq_translation('what_langs_available', $lang_code, array($song_title))
            : gufte_get_faq_translation('what_lang_available', $lang_code, array($song_title));
        $answer = gufte_get_faq_translation('translations_available_in', $lang_code, array($song_title, $translations_text));

        $faq_items[] = array(
            'question' => $question,
            'answer' => $answer
        );
    }
    
    // 4. Album question
    if ($albums && !is_wp_error($albums)) {
        $album = reset($albums);
        $album_year = get_term_meta($album->term_id, 'album_year', true);
        $year_text = !empty($album_year) ? " ({$album_year})" : "";

        $faq_items[] = array(
            'question' => gufte_get_faq_translation('which_album', $lang_code, array($song_title)),
            'answer' => gufte_get_faq_translation('is_from_album', $lang_code, array($song_title, $album->name, $year_text))
        );
    }

    // 5. Genre question (from Apple Music data)
    $music_genre = get_post_meta($post_id, '_music_genre', true);
    if (!empty($music_genre)) {
        $faq_items[] = array(
            'question' => gufte_get_faq_translation('what_genre', $lang_code, array($song_title)),
            'answer' => gufte_get_faq_translation('is_genre_song', $lang_code, array($song_title, $music_genre))
        );
    }

    // 6. Music platforms question
    $platforms = array();
    if ($spotify_url) $platforms[] = 'Spotify';
    if ($youtube_url) $platforms[] = 'YouTube Music';
    if ($apple_music_url) $platforms[] = 'Apple Music';

    if (!empty($platforms)) {
        $platforms_text = implode(', ', $platforms);

        $faq_items[] = array(
            'question' => gufte_get_faq_translation('where_listen', $lang_code, array($song_title)),
            'answer' => gufte_get_faq_translation('can_listen_on', $lang_code, array($song_title, $platforms_text))
        );
    }
    
// Mevcut kod bloğunun sonuna, "6. Lyrics information" kısmından önce ekleyin:

// Awards questions
$awards = function_exists('gufte_get_post_awards') ? gufte_get_post_awards($post_id) : array();
if (!empty($awards)) {
    $awards_summary = function_exists('gufte_prepare_awards_summary') ? gufte_prepare_awards_summary($awards) : array();
    
    // General awards question
    $total_awards = count($awards);
    if ($total_awards > 0) {
        $question = $total_awards > 1 ? "What awards has {$song_title} won or been nominated for?" : "What award has {$song_title} won or been nominated for?";
        
        $answer_parts = array();
        
        if (!empty($awards_summary['winners'])) {
            $winner_text = $awards_summary['winners'] > 1 ? "won {$awards_summary['winners']} awards" : "won 1 award";
            $answer_parts[] = $winner_text;
        }
        
        if (!empty($awards_summary['nominees'])) {
            $nominee_text = $awards_summary['nominees'] > 1 ? "been nominated for {$awards_summary['nominees']} awards" : "been nominated for 1 award";
            $answer_parts[] = $nominee_text;
        }
        
        if (!empty($awards_summary['mentions'])) {
            $mention_text = $awards_summary['mentions'] > 1 ? "received {$awards_summary['mentions']} honorable mentions" : "received 1 honorable mention";
            $answer_parts[] = $mention_text;
        }
        
        if (!empty($answer_parts)) {
            $achievements_text = implode(' and ', $answer_parts);
            $answer = "{$song_title} has {$achievements_text}.";
            
// Add specific award examples - yıl bilgisi dahil:
$major_awards = array();
foreach (array_slice($awards, 0, 2) as $award) {
    $org_name = !empty($award['organization']) ? $award['organization'] : $award['type_label'];
    $year_text = !empty($award['year']) ? " in {$award['year']}" : "";
    
    // Tüm award tiplerini dahil et (sadece winner değil)
    if ($award['result'] === 'winner') {
        $major_awards[] = "{$award['category']} ({$org_name}){$year_text}";
    } elseif ($award['result'] === 'nominee') {
        $major_awards[] = "{$award['category']} ({$org_name}){$year_text}";
    } elseif ($award['result'] === 'honorable_mention') {
        $major_awards[] = "{$award['category']} ({$org_name}){$year_text}";
    }
}

if (!empty($major_awards)) {
    $answer .= " Including " . implode(' and ', $major_awards) . ".";
}
            
            $faq_items[] = array(
                'question' => $question,
                'answer' => $answer
            );
        }
    }
    
    // Grammy-specific question (if applicable)
    $grammy_awards = array_filter($awards, function($award) {
        return $award['type'] === 'grammy';
    });
    
    if (!empty($grammy_awards)) {
        $grammy_count = count($grammy_awards);
        $grammy_winners = array_filter($grammy_awards, function($award) {
            return $award['result'] === 'winner';
        });
        
        if (!empty($grammy_winners)) {
            $question = count($grammy_winners) > 1 ? "How many Grammy Awards has {$song_title} won?" : "Did {$song_title} win a Grammy Award?";
            $answer = count($grammy_winners) > 1 ? 
                "{$song_title} has won " . count($grammy_winners) . " Grammy Awards." :
                "Yes, {$song_title} won a Grammy Award.";
                
            $faq_items[] = array(
                'question' => $question,
                'answer' => $answer
            );
        } elseif ($grammy_count > 0) {
            $question = "Was {$song_title} nominated for a Grammy Award?";
            $answer = $grammy_count > 1 ? 
                "Yes, {$song_title} was nominated for {$grammy_count} Grammy Awards." :
                "Yes, {$song_title} was nominated for a Grammy Award.";
                
            $faq_items[] = array(
                'question' => $question,
                'answer' => $answer
            );
        }
    }
    
    // Recent awards question (awards from last 2 years)
    $recent_awards = array_filter($awards, function($award) {
        if (empty($award['year'])) return false;
        $award_year = (int) preg_replace('/\D+/', '', $award['year']);
        $current_year = (int) date('Y');
        return ($award_year >= ($current_year - 2));
    });
    
    if (!empty($recent_awards) && count($recent_awards) < $total_awards) {
        $recent_count = count($recent_awards);
        $question = "What recent awards has {$song_title} received?";
        $answer = $recent_count > 1 ? 
            "{$song_title} has received {$recent_count} awards in recent years." :
            "{$song_title} has received 1 award recently.";
            
        $faq_items[] = array(
            'question' => $question,
            'answer' => $answer
        );
    }
}
    
    // 6. Lyrics information
    $faq_items[] = array(
        'question' => gufte_get_faq_translation('where_find_lyrics', $lang_code, array($song_title)),
        'answer' => gufte_get_faq_translation('find_lyrics_answer', $lang_code, array($song_title))
    );
    
    // FAQ HTML'ini oluştur
    if (empty($faq_items)) {
        return '';
    }

    return gufte_render_faq_html($faq_items, $post_id, $lang_code);
}

/**
 * Get translated FAQ string
 */
function gufte_get_faq_translation($key, $lang_code = 'en', $args = array()) {
    $translations = array(
        'en' => array(
            'faq_title' => 'Frequently Asked Questions About %s',
            'have_more' => 'Have more questions? Feel free to contact us through the comments section.',
            // Release date
            'when_released' => 'When was %s released?',
            'was_released_on' => '%s was released on %s.',
            // Singer
            'who_sings' => 'Who sings %s?',
            'who_is_singer' => 'Who is the singer of %s?',
            'is_performed_by' => '%s is performed by %s.',
            'is_sung_by' => '%s is sung by %s.',
            // Language
            'what_is_original_lang' => 'What is the original language of %s?',
            'original_lang_is' => 'The original language of %s is %s.',
            'what_langs_available' => 'What languages are %s translations available in?',
            'what_lang_available' => 'What language is %s translation available in?',
            'translations_available_in' => 'Translations of %s are available in %s.',
            // Album
            'which_album' => 'Which album is %s from?',
            'is_from_album' => '%s is from the album "%s"%s.',
            // Genre
            'what_genre' => 'What genre is %s?',
            'is_genre_song' => '%s is a %s song.',
            // Platforms
            'where_listen' => 'Where can I listen to %s?',
            'can_listen_on' => 'You can listen to %s on %s.',
            // Lyrics
            'where_find_lyrics' => 'Where can I find the complete lyrics of %s?',
            'find_lyrics_answer' => 'You can find the complete lyrics and translations of %s on this page. The lyrics are available in both the original language and various translations.',
            'and' => 'and',
            'more' => 'more',
        ),
        'es' => array(
            'faq_title' => 'Preguntas Frecuentes Sobre %s',
            'have_more' => '¿Tienes más preguntas? No dudes en contactarnos a través de la sección de comentarios.',
            // Release date
            'when_released' => '¿Cuándo se lanzó %s?',
            'was_released_on' => '%s fue lanzado el %s.',
            // Singer
            'who_sings' => '¿Quién canta %s?',
            'who_is_singer' => '¿Quién es el cantante de %s?',
            'is_performed_by' => '%s es interpretado por %s.',
            'is_sung_by' => '%s es cantado por %s.',
            // Language
            'what_is_original_lang' => '¿Cuál es el idioma original de %s?',
            'original_lang_is' => 'El idioma original de %s es %s.',
            'what_langs_available' => '¿En qué idiomas están disponibles las traducciones de %s?',
            'what_lang_available' => '¿En qué idioma está disponible la traducción de %s?',
            'translations_available_in' => 'Las traducciones de %s están disponibles en %s.',
            // Album
            'which_album' => '¿De qué álbum es %s?',
            'is_from_album' => '%s es del álbum "%s"%s.',
            // Genre
            'what_genre' => '¿Qué género es %s?',
            'is_genre_song' => '%s es una canción de %s.',
            // Platforms
            'where_listen' => '¿Dónde puedo escuchar %s?',
            'can_listen_on' => 'Puedes escuchar %s en %s.',
            // Lyrics
            'where_find_lyrics' => '¿Dónde puedo encontrar la letra completa de %s?',
            'find_lyrics_answer' => 'Puedes encontrar la letra completa y las traducciones de %s en esta página. La letra está disponible tanto en el idioma original como en varias traducciones.',
            'and' => 'y',
            'more' => 'más',
        ),
        'tr' => array(
            'faq_title' => '%s Hakkında Sık Sorulan Sorular',
            'have_more' => 'Daha fazla sorunuz mu var? Yorum bölümünden bize ulaşabilirsiniz.',
            // Release date
            'when_released' => '%s ne zaman yayınlandı?',
            'was_released_on' => '%s, %s tarihinde yayınlandı.',
            // Singer
            'who_sings' => '%s şarkısını kim söylüyor?',
            'who_is_singer' => '%s şarkısının sanatçısı kim?',
            'is_performed_by' => '%s, %s tarafından seslendirilmiştir.',
            'is_sung_by' => '%s, %s tarafından söylenmiştir.',
            // Language
            'what_is_original_lang' => '%s şarkısının orijinal dili nedir?',
            'original_lang_is' => '%s şarkısının orijinal dili %s.',
            'what_langs_available' => '%s çevirileri hangi dillerde mevcut?',
            'what_lang_available' => '%s çevirisi hangi dilde mevcut?',
            'translations_available_in' => '%s çevirileri %s dillerinde mevcuttur.',
            // Album
            'which_album' => '%s hangi albümde yer alıyor?',
            'is_from_album' => '%s, "%s" albümünden%s.',
            // Genre
            'what_genre' => '%s şarkısının türü nedir?',
            'is_genre_song' => '%s bir %s şarkısıdır.',
            // Platforms
            'where_listen' => '%s şarkısını nerede dinleyebilirim?',
            'can_listen_on' => '%s şarkısını %s üzerinden dinleyebilirsiniz.',
            // Lyrics
            'where_find_lyrics' => '%s şarkısının tam sözlerini nerede bulabilirim?',
            'find_lyrics_answer' => '%s şarkısının tam sözlerini ve çevirilerini bu sayfada bulabilirsiniz. Şarkı sözleri hem orijinal dilde hem de çeşitli dillerde mevcuttur.',
            'and' => 've',
            'more' => 'daha fazla',
        ),
    );

    $lang = isset($translations[$lang_code]) ? $lang_code : 'en';
    $template = isset($translations[$lang][$key]) ? $translations[$lang][$key] : $translations['en'][$key];

    if (!empty($args)) {
        return vsprintf($template, $args);
    }

    return $template;
}

/**
 * FAQ HTML çıktısını oluştur
 */
function gufte_render_faq_html($faq_items, $post_id, $lang_code = 'en') {
    $song_title = get_the_title($post_id);
    $faq_id = 'faq-' . $post_id;

    // FAQ Schema.org JSON-LD oluştur
    $faq_schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array()
    );

    foreach ($faq_items as $item) {
        $faq_schema['mainEntity'][] = array(
            '@type' => 'Question',
            'name' => $item['question'],
            'acceptedAnswer' => array(
                '@type' => 'Answer',
                'text' => $item['answer']
            )
        );
    }

    ob_start();
    ?>
    <!-- FAQ Schema.org JSON-LD -->
    <script type="application/ld+json">
    <?php echo wp_json_encode($faq_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>
    </script>

    <div class="auto-faq-section mb-8 p-6 bg-white rounded-lg shadow-md border border-gray-200">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 pb-4 border-b border-gray-200 flex items-center">
            <?php gufte_icon('help-circle', 'mr-3 text-primary-600 text-3xl'); ?>
            <?php echo esc_html(gufte_get_faq_translation('faq_title', $lang_code, array($song_title))); ?>
        </h2>
        
        <div class="faq-container" id="<?php echo esc_attr($faq_id); ?>">
            <?php foreach ($faq_items as $index => $item) : 
                $item_id = $faq_id . '-item-' . $index;
            ?>
            <div class="faq-item border border-gray-200 rounded-lg mb-4 overflow-hidden transition-all duration-300 hover:shadow-md">
                <button class="faq-question w-full text-left p-4 bg-gray-50 hover:bg-gray-100 transition-colors duration-200 flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-inset" 
                        type="button" 
                        data-faq-toggle="<?php echo esc_attr($item_id); ?>"
                        aria-expanded="false"
                        aria-controls="<?php echo esc_attr($item_id); ?>">
                    <span class="font-semibold text-gray-800 pr-4"><?php echo esc_html($item['question']); ?></span>
                    <span class="faq-icon flex-shrink-0 transition-transform duration-200 text-primary-600" data-icon-collapsed="mdi:chevron-down" data-icon-expanded="mdi:chevron-up">
                        <?php gufte_icon('chevron-down', ''); ?>
                    </span>
                </button>
                <div class="faq-answer hidden p-4 bg-white border-t border-gray-200" id="<?php echo esc_attr($item_id); ?>" role="region">
                    <p class="text-gray-700 leading-relaxed"><?php echo esc_html($item['answer']); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="faq-footer mt-6 pt-4 border-t border-gray-200 text-center">
            <p class="text-sm text-gray-500">
                <?php gufte_icon('information', 'mr-1'); ?>
                <?php echo esc_html(gufte_get_faq_translation('have_more', $lang_code)); ?>
            </p>
        </div>
    </div>
    
    <?php
    // FAQ JavaScript'i ekle
    gufte_add_faq_scripts($faq_id);
    
    return ob_get_clean();
}

/**
 * FAQ JavaScript kodlarını ekle
 * Note: FAQ JavaScript is now handled in frontend.js
 * This function is kept for backwards compatibility but does nothing
 */
function gufte_add_faq_scripts($faq_id) {
    // FAQ JavaScript is now in frontend.js - no need to add inline scripts
    return;
}
