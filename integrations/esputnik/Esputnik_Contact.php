<?php
/*** class sending and updating data in Esputnik service ***/

namespace Yespo\Integrations\Esputnik;

class Esputnik_Contact
{
    private $period_selection_since = 600;
    private $period_selection_up = 400;
    private $table_log_users;
    const REMOTE_CONTACT_ESPUTNIK_URL = "https://esputnik.com/api/v1/contact";
    const REMOTE_CONTACTS_ESPUTNIK_URL = "https://esputnik.com/api/v1/contacts";
    const CUSTOM_REQUEST = "POST";
    const CUSTOM_REQUEST_DELETE = "DELETE";
    const USER_META_KEY = 'yespo_contact_id';
    private $authData;
    private $wpdb;

    public function __construct(){
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->authData = get_option('yespo_options');
        $this->table_log_users = $this->wpdb->prefix . 'yespo_contact_log';
    }

    public function create_on_yespo($email, $wc_id){
        $user = get_user_by('id', $wc_id);
        if ($this->check_user_role($user) || !email_exists($email)) {
            return $this->process_on_yespo(Esputnik_Contact_Mapping::woo_to_yes($user), 'create', $wc_id);
        }
    }

    public function create_guest_user_on_yespo($order){
        $email = $order->get_billing_email();
        $user = get_user_by('email', $email);
        if ($this->check_user_role($user) || !email_exists($email)) {
            if ($user) return $this->create_on_yespo($email, $user->ID);
            else return $this->process_on_yespo(Esputnik_Contact_Mapping::guest_user_woo_to_yes($order), 'guest', $email);
        }
        //return $this->process_on_yespo(Esputnik_Contact_Mapping::guest_user_woo_to_yes($order), 'guest', $email);
    }

    public function create_guest_user_admin_on_yespo($post){
        $email = $post['_billing_email'] ?? $post['_shipping_email'];
        if ($this->check_user_role( get_user_by('email', $email) ) || !email_exists($email)) {
            return $this->process_on_yespo(Esputnik_Contact_Mapping::guest_user_admin_woo_to_yes($post), 'guest', $email);
        }
    }

    public function create_subscribed_user_on_yespo($email){
        if ($this->check_user_role( get_user_by('email', $email) ) || !email_exists($email)) {
            return $this->process_on_yespo(Esputnik_Contact_Mapping::subscribed_user_woo_to_yes($email), 'subscription', $email);
        }
    }

    public function update_on_yespo($user){
        if ($this->check_user_role($user)) {
            return $this->process_on_yespo(Esputnik_Contact_Mapping::woo_to_yes($user), 'update', $user->ID);
        }
    }

    public function remove_user_phone_on_yespo($email){
        if ($this->check_user_role( get_user_by('email', $email) ) || !email_exists($email)) {
            return $this->process_on_yespo(Esputnik_Contact_Mapping::clean_user_phone_data($email), 'clean');
        }
    }

    public function remove_user_data_from_yespo($email){
        if ($this->check_user_role( get_user_by('email', $email) ) || !email_exists($email)) {
            return $this->process_on_yespo(Esputnik_Contact_Mapping::clean_user_personal_data($email), 'update');
        }
    }

    public function delete_from_yespo($user_id){
        //$yespo_id = $this->get_user_metafield_id($user_id);
        /*
        if (!empty($this->authData) && !empty($yespo_id)) {
            return $this->process_on_yespo(null, 'delete', null, $yespo_id);
        }
        */
        if (!empty($this->authData) && !empty($user_id)) {
            return $this->process_on_yespo(null, 'delete', null, $user_id);
        }
    }

    public function remove_user_after_erase(){
        $users = $this->get_latest_users_activity();
        if(count($users) > 0){
            foreach ($users as $user_id){
                if(!get_user_by( 'ID', $user_id )) $this->delete_from_yespo($user_id);
            }
        }
    }

    private function process_on_yespo($data, $operation, $wc_id = null, $yespo_id = null) {
        if (empty($this->authData)) {
            return __( 'Empty user authorization data', Y_TEXTDOMAIN );
        }

        $url = self::REMOTE_CONTACT_ESPUTNIK_URL;
        $request = self::CUSTOM_REQUEST;
        if($operation === 'delete'){
            $url = self::REMOTE_CONTACT_ESPUTNIK_URL . '?externalCustomerId=' . $yespo_id . '&erase=true';
            //$url = self::REMOTE_CONTACT_ESPUTNIK_URL . '/' . $yespo_id . '?erase=false';
            $request = self::CUSTOM_REQUEST_DELETE;
            //var_dump($url);
        }
        if($operation === 'clean') $url = self::REMOTE_CONTACTS_ESPUTNIK_URL;

        $response = Esputnik_Curl_Request::curl_request($url, $request, $this->authData, $data);
        $responseArray = json_decode($response, true);

        if(isset($responseArray['id'])) {
            if ($operation !== 'delete') {
                if ($wc_id !== null) {
                    $this->add_esputnik_id_to_userprofile($wc_id, $responseArray['id']);
                    //$log_operation = ($operation === 'create') ? 'create' : (($operation === 'guest') ? 'guest' : 'update');
                    $log_operation = ($operation === 'create') ? 'create' : (($operation === 'guest') ? 'guest' : (($operation === 'subscription') ? 'subscription' : 'update'));
                    (new Esputnik_Logging_Data())->create((string)$wc_id, (string)$responseArray['id'], $log_operation); //add entry to logfile
                    return true;
                }
            }
            return true;
        }
        return __( 'Other user authorization operation', Y_TEXTDOMAIN );
    }

    public function get_meta_key(){
        return self::USER_META_KEY;
    }

    public function check_user_role($user){
        if (isset($user->roles) &&
            is_array($user->roles) &&
            !empty($user->roles) &&
            in_array($user->roles[0], $this->get_user_type_allowed())
        ) return true;
    }
    private function get_user_type_allowed(){
        return [
            'subscriber',
            'customer'
        ];
    }

    private function get_user_metafield_id($user_id){
        return get_user_meta($user_id, self::USER_META_KEY, true);
    }

    private function add_esputnik_id_to_userprofile($user_id, $external_id){
        if (empty(get_user_meta($user_id, self::USER_META_KEY, true))) update_user_meta($user_id, self::USER_META_KEY, $external_id);
        else add_user_meta($user_id, self::USER_META_KEY, $external_id, true);
    }

    private function get_latest_users_activity(){
        $results = $this->get_users_from_log();
        $users = [];
        if(count($results) > 0){
            foreach($results as $item){
                $users[] = $item->user_id;
            }
        }
        return array_unique($users);
    }
    private function get_users_from_log(){
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM $this->table_log_users WHERE action = %s AND log_date BETWEEN %s AND %s",
                'update',
                date('Y-m-d H:i:s', time() - $this->period_selection_since),
                date('Y-m-d H:i:s', time() - $this->period_selection_up)
            )
        );
    }
}