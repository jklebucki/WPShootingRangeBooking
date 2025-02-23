<?php
if (!defined('ABSPATH')) {
    exit;
}

function srbs_get_time_slots() {
    global $wpdb;
    $settings_table = $wpdb->prefix . 'srbs_settings';
    $time_slots = $wpdb->get_results("SELECT setting_value FROM $settings_table WHERE setting_key LIKE 'time_slot_%' ORDER BY setting_key");

    return array_map(function($slot) {
        return json_decode($slot->setting_value);
    }, $time_slots);
}
