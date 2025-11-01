<?php
/**
 * Credits System - Producer, Songwriter, Composer Taksonomileri
 * 
 * @package Gufte
 * @subpackage Credits
 * @since 1.0.0
 */

// Güvenlik kontrolü
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Credits taksonomilerini kaydet
 */
function gufte_register_credits_taxonomies() {
    
    // 1. PRODUCER TAKSONOMİSİ
    $producer_labels = array(
        'name'                       => _x('Producers', 'taxonomy general name', 'gufte'),
        'singular_name'              => _x('Producer', 'taxonomy singular name', 'gufte'),
        'search_items'               => __('Search Producers', 'gufte'),
        'popular_items'              => __('Popular Producers', 'gufte'),
        'all_items'                  => __('All Producers', 'gufte'),
        'edit_item'                  => __('Edit Producer', 'gufte'),
        'update_item'                => __('Update Producer', 'gufte'),
        'add_new_item'               => __('Add New Producer', 'gufte'),
        'new_item_name'              => __('New Producer Name', 'gufte'),
        'separate_items_with_commas' => __('Separate producers with commas', 'gufte'),
        'add_or_remove_items'        => __('Add or remove producers', 'gufte'),
        'choose_from_most_used'      => __('Choose from the most used producers', 'gufte'),
        'menu_name'                  => __('Producers', 'gufte'),
    );

    $producer_args = array(
        'labels'            => $producer_labels,
        'hierarchical'      => false,
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud'     => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'producer', 'with_front' => true),
        'show_in_rest'      => true,
    );

    register_taxonomy('producer', 'lyrics', $producer_args);

    // 2. SONGWRITER TAKSONOMİSİ
    $songwriter_labels = array(
        'name'                       => _x('Songwriters', 'taxonomy general name', 'gufte'),
        'singular_name'              => _x('Songwriter', 'taxonomy singular name', 'gufte'),
        'search_items'               => __('Search Songwriters', 'gufte'),
        'popular_items'              => __('Popular Songwriters', 'gufte'),
        'all_items'                  => __('All Songwriters', 'gufte'),
        'edit_item'                  => __('Edit Songwriter', 'gufte'),
        'update_item'                => __('Update Songwriter', 'gufte'),
        'add_new_item'               => __('Add New Songwriter', 'gufte'),
        'new_item_name'              => __('New Songwriter Name', 'gufte'),
        'separate_items_with_commas' => __('Separate songwriters with commas', 'gufte'),
        'add_or_remove_items'        => __('Add or remove songwriters', 'gufte'),
        'choose_from_most_used'      => __('Choose from the most used songwriters', 'gufte'),
        'menu_name'                  => __('Songwriters', 'gufte'),
    );

    $songwriter_args = array(
        'labels'            => $songwriter_labels,
        'hierarchical'      => false,
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud'     => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'songwriter', 'with_front' => true),
        'show_in_rest'      => true,
    );

    register_taxonomy('songwriter', 'lyrics', $songwriter_args);

    // 3. COMPOSER TAKSONOMİSİ
    $composer_labels = array(
        'name'                       => _x('Composers', 'taxonomy general name', 'gufte'),
        'singular_name'              => _x('Composer', 'taxonomy singular name', 'gufte'),
        'search_items'               => __('Search Composers', 'gufte'),
        'popular_items'              => __('Popular Composers', 'gufte'),
        'all_items'                  => __('All Composers', 'gufte'),
        'edit_item'                  => __('Edit Composer', 'gufte'),
        'update_item'                => __('Update Composer', 'gufte'),
        'add_new_item'               => __('Add New Composer', 'gufte'),
        'new_item_name'              => __('New Composer Name', 'gufte'),
        'separate_items_with_commas' => __('Separate composers with commas', 'gufte'),
        'add_or_remove_items'        => __('Add or remove composers', 'gufte'),
        'choose_from_most_used'      => __('Choose from the most used composers', 'gufte'),
        'menu_name'                  => __('Composers', 'gufte'),
    );

    $composer_args = array(
        'labels'            => $composer_labels,
        'hierarchical'      => false,
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud'     => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'composer', 'with_front' => true),
        'show_in_rest'      => true,
    );

    register_taxonomy('composer', 'lyrics', $composer_args);
}
add_action('init', 'gufte_register_credits_taxonomies');

