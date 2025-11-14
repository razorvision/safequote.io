#!/bin/bash
# SafeQuote Traditional WordPress Theme - Production Build Script
# Creates a production-ready theme package
# Run from root: ./build-production.sh

THEME_DIR="wp-content/themes/safequote-traditional"
PROD_DIR="safequote-traditional"
ZIP_FILE="safequote-traditional.zip"

echo "🔨 Building production theme package..."

# Check if theme directory exists
if [ ! -d "$THEME_DIR" ]; then
  echo "❌ Error: Theme directory not found at $THEME_DIR"
  exit 1
fi

# Create production directory
rm -rf "$PROD_DIR"
mkdir -p "$PROD_DIR"

# Copy all theme files except development files
echo "📦 Copying theme files..."

# Core theme files
cp "$THEME_DIR/style.css" "$PROD_DIR/"
cp "$THEME_DIR/functions.php" "$PROD_DIR/"
cp "$THEME_DIR/index.php" "$PROD_DIR/"
cp "$THEME_DIR/header.php" "$PROD_DIR/"
cp "$THEME_DIR/footer.php" "$PROD_DIR/"
cp "$THEME_DIR/front-page.php" "$PROD_DIR/"
cp "$THEME_DIR/page.php" "$PROD_DIR/"
cp "$THEME_DIR/404.php" "$PROD_DIR/"

# Copy directories
cp -r "$THEME_DIR/assets" "$PROD_DIR/"
cp -r "$THEME_DIR/inc" "$PROD_DIR/"

# Create empty directories if they don't exist yet
mkdir -p "$PROD_DIR/template-parts"
mkdir -p "$PROD_DIR/languages"

# Copy screenshot
if [ -f "$THEME_DIR/screenshot.png" ]; then
  cp "$THEME_DIR/screenshot.png" "$PROD_DIR/"
  echo "✅ Screenshot included"
fi

# Remove development files if any
find "$PROD_DIR" -name ".DS_Store" -delete
find "$PROD_DIR" -name "*.map" -delete
find "$PROD_DIR" -name ".gitkeep" -delete

# Create zip
echo "🗜️ Creating zip archive..."
zip -r -q "$ZIP_FILE" "$PROD_DIR"

# Cleanup
rm -rf "$PROD_DIR"

# Show result
SIZE=$(du -sh "$ZIP_FILE" | cut -f1)
echo "✅ Done! Created: $ZIP_FILE ($SIZE)"
echo "📦 Ready for WordPress upload"
echo "📁 Theme includes:"
echo "   - All PHP templates"
echo "   - CSS/JS assets"
echo "   - Include files (customizer, post types, etc.)"
echo "   - Screenshot.png"
