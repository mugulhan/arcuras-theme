<?php
/**
 * Gufte Theme - Sitemap Generator Admin Page
 *
 * Bu dosyayı inc/admin-sitemap-page.php olarak kaydedin
 *
 * @package Gufte
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<style>
/* Notification Popup Styles */
.gufte-notification-popup {
    position: fixed;
    bottom: 20px;
    right: 20px;
    min-width: 300px;
    max-width: 400px;
    background: white;
    border-left: 4px solid #46b450;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border-radius: 4px;
    padding: 15px 40px 15px 15px;
    z-index: 999999;
    animation: slideInRight 0.3s ease-out;
}

.gufte-notification-popup.error {
    border-left-color: #dc3232;
}

.gufte-notification-popup.warning {
    border-left-color: #ffb900;
}

.gufte-notification-popup p {
    margin: 0;
    font-size: 14px;
    line-height: 1.5;
}

.gufte-notification-popup .notice-dismiss {
    position: absolute;
    top: 8px;
    right: 8px;
    border: none;
    background: none;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    color: #787c82;
}

.gufte-notification-popup .notice-dismiss:hover {
    color: #dc3232;
}

.gufte-notification-popup .notice-dismiss::before {
    content: '\f153';
    font-family: dashicons;
    font-size: 20px;
    line-height: 24px;
    display: block;
}

@keyframes slideInRight {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOutRight {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}

.gufte-notification-popup.closing {
    animation: slideOutRight 0.3s ease-in forwards;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Move notices to bottom right as popup
    $('.notice.is-dismissible').each(function() {
        var $notice = $(this);
        var type = 'success';

        if ($notice.hasClass('notice-error')) {
            type = 'error';
        } else if ($notice.hasClass('notice-warning')) {
            type = 'warning';
        }

        // Clone notice content
        var content = $notice.find('p').html();

        // Create popup
        var $popup = $('<div class="gufte-notification-popup ' + type + '">' +
            '<p>' + content + '</p>' +
            '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss</span></button>' +
            '</div>');

        // Add to body
        $('body').append($popup);

        // Remove original notice
        $notice.remove();

        // Auto close after 5 seconds
        setTimeout(function() {
            closePopup($popup);
        }, 5000);

        // Manual close
        $popup.find('.notice-dismiss').on('click', function() {
            closePopup($popup);
        });
    });

    function closePopup($popup) {
        $popup.addClass('closing');
        setTimeout(function() {
            $popup.remove();
        }, 300);
    }
});
</script>

