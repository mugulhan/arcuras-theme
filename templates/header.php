<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Gufte
 */

// Son aramaları kaydetme ve getirme işlevleri
function gufte_save_recent_search($search_query) {
    if (empty($search_query)) return;
    
    $recent_searches = get_transient('gufte_user_recent_searches_' . get_current_user_id());
    if (!is_array($recent_searches)) {
        $recent_searches = array();
    }
    
    // Eğer bu arama zaten varsa, listeden çıkar (daha sonra en başa eklemek için)
    if (($key = array_search($search_query, $recent_searches)) !== false) {
        unset($recent_searches[$key]);
    }
    
    // Aramayı listenin başına ekle
    array_unshift($recent_searches, $search_query);
    
    // Listeyi 5 öğeyle sınırla
    $recent_searches = array_slice($recent_searches, 0, 5);
    
    // Transient olarak kaydet (1 hafta süreyle)
    set_transient('gufte_user_recent_searches_' . get_current_user_id(), $recent_searches, WEEK_IN_SECONDS);
}

function gufte_get_recent_searches() {
    if (!is_user_logged_in()) return array();
    
    $recent_searches = get_transient('gufte_user_recent_searches_' . get_current_user_id());
    return is_array($recent_searches) ? $recent_searches : array();
}

// Son ziyaret edilen içerikleri kaydetme ve getirme işlevleri
function gufte_save_recent_visit($post_id) {
    if (!is_user_logged_in() || !is_single()) return;
    
    $recent_visits = get_transient('gufte_user_recent_visits_' . get_current_user_id());
    if (!is_array($recent_visits)) {
        $recent_visits = array();
    }
    
    // Eğer bu içerik zaten varsa, listeden çıkar (daha sonra en başa eklemek için)
    if (($key = array_search($post_id, $recent_visits)) !== false) {
        unset($recent_visits[$key]);
    }
    
    // İçeriği listenin başına ekle
    array_unshift($recent_visits, $post_id);
    
    // Listeyi 5 öğeyle sınırla
    $recent_visits = array_slice($recent_visits, 0, 5);
    
    // Transient olarak kaydet (1 hafta süreyle)
    set_transient('gufte_user_recent_visits_' . get_current_user_id(), $recent_visits, WEEK_IN_SECONDS);
}

function gufte_get_recent_visits() {
    if (!is_user_logged_in()) return array();
    
    $recent_visits = get_transient('gufte_user_recent_visits_' . get_current_user_id());
    return is_array($recent_visits) ? $recent_visits : array();
}

// Eğer arama sayfasındaysak, arama sorgusunu kaydet
if (is_search() && !empty(get_search_query())) {
    gufte_save_recent_search(get_search_query());
}

