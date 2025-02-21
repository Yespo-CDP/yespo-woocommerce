<?php

namespace Yespo\Integrations\Webtracking;

class Yespo_Front_Event extends Yespo_Web_Tracking_Abstract
{
    public function get_data(){
        // TODO: Implement get_data() method.
        if (is_front_page() || is_home()) {

            return [
                'frontKey' => 'MainPage'
            ];
        }
        return null;
    }
}