# Purpose

The purpose of the plugin is to simplify the integration of WooCommerce-based online stores with the Yespo platform and to provide all the necessary functionality without manual code intervention.

The plugin implements:

* Automatic transfer of current and historical customer data (contacts) from WooCommerce to Yespo — including creation, update, regular and GDPR-compliant deletion  
* Automatic transfer of current and historical order data from WooCommerce to Yespo — including creation, update, and status changes  
* Automatic registration of the store domain in Yespo (to obtain general and web push scripts)  
* Automatic installation of the required scripts (site tracking, push) and the service worker for push notifications on the site  
* Web tracking configuration for collecting user activity on the site (product page views, add to cart, etc.)  
* Logging of errors, events, and export status

Below is detailed information about the technical solutions used in the development of the Yespo for WooCommerce plugin

# Plugin installation

During plugin installation, the following tables are created in the database:

1. **Prefix \+ yespo\_auth\_log** — this table logs authorizations with Yespo.  
    **Structure:**   
   * id  
   * api\_key – the API key entered by the user for authorization  
   * response – response code received (usually 200 when authorization is successful)  
   * time

2. **Prefix \+ wp\_yespo\_contact\_log** — this table logs actions related to users (adding, updating, or deleting).  
    **Structure:**  
   * id  
   * user\_id  
   * action – the type of action performed on the user  
   * log\_date

3. **Prefix \+ yespo\_curl\_json** — this table logs all exported data sent to Yespo (users and orders).  
    **Structure:**  
   * id  
   * text – data for each export, recorded in JSON format  
   * created\_at

4. **Prefix \+ yespo\_errors** — this table stores errors that may occur during data export to Yespo.  
    **Structure:**  
   * id  
   * error – error code (e.g., 401\)  
   * time

5. **Prefix \+ yespo\_export\_status\_log** — this table logs export processes, the number of records to export, number exported, and their status.  
    This table is important, as it powers the progress bar and the logic of historical data export.  
    **Structure:**  
   * id  
   * export\_type – the type of data being exported (users or orders)  
   * total – total number of records to export  
   * exported – number of records exported during the current session  
   * status – export process status (active, completed, stopped, error)  
   * code – last response code from Yespo  
   * updated\_at

6. **Prefix \+ yespo\_order\_log** — this table logs exported orders.  
    **Structure:**  
   * id  
   * order\_id  
   * action – the type of operation sent to Yespo  
   * status – the response code from Yespo for the given order  
   * created\_at  
   * updated\_at

7. **Prefix \+ yespo\_queue** — this table logs the start and completion of historical user data export.  
    **Structure:**  
   * id  
   * session\_id – export session ID for users  
   * export\_status – the status of each export batch (STARTED – export started, FINISHED – export completed)  
   * local\_status

8. **Prefix \+ yespo\_queue\_items** — this table stores email addresses of users whose data was exported to Yespo.  
    **Structure:**  
   * id  
   * session\_id  
   * contact\_id – email address of the user whose data was exported to Yespo

9. **Prefix \+ yespo\_queue\_orders** — this table stores data for the current order export session to Yespo.  
    **Structure:**  
   * id  
   * yespo\_status – start and end of the export session (STARTED and FINISHED)

10. **Prefix \+ yespo\_removed\_users** — this table stores information about users deleted via GDPR.  
     **Structure:**  
    * id  
    * email – email addresses of users whose data was removed via GDPR. It is done not to send orders update after user deletion.  
    * time

**Cron jobs created:**  
     yespo\_export\_data\_cron  
     yespo\_script\_cron\_event

# Plugin authorization

Authorization in the plugin is performed using the Yespo API Key.

When the plugin page is opened for the first time, an API key input form is displayed. It is generated via JavaScript using the method showApiKeyForm() from the class YespoExportData.

The entered key is sent using the method checkSynchronization() via XMLHttpRequest to the backend. There, it is processed by the hook wp\_ajax\_yespo\_check\_api\_authorization\_yespo, which calls the function yespo\_check\_api\_authorization\_function().

