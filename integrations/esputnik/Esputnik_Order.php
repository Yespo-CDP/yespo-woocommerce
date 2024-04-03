<?php

namespace Yespo\Integrations\Esputnik;

class Esputnik_Order
{
    const REMOTE_ORDER_YESPO_URL = 'https://esputnik.com/api/v1/orders';
    const CUSTOM_ORDER_REQUEST = 'POST';
    private $authData;
    const ORDER_META_KEY = 'sent_order_to_yespo';

    public function __construct(){
        $this->authData = get_option('yespo_options');
    }
    public function create_order_on_yespo($order){

        if (empty($this->authData)) {
            return __( 'Empty user authorization data', Y_TEXTDOMAIN );
        }

        $response = Esputnik_Curl_Request::curl_request(self::REMOTE_ORDER_YESPO_URL, self::CUSTOM_ORDER_REQUEST, $this->authData, Esputnik_Order_Mapping::order_woo_to_yes($order));
        if(strlen($response) < 1){
            if ($order && is_a($order, 'WC_Order') && $order->get_id()) update_post_meta( $order->get_id(), self::ORDER_META_KEY, 'true' );
            return true;
        }
        return $response;
    }

    public function get_meta_key(){
        return self::ORDER_META_KEY;
    }
    private function mark_exported_orders($order_id){
        if (empty(get_user_meta($order_id, self::ORDER_META_KEY, true))) update_user_meta($order_id, self::ORDER_META_KEY, 'true');
        else add_user_meta($order_id, self::ORDER_META_KEY, 'true', false);
    }
}