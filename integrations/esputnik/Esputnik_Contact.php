<?php
/*** class sending and updating data in Esputnik service ***/

namespace Yespo\Integrations\Esputnik;

class Esputnik_Contact
{
    const REMOTE_CONTACT_ESPUTNIK_URL = "https://esputnik.com/api/v1/contact";
    const CUSTOM_REQUEST = "POST";
    const CUSTOM_REQUEST_DELETE = "DELETE";
    const USER_META_KEY = 'yespo_contact_id';
    private $authData;

    public function __construct(){
        $this->authData = get_option('yespo_options');
    }

    public function create_on_yespo($email, $wc_id){
        $user = get_user_by('id', $wc_id);
        return $this->process_on_yespo(Esputnik_Contact_Mapping::woo_to_yes($user), 'create', $wc_id);
    }

    public function create_guest_user_on_yespo($order){
        return $this->process_on_yespo(Esputnik_Contact_Mapping::guest_user_woo_to_yes($order), 'guest', $order->get_billing_email());
    }

    public function create_guest_user_admin_on_yespo($post){
        return $this->process_on_yespo(Esputnik_Contact_Mapping::guest_user_admin_woo_to_yes($post), 'guest', $post['_billing_email']??$post['_shipping_email']);
    }

    public function update_on_yespo($user){
        return $this->process_on_yespo(Esputnik_Contact_Mapping::woo_to_yes($user), 'update', $user->ID);
    }

    public function delete_from_yespo($user_id){
        $yespo_id = $this->get_user_metafield_id($user_id);
        if(!empty($this->authData) && !empty($yespo_id)){
            return $this->process_on_yespo(null, 'delete', null, $yespo_id);
        }
    }

    private function process_on_yespo($data, $operation, $wc_id = null, $yespo_id = null) {
        if (empty($this->authData)) {
            return __( 'Empty user authorization data', Y_TEXTDOMAIN );
        }

        $url = self::REMOTE_CONTACT_ESPUTNIK_URL;
        $request = self::CUSTOM_REQUEST;
        if($operation === 'delete'){
            $url = self::REMOTE_CONTACT_ESPUTNIK_URL . '/' . $yespo_id . '?erase=false';
            $request = self::CUSTOM_REQUEST_DELETE;
        }

        $response = Esputnik_Curl_Request::curl_request($url, $request, $this->authData, $data);
        $responseArray = json_decode($response, true);

        if(isset($responseArray['id'])) {
            if ($operation !== 'delete') {
                if ($wc_id !== null) {
                    $this->add_esputnik_id_to_userprofile($wc_id, $responseArray['id']);
                    $log_operation = ($operation === 'create') ? 'create' : (($operation === 'guest') ? 'guest' : 'update');
                    (new Esputnik_Logging_Data())->create((string)$wc_id, (string)$responseArray['id'], $log_operation); //add entry to logfile
                }
            }
            return true;
        }
        return __( 'Empty user authorization data', Y_TEXTDOMAIN );
    }

    public function get_meta_key(){
        return self::USER_META_KEY;
    }

    private function get_user_metafield_id($user_id){
        return get_user_meta($user_id, self::USER_META_KEY, true);
    }

    private function add_esputnik_id_to_userprofile($user_id, $external_id){
        if (empty(get_user_meta($user_id, self::USER_META_KEY, true))) update_user_meta($user_id, self::USER_META_KEY, $external_id);
        else add_user_meta($user_id, self::USER_META_KEY, $external_id, true);
    }
}