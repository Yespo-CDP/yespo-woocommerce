<?php

namespace Yespo\Integrations\Webtracking;

class Yespo_User_Event extends Yespo_Web_Tracking_Abstract
{

    public function get_data()
    {
        // TODO: Implement get_data() method.

    }

    public function get_user_data(){
        $current_user = wp_get_current_user();

        $current_user = [];
        if ( $current_user->ID ) {
            $user_data['user'][] = array(
                'externalCustomerId' => $current_user->ID,
                'user_email' => $current_user->user_email,
                'user_name' => $current_user->display_name,
                'user_phone' => '3801111111111',
            );
        }

    }
}