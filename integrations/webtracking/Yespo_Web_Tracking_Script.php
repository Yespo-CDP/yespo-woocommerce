<?php

namespace Yespo\Integrations\Webtracking;

use Exception;

class Yespo_Web_Tracking_Script
{
    const ADD_DOMAIN_URL = "https://yespo.io/api/v1/site/domains";
    const SCRIPT_URL = "https://yespo.io/api/v1/site/script";
    const METHOD_POST = "POST";
    const METHOD_GET = "GET";
    const WEBTRACKING_LABEL_400 = 'yespo_webtracking_label_400';
    const WEBTRACKING_LABEL_500 = 'yespo_webtracking_label_500';
    const WEBTRACKING_FORM_500 = 'yespo_webtracking_form_500';
    private $options;
    private $cur_time;

    public function __construct(){
        $this->options = get_option('yespo_options');
        $this->cur_time = current_time('mysql', true);
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
        if(!$this->is_script_in_options()){
            $this->add_label_domain_options('true');

            $response = $this->add_script_to_options();
            $this->add_tenant_id_to_options();

            if($response === 400 || $response === 404 || $response === 500) return $response;
            else {
                $this->remove_form_500();
                $this->remove_label_500();
                return true;
            }
        }
        return false;
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
            $script = json_decode($this->get_tracking_script(), true);
            if($script['code'] === 400 || $script['code'] === 404 || $script['code'] === 500) {
                Yespo_Logging_Remote::get_site_script_error($script['code'], $script['response_body'], $script['code']);
                
                return $script['code'];
            }

            if ($script && !empty($script['response_body'])) {
                Yespo_Logging_Remote::get_site_script_success($script['response_body'], $script['code']);
                $this->options['yespo_tracking_script'] = wp_json_encode($script['response_body']);

                // Перевіряємо результат update_option
                if (update_option('yespo_options', $this->options)) {
                    Yespo_Logging_Remote::add_site_script_html_success();
                    return true;
                } else {
                    Yespo_Logging_Remote::add_site_script_html_error($script['code']);
                    return false;
                }
            }

        }
        return false;
    }

    public function add_tenant_id_to_options(){
        if (!$this->is_tenant_in_options()) {
            $response = $this->send_domain_to_yespo();

            if (!empty($response)) {
                $data = json_decode($response, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($data["response_body"]) && !empty($data["response_body"]['siteId'])) {
                    $tenantId = $data["response_body"]['siteId'];

                    (new Yespo_Logger())->write_to_file('Response post curl', $tenantId, 'got tenantId');

                    $this->options['yespo_tenant_id'] = $tenantId;
                    update_option('yespo_options', $this->options);

                    Yespo_Logging_Remote::add_site_domain_success($data["request_data"], $data["response_body"], $data["code"]);
                } else {
                    $error_msg = json_last_error() !== JSON_ERROR_NONE
                        ? 'JSON decode error: ' . json_last_error_msg()
                        : 'Missing or invalid siteId';
                    (new Yespo_Logger())->write_to_file('Response post curl', $error_msg, 'error');

                    Yespo_Logging_Remote::add_site_domain_error($error_msg, $data["request_data"], $data["response_body"], $data["code"]);
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

                $code = wp_remote_retrieve_response_code($response);
                $body = wp_remote_retrieve_body($response);

                $decoded_body = json_decode($body, true);

                return wp_json_encode([
                    'request_data' => $data,
                    'response_body' => $decoded_body ?: $body,
                    'code' => $code,
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            }

        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }


    /*** 500 error dealing ***/
    public function check_exist_label_500() {
        if(!$this->is_script_in_options()) {

            $label_time = $this->get_label_500();
            $current_timestamp = current_time('timestamp', true);

            if (is_string($label_time) && !$this->get_form_500()) {
                $label_timestamp = strtotime($label_time);

                if ($label_timestamp !== false && $current_timestamp < ($label_timestamp + 300) && !$this->get_form_500()) {

                    $response = $this->make_tracking_script();
                    (new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('web tracking script', 'inside check_exist_label_500', json_encode($response));

                    if ($response > 199 && $response < 300){
                        $this->remove_label_400();
                        $this->remove_form_500();
                        $this->remove_label_500();

                        return 200;
                    }

                } else if ($label_timestamp !== false && $current_timestamp > ($label_timestamp + 300) && !$this->get_form_500()) {
                    (new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file('web tracking script', 'inside check_exist_label_500', 'other response than 200');

                    $this->remove_label_500();
                    $this->add_form_500();

                    return false;
                }

                return 500;
            }
            return false;
        }
    }

    public function add_label_400() {
        if(!$this->get_label_400()) {
            $this->options[self::WEBTRACKING_LABEL_400] = true;
            update_option('yespo_options', $this->options);
        }
    }
    public function add_label_500() {
        if(!$this->get_label_500()) {
            $this->options[self::WEBTRACKING_LABEL_500] = $this->cur_time;
            update_option('yespo_options', $this->options);
        }
    }

    public function add_form_500() {
        if(!$this->get_form_500()) {
            $this->options[self::WEBTRACKING_FORM_500] = 1;
            update_option('yespo_options', $this->options);
        }
    }

    public function get_label_400(){
        if (isset($this->options[self::WEBTRACKING_LABEL_400])) return $this->options[self::WEBTRACKING_LABEL_400];
        return false;
    }
    public function get_label_500(){
        if (isset($this->options[self::WEBTRACKING_LABEL_500])) return $this->options[self::WEBTRACKING_LABEL_500];
        return false;
    }

    public function get_form_500(){
        if (isset($this->options[self::WEBTRACKING_FORM_500])) return true;
        return false;
    }

    public function remove_label_400(){
        if (isset($this->options[self::WEBTRACKING_LABEL_400])) {
            unset($this->options[self::WEBTRACKING_LABEL_400]);
            update_option('yespo_options', $this->options);
        }
    }

    public function remove_label_500(){
        if (isset($this->options[self::WEBTRACKING_LABEL_500])) {
            unset($this->options[self::WEBTRACKING_LABEL_500]);
            update_option('yespo_options', $this->options);
        }
    }

    public function remove_form_500(){
        if (isset($this->options[self::WEBTRACKING_FORM_500])) {
            unset($this->options[self::WEBTRACKING_FORM_500]);
            update_option('yespo_options', $this->options);
        }
    }


}