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
- [🤝 Contributing](#-contributing)
- [📦 Installation](#-installation)
- [✨ Features Overview](#-features-overview)
- [📝 Changelog](#-changelog)
- [🆘 Support](#-support)

### 📚 Additional Documentation

- **[FEATURES.md](FEATURES.md)** - Comprehensive feature descriptions, web tracking events, segmentation capabilities, and marketing automation details
- **[DEVELOPMENT.md](DEVELOPMENT.md)** - Technical documentation: database schema, API flows, hooks reference, and implementation details

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

### Quick Overview

The plugin consists of:
- **Backend**: Admin interface, data export, API integration
- **Frontend**: Web tracking, event collection, user interface
- **Database**: 10 custom tables for logging and queue management
- **Cron Jobs**: Automated data synchronization and cleanup
- **AJAX Endpoints**: Real-time communication between frontend and backend

### Key Technical Details

- **Database Tables**: 10 custom tables for comprehensive logging and export management
- **Export Batches**: 2000 users / 1000 orders per batch with response handling
- **Cron Schedule**: Every minute for exports, hourly for script validation
- **Event Tracking**: 9 different web events (PageView, ProductPage, StatusCart, etc.)
- **API Integration**: Full CRUD operations with Yespo platform

### Troubleshooting

- **401 Invalid API key**: Re-enter a valid key; sync stops until corrected
- **0 Outgoing blocked**: Contact hosting provider about outbound requests
- **429/500 from Yespo**: Automatic 5-minute pause and resume
- **No cron activity**: Ensure WP-Cron is enabled or configure system cron

📚 **For comprehensive technical documentation, see [DEVELOPMENT.md](DEVELOPMENT.md)** - includes detailed database schema, data flows, hooks, authentication process, and complete API reference.

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
- **Customer Data Sync**: Automatic synchronization of contacts and orders
- **Order Tracking**: Real-time order data transfer (creation, status updates, historical data)
- **Web Tracking**: Track user behavior, cart events, and purchases  
- **GDPR Compliance**: Automatic data removal when customers are deleted from WooCommerce

### Marketing Automation Capabilities
- **Customer Segmentation**: Advanced behavioral and predictive segmentation
- **Product Recommendations**: 200+ algorithms, generates ~20% of store sales
- **Omnichannel Campaigns**: Email, SMS, push notifications, widgets across 9 channels
- **Trigger Workflows**: Automated campaigns (abandoned cart, purchase follow-up, etc.)
- **Multilingual Support**: Automatically determine language based on user's browser

### Data Protection & Compliance
- **GDPR Compliant**: Full compliance with privacy regulations
- **ISO Certified**: Information security management system certified under ISO/IEC 27001:2022
- **Data Ownership**: You retain full ownership; deletions in WooCommerce sync to Yespo

📋 **For comprehensive feature details, see [FEATURES.md](FEATURES.md)** - includes detailed descriptions of web tracking events, segmentation capabilities, personalization options, and compliance information.

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