<div class="wrap">
    <h1>🗺️ Gufte Sitemap Generator</h1>
    
    <!-- Genel Bilgiler -->
    <div class="card">
        <h2>Sitemap Durumu</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
            <div style="background: #d4edda; padding: 15px; border-radius: 8px; text-align: center;">
                <h3 style="margin: 0 0 10px 0; color: #155724;">📝 Lyrics</h3>
                <p style="font-size: 24px; font-weight: bold; margin: 0; color: #155724;"><?php echo number_format($posts_count); ?></p>
            </div>
            
            <div style="background: #cce5ff; padding: 15px; border-radius: 8px; text-align: center;">
                <h3 style="margin: 0 0 10px 0; color: #004085;">📄 Pages</h3>
                <p style="font-size: 24px; font-weight: bold; margin: 0; color: #004085;"><?php echo number_format($pages_count); ?></p>
            </div>
            
            <div style="background: #fff3cd; padding: 15px; border-radius: 8px; text-align: center;">
                <h3 style="margin: 0 0 10px 0; color: #856404;">📂 Categories</h3>
                <p style="font-size: 24px; font-weight: bold; margin: 0; color: #856404;"><?php echo number_format($categories_count); ?></p>
            </div>
            
            <div style="background: #f8d7da; padding: 15px; border-radius: 8px; text-align: center;">
                <h3 style="margin: 0 0 10px 0; color: #721c24;">🎤 Singers</h3>
                <p style="font-size: 24px; font-weight: bold; margin: 0; color: #721c24;"><?php echo number_format($singers_count); ?></p>
            </div>
            
            <div style="background: #e2e3e5; padding: 15px; border-radius: 8px; text-align: center;">
                <h3 style="margin: 0 0 10px 0; color: #383d41;">💿 Albums</h3>
                <p style="font-size: 24px; font-weight: bold; margin: 0; color: #383d41;"><?php echo number_format($albums_count); ?></p>
            </div>

            <div style="background: #d1ecf1; padding: 15px; border-radius: 8px; text-align: center;">
                <h3 style="margin: 0 0 10px 0; color: #0c5460;">✍️ Songwriters</h3>
                <p style="font-size: 24px; font-weight: bold; margin: 0; color: #0c5460;"><?php echo number_format($songwriters_count); ?></p>
            </div>
        </div>
        
        <h3>🔗 Sitemap Links</h3>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
            <ul style="list-style: none; padding: 0; margin: 0;">
                <li style="margin-bottom: 8px;">
                    <strong>📋 Main Index:</strong> 
                    <a href="<?php echo home_url('/gufte-sitemap.xml'); ?>" target="_blank"><?php echo home_url('/gufte-sitemap.xml'); ?></a>
                </li>
                <li style="margin-bottom: 8px;">
                    <strong>📝 Lyrics (includes translations):</strong>
                    <a href="<?php echo home_url('/gufte-sitemap-posts.xml'); ?>" target="_blank"><?php echo home_url('/gufte-sitemap-posts.xml'); ?></a>
                    <span style="color: #10b981; font-size: 12px; margin-left: 10px;">✓ /tr/, /es/ URLs included</span>
                </li>
                <li style="margin-bottom: 8px;">
                    <strong>📄 Pages:</strong> 
                    <a href="<?php echo home_url('/gufte-sitemap-pages.xml'); ?>" target="_blank"><?php echo home_url('/gufte-sitemap-pages.xml'); ?></a>
                </li>
                <li style="margin-bottom: 8px;">
                    <strong>📂 Categories:</strong> 
                    <a href="<?php echo home_url('/gufte-sitemap-categories.xml'); ?>" target="_blank"><?php echo home_url('/gufte-sitemap-categories.xml'); ?></a>
                </li>
                <li style="margin-bottom: 8px;">
                    <strong>🎤 Singers:</strong> 
                    <a href="<?php echo home_url('/gufte-sitemap-singers.xml'); ?>" target="_blank"><?php echo home_url('/gufte-sitemap-singers.xml'); ?></a>
                </li>
                <li style="margin-bottom: 8px;">
                    <strong>💿 Albums:</strong>
                    <a href="<?php echo home_url('/gufte-sitemap-albums.xml'); ?>" target="_blank"><?php echo home_url('/gufte-sitemap-albums.xml'); ?></a>
                </li>
                <li style="margin-bottom: 0;">
                    <strong>✍️ Songwriters:</strong>
                    <a href="<?php echo home_url('/gufte-sitemap-songwriters.xml'); ?>" target="_blank"><?php echo home_url('/gufte-sitemap-songwriters.xml'); ?></a>
                </li>
            </ul>
        </div>
    </div>
    
    <!-- Cache Yönetimi - GELİŞTİRİLMİŞ -->
    <div class="card">
        <h2>🔄 Sitemap Yönetimi</h2>
        <p>Sitemap'leri yeniden oluşturmak, cache'i temizlemek ve arama motorlarına bildirmek için aşağıdaki butonları kullanın.</p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin: 20px 0;">
            <!-- Hızlı Cache Temizle -->
            <div style="background: #e7f3ff; padding: 15px; border-radius: 8px;">
                <h4 style="margin-top: 0;">⚡ Hızlı Güncelleme</h4>
                <form method="post" style="margin: 0;">
                    <?php wp_nonce_field('gufte_sitemap_clear_cache'); ?>
                    <input type="hidden" name="clear_sitemap_cache" value="1">
                    <p class="submit" style="margin: 10px 0 0 0;">
                        <input type="submit" class="button button-primary" value="🔄 Cache Temizle">
                    </p>
                </form>
                <small>Sitemap'leri hemen yeniden oluşturur</small>
            </div>
            
            <!-- Manuel Tetikleme -->
            <div style="background: #fff3cd; padding: 15px; border-radius: 8px;">
                <h4 style="margin-top: 0;">🚀 Tam Güncelleme</h4>
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="margin: 0;">
                    <input type="hidden" name="action" value="trigger_sitemap_update">
                    <?php wp_nonce_field('manual_sitemap_update'); ?>
                    <p class="submit" style="margin: 10px 0 0 0;">
                        <input type="submit" class="button button-secondary" value="🔥 Tam Yenile">
                    </p>
                </form>
                <small>Cache temizle + Arama motorlarına bildir</small>
            </div>
            
            <!-- Dil Tespiti -->
            <div style="background: #d4edda; padding: 15px; border-radius: 8px;">
                <h4 style="margin-top: 0;">🌍 Dil Güncelleme</h4>
                <form method="post" style="margin: 0;">
                    <?php wp_nonce_field('gufte_sitemap_update_languages'); ?>
                    <input type="hidden" name="update_all_languages" value="1">
                    <p class="submit" style="margin: 10px 0 0 0;">
                        <input type="submit" class="button button-secondary" value="🔍 Dil Tespit">
                    </p>
                </form>
                <small>Tüm dilleri yeniden tespit et</small>
            </div>
        </div>
        
        <!-- Son Güncelleme Bilgisi -->
        <?php
        $last_trigger = get_transient('gufte_sitemap_last_trigger');
        $last_update = $last_trigger ? human_time_diff($last_trigger, time()) . ' ago' : 'Never';
        ?>
        
        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 15px;">
            <h4 style="margin-top: 0;">📊 Güncelleme Bilgileri</h4>
            <ul style="margin: 0; padding-left: 20px;">
                <li><strong>Son otomatik güncelleme:</strong> <?php echo esc_html($last_update); ?></li>
                <li><strong>Cache durumu:</strong> 
                    <?php 
                    $cache_exists = wp_cache_get('gufte_sitemap_index', 'gufte_sitemaps');
                    echo $cache_exists ? '<span style="color: #00a32a;">Aktif</span>' : '<span style="color: #d63638;">Boş</span>';
                    ?>
                </li>
                <li><strong>Toplam sitemap sayısı:</strong> 7 adet</li>
            </ul>
        </div>
        
        <!-- Otomatik Tetikleme Durumu -->
        <div style="background: #e2e3e5; padding: 15px; border-radius: 8px; margin-top: 15px;">
            <h4 style="margin-top: 0;">🤖 Otomatik Tetikleme Durumu</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                <div>
                    <strong>✅ Aktif Tetiklemeler:</strong>
                    <ul style="font-size: 12px; margin: 5px 0; padding-left: 15px;">
                        <li>Post kaydet/sil/güncelle</li>
                        <li>Kategori/tag değişiklikleri</li>
                        <li>Şarkıcı/albüm güncellemeleri</li>
                        <li>Dil bilgisi değişiklikleri</li>
                    </ul>
                </div>
                <div>
                    <strong>⏱️ Gecikmeli Tetiklemeler:</strong>
                    <ul style="font-size: 12px; margin: 5px 0; padding-left: 15px;">
                        <li>Featured image değişimi</li>
                        <li>Meta veri güncellemeleri</li>
                        <li>Yorum onay/reddi (10dk)</li>
                        <li>Bulk edit işlemleri</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Çok Dilli Yönetim -->
    <div class="card">
        <h2>🌍 Çok Dilli Sitemap Yönetimi</h2>
        
        <?php
        // Dil istatistikleri
        $posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => -1
        ));
        
        $language_stats = array();
        $posts_without_languages = 0;
        $language_settings = array(
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
            )
        );
        
        foreach ($posts as $post) {
            $available_languages = get_post_meta($post->ID, '_available_languages', true);
            
            if (empty($available_languages) || !is_array($available_languages)) {
                $posts_without_languages++;
            } else {
                foreach ($available_languages as $lang) {
                    if (array_key_exists($lang, $language_settings['language_map'])) {
                        $language_stats[$lang] = isset($language_stats[$lang]) ? $language_stats[$lang] + 1 : 1;
                    }
                }
            }
        }
        
        arsort($language_stats);
        ?>
        
        <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <h3 style="margin-top: 0;">📊 Dil İstatistikleri</h3>
            
            <?php if (!empty($language_stats)) : ?>
            <table class="wp-list-table widefat fixed striped" style="margin-top: 15px;">
                <thead>
                    <tr>
                        <th>Dil</th>
                        <th>Yazı Sayısı</th>
                        <th>Yüzde</th>
                        <th>Sitemap URL'leri</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($language_stats as $lang => $count) : ?>
                    <tr>
                        <td><strong><?php echo esc_html($language_settings['language_map'][$lang]); ?></strong></td>
                        <td><?php echo number_format($count); ?></td>
                        <td><?php echo round(($count / count($posts)) * 100, 1); ?>%</td>
                        <td>
                            <?php if ($lang === 'english') : ?>
                                <span style="color: #666;">Original URLs</span>
                            <?php else : ?>
                                <code>?lang=<?php echo $lang; ?></code>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
            
            <?php if ($posts_without_languages > 0) : ?>
            <div style="background: #f8d7da; padding: 15px; border-radius: 8px; margin-top: 15px;">
                <p style="margin: 0;"><strong>⚠️ Uyarı:</strong> <?php echo $posts_without_languages; ?> yazının dil bilgisi tanımlanmamış.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Arama Motoru Entegrasyonu -->
    <div class="card">
        <h2>🔍 Arama Motoru Entegrasyonu</h2>
        
        <h3>Google Search Console</h3>
        <p>Sitemap'lerinizi Google Search Console'a eklemek için:</p>
        <ol>
            <li><a href="https://search.google.com/search-console" target="_blank">Google Search Console</a>'a gidin</li>
            <li>Sitenizi seçin</li>
            <li>Sol menüden "Sitemaps" seçeneğine tıklayın</li>
            <li>Aşağıdaki URL'leri ekleyin:</li>
        </ol>
        
        <div style="background: #e7f3ff; padding: 15px; border-radius: 8px; margin: 15px 0;">
            <ul style="list-style: none; padding: 0; margin: 0; font-family: monospace;">
                <li style="margin-bottom: 5px;"><strong>Ana Index:</strong> <code>gufte-sitemap.xml</code></li>
                <li style="margin-bottom: 5px;"><strong>Çok Dilli:</strong> <code style="text-decoration: line-through; opacity: 0.5;">gufte-sitemap-multilingual.xml</code> <span style="color: #ef4444; font-size: 12px;">(Removed - translations now in posts sitemap)</span></li>
                <li style="margin-bottom: 5px;"><strong>Posts:</strong> <code>gufte-sitemap-posts.xml</code></li>
                <li><strong>Kategoriler:</strong> <code>gufte-sitemap-categories.xml</code></li>
            </ul>
        </div>
        
        <h3>Manuel Ping Gönderimi</h3>
        <p>Sitemap'leri arama motorlarına manuel olarak bildirmek için:</p>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">
            <div style="background: #f0f0f1; padding: 15px; border-radius: 8px;">
                <h4>Google</h4>
                <p><small>Aşağıdaki URL'leri tarayıcınızda açın:</small></p>
                <ul style="font-size: 11px; word-break: break-all;">
                    <li><a href="http://www.google.com/ping?sitemap=<?php echo urlencode(home_url('/gufte-sitemap.xml')); ?>" target="_blank">Main Sitemap (translations included in posts)</a></li>
                </ul>
            </div>

            <div style="background: #f0f0f1; padding: 15px; border-radius: 8px;">
                <h4>Bing</h4>
                <p><small>Aşağıdaki URL'leri tarayıcınızda açın:</small></p>
                <ul style="font-size: 11px; word-break: break-all;">
                    <li><a href="http://www.bing.com/ping?sitemap=<?php echo urlencode(home_url('/gufte-sitemap.xml')); ?>" target="_blank">Main Sitemap (translations included in posts)</a></li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Sitemap Validasyon -->
    <div class="card">
        <h2>✅ Sitemap Validasyon</h2>
        <p>Sitemap'lerinizi test etmek ve hatalarını kontrol etmek için:</p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 15px;">
            <div style="background: #d1ecf1; padding: 15px; border-radius: 8px;">
                <h4>🔍 XML Sitemap Validator</h4>
                <ul>
                    <li><a href="https://www.xml-sitemaps.com/validate-xml-sitemap.html?url=<?php echo urlencode(home_url('/gufte-sitemap.xml')); ?>" target="_blank">Main Index Validate</a></li>
                    <li><a href="https://www.xml-sitemaps.com/validate-xml-sitemap.html?url=<?php echo urlencode(home_url('/gufte-sitemap-posts.xml')); ?>" target="_blank">Posts Sitemap (with translations)</a></li>
                </ul>
            </div>
            
            <div style="background: #d4edda; padding: 15px; border-radius: 8px;">
                <h4>🌐 Hreflang Testing</h4>
                <ul>
                    <li><a href="https://support.google.com/webmasters/answer/189077" target="_blank">Google Hreflang Guide</a></li>
                    <li><a href="https://www.sistrix.com/hreflang-checker/" target="_blank">Hreflang Checker</a></li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Robots.txt Kontrolü -->
    <div class="card">
        <h2>🤖 Robots.txt Kontrolü</h2>
        <p>Robots.txt dosyanızda sitemap'lerinizin doğru şekilde tanımlandığından emin olun:</p>
        
        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;">
            <h4>Beklenen İçerik:</h4>
            <pre style="background: #e9ecef; padding: 15px; border-radius: 4px; overflow-x: auto;"><code># Gufte Theme Sitemap (translations included in posts)
