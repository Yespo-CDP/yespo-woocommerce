<?php
/*** class sending and updating data in Esputnik service ***/

namespace Yespo\Integrations\Esputnik;

class Esputnik_Contact
{
    const REMOTE_CONTACT_ESPUTNIK_URL = "https://esputnik.com/api/v1/contact";
    const CUSTOM_REQUEST = "POST";
    private $authData;
    private $meta_key = 'yespo_contact_id';

    public function __construct(){
        $this->authData = get_option('yespo_options');
    }

    public function create_on_yespo($email, $wc_id){
        if(!empty($this->authData)){
            $response = Esputnik_Curl_Request::curl_request(
                self::REMOTE_CONTACT_ESPUTNIK_URL,
                self::CUSTOM_REQUEST,
                $this->authData,
                $this->get_user_data($email, $wc_id)
            );
            $responseArray = json_decode($response, true);

            if(isset($responseArray['id'])) {
                $this->add_esputnik_id_to_userprofile($wc_id, $responseArray['id']);
                (new Esputnik_Logging_Data())->create((string)$wc_id, (string)$responseArray['id'], 'create'); //add entry to logfile
            }
            return true;
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

    private function add_esputnik_id_to_userprofile($user_id, $external_id){
        add_user_meta($user_id, $this->meta_key, $external_id, true);
    }
}