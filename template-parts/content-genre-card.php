<?php
/**
 * Template part for displaying genre card with animated gradient background
 *
 * @package Gufte
 * @since 1.9.6
 *
 * Available variables:
 * @var object $genre          Genre term object
 * @var string $card_style     Card style: 'default', 'compact' (default: 'default')
 * @var int    $lyrics_count   Number of lyrics in this genre
 */

// Get variables from query vars
$genre = get_query_var('genre', null);
$card_style = get_query_var('card_style', 'default');
$lyrics_count = get_query_var('lyrics_count', 0);
$genre_images = get_query_var('genre_images', array());

if (!$genre) {
    return;
}

// Define gradient color schemes for different genres
$gradient_schemes = array(
    array('from' => '#667eea', 'via' => '#764ba2', 'to' => '#f093fb'),
    array('from' => '#4facfe', 'via' => '#00f2fe', 'to' => '#43e97b'),
    array('from' => '#fa709a', 'via' => '#fee140', 'to' => '#30cfd0'),
    array('from' => '#a8edea', 'via' => '#fed6e3', 'to' => '#fbc2eb'),
    array('from' => '#ff9a9e', 'via' => '#fecfef', 'to' => '#ffecd2'),
    array('from' => '#fdcbf1', 'via' => '#e6dee9', 'to' => '#a1c4fd'),
    array('from' => '#ffecd2', 'via' => '#fcb69f', 'to' => '#ff6e7f'),
    array('from' => '#08aeea', 'via' => '#2af598', 'to' => '#1de9b6'),
);

// Select gradient based on genre ID for consistency
$gradient_index = $genre->term_id % count($gradient_schemes);
$gradient = $gradient_schemes[$gradient_index];

// Generate unique animation name
$animation_name = 'gradient-animation-' . $genre->term_id;

// Get genre URL - since it's meta based, create search URL
$genre_url = add_query_arg(array(
    's' => '',
    'post_type' => 'lyrics',
    'genre' => $genre->slug
), home_url('/'));
?>

<div class="genre-card" data-genre-id="<?php echo esc_attr($genre->term_id); ?>">
    <a href="<?php echo esc_url($genre_url); ?>" class="genre-card-link">
        <?php if (!empty($genre_images)) : ?>
        <!-- Featured Images Collage Background -->
        <div class="genre-card-images">
            <?php foreach (array_slice($genre_images, 0, 3) as $index => $image_url) : ?>
            <div class="genre-card-image genre-card-image-<?php echo $index + 1; ?>" style="background-image: url('<?php echo esc_url($image_url); ?>');"></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Animated Gradient Overlay -->
        <div class="genre-card-bg" style="background: linear-gradient(135deg, <?php echo esc_attr($gradient['from']); ?>, <?php echo esc_attr($gradient['via']); ?>, <?php echo esc_attr($gradient['to']); ?>); animation: <?php echo esc_attr($animation_name); ?> 15s ease infinite;"></div>

        <!-- Overlay Pattern -->
        <div class="genre-card-pattern"></div>

        <!-- Content -->
        <div class="genre-card-content">
            <div class="genre-card-header">
                <h3 class="genre-card-title"><?php echo esc_html($genre->name); ?></h3>
                <?php if (isset($genre->count) && $genre->count > 0) : ?>
                <span class="genre-card-count"><?php echo esc_html($genre->count); ?> songs</span>
                <?php endif; ?>
            </div>

            <div class="genre-card-footer">
                <span class="genre-card-cta">
                    Explore
                    <svg class="genre-card-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M13.22 19.03a.75.75 0 0 1 0-1.06L18.19 13H3.75a.75.75 0 0 1 0-1.5h14.44l-4.97-4.97a.749.749 0 0 1 .326-1.275.749.749 0 0 1 .734.215l6.25 6.25a.75.75 0 0 1 0 1.06l-6.25 6.25a.75.75 0 0 1-1.06 0Z"></path>
                    </svg>
                </span>
            </div>
        </div>
    </a>

    <style>
    /* Unique keyframe animation for this genre */
    @keyframes <?php echo esc_attr($animation_name); ?> {
        0% {
            background-position: 0% 50%;
        }
        50% {
            background-position: 100% 50%;
        }
        100% {
            background-position: 0% 50%;
        }
    }
    </style>
</div>

<style>
/* Genre Card Base Styles */
.genre-card {
    position: relative;
    border-radius: 24px;
    overflow: hidden;
    aspect-ratio: 2 / 3;
    box-shadow:
        0 10px 40px rgba(0, 0, 0, 0.2),
        0 0 0 1px rgba(255, 255, 255, 0.1) inset;
    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1),
                box-shadow 0.4s ease,
                filter 0.4s ease;
}

