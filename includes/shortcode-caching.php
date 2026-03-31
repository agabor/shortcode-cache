<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function shortcode_cache_wrap_shortcode_with_cache( $shortcode_name ) {
    global $shortcode_tags;

    if ( ! isset( $shortcode_tags[ $shortcode_name ] ) ) {
        return;
    }

    $original_callback = $shortcode_tags[ $shortcode_name ];

    $shortcode_tags[ $shortcode_name ] = function( $atts ) use ( $original_callback, $shortcode_name ) {
        $cache_key = shortcode_cache_generate_cache_key( $shortcode_name, $atts );
        $group     = 'shortcode_cache';

        $output = shortcode_cache_get( $cache_key, $group );

        if ( false === $output ) {
            $output = call_user_func( $original_callback, $atts );
            shortcode_cache_set( $cache_key, $output, $group, HOUR_IN_SECONDS );
            shortcode_cache_track_cached_item( $cache_key, $shortcode_name, $atts );
        }

        return $output;
    };
}

function shortcode_cache_generate_cache_key( $shortcode_name, $atts ) {
    $atts = (array) $atts;
    $serialized = serialize( $atts );

    if ( shortcode_cache_is_role_based_enabled( $shortcode_name ) ) {
        $current_user = wp_get_current_user();
        $user_role = ! empty( $current_user->roles ) ? $current_user->roles[0] : 'guest';
        $serialized .= '|role:' . $user_role;
    }

    $hash = md5( $serialized );

    return 'shortcode_' . $shortcode_name . '_' . $hash;
}

function shortcode_cache_is_role_based_enabled( $shortcode_name ) {
    $config = get_option( 'shortcode_cache_config', array() );

    if ( ! is_array( $config ) ) {
        return false;
    }

    foreach ( $config as $item ) {
        if ( isset( $item['name'] ) && $item['name'] === $shortcode_name ) {
            return isset( $item['role_based'] ) ? (bool) $item['role_based'] : false;
        }
    }

    return false;
}

function shortcode_cache_track_cached_item( $cache_key, $shortcode_name, $atts ) {
    $cached_items = get_transient( 'shortcode_cache_items' );

    if ( false === $cached_items ) {
        $cached_items = array();
    }

    if ( ! is_array( $cached_items ) ) {
        $cached_items = array();
    }

    $cached_items[ $cache_key ] = array(
        'shortcode' => $shortcode_name,
        'parameters' => (array) $atts,
        'timestamp' => time(),
    );

    set_transient( 'shortcode_cache_items', $cached_items, DAY_IN_SECONDS );
}