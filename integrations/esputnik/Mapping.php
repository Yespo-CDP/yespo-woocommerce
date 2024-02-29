<?php

namespace Yespo\Integrations\Esputnik;

class Mapping
{
    public static function woo_to_yes(
        $email,
        $id,
        $first_name = null,
        $last_name = null,
        $meta_data = null,
        $state = null,
        $country = null,
        $city = null,
        $address_1 = null,
        $address_2 = null,
        $postcode = null
    ){
        $address = ($address_1)??'';
        $address .= ' ' . ($address_2) ?? '';
        $region = ($state)??$country??'';

        //$meta_data

        $data['channels'][] = [
            'value' => $email,
            'type' => 'email'
        ];
        $data['externalCustomerId'] = $id;
        if($first_name !== null) $data['firstName'] = $first_name;
        if($last_name !== null) $data['lastName'] = $last_name;

        $data['address'] = [
            'region' => $region,
            'town' => ($city) ?? '',
            'address' => $address,
            'postcode' => ($postcode) ?? ''
        ];

        return $data;
    }
}