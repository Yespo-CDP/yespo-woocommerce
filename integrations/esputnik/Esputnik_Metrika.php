<?php

namespace Yespo\Integrations\Esputnik;

class Esputnik_Metrika
{
    const ACTIVITY_ESPUTNIK_URL = "https://esputnik.com/user-activity/public/v1/activity";
    const ACTIVITY_REQUEST = "POST";

    public static function count_installations(){}
    public static function count_activations(){
        $args["userId"] = 2343423;
        $data = self::get_data($args);
        //Esputnik_Curl_Request::curl_request(self::ACTIVITY_ESPUTNIK_URL, self::ACTIVITY_REQUEST, $data);
/*
        $file_path = $_SERVER['DOCUMENT_ROOT'] . '/filedebug.txt';
        $data_to_append = ' plugin activated ' . json_encode($data);
        $file_handle = fopen($file_path, 'a');
        if ($file_handle) {
            fwrite($file_handle, $data_to_append);
            fclose($file_handle);
        }
*/
    }
    public static function count_start_connections(){
        $args["userId"] = 2343423;
        $data = self::get_data($args);
        //Esputnik_Curl_Request::curl_request(self::ACTIVITY_ESPUTNIK_URL, self::ACTIVITY_REQUEST, $data);

    }
    public static function count_finish_connections(){
        $args["userId"] = 2343423;
        $data = self::get_data($args);
        //Esputnik_Curl_Request::curl_request(self::ACTIVITY_ESPUTNIK_URL, self::ACTIVITY_REQUEST, $data);

    }
    public static function count_page_views(){}
    public static function count_start_exported(){
        $args["userId"] = 2343423;
        $data = self::get_data($args);
        //Esputnik_Curl_Request::curl_request(self::ACTIVITY_ESPUTNIK_URL, self::ACTIVITY_REQUEST, $data);

                $file_path = $_SERVER['DOCUMENT_ROOT'] . '/filedebug.txt';
                $data_to_append = ' export started ' . json_encode($data);
                $file_handle = fopen($file_path, 'a');
                if ($file_handle) {
                    fwrite($file_handle, $data_to_append);
                    fclose($file_handle);
                }

    }
    public static function count_finish_exported(){
        $args["userId"] = 2343423;
        $data = self::get_data($args);
        //Esputnik_Curl_Request::curl_request(self::ACTIVITY_ESPUTNIK_URL, self::ACTIVITY_REQUEST, $data);

                $file_path = $_SERVER['DOCUMENT_ROOT'] . '/filedebug.txt';
                $data_to_append = ' export finished ' . json_encode($data);
                $file_handle = fopen($file_path, 'a');
                if ($file_handle) {
                    fwrite($file_handle, $data_to_append);
                    fclose($file_handle);
                }

    }
    public static function count_deactivations(){
        $args["userId"] = 2343423;
        $data = self::get_data($args);
        //Esputnik_Curl_Request::curl_request(self::ACTIVITY_ESPUTNIK_URL, self::ACTIVITY_REQUEST, $data);

    }
    public static function count_removes(){}

    private static function get_data($args = null){
        $data = [];

        if(Y_NAME !== null) $data["pluginName"] = Y_NAME;
        if(Y_VERSION !== null) $data["pluginVersion"] = Y_VERSION;

        if(isset($args) && is_array($args)){
            if(array_key_exists("customerId", $args) && !empty($args["customerId"])) $data["customerId"] = $args["customerId"];
            if(array_key_exists("name", $args) && !empty($args["name"])) $data["name"] = $args["name"];
            if(array_key_exists("url", $args) && !empty($args["url"])) $data["url"] = $args["url"];
            if(array_key_exists("userId", $args) && !empty($args["userId"])) $data["userId"] = $args["userId"];
        }

        $data["createdDate"] = gmdate('Y-m-d\TH:i:s\Z', time());

        return $data;
    }
}