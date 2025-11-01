<?php
/**
 * Template Name: Become a Contributor
 * Description: Contributor application page with login and form
 */

// Güvenlik kontrolü
if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Kullanıcı giriş yapmış mı kontrol et
$is_logged_in = is_user_logged_in();
$current_user = wp_get_current_user();
?>

<div class="contributor-page-wrapper min-h-screen bg-gray-50 py-12">
    <div class="container mx-auto px-4 max-w-4xl">
        
        <!-- Başlık Alanı -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Become a Contributor</h1>
            <p class="text-xl text-gray-600">Help us translate song lyrics into your native language</p>
        </div>

        <?php if (!$is_logged_in): ?>
        <!-- Login Prompt -->
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <div class="mb-8">
                <span class="iconify text-6xl text-blue-600" data-icon="mdi:account-circle"></span>
            </div>
            <h2 class="text-2xl font-semibold mb-4">Sign In to Continue</h2>
            <p class="text-gray-600 mb-6">Please sign in to apply as a contributor</p>
            
            <button id="open-login-modal" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                Sign In / Register
            </button>
            
            <p class="mt-4 text-sm text-gray-500">
                You can sign in with Google or create a new account
            </p>
        </div>
        
        <?php else: ?>
        <!-- Contributor Application Form -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-semibold mb-6">Contributor Application</h2>
            
            <form id="contributor-application-form" class="space-y-6">
                <?php wp_nonce_field('contributor_application', 'contributor_nonce'); ?>
                
                <!-- User Info (Pre-filled) -->
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                        <input type="text" name="full_name" required 
                               value="<?php echo esc_attr($current_user->display_name); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                        <input type="email" name="email" required readonly
                               value="<?php echo esc_attr($current_user->user_email); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50">
                    </div>
                </div>
                
                <!-- Country Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Country of Residence *</label>
                    <select name="country" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Select your country</option>
                    </select>
                </div>
                
                <!-- Native Language -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Native Language *</label>
                    <select name="native_language" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Select your native language</option>
                    </select>
                </div>
                
                <!-- Languages to Contribute -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Languages You Can Translate To *</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mt-3">
                        <?php
                        $languages = array(
                            'english' => 'English',
                            'spanish' => 'Spanish',
                            'turkish' => 'Turkish',
                            'german' => 'German',
                            'french' => 'French',
                            'italian' => 'Italian',
                            'portuguese' => 'Portuguese',
                            'russian' => 'Russian',
                            'arabic' => 'Arabic',
                            'japanese' => 'Japanese',
                            'korean' => 'Korean',
                            'chinese' => 'Chinese',
                            'hindi' => 'Hindi',
                            'persian' => 'Persian'
                        );
                        
                        foreach ($languages as $code => $name): ?>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="contribution_languages[]" value="<?php echo esc_attr($code); ?>"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm"><?php echo esc_html($name); ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Experience -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Translation Experience</label>
                    <textarea name="experience" rows="4" 
                              placeholder="Tell us about any translation experience you have (optional)"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                
                <!-- Why Join -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Why do you want to contribute? *</label>
                    <textarea name="motivation" rows="4" required
                              placeholder="Tell us why you'd like to help translate song lyrics"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                
                <!-- Agreement -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="flex items-start space-x-3 cursor-pointer">
                        <input type="checkbox" name="agreement" required
                               class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">
                            I agree to contribute accurate translations and understand that my contributions will be reviewed before publication. 
                            I confirm that I am fluent in the languages I selected above.
                        </span>
                    </label>
                </div>
                
                <!-- Submit Button -->
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="window.location.reload()" 
                            class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                        Submit Application
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>
        
    </div>
</div>

