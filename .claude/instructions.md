# 🔴 MANDATORY: WordPress Theme Development Instructions for Claude

**⚠️ IMPORTANT: This file MUST be read at the start of EVERY new context window or conversation.**

Instructions for all Claude interactions and AI agents working on SafeQuote WordPress Theme development.

## 📋 Required Reading Checklist

Before starting ANY task, confirm you have:
- [ ] Read this entire instructions.md file
- [ ] Checked for any updates to the sync commands (lines 343-365)
- [ ] Reviewed the WordPress theme structure (lines 33-81)
- [ ] Understood the Local WP sync process
- [ ] Reviewed the PHP security standards (lines 83-119)

## Before Starting Any Task

1. **Read relevant .claude/ files**:
   - README.md (orientation)
   - CLAUDE.md (pre-flight checklist)

2. **Read the GitHub issue completely**:
   - Understand acceptance criteria
   - Note file locations
   - Check implementation notes

3. **Know the WordPress theme structure**:
   ```
   wp-content/themes/safequote-traditional/
   ├── assets/
   │   ├── css/         (Tailwind compiled CSS + custom styles)
   │   └── js/          (JavaScript modules)
   ├── inc/             (PHP includes)
   ├── template-parts/  (Reusable template components)
   ├── front-page.php   (Homepage template)
   ├── header.php       (Theme header)
   ├── footer.php       (Theme footer)
   ├── functions.php    (Theme functions & hooks)
   ├── style.css        (Theme metadata)
   └── index.html       (Fallback)
   ```

4. **Understand WordPress hooks & actions**:
   - `wp_head` - Output in document head
   - `wp_footer` - Output before closing body tag
   - `after_setup_theme` - Theme initialization
   - `wp_enqueue_scripts` - Load CSS/JS
   - `customize_register` - Theme Customizer settings

5. **Know the data flow**:
   - Vehicles: `inc/vehicle-data.php` function calls
   - Insurance: `inc/insurance-data.php` calculations
   - NHTSA API: Direct calls from `assets/js/nhtsa-api.js`
   - Customizer: `inc/customizer.php` settings
   - SEO: `inc/seo.php` meta tags & structured data

## Code Quality Principles for WordPress

### 1. Security First - Always Sanitize and Escape

**Bad**: Outputs without escaping
```php
// NEVER do this!
echo $_GET['search'];
echo $post->post_content;
```

**Good**: Proper escaping for context
```php
// For HTML content
echo esc_html($post->post_title);

// For URLs
echo esc_url($post->post_url);

// For HTML attributes
echo esc_attr($post->post_name);

// For HTML content (with tags allowed)
echo wp_kses_post($post->post_content);
```

**Good**: Sanitize input on save
```php
$meta = sanitize_text_field($_POST['field_name']);
$meta = sanitize_email($_POST['email']);
$meta = sanitize_hex_color($_POST['color']);
```

**Principle**: Never trust user input. Always sanitize on save, escape on output.

### 2. WordPress Coding Standards

**Bad**: Non-standard formatting
```php
function getVehicles($filter){
    $result = array();
    for($i=0;$i<count($vehicles);$i++){
        if($vehicles[$i]['price'] < $filter){
            $result[] = $vehicles[$i];
        }
    }
    return $result;
}
```

**Good**: WordPress standards with clear formatting
```php
function safequote_get_filtered_vehicles($max_price = 50000) {
    $all_vehicles = safequote_get_vehicles();
    $filtered_vehicles = array();

    foreach ($all_vehicles as $vehicle) {
        if ($vehicle['price'] <= $max_price) {
            $filtered_vehicles[] = $vehicle;
        }
    }

    return $filtered_vehicles;
}
```

**Principle**:
- Use 2 spaces indentation (NOT tabs)
- Prefix functions: `safequote_*`
- Use snake_case for function/variable names
- Use PascalCase for class names
- Add proper inline documentation

### 3. Theme Customizer Implementation

**Bad**: No customizer settings
```php
// Hardcoded values
$logo_size = 100;
$primary_color = '#3B82F6';
```

**Good**: Use customizer settings
```php
$logo_size = get_theme_mod('custom_logo_size', 100);
$primary_color = get_theme_mod('primary_color', '#3B82F6');
```