.genre-card::before {
    content: '';
    position: absolute;
    inset: -2px;
    border-radius: 24px;
    padding: 2px;
    background: linear-gradient(135deg,
        rgba(255, 255, 255, 0.4),
        rgba(255, 255, 255, 0.1),
        rgba(255, 255, 255, 0.4));
    -webkit-mask:
        linear-gradient(#fff 0 0) content-box,
        linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
    z-index: 10;
}

.genre-card:hover {
    transform: translateY(-12px) scale(1.03);
    box-shadow:
        0 25px 60px rgba(0, 0, 0, 0.35),
        0 0 0 1px rgba(255, 255, 255, 0.2) inset;
}

.genre-card:hover::before {
    opacity: 1;
}

.genre-card-link {
    display: block;
    width: 100%;
    height: 100%;
    position: relative;
    text-decoration: none;
    color: white;
}

/* Featured Images Collage Background - 3 images for performance */
.genre-card-images {
    position: absolute;
    inset: 0;
    z-index: 0;
    display: flex;
    gap: 1px;
    opacity: 0.4;
    transition: opacity 0.3s ease;
}

.genre-card:hover .genre-card-images {
    opacity: 0.6;
}

.genre-card-image {
    flex: 1;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    filter: grayscale(20%) brightness(1);
    transition: filter 0.3s ease;
}

.genre-card:hover .genre-card-image {
    filter: grayscale(0%) brightness(1.1);
}

/* Desktop - more visible images */
@media (min-width: 1024px) {
    .genre-card-images {
        opacity: 0.5;
    }

    .genre-card:hover .genre-card-images {
        opacity: 0.7;
    }
}

/* Animated Gradient Overlay */
.genre-card-bg {
    position: absolute;
    inset: 0;
    background-size: 400% 400% !important;
    z-index: 1;
    opacity: 0.75;
    mix-blend-mode: multiply;
    transition: opacity 0.3s ease;
}

.genre-card:hover .genre-card-bg {
    opacity: 0.65;
}

/* Desktop - lighter overlay to show images better */
@media (min-width: 1024px) {
    .genre-card-bg {
        opacity: 0.7;
    }

    .genre-card:hover .genre-card-bg {
        opacity: 0.6;
    }
}

/* Neon Glow Effect */
.genre-card::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 24px;
    opacity: 0;
    background: inherit;
    filter: blur(20px);
    z-index: -1;
    transition: opacity 0.4s ease;
}

.genre-card:hover::after {
    opacity: 0.6;
}

/* Pattern Overlay */
.genre-card-pattern {
    position: absolute;
    inset: 0;
    z-index: 2;
    background-image:
        radial-gradient(circle at 20% 30%, rgba(255, 255, 255, 0.15) 0%, transparent 50%),
        radial-gradient(circle at 80% 70%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.05) 0%, transparent 60%),
        repeating-linear-gradient(0deg, rgba(255, 255, 255, 0.03) 0px, transparent 1px, transparent 2px, rgba(255, 255, 255, 0.03) 3px);
    opacity: 0.7;
    pointer-events: none;
    mix-blend-mode: overlay;
    transition: opacity 0.3s ease;
}

.genre-card:hover .genre-card-pattern {
    opacity: 0.9;
}

/* Content */
.genre-card-content {
    position: absolute;
    inset: 0;
    z-index: 3;
    padding: clamp(16px, 3vw, 24px);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.genre-card-header {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.genre-card-title {
    font-size: clamp(18px, 4vw, 28px);
    font-weight: 900;
    color: white;
    margin: 0;
    text-shadow:
        0 0 20px rgba(255, 255, 255, 0.5),
        0 0 40px rgba(255, 255, 255, 0.3),
        0 4px 15px rgba(0, 0, 0, 0.4);
    letter-spacing: -0.5px;
    transition: text-shadow 0.3s ease;
    line-height: 1.1;
}

.genre-card:hover .genre-card-title {
    text-shadow:
        0 0 30px rgba(255, 255, 255, 0.8),
        0 0 60px rgba(255, 255, 255, 0.5),
        0 4px 15px rgba(0, 0, 0, 0.4);
}

.genre-card-count {
    font-size: clamp(10px, 2vw, 13px);
    font-weight: 700;
    color: white;
    background: rgba(255, 255, 255, 0.25);
    backdrop-filter: blur(12px);
    padding: clamp(6px, 1.5vw, 8px) clamp(12px, 2.5vw, 16px);
    border-radius: 24px;
    display: inline-block;
    width: fit-content;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow:
        0 4px 15px rgba(0, 0, 0, 0.2),
        0 0 20px rgba(255, 255, 255, 0.2) inset;
    transition: all 0.3s ease;
}

.genre-card:hover .genre-card-count {
    background: rgba(255, 255, 255, 0.35);
    border-color: rgba(255, 255, 255, 0.5);
    box-shadow:
        0 4px 20px rgba(0, 0, 0, 0.3),
        0 0 30px rgba(255, 255, 255, 0.3) inset;
}

.genre-card-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
}

.genre-card-cta {
    display: inline-flex;
    align-items: center;
    gap: clamp(6px, 1.5vw, 8px);
    font-size: clamp(11px, 2.2vw, 14px);
    font-weight: 800;
    color: white;
    background: rgba(255, 255, 255, 0.25);
    backdrop-filter: blur(12px);
    padding: clamp(10px, 2vw, 14px) clamp(16px, 3vw, 24px);
    border-radius: 30px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow:
        0 4px 15px rgba(0, 0, 0, 0.2),
        0 0 20px rgba(255, 255, 255, 0.2) inset;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
}

.genre-card:hover .genre-card-cta {
    background: rgba(255, 255, 255, 0.4);
    border-color: rgba(255, 255, 255, 0.6);
    gap: 12px;
    box-shadow:
        0 6px 25px rgba(0, 0, 0, 0.3),
        0 0 40px rgba(255, 255, 255, 0.4) inset;
    text-shadow:
        0 0 20px rgba(255, 255, 255, 0.8),
        0 2px 10px rgba(0, 0, 0, 0.3);
}

.genre-card-arrow {
    width: 20px;
    height: 20px;
    transition: transform 0.3s ease;
}

.genre-card:hover .genre-card-arrow {
    transform: translateX(4px);
}

/* Responsive - minor adjustments only */
@media (max-width: 640px) {
    .genre-card {
        border-radius: 20px;
    }

    .genre-card::before,
    .genre-card::after {
        border-radius: 20px;
    }

    .genre-card:hover {
        transform: translateY(-6px) scale(1.02);
    }
}

/* Desktop optimizations */
@media (min-width: 1024px) {
    .genre-card-header {
        gap: 10px;
    }
}
</style>
