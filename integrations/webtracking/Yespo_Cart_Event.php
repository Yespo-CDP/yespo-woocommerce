<?php

namespace Yespo\Integrations\Webtracking;

class Yespo_Cart_Event extends Yespo_Web_Tracking_Abstract
{
    public function get_data(){
        // TODO: Implement get_data() method.
        if ( WC()->cart ) {
            $cart_items = [];

            foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                $product = $cart_item['data'];
                $cart_items['products'][] = array(
                    'productKey' => $product->get_id(),
                    'price' => $product->get_price(),
                    'quantity' => $cart_item['quantity'],
                    'currency' => get_woocommerce_currency()
                );
            }

            $cart_items['GUID'] =  WC()->cart->get_cart_hash();

            if(count($cart_items['products']) > 0) return $cart_items;
            else return null;
        }
        return null;
    }
}