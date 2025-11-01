<?php
/**
 * Arcuras Theme Settings
 *
 * This file handles all theme settings and options panel
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Arcuras Theme Settings Menu
 */
function arcuras_theme_settings_menu() {
    add_menu_page(
        'Arcuras',
        'Arcuras',
        'manage_options',
        'arcuras-theme-settings',
        'arcuras_theme_settings_page',
        'dashicons-admin-settings',
        2
    );
}
add_action('admin_menu', 'arcuras_theme_settings_menu');

/**
 * Theme Settings Page
 */
function arcuras_theme_settings_page() {
    // Save settings
    if (isset($_POST['arcuras_settings_submit'])) {
        check_admin_referer('arcuras_settings_nonce');

        // General Settings
        update_option('arcuras_site_logo', sanitize_text_field($_POST['arcuras_site_logo']));
        update_option('arcuras_footer_text', wp_kses_post($_POST['arcuras_footer_text']));
        update_option('arcuras_contact_email', sanitize_email($_POST['arcuras_contact_email']));
        update_option('arcuras_contact_phone', sanitize_text_field($_POST['arcuras_contact_phone']));

        // Social Media
        update_option('arcuras_facebook_url', esc_url($_POST['arcuras_facebook_url']));
        update_option('arcuras_twitter_url', esc_url($_POST['arcuras_twitter_url']));
        update_option('arcuras_instagram_url', esc_url($_POST['arcuras_instagram_url']));
        update_option('arcuras_linkedin_url', esc_url($_POST['arcuras_linkedin_url']));

        // SEO & Analytics
        update_option('arcuras_google_analytics', sanitize_text_field($_POST['arcuras_google_analytics']));

        // Google OAuth
        update_option('arcuras_google_client_id', sanitize_text_field($_POST['arcuras_google_client_id']));
        update_option('arcuras_google_client_secret', sanitize_text_field($_POST['arcuras_google_client_secret']));

        // AI Translation Settings
        update_option('arcuras_gemini_api_key', sanitize_text_field($_POST['arcuras_gemini_api_key']));
        update_option('arcuras_ai_model', sanitize_text_field($_POST['arcuras_ai_model']));
        update_option('arcuras_ai_custom_instructions', wp_kses_post($_POST['arcuras_ai_custom_instructions']));
        update_option('arcuras_google_oauth_enabled', isset($_POST['arcuras_google_oauth_enabled']) ? '1' : '0');
        update_option('arcuras_google_one_tap_enabled', isset($_POST['arcuras_google_one_tap_enabled']) ? '1' : '0');

        echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully!</p></div>';
    }

    // Get current settings
    $site_logo = get_option('arcuras_site_logo', '');
    $footer_text = get_option('arcuras_footer_text', '');
    $contact_email = get_option('arcuras_contact_email', '');
    $contact_phone = get_option('arcuras_contact_phone', '');
    $facebook_url = get_option('arcuras_facebook_url', '');
    $twitter_url = get_option('arcuras_twitter_url', '');
    $instagram_url = get_option('arcuras_instagram_url', '');
    $linkedin_url = get_option('arcuras_linkedin_url', '');
    $google_analytics = get_option('arcuras_google_analytics', '');
    $google_client_id = get_option('arcuras_google_client_id', '');
    $google_client_secret = get_option('arcuras_google_client_secret', '');
    $google_oauth_enabled = get_option('arcuras_google_oauth_enabled', '0');
    $google_one_tap_enabled = get_option('arcuras_google_one_tap_enabled', '1');

    // Get active tab
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';

    ?>
    <div class="wrap">
        <h1>Arcuras</h1>

        <h2 class="nav-tab-wrapper">
            <a href="?page=arcuras-theme-settings&tab=setup"
               class="nav-tab <?php echo $active_tab == 'setup' ? 'nav-tab-active' : ''; ?>">
                🚀 Setup
            </a>
            <a href="?page=arcuras-theme-settings&tab=general"
               class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">
                General
            </a>
            <a href="?page=arcuras-theme-settings&tab=social"
               class="nav-tab <?php echo $active_tab == 'social' ? 'nav-tab-active' : ''; ?>">
                Social Media
            </a>
            <a href="?page=arcuras-theme-settings&tab=seo"
               class="nav-tab <?php echo $active_tab == 'seo' ? 'nav-tab-active' : ''; ?>">
                SEO & Analytics
            </a>
            <a href="?page=arcuras-theme-settings&tab=oauth"
               class="nav-tab <?php echo $active_tab == 'oauth' ? 'nav-tab-active' : ''; ?>">
                Google OAuth
            </a>
            <a href="?page=arcuras-theme-settings&tab=ai-translation"
               class="nav-tab <?php echo $active_tab == 'ai-translation' ? 'nav-tab-active' : ''; ?>">
                🤖 AI Translation
            </a>
        </h2>

        <form method="post" action="?page=arcuras-theme-settings&tab=<?php echo $active_tab; ?>">
            <?php wp_nonce_field('arcuras_settings_nonce'); ?>

            <?php if ($active_tab == 'setup'): ?>
                <div style="max-width: 800px;">
                    <h2>🚀 Theme Setup Wizard</h2>
                    <p>Automatically create all required theme pages with one click.</p>

                    <?php
                    // Handle page creation
                    if (isset($_POST['create_theme_pages'])) {
                        check_admin_referer('arcuras_settings_nonce');
                        $created_pages = arcuras_create_theme_pages();
                        if (!empty($created_pages)) {
                            echo '<div class="notice notice-success"><p><strong>✅ Pages created successfully!</strong></p><ul>';
                            foreach ($created_pages as $page) {
                                echo '<li>' . esc_html($page['title']) . ' - <a href="' . esc_url($page['url']) . '" target="_blank">View</a> | <a href="' . esc_url(get_edit_post_link($page['id'])) . '">Edit</a></li>';
                            }
                            echo '</ul></div>';
                        }
                    }
                    ?>

                    <table class="form-table">
                        <tr>
                            <th scope="row">Required Pages</th>
                            <td>
                                <p>The following pages will be created:</p>
                                <ul style="list-style: disc; margin-left: 20px;">
                                    <li><strong>Sign In</strong> (page-sign-in.php template)</li>
                                    <li><strong>Register</strong> (page-register.php template)</li>
                                    <li><strong>Profile</strong> (template-profile.php template)</li>
                                    <li><strong>Contributor</strong> (page-contributor.php template)</li>
                                    <li><strong>Lyrics</strong> (template-lyrics.php template)</li>
                                    <li><strong>Categories</strong> (template-categories.php template)</li>
                                    <li><strong>Languages</strong> (languages.php template)</li>
                                    <li><strong>Singers</strong> (template-singers.php template)</li>
                                    <li><strong>Albums</strong> (template-albums.php template)</li>
                                    <li><strong>Producers</strong> (template-producers.php template)</li>
                                    <li><strong>Songwriters</strong> (template-songwriters.php template)</li>
                                    <li><strong>Contribute Translation</strong> (template-contribute-translation.php template)</li>
                                    <li><strong>About</strong> (page-about.php template)</li>
                                    <li><strong>Recent Views</strong> (template-recent-views.php template)</li>
                                </ul>
                                <p class="description">Note: If a page already exists, it will be skipped.</p>
                            </td>
                        </tr>
                    </table>

                    <p class="submit">
                        <button type="submit" name="create_theme_pages" class="button button-primary button-hero">
                            🎨 Create All Theme Pages
                        </button>
                    </p>
                </div>

            <?php elseif ($active_tab == 'general'): ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="arcuras_site_logo">Site Logo URL</label>
                        </th>
                        <td>
                            <input type="text" id="arcuras_site_logo" name="arcuras_site_logo"
                                   value="<?php echo esc_attr($site_logo); ?>" class="regular-text">
                            <p class="description">Enter your site logo URL.</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="arcuras_footer_text">Footer Text</label>
                        </th>
                        <td>
                            <textarea id="arcuras_footer_text" name="arcuras_footer_text"
                                      rows="3" class="large-text"><?php echo esc_textarea($footer_text); ?></textarea>
                            <p class="description">Text to appear in the footer.</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="arcuras_contact_email">Contact Email</label>
                        </th>
                        <td>
                            <input type="email" id="arcuras_contact_email" name="arcuras_contact_email"
                                   value="<?php echo esc_attr($contact_email); ?>" class="regular-text">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="arcuras_contact_phone">Contact Phone</label>
                        </th>
                        <td>
                            <input type="text" id="arcuras_contact_phone" name="arcuras_contact_phone"
                                   value="<?php echo esc_attr($contact_phone); ?>" class="regular-text">
                        </td>
                    </tr>
                </table>

            <?php elseif ($active_tab == 'social'): ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="arcuras_facebook_url">Facebook URL</label>
                        </th>
                        <td>
                            <input type="url" id="arcuras_facebook_url" name="arcuras_facebook_url"
                                   value="<?php echo esc_attr($facebook_url); ?>" class="regular-text">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="arcuras_twitter_url">Twitter URL</label>
                        </th>
                        <td>
                            <input type="url" id="arcuras_twitter_url" name="arcuras_twitter_url"
                                   value="<?php echo esc_attr($twitter_url); ?>" class="regular-text">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="arcuras_instagram_url">Instagram URL</label>
                        </th>
                        <td>
                            <input type="url" id="arcuras_instagram_url" name="arcuras_instagram_url"
                                   value="<?php echo esc_attr($instagram_url); ?>" class="regular-text">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="arcuras_linkedin_url">LinkedIn URL</label>
                        </th>
                        <td>
                            <input type="url" id="arcuras_linkedin_url" name="arcuras_linkedin_url"
                                   value="<?php echo esc_attr($linkedin_url); ?>" class="regular-text">
                        </td>
                    </tr>
                </table>

            <?php elseif ($active_tab == 'seo'): ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="arcuras_google_analytics">Google Analytics ID</label>
                        </th>
                        <td>
                            <input type="text" id="arcuras_google_analytics" name="arcuras_google_analytics"
                                   value="<?php echo esc_attr($google_analytics); ?>" class="regular-text">
                            <p class="description">Example: G-XXXXXXXXXX or UA-XXXXXXXXX-X</p>
                        </td>
                    </tr>
                </table>

            <?php elseif ($active_tab == 'oauth'): ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="arcuras_google_oauth_enabled">Enable Google Sign-In</label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" id="arcuras_google_oauth_enabled" name="arcuras_google_oauth_enabled"
                                       value="1" <?php checked($google_oauth_enabled, '1'); ?>>
                                Enable Google OAuth Sign-In
                            </label>
                            <p class="description">Allow users to sign in with their Google account</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="arcuras_google_one_tap_enabled">Enable Google One Tap</label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" id="arcuras_google_one_tap_enabled" name="arcuras_google_one_tap_enabled"
                                       value="1" <?php checked($google_one_tap_enabled, '1'); ?>>
                                Enable Google One Tap Sign-In popup
                            </label>
                            <p class="description">Show a small popup in the corner for quick Google sign-in on all pages (for non-logged-in users)</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="arcuras_google_client_id">Google Client ID</label>
                        </th>
                        <td>
                            <input type="text" id="arcuras_google_client_id" name="arcuras_google_client_id"
                                   value="<?php echo esc_attr($google_client_id); ?>" class="large-text">
                            <p class="description">Get this from <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Google Cloud Console</a></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="arcuras_google_client_secret">Google Client Secret</label>
                        </th>
                        <td>
                            <input type="password" id="arcuras_google_client_secret" name="arcuras_google_client_secret"
                                   value="<?php echo esc_attr($google_client_secret); ?>" class="large-text">
                            <p class="description">Get this from <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Google Cloud Console</a></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label>Authorized Redirect URI</label>
                        </th>
                        <td>
                            <input type="text" readonly value="<?php echo esc_attr(home_url('/oauth/google/callback')); ?>" class="large-text" onclick="this.select();">
                            <p class="description">
                                <strong>Copy this URL and add it to your Google Cloud Console:</strong><br>
                                1. Go to <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Google Cloud Console</a><br>
                                2. Select your OAuth 2.0 Client ID<br>
                                3. Add this URL to "Authorized redirect URIs"<br>
                                4. Also add your site URL (<?php echo home_url(); ?>) to "Authorized JavaScript origins"
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label>Test Configuration</label>
                        </th>
                        <td>
                            <?php if (!empty($google_client_id) && !empty($google_client_secret)): ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=arcuras-theme-settings&tab=oauth&action=test_google_oauth'), 'test_google_oauth')); ?>"
                                   class="button button-secondary">
                                    Test Google OAuth Connection
                                </a>
                                <p class="description">Click to test if your Google OAuth credentials are working correctly</p>

                                <?php
                                // Show test results
                                if (isset($_GET['action']) && $_GET['action'] == 'test_google_oauth' && isset($_GET['_wpnonce'])) {
                                    if (wp_verify_nonce($_GET['_wpnonce'], 'test_google_oauth')) {
                                        $test_result = arcuras_test_google_oauth_connection($google_client_id, $google_client_secret);
                                        if ($test_result['success']) {
                                            echo '<div class="notice notice-success inline" style="margin: 10px 0; padding: 10px;"><p><strong>✓ Success!</strong> ' . esc_html($test_result['message']) . '</p></div>';
                                        } else {
                                            echo '<div class="notice notice-error inline" style="margin: 10px 0; padding: 10px;"><p><strong>✗ Error:</strong> ' . esc_html($test_result['message']) . '</p></div>';
                                        }
                                    }
                                }
                                ?>
                            <?php else: ?>
                                <p class="description" style="color: #d63638;">Please enter both Client ID and Client Secret before testing.</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>

            <?php elseif ($active_tab == 'ai-translation'): ?>
                <table class="form-table">
                    <tr>
                        <th scope="row" colspan="2">
                            <h2 style="margin-top: 0;">🤖 AI Translation for Lyrics</h2>
                            <p style="font-size: 14px; font-weight: normal; color: #666;">
                                Configure AI-powered translation for song lyrics. Choose your preferred provider and customize translation style.
                            </p>
                        </th>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="arcuras_gemini_api_key">Google Gemini API Key</label>
                        </th>
                        <td>
                            <input type="password" id="arcuras_gemini_api_key" name="arcuras_gemini_api_key"
                                   value="<?php echo esc_attr(get_option('arcuras_gemini_api_key', '')); ?>" class="large-text">
                            <p class="description">
                                Enter your Google Gemini API key. Get it from <a href="https://makersuite.google.com/app/apikey" target="_blank">Google AI Studio</a>.<br>
                                <strong>This key is used for both OCR (image text extraction) and lyrics translation.</strong>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="arcuras_ai_model">Gemini Translation Model</label>
                        </th>
                        <td>
                            <select id="arcuras_ai_model" name="arcuras_ai_model" class="regular-text">
                                <optgroup label="🤖 Gemini Models">
                                    <option value="gemini-2.0-flash-exp" <?php selected(get_option('arcuras_ai_model', 'gemini-2.0-flash-exp'), 'gemini-2.0-flash-exp'); ?>>
                                        Gemini 2.0 Flash (Recommended - Latest & Fastest)
                                    </option>
                                    <option value="gemini-1.5-pro" <?php selected(get_option('arcuras_ai_model', 'gemini-2.0-flash-exp'), 'gemini-1.5-pro'); ?>>
                                        Gemini 1.5 Pro (Highest Quality)
                                    </option>
                                    <option value="gemini-1.5-flash" <?php selected(get_option('arcuras_ai_model', 'gemini-2.0-flash-exp'), 'gemini-1.5-flash'); ?>>
                                        Gemini 1.5 Flash (Fast & Efficient)
                                    </option>
                                </optgroup>
                            </select>
                            <p class="description">
                                Choose the Gemini model for translation.<br>
                                <strong>Gemini 2.0 Flash</strong> provides the best balance of quality and speed (Recommended).<br>
                                <strong>Gemini 1.5 Pro</strong> offers highest quality but slower.<br>
                                <strong>Gemini 1.5 Flash</strong> is the fastest option.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="arcuras_ai_custom_instructions">Custom Translation Instructions</label>
                        </th>
                        <td>
                            <textarea id="arcuras_ai_custom_instructions" name="arcuras_ai_custom_instructions"
                                      rows="12" class="large-text code" style="font-family: monospace;"><?php echo esc_textarea(get_option('arcuras_ai_custom_instructions', '')); ?></textarea>
                            <p class="description">
                                <strong>Optional:</strong> Add custom instructions to guide how the AI should translate lyrics.<br>
                                <strong>Translation Quality Tips:</strong><br>
                                • Be specific about slang and colloquial expressions<br>
                                • Define how to handle explicit/sexual content<br>
                                • Specify cultural adaptation preferences<br>
                                • Set tone (formal, casual, street slang, etc.)<br>
                                <br>
                                <strong>Example for Turkish translations:</strong><br>
                                <code style="display: block; background: #f5f5f5; padding: 10px; margin: 5px 0; white-space: pre-wrap;">- "My man" gibi ifadeleri "erkeğim" olarak çevir, "adamım" değil
- Cinsel içerikli slang'leri açık ve doğru çevir, yumuşatma
- Günlük konuşma dilini kullan, literal çeviriden kaçın
- R&B/Hip-Hop kültürüne uygun Türkçe kullan
- Kafiye ve akış önemli, kelime kelime çevirme</code>
                                <br>
                                <strong>Default behavior (if empty):</strong><br>
                                - Preserve poetic and emotional tone<br>
                                - Keep rhyme and rhythm where possible<br>
                                - Maintain section tags ([Chorus], [Verse], etc.)<br>
                                - Focus on meaning rather than literal translation
                            </p>
                        </td>
                    </tr>
                </table>
            <?php endif; ?>

            <p class="submit">
                <input type="submit" name="arcuras_settings_submit" class="button button-primary"
                       value="Save Settings">
            </p>
        </form>
    </div>
    <?php
}

