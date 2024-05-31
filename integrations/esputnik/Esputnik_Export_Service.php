<?php

namespace Yespo\Integrations\Esputnik;

//class is not used
class Esputnik_Export_Service
{
    private $contactClass;
    private $orderClass;
    private $table_name;
    private $wpdb;
    private $export_type;

    public function __construct(){
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $this->wpdb->prefix . 'yespo_export_status_log';
        $this->contactClass = new Esputnik_Export_Users();
        $this->orderClass = new Esputnik_Export_Orders();
        $this->export_type = 'allexport';
    }

    public static function get_export_total(){
        $service = new self();
        $total = intval($service->contactClass->get_users_total_count()) + intval($service->orderClass->get_total_orders());
        if($total === 0) $total = 1;
        return $total;
    }

    public static function get_exported_number(){
        $service = new self();
        $total = self::get_export_total();
        $total_for_export = intval($service->contactClass->get_users_export_count()) + intval($service->orderClass->get_export_orders_count());
        return $total - $total_for_export;
    }


    public function get_total_export_number(){
        //method returns number total data export
    }

    public function create_entry_export_number(){
        /*
        $total = $this->get_export_total();
        $total_for_export = intval($this->contactClass->get_users_export_count()) + intval($this->orderClass->get_export_orders_count());
        $total_exported = $total - $total_for_export;
        if($total > $total_exported) return $this->create_entry_db($total, $total_exported);
        */
    }

    private function create_entry_db($total,$total_exported){
        $data = [
            'export_type' => $this->export_type,
            'total' => $total,
            'exported' => $total_exported,
            'status' => 'active'
        ];
        $result = $this->wpdb->insert($this->table_name, $data);

        if ($result !== false) return true;
        else return false;
    }

    private function update_entry_db($id, $exported, $status, $total = null, $display = null)
    {
        return $this->wpdb->update(
            $this->table_name,
            array('exported' => $exported, 'status' => $status, 'display' => $display),
            array('id' => $id),
            array('%d', '%s', '%s'),
            array('%d')
        );
    }

    private function get_export_entry(){
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM $this->table_name WHERE export_type = %s ORDER BY id DESC LIMIT 1",
                $this->export_type
            )
        );
    }
}