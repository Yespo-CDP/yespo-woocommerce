<?php

namespace Yespo\Integrations\Webtracking;

use Exception;

class Yespo_Web_Tracking_Script
{
    const ADD_DOMAIN_URL = "https://yespo.io/api/v1/site/domains";
    const SCRIPT_URL = "https://yespo.io/api/v1/site/script";
    const METHOD_POST = "POST";
    const METHOD_GET = "GET";
    private $options;

    public function __construct(){
        $this->options = get_option('yespo_options');
    }

    public function check_script_code_cron(){
        if(!$this->is_script_in_options()){
            $this->make_tracking_script();
        }
    }
    public function get_script_from_options(){
        if($this->is_script_in_options()) {
            return json_decode( $this->options['yespo_tracking_script'] );
        }

    }

    public function get_tenant_id_from_options(){
        if($this->is_tenant_in_options()) {
            return $this->options['yespo_tenant_id'];
        }

    }

    public function make_tracking_script(){
        if(!$this->get_label_domain_from_options()) {
            $this->add_label_domain_options('true');

            $this->add_script_to_options();
            $this->add_tenant_id_to_options();

        }
    }

    public function send_domain_to_yespo(){
        $url = $this->get_url();
        if(!empty($url)) {
            $data = ['domain' => $url];
            return $this->make_curl_request(
                self::ADD_DOMAIN_URL,
                self::METHOD_POST,
                $this->options,
                $data
            );
        }
        return false;
    }

    public function add_label_domain_options($status){
        $this->options['yespo_domain_sent'] = $status;
        update_option('yespo_options', $this->options);
    }

    public function get_label_domain_from_options(){
        if (isset($this->options['yespo_domain_sent'])) return $this->options['yespo_domain_sent'];
    }

    public function add_script_to_options(){
        if(!$this->is_script_in_options()) {
            $script = $this->get_tracking_script();
            if($script) {
                $this->options['yespo_tracking_script'] = $script;
                update_option('yespo_options', $this->options);
            }
        }
    }

    public function add_tenant_id_to_options(){
        if (!$this->is_tenant_in_options()) {
            $response = $this->send_domain_to_yespo();

            if (!empty($response)) {
                $data = json_decode($response, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($data) && !empty($data['siteId'])) {
                    $tenantId = $data['siteId'];

                    (new Yespo_Logger())->write_to_file('Response post curl', $tenantId, 'got tenantId');

                    $this->options['yespo_tenant_id'] = $tenantId;
                    update_option('yespo_options', $this->options);
                } else {
                    $error_msg = json_last_error() !== JSON_ERROR_NONE
                        ? 'JSON decode error: ' . json_last_error_msg()
                        : 'Missing or invalid siteId';
                    (new Yespo_Logger())->write_to_file('Response post curl', $error_msg, 'error');
                }
            } else {
                (new Yespo_Logger())->write_to_file('Response post curl', 'Empty response from send_domain_to_yespo', 'error');
            }

        }

    }

    public function is_script_in_options(){
        if (isset($this->options['yespo_tracking_script'])) return true;
        return false;
    }

    public function is_tenant_in_options(){
        if (isset($this->options['yespo_tenant_id'])) return true;
        return false;
    }

    private function get_tracking_script(){
        return $this->make_curl_request(
            self::SCRIPT_URL,
            self::METHOD_GET,
            $this->options
        );
    }

    private function get_url(){
        $url = get_site_url();
        if(empty($url)) $url = home_url();

        return $url;
    }

    private function make_curl_request(
        $url,
        $custom_request,
        $auth_data,
        $data = false
    ){
        try {

            if (!empty($auth_data['yespo_api_key'])) {

                $headers = [
                    'Accept' => 'application/json; charset=UTF-8',
                    'Authorization' => 'Basic ' . base64_encode(':' . $auth_data['yespo_api_key']),
                    'Content-Type' => 'application/json',
                ];

                if ($custom_request === 'GET') {
                    $headers = [
                        'Accept' => 'text/plain',
                        'Authorization' => 'Basic ' . base64_encode(':' . $auth_data['yespo_api_key']),
                    ];
                }

                $args = [
                    'method' => $custom_request,
                    'timeout' => 30,
                    'headers' => $headers,
                    'body' => !empty($data) ? wp_json_encode($data) : '',
                ];

                $response = wp_remote_request($url, $args);

                if (is_wp_error($response)) {
                    return 'Error: ' . $response->get_error_message();
                }

                $http_code = wp_remote_retrieve_response_code($response);
                $response_body = wp_remote_retrieve_body($response);

                if ($custom_request === 'POST') {
                    if (($http_code === 200) || ($http_code === 201) || ($http_code === 400 && $response_body === "Domain already exists")) {
                        return $response_body;
                    }

                    return false;
                }

                if ($custom_request === 'GET') {
                    if ($http_code === 200) {
                        return wp_json_encode($response_body);
                    }

                    return false;
                }

                return $response_body;
            }

        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}