<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function shortcode_cache_get_monitored_url() {
    return get_option( 'shortcode_cache_monitored_url', '' );
}

function shortcode_cache_set_monitored_url( $url ) {
    $url = trim( $url );

    if ( empty( $url ) ) {
        delete_option( 'shortcode_cache_monitored_url' );
        return true;
    }

    if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
        return false;
    }

    update_option( 'shortcode_cache_monitored_url', $url );
    return true;
}

function shortcode_cache_is_monitored_page() {
    if ( is_admin() ) {
        return false;
    }

    $monitored_url = shortcode_cache_get_monitored_url();

    if ( empty( $monitored_url ) ) {
        return false;
    }

    $current_url = home_url( $_SERVER['REQUEST_URI'] );
    $monitored_url = rtrim( $monitored_url, '/' );
    $current_url = rtrim( $current_url, '/' );

    return $current_url === $monitored_url;
}

function shortcode_cache_get_detected_shortcodes() {
    $detected = get_transient( 'shortcode_cache_detected_shortcodes' );

    if ( false === $detected || ! is_array( $detected ) ) {
        return array();
    }

    return $detected;
}

function shortcode_cache_detect_current_page_shortcodes() {
    if ( ! shortcode_cache_is_monitored_page() ) {
        return;
    }

    global $post;

    if ( ! $post || empty( $post->post_content ) ) {
        return;
    }

    $content = $post->post_content;
    $detected_shortcodes = array();

    preg_match_all( '/\[([a-zA-Z0-9_-]+)(?:\s[^\]]*?)?\]/s', $content, $matches );

    if ( ! empty( $matches[1] ) ) {
        foreach ( $matches[1] as $shortcode ) {
            if ( ! isset( $detected_shortcodes[ $shortcode ] ) ) {
                $detected_shortcodes[ $shortcode ] = 0;
            }
            $detected_shortcodes[ $shortcode ]++;
        }
    }

    if ( ! empty( $detected_shortcodes ) ) {
        set_transient( 'shortcode_cache_detected_shortcodes', $detected_shortcodes, WEEK_IN_SECONDS );
    }
}

function shortcode_cache_clear_detected_shortcodes() {
    delete_transient( 'shortcode_cache_detected_shortcodes' );
}