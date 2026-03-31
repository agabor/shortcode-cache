<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function shortcode_cache_handle_clear_cache() {
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
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'shortcode-cache' ) ) );
    }

    shortcode_cache_clear_all_cache();

    wp_send_json_success( array( 'message' => __( 'All cache cleared successfully', 'shortcode-cache' ) ) );
}

function shortcode_cache_handle_clear_detected_shortcodes() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'shortcode-cache' ) ) );
    }

    shortcode_cache_clear_detected_shortcodes();

    wp_send_json_success( array( 'message' => __( 'Detected shortcodes cleared successfully', 'shortcode-cache' ) ) );
}

function shortcode_cache_handle_add_shortcode() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'shortcode-cache' ) ) );
    }

    $shortcode_name = isset( $_POST['shortcode_name'] ) ? sanitize_text_field( $_POST['shortcode_name'] ) : '';

    if ( empty( $shortcode_name ) ) {
        wp_send_json_error( array( 'message' => __( 'Shortcode name cannot be empty', 'shortcode-cache' ) ) );
    }

    $config = get_option( 'shortcode_cache_config', array() );

    if ( ! is_array( $config ) ) {
        $config = array();
    }

    foreach ( $config as $item ) {
        if ( isset( $item['name'] ) && $item['name'] === $shortcode_name ) {
            wp_send_json_error( array( 'message' => __( 'This shortcode is already in the list', 'shortcode-cache' ) ) );
        }
    }

    $new_item = array(
        'name' => $shortcode_name,
    );

    $config[] = $new_item;

    update_option( 'shortcode_cache_config', $config );

    wp_send_json_success( array(
        'message' => __( 'Shortcode added successfully', 'shortcode-cache' ),
        'index' => count( $config ) - 1,
    ) );
}

function shortcode_cache_handle_delete_shortcode() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'shortcode-cache' ) ) );
    }

    $index = isset( $_POST['index'] ) ? intval( $_POST['index'] ) : -1;

    if ( $index < 0 ) {
        wp_send_json_error( array( 'message' => __( 'Invalid index', 'shortcode-cache' ) ) );
    }

    $config = get_option( 'shortcode_cache_config', array() );

    if ( ! is_array( $config ) || ! isset( $config[ $index ] ) ) {
        wp_send_json_error( array( 'message' => __( 'Shortcode not found', 'shortcode-cache' ) ) );
    }

    unset( $config[ $index ] );
    $config = array_values( $config );

    update_option( 'shortcode_cache_config', $config );

    wp_send_json_success( array( 'message' => __( 'Shortcode deleted successfully', 'shortcode-cache' ) ) );
}

function shortcode_cache_handle_update_global_roles() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'shortcode-cache' ) ) );
    }

    $selected_roles = isset( $_POST['selected_roles'] ) ? (array) $_POST['selected_roles'] : array();

    $available_roles = shortcode_cache_get_all_roles();
    $sanitized_roles = array();

    foreach ( $selected_roles as $role ) {
        $role = sanitize_text_field( $role );
        if ( isset( $available_roles[ $role ] ) ) {
            $sanitized_roles[] = $role;
        }
    }

    update_option( 'shortcode_cache_global_roles', $sanitized_roles );

    wp_send_json_success( array( 'message' => __( 'Global roles updated successfully', 'shortcode-cache' ) ) );
}

function shortcode_cache_handle_get_available_roles() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'shortcode-cache' ) ) );
    }

    $all_roles = shortcode_cache_get_all_roles();

    wp_send_json_success( array( 'roles' => $all_roles ) );
}