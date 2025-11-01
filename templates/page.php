<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Arcuras
 */

get_header();

// Enqueue page-specific CSS
wp_enqueue_style('arcuras-page-style', get_template_directory_uri() . '/style-page.css', array(), '1.0.0');
?>

<main id="primary" class="site-main pt-2 pb-8">
    <div class="max-w-4xl mx-auto px-4">
        <div class="w-full">
            <?php
            while ( have_posts() ) :
                the_post();
                ?>
                    
                    <article id="post-<?php the_ID(); ?>" <?php post_class('bg-white rounded-lg shadow-md overflow-hidden border border-gray-200'); ?>>
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="post-thumbnail">
                                <?php the_post_thumbnail('full', array('class' => 'w-full h-auto')); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="p-6">
                            <header class="entry-header mb-6">
                                <?php the_title( '<h1 class="entry-title text-3xl font-bold text-gray-800 mb-4">', '</h1>' ); ?>
                            </header><!-- .entry-header -->

                            <div class="entry-content prose max-w-none text-gray-700">
                                <?php
                                the_content();

                                wp_link_pages(
                                    array(
                                        'before' => '<div class="page-links mt-6 pt-6 border-t border-gray-200">' . esc_html__( 'Pages:', 'arcuras' ),
                                        'after'  => '</div>',
                                    )
                                );
                                ?>
                            </div><!-- .entry-content -->

                            <?php if ( get_edit_post_link() ) : ?>
                                <footer class="entry-footer mt-8 pt-6 border-t border-gray-200">
                                    <?php
                                    edit_post_link(
                                        sprintf(
                                            wp_kses(
                                                /* translators: %s: Name of current post. Only visible to screen readers */
                                                __( 'Edit <span class="sr-only">%s</span>', 'arcuras' ),
                                                array(
                                                    'span' => array(
                                                        'class' => array(),
                                                    ),
                                                )
                                            ),
                                            wp_kses_post( get_the_title() )
                                        ),
                                        '<span class="edit-link flex items-center text-primary-600 hover:text-primary-700 transition-colors duration-300">',
                                        '<span class="iconify ml-2" data-icon="mdi:pencil"></span></span>'
                                    );
                                    ?>
                                </footer><!-- .entry-footer -->
                            <?php endif; ?>
                        </div>
                    </article><!-- #post-<?php the_ID(); ?> -->

                    <?php
                    // If comments are open or we have at least one comment, load up the comment template.
                    if ( comments_open() || get_comments_number() ) :
                        comments_template();
                    endif;

                endwhile; // End of the loop.
                ?>
            </div>
        </div>
    </div>
</main><!-- #main -->

<?php
get_footer();