<?php
/**
 * Awards System - Music Awards Management
 * 
 * @package Gufte
 * @subpackage Awards
 * @since 1.0.0
 */

// Güvenlik kontrolü
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Awards taksonomisini kaydet - Hiyerarşik yapı
 */
function gufte_register_awards_taxonomy() {
    
    $labels = array(
        'name'                       => _x('Awards', 'taxonomy general name', 'gufte'),
        'singular_name'              => _x('Award', 'taxonomy singular name', 'gufte'),
        'search_items'               => __('Search Awards', 'gufte'),
        'all_items'                  => __('All Awards', 'gufte'),
        'parent_item'                => __('Parent Award', 'gufte'),
        'parent_item_colon'          => __('Parent Award:', 'gufte'),
        'edit_item'                  => __('Edit Award', 'gufte'),
        'update_item'                => __('Update Award', 'gufte'),
        'add_new_item'               => __('Add New Award', 'gufte'),
        'new_item_name'              => __('New Award Name', 'gufte'),
        'menu_name'                  => __('Awards', 'gufte'),
    );

    $args = array(
        'labels'            => $labels,
        'hierarchical'      => true,
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud'     => false,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'awards', 'with_front' => false, 'hierarchical' => true),
        'show_in_rest'      => true,
    );

    register_taxonomy('awards', array('lyrics'), $args);
}
add_action('init', 'gufte_register_awards_taxonomy');

/**
 * Award meta alanları kaydet
 */
function gufte_register_award_meta_fields() {
    // Award Type (Grammy, Billboard, MTV vb.)
    register_term_meta('awards', 'award_type', array(
        'type' => 'string',
        'description' => 'Type of award (Grammy, Billboard, etc.)',
        'single' => true,
        'show_in_rest' => true,
    ));
    
    // Award Category (Record of the Year, Best Pop Album vb.)
    register_term_meta('awards', 'award_category', array(
        'type' => 'string',
        'description' => 'Award category',
        'single' => true,
        'show_in_rest' => true,
    ));
    
    // Award Icon/Logo
    register_term_meta('awards', 'award_icon', array(
        'type' => 'string',
        'description' => 'Award icon or logo URL',
        'single' => true,
        'show_in_rest' => true,
    ));
    
    // Award Official Website
    register_term_meta('awards', 'award_website', array(
        'type' => 'string',
        'description' => 'Official award website',
        'single' => true,
        'show_in_rest' => true,
    ));
}
add_action('init', 'gufte_register_award_meta_fields');

/**
 * Award Result meta box - Post'lara eklenecek
 */
