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

    public function get_script_from_options(){
        if($this->is_script_in_options()) {
            return json_decode( $this->options['yespo_tracking_script'] );
        }
        else $this->make_tracking_script();
    }

    public function make_tracking_script(){
        if(!$this->get_label_domain_from_options()) {
            $this->add_label_domain_options('true');
            if ($this->send_domain_to_yespo()) {
                $this->add_script_to_options();
            }
        }
    }

    public function send_domain_to_yespo(){
        $url = $this->get_url();
        //$url = 'https://www.padi.com/'; // needs be removed
        //$url = 'https://www.krabiresort.net/';
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

    public function is_script_in_options(){
        if (isset($this->options['yespo_tracking_script'])) return true;
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

            $headers = [
                'Accept'        => 'application/json; charset=UTF-8',
                'Authorization' => 'Basic ' . base64_encode(':' . $auth_data['yespo_api_key']),
                'Content-Type'  => 'application/json',
            ];

            if ($custom_request === 'GET') {
                $headers = [
                    'Accept'        => 'text/plain',
                    'Authorization' => 'Basic ' . base64_encode(':' . $auth_data['yespo_api_key']),
                ];
            }

            $args = [
                'method'  => $custom_request,
                'timeout' => 30,
                'headers' => $headers,
                'body'    => !empty($data) ? wp_json_encode($data) : '',
            ];

            $response = wp_remote_request($url, $args);

            if (is_wp_error($response)) {
                return 'Error: ' . $response->get_error_message();
            }

            $http_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);


            $file_path = $_SERVER['DOCUMENT_ROOT'] . '/filedebug.txt';
            $data_to_append = json_encode($data) . "\n" . json_encode($response) . "\n" . json_encode($http_code) . "\n" . json_encode($response_body) . "\n";
            $file_handle = fopen($file_path, 'a');
            if ($file_handle) {
                fwrite($file_handle, $data_to_append);
                fclose($file_handle);
            }


            if ($custom_request === 'POST') {
                if (($http_code === 200) || ($http_code === 400 && $response_body === "Domain already exists")) {
                    return true;
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

        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}