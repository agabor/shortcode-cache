<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function shortcode_cache_get( $cache_key, $group ) {
    if ( wp_using_ext_object_cache() ) {
        return wp_cache_get( $cache_key, $group );
    }

    return get_transient( $cache_key );
}

function shortcode_cache_set( $cache_key, $output, $group, $expiration ) {
    if ( wp_using_ext_object_cache() ) {
        wp_cache_set( $cache_key, $output, $group, $expiration );
    } else {
        set_transient( $cache_key, $output, $expiration );
    }
}

function shortcode_cache_delete( $cache_key, $group ) {
    if ( wp_using_ext_object_cache() ) {
        return wp_cache_delete( $cache_key, $group );
    }

    return delete_transient( $cache_key );
}

function shortcode_cache_flush() {
    if ( wp_using_ext_object_cache() ) {
        wp_cache_flush();
    }
}