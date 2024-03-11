<?php
/*** class sending and updating data in Esputnik service ***/

namespace Yespo\Integrations\Esputnik;

class Esputnik_Contact
{
    const REMOTE_CONTACT_ESPUTNIK_URL = "https://esputnik.com/api/v1/contact";
    const CUSTOM_REQUEST = "POST";
    const USER_META_KEY = 'yespo_contact_id';
    private $authData;

    public function __construct(){
        $this->authData = get_option('yespo_options');
    }

    public function create_on_yespo($email, $wc_id){
        if(!empty($this->authData)){
            $user = get_user_by('id', $wc_id);
            $response = Esputnik_Curl_Request::curl_request(
                self::REMOTE_CONTACT_ESPUTNIK_URL,
                self::CUSTOM_REQUEST,
                $this->authData,
                Esputnik_Contact_Mapping::woo_to_yes($user)//$this->get_user_data($email, $wc_id)
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

    public function update_on_yespo($user){
        if(!empty($user)){
            $response = Esputnik_Curl_Request::curl_request(
                self::REMOTE_CONTACT_ESPUTNIK_URL,
                self::CUSTOM_REQUEST,
                $this->authData,
                Esputnik_Contact_Mapping::woo_to_yes($user)
            );
            $responseArray = json_decode($response, true);

            if(isset($responseArray['id'])) {
                $this->add_esputnik_id_to_userprofile($user->ID, $responseArray['id']);
                (new Esputnik_Logging_Data())->create((string)$user->ID, (string)$responseArray['id'], 'update'); //update entry to logfile
            }
            return $responseArray;
        }
        return __( 'Empty user authorization data', Y_TEXTDOMAIN );
    }

    public function get_meta_key(){
        return self::USER_META_KEY;
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
        if (empty(get_user_meta($user_id, self::USER_META_KEY, true))) update_user_meta($user_id, self::USER_META_KEY, $external_id);
        else add_user_meta($user_id, self::USER_META_KEY, $external_id, true);
    }
}