Sitemap: <?php echo home_url('/gufte-sitemap.xml'); ?></code></pre>
        </div>
        
        <p>
            <a href="<?php echo home_url('/robots.txt'); ?>" target="_blank" class="button button-secondary">📄 Robots.txt'i Görüntüle</a>
            <span class="description" style="margin-left: 15px;">Mevcut robots.txt dosyanızı kontrol edin</span>
        </p>
    </div>
    
    <!-- Sorun Giderme -->
    <div class="card">
        <h2>🛠️ Sorun Giderme</h2>
        
        <div style="background: #fff3cd; padding: 15px; border-radius: 8px;">
            <h3>Sık Karşılaşılan Sorunlar</h3>
            
            <div style="margin-bottom: 20px;">
                <h4>❓ Sitemap'ler yüklenmiyor (404 hatası)</h4>
                <p><strong>Çözüm:</strong> WordPress Admin → Ayarlar → Kalıcı Bağlantılar sayfasına gidin ve "Değişiklikleri Kaydet" butonuna tıklayın. Bu permalink'leri yeniler.</p>
            </div>
            
            <div style="margin-bottom: 20px;">
                <h4>❓ Çok dilli URL'ler çalışmıyor</h4>
                <p><strong>Çözüm:</strong> "Tüm Dilleri Otomatik Tespit Et" butonuna tıklayın. Sonra cache'i temizleyin.</p>
            </div>
            
            <div style="margin-bottom: 20px;">
                <h4>❓ Google Search Console'da hreflang hataları</h4>
                <p><strong>Çözüm:</strong> Dil bilgilerini güncelleyin ve multilingual sitemap'i yeniden gönderin.</p>
            </div>
            
            <div>
                <h4>❓ Sitemap'ler güncellenmiyor</h4>
                <p><strong>Çözüm:</strong> Cache'i temizleyin. Eğer sorun devam ederse, hosting sağlayıcınızın cache sistemini kontrol edin.</p>
            </div>
        </div>
    </div>
