<?php

namespace Yespo\Integrations\Webtracking;

class Yespo_Cart_Event extends Yespo_Web_Tracking_Abstract
{
    private $options;

    public function __construct(){
        $this->options = get_option('yespo_options');
    }

    public function get_data(){
        // TODO: Implement get_data() method.
        $cart_items = [];
        if ( class_exists( 'WooCommerce' ) && WC()->cart ) {

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

            if(empty($cart_items['GUID'])){
                $cart_items['GUID'] = $this->get_option();
                $this->update_option('');
            } else $this->update_option($cart_items['GUID']);

            return $cart_items;

        }
        return null;
    }

    private function update_option($cart_hash){
        $this->options['cart_hash'] = sanitize_text_field($cart_hash);
        update_option('yespo_options', $this->options);
    }

    public function get_option(){
        if (isset($this->options['cart_hash'])) return $this->options['cart_hash'];
    }
}