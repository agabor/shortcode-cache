<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter( 'sanitize_option_shortcode_cache_config', 'shortcode_cache_sanitize_shortcode_config' );
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

        if ( isset( $item['id'] ) ) {
            $sanitized_item['id'] = sanitize_text_field( $item['id'] );
        }

        if ( isset( $item['cache_by_role'] ) ) {
            $sanitized_item['cache_by_role'] = (bool) $item['cache_by_role'];
        } else {
            $sanitized_item['cache_by_role'] = false;
        }

        if ( ! empty( $sanitized_item['name'] ) ) {
            $sanitized_config[] = $sanitized_item;
        }
    }

    return $sanitized_config;
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