<!-- Login Modal -->
<div id="login-modal" class="fixed inset-0 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black opacity-50"></div>
        <div class="relative bg-white rounded-lg max-w-md w-full p-8">
            <button id="close-login-modal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <span class="iconify text-2xl" data-icon="mdi:close"></span>
            </button>
            
            <h3 class="text-2xl font-bold mb-6">Sign In</h3>
            
            <!-- WordPress Login Form -->
            <?php 
            $args = array(
                'echo' => true,
                'redirect' => get_permalink(),
                'form_id' => 'contributor-loginform',
                'label_username' => __('Username or Email'),
                'label_password' => __('Password'),
                'label_remember' => __('Remember Me'),
                'label_log_in' => __('Sign In'),
                'remember' => true
            );
            wp_login_form($args);
            ?>
            
            <!-- Nextend Social Login -->
            <?php if (function_exists('nsl_add_login_form_buttons')): ?>
                <div class="social-login-divider my-6 text-center text-gray-500">
                    <span class="bg-white px-3">OR</span>
                </div>
                <?php nsl_add_login_form_buttons(); ?>
            <?php endif; ?>
            
            <p class="mt-6 text-sm text-center">
                Don't have an account? 
                <a href="<?php echo wp_registration_url(); ?>" class="text-blue-600 hover:underline">Register here</a>
            </p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded, initializing country and language lists');
    
    const modal = document.getElementById('login-modal');
    const openBtn = document.getElementById('open-login-modal');
    const closeBtn = document.getElementById('close-login-modal');
    
    if (openBtn) {
        openBtn.addEventListener('click', () => {
            modal.classList.remove('hidden');
        });
    }
    
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            modal.classList.add('hidden');
        });
    }
    
    // Form submission
    const form = document.getElementById('contributor-application-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            formData.append('action', 'submit_contributor_application');
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Başvuru başarıyla gönderildi! İncelememizden sonra size geri döneceğiz.');
                    window.location.href = '<?php echo home_url(); ?>';
                } else {
                    alert('Hata: ' + data.data);
                }
            })
            .catch(error => {
                console.error('Form gönderim hatası:', error);
                alert('Form gönderilirken bir hata oluştu.');
            });
        });
    }
    
    // Load country and language lists
    loadCountries();
    loadLanguages();
});

// Ülkeler listesi
function loadCountries() {
    const select = document.querySelector('select[name="country"]');
    if (!select) {
        console.error('Ülke seçme elementi bulunamadı');
        return;
    }

    const countries = [
        { code: 'US', name: 'United States (United States)' },
        { code: 'TR', name: 'Turkey (Türkiye)' },
        { code: 'GB', name: 'United Kingdom (United Kingdom)' },
        { code: 'DE', name: 'Germany (Deutschland)' },
        { code: 'FR', name: 'France (France)' },
        { code: 'IT', name: 'Italy (Italia)' },
        { code: 'ES', name: 'Spain (España)' },
        { code: 'RU', name: 'Russia (Россия)' },
        { code: 'CN', name: 'China (中国)' },
        { code: 'JP', name: 'Japan (日本)' },
        // Daha fazla ülke eklenebilir
    ].sort((a, b) => a.name.localeCompare(b.name));

    countries.forEach(country => {
        const option = document.createElement('option');
        option.value = country.code;
        option.textContent = country.name;
        select.appendChild(option);
    });
}

// Diller listesi
function loadLanguages() {
    const select = document.querySelector('select[name="native_language"]');
    if (!select) {
        console.error('Dil seçme elementi bulunamadı');
        return;
    }

    const languages = [
        { code: 'en', name: 'English (English)' },
        { code: 'es', name: 'Spanish (Español)' },
        { code: 'tr', name: 'Turkish (Türkçe)' },
        { code: 'de', name: 'German (Deutsch)' },
        { code: 'fr', name: 'French (Français)' },
        { code: 'it', name: 'Italian (Italiano)' },
        { code: 'pt', name: 'Portuguese (Português)' },
        { code: 'ru', name: 'Russian (Русский)' },
        { code: 'ar', name: 'Arabic (العربية)' },
        { code: 'ja', name: 'Japanese (日本語)' },
        { code: 'ko', name: 'Korean (한국어)' },
        { code: 'zh', name: 'Chinese (中文)' },
        { code: 'hi', name: 'Hindi (हिन्दी)' },
        { code: 'fa', name: 'Persian (فارسی)' }
    ];

    languages.forEach(lang => {
        const option = document.createElement('option');
        option.value = lang.code;
        option.textContent = lang.name;
        select.appendChild(option);
    });
}
</script>

<?php get_footer(); ?>