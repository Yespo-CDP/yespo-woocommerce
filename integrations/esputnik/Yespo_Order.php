<?php

namespace Yespo\Integrations\Esputnik;

use WP_Query;

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

        //(new Yespo_Export_Orders())->add_json_log_entry($orders);// add log entry to DB

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

    public function add_time_label($order_id){
        $order = wc_get_order($order_id);
        $status = $order->get_status();

        if (strpos($status, 'draft') === false) {
            $order_time = $order->get_meta('order_time');

            if (empty($order_time)) {
                $current_time = gmdate('Y-m-d H:i:s', current_time('timestamp', true));
                $order->update_meta_data('order_time', $current_time);
                $order->save();
            }
        }
    }

    public function add_label_deleted_customer($email){
        if(!empty($email)) {
            $orders = $this->get_orders_by_email($email);

            if ($orders) {
                foreach ($orders as $order) {
                    $order->update_meta_data('customer_removed', 'deleted');
                    $order->save();
                }
            }
        }
    }

    public function get_orders_by_email($email){
        $query = new WP_Query($this->args_get_orders_by_email($email));

        if ($query->have_posts()) {
            $orders = array();
            while ($query->have_posts()) {
                $query->the_post();
                $order_id = get_the_ID();
                $orders[] = wc_get_order($order_id);
            }
            wp_reset_postdata();
            return $orders;
        } else {
            return null;
        }
    }
    private function args_get_orders_by_email($email){
        $post_types = ['shop_order', 'shop_order_placehold'];

        $existing_post_types = array_filter($post_types, function($type) {
            return post_type_exists($type);
        });
        return [
            'post_type' => $existing_post_types,
            'post_status' => 'any',
            'meta_query' => array(
                array(
                    'key' => '_billing_email',
                    'value' => $email,
                    'compare' => '='
                )
            )
        ];
    }

    private function mark_exported_orders($order_id){
        if (empty(get_user_meta($order_id, self::ORDER_META_KEY, true))) update_user_meta($order_id, self::ORDER_META_KEY, 'true');
        else add_user_meta($order_id, self::ORDER_META_KEY, 'true', false);
    }
}