// Eğer tekil içerik sayfasındaysak, ziyareti kaydet
if (is_single()) {
    gufte_save_recent_visit(get_the_ID());
}

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <meta name="msvalidate.01" content="B68AA5206FAD98D514A12D6FB281914C" />
    
    <!-- Tailwind CSS - Pre-built and optimized -->
    <link rel="preload" href="<?php echo GUFTE_URI; ?>/dist/css/tailwind.min.css?ver=<?php echo GUFTE_VERSION; ?>" as="style">
    <link rel="stylesheet" href="<?php echo GUFTE_URI; ?>/dist/css/tailwind.min.css?ver=<?php echo GUFTE_VERSION; ?>">

    <?php
    // Preload LCP image (first hero slide) on homepage for better performance
    if (is_front_page()) :
        $featured_query = new WP_Query(array(
            'post_type' => 'post',
            'posts_per_page' => 1,
            'meta_key' => '_is_featured',
            'meta_value' => '1',
            'orderby' => 'date',
            'order' => 'DESC',
        ));
        if ($featured_query->have_posts()) :
            $featured_query->the_post();
            if (has_post_thumbnail()) :
                $thumbnail_id = get_post_thumbnail_id();
                $thumbnail_url = wp_get_attachment_image_url($thumbnail_id, 'large');
                if ($thumbnail_url) :
    ?>
    <link rel="preload" as="image" href="<?php echo esc_url($thumbnail_url); ?>" fetchpriority="high">
    <?php
                endif;
            endif;
            wp_reset_postdata();
        endif;
    endif;
    ?>

    <!-- Swiper - Minimal inline CSS for critical rendering -->
    <?php if (is_front_page() || is_singular('post')) : ?>
    <style>
    /* Minimal Swiper CSS - only critical styles inline */
    .swiper-container{margin-left:auto;margin-right:auto;position:relative;overflow:hidden;list-style:none;padding:0;z-index:1}
    .swiper-wrapper{position:relative;width:100%;height:100%;z-index:1;display:flex;transition-property:transform;box-sizing:content-box}
    .swiper-slide{flex-shrink:0;width:100%;height:100%;position:relative;transition-property:transform}
    .swiper-pagination{position:absolute;text-align:center;transition:.3s opacity;transform:translate3d(0,0,0);z-index:10}
    .swiper-pagination-bullet{width:8px;height:8px;display:inline-block;border-radius:50%;opacity:.2;cursor:pointer}
    .swiper-pagination-bullet-active{opacity:1}
    .swiper-button-next,.swiper-button-prev{position:absolute;top:50%;width:27px;height:44px;margin-top:-22px;z-index:10;cursor:pointer}
    </style>

    <!-- Swiper JS - Deferred with minimal CSS -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'" media="print">
    <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css"></noscript>

    <script>
    // Swiper loader - load on front page and single posts
    (function() {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js';
        script.async = true;

        // Trigger custom event when Swiper is loaded
        script.onload = function() {
            window.dispatchEvent(new CustomEvent('swiperLoaded'));
        };

        document.head.appendChild(script);
    })();
    </script>
    <?php endif; ?>
    


    <!-- Tailwind config now in build/tailwind.config.js -->

    <!-- Özel CSS - Yatay kaydırmayı önlemek için -->
    <style>
      html, body {
        max-width: 100%;
        overflow-x: hidden;
      }
      
      /* Swiper düzeltmeleri */
      .swiper-container {
        width: 100%;
        overflow: hidden;
      }
      
      .swiper-slide {
        width: 100% !important;
      }
      
      /* Logo boyutunu düzenle */
      .custom-logo-link img {
        max-height: 40px;
        width: auto;
        transition: transform 0.3s ease;
        border-radius: 6px;
      }

      /* Logo hover animasyonu */
      .custom-logo-link:hover img {
        transform: scale(1.05);
      }

      /* Search modal overlay pointer management */
      body.search-modal-open #page,
      body.search-modal-open #page * {
        pointer-events: none !important;
        user-select: none;
      }
      
      body.search-modal-open .search-modal,
      body.search-modal-open .search-modal * {
        pointer-events: auto !important;
        user-select: auto;
      }

      
      body.modal-active .site-header {
        z-index: 30;
      }

      body.modal-active .search-modal {
        z-index: 10000;
      }
      body.search-modal-open .hero-posts,
      body.search-modal-open .hero-slider-container,
      body.search-modal-open .swiper-container {
        opacity: 0 !important;
        transition: opacity 0.2s ease;
      }

      body:not(.search-modal-open) .hero-posts,
      body:not(.search-modal-open) .hero-slider-container,
      body:not(.search-modal-open) .swiper-container {
        opacity: 1;
      }

      
      /* Sticky header */
      .site-header {
        position: sticky;
        position: -webkit-sticky;
        top: 0;
        z-index: 50;
        backdrop-filter: saturate(180%) blur(12px);
      }

      /* Mobile Menu Modal - Modal popup style */
      #mobile-menu:not(.hidden) {
        display: flex !important;
      }

      /* Performans için GPU acceleration */
      #mobile-menu,
      #mobile-menu-overlay {
        -webkit-transform: translateZ(0);
        transform: translateZ(0);
      }

      /* Menü açıkken body scroll'u engelle - Layout bozulmasını önle */
      body.overflow-hidden {
        overflow: hidden !important;
        position: fixed !important;
        width: 100% !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
      }

      /* Touch action'ı engelle - iOS için */
      body.overflow-hidden {
        touch-action: none;
        -webkit-overflow-scrolling: auto;
      }

    </style>

    <!-- Critical CSS - Inline for faster initial render -->
    <style id="critical-css">
      /* Reset & Base */
      *,::before,::after{box-sizing:border-box;border-width:0;border-style:solid;border-color:#e5e7eb}
      body{margin:0;font-family:inherit;line-height:inherit}
      img,svg{display:block;vertical-align:middle}
      img{max-width:100%;height:auto}

      /* Tailwind Base Classes - Critical Only */
      .flex{display:flex}
      .grid{display:grid}
      .hidden{display:none}
      .block{display:block}
      .inline-block{display:inline-block}
      .relative{position:relative}
      .absolute{position:absolute}
      .fixed{position:fixed}
      .w-full{width:100%}
      .h-full{height:100%}
      .max-w-7xl{max-width:80rem}
      .mx-auto{margin-left:auto;margin-right:auto}
      .px-4{padding-left:1rem;padding-right:1rem}
      .py-8{padding-top:2rem;padding-bottom:2rem}
      .shadow-md{box-shadow:0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)}
      .rounded-lg{border-radius:0.5rem}
      .items-center{align-items:center}
      .justify-between{justify-content:space-between}
      .gap-4{gap:1rem}
      .overflow-hidden{overflow:hidden}
      .antialiased{-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}

      /* Color Classes - Critical for icons and text */
      .text-gray-300{color:rgb(209 213 219)}
      .text-gray-400{color:rgb(156 163 175)}
      .text-gray-500{color:rgb(107 114 128)}
      .text-gray-600{color:rgb(75 85 99)}
      .text-gray-700{color:rgb(55 65 81)}
      .text-gray-800{color:rgb(31 41 55)}
      .text-gray-900{color:rgb(17 24 39)}
      .text-blue-500{color:rgb(59 130 246)}
      .text-blue-600{color:rgb(37 99 235)}
      .text-blue-700{color:rgb(29 78 216)}
      .text-primary-500{color:rgb(37 99 235)}
      .text-primary-600{color:rgb(29 78 216)}
      .text-primary-700{color:rgb(30 64 175)}
      .text-accent-500{color:rgb(59 130 246)}
      .text-accent-600{color:rgb(37 99 235)}
      .text-accent-700{color:rgb(29 78 216)}
      .text-white{color:rgb(255 255 255)}
      .text-amber-600{color:rgb(217 119 6)}
      .text-amber-700{color:rgb(180 83 9)}
      .text-green-600{color:rgb(22 163 74)}

      /* Background Colors */
      .bg-white{background-color:rgb(255 255 255)}
      .bg-gray-50{background-color:rgb(249 250 251)}
      .bg-gray-100{background-color:rgb(243 244 246)}
      .bg-gray-200{background-color:rgb(229 231 235)}
      .bg-blue-50{background-color:rgb(239 246 255)}
      .bg-primary-50{background-color:rgb(239 246 255)}
      .bg-primary-100{background-color:rgb(219 234 254)}
      .bg-primary-500{background-color:rgb(37 99 235)}
      .bg-primary-600{background-color:rgb(29 78 216)}
      .bg-primary-700{background-color:rgb(30 64 175)}
      .bg-accent-50{background-color:rgb(239 246 255)}
      .bg-accent-100{background-color:rgb(219 234 254)}
      .bg-accent-600{background-color:rgb(37 99 235)}
      .bg-amber-600{background-color:rgb(217 119 6)}

      /* Border Colors */
      .border-gray-100{border-color:rgb(243 244 246)}
      .border-gray-200{border-color:rgb(229 231 235)}
      .border-gray-300{border-color:rgb(209 213 219)}
      .border-primary-200{border-color:rgb(191 219 254)}
      .border-primary-300{border-color:rgb(147 197 253)}
      .border-primary-400{border-color:rgb(96 165 250)}
      .border-primary-500{border-color:rgb(37 99 235)}
      .border-accent-300{border-color:rgb(147 197 253)}
      .border-accent-500{border-color:rgb(59 130 246)}
      .border-white{border-color:rgb(255 255 255)}
      .border-t{border-top-width:1px}
      .border-r{border-right-width:1px}
      .border-2{border-width:2px}
      .border-4{border-width:4px}
      .border-8{border-width:8px}

      /* Width/Height for icons */
      .w-4{width:1rem}
      .w-5{width:1.25rem}
      .w-6{width:1.5rem}
      .h-4{height:1rem}
      .h-5{height:1.25rem}
      .h-6{height:1.5rem}
      .text-lg{font-size:1.125rem;line-height:1.75rem}
      .text-xl{font-size:1.25rem;line-height:1.75rem}
      .text-2xl{font-size:1.5rem;line-height:2rem}
      .text-3xl{font-size:1.875rem;line-height:2.25rem}

      /* Spacing */
      .mr-1{margin-right:0.25rem}
      .mr-2{margin-right:0.5rem}
      .mr-3{margin-right:0.75rem}
      .ml-1{margin-left:0.25rem}
      .ml-2{margin-left:0.5rem}
      .mt-1{margin-top:0.25rem}
      .mb-1{margin-bottom:0.25rem}
      .mb-3{margin-bottom:0.75rem}
      .mb-4{margin-bottom:1rem}
      .p-4{padding:1rem}
      .px-2{padding-left:0.5rem;padding-right:0.5rem}
      .px-3{padding-left:0.75rem;padding-right:0.75rem}
      .py-1{padding-top:0.25rem;padding-bottom:0.25rem}
      .py-2{padding-top:0.5rem;padding-bottom:0.5rem}
      .py-1\.5{padding-top:0.375rem;padding-bottom:0.375rem}
      .px-2\.5{padding-left:0.625rem;padding-right:0.625rem}

      /* Border Radius */
      .rounded{border-radius:0.25rem}
      .rounded-md{border-radius:0.375rem}
      .rounded-full{border-radius:9999px}

      /* Font */
      .font-medium{font-weight:500}
      .font-semibold{font-weight:600}
      .font-bold{font-weight:700}
      .text-xs{font-size:0.75rem;line-height:1rem}
      .text-sm{font-size:0.875rem;line-height:1.25rem}
      .uppercase{text-transform:uppercase}
      .tracking-wider{letter-spacing:0.05em}

      /* Layout - Extended */
      .sticky{position:sticky}
      .top-0{top:0}
      .left-0{left:0}
      .right-0{right:0}
      .z-10{z-index:10}
      .w-64{width:16rem}
      .w-3{width:0.75rem}
      .h-3{height:0.75rem}
      .h-screen{height:100vh}
      .min-h-screen{min-height:100vh}
      .overflow-y-auto{overflow-y:auto}
      .overflow-x-hidden{overflow-x:hidden}
      .flex-col{flex-direction:column}
      .flex-row{flex-direction:row}
      .flex-1{flex:1 1 0%}
      .flex-wrap{flex-wrap:wrap}
      .justify-center{justify-content:center}
      .space-x-2>:not([hidden])~:not([hidden]){margin-left:0.5rem}
      .space-y-2>:not([hidden])~:not([hidden]){margin-top:0.5rem}
      .gap-2{gap:0.5rem}
      .gap-6{gap:1.5rem}

      /* Container */
      .container{width:100%;margin-left:auto;margin-right:auto}
      @media (min-width:640px){.container{max-width:640px}}
      @media (min-width:768px){.container{max-width:768px}}
      @media (min-width:1024px){.container{max-width:1024px}}
      @media (min-width:1280px){.container{max-width:1280px}}

      /* Display utilities */
      .md\:flex{display:flex}
      .md\:block{display:block}

      /* Mobile: Hide sidebar */
      @media (max-width:767px){
        .arcuras-sidebar{display:none}
      }

      @media (min-width:768px){
        .md\:flex{display:flex}
        .md\:flex-row{flex-direction:row}
        .md\:block{display:block}
        .hidden.md\:block{display:block}
      }

      /* Desktop: Hide mobile menu toggle */
      @media (min-width:1024px){
        .menu-toggle{display:none !important}
        .lg\:hidden{display:none !important}
        .lg\:block{display:block !important}
        .lg\:flex{display:flex !important}
      }

      /* Transitions */
      .transition-colors{transition-property:color,background-color,border-color,text-decoration-color,fill,stroke;transition-timing-function:cubic-bezier(0.4,0,0.2,1);transition-duration:150ms}
      .duration-200{transition-duration:200ms}

      /* Group hover (for sidebar icons) */
      .group:hover .group-hover\:text-accent-500{color:rgb(59 130 246)}
      .hover\:text-accent-600:hover{color:rgb(37 99 235)}
      .hover\:text-accent-700:hover{color:rgb(29 78 216)}
      .hover\:bg-gray-50:hover{background-color:rgb(249 250 251)}
      .hover\:bg-accent-100:hover{background-color:rgb(219 234 254)}
    </style>

    <?php wp_head(); ?>