/**
 * Credits taksonomileri için meta alanları kaydet
 */
function gufte_register_credits_meta_fields() {
    $credit_taxonomies = array('producer', 'songwriter', 'composer');
    
    foreach ($credit_taxonomies as $taxonomy) {
        // Gerçek ad
        register_term_meta($taxonomy, 'real_name', array(
            'type' => 'string',
            'description' => 'Real name',
            'single' => true,
            'show_in_rest' => true,
        ));
        
        // Profil görseli
        register_term_meta($taxonomy, 'profile_image_id', array(
            'type' => 'integer',
            'description' => 'Profile image attachment ID',
            'single' => true,
            'show_in_rest' => true,
        ));
        
        // Doğum tarihi
        register_term_meta($taxonomy, 'birth_date', array(
            'type' => 'string',
            'description' => 'Birth date',
            'single' => true,
            'show_in_rest' => true,
        ));
        
        // Ölüm tarihi
        register_term_meta($taxonomy, 'death_date', array(
            'type' => 'string',
            'description' => 'Death date',
            'single' => true,
            'show_in_rest' => true,
        ));
        
        // Doğum yeri
        register_term_meta($taxonomy, 'birth_place', array(
            'type' => 'string',
            'description' => 'Birth place',
            'single' => true,
            'show_in_rest' => true,
        ));
        
        // Ülke
        register_term_meta($taxonomy, 'birth_country', array(
            'type' => 'string',
            'description' => 'Birth country',
            'single' => true,
            'show_in_rest' => true,
        ));
        
        // Website
        register_term_meta($taxonomy, 'website_url', array(
            'type' => 'string',
            'description' => 'Official website URL',
            'single' => true,
            'show_in_rest' => true,
        ));
        
        // Sosyal medya linkleri
        register_term_meta($taxonomy, 'instagram_url', array(
            'type' => 'string',
            'description' => 'Instagram profile URL',
            'single' => true,
            'show_in_rest' => true,
        ));
        
        register_term_meta($taxonomy, 'twitter_url', array(
            'type' => 'string',
            'description' => 'Twitter/X profile URL',
            'single' => true,
            'show_in_rest' => true,
        ));
        
        register_term_meta($taxonomy, 'linkedin_url', array(
            'type' => 'string',
            'description' => 'LinkedIn profile URL',
            'single' => true,
            'show_in_rest' => true,
        ));
    }
}
add_action('init', 'gufte_register_credits_meta_fields');

/**
 * Credits düzenleme formuna özel alanlar ekle
 */
