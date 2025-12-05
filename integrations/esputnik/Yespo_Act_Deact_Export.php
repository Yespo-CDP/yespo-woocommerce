<?php

namespace Yespo\Integrations\Esputnik;

use Exception;
use WP_User_Query;
use Yespo\Integrations\Webtracking\Yespo_Logger;

class Yespo_Act_Deact_Export
{
    public static function activate() {
        $options = get_option('yespo_options');

        if (!is_array($options) || !isset($options['yespo_plugin_deactivated_unix'], $options['yespo_plugin_deactivated_datetime'])) {
            return;
        }

        $deactivated_timestamp_unix = (int) $options['yespo_plugin_deactivated_unix'];
        $deactivated_timestamp_datetime = sanitize_text_field($options['yespo_plugin_deactivated_datetime']);

        try {
            self::update_contacts($deactivated_timestamp_unix);
            self::update_orders($deactivated_timestamp_datetime);
        } catch (Exception $e) {
            (new Yespo_Logger())->write_to_file('ACT DEACT', $e->getMessage(), 'EXCEPTION');
        }

        unset($options['yespo_plugin_deactivated_unix'], $options['yespo_plugin_deactivated_datetime']);
        update_option('yespo_options', $options);
        wp_cache_flush();
    }

    public static function deactivate() {
        $options = get_option('yespo_options');
        if (!is_array($options)) {
            $options = [];
        }

        $options['yespo_plugin_deactivated_unix'] = time();
        $options['yespo_plugin_deactivated_datetime'] = gmdate('Y-m-d H:i:s');

        update_option('yespo_options', $options);
    }

    private static function update_contacts($deactivated_timestamp) {
        $deactivated_timestamp = (int) $deactivated_timestamp;
        $current_timestamp = time();

        $args = [
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key'     => 'last_update',
                    'value'   => [$deactivated_timestamp, $current_timestamp],
                    'compare' => 'BETWEEN',
                    'type'    => 'NUMERIC',
                ],
                [
                    'key'     => 'yespo_contact_id',
                    'compare' => 'EXISTS',
                ],
            ],
            'fields' => ['ID'],
            'number' => 2000,
        ];

        $user_query = new WP_User_Query($args);
        $user_ids = array_map(function($user) {
            return $user->ID;
        }, $user_query->get_results());

        if (!empty($user_ids)) {
            try {
                $export_array = Yespo_Contact_Mapping::create_bulk_export_array($user_ids);
                $response = (new Yespo_Contact())->export_bulk_users($export_array);
                if($response) (new Yespo_Export_Users())->update_entry_yespo_queue($response, "FINISHED", "FINISHED");

                if (is_wp_error($response)) {
                    throw new Exception($response->get_error_message());
                }

                (new Yespo_Logger())->write_to_file('ACT DEACT', json_encode([
                    'user_ids' => $user_ids,
                    'response' => $response,
                    'count' => count($user_ids),
                    'timestamp' => current_time('mysql', true),
                ]), 'USERS UPDATED');
            } catch (Exception $e) {
                (new Yespo_Logger())->write_to_file('ACT DEACT', $e->getMessage(), 'CONTACT EXPORT ERROR');
            }
        } else {
            (new Yespo_Logger())->write_to_file('ACT DEACT', 'CONTACTS EMPTY', 'EMPTY');
        }

        wp_reset_postdata();
    }

    private static function update_orders($deactivated_timestamp) {
        $orders_ids = self::get_updated_orders($deactivated_timestamp);

        if (!empty($orders_ids)) {
            try {
                $export_array = Yespo_Order_Mapping::create_bulk_order_export_array($orders_ids);
                $response = (new Yespo_Order())->create_bulk_orders_on_yespo($export_array, 'update');

                if (is_wp_error($response)) {
                    (new Yespo_Logger())->write_to_file('ACT DEACT', 'wp error response', 'ORDER EXPORT ERROR');
                }

                (new Yespo_Logger())->write_to_file('ACT DEACT', json_encode([
                    'orders_ids' => $orders_ids,
                    'response' => $response,
                    'count' => count($orders_ids),
                    'timestamp' => current_time('mysql', true),
                ]), 'ORDERS UPDATED');
            } catch (Exception $e) {
                (new Yespo_Logger())->write_to_file('ACT DEACT', $e->getMessage(), 'ORDER EXPORT ERROR');
            }
        } else {
            (new Yespo_Logger())->write_to_file('ACT DEACT', 'ORDERS EMPTY', 'EMPTY');
        }
    }

    private static function get_updated_orders($timestamp){
        global $wpdb;

        return  $wpdb->get_results(
            $wpdb->prepare(
                "
                SELECT DISTINCT p.ID
                FROM {$wpdb->prefix}posts p
                INNER JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id
                WHERE p.post_type LIKE %s
                    AND p.post_modified_gmt > %s
                    AND pm.meta_key = %s
                    AND pm.meta_value IS NOT NULL
                    AND pm.meta_value != ''
                ",
                $wpdb->esc_like('shop_order') . '%',
                $timestamp,
                'sent_order_to_yespo'
            ),
            OBJECT
        );
    }
}