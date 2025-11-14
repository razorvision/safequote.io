# Visual Parity Checklist - React to WordPress Migration

## Overview
This checklist ensures the WordPress theme matches the React app EXACTLY - pixel-perfect where possible. Each item must be verified before marking migration phases as complete.

**Status Legend:**
- ❌ Not Started
- 🔄 In Progress
- ✅ Complete
- ⚠️ Needs Fix

---

## 1. Color System Validation

### Primary Colors
- [ ] ❌ Primary: `hsl(195, 90%, 40%)` (Teal) - NOT blue
  - Current: `#3B82F6` (blue) ⚠️ WRONG
  - Target: Teal color from React
  - Files to update: functions.php, main.css

- [ ] ❌ Secondary: `hsl(35, 85%, 90%)` (Warm Sand) - NOT green
  - Current: `#10B981` (green) ⚠️ WRONG
  - Target: Sand color from React
  - Files to update: functions.php, main.css

### CSS Variables (from src/index.css)
- [ ] ❌ `--primary: 195 90% 40%`
- [ ] ❌ `--primary-foreground: 0 0% 100%`
- [ ] ❌ `--secondary: 35 85% 90%`
- [ ] ❌ `--secondary-foreground: 195 90% 30%`
- [ ] ❌ `--background: 0 0% 100%`
- [ ] ❌ `--foreground: 240 10% 3.9%`
- [ ] ❌ `--card: 0 0% 100%`
- [ ] ❌ `--card-foreground: 240 10% 3.9%`
- [ ] ❌ `--popover: 0 0% 100%`
- [ ] ❌ `--popover-foreground: 240 10% 3.9%`
- [ ] ❌ `--muted: 240 4.8% 95.9%`
- [ ] ❌ `--muted-foreground: 240 3.8% 46.1%`
- [ ] ❌ `--accent: 35 85% 90%`
- [ ] ❌ `--accent-foreground: 195 90% 30%`
- [ ] ❌ `--destructive: 0 84.2% 60.2%`
- [ ] ❌ `--destructive-foreground: 0 0% 98%`
- [ ] ❌ `--border: 240 5.9% 90%`
- [ ] ❌ `--input: 240 5.9% 90%`
- [ ] ❌ `--ring: 195 90% 40%`
- [ ] ❌ `--radius: 0.75rem`

---

## 2. Typography

### Font Family
- [ ] ❌ Google Font 'Inter' loaded
- [ ] ❌ Font stack: `'Inter', system-ui, -apple-system, sans-serif`

### Text Sizes (Tailwind Classes)
- [ ] ❌ Hero h1: `text-4xl md:text-6xl`
- [ ] ❌ Section h2: `text-3xl md:text-4xl`
- [ ] ❌ Card titles: `text-xl font-semibold`
- [ ] ❌ Body text: `text-gray-600`
- [ ] ❌ Small text: `text-sm text-gray-500`

### Font Weights
- [ ] ❌ Headings: `font-bold` or `font-semibold`
- [ ] ❌ Body: `font-normal`
- [ ] ❌ Emphasis: `font-medium`

---

## 3. Layout & Spacing

### Container
- [ ] ❌ Max width: `max-w-7xl`
- [ ] ❌ Padding: `mx-auto px-4`

### Section Spacing
- [ ] ❌ Major sections: `py-16` or `py-20`
- [ ] ❌ Section gaps: `space-y-20`
- [ ] ❌ Component gaps: `gap-6` or `gap-8`

### Grid Layouts
- [ ] ❌ Vehicle grid: `grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6`
- [ ] ❌ Insurance grid: `grid-cols-1 md:grid-cols-3 gap-6`
- [ ] ❌ Feature grid: `grid-cols-1 md:grid-cols-2 gap-8`

---

## 4. Components Visual Match

### Header/Navigation
- [ ] ❌ Logo position and size
- [ ] ❌ Navigation menu styling
- [ ] ❌ Mobile menu animation
- [ ] ❌ Sticky header behavior
- [ ] ❌ Background: `bg-white/95 backdrop-blur-sm`