function gufte_add_award_result_meta_box() {
    add_meta_box(
        'gufte_award_results',
        __('Award Status', 'gufte'),
        'gufte_award_result_meta_box_callback',
        'lyrics',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'gufte_add_award_result_meta_box');

/**
 * Award Result meta box içeriği - DÜZELTILMIŞ
 */
function gufte_award_result_meta_box_callback($post) {
    wp_nonce_field('gufte_award_result_nonce', 'gufte_award_result_nonce');
    
    $award_results = get_post_meta($post->ID, '_award_results', true);
    if (!is_array($award_results)) {
        $award_results = array();
    }
    
    // SADECE en alt seviyedeki (leaf) ödülleri al
    $post_awards = wp_get_post_terms($post->ID, 'awards', array('fields' => 'all'));
    
    // Sadece leaf node'ları (child'ı olmayan terms) filtrele
    $leaf_awards = array();
    foreach ($post_awards as $award) {
        $children = get_term_children($award->term_id, 'awards');
        if (empty($children)) {
            $leaf_awards[] = $award;
        }
    }
    
    ?>
    <div class="award-results-container">
        <p class="description"><?php _e('Set the result for each award this song is associated with.', 'gufte'); ?></p>
        
        <?php if (!empty($leaf_awards)) : ?>
            <?php foreach ($leaf_awards as $award) : 
                $award_type = get_term_meta($award->term_id, 'award_type', true);
                $current_result = isset($award_results[$award->term_id]) ? $award_results[$award->term_id] : 'nominee';
                
                // Hiyerarşik isim oluşturma - sadece görüntüleme için
                $full_award_name = gufte_get_full_award_hierarchy($award);
                ?>
                
                <div style="margin-bottom: 15px; padding: 10px; background: #f7f7f7; border-radius: 5px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">
                        <?php echo esc_html($full_award_name); ?>
                    </label>
                    
                    <select name="award_results[<?php echo $award->term_id; ?>]" 
                            class="award-result-select" 
                            data-award-id="<?php echo $award->term_id; ?>" 
                            style="width: 100%;">
                        <option value="winner" <?php selected($current_result, 'winner'); ?>>
                            🏆 <?php _e('Winner', 'gufte'); ?>
                        </option>
                        <option value="nominee" <?php selected($current_result, 'nominee'); ?>>
                            🎯 <?php _e('Nominee', 'gufte'); ?>
                        </option>
                        <option value="honorable_mention" <?php selected($current_result, 'honorable_mention'); ?>>
                            🌟 <?php _e('Honorable Mention', 'gufte'); ?>
                        </option>
                    </select>
                    
                    <?php 
                    $notes = isset($award_results[$award->term_id . '_notes']) ? $award_results[$award->term_id . '_notes'] : '';
                    ?>
                    <input type="text" 
                           name="award_results[<?php echo $award->term_id; ?>_notes]" 
                           class="award-result-notes" 
                           data-award-id="<?php echo $award->term_id; ?>"
                           value="<?php echo esc_attr($notes); ?>"
                           placeholder="<?php _e('Additional notes (e.g., Top 5 Nominee, 2nd Place)', 'gufte'); ?>"
                           style="width: 100%; margin-top: 5px; <?php echo ($current_result === 'winner') ? 'display: none;' : ''; ?>">
                </div>
                
            <?php endforeach; ?>
        <?php else : ?>
            <p style="color: #666; font-style: italic;">
                <?php _e('No specific award categories assigned yet. Please assign specific award categories (not just the parent awards) from the Awards taxonomy.', 'gufte'); ?>
            </p>
        <?php endif; ?>
        
        <p class="howto">
            <?php _e('First assign specific award categories (like "Record of the Year") to this post, not just the parent categories. Then set the result for each award here.', 'gufte'); ?>
            <a href="<?php echo admin_url('edit-tags.php?taxonomy=awards'); ?>">
                <?php _e('Manage awards', 'gufte'); ?>
            </a>
        </p>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('.award-result-select').on('change', function() {
            var awardId = $(this).data('award-id');
            var selectedValue = $(this).val();
            var $notesField = $('.award-result-notes[data-award-id="' + awardId + '"]');
            
            if (selectedValue === 'winner') {
                $notesField.hide();
                $notesField.val('');
            } else {
                $notesField.show();
            }
        });
    });
    </script>
    <?php
}


/**
 * Ödül hiyerarşisini tam olarak getir - YENİ HELPER FONKSİYON
 */
function gufte_get_full_award_hierarchy($term) {
    if (!$term || is_wp_error($term)) {
        return '';
    }
    
    $hierarchy = array();
    $current_term = $term;
    
    // Hiyerarşiyi ters sırada topla (child'dan parent'a)
    while ($current_term && !is_wp_error($current_term)) {
        array_unshift($hierarchy, $current_term->name);
        
        if ($current_term->parent == 0) {
            break;
        }
        
        $current_term = get_term($current_term->parent, 'awards');
    }
    
    return implode(' › ', $hierarchy);
}

/**
 * Award results kaydet
 */
