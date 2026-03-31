<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function shortcode_cache_get_all_cached_items() {
    $cached_items = shortcode_cache_get_items();

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
    $parts = array();

    $parameters = isset( $item_data['parameters'] ) ? $item_data['parameters'] : array();

    if ( ! empty( $parameters ) ) {
        foreach ( $parameters as $key => $value ) {
            if ( 'id' !== $key ) {
                $parts[] = sprintf( '%s=%s', esc_html( $key ), esc_html( $value ) );
            }
        }
    }

    if ( isset( $item_data['cached_for_role'] ) ) {
        $parts[] = sprintf( 'role=%s', esc_html( $item_data['cached_for_role'] ) );
    }

    if ( empty( $parts ) ) {
        return '—';
    }

    return implode( ', ', $parts );
}

function shortcode_cache_clear_specific_cache( $cache_key ) {
    $group = 'shortcode_cache';
    
    $success = shortcode_cache_delete( $cache_key, $group );

    if ( $success ) {
        $cached_items = shortcode_cache_get_items();

        if ( isset( $cached_items[ $cache_key ] ) ) {
            unset( $cached_items[ $cache_key ] );
            shortcode_cache_set_items( $cached_items );
        }
    }

    return $success;
}

function shortcode_cache_clear_all_cache() {
    $cached_items = shortcode_cache_get_items();
    
    foreach ( $cached_items as $cache_key => $item_data ) {
        shortcode_cache_delete( $cache_key, 'shortcode_cache' );
    }
    
    shortcode_cache_flush();
    shortcode_cache_delete_items();
}

function shortcode_cache_get_cached_item_content( $cache_key ) {
    $group = 'shortcode_cache';
    $content = shortcode_cache_get( $cache_key, $group );

    if ( false === $content ) {
        return null;
    }

    return $content;
}