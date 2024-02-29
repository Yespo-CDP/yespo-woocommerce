<?php
/*** CURL REQUEST CLASS ***/

namespace Yespo\Integrations\Esputnik;

use Exception;

class CurlRequest
{
    public static function curl_request(
        $url,
        $custom_request,
        $auth_data,
        $user_data
    ){
        try {
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $custom_request,
                CURLOPT_POSTFIELDS => !empty($user_data) ? json_encode($user_data) : '',
                CURLOPT_HTTPHEADER => [
                    "accept: application/json; charset=UTF-8",
                    "authorization: Basic " . base64_encode($auth_data['yespo_username'] . ':' . $auth_data['yespo_api_key']),
                    "content-type: application/json"
                ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) return "cURL Error #:" . $err;
            else return $response;
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }
}