function gufte_save_award_results($post_id) {
    if (!isset($_POST['gufte_award_result_nonce']) || 
        !wp_verify_nonce($_POST['gufte_award_result_nonce'], 'gufte_award_result_nonce')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    if (isset($_POST['award_results'])) {
        $award_results = array();
        foreach ($_POST['award_results'] as $key => $value) {
            if (strpos($key, '_notes') === false) {
                $award_results[sanitize_text_field($key)] = sanitize_text_field($value);
            }
            if (strpos($key, '_notes') !== false && isset($award_results[str_replace('_notes', '', $key)]) && $award_results[str_replace('_notes', '', $key)] !== 'winner') {
                $award_results[sanitize_text_field($key)] = sanitize_text_field($value);
            }
        }
        update_post_meta($post_id, '_award_results', $award_results);
    }
}
add_action('save_post', 'gufte_save_award_results');

/**
 * Awards için form alanları - Edit
 */
function gufte_add_award_form_fields($term) {
    if ($term->taxonomy !== 'awards') {
        return;
    }
    
    $award_type = get_term_meta($term->term_id, 'award_type', true);
    $award_category = get_term_meta($term->term_id, 'award_category', true);
    $award_icon = get_term_meta($term->term_id, 'award_icon', true);
    $award_website = get_term_meta($term->term_id, 'award_website', true);
    ?>
    
    <tr class="form-field">
        <th scope="row">
            <label for="award_type"><?php _e('Award Organization', 'gufte'); ?></label>
        </th>
        <td>
            <select name="award_type" id="award_type" style="width: 95%;">
                <option value=""><?php _e('Select Award Type', 'gufte'); ?></option>
                <option value="grammy" <?php selected($award_type, 'grammy'); ?>>Grammy Awards</option>
                <option value="billboard" <?php selected($award_type, 'billboard'); ?>>Billboard Music Awards</option>
                <option value="mtv" <?php selected($award_type, 'mtv'); ?>>MTV Music Awards</option>
                <option value="ama" <?php selected($award_type, 'ama'); ?>>American Music Awards</option>
                <option value="brit" <?php selected($award_type, 'brit'); ?>>BRIT Awards</option>
                <option value="iheartradio" <?php selected($award_type, 'iheartradio'); ?>>iHeartRadio Music Awards</option>
                <option value="vma" <?php selected($award_type, 'vma'); ?>>MTV Video Music Awards</option>
                <option value="cma" <?php selected($award_type, 'cma'); ?>>Country Music Association Awards</option>
                <option value="acm" <?php selected($award_type, 'acm'); ?>>Academy of Country Music Awards</option>
                <option value="other" <?php selected($award_type, 'other'); ?>>Other</option>
            </select>
            <p class="description"><?php _e('Select the award organization', 'gufte'); ?></p>
        </td>
    </tr>
    
    <tr class="form-field">
        <th scope="row">
            <label for="award_category"><?php _e('Award Category', 'gufte'); ?></label>
        </th>
        <td>
            <input type="text" name="award_category" id="award_category" 
                   value="<?php echo esc_attr($award_category); ?>" 
                   style="width: 95%;" />
            <p class="description"><?php _e('e.g., Record of the Year, Best Pop Solo Performance', 'gufte'); ?></p>
        </td>
    </tr>
    
    <tr class="form-field">
        <th scope="row">
            <label for="award_website"><?php _e('Official Website', 'gufte'); ?></label>
        </th>
        <td>
            <input type="url" name="award_website" id="award_website" 
                   value="<?php echo esc_attr($award_website); ?>" 
                   style="width: 95%;" />
            <p class="description"><?php _e('Link to official award website or ceremony page', 'gufte'); ?></p>
        </td>
    </tr>
    <?php
}
add_action('awards_edit_form_fields', 'gufte_add_award_form_fields');

/**
 * Awards için form alanları - Add New
 */
function gufte_add_new_award_form_fields() {
    ?>
    <div class="form-field">
        <label for="award_type"><?php _e('Award Organization', 'gufte'); ?></label>
        <select name="award_type" id="award_type">
            <option value=""><?php _e('Select Award Type', 'gufte'); ?></option>
            <option value="grammy">Grammy Awards</option>
            <option value="billboard">Billboard Music Awards</option>
            <option value="mtv">MTV Music Awards</option>
            <option value="ama">American Music Awards</option>
            <option value="brit">BRIT Awards</option>
            <option value="iheartradio">iHeartRadio Music Awards</option>
            <option value="vma">MTV Video Music Awards</option>
            <option value="cma">Country Music Association Awards</option>
            <option value="acm">Academy of Country Music Awards</option>
            <option value="other">Other</option>
        </select>
        <p><?php _e('Select the award organization', 'gufte'); ?></p>
    </div>
    
    <div class="form-field">
        <label for="award_category"><?php _e('Award Category', 'gufte'); ?></label>
        <input type="text" name="award_category" id="award_category" />
        <p><?php _e('e.g., Record of the Year, Best Pop Solo Performance', 'gufte'); ?></p>
    </div>
    
    <div class="form-field">
        <label for="award_website"><?php _e('Official Website', 'gufte'); ?></label>
        <input type="url" name="award_website" id="award_website" />
        <p><?php _e('Link to official award website or ceremony page', 'gufte'); ?></p>
    </div>
    <?php
}
add_action('awards_add_form_fields', 'gufte_add_new_award_form_fields');

/**
 * Award meta verilerini kaydet
 */
function gufte_save_award_meta($term_id) {
    $fields = array('award_type', 'award_category', 'award_icon', 'award_website');
    
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $value = sanitize_text_field($_POST[$field]);
            if ($field === 'award_website') {
                $value = sanitize_url($_POST[$field]);
            }
            update_term_meta($term_id, $field, $value);
        }
    }
}
add_action('created_awards', 'gufte_save_award_meta');
add_action('edited_awards', 'gufte_save_award_meta');

