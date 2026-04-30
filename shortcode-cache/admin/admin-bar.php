<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function shortcode_cache_add_admin_bar_menu() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    global $wp_admin_bar;

    if ( ! is_object( $wp_admin_bar ) ) {
        return;
    }

    $wp_admin_bar->add_menu(
        array(
            'id'    => 'shortcode-cache',
            'title' => __( 'Shortcode Cache', 'shortcode-cache' ),
            'href'  => admin_url( 'admin.php?page=shortcode_cache' ),
        )
    );

    $wp_admin_bar->add_menu(
        array(
            'id'     => 'shortcode-cache-clear-all',
            'parent' => 'shortcode-cache',
            'title'  => __( 'Clear All Cache', 'shortcode-cache' ),
            'href'   => '#',
            'meta'   => array(
                'class' => 'shortcode-cache-admin-bar-clear-all',
            ),
        )
    );
}

add_action( 'admin_bar_menu', 'shortcode_cache_add_admin_bar_menu', 100 );

function shortcode_cache_enqueue_admin_bar_scripts() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    wp_enqueue_script(
        'shortcode-cache-admin-bar',
        SHORTCODE_CACHE_PLUGIN_URL . 'assets/js/admin-bar.js',
        array( 'jquery' ),
        SHORTCODE_CACHE_VERSION,
        true
    );

    wp_localize_script(
        'shortcode-cache-admin-bar',
        'shortcodeCacheAdminBar',
        array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        )
    );
}

add_action( 'admin_enqueue_scripts', 'shortcode_cache_enqueue_admin_bar_scripts' );