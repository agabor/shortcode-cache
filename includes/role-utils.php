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