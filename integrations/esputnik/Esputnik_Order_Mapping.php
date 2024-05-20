<?php

namespace Yespo\Integrations\Esputnik;

class Esputnik_Order_Mapping
{
    const INITIALIZED = 'INITIALIZED';
    const IN_PROGRESS = 'IN_PROGRESS';
    const CANCELLED = 'CANCELLED';
    const DELIVERED = 'DELIVERED';
    const NO_CATEGORY = 'no category';


    public static function order_woo_to_yes($order){
        $orderArray = self::order_transformation_to_array($order);
        if (isset($orderArray['phone']) && !empty($orderArray['phone'])) {
            //if(!empty($orderArray['country_id'])) $phoneNumber = Esputnik_Phone_Validation::start_validation($orderArray['phone'], $orderArray['country_id']);
            //else $phoneNumber = preg_replace("/[^0-9]/", "", $orderArray['phone']);
            $phoneNumber = $orderArray['phone'];
        } else $phoneNumber = '';

        $data['orders'][0]['status'] = $orderArray['status'];
        $data['orders'][0]['externalOrderId'] = $orderArray['externalOrderId'];
        $data['orders'][0]['externalCustomerId'] = $orderArray['externalCustomerId'];
        $data['orders'][0]['totalCost'] = $orderArray['totalCost'];
        $data['orders'][0]['email'] = $orderArray['email'];
        $data['orders'][0]['date'] = $orderArray['date'];
        $data['orders'][0]['currency'] = $orderArray['currency'];
        if(Esputnik_Contact_Validation::name_validation($orderArray['firstName'])) $data['orders'][0]['firstName'] = $orderArray['firstName'];
        if(Esputnik_Contact_Validation::lastname_validation($orderArray['lastName'])) $data['orders'][0]['lastName'] = $orderArray['lastName'];
        $data['orders'][0]['deliveryAddress'] = $orderArray['deliveryAddress'];
        $data['orders'][0]['phone'] = preg_replace("/[^0-9]/", "", $phoneNumber);
        $data['orders'][0]['shipping'] = $orderArray['shipping'];
        $data['orders'][0]['discount'] = $orderArray['discount'];
        $data['orders'][0]['taxes'] = $orderArray['taxes'];
        $data['orders'][0]['source'] = $orderArray['source'];
        //$data['orders']['deliveryMethod'] = $orderArray['deliveryMethod'];
        $data['orders'][0]['paymentMethod'] = $orderArray['paymentMethod'];
        $data['orders'][0]['items'] = self::get_orders_items($order);

        return $data;
    }

    public static function map_clean_user_data_order($order){
        $orderArray = self::order_transformation_to_array($order);

        $data['orders'][0]['status'] = 'IN_PROGRESS';
        $data['orders'][0]['externalOrderId'] = $orderArray['externalOrderId'];
        $data['orders'][0]['externalCustomerId'] = $orderArray['externalCustomerId'];
        $data['orders'][0]['totalCost'] = $orderArray['totalCost'];
        $data['orders'][0]['date'] = $orderArray['date'];
        $data['orders'][0]['currency'] = $orderArray['currency'];
        $data['orders'][0]['email'] = 'deleted@site.invalid';
        $data['orders'][0]['firstName'] = ' ';
        $data['orders'][0]['lastName'] = ' ';
        $data['orders'][0]['deliveryAddress'] = ' ';
        $data['orders'][0]['phone'] = ' ';
        $data['orders'][0]['shipping'] = ' ';
        $data['orders'][0]['discount'] = ' ';
        $data['orders'][0]['taxes'] = ' ';
        $data['orders'][0]['source'] = ' ';
        $data['orders']['deliveryMethod'] = ' ';
        $data['orders'][0]['paymentMethod'] = ' ';
        $data['orders'][0]['items'] = self::get_orders_items($order);

        return $data;
    }

