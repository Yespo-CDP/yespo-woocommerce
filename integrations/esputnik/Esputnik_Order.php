<?php

namespace Yespo\Integrations\Esputnik;

class Esputnik_Order
{
    const REMOTE_ORDER_YESPO_URL = 'https://esputnik.com/api/v1/orders';
    const CUSTOM_ORDER_REQUEST = 'POST';
    private $authData;

    public function __construct(){
        $this->authData = get_option('yespo_options');
    }
    public function create_order_on_yespo($order){

        if (empty($this->authData)) {
            return __( 'Empty user authorization data', Y_TEXTDOMAIN );
        }

        $response = Esputnik_Curl_Request::curl_request(self::REMOTE_ORDER_YESPO_URL, self::CUSTOM_ORDER_REQUEST, $this->authData, Esputnik_Order_Mapping::order_woo_to_yes($order));
        if(strlen($response) < 1){
            return true;
        }
        return $response;
    }
}