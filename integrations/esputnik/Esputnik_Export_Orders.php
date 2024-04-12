<?php

namespace Yespo\Integrations\Esputnik;

use WP_Query;

class Esputnik_Export_Orders
{
    private $number_for_export = 10;
    private $table_name;
    private $meta_key;
    private $wpdb;

    public function __construct(){
        global $wpdb;
        $this->meta_key = (new Esputnik_Order())->get_meta_key();
        $this->wpdb = $wpdb;
        $this->table_name = $this->wpdb->prefix . 'yespo_export_status_log';
    }

    public function add_orders_export_task(){
        $status = $this->get_order_export_status_active();
        if(empty($status)){
            $data = [
                'export_type' => 'orders',
                'total' => $this->get_export_orders_count(),
                'exported' => 0,
                'status' => 'active'
            ];
            $result = $this->wpdb->insert($this->table_name, $data);

            if ($result !== false) return true;
            else return false;
        }
        else return false;
    }

    public function start_export_orders() {
        $status = $this->get_order_export_status_active();
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

            if($total <= $exported + $live_exported){
                $current_status = 'completed';
                $exported = $total;
            } else $exported += $live_exported;

            $this->update_table_data($status->id, $exported, $current_status);
        } else {
            $status = $this->get_order_export_status();
            if(!empty($status) && $status->status === 'completed' && $status->display === null){
                $this->update_table_data($status->id, intval($status->total), $status->status);
            }
        }
    }

    public function get_final_orders_exported(){
        $status = $this->get_order_export_status();
        return $this->update_table_data($status->id, intval($status->total), $status->status, true);
    }

    public function export_orders_to_esputnik(){
        $orders = $this->get_orders_export_esputnik($this->get_orders_export_args());

        if(count($orders) > 0 && isset($orders[0])){
            return (new Esputnik_Order())->create_order_on_yespo(
                wc_get_order($orders[0])
            );
        }
    }

    public function get_process_orders_exported(){
        return $this->get_order_export_status();
    }

    public function get_order_export_status(){
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM $this->table_name WHERE export_type = %s ORDER BY id DESC LIMIT 1",
                'orders'
            )
        );
    }

    public function get_order_export_status_active(){
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM $this->table_name WHERE export_type = %s AND status = %s ORDER BY id DESC LIMIT 1",
                'orders',
                'active'
            )
        );
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

    public function get_total_orders(){
        return count($this->get_orders_export_esputnik($this->get_orders_args()));
    }
    public function get_export_orders_count(){
        return count($this->get_orders_export_esputnik($this->get_orders_export_args()));
    }
    public function get_orders_export_esputnik($args){
        $orders = [];
        $orders_query = new WP_Query( $args );

        if ( $orders_query->have_posts() ) {
            while ( $orders_query->have_posts() ) {
                $orders_query->the_post();
                $orders[] = $orders_query->post->ID;
            }
            wp_reset_postdata();
        }
        return $orders;
    }
    private function get_orders_args(){
        return [
            'post_type'      => 'shop_order_placehold',
            'posts_per_page' => -1,
            'post_status'    => 'any',
        ];
    }
    private function get_orders_export_args(){
        return [
            'post_type'      => 'shop_order_placehold',
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
}