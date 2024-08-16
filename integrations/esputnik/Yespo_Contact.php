<?php
/*** class sending and updating data in Esputnik service ***/

namespace Yespo\Integrations\Esputnik;

class Yespo_Contact
{
    const CUSTOMER = 'customer';
    const SUBSCRIBER = 'subscriber';
    private $period_selection_since = 7200;
    private $period_selection_up = 600;
    private $period_selection = 300;
    private $table_log_users;
    private $table_users;
    const REMOTE_CONTACT_ESPUTNIK_URL = "https://esputnik.com/api/v1/contact";
    const REMOTE_CONTACTS_ESPUTNIK_URL = "https://esputnik.com/api/v1/contacts";
    const GET_EXPORT_BULK_ESPUTNIK_URL = "https://esputnik.com/api/v1/importstatus/";
    const CUSTOM_REQUEST = "POST";
    const CUSTOM_REQUEST_DELETE = "DELETE";
    const CUSTOM_REQUEST_GET = "GET";
    const USER_META_KEY = 'yespo_contact_id';
    private $authData;
    private $wpdb;
    private $table_yespo_removed;

    public function __construct(){
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->authData = get_option('yespo_options');
        $this->table_log_users = $this->wpdb->prefix . 'yespo_contact_log';
        $this->table_users = $this->wpdb->prefix . 'users';
        $this->table_yespo_removed = $this->wpdb->prefix . 'yespo_removed_users';
    }

    public function create_on_yespo($email, $wc_id){
        $user = get_user_by('id', $wc_id);
        if ($this->check_user_role($user) || !email_exists($email)) {
            return $this->process_on_yespo(Yespo_Contact_Mapping::woo_to_yes($user), 'create', $wc_id);
        }
    }

    public function update_on_yespo($user){
        if ($this->check_user_role($user)) {
            return $this->process_on_yespo(Yespo_Contact_Mapping::woo_to_yes($user), 'update', $user->ID);
        }
    }

    public function update_woo_profile_yespo($request, $user){
        if ($this->check_user_role($user)) {
            return $this->process_on_yespo(Yespo_Contact_Mapping::update_woo_to_yes($request, $user->ID), 'update', $user->ID);
        }
    }

    public function update_woo_registered_user(){
        $users = $this->get_latest_created_users();
        if($users && is_array($users) && count($users) > 0){
            foreach($users as $userID){
                $user = get_user_by('id', $userID);
                if($user && $this->check_user_role($user)) $this->update_on_yespo($user);
            }
        }
    }

    public function get_yespo_user_id($id){
        $url = "https://esputnik.com/api/v1/contacts?externalCustomerId=" . $id ."&startindex=1&maxrows=500";
        $result = $this->process_on_yespo($url, 'add_meta_key');
        if($result){
            $response = json_decode($result);
            if($response[0]->id) {
                var_dump($response[0]->id);
                update_user_meta($id, self::USER_META_KEY, $response[0]->id);
                return $response[0]->id;
            }
        }
    }


    //method export bulk
    public function export_bulk_users($data){
        if(!empty($data)){

            //(new Yespo_Export_Orders())->add_json_log_entry($data);// add log entry to DB

            $response = $this->process_on_yespo($data, 'bulk');
            if($response === 0) (new Yespo_Export_Users())->error_export_users('555');
            else if($response == 400 || $response == 429 || $response == 500) return $response;

            if($response) $response = json_decode($response, true);

            if(isset($response["asyncSessionId"])){
                (new Yespo_Export_Users())->add_entry_yespo_queue($response["asyncSessionId"]);

                return $response["asyncSessionId"];
            } else if(isset($response["status"]) && intval($response["status"]) === 401){
                (new Yespo_Export_Users())->error_export_users($response["status"]);
            }
        }
    }

    //make private method
    public function get_bulk_response($sessionId) {
        if (isset($sessionId) && !empty($sessionId)) {
            do {
                $response = Yespo_Curl_Request::curl_request(self::GET_EXPORT_BULK_ESPUTNIK_URL . $sessionId, self::CUSTOM_REQUEST_GET, $this->authData);

                if ($response) {
                    $response = json_decode($response, true);

                    if ($response && $response["status"] === "FINISHED") {
                        if (isset($response["mapping"]) && is_array($response["mapping"])) {
                            $response["sessionId"] = $sessionId;
                            return $response;
                        } else return false;
                    } else {
                        sleep(10);
                    }
                }
            } while ($response && $response["status"] !== "FINISHED");
        }
        return false;
    }

    public function remove_user_phone_on_yespo($email){
        if ($this->check_user_role( get_user_by('email', $email) ) || !email_exists($email)) {
            return $this->process_on_yespo(Yespo_Contact_Mapping::clean_user_phone_data($email), 'clean');
        }
    }

    public function delete_from_yespo($user_id, $relocate = false){
        if (!empty($this->authData) && !empty($user_id)) {
            return $this->process_on_yespo(null, 'delete', null, $user_id, $relocate);
        }
    }

    public function remove_user_after_erase(){
        $users = $this->get_latest_users_activity();
        if(count($users) > 0){
            foreach ($users as $user_id){
                if(get_user_by( 'ID', $user_id )) {
                    $this->delete_from_yespo($user_id);
                }
            }
        }
    }

