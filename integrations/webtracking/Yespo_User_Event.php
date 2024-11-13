<?php

namespace Yespo\Integrations\Webtracking;

class Yespo_User_Event extends Yespo_Web_Tracking_Abstract
{
    const USER_AUTH_LABEL = 'user_auth_label';
    const TABLE_ORDERS = 'wc_orders';

    public function __construct() {
        add_action('wp_login', array($this, 'handle_user_event'), 10, 2); // user authorization
        add_action('user_register', array($this, 'handle_user_event'), 10, 1); // user registration
        add_action('profile_update', array($this, 'handle_user_event'), 10, 2); // user update profile
        add_action('woocommerce_thankyou', array($this, 'after_order_complete'), 10, 1); // user after order confirmation
    }

    public function get_data()
    {
        // TODO: Implement get_data() method.

        $current_user = wp_get_current_user();

        if ( $current_user->ID && get_user_meta($current_user->ID, self::USER_AUTH_LABEL, true)) {
            $user_data = array(
                'externalCustomerId' => $current_user->ID,
                'user_email' => $current_user->user_email,
                'user_name' => $current_user->display_name,
                'user_phone' => get_user_meta($current_user->ID, 'billing_phone', true)
            );

            delete_user_meta($current_user->ID, self::USER_AUTH_LABEL);

        } else {
            $order = wc_get_order($this->get_last_order_id());

            $user_data = array(
                'externalCustomerId' => '',
                'user_email' => $order->get_billing_email(),
                'user_name' => trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()),
                'user_phone' => !empty($order->get_billing_phone()) ? $order->get_billing_phone() : ''
            );

        }

        return $user_data;

    }

    public function handle_user_event($user_id_or_login, $user = null) {
        if (is_int($user_id_or_login)) $user_id = $user_id_or_login;
        else $user_id = $user->ID;

        return $this->add_label_to_user($user_id);
    }

    public function after_order_complete($order_id){
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();
        if($user_id) return $this->add_label_to_user($user_id);

        return false;
    }

    private function add_label_to_user($user_id){
        return update_user_meta($user_id, self::USER_AUTH_LABEL, 'true');
    }

    private function get_last_order_id(){
        global $wpdb;
        $table_orders = esc_sql($wpdb->prefix . self::TABLE_ORDERS);

        // phpcs:ignore WordPress.DB
        return $wpdb->get_var(
            $wpdb->prepare(
                "SELECT ID FROM %i WHERE type = %s AND status != %s ORDER BY ID DESC LIMIT 1",
                $table_orders,
                'shop_order',
                'wc-checkout-draft'
            )
        );
    }
}