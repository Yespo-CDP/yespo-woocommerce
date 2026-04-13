<?php

namespace Yespo\Integrations\Esputnik;

use Exception;

class Yespo_Account
{
    const YESPO_REMOTE_ESPUTNIK_URL = "https://yespo.io/api/v1/account/info";

    public function send_keys($api_key) {
        try {
            $response = wp_remote_get(self::YESPO_REMOTE_ESPUTNIK_URL, [
                'timeout' => 30,
                'headers' => [
                    'Accept' => 'application/json; charset=UTF-8',
                    'Authorization' => 'Basic ' . base64_encode(':' . $api_key)
                ],
            ]);

            if (is_wp_error($response)) {
                return 'Error: ' . $response->get_error_message();
            }

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
                'message' => $message,
                'response_body' => $decoded_body ?: $body,
                'code' => $code,
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function get_profile_name(){
        return Yespo_Curl_Request::curl_request(self::YESPO_REMOTE_ESPUTNIK_URL, 'GET', get_option('yespo_options'));
    }

    public function add_entry_auth_log($api_key, $response){
        global $wpdb;

        $table_yespo_auth = esc_sql($wpdb->prefix . 'yespo_auth_log');
        $api_key = sanitize_text_field($api_key);
        $response = sanitize_text_field($response);
        $time = gmdate('Y-m-d H:i:s');

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        return $wpdb->query(
            $wpdb->prepare(
            "INSERT INTO %i (api_key, response, time) 
                VALUES (%s, %s, %s)",
                $table_yespo_auth,
                $api_key,
                $response,
                $time
            )
        );

    }

    /*** set orgId value ***/
    public function set_orgId() {

        $options = get_option('yespo_options', array());

        if (!isset($options['yespo_orgId']) && isset($options['yespo_api_key'])) {
            $response = $this->get_profile_name();

            if (!empty($response)) {
                $objResponse = json_decode($response);

                if (json_last_error() === JSON_ERROR_NONE && is_object($objResponse)) {

                    if (isset($objResponse->orgId)) {
                        $orgId = sanitize_text_field($objResponse->orgId);

                        if (!empty($orgId)) {
                            $options['yespo_orgId'] = $orgId;

                            if (update_option('yespo_options', $options)) {
                                return true;
                            }
                        }
                    }

                }
            }
        }
        return false;
    }



}