    private function process_on_yespo($data, $operation, $wc_id = null, $yespo_id = null, $relocate = false) {
        if (empty($this->authData)) {
            return esc_html__( 'Empty user authorization data', YESPO_TEXTDOMAIN );
        }

        $url = self::REMOTE_CONTACT_ESPUTNIK_URL;
        $request = self::CUSTOM_REQUEST;
        if($operation === 'delete') {
            if ($relocate) $erase = '&erase=false';
            else $erase = '&erase=true';
            $url = self::REMOTE_CONTACT_ESPUTNIK_URL . '?externalCustomerId=' . $yespo_id . $erase;

            $response = Yespo_Curl_Request::curl_request($url, self::CUSTOM_REQUEST_DELETE, $this->authData, $data);
            (new \Yespo\Integrations\Esputnik\Yespo_Logging_Data())->update_contact_log($yespo_id, $operation, $response);
        } else if($operation === 'add_meta_key'){
            return Yespo_Curl_Request::curl_request($data, self::CUSTOM_REQUEST_GET, $this->authData);
        } else if($operation === 'bulk'){
            return Yespo_Curl_Request::curl_request(self::REMOTE_CONTACTS_ESPUTNIK_URL, $request, $this->authData, $data);
        } else {
            if ($operation === 'clean') $url = self::REMOTE_CONTACTS_ESPUTNIK_URL;

            $response = Yespo_Curl_Request::curl_request($url, $request, $this->authData, $data);

            $responseArray = json_decode($response, true);

            if (isset($responseArray['id'])) {
                if ($operation !== 'delete') {
                    if ($wc_id !== null) {
                        $this->add_esputnik_id_to_userprofile($wc_id, $responseArray['id']);
                        $log_operation = ($operation === 'create') ? 'create' : (($operation === 'guest') ? 'guest' : (($operation === 'subscription') ? 'subscription' : 'update'));
                        (new Yespo_Logging_Data())->create((string)$wc_id, (string)$responseArray['id'], $log_operation); //add entry to logfile
                        return true;
                    }
                }
                return true;
            } else if ($response == 400){

            }
        }
        return esc_html__( 'Other user authorization operation', YESPO_TEXTDOMAIN );
    }

    public function get_meta_key(){
        return self::USER_META_KEY;
    }

    public function get_user_id_by_email($email){
        if ($user = get_user_by('email', $email)) {
            return $user->ID;
        }
        return false;
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
            self::CUSTOMER
        ];
    }

    public function get_user_metafield_id($user_id){
        return get_user_meta($user_id, self::USER_META_KEY, true);
    }


    public function add_esputnik_id_to_userprofile($user_id, $external_id){
        if (empty(get_user_meta($user_id, self::USER_META_KEY, true))) update_user_meta($user_id, self::USER_META_KEY, $external_id);
        else add_user_meta($user_id, self::USER_META_KEY, $external_id, true);
    }

    public function add_bulk_esputnik_id_to_userprofile($usersForExport, $meta_value){
        $values = [];
        foreach ($usersForExport as $user_id) {
            $values[] = $this->wpdb->prepare("(%d, %s, %s)", $user_id, self::USER_META_KEY, $meta_value);
        }

        if (!empty($values)) {
            $values_string = implode(", ", $values);
            $query = "INSERT INTO {$this->wpdb->usermeta} (user_id, meta_key, meta_value) VALUES $values_string 
              ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)";

            $this->wpdb->query($query);
        }
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
                "SELECT * FROM $this->table_log_users WHERE action = %s AND log_date BETWEEN %s AND %s AND yespo <> %d",
                'delete',
                date('Y-m-d H:i:s', time() - $this->period_selection_since),
                date('Y-m-d H:i:s', time() - $this->period_selection_up),
                200
            )
        );
    }

    private function get_latest_created_users(){
        return $this->wpdb->get_col(
            $this->wpdb->prepare(
                "SELECT ID FROM $this->table_users WHERE user_registered > %s",
                date('Y-m-d H:i:s', time() - $this->period_selection)
            )
        );
    }

    public function add_entry_removed_user($email){
        $time = current_time('mysql');

        $this->wpdb->insert(
            $this->table_yespo_removed,
            array(
                'email' => $email,
                'time' => $time,
            ),
            array(
                '%s',
                '%s',
            )
        );
    }



    public function export_active_bulk_users($data){
        if(!empty($data)){

            //(new Yespo_Export_Orders())->add_json_log_entry($data);// add log entry to DB

            $response = $this->process_on_yespo($data, 'bulk');
            if($response === 0) (new Yespo_Export_Users())->error_export_users('555');
            if($response) $response = json_decode($response, true);

            if(isset($response["asyncSessionId"])){
                (new Yespo_Export_Users())->add_entry_yespo_queue($response["asyncSessionId"]);

                return $response["asyncSessionId"];
            } else if(isset($response["status"]) && intval($response["status"]) === 401){
                (new Yespo_Export_Users())->error_export_users($response["status"]);
            }
        }
    }


}