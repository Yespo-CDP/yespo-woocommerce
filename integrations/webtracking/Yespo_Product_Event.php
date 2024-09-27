<?php

namespace Yespo\Integrations\Webtracking;

class Yespo_Product_Event extends Yespo_Web_Tracking_Abstract
{
    private $product;

    public function getData(){
        return $this->product;
    }

    private function getId(){
        return get_the_ID();
    }


}