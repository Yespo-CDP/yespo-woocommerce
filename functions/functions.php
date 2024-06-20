<?php
/**
 * Yespo
 *
 * @package   Yespo
 * @author    Yespo Omnichannel CDP <vadym.gmurya@asper.pro>
 * @copyright 2022 Yespo
 * @license   GPL 3.0+
 * @link      https://yespo.io/
 */

use Yespo\Integrations\Esputnik\Esputnik_Metrika;

/**
 * Get the settings of the plugin in a filterable way
 *
 * @since 1.0.0
 * @return array
 */
function y_get_settings() {
    return apply_filters( 'y_get_settings', get_option( Y_TEXTDOMAIN . '-settings' ) );
}

/**
 * show error api key notice
 */
function error_api_key_admin_notice() {
    if ( get_option( 'yespo_options' ) !== false ) {
        $options = get_option('yespo_options', array());
        if (isset($options['yespo_api_key'])) $yespo_api_key = $options['yespo_api_key'];
        else $yespo_api_key = '';
    }
    if(!empty($yespo_api_key)){
        $result = (new \Yespo\Integrations\Esputnik\Esputnik_Account())->send_keys($yespo_api_key);
        (new \Yespo\Integrations\Esputnik\Esputnik_Account())->add_entry_auth_log($yespo_api_key, $result);
    }
    if(!empty($yespo_api_key) && $result !== 200){
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo __("Invalid API key. Please delete the plugin and start the configuration from scratch using a valid API key. No data will be lost.", Y_TEXTDOMAIN); ?></p>
        </div>
        <?php
    }
}
add_action( 'admin_notices', 'error_api_key_admin_notice' );


/*** Get profile username on Yespo ***/
function get_account_profile_name(){
    if(isset($_REQUEST['action']) && $_REQUEST['action'] === 'get_account_yespo_name' ) {
        $organisationName = '';
        if ( get_option( 'yespo_options' ) !== false ) {
            $options = get_option('yespo_options', array());
            if (isset($options['yespo_username'])) $organisationName = $options['yespo_username'];
        }
        if(!isset($organisationName)){
            $response = (new Yespo\Integrations\Esputnik\Esputnik_Account())->get_profile_name();
            if (!empty($response)) {
                $objResponse = json_decode($response);
                $organisationName = $objResponse->organisationName;
                $options['yespo_username'] = $organisationName;
                update_option('yespo_options', $options);
            }
        }
        if($organisationName){
            echo json_encode(['username' => $organisationName]);
        } else echo json_encode(0);
    }
    wp_die();
}
add_action('wp_ajax_get_account_yespo_name', 'get_account_profile_name');
add_action('wp_ajax_nopriv_get_account_yespo_name', 'get_account_profile_name');

/** check authorization **/
function check_api_authorization(){
    if(isset($_REQUEST['action']) && $_REQUEST['action'] === 'check_api_authorization_yespo' ) {
        if ( get_option( 'yespo_options' ) !== false ) {
            $options = get_option('yespo_options', array());
            if (isset($options['yespo_api_key'])) $yespo_api_key = $options['yespo_api_key'];
        }
        if(isset($yespo_api_key)){
            $result = (new \Yespo\Integrations\Esputnik\Esputnik_Account())->send_keys($yespo_api_key);
            (new \Yespo\Integrations\Esputnik\Esputnik_Account())->add_entry_auth_log($yespo_api_key, $result);
            if ($result === 200) {
                //(new \Yespo\Integrations\Esputnik\Esputnik_Account())->add_entry_auth_log($yespo_api_key, $result);
                echo json_encode(['auth' => 'success']);
            } else if($result === 401 || $result === 0){
                echo json_encode(['auth' => 'incorrect', 'code' => $result]);
            } else {
                //(new \Yespo\Integrations\Esputnik\Esputnik_Account())->add_entry_auth_log($yespo_api_key, $result);
                echo json_encode(0);
            }
        } else echo json_encode(0);
    }
    wp_die();
}
add_action('wp_ajax_check_api_authorization_yespo', 'check_api_authorization');
add_action('wp_ajax_nopriv_check_api_authorization_yespo', 'check_api_authorization');

