# SafeQuote Theme - Development Guide

## Preview React App (No WordPress)

Run from **root directory**:

```bash
npm run dev
```

Visit: http://localhost:3000

Changes auto-reload. Hot Module Replacement enabled for fast development.

## Build for WordPress

```bash
npm run build
```

Outputs to `wp-content/themes/safequote-theme/build/`. Changes are reflected in WordPress theme immediately.

## Deploy to WordPress

Run from **root directory**:

```bash
./build-production.sh
```

Creates `safequote-theme.zip` (~160KB) ready for WordPress upload.

## Generate Theme Screenshot

For WordPress theme directory display:

```bash
npm run dev      # Start dev server (in one terminal)
node generate-theme-thumbnail.js  # In another terminal
```

Creates `wp-content/themes/safequote-theme/screenshot.png` (1200x900px).

---

**Note:** `react-app/` in the theme directory is a symlink to the root, so all commands work from root with the same files.
