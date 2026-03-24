<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter( 'sanitize_option_shortcode_cache_list', 'shortcode_cache_sanitize_shortcode_names' );

function shortcode_cache_sanitize_shortcode_names( $value ) {
    if ( empty( $value ) ) {
        return '';
    }

    $shortcode_list = array_map( 'trim', explode( "\n", $value ) );
    $shortcode_list = array_filter( $shortcode_list );

    $sanitized_list = array_map( function( $shortcode ) {
        return sanitize_text_field( $shortcode );
    }, $shortcode_list );

    return implode( "\n", $sanitized_list );
}