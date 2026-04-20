<?php

namespace Yespo\Integrations\Webtracking;

use Exception;

class Yespo_Logging_Remote
{

    //added in functions yespo_save_settings_via_form_function()
    public static function add_api_key_success()
    {
        $message = "ADD_API_KEY_SUCCESS";
        $log_level =  "INFO";
        $data = self::get_data();

        //(new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('200', self::generate_json($message, $log_level, $data), 'add_api_key_success');

        return self::send_curl_request(self::generate_json($message, $log_level, $data));
    }

    //added in functions yespo_save_settings_via_form_function()
    public static function add_api_key_error($errorMessage)
    {
        $message = "ADD_API_KEY_FAILED";
        $log_level = "ERROR";
        $data = self::get_data();

        //(new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('400', self::generate_json($message, $log_level, $data, $errorMessage), 'add_api_key_error');

        return self::send_curl_request(self::generate_json($message, $log_level, $data, $errorMessage));
    }

    //added in add_tenant_id_to_options() of Yespo_Web_Tracking_Script
    public static function add_site_domain_success($requestBody, $responseBody, $statusCode)
    {
        $message = "ADD_DOMAIN_SUCCESS";
        $log_level = "INFO";
        $data = self::get_data($requestBody, $responseBody, $statusCode);

        //(new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('200', self::generate_json($message, $log_level, $data), 'add_site_domain_success');

        return self::send_curl_request(self::generate_json($message, $log_level, $data));
    }

    //added in add_tenant_id_to_options() of Yespo_Web_Tracking_Script
    public static function add_site_domain_error($errorMessage, $requestBody, $responseBody, $statusCode)
    {
        $message = "ADD_DOMAIN_FAILED";
        $log_level = "ERROR";
        $data = self::get_data($requestBody, $responseBody, $statusCode);

        //(new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('400', self::generate_json($message, $log_level, $data, $errorMessage), 'add_site_domain_error');

        return self::send_curl_request(self::generate_json($message, $log_level, $data, $errorMessage));
    }

    //added in add_script_to_options() of Yespo_Web_Tracking_Script class
    public static function add_site_script_html_success()
    {
        $message = "INSERT_SITE_SCRIPT_SUCCESS";
        $log_level = "INFO";
        $data = self::get_data();

        //(new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('200', self::generate_json($message, $log_level, $data), 'add_site_script_html_success');

        return self::send_curl_request(self::generate_json($message, $log_level, $data));
    }

    //added in add_script_to_options() of Yespo_Web_Tracking_Script class
    public static function add_site_script_html_error($errorMessage)
    {
        $message = "INSERT_SITE_SCRIPT_FAILED";
        $log_level = "ERROR";
        $data = self::get_data();

        //(new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('400', self::generate_json($message, $log_level, $data, $errorMessage), 'add_site_script_html_error');

        return self::send_curl_request(self::generate_json($message, $log_level, $data, $errorMessage));
    }

    //added in add_script_to_options() of Yespo_Web_Tracking_Script class
    public static function get_site_script_success($responseBody, $statusCode)
    {
        $message = "GET_SCRIPT_SUCCESS";
        $log_level = "INFO";
        $requestBody = null;
        $data = self::get_data($requestBody, $responseBody, $statusCode);

        //(new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('200', self::generate_json($message, $log_level, $data), 'get_site_script_success');

        return self::send_curl_request(self::generate_json($message, $log_level, $data));
    }

    //added in add_script_to_options() of Yespo_Web_Tracking_Script class
    public static function get_site_script_error($errorMessage, $responseBody, $statusCode)
    {
        $message = "GET_SCRIPT_FAILED";
        $log_level = "ERROR";
        $requestBody = null;
        $data = self::get_data($requestBody, $responseBody, $statusCode);

        //(new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('400', self::generate_json($message, $log_level, $data,$errorMessage), 'get_site_script_error');

        return self::send_curl_request(self::generate_json($message, $log_level, $data,$errorMessage));
    }

    //added in start() of Yespo_Web_Push class
    public static function add_web_push_domain_success($requestBody, $responseBody, $statusCode)
    {
        $message = "ADD_WEB_PUSH_DOMAIN_SUCCESS";
        $log_level = "INFO";
        $data = self::get_data($requestBody, $responseBody, $statusCode);

        //(new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('200', self::generate_json($message, $log_level, $data), 'add_web_push_domain_success');

        return self::send_curl_request(self::generate_json($message, $log_level, $data));
    }

