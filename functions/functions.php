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

function yespo_save_settings() {

    if ( ! isset( $_POST['yespo_plugin_settings_nonce'] ) || ! wp_verify_nonce( $_POST['yespo_plugin_settings_nonce'], 'yespo_plugin_settings_save' ) ) {
        return;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if(isset($_REQUEST['action']) && $_REQUEST['action'] === 'check_api_key_esputnik' ) {
        //Esputnik_Metrika::count_start_connections();
        $options['yespo_api_key'] = sanitize_text_field($_POST['yespo_api_key']);
        $result = (new \Yespo\Integrations\Esputnik\Esputnik_Account())->send_keys($options['yespo_api_key']);
        if ($result === 200) {
            $response_data = array(
                'status' => 'success',
                'message' => '<div class="notice notice-success is-dismissible"><p>' . __("Authorization is successful", Y_TEXTDOMAIN) . '</p></div>',
                'total' => __("Completed successfully!", Y_TEXTDOMAIN),
            );
            //Esputnik_Metrika::count_finish_connections();
        } else {
            $response_data = array(
                'status' => 'error',
                'message' => '<div class="notice notice-error is-dismissible"><p>' . __("The authorization attempt failed, please check your API Key and try again", Y_TEXTDOMAIN) . '</p></div>',
                'total' => __("Completed unsuccessfully!", Y_TEXTDOMAIN),
            );
        }
        update_option('yespo_options', $options);
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
    $users = (new Yespo\Integrations\Esputnik\Esputnik_Export_Users)->get_users_export_count();
    if($users > 0) echo json_encode($users);
    else echo json_encode(0);
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
            echo json_encode(['total' => $response->total, 'exported' => $response->exported, 'status' => $response->status, 'display' => $response->display]);
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
    (new Yespo\Integrations\Esputnik\Esputnik_Contact())->delete_from_yespo($user_id);
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
    $orders = (new Yespo\Integrations\Esputnik\Esputnik_Export_Orders)->get_export_orders_count();
    if($orders > 0) echo json_encode($orders);
    else echo json_encode(0);
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

/*** Get process status of exporting users to Yespo ***/
function get_process_export_orders_data_to_esputnik(){
    if(isset($_REQUEST['action']) && $_REQUEST['action'] === 'get_process_export_orders_data_to_esputnik' ) {
        $response = (new Yespo\Integrations\Esputnik\Esputnik_Export_Orders())->get_process_orders_exported();
        if( !empty($response)) {
            echo json_encode(['total' => $response->total, 'exported' => $response->exported, 'status' => $response->status, 'display' => $response->display]);
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
    (new \Yespo\Integrations\Esputnik\Esputnik_Export_Users())->start_export_users();
    (new \Yespo\Integrations\Esputnik\Esputnik_Export_Orders())->start_export_orders();
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