/** check authorization via form **/
function yespo_save_settings() {

    if ( ! isset( $_POST['yespo_plugin_settings_nonce'] ) || ! wp_verify_nonce( $_POST['yespo_plugin_settings_nonce'], 'yespo_plugin_settings_save' ) ) {
        return;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if(isset($_REQUEST['action']) && $_REQUEST['action'] === 'check_api_key_esputnik' ) {
        //Esputnik_Metrika::count_start_connections();
        $options = [];
        $options['yespo_api_key'] = sanitize_text_field($_POST['yespo_api_key']);
        //update_option('yespo_options', $options);
        $accountClass = new \Yespo\Integrations\Esputnik\Esputnik_Account();
        $result = $accountClass->send_keys($options['yespo_api_key']);
        if ($result === 200) {
            update_option('yespo_options', $options);
            $userData = $accountClass->get_profile_name();
            if (!empty($userData)) {
                $objResponse = json_decode($userData);
                $organisationName = $objResponse->organisationName;
                $options['yespo_username'] = $organisationName;
                update_option('yespo_options', $options);
            }
            $response_data = array(
                'status' => 'success',
                'message' => '<div class="notice notice-success is-dismissible"><p>' . __("Authorization is successful", Y_TEXTDOMAIN) . '</p></div>',
                'total' => __("Completed successfully!", Y_TEXTDOMAIN),
                'username' => isset($organisationName) ? $organisationName : ''
            );
            //Esputnik_Metrika::count_finish_connections();
        } else {
            $response_data = array(
                'status' => 'error',
                'message' => '<div class="errorAPiKey"><p>' . __("Invalid API key", Y_TEXTDOMAIN) . '</p></div>',
                'total' => __("Completed unsuccessfully!", Y_TEXTDOMAIN),
            );
        }
        $accountClass->add_entry_auth_log($options['yespo_api_key'], $result);
        echo json_encode( $response_data );
        exit;
    }
}
add_action('wp_ajax_check_api_key_esputnik', 'yespo_save_settings');
add_action('wp_ajax_nopriv_check_api_key_esputnik', 'yespo_save_settings');

/** send user data to Yespo **/
function register_woocommerce_user_esputnik($user_id){
    if(!empty($user_id)) {
        $user_data = get_userdata($user_id);
        if(isset($user_data->user_email)) return (new \Yespo\Integrations\Esputnik\Esputnik_Contact())->create_on_yespo($user_data->user_email, $user_id);
    }
}
//add_action('user_register', 'register_woocommerce_user_esputnik', 10, 1);

/** Send guest user and order to Yespo **/
function register_woocommerce_guest_user_esputnik($order_id) {
    if(!empty($order_id)){
        $responseContact = (new \Yespo\Integrations\Esputnik\Esputnik_Order())->create_order_on_yespo(wc_get_order($order_id), 'create');
        $responseOrder = (new \Yespo\Integrations\Esputnik\Esputnik_Contact())->create_guest_user_on_yespo(wc_get_order($order_id));
    }
    if(isset($responseContact) && isset($responseOrder) && $responseOrder === true) return true;
}
//add_action('woocommerce_thankyou', 'register_woocommerce_guest_user_esputnik', 10, 1);

/** create guest user when make order via admin **/
function register_woocommerce_admin_guest_user_esputnik($order_id, $post) {
    if(isset($_POST['_wp_http_referer'])) parse_str(parse_url($_POST['_wp_http_referer'], PHP_URL_QUERY), $get_params);
    if (isset($get_params['page']) && $get_params['page'] === 'wc-orders' && isset($get_params['action']) && $get_params['action'] === 'new' && isset($_POST['action']) && $_POST['action'] === 'edit_order') {
        if(!empty($order_id)) return (new \Yespo\Integrations\Esputnik\Esputnik_Contact())->create_guest_user_admin_on_yespo($_POST);
    }
}
//add_action('woocommerce_process_shop_order_meta', 'register_woocommerce_admin_guest_user_esputnik', 10, 2);

/** update user profile on Yespo service **/
function update_user_profile_esputnik($user_id, $old_user_data) {
    if (!is_admin()) {
        return;
    }
    if(!empty($user_id)) {
        $user = get_user_by('id', $user_id);
        if(empty($user->billing_phone) && empty($user->shipping_phone)) (new \Yespo\Integrations\Esputnik\Esputnik_Contact())->remove_user_phone_on_yespo($user->data->user_email);
        if(isset($user->data->user_email)) return (new \Yespo\Integrations\Esputnik\Esputnik_Contact())->update_on_yespo($user);
    }
}
add_action('profile_update', 'update_user_profile_esputnik', 10, 2);

/***
 * EXPORT USERS
 */
/*** Get total users number ***/
function get_all_users_total() {
    $users = (new Yespo\Integrations\Esputnik\Esputnik_Export_Users)->get_users_total_count();
    if($users > 0) echo json_encode($users);
    else echo json_encode(0);
    wp_die();
}
add_action('wp_ajax_get_users_total', 'get_all_users_total');
add_action('wp_ajax_nopriv_get_users_total', 'get_all_users_total');

/*** Get total users number for export ***/
function get_all_users_total_export() {
    $user = new Yespo\Integrations\Esputnik\Esputnik_Export_Users();
    $users = $user->get_users_export_count();
    if($users > 0) echo json_encode(['percent' => floor( (Yespo\Integrations\Esputnik\Esputnik_Export_Service::get_exported_number() / Yespo\Integrations\Esputnik\Esputnik_Export_Service::get_export_total()) * 100), 'export' => $users, 'status' => $user->check_user_for_stopped()]);
    else echo json_encode(['export' => 0 ]);
    wp_die();

}
add_action('wp_ajax_get_users_total_export', 'get_all_users_total_export');
add_action('wp_ajax_nopriv_get_users_total_export', 'get_all_users_total_export');

/*** Export users to Yespo ***/
function export_user_data_to_esputnik(){
    if(isset($_REQUEST['action']) && $_REQUEST['action'] === 'export_user_data_to_esputnik' ) {
        //Esputnik_Metrika::count_start_exported();
        if(isset($_REQUEST['service'])){
            $response = (new Yespo\Integrations\Esputnik\Esputnik_Export_Users)->add_users_export_task();
            echo json_encode($response);
        }
    }
    wp_die();
}
add_action('wp_ajax_export_user_data_to_esputnik', 'export_user_data_to_esputnik');
add_action('wp_ajax_nopriv_export_user_data_to_esputnik', 'export_user_data_to_esputnik');

/*** Get process status of exporting users to Yespo ***/
function get_process_export_users_data_to_esputnik(){
    if(isset($_REQUEST['action']) && $_REQUEST['action'] === 'get_process_export_users_data_to_esputnik' ) {
        $response = (new Yespo\Integrations\Esputnik\Esputnik_Export_Users())->get_process_users_exported();
        if( !empty($response)) {
            echo json_encode(['total' => Yespo\Integrations\Esputnik\Esputnik_Export_Service::get_export_total(), 'exported' => Yespo\Integrations\Esputnik\Esputnik_Export_Service::get_exported_number(), 'percent' => floor(($response->exported / $response->total) * 100), 'status' => $response->status, 'code' => $response->code]);
        } else echo json_encode(0);
    }
    wp_die();
}
add_action('wp_ajax_get_process_export_users_data_to_esputnik', 'get_process_export_users_data_to_esputnik');
add_action('wp_ajax_nopriv_get_process_export_users_data_to_esputnik', 'get_process_export_users_data_to_esputnik');

/*** Get final status of exporting users to Yespo ***/
function get_final_export_users_data_to_esputnik(){
    if(isset($_REQUEST['action']) && $_REQUEST['action'] === 'final_export_users_data_to_esputnik' ) {
        (new Yespo\Integrations\Esputnik\Esputnik_Export_Users())->get_final_users_exported();
    }
    wp_die();
}
add_action('wp_ajax_final_export_users_data_to_esputnik', 'get_final_export_users_data_to_esputnik');
add_action('wp_ajax_nopriv_final_export_users_data_to_esputnik', 'get_final_export_users_data_to_esputnik');

/** remove woocommerce user **/
function delete_woocommerce_user( $user_id ) {
    (new Yespo\Integrations\Esputnik\Esputnik_Contact())->delete_from_yespo($user_id, true);
}
add_action( 'delete_user', 'delete_woocommerce_user');

/** Send data to yespo from subscription form **/
function custom_wpcf7_before_send_mail( $contact_form ) {
    if( $submission = WPCF7_Submission::get_instance()) {
        $postedData = $submission->get_posted_data();
        if(isset($postedData['your-email']) && !empty($postedData['your-email'])) (new Yespo\Integrations\Esputnik\Esputnik_Contact())->create_subscribed_user_on_yespo($postedData['your-email']);
    }
}
add_action( 'wpcf7_before_send_mail', 'custom_wpcf7_before_send_mail' );

/*** Remove personal data from Yespo after erase personal data ***/
function clean_user_data_after_data_erased( $erased ){
    if ( is_numeric( $erased ) && $erased > 0 ){
        $order = get_post( $erased );
        if ( $order && $order instanceof WP_Post ){
            if (filter_var($order->post_title, FILTER_VALIDATE_EMAIL)){
                $email = $order->post_title;
                if($email) $user = get_user_by('email', $email);
            }
            else {
                $user = get_user_by( 'login', $order->post_title );
                if($user) $email = $user->user_email;
            }

            if($user && $user->ID){
                (new \Yespo\Integrations\Esputnik\Esputnik_Logging_Data())->create(
                    $user->ID,
                    (new \Yespo\Integrations\Esputnik\Esputnik_Contact())->get_user_metafield_id($user->ID),
                    'delete');
            }

        }
    }
}
add_action( 'wp_privacy_personal_data_erased', 'clean_user_data_after_data_erased', 10, 1 );


/***
 * EXPORT ORDERS
 */
/*** Get total orders number ***/
function get_all_orders_total() {
    $orders = (new Yespo\Integrations\Esputnik\Esputnik_Export_Orders)->get_total_orders();
    if($orders > 0) echo json_encode($orders);
    else echo json_encode(0);
    wp_die();
}
add_action('wp_ajax_get_orders_total', 'get_all_orders_total');
add_action('wp_ajax_nopriv_get_orders_total', 'get_all_orders_total');


/*** Get total orders number for export ***/
function get_all_orders_total_export() {
    $order = new Yespo\Integrations\Esputnik\Esputnik_Export_Orders();
    $orders = $order->get_export_orders_count();
    if($orders > 0) echo json_encode(['percent' => floor( (Yespo\Integrations\Esputnik\Esputnik_Export_Service::get_exported_number() / Yespo\Integrations\Esputnik\Esputnik_Export_Service::get_export_total()) * 100), 'export' => $orders, 'status' => $order->check_orders_for_stopped()]);
    else echo json_encode(['export' => 0 ]);
    wp_die();

}
add_action('wp_ajax_get_orders_total_export', 'get_all_orders_total_export');
add_action('wp_ajax_nopriv_get_orders_total_export', 'get_all_orders_total_export');

/*** Export orders to Yespo ***/
function export_order_data_to_esputnik(){
    if(isset($_REQUEST['action']) && $_REQUEST['action'] === 'export_order_data_to_esputnik' ) {
        //Esputnik_Metrika::count_start_exported();
        if(isset($_REQUEST['service'])){
            $response = (new Yespo\Integrations\Esputnik\Esputnik_Export_Orders)->add_orders_export_task();
            echo json_encode($response);
        }
    }
    wp_die();
}
add_action('wp_ajax_export_order_data_to_esputnik', 'export_order_data_to_esputnik');
add_action('wp_ajax_nopriv_export_order_data_to_esputnik', 'export_order_data_to_esputnik');

/*** Get process status of exporting orders to Yespo ***/
function get_process_export_orders_data_to_esputnik(){
    if(isset($_REQUEST['action']) && $_REQUEST['action'] === 'get_process_export_orders_data_to_esputnik' ) {
        $response = (new Yespo\Integrations\Esputnik\Esputnik_Export_Orders())->get_process_orders_exported();
        if( !empty($response)) {
            echo json_encode(['total' => Yespo\Integrations\Esputnik\Esputnik_Export_Service::get_export_total(), 'exported' => Yespo\Integrations\Esputnik\Esputnik_Export_Service::get_exported_number(), 'percent' => floor(($response->exported / $response->total) * 100), 'status' => $response->status, 'code' => $response->code]);
        } else echo json_encode(0);
    }
    wp_die();
}
add_action('wp_ajax_get_process_export_orders_data_to_esputnik', 'get_process_export_orders_data_to_esputnik');
add_action('wp_ajax_nopriv_get_process_export_orders_data_to_esputnik', 'get_process_export_orders_data_to_esputnik');

/*** Get final status of exporting users to Yespo ***/
function get_final_export_orders_data_to_esputnik(){
    if(isset($_REQUEST['action']) && $_REQUEST['action'] === 'final_export_orders_data_to_esputnik' ) {
        (new Yespo\Integrations\Esputnik\Esputnik_Export_Orders())->get_final_orders_exported();
    }
    wp_die();
}
add_action('wp_ajax_final_export_orders_data_to_esputnik', 'get_final_export_orders_data_to_esputnik');
add_action('wp_ajax_nopriv_final_export_orders_data_to_esputnik', 'get_final_export_orders_data_to_esputnik');

/*** update order on yespo ***/
function update_order_after_changes_save( $order ) {

    if(function_exists('is_wc_privacy_remove_order') && is_wc_privacy_remove_order()) {
        return;
    }

    if($order !== null) (new \Yespo\Integrations\Esputnik\Esputnik_Order())->create_order_on_yespo($order, 'update');

}
//add_action( 'woocommerce_before_order_object_save', 'update_order_after_changes_save', 20, 1 );

/***
 * STOP EXPORT DATA
 */
function stop_export_to_yespo(){
    if(isset($_REQUEST['action']) && $_REQUEST['action'] === 'stop_export_data_to_yespo' ) {
        $exported = Yespo\Integrations\Esputnik\Esputnik_Export_Service::get_exported_number();
        $total = Yespo\Integrations\Esputnik\Esputnik_Export_Service::get_export_total();
        $users = (new Yespo\Integrations\Esputnik\Esputnik_Export_Users())->stop_export_users();
        $orders = (new Yespo\Integrations\Esputnik\Esputnik_Export_Orders())->stop_export_orders();
        if($exported !== $total) {
            //echo json_encode(floor( ($exported / $total) * 100));
            echo json_encode(floor( ($exported / $total) * 100));
        } else echo json_encode(0);

    }
    wp_die();
}
add_action('wp_ajax_stop_export_data_to_yespo', 'stop_export_to_yespo');
add_action('wp_ajax_nopriv_stop_export_data_to_yespo', 'stop_export_to_yespo');

/***
 * RESUME EXPORT DATA
 */
function resume_export_to_yespo(){
    if(isset($_REQUEST['action']) && $_REQUEST['action'] === 'resume_export_data_to_yespo' ) {
        $exported = Yespo\Integrations\Esputnik\Esputnik_Export_Service::get_exported_number();
        $total = Yespo\Integrations\Esputnik\Esputnik_Export_Service::get_export_total();
        $users = (new Yespo\Integrations\Esputnik\Esputnik_Export_Users())->resume_export_users();
        $orders = (new Yespo\Integrations\Esputnik\Esputnik_Export_Orders())->resume_export_orders();
        //if($users || $orders) {
        if($exported !== $total) {
            //echo json_encode(floor( ($exported / $total) * 100));
            echo json_encode(floor( ($exported / $total) * 100));
        } else echo json_encode(0);

    }
    wp_die();
}
add_action('wp_ajax_resume_export_data_to_yespo', 'resume_export_to_yespo');
add_action('wp_ajax_nopriv_resume_export_data_to_yespo', 'resume_export_to_yespo');

/***
 * CRON
 */
/*** CHANGE PERIOD UPDATING CRON TASKS ***/
function establish_custom_cron_interval( $schedules ) {
    $schedules['every_minute'] = array(
        'interval' => 60,
        'display'  => esc_html__( 'Start every minute' ), );
    return $schedules;
}
add_filter( 'cron_schedules', 'establish_custom_cron_interval' );

/*** START CRON JOB ***/
function yespo_export_data_cron_function(){
/*
    $file_path = $_SERVER['DOCUMENT_ROOT'] . '/filedebug.txt';
    $data_to_append = ' cron-works-333 ';
    $file_handle = fopen($file_path, 'a');
    if ($file_handle) {
        fwrite($file_handle, $data_to_append);
        fclose($file_handle);
    }
*/

    //(new \Yespo\Integrations\Esputnik\Esputnik_Export_Users())->start_export_users();
    (new \Yespo\Integrations\Esputnik\Esputnik_Export_Users())->start_bulk_export_users();
    //(new \Yespo\Integrations\Esputnik\Esputnik_Export_Orders())->start_export_orders();
    (new \Yespo\Integrations\Esputnik\Esputnik_Export_Orders())->start_bulk_export_orders();
    (new \Yespo\Integrations\Esputnik\Esputnik_Export_Orders())->schedule_export_orders();
    (new \Yespo\Integrations\Esputnik\Esputnik_Contact())->remove_user_after_erase();
    //(new \Yespo\Integrations\Esputnik\Esputnik_Contact())->update_woo_registered_user();

/*
        $file_path = $_SERVER['DOCUMENT_ROOT'] . '/filedebug.txt';
        $data_to_append = ' cron-works-0 ';
        $file_handle = fopen($file_path, 'a');
        if ($file_handle) {
            fwrite($file_handle, $data_to_append);
            fclose($file_handle);
        }
*/

}
add_action('yespo_export_data_cron', 'yespo_export_data_cron_function');


/**
 * GET PROFUCTS FEEDS FILES URLS
**/
function get_feed_urls_function() {

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if(isset($_REQUEST['action']) && $_REQUEST['action'] === 'get_feed_urls' ) {
        $response = [];

        $ctx = \Yespo\Integrations\Feeds\CTX_Feed::get_feed_url();
        if(count($ctx) > 0){
            foreach($ctx as $el) $response[] = $el;
        }

        $pf = \Yespo\Integrations\Feeds\Product_Feed_PRO_WC::get_feed_url();
        if(count($pf) > 0){
            foreach($pf as $el) $response[] = $el;
        }

        echo json_encode($response);
    }
}
add_action('wp_ajax_get_feed_urls', 'get_feed_urls_function');
add_action('wp_ajax_nopriv_get_feed_urls', 'get_feed_urls_function');



/***
/////////////////////////////////// TEST /////////////////////////////////
 */



function get_all_users($post)
{

    $email = 'test';
    //$email = 'test';
    if(!empty($email)){

        //$result = (new \Yespo\Integrations\Esputnik\Esputnik_Logging_Data())->create_single_contact_log($email);

        //var_dump($result);
        /*
        $user = get_user_by('email', $email);


        if($user && $user->ID){
            $result = (new \Yespo\Integrations\Esputnik\Esputnik_Contact())->get_yespo_user_id($user->ID);
            //$yespo_contact_id = get_user_meta($user->ID, 'yespo_contact_id', true);
            var_dump($result);
        }
        */
    }

    //(new \Yespo\Integrations\Esputnik\Esputnik_Account())->add_entry_auth_log('apiapi', '200');
    /*
    $result = (new \Yespo\Integrations\Esputnik\Esputnik_Account())->send_keys('712C72C7EFF83A56CAD2F7462714398E');
    var_dump($result);
*/
    //$orders = \Yespo\Integrations\Esputnik\Esputnik_Order_Mapping::create_bulk_order_export_array((new \Yespo\Integrations\Esputnik\Esputnik_Export_Orders())->get_bulk_export_orders());
    //var_dump($orders['orders']);
/*
    $export_res = (new \Yespo\Integrations\Esputnik\Esputnik_Order())->create_bulk_orders_on_yespo(
        \Yespo\Integrations\Esputnik\Esputnik_Order_Mapping::create_bulk_order_export_array(
            (new \Yespo\Integrations\Esputnik\Esputnik_Export_Orders())->get_bulk_export_orders()),
        'update');
    var_dump($export_res);
*/
    /*
    $orders = (new \Yespo\Integrations\Esputnik\Esputnik_Export_Orders())->get_bulk_export_orders();
    $arr = \Yespo\Integrations\Esputnik\Esputnik_Order_Mapping::create_bulk_order_export_array($orders);

    var_dump($arr);

    $orders = (new \Yespo\Integrations\Esputnik\Esputnik_Export_Orders())->get_bulk_export_orders();
    var_dump($orders);
*/

    global $wpdb;

    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM $wpdb->usermeta WHERE meta_key = %s",
            'yespo_contact_id'
        )
    );

    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s AND post_id IN (
                SELECT ID FROM {$wpdb->posts} WHERE post_type IN ('shop_order', 'shop_order_placehold')
            )",
            'sent_order_to_yespo'
        )
    );

