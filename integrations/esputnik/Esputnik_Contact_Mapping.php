<?php

namespace Yespo\Integrations\Esputnik;

class Esputnik_Contact_Mapping
{
    public static function woo_to_yes($user_data){
        /*
        echo "<pre>";
        print_r($user_data );
        echo "</pre>";
        var_dump( get_user_meta($user_data->ID, 'locale', true) );
        var_dump( get_bloginfo('language') );
        die();
        */
        return self::data_woo_to_yes(self::user_transformation_to_array($user_data));
    }

    public static function guest_user_woo_to_yes($order){
        return self::data_woo_to_yes(self::order_transformation_to_array($order));
    }

    public static function guest_user_admin_woo_to_yes($post){
        return self::data_woo_to_yes(self::admin_order_transformation_to_array($post));
    }

    public static function subscribed_user_woo_to_yes($email){
        return self::data_woo_to_yes(self::subscription_transformation_to_array($email));
    }

    public static function clean_user_phone_data($email){
        return self::remove_phone_number_array($email);
    }

    public static function clean_user_personal_data($email){
        return self::remove_all_personal_data($email);
    }

    private static function data_woo_to_yes($user)
    {
        $address = !empty($user['address_1']) ? $user['address_1'] : (!empty($user['address_2']) ? $user['address_2'] : ' ');
        $region = ($user['state']) ?? $user['country'] ?? ' ';

        $data['channels'][] = [
            'value' => $user['email'],
            'type' => 'email'
        ];
        if (isset($user['phone']) && !empty($user['phone'])) {
            //if(!empty($user['country_id'])) $phoneNumber = Esputnik_Phone_Validation::start_validation($user['phone'], $user['country_id']);
            //else $phoneNumber = preg_replace("/[^0-9]/", "", $user['phone']);
            $phoneNumber = preg_replace("/[^0-9]/", "", $user['phone']);
        } else $phoneNumber = ' ';
        $data['channels'][] = [
            'value' => $phoneNumber,
            'type' => 'sms'
        ];

        if(isset($user['ID'])) $data['externalCustomerId'] = $user['ID'];
        if($user['first_name'] !== null && Esputnik_Contact_Validation::name_validation($user['first_name'])) $data['firstName'] = $user['first_name'];
        else $data['firstName'] = ' ';

        if($user['last_name'] !== null && Esputnik_Contact_Validation::lastname_validation($user['last_name'])) $data['lastName'] = $user['last_name'];
        else $data['lastName'] = ' ';

        $data['address'] = [
            'region' => $region,
            'town' => $user['city'] ?? ' ',
            'address' => $address,
            'postcode' => $user['postcode'] ?? ' '
        ];
        if($user['languageCode']) $data['languageCode'] = $user['languageCode'];
        if (!empty($meta_data) && is_array($meta_data)){
            $data['fields'] = self::fields_transformation($meta_data);
        }

        return $data;
    }

    //remove user phone
    private static function remove_phone_number_array($email){
        return [
            'dedupeOn' => 'email',
            'contactFields' => ['sms'],
            'contacts' => [
                [
                    'channels' => [
                        [
                            'type' => 'email',
                            'value' => $email
                        ]
                    ]
                ]
            ]
        ];
    }

