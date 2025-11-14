    </div><!-- #content -->

    <footer id="colophon" class="site-footer bg-gray-900 text-gray-300">
        <?php if (is_active_sidebar('footer-1') || is_active_sidebar('footer-2') || is_active_sidebar('footer-3') || is_active_sidebar('footer-4')) : ?>
        <div class="footer-widgets py-12 border-b border-gray-800">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <?php if (is_active_sidebar('footer-1')) : ?>
                        <div class="footer-widget-area">
                            <?php dynamic_sidebar('footer-1'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (is_active_sidebar('footer-2')) : ?>
                        <div class="footer-widget-area">
                            <?php dynamic_sidebar('footer-2'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (is_active_sidebar('footer-3')) : ?>
                        <div class="footer-widget-area">
                            <?php dynamic_sidebar('footer-3'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (is_active_sidebar('footer-4')) : ?>
                        <div class="footer-widget-area">
                            <?php dynamic_sidebar('footer-4'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="footer-main py-16 border-b border-gray-800">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <!-- Company Info -->
                    <div class="space-y-4">
                        <h3 class="text-white text-lg font-semibold mb-4"><?php bloginfo('name'); ?></h3>
                        <p class="text-sm">
                            <?php bloginfo('description'); ?>
                        </p>
                        <div class="flex space-x-4">
                            <a href="#" class="text-gray-400 hover:text-white transition-colors">
                                <span class="sr-only">Facebook</span>
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/>
                                </svg>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-white transition-colors">
                                <span class="sr-only">Twitter</span>
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"/>
                                </svg>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-white transition-colors">
                                <span class="sr-only">LinkedIn</span>
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h3 class="text-white text-lg font-semibold mb-4"><?php esc_html_e('Quick Links', 'safequote-traditional'); ?></h3>
                        <?php
                        wp_nav_menu(array(
                            'theme_location' => 'footer',
                            'menu_class'     => 'space-y-2',
                            'container'      => false,
                            'depth'          => 1,
                            'fallback_cb'    => false,
                            'items_wrap'     => '<ul class="%2$s">%3$s</ul>',
                            'link_before'    => '<span class="text-sm hover:text-white transition-colors">',
                            'link_after'     => '</span>',
                        ));
                        ?>
                    </div>

                    <!-- Services -->
                    <div>
                        <h3 class="text-white text-lg font-semibold mb-4"><?php esc_html_e('Services', 'safequote-traditional'); ?></h3>
                        <ul class="space-y-2">
                            <li><a href="#" class="text-sm hover:text-white transition-colors"><?php esc_html_e('Insurance Quotes', 'safequote-traditional'); ?></a></li>
                            <li><a href="#" class="text-sm hover:text-white transition-colors"><?php esc_html_e('Vehicle Safety Ratings', 'safequote-traditional'); ?></a></li>
                            <li><a href="#" class="text-sm hover:text-white transition-colors"><?php esc_html_e('Comparison Tool', 'safequote-traditional'); ?></a></li>
                            <li><a href="#" class="text-sm hover:text-white transition-colors"><?php esc_html_e('Risk Assessment', 'safequote-traditional'); ?></a></li>
                            <li><a href="#" class="text-sm hover:text-white transition-colors"><?php esc_html_e('Expert Advice', 'safequote-traditional'); ?></a></li>
                        </ul>
                    </div>

                    <!-- Contact -->
                    <div>
                        <h3 class="text-white text-lg font-semibold mb-4"><?php esc_html_e('Contact', 'safequote-traditional'); ?></h3>
                        <ul class="space-y-2">
                            <li class="flex items-start">
                                <svg class="h-5 w-5 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="text-sm">123 Insurance Ave, Suite 100<br>New York, NY 10001</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span class="text-sm">1-800-SAF-QUOT</span>
                            </li>
                            <li class="flex items-center">
                                <svg class="h-5 w-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-sm">info@safequote.io</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom py-6">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="text-sm text-center md:text-left mb-4 md:mb-0">
                        &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>.
                        <?php esc_html_e('All rights reserved.', 'safequote-traditional'); ?>
                    </div>
                    <div class="flex space-x-6 text-sm">
                        <a href="#" class="hover:text-white transition-colors"><?php esc_html_e('Privacy Policy', 'safequote-traditional'); ?></a>
                        <a href="#" class="hover:text-white transition-colors"><?php esc_html_e('Terms of Service', 'safequote-traditional'); ?></a>
                        <a href="#" class="hover:text-white transition-colors"><?php esc_html_e('Cookie Policy', 'safequote-traditional'); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>