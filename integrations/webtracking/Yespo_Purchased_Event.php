<?php

namespace Yespo\Integrations\Webtracking;

class Yespo_Purchased_Event extends Yespo_Web_Tracking_Abstract
{
    public function get_data(){
        // TODO: Implement get_data() method.
        if ( strpos(sanitize_text_field($_SERVER['REQUEST_URI']), 'order-received') !== false ) {

            $order_id = $this->get_created_order();

            if (!empty($order_id) && !empty($order_id[0]->id)) {
                $order = wc_get_order($order_id[0]->id);
                $hash = (new Yespo_Cart_Event())->get_option();

                return $this->get_orders_items($order, $order_id[0]->id, $hash);
            }
        }
        return null;
    }

    public function get_orders_items($order, $id, $hash){

        $purchased_items = [];

        if ($order) {
            $currency = $order->get_currency();
            $cart = $order->get_items();

            $purchased_items['OrderNumber'] = $id;
            $purchased_items['GUID'] = $hash;

            foreach ($cart as $item) {

                $quantity = $item->get_quantity();

                $purchased_items['PurchasedItems'][] = array(
                    'productKey' => $item->get_product_id(),
                    'price' => $item->get_subtotal() / $quantity,
                    'quantity' => $quantity,
                    'currency' => $currency
                );
            }
        }
        return $purchased_items;
    }

    public function get_created_order(){

        global $wpdb;
        $table_posts = esc_sql($wpdb->prefix . 'wc_orders');

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id FROM %i 
                    WHERE type = %s 
                    AND status != %s 
                    ORDER BY id DESC
                    LIMIT 1",
                $table_posts,
                'shop_order',
                'wc-checkout-draft'
            )
        );

    }
}