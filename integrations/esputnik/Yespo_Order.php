<?php

namespace Yespo\Integrations\Esputnik;

class Yespo_Order
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
            return __( 'Empty user authorization data', YESPO_TEXTDOMAIN );
        }

        $data = Yespo_Order_Mapping::order_woo_to_yes($order);

        if($data){
            $response = Yespo_Curl_Request::curl_request(self::REMOTE_ORDER_YESPO_URL, self::CUSTOM_ORDER_REQUEST, $this->authData, $data, 'orders');
            if ($response > 199 && $response < 300) {
                if ($order && is_a($order, 'WC_Order') && $order->get_id()) {
                    update_post_meta($order->get_id(), self::ORDER_META_KEY, 'true');
                    (new Yespo_Logging_Data())->create_entry_order($order->get_id(), $operation, $response); //add entry to logfile
                    (new Yespo_Logging_Data())->create_single_contact_log($order->get_billing_email()); //add entry contact log file
                }
            }
        } else{
            update_post_meta($order->get_id(), self::ORDER_META_KEY, 'true');
        }
        return true;

    }

    public function create_bulk_orders_on_yespo($orders, $operation = 'update'){

        if (empty($this->authData)) {
            return __( 'Empty user authorization data', YESPO_TEXTDOMAIN );
        }

        (new Yespo_Export_Orders())->add_json_log_entry($orders);// add log entry to DB

        if($orders['orders'] > 0) {
            $response = Yespo_Curl_Request::curl_request(self::REMOTE_ORDER_YESPO_URL, self::CUSTOM_ORDER_REQUEST, $this->authData, $orders, 'orders');

            (new Yespo_Export_Orders())->add_entry_queue_items();

            if ($response > 199 && $response < 300) {
                $orderCounter = 0;
                foreach ($orders['orders'] as $item) {
                    $order = wc_get_order($item['externalOrderId']);
                    if ($order && is_a($order, 'WC_Order') && $order->get_id()) {
                        update_post_meta($order->get_id(), self::ORDER_META_KEY, 'true');
                        (new Yespo_Logging_Data())->create_entry_order($order->get_id(), $operation, $response); //add entry to logfile
                        $orderCounter++;
                    }
                }
                return $orderCounter;
            } else if($response === 401){
                (new Yespo_Export_Orders())->error_export_orders('401');
            } else if($response === 0){
                (new Yespo_Export_Orders())->error_export_orders('555');
            }
        }
        return false;
    }

    private function find_orders_by_user_email($email){
        $customer_orders = wc_get_orders( array(
            'limit'    => -1,
            'orderby'  => 'date',
            'order'    => 'DESC',
            'customer' => sanitize_email($email),
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