    //added in start() of Yespo_Web_Push class
    public static function add_web_push_domain_error($errorMessage, $requestBody, $responseBody, $statusCode)
    {
        $message = "ADD_WEB_PUSH_DOMAIN_FAILED";
        $log_level = "ERROR";
        $data = self::get_data($requestBody, $responseBody, $statusCode);

        //(new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('400', self::generate_json($message, $log_level, $data, $errorMessage), 'add_web_push_domain_error');

        return self::send_curl_request(self::generate_json($message, $log_level, $data, $errorMessage));
    }

    //added in start() of Yespo_Web_Push class
    public static function get_web_push_script_success($responseBody, $statusCode)
    {
        $message = "GET_WEB_PUSH_DOMAIN_SUCCESS";
        $log_level = "INFO";
        $data = self::get_data(null, $responseBody, $statusCode);

        //(new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('200', self::generate_json($message, $log_level, $data), 'get_web_push_script_success');

        return self::send_curl_request(self::generate_json($message, $log_level, $data));
    }

    //added in start() of Yespo_Web_Push class
    public static function get_web_push_script_error($errorMessage, $responseBody, $statusCode)
    {
        $message = "GET_WEB_PUSH_DOMAIN_FAILED";
        $log_level = "ERROR";
        $data = self::get_data(null, $responseBody, $statusCode);

        //(new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('400', self::generate_json($message, $log_level, $data, $errorMessage), 'get_web_push_script_error');

        return self::send_curl_request(self::generate_json($message, $log_level, $data, $errorMessage));
    }

    //added in add_script_to_options($script) of Yespo_Web_Push class
    public static function add_web_push_script_html_success()
    {
        $message = "INSERT_WEB_PUSH_SCRIPT_SUCCESS";
        $log_level = "INFO";
        $data = self::get_data();

        //(new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('200', self::generate_json($message, $log_level, $data), 'add_web_push_script_html_success');

        return self::send_curl_request(self::generate_json($message, $log_level, $data));
    }

    //added in add_script_to_options($script) of Yespo_Web_Push class
    public static function add_web_push_script_html_error($errorMessage)
    {
        $message = "INSERT_WEB_PUSH_SCRIPT_FAILED";
        $log_level = "ERROR";
        $data = self::get_data();

        //(new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('400', self::generate_json($message, $log_level, $data, $errorMessage), 'add_web_push_script_html_error');

        return self::send_curl_request(self::generate_json($message, $log_level, $data, $errorMessage));
    }

    //added in add_script_to_options($script) of Yespo_Web_Push class
    public static function add_swjs_site_root_success($responseBody, $statusCode)
    {
        $message = "ADD_SERVICE_WORKER_SUCCESS";
        $log_level = "INFO";
        $data = self::get_data(null, $responseBody, $statusCode);

        //(new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('200', self::generate_json($message, $log_level, $data), 'add_swjs_site_root_success');

        return self::send_curl_request(self::generate_json($message, $log_level, $data));
    }

    //added in add_script_to_options($script) of Yespo_Web_Push class
    public static function add_swjs_site_root_error($errorMessage, $responseBody, $statusCode)
    {
        $message = "INSERT_WEB_PUSH_SCRIPT_FAILED";
        $log_level = "ERROR";
        $data = self::get_data(null, $responseBody, $statusCode);

        //(new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('400', self::generate_json($message, $log_level, $data, $errorMessage), 'add_swjs_site_root_error');

        return self::send_curl_request(self::generate_json($message, $log_level, $data, $errorMessage));
    }

    public static function data_sync_error($errorMessage)
    {
        $message = "DATA_SYNC_FAILED";
        $log_level = "ERROR";
        $data = self::get_data();
        $errorMessage = 'Error ' . $errorMessage;

        //(new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('400', self::generate_json($message, $log_level, $data, $errorMessage), 'data_sync_error');

        return self::send_curl_request(self::generate_json($message, $log_level, $data, $errorMessage));
    }

