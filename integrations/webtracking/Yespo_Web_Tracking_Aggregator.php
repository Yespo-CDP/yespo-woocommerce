<?php

namespace Yespo\Integrations\Webtracking;

class Yespo_Web_Tracking_Aggregator
{
    private $category;
    private $product;
    public function __construct(){
        $this->category = new Yespo_Category_Event();
        $this->product = new Yespo_Product_Event();
    }

    public function localize_scripts(){

        $category = $this->category->getData();
        $product = $this->product->getData();

        $tracking_data = $this->get_localization_map(
            $category,
            $product
        );

        if (!empty($tracking_data)) {
            wp_localize_script('yespo-tracking-script', 'trackingData', $tracking_data);
        }

    }

    private function get_localization_map($category, $product){

        $tracking_data = [];

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

        return $tracking_data;
    }

}