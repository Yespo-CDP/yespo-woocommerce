<?php

namespace Yespo\Integrations\Webtracking;

use Exception;

class Yespo_Web_Tracking_Curl_Request
{
    private static string $url;
    private static string $custom_request;
    private static array $auth_data;

    private static function init()
    {
        if (!isset(self::$url)) {
            self::$url = 'https://tracker.yespo.io/api/v2';
            self::$custom_request = "POST";
            self::$auth_data = get_option('yespo_options');
        }
    }

    public static function curl_request($tracking_data = '')
    {
        self::init();

        try {
            $args = [
                'method'  => self::$custom_request,
                'timeout' => 60,
                'headers' => [
                    'Accept'        => 'application/json; charset=UTF-8',
                    'Authorization' => 'Basic ' . base64_encode(':' . self::$auth_data['yespo_api_key']),
                    'Content-Type'  => 'application/json',
                ],
                'body'    => !empty($tracking_data) ? wp_json_encode($tracking_data) : '',
            ];

            $response = wp_remote_request(self::$url, $args);

            if (is_wp_error($response)) {
                return 'Error: ' . $response->get_error_message();
            }

            return wp_remote_retrieve_response_code($response);
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}