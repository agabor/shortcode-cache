<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function shortcode_cache_wrap_shortcode_with_cache( $shortcode_name, $shortcode_config = array() ) {
    global $shortcode_tags;

    if ( ! isset( $shortcode_tags[ $shortcode_name ] ) ) {
        return;
    }

    $original_callback = $shortcode_tags[ $shortcode_name ];
    $configured_id = isset( $shortcode_config['id'] ) ? $shortcode_config['id'] : null;

    $shortcode_tags[ $shortcode_name ] = function( $atts = array(), $content = '', $tag = '' ) use ( $original_callback, $shortcode_name, $configured_id, $shortcode_config ) {
        $should_use_cache = shortcode_cache_should_use_cache( $shortcode_name, $configured_id );

        if ( ! $should_use_cache ) {
            return call_user_func( $original_callback, $atts, $content, $tag );
        }

        $role_caching_enabled = shortcode_cache_is_role_caching_enabled_for_shortcode( $shortcode_name, $configured_id );
        $cache_key = shortcode_cache_generate_cache_key( $shortcode_name, $atts, $role_caching_enabled, $configured_id );
        $group     = 'shortcode_cache';

        $output = shortcode_cache_get( $cache_key, $group );

        if ( false === $output ) {
            $output = call_user_func( $original_callback, $atts, $content, $tag );
            shortcode_cache_set( $cache_key, $output, $group, HOUR_IN_SECONDS );
            shortcode_cache_track_cached_item( $cache_key, $shortcode_name, $atts, $role_caching_enabled, $configured_id );
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
        $instance_id = isset( $atts['id'] ) ? $atts['id'] : null;
        shortcode_cache_track_shortcode_execution( $shortcode_name, $instance_id );
        return call_user_func( $original_callback, $atts, $content, $tag );
    };
}

function shortcode_cache_should_use_cache( $shortcode_name, $configured_id = null ) {
    $allowed_roles = shortcode_cache_get_global_allowed_roles();

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

function shortcode_cache_generate_cache_key( $shortcode_name, $atts, $role_caching_enabled = false, $configured_id = null ) {
    $atts = (array) $atts;
    $serialized = serialize( $atts );

    if ( null !== $configured_id ) {
        $serialized = $configured_id . '|' . $serialized;
    } elseif ( isset( $atts['id'] ) && ! empty( $atts['id'] ) ) {
        $serialized = $atts['id'] . '|' . $serialized;
    }

    if ( $role_caching_enabled ) {
        $current_user = wp_get_current_user();
        $user_role = ! empty( $current_user->roles ) ? $current_user->roles[0] : 'guest';
        $serialized .= '|role:' . $user_role;
    }

    $hash = md5( $serialized );

    return 'shortcode_' . $shortcode_name . '_' . $hash;
}

function shortcode_cache_track_cached_item( $cache_key, $shortcode_name, $atts, $role_caching_enabled = false, $configured_id = null ) {
    $cached_items = get_transient( 'shortcode_cache_items' );

    if ( false === $cached_items ) {
        $cached_items = array();
    }

    if ( ! is_array( $cached_items ) ) {
        $cached_items = array();
    }

    $instance_id = null;
    if ( null !== $configured_id ) {
        $instance_id = $configured_id;
    } elseif ( isset( $atts['id'] ) && ! empty( $atts['id'] ) ) {
        $instance_id = $atts['id'];
    }

    $item_data = array(
        'shortcode' => $shortcode_name,
        'parameters' => (array) $atts,
        'timestamp' => time(),
    );

    if ( null !== $instance_id ) {
        $item_data['id'] = $instance_id;
    }

    if ( $role_caching_enabled ) {
        $current_user = wp_get_current_user();
        $user_role = ! empty( $current_user->roles ) ? $current_user->roles[0] : 'guest';
        $item_data['cached_for_role'] = $user_role;
    }

    $cached_items[ $cache_key ] = $item_data;

    set_transient( 'shortcode_cache_items', $cached_items, DAY_IN_SECONDS );
}

function shortcode_cache_track_shortcode_execution( $shortcode_name, $instance_id = null ) {
    $detected_shortcodes = get_transient( 'shortcode_cache_detected_shortcodes' );

    if ( false === $detected_shortcodes ) {
        $detected_shortcodes = array();
    }

    if ( ! is_array( $detected_shortcodes ) ) {
        $detected_shortcodes = array();
    }

    $key = $shortcode_name;
    if ( null !== $instance_id && ! empty( $instance_id ) ) {
        $key = $shortcode_name . '::' . $instance_id;
    }

    if ( ! isset( $detected_shortcodes[ $key ] ) ) {
        $detected_shortcodes[ $key ] = 0;
    }

    $detected_shortcodes[ $key ]++;

    set_transient( 'shortcode_cache_detected_shortcodes', $detected_shortcodes, WEEK_IN_SECONDS );
}