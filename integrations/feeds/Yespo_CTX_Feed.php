<?php

namespace Yespo\Integrations\Feeds;

class Yespo_CTX_Feed
{
    private $plugin_name = "webappick-product-feed-for-woocommerce/woo-feed.php";
    private $option_part_name = "wf_feed";
    private $file_name = "url";

    public static function get_feed_url(){
        $ctx = new self();
        if($ctx->check_plugin_installation()){
            return $ctx->get_url_from_options();
        }
        return [];
    }

    private function get_url_from_options(){
        $options = $this->get_option_name();
        $arr = [];
        if(!empty($options) && count($options) > 0){
            foreach ($options as $option) {
                $string = get_option($option->option_name);
                if(is_string($string)) $string = unserialize($string);
                if ($string && is_array($string)) {
                    if (isset($string[$this->file_name]) && $string["status"] == 1) $arr[] = $string[$this->file_name];
                }
                unset($string);
            }
        }
        return $arr;
    }

    private function check_plugin_installation(){
        $active_plugins = get_option('active_plugins');
        foreach($active_plugins as $active){
            if($active === $this->plugin_name) return true;
        }
        return false;
    }

    private function get_option_name(){
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
                '%' . $wpdb->esc_like($this->option_part_name) . '%'
            )
        );
    }
}