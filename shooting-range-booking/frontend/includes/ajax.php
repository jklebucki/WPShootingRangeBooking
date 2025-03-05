<?php
if (!defined('ABSPATH')) {
    exit;
}

// ✅ AJAX handling for user booking
add_action('wp_ajax_make_booking', 'srbs_make_booking');

function srbs_make_booking()
{
    check_ajax_referer('srbs_nonce', 'security');

    if (!is_user_logged_in()) {
        wp_send_json_error(__("You must be logged in.", 'srbs'));
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $club_number = get_user_meta($user_id, 'club_number', true);
    $time_slot = sanitize_text_field($_POST['time_slot']);
    $dynamic = filter_var($_POST['dynamic'], FILTER_VALIDATE_BOOLEAN);
    $date = srbs_get_setting('next_reservation_date');

    $booking_type = $dynamic ? 'dynamic' : 'static';

    // Check if the user has already made a booking for this type of shooting
    $bookings_table = $wpdb->prefix . 'srbs_bookings';
    $existing_booking = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*) FROM $bookings_table
        WHERE user_id = %d AND date = %s AND booking_type = %s
    ", $user_id, $date, $booking_type));

    if ($existing_booking > 0) {
        wp_send_json_error(__("You can only make one booking for $booking_type shooting.", 'srbs'));
    }

    // Check if the slot is already booked
    $stand_number = $dynamic ? 0 : intval($_POST['stand_number']);

    if ($stand_number > 0) {
        $slot_booked = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*) FROM $bookings_table
        WHERE date = %s AND time_slot = %s AND stand_number = %d
    ", $date, $time_slot, $stand_number));

        if ($slot_booked > 0) {
            wp_send_json_error(__("The selected slot is already booked.", 'srbs'));
        }
    }

    // Check if the number of dynamic shooting slots has been exceeded
    if ($stand_number == 0) {
        $dynamic_slots = srbs_get_setting('max_dynamic_slots');
        $dynamic_slots = $dynamic_slots ? intval($dynamic_slots) : 5;
        $dynamic_bookings_count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM $bookings_table
            WHERE date = %s AND time_slot = %s AND stand_number = 0
        ", $date, $time_slot));

        if ($dynamic_bookings_count >= $dynamic_slots) {
            wp_send_json_error(__("All dynamic shooting slots are already booked.", 'srbs'));
        }
    }

    // Insert booking with a value of 0 for dynamic shooting
    $wpdb->insert($bookings_table, [
        'user_id' => $user_id,
        'club_number' => $club_number,
        'date' => $date,
        'time_slot' => $time_slot,
        'stand_number' => $stand_number,
        'booking_type' => $booking_type
    ]);

    wp_send_json_success(__("Booking added.", 'srbs'));
}

// ✅ AJAX handling for canceling booking
add_action('wp_ajax_cancel_booking', 'srbs_cancel_booking');

function srbs_cancel_booking()
{
    check_ajax_referer('srbs_nonce', 'security');

    if (!is_user_logged_in()) {
        wp_send_json_error(__("You must be logged in.", 'srbs'));
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $booking_id = intval($_POST['booking_id']);

    // Check if the booking belongs to the logged-in user
    $bookings_table = $wpdb->prefix . 'srbs_bookings';
    $booking = $wpdb->get_row($wpdb->prepare("
        SELECT * FROM $bookings_table WHERE id = %d AND user_id = %d
    ", $booking_id, $user_id));

    if (!$booking) {
        wp_send_json_error(__("Booking not found or you do not have permission to cancel it.", 'srbs'));
    }

    // Delete booking
    $wpdb->delete($bookings_table, ['id' => $booking_id]);

    wp_send_json_success(__("Booking canceled.", 'srbs'));
}

function srbs_is_slot_booked($bookings, $stand_number, $time_slot)
{
    foreach ($bookings as $booking) {
        if ($booking->time_slot == $time_slot && ($booking->stand_number == $stand_number || ($booking->booking_type == 'dynamic' && $booking->stand_number == 0))) {
            return $booking;
        }
    }
    return false;
}

// ✅ AJAX handling for loading booking table
add_action('wp_ajax_load_booking_table', 'srbs_load_booking_table');

function srbs_load_booking_table()
{
    check_ajax_referer('srbs_nonce', 'security');

    if (!is_user_logged_in()) {
        wp_send_json_error(__("You must be logged in.", 'srbs'));
    }

    global $wpdb;
    $next_reservation_date = srbs_get_setting('next_reservation_date');
    $bookings_table = $wpdb->prefix . 'srbs_bookings';
    $bookings = $wpdb->get_results($wpdb->prepare("
        SELECT * FROM $bookings_table WHERE date = %s
    ", $next_reservation_date));

    $dynamic_slots = srbs_get_setting('max_dynamic_slots');
    $dynamic_slots = $dynamic_slots ? intval($dynamic_slots) : 5;

    ob_start();
    include plugin_dir_path(dirname(__FILE__)) . 'templates/booking-table.php';
    $table_html = ob_get_clean();

    wp_send_json_success($table_html);
}
