<?php
/**
 * Yespo
 *
 * @package   Yespo
 * @author    Yespo Omnichannel CDP <yespoplugin@yespo.io>
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
        $result = json_decode( (new \Yespo\Integrations\Esputnik\Yespo_Account())->send_keys($yespo_api_key));
        (new \Yespo\Integrations\Esputnik\Yespo_Account())->add_entry_auth_log($yespo_api_key, $result);
    }
    if (isset($result) && strpos($result->code, 'Connection refused') !== false) $result->code = 0;
    if(!empty($yespo_api_key) && $result->code === 401){
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo esc_html__("Invalid API key. Please delete the plugin and start the configuration from scratch using a valid API key. No data will be lost.", 'yespo-cdp'); ?></p>
        </div>
        <?php
    }
    if(isset($result) && $result->code === 0){
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

    if ( ! isset( $_GET['yespo_get_account_yespo_name_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_GET['yespo_get_account_yespo_name_nonce'])), 'yespo_get_account_yespo_name' ) ) {
        return;
    }

    if (isset($_GET['action']) && sanitize_text_field(wp_unslash($_GET['action'])) === 'yespo_get_account_yespo_name') {
        $organisationName = '';
        $orgId = '';
        if ( get_option( 'yespo_options' ) !== false ) {
            $options = get_option('yespo_options', array());
            if (isset($options['yespo_username'])) $organisationName = sanitize_text_field($options['yespo_username']);
            if (isset($options['yespo_orgId'])) $orgId = sanitize_text_field($options['yespo_orgId']);
        }
        if(!isset($organisationName) || !isset($orgId)){
            $response = (new Yespo\Integrations\Esputnik\Yespo_Account())->get_profile_name();
            if (!empty($response)) {
                $objResponse = json_decode($response);
                if(!isset($organisationName)) {
                    $organisationName = sanitize_text_field($objResponse->response_body->organisationName);
                    $options['yespo_username'] = $organisationName;
                }
                if(!isset($orgId)) {
                    $orgId = sanitize_text_field($objResponse->response_body->orgId);
                    $options['yespo_orgId'] = $orgId;
                }
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

/** check authorization **/
function yespo_check_api_authorization_function(){

    if ( ! isset( $_GET['yespo_check_api_authorization_yespo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_GET['yespo_check_api_authorization_yespo_nonce'])), 'yespo_check_api_authorization_yespo' ) ) {
        return;
    }

    if(isset($_GET['action']) && sanitize_text_field(wp_unslash($_GET['action'])) === 'yespo_check_api_authorization_yespo' ) {
        if ( get_option( 'yespo_options' ) !== false ) {
            $options = get_option('yespo_options', array());
            if (isset($options['yespo_api_key'])) $yespo_api_key = sanitize_text_field($options['yespo_api_key']);
        }
        if(isset($yespo_api_key)){
            $result = json_decode((new \Yespo\Integrations\Esputnik\Yespo_Account())->send_keys($options['yespo_api_key']));
            (new \Yespo\Integrations\Esputnik\Yespo_Account())->add_entry_auth_log($yespo_api_key, $result);
            if (isset($result) && strpos($result->code, 'Connection refused') !== false) $result->code = 0;
            if ($result && $result->code === 200) {
                (new \Yespo\Integrations\Esputnik\Yespo_Export_Orders())->start_unexported_orders_because_errors();

                /* webtracking */
                $is_webtracking = (new Yespo\Integrations\Webtracking\Yespo_Web_Tracking_Script())->is_script_in_options();
                $display_webtracking_form = (new Yespo\Integrations\Webtracking\Yespo_Web_Tracking_Script())->get_form_500();
                $webtracking_label_400 = (new Yespo\Integrations\Webtracking\Yespo_Web_Tracking_Script())->get_label_400();
                $webtracking_label_500 = (new Yespo\Integrations\Webtracking\Yespo_Web_Tracking_Script())->get_label_500();

                $get_webtracking_response = 200;
                if((!$is_webtracking && $display_webtracking_form) || (!$is_webtracking && $webtracking_label_400) ) $get_webtracking_response = 400;
                else if(!$is_webtracking && $webtracking_label_500) $get_webtracking_response = 500;

                //(new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('api', $is_webtracking, 'pered umovou 500 api webtracking');
                if($get_webtracking_response && ($get_webtracking_response === 400 || $get_webtracking_response === 404) && !$is_webtracking) $is_webtracking = false;
                if($get_webtracking_response && $get_webtracking_response === 500 && !$is_webtracking){

                    (new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('500', $get_webtracking_response, 'umova inside 500 api');
                    $is_webtracking = (new Yespo\Integrations\Webtracking\Yespo_Web_Tracking_Script())->check_exist_label_500();
                }
                if($get_webtracking_response > 199 && $get_webtracking_response < 300){
                    (new Yespo\Integrations\Webtracking\Yespo_Web_Tracking_Script())->remove_label_500();
                    (new Yespo\Integrations\Webtracking\Yespo_Web_Tracking_Script())->remove_form_500();
                }

                /* webpush */
                $is_webpush = (new Yespo\Integrations\Webpush\Yespo_Web_Push())->check_webpush_installation(); // webpush
                $display_wepush_form = (new Yespo\Integrations\Webtracking\Yespo_Web_Tracking_Script())->get_form_500();
                $webpush_label_400 = (new Yespo\Integrations\Webtracking\Yespo_Web_Tracking_Script())->get_label_400();
                $webpush_label_500 = (new Yespo\Integrations\Webtracking\Yespo_Web_Tracking_Script())->get_label_500();

                $get_push_response = 200;
                if((!$is_webpush && $display_wepush_form) || (!$is_webpush && $webpush_label_400) ) $get_push_response = 400;
                else if(!$is_webpush && $webpush_label_500) $get_push_response = 500;

                //(new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('api', $is_webpush, 'pered umovou 500 api');
                if($get_push_response && ($get_push_response === 400 || $get_push_response === 404) && !$is_webpush) $is_webpush = false;
                if($get_push_response && $get_push_response === 500 && !$is_webpush){

                    (new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('500', $get_push_response, 'umova inside 500 api');
                    
                    $is_webpush = (new Yespo\Integrations\Webpush\Yespo_Web_Push())->check_exist_label_500();
                }
                if($get_push_response > 199 && $get_push_response < 300){
                    (new Yespo\Integrations\Webpush\Yespo_Web_Push())->remove_label_500();
                    (new Yespo\Integrations\Webpush\Yespo_Web_Push())->remove_form_500();
                }

                (new Yespo\Integrations\Webtracking\Yespo_Web_Tracking_Script())->add_tenant_id_to_options(); // tenant
                (new \Yespo\Integrations\Esputnik\Yespo_Logging_Data())->remove_contact_log_column(); //update tables

                wp_send_json_success(['auth' => 'success', 'tracker' => $is_webtracking, 'webpush' => $is_webpush]);
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

/** check authorization via form **/
function yespo_save_settings_via_form_function() {

    if ( ! isset( $_POST['yespo_plugin_settings_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['yespo_plugin_settings_nonce'])), 'yespo_plugin_settings_save' ) ) {
        return;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if(isset($_POST['action']) && sanitize_text_field(wp_unslash($_POST['action'])) === 'yespo_check_api_key_esputnik' ) {
        $options = [];
        if (isset($_POST['yespo_api_key'])) $options['yespo_api_key'] = sanitize_text_field(wp_unslash($_POST['yespo_api_key']));
        else $options['yespo_api_key'] = '';
        $accountClass = new \Yespo\Integrations\Esputnik\Yespo_Account();
        $result = json_decode($accountClass->send_keys($options['yespo_api_key']));
        if (isset($result) && strpos($result->code, 'Connection refused') !== false) $result->code = 0;
        if ($result->code === 200) {
            update_option('yespo_options', $options);
            $userData = $accountClass->get_profile_name();
            if (!empty($userData)) {
                $objResponse = json_decode($userData);
                $organisationName = sanitize_text_field($objResponse->organisationName);
                $options['yespo_username'] = $organisationName;
                $orgId = sanitize_text_field($objResponse->orgId);
                $options['yespo_orgId'] = $orgId;
                update_option('yespo_options', $options);
                Yespo\Integrations\Webtracking\Yespo_Logging_Remote::add_api_key_success();
                $get_webtracking_response = (new Yespo\Integrations\Webtracking\Yespo_Web_Tracking_Script())->make_tracking_script(); //comment when button for tracking
                $get_push_response = (new Yespo\Integrations\Webpush\Yespo_Web_Push())->start(); //webpush
            }
            $is_webtracking = (new Yespo\Integrations\Webtracking\Yespo_Web_Tracking_Script())->is_script_in_options();
            $is_webpush = (new Yespo\Integrations\Webpush\Yespo_Web_Push())->check_webpush_installation(); // webpush

            /* webtraking */
            (new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('500', $get_webtracking_response, 'response via form webtracking');

            if($get_webtracking_response && ($get_webtracking_response === 400 || $get_webtracking_response === 404) && !$is_webtracking) {
                $is_webtracking = false;
                (new Yespo\Integrations\Webtracking\Yespo_Web_Tracking_Script())->add_label_400();
            }
            if($get_webtracking_response && $get_webtracking_response== 500 && !$is_webtracking){
                $is_webtracking = 500;
                (new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('500', 'inside 500 auth form', 'if 500 works webtracking');
                (new Yespo\Integrations\Webtracking\Yespo_Web_Tracking_Script())->add_label_500();
            }

            /* webpush */
            (new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('500', $get_push_response, 'response via form webpush');
            if($get_push_response && ($get_push_response === 400 || $get_push_response === 404) && !$is_webpush){
                $is_webpush = false;
                (new Yespo\Integrations\Webpush\Yespo_Web_Push())->add_label_400();
            }
            if($get_push_response && $get_push_response === 500 && !$is_webpush){
                $is_webpush = 500;
                (new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('500', 'inside 500 auth form', 'if 500 works');
                (new Yespo\Integrations\Webpush\Yespo_Web_Push())->add_label_500();
            }

            $response_data = array(
                'status' => 'success',
                'message' => wp_kses_post('<div class="notice notice-success is-dismissible"><p>' . __("Authorization is successful", 'yespo-cdp') . '</p></div>'),
                'total' => esc_html__("Completed successfully!", 'yespo-cdp'),
                'username' => isset($organisationName) ? $organisationName : '',
                'tracker' => $is_webtracking, // webtracking
                'webpush' => $is_webpush //webpush
            );
        } else if($result === 0){
            Yespo\Integrations\Webtracking\Yespo_Logging_Remote::add_api_key_error($result);
            $response_data = array(
                'status' => 'incorrect',
                'code' => $result
            );
        } else {
            Yespo\Integrations\Webtracking\Yespo_Logging_Remote::add_api_key_error($result->message);
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


/** get yespo tracking code **/
function yespo_get_yespo_tracking_code_function() {

    if ( ! isset( $_GET['yespo_get_tracking_script_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_GET['yespo_get_tracking_script_nonce'])), 'yespo_get_tracking_script' ) ) {
        return;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if (get_option('yespo_options') !== false) {
        $options = get_option('yespo_options', array());
        if (isset($options['yespo_api_key'])) $yespo_api_key = sanitize_text_field($options['yespo_api_key']);

        if(isset($yespo_api_key)) {
            $accountClass = new \Yespo\Integrations\Esputnik\Yespo_Account();
            $result = json_decode($accountClass->send_keys($options['yespo_api_key']));
            if (isset($result) && strpos($result->code, 'Connection refused') !== false) $result->code = 0;
            if ($result && $result->code === 200) {

                $get_webtracker_response = (new Yespo\Integrations\Webtracking\Yespo_Web_Tracking_Script())->make_tracking_script();

                $is_tracking = (new Yespo\Integrations\Webtracking\Yespo_Web_Tracking_Script())->is_script_in_options();

                $webtracking500 = false;
                if($get_webtracker_response === 500 && !$is_tracking){
                    (new Yespo\Integrations\Webtracking\Yespo_Web_Tracking_Script())->remove_form_500();
                    (new Yespo\Integrations\Webtracking\Yespo_Web_Tracking_Script())->add_label_500();
                    $webtracking500 = true;
                }
                $webtracking400 = false;
                if(($get_webtracker_response === 400 || $get_webtracker_response === 404) && !$is_tracking) $webtracking400 = true;

                if ($is_tracking) {
                    $response_data = array(
                        'status' => 'success',
                        'message' => wp_kses_post('<div class="notice notice-success is-dismissible"><p>' . __("Web script installed successfully", 'yespo-cdp') . '</p></div>'),
                        'tracker' => $is_tracking
                    );


                } else {
                    $response_data = array(
                        'status' => 'error',
                        'message' => wp_kses_post('<div class="errorAPiKey"><p>' . __("Problem with installation web tracking script", 'yespo-cdp') . '</p></div>'),
                        'total' => esc_html__("Completed unsuccessfully!", 'yespo-cdp'),
                        'webtracking400' => $webtracking400,
                        'webtracking500' => $webtracking500,
                    );
                }
                wp_send_json($response_data);
                exit;

            }
        }
    }
}
add_action('wp_ajax_yespo_get_webtracking_script_action', 'yespo_get_yespo_tracking_code_function');


function yespo_get_yespo_webpush_code_function() {

    if ( ! isset( $_GET['yespo_get_webpush_script_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_GET['yespo_get_webpush_script_nonce'])), 'yespo_get_webpush_script' ) ) {
        return;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if (get_option('yespo_options') !== false) {
        $options = get_option('yespo_options', array());
        if (isset($options['yespo_api_key'])) $yespo_api_key = sanitize_text_field($options['yespo_api_key']);

        if(isset($yespo_api_key)) {
            $accountClass = new \Yespo\Integrations\Esputnik\Yespo_Account();
            $result = json_decode($accountClass->send_keys($options['yespo_api_key']));
            if (isset($result) && strpos($result->code, 'Connection refused') !== false) $result->code = 0;
            if ($result && $result->code === 200) {

                $get_push_response = (new Yespo\Integrations\Webpush\Yespo_Web_Push())->start(); //webpush

                $is_webpush = (new Yespo\Integrations\Webpush\Yespo_Web_Push())->check_webpush_installation(); // webpush

                $webpush500 = false;
                if($get_push_response === 500 && !$is_webpush){
                    (new Yespo\Integrations\Webpush\Yespo_Web_Push())->remove_form_500();
                    (new Yespo\Integrations\Webpush\Yespo_Web_Push())->add_label_500();
                    $webpush500 = true;
                }

                $webpush400 = false;
                if(($get_push_response === 400 || $get_push_response === 404) && !$is_webpush) $webpush400 = true;

                if ($is_webpush) {
                    $response_data = array(
                        'status' => 'success',
                        'message' => wp_kses_post('<div class="notice notice-success is-dismissible"><p>' . __("Web script installed successfully", 'yespo-cdp') . '</p></div>'),
                        'webpush' => $is_webpush
                    );


                } else {
                    $response_data = array(
                        'status' => 'error',
                        'message' => wp_kses_post('<div class="errorAPiKey"><p>' . __("Problem with installation web tracking script", 'yespo-cdp') . '</p></div>'),
                        'total' => esc_html__("Completed unsuccessfully!", 'yespo-cdp'),
                        'webpush400' => $webpush400,
                        'webpush500' => $webpush500,
                    );
                }
                wp_send_json($response_data);
                exit;

            }
        }
    }
}
add_action('wp_ajax_yespo_get_webpush_script_action', 'yespo_get_yespo_webpush_code_function');

/*** Get profile username on Yespo ***/
function yespo_get_current_status_500_function(){

    if ( ! isset( $_GET['yespo_get_current_status_500_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_GET['yespo_get_current_status_500_nonce'])), 'yespo_get_current_status_500' ) ) {
        return;
    }

    if (isset($_GET['action']) && sanitize_text_field(wp_unslash($_GET['action'])) === 'yespo_get_current_status_500') {

        $get_webtracking_label_400 =  (new Yespo\Integrations\Webtracking\Yespo_Web_Tracking_Script())->get_label_400();
        $get_webtracking_label_500 =  (new Yespo\Integrations\Webtracking\Yespo_Web_Tracking_Script())->get_label_500();
        $get_webpush_label_400 =  (new \Yespo\Integrations\Webpush\Yespo_Web_Push())->get_label_400();
        $get_webpush_label_500 =  (new \Yespo\Integrations\Webpush\Yespo_Web_Push())->get_label_500();
        $webtracking_code = 200;
        $webpush_code = 200;
        if ($get_webtracking_label_400 !== false) $webtracking_code = 400;
        else if ($get_webtracking_label_500 !== false) $webtracking_code = 500;

        if ($get_webpush_label_400 !== false) $webpush_code = 400;
        else if ($get_webpush_label_500 !== false) $webpush_code = 500;


        $get_webtracking_500 = (new Yespo\Integrations\Webtracking\Yespo_Web_Tracking_Script())->get_form_500();
        $get_webpush_500 = (new \Yespo\Integrations\Webpush\Yespo_Web_Push())->get_form_500();

        $response_data = array(
            'status' => 'success',
            'webtracking_code' => $webtracking_code,
            'webpush_code' => $webpush_code,
            'tracker' => $get_webtracking_500, // webtracking
            'webpush' => $get_webpush_500 //webpush
        );

        wp_send_json($response_data);
        exit;

    }
    wp_die();
}
add_action('wp_ajax_yespo_get_current_status_500', 'yespo_get_current_status_500_function');

/** update user profile on Yespo service **/
function yespo_update_user_profile_function($user_id, $old_user_data) {
    if (isset($_POST['wc-ajax']) && sanitize_text_field(wp_unslash($_POST['wc-ajax'])) === 'checkout') {
        return;
    }

    if (isset($_POST['action']) && sanitize_text_field(wp_unslash($_POST['action'])) === 'wp-privacy-erase-personal-data') {
        return;
    }

    if (isset($_POST['woocommerce-edit-address-nonce'])) {
        if (!check_admin_referer('woocommerce-edit-address', 'woocommerce-edit-address-nonce')) {
            return;
        }
        if (!empty($user_id)) {
            $user = get_user_by('id', $user_id);
            $request = [
                'first_name'        => isset($_POST['first_name']) ? sanitize_text_field(wp_unslash($_POST['first_name'])) : '',
                'last_name'         => isset($_POST['last_name']) ? sanitize_text_field(wp_unslash($_POST['last_name'])) : '',
                'email'             => isset($_POST['email']) && is_email(wp_unslash($_POST['email'])) ? sanitize_email(wp_unslash($_POST['email'])) : '',
                'billing_address_1' => isset($_POST['billing_address_1']) ? sanitize_text_field(wp_unslash($_POST['billing_address_1'])) : '',
                'billing_address_2' => isset($_POST['billing_address_2']) ? sanitize_text_field(wp_unslash($_POST['billing_address_2'])) : '',
                'billing_city'      => isset($_POST['billing_city']) ? sanitize_text_field(wp_unslash($_POST['billing_city'])) : '',
                'billing_postcode'  => isset($_POST['billing_postcode']) ? sanitize_text_field(wp_unslash($_POST['billing_postcode'])) : '',
                'billing_country'   => isset($_POST['billing_country']) ? sanitize_text_field(wp_unslash($_POST['billing_country'])) : '',
                'billing_state'     => isset($_POST['billing_state']) ? sanitize_text_field(wp_unslash($_POST['billing_state'])) : '',
                'billing_phone'     => isset($_POST['billing_phone']) ? sanitize_text_field(wp_unslash($_POST['billing_phone'])) : ''
            ];

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

/*** Get total users number for export ***/
function yespo_get_all_users_total_export_function() {

    if ( ! isset( $_GET['yespo_get_users_total_export_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_GET['yespo_get_users_total_export_nonce'])), 'yespo_get_users_total_export' ) ) {
        return;
    }

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

/*** Export users to Yespo ***/
function yespo_export_user_data_to_esputnik_function(){
    if ( ! isset( $_POST['yespo_start_export_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['yespo_start_export_nonce'])), 'yespo_export_user_data_to_esputnik' ) ) {
        return;
    }
    if(isset($_POST['action']) && sanitize_text_field(wp_unslash($_POST['action'])) === 'yespo_export_user_data_to_esputnik' ) {
        if(isset($_POST['service'])){
            $response = (new Yespo\Integrations\Esputnik\Yespo_Export_Users)->add_users_export_task();
            wp_send_json($response);
        }
    }
    wp_die();
}
add_action('wp_ajax_yespo_export_user_data_to_esputnik', 'yespo_export_user_data_to_esputnik_function');

/*** Get process status of exporting users to Yespo ***/
function yespo_get_process_export_users_function(){
    if ( ! isset( $_GET['yespo_get_process_export_users_data_to_esputnik_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_GET['yespo_get_process_export_users_data_to_esputnik_nonce'])), 'yespo_get_process_export_users_data_to_esputnik' ) ) {
        return;
    }

    if(isset($_GET['action']) && sanitize_text_field(wp_unslash($_GET['action'])) === 'yespo_get_process_export_users_data_to_esputnik' ) {
        $response = (new Yespo\Integrations\Esputnik\Yespo_Export_Users())->get_process_users_exported();
        if( !empty($response)) {
            wp_send_json(['total' => Yespo\Integrations\Esputnik\Yespo_Export_Service::get_export_total(), 'exported' => Yespo\Integrations\Esputnik\Yespo_Export_Service::get_exported_number(), 'percent' => floor(($response->exported / $response->total) * 100), 'status' => $response->status, 'code' => $response->code]);
        } else wp_send_json(0);
    }
    wp_die();
}
add_action('wp_ajax_yespo_get_process_export_users_data_to_esputnik', 'yespo_get_process_export_users_function');

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

/*** Get total orders number for export ***/
function yespo_get_all_orders_total_export_function() {
    if ( ! isset( $_GET['yespo_get_orders_total_export_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_GET['yespo_get_orders_total_export_nonce'])), 'yespo_get_orders_total_export' ) ) {
        return;
    }

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

/*** Export orders to Yespo ***/
function yespo_export_order_data_function(){

    if ( ! isset( $_POST['yespo_start_export_orders_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['yespo_start_export_orders_nonce'])), 'yespo_export_order_data_to_esputnik' ) ) {
        return;
    }

    if(isset($_POST['action']) && sanitize_text_field(wp_unslash($_POST['action'])) === 'yespo_export_order_data_to_esputnik' ) {
        if(isset($_POST['service'])){
            $response = (new Yespo\Integrations\Esputnik\Yespo_Export_Orders)->add_orders_export_task();
            wp_send_json($response);
        }
    }
    wp_die();
}
add_action('wp_ajax_yespo_export_order_data_to_esputnik', 'yespo_export_order_data_function');

/*** Get process status of exporting orders to Yespo ***/
function yespo_get_process_export_orders_data_function(){
    if ( ! isset( $_GET['yespo_get_process_export_orders_data_to_esputnik_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_GET['yespo_get_process_export_orders_data_to_esputnik_nonce'])), 'yespo_get_process_export_orders_data_to_esputnik' ) ) {
        return;
    }

    if(isset($_GET['action']) && sanitize_text_field(wp_unslash($_GET['action'])) === 'yespo_get_process_export_orders_data_to_esputnik' ) {
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

    if ( ! isset( $_GET['yespo_stop_export_data_to_yespo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_GET['yespo_stop_export_data_to_yespo_nonce'])), 'yespo_stop_export_data_to_yespo' ) ) {
        return;
    }

    if(isset($_GET['action']) && sanitize_text_field(wp_unslash($_GET['action'])) === 'yespo_stop_export_data_to_yespo' ) {
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

/***
 * RESUME EXPORT DATA
 */
function yespo_resume_export_function(){

    if ( ! isset( $_GET['yespo_resume_export_data_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_GET['yespo_resume_export_data_nonce'])), 'yespo_resume_export_data' ) ) {
        return;
    }

    if(isset($_GET['action']) && sanitize_text_field(wp_unslash($_GET['action'])) === 'yespo_resume_export_data' ) {
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
    set_time_limit(60);
    (new \Yespo\Integrations\Esputnik\Yespo_Account())->set_orgId(); //set orgId value
    (new \Yespo\Integrations\Esputnik\Yespo_Export_Orders())->start_unexported_orders_because_errors();
    (new \Yespo\Integrations\Esputnik\Yespo_Export_Users())->start_active_bulk_export_users();
    (new \Yespo\Integrations\Esputnik\Yespo_Export_Orders())->start_bulk_export_orders();
    (new \Yespo\Integrations\Esputnik\Yespo_Export_Orders())->schedule_export_orders();
    (new \Yespo\Integrations\Esputnik\Yespo_Contact())->remove_user_after_erase();
    //(new Yespo\Integrations\Webtracking\Yespo_Web_Tracking_Script())->check_script_code_cron();
    (new Yespo\Integrations\Webtracking\Yespo_Web_Tracking_Script())->check_exist_label_500();
    (new \Yespo\Integrations\Webpush\Yespo_Web_Push())->check_exist_label_500();
}
add_action('yespo_export_data_cron', 'yespo_export_data_cron_function');

function yespo_script_cron_event_function(){
    //(new Yespo\Integrations\Webtracking\Yespo_Web_Tracking_Script())->check_script_code_cron();
    (new \Yespo\Integrations\Esputnik\Yespo_Export_Orders())->remove_old_json_log_entries();
}
add_action('yespo_script_cron_event', 'yespo_script_cron_event_function');

function yespo_remove_old_logs_function(){
    (new \Yespo\Integrations\Webtracking\Yespo_Logger())->remove_old_logs(); //ones per day
}
add_action('yespo_remove_old_logs', 'yespo_remove_old_logs_function');

/***
 * JAVASCRIPT ADMIN LOCALIZATION
 */
function yespo_enqueue_scripts_localization() {
    if (!wp_script_is(YESPO_TEXTDOMAIN . '-settings-admin', 'enqueued')) {
        wp_enqueue_script( YESPO_TEXTDOMAIN . '-settings-admin', plugins_url( 'assets/build/plugin-admin.js', YESPO_PLUGIN_ABSOLUTE ), array(), '1.0', true );
    }

    return \Yespo\Integrations\Esputnik\Yespo_Localization::localize_template();
}
add_action( 'admin_enqueue_scripts', 'yespo_enqueue_scripts_localization' );


/**
 * WEB TRACKING
 **/
//add tracking code and send tracking data to yespo
function yespo_add_tracking_codes() {
    $script = (new Yespo\Integrations\Webtracking\Yespo_Web_Tracking_Script())->get_script_from_options();
    if($script) {
        echo $script;
        do_action('yespo_after_scripts');
    }
}
add_action('wp_footer', 'yespo_add_tracking_codes');

//generate code for sending to yespo
function yespo_enqueue_tracking_scripts() {
    if (!wp_script_is(YESPO_TEXTDOMAIN . '-plugin-script', 'enqueued')) {
        wp_enqueue_script( YESPO_TEXTDOMAIN . '-plugin-script', plugins_url( 'assets/build/plugin-public.js', YESPO_PLUGIN_ABSOLUTE ), array(), '1.0', true );
    }

    (new Yespo\Integrations\Webtracking\Yespo_Web_Tracking_Aggregator())->localize_scripts();
}
add_action('yespo_after_scripts', 'yespo_enqueue_tracking_scripts');


// get cart data due ajax request
function yespo_get_cart_contents_function(){
    if ( ! isset( $_POST['yespo_get_cart_nonce_name'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['yespo_get_cart_nonce_name'])), 'yespo_get_cart_content_nonce' ) ) {
        return;
    }

    if(isset($_POST['action']) && sanitize_text_field(wp_unslash($_POST['action'])) === 'yespo_get_cart_contents' ) {
        $cart = (new Yespo\Integrations\Webtracking\Yespo_Cart_Event())->get_data();
        if ($cart) {
            wp_send_json_success(['cart' => $cart]);
        } else wp_send_json_error(0);
    }
}
add_action('wp_ajax_yespo_get_cart_contents', 'yespo_get_cart_contents_function');
add_action('wp_ajax_nopriv_yespo_get_cart_contents', 'yespo_get_cart_contents_function');

// UPDATE ORDER LAST MODIFIED TIME
add_action('woocommerce_update_order', function($order_id) {
    if ( 'yes' !== get_option( 'woocommerce_db_sync_enabled', 'no' ) ) {
        if ( ! is_numeric( $order_id ) || $order_id <= 0 ) {
            return false;
        }
        (new Yespo\Integrations\Esputnik\Yespo_Order())->update_last_modified_time($order_id);
    }
});


/**** BACKEND TRACKING CODE ****/
// Getting and saving webid from frontend
function yespo_save_webid_to_session() {
    if ( ! isset( $_POST['yespo_tenant_webid_nonce_name'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['yespo_tenant_webid_nonce_name'])), 'yespo_send_tenant_webid' ) ) {
        return;
    }

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    $response = [];

    foreach (['webId', 'orgId'] as $key) {

        $userIP = (new \Yespo\Integrations\Webtracking\Yespo_Logger())->get_user_IP();

        if (isset($_POST[$key])) {
            $value = trim(sanitize_text_field($_POST[$key]));

            if ($value !== '' && $value !== 'null' && $value !== 'undefined') {
                if (!isset($_SESSION[$key])) {
                    $_SESSION[$key] = $value;
                    $response[$key] = [
                        'message' => "$key saved in session",
                        'saved'   => true,
                    ];

                    //if ($key === 'webId') (new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('sc', $value . ' user-agent: ' . $_POST['userAgent'] . ' esState: ' . $_POST['esState'], 'successfully saved in session ' .  $userIP);
                } else {
                    //if ($key === 'webId') (new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('sc', $value . ' user-agent: ' . $_POST['userAgent'] . ' esState: ' . $_POST['esState'], 'sc is already in session ' .  $userIP);
                    $response[$key] = [
                        'message' => "$key already set in session, not overwritten",
                        'saved'   => true,
                    ];
                }
            } else {
                //if ($key === 'webId') (new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('sc', $value . ' user-agent: ' . $_POST['userAgent'] . ' esState: ' . $_POST['esState'], 'sc is empty, null or underfined ' .  $userIP);
                $response[$key] = [
                    'message' => "$key ignored due to invalid value",
                    'saved'   => false,
                ];
            }
        } else {
            //if ($key === 'webId') (new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('sc', $value . ' user-agent: ' . $_POST['userAgent'] . ' esState: ' . $_POST['esState'], 'sc is not transfered ' .  $userIP);
            $response[$key] = [
                'message' => "$key not transferred",
                'saved'   => false,
            ];
        }
    }

    wp_send_json((isset($_SESSION['webId']) || isset($_SESSION['orgId'])) ? wp_send_json_success($response) : wp_send_json_error($response));
}
add_action('wp_ajax_save_webid', 'yespo_save_webid_to_session');
add_action('wp_ajax_nopriv_save_webid', 'yespo_save_webid_to_session');

// user events
function yespo_handle_user_event_function($user_id_or_login, $user = null) {
    (new Yespo\Integrations\Webtracking\Yespo_User_Event())->handle_user_event($user_id_or_login, $user);
}
add_action('wp_login', 'yespo_handle_user_event_function', 10, 2); // user authorization

function yespo_update_user_event_function($user_id_or_login, $user = null) {

    if (get_transient('yespo_user_event_triggered')) return;

    set_transient('yespo_user_event_triggered', true, 3);

    if (is_numeric($user_id_or_login)) {
        $user_data = get_userdata($user_id_or_login);
        if ($user_data) {
            $user_login = $user_data->user_login;
        } else return;
    } else $user_login = $user_id_or_login;

    (new Yespo\Integrations\Webtracking\Yespo_User_Event())->handle_user_event($user_login, $user);
}
add_action('profile_update', 'yespo_update_user_event_function', 10, 2); // user update profile

//cart events
function yespo_add_to_cart_event_function($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
    if (get_transient('yespo_add_to_cart_event_triggered')) return;
    set_transient('yespo_add_to_cart_event_triggered', true, 3);
    (new Yespo\Integrations\Webtracking\Yespo_Cart_Event())->add_to_cart_event();
}
add_action('woocommerce_add_to_cart', 'yespo_add_to_cart_event_function', 10, 6);

function yespo_update_cart_event_function($cart_item_key, $quantity, $old_quantity, $cart ) {
    if (get_transient('yespo_update_cart_event_triggered')) return;
    set_transient('yespo_update_cart_event_triggered', true, 3);
    (new Yespo\Integrations\Webtracking\Yespo_Cart_Event())->after_cart_item_quantity_update();
}
add_action('woocommerce_after_cart_item_quantity_update', 'yespo_update_cart_event_function', 10, 4);

function yespo_remove_cart_items_event_function($cart_item_key, $quantity ) {
    (new Yespo\Integrations\Webtracking\Yespo_Cart_Event())->cart_item_removed();
}
add_action('woocommerce_cart_item_removed', 'yespo_remove_cart_items_event_function', 10, 2);

//purchased items
function yespo_send_purchased_data_function($order_id) {
    (new Yespo\Integrations\Webtracking\Yespo_User_Event())->after_order_complete($order_id);
    (new Yespo\Integrations\Webtracking\Yespo_Purchased_Event())->send_order_to_yespo($order_id);
}
add_action('woocommerce_thankyou', 'yespo_send_purchased_data_function', 10, 1);


/**
 * WEB PUSH
 **/
function yespo_add_webpush_codes() {
    $script = (new Yespo\Integrations\Webpush\Yespo_Web_Push())->get_script_from_options();
    if($script) {
        echo $script;
    }
}
add_action('wp_head', 'yespo_add_webpush_codes');



/**
 * Events after plugin update
 **/
function yespo_cdp_plugin_updated($upgrader_object, $options) {
    if ($options['action'] === 'update' && $options['type'] === 'plugin') {
        $target_plugin = 'yespo-cdp/yespo.php';

        if (!empty($options['plugins']) && in_array($target_plugin, $options['plugins'])) {
            (new \Yespo\Integrations\Esputnik\Yespo_Logging_Data())->remove_contact_log_column();
        }
    }
}
add_action('upgrader_process_complete', 'yespo_cdp_plugin_updated', 10, 2);

/*** Change configuration status ***/
function yespo_change_configuration_status() {

    if (
        ! isset($_POST['yespo_set_configuring_webtracking_script_name']) ||
        ! wp_verify_nonce(
            sanitize_text_field(
                wp_unslash($_POST['yespo_set_configuring_webtracking_script_name'])
            ),
            'yespo_set_configuring_webtracking_script_action'
        )
    ) {

        wp_send_json_error([
            'message' => 'Invalid nonce'
        ]);
    }

    $script_configuration = new Yespo\Integrations\Webtracking\Yespo_Web_Tracking_Script_Configuration();

    if($script_configuration->toggle_app_inbox()) {
        wp_send_json_success([
            'message' => true,
            'status' => $script_configuration->get_app_inbox_status()
        ]);
    } else {
        wp_send_json_error([
            'message' => false
        ]);
    }
}
add_action('wp_ajax_change_configuration_status', 'yespo_change_configuration_status');
add_action('wp_ajax_nopriv_change_configuration_status', 'yespo_change_configuration_status');