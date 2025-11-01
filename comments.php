<?php
/**
 * The template for displaying comments
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Gufte
 */
/*
 * If the current post is protected by a password and
 * the visitor has not entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
    return;
}
?>
<div id="comments" class="comments-area my-8 p-6 bg-dark-800 rounded-lg shadow-lg border border-dark-700">
    <?php
    // You can start editing here -- including this comment!
    if ( have_comments() ) :
        ?>
        <h2 class="comments-title text-2xl font-bold text-gray-100 mb-6">
            <?php
            $gufte_comment_count = get_comments_number();
            if ( '1' === $gufte_comment_count ) {
                printf(
                    /* translators: 1: title. */
                    esc_html__( 'One comment for &ldquo;%1$s&rdquo;', 'gufte' ),
                    '<span>' . wp_kses_post( get_the_title() ) . '</span>'
                );
            } else {
                printf( 
                    /* translators: 1: comment count number, 2: title. */
                    esc_html( _nx( '%1$s comment for &ldquo;%2$s&rdquo;', '%1$s comments for &ldquo;%2$s&rdquo;', $gufte_comment_count, 'comments title', 'gufte' ) ),
                    number_format_i18n( $gufte_comment_count ),
                    '<span>' . wp_kses_post( get_the_title() ) . '</span>'
                );
            }
            ?>
        </h2><!-- .comments-title -->
        <div class="comment-stats flex items-center mb-6 text-gray-400 text-sm border-b border-dark-600 pb-4">
            <span class="flex items-center mr-4">
                <?php gufte_icon('comment-multiple', 'mr-1 w-4 h-4'); ?>
                <?php
                printf(
                    /* translators: %d: number of comments */
                    esc_html(_n('%d Comment', '%d Comments', $gufte_comment_count, 'gufte')),
                    number_format_i18n($gufte_comment_count)
                );
                ?>
            </span>

            <?php if ( get_comment_pages_count() > 1 ) : ?>
            <span class="flex items-center">
                <?php gufte_icon('file-document-multiple', 'mr-1 w-4 h-4'); ?>
                <?php
                /* translators: %d: number of comment pages */
                printf(esc_html__('Page %1$d / %2$d', 'gufte'), get_query_var('cpage') ? absint(get_query_var('cpage')) : 1, get_comment_pages_count());
                ?>
            </span>
            <?php endif; ?>
        </div>
        <?php the_comments_navigation(); ?>
        <ol class="comment-list space-y-6 mb-6">
            <?php
            wp_list_comments(
                array(
                    'style'      => 'ol',
                    'short_ping' => true,
                    'callback'   => 'gufte_comment_callback',
                    'avatar_size' => 60,
                )
            );
            ?>
        </ol><!-- .comment-list -->
        <?php
        the_comments_navigation(
            array(
                'prev_text' => '<span class="flex items-center">' . gufte_icon('arrow-left', 'mr-1 w-4 h-4', false) . esc_html__('Older Comments', 'gufte') . '</span>',
                'next_text' => '<span class="flex items-center">' . esc_html__('Newer Comments', 'gufte') . gufte_icon('arrow-right', 'ml-1 w-4 h-4', false) . '</span>',
                'screen_reader_text' => esc_html__('Comments Navigation', 'gufte'),
            )
        );
        // If comments are closed and there are comments, let's leave a little note, shall we?
        if ( ! comments_open() ) :
            ?>
            <p class="no-comments text-gray-400 italic"><?php esc_html_e( 'Comments are closed.', 'gufte' ); ?></p>
            <?php
        endif;
    endif; // Check for have_comments().
    // Define comment form arguments
    $comment_form_args = array(
        'class_form'          => 'comment-form',
        'title_reply'         => esc_html__( 'Leave a Comment', 'gufte' ),
        'title_reply_before'  => '<h3 id="reply-title" class="comment-reply-title text-xl font-bold text-primary-400 mb-4">',
        'title_reply_after'   => '</h3>',
        'title_reply_to'      => esc_html__( 'Reply to %s', 'gufte' ),
        'cancel_reply_link'   => esc_html__( 'Cancel Reply', 'gufte' ),
        'label_submit'        => esc_html__( 'Submit Comment', 'gufte' ),
        'submit_button'       => '<button name="%1$s" type="submit" id="%2$s" class="%3$s px-6 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-md transition-colors duration-300 flex items-center justify-center shadow-md">%4$s</button>',
        'submit_field'        => '<div class="form-submit flex justify-end mt-4">%1$s %2$s</div>',
        
        'comment_field'       => '<div class="comment-form-comment mb-4">
                                    <label for="comment" class="block text-gray-300 mb-2">' . esc_html__( 'Comment', 'gufte' ) . '<span class="required text-accent-500">*</span></label>
                                    <textarea id="comment" name="comment" class="w-full px-3 py-2 bg-dark-700 text-white rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 border border-dark-600" rows="6" required></textarea>
                                  </div>',
        
        'comment_notes_before' => '<p class="comment-notes text-gray-400 mb-4">' . 
                                  sprintf(
                                    /* translators: %1$s: required indicator, %2$s: explanation */
                                    esc_html__( 'Your email address will not be published. Fields marked with %1$s are required.', 'gufte' ),
                                    '<span class="required text-accent-500">*</span>',
                                    ''
                                  ) . '</p>',
                                  
        'comment_notes_after'  => '<p class="comment-notes-after text-gray-400 text-sm mt-4">' . 
                                  esc_html__( 'By submitting a comment, you accept our privacy policy.', 'gufte' ) . '</p>' .
                                  '<p id="comment-spam-warning" style="display:none;" class="comment-spam-warning text-sm text-red-400 mt-2">' .
                                  esc_html__( 'Please avoid sharing links or inappropriate content. Comments containing promotional or adult material will be blocked.', 'gufte' ) . '</p>',
                                  
        'fields'              => array(
            'author' => '<div class="comment-form-author mb-4">
                            <label for="author" class="block text-gray-300 mb-2">' . esc_html__( 'Name', 'gufte' ) . '<span class="required text-accent-500">*</span></label>
                            <input id="author" name="author" type="text" class="w-full px-3 py-2 bg-dark-700 text-white rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 border border-dark-600" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" required />
                        </div>',
                        
            'email'  => '<div class="comment-form-email mb-4">
                            <label for="email" class="block text-gray-300 mb-2">' . esc_html__( 'Email', 'gufte' ) . '<span class="required text-accent-500">*</span></label>
                            <input id="email" name="email" type="email" class="w-full px-3 py-2 bg-dark-700 text-white rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 border border-dark-600" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30" required />
                        </div>',
                        
            'url'    => '<div class="comment-form-url mb-4">
                            <label for="url" class="block text-gray-300 mb-2">' . esc_html__( 'Website', 'gufte' ) . '</label>
                            <input id="url" name="url" type="url" class="w-full px-3 py-2 bg-dark-700 text-white rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 border border-dark-600" value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" />
                        </div>',
        ),
        
        'class_submit'        => 'submit',
        'logged_in_as'        => (function() {
            if (!is_user_logged_in()) {
                return '';
            }

            $current_user = wp_get_current_user();
            $avatar       = get_avatar($current_user->ID, 48, '', '', array('class' => 'w-12 h-12 rounded-full border border-dark-600 shadow-sm'));
            $display_name = esc_html($current_user->display_name ?: $current_user->user_login);
            $edit_link    = esc_url(get_edit_user_link());
            $logout_link  = esc_url(wp_logout_url(get_permalink()));

            ob_start();
            ?>
            <div class="logged-in-as flex items-center gap-3 mb-4 text-gray-300 bg-dark-700/80 border border-dark-600 rounded-lg p-3">
                <div class="logged-in-avatar">
                    <?php echo $avatar; ?>
                </div>
                <div class="logged-in-info text-sm leading-snug">
                    <div>
                        <a href="<?php echo $edit_link; ?>" class="text-primary-400 hover:text-primary-300 transition-colors duration-300 font-semibold">
                            <?php echo $display_name; ?>
                        </a>
                        <span class="text-gray-400 ml-1"><?php esc_html_e('is commenting.', 'gufte'); ?></span>
                    </div>
                    <div>
                        <a href="<?php echo $logout_link; ?>" class="text-primary-400 hover:text-primary-300 transition-colors duration-300">
                            <?php esc_html_e('Log Out', 'gufte'); ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php
            return ob_get_clean();
        })()
    );
    
    // Output comment form
    comment_form( $comment_form_args );
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.querySelector('.comment-form');
        if (!form) { return; }

        var commentField = form.querySelector('#comment');
        var warning = form.querySelector('#comment-spam-warning');
        var submitBtn = form.querySelector('.submit');

        if (!commentField || !warning || !submitBtn) { return; }

        var spamPatterns = [
            /https?:\/\//i,
            /www\./i,
            /\bsex\b/i,
            /\bporn\b/i,
            /\bcasino\b/i,
            /\bviagra\b/i,
            /\badult\b/i
        ];

        function toggleWarning(hasIssue) {
            if (hasIssue) {
                warning.style.display = 'block';
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.6';
                submitBtn.style.cursor = 'not-allowed';
            } else {
                warning.style.display = 'none';
                submitBtn.disabled = false;
                submitBtn.style.opacity = '';
                submitBtn.style.cursor = '';
            }
        }

        function checkContent() {
            var value = commentField.value || '';
            var hasIssue = spamPatterns.some(function (pattern) {
                return pattern.test(value);
            });
            toggleWarning(hasIssue);
        }

        commentField.addEventListener('input', checkContent);
        commentField.addEventListener('paste', function () {
            setTimeout(checkContent, 0);
        });

        form.addEventListener('submit', function (event) {
            if (submitBtn.disabled) {
                event.preventDefault();
                commentField.focus();
            }
        });
    });
    </script>
    <?php
    ?>
