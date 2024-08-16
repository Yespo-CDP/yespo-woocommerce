<?php

namespace Yespo\Integrations\Esputnik;

class Yespo_Errors
{
    const BAD_REQUEST = 'bad_request';
    const WAITING_TIME = 300;

    public static function get_mark_br(){
        return self::BAD_REQUEST;
    }
    public static function error_400($data, $type){
        if($type == 'users') self::add_label_to_users($data, self::BAD_REQUEST);
        if($type =='orders') (new Yespo_Order())->add_labels_to_orders($data);
    }

    public static function set_error_entry($error){
        if($error == 429 || $error == 500) {
            global $wpdb;

            $table_yespo_errors = $wpdb->prefix . 'yespo_errors';

            $data = array(
                'error' => $error,
                'time' => current_time('mysql')
            );

            $wpdb->insert(
                $table_yespo_errors,
                $data
            );
        }

    }

    public static function get_error_entry(){
        global $wpdb;

        $table_yespo_errors = $wpdb->prefix . 'yespo_errors';

        $time_current = current_time('mysql');
        $time_selection = date('Y-m-d H:i:s', strtotime($time_current) - self::WAITING_TIME);

        $sql = $wpdb->prepare("
            SELECT * 
            FROM $table_yespo_errors
            WHERE time >= %s
            LIMIT 1
        ", $time_selection);


        return $wpdb->get_row($sql);
    }

    public static function get_error_entry_old(){
        global $wpdb;

        $table_yespo_errors = $wpdb->prefix . 'yespo_errors';


        $time_current = current_time('mysql');
        $time_selection = date('Y-m-d H:i:s', strtotime($time_current) - self::WAITING_TIME);

        $sql = $wpdb->prepare("
            SELECT * 
            FROM $table_yespo_errors
            WHERE time < %s
            ORDER BY time DESC
            LIMIT 1
        ", $time_selection);

        return $wpdb->get_row($sql);

    }

    public static function unblock_bulk_error(){
        $exportOrders = new Yespo_Export_Orders();
        $status = $exportOrders->get_order_export_status_processed('error');
        if(!empty($status) && $status->status == 'error'){
            $exportOrders->update_table_data($status->id, intval($status->exported), 'active', 200);
            return $status;
        }
    }


    public static function add_label_to_users($users, $meta_key){
        global $wpdb;
        $values = [];
        foreach ($users as $user_id) {
            $values[] = $wpdb->prepare("(%d, %s, %s)", $user_id, $meta_key, 'true');
        }

        if (!empty($values)) {
            $values_string = implode(", ", $values);
            $query = "INSERT INTO {$wpdb->usermeta} (user_id, meta_key, meta_value) VALUES $values_string 
              ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)";

            $wpdb->query($query);
        }
    }

}