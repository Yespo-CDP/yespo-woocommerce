<?php

namespace Yespo\Integrations\Webtracking;

class Yespo_Web_Tracking_Aggregator
{
    private $category;
    public function __construct(){
        $this->category = new Yespo_Category_Event();
    }

    public function localize_scripts(){
        $category_key = $this->category->getData();

        wp_localize_script('yespo-tracking-script', 'trackingData', array(
            'categoryKey' => esc_js($category_key),
            'product' => array(
                'id' => esc_js($product_id),
                'name' => esc_js($product_name),
                'price' => esc_js($product_price),
            ),
        ));
    }
}