</div><!-- #comments -->
<?php
/**
 * Custom comment callback function to style comments according to theme
 */
if ( ! function_exists( 'gufte_comment_callback' ) ) :
    function gufte_comment_callback( $comment, $args, $depth ) {
        $tag = ( 'div' === $args['style'] ) ? 'div' : 'li';
        
        $commenter     = wp_get_current_commenter();
        $show_pending  = current_user_can( 'edit_comment', $comment->comment_ID );
        $comment_class = 'comment-body bg-dark-700 p-4 rounded-lg';
        
        if ( '0' == $comment->comment_approved && ! $show_pending ) {
            $comment_class .= ' comment-awaiting-moderation';
        }
        ?>
        <<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( $comment_class, $comment ); ?>>
            <div class="comment-meta mb-2">
                <div class="flex items-start">
                    <div class="comment-author vcard mr-4">
                        <?php
                        if ( 0 != $args['avatar_size'] ) {
                            echo get_avatar( $comment, $args['avatar_size'], '', '', array( 'class' => 'rounded-full border-2 border-primary-500' ) );
                        }
                        ?>
                    </div>
                    
                    <div class="comment-metadata">
                        <div class="flex flex-col md:flex-row md:items-center">
                            <h4 class="text-lg font-medium text-gray-100"><?php echo get_comment_author_link( $comment ); ?></h4>
                            
                            <span class="text-gray-400 text-sm md:ml-3">
                                <time datetime="<?php comment_time( 'c' ); ?>">
                                    <?php
                                    /* translators: 1: comment date, 2: comment time */
                                    printf( esc_html__( '%1$s, %2$s', 'gufte' ), get_comment_date( '', $comment ), get_comment_time() );
                                    ?>
                                </time>
                            </span>
                        </div>
                        
                        <?php
                        if ( '0' == $comment->comment_approved ) :
                            ?>
                            <em class="comment-awaiting-moderation text-yellow-500 text-sm block mt-1">
                                <?php esc_html_e( 'Your comment is awaiting moderation.', 'gufte' ); ?>
                            </em>
                            <?php
                        endif;
                        ?>
                    </div>
                </div>
            </div>
            <div class="comment-content text-gray-300 mt-3 border-l-4 border-dark-600 pl-4 ml-2">
                <?php comment_text(); ?>
            </div>
            <div class="reply mt-3 text-right">
                <?php
                comment_reply_link(
                    array_merge(
                        $args,
                        array(
                            'add_below' => 'comment',
                            'depth'     => $depth,
                            'max_depth' => $args['max_depth'],
                            'before'    => '<span class="reply-link inline-flex items-center text-primary-400 hover:text-primary-300 transition-colors duration-300 text-sm">',
                            'after'     => '</span>',
                        )
                    )
                );
                
                // Edit comment link for logged in users who can edit
                if ( current_user_can( 'edit_comment', $comment->comment_ID ) ) {
                    echo '<span class="edit-link ml-3">';
                    edit_comment_link( 
                        esc_html__( 'Edit', 'gufte' ), 
                        '<span class="inline-flex items-center text-primary-400 hover:text-primary-300 transition-colors duration-300 text-sm">', 
                        '</span>' 
                    );
                    echo '</span>';
                }
                ?>
            </div>
        <?php
    }
endif;
?>
