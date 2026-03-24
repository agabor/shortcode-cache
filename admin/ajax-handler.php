<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function shortcode_cache_handle_clear_cache() {
    check_ajax_referer( 'shortcode_cache_nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'shortcode-cache' ) ) );
    }

    $cache_key = isset( $_POST['cache_key'] ) ? sanitize_text_field( $_POST['cache_key'] ) : '';

    if ( empty( $cache_key ) ) {
        wp_send_json_error( array( 'message' => __( 'Invalid cache key', 'shortcode-cache' ) ) );
    }

    $success = shortcode_cache_clear_specific_cache( $cache_key );

    if ( $success ) {
        wp_send_json_success( array( 'message' => __( 'Cache cleared successfully', 'shortcode-cache' ) ) );
    } else {
        wp_send_json_error( array( 'message' => __( 'Failed to clear cache', 'shortcode-cache' ) ) );
    }
}

function shortcode_cache_handle_clear_all_cache() {
    check_ajax_referer( 'shortcode_cache_nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'shortcode-cache' ) ) );
    }

    shortcode_cache_clear_all_cache();

    wp_send_json_success( array( 'message' => __( 'All cache cleared successfully', 'shortcode-cache' ) ) );
}