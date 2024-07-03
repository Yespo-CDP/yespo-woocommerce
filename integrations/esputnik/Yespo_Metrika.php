<?php

namespace Yespo\Integrations\Esputnik;

class Yespo_Metrika
{
    const ACTIVITY_ESPUTNIK_URL = "https://esputnik.com/user-activity/public/v1/activity";
    const ACTIVITY_REQUEST = "POST";

    public static function count_installations(){}
    public static function count_activations(){
        $args["userId"] = 2343423;
        $data = self::get_data($args);
    }
    public static function count_start_connections(){
        $args["userId"] = 2343423;
        $data = self::get_data($args);
    }
    public static function count_finish_connections(){
        $args["userId"] = 2343423;
        $data = self::get_data($args);
    }
    public static function count_page_views(){}
    public static function count_start_exported(){
        $args["userId"] = 2343423;
        $data = self::get_data($args);
    }
    public static function count_finish_exported(){
        $args["userId"] = 2343423;
        $data = self::get_data($args);
    }
    public static function count_deactivations(){
        $args["userId"] = 2343423;
        $data = self::get_data($args);
    }
    public static function count_removes(){}

    private static function get_data($args = null){
        $data = [];

        if(YESPO_NAME !== null) $data["pluginName"] = YESPO_NAME;
        if(YESPO_VERSION !== null) $data["pluginVersion"] = YESPO_VERSION;

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