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

function shortcode_cache_handle_clear_group_cache() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'shortcode-cache' ) ) );
    }

    $cache_keys = isset( $_POST['cache_keys'] ) ? (array) $_POST['cache_keys'] : array();

    if ( empty( $cache_keys ) ) {
        wp_send_json_error( array( 'message' => __( 'No cache keys provided', 'shortcode-cache' ) ) );
    }

    $cleared_count = 0;

    foreach ( $cache_keys as $cache_key ) {
        $cache_key = sanitize_text_field( $cache_key );

        if ( empty( $cache_key ) ) {
            continue;
        }

        $success = shortcode_cache_clear_specific_cache( $cache_key );

        if ( $success ) {
            $cleared_count++;
        }
    }

    wp_send_json_success( array(
        'message' => sprintf(
            __( '%d cached item(s) cleared successfully', 'shortcode-cache' ),
            $cleared_count
        ),
        'cleared_count' => $cleared_count,
    ) );
}

function shortcode_cache_handle_add_shortcode() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'shortcode-cache' ) ) );
    }

    $shortcode_name = isset( $_POST['shortcode_name'] ) ? sanitize_text_field( $_POST['shortcode_name'] ) : '';
    $shortcode_id = isset( $_POST['shortcode_id'] ) ? sanitize_text_field( $_POST['shortcode_id'] ) : '';
    $shortcode_note = isset( $_POST['shortcode_note'] ) ? sanitize_textarea_field( $_POST['shortcode_note'] ) : '';

    if ( empty( $shortcode_name ) ) {
        wp_send_json_error( array( 'message' => __( 'Shortcode name cannot be empty', 'shortcode-cache' ) ) );
    }

    $config = get_option( 'shortcode_cache_config', array() );

    if ( ! is_array( $config ) ) {
        $config = array();
    }

    foreach ( $config as $item ) {
        if ( isset( $item['name'] ) && $item['name'] === $shortcode_name ) {
            $item_id = isset( $item['id'] ) ? $item['id'] : '';
            if ( $item_id === $shortcode_id ) {
                wp_send_json_error( array( 'message' => __( 'This shortcode configuration is already in the list', 'shortcode-cache' ) ) );
            }
        }
    }

    $new_item = array(
        'name' => $shortcode_name,
        'cache_by_role' => false,
        'cache_by_page' => false,
        'note' => $shortcode_note,
    );

    if ( ! empty( $shortcode_id ) ) {
        $new_item['id'] = $shortcode_id;
    }

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

    $deleted_item = $config[ $index ];
    $deleted_name = isset( $deleted_item['name'] ) ? $deleted_item['name'] : '';
    $deleted_id = isset( $deleted_item['id'] ) ? $deleted_item['id'] : '';

    unset( $config[ $index ] );
    $config = array_values( $config );

    update_option( 'shortcode_cache_config', $config );

    shortcode_cache_clear_cache_for_config_item( $deleted_name, $deleted_id );

    wp_send_json_success( array( 'message' => __( 'Shortcode deleted successfully', 'shortcode-cache' ) ) );
}

function shortcode_cache_handle_update_shortcode_role_caching() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'shortcode-cache' ) ) );
    }

    $index = isset( $_POST['index'] ) ? intval( $_POST['index'] ) : -1;
    $cache_by_role = isset( $_POST['cache_by_role'] ) ? (bool) $_POST['cache_by_role'] : false;

    if ( $index < 0 ) {
        wp_send_json_error( array( 'message' => __( 'Invalid index', 'shortcode-cache' ) ) );
    }

    $config = get_option( 'shortcode_cache_config', array() );

    if ( ! is_array( $config ) || ! isset( $config[ $index ] ) ) {
        wp_send_json_error( array( 'message' => __( 'Shortcode not found', 'shortcode-cache' ) ) );
    }

    $config[ $index ]['cache_by_role'] = $cache_by_role;

    update_option( 'shortcode_cache_config', $config );

    wp_send_json_success( array( 'message' => __( 'Role caching setting updated successfully', 'shortcode-cache' ) ) );
}

