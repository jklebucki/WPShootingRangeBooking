<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'srbs_bookings';
$bookings = $wpdb->get_results("SELECT * FROM $table_name ORDER BY date DESC, time_slot");

?>
<div class="wrap">
    <h1><?php _e('Shooting Range Reservations', 'srbs'); ?></h1>
    <p><?php _e('Management panel for shooting range reservations.', 'srbs'); ?></p>

    <div class="filter-wrapper">
        <label for="filter-date"><?php _e('Filter by date:', 'srbs'); ?></label>
        <input type="date" id="filter-date" name="filter-date">
        <button class="button button-primary" style="margin: 0px !important;" id="apply-filter"><?php _e('Filter', 'srbs'); ?></button>
    </div>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('Date', 'srbs'); ?></th>
                <th><?php _e('User', 'srbs'); ?></th>
                <th><?php _e('Club Number', 'srbs'); ?></th>
                <th><?php _e('Time', 'srbs'); ?></th>
                <th><?php _e('Stand', 'srbs'); ?></th>
                <th><?php _e('Type', 'srbs'); ?></th>
                <th><?php _e('Actions', 'srbs'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $grouped_bookings = [];
            foreach ($bookings as $booking) {
                $grouped_bookings[$booking->date][] = $booking;
            }

            foreach ($grouped_bookings as $date => $bookings_list): ?>
                <tr class="grouped-row" data-date="<?php echo esc_attr($date); ?>">
                    <td colspan="7"><strong><?php echo esc_html($date); ?></strong></td>
                </tr>
                <?php foreach ($bookings_list as $booking): ?>
                    <tr class="details-row" data-date="<?php echo esc_attr($booking->date); ?>" data-id="<?php echo $booking->id; ?>" style="display: none;">
                        <td><?php echo esc_attr($booking->date); ?></td>
                        <td><?php echo get_userdata($booking->user_id)->display_name; ?></td>
                        <td><?php echo esc_html($booking->club_number); ?></td>
                        <td><?php echo esc_html($booking->time_slot); ?></td>
                        <td><?php echo esc_html($booking->stand_number); ?></td>
                        <td><?php echo esc_html(ucfirst($booking->booking_type)); ?></td>
                        <td>
                            <button class="button delete-booking" data-id="<?php echo $booking->id; ?>"><?php _e('Delete', 'srbs'); ?></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".grouped-row").forEach(row => {
            row.addEventListener("click", function() {
                let date = this.getAttribute("data-date");
                document.querySelectorAll(`.details-row[data-date='${date}']`).forEach(detailRow => {
                    detailRow.style.display = detailRow.style.display === "none" ? "table-row" : "none";
                });
            });
        });

        document.getElementById("apply-filter").addEventListener("click", function() {
            let selectedDate = document.getElementById("filter-date").value;
            document.querySelectorAll(".grouped-row, .details-row").forEach(row => {
                row.style.display = row.getAttribute("data-date") === selectedDate ? "table-row" : "none";
            });
        });
    });
</script>