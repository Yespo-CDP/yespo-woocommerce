<?php

namespace Yespo\Integrations\Webtracking;

class Yespo_NotFound_Event extends Yespo_Web_Tracking_Abstract
{
    public function get_data(){
        // TODO: Implement get_data() method.
        if (is_404()) {

            return [
                'notFoundKey' => 'NotFound'
            ];
        }
        return null;
    }
}