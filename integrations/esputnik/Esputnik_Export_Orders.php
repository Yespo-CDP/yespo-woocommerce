<?php

namespace Yespo\Integrations\Esputnik;

use WP_Query;

class Esputnik_Export_Orders
{
    const CUSTOMER = 'customer';
    const SUBSCRIBER = 'subscriber';
    private $meta_key;

    public function __construct(){
        $this->meta_key = (new Esputnik_Order())->get_meta_key();
    }

    public function export_orders_to_esputnik(){
        $orders = $this->get_orders_export_esputnik($this->get_orders_export_args());

        if(count($orders) > 0 && isset($orders[0])){
            return (new Esputnik_Order())->create_order_on_yespo(
                wc_get_order($orders[0])
            );
        }
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