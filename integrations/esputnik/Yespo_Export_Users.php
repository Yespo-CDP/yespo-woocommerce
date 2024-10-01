<?php

namespace Yespo\Integrations\Esputnik;

class Yespo_Export_Users
{
    const CUSTOMER = 'customer';
    const SUBSCRIBER = 'subscriber';
    private $number_for_export = 2000;
    private $export_time = 7.5;
    private $table_name;
    private $table_yespo_queue;
    private $table_yespo_queue_items;
    private $meta_key;
    private $wpdb;
    private $esputnikContact;
    private $id_more_then;

    public function __construct(){
        global $wpdb;
        $this->esputnikContact = new Yespo_Contact();
        $this->meta_key = $this->esputnikContact->get_meta_key();
        $this->wpdb = $wpdb;
        $this->table_name = $this->wpdb->prefix . 'yespo_export_status_log';
        $this->table_yespo_queue = $this->wpdb->prefix . 'yespo_queue';
        $this->table_yespo_queue_items = $this->wpdb->prefix . 'yespo_queue_items';
        $this->id_more_then = $this->get_exported_user_id();
    }

    public function add_users_export_task(){
        $status = $this->get_user_export_status_processed('active');
        if(empty($status)){
            $data = [
                'export_type' => 'users',
                'total' => intval( $this->get_users_export_count() ),
                'exported' => 0,
                'status' => 'active'
            ];
            if($data['total'] > 0) {
                $uncompleted_orders = $this->get_last_order_entry_not_completed();
                if(!$uncompleted_orders) {
                    $result = $this->insert_export_users_data($data);

                    if ($result !== false) return true;
                    else return false;
                }
            }
        }
        else return false;
    }

    //after getting result bulk users export
    public function start_active_bulk_export_users() {
        $status = $this->get_user_export_status_processed('active');
        $error = Yespo_Errors::get_error_entry();
        //$this->update_after_activation();

        if (
            !empty($status) &&
            $status->status == 'active' &&
            $error == null &&
            $this->check_sessios_importing() !== true
        ){
            $startTime = microtime(true);
            $total = intval($status->total);
            $exported = intval($status->exported);
            $current_status = $status->status;
            $live_exported = 0;
            $export_quantity = 0;

            do {
                $export_quantity++;
                $endTime = microtime(true);

                $usersForExport = $this->get_bulk_users_object();

                if($usersForExport && count($usersForExport) > 0) {
                    $response = $this->esputnikContact->export_bulk_users(Yespo_Contact_Mapping::create_bulk_export_array($usersForExport));
                    $endTime = microtime(true);

                    if ($response == 429 || $response == 500) {
                        $this->update_entry_yespo_queue($response, "FINISHED", "FINISHED");
                        Yespo_Errors::set_error_entry($response);
                    } else if($response == 'blocked' || strpos($response, 'Connection refused') !== false){
                        $this->update_entry_yespo_queue($response, "FINISHED", "FINISHED");
                        $http_code = '0';
                    } else if($response){
                        $last_element = end($usersForExport);
                        $this->esputnikContact->add_bulk_esputnik_id_to_userprofile($usersForExport, 'true');
                        if($response == 400){
                            Yespo_Errors::error_400($usersForExport, 'users');
                        }
                        if(isset($response) && $this->check_queue_items_for_session($response)) $this->update_entry_yespo_queue($response, "FINISHED", "FINISHED");
                        $live_exported += count($usersForExport);
                        if(count($usersForExport) > 0 ) $this->set_exported_user_id($last_element);
                        $http_code = 200;
                    }
                }

                $error = Yespo_Errors::get_error_entry();

            } while ( ($endTime - $startTime) <= $this->export_time && $export_quantity < 3 && $error == null);

            if(($total <= $exported + $live_exported) || $this->get_users_export_count() < 1){
                $current_status = 'completed';
                $exported = $total;
            } else $exported += $live_exported;

            $this->update_table_data($status->id, $exported, $current_status, $http_code);

        } else {
            $status = $this->get_user_export_status();
            if(!empty($status) && $status->status === 'completed' && $status->code === null){
                $this->update_table_data($status->id, intval($status->total), $status->status);
            }
        }
    }

