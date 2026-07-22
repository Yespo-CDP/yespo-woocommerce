<?php

namespace Yespo\Integrations\Webtracking;

class Yespo_Web_Tracking_Aggregator
{
    private $category;
    private $product;
    private $cart;
    private $front;
    private $notFound;
    private $userData;
    public function __construct(){
        $this->category = new Yespo_Category_Event();
        $this->product = new Yespo_Product_Event();
        $this->cart = new Yespo_Cart_Event();
        $this->front = new Yespo_Front_Event();
        $this->notFound = new Yespo_NotFound_Event();
        $this->userData = new Yespo_User_Event();
    }

    public function localize_scripts(){

        $category = $this->category->get_data();
        $product = $this->product->get_data();
        $front = $this->front->get_data();
        $notFound = $this->notFound->get_data();
        $cart = $this->cart->get_cart_page();
        $userData = $this->userData->get_data();

        $tracking_data = $this->get_localization_map(
            $category,
            $product,
            $front,
            $notFound,
            $cart,
            $userData
        );

        if (!empty($tracking_data)) {
            wp_localize_script(YESPO_TEXTDOMAIN . '-plugin-script', 'trackingData', $tracking_data);
        }

    }

    private function get_localization_map(
        $category,
        $product,
        $front,
        $notFound,
        $cart,
        $userData
    ){

        $tracking_data = [];

        $tracking_data['ajaxUrl'] = esc_url( admin_url( 'admin-ajax.php' ) );
        $tracking_data['getCartContentNonce'] = wp_create_nonce('yespo_get_cart_content_nonce');
        $tracking_data['getAppInboxAuthToken'] = ''; //(new Yespo_Web_Tracking_Script_Configuration())->get_auth_token();
        $tracking_data['getAuthCallbackNonce'] = wp_create_nonce('yespo_get_app_inbox_auth_token_action');


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

        if (!is_null($front)) {
            $tracking_data['front'] = array(
                'frontKey' => isset($front['frontKey']) ? esc_js($front['frontKey']) : '',
            );
        }

        if (!is_null($notFound)) {
            $tracking_data['notFound'] = array(
                'notFoundKey' => isset($notFound['notFoundKey']) ? esc_js($notFound['notFoundKey']) : '',
            );
        }

        if (!is_null($cart)) {
            $tracking_data['cart'] = array(
                'cartPageKey' => isset($cart['cartPageKey']) ? esc_js($cart['cartPageKey']) : '',
            );
        }
/*
        if (!is_null($userData)) {
            $tracking_data['userData'] = array(
                'externalCustomerId' => isset($userData['externalCustomerId']) ? esc_js($userData['externalCustomerId']) : '',
                'user_email' => isset($userData['user_email']) ? esc_js($userData['user_email']) : '',
                'user_name' => isset($userData['user_name']) ? esc_js($userData['user_name']) : '',
                'user_phone' => isset($userData['user_phone']) ? esc_js($userData['user_phone']) : '',
            );
        }
*/
        if (!is_null($userData) && !empty($userData['externalCustomerId'])) {
            $tracking_data['customerData'] = $userData;
        }

        $tracking_data['tenantWebId'] = wp_create_nonce('yespo_send_tenant_webid');

        return $tracking_data;
    }

}