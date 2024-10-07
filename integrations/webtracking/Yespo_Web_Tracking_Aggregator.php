<?php

namespace Yespo\Integrations\Webtracking;

class Yespo_Web_Tracking_Aggregator
{
    private $category;
    private $product;
    private $cart;

    public function __construct(){
        $this->category = new Yespo_Category_Event();
        $this->product = new Yespo_Product_Event();
        $this->cart = new Yespo_Cart_Event();
    }

    public function localize_scripts(){

        $category = $this->category->get_data();
        $product = $this->product->get_data();
        //if( is_cart() ) $cart = $this->cart->get_data();
        //else $cart = null;

        $tracking_data = $this->get_localization_map(
            $category,
            $product
        );

        if (!empty($tracking_data)) {
            wp_localize_script(YESPO_TEXTDOMAIN . '-plugin-script', 'trackingData', $tracking_data);
        }

    }

    private function get_localization_map(
        $category,
        $product
    ){

        $tracking_data = [];

        $tracking_data['ajaxUrl'] = esc_url( admin_url( 'admin-ajax.php' ) );

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

        //if (!is_null($cart)) {
            //$tracking_data['cart'] = $cart;
        //}

        return $tracking_data;
    }

}