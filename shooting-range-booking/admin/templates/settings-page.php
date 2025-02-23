<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$settings_table = $wpdb->prefix . 'srbs_settings';

// Pobierz bieżące ustawienia
$next_reservation_date = $wpdb->get_var("SELECT setting_value FROM $settings_table WHERE setting_key = 'next_reservation_date'");
$dynamic_slots = $wpdb->get_var("SELECT setting_value FROM $settings_table WHERE setting_key = 'max_dynamic_slots'");
$custom_message = $wpdb->get_var("SELECT setting_value FROM $settings_table WHERE setting_key = 'custom_message'");
$static_slots = $wpdb->get_var("SELECT setting_value FROM $settings_table WHERE setting_key = 'max_static_slots'");
$time_slots = json_decode($wpdb->get_var("SELECT setting_value FROM $settings_table WHERE setting_key = 'time_slot'"), true);
$booking_available = $wpdb->get_var("SELECT setting_value FROM $settings_table WHERE setting_key = 'booking_available'") === '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_admin_referer('srbs_save_settings');

    $next_reservation_date = sanitize_text_field($_POST['next_reservation_date']);
    $dynamic_slots = intval($_POST['dynamic_slots']);
    $custom_message = sanitize_textarea_field($_POST['custom_message']);
    $static_slots = intval($_POST['static_slots']);
    $booking_available = isset($_POST['booking_available']) ? '1' : '0';

    $wpdb->replace($settings_table, ['setting_key' => 'next_reservation_date', 'setting_value' => $next_reservation_date]);
    $wpdb->replace($settings_table, ['setting_key' => 'max_dynamic_slots', 'setting_value' => $dynamic_slots]);
    $wpdb->replace($settings_table, ['setting_key' => 'custom_message', 'setting_value' => $custom_message]);
    $wpdb->replace($settings_table, ['setting_key' => 'max_static_slots', 'setting_value' => $static_slots]);
    $wpdb->replace($settings_table, ['setting_key' => 'booking_available', 'setting_value' => $booking_available]);

    // Save time slots
    $time_slots = $_POST['time_slots'];
    // Sort time slots by 'range' before saving
    usort($time_slots, function ($a, $b) {
        return strcmp($a['range'], $b['range']);
    });
    $wpdb->replace($settings_table, ['setting_key' => 'time_slot', 'setting_value' => json_encode($time_slots)]);

    echo '<div class="notice notice-success is-dismissible"><p>Ustawienia zostały zapisane.</p></div>';
}

// Sort time slots by 'range'
if (!empty($time_slots)) {
    usort($time_slots, function ($a, $b) {
        return strcmp($a['range'], $b['range']);
    });
}
?>

<div class="wrap">
    <h1>Ustawienia systemu</h1>
    <form method="POST">
        <?php wp_nonce_field('srbs_save_settings'); ?>
        <table class="form-table">
            <tr>
                <th>
                    <label>Shortcode na stronę:</label>
                </th>
                <td>
                    <label>[srbs_booking]</label>
                </td>
            </tr>
            <tr>
                <th><label for="booking_available">Rezerwacja dostępna:</label></th>
                <td>
                    <label class="switch">
                        <input type="checkbox" id="booking_available" name="booking_available" <?php checked($booking_available, '1'); ?>>
                        <span class="slider round"></span>
                    </label>
                </td>
            </tr>
            <tr>
                <th><label for="next_reservation_date">Data następnej rezerwacji:</label></th>
                <td><input type="date" id="next_reservation_date" name="next_reservation_date" value="<?php echo esc_attr($next_reservation_date); ?>" required></td>
            </tr>
            <tr>
                <th><label for="static_slots">Maksymalna liczba miejsc na strzelanie statyczne:</label></th>
                <td><input type="number" id="static_slots" name="static_slots" value="<?php echo esc_attr($static_slots); ?>" min="1" max="100" required></td>
            </tr>
            <tr>
                <th><label for="dynamic_slots">Maksymalna liczba miejsc na strzelanie dynamiczne:</label></th>
                <td><input type="number" id="dynamic_slots" name="dynamic_slots" value="<?php echo esc_attr($dynamic_slots); ?>" min="1" max="100" required></td>
            </tr>
            <tr>
                <th><label for="custom_message">Komunikat dla użytkowników:</label></th>
                <td>
                    <textarea id="custom_message" name="custom_message" rows="5" cols="50"><?php echo esc_textarea($custom_message); ?></textarea>
                </td>
            </tr>
            <tr>
                <th colspan="2">
                    <h2>Sloty czasowe</h2>
                </th>
            </tr>
            <tr>
                <td colspan="2">
                    <table id="time-slots-container">
                        <thead>
                            <tr>
                                <th>Zakres godzin</th>
                                <th>Rodzaj strzelania</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($time_slots)): ?>
                                <?php foreach ($time_slots as $i => $slot): ?>
                                    <tr>
                                        <td>
                                            <input type="text" id="time_slots_<?php echo $i; ?>_range" name="time_slots[<?php echo $i; ?>][range]" value="<?php echo esc_attr($slot['range']); ?>" required>
                                        </td>
                                        <td>
                                            <select id="time_slots_<?php echo $i; ?>_type" name="time_slots[<?php echo $i; ?>][type]" required>
                                                <option value="static" <?php selected($slot['type'], 'static'); ?>>Statyczne</option>
                                                <option value="dynamic" <?php selected($slot['type'], 'dynamic'); ?>>Dynamiczne</option>
                                            </select>
                                        </td>
                                        <td>
                                            <button type="button" class="button remove-time-slot">Usuń</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <button type="button" class="button button-sized" id="add-time-slot">Dodaj slot czasowy</button>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <input type="submit" class="button-primary button-sized" value="Zapisz ustawienia">
                </td>
            </tr>
        </table>
    </form>
</div>