<?php

namespace Yespo\Integrations\Esputnik;

use WP_Query;

class Yespo_Export_Orders
{
    private $period_selection = 300;
    private $period_selection_since = 300;
    private $period_selection_up = 30;
    private $number_for_export = 1000;
    private $table_name;
    private $table_yespo_queue_orders;
    private $table_posts;
    private $meta_key;
    private $wpdb;
    private $time_limit;
    private $gmt;
    private $shop_order = 'shop_order';
    private $shop_order_placehold = 'shop_order_placehold';

    private $table_yespo_curl_json;

    public function __construct(){
        global $wpdb;
        $this->meta_key = (new Yespo_Order())->get_meta_key();
        $this->wpdb = $wpdb;
        $this->table_posts = $this->wpdb->prefix . 'wc_orders';
        $this->table_name = $this->wpdb->prefix . 'yespo_export_status_log';
        $this->table_yespo_queue_orders = $this->wpdb->prefix . 'yespo_queue_orders';
        $this->time_limit = current_time('timestamp') - $this->period_selection;
        $this->gmt = time() - $this->period_selection;

        $this->table_yespo_curl_json = $wpdb->prefix . 'yespo_curl_json';
    }

    public function add_orders_export_task(){
        $status = $this->get_order_export_status_processed('active');
        if(empty($status)){
            $data = [
                'export_type' => 'orders',
                'total' => $this->get_export_orders_count(),
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

    public function start_export_orders() {
        $status = $this->get_order_export_status_processed('active');
        if(!empty($status) && $status->status == 'active'){
            $total = intval($status->total);
            $exported = intval($status->exported);
            $current_status = $status->status;
            $live_exported = 0;

            if($total - $exported < $this->number_for_export) $this->number_for_export = $total - $exported;

            for($i = 0; $i < $this->number_for_export; $i++){

                $result = $this->export_orders_to_esputnik();
                if($result){
                    $live_exported += 1;
                }
            }

            if(($total <= $exported + $live_exported) || $this->get_export_orders_count() < 1){
                $current_status = 'completed';
                $exported = $total;
            } else $exported += $live_exported;

            $this->update_table_data($status->id, $exported, $current_status);
        } else {
            $status = $this->get_order_export_status();
            if(!empty($status) && $status->status === 'completed' && $status->code === null){
                $this->update_table_data($status->id, intval($status->total), $status->status);
            }
        }
    }

    public function schedule_export_orders(){

        $orders = $this->get_latest_orders();

        if(count($orders) > 0 ){
            foreach ($orders as $order) {
                $item = wc_get_order($order);
                if ($item) {
                    (new Yespo_Order())->create_order_on_yespo($item, 'update');
                }
            }
        } else {
            $status = $this->get_order_export_status();
            if(!empty($status) && ($status->status === 'completed' || $this->get_export_orders_count() < 1) && $status->code === null){
                $this->update_table_data($status->id, intval($status->total), 'completed');
            }
        }

    }
    public function start_bulk_export_orders(){
        $status = $this->get_order_export_status_processed('active');
        $orders = $this->get_bulk_export_orders();
        if(!empty($status) && $status->status == 'active' && !$this->check_queue_items_for_session()){
            $total = intval($status->total);
            $exported = intval($status->exported);
            $current_status = $status->status;
            $live_exported = 0;
            $code = $status->code;

            if($total - $exported < $this->number_for_export) $this->number_for_export = $total - $exported;


            $export_res = (new Yespo_Order())->create_bulk_orders_on_yespo(Yespo_Order_Mapping::create_bulk_order_export_array($orders), 'update');

            if($export_res) {
                $live_exported = $export_res;
                $this->update_entry_queue_items('FINISHED');
            }

            if($total <= $exported + $live_exported){
                $current_status = 'completed';
                $exported = $total;
            } else $exported += $live_exported;

            $is_error = $this->check_orders_for_error();
            if($is_error){
                $current_status = 'error';
                $code = $is_error;
            }
            $this->update_table_data($status->id, $exported, $current_status, $code);
        } else {
            $status = $this->get_order_export_status();
            if(!empty($status) && ($status->status === 'completed' || $this->get_export_orders_count() < 1) && $status->code === null){
                $this->update_table_data($status->id, intval($status->total), $status->status);
            }
        }

    }

    public function get_final_orders_exported(){
        $status = $this->get_order_export_status();
        return $this->update_table_data($status->id, intval($status->total), $status->status, '200');
    }

    public function export_orders_to_esputnik(){
        $orders = $this->get_orders_export_esputnik();
        if(count($orders) > 0 && isset($orders[0])){
            return (new Yespo_Order())->create_order_on_yespo(
                wc_get_order($orders[0])
            );
        }
    }

    public function get_process_orders_exported(){
        return $this->get_order_export_status();
    }

    public function stop_export_orders(){
        $status = $this->get_order_export_status_processed('active');
        if(!empty($status) && $status->status == 'active'){
            $this->update_table_data($status->id, intval($status->exported), 'stopped', '200');
            return $status;
        }
    }

    public function check_orders_for_stopped(){
        $status = $this->get_order_export_status_processed('stopped');
        if($status) return true;
        return false;
    }
    public function resume_export_orders(){
        $status = $this->get_order_export_status_processed('stopped');
        if(!empty($status) && $status->status == 'stopped'){
            $this->update_table_data($status->id, intval($status->exported), 'active', '200');
            return $status;
        }
    }

    public function get_order_export_status(){
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM $this->table_name WHERE export_type = %s ORDER BY id DESC LIMIT 1",
                'orders'
            )
        );
    }

    public function error_export_orders($code){
        $status = $this->get_order_export_status_processed('active');
        if(!empty($status) && $status->status == 'active'){
            $this->update_table_data($status->id, intval($status->exported), 'error', $code);
            return $status;
        }
    }

    public function check_orders_for_error(){
        $status = $this->get_order_export_status_processed('error');
        if($status) return $status->code;
        return false;
    }

    public function get_order_export_status_processed($action){
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM $this->table_name WHERE export_type = %s AND status = %s ORDER BY id DESC LIMIT 1",
                'orders',
                $action
            )
        );
    }

    public function update_after_activation(){
        $order = $this->get_order_export_status_processed('active');
        if(empty($order)) $order = $this->get_order_export_status_processed('stopped');
        if(!empty($order) && ($order->status == 'stopped' || $order->status == 'active') ){
            $exportEntry = intval($order->total) - intval($order->exported);
            $export = $this->get_export_orders_count();
            if($exportEntry != $export){
                $newTotal = intval($order->total) + ($export - $exportEntry);
                $this->update_table_total($order->id, $newTotal);
            }
        }
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

    private function update_table_data($id, $exported, $status, $code = null){
        return $this->wpdb->update(
            $this->table_name,
            array('exported' => $exported, 'status' => $status, 'code' => $code),
            array('id' => $id),
            array('%d', '%s', '%s'),
            array('%d')
        );
    }

    public function get_total_orders(){
        return $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_posts} 
                 WHERE type = %s 
                 AND status != %s",
                'shop_order',
                'wc-checkout-draft'
            )
        );
    }
    public function get_export_orders_count(){
        return $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM $this->table_posts
                WHERE type = %s
                AND status != %s
                AND ID NOT IN (
                    SELECT post_id FROM {$this->wpdb->prefix}postmeta
                    WHERE meta_key = %s AND meta_value = 'true'
                )",
                'shop_order',
                'wc-checkout-draft',
                $this->meta_key
            )
        );
    }

    public function get_orders_export_esputnik(){
        $orders = $this->get_orders_from_database_without_metakey();
        $order_ids = [];
        if($orders && count($orders) > 0){
            foreach ($orders as $order) {
                $order_ids[] = $order->id;
            }
        }
        return $order_ids;
    }
    private function get_orders_args($shop_order){
        return [
            'post_type'      => $shop_order,
            'posts_per_page' => -1,
            'post_status'    => 'any',
        ];
    }
    private function get_orders_export_args($shop_order){
        return [
            'post_type'      => $shop_order,
            'posts_per_page' => -1,
            'post_status'    => 'any',
            'order'          => 'ASC',
            'meta_query'     => array(
                'relation' => 'OR',
                array(
                    'key'     => $this->meta_key,
                    'value'   => 'true',
                    'compare' => 'NOT EXISTS',
                ),
            ),
        ];
    }

    /**
     * entry to yespo queue orders
     **/

    public function add_entry_queue_items() {
        $count = $this->check_last_entry_status('STARTED');

        if ($count == 0) {
            $data = [
                'yespo_status' => 'STARTED'
            ];
            return $this->wpdb->insert($this->table_yespo_queue_orders, $data);
        }

        return false;
    }

    public function update_entry_queue_items($status) {
        $last_id = $this->wpdb->get_var(
            "SELECT ID 
        FROM {$this->table_yespo_queue_orders} 
        ORDER BY ID DESC 
        LIMIT 1"
        );

        if ($last_id) {
            $data = ['yespo_status' => $status];
            $where = ['ID' => $last_id];
            return $this->wpdb->update($this->table_yespo_queue_orders, $data, $where);
        }

        return false;
    }
    public function check_queue_items_for_session() {
        return $this->check_last_entry_status('STARTED');
    }
    private function check_last_entry_status($status) {
        $last_status = $this->wpdb->get_var(
            "SELECT yespo_status 
        FROM {$this->table_yespo_queue_orders} 
        ORDER BY ID DESC 
        LIMIT 1"
        );
        return $last_status === $status;
    }


    private function get_latest_orders(){
        $results = $this->get_orders_from_db($this->time_limit);
        if(empty($results)) $results = $this->get_orders_from_db($this->gmt);

        $orders = [];
        if(count($results) > 0){
            foreach ($results as $post){
                $orders[] = $post->id;
            }
        }
        return $orders;
    }

    public function get_bulk_export_orders(){
        $period_start = date('Y-m-d H:i:s', time() - $this->period_selection);

        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM $this->table_posts
            WHERE type = %s
            AND status != %s
            AND ID NOT IN (
                SELECT post_id FROM {$this->wpdb->prefix}postmeta
                WHERE meta_key = %s AND meta_value = 'true'
            )
            AND date_created_gmt < %s
            ORDER BY ID ASC
            LIMIT %d",
                'shop_order',
                'wc-checkout-draft',
                $this->meta_key,
                $period_start,
                $this->number_for_export
            ),
            OBJECT
        );
    }

    private function get_orders_from_database_without_metakey(){
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM $this->table_posts
            WHERE type = %s
            AND status != %s
            AND ID NOT IN (
                SELECT post_id FROM {$this->wpdb->prefix}postmeta
                WHERE meta_key = %s AND meta_value = 'true'
            )",
                'shop_order',
                'wc-checkout-draft',
                $this->meta_key
            )
        );
    }

    private function get_orders_from_database(){
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM $this->table_posts WHERE type = %s AND status != %s",
                'shop_order',
                'wc-checkout-draft'
            )
        );
    }

    private function get_orders_from_db($time){
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM $this->table_posts WHERE type = %s AND status != %s AND date_updated_gmt BETWEEN %s AND %s",
                'shop_order',
                'wc-checkout-draft',
                date('Y-m-d H:i:s', time() - $this->period_selection_since),
                date('Y-m-d H:i:s', time() - $this->period_selection_up)
            )
        );
    }

    //add json of exported orders
    public function add_json_log_entry($orders) {
        $json = json_encode($orders);
        if ($json !== false) {
            $data = [
                'text' => $json,
                'created_at' => current_time('mysql')
            ];
            $this->wpdb->insert($this->table_yespo_curl_json, $data);
        }
    }

}