    private static function order_transformation_to_array($order){
        return [
            'externalOrderId' => $order->id,
            //'externalCustomerId' => $order->customer_id ?? $order->get_billing_email(),
            'externalCustomerId' => !empty($order->customer_id) ? $order->customer_id : (!empty($order) && !is_bool($order) && method_exists($order, 'get_billing_email') && !empty($order->get_billing_email()) ? $order->get_billing_email() : 'deleted@site.invalid'),
            'totalCost' => $order->total,
            'status' => self::get_order_status($order->status) ? self::get_order_status($order->status) : self::INITIALIZED,
            //'email' => $order->get_billing_email(),
            'email' => (!empty($order) && !is_bool($order) && method_exists($order, 'get_billing_email') && !empty($order->get_billing_email())) ? $order->get_billing_email() : 'deleted@site.invalid',
            'date' => ($order && !is_bool($order) && method_exists($order, 'get_date_created') && ($date_created = $order->get_date_created())) ? $date_created->format('Y-m-d\TH:i:s.uP') : null,
            'currency' => $order->currency,
            'firstName' => (!empty($order) && !is_bool($order) && method_exists($order, 'get_billing_first_name') && !empty($order->get_billing_first_name())) ? $order->get_billing_first_name() : (!empty($order) && !is_bool($order) && method_exists($order, 'get_shipping_first_name') && !empty($order->get_shipping_first_name()) ? $order->get_shipping_first_name() : ''),
            'lastName' => (!empty($order) && !is_bool($order) && method_exists($order, 'get_billing_last_name') && !empty($order->get_billing_last_name())) ? $order->get_billing_last_name() : (!empty($order) && !is_bool($order) && method_exists($order, 'get_shipping_last_name') && !empty($order->get_shipping_last_name()) ? $order->get_shipping_last_name() : ''),
            'deliveryAddress' => self::get_delivery_address($order, 'shipping') ? self::get_delivery_address($order, 'shipping') : ( self::get_delivery_address($order, 'billing') ? self::get_delivery_address($order, 'billing') : ''),
            //'phone' => (!empty($order) && !is_bool($order) && method_exists($order, 'get_billing_phone') && !empty($order->get_billing_phone())) ? $order->get_billing_phone() : (!empty($order) && !is_bool($order) && method_exists($order, 'get_shipping_phone') && !empty($order->get_shipping_phone()) ? $order->get_shipping_phone() : ''),
            'phone' => (!empty($order) && !is_bool($order) && method_exists($order, 'get_billing_phone') && !empty($order->get_billing_phone())) ? $order->get_billing_phone() : (!empty($order) && !is_bool($order) && method_exists($order, 'get_shipping_phone') && !empty($order->get_shipping_phone()) ? $order->get_shipping_phone() : ''),
            'shipping' => ($order->shipping_total) ? $order->shipping_total : '',
            'discount' => ($order->discount) ? $order->discount : '',
            'taxes' => !empty($order->total_tax) ? $order->total_tax : ((!empty($order->discount_tax)) ? $order->discount_tax : ((!empty($order->cart_tax)) ? $order->cart_tax : ((!empty($order->shipping_tax)) ? $order->shipping_tax : ''))),
            'source' => ($order->created_via) ? $order->created_via : '',
            //'deliveryMethod' => $order['method_title'],
            'paymentMethod' => ($order->payment_method) ? $order->payment_method : '',
            'country_id' => (!empty($order) && !is_bool($order) && method_exists($order, 'get_billing_country') && !empty($order->get_billing_country())) ? $order->get_billing_country() : (!empty($order) && !is_bool($order) && method_exists($order, 'get_shipping_country') && !empty($order->get_shipping_country()) ? $order->get_shipping_country() : ''),
        ];
    }

    private static function get_delivery_address($order, $way){
        $deliveryAddress = '';
        $fields = array('address_2', 'address_1', 'city', 'state', 'postcode', 'country');

        foreach ($fields as $field) {
            $method = 'get_' . $way . '_' . $field;
            $value = (!empty($order) && !is_bool($order) && method_exists($order, $method)) ? $order->$method() : '';
            if($field === 'country') $value = self::get_country_name($value);
            if($field === 'state'){
                $method = 'get_' . $way . '_country';
                $country = (!empty($order) && !is_bool($order) && method_exists($order, $method)) ? $order->$method() : '';
                $value = self::get_state_name($country, $value);
            }

            if (!empty($value)) {
                $deliveryAddress .= $value . ' ';
            }
        }
        return $deliveryAddress;
    }

    private static function get_order_status($order_status){
        switch ($order_status){
            case 'processing':
            case 'on-hold':
                $result = self::IN_PROGRESS;
                break;
            case 'failed':
            case 'cancelled':
            case 'trash':
            case 'refunded':
                $result = self::CANCELLED;
                break;
            case 'completed':
                $result = self::DELIVERED;
                break;
            default:
                $result = self::INITIALIZED;
        }
        return $result;
    }

    private static function get_orders_items($order){
        $data = [];
        $i = 0;
        if (!empty($order) && !is_bool($order) && method_exists($order, 'get_items')) {
            foreach ($order->get_items() as $item_id => $item) {
                $data[$i]['externalItemId'] = $item->get_product_id();
                $data[$i]['name'] = $item->get_name();
                $data[$i]['category'] = self::get_product_category($data[$i]['externalItemId']);
                $data[$i]['quantity'] = $item->get_quantity();
                $data[$i]['cost'] = $item->get_subtotal();
                $data[$i]['url'] = get_permalink( $data[$i]['externalItemId'] );
                $data[$i]['imageUrl'] = self::get_product_thumbnail_url($data[$i]['externalItemId']);
                $data[$i]['description'] = (wc_get_product( $data[$i]['externalItemId']))->get_short_description();
                $i++;
            }
        }
        return $data;
    }

    private static function get_product_category($product_id){
        $terms = wp_get_post_terms( $product_id, 'product_cat' );
        $categories = [];
        if ( !empty( $terms ) && !is_wp_error( $terms ) ) {
            $categories = array_map(function($term) {
                return $term->name;
            }, $terms);
        }
        return !empty($categories) ? implode(", ", $categories) : self::NO_CATEGORY;
    }
    private static function get_product_thumbnail_url($product_id){
        $image_id = get_post_thumbnail_id( $product_id );
        if ( $image_id ) $image_src = wp_get_attachment_image_src( $image_id, 'full' );
        return !empty($image_src) && is_array($image_src) ? $image_src[0] : '';
    }

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