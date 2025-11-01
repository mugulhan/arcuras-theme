<?php
/**
 * Template Name: User Profile Page
 *
 * Allows logged-in users to view and edit their profile information.
 * Includes the Arcuras Sidebar on the left.
 *
 * @package Gufte
 */

// Redirect non-logged-in users
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

// --- Process Profile Update ---
$update_message = '';
$error_message = '';
$current_user = wp_get_current_user();

// Check if we just redirected after a successful update
if (isset($_GET['profile_updated']) && $_GET['profile_updated'] === 'success') {
    $update_message = __('Profile updated successfully!', 'gufte');
}

// Get user's language preference (default to 'en' if not set)
$user_language = get_user_meta($current_user->ID, 'user_language', true);
if (empty($user_language)) {
    $user_language = 'en'; // Default to English
}

// Get user's known languages for translation contributions
$user_known_languages = get_user_meta($current_user->ID, 'user_known_languages', true);
if (!is_array($user_known_languages)) {
    $user_known_languages = array();
}

if ('POST' == $_SERVER['REQUEST_METHOD'] && !empty($_POST['action']) && $_POST['action'] == 'update-user-profile') {

    // Verify nonce for security
    if (!isset($_POST['profile_nonce_field']) || !wp_verify_nonce($_POST['profile_nonce_field'], 'update_user_profile_' . $current_user->ID)) {
        $error_message = __('Security check failed. Please refresh and try again.', 'gufte');
    } else {
        // Check permissions
        if (!current_user_can('edit_user', $current_user->ID)) {
            $error_message = __('You do not have permission to edit this profile.', 'gufte');
        } else {
            $user_id = $current_user->ID;
            
            // Sanitize and validate input data
            $first_name = sanitize_text_field($_POST['first_name'] ?? '');
            $last_name = sanitize_text_field($_POST['last_name'] ?? '');
            $nickname = sanitize_text_field($_POST['nickname'] ?? '');
            $display_name = sanitize_text_field($_POST['display_name'] ?? '');
            $email = sanitize_email($_POST['email'] ?? '');
            $url = esc_url_raw($_POST['url'] ?? '');
            $description = sanitize_textarea_field($_POST['description'] ?? '');
            $pass1 = $_POST['pass1'] ?? '';
            $pass2 = $_POST['pass2'] ?? '';
            $user_language = sanitize_text_field($_POST['user_language'] ?? 'en');
            $user_known_languages = isset($_POST['user_known_languages']) && is_array($_POST['user_known_languages'])
                ? array_map('sanitize_text_field', $_POST['user_known_languages'])
                : array();

            // Validate required fields
            if (empty($nickname)) {
                $error_message .= __('Error: Please enter a nickname.', 'gufte') . '<br>';
            }

            if (empty($email)) {
                $error_message .= __('Error: Please enter your email address.', 'gufte') . '<br>';
            } elseif (!is_email($email)) {
                $error_message .= __('Error: Please enter a valid email address.', 'gufte') . '<br>';
            } elseif (email_exists($email) && $email !== $current_user->user_email) {
                $error_message .= __('Error: This email address is already registered.', 'gufte') . '<br>';
            }

            // Validate passwords if entered
            if (!empty($pass1) || !empty($pass2)) {
                if (empty($pass1) || empty($pass2)) {
                    $error_message .= __('Error: Please enter the new password in both fields.', 'gufte') . '<br>';
                } elseif ($pass1 !== $pass2) {
                    $error_message .= __('Error: The passwords do not match.', 'gufte') . '<br>';
                } elseif (strlen($pass1) < 6) {
                    $error_message .= __('Error: Password must be at least 6 characters long.', 'gufte') . '<br>';
                }
            }

            // If display name is empty, use nickname
            if (empty($display_name)) {
                $display_name = $nickname;
            }

            // If no errors, proceed with update
            if (empty($error_message)) {
                $user_data = array(
                    'ID' => $user_id,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'nickname' => $nickname,
                    'display_name' => $display_name,
                    'user_email' => $email,
                    'user_url' => $url,
                    'description' => $description,
                );

                // Only update password if new one was entered
                if (!empty($pass1)) {
                    $user_data['user_pass'] = $pass1;
                }

                $updated = wp_update_user($user_data);

                if (is_wp_error($updated)) {
                    $error_message = $updated->get_error_message();
                } else {
                    // Save language preference BEFORE any other processing
                    update_user_meta($user_id, 'user_language', $user_language);

                    // Save known languages for translation contributions
                    update_user_meta($user_id, 'user_known_languages', $user_known_languages);

                    do_action('personal_options_update', $user_id);

                    // CRITICAL: Redirect to reload the page with new locale
                    // This ensures the locale filter picks up the new language
                    wp_redirect(add_query_arg('profile_updated', 'success', get_permalink()));
                    exit;
                }
            }
        }
    }
}

get_header();
?>

