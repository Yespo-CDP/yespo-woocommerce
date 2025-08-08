<?php

namespace Yespo\Integrations\Webpush;

use Yespo\Integrations\Webtracking\Yespo_Logger;

class Yespo_Web_Push
{
    const SERVICE_WORKER_NAME = 'push-yespo-sw.js';
    const YESPO_UPLOADS_DIRECTORY = '/yespo-cdp/';
    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';
    const POST_WEBPUSH_YESPO_URL = 'https://yespo.io/api/v1/site/webpush/domains';
    const GET_WEBPUSH_YESPO_URL = 'https://yespo.io/api/v1/site/webpush/script?domain=';
    const WEBPUSH_OPTION_NAME = 'yespo_webpush_script';
    private $options;


    public function __construct(){
        $this->options = get_option('yespo_options');
    }

    public function start(){

        $response_post = $this->send_post_data();

        //loging debug
        (new Yespo_Logger())->write_to_file('POST', json_encode($this->get_json()), $response_post);


        if ($response_post < 200 || $response_post >= 300) return;

        $response_get = $this->send_get_data();

        //loging debug
        (new Yespo_Logger())->write_to_file('GET', json_encode($this->get_full_site_url()), $response_get);

        if (!is_string($response_get) || empty($response_get)) return;

        $data = json_decode($response_get, true);

        if (json_last_error() !== JSON_ERROR_NONE) return;
        if (!is_array($data) || !isset($data['script']) || !isset($data['serviceWorker'])) return;

        $this->add_script_to_options(json_encode($data['script']));
        $this->write_script_to_file($data['serviceWorker']);

    }

    public function check_webpush_installation(){
        if($this->check_script_file() && $this->is_script_in_options()) return true;
        return false;
    }

    public function check_script_file()
    {
        $wp_upload_dir = wp_upload_dir();
        if (empty($wp_upload_dir['basedir'])) return false;

        $file_path = $wp_upload_dir['basedir'] . self::YESPO_UPLOADS_DIRECTORY . self::SERVICE_WORKER_NAME;

        if (!file_exists($file_path)) {
            return false;
        }

        $file_content = file_get_contents($file_path);
        if (empty($file_content)) {
            return false;
        }

        return true;
    }

    private function send_post_data(){
        $data = $this->get_json();
        return Yespo_Web_Push_Curl_Request::curl_request(self::POST_WEBPUSH_YESPO_URL, self::METHOD_POST, $data);
    }

    private function send_get_data(){
        $domain = $this->get_full_site_url();
        return Yespo_Web_Push_Curl_Request::curl_request(self::GET_WEBPUSH_YESPO_URL, self::METHOD_GET, $domain);
    }

    private function write_script_to_file($script) {
        $wp_upload_dir = wp_upload_dir();
        if (empty($wp_upload_dir['basedir'])) return false;

        $dir_path = $wp_upload_dir['basedir'] . '/' . self::YESPO_UPLOADS_DIRECTORY;
        $file_path = $dir_path . '/' . self::SERVICE_WORKER_NAME;

        if (!is_dir($dir_path)) {
            if (!mkdir($dir_path, 0755, true)) {
                return false;
            }
        }

        if (!wp_is_writable($dir_path)) {
            return false;
        }

        if (file_put_contents($file_path, $script) !== false) {
            return true;
        } else {
            return false;
        }
    }

    public function get_relative_upload_path($subdir = '') {
        $upload_dir = wp_upload_dir();
        $baseurl_path = wp_parse_url($upload_dir['baseurl'], PHP_URL_PATH); // /wp-content/uploads

        if (!empty($subdir)) {
            $baseurl_path = trailingslashit($baseurl_path) . trim($subdir, '/') . '/';
        } else {
            $baseurl_path = trailingslashit($baseurl_path);
        }

        return $baseurl_path;
    }

    public function get_json(){
        $path = $this->get_relative_upload_path(self::YESPO_UPLOADS_DIRECTORY);
        return [
            "domains" => [
                [
                    "domain" => $this->get_full_site_url(),
                    "serviceWorkerName" => self::SERVICE_WORKER_NAME,
                    "serviceWorkerPath" => $path,
                    "serviceWorkerScope" => $path
                ]
            ]
        ];

    }

    private function get_full_site_url() {
        return home_url();
    }

    public function get_script_from_options(){
        if($this->is_script_in_options()) {
            return json_decode( $this->options[self::WEBPUSH_OPTION_NAME] );
        }
        return  false;
    }

    public function add_script_to_options($script){
        if(!$this->is_script_in_options()) {
            if($script) {
                $this->options[self::WEBPUSH_OPTION_NAME] = $script;
                update_option('yespo_options', $this->options);
            }
        }
    }

    public function is_script_in_options(){
        if (isset($this->options[self::WEBPUSH_OPTION_NAME])) return true;
        return false;
    }

}