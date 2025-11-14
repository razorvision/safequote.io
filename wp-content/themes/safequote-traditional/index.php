<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 *
 * @package SafeQuote_Traditional
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content Area -->
            <div class="lg:col-span-2">
                <?php
                if (have_posts()) :

                    if (is_home() && !is_front_page()) :
                        ?>
                        <header>
                            <h1 class="page-title text-3xl font-bold mb-8">
                                <?php single_post_title(); ?>
                            </h1>
                        </header>
                        <?php
                    endif;

                    /* Start the Loop */
                    while (have_posts()) :
                        the_post();
                        ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class('bg-white rounded-lg shadow-md mb-6 overflow-hidden'); ?>>
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="post-thumbnail">
                                    <a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
                                        <?php
                                        the_post_thumbnail('large', array(
                                            'class' => 'w-full h-64 object-cover',
                                            'alt' => the_title_attribute(array('echo' => false))
                                        ));
                                        ?>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <div class="p-6">
                                <header class="entry-header mb-4">
                                    <?php
                                    if (is_singular()) :
                                        the_title('<h1 class="entry-title text-2xl font-bold text-gray-900">', '</h1>');
                                    else :
                                        the_title('<h2 class="entry-title text-2xl font-bold text-gray-900"><a href="' . esc_url(get_permalink()) . '" rel="bookmark" class="hover:text-blue-600 transition-colors">', '</a></h2>');
                                    endif;
                                    ?>

                                    <?php if ('post' === get_post_type()) : ?>
                                        <div class="entry-meta mt-2 text-sm text-gray-600">
                                            <span class="posted-on">
                                                <time class="entry-date published" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                                    <?php echo esc_html(get_the_date()); ?>
                                                </time>
                                            </span>
                                            <span class="mx-2">•</span>
                                            <span class="byline">
                                                <?php
                                                printf(
                                                    esc_html__('by %s', 'safequote-traditional'),
                                                    '<span class="author vcard"><a class="url fn n hover:text-blue-600" href="' . esc_url(get_author_posts_url(get_the_author_meta('ID'))) . '">' . esc_html(get_the_author()) . '</a></span>'
                                                );
                                                ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </header>

                                <div class="entry-content prose prose-lg max-w-none">
                                    <?php
                                    if (is_singular()) :
                                        the_content();

                                        wp_link_pages(array(
                                            'before' => '<div class="page-links mt-4">' . esc_html__('Pages:', 'safequote-traditional'),
                                            'after'  => '</div>',
                                        ));
                                    else :
                                        the_excerpt();
                                        ?>
                                        <a href="<?php the_permalink(); ?>" class="inline-block mt-4 px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                                            <?php esc_html_e('Read More', 'safequote-traditional'); ?>
                                        </a>
                                        <?php
                                    endif;
                                    ?>
                                </div>

                                <?php if (is_singular() && has_category()) : ?>
                                    <footer class="entry-footer mt-6 pt-6 border-t">
                                        <div class="cat-links text-sm">
                                            <?php esc_html_e('Categories: ', 'safequote-traditional'); ?>
                                            <?php the_category(', '); ?>
                                        </div>
                                        <?php if (has_tag()) : ?>
                                            <div class="tag-links text-sm mt-2">
                                                <?php esc_html_e('Tags: ', 'safequote-traditional'); ?>
                                                <?php the_tags('', ', ', ''); ?>
                                            </div>
                                        <?php endif; ?>
                                    </footer>
                                <?php endif; ?>
                            </div>
                        </article>
                        <?php

                        // If comments are open or we have at least one comment, load up the comment template.
                        if (is_singular() && (comments_open() || get_comments_number())) :
                            comments_template();
                        endif;

                    endwhile;

                    // Pagination
                    ?>
                    <nav class="navigation pagination mt-8" role="navigation" aria-label="<?php esc_attr_e('Posts navigation', 'safequote-traditional'); ?>">
                        <div class="nav-links flex justify-center space-x-2">
                            <?php
                            echo paginate_links(array(
                                'prev_text' => '<span class="screen-reader-text">' . __('Previous', 'safequote-traditional') . '</span>&laquo;',
                                'next_text' => '<span class="screen-reader-text">' . __('Next', 'safequote-traditional') . '</span>&raquo;',
                                'before_page_number' => '<span class="meta-nav screen-reader-text">' . __('Page', 'safequote-traditional') . ' </span>',
                            ));
                            ?>
                        </div>
                    </nav>
                    <?php

                else :
                    ?>
                    <section class="no-results not-found bg-white rounded-lg shadow-md p-8">
                        <header class="page-header mb-4">
                            <h1 class="page-title text-2xl font-bold"><?php esc_html_e('Nothing Found', 'safequote-traditional'); ?></h1>
                        </header>

                        <div class="page-content">
                            <?php
                            if (is_home() && current_user_can('publish_posts')) :

                                printf(
                                    '<p>' . wp_kses(
                                        __('Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'safequote-traditional'),
                                        array(
                                            'a' => array(
                                                'href' => array(),
                                            ),
                                        )
                                    ) . '</p>',
                                    esc_url(admin_url('post-new.php'))
                                );

                            elseif (is_search()) :
                                ?>
                                <p><?php esc_html_e('Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'safequote-traditional'); ?></p>
                                <?php
                                get_search_form();

                            else :
                                ?>
                                <p><?php esc_html_e('It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'safequote-traditional'); ?></p>
                                <?php
                                get_search_form();

                            endif;
                            ?>
                        </div>
                    </section>
                    <?php
                endif;
                ?>
            </div>

            <!-- Sidebar -->
            <aside id="secondary" class="widget-area lg:col-span-1">
                <?php if (is_active_sidebar('sidebar-1')) : ?>
                    <?php dynamic_sidebar('sidebar-1'); ?>
                <?php else : ?>
                    <!-- Default sidebar content if no widgets -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h3 class="text-lg font-semibold mb-4"><?php esc_html_e('Recent Posts', 'safequote-traditional'); ?></h3>
                        <ul class="space-y-2">
                            <?php
                            $recent_posts = wp_get_recent_posts(array(
                                'numberposts' => 5,
                                'post_status' => 'publish',
                            ));
                            foreach ($recent_posts as $post) :
                                ?>
                                <li>
                                    <a href="<?php echo get_permalink($post['ID']); ?>" class="text-blue-600 hover:text-blue-800">
                                        <?php echo $post['post_title']; ?>
                                    </a>
                                </li>
                                <?php
                            endforeach;
                            wp_reset_query();
                            ?>
                        </ul>
                    </div>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold mb-4"><?php esc_html_e('Categories', 'safequote-traditional'); ?></h3>
                        <ul class="space-y-2">
                            <?php
                            wp_list_categories(array(
                                'orderby' => 'name',
                                'title_li' => '',
                                'show_count' => true,
                            ));
                            ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</main><!-- #primary -->

<?php
get_footer();