    public function get_final_users_exported(){
        $status = $this->get_user_export_status();
        return $this->update_table_data($status->id, intval($status->total), $status->status, '200');
    }

    public function get_process_users_exported(){
        return $this->get_user_export_status();
    }

    public function stop_export_users(){
        $status = $this->get_user_export_status_processed('active');
        if(!empty($status) && $status->status == 'active'){
            $this->update_table_data($status->id, intval($status->exported), 'stopped', '200');
            return $status;
        }
    }
    public function check_user_for_stopped(){
        $status = $this->get_user_export_status_processed('stopped');
        if($status) return true;
        return false;
    }
    public function resume_export_users(){
        $status = $this->get_user_export_status_processed('stopped');
        if(!empty($status) && $status->status == 'stopped'){
            $this->update_table_data($status->id, intval($status->exported), 'active', '200');
            return $status;
        }
    }

    public function error_export_users($code){
        $status = $this->get_user_export_status_processed('active');
        if(!empty($status) && $status->status == 'active'){
            $this->update_table_data($status->id, intval($status->exported), 'error', $code);
            return $status;
        }
    }
    public function check_user_for_error(){
        $status = $this->get_user_export_status_processed('error');
        if($status) return true;
        return false;
    }

    public function update_after_activation(){
        $user = $this->get_user_export_status_processed('active');
        if(empty($user)) $user = $this->get_user_export_status_processed('stopped');
        if(!empty($user) && ($user->status == 'stopped' || $user->status == 'active') ){
            $exportEntry = intval($user->total) - intval($user->exported);
            $export = $this->get_users_export_count();
            if($exportEntry != $export){
                $newTotal = intval($user->total) + ($export - $exportEntry);
                $this->update_table_total($user->id, $newTotal);
            }
        }
    }

    public function get_users_bulk_export(){
        return Yespo_Contact_Mapping::create_bulk_export_array(
            $this->get_users_object($this->get_bulk_users_export_args())
        );
    }

