<?php

namespace Yespo\Integrations\Webpush;

use Exception;

class Yespo_Web_Push_Curl_Request
{
    private static $auth_data;

    public static function curl_request($url, $method, $data)
    {
        self::$auth_data = get_option('yespo_options');

        try {
            if ($method === 'GET') {
                $url = self::prepare_get_url($url, $data);
            }

            $args = self::prepare_request_args($method, $data);
            $response = wp_remote_request($url, $args);

            $code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);

            $decoded_body = json_decode($body, true);

            $message = '';
            if (isset($decoded_body['errors']['message'])) {
                $message = $decoded_body['errors']['message'];
            } else if ($code < 200 || $code >= 300){
                $message = $code . ' ERROR';
            }

            return wp_json_encode([
                'request_data' => $data,
                'message' => $message,
                'response_body' => $decoded_body ?: $body,
                'code' => $code,
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            //return self::handle_response($method, $response);
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    private static function prepare_get_url(string $url, $data): string
    {
        return !empty($data) ? $url . urlencode($data) : $url;
    }

    private static function prepare_request_args(string $method, $data): array
    {
        $args = [
            'method'  => $method,
            'timeout' => 60,
            'headers' => [
                'Accept'        => 'application/json; charset=UTF-8',
                'Authorization' => 'Basic ' . base64_encode(':' . self::$auth_data['yespo_api_key']),
                'Content-Type'  => 'application/json',
            ],
        ];

        if ($method === 'POST' && is_array($data)) {
            $args['body'] = wp_json_encode($data);
        }

        return $args;
    }

    private static function handle_response(string $method, $response)
    {
        if (is_wp_error($response)) {
            return 'Error: ' . $response->get_error_message();
        }

        return ($method === 'POST')
            ? wp_remote_retrieve_response_code($response)
            : wp_remote_retrieve_body($response);
    }
}