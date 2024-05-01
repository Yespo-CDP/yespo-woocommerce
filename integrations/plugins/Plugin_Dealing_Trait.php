<?php

namespace Yespo\Integrations\Plugins;

trait Plugin_Dealing_Trait
{
    protected static $wpdb;
    protected static $users = 'users';

    public static function init()
    {
        global $wpdb;
        self::$wpdb = $wpdb;
    }

    public static function getUniqeEmails(string $table){
        self::init();
        return self::getEmails($table);
        return array_diff(self::getEmails($table), self::getUsersEmails());
    }

    protected static function getUsersEmails()
    {
        return self::getEmails(self::$users);
    }

    protected static function getEmails(string $table)
    {
        $query = "
            SELECT user_email
            FROM " . self::$wpdb->prefix . $table . "
        ";
        return self::$wpdb->get_col($query);
    }

    protected static function getUserByEmail(string $table, $email){

        $query = self::$wpdb->prepare("
            SELECT *
            FROM " . self::$wpdb->prefix . $table . "
            WHERE email = %s
        ", $email);

        return self::$wpdb->get_row($query);
    }
}