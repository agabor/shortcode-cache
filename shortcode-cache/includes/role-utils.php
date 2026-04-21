<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function shortcode_cache_get_all_roles() {
    global $wp_roles;

    if ( ! isset( $wp_roles ) ) {
        $wp_roles = new WP_Roles();
    }

    return $wp_roles->get_names();
}

function shortcode_cache_get_global_allowed_roles() {
    $allowed_roles = get_option( 'shortcode_cache_global_roles', array() );

    if ( ! is_array( $allowed_roles ) ) {
        return array();
    }

    return $allowed_roles;
}

function shortcode_cache_is_role_caching_enabled_for_shortcode( $shortcode_name, $configured_id = null ) {
    $config = get_option( 'shortcode_cache_config', array() );

    if ( ! is_array( $config ) ) {
        return false;
    }

    foreach ( $config as $item ) {
        if ( isset( $item['name'] ) && $item['name'] === $shortcode_name ) {
            if ( null !== $configured_id ) {
                if ( isset( $item['id'] ) && $item['id'] === $configured_id ) {
                    return isset( $item['cache_by_role'] ) && $item['cache_by_role'];
                }
            } else {
                return isset( $item['cache_by_role'] ) && $item['cache_by_role'];
            }
        }
    }

    return false;
}

function shortcode_cache_is_page_caching_enabled_for_shortcode( $shortcode_name, $configured_id = null ) {
    $config = get_option( 'shortcode_cache_config', array() );

    if ( ! is_array( $config ) ) {
        return false;
    }

    foreach ( $config as $item ) {
        if ( isset( $item['name'] ) && $item['name'] === $shortcode_name ) {
            if ( null !== $configured_id ) {
                if ( isset( $item['id'] ) && $item['id'] === $configured_id ) {
                    return isset( $item['cache_by_page'] ) && $item['cache_by_page'];
                }
            } else {
                return isset( $item['cache_by_page'] ) && $item['cache_by_page'];
            }
        }
    }

    return false;
}