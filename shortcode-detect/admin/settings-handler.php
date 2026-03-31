<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter( 'sanitize_option_shortcode_detect_monitored_url', 'shortcode_detect_sanitize_monitored_url' );

function shortcode_detect_sanitize_monitored_url( $value ) {
    $value = trim( $value );

    if ( empty( $value ) ) {
        return '';
    }

    if ( ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
        add_settings_error(
            'shortcode_detect_monitored_url',
            'invalid_url',
            __( 'Please enter a valid URL.', 'shortcode-detect' )
        );
        return get_option( 'shortcode_detect_monitored_url', '' );
    }

    return esc_url_raw( $value );
}