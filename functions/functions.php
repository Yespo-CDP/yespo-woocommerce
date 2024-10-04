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

/**
 * Get the settings of the plugin in a filterable way
 *
 * @since 1.0.0
 * @return array
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function yespo_get_settings() {
    return apply_filters( 'yespo_get_settings', get_option( YESPO_TEXTDOMAIN . '-settings' ) );
}

/**
 * show error api key notice
 */
function yespo_error_api_key_admin_notice_function() {
    if ( get_option( 'yespo_options' ) !== false ) {
        $options = get_option('yespo_options', array());
        if (isset($options['yespo_api_key'])) $yespo_api_key = sanitize_text_field($options['yespo_api_key']);
        else $yespo_api_key = '';
    }
    if(!empty($yespo_api_key)){
        $result = (new \Yespo\Integrations\Esputnik\Yespo_Account())->send_keys($yespo_api_key);
        (new \Yespo\Integrations\Esputnik\Yespo_Account())->add_entry_auth_log($yespo_api_key, $result);
    }
    if (isset($result) && strpos($result, 'Connection refused') !== false) $result = 0;
    if(!empty($yespo_api_key) && $result === 401){
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo esc_html__("Invalid API key. Please delete the plugin and start the configuration from scratch using a valid API key. No data will be lost.", 'yespo-cdp'); ?></p>
        </div>
        <?php
    }
    if(isset($result) && $result === 0){
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo esc_html__('Outgoing activity on the server is blocked. Please contact your provider to resolve this issue. Data synchronization will automatically be resumed without any data loss once the issue is resolved.', 'yespo-cdp')?></p>
        </div>
        <?php
    }
}
add_action( 'admin_notices', 'yespo_error_api_key_admin_notice_function' );


/*** Get profile username on Yespo ***/
function yespo_get_account_profile_name_function(){
    if (isset($_REQUEST['action']) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'yespo_get_account_yespo_name') {
        $organisationName = '';
        if ( get_option( 'yespo_options' ) !== false ) {
            $options = get_option('yespo_options', array());
            if (isset($options['yespo_username'])) $organisationName = sanitize_text_field($options['yespo_username']);
        }
        if(!isset($organisationName)){
            $response = (new Yespo\Integrations\Esputnik\Yespo_Account())->get_profile_name();
            if (!empty($response)) {
                $objResponse = json_decode($response);
                $organisationName = sanitize_text_field($objResponse->organisationName);
                $options['yespo_username'] = $organisationName;
                update_option('yespo_options', $options);
            }
        }
        if($organisationName){
            wp_send_json_success(['username' => $organisationName]);
        } else wp_send_json_error(0);
    }
    wp_die();
}
add_action('wp_ajax_yespo_get_account_yespo_name', 'yespo_get_account_profile_name_function');
add_action('wp_ajax_nopriv_yespo_get_account_yespo_name', 'yespo_get_account_profile_name_function');

/** check authorization **/
function yespo_check_api_authorization_function(){
    if(isset($_REQUEST['action']) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'yespo_check_api_authorization_yespo' ) {
        if ( get_option( 'yespo_options' ) !== false ) {
            $options = get_option('yespo_options', array());
            if (isset($options['yespo_api_key'])) $yespo_api_key = sanitize_text_field($options['yespo_api_key']);
        }
        if(isset($yespo_api_key)){
            $result = (new \Yespo\Integrations\Esputnik\Yespo_Account())->send_keys($yespo_api_key);
            (new \Yespo\Integrations\Esputnik\Yespo_Account())->add_entry_auth_log($yespo_api_key, $result);
            if (strpos($result, 'Connection refused') !== false) $result = 0;
            if ($result === 200) {
                (new \Yespo\Integrations\Esputnik\Yespo_Export_Orders())->start_unexported_orders_because_errors();
                wp_send_json_success(['auth' => 'success']);
            } else if($result === 401 || $result === 0){
                wp_send_json_error(['auth' => 'incorrect', 'code' => $result]);
            } else {
                wp_send_json_error(['auth' => 'another_error']);
            }
        } else wp_send_json_error(['auth' => 'no_key']);
    }
    wp_die();
}
add_action('wp_ajax_yespo_check_api_authorization_yespo', 'yespo_check_api_authorization_function');
add_action('wp_ajax_nopriv_yespo_check_api_authorization_yespo', 'yespo_check_api_authorization_function');

