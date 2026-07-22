<?php

namespace Yespo\Integrations\Webtracking;

use Exception;

class Yespo_Web_Tracking_Script_Configuration
{
    const YESPO_TOKEN_URL = 'https://yespo.io/api/v1/auth/contact/token';
    private $option_name = 'yespo_options';
    private $tracking_script_key = 'yespo_tracking_script';
    private $simple_init = "eS('init')";
    private $site_language;

    public function __construct() {
        $locale = get_locale();
        $this->site_language = substr($locale, 0, 2);

        $this->advanced_init = "eS('init', {APP_INBOX: true}, {getAuthTokenCallback: () => new YespoTracker().getAuthCallback(), language: '" . $this->site_language . "' })";
        $this->advanced_pattern = "/eS\\('init',\\s*\\{APP_INBOX:\\s*true\\},\\s*\\{getAuthTokenCallback:\\s*\\(\\)\\s*=>\\s*new\\s+YespoTracker\\(\\)\\.getAuthCallback\\(\\),\\s*language:\\s*'" . $this->site_language . "'\\s*\\}\\)/";
    }
    public function get_auth_token(){
        $response = $this->send_curl_request(
            $this->generate_json(
                $this->get_user_id(),
                $this->get_user_email(),
                $this->get_user_phone()
            )
        );

        if (is_array($response)) {
            $decoded = $response;
        } else {
            $decoded = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $response;
            }
        }

        if (
            isset($decoded['code']) && (int)$decoded['code'] === 200 &&
            isset($decoded['response_body']['token'])
        ) {
            return $decoded['response_body']['token'];
        }

        return $decoded;
    }

    public function toggle_app_inbox() {

        if ($this->get_app_inbox_status()) {
            $this->disable_app_inbox();
        } else $this->enable_app_inbox();

        return true;
    }

    public function enable_app_inbox() {

        $script = $this->get_tracking_script();

        if ($this->get_app_inbox_status()) {
            return true;
        }

        $updated_script = str_replace(
            $this->simple_init,
            $this->getAdvancedInit(),
            $script
        );

        $result = $this->update_tracking_script($updated_script);
        if ($result) Yespo_Logging_Remote::set_app_inbox_enable();

        return $result;

    }

    public function disable_app_inbox() {

        $script = $this->get_tracking_script();

        if (!$this->get_app_inbox_status()) {
            return true;
        }

        $updated_script = preg_replace(
            $this->getAdvancedPattern(),
            $this->simple_init,
            $script
        );

        $result = $this->update_tracking_script($updated_script);
        if ($result) Yespo_Logging_Remote::set_app_inbox_disable();

        return $result;
    }

    public function get_app_inbox_status(){

        $script = $this->get_tracking_script();

        return strpos($script, 'APP_INBOX: true') !== false;
    }

    private function getAdvancedInit() {
        return $this->advanced_init;
    }

    private function getAdvancedPattern() {
        return $this->advanced_pattern;
    }

    private function get_options() {

        $options = get_option($this->option_name, []);

        return is_array($options) ? $options : [];
    }

    private function get_tracking_script() {

        $options = $this->get_options();

        return isset($options[$this->tracking_script_key])
            ? (string) $options[$this->tracking_script_key]
            : '';
    }

    private function update_tracking_script(string $script) {

        $options = $this->get_options();

        $options[$this->tracking_script_key] = $script;

        return update_option($this->option_name, $options);
    }

    private function get_user_id(){
        $current_user = wp_get_current_user();

        return $current_user->ID ? (string)$current_user->ID : '';
    }

    private function get_user_email(){
        $current_user = wp_get_current_user();

        return $current_user->ID ? sanitize_email($current_user->user_email) : '';
    }

    private function get_user_phone(){
        $current_user = wp_get_current_user();
        $phone = '';

        if ($current_user->ID) {
            $phone = get_user_meta($current_user->ID, 'billing_phone', true);
            if (empty($phone)) {
                $phone = get_user_meta($current_user->ID, 'shipping_phone', true);
            }
            $phone = !empty($phone) ? sanitize_text_field($phone) : '';
        }

        return $phone;
    }

    private function generate_json($externalCustomerId, $email, $phone )
    {
        return [
            'email'   => $email,
            'phone'  => $phone,
            'externalCustomerId' => $externalCustomerId
        ];
    }

    private function send_curl_request($json)
    {
        $auth_data = get_option('yespo_options');

        try {
            $args = [
                'method'  => "POST",
                'timeout' => 60,
                'headers' => [
                    'Accept'        => 'application/json; charset=UTF-8',
                    'Authorization' => 'Basic ' . base64_encode(':' . $auth_data['yespo_api_key']),
                    'Content-Type'  => 'application/json',
                ],
                'body'    => !empty($json) ? wp_json_encode($json) : '',
            ];

            $response = wp_remote_request(self::YESPO_TOKEN_URL, $args);


            $code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);

            $decoded_body = json_decode($body, true);

            $result = wp_json_encode([
                'response_body' => $decoded_body ?: $body,
                'code' => $code,
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);


            //(new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file($code, $result, 'sent data to remote logs');

            return $result;

        } catch (Exception $e) {
            (new \Yespo\Integrations\Webtracking\Yespo_Logger())->write_to_file($code, wp_json_encode($e), 'sent data to remote logs error');
            return 'Error: ' . $e->getMessage();
        }

    }

}