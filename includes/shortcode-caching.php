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

    $shortcode_tags[ $shortcode_name ] = function( $atts = array(), $content = '', $tag = '' ) use ( $original_callback, $shortcode_name ) {
        $should_use_cache = shortcode_cache_should_use_cache( $shortcode_name );

        if ( ! $should_use_cache ) {
            return call_user_func( $original_callback, $atts, $content, $tag );
        }

        $cache_key = shortcode_cache_generate_cache_key( $shortcode_name, $atts );
        $group     = 'shortcode_cache';

        $output = shortcode_cache_get( $cache_key, $group );

        if ( false === $output ) {
            $output = call_user_func( $original_callback, $atts, $content, $tag );
            shortcode_cache_set( $cache_key, $output, $group, HOUR_IN_SECONDS );
            shortcode_cache_track_cached_item( $cache_key, $shortcode_name, $atts );
        }

        return $output;
    };
}

function shortcode_cache_wrap_shortcode_for_detection( $shortcode_name ) {
    global $shortcode_tags;

    if ( ! isset( $shortcode_tags[ $shortcode_name ] ) ) {
        return;
    }

    $original_callback = $shortcode_tags[ $shortcode_name ];

    $shortcode_tags[ $shortcode_name ] = function( $atts = array(), $content = '', $tag = '' ) use ( $original_callback, $shortcode_name ) {
        shortcode_cache_track_shortcode_execution( $shortcode_name );
        return call_user_func( $original_callback, $atts, $content, $tag );
    };
}

function shortcode_cache_should_use_cache( $shortcode_name ) {
    $config = get_option( 'shortcode_cache_config', array() );

    if ( ! is_array( $config ) ) {
        return true;
    }

    foreach ( $config as $item ) {
        if ( ! isset( $item['name'] ) || $item['name'] !== $shortcode_name ) {
            continue;
        }

        $allowed_roles = isset( $item['allowed_roles'] ) ? $item['allowed_roles'] : array();

        if ( ! is_array( $allowed_roles ) ) {
            $allowed_roles = array();
        }

        $current_user = wp_get_current_user();

        if ( 0 === $current_user->ID ) {
            return true;
        }

        if ( empty( $allowed_roles ) ) {
            return true;
        }

        $user_roles = $current_user->roles;

        foreach ( $user_roles as $user_role ) {
            if ( in_array( $user_role, $allowed_roles, true ) ) {
                return true;
            }
        }

        return false;
    }

    return true;
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

function shortcode_cache_track_shortcode_execution( $shortcode_name ) {
    $detected_shortcodes = get_transient( 'shortcode_cache_detected_shortcodes' );

    if ( false === $detected_shortcodes ) {
        $detected_shortcodes = array();
    }

    if ( ! is_array( $detected_shortcodes ) ) {
        $detected_shortcodes = array();
    }

    if ( ! isset( $detected_shortcodes[ $shortcode_name ] ) ) {
        $detected_shortcodes[ $shortcode_name ] = 0;
    }

    $detected_shortcodes[ $shortcode_name ]++;

    set_transient( 'shortcode_cache_detected_shortcodes', $detected_shortcodes, WEEK_IN_SECONDS );
}