    private function get_user_export_status(){
        global $wpdb;
        $table_name = esc_sql($this->table_name);

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE export_type = %s ORDER BY id DESC LIMIT 1",
                'users'
            )
        );
    }
    private function get_user_export_status_processed($action){
        global $wpdb;
        $table_name = esc_sql($this->table_name);

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE export_type = %s AND status = %s ORDER BY id DESC LIMIT 1",
                'users',
                $action
            )
        );
    }
    public function export_users_to_esputnik(){
        $users = $this->get_users_object($this->get_users_export_args());
        if(count($users) > 0 && isset($users[0])){
            return (new Yespo_Contact())-> create_on_yespo(
                (get_user_by('id', $users[0]))->user_email,
                $users[0]
            );
        }
    }

    public function get_users_total_count(){
        global $wpdb;
        $capabilities_meta_key = esc_sql($this->wpdb->prefix) . 'capabilities';

        return $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->wpdb->usermeta} WHERE meta_key = %s AND meta_value LIKE %s",
                $capabilities_meta_key,
                '%"customer"%'
            )
        );
    }
    public function get_users_export_count(){
        global $wpdb;
        $capabilities_meta_key = esc_sql($this->wpdb->prefix) . 'capabilities';

        return $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->usermeta} um
                WHERE um.meta_key = %s
                AND um.meta_value LIKE %s
                AND NOT EXISTS (
                    SELECT 1 FROM {$wpdb->usermeta} um2
                    WHERE um2.user_id = um.user_id
                    AND um2.meta_key = %s
                )",
                $capabilities_meta_key,
                '%\"customer\"%',
                $this->esputnikContact->get_meta_key()
            )
        );
    }
    public function get_users_object($args){
        return get_users($args);
    }


    public function get_bulk_users_object(){
        global $wpdb;

        return $wpdb->get_col(
            $wpdb->prepare("
                    SELECT u.ID 
                    FROM {$wpdb->users} u
                    INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
                    WHERE um.meta_key = '{$wpdb->prefix}capabilities'
                      AND um.meta_value LIKE %s
                      AND u.ID > %d
                      AND u.ID NOT IN (
                        SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value != ''
                      )
                    ORDER BY u.user_registered ASC
                    LIMIT %d
                ",
                '%' . $wpdb->esc_like(self::CUSTOMER) . '%',
                $this->id_more_then,
                $this->meta_key,
                $this->number_for_export
            )
        );



    }

    /**
     * entry to yespo queue
     **/
    public function add_entry_yespo_queue($session_id){
        global $wpdb;

        $table_yespo_queue = esc_sql($this->table_yespo_queue);
        $session_id = sanitize_text_field($session_id);

        return $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$table_yespo_queue} (session_id, export_status, local_status) VALUES (%s, %s, %s)",
                $session_id,
                'IMPORTING',
                ''
            )
        );
    }

    public function update_entry_yespo_queue($session_id, $export_status = '', $local_status = '') {
        global $wpdb;

        $table_yespo_queue = esc_sql($this->table_yespo_queue);
        $data = [];
        $values = [];

        if (!empty($export_status)) {
            $data[] = 'export_status = %s';
            $values[] = sanitize_text_field($export_status);
        }

        if (!empty($local_status)) {
            $data[] = 'local_status = %s';
            $values[] = sanitize_text_field($local_status);
        }

        if (empty($data)) {
            return false;
        }

        $where_clause = 'session_id = %s';
        $values[] = sanitize_text_field($session_id);

        $set_clause = implode(', ', $data);

        return $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$table_yespo_queue} SET {$set_clause} WHERE {$where_clause}",
                ...$values
            )
        );

    }

    /**
     * entry to yespo queue items
     **/
    public function add_entry_queue_items($user_id){
        global $wpdb;
        $table_yespo_queue_items = esc_sql($this->table_yespo_queue_items);
        $contact_id = sanitize_text_field($user_id);

        return $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$table_yespo_queue_items} (session_id, contact_id, yespo_id) VALUES (%s, %s, %s)",
                '', $contact_id, ''
            )
        );
    }

    public function update_entry_queue_items($session_id, $user_id, $yespo_id = null) {
        global $wpdb;

        $table_yespo_queue_items = esc_sql($this->table_yespo_queue_items);
        $session_id = sanitize_text_field($session_id);
        $yespo_id = sanitize_text_field($yespo_id);
        $contact_id = sanitize_text_field($user_id);

        $data = [];
        $values = [];

        if (!empty($session_id)) {
            $data[] = 'session_id = %s';
            $values[] = $session_id;
        }

        if (!empty($yespo_id)) {
            $data[] = 'yespo_id = %s';
            $values[] = $yespo_id;
        }

        if (empty($data)) {
            return false;
        }

        $where_clause = 'contact_id = %s';
        $values[] = $contact_id;

        $set_clause = implode(', ', $data);

        return $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$table_yespo_queue_items} SET {$set_clause} WHERE {$where_clause}",
                ...$values
            )
        );

    }

    public function check_queue_items_for_session($session_id) {
        global $wpdb;
        $table_yespo_queue_items = esc_sql($this->table_yespo_queue_items);

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) 
                FROM {$table_yespo_queue_items} 
                WHERE session_id = %s 
                AND (yespo_id IS NULL OR yespo_id = '')",
                $session_id
            )
        );
        return $count == 0;
    }

    private function update_table_data($id, $exported, $status, $code = null){
        global $wpdb;
        $table_name = esc_sql($this->table_name);

        $id = intval($id);
        $exported = intval($exported);
        $status = sanitize_text_field($status);
        $code = sanitize_text_field($code);
        $updated_at = gmdate('Y-m-d H:i:s', time());

        return $wpdb->query(
            $wpdb->prepare(
                "
                    UPDATE {$table_name} 
                    SET exported = %d, status = %s, code = %s, updated_at = %s 
                    WHERE id = %d
                ",
                $exported, $status, $code, $updated_at, $id
            )
        );
    }

    private function update_table_total($id, $total){
        global $wpdb;
        $table_name = esc_sql($this->table_name);

        return $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$table_name} SET total = %d WHERE id = %d",
                $total,
                $id
            )
        );
    }

    private function get_users_export_args(){
        return [
            'role__in'    => [self::CUSTOMER],
            'orderby' => 'registered',
            'order'   => 'ASC',
            'fields'  => 'ID',
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key'     => $this->meta_key,
                    'compare' => 'NOT EXISTS',
                ),
                array(
                    'key'     => $this->meta_key,
                    'value'   => '',
                    'compare' => '=',
                ),
            ),
        ];
    }

    private function get_bulk_users_export_args(){
        return [
            'role__in'    => [self::CUSTOMER],
            'orderby' => 'registered',
            'order'   => 'ASC',
            'fields'  => 'ID',
            'number' => $this->number_for_export,
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key'     => $this->meta_key,
                    'compare' => 'NOT EXISTS',
                ),
                array(
                    'key'     => $this->meta_key,
                    'value'   => '',
                    'compare' => '=',
                ),
            ),
        ];
    }



    public function get_active_users_bulk_export($part){
        return Yespo_Contact_Mapping::create_bulk_export_array(
            $this->get_active_users_object($this->get_active_bulk_users_export_args($part))
        );
    }

    public function get_active_users_object($args){
        return get_users($args);
    }

    private function get_active_bulk_users_export_args($page = 1){
        $number = $this->number_for_export;
        $offset = ($page - 1) * $number;

        return [
            'role__in'    => [self::CUSTOMER],
            'orderby' => 'registered',
            'order'   => 'ASC',
            'fields'  => 'ID',
            'number' => $number,
            'offset' => $offset,
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key'     => $this->meta_key,
                    'compare' => 'NOT EXISTS',
                ),
                array(
                    'key'     => $this->meta_key,
                    'value'   => '',
                    'compare' => '=',
                ),
            ),
        ];
    }

    private function check_sessios_importing(){
        global $wpdb;
        $table_yespo_queue = esc_sql($this->table_yespo_queue);

        $count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_yespo_queue} WHERE export_status = %s",
                'IMPORTING'
            )
        );

        if ($count > 0) return true;
        else return false;
    }

    private function get_exported_user_id(){
        if ( get_option( 'yespo_options' ) !== false ) {
            $options = get_option('yespo_options', array());
            $yespo_api_key = 0;
            if (isset($options['yespo_highest_exported_user'])) $yespo_api_key = intval($options['yespo_highest_exported_user']);

            return $yespo_api_key;
        }
        return 0;
    }

    private function set_exported_user_id($user){
        if ( get_option( 'yespo_options' ) !== false ) {
            $options = get_option('yespo_options', array());
            $options['yespo_highest_exported_user'] = intval($user);
            update_option('yespo_options', $options);
        }
    }

    private function get_last_order_entry_not_completed(){
        global $wpdb;
        $table_name = esc_sql($this->table_name);

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE export_type = %s AND status != %s ORDER BY id DESC LIMIT 1",
                'orders',
                'completed'
            )
        );
    }

    private function insert_export_users_data($data) {
        global $wpdb;
        $table_name = esc_sql($this->table_name);

        $data['export_type'] = sanitize_text_field($data['export_type']);
        $data['total'] = absint($data['total']);
        $data['exported'] = absint($data['exported']);
        $data['status'] = sanitize_text_field($data['status']);

        return $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$table_name} (export_type, total, exported, status)
        VALUES (%s, %d, %d, %s)",
                $data['export_type'],
                $data['total'],
                $data['exported'],
                $data['status']
            )
        );

    }

}