jQuery(document).ready(function ($) {
    let timeSlotIndex = srbs_ajax.timeSlotIndex || 0;

    $('#add-time-slot').on('click', function () {
        const container = $('#time-slots-container');
        const newRow = `
            <tr>
                <td>
                    <input type="text" id="time_slots_${timeSlotIndex}_range" name="time_slots[${timeSlotIndex}][range]" required>
                </td>
                <td>
                    <select id="time_slots_${timeSlotIndex}_type" name="time_slots[${timeSlotIndex}][type]" required>
                        <option value="static"><?php _e('Static', 'srbs'); ?></option>
                        <option value="dynamic"><?php _e('Dynamic', 'srbs'); ?></option>
                    </select>
                </td>
                <td>
                    <button type="button" class="button remove-time-slot"><?php _e('Remove', 'srbs'); ?></button>
                </td>
            </tr>
        `;
        container.append(newRow);
        timeSlotIndex++;
    });

    $('#time-slots-container').on('click', '.remove-time-slot', function () {
        $(this).closest('tr').remove();
    });
});
