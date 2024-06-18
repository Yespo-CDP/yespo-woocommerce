<?php

namespace Yespo\Integrations\Esputnik;

use Exception;

class Esputnik_Account
{
    const REMOTE_ESPUTNIK_URL = "https://esputnik.com/api/v1/account/info";

    public function send_keys($api_key) {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => self::REMOTE_ESPUTNIK_URL,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => [
                    "accept: application/json; charset=UTF-8",
                    "authorization: Basic " . base64_encode(':' . $api_key)
                ],
            ]);

            $response = curl_exec($curl);

            if ($response === false) {
                throw new Exception(curl_error($curl), curl_errno($curl));
            }

            $result = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            return $result;

        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public function get_profile_name(){
        return Esputnik_Curl_Request::curl_request(self::REMOTE_ESPUTNIK_URL, 'GET', get_option('yespo_options'));
    }

    public function add_entry_auth_log($api_key, $response){
        global $wpdb;
        $table_yespo_auth = $wpdb->prefix . 'yespo_auth_log';

        $data = [
            'api_key' => $api_key,
            'response' => $response,
            'time' => date('Y-m-d H:i:s', time())
        ];

        $wpdb->insert($table_yespo_auth, $data);

    }
}