</head>

<body <?php body_class('bg-white text-gray-800 antialiased overflow-x-hidden'); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site min-h-screen flex flex-col overflow-x-hidden">

    <header id="masthead" class="site-header bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
        <div class="px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="site-branding flex items-center">
                    <?php
                    $has_logo = has_custom_logo();

                    if ($has_logo) {
                        $logo_html = get_custom_logo();

                        if ($logo_html) {
                            // Logo boyutunu maksimum 40px yüksekliğe sınırla.
                            $logo_html = str_replace('class="custom-logo', 'class="custom-logo max-h-10 w-auto', $logo_html);
                            echo $logo_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        }
                    } else {
                        ?>
                        <span class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-100 text-primary-600 shadow-inner" aria-hidden="true">
                            <?php gufte_icon('music', 'w-6 h-6'); ?>
                        </span>
                        <?php
                    }
                    ?>
                    
                    <div class="site-title-area ml-3">
                        <div class="site-title<?php echo $has_logo ? ' hidden md:block' : ''; ?>">
                            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="text-xl font-bold text-gray-800 hover:text-primary-600 transition-colors duration-300">
                                <?php bloginfo( 'name' ); ?>
                            </a>
                        </div>
                        <?php
                        // Slogan kısmını tamamen kaldırıyoruz
                        // $gufte_description satırları silindi
                        ?>
                    </div>
                </div><!-- .site-branding -->

                <div class="flex items-center space-x-4">
                    <button type="button" class="hidden lg:flex items-center justify-center w-10 h-10 rounded-lg border border-gray-200 text-gray-600 hover:text-primary-600 hover:border-primary-400 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500" data-search-modal-open aria-haspopup="dialog" aria-expanded="false" aria-label="<?php esc_attr_e('Search', 'gufte'); ?>">
                        <?php gufte_icon('magnify', 'w-5 h-5'); ?>
                    </button>

                    <!-- Contributor Button -->

                    <button type="button" class="lg:hidden flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 text-gray-600 hover:text-primary-600 hover:border-primary-400 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500" data-search-modal-open aria-haspopup="dialog" aria-expanded="false" aria-label="<?php esc_attr_e('Search', 'gufte'); ?>">
                        <?php gufte_icon('magnify', 'w-5 h-5'); ?>
                    </button>

                    <nav id="site-navigation" class="main-navigation order-2 lg:order-1">
                        <button class="menu-toggle lg:hidden flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 text-gray-600 hover:text-primary-600 hover:border-primary-400 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500" aria-controls="primary-menu" aria-expanded="false" aria-label="<?php esc_attr_e( 'Menu', 'gufte' ); ?>">
                            <?php gufte_icon("menu", "w-5 h-5"); ?>
                        </button>
                        
                        <div id="primary-menu-wrapper" class="hidden lg:block">
                            <?php
                            wp_nav_menu(
                                array(
                                    'theme_location' => 'primary',
                                    'menu_id'        => 'primary-menu',
                                    'container'      => false,
                                    'menu_class'     => 'flex space-x-1',
                                    'fallback_cb'    => false,
                                    'link_before'    => '<span class="relative py-2 px-3 rounded-md hover:bg-gray-100 hover:text-primary-600 transition-colors duration-300">',
                                    'link_after'     => '</span>',
                                )
                            );
                            ?>
                        </div>
                    </nav><!-- #site-navigation -->
                    
                    <!-- User Account / Login Button -->
                    <div class="user-account order-3">
                        
                        <?php if ( is_user_logged_in() ) : 
                            $current_user = wp_get_current_user();
                        ?>
                            <div class="flex items-center relative group">
                                <button class="flex items-center space-x-1 focus:outline-none" aria-expanded="false">
                                    <div class="w-8 h-8 rounded-full overflow-hidden border-2 border-primary-400 shadow-md">
                                        <?php echo get_avatar( $current_user->ID, 32, '', '', array('class' => 'w-full h-full object-cover') ); ?>
                                    </div>
                                    <?php gufte_icon("chevron-down", "text-gray-400 group-hover:text-gray-600 transition-colors duration-300 w-5 h-5"); ?>
                                </button>
                                
                                <!-- Dropdown Menu -->
                                <div class="user-dropdown absolute right-0 top-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg w-48 hidden group-hover:block transition-opacity duration-300 py-1 z-50">
                                    <div class="border-b border-gray-200 px-4 py-2 mb-1">
                                        <div class="font-medium text-gray-800"><?php echo esc_html( $current_user->display_name ); ?></div>
                                        <div class="text-xs text-gray-500 truncate"><?php echo esc_html( $current_user->user_email ); ?></div>
                                        <?php
                                        // Kullanıcı rolünü göster
                                        $user_roles = $current_user->roles;
                                        if (!empty($user_roles)) {
                                            $role = $user_roles[0]; // İlk rolü al
                                            // Rol isimlerini Türkçeleştir
                                            $role_names = array(
                                                'administrator' => __('Administrator', 'gufte'),
                                                'editor' => __('Editor', 'gufte'),
                                                'author' => __('Author', 'gufte'),
                                                'contributor' => __('Contributor', 'gufte'),
                                                'subscriber' => __('Subscriber', 'gufte'),
                                            );
                                            $role_display = isset($role_names[$role]) ? $role_names[$role] : ucfirst($role);

                                            // Rol badge renkleri
                                            $role_colors = array(
                                                'administrator' => 'bg-red-100 text-red-700 border-red-200',
                                                'editor' => 'bg-purple-100 text-purple-700 border-purple-200',
                                                'author' => 'bg-blue-100 text-blue-700 border-blue-200',
                                                'contributor' => 'bg-green-100 text-green-700 border-green-200',
                                                'subscriber' => 'bg-gray-100 text-gray-700 border-gray-200',
                                            );
                                            $role_color = isset($role_colors[$role]) ? $role_colors[$role] : 'bg-gray-100 text-gray-700 border-gray-200';
                                        ?>
                                        <span class="inline-block mt-1 px-2 py-0.5 text-xs font-medium rounded-full border <?php echo esc_attr($role_color); ?>">
                                            <?php echo esc_html($role_display); ?>
                                        </span>
                                        <?php } ?>
                                    </div>
                                    
                                    <?php if ( current_user_can('edit_posts') ) : ?>
                                    <a href="<?php echo esc_url( admin_url('post-new.php') ); ?>" class="flex items-center px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-primary-600 transition-colors duration-150">
                                        <?php gufte_icon("music-note-plus", "mr-2 w-5 h-5"); ?> 
                                        <?php esc_html_e( 'Add New Lyrics', 'gufte' ); ?>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <a href="<?php echo esc_url( home_url('/my-profile/') ); ?>" class="flex items-center px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-primary-600 transition-colors duration-150">
                                        <?php gufte_icon("account-edit", "mr-2 w-5 h-5"); ?> 
                                        <?php esc_html_e( 'Edit Profile', 'gufte' ); ?>
                                    </a>
                                    
                                    <a href="<?php echo esc_url( get_dashboard_url() ); ?>" class="flex items-center px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-primary-600 transition-colors duration-150">
                                        <?php gufte_icon("dashboard", "mr-2 w-5 h-5"); ?> 
                                        <?php esc_html_e( 'Dashboard', 'gufte' ); ?>
                                    </a>
                                    
                                    <div class="border-t border-gray-200 mt-1 pt-1">
                                        <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="flex items-center px-4 py-2 text-sm text-red-500 hover:bg-gray-100 hover:text-red-600 transition-colors duration-150">
                                            <?php gufte_icon("logout", "mr-2 w-5 h-5"); ?> 
                                            <?php esc_html_e( 'Log Out', 'gufte' ); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php else : ?>
                            <a href="<?php echo esc_url( wp_login_url( home_url() ) ); ?>" aria-label="<?php esc_html_e( 'Login', 'gufte' ); ?>" class="flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 text-gray-600 hover:text-primary-600 hover:border-primary-400 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 mr-2">
                                <?php gufte_icon("login", "w-5 h-5"); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Mobile Menu Modal -->
            <div id="mobile-menu" class="lg:hidden fixed inset-0 z-[9999] hidden flex items-center justify-center min-h-screen p-4" role="dialog" aria-modal="true" aria-hidden="true">
                <!-- Backdrop -->
                <div id="mobile-menu-overlay" class="absolute inset-0 bg-gray-900/70 backdrop-blur-sm transition-opacity" data-mobile-menu-close style="z-index: 1;"></div>

                <!-- Modal Content -->
                <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-2xl ring-1 ring-gray-100 max-h-[calc(100vh-2rem)] overflow-hidden flex flex-col" style="z-index: 10; position: relative;">
                    <!-- Modal Header -->
                    <div class="flex items-start justify-between border-b border-gray-100 px-6 py-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                <?php gufte_icon("menu", "mr-2 text-primary-500 w-5 h-5"); ?>
                                <?php esc_html_e('Menu', 'gufte'); ?>
                            </h2>
                            <p class="mt-1 text-sm text-gray-500"><?php esc_html_e('Navigate through the site', 'gufte'); ?></p>
                        </div>
                        <button type="button" id="mobile-menu-close" class="flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:text-primary-600 hover:border-primary-400 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500" aria-label="<?php esc_attr_e('Close menu', 'gufte'); ?>">
                            <?php gufte_icon("close", "w-5 h-5"); ?>
                        </button>
                    </div>

                    <!-- Modal Content -->
                    <div class="flex-1 px-6 py-5 space-y-6 overflow-y-auto">
                    <!-- User Profile Section -->
                    <?php if ( is_user_logged_in() ) :
                        $current_user = wp_get_current_user();
                    ?>
                    <div class="mobile-user-profile bg-white border border-gray-200 rounded-lg overflow-hidden">
                        <!-- User Info -->
                        <div class="flex items-center space-x-3 p-3 border-b border-gray-100">
                            <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-primary-400 flex-shrink-0">
                                <?php echo get_avatar( $current_user->ID, 40, '', '', array('class' => 'w-full h-full object-cover') ); ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-semibold text-gray-800 truncate"><?php echo esc_html( $current_user->display_name ); ?></h3>
                                <p class="text-xs text-gray-500 truncate"><?php echo esc_html( $current_user->user_email ); ?></p>
                            </div>
                        </div>

                        <!-- Quick Actions List -->
                        <div class="divide-y divide-gray-100">
                            <?php if ( current_user_can('edit_posts') ) : ?>
                            <a href="<?php echo esc_url( admin_url('post-new.php') ); ?>" class="flex items-center px-3 py-2.5 hover:bg-gray-50 transition-colors duration-150">
                                <?php gufte_icon("plus", "w-4 h-4 mr-3 text-blue-500"); ?>
                                <span class="text-sm text-gray-700"><?php esc_html_e( 'New Post', 'gufte' ); ?></span>
                            </a>
                            <?php endif; ?>
                            <a href="<?php echo esc_url( home_url('/my-profile/') ); ?>" class="flex items-center px-3 py-2.5 hover:bg-gray-50 transition-colors duration-150">
                                <?php gufte_icon("account-edit", "w-4 h-4 mr-3 text-purple-500"); ?>
                                <span class="text-sm text-gray-700"><?php esc_html_e( 'My Profile', 'gufte' ); ?></span>
                            </a>
                        </div>
                    </div>
                    <?php else : ?>
                    <div class="mobile-user-profile bg-gradient-to-r from-blue-50 to-purple-50 p-4 rounded-lg border border-blue-100">
                        <div class="text-center">
                            <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-gray-200 flex items-center justify-center">
                                <?php gufte_icon("account-circle", "w-10 h-10 text-gray-400"); ?>
                            </div>
                            <h3 class="font-bold text-gray-800 mb-2"><?php esc_html_e('Welcome!', 'gufte'); ?></h3>
                            <p class="text-xs text-gray-600 mb-3"><?php esc_html_e('Sign in to access all features', 'gufte'); ?></p>
                            <a href="<?php echo esc_url( wp_login_url( home_url() ) ); ?>" class="inline-flex items-center justify-center px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-lg text-sm font-medium transition-colors duration-150 shadow-sm">
                                <?php gufte_icon("login", "w-4 h-4 mr-1"); ?>
                                <?php esc_html_e( 'Sign In', 'gufte' ); ?>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Ana Menü -->
                    <div class="mobile-main-menu">
                        <h3 class="text-sm font-bold text-gray-700 mb-3 border-b-2 border-primary-200 pb-2 flex items-center">
                            <?php gufte_icon("menu", "mr-2 text-primary-500 w-5 h-5"); ?>
                            <?php esc_html_e('Main Menu', 'gufte'); ?>
                        </h3>
                        <?php
                        wp_nav_menu(
                            array(
                                'theme_location' => 'primary',
                                'menu_id'        => 'mobile-primary-menu',
                                'container'      => false,
                                'menu_class'     => 'flex flex-col space-y-1',
                                'fallback_cb'    => false,
                                'link_before'    => '<span class="block py-3 px-4 rounded-lg hover:bg-blue-50 hover:text-primary-600 transition-colors duration-150">',
                                'link_after'     => '</span>',
                            )
                        );
                        ?>
                    </div>
                    
                    <!-- Son Çeviriler -->
                    <?php
                    $recent_posts = new WP_Query(array(
                        'post_type' => array('post', 'lyrics'),
                        'posts_per_page' => 5,
                        'ignore_sticky_posts' => 1
                    ));

                    if ($recent_posts->have_posts()) :
                    ?>
                    <div class="mobile-recent-posts bg-blue-50 p-3 rounded-lg">
                        <h3 class="text-sm font-bold text-gray-700 mb-3 border-b-2 border-blue-200 pb-2 flex items-center">
                            <?php gufte_icon("clock", "mr-2 text-blue-500 w-5 h-5"); ?>
                            <?php esc_html_e('Latest Translations', 'gufte'); ?>
                        </h3>
                        <ul class="space-y-2">
                            <?php
                                while ($recent_posts->have_posts()) : $recent_posts->the_post();
                                    // İçerik türlerine göre ikonları ayarla
                                    $post_icon = 'file-document';
                                    if (has_term('', 'singer')) {
                                        $post_icon = 'music-note';
                                    }
                            ?>
                                <li class="flex items-start space-x-2 p-2 rounded-lg hover:bg-white transition-colors duration-100">
                                    <?php gufte_icon($post_icon, 'text-accent-500 mt-1 flex-shrink-0 w-5 h-5'); ?>
                                    <a href="<?php the_permalink(); ?>" class="text-sm hover:text-primary-600 transition-colors duration-200 line-clamp-2">
                                        <?php the_title(); ?>
                                    </a>
                                </li>
                            <?php
                                endwhile;
                                wp_reset_postdata();
                            ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Kategoriler -->
                    <?php
                    // Original dilleri al
                    $original_languages = get_terms(array(
                        'taxonomy' => 'original_language',
                        'orderby' => 'count',
                        'order' => 'DESC',
                        'number' => 5,
                        'hide_empty' => true,
                    ));

                    // Translation dilleri al
                    $translation_languages = get_terms(array(
                        'taxonomy' => 'translated_language',
                        'orderby' => 'count',
                        'order' => 'DESC',
                        'number' => 5,
                        'hide_empty' => true,
                    ));

                    $has_languages = (!empty($original_languages) && !is_wp_error($original_languages)) ||
                                    (!empty($translation_languages) && !is_wp_error($translation_languages));

                    if ($has_languages) :
                    ?>
                    <div class="mobile-categories bg-green-50 p-3 rounded-lg">
                        <h3 class="text-sm font-bold text-gray-700 mb-3 border-b-2 border-green-200 pb-2 flex items-center">
                            <?php gufte_icon("folder", "mr-2 text-green-500 w-5 h-5"); ?>
                            <?php esc_html_e('Language Categories', 'gufte'); ?>
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            <?php
                            // Original dilleri göster
                            if (!empty($original_languages) && !is_wp_error($original_languages)) :
                                foreach ($original_languages as $language) :
                            ?>
                                <a href="<?php echo esc_url(get_term_link($language)); ?>" class="text-xs bg-blue-100 hover:bg-blue-500 text-blue-700 hover:text-white transition-colors duration-150 px-3 py-1.5 rounded-lg">
                                    <?php echo esc_html($language->name); ?>
                                    <span class="text-xs opacity-75">(<?php echo esc_html($language->count); ?>)</span>
                                </a>
                            <?php
                                endforeach;
                            endif;

                            // Translation dilleri göster
                            if (!empty($translation_languages) && !is_wp_error($translation_languages)) :
                                foreach ($translation_languages as $language) :
                            ?>
                                <a href="<?php echo esc_url(get_term_link($language)); ?>" class="text-xs bg-green-100 hover:bg-green-500 text-green-700 hover:text-white transition-colors duration-150 px-3 py-1.5 rounded-lg">
                                    <?php echo esc_html($language->name); ?>
                                    <span class="text-xs opacity-75">(<?php echo esc_html($language->count); ?>)</span>
                                </a>
                            <?php
                                endforeach;
                            endif;
                            ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Şarkıcılar -->
                    <?php if (taxonomy_exists('singer')) : 
                        $singers = get_terms(array(
                            'taxonomy' => 'singer',
                            'orderby' => 'count',
                            'order' => 'DESC',
                            'number' => 6,
                            'hide_empty' => true,
                        ));
                        
                        if (!empty($singers) && !is_wp_error($singers)) :
                    ?>
                    <div class="mobile-singers bg-purple-50 p-3 rounded-lg">
                        <h3 class="text-sm font-bold text-gray-700 mb-3 border-b-2 border-purple-200 pb-2 flex items-center">
                            <?php gufte_icon("microphone", "mr-2 text-purple-500 w-5 h-5"); ?>
                            <?php esc_html_e('Popular Singers', 'gufte'); ?>
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($singers as $singer) : ?>
                                <a href="<?php echo esc_url(get_term_link($singer)); ?>" class="text-xs bg-purple-100 hover:bg-purple-500 text-purple-700 hover:text-white transition-colors duration-150 px-3 py-1.5 rounded-lg">
                                    <?php echo esc_html($singer->name); ?>
                                    <span class="text-xs opacity-75">(<?php echo esc_html($singer->count); ?>)</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; endif; ?>

                    <!-- Logout Button (if logged in) -->
                    <?php if ( is_user_logged_in() ) : ?>
                    <div class="mobile-logout border-t border-gray-200 pt-4">
                        <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="flex items-center justify-center px-4 py-3 bg-red-50 hover:bg-red-500 text-red-600 hover:text-white rounded-lg font-medium transition-colors duration-150 shadow-sm">
                            <?php gufte_icon("logout", "w-5 h-5 mr-2"); ?>
                            <?php esc_html_e( 'Sign Out', 'gufte' ); ?>
                        </a>
                    </div>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