This function passes the key to the method send\_keys() of the class Yespo\_Account to [https://yespo.io/api/v1/account/info](https://yespo.io/api/v1/account/info). If the response from Yespo returns status 200, the API key is saved in the yespo\_options under the property yespo\_api\_key. The plugin then uses this value for further data transfers to Yespo.

## Tracking script retrieval

Immediately after authorization on the backend, a POST request is executed by the send\_domain\_to\_yespo() method to [https://yespo.io/api/v1/site/domains](https://yespo.io/api/v1/site/domains). If the response is 200 OK, a GET request is sent using the make\_tracking\_script() method of the Yespo\_Web\_Tracking\_Script class to [https://yespo.io/api/v1/site/script](https://yespo.io/api/v1/site/script). 

After successfully retrieving the script code, it is stored in the yespo\_tracking\_script property within the yespo\_options. On the frontend, the addSuccessMessage() method displays a notification confirming the successful installation of the tracking script.

## Retrieving account name

After this, a request is made to retrieve the Yespo account name. The method getAccountYespoName() of the YespoExportData class is used, which sends a GET request to the backend.

On the server, the request is intercepted by the hook wp\_ajax\_yespo\_get\_account\_yespo\_name. First, the presence of the yespo\_username property in the options is checked. If it is absent, the plugin makes a GET request to  [https://yespo.io/api/v1/account/info](https://yespo.io/api/v1/account/info) using the method get\_profile\_name() of the Yespo\_Account class.

## Initialization of export data count

After receiving a positive response (status code 200), the plugin proceeds to initialize the data count for export. On the frontend, the method getNumberDataExport() of the YespoExportData class sequentially calls:

1. The method getRequest(), which sends a GET request to the backend to check for users available for export.  
   * The request is processed by the hook wp\_ajax\_yespo\_get\_users\_total\_export  
   * The response with the number of users is generated by the method get\_users\_export\_count() of the Yespo\_Export\_Users class  
   * The result is passed to the frontend  
2. Similarly, a request is sent to retrieve the number of orders for export.  
   * It is processed by the hook wp\_ajax\_yespo\_get\_orders\_total\_export  
   * The response with the number of orders is formed by the method get\_export\_orders\_count() of the Yespo\_Export\_Orders class  
   * The result is passed to the frontend

# Data export to Yespo

## Fields mapping

### User Mapping

User mapping is performed in the Yespo\_Contact\_Mapping class. The following fields are prepared for sending to Yespo:

* **\*email** – received from billing\_email  
* **externalCustomerId** – user ID  
* **firstName** – received from billing\_first\_name, fallback shipping\_first\_name, empty if absent  
* **lastName** – received from billing\_last\_name, fallback shipping\_last\_name, empty if absent  
* **region** – received from billing\_state, fallback shipping\_state, fallback billing\_country, fallback shipping\_country, empty if absent  
* **town**– received from billing\_city, fallback shipping\_city, empty if absent  
* **address** – received from billing\_address\_1, fallback shipping\_address\_1, fallback billing\_address\_2, fallback shipping\_address\_2, empty if absent  
* **\*sms**– received from billing\_phone, fallback shipping\_phone, empty if absent  
* **postcode** – received from billing\_postcode, fallback shipping\_postcode, empty if absent  
* **languageCode** – received from locale meta field, fallback blog language, empty if absent

The **email** and **sms** are passed to Yespo using the **channels** array, where **type** specifies the data type (email, sms) and **value** contains the value.

### Order Mapping

Order mapping is performed in the Yespo\_Orders\_Mapping class. The following fields are prepared for sending to Yespo:

* **externalOrderId** (required) – order ID  
* **externalCustomerId** – user ID, empty if absent  
* **totalCost** (required) – received from total property  
* **status** (required) – received from status property. WooCommerce to Yespo statuses mapping:  
  * 'processing' and 'on-hold'- IN\_PROGRESS  
  * 'failed', 'cancelled', 'trash', and 'refunded'- CANCELLED  
  * 'completed' \- DELIVERED  
  * By default (if none of the listed statuses apply), INITIALIZED is sent  
* **date** (required) – received from yespo\_order\_time, fallback get\_date\_created() method  
* **currency** (required) – received from currency property  
* **email** – received via get\_billing\_email() method, empty if absent  
* **phone** – received via get\_phone\_number() method, empty if absent  
* **firstName** – received via get\_billing\_first\_name() method, fallback get\_shipping\_first\_name(), empty if absent  
* **lastName** – received via get\_billing\_last\_name() method, fallback get\_shipping\_last\_name(), empty if absent  
* **shipping** – received from shipping\_total property, empty if absent  
* **discount** – received from discount property, empty if absent  
* **taxes** – received from  total\_tax property, fallback discount\_tax, fallback cart\_tax, fallback shipping\_tax, empty if absent  
* **source** – received from created\_via property, empty if absent  
* **paymentMethod** – received from payment\_method property, empty if absent  
* **deliveryAddress** – received via get\_delivery\_address() method, empty if absent  
* **additionalInfo** – received via get\_customer\_note() method, empty if absent

#### Order Items

* **externalItemId** (required) – received via get\_product\_id() method  
* **name** – received via get\_name() method  
* **category** – received from product\_cat taxonomy  
* **quantity** (required) – received via get\_quantity() method  
* **cost** – received via get\_subtotal() method  
* **url** – received via get\_permalink() function  
* **imageUrl** – received via wp\_get\_attachment\_image\_src() function  
* **description** – received via get\_short\_description() method, empty if absent

## Historical data export

After the cron is triggered, the method start\_active\_bulk\_export\_users() of the Yespo\_Export\_Users class is activated, and the export of historical data begins.

### Historical users data export

1. **Start conditions:**  
   * There is an entry with active status for user export in the table yespo\_export\_status\_log  
   * No errors 0, 429, or 500 from the previous session  
   * The previous cron export iteration is completed  
2. **Initialization:**  
   * Start time  
   * Total number of users  
   * Number of already exported contacts  
   * Current status  
   * API response code  
3. **do-while loop:**  
   * Used to limit the sending time of contact batches to 7.5 seconds or a maximum of three iterations per minute  
4. **User retrieval:**  
   * From the users table, 2000 user ids are selected without the mark yespo\_contact\_id (or fewer if less remain) and with ids greater than yespo\_highest\_exported\_user.  
5. **Batch formation and sending:**  
   * Data is mapped using the method create\_bulk\_export\_array() of the class Yespo\_Contact\_Mapping  
   * Sending is performed using the method export\_bulk\_users() of the class Yespo\_Contact to POST [https://yespo.io/api/v1/contacts](https://yespo.io/api/v1/contacts)  
6. **API response handling:**  
   * 200: yespo\_contact\_id is added, yespo\_highest\_exported\_user in the yespo\_options is updated  
   * 400: the mark yespo\_bad\_request is added to contacts, and the process continues as in the 200 case  
   * 429, 500: the batch is marked as FINISHED. The error is written to yespo\_errors. The next attempt will occur only after 5 minutes  
   * 401, 0: export is paused, a message is displayed. The batch is marked as FINISHED.  
7. **Indicator update:**  
   * The ID of the last exported contact in the batch is stored in the property yespo\_highest\_exported\_user of yespo\_options.  
8. **Export status update:**  
   * If all contacts are exported, the status in yespo\_export\_status\_log is changed to completed using the method update\_table\_data() of the class Yespo\_Export\_Users.

### Historical orders data export

1. **Start conditions:**  
   * There are orders without the mark sent\_order\_to\_yespo, whose last update was more than 5 minutes ago  
   * No active export process is running  
   * A record with status active exists for order export in the table yespo\_export\_status\_log  
   * No errors 0, 429, or 500 from the previous session  
   * The previous cron export iteration is completed  
2. **Initialization:**  
   * Start time  
   * Total number of orders  
   * Number of exported orders  
   * Current status  
   * API response code  
3. **do-while loop:**  
   * Used to limit the sending time of order batches to 7.5 seconds or a maximum of three iterations per minute  
4. **Order retrieval:**  
   * From the orders table, 1000 order ids are selected without the mark sent\_order\_to\_yespo (or fewer if less remain) and with ids greater than yespo\_highest\_exported\_order  
5. **Batch formation and sending:**  
   * Data is mapped using the method create\_bulk\_order\_export\_array() of the class Yespo\_Order\_Mapping  
   * Sending is performed using the method create\_bulk\_orders\_on\_yespo() of the class Yespo\_Order to POST  [https://yespo.io/api/v1/orders](https://yespo.io/api/v1/orders)  
6. **API response handling:**  
   * 200: the mark sent\_order\_to\_yespo is added, the batch is logged in yespo\_curl\_json  
   * 400: the mark yespo\_bad\_request is added to the orders, and the process continues as in the 200 case  
   * 429, 500: the batch is marked as FINISHED. The error is recorded in yespo\_errors. The next attempt will occur only after 5 minutes  
   * 401, 0: export is paused, a message is displayed. The batch is marked as FINISHED  
7. **Indicator update:**  
   * The ID of the last exported order in the batch is stored in the property yespo\_highest\_exported\_order of yespo\_options  
8. **Export status update:**  
   * If all orders are exported, the status in yespo\_export\_status\_log is changed to completed

### Progress bar operation

The progress bar displays the overall export progress across all data — both contacts and orders. Visually, it is updated in real time based on the number of entities already exported.

1. **User export initiation:**  
   * If the number of users is greater than zero, the method route initiates a request to create an export task  
   * The method startExportUsers() sends a POST request  
   * The method add\_users\_export\_task() of the class Yespo\_Export\_Users creates a record (if none exists with status active for users) in the table yespo\_export\_status\_log, specifying the number of users to export, the number already exported, the status active, and the users export type.  
2. **User export progress update:**  
   * The method checkExportStatus() of the class YespoExportData sends a request to the backend every 5 seconds to retrieve the current data  
   * The method get\_process\_users\_exported() of the class Yespo\_Export\_Users retrieves the actual data from the yespo\_export\_status\_log database table and returns it to the frontend  
   * The received data is passed to the method updateProgress() of the class YespoExportData, which updates the progress bar  
3. **Pause / resume user export:**  
   * If the site administrator clicks PAUSE, the method stopExportData() of the class YespoExportData sends a GET request to the backend, which activates the method stop\_export\_users() of the class Yespo\_Export\_Users and changes the export event status to stopped in the yespo\_export\_status\_log table  
   * If the administrator clicks RESUME, the method resumeExportData() of the class YespoExportData sends a GET request to the backend, which activates the method resume\_export\_users() of the class Yespo\_Export\_Users and changes the export status to active in the yespo\_export\_status\_log table.  
4. **Order export initiation:**  
   * If users have been exported or there are no users to export, and orders are present, the method startExportOrders() sends a POST request  
   * The method add\_orders\_export\_task() of the class Yespo\_Export\_Orders creates a record (if none exists with status active for orders) in the table yespo\_export\_status\_log, indicating the number of orders to export, the number exported, the status active, and the export type orders.  
5. **Order export progress update:**  
   * The method processExportOrders() triggers checkExportStatus() every 5 seconds and sends a request to the backend to retrieve the current data  
   * The received data is passed to the method updateProgress() of the class YespoExportData, which updates the progress bar  
   * If all orders are exported, the method startExportUsers() checks for remaining contacts to export  
6. **Pause / resume order export:**  
   * If the site administrator clicks PAUSE, the method stopExportData() of the class YespoExportData sends a GET request to the backend, which activates the method stop\_export\_orders() of the class Yespo\_Export\_Orders and changes the export status to stopped in the yespo\_export\_status\_log database table  
   * If the administrator clicks RESUME, the method resumeExportData() of the class YespoExportData sends a GET request to the backend, which activates the method resume\_export\_orders() of the class Yespo\_Export\_Orders and changes the export status to active in the yespo\_export\_status\_log database table  
7. **Historical export completion:**  
   * If data export is finished or there is no data to export, a message is displayed using the method addSuccessMessage() of the class YespoExportData.  
8. **Error handling:**  
   * The method showErrorPage() displays a message in case of status 401 or blocked activity.

## Real-time data export

### Real-time users data export

1. When a new user is registered or an existing user is updated, the event is captured by the hook profile\_update. In the function yespo\_update\_user\_profile\_function, user data is retrieved  
2. The method update\_woo\_profile\_yespo() of the class Yespo\_Contact maps the data using update\_woo\_to\_yes() of the class Yespo\_Contact\_Mapping and sends POST [https://esputnik.com/api/v1/contact](https://esputnik.com/api/v1/contact) to Yespo

#### User deletion

##### Soft delete

1. User deletion is captured by the hook delete\_user, and the user's email is saved to the table yespo\_removed\_users using the method add\_entry\_removed\_user of the class Yespo\_Contact. The DELETE [https://yespo.io/api/v1/contact?externalCustomerId={ID}\&erase=false](https://yespo.io/api/v1/contact?externalCustomerId={ID}&erase=false) request is sent to Yespo  
2. Deletion in Yespo is performed via the method delete\_from\_yespo() of the class Yespo\_Contact using the user id

##### GDPR deletion

1. The GDPR deletion request is captured by the hook wp\_privacy\_personal\_data\_erased. The hook triggers the function yespo\_clean\_user\_data\_after\_data\_erased\_function, which retrieves the user by email  
2. The event is logged into the table yespo\_contact\_log using the method create() of the class Yespo\_Logging\_Data  
3.  After being processed by the cron job, the user is deleted using delete\_from\_yespo() of the class Yespo\_Contact via DELETE [https://yespo.io/api/v1/contact?externalCustomerId={ID}\&erase=true](https://yespo.io/api/v1/contact?externalCustomerId={ID}&erase=true). This occurs within 15–20 minutes

### Real-time orders data export

1. The method schedule\_export\_orders() of the class Yespo\_Export\_Orders selects the IDs of orders that were modified more than 5 minutes ago  
2. If there are errors — the export is stopped  
3. If the number of orders is greater than 0:  
   * In a loop, the order IDs are processed, and each order is mapped using the method order\_woo\_to\_yes() of the class Yespo\_Order\_Mapping  
   * The data is sent using the method create\_order\_on\_yespo() of the class Yespo\_Order via POST [https://yespo.io/api/v1/orders](https://yespo.io/api/v1/orders).

# Web tracking

## Frontend events (via [eS.js](http://eS.js) function)

* **CategoryPage**: sends the category name when a user opens a category page. The value is retrieved in the class Yespo\_Category\_Event and passed to Yespo via the method sendCategory().  
* **ProductPage**: sends the product ID, price, and availability when a user opens a product page. The value is retrieved in the class Yespo\_Product\_Event and passed to Yespo via the method sendProduct().  
* **MainPage**: sends "MainPage" when the homepage is opened. The value is retrieved in the class Yespo\_Front\_Event and passed to Yespo via the method sendFront().  
* **NotFound**: sends "NotFound" for 404 pages. The value is retrieved in the class Yespo\_NotFound\_Event and passed to Yespo via the method sendNotFound().  
* **StatusCartPage**: sends "StatusCartPage" when the cart page is opened. The value is retrieved in the class Yespo\_Cart\_Event, where the method get\_cart\_page() checks whether the current page is the cart. Then, it is passed to Yespo via the method sendCart().

## Backend events (via curl at https://tracker.yespo.io/api/v2)

* **CustomerData:**  
  * Tracked by the WordPress hook profile\_update (registration, login, profile update)  
  * Tracked by the WooCommerce hook woocommerce\_thankyou (after order placement)  
  * Data is formed using the method handle\_user\_event of the class Yespo\_User\_Event and sent to Yespo  
* **StatusCart:**  
  * Product addition to cart is tracked by the WooCommerce hook woocommerce\_add\_to\_cart. The method add\_to\_cart\_event() of the class Yespo\_Cart\_Event forms and sends the data to Yespo  
  * Product quantity change is tracked by the WooCommerce hook woocommerce\_after\_cart\_item\_quantity\_update. The method after\_cart\_item\_quantity\_update() of the class Yespo\_Cart\_Event forms and sends the data to Yespo  
  * Product removal from the cart or clearing the cart is tracked by the WooCommerce hook woocommerce\_cart\_item\_removed. The method cart\_item\_removed() of the class Yespo\_Cart\_Event forms and sends the data to Yespo.  
* **PurchasedItems**:  
  * Tracked by the hook woocommerce\_thankyou when a user completes an order on the checkout page and is redirected to the thank you page. The method send\_order\_to\_yespo() of the class Yespo\_Purchased\_Event forms and sends the data to Yespo

For these events, orgId and webId are intercepted by the method actionTenantIdWebId() of the class YespoTracker and stored in the session via the wp\_ajax\_nopriv\_save\_webid hook and the yespo\_save\_webid\_to\_session() function.

The values are retrieved using the methods get\_webId() and get\_orgId() of the class Yespo\_User\_Event and appended to the events.

The tenantId parameter is obtained during the plugin’s initial activation, stored using the method add\_tenant\_id\_to\_options() of the class Yespo\_Web\_Tracking\_Script in the yespo\_tenant\_id property of the yespo\_options option. Then tenantId  is added to events via the method get\_tenant\_id\_from\_options() of the class Yespo\_Web\_Tracking\_Script.

# Web push functionality

## Retrieving and storing data

After the plugin is updated or user is authorized for the first time via API key, the plugin:

1. Sends a POST request to [https://yespo.io/api/v1/site/webpush/domains](https://yespo.io/api/v1/site/webpush/domains) with the following parameters using the method send\_post\_data() of the class Yespo\_Web\_Push:  
   * domain  
   * serviceWorkerName  
   * serviceWorkerPath  
   * serviceWorkerScope  
2. If the response is 200, the plugin sends a GET request to [https://yespo.io/api/v1/site/webpush/script?domain=](https://yespo.io/api/v1/site/webpush/script?domain=)...  
   * If the response is 200, the returned JSON object contains:  
     * Script is added to options via add\_script\_to\_options()  
     * serviceWorker is saved to the file push-yespo-sw.js at the path from the serviceWorkerPath property using the method write\_script\_to\_file()  
3. After the data is saved, the plugin page displays the confirmation message

## Using web push scripts

If the scripts are saved, the plugin automatically inserts the value of yespo\_webpush\_script from the yespo\_options into the \<head\> of the site using the wp\_head hook and the method get\_script\_from\_options() of the class Yespo\_Web\_Push.

This enables the automatic display of the Web Push subscription form in the upper left corner of the browser.

# Plugin uninstallation

When the user deletes the plugin, the following tables are removed:

1. Prefix \+ yespo\_auth\_log  
2. Prefix \+ wp\_yespo\_contact\_log  
3. Prefix \+ yespo\_curl\_json  
4. Prefix \+ yespo\_errors  
5. Prefix \+ yespo\_export\_status\_log  
6. Prefix \+ yespo\_order\_log  
7. Prefix \+ yespo\_queue  
8. Prefix \+ yespo\_queue\_items  
9. Prefix \+ yespo\_queue\_orders  
10. Prefix \+ yespo\_removed\_users

Additionally, the following are deleted:

* User metadata: yespo\_contact\_id and yespo\_bad\_request  
* Order metadata: sent\_order\_to\_yespo, yespo\_order\_time, yespo\_customer\_removed, and yespo\_bad\_request  
* Options: yespo\_options and yespo-version  
* Cron jobs: yespo\_export\_data\_cron and yespo\_script\_cron\_event

If the user reinstalls the plugin, the configuration process starts from scratch. No duplicates will occur.

### 

### 🌿 Branch Structure

This project uses **Git Flow** workflow:
- **`main`** - Production-ready code, stable releases
- **`develop`** - Active development branch, all PRs should target this branch
- **Feature branches** - Created from `develop` for new features or fixes

### 🚀 Quick Start

## Prerequisites

Before you begin, you'll need the following:

* Wordpress: version 6.5.5 Download it if you haven't already.  
* Setup test store with PHP 7.4  
* Download and install Yespo CDP plugin from Wordpress repository to your test store

Ensure the presence of these [hooks](https://docs.yespo.io/docs/installing-plugin-woocommerce-sites#hooks-tables-functions-and-other-wordpress-and-woocommerce-components), tables, and functions.

## Local Development Setup

1. **Fork the Repository**
   ```bash
   # Fork on GitHub, then clone your fork
   git clone --branch develop https://github.com/ardas/yespo-cdp.git
   cd yespo-cdp
   ```

2. **Set Up Development Environment**
   ```bash
   # Install PHP dependencies
   composer install
      
   # Update autoload
   composer dumpautoload -o
   ```

3. **Configure WordPress Environment**
   - Set up a local WordPress installation
   - Install and activate WooCommerce
   - Install the plugin Yespo CDP
   - Configure your Yespo API credentials

### 📝 Contributing

#### Branch Naming
- `feature/description` - for new features
- `fix/description` - for bug fixes
- `docs/description` - for documentation updates
- `refactor/description` - for code refactoring
- `security/description` - for security fixes

#### Commit Messages
Follow conventional commits:
```
type(scope): description

Example:
feat(api): add webhook for order updates
fix(ui): resolve API key validation issue
docs(readme): update installation instructions
refactor(export): improve user data export performance
```

#### Pull Request Process

1. **Create a Feature Branch from `develop`**
   ```bash
   git checkout develop
   git pull origin develop
   git checkout -b feature/your-feature-name
   ```

2. **Make Your Changes**
   - Follow our [coding standards](#-coding-standards)
   - Update documentation if needed
   - Add tests for new functionality

3. **Submit Pull Request to `develop`**
   - Push to your fork: `git push origin feature/your-feature-name`
   - Create a Pull Request **targeting the `develop` branch** with:
     - **Clear title** describing the change
     - **Detailed description** explaining:
       - What problem does this solve?
       - What changes were made?
       - How to test the changes?
     - **Screenshots** for UI changes
     - **Link to related issues**

### 🔧 Coding Standards

#### PHP & WordPress

#### File Organization
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
│   ├── webpush/        # Yespo WebPush integration
│   └── webtracking/    # Web tracking functionality
├── internals/          # Internal plugin components
├── languages/          # Translation files
├── rest/               # REST API endpoints
└── templates/          # Frontend templates
```

#### Code Style
- **90 characters** line length (max 120)
- **4 spaces** indentation
- Use **meaningful names** (avoid single letters except in loops)
- Follow **WordPress Coding Standards**
- Use **proper sanitization** for all user inputs

#### Naming Conventions

**Classes:**
```php
// ✅ Component classes - descriptive names with Component suffix
class Settings_Page extends Base {
    // Component logic here
}

// ✅ Service classes - descriptive names
class Yespo_Account {
    // Service logic here
}

// ✅ Integration classes - descriptive names with service prefix
class Yespo_Contact_Mapping {
    // Integration logic here
}
```

**Functions:**
```php
// ✅ WordPress hooks - descriptive names with function suffix
function yespo_check_api_authorization_function() {
    // Function logic here
}

// ✅ Helper functions - descriptive names
function yespo_get_settings() {
    // Helper logic here
}
```

**Interfaces:**
```php
// ✅ Interface naming - descriptive names with I suffix
interface Yespo_Integration_I {
    public function initialize();
}
```

#### Security Guidelines
```php
// ✅ Proper sanitization
$api_key = sanitize_text_field(wp_unslash($_POST['yespo_api_key']));

// ✅ Nonce verification
if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'action_name')) {
    return;
}

// ✅ Capability checks
if (!current_user_can('manage_options')) {
    return;
}

// ✅ Prepared statements for database queries
$wpdb->prepare("INSERT INTO %i (api_key, response, time) VALUES (%s, %s, %s)", ...);
```

### JavaScript & Frontend

#### File Organization
```
assets/
├── build/                # Source files
│   ├── plugin-admin.css  # CSS file
│   ├── plugin-admin.js   # JavaScript file administration functionality
│   └── plugin-public.js  # JavaScript file for webtracking functionality
└── images/               # Images, icons
```

#### Code Style
- Use **ES6+** features
- Follow **WordPress JavaScript Coding Standards**
- Use **meaningful variable names**
- Add **error handling** for all API calls

#### Component Guidelines
```javascript
// ✅ Good: Clear component structure
class YespoAdmin {
    constructor() {
        this.apiKey = '';
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.checkAuthorization();
    }
    
    bindEvents() {
        // Event binding logic
    }
}
```

### 🐛 Bug Reports

Found a bug? Help us fix it by providing detailed information.

#### Before Reporting
- Check if the issue already exists in [GitHub Issues](https://github.com/ardas/yespo-cdp/issues)
- Make sure you're using the latest version
- Try to reproduce the issue consistently
- Check WordPress and WooCommerce compatibility

#### Bug Report Template
Use the GitHub bug report template when creating an issue. Include:
- WordPress version
- WooCommerce version
- PHP version
- Plugin version
- Steps to reproduce
- Expected vs actual behavior
- Error logs if applicable

### 💡 Feature Requests

Have an idea for improvement? We'd love to hear it!

#### Feature Request Template
Use the GitHub feature request template when suggesting new features. Include:
- Problem statement
- Proposed solution
- Implementation ideas
- Use cases

### 🔒 Security

#### Reporting Security Issues
**Do not report security vulnerabilities through public GitHub issues.**

Instead, please email us directly at: **support@yespo.io**

Include:
- Description of the vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if any)

#### Security Guidelines for Contributors
- **Never commit** API keys, passwords, or secrets
- Use **WordPress options** for storing sensitive configuration
- **Validate all inputs** and sanitize user data
- Follow **WordPress security best practices**
- Keep dependencies up to date
- Use **nonces** for all forms and AJAX requests
- Implement **capability checks** for admin functions

### 🤝 Community Guidelines

#### Code of Conduct
- **Be respectful** and inclusive
- **Focus on constructive feedback**
- **Help newcomers** feel welcome
- **Assume good intentions**
- **No harassment** or inappropriate behavior

#### Getting Help
- 📖 Check the [documentation](https://yespo.io/support)
- 💬 Ask questions in GitHub Discussions
- 📧 Contact us at support@yespo.io
- 🐛 Report bugs through GitHub Issues

### 📊 Issue Management

#### 🏷️ Labels We Use

| Label | Description | Used For |
|-------|-------------|----------|
| `bug` | Something isn't working | Bug reports |
| `feature` | New feature request | Feature requests |
| `enhancement` | Improvement to existing feature | Enhancements |
| `documentation` | Documentation needs update | Docs updates |
| `good first issue` | Good for newcomers | Beginner-friendly |
| `help wanted` | Extra attention needed | Community help |
| `question` | General questions | Q&A |
| `priority: critical` | Urgent fix needed | Critical bugs |
| `priority: high` | Should be fixed soon | Important issues |
| `priority: medium` | Normal priority | Standard issues |
| `priority: low` | Can wait | Minor issues |
| `status: waiting-for-feedback` | Needs more info | Pending response |
| `status: in-progress` | Being worked on | Active work |
| `scope: api` | Backend/API related | API changes |
| `scope: ui` | Frontend/UI related | UI changes |
| `scope: docs` | Documentation related | Docs changes |
| `scope: integration` | Yespo integration related | Integration changes |
| `scope: webtracking` | Web tracking related | Tracking changes |

#### 📝 Issue Templates

We provide several issue templates to help you report issues effectively:

- **🐛 Bug Report** - For reporting bugs and issues
- **💡 Feature Request** - For suggesting new features
- **❓ Question** - For asking questions about the plugin

Each template includes specific sections to help us understand and address your request quickly.

### 📞 Support Channels

- **🐛 Found a bug?** → [Create a Bug Report](https://github.com/ardas/yespo-cdp/issues/new?template=bug_report.md)
- **💡 Have a feature idea?** → [Submit a Feature Request](https://github.com/ardas/yespo-cdp/issues/new?template=feature_request.md)
- **❓ Need help?** → [Ask a Question](https://github.com/ardas/yespo-cdp/issues/new?template=question.md)
- **📚 Check documentation** → [yespo.io/support](https://yespo.io/support)
- **📧 Direct support** → support@yespo.io
- **🔒 Security issues** → support@yespo.io

### 🧪 Testing

#### Manual Testing Checklist
Before submitting a PR, ensure you've tested:

- [ ] Plugin activation/deactivation
- [ ] API key configuration
- [ ] User data export functionality
- [ ] Order data export functionality
- [ ] Web tracking script installation
- [ ] Event tracking (cart, purchase, etc.)
- [ ] Admin interface functionality
- [ ] Multisite compatibility (if applicable)
- [ ] WordPress/WooCommerce version compatibility

#### Automated Testing
```bash
# Run PHP linting
composer lint

# Run tests (if available)
composer test
```
