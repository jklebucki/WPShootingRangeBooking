<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_delete_booking', 'srbs_delete_booking');

function srbs_delete_booking()
{
    // Check nonce for security
    check_ajax_referer('srbs_nonce', 'security');

    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__("You do not have permission.", 'srbs'));
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'srbs_bookings';
    $booking_id = intval($_POST['booking_id']);

    // Delete booking by ID
    $result = $wpdb->delete($table_name, ['id' => $booking_id]);

    if ($result) {
        wp_send_json_success(__("Booking has been deleted.", 'srbs'));
    } else {
        wp_send_json_error(__("Failed to delete booking.", 'srbs'));
    }
}