<div class="site-content-wrapper flex flex-col md:flex-row">
    <?php get_template_part('template-parts/arcuras-sidebar'); ?>

    <main id="primary" class="site-main flex-1 bg-white overflow-x-hidden">

        <?php
        // Breadcrumb
        set_query_var('breadcrumb_items', array(
            array('label' => 'Home', 'url' => home_url('/')),
            array('label' => __('My Profile', 'gufte'))
        ));
        get_template_part('template-parts/page-components/page-breadcrumb');

        // Hero
        $posts_count = count_user_posts($current_user->ID);
        $member_since = human_time_diff(strtotime($current_user->user_registered), current_time('timestamp'));

        // Get browser and device info from User Agent
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Detect browser
        $browser = 'Unknown';
        if (preg_match('/Edge\/([0-9\.]+)/i', $user_agent)) {
            $browser = 'Edge';
        } elseif (preg_match('/Edg\/([0-9\.]+)/i', $user_agent)) {
            $browser = 'Edge';
        } elseif (preg_match('/Chrome\/([0-9\.]+)/i', $user_agent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Safari\/([0-9\.]+)/i', $user_agent) && !preg_match('/Chrome/i', $user_agent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Firefox\/([0-9\.]+)/i', $user_agent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/MSIE|Trident/i', $user_agent)) {
            $browser = 'Internet Explorer';
        } elseif (preg_match('/Opera|OPR\/([0-9\.]+)/i', $user_agent)) {
            $browser = 'Opera';
        }

        // Detect device/platform
        $device = 'Desktop';
        if (preg_match('/Mobile|Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $user_agent)) {
            if (preg_match('/iPad/i', $user_agent)) {
                $device = 'iPad';
            } elseif (preg_match('/iPhone/i', $user_agent)) {
                $device = 'iPhone';
            } elseif (preg_match('/Android/i', $user_agent)) {
                if (preg_match('/Mobile/i', $user_agent)) {
                    $device = 'Android Phone';
                } else {
                    $device = 'Android Tablet';
                }
            } else {
                $device = 'Mobile';
            }
        } elseif (preg_match('/Macintosh|Mac OS X/i', $user_agent)) {
            $device = 'Mac';
        } elseif (preg_match('/Windows/i', $user_agent)) {
            $device = 'Windows';
        } elseif (preg_match('/Linux/i', $user_agent)) {
            $device = 'Linux';
        }

        set_query_var('hero_title', __('My Profile', 'gufte'));
        set_query_var('hero_icon', 'account-edit');
        set_query_var('hero_description', __('Manage your account settings and profile information.', 'gufte'));
        set_query_var('hero_meta', array(
            __('Member Since', 'gufte') => $member_since,
            __('Posts Published', 'gufte') => number_format_i18n($posts_count),
            __('User Role', 'gufte') => ucfirst(implode(', ', $current_user->roles)),
            __('Browser', 'gufte') => $browser,
            __('Device', 'gufte') => $device
        ));
        get_template_part('template-parts/page-components/page-hero');
        ?>

        <div class="px-4 sm:px-6 lg:px-8 py-6">

            <!-- Messages -->
            <?php if (!empty($update_message)) : ?>
            <div class="success-message mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center">
                <?php gufte_icon('check-circle', 'mr-3 text-green-600 text-xl'); ?>
                <?php echo esc_html($update_message); ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($error_message)) : ?>
            <div class="error-message mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg flex items-center">
                <?php gufte_icon('alert-circle', 'mr-3 text-red-600 text-xl'); ?>
                <div><?php echo $error_message; ?></div>
            </div>
            <?php endif; ?>

            <!-- Tabs Navigation -->
            <div class="mb-6 bg-white rounded-lg shadow-md border border-gray-200">
                <div class="flex border-b border-gray-200">
                    <button type="button"
                            class="profile-tab active flex items-center px-6 py-4 text-sm font-medium border-b-2 border-primary-600 text-primary-600 focus:outline-none"
                            data-tab="tab-profile-info">
                        <?php gufte_icon('account', 'mr-2'); ?>
                        <?php esc_html_e('Profile Information', 'gufte'); ?>
                    </button>
                    <button type="button"
                            class="profile-tab flex items-center px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 focus:outline-none"
                            data-tab="tab-my-translations">
                        <?php gufte_icon('translate', 'mr-2'); ?>
                        <?php esc_html_e('My Translations', 'gufte'); ?>
                        <?php
                        // Get translation count
                        $translation_count = wp_count_posts('translation_submission');
                        $user_translations = get_posts(array(
                            'post_type' => 'translation_submission',
                            'author' => $current_user->ID,
                            'posts_per_page' => -1,
                            'fields' => 'ids',
                        ));
                        $total_count = count($user_translations);
                        if ($total_count > 0) :
                        ?>
                        <span class="ml-2 px-2 py-0.5 bg-primary-100 text-primary-700 text-xs font-semibold rounded-full">
                            <?php echo $total_count; ?>
                        </span>
                        <?php endif; ?>
                    </button>
                    <button type="button"
                            class="profile-tab flex items-center px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 focus:outline-none"
                            data-tab="tab-saved-lines">
                        <?php gufte_icon('bookmark', 'mr-2'); ?>
                        <?php esc_html_e('Saved Lines', 'gufte'); ?>
                        <?php
                        // Get saved lines count
                        $saved_lines = get_user_meta($current_user->ID, 'arcuras_saved_lyric_lines', true);
                        $saved_count = is_array($saved_lines) ? count($saved_lines) : 0;
                        if ($saved_count > 0) :
                        ?>
                        <span class="ml-2 px-2 py-0.5 bg-purple-100 text-purple-700 text-xs font-semibold rounded-full">
                            <?php echo $saved_count; ?>
                        </span>
                        <?php endif; ?>
                    </button>
                </div>
            </div>

        <!-- Tab Content: Profile Information -->
        <div id="tab-profile-info" class="tab-content">
            <form method="post" id="profile-form" action="<?php echo esc_url(get_permalink()); ?>" class="space-y-8">
                <?php wp_nonce_field('update_user_profile_' . $current_user->ID, 'profile_nonce_field'); ?>
                <input type="hidden" name="action" value="update-user-profile">

                <!-- Profile Information Card -->
                <div class="profile-info bg-white rounded-lg shadow-md p-6 border border-gray-200">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 pb-4 border-b border-gray-200 flex items-center">
                        <?php gufte_icon('account', 'mr-3 text-blue-600'); ?>
                        <?php esc_html_e('Personal Information', 'gufte'); ?>
                    </h2>

                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                        <!-- Avatar Section -->
                        <div class="lg:col-span-1">
                            <div class="text-center">
                                <div class="w-32 h-32 mx-auto rounded-full overflow-hidden border-4 border-primary-400 shadow-lg mb-4">
                                    <?php echo get_avatar($current_user->ID, 128, ', ', array('class' => 'w-full h-full object-cover')); ?>
                                </div>
                                <p class="text-xs text-gray-600 mb-4">
                                    <?php printf(__('Change your avatar at %s', 'gufte'), '<a href="https://gravatar.com/" target="_blank" rel="noopener noreferrer" class="text-primary-600 hover:text-primary-800 underline">Gravatar.com</a>'); ?>
                                </p>
                                
                                <!-- User Stats -->
                                <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                                    <div class="text-center">
                                        <div class="text-xl font-bold text-primary-600">
                                            <?php echo count_user_posts($current_user->ID); ?>
                                        </div>
                                        <div class="text-xs text-gray-600"><?php esc_html_e('Posts Published', 'gufte'); ?></div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-sm font-medium text-gray-800">
                                            <?php echo esc_html(human_time_diff(strtotime($current_user->user_registered), current_time('timestamp'))); ?>
                                        </div>
                                        <div class="text-xs text-gray-600"><?php esc_html_e('Member Since', 'gufte'); ?></div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-xs font-medium text-gray-700">
                                            <?php echo esc_html(ucfirst(implode(', ', $current_user->roles))); ?>
                                        </div>
                                        <div class="text-xs text-gray-600"><?php esc_html_e('User Role', 'gufte'); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Fields -->
                        <div class="lg:col-span-3 space-y-6">
                            <!-- Name Fields Row -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="form-group">
                                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        <?php esc_html_e('First Name', 'gufte'); ?>
                                    </label>
                                    <input type="text" 
                                           id="first_name" 
                                           name="first_name" 
                                           value="<?php echo esc_attr($current_user->first_name); ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-300">
                                </div>

                                <div class="form-group">
                                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        <?php esc_html_e('Last Name', 'gufte'); ?>
                                    </label>
                                    <input type="text" 
                                           id="last_name" 
                                           name="last_name" 
                                           value="<?php echo esc_attr($current_user->last_name); ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-300">
                                </div>
                            </div>

                            <!-- Username (Read-only) -->
                            <div class="form-group">
                                <label for="user_login" class="block text-sm font-medium text-gray-700 mb-2">
                                    <?php esc_html_e('Username', 'gufte'); ?>
                                </label>
                                <input type="text" 
                                       id="user_login" 
                                       value="<?php echo esc_attr($current_user->user_login); ?>" 
                                       disabled 
                                       class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg text-gray-500 cursor-not-allowed">
                                <p class="text-xs text-gray-600 mt-1">
                                    <?php esc_html_e('Username cannot be changed.', 'gufte'); ?>
                                </p>
                            </div>

                            <!-- Nickname and Display Name Row -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="form-group">
                                    <label for="nickname" class="block text-sm font-medium text-gray-700 mb-2">
                                        <?php esc_html_e('Nickname', 'gufte'); ?> <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           id="nickname" 
                                           name="nickname" 
                                           value="<?php echo esc_attr($current_user->nickname); ?>" 
                                           required
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-300">
                                    <p class="text-xs text-gray-600 mt-1">
                                        <?php esc_html_e('This field is required.', 'gufte'); ?>
                                    </p>
                                </div>

                                <div class="form-group">
                                    <label for="display_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        <?php esc_html_e('Display Name', 'gufte'); ?>
                                    </label>
                                    <select id="display_name" 
                                            name="display_name" 
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-300">
                                        <?php
                                        $display_options = array();
                                        $display_options[$current_user->user_login] = $current_user->user_login;
                                        $display_options[$current_user->nickname] = $current_user->nickname;
                                        if (!empty($current_user->first_name)) {
                                            $display_options[$current_user->first_name] = $current_user->first_name;
                                        }
                                        if (!empty($current_user->last_name)) {
                                            $display_options[$current_user->last_name] = $current_user->last_name;
                                        }
                                        if (!empty($current_user->first_name) && !empty($current_user->last_name)) {
                                            $display_options[$current_user->first_name . ' ' . $current_user->last_name] = $current_user->first_name . ' ' . $current_user->last_name;
                                            $display_options[$current_user->last_name . ' ' . $current_user->first_name] = $current_user->last_name . ' ' . $current_user->first_name;
                                        }
                                        foreach ($display_options as $value => $label) :
                                            $selected = ($value == $current_user->display_name) ? 'selected' : '';
                                        ?>
                                            <option value="<?php echo esc_attr($value); ?>" <?php echo $selected; ?>>
                                                <?php echo esc_html($label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="text-xs text-gray-600 mt-1">
                                        <?php esc_html_e('Choose how your name appears publicly.', 'gufte'); ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="form-group">
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    <?php esc_html_e('Email Address', 'gufte'); ?> <span class="text-red-500">*</span>
                                </label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo esc_attr($current_user->user_email); ?>" 
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-300">
                            </div>

                            <!-- Website -->
                            <div class="form-group">
                                <label for="url" class="block text-sm font-medium text-gray-700 mb-2">
                                    <?php esc_html_e('Website', 'gufte'); ?>
                                </label>
                                <input type="url" 
                                       id="url" 
                                       name="url" 
                                       value="<?php echo esc_attr($current_user->user_url); ?>" 
                                       placeholder="https://"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-300">
                            </div>

                            <!-- Bio -->
                            <div class="form-group">
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                    <?php esc_html_e('Biographical Info', 'gufte'); ?>
                                </label>
                                <textarea id="description"
                                         name="description"
                                         rows="4"
                                         placeholder="<?php esc_attr_e('Tell us a bit about yourself...', 'gufte'); ?>"
                                         class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-300 resize-vertical"><?php echo esc_textarea($current_user->description); ?></textarea>
                                <p class="text-xs text-gray-600 mt-1">
                                    <?php esc_html_e('Share some information about yourself. This may be shown publicly.', 'gufte'); ?>
                                </p>
                            </div>

                            <!-- Language Preference -->
                            <div class="form-group">
                                <label for="user_language" class="block text-sm font-medium text-gray-700 mb-2">
                                    <?php gufte_icon('translate', 'mr-2'); ?>
                                    <?php esc_html_e('Language Preference', 'gufte'); ?>
                                </label>
                                <select id="user_language"
                                        name="user_language"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-300">
                                    <option value="en" <?php selected($user_language, 'en'); ?>>English</option>
                                    <option value="tr" <?php selected($user_language, 'tr'); ?>>Türkçe</option>
                                </select>
                                <p class="text-xs text-gray-600 mt-1">
                                    <?php esc_html_e('Select your preferred language for the site interface.', 'gufte'); ?>
                                </p>
                            </div>

                            <!-- Known Languages for Translation Contributions -->
                            <div class="form-group">
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    <?php gufte_icon('google-translate', 'mr-2'); ?>
                                    <?php esc_html_e('Languages I Know', 'gufte'); ?>
                                </label>
                                <div class="bg-gray-50 border border-gray-300 rounded-lg p-4">
                                    <p class="text-xs text-gray-600 mb-3">
                                        <?php esc_html_e('Select the languages you know. We\'ll show you translation opportunities for songs in these languages.', 'gufte'); ?>
                                    </p>
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                        <?php
                                        // Available languages for translation
                                        $available_languages = array(
                                            'english' => array('name' => 'English', 'flag' => '🇬🇧'),
                                            'spanish' => array('name' => 'Español', 'flag' => '🇪🇸'),
                                            'turkish' => array('name' => 'Türkçe', 'flag' => '🇹🇷'),
                                            'german' => array('name' => 'Deutsch', 'flag' => '🇩🇪'),
                                            'french' => array('name' => 'Français', 'flag' => '🇫🇷'),
                                            'italian' => array('name' => 'Italiano', 'flag' => '🇮🇹'),
                                            'portuguese' => array('name' => 'Português', 'flag' => '🇵🇹'),
                                            'russian' => array('name' => 'Русский', 'flag' => '🇷🇺'),
                                            'arabic' => array('name' => 'العربية', 'flag' => '🇸🇦'),
                                            'japanese' => array('name' => '日本語', 'flag' => '🇯🇵'),
                                        );

                                        foreach ($available_languages as $lang_code => $lang_data) :
                                            $checked = in_array($lang_code, $user_known_languages) ? 'checked' : '';
                                        ?>
                                        <label class="flex items-center space-x-2 cursor-pointer group">
                                            <input type="checkbox"
                                                   name="user_known_languages[]"
                                                   value="<?php echo esc_attr($lang_code); ?>"
                                                   <?php echo $checked; ?>
                                                   class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                            <span class="text-lg"><?php echo $lang_data['flag']; ?></span>
                                            <span class="text-sm text-gray-700 group-hover:text-primary-600 transition-colors">
                                                <?php echo esc_html($lang_data['name']); ?>
                                            </span>
                                        </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Password Change Card -->
                <div class="password-section bg-white rounded-lg shadow-md p-6 border border-gray-200">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 pb-4 border-b border-gray-200 flex items-center">
                        <?php gufte_icon('lock', 'mr-3 text-red-600'); ?>
                        <?php esc_html_e('Change Password', 'gufte'); ?>
                    </h2>
                    
                    <p class="text-sm text-gray-600 mb-6">
                        <?php esc_html_e('Leave these fields blank if you do not want to change your password.', 'gufte'); ?>
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label for="pass1" class="block text-sm font-medium text-gray-700 mb-2">
                                <?php esc_html_e('New Password', 'gufte'); ?>
                            </label>
                            <input type="password" 
                                   id="pass1" 
                                   name="pass1" 
                                   autocomplete="new-password"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-300">
                            <div id="password-strength" class="mt-2 text-xs text-gray-600"></div>
                        </div>

                        <div class="form-group">
                            <label for="pass2" class="block text-sm font-medium text-gray-700 mb-2">
                                <?php esc_html_e('Confirm New Password', 'gufte'); ?>
                            </label>
                            <input type="password" 
                                   id="pass2" 
                                   name="pass2" 
                                   autocomplete="new-password"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-300">
                            <div id="password-match" class="mt-2 text-xs"></div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="form-actions bg-white rounded-lg shadow-md p-6 border border-gray-200">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-sm text-gray-600">
                            <?php gufte_icon('information-outline', 'mr-2'); ?>
                            <?php esc_html_e('Make sure to save your changes before leaving this page.', 'gufte'); ?>
                        </div>
                        <div class="flex gap-3">
                            <a href="<?php echo esc_url(home_url('/')); ?>" 
                               class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-300 shadow-md hover:shadow-lg">
                                <?php esc_html_e('Cancel', 'gufte'); ?>
                            </a>
                            <button type="submit" 
                                    name="submit" 
                                    class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors duration-300 shadow-md hover:shadow-lg flex items-center">
                                <?php gufte_icon('content-save', 'mr-2'); ?>
                                <?php esc_html_e('Save Changes', 'gufte'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!-- End Tab: Profile Information -->

        <!-- Tab Content: My Translations -->
        <div id="tab-my-translations" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 pb-4 border-b border-gray-200 flex items-center">
                    <?php gufte_icon('translate', 'mr-3 text-primary-600'); ?>
                    <?php esc_html_e('My Translation Contributions', 'gufte'); ?>
                </h2>

                <?php
                // Get user's translation submissions
                $user_submissions = get_posts(array(
                    'post_type' => 'translation_submission',
                    'author' => $user_id,
                    'posts_per_page' => -1,
                    'post_status' => array('publish', 'pending', 'draft'),
                ));

                // Count by status
                $status_counts = array(
                    'pending' => 0,
                    'approved' => 0,
                    'rejected' => 0,
                );

                foreach ($user_submissions as $submission) {
                    $status = get_post_status($submission->ID);
                    if ($status === 'publish') {
                        $status_counts['approved']++;
                    } elseif ($status === 'trash') {
                        $status_counts['rejected']++;
                    } else {
                        $status_counts['pending']++;
                    }
                }
                ?>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-blue-600 font-medium"><?php esc_html_e('Total Contributions', 'gufte'); ?></p>
                                <p class="text-3xl font-bold text-blue-800 mt-1"><?php echo count($user_submissions); ?></p>
                            </div>
                            <?php gufte_icon('translate', 'text-4xl text-blue-400'); ?>
                        </div>
                    </div>

                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-green-600 font-medium"><?php esc_html_e('Approved', 'gufte'); ?></p>
                                <p class="text-3xl font-bold text-green-800 mt-1"><?php echo $status_counts['approved']; ?></p>
                            </div>
                            <?php gufte_icon('check-circle', 'text-4xl text-green-400'); ?>
                        </div>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-yellow-600 font-medium"><?php esc_html_e('Pending Review', 'gufte'); ?></p>
                                <p class="text-3xl font-bold text-yellow-800 mt-1"><?php echo $status_counts['pending']; ?></p>
                            </div>
                            <?php gufte_icon('clock-outline', 'text-4xl text-yellow-400'); ?>
                        </div>
                    </div>
                </div>

                <!-- Submissions List -->
                <?php if (empty($user_submissions)) : ?>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                        <?php gufte_icon('translate-off', 'text-6xl text-gray-300 mb-4'); ?>
                        <p class="text-gray-600 mb-4"><?php esc_html_e('You haven\'t contributed any translations yet.', 'gufte'); ?></p>
                        <p class="text-sm text-gray-500 mb-6"><?php esc_html_e('Start translating songs to help the community!', 'gufte'); ?></p>
                        <a href="<?php echo esc_url(home_url('/songs')); ?>"
                           class="inline-flex items-center px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors duration-300">
                            <?php gufte_icon('music-note-plus', 'mr-2'); ?>
                            <?php esc_html_e('Browse Songs', 'gufte'); ?>
                        </a>
                    </div>
                <?php else : ?>
                    <div class="space-y-4">
                        <?php foreach ($user_submissions as $submission) :
                            $original_post_id = get_post_meta($submission->ID, '_original_post_id', true);
                            $target_language = get_post_meta($submission->ID, '_target_language', true);
                            $submission_status = get_post_status($submission->ID);
                            $original_post = get_post($original_post_id);

                            // Status badge styling
                            $status_class = '';
                            $status_icon = '';
                            $status_text = '';

                            if ($submission_status === 'publish') {
                                $status_class = 'bg-green-100 text-green-700 border-green-200';
                                $status_icon = 'check-circle';
                                $status_text = __('Approved & Published', 'gufte');
                            } elseif ($submission_status === 'trash') {
                                $status_class = 'bg-red-100 text-red-700 border-red-200';
                                $status_icon = 'close-circle';
                                $status_text = __('Rejected', 'gufte');
                            } else {
                                $status_class = 'bg-yellow-100 text-yellow-700 border-yellow-200';
                                $status_icon = 'clock-outline';
                                $status_text = __('Pending Review', 'gufte');
                            }

                            // Language names
                            $language_names = array(
                                'english' => 'English 🇬🇧',
                                'spanish' => 'Español 🇪🇸',
                                'turkish' => 'Türkçe 🇹🇷',
                                'german' => 'Deutsch 🇩🇪',
                                'french' => 'Français 🇫🇷',
                                'italian' => 'Italiano 🇮🇹',
                                'portuguese' => 'Português 🇵🇹',
                                'russian' => 'Русский 🇷🇺',
                                'arabic' => 'العربية 🇸🇦',
                                'japanese' => '日本語 🇯🇵',
                            );
                            $language_display = isset($language_names[$target_language]) ? $language_names[$target_language] : ucfirst($target_language);
                        ?>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-5 hover:border-primary-300 transition-colors">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-2">
                                        <?php if ($original_post) : ?>
                                            <a href="<?php echo esc_url(get_permalink($original_post_id)); ?>"
                                               class="hover:text-primary-600 transition-colors">
                                                <?php echo esc_html(get_the_title($original_post_id)); ?>
                                            </a>
                                        <?php else : ?>
                                            <?php esc_html_e('Song Not Found', 'gufte'); ?>
                                        <?php endif; ?>
                                    </h3>
                                    <div class="flex flex-wrap gap-3 text-sm text-gray-600">
                                        <span class="flex items-center">
                                            <?php gufte_icon('translate', 'mr-1'); ?>
                                            <?php echo esc_html($language_display); ?>
                                        </span>
                                        <span class="flex items-center">
                                            <?php gufte_icon('calendar', 'mr-1'); ?>
                                            <?php echo get_the_date('', $submission->ID); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center px-3 py-1.5 border rounded-full text-xs font-medium <?php echo $status_class; ?>">
                                        <?php gufte_icon($status_icon, 'mr-1.5'); ?>
                                        <?php echo esc_html($status_text); ?>
                                    </span>
                                </div>
                            </div>

                            <?php if ($submission_status === 'trash') :
                                $rejection_reason = get_post_meta($submission->ID, '_rejection_reason', true);
                                if (!empty($rejection_reason)) :
                            ?>
                                <div class="mt-4 pt-4 border-t border-gray-300">
                                    <p class="text-sm text-gray-700">
                                        <span class="font-medium"><?php esc_html_e('Rejection Reason:', 'gufte'); ?></span>
                                        <?php echo esc_html($rejection_reason); ?>
                                    </p>
                                </div>
                            <?php
                                endif;
                            endif;
                            ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- End Tab: My Translations -->

        <!-- Tab Content: Saved Lines -->
        <div id="tab-saved-lines" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 pb-4 border-b border-gray-200 flex items-center">
                    <?php gufte_icon('bookmark', 'mr-3 text-purple-600'); ?>
                    <?php esc_html_e('Saved Lyric Lines', 'gufte'); ?>
                </h2>

                <?php
                // Get user's saved lines
                $saved_lines = get_user_meta($current_user->ID, 'arcuras_saved_lyric_lines', true);
                if (!is_array($saved_lines)) {
                    $saved_lines = array();
                }

                // Clean up invalid featured_image URLs
                $needs_update = false;
                foreach ($saved_lines as $key => &$line) {
                    if (isset($line['featured_image'])) {
                        // Check if it's a valid URL
                        if (empty($line['featured_image']) || !filter_var($line['featured_image'], FILTER_VALIDATE_URL)) {
                            // Try to get the correct featured image from the post
                            if (isset($line['post_id'])) {
                                $correct_image = get_the_post_thumbnail_url($line['post_id'], 'medium');
                                $line['featured_image'] = $correct_image ? $correct_image : '';
                                $needs_update = true;
                            } else {
                                $line['featured_image'] = '';
                                $needs_update = true;
                            }
                        }
                    }
                }
                unset($line); // Break reference

                // Update user meta if we fixed any URLs
                if ($needs_update) {
                    update_user_meta($current_user->ID, 'arcuras_saved_lyric_lines', $saved_lines);
                }

                // Sort by saved_at date (newest first)
                usort($saved_lines, function($a, $b) {
                    $time_a = isset($a['saved_at']) ? strtotime($a['saved_at']) : 0;
                    $time_b = isset($b['saved_at']) ? strtotime($b['saved_at']) : 0;
                    return $time_b - $time_a;
                });
                ?>

                <?php if (empty($saved_lines)) : ?>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                        <?php gufte_icon('bookmark-outline', 'text-6xl text-gray-300 mb-4'); ?>
                        <p class="text-gray-600 mb-4"><?php esc_html_e('You haven\'t saved any lyric lines yet.', 'gufte'); ?></p>
                        <p class="text-sm text-gray-500 mb-6"><?php esc_html_e('Start saving your favorite lines from songs!', 'gufte'); ?></p>
                        <a href="<?php echo esc_url(home_url('/lyrics')); ?>"
                           class="inline-flex items-center px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors duration-300">
                            <?php gufte_icon('music-note', 'mr-2'); ?>
                            <?php esc_html_e('Browse Lyrics', 'gufte'); ?>
                        </a>
                    </div>
                <?php else : ?>
                    <!-- Statistics -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-purple-600 font-medium"><?php esc_html_e('Total Saved', 'gufte'); ?></p>
                                    <p class="text-3xl font-bold text-purple-800 mt-1"><?php echo count($saved_lines); ?></p>
                                </div>
                                <?php gufte_icon('bookmark-multiple', 'text-4xl text-purple-400'); ?>
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-blue-600 font-medium"><?php esc_html_e('Unique Songs', 'gufte'); ?></p>
                                    <p class="text-3xl font-bold text-blue-800 mt-1">
                                        <?php
                                        $unique_posts = array_unique(array_column($saved_lines, 'post_id'));
                                        echo count($unique_posts);
                                        ?>
                                    </p>
                                </div>
                                <?php gufte_icon('music-note-multiple', 'text-4xl text-blue-400'); ?>
                            </div>
                        </div>

                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-green-600 font-medium"><?php esc_html_e('Languages', 'gufte'); ?></p>
                                    <p class="text-3xl font-bold text-green-800 mt-1">
                                        <?php
                                        $unique_langs = array_unique(array_column($saved_lines, 'language_code'));
                                        echo count($unique_langs);
                                        ?>
                                    </p>
                                </div>
                                <?php gufte_icon('translate', 'text-4xl text-green-400'); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Saved Lines List -->
                    <div class="space-y-4" id="saved-lines-list">
                        <?php foreach ($saved_lines as $line_key => $line) :
                            $post = get_post($line['post_id']);
                            if (!$post) continue;
                        ?>
                        <div class="saved-line-card bg-gradient-to-br from-white to-gray-50 border-2 border-gray-200 rounded-lg p-5 hover:border-purple-300 hover:shadow-lg transition-all duration-300"
                             data-line-key="<?php echo esc_attr($line_key); ?>">
                            <div class="flex items-start gap-4">
                                <!-- Thumbnail -->
                                <?php
                                $featured_image = isset($line['featured_image']) ? $line['featured_image'] : '';
                                // Only show if it's a valid URL
                                if (!empty($featured_image) && filter_var($featured_image, FILTER_VALIDATE_URL)) :
                                ?>
                                <div class="flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden shadow-md">
                                    <img src="<?php echo esc_url($featured_image); ?>"
                                         alt="<?php echo esc_attr($line['post_title']); ?>"
                                         class="w-full h-full object-cover"
                                         onerror="this.parentElement.style.display='none'">
                                </div>
                                <?php endif; ?>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <!-- Song Title -->
                                    <div class="flex items-start justify-between mb-3">
                                        <h3 class="text-base font-semibold text-gray-800">
                                            <a href="<?php echo esc_url(get_permalink($line['post_id'])); ?>"
                                               class="hover:text-purple-600 transition-colors flex items-center">
                                                <?php gufte_icon('music-note', 'mr-1.5 text-purple-500'); ?>
                                                <?php echo esc_html($line['post_title']); ?>
                                            </a>
                                        </h3>
                                        <button type="button"
                                                class="delete-saved-line flex-shrink-0 ml-2 p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                                                data-line-key="<?php echo esc_attr($line_key); ?>"
                                                title="<?php esc_attr_e('Delete', 'gufte'); ?>">
                                            <?php gufte_icon('delete', 'text-xl'); ?>
                                        </button>
                                    </div>

                                    <!-- Original Line -->
                                    <div class="mb-2">
                                        <p class="text-lg font-medium text-gray-900 leading-relaxed">
                                            "<?php echo esc_html($line['original_text']); ?>"
                                        </p>
                                    </div>

                                    <!-- Translation Line -->
                                    <?php if (!empty($line['translation_text'])) : ?>
                                    <div class="mb-3 pl-4 border-l-3 border-purple-400">
                                        <p class="text-base text-gray-700 italic leading-relaxed">
                                            <?php echo esc_html($line['translation_text']); ?>
                                        </p>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Meta Info -->
                                    <div class="flex flex-wrap gap-3 text-xs text-gray-600">
                                        <span class="inline-flex items-center px-2 py-1 bg-purple-100 text-purple-700 rounded-full font-medium">
                                            <?php gufte_icon('translate', 'mr-1'); ?>
                                            <?php echo esc_html($line['language_name']); ?>
                                        </span>
                                        <span class="inline-flex items-center">
                                            <?php gufte_icon('calendar-clock', 'mr-1'); ?>
                                            <?php echo human_time_diff(strtotime($line['saved_at']), current_time('timestamp')) . ' ' . __('ago', 'gufte'); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- End Tab: Saved Lines -->

        </div><!-- .px-4 -->

    </main>
</div><!-- .site-content-wrapper -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pass1 = document.getElementById('pass1');
    const pass2 = document.getElementById('pass2');
    const strengthDiv = document.getElementById('password-strength');
    const matchDiv = document.getElementById('password-match');
    const nickname = document.getElementById('nickname');
    const displayName = document.getElementById('display_name');

    // Password strength checker
    function checkPasswordStrength(password) {
        let strength = 0;
        let feedback = '';

        if (password.length >= 6) strength += 1;
        if (password.length >= 10) strength += 1;
        if (/[a-z]/.test(password)) strength += 1;
        if (/[A-Z]/.test(password)) strength += 1;
        if (/[0-9]/.test(password)) strength += 1;
        if (/[^A-Za-z0-9]/.test(password)) strength += 1;

        switch (strength) {
            case 0:
            case 1:
            case 2:
                feedback = '<span class="text-red-600">Weak password</span>';
                break;
            case 3:
            case 4:
                feedback = '<span class="text-yellow-600">Medium strength</span>';
                break;
            case 5:
            case 6:
                feedback = '<span class="text-green-600">Strong password</span>';
                break;
        }

        return feedback;
    }

    // Password match checker
    function checkPasswordMatch() {
        if (pass1.value && pass2.value) {
            if (pass1.value === pass2.value) {
                matchDiv.innerHTML = '<span class="text-green-600">Passwords match</span>';
                pass2.setCustomValidity('');
            } else {
                matchDiv.innerHTML = '<span class="text-red-600">Passwords do not match</span>';
                pass2.setCustomValidity('Passwords do not match');
            }
        } else {
            matchDiv.innerHTML = '';
            pass2.setCustomValidity('');
        }
    }

    // Update display name options when nickname changes
    function updateDisplayNameOptions() {
        const firstName = document.getElementById('first_name').value;
        const lastName = document.getElementById('last_name').value;
        const nicknameValue = nickname.value;
        const currentDisplay = displayName.value;

        // Clear existing options
        displayName.innerHTML = '';

        // Add nickname option
        if (nicknameValue) {
            const option = new Option(nicknameValue, nicknameValue, false, currentDisplay === nicknameValue);
            displayName.add(option);
        }

        // Add first name option
        if (firstName) {
            const option = new Option(firstName, firstName, false, currentDisplay === firstName);
            displayName.add(option);
        }

        // Add last name option
        if (lastName) {
            const option = new Option(lastName, lastName, false, currentDisplay === lastName);
            displayName.add(option);
        }

        // Add full name combinations
        if (firstName && lastName) {
            const fullName1 = firstName + ' ' + lastName;
            const fullName2 = lastName + ' ' + firstName;
            
            const option1 = new Option(fullName1, fullName1, false, currentDisplay === fullName1);
            const option2 = new Option(fullName2, fullName2, false, currentDisplay === fullName2);
            
            displayName.add(option1);
            displayName.add(option2);
        }
    }

    // Event listeners
    if (pass1) {
        pass1.addEventListener('input', function() {
            if (this.value) {
                strengthDiv.innerHTML = checkPasswordStrength(this.value);
            } else {
                strengthDiv.innerHTML = '';
            }
            checkPasswordMatch();
        });
    }

    if (pass2) {
        pass2.addEventListener('input', checkPasswordMatch);
    }

    if (nickname) {
        nickname.addEventListener('input', updateDisplayNameOptions);
        document.getElementById('first_name').addEventListener('input', updateDisplayNameOptions);
        document.getElementById('last_name').addEventListener('input', updateDisplayNameOptions);
    }

    // Form submission confirmation
    const form = document.getElementById('profile-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const hasPasswordChange = pass1.value || pass2.value;
            if (hasPasswordChange) {
                if (!confirm('<?php echo esc_js(__("You are changing your password. Continue?", "gufte")); ?>')) {
                    e.preventDefault();
                }
            }
        });
    }

    // Tab switching functionality
    const tabButtons = document.querySelectorAll('.profile-tab');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');

            // Remove active class from all buttons
            tabButtons.forEach(btn => {
                btn.classList.remove('border-primary-600', 'text-primary-600');
                btn.classList.add('border-transparent', 'text-gray-600', 'hover:text-gray-800', 'hover:border-gray-300');
            });

            // Add active class to clicked button
            this.classList.remove('border-transparent', 'text-gray-600', 'hover:text-gray-800', 'hover:border-gray-300');
            this.classList.add('border-primary-600', 'text-primary-600');

            // Hide all tab contents
            tabContents.forEach(content => {
                content.classList.add('hidden');
            });

            // Show target tab content
            const targetContent = document.getElementById(targetTab);
            if (targetContent) {
                targetContent.classList.remove('hidden');
            }
        });
    });

    // Delete saved line functionality
    const deleteButtons = document.querySelectorAll('.delete-saved-line');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const lineKey = this.getAttribute('data-line-key');
            const card = this.closest('.saved-line-card');

            if (!confirm('<?php echo esc_js(__("Are you sure you want to delete this saved line?", "gufte")); ?>')) {
                return;
            }

            // Disable button during request
            this.disabled = true;
            this.style.opacity = '0.5';

            // Send delete request
            fetch('/wp-json/arcuras/v1/delete-lyric-line', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                body: JSON.stringify({
                    line_key: lineKey
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Fade out and remove card
                    card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'translateX(-20px)';

                    setTimeout(() => {
                        card.remove();

                        // Update badge count
                        const badge = document.querySelector('[data-tab="tab-saved-lines"] .bg-purple-100');
                        if (badge && data.data.remaining > 0) {
                            badge.textContent = data.data.remaining;
                        } else if (badge && data.data.remaining === 0) {
                            badge.remove();
                        }

                        // Show empty state if no more lines
                        if (data.data.remaining === 0) {
                            location.reload();
                        }
                    }, 300);
                } else {
                    alert(data.message || '<?php echo esc_js(__("Failed to delete line. Please try again.", "gufte")); ?>');
                    this.disabled = false;
                    this.style.opacity = '1';
                }
            })
            .catch(error => {
                console.error('Delete error:', error);
                alert('<?php echo esc_js(__("An error occurred. Please try again.", "gufte")); ?>');
                this.disabled = false;
                this.style.opacity = '1';
            });
        });
    });
});
</script>

<?php get_footer(); ?>