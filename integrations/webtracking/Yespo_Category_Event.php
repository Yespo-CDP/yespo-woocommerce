<?php

namespace Yespo\Integrations\Webtracking;

class Yespo_Category_Event extends Yespo_Web_Tracking_Abstract
{
    public function getData(){
        // TODO: Implement getData() method.
        if (is_product_category()) {
            $category = get_queried_object();

            return [
                'categoryKey' => $category->term_id
            ];
        }
        return null;
    }
}