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
    public function create_order_on_yespo($order, $operation = 'update'){

        if (empty($this->authData)) {
            return __( 'Empty user authorization data', Y_TEXTDOMAIN );
        }

        //if($operation === 'delete') $response = Esputnik_Curl_Request::curl_request(self::REMOTE_ORDER_YESPO_URL, self::CUSTOM_ORDER_REQUEST, $this->authData, Esputnik_Order_Mapping::map_clean_user_data_order($order));
        //else $response = Esputnik_Curl_Request::curl_request(self::REMOTE_ORDER_YESPO_URL, self::CUSTOM_ORDER_REQUEST, $this->authData, Esputnik_Order_Mapping::order_woo_to_yes($order));
        $response = Esputnik_Curl_Request::curl_request(self::REMOTE_ORDER_YESPO_URL, self::CUSTOM_ORDER_REQUEST, $this->authData, Esputnik_Order_Mapping::order_woo_to_yes($order), 'orders');
        if ($response > 199 && $response < 300) {
            if ($order && is_a($order, 'WC_Order') && $order->get_id()){
                update_post_meta( $order->get_id(), self::ORDER_META_KEY, 'true' );
                (new Esputnik_Logging_Data())->create_entry_order($order->get_id(), $operation, $response); //add entry to logfile
                (new Esputnik_Logging_Data())->create_single_contact_log($order->get_billing_email()); //add entry contact log file
            }
        }
        return true;
    }

    public function create_bulk_orders_on_yespo($orders, $operation = 'update'){

        if (empty($this->authData)) {
            return __( 'Empty user authorization data', Y_TEXTDOMAIN );
        }

        (new Esputnik_Export_Orders())->add_json_log_entry($orders);// add log entry to DB

        if($orders['orders'] > 0) {
            $response = Esputnik_Curl_Request::curl_request(self::REMOTE_ORDER_YESPO_URL, self::CUSTOM_ORDER_REQUEST, $this->authData, $orders, 'orders');

            (new Esputnik_Export_Orders())->add_entry_queue_items();

            if ($response > 199 && $response < 300) {
                $orderCounter = 0;
                foreach ($orders['orders'] as $item) {
                    $order = wc_get_order($item['externalOrderId']);
                    if ($order && is_a($order, 'WC_Order') && $order->get_id()) {
                        update_post_meta($order->get_id(), self::ORDER_META_KEY, 'true');
                        (new Esputnik_Logging_Data())->create_entry_order($order->get_id(), $operation, $response); //add entry to logfile
                        $orderCounter++;
                    }
                }
                return $orderCounter;
            }
        }
        return false;
    }

    public function clean_users_data_from_orders_yespo($email){
        $orders = $this->find_orders_by_user_email($email);
        if(count($orders) > 0){
            foreach($orders as $order_id){
                $this->create_order_on_yespo(wc_get_order($order_id), 'delete');
                (new Esputnik_Logging_Data())->create_entry_order($order_id, 'delete'); //add entry to logfile
            }
        }
    }
    private function find_orders_by_user_email($email){
        $customer_orders = wc_get_orders( array(
            'limit'    => -1,
            'orderby'  => 'date',
            'order'    => 'DESC',
            'customer' => $email,
        ) );
        $orders = [];
        foreach( $customer_orders as $order ) {
            $orders[] = $order->get_id();
        }
        return array_unique($orders);
    }

    public function get_meta_key(){
        return self::ORDER_META_KEY;
    }
    private function mark_exported_orders($order_id){
        if (empty(get_user_meta($order_id, self::ORDER_META_KEY, true))) update_user_meta($order_id, self::ORDER_META_KEY, 'true');
        else add_user_meta($order_id, self::ORDER_META_KEY, 'true', false);
    }
}