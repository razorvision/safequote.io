#!/bin/bash
# SafeQuote WordPress Theme - Production Build Script
# Creates a production-ready theme package (520KB instead of 200MB)
# Run from root: ./build-production.sh

THEME_DIR="wp-content/themes/safequote-theme"
PROD_DIR="safequote-theme"
ZIP_FILE="safequote-theme.zip"

echo "🔨 Building production theme package..."

# Create production directory
rm -rf "$PROD_DIR"
mkdir -p "$PROD_DIR"

# Copy only production files from theme directory
cp "$THEME_DIR/style.css" "$PROD_DIR/"
cp "$THEME_DIR/functions.php" "$PROD_DIR/"
cp "$THEME_DIR/index.php" "$PROD_DIR/"
cp -r "$THEME_DIR/build" "$PROD_DIR/"

# Copy screenshot if it exists
if [ -f "$THEME_DIR/screenshot.png" ]; then
  cp "$THEME_DIR/screenshot.png" "$PROD_DIR/"
fi

# Create zip
zip -r -q "$ZIP_FILE" "$PROD_DIR"

# Cleanup
rm -rf "$PROD_DIR"

# Show result
SIZE=$(du -sh "$ZIP_FILE" | cut -f1)
echo "✅ Done! Created: $ZIP_FILE ($SIZE)"
echo "📦 Ready for WordPress upload"
