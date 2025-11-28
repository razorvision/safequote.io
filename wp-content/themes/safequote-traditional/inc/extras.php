<?php
/**
 * Custom functions that act independently of the theme templates
 *
 * @package SafeQuote_Traditional
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add custom classes to the array of body classes
 */
function safequote_body_classes_extras($classes) {
    // Add page slug if it doesn't exist
    if (is_single() || is_page() && !is_front_page()) {
        if (!in_array(basename(get_permalink()), $classes)) {
            $classes[] = basename(get_permalink());
        }
    }

    // Add class if we're viewing the Customizer
    if (is_customize_preview()) {
        $classes[] = 'is-customizer-preview';
    }

    // Add class for no sidebar
    if (!is_active_sidebar('sidebar-1')) {
        $classes[] = 'no-sidebar';
    }

    // Add class for transparent header pages
    if (is_front_page() || is_page_template('page-hero.php')) {
        $classes[] = 'has-transparent-header';
    }

    return $classes;
}
add_filter('body_class', 'safequote_body_classes_extras');

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments
 */
function safequote_pingback_header() {
    if (is_singular() && pings_open()) {
        printf('<link rel="pingback" href="%s">', esc_url(get_bloginfo('pingback_url')));
    }
}
add_action('wp_head', 'safequote_pingback_header');

/**
 * Custom excerpt length
 */
function safequote_custom_excerpt_length($length) {
    if (is_home() || is_archive()) {
        return 30;
    }
    return $length;
}
add_filter('excerpt_length', 'safequote_custom_excerpt_length', 999);

/**
 * Custom excerpt more link
 */
function safequote_excerpt_more_link($more) {
    if (!is_admin()) {
        global $post;
        return '... <a class="read-more-link" href="' . esc_url(get_permalink($post->ID)) . '">' . __('Read More', 'safequote-traditional') . '</a>';
    }
    return $more;
}
add_filter('excerpt_more', 'safequote_excerpt_more_link');

/**
 * Add custom image sizes
 */
function safequote_add_image_sizes() {
    add_image_size('vehicle-thumb', 400, 300, true);
    add_image_size('vehicle-full', 800, 600, true);
    add_image_size('hero-banner', 1920, 800, true);
    add_image_size('blog-featured', 800, 450, true);
}
add_action('after_setup_theme', 'safequote_add_image_sizes');

/**
 * Register custom image size names
 */
function safequote_custom_image_size_names($sizes) {
    return array_merge($sizes, array(
        'vehicle-thumb' => __('Vehicle Thumbnail', 'safequote-traditional'),
        'vehicle-full' => __('Vehicle Full', 'safequote-traditional'),
        'hero-banner' => __('Hero Banner', 'safequote-traditional'),
        'blog-featured' => __('Blog Featured', 'safequote-traditional'),
    ));
}
add_filter('image_size_names_choose', 'safequote_custom_image_size_names');

/**
 * Add custom login logo
 */
function safequote_login_logo() {
    $logo_url = get_theme_mod('custom_logo');
    if ($logo_url) {
        $logo_id = attachment_url_to_postid($logo_url);
        $logo_data = wp_get_attachment_image_src($logo_id, 'medium');
        ?>
        <style type="text/css">
            #login h1 a, .login h1 a {
                background-image: url(<?php echo esc_url($logo_data[0]); ?>);
                height: 100px;
                width: 320px;
                background-size: contain;
                background-repeat: no-repeat;
                padding-bottom: 30px;
            }
        </style>
        <?php
    }
}
add_action('login_enqueue_scripts', 'safequote_login_logo');

/**
 * Change login logo URL
 */
function safequote_login_logo_url() {
    return home_url();
}
add_filter('login_headerurl', 'safequote_login_logo_url');

/**
 * Change login logo URL title
 */
function safequote_login_logo_url_title() {
    return get_bloginfo('name');
}
add_filter('login_headertext', 'safequote_login_logo_url_title');

/**
 * Disable WordPress emoji scripts
 */
function safequote_disable_emojis() {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    add_filter('tiny_mce_plugins', 'safequote_disable_emojis_tinymce');
    add_filter('wp_resource_hints', 'safequote_disable_emojis_remove_dns_prefetch', 10, 2);
}
add_action('init', 'safequote_disable_emojis');

/**
 * Filter function to remove the tinymce emoji plugin
 */
