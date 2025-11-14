<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('Skip to content', 'safequote-traditional'); ?></a>

    <header id="masthead" class="site-header bg-white shadow-sm">
        <nav class="navbar bg-gradient-to-r from-blue-600 to-blue-700">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo/Brand -->
                    <div class="flex items-center">
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center space-x-3">
                            <?php if (has_custom_logo()) : ?>
                                <?php the_custom_logo(); ?>
                            <?php else : ?>
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                    </path>
                                </svg>
                                <span class="text-xl font-bold text-white"><?php bloginfo('name'); ?></span>
                            <?php endif; ?>
                        </a>
                    </div>

                    <!-- Desktop Navigation -->
                    <div class="hidden md:block">
                        <?php
                        wp_nav_menu(array(
                            'theme_location' => 'primary',
                            'menu_id'        => 'primary-menu',
                            'menu_class'     => 'flex items-center space-x-1',
                            'container'      => false,
                            'fallback_cb'    => false,
                            'walker'         => new Safequote_Walker_Nav_Menu(),
                            'depth'          => 2,
                        ));
                        ?>
                    </div>

                    <!-- Mobile menu button -->
                    <div class="md:hidden">
                        <button id="mobile-menu-button" type="button"
                                class="inline-flex items-center justify-center p-2 rounded-md text-white hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                                aria-controls="mobile-menu" aria-expanded="false">
                            <span class="sr-only"><?php esc_html_e('Open main menu', 'safequote-traditional'); ?></span>
                            <!-- Menu icon -->
                            <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <!-- Close icon -->
                            <svg class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div class="md:hidden hidden" id="mobile-menu">
                <div class="px-2 pt-2 pb-3 space-y-1 bg-blue-700">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'menu_id'        => 'mobile-primary-menu',
                        'menu_class'     => 'mobile-menu-items',
                        'container'      => false,
                        'fallback_cb'    => false,
                        'walker'         => new Safequote_Mobile_Walker_Nav_Menu(),
                        'depth'          => 2,
                    ));
                    ?>
                </div>
            </div>
        </nav>

        <!-- Secondary Navigation (if needed) -->
        <?php if (has_nav_menu('secondary')) : ?>
        <nav class="secondary-navigation bg-gray-50 border-b">
            <div class="container mx-auto px-4">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'secondary',
                    'menu_id'        => 'secondary-menu',
                    'menu_class'     => 'flex items-center space-x-6 py-3 text-sm',
                    'container'      => false,
                    'depth'          => 1,
                ));
                ?>
            </div>
        </nav>
        <?php endif; ?>
    </header><!-- #masthead -->

    <div id="content" class="site-content">