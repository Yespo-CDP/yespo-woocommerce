<?php

namespace Yespo\Integrations\Esputnik;

class Esputnik_Contact_Mapping
{
    public static function woo_to_yes($user_data){
        return self::data_woo_to_yes(self::user_transformation_to_array($user_data));
    }

    public static function guest_user_woo_to_yes($order){
        return self::data_woo_to_yes(self::order_transformation_to_array($order));
    }

    private static function data_woo_to_yes($user){
        $address = !empty($user['address_1']) ? $user['address_1'] : (!empty($user['address_2']) ? $user['address_2'] : '');
        $region = ($user['state']) ?? $user['country'] ?? '';

        $data['channels'][] = [
            'value' => $user['email'],
            'type' => 'email'
        ];
        $data['externalCustomerId'] = $user['ID'];
        if($user['first_name'] !== null) $data['firstName'] = $user['first_name'];
        if($user['last_name'] !== null) $data['lastName'] = $user['last_name'];

        $data['address'] = [
            'region' => $region,
            'town' => $user['city'] ?? '',
            'address' => $address,
            'postcode' => ($user['postcode']) ?? ''
        ];
        if (!empty($meta_data) && is_array($meta_data)){
            $data['fields'] = self::fields_transformation($meta_data);
        }

        return $data;
    }

    //necessary to improve code after getting incoming data
    private static function fields_transformation($fields) {
        return array_map(function($field) {
            return [
                'id' => $field['id'],
                'value' => $field['value']
            ];
        }, $fields);
    }

    private static function user_transformation_to_array($user){
        return [
            'email' => $user->data->user_email,
            'ID' => $user->ID,
            'first_name' => $user->first_name ?? '',
            'last_name' => $user->last_name ?? '',
            'state' => self::get_state_name($user->billing_country, $user->billing_state) ?? '',
            //'country' => self::get_country_name($user->billing_country) ?? '',
            'city' => $user->billing_city ?? '',
            'address_1' => $user->billing_address_1 ?? '',
            'address_2' => $user->billing_address_2 ?? '',
            'postcode' => $user->billing_postcode ?? ''
        ];
    }

    private static function order_transformation_to_array($order){
        return [
            'email' => $order->get_billing_email(),
            'ID' => $order->get_billing_email(),
            'first_name' => $order->get_billing_first_name() ?? '',
            'last_name' => $order->get_billing_last_name() ?? '',
            'state' => self::get_state_name($order->get_billing_country(), $order->get_billing_state()) ?? '',
            //'country' => self::get_country_name($user->billing_country) ?? '',
            //'timeZone' => $user->billing_country ?? '',
            'city' => $order->get_billing_city() ?? '',
            'address_1' => $order->get_billing_address_1() . ', ' . $order->get_billing_address_2() ?? '',
            'address_2' => $order->get_billing_address_1() . ', ' . $order->get_billing_address_2() ?? '',
            'postcode' => $order->get_billing_postcode() ?? ''
        ];
    }

    /** get country name by ID **/
    private static function get_country_name($country_id){
        if(!empty($country_id)) {
            $country_list = WC()->countries->get_countries();
            if (isset($country_list[$country_id])) {
                return $country_list[$country_id];
            }
        }
    }
    /** get state name by ID **/
    private static function get_state_name($country_id, $state_id){
        if(!empty($country_id) && !empty($state_id)) {
            $states = WC()->countries->get_states($country_id);
            if (isset($states[$state_id])) {
                return $states[$state_id];
            }
        }
    }
}