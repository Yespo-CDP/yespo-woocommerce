<?php

namespace Yespo\Integrations\Webtracking;

use WP_Query;
use Yespo\Integrations\Esputnik\Yespo_Export_Orders;

class Yespo_Purchased_Event extends Yespo_Web_Tracking_Abstract
{
    public function get_data(){
        // TODO: Implement get_data() method.
        if ( strpos(sanitize_text_field($_SERVER['REQUEST_URI']), 'order-received') !== false ) {

            $cart_items = [];

            $order = $this->get_created_order();

            $file_path = $_SERVER['DOCUMENT_ROOT'] . '/filedebug.txt';
            $data_to_append = json_encode($order) . '---order---data' . "\n";
            $file_handle = fopen($file_path, 'a');
            if ($file_handle) {
                fwrite($file_handle, $data_to_append);
                fclose($file_handle);
            }
/*
            $args = array(
                'post_type'      => 'shop_order', // Тип поста - замовлення
                'post_status'    => array(         // Включаємо всі статуси, окрім 'wc-checkout-draft'
                    'wc-pending',
                    'wc-processing',
                    'wc-on-hold',
                    'wc-completed',
                    'wc-cancelled',
                    'wc-refunded',
                    'wc-failed',
                    // Додаткові статуси замовлень, якщо вони є
                ),
                'posts_per_page' => 1,            // Тільки одне замовлення
                'orderby'        => 'date',        // Сортування за датою
                'order'         => 'DESC',         // Від найновішого
            );

            $query = new WP_Query($args);

            if ($query->have_posts()) {
                $query->the_post();
                $order_id = get_the_ID(); // Отримуємо ID останнього замовлення
                $order = wc_get_order($order_id); // Отримуємо об'єкт замовлення


                $file_path = $_SERVER['DOCUMENT_ROOT'] . '/filedebug.txt';
                $data_to_append = json_encode($order) . '---order' . "\n";
                $file_handle = fopen($file_path, 'a');
                if ($file_handle) {
                    fwrite($file_handle, $data_to_append);
                    fclose($file_handle);
                }


                wp_reset_postdata(); // Повертаємо оригінальний контекст

                return $order; // Повертаємо об'єкт замовлення
            }
*/
        }

        return null;

        /*
        if ( class_exists( 'WooCommerce' ) && WC()->cart ) {

            foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                $product = $cart_item['data'];
                $cart_items['products'][] = array(
                    'productKey' => $product->get_id(),
                    'price' => $product->get_price(),
                    'quantity' => $cart_item['quantity'],
                    'currency' => get_woocommerce_currency()
                );
            }

            $cart_items['GUID'] =  WC()->cart->get_cart_hash();

            if(empty($cart_items['GUID'])){
                $cart_items['GUID'] = $this->get_option();
                $this->update_option('');
            } else $this->update_option($cart_items['GUID']);

            if(count($cart_items['products']) > 0) return $cart_items;
            else return null;
        }
        return null;
        */
    }

    public function get_created_order(){

        global $wpdb;
        $table_posts = esc_sql($wpdb->prefix . 'wc_orders');

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM %s 
                    WHERE type = %s 
                    AND status != %s 
                    ORDER BY id DESC
                    LIMIT 1",
                $table_posts,
                'shop_order',
                'wc-checkout-draft'
            )
        );

    }
}