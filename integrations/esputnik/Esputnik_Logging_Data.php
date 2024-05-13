<?php

namespace Yespo\Integrations\Esputnik;

use Exception;

class Esputnik_Logging_Data
{
    private $wpdb;
    private $table_name;
    private $table_name_order;

    public function __construct(){
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $this->wpdb->prefix . 'yespo_contact_log';
        $this->table_name_order = $this->wpdb->prefix . 'yespo_order_log';

    }
    public function create(string $user_id, string $contact_id, string $action){
        if ($this->wpdb->get_var("SHOW TABLES LIKE '$this->table_name'") === $this->table_name)
            return $this->create_log_entry_user($user_id, $contact_id, $action); //if success returns 1
    }

    public function update_contact_log($user_id, $action, $response){
        if ($this->wpdb->get_var("SHOW TABLES LIKE '$this->table_name'") === $this->table_name)
            $this->update_log_entry_user($user_id, $action, $response);
    }

    public function create_entry_order($order_id, $action = 'update'){
        if ($this->wpdb->get_var("SHOW TABLES LIKE '$this->table_name_order'") === $this->table_name_order)
            return $this->create_log_entry_order($order_id, $action); //if success returns 1
    }

    /** create new log user entry in database **/
    private function create_log_entry_user(string $user_id, string $contact_id, string $action){
        $data = array(
            'user_id' => sanitize_text_field($user_id),
            'contact_id' => sanitize_text_field($contact_id),
            'action' => sanitize_text_field($action),
            'yespo' => 1,
            'log_date' => date('Y-m-d H:i:s', time())
        );

        try {
            $response = $this->wpdb->insert(
                $this->table_name,
                $data,
                array('%s', '%s', '%s', '%s', '%s')
            );
            return $response;
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    private function update_log_entry_user($user_id, $action, $response){
        $this->wpdb->query(
            $this->wpdb->prepare(
                "UPDATE $this->table_name SET yespo = %d WHERE action = %s AND user_id = %s",
                $response,
                $action,
                $user_id
            )
        );
    }

    /** create new log order entry in database **/
    private function create_log_entry_order(string $order_id, string $action){
        if(!$this->check_presence_in_database($order_id, $action, 'completed')) {
            $data = [
                'order_id' => $order_id,
                'action' => $action,
                'status' => 'completed',
                'created_at' => current_time('mysql', 1)
            ];

            try {
                $response = $this->wpdb->insert(
                    $this->table_name_order,
                    $data,
                    array('%s', '%s', '%s', '%s')
                );
                return $response;
            } catch (Exception $e) {
                return "Error: " . $e->getMessage();
            }
        }
    }

    private function check_presence_in_database(string $order_id, string $action, string $status){
        $query = $this->wpdb->prepare(
            "SELECT COUNT(*) FROM $this->table_name_order WHERE order_id = %s AND action = %s AND status = %s",
            $order_id,
            $action,
            $status
        );
       return $this->wpdb->get_var($query);
    }

}