function shortcode_cache_handle_update_shortcode_page_caching() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'shortcode-cache' ) ) );
    }

    $index = isset( $_POST['index'] ) ? intval( $_POST['index'] ) : -1;
    $cache_by_page = isset( $_POST['cache_by_page'] ) ? (bool) $_POST['cache_by_page'] : false;

    if ( $index < 0 ) {
        wp_send_json_error( array( 'message' => __( 'Invalid index', 'shortcode-cache' ) ) );
    }

    $config = get_option( 'shortcode_cache_config', array() );

    if ( ! is_array( $config ) || ! isset( $config[ $index ] ) ) {
        wp_send_json_error( array( 'message' => __( 'Shortcode not found', 'shortcode-cache' ) ) );
    }

    $config[ $index ]['cache_by_page'] = $cache_by_page;

    update_option( 'shortcode_cache_config', $config );

    wp_send_json_success( array( 'message' => __( 'Page caching setting updated successfully', 'shortcode-cache' ) ) );
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

function shortcode_cache_handle_get_cached_content() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'shortcode-cache' ) ) );
    }

    $cache_key = isset( $_POST['cache_key'] ) ? sanitize_text_field( $_POST['cache_key'] ) : '';

    if ( empty( $cache_key ) ) {
        wp_send_json_error( array( 'message' => __( 'Invalid cache key', 'shortcode-cache' ) ) );
    }

    $content = shortcode_cache_get_cached_item_content( $cache_key );

    if ( null === $content ) {
        wp_send_json_error( array( 'message' => __( 'Cache content not found', 'shortcode-cache' ) ) );
    }

    wp_send_json_success( array(
        'content' => $content,
    ) );
}

function shortcode_cache_handle_export_csv() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'shortcode-cache' ) ) );
    }

    $csv_content = shortcode_cache_export_config_to_csv();

    wp_send_json_success( array(
        'csv_content' => $csv_content,
    ) );
}

function shortcode_cache_handle_import_csv() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'shortcode-cache' ) ) );
    }

    if ( ! isset( $_FILES['csv_file'] ) ) {
        wp_send_json_error( array( 'message' => __( 'No file uploaded', 'shortcode-cache' ) ) );
    }

    $file = $_FILES['csv_file'];
    $file_name = $file['name'];
    $file_error = $file['error'];
    $file_tmp = $file['tmp_name'];

    if ( $file_error !== UPLOAD_ERR_OK ) {
        wp_send_json_error( array( 'message' => __( 'File upload error', 'shortcode-cache' ) ) );
    }

    $file_extension = pathinfo( $file_name, PATHINFO_EXTENSION );

    if ( strtolower( $file_extension ) !== 'csv' ) {
        wp_send_json_error( array( 'message' => __( 'Only CSV files are allowed', 'shortcode-cache' ) ) );
    }

    $csv_content = file_get_contents( $file_tmp );

    if ( false === $csv_content ) {
        wp_send_json_error( array( 'message' => __( 'Failed to read file', 'shortcode-cache' ) ) );
    }

    $import_result = shortcode_cache_import_config_from_csv( $csv_content );

    if ( ! $import_result['success'] ) {
        wp_send_json_error( array( 'message' => $import_result['message'] ) );
    }

    $imported_config = $import_result['config'];
    $current_config = get_option( 'shortcode_cache_config', array() );

    if ( ! is_array( $current_config ) ) {
        $current_config = array();
    }

    $merge_mode = isset( $_POST['merge_mode'] ) ? sanitize_text_field( $_POST['merge_mode'] ) : 'replace';

    if ( 'replace' === $merge_mode ) {
        $final_config = $imported_config;
    } else {
        $final_config = array_merge( $current_config, $imported_config );
    }

    update_option( 'shortcode_cache_config', $final_config );

    $warning_message = '';

    if ( ! empty( $import_result['errors'] ) ) {
        $warning_message = ' ' . sprintf(
            __( 'Some rows were skipped: %s', 'shortcode-cache' ),
            implode( ' | ', $import_result['errors'] )
        );
    }

    wp_send_json_success( array(
        'message' => $import_result['message'] . $warning_message,
        'imported_count' => count( $imported_config ),
    ) );
}