**Principle**: User-configurable settings belong in the customizer.

### 4. SEO & Structured Data

**Bad**: No meta tags or structured data
```html
<meta charset="UTF-8">
<!-- That's it -->
```

**Good**: Complete SEO implementation
```php
// In inc/seo.php
- Open Graph meta tags
- Twitter Card tags
- Meta descriptions
- Schema.org JSON-LD
- Canonical URLs
```

**Principle**: Every page should have proper SEO metadata.

### 5. Accessibility Standards

**Bad**: No accessibility attributes
```html
<button id="menu-toggle">Menu</button>
<nav>Navigation</nav>
```

**Good**: Proper ARIA and semantic HTML
```html
<button id="menu-toggle" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="mobile-menu">Menu</button>
<nav role="navigation" aria-label="Primary">Navigation</nav>
```

**Principle**: Users with assistive technology deserve the same experience.

### 6. Meaningful Comments

**Bad**: Obvious comments
```php
// Set title to get_the_title()
$title = get_the_title();
// Loop through vehicles
foreach ($vehicles as $vehicle) {
```

**Good**: Explains reasoning
```php
// Fetch title from WordPress database (custom field fallback available)
$title = get_the_title();

// Filter vehicles by safety rating to show only 5-star options first
foreach ($vehicles as $vehicle) {
```

**Principle**: Comments should answer "why did you do this?"

### 7. Template Best Practices

**Bad**: Logic in template
```php
<?php
$vehicles = array_filter($all_vehicles, function($v) {
    return $v['price'] < 50000 && $v['rating'] >= 4;
});
foreach ($vehicles as $vehicle) {
    echo '<div class="vehicle">' . esc_html($vehicle['name']) . '</div>';
}
?>
```

**Good**: Logic in functions, templates display data
```php
<?php
// functions.php
function safequote_get_featured_vehicles() {
    // All business logic here
}

// template-parts/featured-vehicles.php
$vehicles = isset($vehicles) ? $vehicles : array();
foreach ($vehicles as $vehicle) {
    set_query_var('vehicle', $vehicle);
    get_template_part('template-parts/vehicle-card');
}
?>
```

**Principle**: Templates display data, functions contain logic.

### 8. Custom Post Types & Taxonomies

**Bad**: Using posts for everything
```php
// No - creates complexity with default WordPress post behavior
```

**Good**: Use custom post types for domain objects
```php
// functions.php
register_post_type('vehicle', array(
    'public' => true,
    'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
    'has_archive' => true,
));

register_taxonomy('vehicle_type', 'vehicle', array(
    'public' => true,
    'hierarchical' => true,
));
```

**Principle**: Custom post types organize your content properly.

## PHP Code Standards

### Variable Naming
```php
$variable_name          // Lowercase with underscores
$post_id                // Descriptive name
$max_price = 50000      // Descriptive with default
```

### Function Naming
```php
function safequote_get_vehicles()       // Prefixed, snake_case
function safequote_render_vehicle()     // Prefixed verb
function safequote_is_valid_price()     // Prefixed with is_ for boolean
```

### Class Naming
```php
class SafeQuote_Walker_Nav_Menu extends Walker_Nav_Menu  // PascalCase
class SafeQuote_Customizer                                 // PascalCase
```

### Hooks & Filters
```php
do_action('safequote_before_vehicle_display', $vehicle);
apply_filters('safequote_vehicle_price', $price, $vehicle);
add_action('wp_head', 'safequote_add_meta_tags');
add_filter('the_title', 'safequote_filter_title');
```

## Debugging Tips

### 1. Check the WordPress Debug Log