/**
 * Predefined Award Categories
 */
function gufte_get_predefined_award_categories($award_type = '') {
    $categories = array(
        'grammy' => array(
            'Record of the Year',
            'Album of the Year',
            'Song of the Year',
            'Best New Artist',
            'Best Pop Solo Performance',
            'Best Pop Duo/Group Performance',
            'Best Pop Vocal Album',
            'Best Traditional Pop Vocal Album',
            'Best Pop Dance Recording',
            'Best Rock Performance',
            'Best Metal Performance',
            'Best Rock Song',
            'Best Rock Album',
            'Best R&B Performance',
            'Best Traditional R&B Performance',
            'Best R&B Song',
            'Best Progressive R&B Album',
            'Best R&B Album',
            'Best Rap Performance',
            'Best Melodic Rap Performance',
            'Best Rap Song',
            'Best Rap Album',
            'Best Country Solo Performance',
            'Best Country Duo/Group Performance',
            'Best Country Song',
            'Best Country Album',
            'Best Dance/Electronic Recording',
            'Best Dance/Electronic Album',
            'Producer of the Year, Non-Classical',
        ),
        'billboard' => array(
            'Top Artist',
            'Top Male Artist',
            'Top Female Artist',
            'Top Duo/Group',
            'Top New Artist',
            'Top Billboard 200 Album',
            'Top Hot 100 Song',
            'Top Streaming Song',
            'Top Radio Song',
            'Top Collaboration',
            'Top R&B Artist',
            'Top R&B Album',
            'Top Rap Artist',
            'Top Rap Album',
            'Top Country Artist',
            'Top Country Album',
            'Top Rock Artist',
            'Top Rock Album',
            'Top Latin Artist',
            'Top Dance/Electronic Artist',
        ),
        'mtv' => array(
            'Video of the Year',
            'Artist of the Year',
            'Song of the Year',
            'Best New Artist',
            'Push Performance of the Year',
            'Best Collaboration',
            'Best Pop',
            'Best Hip-Hop',
            'Best Rock',
            'Best Alternative',
            'Best Latin',
            'Best R&B',
            'Best K-Pop',
            'Video for Good',
            'Best Direction',
            'Best Cinematography',
            'Best Visual Effects',
            'Best Choreography',
        ),
    );
    
    if ($award_type && isset($categories[$award_type])) {
        return $categories[$award_type];
    }
    
    return $categories;
}

/**
 * Get award display name with icon
 */
function gufte_get_award_display_name($term_id) {
    $term = get_term($term_id, 'awards');
    if (!$term || is_wp_error($term)) {
        return '';
    }
    
    $award_type = get_term_meta($term_id, 'award_type', true);
    
    $icons = array(
        'grammy' => '🎵',
        'billboard' => '📊',
        'mtv' => '📺',
        'ama' => '🎤',
        'brit' => '🇬🇧',
        'vma' => '🎬',
        'other' => '🏆',
    );
    
    $icon = isset($icons[$award_type]) ? $icons[$award_type] : '🏆';
    $name = $term->name;
    
    // Yıl bilgisi hiyerarşiden geliyor, meta'dan değil
    return $icon . ' ' . $name;
}

/**
 * Get awards for a post with results - DÜZELTILMIŞ
 */