/*
    $users = (new Yespo\Integrations\Esputnik\Esputnik_Export_Users())->get_users_bulk_export();
    var_dump($users);
*/
/*
    $users = [];
    for($i = 1; $i <= 500; $i++){
        //$user = new stdClass();
        $user = [];
        $user['email'] = 'testmail+' . $i . '@test.com.ua';
        $user['ID'] = 'te' . $i;
        $users[] = $user;
    }

    $usersObj = (new Yespo\Integrations\Esputnik\Esputnik_Contact_Mapping())->create_bulk_export_array($users);

    $file_path = $_SERVER['DOCUMENT_ROOT'] . '/filedebug.txt';
    $data_to_append = json_encode($usersObj);
    $file_handle = fopen($file_path, 'a');
    if ($file_handle) {
        fwrite($file_handle, $data_to_append);
        fclose($file_handle);
    }
*/
    //$order = wc_get_order(555);
    //$res = Yespo\Integrations\Esputnik\Esputnik_Order_Mapping::order_woo_to_yes($order);
    //$res = (new Yespo\Integrations\Esputnik\Esputnik_Order())->create_order_on_yespo($order);
    //$email = (!empty($order) && !is_bool($order) && method_exists($order, 'get_billing_email') && !empty($order->get_billing_email())) ? $order->get_billing_email() : 'deleted@site.invalid';
    //var_dump($res);
    //$res = Yespo\Integrations\Esputnik\Esputnik_Export_Service::get_export_total();
    //$res2 = Yespo\Integrations\Esputnik\Esputnik_Export_Service::get_exported_number();
    //var_dump($res . ' --- ' . $res2);
