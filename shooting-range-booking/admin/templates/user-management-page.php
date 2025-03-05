<?php
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have permission.', 'srbs'));
}

global $wpdb;
$users = get_users();

$sort_by = isset($_GET['sort_by']) ? sanitize_text_field($_GET['sort_by']) : 'ID';
$order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'asc' : 'desc';

usort($users, function ($a, $b) use ($sort_by, $order) {
    if ($sort_by === 'club_number') {
        $a_meta = get_user_meta($a->ID, 'club_number', true);
        $b_meta = get_user_meta($b->ID, 'club_number', true);
        if ($a_meta == $b_meta) {
            return 0;
        }
        return ($order === 'asc' ? ($a_meta < $b_meta) : ($a_meta > $b_meta)) ? -1 : 1;
    } else {
        if ($a->$sort_by == $b->$sort_by) {
            return 0;
        }
        return ($order === 'asc' ? ($a->$sort_by < $b->$sort_by) : ($a->$sort_by > $b->$sort_by)) ? -1 : 1;
    }
});
?>
<div class="wrap srbs-admin">
    <h1><?php _e('User Management', 'srbs'); ?></h1>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th class="sortable-column" data-sort="ID" style="color: white;"><?php _e('ID', 'srbs'); ?></th>
                <th class="sortable-column" data-sort="user_login" style="color: white;"><?php _e('Username', 'srbs'); ?></th>
                <th class="sortable-column" data-sort="user_email" style="color: white;"><?php _e('Email', 'srbs'); ?></th>
                <th class="sortable-column" data-sort="first_name" style="color: white;"><?php _e('First Name', 'srbs'); ?></th>
                <th class="sortable-column" data-sort="last_name" style="color: white;"><?php _e('Last Name', 'srbs'); ?></th>
                <th class="sortable-column" data-sort="club_number" style="color: white;"><?php _e('Club Number', 'srbs'); ?></th>
                <th style="color: white;"><?php _e('Roles', 'srbs'); ?></th>
                <th style="color: white;"><?php _e('Actions', 'srbs'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo esc_html($user->ID); ?></td>
                    <td><?php echo esc_html($user->user_login); ?></td>
                    <td><?php echo esc_html($user->user_email); ?></td>
                    <td><?php echo esc_html($user->first_name); ?></td>
                    <td><?php echo esc_html($user->last_name); ?></td>
                    <td>
                        <input type="text" class="club-number-input" data-user-id="<?php echo esc_attr($user->ID); ?>" value="<?php echo esc_attr(get_user_meta($user->ID, 'club_number', true)); ?>">
                    </td>
                    <td><?php echo implode(', ', $user->roles); ?></td>
                    <td>
                        <?php if (in_array('shooter', $user->roles)): ?>
                            <button class="remove-shooter-role" data-user-id="<?php echo esc_attr($user->ID); ?>"><?php _e('Remove Shooter Role', 'srbs'); ?></button>
                        <?php else: ?>
                            <button class="add-shooter-role" data-user-id="<?php echo esc_attr($user->ID); ?>"><?php _e('Add Shooter Role', 'srbs'); ?></button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>