function gufte_get_post_awards($post_id) {
    // Sadece leaf awards al (child'ı olmayan)
    $all_awards = wp_get_post_terms($post_id, 'awards');
    $awards = array();
    
    foreach ($all_awards as $award) {
        $children = get_term_children($award->term_id, 'awards');
        if (empty($children)) {
            $awards[] = $award;
        }
    }
    
    $award_results = get_post_meta($post_id, '_award_results', true);
    
    if (!is_array($award_results)) {
        $award_results = array();
    }
    
    $awards_with_results = array();
    
    foreach ($awards as $award) {
        $result = isset($award_results[$award->term_id]) ? $award_results[$award->term_id] : 'nominee';
        $notes = isset($award_results[$award->term_id . '_notes']) ? $award_results[$award->term_id . '_notes'] : '';
        
        // Yıl bilgisini hiyerarşiden al
        $year = '';
        $award_type = get_term_meta($award->term_id, 'award_type', true);
        $award_category = get_term_meta($award->term_id, 'award_category', true);
        
        // Hiyerarşiden yıl ve organization bilgisini çıkar
        $hierarchy_info = gufte_parse_award_hierarchy($award);
        
        // Type label'ı düzelt
        $type_labels = array(
            'grammy' => 'Grammy Awards',
            'billboard' => 'Billboard Music Awards',
            'mtv' => 'MTV Music Awards',
            'ama' => 'American Music Awards',
            'brit' => 'BRIT Awards',
            'iheartradio' => 'iHeartRadio Music Awards',
            'vma' => 'MTV Video Music Awards',
            'cma' => 'Country Music Association Awards',
            'acm' => 'Academy of Country Music Awards',
            'other' => 'Other Awards',
        );
        
        $type_label = isset($type_labels[$award_type]) ? $type_labels[$award_type] : ucfirst($award_type);
        
        $awards_with_results[] = array(
            'term' => $award,
            'result' => $result,
            'notes' => $notes,
            'year' => $hierarchy_info['year'],
            'organization' => $hierarchy_info['organization'],
            'type' => $award_type,
            'type_label' => $type_label, // Bu eksikti!
            'category' => $award_category ?: $award->name,
        );
    }
    
    // Yıla göre sırala (yeniden eskiye)
    usort($awards_with_results, function($a, $b) {
        $ya = isset($a['year']) ? (int) preg_replace('/\D+/', '', $a['year']) : 0;
        $yb = isset($b['year']) ? (int) preg_replace('/\D+/', '', $b['year']) : 0;
        return $yb <=> $ya;
    });
    
    return $awards_with_results;
}


/**
 * Award hiyerarşisinden yıl ve organization bilgisini çıkar - YENİ FONKSİYON
 */
function gufte_parse_award_hierarchy($award) {
    $year = '';
    $organization = '';
    
    if ($award->parent > 0) {
        $parent = get_term($award->parent, 'awards');
        if ($parent && !is_wp_error($parent)) {
            // Parent organization olabilir
            $organization = $parent->name;
            
            if ($parent->parent > 0) {
                $grandparent = get_term($parent->parent, 'awards');
                if ($grandparent && !is_wp_error($grandparent)) {
                    // Grandparent genelde yıl
                    $year = $grandparent->name;
                }
            }
        }
    }
    
    return array(
        'year' => $year,
        'organization' => $organization
    );
}

/**
 * Admin columns for awards
 */
function gufte_add_awards_admin_columns($columns) {
    $new_columns = array();
    
    foreach ($columns as $key => $value) {
        if ($key === 'posts') {
            $new_columns['award_type'] = __('Type', 'gufte');
            $new_columns['award_year'] = __('Year', 'gufte');
        }
        $new_columns[$key] = $value;
    }
    
    return $new_columns;
}
add_filter('manage_edit-awards_columns', 'gufte_add_awards_admin_columns');

/**
 * Admin column content
 */
function gufte_display_awards_admin_columns($content, $column_name, $term_id) {
    switch ($column_name) {
        case 'award_type':
            $type = get_term_meta($term_id, 'award_type', true);
            $types = array(
                'grammy' => 'Grammy',
                'billboard' => 'Billboard',
                'mtv' => 'MTV',
                'ama' => 'AMA',
                'brit' => 'BRIT',
                'vma' => 'VMA',
            );
            $content = isset($types[$type]) ? $types[$type] : ucfirst($type);
            break;
            
        case 'award_year':
            $term = get_term($term_id, 'awards');
            if ($term && !is_wp_error($term) && $term->parent > 0) {
                $parent = get_term($term->parent, 'awards');
                if ($parent && !is_wp_error($parent) && $parent->parent > 0) {
                    $grandparent = get_term($parent->parent, 'awards');
                    if ($grandparent && !is_wp_error($grandparent)) {
                        $content = $grandparent->name;
                    }
                }
            }
            break;
    }
    
    return $content;
}
add_filter('manage_awards_custom_column', 'gufte_display_awards_admin_columns', 10, 3);
?>