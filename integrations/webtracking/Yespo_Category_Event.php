<?php

namespace Yespo\Integrations\Webtracking;

class Yespo_Category_Event extends Yespo_Web_Tracking_Abstract
{
    public function get_data(){
        // TODO: Implement get_data() method.
        if (is_product_category()) {
            $category = get_queried_object();

            return [
                'categoryKey' => $category->name
            ];
        }
        return null;
    }
}