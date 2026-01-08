<?php

namespace Yespo\Integrations\Webtracking;

class Yespo_User_Event extends Yespo_Web_Tracking_Abstract
{
    const USER_AUTH_LABEL = 'user_auth_label';

    public function __construct() {}

    public function get_data() {
        $user_id = get_current_user_id();

        if (!$user_id) return null;

        $user_meta = get_user_meta($user_id);
        $user_info = get_userdata($user_id);

        if (empty($user_meta[self::USER_AUTH_LABEL][0])) return null;

        delete_user_meta($user_id, self::USER_AUTH_LABEL);

        return [
            'externalCustomerId' => $user_id,
            'user_email' => $user_info->user_email ?? null,
            'user_name' => $user_info->display_name ?? null,
            'user_phone' => $user_meta['billing_phone'][0] ?? null,
        ];
    }

    public function handle_user_event($user_id_or_login, $user = null) {
        $user_data = $this->get_user_data($user_id_or_login);
        if(!$this->get_webId()) return false;

        $user_json = $this->generate_user_json("CustomerData", $user_data);
        Yespo_Web_Tracking_Curl_Request::curl_request($user_json);

        $webcontact = $this->get_webcontact_data($user_data);
        $response = json_decode(Yespo_Web_Tracking_Curl_Request::curl_request($webcontact), true);

        (new Yespo_Logger())->write_to_file('CustomerData', $user_json, $response['response_body']);
        if($response['code'] > 199 && $response['code'] < 300) Yespo_Logging_Remote::send_customer_data_event_success($response['request_data'], $response['response_body'], $response['code']);
        else Yespo_Logging_Remote::send_customer_data_event_error($response['code'], $response['request_data'], $response['response_body'], $response['code']);

        $user = get_user_by('login', $user_id_or_login);
        if ($user) {
            return $this->add_label_to_user($user->ID);
        }

        return true;
    }

    public function after_order_complete($order_id){

        $get_webId = $this->get_webId();
        if (!isset($order_id) || !is_numeric($order_id) || !$get_webId) {
            return;
        }

        $user_data = $this->get_user_from_order($order_id);
        $user_json = ["GeneralInfo" => $this->generate_user_info( "CustomerData", $user_data, $get_webId, $this->get_tenantId()) ];

        Yespo_Web_Tracking_Curl_Request::curl_request($user_json);
        $webcontact = $this->get_webcontact_data($user_data);

        $response = Yespo_Web_Tracking_Curl_Request::curl_request($webcontact);

        (new Yespo_Logger())->write_to_file('CustomerData', $user_json, $response);

        return true;
    }

    public function get_webcontact_data($user_data){
        $webcontact = [
            "externalCustomerId" => !empty($user_data['externalCustomerId']) ? strval($user_data['externalCustomerId']) : null,
            "webId" => $this->get_webId(),
            "orgId" => (int)$this->get_orgId()
        ];
        if (!empty($user_data['user_email'])) {
            $webcontact['email'] = $user_data['user_email'];
        }

        if (!empty($user_data['user_phone'])) {
            $webcontact['phone'] = $user_data['user_phone'];
        }

        return $webcontact;
    }

    public function get_user_data($user_id_or_login) {

        if (empty($user_id_or_login) || !is_string($user_id_or_login)) {
            return null;
        }

        $user = get_user_by('login', sanitize_user($user_id_or_login));
        $user_id = $user instanceof \WP_User ? $user->ID : null;

        if (!$user_id) return null;

        $user_meta = get_user_meta($user_id);
        $user_info = get_userdata($user_id);

        return [
            'externalCustomerId' => $user_id,
            'user_email' => $user_info->user_email ?? null,
            'user_name' => $user_info->display_name ?? null,
            'user_phone' => $user_meta['billing_phone'][0] ?? null,
        ];
    }

    private function add_label_to_user($user_id){
        return update_user_meta($user_id, self::USER_AUTH_LABEL, 'true');
    }

    public function generate_user_json($eventName, $user_data){
        return [
            "GeneralInfo" => $this->generate_user_array($eventName, $user_data)
        ];
    }

    public function generate_user_array($eventName, $user_data){
        return $this->generate_user_info(
            $eventName,
            $user_data,
            $this->get_webId(),
            $this->get_tenantId()
        );
    }

    public function get_tenantId(){
        return (new Yespo_Web_Tracking_Script())->get_tenant_id_from_options();
    }

    public function get_webId(){
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $webId = $_SESSION['webId'] ?? null;
        $userIP = (new Yespo_Logger())->get_user_IP();
        //(new Yespo_Logger())->write_to_file('sc', $webId, 'used from session ' . $userIP);

        return $webId;
    }

    public function get_orgId(){
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION['orgId'] ?? null;
    }

    // json generation
    public function generate_user_info($eventName, $user_data, $webId, $tenantId){
        return array_filter([
            "eventName" => $eventName,
            "siteId" => $tenantId,
            "datetime" => time() * 1000,
            "externalCustomerId" => !empty($user_data['externalCustomerId']) ? $user_data['externalCustomerId'] : null,
            "user_phone" => !empty($user_data['user_phone']) ? $user_data['user_phone'] : null,
            "user_email" => !empty($user_data['user_email']) ? $user_data['user_email'] : null,
            "user_name" => !empty($user_data['user_name']) ? $user_data['user_name'] : null,
            "cookies" => [
                "sc" => $webId
            ]
        ], function ($value) {
            return !is_null($value) && $value !== '';
        });

    }

    //user from order
    private function get_user_from_order($order_id){

        $order = wc_get_order($order_id);
        if (!$order) return false;

        return array_filter([
            'externalCustomerId' => $order->get_customer_id() ?: null,
            'user_email' => $order->get_billing_email() ?: null,
            'user_name' => trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()) ?: null,
            'user_phone' => $order->get_billing_phone() ?: null,
        ]);

    }

}