function gufte_add_credits_form_fields($term) {
    $allowed_taxonomies = array('producer', 'songwriter', 'composer');
    
    if (!in_array($term->taxonomy, $allowed_taxonomies)) {
        return;
    }
    
    // Meta değerlerini al
    $real_name = get_term_meta($term->term_id, 'real_name', true);
    $birth_date = get_term_meta($term->term_id, 'birth_date', true);
    $death_date = get_term_meta($term->term_id, 'death_date', true);
    $birth_place = get_term_meta($term->term_id, 'birth_place', true);
    $birth_country = get_term_meta($term->term_id, 'birth_country', true);
    $website_url = get_term_meta($term->term_id, 'website_url', true);
    $instagram_url = get_term_meta($term->term_id, 'instagram_url', true);
    $twitter_url = get_term_meta($term->term_id, 'twitter_url', true);
    $linkedin_url = get_term_meta($term->term_id, 'linkedin_url', true);
    $profile_image_id = get_term_meta($term->term_id, 'profile_image_id', true);
    
    ?>
    <!-- Profil Görseli -->
    <tr class="form-field">
        <th scope="row">
            <label for="profile-image"><?php _e('Profile Image', 'gufte'); ?></label>
        </th>
        <td>
            <div class="profile-image-container">
                <?php if ($profile_image_id) : 
                    $image_url = wp_get_attachment_image_url($profile_image_id, 'thumbnail');
                ?>
                    <div class="profile-image-preview" style="margin-bottom: 10px;">
                        <img src="<?php echo esc_url($image_url); ?>" style="max-width: 150px; height: auto; border-radius: 8px;" />
                    </div>
                <?php endif; ?>
                
                <input type="hidden" id="profile_image_id" name="profile_image_id" value="<?php echo esc_attr($profile_image_id); ?>" />
                <button type="button" class="button button-secondary" id="upload-profile-image">
                    <?php _e('Select Image', 'gufte'); ?>
                </button>
                
                <?php if ($profile_image_id) : ?>
                    <button type="button" class="button button-secondary" id="remove-profile-image">
                        <?php _e('Remove Image', 'gufte'); ?>
                    </button>
                <?php endif; ?>
            </div>
        </td>
    </tr>
    
    <!-- Gerçek Ad -->
    <tr class="form-field">
        <th scope="row">
            <label for="real_name"><?php _e('Real Name', 'gufte'); ?></label>
        </th>
        <td>
            <input type="text" name="real_name" id="real_name" value="<?php echo esc_attr($real_name); ?>" />
            <p class="description"><?php _e('Full legal name if different from professional name', 'gufte'); ?></p>
        </td>
    </tr>
    
    <!-- Doğum Tarihi -->
    <tr class="form-field">
        <th scope="row">
            <label for="birth_date"><?php _e('Birth Date', 'gufte'); ?></label>
        </th>
        <td>
            <input type="date" name="birth_date" id="birth_date" value="<?php echo esc_attr($birth_date); ?>" />
        </td>
    </tr>
    
    <!-- Ölüm Tarihi -->
    <tr class="form-field">
        <th scope="row">
            <label for="death_date"><?php _e('Death Date', 'gufte'); ?></label>
        </th>
        <td>
            <input type="date" name="death_date" id="death_date" value="<?php echo esc_attr($death_date); ?>" />
            <p class="description"><?php _e('Leave empty if still alive', 'gufte'); ?></p>
        </td>
    </tr>
    
    <!-- Doğum Yeri -->
    <tr class="form-field">
        <th scope="row">
            <label for="birth_place"><?php _e('Birth Place', 'gufte'); ?></label>
        </th>
        <td>
            <input type="text" name="birth_place" id="birth_place" value="<?php echo esc_attr($birth_place); ?>" />
            <p class="description"><?php _e('City, State/Province', 'gufte'); ?></p>
        </td>
    </tr>
    
    <!-- Ülke -->
    <tr class="form-field">
        <th scope="row">
            <label for="birth_country"><?php _e('Country', 'gufte'); ?></label>
        </th>
        <td>
            <input type="text" name="birth_country" id="birth_country" value="<?php echo esc_attr($birth_country); ?>" />
        </td>
    </tr>
    
    <!-- Website -->
    <tr class="form-field">
        <th scope="row">
            <label for="website_url"><?php _e('Official Website', 'gufte'); ?></label>
        </th>
        <td>
            <input type="url" name="website_url" id="website_url" value="<?php echo esc_attr($website_url); ?>" style="width: 95%;" />
        </td>
    </tr>
    
    <!-- Sosyal Medya -->
    <tr class="form-field">
        <th colspan="2">
            <h3><?php _e('Social Media Profiles', 'gufte'); ?></h3>
        </th>
    </tr>
    
    <tr class="form-field">
        <th scope="row">
            <label for="instagram_url"><?php _e('Instagram', 'gufte'); ?></label>
        </th>
        <td>
            <input type="url" name="instagram_url" id="instagram_url" value="<?php echo esc_attr($instagram_url); ?>" style="width: 95%;" placeholder="https://instagram.com/username" />
        </td>
    </tr>
    
    <tr class="form-field">
        <th scope="row">
            <label for="twitter_url"><?php _e('Twitter/X', 'gufte'); ?></label>
        </th>
        <td>
            <input type="url" name="twitter_url" id="twitter_url" value="<?php echo esc_attr($twitter_url); ?>" style="width: 95%;" placeholder="https://twitter.com/username" />
        </td>
    </tr>
    
    <tr class="form-field">
        <th scope="row">
            <label for="linkedin_url"><?php _e('LinkedIn', 'gufte'); ?></label>
        </th>
        <td>
            <input type="url" name="linkedin_url" id="linkedin_url" value="<?php echo esc_attr($linkedin_url); ?>" style="width: 95%;" placeholder="https://linkedin.com/in/username" />
        </td>
    </tr>
    
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Media Uploader
        var file_frame;
        
        $('#upload-profile-image').on('click', function(e) {
            e.preventDefault();
            
            if (file_frame) {
                file_frame.open();
                return;
            }
            
            file_frame = wp.media.frames.file_frame = wp.media({
                title: '<?php _e('Select Profile Image', 'gufte'); ?>',
                button: {
                    text: '<?php _e('Use this image', 'gufte'); ?>'
                },
                multiple: false
            });
            
            file_frame.on('select', function() {
                var attachment = file_frame.state().get('selection').first().toJSON();
                
                $('#profile_image_id').val(attachment.id);
                
                if ($('.profile-image-preview').length) {
                    $('.profile-image-preview img').attr('src', attachment.sizes.thumbnail.url);
                } else {
                    $('.profile-image-container').prepend(
                        '<div class="profile-image-preview" style="margin-bottom: 10px;">' +
                        '<img src="' + attachment.sizes.thumbnail.url + '" style="max-width: 150px; height: auto; border-radius: 8px;" />' +
                        '</div>'
                    );
                }
                
                if (!$('#remove-profile-image').length) {
                    $('#upload-profile-image').after(
                        ' <button type="button" class="button button-secondary" id="remove-profile-image"><?php _e('Remove Image', 'gufte'); ?></button>'
                    );
                }
            });
            
            file_frame.open();
        });
        
        $(document).on('click', '#remove-profile-image', function(e) {
            e.preventDefault();
            $('.profile-image-preview').remove();
            $('#profile_image_id').val('');
            $(this).remove();
        });
    });
    </script>
    <?php
}
add_action('producer_edit_form_fields', 'gufte_add_credits_form_fields');
add_action('songwriter_edit_form_fields', 'gufte_add_credits_form_fields');
add_action('composer_edit_form_fields', 'gufte_add_credits_form_fields');

