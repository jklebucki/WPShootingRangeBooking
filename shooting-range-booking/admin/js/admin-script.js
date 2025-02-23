jQuery(document).ready(function ($) {
    $(".delete-booking").on("click", function () {
        var bookingId = $(this).data("id");
        $.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                action: "delete_booking",
                booking_id: bookingId,
                security: srbs_ajax.nonce
            },
            success: function (response) {
                if (response.success) {
                    //alert("Rezerwacja została usunięta.");
                    $(`.details-row[data-id='${bookingId}']`).remove();
                } else {
                    alert("Wystąpił błąd: " + response.data);
                }
            }
        });
    });

    let timeSlotIndex = srbs_ajax.timeSlotIndex || 0;

    $('#add-time-slot').on('click', function () {
        const container = $('#time-slots-container');
        const newRow = `
            <tr>
                <th><label for="time_slots_${timeSlotIndex}_range">Zakres godzin:</label></th>
                <td><input type="text" id="time_slots_${timeSlotIndex}_range" name="time_slots[${timeSlotIndex}][range]" required></td>
            </tr>
            <tr>
                <th><label for="time_slots_${timeSlotIndex}_type">Rodzaj strzelania:</label></th>
                <td>
                    <select id="time_slots_${timeSlotIndex}_type" name="time_slots[${timeSlotIndex}][type]" required>
                        <option value="static">Statyczne</option>
                        <option value="dynamic">Dynamiczne</option>
                    </select>
                    <button type="button" class="button remove-time-slot">Usuń</button>
                </td>
            </tr>
        `;
        container.append(newRow);
        timeSlotIndex++;
    });

    $('#time-slots-container').on('click', '.remove-time-slot', function () {
        $(this).closest('tr').next().remove();
        $(this).closest('tr').remove();
    });
});
