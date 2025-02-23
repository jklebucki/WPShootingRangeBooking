<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(dirname(__FILE__)) . 'includes/functions.php';

$current_user_id = get_current_user_id();
$static_slots = srbs_get_setting('max_static_slots');
$dynamic_slots = srbs_get_setting('max_dynamic_slots');
$time_slots = srbs_get_time_slots();
?>

<table class="srbs-booking-table">
    <thead>
        <tr>
            <th>Godzina</th>
            <?php for ($i = 1; $i <= $static_slots; $i++): ?>
                <th>St. <?php echo $i; ?></th>
            <?php endfor; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($time_slots as $time_slot): ?>
            <tr>
                <td data-label="Godzina"><?php echo esc_html($time_slot['range']); ?></td>
                <?php if ($time_slot['type'] == 'static'): ?>
                    <?php for ($i = 1; $i <= $static_slots; $i++): ?>
                        <td data-label="St. <?php echo $i; ?>">
                            <?php
                            $booking = srbs_is_slot_booked($bookings, $i, $time_slot['range']);
                            if ($booking): ?>
                                <span class="badge">#<?php echo esc_html($booking->club_number); ?>
                                    <?php if ($booking->user_id == $current_user_id): ?>
                                        <button class="srbs-cancel-booking" data-booking-id="<?php echo $booking->id; ?>">x</button>
                                    <?php endif; ?>
                                </span>
                            <?php else: ?>
                                <button class="srbs-book-slot" data-stand="<?php echo $i; ?>" data-time="<?php echo $time_slot['range']; ?>">Rezerwuj</button>
                            <?php endif; ?>
                        </td>
                    <?php endfor; ?>
                <?php else: ?>
                    <td colspan="<?php echo $static_slots; ?>">
                        <?php
                        $dynamic_bookings = array_filter($bookings, function ($booking) use ($time_slot) {
                            return $booking->time_slot == $time_slot['range'] && $booking->booking_type == 'dynamic';
                        });

                        if (count($dynamic_bookings) >= $dynamic_slots): ?>
                            <span>Wszystkie miejsca zajęte</span>
                        <?php else: ?>
                            <button class="srbs-book-slot" style="margin-bottom: 3px !important;" data-time="<?php echo $time_slot['range']; ?>" data-dynamic="true">Rezerwuj</button>
                        <?php endif; ?>

                        <?php if (!empty($dynamic_bookings)): ?>
                            <strong>Uczestnicy:</strong>
                            <?php foreach ($dynamic_bookings as $booking): ?>
                                <span class="badge">#<?php echo esc_html($booking->club_number); ?>
                                    <?php if ($booking->user_id == $current_user_id): ?>
                                        <button class="srbs-cancel-booking" data-booking-id="<?php echo $booking->id; ?>">x</button>
                                    <?php endif; ?>
                                </span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span>Brak zapisanych uczestników.</span>
                        <?php endif; ?>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>