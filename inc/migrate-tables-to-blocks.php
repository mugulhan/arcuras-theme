<?php
/**
 * Migrate Table Lyrics to Gutenberg Block
 * Converts wp:table blocks to arcuras/lyrics-translations blocks
 *
 * @package Gufte
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Parse table HTML and extract languages and lyrics
 */
function arcuras_parse_table_lyrics($table_html) {
    $languages = array();

    // Extract table headers (language names)
    preg_match('/<thead>(.*?)<\/thead>/s', $table_html, $header_matches);
    if (!empty($header_matches[1])) {
        preg_match_all('/<th[^>]*>(.*?)<\/th>/s', $header_matches[1], $lang_matches);
        if (!empty($lang_matches[1])) {
            foreach ($lang_matches[1] as $index => $lang_name) {
                $lang_name = trim(strip_tags($lang_name));

                // Handle dual language headers (e.g., "English, Russian")
                // Take only the first language
                if (strpos($lang_name, ',') !== false) {
                    $lang_parts = explode(',', $lang_name);
                    $lang_name = trim($lang_parts[0]);
                }

                // Map language names to codes
                $lang_code_map = array(
                    'English' => 'en',
                    'Spanish' => 'es',
                    'Turkish' => 'tr',
                    'Arabic' => 'ar',
                    'German' => 'de',
                    'Russian' => 'ru',
                    'Japanese' => 'ja',
                    'Korean' => 'ko',
                    'French' => 'fr',
                    'Italian' => 'it',
                    'Portuguese' => 'pt'
                );

                $lang_code = isset($lang_code_map[$lang_name]) ? $lang_code_map[$lang_name] : strtolower(substr($lang_name, 0, 2));

                $languages[$index] = array(
                    'code' => $lang_code,
                    'name' => $lang_name,
                    'lyrics' => array(),
                    'isOriginal' => $index === 0 // First language is original
                );
            }
        }
    }

    // Extract table body (lyrics lines)
    preg_match('/<tbody>(.*?)<\/tbody>/s', $table_html, $body_matches);
    if (!empty($body_matches[1])) {
        preg_match_all('/<tr[^>]*>(.*?)<\/tr>/s', $body_matches[1], $row_matches);
        if (!empty($row_matches[1])) {
            foreach ($row_matches[1] as $row_html) {
                preg_match_all('/<td[^>]*>(.*?)<\/td>/s', $row_html, $cell_matches);
                if (!empty($cell_matches[1])) {
                    foreach ($cell_matches[1] as $cell_index => $cell_content) {
                        // Replace <br> tags with newlines before stripping
                        $cell_content = preg_replace('/<br\s*\/?>/i', "\n", $cell_content);
                        // Remove all other HTML tags
                        $line = trim(strip_tags($cell_content));
                        // Only add non-empty lines
                        if (!empty($line) && isset($languages[$cell_index])) {
                            $languages[$cell_index]['lyrics'][] = $line;
                        }
                    }
                }
            }
        }
    }

    // Convert lyrics arrays to string with proper newlines
    foreach ($languages as $index => $lang) {
        if (!empty($lang['lyrics'])) {
            // Join with actual newline character
            $languages[$index]['lyrics'] = implode("\n", $lang['lyrics']);
        } else {
            $languages[$index]['lyrics'] = '';
        }
    }

    return array_values($languages);
}

/**
 * Convert table block to lyrics-translations block
 */
function arcuras_convert_table_to_lyrics_block($content) {
    // Find all table blocks
    if (!has_blocks($content)) {
        return $content;
    }

    $blocks = parse_blocks($content);
    $converted = false;

    foreach ($blocks as $index => $block) {
        if ($block['blockName'] === 'core/table') {
            // Parse the table
            $table_html = render_block($block);
            $languages = arcuras_parse_table_lyrics($table_html);

            if (!empty($languages)) {
                // Create new lyrics-translations block
                $new_block = array(
                    'blockName' => 'arcuras/lyrics-translations',
                    'attrs' => array(
                        'languages' => $languages
                    ),
                    'innerBlocks' => array(),
                    'innerHTML' => '',
                    'innerContent' => array()
                );

                $blocks[$index] = $new_block;
                $converted = true;
            }
        }
    }

    if ($converted) {
        return serialize_blocks($blocks);
    }

    return $content;
}

