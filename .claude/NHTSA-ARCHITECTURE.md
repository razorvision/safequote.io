# NHTSA Data Architecture Documentation

> **Purpose**: This document explains the hybrid CSV + API architecture for fetching, caching, and serving NHTSA (National Highway Traffic Safety Administration) vehicle safety ratings data in the SafeQuote WordPress theme.

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Data Sources](#data-sources)
3. [Multi-tier Caching Strategy](#multi-tier-caching-strategy)
4. [Smart Merge Logic](#smart-merge-logic)
5. [WP-Cron Scheduled Tasks](#wp-cron-scheduled-tasks)
6. [Database Schema](#database-schema)
7. [AJAX Endpoints](#ajax-endpoints)
8. [Class Reference](#class-reference)
9. [How to Add New Data Consumers](#how-to-add-new-data-consumers)
10. [Performance Benefits](#performance-benefits)
11. [Troubleshooting](#troubleshooting)

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          NHTSA Data Architecture                            │
└─────────────────────────────────────────────────────────────────────────────┘

                         DATA SOURCES
                              │
           ┌──────────────────┼──────────────────┐
           │                  │                  │
           ▼                  ▼                  ▼
    ┌─────────────┐   ┌─────────────┐   ┌─────────────┐
    │  NHTSA CSV  │   │  NHTSA API  │   │   Manual    │
    │  (Primary)  │   │ (Fallback)  │   │  Override   │
    └──────┬──────┘   └──────┬──────┘   └──────┬──────┘
           │                  │                  │
           └──────────┬───────┴──────────────────┘
                      │
                      ▼
              ┌───────────────┐
              │  Smart Merge  │  ← CSV nulls don't overwrite API data
              │     Logic     │
              └───────┬───────┘
                      │
                      ▼
    ┌─────────────────────────────────────────────────┐
    │              MULTI-TIER CACHE                   │
    │                                                 │
    │  L1: WordPress Transients (24 hours)           │
    │      ├── Fast in-memory cache                  │
    │      └── Lost on cache flush                   │
    │                      │                         │
    │                      ▼                         │
    │  L2: Database Table (Permanent)                │
    │      ├── wp_nhtsa_vehicle_cache                │
    │      └── Survives cache flushes                │
    │                      │                         │
    │                      ▼                         │
    │  L3: Live API Call (Fallback)                  │
    │      ├── Called when DB query fails            │
    │      └── Rate-limited by NHTSA                 │
    │                      │                         │
    │                      ▼                         │
    │  L4: Stale Cache (Last Resort)                 │
    │      ├── Previous successful fetch             │
    │      └── Prevents errors during downtime       │
    │                                                 │
    └─────────────────────────────────────────────────┘
                      │
                      ▼
    ┌─────────────────────────────────────────────────┐
    │              DATA CONSUMERS                     │
    │                                                 │
    │  • AJAX Endpoints (frontend filtering)         │
    │  • Template Functions (PHP rendering)          │
    │  • Admin Dashboard (sync status)               │
    │  • Top Safety Picks (homepage display)         │
    │                                                 │
    └─────────────────────────────────────────────────┘
```

---

## Data Sources

### 1. CSV Import (Primary Source)

**File**: `inc/class-nhtsa-csv-import.php`

The official NHTSA dataset is the primary data source, providing bulk vehicle safety ratings.

```
Source URL: https://static.nhtsa.gov/nhtsa/downloads/Safercar/Safercar_data.csv
Check URL:  https://www.nhtsa.gov/nhtsa-datasets-and-apis
```

**How it works**:
1. WP-Cron checks the remote file's `Last-Modified` header daily at 2 AM
2. If updated, downloads the CSV to `wp-content/cache/nhtsa/`
3. Parses and imports all vehicles into `wp_nhtsa_vehicle_cache`
4. Marks records with `rating_source = 'csv'`

**CSV Data Fields**:
```
MODEL_YR      → year
MAKE          → make
MODEL         → model
OVERALL_STARS → nhtsa_overall_rating
FRNT_STARS    → front_crash
SIDE_STARS    → side_crash
ROLLOVER_STARS → rollover_crash
```

### 2. Live API (Fallback/Gap-filling)

**File**: `inc/class-nhtsa-cache.php`

The NHTSA REST API fills gaps when CSV data is incomplete or unavailable.

```
API Base:  https://api.nhtsa.gov/SafetyRatings
Timeout:   15 seconds
Retries:   3 attempts
```

**API Endpoints Used**:
- `/modelyear/{year}` - Get available years
- `/modelyear/{year}/make/{make}` - Get models for year/make
- `/modelyear/{year}/make/{make}/model/{model}` - Get VehicleIds
- `/VehicleId/{id}` - Get detailed ratings

**API Response Fields**:
```php
'VehicleId'              → Unique NHTSA vehicle identifier
'OverallRating'          → Overall safety rating (1-5)
'OverallFrontCrashRating'
'OverallSideCrashRating'
'RolloverRating'
'VehiclePicture'         → Official vehicle image URL
'ComplaintsCount'
'RecallsCount'
'InvestigationCount'
'NHTSAElectronicStabilityControl'
'NHTSAForwardCollisionWarning'
'NHTSALaneDepartureWarning'
```

---

## Multi-tier Caching Strategy

### Layer 1: WordPress Transients (24 hours)

**Purpose**: Fast, in-memory access for frequently requested vehicles

```php
// Cache key format
$cache_key = "nhtsa_rating_{$year}_{$make}_{$model}";

// Set transient
set_transient($cache_key, $data, 24 * HOUR_IN_SECONDS);

// Get transient
$cached = get_transient($cache_key);
```

**Characteristics**:
- Fastest access layer
- Volatile (lost on cache flush)
- TTL: 24 hours
- Automatically repopulated from database layer

### Layer 2: Database Storage (Permanent)

**Purpose**: Persistent storage that survives cache flushes

**Table**: `wp_nhtsa_vehicle_cache`

```php
// Query database cache
$db_cache = SafeQuote_NHTSA_Database::get_vehicle_cache($year, $make, $model);

// Update database cache
SafeQuote_NHTSA_Database::update_vehicle_cache(
    $year,
    $make,
    $model,
    $nhtsa_data,
    null,    // NULL = permanent storage (never expires)
    'api'    // Source: 'csv', 'api', or 'manual'
);
```

**Key Features**:
- Records are permanent (never auto-deleted)
- Survives WordPress cache flushes
- Indexed for fast lookups
- Tracks data source for smart merge logic

### Layer 3: Live API Call

**Purpose**: Fetch fresh data when cache layers miss

```php
$api_data = SafeQuote_NHTSA_Cache::fetch_from_api($year, $make, $model);
```

**Rate Limiting**:
- 100ms delay between batch API calls
- NHTSA imposes external rate limits
- Retry with exponential backoff on failure

### Layer 4: Stale Cache Fallback

**Purpose**: Prevent errors during API downtime

```php
// Returns cached data even if "expired" (though we don't expire DB records)
$stale = self::get_stale_cache($year, $make, $model);
```

### Cache Retrieval Flow

```php
// From class-nhtsa-cache.php: get_vehicle_rating()

function get_vehicle_rating($year, $make, $model) {
    // L1: Transient cache (24h TTL)
    $cached = get_transient($cache_key);
    if ($cached) return $cached;

    // L2: Database cache
    $db_cache = SafeQuote_NHTSA_Database::get_vehicle_cache($year, $make, $model);
    if ($db_cache && $db_cache->nhtsa_overall_rating) {
        set_transient($cache_key, $data, HOUR_IN_SECONDS * 24);
        return $data;
    }

    // L2b: Database has record but no rating - try API to fill gap
    if ($db_cache) {
        $api_data = self::fetch_from_api($year, $make, $model);
        if ($api_data) {
            SafeQuote_NHTSA_Database::update_vehicle_cache(..., 'api');
            return $api_data;
        }
        return $db_cache_data; // Return what we have
    }

    // L3: Live API for new vehicles
    $data = self::fetch_from_api($year, $make, $model);
    if ($data) {
        set_transient($cache_key, $data, HOUR_IN_SECONDS * 24);
        SafeQuote_NHTSA_Database::update_vehicle_cache(..., 'api');
        return $data;
    }

    // L4: Stale cache fallback
    $stale = self::get_stale_cache($year, $make, $model);
    if ($stale) return $stale;

    return false; // All layers failed
}
```

---

## Smart Merge Logic

The system uses "smart merge" to prevent less-complete data from overwriting better data.

**Rule**: CSV null values never overwrite existing API data

```php
// From class-nhtsa-database.php: update_vehicle_cache()

// Smart merge: Don't overwrite API data with CSV null/empty
if ($existing && $source === 'csv' && $new_rating === null && $existing->rating_source === 'api') {
    error_log("[NHTSA DB] Skipping CSV update - protecting API data");
    return true; // Don't overwrite
}
```

**Scenario Examples**:

| Existing Source | Existing Rating | New Source | New Rating | Result |
|-----------------|-----------------|------------|------------|--------|
| None            | N/A             | CSV        | 5.0        | Accept CSV |
| CSV             | NULL            | API        | 4.5        | Accept API |
| API             | 4.5             | CSV        | NULL       | **Keep API** |
| CSV             | 4.0             | CSV        | 5.0        | Accept new CSV |
| API             | 4.5             | API        | 5.0        | Accept new API |

---

## WP-Cron Scheduled Tasks

**File**: `inc/class-nhtsa-init.php`

### Schedule Overview

| Task | Hook | Interval | Time | Purpose |
|------|------|----------|------|---------|
| CSV Sync | `safequote_nhtsa_csv_sync` | Daily | 2:00 AM | Check/download NHTSA CSV |
| Validation | `safequote_nhtsa_validate` | Daily | 3:00 AM | Generate health reports |
| Cleanup | `safequote_nhtsa_cleanup` | Daily | 4:00 AM | Clear expired transients |
| Auto Batch | `safequote_nhtsa_auto_batch_fill` | 3 min | Continuous | Fill missing API data |

### Task Details

#### 1. CSV Sync (`cron_csv_sync`)
```php
// Checks if NHTSA CSV was updated
SafeQuote_NHTSA_CSV_Import::sync_csv_data();
```

#### 2. Validation (`cron_validate`)
```php
// Generates sync status report
SafeQuote_NHTSA_Validate::validate_sync();
```

#### 3. Cleanup (`cron_cleanup`)
```php
// Clears expired cache entries (transients only)
SafeQuote_NHTSA_Validate::cleanup_expired_cache();
```

#### 4. Auto Batch Fill (`cron_auto_batch_fill`)
```php
// Processes 50 vehicles at a time from NHTSA API
// Fills gaps where CSV has null ratings
self::batch_fill_missing_ratings();
```

### Managing Cron Tasks

```php
// Disable all NHTSA crons (from admin page)
SafeQuote_NHTSA_Init::disable_all_crons();

// Re-enable all NHTSA crons
SafeQuote_NHTSA_Init::enable_all_crons();
```

---

## Database Schema

**File**: `inc/class-nhtsa-database.php`

### Table: `wp_nhtsa_vehicle_cache`

Primary storage for vehicle safety data.

```sql
CREATE TABLE wp_nhtsa_vehicle_cache (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    year YEAR NOT NULL,
    make VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    vehicle_id INT,
    nhtsa_overall_rating DECIMAL(3,1),
    front_crash DECIMAL(3,1),
    side_crash DECIMAL(3,1),
    rollover_crash DECIMAL(3,1),
    vehicle_picture VARCHAR(500),
    nhtsa_data LONGTEXT NOT NULL,          -- Full JSON response
    rating_source ENUM('csv', 'api', 'manual') DEFAULT 'csv',
    cached_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,                    -- NULL = permanent
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_vehicle (year, make, model),
    KEY idx_year_make_model (year, make, model),
    KEY idx_vehicle_id (vehicle_id),
    KEY idx_rating_source (rating_source)
);
```

### Table: `wp_nhtsa_sync_log`

Tracks sync status for each vehicle.

```sql
CREATE TABLE wp_nhtsa_sync_log (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    year YEAR NOT NULL,
    make VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    vehicle_id INT,
    nhtsa_rating DECIMAL(3,1),
    has_data BOOLEAN DEFAULT FALSE,
    sync_attempt INT DEFAULT 0,
    last_attempt DATETIME,
    next_attempt DATETIME,
    error_message TEXT,
    status ENUM('pending', 'syncing', 'success', 'no_data', 'failed') DEFAULT 'pending',
    sync_hash VARCHAR(64),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_vehicle (year, make, model),
    KEY idx_status (status),
    KEY idx_next_attempt (next_attempt)
);
```

---

## AJAX Endpoints

**File**: `inc/ajax-handlers.php`

All AJAX handlers include built-in transient caching.

### Available Endpoints

| Action | Function | Method | Purpose |
|--------|----------|--------|---------|
| `search_vehicles` | `safequote_ajax_search_vehicles` | POST | Search with filters |
| `get_nhtsa_rating` | `safequote_ajax_get_nhtsa_rating` | GET | Get rating by year/make/model |
| `get_makes` | `safequote_ajax_get_makes` | GET | Get makes (optionally by year) |
| `get_models` | `safequote_ajax_get_models` | GET | Get models for make |
| `get_years` | `safequote_ajax_get_years` | GET | Get available years |

### Usage Examples

#### Search Vehicles
```javascript
jQuery.ajax({
    url: safequote_ajax.ajax_url,
    type: 'POST',
    data: {
        action: 'search_vehicles',
        nonce: safequote_ajax.nonce,
        year: 2024,
        make: 'Toyota',
        model: 'Camry',
        minSafetyRating: 4
    },
    success: function(response) {
        console.log(response.data.vehicles);
    }
});
```

#### Get NHTSA Rating
```javascript
jQuery.ajax({
    url: safequote_ajax.ajax_url,
    type: 'GET',
    data: {
        action: 'get_nhtsa_rating',
        nonce: safequote_ajax.nonce,
        year: 2024,
        make: 'Toyota',
        model: 'Camry'
    },
    success: function(response) {
        // Returns array of all matching variants
        console.log(response.data); // [{OverallRating: 5, ...}, ...]
    }
});
```

#### Get Dynamic Dropdown Data
```javascript
// Get years (for first dropdown)
jQuery.get(safequote_ajax.ajax_url, {
    action: 'get_years',
    nonce: safequote_ajax.nonce
});

// Get makes filtered by year
jQuery.get(safequote_ajax.ajax_url, {
    action: 'get_makes',
    nonce: safequote_ajax.nonce,
    year: 2024
});

// Get models filtered by make (and optionally year)
jQuery.get(safequote_ajax.ajax_url, {
    action: 'get_models',
    nonce: safequote_ajax.nonce,
    make: 'Toyota',
    year: 2024
});
```

---

## Class Reference

### Core Classes

| Class | File | Purpose |
|-------|------|---------|
| `SafeQuote_NHTSA_Init` | `class-nhtsa-init.php` | Initialization, cron scheduling |
| `SafeQuote_NHTSA_Cache` | `class-nhtsa-cache.php` | Multi-tier caching, API calls |
| `SafeQuote_NHTSA_Database` | `class-nhtsa-database.php` | Database CRUD operations |
| `SafeQuote_NHTSA_CSV_Import` | `class-nhtsa-csv-import.php` | CSV download and import |
| `SafeQuote_NHTSA_Fetch` | `class-nhtsa-fetch.php` | Batch API fetching |
| `SafeQuote_NHTSA_Validate` | `class-nhtsa-validate.php` | Health checks, validation |
| `SafeQuote_NHTSA_Discover` | `class-nhtsa-discover.php` | Vehicle discovery |
| `SafeQuote_NHTSA_Admin_Page` | `class-nhtsa-admin-page.php` | Admin interface |

### Key Methods

#### SafeQuote_NHTSA_Cache

```php
// Get rating with multi-tier caching
SafeQuote_NHTSA_Cache::get_vehicle_rating($year, $make, $model);

// Fetch directly from API
SafeQuote_NHTSA_Cache::fetch_from_api($year, $make, $model);

// Get available years/makes/models
SafeQuote_NHTSA_Cache::get_available_years();
SafeQuote_NHTSA_Cache::get_makes_for_year($year);
SafeQuote_NHTSA_Cache::get_models_for_year_make($year, $make);

// Clear caches
SafeQuote_NHTSA_Cache::clear_transients();
SafeQuote_NHTSA_Cache::clear_vehicle_cache($year, $make, $model);
```

#### SafeQuote_NHTSA_Database

```php
// Get/update vehicle cache
SafeQuote_NHTSA_Database::get_vehicle_cache($year, $make, $model);
SafeQuote_NHTSA_Database::update_vehicle_cache($year, $make, $model, $data, $expires, $source);

// Get/update sync log
SafeQuote_NHTSA_Database::get_sync_log($year, $make, $model);
SafeQuote_NHTSA_Database::update_sync_log($year, $make, $model, $status, $data);

// Statistics
SafeQuote_NHTSA_Database::get_sync_stats();
```

---

## How to Add New Data Consumers

### Option 1: Use Existing AJAX Endpoints (Recommended)

For frontend JavaScript:

```javascript
// Use the existing search_vehicles endpoint
jQuery.post(safequote_ajax.ajax_url, {
    action: 'search_vehicles',
    nonce: safequote_ajax.nonce,
    year: 2024,
    minSafetyRating: 4.5
}, function(response) {
    // Process response.data.vehicles
});
```

### Option 2: Use PHP Template Functions

For server-side rendering:

```php
// Get vehicles from NHTSA database with filters
require_once SAFEQUOTE_THEME_DIR . '/inc/vehicle-data-nhtsa.php';

$vehicles = safequote_get_vehicles_from_nhtsa(array(
    'limit'      => 12,
    'min_rating' => 4.0,
    'year'       => 2024,
    'make'       => 'Toyota'
));

// Get top safety picks
$top_picks = safequote_get_top_safety_picks_from_db(6, 4.5);

// Get specific vehicle rating (with caching)
$rating = safequote_get_nhtsa_rating(2024, 'Toyota', 'Camry');
```

### Option 3: Direct Database Query

For custom queries:

```php
global $wpdb;
$table = $wpdb->prefix . 'nhtsa_vehicle_cache';

$results = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table
     WHERE year >= %d
     AND nhtsa_overall_rating >= %f
     ORDER BY nhtsa_overall_rating DESC
     LIMIT %d",
    2020, 4.0, 20
));
```

### Option 4: Add New AJAX Endpoint

```php
// In inc/ajax-handlers.php or functions.php

function safequote_ajax_get_custom_data() {
    // Verify nonce
    if (!wp_verify_nonce($_GET['nonce'], 'safequote_ajax_nonce')) {
        wp_die('Security check failed');
    }

    // Check transient cache first
    $cache_key = 'custom_data_' . md5(serialize($_GET));
    $cached = get_transient($cache_key);

    if ($cached !== false) {
        wp_send_json_success($cached);
        return;
    }

    // Query database
    global $wpdb;
    $table = $wpdb->prefix . 'nhtsa_vehicle_cache';
    $results = $wpdb->get_results("...");

    // Cache for 24 hours
    set_transient($cache_key, $results, DAY_IN_SECONDS);

    wp_send_json_success($results);
}
add_action('wp_ajax_get_custom_data', 'safequote_ajax_get_custom_data');
add_action('wp_ajax_nopriv_get_custom_data', 'safequote_ajax_get_custom_data');
```

---

## Performance Benefits

### Caching Metrics

| Layer | Average Response Time | Cache Duration |
|-------|----------------------|----------------|
| L1 Transient | ~5ms | 24 hours |
| L2 Database | ~20ms | Permanent |
| L3 API | ~500-2000ms | N/A |
| L4 Stale | ~25ms | N/A |

### Why This Architecture?

1. **High Performance**: Most requests hit L1 (transient) cache
2. **Resilience**: 4 fallback layers ensure data availability
3. **Data Quality**: Smart merge preserves best available data
4. **Efficiency**: Bulk CSV import reduces API calls
5. **Automatic**: WP-Cron keeps data fresh without manual intervention

### Cache Hit Rate Optimization

- Transient cache serves ~95% of requests
- Database cache catches transient misses
- API calls only for new vehicles or gap-filling
- Stale cache prevents errors during API outages

---

## Troubleshooting

### Common Issues

#### 1. No vehicles showing
```bash
# Check if database tables exist
SELECT * FROM wp_nhtsa_vehicle_cache LIMIT 5;

# Check if CSV import ran
SELECT * FROM wp_options WHERE option_name LIKE '%nhtsa%';
```

#### 2. Cron not running
```php
// Check scheduled crons
wp_next_scheduled('safequote_nhtsa_csv_sync');
wp_next_scheduled('safequote_nhtsa_auto_batch_fill');

// Manually trigger CSV sync
SafeQuote_NHTSA_CSV_Import::sync_csv_data();
```

#### 3. API rate limiting
- Auto batch runs every 3 minutes with 50 vehicle limit
- 100ms delay between API calls
- Exponential backoff on failures

#### 4. Stale data
```php
// Clear all transients
SafeQuote_NHTSA_Cache::clear_transients();

// Force CSV reimport
SafeQuote_NHTSA_CSV_Import::force_reimport();
```

### Debug Logging

All NHTSA operations log to `wp-content/debug.log`:

```
[NHTSA] ✓ Transient HIT: 2024 Toyota Camry
[NHTSA] → Database has record but no rating, trying API
[NHTSA] ✓ API filled gap: 2024 Toyota Camry
[NHTSA DB] Skipping CSV update - protecting API data
[NHTSA Cron] Auto batch: Processed 50, Updated 23
```

### Admin Page

Access the admin dashboard at:
**WordPress Admin > NHTSA Data**

Features:
- Database status overview
- Manual CSV import trigger
- Batch fill missing ratings
- Cron status monitoring
- System information

---

## Related Files Reference

```
wp-content/themes/safequote-traditional/
├── inc/
│   ├── class-nhtsa-init.php          # Initialization & cron setup
│   ├── class-nhtsa-cache.php         # Multi-tier caching
│   ├── class-nhtsa-database.php      # Database operations
│   ├── class-nhtsa-csv-import.php    # CSV import logic
│   ├── class-nhtsa-fetch.php         # Batch API fetching
│   ├── class-nhtsa-validate.php      # Validation & health
│   ├── class-nhtsa-discover.php      # Vehicle discovery
│   ├── class-nhtsa-admin-page.php    # Admin interface
│   ├── ajax-handlers.php             # AJAX endpoints
│   └── vehicle-data-nhtsa.php        # Template functions
└── assets/js/
    ├── filters.js                     # Frontend filtering
    └── top-safety-picks.js            # Top picks display
```

---

## Related Issues

- Epic: #31 (NHTSA Integration)
- References: #32, #33, #35

---

**Last Updated**: November 2024
**Version**: 1.0
