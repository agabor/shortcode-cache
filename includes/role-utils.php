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