/**
 * Rollback migration - Convert lyrics-translations blocks back to tables
 */
function arcuras_rollback_migration() {
    // Temporarily disable save_post hooks to prevent AJAX responses
    remove_action('save_post', 'gufte_auto_fetch_music_cover_art', 20);
    remove_action('save_post', 'gufte_save_music_cover_art_meta_data');

    $args = array(
        'post_type' => 'lyrics',
        'post_status' => 'any',
        'posts_per_page' => -1,
        'orderby' => 'ID',
        'order' => 'ASC'
    );

    $posts = get_posts($args);
    $rolled_back = 0;

    foreach ($posts as $post) {
        $content = $post->post_content;

        // Check if has lyrics-translations block
        if (strpos($content, 'arcuras/lyrics-translations') !== false) {
            // Remove the block (this will force re-migration)
            $blocks = parse_blocks($content);
            $new_blocks = array();

            foreach ($blocks as $block) {
                if ($block['blockName'] !== 'arcuras/lyrics-translations') {
                    $new_blocks[] = $block;
                }
            }

            $new_content = serialize_blocks($new_blocks);

            if ($new_content !== $content) {
                wp_update_post(array(
                    'ID' => $post->ID,
                    'post_content' => $new_content
                ), true);
                $rolled_back++;
            }
        }
    }

    // Re-enable hooks
    add_action('save_post', 'gufte_auto_fetch_music_cover_art', 20);
    add_action('save_post', 'gufte_save_music_cover_art_meta_data');

    return $rolled_back;
}

/**
 * Migrate all lyrics posts
 */
function arcuras_migrate_all_lyrics_tables() {
    $args = array(
        'post_type' => 'lyrics',
        'post_status' => 'any',
        'posts_per_page' => -1,
        'orderby' => 'ID',
        'order' => 'ASC'
    );

    $posts = get_posts($args);
    $migrated = 0;
    $skipped = 0;
    $errors = 0;
    $details = array();

    foreach ($posts as $post) {
        $content = $post->post_content;
        $post_info = array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'status' => '',
            'message' => ''
        );

        // Check if post has table blocks
        if (strpos($content, 'wp:table') === false) {
            $post_info['status'] = 'skipped';
            $post_info['message'] = 'No table block found';
            $skipped++;
            $details[] = $post_info;
            continue;
        }

        // Check if already has lyrics-translations block
        if (strpos($content, 'arcuras/lyrics-translations') !== false) {
            $post_info['status'] = 'skipped';
            $post_info['message'] = 'Already has lyrics-translations block';
            $skipped++;
            $details[] = $post_info;
            continue;
        }

        // Convert tables to lyrics blocks
        $new_content = arcuras_convert_table_to_lyrics_block($content);

        if ($new_content !== $content) {
            // Temporarily disable save_post hooks to prevent AJAX responses
            remove_action('save_post', 'gufte_auto_fetch_music_cover_art', 20);
            remove_action('save_post', 'gufte_save_music_cover_art_meta_data');

            // Remove KSES filters temporarily to allow block HTML
            $has_filter = false;
            if (has_filter('content_save_pre', 'wp_filter_post_kses')) {
                $has_filter = true;
                kses_remove_filters();
            }

            // Use direct SQL update to preserve newlines
            global $wpdb;
            $result = $wpdb->update(
                $wpdb->posts,
                array('post_content' => $new_content),
                array('ID' => $post->ID),
                array('%s'),
                array('%d')
            );

            if ($has_filter) {
                kses_init_filters();
            }

            // Re-enable hooks for next post
            add_action('save_post', 'gufte_auto_fetch_music_cover_art', 20);
            add_action('save_post', 'gufte_save_music_cover_art_meta_data');

            if (is_wp_error($result)) {
                $post_info['status'] = 'error';
                $post_info['message'] = $result->get_error_message();
                $errors++;
            } else {
                $post_info['status'] = 'success';
                $post_info['message'] = 'Successfully migrated';
                $migrated++;
            }
        } else {
            $post_info['status'] = 'skipped';
            $post_info['message'] = 'No changes detected';
            $skipped++;
        }

        $details[] = $post_info;
    }

    return array(
        'total' => count($posts),
        'migrated' => $migrated,
        'skipped' => $skipped,
        'errors' => $errors,
        'details' => $details
    );
}