/**
 * Test Google OAuth Connection
 *
 * @param string $client_id Google Client ID
 * @param string $client_secret Google Client Secret
 * @return array Result with success status and message
 */
function arcuras_test_google_oauth_connection($client_id, $client_secret) {
    // Validate inputs
    if (empty($client_id) || empty($client_secret)) {
        return array(
            'success' => false,
            'message' => 'Client ID and Client Secret are required.'
        );
    }

    // Test Google OAuth endpoint
    $token_endpoint = 'https://oauth2.googleapis.com/token';

    // Try to validate the credentials by checking if they're well-formed
    // Client ID should be in format: xxx-yyy.apps.googleusercontent.com
    if (!preg_match('/^[0-9]+-[a-z0-9]+\.apps\.googleusercontent\.com$/', $client_id)) {
        return array(
            'success' => false,
            'message' => 'Client ID format is invalid. It should end with .apps.googleusercontent.com'
        );
    }

    // Test the discovery document endpoint
    $discovery_url = 'https://accounts.google.com/.well-known/openid-configuration';
    $response = wp_remote_get($discovery_url, array(
        'timeout' => 10,
        'headers' => array(
            'Accept' => 'application/json'
        )
    ));

    if (is_wp_error($response)) {
        return array(
            'success' => false,
            'message' => 'Cannot connect to Google OAuth servers: ' . $response->get_error_message()
        );
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        return array(
            'success' => false,
            'message' => 'Google OAuth service returned error code: ' . $response_code
        );
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!$data || !isset($data['authorization_endpoint'])) {
        return array(
            'success' => false,
            'message' => 'Invalid response from Google OAuth servers.'
        );
    }

    // Everything looks good
    return array(
        'success' => true,
        'message' => 'Configuration appears valid! Google OAuth servers are reachable. Authorization endpoint: ' . $data['authorization_endpoint']
    );
}

