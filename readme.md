# Yespo CDP for WooCommerce

A WordPress plugin that integrates WooCommerce with the Yespo Customer Data Platform for marketing automation, web tracking, and customer data synchronization.

[![WordPress](https://img.shields.io/badge/WordPress-6.5.5+-blue.svg)](https://wordpress.org/)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-Compatible-96588a.svg)](https://woocommerce.com/)
[![PHP](https://img.shields.io/badge/PHP-7.4+-787CB5.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPLv2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

**Contributors:** Yespo Marketing Automation & Customer Data Platform  
**Tags:** marketing automation, personalization, customer segmentation, CDP, woocommerce, ecommerce, web tracking  
**Requires at least:** WordPress 6.5.5  
**Tested up to:** 6.7.1  
**Stable tag:** 1.1.2  
**Requires PHP:** 7.4  
**License:** GPLv2 or later

## Table of Contents

- [🚀 Quick Start for Developers](#-quick-start-for-developers)
- [🏗️ Architecture & Development](#%EF%B8%8F-architecture--development)
  - [Database Schema](#database-schema)
  - [Cron Jobs](#cron-jobs)
  - [AJAX Endpoints](#ajax-endpoints)
  - [Core Components](#core-components)
- [🤝 Contributing](#-contributing)
- [📦 Installation](#-installation)
- [✨ Features Overview](#-features-overview)
- [📝 Changelog](#-changelog)
- [🆘 Support](#-support)

## Overview

This plugin integrates WooCommerce stores with [Yespo CDP](https://yespo.io/?utm_source=wordpress&utm_medium=referral&utm_campaign=woocommerce), enabling:

- **Customer Data Sync**: Automatic synchronization of contacts and orders
- **Web Tracking**: Track user behavior, cart events, and purchases
- **Real-time Updates**: Live data updates for customer profiles and orders
- **Marketing Automation**: Enable targeted campaigns through Yespo platform

## 🚀 Quick Start for Developers

### Requirements

- **Node.js**: >= 14 (for building assets)
- **Composer**: for PHP dependencies
- **WordPress**: >= 6.5.5
- **WooCommerce**: Latest version recommended
- **PHP**: >= 7.4

### Local Development Setup

1. **Clone the repository**:
   ```bash
   cd /path/to/wp-content/plugins
   git clone https://github.com/ardas/yespo-cdp.git yespo-cdp
   cd yespo-cdp
   ```

2. **Install dependencies and build**:
   ```bash
   composer install
   npm install
   npm run build
   ```

3. **Activate the plugin**:
   ```bash
   # Via WP-CLI
   wp plugin activate yespo-cdp
   
   # Or activate through WordPress admin panel
   ```

4. **Configure API connection**: Enter your Yespo API key in the plugin settings to start data synchronization.

### Useful WP-CLI Commands

```bash
# Manual cron execution (useful for testing)
wp cron event run yespo_export_data_cron
wp cron event run yespo_script_cron_event

# Check plugin status
wp plugin status yespo-cdp
```

## 🏗️ Architecture & Development

### Project Structure

```
yespo-cdp/
├── ajax/               # AJAX handlers
├── assets/             # Frontend assets (CSS, JS, images)
├── backend/            # Admin panel functionality
│   ├── views/          # Admin templates
│   └── ActDeact.php    # Activation/deactivation hooks
├── engine/             # Core plugin engine
├── frontend/           # Frontend functionality
├── functions/          # WordPress hooks and functions
├── integrations/       # External service integrations
│   ├── esputnik/       # Yespo API integration
│   └── webtracking/    # Web tracking functionality
├── internals/          # Internal plugin components
├── languages/          # Translation files
├── rest/               # REST API endpoints
└── templates/          # Frontend templates
```

### Database Schema

The plugin creates the following tables on activation (all prefixed with your WordPress table prefix):

- **`yespo_contact_log`**: Tracks user-related actions (contact create/update/delete)
  - Fields: `id`, `user_id`, `contact_id`, `action`, `yespo`, `log_date`
- **`yespo_order_log`**: Tracks exported orders and responses
  - Fields: `id`, `order_id`, `action`, `status`, `created_at`, `updated_at`
- **`yespo_export_status_log`**: Export session state for users/orders
  - Fields: `id`, `export_type` (`users`|`orders`), `total`, `exported`, `status`, `code`, `updated_at`
- **`yespo_queue`**: Bulk users export queue sessions
  - Fields: `id`, `session_id`, `export_status` (`STARTED`|`FINISHED`), `local_status`
- **`yespo_queue_items`**: Exported contacts in a session
  - Fields: `id`, `session_id`, `contact_id`, `yespo_id`
- **`yespo_auth_log`**: API key authorization attempts
  - Fields: `id`, `api_key`, `response`, `time`
- **`yespo_errors`**: Export errors
  - Fields: `id`, `error`, `time`

**User/Order Meta:**
- Users: `yespo_contact_id`, `yespo_bad_request`
- Orders: `sent_order_to_yespo`, `yespo_order_time`, `yespo_customer_removed`, `yespo_bad_request`

**Options:**
- `yespo_options`: Stores API key, tracking scripts, export pointers
- `yespo-version`: Plugin version tracking

### Cron Jobs

- **`yespo_export_data_cron`** (every minute):
  - Bulk export of users and orders
  - Resumes failed orders
  - Removes users after GDPR erase
- **`yespo_script_cron_event`** (hourly):
  - Validates/refreshes tracking script
  - Removes old export JSON logs

### AJAX Endpoints

**API & Authorization:**
- `yespo_check_api_authorization_yespo`: Validate API key
- `yespo_check_api_key_esputnik`: Save API key via settings
- `yespo_get_account_yespo_name`: Fetch Yespo account name

**Data Export:**
- `yespo_export_user_data_to_esputnik`: Enqueue users export
- `yespo_get_process_export_users_data_to_esputnik`: Users export progress
- `yespo_export_order_data_to_esputnik`: Enqueue orders export
- `yespo_get_process_export_orders_data_to_esputnik`: Orders export progress
- `yespo_stop_export_data_to_yespo`: Pause export
- `yespo_resume_export_data`: Resume export

**Web Tracking:**
- `yespo_get_webtracking_script_action`: Fetch tracking script
- `yespo_get_cart_contents`: Current cart snapshot for tracking
- `save_webid`: Store tracking identifiers in session

### Core Components

**Main Plugin Files:**
- `backend/ActDeact.php`: Activation/deactivation, DB creation, upgrade procedures
- `functions/functions.php`: WordPress hooks, AJAX handlers, cron functions
- `engine/Initialize.php`: Bootstraps all plugin components

**Yespo API Integration (`integrations/esputnik/`):**
- **`Yespo_Account`**: API key verification, profile name fetch, auth logging
- **`Yespo_Contact` / `Yespo_Contact_Mapping`**: Contact CRUD, bulk users export payloads
- **`Yespo_Order` / `Yespo_Order_Mapping`**: Order meta handling, mapping, bulk orders export
- **`Yespo_Export_Users` / `Yespo_Export_Orders` / `Yespo_Export_Service`**: Export orchestration, queue management, progress tracking
- **`Yespo_Logging_Data`**: Contact action logging
- **`Yespo_Localization`**: Admin JS localization

**Web Tracking (`integrations/webtracking/`):**
- **`Yespo_Web_Tracking_Script`**: Retrieve/store tracking code, cron verification
- **`Yespo_Web_Tracking_Aggregator`**: Localize and enqueue client scripts
- **Event Classes**: Track user behavior, cart events, purchases, page views

### Data Flow Overview

**Authorization**: API key verified via `Yespo_Account::send_keys()` → account name cached → tracking script retrieval enabled

**Historical Export**:
- **Users**: Cron → select up to 2000 users → map → send → handle response → update progress
- **Orders**: Cron → select up to 1000 orders → map → send → log JSON payloads

**Real-time Updates**:
- User changes → update Yespo profile
- Order changes → schedule export via cron
- GDPR deletes → queue removal via cron

### Troubleshooting

- **401 Invalid API key**: Re-enter a valid key; the plugin will not sync until corrected
- **0 Outgoing activity blocked**: Hosting blocks outbound requests; contact your provider
- **429/500 from Yespo**: Exports pause for 5 minutes and resume automatically
- **No cron activity**: Ensure WP-Cron is enabled or configure a system cron

## 🤝 Contributing

We welcome contributions! Whether you're fixing bugs, improving documentation, or adding new features.

### Branch Structure (Git Flow)
- **`main`** - Production-ready code, stable releases
- **`develop`** - Active development branch, target for all PRs
- **Feature branches** - Created from `develop` for new features/fixes

### Getting Started
1. **Fork** the repository on GitHub
2. **Clone** your fork and switch to `develop` branch
3. Follow the **Quick Start** guide above to set up locally
4. Make your changes and **test thoroughly**
5. Submit a **Pull Request** to the `develop` branch

### Commit Messages
Use conventional commits format:
```
feat(api): add webhook for order updates
fix(ui): resolve API key validation issue
docs(readme): update installation instructions
```

### Code Standards
- **PHP**: Follow WordPress Coding Standards, use proper sanitization
- **JavaScript**: Use ES6+, meaningful variable names, error handling
- **Security**: Never commit secrets, validate inputs, use nonces and capability checks

### Testing Checklist
Before submitting a PR, test:
- [ ] Plugin activation/deactivation
- [ ] API key configuration and data export
- [ ] Web tracking functionality
- [ ] Admin interface
- [ ] WordPress/WooCommerce compatibility

### Support
- **🐛 Bug reports**: [GitHub Issues](https://github.com/ardas/yespo-cdp/issues)
- **💡 Feature requests**: [GitHub Issues](https://github.com/ardas/yespo-cdp/issues)
- **🔒 Security issues**: support@yespo.io
- **📚 Documentation**: [yespo.io/support](https://yespo.io/support)

## 📦 Installation

### For End Users

1. **Install and activate** the Yespo plugin from WordPress admin
2. Navigate to the **Yespo section** in WordPress admin panel
3. **Enter your API key** from your [Yespo account](https://my.yespo.io/settings-ui/#/api-keys-list)
4. Click **Synchronize** to start data transfer
5. Optionally click **Configure Web Tracking** to enable event tracking

### Requirements
- **WordPress**: >= 6.5.5
- **WooCommerce**: Latest version recommended
- **PHP**: >= 7.4
- **Cron**: WordPress cron or server-side cron required

### Important Notes
- Plugin supports **multisite** configurations (each store needs separate Yespo account)
- Clear cache after updates if using caching plugins
- Ensure cron jobs are working for data synchronization

## ✨ Features Overview

### Core Integration Features
- **Customer Data Sync**: Automatic contact synchronization (registration, orders, profile updates)
- **Order Tracking**: Real-time order data transfer (creation, status updates, historical data)
- **GDPR Compliance**: Automatic data removal when customers are deleted from WooCommerce

### Web Tracking Events
The plugin tracks these key ecommerce events:
- **PageView**: Basic page tracking (required for other events)
- **ProductPage**: Product view tracking for recommendations
- **CategoryPage**: Category browsing behavior
- **CustomerData**: User identification after registration/login
- **StatusCart**: Shopping cart state changes
- **PurchasedItems**: Completed order tracking
- **MainPage**: Homepage visits
- **NotFound**: 404 page tracking
- **ProductImpression**: Recommendation block impressions

### Marketing Automation Features
- **Customer Segmentation**: Advanced segmentation based on behavior and purchase data
- **Personalization**: Product recommendations using 200+ algorithms
- **Omnichannel Campaigns**: Email, SMS, push notifications, widgets across 9 channels
- **Trigger Workflows**: Automated campaigns (abandoned cart, purchase follow-up, etc.)

### Data Protection
- **GDPR Compliant**: Automatic data synchronization with privacy regulations
- **ISO Certified**: Security management system certified under ISO/IEC 27001:2022
- **Data Ownership**: You retain full ownership; deletions in WooCommerce sync to Yespo

## 📝 Changelog

### 1.1.2 (2025-04-14)
- **Fixed**: Export-related bugs blocking historical data transmission
- **Improved**: Real-time data flow reliability
- **Changed**: Moved StatusCart, PurchasedItems, and CustomerData events to backend

### 1.1.1 (2025-02-21)
- **Added**: Optional web tracking script installation (manual activation)

### 1.1.0 (2025-02-04)
- **Added**: MainPage event for homepage recommendation setup
- **Added**: NotFound event for 404 page recommendations
- **Added**: ProductImpression event for recommendation analytics
- **Improved**: WooCommerce version compatibility

### 1.0.2 (2024-11-21)
- **Added**: Automatic web tracking configuration

### 1.0.1 (2024-10-30)
- **Updated**: README link formatting

### 1.0.0
- **Initial release**: Core functionality for WooCommerce-Yespo integration

## 🆘 Support

### Getting Help
- **📚 Documentation**: [Yespo Support Center](https://yespo.io/support)
- **🔧 Setup Guide**: [WooCommerce Installation Manual](https://yespo.io/support/installing-plugin-woocommerce-sites)
- **📧 Direct Support**: support@yespo.io

### Reporting Issues
- **🐛 Bug Reports**: [GitHub Issues](https://github.com/ardas/yespo-cdp/issues)
- **💡 Feature Requests**: [GitHub Issues](https://github.com/ardas/yespo-cdp/issues)
- **🔒 Security Vulnerabilities**: support@yespo.io (private disclosure)

### Common Solutions
- **API Key Issues**: Verify your key in [Yespo Dashboard](https://my.yespo.io/settings-ui/#/api-keys-list)
- **Sync Problems**: Check WordPress cron is enabled and working
- **Tracking Issues**: Ensure web tracking is configured in plugin settings

---

**About Yespo**: Learn more about our [Customer Data Platform](https://yespo.io/?utm_source=wordpress&utm_medium=referral&utm_campaign=woocommerce) and [Privacy Policy](https://yespo.io/privacy-policy?utm_source=wordpress&utm_medium=referral&utm_campaign=woocommerce).