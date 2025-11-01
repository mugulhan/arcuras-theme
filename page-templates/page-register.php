<?php
/**
 * Template Name: Register
 * Description: Custom registration page for new users
 *
 * @package Gufte
 * @since 2.4.9
 */

get_header();

// Check if user is already logged in
$is_logged_in = is_user_logged_in();
$current_user = wp_get_current_user();

// Check if registration is enabled
$registration_enabled = get_option('users_can_register');
?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-50 to-primary-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo and Title -->
        <div class="text-center">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="inline-block">
                <?php
                $custom_logo_id = get_theme_mod('custom_logo');
                if ($custom_logo_id) {
                    $logo_url = wp_get_attachment_image_src($custom_logo_id, 'full');
                    if ($logo_url) {
                        echo '<img src="' . esc_url($logo_url[0]) . '" alt="' . esc_attr(get_bloginfo('name')) . '" class="h-16 w-auto mx-auto">';
                    }
                } else {
                    echo '<h1 class="text-4xl font-bold text-primary-600">' . esc_html(get_bloginfo('name')) . '</h1>';
                }
                ?>
            </a>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                <?php echo $is_logged_in ? esc_html__('You are already registered', 'gufte') : esc_html__('Create your account', 'gufte'); ?>
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                <?php echo $is_logged_in ? '' : esc_html__('Join our lyrics community', 'gufte'); ?>
            </p>
        </div>

        <?php if ($is_logged_in) : ?>
        <!-- Already Logged In Message -->
        <div class="bg-white py-8 px-4 shadow-2xl rounded-2xl sm:px-10">
            <div class="text-center space-y-6">
                <!-- User Avatar -->
                <div class="flex justify-center">
                    <div class="w-24 h-24 rounded-full bg-primary-100 flex items-center justify-center">
                        <?php echo get_avatar($current_user->ID, 96, '', '', array('class' => 'rounded-full')); ?>
                    </div>
                </div>

                <!-- Welcome Message -->
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">
                        <?php printf(esc_html__('Welcome, %s!', 'gufte'), esc_html($current_user->display_name)); ?>
                    </h3>
                    <p class="text-sm text-gray-600">
                        <?php esc_html_e('You already have an account.', 'gufte'); ?>
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    <a href="<?php echo esc_url(home_url()); ?>"
                       class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                        <?php gufte_icon('home', 'inline-block w-4 h-4 mr-2'); ?>
                        <?php esc_html_e('Go to Homepage', 'gufte'); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php elseif (!$registration_enabled) : ?>
        <!-- Registration Disabled Message -->
        <div class="bg-white py-8 px-4 shadow-2xl rounded-2xl sm:px-10">
            <div class="text-center space-y-6">
                <div class="flex justify-center">
                    <div class="w-16 h-16 rounded-full bg-yellow-100 flex items-center justify-center">
                        <?php gufte_icon('alert-circle', 'w-8 h-8 text-yellow-600'); ?>
                    </div>
                </div>

                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">
                        <?php esc_html_e('Registration Disabled', 'gufte'); ?>
                    </h3>
                    <p class="text-sm text-gray-600">
                        <?php esc_html_e('New user registration is currently disabled. Please contact the site administrator.', 'gufte'); ?>
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    <a href="<?php echo esc_url(home_url('/sign-in/')); ?>"
                       class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                        <?php esc_html_e('Sign In Instead', 'gufte'); ?>
                    </a>

                    <a href="<?php echo esc_url(home_url()); ?>"
                       class="w-full flex justify-center items-center py-3 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                        <?php gufte_icon('home', 'inline-block w-4 h-4 mr-2'); ?>
                        <?php esc_html_e('Go to Homepage', 'gufte'); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php else : ?>
        <!-- Registration Form -->
        <div class="bg-white py-8 px-4 shadow-2xl rounded-2xl sm:px-10">
            <?php
            // Show error/success messages
            if (isset($_GET['registered']) && $_GET['registered'] == 'success') {
                echo '<div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">';
                echo '<strong>' . esc_html__('Success!', 'gufte') . '</strong> ';
                echo esc_html__('Your account has been created. Please check your email for the password.', 'gufte');
                echo '</div>';
            }

            if (isset($_GET['error'])) {
                $error_message = '';
                switch ($_GET['error']) {
                    case 'username_exists':
                        $error_message = esc_html__('This username is already taken.', 'gufte');
                        break;
                    case 'email_exists':
                        $error_message = esc_html__('This email address is already registered.', 'gufte');
                        break;
                    case 'invalid_email':
                        $error_message = esc_html__('Please enter a valid email address.', 'gufte');
                        break;
                    case 'invalid_username':
                        $error_message = esc_html__('Please enter a valid username.', 'gufte');
                        break;
                    case 'empty_fields':
                        $error_message = esc_html__('Please fill in all required fields.', 'gufte');
                        break;
                    default:
                        $error_message = esc_html__('An error occurred. Please try again.', 'gufte');
                }

                if ($error_message) {
                    echo '<div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">';
                    echo '<strong>' . esc_html__('Error:', 'gufte') . '</strong> ';
                    echo $error_message;
                    echo '</div>';
                }
            }

            // Check if Google OAuth is enabled
            $google_oauth_enabled = get_option('arcuras_google_oauth_enabled', '0');
            $google_client_id = get_option('arcuras_google_client_id', '');
            ?>

            <?php if ($google_oauth_enabled == '1' && !empty($google_client_id)) : ?>
            <!-- Google Sign-In Button -->
            <div class="mb-6">
                <a href="<?php echo esc_url(home_url('/oauth/google/login')); ?>"
                   class="w-full flex items-center justify-center gap-3 py-3 px-4 border border-gray-300 rounded-lg shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    <?php esc_html_e('Continue with Google', 'gufte'); ?>
                </a>
            </div>

            <!-- Divider -->
            <div class="relative mb-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500"><?php esc_html_e('Or register with email', 'gufte'); ?></span>
                </div>
            </div>
            <?php endif; ?>

            <form class="space-y-6" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="gufte_custom_register">
                <?php wp_nonce_field('gufte_register_action', 'gufte_register_nonce'); ?>

                <!-- Username Field -->
                <div>
                    <label for="user_login" class="block text-sm font-medium text-gray-700">
                        <?php esc_html_e('Username', 'gufte'); ?> <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1">
                        <input id="user_login" name="user_login" type="text" required
                               value="<?php echo isset($_GET['username']) ? esc_attr($_GET['username']) : ''; ?>"
                               class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                               placeholder="<?php esc_attr_e('Choose a username', 'gufte'); ?>">
                    </div>
                    <p class="mt-1 text-xs text-gray-500"><?php esc_html_e('Lowercase letters, numbers, and underscores only', 'gufte'); ?></p>
                </div>

                <!-- Email Field -->
                <div>
                    <label for="user_email" class="block text-sm font-medium text-gray-700">
                        <?php esc_html_e('Email Address', 'gufte'); ?> <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1">
                        <input id="user_email" name="user_email" type="email" autocomplete="email" required
                               value="<?php echo isset($_GET['email']) ? esc_attr($_GET['email']) : ''; ?>"
                               class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                               placeholder="<?php esc_attr_e('your@email.com', 'gufte'); ?>">
                    </div>
                </div>

                <!-- First Name Field -->
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700">
                        <?php esc_html_e('First Name', 'gufte'); ?>
                    </label>
                    <div class="mt-1">
                        <input id="first_name" name="first_name" type="text"
                               value="<?php echo isset($_GET['first_name']) ? esc_attr($_GET['first_name']) : ''; ?>"
                               class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                               placeholder="<?php esc_attr_e('Optional', 'gufte'); ?>">
                    </div>
                </div>

                <!-- Last Name Field -->
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700">
                        <?php esc_html_e('Last Name', 'gufte'); ?>
                    </label>
                    <div class="mt-1">
                        <input id="last_name" name="last_name" type="text"
                               value="<?php echo isset($_GET['last_name']) ? esc_attr($_GET['last_name']) : ''; ?>"
                               class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                               placeholder="<?php esc_attr_e('Optional', 'gufte'); ?>">
                    </div>
                </div>

                <!-- Info Notice -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <?php gufte_icon('information', 'w-5 h-5 text-blue-600'); ?>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <?php esc_html_e('Your password will be automatically generated and sent to your email address.', 'gufte'); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                        <?php esc_html_e('Create Account', 'gufte'); ?>
                    </button>
                </div>
            </form>

            <!-- Sign In Link -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    <?php esc_html_e('Already have an account?', 'gufte'); ?>
                    <a href="<?php echo esc_url(home_url('/sign-in/')); ?>" class="font-medium text-primary-600 hover:text-primary-500">
                        <?php esc_html_e('Sign in', 'gufte'); ?>
                    </a>
                </p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Back to Home -->
        <div class="text-center">
            <a href="<?php echo esc_url(home_url()); ?>" class="text-sm text-gray-600 hover:text-primary-600 transition-colors">
                <?php gufte_icon('arrow-left', 'inline-block w-4 h-4 mr-1'); ?>
                <?php esc_html_e('Back to homepage', 'gufte'); ?>
            </a>
        </div>
    </div>
</div>

<?php get_footer(); ?>
