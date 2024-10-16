<?php

namespace Yespo\Integrations\Webtracking;

class Yespo_Purchased_Event extends Yespo_Web_Tracking_Abstract
{

    const PURCHASED_ITEMS = 'purchased_items';
    public function __construct() {

        add_action('woocommerce_thankyou', [$this, 'add_order_to_session'], 10, 1);

        if (!session_id()) {
            session_start();
        }
    }

    public function add_order_to_session($order_id) {
        if (!empty($order_id)) {
            $order = wc_get_order($order_id);
            $hash = (new Yespo_Cart_Event())->get_option();

            $purchased_items = $this->get_orders_items($order, $order_id, $hash);

            $_SESSION[self::PURCHASED_ITEMS] = $purchased_items;
        }
    }

    public function get_data() {

        if (!empty($_SESSION[self::PURCHASED_ITEMS])) {
            $data = $_SESSION[self::PURCHASED_ITEMS];

            unset($_SESSION[self::PURCHASED_ITEMS]);

            return $data;
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

}