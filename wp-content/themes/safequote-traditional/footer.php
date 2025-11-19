    </main><!-- #primary -->

    <footer id="colophon" role="contentinfo" class="site-footer bg-gray-900 text-gray-200 py-12">
        <div class="container mx-auto px-4">
            <!-- Footer Widget Areas -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
                <?php
                // Display footer widget areas if registered
                if (is_active_sidebar('footer-1')) {
                    echo '<div class="footer-widget-area">';
                    dynamic_sidebar('footer-1');
                    echo '</div>';
                }
                if (is_active_sidebar('footer-2')) {
                    echo '<div class="footer-widget-area">';
                    dynamic_sidebar('footer-2');
                    echo '</div>';
                }
                if (is_active_sidebar('footer-3')) {
                    echo '<div class="footer-widget-area">';
                    dynamic_sidebar('footer-3');
                    echo '</div>';
                }
                if (is_active_sidebar('footer-4')) {
                    echo '<div class="footer-widget-area">';
                    dynamic_sidebar('footer-4');
                    echo '</div>';
                }
                ?>
            </div>

            <!-- Footer Menu and Copyright -->
            <div class="border-t border-gray-700 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <!-- Footer Menu -->
                    <nav role="navigation" aria-label="<?php esc_attr_e('Footer', 'safequote-traditional'); ?>" class="mb-4 md:mb-0">
                        <?php
                        wp_nav_menu(array(
                            'theme_location' => 'footer',
                            'fallback_cb'    => 'wp_page_menu',
                            'depth'          => 1,
                            'menu_class'     => 'flex gap-6',
                            'link_before'    => '<span class="text-gray-400 hover:text-white transition-colors">',
                            'link_after'     => '</span>',
                        ));
                        ?>
                    </nav>

                    <!-- Copyright and Social Links -->
                    <div class="flex flex-col md:flex-row items-center gap-4">
                        <!-- Copyright Text -->
                        <p class="text-sm text-gray-400">
                            <?php
                            $copyright_text = get_theme_mod('footer_copyright_text');
                            if (!empty($copyright_text)) {
                                echo wp_kses_post($copyright_text);
                            } else {
                                printf(
                                    '&copy; %s %s. ' . esc_html__('All rights reserved.', 'safequote-traditional'),
                                    esc_html(date('Y')),
                                    esc_html(get_bloginfo('name'))
                                );
                            }
                            ?>
                        </p>

                        <!-- Social Media Links -->
                        <div class="flex gap-4" role="navigation" aria-label="<?php esc_attr_e('Social media links', 'safequote-traditional'); ?>">
                            <?php
                            $social_links = array(
                                'facebook_url'  => 'Facebook',
                                'twitter_url'   => 'Twitter',
                                'instagram_url' => 'Instagram',
                                'linkedin_url'  => 'LinkedIn',
                                'youtube_url'   => 'YouTube',
                            );

                            foreach ($social_links as $setting => $label) {
                                $url = get_theme_mod($setting);
                                if (!empty($url)) {
                                    printf(
                                        '<a href="%s" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white transition-colors" aria-label="%s">%s</a>',
                                        esc_url($url),
                                        esc_attr($label),
                                        esc_html($label)
                                    );
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer><!-- #colophon -->

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
