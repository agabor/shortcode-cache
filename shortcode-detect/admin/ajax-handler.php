<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function shortcode_cache_handle_clear_detected_shortcodes() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'shortcode-cache' ) ) );
    }

    shortcode_cache_clear_detected_shortcodes();

    wp_send_json_success( array( 'message' => __( 'Detected shortcodes cleared successfully', 'shortcode-cache' ) ) );
}