</header><!-- #masthead -->

    <?php if ( is_front_page() && !is_home() ) : ?>
    <div class="hero-section bg-gradient-to-r from-blue-50 via-primary-100 to-accent-100 text-gray-800 py-16 overflow-hidden"> <!-- Renkleri açık tema için güncelledik -->
        <div class="max-w-6xl mx-auto px-4">
            <div class="md:w-2/3">
                <h1 class="text-4xl md:text-5xl font-bold mb-4"><?php esc_html_e( 'Welcome to Güfte', 'gufte' ); ?></h1>
                <p class="text-xl mb-8 text-gray-600"><?php esc_html_e( 'A modern WordPress theme built with Tailwind CSS', 'gufte' ); ?></p>
                <a href="#primary" class="inline-block px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium shadow-md hover:shadow-lg transition-all duration-300">
                    <?php esc_html_e( 'Explore', 'gufte' ); ?>
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div id="content" class="site-content flex-grow overflow-hidden"> <!-- overflow-hidden eklendi -->

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.querySelector('.menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');
        const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');
        const mobileMenuClose = document.getElementById('mobile-menu-close');
        let scrollPosition = 0;

        function openMobileMenu() {
            if (mobileMenu) {
                // Mevcut scroll pozisyonunu kaydet
                scrollPosition = window.scrollY;
                document.body.setAttribute('data-scroll-position', scrollPosition);

                // Body scroll'u engelle
                document.body.style.position = 'fixed';
                document.body.style.top = `-${scrollPosition}px`;
                document.body.style.width = '100%';
                document.body.classList.add('overflow-hidden');

                // Modal'ı göster
                mobileMenu.classList.remove('hidden');
                mobileMenu.setAttribute('aria-hidden', 'false');

                // Accessibility
                if (menuToggle) {
                    menuToggle.setAttribute('aria-expanded', 'true');
                }
            }
        }

        function closeMobileMenu() {
            if (mobileMenu) {
                // Modal'ı gizle
                mobileMenu.classList.add('hidden');
                mobileMenu.setAttribute('aria-hidden', 'true');

                // Body scroll'u geri aç ve pozisyonu restore et
                const savedScrollPosition = parseInt(document.body.getAttribute('data-scroll-position') || '0', 10);

                document.body.classList.remove('overflow-hidden');
                document.body.style.position = '';
                document.body.style.top = '';
                document.body.style.width = '';
                document.body.removeAttribute('data-scroll-position');

                // Scroll pozisyonunu geri yükle
                window.scrollTo(0, savedScrollPosition);

                // Accessibility
                if (menuToggle) {
                    menuToggle.setAttribute('aria-expanded', 'false');
                }
            }
        }

        // Hamburger menü tıklandığında aç
        if (menuToggle) {
            menuToggle.addEventListener('click', function() {
                const isOpen = !mobileMenu.classList.contains('hidden');
                if (isOpen) {
                    closeMobileMenu();
                } else {
                    openMobileMenu();
                }
            });
        }

        // Kapat butonu
        if (mobileMenuClose) {
            mobileMenuClose.addEventListener('click', closeMobileMenu);
        }

        // Overlay'e tıklandığında kapat
        if (mobileMenuOverlay) {
            mobileMenuOverlay.addEventListener('click', function(e) {
                if (e.target === mobileMenuOverlay || e.target.hasAttribute('data-mobile-menu-close')) {
                    closeMobileMenu();
                }
            });
        }

        // ESC tuşu ile kapat
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && mobileMenu && !mobileMenu.classList.contains('hidden')) {
                closeMobileMenu();
            }
        });
        
        const searchModal = document.querySelector('[data-search-modal]');
        const searchOpenButtons = document.querySelectorAll('[data-search-modal-open]');
        const searchCloseTargets = searchModal ? searchModal.querySelectorAll('[data-search-modal-close]') : [];
        const searchInput = searchModal ? searchModal.querySelector('[data-search-input]') : null;
        const pageContainer = document.getElementById('page');
        let lastFocusedElement = null;

        function openSearchModal() {
            if (!searchModal) {
                return;
            }
            lastFocusedElement = document.activeElement;
            searchModal.classList.remove('hidden');
            searchModal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');
            document.body.classList.add('search-modal-open');
            document.body.classList.add('modal-active');
            if (pageContainer) {
                pageContainer.setAttribute('aria-hidden', 'true');
                pageContainer.setAttribute('inert', '');
            }
            searchOpenButtons.forEach(function(button) {
                button.setAttribute('aria-expanded', 'true');
            });
            window.setTimeout(function() {
                if (searchInput) {
                    searchInput.focus({ preventScroll: true });
                }
            }, 50);
        }

        function closeSearchModal() {
            if (!searchModal) {
                return;
            }
            searchModal.classList.add('hidden');
            searchModal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-hidden');
            document.body.classList.remove('search-modal-open');
            document.body.classList.remove('modal-active');
            if (pageContainer) {
                pageContainer.removeAttribute('aria-hidden');
                pageContainer.removeAttribute('inert');
            }
            searchOpenButtons.forEach(function(button) {
                button.setAttribute('aria-expanded', 'false');
            });
            if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
                lastFocusedElement.focus({ preventScroll: true });
            }
        }

        if (searchModal) {
            searchOpenButtons.forEach(function(button) {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    openSearchModal();
                });
            });

            searchCloseTargets.forEach(function(target) {
                target.addEventListener('click', function(event) {
                    event.preventDefault();
                    closeSearchModal();
                });
            });

            searchModal.addEventListener('click', function(event) {
                if (event.target === searchModal) {
                    closeSearchModal();
                }
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && searchModal.getAttribute('aria-hidden') === 'false') {
                    closeSearchModal();
                }
            });
        }
        
        // Swiper ayarlarını güncelle ve yatay taşmaları önle
        if (typeof Swiper !== 'undefined') {
            const swipers = document.querySelectorAll('.swiper-container');
            swipers.forEach(container => {
                const swiper = container.swiper;
                if (swiper) {
                    swiper.update(); // Swiper'ı güncelle
                }
            });
        }
    });
</script>
