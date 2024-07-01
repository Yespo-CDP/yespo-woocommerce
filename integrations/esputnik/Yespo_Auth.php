<?php

namespace Yespo\Integrations\Esputnik;

use Exception;

class Yespo_Auth
{
    private const URL_ADDRESS_TOKEN = 'https://uaa.esputnik.com/uaa/oauth/token';
    private const TYPE_REQUEST = 'POST';
    private const ACCESS_TOKEN_NAME = 'yespo_access_token';
    private const REFRESH_TOKEN_NAME = 'yespo_refresh_token';
    private const ACCESS_TOKEN_TIME = 172800;
    private const REFRESH_TOKEN_TIME = 2592000;


    public function __construct(){

    }
    private function request_access_token(){
        $params = [
            'client_id'     => YESPO_CLIENT_ID,
            //'client_secret' => $this->getOption( self::APP_SECRET_FIELD ),
            //'redirect_uri'  => $this->callbackUrl,
            //'code'          => get_query_var( self::AUTH_RESPONSE_TYPE ),
        ];
    }


    private function get_refresh_token(){
        $refresh_token = get_option(self::REFRESH_TOKEN_NAME);
        $current_time = time();
        if($refresh_token !== null){
            $token_data = json_decode($refresh_token, true);
            if($current_time > $token_data->valid){
                $new_token = 'token';//get new access token
                $this->refresh_token_update($new_token, $current_time + self::REFRESH_TOKEN_TIME);
            } else return $token_data->token;
        } else {
            $new_token = 'token';//get new access token
            $this->create_token(self::REFRESH_TOKEN_NAME, $new_token, $current_time + self::REFRESH_TOKEN_TIME);
        }
    }

    private function get_access_token(){
        $access_token = get_option(self::ACCESS_TOKEN_NAME);
        $current_time = time();
        if($access_token !== null){
            $token_data = json_decode($access_token, true);
            if($current_time > $token_data->valid){
                $new_token = 'token';//get new access token
                $this->access_token_update($new_token, $current_time + self::ACCESS_TOKEN_TIME);
            } else return $token_data->token;
        } else {
            $new_token = 'token';//get new access token
            $this->create_token(self::ACCESS_TOKEN_NAME, $new_token, $current_time + self::ACCESS_TOKEN_TIME);
        }
    }

    /** update tokens **/
    private function access_token_update($tokenValue, $valid){
        if(get_option(self::ACCESS_TOKEN_NAME)) $this->create_token(self::ACCESS_TOKEN_NAME, $tokenValue, $valid);
        else $this->update_token(self::ACCESS_TOKEN_NAME, $tokenValue, $valid);
    }

    private function refresh_token_update($tokenValue, $valid){
        if(get_option(self::REFRESH_TOKEN_NAME)) $this->create_token(self::REFRESH_TOKEN_NAME, $tokenValue, $valid);
        else $this->update_token(self::REFRESH_TOKEN_NAME, $tokenValue, $valid);
    }

    private function update_token($optionName, $tokenValue, $valid){
        $option = get_option($optionName);
        $token_data = json_decode($option, true);
        $token_data['token'] = $tokenValue;
        $token_data['valid'] = $valid;

        update_option($optionName, json_encode($token_data));
    }

    /** create tokens **/
    private function create_token($optionName, $tokenValue, $valid){
        $access_token_json = get_option($optionName);
        $access_token_data = array(
            'token' => $tokenValue,
            'valid' => $valid
        );
        add_option($optionName, json_encode($access_token_data));
    }

    private function authorization_curl_request($url, $params){
        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

            $response = curl_exec($ch);

            if($response === false) {
                echo 'Помилка CURL: ' . curl_error($ch);
            } else {
                $responseData = json_decode($response, true);
                if(isset($responseData['access_token'])) {
                    $accessToken = $responseData['access_token'];
                    $refreshToken = $responseData['refresh_token'];
                } else {
                    echo "Помилка отримання токену: " . $responseData['error_description'];
                }
            }

            curl_close($ch);
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }
}