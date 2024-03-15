<?php

namespace Yespo\Integrations\Esputnik;

use Exception;

class Esputnik_Logging_Data
{
    private $wpdb;
    private $table_name;

    public function __construct(){
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $this->wpdb->prefix . 'yespo_contact_log';
    }
    public function create(string $user_id, string $contact_id, string $action){
        if ($this->wpdb->get_var("SHOW TABLES LIKE '$this->table_name'") != $this->table_name) $this->create_table();
        return $this->create_log_entry($user_id, $contact_id, $action); //if success returns 1
    }

    /** create new entry in database **/
    private function create_log_entry(string $user_id, string $contact_id, string $action){
        $data = array(
            'user_id' => sanitize_text_field($user_id),
            'contact_id' => sanitize_text_field($contact_id),
            'action' => sanitize_text_field($action),
            'log_date' => current_time('mysql', 1)
        );

        try {
            $response = $this->wpdb->insert(
                $this->table_name,
                $data,
                array('%s', '%s', '%s', '%s')
            );
            return $response;
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

}