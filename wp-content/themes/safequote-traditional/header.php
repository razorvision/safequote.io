<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">

    <?php wp_head(); ?>
</head>

<body <?php body_class('bg-gray-50'); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site min-h-screen">
    <a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('Skip to content', 'safequote-traditional'); ?></a>

    <!-- Header - EXACT MATCH to React Header.jsx -->
    <header id="masthead" class="site-header bg-white/80 backdrop-blur-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <!-- Logo/Brand - EXACT MATCH -->
                <a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center gap-3">
                    <div class="bg-gradient-to-br from-primary to-teal-500 p-2 rounded-lg">
                        <!-- Shield Icon (matching React's lucide-react Shield) -->
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">
                            <span class="bg-gradient-to-r from-primary to-teal-600 bg-clip-text text-transparent">safequote</span><span class="text-teal-600">.io</span>
                        </h1>
                        <p class="text-xs text-gray-600 -mt-1"><?php esc_html_e('Peace of mind one search at a time', 'safequote-traditional'); ?></p>
                    </div>
                </a>

                <div class="flex items-center gap-2 md:gap-4">
                    <!-- Desktop Navigation - EXACT MATCH -->
                    <nav class="hidden md:flex items-center gap-6">
                        <a href="/?flow=findCar" class="text-gray-700 hover:text-primary transition-colors font-medium cursor-pointer">
                            <?php esc_html_e('Vehicles', 'safequote-traditional'); ?>
                        </a>
                        <a href="/?flow=getInsurance" class="text-gray-700 hover:text-primary transition-colors font-medium cursor-pointer">
                            <?php esc_html_e('Insurance', 'safequote-traditional'); ?>
                        </a>
                        <a href="/safequote-safety-ratings" class="text-gray-700 hover:text-primary transition-colors font-medium cursor-pointer flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <?php esc_html_e('Safety Ratings', 'safequote-traditional'); ?>
                        </a>
                        <a href="#" id="dashboard-link" class="flex items-center text-gray-700 hover:text-primary transition-colors font-medium cursor-pointer">
                            <!-- LayoutDashboard Icon (matching React's lucide-react LayoutDashboard) -->
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <rect x="3" y="3" width="7" height="7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></rect>
                                <rect x="14" y="3" width="7" height="7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></rect>
                                <rect x="14" y="14" width="7" height="7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></rect>
                                <rect x="3" y="14" width="7" height="7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></rect>
                            </svg>
                            <?php esc_html_e('Dashboard', 'safequote-traditional'); ?>
                        </a>
                    </nav>

                    <!-- Login Button -->
                    <button id="login-button" class="hidden md:flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <?php esc_html_e('Login', 'safequote-traditional'); ?>
                    </button>

                    <!-- Mobile Menu Button -->
                    <button id="mobile-menu-button" class="md:hidden p-2 text-gray-700 hover:bg-gray-100 rounded-md transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu (hidden by default) -->
        <div class="md:hidden hidden" id="mobile-menu">
            <div class="px-4 py-2 space-y-1 bg-white border-t border-gray-200">
                <a href="/?flow=findCar" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-md font-medium">
                    <?php esc_html_e('Vehicles', 'safequote-traditional'); ?>
                </a>
                <a href="/?flow=getInsurance" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-md font-medium">
                    <?php esc_html_e('Insurance', 'safequote-traditional'); ?>
                </a>
                <a href="/safequote-safety-ratings" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-md font-medium">
                    <?php esc_html_e('Safety Ratings', 'safequote-traditional'); ?>
                </a>
                <a href="#" class="block px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-md font-medium">
                    <?php esc_html_e('Dashboard', 'safequote-traditional'); ?>
                </a>
                <button class="w-full text-left px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-md font-medium">
                    <?php esc_html_e('Login', 'safequote-traditional'); ?>
                </button>
            </div>
        </div>
    </header>

    <div id="content" class="site-content">