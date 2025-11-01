<?php
/**
 * Server-side render for Lyrics & Translations block
 *
 * @param array $attributes Block attributes
 * @param string $content Block content (usually empty for dynamic blocks)
 * @param WP_Block $block Block instance
 * @return string Rendered HTML
 */

// Call the main render function from functions.php
if (function_exists('arcuras_render_lyrics_block')) {
    echo arcuras_render_lyrics_block($attributes);
}
