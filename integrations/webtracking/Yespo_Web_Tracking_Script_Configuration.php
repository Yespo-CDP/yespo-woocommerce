<?php

namespace Yespo\Integrations\Webtracking;

class Yespo_Web_Tracking_Script_Configuration
{
    private $option_name = 'yespo_options';
    private $tracking_script_key = 'yespo_tracking_script';
    private $simple_init = "eS('init')";
    private $advanced_init = "eS('init', {APP_INBOX: true}, {getAuthTokenCallback: () => yourImplementationOfAuthCallback(), language: 'en', })";

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
            $this->advanced_init,
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
            "/eS\\('init',\\s*\\{APP_INBOX:\\s*true\\},\\s*\\{getAuthTokenCallback:\\s*\\(\\)\\s*=>\\s*yourImplementationOfAuthCallback\\(\\),\\s*language:\\s*'en',\\s*\\}\\)/",
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

}