</div>

<?php
// URL güncellemelerini kontrol edelim
if (isset($_GET['updated'])) {
    $update_type = sanitize_text_field($_GET['updated']);
    
    switch ($update_type) {
        case 'manual_trigger':
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p><strong>✅ Başarılı!</strong> Sitemap manuel olarak güncellendi ve arama motorlarına bildirildi.</p>';
            echo '</div>';
            break;
    }
}
?>

<style>
/* Ana wrapper için grid layout */
.wrap {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 20px;
    grid-template-areas: 
        "header header"
        "status management"
        "multilingual multilingual"
        "search search"
        "validation validation"
        "robots robots"
        "troubleshoot troubleshoot";
}

/* Başlık tam genişlik */
.wrap > h1 {
    grid-area: header;
    grid-column: 1 / -1;
}

/* Kartların grid alanları */
.card:nth-of-type(1) { grid-area: status; }      /* Sitemap Durumu */
.card:nth-of-type(2) { grid-area: management; }  /* Sitemap Yönetimi */
.card:nth-of-type(3) { grid-area: multilingual; }/* Çok Dilli */
.card:nth-of-type(4) { grid-area: search; }      /* Arama Motoru */
.card:nth-of-type(5) { grid-area: validation; }  /* Validasyon */
.card:nth-of-type(6) { grid-area: robots; }      /* Robots.txt */
.card:nth-of-type(7) { grid-area: troubleshoot; }/* Sorun Giderme */

