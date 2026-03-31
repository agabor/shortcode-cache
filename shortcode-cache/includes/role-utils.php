<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function shortcode_cache_get_all_roles() {
    global $wp_roles;

    if ( ! isset( $wp_roles ) ) {
        $wp_roles = new WP_Roles();
    }

    $roles = array();

    foreach ( $wp_roles->roles as $role_slug => $role_data ) {
        $roles[ $role_slug ] = $role_data['name'];
    }

    return $roles;
}

function shortcode_cache_get_global_allowed_roles() {
    $allowed_roles = get_option( 'shortcode_cache_global_roles', array() );

    if ( ! is_array( $allowed_roles ) ) {
        return array();
    }

    return $allowed_roles;
}

function shortcode_cache_is_global_role_caching_enabled() {
    $allowed_roles = shortcode_cache_get_global_allowed_roles();
    return ! empty( $allowed_roles );
}

function shortcode_cache_is_role_caching_enabled_for_shortcode( $shortcode_name, $configured_id = null ) {
    $config = get_option( 'shortcode_cache_config', array() );

    if ( ! is_array( $config ) ) {
        return false;
    }

    foreach ( $config as $item ) {
        if ( isset( $item['name'] ) && $item['name'] === $shortcode_name ) {
            $id_matches = true;
            if ( null !== $configured_id ) {
                $id_matches = isset( $item['id'] ) && $item['id'] === $configured_id;
            } else {
                $id_matches = ! isset( $item['id'] ) || empty( $item['id'] );
            }

            if ( $id_matches ) {
                return isset( $item['cache_by_role'] ) && (bool) $item['cache_by_role'];
            }
        }
    }

    return false;
}