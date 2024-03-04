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

    /** if not exist table this code creates new table in database **/
    private function create_table(){
        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = "CREATE TABLE $this->table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id varchar(255) NOT NULL,
            contact_id varchar(255) NOT NULL,
            action varchar(255) NOT NULL,
            log_date datetime NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        $this->wpdb->query($this->wpdb->prepare($sql));
    }
}