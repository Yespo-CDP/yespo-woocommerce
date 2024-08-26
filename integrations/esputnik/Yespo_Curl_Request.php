<?php
/*** CURL REQUEST CLASS ***/

namespace Yespo\Integrations\Esputnik;

use Exception;

class Yespo_Curl_Request
{
    public static function curl_request(
        $url,
        $custom_request,
        $auth_data,
        $user_data = '',
        $type_response = ''
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
                    "authorization: Basic " . base64_encode(':' . $auth_data['yespo_api_key']),
                    "content-type: application/json"
                ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);

            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            
            if(strpos($response, 'Connection refused') !== false || $response === false) $http_code = 0;

            if(
                $custom_request === 'DELETE' ||
                $type_response === 'orders' ||
                $http_code === 400 ||
                $http_code === 429 ||
                $http_code === 500 ||
                $http_code === 0
            ) $response = $http_code;

            curl_close($curl);

            if(!empty($user_data)) (new Yespo_Export_Orders())->add_json_log_entry($user_data);// add log entry to DB

            if ($err) return "cURL Error #:" . $err;
            else return $response;
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }
}