/**
 * Create all required theme pages
 * 
 * @return array Array of created pages
 */
function arcuras_create_theme_pages() {
    $created_pages = array();
    
    $pages = array(
        array(
            'title' => 'Sign In',
            'slug' => 'sign-in',
            'template' => 'page-templates/page-sign-in.php'
        ),
        array(
            'title' => 'Register',
            'slug' => 'register',
            'template' => 'page-templates/page-register.php'
        ),
        array(
            'title' => 'Profile',
            'slug' => 'profile',
            'template' => 'page-templates/template-profile.php'
        ),
        array(
            'title' => 'Contributor',
            'slug' => 'contributor',
            'template' => 'page-templates/page-contributor.php'
        ),
        array(
            'title' => 'Lyrics',
            'slug' => 'lyrics',
            'template' => 'page-templates/template-lyrics.php'
        ),
        array(
            'title' => 'Categories',
            'slug' => 'categories',
            'template' => 'page-templates/template-categories.php'
        ),
        array(
            'title' => 'Languages',
            'slug' => 'languages',
            'template' => 'page-templates/languages.php'
        ),
        array(
            'title' => 'Singers',
            'slug' => 'singers',
            'template' => 'page-templates/template-singers.php'
        ),
        array(
            'title' => 'Albums',
            'slug' => 'albums',
            'template' => 'page-templates/template-albums.php'
        ),
        array(
            'title' => 'Producers',
            'slug' => 'producers',
            'template' => 'page-templates/template-producers.php'
        ),
        array(
            'title' => 'Songwriters',
            'slug' => 'songwriters',
            'template' => 'page-templates/template-songwriters.php'
        ),
        array(
            'title' => 'Contribute Translation',
            'slug' => 'contribute-translation',
            'template' => 'page-templates/template-contribute-translation.php'
        ),
        array(
            'title' => 'About',
            'slug' => 'about',
            'template' => 'page-templates/page-about.php'
        ),
        array(
            'title' => 'Recent Views',
            'slug' => 'recent-views',
            'template' => 'page-templates/template-recent-views.php'
        ),
    );
    
    foreach ($pages as $page_data) {
        // Check if page already exists
        $existing_page = get_page_by_path($page_data['slug']);
        
        if ($existing_page) {
            continue; // Skip if page already exists
        }
        
        // Create page
        $page_id = wp_insert_post(array(
            'post_title' => $page_data['title'],
            'post_name' => $page_data['slug'],
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_content' => '' // Empty content, template will handle display
        ));
        
        if ($page_id && !is_wp_error($page_id)) {
            // Set page template
            update_post_meta($page_id, '_wp_page_template', $page_data['template']);
            
            $created_pages[] = array(
                'id' => $page_id,
                'title' => $page_data['title'],
                'url' => get_permalink($page_id)
            );
        }
    }
    
    return $created_pages;
}