/*
    $response = (new Yespo\Integrations\Esputnik\Esputnik_Account())->get_profile_name();
    $res = (json_decode($response))->organisationName;
    var_dump($res);
*/
    //var_dump($response->organisationName);
    //$id = (new \Yespo\Integrations\Esputnik\Esputnik_Contact())->get_user_id_by_email('test');
    //var_dump($id);

    //$res = (new \Yespo\Integrations\Esputnik\Esputnik_Contact())->update_woo_registered_user();
    //$res = (new \Yespo\Integrations\Esputnik\Esputnik_Export_Orders())->get_orders_export_esputnik();
    //var_dump($res);
    /*
    global $wpdb;

    $query = "
        SELECT email
        FROM {$wpdb->prefix}newsletter
    ";

    $emails = $wpdb->get_col( $query );
    var_dump($emails);
    */
    /*
    $emails = (new \Yespo\Integrations\Plugins\Newsletter())->sendUserToYespo();
    var_dump($emails);
    die();
    */
    //$email = 'test';
    //var_dump(!email_exists($email));

    //$orders = (new \Yespo\Integrations\Esputnik\Esputnik_Export_Orders())->schedule_export_orders();
    //var_dump($orders);
    //die();

    //$res = (new \Yespo\Integrations\Esputnik\Esputnik_Contact())->remove_user_after_erase();
    //var_dump($res);
}
//add_action('save_post', 'get_all_users' , 10 , 1);

