<?php

namespace Yespo\Integrations\Webtracking;

class Yespo_User_Event extends Yespo_Web_Tracking_Abstract
{

    public function get_data()
    {
        // TODO: Implement get_data() method.
        $current_user = wp_get_current_user();

        if ( $current_user->ID ) {
            $user_data[] = array(
                'externalCustomerId' => $current_user->ID,
                'user_email' => $current_user->user_email,
                'user_name' => $current_user->display_name,
                'user_phone' => get_user_meta($current_user->ID, 'billing_phone', true)
            );

            return $user_data;
        }

        return null;

    }

    public function get_user_data(){
    }
}