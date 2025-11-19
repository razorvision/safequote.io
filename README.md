# SafeQuote - WordPress Theme

A modern WordPress theme that helps parents find safe vehicles for their teens, compare insurance quotes, and discover driver's education resources. Built with real NHTSA safety ratings integration, Tailwind CSS, and accessibility-first design.

## 🎯 Project Description

SafeQuote provides an intuitive platform for families to:

- **Find Safe Vehicles** - Search and filter vehicles with real NHTSA crash test ratings
- **Compare Insurance** - Get quotes from major insurance providers based on vehicle safety
- **Discover Driver's Education** - Find local and online driver's ed programs
- **Access Real Safety Data** - View detailed crash test results, safety features, and vehicle ratings

The WordPress theme combines vehicle/insurance data with real government safety ratings, providing comprehensive vehicle safety information for informed purchasing decisions.

## 🛠 Tech Stack

### CMS & Backend
- **WordPress 6.x** - Content management system
- **PHP 8.0+** - Server-side language
- **MySQL 8.0+** - Database

### Frontend & Styling
- **Tailwind CSS 3.3.3** - Utility-first CSS framework (JIT compilation)
- **Vanilla JavaScript (ES6+)** - Interactive features
- **Lucide Icons** - SVG icon library

### External Integrations
- **NHTSA API** - Real National Highway Traffic Safety Administration safety ratings
- **Local by Flywheel** - Development environment (recommended)

### Development Tools
- **npm** - Package management (Tailwind CSS build)
- **Rsync** - Theme file synchronization
- **GitHub CLI (gh)** - GitHub automation

## 📦 Installation & Setup

### Prerequisites

