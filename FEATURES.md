# Yespo CDP Features & Capabilities

This document provides a comprehensive overview of Yespo CDP features and how they integrate with your WooCommerce store.

## Table of Contents

- [About Yespo CDP](#about-yespo-cdp)
- [Web Tracking Events](#web-tracking-events)
- [Customer Segmentation](#customer-segmentation)
- [Personalization & Product Recommendations](#personalization--product-recommendations)
- [Marketing Automation Workflows](#marketing-automation-workflows)
- [Omnichannel Marketing](#omnichannel-marketing)
- [Multilingual Campaign Automation](#multilingual-campaign-automation)
- [Data Protection & Compliance](#data-protection--compliance)

## About Yespo CDP

[**Yespo**](https://yespo.io/?utm_source=wordpress&utm_medium=referral&utm_campaign=woocommerce) is an omnichannel customer data platform (CDP) that helps medium and large online projects easily use customer behavior data to increase additional sales. The platform allows companies to collect and combine clients' data from the website, mobile app, offline, email and direct channels for marketing automation, customer segmentation, and product recommendations.

**Trusted by**: More than 3,500 brands from 23 countries chose Yespo, and 300 of these companies are enterprise-level.

### Core CDP Features

- **Data Collection**: Collects first-party data from WooCommerce store, mobile application, direct channels (SMS, email), and offline sources
- **Profile Unification**: Processes customer data, brings it to a single view, and maintains real-time single customer profiles
- **Real-time Processing**: CDP receives and processes data in real-time for up-to-date customer profiles

## Web Tracking Events

The plugin simplifies web tracking setup, enabling enhanced personalization, product recommendations, and marketing automation. Unlike other solutions, installing web tracking on your WooCommerce site doesn't require developers or in-depth technical skills. Once you enter the correct API key into the plugin, the rest is done automatically.

### Frontend Events (via JavaScript)

**PageView**  
The default event that tracks URLs of pages. It is required for all other web tracking events to work.

**ProductPage**  
Tracks specific product pages. This key event enables a wide range of uses, such as abandoned view campaigns, discount notifications for viewed items, or win-back campaigns. For example, if a product viewed by a visitor previously goes on discount, you can notify them about the deal.

**CategoryPage**  
Monitors views of product categories on your website. You can use this information, for example, to send them an email with product recommendations for the most popular items in their viewed category—offering a unique twist on abandoned view campaigns.

**CustomerData**  
Tracks customer data after the registration, login, or completed purchase. This essential event identifies your website visitors and links them to accounts in your system, enabling personalized campaigns, for example via email marketing.

**StatusCart**  
Tracks the current state of the shopping cart. This event is used to run some of the most effective ecommerce workflows, such as the abandoned cart, discount notifications for products in the abandoned cart, and notifications for discounts on products similar to those in the cart. For example, if a user added something to the cart but didn't purchase it, you can send them product recommendations with similar discounted items or reminder about abandoned cart.

**PurchasedItems**  
Tracks completed orders. It's essential for compiling lists of popular products for recommendations, upselling, and cross-selling. For example, if a customer purchased a phone, you can send them a message with product recommendations featuring a phone case and a charger.

**MainPage**  
Tracks that a user is currently on the main page. Necessary to display recommendations on the site.

**NotFound**  
Tracks 404 page. Necessary to display recommendations on the site.

**ProductImpression**  
Is used to show impressions for recommendation blocks in Reports.

### Performance & Optimization

The web tracking code for WooCommerce is highly optimized, ensuring fast page loading speed and uninterrupted customer experience. If you decide to stop using the Yespo CDP plugin for WooCommerce, all scripts can be safely removed from your website.

## Customer Segmentation

Yespo CDP allows for in-depth [customer segmentation](https://yespo.io/blog/customer-segmentation-key-role-types-usage-and-case-studies?utm_source=wordpress&utm_medium=referral&utm_campaign=woocommerce) based on all available information, including customer behavior data. There is no limit to the number of conditions that can be used to form a segment.

### Advanced Segmentation Functions

- **Behavioral segmentation** - Based on user actions and interactions
- **[Segmentation by events](https://yespo.io/support/segmentation)** - Target users based on specific events they triggered
- **Segmentation by parameter** - Custom parameter-based targeting
- **[Predictive segmentation](https://yespo.io/blog/predictive-segmentation-101-brief-history-and-core-principles/amp)** - Ready-made algorithms that predict the probability of purchasing or churning customers
- **Value-based customer segmentation** - Segment by customer lifetime value
- **[RFM segmentation](https://yespo.io/blog/rfm-segmentation?utm_source=wordpress&utm_medium=referral&utm_campaign=woocommerce)** - Recency, Frequency, Monetary analysis
- **Cohort analysis segmentation** - Time-based user behavior analysis

### Data Usage

Processed and structured data can be immediately used in CDP for personalized omnichannel marketing campaigns on the WooCommerce website, in the mobile application, and through direct channels, such as push notifications, SMS, and email campaigns.

## Personalization & Product Recommendations

Due to the completeness of data and its comprehensive analysis, you can encourage users to take the desired actions through personalized interactions. These interactions consider user needs, preferences, optimal channels, language, and timing of communication.

### Product Recommendations

[Product recommendations](https://yespo.io/blog/personalized-product-recommendations-technology-details-and-case-studies?utm_source=wordpress&utm_medium=referral&utm_campaign=woocommerce) are an important tool for personalization and increased sales for WooCommerce online stores. According to our statistics, **personal product recommendations generate about 20% of online store sales**.

#### Key Features

- **200+ ready-made algorithms** available in the system
- **Custom algorithm development** possibility for unique business needs
- **Multi-channel usage**: website, mobile application, email marketing, and even by consultants in offline stores
- **Seamless experience**: customers receive the same offers across all channels due to data unification and omnichannel nature

#### Implementation

Personalized product sets created in Yespo can be used on the website, in the mobile application, in email marketing, and even by consultants in offline stores. Thanks to the unification of data and the omnichannel nature of Yespo CDP technology, the customer receives the same offers in all channels, which provides them with a seamless and personalized user experience.

## Marketing Automation Workflows

The customer data platform solves the business challenge of marketing automation. Our CDP enables the creation and launching of trigger campaigns in various channels, including SMS and email marketing, that automatically respond to customer actions or inactions.

### Most Profitable Ecommerce Scenarios

Over the years of analysis, the Yespo team has identified the most profitable scenarios for ecommerce:

- **Reduced cost** of items in the cart
- **Regular demand** campaigns
- **Out of stock** notifications
- **Next best offer** recommendations
- **WishList event** triggers
- **Abandoned cart** recovery
- **Abandoned browse** campaigns
- **Abandoned search** follow-up
- **Reactivation** campaigns and others

*Note: Our team is currently working on implementing the ability to launch triggers via a plugin.*

## Omnichannel Marketing

The functionality of Yespo makes real omnichannel marketing possible: all communication channels are integrated into a single system. Each channel enriches a single profile and has access to the full set of data in the system. Thus, the channels are coordinated and complement each other, ensuring high-quality customer interaction.

### 9 Available Channels

In the Yespo system, you can use up to **9 channels** in a single connection:

- **[Email](https://yespo.io/email-campaigns?utm_source=wordpress&utm_medium=referral&utm_campaign=woocommerce)** - Traditional email marketing campaigns
- **[Web push notifications](https://yespo.io/automated-web-push-notifications?utm_source=wordpress&utm_medium=referral&utm_campaign=woocommerce)** - Browser notifications
- **[Mobile push notifications](https://yespo.io/mobile-push-notifications?utm_source=wordpress&utm_medium=referral&utm_campaign=woocommerce)** - Native mobile app notifications
- **[App Inbox](https://yespo.io/app-inbox?utm_source=wordpress&utm_medium=referral&utm_campaign=woocommerce)** - In-app message center
- **[In-App messages](https://yespo.io/support/creating-in-app-message?utm_source=wordpress&utm_medium=referral&utm_campaign=woocommerce)** - Native app messages
- **[Widgets](https://yespo.io/popup?utm_source=wordpress&utm_medium=referral&utm_campaign=woocommerce)** - Website popups and widgets
- **[SMS](https://yespo.io/sms-campaigns?utm_source=wordpress&utm_medium=referral&utm_campaign=woocommerce)** - Text messaging campaigns
- **[Viber](https://yespo.io/viber?utm_source=wordpress&utm_medium=referral&utm_campaign=woocommerce)** - Viber messaging
- **[Telegram](https://yespo.io/telegram?utm_source=wordpress&utm_medium=referral&utm_campaign=woocommerce)** - Telegram messaging

## Multilingual Campaign Automation

Another challenge for companies with a complex trigger map and operations in several markets is maintaining communication in the client's language. Yespo has a convenient solution for [automating multilingual campaigns](https://yespo.io/multilingual-campaigns?utm_source=wordpress&utm_medium=referral&utm_campaign=woocommerce), which reduces work with multilingual audiences by 5 times.

### Features

- **Single template creation**: The interface allows the creation of a single message template for all language versions
- **Automatic language detection**: The system will automatically determine which version to send based on the user's browser language
- **5x efficiency improvement**: Dramatically reduces manual work with multilingual audiences

## Data Protection & Compliance

This plugin transmits data from your WooCommerce website to the external Yespo system to enhance the customer experience through the following system features:

### Data Processing

1. **Data collection and unification**: creation of a single customer profile with user contact information provided during registration or order placement, plus order details including creation, status updates, and historical order data
2. **Customer segmentation** based on available data within the system
3. **Marketing automation** and personalization of communications across 9 direct channels

### Security & Compliance Standards

**GDPR Compliance**  
[CDP Yespo fully complies with GDPR](https://yespo.io/gdpr-compliant?utm_source=wordpress&utm_medium=referral&utm_campaign=woocommerce), ensuring responsible data collection and usage.

**ISO Certification**  
Information security management system is certified under **ISO/IEC 27001:2022**.

**Data Ownership**  
You retain ownership of your data: if a customer's personal data is deleted in WooCommerce, it will also be deleted from the Yespo system.

### Legal Documentation

To understand how [Yespo](https://yespo.io/?utm_source=wordpress&utm_medium=referral&utm_campaign=woocommerce) processes the user data collected from your website, please review our:

- **[Data Processing Agreement](https://yespo.io/data-processing-agreement?utm_source=wordpress&utm_medium=referral&utm_campaign=woocommerce)** (DPA)
- **[Terms of Use](https://yespo.io/terms-of-use?utm_source=wordpress&utm_medium=referral&utm_campaign=woocommerce)**
- **[Privacy Policy](https://yespo.io/privacy-policy?utm_source=wordpress&utm_medium=referral&utm_campaign=woocommerce)**

### Data Platform Benefits

CDP Yespo provides a platform for stores to manage and improve the user experience. Since the Yespo database integrates seamlessly with your WooCommerce store, you can get complete information about every customer who interacts with your business. Then, you are able to use this data for personalized marketing automation across 9 channels, including push notifications, SMS, and email campaigns.

---

**Learn More**: Visit [yespo.io](https://yespo.io/?utm_source=wordpress&utm_medium=referral&utm_campaign=woocommerce) to explore Yespo's [subscription plans](https://yespo.io/tarif-universum?utm_source=wordpress&utm_medium=referral&utm_campaign=woocommerce) and capabilities.
