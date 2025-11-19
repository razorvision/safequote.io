<?php
/**
 * SEO Features for SafeQuote Theme
 *
 * Implements Open Graph meta tags, Twitter Cards, and structured data
 * for improved social sharing and search engine optimization.
 *
 * @package SafeQuote
 * @subpackage SEO
 */

// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Add Open Graph and Twitter Card meta tags
 *
 * Only adds tags if Yoast SEO or Rank Math is not active
 * (to avoid conflicts with dedicated SEO plugins)
 *
 * @since 1.0.0
 */
function safequote_add_meta_tags() {
  // Don't add if Yoast SEO is active
  if (defined('WPSEO_VERSION')) {
    return;
  }

  // Don't add if Rank Math is active
  if (defined('RANK_MATH_VERSION')) {
    return;
  }

  // Get current post/page
  $post_id = get_queried_object_id();
  $post = get_post($post_id);

  if (!$post) {
    return;
  }

  // Prepare meta data
  $title = get_the_title($post);
  $description = safequote_get_meta_description($post);
  $url = get_permalink($post);
  $site_name = get_bloginfo('name');

  // Get featured image
  $image_url = '';
  if (has_post_thumbnail($post->ID)) {
    $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
    $image_url = $image[0];
  } else {
    // Fallback to site logo if no featured image
    $custom_logo_id = get_theme_mod('custom_logo');
    if ($custom_logo_id) {
      $image = wp_get_attachment_image_src($custom_logo_id, 'large');
      $image_url = $image[0];
    }
  }

  // Determine post type for og:type
  $og_type = 'website';
  if ($post->post_type === 'post') {
    $og_type = 'article';
  } elseif ($post->post_type === 'vehicle') {
    $og_type = 'product';
  }

  // Output Open Graph meta tags
  echo "<!-- Open Graph Meta Tags -->\n";
  echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
  echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
  echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
  echo '<meta property="og:site_name" content="' . esc_attr($site_name) . '">' . "\n";
  echo '<meta property="og:type" content="' . esc_attr($og_type) . '">' . "\n";

  if ($image_url) {
    echo '<meta property="og:image" content="' . esc_url($image_url) . '">' . "\n";
    echo '<meta property="og:image:width" content="1200">' . "\n";
    echo '<meta property="og:image:height" content="630">' . "\n";
  }

  // Output Twitter Card meta tags
  echo "<!-- Twitter Card Meta Tags -->\n";
  echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
  echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
  echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";

  if ($image_url) {
    echo '<meta name="twitter:image" content="' . esc_url($image_url) . '">' . "\n";
  }

  // Output canonical URL
  echo "<!-- Canonical URL -->\n";
  echo '<link rel="canonical" href="' . esc_url($url) . '">' . "\n";
}
add_action('wp_head', 'safequote_add_meta_tags', 5);

/**
 * Get meta description for a post
 *
 * Uses custom meta field if available, otherwise falls back to excerpt
 *
 * @param WP_Post $post The post object
 * @return string Meta description (truncated to 160 characters)
 * @since 1.0.0
 */
function safequote_get_meta_description($post) {
  // Check for custom meta description field
  $custom_description = get_post_meta($post->ID, '_meta_description', true);
  if (!empty($custom_description)) {
    return wp_trim_words($custom_description, 160);
  }

  // Fall back to excerpt
  if (!empty($post->post_excerpt)) {
    return wp_trim_words($post->post_excerpt, 160);
  }

  // Fall back to post content excerpt
  $excerpt = wp_trim_words($post->post_content, 20);
  return $excerpt;
}

/**
 * Add Schema.org Organization structured data (JSON-LD)
 *
 * Outputs on homepage and archives with organization information
 * for SEO and knowledge graph integration
 *
 * @since 1.0.0
 */