- **Local by Flywheel** - WordPress local development environment ([Download](https://localwp.com/))
- **Node.js 18+** - Required for Tailwind CSS compilation
- **npm** - Package manager (comes with Node.js)
- **PHP 8.0+** - For WordPress theme development
- **Git** - Version control

### Setup Steps

1. **Clone the repository**
   ```bash
   cd safequote
   ```

2. **Install theme dependencies**
   ```bash
   cd wp-content/themes/safequote-traditional
   npm install
   ```

3. **Build Tailwind CSS**
   ```bash
   npm run build:css
   ```

4. **Sync theme to Local WP**
   ```bash
   rsync -avz --delete ./ "/Users/lucianasilvaoliveira/Local Sites/safequote/app/public/wp-content/themes/safequote-traditional/"
   ```

5. **Open Local WordPress site**
   - Launch Local by Flywheel
   - Start the SafeQuote site
   - Go to http://safequote.local
   - Access admin: http://safequote.local/wp-admin/

## 🚀 Development Commands

### Build Tailwind CSS
```bash
npm run build:css
```
Compiles Tailwind CSS with JIT compilation. Run this **before** syncing theme files.

### Sync Theme to Local WP (One-liner)
```bash
cd /Users/lucianasilvaoliveira/Downloads/safequote/wp-content/themes/safequote-traditional && npm run build:css && rsync -avz --delete ./ "/Users/lucianasilvaoliveira/Local Sites/safequote/app/public/wp-content/themes/safequote-traditional/"
```

### Quick Reference
- **Dev Theme**: `/Users/lucianasilvaoliveira/Downloads/safequote/wp-content/themes/safequote-traditional/`
- **Local WP Theme**: `/Users/lucianasilvaoliveira/Local Sites/safequote/app/public/wp-content/themes/safequote-traditional/`
- **Debug Log**: `/Users/lucianasilvaoliveira/Local Sites/safequote/app/public/wp-content/debug.log`

## 📁 Project Structure

```
safequote/
│
├── README.md                                    # This file
├── package.json                                 # Dependencies (npm)
│
└── wp-content/
    └── themes/
        └── safequote-traditional/              # Main theme directory
            │
            ├── assets/
            │   ├── css/
            │   │   ├── tailwind.css            # Compiled Tailwind CSS
            │   │   ├── main.css                # Custom component styles
            │   │   ├── components.css          # Component utilities
            │   │   └── animations.css          # Animation definitions
            │   │
            │   └── js/
            │       ├── main.js                 # Core functionality
            │       ├── filters.js              # Vehicle filtering
            │       ├── modals.js               # Modal functionality
            │       ├── animations.js           # Animation handlers
            │       ├── nhtsa-api.js            # Safety ratings API
            │       ├── insurance.js            # Insurance comparison
            │       ├── notifications.js        # Notification system
            │       ├── forms.js                # Form handling
            │       ├── safety-ratings.js       # Safety ratings page
            │       └── customizer.js           # Customizer preview
            │
            ├── inc/
            │   ├── seo.php                     # SEO & structured data
            │   ├── customizer.php              # Theme customizer settings
            │   ├── template-tags.php           # Custom template functions
            │   ├── extras.php                  # Extra functionality
            │   ├── post-types.php              # Custom post types
            │   ├── taxonomies.php              # Custom taxonomies
            │   ├── ajax-handlers.php           # AJAX endpoints
            │   ├── vehicle-data.php            # Vehicle data functions
            │   └── insurance-data.php          # Insurance data functions
            │
            ├── template-parts/
            │   ├── hero.php                    # Hero section
            │   ├── features.php                # Features section
            │   ├── search-filters.php          # Vehicle filters
            │   ├── vehicle-grid.php            # Vehicle listing
            │   ├── vehicle-card.php            # Vehicle card component
            │   ├── top-safety-picks.php        # Featured safe vehicles
            │   ├── insurance-comparison.php    # Insurance comparison
            │   ├── safety-ratings.php          # Safety ratings section
            │   ├── drivers-ed.php              # Driver education section
            │   ├── cta.php                     # Call-to-action section
            │   └── modal-login.php             # Login modal
            │
            ├── front-page.php                  # Homepage template
            ├── page.php                        # Standard page template
            ├── page-safequote-safety-ratings.php  # Safety ratings page
            ├── index.php                       # Default index template
            ├── 404.php                         # 404 error page
            ├── header.php                      # Theme header
            ├── footer.php                      # Theme footer
            ├── functions.php                   # Theme functions & hooks
            ├── style.css                       # Theme metadata
            ├── package.json                    # npm dependencies
            ├── tailwind.config.js              # Tailwind configuration
            └── postcss.config.js               # PostCSS configuration
```

## 🎨 WordPress Theme Features

### Custom Post Types
- **Vehicle** - Individual vehicle listings
  - Supports: Title, Editor, Thumbnail, Custom Fields
  - Taxonomies: Vehicle Type, Make, Features
  - Archive: `/vehicles/`

- **Insurance Provider** - Insurance company listings
  - Public: Yes
  - REST API: Enabled

### Custom Taxonomies
- **Vehicle Type** (hierarchical) - Sedan, SUV, Truck, Coupe, etc.
- **Vehicle Make** (hierarchical) - Honda, Toyota, BMW, etc.
- **Vehicle Feature** (non-hierarchical) - Safety features

### Theme Customizer
**Location**: WordPress Admin → Customize → SafeQuote Settings

- **Theme Colors**: Primary, Secondary (with live preview)
- **Header Settings**: Sticky header, layout options
- **Footer Settings**: Copyright text, widget columns
- **Social Media**: Facebook, Twitter, LinkedIn, Instagram, YouTube
- **Layout**: Container width, sidebar position
- **Typography**: Font families with Google Fonts
- **Vehicle Display**: Vehicles per page, safety ratings, compare buttons

### Widget Areas
- **Primary Sidebar** - For blog/page sidebars
- **Footer Widget Area 1-4** - 4-column footer widget layout

### SEO & Structured Data
- **Open Graph Meta Tags** - Social media sharing
- **Twitter Card Tags** - Twitter sharing
- **Schema.org JSON-LD** - Structured data:
  - Organization schema
  - WebSite schema with search
  - Product schema (vehicles)
  - BreadcrumbList schema
  - WebPage schema

### Accessibility Features
- **Semantic HTML** - Proper landmark roles (`<main>`, `<nav>`, `<footer>`)
- **ARIA Attributes** - Navigation labels, button descriptions
- **Keyboard Navigation** - Tab support, Escape key, arrow keys
- **ARIA Live Regions** - Dynamic content announcements
- **Skip Links** - Skip to content link
- **Focus Management** - Visible focus states

## 🔧 Development Workflow

### 1. Create Feature Branch
```bash
git checkout -b feature/issue-XX-description
```

### 2. Make Theme Changes
- Edit PHP templates in `template-parts/`
- Update styles using Tailwind classes
- Add JavaScript modules to `assets/js/`

### 3. Build CSS
```bash
npm run build:css
```

### 4. Sync to Local WP
```bash
rsync -avz --delete ./ "/Users/lucianasilvaoliveira/Local Sites/safequote/app/public/wp-content/themes/safequote-traditional/"
```

### 5. Test in Browser
- Check http://safequote.local in browser
- Open WordPress admin: http://safequote.local/wp-admin/
- Check browser console for errors (F12)

### 6. Commit Changes
```bash
git add .
git commit -m "feat: Describe your changes"
```

### 7. Create Pull Request
```bash
git push origin feature/issue-XX-description
```
Then create PR on GitHub referencing the issue.

## 🧪 Testing Checklist

Before committing, test:

- [ ] **Visual Design**
  - [ ] Desktop (1920px+)
  - [ ] Tablet (768px)
  - [ ] Mobile (375px)

- [ ] **Functionality**
  - [ ] Links work correctly
  - [ ] Forms submit
  - [ ] AJAX calls work
  - [ ] Filters update results
  - [ ] Customizer saves settings

- [ ] **Accessibility**
  - [ ] Keyboard navigation (Tab, Enter, Esc)
  - [ ] Screen reader announces content
  - [ ] ARIA attributes present
  - [ ] Focus states visible
  - [ ] Color contrast sufficient

- [ ] **SEO & Performance**
  - [ ] Meta tags in HTML source
  - [ ] Schema.org validates
  - [ ] Page loads <3 seconds
  - [ ] No console errors

- [ ] **WordPress Admin**
  - [ ] Customizer options work
  - [ ] Widgets display correctly
  - [ ] No PHP errors in debug.log

## 🐛 Troubleshooting

### Tailwind CSS Not Compiling
```bash
# Clear cache and rebuild
rm -rf node_modules
npm install
npm run build:css
```

### Changes Not Appearing in Local WP
1. Run `npm run build:css` (generates new CSS)
2. Run rsync sync command
3. Hard refresh browser (Cmd+Shift+R or Ctrl+Shift+R)
4. Clear browser cache if needed

### WordPress Debug Log Errors
1. Check `/Users/lucianasilvaoliveira/Local Sites/safequote/app/public/wp-content/debug.log`
2. Look for PHP warnings or errors
3. Check for missing files or undefined variables

### Customizer Changes Not Saving
1. Check WordPress admin permissions
2. Verify `wp-config.php` has correct database settings
3. Check PHP error log in Local

### Theme Not Appearing in WordPress
1. Verify theme folder is in correct location
2. Check `style.css` has valid header comment
3. Verify `functions.php` has no PHP syntax errors
4. Go to Appearance → Themes and activate theme

## 📚 PHP Code Standards

### Function Naming
```php
function safequote_get_vehicles()       // Always prefix with safequote_
function safequote_render_vehicle()     // Descriptive action verb
```

### Escaping Output
```php
echo esc_html($text);                   // For plain text
echo esc_attr($attribute);              // For HTML attributes
echo esc_url($url);                     // For URLs
echo wp_kses_post($content);            // For HTML with allowed tags
```

### Sanitizing Input
```php
$text = sanitize_text_field($_POST['field']);
$email = sanitize_email($_POST['email']);
$color = sanitize_hex_color($_POST['color']);
```

## 📖 Resources

### WordPress Theme Development
- [WordPress Theme Handbook](https://developer.wordpress.org/themes/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [Template Hierarchy](https://developer.wordpress.org/themes/basics/template-hierarchy/)
- [Hooks and Filters](https://developer.wordpress.org/plugins/hooks/)

### CSS & Styling
- [Tailwind CSS Documentation](https://tailwindcss.com/)
- [Tailwind CSS Class Reference](https://tailwindcss.com/docs/utility-first)
- [PostCSS Documentation](https://postcss.org/)

### External APIs
- [NHTSA Safety Ratings API](https://one.nhtsa.gov/webapi/api)

### Accessibility
- [WAI-ARIA Authoring Practices](https://www.w3.org/WAI/ARIA/apg/)
- [WebAIM Accessibility Resources](https://webaim.org/)
- [MDN Web Accessibility](https://developer.mozilla.org/en-US/docs/Web/Accessibility)

### Tools & Utilities
- [Local by Flywheel Documentation](https://localwp.com/help-docs/)
- [GitHub CLI Documentation](https://cli.github.com/)
- [rsync Manual](https://linux.die.net/man/1/rsync)

## 📄 License

[Add license information here]

---

**Last Updated**: November 2024
**Version**: 1.0 (WordPress Theme)
**PHP Version**: 8.0+
**WordPress Version**: 6.0+
**Node Version**: 18+