Enable in `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Check log file: `/Local Sites/safequote/app/public/wp-content/debug.log`

### 2. Use error_log()

For debugging:
```php
error_log('Vehicle ID: ' . $post_id);
error_log('Vehicle data: ' . print_r($vehicle, true));
```

### 3. Use var_dump() Temporarily

For development only:
```php
echo '<pre>';
var_dump($vehicles);
echo '</pre>';
die();
```

Remove before committing!

### 4. Check WordPress Admin

- Customizer: Customize > SafeQuote Settings
- Widget Areas: Widgets > Footer Widget Areas
- Menus: Menus > Manage Locations
- Pages: Pages > Safequote pages

### 5. Browser Developer Tools

- Inspector: Check HTML structure, semantic markup
- Console: Check for JavaScript errors
- Network: Check AJAX requests, API calls
- Accessibility: Use axe DevTools extension

## Testing Checklist

Before submitting PR, test:

- [ ] **Desktop**
  - [ ] Chrome/Edge (latest)
  - [ ] Firefox (latest)
  - [ ] Safari (if possible)

- [ ] **Mobile**
  - [ ] iOS Safari (iPhone sim or device)
  - [ ] Chrome Android (Android sim or device)

- [ ] **Responsive Design**
  - [ ] 375px width (mobile)
  - [ ] 768px width (tablet)
  - [ ] 1024px+ width (desktop)

- [ ] **Functionality**
  - [ ] Links functional and correct
  - [ ] Forms submit properly
  - [ ] AJAX calls work
  - [ ] Customizer saves settings
  - [ ] Widgets display correctly
  - [ ] SEO meta tags in HTML
  - [ ] Schema.org validates

- [ ] **Accessibility**
  - [ ] Keyboard navigation works (Tab, Enter, Esc, Arrow keys)
  - [ ] Screen reader announces content
  - [ ] ARIA attributes correct
  - [ ] Skip to content link works
  - [ ] Color contrast sufficient
  - [ ] Focus styles visible

- [ ] **Performance**
  - [ ] Page loads in <3 seconds
  - [ ] No console errors
  - [ ] No console warnings
  - [ ] Smooth scrolling/animations

- [ ] **Browser Console**
  - [ ] No red errors
  - [ ] No yellow warnings
  - [ ] No debug logs left

- [ ] **WordPress Admin**
  - [ ] Settings save in customizer
  - [ ] Widgets display in widget areas
  - [ ] Menus assign to locations
  - [ ] No errors in WP debug log

## Git Workflow

### Commit Messages

Format: `[TYPE] Description`

```bash
git commit -m "feat: Add SEO meta tags and Schema.org structured data"
git commit -m "fix: Correct heading hierarchy in vehicle grid"
git commit -m "docs: Update accessibility guidelines in instructions"
git commit -m "refactor: Extract customizer color logic to function"
```

### Branch Names

Format: `feature/issue-XX-brief-description`

```bash
git checkout -b feature/issue-19-seo-accessibility
git checkout -b fix/issue-25-widget-styling
```

### Pull Requests

Always create PRs:
1. Push branch to GitHub
2. Create PR with description
3. Reference the issue: "Closes #19"
4. Link to relevant .claude/ files if helpful

## File Editing Guidelines

### PHP Files (WordPress)

**Format**: 2 spaces indentation
**Naming**: snake_case for functions, PascalCase for classes
**Prefixes**: Always prefix custom functions: `safequote_*`

```php
<?php
/**
 * Short description
 *
 * Longer description if needed
 *
 * @param  type $param Description
 * @return type  What it returns
 * @since  1.0.0
 */
function safequote_function_name($param) {
    // Function body
    return $result;
}
```

### CSS/Tailwind Files

**Use**: Tailwind utilities (not custom CSS when possible)
**Pattern**: Utility classes in className/class attribute
**Responsive**: Use sm:, md:, lg: prefixes

```html
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
```

### JavaScript Files

**Format**: 2 spaces indentation
**Module Pattern**: Use IIFE or ES6 modules
**Comments**: JSDoc for functions

```javascript
/**
 * Initialize vehicle filters
 * @param {Object} options Configuration options
 */