/**
 * Yeni credit eklerken form alanları
 */
function gufte_add_new_credits_form_fields($taxonomy) {
    ?>
    <div class="form-field">
        <label for="real_name"><?php _e('Real Name', 'gufte'); ?></label>
        <input type="text" name="real_name" id="real_name" />
        <p><?php _e('Full legal name if different from professional name', 'gufte'); ?></p>
    </div>
    
    <div class="form-field">
        <label for="birth_date"><?php _e('Birth Date', 'gufte'); ?></label>
        <input type="date" name="birth_date" id="birth_date" />
    </div>
    
    <div class="form-field">
        <label for="birth_place"><?php _e('Birth Place', 'gufte'); ?></label>
        <input type="text" name="birth_place" id="birth_place" />
        <p><?php _e('City, State/Province', 'gufte'); ?></p>
    </div>
    
    <div class="form-field">
        <label for="birth_country"><?php _e('Country', 'gufte'); ?></label>
        <input type="text" name="birth_country" id="birth_country" />
    </div>
    
    <div class="form-field">
        <label for="website_url"><?php _e('Official Website', 'gufte'); ?></label>
        <input type="url" name="website_url" id="website_url" />
    </div>
    <?php
}
add_action('producer_add_form_fields', 'gufte_add_new_credits_form_fields');
add_action('songwriter_add_form_fields', 'gufte_add_new_credits_form_fields');
add_action('composer_add_form_fields', 'gufte_add_new_credits_form_fields');

/**
 * Credit meta verilerini kaydet
 */
