<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter( 'sanitize_option_shortcode_cache_monitored_url', 'shortcode_cache_sanitize_monitored_url' );

function shortcode_cache_sanitize_monitored_url( $value ) {
    $value = trim( $value );

    if ( empty( $value ) ) {
        return '';
    }

    if ( ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
        add_settings_error(
            'shortcode_cache_monitored_url',
            'invalid_url',
            __( 'Please enter a valid URL.', 'shortcode-cache' )
        );
        return get_option( 'shortcode_cache_monitored_url', '' );
    }

    return esc_url_raw( $value );
}