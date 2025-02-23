<?php

if (!defined('ABSPATH')) {
    exit;
}

// Handle AJAX requests for settings page
function srbs_handle_settings_ajax()
{
    check_ajax_referer('srbs_nonce', 'nonce');

    // Handle the AJAX request here

    wp_send_json_success();
}
add_action('wp_ajax_srbs_handle_settings', 'srbs_handle_settings_ajax');
