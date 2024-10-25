<?php

namespace Yespo\Integrations\Webtracking;

class Yespo_Web_Tracking_Aggregator
{
    private $category;
    private $product;
    private $cart;
    private $purchase;
    private $user;

    public function __construct(){
        $this->category = new Yespo_Category_Event();
        $this->product = new Yespo_Product_Event();
        $this->cart = new Yespo_Cart_Event();
        $this->purchase = new Yespo_Purchased_Event();
        $this->user = new Yespo_User_Event();
    }

    public function localize_scripts(){

        $category = $this->category->get_data();
        $product = $this->product->get_data();
        $purchase = $this->purchase->get_data();
        $user = $this->user->get_data();
        //if( is_cart() ) $cart = $this->cart->get_data();
        //else $cart = null;

        $tracking_data = $this->get_localization_map(
            $category,
            $product,
            $purchase,
            $user
        );

        if (!empty($tracking_data)) {
            wp_localize_script(YESPO_TEXTDOMAIN . '-plugin-script', 'trackingData', $tracking_data);
        }

    }

    private function get_localization_map(
        $category,
        $product,
        $purchase,
        $user
    ){

        $tracking_data = [];

        $tracking_data['ajaxUrl'] = esc_url( admin_url( 'admin-ajax.php' ) );
        $tracking_data['getCartContentNonce'] = wp_create_nonce('yespo_get_cart_content_nonce');

        if (!is_null($category)) {
            $tracking_data['category'] = array(
                'categoryKey' => isset($category['categoryKey']) ? esc_js($category['categoryKey']) : '',
            );
        }

        if (!is_null($product)) {
            $tracking_data['product'] = array(
                'id' => isset($product['id']) ? esc_js($product['id']) : '',
                'price' => isset($product['price']) ? esc_js($product['price']) : '',
                'stock' => isset($product['stock']) ? esc_js($product['stock']) : '',
            );
        }

        if (!is_null($purchase)) {
            $tracking_data['thankYou'] = $purchase;
        }

        if (!is_null($user)) {
            $tracking_data['customerData'] = $user;
        }

        return $tracking_data;
    }

}