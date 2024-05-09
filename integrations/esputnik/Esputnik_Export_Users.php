<?php

namespace Yespo\Integrations\Esputnik;

class Esputnik_Export_Users
{
    const CUSTOMER = 'customer';
    const SUBSCRIBER = 'subscriber';
    private $number_for_export = 30;
    private $table_name;
    private $meta_key;
    private $wpdb;

    public function __construct(){
        global $wpdb;
        $this->meta_key = (new Esputnik_Contact())->get_meta_key();
        $this->wpdb = $wpdb;
        $this->table_name = $this->wpdb->prefix . 'yespo_export_status_log';
    }

    public function add_users_export_task(){
        $status = $this->get_user_export_status_active();
        if(empty($status)){
            $data = [
                'export_type' => 'users',
                'total' => $this->get_users_export_count(),
                'exported' => 0,
                'status' => 'active'
            ];
            $result = $this->wpdb->insert($this->table_name, $data);

            if ($result !== false) return true;
            else return false;
        }
        else return false;
    }

    public function start_export_users() {
        $status = $this->get_user_export_status_active();
        if(!empty($status) && $status->status == 'active'){
            $total = intval($status->total);
            $exported = intval($status->exported);
            $current_status = $status->status;
            $live_exported = 0;

            if($total - $exported < $this->number_for_export) $this->number_for_export = $total - $exported;

            for($i = 0; $i < $this->number_for_export; $i++){

                $result = $this->export_users_to_esputnik();
                if($result){
                    $live_exported += 1;
                }
            }

            if($total <= $exported + $live_exported){
                $current_status = 'completed';
                $exported = $total;
                Esputnik_Metrika::count_finish_exported();
            } else $exported += $live_exported;

            $this->update_table_data($status->id, $exported, $current_status);
        } else {
            $status = $this->get_user_export_status();
            if(!empty($status) && $status->status === 'completed' && $status->display === null){
                $this->update_table_data($status->id, intval($status->total), $status->status);
            }
        }
    }

    public function get_final_users_exported(){
        $status = $this->get_user_export_status();
        return $this->update_table_data($status->id, intval($status->total), $status->status, true);
    }

    public function get_process_users_exported(){
        return $this->get_user_export_status();
    }

    private function get_user_export_status(){
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM $this->table_name WHERE export_type = %s ORDER BY id DESC LIMIT 1",
                'users'
            )
        );
    }
    private function get_user_export_status_active(){
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM $this->table_name WHERE export_type = %s AND status = %s ORDER BY id DESC LIMIT 1",
                'users',
                'active'
            )
        );
    }
    public function export_users_to_esputnik(){
        $users = $this->get_users_object($this->get_users_export_args());
        if(count($users) > 0 && isset($users[0])){
            return (new Esputnik_Contact())-> create_on_yespo(
                (get_user_by('id', $users[0]))->user_email,
                $users[0]
            );
        }
    }

    public function get_users_total_count(){
        return count($this->get_users_object($this->get_users_total_args()));
    }
    public function get_users_export_count(){
        return count($this->get_users_object($this->get_users_export_args()));
        //return 10000;
    }
    public function get_users_object($args){
        return get_users($args);
    }
    private function update_table_data($id, $exported, $status, $display = null){
        return $this->wpdb->update(
            $this->table_name,
            array('exported' => $exported, 'status' => $status, 'display' => $display),
            array('id' => $id),
            array('%d', '%s', '%s'),
            array('%d')
        );
    }
    private function get_users_total_args(){
        return [
            'role__in'    => [self::CUSTOMER, self::SUBSCRIBER],
            'orderby' => 'registered',
            'order'   => 'DESC',
        ];
    }
    private function get_users_export_args(){
        return [
            'role__in'    => [self::CUSTOMER, self::SUBSCRIBER],
            'orderby' => 'registered',
            'order'   => 'DESC',
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

}