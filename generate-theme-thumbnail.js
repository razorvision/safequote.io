#!/usr/bin/env node
/**
 * SafeQuote Theme - Thumbnail Generator
 * Generates a screenshot for the WordPress theme
 * Run: node generate-theme-thumbnail.js
 */

import { chromium } from 'playwright';
import http from 'http';
import path from 'path';
import fs from 'fs';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const DEV_SERVER_URL = 'http://localhost:3000';
const SCREENSHOT_PATH = 'wp-content/themes/safequote-theme/screenshot.png';
const THUMBNAIL_WIDTH = 1200;
const THUMBNAIL_HEIGHT = 900;

// Check if dev server is running
async function isServerRunning() {
  return new Promise((resolve) => {
    const req = http.get(DEV_SERVER_URL, { timeout: 2000 }, () => {
      resolve(true);
    });
    req.on('error', () => resolve(false));
    req.on('timeout', () => resolve(false));
  });
}

async function generateThumbnail() {
  console.log('📸 Generating theme thumbnail...');

  // Check if dev server is running
  const serverRunning = await isServerRunning();
  if (!serverRunning) {
    console.error('❌ Dev server is not running at ' + DEV_SERVER_URL);
    console.error('   Start it with: npm run dev');
    process.exit(1);
  }

  let browser;
  try {
    browser = await chromium.launch({ headless: true });
    const page = await browser.newPage({
      viewport: { width: THUMBNAIL_WIDTH, height: THUMBNAIL_HEIGHT },
    });

    console.log(`🌐 Navigating to ${DEV_SERVER_URL}...`);
    await page.goto(DEV_SERVER_URL, { waitUntil: 'networkidle' });

    // Wait for main content to load
    await page.waitForSelector('#root', { timeout: 10000 });

    // Wait a bit for animations
    await page.waitForTimeout(2000);

    const screenshotPath = path.resolve(SCREENSHOT_PATH);
    await page.screenshot({ path: screenshotPath, fullPage: false });

    console.log(`✅ Screenshot saved: ${SCREENSHOT_PATH}`);
    console.log(`📐 Size: ${THUMBNAIL_WIDTH}x${THUMBNAIL_HEIGHT}px`);

    const stats = fs.statSync(screenshotPath);
    console.log(`📦 File size: ${(stats.size / 1024).toFixed(2)}KB`);

    await page.close();
  } catch (error) {
    console.error('❌ Error generating thumbnail:', error.message);
    process.exit(1);
  } finally {
    if (browser) {
      await browser.close();
    }
  }
}

generateThumbnail();