.card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    margin-bottom: 0; /* Grid kullandığımız için margin'i kaldır */
    padding: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
    min-height: fit-content;
}

.card h2 {
    margin-top: 0;
    font-size: 1.3em;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
    display: flex;
    align-items: center;
}

.card h3 {
    color: #23282d;
    font-size: 1.1em;
    margin-bottom: 10px;
}

.card h4 {
    color: #555;
    font-size: 1em;
    margin-bottom: 5px;
    margin-top: 15px;
}

.card code {
    background: #f1f1f1;
    padding: 2px 4px;
    border-radius: 3px;
    font-family: Consolas, Monaco, monospace;
    font-size: 12px;
}

.card ul li {
    margin-bottom: 5px;
}

/* Tablet ve küçük ekranlar için */
@media (max-width: 1200px) {
    .wrap {
        grid-template-columns: 1fr;
        grid-template-areas: 
            "header"
            "status"
            "management"
            "multilingual"
            "search"
            "validation"
            "robots"
            "troubleshoot";
    }
}

/* Mobil ekranlar için */
@media (max-width: 768px) {
    .wrap {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .card {
        padding: 15px;
    }
    
    .card div[style*="grid"] {
        grid-template-columns: 1fr !important;
    }
    
    /* Mobilde buton grid'leri de tek sütun yap */
    .card div[style*="display: grid"] {
        grid-template-columns: 1fr !important;
    }
}

/* Çok geniş ekranlar için */
@media (min-width: 1600px) {
    .wrap {
        grid-template-columns: repeat(3, 1fr);
        grid-template-areas: 
            "header header header"
            "status management multilingual"
            "search search search"
            "validation robots troubleshoot";
    }
}
</style>