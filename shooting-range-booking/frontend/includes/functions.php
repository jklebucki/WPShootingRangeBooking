<?php
if (!defined('ABSPATH')) {
    exit;
}

function srbs_get_time_slots() {
    $time_slots = srbs_get_setting('time_slot');
    $time_slots = $time_slots ? json_decode($time_slots, true) : [];
    usort($time_slots, function($a, $b) {
        return $a[0] <=> $b[0];
    });
    return $time_slots;
}