### Hero Section
- [ ] ❌ Background image (road/highway)
- [ ] ❌ Dual gradient overlay: `from-black/70 via-black/40 to-transparent`
- [ ] ❌ Text positioning and alignment
- [ ] ❌ CTA buttons with gradients: `from-primary to-teal-500`
- [ ] ❌ Stats section with icons

### VehicleCard Component
- [ ] ❌ Card shadow: `shadow-md hover:shadow-xl`
- [ ] ❌ Border radius: `rounded-xl`
- [ ] ❌ Image aspect ratio and object-fit
- [ ] ❌ Price badge styling
- [ ] ❌ Safety rating stars
- [ ] ❌ Button gradient: `from-primary to-teal-500`
- [ ] ❌ Hover scale: `transform scale-105`

### Filter Section
- [ ] ❌ Search input with icon
- [ ] ❌ Dropdown select styling
- [ ] ❌ Radio button groups
- [ ] ❌ Active filter states
- [ ] ❌ Reset button styling

### Insurance Comparison
- [ ] ❌ Card-based layout (NOT table)
- [ ] ❌ Provider logos
- [ ] ❌ Price display with currency
- [ ] ❌ Features list with checkmarks
- [ ] ❌ CTA button styling

### TopSafetyPicks
- [ ] ❌ Background gradient: `from-primary/10 to-secondary/30`
- [ ] ❌ Vehicle showcase cards
- [ ] ❌ Badge styling for awards
- [ ] ❌ Carousel/slider functionality

### Footer
- [ ] ❌ Background color
- [ ] ❌ Column layout
- [ ] ❌ Social media icons
- [ ] ❌ Newsletter signup form
- [ ] ❌ Copyright text

---

## 5. Gradients

### Background Gradients
- [ ] ❌ Body: `bg-gradient-to-br from-white via-secondary to-white`
- [ ] ❌ Hero overlay: `from-black/70 via-black/40 to-transparent`
- [ ] ❌ Section backgrounds: Various gradient combinations

### Button Gradients
- [ ] ❌ Primary buttons: `bg-gradient-to-r from-primary to-teal-500`
- [ ] ❌ Secondary buttons: Solid colors with hover states
- [ ] ❌ Gradient direction preservation

### Card Gradients
- [ ] ❌ Premium cards: Subtle gradient backgrounds
- [ ] ❌ Hover effects: Gradient intensity changes

---

## 6. Animations & Transitions

### Hover Effects (300ms ease)
- [ ] ❌ Button hover: Scale and shadow
- [ ] ❌ Card hover: Lift effect with shadow-xl
- [ ] ❌ Link hover: Color transition
- [ ] ❌ Image hover: Scale 1.05

### Scroll Animations
- [ ] ❌ Fade in on scroll
- [ ] ❌ Stagger delay: `index * 0.1s`
- [ ] ❌ Slide up animation
- [ ] ❌ Duration: 0.5s standard

### Loading States
- [ ] ❌ Skeleton screens
- [ ] ❌ Spinner animations
- [ ] ❌ Progress indicators

### Page Transitions
- [ ] ❌ Smooth scroll behavior
- [ ] ❌ Modal fade in/out
- [ ] ❌ Toast notifications slide

---

## 7. Interactive Elements

### Forms
- [ ] ❌ Input field styling with focus states
- [ ] ❌ Error message styling
- [ ] ❌ Success states
- [ ] ❌ Loading button states

### Modals/Dialogs
- [ ] ❌ Backdrop blur effect
- [ ] ❌ Center positioning
- [ ] ❌ Close button (X) styling
- [ ] ❌ Animation: Fade and scale

### Tooltips
- [ ] ❌ Dark background with white text
- [ ] ❌ Arrow positioning
- [ ] ❌ Hover trigger behavior

### Dropdowns
- [ ] ❌ Smooth open/close animation
- [ ] ❌ Border and shadow styling
- [ ] ❌ Active item highlighting

---

## 8. Responsive Design

### Breakpoints
- [ ] ❌ Mobile: < 768px
- [ ] ❌ Tablet: 768px - 1024px
- [ ] ❌ Desktop: > 1024px