function initFilters(options) {
    // Function body
}
```

## When to Ask for Help

Ask in the GitHub issue if:
- Specification is unclear
- Not sure about WordPress approach
- Task seems larger than estimated
- You've hit a genuine blocker
- You need clarification on .claude/ files

Don't guess! Clarification is always better than wrong implementations.

## After You're Done

1. **Self-review**:
   - [ ] Does it solve the issue?
   - [ ] All acceptance criteria met?
   - [ ] Is code quality good?
   - [ ] No console errors?
   - [ ] No debug logs left?

2. **Rebuild CSS and Sync**:
   ```bash
   cd /Users/lucianasilvaoliveira/Downloads/safequote/wp-content/themes/safequote-traditional
   npm run build:css
   rsync -avz --delete ./ "/Users/lucianasilvaoliveira/Local Sites/safequote/app/public/wp-content/themes/safequote-traditional/"
   ```

3. **Test in Local WP**:
   - [ ] Open Local WordPress site
   - [ ] Test all functionality
   - [ ] Check console for errors
   - [ ] Verify in WordPress admin

4. **Create PR**:
   - [ ] Use PR template
   - [ ] Reference issue
   - [ ] List testing done
   - [ ] Add any breaking changes

5. **Await Review**:
   - [ ] Respond to feedback
   - [ ] Make requested changes
   - [ ] Re-test after changes

## Local Sync Instructions

After making changes to the WordPress theme files, rebuild Tailwind CSS and sync to the Local WP installation:

```bash
# 1. Navigate to the WordPress theme directory
cd /Users/lucianasilvaoliveira/Downloads/safequote/wp-content/themes/safequote-traditional

# 2. Rebuild Tailwind CSS to include any new utility classes
npm run build:css

# 3. Sync theme files from development to Local WP site
rsync -avz --delete \
  /Users/lucianasilvaoliveira/Downloads/safequote/wp-content/themes/safequote-traditional/ \
  "/Users/lucianasilvaoliveira/Local Sites/safequote/app/public/wp-content/themes/safequote-traditional/"
```

**Important:** Always run `npm run build:css` before syncing! Tailwind CSS uses JIT compilation and only generates CSS for classes it finds in your PHP templates. New or changed classes won't work until the CSS is rebuilt.

**Quick one-liner for both operations:**
```bash
cd /Users/lucianasilvaoliveira/Downloads/safequote/wp-content/themes/safequote-traditional && npm run build:css && rsync -avz --delete ./ "/Users/lucianasilvaoliveira/Local Sites/safequote/app/public/wp-content/themes/safequote-traditional/"
```

## 🚀 QUICK REFERENCE CARD - Essential Commands

### Every Time You Make WordPress Theme Changes:
```bash
# The ONE command you need after ANY theme file changes:
cd /Users/lucianasilvaoliveira/Downloads/safequote/wp-content/themes/safequote-traditional && npm run build:css && rsync -avz --delete ./ "/Users/lucianasilvaoliveira/Local Sites/safequote/app/public/wp-content/themes/safequote-traditional/"
```

### Key Paths:
- **Dev Theme**: `/Users/lucianasilvaoliveira/Downloads/safequote/wp-content/themes/safequote-traditional/`
- **Local WP Theme**: `/Users/lucianasilvaoliveira/Local Sites/safequote/app/public/wp-content/themes/safequote-traditional/`
- **Debug Log**: `/Users/lucianasilvaoliveira/Local Sites/safequote/app/public/wp-content/debug.log`

### Critical Rules:
1. ✅ ALWAYS run `npm run build:css` BEFORE syncing
2. ✅ ALWAYS sync to Local WP after changes
3. ✅ ALWAYS escape output with esc_html(), esc_url(), esc_attr()
4. ✅ ALWAYS sanitize input with sanitize_text_field(), sanitize_email(), etc.
5. ✅ ALWAYS prefix custom functions with `safequote_`
6. ✅ NEVER commit debug logs or hardcoded values
7. ✅ NEVER trust $_GET, $_POST without sanitization

---

## Key Reminders

> 💡 **Security First** - Sanitize input, escape output. Always.
>
> 📖 **Read the docs** - WordPress.org has excellent documentation
>
> 🧪 **Test thoroughly** - Desktop, mobile, keyboard, screen reader
>
> 💬 **Ask questions** - Clarification is free, mistakes are expensive
>
> 🔄 **Follow patterns** - Use existing code as reference
>
> 🎯 **Focus on the task** - Don't add extra features
>
> ✅ **Verify completion** - Check all acceptance criteria

---

**Last Updated**: November 2024
**Version**: 2.0 (WordPress Theme Edition)
**Priority**: MANDATORY READING
