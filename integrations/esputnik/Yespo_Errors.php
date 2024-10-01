<?php

namespace Yespo\Integrations\Esputnik;

class Yespo_Errors
{
    const BAD_REQUEST = 'yespo_bad_request';
    const WAITING_TIME = 300;

    public static function get_mark_br(){
        return self::BAD_REQUEST;
    }
    public static function error_400($data, $type){
        if($type == 'users') self::add_label_to_users($data, self::BAD_REQUEST);
        if($type =='orders') (new Yespo_Order())->add_labels_to_orders($data, self::BAD_REQUEST, 'true');
    }

    public static function set_error_entry($error){
        if($error == 429 || $error == 500) {
            global $wpdb;

            $table_yespo_errors = $wpdb->prefix . 'yespo_errors';

            $error_code = intval($error);
            $current_time = current_time('mysql');

            return $wpdb->query(
                $wpdb->prepare(
                "
                    INSERT INTO {$table_yespo_errors} (error, time) 
                    VALUES (%d, %s)
                    ",
                    $error_code,
                    $current_time
                )
            );

        }

    }

    public static function get_error_entry(){
        global $wpdb;

        $table_yespo_errors = $wpdb->prefix . 'yespo_errors';

        $time_current = current_time('mysql');
        $time_selection = gmdate('Y-m-d H:i:s', strtotime($time_current) - self::WAITING_TIME);

        return $wpdb->get_row(
            $wpdb->prepare("
                    SELECT * 
                    FROM {$table_yespo_errors}
                    WHERE time >= %s
                    LIMIT 1
                ",
                $time_selection
            )
        );

    }

    public static function get_error_entry_old(){
        global $wpdb;

        $table_yespo_errors = $wpdb->prefix . 'yespo_errors';


        $time_current = current_time('mysql');
        $time_selection = gmdate('Y-m-d H:i:s', strtotime($time_current) - self::WAITING_TIME);

        return $wpdb->get_row(
            $wpdb->prepare("
                    SELECT * 
                    FROM {$table_yespo_errors}
                    WHERE time < %s
                    ORDER BY time DESC
                    LIMIT 1
                ",
                $time_selection
            )
        );

    }

    public static function unblock_bulk_error(){
        $exportOrders = new Yespo_Export_Orders();
        $status = $exportOrders->get_order_export_status_processed('error');
        if(!empty($status) && $status->status == 'error'){
            $exportOrders->update_table_data($status->id, intval($status->exported), 'active', 200);
            return $status;
        }
    }


    public static function add_label_to_users($users, $meta_key) {
        global $wpdb;

        $values = [];
        $placeholders = [];

        foreach ($users as $user_id) {
            $placeholders[] = "(%d, %s, %s)";
            $values[] = $user_id;
            $values[] = $meta_key;
            $values[] = 'true';
        }

        if (!empty($values)) {
            $placeholders_string = implode(", ", $placeholders);

            return $wpdb->query(
                $wpdb->prepare(
                    "
                    INSERT INTO {$wpdb->usermeta} (user_id, meta_key, meta_value) 
                    VALUES {$placeholders_string}
                    ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)
                    ",
                    ...$values
                )
            );
        }

        return false;
    }

}