<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function shortcode_cache_get_all_cached_items() {
    $cached_items = get_transient( 'shortcode_cache_items' );

    if ( false === $cached_items || ! is_array( $cached_items ) ) {
        return array();
    }

    $items_with_status = array();

    foreach ( $cached_items as $cache_key => $item_data ) {
        $group = 'shortcode_cache';
        
        $cached_value = shortcode_cache_get( $cache_key, $group );

        if ( false !== $cached_value ) {
            $items_with_status[ $cache_key ] = $item_data;
        }
    }

    return $items_with_status;
}

function shortcode_cache_extract_parameters_from_item( $item_data ) {
    $parameters = isset( $item_data['parameters'] ) ? $item_data['parameters'] : array();

    if ( empty( $parameters ) ) {
        return '—';
    }

    $param_strings = array();

    foreach ( $parameters as $key => $value ) {
        $param_strings[] = sprintf( '%s=%s', esc_html( $key ), esc_html( $value ) );
    }

    return implode( ', ', $param_strings );
}

function shortcode_cache_clear_specific_cache( $cache_key ) {
    $group = 'shortcode_cache';
    
    $success = shortcode_cache_delete( $cache_key, $group );

    if ( $success ) {
        $cached_items = get_transient( 'shortcode_cache_items' );

        if ( is_array( $cached_items ) && isset( $cached_items[ $cache_key ] ) ) {
            unset( $cached_items[ $cache_key ] );
            set_transient( 'shortcode_cache_items', $cached_items, DAY_IN_SECONDS );
        }
    }

    return $success;
}

function shortcode_cache_clear_all_cache() {
    $cached_items = get_transient( 'shortcode_cache_items' );
    
    if ( is_array( $cached_items ) ) {
        foreach ( $cached_items as $cache_key => $item_data ) {
            shortcode_cache_delete( $cache_key, 'shortcode_cache' );
        }
    }
    
    shortcode_cache_flush();
    delete_transient( 'shortcode_cache_items' );
}