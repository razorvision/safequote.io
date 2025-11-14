<?php
/**
 * The template for displaying all pages
 *
 * @package SafeQuote_Traditional
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="container mx-auto px-4 py-8">
        <?php
        while (have_posts()) :
            the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('bg-white rounded-lg shadow-md overflow-hidden'); ?>>
                <?php if (has_post_thumbnail()) : ?>
                    <div class="post-thumbnail">
                        <?php
                        the_post_thumbnail('full', array(
                            'class' => 'w-full h-64 md:h-96 object-cover',
                            'alt' => the_title_attribute(array('echo' => false))
                        ));
                        ?>
                    </div>
                <?php endif; ?>

                <div class="p-6 md:p-12">
                    <header class="entry-header mb-8">
                        <?php the_title('<h1 class="entry-title text-3xl md:text-4xl font-bold text-gray-900">', '</h1>'); ?>
                    </header>

                    <div class="entry-content prose prose-lg max-w-none">
                        <?php
                        the_content();

                        wp_link_pages(array(
                            'before' => '<div class="page-links mt-8">' . esc_html__('Pages:', 'safequote-traditional'),
                            'after'  => '</div>',
                            'link_before' => '<span class="inline-block px-3 py-1 mx-1 bg-secondary text-primary rounded">',
                            'link_after'  => '</span>',
                        ));
                        ?>
                    </div>

                    <?php if (get_edit_post_link()) : ?>
                        <footer class="entry-footer mt-8 pt-8 border-t">
                            <?php
                            edit_post_link(
                                sprintf(
                                    wp_kses(
                                        __('Edit <span class="screen-reader-text">%s</span>', 'safequote-traditional'),
                                        array(
                                            'span' => array(
                                                'class' => array(),
                                            ),
                                        )
                                    ),
                                    wp_kses_post(get_the_title())
                                ),
                                '<span class="edit-link inline-block px-4 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors">',
                                '</span>'
                            );
                            ?>
                        </footer>
                    <?php endif; ?>
                </div>
            </article>

            <?php
            // If comments are open or we have at least one comment, load up the comment template.
            if (comments_open() || get_comments_number()) :
                ?>
                <div class="mt-8 bg-white rounded-lg shadow-md p-6 md:p-12">
                    <?php comments_template(); ?>
                </div>
                <?php
            endif;

        endwhile;
        ?>
    </div>
</main><!-- #primary -->

<?php
get_footer();