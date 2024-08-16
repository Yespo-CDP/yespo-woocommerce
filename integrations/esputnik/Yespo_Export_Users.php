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
                'total' => $this->get_users_export_count(),
                'exported' => 0,
                'status' => 'active'
            ];
            if($data['total'] > 0) {
                $result = $this->wpdb->insert($this->table_name, $data);

                if ($result !== false) return true;
                else return false;
            }
        }
        else return false;
    }

    //after getting result bulk users export
    public function start_active_bulk_export_users() {
        $status = $this->get_user_export_status_processed('active');
        $queue = $this->get_yespo_queue_statuses();
        $error = Yespo_Errors::get_error_entry();
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
                    } else if($response){
                        $this->esputnikContact->add_bulk_esputnik_id_to_userprofile($usersForExport, 'true');
                        if($response == 400) Yespo_Errors::error_400($usersForExport, 'users');
                        if(isset($response) && $this->check_queue_items_for_session($response)) $this->update_entry_yespo_queue($response, "FINISHED", "FINISHED");
                        $live_exported += count($usersForExport);
                        if(count($usersForExport) > 0 ) $this->set_exported_user_id(end($usersForExport));
                    }
                }

                $error = Yespo_Errors::get_error_entry();

            } while ( ($endTime - $startTime) <= $this->export_time && $export_quantity < 3 && $error == null);

            if(($total <= $exported + $live_exported) || $this->get_users_export_count() < 1){
                $current_status = 'completed';
                $exported = $total;
            } else $exported += $live_exported;

            $this->update_table_data($status->id, $exported, $current_status);

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
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM $this->table_name WHERE export_type = %s ORDER BY id DESC LIMIT 1",
                'users'
            )
        );
    }
    private function get_user_export_status_processed($action){
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM $this->table_name WHERE export_type = %s AND status = %s ORDER BY id DESC LIMIT 1",
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
        $capabilities_meta_key = $this->wpdb->prefix . 'capabilities';
        return $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->wpdb->usermeta} WHERE meta_key = %s AND meta_value LIKE %s",
                $capabilities_meta_key,
                '%"customer"%'
            )
        );
    }
    public function get_users_export_count(){
        $capabilities_meta_key = $this->wpdb->prefix . 'capabilities';

        return $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->wpdb->usermeta} um
                WHERE um.meta_key = %s
                AND um.meta_value LIKE %s
                AND NOT EXISTS (
                    SELECT 1 FROM {$this->wpdb->usermeta} um2
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

        $query = $this->wpdb->prepare("
            SELECT u.ID 
            FROM {$this->wpdb->users} u
            INNER JOIN {$this->wpdb->usermeta} um ON u.ID = um.user_id
            WHERE um.meta_key = '{$this->wpdb->prefix}capabilities'
              AND um.meta_value LIKE %s
              AND u.ID > %d
              AND u.ID NOT IN (
                SELECT user_id FROM {$this->wpdb->usermeta} WHERE meta_key = %s AND meta_value != ''
              )
            ORDER BY u.user_registered ASC
            LIMIT %d
        ", '%' . $this->wpdb->esc_like(self::CUSTOMER) . '%', $this->id_more_then, $this->meta_key, $this->number_for_export);

        return $this->wpdb->get_col($query);
    }

    /**
     * entry to yespo queue
     **/
    public function add_entry_yespo_queue($session_id){
        $data = [
            'session_id' => sanitize_text_field($session_id),
            'export_status' => 'IMPORTING',
            'local_status' => ''
        ];
        return $this->wpdb->insert($this->table_yespo_queue, $data);
    }
    public function update_entry_yespo_queue($session_id, $export_status = '', $local_status = '') {
        $data = [];
        if(!empty($export_status)) $data['export_status'] = sanitize_text_field($export_status);
        if(!empty($local_status)) $data['local_status'] = sanitize_text_field($local_status);
        $where = ['session_id' => $session_id];

        return $this->wpdb->update($this->table_yespo_queue, $data, $where);
    }
    public function get_yespo_queue_statuses() {
        $result = $this->wpdb->get_row(
            "SELECT export_status, local_status 
        FROM {$this->table_yespo_queue} 
        ORDER BY id DESC 
        LIMIT 1",
            OBJECT
        );

        return $result;
    }

    /**
     * entry to yespo queue items
     **/
    public function add_entry_queue_items($user_id){
        $data = [
            'session_id' =>'',
            'contact_id' => sanitize_text_field($user_id),
            'yespo_id' =>''
        ];
        return $this->wpdb->insert($this->table_yespo_queue_items, $data);
    }
    public function update_entry_queue_items($session_id, $user_id, $yespo_id = null) {
        $data = [
            'session_id' => sanitize_text_field($session_id),
            'yespo_id' => sanitize_text_field($yespo_id)
        ];
        $where = ['contact_id' => $user_id];

        return $this->wpdb->update($this->table_yespo_queue_items, $data, $where);
    }
    public function check_queue_items_for_session($session_id) {
        $count = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) 
                FROM {$this->table_yespo_queue_items} 
                WHERE session_id = %s 
                AND (yespo_id IS NULL OR yespo_id = '')",
                $session_id
            )
        );
        return $count == 0;
    }

    private function update_table_data($id, $exported, $status, $code = null){
        return $this->wpdb->update(
            $this->table_name,
            array('exported' => $exported, 'status' => $status, 'code' => $code),
            array('id' => $id),
            array('%d', '%s', '%s'),
            array('%d')
        );
    }
    private function update_table_total($id, $total){
        return $this->wpdb->update(
            $this->table_name,
            array('total' => $total),
            array('id' => $id),
            array('%d'),
            array('%d')
        );
    }
    private function get_users_total_args(){
        return [
            'role__in'    => [self::CUSTOMER],
            'orderby' => 'registered',
            'order'   => 'DESC',
        ];
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
        $query = $this->wpdb->prepare(
            "SELECT COUNT(*) FROM $this->table_yespo_queue WHERE export_status = %s",
            'IMPORTING'
        );

        $count = $this->wpdb->get_var($query);
        if ($count > 0) return true;
        else return false;
    }

    private function get_exported_user_id(){
        if ( get_option( 'yespo_options' ) !== false ) {
            $options = get_option('yespo_options', array());
            $yespo_api_key = 0;
            if (isset($options['highest_exported_user'])) $yespo_api_key = intval($options['highest_exported_user']);

            return $yespo_api_key;
        }
        return 0;
    }

    private function set_exported_user_id($user){
        if ( get_option( 'yespo_options' ) !== false ) {
            $options = get_option('yespo_options', array());
            $options['highest_exported_user'] = intval($user);
            update_option('yespo_options', $options);
        }
    }

}