/**
 * Admin page for migration
 * DISABLED - Migration completed, no longer needed in menu
 */
function arcuras_migration_admin_page() {
    add_submenu_page(
        'edit.php?post_type=lyrics',
        'Migrate Tables to Blocks',
        'Migrate Tables',
        'manage_options',
        'migrate-tables',
        'arcuras_migration_page_content'
    );
}
// DISABLED - Migration tool removed from menu
// add_action('admin_menu', 'arcuras_migration_admin_page');

/**
 * Migration page content
 */
function arcuras_migration_page_content() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

    $migrated = false;
    $rolled_back = false;
    $results = null;
    $rollback_count = 0;

    // Handle rollback
    if (isset($_POST['rollback_migration']) && check_admin_referer('rollback_migration_action', 'rollback_migration_nonce')) {
        set_time_limit(300); // 5 minutes

        // Start output buffering to catch any AJAX responses
        ob_start();
        $rollback_count = arcuras_rollback_migration();
        // Discard any output
        ob_end_clean();

        $rolled_back = true;
    }

    // Handle form submission
    if (isset($_POST['migrate_tables']) && check_admin_referer('migrate_tables_action', 'migrate_tables_nonce')) {
        set_time_limit(300); // 5 minutes

        // Start output buffering to catch any AJAX responses
        ob_start();
        $results = arcuras_migrate_all_lyrics_tables();
        // Discard any output
        ob_end_clean();

        $migrated = true;
    }

    ?>
    <div class="wrap">
        <h1>🎵 Migrate Table Lyrics to Gutenberg Blocks</h1>

        <?php if ($rolled_back): ?>
            <div class="notice notice-info is-dismissible" style="padding: 15px; margin: 20px 0;">
                <h2 style="margin-top: 0;">🔄 Rollback Complete!</h2>
                <p><strong><?php echo $rollback_count; ?></strong> lyrics-translations blocks were removed.</p>
                <p>You can now run the migration again with the fixed parser.</p>
            </div>
        <?php endif; ?>

        <?php if ($migrated && $results): ?>
            <div class="notice notice-success is-dismissible" style="padding: 15px; margin: 20px 0;">
                <h2 style="margin-top: 0;">✅ Migration Complete!</h2>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin: 15px 0;">
                    <div style="background: #f0f0f1; padding: 15px; border-radius: 5px; text-align: center;">
                        <div style="font-size: 32px; font-weight: bold; color: #2271b1;"><?php echo $results['total']; ?></div>
                        <div style="font-size: 14px; color: #646970;">Total Posts</div>
                    </div>
                    <div style="background: #d5f4e6; padding: 15px; border-radius: 5px; text-align: center;">
                        <div style="font-size: 32px; font-weight: bold; color: #00a32a;"><?php echo $results['migrated']; ?></div>
                        <div style="font-size: 14px; color: #00a32a;">Successfully Migrated</div>
                    </div>
                    <div style="background: #f0f6fc; padding: 15px; border-radius: 5px; text-align: center;">
                        <div style="font-size: 32px; font-weight: bold; color: #3582c4;"><?php echo $results['skipped']; ?></div>
                        <div style="font-size: 14px; color: #3582c4;">Skipped</div>
                    </div>
                    <?php if ($results['errors'] > 0): ?>
                    <div style="background: #fcf0f1; padding: 15px; border-radius: 5px; text-align: center;">
                        <div style="font-size: 32px; font-weight: bold; color: #d63638;"><?php echo $results['errors']; ?></div>
                        <div style="font-size: 14px; color: #d63638;">Errors</div>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($results['details'])): ?>
                    <details style="margin-top: 20px;">
                        <summary style="cursor: pointer; font-weight: bold; padding: 10px; background: #f0f0f1; border-radius: 3px;">
                            📋 View Detailed Results (<?php echo count($results['details']); ?> posts)
                        </summary>
                        <div style="margin-top: 10px; max-height: 400px; overflow-y: auto; border: 1px solid #ddd; border-radius: 3px;">
                            <table class="wp-list-table widefat fixed striped">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">ID</th>
                                        <th>Title</th>
                                        <th style="width: 100px;">Status</th>
                                        <th>Message</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results['details'] as $detail): ?>
                                        <tr>
                                            <td><?php echo $detail['id']; ?></td>
                                            <td>
                                                <a href="<?php echo get_edit_post_link($detail['id']); ?>" target="_blank">
                                                    <?php echo esc_html($detail['title']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php
                                                $badge_colors = array(
                                                    'success' => 'background: #00a32a; color: white;',
                                                    'skipped' => 'background: #3582c4; color: white;',
                                                    'error' => 'background: #d63638; color: white;'
                                                );
                                                $style = isset($badge_colors[$detail['status']]) ? $badge_colors[$detail['status']] : '';
                                                ?>
                                                <span style="<?php echo $style; ?> padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: bold;">
                                                    <?php echo strtoupper($detail['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo esc_html($detail['message']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </details>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="card" style="max-width: 900px; margin-top: 20px; padding: 20px;">
            <h2 style="margin-top: 0;">📖 What This Tool Does</h2>
            <p style="font-size: 15px; line-height: 1.6;">
                This tool converts all HTML table lyrics (<code>wp:table</code> blocks) into the new
                <strong>Lyrics & Translations Gutenberg block</strong> (<code>arcuras/lyrics-translations</code>).
            </p>

            <h3>🔄 Migration Process:</h3>
            <ol style="line-height: 1.8;">
                <li>Scans all lyrics posts for table blocks</li>
                <li>Parses language headers (English, Spanish, Turkish, etc.)</li>
                <li>Extracts lyrics line by line for each language</li>
                <li>Creates new Lyrics & Translations block with the data</li>
                <li>Replaces the old table block with the new block</li>
                <li>Sets the first language as the original language</li>
            </ol>

            <h3>✅ Safety Features:</h3>
            <ul style="line-height: 1.8;">
                <li>Posts already using the new block will be skipped</li>
                <li>Posts without tables will be skipped</li>
                <li>You can run this multiple times safely</li>
                <li>Detailed results show exactly what happened to each post</li>
            </ul>

            <h3>🌍 Supported Languages:</h3>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin: 10px 0;">
                <div>• English (en)</div>
                <div>• Spanish (es)</div>
                <div>• Turkish (tr)</div>
                <div>• Arabic (ar)</div>
                <div>• German (de)</div>
                <div>• Russian (ru)</div>
                <div>• Japanese (ja)</div>
                <div>• Korean (ko)</div>
                <div>• French (fr)</div>
                <div>• Italian (it)</div>
                <div>• Portuguese (pt)</div>
            </div>

            <div style="display: flex; gap: 20px; margin-top: 30px;">
                <form method="post" onsubmit="return confirm('Are you sure you want to start the migration? This will process all lyrics posts.');">
                    <?php wp_nonce_field('migrate_tables_action', 'migrate_tables_nonce'); ?>
                    <button type="submit" name="migrate_tables" class="button button-primary button-hero" style="padding: 15px 40px; font-size: 16px;">
                        🚀 Start Migration
                    </button>
                </form>

                <form method="post" onsubmit="return confirm('Are you sure? This will remove all lyrics-translations blocks and restore the original tables.');">
                    <?php wp_nonce_field('rollback_migration_action', 'rollback_migration_nonce'); ?>
                    <button type="submit" name="rollback_migration" class="button button-secondary button-hero" style="padding: 15px 40px; font-size: 16px;">
                        🔄 Rollback Migration
                    </button>
                </form>
            </div>
            <p style="color: #646970; margin-top: 10px;">
                <em>⚠️ Recommendation: Create a database backup before running migration.</em>
            </p>
        </div>
    </div>

    <style>
        .wrap h1 { font-size: 28px; margin-bottom: 10px; }
        .wrap h2 { font-size: 20px; margin-top: 20px; }
        .wrap h3 { font-size: 16px; margin-top: 15px; color: #1d2327; }
        .card { background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    </style>
    <?php
}
