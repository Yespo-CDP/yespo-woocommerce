<?php

namespace Yespo\Integrations\Esputnik;

class Esputnik_Export_Users
{
    const CUSTOMER = 'customer';
    const SUBSCRIBER = 'subscriber';
    private $meta_key;

    public function __construct(){
        $this->meta_key = (new Esputnik_Contact())->get_meta_key();
    }

    public function export_users_to_esputnik(){
        $users = $this->get_users_object();
        if(count($users) > 0 && isset($users[0])){
            return (new Esputnik_Contact())-> create_on_yespo(
                (get_user_by('id', $users[0]))->user_email,
                $users[0]
            );
        }
    }

    public function get_users_total_count(){
        return count($this->get_users_object($this->get_users_total_args()));
    }
    public function get_users_export_count(){
        return count($this->get_users_object($this->get_users_export_args()));
    }
    public function get_users_object($args){
        return get_users($args);
    }
    private function get_users_total_args(){
        return [
            'role__in'    => [self::CUSTOMER, self::SUBSCRIBER],
            'orderby' => 'registered',
            'order'   => 'DESC',
        ];
    }
    private function get_users_export_args(){
        return [
            'role__in'    => [self::CUSTOMER, self::SUBSCRIBER],
            'orderby' => 'registered',
            'order'   => 'DESC',
            'fields'  => 'ID',
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key'     => $this->meta_key,
                    'compare' => 'NOT EXISTS',
                ),
                array(
                    'key'     => $this->meta_key,
                    'value'   => '',
                    'compare' => '=',
                ),
            ),
        ];
    }

}