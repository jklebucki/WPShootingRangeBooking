<?php
if (!defined('ABSPATH')) {
    exit;
}

function srbs_get_time_slots() {
    $time_slots = srbs_get_setting('time_slot');
    return $time_slots ? json_decode($time_slots, true) : [];
}

function srbs_get_bookings() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'srbs_bookings';
    $results = $wpdb->get_results("SELECT * FROM $table_name");
    return $results;
}
