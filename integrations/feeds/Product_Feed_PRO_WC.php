<?php

namespace Yespo\Integrations\Feeds;
class Product_Feed_PRO_WC
{
    private $plugin_name = "woo-product-feed-pro/woocommerce-sea.php";
    private $option_name = "cron_projects";
    private $file_name = "external_file";

    public static function get_feed_url(){
        $pf = new self();
        if($pf->check_plugin_installation()){
            return $pf->get_url_from_options();
        }
        return [];
    }

    private function get_url_from_options(){
        $string = get_option($this->option_name);
        $arr = [];
        if ($string && is_array($string)) {
            foreach ($string as $el) {
                if (isset($el[$this->file_name]) && $el["active"] === true) $arr[] = $el[$this->file_name];
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
}