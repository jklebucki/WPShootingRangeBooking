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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_admin_referer('srbs_save_settings');

    $next_reservation_date = sanitize_text_field($_POST['next_reservation_date']);
    $dynamic_slots = intval($_POST['dynamic_slots']);
    $custom_message = sanitize_textarea_field($_POST['custom_message']);
    $static_slots = intval($_POST['static_slots']);

    $wpdb->replace($settings_table, ['setting_key' => 'next_reservation_date', 'setting_value' => $next_reservation_date]);
    $wpdb->replace($settings_table, ['setting_key' => 'max_dynamic_slots', 'setting_value' => $dynamic_slots]);
    $wpdb->replace($settings_table, ['setting_key' => 'custom_message', 'setting_value' => $custom_message]);
    $wpdb->replace($settings_table, ['setting_key' => 'max_static_slots', 'setting_value' => $static_slots]);

    // Save time slots
    $time_slots = $_POST['time_slots'];
    $wpdb->replace($settings_table, ['setting_key' => 'time_slot', 'setting_value' => json_encode($time_slots)]);

    echo '<div class="notice notice-success is-dismissible"><p>Ustawienia zostały zapisane.</p></div>';
}
?>

<div class="wrap">
    <h1>Ustawienia Systemu</h1>
    <form method="POST">
        <?php wp_nonce_field('srbs_save_settings'); ?>

        <table class="form-table">
            <tr>
                <th><label for="next_reservation_date">Data następnej rezerwacji:</label></th>
                <td><input type="date" id="next_reservation_date" name="next_reservation_date" value="<?php echo esc_attr($next_reservation_date); ?>" required></td>
            </tr>
            <tr>
                <th><label for="static_slots">Maksymalna liczba miejsc na strzelanie statyczne:</label></th>
                <td><input type="number" id="static_slots" name="static_slots" value="<?php echo esc_attr($static_slots); ?>" min="1" max="10" required></td>
            </tr>
            <tr>
                <th><label for="dynamic_slots">Maksymalna liczba miejsc na strzelanie dynamiczne:</label></th>
                <td><input type="number" id="dynamic_slots" name="dynamic_slots" value="<?php echo esc_attr($dynamic_slots); ?>" min="1" max="10" required></td>
            </tr>
            <tr>
                <th><label for="custom_message">Komunikat dla użytkowników:</label></th>
                <td>
                    <textarea id="custom_message" name="custom_message" rows="5" cols="50"><?php echo esc_textarea($custom_message); ?></textarea>
                </td>
            </tr>
            <tr>
                <th colspan="2"><h2>Sloty Czasowe</h2></th>
            </tr>
            <tbody id="time-slots-container">
                <?php if (!empty($time_slots)): ?>
                    <?php foreach ($time_slots as $i => $slot): ?>
                        <tr>
                            <th><label for="time_slots_<?php echo $i; ?>_range">Zakres godzin:</label></th>
                            <td><input type="text" id="time_slots_<?php echo $i; ?>_range" name="time_slots[<?php echo $i; ?>][range]" value="<?php echo esc_attr($slot['range']); ?>" required></td>
                        </tr>
                        <tr>
                            <th><label for="time_slots_<?php echo $i; ?>_type">Rodzaj strzelania:</label></th>
                            <td>
                                <select id="time_slots_<?php echo $i; ?>_type" name="time_slots[<?php echo $i; ?>][type]" required>
                                    <option value="static" <?php selected($slot['type'], 'static'); ?>>Statyczne</option>
                                    <option value="dynamic" <?php selected($slot['type'], 'dynamic'); ?>>Dynamiczne</option>
                                </select>
                                <button type="button" class="button remove-time-slot">Usuń</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <button type="button" class="button" id="add-time-slot">Dodaj Slot Czasowy</button>

        <p class="submit">
            <input type="submit" class="button-primary" value="Zapisz ustawienia">
        </p>
    </form>
</div>

<script>
    var srbs_ajax = {
        timeSlotIndex: <?php echo count($time_slots); ?>
    };
</script>