    //removes personal user data
    private static function remove_all_personal_data($email){
        $data['channels'][] = [
            'value' => $email,
            'type' => 'email'
        ];
        $data['firstName'] = ' ';
        $data['lastName'] = ' ';
        $data['address'] = [
            'region' => ' ',
            'town' => ' ',
            'address' => ' ',
            'postcode' => ' '
        ];
        $data['languageCode'] = ' ';

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
            'first_name' => !empty($user->first_name) ? $user->first_name : (!empty($user->billing_first_name) ? $user->billing_first_name : (!empty($user->shipping_first_name) ? $user->shipping_first_name : '')),
            'last_name' => !empty($user->last_name) ? $user->last_name : (!empty($user->billing_last_name) ? $user->billing_last_name : (!empty($user->shipping_last_name) ? $user->shipping_last_name : '')),
            'state' => !empty(self::get_state_name($user->billing_country, $user->billing_state)) ? self::get_state_name($user->billing_country, $user->billing_state) : (!empty(self::get_state_name($user->shipping_country, $user->shipping_state)) ? self::get_state_name($user->shipping_country, $user->shipping_state) : ''),
            //'country' => self::get_country_name($user->billing_country) ?? '',
            'country_id' => !empty($user->billing_country) ? $user->billing_country : (!empty($user->shipping_country) ? $user->shipping_country : ''),
            'city' => !empty($user->billing_city) ? $user->billing_city : (!empty($user->shipping_city) ? $user->shipping_city : ''),
            'address_1' => !empty($user->billing_address_1) ? $user->billing_address_1 : (!empty($user->shipping_address_1) ? $user->shipping_address_1 : ''),
            'address_2' => !empty($user->billing_address_2) ? $user->billing_address_2 : (!empty($user->shipping_address_2) ? $user->shipping_address_2 : ''),
            'phone' => !empty($user->billing_phone) ? $user->billing_phone : (!empty($user->shipping_phone) ? $user->shipping_phone : ''),
            'postcode' => !empty($user->billing_postcode) ? $user->billing_postcode : (!empty($user->shipping_postcode) ? $user->shipping_postcode : ''),
            'languageCode' => !empty(substr(get_user_meta($user->ID, 'locale', true), 0, 2)) ? substr(get_user_meta($user->ID, 'locale', true), 0, 2) : ( get_bloginfo('language') ? get_bloginfo('language') : '')
        ];
    }

    private static function order_transformation_to_array($order){
        return [
            'email' => !empty($order->get_billing_email()) ? $order->get_billing_email() : (!empty($order->get_shipping_email()) ? $order->get_shipping_email() : ''),
            //'ID' => !empty($order->get_billing_email()) ? $order->get_billing_email() : (!empty($order->get_shipping_email()) ? $order->get_shipping_email() : ''),
            'ID' => self::get_registered_user_id($order),
            'first_name' => !empty($order->get_billing_first_name()) ? $order->get_billing_first_name() : (!empty($order->get_shipping_first_name()) ? $order->get_shipping_first_name() : ''),
            'last_name' => !empty($order->get_billing_last_name()) ? $order->get_billing_last_name() : (!empty($order->get_shipping_last_name()) ? $order->get_shipping_last_name() : ''),
            'state' => !empty(self::get_state_name($order->get_billing_country(), $order->get_billing_state())) ? self::get_state_name($order->get_billing_country(), $order->get_billing_state()) : (!empty(self::get_state_name($order->get_shipping_country(), $order->get_shipping_state())) ? self::get_state_name($order->get_shipping_country(), $order->get_shipping_state()) : ''),
            //'country' => self::get_country_name($user->billing_country) ?? '',
            'country_id' => !empty($order->get_billing_country()) ? $order->get_billing_country() : (!empty($order->get_shipping_country()) ? $order->get_shipping_country() : ''),
            //'timeZone' => $user->billing_country ?? '',
            'city' => !empty($order->get_billing_city()) ? $order->get_billing_city() : (!empty($order->get_shipping_city()) ? $order->get_shipping_city() : ''),
            'address_1' => (!empty($order->get_billing_address_1()) ? $order->get_billing_address_1() : '') . (!empty($order->get_billing_address_2()) ? ', ' . $order->get_billing_address_2() : '') ?? (!empty($order->get_shipping_address_1()) ? $order->get_shipping_address_1() : '') . (!empty($order->get_shipping_address_2()) ? ', ' . $order->get_shipping_address_2() : ''),
            'address_2' => (!empty($order->get_billing_address_1()) ? $order->get_billing_address_1() : '') . (!empty($order->get_billing_address_2()) ? ', ' . $order->get_billing_address_2() : '') ?? (!empty($order->get_shipping_address_1()) ? $order->get_shipping_address_1() : '') . (!empty($order->get_shipping_address_2()) ? ', ' . $order->get_shipping_address_2() : ''),
            'phone' => !empty($order->get_billing_phone()) ? $order->get_billing_phone() : (!empty($order->get_shipping_phone()) ? $order->get_shipping_phone() : ''),
            'postcode' => !empty($order->get_billing_postcode()) ? $order->get_billing_postcode() : (!empty($order->get_shipping_postcode()) ? $order->get_shipping_postcode() : '')
        ];
    }

    private static function admin_order_transformation_to_array($post){
        return [
            'email' => $post['_billing_email'],
            //'ID' => $post['_billing_email'],
            'first_name' => !empty($post['_billing_first_name']) ? $post['_billing_first_name'] : (!empty($post['_shipping_first_name']) ? $post['_shipping_first_name'] : ''),
            'last_name' => !empty($post['_billing_last_name']) ? $post['_billing_last_name'] : (!empty($post['_shipping_last_name']) ? $post['_shipping_last_name'] : ''),
            'state' => !empty(self::get_state_name($post['_billing_country'], $post['_billing_state'])) ? self::get_state_name($post['_billing_country'], $post['_billing_state']) : (!empty(self::get_state_name($post['_shipping_country'], $post['_shipping_state'])) ? self::get_state_name($post['_shipping_country'], $post['_shipping_state']) : ''),
            //'country' => self::get_country_name($user->billing_country) ?? '',
            'country_id' => !empty($post['_billing_country']) ? $post['_billing_country'] : (!empty($post['_shipping_country']) ? $post['_shipping_country'] : ''),
            //'timeZone' => $user->billing_country ?? '',
            'city' => !empty($post['_billing_city']) ? $post['_billing_city'] : (!empty($post['_shipping_city']) ? $post['_shipping_city'] : ''),
            'address_1' => !empty($post['_billing_address_1']) ? $post['_billing_address_1'] : (!empty($post['_shipping_address_1']) ? $post['_shipping_address_1'] : ''),
            'address_2' => !empty($post['_billing_address_2']) ? $post['_billing_address_2'] : (!empty($post['_shipping_address_2']) ? $post['_shipping_address_2'] : ''),
            'phone' => !empty($post['_billing_phone']) ? $post['_billing_phone'] : '',
            'postcode' => !empty($post['_billing_postcode']) ? $post['_billing_postcode'] : (!empty($post['_shipping_postcode']) ? $post['_shipping_postcode'] : '')
        ];
    }

    private static function subscription_transformation_to_array($email){
        return [
            'email' => $email,
            'ID' => $email
        ];
    }

    private static function get_registered_user_id($order){
        $user = get_user_by('email', $order->get_billing_email());
        if($user) return $user->ID;
        else return null;
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