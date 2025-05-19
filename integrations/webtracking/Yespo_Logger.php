<?php

namespace Yespo\Integrations\Webtracking;

use DateTime;

class Yespo_Logger
{
    const ENTRY_LINE = '==================================================';
    const YESPO_LOG_DIR = 'yespo-cdp';
    const YESPO_FILE_NAME = 'yespo.logs';
    private $month;
    private $year;

    public function __construct() {
        $this->month = date('m');
        $this->year = date('Y');
    }

    public function write_to_file($property, $data, $response) {
        $upload_dir = wp_upload_dir();
        if (empty($upload_dir['basedir'])) return;

        if(!empty($data)) $data = json_encode($data);

        $log_dir = trailingslashit($upload_dir['basedir']) . self::YESPO_LOG_DIR . DIRECTORY_SEPARATOR;

        if (!file_exists($log_dir)) wp_mkdir_p($log_dir);
        if (!is_writable($log_dir)) return;

        $log_file = $log_dir . self::YESPO_FILE_NAME . '.' . $this->month . $this->year . '.txt';
        $time = date('d-m-Y H:i:s');
        $is_new_file = !file_exists($log_file) || filesize($log_file) === 0;

        $log_entry = '';
        if ($is_new_file) $log_entry .= self::ENTRY_LINE . PHP_EOL;
        $log_entry .= "{$time}" . PHP_EOL;
        $log_entry .= "{$property}" . PHP_EOL;
        $log_entry .= "{$data}" . PHP_EOL;
        $log_entry .= "{$response}" . PHP_EOL;
        $log_entry .= self::ENTRY_LINE . PHP_EOL;

        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }

    public function remove_old_logs() {
        $upload_dir = wp_upload_dir();
        if (empty($upload_dir['basedir'])) return;

        $log_dir = trailingslashit($upload_dir['basedir']) . self::YESPO_LOG_DIR . DIRECTORY_SEPARATOR;
        if (!file_exists($log_dir)) return;

        $log_file = $log_dir . self::YESPO_FILE_NAME . '.' . $this->month . $this->year . '.txt';
        if (!file_exists($log_file) || !is_writable($log_file)) return;

        $handle = fopen($log_file, 'r');
        if (!$handle) return;

        $current_time = time();
        $new_blocks = [];
        $current_block = [];

        while (($line = fgets($handle)) !== false) {
            $current_block[] = rtrim($line, "\r\n");

            if (trim($line) === self::ENTRY_LINE) {
                if (!empty($current_block)) {
                    $first_line = $current_block[0];
                    $datetime = DateTime::createFromFormat('d-m-Y H:i:s', $first_line);

                    if ($datetime instanceof DateTime) {
                        $log_time = $datetime->getTimestamp();

                        if (($current_time - $log_time) <= (3 * 24 * 60 * 60)) {
                            $new_blocks[] = implode(PHP_EOL, $current_block);
                        }
                    }
                    else {
                        $new_blocks[] = implode(PHP_EOL, $current_block);
                    }
                }
                $current_block = [];
            }
        }

        fclose($handle);

        $new_content = implode(PHP_EOL, $new_blocks) . PHP_EOL;
        file_put_contents($log_file, $new_content, LOCK_EX);
    }

}