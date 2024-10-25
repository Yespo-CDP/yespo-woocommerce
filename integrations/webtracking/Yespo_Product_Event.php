<?php

namespace Yespo\Integrations\Webtracking;

class Yespo_Product_Event extends Yespo_Web_Tracking_Abstract
{
    private $product;

    public function __construct(){
        global $product;
        $this->product = $product;
    }

    public function get_data(){
        // TODO: Implement get_data() method.
        if(is_product()){
            return [
                'id' => $this->getId(),
                'price' => $this->getPrice(),
                'stock' => $this->getStock()
            ];
        }
        return null;
    }

    private function getId(){
        return $this->product->get_id();
    }

    private function getPrice(){
        return $this->product->get_price();
    }

    private function getStock(){
        return $this->product->is_in_stock();
    }


}