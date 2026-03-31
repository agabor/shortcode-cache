<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter( 'sanitize_option_shortcode_cache_config', 'shortcode_cache_sanitize_shortcode_config' );
add_filter( 'sanitize_option_shortcode_cache_monitored_url', 'shortcode_cache_sanitize_monitored_url' );
add_filter( 'sanitize_option_shortcode_cache_global_roles', 'shortcode_cache_sanitize_global_roles' );

function shortcode_cache_sanitize_shortcode_config( $value ) {
    if ( empty( $value ) ) {
        return array();
    }

    if ( ! is_array( $value ) ) {
        return array();
    }

    $sanitized_config = array();

    foreach ( $value as $item ) {
        if ( ! is_array( $item ) ) {
            continue;
        }

        $sanitized_item = array();

        if ( isset( $item['name'] ) ) {
            $sanitized_item['name'] = sanitize_text_field( $item['name'] );
        }

        if ( ! empty( $sanitized_item['name'] ) ) {
            $sanitized_config[] = $sanitized_item;
        }
    }

    return $sanitized_config;
}

function shortcode_cache_sanitize_monitored_url( $value ) {
    $value = trim( $value );

    if ( empty( $value ) ) {
        return '';
    }

    if ( ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
        add_settings_error(
            'shortcode_cache_monitored_url',
            'invalid_url',
            __( 'Please enter a valid URL.', 'shortcode-cache' )
        );
        return get_option( 'shortcode_cache_monitored_url', '' );
    }

    return esc_url_raw( $value );
}

function shortcode_cache_sanitize_global_roles( $value ) {
    if ( empty( $value ) ) {
        return array();
    }

    if ( ! is_array( $value ) ) {
        return array();
    }

    $available_roles = shortcode_cache_get_all_roles();
    $sanitized_roles = array();

    foreach ( $value as $role ) {
        $role = sanitize_text_field( $role );
        if ( isset( $available_roles[ $role ] ) ) {
            $sanitized_roles[] = $role;
        }
    }

    return $sanitized_roles;
}