### Mobile Specific
- [ ] ❌ Hamburger menu
- [ ] ❌ Touch-friendly tap targets (min 44px)
- [ ] ❌ Swipe gestures for carousels
- [ ] ❌ Appropriate font sizes

### Tablet Specific
- [ ] ❌ 2-column layouts
- [ ] ❌ Adjusted spacing
- [ ] ❌ Navigation changes

### Desktop Specific
- [ ] ❌ Full navigation bar
- [ ] ❌ 3+ column grids
- [ ] ❌ Hover states enabled

---

## 9. Icons & Images

### Icons
- [ ] ❌ Lucide icons or equivalent SVGs
- [ ] ❌ Consistent size (24px default)
- [ ] ❌ Proper color inheritance

### Images
- [ ] ❌ Vehicle images (same URLs/assets)
- [ ] ❌ Placeholder images
- [ ] ❌ Lazy loading implementation
- [ ] ❌ Responsive srcset

### Logo
- [ ] ❌ SafeQuote logo (text or image)
- [ ] ❌ Proper sizing and positioning
- [ ] ❌ Retina support

---

## 10. Special Effects

### Shadows
- [ ] ❌ `shadow-sm`: Small elements
- [ ] ❌ `shadow-md`: Cards default
- [ ] ❌ `shadow-lg`: Elevated elements
- [ ] ❌ `shadow-xl`: Hover states

### Border Radius
- [ ] ❌ `rounded-xl`: Cards
- [ ] ❌ `rounded-2xl`: Hero sections
- [ ] ❌ `rounded-full`: Avatars/badges
- [ ] ❌ `rounded-lg`: Buttons

### Backdrop Effects
- [ ] ❌ `backdrop-blur-sm`: Navigation
- [ ] ❌ `backdrop-blur-md`: Modals
- [ ] ❌ Proper browser support

---

## 11. Validation Tools

### Browser DevTools
- [ ] ❌ Color picker verification
- [ ] ❌ Computed styles comparison
- [ ] ❌ Animation timeline check
- [ ] ❌ Network tab (same assets)

### Screenshot Comparison
- [ ] ❌ Homepage full page
- [ ] ❌ Vehicle grid section
- [ ] ❌ Insurance comparison
- [ ] ❌ Mobile view
- [ ] ❌ Tablet view

### Performance Metrics
- [ ] ❌ Page load time < 3s
- [ ] ❌ First Contentful Paint
- [ ] ❌ Cumulative Layout Shift
- [ ] ❌ Time to Interactive

---

## 12. Cross-Browser Testing

### Chrome/Edge
- [ ] ❌ Visual consistency
- [ ] ❌ Animations working
- [ ] ❌ No console errors

### Firefox
- [ ] ❌ Visual consistency
- [ ] ❌ CSS compatibility
- [ ] ❌ No console errors

### Safari
- [ ] ❌ Visual consistency
- [ ] ❌ Webkit prefixes working
- [ ] ❌ No console errors

### Mobile Browsers
- [ ] ❌ iOS Safari
- [ ] ❌ Chrome Mobile
- [ ] ❌ Samsung Internet

---

## Sign-off Criteria

Before marking any migration phase as complete:

1. **Colors**: All HSL values match exactly
2. **Layout**: Spacing and grids identical
3. **Typography**: Same fonts, sizes, weights
4. **Components**: Pixel-perfect match
5. **Animations**: Same timing and easing
6. **Interactions**: Identical behavior
7. **Responsive**: All breakpoints working
8. **Performance**: Meets or exceeds React app

**Phase Approval Requires:**
- [ ] Side-by-side screenshot comparison uploaded
- [ ] All checklist items for phase marked ✅
- [ ] No visual regression from React app
- [ ] Tested on 3+ browsers
- [ ] Mobile and desktop verified

---

**Document Version**: 1.0
**Created**: 2025-11-14
**Last Updated**: 2025-11-14
**Status**: 🔄 In Progress - Initial setup, no items validated yet

## Next Steps
1. Copy all CSS variables from React app
2. Update WordPress theme colors to match
3. Begin component-by-component validation
4. Take comparison screenshots
5. Fix discrepancies as found