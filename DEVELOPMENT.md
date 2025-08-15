# Yespo CDP WooCommerce Plugin - Developer Documentation

This document provides comprehensive technical documentation for developers working on the Yespo CDP WooCommerce plugin.

## Table of Contents

- [Purpose and Goals](#purpose-and-goals)
- [Database Schema](#database-schema)
- [Plugin Installation Process](#plugin-installation-process)
- [Authentication Flow](#authentication-flow)
- [Data Export System](#data-export-system)
- [Web Tracking Implementation](#web-tracking-implementation)
- [Web Push Notifications](#web-push-notifications)
- [Plugin Uninstallation](#plugin-uninstallation)
- [WordPress Dependencies](#wordpress-dependencies)
- [Development Setup](#development-setup)

## Purpose and Goals

The plugin's main goal is to simplify the integration of WooCommerce online stores with the Yespo platform and provide all necessary functionality without manual code intervention.

### Core Features

The plugin implements:

- **Automatic data transfer** from WooCommerce to Yespo for current and historical customer data (contacts) - creation, updates, standard and GDPR deletion
- **Automatic order data transfer** from WooCommerce to Yespo for current and historical orders - creation, updates, status changes
- **Automatic domain registration** in Yespo (for obtaining general and web push scripts)
- **Automatic script installation** (site tracking, push) and service worker for push notifications on the site
- **Web tracking configuration** for collecting user actions on the site (product page views, cart additions, etc.)
- **Error logging, events and export status tracking**

## Database Schema

### Tables Created During Installation

The plugin creates the following tables in the database (all prefixed with WordPress table prefix):

#### 1. `{prefix}yespo_auth_log`
Logs authorization attempts with Yespo.
```sql
CREATE TABLE {prefix}yespo_auth_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    api_key VARCHAR(255),
    response VARCHAR(10),
    time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 2. `{prefix}yespo_contact_log` 
Logs user-related actions (creation, updates, deletion).
```sql
CREATE TABLE {prefix}yespo_contact_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50),
    log_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 3. `{prefix}yespo_curl_json`
Logs all export data sent to Yespo (users and orders).
```sql
CREATE TABLE {prefix}yespo_curl_json (
    id INT AUTO_INCREMENT PRIMARY KEY,
    text LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 4. `{prefix}yespo_errors`
Records errors that may occur during data export to Yespo.
```sql
CREATE TABLE {prefix}yespo_errors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    error VARCHAR(10),
    time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 5. `{prefix}yespo_export_status_log`
Tracks export processes, data quantities, exported counts, and status.
```sql
CREATE TABLE {prefix}yespo_export_status_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    export_type ENUM('users', 'orders'),
    total INT,
    exported INT,
    status ENUM('active', 'completed', 'stopped', 'error'),
    code VARCHAR(10),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 6. `{prefix}yespo_order_log`
Logs exported orders.
```sql
CREATE TABLE {prefix}yespo_order_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    action VARCHAR(50),
    status VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 7. `{prefix}yespo_queue`
Records the start and completion of historical user data exports.
```sql
CREATE TABLE {prefix}yespo_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(50),
    export_status ENUM('STARTED', 'FINISHED'),
    local_status VARCHAR(50)
);
```

#### 8. `{prefix}yespo_queue_items`
Records email addresses of users whose data has been sent to Yespo.
```sql
CREATE TABLE {prefix}yespo_queue_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(50),
    contact_id VARCHAR(255)
);
```

#### 9. `{prefix}yespo_queue_orders`
Records data about current order exports to Yespo.
```sql
CREATE TABLE {prefix}yespo_queue_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    yespo_status ENUM('STARTED', 'FINISHED')
);
```

#### 10. `{prefix}yespo_removed_users`
Records users deleted via GDPR.
```sql
CREATE TABLE {prefix}yespo_removed_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255),
    time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Cron Jobs Created

- `yespo_export_data_cron` - Main data export job
- `yespo_script_cron_event` - Script validation and cleanup job

## Authentication Flow

### API Key Authorization

1. **Form Display**: When first opening the plugin page, an API key input form is displayed via JavaScript method `showApiKeyForm()` in the `YespoExportData` class.

2. **Key Submission**: The entered key is sent via `checkSynchronization()` method through XMLHttpRequest to the backend, processed by the `wp_ajax_yespo_check_api_authorization_yespo` hook, which calls `yespo_check_api_authorization_function()`.

3. **Key Validation**: This function passes the key to the `send_keys()` method of the `Yespo_Account` class. If Yespo responds with status 200, the API key is stored in `yespo_options` under the `yespo_api_key` property.

### Tracking Script Initialization

1. **Script Retrieval**: Immediately after authorization, web tracking script initialization is performed via the `make_tracking_script()` method of the `Yespo_Web_Tracking_Script` class.

2. **Script Storage**: On successful script retrieval, it's stored in the `yespo_tracking_script` property in `yespo_options`.

3. **Success Message**: The frontend `addSuccessMessage()` method displays a success message about tracking script installation.

### Account Name Retrieval

1. **Account Request**: A request is made to get the Yespo account name using the `getAccountYespoName()` method of the `YespoExportData` class.

2. **Server Processing**: The server intercepts the request via the `wp_ajax_yespo_get_account_yespo_name` hook, first checking for `yespo_username` property in options. If absent, it calls Yespo via the `get_profile_name()` method of the `Yespo_Account` class.

## Data Export System

### Export Count Initialization

After receiving a positive response (code 200), the plugin initializes data export counting:

1. **User Count**: The `getNumberDataExport()` method calls `getRequest()` to check for users to export, processed by `wp_ajax_yespo_get_users_total_export` hook, with count generated by `get_users_export_count()` method of `Yespo_Export_Users` class.

2. **Order Count**: Similarly, order export requests are processed by `wp_ajax_yespo_get_orders_total_export` hook, with count from `get_export_orders_count()` method of `Yespo_Export_Orders` class.

### Historical Data Export

#### Users Export Process

**Startup Conditions:**
- Active user export record exists in `yespo_export_status_log`
- No errors (0, 429, 500) from previous session
- Previous cron iteration completed

**Initialization:**
- Start time
- Total user count
- Exported contact count
- Current status
- API response code

**Processing Loop (do-while):**
- Time-limited to 7.5 seconds or maximum 3 iterations per minute
- Selects 2000 user IDs without `yespo_contact_id` meta, above `yespo_highest_exported_user`

**Batch Formation and Sending:**
- Data mapped via `create_bulk_export_array()` (`Yespo_Contact_Mapping`)
- Sent via `export_bulk_users()` method (`Yespo_Contact`)

**API Response Handling:**
- **200**: Adds `yespo_contact_id`, updates `yespo_highest_exported_user`
- **400**: Adds `yespo_bad_request` meta, continues as with 200
- **429/500**: Marks batch as FINISHED, logs error, 5-minute delay
- **401/0**: Stops export, displays admin notice

#### Orders Export Process

**Startup Conditions:**
- Orders exist without `sent_order_to_yespo` meta, modified >5 minutes ago
- No active export process
- Active order export record in `yespo_export_status_log`
- No previous session errors (0, 429, 500)

**Processing:**
- Selects up to 1000 order IDs without `sent_order_to_yespo` meta
- Data mapped via `create_bulk_order_export_array()` (`Yespo_Order_Mapping`)
- Sent via `create_bulk_orders_on_yespo()` (`Yespo_Order`)
- JSON payloads logged in `yespo_curl_json`

### Progress Bar Functionality

The progress bar shows overall export progress for both contacts and orders, updating in real-time.

#### User Export Initiation:
- If user count > 0, `route` method initiates export task creation
- `startExportUsers()` sends POST request
- `add_users_export_task()` (`Yespo_Export_Users`) creates record in `yespo_export_status_log`

#### Progress Updates:
- `checkExportStatus()` (`YespoExportData`) polls every 5 seconds
- `get_process_users_exported()` (`Yespo_Export_Users`) retrieves current data
- `updateProgress()` (`YespoExportData`) updates progress bar

#### Export Control:
- **Stop**: `stopExportData()` → `stop_export_users()` → status = 'stopped'
- **Resume**: `resumeExportData()` → `resume_export_users()` → status = 'active'

### Current Data Export

#### User Data Export

**Profile Updates:**
- `profile_update` hook triggers `yespo_update_user_profile_function()`
- `update_woo_profile_yespo()` (`Yespo_Contact`) maps data via `update_woo_to_yes()` (`Yespo_Contact_Mapping`)

**User Deletion:**
- **Soft Delete**: `delete_user` hook → email recorded in `yespo_removed_users` → `delete_from_yespo()` (`Yespo_Contact`)
- **GDPR Delete**: `wp_privacy_personal_data_erased` hook → `yespo_clean_user_data_after_data_erased_function()` → logged in `yespo_contact_log` → processed by cron within 15-20 minutes

#### Order Data Export

- `schedule_export_orders()` (`Yespo_Export_Orders`) selects changed order IDs >5 minutes old
- Orders processed via `order_woo_to_yes()` (`Yespo_Order_Mapping`)
- Sent via `create_order_on_yespo()` (`Yespo_Order`)

## Web Tracking Implementation

### Frontend Events (via eS.js function)

#### CategoryPage
- **Trigger**: User opens category page
- **Data**: Category name
- **Class**: `Yespo_Category_Event`
- **Method**: `sendCategory()`

#### ProductPage
- **Trigger**: User opens product page
- **Data**: Product ID, price, availability
- **Class**: `Yespo_Product_Event`
- **Method**: `sendProduct()`

#### MainPage
- **Trigger**: User opens homepage
- **Data**: "MainPage" identifier
- **Class**: `Yespo_Front_Event`
- **Method**: `sendFront()`

#### NotFound
- **Trigger**: 404 page access
- **Data**: "NotFound" identifier
- **Class**: `Yespo_NotFound_Event`
- **Method**: `sendNotFound()`

#### StatusCartPage
- **Trigger**: Cart page access
- **Data**: "StatusCartPage" identifier
- **Class**: `Yespo_Cart_Event`
- **Method**: `get_cart_page()` → `sendCart()`

### Backend Events (via curl to https://tracker.yespo.io/api/v2)

#### CustomerData
- **WordPress Hook**: `profile_update` (registration, login, profile update)
- **WooCommerce Hook**: `woocommerce_thankyou` (after order completion)
- **Processing**: `handle_user_event()` method (`Yespo_User_Event`)

#### StatusCart
- **Add to Cart**: `woocommerce_add_to_cart` → `add_to_cart_event()` (`Yespo_Cart_Event`)
- **Quantity Update**: `woocommerce_after_cart_item_quantity_update` → `after_cart_item_quantity_update()` (`Yespo_Cart_Event`)
- **Item Removal**: `woocommerce_cart_item_removed` → `cart_item_removed()` (`Yespo_Cart_Event`)

#### PurchasedItems
- **Hook**: `woocommerce_thankyou`
- **Processing**: `send_order_to_yespo()` (`Yespo_Purchased_Event`)

### Tracking Parameters

- **orgId & webId**: Captured by `actionTenantIdWebId()` (`YespoTracker`), stored in session via `wp_ajax_nopriv_save_webid` hook
- **tenantId**: Obtained during first plugin activation, stored in `yespo_tenant_id` property of `yespo_options`

## Web Push Notifications

### Data Retrieval and Storage

After plugin update or first API key authorization:

1. **Domain Registration**: POST request to `https://yespo.io/api/v1/site/webpush/domains` via `send_post_data()` (`Yespo_Web_Push`) with parameters:
   - domain
   - serviceWorkerName
   - serviceWorkerPath
   - serviceWorkerScope

2. **Script Retrieval**: On 200 response, GET request to `https://yespo.io/api/v1/site/webpush/script?domain=...`

3. **Script Storage**: JSON response contains:
   - **script**: Added to options via `add_script_to_options()`
   - **serviceWorker**: Written to `push-yespo-sw.js` file via `write_script_to_file()`

### Script Usage

When scripts are saved, the plugin automatically inserts `yespo_webpush_script` value from `yespo_options` into site `<head>` via `wp_head` hook and `get_script_from_options()` method (`Yespo_Web_Push`).

## Plugin Uninstallation

### Database Cleanup

When user deletes the plugin, the following tables are removed:
- `{prefix}yespo_auth_log`
- `{prefix}yespo_contact_log`
- `{prefix}yespo_curl_json`
- `{prefix}yespo_errors`
- `{prefix}yespo_export_status_log`
- `{prefix}yespo_order_log`
- `{prefix}yespo_queue`
- `{prefix}yespo_queue_items`
- `{prefix}yespo_queue_orders`
- `{prefix}yespo_removed_users`

### Meta and Options Cleanup

**User Meta Removed:**
- `yespo_contact_id`
- `yespo_bad_request`

**Order Meta Removed:**
- `sent_order_to_yespo`
- `yespo_order_time`
- `yespo_customer_removed`
- `yespo_bad_request`

**Options Removed:**
- `yespo_options`
- `yespo-version`

**Cron Jobs Removed:**
- `yespo_export_data_cron`
- `yespo_script_cron_event`

### Reinstallation

If user reinstalls the plugin, all configuration starts fresh without data duplication.

## WordPress Dependencies

### WordPress Hooks Used

| Hook | Description | Usage |
|------|-------------|--------|
| `admin_notices` | Display admin notices | Show blocked outbound activity messages |
| `profile_update` | User profile created/updated | Send user data to Yespo, CustomerData events |
| `delete_user` | Before user deletion | Delete user from Yespo |
| `cron_schedules` | Add custom cron intervals | Historical/current data export, GDPR deletion |
| `wp_footer` | Add elements to footer | Web tracking code injection |
| `wp_login` | After successful login | CustomerData event tracking |
| `wp_head` | Add elements to head | Web push code injection |
| `wp_privacy_personal_data_erased` | After GDPR data erasure | Delete user from Yespo |
| `admin_enqueue_scripts` | Enqueue admin scripts/styles | Plugin JavaScript inclusion |

### WooCommerce Hooks Used

| Hook | Description | Usage |
|------|-------------|--------|
| `woocommerce_thankyou` | After successful order | PurchasedItems, CustomerData events |
| `woocommerce_add_to_cart` | After item added to cart | StatusCart event |
| `woocommerce_after_cart_item_quantity_update` | After cart quantity update | StatusCart event |
| `woocommerce_cart_item_removed` | After item removed from cart | StatusCart event |

### WordPress Functions Used

| Function | Purpose | Usage |
|----------|---------|--------|
| `wp_remote_request` | HTTP requests | Yespo API communication |
| `wp_localize_script` | Pass PHP data to JavaScript | Backend to frontend data transfer |
| `esc_html__` | Translate and escape HTML | Variable escaping and translation |
| `esc_js` | Escape for JavaScript | JavaScript variable escaping |
| `esc_url` | Escape URLs | URL escaping |
| `esc_sql` | Escape for SQL | SQL injection prevention |
| `wp_create_nonce` | Create CSRF tokens | Form protection |
| `wp_enqueue_script` | Enqueue JavaScript | Plugin script inclusion |
| `wp_send_json` | Send JSON responses | AJAX response handling |
| `get_option` | Get option values | Retrieve stored settings |
| `update_option` | Update option values | Store settings |
| `sanitize_text_field` | Sanitize text | Input cleaning |
| `update_user_meta` | Update user metadata | User marking |
| `get_user_by` | Get user object | User retrieval |
| `wp_json_encode` | Encode to JSON | Data transformation for Yespo |

### Global Objects Used

| Object | Description | Usage |
|--------|-------------|--------|
| `$wpdb` | WordPress database interface | Database operations |
| `WC` | WooCommerce main object | WooCommerce functionality access |
| `WC_Order` | WooCommerce order object | Order data management |

## Project Structure

```
yespo-cdp/
├── ajax/                   # AJAX handlers
│   ├── Ajax_Admin.php     # Admin AJAX endpoints
│   ├── Ajax.php           # Frontend AJAX endpoints
│   └── index.php          # Security index file
├── assets/                 # Frontend assets
│   ├── images/            # Plugin images and icons
│   └── index.php          # Security index file
├── backend/               # Admin panel functionality
│   ├── views/             # Admin template files
│   │   ├── admin.php      # Main admin view
│   │   ├── settings.php   # Settings page view
│   │   └── settings-2.php # Additional settings view
│   ├── ActDeact.php       # Plugin activation/deactivation
│   ├── Enqueue.php        # Admin asset enqueuing
│   ├── ImpExp.php         # Import/export functionality
│   ├── Notices.php        # Admin notices
│   └── Settings_Page.php  # Settings page controller
├── cli/                   # Command line interface
│   ├── Example.php        # CLI example implementation
│   └── index.php          # Security index file
├── engine/                # Core plugin engine
│   ├── Base.php           # Base plugin class
│   ├── Context.php        # Plugin context management
│   └── Initialize.php     # Plugin initialization
├── frontend/              # Frontend functionality
│   ├── Extras/            # Additional frontend features
│   │   └── Body_Class.php # Body class modifications
│   ├── Enqueue.php        # Frontend asset enqueuing
│   └── index.php          # Security index file
├── functions/             # WordPress hooks and functions
│   ├── debug.php          # Debug utilities
│   └── functions.php      # Main plugin functions
├── integrations/          # External service integrations
│   ├── esputnik/          # Yespo API integration
│   │   ├── Yespo_Account.php           # Account management
│   │   ├── Yespo_Contact.php           # Contact operations
│   │   ├── Yespo_Contact_Mapping.php   # Contact data mapping
│   │   ├── Yespo_Contact_Validation.php # Contact validation
│   │   ├── Yespo_Curl_Request.php      # HTTP request handler
│   │   ├── Yespo_Errors.php            # Error handling
│   │   ├── Yespo_Export_Orders.php     # Order export logic
│   │   ├── Yespo_Export_Service.php    # Export service coordinator
│   │   ├── Yespo_Export_Users.php      # User export logic
│   │   ├── Yespo_Localization.php      # Localization handler
│   │   ├── Yespo_Logging_Data.php      # Data logging
│   │   ├── Yespo_Order.php             # Order operations
│   │   └── Yespo_Order_Mapping.php     # Order data mapping
│   ├── webtracking/       # Web tracking functionality
│   │   ├── Yespo_Cart_Event.php        # Cart event tracking
│   │   ├── Yespo_Category_Event.php    # Category page tracking
│   │   ├── Yespo_Front_Event.php       # Homepage tracking
│   │   ├── Yespo_NotFound_Event.php    # 404 page tracking
│   │   ├── Yespo_Product_Event.php     # Product page tracking
│   │   ├── Yespo_Purchased_Event.php   # Purchase event tracking
│   │   ├── Yespo_User_Event.php        # User event tracking
│   │   ├── Yespo_Web_Tracking_Abstract.php     # Abstract tracking class
│   │   ├── Yespo_Web_Tracking_Aggregator.php   # Tracking aggregation
│   │   ├── Yespo_Web_Tracking_Curl_Request.php # Tracking HTTP requests
│   │   └── Yespo_Web_Tracking_Script.php       # Tracking script management
│   ├── Widgets/           # WordPress widgets
│   │   └── Yespo_Recent_Posts_Widget.php # Recent posts widget
│   ├── CMB.php            # Custom meta boxes
│   ├── Cron.php           # Cron job management
│   └── Template.php       # Template functionality
├── internals/             # Internal plugin components
│   ├── Block.php          # Gutenberg block functionality
│   ├── Shortcode.php      # Shortcode implementation
│   └── Transient.php      # Transient data management
├── languages/             # Translation files
│   ├── yespo-cdp-*.po     # Translation files for various languages
│   ├── yespo-cdp-*.mo     # Compiled translation files
│   └── yespo.pot          # Translation template
├── rest/                  # REST API endpoints
│   ├── Example.php        # REST API example
│   └── index.php          # Security index file
├── templates/             # Frontend templates
│   ├── content-demo.php   # Demo content template
│   └── index.php          # Security index file
├── composer.json          # PHP dependencies
├── package.json           # Node.js dependencies
├── yespo.php             # Main plugin file
├── uninstall.php         # Uninstall cleanup
├── readme.md             # Main documentation
├── DEVELOPMENT.md        # Developer documentation
└── LICENSE.txt           # License file
```

### Key Directory Purposes

| Directory | Purpose |
|-----------|---------|
| `ajax/` | Handles AJAX requests for both admin and frontend |
| `backend/` | Admin panel interface and functionality |
| `engine/` | Core plugin architecture and initialization |
| `frontend/` | Frontend user-facing functionality |
| `functions/` | WordPress hooks, filters, and utility functions |
| `integrations/esputnik/` | All Yespo API communication and data handling |
| `integrations/webtracking/` | Web tracking events and script management |
| `internals/` | WordPress-specific components (blocks, shortcodes) |
| `languages/` | Internationalization and translation files |

## Development Setup

### Prerequisites

1. **WordPress Installation**: Functional WordPress installation
2. **WooCommerce Plugin**: Installed and activated
3. **Development Tools**: 
   - Git
   - Composer
   - Node.js (for asset building)

### Setup Steps

1. **Clone Repository**:
   ```bash
   cd /wp-content/plugins
   git clone https://github.com/ardas/yespo-cdp.git
   ```

2. **Navigate to Directory**:
   ```bash
   cd yespo-cdp
   ```

3. **Install Dependencies**:
   ```bash
   composer install
   ```

4. **Update Autoloader**:
   ```bash
   composer dumpautoload -o
   ```

5. **Activate Plugin**: Via WordPress admin or WP-CLI

### File Locations

| Path | Description |
|------|-------------|
| `/wp-content/plugins` | Plugin installation directory |
| `/wp-content/uploads` | Web push file and tracking logs storage |
| `admin-ajax.php` | AJAX request handling |

---

This documentation provides a comprehensive technical reference for developers working on the Yespo CDP WooCommerce plugin. For additional information, refer to the main README.md file and plugin source code.