function safequote_disable_emojis_tinymce($plugins) {
    if (is_array($plugins)) {
        return array_diff($plugins, array('wpemoji'));
    } else {
        return array();
    }
}

/**
 * Remove emoji CDN hostname from DNS prefetching hints
 */
function safequote_disable_emojis_remove_dns_prefetch($urls, $relation_type) {
    if ('dns-prefetch' == $relation_type) {
        $emoji_svg_url = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/');
        $urls = array_diff($urls, array($emoji_svg_url));
    }
    return $urls;
}

/**
 * Clean up WordPress head
 */
function safequote_head_cleanup() {
    // Remove the links to the extra feeds
    remove_action('wp_head', 'feed_links_extra', 3);
    // Remove the links to the general feeds
    remove_action('wp_head', 'feed_links', 2);
    // Remove the link to the Really Simple Discovery service endpoint
    remove_action('wp_head', 'rsd_link');
    // Remove the link to the Windows Live Writer manifest file
    remove_action('wp_head', 'wlwmanifest_link');
    // Remove index link
    remove_action('wp_head', 'index_rel_link');
    // Remove previous link
    remove_action('wp_head', 'parent_post_rel_link', 10, 0);
    // Remove start link
    remove_action('wp_head', 'start_post_rel_link', 10, 0);
    // Remove relational links for the posts adjacent to the current post
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
    // Remove the WordPress version
    remove_action('wp_head', 'wp_generator');
}
add_action('init', 'safequote_head_cleanup');

/**
 * Remove WordPress version from RSS feeds
 */
function safequote_remove_wp_version_rss() {
    return '';
}
add_filter('the_generator', 'safequote_remove_wp_version_rss');

/**
 * Add page slug to body class
 */
function safequote_add_slug_to_body_class($classes) {
    global $post;
    if (is_home()) {
        $key = array_search('blog', $classes);
        if ($key > -1) {
            unset($classes[$key]);
        }
    } elseif (is_page()) {
        $classes[] = sanitize_html_class($post->post_name);
    } elseif (is_singular()) {
        $classes[] = sanitize_html_class($post->post_name);
    }
    return $classes;
}
add_filter('body_class', 'safequote_add_slug_to_body_class');

/**
 * Custom breadcrumbs
 */
function safequote_breadcrumbs() {
    if (is_front_page()) {
        return;
    }

    echo '<nav class="breadcrumbs text-sm text-gray-600 mb-4">';
    echo '<a href="' . home_url() . '" class="hover:text-blue-600">' . __('Home', 'safequote-traditional') . '</a>';

    if (is_category() || is_single()) {
        echo ' <span class="mx-2">/</span> ';
        the_category(' <span class="mx-2">/</span> ');
        if (is_single()) {
            echo ' <span class="mx-2">/</span> ';
            the_title();
        }
    } elseif (is_page()) {
        if ($post->post_parent) {
            $parent_id = $post->post_parent;
            $breadcrumbs = array();
            while ($parent_id) {
                $page = get_page($parent_id);
                $breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '" class="hover:text-blue-600">' . get_the_title($page->ID) . '</a>';
                $parent_id = $page->post_parent;
            }
            $breadcrumbs = array_reverse($breadcrumbs);
            foreach ($breadcrumbs as $crumb) {
                echo ' <span class="mx-2">/</span> ' . $crumb;
            }
        }
        echo ' <span class="mx-2">/</span> ';
        echo the_title();
    } elseif (is_search()) {
        echo ' <span class="mx-2">/</span> ';
        echo __('Search Results', 'safequote-traditional');
    }

    echo '</nav>';
}

/**
 * Limit search to posts only
 */
function safequote_search_filter($query) {
    if (!is_admin() && $query->is_main_query()) {
        if ($query->is_search()) {
            $query->set('post_type', array('post', 'page'));
        }
    }
}
add_action('pre_get_posts', 'safequote_search_filter');

/**
 * Custom pagination
 */
function safequote_pagination() {
    global $wp_query;

    $big = 999999999;
    $pagination = paginate_links(array(
        'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
        'format' => '?paged=%#%',
        'current' => max(1, get_query_var('paged')),
        'total' => $wp_query->max_num_pages,
        'type' => 'array',
        'prev_text' => '&laquo;',
        'next_text' => '&raquo;',
    ));

    if (!empty($pagination)) {
        echo '<nav class="pagination flex justify-center space-x-2 mt-8">';
        foreach ($pagination as $page) {
            echo $page;
        }
        echo '</nav>';
    }
}