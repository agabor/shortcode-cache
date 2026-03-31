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

    return round( $bytes, $precision ) . ' ' . $units[ $pow ];
}