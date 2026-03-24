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

        $output = wp_cache_get( $cache_key, $group );

        if ( false === $output ) {
            $output = call_user_func( $original_callback, $atts );
            wp_cache_set( $cache_key, $output, $group, HOUR_IN_SECONDS );
        }

        return $output;
    };
}

function shortcode_cache_generate_cache_key( $shortcode_name, $atts ) {
    $atts = (array) $atts;
    $serialized = serialize( $atts );
    $hash = md5( $serialized );

    return 'shortcode_' . $shortcode_name . '_' . $hash;
}