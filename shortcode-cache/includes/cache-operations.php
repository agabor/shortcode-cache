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

function shortcode_cache_get_size( $cache_key, $group ) {
    $content = shortcode_cache_get( $cache_key, $group );

    if ( false === $content ) {
        return 0;
    }

    return strlen( $content );
}

function shortcode_cache_format_bytes( $bytes, $precision = 2 ) {
    $bytes = (int) $bytes;
    $units = array( 'B', 'KB', 'MB', 'GB' );

    $bytes = max( $bytes, 0 );
    $pow = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
    $pow = min( $pow, count( $units ) - 1 );
    $bytes /= ( 1 << ( 10 * $pow ) );

    return round($bytes, $precision) . ' cache-operations.php' . $units[ $pow ];
}

function shortcode_cache_get_items() {
    if ( wp_using_ext_object_cache() ) {
        $items = wp_cache_get( 'shortcode_cache_items', 'shortcode_cache' );
        return ( false === $items || ! is_array( $items ) ) ? array() : $items;
    }

    $items = get_transient( 'shortcode_cache_items' );
    return ( false === $items || ! is_array( $items ) ) ? array() : $items;
}

function shortcode_cache_set_items( $items ) {
    if ( ! is_array( $items ) ) {
        $items = array();
    }

    if ( wp_using_ext_object_cache() ) {
        wp_cache_set( 'shortcode_cache_items', $items, 'shortcode_cache', DAY_IN_SECONDS );
    } else {
        set_transient( 'shortcode_cache_items', $items, DAY_IN_SECONDS );
    }
}

function shortcode_cache_delete_items() {
    if ( wp_using_ext_object_cache() ) {
        wp_cache_delete( 'shortcode_cache_items', 'shortcode_cache' );
    } else {
        delete_transient( 'shortcode_cache_items' );
    }
}