<?php

namespace Yespo\Integrations\Webtracking;

class Yespo_Cart_Event extends Yespo_Web_Tracking_Abstract
{
    const CART_HASH = 'cart_hash';
    const YESPO_OPTIONS = 'yespo_options';
    private $options;

    public function __construct(){
        $this->options = get_option(self::YESPO_OPTIONS);
    }

    // SEND DATA TO YESPO
    public function add_to_cart_event() {
        $json = $this->generate_json();
        $response = Yespo_Web_Tracking_Curl_Request::curl_request($json);

        (new Yespo_Logger())->write_to_file('StatusCart', $json, $response);

        return true;
    }

    public function after_cart_item_quantity_update() {
        $json = $this->generate_json();
        $response = Yespo_Web_Tracking_Curl_Request::curl_request($json);

        (new Yespo_Logger())->write_to_file('StatusCart', $json, $response);

        return true;
    }

    public function cart_item_removed() {
        $json = $this->generate_json();
        $response = Yespo_Web_Tracking_Curl_Request::curl_request($json);

        (new Yespo_Logger())->write_to_file('StatusCart', $json, $response);

        return true;
    }


    // GET CART DATA
    public function get_data(){
        // TODO: Implement get_data() method.
        $cart_items = [];
        if ( class_exists( 'WooCommerce' ) && WC()->cart ) {

            foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                $product = $cart_item['data'];
                $cart_items['products'][] = array(
                    'productKey' => strval($product->get_id()),
                    'price' => $product->get_price(),
                    'quantity' => $cart_item['quantity'],
                    'currency' => get_woocommerce_currency()
                );
            }

            $cart_items['GUID'] =  WC()->cart->get_cart_hash();

            if(empty($cart_items['GUID'])){
                $cart_items['GUID'] = $this->get_option();
                $cart_items['empty'] = true;
                //$this->update_option('');
            } else $this->update_option($cart_items['GUID']);

            return $cart_items;

        }
        return null;
    }

    private function update_option($cart_hash){
        $this->options[self::CART_HASH] = sanitize_text_field($cart_hash);
        update_option(self::YESPO_OPTIONS, $this->options);
    }

    public function get_option(){
        if (isset($this->options[self::CART_HASH])) return $this->options[self::CART_HASH];
        return false;
    }

    //DETECTING CART PAGE
    public function get_cart_page(){
        if (is_cart()) {
            return [
                'cartPageKey' => 'StatusCartPage'
            ];
        }
        return null;
    }


    //GENERATE JSON FOR TRANSFERING
    public function generate_json() {
        $cart_data = $this->get_data();

        $general_info = (new Yespo_User_Event)->generate_user_array("StatusCart", '');
        $status_cart = [
            "GUID" => $cart_data['GUID'] ?? uniqid(),
            "Products" => array_map(function ($product) {
                return array_filter([
                    "productKey" => $product['productKey'] ?? null,
                    "price" => $product['price'] ?? null,
                    "discount" => $product['discount'] ?? null,
                    "quantity" => $product['quantity'] ?? null,
                    "price_currency_code" => $product['currency'] ?? null
                ], function ($value) {
                    return !is_null($value);
                });
            }, $cart_data['products'] ?? [])
        ];

        if (isset($cart_items['empty']) && $cart_items['empty'] === true ) $this->update_option('');

        return [
            "GeneralInfo" => $general_info,
            "StatusCart" => $status_cart
        ];
    }

}