function gufte_save_credits_meta($term_id) {
    $fields = array(
        'real_name', 'birth_date', 'death_date', 'birth_place', 
        'birth_country', 'website_url', 'instagram_url', 
        'twitter_url', 'linkedin_url', 'profile_image_id'
    );
    
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $value = sanitize_text_field($_POST[$field]);
            if ($field === 'profile_image_id') {
                $value = absint($_POST[$field]);
            } elseif (strpos($field, '_url') !== false) {
                $value = sanitize_url($_POST[$field]);
            }
            update_term_meta($term_id, $field, $value);
        }
    }
}
add_action('created_producer', 'gufte_save_credits_meta');
add_action('edited_producer', 'gufte_save_credits_meta');
add_action('created_songwriter', 'gufte_save_credits_meta');
add_action('edited_songwriter', 'gufte_save_credits_meta');
add_action('created_composer', 'gufte_save_credits_meta');
add_action('edited_composer', 'gufte_save_credits_meta');

/**
 * Admin sütunları ekle
 */
function gufte_add_credits_admin_columns($columns) {
    $new_columns = array();
    
    foreach ($columns as $key => $value) {
        if ($key === 'posts') {
            $new_columns['birth_date'] = __('Birth Date', 'gufte');
            $new_columns['country'] = __('Country', 'gufte');
        }
        $new_columns[$key] = $value;
    }
    
    return $new_columns;
}
add_filter('manage_edit-producer_columns', 'gufte_add_credits_admin_columns');
add_filter('manage_edit-songwriter_columns', 'gufte_add_credits_admin_columns');
add_filter('manage_edit-composer_columns', 'gufte_add_credits_admin_columns');

/**
 * Admin sütun içeriklerini doldur
 */
function gufte_display_credits_admin_columns($content, $column_name, $term_id) {
    switch ($column_name) {
        case 'birth_date':
            $birth_date = get_term_meta($term_id, 'birth_date', true);
            if ($birth_date) {
                $content = date('Y', strtotime($birth_date));
            }
            break;
            
        case 'country':
            $country = get_term_meta($term_id, 'birth_country', true);
            if ($country) {
                $content = esc_html($country);
            }
            break;
    }
    
    return $content;
}
add_filter('manage_producer_custom_column', 'gufte_display_credits_admin_columns', 10, 3);
add_filter('manage_songwriter_custom_column', 'gufte_display_credits_admin_columns', 10, 3);
add_filter('manage_composer_custom_column', 'gufte_display_credits_admin_columns', 10, 3);

/**
 * Helper Functions
 */

/**
 * Credit profil resmini getir
 */
function gufte_get_credit_image($term_id, $size = 'thumbnail') {
    $image_id = get_term_meta($term_id, 'profile_image_id', true);
    
    if ($image_id) {
        return wp_get_attachment_image($image_id, $size, false, array('class' => 'credit-profile-image'));
    }
    
    return '';
}

/**
 * Credit yaşam süresini hesapla
 */
function gufte_get_credit_lifespan($term_id) {
    $birth_date = get_term_meta($term_id, 'birth_date', true);
    $death_date = get_term_meta($term_id, 'death_date', true);
    
    if (empty($birth_date)) {
        return '';
    }
    
    $birth_year = date('Y', strtotime($birth_date));
    
    if (!empty($death_date)) {
        $death_year = date('Y', strtotime($death_date));
        $age = gufte_calculate_singer_age($birth_date, $death_date);
        
        return sprintf('%s - %s (died at age %d)', $birth_year, $death_year, $age);
    }
    
    $age = gufte_calculate_singer_age($birth_date);
    return sprintf('Born %s (age %d)', $birth_year, $age);
}

/**
 * Credit sosyal medya linklerini getir
 */
function gufte_get_credit_social_links($term_id) {
    return array(
        'website' => get_term_meta($term_id, 'website_url', true),
        'instagram' => get_term_meta($term_id, 'instagram_url', true),
        'twitter' => get_term_meta($term_id, 'twitter_url', true),
        'linkedin' => get_term_meta($term_id, 'linkedin_url', true),
    );
}

/**
 * Admin için media uploader scripti
 */
function gufte_credits_admin_scripts() {
    $screen = get_current_screen();
    
    if ($screen && in_array($screen->taxonomy, array('producer', 'songwriter', 'composer'))) {
        wp_enqueue_media();
    }
}
add_action('admin_enqueue_scripts', 'gufte_credits_admin_scripts');