function safequote_add_organization_schema() {
  // Don't add if Yoast SEO or Rank Math is active
  if (defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION')) {
    return;
  }

  if (!is_front_page() && !is_archive()) {
    return;
  }

  $organization = array(
    '@context'      => 'https://schema.org',
    '@type'         => 'Organization',
    'name'          => get_bloginfo('name'),
    'description'   => get_bloginfo('description'),
    'url'           => home_url(),
    'logo'          => safequote_get_schema_logo(),
  );

  // Add social profiles if customizer settings exist
  $social_profiles = safequote_get_social_profiles();
  if (!empty($social_profiles)) {
    $organization['sameAs'] = $social_profiles;
  }

  echo "<script type=\"application/ld+json\">\n";
  echo wp_json_encode($organization, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
  echo "\n</script>\n";
}
add_action('wp_head', 'safequote_add_organization_schema', 5);

/**
 * Add Schema.org WebSite structured data (JSON-LD)
 *
 * Adds website schema with search URL for search engine integration
 *
 * @since 1.0.0
 */
function safequote_add_website_schema() {
  // Don't add if Yoast SEO or Rank Math is active
  if (defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION')) {
    return;
  }

  if (!is_front_page()) {
    return;
  }

  $website = array(
    '@context'      => 'https://schema.org',
    '@type'         => 'WebSite',
    'name'          => get_bloginfo('name'),
    'description'   => get_bloginfo('description'),
    'url'           => home_url(),
    'potentialAction' => array(
      '@type'       => 'SearchAction',
      'target'      => array(
        '@type'     => 'EntryPoint',
        'urlTemplate' => home_url() . '/?s={search_term_string}',
      ),
      'query-input' => 'required name=search_term_string',
    ),
  );

  echo "<script type=\"application/ld+json\">\n";
  echo wp_json_encode($website, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
  echo "\n</script>\n";
}
add_action('wp_head', 'safequote_add_website_schema', 5);

/**
 * Add Schema.org BreadcrumbList structured data (JSON-LD)
 *
 * Adds breadcrumb schema for improved navigation in search results
 *
 * @since 1.0.0
 */
function safequote_add_breadcrumb_schema() {
  // Don't add if Yoast SEO or Rank Math is active
  if (defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION')) {
    return;
  }

  // Don't add on homepage
  if (is_front_page()) {
    return;
  }

  $breadcrumbs = array(
    '@context'  => 'https://schema.org',
    '@type'     => 'BreadcrumbList',
    'itemListElement' => array(),
  );

  $position = 1;

  // Home link
  $breadcrumbs['itemListElement'][] = array(
    '@type'     => 'ListItem',
    'position'  => $position,
    'name'      => 'Home',
    'item'      => home_url(),
  );
  $position++;

  // Category/Archive breadcrumbs
  if (is_category()) {
    $category = get_queried_object();
    $breadcrumbs['itemListElement'][] = array(
      '@type'     => 'ListItem',
      'position'  => $position,
      'name'      => $category->name,
      'item'      => get_category_link($category->term_id),
    );
  } elseif (is_tax()) {
    $term = get_queried_object();
    $breadcrumbs['itemListElement'][] = array(
      '@type'     => 'ListItem',
      'position'  => $position,
      'name'      => $term->name,
      'item'      => get_term_link($term),
    );
  } elseif (is_single()) {
    // Add category if post has one
    $categories = get_the_category();
    if (!empty($categories)) {
      $breadcrumbs['itemListElement'][] = array(
        '@type'     => 'ListItem',
        'position'  => $position,
        'name'      => $categories[0]->name,
        'item'      => get_category_link($categories[0]->term_id),
      );
      $position++;
    }

    // Add post link
    $breadcrumbs['itemListElement'][] = array(
      '@type'     => 'ListItem',
      'position'  => $position,
      'name'      => get_the_title(),
      'item'      => get_permalink(),
    );
  } elseif (is_page()) {
    $breadcrumbs['itemListElement'][] = array(
      '@type'     => 'ListItem',
      'position'  => $position,
      'name'      => get_the_title(),
      'item'      => get_permalink(),
    );
  }

  // Only output if we have more than just homepage
  if (count($breadcrumbs['itemListElement']) > 1) {
    echo "<script type=\"application/ld+json\">\n";
    echo wp_json_encode($breadcrumbs, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    echo "\n</script>\n";
  }
}
add_action('wp_head', 'safequote_add_breadcrumb_schema', 5);

/**
 * Add Schema.org WebPage structured data (JSON-LD)
 *
 * Adds basic webpage information for improved search engine understanding
 *
 * @since 1.0.0
 */
function safequote_add_webpage_schema() {
  // Don't add if Yoast SEO or Rank Math is active
  if (defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION')) {
    return;
  }

  if (is_singular()) {
    $post = get_queried_object();

    $webpage = array(
      '@context'      => 'https://schema.org',
      '@type'         => 'WebPage',
      'name'          => get_the_title(),
      'url'           => get_permalink(),
      'description'   => safequote_get_meta_description($post),
      'datePublished' => get_the_date('c'),
      'dateModified'  => get_the_modified_date('c'),
      'isPartOf'      => array(
        '@type' => 'WebSite',
        '@id'   => home_url(),
      ),
    );

    // Add author if available
    if (get_the_author()) {
      $webpage['author'] = array(
        '@type' => 'Person',
        'name'  => get_the_author(),
      );
    }

    // Add featured image
    if (has_post_thumbnail()) {
      $image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'large');
      $webpage['image'] = array(
        '@type'  => 'ImageObject',
        'url'    => $image[0],
        'width'  => $image[1],
        'height' => $image[2],
      );
    }

    echo "<script type=\"application/ld+json\">\n";
    echo wp_json_encode($webpage, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    echo "\n</script>\n";
  }
}
add_action('wp_head', 'safequote_add_webpage_schema', 5);

/**
 * Add Schema.org Product schema for vehicle posts (JSON-LD)
 *
 * Adds product/offer information for vehicle custom post type
 *
 * @since 1.0.0
 */
function safequote_add_product_schema() {
  // Don't add if Yoast SEO or Rank Math is active
  if (defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION')) {
    return;
  }

  if (!is_singular('vehicle')) {
    return;
  }

  $post = get_queried_object();

  $product = array(
    '@context'      => 'https://schema.org',
    '@type'         => 'Product',
    'name'          => get_the_title(),
    'url'           => get_permalink(),
    'description'   => wp_trim_words($post->post_content, 50),
  );

  // Add featured image
  if (has_post_thumbnail()) {
    $image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'large');
    $product['image'] = $image[0];
  }

  // Get safety rating from post meta if available
  $safety_rating = get_post_meta($post->ID, 'safety_rating', true);
  if ($safety_rating) {
    $product['aggregateRating'] = array(
      '@type'       => 'AggregateRating',
      'ratingValue' => $safety_rating,
      'worstRating' => 1,
      'bestRating'  => 5,
    );
  }

  echo "<script type=\"application/ld+json\">\n";
  echo wp_json_encode($product, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
  echo "\n</script>\n";
}
add_action('wp_head', 'safequote_add_product_schema', 5);

/**
 * Get logo URL for schema.org markup
 *
 * Uses custom logo from customizer, falls back to site icon
 *
 * @return array|null Logo details or null if none found
 * @since 1.0.0
 */
function safequote_get_schema_logo() {
  $custom_logo_id = get_theme_mod('custom_logo');

  if ($custom_logo_id) {
    $logo = wp_get_attachment_image_src($custom_logo_id, 'full');
    if ($logo) {
      return array(
        '@type'  => 'ImageObject',
        'url'    => $logo[0],
        'width'  => $logo[1],
        'height' => $logo[2],
      );
    }
  }

  // Fall back to site icon
  $site_icon = get_site_icon_url();
  if ($site_icon) {
    return array(
      '@type'  => 'ImageObject',
      'url'    => $site_icon,
      'width'  => 512,
      'height' => 512,
    );
  }

  return null;
}

/**
 * Get social media profiles from customizer
 *
 * Collects social media links for schema.org sameAs property
 *
 * @return array Array of social media URLs
 * @since 1.0.0
 */
function safequote_get_social_profiles() {
  $profiles = array();

  $social_fields = array(
    'facebook_url',
    'twitter_url',
    'linkedin_url',
    'instagram_url',
    'youtube_url',
  );

  foreach ($social_fields as $field) {
    $url = get_theme_mod($field);
    if (!empty($url)) {
      $profiles[] = esc_url($url);
    }
  }

  return $profiles;
}