/** check authorization via form **/
function yespo_save_settings_via_form_function() {

    if ( ! isset( $_POST['yespo_plugin_settings_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['yespo_plugin_settings_nonce'])), 'yespo_plugin_settings_save' ) ) {
        return;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if(isset($_REQUEST['action']) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'yespo_check_api_key_esputnik' ) {
        $options = [];
        if (isset($_POST['yespo_api_key'])) $options['yespo_api_key'] = sanitize_text_field(wp_unslash($_POST['yespo_api_key']));
        else $options['yespo_api_key'] = '';
        $accountClass = new \Yespo\Integrations\Esputnik\Yespo_Account();
        $result = $accountClass->send_keys($options['yespo_api_key']);
        if (strpos($result, 'Connection refused') !== false) $result = 0;
        if ($result === 200) {
            update_option('yespo_options', $options);
            $userData = $accountClass->get_profile_name();
            if (!empty($userData)) {
                $objResponse = json_decode($userData);
                $organisationName = sanitize_text_field($objResponse->organisationName);
                $options['yespo_username'] = $organisationName;
                update_option('yespo_options', $options);
            }
            $response_data = array(
                'status' => 'success',
                'message' => wp_kses_post('<div class="notice notice-success is-dismissible"><p>' . __("Authorization is successful", 'yespo-cdp') . '</p></div>'),
                'total' => esc_html__("Completed successfully!", 'yespo-cdp'),
                'username' => isset($organisationName) ? $organisationName : ''
            );
        } else if($result === 0){
            $response_data = array(
                'status' => 'incorrect',
                'code' => $result
            );
        } else {
            $response_data = array(
                'status' => 'error',
                'message' => wp_kses_post('<div class="errorAPiKey"><p>' . __("Invalid API key", 'yespo-cdp') . '</p></div>'),
                'total' => esc_html__("Completed unsuccessfully!", 'yespo-cdp'),
            );
        }
        $accountClass->add_entry_auth_log($options['yespo_api_key'], $result);
        wp_send_json( $response_data );
        exit;
    }
}
add_action('wp_ajax_yespo_check_api_key_esputnik', 'yespo_save_settings_via_form_function');
add_action('wp_ajax_nopriv_yespo_check_api_key_esputnik', 'yespo_save_settings_via_form_function');


/** update user profile on Yespo service **/
function yespo_update_user_profile_function($user_id, $old_user_data) {
    if (isset($_REQUEST['wc-ajax']) && sanitize_text_field(wp_unslash($_REQUEST['wc-ajax'])) === 'checkout') {
        return;
    }

    if (isset($_REQUEST['action']) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'wp-privacy-erase-personal-data') {
        return;
    }

    if (isset($_REQUEST['woocommerce-edit-address-nonce'])) {
        if (!check_admin_referer('woocommerce-edit-address', 'woocommerce-edit-address-nonce')) {
            return;
        }
        if(!empty($user_id)) {
            $user = get_user_by('id', $user_id);
            $request = map_deep($_REQUEST, function($value, $key) {
                if ($key === 'email') {
                    return sanitize_email($value);
                } elseif ($key === 'url') {
                    return esc_url_raw($value);
                } elseif (is_numeric($value)) {
                    return absint($value);
                }
                return sanitize_text_field($value);
            });
            return (new \Yespo\Integrations\Esputnik\Yespo_Contact())->update_woo_profile_yespo($request, $user);
        }
    } else {
        if (!empty($user_id)) {
            $user = get_user_by('id', $user_id);

            if (empty($user->billing_phone) && empty($user->shipping_phone)) {
                (new \Yespo\Integrations\Esputnik\Yespo_Contact())->remove_user_phone_on_yespo(sanitize_email($user->data->user_email));
            }

            if (isset($user->data->user_email)) {
                return (new \Yespo\Integrations\Esputnik\Yespo_Contact())->update_on_yespo($user);
            }
        }
    }
}
add_action('profile_update', 'yespo_update_user_profile_function', 10, 2);

/***
 * EXPORT USERS
 */
/*** Get total users number ***/
function yespo_get_all_users_total_function() {
    $users = (new Yespo\Integrations\Esputnik\Yespo_Export_Users)->get_users_total_count();
    if($users > 0) wp_send_json(intval($users));
    else wp_send_json( 0 );
    wp_die();
}
add_action('wp_ajax_get_users_total', 'yespo_get_all_users_total_function');
add_action('wp_ajax_nopriv_get_users_total', 'yespo_get_all_users_total_function');

/*** Get total users number for export ***/
function yespo_get_all_users_total_export_function() {
    $user = new Yespo\Integrations\Esputnik\Yespo_Export_Users();
    $users = $user->get_users_export_count();
    if($users > 0){
        $percent = floor( ( Yespo\Integrations\Esputnik\Yespo_Export_Service::get_exported_number() / Yespo\Integrations\Esputnik\Yespo_Export_Service::get_export_total() ) * 100 );
        $status = $user->check_user_for_stopped();

        wp_send_json([
            'percent' => intval( $percent ),
            'export' => intval( $users ),
            'status' => $status
        ]);
    } else wp_send_json(['export' => 0]);

    wp_die();

}
add_action('wp_ajax_yespo_get_users_total_export', 'yespo_get_all_users_total_export_function');
add_action('wp_ajax_nopriv_yespo_get_users_total_export', 'yespo_get_all_users_total_export_function');

/*** Export users to Yespo ***/
function yespo_export_user_data_to_esputnik_function(){
    if ( ! isset( $_POST['yespo_start_export_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['yespo_start_export_nonce'])), 'yespo_export_user_data_to_esputnik' ) ) {
        return;
    }
    if(isset($_REQUEST['action']) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'yespo_export_user_data_to_esputnik' ) {
        if(isset($_REQUEST['service'])){
            $response = (new Yespo\Integrations\Esputnik\Yespo_Export_Users)->add_users_export_task();
            wp_send_json($response);
        }
    }
    wp_die();
}
add_action('wp_ajax_yespo_export_user_data_to_esputnik', 'yespo_export_user_data_to_esputnik_function');
add_action('wp_ajax_nopriv_yespo_export_user_data_to_esputnik', 'yespo_export_user_data_to_esputnik_function');

/*** Get process status of exporting users to Yespo ***/
function yespo_get_process_export_users_function(){
    if(isset($_REQUEST['action']) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'yespo_get_process_export_users_data_to_esputnik' ) {
        $response = (new Yespo\Integrations\Esputnik\Yespo_Export_Users())->get_process_users_exported();
        if( !empty($response)) {
            wp_send_json(['total' => Yespo\Integrations\Esputnik\Yespo_Export_Service::get_export_total(), 'exported' => Yespo\Integrations\Esputnik\Yespo_Export_Service::get_exported_number(), 'percent' => floor(($response->exported / $response->total) * 100), 'status' => $response->status, 'code' => $response->code]);
        } else wp_send_json(0);
    }
    wp_die();
}
add_action('wp_ajax_yespo_get_process_export_users_data_to_esputnik', 'yespo_get_process_export_users_function');
add_action('wp_ajax_nopriv_yespo_get_process_export_users_data_to_esputnik', 'yespo_get_process_export_users_function');

/** remove woocommerce user **/
function yespo_delete_woocommerce_user_function( $user_id ) {
    $user = get_userdata($user_id);
    if($user && $user->user_email){
        (new Yespo\Integrations\Esputnik\Yespo_Contact())->add_entry_removed_user($user->user_email);
    }
    (new Yespo\Integrations\Esputnik\Yespo_Contact())->delete_from_yespo($user_id, true);
}
add_action( 'delete_user', 'yespo_delete_woocommerce_user_function');

/*** Remove personal data from Yespo after erase personal data ***/
function yespo_clean_user_data_after_data_erased_function( $erased ){
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
                (new \Yespo\Integrations\Esputnik\Yespo_Logging_Data())->create(
                    $user->ID,
                    (new \Yespo\Integrations\Esputnik\Yespo_Contact())->get_user_metafield_id($user->ID),
                    'delete');
            }

        }
    }
}
add_action( 'wp_privacy_personal_data_erased', 'yespo_clean_user_data_after_data_erased_function', 10, 1 );


/***
 * EXPORT ORDERS
 */
/*** Get total orders number ***/
function yespo_get_all_orders_total_function() {
    $orders = (new Yespo\Integrations\Esputnik\Yespo_Export_Orders)->get_total_orders();
    if($orders > 0) wp_send_json($orders);
    else wp_send_json(0);
    wp_die();
}
add_action('wp_ajax_get_orders_total', 'yespo_get_all_orders_total_function');
add_action('wp_ajax_nopriv_get_orders_total', 'yespo_get_all_orders_total_function');


/*** Get total orders number for export ***/
function yespo_get_all_orders_total_export_function() {
    $order = new Yespo\Integrations\Esputnik\Yespo_Export_Orders();
    $orders = $order->get_export_orders_count();
    if ( $orders > 0 ) {
        $percent = floor( ( Yespo\Integrations\Esputnik\Yespo_Export_Service::get_exported_number() / Yespo\Integrations\Esputnik\Yespo_Export_Service::get_export_total() ) * 100 );
        $status = $order->check_orders_for_stopped();
        wp_send_json([
            'percent' => $percent,
            'export' => $orders,
            'status' => $status
        ]);
    } else wp_send_json(['export' => 0]);
    wp_die();

}
add_action('wp_ajax_yespo_get_orders_total_export', 'yespo_get_all_orders_total_export_function');
add_action('wp_ajax_nopriv_yespo_get_orders_total_export', 'yespo_get_all_orders_total_export_function');

/*** Export orders to Yespo ***/
function yespo_export_order_data_function(){

    if ( ! isset( $_POST['yespo_start_export_orders_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['yespo_start_export_orders_nonce'])), 'yespo_export_order_data_to_esputnik' ) ) {
        return;
    }

    if(isset($_REQUEST['action']) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'yespo_export_order_data_to_esputnik' ) {
        if(isset($_REQUEST['service'])){
            $response = (new Yespo\Integrations\Esputnik\Yespo_Export_Orders)->add_orders_export_task();
            wp_send_json($response);
        }
    }
    wp_die();
}
add_action('wp_ajax_yespo_export_order_data_to_esputnik', 'yespo_export_order_data_function');
add_action('wp_ajax_nopriv_yespo_export_order_data_to_esputnik', 'yespo_export_order_data_function');

/*** Get process status of exporting orders to Yespo ***/
function yespo_get_process_export_orders_data_function(){
    if(isset($_REQUEST['action']) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'yespo_get_process_export_orders_data_to_esputnik' ) {
        $response = (new Yespo\Integrations\Esputnik\Yespo_Export_Orders())->get_process_orders_exported();
        if( !empty($response)) {
            wp_send_json( [
                'total' => Yespo\Integrations\Esputnik\Yespo_Export_Service::get_export_total(),
                'exported' => Yespo\Integrations\Esputnik\Yespo_Export_Service::get_exported_number(),
                'percent' => floor( ( $response->exported / $response->total ) * 100 ),
                'status' => $response->status,
                'code' => $response->code,
            ] );
        } else wp_send_json(0);
    }
    wp_die();
}
add_action('wp_ajax_yespo_get_process_export_orders_data_to_esputnik', 'yespo_get_process_export_orders_data_function');
add_action('wp_ajax_nopriv_yespo_get_process_export_orders_data_to_esputnik', 'yespo_get_process_export_orders_data_function');

/*** set label of creation time to order ***/
function yespo_add_order_time($order_id) {
    if (!$order_id) {
        return;
    }

    (new Yespo\Integrations\Esputnik\Yespo_Order())->add_time_label($order_id);
}

add_action('woocommerce_thankyou', 'yespo_add_order_time', 10, 1);

/***
 * STOP EXPORT DATA
 */
function yespo_stop_export_function(){
    if(isset($_REQUEST['action']) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'yespo_stop_export_data_to_yespo' ) {
        $exported = Yespo\Integrations\Esputnik\Yespo_Export_Service::get_exported_number();
        $total = Yespo\Integrations\Esputnik\Yespo_Export_Service::get_export_total();
        $users = (new Yespo\Integrations\Esputnik\Yespo_Export_Users())->stop_export_users();
        $orders = (new Yespo\Integrations\Esputnik\Yespo_Export_Orders())->stop_export_orders();
        if($exported !== $total) {
            wp_send_json( floor( ( $exported / $total ) * 100 ) );
        } else wp_send_json(0);

    }
    wp_die();
}
add_action('wp_ajax_yespo_stop_export_data_to_yespo', 'yespo_stop_export_function');
add_action('wp_ajax_nopriv_yespo_stop_export_data_to_yespo', 'yespo_stop_export_function');

/***
 * RESUME EXPORT DATA
 */
function yespo_resume_export_function(){
    if(isset($_REQUEST['action']) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'yespo_resume_export_data' ) {
        $exported = Yespo\Integrations\Esputnik\Yespo_Export_Service::get_exported_number();
        $total = Yespo\Integrations\Esputnik\Yespo_Export_Service::get_export_total();
        $users = (new Yespo\Integrations\Esputnik\Yespo_Export_Users())->resume_export_users();
        $orders = (new Yespo\Integrations\Esputnik\Yespo_Export_Orders())->resume_export_orders();
        if($exported !== $total) {
            wp_send_json( floor( ( $exported / $total ) * 100 ) );
        } else wp_send_json(0);
    }
    wp_die();
}
add_action('wp_ajax_yespo_resume_export_data', 'yespo_resume_export_function');
add_action('wp_ajax_nopriv_yespo_resume_export_data', 'yespo_resume_export_function');

/***
 * CRON
 */
/*** CHANGE PERIOD UPDATING CRON TASKS ***/
function yespo_establish_custom_cron_interval_function( $schedules ) {
    $schedules['every_minute'] = array(
        'interval' => 60,
        'display'  => esc_html__('Start every minute', 'yespo-cdp' ),
    );
    return $schedules;
}
add_filter( 'cron_schedules', 'yespo_establish_custom_cron_interval_function' );

/*** START CRON JOB ***/
function yespo_export_data_cron_function(){
    (new \Yespo\Integrations\Esputnik\Yespo_Export_Orders())->start_unexported_orders_because_errors();
    (new \Yespo\Integrations\Esputnik\Yespo_Export_Users())->start_active_bulk_export_users();
    (new \Yespo\Integrations\Esputnik\Yespo_Export_Orders())->start_bulk_export_orders();
    (new \Yespo\Integrations\Esputnik\Yespo_Export_Orders())->schedule_export_orders();
    (new \Yespo\Integrations\Esputnik\Yespo_Contact())->remove_user_after_erase();
}
add_action('yespo_export_data_cron', 'yespo_export_data_cron_function');

/***
 * JAVASCRIPT LOCALIZATION
 */
function yespo_enqueue_scripts_localization() {
    if (!wp_script_is(YESPO_TEXTDOMAIN . '-settings-admin', 'enqueued')) {
        wp_enqueue_script( YESPO_TEXTDOMAIN . '-settings-admin', plugins_url( 'assets/build/plugin-admin.js', YESPO_PLUGIN_ABSOLUTE ), array(), '1.0', true );
    }

    wp_localize_script( YESPO_TEXTDOMAIN . '-settings-admin', 'yespoVars', array(
        'h1' => esc_html__( 'Synchronization progress', 'yespo-cdp' ),
        'outSideText' => esc_html__( 'Synchronize contacts and orders for subsequent analysis and efficient data utilization using Yespo marketing automation tools', 'yespo-cdp' ),
        'h4' => esc_html__( 'The first data export will take some time; it will happen in the background, and it is not necessary to stay on the page', 'yespo-cdp' ),
        'resume' => esc_html__( 'The synchronization process has been paused; you can resume it from the moment of pausing without losing the previous progress', 'yespo-cdp' ),
        'error' => esc_html__( 'Some error have occurred. Try to resume synchronization. If it doesn’t help, contact Support', 'yespo-cdp' ),
        'error401' => esc_html__( 'Invalid API key. Please delete the plugin and start the configuration from scratch using a valid API key. No data will be lost.', 'yespo-cdp' ),
        'error555' => esc_html__( 'Outgoing activity on the server is blocked. Please contact your provider to resolve this issue. Data synchronization will automatically be resumed without any data loss once the issue is resolved.', 'yespo-cdp' ),
        'success' => esc_html__( 'Data is successfully synchronized', 'yespo-cdp' ),
        'synhStarted' => esc_html__( 'Data synchronization has started', 'yespo-cdp' ),
        'pluginUrl' => esc_url( YESPO_PLUGIN_URL ),
        'pauseButton' => esc_html__( 'Pause', 'yespo-cdp' ),
        'resumeButton' => esc_html__( 'Resume', 'yespo-cdp' ),
        'contactSupportButton' => esc_html__( 'Contact Support', 'yespo-cdp' ),
        'ajaxUrl' => esc_url( admin_url( 'admin-ajax.php' ) ),
        //'nonceApiKeyForm' => wp_create_nonce( 'yespo_api_key_nonce' ),
        'nonceApiKeyForm' => wp_nonce_field('yespo_plugin_settings_save', 'yespo_plugin_settings_nonce', true, false),
        'apiKeyValue' => isset( $yespo_api_key ) ? esc_js( $yespo_api_key ) : '',
        'apiKeyText' => esc_html__( 'The API key to connect the account can be received by the', 'yespo-cdp' ),
        'yespoLink' => 'https://my.yespo.io/settings-ui/#/api-keys-list',
        'yespoLinkText' => esc_html__( 'link', 'yespo-cdp' ),
        'yespoApiKey' => esc_js(__( 'API Key', 'yespo-cdp' )),
        'synchronize' =>  esc_js(__('Synchronize', 'yespo-cdp')),
        'startExportUsersNonce' => wp_create_nonce('yespo_export_user_data_to_esputnik'),
        'startExportOrdersNonce' => wp_create_nonce('yespo_export_order_data_to_esputnik')
    ));
}
add_action( 'admin_enqueue_scripts', 'yespo_enqueue_scripts_localization' );
