<?php
/*** class sending and updating data in Esputnik service ***/

namespace Yespo\Integrations\Esputnik;

class Esputnik_Contact
{
    const REMOTE_CONTACT_ESPUTNIK_URL = "https://esputnik.com/api/v1/contact";
    const CUSTOM_REQUEST = "POST";
    private $authData = null;

    public function send_data($email, $wc_id){
        if(get_option('yespo_options') !== null && !empty(get_option('yespo_options'))){
            $this->authData = get_option('yespo_options');
            return Esputnik_Curl_Request::curl_request(
                self::REMOTE_CONTACT_ESPUTNIK_URL,
                self::CUSTOM_REQUEST,
                $this->authData,
                $this->get_user_data($email, $wc_id)
            );
        }
        return __( 'Empty user authorization data', Y_TEXTDOMAIN );
    }

    private function get_user_data($email, $wc_id){
        return [
            'channels' => [
                [
                    'value' => $email,
                    'type' => 'email'
                ],
            ],
            'externalCustomerId' => $wc_id,
        ];
    }
}