    public static function add_contacts_bulk_success($response_body, $code, $offset)
    {
        $message = "SEND_CONTACTS_BULK_SUCCESS";
        $log_level = "INFO";
        $data = self::get_data(null, $response_body, $code, $offset);

        (new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('200', self::generate_json($message, $log_level, $data), 'add_contacts_bulk_success');

        return self::send_curl_request(self::generate_json($message, $log_level, $data));
    }

    public static function add_contacts_bulk_error($errorMessage, $response_body, $code, $offset)
    {
        $message = "SEND_CONTACTS_BULK_FAILED";
        $log_level = "ERROR";
        $data = self::get_data(null, $response_body, $code, $offset);
        $errorMessage = 'Error ' . $errorMessage;

        (new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('400', self::generate_json($message, $log_level, $data, $errorMessage), 'add_contacts_bulk_error');

        return self::send_curl_request(self::generate_json($message, $log_level, $data, $errorMessage));
    }

    public static function add_orders_bulk_success($response_body, $code, $offset)
    {
        $message = "SEND_ORDERS_BULK_SUCCESS";
        $log_level = "INFO";
        $data = self::get_data(null, $response_body, $code, $offset);

        (new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('200', self::generate_json($message, $log_level, $data), 'add_orders_bulk_success');

        return self::send_curl_request(self::generate_json($message, $log_level, $data));
    }

    public static function add_orders_bulk_error($errorMessage, $response_body, $code, $offset)
    {
        $message = "SEND_ORDERS_BULK_FAILED";
        $log_level = "ERROR";
        $data = self::get_data(null, $response_body, $code, $offset);
        $errorMessage = 'Error ' . $errorMessage;

        (new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('400', self::generate_json($message, $log_level, $data, $errorMessage), 'add_orders_bulk_error');

        return self::send_curl_request(self::generate_json($message, $log_level, $data, $errorMessage));
    }

    public static function web_tracking_sync_error($errorMessage)
    {
        $message = "WEB_TRACKING_FAILED";
        $log_level = "ERROR";
        $data = self::get_data();

        //(new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('400', self::generate_json($message, $log_level, $data, $errorMessage), 'web_tracking_sync_error');

        return self::send_curl_request(self::generate_json($message, $log_level, $data, $errorMessage));
    }

    // add in add_to_cart_event(), after_cart_item_quantity_update(), cart_item_removed() of Yespo_Cart_Event class
    public static function send_status_cart_event_success($requestBody, $responseBody, $statusCode)
    {
        $message = "WEB_TRACKING_STATUSCART_SUCCESS";
        $log_level = "INFO";
        $data = self::get_data($requestBody, $responseBody, $statusCode);

        //(new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('200', self::generate_json($message, $log_level, $data), 'send_status_cart_event_success');

        return self::send_curl_request(self::generate_json($message, $log_level, $data));
    }

    // add in add_to_cart_event(), after_cart_item_quantity_update(), cart_item_removed() of Yespo_Cart_Event class
    public static function send_status_cart_event_error($errorMessage, $requestBody, $responseBody, $statusCode)
    {
        $message = "WEB_TRACKING_STATUSCART_ERROR";
        $log_level = "ERROR";
        $data = self::get_data($requestBody, $responseBody, $statusCode);

        //(new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('400', self::generate_json($message, $log_level, $data, $errorMessage), 'send_status_cart_event_error');

        return self::send_curl_request(self::generate_json($message, $log_level, $data, $errorMessage));
    }

    //added in send_order_to_yespo() of Yespo_Purchased_Event class
    public static function send_purchased_items_event_success($requestBody, $responseBody, $statusCode)
    {
        $message = "WEB_TRACKING_PURCHASED_ITEMS_SUCCESS";
        $log_level = "INFO";
        $data = self::get_data($requestBody, $responseBody, $statusCode);

        //(new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('200', self::generate_json($message, $log_level, $data), 'send_purchased_items_event_success');

        return self::send_curl_request(self::generate_json($message, $log_level, $data));
    }

    //added in send_order_to_yespo() of Yespo_Purchased_Event class
    public static function send_purchased_items_event_error($errorMessage, $requestBody, $responseBody, $statusCode)
    {
        $message = "WEB_TRACKING_PURCHASED_ITEMS_ERROR";
        $log_level = "ERROR";
        $data = self::get_data($requestBody, $responseBody, $statusCode);

        //(new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('400', self::generate_json($message, $log_level, $data, $errorMessage), 'send_purchased_items_event_error');

        return self::send_curl_request(self::generate_json($message, $log_level, $data, $errorMessage));
    }

    //added in handle_user_event() of Yespo_User_Event class and send_order_to_yespo() of Yespo_Purchased_Event
    public static function send_customer_data_event_success($requestBody, $responseBody, $statusCode)
    {
        $message = "WEB_TRACKING_CUSTOMER_DATA_SUCCESS";
        $log_level = "INFO";
        $data = self::get_data($requestBody, $responseBody, $statusCode);

        //(new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('200', self::generate_json($message, $log_level, $data), 'send_customer_data_event_success');

        return self::send_curl_request(self::generate_json($message, $log_level, $data));
    }

    //added in handle_user_event() of Yespo_User_Event class and send_order_to_yespo() of Yespo_Purchased_Event
    public static function send_customer_data_event_error($errorMessage, $requestBody, $responseBody, $statusCode)
    {
        $message = "WEB_TRACKING_CUSTOMER_DATA_ERROR";
        $log_level = "ERROR";
        $data = self::get_data($requestBody, $responseBody, $statusCode);

        //(new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('400', self::generate_json($message, $log_level, $data, $errorMessage), 'send_customer_data_event_error');

        return self::send_curl_request(self::generate_json($message, $log_level, $data, $errorMessage));
    }

    /*
     * added in curl_request of Yespo_Curl_Request class */
    public static function export_data_dealing($response_body, $code, $type_response, $offset){
        if($response_body && $code > 199 && $code < 300 && $type_response === 'users' && $offset > 0) self::add_contacts_bulk_success($response_body, $code, $offset);
        else if($response_body && $code < 199 || $code > 299 && $type_response === 'users' && $offset > 0) self::add_contacts_bulk_error($code, $response_body, $code, $offset);
        else if($response_body && $code > 199 && $code < 300 && $type_response === 'orders' && $offset > 0) self::add_orders_bulk_success($response_body, $code, $offset);
        else if($response_body && $code < 199 || $code > 299 && $type_response === 'orders' && $offset > 0) self::add_orders_bulk_error($code, $response_body, $code, $offset);
        else if($response_body && $code < 199 || $code > 299) self::data_sync_error($code);
    }

    /***
     * Private methods
     ***/
    private static function get_orgid() {
        if ( get_option( 'yespo_options' ) !== false ) {
            $options = get_option('yespo_options', array());
            if (isset($options['yespo_orgId'])) return sanitize_text_field($options['yespo_orgId']);
        }
        return null;
    }

    private static function get_data(
        $requestBody = null,
        $responseBody = null,
        $statusCode = null,
        $offset = null
    )
    {
        $data['domain'] = home_url();
        if (!empty($requestBody)) $data['requestBody'] = $requestBody;
        if (!empty($offset)) $data['offset'] = $offset;
        if (!empty($responseBody)) $data['responseBody'] = $responseBody;
        if (!empty($statusCode)) $data['statusCode'] = $statusCode;

        return wp_json_encode($data, JSON_UNESCAPED_SLASHES);
        //return $data;
    }

    private static function generate_json($message, $log_level, $data, $errorMessage = '')
    {
        $orgId = self::get_orgid();
        return [
            'orgId'   => intval($orgId),
            'typeCMS'  => 'Wordpress',
            'errorMessage' => $errorMessage,
            'data'    => $data,
            'message' => $message,
            'log_level'    => $log_level
        ];
    }

    private static function send_curl_request($json)
    {
        $url = "https://events.yespo.io/logs/v1/plugin";
        $auth_data = get_option('yespo_options');

        try {
            $args = [
                'method'  => "POST",
                'timeout' => 60,
                'headers' => [
                    'Accept'        => 'application/json; charset=UTF-8',
                    'Authorization' => 'Basic ' . base64_encode(':' . $auth_data['yespo_api_key']),
                    'Content-Type'  => 'application/json',
                ],
                'body'    => !empty($json) ? wp_json_encode($json) : '',
            ];

            $response = wp_remote_request($url, $args);
/*
            if (is_wp_error($response)) {
                return 'Error: ' . $response->get_error_message();
            }
*/
            $code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);

            $decoded_body = json_decode($body, true);

            $result = wp_json_encode([
                'response_body' => $decoded_body ?: $body,
                'code' => $code,
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);


            //(new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file($code, $result, 'sent data to remote logs');

            return $result;

        } catch (Exception $e) {
            (new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file($code, wp_json_encode($e), 'sent data to remote logs error');
            return 'Error: ' . $e->getMessage();
        }

    }

}