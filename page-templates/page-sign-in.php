<?php
/**
 * Template Name: Sign In
 * Description: Custom sign in page for users
 *
 * @package Gufte
 * @since 2.4.9
 */

get_header();

// Check if user is already logged in
$is_logged_in = is_user_logged_in();
$current_user = wp_get_current_user();
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
                <?php echo $is_logged_in ? esc_html__('You are already signed in', 'gufte') : esc_html__('Sign in to your account', 'gufte'); ?>
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                <?php echo $is_logged_in ? '' : esc_html__('Access your lyrics collection', 'gufte'); ?>
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
                        <?php printf(esc_html__('Welcome back, %s!', 'gufte'), esc_html($current_user->display_name)); ?>
                    </h3>
                    <p class="text-sm text-gray-600">
                        <?php esc_html_e('You are currently signed in.', 'gufte'); ?>
                    </p>
                </div>

                <!-- User Info -->
                <div class="bg-gray-50 rounded-lg p-4 text-left">
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600"><?php esc_html_e('Username:', 'gufte'); ?></span>
                            <span class="font-medium text-gray-900"><?php echo esc_html($current_user->user_login); ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600"><?php esc_html_e('Email:', 'gufte'); ?></span>
                            <span class="font-medium text-gray-900"><?php echo esc_html($current_user->user_email); ?></span>
                        </div>
                        <?php if (current_user_can('edit_posts')) : ?>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600"><?php esc_html_e('Role:', 'gufte'); ?></span>
                            <span class="font-medium text-primary-600">
                                <?php
                                $role_names = array(
                                    'administrator' => __('Administrator', 'gufte'),
                                    'editor' => __('Editor', 'gufte'),
                                    'author' => __('Author', 'gufte'),
                                    'contributor' => __('Contributor', 'gufte')
                                );
                                $user_roles = $current_user->roles;
                                $role = reset($user_roles);
                                echo isset($role_names[$role]) ? esc_html($role_names[$role]) : esc_html(ucfirst($role));
                                ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    <?php if (current_user_can('edit_posts')) : ?>
                    <a href="<?php echo esc_url(admin_url()); ?>"
                       class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                        <?php gufte_icon('dashboard', 'inline-block w-4 h-4 mr-2'); ?>
                        <?php esc_html_e('Go to Dashboard', 'gufte'); ?>
                    </a>
                    <?php endif; ?>

                    <a href="<?php echo esc_url(home_url()); ?>"
                       class="w-full flex justify-center items-center py-3 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                        <?php gufte_icon('home', 'inline-block w-4 h-4 mr-2'); ?>
                        <?php esc_html_e('Go to Homepage', 'gufte'); ?>
                    </a>

                    <a href="<?php echo esc_url(wp_logout_url(home_url('/sign-in/'))); ?>"
                       class="w-full flex justify-center items-center py-3 px-4 border border-red-300 rounded-lg shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                        <?php gufte_icon('logout', 'inline-block w-4 h-4 mr-2'); ?>
                        <?php esc_html_e('Sign Out', 'gufte'); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php else : ?>
        <!-- Login Form -->
        <div class="bg-white py-8 px-4 shadow-2xl rounded-2xl sm:px-10">
            <?php
            // Show error/success messages (only when URL parameters exist)
            if (isset($_GET['login'])) {
                if ($_GET['login'] == 'failed') {
                    echo '<div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">';
                    echo '<strong>' . esc_html__('Error:', 'gufte') . '</strong> ';
                    echo esc_html__('Invalid username or password.', 'gufte');
                    echo '</div>';
                } elseif ($_GET['login'] == 'empty') {
                    echo '<div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">';
                    echo '<strong>' . esc_html__('Error:', 'gufte') . '</strong> ';
                    echo esc_html__('Username and password are required.', 'gufte');
                    echo '</div>';
                }
            }

            if (isset($_GET['registered']) && $_GET['registered'] == 'true') {
                echo '<div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">';
                echo esc_html__('Registration successful! Please sign in.', 'gufte');
                echo '</div>';
            }

            if (isset($_GET['loggedout']) && $_GET['loggedout'] == 'true') {
                echo '<div class="mb-4 p-4 bg-blue-50 border border-blue-200 text-blue-700 rounded-lg text-sm">';
                echo esc_html__('You have been logged out successfully.', 'gufte');
                echo '</div>';
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
                    <span class="px-2 bg-white text-gray-500"><?php esc_html_e('Or continue with email', 'gufte'); ?></span>
                </div>
            </div>
            <?php endif; ?>

            <form class="space-y-6" action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>" method="post">
                <!-- Username Field -->
                <div>
                    <label for="user_login" class="block text-sm font-medium text-gray-700">
                        <?php esc_html_e('Username or Email', 'gufte'); ?>
                    </label>
                    <div class="mt-1">
                        <input id="user_login" name="log" type="text" autocomplete="username" required
                               class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                               placeholder="<?php esc_attr_e('Enter your username or email', 'gufte'); ?>">
                    </div>
                </div>

                <!-- Password Field -->
                <div>
                    <label for="user_pass" class="block text-sm font-medium text-gray-700">
                        <?php esc_html_e('Password', 'gufte'); ?>
                    </label>
                    <div class="mt-1">
                        <input id="user_pass" name="pwd" type="password" autocomplete="current-password" required
                               class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                               placeholder="<?php esc_attr_e('Enter your password', 'gufte'); ?>">
                    </div>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="rememberme" name="rememberme" type="checkbox" value="forever"
                               class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                        <label for="rememberme" class="ml-2 block text-sm text-gray-700">
                            <?php esc_html_e('Remember me', 'gufte'); ?>
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="font-medium text-primary-600 hover:text-primary-500">
                            <?php esc_html_e('Forgot password?', 'gufte'); ?>
                        </a>
                    </div>
                </div>

                <!-- Hidden Fields -->
                <input type="hidden" name="redirect_to" value="<?php echo esc_url(home_url()); ?>">
                <input type="hidden" name="testcookie" value="1">

                <!-- Submit Button -->
                <div>
                    <button type="submit" name="wp-submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                        <?php esc_html_e('Sign in', 'gufte'); ?>
                    </button>
                </div>
            </form>

            <!-- Register Link -->
            <?php if (get_option('users_can_register')) : ?>
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    <?php esc_html_e("Don't have an account?", 'gufte'); ?>
                    <a href="<?php echo esc_url(home_url('/register/')); ?>" class="font-medium text-primary-600 hover:text-primary-500">
                        <?php esc_html_e('Sign up', 'gufte'); ?>
                    </a>
                </p>
            </div>
            <?php endif; ?>
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
