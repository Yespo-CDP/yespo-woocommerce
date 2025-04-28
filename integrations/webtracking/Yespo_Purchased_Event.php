<?php

namespace Yespo\Integrations\Webtracking;

class Yespo_Purchased_Event extends Yespo_Web_Tracking_Abstract
{

    public function __construct() {}


    public function send_order_to_yespo($order_id) {
        if (!empty($order_id)) {
            $order = wc_get_order($order_id);
            $hash = (new Yespo_Cart_Event())->get_option();

            $json = $this->generate_json($order, $order_id, $hash);
            $response = Yespo_Web_Tracking_Curl_Request::curl_request($json);

            (new Yespo_Logger())->write_to_file('PurchasedItems', $json, $response);

            return true;
        }
    }


    public function get_data() {}

    public function get_orders_items($order, $id, $hash){
        $purchased_items = [];

        if ($order) {
            $currency = $order->get_currency();
            $cart = $order->get_items();

            $purchased_items['OrderNumber'] = $id;
            $purchased_items['GUID'] = $hash;

            foreach ($cart as $item) {
                $quantity = $item->get_quantity();

                $purchased_items['PurchasedItems'][] = array(
                    'productKey' => $item->get_product_id(),
                    'price' => $item->get_subtotal() / $quantity,
                    'quantity' => $quantity,
                    'currency' => $currency
                );
            }
        }

        return $purchased_items;
    }

    private function generate_trackedOrderId() {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function generate_json($order, $id, $hash) {
        $order_data = $this->get_orders_items($order, $id, $hash);

        $tenantId =  (new Yespo_User_Event)->get_tenantId();
        $webId = (new Yespo_User_Event)->get_webId();

        $user_data = [
            "externalCustomerId" => !empty($order->get_customer_id()) ? $order->get_customer_id() : null,
            "user_phone" => !empty($order->get_billing_phone()) ? $order->get_billing_phone() : null,
            "user_email" => !empty($order->get_billing_email()) ? $order->get_billing_email() : null,
            "user_name" => trim(
                (!empty($order->get_billing_first_name()) ? $order->get_billing_first_name() : '') . ' ' .
                (!empty($order->get_billing_last_name()) ? $order->get_billing_last_name() : '')
            )
        ];

        if (empty(trim($user_data["user_name"]))) {
            $user_data["user_name"] = null;
        }

        $general_info = array_filter([
            "eventName" => "PurchasedItems",
            "siteId" => $tenantId,
            "datetime" => time() * 1000,
            "externalCustomerId" => $user_data["externalCustomerId"],
            "user_phone" => $user_data["user_phone"],
            "user_email" => $user_data["user_email"],
            "user_name" => $user_data["user_name"],
            "cookies" => [
                "sc" => $webId
            ]
        ], function ($value) {
            return !is_null($value) && $value !== '';
        });

        return [
            "GeneralInfo" => $general_info,
            "TrackedOrderId" => $this->generate_trackedOrderId(),
            "PurchasedItems" => [
                "Products" => array_map(function ($product) {
                    return array_filter([
                        "product_id" => strval($product['productKey']) ?? null,
                        "unit_price" => strval($product['price']) ?? null,
                        "quantity" => $product['quantity'] ?? null
                    ], function ($value) {
                        return !is_null($value);
                    });
                }, $order_data['PurchasedItems'] ?? []),
                "OrderNumber" => strval($order_data['OrderNumber']) ?? null
            ]
        ];
    }

}